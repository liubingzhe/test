<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/17
 * Time: 13:29
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class MobileSmsCode extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    // 指定表名
    protected $table = 'mobile_sms_code';

    // 指定id
    protected $primaryKey = 'id';

    public $timestamps = true;

    public function getUpdatedAtColumn() {
        return null;
    }
}