<?php
/**
 * PostgreSQL array fields support behavior
 *
 * Usage example:
 *
 * ```php
 * use yii\db\ActiveRecord;
 * use \kossmoss\PostgresqlArrayField\PostgresqlArrayFieldBehavior;
 *
 * /**
 *  * @property array $modelField; // this field has array format
 *  *\/
 * class Model extends ActiveRecord{
 *
 * ...
 *     public function behaviors() {
 *         return [
 *             'class' => PostgresqlArrayAccessFieldBehavior::className(),
 *             'arrayFieldName' => 'modelField'
 *         ];
 *     }
 * ...
 * }
 *
 * After that $modelField can be handled as array; it will be saved into database as PostgreSQL array
 * and loaded from database as a PHP array
 *
 * @author kossmoss <radiokoss@gmail.com>
 */

namespace kossmoss\PostgresqlArrayField;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class PostgresqlArrayFieldBehavior extends Behavior
{
	/**
	 * @var string Field name supposed to contain array data
	 */
	protected $arrayFieldName;

	/**
	 * @inheritdoc
	 */
	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_FIND => '_loadArray',
			ActiveRecord::EVENT_BEFORE_UPDATE => '_saveArray'
		];
	}

	/**
	 * Returns array field name
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getArrayFieldName()
	{
		if (!$this->arrayFieldName) {
			throw new \Exception('Array field name doesn\'t exist');
		}

		return $this->arrayFieldName;
	}

	/**
	 * Sets array field name
	 *
	 * @param $arrayFieldName
	 * @return $this
	 */
	public function setArrayFieldName($arrayFieldName)
	{
		$this->arrayFieldName = $arrayFieldName;

		return $this;
	}

	/**
	 * Returns model
	 *
	 * @return ActiveRecord
	 * @throws \Exception
	 */
	protected function getModel()
	{
		if (!$model = $this->owner) {
			throw new \Exception('Model is not been initialized properly.');
		}
		if (!$model instanceof ActiveRecord) {
			throw new \Exception(sprintf('Behavior must be applied to the ActiveRecord model class and it\'s iheritants, the unsupported class provided: `%s`', get_class($model)));
		}

		return $model;
	}

	/**
	 * Loads raw data from model
	 *
	 * @return string Postgresql-coded array representation
	 * @throws \Exception
	 */
	protected function getRawData()
	{
		return $this->getModel()->getAttribute($this->getArrayFieldName());
	}

	/**
	 * Sets raw sata to the model
	 * @param $data
	 * @return $this
	 * @throws \Exception
	 */
	protected function setRawData($data)
	{
		$this->getModel()->setAttribute($this->getArrayFieldName(), $data);

		return $this;
	}

	/**
	 * Loads array field
	 * @return $this
	 */
	public function _loadArray()
	{
		$rawData = $this->getRawData();
		$value = $this->_postgresqlArrayDecode($rawData);
		$this->getModel()->setAttribute($this->getArrayFieldName(), $value);

		return $this;
	}

	/**
	 * Decodes PostgreSQL array data into PHP array
	 *
	 * @link http://stackoverflow.com/questions/3068683/convert-postgresql-array-to-php-array/27964420#27964420
	 *
	 * @param string $data PostgreSQL-encoded array
	 *
	 * @param int $start start position for recursive inner arrays parsing
	 * @return array
	 */
	protected function _postgresqlArrayDecode($data, $start = 0)
	{
		if (empty($data) || $data[0] != '{') {
			return null;
		}

		$result = [];

		$string = false;
		$quote = '';
		$len = strlen($data);
		$v = '';

		for ($i = $start + 1; $i < $len; $i++) {
			$ch = $data[$i];

			if (!$string && $ch == '}') {
				if ($v !== '' || !empty($result)) {
					$result[] = $v;
				}
				break;
			} else if (!$string && $ch == '{') {
				$v = $this->_postgresqlArrayDecode($data, $i);
			} else if (!$string && $ch == ',') {
				$result[] = $v;
				$v = '';
			} else if (!$string && ($ch == '"' || $ch == "'")) {
				$string = true;
				$quote = $ch;
			} else if ($string && $ch == $quote && $data[$i - 1] == "\\") {
				$v = substr($v, 0, -1) . $ch;
			} else if ($string && $ch == $quote && $data[$i - 1] != "\\") {
				$string = false;
			} else {
				$v .= $ch;
			}
		}

		return $result;
	}

	/**
	 * Sets array field data into format suitable for save
	 *
	 * @return $this
	 */
	public function _saveArray()
	{
		$value = $this->getModel()->getAttribute($this->getArrayFieldName());;
		$value = $this->_postgresqlArrayEncode($value);
		$this->getModel()->setAttribute($this->getArrayFieldName(), $value);

		return $this;
	}

	/**
	 * Encodes PHP array into PostgreSQL array data format
	 *
	 * @param array $value
	 * @return string PostgreSQL-encoded array
	 * @throws \Exception
	 */
	protected function _postgresqlArrayEncode($value)
	{
		if (empty($value) || !is_array($value)) {
			return null;
		}

		$result = '{';
		$firstElem = true;

		foreach($value as $elem) {
			// add comma before element if it is not the first one
			if(!$firstElem){
				$result .= ',';
			}
			if(is_array($elem)){
				$result .= $this->_postgresqlArrayEncode($elem);
			}else if(is_string($elem)){
				if(strpos($elem, ',') !== false) {
					$result .= '"'.$elem.'"';
				}else{
					$result .= $elem;
				}
			}else if(is_numeric($elem)){
				$result .= $elem;
			}else{
				// we can only save strings and numeric
				throw new \Exception('Array contains other than string or numeric values, can\'t save to PostgreSQL array field');
			}
			$firstElem = false;
		}
		$result .= '}';
		return $result;
	}
}
