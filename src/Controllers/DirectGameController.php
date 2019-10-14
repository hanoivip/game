<?php

namespace Hanoivip\Game\Controllers;

use Hanoivip\Game\Contracts\IGameService;
use Hanoivip\Game\Services\ServerService;
use Hanoivip\Game\Services\UserLogService;
use Hanoivip\Game\Services\ScheduleService;

class DirectGameController extends Controller
{
    protected $games;
    
    protected $servers;
    
    protected $logs;
    
    protected $schedule;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(IGameService $games, 
        ServerService $servers, UserLogService $logs,
        ScheduleService $schedule)
    {
        $this->games = $games;
        $this->servers = $servers;
        $this->logs = $logs;
        $this->schedule = $schedule;
    }
    
    public function recharge($svname, $user, $coin)
    {
        $server = $this->servers->getServerByName($svname);
        if (empty($server))
            return false;
        return $this->games->recharge($server, $user, $money);
    }
    
    public function serverlist()
    {
        return $this->servers->getAll();
    }
    
    public function schedule()
    {
        return $this->schedule->getAll();
    }
    
    public function serverstatus()
    {
        return $this->games->status();
    }
    
    public function serverrecent($user)
    {
        return $this->logs->getRecentEnter($uid);
    }
    
    public function rank($svname)
    {
        return $this->games->rank($svname);
    }
}
