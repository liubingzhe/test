<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/17
 * Time: 13:30
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class MobileSmsSentLog extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
// 指定表名
    protected $table = 'mobile_sms_sent_log';

    // 指定id
    protected $primaryKey = 'id';

    public $timestamps = false;

    public function getUpdatedAtColumn() {
        return null;
    }
}