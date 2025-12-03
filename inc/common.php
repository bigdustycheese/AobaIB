<?php

namespace Mitsuba;

/**
 * Common
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
class Common {

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
    function __construct($connection, &$mitsuba) {

        $this->conn = $connection;

        $this->mitsuba = $mitsuba;

    }

    /**
     * getEmbed
     * Insert description here
     *
     * @param $url
     * @param $embedTable
     * @param $s
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function getEmbed($url, $embedTable = null, $s = 250) {

        foreach ($embedTable as $row) {

            if (preg_match($row['regex'], $url, $vresult)) {

                $vresult[0] = $s;

                foreach ($vresult as $k => $v) {

                    $vresult[$k] = htmlspecialchars($v);

                }

                return vsprintf($row['code'], $vresult);

            }

        }

        return 0;

    }

    /**
     * addSystemBan
     * Insert description here
     *
     * @param $ipAddress
     * @param $reason
     * @param $note
     * @param $expires
     * @param $boards
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function addSystemBan($ipAddress, $reason, $note, $expires, $boards) {

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

                $expires = $this->parse_time($expires);

                $perma = 0;

            }

            if (($expires == false) && ($perma == 0)) {

                return -2;

            }

            $this->conn->query("INSERT INTO bans (ip, mod_id, reason, note, created, expires, boards) VALUES ('" . $ipAddress . "', 0, '" . $reason . "', '" . $note . "', " . $created . ", " . $expires . ", '" . $boards . "');");

            return 1;

        }

    }

    /**
     * isEmbed
     * Insert description here
     *
     * @param $url
     * @param $embedTable
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function isEmbed($url, $embedTable = null) {

        foreach ($embedTable as $row) {

            if (preg_match($row['regex'], $url, $vresult)) {

                return 1;

            }

        }

        return 0;

    }

    /**
     * human_filesize
     * Insert description here
     *
     * @param $bytes
     * @param $decimals
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function human_filesize($bytes, $decimals = 2) {

        $size = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $size[$factor];

    }

    /**
     * mkid
     * Insert description here
     *
     * @param $ipAddress
     * @param $topic
     * @param $board
     * @param $junk
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function mkid($ipAddress, $topic, $board, $junk = "") {

        global $id_salt;

        return substr(crypt(md5($ipAddress . 't' . $board . $topic . $junk . $id_salt), $id_salt), -8);

    }

    /**
     * getIP
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function getIP(){
    $fields = array('HTTP_X_FORWARDED_FOR',
                    'REMOTE_ADDR',
                    'HTTP_CF_CONNECTING_IP',
                    'HTTP_X_CLUSTER_CLIENT_IP');

    foreach($fields as $f)
    {
        $tries = isset($_SERVER[$f]) ? $_SERVER[$f] : '';
        if (empty($tries))
            continue;
        $tries = explode(',',$tries);
        foreach($tries as $try)
        {
            $r = filter_var($try,
                            FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 |
                            FILTER_FLAG_NO_PRIV_RANGE |
                            FILTER_FLAG_NO_RES_RANGE);

            if ($r !== false)
            {
                return $try;
            }else {
              return '0.0.0.0';
            }
        }
    }
    return false;
}

    /**
     * getGraphicsExtension
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function getGraphicsExtension() {

        if (extension_loaded('imagick')) {

            return "imagick";

        } elseif (extension_loaded('gd')) {

            return "gd";

        } else {

            return 0;

        }

    }

    /**
     * getBoardData
     * Insert description here
     *
     * @param $short
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function getBoardData($short) {
        return $this->isBoard($short); //yeah, yeah, I know...
    }

    /**
     * isLocked
     * Insert description here
     *
     * @param $short
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */

    function isLocked($id){
      $result = $this->conn->query("SELECT * FROM posts WHERE `id`='".$this->conn->real_escape_string($id)."' AND `locked`=1");
      if ($result->num_rows == 1){
        return true;
      }else{
        return false;
      }
    }

    /**
     * isBoard
     * Insert description here
     *
     * @param $short
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function isBoard($short) {
        $result = $this->conn->query("SELECT * FROM boards WHERE short='" . $this->conn->real_escape_string($short) . "'");
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }

    /**
     * showMsg
     * Insert description here
     *
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
    function showMsg($title, $text) {

        global $lang;
        echo "<html>
        <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
        <title>$title</title>
        <script type='text/javascript' src='/js/style.js'></script>
        </head>
        <body>
        <p align='center' style='font-size: x-large; font-weight: bold;'>
        <span id='errmsg' style='color: red;'>$text</span>
        <br><br>";
        if (isset($_POST['board'])) {
          echo "[<a href='/{$_POST['board']}/'>{$lang['img/return']}</a>]";
        }else {
          echo "[<a href='/'>{$lang['img/return']}</a>]";
        }
        echo "</p>
        <style>
        body{
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          width: 100%;
          height: 100vh;
        }
        </style>
        </body>
        </html>";
    }

    /**
     * thumb
     * Insert description here
     *
     * @param $board
     * @param $filename
     * @param $ext
     * @param $s
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function thumb($board, $filename, $ext, $s = 250) {
    $thumbDirectory = './'.$board.'/src/thumb/';
    $filePath = './'.$board.'/src/'.$filename.$ext;

    if (!file_exists($filePath)) {
        return ["width" => 0, "height" => 0];
    }

    $extension = $this->getGraphicsExtension();

    if ($ext == ".webm" || $ext == ".mp4") {
        require_once dirname(__FILE__) . '/webm.class.php';
        $movie = new \webm($filePath);
        if ($movie->thumbnail($thumbDirectory.$filename.'.gif', $s, $s)) {
            return ["width" => $s, "height" => $s];
        }
        return ["width" => 0, "height" => 0];
    }

    // Manejo de URLs embebidas
    if (str_contains($filename ?? '', "url:")) {
        return ["width" => 0, "height" => 0];
    }

    // Manejo de imágenes con Imagick
    if (($extension == "imagick") && !($ext == ".webm") && !($ext == ".mp4")) {
        try {
            $img = new \Imagick($filePath);
            $img->setImageColorspace(13);
            $img = $img->coalesceImages();
            foreach ($img as $frame) {
                $frame->thumbnailImage($s, $s, true);
            }
            $img->writeImages($thumbDirectory . $filename . $ext, true);
            $geometry = $img->getImageGeometry();
            $img->destroy();
            return $geometry;
        } catch (\Exception $e) {
            return ["width" => 0, "height" => 0];
        }
    }

    if ($extension == "gd") {
        $size = @getimagesize($filePath);
        if (!$size || !isset($size[2])) return ["width" => 0, "height" => 0];

        switch ($size[2]) {
            case IMAGETYPE_GIF: $im_in = @imagecreatefromgif($filePath); $type="gif"; break;
            case IMAGETYPE_JPEG: $im_in = @imagecreatefromjpeg($filePath); $type="jpg"; break;
            case IMAGETYPE_PNG: $im_in = @imagecreatefrompng($filePath); $type="png"; break;
            default: return ["width" => 0, "height" => 0];
        }
        if (!$im_in) return ["width" => 0, "height" => 0];

        $ratio = min($s/$size[0], $s/$size[1]);
        $out_w = ceil($size[0]*$ratio);
        $out_h = ceil($size[1]*$ratio);

        $im_out = function_exists("imagecreatetruecolor") ? imagecreatetruecolor($out_w, $out_h) : imagecreate($out_w, $out_h);
        imagealphablending($im_out, false);
        imagesavealpha($im_out, true);
        $trans_layer_overlay = imagecolorallocatealpha($im_out, 220, 220, 220, 127);
        imagefill($im_out, 0, 0, $trans_layer_overlay);

        imagecopyresampled($im_out, $im_in, 0, 0, 0, 0, $out_w, $out_h, $size[0], $size[1]);

        switch ($type) {
            case "jpg": imagejpeg($im_out, $thumbDirectory.$filename.$ext, 70); break;
            case "png": imagepng($im_out, $thumbDirectory.$filename.$ext, 9); break;
            case "gif": imagegif($im_out, $thumbDirectory.$filename.$ext); break;
        }

        chmod($thumbDirectory.$filename.$ext, 0666);
        imagedestroy($im_in);
        imagedestroy($im_out);

        return ["width"=>$out_w, "height"=>$out_h];
    }

    return ["width" => 0, "height" => 0];
}

    /**
     * delTree
     * Insert description here
     *
     * @param $dir
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function delTree($dir) {

        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {

            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");

        }

        return rmdir($dir);

    }

    /**
     * isWhitelisted
     * Insert description here
     *
     * @param $ipAddress
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function isWhitelisted($ipAddress) {

        $whitelist = $this->conn->query("SELECT * FROM whitelist WHERE ip='" . $ipAddress . "' ORDER BY id DESC LIMIT 0, 1");

        if ($whitelist->num_rows >= 1) {

            $wlistdata = $whitelist->fetch_assoc();

            if ($wlistdata['nolimits'] == 1) {

                return 2;

            }

            return 1;

        } else {

            return 0;

        }

    }
    //thanks Rijndael. Easily en/decrypting your PMs since whenever.
    /**
     * dec_enc
     * Insert description here
     *
     * @param $action
     * @param $string
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
     function dec_enc($action, $string) {
       global $securetrip_salt, $id_salt;
         $output = false;

         $encrypt_method = "AES-256-CBC";
         $secret_key = $securetrip_salt;
         $secret_iv = $id_salt;

         // hash
         $key = hash('sha256', $secret_key);

         // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
         $iv = substr(hash('sha256', $secret_iv), 0, 16);

         if( $action == 'encrypt' ) {
             $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
             $output = base64_encode($output);
         }
         else if( $action == 'decrypt' ){
             $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
         }

         return $output;
     }
    /**
     * startsWith
     * Insert description here
     *
     * @param $haystack
     * @param $needle
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function startsWith($haystack, $needle) {

        return !strncmp($haystack, $needle, strlen($needle));

    }

    /**
     * endsWith
     * Insert description here
     *
     * @param $haystack
     * @param $needle
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function endsWith($haystack, $needle) {

        $length = strlen($needle);

        if ($length == 0) {

            return true;

        }

        return (substr($haystack, -$length) === $needle);

    }

    /*function randomPassword() {

    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";

    $pass = array();

    $alphaLength = strlen($alphabet) - 1;

    for ($i = 0; $i < 8; $i++) {

    $n = rand(0, $alphaLength);

    $pass[] = $alphabet[$n];

    }

    return implode($pass);

    }*/

    /**
     * randomPassword
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function randomPassword() {

        $fp = @fopen('/dev/urandom', 'rb');

        $result = '';

        if ($fp !== FALSE) {

            $result.= @fread($fp, 8);

            @fclose($fp);

        } else {

            $seed = random_bytes(32);
        }

        // convert from binary to string

        $result = base64_encode($result);

        // remove none url chars

        $result = strtr($result, '+/', '-_');

        // Remove = from the end

        $result = str_replace('=', ' ', $result);

        return $result;

    }

    /**
     * randomSalt
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function randomSalt() {

        $alphabet = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789+_-)(*&^%$#@!~|';

        $pass = array();

        $alphaLength = strlen($alphabet) - 1;

        for ($i = 0;$i < 15;$i++) {

            $n = rand(0, $alphaLength);

            $pass[] = $alphabet[$n];

        }

        return implode($pass);

    }

    /**
     * getsecuretripcode
     * Insert description here
     *
     * @param $pwd
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function getsecuretripcode($pwd) {

        $striphash = $this->mktripcode(substr($pwd, 1));

        $strips = $this->conn->query("SELECT * FROM tripcodes WHERE hash='" . $striphash . "' AND secure=1");

        if ($strips->num_rows >= 1) {

            $row = $strips->fetch_assoc();

            return $row['replace'];

        } else {

            $strip = $this->mksecuretripcode($pwd);

            $this->conn->query("INSERT INTO tripcodes (`hash`, `replace`, `secure`) VALUES ('" . $striphash . "', '" . $strip . "', 1);");

            return $strip;

        }

    }

    /**
     * processName
     * Insert description here
     *
     * @param $string
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function processName($string) {

        $arr = array();

        $new = $string;

        //$new = str_replace("##", "#", $new);

        $exploded = explode("#", $new, 2);

        $arr['trip'] = "";

        $arr['name'] = "";

        $arr['strip'] = "";

        //$arr['striphash'] = "";

        if (count($exploded) > 1) {

            $arr['name'] = $exploded[0];

            if (substr($exploded[1], 0, 1) == "#") {

                $moretrips = explode("#", substr($exploded[1], 1), 2);

                if (count($moretrips) > 1) {

                    $arr['strip'] = $this->getsecuretripcode($moretrips[0]);

                    $arr['trip'] = $this->mktripcode($moretrips[1]);

                } else {

                    $arr['strip'] = $this->getsecuretripcode(substr($exploded[1], 1));

                }

            } else {

                $moretrips = explode("#", $exploded[1], 2);

                if (count($moretrips) > 1) {

                    if (substr($moretrips[1], 0, 1) == "#") {

                        $arr['strip'] = $this->getsecuretripcode(substr($moretrips[1], 1));

                    } else {

                        $arr['strip'] = $this->getsecuretripcode($moretrips[1]);

                    }

                    $arr['trip'] = $this->mktripcode($moretrips[0]);

                } else {

                    $arr['trip'] = $this->mktripcode($exploded[1]);

                }

            }

        } else {

            $arr['name'] = $new;

            $arr['trip'] = "";

        }

        $arr['name'] = $this->conn->real_escape_string($arr['name']);

        $arr['name'] = htmlspecialchars($arr['name']);

        return $arr;

    }

    /**
     * processString
     * Insert description here
     *
     * @param $string
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function processString($string) {

        $new = $string;

        $new = $this->conn->real_escape_string($new);

        $new = htmlspecialchars($new);

        return $new;

    }

    /**
     * preprocessComment
     * Insert description here
     *
     * @param $string
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function preprocessComment($string) {

        $new = str_replace("\r", "", $string);

        $new = $this->conn->real_escape_string($new);

        return $new;

    }

    /**
     * isFile
     * Insert description here
     *
     * @param $path
     * @param $boardFiles
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function isFile($path, $boardFiles = "%") {

        $mime = "";

        if (empty($boardFiles)) {

            $boardFiles = "%";

        }

        if (function_exists("finfo_file")) {

            $finfo = finfo_open();

            $mime = finfo_file($finfo, $path, FILEINFO_MIME_TYPE);

        } elseif (function_exists("mime_content_type")) {

            $mime = mime_content_type($path);

        } else {

            if (function_exists("getimagesize")) {

                $imageSize = getimagesize($path);

                $imageType = $imageSize[2];

                $mime = image_type_to_mime_type($imageType);

            } else {

                return false;

            }

        }

        $extensions = $this->conn->query("SELECT * FROM extensions WHERE mimetype='" . $this->conn->real_escape_string($mime) . "'");

        if ($extensions->num_rows == 1) {

            $ext = $extensions->fetch_assoc();

            if (($boardFiles == "%") || (in_array($ext['ext'], explode(",", $boardFiles)))) //If the extensions in $boardFiles are in $ext['ext']

            {

                $nfo['extension'] = $ext['ext'];

                $nfo['image'] = $ext['image'];

                $nfo['mimetype'] = $mime;

                return $nfo;

            } else {

                return false;

            }

        } else {

            return false;

        }

    }

    /**
     * mktripcode
     * Insert description here
     *
     * @param $pass
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function mktripcode($pass) {

        $pass = mb_convert_encoding($pass, 'SJIS', 'UTF-8');

        $salt = substr($pass . 'H.', 1, 2);

        $salt = preg_replace('/[^\.-z]/', '.', $salt);

        $salt = strtr($salt, ':;<=>?@[\]^_`', 'ABCDEFGabcdef');

        $trip = substr(crypt($pass, $salt), -10);

        return $trip;

    }

    /**
     * mksecuretripcode
     * Insert description here
     *
     * @param $pass
     * @param $junk
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function mksecuretripcode($pass, $junk = "r3volution") {

        global $securetrip_salt;

        $pass = mb_convert_encoding($pass, 'SJIS', 'UTF-8');

        $pass = str_replace('&', '&amp;', $pass);

        $pass = str_replace('"', '&quot;', $pass);

        $pass = str_replace("'", '&#39;', $pass);

        $pass = str_replace('<', '&lt;', $pass);

        $pass = str_replace('>', '&gt;', $pass);

        $randomstring = "";

        $poststring = "";

        foreach ($_POST as $key => $value) {

            $poststring.= $key . "!" . $value;

        }

        $poststring = strtr($poststring, ':;<=>?@[\]^_`', 'ABCDEFGabcdef');

        $randomstring = md5($poststring) . time() . rand(90, 1681018501) . $junk;

        $salt = substr($pass . 'H!' . $randomstring, 1, 2);

        $salt = preg_replace('/[^.\/0-9:;<=>?@A-Z\[\\\]\^_`a-z]/', '.', $salt);

        $salt = strtr($salt, ':;<=>?@[\]^_`', 'ABCDEFGabcdef');

        $trip = crypt($pass . $randomstring . $securetrip_salt, $salt);

        return $trip;

    }

    /**
     * isBanned
     * Insert description here
     *
     * @param $ipAddress
     * @param $board
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */

    function isBanned($ipAddress, $board) {

        $ipAddressbans = $this->conn->query("SELECT * FROM bans WHERE ip='" . $ipAddress . "' AND (expires>" . time() . " OR expires=0) ORDER BY expires DESC;");

        $rangebans = $this->conn->query("SELECT * FROM rangebans ORDER BY expires DESC;");

        $ipAddressbandata = null;

        $rangebandata = null;

        $bandata = null;

        $otherbans = array();

        while ($row = $rangebans->fetch_assoc()) {

            $range = str_replace('*', '(.*)', $row['ip']);

            if ($this->startsWith($range, ".")) {

                if ((strpos($ipAddress, $range) !== FALSE)) {

                    if ($row['boards'] == "%") {

                        $rangebandata = $row;

                        $rangebandata['range'] = 1;

                    } else {

                        if ($board == "%") {

                            $rangebandata = $row;

                            $rangebandata['range'] = 1;

                        } else {

                            $boards = explode(",", $row['boards']);

                            if (in_array($board, $boards)) {

                                $rangebandata = $row;

                                $rangebandata['range'] = 1;

                            }

                        }

                    }

                    $otherbans[] = $row;

                    $otherbans[count($otherbans) - 1]['range'] = 1;

                }

            } elseif ($this->startsWith($ipAddress, $range)) {

                if ($row['boards'] == "%") {

                    $rangebandata = $row;

                    $rangebandata['range'] = 1;

                } else {

                    if ($board == "%") {

                        $rangebandata = $row;

                        $rangebandata['range'] = 1;

                    } else {

                        $boards = explode(",", $row['boards']);

                        if (in_array($board, $boards)) {

                            $rangebandata = $row;

                            $rangebandata['range'] = 1;

                        }

                    }

                }

                $otherbans[] = $row;

                $otherbans[count($otherbans) - 1]['range'] = 1;

            } elseif (preg_match('/' . $range . '/', $ipAddress)) {

                if ($row['boards'] == "%") {

                    $rangebandata = $row;

                    $rangebandata['range'] = 1;

                } else {

                    if ($board == "%") {

                        $rangebandata = $row;

                        $rangebandata['range'] = 1;

                    } else {

                        $boards = explode(",", $row['boards']);

                        if (in_array($board, $boards)) {

                            $rangebandata = $row;

                            $rangebandata['range'] = 1;

                        }

                    }

                }

                $otherbans[] = $row;

                $otherbans[count($otherbans) - 1]['range'] = 1;

            }

        }

        while ($row = $ipAddressbans->fetch_assoc()) {

            if ((empty($ipAddressbandata)) || ($ipAddressbandata['expires'] < $row['expires'])) {

                if ($row['boards'] == "%") {

                    $ipAddressbandata = $row;

                } else {

                    if ($board == "%") {

                        $ipAddressbandata = $row;

                    } else {

                        $boards = explode(",", $row['boards']);

                        if (in_array($board, $boards)) {

                            $ipAddressbandata = $row;

                        }

                    }

                }

            }

            $otherbans[] = $row;

            $otherbans[count($otherbans) - 1]['range'] = 0;

        }

        if (($ipAddressbandata != null) && ($rangebandata != null)) {

            if (($ipAddressbandata['expires'] == 0) || ($ipAddressbandata['expires'] > $rangebandata['expires'])) {

                $bandata = $ipAddressbandata;

            } elseif (($rangebandata['expires'] == 0) || ($rangebandata['expires'] > $ipAddressbandata['expires'])) {

                $bandata = $rangebandata;

            } else {

                $bandata = $ipAddressbandata;

            }

        } elseif (($ipAddressbandata != null) || ($rangebandata != null)) {

            if ($ipAddressbandata != null) {

                $bandata = $ipAddressbandata;

            } elseif ($rangebandata != null) {

                $bandata = $rangebandata;

            } else {

                return 0;

            }

        } else {

            return 0; //not banned



        }

        if (!empty($bandata)) {

            if (count($otherbans) >= 1) {

                $bandata['more'] = $otherbans;

            }

            return $bandata;

        }

        return 0;

    }

    /**
     * isWarned
     * Insert description here
     *
     * @param $ipAddress
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function isWarned($ipAddress) {

        $warns = $this->conn->query("SELECT * FROM warnings WHERE ip='" . $ipAddress . "' AND seen=0 ORDER BY created ASC LIMIT 0, 1;");

        if ($warns->num_rows == 1) {

            $warndata = $warns->fetch_assoc();

            return $warndata;

        } else {

            return 0;

        }

        return 0;

    }

    /**
     * banInfo
     * Insert description here
     *
     * @param $bandata
     * @param $board
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function banInfo($bandata, $board) {

        if ((empty($bandata['range'])) && ($bandata['seen'] == 0)) {

            $this->conn->query("UPDATE bans SET seen=1 WHERE id=" . $bandata['id']);

        }

        if ($bandata['boards'] == "%") {

            $boards = 1;

        } else {

            $boards = 0;

        }

        if ($bandata['expires'] != 0) {

            $left = floor(($bandata['expires'] - time()) / (60 * 60 * 24));
            if ($left == 0) {
              $left = self::seconds2human($bandata['expires']- time());
            }
            $days = floor(($bandata['expires'] - $bandata['created']) / (60 * 60 * 24));

        } else {

            $left = - 1;

            $days = - 1;

        }

?>

		<p>You have been <?php if ($left == - 1) {

            echo "<b>permanently</b>";

        } ?> <?php if (!empty($bandata['range'])) {

            echo "<b>range-</b>";

        } ?>banned from <b><?php if ($boards == 1) {

            echo "all ";

        } else {

            echo "few ";

        } ?></b>boards for the following reason:</p>

		<p><?php echo $bandata['reason']; ?></p>

		<p>You were banned on <b><?php echo date("d/m/Y (D) H:i:s", $bandata['created']); ?></b> and your ban expires

		<b><?php if ($left != - 1) {

            echo " </b>on <b>" . date("d/m/Y (D) H:i:s", $bandata['expires']) . ",</b> which is <b>" . $left . "</b> from now.";

        } else {

            echo " never";

        }; ?></p>

		<p>According to our server your IP is: <b><?php echo self::getIP(); ?></b></p>
		<?php

        $range = 0;

        if (!empty($bandata['range_ip'])) {

            $range = 1;

        }

        $appeals = $this->conn->query("SELECT * FROM appeals WHERE ban_id=" . $bandata['id'] . " AND rangeban=" . $range);

        //Your appeal has been sent and is waiting until review, you can change it here.

        $appeal = ($bandata['appeal'] - time()) / (60 * 60 * 24);

        if (($bandata['appeal'] != 0) && ($appeal < 0)) {

?>

			<br /><p>You may appeal your ban in the form below. Please explain why you deserve to be unbanned.</p>

			<?php

            $appMsg = "";

            $appMail = "";

            if ($appeals->num_rows == 1) {

                $appealdata = $appeals->fetch_assoc();

                $appMsg = $appealdata['msg'];

                $appMail = $appealdata['email'];

                echo "<b>Your appeal has been sent and is waiting until review, you can change it here.</b>";

            }

?>

			<form action="/imgboard.php" method="POST" class="col s12">
			<input type="hidden" name="mode" value="usrapp" />
			<input type="hidden" name="banid" value="<?php echo $bandata['id']; ?>" />
			<input type="hidden" name="banrange" value="<?php echo $range; ?>" />
			<div class="input-field col s6">
			</div>
			<div class="input-field col s12">
			<textarea id="appeal" class="materialize-textarea" rows="6" name="msg"><?php echo $appMsg; ?></textarea>
			<label for="appeal">Appeal Message</label>
			</div>
			<!--<input type="submit" value="Submit">-->
			<!-- button is bugged... I know, I know.-->
			<button type="submit" class="btn-floating btn-large waves-effect waves-light red"><i class="material-icons">send</i></button>
			</form>

			<?php

        } elseif ($bandata['appeal'] != 0) {

?>

			<p>You'll be allowed to appeal your ban in <b><?php echo floor(($bandata['appeal'] - time()) / (60 * 60 * 24)); ?></b> days.</p>

			<?php

        } else {

?>

			<p>You may not appeal your ban.</p>

			<?php

        }

    }

    /**
     * banMessage
     * Insert description here
     *
     * @param $board
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function banMessage($board = "%") {

        $bandata = $this->isBanned(self::getIP(), $board);

        if ($bandata != 0) {

?>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
		<meta charset="UTF-8">
		<title>You are banned ;-;</title>
		<link href="/css/MIcons.css" rel="stylesheet">
		<link href="/css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
		<link href="/css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
	</head>

	<body>

		<div class="navbar-fixed">
			<nav class="marooncolor" role="navigation">
				<div class="nav-wrapper container">
					<a id="logo-container" href="/" class="brand-logo">314chan</a>

					<ul class="right hide-on-med-and-down">
						<li><a href="rules.html">Rules</a></li>
						<li><a href="faq.html">FAQ</a></li>
						<li><a href="news.html">News</a></li>
						<li><a href="https://irc.314chan.org">IRC</a></li>
					</ul>

					<ul id="nav-mobile" class="side-nav" style="left: -250px;">
						<li><a href="rules.html">Rules</a></li>
						<li><a href="faq.html">FAQ</a></li>
						<li><a href="news.html">News</a></li>
						<li><a href="https://irc.314chan.org">IRC</a></li>
					</ul>

					<a href="#" data-activates="nav-mobile" class="button-collapse"><i class="material-icons">menu</i></a>
				</div>
			</nav>
		</div>

		<div class="container">
			<div class="card-panel">
				<h4>You are banned ;_;</h4>
				<img style="float: right;" src="/banned/r.php" alt="Haruko" height="155px"/>
				<?php
					$this->banInfo($bandata, $board);
					if ((!empty($bandata['more'])) && (count($bandata['more']) > 1)) {
				?>

				<p><b>There's more than one ban placed on your IP.</b></p>
					<?php
						foreach ($bandata['more'] as $ban) {
							echo "<hr />";
							$this->banInfo($ban, $board);
						}
					}
				?>
			</div>
		</div>
	</body>
</html>
	<?php
            die();
        }

    }

    /**
     * verifyBan
     * Insert description here
     *
     * @param $ipAddress
     * @param $banID
     * @param $range
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function verifyBan($ipAddress, $banID, $range) {

        if ((!is_numeric($banID)) || (!is_numeric($range))) {

            return false;

        }

        if ($range == 0) {

            $ban = $this->conn->query("SELECT * FROM bans WHERE id=" . $banID);

            if ($ban->num_rows == 1) {

                $binfo = $ban->fetch_assoc();

                if ($binfo['ip'] == $ipAddress) {

                    return true;

                }

            }

        } else {

            $ban = $this->conn->query("SELECT * FROM rangebans WHERE id=" . $banID);

            if ($ban->num_rows == 1) {

                $binfo = $ban->fetch_assoc();

                $range = str_replace('*', '(.*)', $binfo['ip']);

                if ($this->startsWith($range, ".")) {

                    if ((strpos($ipAddress, $range) !== FALSE)) {

                        return true;

                    }

                } elseif ($this->startsWith($ipAddress, $range)) {

                    return true;

                } elseif (preg_match('/' . $range . '/', $ipAddress)) {

                    return true;

                }

            }

        }

        return false;

    }

    /**
     * warningMessage
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function warningMessage() {

        $warndata = $this->isWarned(self::getIP());

        if ($warndata != 0) {

?>
<html lang="en">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
		<meta charset="UTF-8">
		<title>You have been warned!</title>
		<link href="/css/MIcons.css" rel="stylesheet">
		<link href="/css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
		<link href="/css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
	</head>

	<body>

		<div class="navbar-fixed">
			<nav class="marooncolor" role="navigation">
				<div class="nav-wrapper container">
					<a id="logo-container" href="/" class="brand-logo">314chan</a>

					<ul class="right hide-on-med-and-down">
						<li><a href="rules.html">Rules</a></li>
						<li><a href="faq.html">FAQ</a></li>
						<li><a href="news.html">News</a></li>
						<li><a href="https://irc.314chan.org">IRC</a></li>
					</ul>

					<ul id="nav-mobile" class="side-nav" style="left: -250px;">
						<li><a href="rules.html">Rules</a></li>
						<li><a href="faq.html">FAQ</a></li>
						<li><a href="news.html">News</a></li>
						<li><a href="https://irc.314chan.org">IRC</a></li>
					</ul>

					<a href="#" data-activates="nav-mobile" class="button-collapse"><i class="material-icons">menu</i></a>
				</div>
			</nav>
		</div>

		<div class="container">
			<div class="card-panel">
				<h4>You were issued a warning! ;_;</h4>
				<p><strong><?php echo $warndata['reason']; ?></strong></p>
				<p>Your warning was issued on <b><?php echo date("d/m/Y (D) H:i:s", $warndata['created']); ?></b>.</p>
				<p>Now that you have seen this message, you should be able to post again. Click <a href="javascript:history.back()">here</a> to return.</p>
			</div>
		</div>
	</body>

</html>

	<?php

            $this->conn->query("UPDATE warnings SET seen=1 WHERE id=" . $warndata['id']);

            die();

        }

    }

    /**
     * pruneOld
     * Insert description here
     *
     * @param $board
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function pruneOld($board) {

        $board = $this->conn->real_escape_string($board);

        if (!$this->isBoard($board)) {

            return -16;

        }

        $bdata = $this->getBoardData($board);

        $toremove = 9001;

        if ($bdata['type'] == "fileboard") {

            $toremove = $bdata['files'] + 1;

        } elseif (($bdata['type'] == "imageboard") || ($bdata['type'] == "textboard")) {

            $toremove = ($bdata['pages'] + 2) * 10;

        } else {

            return 0;

        }

        $threads = $this->conn->query("SELECT * FROM posts WHERE resto=0 AND board='" . $board . "' AND deleted=0 ORDER BY sticky DESC, lastbumped DESC LIMIT " . $toremove . ", 2000");

        while ($row = $threads->fetch_assoc()) {

            $files = $this->conn->query("SELECT * FROM posts WHERE filename != '' AND resto=" . $row['id'] . " AND board='" . $board . "'");

            while ($file = $files->fetch_assoc()) {

                $filename = $file['filename'];

                if (substr($filename, 0, 8) == "spoiler:") {

                    $filename = substr($filename, 8);

                }

                if ((substr($filename, 0, 6) != "embed:") && ($filename != "deleted")) {

                    unlink("./" . $board . "/src/" . $filename);

                    if (file_exists("./" . $board . "/src/thumb/" . $filename)) {

                        unlink("./" . $board . "/src/thumb/" . $filename);

                    }

                }

            }

            $filename = $row['filename'];

            if (substr($filename, 0, 8) == "spoiler:") {

                $filename = substr($filename, 8);

            }

            if ((substr($filename, 0, 6) != "embed:") && ($filename != "deleted")) {

                unlink("./" . $board . "/src/" . $filename);

                if (file_exists("./" . $board . "/src/thumb/" . $filename)) {

                    unlink("./" . $board . "/src/thumb/" . $filename);

                }

            }

            $this->conn->query("DELETE FROM posts WHERE resto=" . $row['id'] . " AND board='" . $board . "'");

            $this->conn->query("DELETE FROM posts WHERE id=" . $row['id'] . " AND board='" . $board . "'");

            if ($bdata['hidden'] == 0) {

                unlink("./" . $board . "/res/" . $row['id'] . ".html");

            }

        }

        $deletedPosts = $this->conn->query("SELECT * FROM posts WHERE board='" . $board . "' AND deleted<" . (time() - 3600 * $this->mitsuba->config['keep_hours']) . " AND deleted<>0");

        while ($row = $deletedPosts->fetch_assoc()) {

            if ($row['resto'] == 0) {

                $files = $this->conn->query("SELECT * FROM posts WHERE filename != '' AND resto=" . $row['id'] . " AND board='" . $board . "'");

                while ($file = $files->fetch_assoc()) {

                    $filename = $file['filename'];

                    if (substr($filename, 0, 8) == "spoiler:") {

                        $filename = substr($filename, 8);

                    }

                    if ((substr($filename, 0, 6) != "embed:") && ($filename != "deleted")) {

                        unlink("./" . $board . "/src/" . $filename);

                        if (file_exists("./" . $board . "/src/thumb/" . $filename)) {

                            unlink("./" . $board . "/src/thumb/" . $filename);

                        }

                    }

                }

            }

            $filename = $row['filename'];

            if (substr($filename, 0, 8) == "spoiler:") {

                $filename = substr($filename, 8);

            }

            if ((substr($filename, 0, 6) != "embed:") && ($filename != "deleted")) {

                unlink("./" . $board . "/src/" . $filename);

                if (file_exists("./" . $board . "/src/thumb/" . $filename)) {

                    unlink("./" . $board . "/src/thumb/" . $filename);

                }

            }

            $this->conn->query("DELETE FROM posts WHERE resto=" . $row['id'] . " AND board='" . $board . "'");

            $this->conn->query("DELETE FROM posts WHERE id=" . $row['id'] . " AND board='" . $board . "'");

            if ($bdata['hidden'] == 0) {

                unlink("./" . $board . "/res/" . $row['id'] . ".html");

            }

        }

    }
    /**
     * seconds2human
     * Insert description here
     *
     * @param $str
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function seconds2human($ss) {
      $s = $ss%60;
      $m = floor(($ss%3600)/60);
      $h = floor(($ss%86400)/3600);
      $d = floor(($ss%2592000)/86400);
      $M = floor($ss/2592000);
      if(!empty($M)){
        $M = $M." months";
      }else {
        $M = "";
      }
      if(!empty($d)){
        $d = $d." days";
      }else {
        $d = "";
      }
      if(!empty($h)){
        $h = $h." hours";
      }else {
        $h = "";
      }
      if(!empty($m)){
        $m = $m." minutes";
      }else {
        $m = "";
      }
      if(!empty($s)){
        $s = "and ". $s." seconds";
      }else {
        $s = "";
      }
    return "$M $d $h $m $s ";
    }

    /**
     * parse_time
     * Insert description here
     *
     * @param $str
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function parse_time($str) {

        if (empty($str)) return false;

        if (($time = @strtotime($str)) !== false) return $time;

        if (!preg_match('/^((\d+)\s?ye?a?r?s?)?\s?+((\d+)\s?mon?t?h?s?)?\s?+((\d+)\s?we?e?k?s?)?\s?+((\d+)\s?da?y?s?)?((\d+)\s?ho?u?r?s?)?\s?+((\d+)\s?mi?n?u?t?e?s?)?\s?+((\d+)\s?se?c?o?n?d?s?)?$/', $str, $matches)) return false;

        $expire = 0;

        if (isset($matches[2])) {

            // Years

            $expire+= $matches[2] * 60 * 60 * 24 * 365;

        }

        if (isset($matches[4])) {

            // Months

            $expire+= $matches[4] * 60 * 60 * 24 * 30;

        }

        if (isset($matches[6])) {

            // Weeks

            $expire+= $matches[6] * 60 * 60 * 24 * 7;

        }

        if (isset($matches[8])) {

            // Days

            $expire+= $matches[8] * 60 * 60 * 24;

        }

        if (isset($matches[10])) {

            // Hours

            $expire+= $matches[10] * 60 * 60;

        }

        if (isset($matches[12])) {

            // Minutes

            $expire+= $matches[12] * 60;

        }

        if (isset($matches[14])) {

            // Seconds

            $expire+= $matches[14];

        }

        return time() + $expire;

    }

}

?>
