<?php
namespace App\Http\Controllers\API\Crossword;

use function AlibabaCloud\Client\json;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrosswordController extends BaseController
{
    public function dealdata(){
        $page = 5;
        $start = 5000*($page-1)+1;
        $end = 5000*$page;
        for ($i = $start;$i<=$end;$i++){
            $idiom = DB::table('crossword_idiom')
                ->where('id',$i)
                ->value('idiom');
            if (empty($idiom)){
                echo $i;
                echo ',';
                continue;
            }
            $idiom_arr = preg_split('/(?<!^)(?!$)/u', $idiom );
            $data = [
                'col1'=>$idiom_arr[0],
                'col2'=>$idiom_arr[1],
                'col3'=>$idiom_arr[2],
                'col4'=>$idiom_arr[3],
            ];
            DB::table('crossword_idiom')
                ->where('id',$i)
                ->update($data);
        }
    }
    public function getIdiom(Request $request){

        if (!is_numeric($request->pos)){
            return response()->json([
                'code' => 1002,
                'succ' => 'false',
                'message' => '位置应该是数字',
            ]);
        }
        if (!preg_match('/^[\x{4e00}-\x{9fa5}]{1}$/u',$request->word)) {
            return response()->json([
                'code' => 1002,
                'succ' => 'false',
                'message' => '关键词应是一个汉字',
            ]);
        }
        $idioms = DB::table('crossword_idiom')
            ->where('col'.$request->pos,$request->word)
            ->pluck('idiom')
            ->toArray();
        if(!empty($idioms)){
            return response()->json([
                'code' => 1000,
                'succ' => 'true',
                'message' => '获取成功',
                'data'=>$idioms
            ]);
        } else {
            return response()->json([
                'code' => 1200,
                'succ' => 'true',
                'message' => '请求成功，但无数据',
            ]);
        }
    }
//    添加关卡
    public function addStage(Request $request){
        $input = $request->json()->all();
        if (!is_numeric($input['num'])){
            return response()->json([
                'code' => 1002,
                'succ' => 'false',
                'message' => '关卡号应该是数字',
            ]);
        }
        $input['name'] = empty($input['name']) ? '#'.$input['num'] : $input['name'];
        $input['stage'] = json_encode($input['stage'],JSON_UNESCAPED_UNICODE);
        $input['words'] = json_encode($input['words'],JSON_UNESCAPED_UNICODE);
        $input['answer'] = json_encode($input['answer'],JSON_UNESCAPED_UNICODE);
        if (substr_count($input['stage'],1) !== count($request->words)){
            return response()->json([
                'code' => 1300,
                'succ' => 'false',
                'message' => '空格和备选字数量不符',
            ]);
        }
        $res = DB::table('crossword_stage')->updateOrInsert(['num'=>$input['num']],$input);

        if($res){
            return response()->json([
                'code' => 1000,
                'succ' => 'true',
                'message' => '添加成功',
            ]);
        } else {
            return response()->json([
                'code' => 1300,
                'succ' => 'false',
                'message' => '添加失败',
            ]);
        }

    }
//    获取关卡信息
    public function getStage(Request $request){

        if (!is_numeric($request->num)){
            return response()->json([
                'code' => 1002,
                'succ' => 'false',
                'message' => '请求参数错误',
            ]);
        }
        $crossword_stage = DB::table('crossword_stage')
            ->where('num',$request->num)
            ->select('num','name','stage','words','answer')
            ->first();
        if (empty($crossword_stage)){
            return response()->json([
                'code' => 1200,
                'succ' => 'false',
                'message' => '请求成功，但无数据'
            ]);
        }else{
            $crossword_stage->stage = json_decode($crossword_stage->stage);
            $crossword_stage->words = json_decode($crossword_stage->words);
            $crossword_stage->answer = json_decode($crossword_stage->answer);
            return response()->json([
                'code' => 1000,
                'succ' => 'true',
                'message' => '获取成功',
                'data'=>$crossword_stage
            ]);
        }

    }

    public function finishStage(Request $request){
        if (!is_numeric($request->stage_id)){
            return response()->json([
                'code' => 1002,
                'succ' => 'false',
                'message' => '请求参数错误',
            ]);
        }
        $pass_info = [
            'user_id'=>$this->userid,
            'stage_id'=>$request->stage_id,
            'stars'=>$request->stars,
            'finish_time'=>$request->finish_time,
        ];
        $res = DB::table('crossword_stage_pass_log')->insert($pass_info);
        if ($res){
            return response()->json([
                'code' => 1000,
                'succ' => 'true',
                'message' => '添加成功'
            ]);
        }else{
            return response()->json([
                'code' => 1300,
                'succ' => 'false',
                'message' => '添加失败'
            ]);
        }
    }

}