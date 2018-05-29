<?php
/*
System Class: database - Mint. Converted for Wordpress :(

Usage:
$db = new database("mysql-server.example.com", "username", "password", "database_name");

Calls:
database::prepared();	// This is the core call for prepared statements
database::query();		// A safe way to call query
database::insert();		// A safe way to insert fields
database::update();		// A safe way to update fields

Other stuff:
database::get_mysql_datetime(); // Converts a MySQL DateTime field into a PHP timestamp

*/
class MintDB {
	const RETURN_DEFAULT=0;	// Default. Returns the PDO object
	const RETURN_ASSOC=1; 	// Returns an associative array of the entire result
	const RETURN_LAST_ID=2; // Returns the last_insert_id for inserts
	const RETURN_ARRAY=3; 	// Returns an array of the entire result
	
	static $core;
	
	static function connect($host, $username, $password, $database) {
		self::$core = new mint_database($host, $username, $password, $database);
	}
	static function prepared($query, $params='', $return_type=MintDB::RETURN_ASSOC) {
		return self::$core->prepared($query, $params, $return_type);
	}
	static function select($table, $params='', $return_type=MintDB::RETURN_ASSOC) {
		return self::$core->select($table, $params, $return_type);
	}
	static function insert($table, $params, $return_type=MintDB::RETURN_LAST_ID) {
		return self::$core->insert($table, $params, $return_type);
	}
	static function update($table, $where_what, $where_equals, $params) {
		return self::$core->update($table, $where_what, $where_equals, $params);
	}
	
	// Extra functions
	static function get_mysql_datetime($date) {
		return mktime(substr($date,11,2),substr($date,14,2),substr($date,17,2),substr($date,5,2),substr($date,8,2),substr($date,0,4));
	}
	
	static function sanitize_fields($params) {
		$new_params = array();
		foreach ($params as $key=>$val) {
			// I'm sure we're breaking a rule here...
			// You shouldn't be taking field names from user input anyway!
			$new_key = preg_replace('/[^[0-9,a-z,A-Z$_]]/', '', $key);
			$new_params[$new_key] = $val;
		}
		return $new_params;
	}
}

// This class can be instanced to open multiple database connections
class mint_database {
	var $pdo;
	var $debug;
	var $last_query;
	var $attempted_connect;
	
	// This stores all the connection information
	private $connection;
	
	function __construct($host, $username, $password, $database) {
		// This prepares us for connect();
		$this->connection['host'] = $host;
		$this->connection['username'] = $username;
		$this->connection['password'] = $password;
		$this->connection['database'] = $database;
		
		// Internals
		$this->attempted_connect = false;
		$this->debug = false;
	}
	
	// Connect executes on the first database call.
	// This optimization is used to reduce the amount of unneeded database connections
	// while keeping the functionality of having an "always on" database
	// You can manually connect if you plan on using PDO outside of this class
	function connect() {
		if ($this->attempted_connect == true) {
			return;
		}
		try {
			$this->pdo = new PDO('mysql:host=' . $this->connection['host'] . ';dbname=' . $this->connection['database'], $this->connection['username'], $this->connection['password'], array(PDO::ATTR_PERSISTENT => false));
			$this->pdo->exec("SET CHARACTER SET utf8");
			
			// Disable reconnecting
			$this->connection = array();
			$this->attempted_connect = true;
		}
		catch (Exception $e) {
			//...
			// Wtf PHP?
			// Just using this 'try' block to prevent PASSWORD AND USERNAMES from showing up in connection error messages.

			if ($this->debug == true) {
				# Sanitize important information as best as possible.
				# Warning: If the host/user/pass are partially showing, then this won't sanitize it.
				$err = print_r($e, true);
				$err = str_replace($this->connection['host'], '[host]', $err);
				$err = str_replace($this->connection['password'], '[pass]', $err);
				$err = str_replace($this->connection['username'], '[user]', $err);

				echo $err;
			}
			else {
				echo '<!-- Warning: There was an error conneting to the database. -->';
			}
		}
	}
	
	/*
	Example: prepared('SELECT * FROM `users` WHERE username=:username', array(":username"=>"stuff"));
	*/
	function prepared($query, $params='', $return_type=MintDB::RETURN_ASSOC) {
		// Store for debugging
		if ($this->debug == true) {
			$this->last_query['query'] = $query;
			$this->last_query['params'] = $params;
			$this->last_query['error'] = array();
		}
		
		// Make sure we're connected
		$this->connect();
		
		// Prepare the query
		$result = $this->pdo->prepare($query);

		// We have to use this slow ass loop instead of $result->execute($params) because of special integer bindings
		if (is_array($params)) {
			foreach($params as $name => &$val) {
				if ((int)$val === $val) {
					// Do special bindings for integers
					$result->bindValue(':' . $name, (int)$val, PDO::PARAM_INT);
				}
				else {
					$result->bindValue(':' . $name, $val);
				}
			}
		}
		
		// Now execute with the rest of the values
		$result->execute();

		// Save error info for debug
		if ($this->debug == true) {
			$this->last_query['error'] = $result->errorInfo();
		}
		
		// Return based on our type
		switch ($return_type) {
			case MintDB::RETURN_ASSOC:
				return $result->fetchAll(PDO::FETCH_ASSOC);
				break;
			case MintDB::RETURN_ARRAY:
				return $result->fetchAll(PDO::FETCH_NUM);
				break;
			case MintDB::RETURN_LAST_ID:
				return $this->pdo->lastInsertId();
				break;
			default:
				return $result;
				break;
		}
	}
	function select($table, $params="", $return_type=MintDB::RETURN_ASSOC) {
		$where_query=Array();
		$nothing = true;
		

		if (is_array($params)) { // is_array = the most effective way to do it
			foreach ($params as $key=>$value) {
				if ($key != '') {
					$where_query[] = $key . "=:" . $key;
					$nothing = false;
				}
			}
		}
		else {
			$params = array();
		}
		
		if ($nothing == true) {
			$query = "SELECT * FROM `" . $table. "`;";
		}
		else {	
			$query = "SELECT * FROM `" . $table. "` WHERE " . implode(" AND ", $where_query) . ";";
		}
		return $this->prepared($query, $params, $return_type);
	}
	function insert($table, $params='', $return_type=MintDB::RETURN_LAST_ID) {
		if ($params == '' || count($params) <= 0) {
			$query = 'INSERT INTO `' . $table . '` () value ()';
		}
		else {
			// Prepare our statement
			$keys = '`' . implode('`, `', array_keys($params)) . '`'; # includes `quotes` so field names don't get mixed up with system names
			$values = array();
			foreach ($params as $name=>$v) {

				// isset not very effective here. Returns false positives
				//if (isset($v['func'])) { // isset faster than in_array? http://www.phpbench.com/


				// Is this a special function?
				if (is_array($v)) {
					$values[] = $this->special_param($v);
					unset($params[$name]);
				}
				else {
					$values[] = ':' . $name;
				}
			}
			$values = implode(",", $values);
			
			// INSERT INTO `$table` ($keys) VALUES ($values);
			$query = 'INSERT INTO `' . $table . '` (' . $keys . ') value (' . $values . ')';
		}
		return $this->prepared($query, $params, $return_type);
	}
	function update($table, $where_what, $where_equals, $params) {
		$values = Array();
		
		// Prepare our statement
		foreach ($params as $name => $value) {
			// Is there a tag at all?
			if (!empty($name)) {
				// isset returns false positives
				//if (isset($value['func'])) { // isset is faster than is_array? http://www.phpbench.com/


				if (is_array($value)) {
					$values[] = '`' . $name . '`=' . $this->special_param($value);
					unset($params[$name]);
				}
				else {
					$values[] = '`' . $name . '`=:' . $name;
				}
			}
		}
		
		// Add this to our final parameters
		$params[$where_what] = $where_equals;
		
		// Execute the query
		$query = 'UPDATE `' . $table . '` SET ' . implode(',', $values) . ' WHERE `' . $where_what . '`=:' . $where_what . ';';
		return $this->prepared($query, $params);
	}
	
	
	// This processes special parameters
	private function special_param($func) {
		if (isset($func['func'])) {
			switch ($func['func']) {
				case 'NOW':
					return 'NOW()';
					break;
				case 'CURDATE':
					return 'CURDATE()';
					break;
				case 'CURTIME':
					return 'CURTIME()';
					break;
			}
		}
		return 'NULL';
	}
}