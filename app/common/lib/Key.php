<?php 
namespace app\common\lib;

class Key {

    /**
     * 分页默认返回数据
     */
    public static  function userCart($userId){ 
        return config("redis.cart_pre").$userId;
    }
}