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
		if (!array_key_exists('id', $model->data[$model->alias]) || !$model->data[$model->alias]['id']) {
			if (!isset($model->id) || !$model->id) {
				return true;
			}
			$model->data[$model->alias]['id'] = $model->id;
		}
		if (!array_key_exists('opt_' . $this->config['field'], $model->data[$model->alias]) || !$model->data[$model->alias]['opt_' . $this->config['field']]) {
			throw new RuntimeException(__d('optimistic_lock', 'Field %s doesn\'t appear in the post request.', $this->config['field']));
		}
		if ($currentRecord = $model->findById($model->data[$model->alias]['id'])) {
			if($model->data[$model->alias]['opt_' . $this->config['field']] != $currentRecord[$model->alias][$this->config['field']]) {
				$model->validationErrors[$this->config['field']] = $this->config['message'];
				return false;
			}
		}
		unset($model->data[$model->alias]['opt_' . $this->config['field']]);
		return true;
	}

	public function beforeSave(Model $model, $options = array()) {
		if (array_key_exists($model->data[$model->alias]['opt_' . $this->config['field']])) {
			unset($model->data[$model->alias]['opt_' . $this->config['field']]);
		}
		return true;
	}
}
