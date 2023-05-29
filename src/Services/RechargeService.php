<?php

namespace Hanoivip\Game\Services;

use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Hanoivip\Payment\Facades\BalanceFacade;
use Hanoivip\IapContract\Facades\IapFacade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Hanoivip\Game\RechargeLog;
use Hanoivip\Game\Jobs\SendCoin;
use Hanoivip\Events\Gate\UserTopup;

class RechargeService
{   
    public function query($userId, $trans)
    {
        $log = RechargeLog::where('user_id', $userId)
        ->where('receipt', $trans)->first();
        if (!empty($log))
        {
            if ($log->status == 5)
                return __('hanoivip.game::newrecharge.not-enough-money');
            return PaymentFacade::query($trans);
        }
        return __('hanoivip.game::newrecharge.receipt-not-exists');
    }
    
    public function queryReceipt($trans, $force=false)
    {
        $log = RechargeLog::where('receipt', $trans)->first();
        if (!empty($log))
        {
            if ($log->status == 5)
                return __('hanoivip.game::newrecharge.not-enough-money');
            return PaymentFacade::query($trans, $force);
        }
        return __('hanoivip.game::newrecharge.receipt-not-exists');
    }
    
    /**
     * Thread safe
     */
    public function onPaymentCallback($userId, $order, $receipt)
    {
        $lock = Cache::lock('PaymentCallback@' . $userId, 10);
        if (!$lock->get())
            return __('hanoivip.game::newrecharge.callback-in-progress');
        $result = PaymentFacade::query($receipt);
        $log = RechargeLog::where('user_id', $userId)
        ->where('order', $order)
        ->where('receipt', $receipt)
        ->first();
        if (empty($log))
        {
            $log = new RechargeLog();
            $log->user_id = $userId;
            $log->order = $order;
            $log->receipt = $receipt;
            $log->status = 0;
            $log->save();
        }
        /** @var \Hanoivip\PaymentMethodContract\IPaymentResult $result */
        if ($result->isPending())
        {
            $log->status = 1;
        }
        elseif ($result->isFailure())
        {
            $log->status = 2;
        }
        elseif ($result->isSuccess())
        {
            if ($log->status < 3)
            {
                // first time to process success
                $log->status = 3;
                $orderDetail = IapFacade::detail($order);
                $amount = intval($result->getAmount());
                $amountCur = $result->getCurrency();
                $price = intval($orderDetail['item_price']);
                $currency = $orderDetail['item_currency'];
                // binh thuong thi su dung dich vu nap cua chinh dat nuoc no, nen ko can so sanh don vi tien te
                // neu su dung dich vu nap cua dat nuoc khac, thi lai phai so sanh ti le
                $conAmount = BalanceFacade::convert($amount, $amountCur, $currency);
                if ($conAmount >= $price)
                {
                    dispatch(new SendCoin($orderDetail, $log->id));
                    $change = $conAmount - $price;
                    if (!empty($change))
                    {
                        Log::debug("RechargeService there was a change $change on $order");
                        BalanceFacade::add($userId, $change, "PaymentChanges", 0, $currency);
                        $log->status = 4;
                    }
                }
                else
                {
                    $log->status = 5;
                    BalanceFacade::add($userId, $conAmount, "PaymentRefund", 0, $currency);
                }
                $log->amount = $conAmount;
                $log->currency = $currency;
                // notice 
                event(new UserTopup($userId, 0, $conAmount, $receipt));
            }
            else
            {
                // already process
            }
        }
        else
        {
            Log::error("Recharge trans result could not determine. Bug in logic..");
        }
        $log->save();
        $lock->release();
        if ($log->status == 5)
            return __('hanoivip.game::newrecharge.not-enough-money');
        return $result;
    }
    /**
     * 
     * @param number $page
     * @param number $count
     * @return array 0: list, 1: total page
     */
    public function history($userId, $page = 0, $count = 10)
    {
        $list = RechargeLog::where('user_id', $userId)
        ->skip($count * ($page - 1))
        ->take($count)
        ->orderBy('id', 'desc')
        ->get();
        $count = RechargeLog::where('user_id', $userId)->count();
        return [$list, ceil($count / 10)];
    }
    
    public function sumAmount($startTime, $endTime)
    {
        return RechargeLog::where('created_at', '>=', $startTime)
        ->where('created_at', '<', $endTime)
        //->where()
        ->sum('amount');
    }
}