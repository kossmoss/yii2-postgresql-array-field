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
		"kossmoss/yii2-postgresql-array-field": "^0.1"
	}
}
```

Usage example
--------------
#### Attach behavior to you model

Model have text attribute `data` for storage array

```php
namespace app\models;

use yii\db\ActiveRecord;
use \kossmoss\PostgresqlArrayField\PostgresqlArrayFieldBehavior;

class Model extends ActiveRecord{
	public function behaviors() {
		return [
			'class' => PostgresqlArrayFieldBehavior::className(),
			'arrayFieldName' => 'modelField'
		];
	}
}