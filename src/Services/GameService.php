<?php

namespace Hanoivip\Game\Services;

use Hanoivip\Game\Recharge;
use Hanoivip\GameContracts\ViewOjects\RechargeVO;
use Hanoivip\GameContracts\ViewOjects\ServerVO;
use Hanoivip\GameContracts\ViewOjects\UserVO;
use Hanoivip\GameContracts\Contracts\IGameOperator;
use Hanoivip\GameContracts\Contracts\ServerState;
use Hanoivip\GateClient\Facades\BalanceFacade;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Hanoivip\Events\Game\UserRecharge;
use Hanoivip\Events\Game\UserPlay;

class GameService
{
    const RANK_CACHE_DURATION = 43200;//half of day 
    
    const RANK_CACHE_PREFIX = "RankOf";
    
    const ONLINE_CACHE_DURATION = 120;//2mins
    
    const ONLINE_CACHE_PREFIX = "OnlineStat";
    
    const ROLE_CACHE_PREFIX = "RoleOf";
    
    const ROLE_CACHE_DURATION = 1800;//30mins
    
    const ALL_ROLE_CACHE_PREFIX = "AllRoleOf";
    
    protected $servers;
    
    protected $logs;
    
    protected $operator;
    
    protected $userBags;
    
    public function __construct(
        ServerService $servers, 
        UserLogService $logs,
        IGameOperator $operator)
    {
        $this->servers = $servers;
        $this->logs = $logs;
        $this->operator = $operator;
    }
    /**
     * Protocol defined:
     * http://game.login.uri/login.php?user=$uid&svname=$svname&key=$loginkey
     * 
     * Return json:
     * + code = 0: success.
     * + iframe
     * 
     * @param ServerVO $server
     * @param UserVO $user
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
     * Non-thread-safe recharge
     * 
     * @param string $server Server name
     * @param UserVO $user
     * @param string $recharge Recharge package code
     * @param array $params
     * @param UserVO $receiver
     */
    public function recharge($serverName, $user, $rechargeCode, $params, $receiver = null)
    {
        $server = $this->servers->getServerByName($serverName);
        $recharge = Recharge::where('code', $rechargeCode)->first();
        // Process
        $uid = $user->getAuthIdentifier();
        $package = $recharge->code;
        $coin = $recharge->coin;
        $cointype = $recharge->coin_type;
        // Check enough
        if (!BalanceFacade::enough($uid, $coin, $cointype))
        {
            Log::error("Game user not enough coin");
            return __('hanoivip::game.recharge-fail.not-enough-coin');
        }
        $order = uniqid();
        try
        {
            $realReceiver = !empty($receiver) ? $receiver : $user;
            if (!$this->operator->recharge($realReceiver, $server, $order, $recharge, $params))
            {
                Log::error("Game game operator return fail.");
                return __('hanoivip::game.recharge-fail.ops-recharge-fail');
            }
            $this->logs->logRecharge($uid, $server, $package, $order, $realReceiver->getAuthIdentifier());
        }
        catch (Exception $ex)
        {
            Log::error("Game game operator exception. Ex:" . $ex->getMessage());
            return __('hanoivip::game.recharge-fail.ops-recharge-ex');
        }
        $reason = "Recharge:" . $cointype . ":" . $coin . ":" . $server->title;
        if (!BalanceFacade::remove($uid, $coin, $reason, $cointype))
        {
            Log::warn("Game charge user's balance fail. User {$uid} coin {$coin} type {$cointype}");
            return __('hanoivip::game.recharge-fail.remove-coin-fail');
        }
        // Event
        event(new UserRecharge($uid, $cointype, $coin, $server->name, $params));
        return true;
    }
    /**
     * 
     * @param ServerVO $server
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
            $onlines[$server->name] = $this->operator->online($server);
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
            $ranks[$server->name] = $this->operator->rank($server);
        }
        
        $expires = Carbon::now()->addSeconds(self::RANK_CACHE_DURATION);
        Cache::put($key, $ranks, $expires);
        return $ranks;
    }
    
    /**
     * 
     * @param string $serverName
     * @param UserVO $user
     * @param string $itemId
     * @param number $itemCount
     * @param array $params
     * @param UserVO $receiver
     */
    public function sendItem($serverName, $user, $itemId, $itemCount, $params = null, $receiver = null)
    {
        $server = $this->servers->getServerByName($serverName);
        // Send Item to game
        $order = uniqid();
        $realReceiver = empty($receiver) ? $user : $receiver;
        if (!$this->operator->sentItem($realReceiver, $server, $order, $itemId, $itemCount, $params))
        {
            Log::error("Game request item exchange fail.");
            return false;
        }
        return true; 
    }
    /**
     * Query and cached info
     * 
     * @param UserVO $user
     * @param ServerVO|string $server
     */
    public function queryRoles($user, $server)
    {
        $serverRec = $server;
        if (gettype($server) == 'string')
            $serverRec = $this->servers->getServerByName($server);
        try 
        {
            $uid = $user->getAuthIdentifier();
            $key = self::ROLE_CACHE_PREFIX . $uid . '_' . $serverRec->name;
            if (Cache::has($key))
            {
                return Cache::get($key);
            }
            $roles = $this->operator->characters($user, $serverRec);
            if (!empty($roles))
                Cache::put($key, $roles, Carbon::now()->addSeconds(self::ROLE_CACHE_DURATION));
            return $roles;
        } 
        catch (Exception $e) 
        {
            Log::error($e->getMessage());
        }
        return [];
    }
    /**
     * 
     * @param UserVO $user
     * @return array server name => array (roleid => rolename)
     */
    public function allRole($user)
    {
        $uid = $user->getAuthIdentifier();
        $key = self::ALL_ROLE_CACHE_PREFIX . $uid;
        $roles = [];
        if (!Cache::has($key))
        {
            // query all roles from all servers..
            $servers = $this->servers->getAll();
            foreach ($servers as $server)
            {
                $roleInServer = $this->operator->characters($user, $server);
                $roles[$server->name] = $roleInServer;
            }
            Cache::put($key, $roles, Carbon::now()->addSeconds(self::ROLE_CACHE_DURATION));
        }
        else
            $roles = Cache::get($key);
        return $roles;
    }
    
    public function accountHasManyChars()
    {
        return $this->operator->supportMultiChar();
    }
    
    public function getRechargePackages()
    {
        return Recharge::all();
    }
}