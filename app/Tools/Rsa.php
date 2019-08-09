<?php

namespace App\Tools;

class Rsa {
    const PRIVATE_KEY = '-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBALbpwlpRwSmhdd2+
DYjsl8PMYa49Qr5Q6y/I1v3V9jLklTo7GwtRtwya7C6rCIYdiy/xjC9k8hCCpK6l
+wBxT9PTU6dS05cpZ6SyO50B4glXI7Oo10i6EA1ZNd5/veOL8egmTJb2QtIiiUDE
XETW6bB0XUx1uHj83lgYkbZTE5LrAgMBAAECgYAeDapxTJ2ASZckJ+XxaW5GLX6f
MlGVE5aQ817fthgNpKEgQDXhVjvNRmcoA9IStyf3aKiv4NYlAFIun/bty7Bhxfw8
3xACCW+qmDkp450new0qk8zoqHTqb8ShBaE85Tbaa977fI6IUbC/DVKHLu+wXAu6
XCacp7OwY128JcmdAQJBAPGh2LyUtkMJNz6D3OQe0KMr+toIND0mkV0g7oEQHij8
rau8FJg6rrTtaKP1n/7ASkCPvfnDYtIj/HGcSeexJfMCQQDByhVzUwNKXrZMctmt
wvUt9hEI1H1Y1UFLOW+PjWDPqMeJjm6xeoTNQCdK93bIZwnesH6zeH2sDRcD0Uxi
HkUpAkEAsnHzDPnKTGFu8fUR2UpEjGx7Wi963Lox2hPq71eG3oAUheJlVzLnOOin
QYaw/MLnUxyUkPJRdZ1r3j8seTXjLwJADcwPfuh4IYFgxUygBukMf7s3N0O5sOtt
/KcYNEZCkEZZ/ocvhl9MuThKE+JOiLIdV8eFHc4EVI2SB+mM4Kd7EQJBAJHYYuqJ
wvsEFuEpdJAj1e9ngvBsR6ouus1uUPclZzhqTeNtp37hrijVzCPsOPfuBGYuVoZX
1fjOdneWPJcMuTs=
-----END PRIVATE KEY-----';
    const PUBLIC_KEY = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC26cJaUcEpoXXdvg2I7JfDzGGu
PUK+UOsvyNb91fYy5JU6OxsLUbcMmuwuqwiGHYsv8YwvZPIQgqSupfsAcU/T01On
UtOXKWeksjudAeIJVyOzqNdIuhANWTXef73ji/HoJkyW9kLSIolAxFxE1umwdF1M
dbh4/N5YGJG2UxOS6wIDAQAB
-----END PUBLIC KEY-----';


    /*-----------------------------  公钥加密, 私钥解密 --------------------------------------*/
    /*
     * RSA公钥加密
     * 使用私钥解密
     */
    public static function enRSA_public($aString) {
        $pu_key = openssl_pkey_get_public(self::PUBLIC_KEY);//这个函数可用来判断公钥是否是可用的
        openssl_public_encrypt($aString, $encrypted, $pu_key);//公钥加密，私钥解密
        $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return $encrypted;
    }
    /*
     * RSA私钥解密
     * 有可能传过来的aString是经过base64加密的，则传来前需先base64_decode()解密
     * 返回未经base64加密的字符串
     */
    public static function deRSA_private($aString) {
        $pr_key = openssl_pkey_get_private(self::PRIVATE_KEY);//这个函数可用来判断私钥是否是可用的
        openssl_private_decrypt(base64_decode($aString), $decrypted, $pr_key);//公钥加密，私钥解密
        return $decrypted;
    }

    /*-----------------------------  私钥加密, 公钥解密 --------------------------------------*/
    /*
     * RSA私钥加密
     * 加密一个字符串，返回RSA加密后的内容
     * aString 需要加密的字符串
     * return encrypted rsa加密后的字符串
     */
    public static function enRSA_private($aString) {
        //echo "------------",$aString,"====";
        $pr_key = openssl_pkey_get_private(self::PRIVATE_KEY);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        openssl_private_encrypt($aString, $encrypted, $pr_key);//私钥加密
        $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        //echo "加密后:",$encrypted,"\n";
        return $encrypted;
    }
    /*
     * RSA公钥解密
     */
    public static function deRSA_public($aString) {
        $pu_key = openssl_pkey_get_public(self::PUBLIC_KEY);//这个函数可用来判断公钥是否是可用的
        openssl_public_decrypt(base64_decode($aString), $decrypted, $pu_key);//公钥加密，私钥解密
        return $decrypted;
    }
}