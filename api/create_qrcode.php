<?php
require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../config/Utils.php';

// 处理跨域
Utils::handleCORS($config['allow_origins']);

try {
    // 获取数据库实例
    $db = Database::getInstance($config);
    
    // 清理过期的二维码
    Utils::cleanExpiredQrcodes($db);
    
    // 获取access_token
    $access_token = Utils::getAccessToken($config);
    
    // 生成scene
    $scene = Utils::generateScene();
    
    // 创建小程序码
    $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
    $data = [
        "page" => "pages/wxlogin/wxlogin",
        "scene" => $scene,
        "check_path" => false,
        "env_version" => "develop"
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    // 确保qrcodes目录存在
    if (!file_exists(QRCODE_PATH)) {
        mkdir(QRCODE_PATH, 0777, true);
    }
    
    // 保存二维码图片
    $qrcodePath = $scene . '.png';
    file_put_contents(QRCODE_PATH . $qrcodePath, $result);
    
    // 写入数据库
    $insertData = [
        'scene' => $scene,
        'status' => 1, // 1:未扫码 2:已扫码 3:已授权 4:已取消
        'expire' => 1, // 1:未过期 2:已过期
        'createTime' => time()
    ];
    
    $db->table('wx_qrcodelogin')->insert($insertData);
    
    // 返回结果
    Utils::jsonResponse([
        'code' => 200,
        'msg' => '创建成功',
        'data' => [
            'scene' => $scene,
            'qrcode' => $qrcodePath,
            'expire_time' => $config['qrcode_expire_time']
        ]
    ]);
    
} catch (Exception $e) {
    Utils::jsonResponse([
        'code' => 500,
        'msg' => $e->getMessage()
    ]);
} 