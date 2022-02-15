<?php

namespace app\api\controller;

 
use think\Request;

use app\common\model\mysql\Dynamic;
use app\common\model\mysql\Dianpu_cat;
use app\common\model\mysql\Fenxiang;
use app\common\model\mysql\Members;
use app\common\model\mysql\Token;
class Index  extends ApiBase
{
     //分享信息  分享码 和分享说明
    public function fenxiang(){
        //判断是否登陆
        $accessToken = $this->request->header("access-token"); 
        
        $userInfo = Token::where("token = '$accessToken'") ->find(); 

        // $userInfo = cache(config("redis.token_pre").$this->accessToken);
        $uid = 0;
        if($userInfo){ 
           if($userInfo['uid']){ 
            $uid= $userInfo['uid'];  
           }  
        }
        $result = [];
      if($uid){

       $membrefind = Fenxiang::where("uid = '$uid'")->find();
       if($membrefind['uid']){
         $code = $membrefind['code'];
         Fenxiang::where("uid = '$uid'")->update(['count'=>$membrefind['count']+1,'updatetime'=>time()]);
       }else{
        $tmpstr = \app\common\lib\Str::getRandom(); //生产一个随机字符串
        $code =  \app\common\lib\Str::getLoginToken($tmpstr); //根据随机字符串 和时间戳生产一个加密字符串
        Fenxiang::create(['code'=>$code,'addtime'=>time(),'uid'=>$uid]);
       }
       $result['code']= $code;

       
       $websiteurl = config("siteinfo.websiteurl");
       $result['find'] = getContentById(20);// 获取分享说明
       $result['find']['pic_path']  =  $websiteurl.str_replace("./","", $result['find']['pic_path']);
       $result['find']['content']=contentTupianChuli($result['find']['content']);
      
     
       
       return show(config("status.success"),"读取分享信息失败！",$result);

      }else{
        return show(config("status.error"),"读取分享信息失败！",$result);
      }
   }


    public function huiyuan(){
         //判断是否登陆
         $accessToken = $this->request->header("access-token"); 
         
         $userInfo = Token::where("token = '$accessToken'") ->find(); 

         // $userInfo = cache(config("redis.token_pre").$this->accessToken);
         $uid = 0;
         if($userInfo){ 
            if($userInfo['uid']){ 
             $uid= $userInfo['uid'];  
            }  
         }
        $membrefind = Members::where("uid = '$uid'")->find();

        $result = []; 
        //如果会员结束日期到期  更新会员状态
        if($membrefind['vip_end_time']<time()){
           $res = Members::where("uid = '$uid'")->update(['qx'=>0]);
        }

        $result['vip_start_time'] = date("Y-m-d",$membrefind['vip_start_time']);  //VIP开始时间
        $result['vip_end_time'] = date("Y-m-d",$membrefind['vip_end_time']);  //VIP结束时间


        $result['balance'] = $membrefind['balance'];
        $result['qx'] = $membrefind['qx']; 
        $result['chongzhibtnstr'] = $membrefind['qx']==1?"充值会员":"立即加入";
        $result['vipshuoming'] = $membrefind['qx']==1?"是VIP会员，到期日期:".$result['vip_end_time']:"您还不是会员";
        $websiteurl = config("siteinfo.websiteurl");
        $result['find'] = getContentById(10);// 获取内容头条
        $result['find']['pic_path']  =  $websiteurl.str_replace("./","", $result['find']['pic_path']);
        $result['find']['content']=contentTupianChuli($result['find']['content']);
 
        $result['yuanjia'] = 39.90;
        $result['xianjia'] = 0.01;
        return show(config("status.success"),"读取会员介绍成功！",$result);
    }

    public function eventlist(){
        $type = input("type");
        $sql = "status=1 ";

        $latitude = input("latitude");
        $longitude = input("longitude");

        
        $cat_id = input("cat_id");

        if($cat_id){
            $sql .= " and cat_id = $cat_id ";
        }




        $province  = input("province");
        if($province){
            $sql .= " and province = '$province' ";
        }


        $city  = input("city");
        if($city){
            $sql .= " and city = '$city' ";
        }


        $district  = input("district");
        if($district){
            $sql .= " and district = '$district' ";
        }


        if($type==1 && $latitude && $longitude){
            $point = returnSquarePoint($longitude,$latitude,20);        //计算经纬度的周围某段距离的正方形的四个点
            $right_bottom_lat = $point['right_bottom']['lat'];   //右下纬度
            $left_top_lat = $point['left_top']['lat'];           //左上纬度
            $left_top_lng = $point['left_top']['lng'];           //左上经度
            $right_bottom_lng = $point['right_bottom']['lng'];   //右下经度

            $sql .= " and latitude>$right_bottom_lat and latitude<$left_top_lat and longitude>$left_top_lng and longitude<$right_bottom_lng ";
        }

       
        // 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
        $list = \app\common\model\mysql\Event::where($sql)->order('addtime desc')->paginate(10);
        $websiteurl = config("siteinfo.websiteurl")."/";

        $elm = getContentById(11);// 获取内容头条
        $elm_pic_path  =  $websiteurl.str_replace("./","", $elm['pic_path']);
        $mt = getContentById(12);// 获取内容头条
        $mt_pic_path  =  $websiteurl.str_replace("./","", $mt['pic_path']);
        foreach($list as $k=>$v){
            $dianpu = \app\common\model\mysql\Dianpu::where("id=$v[dianpu_id]")->find();
            $v['dianpu_pic'] = $websiteurl.str_replace("./","",$dianpu['pic']);
            
            $v['baoming_start_time'] = date("H:i",$v['baoming_start_time']);
            $v['baoming_end_time'] = date("H:i",$v['baoming_end_time']);
             $juli = GetDistance($latitude, $longitude, $v['latitude'], $v['longitude']);
             if($juli<99){
             $v['juli'] = $juli."km";
            }else{
                $v['juli'] = ">99km";
            } 
            if($v['pingtai']=="饿了么"){
            $v['pingtai_pic'] = $elm_pic_path;
             }else{
            $v['pingtai_pic'] = $mt_pic_path;
            }
            $v['fhyfxbfb'] = 0.8;
            $v['baoming_count'] = \app\common\model\mysql\Event_baoming::where("event_id=$v[id] and status=1")->count();

            $list[$k]=$v;

        }

        $result = $list;


        return show(config("status.success"),"读取活动列表成功！",$result);

    }

    	/**
	 * 利用腾讯地图api
	 * 计算两点地理坐标之间的距离
	 */
    public function getDistance(){

        $result = [];

        $lng = 39.983171;
        $lat = 116.308479;

           //使用此函数计算得到结果后，带入sql查询。
    $point = returnSquarePoint($lng,$lat,5);        //计算经纬度的周围某段距离的正方形的四个点
    $right_bottom_lat = $point['right_bottom']['lat'];   //右下纬度
    $left_top_lat = $point['left_top']['lat'];           //左上纬度
    $left_top_lng = $point['left_top']['lng'];           //左上经度
    $right_bottom_lng = $point['right_bottom']['lng'];   //右下经度

    // $sql = "SELECT * FROM `表名` WHERE LastGpsWei<>0 AND latitude>$right_bottom_lat AND latitude<$left_top_lat AND longitude>$left_top_lng AND longitude<$right_bottom_lng";


// ————————————————
// 版权声明：本文为CSDN博主「笑戏人间」的原创文章，遵循CC 4.0 BY-SA版权协议，转载请附上原文出处链接及本声明。
// 原文链接：https://blog.csdn.net/qq_38256963/article/details/78763383

        // select * from 表名 where status=1 and isopen =0 and jingyingtype=1 and waimai=1 and bstatus = 1
        
        // and (acos(sin(({$httpData['lat']}*3.1415)/180) * sin((lat*3.1415)/180) + cos(({$httpData['lat']}*3.1415)/180) * cos((lat*3.1415)/180) * cos(({$httpData['lng']}*3.1415)/180 - (lng*3.1415)/180))*6370.996)<=5


        // select * from (select shop_id,shop_name,shop_tel,shop_position,shop_logo,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(($latitude*PI()/180-`shop_wei`*PI()/180)/2),2)+COS($latitude*PI()/180)*COS(`shop_wei`*PI()/180)*POW(SIN(($longitude*PI()/180-`shop_jing`*PI()/180)/2),2)))*1000) AS distance from sp_shop order by distance ) as a where a.distance<=5000; 

    //    $tmp =  GetDistance(39.983171, 116.308479, 39.996060, 116.353455);
    //    echo $tmp;

		// $key = 'OB4BZ-D4W3U-B7VVO-4PJWW-6TKDJ-WPB77'; //腾讯地图开发自己申请
		// $mode = 'driving'; //driving（驾车）、walking（步行）
		// $from = '39.983171,116.308479'; //例如：39.14122,117.14428
		// $to = '39.996060,116.353455;39.949227,116.394310'; //例如(格式：终点坐标;起点坐标)：39.10149,117.10199;39.14122,117.14428
        // $url = 'https://apis.map.qq.com/ws/distance/v1/?mode='.$mode.'&from='.$from.'&to='.$to.'&key='.$key;
        // $info = file_get_contents($url);
        // $info = json_decode($info, true);
        // print_r($info);
	}

  
   
     //舌尖霸王餐 首页信息
    public function index2()
    {
        // $this -> isLogin();  //判断用户是否登录
        // $this->view->assign('title', config("websitename"));

        $result = [];
        // A non well formed numeric value encountered  "create_time desc" 换成 "create_time","desc"
    $tupianlist= Dynamic::where("status = 0 and cat_id=4 ")->order("create_time","desc")->paginate(8)->toArray();
          
  
   
    //    $websiteurl = config("siteinfo.websiteurl")."/";
        // foreach($tupianlist['data'] as $k=>$v){
        //  $v['pic_path'] = str_replace("./","",$v['pic_path']);
        //  $v['pic_path'] = $websiteurl. $v['pic_path'];
        //  // $v['content'] = contentTupianChuli($v['content']);
        //  $v['content']  = "";
        //  $tupianlist['data'][$k]=$v;
        // }
    
        $dianpucatlist =  Dianpu_cat::where("")->select();
        $websiteurl = config("siteinfo.websiteurl")."/";
        foreach($dianpucatlist as $k=>$v){
         $v['pic'] = str_replace("./","",$v['pic']);
         $v['pic'] = $websiteurl. $v['pic'];
 
         $dianpucatlist[$k]=$v;
        }
        $result['dianpucatlist']  =  $dianpucatlist;
        $result['swipers'] = $tupianlist['data'];// 获取内容头条 
 
        $result['tistext'] = "作业会在当天晚上10点，次日中午12点分别审核完成，请尽早提交；订单通过审核之后餐币24小时内餐币会到账，到账后可以随时提现。报名不能点餐的请及时取消，多次未提交作业被限制点餐次数";
         
        return show(config("status.success"),"读取轮播图成功！",$result);

     
    }


 // 舌尖霸王餐商家首页信息
    public function index()
    {
        // $this -> isLogin();  //判断用户是否登录
        // $this->view->assign('title', config("websitename"));

        $result = [];
        $time = time(); 
        // $tupianlist= Dynamic::where("status = 0 and cat_id=3 ")->order(" create_time desc ")->limit("0,8")->select();
          
    //   $websiteurl = config("siteinfo.websiteurl")."/";
        // foreach($tupianlist as $k=>$v){
        //  $v['pic_path'] = str_replace("./","",$v['pic_path']);
        //  $v['pic_path'] = $websiteurl. $v['pic_path'];
        //  // $v['content'] = contentTupianChuli($v['content']);
        //  $v['content']  = "";
        //   $tupianlist[$k]=$v;
        // }
        // $result['tupianlist'] = $tupianlist;// 获取内容头条
 
 
 
        $websiteurl = config("siteinfo.websiteurl");
        $result['jieshao'] = getContentById(1);// 获取内容头条
        $result['jieshao']['pic_path']  =  $websiteurl.str_replace("./","", $result['jieshao']['pic_path']);
        $result['jieshao']['content']=contentTupianChuli($result['jieshao']['content']);
 
         
        return show(config("status.success"),"绑定手机号成功！",$result);

     
    }





    public function _empty($method)
    {

        return '访问的方法' . $method . '不存在';
    }
}
