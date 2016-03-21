# yii2-postgresql-array-field
Yii2 postgresql array field support behavior

================

Provides PostgreSQL array fields support for yii2 models.

Installation
------------
Add a dependency to your project's composer.json:

```json
{
	"require": {
		"kossmoss/yii2-postgresql-array-field": "^0.2"
	}
}
```

Usage example
--------------
#### Attach behavior to one or more fields of your model

```php
use yii\db\ActiveRecord;
use \kossmoss\PostgresqlArrayField\PostgresqlArrayFieldBehavior;

/**
 * @property array $modelField
 */
class Model extends ActiveRecord{
	public function behaviors() {
		return [
			[
				'class' => PostgresqlArrayFieldBehavior::className(),
				'arrayFieldName' => 'modelField', // model's field to attach behavior
				'onEmptySaveNull' => true // if set to false, empty array will be saved as empty PostreSQL array '{}' (default: true)
			]
		];
	}
}