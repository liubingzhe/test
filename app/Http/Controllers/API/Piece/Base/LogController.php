<?php


namespace App\Http\Controllers\API\Piece\Base;


use App\Http\Controllers\Controller;
use App\Tools\ActLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function saveActionLog(Request $request)
    {
        $input = $request->json()->all();
        $res   = ActLog::addMultipleLog($input);
        if ($res) {
            return response()->json([
                'code' => 1000,
                'succ' => 'true',
                'message' => '添加成功',
            ]);
        }else{
            return response()->json([
                'code' => 1300,
                'succ' => 'false',
                'message' => '添加失败',
            ]);
        }
    }
}