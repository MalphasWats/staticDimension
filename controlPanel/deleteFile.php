<?php
	session_start();
	//logged in?
	$user = $_SESSION['user'];
	if(!isset($user))
	{
		header('Location: ./'); exit;
	}

	$d = $_GET['dir'];
	
	if ($_GET['filename'])
	{
		$f = $_GET['filename'];

		if($f != '' && strpos($filename, '/') == 0) { unlink("../files/$d$f"); }
	}
	else
	{
		rmdir("../files/$d");
		$d = substr($d, 0, strrpos($d, '/', -2));
		if(strlen($d) > 0) {$d .= '/';}
	}
	
	header("Location: ./?file&dir=$d");
?>