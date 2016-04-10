/* session table */
CREATE TABLE `session` (
  `ses_key` char(32) NOT NULL default '',
  `ses_value` text,
  `sign` varchar(10) default '',
  `time` int(11) default '0',
  PRIMARY KEY  (`ses_key`),
  KEY `sign` (`sign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* static_file table */
CREATE TABLE `bet_static_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `v` varchar(255) NOT NULL DEFAULT '',
  `p` char(32) NOT NULL DEFAULT '',
  `u` varchar(255) NOT NULL DEFAULT '',
  `e` varchar(255) NOT NULL DEFAULT '',
  `t` int(11) unsigned DEFAULT '0',
  `tEx` varchar(255) DEFAULT '',
  `c` tinyint(2) unsigned DEFAULT '1',
  `isFull` tinyint(2) unsigned DEFAULT '0',
  `note` varchar(255) DEFAULT '',
  `addTime` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
p parent page
v class_method
u url
e unless
t time
tEx     detail of time ,for example 4*3600 with four hour.
c       isCache
isFull  if maximize match
note
*/

