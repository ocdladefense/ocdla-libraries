<?php

//include an array of files here
//class DBQuery implements QueryPrinter {
class DBQuery {

	private $joins;
	private $querystring;
	private $results;
	private $num_rows;
	private $table_string;
	private $join_string;
	private $params;

	private $dbh;
	
	public function getNumRows() {
		return $this->num_rows;
	}

	private function connect() {
		if(!$this->dbh)
		{
			$p=get_resource_info('default');
			$this->dbh = mysql_connect($p['hostname'],$p['username'],$p['password']);
			mysql_query('use ocdla',$this->dbh);
		}
	}
	
	protected function getConnectionString() {
		//call an ASP function to fetch the connection string
	}
	
	protected function db_close() {
		//close the sql connection
	}

	public function __construct( $params = array() ) {
		$this->params = $params;
		// tail('params are: ' . print_r($params, TRUE) );
		// set default values:
		$this->join_string = "";
		// define a default WHERE clause based on index values
		// used for all cases
		
		if( isset( $params["joins"] ) ) $this->joins = $params["joins"];
		if( !isset( $params["tablenames"] ) ) throw new Exception('SQL table missing from parameter.');
		
			$table_counter = 1;
			
			if(count($params["tablenames"])==1) {
				$this->table_string = $params["tablenames"][0]["name"];
			
			}//
			else {// count($params["tablenames"]) 

					$t1 = $params["tablenames"][0];				
					$t2 = $params["tablenames"][1];
				
				
					$this->table_string .= "{$t1['name']} {$t2['op']} {$t2['name']} ON ({$t1['name']}.{$t1['fields'][0]}={$t2['name']}.{$t2['fields'][0]})";
					
					try{
						$t3 = $params["tablenames"][2];
					} catch( Exception $e ) {
						throw new Exception('DBQuery class: could not assign third table join clause.');
					}//catch
					if( !empty( $t3 ) ) {
						$this->table_string .= " {$t3['op']} {$t3['name']} ON ({$t2['name']}.{$t2['fields'][1]}={$t3['name']}.{$t3['fields'][0]})";
					}// if table 3 is valid

			}//else
			
					// join clauses take the form of:
					// "joins" => array(
					//		array(
					//			"join_tables" => array(
					//				array(
					//					"tablename" => "catalog",
					//					"alias" => "cat1"
					//				),//first join table
					//				array(
					//					"tablename" => "catalog",
					//					"alias" => "cat2"
					//				)//second join table
					//			),//these tables make up the first join
					//			"join_type" => "LEFT JOIN",
					//			"join_fields" => array(
					//				"alias1.field1",
					//				"alias2.field2"
					//			)//join_fields
					//			"join_op" => "="
					//		),//first join
					//		array(//second join and so on..
					//			...
					//		)//second join
					//	)//joins
					if( isset( $params["joins"] ) ) {
						// do some basic syntax checking:
						if( count( $this->joins ) < 1 ) throw new Exception('Class DBQuery: Join parameter found but no join information given.');

						foreach( $this->joins AS $join ) {
							$this->join_string .= $join["join_tables"][0]["tablename"];
							if( !empty( $join["join_tables"][0]["alias"] ) ) $this->join_string .= " AS {$join['join_tables'][0]['alias']}";
							$this->join_string .= " " . $join["join_type"] . " ";
							$this->join_string .= $join["join_tables"][1]["tablename"];
							if( !empty( $join["join_tables"][1]["alias"] ) ) $this->join_string .= " AS {$join['join_tables'][1]['alias']} ";
							
							//construct the ON clause
							$this->join_string .= " ON (";
							$this->join_string .= $join["join_fields"][0] . "=" . $join["join_fields"][1];
							$this->join_string .= ") ";
						}//foreach
						
						// override the table string with the join string, which should take precedence.
						$this->table_string = $this->join_string;
					}//if

		$where = array();

//if($params['type']!='insert') {
		if( isset($params["keys"]) ) {
			foreach( $params["keys"] AS $key=>$value ) {
				$where[]="$key='$value'";
			}
			$where = isset($params['op']) ? implode(" {$params['op']} ",$where) : $where[0];
		} elseif( is_array($params['where']) ) {
			$where = implode(' ', $params["where"]);
		} else {
			$where = $params['where'];
		}
		
		// we should do some checks on the WHERE clause here
		if( empty($where) && $params["type"]!="insert" ) {
			throw new Exception('DBQuery class: WHERE clause expected, instead empty string.');
			return;
		}
//}	
	
	
	
		// assume that $params["type"] is set
		switch( $params["type"] ) {

			case "select":
				if( isset( $params["select"] ) ) {
					$select_columns = implode( $params["select"], ',');
					$this->querystring = "SELECT {$select_columns} FROM {$this->table_string} WHERE {$where}";
				} else {
				$this->querystring = "SELECT * FROM {$this->table_string} WHERE $where";
				}
				break;
			
			case "update":
				$set = array();
				foreach( $params["fields"] AS $key=>$value ) {
					$set[] = "$key='$value'";
				}
				$set = implode(',',$set);
			
				$this->querystring = "UPDATE {$this->table_string} SET $set WHERE $where";
				break;
			
			case "insert":
				$query_columns = array();
				$query_values = array();

				foreach( $params["fields"] AS $key=>$value ) {
					$insert_columns[] = $key;
					$insert_values[] = "'{$value}'";//return either int or string					
				}//foreach

				$this->querystring = "INSERT INTO {$this->table_string} (" . implode(',',$insert_columns) .") VALUES(" . implode(',',$insert_values) . ")";
				$this->exec();
				break;
			
			case "delete":
				//require the indices or keys array to be set
				$this->querystring = "DELETE FROM {$this->table_string} WHERE {$where}";		
				$this->exec();
				break;
		}
	}
	
	public function getTables()
	{
		return $this->table_string;
	}
	
	public function getQuery()
	{
		return $this->querystring;
	}
	
	
	public function exec()
	{
		$this->connect();
		$resource = mysql_query($this->querystring,$this->dbh);
		if (!$resource)
		{
			$msg = "<p>Error: " . mysql_error() . "</p><p>Query:<pre>{$this->querystring}</pre></p>";
			throw new Exception($msg);
		}
		
		if ($this->params["type"]=="update")
		{
			$this->num_rows = mysql_affected_rows();		
		}
		
		if ($this->params["type"]=="select")
		{
			$this->num_rows = mysql_num_rows($resource);
			for($i=0; $i<mysql_num_rows($resource);$i++)
			{
				$row = mysql_fetch_assoc($resource);
				$this->results[$i] = array();
				foreach($row as $key=>$value)
				{
					$this->results[$i][$key]=$value;
				}
			}
			return $this->results;
		}
	}
	
	public function dump()
	{
		print_r($this->exec());
	}
	
	public static function formatDate($type = "today")
	{
		$date = "";
		switch($type)
		{
			case "today":
				$date = date('Ymd');
				break;
		}
		return $date;
	}
}