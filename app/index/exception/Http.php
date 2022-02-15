<?php 
namespace app\index\exception;
use think\exception\Handle;
use think\Response;
use Throwable;
class Http extends Handle{
    public $httpStarts = 500;

     /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        if(method_exists($e,"getStatusCode")){
           $httpStarts = $e->getCode();
        }else{
            $httpStarts = $this->httpStarts;
        }
        // 添加自定义异常处理机制
          return show(config("status.error"),$e->getMessage(),[],$httpStarts);
        // 其他错误交给系统处理
        // return parent::render($request, $e);
    }

}
