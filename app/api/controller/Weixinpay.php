<?php

namespace app\api\controller;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\RequestException;
use WechatPay\GuzzleMiddleware\WechatPayMiddleware;
use WechatPay\GuzzleMiddleware\Util\PemUtil;
 
 use app\common\lib\Str;
use think\Request;
use WechatPay\GuzzleMiddleware\Validator;
use app\common\model\mysql\Chongzhi;
use app\common\model\mysql\Balance_list;
use app\common\model\mysql\Members;
use think\facade\Db;
class Weixinpay  extends ApiBase
{


    // 小程序下单充值会员返回通知
    public function chongzhihuiyuannotify(){
    
          $data = file_get_contents('php://input');
        // $data = input("data");
    
    //  $data = '{"id":"c0dc5f9e-46a9-5574-ad23-d59269f45807","create_time":"2021-06-15T10:43:04+08:00","resource_type":"encrypt-resource","event_type":"TRANSACTION.SUCCESS","summary":"支付成功","resource":{"original_type":"transaction","algorithm":"AEAD_AES_256_GCM","ciphertext":"EZmm5k7wDPvplKAnxEqFPRSEUSit1z//h2AotFsBd/PqFPShJb9X3CXamIfnKy2nKX7NZ4P+doF7iEMnU2WQKJmrBGeB3snInmTpCoVKLSQBfTL9hl18+JwWkGhy2y3owJDGKWKWSHZe/Sb65oKWjDQ9sdvXGcoHsVhX3yBwNpZBq4haG1Gt/hig2QvfR3FHOhsXQDFK29SOWH/HLurKWTI1D+1N6i76Y4lqYJNstIcbVLtbI6yOoX+/wxKUs9y5qoukM4lEfn1qTzNE7seEjKqMmvwdwKENGKEX8tetsrQ68nflofB4I9ID2X0anZoKBAVE6WrIBmGI5RPpFZy0/1/DOm/xWwuGkU9374uunm+/ghRwjOhJTMYok93KnGLhJlWJO/bSls/8puXYOeAQ00lQiFuoUPhSU3alav3HfA9SZLsssf/uGQHN1oxuNOxY8eDkoT4SnbiANI/ZZBAQ73vgLMcAI/zomlFsbJ2qyW/yc8bawUFqgpJch8/+lZjr0bNpt5slMVkbJuobjYhqc2oUKiYRCFkhOThrJsaSyJmfthyJTOpEBfI37Uvr/bF1F1u+Dx8K9VY=","associated_data":"transaction","nonce":"5BYgzkmNyzBf"}}';
     
        //file_put_contents('/tmp/2.txt',$wxData,FILE_APPEND);
        file_put_contents("notify.log","jishou:ssssss",FILE_APPEND);
        //   echo "sss";
        file_put_contents("notify.log",$data,FILE_APPEND);
        file_put_contents("notify.log","jishou:eeee",FILE_APPEND);
    
          $data = json_decode($data, true);
    
          
          
        //   $data = '{
        //     "id":"EV-2018022511223320873",
        //     "create_time":"2015-05-20T13:29:35+08:00",
        //     "resource_type":"encrypt-resource",
        //     "event_type":"TRANSACTION.SUCCESS",
        //     "resource":{
        //         "algorithm":"AEAD_AES_256_GCM",
        //         "ciphertext":"...",
        //         "nonce":"...",
        //         "original_type":"transaction",
        //         "associated_data":""
        //     },
        //     "summary":"支付成功"
        // }';
        $resource = $data['resource'];
        $nonceStr = $resource['nonce'];
    $associatedData = $resource['associated_data'];
    $ciphertext = $resource['ciphertext'];
    $ciphertext = \base64_decode($ciphertext);
    if (strlen($ciphertext) <= 12) {
        return "ciphertext小于12";
    }
    
    
    $APIv3_KEY = "sdaklfladsf2332dsee932k3j23s23dd";
    
    if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
        //$APIv3_KEY就是在商户平台后端设置是APIv3秘钥
        $orderData = \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $APIv3_KEY);
        $orderData = json_decode($orderData, true);
 
    
        //到数据库查看订单状态是否已更新为"success"
    
    
        $transaction_id = $orderData['transaction_id'];//微信支付订单号
        $trade_type = $orderData['trade_type']; //交易类型
        $out_trade_no = $orderData['out_trade_no'];//商户订单号
        $appid = $orderData['appid'];//应用ID 
        $mchid = $orderData['mchid'];//商户号
    
        $trade_state = $orderData['trade_state'];//交易状态  //SUCCESS 
        $trade_state_desc = $orderData['trade_state_desc'];//交易状态描述 
        $success_time = $orderData['success_time'];//交易状态描述 
        $openid = $orderData['payer']['openid'];//用户在直连商户appid下的唯一标识。  
        $payer_total = $orderData['amount']['payer_total'];//用户支付金额，单位为分。 
    
        if($trade_state=="SUCCESS" && $appid==config("siteinfo.appid") &&  $mchid=="1609843178"){
            $chongzhifind = Chongzhi::where("out_trade_no=$out_trade_no")->find();

      
            if($chongzhifind['id']){
    
                if($chongzhifind['status']){
                      //已更新
                    //   订单已经更新
                    //应答微信支付已处理该订单的通知
        return json_encode(['code' => 'SUCCESS', 'message' => '']);
                 
                }else{
                $uid = $chongzhifind['uid'];
            $jine = $chongzhifind['jine']*100;
            if($jine==$payer_total){

              

                // Db::transaction(function () {
                //     Db::table('think_user')->find(1);
                //     Db::table('think_user')->delete(1);
                // });
            // 启动事务
    Db::startTrans();
    try {
       
    
          
    
                 //更新充值订单状态
        Db::table('jdy_chongzhi')->where("out_trade_no=$out_trade_no")->update(['transaction_id'=>$transaction_id,
        'trade_type'=>$trade_type,
        'trade_state'=>$trade_state,
        'trade_state_desc'=>$trade_state_desc,
        'success_time'=>$success_time,
        'openid'=>$openid,
        'payer_total'=>$payer_total,
        'status'=>1
        ]);
     
    
        //查找当前订单用户
          $member=  Db::table('jdy_members')->where("uid=$uid")->find();
          $price = $payer_total/100;
          $time = time();
         $vip_start_time = $member['vip_start_time']?$member['vip_start_time']:$time;
        
            
          $yuenum = $chongzhifind['yuenum']; 

       

          $vip_end_time = $member['vip_end_time']?$member['vip_end_time']:$time; //会员结束时间

          $vip_end_time = plusOneMonth($vip_end_time,$yuenum);
          file_put_contents("notify.log","mvipendtime:".$member['vip_end_time']."yunum:".$yuenum." vip_end_time:".$vip_end_time,FILE_APPEND);
         
          //  //更新会员VIP充值流水
        Db::table('jdy_vip_liushui')->insertGetId(['uid'=>$uid,'yuenum'=>$yuenum,'jine'=>$price,'addtime'=>$time,'endtime'=>$vip_end_time,'starttime'=>$vip_start_time]);
    
        //更新用户会员情况
        Db::table('jdy_members')->where("uid=$uid")->update(['qx'=>1,'vip_start_time'=>$vip_start_time,'vip_end_time'=>$vip_end_time]);
        // 提交事务
        Db::commit();
    
            
    
        //应答微信支付已处理该订单的通知
        return json_encode(['code' => 'SUCCESS', 'message' => '']);
      
    } catch (\Exception $e) {
        // 回滚事务
        Db::rollback();
    
    
        //应答微信支付已处理该订单的通知
        return "更新数据失败".$e->getMessage();
    }
    
              
    
    
    
    
             }else{
                 return "金额不对";
             }
            }
    
             }else{
                 return "没有查到对应的订单";
             }
    
        }else{
            return "支付失败";
        }
    

}else{
    echo "不支持";
}



 }

//商家版小程序下单返回通知
    public function notify(){
    
    $data = file_get_contents('php://input');
    // $data = input("data");

    // $data = '{"id":"13dc52a2-65b8-5401-86bc-15c213c8dfec","create_time":"2021-05-29T09:39:53+08:00","resource_type":"encrypt-resource","event_type":"TRANSACTION.SUCCESS","summary":"支付成功","resource":{"original_type":"transaction","algorithm":"AEAD_AES_256_GCM","ciphertext":"8OwOZJmVtD/rliY5LYrYalHdJ52QjqGWjkLyVPjsWNqBPzDOgp/NbJHfmUABh17TpXPy1Rl/OmYcGx22J2PeamIhafKt33LcGiFw5Qmm1g7bvjsp3Q+K5yzVJ7MiW/oIMbmUfGKAk4YaUOOdrTR59cxRx9LURQKBgneTG7Lglct+WgsqvXGxJIhVB5LE8VyyOYVrmdI77QKVLZ4zysGE1RissHdYAh79n/QQ4xHryZ04+IaT3uo+M5gDDVahvu5WVv/pUqBDKo6sTol/Yok7TnKK+0CVk/i1zxyZiI5yUAOTuv7DoL2rJkX9J5zVLcmiQ1NpXX8YZPsCxB0tNPk59oeHVVEhg35Urmyjt0rw5/mVKsv36+j1lvYsGSo6yBjlAePJ+NdAt8jfn6cRl7waF5kAbrUYHt+kxGgDKkjjhihPpK/2ypPoEu+XJk00pee9Xwa5sa5UrvcwY0G1lSqxQzYDEnstKGsO3WOzNKsB+GS0UdXa6k3WMFS6Va286pyenXIaeOjzLpaDrqrkXFFRH8kLlMnTtIX5GI3zbxsT3MSiv7rOhaYBN17pz5/pmVbyxIFjzPm4KGSBlg==","associated_data":"transaction","nonce":"LBUO49vo66Tw"}}';
 
    //file_put_contents('/tmp/2.txt',$wxData,FILE_APPEND);
    file_put_contents("notify.log","jishou:ssssss",FILE_APPEND);
    //   echo "sss";
    file_put_contents("notify.log",$data,FILE_APPEND);
    file_put_contents("notify.log","jishou:eeee",FILE_APPEND);

      $data = json_decode($data, true);

      
      
    //   $data = '{
    //     "id":"EV-2018022511223320873",
    //     "create_time":"2015-05-20T13:29:35+08:00",
    //     "resource_type":"encrypt-resource",
    //     "event_type":"TRANSACTION.SUCCESS",
    //     "resource":{
    //         "algorithm":"AEAD_AES_256_GCM",
    //         "ciphertext":"...",
    //         "nonce":"...",
    //         "original_type":"transaction",
    //         "associated_data":""
    //     },
    //     "summary":"支付成功"
    // }';
    $resource = $data['resource'];
    $nonceStr = $resource['nonce'];
$associatedData = $resource['associated_data'];
$ciphertext = $resource['ciphertext'];
$ciphertext = \base64_decode($ciphertext);
if (strlen($ciphertext) <= 12) {
	return "ciphertext小于12";
}


$APIv3_KEY = "sdaklfladsf2332dsee932k3j23s23dd";

if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
	//$APIv3_KEY就是在商户平台后端设置是APIv3秘钥
	$orderData = \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $APIv3_KEY);
	$orderData = json_decode($orderData, true);

	//到数据库查看订单状态是否已更新为"success"


    $transaction_id = $orderData['transaction_id'];//微信支付订单号
    $trade_type = $orderData['trade_type']; //交易类型
    $out_trade_no = $orderData['out_trade_no'];//商户订单号
    $appid = $orderData['appid'];//应用ID 
    $mchid = $orderData['mchid'];//商户号

    $trade_state = $orderData['trade_state'];//交易状态  //SUCCESS 
    $trade_state_desc = $orderData['trade_state_desc'];//交易状态描述 
    $success_time = $orderData['success_time'];//交易状态描述 
    $openid = $orderData['payer']['openid'];//用户在直连商户appid下的唯一标识。  
    $payer_total = $orderData['amount']['payer_total'];//用户支付金额，单位为分。 

    if($trade_state=="SUCCESS" && $appid==config("siteinfo.appidsj") &&  $mchid=="1609843178"){
        $chongzhifind = Chongzhi::where("out_trade_no=$out_trade_no")->find();
        if($chongzhifind['id']){

            if($chongzhifind['status']){
                  //已更新
                //   订单已经更新
                //应答微信支付已处理该订单的通知
	return json_encode(['code' => 'SUCCESS', 'message' => '']);
             
            }else{
            $uid = $chongzhifind['uid'];
        $jine = $chongzhifind['jine']*100;
        if($jine==$payer_total){
            // Db::transaction(function () {
            //     Db::table('think_user')->find(1);
            //     Db::table('think_user')->delete(1);
            // });
        // 启动事务
Db::startTrans();
try {
   

      

             //更新充值订单状态
    Db::table('jdy_chongzhi')->where("out_trade_no=$out_trade_no")->update(['transaction_id'=>$transaction_id,
    'trade_type'=>$trade_type,
    'trade_state'=>$trade_state,
    'trade_state_desc'=>$trade_state_desc,
    'success_time'=>$success_time,
    'openid'=>$openid,
    'payer_total'=>$payer_total,
    'status'=>1
    ]);


    //查找当前订单用户
      $member=  Db::table('jdy_members')->where("uid=$uid")->find();
      $q_balance = $member['balance'];
      $price = $payer_total/100;
      $balance = $q_balance + $price;
      $descriction = $chongzhifind['descriction'];

      //  //更新用户余额变化记录
    Db::table('jdy_balance_list')->insertGetId(['q_balance'=>$q_balance,'price'=>$price,'balance'=>$balance,'description'=>$descriction,'uid'=>$uid,'addtime'=>time()]);

    //更新用户余额变化
    Db::table('jdy_members')->where("uid=$uid")->update(['balance'=>$balance]);
    // 提交事务
    Db::commit();

        

	//应答微信支付已处理该订单的通知
    return json_encode(['code' => 'SUCCESS', 'message' => '']);
  
} catch (\Exception $e) {
    // 回滚事务
    Db::rollback();


	//应答微信支付已处理该订单的通知
	return "更新数据失败";
}

          




         }else{
             return "金额不对";
         }
        }

         }else{
             return "没有查到对应的订单";
         }

    }else{
        return "支付失败";
    }

// 商户对resource对象进行解密后，得到的资源对象示例


// {
//     "transaction_id":"1217752501201407033233368018",
//     "amount":{
//         "payer_total":100,
//         "total":100,
//         "currency":"CNY",
//         "payer_currency":"CNY"
//     },
//     "mchid":"1230000109",
//     "trade_state":"SUCCESS",
//     "bank_type":"CMC",
//     "promotion_detail":[
//         {
//             "amount":100,
//             "wechatpay_contribute":0,
//             "coupon_id":"109519",
//             "scope":"GLOBAL",
//             "merchant_contribute":0,
//             "name":"单品惠-6",
//             "other_contribute":0,
//             "currency":"CNY",
//             "stock_id":"931386",
//             "goods_detail":[
//                 {
//                     "goods_remark":"商品备注信息",
//                     "quantity":1,
//                     "discount_amount":1,
//                     "goods_id":"M1006",
//                     "unit_price":100
//                 },
//                 {
//                     "goods_remark":"商品备注信息",
//                     "quantity":1,
//                     "discount_amount":1,
//                     "goods_id":"M1006",
//                     "unit_price":100
//                 }
//             ]
//         },
//         {
//             "amount":100,
//             "wechatpay_contribute":0,
//             "coupon_id":"109519",
//             "scope":"GLOBAL",
//             "merchant_contribute":0,
//             "name":"单品惠-6",
//             "other_contribute":0,
//             "currency":"CNY",
//             "stock_id":"931386",
//             "goods_detail":[
//                 {
//                     "goods_remark":"商品备注信息",
//                     "quantity":1,
//                     "discount_amount":1,
//                     "goods_id":"M1006",
//                     "unit_price":100
//                 },
//                 {
//                     "goods_remark":"商品备注信息",
//                     "quantity":1,
//                     "discount_amount":1,
//                     "goods_id":"M1006",
//                     "unit_price":100
//                 }
//             ]
//         }
//     ],
//     "success_time":"2018-06-08T10:34:56+08:00",
//     "payer":{
//         "openid":"oUpF8uMuAJO_M2pxb1Q9zNjWeS6o"
//     },
//     "out_trade_no":"1217752501201407033233368018",
//     "appid":"wxd678efh567hg6787",
//     "trade_state_desc":"支付成功",
//     "trade_type":"MICROPAY",
//     "attach":"自定义数据",
//     "scene_info":{
//         "device_id":"013467007045764"
//     }
// }




}else{
    echo "不支持";
}




    }

    


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
