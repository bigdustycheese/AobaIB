<?php

namespace Mitsuba;

class Frontpage
{

	private $conn;

	private $config;

	private $mitsuba;

	function __construct($connection, &$mitsuba)
	{

		$this->conn = $connection;

		$this->mitsuba = $mitsuba;

		$this->config = $this->mitsuba->config;

	}



	function generateFrontpage($action = "none")
	{

		$file = "<!doctype html>\n";
		$file.= "<html lang='en'>
		<head>
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
		  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1.0'/>
		  <meta charset='UTF-8'>";
			if ($this->config['sitename'] == "314chan") {
				$file.="<title>{$this->config["sitename"]} - The Only Imageboard That Cares!</title>
				<meta name='description' content='314chan is an imageboard forum with no registration required established in 2011.' />
				<meta name='twitter:card' content='summary' />
				<meta name='twitter:site' content='@314chan' />
				<meta property='og:url' content='https://www.314chan.co' />
				<meta property='og:title' content='314chan' />
				<meta property='og:description' content='314chan is an imageboard forum with no registration required established in 2011.' />
				<meta property='og:image' content='https://www.314chan.co/img/justpi.png' />
				<meta name='theme-color' content='#0f4a2f'>
				<meta name='monetization' content='\$ilp.uphold.com/g2d8mfwMwgaz' /> ";
			}else {
				$file.="<title>{$this->config["sitename"]}</title>";
			}
			$file.="<meta name='mobile-web-app-capable' content='yes'>
							<link href='css/MIcons.css' rel='stylesheet'>
							<link href='css/materialize.css' type='text/css' rel='stylesheet' media='screen,projection'/>
							<link href='css/style.css' type='text/css' rel='stylesheet' media='screen,projection'/>
							</head>";
			//start body
			$file.="<body>
			<script>
			function imgError(image) {
			  image.onerror = '';
			  image.src = '/img/deleted.gif';
			  return true;
			}

			//props to anon for the fix.
			if (localStorage.getItem('o_night_css') == 1) {

			  //this gets the current hour in 24 hour format.
			  var CurrHour = new Date().getHours();

			  //if the Current Hour is greater than 9PM, or less than 5AM change CSS to the 'night.css'.
			  if (CurrHour >= '18'|| CurrHour <= '5'){
			    var path = '/css/front-night.css';

			    //create it inline temporarily while page is loading from localstorage, prevents bright flash
			    var css = localStorage.getItem('front_night_theme_css');
			    if (css != null) {
			      var inlineTheme = document.createElement('style');
			      document.head.appendChild(inlineTheme);
			      inlineTheme.innerHTML = css;
			      document.styleSheets[0].disable = true;
			    }

			    //next insert the normal link element
			    var linkTheme = document.createElement('link');
			    linkTheme.rel = 'stylesheet';
			    linkTheme.onload = function() {
			    css = '';
			    for(var i = 0; i < linkTheme.sheet.rules.length; i++) {

			      //add all the rules to the css to save in localstorage for next load
			      css += linkTheme.sheet.rules[i].cssText;
			    }
			    localStorage.setItem('front_night_theme_css', css);

			    //remove the inline style afterwards, not needed anymore
			    if (inlineTheme) {
			      inlineTheme.remove();
			    }
			  }

			  linkTheme.href = path;
			  document.head.appendChild(linkTheme);
			  document.styleSheets[0].disable = true;
			  }
			}
			</script>
			<!--Navbar container-->
			<div class='navbar-fixed'>";
			if ($this->config['sitename'] == "314chan") {
				$file.="<nav class='greencolor'>
									<div class='nav-wrapper container'>
										<a id='logo-container' href='/' class='brand-logo'>
											<picture>
												<source srcset='img/logo.webp' type='image/webp'>
												<source srcset='img/logo.png' type='image/jpeg'>
												<img src='img/logo.png' alt='314chan'/>
											</picture>
										</a>
										<ul class='right hide-on-med-and-down'>
											<li><a href='rules.html'>Rules</a></li>
											<li><a href='faq.html'>FAQ</a></li>
											<li><a href='news.html'>News</a></li>
											<li><a href='https://matrix.to/#/+314chan:matrix.org'>Matrix Chat</a></li>
											<li><a href='/boards.php'>Boards List</a></li>
										</ul>
									</div>
								</nav>";
			}else {
				$file.="<nav class='marooncolor'>
									<div class='nav-wrapper container'>
										<a id='logo-container' href='/' class='brand-logo'>{$this->config["sitename"]}</a>
										<ul class='right hide-on-med-and-down'>
										<!-- add important links as li here, in /inc/homepage/haruko.php-->
										</ul>
									</div>
								</nav>";
			}
			$file.="</div>
			<!-- Board Container -->
			<div class='container'>
			  <div class='row'>
			    <div class='col s12'></div></div>
			  <div class='card small'>
			    <div class='columns board-banner'>
			      <h4 class='left-align column is-four-fifths'>Boards</h4>
			    </div>
			    <div class='columns board-list'>";
				$cats = $this->conn->query("SELECT * FROM links WHERE parent=-1 ORDER BY short ASC;");
				while ($row = $cats->fetch_assoc()){
					/*ugly for me, but pretty in the viewsource ;)*/
					$file .= "\n\t\t\t\t\t\t<div class='column board-section'>";
					$file .= "\n\t\t\t\t\t\t\t<h6 style='text-decoration: underline; display: inline;'>".$row['title']."</h6>";
					$file .= "\n\t\t\t\t\t\t\t<ul>";

			$children = $this->conn->query("SELECT * FROM links WHERE parent=".$row['id']." ORDER BY short ASC");

			while ($child = $children->fetch_assoc()){
				if (!empty($child['url_index'])) {
					$file .= "\t\t\t\t\t\t\t\t<li class='boardlistitem'>\n\t\t\t\t\t\t\t\t\t<a class='boardlink' href='". $child['url_index'] ."' title='". $child['title'] ."'>/". $child['url_index'] ."/ - ". $child['title'] ."</a>\n\t\t\t\t\t\t\t\t</li>";
				} else {
					$file .= "\n\t\t\t\t\t\t\t\t<li class='boardlistitem'>\n\t\t\t\t\t\t\t\t\t<a class='boardlink' href='".$child['url']."' title='".$child['title']."'>".$child['title']."</a>\n\t\t\t\t\t\t\t\t</li>";

				}

			}

			$file .= "\n\t\t\t\t\t\t\t</div>\n";

		}
		$file .="\t\t\t\t\t\t</div>
					</div>
				</div>
			</div>
		</div>
		<!--Recents/Statistics Container-->
        <div class='container'>
        	<div class='row'>
        	<!--Recent Posts-->
        	<div class='col s12 m3'>
        	<div class='row'>
        	<div class='card-panel'><h4>Recent Posts</h4></div>";
        	$post = $this->conn->query("SELECT * FROM posts WHERE `posts`.`deleted` = 0 AND `board`<>'test' AND `board`<>'s' AND `board`<>'b' ORDER BY date DESC LIMIT 4");
        	foreach($post as $posts){
	        	$file .="<div class='card'>";
	        	if(!empty($posts['mimetype'])){
		        	$file .="<div class='card-image'><img src='/$posts[board]/src/$posts[filename]'/></div>";
	        	}
        		$file .="<div class='card-stacked'>";
				$file .="<div class='card-content'>";
				if(!empty($posts['subject'])){
				$file .="<span class='card-title'>If a thread has a subject, It'll go here.</span>";
				}
				$file .="<div class='card-content'><p>$posts[comment]</p></div>";

				$file .="</div>
						<div class='card-action'>";
						if($posts['resto']!= 0) {
						$file .='<a href="/'.$posts['board'].'/res/'.$posts['resto'].'.html#p'.$posts['id'].'" class="btn orange">View</a>&nbsp;';
						}else{
						$file .='<a href="/'.$posts['board'].'/res/'.$posts['id'].'.html" class="btn orange">View</a>&nbsp;';
						}
						if($posts['resto']!= 0) {
						$file .='<a href="/'.$posts['board'].'/res/'.$posts['resto'].'.html#q'.$posts['id'].'" class="btn orange">Reply</a>';
						}else{
						$file .='<a href="/'.$posts['board'].'/res/'.$posts['id'].'.html#q'.$posts['id'].'" class="btn orange">Reply</a>';
						}

				$file.="</div>

					</div>
				</div>";

        	}
        			$file .="
			</div>

        </div>";
			$file .='
               <!--Statistics-->
               <div class="col s12 m9">

                 <div class="card medium">

                 <ul class="collection with-header">

                <li class="collection-header"><h4>Statistics</h4></li>';

		$result = $this->conn->query("SELECT * FROM posts");

		$num_rows = $result->num_rows;

		$result = $this->conn->query("SELECT DISTINCT ip FROM posts");

		$num_users = $result->num_rows;

		$result = $this->conn->query("SELECT sum(orig_filesize) FROM posts");

		$num_bytes = $result->fetch_array()[0];

		$result = $this->conn->query("SELECT * FROM `bans`");

		$num_bans = $result->num_rows;

		{

			$file .= '<li class="collection-item"><strong>Total posts:</strong> '.$num_rows.'</li>

					  <li class="collection-item"><strong>Unique posters:</strong> '.$num_users.'</li>

					  <li class="collection-item"><strong>Active content:</strong> '.$this->mitsuba->common->human_filesize($num_bytes).'</li>

					  <li class="collection-item"><strong>Active Bans:</strong> '.$num_bans.'</li>

			';

		}

		$file .= "</ul></div></div></div></div>
		<!--Footer Content-->";

		if ($this->config['sitename'] == "314chan") {
			$file.="<footer class='page-footer greencolor' style='padding-top: 20px;'>
			<div class='container'>
			<div class='row'>
			<div class='col l6 s12'>
				<h5 class='white-text'>The Constitutional Monarchy.</h5>
				<p class='grey-text text-lighten-4'>314chan would like to be as open as possible. We employ a system in which the boards are controlled by the users, and for the users. I will explain more in depth <a href='monarchy.html'>here</a></p>
			</div>
			<div class='col l3 s12'>
				<h5 class='white-text'>Our Friends</h5>
				<ul>
					<!--<li><a class='white-text' href='#!'>Friend links</a></li>-->
				</ul>
			</div>
			<div class='col l3 s12'>
				<h5 class='white-text'>Why 314chan?</h5>
				<ul>
					<li class='white-text'><strong>Permanent U.S. Ownership.</strong>&nbsp;<em>We will never sell out to any company.</em></li>
					<li class='white-text'><strong>Head staff that cares.</strong>&nbsp;<em>Our staff have never intentionally ignored a user in its history.</em></li>
					<li class='white-text'><strong>Wide variety of subjects.</strong>&nbsp;<em>We have a unique variety of boards in which anyone can find their favorite subject!</em></li>
					<li class='white-text'><strong>Captcha as a last resort.</strong>&nbsp;<em>We will never enable Captchas (unless there are ongoing spam attacks.) If we do, it's our <a href='/captcha.html'>self hosted</a> captcha.</em></li>
				</ul>
				</div>
				</div>
				</div>
				<div class='footer-copyright'>
				<div class='container'>
				Site &copy; ".date('Y')."&nbsp{$this->config['sitename']}
				<div class='right'><a href='https://www.law.cornell.edu/uscode/text/47/230'>All posts are the responsibility of the original poster.</a><div>
				</div>
				</div>
				</footer>";
		}else {
			$file.="<footer class='page-footer marooncolor'>
				<div class='footer-copyright'>
				<div class='container'>
				Site &copy; ".date('Y')."&nbsp{$this->config['sitename']}
				<div class='right'><a href='https://www.law.cornell.edu/uscode/text/47/230'>All posts are the responsibility of the original poster.</a><div>
				</div>
				</div>
			</footer>";
		}

		$handle = fopen("./" . $this->config['frontpage_url'], "w");

		fwrite($handle, $file);

		fclose($handle);

	}

	function generateNews()
	{

		$file = "<!doctype html>";

		$file.= '

        <html lang="en">

        <head>

            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>

						<meta charset="UTF-8">

						<title>' . $this->config['sitename'] . ' - News</title>

            <link href="css/MIcons.css" rel="stylesheet">

            <link href="css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>

            <link href="css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>

					</head>



        ';

		$file.= '

        					<body>

          <div class="navbar-fixed">

              <nav class="greencolor" role="navigation">

            <div class="nav-wrapper container"><a id="logo-container" href="/" class="brand-logo">' . $this->config['sitename'] . '</a>

            '.

			//put an if statement on weather the sitename equals "314chan" here"

		'

              <ul class="right hide-on-med-and-down">

                <li><a href="rules.html">Rules</a></li>

                <li><a href="faq.html">FAQ</a></li>

                <li><a href="news.html">News</a></li>

                <li><a href="https://matrix.to/#/+314chan:matrix.org">Matrix Chat</a></li>

              </ul>
              '.

			//end if statement

		'
            </div>

          </nav>

        </div>



        ';

		$file.= '

        <div class="section no-pad-bot" id="index-banner">

            <div class="container">

              <br><br>

        ';

		$result = $this->conn->query("SELECT * FROM news ORDER BY date DESC;");

		while ($row = $result->fetch_assoc()) {

			$file.= '<a name="'.$row['id'].'"></a><div class="card-panel">';

			$file.= '<h5><strong>' . $row['title'] . '</strong> by ' . $row['who'] . ' - ' . date("d/m/Y h:i", $row['date']) . '</h5><hr />';

			$file.= $row['text'];

			$file.= '</div>';

		}

		$file.= '</div>

			</div>

			</div>

			</div>

			</body>

			</html>';

		$handle = fopen("./" . $this->config['news_url'], "w");

		fwrite($handle, $file);

		fclose($handle);

	}

}

?>
