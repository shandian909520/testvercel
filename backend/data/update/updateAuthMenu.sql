/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50726
Source Host           : 127.0.0.1:3306
Source Database       : test2

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2022-12-16 11:08:50
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ea_system_auth
-- ----------------------------
DROP TABLE IF EXISTS `ea_system_auth`;
CREATE TABLE `ea_system_auth` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL COMMENT '权限名称',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(1:禁用,2:启用)',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注说明',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='系统权限表';

-- ----------------------------
-- Records of ea_system_auth
-- ----------------------------
INSERT INTO `ea_system_auth` VALUES ('1', '管理员', '1', '2', '测试管理员', '1588921753', '1589614331', null);
INSERT INTO `ea_system_auth` VALUES ('2', '代理', '0', '1', '代理管理员', '1667392938', '1667392938', null);
INSERT INTO `ea_system_auth` VALUES ('6', '游客权限', '3', '2', '', '1588227513', '1589591751', '1589591751');

-- ----------------------------
-- Table structure for ea_system_auth_node
-- ----------------------------
DROP TABLE IF EXISTS `ea_system_auth_node`;
CREATE TABLE `ea_system_auth_node` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `auth_id` bigint(20) unsigned DEFAULT NULL COMMENT '角色ID',
  `node_id` bigint(20) DEFAULT NULL COMMENT '节点ID',
  PRIMARY KEY (`id`),
  KEY `index_system_auth_auth` (`auth_id`) USING BTREE,
  KEY `index_system_auth_node` (`node_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=505 DEFAULT CHARSET=utf8 COMMENT='角色与节点关系表';

-- ----------------------------
-- Records of ea_system_auth_node
-- ----------------------------
INSERT INTO `ea_system_auth_node` VALUES ('1', '6', '1');
INSERT INTO `ea_system_auth_node` VALUES ('2', '6', '2');
INSERT INTO `ea_system_auth_node` VALUES ('3', '6', '9');
INSERT INTO `ea_system_auth_node` VALUES ('4', '6', '12');
INSERT INTO `ea_system_auth_node` VALUES ('5', '6', '18');
INSERT INTO `ea_system_auth_node` VALUES ('6', '6', '19');
INSERT INTO `ea_system_auth_node` VALUES ('7', '6', '21');
INSERT INTO `ea_system_auth_node` VALUES ('8', '6', '22');
INSERT INTO `ea_system_auth_node` VALUES ('9', '6', '29');
INSERT INTO `ea_system_auth_node` VALUES ('10', '6', '30');
INSERT INTO `ea_system_auth_node` VALUES ('11', '6', '38');
INSERT INTO `ea_system_auth_node` VALUES ('12', '6', '39');
INSERT INTO `ea_system_auth_node` VALUES ('13', '6', '45');
INSERT INTO `ea_system_auth_node` VALUES ('14', '6', '46');
INSERT INTO `ea_system_auth_node` VALUES ('15', '6', '52');
INSERT INTO `ea_system_auth_node` VALUES ('16', '6', '53');
INSERT INTO `ea_system_auth_node` VALUES ('346', '2', '18');
INSERT INTO `ea_system_auth_node` VALUES ('347', '2', '20');
INSERT INTO `ea_system_auth_node` VALUES ('348', '2', '69');
INSERT INTO `ea_system_auth_node` VALUES ('349', '2', '70');
INSERT INTO `ea_system_auth_node` VALUES ('350', '2', '71');
INSERT INTO `ea_system_auth_node` VALUES ('351', '2', '134');
INSERT INTO `ea_system_auth_node` VALUES ('352', '2', '135');
INSERT INTO `ea_system_auth_node` VALUES ('353', '2', '72');
INSERT INTO `ea_system_auth_node` VALUES ('354', '2', '73');
INSERT INTO `ea_system_auth_node` VALUES ('355', '2', '74');
INSERT INTO `ea_system_auth_node` VALUES ('356', '2', '75');
INSERT INTO `ea_system_auth_node` VALUES ('357', '2', '76');
INSERT INTO `ea_system_auth_node` VALUES ('358', '2', '77');
INSERT INTO `ea_system_auth_node` VALUES ('359', '2', '78');
INSERT INTO `ea_system_auth_node` VALUES ('360', '2', '79');
INSERT INTO `ea_system_auth_node` VALUES ('361', '2', '80');
INSERT INTO `ea_system_auth_node` VALUES ('362', '2', '81');
INSERT INTO `ea_system_auth_node` VALUES ('363', '2', '82');
INSERT INTO `ea_system_auth_node` VALUES ('364', '2', '83');
INSERT INTO `ea_system_auth_node` VALUES ('365', '2', '84');
INSERT INTO `ea_system_auth_node` VALUES ('366', '2', '85');
INSERT INTO `ea_system_auth_node` VALUES ('367', '2', '86');
INSERT INTO `ea_system_auth_node` VALUES ('368', '2', '87');
INSERT INTO `ea_system_auth_node` VALUES ('369', '2', '88');
INSERT INTO `ea_system_auth_node` VALUES ('370', '2', '89');
INSERT INTO `ea_system_auth_node` VALUES ('371', '2', '90');
INSERT INTO `ea_system_auth_node` VALUES ('372', '2', '91');
INSERT INTO `ea_system_auth_node` VALUES ('373', '2', '92');
INSERT INTO `ea_system_auth_node` VALUES ('374', '2', '93');
INSERT INTO `ea_system_auth_node` VALUES ('375', '2', '94');
INSERT INTO `ea_system_auth_node` VALUES ('376', '2', '95');
INSERT INTO `ea_system_auth_node` VALUES ('377', '2', '96');
INSERT INTO `ea_system_auth_node` VALUES ('378', '2', '97');
INSERT INTO `ea_system_auth_node` VALUES ('379', '2', '98');
INSERT INTO `ea_system_auth_node` VALUES ('380', '2', '99');
INSERT INTO `ea_system_auth_node` VALUES ('381', '2', '100');
INSERT INTO `ea_system_auth_node` VALUES ('382', '2', '101');
INSERT INTO `ea_system_auth_node` VALUES ('383', '2', '102');
INSERT INTO `ea_system_auth_node` VALUES ('384', '2', '180');
INSERT INTO `ea_system_auth_node` VALUES ('385', '2', '181');
INSERT INTO `ea_system_auth_node` VALUES ('386', '2', '182');
INSERT INTO `ea_system_auth_node` VALUES ('387', '2', '183');
INSERT INTO `ea_system_auth_node` VALUES ('388', '2', '184');
INSERT INTO `ea_system_auth_node` VALUES ('389', '2', '185');
INSERT INTO `ea_system_auth_node` VALUES ('390', '2', '186');
INSERT INTO `ea_system_auth_node` VALUES ('391', '2', '103');
INSERT INTO `ea_system_auth_node` VALUES ('392', '2', '104');
INSERT INTO `ea_system_auth_node` VALUES ('393', '2', '105');
INSERT INTO `ea_system_auth_node` VALUES ('394', '2', '106');
INSERT INTO `ea_system_auth_node` VALUES ('395', '2', '107');
INSERT INTO `ea_system_auth_node` VALUES ('396', '2', '108');
INSERT INTO `ea_system_auth_node` VALUES ('397', '2', '109');
INSERT INTO `ea_system_auth_node` VALUES ('398', '2', '172');
INSERT INTO `ea_system_auth_node` VALUES ('399', '2', '110');
INSERT INTO `ea_system_auth_node` VALUES ('400', '2', '111');
INSERT INTO `ea_system_auth_node` VALUES ('401', '2', '112');
INSERT INTO `ea_system_auth_node` VALUES ('402', '2', '113');
INSERT INTO `ea_system_auth_node` VALUES ('403', '2', '114');
INSERT INTO `ea_system_auth_node` VALUES ('404', '2', '115');
INSERT INTO `ea_system_auth_node` VALUES ('405', '2', '116');
INSERT INTO `ea_system_auth_node` VALUES ('406', '2', '143');
INSERT INTO `ea_system_auth_node` VALUES ('407', '2', '117');
INSERT INTO `ea_system_auth_node` VALUES ('408', '2', '118');
INSERT INTO `ea_system_auth_node` VALUES ('409', '2', '119');
INSERT INTO `ea_system_auth_node` VALUES ('410', '2', '120');
INSERT INTO `ea_system_auth_node` VALUES ('411', '2', '121');
INSERT INTO `ea_system_auth_node` VALUES ('412', '2', '122');
INSERT INTO `ea_system_auth_node` VALUES ('413', '2', '123');
INSERT INTO `ea_system_auth_node` VALUES ('414', '2', '188');
INSERT INTO `ea_system_auth_node` VALUES ('415', '2', '189');
INSERT INTO `ea_system_auth_node` VALUES ('416', '2', '124');
INSERT INTO `ea_system_auth_node` VALUES ('417', '2', '125');
INSERT INTO `ea_system_auth_node` VALUES ('418', '2', '126');
INSERT INTO `ea_system_auth_node` VALUES ('419', '2', '127');
INSERT INTO `ea_system_auth_node` VALUES ('420', '2', '128');
INSERT INTO `ea_system_auth_node` VALUES ('421', '2', '129');
INSERT INTO `ea_system_auth_node` VALUES ('422', '2', '130');
INSERT INTO `ea_system_auth_node` VALUES ('423', '2', '190');
INSERT INTO `ea_system_auth_node` VALUES ('424', '2', '131');
INSERT INTO `ea_system_auth_node` VALUES ('425', '2', '132');
INSERT INTO `ea_system_auth_node` VALUES ('426', '2', '133');
INSERT INTO `ea_system_auth_node` VALUES ('427', '2', '134');
INSERT INTO `ea_system_auth_node` VALUES ('428', '2', '135');
INSERT INTO `ea_system_auth_node` VALUES ('429', '2', '136');
INSERT INTO `ea_system_auth_node` VALUES ('430', '2', '137');
INSERT INTO `ea_system_auth_node` VALUES ('431', '2', '138');
INSERT INTO `ea_system_auth_node` VALUES ('432', '2', '139');
INSERT INTO `ea_system_auth_node` VALUES ('433', '2', '140');
INSERT INTO `ea_system_auth_node` VALUES ('434', '2', '141');
INSERT INTO `ea_system_auth_node` VALUES ('435', '2', '142');
INSERT INTO `ea_system_auth_node` VALUES ('436', '2', '144');
INSERT INTO `ea_system_auth_node` VALUES ('437', '2', '145');
INSERT INTO `ea_system_auth_node` VALUES ('438', '2', '146');
INSERT INTO `ea_system_auth_node` VALUES ('439', '2', '147');
INSERT INTO `ea_system_auth_node` VALUES ('440', '2', '148');
INSERT INTO `ea_system_auth_node` VALUES ('441', '2', '149');
INSERT INTO `ea_system_auth_node` VALUES ('442', '2', '150');
INSERT INTO `ea_system_auth_node` VALUES ('443', '2', '151');
INSERT INTO `ea_system_auth_node` VALUES ('444', '2', '152');
INSERT INTO `ea_system_auth_node` VALUES ('445', '2', '153');
INSERT INTO `ea_system_auth_node` VALUES ('446', '2', '154');
INSERT INTO `ea_system_auth_node` VALUES ('447', '2', '155');
INSERT INTO `ea_system_auth_node` VALUES ('448', '2', '156');
INSERT INTO `ea_system_auth_node` VALUES ('449', '2', '157');
INSERT INTO `ea_system_auth_node` VALUES ('450', '2', '158');
INSERT INTO `ea_system_auth_node` VALUES ('451', '2', '159');
INSERT INTO `ea_system_auth_node` VALUES ('452', '2', '160');
INSERT INTO `ea_system_auth_node` VALUES ('453', '2', '161');
INSERT INTO `ea_system_auth_node` VALUES ('454', '2', '162');
INSERT INTO `ea_system_auth_node` VALUES ('455', '2', '163');
INSERT INTO `ea_system_auth_node` VALUES ('456', '2', '187');
INSERT INTO `ea_system_auth_node` VALUES ('457', '2', '173');
INSERT INTO `ea_system_auth_node` VALUES ('458', '2', '174');
INSERT INTO `ea_system_auth_node` VALUES ('459', '2', '175');
INSERT INTO `ea_system_auth_node` VALUES ('460', '2', '176');
INSERT INTO `ea_system_auth_node` VALUES ('461', '2', '177');
INSERT INTO `ea_system_auth_node` VALUES ('462', '2', '178');
INSERT INTO `ea_system_auth_node` VALUES ('463', '2', '179');
INSERT INTO `ea_system_auth_node` VALUES ('472', '2', '191');
INSERT INTO `ea_system_auth_node` VALUES ('473', '2', '192');
INSERT INTO `ea_system_auth_node` VALUES ('474', '2', '193');
INSERT INTO `ea_system_auth_node` VALUES ('475', '2', '194');
INSERT INTO `ea_system_auth_node` VALUES ('476', '2', '195');
INSERT INTO `ea_system_auth_node` VALUES ('477', '2', '196');
INSERT INTO `ea_system_auth_node` VALUES ('478', '2', '197');
INSERT INTO `ea_system_auth_node` VALUES ('479', '2', '198');
INSERT INTO `ea_system_auth_node` VALUES ('480', '2', '199');
INSERT INTO `ea_system_auth_node` VALUES ('481', '2', '200');
INSERT INTO `ea_system_auth_node` VALUES ('482', '2', '201');
INSERT INTO `ea_system_auth_node` VALUES ('483', '2', '202');
INSERT INTO `ea_system_auth_node` VALUES ('484', '2', '203');
INSERT INTO `ea_system_auth_node` VALUES ('485', '2', '204');
INSERT INTO `ea_system_auth_node` VALUES ('486', '2', '205');
INSERT INTO `ea_system_auth_node` VALUES ('487', '2', '206');
INSERT INTO `ea_system_auth_node` VALUES ('488', '2', '207');
INSERT INTO `ea_system_auth_node` VALUES ('489', '2', '208');
INSERT INTO `ea_system_auth_node` VALUES ('490', '2', '209');
INSERT INTO `ea_system_auth_node` VALUES ('491', '2', '210');
INSERT INTO `ea_system_auth_node` VALUES ('492', '2', '211');
INSERT INTO `ea_system_auth_node` VALUES ('493', '2', '212');
INSERT INTO `ea_system_auth_node` VALUES ('494', '2', '213');
INSERT INTO `ea_system_auth_node` VALUES ('495', '2', '214');
INSERT INTO `ea_system_auth_node` VALUES ('496', '2', '215');
INSERT INTO `ea_system_auth_node` VALUES ('497', '2', '216');
INSERT INTO `ea_system_auth_node` VALUES ('498', '2', '217');
INSERT INTO `ea_system_auth_node` VALUES ('499', '2', '218');
INSERT INTO `ea_system_auth_node` VALUES ('500', '2', '219');
INSERT INTO `ea_system_auth_node` VALUES ('501', '2', '220');
INSERT INTO `ea_system_auth_node` VALUES ('502', '2', '221');
INSERT INTO `ea_system_auth_node` VALUES ('503', '2', '222');
INSERT INTO `ea_system_auth_node` VALUES ('504', '2', '223');

-- ----------------------------
-- Table structure for ea_system_menu
-- ----------------------------
DROP TABLE IF EXISTS `ea_system_menu`;
CREATE TABLE `ea_system_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `icon` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单图标',
  `href` varchar(100) NOT NULL DEFAULT '' COMMENT '链接',
  `params` varchar(500) DEFAULT '' COMMENT '链接参数',
  `target` varchar(20) NOT NULL DEFAULT '_self' COMMENT '链接打开方式',
  `sort` int(11) DEFAULT '0' COMMENT '菜单排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0:禁用,1:启用)',
  `remark` varchar(255) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `title` (`title`) USING BTREE,
  KEY `href` (`href`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=293 DEFAULT CHARSET=utf8 COMMENT='系统菜单表';

-- ----------------------------
-- Records of ea_system_menu
-- ----------------------------
INSERT INTO `ea_system_menu` VALUES ('227', '99999999', '后台首页', 'fa fa-home', 'index/welcome', '', '_self', '0', '1', null, null, '1573120497', null);
INSERT INTO `ea_system_menu` VALUES ('228', '0', '功能菜单', 'fa fa-cog', '', '', '_self', '0', '1', '', null, '1641958467', null);
INSERT INTO `ea_system_menu` VALUES ('234', '254', '菜单管理', 'fa fa-tree', 'system.menu/index', '', '_self', '10', '1', '', null, '1643095151', null);
INSERT INTO `ea_system_menu` VALUES ('244', '254', '管理员管理', 'fa fa-user', 'system.admin/index', '', '_self', '12', '1', '', '1573185011', '1641958505', null);
INSERT INTO `ea_system_menu` VALUES ('245', '254', '角色管理', 'fa fa-bitbucket-square', 'system.auth/index', '', '_self', '11', '1', '', '1573435877', '1641958516', null);
INSERT INTO `ea_system_menu` VALUES ('246', '254', '节点管理', 'fa fa-list', 'system.node/index', '', '_self', '9', '0', '', '1573435919', '1643095151', null);
INSERT INTO `ea_system_menu` VALUES ('247', '254', '配置管理', 'fa fa-asterisk', 'system.config/index', '', '_self', '8', '0', '', '1573457448', '1643095146', null);
INSERT INTO `ea_system_menu` VALUES ('248', '228', '上传管理', 'fa fa-arrow-up', 'system.uploadfile/index', '', '_self', '0', '0', '', '1573542953', '1641958584', null);
INSERT INTO `ea_system_menu` VALUES ('249', '0', '商城管理', 'fa fa-list', '', '', '_self', '0', '1', '', '1589439884', '1641958443', '1641958443');
INSERT INTO `ea_system_menu` VALUES ('250', '249', '商品分类', 'fa fa-calendar-check-o', 'mall.cate/index', '', '_self', '0', '1', '', '1589439910', '1589439966', null);
INSERT INTO `ea_system_menu` VALUES ('251', '249', '商品管理', 'fa fa-list', 'mall.goods/index', '', '_self', '0', '1', '', '1589439931', '1641958436', '1641958436');
INSERT INTO `ea_system_menu` VALUES ('252', '228', '快捷入口', 'fa fa-list', 'system.quick/index', '', '_self', '0', '0', '', '1589623683', '1641958581', null);
INSERT INTO `ea_system_menu` VALUES ('253', '254', '日志管理', 'fa fa-connectdevelop', 'system.log/index', '', '_self', '0', '1', '', '1589623684', '1641958593', null);
INSERT INTO `ea_system_menu` VALUES ('254', '228', '系统管理', 'fa fa-list', '', '', '_self', '0', '0', '', '1641958481', '1653030678', null);
INSERT INTO `ea_system_menu` VALUES ('255', '228', '参数配置', 'fa fa-list', 'app_config/index', '', '_self', '0', '1', '', '1641960166', '1653030443', null);
INSERT INTO `ea_system_menu` VALUES ('256', '228', '会员管理', 'fa fa-address-book-o', 'users/index', '', '_self', '100', '1', '', '1641976407', '1641978534', null);
INSERT INTO `ea_system_menu` VALUES ('257', '228', '卡密', 'fa fa-list', '', '', '_self', '90', '1', '', '1641978505', '1642045984', null);
INSERT INTO `ea_system_menu` VALUES ('258', '257', '卡密列表', 'fa fa-list', 'active.idents/index', '', '_self', '0', '1', '', '1642045971', '1642045971', null);
INSERT INTO `ea_system_menu` VALUES ('259', '257', '设置', 'fa fa-list', 'active.ident_config/index', '', '_self', '0', '1', '', '1642046182', '1642046182', null);
INSERT INTO `ea_system_menu` VALUES ('260', '228', '订单', 'fa fa-list', 'orders/index', '', '_self', '95', '1', '', '1642058801', '1642058824', null);
INSERT INTO `ea_system_menu` VALUES ('261', '228', '首页', 'fa fa-home', 'index/welcome', '', '_self', '200', '1', '', '1642059509', '1642059550', null);
INSERT INTO `ea_system_menu` VALUES ('262', '228', '分销', 'fa fa-list', '', '', '_self', '80', '1', '', '1642405109', '1642405119', null);
INSERT INTO `ea_system_menu` VALUES ('263', '262', '设置', 'fa fa-list', 'retail/config', '', '_self', '100', '1', '', '1642405200', '1642405216', null);
INSERT INTO `ea_system_menu` VALUES ('264', '262', '分销订单', 'fa fa-list', 'retail/index', '', '_self', '300', '1', '', '1642405235', '1642405248', null);
INSERT INTO `ea_system_menu` VALUES ('266', '265', '发布', 'fa fa-list', 'push_config/push', '', '_self', '0', '1', '', '1643011750', '1643014754', null);
INSERT INTO `ea_system_menu` VALUES ('267', '265', '发布设置', 'fa fa-list', 'push_config/index', '', '_self', '0', '0', '', '1643011770', '1643095140', null);
INSERT INTO `ea_system_menu` VALUES ('268', '228', '云更新', 'fa fa-list', '', '', '_self', '0', '1', '', '1643249113', '1643249113', null);
INSERT INTO `ea_system_menu` VALUES ('269', '268', '系统授权', 'fa fa-list', 'index/shouquan', '', '_self', '100', '1', '', '1643249128', '1643249138', null);
INSERT INTO `ea_system_menu` VALUES ('270', '268', '数据备份', 'fa fa-list', 'index/beifen', '', '_self', '0', '0', '', '1643249172', '1643267093', null);
INSERT INTO `ea_system_menu` VALUES ('271', '268', '版本更新', 'fa fa-list', 'index/cloudupdate', '', '_self', '50', '1', '', '1643249219', '1643249311', null);
INSERT INTO `ea_system_menu` VALUES ('272', '228', '微信商户进件', 'fa fa-list', '', '', '_self', '60', '1', '', '1644981414', '1644981414', null);
INSERT INTO `ea_system_menu` VALUES ('273', '272', '套餐', 'fa fa-list', 'incoming.parts/index', '', '_self', '0', '1', '', '1644981474', '1644981474', null);
INSERT INTO `ea_system_menu` VALUES ('274', '272', '订单', 'fa fa-list', 'in_coming_order/index', '', '_self', '100', '1', '', '1644981507', '1644981507', null);
INSERT INTO `ea_system_menu` VALUES ('275', '272', '设置', 'fa fa-list', 'incoming.parts/config', '', '_self', '0', '1', '', '1644981558', '1644981764', null);
INSERT INTO `ea_system_menu` VALUES ('276', '272', '分销订单', 'fa fa-list', 'in_coming_retail_order/index', '', '_self', '50', '1', '', '1644981587', '1644981587', null);
INSERT INTO `ea_system_menu` VALUES ('278', '228', 'Banner管理', 'fa fa-list', 'banner/index', '', '_self', '90', '1', '', '1652864260', '1652864260', null);
INSERT INTO `ea_system_menu` VALUES ('279', '228', '文章', 'fa fa-list', 'article/index', '', '_self', '2', '1', '', '1657978866', '1657978866', null);
INSERT INTO `ea_system_menu` VALUES ('280', '228', '上传小程序', 'fa fa-list', 'upcode/index', '', '_self', '1', '1', '', '1657978866', '1657978866', null);
INSERT INTO `ea_system_menu` VALUES ('281', '272', '进件', 'fa fa-list', 'admin_incoming_parts/index', '', '_self', '0', '0', '', '1644981507', '1644981507', null);
INSERT INTO `ea_system_menu` VALUES ('282', '228', '第三方平台设置', 'fa fa-list', 'xcx.app_config/index', '', '_self', '0', '1', '', '1641630950', '1641631053', null);
INSERT INTO `ea_system_menu` VALUES ('284', '228', '创建代理', 'fa fa-list', 'proxy/index', '', '_self', '0', '1', '', '1641633738', '1641633738', null);
INSERT INTO `ea_system_menu` VALUES ('287', '228', '支付宝商户进件', 'fa fa-list', '', '', '_self', '61', '1', '', '1669698837', '1669698837', null);
INSERT INTO `ea_system_menu` VALUES ('288', '287', '订单', 'fa fa-list', 'alipay.ali_orders/index', '', '_self', '0', '1', '', '1669700027', '1669700027', null);
INSERT INTO `ea_system_menu` VALUES ('289', '287', '分销订单', 'fa fa-list', 'alipay.ali_orders_dis/index', '', '_self', '0', '1', '', '1669701371', '1669701382', null);
INSERT INTO `ea_system_menu` VALUES ('290', '287', '套餐', 'fa fa-list', 'alipay.aliIncoming_parts/index', '', '_self', '0', '1', '', '1669701546', '1669718109', null);
INSERT INTO `ea_system_menu` VALUES ('291', '287', '设置', 'fa fa-list', 'alipay.ali_orders/setConfig', '', '_self', '0', '1', '', '1669701657', '1669701657', null);
INSERT INTO `ea_system_menu` VALUES ('292', '287', '进件', 'fa fa-list', 'alipay.ali_orders/incomingParts', '', '_self', '0', '0', '', '1669701674', '1669701674', null);

-- ----------------------------
-- Table structure for ea_system_node
-- ----------------------------
DROP TABLE IF EXISTS `ea_system_node`;
CREATE TABLE `ea_system_node` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `node` varchar(100) DEFAULT NULL COMMENT '节点代码',
  `title` varchar(500) DEFAULT NULL COMMENT '节点标题',
  `type` tinyint(1) DEFAULT '3' COMMENT '节点类型（1：控制器，2：节点）',
  `is_auth` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启动RBAC权限控制',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `node` (`node`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8 COMMENT='系统节点表';

-- ----------------------------
-- Records of ea_system_node
-- ----------------------------
INSERT INTO `ea_system_node` VALUES ('1', 'system.admin', '管理员管理', '1', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('2', 'system.admin/index', '列表', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('3', 'system.admin/add', '添加', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('4', 'system.admin/edit', '编辑', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('5', 'system.admin/password', '编辑', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('6', 'system.admin/delete', '删除', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('7', 'system.admin/modify', '属性修改', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('8', 'system.admin/export', '导出', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('9', 'system.auth', '角色权限管理', '1', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('10', 'system.auth/authorize', '授权', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('11', 'system.auth/saveAuthorize', '授权保存', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('12', 'system.auth/index', '列表', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('13', 'system.auth/add', '添加', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('14', 'system.auth/edit', '编辑', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('15', 'system.auth/delete', '删除', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('16', 'system.auth/export', '导出', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('17', 'system.auth/modify', '属性修改', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('18', 'system.config', '系统配置管理', '1', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('19', 'system.config/index', '列表', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('20', 'system.config/save', '保存', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('21', 'system.menu', '菜单管理', '1', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('22', 'system.menu/index', '列表', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('23', 'system.menu/add', '添加', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('24', 'system.menu/edit', '编辑', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('25', 'system.menu/delete', '删除', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('26', 'system.menu/modify', '属性修改', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('27', 'system.menu/getMenuTips', '添加菜单提示', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('28', 'system.menu/export', '导出', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('29', 'system.node', '系统节点管理', '1', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('30', 'system.node/index', '列表', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('31', 'system.node/refreshNode', '系统节点更新', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('32', 'system.node/clearNode', '清除失效节点', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('33', 'system.node/add', '添加', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('34', 'system.node/edit', '编辑', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('35', 'system.node/delete', '删除', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('36', 'system.node/export', '导出', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('37', 'system.node/modify', '属性修改', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('38', 'system.uploadfile', '上传文件管理', '1', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('39', 'system.uploadfile/index', '列表', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('40', 'system.uploadfile/add', '添加', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('41', 'system.uploadfile/edit', '编辑', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('42', 'system.uploadfile/delete', '删除', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('43', 'system.uploadfile/export', '导出', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('44', 'system.uploadfile/modify', '属性修改', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('45', 'mall.cate', '商品分类管理', '1', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('46', 'mall.cate/index', '列表', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('47', 'mall.cate/add', '添加', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('48', 'mall.cate/edit', '编辑', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('49', 'mall.cate/delete', '删除', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('50', 'mall.cate/export', '导出', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('51', 'mall.cate/modify', '属性修改', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('52', 'mall.goods', '商城商品管理', '1', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('53', 'mall.goods/index', '列表', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('54', 'mall.goods/stock', '入库', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('55', 'mall.goods/add', '添加', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('56', 'mall.goods/edit', '编辑', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('57', 'mall.goods/delete', '删除', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('58', 'mall.goods/export', '导出', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('59', 'mall.goods/modify', '属性修改', '2', '1', '1589580432', '1589580432');
INSERT INTO `ea_system_node` VALUES ('60', 'system.quick', '快捷入口管理', '1', '1', '1589623188', '1589623188');
INSERT INTO `ea_system_node` VALUES ('61', 'system.quick/index', '列表', '2', '1', '1589623188', '1589623188');
INSERT INTO `ea_system_node` VALUES ('62', 'system.quick/add', '添加', '2', '1', '1589623188', '1589623188');
INSERT INTO `ea_system_node` VALUES ('63', 'system.quick/edit', '编辑', '2', '1', '1589623188', '1589623188');
INSERT INTO `ea_system_node` VALUES ('64', 'system.quick/delete', '删除', '2', '1', '1589623188', '1589623188');
INSERT INTO `ea_system_node` VALUES ('65', 'system.quick/export', '导出', '2', '1', '1589623188', '1589623188');
INSERT INTO `ea_system_node` VALUES ('66', 'system.quick/modify', '属性修改', '2', '1', '1589623188', '1589623188');
INSERT INTO `ea_system_node` VALUES ('67', 'system.log', '操作日志管理', '1', '1', '1589623188', '1589623188');
INSERT INTO `ea_system_node` VALUES ('68', 'system.log/index', '列表', '2', '1', '1589623188', '1589623188');
INSERT INTO `ea_system_node` VALUES ('69', 'app_config', '参数配置', '1', '1', '1641960131', '1641960131');
INSERT INTO `ea_system_node` VALUES ('70', 'app_config/index', '列表', '2', '1', '1641960131', '1641960131');
INSERT INTO `ea_system_node` VALUES ('71', 'app_config/save', '保存', '2', '1', '1641960131', '1641960131');
INSERT INTO `ea_system_node` VALUES ('72', 'users', '会员管理', '1', '1', '1641976358', '1641976358');
INSERT INTO `ea_system_node` VALUES ('73', 'users/index', '列表', '2', '1', '1641976358', '1641976358');
INSERT INTO `ea_system_node` VALUES ('74', 'users/add', '添加', '2', '1', '1641976358', '1641976358');
INSERT INTO `ea_system_node` VALUES ('75', 'users/edit', '编辑', '2', '1', '1641976358', '1641976358');
INSERT INTO `ea_system_node` VALUES ('76', 'users/delete', '删除', '2', '1', '1641976358', '1641976358');
INSERT INTO `ea_system_node` VALUES ('77', 'users/export', '导出', '2', '1', '1641976358', '1641976358');
INSERT INTO `ea_system_node` VALUES ('78', 'users/modify', '属性修改', '2', '1', '1641976358', '1641976358');
INSERT INTO `ea_system_node` VALUES ('79', 'active.idents', '卡密', '1', '1', '1641978561', '1641978561');
INSERT INTO `ea_system_node` VALUES ('80', 'active.idents/index', '卡密-列表', '2', '1', '1641978561', '1641978561');
INSERT INTO `ea_system_node` VALUES ('81', 'active.idents/add', '添加', '2', '1', '1641978561', '1641978561');
INSERT INTO `ea_system_node` VALUES ('82', 'active.idents/edit', '编辑', '2', '1', '1641978561', '1641978561');
INSERT INTO `ea_system_node` VALUES ('83', 'active.idents/delete', '删除', '2', '1', '1641978561', '1641978561');
INSERT INTO `ea_system_node` VALUES ('84', 'active.idents/export', '导出', '2', '1', '1641978561', '1641978561');
INSERT INTO `ea_system_node` VALUES ('85', 'active.idents/modify', '属性修改', '2', '1', '1641978561', '1641978561');
INSERT INTO `ea_system_node` VALUES ('86', 'active.ident_code', 'active_ident_code', '1', '1', '1642039472', '1642039472');
INSERT INTO `ea_system_node` VALUES ('87', 'active.ident_code/index', '列表', '2', '1', '1642039472', '1642039472');
INSERT INTO `ea_system_node` VALUES ('88', 'active.ident_code/add', '添加', '2', '1', '1642039472', '1642039472');
INSERT INTO `ea_system_node` VALUES ('89', 'active.ident_code/edit', '编辑', '2', '1', '1642039472', '1642039472');
INSERT INTO `ea_system_node` VALUES ('90', 'active.ident_code/delete', '删除', '2', '1', '1642039472', '1642039472');
INSERT INTO `ea_system_node` VALUES ('91', 'active.ident_code/export', '导出', '2', '1', '1642039472', '1642039472');
INSERT INTO `ea_system_node` VALUES ('92', 'active.ident_code/modify', '属性修改', '2', '1', '1642039472', '1642039472');
INSERT INTO `ea_system_node` VALUES ('93', 'active.ident_config', '卡密设置', '1', '1', '1642046130', '1642046130');
INSERT INTO `ea_system_node` VALUES ('94', 'active.ident_config/index', '列表', '2', '1', '1642046130', '1642046130');
INSERT INTO `ea_system_node` VALUES ('95', 'active.ident_config/save', '保存', '2', '1', '1642046130', '1642046130');
INSERT INTO `ea_system_node` VALUES ('96', 'orders', '订单', '1', '1', '1642058733', '1642058733');
INSERT INTO `ea_system_node` VALUES ('97', 'orders/index', '列表', '2', '1', '1642058733', '1642058733');
INSERT INTO `ea_system_node` VALUES ('98', 'orders/add', '添加', '2', '1', '1642058733', '1642058733');
INSERT INTO `ea_system_node` VALUES ('99', 'orders/edit', '编辑', '2', '1', '1642058733', '1642058733');
INSERT INTO `ea_system_node` VALUES ('100', 'orders/delete', '删除', '2', '1', '1642058733', '1642058733');
INSERT INTO `ea_system_node` VALUES ('101', 'orders/export', '导出', '2', '1', '1642058733', '1642058733');
INSERT INTO `ea_system_node` VALUES ('102', 'orders/modify', '属性修改', '2', '1', '1642058733', '1642058733');
INSERT INTO `ea_system_node` VALUES ('103', 'retail', '分销', '1', '1', '1642405078', '1642405078');
INSERT INTO `ea_system_node` VALUES ('104', 'retail/index', '分销订单列表', '2', '1', '1642405078', '1642405078');
INSERT INTO `ea_system_node` VALUES ('105', 'retail/add', '添加', '2', '1', '1642405078', '1642405078');
INSERT INTO `ea_system_node` VALUES ('106', 'retail/edit', '编辑', '2', '1', '1642405078', '1642405078');
INSERT INTO `ea_system_node` VALUES ('107', 'retail/delete', '删除', '2', '1', '1642405078', '1642405078');
INSERT INTO `ea_system_node` VALUES ('108', 'retail/export', '导出', '2', '1', '1642405078', '1642405078');
INSERT INTO `ea_system_node` VALUES ('109', 'retail/modify', '属性修改', '2', '1', '1642405078', '1642405078');
INSERT INTO `ea_system_node` VALUES ('110', 'incoming.parts', '商户进件-套餐', '1', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('111', 'incoming.parts/index', '套餐列表', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('112', 'incoming.parts/add', '添加', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('113', 'incoming.parts/edit', '编辑', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('114', 'incoming.parts/delete', '删除', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('115', 'incoming.parts/export', '导出', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('116', 'incoming.parts/modify', '属性修改', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('117', 'in_coming_order', '商户进件-订单', '1', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('118', 'in_coming_order/index', '列表', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('119', 'in_coming_order/delete', '删除', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('120', 'in_coming_order/add', '添加', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('121', 'in_coming_order/edit', '编辑', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('122', 'in_coming_order/export', '导出', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('123', 'in_coming_order/modify', '属性修改', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('124', 'in_coming_retail_order', '商户进件-分销订单', '1', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('125', 'in_coming_retail_order/index', '列表', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('126', 'in_coming_retail_order/add', '添加', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('127', 'in_coming_retail_order/edit', '编辑', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('128', 'in_coming_retail_order/delete', '删除', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('129', 'in_coming_retail_order/export', '导出', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('130', 'in_coming_retail_order/modify', '属性修改', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('131', 'push_config', '系统配置管理', '1', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('132', 'push_config/index', '列表', '2', '1', '1644981358', '1644981358');
INSERT INTO `ea_system_node` VALUES ('133', 'xcx.app_config', '第三方平台设置', '1', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('134', 'xcx.app_config/index', '列表', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('135', 'xcx.app_config/save', '保存', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('136', 'xcx.app_project', '小程序列表', '1', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('137', 'xcx.app_project/index', '列表', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('138', 'xcx.app_project/add', '添加', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('139', 'xcx.app_project/edit', '编辑', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('140', 'xcx.app_project/delete', '删除', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('141', 'xcx.app_project/export', '导出', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('142', 'xcx.app_project/modify', '属性修改', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('143', 'incoming.parts/config', '设置', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('144', 'admin_incoming_parts', '商户进件-进件', '1', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('145', 'admin_incoming_parts/add', '添加', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('146', 'admin_incoming_parts/edit', '编辑', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('147', 'admin_incoming_parts/delete', '删除', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('148', 'admin_incoming_parts/export', '导出', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('149', 'admin_incoming_parts/modify', '属性修改', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('150', 'article', '帮助中心', '1', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('151', 'article/index', '列表', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('152', 'article/add', '添加', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('153', 'article/edit', '编辑', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('154', 'article/delete', '删除', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('155', 'article/export', '导出', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('156', 'article/modify', '属性修改', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('157', 'banner', 'banner管理', '1', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('158', 'banner/index', 'banner列表', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('159', 'banner/kefu_page', '添加', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('160', 'banner/edit', '编辑', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('161', 'banner/delete', '删除', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('162', 'banner/export', '导出', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('163', 'banner/modify', '属性修改', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('164', 'proxy', '代理管理', '1', '0', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('165', 'proxy/index', '列表', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('166', 'proxy/add', '添加', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('167', 'proxy/edit', '编辑', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('168', 'proxy/password', '编辑', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('169', 'proxy/delete', '删除', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('170', 'proxy/modify', '属性修改', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('171', 'proxy/export', '导出', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('172', 'retail/config', '分销设置', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('173', 'upcode', '上传小程序', '1', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('174', 'upcode/index', '列表', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('175', 'upcode/add', '添加', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('176', 'upcode/edit', '编辑', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('177', 'upcode/delete', '删除', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('178', 'upcode/export', '导出', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('179', 'upcode/modify', '属性修改', '2', '1', '1667392437', '1667392437');
INSERT INTO `ea_system_node` VALUES ('180', 'orders/detail', '详情', '2', '1', '1667479298', '1667479298');
INSERT INTO `ea_system_node` VALUES ('181', 'orders/re_create', '再来一单', '2', '1', '1667479298', '1667479298');
INSERT INTO `ea_system_node` VALUES ('182', 'orders/select', 'select', '2', '1', '1667479298', '1667479298');
INSERT INTO `ea_system_node` VALUES ('183', 'orders/ok', '确认付款', '2', '1', '1667479298', '1667479298');
INSERT INTO `ea_system_node` VALUES ('184', 'orders/verifybetaweapp', '确认转正', '2', '1', '1667479298', '1667479298');
INSERT INTO `ea_system_node` VALUES ('185', 'orders/get_business_info', 'get_business_info', '2', '1', '1667479298', '1667479298');
INSERT INTO `ea_system_node` VALUES ('186', 'orders/setbetaweappnickname', '小程序更名', '2', '1', '1667479298', '1667479298');
INSERT INTO `ea_system_node` VALUES ('187', 'banner/add', '添加', '2', '1', '1667479995', '1667479995');
INSERT INTO `ea_system_node` VALUES ('188', 'in_coming_order/detail', '详情', '2', '1', '1667479995', '1667479995');
INSERT INTO `ea_system_node` VALUES ('189', 'in_coming_order/ok', '提交申请', '2', '1', '1667479995', '1667479995');
INSERT INTO `ea_system_node` VALUES ('190', 'in_coming_retail_order/payment', '分销订单-payment', '2', '1', '1667479995', '1667479995');
INSERT INTO `ea_system_node` VALUES ('191', 'upcode/xcxUpcode', '小程序上传代码执行node ', '2', '1', '1667747764', '1667747764');
INSERT INTO `ea_system_node` VALUES ('192', 'alipay.ali_orders', 'ali_orders', '1', '1', '1669698882', '1669698882');
INSERT INTO `ea_system_node` VALUES ('193', 'alipay.ali_orders/index', '列表', '2', '1', '1669698882', '1669698882');
INSERT INTO `ea_system_node` VALUES ('194', 'alipay.ali_orders/add', '添加', '2', '1', '1669698882', '1669698882');
INSERT INTO `ea_system_node` VALUES ('195', 'alipay.ali_orders/edit', '编辑', '2', '1', '1669698882', '1669698882');
INSERT INTO `ea_system_node` VALUES ('196', 'alipay.ali_orders/delete', '删除', '2', '1', '1669698882', '1669698882');
INSERT INTO `ea_system_node` VALUES ('197', 'alipay.ali_orders/export', '导出', '2', '1', '1669698882', '1669698882');
INSERT INTO `ea_system_node` VALUES ('198', 'alipay.ali_orders/modify', '属性修改', '2', '1', '1669698882', '1669698882');
INSERT INTO `ea_system_node` VALUES ('199', 'alipay.ali_orders/distributionList', '分销订单-列表', '2', '1', '1669701155', '1669701155');
INSERT INTO `ea_system_node` VALUES ('200', 'alipay.ali_orders/packageList', '套餐', '2', '1', '1669701506', '1669701506');
INSERT INTO `ea_system_node` VALUES ('201', 'alipay.ali_orders/setConfig', '设置', '2', '1', '1669701636', '1669701636');
INSERT INTO `ea_system_node` VALUES ('202', 'alipay.ali_orders/incomingParts', '进件', '2', '1', '1669701636', '1669701636');
INSERT INTO `ea_system_node` VALUES ('203', 'alipay.ali_incoming_parts', 'ali_incoming_parts', '1', '1', '1669718043', '1669718043');
INSERT INTO `ea_system_node` VALUES ('204', 'alipay.ali_incoming_parts/index', '列表', '2', '1', '1669718043', '1669718043');
INSERT INTO `ea_system_node` VALUES ('205', 'alipay.ali_incoming_parts/add', '添加', '2', '1', '1669718043', '1669718043');
INSERT INTO `ea_system_node` VALUES ('206', 'alipay.ali_incoming_parts/edit', '编辑', '2', '1', '1669718043', '1669718043');
INSERT INTO `ea_system_node` VALUES ('207', 'alipay.ali_incoming_parts/delete', '删除', '2', '1', '1669718043', '1669718043');
INSERT INTO `ea_system_node` VALUES ('208', 'alipay.ali_incoming_parts/export', '导出', '2', '1', '1669718043', '1669718043');
INSERT INTO `ea_system_node` VALUES ('209', 'alipay.ali_incoming_parts/modify', '属性修改', '2', '1', '1669718043', '1669718043');
INSERT INTO `ea_system_node` VALUES ('210', 'alipay.ali_orders/alipaymccC2', '二级类目', '2', '0', '1669730878', '1669730878');
INSERT INTO `ea_system_node` VALUES ('211', 'alipay.ali_orders/adminIncoming', '后台进件', '2', '0', '1669735155', '1669735155');
INSERT INTO `ea_system_node` VALUES ('212', 'alipay.ali_orders/detail', '详情', '2', '0', '1669768907', '1669768907');
INSERT INTO `ea_system_node` VALUES ('213', 'alipay.ali_orders/ok', '提交申请', '2', '0', '1669782165', '1669782165');
INSERT INTO `ea_system_node` VALUES ('214', 'alipay.ali_orders/checkRes', '查询结果', '2', '0', '1669814315', '1669814315');
INSERT INTO `ea_system_node` VALUES ('215', 'alipay.ali_orders/getRegion', '地区', '2', '0', '1669814315', '1669814315');
INSERT INTO `ea_system_node` VALUES ('216', 'alipay.ali_orders_dis', '支付宝进件-分销订单', '1', '0', '1670231280', '1670231280');
INSERT INTO `ea_system_node` VALUES ('217', 'alipay.ali_orders_dis/index', '列表', '2', '1', '1670231280', '1670231280');
INSERT INTO `ea_system_node` VALUES ('218', 'alipay.ali_orders_dis/payment', '分销订单-payment', '2', '1', '1670231280', '1670231280');
INSERT INTO `ea_system_node` VALUES ('219', 'alipay.ali_orders_dis/add', '添加', '2', '1', '1670231280', '1670231280');
INSERT INTO `ea_system_node` VALUES ('220', 'alipay.ali_orders_dis/edit', '编辑', '2', '1', '1670231280', '1670231280');
INSERT INTO `ea_system_node` VALUES ('221', 'alipay.ali_orders_dis/delete', '删除', '2', '1', '1670231280', '1670231280');
INSERT INTO `ea_system_node` VALUES ('222', 'alipay.ali_orders_dis/export', '导出', '2', '1', '1670231280', '1670231280');
INSERT INTO `ea_system_node` VALUES ('223', 'alipay.ali_orders_dis/modify', '属性修改', '2', '1', '1670231280', '1670231280');
INSERT INTO `ea_system_node` VALUES ('224', 'in_coming_order/selectService', '提交申请', '2', '0', '1670941493', '1670941493');