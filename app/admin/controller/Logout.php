<?php 

namespace app\admin\controller;

class Logout extends AdminBase{

    public function index(){

        //清除session
    
        session(config("admin.session_admin"),null);

        // halt(session(config("admin.session_admin")));
    
        return redirect(url("login/index"));
    }

}