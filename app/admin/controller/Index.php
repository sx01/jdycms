<?php

namespace app\admin\controller;
 
use think\facade\View;

class Index extends Base
{
    public function index()
    {
        // $this->isLogin();  //判断用户是否登录
        View::assign('title', config("siteinfo.websitename").'管理系统');
        $ip = $this->request->ip(0);

        View::assign('ip', $ip);
    
        return View::fetch('index');
    }

    public function welcome(){
        return View::fetch();
    }

   
}
