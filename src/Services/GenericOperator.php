<?php

namespace Hanoivip\Game\Services;

use Hanoivip\GameContracts\Contracts\IGameOperator;

class GenericOperator implements IGameOperator
{
    public function characters($user, $server)
    {}

    public function recharge($user, $server, $order, $package, $params = null)
    {}

    public function supportMultiChar()
    {}

    public function online($server)
    {}

    public function rank($server)
    {}

    public function enter($user, $server)
    {}

    public function sentItem($user, $server, $order, $itemId, $itemCount, $params = null)
    {}

    public function order($user, $server, $package, $params = null)
    {}
    public function useCode($user, $server, $code, $params)
    {}


    
}