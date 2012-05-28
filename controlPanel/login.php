<?php
	session_start();
	include '_users.php';
	
	if($users[$_POST['username']]['password'] == md5($_POST['password']))
	{
		$_SESSION['user'] = $users[$_POST['username']];
	}
	header('Location: '.$_SERVER['HTTP_REFERER']);
?>