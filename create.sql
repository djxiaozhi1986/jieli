DROP TABLE IF EXISTS `jl_users`;
CREATE TABLE `jl_users`  (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `username` varchar(50) NULL COMMENT '用户',
  `password` varchar(128) NULL COMMENT '密码',
  `realname` varchar(50) NULL COMMENT '真实姓名',
  `nikename` varchar(50) NULL COMMENT '昵称',
  `phone` varchar(18) NULL COMMENT '手机号码',
  `avator` varchar(255) NULL COMMENT '头像地址',
  `created_at` int(11) NULL COMMENT '创建时间',
  `updated_at` int(11) NULL COMMENT '更新时间',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态 0禁用，1启用，默认1',
  PRIMARY KEY (`user_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `jl_users_vendor_login`;
CREATE TABLE `jl_users_vendor_login` (
  `vendor_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '三方登录自增id',
  `user_id` int(11) NOT NULL COMMENT '用户自增id',
  `user_logintype` tinyint(1) DEFAULT NULL COMMENT '用户注册类型 1为qq，2为微博，3为微信,4,github',
  `user_head` varchar(255) NOT NULL COMMENT '用户头像',
  `unionid` varchar(100) DEFAULT NULL COMMENT '用户唯一ID',
  `open_id` varchar(100) DEFAULT NULL COMMENT '第三方openid',
  `access_token` varchar(255) DEFAULT NULL COMMENT '第三方的授权令牌',
  `user_nickname` varchar(60) NOT NULL COMMENT '第三方用户昵称',
  `user_sex` enum('男','女','保密') DEFAULT NULL COMMENT '用户性别',
  PRIMARY KEY (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='三方登录表';
