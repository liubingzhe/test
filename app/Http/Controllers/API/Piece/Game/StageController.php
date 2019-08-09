<?php
namespace App\Http\Controllers\API\Piece\Game;

use function AlibabaCloud\Client\json;
use App\Http\Controllers\BaseController;
use App\Models\Stage;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Validator;
/**
 * 关卡信息
 */
class StageController extends BaseController
{
    /**
     * 关卡设置 获取关卡基本信息 api
     *
     * @return \Illuminate\Http\Response
     */
    public function getStage()
    {
        //关卡名称  图片 id 是否通过  几星通关
        $stage_list = Stage::all(['id','name','icon_url','images_id','error_max','time_len_max','detail']);
        if ($stage_list->isEmpty()) {
            return response()->json([
                'code' => 1200,
                'succ' => 'true',
                'message' => '数据为空',
            ]);
        }
        foreach ($stage_list as &$val) {
            if (!strstr($val['icon_url'],'http')) {
                $val['icon_url'] = Config::get('constants.PATH') . $val['icon_url'];
            }
        }
        return response()->json([
            'code' => 1000,
            'succ' => 'true',
            'message' => '请求成功',
            'data' => [
                'stages' => $stage_list
            ]
        ]);
    }
    /**
     * 用户通关情况 每个关卡通关情况  和总体概况通关情况 api
     *
     * @return \Illuminate\Http\Response
     */
    public function passed($user_id)
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
        //用户通过关信息
        $stage_id = DB::table('stage_pass_log')
            ->where('user_id',$user_id)
            ->where('type',1)
            ->orderByRaw('stage_id desc')
            ->limit(1)
            ->value('stage_id');
        $stage_id = $stage_id ?? 0;
        $stage_list = Stage::where('id','<=',$stage_id)->get(['id','name']);
        foreach ($stage_list as $key => $value) {
            //关卡id
            $tool_id = $value->id;
            $value->stars_max = 0;
            $value->points_max = 0;
            //获取通关情况(如关卡有多次玩的情况 取最高的一次通关星数)
            $pass_star = DB::table('stage_pass_log')
                ->where(['user_id'=>$user_id,'stage_id'=>$tool_id,'type'=>1])
                ->orderBy('star_num','desc')
                ->limit(1)
                ->value('star_num');

            $points_max = DB::table('stage_pass_log')
                ->where(['user_id'=>$user_id,'stage_id'=>$tool_id,'type'=>1])
                ->orderBy('point','desc')
                ->limit(1)
                ->value('point');

            if (!empty($pass_star)) {
                $value->stars_max = $pass_star;//最高祥云
                $value->points_max = $points_max;//
            }
        }
//        三星通关数
        $star = DB::table('stage_pass_log')
            ->distinct('stage_id')
            ->where(['user_id' => $user_id,'star_num' => 3,'type'=>1])
            ->get('stage_id');
//        关卡信息
        /*$stage_count = DB::table('stage')->count();
        $stage_star = $stage_count * 3;*/
        $stage_info = DB::table('stage_pass_log')
            ->distinct('stage_id')
            ->where(['user_id' => $user_id,'type'=>1])
            ->get('stage_id');
        $stage_star = 0;
        foreach ($stage_info as $k=>$v) {
            $a = DB::table('stage_pass_log')
                ->where(['user_id' => $user_id,'stage_id' => $v->stage_id,'type'=>1])
                ->orderBy('star_num','desc')
                ->limit(1)
                ->value('star_num');
            $stage_star += $a;
        }
        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '请求成功',
            'data' => [
                'user_passed_stages' => $stage_list,
    //          'stage_id' => $stage_id,//最高关
                'total_3star_stages' => count($star),//三星通关数
    //          stage_count' => $stage_count,//总关数
                'total_stars' => $stage_star,//总星数（关卡）
            ]
        ]);
    }
    /**
     * 进入游戏 消耗五点活力值
     *
     * @return \Illuminate\Http\Response
     */
    public function inStage($stage_id,$user_id)
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
        $user = User::find($user_id);
        $vitality = $user->vitality;
        //关卡消耗活力值
        $stage = Stage::find($stage_id);
        if (empty($stage)) {
            return response()->json(['code' => 1036,'succ' => 'false','message' => "关卡id错误"]);
        }
        $constellation_num = 0;
        $constellation_id = 0;
        $constellation_name = "";
        $constellation_description = "";
        if ($stage->stage_type == 1) {
            $constellation = DB::table('constellation')->where('id',$stage->constellation_id)->first();
            if (!empty($constellation)) {
                $constellation_num = $constellation->num;
                $constellation_id = $stage->constellation_id;
                $constellation_name = $constellation->name;
                $constellation_description = $constellation->description;
            }
        }else {
            $count = DB::table('constellation')->count();
            $constellation_id = get_rand(1,$count);
            $constellation = DB::table('constellation')->where('id',$constellation_id)->first();
            $constellation_num = $constellation->num;
            $constellation_name = $constellation->name;
            $constellation_description = $constellation->description;
        }
        $stage_vitality = get_config('stage_main_enter');
        //判断活力值是否足够
        if ($vitality < $stage_vitality) {
            return response()->json([
                'code' => 1025,
                'succ' => 'false',
                'message' => '活力值不够',
            ]);
        }

        //进入游戏消耗五点活力值
        DB::table('user')->where('id',$user_id)->decrement('vitality', $stage_vitality);
        $code = md5(make_password(8));
        $ins['user_id'] = $user_id;
        $ins['stage_id'] = $stage_id;
        $ins['type'] = 0;
        $ins['security_code'] = $code;
        DB::table('stage_pass_log')->insert($ins);
        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '进入关卡',
            'data' => [
                'reduced_vitality'=> get_config('reduced_vitality'),
                'reduced_diamonds'=> get_config('reduced_diamonds'),
                'reduced_points'=> get_config('reduced_points'),
                'security_code'=> $code,
                'constellation_id'=> $constellation_id,
                'constellation_num'=> $constellation_num,
                'constellation_name'=> $constellation_name,
                'constellation_description'=> $constellation_description,
                'stage_max_time'=> $constellation_num * get_config('preset_time') * get_config('multiple_time'),
                'images_id'=> $stage->images_id
            ]

        ]);



    }
    /**
     * 游戏结束  结算 游戏结束分为  未完成退出  完成退出  需要记录两种情况数据
     *记录通关信息  几星 积分  用时  碎片数  更新用户  积分 星星
     *
     * @return \Illuminate\Http\Response
     */

    public function stageInfo(Request $request,$stage_id,$user_id)
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
        //参数
        $input = $request->json()->all();
        if (empty($input) || empty($input['time_len']) || empty($input['part_count']) || empty($input['security_code'])) {
            return response([
                'code' => 1002,
                'succ' => 'false',
                'message' => '参数错误',
            ]);
        }
        $pass_info = Db::table('stage_pass_log')->where(['stage_id'=>$stage_id,'user_id'=>$user_id,'type'=>0])->orderBy('created_at','desc')->first();
        if (empty($pass_info) || $pass_info->security_code != $input['security_code'] ) {
            return response([
                'code' => 1035,
                'succ' => 'false',
                'message' => '关卡验证码错误',
            ]);
        }
        //判断用户传值user_id 是否和验证用户一致
        if ($user_id != $this->userid) {
            return response()->json(['code' => 1017,'succ' => 'false','message' => "用户id参数有误"]);
        }
        /*$standard_time = $input['part_count'] * get_config('preset_time');//标准完成时间
        $stage_len = $standard_time * 3;//关卡最长时长
        if ($input['time_len'] > $stage_len) {
            return response()->json(['code' => 1034,'succ' => 'false','message' => "通关超时"]);
        }*/
        $ins['part_error'] = $input['errors'];
        $ins['time_seconds'] = $input['time_len'];
        $ins['part_count'] = $input['part_count'];
        $reward_points = $this->getPoints($input['part_count'],$input['errors'],$input['time_len']);
        $ins['point'] = $reward_points;
        $stars = $this->getStats($input['part_count'],$input['errors'],$input['time_len']);
        $vitality = 0;
        switch ($stars) {
            case $stars <= 0.8 :
                $stars = 1;
                $vitality = 1;
                break;
            case $stars <= 1.1 :
                $stars = 2;
                $vitality = 3;
                break;
            case $stars <= 1.6 :
                $stars = 3;
                $vitality = 5;
                break;
        }
        $ins['star_num'] = $stars;
        $ins['type'] = 1;
        //资金变动记录
        $account['user_id'] = $user_id;
        $account['behavior'] = 201;

        $account['points'] = $reward_points;
        DB::beginTransaction(); //开启事务
        //判断用户是否玩过该关卡
        $this->playGame($user_id,$stage_id,$stars);

        DB::table('stage_pass_log')->where('id',$pass_info->id)->update($ins);
        $account['correlate_id'] = $pass_info->id;
        $account['detail'] = "通关获得积分奖励".$reward_points;

        //更新用户积分和星星记录
        $user_point = DB::table('user')->where('id',$user_id)->increment('points',$reward_points);

        //更新活力值
        DB::table('user')->where('id',$user_id)->increment('vitality',$vitality);
        $user_vitality = DB::table('user')->where('id',$user_id)->value('vitality');

        //活力值超过30 更新成30
        if ($user_vitality > 30) {
            DB::table('user')->where('id',$user_id)->update(['vitality'=>30]);
        }
        $user_account = DB::table('user_account_log')->insert($account);
        if ($pass_info->id && $user_point && $user_account) {
            DB::commit();  //提交
            return response([
                'code' => 1000,
                'succ' => 'true',
                'msg' => '通关成功',
                'data' => [
                    'reward_vitality' => $vitality,
                    'reward_diamonds' => 0,
                    'reward_points' => $reward_points,
                    'stars' => $stars,
                ]
            ]);
        } else {
        return response([
            'code' => 1027,
            'succ' => 'false',
            'message' => '请求失败，数据更新失败',
        ]);
        }
    }
    /**   计算通关星级
     * $part_count 关卡碎片数
    *$errors 错误次数
    *time_len  完成时间
     * 星级系数=标准完成时间/（实际完成时间+错误增量时间）
    * */
    public function getStats($part_count,$errors,$time_len)
    {
        $standard_time = $part_count * get_config('preset_time');//标准完成时间
        $errors_time = $standard_time * $errors * get_config('error_increment');//关卡错误增量时间
        $stars = $standard_time/($time_len + $errors_time);
        $star_ceiling = get_config('star_ceiling');
        $stars = ($stars > $star_ceiling) ? $star_ceiling : $stars;//上限值

        return $stars;

    }
    /**   计算通关积分铜钱数
     * $part_count 关卡碎片数
     *$errors 错误次数
     *time_len  完成时间
     * 星级系数=标准完成时间/（实际完成时间+错误增量时间）
     * */
    public function getPoints($part_count,$errors,$time_len)
    {
        $stars = $this->getStats($part_count,$errors,$time_len);
        $stage_point = get_config('standard_score') * $part_count;
        $points = $stars * $stage_point;
        return ceil($points);
    }
    /**   判断用户是否玩过该关卡
     * 1：未玩过 增加星
     * 2：玩过 如果星一样则不做变化 星高于以前的记录做改变
     * */
    public function playGame($user_id,$stage_id,$star)
    {
        $star_num = Db::table('stage_pass_log')->where(['stage_id'=>$stage_id,'user_id'=>$user_id,'type'=>1])->orderBy('star_num','desc')->limit(1)->value('star_num');
        if (empty($star_num)) {
            DB::table('user')->where('id',$user_id)->increment('stars',$star);
        } else {
            if ($star > $star_num) {
                $sta = $star - $star_num;
                DB::table('user')->where('id',$user_id)->increment('stars',$sta);
            }
        }
    }
}
