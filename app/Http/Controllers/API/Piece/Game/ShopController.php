<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 13:59
 */

namespace App\Http\Controllers\API\Piece\Game;


use Illuminate\Support\Facades\Config;
use App\Http\Controllers\BaseController;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Validator;

class ShopController extends BaseController
{
    /**
     *道具商店
     *
     *
     */
    public function getToolsInfo()
    {
        //tools_name,rejuvenation,need_money
        $dict_tools_type = DB::table('dict_tools_type')->get(['tools_type as type_id','detail as type_name']);
        $tools = DB::table('tools')
            ->where('is_valid','=','1')
            ->get(['id','tools_type as type_id','tools_name as name','price_points','price_diamonds','icon_path as icon_url','detail']);
        if ($tools->isEmpty()) {
            return response([
                'code' => 1200,
                'succ' => 'true',
                'message' => '请求成功，但无数据',
            ]);
        }

        foreach ($tools as &$val) {
            if (!strstr($val->icon_url,'http')) {
                $val->icon_url = Config::get('constants.PATH') .$val->icon_url;
            }
        }
        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '数据返回成功',
            'data' => [
                'tools_types' => $dict_tools_type,
                'tools_list' => $tools
            ]
        ]);
    }

    /**
     *购买道具
     * 1.参数  $tools_id $tools_num
     * 判断用户钻石是否购买、
     *
     */
    public function getTools(Request $request,$user_id)
    {
        if ($user_id != $this->userid) {
            return response()->json(['code' => 1017,'succ' => 'false','message' => "用户id参数有误"]);
        }
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
        $input = $request->json()->all();
        if (empty($input) || empty($input['tools']) || empty($input['pay_diamonds_points']) || empty($input['client_create_time'])) {
            return response([
                'code' => 1002,
                'succ' => 'false',
                'message' => '参数错误',
            ]);
        }
        foreach ($input['tools'] as $k=>$v) {
            if ($v['tools_num'] == 0) {
                unset($input['tools'][$k]);
            }
        }
        if (empty($input['tools'])) {
            return response([
                'code' => 1002,
                'succ' => 'false',
                'message' => '参数错误,道具数量不能为0',
            ]);
        }
        $price = 0;
        //支付类型
        if ($input['pay_diamonds_points'] == 1) {
            $zhanghu = User::find($user_id)->diamonds;
            $pay = 'price_diamonds';//元宝
            $user_pay = 'diamonds';//元宝
        } else {
            $zhanghu = User::find($user_id)->points;
            $pay = 'price_points';//金币
            $user_pay = 'points';//元宝
        }
        foreach ($input['tools'] as $key => $value) {
            $pay_price = DB::table('tools')->where(['id' => $value['tools_id'],'is_valid' => 1])->value($pay);
            $price += $pay_price * $value['tools_num'];
        }
        //支付类型
        if ($input['pay_diamonds_points'] == 1) {
            $pay_diamonds = $price;
            $pay_points = 0;
        } else {
            $pay_diamonds = 0;
            $pay_points = $price;
        }
        //判断用户账户资金是否足够

        if ($zhanghu < $price) {
            return response()->json(['code'=>1019,'succ'=>'false','message'=>'用户资金不足']);
        }
        DB::beginTransaction(); //开启事务
        try {

            $this->updateTools($input['tools'],$user_id,$input['pay_diamonds_points']);
            //元宝或金币减少
            DB::table('user')->where('id',$user_id)->decrement($user_pay, $price);
            //增加元宝变动记录
            DB::table('user_account_log')->insert(['user_id' => $user_id,'behavior' => 101,$user_pay => '-'.$price,'detail' => "使用金币购买道具"]);
            DB::commit();
        } catch (ClientException $e) {
            DB::rollback();  //回滚
            return response()->json(['code'=>1500,'succ'=>'false','message'=>'服务繁忙，请稍后再试','data' => $e->getErrorMessage()]);
        }
        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '购买道具成功',
            'data' => [
                'tools' => $input['tools'],
                'pay_diamonds' => $pay_diamonds,
                'pay_points' => $pay_points,
                'pay_time' => Carbon::now(),
            ]
        ]);

    }

    function updateTools($buy_info,$user_id,$pay_diamonds_points)
    {
        foreach ($buy_info as $key => $value) {
            //根据道具id 获取道具价格
            $tools = Db::table('tools')->find($value['tools_id']);

            DB::table('backpack')->updateOrInsert([
                'user_id' => $user_id,
                'tools_id' => $value['tools_id'],
            ], [
                'tools_num' => DB::raw('tools_num + ' . $value['tools_num'])
            ]);
            //增加道具增加记录
            if ($pay_diamonds_points == 1) {
                $log['behavior'] = 101;
                $log['cost_diamonds'] = $tools->price_diamonds * $value['tools_num'];
            } else {
                $log['behavior'] = 301;
                $log['cost_points'] =$tools->price_points * $value['tools_num'];
            }
            $log['user_id'] = $user_id;
            $log['tools_id'] = $value['tools_id'];
            $log['tools_count'] = $value['tools_num'];

            DB::table('user_tools_log')->insert($log);
        }

    }

    /**
     *用户使用某种道具.每次只能使用1种.
     * 1.参数  $tools_id $tools_num
     * 判断用户钻石是否购买、
     *
     */
    public function useTool($user_id,$tool_id,$tool_num = 1)
    {
        $validator = Validator::make(['user_id' => $user_id,'tool_id' => $tool_id,'tool_num' => $tool_num], [
            'user_id' =>'required|integer|regex:/^[0-9]+/',
            'tool_id' =>'required|integer|regex:/^[0-9]+/',
            'tool_num' =>'required|integer|regex:/^[0-9]+/',
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
        //判断道具是否充足
        $t_count = db::table('backpack')
            ->where(['user_id'=>$user_id,'tools_id'=>$tool_id])
            ->value('tools_num');

        if ($t_count <= 0 || $t_count < $tool_num) {
            return response([
                'code' => 1024,
                'succ' => 'false',
                'message' => '道具数量不足请在商店中购买',
            ]);
        }
        //获取道具相关信息
        $tool_info = $this->getToolInfo($tool_id);

        $tool = [];
        $user = User::find($user_id);
        foreach ($tool_info as &$val) {
            $tool = $val;
            //增加活力的道具
            if ($val->tools_type == 1) {
                if ($user->vitality == 30) {
                    return response([
                        'code' => 1024,
                        'succ' => 'false',
                        'message' => '活力值已满',
                    ]);
                }
                //活力值不能大于30
                $vitality = $user->vitality + ($val->add_vitality * $tool_num);
                if ($vitality > 30 ) {
                    $vitality = 30;
                }
                $user->vitality = $vitality;
                $user->diamonds = $user->diamonds + ($val->add_diamonds * $tool_num);
                $user->points = $user->points + ($val->add_points * $tool_num);
                $user->save();
            }
        }
        $log['user_id'] = $user_id;
        $log['tools_id'] = $tool_id;
        $log['tools_count'] = $tool_num;
        $log['behavior'] = 401;

        $bool = db::table('user_tools_log')
            ->insert($log);
        $up_back = db::table('backpack')->where(['user_id' => $user_id,'tools_id' => $tool_id])->decrement('tools_num',$tool_num);
        if ($bool && $up_back) {
            return response([
                'code' => 1000,
                'succ' => 'true',
                'msg' => '使用道具成功',
                'data' => [
                    'add_vitality' => $tool->add_vitality * $tool_num,
                    'add_diamonds' => $tool->add_diamonds * $tool_num,
                    'add_points' => $tool->add_points * $tool_num,
                ]
            ]);
        }
        //使用道具  记录道具消耗  背包道具减少  道具使用记录
    }

    //获取道具相关信息
    public function getToolInfo($tool_id)
    {

        $info = DB::table('tools')->where(['id' => $tool_id,'is_valid' => 1])->get();
        return $info;
    }
}