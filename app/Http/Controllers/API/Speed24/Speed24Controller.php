<?php

namespace App\Http\Controllers\API\Speed24;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Speed24Controller extends BaseController
{

    public function getStage(Request $request)
    {
        if($this->userid != $request->user_id){
            return response()->json([
                'code' => 1017,
                'succ' => 'false',
                'message' => '用户id有误',
            ]);
        }
        $vitality = DB::table('user')
            ->where('is_valid',1)
            ->where('id',$request->user_id)
            ->value('vitality');
        $stage_vitality = get_config('stage_main_enter');

        if ($vitality < $stage_vitality){
            return response()->json([
                'code' => 1025,
                'succ' => 'false',
                'message' => '活力值不够',
            ]);
        }
        DB::table('user')
            ->where('id',$request->user_id)
            ->decrement('vitality', $stage_vitality);
        //简单
        $easy = DB::table('speed24_stage')
            ->where('pow',0)
            ->where('num1','<=',10)
            ->where('num2','<=',10)
            ->where('num3','<=',10)
            ->where('num4','<=',10)
            ->whereRaw('num1!=num2')
            ->WhereRaw('num2!=num3')
            ->WhereRaw('num3!=num4')
            ->select('id','num1','num2','num3','num4','answer')
            ->get();
        //普通
        $normal = DB::table('speed24_stage')
            ->where('pow',0)
            ->where('num1','<=',13)
            ->where('num2','<=',13)
            ->where('num3','<=',13)
            ->where('num4','<=',13)
            ->whereRaw('num1!=num2')
            ->WhereRaw('num2!=num3')
            ->WhereRaw('num3!=num4')
            ->select('id','num1','num2','num3','num4','answer')
            ->get();
        //困难
        $hard = DB::table('speed24_stage')
            ->where('pow',0)
            ->whereRaw('(num1=num2 or num2=num3 or num3=num4)')
            ->select('id','num1','num2','num3','num4','answer')
            ->get();
        $easy_limit_time = 90;
        $normal_limit_time = 60;
        $hard_limit_time = 40;
        $hardest_limit_time = 15;
        return response()->json([
            'code' => 1000,
            'succ' => 'true',
            'message' => '获取成功',
            'data'=>[
                'limit_time'=>[
                    'lvl1'=>$easy_limit_time,
                    'lvl2'=>$normal_limit_time,
                    'lvl3'=>$hard_limit_time,
                    'lvl4'=>$hardest_limit_time,
                ],
                'stage'=>[
                    'stage1'=>$easy,
                    'stage2'=>$normal,
                    'stage3'=>$hard,
                ],
            ]
        ]);
    }

    public function finishStage(Request $request){
        $input = $request->json()->all();
        if ($request->user_id != $this->userid) {
            return response()->json([
                'code' => 1017,
                'succ' => 'false',
                'message' => "用户id参数有误"
            ]);
        }
        if (!in_array($input['level'],[1,2,3,4])){
            return response()->json([
                'code' => 1002,
                'succ' => 'false',
                'message' => '请求参数错误',
            ]);
        }
        $points = 0;
        switch ($input['level']){
            case 1:
                $points = 10;
                break;
            case 2:
                $points = 15;
                break;
            case 3:
                $points = 20;
                break;
            case 4:
                $points = 25;
                break;
        }
        $pass_info = [
            'user_id'=>$this->userid,
            'level'=>$input['level'],
            'stage_id'=>$input['stage_id'],
            'points'=>$points,
            'finish_time'=>$input['finish_time'],
        ];
        $res = DB::table('speed24_stage_pass_log')->insert($pass_info);
        DB::table('user')->where('id',$this->userid)->increment('points',$points);
        //资金变动记录
        $account['user_id'] = $this->userid;
        $account['behavior'] = 801;
        $account['points'] = $points;
        $account['detail'] = "速算24通关获得".$points."积分奖励";
        DB::table('user_account_log')->insert($account);
        if ($res){
            return response()->json([
                'code' => 1000,
                'succ' => 'true',
                'message' => '过关',
                'data' => [
                    'reward_points' => $points,
                ]
            ]);
        }else{
            return response()->json([
                'code' => 1200,
                'succ' => 'true',
                'message' => '记录失败'
            ]);
        }
    }
}