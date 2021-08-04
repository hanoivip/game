<?php

namespace Hanoivip\Game\Jobs;

use Hanoivip\Game\Facades\GameHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendCoin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //private $userId;
    private $orderDetail;
    private $log;
    public function __construct($orderDetail, $log)
    {
        //$this->userId = $userId;
        $this->orderDetail = $orderDetail;
        $this->log = $log;
    }

    public function handle()
    {
        $result = GameHelper::recharge($this->orderDetail['user'],
            $this->orderDetail['server'], 
            $this->orderDetail['item'], 
            $this->orderDetail['role']);
        if ($result === true)
        {
            $this->log->game_status = 1;
            $this->log->save();
        }
        else
        {
            $this->log->game_status = 2;
            $this->log->save();
            $this->release(60);
        }
        
    }
}
