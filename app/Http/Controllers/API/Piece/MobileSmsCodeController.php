<?php

namespace App\Http\Controllers\API\Piece;


use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MobileSmsCode;
use App\Models\MobileSmsSentLog;
use Carbon\Carbon;
use \Exception;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use phpDocumentor\Reflection\Types\Boolean;
use Validator;

/**
 * 短信验证码发送与校验. 这里依赖阿里云的短信SDK和阿里云的短信通道.
 */
class MobileSmsCodeController extends Controller
{
    //
    public function newCodeAndSend($mobile){
        $validator = Validator::make(['mobile' => $mobile], [
            'mobile' =>'required|regex:/^1[0-9]{10}$/',
        ], [
            'regex' => ':attribute 格式不正确',
            'required' => ':attribute 不能为空',

            ]);

        if ($validator->fails()) {
            return response()->json(['code' => 1002,'succ' => 'false','message' => $validator->errors()]);
        }
        //短信过期时间
        $valid_time_len = get_config('valid_time_len');
        $new_sms = new MobileSmsCode();
        // 得到新的验证码
        $new_sms->mobile = $mobile;
        if ($_SERVER['SERVER_ADDR'] !== '192.168.10.150'){
            $new_sms->sms_code = $this->newCode();
            // 发送
            $result = $this->sendCode($new_sms);
        }else{
            $new_sms->sms_code = 6666;
            $result = new \stdClass();
            $result->Code = 'OK';
        }

        $new_sms->create_time = Carbon::now();
        $new_sms->invalid_time = $new_sms->create_time->copy()->addSeconds($valid_time_len);
        // 存入
        $new_sms->save();

        //检查手机号是否为已注册
        $user_id = DB::table('user')
            ->where('mobile', $mobile)
            ->value('id');


        if($result->Code == 'OK'){
            $new_sms->is_sent = 1;
            $new_sms->save();
            // 写入数据库库

            // 返回
            return response()->json([
                'code' => 1000,
                'succ' => 'true',
                'message' => '请求成功',
                'data' => [
                    'mobile' => $new_sms->mobile,
                    'create_time' => $new_sms->create_time,
                    'valid_time_len' => get_config('count_down_time'),
                    'user_exist' => (boolean)($user_id),
                ] ,
            ]);
        }else{
            return response()->json([
                'code' => 1008,
                'succ' => 'false',
                'message' => '验证码发送失败',
                ]);
        }
    }

    public function checkSmsCode(Request $request,$mobile){

        $input = $request->json()->all();
        $sms_code = $input['code'];
        $res = [
            'mobile' => $mobile,
            'name' => $input['name'],
            'sms_code' => $sms_code,
            'server_create_time' => $input['server_create_time'],
            'server_valid_time_len' => $input['server_valid_time_len'],
        ];
        $messages = [
            [
                'regex' => ':attribute 格式不正确',
                'required' => ':attribute 必须填写',
                'string' => ':attribute 长度不能超过过 :max',
            ],
        ];
        $validator = Validator::make($res, [
            'mobile' =>'required|regex:/^1[0-9]{10}$/',
            'name' => 'max:8',
            'sms_code' => 'required|digits:4',
            'server_create_time' => 'required|date',
            'server_valid_time_len' => 'required|digits:2',
        ],$messages);

        if ($validator->fails()) {
            return response()->json(['code' => 1002,'succ' => 'false','message' => $validator->errors()]);
        }
        if (!filterString($input['name'])){

            return response()->json([
                'code' => 1002,
                'succ' => 'false',
                'message' => '昵称中含有敏感词，请更改'
            ]);
        }
        DB::beginTransaction();
        try{
            $check_sms = MobileSmsCode::where([
                ['mobile','=',$mobile],
                ['sms_code','=',$sms_code],
                ['is_sent','=','1'],
                ['is_checked','=','0'],
                ['invalid_time','>',Carbon::now()]
            ])->orderBy('create_time','desc')->first();
            if (empty($check_sms)) {
                return response()->json(['code' => 1010 ,'succ' => 'false','message' => '验证码超时失效']);
            }
            if($sms_code == $check_sms->sms_code){
                if (Carbon::now()->toDateTimeString() > $check_sms->invalid_time) {
                    return response()->json(['code' => 1010 ,'succ' => 'false','message' => '验证码超时失效']);
                }else {
                    $check_sms->is_checked = 1;
                    $check_sms->check_time = Carbon::now();
                    $check_sms->save();
                    //检查手机号是否为已注册
                    $user = new UserController();
                    $token = $user->checkUser($mobile,$input['name']);

                    $user_id = DB::table('user')
                        ->where('mobile',$mobile)
                        ->value('id');
                    $where = [
                        'mac'=>$input['mac'],
                        'device_id'=>$input['device_id'],
                        'device_gpu_id'=>$input['device_gpu_id'],
                    ];
                    if (!empty($where)){
                        $res = DB::table('device')
                            ->updateOrInsert($where,[
                                'user_id'=>$user_id
                            ]);
                    }

                    DB::commit();
                    return response()->json(['code' => 1000 ,'succ' => 'true','message' => '数据返回成功','user_id'=>$user_id,'token' => $token,'accept'=>'application/json'], 200);
                }

            }else{
                return response()->json([
                        'code' => 1011,
                        'succ' => 'false',
                        'message' => '验证码校验失败',
                ]);
            }
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['code' => 1011, 'succ' => 'false','message'=>'验证码校验失败','data' => $e->getMessage()]);
        }
    }

    /**
     * 随机生成4位验证码
     *
     */
    private function newCode(){
        $low = 1000;
        $up = 9999;
        return mt_rand($low, $up);
    }

    /**
     * 通过阿里云短信接口, 发送验证码.
     *
     * @param [type] $new_sms
     * @return void
     */
    private function sendCode($new_sms){
        AlibabaCloud::accessKeyClient('LTAIWOvYXfo9xuVj', 'Zg6X92wOZg1uFl9VPWe3xjEDYm4Bxx')
            ->regionId('cn-hangzhou') // replace regionId as you need
            ->asGlobalClient();

        try {
            $result = AlibabaCloud::rpcRequest()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'PhoneNumbers' => $new_sms->mobile,
                        'SignName' => '中华盛视网',
                        'TemplateCode' => 'SMS_140705163',
                        'TemplateParam' => '{"code":"'.$new_sms->sms_code.'"}',
                    ],
                ])
                ->request();

        } catch (ClientException $e) {
//            response()->json(['error'=>'send code fail','msg' => $e->getErrorMessage()]);
        } catch (ServerException $e) {
            //          response()->json(['error'=>'send code fail','msg' => $e->getErrorMessage()]);
        } finally{
            $new_log = new MobileSmsSentLog();
            $new_log->mobile = $new_sms->mobile;
            $new_log->sms_code = $new_sms->sms_code;
            $new_log->return_code = $result->Code;
            $new_log->return_msg = $result->Message;
            $new_log->request_id = $result->RequestId;
            $new_log->biz_id = $result->BizId;
            $new_log->sms_code_id = $new_sms->id;
            $new_log->save();

            return $result;
        }
    }
}
