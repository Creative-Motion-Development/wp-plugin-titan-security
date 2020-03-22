<?php

namespace WBCR\Titan\Firewall\Model;

abstract class Model {

	private $data;
	private $db;
	private $dirty = false;

	/**
	 * Column name of the primary key field.
	 *
	 * @return string
	 */
	abstract public function getIDColumn();

	/**
	 * Table name.
	 *
	 * @return mixed
	 */
	abstract public function getTable();

	/**
	 * Checks if this is a valid column in the table before setting data on the model.
	 *
	 * @param string $column
	 * @return boolean
	 */
	abstract public function hasColumn($column);

	/**
	 * wfModel constructor.
	 * @param array|int|string $data
	 */
	public function __construct($data = array())
	{
		if( is_array($data) || is_object($data) ) {
			$this->setData($data);
		} else if( is_numeric($data) ) {
			$this->fetchByID($data);
		}
	}

	public function fetchByID($id)
	{
		$id = absint($id);
		$data = $this->getDB()->get_row($this->getDB()->prepare('SELECT * FROM ' . $this->getTable() . ' WHERE ' . $this->getIDColumn() . ' = %d', $id));
		if( $data ) {
			$this->setData($data);

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function save()
	{
		if( !$this->dirty ) {
			return false;
		}
		$this->dirty = ($this->getPrimaryKey() ? $this->update() : $this->insert()) === false;

		return !$this->dirty;
	}

	/**
	 * @return false|int
	 */
	public function insert()
	{
		$data = $this->getData();
		unset($data[$this->getPrimaryKey()]);
		$rowsAffected = $this->getDB()->insert($this->getTable(), $data);
		$this->setPrimaryKey($this->getDB()->insert_id);

		return $rowsAffected;
	}

	/**
	 * @return false|int
	 */
	public function update()
	{
		return $this->getDB()->update($this->getTable(), $this->getData(), array(
			$this->getIDColumn() => $this->getPrimaryKey(),
		));
	}

	/**
	 * @param $name string
	 * @return mixed
	 */
	public function __get($name)
	{
		if( !$this->hasColumn($name) ) {
			return null;
		}

		return array_key_exists($name, $this->data) ? $this->data[$name] : null;
	}

	/**
	 * @param $name string
	 * @param $value mixed
	 */
	public function __set($name, $value)
	{
		if( !$this->hasColumn($name) ) {
			return;
		}
		$this->data[$name] = $value;
		$this->dirty = true;
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param array $data
	 * @param bool $flagDirty
	 */
	public function setData($data, $flagDirty = true)
	{
		$this->data = array();
		foreach($data as $column => $value) {
			if( $this->hasColumn($column) ) {
				$this->data[$column] = $value;
				$this->dirty = (bool)$flagDirty;
			}
		}
	}

	/**
	 * @return wpdb
	 */
	public function getDB()
	{
		if( $this->db === null ) {
			global $wpdb;
			$this->db = $wpdb;
		}

		return $this->db;
	}

	/**
	 * @param wpdb $db
	 */
	public function setDB($db)
	{
		$this->db = $db;
	}

	/**
	 * @return int
	 */
	public function getPrimaryKey()
	{
		return $this->{$this->getIDColumn()};
	}

	/**
	 * @param int $value
	 */
	public function setPrimaryKey($value)
	{
		$this->{$this->getIDColumn()} = $value;
	}
}