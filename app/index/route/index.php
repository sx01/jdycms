<?php 

use think\facade\Route;
Route::rule("index/test","index/hello","GET");
Route::get('verify','verify/verify');
// Route::rule("detail","detail/index","GET")->middleware(\app\index\middleware\Detail::Class);