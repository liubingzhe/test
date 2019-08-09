<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/18
 * Time: 18:12
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SensitiveWord extends Model
{
    // 指定id
    protected $table = 'sensitive_word';
    protected $primaryKey = 'id';

    public $timestamps  = true;

    public function getUpdatedAtColumn() {
        return null;
    }

}