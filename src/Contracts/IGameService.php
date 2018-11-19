<?php

namespace Hanoivip\Game\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Hanoivip\Game\Server;
use Hanoivip\Game\Recharge;

interface IGameService
{
    /**
     * 
     * @param Authenticatable $user
     * @param Server $server
     */
    public function enter($user, $server);
    
    /**
     * 
     * @param Authenticatable $user
     * @param Server $server
     * @param string $order Order ID, Mapping id
     * @param Recharge $package
     * @param array $params
     * @return boolean
     */
    public function recharge($user, $server, $order, $package, $params = null);
    
    /**
     * 
     * @param Server $server
     * @return number
     */
    public function online($server);
    
    /**
     * 
     * @param Server $server
     * @return array
     */
    public function rank($server);
    
    /**
     * 
     * @param Authenticatable $user
     * @param Server $server
     * @param string $order
     * @param number $itemId
     * @param number $itemCount
     * @param array $params
     */
    public function sentItem($user, $server, $order, $itemId, $itemCount, $params = null);
    
    /**
     * List all of game character in a server
     * 
     * @param Authenticatable $user
     * @param Server $server
     */
    public function characters($user, $server);
    
    /**
     * Does this operator support multiple of chars
     */
    public function supportMultiChar();
}