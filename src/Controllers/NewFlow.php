<?php

namespace Hanoivip\Game\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Imdhemy\Purchases\Facades\Product;
use Exception;
use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Hanoivip\IapContract\Facades\IapFacade;
use Hanoivip\Game\Facades\GameHelper;
use Hanoivip\Game\Services\RechargeService;
use Hanoivip\Game\Jobs\GoogleSlowCard;
use Hanoivip\Game\Jobs\CheckPendingReceipt;
use Hanoivip\Game\GoogleReceipt;

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
    
    private function isAppReceipt($receipt)
    {
        try
        {
            $json = json_decode($receipt, true);
            if (!empty($json))
            {
                return isset($json['trans']) && isset($json['detail']) && isset($json['isPending']) && isset($json['isFailure']) && isset($json['isSuccess']);
            }
            return false;
        }
        catch (Exception $ex)
        {
            Log::error('NewFlow check app receipt: ' . $ex->getMessage());
            return false;
        }
    }
    
    public function rechargeDone(Request $request)
    {
        $order = $request->input('order');
        $receipt = $request->input('receipt');
        if ($this->isGoogleReceipt($receipt))
            return $this->onGoogleAppPurchased($request);
        if ($this->isAppReceipt($receipt))
        {
            $receiptArr = json_decode($receipt, true);
            $receipt = $receiptArr['trans'];
        }
        try 
        {
            $result = $this->rechargeService->onPaymentCallback(Auth::user()->getAuthIdentifier(), $order, $receipt);
            if (gettype($result) == 'string')
            {
                if ($request->ajax())
                {
                    return ['error' => 1, 'message' => $result, 'data' => []];
                }
                else
                {
                    return view('hanoivip::newrecharge-failure', ['message' => $result]);
                }
            }
            else 
            {
                /** @var \Hanoivip\PaymentMethodContract\IPaymentResult $result */
                if ($result->isPending())
                {
                    dispatch(new CheckPendingReceipt(Auth::user()->getAuthIdentifier(), $order, $receipt))->delay(60);
                    if ($request->ajax())
                    {
                        return ['error' => 0, 'message' => 'pending', 'data' => ['trans' => $receipt]];
                    }
                    else
                    {
                        return view('hanoivip::newrecharge-result-pending', ['trans' => $receipt]);
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
                        return view('hanoivip::newrecharge-failure', ['message' => $result->getDetail()]);
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
                        return view('hanoivip::newrecharge-result-success');
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
                return view('hanoivip::newrecharge-failure', ['message' => __('hanoivip::newrecharge.callback-error')]);
            }
        }
    }
    
    public function query(Request $request)
    {
        try
        {
            $trans = $request->input("trans");
            $result = $this->rechargeService->query(Auth::user()->getAuthIdentifier(), $trans);
            if (gettype($result) == 'string')
            {
                return view('hanoivip::newrecharge-failure', ['message' => $result]);
            }
            else 
            {
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
    
    private function isGoogleReceipt($receipt)
    {
        try
        {
            $json = json_decode($receipt, true);
            if (isset($json['transactionReceipt']))
            {
                $receiptCli = json_decode($json['transactionReceipt'], true);
                return isset($receiptCli['productId']) && isset($receiptCli['packageName']) && isset($receiptCli['purchaseToken']);
            }
            return false;
        }
        catch (Exception $ex)
        {
            Log::error('NewFlow check google receipt: ' . $ex->getMessage());
            return false;
        }
    }
    
    public function onGoogleAppPurchased(Request $request)
    {
        $order = $request->input('order');
        $raw = $request->input('receipt');
        if (empty($raw))
        {
            return ['error' => 1, 'message' => 'Empty receipt', 'data' => []];
        }
        $json = json_decode($raw, true);
        $str = $json['transactionReceipt'];
        $receiptCli = json_decode($str, true);
        $productId = $receiptCli['productId'];
        $packageName = $receiptCli['packageName'];
        $token = $receiptCli['purchaseToken'];
        try
        {
            $oldLog = GoogleReceipt::where('purchase_token', $token)->get();
            if ($oldLog->isNotEmpty())
            {
                return ['error' => 2, 'message' => 'Duplicated callback', 'data' => []];
            }
            // TODO: temporary fixes
            $log = new GoogleReceipt();
            $log->product_id = $productId;
            $log->purchase_token = $token;
            $log->save();
            // validate this payments
            $receipt = Product::googlePlay()->id($productId)->token($token)->get();
            if (!empty($receipt) && $receipt->getPurchaseState()->isPending())
            {
                Log::debug(">> slow card processing..");
                dispatch(new GoogleSlowCard($order, $productId, $token));
                return ['error' => 0, 'message' => 'Delay payment', 'data' => []];
            }
            if (!empty($receipt) && $receipt->getPurchaseState()->isPurchased())
            {
                Log::debug(">> valid payment..");
                $orderDetail = IapFacade::detail($order);
                $result = GameHelper::recharge($orderDetail['user'],
                    $orderDetail['server'],
                    $orderDetail['item'],
                    $orderDetail['role']);
                $log->state = $result ? 1 : 2;
                $log->save();
                return ['error' => 0, 'message' => 'Success.', 'data' => []];
            }
            return ['error' => 2, 'message' => 'Invalid payment', 'data' => []];
        }
        catch (Exception $ex)
        {
            Log::error("Order process receipt error. Delay processing..");
            dispatch(new GoogleSlowCard($order, $productId, $token));
            return ['error' => 0, 'message' => 'We need more time to process this payment', 'data' => []];
        }
        
    }
}