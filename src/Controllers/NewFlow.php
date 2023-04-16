<?php

namespace Hanoivip\Game\Controllers;

use App\Http\Controllers\Controller;
use Hanoivip\Game\Jobs\CheckPendingReceipt;
use Hanoivip\Game\Services\RechargeService;
use Hanoivip\IapContract\Facades\IapFacade;
use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\Game\Services\ServerService;

class NewFlow extends Controller
{   
    private $rechargeService;
    
    private $servers;
    
    public function __construct(
        RechargeService $recharge,
        ServerService $servers)
    {
        $this->rechargeService = $recharge;
        $this->servers = $servers;
    }
    /**
     * Ajax support
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function start(Request $request)
    {
        $client = "";
        if ($request->has('client'))
        {
            $client = $request->input('client');
        }
        $servers = $this->servers->getUserServer();
        return view('hanoivip::newrecharge-ajax', ['client' => $client, 'servers' => $servers]);
    }
    /**
     * Old react supported
     * @deprecated
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startWizard(Request $request)
    {
        if ($request->has('client'))
        {
            session(['client' => $request->input('client')]);
        }
        return redirect()->route('wizard.role', ['next' => 'newrecharge.shop']);
    }
    
    public function showShop(Request $request)
    {
        $svname=$request->input('svname');
        $role=$request->input('role');
        $client=session()->get('client', 'app');
        $template = 'hanoivip::newrecharge-shop';
        if ($request->has('template'))
        {
            $template = $request->input('template');
        }
        try
        {
            // show items
            $items = IapFacade::items($client);
            if ($request->expectsJson())
            {
                return [
                    'error' => 0,
                    'message' => 'success',
                    'data' => [['items' => $items, 'svname' => $svname, 'role' => $role, 'client' => $client]]
                ];
            }
            else {
                return view($template, ['items' => $items, 'svname' => $svname, 'role' => $role, 'client' => $client]);
            }
        }
        catch (Exception $ex)
        {
            Log::error("NewFlow show shop error:" . $ex->getMessage());
            return view('hanoivip::newrecharge-failure', ['error' => __('hanoivip.game::newrecharge.shop-error')]);
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
        $client=$request->input('client');
        try 
        {
            //Log::debug("Start new flow of recharging...");
            $order = IapFacade::order(Auth::user(), $svname, $role, $item, $client);
            //Log::debug("Create new order " . $order);
            return PaymentFacade::pay($order, 'newrecharge.done', $client);
        } 
        catch (Exception $ex) 
        {
            Log::error("NewFlow recharge exception: " . $ex->getMessage());
            return view('hanoivip::newrecharge-failure', ['error' => __('hanoivip.game::newrecharge.recharge-error')]);
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
                    return view('hanoivip::newrecharge-failure', ['error' => $result]);
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
                        return ['error' => 0, 'message' => 'pending', 'data' => ['trans' => $receipt, 'detail' => $result->getDetail()]];
                    }
                    else
                    {
                        return view('hanoivip::newrecharge-result-pending', ['trans' => $receipt, 'detail' => $result->getDetail()]);
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
                        return view('hanoivip::newrecharge-failure', ['error' => $result->getDetail()]);
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
                return ['error' => 3, 'message' => __('hanoivip.game::newrecharge.callback-error'), 'data' => []];
            }
            else
            {
                return view('hanoivip::newrecharge-failure', ['error' => __('hanoivip.game::newrecharge.callback-error')]);
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
                return view('hanoivip::newrecharge-failure', ['error' => $result]);
            }
            else 
            {
                /** @var \Hanoivip\PaymentMethodContract\IPaymentResult $result */
                if ($result->isPending())
                {
                    return view('hanoivip::newrecharge-result-pending', ['trans' => $trans, 'detail' => $result->getDetail()]);
                }
                elseif ($result->isFailure())
                {
                    return view('hanoivip::newrecharge-failure', ['error' => $result->getDetail()]);
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
            return view('hanoivip::newrecharge-failure', ['error' => __('hanoivip.game::newrecharge.query-error')]);
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
            return view('hanoivip::newrecharge-failure', ['error' => __('hanoivip.game::newrecharge.query-error')]);
        }
    }

}