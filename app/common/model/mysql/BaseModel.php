<?php
namespace app\common\model\mysql;
use think\Model;
class BaseModel extends Model{

        //自动修改时间
        protected $autoWriteTimestamp = false;
        /**
         * title查询条件表达式
         * 搜索器仅在 withSearch方法的时候触发
         */
        public function searchTitleAttr($query,$value){
            $query->where('title','like','%'.$value.'%');
        }
        public function searchNameAttr($query,$value){
            $query->where('name','like','%'.$value.'%');
        }
        public function searchCreateTimeAttr($query,$value){
            $query->whereBetweenTime('create_time',$value[0],$value[1]);
        }
        public function getLists($likeKeys,$where, $num = 10)
        {
            $order = ["listorder" => "desc", "id" => "desc"];
            if(!empty($likeKeys)){
               $res = $this->withSearch($likeKeys,$where);
            }else{
                $res = $this;
            }
            $result = $res->whereIn("status",[0,1]) 
                ->order($order)
                ->paginate($num);
            //  echo $this->getLastSql();
            return $result;
        }
        
 
       
   /**
      * 根据主键数据更新数据表里面的数据
      */
      public function updateById($id,$data)
      {
          
         
          $where =  [
              "id" => $id
          ];
          try{
            
            $res = $this->where($where)->save($data);
          
          }catch(\Exception $e){
            
              return false;
          }
          if($res===false){
            return false;
          }else{
            return true;
          }
         
      }
        /**
 * 根据id 获取正常的分类数据
 */
    public function getNormalById($id=0,$field="*"){
        $where = ['id'=>$id,"status"=>config("status.mysql.table_normal")];
   
        $res = $this->where($where)->field($field)->find();
 
        return $res;
     }

     public function getNormalInIds($ids){
        
        return $this->whereIn("id",$ids)
                    ->where("status","=",config("status.mysql.table_normal"))
                    ->select();
    }
    /**
     *  根据条件查询
     */
    public function getByCondition($condition = [], $order = ["id"=>"desc"]){
        if(!$condition || !is_array($condition)){
            return [];
        }
        $result = $this->where($condition)->order($order)->select();
        return $result;
    }

      /**
     *  根据条件查询
     */
    public function getByStrCondition($condition = "", $order = ["id"=>"desc"]){
        if(!$condition){
            return [];
        }
        $result = $this->where($condition)->order($order)->select();
        return $result;
    }

}