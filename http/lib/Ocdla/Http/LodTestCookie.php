<?php

namespace Ocdla\Http;

class LodTestCookie extends LodCookie {
	public function __construct($UserID){
		$this->cookieFilePath = COOKIE_PATH . '/lodtest_cookiefile_'.$UserID.'.txt';

		if( !$h = fopen( $this->cookieFilePath, 'w') )
		{
			ttail ( 'cookie file creation failed','lod');
			throw new Exception('Error trying to open cookie file.');
		}

		fclose($h);
	}
}