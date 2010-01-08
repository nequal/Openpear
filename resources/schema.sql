-- phpMyAdmin SQL Dump
-- version 3.2.2
-- http://www.phpmyadmin.net
--
-- ホスト: localhost
-- 生成時間: 2010 年 1 月 09 日 01:14
-- サーバのバージョン: 5.4.3
-- PHP のバージョン: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- データベース: `openpear2`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_account_maintainer`
--

CREATE TABLE IF NOT EXISTS `openpear_account_maintainer` (
  `maintainer_id` decimal(24,2) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_changeset`
--

CREATE TABLE IF NOT EXISTS `openpear_changeset` (
  `revision` int(24) unsigned NOT NULL DEFAULT '0',
  `maintainer_id` decimal(24,2) DEFAULT NULL,
  `package_id` decimal(24,2) DEFAULT NULL,
  `changed` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_charge`
--

CREATE TABLE IF NOT EXISTS `openpear_charge` (
  `package_id` decimal(24,2) DEFAULT NULL,
  `maintainer_id` decimal(24,2) DEFAULT NULL,
  `role` longblob,
  KEY `package_id` (`package_id`),
  KEY `maintainer_id` (`maintainer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_favorite`
--

CREATE TABLE IF NOT EXISTS `openpear_favorite` (
  `package_id` decimal(24,2) DEFAULT NULL,
  `maintainer_id` decimal(24,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_maintainer`
--

CREATE TABLE IF NOT EXISTS `openpear_maintainer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longblob,
  `mail` varchar(255) DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `profile` longblob,
  `url` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `svn_password` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_message`
--

CREATE TABLE IF NOT EXISTS `openpear_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maintainer_to_id` decimal(24,2) DEFAULT NULL,
  `maintainer_from_id` decimal(24,2) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `description` longblob,
  `unread` tinyint(1) DEFAULT NULL,
  `type` longblob,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_newproject_queue`
--

CREATE TABLE IF NOT EXISTS `openpear_newproject_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` decimal(24,2) DEFAULT NULL,
  `maintainer_id` decimal(24,2) DEFAULT NULL,
  `mail_possible` varchar(255) DEFAULT NULL,
  `settings` varchar(255) DEFAULT NULL,
  `trial_count` decimal(24,2) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_openid_maintainer`
--

CREATE TABLE IF NOT EXISTS `openpear_openid_maintainer` (
  `maintainer_id` decimal(24,2) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_package`
--

CREATE TABLE IF NOT EXISTS `openpear_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `public_level` decimal(24,2) DEFAULT NULL,
  `external_repository` varchar(255) DEFAULT NULL,
  `external_repository_type` longblob,
  `favored_count` decimal(24,2) DEFAULT NULL,
  `recent_changeset` decimal(24,2) DEFAULT NULL,
  `released_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `latest_release_id` decimal(24,2) DEFAULT NULL,
  `author_id` decimal(24,2) DEFAULT NULL,
  `license` varchar(255) DEFAULT NULL,
  `license_uri` varchar(255) DEFAULT NULL,
  `notify` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_package_message`
--

CREATE TABLE IF NOT EXISTS `openpear_package_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` decimal(24,2) DEFAULT NULL,
  `description` longblob,
  `unread` tinyint(1) DEFAULT NULL,
  `type` longblob,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_package_tag`
--

CREATE TABLE IF NOT EXISTS `openpear_package_tag` (
  `package_id` decimal(24,2) DEFAULT NULL,
  `tag_id` decimal(24,2) DEFAULT NULL,
  `prime` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_release`
--

CREATE TABLE IF NOT EXISTS `openpear_release` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` decimal(24,2) DEFAULT NULL,
  `maintainer_id` decimal(24,2) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `version_stab` longblob,
  `notes` longblob,
  `settings` longblob,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_release_queue`
--

CREATE TABLE IF NOT EXISTS `openpear_release_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` decimal(24,2) DEFAULT NULL,
  `maintainer_id` decimal(24,2) DEFAULT NULL,
  `revision` decimal(24,2) DEFAULT NULL,
  `build_path` varchar(255) DEFAULT NULL,
  `build_conf` longblob,
  `description` longblob,
  `notes` longblob,
  `trial_count` decimal(24,2) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_tag`
--

CREATE TABLE IF NOT EXISTS `openpear_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `prime` tinyint(1) DEFAULT NULL,
  `package_count` decimal(24,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `openpear_timeline`
--

CREATE TABLE IF NOT EXISTS `openpear_timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longblob,
  `type` longblob,
  `package_id` decimal(24,2) DEFAULT NULL,
  `maintainer_id` decimal(24,2) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
