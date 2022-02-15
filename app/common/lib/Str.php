<?php
namespace app\common\lib;
class Str {
    public static function getLoginToken($string){
        //生产token
        $str = md5(uniqid(md5(microtime(true))),true); //生成一个不会重复的 字符串
        $token = sha1($str.$string); //加密
        return $token;
    }

    public static function getRandom(){
    
        $str="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = "";
        for($i=0;$i<32;$i++)
         {
             $key .= $str[mt_rand(0,32)];    //生成php随机数
         }
         return $key;
   
    }

    //生成 sha256WithRSA 签名
    public static function getSign($content, $privateKey){

        openssl_sign($content, $binary_signature, file_get_contents($privateKey), OPENSSL_ALGO_SHA256);

     
    $sign = base64_encode($binary_signature);
    return $sign;
}

 

}