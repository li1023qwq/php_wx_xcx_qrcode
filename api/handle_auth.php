<?php
require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../config/Utils.php';

// 处理跨域
Utils::handleCORS($config['allow_origins']);

try {
    // 获取参数
    $scene = isset($_GET['scene']) ? trim($_GET['scene']) : '';
    $action = isset($_GET['action']) ? trim($_GET['action']) : 'confirm'; // confirm or cancel
    
    if (empty($scene)) {
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
    
    // 根据action处理不同的授权结果
    if ($action === 'confirm') {
        // 生成token
        $token = Utils::generateToken($scene, $config['token_salt']);
        
        // 更新记录
        $updateData = [
            'status' => 3,
            'token' => $token,
            'authTime' => time(),
            'expire' => 2 // 授权成功后设置二维码为已过期
        ];
        
        $db->table('wx_qrcodelogin')->update(['scene' => $scene], $updateData);
        
        // 删除二维码图片
        $qrcodePath = QRCODE_PATH . $scene . '.png';
        if (file_exists($qrcodePath)) {
            unlink($qrcodePath);
        }
        
        Utils::jsonResponse([
            'code' => 200,
            'msg' => '授权成功'
        ]);
    } else {
        // 取消授权
        $updateData = [
            'status' => 4,
            'authTime' => time(),
            'expire' => 2 // 取消授权后设置二维码为已过期
        ];
        
        $db->table('wx_qrcodelogin')->update(['scene' => $scene], $updateData);
        
        // 删除二维码图片
        $qrcodePath = QRCODE_PATH . $scene . '.png';
        if (file_exists($qrcodePath)) {
            unlink($qrcodePath);
        }
        
        Utils::jsonResponse([
            'code' => 200,
            'msg' => '已取消授权'
        ]);
    }
    
} catch (Exception $e) {
    Utils::jsonResponse([
        'code' => 500,
        'msg' => $e->getMessage()
    ]);
} 