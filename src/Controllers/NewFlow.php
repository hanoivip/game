<?php

namespace Hanoivip\Game\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Hanoivip\IapContract\Facades\IapFacade;
use Hanoivip\Game\Facades\GameHelper;
use Hanoivip\Game\Jobs\CheckPendingReceipt;
use Hanoivip\Game\Services\RechargeService;

class NewFlow extends Controller
{   
    private $rechargeService;
    
    public function __construct(RechargeService $recharge)
    {
        $this->rechargeService = $recharge;
    }
    
    public function startWizard(Request $request)
    {
        return redirect()->route('wizard.role', ['next' => 'newrecharge.shop']);
    }
    public function showShop(Request $request)
    {
        $svname=$request->input('svname');
        $role=$request->input('role');
        try
        {
            // show items
            $items = IapFacade::items();// or recharges ?
            return view('hanoivip::newrecharge-shop', ['items' => $items, 'svname' => $svname, 'role' => $role]);
        }
        catch (Exception $ex)
        {
            Log::error("NewFlow show shop error:" . $ex->getMessage());
            return view('hanoivip::newrecharge-failure', ['message' => __('hanoivip::newrecharge.shop-error')]);
        }
    }
    /**
     * Recharge game with new flow
     * + Select server&role
     * + Create order
     * + Pay for order
     * + Validate receipt
     * + Invoke game services
     * @param Request $request
     */
    public function recharge(Request $request)
    {
        $svname=$request->input('svname');
        $role=$request->input('role');
        $item=$request->input('item');
        try 
        {
            //Log::debug("Start new flow of recharging...");
            $order = IapFacade::order(Auth::user(), $svname, $role, $item);
            //Log::debug("Create new order " . $order);
            return PaymentFacade::pay($order, 'newrecharge.done');
        } 
        catch (Exception $ex) 
        {
            Log::error("NewFlow recharge exception: " . $ex->getMessage());
        }
    }
    public function rechargeDone(Request $request)
    {
        $order = $request->input('order');
        $receipt = $request->input('receipt');
        try 
        {
            $result = $this->rechargeService->onPaymentCallback(Auth::user()->getAuthIdentifier(), $order, $receipt);
            /** @var \Hanoivip\PaymentMethodContract\IPaymentResult $result */
            if ($result->isPending())
            {
                return view('hanoivip::newrecharge-result-pending', ['trans' => $receipt]);
            }
            elseif ($result->isFailure())
            {
                return view('hanoivip::newrecharge-failure', ['message' => $result->getDetail()]);
            }
            else
            {
                return view('hanoivip::newrecharge-result-success');
            }
        }
        catch (Exception $ex)
        {
            Log::error("NewFlow recharge callback exception: " . $ex->getMessage() . $ex->getTraceAsString());
            return view('hanoivip::newrecharge-failure', ['message' => __('hanoivip::newrecharge.callback-error')]);
        }
    }
    public function rechargeDone1(Request $request)
    {
        $order = $request->input('order');
        $receipt = $request->input('receipt');
        try
        {
            $result = PaymentFacade::query($receipt);
            /** @var \Hanoivip\PaymentMethodContract\IPaymentResult $result */
            if ($result->isPending())
            {
                dispatch(new CheckPendingReceipt($order, $receipt));
                return view('hanoivip::newrecharge-result-pending', ['trans' => $receipt]);
            }
            elseif ($result->isFailure())
            {
                return view('hanoivip::newrecharge-failure', ['message' => $result->getDetail()]);
            }
            else
            {
                // success
                $orderDetail = IapFacade::detail($order);
                $result = GameHelper::recharge(Auth::user()->getAuthIdentifier(),
                    $orderDetail['server'], $orderDetail['item'], $orderDetail['role']);
                if ($result === true)
                {
                    return view('hanoivip::newrecharge-result-success');
                }
                else
                {
                    return view('hanoivip::newrecharge-failure', ['message' => $result]);
                }
            }
        }
        catch (Exception $ex)
        {
            Log::error("NewFlow recharge callback exception: " . $ex->getMessage());
            return view('hanoivip::newrecharge-failure', ['message' => __('hanoivip::newrecharge.callback-error')]);
        }
    }
    public function query(Request $request)
    {
        try
        {
            $trans = $request->input("trans");
            $result = PaymentFacade::query($trans);
            /** @var \Hanoivip\PaymentMethodContract\IPaymentResult $result */
            if ($result->isPending())
            {
                return view('hanoivip::newrecharge-result-pending', ['trans' => $trans]);
            }
            elseif ($result->isFailure())
            {
                return view('hanoivip::newrecharge-failure', ['message' => $result->getDetail()]);
            }
            else
            {
                return view('hanoivip::newrecharge-result-success');
            }
        }
        catch (Exception $ex)
        {
            Log::error("NewFlow query trans exception: " . $ex->getMessage());
            return view('hanoivip::newrecharge-failure', ['message' => __('hanoivip::newrecharge.query-error')]);
        }
    }
    /**
     * List all purchases
     * @param Request $request
     */
    public function history(Request $request)
    {
        try
        {
            $page = 0;
            if ($request->has("page"))
                $page = $request->input("page");
            $result = $this->rechargeService->history(Auth::user()->getAuthIdentifier(), $page);
            if ($request->ajax())
            {
                return ['error' => 0, 'message' => 'success', 'data' => ['history' => $result[0], 'total_page' => $result[1]]];
            }
            else
            {
                return view('hanoivip::newrecharge-history', ['history' => $result[0], 'total_page' => $result[1]]);
            }
        }
        catch (Exception $ex)
        {
            Log::error("NewFlow query trans exception: " . $ex->getMessage());
            return view('hanoivip::newrecharge-failure', ['message' => __('hanoivip::newrecharge.query-error')]);
        }
    }
}