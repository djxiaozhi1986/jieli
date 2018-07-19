CREATE TABLE `jl_carts` (
  `cart_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '购物车id',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `course_id` int(11) DEFAULT NULL COMMENT '课程id',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`cart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='购物车';

CREATE TABLE `jl_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '评论id',
  `course_id` int(11) DEFAULT NULL COMMENT '课程id',
  `parent_id` int(11) DEFAULT NULL COMMENT '评论那条评论',
  `content` varchar(255) DEFAULT NULL COMMENT '评论内容',
  `from_user` int(11) DEFAULT NULL COMMENT '评论人id',
  `from_user_name` varchar(255) DEFAULT NULL COMMENT '评论人昵称',
  `to_user` int(11) DEFAULT NULL COMMENT '被评论人',
  `to_user_name` varchar(255) DEFAULT NULL COMMENT '被评论人昵称',
  `created_at` int(11) DEFAULT NULL COMMENT '创建时间',
  `status` tinyint(1) DEFAULT '0' COMMENT '课程状态0,禁用，不显示；1，正常',
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='评论表';


CREATE TABLE `jl_courses` (
  `course_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '课程id',
  `title` varchar(255) DEFAULT NULL COMMENT '课程名称',
  `description` varchar(1000) DEFAULT NULL COMMENT '内容简介',
  `lecturer_id` int(11) DEFAULT NULL COMMENT '讲师id',
  `lecturer_name` varchar(50) DEFAULT NULL COMMENT '讲师姓名或昵称',
  `cover` varchar(300) DEFAULT NULL COMMENT '封皮地址',
  `old_price` decimal(10,2) DEFAULT NULL COMMENT '原价',
  `now_price` decimal(10,2) DEFAULT NULL COMMENT '现价',
  `is_live` tinyint(1) DEFAULT NULL COMMENT '课程类别，0视频课程，1直播课程',
  `audio_url` varchar(500) DEFAULT NULL COMMENT '课程视频地址',
  `is_good` tinyint(1) DEFAULT '0' COMMENT '是否为精华课程',
  `is_home` tinyint(1) DEFAULT '0' COMMENT '推荐到首页',
  `opened_at` int(11) DEFAULT NULL COMMENT '开课时间',
  `closed_at` int(11) DEFAULT NULL COMMENT '关闭时间',
  `created_at` int(11) DEFAULT NULL COMMENT '创建时间',
  `create_user` int(11) DEFAULT NULL COMMENT '创建人',
  `updated_at` int(11) DEFAULT NULL COMMENT '更新人',
  `update_user` int(11) DEFAULT NULL COMMENT '更新人',
  `status` tinyint(1) DEFAULT NULL COMMENT '课程状态0,禁用，不显示；1，正常，2，已结束',
  PRIMARY KEY (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='微课表';


CREATE TABLE `jl_favorites` (
  `favorite_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '收藏id',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `course_id` int(11) DEFAULT NULL COMMENT '微课id',
  `created_at` int(11) DEFAULT NULL COMMENT '收藏时间',
  PRIMARY KEY (`favorite_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='收藏表';


CREATE TABLE `jl_foots` (
  `foot_id` bigint(19) unsigned NOT NULL AUTO_INCREMENT COMMENT '足迹id',
  `course_id` int(11) DEFAULT NULL COMMENT '课程id',
  `in_time` int(11) DEFAULT NULL COMMENT '浏览时间',
  `out_time` int(11) DEFAULT NULL COMMENT '停留时间',
  PRIMARY KEY (`foot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='浏览足迹';


CREATE TABLE `jl_lecturers` (
  `lecturer_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '讲师ID',
  `lecturer_name` varchar(100) DEFAULT NULL COMMENT '讲师姓名或昵称',
  `lecturer_title` varchar(255) DEFAULT NULL COMMENT '称谓',
  `description` varchar(500) DEFAULT NULL COMMENT '简介',
  `created_at` int(11) DEFAULT NULL,
  `create_user` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `update_user` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1' COMMENT '是否启用，0,禁用，1，启用',
  PRIMARY KEY (`lecturer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='讲师表';


CREATE TABLE `jl_orders` (
  `order_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单id',
  `order_title` varchar(100) DEFAULT NULL COMMENT '订单标题',
  `order_no` varchar(128) NOT NULL COMMENT '订单号',
  `order_amount` decimal(11,2) DEFAULT NULL COMMENT '订单金额',
  `order_plat` tinyint(1) DEFAULT NULL COMMENT '支付平台,0微信，1支付宝，2天鹅币',
  `transaction_no` varchar(129) DEFAULT NULL COMMENT '平台交易号',
  `created_at` int(11) DEFAULT NULL COMMENT '订单生成时间',
  `completed_at` int(11) DEFAULT NULL COMMENT '支付完成时间',
  `order_status` tinyint(1) DEFAULT '0' COMMENT '订单状态,0:支付中,1:完成支付,2:取消支付/支付失败',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `course_id` int(11) DEFAULT NULL COMMENT '课程id',
  `remarks` varchar(500) DEFAULT NULL COMMENT '订单备注',
  `order_type` tinyint(1) DEFAULT '0' COMMENT '0,商品订单；1,充值订单',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='订单';



CREATE TABLE `jl_praises` (
  `praise_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '点赞id',
  `from_user` int(11) DEFAULT NULL COMMENT '点赞用户',
  `course_id` int(11) DEFAULT NULL COMMENT '微课id',
  `comment_id` int(11) DEFAULT NULL COMMENT '评论id',
  `created_at` int(11) DEFAULT NULL COMMENT '点赞时间',
  PRIMARY KEY (`praise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='点赞表';


CREATE TABLE `jl_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `username` varchar(50) DEFAULT NULL COMMENT '用户',
  `password` varchar(128) DEFAULT NULL COMMENT '密码',
  `realname` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `nickname` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '昵称',
  `phone` varchar(18) DEFAULT NULL COMMENT '手机号码',
  `avator` varchar(255) DEFAULT NULL COMMENT '头像地址',
  `e_coin` int(11) DEFAULT '0' COMMENT '天鹅币',
  `score` int(11) DEFAULT '0' COMMENT '我的积分',
  `sex` tinyint(1) DEFAULT '1' COMMENT '性别:0女，1男',
  `birthday` int(11) DEFAULT NULL COMMENT '生日',
  `user_token` varchar(255) DEFAULT NULL COMMENT '用户接口验证token',
  `user_token_expire` int(11) DEFAULT NULL COMMENT 'token过期时间',
  `is_bound` tinyint(1) DEFAULT '0' COMMENT '是否绑定',
  `device_type` tinyint(1) DEFAULT NULL COMMENT '设备型号',
  `device_id` varchar(100) DEFAULT NULL COMMENT '设备id',
  `last_time` int(11) DEFAULT NULL COMMENT '最后登录时间',
  `last_ip` varchar(30) DEFAULT NULL COMMENT '最后登录IP',
  `created_at` int(11) DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) DEFAULT NULL COMMENT '更新时间',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态 0禁用，1启用，默认1',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='用户表';


CREATE TABLE `jl_users_coin_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '天鹅币日志id',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `coin_value` int(11) DEFAULT NULL COMMENT '天鹅币变化，正负数',
  `log_at` int(11) DEFAULT NULL COMMENT '日志时间',
  `change_type` tinyint(1) DEFAULT NULL COMMENT '0充值，1活动奖励，2违规处罚',
  `order_id` int(11) DEFAULT NULL COMMENT '充值订单id',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='天鹅币日志';


CREATE TABLE `jl_users_courses_relation` (
  `relation_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '关联id',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `course_id` int(11) DEFAULT NULL COMMENT '微课id',
  `create_at` int(11) DEFAULT NULL COMMENT '关联时间',
  PRIMARY KEY (`relation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户微课表';


CREATE TABLE `jl_users_score_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '积分日志id',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `score` int(11) DEFAULT NULL COMMENT '积分变化，正负数',
  `log_at` int(11) DEFAULT NULL COMMENT '日志时间',
  `log_desc` varchar(1000) DEFAULT NULL COMMENT '日志描述',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分日志';



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