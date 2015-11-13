<?php

function LodLogout($UserID)
{
	$cookie = new \Ocdla\Http\LodCookie($UserID);
	$cookiefile = $cookie->getFilePath();
	$request_body = \formatRequestBody( array( 'action' => 'logout') );
	$lod_logout = \cinit( SSO_WIKI_API, $request_body, $cookiefile );

	// $lod_logout_response = \parseResponse( $lod_logout['response_body'] );
	// tail( 'sso logout response:'.print_r($lod_logout, TRUE) );

}

function removeCookies($cookieNames,$options=null)
{
	if(is_array($options['prefix']))
	{
		foreach($options['prefix'] as $prefix)
		{
			removeCookie($cookieNames,$prefix);
		}
	}
	else
	{
		removeCookie($cookieNames);
	}
}


function removeCookie($cookieNames,$prefix=null)
{
	if(is_array($cookieNames))
	{
		foreach($cookieNames as $cName)
		{
			$fullName = isset($prefix) ? $prefix.$cName : $cName;
			\setcookie($fullName, '', time()-3600, "/", ".ocdla.org");		
		}
	}
	else
	{
		$fullName = isset($prefix) ? $prefix.$cookieNames : $cookieNames;
		\setcookie($fullName, '', time()-3600, "/", ".ocdla.org");	
	}
}