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
		# Connect to the database as the specified user, or end
		if (!$this->connection = @mysqli_connect ($hostname, $username, $password)) {
			$this->errors[] = "Error opening database connection with the database username '<strong>" . htmlspecialchars ($username) . "</strong>'. The database server said: '<em>" . htmlspecialchars (mysqli_connect_error ()) . "</em>'";
			return;		// End
		}
		
		# Ensure we are talking in Unicode, or end
		if (!mysqli_query ($this->connection, "SET NAMES 'utf8';")) {
			$this->errors[] = "Error setting the database connection to UTF-8";
			return;		// End
		}
		
		# Select the database, or end
		if (!@mysqli_select_db ($this->connection, $database)) {
			$this->errors[] = "Error selecting the database '<strong>" . htmlspecialchars ($database) . "</strong>'. Check it exists and that the user {$username} has rights to it. The database server said: '<em>" . htmlspecialchars (mysqli_error ($this->connection)) . "</em>'";
			$this->connection = NULL;
			return;		// End
		}
		
	}
	
	
	# Function to return the connection status
	public function isConnected ()
	{
		return ($this->connection);
	}
	
	
	# Getter to obtain the raw connection
	public function getConnection ()
	{
		return $this->connection;
	}
	
	
	# Getter to return errors
	public function getErrors ()
	{
		return $this->errors;
	}
	
	
	# Function to close the database explicitly
	public function close ()
	{
		# Explicitly close the database connection so that it cannot be reused
		if ($this->connection) {
			mysqli_close ($this->connection);
			$this->connection = NULL;
		}
	}
	
}

?>
