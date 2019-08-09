<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 18:02
 */

namespace App\Http\Controllers\API\Piece;


use App\Http\Controllers\BaseController;
use App\User;
use Illuminate\Support\Facades\DB;
use Validator;

class BroadCastController extends BaseController
{
    //获取最新通知  只显示最新五条记录  客户端会每十分钟请求一次接口
    public function broadCast($user_id)
    {
        $validator = Validator::make(['user_id' => $user_id], [
            'user_id' =>'required|integer|regex:/^[0-9]+/',
        ], [
            'regex' => ':attribute 格式不正确',
            'required' => ':attribute 不能为空',
            'integer' => ':attribute 必须是数字',

        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 1002,'succ' => 'false','message' => $validator->errors()]);
        }
        //判断用户传值user_id 是否和验证用户一致
        if ($user_id != $this->userid) {
            return response()->json(['code' => 1017,'succ' => 'false','message' => "用户id参数有误"]);
        }
        //获取通知信息
        $list = DB::table('broadcast')
            ->where(['is_valid'=>1,'type'=>3])
            ->orderBy('created_at','desc')
            ->limit(5)
            ->get();
        $vitality = User::find($user_id)->vitality;
        if ($list->isEmpty()) {
            return response([
                'code' => 1200,
                'succ' => 'true',
                'msg' => '请求成功，但无数据',
                'data' => [
                    'user_vitality' => $vitality,
                ]
            ]);
        }
        return response([
            'code' => 1000,
            'succ' => 'true',
            'msg' => '请求成功',
            'data' => [
                'user_vitality' => $vitality,
                'server_time' => time(),
                'boardcast' => $list,
            ]
        ]);
    }
}