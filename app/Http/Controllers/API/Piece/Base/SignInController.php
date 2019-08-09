<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 17:27
 */

namespace App\Http\Controllers\API\Piece\Base;


use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="GameApi",
 *      description="L5 Swagger OpenApi description",
 *      @OA\Contact(
 *          email="344529372@qq.com"
 *      ),
 *     @OA\License(
 *         name="Nginx/1.14.1",
 *         url=""
 *     )
 * )
 */

/**
 *  @OA\PathItem(
 *     path = "/app/Http/Controllers/API/SignInController.php"
 *  )
 *
 */
class SignInController extends BaseController{

    /**
     * @OA\Get(
     *      path="/api/game/base/sign/setting/{user_id}",
     *      operationId="setting",
     *      tags={"Projects"},
     *      summary="Get json information",
     *      description="Returns json data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Project id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(response=200, description="successful operation"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      security={
     *         {
     *             "oauth2_security_example": {"write:projects", "read:projects"}
     *         }
     *     }
     * )
     */
    /**
     * 签到设置
     *
     **/
    public function setting($user_id = 0)
    {
        //签到设置
        if (!is_numeric($user_id) || $user_id != $this->userid){
            return response([
                'code' => 1017,
                'succ' => 'false',
                'message' => '用户id参数有误'
            ]);
        }
        $data['signin_month'] = date('n');//签到月
        $data['signin_today'] = date('j');//签到日
        $data['signin_month_days'] = date('t');//当月天数
        $data['signin_fix_cost'] = get_config('signin_fix');//补签消耗元宝数
        $total_reward = DB::table('signin_setting')
            ->where('day_type',1)
            ->select('signin_day as signin_days','gift_bag_id','gift_bag_num')
            ->get();
        if (empty($total_reward)){
            return response([
                'code' => 1200,
                'succ' => 'false',
                'message' => '请求成功，但无数据。'
            ]);
        }
        foreach ($total_reward as $k => $v){
            if ($v->gift_bag_id){
             /*   $total_reward[$k]->gift_bag = DB::table('gift_bag')
                    ->where('id',$v->gift_bag_id)
                    ->where('is_valid',1)
                    ->select('id as gift_id','include_diamonds','include_points')
                    ->first();
                $total_reward[$k]->gift_bag->tools = DB::table('gift_bag_tools as g')
                    ->join('tools as t','g.tools_id','t.id')
                    ->where('g.gift_bag_id',$v->gift_bag_id)
                    ->where('t.is_valid',1)
                    ->select('g.tools_id as tool_id','t.tools_name as name','g.tools_num as number')
                    ->get();*/
                $is_receive = DB::table('user_gift_bag_log')
                    ->where('gift_bag_id',$v->gift_bag_id)
                    ->where('user_id',$user_id)
                    ->where('year',date('Y'))
                    ->where('month',date('n'))
                    ->where('day',$v->signin_days)
                    ->first();
                $total_reward[$k]->gift_bag_received = $is_receive ? true : false;
            }
        }
        $data['total_reward'] = $total_reward;

        $days = [];
        for ($i = 1;$i<=date('j');$i++){
            $days[] = 'day_'.$i;
        }
         $signin = DB::table('signin')
            ->where('user_id',$user_id)
            ->where('date_year',date('Y'))
            ->where('date_month',date('n'))
            ->first($days);
        if ($signin){
            foreach ($signin as $k => $v){
                if ($v != 3){
                    $data['my_signin'][] = substr($k,4);
                }
            }
        }

        return response([
            'code' => 1000,
            'succ' => 'true',
            'message' => '获取成功',
            'data' => $data
        ]);

    }
    public function today($user_id = 0)
    {
        if( abs(strtotime(date('Y-m-d 23:59:59'))-time())<1800){
            return response([
                'code' => 1038,
                'succ' => 'false',
                'message' => '23:30-00:30不能签到'
            ]);
        }
        if (!is_numeric($user_id) || $user_id != $this->userid){
            return response([
                'code' => 1017,
                'succ' => 'false',
                'message' => '用户id参数有误'
            ]);
        }
        $today = date('j');//日
        $month = date('n');//月
        $year = date('Y');//年
        $data = DB::transaction(function () use ($user_id,$today,$month,$year) {

//            签到
            $where = [
                'user_id'=>$user_id,
                'date_year'=>$year,
                'date_month'=>$month
            ];
            $update_data = [
                'day_'.$today => 1,
                'user_id'=>$user_id,
                'date_year'=>$year,
                'date_month'=>$month
            ];
            $signin = DB::table('signin')->updateOrInsert($where,$update_data);
            $setting = DB::table('signin_setting as s')
                ->leftJoin('tools as t','s.tools_id','t.id')
                ->where('s.day_type',2)
                ->where('s.signin_day',$today)
                ->select('s.reward_points','s.reward_diamonds','s.tools_id','s.tools_num','t.tools_name')
                ->first();
            if (!empty($setting) && $signin){
                if ($setting->reward_points || $setting->reward_diamonds){
                    DB::table('user')->where('id', $user_id)
                        ->update([
                            'points' => DB::raw('points + '.$setting->reward_points),
                            'diamonds'  => DB::raw('diamonds + '.$setting->reward_diamonds),
                        ]);

                    DB::table('user_account_log')
                        ->insert([
                            'user_id'=>$user_id,
                            'behavior'=>403,
                            'diamonds'=>$setting->reward_points,
                            'points'=>$setting->reward_diamonds,
                            'detail'=>'签到'.$month.'月'.$today.'日，获得'.$setting->reward_diamonds.'元宝，获得'.$setting->reward_points.'铜钱',
                        ]);
                }
                if ($setting->tools_id && $setting->tools_num) {
                    DB::table('backpack')->insertGetId([
                        'user_id'=>$user_id,
                        'tools_id'=>$setting->tools_id,
                        'tools_num'=>$setting->tools_num
                    ]);
                    DB::table('user_tools_log')->insert([
                        'user_id'=>$user_id,
                        'tools_id'=>$setting->tools_id,
                        'tools_count'=>$setting->tools_num,
                        'behavior'=>403,
                    ]);
                }
            }
            $data['reward_diamonds'] = $setting->reward_diamonds ?? 0;
            $data['reward_points'] = $setting->reward_points ?? 0;
            $data['reward_tool_id'] =$setting->tools_id ?? 0;
            $data['reward_tool_name'] =$setting->tools_name ?? '';
            $data['reward_tool_count'] =$setting->tools_num ?? 0;
            $data['signin_today'] = $today;
            $data['is_signin'] = $signin;
            return $data;


        });
        $days = [];
        for ($i = 1;$i<=$today;$i++){
            $days[] = 'day_'.$i;
        }
        $my_signin = DB::table('signin')
            ->where('user_id',$user_id)
            ->where('date_year',$year)
            ->where('date_month',$month)
            ->first($days);
        if (!empty($my_signin)){
            foreach ($my_signin as $k => $v){
                if ($v != 3){
                    $data['my_signin'][] = substr($k,4);
                }
            }
        }
        if ($data['is_signin']){
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '签到成功',
                'data' => $data
            ]);
        }else{
            return response([
                'code' => 1028,
                'succ' => 'false',
                'message' => '已签到',
                'data' => $data
            ]);
        }



    }
    public function fix($user_id = 0)
    {
        if( abs(strtotime(date('Y-m-d 23:59:59'))-time())<1800){
            return response([
                'code' => 1038,
                'succ' => 'false',
                'message' => '23:30-00:30不能签到'
            ]);
        }
        if (!is_numeric($user_id) || $user_id != $this->userid){
            return response([
                'code' => 1017,
                'succ' => 'false',
                'message' => '用户id参数有误'
            ]);
        }
//        签到消耗元宝数
        $fix_config = get_config('signin_fix');
        $user_diamonds = DB::table('user')
            ->where('id',$user_id)
            ->value('diamonds');
//        var_dump($user_diamonds);die;
        if ($user_diamonds < $fix_config){
            return response([
                'code' => 1032,
                'succ' => 'false',
                'message' => '元宝余额不足'
            ]);
        }
        $today = date('j');//日
        $month = date('n');//月
        $year = date('Y');//年
        $signin_data = [
            'user_id'=>$user_id,
            'date_year'=>$year,
            'date_month'=>$month
        ];
        DB::table('signin')->updateOrInsert($signin_data);
        $days = [];
        for ($i = 1;$i<$today;$i++){
            $days[] = 'day_'.$i;
        }
        $signin = DB::table('signin')
            ->where('user_id',$user_id)
            ->where('date_year',$year)
            ->where('date_month',$month)
            ->first($days);

        if (empty($signin)){
            return response([
                'code' => 1033,
                'succ' => 'false',
                'message' => '无需补签'
            ]);
        }
        foreach ($signin as $k => $v){
            if ($v == 3){
                $no_signin = $k;
                break;
            }
        }
        if (!isset($no_signin)){
            return response([
                'code' => 1033,
                'succ' => 'false',
                'message' => '无需补签'
            ]);
        }
        $data = DB::transaction(function () use ($user_id,$today,$month,$year,$no_signin,$fix_config) {

            //补签  顺序补签  例如 2,6,19 为未签到的  补签 第一次为2号  顺序补签


            $update_data[$no_signin] = 2;
    //        for ($i = $today+1;$i<=31;$i++){
    //            $update_data['day_'.$i] = 3;
    //        }
            $update_data['date_year'] = $year;
            $update_data['date_month'] = $month;
            $update_data['user_id'] = $user_id;
            $where['user_id'] = $user_id;
            $where['date_year'] = $year;
            $where['date_month'] = $month;
            $fix_signin = DB::table('signin')
                ->updateOrInsert($where,$update_data);
            if ($fix_signin){
                DB::table('user_account_log')
                    ->insert([
                        'user_id'=>$user_id,
                        'behavior'=>404,
                        'diamonds'=>-1*$fix_config,
                        'points'=>0,
                        'detail'=>'补签'.$month.'月'.substr($no_signin,4).'日，消耗'.$fix_config.'元宝',
                    ]);
            }

            $setting = DB::table('signin_setting as s')
                ->leftJoin('tools as t','s.tools_id','t.id')
                ->where('s.day_type',2)
                ->where('s.signin_day',substr($no_signin,4))
                ->select('s.reward_diamonds','s.reward_points','s.tools_id','s.tools_num','t.tools_name')
                ->first();

            if (!empty($setting) && $fix_signin){
                if ($setting->reward_points || $setting->reward_diamonds){
                    $user_points = DB::table('user')->where('id', $user_id)
                        ->update([
                            'points' => DB::raw('points + '.$setting->reward_points),
                            'diamonds'  => DB::raw('diamonds + '.$setting->reward_diamonds),
                        ]);

                    DB::table('user_account_log')
                        ->insert([
                            'user_id'=>$user_id,
                            'behavior'=>404,
                            'diamonds'=>$setting->reward_points,
                            'points'=>$setting->reward_diamonds,
                            'detail'=>'补签'.$month.'月'.substr($no_signin,4).'日，获得'.$setting->reward_diamonds.'元宝，获得'.$setting->reward_points.'铜钱',
                        ]);
                }
                if ($setting->tools_id && $setting->tools_num) {
                    $backpack_id = DB::table('backpack')->insertGetId([
                        'user_id'=>$user_id,
                        'tools_id'=>$setting->tools_id,
                        'tools_num'=>$setting->tools_num
                    ]);

                    DB::table('user_tools_log')->insert([
                        'user_id'=>$user_id,
                        'tools_id'=>$setting->tools_id,
                        'tools_count'=>$setting->tools_num,
                        'behavior'=>404,
                    ]);
                }
            }
            $data['reward_diamonds'] = $setting->reward_diamonds ?? 0;
            $data['reward_points'] = $setting->reward_points ?? 0;
            $data['reward_tool_id'] =$setting->tools_id ?? 0;
            $data['reward_tool_name'] =$setting->tools_name ?? '';
            $data['reward_tool_count'] =$setting->tools_num ?? 0;
            $data['signin_today'] = substr($no_signin,4);
            $data['is_fix'] = $fix_signin;
            return $data;
        });
        $days = [];
        for ($i = 1;$i<=$today;$i++){
            $days[] = 'day_'.$i;
        }
        $my_signin = DB::table('signin')
            ->where('user_id',$user_id)
            ->where('date_year',$year)
            ->where('date_month',$month)
            ->first($days);
        if (!empty($my_signin)){
            foreach ($my_signin as $k => $v){
                if ($v != 3){
                    $data['my_signin'][] = substr($k,4);
                }
            }
        }
        if ($data['is_fix']){
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '补签成功',
                'data' => $data
            ]);
        }else{
            return response([
                'code' => 1029,
                'succ' => 'false',
                'message' => '补签失败',
                'data' => $data
            ]);
        }




    }
//    累计签到奖励
    public function gift($user_id = 0 ,$total_signin_days = 0)
    {
        if (!is_numeric($user_id) || $user_id != $this->userid){
            return response([
                'code' => 1017,
                'succ' => 'false',
                'message' => '用户id参数有误'
            ]);
        }
        if (!is_numeric($total_signin_days)){
            return response([
                'code' => 1002,
                'succ' => 'false',
                'message' => '接口参数无效'
            ]);
        }
        $setting = DB::table('signin_setting as s')
            ->leftJoin('tools as t','s.tools_id','t.id')
            ->where('s.day_type',1)
            ->where('s.signin_day',$total_signin_days)
            ->select('s.reward_points','s.reward_diamonds','s.tools_id','s.tools_num','s.gift_bag_id','s.gift_bag_num','t.tools_name')
            ->first();
        $is_receive = DB::table('user_gift_bag_log')
            ->where('gift_bag_id',$setting->gift_bag_id)
            ->where('user_id',$user_id)
            ->where('year',date('Y'))
            ->where('month',date('n'))
            ->where('day',$total_signin_days)
            ->first();
        if (!empty($is_receive)) {
            return response([
                'code' => 1037,
                'succ' => 'false',
                'message' => '已领取，请勿重复领取'
            ]);
        }
        $behavior = 405;
        $data = DB::transaction(function () use ($user_id,$total_signin_days,$setting,$behavior) {

            if (!empty($setting)){
                //奖励钻石和铜钱
                if ($setting->reward_points || $setting->reward_diamonds){
                    DB::table('user')->where('id', $user_id)
                        ->update([
                            'points' => DB::raw('points + '.$setting->reward_points),
                            'diamonds'  => DB::raw('diamonds + '.$setting->reward_diamonds),
                        ]);

                    DB::table('user_account_log')
                        ->insert([
                            'user_id'=>$user_id,
                            'behavior'=> $behavior,
                            'diamonds'=>$setting->reward_points,
                            'points'=>$setting->reward_diamonds,
                            'detail'=>date('n').'月累计签到'.$total_signin_days.'天，获得'.$setting->reward_diamonds.'元宝，获得'.$setting->reward_points.'铜钱',
                        ]);
                }
                if ($setting->tools_id && $setting->tools_num) {

                    DB::table('backpack')->updateOrInsert([
                        'user_id' => $user_id,
                        'tools_id' => $setting->tools_id,
                    ], [
                        'tools_num' => DB::raw('tools_num + ' . $setting->tools_num)
                    ]);
                    DB::table('user_tools_log')->insert([
                        'user_id'=>$user_id,
                        'tools_id'=>$setting->tools_id,
                        'tools_count'=>$setting->tools_num,
                        'behavior'=> $behavior,
                    ]);
                }
                $gift_bag = new GiftController();
                $gift = $gift_bag->get_gift($user_id,$setting->gift_bag_id,$setting->gift_bag_num,$behavior,$total_signin_days);
                if ($gift){
                    $data['signin_days'] = $total_signin_days;
                    $data['gift_bag'] = [
                        'include_diamonds'=> $gift['include_diamonds'] + $setting->reward_diamonds ,
                        'include_points'=> $gift['include_points'] + $setting->reward_points,
                        'tools'=> $gift['tools'],
                    ];
                    return $data;
                }else{
                    return false;
                }
            }else{
                return null;
            }


        });
        if (is_null($data)){
            return response([
                'code' => 1030,
                'succ' => 'false',
                'message' => '无可领取礼包'
            ]);
        }elseif ($data === false){
            return response([
                'code' => 1031,
                'succ' => 'false',
                'message' => '领取礼包失败'
            ]);
        }elseif($data){
            return response([
                'code' => 1000,
                'succ' => 'true',
                'message' => '领取成功',
                'data' => $data
            ]);
        }
    }



}