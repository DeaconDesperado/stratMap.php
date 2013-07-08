<?php
require_once('stratmap.php');


function alwaysPoop(){
	return 'poop';
}

$input_array = array('foo'=>'bar');

$sm = new stratMap($input_array);

$sm->remap('foo','baz');
$sm->mapCallback('baz',function($arg) { return $arg.'!!!!'; });
$sm->mapCallback('baz','alwaysPoop');
$sm->generate();

echo "\n";

echo $sm['baz'];

