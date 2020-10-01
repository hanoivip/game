<?php

namespace Hanoivip\Game\Services;

use Hanoivip\Game\Server;
use Hanoivip\GameContracts\ViewOjects\UserVO;
use Hanoivip\GameContracts\Contracts\IGameOperator;

class GameHelper
{
    private $game;
    
    public function __construct(
        IGameOperator $game)
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
    public function sendItem($userId, $server, $item, $count, $role)
    {
        $serverRec = Server::where('name', $server)->first();
        return $this->game->sentItem(new UserVO($userId, ""), $serverRec, uniqid(), $item, $count, ['roleid' => $role]);
    }
}