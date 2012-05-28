<?php

	class FileManager
	{
	
		/* Class Constructor */
		function FileManager()
		{
			//do some contruction
			
		}
		
		public function getFileBrowser($dir)
		{
			if(strpos($dir, '../') !== false)
			{
				$dir = '';
			}
		
			if (!file_exists("../files/$dir")) {return '<p>File Not Found</p>';}
			$files = scandir("../files/$dir");
			
			$output = "<ul id=\"fileBrowser\">\n";
			$dirEmpty = true;
			foreach($files as $f)
			{
				if($f != '..' && $f != '.')
				{
					$dirEmpty = false;
					if(is_dir("../files/$dir/$f"))
					{
						$output .= "<li><a class=\"dir\" href=\"?file&amp;dir=$dir$f/\" title=\"$f\">$f</a></li>\n";
					}
					else
					{
						$ext = substr($f, strrpos($f, '.')+1);
						$output .= "<li><a class=\"$ext\" href=\"?file=$f&amp;dir=$dir\" title=\"$f\">$f</a></li>\n";
					}
				}
			}
			$output .= "</ul>\n";
			
			if($dirEmpty)
			{
			
				$output = '<ul id="fileBrowser"><li>No Files. ';
				if($dir != '') {$output .= "<a class=\"dirx\" href=\"deleteFile.php?dir=$dir\" onclick=\"return confirmDelete();\">Remove Directory</a>";}
				$output .= '</li></ul>';
			
			}
			return $output;
		}
		
		public function getFileInfoPanel($dir, $filename)
		{
			if(strpos($dir, '../') !== false)
			{
				$dir = '';
			}
		
			$pos = strrpos($filename, '/');
			if($pos > 0) {$filename=substr($filename, $pos+1);}
			
			//if($dir) {$dir .= '/';}
			
			if(!file_exists("../files/$dir$filename")) {return '<p>No Such File</p>';}
			$ext = strtolower(substr($filename, strrpos($filename, '.')+1));
			
			$output = "<table>\n";
			$output .= "<tr><th>download</th><th>file info</th></tr>\n";
			$output .= "<tr><td id=\"fileInfo\" rowspan=\"3\"><a class=\"$ext\" href=\"../files/$dir$filename\">$filename</a></td>";
			
			$output .= "<td>$filename</td>";
			$output .= "</tr>\n";
			$output .= "<tr><th>actions</th></tr>\n";
			$output .= "<tr><td><form method=\"POST\" action=\"renameFile.php\"><input type=\"hidden\" name=\"dir\" value=\"$dir\"><input type=\"hidden\" name=\"oldname\" value=\"$filename\"><input type=\"text\" name=\"name\" id=\"name\" value=\"$filename\" size=\"10\"> <input type=\"submit\" class=\"btn\" value=\"rename\"></form>";
			$output .= "<form method=\"POST\" action=\"renameFile.php\"><input type=\"hidden\" name=\"dir\" value=\"$dir\"><input type=\"hidden\" name=\"filename\" value=\"$filename\"><select name=\"targetDir\" id=\"targetDir\"><option value=\"\">-- directory --<option>";
			if($dir){$output .= '<option value="..">.. parent dir ..</option>';}
			$files = scandir("../files/$dir");
			foreach($files as $f)
			{
				if (is_dir("../files/$dir$f") && $f != '..' && $f != '.') {$output .= "<option value=\"$f\">$f</option>";}
			}
			
			$output .= '</select> <input type="submit" class="btn" value="move"></form>';
			$output .= "<a href=\"deleteFile.php?dir=$dir&filename=$filename\" onclick=\"return confirmDelete();\">delete</a>
			</td></tr>\n";
			
			$output .= "</table>\n";
			
			return $output;
		}
		
		public function buildDirectoryBreadcrumb($dir)
		{
			if(strpos($dir, '../') !== false)
			{
				$dir = '';
			}
			if($dir)
			{
				$dirs = explode('/', $dir);
			
				foreach ($dirs as $d)
				{
					$path .= $d . '/';
					$output .= " / <a href=\"?file&amp;dir=$path\">$d</a>";
				}
				return $output;
			}
		}
		
		public function checkFileType($ext)
		{
			return true;
		}
	}
?>