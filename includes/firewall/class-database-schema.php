<?php

namespace WBCR\Titan\Database;

class Schema {

	private static $tables = array(
		"blocks7" => "(
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(10) unsigned NOT NULL DEFAULT '0',
  `IP` binary(16) NOT NULL DEFAULT '\\\0\\\0\\\0\\\0\\\0\\\0\\\0\\\0\\\0\\\0\\\0\\\0\\\0\\\0\\\0\\\0',
  `blockedTime` bigint(20) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `lastAttempt` int(10) unsigned DEFAULT '0',
  `blockedHits` int(10) unsigned DEFAULT '0',
  `expiration` bigint(20) unsigned NOT NULL DEFAULT '0',
  `parameters` text,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `IP` (`IP`),
  KEY `expiration` (`expiration`)
) DEFAULT CHARSET=utf8",
		"crawlers" => "(
  `IP` binary(16) NOT NULL DEFAULT '\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0',
  `patternSig` binary(16) NOT NULL,
  `status` char(8) NOT NULL,
  `lastUpdate` int(10) unsigned NOT NULL,
  `PTR` varchar(255) DEFAULT '',
  PRIMARY KEY (`IP`,`patternSig`)
) DEFAULT CHARSET=utf8",
		"hits" => "(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attackLogTime` double(17,6) unsigned NOT NULL,
  `ctime` double(17,6) unsigned NOT NULL,
  `IP` binary(16) DEFAULT NULL,
  `jsRun` tinyint(4) DEFAULT '0',
  `statusCode` int(11) NOT NULL DEFAULT '200',
  `isGoogle` tinyint(4) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `newVisit` tinyint(3) unsigned NOT NULL,
  `URL` text,
  `referer` text,
  `UA` text,
  `action` varchar(64) NOT NULL DEFAULT '',
  `actionDescription` text,
  `actionData` text,
  PRIMARY KEY (`id`),
  KEY `k1` (`ctime`),
  KEY `k2` (`IP`,`ctime`),
  KEY `attackLogTime` (`attackLogTime`)
) DEFAULT CHARSET=utf8",
		"traffic_rates" => "(
  `eMin` int(10) unsigned NOT NULL,
  `IP` binary(16) NOT NULL DEFAULT '\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0',
  `hitType` enum('hit','404') NOT NULL DEFAULT 'hit',
  `hits` int(10) unsigned NOT NULL,
  PRIMARY KEY (`eMin`,`IP`,`hitType`)
) DEFAULT CHARSET=utf8",
		"locs" => "(
  `IP` binary(16) NOT NULL DEFAULT '\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0',
  `ctime` int(10) unsigned NOT NULL,
  `failed` tinyint(3) unsigned NOT NULL,
  `city` varchar(255) DEFAULT '',
  `region` varchar(255) DEFAULT '',
  `countryName` varchar(255) DEFAULT '',
  `countryCode` char(2) DEFAULT '',
  `lat` float(10,7) DEFAULT '0.0000000',
  `lon` float(10,7) DEFAULT '0.0000000',
  PRIMARY KEY (`IP`)
) DEFAULT CHARSET=utf8",
		"logins" => "(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hitID` int(11) DEFAULT NULL,
  `ctime` double(17,6) unsigned NOT NULL,
  `fail` tinyint(3) unsigned NOT NULL,
  `action` varchar(40) NOT NULL,
  `username` varchar(255) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `IP` binary(16) DEFAULT NULL,
  `UA` text,
  PRIMARY KEY (`id`),
  KEY `k1` (`IP`,`fail`),
  KEY `hitID` (`hitID`)
) DEFAULT CHARSET=utf8",
		'blocked_ip_log' => "(
  `IP` binary(16) NOT NULL DEFAULT '\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0',
  `countryCode` varchar(2) NOT NULL,
  `blockCount` int(10) unsigned NOT NULL DEFAULT '0',
  `unixday` int(10) unsigned NOT NULL,
  `blockType` varchar(50) NOT NULL DEFAULT 'generic',
  PRIMARY KEY (`IP`,`unixday`,`blockType`)
) DEFAULT CHARSET=utf8",
		'live_traffic_human' => "(
  `IP` binary(16) NOT NULL DEFAULT '\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0',
  `identifier` binary(32) NOT NULL DEFAULT '\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0',
  `expiration` int(10) unsigned NOT NULL,
  PRIMARY KEY (`IP`,`identifier`),
  KEY `expiration` (`expiration`)
) DEFAULT CHARSET=utf8;",
	);
	private $db = false;

	public function __construct($dbhost = false, $dbuser = false, $dbpassword = false, $dbname = false)
	{
		global $wpdb;
		$this->db = $wpdb;
	}

	public function get_table_name($table)
	{
		return $this->db->prefix . \WBCR\Titan\Plugin::app()->getPrefix() . $table;
	}

	public function dropAll()
	{
		foreach(self::$tables as $table => $def) {
			$table = $this->get_table_name($table);

			$this->db->query("DROP TABLE IF EXISTS {$table}");
		}
	}

	public function create_all()
	{
		foreach(self::$tables as $table => $def) {
			$this->db->query("CREATE TABLE IF NOT EXISTS " . $this->get_table_name($table) . " " . $def);
		}
	}

	public function create($table)
	{
		$this->db->query("CREATE TABLE IF NOT EXISTS " . $this->get_table_name($table) . " " . self::$tables[$table]);
	}

	public function drop($table)
	{
		$table = $this->get_table_name($table);
		$this->db->query("DROP TABLE IF EXISTS {$table}");
	}

	public static function table_list()
	{
		return array_keys(self::$tables);
	}
}
