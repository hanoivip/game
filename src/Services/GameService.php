<?php

namespace Hanoivip\Game\Services;

use Hanoivip\Game\Recharge;
use Hanoivip\Game\Server;
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

class GameService
{
    const RANK_CACHE_DURATION = 43200;//half of day 
    
    const RANK_CACHE_PREFIX = "RankOf";
    
    const ONLINE_CACHE_DURATION = 120;//2mins
    
    const ONLINE_CACHE_PREFIX = "OnlineStat";
    
    
    protected $servers;
    
    protected $logs;
    
    protected $balance;
    
    public function __construct(ServerService $servers, 
        UserLogService $logs, BalanceUtil $balance)
    {
        $this->servers = $servers;
        $this->logs = $logs;
        $this->balance = $balance;
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
        //Log::debug(print_r($user, true));
        $enterParam = [
            'loginid' => $user['id'],
            'loginname' => $user['id'],//need loginname builder $user['email'],
            'svid' => $server->name,
            'ticket' => md5($user['id'] . $user['id'] . $server->name . config('game.loginkey')),
        ];
        $enterUrl = $server->login_uri . '/login.php?' . http_build_query($enterParam);
        $response = CurlHelper::factory($enterUrl)->exec();
        if ($response['data'] === false)
        {
            Log::error("Game enter server exception. Raw content = " . $response['content']);
            return;
        }
        if ($response['data']['code'] != 0)
        {
            Log::error("Game enter server error. Returned code = " . $response['data']['code']);
            return;
        }
        $this->logs->logEnter($user['id'], $server);
        event(new UserPlay($user['id'], $server->name));
        return 'uri=' . $response['data']['iframe'];
    }
    
    /**
     * 
     * @param Server $server
     * @param number $uid
     * @param string $package
     * @return boolean
     */
    public function recharge($server, $uid, $package)
    {
        $recharge = Recharge::where('code', $package)
                        ->first();
        if (empty($recharge))
        {
            Log::error("Game recharge package bogus");
            return false;
        }
        $coin = $recharge->coin;
        $cointype = $recharge->coin_type;
        if (!$this->balance->enough($uid, $coin, $cointype))
        {
            Log::error("Game user not enough coin");
            return false;
        }
        
        $now = time();
        $order = uniqid();
        $rechargeParams = [
            'loginname' => $uid,
            'svid' => $server->name,
            'type' => $cointype,
            'value' => $coin,
            'tstamp' => $now,
            'order' => $order,
            'ticket' => md5($uid . $coin . $order . $now . config('game.recharge')),
        ];
        $rechargeUrl = $server->recharge_uri . '/pay.php?' . http_build_query($rechargeParams);
        $response = CurlHelper::factory($rechargeUrl)->exec();
        if ($response['data'] === false)
        {
            Log::error("Game recharge server exception. Returned content: " . $response['content']);
            return false;
        }
        if ($response['data']['code'] != 0)
        {
            Log::error("Game recharge server error. Code=" . $response['data']['code']);
            return false;
        }
        $this->logs->logRecharge($uid, $server, $package, $order);
        $reason = "Recharge:" . $cointype . ":" . $coin . ":" . $server->title;
        if (!$this->balance->remove($uid, $coin, $reason, $cointype))
        {
            Log::warn("Game charge user's balance fail. User {$uid} coin {$coin} type {$cointype}");
        }
        
        event(new UserRecharge($uid, $cointype, $coin, $server->name));
        
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
                $onlineUrl = $server->operate_uri . "/online.php";
                $response = \CurlHelper::factory($onlineUrl)->exec();
                if ($response['data'] === false)
                {
                    Log::error("Game query online number of {$server->name} fail. Skip!");
                    $onlines[$server->name] = -1;
                }
                else 
                {
                    $onlines[$server->name] = $response['data']['online'];
                }
            }
            catch (Exception $ex)
            {
                Log::error("Game query online number of {$server->name} fail. Skip!");
                $onlines[$server->name] = -1;
            }
        }
        $expires = Carbon::now()->addSeconds(self::ONLINE_CACHE_DURATION);
        Cache::add(self::ONLINE_CACHE_PREFIX, $onlines, $expires);
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
            // fetch and cache
            $rankUrl = $server->operate_uri . "/rank.php";
            $response = \CurlHelper::factory($rankUrl)->exec();
            if ($response['data'] === false)
            {
                Log::error("Game fetch server rank exception.");
                $ranks[$server->name] = [];
            }
            else
                $ranks[$server->name] = $response['data'];
        }
        
        $expires = Carbon::now()->addSeconds(self::RANK_CACHE_DURATION);
        Cache::add($key, $ranks, $expires);
        return $ranks;
    }
}