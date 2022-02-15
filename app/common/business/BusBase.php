<?php
namespace app\common\business;
class BusBase {


     /**
      * 根据$data 和 $num 查询数据
      */
    public function getLists($data,$num){
        $likeKeys =[];
        if(!empty($data)){
            $likeKeys = array_keys($data);
        }
        try{

        $list = $this->model->where($data)->select(); 
 
     

        }catch (\Exception $e){
            $list =  \app\common\lib\Arr::getPageinateDefaultData($num);
        }
         

        return $list;
    }
    /**
     * 根据ID修改排序
     */

    public function listorder($id,$listorder){
        //查询 ID这条数据是否存在
        $res = $this->getById($id);
        if(!$res){
            throw new \think\Exception("不存在该条记录");
        }
        $data = [
            "listorder" =>$listorder,
        ];
        try{
            $res = $this->model->updateById($id,$data);
        }catch (\Exception $e){
            return false ;

        }
        return $res;
    }



     /**
   * 修改状态
   */
  public function editfield($id,$field,$value){
    //查询 ID这条数据是否存在
    $res = $this->getById($id);
    if(!$res){
        throw new \think\Exception("不存在该条记录");
    }
    // if($res[$field] == $value){
    //     throw new \think\Exception("状态修改前和修改后一样");
    // }
    $data = [
        $field =>$value,
    ];
    try{
        
        $res = $this->model->updateById($id,$data);
    }catch (\Exception $e){
        return false ;

    }
    return $res;
}

  /**
   * 修改状态
   */
    public function status($id,$status){
        //查询 ID这条数据是否存在
        $res = $this->getById($id);
        if(!$res){
            throw new \think\Exception("不存在该条记录");
        }
        if($res['status'] == $status ){
            throw new \think\Exception("状态修改前和修改后一样");
        }
        $data = [
            "status" =>intval($status),
        ];
        try{
            
            $res = $this->model->updateById($id,$data);
        }catch (\Exception $e){
            return false ;

        }
        return $res;
    }

    public function delById($id){
        //查询 ID这条数据是否存在
        $res = $this->getById($id);
        if(!$res){
            throw new \think\Exception("不存在该条记录");
        }
        try{
            $resdel = $this->model->where('id',$id)->delete();
           
        }catch (\Exception $e){
            return false ;

        }
         
        return $res;
    }
   /**
    * 根据ID 获取find数据
    */
    public function getById($id){
        $result = $this->model->find($id);
        if(empty($result)){
            return [];
        }
        $result = $result->toArray();
        return $result;
    }

      /**
       * 新增逻辑
       */
      public function create($data)
      {
  
          $data['status'] = config("status.mysql.table_normal"); 
          try {
              $this->model->save($data);
          } catch (\Exception $e) {
              throw new \think\Exception("服务内部异常");
          }
          //返回主键ID
          return $this->model->id;
      }


      /**
       * 更新逻辑
       */
      public function update($where=[],$data)
      {
   
          try {
    
            $res =  $this->model->where($where)->save($data);
           
             
          } catch (\Exception $e) { 
           
          
              throw new \think\Exception("服务内部异常");
          }
          return $res;
         
      }
}