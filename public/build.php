<?php



/**
 * Build process
 * - delete bin folder and everything in it
 * - create bin folder
 * - copy src to bin folder
 * - read styles.css etc into variables
 * - read index.php into variable
 * - insert styles etc into index.php
 * - delete styles.css etc
 * - file_put_contents of index.php var string into index.php
 */

system('cls');

deleteDir("./bin");
mkdir("bin");
copyFolderAndContents("./src","./bin");

$composer = file_get_contents("./composer.json");
$composer = json_decode($composer);

// echo($composer->version);
// die();
//$css = file_get_contents("./src/styles.css");
$js = file_get_contents("./src/script.js");
$css = minify("./src/styles.css");
// $js = minify("./src/script.js");



$js = str_replace("</script>","",$js);

$mainFile = file_get_contents("./src/index.php");
$mainFile = str_replace("[version]",$composer->version,$mainFile);
$mainFile = str_replace("[CSS]",$css,$mainFile);
$mainFile = str_replace("//[JS]",$js,$mainFile);

deleteFile("./bin/styles.css");
deleteFile("./bin/script.js");
file_put_contents("./bin/index.php",$mainFile);

echo("AFTC: If all went well you should now have a bin directory with an index.php file you can use!");









// Utility functions
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

function minify($file){
    $handle = fopen($file, "r");
    $out = "";
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            // process the line read.
            //$line = preg_replace("/[\r\n\t]+/", " ", $line); // Remove newlines & tabs
            $i;
            for ($i=0; $i<10; $i++){
                $line = str_replace("\n","",$line); // Minification step
                $line = str_replace("\t","",$line); // Minification step
                $line = str_replace("    ","",$line); // Minification step
                $line = str_replace("  ","",$line); // Minification step
            }
            $line = str_replace("  ","",$line); // Minification step
            $isComment = strpos($line,"//");
            if ($isComment === false){
                $out .= $line;
            }
        }

        fclose($handle);
    }

    return $out;
}




function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        return false;
        //throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
    return true;
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
function copyFolderAndContents($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                copyFolderAndContents($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
function deleteFile($file){
    if (file_exists($file)){
        unlink($file);
        return true;
    } else {
        return false;
    }
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -




?>