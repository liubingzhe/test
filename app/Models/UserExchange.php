<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 18:12
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserExchange extends Model
{
    // 指定id
    protected $table = 'user_exchange_log';
    protected $primaryKey = 'id';

    public $timestamps  = false;

}