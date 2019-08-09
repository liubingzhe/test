<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/16
 * Time: 11:33
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    public $userid;
    public function __construct()
    {
        //无需验证
        $accross_uri = [
            '/api/game/loop/submit',
            '/api/game/crossword/addStage',
        ];
        $uri = \Request::getRequestUri();
        $res = false;
        foreach ($accross_uri as $k => $v){
            if (strstr($uri,$v)){
                $res = true;
                break;
            }
        }
        if (!$res){
            $this->middleware(function($request, $next) {
                if (! $request->expectsJson()) {
                    return response()->json(['code' => 1002,'succ' => 'false','message' => '接口参数无效']);
                }
                $user = \auth('api')->user();
                if (!$user) {
                    return response([
                        'code' => 1001,
                        'succ' => 'false',
                        'message' => '接口认证失败,请勿非法操作！',

                    ]);
                }
                $this->userid = $user->id;
                return $next($request);
            });
        }

    }
}