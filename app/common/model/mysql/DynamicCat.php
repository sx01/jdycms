<?php

namespace app\common\model\mysql;

 

class DynamicCat extends BaseModel
{
 
    /**
     * 获取分类数据
     */
    public function getNormalDynamicCats($field = "*")
    {
        // $where = [
        //     "status" => config("status.mysql.table_normal"),
        // ];
        $order = [
            "paixu" => "desc",
            "id" => "desc"
        ];
        $result = $this->field($field)->order($order)->select();
        return $result;
    }

    
    
    public function getChildCountInPids($condition)
    {
        $where[] = ["pid", "in", $condition['pid']];
        $where[] = ['status', "<>", config("status.mysql.table_delete")];
        $res =  $this->where($where)
            ->field(['pid', "count(*) as count"])
            ->group("pid")
            ->select();
        //    echo $this->getLastSql();exit;
        return $res;
    }
/**
 * 根据pid 获取正常的分类数据
 */
    public function getNormalByPid($pid=0,$field="*"){
       
       $where = ['pid'=>$pid,"status"=>config("status.mysql.table_normal")];
       $order = ["listorder"=>"desc","id"=>"desc"];
       $res = $this->where($where)->field($field)->order($order)->select();
        //   echo $this->getLastSql();exit;
       
       return $res;
    }
}
