<?php

namespace Hanoivip\Game\Services;

use Hanoivip\Game\UserLog;
use Illuminate\Support\Facades\DB;

class UserLogService
{
    public function logEnter($uid, $server)
    {
        $log = new UserLog();
        $log->user_id = $uid;
        $log->server_name = $server->name;
        $log->server_title = $server->title;
        $log->action = "enter";
        $log->save();
    }

    public function logRecharge($uid, $server, $package, $order, $rid = 0)
    {
        $log = new UserLog();
        $log->user_id = $uid;
        $log->server_name = $server->name;
        $log->server_title = $server->title;
        $log->action = "recharge";
        $log->action_params = json_encode(['package' => $package, 'order' => $order, 'receiver' => $rid]);
        $log->save();
    }
    
    public function getRecentEnter($uid)
    {
        $recents = DB::table('user_logs')
                        ->select('server_name', 'server_title')
                        ->where('user_id', $uid)
                        ->groupBy('server_name', 'server_title')
                        ->get();
        return $recents;
    }
    
    public function logTransfer($oldId, $newId)
    {
        $log = new UserLog();
        $log->user_id = $newId;
        $log->action = "transfer";
        $log->action_params = $oldId;
        $log->save();
    }
}