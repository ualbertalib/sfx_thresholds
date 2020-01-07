<?php


class curl {

	static $hndl; // Handle
	static $b = ''; // Response body
	static $h = ''; // Response head
	static $i = array();
	
	static function head($ch,$data) {
		curl::$h .= $data;
		return strlen($data);
	}
	
	static function body($ch,$data) {
		curl::$b .= $data;
		return strlen($data);
	}
	
	static function fetch($url,$opts = array()) {
		curl::$h = curl::$b = '';
		curl::$i = array();
		curl::$hndl = curl_init($url);
		curl_setopt_array(curl::$hndl,$opts);
		curl_exec(curl::$hndl);
		curl::$i = curl_getinfo(curl::$hndl);
		curl_close(curl::$hndl);
	}
}