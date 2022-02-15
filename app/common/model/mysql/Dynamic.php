<?php

namespace app\common\model\mysql;

 

class Dynamic extends BaseModel
{
    
     

    public function getNormalDynamicByCondition($where,$field=true,$limit = 5){
        $order = ["paixu"=>"desc","id"=>"desc"];
        $where["status"] = config("status.success");
        $result = $this->where($where)->field($field)->order($order)->limit($limit)->select();
        
        return $result;
    }
    public function getImageAttr($value){
        return request()->domain().$value;
    }
    public function getPicPathAttr($value){
        if(!empty($value)){
            $value =request()->domain().$value; 
        }
        return  $value;
    }

    public function getNormalDynamicFindInSetDynamicCatId($dynamicCatId,$field=true,$limit=10){
             $order = ["listorder"=>"desc","id"=>"desc"];
            $result = $this->whereFindInSet("dynamic_cat_id",$dynamicCatId)
                   ->where("status","=",config("status.success"))
                   ->order($order)
                   ->field($field)
                   ->limit($limit)
                   ->select();
            return $result;
    }

    public function searchDynamicCatIdAttr($query,$value){
        $query->whereFindInSet('dynamic_cat_id',$value);
    }
/**
 * 根据分类ID 获取商品数据
 */
    public function  getNormalLists($likeKeys,$data,$num = 10 ,$field = true,$order){
        // $order = ['listorder'=>"desc","id"=>"desc"];

        if(!empty($likeKeys)){
            // 搜索器
            $res = $this->withSearch($likeKeys,$data);
         }else{
             $res = $this;
         } 
        
        // if(isset($data['category_path_id'])){
        //     $res = $this->whereFindInSet("category_path_id",$data['category_path_id']);
        // }
        $list = $res->where("")
        ->order($order) 
        ->paginate($num);
       
   
        return $list;
    }

}