<?php

ini_set('memory_limit', '256M');
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

function debug_out($label, $message, $disable=false) {
    if (!$disable) {
        echo $label . ': "' . $message . '"</br>';
    }
}

function resize_image($file, $w, $h) {
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($w / $h > $r) {
        $newwidth = $h * $r;
        $newheight = $h;
    } else {
        $newheight = $w / $r;
        $newwidth = $w;
    }
    $src = imagecreatefromjpeg($file);
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    unset($src);

    return $dst;
}

function saveCurrentImageForDisplay($path) {
    $image = resize_image($path, 1200, 800);
    $save_path = path_prefix('save');
    imagejpeg($image, $save_path);
    imagedestroy($image);
    unset($image);
}

function arrayDirectories() {
    return removeArrayItemsByValue(scandir(path_prefix('scan')), array('.', '..', '.htaccess', 'current_image'));
}

function removeArrayItemsByValue($array, $values) {
    foreach ($values as $value) {
        $delete_key = array_search($value, $array);
        array_splice($array, $delete_key, 1);
    }
    return $array;
}

function arrayFilenames($dir) {
    $filenames = removeArrayItemsByValue(scandir(path_prefix('scan') . '/' . $dir), array('.', '..'));
    $jpgs = preg_grep("/.*(jpg|JPG)$/", $filenames);
    return $jpgs;
}

function currentFilename($dir) {
    $post_data = $_POST;
    if (array_key_exists('nextImageButton', $post_data)) {
        return $post_data['nextImage'];
    } elseif (array_key_exists('previousImageButton', $post_data)) {
        return $post_data['previousImage'];
    } else {
        return arrayFilenames($dir)[0];
    }
}

function nextFilename($currentFilename, $dir) {
    $filenames = arrayFilenames($dir);
    $current_key = array_search($currentFilename, $filenames);
    if ($current_key == count($filenames) - 1) {
        return $filenames[0];
    } else {
        return $filenames[$current_key + 1];
    }
}

function previousFilename($currentFilename, $dir) {
    $filenames = arrayFilenames($dir);
    $current_key = array_search($currentFilename, $filenames);
    if ($current_key == 0) {
        return $filenames[count($filenames) - 1];
    } else {
        return $filenames[$current_key - 1];
    }
}


function path_prefix($target) {
    if ($_ENV['USER'] == 'F3M3') {
        if ($target == 'images') {
            return 'http://localhost:8888/projects/php/pic_flip/images';
        } elseif ($target == 'scan') {
            return 'images';
        } elseif ($target == 'action') {
            return 'http://localhost:8888/projects/php/pic_flip';
        } elseif ($target == 'save') {
            return 'images/current_image/current_image.jpg';
        }
    } else {
        if ($target == 'images') {
            return '../imad';
        } elseif ($target == 'scan') {
            return '../imad';
        } elseif ($target == 'action') {
            return 'http://www.f3mmedia.com/pic_flip';
        } elseif ($target == 'save') {
            return '../imad/current_image/current_image.jpg';
        }
    }
}

function dirSelectHtml() {
    $inputs_html = '';
    foreach(arrayDirectories() as $dir_name) {
        $inputs_html .= '<option value="' . $dir_name . '">' . $dir_name . '</option>';
    }
    $action_prefix = path_prefix('action');
    return <<<HTML
    <html>
        <head>
        </head>
        <body>
            <form action="$action_prefix/pic_flip.php" method="post">
                <select name="dir">
                    $inputs_html
                </select>
                <input type="submit" name="go_to_dir" value="Go to directory" />
            </form>
        </body>
    </html>
HTML;

}

function picFlipBrowserHtml() {
    $currentDir = $_POST['dir'];
    $currentFilename = currentFilename($currentDir);
    $nextFilename = nextFilename($currentFilename, $currentDir);
    $previousFilename = previousFilename($currentFilename, $currentDir);
    $images_prefix = path_prefix('images');
    $action_prefix = path_prefix('action');
    saveCurrentImageForDisplay($images_prefix . '/' . $currentDir . '/' . $currentFilename);
    $html = <<<HTML
        <html>
            <head>
            </head>
            <body>
                <h1>$currentFilename</h1>
                <a href="$action_prefix/pic_flip.php">return to dir list</a>
                <form action="$action_prefix/pic_flip.php" method="post">
                    <input type="hidden" name="nextImage" value="$nextFilename" />
                    <input type="hidden" name="previousImage" value="$previousFilename" />
                    <input type="hidden" name="dir" value="$currentDir" />

                    <input type="submit" name="previousImageButton" value="PREVIOUS"/>
                    <input type="submit" name="nextImageButton" value="NEXT"/>
                </form>
                <img src="$images_prefix/current_image/current_image.jpg" />
            </body>
        </html>
HTML;
    return $html;
}

function htmlOutput() {
    if (empty($_POST)) {
        return dirSelectHtml();
    } else {
        return picFlipBrowserHtml();
    }
}

$out = htmlOutput();
echo $out;
