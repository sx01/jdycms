<?php

namespace app\admin\controller;

use think\facade\View;
use app\common\business\DynamicCat as DynamicCatBus;

class DynamicCat extends Base
{
    public function del(){
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];
        View::assign('title', '用户管理系统');


        // $list =  \app\common\model\mysql\DynamicCat::order('paixu desc')->select();


        // View::assign('catlist', tree($list));


        if ($request->isAjax()) {

            $id = $request->param("id");
            $newstype =  \app\common\model\mysql\DynamicCat::where("`id`='$id'")->find();

            if ($newstype['id']) {

                $uptype =  \app\common\model\mysql\DynamicCat::where("`pid`='$id'")->find();
                $r = array();
                if ($uptype['id']) {
                    $r['status'] = 0;
                    $r['tishi'] = "要删除的id下面还有子分类不能删除！";
                } else {

                    $res =  \app\common\model\mysql\DynamicCat::where("`id`='$id'")->delete(); // 删除id为5的用户数据

                    if ($res) {
                        $r['status'] = 1;
                        $r['tishi'] = "删除成功！";
                    } else {

                        $r['status'] = 0;
                        $r['tishi'] = "删除失败！";
                    }
                }
            } else {
                $r['status'] = 0;
                $r['tishi'] = "要删除的id不存在！";
            }
            return json($r);
        }
    }
    public function index()
    {
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];

       
        try {
            $dynamicCats = (new DynamicCatBus())->getNormalDynamicCats();
        } catch (\Exception $e) {
            $dynamicCats = [];
        }
       
         
        return View::fetch('', ["list" => \app\common\lib\Tree::tree($dynamicCats)]);
    }
    public function add()
    {
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];
        // 模板变量赋值
        View::assign('title', 'add');

        if ($request->isPost()) {


            $Varr = $request->param();




            $name = $Varr['name'];
            if ($Varr['name']) {



                $brand = \app\common\model\mysql\DynamicCat::where("`name`='$name'")->find();

                $id = $brand['id'];



                if (!$id) {
                    //echo "没有记录，开始采集：";

                    $res = \app\common\model\mysql\DynamicCat::create($Varr);

                     $id = $res['id'];
                    if ($id) {

                        $this->success("添加成功！", url('index'), 3);
                    } else {
                        $this->error("添加失败！", url('add'), 3);
                    }
                } else {

                    $this->success("分类名称已经存在记录了 ！", url('add'), 3);
                }
            } else {
                $this->error("分类名称不能为空！", url('add'), 3);
            }
        } else {

        try {
            $dynamicCats = (new DynamicCatBus())->getNormalDynamicCats();
        } catch (\Exception $e) {
            $dynamicCats = [];
        }

        return View::fetch('', [
            "catlist" =>  \app\common\lib\Tree::tree($dynamicCats),
        ]);
        }
    }

    public function edit()
    {
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];
        // 模板变量赋值
        View::assign('title', 'edit');
        try {
            $dynamicCats = (new DynamicCatBus())->getNormalDynamicCats();
        } catch (\Exception $e) {
            $dynamicCats = [];
        }

        $id = input("param.id", 0, "intval");
        if (!$id) {
            $find = [];
            $this->error("分类id的内容不存在！", url('index'), 3);
        } else {

            if ($request->isPost()) {


                $Varr = $request->param();




                $name = $Varr['name'];
                if ($name) {



                    $brand = \app\common\model\mysql\DynamicCat::where("`name`='$name'")->find();



                    if (!$brand['id'] or $id == $brand['id']) {
                        //echo "没有记录，开始采集：";

                        $res = \app\common\model\mysql\DynamicCat::where("`id`=$id")->update($Varr);


                        if ($res !== false) {

                            $this->success("修改成功！", url('index'), 3);
                        } else {
                            $this->error("修改失败！", url('edit', array('id' => $id)), 3);
                        }
                    } else {

                        $this->success("分类名称已经存在记录了 ！", url('edit', array('id' => $id)), 3);
                    }
                } else {
                    $this->error("分类名称不能为空！", url('edit', array('id' => $id)), 3);
                }
            } else {


            try {
                $find = (new DynamicCatBus())->getById($id);
            } catch (\Exception $e) {
                $find = [];
                $this->error("分类id的内容不存在！", url('index'), 3);
            }

            return View::fetch('', [
                "catlist" =>  \app\common\lib\Tree::tree($dynamicCats),
                "find" => $find,
                "id" => $id,
            ]);

           }
        }

      
    }

   
}
