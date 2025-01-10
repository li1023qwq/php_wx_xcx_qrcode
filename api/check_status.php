<?php
require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../config/Utils.php';

// 处理跨域
Utils::handleCORS($config['allow_origins']);

try {
    // 获取scene参数
    $scene = isset($_GET['scene']) ? trim($_GET['scene']) : '';
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
        // 更新状态为已过期
        $db->table('wx_qrcodelogin')->update(
            ['scene' => $scene],
            ['expire' => 2]
        );
        
        Utils::jsonResponse([
            'code' => 408,
            'msg' => '二维码已过期'
        ]);
    }
    
    // 根据状态返回不同的结果
    switch ($record['status']) {
        case 1:
            $response = [
                'code' => 201,
                'msg' => '等待扫码'
            ];
            break;
            
        case 2:
            $response = [
                'code' => 202,
                'msg' => '已扫码，等待确认'
            ];
            break;
            
        case 3:
            $response = [
                'code' => 200,
                'msg' => '登录成功',
                'data' => [
                    'token' => $record['token'],
                    'openid' => $record['openid']
                ]
            ];
            break;
            
        case 4:
            $response = [
                'code' => 203,
                'msg' => '已取消授权'
            ];
            break;
            
        default:
            $response = [
                'code' => 204,
                'msg' => '未知状态'
            ];
    }
    
    Utils::jsonResponse($response);
    
} catch (Exception $e) {
    Utils::jsonResponse([
        'code' => 500,
        'msg' => $e->getMessage()
    ]);
} 