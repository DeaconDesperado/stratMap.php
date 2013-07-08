<?php
require_once('stratMap.php');

function alwaysPoop($arg){
	return 'poop';
}

class mapTest extends PHPUnit_Framework_TestCase{

	var $test_array = array(
		'foo'=>'bar',
		'baz'=>'brack',
		'leaveme'=>'alone'
	);

	protected $sm;

	protected function setUp(){
		$this->sm = new stratMap($this->test_array);
	}

	public function testRemappingKeyAccess(){
		$this->sm->remap('foo','snake');
		$this->assertEquals($this->sm['snake'],'bar');
		$this->assertEquals($this->sm['leaveme'],'alone');
	}

	public function testRemappingGeneration(){
		$this->sm->remap('foo','snake');
		$this->sm->remap('baz','tiger');
		$output = $this->sm->generate();
		$this->assertEquals($output['snake'],'bar');
		$this->assertEquals($output['tiger'],'brack');
	}

	public function testUnmappedValues(){
		$output = $this->sm->generate();
		$this->assertEquals($output,$this->test_array);
	}

	public function testLambdaCallback(){
		$this->sm->mapCallback('foo',function($arg){ return $arg.'!!!'; });
		$this->assertEquals($this->sm['foo'],'bar!!!');

		$output = $this->sm->generate();
		$this->assertEquals($output['foo'],'bar!!!');
	}

	public function testCallbackIdempotency(){
		$this->sm->mapCallback('foo', function($arg){ return $arg.'!!!'; });
		$this->sm->generate();
		$this->sm->generate();
		$this->sm->generate();
		$output = $this->sm->generate();
		$this->assertEquals($output['foo'],'bar!!!');

		$this->sm->remap('foo','cat');
		$output = $this->sm->generate();
		$this->assertEquals($output['cat'],'bar');
		$this->assertEquals(isset($output['foo']),False);
	}

	public function testNamedCallback(){
		$this->sm->mapCallback('foo','alwaysPoop');
		$this->assertEquals($this->sm['foo'],'poop');
	}

	public function testChainedCallbacks(){
		$this->sm->mapCallback('foo', function($arg){ return $arg.'!'; });
		$this->sm->mapCallback('foo', function($arg){ return $arg.'!'; });
		$this->sm->mapCallback('foo', function($arg){ return $arg.'!'; });
		$this->assertEquals($this->sm['foo'],'bar!!!');
	}

	public function testJsonSerialize(){
		$this->sm->remap('foo','bird');
		$this->sm->mapCallback('baz','alwaysPoop');
		$this->sm->mapCallback('bird',function($arg){ return 'man';});
		$output_serial = json_encode($this->sm);
		
		$expected = '{"bird":"man","baz":"poop","leaveme":"alone"}';
		$this->assertEquals($output_serial,$expected);
	}

	public function testAddValue(){
		$this->sm['arbitrary'] = 'January 18, 2000';
		$this->assertEquals($this->sm['arbitrary'],'January 18, 2000');
		$this->sm->mapCallback('arbitrary',function($arg){
			$dt = new DateTime($arg);
			return $dt->format('Y-m-d');
		});
		$this->assertEquals($this->sm['arbitrary'],'2000-01-18');
	}

	protected function tearDown(){
		unset($this->sm);
	}

}
