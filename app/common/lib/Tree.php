<?php 
namespace app\common\lib;

class Tree {


  public static  function getIdAsKey($data){

    $tmp = [];
    foreach($data as $k=>$v){
        if(isset($v['id'])){
            $tmp[$v['id']] = $v;
        }
       
    }
    return $tmp;
  }

 /**
  * 分类树 ，支持无限极分类
  */
  public static function getTree($data){
      $items = array();
      foreach($data as $v){
        $v['category_id'] = $v['id'];
        unset($v['id']);
          $items[$v['category_id']] = $v;

      }
      $tree = [];
      foreach($items as $id =>$item){
          if(isset($items[$item['pid']])){  //父ID对应的items中否存在数据 如果存在 把当前数据 放在 父数据的 list下面
              $items[$item['pid']]['list'][] = &$items[$id];//引用  
          }else{
           // 父ID对应的items中否存在数据 如果不存在  就把当前数据直接放在 树数组中
              $tree[] = &$items[$id];
          }
      }
      return $tree;
  }
 
  /**
   * 分类截取 数组的前几个
   */
  public static  function sliceTreeArr($data,$firstCount=5,$secondCount=3,$threeCount=5){
      $data = array_slice($data,0,$firstCount);
      foreach($data as $k =>$v){
          if(!empty($v['list'])){
                $data[$k]['list'] = array_slice($v['list'],0,$secondCount);
                foreach($v['list'] as $kk=>$vv){
                    if(!empty($vv['list'])){
                        $data[$k]['list'][$kk]['list'] = array_slice($vv['list'],0,$threeCount);
                    }
                }
          }
      }
      return $data;
  }
 
    /**
 * 无限级分类
 * @access public
 * @param Array $data     //数据库里获取的结果集
 * @param Int $pid
 * @param Int $count       //第几级分类
 * @return Array $treeList
 */
public static function tree($data,$pid = 0,$count = 1) {

    $treeList=array(); //存放无限分类结果如果一页面有多个无限分类可以
    foreach ($data as $key => $value){
        if($value['pid']==$pid){
            $value['count'] = $count;
            $treeList[]=$value;
            unset($data[$key]);
            $tmptreeList=self::tree($data,$value['id'],$count+1);

            $treeList=array_merge($treeList,$tmptreeList);
        }
    }
    return $treeList;
}
}