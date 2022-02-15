<?php

namespace app\admin\controller;

use think\facade\View;
 
use app\common\business\Kv as KvBus;
class Kv extends AdminBase
{
    public function index()
    {
       
        $data = [];
         
       
        try {
            $kvList = (new KvBus())->getLists($data, 10);
        } catch (\Exception $e) {
            $kvList = [];
        }
         

         
         
        return View::fetch('', ["kvList" => $kvList]);
    }
  
    public function add()
    {
        // 模板变量赋值
        View::assign('title', 'add');
        

        return View::fetch('');
    }

    public function edit()
    {
        // 模板变量赋值
        // View::assign('title', 'add');
        
        $id = input("param.id", 0, "intval");
        if (!$id) {
            $find = [];
            
        } else {
            try {
                $find = (new KvBus())->getById($id);
            } catch (\Exception $e) {
                $find = [];
            }
        }

        return View::fetch('', [ 
            "find" => $find,
            "id" => $id,
        ]);
    }
    public function del(){
        $id = input("param.id", 0, "intval");
        if(!$id){
            return show(config("status.error"), "id不能为空");
        }

        try {
            $result = (new KvBus())->del($id);
        } catch (\Exception $e) {
            return show(config("status.error"), $e->getMessage());
        }
        if ($result) {
            return show(config("status.success"), "OK");
        }else{
            return show(config("status.error"), "删除失败");
        }

    }
    public function save()
    {
        $id = input("param.id", 0, "intval");
    
        $key = input("param.key", "", "trim");
        $value = input("param.value", "", "trim");
        //参数校验
        $data = [
            
            'key' => $key,
             'value' =>$value
        ];

        $validate = new \app\admin\validate\Kv();
        if (!$validate->check($data)) {
            return show(config("status.error"), $validate->getError());
        }

        $adminUser = $this->adminUser;
        $data['operate_user'] = $adminUser['username'];
 

        if($id){

            try {
                $result = (new KvBus())->edit($id,$data);
            } catch (\Exception $e) {
                return show(config("status.error"), $e->getMessage());
            }
            if ($result) {
                return show(config("status.success"), "OK");
            }else{
                return show(config("status.error"), "修改规格值失败");
            }
            


        }else{
     
        
        

       
        try {
            $add_id = (new KvBus())->add($data);
        } catch (\Exception $e) {
            return show(config("status.error"), $e->getMessage());
        }
        if ($add_id) {
            return show(config("status.success"), "OK", ['id'=>$add_id,'key' => $key,'value'=>$data['value']]);
        }else{
            return show(config("status.error"), "kv添加失败");
        }
       
      }
    }
  
    
}
