<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 18:12
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Tools extends Model
{
    // 指定id
    protected $table = 'tools';
    protected $primaryKey = 'id';

    public $timestamps  = true;

    public function getUpdatedAtColumn() {
        return null;
    }
    public function getIconPathAttribute($value)
    {
        if (!strstr($value,'http')) {
            $value = \Illuminate\Support\Facades\Config::get('constants.PATH').$value;
        }
        return $value;

    }
}