<?php

namespace Hanoivip\Game\Services;

use Hanoivip\Game\Contracts\IGameOperator;

class GenericOperator implements IGameOperator
{
    public function recharge($user, $server, $order, $package, $params = null)
    {}

    public function online($server)
    {}

    public function rank($server)
    {}

    public function enter($user, $server)
    {}
    public function sentItem($user, $server, $order, $itemId, $itemCount, $params = null)
    {}
    public function characters($uid, $server)
    {}
    public function supportMultiChar()
    {}




    
}