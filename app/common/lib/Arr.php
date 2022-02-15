<?php 
namespace app\common\lib;

class Arr {

    /**
     * 分页默认返回数据
     */
    public static  function getPageinateDefaultData($num){

        $result = [
            'total'=>0,
            "per_page"=>$num,
            "current_page"=>1,
            "last_page"=>0,
            "data"=>[],
            "render"=>""
        ];
        return $result;
      }

      public static function arrsSortByKey($result,$key,$sort = SORT_DESC){
          if(!is_array($result) || !$key){
              return [];
          }
            array_multisort(array_column($result,$key),$sort,$result);
            return $result;
      }
}