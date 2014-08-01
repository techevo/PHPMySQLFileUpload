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
	if (!empty($_FILES)) {
		//call addpdf
		$file_name = addpdf($_POST['ordernumber']);
		$tempFile = $_FILES['file']['tmp_name'];
		$targetPath = dirname(__FILE__) . '/' . $targetFolder;
		$targetFile = rtrim($targetPath,'/') . '/' . $file_name;//$_FILES['file']['name'];
		// Validate the file type
		$fileTypes = array('pdf','PDF'); // File extensions
		$fileParts = pathinfo($_FILES['file']['name']);
		if (in_array($fileParts['extension'],$fileTypes)) {
			//print_r($tempFile);
			//print_r($targetFile);
			move_uploaded_file($tempFile,$targetFile);
			echo '1';
		} else {
			echo 'Invalid file type.';
		}
		
	}
}

if($_POST['operationtype'] == 'change'){
	if (!empty($_FILES)) {
		//call addpdf
		$file_name = changepdf($_POST['ordernumber']);
		if($file_name != "0") {
			$tempFile = $_FILES['file']['tmp_name'];
			$targetPath = dirname(__FILE__) . '/' . $targetFolder;
			$targetFile = rtrim($targetPath,'/') . '/' . $file_name;//$_FILES['file']['name'];
			// Validate the file type
			$fileTypes = array('pdf','PDF'); // File extensions
			$fileParts = pathinfo($_FILES['file']['name']);
			if (in_array($fileParts['extension'],$fileTypes)) {
				//print_r($tempFile);
				//print_r($targetFile);
				move_uploaded_file($tempFile,$targetFile);
				echo '1';
			} else {
				echo 'Invalid file type.';
			}
		} else {
			echo 'old file not found';
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
		echo $file;
		downloadFile($file);
		/*if(downloadFile($file) == "1") {
			echo $file;
		} else {
			echo '0';
		}*/
	}
	mysqli_close($con);
	

}
if($_POST['operationtype'] == 'delete'){
	//first retrieve the file from the url. query form the database
	$fileName = '';
	$result = mysqli_query($con,getRetriveQuery($_POST['ordernumber']));
	while($row = mysqli_fetch_array($result)) {
		$filePath = $row['url'];
		$fileName = $root.$filePath;
	}
	if($fileName != ''){
	//rename the file
	$renameSuccess = renameFile($fileName,$root.$basepath);
	}
	//delete the entry from the database
	$result = mysqli_query($con,getDeleteQuery($_POST['ordernumber']));
	if($renameSuccess == "1") {
		echo 'done';
	} else {
		echo $renameSuccess;
	}
	mysqli_close($con);
}


//insert when clicked on add
function addpdf($orderNum){
	$retInfo = retrivePdfForOrderAddChange($orderNum);
	$file_name;
	$file_name = $_SESSION["session_account"]."_".$orderNum.".pdf";
	global $con;
	global $root;
	global $basepath;
	$query = getInsertQuery($_SESSION["session_account"],
											   $orderNum,
											   $basepath.$file_name,
											   $_SESSION["session_repID"],
											   $_SESSION["session_repID"]);
	mysqli_query($con,$query);
	if(mysqli_affected_rows($con)==1){
        $iid = mysqli_insert_id($con);
    } else {
        echo 'insertion failed' . mysqli_error($dbc);
    }
	mysqli_close($con);
	return $file_name;
}

//update when clicked on change
function changepdf($orderNum){
	$retInfo = retrivePdfForOrderAddChange($orderNum);
	$file_name;
	$file_name = $_SESSION["session_account"]."_".$orderNum.".pdf";
	$renameSuccess = renameFile($basepath.$file_name);
	if($renameSuccess =="1") {
		global $con;
		global $root;
		global $basepath;
		$query = getUpdateQuery($_SESSION["session_account"],
												   $root.$basepath.$file_name,
												   $_SESSION["session_repID"]);
		mysqli_query($con,$query);
		if(mysqli_affected_rows($con)==1){
			$iid = mysqli_insert_id($con);
		} else {
			echo 'insertion failed' . mysqli_error($dbc);
		}
		mysqli_close($con);
		return $file_name;
	} else {
		return "0";
	}
}  

//onload function
function retrivePdfForOrder($paramSent){
	//query form the database
	global $con;
	$result = mysqli_query($con,getRetriveQuery($paramSent));
	$row = mysqli_fetch_array($result);
	echo json_encode($row);
	mysqli_close($con);
	return $row;
}
//called from add or change function
function retrivePdfForOrderAddChange($paramSent){
	//query form the database
	global $con;
	$result = mysqli_query($con,getRetriveQuery($paramSent));
	$row = mysqli_fetch_array($result);
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

//		return "1";
        }
		else{
	//		return "0";
			echo 'file does not exist';
		}
}
//delete pdf
//rename file when delete and make the column "file path" value to null and enable add button
function renameFile($fileName){
	try{
		$obsoleteFileName = substr($fileName, 0, -3)."_obsolete_001";
		$index = 1;
		while(true) {
			if(file_exists($obsoleteFileName."pdf")) {
				$index ++;
				$obsoleteFileName = substr($fileName, 0, -3)."_obsolete_".str_pad("$index", 3, "0", STR_PAD_LEFT);
				continue;
			} else {
				break;
			}
		}
		rename($fileName,$renameFile.$obsoleteFileName."pdf");
		return "1";
		
	}
	catch(Exception $e){
		return $e->getMessage();
	}
}
?>