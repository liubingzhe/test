<?php
namespace App\Http\Controllers\API\Piece;

use App\Http\Controllers\API\Piece\Base\GiftController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;
use App\User;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

    public $successStatus = 200;

    /**
     * checkUser api
     * mobile 用户手机号
     * @return \Illuminate\Http\Response
     */
    public function checkUser($mobile,$name){
        $check_user = User::where([
            ['mobile','=',$mobile],
            ['is_valid','=','1'],
        ])->first();

        if (empty($check_user)) {
            $input['mobile'] = $mobile;
            $input['name'] = $name;
            $input['avatar'] = $this->randAvatar();
            $input['password'] = Hash::make(substr($mobile,7));
            $input['vitality'] = 30;
            $user = User::create($input);
            $user_id = $user->id;
            DB::table('name_change_log')
                ->insert([
                    'user_id'=>$user_id,
                    'name'=>$name ?? '',
                    'type'=>1,
                    'description'=>'用户首次注册',
                ]);
            /*$gift = new GiftController();
            $gift->get_gift($user_id,3,1,4);*/
            $token =  $user->createToken('MyApp')->accessToken;
        } else {
            $token =  $check_user->createToken('MyApp')->accessToken;
        }
        return $token;

    }
    //随机获取头像
    public function randAvatar()
    {
        $num = mt_rand(1,9);
        return "images/avatar/".$num .".png";
    }
    /**
     * 获取随机昵称
     * @return \Illuminate\Http\Response
     */
    public function getRandName($last_time)
    {
        $updata_time = DB::table('admin_operation_log')
            ->where('path', 'admin/getRandName')
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
        $first_name  = DB::table('rand_names')
            ->where('type', 1)
            ->get('value');
        $second_name = DB::table('rand_names')
            ->where('type', 2)
            ->get('value');

        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '数据返回成功',
            'data' => [
                'names_updata_time' => $updata_time,
                'first_name' => $first_name,
                'second_name' => $second_name,
            ],
        ]);

    }

    public function checkRandName($mobile)
    {
        $res       = [
            'mobile' => $mobile,
        ];
        $messages  = [
            [
                'regex' => ':attribute 格式不正确',
                'required' => ':attribute 必须填写',
            ],
        ];
        $validator = Validator::make($res, [
            'mobile' => 'required|regex:/^1[0-9]{10}$/',
        ], $messages);
        if ($validator->fails()) {
            return response()->json(['code' => 1002, 'succ' => 'false', 'message' => $validator->errors()]);
        }
        //检查手机号是否为已注册
        $user_id = DB::table('user')
            ->where('mobile', $mobile)
            ->value('id');
        if ($user_id) {
            return response([
                'code' => 1200,
                'succ' => 'false',
                'message' => '用户已注册，不能随机昵称',
            ]);
        }else{
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '用户未注册',
            ]);
        }
    }
}
