<?php

namespace Hanoivip\Game\Jobs;

use Hanoivip\Game\Facades\GameHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Hanoivip\Game\RechargeLog;
use Hanoivip\Events\Game\UserBuyItem;

//Setup 1 supervisor worker!!!
class SendCoin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 
     * @var array
     */
    private $orderDetail;
    /**
     * 
     * @var number
     */
    private $logId;
    
    public $tries = 5;
    
    public function __construct($orderDetail, $logId)
    {
        $this->orderDetail = $orderDetail;
        $this->logId = $logId;
    }

    public function handle()
    {
        Redis::funnel('SendCoin@' . $this->orderDetail['user'])->limit(1)->then(function () {
            $result = GameHelper::recharge($this->orderDetail['user'],
                $this->orderDetail['server'],
                $this->orderDetail['item'],
                $this->orderDetail['role']);
            $log = RechargeLog::find($this->logId);
            if ($result === true)
            {
                event(new UserBuyItem(
                    $this->orderDetail['user'],
                    $this->orderDetail['server'],
                    $this->orderDetail['item'],
                    $this->orderDetail['role']));
                $log->game_status = 1;
                $log->save();
            }
            else
            {
                $log->game_status = 2;
                $log->save();
                $this->release(60);
            }
        }, function () {
            // Could not obtain lock...
            return $this->release(60);
        });
        
    }
}
