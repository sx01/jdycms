<?php 
namespace app\api\exception;
use think\exception\Handle;

use think\Response;
use Throwable;

class Http extends Handle {

    public $httpStatus = 500;
    public function render($request,Throwable $e):Response {
          if($e instanceof \think\Exception){
              return show($e->getCode(),$e->getMessage());
          }
          
          if($e instanceof \think\exception\HttpResponseException){
              return parent::render($request,$e);
          }

          if(method_exists($e,"getStatusCode")){
              $httpStatus = $e->getCode();
          }else{
              $httpStatus = $this->httpStatus;
          }
          return show(config("status.error"),$e->getMessage(),[],$httpStatus);
    }
}