<?php

namespace Hanoivip\Game\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\Game\Recharge;
use Hanoivip\Game\Services\GameService;
use Hanoivip\Game\Services\ScheduleService;
use Hanoivip\Game\Services\ServerService;
use Hanoivip\Game\Services\UserLogService;
use Hanoivip\Payment\Facades\BalanceFacade;
use Illuminate\Auth\Authenticatable;

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
	    if ($request->expectsJson())
	    {
	        // react client
	        $roles = $this->games->queryRoles($user, $svname);
	        //return ['error' => 0, 'message' => 'success', 'data' => ['roles' => $roles]];
	        return ['roles' => $roles];
	    }
	    else
	    {
	        // brower client
	        $template = 'hanoivip::recharge-roles-partial';
	        if ($request->has('template'))
	            $template = $request->input('template');
            $roles = $this->games->queryRoles($user, $svname);
            return view($template, ['roles' => $roles]);
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
	
	// move from service to here
	private function _recharge($svname, $user, $package, $params, $balanceType = 0)
	{
	    $server = $this->servers->getServerByName($svname);
	    $recharge = Recharge::where('code', $package)->first();
	    
	    $uid = $user->getAuthIdentifier();
	    $package = $recharge->code;
	    $coin = $recharge->coin;
	    $cointype = $balanceType;//$recharge->coin_type;
	    if (!BalanceFacade::enough($uid, $coin, $cointype))
	    {
	        Log::error("Game user not enough coin");
	        return __('hanoivip.game::recharge.not-enough-coin');
	    }
	    $result = $this->games->recharge($svname, $user, $package, $params);
	    if ($result === true)
	    {
    	    $reason = "Recharge:" . $cointype . ":" . $coin . ":" . $server->title;
    	    if (!BalanceFacade::remove($uid, $coin, $reason, $cointype))
    	    {
    	        Log::warn("Game charge user's balance fail. User {$uid} coin {$coin} type {$cointype}");
    	        return __('hanoivip.game::recharge.remove-coin-fail');
    	    }
	    }
	    return $result;
	}
	
	/**
	 * https://laravelcollective.com/docs/5.4/html
	 * From laravel 5.0, Html Helper is not default included.
	 * 
	 * Enable thread-safe per user 
	 * 
	 * TODO: 
	 * + request validation
	 * + request lock (server side), middelware
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
	    $balanceType = 0;
	    if ($request->has('balance_type'))
	    {
	        $balanceType = $request->input('balance_type');
	    }
	    $user = Auth::user();
	    try 
	    { 	             
	        $result = $this->_recharge($svname, $user, $package, $params, $balanceType);
    	    if ($result === true)
    	    {
    	        if ($request->expectsJson())
    	        {
    	            return ['error' => 0, 'message' => __('hanoivip.game::recharge.success')]; 
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
	            return ['error' => 1, 'error_message' => __('hanoivip.game::recharge.exception')];
	        }
	        else
	        {
	            return response()->redirectToRoute('recharge.fail', ['error_message' => __('hanoivip.game::recharge.exception')]);
	        }
	    }
	}
	
	public function onRechargeSuccess(Request $request)
	{
	    return view('hanoivip::recharge-result-success', ['message' => __('hanoivip.game::recharge.success')]);
	}
	
	public function onRechargeFail(Request $request)
	{
	    $message = __('hanoivip.game::recharge.fail');
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
