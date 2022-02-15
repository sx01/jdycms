<?php
namespace app\api\controller;
class Error{
    public function __call($name,$arguments){
           //逻辑：如果API找不到方法，需要输出API的数据格式
        //    $result = [
        //     'status'=>0,
        //     'message'=>"找不到该控制器！",
        //     'result'=>null,
        // ];
        return show(config("status.controller_not_found"),"找不到该控制器！",404);
    }
}