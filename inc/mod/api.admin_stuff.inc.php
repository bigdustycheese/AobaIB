<?php

if (!defined("IN_MOD")) {

    die("Nah, I won't serve that file to you.");

}

if(isset($_SESSION['logged'])) {
    $mitsuba->admin->reqPermission("post.viewip");

    if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && ($mitsuba->common->isBoard($_GET['b'])) && (is_numeric($_GET['p'])) && $mitsuba->admin->canBoard($_GET['b'])) {

        $result = $conn->query("SELECT * FROM posts WHERE id=".$_GET['p']." AND board='".$_GET['b']."'");

        if ($result->num_rows == 1) {

            $row = $result->fetch_assoc();
            header('Content-Type: application/json');
            if($mitsuba->admin->canBoard("%")) {
                echo json_encode(array('ip' => $row['ip'], 'sage' => $row['sage']));
            }else{
                global $securetrip_salt;
                echo json_encode(array('ip' => mb_strimwidth(crypt($row['ip'], $securetrip_salt), 0, 10), 'sage' => $row['sage']));
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(array('error' => 404));

        }

    } else {
        header('Content-Type: application/json');
        echo json_encode(array('error' => 404));

    }
}else {
    //user doesn't have the 'logged' php session. destroy session.
    session_destroy();
    die();
}
?>
