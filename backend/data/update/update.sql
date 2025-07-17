ALTER TABLE ea_users ADD `type` int(11) NOT NULL DEFAULT '1' COMMENT '类型  1小程序   2公众号';
INSERT INTO `ea_system_config` VALUES (143, 'wx_mp_app_id', 'wx_config', '', '微信公众号id', 0, NULL, NULL);
INSERT INTO `ea_system_config` VALUES (144, 'wx_mp_app_secret', 'wx_config', '', '微信公众号sercet', 0, NULL, NULL);
INSERT INTO `ea_system_config` VALUES (145, 'wx_mp_mchid', 'wx_config', '', '微信公众号商户号', 0, NULL, NULL);
INSERT INTO `ea_system_config` VALUES (146, 'wx_mp_mchid_secret', 'wx_config', '', '微信公众号商户密钥', 0, NULL, NULL);
INSERT INTO `ea_system_config` VALUES (147, 'wx_mp_key_pem', 'wx_config', '', '微信公众号 key', 0, NULL, NULL);
INSERT INTO `ea_system_config` VALUES (148, 'wx_mp_cert_pem', 'wx_config', '', '微信公众号 cert', 0, NULL, NULL);
INSERT INTO `ea_system_config` VALUES (149, 'wx_mp_mchid_serial_no', 'wx_config', '', '微信公众号 序列号', 0, NULL, NULL);
INSERT INTO `ea_system_config` VALUES (150, 'wx_mp_pay_type', 'wx_config', '2', '微信公众号号支付', 0, NULL, NULL);