<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 18:12
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    // 指定id
    protected $table = 'stage';
    protected $primaryKey = 'id';

    public $timestamps  = true;

    public function getUpdatedAtColumn() {
        return null;
    }

    /*public function getStageTypeAttribute($value)
    {
        $gender = array(1=>'正常碎片',2=>'随机碎片');

        return $gender[$value];
    }*/
    public function getAvatarAttribute($value)
    {
        if (!strstr($value,'http')) {
            $value = \Illuminate\Support\Facades\Config::get('constants.PATH').$value;
        }
        return $value;

    }

}