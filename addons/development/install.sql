CREATE TABLE `ohyueo_develo_curdlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT 'curd列表标题',
  `navid` int(11) unsigned DEFAULT '0' COMMENT '顶级菜单id',
  `model` varchar(255) DEFAULT NULL COMMENT '模块名称',
  `action` varchar(255) DEFAULT NULL COMMENT '方法名称',
  `table` varchar(255) DEFAULT NULL COMMENT '数据表名称',
  `beizhu` varchar(255) DEFAULT NULL COMMENT '表备注',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `role_type` int(3) DEFAULT '1' COMMENT '生成权限  1是  2否',
  `daochu` int(3) DEFAULT '1' COMMENT '导出  1是  2否',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='curd列表';
CREATE TABLE `ohyueo_develo_curdtable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listid` int(11) DEFAULT '0' COMMENT '列表id',
  `name` varchar(155) DEFAULT NULL COMMENT '数据表名称',
  `type` int(3) DEFAULT '1' COMMENT '字段类型  1 varchar  2int',
  `length` varchar(55) DEFAULT NULL COMMENT '字段长度',
  `default` varchar(255) DEFAULT NULL COMMENT '默认值',
  `beizhu` varchar(255) DEFAULT NULL COMMENT '备注',
  `style` int(3) DEFAULT '1' COMMENT '字段类型  1 文本框  2单选框',
  `val` varchar(125) DEFAULT NULL COMMENT '表单值，多个用,隔开',
  `search` int(3) DEFAULT '0' COMMENT '搜索该字段  0否  1是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='curd的数据表';