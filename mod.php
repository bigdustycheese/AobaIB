<?php

ob_start();

if (!file_exists("./config.php")) {

    header("Location: ./install.php");

    die();

}

define("IN_MOD", true);

session_start();

require "config.php";

require "version.php";

require "inc/mitsuba.php";

require "inc/strings/mod.strings.php";

require "inc/strings/imgboard.strings.php";

require "inc/strings/log.strings.php";

if (count($_GET) == 0) {

    $path = "/";

} else {

    $pkey = array_keys($_GET);

    if (substr($pkey[0], 0, 1) == "/") {

        $path = $pkey[0];

    } else {

        $path = "/";

    }

}

if ($path != "/") {

    $path = rtrim($path, "/ ");

}

if (((!isset($_SESSION['logged'])) || ($_SESSION['logged'] == 0)) && (!(($path == "/") || ($path == "/login"))) && ($path == "/api") ) {

    echo '<div class="callout callout-danger">

                    <h4>Uh oh!</h4>

                    <p>'.$lang['mod/not_logged_in'].'</p>

                  </div>';

}

$conn = new mysqli($db_host, $db_username, $db_password, $db_database);

$mitsuba = new Mitsuba($conn);

/**
 * deleteEntry
 * Insert description here
 *
 * @param $conn
 * @param $type
 * @param $id
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function deleteEntry($conn, $type, $id)
{

    global $mitsuba;

    if (!is_numeric($id)) {

        return -1;

    }

    $table = "";

    if ($type == 0) {

        $table = "announcements";

    }

    if ($type == 1) {

        $table = "news";

    }

    if ($mitsuba->admin->checkPermission($table . ".delete", $_SESSION['group'])) {

        $conn->query("DELETE FROM " . $table . " WHERE id=" . $id);

    } elseif ($mitsuba->admin->checkPermission($table . ".delete.own", $_SESSION['group'])) {

        $result = $conn->query("SELECT * FROM " . $table . " WHERE id=" . $id);

        $entry = $result->fetch_assoc();

        if ($entry['mod_id'] == $_SESSION['id']) {

            $conn->query("DELETE FROM " . $table . " WHERE id=" . $id);

        }

    } else {

        die("Insufficient permissions4");

    }

    if ($type == 1) {

        $mitsuba->caching->generateNews();

    }

}

/**
 * updateEntry
 * Insert description here
 *
 * @param $conn
 * @param $type
 * @param $id
 * @param $who
 * @param $title
 * @param $text
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function updateEntry($conn, $type, $id, $who, $title, $text)
{
    global $mitsuba;

    if (! is_numeric($id) || $id <= 0) {
        return -1;
    }

    $id = intval($id);
    $validTables = ["announcements", "news"];
    $tableName = ($type == 0) ? "announcements" : (($type == 1) ? "news" : null);
    
    if (! in_array($tableName, $validTables)) {
        return -1;
    }

    if ($mitsuba->admin->checkPermission($tableName . ".update", $_SESSION['group'])) {
        $mitsuba->safeExecute(
            "UPDATE " . $tableName . " SET who = ?, title = ?, text = ? WHERE id = ?",
            "sssi",
            [$who, $title, $text, $id]
        );
    } elseif ($mitsuba->admin->checkPermission($tableName . ".update.own", $_SESSION['group'])) {
        $result = $mitsuba->safeQuery(
            "SELECT mod_id FROM " . $tableName . " WHERE id = ?",
            "i",
            [$id]
        );
        
        if ($result && $result->num_rows === 1) {
            $entry = $result->fetch_assoc();
            if ($entry['mod_id'] == $_SESSION['id']) {
                $mitsuba->safeExecute(
                    "UPDATE " . $tableName . " SET who = ?, title = ?, text = ? WHERE id = ?",
                    "sssi",
                    [$who, $title, $text, $id]
                );
            }
        }
    }

    if ($type == 1) {
        $mitsuba->caching->generateNews();
    }
}

/**
 * processEntry
 * Insert description here
 *
 * @param $conn
 * @param $string
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function processEntry($conn, $string)
{
    $new = str_replace("\r", "", $string);
    $lines = explode("\n", $new);
    $new = "";
    $allowedTags = "<b><i><u><a>";
    foreach ($lines as $line) {
        $line = trim($line);
        if (! empty($line) && substr($line, 0, 1) != "<") {
            $line = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
            $new .= "<p>" . strip_tags($line, $allowedTags) .  "</p>";
        }
    }
    return $new;
}

if ((!empty($_SESSION['logged'])) && (!empty($_SESSION['cookie_set'])) && ($_SESSION['cookie_set'] == 2)) {

    $cookie = "";

    $cookie.= ($mitsuba->admin->checkPermission("post.ignorenoname") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.ignoresizelimit") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.raw") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.antibump") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.sticky") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.closed") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.nofile") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.fakeid") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.ignorecaptcha") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.capcode") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.customcapcode") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.viewip") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.delete.single") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("post.edit") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("bans.add") ? 1 : 0);

    $cookie.= ($mitsuba->admin->checkPermission("bans.add.request") ? 1 : 0);

    setcookie('in_mod', $cookie, 0);

    $_SESSION['cookie_set'] = 1;

}

if (($path != "/nav") && ($path != "/board") && ($path != "/board/action") && (($path != "/") || ((!isset($_SESSION['logged'])) || ($_SESSION['logged'] == 0))) && (substr($path, 0, 5) != "/api/")) {

?>

<?php

}

if ((!empty($_SESSION['logged'])) && ($_SESSION['logged'] == 1) && ($_SESSION['ip'] != $mitsuba->common->getIP())) {

    $mitsuba->admin->logAction(sprintf($lang['log/ip_changed'], $_SESSION['ip'], $mitsuba->common->getIP()));

    $_SESSION['ip'] = $mitsuba->common->getIP();

}

switch ($path) {

case "/":

    include "inc/mod/main.inc.php";

    break;
//} Caused an error; Why is this on anyway?

        //yes, I know this is hard-wired into the code. We need to figure out a better way.


case "/login":

    include "inc/mod/login.inc.php";

    header("Location: /mod.php");

    break;

        // /?logout



case "/logout":

    setcookie('in_mod', '0', time() - 86400);

    session_destroy();

    header("Location: /mod.php");

    break;

default:

    /*$file = "inc/mod/".str_replace(array("/", "\\", ".."), ".", trim($path, " \t\n\r\0\x0B/\\")).".inc.php";

    if (file_exists($file))

    {

    include($file);

    } else {

    $modules = $conn->query("SELECT * FROM module_pages WHERE url='/".$conn->real_escape_string(str_replace(array("/", "\\", "/"), ".", trim($path, " \t\n\r\0\x0B/\\")))."'");

    while ($module = $modules->fetch_assoc())

    {

    include("./".$module['namespace']."/".$module['file']);

    $pageclass = new $module['class']($conn, $mitsuba);

    $pageclass->$module['method']();

    }

    }*/
    if((stristr($path, 'api'))) {
        switch (true){
        case stristr($path, 'admin_stuff'):
            include "inc/mod/api.admin_stuff.inc.php";
            break;
        case stristr($path, 'get_post'):
            include "inc/mod/api.get_post.inc.php";
            break;
        case stristr($path, 'update_post'):
            include "inc/mod/api.update_post.inc.php";
            break;
        default:
            include "inc/mod/api.admin_stuff.inc.php";
            break;
        }
        //I hate preg_match, but it's the best I could do..
    }else if((preg_match("~\bboard\b~", $path))) {
        include "inc/mod/board.inc.php";
    }else{
        include "inc/mod/main.inc.php";
    }

    break;

}

if (($path != "/nav") && ($path != "/board") && ($path != "/board/action") && (($path != "/") || ((!isset($_SESSION['logged'])) || ($_SESSION['logged'] == 0))) && (substr($path, 0, 5) != "/api/")) {

?>

<?php

}

$conn->close();
