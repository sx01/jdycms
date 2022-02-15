<?php
/**
 * Created by PhpStorm.
 * User: sunxin
 * Date: 18/2/27
 * Time: 10:33
 */

namespace app\index\controller;



use app\common\model\mysql\Members;
 
 use think\facade\View;


class User  extends IndexBase {


    //登陆界面
    public function index()
    {
         


    

            return View::fetch("");
       

    }

  
 
    public function editprofile()
    {
        $request = $this->request;
     
        $updatepic = $request->param("updatepic");
        View::assign('updatepic', $updatepic);
      

     
            $uid = $this->uid;

        
          
            /* 获取用户数据 */

            $member = Members::where("uid=$uid")->find(); 
         
         
            if ($request->isPost()) {
                                //前台logo开始
            // 捕获异常
            try {
                // 此时可能会报错
                // 比如:上传的文件过大,超出了配置文件中限制的大小
                $file = request()->file('pic');
                // 如果表单没有设置文件上传需要的编码 $file始终是null
                if (is_null($file)) {
                    // 请检查请求类型和表单编码
                    // 不是post请求或没有指定enctype="multipart/form-data"会进入这里
                    // throw new \think\Exception('没有文件上传');
                } else {
                    $dir = $this->app->getRootPath() . 'public/uploads';

                    !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                    $dir = $this->app->getRootPath() . 'public/uploads/avatar';

                    !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                    // 使用验证器验证上传的文件
                    validate(
                        [
                            'pic' => [
                                // 限制文件大小(单位b)，这里限制为4M
                                'fileSize' => 5 * 1024 * 1024,
                                // 限制文件后缀，多个后缀以英文逗号分割
                                'fileExt'  => 'gif,jpg,png'
                            ]
                        ],
                        [
                            'pic.fileSize' => '文件太大',
                            'pic.fileExt' => '不支持的文件后缀',
                        ]
                    )->check(['pic' => $file]);

                    $savename = "avatar_".$uid;

                    if ($savename) {
                        $filename = \think\facade\Filesystem::disk("public")->putFileAs('avatar', $file, $savename . '.' . $file->extension());
                    } else {
                        $filename = \think\facade\Filesystem::disk("public")->putFile("avatar", $file);
                    }



                    // 直接这样即可：
                    // $dir = ROOT_PATH .    'uploads' . DS . "logo";
                    // is_dir($dir) OR mkdir($dir, 0755, true);   // 如果文件夹不存在，将以递归方式创建该文件夹
                    // $info = $file->validate(['size' => 2 * 1024 * 1024, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . "logo", "logo");

                    if ($filename) {
                        // 成功上传后 获取上传信息
                        // 输出 jpg
                        // echo $info->getExtension();
                        // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                        //echo $info->getSaveName();
                        // 输出 42a79759f284b767dfcb2a0197904287.jpg
                        //echo $info->getFilename();

                        $path = '/uploads/' . $filename;


                        $data_img['avatar'] = $path;

                      

                        $result = \app\common\model\mysql\Members::where("uid = $uid ")->update($data_img);
                         
                    }
                }
            } catch (\think\Exception $e) {
                // 获取异常错误信息
                // halt($e->getMessage());
            }
            //前台logo结束

                    $oldpassword = $request->param('old');
                    $password = $request->param('password');
                    $repassword = $request->param('repassword');
                    //  $mobile = I('mobile');

                    // empty($password) && $this->error('请输入原密码');
                    // empty($data['password']) && $this->error('请输入新密码');
                    // empty($repassword) && $this->error('请输入确认密码');
                    $data = array();

                    if ($password) {


                        if ($password !== $repassword) {

                            return json(array('status' => "0", 'tishi' => "您输入的新密码与确认密码不一致！"));
                        } else {
                            $data['password'] = $password;
                        }
                    }


                    if ($member) {

                        $datauser = array(

                            //  'mobile'=>$mobile,
                            //'update_time'=>time()
                        );

                        if ($oldpassword && $data['password']) {

                            /* 验证用户密码 */

                            $passwordmd5 = preg_match('/^\w{32}$/', $oldpassword) ? $oldpassword : md5($oldpassword);
                            if ($member['password'] != md5($passwordmd5 . $member['salt'])) {

                                return json(array('status' => "0", 'tishi' => "修改失败，原密码不正确！！"));
                            } else {
                                $data['password'] = md5($data['password']);
                                $datauser['password'] = md5($data['password'] . $member['salt']);
                            }


                        }
                         
                          
                        if ($request->param('phone')) {
                            $datauser['phone'] = $request->param('phone');
                        }

                        if ($request->param('sex')) {
                            $datauser['sex'] = $request->param('sex');
                        }

                        if ($request->param('realname')) {
                            $datauser['realname'] = $request->param('realname');
                        }
                        $nickname = $request->param('nickname');

                    
                        

                        $res = 0;

                        if ($nickname) {
                            $datauser['nickname'] = $nickname;

                        }
                        
                        if ($request->param('weixin')) {
                            $datauser['weixin'] = $request->param('weixin');
                        }
                        if ($request->param('phone')) {
                            $datauser['phone'] = $request->param('phone');
                        }

                        $res = Members::where("uid=$uid")->update($datauser);
                        if ($res === false) {


                            $this->error('修改资料失败！', url("index/user/editprofile"));

                        } else {


                      $this->success('修改资料成功！', url('index/user/editprofile', array('new' => "x", 'updatepic' => time())));
                           

                        }

                    }

               

            } else {
              
                View::assign('member', $member);
                return View::fetch();  //渲染首页模板
            }

        
    } 
}