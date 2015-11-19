<?php

namespace Ocdla;


class UserDownload
{
	/**
	 * variable downloadPath
	 *
	 * The path prepended to saved files. The
	 * complete filename should be saved as:
	 * self::$downloadPath . '/'. $user_filename
	 * where $user_filename = "{$this->username}_{$this->filename}".
	 */
	static $destPath = DOWNLOAD_PATH;
	
	static $sourcePath = UPLOAD_PATH;
	
	public $type;
	
	const ownerpw = "ocdla";
	
	const ITEM_TITLE_SEPARATOR = '-';
	
	private $userId;
	
	/**
	 * var username
	 *
	 * The username of the user associated with this download.
	 */
	private $username;
	
	private $password;
	
	private $productId;
	
	private $productName;
	
	private $downloadLocation;

	public function setDownloadPath($path)
	{
		self::$downloadPath = $path;
	}

	public static function newFromDownloadId($id)
	{
	
	}
	
	public static function newFromParams($params)
	{
		if(empty($params['memberId'])||empty($params['productId']))
		{
			throw new \Exception('Class UserDownload: constructor expects parameter memberId.');
		}
		$stmt = db_query('SELECT d.i AS downloadId FROM {downloads} d WHERE memberid=:memberId AND productid=:productId',
			array('memberId'=>$params['memberId']
				,'productId'=>$params['productId']),'pdo');
		$info = $stmt->fetch();
		return new UserDownload($info['downloadId']);
	}

	public function __construct($downloadId)
	{	
		if(empty($downloadId)) throw new \Exception('Class UserDownload: constructor expects parameter downloadId.');
		
		$stmt = db_query('SELECT m.username, m.autoId, m.id AS memberId, cat.i AS productId, cat.DownloadLocation, cat.Item, cat.SoftwareType, d.* FROM {members} m LEFT JOIN {downloads} d ON(m.id=d.memberid) JOIN {catalog} cat ON(cat.i=d.productid) WHERE d.i=:downloadId',
			array('downloadId'=>$downloadId),'pdo');
		
		$info = $stmt->fetch();
		if(!$info) throw new \Exception("No download with id, {$downloadId}, exists!");
		
		$this->productId 				= $info['productId'];
		
		$this->downloadId				= $info['i'];
		
		$this->userId 					= $info['memberId'];
		
		$this->productName			= $this->parseProductName($info['Item']);
		
		$this->type 						= $info['SoftwareType'];
		
		$path 									= explode('\\',$info["DownloadLocation"]);
		$this->filename 				= array_pop($path);
		
		$this->username 				= $info['username'];
		
		$this->password 				= $info['password'];
		
		$this->userFilename 		= "{$this->username}_{$this->filename}";
		$this->userFilename			= $this->type == 'zip'? $this->userFilename .'.zip':
																	$this->userFilename;
		
		clearstatcache();
		/* one last error should be thrown if we can't find the file associated with this product */
	
		/**
		 * Check to make sure the file referred to actually exists
		 */
		if(false&&!file_exists($this->downloadLocation))
		{
			throw new \Exception("Class UserDownload: Associated file {$this->downloadLocation} does not exist on this filesystem.");
		}
	}

	private function parseProductName($title)
	{
		return
			$this->titleHasSeparator($title)
					?
					substr($info["Item"],0,strpos($info["Item"],self::ITEM_TITLE_SEPARATOR))
					:
					$title;
	}
	
	private function titleHasSeparator($title)
	{
		return strpos($title,self::ITEM_TITLE_SEPARATOR)!==false;
	}

	public function fileExists()
	{
		return file_exists(self::$sourcePath .'/' . $this->filename);
	}

	public function getDownloadType()
	{
		return $this->type;
	}

	public function getProductName()
	{
		return $this->productName;
	}

	public function getProductId()
	{
		return $this->productId;
	}
	
	public function getMemberId()
	{
		return $this->userId;
	}
	
	public function getUserId()
	{
		return $this->userId;
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
					"memberid={$this->userId}",
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
					"memberid={$this->userId}",
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
	
	private function getFilepath()
	{
		return self::$sourcePath .'/' . $this->filename;
	}
	
	private function userFileExists($basePath)
	{
		$path = isset($basePath)?$base_path:self::$sourcePath;
		return file_exists($path . "/{$this->userFilename}");
	}
	
	private function getZipCommand()
	{
		$executable = "/usr/bin/zip";
		$options = "rv9";
		$inFile = ".";
		$outFile = "../{$this->userFilename}";
		return $executable . " -{$options} {$outFile} {$inFile}";
	}
	
	private function createUserZip()
	{
		if($this->type!='zip') throw new \Exception("Cannot create a zip archive from a non-zip source!");
		$sourceDir = self::$sourcePath . '/' . $this->filename;
		if(chdir($sourceDir))
		{
			return shell_exec($this->getZipCommand());
		}
		else throw new \Exception("Could not initialize zip creation within directory, {$sourceDir}.");
	}
	
	public function copyToDownloads()
	{
		$source = self::$sourcePath . '/' . $this->userFilename;
		$target = self::$destPath . '/' . $this->userFilename;
		return copy($source,$target);
	}
	
	private function createUserPdf()
	{	
		if ($this->type != "pdf") throw new \Exception('Class UserDownload: function createUserPdf being invoked on a non-pdf UserDownload instance!'); 
		
		if (chdir(self::$downloadPath))
		{
			exec($pdftk_command, $output, $return_var);
		}
		else
		{
			throw new \Exception("Could not change directory to {self::$downloadPath}.");
		}
		
		$pdftk_command = "/usr/bin/pdftk ".self::$uploadPath . "/{$this->filename} output " . self::$downloadPath . "/{$this->userFilename} owner_pw ".self::$ownerpw." allow Printing CopyContents verbose >> /var/log/pdftk-web.log";	
		
		exec($pdftk_command, $output, $return_var);
		return $return_var;	
	}

	private function fileStatusIcon($boolean)
	{
		if($boolean)
		{
			return "<img style='width:18px;' src='/sites/default/files/icons/status-ok.png' alt='File exists.' />";
		}
		else
		{
			return "<img style='width:18px;' src='/sites/default/files/icons/chrome_red_lock_details.png' alt='File doesn't exist.' />";
		}
	}

	
	public function getUrl()
	{
		return '/sites/default/files/downloads/'.$this->userFilename;
	}

	public function __toString()
	{
		$sourceFileStatus = $this->fileStatusIcon($this->fileExists());
		$userFileStatus = $this->fileStatusIcon($this->userFileExists());
									
		$str = "<table style='margin-bottom:8px; width:475px; border:1px solid #666;'>
			<thead>
				<tr>
					<th>FileExists?</th>
					<th>FileName</th>
					<th>Type</th>
					<th>DownloadId</th>
					<th>UserId</th>
					<th>ProductId</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style='width:25px;text-align:center;'>
						{$sourceFileStatus}
					</td>
					<td style='width:225px;text-align:center;'>{$this->filename}</td>
					<td style='text-align:center;'>{$this->type}</td>
					<td style='text-align:center;'>{$this->downloadId}</td>
					<td style='text-align:center;'>{$this->userId}</td>
					<td style='text-align:center;'>{$this->productId}</td>
				</tr>
				<tr>
					<td colspan='6' style='text-align:center;background-color:#666;color:#fff;'>
						Below is the user information for this file:
					</td>
				</tr>
				<tr>
					<td colspan='6' style='text-align:center;background-color:#666;color:#fff;'>
						{$this->getFilepath()}
					</td>
				</tr>
				<tr style='border-top:1px solid #666;'>
					<td style='text-align:center;'>
						{$userFileStatus}
					</td>
					<td colspan='5'>
						{$this->getZipCommand()}
					</td>
				</tr>
			</tbody>
		</table>";
		return $str;
	}
}