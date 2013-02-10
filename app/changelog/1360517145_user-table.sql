CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `fullname` varchar(128) NOT NULL,
  `password` varchar(64) NOT NULL,
  `role` varchar(64) NOT NULL,
  `ins_dt` datetime NOT NULL,
  `del_flag` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;