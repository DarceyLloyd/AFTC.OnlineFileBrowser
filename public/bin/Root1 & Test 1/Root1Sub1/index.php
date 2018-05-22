<?php

	class AFTCDirBrowser
	{
		public $url;
		public $this_file_url;
		public $dir;
		public $browser_title;
		
		public $nav_path;
		public $dir_data;
		public $dirs = array();
		public $files = array();
		public $file_sizes = array();
		
		public $name_filters = array();
		public $partial_filters = array();
		
		public $current_file_path_parts;
		public $current_file;
		
		public $folder_path_is_set = false;
		
		public function __construct()
		{
			// Fully qualified names ignore rules
			$this->name_filters = array(
				".htaccess",
				".htpasswd"
			);
			
			// Partial ignore rules
			$this->partial_filters = array(
				"-hide-",
				"01"
			);
			
			
			
			//$this->url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$this->url = "http://$_SERVER[HTTP_HOST]" . strtok($_SERVER["REQUEST_URI"],'?');
			$this->this_file_url = $this->url;
			$this->browser_title = str_replace("http://","",$this->url);
			$this->browser_title = str_replace("/"," > ",$this->browser_title);
			$this->browser_title .= " AFTC Dir Browser";
			$this->file_name = basename($_SERVER["SCRIPT_FILENAME"]);
			$this->browser_title = str_replace( $this->file_name , "" , $this->browser_title);
			//var_dump($this->browser_title);
			//var_dump($this->file_name);
			
			if (isset($_GET['folder'])) {
				$this->folder_path_is_set = true;
				$this->nav_path = $_GET['folder'];
				$this->nav_path = str_replace("?folder=","",$this->nav_path);
				$this->dir = getcwd() . "/" . $_GET['folder'];
				$this->dir = str_replace("../","",$this->dir);
			} else {
				$this->nav_path = "";
				$this->dir = getcwd();
				
				// In root dir we dont want index.php to show as it's this file!
				array_push($this->name_filters,"index.php");
			}
			
			
			
			$this->current_file_path_parts = pathinfo(__FILE__);
			$this->current_file = $this->current_file_path_parts['basename'];
			$this->url = str_replace($this->current_file,"",$this->url);
			
			//var_dump($this);
			
			// Check server path with url path is a valid dir or file
			if (is_dir($this->dir)){
				if (file_exists($this->dir)){
					// We have a directory!
				} else {
					echo("AFTC Directory Browser - Directory no longer exists");
					die;
				}
				
			} else if (is_file($this->dir)){
				// we have a file!
				$target = ($this-url . $this->nav_path);
				//echo("AFTC Directory Browser - FILE FOUND!");
				//echo("ATTEMPTING TO REDIRECT TO [" . $target . "]");
				
				header("location: " . $target);
				//var_dump($this);
				die;
			} else {
				// dont know what we have?
				echo("AFTC Directory Browser - URL / File no longer exists");
				die;
			}
			
			
			
			
			
			
			
			// Get dir listing and run name_filters
			$this->dir_data = array_diff(scandir($this->dir), array('..', '.','../'));
			$this->dir_data = array_diff($this->dir_data,$this->name_filters);
			
			
			foreach ($this->dir_data as $value) 
			{
				//echo($key . " = " . $value . "<br>");
				//echo("<br>");
				$found = isArrayInArray($value,$this->partial_filters);
				if (!$found){
					
					$item = $this->dir . "/" . $value;
					//echo("<b>CHECKING TO SEE IF [" . $item . "] IS A DIR</b><br>");
					if (is_dir($item))
					{
						//echo("<b>YES IT IS A DIR!</b><br>");
						array_push($this->dirs,$value);
					} else {
						//echo("<b>NO IT IS A FILE!</b><br>");
						array_push($this->files,$value);
						$file = $this->dir . "/" . $value;
						$osize = filesize($file);
						
						if ($osize > 1048576){
							$size = $osize / 1024 / 1024;
							$size = number_format((float)$size, 1, '.', '');
							$size = $size . "mb";
						} else {
							$size = $osize / 1024;
							$size = number_format((float)$size, 1, '.', '');
							$size = $size . "kb";
						}
						
						
						array_push($this->file_sizes,$size);
						//var_dump($this->dir . "\\" . $value);
					}
				}
				
				
			}
			
			natcasesort($this->dirs);
			natcasesort($this->files);
			
		}
		
		
		public function listDirectories()
		{
			foreach ($this->dirs as $key => $value) 
			{
				//trace( __FILE__ );
			//trace( $this->this_file_url );
				
				//echo($key . " = " . $value . "<br>");
				$directory = str_replace($this->url."\\","",$value);
				if ($this->nav_path != ""){
					$link = $this->this_file_url . "?folder=" . $this->nav_path . "/" . $directory;
				} else {
					$link = $this->this_file_url . "?folder=" . $directory;
				}
				echo("<tr>\n");
				echo("<td class='col1 dirCol' onclick='navigateTo(\"" . $link . "\");'><a href='" . $link . "'>" . $directory . "</a></td>\n");
				$html_link = "<a href='" . $link . "' >" . $link . "</a>";
				//echo("<td class='dirTableCol1 dirCol' title='CLICK TO NAVIGATE INTO THIS FOLDER' onclick='navigateTo(\"" . $link . "\");'>" . $directory . "</td>\n");
				echo("</tr>\n");
			}
		}
		
		
		public function listFiles()
		{
			foreach ($this->files as $key => $value) 
			{
				//echo($key . " = " . $value . "<br>");				
				$file = str_replace($this->url."\\","",$value);
				if ($this->nav_path != ""){
					$link = $this->url . str_replace($this->current_file,"",$this->nav_path) . "/" . $file;
				} else {
					$link = $this->url . $file;
				}
				
				//$this->url = str_replace($current_file,"",$this->url);
				
				
				$html_link = "<a href='" . $link . "'>" . $file . "</a>";
				echo("<tr>\n");
				//echo("<td class='col1 fileNameCol' onclick='navigateTo(\"" . $link . "\");'><a href='" . $link . "'>" . $file . "</a></td>\n");
				echo("<td class='fileTableCol1 fileNameCol' title='CLICK TO OPEN/SAVE THIS FILE' onclick='navigateTo(\"" . $link . "\");'>" . $html_link . "</td>\n");
				echo("<td class='fileTableCol2 fileSizeCol'>" . $this->file_sizes[$key] . "</td>\n");
				echo("</tr>\n");
			}
		}
		
		
	}
	
	
	if (isSet($_GET["phpinfo"]))
	{
		if ($_GET["phpinfo"] == "1")
		{
			phpinfo();
			die;
		}
	}
	
	$aftc = new AFTCDirBrowser();
	//var_dump($aftc);
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title><?php echo($aftc->browser_title); ?></title>
	<meta description="AFTC Directory & File Browser - By Darcey@AllForTheCode.co.uk"/>
		<meta author="Darcey@AllForTheCode.co.uk"/>

		
		<style>
			* {
				font-family: arial;
				font-size: 18px;
			}
			
			#listingTable {
				width: 100%;
			}
			
			#listingTable tr {
				border: 1px solid #000000;
			}
			
			#listingTable td {
				border: 1px solid #000000;
				padding: 5px;
			}
			

			
			.dirTableCol1 {
				
			}
			
			.fileTableCol1 {
				
			}
			
			.fileTableCol2 {
				width: 70px;
				
			}
			
			
			
			.titleCol {
				font-size: 19px;
				font-weight: bold;
				color: #FFFFFF;
				background: #333333;
			}

			
			
			
			
			.dirCol {
				font-weight: bold;
				background: #AAAAAA;
				cursor: pointer;
			}
			
			.dirCol:hover {
				background: #EEEEFF;
			}
			
			
			
			
			
			@media (min-width: 1px) and (max-width: 700px) {
				
			}
			
			@media (min-width: 601px) {


			}

		
			
			.fileNameCol {
				font-weight: bold;
				background: #DDDDDD;
				cursor: pointer;
			}
			
			.fileNameCol:hover {
				background: #EEEEFF;
			}
			
			.fileSizeCol {
				background: #DDDDDD;
				cursor: pointer;
			}
			
			
			
			
			a {
				color: #990000;
				text-decoration: none;
			}
			
			a:visited {
				color: #330000;
			}
			
			a:hover {
				text-decoration: underline;
			}
			
			h3 {
				padding: 0 0 0 5px;
				margin: 0;
				font-size: 21px;
			}
			
			h4 {
				padding: 5px;
				font-size: 14px;
				padding: 0 0 10px 5px;
				margin: 0 0 0 0;
			}
			
			h4 a {
				font-size: 14px;
			}
			
			#crumbs {
				margin-top: 10px;
				margin-bottom: 10px;
			}
			
			.crumb {
				background: #DDDDDD;
				padding: 3px;
			}
		</style>
		
		<script>
			function navigateTo($url){
				self.location.href = $url;
			}
		</script>
	</head>
	<body>
		
		<h3>Data &amp; Dreams LTD | AllForTheCode - Directory &amp; File Browser</h3>
		<h4><a href="mailto:Darcey@AllForTheCode.co.uk" target="_blank">Darcey@AllForTheCode.co.uk</a></h4>
		
		<div id="crumbs" style="padding-left: 5px;">
		<?php
		//var_dump($aftc);
		$bits = explode("/",$aftc->nav_path);
		$no_of_bits = sizeof($bits) - 1;
		//echo("<h1>" . $no_of_bits . "</h1>");
		
		
		if (($aftc->folder_path_is_set == true) && ($no_of_bits < 1)){
			echo("<a class='crumb' href='" . $aftc->url . "'>" . $aftc->url . "</a> > ");
			echo("<a class='crumb' href='" . $aftc->url . "?folder=" . $aftc->nav_path . "'>" . $aftc->nav_path . "</a>");
		}
		
		//echo( $aftc->url );
		
		if ($no_of_bits > 0){
			
			echo("<a class='crumb' href='" . $aftc->url . "'>" . $aftc->url . $aftc->file_name . "</a> > ");
			
			foreach ($bits as $key => $value){
			 	$link = $aftc->url . $aftc->file_name . "?folder=";
				for ($i=0; $i <= $key; $i++){
					if ($i==0){
			 	 		$link = $link . $bits[$i];
			 	 	} else {
			 	 		$link = $link . "/" . $bits[$i];
			 	 	}
			 	}
			 	
			 	//echo($key . " = " . $value . " : " . $link . "<br>\n");
			 	if ($key < $no_of_bits){
			 	 	$html_link = "<a class='crumb' href='" . $link . "'>" . $value . "</a> > ";
			 	} else {
			 		$html_link = "<a class='crumb' href='" . $link . "'>" . $value . "</a>";
			 	}
			 	 echo($html_link);
				}
		}
		?>
		</div>
		
		<?php if (sizeof($aftc->dirs) > 0){ ?>
		<table id="listingTable">
			<tr>
				<td class="titleCol">Directory names</td>
			</tr>
			<?php $aftc->listDirectories(); ?>
		</table>
		<?php } ?>
		
		
		<?php if (sizeof($aftc->files) > 0){ ?>
		<table id="listingTable">
			<tr>
				<td class="titleCol">File names</td>
				<td class="titleCol">Size</td>
			</tr>
			<?php $aftc->listFiles(); ?>
		</table>
		<?php } ?>
		
		
		
		
	</body>
</html>
<?php

// Function utilities
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
function trace($str){ echo($str."<br>");}


function isArrayInArray($string, $sub_strings){
    foreach($sub_strings as $substr){
        if(strpos($string, $substr) !== FALSE)
        {
          return true;
        }
    }

   return false;
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


?>

