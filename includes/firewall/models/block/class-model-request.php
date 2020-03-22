<?php

namespace WBCR\Titan\Firewall\Model;

/**
 * Represents a request record
 *
 * @property int $id
 * @property float $attackLogTime
 * @property float $ctime
 * @property string $IP
 * @property bool $jsRun
 * @property int $statusCode
 * @property bool $isGoogle
 * @property int $userID
 * @property string $URL
 * @property string $referer
 * @property string $UA
 * @property string $action
 * @property string $actionDescription
 * @property string $actionData
 */
class Request extends Model {

	private static $actionDataEncodedParams = array(
		'paramKey',
		'paramValue',
		'path',
	);

	/**
	 * @param $actionData
	 * @return mixed|string|void
	 */
	public static function serializeActionData($actionData)
	{
		if( is_array($actionData) ) {
			foreach(self::$actionDataEncodedParams as $key) {
				if( array_key_exists($key, $actionData) ) {
					$actionData[$key] = base64_encode($actionData[$key]);
				}
			}
		}

		return json_encode($actionData);
	}

	/**
	 * @param $actionDataJSON
	 * @return mixed|string|void
	 */
	public static function unserializeActionData($actionDataJSON)
	{
		$actionData = json_decode($actionDataJSON, true);
		if( is_array($actionData) ) {
			foreach(self::$actionDataEncodedParams as $key) {
				if( array_key_exists($key, $actionData) ) {
					$actionData[$key] = base64_decode($actionData[$key]);
				}
			}
		}

		return $actionData;
	}

	private $columns = array(
		'id',
		'attackLogTime',
		'ctime',
		'IP',
		'jsRun',
		'statusCode',
		'isGoogle',
		'userID',
		'URL',
		'referer',
		'UA',
		'action',
		'actionDescription',
		'actionData',
	);

	public function getIDColumn()
	{
		return 'id';
	}

	public function getTable()
	{
		return wfDB::networkTable('wfHits');
	}

	public function hasColumn($column)
	{
		return in_array($column, $this->columns);
	}

	public function save()
	{
		$sapi = @php_sapi_name();
		if( $sapi == "cli" ) {
			return false;
		}

		return parent::save();
	}
}