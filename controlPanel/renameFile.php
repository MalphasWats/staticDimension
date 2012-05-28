<?php
	require_once('classes/FileManager.class.php');
	require_once('classes/StaticDimension.class.php');
	
	session_start();
	//logged in?
	$user = $_SESSION['user'];
	if(!isset($user))
	{
		header('Location: ./'); exit;
	}
	
	$fm = new FileManager();
	
	if(isset($_POST['targetDir'])) 
	{
		$od = $_POST['dir'];
		$nd = $_POST['targetDir'];
		$f = $_POST['filename'];
		
		$ext = strtolower(substr($f, strrpos($f, '.')+1));
		
		$source = "../files/$od$f";
		$dest = "../files/$od$nd/$f";
		
		$d = $od;
	}
	else
	{
		$sd = new StaticDimension();
		$of = $sd->stripPath($_POST['oldname']);
		$nf = $sd->stripPath($_POST['name']);
		$d = $_POST['dir'];
		
		
	
		$ext = strtolower(substr($nf, strrpos($nf, '.')+1));
	
		$source = "../files/$d$of";
		$dest = "../files/$d$nf";
	}
	
	if($fm->checkFileType($ext)) { rename($source, $dest); }
	
	header("Location: ./?file&dir=$d");
?>