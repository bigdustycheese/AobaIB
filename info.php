<?php
    require "config.php";
    require "inc/mitsuba. php";
    $conn = new mysqli($db_host, $db_username, $db_password, $db_database);
    $mitsuba = new Mitsuba($conn);
    
    $ipAddress = $mitsuba->common->getIP();
    
    $result = $mitsuba->safeQuery(
        "SELECT COUNT(*) AS num_posts, ROUND((COUNT(*) / (SELECT COUNT(*) FROM posts)) * 100, 2) AS percent FROM posts WHERE ip = ? ",
        "s",
        [$ipAddress]
    );
    
    if (! $result) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        exit;
    }
    
    $row = $result->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode([
        'num_posts' => intval($row['num_posts']),
        'percent' => floatval($row['percent'])
    ]);
?>