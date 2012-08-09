<?php
	/******************************
	*	Class staticDimension
	*
	******************************/
	require_once('markdown.class.php');
	require_once('RSSFeed.class.php');
	
	class StaticDimension
	{
		var $settings;
		
		
	
		/* Class Constructor
		
		*/
		function StaticDimension()
		{
			//construct
			
			//load settings from file
			include('_settings.php');
		}
		
		public function getArticleList()
		{
			$files = array_reverse(scandir('../articles/'));
			$output = '<ul id="articleList">';
			$dirEmpty = true;
			foreach($files as $f)
			{
				if($f != '..' && $f != '.')
				{
					$dirEmpty = false;
					$output .= '<li><a href="?article='.$f.'">'.$f.'</a> <a rel="nofollow" href="?article='.$f.'&amp;delete" onclick="return confirmDelete();">delete</a></li>';
				}
			}
			if ($dirEmpty)
			{
				$output .= '<li>no articles found</li>';
			}
			$output .= '</ul>'."\n";
			
			return $output;
		}
		
		public function getPageList()
		{
			$files = array_reverse(scandir('../pages/'));
			$output = '<ul id="pageList">';
			$dirEmpty = true;
			foreach($files as $f)
			{
				//text and html versions are stored together.
				if(strpos($f, '.txt') !== false)
				{
					$dirEmpty = false;
					$output .= '<li><a href="?page='.$f.'">'.$f.'</a> <a rel="nofollow" href="?page='.$f.'&amp;delete" onclick="return confirmDelete();">delete</a></li>';
				}
			}
			if ($dirEmpty)
			{
				$output .= '<li>no pages found</li>';
			}
			$output .= '</ul>'."\n";
			
			return $output;
		}
		
		public function getDraftList()
		{
			$files = array_reverse(scandir('../drafts/'));
			$output = '<ul id="draftList">';
			$dirEmpty = true;
			foreach($files as $f)
			{
				if($f != '..' && $f != '.')
				{
					$dirEmpty = false;
					$output .= '<li><a href="?draft='.$f.'">'.$f.'</a> <a rel="nofollow" href="?draft='.$f.'&amp;delete" onclick="return confirmDelete();">delete</a></li>';
				}
			}
			if ($dirEmpty)
			{
				$output .= '<li>no drafts found</li>';
			}
			$output .= '</ul>'."\n";
			
			return $output;
		}
		
		public function createArticle($text, $title, $link)
		{
			$t = time();
			$datestamp = gmdate('Y-m-d_H-i_', $t);
			$fileTitle = str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $title));
			$path = "../articles/$datestamp$fileTitle.txt";
			
			$article = $this->makeArticle($text, $title, $link, $t);
			
			$success = file_put_contents($path, $article);
			
			$apath = gmdate('Y/m/d', $t);
			$this->rebuildArticlePath($apath);
			
			$pagePath = "$apath/$fileTitle.html";
			$page = $this->buildArticleFromTemplate($path);
			
			$success = file_put_contents("../$pagePath", $page);
			
			// $this->buildArchive($apath);
			
			return $pagePath;
		}
		
		public function createPage($text, $title)
		{
			$fileTitle = str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $title));
			$path = "../pages/$fileTitle.txt";
			
			$text = $this->makePage($text, $title);
			
			$success = file_put_contents($path, $text);
			
			$page = $this->buildPageFromTemplate($path);
			
			$pagePath = "pages/$fileTitle.html";
			
			$success = file_put_contents("../$pagePath", $page);
			
			return $pagePath;
		}
		
		public function createDraft($text, $title, $link)
		{
			$t = time();
			$datestamp = gmdate('Y-m-d_H-i_', $t);
			$fileTitle = str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $title));
			$path = "../drafts/$datestamp$fileTitle.txt";
			
			$article = $this->makeArticle($text, $title, $link, $t);
			
			$success = file_put_contents($path, $article);
		}
		
		public function updateArticle($text, $filename, $title, $link, $rename)
		{
			$filename = $this->stripPath($filename);
			
			$e = $this->getArticleElements($filename);
			if($rename)
			{
				$this->deleteArticle($filename);
				$ts = substr($filename, 0, 17);
				$filename = $ts . str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $title)) . '.txt';
			}
			$path = "../articles/$filename";
			
			if($link != '')
			{
				//$article = '#['.$e['title'].']('.$link.')'."\n\n".$e['byline']."\n\n".$text;
				$article = '#['.$title.']('.$link.')'."\n\n".$e['byline']."\n\n".$text;
			}
			else
			{
				//$article = '#'.$e['title']."\n\n".$e['byline']."\n\n".$text;
				$article = '#'.$title."\n\n".$e['byline']."\n\n".$text;
			}
			
			$success = file_put_contents($path, $article);
			
			$pagePath = $this->buildURLFromFilename($filename);
			
			$page = $this->buildArticleFromTemplate($path);
			
			if(!file_exists('../'.$pagePath))
			{
				$this->rebuildArticlePath($pagePath);
			}
			
			$success = file_put_contents("../$pagePath", $page);
			
			return $pagePath;
		}
		
		public function updateDraft($text, $filename, $title, $link)
		{
			$filename = $this->stripPath($filename);
			
			//$e = $this->getDraftElements($filename);
			$path = "../drafts/$filename";
			
			$article = $this->makeArticle($text, $title, $link, time());
			
			$success = file_put_contents($path, $article);
		}

		
		public function updatePage($text, $filename, $title, $rename)
		{
			$filename = $this->stripPath($filename);
			
			//$e = $this->getPageElements($filename);
			if($rename)
			{
				$this->deletePage($filename);
				$filename = str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $title)) . '.txt';
			}
			$path = "../pages/$filename";
			
			$page = $this->makePage($text, $title);
			
			$success = file_put_contents($path, $page);
			
			$page = $this->buildPageFromTemplate($path);
			
			$pagePath = 'pages/'.substr($filename, 0, strrpos($filename, '.')).'.html';
			
			$success = file_put_contents("../$pagePath", $page);
			
			return $pagePath;
		}
		
		public function deleteArticle($filename)
		{
			$filename = $this->stripPath($filename);
			if(file_exists('../articles/'.$filename))
			{
				unlink('../articles/'.$filename);
			}
			
			$pagePath = $this->buildURLFromFilename($filename);
			
			if(file_exists('../'.$pagePath))
			{
				unlink('../'.$pagePath);
			}
			
			// $this->buildArchive(substr($pagePath, 0, 10));
		}
		
		public function deletePage($filename)
		{
			$filename = $this->stripPath($filename);
			if(file_exists('../pages/'.$filename))
			{
				unlink('../pages/'.$filename);
			}

			$pagePath = 'pages/'.substr($filename, 0, strrpos($filename, '.')).'.html';
			
			if(file_exists('../'.$pagePath))
			{
				unlink('../'.$pagePath);
			}
		}
		
		public function deleteDraft($filename)
		{
			$filename = $this->stripPath($filename);
			if(file_exists('../drafts/'.$filename))
			{
				unlink('../drafts/'.$filename);
			}
		}
		
		private function buildURLFromFilename($filename)
		{
			$pagePath = substr($filename, 0, 10);
			$title = substr($filename, 17);
			$pagePath = str_replace('-', '/', $pagePath);
			$title = str_replace('.txt', '.html', $title);
			return $pagePath.'/'.$title;
		}
		
		public function getArticleTextFilenameFromArticleURL($path)
		{
			$date = str_replace('/', '-', substr($path, 0, 10));
			$title = substr($path, 11, strrpos($path, '.')-11);
			
			$files = glob("../articles/$date_*_$title.txt");
			
			return substr($files[0], 12);
		}
		
		public function getArticleElements($filename)
		{
			$filename = $this->stripPath($filename);
			
			$article = file_get_contents('../articles/'.$filename);
			
			$t = substr($article, 1, strpos($article, "\n")-1);
			$p = strpos($t, '](');
			if ($p === false)
			{
				$e['title'] = $t;
				$e['link'] = '';
				$e['glyph'] = $this->settings['longFormGlyph'];
			}
			else
			{
				$e['title'] = substr($t, 1, $p-1);
				$e['link'] = substr($t, $p+2, strrpos($t, ')')-($p+2));
				$e['glyph'] = $this->settings['linkGlyph'];
			}
			$nn = strpos($article, "\n\n")+2;
			$e['raw'] = $article;
			$e['byline'] = substr($article, $nn, strpos($article, "\n\n", $nn)-$nn);
			$e['text'] = substr($article, strpos($article, "\n\n", strpos($article, 'class="byline"'))+2);
			$e['timestamp'] = $this->getTimestampFromArticleFilename($filename);
			$e['filename'] = $filename;
			$e['url'] = $this->buildURLFromFilename($filename);
			
			$e['markdown'] = $this->makeArticle($e['text'], $e['glyph'].$e['title'], $e['link'], $e['timestamp']);
			
			return $e;
		}
		
		public function getTimestampFromArticleFilename($filename)
		{
			$d = substr($filename, 0, 10);
			$t = substr($filename, 11, 2) . ':' . substr($filename, 14, 2);
			
			return strtotime("$d $t");
		}
		
		public function getPageElements($filename)
		{
			$filename = $this->stripPath($filename);
			
			$page = file_get_contents('../pages/'.$filename);
			
			$e['title'] = substr($page, 1, strpos($page, "\n")-1);
			/*$nn = strpos($article, "\n\n")+2;
			$e['byline'] = substr($article, $nn, strpos($article, "\n\n", $nn)-$nn);*/
			$e['text'] = substr($page, strpos($page, "\n\n")+2);
			$e['filename'] = "pages/$filename";
			$e['url'] = 'pages/' . substr($filename, 0, strrpos($filename, '.txt')) . '.html';
			$e['timestamp'] = filemtime("../pages/$filename");
			
			return $e;
		}
		
		public function getDraftElements($filename)
		{
			$filename = $this->stripPath($filename);
			
			$article = file_get_contents('../drafts/'.$filename);
			
			$t = substr($article, 1, strpos($article, "\n")-1);
			$p = strpos($t, '](');
			if ($p === false)
			{
				$e['title'] = $t;
				$e['link'] = '';
			}
			else
			{
				$e['title'] = substr($t, 1, $p-1);
				$e['link'] = substr($t, $p+2, strrpos($t, ')')-($p+2));
			}
			
			//$e['title'] = substr($page, 1, strpos($page, "\n"));
			//$e['text'] = substr($page, strpos($page, "\n\n")+2);
			$nn = strpos($article, "\n\n")+2;
			$e['byline'] = substr($article, $nn, strpos($article, "\n\n", $nn)-$nn);
			$e['text'] = substr($article, strpos($article, "\n\n", strpos($article, 'class="byline"'))+1);
			
			return $e;
		}
		
		private function buildArticleFromTemplate($textPath)
		{
			$a = $this->getArticleElements($textPath);
			$article = Markdown($a['markdown']);
			
			if(file_exists('../templates/'.$this->settings['currentTemplate'].'/_article.html'))
			{
				$template = file_get_contents('../templates/'.$this->settings['currentTemplate'].'/_article.html');
			}
			else
			{
				$template = file_get_contents('../templates/'.$this->settings['currentTemplate'].'/_home.html');
			}
			
			$output = str_replace('$PAGE_CONTENT', $article, $template);
			
			$output = str_replace('$PAGE_URL', $this->settings['siteRoot'].$a['url'], $output);
			
			$title = str_replace('_', ' ', substr($textPath, 29, strrpos($textPath, '.')-29));
			$output = str_replace('$PAGE_TITLE', $title, $output);
			
			return $output;
		}
		
		private function buildPageFromTemplate($textPath)
		{
			$page = Markdown(file_get_contents($textPath));
			$fileTitle = substr($textPath, 9, strrpos($textPath, '.')-9);
			$title = str_replace('_', ' ', $fileTitle);
			if(file_exists('../templates/'.$this->settings['currentTemplate'].'/'.$fileTitle.'.html'))
			{
				$template = file_get_contents('../templates/'.$this->settings['currentTemplate'].'/'.$fileTitle.'.html');
			}
			else if(file_exists('../templates/'.$this->settings['currentTemplate'].'/_page.html'))
			{
				$template = file_get_contents('../templates/'.$this->settings['currentTemplate'].'/_page.html');
			}
			else
			{
				$template = file_get_contents('../templates/'.$this->settings['currentTemplate'].'/_home.html');
			}
			
			$output = str_replace('$PAGE_CONTENT', $page, $template);
			
			$output = str_replace('$PAGE_TITLE', $title, $output);
			
			return $output;
		}
		
		// private function buildArchivePageFromTemplate($content)
		// {
		// 	if(file_exists('../templates/'.$this->settings['currentTemplate'].'/_archive.html'))
		// 	{
		// 		$template = file_get_contents('../templates/'.$this->settings['currentTemplate'].'/_archive.html');
		// 	}
		// 	else if(file_exists('../templates/'.$this->settings['currentTemplate'].'/_article.html'))
		// 	{
		// 		$template = file_get_contents('../templates/'.$this->settings['currentTemplate'].'/_article.html');
		// 	}
		// 	else
		// 	{
		// 		$template = file_get_contents('../templates/'.$this->settings['currentTemplate'].'/_home.html');
		// 	}
			
		// 	$output = str_replace('$PAGE_CONTENT', $content, $template);
			
		// 	//TODO: needs to be more descriptive.
		// 	$output = str_replace('$PAGE_TITLE', 'archive', $output);
			
		// 	return $output;
		// }
		
		private function makeArticle($text, $title, $link, $t)
		{
			$date = gmdate('l, d F Y @ H:i', $t);
			
			$byline = "\n<p class=\"byline\">posted by {$_SESSION['user']['name']} on $date</p>\n";
			if ($link != '')
			{
				return '#['.$title.']('.$link.')' ."\n".$byline."\n".$text;
			}
			else
			{
				return '#'.$title."\n".$byline."\n".$text;
			}
		}
		
		private function makePage($text, $title)
		{
			return '#'.$title."\n\n".$text;
		}
		
		public function getRecentArticles($count)
		{
			$files = array_reverse(scandir('../articles/'));
			
			$c = 0;
			for($i=0; $i<count($files) && $c < $count; $i++)
			{
				if ($files[$i] != '.' && $files[$i] != '..')
				{
					$c++;
					$articles[] = $this->getArticleElements($files[$i]);
				}
			}
			return $articles;
		}
		
		public function getPages()
		{
			$files = scandir('../pages/');
			
			for($i=0; $i<count($files); $i++)
			{
				if (strpos($files[$i], '.txt') !== false)
				{
					$pages[] = $this->getPageElements($files[$i]);
				}
			}
			return $pages;
		}
		
		public function buildHomepage()
		{
			//get articles
			$files = array_reverse(scandir('../articles/'));
			
			$feed = new RSSFeed();
			
			$lastBuildDate = date("D, d M Y H:i:s", time());
			$ttl = '1440'; //1 Day
			
			$feed->setFeedHeader(
					$this->settings['feedTitle'],
					$this->settings['siteRoot'],
					'',
					'All rights reserved.',
					$lastBuildDate,
					$ttl);
			
			for($i=0; $i<10 && $i<count($files)-2; $i++)
			{
				if ($files[$i] != '.' && $files[$i] != '..')
				{
					$t = str_replace('_', ' ', substr($files[$i], 0, 16));
					$t = substr($files[$i], 0, 10) . ' ' . substr($files[$i], 11, 2) . ':' . substr($files[$i], 14, 2);
					$t = strtotime($t);
					
					$pubDateTime = date("D, d M Y H:i", $t);
					$e = $this->getArticleElements('../articles/'.$files[$i]);
					//$content = Markdown(file_get_contents('../articles/'.$files[$i]));
					$content = Markdown($e['markdown']);
					
					$lurl = $this->buildURLFromFilename($files[$i]);

					$output .= '<article>'. $content .'<p><a href="'.$lurl.'">#</a></p></article>';
					
					if ($e['link'] == '') {$url = $this->settings['siteRoot'].$lurl;}
					else {$url = $e['link'];}
					
					$feedArticle = Markdown($e['text']) . '<p><a href="'.$lurl.'">#</a></p>';
					
					$feed->queueItem($e['glyph'].$e['title'], $url, $feedArticle, $pubDateTime);
				}
			}
			
			$feed->writeOutFeed('../feed.xml');
			
			$template = file_get_contents('../templates/'.$this->settings['currentTemplate'].'/_home.html');
			
			$output = str_replace('$PAGE_CONTENT', $output, $template);
			
			if($this->settings['blogNotHomepage'])
			{
				$success = file_put_contents('../blog.html', $output);
			}
			else
			{
				$success = file_put_contents('../index.html', $output);
			}
		}
		
		public function rebuildSite()
		{
			$this->buildHomepage();
			$this->rebuildArticles();
			$this->rebuildArchive();
			$this->rebuildPages();
		}
		
		private function rebuildArticles()
		{
			$files = array_reverse(scandir('../articles/'));
			
			foreach($files as $f)
			{
				if($f != '..' && $f != '.')
				{
					$page = $this->buildArticleFromTemplate('../articles/'.$f);
					$pagePath = $this->buildURLFromFilename($f);
					
					if(!file_exists($pagePath))
					{
						//TODO
						$this->rebuildArticlePath($pagePath);
					}
					
					$success = file_put_contents("../$pagePath", $page);
				}
			}
		}
		
		private function rebuildPages()
		{
			$files = array_reverse(scandir('../pages/'));
			
			foreach($files as $f)
			{
				if(strpos($f, '.txt') !== false)
				{
					$page = $this->buildPageFromTemplate('../pages/'.$f);
					$filename = substr($f, 0, strrpos($f, '.'));
					
					$success = file_put_contents("../pages/$filename.html", $page);
				}
			}
		}
		
		// private function buildArchive($path)
		// {
		// 	$months = array(1=>"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		
		// 	$files = scandir("../$path");
			
		// 	$output = '<h1>Archive: '.$path.'</h1><nav><ul>';
		// 	$empty = true;
		// 	foreach($files as $f)
		// 	{
		// 		if($f != '..' && $f != '.' && $f != 'index.html')
		// 		{
		// 			$t = $this->getArticleTextFilenameFromArticleURL("$path/$f");
		// 			$a = $this->getArticleElements($t);
		// 			$output .= '<li><a href="'.$f.'">'.$a['title'].'</a></li>';
		// 			$empty = false;
		// 		}
		// 	}
		// 	$output .= '</ul></nav>';
			
		// 	if($empty)
		// 	{
		// 		//no articles in this folder any more, delete it
		// 		if(file_exists("../$path/index.html"))
		// 		{
		// 			unlink("../$path/index.html");
		// 		}
		// 		rmdir("../$path");
		// 	}
		// 	else
		// 	{
		// 		$page = $this->buildArchivePageFromTemplate($output);
			
		// 		$success = file_put_contents("../$path/index.html", $page);
		// 	}
			
		// 	$path = substr($path, 0, 7);
			
		// 	$files = scandir("../$path");
			
		// 	$output = '<h1>Archive: '.$path.'</h1><nav><ul>';
		// 	$empty = true;
		// 	foreach($files as $f)
		// 	{
		// 		if($f != '..' && $f != '.' && $f != 'index.html')
		// 		{
		// 			$day = gmdate('l, d', strtotime("$path/$f 12:00:00"));
		// 			$output .= '<li><a href="'.$f.'">'.$day.'</a></li>';
		// 			$empty = false;
		// 		}
		// 	}
		// 	$output .= '</ul></nav>';
			
		// 	if($empty)
		// 	{
		// 		//no articles in this folder any more, delete it
		// 		if(file_exists("../$path/index.html"))
		// 		{
		// 			unlink("../$path/index.html");
		// 		}
		// 		rmdir("../$path");
		// 	}
		// 	else
		// 	{
		// 		$page = $this->buildArchivePageFromTemplate($output);
			
		// 		$success = file_put_contents("../$path/index.html", $page);
		// 	}
			
		// 	$path = substr($path, 0, 4);
			
		// 	$files = scandir("../$path");
			
		// 	$output = '<h1>Archive: '.$path.'</h1><nav><ul>';
		// 	$empty = true;
		// 	foreach($files as $f)
		// 	{
		// 		if($f != '..' && $f != '.' && $f != 'index.html')
		// 		{
		// 			$output .= '<li><a href="'.$f.'">'.$months[intval($f)].'</a></li>';
		// 			$empty = false;
		// 		}
		// 	}
		// 	$output .= '</ul></nav>';
			
		// 	if($empty)
		// 	{
		// 		//no articles in this folder any more, delete it
		// 		if(file_exists("../$path/index.html"))
		// 		{
		// 			unlink("../$path/index.html");
		// 		}
		// 		rmdir("../$path");
		// 	}
		// 	else
		// 	{
		// 		$page = $this->buildArchivePageFromTemplate($output);
			
		// 		$success = file_put_contents("../$path/index.html", $page);
		// 	}
			
		// 	$files = array_reverse(scandir(".."));
		// 	$output = '<h1>Archive</h1><nav><ul>';
		// 	foreach($files as $f)
		// 	{
		// 		$f_ = substr($f, 0, 2);
		// 		if($f_ == '19' || $f_ == '20')
		// 		{
		// 			$output .= '<li><a href="'.$f.'">'.$f.'</a></li>';
		// 		}
		// 	}
		// 	$output .= '</ul></nav>';
		// 	$page = $this->buildArchivePageFromTemplate($output);
		// 	$success = file_put_contents("../archive.html", $page);
			
		// }
		
		private function rebuildArticlePath($path)
		{
			$y = substr($path, 0, 4);
			$m = substr($path, 5, 2);
			$d = substr($path, 8, 2);
			
			if(!file_exists("../$y"))
			{
				//create all 3
				mkdir("../$y");
				mkdir("../$y/$m");
				mkdir("../$y/$m/$d");
			}
			else if(!file_exists("../$y/$m"))
			{
				//create m & d
				mkdir("../$y/$m");
				mkdir("../$y/$m/$d");
			}
			else if(!file_exists("../$y/$m/$d"))
			{
				//create d
				mkdir("../$y/$m/$d");
			}
		}
		
		private function rebuildArchive()
		{
			// //TODO - need to check for empty directories (from deleted articles)
			// $files = array_reverse(scandir(".."));
			// foreach($files as $f)
			// {
			// 	$f_ = substr($f, 0, 2);
			// 	if($f_ == '19' || $f_ == '20')
			// 	{
			// 		$files2 = array_reverse(scandir("../$f"));
					
			// 		foreach($files2 as $f2)
			// 		{
						
			// 			if($f2 != '..' && $f2 != '.' && $f2 != 'index.html')
			// 			{
							
			// 				$files3 = array_reverse(scandir("../$f/$f2"));
					
			// 				foreach($files3 as $f3)
			// 				{
						
			// 					if($f3 != '..' && $f3 != '.' && $f3 != 'index.html')
			// 					{
			// 						$this->buildArchive("$f/$f2/$f3");
			// 					}
			// 				}
			// 			}
			// 		}
			// 	}
			// }

			// Create single-page archive of all files in reverse order of creation
			$files = array_reverse(scandir('../articles/'));
			
			for($i=0; $i<count($files)-1; $i++)
			{
				if ($files[$i] != '.' && $files[$i] != '..')
				{
					$content = Markdown(file_get_contents('../articles/'.$files[$i]));
					
					$lurl = $this->buildURLFromFilename($files[$i]);

					$e = $this->getArticleElements($files[$i]);
					
					if ($e['link'] == '') {$url = $this->settings['siteRoot'].$lurl;}
					else {$url = $e['link'];}
					
					$title = '<a href='.$url.'><h3>'.$e['title'].'</h3></a>';
					$byline = $e['byline'];

					$output .= $title.$byline."\n\n";
				}
			}
			
			$template = file_get_contents('../templates/'.$this->settings['currentTemplate'].'/_archive.html');
			
			$output = str_replace('$PAGE_CONTENT', $output, $template);
			
			$success = file_put_contents('../archive.html', $output);
		}
		
		public function stripPath($path)
		{
			if(strrpos($path, '/') !== false)
			{
				return substr($path, strrpos($path, '/')+1);
			}
			return $path;
		}
	}
?>