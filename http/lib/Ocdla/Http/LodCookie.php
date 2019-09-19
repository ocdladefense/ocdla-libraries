<?php

namespace Ocdla\Http;

class LodCookie
{
	protected $cookieFilePath;
	
	public function __construct($uid, $mode = 'r')
	{
		$this->cookieFilePath = COOKIE_PATH . '/cookiefile_'.$uid.'.txt';


		if( !$h = fopen( $this->cookieFilePath, $mode) )
		{
			throw new Exception('Error trying to access cookie file: '.$this->cookieFilePath);
		}

		fclose($h);
	}
	public function getFilePath()
	{
		return $this->cookieFilePath;
	}
	


}