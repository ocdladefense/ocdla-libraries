<?php

function cinit( $url, $body, $cookiefile ) {
	$ch = curl_init($url);
	curl_setopt($ch, 		CURLOPT_POST, true);
	// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
	curl_setopt($ch, 		CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, 		CURLOPT_ENCODING, "UTF-8" );
	curl_setopt($ch, 		CURLOPT_POSTFIELDS, $body);
	curl_setopt($ch, 		CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, 		CURLOPT_COOKIEFILE, $cookiefile);
	curl_setopt($ch, 		CURLOPT_COOKIEJAR, $cookiefile);
	curl_setopt($ch, 		CURLOPT_VERBOSE, 0);
	curl_setopt($ch, 		CURLOPT_HEADER, 1);
	$response = curl_exec ($ch);
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($response, 0, $header_size);
	$body = substr($response, $header_size);
	curl_close ($ch);
	return array('header'=>$header, 'response_body'=>$body);
}


/* login array*/ 
function parseResponse($xml)
{
	if(empty($xml)) return false;
	$d = new DOMDocument() and $d->preserveWhiteSpace = false and $d->formatOutput=true;
	$d->loadXML($xml);
	$a = $d->getElementsByTagName('login')->item(0)->attributes;
	if($result = $a->getNamedItem('result')->nodeValue and $result != 'Success' ) {
		$array = array(
			'result'=>$a->getNamedItem('result')->nodeValue,
			'token' => $a->getNamedItem('token')->nodeValue,
			'cookieprefix' => $a->getNamedItem('cookieprefix')->nodeValue,
			'sessionid' => $a->getNamedItem('sessionid')->nodeValue,
		);
	}
	else
	{
		$array = array(
			'result'=>$a->getNamedItem('result')->nodeValue,
			'cookieprefix' => $a->getNamedItem('cookieprefix')->nodeValue,
			'sessionid' => $a->getNamedItem('sessionid')->nodeValue,
			'lgtoken' => $a->getNamedItem('lgtoken')->nodeValue,
			'lguserid' => $a->getNamedItem('lguserid')->nodeValue,
			'lgusername' => $a->getNamedItem('lgusername')->nodeValue,
		);
	}
	return $array;
}

function getMediaWikiApiLoginResult($xml)
{
	$d = new DOMDocument() and $d->preserveWhiteSpace = false and $d->formatOutput=true;
	$d->loadXML($xml);
	$a = $d->getElementsByTagName('login')->item(0)->attributes;
	return $a->getNamedItem('result')->nodeValue;
}
/* login array*/ 
function parseMediaWikiLoginXml($r)
{
	if(empty($r)) return false;
	$d = new DOMDocument() and $d->preserveWhiteSpace = false and $d->formatOutput=true;
	$d->loadXML($r);
	$a = $d->getElementsByTagName('login')->item(0)->attributes;
	if($result = $a->getNamedItem('result')->nodeValue and $result != 'Success' ) {
		$array = array(
			'result'=>$a->getNamedItem('result')->nodeValue,
			'token' => $a->getNamedItem('token')->nodeValue,
			'cookieprefix' => $a->getNamedItem('cookieprefix')->nodeValue,
			'sessionid' => $a->getNamedItem('sessionid')->nodeValue,
		);
	}
	else
	{
		$array = array(
			'result'=>$a->getNamedItem('result')->nodeValue,
			'cookieprefix' => $a->getNamedItem('cookieprefix')->nodeValue,
			'sessionid' => $a->getNamedItem('sessionid')->nodeValue,
			'lgtoken' => $a->getNamedItem('lgtoken')->nodeValue,
			'lguserid' => $a->getNamedItem('lguserid')->nodeValue,
			'lgusername' => $a->getNamedItem('lgusername')->nodeValue,
		);
	}
	return $array;
}


/* login array*/ 
function getXmlResponse($r)
{
	if(empty($r)) return false;
	$d = new DOMDocument() and $d->preserveWhiteSpace = false and $d->formatOutput=true;
	$d->loadXML($r);
	return $d->saveXML();
}


function formatRequestBody( $body = array() ) {
	$b;
	foreach( $body AS $k=>$v ) {
		$b[] = "$k=$v";
	}
	$b = implode("&",$b);
	return $b;
}
