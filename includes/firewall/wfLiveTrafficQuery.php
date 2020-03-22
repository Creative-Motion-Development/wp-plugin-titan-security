<?php

class wfLiveTrafficQuery {

	protected $validParams = array(
		'id' => 'h.id',
		'ctime' => 'h.ctime',
		'ip' => 'h.ip',
		'jsrun' => 'h.jsrun',
		'statuscode' => 'h.statuscode',
		'isgoogle' => 'h.isgoogle',
		'userid' => 'h.userid',
		'url' => 'h.url',
		'referer' => 'h.referer',
		'ua' => 'h.ua',
		'action' => 'h.action',
		'actiondescription' => 'h.actiondescription',
		'actiondata' => 'h.actiondata',

		// wfLogins
		'user_login' => 'u.user_login',
		'username' => 'l.username',
	);

	/** @var wfLiveTrafficQueryFilterCollection */
	private $filters = array();

	/** @var wfLiveTrafficQueryGroupBy */
	private $groupBy;
	/**
	 * @var float|null
	 */
	private $startDate;
	/**
	 * @var float|null
	 */
	private $endDate;
	/**
	 * @var int
	 */
	private $limit;
	/**
	 * @var int
	 */
	private $offset;

	private $tableName;

	/** @var wfLog */
	private $wfLog;

	/**
	 * wfLiveTrafficQuery constructor.
	 *
	 * @param wfLog $wfLog
	 * @param wfLiveTrafficQueryFilterCollection $filters
	 * @param wfLiveTrafficQueryGroupBy $groupBy
	 * @param float $startDate
	 * @param float $endDate
	 * @param int $limit
	 * @param int $offset
	 */
	public function __construct($wfLog, $filters = null, $groupBy = null, $startDate = null, $endDate = null, $limit = 20, $offset = 0)
	{
		$this->wfLog = $wfLog;
		$this->filters = $filters;
		$this->groupBy = $groupBy;
		$this->startDate = $startDate;
		$this->endDate = $endDate;
		$this->limit = $limit;
		$this->offset = $offset;
	}

	/**
	 * @return array|null|object
	 */
	public function execute()
	{
		global $wpdb;
		$delayedHumanBotFiltering = false;
		$humanOnly = false;
		$sql = $this->buildQuery($delayedHumanBotFiltering, $humanOnly);
		$results = $wpdb->get_results($sql, ARRAY_A);

		if( $delayedHumanBotFiltering ) {
			$browscap = wfBrowscap::shared();
			foreach($results as $index => $res) {
				if( $res['UA'] ) {
					$b = $browscap->getBrowser($res['UA']);
					$jsRun = wfUtils::truthyToBoolean($res['jsRun']);
					if( $b && $b['Parent'] != 'DefaultProperties' ) {
						$jsRun = wfUtils::truthyToBoolean($res['jsRun']);
						if( !wfConfig::liveTrafficEnabled() && !$jsRun ) {
							$jsRun = !(isset($b['Crawler']) && $b['Crawler']);
						}
					}

					if( !$humanOnly && $jsRun || $humanOnly && !$jsRun ) {
						unset($results[$index]);
					}
				}
			}
		}

		$this->getWFLog()->processGetHitsResults('', $results);

		$verifyCrawlers = false;
		if( $this->filters !== null && count($this->filters->getFilters()) > 0 ) {
			$filters = $this->filters->getFilters();
			foreach($filters as $f) {
				if( strtolower($f->getParam()) == "isgoogle" ) {
					$verifyCrawlers = true;
					break;
				}
			}
		}

		foreach($results as $key => &$row) {
			if( $row['isGoogle'] && $verifyCrawlers ) {
				if( !wfCrawl::isVerifiedGoogleCrawler($row['IP'], $row['UA']) ) {
					unset($results[$key]); //foreach copies $results and iterates on the copy, so it is safe to mutate $results within the loop
					continue;
				}
			}

			$row['actionData'] = (array)json_decode($row['actionData'], true);
		}

		return array_values($results);
	}

	/**
	 * @param mixed $delayedHumanBotFiltering Whether or not human/bot filtering should be applied in PHP rather than SQL.
	 * @param mixed $humanOnly When using delayed filtering, whether to show only humans or only bots.
	 *
	 * @return string
	 * @throws wfLiveTrafficQueryException
	 */
	public function buildQuery(&$delayedHumanBotFiltering = null, &$humanOnly)
	{
		global $wpdb;
		$filters = $this->getFilters();
		$groupBy = $this->getGroupBy();
		$startDate = $this->getStartDate();
		$endDate = $this->getEndDate();
		$limit = absint($this->getLimit());
		$offset = absint($this->getOffset());

		$wheres = array("h.action != 'logged:waf'", "h.action != 'scan:detectproxy'");
		if( $startDate ) {
			$wheres[] = $wpdb->prepare('h.ctime > %f', $startDate);
		}
		if( $endDate ) {
			$wheres[] = $wpdb->prepare('h.ctime < %f', $endDate);
		}

		if( $filters instanceof wfLiveTrafficQueryFilterCollection ) {
			if( !wfConfig::liveTrafficEnabled() ) {
				$individualFilters = $filters->getFilters();
				foreach($individualFilters as $index => $f) {
					if( $f->getParam() == 'jsRun' && $delayedHumanBotFiltering !== null && $humanOnly !== null ) {
						$humanOnly = wfUtils::truthyToBoolean($f->getValue());
						if( $f->getOperator() == '!=' ) {
							$humanOnly = !$humanOnly;
						}
						$delayedHumanBotFiltering = true;
						unset($individualFilters[$index]);
					}
				}
				$filters->setFilters($individualFilters);
			}

			$filtersSQL = $filters->toSQL();
			if( $filtersSQL ) {
				$wheres[] = $filtersSQL;
			}
		}

		$orderBy = 'ORDER BY h.ctime DESC';
		$select = ', l.username';
		$groupBySQL = '';
		if( $groupBy && $groupBy->validate() ) {
			$groupBySQL = "GROUP BY {$groupBy->getParam()}";
			$orderBy = 'ORDER BY hitCount DESC';
			$select .= ', COUNT(h.id) as hitCount, MAX(h.ctime) AS lastHit, u.user_login AS username';

			if( $groupBy->getParam() == 'user_login' ) {
				$wheres[] = 'user_login IS NOT NULL';
			} else if( $groupBy->getParam() == 'action' ) {
				$wheres[] = '(statusCode = 403 OR statusCode = 503)';
			}
		}

		$where = join(' AND ', $wheres);
		if( $where ) {
			$where = 'WHERE ' . $where;
		}
		if( !$limit || $limit > 1000 ) {
			$limit = 20;
		}
		$limitSQL = $wpdb->prepare('LIMIT %d, %d', $offset, $limit);

		$table_wfLogins = wfDB::networkTable('wfLogins');
		$sql = <<<SQL
SELECT h.*, u.display_name{$select} FROM {$this->getTableName()} h
LEFT JOIN {$wpdb->users} u on h.userID = u.ID
LEFT JOIN {$table_wfLogins} l on h.id = l.hitID
$where
$groupBySQL
$orderBy
$limitSQL
SQL;

		return $sql;
	}

	/**
	 * @param $param
	 * @return bool
	 */
	public function isValidParam($param)
	{
		return array_key_exists(strtolower($param), $this->validParams);
	}

	/**
	 * @param $getParam
	 * @return bool|string
	 */
	public function getColumnFromParam($getParam)
	{
		$getParam = strtolower($getParam);
		if( array_key_exists($getParam, $this->validParams) ) {
			return $this->validParams[$getParam];
		}

		return false;
	}

	/**
	 * @return wfLiveTrafficQueryFilterCollection
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 * @param wfLiveTrafficQueryFilterCollection $filters
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;
	}

	/**
	 * @return float|null
	 */
	public function getStartDate()
	{
		return $this->startDate;
	}

	/**
	 * @param float|null $startDate
	 */
	public function setStartDate($startDate)
	{
		$this->startDate = $startDate;
	}

	/**
	 * @return float|null
	 */
	public function getEndDate()
	{
		return $this->endDate;
	}

	/**
	 * @param float|null $endDate
	 */
	public function setEndDate($endDate)
	{
		$this->endDate = $endDate;
	}

	/**
	 * @return wfLiveTrafficQueryGroupBy
	 */
	public function getGroupBy()
	{
		return $this->groupBy;
	}

	/**
	 * @param wfLiveTrafficQueryGroupBy $groupBy
	 */
	public function setGroupBy($groupBy)
	{
		$this->groupBy = $groupBy;
	}

	/**
	 * @return int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @param int $limit
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
	}

	/**
	 * @return int
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * @param int $offset
	 */
	public function setOffset($offset)
	{
		$this->offset = $offset;
	}

	/**
	 * @return string
	 */
	public function getTableName()
	{
		if( $this->tableName === null ) {
			$this->tableName = wfDB::networkTable('wfHits');
		}

		return $this->tableName;
	}

	/**
	 * @param string $tableName
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;
	}

	/**
	 * @return wfLog
	 */
	public function getWFLog()
	{
		return $this->wfLog;
	}

	/**
	 * @param wfLog $wfLog
	 */
	public function setWFLog($wfLog)
	{
		$this->wfLog = $wfLog;
	}
}