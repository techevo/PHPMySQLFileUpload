<?php
include("config.php");
$targetFolder = '/uploads'; // Relative to the root

//view pdf
//queryfrom the database the file path by using order number and passing the file path 
if($_POST['operationtype'] == 'view'){
	$root = realpath($_SERVER["DOCUMENT_ROOT"]);
	$file = $root.'/PHPMySQLFileUpload/uploads/18072014033237036_1405677757036_XXXPT6161X_ITRV.pdf';
	downloadFile($file);
}
if($_POST['delete']=='delete'){
	$root = realpath($_SERVER["DOCUMENT_ROOT"]);
	$fileName = $root."/FileUploadUpdate/uploads/18072014033237036_1405677757036_XXXPT6161X_ITRV.pdf"
	renameFile($fileName);
}
//delete pdf
//rename file when delete and make the column "file path" value to null and enable add button
function renameFile($fileName){
	rename($fileName,$root."/FileUploadUpdate/uploads/_obsolete.pdf");
	//modify database column for that row.
}

//change pdf
//rename the existing pdf by retriving the file path from the database and update the lastmodifieddate and file path columns


function downloadFile($file) { // $file = include path 
        if(file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
        }
		else{
			echo 'file does not exist';
		}
}
if (!empty($_FILES)) {
	$tempFile = $_FILES['file']['tmp_name'];
	$targetPath = dirname(__FILE__) . '/' . $targetFolder;
	$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['file']['name'];
	// Validate the file type
	$fileTypes = array('pdf'); // File extensions
	$fileParts = pathinfo($_FILES['file']['name']);
	
	if (in_array($fileParts['extension'],$fileTypes)) {
		move_uploaded_file($tempFile,$targetFile);
		echo '1';
	} else {
		echo 'Invalid file type.';
	}
}
?>