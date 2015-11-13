<?php


namespace Ocdla\MediaWiki;

use Ocdla\Http\LodCookie as LodCookie;
use Ocdla\Http\LodTestCookie as LodTestCookie;
use Ocdla\MediaWikiException\AuthenticationException as AuthenticationException;

class ApiAuthenticationRequest
{
	const API_RESPONSE_FORMAT = 'xml';
	
	protected $apiUrl;
	
	protected $uid;
	
	protected $username;
	
	protected $password;
	
	protected $cookie;
	
	protected $cookiefile;

	private $initParams;
	
	private $loginParams;
	
	private $initReqBody;
	
	private $loginReqBody;

	private $initRespParser;
	
	private $loginRespParser;
	
	private $cookies = array( 'sessionid' => 'session', 'lguserid'=>'UserID', 'lgusername' => 'UserName', 'lgtoken' => 'Token');
	
	public function __construct($apiUrl,$UserID,$username,$password='1234')
	{	
		$this->apiUrl = $apiUrl;
		$cookieClass = $apiUrl==SSO_WIKI_API_TEST?"\\Ocdla\\Http\\LodTestCookie":"\\Ocdla\\Http\\LodCookie";
		$this->cookie = new $cookieClass($UserID);
		$this->cookiefile = $this->cookie->getFilePath();
		$this->username 		= $username;
		$this->password 		= $password;
	
		$this->initParams = array(
			'action'			=> 'login',
			'lgname'			=> $username,
			'lgpassword'	=> $password,
			'format'			=> self::API_RESPONSE_FORMAT,
		);
		$this->loginParams = array(
			'action'			=> 'login',
			'lgname'			=> $username,
			'lgpassword'	=> $password,
			'format'			=> self::API_RESPONSE_FORMAT,
		);
	}

	public function doAuthentication()
	{
		$this->sendInitialRequest();
	
		$this->sendAuthenticationRequest();

		$this->setMediaWikiAuthCookies();
		
		if(!$this->success())
		{
			throw new AuthenticationException($this);
		}
		return true;
	}

	private function doRequest($params)
	{
		return $this->doCurlRequest($this->apiUrl,$this->formatRequestBody($params),$this->cookiefile);
	}
	
	private function doCurlRequest( $url, $body, $cookiefile )
	{
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
	
	protected function sendInitialRequest()
	{
		$init = $this->doRequest($this->initParams);
		$this->initRespParser = new ApiLoginResponseParser($init['response_body']);
		$this->lgtoken=$this->initRespParser->getToken();
	}

	protected function sendAuthenticationRequest()
	{
		$confirm = $this->doRequest($this->loginParams+array('lgtoken'=>$this->lgtoken));
		$this->loginRespParser = new ApiLoginResponseParser($confirm['response_body']);
		$this->secondaryResponseXml=$this->loginRespParser->getXml();
	}
	
	public function success()
	{
		return ($this->loginRespParser->getResult() == 'Success');
	}
	
	public function getLoginResult()
	{
		return $this->loginRespParser->getResult();
	}
	
	private function getRequestParams($stage='init')
	{
		switch($stage)
		{
			case 'init':
				return $this->formatRequestBody($this->initParams);
				break;
			case 'login':
				return $this->formatRequestBody($this->loginParams);
				break;
		}
	}
	

	
	private function formatRequestBody($params=array())
	{
		$b;
		foreach( $params AS $k=>$v ) {
			$b[] = "$k=$v";
		}
		$b = implode("&",$b);
		return $b;
	}
	

	
	public function setMediaWikiAuthCookies()
	{
		foreach($this->cookies as $key=>$cookiename)
		{
			setcookie($this->getVal('cookieprefix') . $cookiename, $this->getVal($key), time() + 2592000, "/", ".ocdla.org");
		}
	}
	
	private function getVal($key)
	{
		return $this->loginRespParser->{$key};
	}
	
	public function __toString()
	{
		$ret="<h3>Initial Request:</h3><p>{$this->getRequestParams('init')}</p>";
		$ret.=$this->initRespParser;
		$ret.="<h3>Secondary Request:</h3><p>{$this->getRequestParams('login')}</p>";
		$ret.=$this->loginRespParser;
		return $ret;
	}
}