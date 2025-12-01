<?php

namespace Mitsuba\Admin;

/**
 * Bans
 * Handles system bans.
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
class Bans
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
     * addBan
     * Inserts a ban into the `bans` table
     *
     * @param $ipAddress The IP address of the banned user
     * @param $reason The reason the user was banned
     * @param $note A note only staff members can see (if it exists)
     * @param $expires The amout of time (in seconds) the user is banned
     * @param $boards Comma-separated (or % if all) list of boards the user is banned from
     * @param $appeal The appeal message the banned user sends to staff
     *
     * @return int 1 Only if user ban was successful | int -2 if ban is unsuccessful
     *
     * @access
     * @static
     * @see
     * @since
     */
    function addBan($ipAddress, $reason, $note, $expires, $boards, $appeal = 0) 
    {

        if (!empty($ipAddress)) {

            $ipAddress = $this->conn->real_escape_string($ipAddress);

            $reason = $this->conn->real_escape_string($reason);

            $note = $this->conn->real_escape_string($note);

            $boards = $this->conn->real_escape_string($boards);

            $created = time();

            $perma = 1;

            $noappeal = 1;

            if (($expires == "0") || ($expires == "never") || ($expires == "") || ($expires == "perm") || ($expires == "permaban")) {

                $expires = 0;

                $perma = 1;

            } else {

                $expires = $this->mitsuba->common->parse_time($expires);

                $perma = 0;

            }

            if (($expires == false) && ($perma == 0)) {

                return -2;

            }

            if (($appeal == "0") || ($appeal == "never") || ($appeal == "")) {

                $appeal = 0;

                $noappeal = 1;

            } else {

                $appeal = $this->mitsuba->common->parse_time($appeal);

                $noappeal = 0;

            }

            if (($appeal == false) && ($noappeal == 0)) {

                return -2;

            }

            $this->conn->query("INSERT INTO bans (ip, mod_id, reason, note, created, expires, appeal, boards, seen) VALUES ('" . $ipAddress . "', " . $_SESSION['id'] . ", '" . $reason . "', '" . $note . "', " . $created . ", " . $expires . ", " . $appeal . ", '" . $boards . "', 0);");

            return 1;

        }

    }

    /**
     * addRangeBan
     * Inserts a ranged ban into the `rangebans` table
     *
     * @param $ipAddress The IP address of the banned user
     * @param $reason The reason the user was banned
     * @param $note A note only staff members can see (if it exists)
     * @param $expires The amout of time (in seconds) the user is banned
     * @param $boards Comma-separated (or % if all) list of boards the user is banned from
     *
     * @return int 1 Only if user ban was successful | int -2 if ban is unsuccessful
     *
     * @access
     * @static
     * @see
     * @since
     */
    function addRangeBan($ipAddress, $reason, $note, $expires, $boards) 
    {

        if (!empty($ipAddress)) {

            $ipAddress = $this->conn->real_escape_string($ipAddress);

            $reason = $this->conn->real_escape_string($reason);

            $note = $this->conn->real_escape_string($note);

            $boards = $this->conn->real_escape_string($boards);

            $created = time();

            $perma = 1;

            if (($expires == "0") || ($expires == "never") || ($expires == "") || ($expires == "perm") || ($expires == "permaban")) {

                $expires = 0;

                $perma = 1;

            } else {

                $expires = $this->mitsuba->common->parse_time($expires);

                $perma = 0;

            }

            if (($expires == false) && ($perma == 0)) {

                return -2;

            }

            $this->conn->query("INSERT INTO rangebans (ip, mod_id, reason, note, created, expires, boards) VALUES ('" . $ipAddress . "', " . $_SESSION['id'] . ", '" . $reason . "', '" . $note . "', " . $created . ", " . $expires . ", '" . $boards . "');");

            return 1;

        }

    }

    /**
     * addWarning
     * Inserts warning into the `warnings` table
     *
     * @param $ipAddress The IP address of the user getting the warning
     * @param $reason The reason the user was warned
     * @param $note A note only staff members can see (if it exists)
     *
     * @return int 1
     *
     * @access
     * @static
     * @see
     * @since
     */
    function addWarning($ipAddress, $reason, $note) 
    {

        if (!empty($ipAddress)) {

            $ipAddress = $this->conn->real_escape_string($ipAddress);

            $reason = $this->conn->real_escape_string($reason);

            $note = $this->conn->real_escape_string($note);

            $created = time();

            $this->conn->query("INSERT INTO warnings (ip, mod_id, reason, note, created, seen) VALUES ('" . $ipAddress . "', " . $_SESSION['id'] . ", '" . $reason . "', '" . $note . "', " . $created . ", 0);");

            return 1;

        }

    }

    /**
     * addBanRequest
     * Inserts ban request into `ban_request` table
     *
     * @param $ipAddress The IP address of the user getting the warning
     * @param $reason The reason the user was warned
     * @param $note A note only staff members can see (if it exists)
     * @param $board Board the staff member wishes the user be banned from
     * @param $post
     * @param $append
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function addBanRequest($ipAddress, $reason, $note, $board = "", $post = 0, $append = 0) 
    {

        if (!empty($ipAddress)) {

            $ipAddress = $this->conn->real_escape_string($ipAddress);

            $reason = $this->conn->real_escape_string($reason);

            $note = $this->conn->real_escape_string($note);

            if (is_numeric($post)) {

            }

            $created = time();

            $this->conn->query("INSERT INTO ban_requests (ip, mod_id, reason, note, created, board, post, append) VALUES ('" . $ipAddress . "', " . $_SESSION['id'] . ", '" . $reason . "', '" . $note . "', " . $created . ", '" . $board . "', " . $post . ", " . $append . ");");

            return 1;

        }

    }

}

?>
