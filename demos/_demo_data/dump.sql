DROP TABLE IF EXISTS `login_user`;
CREATE TABLE `login_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_role_idx` (`role_id`)
) ENGINE=InnoDB;

INSERT INTO `user` VALUES (1,'Standard User','user','user',1),(2,'Administrator','admin','admin',2);


DROP TABLE IF EXISTS `login_role`;
CREATE TABLE `login_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `role` VALUES (1,'User Role'),(2,'Admin Role');


DROP TABLE IF EXISTS `login_access_rule`;
CREATE TABLE `login_access_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `model` varchar(255) NOT NULL,
  `all_visible` int(1) DEFAULT 1,
  `visible_fields` varchar(255) DEFAULT NULL,
  `all_editable` int(1) DEFAULT 1,
  `editable_fields` varchar(255) DEFAULT NULL,
  `all_actions` int(1) DEFAULT 1,
  `actions` varchar(255) DEFAULT NULL,
  `conditions` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `login_access_rule` VALUES
    (1,2,'\\atk4\login\\Model\\User',1,null,1,null,1,null,null),
    (2,1,'\\atk4\login\\Model\\Role',1,null,0,null,1,null,null);
