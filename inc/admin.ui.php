<?php

namespace Mitsuba\Admin;

/**
 * UI
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class UI
{

    private $conn;

    private $mitsuba;

    /**
     * __construct
     * Insert description here
     *
     * @param $connection
     * @param $mitsuba
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function __construct($connection, &$mitsuba)
    {

        $this->conn = $connection;

        $this->mitsuba = $mitsuba;

    }

    /**
     * getToken
     * Insert description here
     *
     * @param $path
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function getToken($path)
    {

        global $id_salt;

        $token = "";

        if ((empty($_SESSION['tokenpath'])) || ($_SESSION['tokenpath'] != $path) || (empty($_SESSION['token']))) {

            $token = md5($this->mitsuba->common->randomSalt() . $id_salt);

            $_SESSION['tokenpath'] = $path;

            $_SESSION['token'] = $token;

        } else {

            $token = $_SESSION['token'];

        }

        echo '<input type="hidden" name="token" value="' . $token . '" />';

    }

    /**
     * checkToken
     * Insert description here
     *
     * @param $token
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function checkToken($token)
    {

        if ($_SESSION['token'] != $token) {
            echo("Invalid form.");

        }else{
	        //echo($_SESSION['token']."<br />");
	        //echo($token."<br />");
        }

    }

    /**
     * getBoardList
     * Insert description here
     *
     * @param $boards
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function getBoardList($boards = "")
    {

        global $lang;

        if ($boards == "%") { $all = " checked";

        } else { $all = '';
        }
        if($this->mitsuba->admin->canBoard("%")) {
            echo $lang['mod/boards'] . ': <input type="checkbox" name="all" id="all" value=1' . $all . '/> ';

            echo "<label style='float:none;display:inline' for='all'>" . $lang['mod/all'] . "</label>";
        }else{
            //don't judge me, too lazy to move this out of the if statement
            echo $lang['mod/boards'].":";
        }
?>

        <fieldset id="boardSelect">

    <?php

    if (($boards != "%") && ($boards != "")) {

        $boards = explode(",", $boards);

    }

        $result = $this->conn->query("SELECT * FROM boards ORDER BY short ASC;");

    while ($row = $result->fetch_assoc()) {
        $checked = "";

        if (($boards !== "%") && ($boards !== "")) {
            echo $this->mitsuba->admin->canBoard($row['short']);
            if (in_array($boards, $row['short'])) {

                $checked = " checked ";

            }

        }
        if($this->mitsuba->admin->canBoard($row['short'])) {
            echo "<div style='float:none;'>";
            echo "<input id='{$row['short']}' type='checkbox' name='boards[]' value='" . $row['short'] . "'" . $checked . "/>";
            echo "<label for='{$row['short']}'>/" . $row['short'] . "/ - " . $row['name'] . "</label>";
            echo "</div>";
        }
    }

?>

        </fieldset>

    <?php

    }

    /**
     * getLinkList
     * Insert description here
     *
     * @param $links
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function getLinkList($links = "")
    {

        global $lang;

        if ($links == "%") {

?>

    <?php echo $lang['mod/board_links']; ?>: <input type="checkbox" name="l_all" id="l_all" onClick="$('#linkSelect').toggle()" value=1 checked/> <?php echo $lang['mod/all']; ?>

    <?php

        } else {

?>

    <?php echo $lang['mod/board_links']; ?>: <input type="checkbox" name="l_all" id="l_all" onClick="$('#linkSelect').toggle()" value=1/> <?php echo $lang['mod/all']; ?>

    <?php

        }

?>

        <br/>

        <fieldset id="linkSelect">

    <?php

    if (($links != "%") && ($links != "")) {

        $links = explode(",", $links);

    }

        $result = $this->conn->query("SELECT * FROM link ORDER BY name ASC;");

    while ($row = $result->fetch_assoc()) {

        $checked = "";

        if (($links !== "%") && ($links !== "")) {

            if (in_array($links, $row['name'])) {

                $checked = " checked ";

            }

        }

        echo "<label for='links'>" . $row['name'] . "</label>";

        echo "<input type='checkbox' onClick='document.getElementById(\"all\").checked=false;' name='links[]' value='" . $row['name'] . "'" . $checked . "/>";

    }

?>

        </fieldset>

    <?php

    }

    /**
     * getExtensionList
     * Insert description here
     *
     * @param $extensions
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function getExtensionList($extensions = "")
    {

        global $lang;

        if ($extensions == "%") {

?>

    <?php echo $lang['mod/extensions']; ?>: <input type="checkbox" name="ext_all" id="ext_all" onClick="$('#extSelect').toggle()" value=1 checked/> <?php echo $lang['mod/all']; ?><br/>

    <?php

        } else {

?>

    <?php echo $lang['mod/extensions']; ?>: <input type="checkbox" name="ext_all" id="ext_all" onClick="$('#extSelect').toggle()" value=1/> <?php echo $lang['mod/all']; ?><br/>

    <?php

        }

?>

        <fieldset id="extSelect">

    <?php

    if (($extensions != "%") && ($extensions != "")) {

        $extensions = explode(",", $extensions);

    }

        $result = $this->conn->query("SELECT DISTINCT ext FROM extensions ORDER BY ext ASC;");

    while ($row = $result->fetch_assoc()) {

        $checked = "";

        if (($extensions !== "%") && ($extensions !== "")) {

            if (in_array($extensions, $row['ext'])) {

                $checked = " checked ";

            }

        }

        if (empty($extensions)) {

            if (($row['ext'] == "jpg") || ($row['ext'] == "gif") || ($row['ext'] == "png")) {

                $checked = " checked ";

            }

        }

        echo "<label for='ext'>" . $row['ext'] . "</label>";

        echo "<input type='checkbox' onClick='document.getElementById(\"ext_all\").checked=false;' name='ext[]' value='" . $row['ext'] . "'" . $checked . "/>";

    }

?>

        </fieldset>

    <?php

    }

    /**
     * parseList
     * Insert description here
     *
     * @param $input
     * @param $all
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function parseList($input, $all = 0)
    {

        $out = "";

        if ((!empty($_POST[$all])) && ($_POST[$all] == 1)) {

            $out = "%";

        } else {

            if (!empty($_POST[$input])) {

                foreach ($_POST[$input] as $s) {

                    $s.= $s . ",";

                }

            } else {

                $out = "%";

            }

        }

        if ($out != "%") {

            $out = substr($out, 0, strlen($out) - 1);

        }

        return $out;

    }

    /**
     * startSection
     * Insert description here
     *
     * @param $title
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function startSection($title)
    {

?>

            <!-- Content Header (Page header) -->

            <section class="content-header">

            <?if($title){?><h1><?php echo $title; ?></h1><?}?>

    <?php

    }

    /**
     * endSection
     * Insert description here
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function endSection()
    {

?>

    </h1>

    </section>



    <?php

    }

}

?>
