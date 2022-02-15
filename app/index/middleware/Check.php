<?php 


namespace app\index\middleware;
class Check {
    public function handle($request, \Closure $next){
         $request->type = " abc ";
        return $next($request);
    }
    /**
     * 中间件结束调度
     */
    public function enc(\think\Response $response){

    }
}