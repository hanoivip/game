<?php

namespace Hanoivip\Game\Jobs;

use Hanoivip\Game\Facades\GameHelper;
use Hanoivip\IapContract\Facades\IapFacade;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Imdhemy\Purchases\Facades\Product;
use Illuminate\Support\Facades\Redis;

class GoogleSlowCard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 15;
    
    private $productId;
    
    private $token;
    
    private $order;
    
    public function __construct($order, $productId, $token)
    {
        $this->order = $order;
        $this->productId = $productId;
        $this->token = $token;
    }
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //Redis::funnel('GoogleSlow@' . $this->order)->limit(1)->then(function () {
        Redis::throttle('GoogleSlow@' . $this->order)->block(0)->allow(1)->every(180)->then(function () {
            $receipt = Product::googlePlay()->id($this->productId)->token($this->token)->get();
            if (!empty($receipt) && $receipt->getPurchaseState()->isPurchased())
            {
                // success
                $orderDetail = IapFacade::detail($this->order);
                $result = GameHelper::recharge($orderDetail['user'],
                    $orderDetail['server'],
                    $orderDetail['item'],
                    $orderDetail['role']);
                //if (!$result) $this->release(120);//not work?
                //$this->delay = 120; not work too, fuck laravel 5
                //$this->delay(120);not work? fuck laravel 5
                $this->release();
                // job done
            }
            if (!empty($receipt) && $receipt->getPurchaseState()->isPending())
            {
                // retry job
                //$this->release(300);
                //$this->delay = 300;not work too
                //$this->delay(180);not work fuck laravel 5
                $this->release();
            }
            if (empty($receipt) || $receipt->getPurchaseState()->isCancelled())
            {
                // job exit
            }
        });
    }
}
