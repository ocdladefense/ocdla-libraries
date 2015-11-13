<?php

namespace Ocdla\Http;

class LodCookie
{
	protected $cookieFilePath;
	
	public function __construct($UserID)
	{
		$this->cookieFilePath = COOKIE_PATH . '/cookiefile_'.$UserID.'.txt';


		if( !$h = fopen( $this->cookieFilePath, 'w') )
		{
			throw new Exception('Error trying to open cookie file.');
		}

		fclose($h);
	}
	public function getFilePath()
	{
		return $this->cookieFilePath;
	}

}