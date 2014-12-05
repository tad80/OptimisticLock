<?php

App::uses('ModelBehavior', 'Model');

class OptimisticLockBehavior extends ModelBehavior {

	public $config = array();
	public static $defaultConfig = array(
		'field' => 'modified',
		'message' => 'Update conflict, another user has already updated the record. Please list and edit the record again.',
	);

	public function setup(Model $model, $config = array()) {
		$this->config = Hash::merge(self::$defaultConfig, (array)$config);
		if (!$model->schema($this->config['field'])) {
			throw new BadFunctionCallException(__d('optimistic_lock', 'Model %s doesn\'t have field %s.', $model->alias, $this->config['field']));
		}
	}

	public function beforeValidate(Model $model, $options = array()) {
		if (isset($model->data[$model->alias]['id']) ){
			if ($currentRecord = $model->findById($model->data[$model->alias]['id'])) {
				if($model->data[$model->alias][$this->config['field']] != $currentRecord[$model->alias][$this->config['field']]) {
					$model->validationErrors[$this->config['field']] = $this->config['message'];
					return false;
				}
			}
		}
		return true;
	}
}