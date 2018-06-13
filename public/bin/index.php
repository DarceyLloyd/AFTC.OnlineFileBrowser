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


//        trace("this->url = " . $this->url);
//        trace("this->browser_title = " . $this->browser_title);
//        trace("this->aftc_browser_file_name = " . $this->aftc_browser_file_name);
//        trace("this->folder_path_is_set = " . $this->folder_path_is_set);
//        trace("this->nav_path = " . $this->nav_path);
//        trace("this->dir = " . $this->dir);
//        trace("this->current_file = " . $this->current_file);

        // Check server path with url path is a valid dir or file
        // print_r("this->dir = " . $this->dir . "\n");

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
                        $in = " GB";
                        $size = $osize / 1024 / 1024 / 1024;
                    } else if ($osize > 1048576) {
                        // MB
                        $in = " mb";
                        $size = $osize / 1024 / 1024;
                    } else {
                        // KB
                        $in = " kb";
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
            $directory = str_replace($this->url . "\\", "", $value);
            $nice_name = $directory;

            if ($this->nav_path != "") {
                $link = $this->url . "?f=" . urlencode($this->nav_path) . "/" . urlencode($directory);
            } else {
                $link = $this->url . "?f=" . urlencode($directory);
            }

            $html_link = "<a href='" . $link . "' class='file-link'>" . $directory . "</a>";

            //trace($link);
            $html .= "<tr>\n";
            // $html .= "<td class='list-col list-col1 btn' onclick='navigateToFolder(\"" . $link . "\");'>" . $link . " - " . $directory . "</td>\n";
            // $html .= "<td class='list-col list-col1 btn' onclick='navigateToFolder(\"" . $link . "\");'>" . $directory . "</td>\n";
            $html .= "<td class='list-col list-col1 btn'>" . $html_link . "</td>\n";
            
            $html .= "</tr>\n";
        }

        echo($html);
    }
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -


    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public function listFiles()
    {
        foreach ($this->files as $key => $value) {
            $file = str_replace($this->url . "\\", "", $value);
            $nice_name = $file;
            $file = htmlspecialchars($file, ENT_QUOTES);
            if ($this->nav_path != "") {
                $link = $this->url . str_replace($this->current_file, "", $this->nav_path) . "/" . $file;
            } else {
                $link = $this->url . $file;
            }

            if (OPEN_FILES_IN_NEW_TAB) {
                $html_link = "<a href='" . $link . "' target='_blank' class='file-link'>" . $nice_name . "</a>";
            } else {
                $html_link = "<a href='" . $link . "' class='file-link'>" . $nice_name . "</a>";
            }

            echo("<tr>\n");
            $title = "";
            //echo("<td class='list-col list-col1 btn' title='".$title."' onclick='navigateTo(\"" . $link . "\");'>" . $link . " - " . $file . "</td>\n");

            // echo("<td class='col-1 btn' title='".$title."' onclick='navigateTo(\"" . $link . "\");'>" . $html_link . "</td>\n");
            echo("<td class='col-1 btn' title='".$title."'>" . $html_link . "</td>\n");
            echo("<td class='col-2 file-size-col'>" . $this->file_sizes[$key] . "</td>\n");
            echo("</tr>\n");
        }
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
            <h1>AFTC - Online Directory &amp; File Browser</h1>
            <h2>For support email <a href="mailto:Darcey@aftc.io"
                                     target="_blank">Darcey@aftc.io</a></h2>
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


        <?php if (sizeof($aftc->files) > 0) { ?>
            <table id="list-table">
                <tr>
                    <th class="col-1">File names</th>
                    <th class="col-2">Size</th>
                </tr>
                <?php $aftc->listFiles(); ?>
            </table>
        <?php } ?>

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