<?php

namespace app\admin\controller;

use think\facade\Session;
use think\facade\View;
use app\BaseController;
use think\exception\HttpResponseException;
use app\common\lib\Show;
use think\Response;
class Base  extends BaseController
{

    public function initialize()
    {
        parent::initialize();
     

        define('ADMINUID', Session::has('uid') ? Session::get('uid') : null);
        $kvlist = getkvall();
        View::assign('kvlist', getkvall()); //获得基本信息
        View::assign('title', $kvlist['网站名称']);
        View::assign('controllentname', $this->request->controller());
        View::assign('modulename', \think\facade\App::initialize()->http->getName());
        View::assign('actionname',$this->request->action());
        if($this->request->controller()=="User"){
        }else{
        if(empty($this->isLogin())){
            return $this->redirect(url("login/index"),302);
           
        }
        }
        
    }
    public function isLogin()
    {

        $request = $this->request;
        View::assign('controllentname', $request->controller());
        View::assign('actionname', $request->action());
        $uid = ADMINUID;
        if (is_null(ADMINUID)) {
            $this->error('用户未登陆,无权访问', url('user/login'));
        } else {
            $lasttime = Session::has('lasttime') ? Session::get('lasttime') : 0;
            $time = time();
            if ($time - $lasttime > 7200) {
                $this->error('登录超时，请重新登录！', url('user/logout'));
            } else {
                Session::set('lasttime', time());
            }
        }

        //查询条件
        $map = [
            'uid' => ADMINUID,

        ];

        //数据表查询,返回模型对象
        $member = \app\common\model\mysql\Members::where($map)->find();



        $qxkz = 3;

        if ($member['qx']==100 and $member['status']==1 and $member['groupid'] < $qxkz) {
            Session::set('nickname', $member['nickname']);
            Session::set('groupid', $member['groupid']);
            $nickname = Session::has('nickname') ? Session::get('nickname') : "";
            $groupid =   Session::has('groupid') ? Session::get('groupid') : 0;
            View::assign('uid', ADMINUID);
            View::assign('member', array("uid" => ADMINUID, "nickname" => $nickname, "groupid" => $groupid
            ,"login_count"=>$member['login_count'],"lastloginip"=>$member['lastloginip'],"lastlogintime"=>$member['lastlogintime']));
            return $member;
        } else {
            $this->error('用户已登陆成功,无权访问，请联系管理员', url('user/logout'));
        }
    }


    //防止用户重复登陆,放在登陆操作前面:user/login
    public function alreadyLogin()
    {
        if (ADMINUID) {
            $this->error('用户已经登陆,请勿重复登陆', url('index/index'));
        }
    }

    public function redirect(...$args)
    {

        // throw new HttpResponseException(Response::create(url('login/index'), 'redirect', 302));
       
         throw new HttpResponseException(redirect(...$args));
    }
}
