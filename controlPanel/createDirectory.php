<?php
	session_start();
	//logged in?
	$user = $_SESSION['user'];
	if(!isset($user))
	{
		header('Location: ./'); exit;
	}
	
	$name = $_POST['name'];
	if($_POST['dir'] != ''){ $d = $_POST['dir']; }
	
	mkdir("../files/$d$name");

	header("Location: ./?file&dir=$d$name/");
?>