<?php

function removeArrayItemsByValue($array, $values) {
    foreach ($values as $value) {
        $delete_key = array_search($value, $array);
        array_splice($array, $delete_key, 1);
    }
    return $array;
}

function arrayFilenames() {
    return removeArrayItemsByValue(scandir('images'), array('.', '..'));
}

function currentFilename() {
    $post_data = $_POST;
    if (empty($post_data)) {
        return arrayFilenames()[0];
    } elseif (array_key_exists('nextImageButton', $post_data)) {
        return $post_data['nextImage'];
    } else {
        return $post_data['previousImage'];
    }
}

function path_prefix($target) {
    if ($_ENV['USER'] == 'F3M3') {
        if ($target == 'images') {
            return 'http://localhost:8888/projects/php/pic_flip/images';
        } elseif ($target == 'action') {
            return 'http://localhost:8888/projects/php/pic_flip';
        }
    } else {
        if ($target == 'images') {
            return 'http://www.f3mmedia.com/imad';
        } elseif ($target == 'action') {
            return 'http://www.f3mmedia.com/pic_flip';
        }
    }
}

function htmlOutput($currentFilename, $nextFilename, $previousFilename) {
    $images_prefix = path_prefix('images');
    $action_prefix = path_prefix('action');
    $html = <<<HTML
        <html>
            <head>
            </head>
            <body>
                <h1>$currentFilename</h1>
                <form action="$action_prefix/pic_flip.php" method="post">
                    <input type="hidden" name="nextImage" value="$nextFilename" />
                    <input type="hidden" name="previousImage" value="$previousFilename" />

                    <input type="submit" name="previousImageButton" value="PREVIOUS"/>
                    <input type="submit" name="nextImageButton" value="NEXT"/>
                </form>
                <img src="$images_prefix/$currentFilename" />
            </body>
        </html>
HTML;
    return $html;
}

function nextFilename($currentFilename) {
    $filenames = arrayFilenames();
    $current_key = array_search($currentFilename, $filenames);
    if ($current_key == count($filenames) - 1) {
        return $filenames[0];
    } else {
        return $filenames[$current_key + 1];
    }
}

function previousFilename($currentFilename) {
    $filenames = arrayFilenames();
    $current_key = array_search($currentFilename, $filenames);
    if ($current_key == 0) {
        return $filenames[count($filenames) - 1];
    } else {
        return $filenames[$current_key - 1];
    }
}

$currentFilename = currentFilename();
$nextFilename = nextFilename($currentFilename);
$previousFilename = previousFilename($currentFilename);
$out = htmlOutput($currentFilename, $nextFilename, $previousFilename);
echo $out;
