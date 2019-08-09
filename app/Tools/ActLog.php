<?php


namespace App\Tools;


use Illuminate\Support\Facades\DB;

class ActLog
{
    public static function addClickLog(array $request) {

    }

    public static function addMultipleLog(array $request){
        $time = time();
        foreach ($request as $k => &$v){
            $v['action_at'] = date('Y-m-d H:i:s',$time - (strtotime($v['request_time']) - strtotime($v['save_time'])));
            unset($v['request_time']);
            unset($v['save_time']);
            $v['attach'] = $v['attach'] ? json_encode($v['attach']) : '';
        }
        if (empty($request)){
            return false;
        }
        $res = DB::table('action_log')
            ->insert($request);
        return $res;
    }
}