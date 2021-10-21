<?php

namespace Hanoivip\Game\Controllers;

use Hanoivip\Game\Facades\GameHelper;
use Hanoivip\Game\Jobs\GoogleSlowCard;
use Hanoivip\IapContract\Facades\IapFacade;
use Hanoivip\Iap\Models\ClientIap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Imdhemy\Purchases\Facades\Product;
use Exception;
use App\SeaHelper;
use Hanoivip\Game\GoogleReceipt;
use Hanoivip\Game\Facades\ServerFacade;

class GoogleController extends Controller
{
    
    public function recallUI(Request $request)
    {
        $servers = ServerFacade::getUserServer();
        return view('google-recall', ['servers' => $servers]);
    }

    public function recall(Request $request)
    {
        $svname = $request->input('svname');
        $role = $request->input('role');
        $productId = $request->input('product_id');
        $token = $request->input('purchase_token');
        // validate this payments
        try
        {
            $log = GoogleReceipt::where('purchase_token', $token)->get();
            if ($log->isNotEmpty())
            {
                $log = $log->first();
                if ($log->state == 2)// send diamond fail
                {
                    // send coin
                    $orderDetail = IapFacade::detail($log->order);
                    $result = GameHelper::recharge($orderDetail['user'],
                        $orderDetail['server'],
                        $orderDetail['item'],
                        $orderDetail['role']);
                    $log->state = $result ? 1 : 2;
                    return view('google-recall-result', ['message' => 'Recall success (resend diamonds)']);
                }
                else
                {
                    return view('google-recall-result', ['message' => 'Already taken diamonds. No need to recall.']);
                }
            }
            $receipt = Product::googlePlay()->id($productId)->token($token)->get();
            $log = new GoogleReceipt();
            $log->product_id = $productId;
            $log->purchase_token = $token;
            $log->save();
            Log::debug("Receipt:" . $receipt);
            if (!empty($receipt) && $receipt->getPurchaseState()->isPending())
            {
    			Log::debug(">> recall slow card processing..");
                dispatch(new GoogleSlowCard($productId, $token));
                return view('google-recall-result', ['message' => 'Just some minutes, google is processing..!']);
            }
            if (!empty($receipt) && $receipt->getPurchaseState()->isPurchased())
            {
    			Log::debug(">> recall valid payment..");
    			$result = GameHelper::recharge(0, $svname, $productId, $role);
    			$log->state = $result ? 1 : 2;
    			if ($result)
					return view('google-recall-result', ['message' => 'Recall success.']);
				else
					return view('google-recall-result', ['message' => 'Recall fail. Try again after some minutes.']);
            }
            return view('google-recall-result', ['message' => 'Invalid payment. Google reject it.']);
        }
        catch (Exception $ex)
        {
            return view('google-recall-result', ['message' => 'Invalid product id and/or purchase token']);
        }
    }
    
    public function tokenUI(Request $request)
    {
        return view('google-token');
    }
    
    public function token(Request $request)
    {
        $productId = $request->input('product_id');
        $token = $request->input('purchase_token');
        try
        {
            $receipt = Product::googlePlay()->id($productId)->token($token)->get();
            if (!empty($receipt))
            {
                return view('google-token-detail', 
                    ['state' => $receipt->getPurchaseState(),
                        'order' => $receipt->getDeveloperPayload()
                    ]);
            }
            else
            {
                return view('google-token-detail', ['message' => 'Ko hop le']);
            }
        }
        catch (Exception $ex)
        {
            return view('google-token-detail', ['message' => 'Product & token ko hop le']);
        }
    }
    
}
