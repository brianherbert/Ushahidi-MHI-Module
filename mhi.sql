-- Create syntax for TABLE 'mhi_category'
CREATE TABLE `mhi_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `category_title` varchar(100) CHARACTER SET utf8 NOT NULL,
  `category_active` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'mhi_log'
CREATE TABLE `mhi_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `notes` text CHARACTER SET utf8 NOT NULL,
  `ip` int(10) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'mhi_log_actions'
CREATE TABLE `mhi_log_actions` (
  `id` int(11) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'mhi_site'
CREATE TABLE `mhi_site` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `site_domain` varchar(32) NOT NULL,
  `custom_domain` varchar(100) NOT NULL DEFAULT '',
  `site_privacy` tinyint(4) NOT NULL DEFAULT '0',
  `site_active` tinyint(4) DEFAULT '1',
  `site_dateadd` datetime NOT NULL,
  `current_hits` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'mhi_site_category'
CREATE TABLE `mhi_site_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'mhi_site_database'
CREATE TABLE `mhi_site_database` (
  `mhi_id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) CHARACTER SET utf8 NOT NULL,
  `pass` varchar(50) CHARACTER SET utf8 NOT NULL,
  `host` varchar(100) CHARACTER SET utf8 NOT NULL,
  `port` smallint(6) NOT NULL,
  `database` varchar(100) CHARACTER SET utf8 NOT NULL,
  `version` int(11) NOT NULL DEFAULT '1',
  `fsmsclk` int(11) NOT NULL DEFAULT '0',
  `clickatell_api` varchar(100) CHARACTER SET utf8 NOT NULL,
  `clickatell_username` varchar(100) CHARACTER SET utf8 NOT NULL,
  `clickatell_password` varchar(100) CHARACTER SET utf8 NOT NULL,
  `frontlinesms_key` varchar(100) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`mhi_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='This table holds DB credentials for MHI instances';

-- Create syntax for TABLE 'mhi_stats_reports'
CREATE TABLE `mhi_stats_reports` (
  `year` int(11) unsigned NOT NULL,
  `month` int(11) unsigned NOT NULL,
  `reports` int(11) DEFAULT NULL,
  `crowdmaps` int(11) DEFAULT NULL,
  `uniques` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'mhi_tags'
CREATE TABLE `mhi_tags` (
  `riverid` varchar(128) NOT NULL,
  `mhi_id` int(11) NOT NULL,
  `tag` varchar(128) NOT NULL,
  PRIMARY KEY (`riverid`,`mhi_id`,`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'mhi_users'
CREATE TABLE `mhi_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `riverid` varchar(128) CHARACTER SET utf8 NOT NULL,
  `email` varchar(50) CHARACTER SET utf8 NOT NULL,
  `firstname` varchar(30) CHARACTER SET utf8 NOT NULL,
  `lastname` varchar(30) CHARACTER SET utf8 NOT NULL,
  `password` varchar(40) CHARACTER SET utf8 NOT NULL,
  `mailinglist` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;