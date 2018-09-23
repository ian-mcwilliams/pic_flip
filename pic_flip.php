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

function htmlOutput($currentFilename, $nextFilename, $previousFilename) {
    $html = <<<HTML
        <html>
            <head>
            </head>
            <body>
                <h1>$currentFilename</h1>
                <img src="http://localhost:8888/projects/php/pic_flip/images/$currentFilename" />
                <form action="http://localhost:8888/projects/php/pic_flip/sandbox.php" method="post">
                    <input type="hidden" name="nextImage" value="$nextFilename" />
                    <input type="hidden" name="previousImage" value="$previousFilename" />

                    <input type="submit" name="previousImageButton" value="PREVIOUS"/>
                    <input type="submit" name="nextImageButton" value="NEXT"/>
                </form>
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
