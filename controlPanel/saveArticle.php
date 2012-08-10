<?php
	require_once('classes/StaticDimension.class.php');
	session_start();
	
	if(!isset($_SESSION['user']))
	{
		header('Location: ./'); exit;
	}
	
	$sd = new staticDimension();
	
	$link = strip_tags(stripslashes($_POST['link']));
	if ($link != '' && substr($link, 0, 4) != 'http') {$link = "http://$link";}
	
	if(isset($_POST['article']))
	{
		//strip off any naughty hackzorz appending paths
		$title = $sd->stripPath(stripslashes($_POST['title']));
		
		$url = $sd->updateArticle(stripslashes($_POST['text']), $_POST['article'], $title, $link, isset($_POST['rename']));
	}
	else
	{
		//strip off any naughty hackzorz appending paths
		$title = $sd->stripPath(stripslashes($_POST['title']));
		
		if(isset($_POST['draft']))
		{
			$sd->createDraft(stripslashes($_POST['text']), $title, $link);
			$url = './controlPanel/';
		}
		else
		{
			$url = $sd->createArticle(stripslashes($_POST['text']), $title, $link);
		}
	}
	
	if(!$_POST['draft'])
	{
		//update homepage and archive
		$sd->updateSite();
	}
	
	header('Location: ../'.$url);
?>