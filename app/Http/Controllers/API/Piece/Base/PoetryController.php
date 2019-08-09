<?php
/**
 * Created by PhpStorm.
 * User: 34452
 * Date: 2019/5/6
 * Time: 17:03
 */

namespace App\Http\Controllers\API\Piece\Base;


use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class PoetryController extends BaseController
{
    public function getPoetry($last_time)
    {
        $updata_time = DB::table('admin_operation_log')
            ->where('path', 'admin/getPoetry')
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
        $data = DB::table('poetry')
            ->select('content','author')
            ->get();
        if ($data){
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '获取成功',
                'data' => [
                    'poetry_updata_time' => $updata_time,
                    'poetry'=> $data
                ]
            ]);
        }else{
            return response([
                'code' => 1200,
                'succ' => 'true',
                'message' => '请求成功，但数据为空',
            ]);
        }
    }
}