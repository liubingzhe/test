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

class BackPack extends Model
{
    // 指定id
    protected $table = 'backpack';
    protected $primaryKey = 'id';

    public $timestamps  = true;

    public function getUpdatedAtColumn() {
        return null;
    }
    public function getToolsIdAttribute($value)
    {
        $gender = DB::table('tools')->where('id',$value)->value('tools_name');

        return $gender;
    }
}