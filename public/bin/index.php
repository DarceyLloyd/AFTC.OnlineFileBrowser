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
            ".htpasswd"
        );

        // Partial ignore rules
        $this->partial_filters = array(
            "-hide-"
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
            $this->nav_path = $_GET['f'];
            $this->nav_path = str_replace("?f=", "", $this->nav_path);
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
                        $crumb["link"] = $crumb["link"] . $this->bits[$l];
                    } else {
                        $crumb["link"] = $crumb["link"] . "/" . $this->bits[$l];
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

                    if ($osize > 1048576) {
                        $size = $osize / 1024 / 1024;
                        $size = number_format((float)$size, 1, '.', '');
                        $size = $size . "mb";
                    } else {
                        $size = $osize / 1024;
                        $size = number_format((float)$size, 1, '.', '');
                        $size = $size . "kb";
                    }


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
            $html .= "<td class='list-col list-col1 btn' onclick='navigateToFolder(\"" . $link . "\");'>[up]</td>\n";
            $html .= "</tr>\n";
        }

        foreach ($this->dirs as $key => $value) {
            //echo($key . " = " . $value . "<br>");
            $directory = str_replace($this->url . "\\", "", $value);
            if ($this->nav_path != "") {
                $link = $this->url . "?f=" . $this->nav_path . "/" . $directory;
            } else {
                $link = $this->url . "?f=" . $directory;
            }
            //trace($link);
            $html .= "<tr>\n";
            // $html .= "<td class='list-col list-col1 btn' onclick='navigateToFolder(\"" . $link . "\");'>" . $link . " - " . $directory . "</td>\n";
            $html .= "<td class='list-col list-col1 btn' onclick='navigateToFolder(\"" . $link . "\");'>" . $directory . "</td>\n";
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
            if ($this->nav_path != "") {
                $link = $this->url . str_replace($this->current_file, "", $this->nav_path) . "/" . $file;
            } else {
                $link = $this->url . $file;
            }

            if (OPEN_FILES_IN_NEW_TAB) {
                $html_link = "<a href='" . $link . "' target='_blank'>" . $file . "</a>";
            } else {
                $html_link = "<a href='" . $link . "'>" . $file . "</a>";
            }

            echo("<tr>\n");
            //echo("<td class='list-col list-col1 btn' title='CLICK TO OPEN / RIGHT CLICK SAVE AS TO SAVE THIS FILE' onclick='navigateTo(\"" . $link . "\");'>" . $link . " - " . $file . "</td>\n");
            echo("<td class='list-col list-col1 btn' title='CLICK TO OPEN / RIGHT CLICK SAVE AS TO SAVE THIS FILE' onclick='navigateTo(\"" . $link . "\");'>" . $file . "</td>\n");
            echo("<td class='list-col list-col2 btn'>" . $this->file_sizes[$key] . "</td>\n");
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
                        $link = $link . $bits[$l];
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
            html, body { font-family: Arial, Verdana, "Times New Roman"; background: #FFFFFF; }  * { -webkit-transition-duration: 250ms; -moz-transition-duration: 250ms; -o-transition-duration: 250ms; transition-duration: 250ms; }      .small { font-size: 11px; }  .red { color: #FF0000; }  .btn { cursor: pointer; }      a { text-decoration: underline; }  a:hover { text-decoration: none; }  a:visited { text-decoration: none; }                #layer1 { position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; /*opacity: 0;*/ }   #layer2 { position: fixed; z-index: 20; left: 0; top: 0; width: 100%; height: 100%; /*opacity: 0;*/ /*background: url("tile.png");*/ background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEIAAABwCAYAAACq5qZOAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3FpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQyIDc5LjE2MDkyNCwgMjAxNy8wNy8xMy0wMTowNjozOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo2Zjg4MGU3Yy0zNjUyLThjNDQtYjg2Yi01NTliODQ2ZDMwNmQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NEUzNEU0RjEwM0I5MTFFOEExNTNBODIwNzk4QkVBODkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NEUzNEU0RjAwM0I5MTFFOEExNTNBODIwNzk4QkVBODkiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjZmODgwZTdjLTM2NTItOGM0NC1iODZiLTU1OWI4NDZkMzA2ZCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo2Zjg4MGU3Yy0zNjUyLThjNDQtYjg2Yi01NTliODQ2ZDMwNmQiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7dZQncAAAEhElEQVR42uydyU8UQRTGa4g3VFATMNFxS1BRR4Oo0TCJCkcNJm53N4STMXEjasSIK9484P4HKKASOeKSQMAFXCBC1CDOqFESd+/6FXllyk4zzvR0dw0z7yVfCG8yQ/evq+pVVfc3BCKRyG9hNnZDg9ANkweRJTgYBINgEAyCQTAIBsEgGASDYBAMgkEwCAbhb4xJgWNog6KZ3iKGoMfQJ+hVpoKQEKq036tMwjAFogYKQU1arhWaDVVCP9MdRDu0EDpKLcIuLkDToYPpCOIBtBwKQz2Uy4b2QDehDqgOmkWvfYVOELR2Pw4w4PF2/lM62VZLfgt0Fppo855qqB76ruWWQOcI5qhqEf3QOqjIAmE99Aa6OgIEGSeht9AOLScrywqoFOoaDSDeQ5uhQqhZy6+hLtEIzYjjc3Kgi1AE2qjl71LrWAv1pSKIX1T+pkLXtfxKupq3oQUOPjdIn9dHMFW0QPMIUiRVQBymq3xeyxXRAHgPKnbhb8wlmF3UPVQ0UoWR3eibKRCqFNZCnyk3EzoDdXs0sC2mMUdCXqrlLxOQA36C6ISWWUrheGg/NADt9aHaScgPaZ0SotwP6DSV4DovQcirXEaj9yMtv41G+VMGZqgl0HOafKkqJKvSPmo9nW6C6KdRWvb1O1p+AwGQzTLX8OKtlo6lUss9oYu2Kp6SGwuEXBpvolLYYimFL6AGaFoKbSmMpYnYOzpuFfe1ktubCAg5o6ugk2zQ8qu1UliYwnssU6BrVHLLLSU3RC158H8gqmn0vWSZ3nZQtygWoydkyb1FXaRMyzdRddsKfdHXGm00+tZTP1MRolyJSI+QC79d9FPvTnIiGJYgrG+YQC8eF+kZ7XR+w6U/GAzado2d1CrSFYJeco9AedYxQk2O5DR5nMiMqIGeRaPRch1EvcNF0WiPyXTuw4NlLu0IZXIUZMW5P5DuEeY7XS5vzDAIBsEgGASDYBAMgkEwCAbBwSAYBINgEAyCQTAIBsEgGASDYBAMgkEwCAbBIBhEioAYZAyiTYKQT7X3ZjCED8Fg8LXqGlUZCuMjnfvfMUI95W7EamgoaqBFaA3NdoOlEauhz2FrueQnb7Unb3W7oxGroccRl+XS2jWU1bBCy3luNfQoErJc2k2ocmis8M1q6HLEslzKymhruYw1s/TNauhSxGO5nJ/MFFtZDbuFvdVwu0jSauhCHKJjcWy5TGStofqa1Wp4RSRpNXShFEo3gTKhOLJcOll0uW41dBCuWy6TWX0q34NslpMo58hqmECMZLncLpK0XLqxDD9GK1jHVsM4S2Esy6X0oCVluXRrPyIpq2GM8M1y6fbGjLIayisYt9XQJkayXJYKjyyXXu1QzREJWA0tEcty2So8slwGfPqmdLsFT7Za8ED52sJvwMTCL+DzV8b/YzWMEb5bLv3evLW1GlrCiOXS5Bdy9dBKUIUcS14KQ5bLgOH/pjBE44MgCAWmDsT0fY08qgj5JiHISIWvcQyLFLi3wne6GASDYBAMgkEwCAbBIBgEg2AQDIJBMAjf4o8AAwBN0i+PtkNW3AAAAABJRU5ErkJggg=='); background-repeat: repeat; }  #layer3 { position: relative; z-index: 30; left: 0; top: 0; width: 100%; /*padding: 10px;*/ /*opacity: 0;*/ }        #header { background: RGBA(50,0,0,0.85); color: #FFFFFF; padding: 10px; }  #header h1 { font-size: 18px; margin: 0; padding: 0; }  #header h2 { font-size: 12px; color: #CCCCCC; margin: 0; padding: 0; }  #header a { color: #FFCC00; text-decoration: underline; } #header a:hover { color: #FFFF00; } #header a:before { content: ""; position: absolute; width: 100%; height: 2px; bottom: 0; left: 0; background-color: #000; visibility: hidden; -webkit-transform: scaleX(0); transform: scaleX(0); -webkit-transition: all 0.3s ease-in-out 0s; transition: all 0.3s ease-in-out 0s; }       #location {  }  #location h3 { font-size: 14px; }  #location ul { margin: 0,0,10px,0; padding: 0; list-style: none; }  #location ul li { display: inline-block; background: #990000; color: #FFFFFF; }  #location ul li a { font-size: 12px; border-radius: 4px; border: 1px solid #660000; padding: 4px; background: #990000; color: #FFFFFF; text-decoration: none; }  #location ul li a:hover { text-decoration: underline; background: #DD0000; }          #dir-table { width: 100%; }  #dir-table td { padding: 5px; }  #dir-table .title-col { font-size: 16px; font-weight: bold; color: #FFFFFF; background: RGBA(0,0,0,0.9); text-align: left; }  #dir-table .title-col1 {  }  #dir-table .list-col { padding: 5px 5px 5px 25px; font-size: 14px; font-weight: bold; text-align: left; color: #000000; background: RGBA(150,150,150,0.9); } #dir-table .list-col:hover { color: #FFFFFF; background: RGBA(90,0,0,0.9); }  #dir-table .list-col1 {  }         #file-table { margin-top: 10px; width: 100%; }  #file-table td { padding: 5px; }  #file-table .title-col { font-size: 16px; font-weight: bold; color: #FFFFFF; background: RGBA(0,0,0,0.9); }  #file-table .title-col1 { text-align: left; }  #file-table .title-col2 { width: 70px; text-align: center; }  #file-table .list-col { padding: 5px 5px 5px 25px; font-size: 14px; font-weight: bold; text-align: left; color: #000000; background: RGBA(125,125,125,0.9); } #file-table .list-col:hover { color: #FFFFFF; background: RGBA(90,0,0,0.9); }  #file-table .list-col1 {  }  .file-list-icon { width: 64px; height: 64px; }      #footer { margin-top: 10px; padding: 10px; background: RGBA(50,0,0,0.85); font-size: 11px; color: #FFFFFF; opacity: 0.75; }  #footer a { color: #FFCC00; text-decoration: underline; }  #footer a:hover { text-decoration: none; }    @media (min-width: 1px) and (max-width: 700px) { }  @media (min-width: 601px) { }       #canvas1 { width: 100%; height: 100%; }
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
            <table id="dir-table">
                <tr>
                    <td class="title-col title-col1">Directories</td>
                </tr>
                <?php $aftc->listDirectories(); ?>
            </table>
        <?php } ?>


        <?php if (sizeof($aftc->files) > 0) { ?>
            <table id="file-table">
                <tr>
                    <td class="title-col title-col1">File names</td>
                    <td class="title-col title-col2">Size</td>
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
        function log(str) { console.log(str); }   var AFTCFileBrowserBackground = function () {  var params = { canvas1: null, ctx1: null, w: 0, h: 0, halfW: 0, halfh: 0, mousePos: {}, t: 0, x: 0, y: 0 };  function init() { params.canvas1 = document.getElementById('canvas1'); params.ctx1 = params.canvas1.getContext('2d'); params.w = (params.canvas1.width = window.innerWidth); params.h = (params.canvas1.height = window.innerHeight); params.halfW = params.w / 2; params.halfH = params.h / 2; params.mousePos.x = params.halfW; params.mousePos.y = params.halfH;    if (typeof window.orientation === 'undefined') { window.addEventListener("mousemove", canvasOnMouseMoveHandler); canvasOnMouseMoveHandler(); } else { animateForMobile(); }  window.addEventListener("resize", function () { params.w = (params.canvas1.width = window.innerWidth); params.h = (params.canvas1.height = window.innerHeight); params.halfW = params.w / 2; params.halfH = params.h / 2; });  }     function animateForMobile() { window.requestAnimationFrame(animateForMobile);  params.t += 0.1; params.x = (params.halfW) + Math.floor(Math.sin(params.t / 5) * params.halfW); params.y = (params.halfH) + Math.floor(Math.cos(params.t / 15) * params.halfH); params.ctx1.clearRect(0, 0, params.w, params.h);  var rad = 400 + Math.floor(Math.cos(params.t / 3) * 200); var grad = params.ctx1.createRadialGradient(params.x, params.y, 1, params.x, params.y, rad);   var h1 = 0 + Math.floor(Math.cos(params.t / 100) * 360); var h2 = 0 + Math.floor(Math.cos((params.t + 404) / 100) * 360); grad.addColorStop(1, 'hsla(' + h1 + ',100%,70%,0)'); grad.addColorStop(0, 'hsla(' + h2 + ',100%,40%,1)'); params.ctx1.fillStyle = grad; params.ctx1.arc(params.x, params.y, rad, 0, Math.PI * 2, false); params.ctx1.fill();  }   function canvasOnMouseMoveHandler(e) { if (e) { params.mousePos = getMousePos(params.canvas1, e); }  params.t += 0.1; params.ctx1.clearRect(0, 0, params.w, params.h); params.ctx1.beginPath(); var rad = 600 + Math.floor(Math.sin(params.t / 10) * 200); var grad = params.ctx1.createRadialGradient(params.mousePos.x, params.mousePos.y, 1, params.mousePos.x, params.mousePos.y, rad); var h1 = 0 + Math.floor(Math.cos(params.t / 100) * 360); var h2 = 0 + Math.floor(Math.cos((params.t + 404) / 50) * 360); grad.addColorStop(1, 'hsla(' + h1 + ',100%,80%,0)'); grad.addColorStop(0, 'hsla(' + h2 + ',100%,80%,1)'); params.ctx1.fillStyle = grad; params.ctx1.arc(params.mousePos.x, params.mousePos.y, rad, 0, Math.PI * 2, false); params.ctx1.fill();  }     function getMousePos(canvas, evt) { var rect = canvas.getBoundingClientRect(), scaleX = canvas.width / rect.width, scaleY = canvas.height / rect.height;  return { x: (evt.clientX - rect.left) * scaleX, y: (evt.clientY - rect.top) * scaleY } }  function degToRad(deg) { return deg * Math.PI / 180; }   init(); };                                        new AFTCFileBrowserBackground();     
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