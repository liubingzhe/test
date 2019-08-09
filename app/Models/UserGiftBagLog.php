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

class UserGiftBagLog extends Model
{
    const UPDATED_AT = null;
    // 指定id
    protected $table = 'user_gift_bag_log';
    protected $primaryKey = 'id';

    public $timestamps  = true;

    public function getUpdatedAtColumn() {
        return null;
    }
    public function getGiftBagIdAttribute($value)
    {
        $gender = DB::table('gift_bag')->where('id',$value)->value('name');

        return $gender;
    }
    public function getBehaviorAttribute($value)
    {
        $gender = DB::table('dict_user_behavior')->where('behavior',$value)->value('detail');

        return $gender;
    }
}