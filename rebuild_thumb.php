<?php
/*
    vote.php has yet to be written.
    for now, it's just collecting info and stuff.
*/
    require "config.php";
    require "inc/mitsuba.php";
    $conn = new mysqli($db_host, $db_username, $db_password, $db_database);
    $haruko = new Mitsuba($conn);
    $sql = "SELECT `short` FROM `boards`";
    $trip = $conn->query($sql);

    /*while($row = $trip->fetch_array()){
	    //echo $row['short']."\n";
	    $haruko->caching->regenThumbnails($row['short']);
	    echo $row['short']."?\n";
	}*/
		    $haruko->caching->regenThumbnails('m');
        //var_dump(shell_exec("/bin/sh -c '/usr/local/bin/ffmpeg -opts 2>&1'"));



?>
