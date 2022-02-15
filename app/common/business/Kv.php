<?php

namespace app\common\business;

use app\common\model\mysql\Kv as KvModel;
 

class Kv extends BusBase
{
   
    public $model = null;
    public  function __construct()
    {
        $this->model = new KvModel();
    }


    public function del($id){
        $res = $this->model->where(['id'=> $id])->findOrEmpty();
        if ($res->isEmpty()) { 

            throw new \think\Exception("key值不存在");
        } 
        return $this->model->where("id","=",$id)->delete();

    }
    public function add($data)
    {

         
        $key = $data['key'];
        // $value = $data['value']; 
        
        $res = $this->model->where(['key'=> $key])->findOrEmpty();
        if (!$res->isEmpty()) {
            // halt("用户名已经存在");

            throw new \think\Exception("key值已经存在");
        } 
        return $this->create($data);
    }

    public function edit($id,$data)
    {

        // $data['status'] = config("status.mysql.table_normal");
        $key = $data['key'];
        $value = $data['value']; 
        //根据$name 去数据库查询是否存在这条记录

        // $find =  $this->model->where("name", $name)->find();
        // //   echo $this->model->getLastSql();
        // $res = $find->toArray();
        
        $res = $this->model->where(['key'=> $key])->find();
        
        if ($res['id']) {
            //如果find查到结果 并且 查到的ID 不等当前ID 
            if($res['id'] != $id){
            throw new \think\Exception("key已经存在");
            }
        }

        $this->update(['id'=>$id],$data); //更新数据 

        return $id;
    }



    public function getValueByKey($key){
     
       $value = $this->model->where("key","=",$key)->find();
        
         
        return $value['value']?$value['value']:"";
    }

 
   
  
  
}
