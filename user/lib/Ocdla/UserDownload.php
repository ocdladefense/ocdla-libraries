<?php

namespace Ocdla;


class UserDownload
{

	public $info;
	
	public $type;
	
	const ownerpw = "ocdla";
	
	private $username;
	
	private $password;
	
	private $passphrase;
	
	private $productId;
	
	private $UserID;

	public function __construct($memberId,$productId)
	{
		
		$this->productId = $productId;
		
		
		$this->UserID = $memberId;

		if(empty($productId)) throw new \Exception('Class UserDownload: constructor expects parameter $productid.');
	
		if(empty($memberId)) throw new \Exception('Class UserDownload: constructor expects parameter $memberid.');



		$stmt = db_query('SELECT * FROM {members} m LEFT JOIN {downloads} d ON(m.id=d.memberid) JOIN {catalog} cat ON(cat.i=d.productid) WHERE memberid=:1 AND productid=:2',array($memberId,$productId));
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

	private function getPassword($security = "local")
	{
		// make sure the members table is also located locally
		
		
		
		// so the webserver should be serving up these files but instead they should be located on the pacinfo server in a protected directory
	}

	public function setFileCreationTime($time=null)
	{
		$update = new DBQuery(
			array(
				"type"=>"update",
				"tables"=>array(
					0 => array(
						"name"=>"downloads",
						"op" => "",
						"fields" => array()
					)
				),//tables
				"fields"=>array(
					"file_creation_time"=>$time
				),//fields
				"where"=>array(
					"memberid={$this->UserID}",
					"AND productid={$this->productId}"
				)
			)
		);
		
		$update->exec();
	}

	public function setNotificationTime($time = NULL)
	{
		$update = new DBQuery(
			array(
				"type"=>"update",
				"tables"=>array(
					0 => array(
						"name"=>"downloads",
						"op" => "",
						"fields" => array()
					)
				),//tables
				"fields"=>array(
					"notification_time"=>$time
				),//fields
				"where"=>array(
					"memberid={$this->UserID}",
					"productid={$this->productId}"
				)
			)
		);
		
		$update->exec();
	}	
	
	public function createUserFile()
	{
		if( $this->type == "pdf" ) $this->createUserPdf();
		else if( $this->type == "zip" ) $this->createUserZip();
	}
	
	private function createFile()
	{
	/**
	 * in the future, this function might check for things like an updated version of the file
	 * for now, return FALSE if the file has already been created
	 */
			$user_filename = "{$this->username}_{$this->filename}";
			if(file_exists( DOWNLOAD_PATH . "/{$this->username}_{$this->filename}") )
			{
				return FALSE;
			}
		else return TRUE;
	}
	
	private function createUserPdf()
	{	
		if( $this->type != "pdf" ) throw new Exception('Class UserDownload: function createUserPdf being invoked on a non-pdf UserDownload instance!'); 
		
		//
		// do some stuff to lookup the user and password, and perhaps what file they
		// are supposed to download.
		//
	
		// don't recreate the file if it already exists
		// @jbernal 2012-01-05 recreate the user file regardless of whether it exists or not
		$user_filename = "{$this->username}_{$this->filename}";
		/*	if(  $this->createFile()===FALSE ) {
		tail("Class UserDownloads: skipping file {$user_filename} (file already exists.)");
			return FALSE;
		} else if ($this->createFile()===TRUE ) {
		*/
		if (TRUE)
		{
			//if( file_exists( DOWNLOAD_PATH . "/pdftk.txt") ) unlink( DOWNLOAD_PATH . "/pdftk.txt" );

			// WE NEED TO RECREATE THE FILE EVEN IF IT ALREADY EXISTS!  in order to remove passwords on existing jbernal_duii files!!

			// we need to 			
			//		$pdftk_command = "/usr/local/bin/pdftk ".UPLOAD_PATH . "/{$this->filename} output " . DOWNLOAD_PATH . "/{$this->username}_{$this->filename} user_pw {$this->passphrase} owner_pw ".self::ownerpw." allow Printing CopyContents verbose >> pdftk.txt";	
		
			$pdftk_command = "/usr/local/bin/pdftk ".UPLOAD_PATH . "/{$this->filename} output " . DOWNLOAD_PATH . "/{$this->username}_{$this->filename} owner_pw ".self::ownerpw." allow Printing CopyContents verbose >> pdftk.txt";	
			chdir( DOWNLOAD_PATH );
			exec( $pdftk_command, $output, $return_var );
			return $return_var;	
		}
	}

	public function __toString()
	{
		$str = "<table style='border:1px solid #666;'>
			<thead>
				<tr>
				<th>FileName</th>
				<th>Type</th>
				<th>MemberId</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>foo</td>
					<td>bar</td>
					<td>baz</td>
				</tr>
			</tbody>
		</table>";
		return $str;
	}
}