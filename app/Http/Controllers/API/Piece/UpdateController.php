<?php

namespace App\Http\Controllers\API\Piece;


use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\DB;

class UpdateController extends Controller
{
    //更新 用户活力值  定时任务
    public function index($user_id)
    {
        //获取通知信息
        $list = DB::table('broadcast')
            ->where(['is_valid'=>1,'type'=>3])
            ->orderBy('created_at','desc')
            ->limit(5)
            ->get(['id','info']);
        $vitality = User::find($user_id)->vitality;
        if ($vitality < 30 ) {
            DB::table('user')->increment('vitality',1);
        }
        if ($list->isEmpty()) {
            return response([
                'code' => 200,
                'succ' => 'false',
                'msg' => '通告信息为空，无数据',
                'data' => [
                    'user_vitality' => $vitality,
                ]
            ], 200);
        }
        return response([
            'code' => 200,
            'succ' => 'true',
            'msg' => '获取通告信息',
            'data' => [
                'user_vitality' => $vitality,
                'boardcast' => $list,
            ]
        ], 200);
    }
}