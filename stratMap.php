<?php

class stratMap implements JsonSerializable,ArrayAccess{

	/**
	 * The original input array, stores values by input key
	 */
	private $input_array = array();

	/**
	 * The output array as populated by doRemapping method 
	 */
	private $output_array = array();

	/**
	 * Stores input keys to output keys
	 */
	private $keymap = array();

	/**
	 * Stores callbacks to be run on input keys
	 */
	private $callbacks = array();

	public function __construct($base_array){
		if(!is_null($base_array) and is_array($base_array)){
			$this->input_array = $base_array;
			foreach(array_keys($this->input_array) as $input_key){
				$this->keymap[$input_key] = $input_key;
			}
		}else if(!is_null($base_array)){
			throw new Exception('If passed, input must be an associative array');
		}
	}

	/**
	 * Map a key from the input array to the output array
	 * Calling this twice will replace any attached callbacks and reset the mapping
	 */
	public function remap($key, $output_key){
		$this->keymap[$key] = $output_key;	
	}

	/**
	 * Run a callback against an input key.  Set the return value to the output key if listed,
	 * or just set like named keys in the output array if not
	 *
	 * Function can be a callable or a string that will be used with call_user_func
	 */
	public function mapCallback($output_key, $function){
		if(!array_key_exists($output_key,$this->callbacks)){
			$this->callbacks[$output_key] = array($function);
		}else{
			$this->callbacks[$output_key][] = $function;
		}
	}

	/** 
	 * Remap a key and generate it to output array 
	 */
	private function doRemapping($input_key){
		$output_key = isset($this->keymap[$input_key]) ? $this->keymap[$input_key] : $input_key; 
		$this->output_array[$output_key] = $this->input_array[$input_key];
		if(isset($this->callbacks[$output_key]) and is_array($this->callbacks[$output_key])){
			foreach($this->callbacks[$output_key] as $func){
				if(is_callable($func)){
					$this->output_array[$output_key] = $func($this->output_array[$output_key]);
				}else{
					$this->output_array[$output_key] = call_user_func($func,$this->output_array[$output_key]);	
				}
			}
		}
	}

	/**
	 * Perform remapping on the entire array and return it
	 */
	public function generate(){
		$this->output_array = array();
		foreach(array_keys($this->input_array) as $input_key){
			$this->doRemapping($input_key);
		}
		return $this->output_array;
	}

	/**
	 * Json serialize as though this were an actual associative array
	 */
	public function jsonSerialize(){
		$this->generate();
		return $this->output_array;
	}

	/**
	 * Set a value in the output array manually
	 */
	public function offsetSet($key,$val){
		$this->output_array[$key] = $val;
	}

	/**
	 * Check a key exists in output array, whether already remapped or not
	 * Will not match input keys
	 */	
	public function offsetExists($key){
		return (array_key_exists($this->output_array) || in_array($this->keymap));
	}

	/**
	 * Delete an offset from output array
	 */
	public function offsetUnset($key){
		if(array_key_exists($key,$this->output_array)){
			unset($this->output_array[$key]);
		}

		$remapKey = array_search($key,$this->keymap);
		if($remapKey){
			unset($this->keymap[$remapKey]);
		}
	}

	/**
	 * Get a value from the output array, remap if necessary
	 */
	public function offsetGet($key){
		$input_key = array_search($key,$this->keymap);
		$this->doRemapping($input_key);
		return $this->output_array[$key];
	}
}
