<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-language: en');
header("Content-Type: text/html");

// CONFIGURATION
define("OPEN_FILES_IN_NEW_TAB", true);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
class AFTCDirBrowser
{
    public $local_version = "[version]";
    public $online_version = "";
    public $enable_self_update = true;
    public $image_mode = true;

    public $url;
    public $dir;
    public $browser_title;

    public $root;

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

    public $bits = [];
    public $no_of_bits = -1;
    public $crumbs = [];



    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public function __construct()
    {
        // Ensure we dont accidentally update src
        $GoodChanceOfUpdatingSrc = false;
        if (file_exists("./script.js") && file_exists("./styles.js") && file_exists("./.gitignore")){
            $GoodChanceOfUpdatingSrc = true;
        }

        // Check auto update
        if ($this->enable_self_update && !$GoodChanceOfUpdatingSrc){
            
            
            $online_cfg = file_get_contents("https://raw.githubusercontent.com/DarceyLloyd/AFTC.OnlineFileBrowser/master/public/composer.json");
            $online_cfg = json_decode($online_cfg);
            $this->online_version = (double) $online_cfg->version;
            $this->local_version = (double) $this->local_version;
            // trace("online_version = " . $this->online_version);
            // trace("local_version = " . $this->local_version);
            // trace(gettype($this->online_version)); // double
            // trace(gettype($this->local_version)); // double
            // die();
            // Check datatypes
            if (gettype($this->online_version) == "double" && gettype($this->online_version) == "double"){
                // check if newer online
                if ($this->online_version > $this->local_version){
                    // trace("UPDATE AVAILABLE");
                    $html = "<html>";
                    $html .= "<head>";
                    $html .= "<style>";
                    $html .= "body { font-family: arial; font-size:14px; padding: 50px;}";
                    $html .= "</style>";
                    $html .= "<script>";
                    $html .= "function reload(){";
                    $html .= "self.location.href = self.location.href;";
                    $html .= "}";
                    $html .= "function init(){";
                    $html .= "setTimeout(reload,3000)";
                    $html .= "}";
                    $html .= "</script>";
                    $html .= "<body onload='init()'>";
                    $html .= "<div align='center'><h1>AFTC Online File Browser - Update Available!</h1></div>";
                    $html .= "<div align='center'><h3>Please wait... I am self updating...</h3></div>";
                    $html .= "<div align='center'>If you are not redirected shortly, please <a href='javascript:reload()'>click here</a>.</div>";
                    $html .= "<hr><div align='center'>If you are experiencing technical issues, please email <a href='mailto:Darcey@aftc.io'>Darcey@aftc.io</a>, detailing as your issue and how I can replicate your issue.</div>";
                    $html .= "";
                    $html .= "</body>";
                    $html .= "</head>";
                    $html .= "</html>";
                    echo($html);

                    $update = file_get_contents("https://raw.githubusercontent.com/DarceyLloyd/AFTC.OnlineFileBrowser/master/public/bin/index.php");
                    file_put_contents("./index.php",$update);
                    die();
                }
            }
            
        }

        // Fully qualified names ignore rules
        $this->name_filters = array(
            ".htaccess",
            ".htpasswd",
            ".well-known",
            "cgi-bin",
            ".ftpquota"
        );

        // Partial ignore rules
        $this->partial_filters = array(
            "-hide-",
            ".trk"
        );


        //$this->protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
        $this->protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        //$this->url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->url = $this->protocol . "://$_SERVER[HTTP_HOST]" . strtok($_SERVER["REQUEST_URI"], '?');
        $this->root = $this->url;
        $this->browser_title = "AFTC Dir & File Browser - Darcey@aftc.io";
        $this->aftc_browser_file_name = basename($_SERVER["SCRIPT_FILENAME"]);

        if (isset($_GET['f'])) {
            $this->folder_path_is_set = true;

            $this->nav_path = urldecode($_GET['f']);
            $this->nav_path = str_replace("?f=", "", $this->nav_path);
            //print_r("_GET['f'] = " . $_GET['f'] . "\n");
            //print_r("this->nav_path = " . $this->nav_path . "\n");

            $this->dir = getcwd() . "/" . $_GET['f'];
            $this->dir = str_replace("../", "", $this->dir);
        } else {
            $this->nav_path = "";
            $this->dir = getcwd();

            // In root dir we dont want index.php to show as it's this file!
            array_push($this->name_filters, "index.php");
        }


        $this->current_file_path_parts = pathinfo(__FILE__);
        $this->current_file = $this->current_file_path_parts['basename'];
        $this->url = str_replace($this->current_file, "", $this->url);


        // Build breadcrumbs here as the parts array is used in multiple places
        if (($this->folder_path_is_set == true)) {
            $this->bits = explode("/", $this->nav_path);
            $this->no_of_bits = sizeof($this->bits) - 1;

            for ($i = 0; $i <= $this->no_of_bits; $i++) {
                //trace($i . " = " . $this->bits[$i]);

                $crumb = [];
                $crumb["label"] = $this->bits[$i];
                $crumb["link"] = "?f=";

                // Build link
                for ($l = 0; $l <= $i; $l++) {
                    if ($l == 0) {
                        // Root
                        $crumb["link"] = $crumb["link"] . urlencode($this->bits[$l]);
                    } else {
                        $crumb["link"] = $crumb["link"] . "/" . urlencode($this->bits[$l]);
                    }
                }
                array_push($this->crumbs,$crumb);
            }
        }

        if (is_dir($this->dir)) {
            if (file_exists($this->dir)) {
                // We have a directory!
            } else {
                echo("AFTC Directory Browser - Directory no longer exists");
                die;
            }

        } else if (is_file($this->dir)) {
            // we have a file!
            $target = ($this - url . $this->nav_path);
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
        $this->dir_data = array_diff(scandir($this->dir), array('..', '.', '../'));
        $this->dir_data = array_diff($this->dir_data, $this->name_filters);

        foreach ($this->dir_data as $value) {
            //echo($key . " = " . $value . "<br>");
            //echo("<br>");
            $found = isArrayInArray($value, $this->partial_filters);
            if (!$found) {

                $item = $this->dir . "/" . $value;
                //echo("<b>CHECKING TO SEE IF [" . $item . "] IS A DIR</b><br>");
                if (is_dir($item)) {
                    //echo("<b>YES IT IS A DIR!</b><br>");
                    array_push($this->dirs, $value);
                } else {
                    //echo("<b>NO IT IS A FILE!</b><br>");
                    array_push($this->files, $value);
                    $file = $this->dir . "/" . $value;
                    $osize = filesize($file);
                    if ($osize < 0){
                        $osize = 0 - $osize;
                    }

                    $in = "";
                    if ($osize > 1073741824) {
                        // GB
                        $in = "GB";
                        $size = $osize / 1024 / 1024 / 1024;
                    } else if ($osize > 1048576) {
                        // MB
                        $in = "Mb";
                        $size = $osize / 1024 / 1024;
                    } else {
                        // KB
                        $in = "kb";
                        $size = $osize / 1024;
                    }
                    $size = number_format((float)$size, 1, '.', '');
                    $size = $size . $in;


                    array_push($this->file_sizes, $size);
                    //var_dump($this->dir . "\\" . $value);
                }
            }


        }

        natcasesort($this->dirs);
        natcasesort($this->files);

    }
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public function listDirectories()
    {
        $html = "";
        if (($this->folder_path_is_set == true)) {
            if ($this->no_of_bits > 0){
                $link = $this->crumbs[$this->no_of_bits-1]["link"];
            } else {
                $link = $this->url;
            }
            $html .= "<tr>\n";
            $html .= "<td class='up-link' onclick='navigateToFolder(\"" . $link . "\");'>[up] </td>\n";
            $html .= "</tr>\n";
        }

        foreach ($this->dirs as $key => $value) {
            //echo($key . " = " . $value . "<br>");
            $dirName = str_replace($this->url . "\\", "", $value);

            if ($this->nav_path != "") {
                $link = $this->url . "?f=" . urlencode($this->nav_path) . "/" . urlencode($dirName);
            } else {
                $link = $this->url . "?f=" . urlencode($dirName);
            }

            $html_link = "<a href='" . $link . "' class='dir-link'>" . $dirName . "</a>";

            //trace($link);
            $html .= "<tr>\n";
            // $html .= "<td class='list-col list-col1 btn' onclick='navigateToFolder(\"" . $link . "\");'>" . $link . " - " . $directory . "</td>\n";
            // $html .= "<td class='list-col list-col1 btn' onclick='navigateToFolder(\"" . $link . "\");'>" . $directory . "</td>\n";
            $html .= "<td class=''>" . $html_link . "</td>\n";
            
            $html .= "</tr>\n";
        }

        echo($html);
    }
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public function listFiles()
    {
        $html = "";
        

        if (sizeof($this->files) > 0) {
            // There be files here!

            $img = "";
            $cnt = 0;
            foreach ($this->files as $key => $value) {
                $fileName = str_replace($this->url . "\\", "", $value);
                $fileName = htmlspecialchars($fileName, ENT_QUOTES);
                if ($this->nav_path != "") {
                    $link = $this->url . str_replace($this->current_file, "", $this->nav_path) . "/" . $fileName;
                } else {
                    $link = $this->url . $fileName;
                }

                // trace("this->current_file = " . $this->current_file);
                // trace("fileName = " . $fileName);
            
                $target = "";
                if (OPEN_FILES_IN_NEW_TAB) {
                    $target = "target='_blank'";
                }


                if ($this->image_mode){
            
                $info = new SplFileInfo($fileName);
                $ext = strtolower( $info->getExtension() );
                $isImage = false;
                if ($ext == "gif" || $ext == "jpg" || $ext == "png" || $ext == "bmp" || $ext == "jpeg" || $ext == "svg"){
                    $isImage = true;
                }

                $img = "";
                if ($isImage){
                    // Image and file links
                    // $img = "<div class='img-container'><image src='" . $link . "' class='img-preview' /></div>";
                    $cnt++;
                    $uid = "img" . $cnt;
                    $img = "<div class='img-container' data-link='" . $link . "'><div class='bg-container'></div></div>";
                }
            }

            $linkS = "<a href='" . $link . "' " . $target . " class='list-link'>";
            $linkE = "</a>\n";

            $html .= "<tr>\n";
                $html .= "<td class='col-list-1'>" . $linkS . $img . $fileName . $linkE . "</td>\n";
                $html .= "<td class='col-list-2'>" . $this->file_sizes[$key] . "</td>\n";
            $html .= "</tr>\n";

            } // end foreach
            

        } else {
            // Nothing to see here, move along...
            $html .= "<tr>\n";
                $html .= "<td class='col-list-1'><h3 class='no-files'>No files found</h3></td>\n";
                $html .= "<td class='col-list-2'>&nbsp;</td>\n";
            $html .= "</tr>\n";
        }
        echo($html);
        
    }
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public function ouputBreadcrumbs()
    {
        $html = "";
        //$html = "<h3>Location:</h3>";
        $html .= "<ul>\n";
        $html .= "\t<li><a class='crumb' href='" . $this->url . "'>Root</a></li>\n";


        if (($this->folder_path_is_set == true)) {

            $bits = explode("/", $this->nav_path);
            $no_of_bits = sizeof($bits) - 1;
            //trace("no-of-bits = " . $no_of_bits);

            for ($i = 0; $i <= $no_of_bits; $i++) {
                //trace($i . " = " . $bits[$i]);
                $label = $bits[$i];
                $link = $this->url . "?f=";
                // Build link
                for ($l = 0; $l <= $i; $l++) {
                    if ($l == 0) {
                        //$link = $link . $bits[$l];
                        $link = $link . urlencode($bits[$l]);
                    } else {
                        $link = $link . "/" . $bits[$l];
                    }
                }
                $html .= "\t<li><a class='crumb btn' href='" . $link . "'>" . $label . "</a></li>\n";
            }
        }

        $html .= "</ul>\n";
        echo($html);
    }
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
if (isSet($_GET["phpinfo"])) {
    if ($_GET["phpinfo"] == "1") {
        phpinfo();
        die;
    }
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
$aftc = new AFTCDirBrowser();
//var_dump($aftc);
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
        <meta name="google" content="notranslate">
        <meta name="robots" content="noindex">
        <meta name="language" content="English">
        <meta http-equiv="content-language" content="en">
        <title><?php echo($aftc->browser_title); ?></title>
        <meta description="AFTC Directory & File Browser - By Darcey@aftc.io"/>
        <meta author="Darcey@aftc.io"/>
        <?php
        $pos = strrpos(__DIR__, "src");
        if ($pos === false) {
            //trace("BIN BUILD");
        } else {
            //trace("SRC BUILD");
            echo("<link rel='stylesheet' type='text/css' href='./styles.css'>\n");
        }
        ?>
        <style>
            [CSS]
        </style>

        <script>
            <?php
            // PHP > JS
            $var = "var OpenFilesInNewTab = ";
            if (OPEN_FILES_IN_NEW_TAB) {
                $var .= "true;\n";
            } else {
                $var .= "false;\n";
            }
            echo($var);
            ?>

            function navigateTo(url) {
                if (!OpenFilesInNewTab) {
                    self.location.href = url;
                } else {
                    var win = window.open(url, '_blank');
                    win.focus();
                }
            }

            function navigateToFolder(url) {
                self.location.href = url;
            }
        </script>
    </head>
    <body>

    <div id="layer1">
        <canvas id="canvas1"></canvas>
    </div>

    <div id="layer2">
        <canvas id="canvas2"></canvas>
    </div>

    <div id="layer3">
        <div id="header">
            <h1>AFTC - Online File Browser V<?php echo($aftc->local_version); ?></h1>
            <h2>For support email <a href="mailto:Darcey@aftc.io" target="_blank">Darcey@aftc.io</a></h2>
        </div>

        <div id="debug"></div>


        <div id="location">
            <?php
            $aftc->ouputBreadcrumbs();
            ?>
        </div>

        <?php if ( sizeof($aftc->dirs) > 0 || $aftc->folder_path_is_set ){ ?>
            <table id="list-table">
                <tr>
                    <th>Directories</th>
                </tr>
                <?php $aftc->listDirectories(); ?>
            </table>
        <?php } ?>

        <table width='100%' border='0' cellspacing='1' cellpadding='0' id='list-table'>
        <tr>
            <th class='col-head-1'>File name</th>
            <th class='col-head-2'>Size</th>
        </tr>
        <?php $aftc->listFiles(); ?>
        </table>

        <div id="footer">
            &copy; Data &amp; Dreams LTD | email: <a href="mailto:darcey@aftc.io" target="_blank">Darcey@aftc.io</a>
        </div>

    </div>


    <?php
    $pos = strrpos(__DIR__, "src");
    if ($pos === false) {
        //trace("BIN BUILD");
    } else {
        //trace("SRC BUILD");
        echo("<script src=\"script.js\" type=\"text/javascript\"></script>\n");
    }
    ?>


    <script>
        //[JS]
    </script>

    </body>
    </html>
<?php

// Function utilities
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
function trace($str)
{
    echo($str . "<br>");
}


function isArrayInArray($string, $sub_strings)
{
    foreach ($sub_strings as $substr) {
        if (strpos($string, $substr) !== FALSE) {
            return true;
        }
    }

    return false;
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


?>