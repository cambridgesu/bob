<?php

# Database wrapper class; this contains no application-specific logic or awareness of database structure

namespace cusu\bob;

class database
{
	# Connection
	private $connection = NULL;
	
	# Handle to errors array
	private $errors = array ();
	
	
	# Constructor
	public function __construct ($hostname, $username, $password, $database, &$errors)
	{
		# Create a handle to the main BOB errors property
		$this->errors = &$errors;
		
		# Connect to the database as the specified user
		if (!$connection = @mysql_connect ($hostname, $username, $password)) {
			$this->errors[] = "Error opening database connection with the database username '<strong>" . htmlspecialchars ($username) . "</strong>'. The database server said: '<em>" . htmlspecialchars (mysql_error ()) . "</em>'";
			return;
		}
		
		# Ensure we are talking in UTF-8
		if (!mysql_query ("SET NAMES 'utf8';")) {
			$this->errors[] = "Error setting the database connection to UTF-8";
			return;
		}
		
		# Connect to the database
		if (!@mysql_select_db ($database, $connection)) {
			$this->errors[] = "Error selecting the database '<strong>" . htmlspecialchars ($database) . "</strong>'. Check it exists and that the user {$dbUsername} has rights to it. The database server said: '<em>" . htmlspecialchars (mysql_error ()) . "</em>'";
			return;
		}
		
		# Register the succesful connection
		$this->connection = $connection;
	}
	
	
	# Connection status
	public function connected ()
	{
		return ($this->connection);
	}
	
	
	# Function to close the database explicitly so that it cannot be reused
	public function close ()
	{
		# End if no connection anyway
		if (!$this->connection) {return;}
		
		# Explicitly close the connection
		mysql_close ($this->connection);
		
		# Null the internal connection property
		$this->connection = NULL;
	}
}

?>