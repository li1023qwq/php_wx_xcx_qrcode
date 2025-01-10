<?php
class Utils {
    // 生成随机scene值
    public static function generateScene() {
        return mt_rand(1000000, 9999999);
    }
    
    // 生成Token
    public static function generateToken($scene, $salt) {
        return md5($scene . $salt . time());
    }
    
    // 获取access_token
    public static function getAccessToken($config) {
        $cacheFile = ACCESS_TOKEN_FILE;
        
        if (file_exists($cacheFile)) {
            $cacheData = include($cacheFile);
            if ($cacheData['expire_time'] > time()) {
                return $cacheData['access_token'];
            }
        }
        
        return self::refreshAccessToken($config);
    }
    
    // 刷新access_token
    private static function refreshAccessToken($config) {
        $url = sprintf(
            "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s",
            $config['appid'],
            $config['appsecret']
        );
        
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        
        if (isset($data['access_token'])) {
            $cacheData = [
                'access_token' => $data['access_token'],
                'expire_time' => time() + 7200
            ];
            
            file_put_contents(
                ACCESS_TOKEN_FILE,
                "<?php return " . var_export($cacheData, true) . ";"
            );
            
            return $data['access_token'];
        }
        
        throw new Exception('获取access_token失败');
    }
    
    // 检查二维码是否过期
    public static function isQrcodeExpired($createTime, $expireTime) {
        return (time() - $createTime) > $expireTime;
    }
    
    // JSON响应
    public static function jsonResponse($data) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 处理跨域
    public static function handleCORS($allowOrigins) {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            if (in_array('*', $allowOrigins) || in_array($_SERVER['HTTP_ORIGIN'], $allowOrigins)) {
                header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Max-Age: 86400');
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            }
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }
            exit(0);
        }
    }
    
    // 清理过期的二维码文件和记录
    public static function cleanExpiredQrcodes($db) {
        try {
            // 开始事务
            $db->beginTransaction();
            
            // 获取所有过期或已完成的记录
            $expiredRecords = $db->table('wx_qrcodelogin')
                ->select([
                    'expire' => 2
                ]);
            
            foreach ($expiredRecords as $record) {
                // 删除二维码文件
                $qrcodePath = QRCODE_PATH . $record['scene'] . '.png';
                if (file_exists($qrcodePath)) {
                    unlink($qrcodePath);
                }
            }
            
            // 删除超过24小时的记录
            $oneDayAgo = time() - 86400;
            $db->table('wx_qrcodelogin')->delete([
                'createTime <' => $oneDayAgo
            ]);
            
            // 提交事务
            $db->commit();
            
            return true;
        } catch (Exception $e) {
            // 回滚事务
            $db->rollback();
            throw $e;
        }
    }
} 