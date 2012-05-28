#!/usr/bin/php -q
<?php
	require_once('classes/StaticDimension.class.php');
	
	$sd = new staticDimension();
	echo 'rebuilding site...';
	$sd->rebuildSite();
?>