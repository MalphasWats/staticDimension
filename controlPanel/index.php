<?php
	require_once('classes/StaticDimension.class.php');
	require_once('classes/FileManager.class.php');
	session_start();
	
	$sd = new staticDimension();
	
	if(isset($_GET['logout']))
	{
		unset($_SESSION['user']);
		header ('Location: ../'); exit;
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="style/staticDimension.css">

<script>
	function confirmDelete()
	{
		if(confirm('Please confirm delete.')){return true;}else{return false;}
	}
</script>

<title>staticDimension - control panel</title>

</head>

<body>

<header>
<h1>staticDimension - control panel</h1>
<nav>
	<ul>
		<li><a href="../">[site home]</a></li>
		<li><a href="./">[control panel]</a></li>
		<li><a href="?article">[new article]</a></li>
		<li><a href="?page">[new page]</a></li>
		<li><a href="?file">[manage files]</a></li>
		<li><a href="?settings">[settings]</a></li>
		<li><a href="?logout">[logout]</a></li>
	</ul>
</nav>
</header>

<section id="page_content">
<?php

	//logged in?
	$user = $_SESSION['user'];
	if(!isset($user))
	{
		echo '<h2>Log in</h2>
				<form method="post" action="login.php" id="login">
				<p><input type="text" name="username" value="" size="22" tabindex="1">
				<label for="username">username</label></p>

				<p><input type="password" name="password" value="" size="22" tabindex="2">
				<label for="password">password</label></p>
				<p><input name="submit" type="submit" id="submit" tabindex="4" value="login"></p>';
	}
	else if(isset($_GET['article']))
	{
		if (isset($_GET['delete']))
		{
			
			$sd->deleteArticle($_GET['article']);
			$sd->buildHomepage();
			echo '<h2>Deleted</h2><p>Article was deleted</p>';
		}
		else
		{
			echo '<form enctype="multipart/form-data" method="post" action="saveArticle.php" id="contentForm">';
			if($_GET['article'] != '')
			{
				echo '<h2>edit article</h2>';
				$article = $sd->getArticleElements($_GET['article']);
				echo '<input type="hidden" name="article" value="'.$_GET['article'].'">
				<p><input type="text" name="title" id="title" size="52" tabindex="1" value="'.$article['title'].'"><label for="title">title</label></p>
				<p><input type="text" name="link" id="link" size="52" tabindex="2" value="'.$article['link'].'"><label for="link">link</label></p>
				<p><textarea name="text" rows="25" cols="80" tabindex="2">'.$article['text'].'</textarea></p>
				<p>
					<input name="save" type="submit" tabindex="5" value="Save">
					<input name="rename" id="rename" type="checkbox" tabindex="4"> <label for="rename">rename file to title</label>
				</p>
				</form>';
			}
			else
			{
				echo '<h2>new article</h2>
				<p><input type="text" name="title" id="title" size="52" tabindex="1" value="">
				<label for="title">title</label></p>
				<p><input type="text" name="link" id="link" size="52" tabindex="2" value="">
				<label for="link">link</label></p>
				<p><textarea name="text" rows="25" cols="80" tabindex="3"></textarea></p>

				<p>
					<input name="save" type="submit" tabindex="5" value="Save">
					<input name="draft" id="draft" type="checkbox" tabindex="4"> <label for="draft">Save as draft</label>
				</p>
				</form>';
			
			}
		}
		
	}
	else if(isset($_GET['draft']))
	{
		if (isset($_GET['delete']))
		{
			$sd->deleteDraft($_GET['draft']);
			echo '<h2>Deleted</h2><p>Draft was deleted</p>';
		}
		else
		{
			echo '<form enctype="multipart/form-data" method="post" action="saveDraft.php" id="contentForm">';
			echo '<h2>edit draft</h2>';
			$article = $sd->getDraftElements($_GET['draft']);
			echo '<input type="hidden" name="article" value="'.$_GET['draft'].'">			<p><input type="text" name="title" id="title" size="52" tabindex="1" value="'.$article['title'].'"><label for="title">title</label></p>
			<p><input type="text" name="link" id="link" size="52" tabindex="2" value="'.$article['link'].'"><label for="link">link</label></p>
			<p><textarea name="text" rows="25" cols="80" tabindex="2">'.$article['text'].'</textarea></p>
			<p><input name="save" type="submit" tabindex="4" value="Save"> <input name="publish" id="publish" type="checkbox" tabindex="3"> <label for="draft">publish</label></p>
			</form>';
		}
	}
	else if(isset($_GET['page']))
	{
		if (isset($_GET['delete']))
		{
			$sd->deletePage($_GET['page']);
			echo '<h2>Deleted</h2><p>Page was deleted</p>';
		}
		else
		{
			if($_GET['page'] != '')
			{
				echo '<h2>new page</h2>';
				echo '<form enctype="multipart/form-data" method="post" action="savePage.php" id="contentForm">';
				echo '<input type="hidden" name="page" value="'.$_GET['page'].'">';
				$page = $sd->getPageElements($_GET['page']);
				echo '<p><input type="text" name="title" id="title" size="52" tabindex="1" value="'.$page['title'].'"><label for="title">title</label></p>
				<p><textarea name="text" rows="25" cols="80" tabindex="2">'.$page['text'].'</textarea></p>
				<p>
					<input name="save" type="submit" tabindex="5" value="Save">
					<input name="rename" id="rename" type="checkbox" tabindex="4"> <label for="rename">rename file to title</label>
				</p>';
				echo '</form>';
			}
			else
			{
				echo '<h2>new page</h2>';
				echo '<form enctype="multipart/form-data" method="post" action="savePage.php" id="contentForm">';
				echo '<p><input type="text" name="title" id="title" size="52" tabindex="1" value=""><label for="title">title</label></p>
				<p><textarea name="text" rows="25" cols="80" tabindex="2"></textarea></p>
				<p><input name="save" type="submit" tabindex="4" value="Save"></p>';
				echo '</form>';
			}
		}
	}
	else if(isset($_GET['file']))
	{
		$fm = new FileManager();
		
		echo '<section><h2>manage files</h2><p id="breadcrumb"><a href="./?file">files </a> ', $fm->buildDirectoryBreadcrumb($_GET['dir']), '</p>';
		
		if($_GET['file'] != '')
		{
			echo $fm->getFileInfoPanel($_GET['dir'], $_GET['file']);
		}
		else
		{
		
			echo $fm->getFileBrowser($_GET['dir']);
			
			echo '</section>';
		
			echo '<section id="sidebar"><h3>Functions</h3><h4>new folder</h4>
<form enctype="multipart/form-data" method="post" action="createDirectory.php" id="uploadForm">
<input type="hidden" name="dir" value="', $_GET['dir'], '">
<p><input type="text" id="name" name="name" size="12"> <label for="name">folder</label></p>
<p><input name="submit" type="submit" id="submit" tabindex="6" value="create"></p>
</form>

<h4>upload a file</h4>
<form enctype="multipart/form-data" method="post" action="uploadFile.php" id="uploadForm">
<input type="hidden" name="dir" value="', $_GET['dir'],  '">
<p><input type="file" id="uploadFile" name="uploadFile" size="15"></p>
<p><input name="submit" type="submit" id="submit" tabindex="6" value="upload"></p>	
</form></section>';
		}
	}
	else if(isset($_GET['settings']))
	{
		if(isset($_GET['rebuild']))
		{
			if($sd->settings['backgroundRebuild'])
			{
				exec('php doRebuild.cli.php &');
				
				echo '<h2>Rebuilding site</h2><p>This may take some time, you can leave this page whenever you want, it will happen in the background.</p>';
			}
			else
			{
				echo '<h2>Rebuilding site</h2><p>This may take some time for large sites. Please wait, do not close your browser or navigate away from this page...</p>';
				$sd->rebuildSite();
				echo '<p>Rebuild complete!</p>';
			}
		}
		else if(isset($_POST['password']))
		{
			echo '<h2>password</h2><p>', md5($_POST['password']), '</p>';
			
			echo '<p>Copy and paste this password into the correct section in the users.php file</p>';
		}
		else
		{
			echo '<section id="settings"><h2>users</h2>';
			include '_users.php';
			
			$usernames = array_keys($users);
			echo '<dl id="userList">';
			foreach($usernames as $u)
			{
				echo "<dt>username: $u</dt><dd>full name: {$users[$u]['name']}</dd>";
			}
			echo '</dl>
			<h2>generate password</h2>
			<p>users can be created by editing the users.php file in the controlPanel folder, passwords are stored as an md5 hash for security. You can use this tool to generate a new password</p>
			<form enctype="multipart/form-data" method="post" action="index.php?settings">
			<p><label for="password">name</label> <input type="text" id="password" name="password" size="20"> <input name="submit" type="submit" id="submit" value="generate"></p>
			</form>
			</section>';
		
			echo '<section id="sidebar"><nav><h3>functions</h3><ul><li><a href="?settings&amp;rebuild" rel="nofollow">rebuild site</a></li></ul></nav></section>';
		}
	}
	else
	{
		//control panel
		
		echo '<section>';
		echo '<h2>articles</h2>';
		echo $sd->getArticleList();
		echo '<h2>drafts</h2>';
		echo $sd->getDraftList();
		echo '<h2>pages</h2>';
		echo $sd->getPageList();
		echo '</section>';
	}
?>
</section>

</body>
</html>
