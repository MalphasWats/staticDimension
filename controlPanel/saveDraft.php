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
		$fileTitle = $sd->stripPath($_POST['article']);
		
		if(isset($_POST['publish']))
		{
		
			$url = $sd->createArticle(stripslashes($_POST['text']), $title, $link);
			//delete the old one
			$sd->deleteDraft($fileTitle);
			
			$sd->buildHomepage();
		}
		else
		{
			$sd->updateDraft(stripslashes($_POST['text']), $_POST['article'], $_POST['title'], $link);
			$url = './controlPanel/';
		}
	}
	
	header('Location: ../'.$url);
?>