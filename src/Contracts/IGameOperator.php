<?php

namespace Hanoivip\Game\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Hanoivip\Game\Server;
use Hanoivip\Game\Recharge;

interface IGameOperator
{
    /**
     * 
     * @param Authenticatable $user
     * @param Server $server
     * @return PlayResponse
     */
    public function enter($user, $server);
    
    /**
     * 
     * @param Authenticatable $user
     * @param Server $server
     * @param string $order Order ID, Mapping id
     * @param Recharge $package
     * @return boolean
     */
    public function recharge($user, $server, $order, $package);
    
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
}