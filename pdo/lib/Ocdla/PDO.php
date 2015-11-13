<?php

namespace Ocdla;

class PDO
{
	public $db;
	
	public function __construct() {
		try {
			$this->db = new \PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET, DB_USER, DB_PASS);
			return $this->db;
		}
		catch(\PDOException $ex)
		{
			print $ex->getMessage();
		}
		return $this->db;
	}
}