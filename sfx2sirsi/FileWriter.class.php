<?php


class FileWriter {
	
	private $file = 'sfx2sirsiError.txt';
	
	function __construct(){
		file_put_contents($this->file, "");
	}
	
	
	
	function write($string){
		file_put_contents($this->file, $string, FILE_APPEND);
	}

	
}