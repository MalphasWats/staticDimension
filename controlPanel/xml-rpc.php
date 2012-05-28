<?php
	require_once('classes/StaticDimension.class.php');
	session_start();
	
	$sd = new staticDimension();
	
	if(isset($_GET['rsd']))
	{
		echo '<?xml version="1.0" encoding="UTF-8"?><rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd">
  <service>
    <engineName>staticDimension</engineName>
    <engineLink>http://subdimension.co.uk/pages/projects.html</engineLink>
    <homePageLink>'.$sd->settings['siteRoot'].'</homePageLink>
    <apis>
      <api name="MetaWeblog" blogID="1" preferred="true" apiLink="'.$sd->settings['siteRoot'].'controlPanel/xml-rpc.php" />
    </apis>
  </service>
</rsd>';
		exit;
	}
	
	$request = file_get_contents('php://input');
	
	$xml = new SimpleXMLElement($request);
	
	if((string)$xml->methodName == 'metaWeblog.newPost' || (string)$xml->methodName == 'metaWeblog.editPost')
	{
		$username = (string)$xml->params->param[1]->value->string;
		$password = (string)$xml->params->param[2]->value->string;
		checkLogin($username, $password);
		
		$postID = (string)$xml->params->param[0]->value->string;
	
		foreach($xml->params->param[3]->value->struct->member as $m)
		{
			if ($m->name == 'title')
			{
				$title = (string)$m->value->string;
			}
			elseif($m->name == 'description')
			{
				$text = (string)$m->value->string;
			}
			elseif($m->name == 'link')
			{
				$link = (string)$m->value->string;
			}
			elseif($m->name == 'categories')
			{
				foreach($m->value->array->data as $d)
				{
					$categories[] = (string)$d->value->string;
				}
			}
		}
		if ($text != '' && $title !='')
		{
			if($categories[0] == 'page')
			{
				if($postID != '1')
				{
					$postID = substr($postID, 6);
					$postID = $sd->updatePage($text, $postID, $title, false);
				}
				else
				{
					$postID = $sd->createPage($text, $title);
					$postID = substr($postID, 0, strrpos($postID, '.html')).'.txt';
				}
			}
			else
			{
				if($postID != '1')
				{
					$postURL = $sd->updateArticle($text, $postID, $title, $link, false);
				}
				else
				{
					$postURL = $sd->createArticle($text, $title, $link);
				}
			
				$postID = $sd->getArticleTextFilenameFromArticleURL($postURL);
				$sd->buildHomepage();
			}
		
			$response = '<?xml version="1.0"?>
<methodResponse>
	<params>
	<param>
		<value><string>'.$postID.'</string></value>
	</param>
	</params>
</methodResponse>';
		}
		else
		{
			$response = $response = '<?xml version="1.0"?>
<methodResponse>
	<fault>
		<value>
			<struct>
			<member>
				<name>faultCode</name>
				<value><int>2</int></value>
			</member>
			<member>
				<name>faultString</name>
				<value><string>Title or post content not supplied</string></value>
			</member>
			</struct>
		</value>
	</fault>
</methodResponse>';
		}
	}
	elseif ((string)$xml->methodName == 'metaWeblog.getPost')
	{
		$username = (string)$xml->params->param[1]->value->string;
		$password = (string)$xml->params->param[2]->value->string;
		checkLogin($username, $password);
		
		$postID = (string)$xml->params->param[0]->value->string;
		
		if(substr($postID, 0, 6) == 'pages/')
		{
			$post = $sd->getPageElements(substr($postID, 6));
			$category = '<member>
			<name>categories</name>
			<value>
				<array>
					<data>
						<value>page</value>
					</data>
				</array>
			</value>
			</member>';
		}
		else
		{
			$post = $sd->getArticleElements($postID);
			$category = '';
		}
		$date = gmdate('Ymd', $post['timestamp']).'T'.gmdate('H:i:s', $post['timestamp']) . 'Z';
		$url = $sd->settings['siteRoot'] . $post['url'];
		
		$response = '<?xml version="1.0"?>
<methodResponse>
	<params>
	<param>
		<value>
		<struct>
		'.$category.'
		<member>
			<name>dateCreated</name>
			<value>
				<dateTime.iso8601>'.$date.'</dateTime.iso8601>
				</value>
			</member>
		<member>
			<name>description</name>
			<value>'.htmlspecialchars($post['text']).'</value>
		</member>
		<member>
			<name>link</name>
			<value>'.$post['link'].'</value>
			</member>
		<member>
			<name>permaLink</name>
			<value>'.$url.'</value>
			</member>
		<member>
			<name>postid</name>
			<value>
				<string>'.$postID.'</string>
			</value>
			</member>
		<member>
			<name>title</name>
			<value>'.htmlspecialchars($post['title']).'</value>
		</member>
		</struct>
		</value>
	</param>
	</params>
</methodResponse>';
		
	}
	elseif ((string)$xml->methodName == 'metaWeblog.newMediaObject')
	{
		$username = (string)$xml->params->param[1]->value->string;
		$password = (string)$xml->params->param[2]->value->string;
		checkLogin($username, $password);
		
		foreach($xml->params->param[3]->value->struct->member as $m)
		{
			if ($m->name == 'name')
			{
				$name = (string)$m->value->string;
				if(substr($name, 0, 1) == '/') {$name = substr($name, 1);}
				//I'm very strange and I really hate file extensions in capitals
				$dot = strrpos($name, '.');
				$ext = strtolower(substr($name, $dot));
				$name = substr($name, 0, $dot) . $ext;
			}
			elseif($m->name == 'bits')
			{
				$bits = base64_decode($m->value->base64);
			}
			if(!file_exists('../files/mediaUpload')) {mkdir('../files/mediaUpload');}
			if(file_put_contents("../files/mediaUpload/$name", $bits))
			{			
				$response = '<?xml version="1.0"?>
<methodResponse>
	<params>
	<param>
		<value>
		<struct>
		<member>
			<name>url</name>
			<value>
				<string>'.$sd->settings['siteRoot'].'files/mediaUpload/'.$name.'</string>
			</value>
		</member>
		</struct>
		</value>
	</param>
	</params>
</methodResponse>';
			}
			else
			{
				$response = '<?xml version="1.0"?>
<methodResponse>
	<fault>
		<value>
			<struct>
			<member>
				<name>faultCode</name>
				<value><int>5</int></value>
			</member>
			<member>
				<name>faultString</name>
				<value><string>error saving file</string></value>
			</member>
			</struct>
		</value>
	</fault>
</methodResponse>';
			}
		}
	}
	elseif ((string)$xml->methodName == 'blogger.deletePost')
	{
		$username = (string)$xml->params->param[2]->value->string;
		$password = (string)$xml->params->param[3]->value->string;
		checkLogin($username, $password);
		
		$postID = (string)$xml->params->param[1]->value->string;
		
		if(substr($postID, 0, 6) == 'pages/')
		{
			$postID = substr($postID, 6);
			$sd->deletePage($postID);
		}
		else
		{
			$sd->deleteArticle($postID);
			$sd->buildHomepage();
		}
		
		$response = '<?xml version="1.0"?>
<methodResponse>
	<params>
	<param>
		<value><boolean>1</boolean></value>
	</param>
	</params>
</methodResponse>';
	}
	elseif ((string)$xml->methodName == 'metaWeblog.getRecentPosts')
	{
		$username = (string)$xml->params->param[1]->value->string;
		$password = (string)$xml->params->param[2]->value->string;
		checkLogin($username, $password);
		
		$numPosts = (string)$xml->params->param[3]->value->int;
		
		$posts = $sd->getRecentArticles($numPosts);
		$pages = $sd->getPages();
		
		$response = '<?xml version="1.0" encoding="UTF-8"?>
<methodResponse>
  <params>
    <param><value><array><data>';
    	foreach($posts as $p)
    	{
    		$url = $sd->settings['siteRoot'] . $p['url'];
    		$date = gmdate('Ymd', $p['timestamp']).'T'.gmdate('H:i:s', $p['timestamp']) . 'Z';

    		$response .= '
    <value>
      <struct>
        <member>
          <name>link</name>
          <value><string>'.$p['link'].'</string></value>
        </member>
        <member>
          <name>permaLink</name>
          <value><string>'.$url.'</string></value>
        </member>
        <member>
          <name>userid</name>
          <value><string>1</string></value>
        </member>
        <member>
          <name>description</name>
          <value><string>'.htmlspecialchars($p['text']).'</string></value>
        </member>
        <member>
          <name>postid</name>
          <value><string>'.$p['filename'].'</string></value>
        </member>
        <member>
          <name>title</name>
          <value><string>'.htmlspecialchars($p['title']).'</string></value>
        </member>
        <member>
          <name>dateCreated</name>
          <value><dateTime.iso8601>'.$date.'</dateTime.iso8601></value>
        </member>
      </struct></value>';
      }
      foreach($pages as $p)
    	{
    		$url = $sd->settings['siteRoot'] . $p['url'];
    		$date = gmdate('Ymd', $p['timestamp']).'T'.gmdate('H:i:s', $p['timestamp']) . 'Z';

    		$response .= '
    <value>
      <struct>
     	 <member>
			<name>categories</name>
			<value>
				<array>
					<data>
						<value>page</value>
					</data>
				</array>
			</value>
		</member>
        <member>
          <name>link</name>
          <value><string>'.$p['link'].'</string></value>
        </member>
        <member>
          <name>permaLink</name>
          <value><string>'.$url.'</string></value>
        </member>
        <member>
          <name>userid</name>
          <value><string>1</string></value>
        </member>
        <member>
          <name>description</name>
          <value><string>'.htmlspecialchars($p['text']).'</string></value>
        </member>
        <member>
          <name>postid</name>
          <value><string>'.$p['filename'].'</string></value>
        </member>
        <member>
          <name>title</name>
          <value><string>'.htmlspecialchars($p['title']).'</string></value>
        </member>
        <member>
          <name>dateCreated</name>
          <value><dateTime.iso8601>'.$date.'</dateTime.iso8601></value>
        </member>
      </struct></value>';
      }
      $response .= '</data></array></value>
    </param>
  </params>
</methodResponse>';
	}
	elseif ((string)$xml->methodName == 'metaWeblog.getCategories')
	{
		$response .= '<?xml version="1.0"?>
<methodResponse> 
	<params>
	<param>
	<value>
		<array>
		<data>
		<value>
			<struct>
			<member>
				<name>description</name>
				<value>page</value>
			</member>
			<member>
				<name>title</name>
				<value>page</value>
			</member>
			</struct>
		</value>
		</data>	
		</array>
	</value>
	</param>
	</params>
</methodResponse>';
	}
	else
	{
		$response = '<?xml version="1.0"?>
<methodResponse>
	<fault>
		<value>
			<struct>
			<member>
				<name>faultCode</name>
				<value><int>1</int></value>
			</member>
			<member>
				<name>faultString</name>
				<value><string>Unsupported operation</string></value>
			</member>
			</struct>
		</value>
	</fault>
</methodResponse>';
	}
	
	header("Connection: close");
	header("Content-Length: ".strlen($response));
	header("Content-Type: text/xml");
	header("Date: " . date("r"));
	echo $response;
	
	session_destroy();
	
	exit;
	
	function checkLogin($username, $password)
	{
		//check login
		include '_users.php';
	
		if($users[$username]['password'] != md5($password))
		{
			$response = '<?xml version="1.0"?>
<methodResponse>
	<fault>
		<value>
			<struct>
			<member>
				<name>faultCode</name>
				<value><int>0</int></value>
			</member>
			<member>
				<name>faultString</name>
				<value><string>invalid username or password</string></value>
			</member>
			</struct>
		</value>
	</fault>
</methodResponse>';
			header("Connection: close");
			header("Content-Length: ".strlen($response));
			header("Content-Type: text/xml");
			header("Date: " . date("r"));
			echo $response;
			exit;
		}
		else 
		{
			$_SESSION['user'] = $users[$username];
			return true;
		}
	}

?>