<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 18:34
 */

namespace App\Http\Controllers\API\Piece\Base;


use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use App\User;
use Carbon\Carbon;

class GiftController extends BaseController
{
    public function index(Request $request)
    {

        $gift_bag = DB::table('gift_bag')
            ->select('id','include_diamonds','include_points', 'price_diamonds','price_points', 'gift_kind','detail')
            ->where('is_valid', 1)
            ->orderBy('id')
            ->get();
        foreach ($gift_bag as $key=>$value) {
            $value->tools = DB::table('gift_bag_tools')
                ->select('tools_id','tools_num')
                ->where('gift_bag_id', $value->id)
                ->orderBy('id')
                ->get();
        }
        if (!empty($gift_bag)) {
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '数据返回成功',
                'data' => $gift_bag
            ]);
        }
        else {
            return response([
                'code' => 1200,
                'succ' => 'false',
                'message' => '请求成功，但无数据。'
            ]);
        }
    }
    public function get_gift($user_id,$gift_bag_id,$gift_bag_num = 1,$behavior = 405,$total_signin_days = 0)
    {
        $gift_bag_points = DB::table('gift_bag')
            ->where('id',$gift_bag_id)
            ->where('is_valid',1)
            ->select('include_diamonds','include_points')
            ->first();
        if ($gift_bag_points->include_points || $gift_bag_points->include_diamonds) {
            DB::table('user')->where('id', $user_id)
                ->update([
                    'points' => DB::raw('points + ' . $gift_bag_points->include_points * $gift_bag_num),
                    'diamonds' => DB::raw('diamonds + ' . $gift_bag_points->include_diamonds * $gift_bag_num),
                ]);
            $data['include_points'] = $gift_bag_points->include_points * $gift_bag_num;
            $data['include_diamonds'] = $gift_bag_points->include_diamonds * $gift_bag_num;
            $day_datail = "";
            if ($total_signin_days) {
                $day_datail = date('n') . '月累计签到' . $total_signin_days . '天';
            }
            $datail = $day_datail . '获得礼包，包含' . $gift_bag_points->include_diamonds * $gift_bag_num . '元宝，' . $gift_bag_points->include_points * $gift_bag_num . '铜钱';
            DB::table('user_account_log')
                ->insert([
                    'user_id' => $user_id,
                    'behavior' => $behavior,
                    'diamonds' => $gift_bag_points->include_diamonds,
                    'points' => $gift_bag_points->include_points,
                    'detail' => $datail
                ]);
        }
        //            礼包中的道具
        $gift_bag_tools = DB::table('gift_bag_tools as g')
            ->join('tools as t', 'g.tools_id', 't.id')
            ->where('g.gift_bag_id', $gift_bag_id)
            ->select('g.tools_id as tool_id', 'g.tools_num as number', 't.tools_name as name')
            ->get();
        if (empty($gift_bag_tools)) {
            return null;
        }
        foreach ($gift_bag_tools as $k => $v) {
            DB::table('backpack')->updateOrInsert([
                'user_id' => $user_id,
                'tools_id' => $v->tool_id,
            ], [
                'tools_num' => DB::raw('tools_num + ' . $v->number * $gift_bag_num)
            ]);

            DB::table('user_tools_log')->insert([
                'user_id' => $user_id,
                'tools_id' => $v->tool_id,
                'tools_count' => $v->number * $gift_bag_num,
                'behavior' => $behavior,
                'gift_bag_id' => $gift_bag_id,
            ]);
            $gift_bag_tools[$k]->number = $v->number * $gift_bag_num;
        }
        //礼包领取记录
        $log_id = DB::table('user_gift_bag_log')->insertGetId([
            'user_id' => $user_id,
            'gift_bag_id' => $gift_bag_id,
            'gift_bag_count' => $gift_bag_num,
            'behavior' => $behavior,
            'year' => date('Y'),
            'month' => date('n'),
            'day' => !empty($total_signin_days) ? $total_signin_days : date('d'),
        ]);
        if (!empty($log_id)) {
            $data = [
                'include_diamonds' => $gift_bag_points->include_diamonds * $gift_bag_num ?? 0,
                'include_points' => $gift_bag_points->include_points * $gift_bag_num ?? 0,
                'tools' => $gift_bag_tools
            ];
        }
        if ($behavior == 405 || $behavior == 502 || $behavior == 503) {
            return $data;
        }

    }


    public function operatingGifts($user_id,$gift_id)
    {
        //判断用户是否是新用户 （是否领过礼包）
        $behavior = 502;
        $get_gift = DB::table('user_gift_bag_log')
            ->where(['user_id' => $user_id,'behavior' => $behavior])
            ->first();
        //判断用户传值user_id 是否和验证用户一致
        if ($user_id != $this->userid) {
            return response()->json(['code' => 1017,'succ' => 'false','message' => "用户id参数有误"]);
        }
        //获取新手礼包id
        $new_user_giftid = DB::table('gift_bag')
            ->where(['gift_kind'=>'3'])
            ->value('id');
        if ($gift_id != $new_user_giftid) {
            return response([
                'code' => 1037,
                'succ' => 'false',
                'message' => '领取礼包失败，请核实是否礼包'
            ]);
        }
        if (!empty($get_gift)) {
            return response([
                'code' => 1037,
                'succ' => 'false',
                'message' => '请勿重复领取新手礼包'
            ]);
        }
        $data = DB::transaction(function () use ($user_id, $gift_id,$behavior) {
            $data = $this->get_gift($user_id, $gift_id, 1, $behavior);
            return $data;
        });
        if ($data) {
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '领取礼包成功',
                'data' => $data
            ]);
        }
    }

    public function buyGiftBag(Request $request,$user_id)
    {
//        ,$gift_bag_id,$gift_bag_num,$pay,$pay_type
        if (!is_numeric($user_id) || $user_id != $this->userid){
            return response([
                'code' => 1017,
                'succ' => 'false',
                'message' => '用户id参数有误'
            ]);
        }
        $input = $request->json()->all();
        if (empty($input) || empty($input['gift_bag']) || empty($input['client_create_time']) || empty($input['pay_diamonds_points'])) {
            return response([
                'code' => 1002,
                'succ' => 'false',
                'message' => '参数错误',
            ]);
        }
        $price = 0;
        //支付类型
        if ($input['pay_diamonds_points'] == 1) {
            $zhanghu = User::find($user_id)->diamonds;
            $pay = 'price_diamonds';//元宝
            $user_pay = 'diamonds';//元宝
            $behavior = 102;
        } else {
            $zhanghu = User::find($user_id)->points;
            $pay = 'price_points';//金币
            $user_pay = 'points';//元宝
            $behavior = 302;
        }
        foreach ($input['gift_bag'] as $key => $value) {
            $gift_kind = DB::table('gift_bag')->where(['id' => $value['gift_bag_id'],'is_valid' => 1])->value('gift_kind');
            if ($gift_kind != 2) {
                return response([
                    'code' => 1002,
                    'succ' => 'false',
                    'message' => '参数错误,礼包不是售卖类型',
                ]);
            }
            $pay_price = DB::table('gift_bag')->where(['id' => $value['gift_bag_id'],'is_valid' => 1])->value($pay);
            $price += $pay_price * $value['gift_bag_num'];
        }
        //判断用户账户资金是否足够
        if ($zhanghu < $price) {
            return response()->json(['code'=>1019,'succ'=>'false','message'=>'用户资金不足']);
        }
        DB::beginTransaction(); //开启事务
        try {
            foreach ($input['gift_bag'] as $key => $value) {
                $this->get_gift($user_id,$value['gift_bag_id'],$value['gift_bag_num'],$behavior);

            }
            //元宝或金币减少
            DB::table('user')->where('id',$user_id)->decrement($user_pay, $price);
            //增加元宝变动记录
            DB::table('user_account_log')->insert(['user_id' => $user_id,'behavior' => $behavior,$user_pay => '-'.$price,'detail' => "使用金币购买礼包"]);
            DB::commit();
        } catch (ClientException $e) {
            DB::rollback();  //回滚
            return response()->json(['code'=>1500,'succ'=>'false','message'=>'服务繁忙，请稍后再试','data' => $e->getErrorMessage()]);
        }
        //支付类型
        if ($input['pay_diamonds_points'] == 1) {
            $pay_diamonds = $price;
            $pay_points = 0;
        } else {
            $pay_diamonds = 0;
            $pay_points = $price;
        }
        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '购买礼包成功',
            'data' => [
                'gift_bag' => $input['gift_bag'],
                'pay_diamonds' => $pay_diamonds,
                'pay_points' => $pay_points,
                'pay_time' => Carbon::now(),
            ]
        ]);
    }
}