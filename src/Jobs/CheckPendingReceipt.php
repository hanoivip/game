<?php

namespace Hanoivip\Game\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Hanoivip\Game\Services\RechargeService;

class CheckPendingReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $order;
    
    private $userId;
    
    private $receipt;
    
    private $service;
    
    public function __construct($userId, $order, $receipt)
    {
        $this->userId = $userId;
        $this->order = $order;
        $this->receipt = $receipt;
        $this->service = new RechargeService();
    }

    public function handle()
    {
        $this->service->onPaymentCallback($this->userId, $this->order, $this->receipt);
    }
}
