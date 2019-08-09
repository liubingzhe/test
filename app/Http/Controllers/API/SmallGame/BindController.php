<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 17:27
 */

namespace App\Http\Controllers\API\SmallGame;


use App\Http\Controllers\BaseController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BindController extends BaseController {

    //绑定手机号码
    public function bindmobile(Request $request)
    {
        $mobile = $request->get('mobile');
        $user_id = $this->userid;
        if (empty($user_id)) {
            return response([
                'code' => 1002,
                'succ' => 'false',
                'message' => '未获取到用户id',

            ]);
        } else {
            $user = DB::table('user')->where('mobile',$mobile)->where('is_valid',1)->first();
            if (empty($user)) {
                DB::table('user')->where('id',$user_id)->update(['mobile' => $mobile]);
            } else {
                //存在老用户  更新微信用户id 更新token  更新所产生的的log的表用户
                DB::transaction(function () use ($user,$user_id){
                    //背包
                    //背包详情
                    $backpack = DB::table('backpack')->where('user_id',$user_id)->get();
                    foreach ($backpack as $k=>$v) {
                        $tools_id = $v->tools_id;
                        //判断老用户是否有这条记录
                        $old_log = DB::table('backpack')->where(['user_id'=>$user->id,'tools_id'=>$tools_id])->first();
                        if (empty($old_log)) {
                            DB::table('backpack')->where('user_id',$user_id)->where('tools_id',$tools_id)->update(['user_id',$user->id]);
                        } else {
                            //老用户道具数相加
                            DB::table('backpack')->where(['user_id'=>$user->id,'tools_id'=>$tools_id])->increment('tools_num',$v->tools_num);
                            //删除新用户背包数据
                            DB::table('backpack')->where(['user_id'=>$user_id,'tools_id'=>$tools_id])->delete();
                        }
                    }
                    //设备
                    DB::table('device')->Where('user_id',$user_id)->update(['user_id'=>$user->id]);
                    //更改用户名

                    DB::table('name_change_log')->Where('user_id',$user_id)->update(['user_id'=>$user->id]);
                    //签到
                    DB::table('signin')->Where('user_id',$user_id)->update(['user_id'=>$user->id]);
                    //通关关卡
                    DB::table('stage_pass_log')->Where('user_id',$user_id)->update(['user_id'=>$user->id]);
                    //资金
                    DB::table('user_account_log')->Where('user_id',$user_id)->update(['user_id'=>$user->id]);
                    //礼包记录
                    DB::table('user_gift_bag_log')->Where('user_id',$user_id)->update(['user_id'=>$user->id]);
                    //成语接龙记录
                    DB::table('user_idiom_log')->Where('user_id',$user_id)->update(['user_id'=>$user->id]);
                    //道具使用记录
                    DB::table('user_tools_log')->Where('user_id',$user_id)->update(['user_id'=>$user->id]);
                    //数算24
                    DB::table('speed24_stage_pass_log')->Where('user_id',$user_id)->update(['user_id'=>$user->id]);
                    DB::table('action_log')->Where('user_id',$user_id)->update(['user_id'=>$user->id]);
                    DB::table('wechat')->Where('user_id',$user_id)->update(['user_id'=>$user->id]);
                    DB::table('user')->Where('id',$user_id)->update(['is_valid'=>0]);
                });
                $user_id = $user->id;
            }
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '手机号绑定成功',
                'data' => [
                    'token' => User::find($user_id)->createToken('MyApp')->accessToken,
                    'user_id' => $user_id
                ]
            ]);
        }
    }
}