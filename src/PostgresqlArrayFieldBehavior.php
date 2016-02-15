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
	protected function _loadArray()
	{
		$rawData = $this->getRawData();
		$value = $this->_postresqlArrayDecode($rawData);
		$this->getModel()->setAttribute($this->getArrayFieldName(), $value);

		return $this;
	}

	/**
	 * Decodes PostgreSQL array data into PHP array
	 *
	 * @param $data
	 * @return mixed
	 * @todo implementation
	 */
	protected function _postresqlArrayDecode($data)
	{
		return $data;
	}

	/**
	 * Sets array field data into format suitable for save
	 *
	 * @return $this
	 */
	protected function _saveArray()
	{
		$value = $this->getModel()->getAttribute($this->getArrayFieldName());;
		$value = $this->_postresqlArrayEncode($value);
		$this->getModel()->setAttribute($this->getArrayFieldName(), $value);

		return $this;
	}

	/**
	 * Encodes PHP array into PostgreSQL array data format
	 *
	 * @param $value
	 * @return mixed
	 * @todo implementation
	 */
	protected function _postresqlArrayEncode($value)
	{
		return $value;
	}
}
