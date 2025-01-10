<?php
require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../config/Utils.php';

// 处理跨域
Utils::handleCORS($config['allow_origins']);

try {
    // 获取参数
    $scene = isset($_GET['scene']) ? trim($_GET['scene']) : '';
    $code = isset($_GET['code']) ? trim($_GET['code']) : '';
    
    if (empty($scene) || empty($code)) {
        throw new Exception('参数错误');
    }
    
    // 获取数据库实例
    $db = Database::getInstance($config);
    
    // 查询记录
    $record = $db->table('wx_qrcodelogin')->find(['scene' => $scene]);
    if (!$record) {
        throw new Exception('二维码不存在');
    }
    
    // 检查是否过期
    if (Utils::isQrcodeExpired($record['createTime'], $config['qrcode_expire_time'])) {
        $db->table('wx_qrcodelogin')->update(
            ['scene' => $scene],
            ['expire' => 2]
        );
        throw new Exception('二维码已过期');
    }
    
    // 获取openid
    $url = sprintf(
        'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code',
        $config['appid'],
        $config['appsecret'],
        $code
    );
    
    $response = file_get_contents($url);
    $result = json_decode($response, true);
    
    if (!isset($result['openid'])) {
        throw new Exception('获取openid失败');
    }
    
    // 更新记录
    $updateData = [
        'openid' => $result['openid'],
        'status' => 2,
        'scanTime' => time()
    ];
    
    $db->table('wx_qrcodelogin')->update(['scene' => $scene], $updateData);
    
    Utils::jsonResponse([
        'code' => 200,
        'msg' => '扫码成功'
    ]);
    
} catch (Exception $e) {
    Utils::jsonResponse([
        'code' => 500,
        'msg' => $e->getMessage()
    ]);
} 