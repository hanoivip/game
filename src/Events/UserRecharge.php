<?php

namespace Hanoivip\Game\Events;

class UserRecharge
{
    public $uid;
    
    public $cointype;
    
    public $coin;
    
    public $svname;
    
    public $params;
    
    public function __construct($uid, $cointype, $coin, $svname)
    {
        $this->uid = $uid;
        $this->cointype = $cointype;
        $this->coin = $coin;
        $this->svname = $svname;
    }
}