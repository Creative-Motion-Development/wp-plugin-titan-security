<?php

class wfLiveTrafficQueryFilter {

	private $param;
	private $operator;
	private $value;

	protected $validOperators = array(
		'=',
		'!=',
		'contains',
		'match',
		'hregexp',
		'hnotregexp',
	);

	/**
	 * @var wfLiveTrafficQuery
	 */
	private $query;

	/**
	 * wfLiveTrafficQueryFilter constructor.
	 *
	 * @param wfLiveTrafficQuery $query
	 * @param string $param
	 * @param string $operator
	 * @param string $value
	 */
	public function __construct($query, $param, $operator, $value)
	{
		$this->query = $query;
		$this->param = $param;
		$this->operator = $operator;
		$this->value = $value;
	}

	/**
	 * @return string|void
	 */
	public function toSQL()
	{
		$sql = '';
		if( $this->validate() ) {
			/** @var wpdb $wpdb */ global $wpdb;
			$operator = $this->getOperator();
			$param = $this->getQuery()->getColumnFromParam($this->getParam());
			if( !$param ) {
				return $sql;
			}
			$value = $this->getValue();
			switch( $operator ) {
				case 'contains':
					$like = addcslashes($value, '_%\\');
					$sql = $wpdb->prepare("$param LIKE %s", "%$like%");
					break;

				case 'match':
					$sql = $wpdb->prepare("$param LIKE %s", $value);
					break;

				case 'hregexp':
					$sql = $wpdb->prepare("HEX($param) REGEXP %s", $value);
					break;

				case 'hnotregexp':
					$sql = $wpdb->prepare("HEX($param) NOT REGEXP %s", $value);
					break;

				default:
					$sql = $wpdb->prepare("$param $operator %s", $value);
					break;
			}
		}

		return $sql;
	}

	/**
	 * @return bool
	 */
	public function validate()
	{
		$valid = $this->isValidParam($this->getParam()) && $this->isValidOperator($this->getOperator());
		if( defined('WP_DEBUG') && WP_DEBUG ) {
			if( !$valid ) {
				throw new wfLiveTrafficQueryException("Invalid param/operator [{$this->getParam()}]/[{$this->getOperator()}] passed to " . get_class($this));
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
	 * @param string $operator
	 * @return bool
	 */
	public function isValidOperator($operator)
	{
		return in_array($operator, $this->validOperators);
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

	/**
	 * @return mixed
	 */
	public function getOperator()
	{
		return $this->operator;
	}

	/**
	 * @param mixed $operator
	 */
	public function setOperator($operator)
	{
		$this->operator = $operator;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
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
}