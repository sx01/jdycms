<?php
namespace app\admin\controller;

use app\BaseController;
use think\exception\HttpResponseException;

class AdminBase extends BaseController{

    public $adminUser = null;

    public function initialize(){
        parent::initialize();
        //判断是否登陆

        if(empty($this->isLogin())){
         return $this->redirect(url("login/index"),302);
        }

    }

    public function isLogin(){
           $this->adminUser = session(config("admin.session_admin"));
           if(empty($this->adminUser)){
               return false;
           }
           return true;


    }

    public function redirect(...$args) {
        throw new HttpResponseException(redirect(...$args));
    }
}