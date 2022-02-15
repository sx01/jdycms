<?php
namespace app\common\lib;

class Time {

    public static function userLoginExpiresTime($type = 2){
        $type = !in_array($type,[0,1,2])?2:$type;
        if($type == 1){
            $day = $type * 7;
        }elseif ($type == 2){
            $day = $type * 30;
        }else{
            $day = 1;
        }
        return $day * 24 * 3600;
    }
}