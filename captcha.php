<?php
	$engine = "haruko";
	$action = $_GET["action"] ?? '';
if ($action == "puzzle") {
  require("inc/piDentify/captcha.puzzle.php");
} else if ($action == "ident"){
	require("inc/piDentify/captcha.ident.php");
}else {
	die("sorry.");
}
?>
