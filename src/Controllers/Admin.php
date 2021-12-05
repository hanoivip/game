<?php

namespace Hanoivip\Game\Controllers;

use App\Http\Controllers\Controller;
use Hanoivip\Game\Services\RechargeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\Game\RechargeLog;
use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Hanoivip\Payment\Models\Transaction;
use Hanoivip\IapContract\Facades\IapFacade;
use Hanoivip\Game\Jobs\CheckPendingReceipt;

class Admin extends Controller
{   
    private $rechargeService;
    
    public function __construct(RechargeService $recharge)
    {
        $this->rechargeService = $recharge;
    }
    public function index()
    {
        return view('hanoivip::admin.newrecharge');
    }
    /**
     * List all purchases
     * @param Request $request
     */
    public function history(Request $request)
    {
        try
        {
            $tid = $request->input('tid');
            $page = 0;
            if ($request->has("page"))
                $page = $request->input("page");
            $result = $this->rechargeService->history($tid, $page);
            if ($request->ajax())
            {
                return ['error' => 0, 'message' => 'success', 'data' => ['history' => $result[0], 'total_page' => $result[1]]];
            }
            else
            {
                return view('hanoivip::admin.newrecharge-history', ['history' => $result[0], 'total_page' => $result[1]]);
            }
        }
        catch (Exception $ex)
        {
            Log::error("Admin query trans exception: " . $ex->getMessage());
            return view('hanoivip::admin.newrecharge-failure', ['message' => __('hanoivip::newrecharge.query-error')]);
        }
    }
    
    public function viewReceipt(Request $request)
    {
        $receipt = null;
        if ($request->has('receipt'))
        {
            $receipt = $request->input('receipt');
        }
        $detail = null;
        $message = null;
        $trans = null;
        if (!empty($receipt))
        {
            $detail = PaymentFacade::query($receipt);
            $trans = Transaction::where('trans_id', $receipt)->first();
        }
        return view('hanoivip::admin.newrecharge-receipt', ['detail' => $detail, 'receipt' => $receipt, 'trans' => $trans]);
    }
    /**
     * Trigger a receipt callback
     * @param Request $request
     */
    public function retry(Request $request)
    {
        $receipt = $request->input('receipt');
        $transaction = Transaction::where('trans_id', $receipt)->first();
        if (empty($transaction))
        {
            return view('hanoivip::admin.newrecharge-receipt-retrigger', ['message' => 'Receipt not found']);
        }
        $order = $transaction->order;
        $orderDetail = IapFacade::detail($order);
        if (empty($orderDetail))
        {
            return view('hanoivip::admin.newrecharge-receipt-retrigger', ['message' => 'Order not found']);
        }
        try
        {
            $result = $this->rechargeService->onPaymentCallback($orderDetail['user'], $order, $receipt);
            if (gettype($result) == 'string')
            {
                if ($request->ajax())
                {
                    return ['error' => 1, 'message' => $result, 'data' => []];
                }
                else
                {
                    return view('hanoivip::admin.newrecharge-receipt-retrigger', ['message' => $result]);
                }
            }
            else
            {
                /** @var \Hanoivip\PaymentMethodContract\IPaymentResult $result */
                if ($result->isPending())
                {
                    dispatch(new CheckPendingReceipt($orderDetail['user'], $order, $receipt))->delay(60);
                    if ($request->ajax())
                    {
                        return ['error' => 0, 'message' => 'pending', 'data' => ['trans' => $receipt]];
                    }
                    else
                    {
                        return view('hanoivip::admin.newrecharge-receipt-retrigger', ['message' => 'Payment still pending..just wait..']);
                    }
                }
                elseif ($result->isFailure())
                {
                    if ($request->ajax())
                    {
                        return ['error' => 2, 'message' => $result->getDetail(), 'data' => []];
                    }
                    else
                    {
                        return view('hanoivip::admin.newrecharge-receipt-retrigger', ['message' => $result->getDetail()]);
                    }
                }
                else
                {
                    if ($request->ajax())
                    {
                        return ['error' => 0, 'message' => 'success', 'data' => []];
                    }
                    else
                    {
                        return view('hanoivip::admin.newrecharge-receipt-retrigger', ['message' => 'Success']);
                    }
                }
            }
        }
        catch (Exception $ex)
        {
            Log::error("NewFlow recharge callback exception: " . $ex->getMessage() . $ex->getTraceAsString());
            if ($request->ajax())
            {
                return ['error' => 3, 'message' => __('hanoivip::newrecharge.callback-error'), 'data' => []];
            }
            else
            {
                return view('hanoivip::admin.newrecharge-receipt-retrigger', ['message' => __('hanoivip::newrecharge.callback-error')]);
            }
        }
    }
    
    public function today()
    {
        $startTime = date('Y-m-d H:i:s', strtotime('today midnight'));
        $endTime = date('Y-m-d H:i:s', strtotime('tomorrow'));
        $sum = $this->rechargeService->sumAmount($startTime, $endTime);
        return view('hanoivip::admin.newrecharge-income-result', ['sum' => $sum]);
    }
    
    public function thisMonth()
    {
        $startTime = date('Y-m-d H:i:s', strtotime('first day of this month midnight'));
        $endTime = date('Y-m-d H:i:s', strtotime('first day of next month midnight'));
        $sum = $this->rechargeService->sumAmount($startTime, $endTime);
        return view('hanoivip::admin.newrecharge-income-result', ['sum' => $sum]);
    }
    
    public function statByTime(Request $request)
    {
        $startTime = $request->get('start_time') . ' 00:00:00';
        $endTime = $request->get('end_time') . ' 23:59:59';
        $sum = $this->rechargeService->sumAmount($startTime, $endTime);
        return view('hanoivip::admin.newrecharge-income-result', ['sum' => $sum]);
    }
    
    public function stats()
    {
        return view('hanoivip::admin.newrecharge-income');
    }
    
}