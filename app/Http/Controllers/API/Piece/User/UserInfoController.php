<?php

namespace App\Http\Controllers\API\Piece\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;
use App\User;

class UserInfoController extends BaseController
{

    public $successStatus = 200;

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserInfo($user_id)
    {
        $validator = Validator::make(['user_id' => $user_id], [
            'user_id' => 'required|integer|regex:/^[0-9]+/',
        ], [
            'regex' => ':attribute 格式不正确',
            'required' => ':attribute 不能为空',
            'integer' => ':attribute 必须是数字',

        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 1002, 'succ' => 'false', 'message' => $validator->errors()]);
        }
        //判断用户传值user_id 是否和验证用户一致
        if ($user_id != $this->userid) {
            return response()->json(['code' => 1017, 'succ' => 'false', 'message' => "用户id参数有误"]);
        }
        $user = User::where(['id' => $user_id, 'is_valid' => 1])->first(['name as nickname', 'avatar', 'vitality', 'diamonds', 'points', 'stars']);
        if (!empty($user)) {
            if (!strstr($user->avatar,'http')) {
                $user->avatar = Config::get('constants.PATH') . $user->avatar;
            }

        }
        return response()->json([
            'code' => 1000,
            'succ' => 'true',
            'message' => '请求成功',
            'data' => [
                'base_info' => $user,
            ]
        ]);
        return response()->json(['success' => $user], $this->successStatus);
    }

    /**
     * 排行榜 api
     *
     * @return \Illuminate\Http\Response
     */
    public function ranking($user_id)
    {
        $validator = Validator::make(['user_id' => $user_id], [
            'user_id' => 'required|integer|regex:/^[0-9]+/',
        ], [
            'regex' => ':attribute 格式不正确',
            'required' => ':attribute 不能为空',
            'integer' => ':attribute 必须是数字',

        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 1002, 'succ' => 'false', 'message' => $validator->errors()]);
        }
        //判断用户传值user_id 是否和验证用户一致
        if ($user_id != $this->userid) {
            return response()->json(['code' => 1017, 'succ' => 'false', 'message' => "用户id参数有误"]);
        }
        //        积分排行榜
        $intergral = DB::table('user')->orderBy('points', 'desc')->orderBy('id', 'asc')->take(10)->get(['name as nickname', 'avatar', 'points']);
        //        星星排行榜
        $stat_num = DB::table('user')->orderBy('stars', 'desc')->orderBy('id', 'asc')->take(10)->get(['name as nickname', 'avatar', 'stars']);
        foreach ($intergral as &$value) {
            if (!strstr($value->avatar,'http')) {
                $value->avatar = Config::get('constants.PATH') . $value->avatar;
            }
        }
        foreach ($stat_num as &$value) {
            if (!strstr($value->avatar,'http')) {
                $value->avatar = Config::get('constants.PATH') . $value->avatar;
            }
        }
        //        用户自身排行
        $self_intergral    = DB::select("select u.rowNo as my_points_ranking from ( select user.*,(@rowNum:=@rowNum+1) as rowNo from user,(select (@rowNum :=0) ) b order by user.points desc,user.id asc) u where u.id = '$user_id'");
        $self_stat_ranking = DB::select("select u.rowNo as my_stars_ranking from ( select user.*,(@rowNum:=@rowNum+1) as rowNo from user,(select (@rowNum :=0) ) b order by user.stars desc,user.id asc ) u where u.id = '$user_id'");


        if (empty($self_intergral)) {
            $self_intergral = 0;
        }
        else {
            $self_intergral = $self_intergral[0]->my_points_ranking;
        }
        if (empty($self_stat_ranking)) {
            $self_stat_ranking = 0;
        }
        else {
            $self_stat_ranking = $self_stat_ranking[0]->my_stars_ranking;
        }
        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '请求成功',
            'data' => [
                'points_top10' => $intergral,
                'stars_top10' => $stat_num,
                'my_points_ranking' => $self_intergral,
                'my_stars_ranking' => $self_stat_ranking,
            ]
        ]);
    }

    /**
     * 更改用户昵称 api
     *$name 用户昵称
     * @return \Illuminate\Http\Response
     */
    public function updatename(Request $request, $user_id)
    {
        $validator = Validator::make(['user_id' => $user_id], [
            'user_id' => 'required|integer|regex:/^[0-9]+/',
        ], [
            'regex' => ':attribute 格式不正确',
            'required' => ':attribute 不能为空',
            'integer' => ':attribute 必须是数字',

        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 1002, 'succ' => 'false', 'message' => $validator->errors()]);
        }
        //判断用户传值user_id 是否和验证用户一致
        if ($user_id != $this->userid) {
            return response()->json(['code' => 1017, 'succ' => 'false', 'message' => "用户id参数有误"]);
        }
        $name = $request->json()->all();

        $name = $name['new_nickname'];
        if (empty($name)) {
            return response([
                'code' => 1012,
                'succ' => 'false',
                'message' => '接口参数无效',
            ]);
        }
        if (!filterString($name)){

            return response()->json([
                'code' => 1002,
                'succ' => 'false',
                'message' => '昵称中含有敏感词，请更改'
            ]);
        }
        $user = User::find($user_id);
        //判断更新次数是否超过三次
        /*if ($user->up_name >= 3) {
            return response([
                'code' => 1016,
                'succ' => 'false',
                'message' => '修改昵称失败，超过三次修改',
            ]);
        }*/
        $user->name = $name;
        //        $user->up_name = $user->up_name+1;
        $bool = $user->save();
        DB::table('name_change_log')
            ->insert([
                'user_id'=>$user_id,
                'name'=>$name,
                'type'=>2,
                'description'=>'客户端修改',
            ]);
        if ($bool) {
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '用户名更新成功',
                'nickname' => $name,
            ]);
        }

    }

    /**
     * 获取用户账户余额
     *$name 元宝和铜钱
     * @return \Illuminate\Http\Response
     */
    public function getAccount($user_id)
    {
        $validator = Validator::make(['user_id' => $user_id], [
            'user_id' => 'required|integer|regex:/^[0-9]+/',
        ], [
            'regex' => ':attribute 格式不正确',
            'required' => ':attribute 不能为空',
            'integer' => ':attribute 必须是数字',

        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 1002, 'succ' => 'false', 'message' => $validator->errors()]);
        }
        //判断用户传值user_id 是否和验证用户一致
        if ($user_id != $this->userid) {
            return response()->json(['code' => 1017,'succ' => 'false','message' => "用户id参数有误"]);
        }
        $account = User::find($user_id, ['points', 'diamonds']);

        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '数据返回成功',
            'data' => $account,
        ]);
    }

    /**
     * 获取随机昵称
     * @return \Illuminate\Http\Response
     */
    public function getRandName($last_time)
    {
        $updata_time = DB::table('admin_operation_log')
            ->where('path', 'admin/getRandName')
            ->orderBy('id', 'desc')
            ->value('updated_at');
        $updata_time = strtotime($updata_time) ? strtotime($updata_time) : 0;
        if ($updata_time <= $last_time) {
            return response([
                'code' => 1200,
                'succ' => 'true',
                'message' => '无需更新',
            ]);
        }
        $first_name  = DB::table('rand_names')
            ->where('type', 1)
            ->get('value');
        $second_name = DB::table('rand_names')
            ->where('type', 2)
            ->get('value');

        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '数据返回成功',
            'data' => [
                'names_updata_time' => $updata_time,
                'first_name' => $first_name,
                'second_name' => $second_name,
            ],
        ]);

    }

    public function checkRandName($mobile)
    {
        $res       = [
            'mobile' => $mobile,
        ];
        $messages  = [
            [
                'regex' => ':attribute 格式不正确',
                'required' => ':attribute 必须填写',
            ],
        ];
        $validator = Validator::make($res, [
            'mobile' => 'required|regex:/^1[0-9]{10}$/',
        ], $messages);
        if ($validator->fails()) {
            return response()->json(['code' => 1002, 'succ' => 'false', 'message' => $validator->errors()]);
        }
        //检查手机号是否为已注册
        $user_id = DB::table('user')
            ->where('mobile', $mobile)
            ->value('id');
        if ($user_id) {
            return response([
                'code' => 1200,
                'succ' => 'false',
                'message' => '用户已注册，不能随机昵称',
            ]);
        }else{
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '用户未注册',
            ]);
        }
    }
}
