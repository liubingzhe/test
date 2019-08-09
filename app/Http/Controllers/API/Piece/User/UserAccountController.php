<?php
namespace App\Http\Controllers\API\Piece\User;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;

class UserAccountController extends BaseController
{


    /**
     * 查询元宝记录 api
     *
     * @return \Illuminate\Http\Response
     */
    public function diamonds_logs($user_id,$month)
    {
        $validator = Validator::make(['user_id' => $user_id,'month' => $month], [
            'user_id' =>'required|integer|regex:/^[0-9]+/',
            'month' =>'required|integer|digits:6',
        ], [
            'regex' => ':attribute 格式不正确',
            'required' => ':attribute 不能为空',
            'integer' => ':attribute 必须是数字',
            'digits' => ':attribute 不能超过字数限制',

        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 1002,'succ' => 'false','message' => $validator->errors()]);
        }
        //判断用户传值user_id 是否和验证用户一致
        if ($user_id != $this->userid) {
            return response()->json(['code' => 1017,'succ' => 'false','message' => "用户id参数有误"]);
        }
        //将月份分割
        $month = str_split($month,4);
        $timeStar = $month[0] ."-" .$month[1] ."-0";
        $timeEnd = $month[0] ."-" .$month[1] ."-31";
        $list = DB::table('user_account_log as ua')
            ->leftJoin('dict_user_behavior as du','ua.behavior','=','du.behavior')
            ->where('ua.user_id',$user_id)
            ->where('ua.diamonds','!=',0)
            ->where('ua.created_at','>',$timeStar)
            ->where('ua.created_at','<',$timeEnd)
            ->get(['ua.id as diamonds_log_id','ua.behavior','diamonds','ua.created_at','ua.detail']);
        foreach ($list as $key => $value) {
            //元宝数为正数是查找支付记录 并找到支付金额  后续有支付补充
            if ($value->diamonds > 0) {
                $value->pay_RMB = 10;
            }
        }
        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '数据返回成功',
            'data' => $list
            ]);
    }
    /**
     * 查询铜钱记录 api
     *
     * @return \Illuminate\Http\Response
     */
    public function points_logs($user_id,$month)
    {
        $validator = Validator::make(['user_id' => $user_id,'month' => $month], [
            'user_id' =>'required|integer|regex:/^[0-9]+/',
            'month' =>'required|integer|digits:6',
        ], [
            'regex' => ':attribute 格式不正确',
            'required' => ':attribute 不能为空',
            'integer' => ':attribute 必须是数字',
            'digits' => ':attribute 不能超过字数限制',

        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 1002,'succ' => 'false','message' => $validator->errors()]);
        }
        //判断用户传值user_id 是否和验证用户一致
        if ($user_id != $this->userid) {
            return response()->json(['code' => 1017,'succ' => 'false','message' => "用户id参数有误"]);
        }
         //将月份分割
        $month = str_split($month,4);
        $timeStar = $month[0] ."-" .$month[1] ."-0";
        $timeEnd = $month[0] ."-" .$month[1] ."-31";
        $list = DB::table('user_account_log as ua')
            ->leftJoin('dict_user_behavior as du','ua.behavior','=','du.behavior')
            ->where('ua.user_id',$user_id)
            ->where('ua.points','!=',0)
            ->where('ua.created_at','>',$timeStar)
            ->where('ua.created_at','<',$timeEnd)
            ->get(['ua.id as diamonds_log_id','ua.behavior','points','ua.created_at','ua.detail']);
        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '数据返回成功',
            'data' => $list
        ]);
    }

}
