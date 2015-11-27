<?php

namespace Ocdla;


class UserDownloadCollection
{
	/**
	 * member c, the collection.
	 */
	private $c;
	
	public function __construct()
	{
		$this->c = array();
	}
	
	public function add(UserDownload $download)
	{
		$this->c[$download->getMemberId()][] = $download;
	}

	
}