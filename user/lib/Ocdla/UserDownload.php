<?php

namespace Ocdla;


class UserDownload
{

	public $info;

	public function __construct($memberid,$productid)
	{

		if(empty($productid)) throw new Exception('Class UserDownload: constructor expects parameter $productid.');
	
		if(empty($memberid)) throw new Exception('Class UserDownload: constructor expects parameter $memberid.');



		$stmt = db_query('SELECT * FROM {members} m LEFT JOIN {downloads} d ON(m.id=d.memberid) JOIN {catalog} cat ON(cat.i=d.productid) WHERE memberid=:1 AND productid=:2',array($memberid,$productid));
		$this->info = $stmt->fetch_assoc();
		
		clearstatcache();
		/* one last error should be thrown if we can't find the file associated with this product */
	
		/**
		 * Check to make sure the file referred to actually exists
		 */
		if(false&&!file_exists($this->info["DownloadLocation"]))
		{
			throw new Exception("Class UserDownload: Associated file {$this->info['DownloadLocation']} does not exist on this filesystem.");
		}
	}
	
	public function getDownloadType()
	{
		return $this->info['SoftwareType'];
	}



	public function getProductId()
	{
		return $this->info['i'];
	}
	
	public function getMemberId()
	{
		return $this->info['id'];
	}

}