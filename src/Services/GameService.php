<?php

namespace Hanoivip\Game\Services;

use Hanoivip\Game\Recharge;
use Hanoivip\Game\Server;
use Hanoivip\Game\Contracts\IGameOperator;
use Hanoivip\Game\Contracts\ServerState;
use Hanoivip\PaymentClient\BalanceUtil;
use Carbon\Carbon;
use CurlHelper;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Hanoivip\Game\Events\UserRecharge;
use Hanoivip\Game\Events\UserPlay;
use Hanoivip\UserBag\Services\UserBagService;
use Hanoivip\Events\Game\UserExchangeItem;

class GameService
{
    const RANK_CACHE_DURATION = 43200;//half of day 
    
    const RANK_CACHE_PREFIX = "RankOf";
    
    const ONLINE_CACHE_DURATION = 120;//2mins
    
    const ONLINE_CACHE_PREFIX = "OnlineStat";
    
    const ROLE_CACHE_PREFIX = "RoleOf";
    
    const ROLE_CACHE_DURATION = 1800;//30mins
    
    
    protected $servers;
    
    protected $logs;
    
    protected $balance;
    
    protected $operator;
    
    protected $userBags;
    
    public function __construct(
        ServerService $servers, 
        UserLogService $logs, 
        BalanceUtil $balance, 
        IGameOperator $operator,
        UserBagService $userBags)
    {
        $this->servers = $servers;
        $this->logs = $logs;
        $this->balance = $balance;
        $this->operator = $operator;
        $this->userBags = $userBags;
    }
    
    /**
     * Protocol defined:
     * http://game.login.uri/login.php?user=$uid&svname=$svname&key=$loginkey
     * 
     * Return json:
     * + code = 0: success.
     * + iframe
     * 
     * @param Server $server
     * @param Authenticatable $user
     * @throws Exception
     * @return string Game url
     */
    public function enter($server, $user)
    {
        if (!$server->can_enter)
        {
            Log::debug("Game server is set not to enter now.");
            return 'message=' . $server->gm_message;
        }
        $onlines = $this->onlines();
        if (isset($onlines[$server->name]))
        {
            if ($server->max_online > 0 &&
                $onlines[$server->name] + 1 > $server->max_online)
            {
                Log::debug("Game server is full.");
                return 'message=Game server is full';
            }
        }
        if (empty($this->operator))
        {
            Log::error("Game operator is not set");
            throw new Exception("Máy chủ đang bảo trì. Vui lòng thử lại sau hoặc liên hệ GM.");
        }
        //TODO: make EnterResponse here;
        $uri = $this->operator->enter($user, $server);
        if (empty($uri))
        {
            Log::error("Game enter uri is empty");
            throw new Exception("Máy chủ đang bảo trì. Vui lòng thử lại sau hoặc liên hệ GM.");
        }
        $this->logs->logEnter($user->getAuthIdentifier(), $server);
        event(new UserPlay($user->getAuthIdentifier(), $server->name));
        return 'uri=' . $uri;
    }
    
    /**
     * 
     * @param Server $server
     * @param Authenticatable $user
     * @param string $package
     * @param array $params
     * @return boolean
     */
    public function recharge($server, $user, $package, $params)
    {
        $uid = $user->getAuthIdentifier();
        $recharge = Recharge::where('code', $package)
                        ->first();
        if (empty($recharge))
        {
            Log::error("Game recharge package bogus");
            return false;
        }
        $lock = "Recharging" . $uid;
        if (!Cache::lock($lock, 120)->get())
        {
            Log::error("Game other recharge is in progress..");
            return false;
        }
        $coin = $recharge->coin;
        $cointype = $recharge->coin_type;
        if (!$this->balance->enough($uid, $coin, $cointype))
        {
            Log::error("Game user not enough coin");
            Cache::lock($lock)->release();
            return false;
        }
        if (empty($this->operator))
        {
            Log::error("Game operator is not set");
            Cache::lock($lock)->release();
            throw new Exception("Chuyển xu vào game không thành công. Vui lòng liên hệ GM.");
        }
        $order = uniqid();
        try
        {
            if (!$this->operator->recharge($user, $server, $order, $recharge, $params))
            {
                Log::error("Game game operator return fail.");
                Cache::lock($lock)->release();
                return false;
            }
        }
        catch (Exception $ex)
        {
            Log::error("Game game operator exception. Ex:" . $ex->getMessage());
            Cache::lock($lock)->release();
            return false;
        }
        $this->logs->logRecharge($uid, $server, $package, $order);
        $reason = "Recharge:" . $cointype . ":" . $coin . ":" . $server->title;
        if (!$this->balance->remove($uid, $coin, $reason, $cointype))
        {
            Log::warn("Game charge user's balance fail. User {$uid} coin {$coin} type {$cointype}");
        }
        Cache::lock($lock)->release();
        $event = new UserRecharge($uid, $cointype, $coin, $server->name);
        if (!empty($params))
            $event->params = $params;
        event($event);
        return true;
    }
    
    /**
     * 
     * @param Server $server
     * @return boolean
     */
    public function isMaintain($server)
    {
        $onlines = $this->onlines();
        return !$server->can_enter || !isset($onlines[$server->name]);
    }
    
    /**
     * Thống kê trạng thái từng cụm máy chủ
     * 
     * @return array server name => ServerState
     */
    public function status()
    {
        $status = [];
        
        $onlines = $this->onlines();
        $all = $this->servers->getAll();
        foreach ($all as $server)
        {
            if ($this->isMaintain($server) ||
                !isset($onlines[$server->name]))
                $status[$server->name] = ServerState::MAINTAIN;
            else 
            {
                $onl = $onlines[$server->name];
                if ($onl >= $server->max_online)
                    $status[$server->name] = ServerState::FULL;
                else if ($onl >= intval(0.8 * $server->max_online))
                    $status[$server->name] = ServerState::HOT;
                else if ($onl >= 0)
                    $status[$server->name] = ServerState::GOOD;
                else 
                    $status[$server->name] = ServerState::MAINTAIN;
            }
            
        }
        
        return $status;
    }
    
    /**
     * Thống kê số lượng online. 
     * 
     * @return array server name => online number
     */
    public function onlines($force = false)
    {
        if (Cache::has(self::ONLINE_CACHE_PREFIX) && !$force)
        {
            return Cache::get(self::ONLINE_CACHE_PREFIX);
        }
        $onlines = [];
        $all = $this->servers->getAll();
        foreach ($all as $server)
        {
            try 
            {
                if (empty($this->operator))
                    throw new Exception("Game operator is not set.");
                $onlines[$server->name] = $this->operator->online($server);
            }
            catch (Exception $ex)
            {
                Log::error("Game query online number of {$server->name} fail. Skip!");
                $onlines[$server->name] = -1;
            }
        }
        $expires = Carbon::now()->addSeconds(self::ONLINE_CACHE_DURATION);
        Cache::put(self::ONLINE_CACHE_PREFIX, $onlines, $expires);
        return $onlines;
    }
    
    /**
     * 
     * @return array server name => [ rank type => [rank index => player info] ]
     */
    public function rank($force = false)
    {
        $key = self::RANK_CACHE_PREFIX;
        if (Cache::has($key) && !$force)
        {
            return Cache::get($key);
        }
        $ranks = [];
        $all = $this->servers->getAll();
        foreach ($all as $server)
        {
            try 
            {
                if (empty($this->operator))
                    throw new Exception("Game operator is not set.");
                $ranks[$server->name] = $this->operator->rank($server);
            }
            catch (Exception $ex)
            {
                Log::error("Game query ranks of {$server->name} fail. Skip!");
                $ranks[$server->name] = [];
            }
        }
        
        $expires = Carbon::now()->addSeconds(self::RANK_CACHE_DURATION);
        Cache::put($key, $ranks, $expires);
        return $ranks;
    }
    
    /**
     * 
     * @param Server $server
     * @param Authenticatable $user
     * @param string $itemId
     * @param number $itemCount
     */
    public function sendItem($server, $user, $itemId, $itemCount, $params = null)
    {
        $uid = $user->getAuthIdentifier();
        $bag = $this->userBags->getUserBag($uid);
        if (empty($bag))
            throw new Exception("Game user bag can not be created!");
        if (!$bag->enough($itemId, $itemCount))
            return __('bag.exchange.not-enough');
        // Send Item to game
        $order = uniqid();
        if (!$this->operator->sentItem($user, $server, $order, $itemId, $itemCount, $params))
        {
            Log::error("Game request item exchange fail.");
            return false;
        }
        if (!$bag->subItem($itemId, $itemCount))
            Log::error("Game item exchanged but can not removed from bag!");
        // Log
        event(new UserExchangeItem($user, $server, $itemId, $itemCount, $params));
        return true; 
    }
    
    /**
     * Query and cached info
     * 
     * @param number $uid
     * @param Server $server
     */
    public function queryRoles($uid, $server)
    {
        //Log::debug(print_r($server, true));
        $key = self::ROLE_CACHE_PREFIX . $uid . '_' . $server->name;
        if (Cache::has($key))
        {
            return Cache::get($key);
        }
        $roles = [];
        try 
        {
            $roles = $this->operator->characters($uid, $server);
        } 
        catch (Exception $e) 
        {
            Log::error($e->getMessage());
        }
        if (!empty($roles))
            Cache::put($key, $roles, Carbon::now()->addMinutes(self::ROLE_CACHE_DURATION));
        return $roles;
    }
}