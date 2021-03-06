<?php

namespace Hanoivip\Game\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\Game\Recharge;
use Hanoivip\Game\Services\GameService;
use Hanoivip\Game\Services\ScheduleService;
use Hanoivip\Game\Services\ServerService;
use Hanoivip\Game\Services\UserLogService;
use Illuminate\Auth\Authenticatable;
use Hanoivip\GateClient\Facades\BalanceFacade;

class GameController extends Controller
{
    protected $games;
    
    protected $servers;
    
    protected $schedule;
    
    protected $logs;

    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        GameService $games, 
        ServerService $servers, 
        ScheduleService $schedule,
        UserLogService $logs)
    {
        $this->games = $games;
        $this->servers = $servers;
        $this->schedule = $schedule;
        $this->logs = $logs;
    }

    /**
     * Vào nhanh 1 game. 
     * Server sẽ tự động được chọn là server mới nhất.
     *  
     */
	public function quickplay()
	{
		$server = $this->servers->getRecommendServer();
		return $this->play($server->name);
	}
	
	/**
	 * Hiển thị danh sách server của game tương ứng.
	 */
	public function serverlist(Request $request)
	{
	    $params = [];
		$params['servers'] = $this->servers->getUserServer();
		$params['schedules'] = $this->schedule->getAll();
		$params['ranks'] = $this->games->rank();
		if (Auth::check())
		{
		    $params['recents'] = $this->logs->getRecentEnter(Auth::user()->getAuthIdentifier());
		}
		if ($request->ajax())
		    return $params;
		else
		    return view('hanoivip::serverlist', $params);
	}
	
	/**
	 * Vào 1 máy chủ của 1 game. 
	 * @param string $name Server Name
	 */
	public function play($svname)
	{
	    try 
	    {
    	    $server = $this->servers->getServerByName($svname);
    	    if (empty($server))
    	        throw new Exception("Cụm máy chủ không tồn tại.");
	        $user = Auth::user();
    		$url = $this->games->enter($server, $user);
    		if (empty($url))
    		    throw new Exception("Cụm máy chủ đang bảo trì.");
    	    if (strpos($url, 'message=') !== false)
    	        return view('hanoivip::playfail', [ 'message' => substr($url, strlen('message=')) ]);
            if (strpos($url, 'uri=') !== false)
            {
                return view('hanoivip::play', [ 'playuri' => substr($url, strlen('uri=')) ]);
            }
	    }
	    catch (Exception $ex)
	    {
	        Log::error('Game play game exception. Msg:' . $ex->getMessage());
	        return view('hanoivip::playfail', [ 'error_message' => "Lỗi xảy ra. Thử lại trước khi liên hệ GM hỗ trợ."]);
	    }
	}
	
	/**
	 * 
	 * @param Authenticatable $user
	 * @param string $selectedServer Name of selected server
	 */
	private function getRechargeViewData($user, $selectedServer = null)
	{
	    $uid = $user->getAuthIdentifier();
	    //$servers = $this->servers->getAll();
	    $servers = $this->servers->getUserServer();
        $packages = $this->games->getRechargePackages();
        $recents = $this->logs->getRecentEnter($uid);
        $balanceInfo = BalanceFacade::getInfo($uid);
        $roles = [];
        if (!empty($selectedServer))
            $roles = $this->games->queryRoles($user, $selectedServer);
        else if ($servers->isNotEmpty())
            $roles = $this->games->queryRoles($user, $servers->first());
        //Log::debug(print_r($roles, true));
        $data = [ 'servers' => $servers, 'packs' => $packages,
            'recents' => $recents, 'balances' => $balanceInfo, 
            'roles' => $roles];
        if (!empty($selectedServer))
            $data['selected'] = $selectedServer;
        return $data;
	}
	
	public function queryRoles(Request $request)
	{
	    $svname = $request->input('svname');
	    $user = Auth::user();
	    if ($request->ajax())
	    {
	        $roles = $this->games->queryRoles($user, $svname);
	        return ['roles' => $roles];
	    }
	    else
	    {
	        $viewData = $this->getRechargeViewData($user, $svname);
	        return view('hanoivip::recharge', $viewData);
	    }
	}
	
	/**
	 * Vào trang cho phép mua tiền tệ trong game.
	 */
	public function recharge()
	{
	    $viewData = $this->getRechargeViewData(Auth::user());
		return view('hanoivip::recharge', $viewData);
	}
	
	/**
	 * https://laravelcollective.com/docs/5.4/html
	 * From laravel 5.0, Html Helper is not default included.
	 * 
	 * Enable thread-safe per user 
	 * 
	 * TODO: 
	 * + request validation
	 * + request throttle
	 * 
	 * @param string $svname
	 * @param string $package
	 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
	 */
	public function doRecharge(Request $request)
	{
	    $params = $request->all();
	    $svname = $request->input('svname');
	    $package = $request->input('package');
	    
	    $user = Auth::user();
	    $lockKey = "Recharging" . $user->getAuthIdentifier();
	    $lock = Cache::lock($lockKey);
	    try 
	    { 	        
	        if (!$lock->get())
	        {
	            if ($request->expectsJson())
	                return ['error' => 1, 'message' => __('hanoivip::recharge.too-fast')]; 
	            else
	               return view('hanoivip::recharge-result', ['error_message' => __('hanoivip::recharge.too-fast')]);
	        }
	        $result = $this->games->recharge($svname, $user, $package, $params);
    	    $lock->release();
    	    if ($result === true)
    	    {
    	        if ($request->expectsJson())
    	        {
    	            return ['error' => 0, 'message' => __('hanoivip::recharge.success')]; 
    	        }
    	        else
    	        {
    	           return response()->redirectToRoute('recharge.success');
    	        }
    	    }
    	    else 
    	    {
    	        if ($request->expectsJson())
    	        {
    	            return ['error' => 1, 'message' => $result ];
    	        }
    	        else
    	        {
    	            return response()->redirectToRoute('recharge.fail', ['error_message' => $result ]);
    	        }
    	    }
	    }
	    catch (Exception $ex)
	    {
	        Log::error("Game recharge exception:" . $ex->getMessage());
	        if ($request->expectsJson())
	        {
	            return ['error' => 1, 'message' => __('hanoivip::recharge.exception')];
	        }
	        else
	        {
	            return response()->redirectToRoute('recharge.fail', ['error_message' => __('hanoivip::recharge.exception')]);
	        }
	    }
	}
	
	public function onRechargeSuccess(Request $request)
	{
	    return view('hanoivip::recharge-result-success');
	}
	
	public function onRechargeFail(Request $request)
	{
	    $message = __('hanoivip::recharge.fail');
	    if ($request->has('error_message'))
	        $message = $request->input('error_message');
	    return view('hanoivip::recharge-result-fail', ['error_message' => $message]);
	}

	public function allRoles(Request $request)
	{
	    $user = Auth::user();
        $roles = $this->games->allRole($user);
        return ['error'=>0,'message'=>'success','data'=> $roles];
	}
}
