<?php
// 应用公共文件
 
//根据时间戳 获取下一个月的 php 给定时间戳 加一月 如果下一月天数不够就返回下一月最后一天
function plusOneMonth($time,$yuenum){
    //$time = time();
    $date = date("Y-m-d",$time);
    $day = date("d",$time);
    
    if($day=="29" || $day=="30" || $day=="31"){
        //获取下月最后一天
        $BeginDate=date('Y-m-01', strtotime(date("Y-m-d",$time)." +$yuenum month"));   
         $next_month_day = date('d', strtotime("$BeginDate -1 day"));
        
        //如果下月最后一天大于等于$day,正常返回
        //如果下月最后一天小于$day,返回下月最后一天
        if($next_month_day>=$day){
            return strtotime(date('Y-m-'.$day, strtotime("$BeginDate -1 day")));
        }else{
            return strtotime(date('Y-m-d', strtotime("$BeginDate -1 day")));
        }
        
    }else{
        return strtotime(date("Y-m-d",strtotime("$date +$yuenum month")));
    }
    
}

// PHP根据自己的经纬度计算5公里范围内的全部经纬度
 
 /**
     *计算某个经纬度的周围某段距离的正方形的四个点
     *@param lng float 经度
     *@param lat float 纬度
     *@param distance float 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
     *@return array 正方形的四个点的经纬度坐标
     */
   function returnSquarePoint($lng, $lat,$distance = 5)
    {
        $earthdata=6371;//地球半径，平均半径为6371km
        $dlng =  2 * asin(sin($distance / (2 * $earthdata)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);
        $dlat = $distance/$earthdata;
        $dlat = rad2deg($dlat);
        $arr=array(
            'left_top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
            'right_top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
            'left_bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
            'right_bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
        );
        return $arr;
    }
 


/**
 * 根据经纬度算距离，返回结果单位是公里，先纬度，后经度
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @return float|int
 */
function GetDistance($lat1, $lng1, $lat2, $lng2)
{
    $EARTH_RADIUS = 6378.137;

    $radLat1 =  rad($lat1);
    $radLat2 = rad($lat2);
    $a = $radLat1 - $radLat2;
    $b =  rad($lng1) - rad($lng2);
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $s = $s * $EARTH_RADIUS;
    $s = round($s * 10000) / 10000;

    return $s;
}

function rad($d)
{
    return $d * M_PI / 180.0;
}



function contentTupianChuli($content){
    $websiteurl = config("siteinfo.websiteurl");
    $content =  preg_replace('/(&lt;img.+?src=&quot;)(.*?)/', '$1'. $websiteurl.'$2',$content);
    $content =  preg_replace('/(&lt;a.+?href=&quot;)(.*?)/','$1'.$websiteurl.'home/index/download?url=$2',$content);
    $content = preg_replace('/(<img.+?src=")(.*?)/','$1'. $websiteurl.'$2',$content);
    return $content;
}


//图片地址加网址处理
function avatarchuli($avatar){
    $websiteurl = config("siteinfo.websiteurl")."/";
    $preg ="/^http(s)?:\\/\\/.+/";
    if (preg_match($preg, $avatar)) {
        $tmpavatar = $avatar?$avatar : '/images/touxiang.jpg';
    } else {
        $avatar = str_replace("./","",$avatar);
        $tmpavatar =  $avatar?$websiteurl.$avatar : $websiteurl.'/images/touxiang.jpg';
    }
    return $tmpavatar;
}

/**
 * 通用化API数据格式输出
 * @param $status
 * @param string $message
 * @param array $data
 * @return \think\response\Json
 */
function show($status,$message = "error",$data=[],$httpStatus = 200 ){
    $result = [
        "status"=>$status,
        "message"=>$message,
        "result" => $data
    ];
    return json($result,$httpStatus);
}
/**
 * 获取所有动态内容分类
 */
function getAllDynamicCatList(){
   
        $list = \app\common\model\mysql\DynamicCat::select();
     
   $res = $list->toArray();
   
       return $res;
   
}

/**
 * 根据分类ID获得分类的拓展字段
 */
function getAllDynamicCatFieldListByCatId($cat_id=0){

    if($cat_id){
        $list = \app\common\model\mysql\DynamicCatField::where("cat_id=$cat_id")->select();
        $res = $list->toArray();
    }else{
        $res = [];
    }

    return $res;
    
 }
 
/**
 * 根据ID获取cat_name
 */
 function getCatNameByid($id)
 {
 
    if(!$id){
        return "";
    }
     //查询条件
     $map = [
         'id' => $id,
 
     ];
     //数据表查询,返回模型对象
     $find = \app\common\model\mysql\DynamicCat::where($map)->find();
 
     return $find['name'] ? $find['name'] : '';
 }

 /**
 * 根据ID获取cat_find
 */
function getCatFindByid($id)
{
    if(!$id){
        return [];
    }
    //查询条件
    $map = [
        'id' => $id,

    ];
    //数据表查询,返回模型对象
    $find = \app\common\model\mysql\DynamicCat::where($map)->find();

    return $find ? $find : [];
}

function get_username_by_uid($uid)
{

    //查询条件
    $map = [
        'uid' => $uid,

    ];
    //数据表查询,返回模型对象
    $common_member = \app\common\model\mysql\Members::where($map)->find();

    return $common_member['username'] ? $common_member['username'] : '';
}

/*图片转换为 base64格式编码
$img = 'uploads/01.png';
$base64_img = base64EncodeImage($img);
echo '<img src="' . $base64_img . '" />';
*/



//替换路径
function  tihuan_imgurl_zx($str)
{

    $str = htmlspecialchars_decode($str);
    $str = str_replace('"Uploads/', config("siteinfo.websiteurl").'/Uploads/', $str);
    $str = str_replace('"/Uploads/', config("siteinfo.websiteurl").'/Uploads/', $str);
    $str = str_replace('"Uploads/', config("siteinfo.websiteurl").'/uploads/', $str);
    $str = str_replace('"/Uploads/', config("siteinfo.websiteurl").'/uploads/', $str);
 
    return $str;
}
//替换路径
function  tihuan_imgurl($str)
{


    $str = htmlspecialchars_decode($str);
     
    $str = tihuantechuzifu($str);
  
    return $str;
}

//替换路径
function  content_htmlspecialchars($str)
{


    $str = htmlspecialchars_decode($str);
     
    $str = tihuantechuzifu($str);
  
    return $str;
}


/*
 * 替换特出字符
 */
function tihuantechuzifu($str){
    if (!empty($str)) {
        // $str=strip_tags($str);

        //  $str = str_replace("style=\"font-family:\"font-size:16px;vertical-align:baseline;color:#333333;background-color:#FFFFFF;\"","",$str);
    //    $str = str_replace("style=\"margin:0px;padding:0px;border:0px;font-family:\"font-size:16px;vertical-align:baseline;color:#333333;background-color:#FFFFFF;\"","",$str);
 
       $str =preg_replace('/style=\"(.*?)\">/',">",$str);
         
        $str = str_replace(chr(13),"",$str);
        $str = str_replace(chr(10),"",$str);
        $str = str_replace(chr(9),"",$str);
        $str = str_replace("&nbsp;"," ",$str);
        // $str = str_replace("'","‘",$str);
 






            return $str;

    }else{
        return $str;
    }
}

/**
 * https 发起post请求
 *
 * @param string $url url信息
 * @param mixed $data 参数信息[$data = '{"a":1,"b":2}' or $data = array("a" => 1,"b" => 2)]
 * @param int $timeOut 超时设置
 * @param string $proxyHost 代理host
 * @param int $proxyPort 代理端口
 * @return string
 */
function fdxpost($url, $data = null, $timeOut = 20, $proxyHost = null, $proxyPort = null)
{/*{{{*/
    try {
        if (strlen($url) < 1) {
            return null;
        }

        $ch = curl_init();

        // 设置url
        curl_setopt($ch, CURLOPT_URL, $url);

        if (false == empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            // array
            if (is_array($data) && count($data) > 0) {
                curl_setopt($ch, CURLOPT_POST, count($data));
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // 如果成功只将结果返回，不自动输出返回的内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // user-agent
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0");
        // 超时
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut);

        // 使用代理
        if (strlen($proxyHost) > 0 && strlen($proxyPort) > 0) {
            // 代理认证模式
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            // 代理服务器地址
            curl_setopt($ch, CURLOPT_PROXY, $proxyHost);
            // 代理服务器端口
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);
            // 使用http代理模式
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }

        // 执行
        $out = curl_exec($ch);
        // 关闭
        curl_close($ch);
        return $out;
    } catch (Exception $e) {
        return null;
    }

    return null;
}/*}}}*/



/**
 * 图片转为base64
 */
function base64EncodeImage($image_file)
{
    $base64_image = '';
    if (file_exists($image_file)) {
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    }
    return $base64_image;
}


/*  base64格式编码转换为图片并保存对应文件夹
   echo base64_image_content($base64_img,"uploads/");
 */
function base64_image_content($base64_image_content, $path, $filename)
{
    //匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
        $type = $result[2];
        //        $new_file = $path."/".date('Ymd',time())."/";
        //        if(!file_exists($path)){
        //            //检查是否有该文件夹，如果没有就创建，并给予最高权限
        //            mkdir($path, 0700);
        //        }
        $new_file = $path . $filename . ".{$type}";
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
            return $filename . ".{$type}";
        } else {
            return false;
        }
    } else {
        return false;
    }
}




//php识别中文文件并自动转换为UTF-8文件
function filetoutf8($filename)
{
    $contents_before = file_get_contents($filename);
    $contents_after = autocharacetutf8($contents_before);
    $contents_after = str_replace("\r\n", "\n", $contents_after);
    $contents_after = str_replace("\n\n", "\n", $contents_after);

    file_put_contents($filename, $contents_after);
}
//php识别中文编码并自动转换为UTF-8
function autocharacetutf8($data)
{
    if (!empty($data)) {
        $fileType = mb_detect_encoding($data, array('CP936', 'ASCII', 'GBK', 'GB2312', 'UTF-8', 'LATIN1', 'BIG5'));
        if ($fileType != 'UTF-8') {
            $data = mb_convert_encoding($data, 'utf-8', $fileType);
        }
    }
    return $data;
}










//秒转换成分秒
function secondsformatms($duration) //as mm:ss
{
    $hours = floor($duration / 3600);
    $minutes = floor(($duration - ($hours * 3600)) / 60);
    $seconds = $duration - ($hours * 3600) - ($minutes * 60);
    //    if($hours<10){
    //        $hours = "0".$hours;
    //    }
    //
    //    if($minutes<10){
    //        $minutes = "0".$minutes;
    //    }
    //
    //    if($seconds<10){
    //        $seconds = "0".$seconds;
    //    }
    //    return $minutes.":".$seconds;

    return sprintf("%02d:%02d",   $minutes, $seconds);
}

//秒转换成时分秒
function secondsformatTime($duration) //as hh:mm:ss
{
    //return sprintf("%d:%02d", $duration/60, $duration%60);
    $hours = floor($duration / 3600);
    $minutes = floor(($duration - ($hours * 3600)) / 60);
    $seconds = $duration - ($hours * 3600) - ($minutes * 60);
    // return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);


    return array('hours' => $hours, 'minutes' => $minutes, 'seconds' => $seconds);
}
 
 


function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{

    if (function_exists("mb_substr")) {
        $strleng = mb_strlen($str, $charset);
        if ($suffix  && $strleng > $length)
            return mb_substr($str, $start, $length, $charset) . "...";
        else
            return mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        $strleng = strlen($str, $charset);
        if ($suffix   && $strleng > $length)
            return iconv_substr($str, $start, $length, $charset) . "...";
        else
            return iconv_substr($str, $start, $length, $charset);
    }
    $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    $strleng = strlen($str, $charset);
    if ($suffix && $strleng > $length) {

        return $slice . "…";
    }
    return $slice;
}
//通过tag值 返回 包含的 数组
function article_parse_tags($tag)
{
    $tag = intval($tag);
    $article_tags = array();
    for ($i = 1; $i <= 8; $i++) {
        $k = pow(2, $i - 1);
        $article_tags[$i] = ($tag & $k) ? 1 : 0;
    }
    return $article_tags;
}
//得到 0到255的 tag值
function article_make_tag($tags)
{
    $tags = (array) $tags;
    $tag = 0;
    for ($i = 1; $i <= 8; $i++) {
        if (!empty($tags[$i])) {
            $tag += pow(2, $i - 1);
        }
    }
    return $tag;
}

//获取内容第一张图片

function getfirstpiccontent($str, $dateline)
{
    $str = htmlspecialchars_decode($str);

    $pattern = '/<img(.*?)src="(.*?)"(.*?)>/i';

    preg_match_all($pattern, $str, $match);



    foreach ($match[2] as $k => $v) {
        if (strpos($v, 'ttp://')) {


            if (!file_exists('./uploads/')) {
                mkdir('./uploads/');
                //echo '创建文件夹flower成功';
            }

            if (!file_exists('./uploads/attachment/')) {
                mkdir('./uploads/attachment/');
                //echo '创建文件夹flower成功';
            }

            if (!file_exists('./uploads/attachment/article/')) {
                mkdir('./uploads/attachment/article/');
                //echo '创建文件夹flower成功';
            }

            $dqriqi = date("Ym", $dateline);

            $dqri = date("d", $dateline);


            $dqhis = date("His", $dateline);

            $strtmp = null;
            $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
            $max = strlen($strPol) - 1;

            for ($i = 0; $i < 8; $i++) {
                $strtmp .= $strPol[rand(0, $max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
            }

            if (!file_exists('./uploads/attachment/article/' . $dqriqi . '/')) {
                mkdir('./uploads/attachment/article/' . $dqriqi . '/');
                //echo '创建文件夹flower成功';
            }

            if (!file_exists('./uploads/attachment/article/' . $dqriqi . '/' . $dqri . '/')) {

                mkdir('./uploads/attachment/article/' . $dqriqi . '/' . $dqri . '/');
                //echo '创建文件夹flower成功';
            }


            //  portal/201610/28/161418emrm96mgbmuy0lgs.png


            $rootPath = "./uploads/attachment/article/" . $dqriqi . '/' . $dqri . '/';

            $imgname = $dqhis . $strtmp;


            curlDownload($v, $rootPath . $imgname . ".jpg");

            $str = str_replace($v, "/uploads/attachment/article/" . $dqriqi . '/' . $dqri . '/' . $imgname . ".jpg", $str);
            $match[2][$k] = $dqriqi . '/' . $dqri . '/' . $imgname . ".jpg";
        } else {
            $match[2][$k] = str_replace("./uploads/attachment/", "", $v);
            $match[2][$k] = str_replace("/uploads/attachment/", "", $v);
            $match[2][$k] = str_replace("uploads/attachment/", "", $v);
        }
    }

    return array("imgarr" => $match[2], "str" => $str);
}


// 应用公共文件
//获取url反馈
function http_request_json($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}




 

//验证邮箱
function validate_email($email)
{


    $checkmail = "/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/"; //定义正则表达式



    if (preg_match($checkmail, $email)) { //先用正则表达式验证email格式的有效性

        return true;
    } else {

        return false;
    }
}
/**
 * 采集远程文件
 * @access public
 * @param string $remote 远程文件名
 * @param string $local 本地保存文件名
 * @return mixed
 */
function curlDownload($remote, $local)
{
    $cp = curl_init($remote);
    $fp = fopen($local, "w");
    curl_setopt($cp, CURLOPT_FILE, $fp);
    curl_setopt($cp, CURLOPT_HEADER, 0);
    curl_exec($cp);
    curl_close($cp);
    fclose($fp);
}



   

/**
 * @return array
 * 获得所有基本信息
 */
function getkvall()
{
    $kvlist = \app\common\model\mysql\Kv::select();
    $tmplist = array();
    foreach ($kvlist as $k => $v) {
        $tmplist[$v['key']] = $v['value'];
    }
    return $tmplist;
}
/**
 * 根据Key获得值
 * @return array
 */
function getvaluebykey($key)
{

    $kv = \app\common\model\mysql\Kv::where("`key`=$key")->find();
    if ($kv) {
        return $kv['value'];
    } else {
        return "";
    }
}

 
/**
 * 根据uid 获取 昵称
 */
function getNicknameByUid($uid){
    if($uid){
    $find = \app\common\model\mysql\Members::where("`uid`=$uid")->field("nickname")->find();
    if ($find) {
        return $find['nickname']?$find['nickname']:'';
    } else {
        return "";
    }
   }else{
    return "";
   }
}

/**
 * 无限级分类
 * @access public
 * @param Array $data     //数据库里获取的结果集
 * @param Int $pid
 * @param Int $count       //第几级分类
 * @return Array $treeList
 */
function tree($data, $pid = 0, $count = 1)
{

    $treeList = array(); //存放无限分类结果如果一页面有多个无限分类可以
    foreach ($data as $key => $value) {
        if ($value['upid'] == $pid) {
            $value['count'] = $count;
            $treeList[] = $value;
            unset($data[$key]);
            $tmptreeList = tree($data, $value['catid'], $count + 1);

            $treeList = array_merge($treeList, $tmptreeList);
        }
    }
    return $treeList;
}


/**
 * 无限级分类
 * @access public
 * @param Array $data     //数据库里获取的结果集
 * @param Int $pid
 * @param Int $count       //第几级分类
 * @return Array $treeList
 */
function pidtree($data, $pid = 0, $count = 1)
{

    $treeList = array(); //存放无限分类结果如果一页面有多个无限分类可以
    foreach ($data as $key => $value) {
        if ($value['pid'] == $pid) {
            $value['count'] = $count;
            $treeList[] = $value;
            unset($data[$key]);
            $tmptreeList = tree($data, $value['id'], $count + 1);

            $treeList = array_merge($treeList, $tmptreeList);
        }
    }
    return $treeList;
}


 /**
 * @param $uid
 * @return 统计登录情况
 */
function tongjilogin($uid, $is_app = 0, $ip)
{
    $device_type = "pc";
    if (isMobile()) {
        $device_type = get_device_type();
    }

    $is_weixin = is_weixin();

    $ip = $ip;
    $time = time();

    $um = \app\common\model\mysql\Members::where("uid=$uid")->find();
    if ($um['uid']) {
        $login_count = $um['login_count'] + 1;
        \app\common\model\mysql\Members::where("uid=$uid")->update(array('lastlogintime' => $time, 'lastloginip' => $ip, 'device_type' => $device_type, 'is_weixin' => $is_weixin, 'login_count' => $login_count));


        $data = array(
            'uid' => $uid,
            'lastlogintime' => $time,
            'lastloginip' => $ip,
            'device_type' => $device_type,
            'is_weixin' => $is_weixin,
            'is_app' => $is_app,
        );
        \app\common\model\mysql\Login_liushui::create($data);
    }
}



//获得内容根据内容id
function getContentById($id){
  
 
    
    try {
        $find = app\common\model\mysql\Dynamic::where("id=$id")->find();
        $find['pic_path'] = str_replace(request()->domain(),"",$find['pic_path']);
    } catch (\Exception $e) {
        $find = [];
    }
    return $find;


}


//获得内容根据类别id
function getContentListByCatId($catId,$limit=10){

    
    $list = app\common\model\mysql\Dynamic::where("status=0 and cat_id=$catId")->limit($limit)->order("paixu","asc")->select();

       $list =$list->toArray();
    return $list?$list:[]; 

}


//判断是不是微信打开
function is_weixin()
{

    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {

        return true;
    }

    return false;
}

/*
 * PHP判断手机是IOS还是Android
 */
function get_device_type()
{
    //全部变成小写字母
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $type = 'other';
    //分别进行判断
    if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
        $type = 'ios';
    }

    if (strpos($agent, 'android')) {
        $type = 'android';
    }
    return $type;
}


//判断是否是手机
function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE']))            return true;
    //此条摘自TPM智能切换模板引擎，适合TPM开发
    if (isset($_SERVER['HTTP_CLIENT']) && 'PhoneClient' == $_SERVER['HTTP_CLIENT'])            return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA']))
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');

        //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}


