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
		<link rel="stylesheet" href="css/main_4.css">
		
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
			alt=\'Banner\'
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
			<div class="selectorDropdown">
				<button class="pixel_image" aria-label="Navigation Dropdown"></button>
				<div class="dropdownContent dropdown">
					<a '.$tab_selection_dropdown[0].'href="https://www.rismosch.com/">Home</a>
					<a '.$tab_selection_dropdown[1].'href="https://www.rismosch.com/blog">Blog</a>
					<a '.$tab_selection_dropdown[2].'href="https://www.rismosch.com/projects">Projects</a>
					<a '.$tab_selection_dropdown[3].'href="https://www.rismosch.com/about">About</a>
				</div>
			</div>
		</div>
		
	</div>
	';
}

$img_sources = [];
$img_styles = [];
$img_count = 0;
function late_image($source, $class, $style)
{
	global
		$img_sources,
		$img_styles,
		$img_count;
	
	echo "
	<img
		src='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
		class='{$class}'
		style='display: none;'
		id='img{$img_count}'
		alt=''
	>
	
	<noscript>
	<img
		src='{$source}'
		class='{$class}'
		style='{$style}'
		alt=''
	>
	</noscript>
	";
	
	$img_sources[] = $source;
	$img_styles[] = $style;
	++$img_count;
}

function echo_foot($uses_captcha)
{
	// foot links
	echo '
	<div class="foot" id="foot">
		
		<a title="Newsletter" href="https://www.rismosch.com/newsletter">
			'; late_image("assets/newsletter.gif", "newsletter_icon pixel_image", ""); echo '
		</a>
		
		<div class="socials" id="socials">
			<a title="YouTube" href="https://www.youtube.com/channel/UCrWSfmTaXTN_LzEsVRKNJTw">
				'; late_image("assets/icon_social/youtube.webp", "social_icon", ""); echo '
			</a>
			<a title="Twitter" href="https://twitter.com/rismosch">
				'; late_image("assets/icon_social/twitter.webp", "social_icon", ""); echo '
			</a>
			<a title="Bandcamp" href="https://rismosch.bandcamp.com">
				'; late_image("assets/icon_social/bandcamp.webp", "social_icon", ""); echo '
			</a>
			<!--<a title="itch.io" href="https://rismosch.itch.io/">
				'; /*late_image("assets/icon_social/itch_io.webp", "social_icon", "");*/ echo '
			</a>-->
			<a title="GitHub" href="https://github.com/Rismosch">
				'; late_image("assets/icon_social/github.webp", "social_icon", ""); echo '
			</a>
		</div>
		
		<div class="foot_links">
			<p>
				<a class="foot_link" href="https://www.rismosch.com/privacy">Privacy Policy</a>
				<span class="foot_link_divider">&nbsp; | &nbsp;</span>
				<a class="foot_link" href="https://www.rismosch.com/references">References</a>
				<span class="foot_link_divider">&nbsp; | &nbsp;</span>
				<a class="foot_link" href="https://www.rismosch.com/contact">Contact Me</a>
			</p>
		</div>
		
		<div class="foot_copyright">
			<p>Copyright &#169; 2021 <span class="simon_sutoris">Simon Sutoris</span></p>
		</div>
		
	</div>
	';
	
	// back to top button
	if($uses_captcha == true)
		echo '<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top scroll_captcha_offset">Top</button>';
	else
		echo '<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top">Top</button>';
	
	// late loading images
	global
		$img_sources,
		$img_styles,
		$img_count;
	
	echo "\n<script>\n";
	for($i = 0; $i < $img_count; ++$i)
	{
		echo "
		var image = document.getElementById(\"img{$i}\");
		image.src = \"{$img_sources[$i]}\";
		image.style = \"{$img_styles[$i]}\";
		";
	}
	echo "</script>\n";
}

?>