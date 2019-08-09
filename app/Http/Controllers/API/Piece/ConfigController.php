<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 18:34
 */

namespace App\Http\Controllers\API\Piece;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfigController extends Controller
{
    public function config()
    {
        $url = get_config('URL');
        $token = get_config('token');
        $type = get_config('type');
        $signin_fix = get_config('signin_fix');
        $stage_main_enter = get_config('stage_main_enter');
        return response([
            'code' => 1000,
            'succ' => 'true',
            'msg' => '数据返回成功',
            'data' => [
                'eshop' => [
                    'url' => $url,
                    'type' => $type,
                    'token' => $token,
                ],
                'signin_fix' => $signin_fix,
                'stage_main_enter' => $stage_main_enter,
            ]
        ]);
    }
}