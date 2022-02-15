<?php

namespace app\index\controller;

use think\facade\Session;
use  think\facade\View;
use think\Request;

use app\common\model\mysql\Members;

class Login  extends IndexBase
{

    public function dengdaishenhe()
    {
        View::assign('title', '用户登录');


        $uid = Session::has('uid') ? Session::get('uid') : null;
        View::assign('uid', $uid);

        if ($uid) {
            //数据表查询,返回模型对象
            $member = \app\common\model\mysql\Members::where("uid=$uid")->find();


            View::assign('nickname',  $member['nickname']);

            if ($member['status'] == 1) {
                $this->success('已经通过审核', url('index/index'));
            }

            return View::fetch();
        } else {
            $this->success('登录超时', url('login/index'));
        }
    }
    public function index()
    {

        $this->alreadyLogin();
        View::assign('title', '用户登录');


        $uid = Session::has('uid') ? Session::get('uid') : null;
        View::assign('uid', $uid);

        try {
            $MembersCats =  \app\common\model\mysql\MembersCat::select();
        } catch (\Exception $e) {
            $MembersCats = [];
        }
        View::assign('catlist',  \app\common\lib\Tree::tree($MembersCats));
        return View::fetch();
    }



    public function ajaxlogin(Request $request)
    {
        $username = input("username", "", 'trim');
        if ($username) {
            if ($request->isAjax()) {

                $tmpadmin = Members::where("`username`='$username'")->find();

                $tmpid = $tmpadmin['uid'];

                if (!$tmpid) {
                    $data['status'] = 0;
                    $data['tishi'] = "用户名不存在，请检查，如果没有注册请先注册！";
                    return $data;
                } else {





                    // $verify = I('param.verify', '');
                    // if (!check_verify($verify)) {
                    //     $this->error("亲，验证码输错了哦！", $this->site_url, 1);
                    // }


                    $password = input("password", "", 'trim');

                    $uparr = Members::where("username='$username'")->find();



                    if ($uparr['password'] ==  md5(md5($password) . $uparr['salt'])) {


                        //创建2个session,用来检测用户登陆状态和防止重复登陆
                        Session::set('uid', $uparr['uid']);
                        Session::set('nickname', $uparr['nickname']);
                        Session::set('groupid', $uparr['groupid']);
                        Session::set('lasttime', time());
                        Session::set('user_info',  $uparr);
                        //更新用户登录次数:自增1
                        Members::where("uid= $uparr[uid]")->update(['login_count' => $uparr['login_count'] + 1]);
                        $data['status'] = 1;
                        $data['tishi'] = "登录成功！";
                        return $data;
                    } else {


                        $data['status'] = 0;
                        $data['tishi'] = "用户名和密码不正确！";

                        return $data;
                    }
                }
            } else {
                $data['status'] = 0;
                $data['tishi'] = "非法提交！";

                return $data;
            }
        } else {
            $data['status'] = 0;
            $data['tishi'] = "用户名不能为空！";

            return $data;
        }
    }

    //注册用户名
    public function ajaxreg(Request $request)
    {

        $username = input("username", "", "trim");
        if ($username) {
            if ($request->isAjax()) {

                $tmpadmin = Members::where("`username`='$username'")->find();

                $tmpid = $tmpadmin['uid'];

                if ($tmpid) {
                    $data['status'] = 0;
                    $data['tishi'] = "已经存在用户名，请再换一个！";
                    return $data;
                } else {

                    $Varr = $request->param();

                    $Varr['regdate'] = time();
                    $Varr['regip'] = $request->ip();

                    $password = $Varr['password'];
                    $repassword = $Varr['repassword'];
                    if ($password) {
                        if (!preg_match("/^[a-zA-Z0-9]{6,16}$/", $password)) {
                            $data['status']  = 0;
                            $data['tishi']    = "密码由6-16位数字和字母组成";
                            return $data;
                        }
                        if ($password == $repassword) {
                        } else {

                            $data['status'] = 0;
                            $data['tishi'] = "两次输入密码不一致，请重试！";
                            return $data;
                        }
                    } else {
                        $data['status'] = 0;
                        $data['tishi'] = "密码不能为空！";
                        return $data;
                    }

                    unset($Varr['repassword']);

                    $salt = mt_rand(100000, 999999);
                    $Varr['salt'] = $salt;

                    if ($password) {
                        $Varr['password'] = md5(md5($password) . $salt);
                    } else {
                        $data['status'] = 0;
                        $data['tishi'] = "密码不能为空！";
                        return $data;
                    }
                    $addid = Members::create($Varr);
                    if ($addid) {
                        $data['status'] = 1;
                        $data['tishi'] = "注册成功，请登录！";
                        return $data;
                    } else {
                        $data['status'] = 0;
                        $data['tishi'] = "注册失败,稍后再试一下！";
                        return $data;
                    }
                }
            } else {
                $data['status'] = 0;
                $data['tishi'] = "非法提交！";

                return $data;
            }
        } else {
            $data['status'] = 0;
            $data['tishi'] = "用户名不能为空！";

            return $data;
        }
    }



    //验证用户名是否存在
    public function checkusername(Request $request)
    {

        $username = input("username", "", 'trim');
        if ($username) {
            if ($request->isAjax()) {

                $tmpadmin = Members::where("`username`='$username'")->find();

                $tmpid = $tmpadmin['uid'];

                if ($tmpid) {




                    $data['status'] = 1;
                    $data['tishi'] = "已经存在用户名，请再换一个！";

                    return $data;
                } else {
                    $data['status'] = 0;
                    $data['tishi'] = "用户名可以注册！";

                    return $data;
                }
            } else {
                $data['status'] = 0;
                $data['tishi'] = "非法提交！";

                return $data;
            }
        } else {
            $data['status'] = 0;
            $data['tishi'] = "用户名不能为空！";

            return $data;
        }
    }





    //退出登陆
    public function logout(Request $request)
    {
        $ip = $request->ip();


        //退出前先更新登录时间字段,下次登录时就知道上次登录时间了
        Members::update(['lastlogintime' => time(), 'lastloginip' => $ip], ['uid' => Session::get('uid')]);


        Session::set('uid', null);
        Session::set('nickname', null);
        Session::set('groupid', null);
        Session::set('lasttime', time());
        Session::set('user_info', null);

        Session::delete('uid');
        Session::delete('nickname');
        Session::delete('groupid');
        Session::delete('user_info');
        Session::delete('lasttime');

        $this->success('注销登陆,正在返回', url('index/login/index'));
    }
}
