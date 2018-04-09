<?php
// configuration variables
$IgnoreFilesWith = array(".fla",".php");


// ##################################################################
// ##################################################################
// NO NEED TO MODIFY ANYTHING BEYOND THIS POINT
// ##################################################################
// ##################################################################

echo("<link rel='stylesheet' type='text/css' href='file_browser/styles.css' />");

// var ini
$dirpath = "";
if (isset($_GET["p"])) { $dirpath = $_GET["p"]; }
$dirs = array();
$files = array();


$exclude = array('.','..','.ftpquota','.htaccess');
$exclude_ext = array('','');

$dirpath = "";
if(get_magic_quotes_gpc())
{
	$dirpath = stripslashes($_GET['p']);
} else {
	if (isset($_GET['p'])) {
		$dirpath = $_GET['p'];
	} else {
		$dirpath = "";
	}

}

if(empty($dirpath)){
	$dirpath = "./";
}


//This is a VERY important security feature. It wont allow people to browse directories above $dir_to_browse. Edit this part at your own risk
$folders_in_URL_security = explode("../",$dirpath);
if(count($folders_in_URL_security) > 1)
{
	$dirpath = "./";
	exit;
}



// 


if ($DirectoryReader = opendir($dirpath)) {
	// Open directory reader resource
    //while ($file = readdir($DirectoryReader)) {
	while(false !== ($file = readdir($DirectoryReader))) {
		//rw("[" . $file . "] = " . is_dir($dirpath.$file));
		
		// Prevent these from getting in . and ..
        if ($file != '.' && $file != '..') {
			if (is_dir($dirpath.$file)){
				array_push($dirs,$file);
			} else {
				array_push($files,$file);
			}
		}
    }

	// close directory reader resource
    closedir($DirectoryReader);
	}
	

	// Gen HTML output
	$dhtml = "";
	$dhtml .= "<div id='dfb-filebrowser'>";
	$dhtml .= "<table id='dfb-table' border='0' cellpadding='0' cellspacing='1'>";
	
	
	// Titles
	$dhtml .= "<tr id='dfb-title-row'>";
		$dhtml .= "<td id='dfb-icon-column'></td>";
		$dhtml .= "<td id='dfb-title-column'>AFTC PHP File Browser V0.1a</td>";
		$dhtml .= "<td id='dfb-title-column'></td>";
	$dhtml .= "</tr>";
	
	
	
	
	// Up a directory row
	$updir = "";
	$GenerateUpLink = true;
	if (!isset($_GET["p"])) {
		$GenerateUpLink = false;
	}
	
	$pathArray = explode ("/",$dirpath);
	$pathArraySize = count($pathArray);
	
	$upPath = "";
	$upTarget = $pathArraySize-3;
	for ($i=0; $i <= $upTarget; $i++)
	{
		$upPath .= $pathArray[$i] . "/";
	}
	
	if ($pathArraySize<3){
		$GenerateUpLink = false;
	}
	
	
	$dhtml .= "<tr id='dfb-dir-row'>";
		$dhtml .= "<td id='dfb-icon-column'>";
		if ($GenerateUpLink){
			$dhtml .= "<a href='index.php?p=".urlencode($upPath)."'>../</a>";
		}
		$dhtml .= "</td>";
		$dhtml .= "<td id='dfb-dir-column1'>Current path: $dirpath</td>";
		$dhtml .= "<td id='dfb-dir-column2'>";
			$dhtml .= "";
		$dhtml .= "</td>";
	$dhtml .= "</tr>";


	
	
	
	// Dump dirs and files to HTML
	foreach ($dirs as $value)
	{
		$dhtml .= "<tr id='dfb-dir-row'>";
			$dhtml .= "<td id='dfb-icon-column'>";
				$dhtml .= "<img src='file_browser/folder.png' width='20' height='20' border='0'>";
			$dhtml .= "</td>";		
			$dhtml .= "<td id='dfb-dir-column1'>";
				$dhtml .= "<a href='index.php?p=".urlencode($dirpath . $value)."/'>".$value."</a>";
			$dhtml .= "</td>";
			$dhtml .= "<td id='dfb-dir-column2'>";
				$dhtml .= "Directory";
			$dhtml .= "</td>";
		$dhtml .= "</tr>";
	}
	
	
	
	// Dump dirs and files to HTML
	foreach ($files as $value)
	{
		$dhtml .= "<tr id='dfb-file-row'>";
			$dhtml .= "<td id='dfb-icon-column'>";
				$dhtml .= "<img src='file_browser/file.png' width='20' height='20' border='0'>";
			$dhtml .= "</td>";
			$dhtml .= "<td id='dfb-file-column1'>";
				$dhtml .= "<a href='$dirpath.$value'>$value</a>";
			$dhtml .= "</td>";
			$dhtml .= "<td id='dfb-file-column2'>";
				$dhtml .= "File";
			$dhtml .= "</td>";
		$dhtml .= "</tr>";
	}
	
	
	
	
	$dhtml .= "</table>";
	$dhtml .= "</div>";
	

	// --------------------------------------------------------
	// Functions
	// --------------------------------------------------------
	
	// Check file exclusions
	function checkFileExclusion($filename)
	{
		foreach ($dirs as $value)
		{
			//array_push($files,$file);
		}
	}
	
	// Quick html write
	function rw($str){
		echo ($str);
	}
	function rwb($str){
		echo ($str . "<br/>");
	}
	
	
	// --------------------------------------------------------
?>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
</head>
<body>

<?php echo($dhtml); ?>

</body>
</html>