<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 18:34
 */

namespace App\Http\Controllers\API\Piece\Base;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class VersionController extends Controller
{
    public function version(Request $request)
    {
        //版本控制  请求数据库最新版本和设备对比  如不一样进行更新
        $input = $request->json()->all();
        $messages  = [
            'required' => ':attribute 不能为空',
        ];
        $validator = Validator::make($input, [
            'mac' => 'required',
            'device_id' => 'required',
            'device_gpu_id' => 'required',
            'app_version' => 'required',
        ], $messages);
        if ($validator->fails()) {
            return response()->json([
                'code' => 1002,
                'succ' => 'false',
                'message' => $validator->errors(),
                'data' => []
            ]);
        }
        $input_device = $input;
        unset($input_device['network']);
        unset($input_device['ip']);
        unset($input_device['device_time']);
        $where = [
            'mac' => $input['mac'],
            'device_id' => $input['device_id'],
            'device_gpu_id' => $input['device_gpu_id'],
        ];
        DB::table('device')->updateOrInsert($where, array_filter($input_device));
        $did = DB::table('device')
            ->where($where)
            ->value('id');

        $input_log = [
            'did' => $did,
            'network' => $input['network'] ?? '',
            'ip' => $input['ip'] ?? '',
            'device_time' => $input['device_time'] ?? date('Y-m-d H:i:s'),
        ];
        DB::table('device_log')->updateOrInsert($input_log);

        switch (strtolower($input['device_os'])) {
            case 'ios':
                $type = 1;
                break;
            case 'android':
                $type = 2;
                break;
            default:
                $type = 3;
        }
        $version_setting = DB::table('versioning')
            ->select('version as lastest_app_version', 'force_update', 'detail')
            ->where('type', $type)
            ->orderBy('id')
            ->first();
        if (!empty($version_setting) && $input['app_version'] !== $version_setting->lastest_app_version) {
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '数据返回成功',
                'data' => $version_setting
            ]);
        }
        else {
            return response([
                'code' => 1200,
                'succ' => 'false',
                'message' => '请求成功，但无数据。'
            ]);
        }
    }
}