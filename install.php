<?php
	//install script
	
	$errLevel = error_reporting(0);
	
	//Check if directory already exists & create if not.
	$successRoot = true;
	if(!file_exists('articles'))
	{
		$successRoot = mkdir('articles');
	}
	
	if(!file_exists('drafts'))
	{
		$successRoot = mkdir('drafts') && $successRoot;
	}
	
	if(!file_exists('pages'))
	{
 		$successRoot = mkdir('pages') && $successRoot;
 	}
 	
 	if(!file_exists('files'))
 	{
 		$successRoot = mkdir('files') && $successRoot;
 	}
 	
 	//create _settings.php file in controlPanel/
 	$successCP = true;
 	if(!file_exists('controlPanel/_settings.php'))
 	{
 		$successCP = file_put_contents('controlPanel/_settings.php', '<?php

	/*
		You can use this setting to set the template used by your site.
		
		Templates must be added to the templates directory in the root of
		your site and any static links should be relative to the root of
		your site, not the templates directory, for more information about
		templates, vist:
			http://subdimension.co.uk/pages/staticDimension_templates.html
			
		The value of this should be the name of the directory where the
		template is stored (with no slashes).
		
		Once you have changed this setting, you will need to rebuild your
		site using the rebuild link in the control panel [settings].
	*/
	$this->settings[\'currentTemplate\'] = \'default\';
	
	/* 
		These 2 settings are used when generating an RSS feed.
		
		feedTitle is the name you want to appear in feed readers
		that subscribe to your feed.
		
		siteRoot is the root of your site, including any subfolders
		(include the trailing slash).
	*/
	$this->settings[\'feedTitle\'] = \'example.com\';
	$this->settings[\'siteRoot\'] = \'http://www.example.com/mysite/\';
	
	/*
		This setting allows the server to rebuild the website in the
		background - you will need to have php-cli installed on the
		server. This might be useful if you have a very large site
		that needs to be rebuilt regularly for some obscure reason!
		
		The doRebuild.cli.php script is responsible for building your
		site in the background.
		
		Change the setting to true to enable background rebuilding.
	*/
	$this->settings[\'backgroundRebuild\'] = false;
	
	/*
		This setting builds the blog homepage at blog.html instead of index.html
		allowing you to use a different static page for your site\'s homepage.
		
		Note, by default, staticDimension doesn\'t manage the separate homepage,
		but you could simply create a page to use as the homepage in the
		controlPanel and then make a symbolic link to that page at the site root.
		With the default template, you would also need to create a special template
		for that page (name it the same as the page you created) so the CSS links
		still work. See more about templates here 
			http://subdimension.co.uk/pages/staticDimension_templates.html
	*/
	$this->settings[\'blogNotHomepage\'] = false;
	
	/*
		Post Glyphs
		These settings allow you to include a glyph at the front of either link posts
		or long-form posts (or both, but I\'m not sure that makes sense).
		
		No space is inserted between a glyph and the post title, so if you want one, add
		it to the setting below.
	*/
	$this->settings[\'linkGlyph\'] = \'\';
	$this->settings[\'longFormGlyph\'] = \'\';

	/*
		Single-page Archive
		This setting enables a single-page archive instead of the default day-month-year
		folder archives. Single-page format lists all posts by title and byline (author, 
		date and time) in reverse order of posting.

		A value of 'true' enables single-page archives. Any other value currently uses
		the default archive format.
	*/
	$this->settings[\'singlePageArchive\'] = false; 
?>');

 	}
 	
 	if(!file_exists('controlPanel/_users.php'))
 	{
 		$successCP = file_put_contents('controlPanel/_users.php', '<?php
	/*
		You can create multiple users to allow different people
		to post articles and create pages.
		
		Simply copy and paste the code below and edit the details.
		
		For example, to create an account for Mike Watts, with the
		user name mwatts and password of password, you would add 
		the following code:
		
			$users[\'mwatts\'] = array
			(
				\'name\' => \'Mike Watts\',
				\'password\' => \'5f4dcc3b5aa765d61d8327deb882cf99\',
			};
			
		Passwords are stored as an md5 hash - the control panel has
		a tool that helps you generate new passwords.
	*/
	$users[\'Admin\'] = array 
	(
		\'name\' => \'Administrator\',
		\'password\' => \'5f4dcc3b5aa765d61d8327deb882cf99\',
	);
?>') && $successCP;

	}
	
 	error_reporting($errLevel);
 	
 	
 	
 	if(! ($successRoot && $successCP) )
 	{
 		$html = '<h2>Error installing</h2>';
 		
 		$html .= '<p>It has not been possible to complete the installation.</p>';
 		
 		if (!$successRoot)
 		{
 			$html .= '<p>Installation failed at step 1 - if you have already run install.php, try logging into the <a href="controlPanel">control panel</a>. If this is the first time you have run install.php it is likely that the php user does not have write access to the root directory. Depending on your server configuration and the level of access you have to your server, there are a number of different ways to grant write access to the user that php runs as. Please consult your server admin &amp; documentation for the best way to do this. If you get <em>really</em> stuck, <a href="http://subdimension.co.uk/pages/about_me.html">get in contact</a> and I will help where I can.</p>';
 		}
 		else if (!$successCP)
 		{
 			$html .= '<p>Installation failed at step 2 - if you have already run install.php, try logging into the <a href="controlPanel">control panel</a>. If this is the first time you have run install.php it is likely that the php user does not have write access to the controlPanel directory. Depending on your server configuration and the level of access you have to your server, there are a number of different ways to grant write access to the user that php runs as. Please consult your server admin &amp; documentation for the best way to do this. If you get <em>really</em> stuck, <a href="http://subdimension.co.uk/pages/about_me.html">get in contact</a> and I will help where I can.</p>';
 		}
 	}
 	else
 	{	
 		require_once('controlPanel/classes/StaticDimension.class.php');
 		
 		chdir('controlPanel');
 		$sd = new staticDimension();
 		
 		$success = file_put_contents('../pages/about.txt', "#about\n\nThis website has been created with [staticDimension](http://www.subdimension.co.uk/pages/projects.html).");
 		
 		$sd->rebuildSite();
 		
 		$html = '<h2>Installation successful</h2>';
 		$html .= '<p>staticDimension has successfully been installed on your server.</p>';
 		$html .= '<p>If you have not done so already, you should change the default password by editing the _users.php file in the controlPanel. You should also make sure that you have entered the correct settings in the _settings.php file.</p>';
 		$html .= '<p>It is strongly recommended that you delete the install.php file from your server once you have completed the installation</p>';
 		$html .= '<p>To begin publishing content, log into the <a href="controlPanel/">control panel</a></p>';
 		$html .= '<p>Thank you for using staticDimension</p>';
 	}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="stylesheet" type="text/css" href="templates/default/style/default.css">

<title>install</title>

</head>

<body>

<header>
	<h1>staticDimension - installation</h1>
	<nav>
		<ul>
			<li><a class="selected" href="./">[home]</a></li>
			<li><a class="selected" href="controlPanel/">[control panel]</a></li>
		</ul>
	</nav>
</header>

<section id="page_content">
	<?php echo $html ?>
</section>

<footer>copyright 2011</footer>

</body>
</html>