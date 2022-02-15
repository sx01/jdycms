<?php

namespace app\common\business;

use app\common\model\mysql\Dynamic as DynamicModel;

use think\facade\Cache;

class Dynamic extends BusBase
{
    public $model = NULL;
    public function __construct()
    {
        $this->model = new DynamicModel();
    }

    public function edit($id, $data)
    {
     
       try{
          $res =  $this->update(['id' => $id], $data);
        
       }catch(\Exception $e){
       
        return false;
       }
      
        return $res;
    }
  
    public function insertData($data)
    {
 
        try {


            $DynamicId = $this->create($data);
            if (!$DynamicId) {
                return $DynamicId;
            }

            
            return true;
        } catch (\think\Exception $e) {
           
            return false;
        }
        return true;
    }

     

   public function getNormalLists($data,$num = 5,$order=['create_time'=>'desc']){
       try{
           $field = "id,title, pic_path";

           $likeKeys =[];
        if(!empty($data)){
            $likeKeys = array_keys($data);
        }

           $list = $this->model->getNormalLists($likeKeys,$data,$num,$field,$order);

            
            
           $result = $list;

       }catch (\Exception $e){
           
           $result = [];

       }
       return $result;
   }
    
 
}
