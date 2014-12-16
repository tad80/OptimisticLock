<?php
App::uses('OptimisticLock.OptimisticLock', 'Model/Behavior');

class OptimisticLockPost extends CakeTestModel {
	public $useTable = 'posts';
	public $actsAs = array(
		'OptimisticLock.OptimisticLock' => array(
			'message' => 'This is error.',
		),
	);
	public $alias = 'Post';
}

class OptimisticLockPostWrongField extends CakeTestModel {
	public $useTable = 'posts';
	public $actsAs = array(
		'OptimisticLock.OptimisticLock' => array(
			'field' => 'updated',
		),
	);
	public $alias = 'Post';
}

class OptimisticLockTest extends CakeTestCase {

	public $fixtures = array('plugin.optimistic_lock.post');

	public function setUp() {
		$this->Post = new OptimisticLockPost();
	}

	public function tearDown() {
		unset($this->Post);
		ClassRegistry::flush();
	}

	public function testValidate() {
		$post = $this->Post->findById(1);
		$this->Post->set($post);
		$this->assertTrue($this->Post->validates());
		$post['Post']['modified'] = date('Y/m/d H:i:s', time());
		$this->Post->set($post);
		$this->assertFalse($this->Post->validates());
		$this->assertEqual('This is error.', $this->Post->validationErrors['modified']);
	}

	public function testValidateException() {
		try {
			$this->PostWrongField = new OptimisticLockPostWrongField();
			$this->expectException(BadFunctionCallException);
		} catch (BadFunctionCallException $e) {
		}
		try {
			$post = $this->Post->findById(1);
			unset($post['Post']['modified']);
			$this->Post->set($post);
			$this->Post->validates();
			$this->expectException(RuntimeException);
		} catch (RuntimeException $e) {
		}
	}
}
