<?php

namespace Hanoivip\Game\Services;

use Hanoivip\GameContracts\ViewObjects\ServerVO;
use Hanoivip\GameContracts\ViewObjects\UserVO;

class GameHelper
{
    private $game;
    
    public function __construct(
        GameService $game)
    {
        $this->game = $game;
    }
    /**
     * 
     * @param number $userId
     * @param string $server Server Name
     * @param string $item Item ID
     * @param number $count
     * @param string $role Player Role ID
     */
    public function sendItem($userId, $server, $item, $count, $role, $receiverId = 0)
    {
        if (empty($receiverId))
            return $this->game->sendItem($server, new UserVO($userId, ""), $item, $count, ['roleid' => $role]);
        else 
            return $this->game->sendItem($server, new UserVO($userId, ""), $item, $count, ['roleid' => $role], new UserVO($receiverId, ""));
    }
    /**
     * 
     * @param number $userId
     * @param string $server Server Name
     * @param string $package Recharge package code
     * @param string $role Role id
     * @param number $receiverId
     */
    public function recharge($userId, $server, $package, $role, $receiverId = 0)
    {
        if (empty($receiverId))
            return $this->game->recharge($server, new UserVO($userId, ""), $package, ['roleid' => $role]);
        else
            return $this->game->recharge($server, new UserVO($userId, ""), $package, ['roleid' => $role], new UserVO($receiverId, ""));
    }
    
    public function getRechargePackages()
    {
        return $this->game->getRechargePackages();
    }
    /**
     * 
     * @param string|ServerVO $server
     * @param number $userId
     */
    public function getRoles($server, $userId)
    {
        return $this->game->queryRoles(new UserVO($userId, ""), $server);
    }
}