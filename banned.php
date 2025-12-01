<?php

if (!file_exists("./config.php")) {

    header("Location: ./install.php");

}

require "config.php";

require "inc/mitsuba.php";

$conn = new mysqli($db_host, $db_username, $db_password, $db_database);

$mitsuba = new Mitsuba($conn);

$mitsuba->common->banMessage("%");

$mitsuba->common->warningMessage();

?>

<h1>NOT BANNED</h1>