<?php

namespace Hanoivip\Game\Services;

use Hanoivip\Game\Recharge;
use Hanoivip\GameContracts\ViewObjects\ServerVO;
use Hanoivip\GameContracts\ViewObjects\UserVO;
use Hanoivip\GameContracts\Contracts\IGameOperator;
use Hanoivip\GameContracts\Contracts\ServerState;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Hanoivip\Events\Game\UserRecharge;
use Hanoivip\Events\Game\UserPlay;
use Hanoivip\Events\Game\UserTransfered;

class GameService
{
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
     * @param string $item Recharge package code
     * @param array $params
     * @param UserVO $receiver
     * @return true|string true if success or string error detail
     */
    public function recharge($serverName, $user, $item, $params, $receiver = null)
    {
        $server = $this->servers->getServerByName($serverName);
        $recharge = Recharge::where('code', $item)->first();
        // Process
        $uid = $user->getAuthIdentifier();
        $package = $recharge->code;
        $coin = $recharge->coin;
        $cointype = $recharge->coin_type;
        $order = uniqid();
        try
        {
            $realReceiver = !empty($receiver) ? $receiver : $user;
            if (!$this->operator->recharge($realReceiver, $server, $order, $recharge, $params))
            {
                Log::error("Game game operator return fail.");
                return __('hanoivip.game::recharge.ops-recharge-fail');
            }
            $this->logs->logRecharge($uid, $server, $package, $order, $realReceiver->getAuthIdentifier());
        }
        catch (Exception $ex)
        {
            Log::error("Game game operator exception. Ex:" . $ex->getMessage());
            return __('hanoivip.game::recharge.ops-recharge-ex');
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
        throw new Exception("Need implement onlines function");
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
        {
            $serverRec = $this->servers->getServerByName($server);
        }
        try 
        {
            return $this->operator->characters($user, $serverRec);
        } 
        catch (Exception $e) 
        {
            Log::error($e->getMessage());
        }
        return [];
    }
    
    public function getRechargePackages()
    {
        return Recharge::all();
    }
    
    public function getRank($server, $type)
    {
        $serverRec = $server;
        if (gettype($server) == 'string')
            $serverRec = $this->servers->getServerByName($server);
        return $this->operator->rank($serverRec, $type);
    }
    
    public function transferAccount($fromUser, $toUser)
    {
        $result = $this->operator->transfer($fromUser, $toUser);
        if ($result)
        {
            $this->logs->logTransfer($fromUser, $toUser);
            event(new UserTransfered($fromUser, $toUser));
        }
        return $result;
    }
}