<?php

namespace Hanoivip\Game\Services;

use Hanoivip\Game\Server;
use Illuminate\Support\Facades\DB;

class ServerService
{
    /**
     * Lấy 1 server khuyến cáo, có thể:
     * + Server mới nhất
     * + Server đông nhất
     * + Server có điểm KC cao nhất
     */
    public function getRecommendServer()
    {
        //$server = Server::where('is_recommend', true)
        $server = DB::table('Servers')
                        ->orderBy('id', 'desc')
                        ->first();
        return $server;
    }
    
    public function getAll()
    {
        $servers = DB::table('Servers')
                        ->orderBy('id', 'desc')
                        ->get();
        return $servers;
    }
    
    public function getServerByName($svname)
    {
        $server = Server::where('name', $svname)->first();
        return $server;
    }
}