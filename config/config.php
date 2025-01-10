<?php
// 数据库配置
$config = [
    // 数据库配置
    'db_host' => 'localhost',       // 数据库服务器
    'db_name' => 'wanye',           // 数据库名
    'db_user' => 'wanye',           // 数据库用户名
    'db_pass' => '123456',          // 数据库密码
    'db_port' => '3306',            // 数据库端口
    'db_charset' => 'utf8mb4',      // 数据库字符集
    
    // 微信小程序配置
    'appid' => 'wx1a72e0508230ff3e',         // 小程序AppID
    'appsecret' => 'dd2c9f8776f8babdb98966d34dfeac7c',    // 小程序AppSecret
    
    // 系统配置
    'qrcode_expire_time' => 300,          // 二维码有效期（秒）
    'polling_interval' => 1500,           // 轮询间隔（毫秒）
    'max_polling_count' => 60,            // 最大轮询次数
    
    // 安全配置
    'token_salt' => 'your_salt_here',     // Token加密盐值
    'allow_origins' => ['*'],             // 允许的跨域来源
];

// 定义系统常量
define('BASE_PATH', dirname(__DIR__));
define('QRCODE_PATH', BASE_PATH . '/assets/qrcodes/');
define('ACCESS_TOKEN_FILE', BASE_PATH . '/config/access_token.php');

// 返回配置
return $config; 