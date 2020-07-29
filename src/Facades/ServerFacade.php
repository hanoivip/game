<?php

namespace Hanoivip\Game\Facades;

use Illuminate\Support\Facades\Facade;

class ServerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ServerService';
    }
}
