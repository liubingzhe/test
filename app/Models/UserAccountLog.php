<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 18:12
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserAccountLog extends Model
{
    // 指定id
    protected $table = 'user_account_log';
    protected $primaryKey = 'id';

    public $timestamps  = true;

    public function getUpdatedAtColumn() {
        return null;
    }
    public function getBehaviorAttribute($value)
    {
        $gender = DB::table('dict_user_behavior')->where('behavior',$value)->value('detail');

        return $gender;
    }
}