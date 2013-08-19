<?php
class Rules
{
	/* Connection to MySQL database */
	private $connection;

	/* Private properties */
	private $_description; /* Exposed via getDescription() */
	private $_preprocess;
	private $_postprocess;
	private $_query;
	private $_message;
	private $_output; /* Exposed via getOutput() */
	private $_active; /* Exposed via getActive() */


	private function connect( $host, $username, $password, $database )
	{
		$this->connection = new mysqli( $host, $username, $password, $database );
	}


	private function disconnect()
	{
		$this->connection->close();
	}


	/* Get rule data */
	private function get_rule_data( $ruleid )
	{
		if ( $results = $this->connection->query( "select rule_desc
			                                       ,      rule_query
			                                       ,      rule_message
			                                       ,      rule_active
			                                       ,      rule_preproc
			                                       ,      rule_postproc
			                                       from   rules
			                                       where  rule_id = ${ruleid}" ) ) {
			$row = mysqli_fetch_array( $results );
			$this->_description = $row[0];
			$this->_query = $row[1];
			$this->_message = $row[2];
			$this->_active = $row[3];
			$this->_preprocess = $row[4];
			$this->_postprocess = $row[5];
		}
	}


	public function run_rule( $ruleid )
	{
		/* Get rule data */
		$this->get_rule_data( $ruleid );

		if ( $this->_active == 'Y' ) {
			/* Clear any previous output */
			$this->_output = NULL;

			/* Execute pre process */
			if ( strlen( $this->_preprocess ) > 0 ) {
				$this->connection->query( $this->_preprocess );
			}

			/* Execute the actual rule */
			$rule_result = $this->connection->query( $this->_query );

			/* Format the message */
			if ( mysqli_num_rows( $rule_result ) > 0 ) {
				$this->_output = vsprintf( $this->_message, mysqli_fetch_array( $rule_result, MYSQLI_NUM ) );
			}

			/* Execute post process */
			if ( strlen( $this->_postprocess ) > 0 ) {
				$this->connection->query( $this->_postprocess );
			}

			/* Update last usage timestamp for rule */
			$this->connection->query( "update rules set rule_last_used=now() where rule_id = ${ruleid}" );

			/* Result result message */
			return ( strlen( $this->_output ) > 0 );
		} 

		/* Return failed indicator */
		return false;
	}


	/* Return the value of the private property _ */
	public function getDescription()
	{
		return $this->_description;
	}


	/* Return the value of the private property _output */
	public function getOutput()
	{
		return $this->_output;
	}


	/* Return the value of the private property _active */
	public function getActive()
	{
		return $this->_active;
	}


	/* Default Constructor */
	public function __construct( $host, $username, $password, $database )
	{
		$this->connect( $host, $username, $password, $database );
	}


	/* Default Destructor */
   function __destruct() {
   		$this->disconnect();
   }
}
?>