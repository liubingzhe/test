<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 17:27
 */

namespace App\Http\Controllers\API\SmallGame;


use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OpenIDController extends Controller{

    public function getopenid(Request $request)
    {
        $appid = 'wx6924e579d0dc1d9c';//appid需自己提供，此处的appid我随机编写
        $secret = 'f242f9310b2b2df04e567b0ce83bc0b2';
//        $appid = 'wx662ddf58fda9f796';
//        $secret = 'ef8baeead26da763f2e2da233bc55dda';
        $code = $request->get('code');
        $user_info = $request->get('userInfo');
        $URL = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";
        $apiData = json_decode(file_get_contents($URL),true);
        if (!isset($apiData['errcode'])) {
            $sessionKey = $apiData['session_key'];
            $openid = $apiData['openid'];
            $user_info['openid'] = $openid;
            $v_user = DB::table('wechat')->where('openid',$openid)->first();
            if (empty($v_user)) {
                $user = array(
                    'is_valid' => 1,
                    'name' => $user_info['nickName'],
                    'avatar' => $user_info['avatarUrl'],
                );
                $user_res = User::create($user);
                $user_info['user_id'] = $user_res->id;
                $user_info['session_key'] = $sessionKey;
                DB::table('wechat')->insert($user_info);


            } else {
                $user_res = User::where([
                    ['id','=',$v_user->user_id],
                    ['is_valid','=','1'],
                ])->first();
            }
            $token =  $user_res->createToken('MyApp')->accessToken;

            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '绑定成功',
                'data' => [
                    'user_id' => $user_res->id,
                    'token' => $token
                ]
            ]);
        }else{
            return response([
                'code' => $apiData['errcode'],
                'succ' => 'true',
                'message' => $apiData['errmsg']

            ]);
        }
    }
}