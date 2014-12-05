OptimisticLock
==============

CakePHP behavior plugin to implement optimistic locking for RDBMS.

## Usage

Most simply, just load this behavior in your model like this.
```php
class Post extends AppModel {
	public $actsAs = array('OptimisticLock.OptimisticLock');
}
```

You can specify which field to compare and error message shown in Model::validationErrors. Default will be like this.
```php
class Post extends AppModel {
	public $actsAs = array(
		'OptimisticLock.OptimisticLock' => array(
			'field' => 'modified',
			'message' => 'Update conflict, another user has already updated the record. Please list and edit the record again.',
		),
	);
}
```
