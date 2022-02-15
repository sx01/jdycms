<?php 


namespace app\index\middleware;
class Detail {
    public function handle($request, \Closure $next){
         $request->type = " detail ";
        return $next($request);
    }
    /**
     * 中间件结束调度
     */
    public function end(\think\Response $response){

    }
}