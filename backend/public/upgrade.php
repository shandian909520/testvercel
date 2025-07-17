<?php
// +----------------------------------------------------------------------
// | 小程序注册服务商助手 
// +----------------------------------------------------------------------
// | 版权所有  晓江云计算有限公司 
// +----------------------------------------------------------------------
// | 官方网站：https://www.xiaojiangy.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | 联系方式: 13163426222 <sc@xiaojiany.com>
// +----------------------------------------------------------------------
// | 系统已获取您的域名和ip信息，本系统未经授权严禁使用，盗版必究。
// +----------------------------------------------------------------------
// | 公司决定2023年1月份对所有盗版用户进行维权诉讼，避免更大损失，请尽早转正。
// +----------------------------------------------------------------------

use app\admin\model\SystemNode;
use app\admin\service\NodeService;
use app\admin\service\TriggerService;
use think\facade\Db;


Db::query("CREATE TABLE IF NOT EXISTS `ea_ali_incoming_parts` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '序号',
    `rate` decimal(4,2) DEFAULT NULL COMMENT '费率(%)',
    `cost` decimal(10,2) DEFAULT NULL COMMENT '费用',
    `retail_num` decimal(10,2) DEFAULT NULL COMMENT '分销金额',
    `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
    `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
    `pf_id` int(11) NOT NULL COMMENT '平台id',
    PRIMARY KEY (`id`) USING BTREE
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='套餐';");
Db::query("update ea_ali_incoming_parts set pf_id = 1 where pf_id is null;");

Db::query("CREATE TABLE IF NOT EXISTS `ea_ali_orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` varchar(255) NOT NULL COMMENT '订单号',
    `user_id` int(11) NOT NULL COMMENT '用户id',
    `pay_type` int(2) NOT NULL DEFAULT '0' COMMENT '支付类型 {select} (1:平台,2:微信,3:卡密)',
    `num` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
    `status` varchar(255) NOT NULL DEFAULT '1' COMMENT '订单状态 {select} (1:未支付,2:进行中,3:已完成)',
    `msg` varchar(255) DEFAULT NULL COMMENT '网关返回码描述',
    `sub_code` varchar(255) DEFAULT NULL COMMENT '业务返回码',
    `sub_msg` varchar(255) DEFAULT NULL COMMENT '业务返回码描述',
    `batch_status` varchar(255) DEFAULT NULL COMMENT '事务状态',
    `code` varchar(255) DEFAULT NULL COMMENT '网关返回码',
    `create_time` datetime DEFAULT NULL COMMENT '创建时间',
    `retail_status` varchar(255) DEFAULT '0' COMMENT '分销-打款状态 {select} {0:无,1:成功,2:失败}',
    `retail_num` decimal(11,2) DEFAULT '0.00' COMMENT '分销-佣金',
    `retail_time` datetime DEFAULT NULL COMMENT '分销-打款时间',
    `update_time` datetime DEFAULT NULL,
    `applyment_id` varchar(255) DEFAULT NULL COMMENT '微信支付申请单号',
    `order_no` varchar(255) DEFAULT NULL COMMENT '签约单号',
    `confirm_url` varchar(255) DEFAULT NULL COMMENT '商户确认签约链接',
    `merchant_pid` varchar(255) DEFAULT NULL COMMENT '商户pid',
    `order_status` varchar(255) DEFAULT NULL COMMENT '申请单状态',
    `pf_id` int(10) DEFAULT NULL COMMENT '平台id',
    PRIMARY KEY (`id`) USING BTREE
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='阿里签约订单';");

Db::query("CREATE TABLE IF NOT EXISTS `ea_ali_orders_info` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
    `ali_orders_id` varchar(255) NOT NULL COMMENT '订单号',
    `batch_no` varchar(255) DEFAULT NULL COMMENT '事务编号',
    `mcc_code` varchar(255) DEFAULT NULL COMMENT '类目编码',
    `special_license_pic` varchar(255) DEFAULT NULL COMMENT '企业特殊资质图片',
    `rate` decimal(4,2) DEFAULT NULL COMMENT '服务费率',
    `sign_and_auth` int(1) DEFAULT NULL COMMENT '签约且授权标识',
    `business_license_no` varchar(255) DEFAULT NULL COMMENT '营业执照号码',
    `business_license_pic` varchar(255) DEFAULT NULL COMMENT '营业执照图片',
    `business_license_auth_pic` varchar(255) DEFAULT NULL COMMENT '营业执照授权函图片',
    `long_term` int(1) DEFAULT NULL COMMENT '营业期限是否长期有效',
    `date_limitation` varchar(255) DEFAULT NULL COMMENT '营业期限',
    `shop_scene_pic` varchar(255) DEFAULT NULL COMMENT '店铺内景图片',
    `shop_sign_board_pic` varchar(255) DEFAULT NULL COMMENT '店铺门头照图片',
    `shop_name` varchar(255) DEFAULT NULL COMMENT '店铺名称',
    `shop_address` varchar(255) DEFAULT NULL COMMENT '店铺地址',
    `business_license_mobile` varchar(255) DEFAULT NULL COMMENT '营业执照法人手机号码',
    `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
    `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
    `account` varchar(255) DEFAULT NULL COMMENT '商户账号',
    `contact_name` varchar(255) DEFAULT NULL COMMENT '联系人名称',
    `contact_mobile` varchar(255) DEFAULT NULL COMMENT '联系人手机号',
    `contact_email` varchar(255) DEFAULT NULL COMMENT '联系人邮箱',
    `province_code` varchar(255) DEFAULT NULL COMMENT '省份编码',
    `city_code` varchar(255) DEFAULT NULL COMMENT '城市编码',
    `district_code` varchar(255) DEFAULT NULL COMMENT '区县编码',
    `detail_address` varchar(255) DEFAULT NULL COMMENT '详细地址',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='阿里签约订单详情';");



$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_active_idents' AND COLUMN_NAME='pf_id'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_active_idents` ADD `pf_id` int(11) NULL DEFAULT NULL COMMENT '平台id' AFTER `create_time`");
}
Db::query("update ea_active_idents set pf_id = 1 where pf_id is null;");
Db::query("CREATE TABLE IF NOT EXISTS `ea_app_project` (
    `id` int(11) NOT NULL,
    `app_id` varchar(255) DEFAULT NULL COMMENT '小程序id',
    `app_secret` varchar(255) DEFAULT NULL COMMENT '小程序秘钥',
    `status` int(2) DEFAULT NULL COMMENT '状态',
    `create_user` varchar(255) DEFAULT NULL COMMENT '创建者',
    `create_id` int(11) DEFAULT NULL COMMENT '创建者id',
    `create_time` datetime DEFAULT NULL COMMENT '创建时间',
    `update_user` varchar(255) DEFAULT NULL COMMENT '修改人',
    `update_id` varchar(255) DEFAULT NULL COMMENT '修改人id',
    `update_time` datetime DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");

$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_article' AND COLUMN_NAME='pf_id'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_article` ADD `pf_id` int(11) NULL DEFAULT NULL COMMENT '平台id' AFTER `status`");
}
Db::query("update ea_article set pf_id = 1 where pf_id is null;");
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_banner' AND COLUMN_NAME='gh_no'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_banner` ADD `gh_no`  varchar(255) NULL DEFAULT NULL COMMENT '原始id' AFTER `sort`");
}
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_banner' AND COLUMN_NAME='pf_id'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_banner` ADD `pf_id` int(11) NULL DEFAULT NULL COMMENT '平台id' AFTER `gh_no`");
}
Db::query("update ea_banner set pf_id = 1 where pf_id is null;");
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_incoming_parts' AND COLUMN_NAME='pf_id'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_incoming_parts` ADD `pf_id` int(11) NULL DEFAULT NULL COMMENT '平台id' AFTER `update_time`");
}
Db::query("update ea_incoming_parts set pf_id = 1 where pf_id is null;");
//-----------


$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_system_admin' AND COLUMN_NAME='independent'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_system_admin` ADD `independent` int(10) NULL DEFAULT NULL COMMENT '三方开关' AFTER `sort`");
}
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_system_admin' AND COLUMN_NAME='uname'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_system_admin` ADD `uname` int(10) NULL DEFAULT NULL COMMENT '名称' AFTER `head_img`");
}
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_system_admin' AND COLUMN_NAME='end_time'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_system_admin` ADD `end_time` int(11) NULL DEFAULT NULL COMMENT '到期时间' AFTER `delete_time`");
}

Db::query("UPDATE ea_system_config
SET `group` = 'wx_config'
WHERE
`name` LIKE '%wx_mp_app_id%'
OR `name` LIKE '%wx_mp_app_secret%'
OR `name` LIKE '%wx_mp_mchid%'
OR `name` LIKE '%wx_mp_mchid_serial_no%'
OR `name` LIKE '%wx_mp_mchid_secret%'
OR `name` LIKE '%wx_mp_key_pem%'
OR `name` LIKE '%wx_mp_cert_pem%'
OR `name` LIKE '%kefu_type_mp%'
OR `name` LIKE '%wx_mp_pay_type%'");

Db::query("UPDATE ea_system_config
SET `group` = 'push_config'
WHERE `name` LIKE '%push_app_upsecret%'");


//user 表 update_time 类型 更新 int
Db::query("update ea_users set update_time = null;");
Db::query("ALTER TABLE `ea_users` MODIFY COLUMN `update_time`  int(11) NULL DEFAULT NULL AFTER `pid`;");

Db::query("UPDATE ea_system_config
SET `group` = 'base_config'
WHERE `name` LIKE '%useragreement%'");

$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_users' AND COLUMN_NAME='pf_id'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_users` ADD `pf_id` int(11) NULL DEFAULT NULL COMMENT '平台id' AFTER `type`");
}
Db::query("update ea_users set pf_id = 1 where pf_id is null or pf_id = '';");
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_orders' AND COLUMN_NAME='pf_id'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_orders` ADD `pf_id` int(10) NULL DEFAULT NULL COMMENT '平台id' AFTER `faststatus`");
}
Db::query("update ea_orders set pf_id = 1 where pf_id is null or pf_id = '';");
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_orders' AND COLUMN_NAME='pfconfig_id'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_orders` ADD `pfconfig_id` int(10) NULL DEFAULT NULL COMMENT 'pfconfig_id' AFTER `pf_id`");
}
//新增扩展字段兼容平台订单
Db::query("update ea_orders set pfconfig_id = pf_id where pfconfig_id = 0 or pfconfig_id is NULL");

$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='license'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `license` VARCHAR(255) NULL DEFAULT NULL COMMENT '组织机构代码证或营业执照' AFTER `openid`");
}
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='license_link'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `license_link` VARCHAR(255) NULL DEFAULT NULL COMMENT '' AFTER `license`");
}


$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='naming_other_stuff_1'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `naming_other_stuff_1` VARCHAR(255) NULL DEFAULT NULL COMMENT '其他证明材料1' AFTER `license_link`");
}
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='naming_other_stuff_1_link'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `naming_other_stuff_1_link` VARCHAR(255) NULL DEFAULT NULL COMMENT '' AFTER `naming_other_stuff_1`");
}

$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='naming_other_stuff_2'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `naming_other_stuff_2` VARCHAR(255) NULL DEFAULT NULL COMMENT '其他证明材料2' AFTER `naming_other_stuff_1_link`");
}
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='naming_other_stuff_2_link'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `naming_other_stuff_2_link` VARCHAR(255) NULL DEFAULT NULL COMMENT '' AFTER `naming_other_stuff_2`");
}

$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='naming_other_stuff_3'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `naming_other_stuff_3` VARCHAR(255) NULL DEFAULT NULL COMMENT '其他证明材料3' AFTER `naming_other_stuff_2_link`");
}
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='naming_other_stuff_3_link'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `naming_other_stuff_3_link` VARCHAR(255) NULL DEFAULT NULL COMMENT '' AFTER `naming_other_stuff_3`");
}
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='naming_other_stuff_4'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `naming_other_stuff_4` VARCHAR(255) NULL DEFAULT NULL COMMENT '其他证明材料4' AFTER `naming_other_stuff_3_link`");
}
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='naming_other_stuff_4_link'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `naming_other_stuff_4_link` VARCHAR(255) NULL DEFAULT NULL COMMENT '' AFTER `naming_other_stuff_4`");
}
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='naming_other_stuff_5'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `naming_other_stuff_5` VARCHAR(255) NULL DEFAULT NULL COMMENT '其他证明材料5' AFTER `naming_other_stuff_4_link`");
}
$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='naming_other_stuff_5_link'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `naming_other_stuff_5_link` VARCHAR(255) NULL DEFAULT NULL COMMENT '' AFTER `naming_other_stuff_5`");
}

$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_orders' AND COLUMN_NAME='gh_id'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_orders` ADD `gh_id` VARCHAR(255) NULL DEFAULT NULL COMMENT '原始 ID' AFTER `faststatus`");
}

$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_order_info' AND COLUMN_NAME='update_time'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_order_info` ADD `update_time` datetime NULL AFTER `naming_other_stuff_5_link`");
}

$column = Db::query("SELECT `column_name` FROM information_schema.COLUMNS WHERE TABLE_NAME='ea_orders' AND COLUMN_NAME='remarks'");
if (empty($column)) {
    Db::query("ALTER TABLE `ea_orders` ADD `remarks`  varchar(255) NULL DEFAULT NULL COMMENT '文字备注' AFTER `gh_id`");
}

$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='pro_status1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'pro_status';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='pro_mchid1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'pro_mchid';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='pro_mchid_secret1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'pro_mchid_secret';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='pro_mchid_serial_no1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'pro_mchid_serial_no';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='pro_key_pem1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'pro_key_pem';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='pro_cert_pem1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'pro_cert_pem';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='pro_app_id1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'pro_app_id';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='pro_app_secret1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'pro_app_secret';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='pro_activities_id1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'pro_activities_id';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='app_id1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'app_id';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='app_secret1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'app_secret';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='token1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'token';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='key1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'key';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='authorize_url1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'authorize_url';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='events_url1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'events_url';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='login_domain1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'login_domain';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='service_type1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'service_type';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='kefu_type1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'kefu_type';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='share_images1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'share_images';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='file1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'file';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='kefu_company_ids1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'kefu_company_ids';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='kefu_company_id1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'kefu_company_id';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='kefu_url1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'kefu_url';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='register_status1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'register_status';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='register_num1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'register_num';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='xcx_pian_status1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'xcx_pian_status';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_pay_status1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_pay_status';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='pian_status1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'pian_status';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='service_phone1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'service_phone';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='share_title1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'share_title';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='share_msg1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'share_msg';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='share_image1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'share_image';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='useragreement1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'useragreement';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='active_ident_status'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'active_ident_status';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_app_id1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_app_id';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_app_secret1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_app_secret';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_mchid1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_mchid';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_mchid_serial_no1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_mchid_serial_no';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_mchid_secret1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_mchid_secret';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_key_pem1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_key_pem';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_cert_pem1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_cert_pem';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_pay_type1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_pay_type';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='kefu_type_mp1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'kefu_type_mp';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_mp_app_id1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_mp_app_id';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_mp_app_secret1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_mp_app_secret';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_mp_mchid1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_mp_mchid';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_mp_mchid_serial_no1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_mp_mchid_serial_no';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_mp_mchid_secret1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_mp_mchid_secret';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_mp_key_pem1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_mp_key_pem';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_mp_cert_pem1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_mp_cert_pem';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='wx_mp_pay_type1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'wx_mp_pay_type';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='baidu_status1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'baidu_status';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='baidu_api_key1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'baidu_api_key';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='baidu_api_secret1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'baidu_api_secret';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='ad_popup1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'ad_popup';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='ad_open1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'ad_open';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='ad_banner1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'ad_banner';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='independenttxt1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'independenttxt';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='push_app_upsecret1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'push_app_upsecret';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='xcx_app_id1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'xcx_app_id';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='xcx_refresh_token1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'xcx_refresh_token';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='xcx_info1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'xcx_info';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='retail_num1'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'retail_num';");
}
$column = Db::query("SELECT `name` FROM ea_system_config WHERE `name`='retail_image'");
if (empty($column)) {
    Db::query("UPDATE `ea_system_config` SET `name` = concat(`NAME`,'1') WHERE `name` = 'retail_image';");
}