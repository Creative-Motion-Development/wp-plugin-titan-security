<?php

class wfLiveTrafficQueryGroupBy {

	private $param;

	/**
	 * @var wfLiveTrafficQuery
	 */
	private $query;

	/**
	 * wfLiveTrafficQueryGroupBy constructor.
	 *
	 * @param wfLiveTrafficQuery $query
	 * @param string $param
	 */
	public function __construct($query, $param)
	{
		$this->query = $query;
		$this->param = $param;
	}

	/**
	 * @return bool
	 * @throws wfLiveTrafficQueryException
	 */
	public function validate()
	{
		$valid = $this->isValidParam($this->getParam());
		if( defined('WP_DEBUG') && WP_DEBUG ) {
			if( !$valid ) {
				throw new wfLiveTrafficQueryException("Invalid param [{$this->getParam()}] passed to " . get_class($this));
			}

			return true;
		}

		return $valid;
	}

	/**
	 * @param string $param
	 * @return bool
	 */
	public function isValidParam($param)
	{
		return $this->getQuery() && $this->getQuery()->isValidParam($param);
	}

	/**
	 * @return wfLiveTrafficQuery
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * @param wfLiveTrafficQuery $query
	 */
	public function setQuery($query)
	{
		$this->query = $query;
	}

	/**
	 * @return mixed
	 */
	public function getParam()
	{
		return $this->param;
	}

	/**
	 * @param mixed $param
	 */
	public function setParam($param)
	{
		$this->param = $param;
	}

}