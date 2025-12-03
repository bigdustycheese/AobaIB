<!doctype html>
<html lang="en">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
            <meta charset="UTF-8">
            <title>AobaIB</title>
            <link href="css/MIcons.css" rel="stylesheet">
            <link href="css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
            <link href="css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
        </head>

        <body>
            <div class="navbar-fixed">
                <nav class="marooncolor" role="navigation">
                    <div class="nav-wrapper container"><a id="logo-container" href="/" class="brand-logo">AobaIB</a>
                    <ul class="right hide-on-med-and-down">
                        <li><a href="rules.html">Rules</a></li>
                        <li><a href="faq.html">FAQ</a></li>
                        <li><a href="news.html">News</a></li>
                        <li><a href="#">IRC</a></li>
                    </ul>

                    <ul id="nav-mobile" class="side-nav">
                        <li><a href="rules.html">Rules</a></li>
                        <li><a href="faq.html">FAQ</a></li>
                        <li><a href="news.html">News</a></li>
                        <li><a href="#">IRC</a></li>
                    </ul>
                    <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="material-icons">menu</i></a>
                    </div>
                </nav>
            </div>

          <div class="section no-pad-bot" id="index-banner">
            <div class="container">
              <br><br>
              <div class="card-panel">
                  This is a comprehensive list of all bans on 314chan. IPs are masked.<br />
                                <?php
                                /*
                                    vote.php has yet to be written.
                                    for now, it's just collecting info and stuff.
                                */
                                    require "config.php";
                                    require "inc/mitsuba.php";
                                    $conn = new mysqli($db_host, $db_username, $db_password, $db_database);
                                    $haruko = new Mitsuba($conn);
                                    $sql = "SELECT * FROM `bans` LEFT JOIN users ON bans.mod_id=users.id ORDER BY created;";
                                    //$sql = "SELECT DISTINCT strip from `posts`";
                                    //$trip = $conn->query("SELECT * FROM posts WHERE trip IS NOT NULL");

                                if(!$result = $conn->query($sql)) {

                                       echo('<br /><strong>There was an error running the query [' . $conn->error . ']</strong>');

                                }else{
                                                        ?>
                                         <table>
                                <colgroup>
                                 <col style="width:15%">
                                 <col style="width:15%">
                                 <col style="width:15%">
                                </colgroup>
                                          <thead>
                                           <tr>
                                            <th data-field="ip">IP Address</th>
                                            <th data-field="reason">Reason</th>
                                            <th data-field="created">Created</th>
                                  <th data-field="expires">Expires</th>
                                  <th data-field="boards">Boards</th>
                                  <th data-field="user">Username</th>
                                  <th data-field="seen">Seen</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                <?php
                                global $securetrip_salt;
                                $logs = 1;
                                while($row = $result->fetch_assoc()){
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars(crypt($row['ip'], $securetrip_salt), ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars(mb_strimwidth($row['reason'], 0, 15, "..."), ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars(date("d/m/Y @ H:i", $row['created']), ENT_QUOTES, 'UTF-8') .  "</td>";
                                    if ($row['expires'] != 0) {
                                        echo "<td>".date("d/m/Y @ H:i", $row['expires'])."</td>";
                                    } else {
                                        echo "<td><b>never</b></td>";
                                    }
                                    if ($row['boards']=="%") {
                                        echo "<td>All boards</td>";
                                    } else {
                                        $banBoards = explode(',', $row['boards']);
                                        if (0.6 * sizeof($_boards) < sizeof($banBoards)) {
                                            echo "<td>All boards <b>excluding</b>: ".implode(', ', array_diff($_boards, $banBoards))."</td>";
                                        } else {
                                            echo "<td>".implode(', ', $banBoards)."</td>";
                                        }
                                    }
                                                echo "<td>" . htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') . "</td>";
                                    if ($row['seen']==1) {
                                        echo "<td style='background-color:#228B22'></td>";
                                    } else {
                                        echo "<td style='background-color:#FF0000'></td>";
                                    }
                                                echo "</tr>";
                                }
                                }
            ?>
                                        </tbody>
                        </table>
            </div>
          </div>
          </div>
             <div class="section">

            </div>
          </div>
          <footer class="page-footer marooncolor">

            <div class="container">

              <div class="row">

                <div class="col l6 s12">

                  <h5 class="white-text">The Constitutional Monarchy.</h5>

                  <p class="grey-text text-lighten-4">AobaIB would like to be as open as possible. We employ a system in which the boards are controlled by the users, and for the users. I will explain more in depth <a href="monarchy.html">here</a></p>

                  </div>

                <div class="col l3 s12">

                  <h5 class="white-text">Our Friends</h5>

                  <ul>

                    <li><a class="white-text" href="http://keychan.cf/">Keychan</a></li>

                    <!--<li><a class="white-text" href="#!">Link 3</a></li>

                    <li><a class="white-text" href="#!">Link 4</a></li>-->

                  </ul>

                </div>

                <div class="col l3 s12">

                  <h5 class="white-text">Why AobaIB?</h5>

                  <ul>

                    <li class="white-text"><strong>Permanent U.S. Ownership.</strong>&nbsp;<em>We will never sell out to any company.</em></li>

                    <li class="white-text"><strong>Head staff that cares.</strong>&nbsp;<em>Our staff have never ignored a user in its 7 year run.</em></li>

                    <li class="white-text"><strong>No Ads.</strong>&nbsp;<em>Parley will never host ads on the server&nbsp;(unless need for money is dire.)</em></li>

                    <li class="white-text"><strong>Captcha as a last resort.</strong>&nbsp;<em>We will never enable Captchas (unless there are ongoing spam attacks.)</em> </li>

                  </ul>

                </div>

              </div>

            </div>

            <div class="footer-copyright">

              <div class="container">

              Site © <?php echo date("Y"); ?>&nbsp;AobaIB

              <div class="right"><a href="https://www.law.cornell.edu/uscode/text/47/230">All posts are the responsibility of the original poster.</a><div>

              </div>

            </div>

          </div></div></footer>
          <!--  Scripts-->
          <script src="js/jquery.js"></script>
          <script src="js/materialize.js"></script>
          <script src="js/init.js"></script>
                    </body>
                    </html>
