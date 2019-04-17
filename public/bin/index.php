<?php

session_start();
// if (!is_writable(session_save_path())) {
//     trace('Session path "'.session_save_path().'" is not writable for PHP!');
// }

// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");
header('Content-language: en');
header("Content-Type: text/html");

// CONFIGURATION
define("OPEN_FILES_IN_NEW_TAB", true);



// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
class AFTCDirBrowser
{
    // Configuration options
    public $enable_self_update = true;

    public $secure = false;

    // Please change the next 3 lines per each use of this file browser (if in sercure mode)
    public $username = "Darcey";
    public $password = "1234";
    public $session_code = "010200100210275012974301928301927591287041283986589127509";
    private $loggedin = false;

    public $animate_bg = false;
    public $image_mode = false;
    public $hide_bg = false;

    public $local_version = "1.12";
    public $online_version = "";

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
        if ($this->secure) {
            if (getSession("aftc_file_browser_state") === $this->session_code) {
                $this->loggedin = true;
            }

            if ($_SERVER["REQUEST_METHOD"] !== "POST" && !$this->loggedin) {
                // Not posting and not logged in, show login form
                $html = "
                <html>
                    <head>
                        <title>Secure Online File Browser - Login</title>
                    </head>
                    <body>
                        <form id='frm' name='frm' method='POST'>
                        <b>Username: <input type='text' id='username' name='username'><br>
                        <b>Password: <input type='password' id='password' name='password'><br>
                        <button>LOGIN</button>
                        </form>
                    </body>
                </html>
                ";
                echo($html);
                die();
            } else if ($_SERVER["REQUEST_METHOD"] === "POST" && !$this->loggedin) {
                // POST and NOT LOGGED IN = Validate POST DATA FOR LOGIN
                if ((getPost("username") === $this->username) && (getPost("password") === $this->password)){
                    // Log them in
                    $_SESSION["aftc_file_browser_state"] = $this->session_code;
                } else {
                    $html = "
                    <html>
                    <body>
                    <h1>LOGIN FAILED</h1>
                    <p>Please refresh page to try again.</p>
                    </body>
                    </html>";
                    echo($html);
                    die();
                }
            } else if ($this->loggedin){

            }

        }

        // Ensure we dont accidentally update src
        // NOTE TO SELF: ???????
        $GoodChanceOfUpdatingSrc = false;
        if (file_exists("./script.js") && file_exists("./styles.js") && file_exists("./.gitignore")) {
            $GoodChanceOfUpdatingSrc = true;
        }


        // Check auto update
        $isLocal = false;
        
        // Disable auto update on local host

        if (substr($_SERVER['REMOTE_ADDR'], 0, 4) == '127.' || $_SERVER['REMOTE_ADDR'] == '::1') {
            $isLocal = true;
        }

        // Check auto update
        if (!$isLocal && $this->enable_self_update && !$GoodChanceOfUpdatingSrc) {
            $r = rand(0, 9999999);
            $online_cfg = file_get_contents("https://raw.githubusercontent.com/DarceyLloyd/AFTC.OnlineFileBrowser/master/public/composer.json?v=" . $r);
            $online_cfg = json_decode($online_cfg);
            $this->online_version = (double) $online_cfg->version;
            $this->local_version = (double) $this->local_version;
            // trace("online_version = " . $this->online_version);
            // trace("local_version = " . $this->local_version);
            // trace(gettype($this->online_version)); // double
            // trace(gettype($this->local_version)); // double
            // die();
            // Check datatypes
            if (gettype($this->online_version) == "double" && gettype($this->online_version) == "double") {
                // check if newer online
                if ($this->online_version > $this->local_version) {
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
                    echo ($html);

                    $update = file_get_contents("https://raw.githubusercontent.com/DarceyLloyd/AFTC.OnlineFileBrowser/master/public/bin/index.php");
                    file_put_contents("./index.php", $update);
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
            ".ftpquota",
        );

        // Partial ignore rules
        $this->partial_filters = array(
            "-hide-",
            ".trk",
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
                array_push($this->crumbs, $crumb);
            }
        }

        if (is_dir($this->dir)) {
            if (file_exists($this->dir)) {
                // We have a directory!
            } else {
                echo ("AFTC Directory Browser - Directory no longer exists");
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
            echo ("AFTC Directory Browser - URL / File no longer exists");
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
                    if ($osize < 0) {
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
                    $size = number_format((float) $size, 1, '.', '');
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
            if ($this->no_of_bits > 0) {
                $link = $this->crumbs[$this->no_of_bits - 1]["link"];
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

        echo ($html);
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

                if ($this->image_mode) {

                    $info = new SplFileInfo($fileName);
                    $ext = strtolower($info->getExtension());
                    $isImage = false;
                    if ($ext == "gif" || $ext == "jpg" || $ext == "png" || $ext == "bmp" || $ext == "jpeg" || $ext == "svg") {
                        $isImage = true;
                    }

                    $img = "";
                    if ($isImage) {
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
        echo ($html);

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
        echo ($html);
    }
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
if (isset($_GET["phpinfo"])) {
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
        <title><?php echo ($aftc->browser_title); ?></title>
        <meta description="AFTC Directory & File Browser - By Darcey@aftc.io"/>
        <meta author="Darcey@aftc.io"/>
        <?php
$pos = strrpos(__DIR__, "src");
if ($pos === false) {
    //trace("BIN BUILD");
} else {
    //trace("SRC BUILD");
    echo ("<link rel='stylesheet' type='text/css' href='./styles.css'>\n");
}
?>
        <style>
            html, body {  font-family: Arial, Verdana, "Times New Roman";  background: #FFFFFF;  box-sizing: border-box; }       * {  -webkit-transition-duration: 250ms;  -moz-transition-duration: 250ms;  -o-transition-duration: 250ms;  transition-duration: 250ms; }       img {  display: block;  border: none;  outline: none; }        .preview {  max-width: 100px; }       .preview:hover {  max-width: 140px; }        .small {  font-size: 11px; }       .red {  color: #FF0000; }       .btn {  cursor: pointer; }           a {  text-decoration: underline; }       a:hover {  text-decoration: none; }       a:visited {  text-decoration: none; }                      #layer1 {  position: fixed;  z-index: 10;  left: 0;  top: 0;  width: 100%;  height: 100%;  /*opacity: 0;*/ }        #layer2 {  position: fixed;  z-index: 20;  left: 0;  top: 0;  width: 100%;  height: 100%;  /*opacity: 0;*/  /*background: url("tile.png");*/  background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEIAAABwCAYAAACq5qZOAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3FpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQyIDc5LjE2MDkyNCwgMjAxNy8wNy8xMy0wMTowNjozOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo2Zjg4MGU3Yy0zNjUyLThjNDQtYjg2Yi01NTliODQ2ZDMwNmQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NEUzNEU0RjEwM0I5MTFFOEExNTNBODIwNzk4QkVBODkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NEUzNEU0RjAwM0I5MTFFOEExNTNBODIwNzk4QkVBODkiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjZmODgwZTdjLTM2NTItOGM0NC1iODZiLTU1OWI4NDZkMzA2ZCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo2Zjg4MGU3Yy0zNjUyLThjNDQtYjg2Yi01NTliODQ2ZDMwNmQiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7dZQncAAAEhElEQVR42uydyU8UQRTGa4g3VFATMNFxS1BRR4Oo0TCJCkcNJm53N4STMXEjasSIK9484P4HKKASOeKSQMAFXCBC1CDOqFESd+/6FXllyk4zzvR0dw0z7yVfCG8yQ/evq+pVVfc3BCKRyG9hNnZDg9ANkweRJTgYBINgEAyCQTAIBsEgGASDYBAMgkEwCAbhb4xJgWNog6KZ3iKGoMfQJ+hVpoKQEKq036tMwjAFogYKQU1arhWaDVVCP9MdRDu0EDpKLcIuLkDToYPpCOIBtBwKQz2Uy4b2QDehDqgOmkWvfYVOELR2Pw4w4PF2/lM62VZLfgt0Fppo855qqB76ruWWQOcI5qhqEf3QOqjIAmE99Aa6OgIEGSeht9AOLScrywqoFOoaDSDeQ5uhQqhZy6+hLtEIzYjjc3Kgi1AE2qjl71LrWAv1pSKIX1T+pkLXtfxKupq3oQUOPjdIn9dHMFW0QPMIUiRVQBymq3xeyxXRAHgPKnbhb8wlmF3UPVQ0UoWR3eibKRCqFNZCnyk3EzoDdXs0sC2mMUdCXqrlLxOQA36C6ISWWUrheGg/NADt9aHaScgPaZ0SotwP6DSV4DovQcirXEaj9yMtv41G+VMGZqgl0HOafKkqJKvSPmo9nW6C6KdRWvb1O1p+AwGQzTLX8OKtlo6lUss9oYu2Kp6SGwuEXBpvolLYYimFL6AGaFoKbSmMpYnYOzpuFfe1ktubCAg5o6ugk2zQ8qu1UliYwnssU6BrVHLLLSU3RC158H8gqmn0vWSZ3nZQtygWoydkyb1FXaRMyzdRddsKfdHXGm00+tZTP1MRolyJSI+QC79d9FPvTnIiGJYgrG+YQC8eF+kZ7XR+w6U/GAzado2d1CrSFYJeco9AedYxQk2O5DR5nMiMqIGeRaPRch1EvcNF0WiPyXTuw4NlLu0IZXIUZMW5P5DuEeY7XS5vzDAIBsEgGASDYBAMgkEwCAbBwSAYBINgEAyCQTAIBsEgGASDYBAMgkEwCAbBIBhEioAYZAyiTYKQT7X3ZjCED8Fg8LXqGlUZCuMjnfvfMUI95W7EamgoaqBFaA3NdoOlEauhz2FrueQnb7Unb3W7oxGroccRl+XS2jWU1bBCy3luNfQoErJc2k2ocmis8M1q6HLEslzKymhruYw1s/TNauhSxGO5nJ/MFFtZDbuFvdVwu0jSauhCHKJjcWy5TGStofqa1Wp4RSRpNXShFEo3gTKhOLJcOll0uW41dBCuWy6TWX0q34NslpMo58hqmECMZLncLpK0XLqxDD9GK1jHVsM4S2Esy6X0oCVluXRrPyIpq2GM8M1y6fbGjLIayisYt9XQJkayXJYKjyyXXu1QzREJWA0tEcty2So8slwGfPqmdLsFT7Za8ED52sJvwMTCL+DzV8b/YzWMEb5bLv3evLW1GlrCiOXS5Bdy9dBKUIUcS14KQ5bLgOH/pjBE44MgCAWmDsT0fY08qgj5JiHISIWvcQyLFLi3wne6GASDYBAMgkEwCAbBIBgEg2AQDIJBMAjf4o8AAwBN0i+PtkNW3AAAAABJRU5ErkJggg==');  background-repeat: repeat; }       #layer3 {  position: relative;  z-index: 30;  left: 0;  top: 0;  width: 100%;  /*padding: 10px;*/  /*opacity: 0;*/ }             #header {  background: RGBA(50,0,0,0.85);  color: #FFFFFF;  padding: 10px; }       #header h1 {  font-size: 18px;  margin: 0;  padding: 0; }       #header h2 {  font-size: 11px;  color: #CCCCCC;  margin: 0;  padding: 0; }       #header a {  color: #FFCC00;  text-decoration: underline; }      #header a:hover {  color: #FFFF00; }      #header a:before {  content: "";  position: absolute;  width: 100%;  height: 2px;  bottom: 0;  left: 0;  background-color: #000;  visibility: hidden;  -webkit-transform: scaleX(0);  transform: scaleX(0);  -webkit-transition: all 0.3s ease-in-out 0s;  transition: all 0.3s ease-in-out 0s; }            #location {  }       #location h3 {  font-size: 14px; }       #location ul {  margin: 0,0,10px,0;  padding: 0;  list-style: none; }       #location ul li {  display: inline-block;  background: #990000;  color: #FFFFFF;  margin-bottom: 10px; }       #location ul li a {  font-size: 12px;  border-radius: 4px;  border: 1px solid #660000;  padding: 4px;  background: #990000;  color: #FFFFFF;  text-decoration: none; }       #location ul li a:hover {  text-decoration: underline;  background: #DD0000; }        .file-list-icon {  width: 64px;  height: 64px; }                  #list-table {  width: 100%;  text-align: left;  user-select: none; }       #list-table th {  padding: 5px 10px 5px 5px;  font-size: 16px;  font-weight: bold;  color: #FFFFFF;  background: RGBA(0,0,0,0.9); }       #list-table .col-head-1 {  }       #list-table .col-head-2 {  width: 85px;  padding: 5px 10px 5px 5px; }       #list-table .col-list-1 {  }       #list-table .col-list-2 {  width: 80px;  display: block;  font-size: 18px;  padding: 5px 10px 5px 10px;  font-size: 14px;  background: RGBA(200,200,200,0.8);  color: #000000; }       #list-table th, #list-table td {  }            .img-container {  display: inline-block;  width: 100px !important;  height: 100px !important;  overflow: hidden;  background: RGBA(255,255,255,0.8);  margin-right: 10px !important; }         .bg-container {  width: 100px !important;  height: 100px !important;  transform-origin: 50% 50%;  width: 100px !important;  background-size: cover;  background-origin: 50% 50%; }       .bg-container:hover {  transform: scale(1.2); }         .img-container .img-preview {  transform-origin: 50% 50%;  width: 100px !important;  }       .img-container .img-preview:hover {  transform: scale(1.2); }              .up-link {  font-size: 12px !important;  font-weight: bold;  font-style: italic;  cursor: pointer;  display: block;  padding: 5px 10px 5px 15px;  font-size: 14px;  background: RGBA(200,200,200,0.7);  text-decoration: none;  color: #000000; }       .up-link:hover {  color: #FFFFFF;  background: RGBA(50,0,0,0.95);  text-decoration: none; }           .no-files {  display: block;  padding: 5px 10px 5px 15px;  margin: 0;  font-weight: bold;  font-size: 14px;  background: RGBA(200,200,200,0.8);  color: #000000;  text-decoration: none; }          .dir-link {  display: block;  padding: 5px 10px 5px 15px;  margin: 0;  font-weight: bold;  font-size: 14px;  background: RGBA(200,200,200,0.8);  color: #000000;  text-decoration: none; }       .dir-link:hover {  color: #FFFFFF;  background: RGBA(50,0,0,0.95);  text-decoration: none; }             .list-link {  display: block;  font-size: 18px;  padding: 5px 10px 5px 15px;  margin: 0;  font-size: 14px;  background: RGBA(200,200,200,0.8);  color: #000000;  text-decoration: none; }       .list-link:hover {  color: #FFFFFF;  background: RGBA(50,0,0,0.95);  text-decoration: none; }              #footer {  margin-top: 10px;  margin-bottom: 20px;  padding: 10px;  background: RGBA(50,0,0,0.85);  font-size: 11px;  color: #FFFFFF; }       #footer a {  color: #FFCC00;  text-decoration: underline; }       #footer a:hover {  text-decoration: none; }               @media (min-width: 1px) and (max-width: 700px) { }       @media (max-width: 640px) {   #list-table .col-head-2 {  display: none;  }        #list-table .col-list-2 {  display: none;  }        #list-table .col-2 {  padding: 2px;  width: 65px;  font-size: 14px;  text-align: center;  }         #list-table th {  font-size: 18px;  font-weight: bold;  color: #FFFFFF;   }        #list-table td a {  font-size: 18px;  }        .file-size-col {  font-weight: bold;  font-size: 12px !important;  }        .crumb {  font-size: 14px !important;  }       }            #canvas1 {  width: 100%; height: 100%; }      
        </style>

        <script>
            function log(arg){ console.log(arg); }

            var hideBg = <?php outBoolean($aftc->hide_bg);?>;
            var imageMode = <?php outBoolean($aftc->image_mode);?>;
            var animateBg = <?php outBoolean($aftc->animate_bg);?>;
            var OpenFilesInNewTab = <?php outBoolean(OPEN_FILES_IN_NEW_TAB);?>;
            
            function init(){                
                if (hideBg){
                    document.getElementById("layer1").style.display = "none";
                    document.getElementById("layer2").style.display = "none";
                }
            }

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
    <body onload="init()">

    <div id="layer1">
        <canvas id="canvas1"></canvas>
    </div>

    <div id="layer2">
        <canvas id="canvas2"></canvas>
    </div>

    <div id="layer3">
        <div id="header">
            <h1>AFTC - Online File Browser <span style='font-size:9px;'>v<?php echo ($aftc->local_version); ?></span></h1>
            <h2>For support email <a href="mailto:Darcey@aftc.io" target="_blank">Darcey@aftc.io</a></h2>
        </div>

        <div id="debug"></div>


        <div id="location">
            <?php
$aftc->ouputBreadcrumbs();
?>
        </div>

        <?php if (sizeof($aftc->dirs) > 0 || $aftc->folder_path_is_set) {?>
            <table id="list-table">
                <tr>
                    <th>Directories</th>
                </tr>
                <?php $aftc->listDirectories();?>
            </table>
        <?php }?>

        <table width='100%' border='0' cellspacing='1' cellpadding='0' id='list-table'>
        <tr>
            <th class='col-head-1'>Files</th>
            <th class='col-head-2'>Size</th>
        </tr>
        <?php $aftc->listFiles();?>
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
    echo ("<script src=\"script.js\" type=\"text/javascript\"></script>\n");
}
?>


    <script>
        ;function log(t){if(console){console.log(t)}};window.isMobile=function(){var t=navigator.userAgent.toLowerCase();if(/windows phone/i.test(t)){return!0}
else{if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){return!0}
else{return!1}}};var AFTCFileBrowser=function(e,o,r){if(isMobile()){return};var t={canvas1:null,ctx1:null,w:0,h:0,halfW:0,halfh:0,mousePos:{},t:0,x:0,y:0};function i(){t.canvas1=document.getElementById('canvas1');t.ctx1=t.canvas1.getContext('2d');t.w=(t.canvas1.width=window.innerWidth);t.h=(t.canvas1.height=window.innerHeight);t.halfW=t.w/2;t.halfH=t.h/2;t.mousePos.x=t.halfW;t.mousePos.y=t.halfH;if(o){if(typeof window.orientation==='undefined'){window.addEventListener('mousemove',n);n()}
else{a()};window.addEventListener('resize',function(){t.w=(t.canvas1.width=window.innerWidth);t.h=(t.canvas1.height=window.innerHeight);t.halfW=t.w/2;t.halfH=t.h/2})};if(e){var r=document.getElementsByClassName('img-container');for(var i=0;i<r.length;i++){var l=r[i],s=l.getElementsByClassName('bg-container')[0],h=l.getAttribute('data-link');s.style.backgroundImage='url("'+h+'")'}}};function a(){window.requestAnimationFrame(a);t.t+=0.1;t.x=(t.halfW)+Math.floor(Math.sin(t.t/5)*t.halfW);t.y=(t.halfH)+Math.floor(Math.cos(t.t/15)*t.halfH);t.ctx1.clearRect(0,0,t.w,t.h);var o=400+Math.floor(Math.cos(t.t/3)*200),e=t.ctx1.createRadialGradient(t.x,t.y,1,t.x,t.y,o),n=0+Math.floor(Math.cos(t.t/100)*360),i=0+Math.floor(Math.cos((t.t+404)/100)*360);e.addColorStop(1,'hsla('+n+',100%,70%,0)');e.addColorStop(0,'hsla('+i+',100%,40%,1)');t.ctx1.fillStyle=e;t.ctx1.arc(t.x,t.y,o,0,Math.PI*2,!1);t.ctx1.fill()};function n(e){if(e){t.mousePos=l(t.canvas1,e)};t.t+=0.1;t.ctx1.clearRect(0,0,t.w,t.h);t.ctx1.beginPath();var a=600+Math.floor(Math.sin(t.t/10)*200),o=t.ctx1.createRadialGradient(t.mousePos.x,t.mousePos.y,1,t.mousePos.x,t.mousePos.y,a),n=0+Math.floor(Math.cos(t.t/100)*360),i=0+Math.floor(Math.cos((t.t+404)/50)*360);o.addColorStop(1,'hsla('+n+',100%,80%,0)');o.addColorStop(0,'hsla('+i+',100%,80%,1)');t.ctx1.fillStyle=o;t.ctx1.arc(t.mousePos.x,t.mousePos.y,a,0,Math.PI*2,!1);t.ctx1.fill()};function l(t,o){var e=t.getBoundingClientRect(),a=t.width/e.width,n=t.height/e.height;return{x:(o.clientX-e.left)*a,y:(o.clientY-e.top)*n}};function s(t){return t*Math.PI/180};i()};new AFTCFileBrowser(imageMode,animateBg,OpenFilesInNewTab);
    </script>

    </body>
    </html>
<?php

// Function utilities
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
function trace($str)
{
    echo ($str . "<br>");
}

function isArrayInArray($string, $sub_strings)
{
    foreach ($sub_strings as $substr) {
        if (strpos($string, $substr) !== false) {
            return true;
        }
    }

    return false;
}

function outBoolean($arg)
{
    if ($arg) {
        echo ("true");
    } else {
        echo ("false");
    }
}

function getPost($index){
    if (isset($_POST[$index])){
        return $_POST[$index];
    } else {
        return null;
    }
}

function getSession($index){
    if (isset($_SESSION[$index])){
        return $_SESSION[$index];
    } else {
        return null;
    }
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

?>