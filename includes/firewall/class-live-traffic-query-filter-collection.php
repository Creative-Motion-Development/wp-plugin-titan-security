<?php

class wfLiveTrafficQueryFilterCollection {

	private $filters = array();

	/**
	 * wfLiveTrafficQueryFilterCollection constructor.
	 *
	 * @param array $filters
	 */
	public function __construct($filters = array())
	{
		$this->filters = $filters;
	}

	public function toSQL()
	{
		$params = array();
		$sql = '';
		$filters = $this->getFilters();
		if( $filters ) {
			/** @var wfLiveTrafficQueryFilter $filter */
			foreach($filters as $filter) {
				$params[$filter->getParam()][] = $filter;
			}
		}

		foreach($params as $param => $filters) {
			// $sql .= '(';
			$filtersSQL = '';
			foreach($filters as $filter) {
				$filterSQL = $filter->toSQL();
				if( $filterSQL ) {
					$filtersSQL .= $filterSQL . ' OR ';
				}
			}
			if( $filtersSQL ) {
				$sql .= '(' . substr($filtersSQL, 0, -4) . ') AND ';
			}
		}
		if( $sql ) {
			$sql = substr($sql, 0, -5);
		}

		return $sql;
	}

	public function addFilter($filter)
	{
		$this->filters[] = $filter;
	}

	/**
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 * @param array $filters
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;
	}
}