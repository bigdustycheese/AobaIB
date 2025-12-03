<html lang="en">

<head>

    <title>AobaIB Banners</title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="shortcut icon" href="/favicon.ico" />

    <link rel="stylesheet" type="text/css" href="/css/img_globals.css">

    <link rel="stylesheet" type="text/css" href="/css/front.css" />

    <link rel="stylesheet" type="text/css" href="/css/site_front.css" />

    <link rel="stylesheet" type="text/css" href="/css/site_global.css">

    <link rel="stylesheet" type="text/css" href="/css/futaba.css" title="futaba">

    <style type="text/css">

img {

  -moz-user-select: none;

  -webkit-user-select: none;

  /* this will work for QtWebKit in future */

  -webkit-user-drag: none;

}

</style>

</head>

<body>

<?php
    ini_set('display_errors', 1);
$allowedExts = array("jpg", "jpeg", "gif", "png");

$extension = end(explode(".", $_FILES["file"]["name"]));

// Image size

$test = getimagesize($_FILES["file"]["tmp_name"]);

$width = $test[0];

$height = $test[1];

// end image size

if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/x-png") || ($_FILES["file"]["type"] == "image/png")) && in_array($extension, $allowedExts)) {

    if ($_FILES["file"]["error"] > 0) {

        echo "Return Code: " . $_FILES["file"]["error"] . "<br />";

        die();

    } else {

        if (file_exists("upload/" . $_FILES["file"]["name"]) || file_exists($_FILES["file"]["name"])) {

            echo $_FILES["file"]["name"] . " already exists. ";

        } else if ($width > 300 || $height > 100) {

            echo '<h1>This image is larger than 300x100, right? Fix it.</h1>';

            die();

        } else {

            $filename = basename($_FILES["file"]["name"]);
            $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
            $uploadDir = __DIR__ . "/upload/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filePath = $uploadDir . $filename;

            if (file_exists($filePath)) {
                echo htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . " already exists. ";
            } else {
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $filePath)) {
                    echo "<h1>Thanks!  '" . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . "' has been uploaded.</h1>";
                } else {
                    echo "Upload failed";
                }
            }

            echo "<h1>Thanks! '" . $_FILES["file"]["name"] . "' has been uploaded.</h1>";

        }

    }

} else {

    echo "Invalid file";

}