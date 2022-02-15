<?php

namespace app\admin\controller;

use think\facade\View;
use app\common\business\DynamicCat as DynamicCatBus;

class DynamicCatField extends Base
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
            $newstype =  \app\common\model\mysql\DynamicCatField::where("`id`='$id'")->find();

            if ($newstype['id']) {

                $uptype =  \app\common\model\mysql\DynamicCatField::where("`pid`='$id'")->find();
                $r = array();
                if ($uptype['id']) {
                    $r['status'] = 0;
                    $r['tishi'] = "要删除的id下面还有子分类不能删除！";
                } else {

                    $res =  \app\common\model\mysql\DynamicCatField::where("`id`='$id'")->delete(); // 删除id为5的用户数据

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

        $sql = "";
        $cat_id = input("cat_id",0,"intval");
        if($cat_id){
            if($sql){
             $sql .=" and  cat_id=$cat_id";
            }else{
             $sql = "cat_id=$cat_id";
            } 
        }
        View::assign('cat_id', $cat_id);

       $fieldtypeList = \app\common\model\mysql\DynamicCatFieldType::select();
       $fieldtypeList =  array_column($fieldtypeList->toArray(), NULL, 'id'); //二维下的id字段 为一维数组的key

  
       $name =  input("name","","trim");
       if($name){
           if($sql){
            $sql .=" and  name like '%$name%'";
           }else{
            $sql = " `name` like '%$name%'";
           }

       }
       View::assign('name', $name);
        try {
            $dynamicCatFields = \app\common\model\mysql\DynamicCatField::where($sql)->order("paixu","asc")->select();
        } catch (\Exception $e) {
            $dynamicCatFields = [];
        }
        
        try {
            $dynamicCats = (new DynamicCatBus())->getNormalDynamicCats();
        } catch (\Exception $e) {
            $dynamicCats = [];
        }

        $dynamicCats =  array_column($dynamicCats, NULL, 'id'); //二维下的id字段 为一维数组的key

        return View::fetch('', ["list"=> $dynamicCatFields,"cat_id"=>$cat_id,"fieldtypeList"=>$fieldtypeList,"dynamicCatList" => $dynamicCats]);
    }
    public function add()
    {
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];
        // 模板变量赋值
        View::assign('title', 'add');

        $cat_id = input("cat_id",0,"intval");

        View::assign('cat_id', $cat_id);

        $fieldtypeList = \app\common\model\mysql\DynamicCatFieldType::select();
        $fieldtypeList =  array_column($fieldtypeList->toArray(), NULL, 'id'); //二维下的id字段 为一维数组的key

        View::assign('fieldtypeList', $fieldtypeList);
        if ($request->isPost()) {


            $Varr = $request->param();

            $Varr['addtime'] = strtotime($Varr['addtime']);
            $check = $this->request->checkToken('__token__');
            if(!$check){
                return show(config('status.error'),"非法请求");
            }
             unset($Varr['__token__']);


            $name = $Varr['name'];
            if ($Varr['name']) {



                $brand = \app\common\model\mysql\DynamicCatField::where("`name`='$name'")->find();

                $id = $brand['id'];



                if (!$id) {
                    

                    $res = \app\common\model\mysql\DynamicCatField::create($Varr);

                     $id = $res['id'];
                    if ($id) {

                        $this->success("添加成功！", url('index',['cat_id'=>$cat_id]), 3);
                    } else {
                        $this->error("添加失败！", url('add',['cat_id'=>$cat_id]), 3);
                    }
                } else {

                    $this->success("分类字段名称已经存在记录了 ！", url('add',['cat_id'=>$cat_id]), 3);
                }
            } else {
                $this->error("分类字段名称不能为空！", url('add',['cat_id'=>$cat_id]), 3);
            }
        } else {

        try {
            $dynamicCats = (new DynamicCatBus())->getNormalDynamicCats();
        } catch (\Exception $e) {
            $dynamicCats = [];
        }

        return View::fetch('', [
            "fanhuiurl"=> url("index",['cat_id'=>$cat_id]),
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
      

        $fieldtypeList = \app\common\model\mysql\DynamicCatFieldType::select();
       
        $fieldtypeList =  array_column($fieldtypeList->toArray(), NULL, 'id'); //二维下的id字段 为一维数组的key

        View::assign('fieldtypeList', $fieldtypeList);


        $id = input("param.id", 0, "intval");
        if (!$id) {
            $find = [];
            $this->error("分类字段id的内容不存在！", url('index'), 3);
        } else {

          

            if ($request->isPost()) {


                $Varr = $request->param();
                $check = $this->request->checkToken('__token__');
                if(!$check){
                    return show(config('status.error'),"非法请求");
                }
                 unset($Varr['__token__']);

                $Varr['addtime'] = strtotime($Varr['addtime']);

                $name = $Varr['name'];
                if ($name) {



                    $brand = \app\common\model\mysql\DynamicCatField::where("`name`='$name'")->find();



                    if (!$brand['id'] or $id == $brand['id']) {
                        //echo "没有记录，开始采集：";

                        $res = \app\common\model\mysql\DynamicCatField::where("`id`=$id")->update($Varr);


                        if ($res !== false) {

                            $this->success("修改成功！", url('index',['cat_id'=>$brand['cat_id']]), 3);
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
                $find = \app\common\model\mysql\DynamicCatField::where("id=$id")->find();
            } catch (\Exception $e) {
                $find = [];
                $this->error("查询分类字段id的内容不存在！", url('admin/dynamicCat/index'), 3);
            }
            

            return View::fetch('', [
                "catlist" =>  \app\common\lib\Tree::tree($dynamicCats),
                "find" => $find,
                "id" => $id,
                "fanhuiurl"=> url("index",['cat_id'=>$find['cat_id']]),
            ]);

           }
        }

      
    }

   
}
