# 微信小程序扫码登录系统

这是一个基于PHP的微信小程序扫码登录系统，提供了完整的扫码登录解决方案。

## 功能特点

- 安全的数据库操作
- 完善的错误处理
- 清晰的代码组织
- 美观的用户界面
- 实时状态更新
- 自动清理过期二维码
- 完整的CORS支持
- 安全的Token生成

## 系统要求

- PHP 7.0+
- MySQL 5.6+
- PDO PHP扩展
- CURL PHP扩展
- 支持PHP的Web服务器（Apache/Nginx）

## 安装步骤

1. 创建数据库：
   ```bash
   mysql -u your_username -p < config/install.sql
   ```

2. 配置系统：
   - 复制`config/config.php`为`config/config.local.php`
   - 修改数据库配置
   - 填入微信小程序的appid和appsecret

3. 设置目录权限：
   ```bash
   chmod 777 assets/qrcodes
   ```

4. 配置Web服务器：
   - 将网站根目录指向项目目录
   - 确保PHP有足够的执行权限

## 目录结构

```
php3_qrcode/
├── api/                    # API接口目录
│   ├── create_qrcode.php   # 创建二维码
│   ├── check_status.php    # 检查状态
│   ├── handle_scan.php     # 处理扫码
│   └── handle_auth.php     # 处理授权
├── assets/                 # 静态资源目录
│   ├── css/               # CSS文件
│   ├── js/                # JavaScript文件
│   ├── images/            # 图片资源
│   └── qrcodes/           # 二维码图片存储目录
├── config/                 # 配置文件目录
│   ├── config.php         # 配置文件
│   ├── Database.php       # 数据库类
│   ├── Utils.php          # 工具类
│   └── install.sql        # 数据库安装文件
└── index.html             # 主页面
```

## API接口说明

### 1. 创建二维码
- URL: `/api/create_qrcode.php`
- 方法: GET
- 返回示例:
  ```json
  {
    "code": 200,
    "msg": "创建成功",
    "data": {
      "scene": "1234567",
      "qrcode": "1234567.png",
      "expire_time": 300
    }
  }
  ```

### 2. 检查状态
- URL: `/api/check_status.php`
- 方法: GET
- 参数: scene=场景值
- 返回示例:
  ```json
  {
    "code": 200,
    "msg": "登录成功",
    "data": {
      "token": "xxx",
      "openid": "xxx"
    }
  }
  ```

### 3. 处理扫码
- URL: `/api/handle_scan.php`
- 方法: GET
- 参数: 
  - scene=场景值
  - code=小程序code
- 返回示例:
  ```json
  {
    "code": 200,
    "msg": "扫码成功"
  }
  ```

### 4. 处理授权
- URL: `/api/handle_auth.php`
- 方法: GET
- 参数:
  - scene=场景值
  - action=confirm/cancel
- 返回示例:
  ```json
  {
    "code": 200,
    "msg": "授权成功"
  }
  ```

## 注意事项

1. 安全性：
   - 请务必修改配置文件中的token_salt
   - 建议启用HTTPS
   - 注意保护好appid和appsecret

2. 性能：
   - 建议设置合适的轮询间隔
   - 定期清理过期的二维码文件
   - 合理设置二维码有效期

3. 兼容性：
   - 确保服务器支持CORS
   - 注意IE浏览器的兼容性
   - 移动端页面自适应

## 常见问题

1. 二维码无法生成
   - 检查access_token是否正确
   - 确保qrcodes目录可写
   - 查看PHP错误日志

2. 无法保存到数据库
   - 检查数据库配置
   - 确保表结构正确
   - 查看数据库连接权限

3. 跨域问题
   - 检查CORS配置
   - 确保域名在允许列表中
   - 使用正确的请求方法

## 更新日志

### v1.0.0 (2025-01-10)
- 初始版本发布
- 完整的扫码登录功能
- 基础的错误处理
- 简单的用户界面

## 作者

- 作者：晚夜深秋

## 许可证

MIT License 