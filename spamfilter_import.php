<?php
error_reporting(E_ALL);

include ("config.php");
include ("inc/mitsuba.php");

$conn = new mysqli($db_host, $db_username, $db_password, $db_database);
$mitsuba = new Mitsuba($conn);

$f = fopen("toxic.txt", "r");
$reason = "Toxic Import - Contact Management.";
while(!feof($f)) {
  $data = array_map('trim',explode(" ", fgets($f)));
  $conn->query("INSERT INTO spamfilter (`id`, `search`, `reason`, `boards`, `expires`, `active`, `regex`) VALUES (NULL, '".$data[0]."','".$reason."','%','never','1','0')");
  //$mysql_query = "INSERT INTO haruko.spamfilter (`id`, `search`, `reason`, `boards`, `expires`, `active`, `regex`) VALUES (NULL, '".$data[0]."','".$reason."','%','never','1','0')";
  var_dump($conn);
}
fclose($f);
?>
