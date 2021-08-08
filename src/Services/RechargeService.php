<?php

namespace Hanoivip\Game\Services;

use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Hanoivip\IapContract\Facades\IapFacade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Hanoivip\Payment\Facades\BalanceFacade;
use Hanoivip\Game\RechargeLog;
use Hanoivip\Game\Jobs\SendCoin;

class RechargeService
{   
    public function query($userId, $trans)
    {
        $log = RechargeLog::where('user_id', $userId)
        ->where('receipt', $trans)->first();
        if (!empty($log))
        {
            if ($log->status == 5)
                return __('hanoivip::newrecharge.not-enough-money');
            return PaymentFacade::query($trans);
        }
        return __('hanoivip::newrecharge.receipt-not-exists');
    }
    /**
     * Thread safe
     */
    public function onPaymentCallback($userId, $order, $receipt)
    {
        $lock = Cache::lock('PaymentCallback@' . $userId, 10);
        if (!$lock->get())
            return __('hanoivip::newrecharge.callback-in-progress');
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
        $status = 0;
        /** @var \Hanoivip\PaymentMethodContract\IPaymentResult $result */
        if ($result->isPending())
        {
            $status = 1;
        }
        elseif ($result->isFailure())
        {
            $status = 2;
        }
        else
        {
            $status = 3;
            $orderDetail = IapFacade::detail($order);
            $amount = intval($result->getAmount());
            $price = intval($orderDetail['item_price']);
            // how is about currency??
            if ($amount >= $price)
            {
                dispatch(new SendCoin($orderDetail, $log->id));
                $change = $amount - $price;
                if (!empty($change))
                {
                    Log::debug("RechargeService there was a change $change on $order");
                    BalanceFacade::add($userId, $change, "PaymentChanges:" . $order);
                    $status = 4;
                }
            }
            else
            {
                $status = 5;
                BalanceFacade::add($userId, $amount, "PaymentRefund:" . $order);
            }
            $log->amount = $amount;
        }
        $log->status = $status;
        $log->save();
        $lock->release();
        if ($status == 5)
            return __('hanoivip::newrecharge.not-enough-money');
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
}