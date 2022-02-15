<?php
/**
 * Created by PhpStorm.
 * User: sunxin
 * Date: 18/2/27
 * Time: 10:33
 */

namespace app\admin\controller;



use app\common\model\mysql\Members;

use think\Request;
use think\facade\Session;
use think\facade\View;
class User  extends Base {
    //登陆界面
    public function login()
    {
        $this -> alreadyLogin();
       View::assign('title', '用户登录'); 

       $uid = Session::has('uid') ? Session::get('uid'):null;
      View::assign('uid', $uid);

   

        return View::fetch();
    }

    //登录验证
    public function checkLogin(Request $request)
    {
        $status = 0; //验证失败标志
        $result = '验证失败'; //失败提示信息
        $data = $request -> param();


        $data = [
            'name' => $data['name'],
            'password' => $data['password'],
            'captcha' =>$data['captcha'],
        ];
        $validate = new \app\admin\validate\AdminUser();
       
           $vres = $validate->check($data);
         if(!$vres){
              return show(config("status.error"),$validate->getError());
          }


       

        //通过验证后,进行数据表查询
        //此处必须全等===才可以,因为验证不通过,$result保存错误信息字符串,返回非零
         

            //查询条件
            $map = [
                'username' => $data['name'],
                //'password' => md5($data['password'])
            ];

            //数据表查询,返回模型对象
            $user = Members::where($map)->find();


            if (null === $user) {
                $result = '没有该用户,请检查';
            } else {




              if ($user['password'] == md5(md5($data['password']) . $user['salt'])) {


                $status = 1;
                $result = '验证通过,点击[确定]后进入后台';

                //创建2个session,用来检测用户登陆状态和防止重复登陆
                Session::set('uid', $user -> uid);
                Session::set('lasttime', time());
                Session::set('user_info', $user -> getData());

                //更新用户登录次数:自增1
                Members::where("uid=$user[uid]")->update(['login_count'=>$user['login_count']+1]);

                    }else{
                        $result = '用户名密码不正确！';
                    }
            }
        
        return ['status'=>$status, 'message'=>$result, 'data'=>$data];
    }

    //退出登陆
    public function logout(Request $request)
    {
        $ip = $request->ip();


        //退出前先更新登录时间字段,下次登录时就知道上次登录时间了
        Members::update(['lastlogintime'=>time(),'lastloginip'=>$ip],['uid'=> Session::get('uid')]);
        Session::delete('uid');
        Session::delete('user_info');
        Session::delete('lasttime');
        $this -> success('注销登陆,正在返回',url('user/login'));
    }





    //管理员列表
    public function  adminList(Request $request)
    {
       View::assign('title', '管理员列表');
      
      
  

        //判断当前是不是admin用户
        //先通过session获取到用户登陆名
        $userName = Session::get('user_info.name');
        if ($userName == 'admin') {
            $list = Members::all();  //admin用户可以查看所有记录,数据要经过模型获取器处理
        } else {
            //为了共用列表模板,使用了all(),其实这里用get()符合逻辑,但有时也要变通
            //非admin只能看自己信息,数据要经过模型获取器处理
            $list = Members::all(['name'=>$userName]);
        }


       View::assign('list', $list);
        //渲染管理员列表模板
        return View::fetch('admin_list');
    }

    //管理员状态变更
    public function setStatus(Request $request)
    {
        $user_id = $request -> param('id');
        $result = Members::where(['uid'=>$user_id])->find();
        if($result->getData('status') == 1) {
            Members::update(['status'=>0],['id'=>$user_id]);
        } else {
            Members::update(['status'=>1],['id'=>$user_id]);
        }
    }

    //渲染编辑管理员界面
    public function adminEdit(Request $request)
    {
        $user_id = $request -> param('id');
        $result = Members::where(['uid'=>$user_id])->find();
        $this->view->assign('title','编辑管理员信息');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');
        $this->view->assign('user_info',$result->getData());
        return $this->view->fetch('admin_edit');
    }

    //更新数据操作
    public function editUser(Request $request)
    {
        //获取表单返回的数据
//        $data = $request -> param();
        $param = $request -> param();

        //去掉表单中为空的数据,即没有修改的内容
        foreach ($param as $key => $value ){
            if (!empty($value)){
                $data[$key] = $value;
            }
        }

        $condition = ['uid'=>$data['uid']] ;
        $result = Members::update($data, $condition);

        //如果是admin用户,更新当前session中用户信息user_info中的角色role,供页面调用
        if (Session::get('user_info.name') == 'admin') {
            Session::set('user_info.role', $data['role']);
        }


        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败,请检查'];
        }
    }

    //删除操作
    public function deleteUser(Request $request)
    {
        // $user_id = $request -> param('id');
        // Members::update(['is_delete'=>1],['id'=> $user_id]);
        // Members::destroy($user_id);

    }

    //恢复删除操作
    public function unDelete()
    {
        // UserModel::update(['delete_time'=>NULL],['is_delete'=>1]);
    }

    //添加操作的界面
    public function  adminAdd()
    {
        // $this->view->assign('title','添加管理员');
        // $this->view->assign('keywords','php.cn');
        // $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');
        // return $this->view->fetch('admin_add');
    }

    //检测用户名是否可用
    public function checkUserName(Request $request)
    {
        $userName = trim($request -> param('name'));
        $status = 1;
        $message = '用户名可用';
        if (Members::where(['name'=> $userName])->find()) {
            //如果在表中查询到该用户名
            $status = 0;
            $message = '用户名重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

    //检测用户邮箱是否可用
    public function checkUserEmail(Request $request)
    {
        $userEmail = trim($request -> param('email'));
        $status = 1;
        $message = '邮箱可用';
        if (Members::where(['email'=> $userEmail])->find()) {
            //查询表中找到了该邮箱,修改返回值
            $status = 0;
            $message = '邮箱重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

    //添加操作
    public function addUser(Request $request)
    {
        $data = $request -> param();
        $status = 1;
        $message = '添加成功';

        $rule = [
            'name|用户名' => "require|min:3|max:10",
            'password|密码' => "require|min:3|max:10",
            'email|邮箱' => 'require|email'
        ];

        $result = $this -> validate($data, $rule);

        if ($result === true) {
            $user= Members::create($request->param());
            if ($user === null) {
                $status = 0;
                $message = '添加失败~~';
            }
        }


        return ['status'=>$status, 'message'=>$message];
    }



    public function  reg(Request $request){
        // $this -> alreadyLogin();
       View::assign('title', '用户登录');
       View::assign('keywords', config("webname").'，用户注册');
       View::assign('desc',config("webname"));
       View::assign('copyRight', config("webname"));
   
        $uid = Session::has('uid') ? Session::get('uid'):null;
       View::assign('uid', $uid);
       View::assign('tmp', $request->param("tmp"));
        return View::fetch();
    }

//添加操作
    public function regUser(Request $request)
    {
        $tmp = $request->param('tmp');
        $status = 0;
        $message = '暂时没有开放注册';

        if($tmp!="safklwjefjwjreicsafsdwelk") {


            return ['status' => $status, 'message' => $message];
        }
        $data = $request->param();
        $rule = [
            'username|用户名' => "require|min:3|max:100",
            'password|密码' => "require|min:3|max:32",
            'email|邮箱' => 'require|email',
            'captcha|验证码' => 'require|captcha'
        ];

        $result = $this -> validate($data, $rule);

        if ($result === true) {

            if($request->param("password")!=$request->param("repassword")){
                $status = 0;
                $message = '输入的密码与确认密码不相同，请重新正确输入~~';
                return ['status'=>$status, 'message'=>$message];
            }
            if (Members::get(['username'=> $request->param("username")])) {
                //如果在表中查询到该用户名
                $status = 0;
                $message = '用户名重复,请重新输入~~';
                return ['status'=>$status, 'message'=>$message];

            }
            if (Members::get(['email'=> $request->param("email")])) {
                //查询表中找到了该邮箱,修改返回值
                $status = 0;
                $message = '邮箱重复,请重新输入~~';
                return ['status'=>$status, 'message'=>$message];

            }

            $salt = substr(uniqid(rand()), -6);
            $password = md5(md5($request->param("password")).$salt);
            $regdata = array(
                'username'=>$request->param("username"),
                'password'=>$password,
                'email'=>$request->param("email")?$request->param("email"):"",
                'regdate'=>time(),
                'regip'=>$request->ip(),
                'nickname'=>$request->param("nickname")?$request->param("nickname"):"",
                'salt'=>$salt,
                'gender'=>$request->param("gender")?$request->param("gender"):0,



            );

            $user= Members::create($regdata);
            if ($user === null) {
                $status = 0;
                $message = '注册失败~~';
                return ['status'=>$status, 'message'=>$message];
            }else{
                $status = 1;
                $message = '注册成功~~';
                return ['status'=>$status, 'message'=>$message];
            }
        }else{
            $status = 0;
            $message = $result;
            return ['status'=>$status, 'message'=>$message];
        }


        return ['status'=>$status, 'message'=>$message];
    }
} 