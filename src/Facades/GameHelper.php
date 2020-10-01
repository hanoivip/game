<?php

namespace Hanoivip\Game\Facades;

use Illuminate\Support\Facades\Facade;

class GameHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'GameHelper';
    }
}
