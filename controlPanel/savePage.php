<?php
	require_once('classes/StaticDimension.class.php');
	session_start();
	
	if(!isset($_SESSION['user']))
	{
		header('Location: ./'); exit;
	}
	
	$sd = new staticDimension();
	
	if(isset($_POST['page']))
	{
		//strip off any naughty hackzorz appending paths
		$title = $sd->stripPath(stripslashes($_POST['title']));
		$fileTitle = $sd->stripPath($_POST['page']);
		//need to check if title changed - delete old one then create new
		/*$title_ = str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $title));
		
		if($title_ != substr($fileTitle, 0, strpos($fileTitle, '.')))
		{
			//title has changed, just create a new page
			$url = $sd->createPage(stripslashes($_POST['text']), $title);
			//delete the old one
			$sd->deletePage($fileTitle);
		}
		else
		{*/
			$url = $sd->updatePage(stripslashes($_POST['text']), $fileTitle, $title, isset($_POST['rename']));
		//}
	}
	else
	{
		//strip off any naughty hackzorz appending paths
		$title = $sd->stripPath(stripslashes($_POST['title']));
		
		$url = $sd->createPage(stripslashes($_POST['text']), $title);
	}
	
	header('Location: ../'.$url);
?>