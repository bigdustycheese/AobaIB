<?php

namespace Mitsuba;

/**
 * Posting
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
class Posting
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
     * escapeMitsubaSpecialCharacters
     * Insert description here
     *
     * @param $text
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function escapeMitsubaSpecialCharacters($text)
    {

        $new = str_replace("<+", '<\+', $text);

        $new = str_replace("+>", '+\>', $text);

        return $new;

    }

    /**
     * deletePost
     * Insert description here
     *
     * @param $board
     * @param $postno
     * @param $password
     * @param $onlyimgdel
     * @param $withoutpassword
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
   function deletePost($board, $postno, $password, $onlyimgdel = 0, $withoutpassword = false)
{

    if (is_numeric($postno)) {

        $board = $this->conn->real_escape_string($board);

        if (!$this->mitsuba->common->isBoard($board)) {

            return -16;

        }

        $bdata = $this->mitsuba->common->getBoardData($board);

        $result = $this->conn->query("SELECT * FROM posts WHERE id=" . $postno . " AND board='" . $board . "' AND deleted=0");

        if ($result->num_rows == 1) {

            $config = $this->mitsuba->config;

            $postdata = $result->fetch_assoc();

            if (!$withoutpassword) {

                if (time() <= ($postdata['date'] + $bdata['time_to_delete'])) {

                    return -4;

                }

                if (md5($password) != $postdata['password']) {

                    return -1;

                }

            }

            if ($onlyimgdel == 1) {

                if ((!empty($postdata['filename'])) && ($postdata['filename'] != "deleted")) {

                    $filename = $postdata['filename'];

                    if (substr($filename, 0, 4) == "url:") {

                        return 1;

                    }

                    if (substr($filename, 0, 8) == "spoiler:") {

                        $filename = substr($filename, 8);

                    }

                    if ((substr($filename, 0, 6) != "embed:") && (substr($filename, 0, 4) != "url:") && ($filename != "deleted")) {
                        $src_path = "../" . $board . "/src/" . $filename;
                        $thumb_path = "../" . $board . "/src/thumb/" . $filename;
                        
                        if (file_exists($src_path)) {
                            unlink($src_path);
                        }
                        if (file_exists($thumb_path)) {
                            unlink($thumb_path);
                        }
                    }

                    $this->conn->query("UPDATE posts SET filename='deleted', mimetype='', filehash='' WHERE id=" . $postno . " AND board='" . $board . "';");

                    if ($postdata['resto'] != 0) {

                        $this->mitsuba->caching->generateView($board, $postdata['resto']);

                        if ($config['caching_mode'] == 1) {

                            $this->mitsuba->caching->forceGetThread($board, $postdata['resto']);

                        }

                        $this->mitsuba->caching->generateView($board);

                    } else {

                        $this->mitsuba->caching->generateView($board, $postno);

                        if ($config['caching_mode'] == 1) {

                            $this->mitsuba->caching->forceGetThread($board, $postno);

                        }

                        $this->mitsuba->caching->generateView($board);

                    }

                    if ($bdata['catalog'] == 1) {

                        $this->mitsuba->caching->generateCatalog($board);

                    }

                    $e = array("postno" => $postno, "onlyimgdel" => $onlyimgdel);

                    $this->mitsuba->emitEvent("posting.delete", $e);

                    $this->mitsuba->caching->generateFrontpage("onPostDeleted");

                    return 1;

                } else {

                    return -3;

                }

            } else {

                if ($postdata['resto'] == 0)
                {

                    $files = $this->conn->query("SELECT * FROM posts WHERE filename != '' AND resto=" . $postdata['id'] . " AND board='" . $board . "'");

                    while ($file = $files->fetch_assoc()) {

                        $filename = $file['filename'];

                        if (substr($filename, 0, 4) == "url:") {

                            $filename = "deleted";

                        }

                        if (substr($filename, 0, 8) == "spoiler:") {

                            $filename = substr($filename, 8);

                        }

                        if ((substr($filename, 0, 6) != "embed:") && ($filename != "deleted")) {
                            $src_path = "../" . $board . "/src/" . $filename;
                            
                            if (file_exists($src_path)) {
                                unlink($src_path);
                            }
                            
                            $thumb_path = "../" . $board . "/src/thumb/" . $filename;
                            if (file_exists($thumb_path)) {
                                unlink($thumb_path);
                            }
                        }

                    }

                    if ((!empty($postdata['filename'])) && ($postdata['filename'] != "deleted")) {

                        $filename = $postdata['filename'];

                        if (substr($filename, 0, 8) == "spoiler:") {

                            $filename = substr($filename, 8);

                        }

                        if ((substr($filename, 0, 6) != "embed:") && ($filename != "deleted")) {
                            $src_path = "../" . $board . "/src/" . $filename;
                            
                            if (file_exists($src_path)) {
                                unlink($src_path);
                            }
                            
                            $thumb_path = "../" . $board . "/src/thumb/" . $filename;
                            if (file_exists($thumb_path)) {
                                unlink($thumb_path);
                            }
                        }

                    }

                    $this->conn->query("UPDATE posts SET deleted=" . time() . " WHERE resto=" . $postno . " AND board='" . $board . "';");

                    $this->conn->query("UPDATE posts SET deleted=" . time() . " WHERE id=" . $postno . " AND board='" . $board . "';");

                    if ($bdata['hidden'] == 0) {
                        $res_json = "./" . $board . "/res/" . $postno . ".json";
                        $res_index = "./" . $board . "/res/" . $postno . "_index.html";
                        $res_html = "./" . $board . "/res/" . $postno . ".html";

                        if (file_exists($res_json)) {
                            unlink($res_json);
                        }
                        if (file_exists($res_index)) {
                            unlink($res_index);
                        }
                        if (file_exists($res_html)) {
                            unlink($res_html);
                        }
                    }

                    if ($bdata['catalog'] == 1) {

                        $this->mitsuba->caching->generateCatalog($board);

                    }

                    $this->mitsuba->caching->generateView($board);

                    $e = array("postno" => $postno, "onlyimgdel" => $onlyimgdel);

                    $this->mitsuba->emitEvent("posting.delete", $e);

                    $this->mitsuba->caching->generateFrontpage("onPostDeleted");

                    return 2;

                } else {

                    if ((!empty($postdata['filename'])) && ($postdata['filename'] != "deleted")) {

                        $filename = $postdata['filename'];

                        if (substr($filename, 0, 4) == "url:") {

                            $filename = "deleted";

                        }

                        if (substr($filename, 0, 8) == "spoiler:") {

                            $filename = substr($filename, 8);

                        }

                        if ((substr($filename, 0, 6) != "embed:") && ($filename != "deleted")) {
                            $src_path = "../" . $board . "/src/" . $filename;
                            $thumb_path = "../" . $board . "/src/thumb/" . $filename;

                            if (file_exists($src_path)) {
                                unlink($src_path);
                            }
                            if (file_exists($thumb_path)) {
                                unlink($thumb_path);
                            }
                        }

                    }

                    $this->conn->query("UPDATE posts SET deleted=" . time() . " WHERE id=" . $postno . " AND board='" . $board . "';");

                    $this->mitsuba->caching->generateView($board, $postdata['resto']);

                    if ($config['caching_mode'] == 1) {

                        $this->mitsuba->caching->forceGetThread($board, $postdata['resto']);

                    }

                    if ($bdata['catalog'] == 1) {

                        $this->mitsuba->caching->generateCatalog($board);

                    }

                    $this->mitsuba->caching->generateView($board);

                    $e = array("postno" => $postno, "onlyimgdel" => $onlyimgdel);

                    $this->mitsuba->emitEvent("posting.delete", $e);

                    $this->mitsuba->caching->generateFrontpage("onPostDeleted");

                    return 2;

                }

            }

            if ($config['enable_api'] == 1) {

                $this->mitsuba->caching->serializeBoard($_GET['b']);

            }

        } else {

            return -2;

        }

    } else {

        return -2;

    }

}

    /**
     * addPost
     * Insert description here
     *
     * @param $board
     * @param $name
     * @param $email
     * @param $subject
     * @param $comment
     * @param $password
     * @param $filename
     * @param $orig_filename
     * @param $mimetype
     * @param $resto
     * @param $md5
     * @param $thumbW
     * @param $thumbH
     * @param $spoiler
     * @param $embed
     * @param $raw
     * @param $sticky
     * @param $locked
     * @param $nolimit
     * @param $nofile
     * @param $fakeID
     * @param $capText
     * @param $capStyle
     * @param $capIcon
     * @param $redirect
     * @param $customFields
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function addPost($board, $name, $email, $subject, $comment, $password, $filename, $origFilename, $mimetype = "", $resto = null, $md5 = "", $thumbW = 0, $thumbH = 0, $spoiler = 0, $embed = 0, $raw = 0, $sticky = 0, $locked = 0, $nolimit = 0, $nofile = 0, $fakeID = "", $capText = "", $capStyle = "", $capIcon = "", $redirect = 0, $customFields = array())
    {

        global $lang;

        $config = $this->mitsuba->config;

        if (!$this->mitsuba->common->isBoard($board)) {

            return -16;

        }

        if (!is_numeric($resto)) {

            $resto = 0;

        }

        $mod = 0;

        if (!empty($_SESSION['group'])) {

            $mod = 1;

        }

        if (!is_numeric($thumbW)) {

            $thumbW = 0;

        }

        if (!is_numeric($thumbH)) {

            $thumbH = 0;

        }

        if ((!is_numeric($raw)) || ($mod == 0) || (!$this->mitsuba->admin->checkPermission("post.raw"))) {

            $raw = 0;

        }

        if ((!is_numeric($sticky)) || ($mod == 0) || (!$this->mitsuba->admin->checkPermission("post.sticky"))) {

            $sticky = 0;

        }

        if ((!is_numeric($locked)) || ($mod == 0) || (!$this->mitsuba->admin->checkPermission("post.closed"))) {

            $locked = 0;

        }

        if ($resto != 0) {

            $sticky = 0;

            $locked = 0;

        }
        $bdata = $this->mitsuba->common->getBoardData($board);

        if (($resto == 0) && (empty($filename)) && ($nofile == 0) && ($bdata['type'] !== 'textboard')) {

            //echo "<center><h1>" . $lang['img/no_file'] . "</h1><br /><a href='./" . $board . "'>" . $lang['img/return'] . "</a></center>";
            $this->mitsuba->common->showMsg($lang['img/error'], $lang['img/no_file']);

            exit;

        }

        if ((empty($filename)) && (empty($comment))) {

            //echo "<center><h1>" . $lang['img/no_file'] . "</h1><br /><a href='./" . $board . "'>" . $lang['img/return'] . "</a></center>";
            $this->mitsuba->common->showMsg($lang['img/error'], $lang['img/no_file']);
            exit;

        }



        $fname2 = $filename;

        if ((!empty($filename)) && ($spoiler == 1) && ($bdata['spoilers'] == 1)) {

            $filename = "spoiler:" . $filename;

        }

        $embedImg = 0;

        if ((!empty($filename)) && ($embed == 1) && ($bdata['embeds'] == 1)) {

            $fname2 = "embed";

            $embedImg = 1;

        }

        if ((substr($filename ?? '', 0, 4) == "url:")) {
    $fname2 = "url";
        }

        $thread = "";

        $tinfo = "";

        $replies = 0;

        if ($resto != 0) {

            $thread = $this->conn->query("SELECT * FROM posts WHERE id=" . $resto . " AND board='" . $board . "'");

            if ($bdata['bumplimit'] > 0) {

                $replies = $this->conn->query("SELECT * FROM posts WHERE resto=" . $resto . " AND board='" . $board . "'");

                $replies = $replies->num_rows;

            }

            if ($thread->num_rows == 0) {

                //echo "<center><h1>" . $lang['img/cant_reply'] . "</h1><br /><a href='./" . $board . "'>" . $lang['img/return'] . "</a></center>";
                $this->mitsuba->common->showMsg($lang['img/error'], $lang['img/cant_reply']);
                exit;

            }

            $tinfo = $thread->fetch_assoc();

            if (($tinfo['locked'] == 1) && ($this->mitsuba->admin->checkPermission("post.closed"))) {

                //echo "<center><h1>" . $lang['img/thread_locked'] . "</h1><br /><a href='./" . $board . "'>" . $lang['img/return'] . "</a></center>";
                $this->mitsuba->common->showMsg($lang['img/error'], $lang['img/thread_locked']);
                exit;

            }

        }

        $lastbumped = time();

        $trip = "";

        $strip = "";

        if (($bdata['noname'] == 0) || ($this->mitsuba->admin->checkPermission("post.ignorenoname"))) {

            $arr = $this->mitsuba->common->processName($name);

            $trip = $arr['trip'];

            $name = $arr['name'];

            $strip = $arr['strip'];

        } else {

            $name = $lang['img/anonymous'];

            if (!empty($bdata['anonymous'])) {

                $name = $bdata['anonymous'];

            }

            /*if (($email != "nonoko") || ($email != "nonokosage") || ($email != "noko") || ($email != "nokosage") || ($email != "sage"))

            {

            $email = "";

            }*/

        }

        if ($raw == 0) {

            $comment = $this->escapeMitsubaSpecialCharacters($comment);

        }

        $pdata = array("board" => & $board, "name" => & $name, "trip" => & $trip, "strip" => & $strip, "email" => & $email, "subject" => & $subject, "comment" => & $comment, "password" => & $password, "filename" => & $filename, "orig_filename" => & $origFilename, "mimetype" => & $mimetype, "resto" => & $resto, "md5" => & $md5, "t_w" => & $thumbW, "t_h" => & $thumbH, "spoiler" => & $spoiler, "embed" => & $embed, "raw" => & $raw, "sticky" => & $sticky, "locked" => & $locked, "nolimit" => & $nolimit, "nofile" => & $nofile, "fake_id" => & $fakeID, "cc_text" => & $capText, "cc_style" => & $capStyle, "cc_icon" => & $capIcon, "custom_fields" => & $customFields);

        $e = array("postdata" => & $pdata, "requestdata" => & $_POST);

        $this->mitsuba->emitEvent("posting.post", $e);

        $oldEmail = $email;

        if (($bdata['noname'] == 1) && (!empty($email)) && ($this->mitsuba->admin->checkPermission("post.ignorenoname"))) {

            if (($email == strtolower("noko")) || ($email == strtolower("nonoko"))) {

                $email = "";

            } elseif (($email == strtolower("nokosage")) || ($email == strtolower("nonokosage")) || ($email == strtolower("sage"))) {

                $email = "sage";

            } else {

                $email = "";

            }

        }

        $mimetype = $this->conn->real_escape_string($mimetype);

        $md5 = $this->conn->real_escape_string($md5);

        $posterID = "";

        if (!empty($fakeID)) {

            $posterID = $fakeID;

        } else {

            if ($bdata['ids'] == 1) {

                if ($resto != 0) {

                    $posterID = $this->mitsuba->common->mkid($this->mitsuba->common->getIP(), $resto, $board);

                }

            }

        }

        $isize = "";

        $osize = 0;

        $fsize = "";

        if ((!empty($fname2)) && ($fname2 != "embed") && ($fname2 != "url")) {

            if (substr($filename, 0, 8) == "spoiler:") {

                $d = getimagesize("./" . $board . "/src/" . substr($filename, 8));

                $isize = $d[0] . "x" . $d[1];

                $osize = filesize("./" . $board . "/src/" . substr($filename, 8));

                $fsize = $this->mitsuba->common->human_filesize($osize);

            } else {

                $d = getimagesize("./" . $board . "/src/" . $filename);

                $isize = $d[0] . "x" . $d[1];

                $osize = filesize("./" . $board . "/src/" . $filename);

                $fsize = $this->mitsuba->common->human_filesize($osize);

            }

        }

        if (empty($capText)) {

            $capText = "";

            $capStyle = "";

            $capIcon = "";

        } else {

            $capText = $this->conn->real_escape_string(htmlspecialchars($capText));

            $capStyle = $this->conn->real_escape_string(htmlspecialchars($capStyle));

            $capIcon = $this->conn->real_escape_string(htmlspecialchars($capIcon));

        }

        $customFieldsNames = "";

        $customFieldsValues = "";

        $allFields = $this->conn->query("SELECT * FROM module_fields WHERE type='postfield';");

        $fields = array();

        while ($row = $allFields->fetch_assoc()) {

            $fields[$row['name']] = 1;

        }

        foreach ($customFields as $key => $value) {

            if (!empty($fields[$key])) {

                $customFieldsNames.= ", " . $this->conn->real_escape_string($key);

                $customFieldsValues.= ", '" . $this->conn->real_escape_string($value) . "'";

            }

        }

        $email = $email ?? '';
        $subject = $subject ?? '';
        $comment = $comment ?? '';
        $origFilename = $origFilename ?? '';
        $filename = $filename ?? '';
        $capText = $capText ?? '';
        $capStyle = $capStyle ?? '';
        $capIcon = $capIcon ?? '';
        $posterID = $posterID ?? '';
        $trip = $trip ?? '';
        $name = $name ?? '';

        $md5 = $md5 ?? ''; 
        $mimetype = $mimetype ?? ''; 

        $resto = $resto ?? 0;
        $lastbumped = $lastbumped ?? 0;
        $osize = $osize ?? 0;
        $thumbW = $thumbW ?? 0;
        $thumbH = $thumbH ?? 0;
        $sticky = $sticky ?? 0;
        $locked = $locked ?? 0;
        $raw = $raw ?? 0;

        $fsize = $fsize ?? ''; 
        $isize = $isize ?? ''; 

        $customFieldsNames = $customFieldsNames ?? '';
        $customFieldsValues = $customFieldsValues ?? '';

        $strip = substr($strip, 0, 50);

        $this->conn->query("INSERT INTO posts (board, `date`, name, trip, strip, poster_id, email, subject, comment, password, orig_filename, filename, resto, ip, lastbumped, filehash, orig_filesize, filesize, imagesize, mimetype, t_w, t_h, sticky, sage, locked, raw, capcode_text, capcode_style, capcode_icon, deleted" . $customFieldsNames . ")" . 

        "VALUES ('" . $this->conn->real_escape_string($board) . "', " . 

        time() . ", '" . 

        $this->conn->real_escape_string($name) . "', '" . 

        $this->conn->real_escape_string($trip) . "', '" . 

        $this->conn->real_escape_string($strip) . "', '" . 

        $this->conn->real_escape_string($posterID) . "', '" . 

        $this->conn->real_escape_string($this->mitsuba->common->processString($email)) . "', '" . 

        $this->conn->real_escape_string($this->mitsuba->common->processString($subject)) . "', '" . 

        $this->conn->real_escape_string($this->mitsuba->common->preprocessComment($comment)) . "', '" . 

        md5($password) . "', '" . 

        $this->conn->real_escape_string($this->mitsuba->common->processString($origFilename)) . "', '" . 

        $this->conn->real_escape_string($filename) . "', " . 

        $resto . ", '" . 

        $this->conn->real_escape_string($this->mitsuba->common->getIP()) . "', " . 

        $lastbumped . ", '" . 

        $this->conn->real_escape_string($md5) . "', " . 

        $osize . ", '" . 

        $this->conn->real_escape_string($fsize) . "', '" . 

        $this->conn->real_escape_string($isize) . "', '" . 

        $this->conn->real_escape_string($mimetype) . "', " . 

        $thumbW . ", " . 

        $thumbH . ", " . 

        $sticky . ", 0, " . 

        $locked . ", " . 

        $raw . ", '" . 

        $this->conn->real_escape_string($capText) . "', '" . 

        $this->conn->real_escape_string($capStyle) . "', '" . 

        $this->conn->real_escape_string($capIcon) . "', 0" . 

        (empty($customFieldsValues) ? "" : "," . $customFieldsValues) . 

        ")");

        $id = $this->conn->insert_id;

        if (empty($fakeID)) {

            $posterID = "";

            if ($bdata['ids'] == 1) {

                if ($resto == 0) {

                    $posterID = $this->mitsuba->common->mkid($this->mitsuba->common->getIP(), $id, $board);

                }

            }

            if ($posterID != "") {

                $this->conn->query("UPDATE posts SET poster_id='" . $this->conn->real_escape_string($posterID) . "' WHERE id=" . $id . " AND board='" . $board . "'");

            }

        }

        if ($resto != 0) {

            if (($email == strtolower("sage")) || ($tinfo['sage'] == 1) || ($replies > $bdata['bumplimit'])) {

            } else {

                $this->conn->query("UPDATE posts SET lastbumped=" . time() . " WHERE id=" . $resto . " AND board='" . $board . "'");

            }

        }

        $email = $oldEmail;

        if (($bdata['type'] == "linkboard") || ($bdata['type'] == "fileboard")) {

            $email = "nonoko";

        }

        if ($redirect == 1) {

            if (($email == "nonoko") || ($email == "nonokosage")) {

                echo '<meta http-equiv="refresh" content="2;URL=' . "'./mod.php?/board&b=" . $board . "'" . '">';

            } else {

                if ($resto != 0) {

                    echo '<meta http-equiv="refresh" content="2;URL=' . "'./mod.php?/board&b=" . $board . "&t=" . $resto . "#p" . $id . "" . '">';

                } else {

                    echo '<meta http-equiv="refresh" content="2;URL=' . "'./mod.php?/board&b=" . $board . "&t=" . $id . "'" . '">';

                }

            }

        } else {

            if (($email == "nonoko") || ($email == "nonokosage")) {

                echo '<meta http-equiv="refresh" content="2;URL=' . "'./" . $board . "/index.html'" . '">';

            } else {

                if ($resto != 0) {

                    echo '<meta http-equiv="refresh" content="2;URL=' . "'./" . $board . "/res/" . $resto . ".html#p" . $id . "" . '">';

                } else {

                    echo '<meta http-equiv="refresh" content="2;URL=' . "'./" . $board . "/res/" . $id . ".html'" . '">';

                }

            }

        }

        if ($resto == 0) {

            $this->mitsuba->common->pruneOld($board);

        }

        if ($resto == 0) {

            $this->mitsuba->caching->generateView($board, $id);

            if ($config['caching_mode'] == 1) {

                $this->mitsuba->caching->forceGetThread($board, $id);

            }

            if ($config['enable_api'] == 1) {

                $this->mitsuba->caching->serializeThread($board, $id);

            }

        } else {

            $this->mitsuba->caching->generateView($board, $resto);

            if ($config['caching_mode'] == 1) {

                $this->mitsuba->caching->forceGetThread($board, $resto);

            }

            if ($config['enable_api'] == 1) {

                $this->mitsuba->caching->serializeThread($board, $resto);

            }

        }

        if ($bdata['catalog'] == 1) {

            $this->mitsuba->caching->generateCatalog($board);

        }

        $this->mitsuba->caching->generateView($board);

        $this->mitsuba->caching->generateFrontpage("onPostCreated");

        if ($config['enable_api'] == 1) {

            $this->mitsuba->caching->serializeBoard(isset($_GET['b']));

        }

        //oh please generate a new index page :p

        $this->mitsuba->caching->generateFrontpage();

    }

    /**
     * reportPost
     * Insert description here
     *
     * @param $board
     * @param $reportID
     * @param $reason
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function reportPost($board, $reportID, $reason){

        if (is_numeric($reportID)) {

            $board = $this->conn->real_escape_string($board);

            if (!$this->mitsuba->common->isBoard($board)) {

                return -16;

            }

            $result = $this->conn->query("SELECT * FROM posts WHERE id=" . $reportID . " AND board='" . $board . "'");

            if ($result->num_rows == 1) {

                $postdata = $result->fetch_assoc();

                $result = $this->conn->query("SELECT * FROM reports WHERE reported_post=" . $reportID . " AND board='" . $board . "'");

                if ($result->num_rows == 0) {

                    $reason = $this->conn->real_escape_string(htmlspecialchars($reason));

                    $this->conn->query("INSERT INTO reports (reporter_ip, reported_post, reason, created, board) VALUES ('" . $this->mitsuba->common->getIP() . "', " . $reportID . ", '" . $reason . "', " . time() . ", '" . $board . "')");

                } else {

                    return 1;

                }

            } else {

                return -15;

            }

        } else {

            return -15;

        }

    }

}

?>
