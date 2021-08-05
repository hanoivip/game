<?php

namespace App\Http\Controllers;

use Hanoivip\Game\Jobs\GoogleSlowCard;
use Hanoivip\Iap\Models\ClientIap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Imdhemy\Purchases\Facades\Product;
use Exception;

class GoogleController extends Controller
{
    
    public function __construct()
    {
    }
    
    public function recallUI(Request $request)
    {
        return view('google-recall');
    }

    public function recall(Request $request)
    {
        $productId = $request->input('product_id');
        $token = $request->input('purchase_token');
        // validate this payments
        try
        {
            $receipt = Product::googlePlay()->id($productId)->token($token)->get();
            if (!empty($receipt) && $receipt->getPurchaseState()->isPending())
            {
    			Log::debug(">> recall slow card processing..");
                dispatch(new GoogleSlowCard($productId, $token));
                return view('google-recall-result', ['message' => 'The tra tre sau vai phut!']);
            }
            if (!empty($receipt) && $receipt->getPurchaseState()->isPurchased())
            {
    			Log::debug(">> recall valid payment..");
    			$order = $receipt->getDeveloperPayload();
    			$item = ClientIap::where('merchant_id', $productId)->first();
                if ($this->helper->paymentNotify($order, $item->price))
					return view('google-recall-result', ['message' => 'Thanh cong']);
				else
					return view('google-recall-result', ['message' => 'Da tra hoac ko tim thay order']);
            }
            return view('google-recall-result', ['message' => 'Khong hop le!']);
        }
        catch (Exception $ex)
        {
            return view('google-recall-result', ['message' => 'Product & token ko hop le']);
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
