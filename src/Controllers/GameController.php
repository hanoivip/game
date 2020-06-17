<?php

namespace Hanoivip\Game\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\Game\Recharge;
use Hanoivip\Game\Server;
use Hanoivip\Game\Services\GameService;
use Hanoivip\Game\Services\ScheduleService;
use Hanoivip\Game\Services\ServerService;
use Hanoivip\Game\Services\UserLogService;
use Hanoivip\PaymentClient\BalanceUtil;
use Hanoivip\UserBag\Services\UserBagService;
use Illuminate\Auth\Authenticatable;

use Hanoivip\Events\Game\UserRecharge;
use Hanoivip\Events\Game\UserPlay;
use Hanoivip\Events\Game\UserExchangeItem;

class GameController extends Controller
{
    protected $games;
    
    protected $servers;
    
    protected $schedule;
    
    protected $logs;
    
    protected $balance;
    
    protected $userBags;

    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        GameService $games, 
        ServerService $servers, 
        ScheduleService $schedule,
        UserLogService $logs, 
        BalanceUtil $balance, 
        UserBagService $userBags)
    {
        $this->games = $games;
        $this->servers = $servers;
        $this->schedule = $schedule;
        $this->logs = $logs;
        $this->balance = $balance;
        $this->userBags = $userBags;
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
		$params['servers'] = $this->servers->getAll();
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
    		event(new UserPlay($user->getAuthIdentifier(), $server->name));
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
	 * @param Server $selectedServer Name of selected server
	 */
	private function getRechargeViewData($user, $selectedServer = null)
	{
	    $uid = $user->getAuthIdentifier();
	    $servers = $this->servers->getAll();
        $packages = Recharge::all();
        $recents = $this->logs->getRecentEnter($uid);
        $balanceInfo = $this->balance->getInfo($uid);
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
            $data['selected'] = $selectedServer->name;
        return $data;
	}
	
	public function queryRoles(Request $request)
	{
	    $svname = $request->input('svname');
	    $selected = $this->servers->getServerByName($svname);
	    $user = Auth::user();
	    if ($request->ajax())
	    {
	        $roles = $this->games->queryRoles($user, $selected);
	        return ['roles' => $roles];
	    }
	    else
	    {
	        $viewData = $this->getRechargeViewData($user, $selected);
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
	 * @param string $svname
	 * @param string $package
	 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
	 */
	public function doRecharge(Request $request)
	{
	    $params = $request->all();
	    $svname = $request->input('svname');
	    $package = $request->input('package');
	    
	    $server = $this->servers->getServerByName($svname);
	    $user = Auth::user();
	    $lockKey = "Recharging" . $user->getAuthIdentifier();
	    $lock = Cache::lock($lockKey);
	    try 
	    {
	        $recharge = Recharge::where('code', $package)->first();
	        if (empty($recharge))
	        {
	            Log::error("GameController recharge package not exist");
	            if ($request->expectsJson())
	            {
	                return ['error' => 1, 'message' => __('hanoivip::recharge.fail')];    
	            }
	            else
	            {
	               return view('hanoivip::recharge-result', ['error_message' => __('hanoivip::recharge.fail')]);
	            }
	        }
	        else
	        {    	        
    	        if (!$lock->get())
    	        {
    	            if ($request->expectsJson())
    	                return ['error' => 1, 'message' => __('hanoivip::recharge.too-fast')]; 
    	            else
    	               return view('hanoivip::recharge-result', ['error_message' => __('hanoivip::recharge.too-fast')]);
    	        }
        	    $result = $this->games->recharge($server, $user, $recharge, $params);
        	    $lock->release();
        	    if ($result)
        	    {
        	        event(new UserRecharge($user->getAuthIdentifier(), 
        	            $recharge->coin_type, $recharge->coin, $server->name, $params));
        	        if ($request->expectsJson())
        	            return ['error' => 0, 'message' => __('hanoivip::recharge.success')]; 
        	        else
        	           return view('hanoivip::recharge-result', ['message' => __('hanoivip::recharge.success')]);
        	    }
        	    else 
        	    {
        	        if ($request->expectsJson())
        	        {
        	            return ['error' => 1, 'message' => __('hanoivip::recharge.fail')];
        	        }
        	        else
        	        {
        	            return view('hanoivip::recharge-result', ['error_message' => __('hanoivip::recharge.fail')]);
        	        }
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
	            return view('hanoivip::recharge-result', ['error_message' => __('hanoivip::recharge.exception')]);
	        }
	    }
	}
	
	public function bagList()
	{
	    $user = Auth::user();
	    try 
	    {
	        $data = $this->getBagViewData($user);
	        return view('hanoivip::bag', $data);
	    }
	    catch (Exception $ex)
	    {
	        Log::error("Game list user bag item ex:" . $ex->getMessage());
	        return view('hanoivip::bag', ['error' => __('hanoivip::bag.list.exception')]);
	    }
	}
	
	private function getBagViewData($user, $selectedServer = null)
	{
	    $bag = $this->userBags->getUserBag($user->getAuthIdentifier());
	    $items = $bag->list();
	    if (gettype($items) == "object")
	        $items = [ $items ];
	    $servers = $this->servers->getAll();
	    $roles = [];
	    if (!empty($selectedServer))
	        $roles = $this->games->queryRoles($user, $selectedServer);
        else if ($servers->isNotEmpty())
            $roles = $this->games->queryRoles($user, $servers->first());
        $data = ['items' => $items, 'servers' => $servers, 'roles' => $roles];
        if (!empty($selectedServer))
            $data['selected'] = $selectedServer->name;
        return $data;
	}
	
	public function bagRoles(Request $request)
	{
	    $svname = $request->input('svname');
	    $selected = $this->servers->getServerByName($svname);
	    $user = Auth::user();
	    if ($request->ajax())
	    {
	        $roles = $this->games->queryRoles($user, $selected);
	        return [$svname => $roles];
	    }
	    else
	    {
	        $viewData = $this->getBagViewData($user, $selected);
	        return view('hanoivip::bag', $viewData);
	    }
	}
	
	// TODO: move to some service
	private function doBagExchange($server, $user, $itemId, $itemCount, $params)
	{
        $uid = $user->getAuthIdentifier();
         $bag = $this->userBags->getUserBag($uid);
         if (empty($bag))
            throw new Exception("Game user bag can not be created!");
         if (!$bag->enough($itemId, $itemCount))
            return __('bag.exchange.not-enough');
         // Send Item to game
         if (!$this->games->sendItem($server, $user, $itemId, $itemCount, $params))
         {
             Log::error("Game request item exchange fail.");
             return false;
         }
         if (!$bag->subItem($itemId, $itemCount))
             Log::error("Game item exchanged but can not removed from bag!");
         //Log
         event(new UserExchangeItem($user, $server, $itemId, $itemCount, $params));
         return true; 
	}
	
	public function bagExchange(Request $request)
	{
	    $svname = $request->input('svname');
	    $itemId = $request->input('itemId');
	    $count = $request->input('count');
	    $params = $request->all();
	    $error = '';
	    $message = '';
	    $user = Auth::user();
	    $lock = "Recharging" . $user->getAuthIdentifier();
	    try
	    {
	        if (!Cache::lock($lock, 120)->get())
	        {
	            $error =  __('hanoivip::bag.exchange.too-fast');
	        }
	        else
	        {
    	        $server = $this->servers->getServerByName($svname);
    	        $result = $this->doBagExchange($server, $user, $itemId, $count, $params);
    	        if (gettype($result) == "string")
    	            $error = $result;
    	        else if ($result)
    	        {
    	            $message =  __('hanoivip::bag.exchange.success');
    	        }
    	        else 
    	            $error =  __('hanoivip::bag.exchange.fail');
	        }
	    }
	    catch (Exception $ex)
	    {
	       Log::error("Game exchange user item exception. Ex:" . $ex->getMessage());
	       $error = __('hanoivip::bag.exchange.exception');
	    }
	    finally
	    {
	        Cache::lock($lock)->release();
	    }
	    return view('hanoivip::bag-exchange-result', ['error' => $error, 'message' => $message]);
	}
}
