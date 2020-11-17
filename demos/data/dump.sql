DROP TABLE IF EXISTS `login_user`;
CREATE TABLE `login_user` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `role_id` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;
INSERT INTO `login_user` VALUES
    (1,'Standard User','user','$2y$10$BwEhcP8f15yOexf077VTHOnySn/mit49ZhpfeBkORQhrsmHr4U6Qy',1),
    (2,'Administrator','admin','$2y$10$p34ciRcg9GZyxukkLIaEnenGBao79fTFa4tFSrl7FvqrxnmEGlD4O',2);

DROP TABLE IF EXISTS `login_role`;
CREATE TABLE `login_role` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;
INSERT INTO `login_role` VALUES
    (1,'User Role'),
    (2,'Admin Role');

DROP TABLE IF EXISTS `login_access_rule`;
CREATE TABLE `login_access_rule` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `role_id` int(11) NOT NULL,
    `model` text NOT NULL,
    `all_visible` tinyint(1) DEFAULT NULL,
    `visible_fields` text DEFAULT NULL,
    `all_editable` tinyint(1) DEFAULT NULL,
    `editable_fields` text DEFAULT NULL,
    `all_actions` tinyint(1) DEFAULT NULL,
    `actions` text DEFAULT NULL,
    `conditions` text DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;
INSERT INTO `login_access_rule` VALUES
    (1,2,'\\atk4\login\\Model\\User',1,null,1,null,1,null,null),
    (2,1,'\\atk4\login\\Model\\Role',1,null,0,null,1,null,null);
