<?php
/*
    vote.php has yet to be written.
    for now, it's just collecting info and stuff.
*/
    require "config.php";
    require "inc/mitsuba.php";
    $conn = new mysqli($db_host, $db_username, $db_password, $db_database);
    $haruko = new Mitsuba($conn);
    $ipAddress = $haruko->common->getIP();
    $sql = "SELECT `ip`, COUNT(*) AS `num_posts`, round((COUNT(*) / (SELECT COUNT(*) from `posts`))* 100) AS `percent` FROM `posts` WHERE `ip` = '".$ipAddress."'";
if(!$result = $conn->query($sql)) {

       die('There was an error running the query [' . $conn->error . ']');

}else{

    while($row2 = $result->fetch_assoc()){

        echo("<pre>");
        if($row2["ip"]) {
            unset($row2["ip"]);
        }
        /*if($row2["password"]) {
            unset($row2["password"]);
        }
        if($row2['resto'] == "0") {
            $row2['resto'] = $row2['id'];
        }*/
        echo json_encode($row2);
        echo("</pre>");

    }

}
?>
