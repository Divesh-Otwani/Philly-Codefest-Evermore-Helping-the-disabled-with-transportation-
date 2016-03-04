<?php
/*==========================================================================*\
Filename : index.php - main driver for my.ionfish.org
Project  : Evermore
Authors  : --
Revision : 20160220
License  : The MIT License (MIT)

Copyright (c) 2016 --

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
\*==========================================================================*/

//----------------------------------------------------------------------------
// core system

require('config.php');                   // hard-coded configuration


function close($row){
	// Thanks, http://andrew.hedges.name/experiments/haversine/
	$dlat = intval($row['lat']) - intval($_GET['lat']);
	$dlng = intval($row['lng']) - intval($_GET['lng']);
	$a = pow(sin($dlat/2),2) + cos($_GET['lat'])*cos($row['lat']) * pow(sin($dlng/2), 2);
	$c = 2*atan2(sqrt($a), sqrt(1 - $a));
	return (3961 * $c) <= 1;
}

if(isset($_POST) && !empty($_POST) && isset($_GET['add']))
{
	$target_dir = "test/images/";
	//error_log(serialize($_FILES));
	$target_file = $target_dir . basename($_FILES["file"]["name"]);
	$uploadOk = 1;
	$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

	$query = $db->prepare("INSERT INTO `obstacles`
		(`timestamp`, `lat`, `lng`, `description`, `vote`, `active`)
		VALUES (?,?,?,?,?,?);");
	$query->execute(array(
		time(),
		$_POST['lat'],
		$_POST['lng'],
		$_POST['description'],
		0,
		1
	));

	$id = $db->lastInsertId();
	
	// fails to inset to db?
	if($id == 0)
	{
		exit();
	}
	
	// directory is new
	$old = umask(0);
	$newpath = dirname(__FILE__) . "/" . basename($target_dir)."/$id";
	if(!@mkdir("$newpath", 0777))
	{
		$name = basename($_FILES["file"]["name"]);
	}
	else{
		$name = '0.jpg';
	}
	umask($old);
	
	$newname = $newpath.'/'.$name;
	//echo "moving from {$_FILES["file"]["tmp_name"]} to $newname<br />";
	
	if (move_uploaded_file($_FILES["file"]["tmp_name"], $newname)) {

		//echo "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
	} else {
		//echo "Sorry, there was an error uploading your file.";
	}
	header('Location: ../');
}
elseif(isset($_POST) && !empty($_POST) && isset($_GET['vote']))
{
	if($_POST['vote'] == '-2')
	{
		$query = $db->prepare("UPDATE `obstacles` SET `active` = 0 WHERE `id` = ?;");
		$query->execute(array($_POST['id']));
	}
	else{
		$query = $db->prepare("UPDATE `obstacles` SET `vote` = ? WHERE `id` = ?;");
		$query->execute(array($_POST['vote'], $_POST['id']));
	}
}
else{
	$query = $db->prepare("SELECT * FROM `obstacles` WHERE active = 1;");
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);

	if(isset($_GET['lat'])){
		$res = array_values(array_filter($result, "close"));
		echo json_encode($res);
	}else{
		echo json_encode($result);
	}
}
