<?php
// check if the MS Access database file exists
// (looks in the same directory)
$dbName = getcwd() . "\ImageUploader.accdb";
if (!file_exists($dbName)) {
	die("Could not find the database file.");
}
// connect to the MS Access database using PDO ODBC
$db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb, *.accdb)}; DBQ=$dbName; Uid=; Pwd=;");

// get base64 string and filename from POST
$filename = $_POST['filename'];
$base64 = explode(',', $_POST['base64'])[1];

// save path of file
$save_path = 'uploads/' . $filename;

// check if the file has already been uploaded
if (file_exists($save_path)) {
	echo 'Error: File already exists. ';

// if not, then proceed to save file to disk and base64 string to database table
} else {
	// insert into database using a prepared statement
	$insert_query = "insert into Images (filename, base64) values (:filename, :base64);";
	$preparedquery = $db->prepare($insert_query);
	$preparedquery->execute(array('filename' => $filename, 'base64' => $base64));

	$result = $db->query("select filename from Images;");
	$rows = $result->fetchAll();
	print_r($rows);

	// decode base64 and convert string to image
	$decodedBase64 = base64_decode($base64);
	$base64Image = imagecreatefromstring($decodedBase64);

	// save image to disk
	if ($imageFile = imagejpeg($base64Image, $save_path, 100) == true) {
		echo 'Success.';
	} else {
		echo 'Failure.';
	}
}
?>