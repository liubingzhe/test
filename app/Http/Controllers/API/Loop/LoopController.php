<?php

namespace App\Http\Controllers\API\Loop;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function Psy\bin;

class LoopController extends BaseController
{
    public function getLoopImg(){
        $loop_img_info = DB::table('loop_img')
            ->select('name','path','ignore','endpoint')
            ->get();
        return response()->json([
            'code' => 1000,
            'succ' => 'true',
            'message' => '获取成功',
            'data'=>$loop_img_info
        ]);
    }
    public function getLoopStage(Request $request){

        if (!is_numeric($request->num)){
            return response()->json([
                'code' => 1002,
                'succ' => 'false',
                'message' => '请求参数错误',
            ]);
        }
        if($this->userid != $request->user_id){
            return response()->json([
                'code' => 1017,
                'succ' => 'false',
                'message' => '用户id有误',
            ]);
        }
        $loop_stage = DB::table('loop_stage')
            ->where('num',$request->num)
            ->where('direction',$request->direction)
            ->select('id as stage_id','num','name','direction','info')
            ->first();
        if (empty($loop_stage)){
            return response()->json([
                'code' => 1200,
                'succ' => 'false',
                'message' => '请求成功，但无数据'
            ]);
        }else{
            if ($request->start == 1){
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
            }
            $loop_stage->info = json_decode($loop_stage->info);
            return response()->json([
                'code' => 1000,
                'succ' => 'true',
                'message' => '获取成功',
                'data'=>$loop_stage
            ]);
        }

    }
    public function submitLoop(Request $request){
        $input = $request->json()->all();
        if (!in_array($input['direction'],[1,2])){
            return response()->json([
                'code' => 1300,
                'succ' => 'false',
                'message' => '需选择横竖版',
            ]);
        }
        $id = DB::table('loop_stage')
            ->where('info',json_encode($input['info']))
            ->where('direction',$input['direction'])
            ->first('id');
        if ($id){
            return response()->json([
                'code' => 1300,
                'succ' => 'false',
                'message' => '已添加过相同关卡',
            ]);
        }
        $data = $this->formateData($input['info']);
        $res = $this->checkLoop($data);

        if (!$res){
            return response()->json([
                'code' => 1300,
                'succ' => 'false',
                'message' => '关卡有错，添加失败',
            ]);
        }else{
            $input['info'] = json_encode($input['info']);
            DB::table('loop_stage')->updateOrInsert(['num'=>$input['num'],'direction'=>$input['direction']],$input);
            return response()->json([
                'code' => 1000,
                'succ' => 'true',
                'message' => '添加成功',
            ]);
        }

    }
    private function formateData($data=[]){
        if (empty($data) || !is_array($data)){
            return false;
        }
        $loop_img_info = DB::table('loop_img')
            ->select('name','endpoint')
            ->get();
        $img_info = [];
        foreach ($loop_img_info as $k => $v){
            $img_info[$v->name] = $v->endpoint;
        }
        $new_data = [];
        foreach ($data as $x => $xv){
            foreach ($data[$x] as $y => $yv){
                $bin = $img_info[$yv['name']]*pow(2,$yv['rotate'])%0b1111;
                $new_data[$x][$y] = $bin == 0 ? $img_info[$yv['name']] : $bin;
            }
        }
        return $new_data;
    }
    private function checkLoop($data=[]){
        if (empty($data) || !is_array($data)){
            return false;
        }
        foreach ($data as $x => $xv){  //行
            foreach ($data[$x] as $y => $yv){  //列
                if ($yv & 0b1){
                    if (!isset($data[$x-1][$y]) || !($data[$x-1][$y] & 0b100)){
                        return false;
                    }
                }
                if ($yv & 0b10){
                    if (!isset($data[$x][$y+1]) || !($data[$x][$y+1] & 0b1000)){
                        return false;
                    }
                }
                if ($yv & 0b100){
                    if (!isset($data[$x+1][$y]) || !($data[$x+1][$y] & 0b1)){
                        return false;
                    }
                }
                if ($yv & 0b1000){
                    if (!isset($data[$x][$y-1]) || !($data[$x][$y-1] & 0b10)){
                        return false;
                    }
                }
            }
        }
        return true;
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
        if (!is_numeric($request->stage_id)){
            return response()->json([
                'code' => 1002,
                'succ' => 'false',
                'message' => '请求参数错误',
            ]);
        }
        $info = $this->formateData($input['info']);
        $info_res = $this->checkLoop($info);
        if (!$info_res){
            return response()->json([
                'code' => 1300,
                'succ' => 'false',
                'message' => '关卡有错',
            ]);
        }
        $points = 20;
        $pass_info = [
            'user_id'=>$this->userid,
            'stage_id'=>$request->stage_id,
            'info'=>json_encode($input['info']),
            'points'=>$points,
            'finish_time'=>$input['finish_time'],
        ];
        $res = DB::table('loop_stage_pass_log')->insert($pass_info);
        DB::table('user')->where('id',$this->userid)->increment('points',$points);
        //资金变动记录
        $account['user_id'] = $this->userid;
        $account['behavior'] = 701;
        $account['points'] = 20;
        $account['detail'] = "神器卯榫通关获得".$points."积分奖励";
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
    public function changeStage(){
        $loop_stages = DB::table('loop_stage')
            ->where('direction',1)
            ->select('num','name','direction','info')
            ->get();
        foreach ($loop_stages as $item) {
//            var_dump($item);
            $this->changeXY($item);
        }
//        $this->changeXY($loop_stage);
//        return response()->json([
//            'data'=> $loop_stages
//
//        ]);
    }
    private function changeXY($data){
        $stage = json_decode($data->info,true);
        $new_stage = [];
        foreach ($stage as $y => $row) {
            foreach ($row as $x => $col) {
                $new_stage[$x][$y] = $stage[$y][$x];
            }
        }
        $new_data['num'] = $data->num;
        $new_data['name'] = $data->name;
        $new_data['direction'] = 2;
        $new_data['info'] = json_encode($new_stage);
        return DB::table('loop_stage')->updateOrInsert($new_data);
//        foreach ($stage as $row) {
//            foreach ($row as $v) {
//                echo $v['name'],'　　　';
//            }
//            echo '<br/>';
//        }
//        echo '<br/>';echo '<br/>';echo '<br/>';echo '<br/>';
//        foreach ($new_stage as $row) {
//            foreach ($row as $v) {
//                echo $v['name'],'　　　';
//            }
//            echo '<br/>';
//        }
//        var_dump($stage);;
//        var_dump($new_stage);die;
    }

}