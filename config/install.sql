-- 创建数据库
CREATE DATABASE IF NOT EXISTS `wanye` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `wanye`;

-- 创建二维码登录表
CREATE TABLE IF NOT EXISTS `wx_qrcodelogin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scene` varchar(32) NOT NULL COMMENT '场景值',
  `openid` varchar(64) DEFAULT NULL COMMENT '用户openid',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1未扫码 2已扫码 3已授权 4已取消',
  `expire` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否过期：1未过期 2已过期',
  `token` varchar(32) DEFAULT NULL COMMENT '登录token',
  `createTime` int(11) NOT NULL COMMENT '创建时间',
  `scanTime` int(11) DEFAULT NULL COMMENT '扫码时间',
  `authTime` int(11) DEFAULT NULL COMMENT '授权时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_scene` (`scene`),
  KEY `idx_openid` (`openid`),
  KEY `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信扫码登录记录'; 