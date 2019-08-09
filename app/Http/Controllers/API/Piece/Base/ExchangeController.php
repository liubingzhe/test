<?php


namespace App\Http\Controllers\API\Piece\Base;


use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExchangeController extends BaseController
{
    public function exchange(Request $request, $user_id)
    {
        $input = $request->json()->all();
        if (empty($input) || empty($input['exchange_ma'])) {
            return response([
                'code' => 1002,
                'succ' => 'false',
                'message' => '参数错误',
            ]);
        }
        if ($user_id != $this->userid) {
            return response()->json(['code' => 1017, 'succ' => 'false', 'message' => "用户id参数有误"]);
        }
        $ma = $input['exchange_ma'];
        //判断码是否存在
        $is_ma = DB::table('exchange')->where('is_valid', 1)->where('exchange_ma', $ma)->first();
        if (empty($is_ma)) {
            return response([
                'code' => 1002,
                'succ' => 'false',
                'message' => '兑换码错误',
            ]);
        }
//        是否该用户兑换过
        $is_dui = DB::table('user_exchange_log')->where('user_id',$user_id)->where('exchange_id',$is_ma->id)->first();
        if (!empty($is_dui)) {
            return response([
                'code' => 1002,
                'succ' => 'false',
                'message' => '兑换码已兑换',
            ]);
        }
        if ($is_ma->begin_time > date('Y-m-d H:i:s')) {
            return response([
                'code' => 1002,
                'succ' => 'false',
                'message' => '兑换时间未到',
            ]);
        }
        if ($is_ma->end_time < date('Y-m-d H:i:s')) {
            return response([
                'code' => 1002,
                'succ' => 'false',
                'message' => '兑换时间已过',
            ]);
        }
        $data = DB::transaction(function () use ($input, $is_ma, $user_id) {
            $gift_bag = new GiftController();
            $gift = $gift_bag->get_gift($user_id, $is_ma->gift_bag_id, 1, 503);
            if ($gift) {
                DB::table('user_exchange_log')->insert(['user_id'=>$user_id,'exchange_id'=>$is_ma->id]);
                $data = [
                    'include_diamonds' => $gift['include_diamonds'],
                    'include_points' => $gift['include_points'],
                    'tools' => $gift['tools'],
                ];
                return $data;
            } else {
                return false;
            }
        });
        if (is_null($data)) {
            return response([
                'code' => 1030,
                'succ' => 'false',
                'message' => '无可兑换礼包'
            ]);
        } elseif ($data === false) {
            return response([
                'code' => 1031,
                'succ' => 'false',
                'message' => '兑换礼包失败'
            ]);
        } elseif ($data) {
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '兑换成功',
                'data' => $data
            ]);
        }
    }
}