<?php

	class RSSFeed
	{
		var $feedHeader;
		var $feedItems;
	
		/* Class Constructor */
		function RSSFeed()
		{
			//do some contruction
			$this->feedHeader = '';
			$this->feedItems = '';
		}
		
		function setFeedHeader($title, $link, $description, $copyright, $lastBuildDate, $ttl)
		{
			$this->feedHeader = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel>';
			$this->feedHeader .= '<title>'.$title.'</title>';
			$this->feedHeader .= '<link>'.$link.'</link>';
			$this->feedHeader .= '<description>'.$description.'</description><copyright>'.$copyright.'</copyright>';
			$this->feedHeader .= '<language>en-GB</language><lastBuildDate>'.$lastBuildDate.' GMT</lastBuildDate><ttl>'.$ttl.'</ttl>';
		}
		
		public function pushItem($title, $link, $description, $pubDateTime)
		{
			$item = $this->generateItem($title, $link, $description, $pubDateTime);
			
			$this->feedItems = $item . $this->feedItems;
		}
		
		public function queueItem($title, $link, $description, $pubDateTime)
		{
			$item = $this->generateItem($title, $link, $description, $pubDateTime);
			
			$this->feedItems = $this->feedItems . $item;
		}
		
		private function generateItem($title, $link, $description, $pubDateTime)
		{
			$item = '<item><title>' . htmlentities(stripslashes($title)) . '</title>';
			$item .= '<link>' . $link . '</link>';
			$item .= '<guid>' . $link . '</guid>';
			//$item .= '<description>' . htmlentities(stripslashes($description)) . '</description>';
			$item .= '<description><![CDATA[' . stripslashes($description) . ']]></description>';
			$item .= '<pubDate>' . $pubDateTime . ' GMT</pubDate></item>';
			
			return $item;
		}
		
		function writeOutFeed($path)
		{
			$file = fopen($path, "w");
			fputs($file, $this->feedHeader);
			fputs($file, $this->feedItems);
			fputs($file, '</channel></rss>');
			fclose($file);
		}
	}
?>