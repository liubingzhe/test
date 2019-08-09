<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 18:12
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    // 指定id
    protected $table = 'broadcast';
    protected $primaryKey = 'id';

    public $timestamps  = true;

    public function getUpdatedAtColumn() {
        return null;
    }
    public function getTypeAttribute($value)
    {
        $gender = array(1 => '系统广播',2 => '后台管理推送',3 => '用户通告');

        return $gender[$value];
    }
}