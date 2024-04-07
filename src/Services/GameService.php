<?php

namespace Hanoivip\Game\Services;

use Hanoivip\Events\Game\UserTransfered;
use Hanoivip\Game\Recharge;
use Hanoivip\GameContracts\Contracts\IGameOperator;
use Hanoivip\GameContracts\Contracts\ServerState;
use Hanoivip\GameContracts\ViewObjects\ServerVO;
use Hanoivip\GameContracts\ViewObjects\UserGameRoleVO;
use Hanoivip\GameContracts\ViewObjects\UserVO;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\Game\DefaultRole;

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
     * Non-thread-safe recharge
     * 
     * @param string $server Server name
     * @param UserVO $user
     * @param string $item Recharge package code
     * @param array $params
     * @return true|string true if success or string error detail
     */
    public function recharge($serverName, $user, $package, $role)
    {
        $server = $this->servers->getServerByName($serverName);
        //$recharge = Recharge::where('code', $item)->first();
        // Process
        $uid = $user->getAuthIdentifier();
        //$package = $recharge->code;
        //$coin = $recharge->coin;
        //$cointype = $recharge->coin_type;
        $order = uniqid();
        try
        {
            if (!$this->operator->buyPackage($user, $server, $order, $package, $role))
            {
                Log::error("Game game operator return fail.");
                return __('hanoivip.game::recharge.ops-recharge-fail');
            }
            $this->logs->logRecharge($uid, $server, $package, $order, $user->getAuthIdentifier());
        }
        catch (Exception $ex)
        {
            Log::error("Game game operator exception. Ex:" . $ex->getMessage());
            return __('hanoivip.game::recharge.ops-recharge-ex');
        }
        return true;
    }
    public function rechargeByMoney($serverName, $user, $amount, $role)
    {
        $server = $this->servers->getServerByName($serverName);
        $uid = $user->getAuthIdentifier();
        $order = uniqid();
        try
        {
            if (!$this->operator->buyByMoney($user, $server, $order, $amount, $role))
            {
                Log::error("Game game operator return fail.");
                return __('hanoivip.game::recharge.ops-recharge-fail');
            }
            //$this->logs->logRecharge($uid, $server, $amount, $order, $user->getAuthIdentifier());
        }
        catch (Exception $ex)
        {
            Log::error("Game game operator exception. Ex:" . $ex->getMessage());
            return __('hanoivip.game::recharge.ops-recharge-ex');
        }
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
    public function sendItem($serverName, $user, $itemId, $itemCount, $role)
    {
        $server = $this->servers->getServerByName($serverName);
        // Send Item to game
        $order = uniqid();
        if (!$this->operator->sentItem($user, $server, $order, $itemId, $itemCount, $role))
        {
            Log::error("Game request item exchange fail.");
            return false;
        }
        return true; 
    }
    /**
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
    /**
     * 
     * @param number $userId
     * @return UserGameRoleVO
     */
    public function getUserDefaultRole($userId)
    {
        return DefaultRole::where('user_id', $userId)->first();
    }
    
    public function saveUserDefaultRole($userId, $server, $role)
    {
        $record = DefaultRole::where('user_id', $userId)->first();
        if (empty($record))
        {
            $record = new DefaultRole();
            $record->user_id = $userId;
        }
        $record->server = $server;
        $record->role = $role;
        $record->save();
        return true;
    }
    
    public function getOrderDetail($server, $order)
    {
        $serverRec = $this->servers->getServerByName($server);
        return $this->operator->orderDetail(null, $serverRec, $order);
    }
}