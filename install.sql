CREATE TABLE IF NOT EXISTS `general_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(12) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `password` varchar(35) NOT NULL,
  `gid` int(11) NOT NULL DEFAULT '1',
  `addtime` int(11) NOT NULL,
  `lastlogin` int(11) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `text` varchar(255) DEFAULT NULL COMMENT '官方条款',
  `updatetime` datetime  COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
INSERT INTO `general_admin` VALUES (1,'admin','管理员','46d4d1db93368a6985e9e4b7b8c4bdcc',1,0,0,1,NULL,NULL);
CREATE TABLE IF NOT EXISTS `general_admin_action_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  `text` varchar(255) DEFAULT NULL,
  `addtime` char(11) DEFAULT NULL,
  `ip` varchar(55) DEFAULT NULL COMMENT 'ip地址',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='后台人员操作记录';
CREATE TABLE IF NOT EXISTS `general_admin_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(16) NOT NULL,
  `logintime` int(11) NOT NULL DEFAULT '0',
  `loginip` varchar(255) NOT NULL,
  `status` int(11) DEFAULT '1',
  `info` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `city` varchar(155) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `general_admin_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '权限名称',
  `controller` varchar(50) DEFAULT NULL COMMENT '控制器',
  `action` varchar(255) DEFAULT NULL COMMENT '方法名',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `is_nav` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是菜单',
  `icon` varchar(30) DEFAULT NULL,
  `p_id` int(3) DEFAULT '0' COMMENT '排序id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='权限表';
INSERT INTO `general_admin_permission` VALUES
(1,'主页',NULL,NULL,0,1,'layui-icon-home',1),
(2,'应用管理',NULL,NULL,0,1,'layui-icon-template-1',2),
(3,'权限管理',NULL,NULL,0,1,'layui-icon-auz',4),
(4,'管理员列表','Admin','userlist',3,1,NULL,0),
(5,'角色列表','Admin','rolelist',3,1,NULL,0),
(6,'编辑','Admin/userlist','edit',4,0,NULL,0),
(7,'添加','Admin/userlist','add',4,0,NULL,0),
(8,'删除','Admin/userlist','del',4,0,NULL,0),
(9,'编辑','Admin/rolelist','editrole',5,0,NULL,0),
(10,'添加','Admin/rolelist','addrole',5,0,NULL,0),
(11,'删除','Admin/rolelist','delrole',5,0,NULL,0),
(12,'轮播图','Zong','zhimg',2,1,NULL,0),
(13,'添加','Zong/zhimg','add',12,0,NULL,0),
(14,'删除','Zong/zhimg','del',12,0,NULL,0),
(15,'修改','Zong/zhimg','edit',12,0,NULL,0),
(16,'管理员日志','Admin','actionlog',3,1,NULL,0),
(17,'控制台','Home','console',1,1,NULL,0),
(18,'用户管理','','',0,1,'layui-icon-user',4),
(19,'用户列表','User','userlist',18,1,NULL,0),
(20,'添加','User/userlist','add',19,0,NULL,0),
(21,'编辑','User/userlist','edit',19,0,NULL,0),
(22,'删除','User/userlist','del',19,0,NULL,0),
(23,'预约管理',NULL,NULL,0,1,' layui-icon-app',2),
(24,'预约列表','Yuyue','yuyuelist',23,1,NULL,1),
(25,'预约分类','Yuyue','yuyueclass',23,1,NULL,0),
(26,'删除','Yuyue/yuyuelist','del',24,0,NULL,0),
(27,'修改','Yuyue/yuyuelist','edit',24,0,NULL,0),
(28,'添加','Yuyue/yuyuelist','add',24,0,NULL,0),
(29,'删除','Yuyue/yuyueclass','delclass',25,0,NULL,0),
(30,'添加','Yuyue/yuyueclass','addclass',25,0,NULL,0),
(31,'修改','Yuyue/yuyueclass','editclass',25,0,NULL,0),
(32,'预约订单',NULL,NULL,0,1,'layui-icon-form',3),
(33,'订单列表','Order','orderlist',32,1,NULL,2),
(34,'删除','Order/orderlist','del',33,0,NULL,0),
(35,'黑名单','User','userlist_hei',18,1,NULL,0),
(36,'黑名单','User/userlist','hei',19,0,NULL,0),
(37,'黑名单','User/userlist_hei','hei',35,0,NULL,0),
(38,'详情','User/userlist_hei','info',35,0,NULL,0),
(39,'详情','User/userlist','info',19,0,NULL,0),
(40,'设置',NULL,NULL,0,1,'layui-icon-set',5),
(41,'接口设置','','',40,2,NULL,0),
(42,'短信设置','Set','smssite',41,1,NULL,0),
(43,'系统公告','Zong','announce',2,1,NULL,0),
(44,'添加','Zong/announce','addan',43,0,NULL,0),
(45,'修改','Zong/announce','editan',43,0,NULL,0),
(46,'删除','Zong/announce','delan',43,0,NULL,0),
(47,'预约时间','Yuyue','yuyuetime',23,1,NULL,6),
(48,'添加','Yuyue/yuyuetime','addtime',47,0,NULL,0),
(49,'修改','Yuyue/yuyuetime','edittime',47,0,NULL,0),
(50,'删除','Yuyue/yuyuetime','deltime',47,0,NULL,0),
(51,'预约表单','Yuyue','edit_form',24,1,NULL,0),
(52,'详情','Order/orderlist','info',33,0,NULL,0),
(53,'修改','Order/orderlist','edit',33,0,NULL,0),
(54,'核销管理',NULL,NULL,0,1,'layui-icon-survey',3),
(55,'核销人员','Employ','employ',54,1,NULL,0),
(56,'添加','Employ/employ','add',55,0,NULL,0),
(57,'修改','Employ/employ','edit',55,0,NULL,0),
(58,'删除','Employ/employ','del',55,0,NULL,0),
(59,'系统文章','Wen','sydex',2,1,NULL,1),
(60,'修改','Wen/sydex','edit',59,0,NULL,0),
(61,'系统设置','',NULL,40,2,NULL,0),
(62,'前端设置','Set','appsite',61,1,NULL,0),
(63,'微信设置','Set','wxsite',61,1,NULL,0),
(64,'地图设置','Set','mapsite',41,1,NULL,0),
(65,'核销记录','Employ','verification',54,1,NULL,0),
(66,'删除','Employ/verification','delver',65,0,NULL,0),
(67,'导出','Order/orderlist','daochu',33,0,NULL,0),
(68,'系统设置','Set','systemsite',61,1,NULL,0),
(69, '推广管理', NULL, NULL, 0, 1, 'layui-icon-diamond', 3),
(70,'推荐人申请','Invitation','invitalist',69,1,NULL,0),
(71,'通过','Invitation/invitalist','add',70,0,NULL,0),
(72,'拒绝','Invitation/invitalist','edit',70,0,NULL,0),
(73,'删除','Invitation/invitalist','del',70,0,NULL,0),
(74,'推荐人列表','Invitation','invitation',69,1,NULL,0),
(75,'删除','Invitation/invitation','del',74,0,NULL,0),
(76,'财务信息',NULL,NULL,0,1,'layui-icon-rmb',3),
(77,'支付订单','Pay','payorder',76,1,NULL,0),
(78,'收入记录','Pay','usermoneylog',76,1,NULL,1),
(79,'提现订单','Pay','tixian',76,1,NULL,2),
(80,'删除','Pay/payorder','delpaylog',77,0,NULL,0),
(81,'删除','Pay/usermoneylog','delmoneylog',78,0,NULL,0),
(82,'通过打款','Pay/tixian','add',79,0,NULL,0),
(83,'拒绝打款','Pay/tixian','edit',79,0,NULL,0),
(84,'删除','Pay/tixian','del',79,0,NULL,0),
(85,'预约设置','',NULL,40,2,NULL,0),
(86,'时间设置','Set','timesite',85,1,NULL,0),
(87,'预约座位','Yuyue','yuyueseat',23,1,NULL,6),
(88,'添加','Yuyue/yuyueseat','addeseat',87,0,NULL,0),
(89,'修改','Yuyue/yuyueseat','editeseat',87,0,NULL,0),
(90,'删除','Yuyue/yuyueseat','deleseat',87,0,NULL,0),
(91,'新闻动态','Wen','index',2,1,NULL,0),
(92,'添加','Wen/index','addwen',91,0,NULL,0),
(93,'修改','Wen/index','editwen',91,0,NULL,0),
(94,'删除','Wen/index','delwen',91,0,NULL,0),
(95,'模板通知','Set','remindsite',85,1,NULL,0),
(96,'商品管理','Shop',NULL,0,1,'layui-icon-cart',3),
(97,'商品列表','Shop','shoplist',96,1,NULL,0),
(98,'添加商品','Shop/shoplist','add',97,0,NULL,0),
(99,'修改商品','Shop/shoplist','edit',97,0,NULL,0),
(100,'删除商品','Shop/shoplist','del',97,0,NULL,0),
(101,'商品分类','Shop','shopclass',96,1,NULL,0),
(102,'添加分类','Shop/shopclass','add',101,0,NULL,0),
(103,'删除分类','Shop/shopclass','del',101,0,NULL,0),
(104,'修改分类','Shop/shopclass','edit',101,0,NULL,0),
(105,'商品订单','Shop','orderlist',96,1,NULL,0),
(106,'删除','Shop/orderlist','del',105,0,NULL,0),
(107,'提醒设置','Set','remindsite',85,1,NULL,0),
(108,'预约人员','Yuyue','yuyuepersonnel',23,1,NULL,6),
(109,'添加','Yuyue/yuyuepersonnel','addperson',108,0,NULL,0),
(110,'修改','Yuyue/yuyuepersonnel','editperson',108,0,NULL,0),
(111,'删除','Yuyue/yuyuepersonnel','delperson',108,0,NULL,0),
(112,'前端装修','Diy','home',2,1,NULL,-1),
(113,'操作','Shop/orderlist','shopst',105,0,NULL,0),
(114,'删除表单','Yuyue','delform',24,1,NULL,0)
;

CREATE TABLE IF NOT EXISTS `general_admin_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `text` varchar(255) DEFAULT NULL COMMENT '说明',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='角色表';
INSERT INTO `general_admin_role` VALUES (1,'超级管理员','管理员');
CREATE TABLE IF NOT EXISTS `general_admin_role_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL COMMENT '角色ID',
  `permission_id` text COMMENT '权限id 1,2,3,4',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='角色权限关联表';
INSERT INTO `general_admin_role_permission` VALUES (1,1,'1,17,2,12,13,14,15,43,44,45,46,91,92,93,94,59,60,23,25,29,30,31,24,26,27,28,51,47,48,49,50,87,88,89,90,32,33,34,52,53,67,54,55,56,57,58,65,66,69,70,71,72,73,74,75,76,77,80,78,81,79,82,83,84,96,97,98,99,100,101,102,103,104,105,106,3,4,6,7,8,5,9,10,11,16,18,19,20,21,22,36,39,35,37,38,40,41,42,64,61,62,63,68,85,86');

CREATE TABLE IF NOT EXISTS `general_admin_user_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户角色关联表';
INSERT INTO `general_admin_user_role` VALUES (1,1,1),(2,2,2);


CREATE TABLE IF NOT EXISTS `general_noti_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `text` text COMMENT '详情',
  `addtime` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='公告列表';

CREATE TABLE IF NOT EXISTS `general_user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  `timo` decimal(10,2) DEFAULT '0.00' COMMENT '已提现金额（总共提现成功多少钱）',
  `lower1_total_money` decimal(10,2) DEFAULT '0.00',
  `rel1_money` decimal(10,2) DEFAULT '0.00',
  `lower2_total_money` decimal(10,2) DEFAULT '0.00',
  `rel2_money` decimal(10,2) DEFAULT '0.00',
  `isyao` int(3) DEFAULT '0' COMMENT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户数据表';

CREATE TABLE IF NOT EXISTS `general_user_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nick` varchar(55) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '用户昵称',
  `headimg` varchar(155) DEFAULT NULL COMMENT '用户头像',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '余额',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '总收入',
  `status` int(3) DEFAULT '1' COMMENT '用户状态  1正常  2封号',
  `openid` varchar(155) DEFAULT NULL,
  `xcx_openid` varchar(255) DEFAULT NULL COMMENT '',
  `unionid` varchar(255) DEFAULT NULL COMMENT '',
  `rel1` int(11) DEFAULT '0' COMMENT '上级',
  `rel2` int(11) DEFAULT '0' ,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户表';

CREATE TABLE IF NOT EXISTS `general_user_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0' COMMENT '用户id',
  `username` varchar(55) DEFAULT NULL COMMENT '登录名',
  `password` varchar(255) DEFAULT NULL COMMENT '密码',
  `reg_time` datetime  COMMENT '注册时间',
  `rece_login_time` datetime  COMMENT '最近登录时间',
  `mobile` varchar(15) DEFAULT NULL COMMENT '手机号',
  `email` varchar(35) DEFAULT NULL COMMENT '邮箱',
  `reg_ip` varchar(55) DEFAULT NULL COMMENT 'ip',
  `reg_city` varchar(255) DEFAULT NULL COMMENT '',
  `reg_province` varchar(255) DEFAULT NULL COMMENT '',
  `session_key` varchar(155) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `general_user_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0',
  `token` varchar(65) DEFAULT NULL,
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户token';

CREATE TABLE IF NOT EXISTS `general_web_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `texter` text NOT NULL,
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `general_web_text` VALUES (1,'平台协议','平台协议','2022-05-17 00:00:00'),(2,'隐私政策','隐私政策','2022-05-17 00:00:00'),
(3,'联系我们','联系我们','2022-05-17 00:00:00');

CREATE TABLE IF NOT EXISTS `general_zonghe_img` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(55) DEFAULT NULL COMMENT '名称',
  `img` varchar(155) DEFAULT NULL COMMENT '图片',
  `type` int(3) DEFAULT '1' COMMENT '图片类型 1首页轮播图  2首页方块  3首页广告位',
  `url` varchar(255) DEFAULT NULL COMMENT '打开地址',
  `style` int(3) DEFAULT '1' COMMENT '打开方式  1内部打开  2浏览器打开 3打开方法  4打开内部页面  5底部导航栏',
  `paiid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `general_yuyue_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `status` int(2) DEFAULT '1' COMMENT '1显示  2不显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='预约分类';

CREATE TABLE IF NOT EXISTS `general_yuyue_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '',
  `classid` int(11) DEFAULT '0' COMMENT '',
  `img` varchar(255) DEFAULT NULL COMMENT '',
  `allimg` varchar(255) DEFAULT NULL COMMENT '',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '',
  `pv` int(11) DEFAULT '0' COMMENT '',
  `dayno` int(11) DEFAULT '0' COMMENT '',
  `bao` int(11) DEFAULT NULL COMMENT '',
  `zno` int(11) DEFAULT NULL COMMENT '',
  `yueno` int(11) DEFAULT '0' COMMENT '',
  `address` varchar(255) DEFAULT NULL COMMENT '',
  `intro` varchar(555) DEFAULT NULL COMMENT '',
  `lat` varchar(25) DEFAULT NULL COMMENT '',
  `lng` varchar(25) DEFAULT NULL COMMENT '',
  `texter` text COMMENT '',
  `addtime` datetime DEFAULT NULL COMMENT '',
  `status` int(2) DEFAULT '1',
  `name` varchar(255) DEFAULT NULL COMMENT '',
  `mobile` varchar(255) DEFAULT NULL COMMENT '',
  `paiid` int(11) DEFAULT '0',
  `istui` int(3) DEFAULT '0',
  `is_info` int(3) DEFAULT '1',
  `recommended` varchar(155) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='';
CREATE TABLE IF NOT EXISTS `general_yuyue_img` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `img` varchar(255) DEFAULT NULL COMMENT '',
  `acid` int(11) DEFAULT '0' COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE IF NOT EXISTS `general_yuyue_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(2) DEFAULT '0' COMMENT '',
  `p_val` varchar(255) DEFAULT NULL COMMENT '',
  `t_val` varchar(255) DEFAULT NULL COMMENT '',
  `number` int(11) DEFAULT '0' COMMENT '',
  `list_id` int(11) DEFAULT '0' COMMENT '',
  `status` int(3) DEFAULT '1' COMMENT '',
  `closed` int(3) DEFAULT '1' COMMENT '',
  `paiid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `general_access_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `access_token` varchar(600) NOT NULL DEFAULT '' COMMENT '',
  `expires_time` varchar(64) DEFAULT NULL COMMENT '',
  `ticket` varchar(600) NOT NULL DEFAULT '' COMMENT '',
  `ticket_expires_time` varchar(64) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='';
CREATE TABLE IF NOT EXISTS `general_yuyue_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) DEFAULT '0' COMMENT '',
  `name` varchar(155) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT '',
  `type` int(3) DEFAULT NULL COMMENT '',
  `mandatory` int(2) DEFAULT '0' COMMENT '',
  `only` int(2) DEFAULT '0' COMMENT '',
  `val` varchar(255) DEFAULT NULL COMMENT '',
  `validate` int(11) DEFAULT '0' COMMENT '',
  `paiid` int(11) DEFAULT '0' COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='';
CREATE TABLE IF NOT EXISTS `general_yuyue_ord` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) DEFAULT '0' COMMENT '',
  `ord_id` int(11) DEFAULT '0' COMMENT '',
  `form_id` int(11) DEFAULT '0' COMMENT '',
  `type` int(3) DEFAULT '0',
  `val` varchar(255) DEFAULT NULL COMMENT '',
  `uid` int(11) DEFAULT '0',
  `paiid` int(11) DEFAULT '0',
  `validate` int(11) DEFAULT '0' COMMENT '',
  `formname` varchar(255) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='';
CREATE TABLE IF NOT EXISTS `general_yuyue_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) DEFAULT NULL COMMENT '预约id',
  `uid` int(11) DEFAULT '0' COMMENT '用户id',
  `money` decimal(10,2) DEFAULT NULL COMMENT '订单金额',
  `paymo` decimal(10,2) DEFAULT '0.00' COMMENT '付款金额',
  `addtime` datetime DEFAULT NULL COMMENT '创建时间',
  `status` int(3) DEFAULT '1' COMMENT '状态 1待付款 2待核销 3已完成 4已取消 5已退款',
  `pay_order` varchar(155) DEFAULT NULL COMMENT '支付订单',
  `cancel` int(2) DEFAULT '0' COMMENT '0 1自动取消',
  `tz` int(2) DEFAULT '0' COMMENT '通知 0 1已通知',
  `ishe` int(2) DEFAULT '0' COMMENT '',
  `personid` int(11) DEFAULT '0' COMMENT '',
  `y_data` date DEFAULT NULL,
  `y_time` varchar(235) DEFAULT NULL,
  `y_timeid` varchar(255) DEFAULT NULL,
  `specid` int(11) DEFAULT '0' COMMENT '',
  `number` int(11) DEFAULT '0' COMMENT '',
  `heno` int(11) DEFAULT '0' COMMENT '已核销的次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE IF NOT EXISTS `general_yuyue_ordermsg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(125) DEFAULT NULL COMMENT '操作说明',
  `order_id` int(11) DEFAULT '0' COMMENT '订单id',
  `status` varchar(25) DEFAULT NULL COMMENT '状态',
  `addtime` datetime DEFAULT NULL COMMENT '操作时间',
  `texter` varchar(355) DEFAULT NULL COMMENT '变动备注',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='预约状态修改记录';
CREATE TABLE IF NOT EXISTS `general_yuyue_browse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(55) DEFAULT NULL,
  `uid` int(11) DEFAULT '0',
  `no` int(11) DEFAULT '0',
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='浏览量';
CREATE TABLE IF NOT EXISTS `general_yuyue_employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(55) DEFAULT NULL COMMENT '姓名',
  `center` varchar(255) DEFAULT NULL COMMENT '备注信息',
  `uid` int(11) DEFAULT '0' COMMENT '用户id',
  `status` int(2) DEFAULT '1' COMMENT '1正常   2异常',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `role` varchar(155) DEFAULT NULL COMMENT '',
  `listval` varchar(255) DEFAULT NULL COMMENT '',
  `spec` varchar(255) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='核销员';
CREATE TABLE IF NOT EXISTS `general_yuyue_verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT '0' COMMENT '订单id',
  `list_id` int(11) DEFAULT '0' COMMENT '预约id',
  `uid` int(11) DEFAULT '0' COMMENT '核销用户',
  `emp_id` int(11) DEFAULT '0' COMMENT '员工id  核销人id',
  `addtime` datetime  COMMENT '核销时间',
  `st` int(11) DEFAULT '0' COMMENT '0预约',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '核销金额',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='核销';
CREATE TABLE IF NOT EXISTS `general_payorder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) DEFAULT '0' COMMENT '订单id',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '支付金额',
  `paymo` decimal(10,2) DEFAULT '0.00' COMMENT '实际支付金额',
  `addtime` datetime DEFAULT NULL,
  `status` int(2) DEFAULT '0' COMMENT '0失败  1成功',
  `ordernum` varchar(55) DEFAULT NULL COMMENT '订单号',
  `trade_no` varchar(155) DEFAULT NULL COMMENT '第三方交易号',
  `uid` int(11) NOT NULL,
  `paytime` datetime DEFAULT NULL,
  `type` int(2) NOT NULL DEFAULT '1' COMMENT '类型 1',
  `style` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='支付记录';
CREATE TABLE IF NOT EXISTS `general_map_regprovince` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `province` varchar(255) DEFAULT NULL COMMENT '省份',
  `name` varchar(255) DEFAULT NULL,
  `value` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='注册省份';
CREATE TABLE IF NOT EXISTS `general_invitamsg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `name` varchar(55) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `msg` varchar(555) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `status` int(3) DEFAULT '0' COMMENT '0 1 2',
  `error` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `general_user_withdrawal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0' COMMENT '',
  `prize` decimal(10,2) DEFAULT '0.00' COMMENT '',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '',
  `addtime` datetime DEFAULT NULL COMMENT '',
  `status` int(3) DEFAULT '0' COMMENT '',
  `uptime` datetime DEFAULT NULL COMMENT '',
  `weid` int(5) DEFAULT NULL,
  `type` int(3) DEFAULT '0' COMMENT '',
  `msg` VARCHAR(155) NOT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `general_user_moneylog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '',
  `text` varchar(255) DEFAULT NULL COMMENT '',
  `type` int(2) DEFAULT '1' COMMENT '',
  `mo_type` int(2) DEFAULT '1' COMMENT '',
  `shop_id` int(11) DEFAULT '0' COMMENT '',
  `yue` decimal(10,2) DEFAULT '0.00' COMMENT '',
  `addtime` datetime COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='';
CREATE TABLE IF NOT EXISTS `general_yuyue_seat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `row` int(3) DEFAULT '0',
  `column` int(3) DEFAULT '0',
  `title` varchar(355) DEFAULT NULL COMMENT '显示标题',
  `closed` varchar(355) DEFAULT NULL,
  `status` int(3) DEFAULT '1' COMMENT '1  2',
  `list_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='预约座位';
CREATE TABLE IF NOT EXISTS `general_yuyue_seatmsg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) DEFAULT '0',
  `list_id` int(11) DEFAULT '0',
  `biao` varchar(155) DEFAULT NULL,
  `title` varchar(155) DEFAULT NULL,
  `seatid` int(11) DEFAULT '0',
  `y_data` date DEFAULT NULL,
  `y_time` varchar(235) DEFAULT NULL,
  `status` int(3) DEFAULT '1' COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='选座记录';

CREATE TABLE IF NOT EXISTS `general_yuyue_texter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `img` varchar(155) DEFAULT NULL COMMENT '图片',
  `texter` text COMMENT '详情',
  `pv` int(11) DEFAULT '0' COMMENT '浏览量',
  `addtime` datetime DEFAULT NULL COMMENT '发布时间',
  `status` int(3) DEFAULT '1' COMMENT '1上线  2下线',
  `istui` int(3) DEFAULT '0' COMMENT '',
  `classid` int(11) DEFAULT '0' COMMENT '',
  `openurl` varchar(255) DEFAULT NULL COMMENT '打开网址',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章';

CREATE TABLE IF NOT EXISTS `general_shop_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '',
  `img` varchar(255) DEFAULT NULL COMMENT '',
  `paiid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `general_shop_info` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) DEFAULT '0' COMMENT '',
  `content` text COMMENT '',
  `addtime` datetime COMMENT '',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='';
CREATE TABLE IF NOT EXISTS `general_shop_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `img` varchar(155) DEFAULT NULL COMMENT '',
  `images` varchar(655) DEFAULT NULL COMMENT '',
  `title` varchar(65) DEFAULT NULL COMMENT '',
  `zmo` decimal(10,2) DEFAULT '0.00' COMMENT '',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '',
  `class_id` int(11) DEFAULT '0' COMMENT '',
  `status` int(3) DEFAULT '1' COMMENT '',
  `sales` int(11) DEFAULT '0' COMMENT '',
  `inventory` int(11) DEFAULT '0' COMMENT '',
  `sorting` int(5) DEFAULT '0' COMMENT '',
  `pv` int(11) DEFAULT '0' COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='';
CREATE TABLE IF NOT EXISTS `general_shop_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) DEFAULT '0' COMMENT '',
  `shop_no` int(11) DEFAULT '0' COMMENT '',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '',
  `pay_mo` decimal(10,2) DEFAULT '0.00' COMMENT '',
  `addtime` datetime COMMENT '',
  `source` int(3) DEFAULT '1' COMMENT '',
  `status` int(3) DEFAULT '1' COMMENT '',
  `name` varchar(155) DEFAULT '0' COMMENT '',
  `mobile` varchar(25) DEFAULT NULL COMMENT '',
  `user_id` int(11) DEFAULT '0' COMMENT '',
  `address` varchar(255) DEFAULT NULL COMMENT '',
  `message` varchar(255) DEFAULT NULL COMMENT '',
  `texter` varchar(255) DEFAULT NULL COMMENT '',
  `pay_order` varchar(155) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='';
CREATE TABLE IF NOT EXISTS `general_shop_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_orderid` int(11) DEFAULT '0' COMMENT '',
  `shop_id` int(11) DEFAULT '0' COMMENT '',
  `no` int(11) DEFAULT '0' COMMENT '',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '',
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='';
CREATE TABLE IF NOT EXISTS `general_yuyue_personnel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) DEFAULT '0',
  `status` int(3) DEFAULT '1' COMMENT '1  2',
  `title` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `Intro` varchar(355) DEFAULT NULL COMMENT '推荐标签',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `general_system_diy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) DEFAULT NULL COMMENT '名称',
  `val` varchar(25) DEFAULT NULL COMMENT '值',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='自定义风格装修';
INSERT INTO `general_system_diy` VALUES (1,'yuyue','1'),(2,'shop','1'),(3,'news','1'),(4,'paymsg','1');
CREATE TABLE `general_yuyue_yuyuespec` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT 'NULL' COMMENT '规格名称',
  `list_id` int(11) DEFAULT '0' COMMENT '预约项目',
  `money` decimal(11,2) DEFAULT '0' COMMENT '规格价格',
  `status` int(3) DEFAULT '1' COMMENT '状态',
  `paiid` int(11) DEFAULT '0' COMMENT '排序ID', 
  `number` int(11) DEFAULT '0' COMMENT '数量', 
  `heno` int(11) DEFAULT '1' COMMENT '核销次数', 
  PRIMARY KEY (`id`) 
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='预约规格';

CREATE TABLE `general_wen_wenclass` ( `id` int(11) NOT NULL AUTO_INCREMENT,`title` varchar(155) DEFAULT 'NULL' COMMENT '分类名称',`status` int(3) DEFAULT '1' COMMENT '状态',`paiid` int(11) DEFAULT '0' COMMENT '排序ID', PRIMARY KEY (`id`) ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='新闻分类';

/**2024-07-18 增加签到功能**/
CREATE TABLE IF NOT EXISTS `general_signin_codelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(155) DEFAULT 'NULL' COMMENT '二维码名称',
  `val` varchar(155) DEFAULT 'NULL' COMMENT '二维码内容',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `updatetime` datetime DEFAULT NULL COMMENT '更新时间',
  `status` int(3) DEFAULT '1' COMMENT '状态',
  `hour` int(11) DEFAULT '0' COMMENT '更新二维码间隔 小时',
  `oldval` varchar(155) DEFAULT 'NULL' COMMENT '老的内容',
  `seat` int(11) DEFAULT '0' COMMENT '座位号 可能是名称也可能是编号 or查询',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到二维码';
CREATE TABLE IF NOT EXISTS `general_signin_signmsg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listid` int(11) DEFAULT '0' COMMENT '项目名称',
  `orderid` int(11) DEFAULT '0' COMMENT '订单id',
  `uid` int(11) DEFAULT '0' COMMENT '签到用户',
  `codeid` int(11) DEFAULT '0' COMMENT '二维码',
  `msg` varchar(155) DEFAULT 'NULL' COMMENT '签到备注',
  `addtime` datetime DEFAULT NULL COMMENT '签到时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到记录';





