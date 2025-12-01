<?php

namespace Mitsuba;
use spam;

/**
 * Board
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
class Board
{

    private $conn;

    private $mitsuba;

    private $config;

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

        $this->config = $this->mitsuba->config;

    }

    /**
     * checkSpam
     * Insert description here
     *
     * @param $comment
     * @param $board
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function checkSpam($comment, $board)
    {
        $spam = $this->conn->query("SELECT * FROM spamfilter WHERE active=1");

        while ($row = $spam->fetch_assoc()) {

            if ($row['boards'] != "%") {

                $boards = explode(",", $row['boards']);

                if (in_array($board, $boards)) {

                    if ($row['regex'] == 1) {

                        try {

                            if (preg_match($row['search'], $comment)) {

                                $this->mitsuba->common->addSystemBan($this->mitsuba->common->getIP(), $row['reason'], htmlspecialchars($_POST['com']), $row['expires'], $row['boards']);

                                echo '<meta http-equiv="refresh" content="2;URL=' . "'./banned.php'" . '">';

                                exit();

                            }

                        }

                        catch(Exception $ex) {

                        }

                    } else {

                        if (stripos($comment, $row['search']) !== false) {

                            $this->mitsuba->common->addSystemBan($this->mitsuba->common->getIP(), $row['reason'], htmlspecialchars($_POST['com']), $row['expires'], $row['boards']);

                            echo '<meta http-equiv="refresh" content="2;URL=' . "'./banned.php'" . '">';

                            exit();

                        }

                    }

                }

            } else {

                if ($row['regex'] == 1) {

                    try {

                        if (preg_match($row['search'], $comment)) {

                            $this->mitsuba->common->addSystemBan($this->mitsuba->common->getIP(), $row['reason'], htmlspecialchars($_POST['com']), $row['expires'], "%");

                            echo '<meta http-equiv="refresh" content="2;URL=' . "'./banned.php'" . '">';

                            exit();

                        }

                    }

                    catch(Exception $ex) {

                    }

                } else {

                    if (stripos($comment, $row['search']) !== false) {

                        $this->mitsuba->common->addSystemBan($this->mitsuba->common->getIP(), $row['reason'], htmlspecialchars($_POST['com']), $row['expires'], "%");

                        echo '<meta http-equiv="refresh" content="2;URL=' . "'./banned.php'" . '">';

                        exit();

                    }

                }

            }

        }

    }

    /**
     * checkThreadDate
     * Insert description here
     *
     * @param $bdata
     * @param $returnURL
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function checkThreadDate($bdata, $returnURL)
    {

        global $lang;

        $lastdate = $this->conn->query("SELECT date FROM posts WHERE ip='" . $this->mitsuba->common->getIP() . "' AND resto=0 AND board='" . $bdata['short'] . "' ORDER BY date DESC LIMIT 0, 1");

        if ($lastdate->num_rows == 1) {

            $pdate = $lastdate->fetch_assoc();

            $pdate = $pdate['date'];

            if (($pdate + $bdata['time_between_threads']) > time()) {

                $this->mitsuba->common->showMsg($lang['img/error'], $lang['img/wait_more_thread']);

                exit;

            }

        }

    }

    /**
     * checkPostDate
     * Insert description here
     *
     * @param $bdata
     * @param $returnURL
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function checkPostDate($bdata, $returnURL)
    {

        global $lang;

        $lastdate = $this->conn->query("SELECT date FROM posts WHERE ip='" . $this->mitsuba->common->getIP() . "' AND board='" . $bdata['short'] . "' ORDER BY date DESC LIMIT 0, 1");

        if ($lastdate->num_rows == 1) {

            $pdate = $lastdate->fetch_assoc();

            $pdate = $pdate['date'];

            if (($pdate + $bdata['time_between_posts']) > time()) {

                $this->mitsuba->common->showMsg($lang['img/error'], $lang['img/wait_more_post']);

                exit;

            }

        }

    }

    /**
     * checkEmbed
     * Insert description here
     *
     * @param $bdata
     * @param $embed
     * @param $returnURL
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function checkEmbed($bdata, $embed, $returnURL)
    {

        global $lang;

        if ($bdata['embeds'] == 0) {

            $this->mitsuba->common->showMsg($lang['img/error'], $lang['img/embed_not_supported']);

            exit;

        }

        $embedTable = array();

        $result = $this->conn->query("SELECT * FROM embeds;");

        while ($row = $result->fetch_assoc()) {

            $embedTable[] = $row;

        }

        if ($this->mitsuba->common->isEmbed($embed, $embedTable)) {

            return "embed:" . $embed;

        } else {

            $this->mitsuba->common->showMsg($lang['img/error'], $lang['img/embed_not_supported']);

            exit;

        }

    }

}

?>
