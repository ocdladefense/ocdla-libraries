<?php
// $Id$
class Url {

public static function UsesHttps() {

	return empty( $_SERVER['HTTPS'] ) xor $_SERVER['HTTPS']=="off" ? FALSE : TRUE;

}

public static function GetReferrer() {

	return $_SERVER['HTTP_REFERER'];

}

public static function GetHostName() {
	
	return $_SERVER['SERVER_NAME'];
	
}

public static function GetDocumentRoot() {
	
	return empty($_SERVER['DOCUMENT_ROOT']) ? dirname(__FILE__) : $_SERVER['DOCUMENT_ROOT'];
	
}

public static function GetLink() {

	$protocol = self::UsesHttps() ? "https://" : "http://";
	return $protocol . self::GetHostName();
	
}

public static function PathAlter($args) {

	if( empty($args["source_path"]) ) {		
		$source_path = self::GetDocumentRoot();
	} else {
		$source_path = $args["source_path"];
	}//else
	
	if( empty($args["target_path"]) ) throw new Exception('Class Url: Altered path was not specified.');
	$target_path = $args["target_path"];
	
	$source_path_parts = explode( '/',$source_path);// change to simple '/' for Linux platforms
	$target_path_parts = explode( '../',$target_path );
	$parent_path_count = substr_count( $target_path, '../' );
	
	for( $i=1; $i <= $parent_path_count; $i++ ) {
		array_pop( $source_path_parts );
	}//for 
	
	array_push( $source_path_parts, $target_path_parts[count($target_path_parts)-1] );
	
	$altered_path = implode( '/', $source_path_parts );

	/*
	$watchdog = new Watchdog(
		$params = array(
			"wd_type" => "phptoolbox",
			"wd_msg"	=>	"Url::PathAlter successfully called.",
			"wd_vars"	=>	array(
				"source_path"						=>	$source_path,
				"target_path"						=>	$target_path,
				"altered_path"					=>	$altered_path,
				"parent_path_count"			=>	$parent_path_count
			)
		)
	);
	*/
	
	return $altered_path;
	
	
				
}//PathAlter


public static function GetInfo() {
	echo "<pre>";
	echo print_r( $_SERVER );
	echo "</pre>";
}

public static function toQueryString( $params = array() )
{
	$string = array();
	foreach($params AS $key=>$value) {
		$string []= "{$key}=".urlencode($value);
	}
	$string = implode('&',$string);
	return $string;
}

}//class Url