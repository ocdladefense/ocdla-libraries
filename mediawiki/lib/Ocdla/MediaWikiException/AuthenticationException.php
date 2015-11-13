<?php

namespace Ocdla\MediaWikiException;

class AuthenticationException extends \Exception
{

	private $ApiQuery;
	private $ApiResult;
	
	public function __construct( $ApiQuery, $ApiResult )
	{
		parent::__construct('MediaWikiApi Login Failure');
		$this->ApiQuery = $ApiQuery;
		$this->ApiResult = $ApiResult;
		$this->message .= "\nAPI Query was:\n" . $this->ApiQuery;
		$this->message .= "\n\nAPI Result was:\n" . print_r($this->ApiResult,true);
	}

}