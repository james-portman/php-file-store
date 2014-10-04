<?php

if (isset($_REQUEST['dl']) && $_REQUEST['dl'] != "") {

	if (!file_exists("files/".$_REQUEST['dl'])) {
		echo "Requested file does not exist.\n";
		exit;
	}

	$parts = explode("-",$_REQUEST['dl'],2);
	$origName = base64_decode($parts[1]);

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$origName);
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize("files/".$_REQUEST['dl']));
	readfile("files/".$_REQUEST['dl']);

	exit;
}




$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);
$uploadMaxSize = 104857600;

if (isset($_FILES['file'])) {

	if ($_FILES["file"]["error"] > 0) {
		echo "Error: ".$_FILES["file"]["error"]."\n";
		exit;
	}

	$newname = time()."-".base64_encode($_FILES["file"]["name"]);

	if ($_FILES["file"]["size"] < $uploadMaxSize) {

		if (file_exists("files/".$newname)) {
			echo "This is almost impossible - random file name already exists\n";
		} else {
			if ( move_uploaded_file($_FILES["file"]["tmp_name"],"files/".$newname) ) {

				if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"curl") === 0) {
					echo "Stored OK\n";
					echo $_FILES["file"]["size"]." bytes\n";
					echo "Link: http://".$_SERVER['HTTP_HOST']."/?dl=".urlencode($newname)."\n";
				} else {
					?><html>
<head>
<meta name="viewport" content="initial-scale=1.0,user-scalable=yes"/>
</head>
<body>
Stored OK<br/>
<?=$_FILES["file"]["size"]?> bytes<br/>
Link: <a href="http://<?=$_SERVER['HTTP_HOST']?>/?dl=<?=urlencode($newname)?>">http://<?=$_SERVER['HTTP_HOST']?>/?dl=<?=urlencode($newname)?></a><br/>

</body>
</html>
<?php

				}

			} else {
				echo "Failed to store the file, sorry!\n";
			}
		}

	} else {
		echo "Invalid file\n";
		// delete the file?
		exit;
	}

	exit;

}


if (empty($_REQUEST)){

	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"curl") === 0) {
		?>Usage:
curl -F "file=@backup.tar.gz" http://<?=$_SERVER['HTTP_HOST']?>
<?php
		exit;
	}
?>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0,user-scalable=yes"/>
</head>
<body>
Usage: curl -F "file=@backup.tar.gz" http://<?=$_SERVER['HTTP_HOST']?><br/>
<br/>
100MB file size limit<br/>
5 day retention - cleared hourly<br/>
Current total usage <?php print file_get_contents("usage"); ?><br/>
<br/>
<br/>
Here's a form in case you can't Linux good and stuff<br/>
<form action="/" method="post"
enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file"><br>
<input type="submit" name="submit" value="Submit">
</form>

</body>
</html>
<?php
	exit;
}

