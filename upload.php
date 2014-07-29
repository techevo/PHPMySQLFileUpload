<?php
$con;
include("config.php");
$root = realpath($_SERVER["DOCUMENT_ROOT"]);
$targetFolder = 'storepdfs/'; // Relative to the root
$session_account = "ADV150";
$session_repID = "DemoRep";

if (!isset($_SESSION["session_account"])) $_SESSION["session_account"]=$session_account;
if (!isset($_SESSION["session_repID"])) $_SESSION["session_repID"]=$session_repID;

if($_POST['operationtype'] == null && $_POST['ordernumber']!= null){
	retrivePdfForOrder($_POST['ordernumber']);
}

if($_POST['operationtype'] == 'add'){
	echo "in upload add";
	exit;
	if (!empty($_FILES)) {
		//call addpdf
		$file_name = addpdf($_POST['ordernumber']);
		$tempFile = $_FILES['file']['tmp_name'];
		$targetPath = dirname(__FILE__) . '/' . $targetFolder;
		$targetFile = rtrim($targetPath,'/') . '/' . $file_name;//$_FILES['file']['name'];
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
}

if($_POST['operationtype'] == 'view'){
	//retrive file path from data base and set the path
	//query form the database
	$result = mysqli_query($con,getRetriveQuery($_POST['ordernumber']));
	while($row = mysqli_fetch_array($result)) {
		$filePath = $row['url'];
		$file = $root.$filePath;
		downloadFile($file);
	}
	
}
if($_POST['operationtype'] == 'delete'){
	$fileName = $root."/PHPMySQLFileUPload/".$targetFolder."/";
	//rename the file
	renameFile($fileName,$root."/FileUploadUpdate/storpdfs/");
	//delete the entry from the database
	
}


//insert when clicked on add
function addpdf($orderNum){
	$retInfo = retrivePdfForOrder($orderNum);
	$file_name;
	if($retInfo == null) {
		$file_name = $_SESSION["session_account"]."_".$orderNum."_001.pdf";
	} else {
		$file_name = $_SESSION["session_account"]."_".$orderNum . (substr($retInfo->url,-6,3) + 1). ".pdf";
	}
	global $con;
	$file_name = $_SESSION["session_account"]."_".$orderNum;
	$result = mysqli_query($con,getInsertQuery($_SESSION["session_account"],
											   $orderNum,
											   $root."/PHPMySQLFileUPload/".$targetFolder."/".$file_name,
											   $_SESSION["session_repID"],
											   $_SESSION["session_repID"]
												));
	
	return $file_name;
}

//onload function
function retrivePdfForOrder($paramSent){
	//query form the database
	global $con;
	$result = mysqli_query($con,getRetriveQuery($paramSent));
	$row = mysqli_fetch_array($result);
	echo json_encode($row);
	return $row;
}


//view pdf
//queryfrom the database the file path by using order number and passing the file path 
function downloadFile($file) { // $file = include path 
        if(file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
			//added single quotes to file name
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
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
//delete pdf
//rename file when delete and make the column "file path" value to null and enable add button
function renameFile($fileName){
	rename($fileName,$root."/FileUploadUpdate/uploads/_obsolete.pdf");
	//modify database column for that row.
}
?>