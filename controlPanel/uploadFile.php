<?php
	require_once('classes/FileManager.class.php');
	
	session_start();
	//logged in?
	$user = $_SESSION['user'];
	if(!isset($user))
	{
		header('Location: ./');
	}
	
	$fm = new FileManager();
	
	$f = $_FILES['uploadFile']['name'];
	if($_POST['dir'] != ''){ $d = $_POST['dir'].'/'; }
	
	$name = substr($f, 0, strrpos($f, '.'));
	$ext = strtolower(substr($f, strrpos($f, '.')+1));
	
	if($fm->checkFileType($ext))
	{
		move_uploaded_file($_FILES['uploadFile']['tmp_name'], "../files/$d$f");
	}
	
	header("Location: ./?file&dir=$d");
?>