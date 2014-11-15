
use simdp;

CREATE TABLE `simdp_task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` bigint(14) unsigned zerofill NOT NULL DEFAULT '00000000000000',
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '3' COMMENT '1-9',
  `freq` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_job_time` (`job_id`,`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

 CREATE TABLE `simdp_job` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `freq` int(10) unsigned NOT NULL DEFAULT '0',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '3' COMMENT '1-9',
  `callback` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `simdp_rely` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rely_job` int(10) unsigned NOT NULL DEFAULT '0',
  `freq` int(10) unsigned NOT NULL DEFAULT '0',
  `start` tinyint(4) NOT NULL DEFAULT '0',
  `long` smallint(5) unsigned NOT NULL DEFAULT '1',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_job_rely` (`job_id`,`rely_job`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

