<?php

namespace app\admin\controller;

use app\common\model\mysql\Kv;

use think\facade\Request;
use think\facade\View;


class Basicinformation  extends Base
{
    //添加基本信息
    public function addkv()
    {
        $this->isLogin();  //判断用户是否登录
        View::assign('title', '基本信息');


        $request = $this->request;

        $kvlist = Kv::select();
        $tmplist = array();
        foreach ($kvlist as $k => $v) {
            $tmplist[$v['key']] = $v['value'];
        }
        View::assign('kvlist', $tmplist); // 赋值数据集

        if ($request->isPost()) {


            $Varr = $request->param();


            foreach ($Varr as $k => $v) {

                if ($k && $k != "logo" && $k != "houtailogo") {



                    $kv = Kv::where("`key`='$k'")->find();
                    if ($kv['id']) {
                        $id = Kv::where("id=$kv[id]")->update(array("value" => $v));
                    } else {
                        $addres = Kv::create(array('key' => $k, "value" => $v));
                    }
                }
            }




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

                    $dir = $this->app->getRootPath() . 'public/uploads/logo';

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

                    $savename = "logo";

                    if ($savename) {
                        $filename = \think\facade\Filesystem::disk("public")->putFileAs('logo', $file, $savename . '.' . $file->extension());
                    } else {
                        $filename = \think\facade\Filesystem::disk("public")->putFile("logo", $file);
                    }



                    // 直接这样即可：
                    // $dir = ROOT_PATH . 'public' . DS . 'uploads' . DS . "logo";
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


                        $data_img['value'] = $path;


                        $kv = Kv::where("`key`='前台logo'")->find();
                        if ($kv['id']) {
                            Kv::where("id=$kv[id]")->update($data_img);
                        } else {
                            Kv::create(array('key' => "前台logo", 'value' => $path));
                        }


                        $resurtarr = array('jg' => 1);
                    }
                }
            } catch (\think\Exception $e) {
                // 获取异常错误信息
                // halt($e->getMessage());
            }
            //前台logo结束
            // 后台logo开始

            try {
                $houtailogo = request()->file('houtailogo');
                if (is_null($houtailogo)) {
                    // 请检查请求类型和表单编码
                    // 不是post请求或没有指定enctype="multipart/form-data"会进入这里
                    // throw new \think\Exception('没有文件上传');
                } else {

                    $dir = $this->app->getRootPath() . 'public/uploads';

                    !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                    $dir = $this->app->getRootPath() . 'public/uploads/logo';

                    !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                    // 使用验证器验证上传的文件
                    validate(
                        [
                            'houtailogo' => [
                                // 限制文件大小(单位b)，这里限制为4M
                                'fileSize' => 5 * 1024 * 1024,
                                // 限制文件后缀，多个后缀以英文逗号分割
                                'fileExt'  => 'gif,jpg,png'
                            ]
                        ],
                        [
                            'houtailogo.fileSize' => '文件太大',
                            'houtailogo.fileExt' => '不支持的文件后缀',
                        ]
                    )->check(['houtailogo' => $houtailogo]);

                    $savename = "houtailogo";

                    if ($savename) {
                        $filename = \think\facade\Filesystem::disk("public")->putFileAs('logo', $houtailogo, $savename . '.' . $houtailogo->extension());
                    } else {
                        $filename = \think\facade\Filesystem::disk("public")->putFile("logo", $houtailogo);
                    }



                    // 直接这样即可：
                    // $dir = ROOT_PATH . 'public' . DS . 'uploads' . DS . "logo";
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


                        $data_img['value'] = $path;


                        $kv = Kv::where("`key`='后台logo'")->find();
                        if ($kv['id']) {
                            Kv::where("id=$kv[id]")->update($data_img);
                        } else {
                            Kv::create(array('key' => "后台logo", 'value' => $path));
                        }
                    }
                }
            } catch (\think\Exception $e) {
                // 获取异常错误信息
                // halt($e->getMessage());
            }

            $this->success("修改成功！", url('addkv'), 3);
        } else {




            $ip = $request->ip();

            View::assign('ip', $ip);
            return View::fetch();  //渲染首页模板
        }
    }

    //删除基本信息
    public function delkv()
    {
        $request = $this->request;
        $this->isLogin();  //判断用户是否登录
        View::assign('title', '爱特豆用户管理系统');


        if ($request->isAjax()) {

            $key = $request->param("key");
            $kv = Kv::where("`key`='$key'")->find();

            if ($kv['id']) {

                $r = array();
                $res = Kv::where("`key`='$key'")->delete(); // 删除id为5的用户数据
                if ($res) {


                    $r['status'] = 1;
                    $r['tishi'] = "删除成功！";
                } else {

                    $r['status'] = 0;
                    $r['tishi'] = "删除失败！";
                }
            } else {
                $r['status'] = 0;
                $r['tishi'] = "要删除的字段不存在！";
            }
            return json($r);
        }
    }

    public function index(Request $request)
    {
        $this->isLogin($request);  //判断用户是否登录
        View::assign('title', '爱特豆用户管理系统');




        return View::fetch();  //渲染首页模板
    }

    public function _empty($method)
    {

        return '访问的方法' . $method . '不存在';
    }
}
