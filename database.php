<?php


# Database class, providing a wrapper to database functions, but contains no application logic
class database
{
	# Class properties
	private $connection = NULL;
	private $errors = array ();
	
	
	# Function to connect to the database
	public function __construct ($hostname, $username, $password, $database)
	{
		# Construct the DSN
		$dsn = "mysql:host={$hostname};dbname={$database}";
		
		# Enable exception throwing; see: https://php.net/pdo.error-handling
		$driverOptions[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
		
		# Use real prepared statements, which also supports native types (integers/floats are returned as such rather than as strings)
		$driverOptions[PDO::ATTR_EMULATE_PREPARES] = false;
		
		# Connect to the database as the specified user, or end
		try {
			$this->connection = new PDO ($dsn, $username, $password, $driverOptions);
		} catch (PDOException $e) {		// "PDO::__construct() will always throw a PDOException if the connection fails regardless of which PDO::ATTR_ERRMODE is currently set." noted at http://php.net/pdo.error-handling
			$this->connection = NULL;
			$this->errors[] = "Error opening database connection for database '<strong>" . htmlspecialchars ($database) . "</strong>' with the username '<strong>" . htmlspecialchars ($username) . "</strong>'. The database server said: '<em>" . htmlspecialchars ($e->getMessage ()) . "</em>'";
			return;
		}
		
		# Ensure we are talking in Unicode, or end
		if (!$result = $this->query ("SET NAMES 'utf8';")) {
			$this->connection = NULL;
			$this->errors[] = 'Error setting the database connection to UTF-8';
			return;		// End
		}
		
	}
	
	
	# Function to return the connection status
	public function isConnected ()
	{
		return ($this->connection);
	}
	
	
	# Getter to return errors
	public function getErrors ()
	{
		return $this->errors;
	}
	
	
	# Function to close the database connection explicitly
	public function close ()
	{
		# Explicitly close the database connection so that it cannot be reused
		if ($this->connection) {
			$this->connection = NULL;	// This is sufficient to prevent further use of this class; full closure also requires closing references such as from a PDOStatement instance
		}
	}
	
	
	# Generalised function to get data from an SQL query and return it as an array
	#!# Add failures as an explicit return false; this is not insecure at present though as array() will be retured (equating to boolean false), with the calling code then stopping execution in each case
	public function getData ($query, $preparedStatementValues = array ())
	{
		# Create an empty array to hold the data
		$data = array ();
		
		# Execute the statement (ending if there is an error in the query or parameters)
		try {
			$this->preparedStatement = $this->connection->prepare ($query);
			$this->preparedStatement->execute ($preparedStatementValues);
		} catch (PDOException $e) {
			return $data;
		}
		
		# Fetch the data
		$this->preparedStatement->setFetchMode (PDO::FETCH_ASSOC);
		$data = $this->preparedStatement->fetchAll ();
		
		# Return the array
		return $data;
	}
	
	
	# Function to execute a query, intended for query types that do not return a result set
	public function query ($query)
	{
		# Run the query
		try {
			$this->connection->exec ($query);
		} catch (PDOException $e) {
			return false;
		}
		
		# Return success
		return true;
	}
	
	
	# Function to execute a query, intended for query types that return a row count (e.g. insert/update)
	public function execute ($query)
	{
		# Run the query and obtain the number of rows (which may be zero), or false on failure
		try {
			$rows = $this->connection->exec ($query);
		} catch (PDOException $e) {
			return false;
		}
		
		# Return the number of rows (which may be zero)
		return $rows;
	}
	
	
	# Function to get one row
	public function getOne ($query, $preparedStatementValues = array ())
	{
		# Get the data (indexed numerically), or end
		if (!$data = $this->getData ($query, $preparedStatementValues)) {return false;}
		
		# Ensure there is only one row
		if (count ($data) != 1) {return false;}
		
		# Return the first row
		return $data[0];
	}
	
	
	# Function to create a table from a list of fields
	public function createTable ($name, $fields)
	{
		# Construct the list of fields
		$fieldsSql = array ();
		foreach ($fields as $fieldname => $specification) {
			$fieldsSql[] = "{$fieldname} {$specification}";
		}
		
		# Compile the overall SQL; type is deliberately set to InnoDB so that rows are physically stored in the unique key order
		$query = "CREATE TABLE `{$name}` (" . implode (', ', $fieldsSql) . ") ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
		
		# Create the table
		if (!$this->query ($query)) {
			$this->errors[] = "There was a problem setting up the {$name} table.";
			return false;
		}
		
		# Signal success
		return true;
	}
	
	
	# Function to obtain a list of tables in a database
	#!# Add failures as an explicit return false; this is not insecure at present though as array() will be retured (equating to boolean false), with the calling code then stopping execution in each case
	public function getTables ($database)
	{
		# Create a list of tables, alphabetically ordered, and put the result into an array
		$query = "SHOW TABLES FROM `{$database}`;";
		
		# Start a list of tables
		$tables = array ();
		
		# Get the tables
		if (!$tablesList = $this->getData ($query)) {
			return $tables;
		}
		
		# Rearrange
		foreach ($tablesList as $index => $attributes) {
			$tables[] = $attributes["Tables_in_{$database}"];
		}
		
		# Return the list of tables
		return $tables;
	}
	
	
	# Function to get the field specification for each field in a table, returning a CREATE TABLE -style string
	public function getFieldTypes ($database, $table)
	{
		# Obtain the current fields; error handling not really needed as we know that the table exists
		$query = "SHOW FULL FIELDS FROM `{$database}`.`{$table}`;";
		if (!$data = $this->getData ($query)) {
			$this->errors[] = "The field status for the table name {$table} could not be retrieved.";
			return false;
		}
		
		# Create a list of fields, building up a string for each equivalent to the per-field specification in a CREATE TABLE query
		$fields = array ();
		foreach ($data as $index => $field) {
			$key = $field['Field'];
			$specification  = strtoupper ($field['Type']);
			if (strlen ($field['Collation'])) {$specification .= ' collate ' . $field['Collation'];}
			if (strtoupper ($field['Null']) == 'NO') {$specification .= ' NOT NULL';}
			if (strtoupper ($field['Key']) == 'PRI') {$specification .= ' PRIMARY KEY';}
			if (strlen ($field['Default'])) {$specification .= ' DEFAULT ' . $field['Default'];}
			$fields[$key] = $specification;
		}
		
		# Return the specification
		return $fields;
	}
	
	
	# Function to determine whether the engine type of a table is InnoDB, which supports transactions and automatic ordering
	public function tableIsInnoDB ($table)
	{
		# Obtain the table type
		$query = "SHOW TABLE STATUS LIKE '{$table}';";	// LIKE does do an exact match here; using only a substring fails to return any results
		if (!$data = $this->getOne ($query)) {
			$this->errors[] = "The table status for the table name {$table} could not be retrieved.";
			return false;
		}
		
		# Check the type
		$engine = $data['Engine'];
		if ($engine != 'InnoDB') {
			$this->errors[] = "The table {$table} is not using the InnoDB storage engine.";
			return false;
		}
		
		# Return success
		return true;
	}
	
}

?>
