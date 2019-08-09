<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/19
 * Time: 9:20
 */

namespace App\Http\Controllers\API\Piece\Game;


use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;
class BackpackController extends BaseController
{
    //背包详情
    public function index($user_id)
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
        $back_list = DB::table('backpack')
            ->leftJoin('tools', 'backpack.tools_id', '=', 'tools.id')
            ->where('backpack.user_id',$user_id)
            ->where('backpack.tools_num','!=',0)
            ->get(['tools.id','tools_num as count']);
        if ($back_list->isEmpty()) {
            return response([
                'code' => 1200,
                'succ' => 'true',
                'message' => '请求成功，但无数据',
            ]);
        }
        /*foreach ($back_list as &$value) {
            $value->icon_path = Config::get('constants.PATH').$value->icon_path;
        }*/
        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '请求成功，返回数据',
            'data'=> [
                'tools' => $back_list
            ]
        ]);
    }
}
