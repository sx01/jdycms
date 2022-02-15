<?php
namespace app\common\lib;
/**
 * Num  记录与数字有关的方法
 */
class Num{
    public static  function getCode($len=4){
        $code = rand(1000,9999);
        if($len == 6){
            $code = rand(100000,999999);
        }
        return $code;
    }
}