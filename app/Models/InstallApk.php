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

class InstallApk extends Model
{
    // 指定id
    protected $table = 'install_apk';
    protected $primaryKey = 'id';

    public $timestamps  = true;

    public function getUpdatedAtColumn() {
        return null;
    }
    public function getUrlAttribute($value)
    {
        if (!strstr($value,'http')) {
            $value = \Illuminate\Support\Facades\Config::get('constants.PATH').$value;
        }
        return $value;
    }
    public function getTypeAttribute($value)
    {
        $gender = array(1=>'关卡文件',2=>'游戏文件');

        return $gender[$value];
    }
}