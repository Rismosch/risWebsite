<?php

function echo_head()
{
	include_once '3rd_party_libraries/Mobile-Detect-2.8.34/Mobile_Detect.php';
	$detect = new Mobile_Detect;
	$isMobile = $detect->isMobile() && !$detect->isTablet();
	
	echo '
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width,initial-scale=1" />
		
		<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
		<link rel="icon" type="image/png" href="favicon.png" sizes="32x32">
		<link rel="shortcut icon" href="favicon.ico">
		<meta name="msapplication-TileImage" content="mstile-144x144.png">
		<meta name="msapplication-TileColor" content="#00aba9">
		
		'; if(!$isMobile) echo '<link rel="stylesheet" href="css/desktop.css">'; echo '
		<link rel="stylesheet" href="css/main.css">
		
		<script src="3rd_party_libraries/disqusloader.js"></script>
		<script src="javascript/util.js"></script>
	';
}

function echo_banner()
{
	echo '
	<a href="https://www.rismosch.com/">
		<img
			id="banner"
			class="banner pixel_image"
			src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
			onmouseover="playHoverAnimation()"
		>
	</a>
	';
}

function echo_selector($active_tab)
{
	$tab_count = 4;

	$tab_selection = array_fill(0,$tab_count,"");
	$tab_selection_dropdown = array_fill(0,$tab_count,"");

	if($active_tab >= 0 && $active_tab < $tab_count)
	{
		$tab_selection[$active_tab] = "active_tab";
		$tab_selection_dropdown[$active_tab] = "class=\"dropdown_selected\" ";
	}
	else
	{
		$active_tab = -1;
	}

	echo '
	<div class="selector" id="selector">

		<ul class="selector_tabs desktop" id="selector_tabs">
			<li class="selector_tab '. $tab_selection[0] .'" id="selector_tab">
				<a href="https://www.rismosch.com/">
					<div><b>Home</b></div>
				</a>
			</li>
			<li class="selector_tab '. $tab_selection[1] .'" id="selector_tab">
				<a href="https://www.rismosch.com/blog">
					<div><b>Blog</b></div>
				</a>
			</li>
			<li class="selector_tab '. $tab_selection[2] .'" id="selector_tab">
				<a href="https://www.rismosch.com/projects">
					<div><b>Projects</b></div>
				</a>
			</li>
			<li class="selector_tab '. $tab_selection[3] .'" id="selector_tab">
				<a href="https://www.rismosch.com/about">
					<div><b>About</b></div>
				</a>
			</li>
		</ul>
		
		<div class="mobile">
			<div onclick="showDropdown(\'dropdownSelector\')" class="dropdownButton dropdownSelector selector_menu pixel_image" id="dropdownButton"></div>
			<div class="dropdownContent dropdownSelector dropdown">
				<a '.$tab_selection_dropdown[0].'href="https://www.rismosch.com/">Home</a>
				<a '.$tab_selection_dropdown[1].'href="https://www.rismosch.com/blog">Blog</a>
				<a '.$tab_selection_dropdown[2].'href="https://www.rismosch.com/projects">Projects</a>
				<a '.$tab_selection_dropdown[3].'href="https://www.rismosch.com/about">About</a>
			</div>
		</div>
		
	</div>
	';
}

function echo_foot($uses_captcha)
{
	echo '
	<div class="foot" id="foot">
		
		<a title="Newsletter" href="https://www.rismosch.com/newsletter">
			<img class="newsletter_icon pixel_image" src="assets/newsletter.gif">
		</a>
		
		<div class="socials" id="socials">
			<a title="YouTube" href="https://www.youtube.com/channel/UCrWSfmTaXTN_LzEsVRKNJTw">
				<img class="social_icon" src="assets/icon_social/youtube.png">
			</a>
			<a title="Bandcamp" href="https://rismosch.bandcamp.com">
				<img class="social_icon" src="assets/icon_social/bandcamp.png">
			</a>
			<a title="itch.io" href="https://rismosch.itch.io/">
				<img class="social_icon" src="assets/icon_social/itch_io.png">
			</a>
			<a title="GitHub" href="https://github.com/Rismosch">
				<img class="social_icon" src="assets/icon_social/github.png">
			</a>
		</div>
		
		<div class="foot_links">
			<p>
				<a class="foot_link" href="https://www.rismosch.com/privacy">Privacy Policy</a>
				<a class="foot_link_divider">&nbsp; | &nbsp;</a>
				<a class="foot_link" href="https://www.rismosch.com/references">References</a>
				<a class="foot_link_divider">&nbsp; | &nbsp;</a>
				<a class="foot_link" href="https://www.rismosch.com/contact">Contact Me</a>
			</p>
		</div>
		
		<div class="foot_copyright">
			<p>Copyright &#169; 2021 <a class="simon_sutoris">Simon Sutoris</a></p>
		</div>
		
	</div>
	';

	if($uses_captcha == true)
		echo '<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top scroll_captcha_offset">Top</button>';
	else
		echo '<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top">Top</button>';
}

?>