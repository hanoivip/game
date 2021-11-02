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
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\Game\GoogleReceipt;

class GoogleSlowCard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 15;
    
    private $productId;
    
    private $token;
    
    private $order;
    
    // laravel 5, can not be dynamic
    //MaxAttemptsExceededException???
    //public $retryAfter = 180;
    
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
        Redis::funnel('GoogleSlow@' . $this->order)->limit(1)->then(function () {
            try 
            {
                $receipt = Product::googlePlay()->id($this->productId)->token($this->token)->get();
                if (!empty($receipt) && $receipt->getPurchaseState()->isPurchased())
                {
                    // success
                    $orderDetail = IapFacade::detail($this->order);
                    $result = GameHelper::recharge($orderDetail['user'],
                        $orderDetail['server'],
                        $orderDetail['item'],
                        $orderDetail['role']);
                    $log = GoogleReceipt::where('purchase_token', $this->token)->first();
                    $log->state = $result ? 1 : 2;
                    $log->save();
                    //if (!$result) $this->release(120);//not work?
                    //$this->delay = 120; not work too, fuck laravel 5
                    //$this->delay(120);not work? fuck laravel 5
                    //$this->release();MaxAttemptsExceededException
                    if (!$result) $this->release(120);//again?
                    // job done
                }
                if (!empty($receipt) && $receipt->getPurchaseState()->isPending())
                {
                    // retry job
                    //$this->release(300);
                    //$this->delay = 300;not work too
                    //$this->delay(180);not work fuck laravel 5
                    $this->release(180);
                }
                if (empty($receipt) || $receipt->getPurchaseState()->isCancelled())
                {
                    // job done
                }
            } 
            catch (Exception $e) 
            {
                Log::debug("GoogleSlow check payment exception." . $e->getMessage());
                $this->release(1500);
            }
        });
    }
}
