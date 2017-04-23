<!DOCTYPE html>
<html lang="zh">
<head>
<title>Vinee</title>
<link rel="icon" href="img/winee-logo-1.png">
<link href="font/Roboto/css/fonts.css" rel="stylesheet">
<link href="font/Roboto-Slab/css/fonts.css" rel="stylesheet">
<link href="font/Source-Sans-Pro/css/fonts.css" rel="stylesheet">
</head>

<body style="font-family: PingFangSC-Regular, sans-serif;">
<?php

function db_connect($is_write=false) {
	var_dump("***************************");
	print("is_write: ");
	var_dump($is_write);
	if ($is_write){
		$servername = "54.223.152.54";
	} else {
		$servername = "localhost";
	}
	$username = "root";
	$password = "TagTalk78388!";
	$dbname = "wineTage1";
	// Create connection
	$conn = new \mysqli($servername, $username, $password, $dbname);
	$conn->set_charset("utf8");
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	return $conn;
}

?>
  <div style="margin-bottom:20px;font-size:18px;font-weight:bold;color:#27ae60">
    <img src="img/winee-logo-1.png" style="vertical-align:middle"height="150px" width="150px" border="0" alt="Vinee">
    <h1> V I N E E </h1>
  </div>
  <div style="font-size:18px;">Tag ID = <b><?php echo $_GET["q"] ?></b></div>
</body>
</html>
