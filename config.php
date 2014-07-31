<?php

    try {
        $con = mysqli_connect("localhost","root","","test");
		$basepath = "/PhpFileUpload-master/PHPMySQLFileUpload/storepdfs/";
		define("tableName" ,  "standardpdf");
		define("col1" , "id");//an auto-increment ID
		define("col2" , "account");//the 6 character identifier of the Account
		define("col3" , "standard_number");//the numeric identifier of the Standard
		define("col4" , "sequence");//for now this will always be ‘1’ as it’s a 1-to-1 relationship of PDF-to-Standard but ultimately we’ll allow multiples and this would control order
		define("col5" , "url");//the URL where the PDF is stored
		define("col6" , "created_on");//timestamp of when the record was created
		define("col7" , "created_by");//the ID of the user that created the record ($session_RepID)
		define("col8" , "modified_on");//timestamp of when the record was last modified
		define("col9" , "modified_by");//the ID of the user that last modified the record ($session_RepID)
		
		
		
		//Prepare query string for each CRUD operations
		function getRetriveQuery($param3) {
			$retriveQuery = "SELECT * from standardpdf 
							 WHERE " .col3. " = $param3 
							 ORDER BY " .col6. " desc limit 1";
			return $retriveQuery;
		}
		 
		function getUpdateQuery($val1,
								$val2,
								$val3
								) {
			$updateQuery = "UPDATE " .tableName. "
								SET " .col2. " ='$val1',
									" .col5. " ='$val2',
									" .col9. " ='$val3'";
		
			return $updateQuery;
		}
		function getInsertQuery($val2,
								$val3,
								$val5,
								$val7,
								$val9) {
		$insertQuery = "INSERT INTO " .tableName. " 
									(
									 ".col2.",
									 ".col3.",
									 ".col5.",
									 ".col7.",
									 ".col9."
									) 
								VALUES
									 (
									 '$val2',
									 '$val3',
									 '$val5',
									 '$val7',
									 '$val9'
									 )
								";
			return $insertQuery;
		}
		function getDeleteQuery ($param3) {
			$deleteQuery = "DELETE FROM " .tableName. " 
							 WHERE ".col3."=$param3";
		}
    }
    catch (PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
		//die('unable to connect to database ' . $e->getMessage());
    }    
    // create LM object, pass in PDO connection
    //$lm = new lazy_mofo($dbh);

?>