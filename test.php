<?php
require_once('stratmap.php');


function alwaysPoop(){
	return 'poop';
}

$input_array = array(
	'foo'=>'bar',
	'said'=>'the bear',
	'leaveme'=>'alone'
);

$sm = new stratMap($input_array);

$sm->remap('foo','baz');
$sm->mapCallback('baz',function($arg) { return $arg.'!!!!'; });
$sm->mapCallback('baz',function($arg) { return $arg.'DFD'; });
$sm->remap('said','screamed');


print_r($sm['screamed']);

echo "\n";

print_r($sm->generate());

print_r(json_encode($sm));

