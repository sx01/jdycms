<?php  

namespace app\admin\middleware;
class Auth {
    public function handle($request, \Closure $next){
 

        // if(empty(session(config("admin.session_admin"))) && !preg_match("/login/",$request->pathinfo()) ){
        //     return redirect(url('login/index'));
        // }

        //后置中间件
        $response = $next($request);
        //  if(empty(session(config("admin.session_admin"))) && $request->controller() != "Login"){
        //     return redirect(url('login/index'));
        // }
        return  $response;
    }
    /**
     * 中间件结束调度
     */
    public function end(\think\Response $response){

    }
}