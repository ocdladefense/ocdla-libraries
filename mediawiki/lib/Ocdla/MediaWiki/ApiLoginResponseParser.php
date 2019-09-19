<?php


namespace Ocdla\MediaWiki;

/* login array*/ 
class ApiLoginResponseParser
{
	private $result;
	
	private $token;
	
	private $cookies;
	
	private $cookieprefix;
	
	private $sessionid;
	
	private $lgtoken;
	
	private $lguserid;
	
	private $lgusername;
	
	private $xml;
	
	private $header;
	
	
	
	public function getBody(){
		return $this->xml;
	}
	
	public function getHeader(){
		return $this->header;
	}
	
	public function __get($prop)
	{
		return $this->{$prop};
	}
	
	public function __construct($xml,$header)
	{
		$this->xml = $xml;
		$this->header = $header;
		$this->initCookies($header);
		
		if(empty($xml)) {
			throw new Exception('Could not log onto to Library of Defense.');
		}
		
		
		$d = new \DOMDocument() and $d->preserveWhiteSpace = false and $d->formatOutput=true;
		$d->loadXML($xml);
		

		$this->xml = $d->saveXML();
		$this->attributes = $d->getElementsByTagName('login')->item(0)->attributes;	
		$login=$d->getElementsByTagName('login')->item(0);

		$this->result					= $login->getAttribute('result');
		$this->token 					= $login->getAttribute('token');
		$this->cookieprefix 	= $login->getAttribute('cookieprefix');
		$this->sessionid 			= $login->getAttribute('sessionid');
		
		$this->lgtoken 				= $login->getAttribute('lgtoken');
		$this->lguserid 			= $login->getAttribute('lguserid');
		$this->lgusername 		= $login->getAttribute('lgusername');
	}
	
	
	private function initCookies($header){
		if(empty($header)){
			$this->cookies = array();
			return;
		}
		
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $matches);
		
		
		$cookies = array();
		
		foreach($matches[1] as $item) {
			parse_str($item, $cookie);
			$cookies = array_merge($cookies, $cookie);
		}
		
		$this->cookies = $cookies;
	}
	
	
	
	public function getCookies(){
		return $this->cookies;
	}
	
	
	public function getXml()
	{
		return $this->xml;
	}
	
	
	public function getResult()
	{
		return $this->result;
	}	
	
	
	public function getToken()
	{
		return $this->token;
	}
	
	
	public function __toString()
	{
		return "<pre>".htmlspecialchars($this->xml)."</pre>";
	}
}