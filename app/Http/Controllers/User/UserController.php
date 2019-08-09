<?php
/**
 *不使用passport验证接口
 *
 */
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//use Illuminate\Http\Request;
use App\User;
use Validator;

class UserController extends Controller
{

    public function userInfo($id)
    {
        $user_id = $id;
        $user = User::find($user_id);
//        用户通过关信息
        $stage_id = DB::table('stage_pass_log')->where('user_id',$user_id)
            ->orderByRaw('create_time desc')
            ->limit(1)
            ->value('stage_id');
//        三星通关数
        $star = DB::table('stage_pass_log')->where(['user_id' => $user_id,'star_num' => 3])->count();
//        关卡信息
        $stage_count = DB::table('stage')->count();
        $stage_stat = $stage_count * 3;
        return response([
            'code' => 200,
            'status' => 'success',
            'msg' => '请求成功',
            'data' => [
                'user_info' => $user,
                'pass_info' => [
                    'stage_id' => $stage_id,
                    'star' => $star,
                    'stage_count' => $stage_count,
                    'stage_stat' => $stage_stat,
                ] ,
            ]
        ], 200);
    }
//更新用户信息
    public function updateInfo(Request $request,$id)
    {
        $all = $request->all();

        $user = User::find($id);
        $user->name = $all['phone'];
        $user->email = $all['email'];
        $bool = $user->save();
        if ($bool) {
            return response([
                'code' => 200,
                'succ' => 'true',
                'message' => '用户信息更新成功',
                'data' => [],
            ], 200);
        } else {
            return response([
                'code' => 400,
                'succ' => 'false',
                'message' => '请求失败，用户信息更新失败',
                'data' => [],
            ], 400);
        }
//        用户通过关信息

    }
//删除数据库数据
    public function deleteUser($id)
{
    $users = User::find($id);
    if (!$users) {
        return response([
            'code' => 400,
            'succ' => 'false',
            'message' => '请求失败，用户不存在',
            'data' => [],
        ], 400);
    }
    $bool = $users->delete();
    if (!$bool) {
        return response([
            'code' => 400,
            'succ' => 'false',
            'message' => '请求失败，删除用户信息失败',
            'data' => [],
        ], 400);
    }
    return response([
        'code' => 200,
        'succ' => 'true',
        'message' => '请求成功，成功删除用户信息',
        'data' => [],
    ], 200);
}
}