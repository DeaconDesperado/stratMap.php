#stratMap


###Background
Nobody likes munging associative arrays in PHP.  On first glance, PHP's implementation of what's commonly referred to as a 'dictionary', 'object' or 'hash' in other languages appears to afford a great level of flexibility with data munging.  The problem is that most of the operations need to be performed by global scope functions.  Tedious reformatting can quickly become nontrivial and take up tons of lines of code, especially if in array remapping is required.

Let's assume the following situation:  You have a REST api you maintain with PHP in front of it.  The system you are curling to up until now has returned the following data format:

```
{
	"name":"Bob Blocker",
	"dob":"January 18, 1978",
	"favorite_food":"pasta"
}
```

Your need to mangle this data for your client's new frontend format (or for whatever reason, really) to something like this:

```
{
	"name":"Bob Blocker",
	"date_of_birth":"1978-01-18",
	"likes":"pasta"
}
```

Two objectives exist:

1. Change some keynames in place, `dob`->`date_of_birth` and `favorite_food`->`likes`
2. Reformat the date time of the birth date.

Using stratmap, these values are easily generated from the input data inline.

```
$sm = new stratMap($input_data);
$sm->remap('dob','date_of_birth');
$sm->remap('favorite_food','likes');
$sm->mapCallback('date_of_birth',function($dob){
	$dt = new DateTime($dob);
	return $dt->format('Y-m-d');
})

$output_format = $sm->generate();
```

Remapping is presently a 1 to 1 relationship and will replace the key inline.  Any key that is not remapped will be preserved in the output.

Callbacks can be chained and will be processed against the key in the order they are specified to stratMap.

A call to `generate` will create the complete output format to the spec, but you can also access individual keys using the typical php key access: `$sm['output_key']`.  Unmapped keys will be available via their input name.  Callbacks are processed at request time, so if your callbacks are computationally expensive, they will only fire as needed when using key access.

The `stratMap` instance itself is json serializable.

#Why?
At work, I had the dubious pleasure of reformatting a single, huge JSON serial for several different legacy systems with PHP as the broker between them.  The resulting code was ugly.  Very ugly.  stratMap is intended to make these operations a little less painful.

#TODO
1. Add support for `nest` and `unNest` to break out nested arrays.
2. Add support for splitting values, like `name` -> `first_name` and `last_name`
3. Add a setting to preserve unmapped keys or discard them by default.
4. Decide how to handle stdClass objects and whether or not to make them equivalent.