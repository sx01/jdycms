<?php 
namespace app\common\lib;

class Show {
    /**
     *  成功提示
     */
    public static function success($data = [],$message = "OK"){
        $result = [
            "status"=>config("status.success"),
            "message"=>$message,
            "result"=>$data
        ];
        return json($result);
    }

     /**
     *  失败提示
     */
    public static function error($message = "OK",$status = 0,$data = []){
        $result = [
            "status"=>$status,
            "message"=>$message,
            "result"=>$data
        ];
        return json($result);
    }

}