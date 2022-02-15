<?php

namespace app\common\business;

use app\common\model\mysql\MembersCat as MembersCatModel;
 

class MembersCat extends BusBase
{
    public $model = null;

    public  function __construct()
    {
        $this->model = new MembersCatModel();
    }
    public function add($data)
    {

        $data['status'] = config("status.mysql.table_normal");
        $name = $data['name'];

        //根据$name 去数据库查询是否存在这条记录

        // $find =  $this->model->where("name", $name)->find();
        // //   echo $this->model->getLastSql();
        // $res = $find->toArray();
        
        $res = $this->model->where('name', $name)->findOrEmpty();
        if (!$res->isEmpty()) {
            // halt("用户名已经存在");

            throw new \think\Exception("分类已经存在");
        }

         

        return $this->create($data);
    }

    public function edit($id,$data)
    {

        // $data['status'] = config("status.mysql.table_normal");
        $name = $data['name'];

        //根据$name 去数据库查询是否存在这条记录

        // $find =  $this->model->where("name", $name)->find();
        // //   echo $this->model->getLastSql();
        // $res = $find->toArray();
        
        $res = $this->model->where('name', $name)->find();
        
        if ($res['id']) {
            //如果find查到结果 并且 查到的ID 不等当前ID 
            if($res['id'] != $id){
            throw new \think\Exception("分类已经存在");
            }
        }

        $this->update(['id'=>$id],$data);
         

        return $id;
    }



    public function getNormalMembersCats(){
        $field = "*";
       $MembersCats = $this->model->getNormalMembersCats($field);
         if(!$MembersCats){
             $MembersCats = [];
             return  $MembersCats;
         }
         $MembersCats = $MembersCats->toArray();

         
         return $MembersCats;
    }

    public function getLists($data,$num){
        
        $result = parent::getLists($data,$num);
         // 获取 pid 子分类数  第一步 拿到表中的pid  第二步 ：in mysql 求 count  第三步  把count  填充到列表页中
       
       
         $pids =  array_column($result['data'],"id");
        
       
         if($pids){
           
             $idCountResult = $this->model->getChildCountInPids(['pid' => $pids]);
             $idCountResult = $idCountResult->toArray(); //如果没有的话返回空数组
             
             $idCounts = [];
             //第一种方式
             foreach($idCountResult as $countResult){
                 $idCounts[$countResult['pid']] = $countResult['count'];
             }
         }
         if($result['data']){
             foreach($result['data'] as $k=>$value){
                // $a ?? 0 等同于 isset($a)?$a :0
                $result['data'][$k]['childCount'] = $idCounts[$value['id']]??0;
             }
          }
          

        return $result;
    }
    
      
 /**
  * 获取一级分类的内容 
  */
    public function getNormalByPid($pid=0,$field = "id,name,pid"){

       
        try{
            $res = $this->model->getNormalByPid($pid,$field);
         
        }catch (\Exception $e){
            // echo $e->getMessage();
          
            return [];
        }
        $res = $res->toArray();
        return $res;
    }
}
