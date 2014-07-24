<?php

    try {
        $dbh = new PDO('mysql:host=localhost;dbname=lms;charset=UTF-8', 'root', 'admin');
		$columns = array('id','account','standard_number','sequence','url','created_on','created_by','modified_on','modified_by');
		$pdfTable = json_encode('StandardPDF',$columns);
    }
    catch (PDOException $e) {
        die('unable to connect to database ' . $e->getMessage());
    }    

    // create LM object, pass in PDO connection
    $lm = new lazy_mofo($dbh);

?>