<?php

namespace Hanoivip\Game\Events;

class UserPlay
{
    public $uid;
    
    public $svname;
    
    public function __construct($uid, $svname)
    {
        $this->uid = $uid;
        $this->svname = $svname;
    }
}