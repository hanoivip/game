<?php

namespace Hanoivip\Game\Controllers;

use App\Http\Controllers\Controller;
use Hanoivip\Game\Services\RechargeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class Admin extends Controller
{   
    private $rechargeService;
    
    public function __construct(RechargeService $recharge)
    {
        $this->rechargeService = $recharge;
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
    
}