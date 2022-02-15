<?php

namespace app\index\controller;
use think\facade\Session;
use app\BaseController;
use think\exception\HttpResponseException;
use think\facade\View;
use app\common\business\Kv as KvBus;
use app\common\lib\Jssdk;

class IndexBase extends BaseController
{


    public $kvlist = null;
    public $uid = 0;
    public $avatar = "";
    public $nickname = "";
    public  $member = null; 
    
    public function initialize()
    {
        parent::initialize();
        define('ADMINUID', Session::has('uid') ? Session::get('uid') : null);
        $kvlist = getkvall();
        View::assign('kvlist', getkvall()); //获得基本信息
        View::assign('title', $kvlist['网站名称']);
        View::assign('menuname', ""); 
        View::assign('controllentname', $this->request->controller());
        View::assign('modulename', \think\facade\App::initialize()->http->getName());
        View::assign('actionname',$this->request->action());
        // if($this->request->controller()=="Login"){
        // }else{
        // // if(empty($this->isLogin())){
        // //     return $this->redirect(url("login/index"),302);
        // // }
        // }
 //查询条件
 $map = [
    'uid' => ADMINUID,

];

//数据表查询,返回模型对象
        $member = \app\common\model\mysql\Members::where($map)->find();
        Session::set('nickname', $member['nickname']);
        Session::set('groupid', $member['groupid']);
        $nickname = Session::has('nickname') ? Session::get('nickname') : "";
        $groupid =   Session::has('groupid') ? Session::get('groupid') : 0;
        View::assign('uid', ADMINUID);
        $this->uid = ADMINUID;
        $this->nickname =$nickname;
        View::assign('nickname', $nickname);

        $avatar = $member['avatar'];
        $this->avatar = $avatar;
        View::assign('avatar', $avatar);
        $this->member = $member;
        View::assign('member', array("uid" => ADMINUID, "nickname" => $nickname, "groupid" => $groupid
        ,"login_count"=>$member['login_count'],"lastloginip"=>$member['lastloginip'],"lastlogintime"=>$member['lastlogintime']));
    


        $appid =  config("weixin.APPID");
        $secret =  config("weixin.SECRET");



        $jssdk = new Jssdk($appid, $secret);

        $signPackage = $jssdk->GetSignPackage();
        View::assign('signPackage', $signPackage);



        $shareurl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];



        $sharetitle = isset($this->kvlist['网站名称']) ? $this->kvlist['网站名称'] : '';
        $shareheadurl = 'http://' . $_SERVER['HTTP_HOST'] . "/Public/images/shareheader.png";
        $sharedesc = isset($this->kvlist['网站描述']) ? $this->kvlist['网站描述'] : '';

     

        View::assign('shareurl', $shareurl);
        View::assign('sharetitle', $sharetitle);
        View::assign('shareheadurl', $shareheadurl);
        View::assign('sharedesc', $sharedesc);
       
     
    }

    public function isLogin()
    {
        $request = $this->request;
   
      
        if (is_null(ADMINUID)) {
            $this->error('用户未登陆,无权访问', url('login/index'));
        } else {
            $lasttime = Session::has('lasttime') ? Session::get('lasttime') : 0;
            $time = time();
            if ($time - $lasttime > 7200) {
                $this->error('登录超时，请重新登录！', url('login/logout'));
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
        
            Session::set('nickname', $member['nickname']);
            Session::set('groupid', $member['groupid']);
            $nickname = Session::has('nickname') ? Session::get('nickname') : "";
            $groupid =   Session::has('groupid') ? Session::get('groupid') : 0;
            View::assign('uid', ADMINUID);
            $this->uid = ADMINUID;
            $this->nickname =$nickname;
            View::assign('nickname', $nickname);
                //数据表查询,返回模型对象
 
            View::assign('member', array("uid" => ADMINUID, "nickname" => $nickname, "groupid" => $groupid
            ,"login_count"=>$member['login_count'],"lastloginip"=>$member['lastloginip'],"lastlogintime"=>$member['lastlogintime']));
            return $member;
        

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
      throw new HttpResponseException(redirect(...$args));
  }
}
