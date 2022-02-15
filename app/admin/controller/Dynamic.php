<?php

namespace app\admin\controller;

use app\common\business\Dynamic as DynamicBis;
use think\facade\View;
use app\common\business\DynamicCat as DynamicCatBus;

class Dynamic extends Base
{
    public function del()
    {
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];
        View::assign('title', '用户管理系统');


        // $list =  \app\common\model\mysql\DynamicCat::order('paixu desc')->select();


        // View::assign('catlist', pidtree($list));


        if ($request->isAjax()) {

            $id = $request->param("id");
            $newstype =  \app\common\model\mysql\Dynamic::where("`id`='$id'")->find();

            if ($newstype['id']) {

                $cat_id = $newstype['cat_id'];

                $res =  \app\common\model\mysql\Dynamic::where("`id`='$id'")->delete(); // 删除id为5的用户数据

                if ($res) {
                    //删除封面图片
                    @unlink("." . $newstype['pic_path']);
                    //删除所有 拓展字段内容
                    $contentlist = \app\common\model\mysql\DynamicFieldContent::where("cat_id=$cat_id and dynamic_id=$id")->select();
                    foreach ($contentlist as $k => $v) {
                        if ($v['type_id'] > 1) {
                            @unlink("." . $v['content']);
                        }
                        \app\common\model\mysql\DynamicFieldContent::where("id=$v[id]")->delete();
                    }


                    $r['status'] = 1;
                    $r['tishi'] = "删除成功！";
                } else {

                    $r['status'] = 0;
                    $r['tishi'] = "删除失败！";
                }
            } else {
                $r['status'] = 0;
                $r['tishi'] = "要删除的id不存在！";
            }
            return json($r);
        }
    }

    /**
     * 获取分类拓展字段
     */
    public function getCatFieldList()
    {
        $request = $this->request;

        View::assign('title', '用户管理系统');



        if ($request->isAjax()) {
            $cat_id = input("cat_id", "0", "intval");
            $dynamic_id = input("dynamic_id", "0", "intval");
            if ($cat_id) {
                $catFieldList =  getAllDynamicCatFieldListByCatId($cat_id);

                if ($dynamic_id) {
                    $catFieldContent = \app\common\model\mysql\DynamicFieldContent::where("cat_id=$cat_id and dynamic_id=$dynamic_id")->select();
                    $catFieldContent = array_column($catFieldContent->toArray(), NULL, 'cat_field_id');
                    foreach ($catFieldList as $k => $v) {
                        if (isset($catFieldContent[$v['id']])) {
                            $v['content'] = $catFieldContent[$v['id']]['content'];
                        } else {
                            $v['content'] = "";
                        }
                        $catFieldList[$k] = $v;
                    }
                }
            } else {
                $catFieldList = [];
            }

            return show(1, "读取成功", $catFieldList);
        } else {


            return show(0, "非法提交");
        }
    }
    public function index()
    {
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];

        $data = [];
        $name = input("param.name", "", "trim");
        $time = input("param.time", "", "trim");
        $cat_id = input("cat_id", 0, "intval");
        if ($cat_id) {
            $data['cat_id'] = $cat_id;
        }
        if (!empty($name)) {
            $data['title'] = $name;
        }
        if (!empty($time)) {
            $timearr = explode("到", $time);
            foreach ($timearr as $k => $v) {
                $timearr[$k] = strtotime(trim($v));
            }
            $data['create_time'] = $timearr;
        }



        $dynamicCatList =  getAllDynamicCatList();
        $dynamicCatList = array_column($dynamicCatList, NULL, 'id'); //二维下的id字段 为一维数组的key
        View::assign('dynamicCatList', $dynamicCatList); // 赋值数据集
        //            $count = Article_title::where($sql)->count();// 查询满足要求的总记录数
        $list = \app\common\model\mysql\Dynamic::where($data)->order('id', 'desc')->paginate(10);
        // 获取分页显示
        $page = $list->render();


        return view('', ["list" => $list->toArray(), 'page' => $page, 'name' => $name, 'time' => $time]);
    }



    public function add()
    {
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];

        try {
            $dynamicCats = (new DynamicCatBus())->getNormalDynamicCats();
        } catch (\Exception $e) {
            $dynamicCats = [];
        }

        // return View::fetch('', [
        //     "dynamicCats" => json_encode(\app\common\lib\Tree::tree($dynamicCats)),
        // ]);



        return View::fetch('', ['fanhuiurl' => url("index"), 'catlist' => \app\common\lib\Tree::tree($dynamicCats)]);
    }

    public function edit()
    {
        // 模板变量赋值
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];

        $id = input("param.id", 0, "intval");
        if (!$id) {
            $find = [];
        } else {
            try {
                $find = (new Dynamicbis())->getById($id);
                $find['pic_path'] = str_replace(request()->domain(), "", $find['pic_path']);
            } catch (\Exception $e) {
                $find = [];
            }
        }

        try {
            $dynamicCats = (new DynamicCatBus())->getNormalDynamicCats();
        } catch (\Exception $e) {
            $dynamicCats = [];
        }



        // $find["create_time"] = date("Y-m-d H:i:s",$find["create_time"]);
        return view('', [
            "find" => $find,
            "id" => $id,
            'fanhuiurl' => url("index"),
            "catlist" =>  \app\common\lib\Tree::tree($dynamicCats),
        ]);
    }

    /**
     * 新增保存数据
     */
    public function save()
    {
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];
        //判断是否post情况
        if (!$this->request->isPost()) {
            return show(config("status.error"), "参数不合法");
        }
        $id = input("param.id", 0, "intval");
        $data = input("param.");
        $check = $this->request->checkToken('__token__');
        if (!$check) {
            return show(config('status.error'), "非法请求");
        }
        unset($data['__token__']);
        $validate = new \app\admin\validate\Dynamic();
        if (!$validate->check($data)) {
            return show(config("status.error"), $validate->getError());
        }

        //数据处理 基于 我们验证成功之后
        //  $data['category_path_id'] = $data['category_path_id'];
        // $result = explode(",", $data['category_path_id']);
        // $data['category_id'] = end($result);
        // $adminUser = $this->adminUser;
        // $data['operate_user'] = $adminUser['username'];
        $data['create_time'] = strtotime($data['create_time']);
        //    unset($data['file']);


        if ($id) {
            $cat_id = $data['cat_id'];

            //查询分类拓展
            $catFieldList =  getAllDynamicCatFieldListByCatId($cat_id);

            $tuozhanfieldcontentlist = [];
            //遍历拓展字段
            foreach ($catFieldList as $k => $v) {

                if (isset($data["field_" . $v['id']])) {
                    $v['content'] = $data["field_" . $v['id']];
                    $tuozhanfieldcontentlist[] = $v;
                    //查询有没有这个拓展的内容
                    $fieldcontent =   \app\common\model\mysql\DynamicFieldContent::where("cat_id=$cat_id and dynamic_id=$id and cat_field_id=$v[id]")->find();
                    if ($fieldcontent['id']) {
                        //查到拓展内容 更新
                        \app\common\model\mysql\DynamicFieldContent::where("id=$fieldcontent[id]")->update(['content' => $v['content'], 'addtime' => time()]);
                    } else {
                        //没有查到拓展内容 添加
                        \app\common\model\mysql\DynamicFieldContent::create(['cat_id' => $cat_id, 'dynamic_id' => $id, 'cat_field_id' => $v['id'], 'type_id' => $v['type_id'], 'content' => $v['content'], 'addtime' => time()]);
                    }
                    unset($data["field_" . $v['id']]);
                }
            }


            $result =  \app\common\model\mysql\Dynamic::where("id = $id ")->update($data);


            if ($result) {




                //前台logo开始
                // 捕获异常
                try {
                    // 此时可能会报错
                    // 比如:上传的文件过大,超出了配置文件中限制的大小
                    $file = request()->file('picfile');
                    // 如果表单没有设置文件上传需要的编码 $file始终是null
                    if (is_null($file)) {
                        // 请检查请求类型和表单编码
                        // 不是post请求或没有指定enctype="multipart/form-data"会进入这里
                        // throw new \think\Exception('没有文件上传');
                    } else {
                        $dir = $this->app->getRootPath() . 'public/uploads';

                        !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                        $dir = $this->app->getRootPath() . 'public/uploads/dynamic';

                        !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                        // 使用验证器验证上传的文件
                        validate(
                            [
                                'picfile' => [
                                    // 限制文件大小(单位b)，这里限制为4M
                                    'fileSize' => 5 * 1024 * 1024,
                                    // 限制文件后缀，多个后缀以英文逗号分割
                                    'fileExt'  => 'gif,jpg,png'
                                ]
                            ],
                            [
                                'picfile.fileSize' => '文件太大',
                                'picfile.fileExt' => '不支持的文件后缀',
                            ]
                        )->check(['picfile' => $file]);

                        $savename = "dynamic_" . $id;

                        if ($savename) {
                            $filename = \think\facade\Filesystem::disk("public")->putFileAs('dynamic', $file, $savename . '.' . $file->extension());
                        } else {
                            $filename = \think\facade\Filesystem::disk("public")->putFile("dynamic", $file);
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


                            $data_img['pic_path'] = $path;



                            $result = \app\common\model\mysql\Dynamic::where("id = $id ")->update($data_img);
                        }
                    }
                } catch (\think\Exception $e) {
                    // 获取异常错误信息
                    // halt($e->getMessage());
                }
                //前台logo结束



                //遍历拓展字段
                foreach ($catFieldList as $k => $v) {

                    //如果拓展类型为图片视频文件
                    if ($v['type_id'] > 1) {



                        $filefieldname = 'file_' . $v['id'];
                        //检查上传文件开始
                        // 捕获异常 
                        try {
                            // 此时可能会报错
                            // 比如:上传的文件过大,超出了配置文件中限制的大小
                            $file = request()->file($filefieldname);
                            // 如果表单没有设置文件上传需要的编码 $file始终是null
                            if (is_null($file)) {
                                // 请检查请求类型和表单编码
                                // 不是post请求或没有指定enctype="multipart/form-data"会进入这里
                                // throw new \think\Exception('没有文件上传');
                            } else {

                                //查询有没有这个拓展的内容
                                $fieldcontent =   \app\common\model\mysql\DynamicFieldContent::where("cat_id=$cat_id and dynamic_id=$id and cat_field_id=$v[id]")->find();
                                $fieldcontentid = $fieldcontent['id'];
                                if ($fieldcontentid) {
                                    //查到拓展内容 更新
                                    // \app\common\model\mysql\DynamicFieldContent::where("id=$fieldcontent[id]")->update(['content' => "", 'addtime' => time()]);
                                } else {
                                    //没有查到拓展内容 添加
                                    $fieldcontentcreateres =   \app\common\model\mysql\DynamicFieldContent::create(['cat_id' => $cat_id, 'dynamic_id' => $id, 'cat_field_id' => $v['id'], 'type_id' => $v['type_id'], 'content' => "", 'addtime' => time()]);
                                    $fieldcontentid = $fieldcontentcreateres['id'];
                                }

                                $dir = $this->app->getRootPath() . 'public/uploads';

                                !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                                $dir = $this->app->getRootPath() . 'public/uploads/dynamicfieldcontent/';

                                !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                                $fileExt = 'gif,jpg,png';
                                $fileSize = 5 * 1024 * 1024;
                                if ($v['type_id'] == 2) {
                                    //如果类型为图片
                                    $fileExt = 'gif,jpg,png';
                                    $fileSize = 10 * 1024 * 1024;
                                } else if ($v['type_id'] == 3) {
                                    //如果类型为视频
                                    $fileExt = 'mp4,egg';
                                    $fileSize = 100 * 1024 * 1024;
                                } else {
                                    $fileExt = 'doc,docx,xls,xlsx,ppt,pptx,apk,rar,zip,pdf';
                                    $fileSize = 50 * 1024 * 1024;
                                }

                                // 使用验证器验证上传的文件
                                validate(
                                    [
                                        $filefieldname => [
                                            // 限制文件大小(单位b)，这里限制为4M
                                            'fileSize' => $fileSize,
                                            // 限制文件后缀，多个后缀以英文逗号分割 
                                            'fileExt'  => $fileExt
                                        ]
                                    ],
                                    [
                                        $filefieldname . '.fileSize' => '文件太大',
                                        $filefieldname . '.fileExt' => '不支持的文件后缀',
                                    ]
                                )->check([$filefieldname => $file]);

                                $savename = "dynamic_content_" . $id . "_" . $cat_id . "_" . $v['id'] . "_" . $fieldcontentid;

                                if ($savename) {
                                    $filename = \think\facade\Filesystem::disk("public")->putFileAs('dynamicfieldcontent', $file, $savename . '.' . $file->extension());
                                } else {
                                    $filename = \think\facade\Filesystem::disk("public")->putFile("dynamicfieldcontent", $file);
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


                                    $data_img['content'] = $path;



                                    $result = \app\common\model\mysql\DynamicFieldContent::where("id = $fieldcontentid ")->update($data_img);
                                }
                            }
                        } catch (\think\Exception $e) {
                            // 获取异常错误信息
                            // halt($e->getMessage());
                        }
                        //前台logo结束
                    }
                }
                // return show(config("status.success"), "修改成功");
                $this->success("修改成功!", url('index'), 3);
            } else {
                // return show(config("status.error"), "修改失败");
                $this->error("修改失败!", url('edit', ['id' => $id]), 3);
            }
        } else {

            $cat_id = $data['cat_id'];
            //查询分类拓展
            $catFieldList =  getAllDynamicCatFieldListByCatId($cat_id);

            $tuozhanfieldcontentlist = []; //存放有拓展内容的字段列表
            //遍历拓展字段
            foreach ($catFieldList as $k => $v) {

                if (isset($data["field_" . $v['id']])) {
                    // 有拓展字段提交的

                    $v['content'] = $data["field_" . $v['id']];
                    $tuozhanfieldcontentlist[] = $v;

                    unset($data["field_" . $v['id']]);
                }
            }


            $res = \app\common\model\mysql\Dynamic::create($data);
            $resid = $res['id'];

            if (!$resid) {
                return show(config("status.error"), "新增内容失败");
                $this->error("新增内容失败!", url('add'), 3);
            }
            //遍历有内容的添加拓展内容
            foreach ($tuozhanfieldcontentlist as $k => $v) {
                //查询有没有这个拓展的内容
                $fieldcontent =   \app\common\model\mysql\DynamicFieldContent::where("cat_id=$cat_id and dynamic_id=$resid and cat_field_id=$v[id]")->find();
                if ($fieldcontent['id']) {
                    //查到拓展内容 更新
                    \app\common\model\mysql\DynamicFieldContent::where("id=$fieldcontent[id]")->update(['content' => $v['content'], 'addtime' => time()]);
                } else {
                    //没有查到拓展内容 添加
                    \app\common\model\mysql\DynamicFieldContent::create(['cat_id' => $cat_id, 'dynamic_id' => $resid, 'cat_field_id' => $v['id'], 'type_id' => $v['type_id'], 'content' => $v['content'], 'addtime' => time()]);
                }
            }

            //前台logo开始
            // 捕获异常
            try {
                // 此时可能会报错
                // 比如:上传的文件过大,超出了配置文件中限制的大小
                $file = request()->file('picfile');
                // 如果表单没有设置文件上传需要的编码 $file始终是null
                if (is_null($file)) {
                    // 请检查请求类型和表单编码
                    // 不是post请求或没有指定enctype="multipart/form-data"会进入这里
                    // throw new \think\Exception('没有文件上传');
                } else {
                    $dir = $this->app->getRootPath() . '/public/uploads';

                    !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                    $dir = $this->app->getRootPath() . 'public/uploads/dynamic';

                    !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                    // 使用验证器验证上传的文件
                    validate(
                        [
                            'picfile' => [
                                // 限制文件大小(单位b)，这里限制为4M
                                'fileSize' => 5 * 1024 * 1024,
                                // 限制文件后缀，多个后缀以英文逗号分割
                                'fileExt'  => 'gif,jpg,png'
                            ]
                        ],
                        [
                            'picfile.fileSize' => '文件太大',
                            'picfile.fileExt' => '不支持的文件后缀',
                        ]
                    )->check(['picfile' => $file]);

                    $savename = "dynamic_" . $resid;

                    if ($savename) {
                        $filename = \think\facade\Filesystem::disk("public")->putFileAs('dynamic', $file, $savename . '.' . $file->extension());
                    } else {
                        $filename = \think\facade\Filesystem::disk("public")->putFile("dynamic", $file);
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


                        $data_img['pic_path'] = $path;

                        $result = \app\common\model\mysql\Dynamic::where("id = $resid ")->update($data_img);
                    }
                }
            } catch (\think\Exception $e) {
                // 获取异常错误信息
                // halt($e->getMessage());
            }
            //前台logo结束



            //遍历拓展字段
            foreach ($catFieldList as $k => $v) {

                //如果拓展类型为图片视频文件
                if ($v['type_id'] > 1) {


                    $filefieldname = 'file_' . $v['id'];
                    //检查上传文件开始
                    // 捕获异常 
                    try {
                        // 此时可能会报错
                        // 比如:上传的文件过大,超出了配置文件中限制的大小
                        $file = request()->file($filefieldname);
                        // 如果表单没有设置文件上传需要的编码 $file始终是null
                        if (is_null($file)) {
                            // 请检查请求类型和表单编码
                            // 不是post请求或没有指定enctype="multipart/form-data"会进入这里
                            // throw new \think\Exception('没有文件上传');
                        } else {


                            //查询有没有这个拓展的内容
                            $fieldcontent =   \app\common\model\mysql\DynamicFieldContent::where("cat_id=$cat_id and dynamic_id=$resid and cat_field_id=$v[id]")->find();
                            $fieldcontentid = $fieldcontent['id'];
                            if ($fieldcontentid) {
                                //查到拓展内容 更新
                                // \app\common\model\mysql\DynamicFieldContent::where("id=$fieldcontent[id]")->update(['content' => "", 'addtime' => time()]);
                            } else {
                                //没有查到拓展内容 添加
                                $fieldcontentcreateres =   \app\common\model\mysql\DynamicFieldContent::create(['cat_id' => $cat_id, 'dynamic_id' => $resid, 'cat_field_id' => $v['id'], 'type_id' => $v['type_id'], 'content' => "", 'addtime' => time()]);
                                $fieldcontentid = $fieldcontentcreateres['id'];
                            }

                            $dir = $this->app->getRootPath() . 'public/uploads';

                            !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                            $dir = $this->app->getRootPath() . 'public/uploads/dynamicfieldcontent/';

                            !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                            $fileExt = 'gif,jpg,png';
                            $fileSize = 5 * 1024 * 1024;
                            if ($v['type_id'] == 2) {
                                //如果类型为图片
                                $fileExt = 'gif,jpg,png';
                                $fileSize = 10 * 1024 * 1024;
                            } else if ($v['type_id'] == 3) {
                                //如果类型为视频
                                $fileExt = 'mp4,egg';
                                $fileSize = 100 * 1024 * 1024;
                            } else {
                                $fileExt = 'doc,docx,xls,xlsx,ppt,pptx,apk,rar,zip,pdf';
                                $fileSize = 50 * 1024 * 1024;
                            }

                            // 使用验证器验证上传的文件
                            validate(
                                [
                                    $filefieldname => [
                                        // 限制文件大小(单位b)，这里限制为4M
                                        'fileSize' => $fileSize,
                                        // 限制文件后缀，多个后缀以英文逗号分割 
                                        'fileExt'  => $fileExt
                                    ]
                                ],
                                [
                                    $filefieldname . '.fileSize' => '文件太大',
                                    $filefieldname . '.fileExt' => '不支持的文件后缀',
                                ]
                            )->check([$filefieldname => $file]);

                            $savename = "dynamic_content_" . $resid . "_" . $cat_id . "_" . $v['id'] . "_" . $fieldcontentid;

                            if ($savename) {
                                $filename = \think\facade\Filesystem::disk("public")->putFileAs('dynamicfieldcontent', $file, $savename . '.' . $file->extension());
                            } else {
                                $filename = \think\facade\Filesystem::disk("public")->putFile("dynamicfieldcontent", $file);
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


                                $data_img['content'] = $path;



                                $result = \app\common\model\mysql\DynamicFieldContent::where("id = $fieldcontentid ")->update($data_img);
                            }
                        }
                    } catch (\think\Exception $e) {
                        // 获取异常错误信息
                        // halt($e->getMessage());
                    }
                    //前台logo结束
                }
            }

            $this->success("添加成功！", url('index'), 3);
        }
    }

    /**
     * 排序
     */
    public function listorder()
    {
        $id = input("param.id", 0, "intval");
        $listorder = input("param.listorder", 0, "intval");

        //参数校验
        $data = [
            'id' => $id,
            'listorder' => $listorder,
        ];
        $validate = new \app\admin\validate\DynamicCatListorder();
        if (!$validate->check($data)) {
            return show(config("status.error"), $validate->getError());
        }


        try {
            $res = (new DynamicBis())->listorder($id, $listorder);
        } catch (\Exception $e) {
            return show(config('status.error'), $e->getMessage());
        }
        if ($res) {
            return show(config("status.success"), "排序成功");
        } else {
            return show(config("status.error"), "排序失败");
        }
    }

    /**
     * 更新状态
     */
    public function status()
    {
        $status = input("param.status", 0, "intval");
        $id =  input("param.id", 0, "intval");
        if (!$id ||  !in_array($status, \app\common\lib\Status::getTableStatus())) {
            return show(config('status.error'), "参数错误");
        }
        try {
            $res = (new DynamicBis())->status($id, $status);
        } catch (\Exception $e) {
            return show(config('status.error'), $e->getMessage());
        }
        if ($res) {
            return show(config("status.success"), "状态更新成功");
        } else {
            return show(config("status.success"), "状态更新失败");
        }
    }

    /**
     * 更新状态
     */
    public function editfield()
    {
        $value = input("param.value");
        $field = input("param.field");
        $id =  input("param.id", 0, "intval");
        if (!$id || !$field) {
        }
        if (!$value) {
            if ($value == 0) {
            } else {
                return show(config('status.error'), "参数错误");
            }
        }
        try {
            $res = (new DynamicBis())->editfield($id, $field, $value);
        } catch (\Exception $e) {
            return show(config('status.error'), $e->getMessage());
        }
        if ($res) {
            return show(config("status.success"), "更新成功");
        } else {
            return show(config("status.success"), "更新失败");
        }
    }
}
