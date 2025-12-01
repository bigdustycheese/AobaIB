<?php

namespace Mitsuba\Admin;

/**
 * Groups
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
class Groups
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
     * isGroup
     * Insert description here
     *
     * @param $identifier
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function isGroup($identifier)
    {

        if (!is_numeric($identifier)) {

            return 0;

        }

        $result = $this->conn->query("SELECT * FROM groups WHERE id=" . $this->conn->real_escape_string($identifier));

        if ($result->num_rows == 1) {

            $row = $result->fetch_assoc();

            return $row['name'];

        } else {

            return 0;

        }

    }

    /**
     * addGroup
     * Insert description here
     *
     * @param $name
     * @param $capcode
     * @param $capcodeStyle
     * @param $capcodeIcon
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function addGroup($name, $capcode, $capcodeStyle, $capcodeIcon)
    {

        $name = $this->conn->real_escape_string($name);

        $capcode = $this->conn->real_escape_string($capcode);

        $capcodeStyle = $this->conn->real_escape_string($capcodeStyle);

        $capcodeIcon = $this->conn->real_escape_string($capcodeIcon);

        $result = $this->conn->query("INSERT INTO groups (`name`, `capcode`, `capcode_style`, `capcode_icon`) VALUES ('" . $name . "', '" . $capcode . "', '" . $capcodeStyle . "', '" . $capcodeIcon . "')");

        if ($result) {

            return 1;

        } else {

            return 0;

        }

    }

    /**
     * updateGroup
     * Insert description here
     *
     * @param $identifier
     * @param $name
     * @param $capcode
     * @param $capcodeStyle
     * @param $capcodeIcon
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function updateGroup($identifier, $name, $capcode, $capcodeStyle, $capcodeIcon)
    {

        if (!is_numeric($identifier)) {

            return -1;

        }

        $group = $this->conn->query("SELECT * FROM groups WHERE id=" . $identifier);

        if ($group->num_rows == 1) {

            $group = $group->fetch_assoc();

            $name = $this->conn->real_escape_string($name);

            $capcode = $this->conn->real_escape_string($capcode);

            $capcodeStyle = $this->conn->real_escape_string($capcodeStyle);

            $capcodeIcon = $this->conn->real_escape_string($capcodeIcon);

            $this->conn->query("UPDATE groups SET name='" . $name . "', capcode='" . $capcode . "', capcode_style='" . $capcodeStyle . "', capcode_icon='" . $capcodeIcon . "' WHERE id=" . $identifier);

        }

    }

    /**
     * delGroup
     * Insert description here
     *
     * @param $identifier
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function delGroup($identifier)
    {

        if (!is_numeric($identifier)) {

            return -1;

        }

        $this->conn->query("DELETE FROM groups WHERE id=" . $identifier);

        //MAYBE: delete users



    }

}

?>
