<?php

function showMsg($status,$message = '',$data = array())
{
    $result = array(
        'status' => $status,
        'message' => $message,
        'data' => $data
    );
    exit(json_encode($result));
}
function checkIphone($mobile)
{

    if(preg_match("/^1[3456789]\d{9}$/", $mobile)){
        return true;
    } else {
        return false;
    }
}
//    检验验证码是否为数字
function checkCode($code)
{

    if(is_numeric($code)){
        return true;
    } else {
        return false;
    }
}

function make_password( $length = 8 ) {
    // 密码字符集，可任意添加你需要的字符
    $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
        't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
        'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
        'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z');

    // 在 $chars 中随机取 $length 个数组元素键名
    $keys = array_rand($chars, $length);

    $password = '';
    for($i = 0; $i < $length; $i++) {
        // 将 $length 个数组元素连接成字符串
        $password .= $chars[$keys[$i]];
    }

    return $password;
}
/**
 * 获取配置表的值 config
 *
 *
 */
function get_config($code)
{
    $value = Illuminate\Support\Facades\DB::table('config')->where('code',$code)->value('values');
    return $value;
}
// 返回随机数
function get_rand($min,$max)
{
    return mt_rand($min,$max);
}

/**
 * 过滤敏感词
 */
function filterString($str){
    if (!$str){
        return '';
    }
    $filter_str = $str;
    $filter_str = str_replace(' ','',$filter_str);
    $filter_str = str_replace('丶','',$filter_str);
    $filter_str = str_replace('·','',$filter_str);
    $filter_str = str_replace('丨','',$filter_str);
    $filter_str = str_replace('灬','',$filter_str);
    $filter_str = str_replace('氵','',$filter_str);
    $filter_str = str_replace('艹','',$filter_str);
    $filter_str = str_replace('了','',$filter_str);
    $filter_str = str_replace('勒','',$filter_str);
    $filter_str = str_replace('个','',$filter_str);
//    $sensitive_words=['你','我','他'];
    $sensitive_words = \Illuminate\Support\Facades\DB::table('sensitive_word')
        ->where('is_valid',1)
        ->pluck('word')
        ->toArray();
    $sensitive_words_arr = array_combine($sensitive_words, array_fill(0, count($sensitive_words), '**'));
//    var_dump($sensitive_words_arr);die;
    $res = strtr($filter_str, $sensitive_words_arr);
    if ($res == $filter_str ){
        return $str;
    }else{
        return false;
    }
}

