<?php

include_once 'secret/contactEmail.php';
include_once 'secret/dbConn.php';
include_once 'secret/reCAPTCHA.php';

include_once 'Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;
$isMobile = $detect->isMobile() && !$detect->isTablet();

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Rismosch</title>
	
	<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
	<link rel="icon" type="image/png" href="favicon.png" sizes="32x32">
	<link rel="shortcut icon" href="favicon.ico">
	<meta name="msapplication-TileImage" content="mstile-144x144.png">
	<meta name="msapplication-TileColor" content="#00aba9">
	
	<link rel="stylesheet" href="css/colors.css">
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/<?php
		if( $isMobile )
			echo "mobile";
		else
			echo "desktop";
	?>.css">
	
	<script src="scripts/jquery-3.5.1.min.js"></script>
	<script src="scripts/banner.js"></script>
	<script src="scripts/continuousSession.js"></script>
	<script src="scripts/cookie.js"></script>
	<script src="scripts/util.js"></script>
</head>
<body>
	<div class="background">
		<a href="https://www.rismosch.com/">
			<img
				id="banner"
				class="banner pixel_image"
				src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
				onmouseover="playHoverAnimation()"
			>
		</a>
		
		<div class="selector" id="selector">
			<ul class="selector_tabs" id="selector_tabs">
				<li class="selector_tab" id="selector_tab">
					<a href="https://www.rismosch.com/">
						<div><b>Home</b></div>
					</a>
				</li>
				<li class="selector_tab" id="selector_tab">
					<a href="https://www.rismosch.com/blog">
						<div><b>Blog</b></div>
					</a>
				</li>
				<li class="selector_tab" id="selector_tab">
					<a href="https://www.rismosch.com/projects">
						<div><b>Projects</b></div>
					</a>
				</li>
				<li class="selector_tab" id="selector_tab">
					<a href="https://www.rismosch.com/about">
						<div><b>About</b></div>
					</a>
				</li>
			</ul>
		</div>
		
		<div class="content copyright_text" id="content copyright_text">
			<h1>Privacy Policy for rismosch.com</h1>

			<p>Last updated: 07 October 2020</p>

			<p>At rismosch.com, accessible from https://www.rismosch.com/, one of my main priorities is the privacy of my visitors. This Privacy Policy document contains types of information that is collected and recorded by rismosch.com and how I use it.</p>

			<p>If you have additional questions or require more information about my Privacy Policy, do not hesitate to contact me.</p>

			<p>This Privacy Policy applies only to my online activities and is valid for visitors to my website with regards to the information that they shared and/or collect on rismosch.com. This policy is not applicable to any information collected offline or via channels other than this website.</p>

			<h2>Consent</h2>

			<p>By using my website, you hereby consent to my Privacy Policy and agree to its terms.</p>

			<h2>Information I collect</h2>

			<p>The personal information that you are asked to provide, and the reasons why you are asked to provide it, will be made clear to you at the point I ask you to provide your personal information.</p>
			<p>If you contact me directly, I may receive additional information about you such as your name, email address, phone number, the contents of the message and/or attachments you may send me, and any other information you may choose to provide.</p>
			<p>When you register for an Account, I may ask for your contact information, including items such as name, company name, address, email address, and telephone number.</p>

			<h2>How I use your information</h2>

			<p>I use the information I collect in various ways, including to:</p>

			<ul>
			<li>Provide, operate, and maintain my website</li>
			<li>Improve, personalize, and expand my website</li>
			<li>Understand and analyze how you use my website</li>
			<li>Develop new products, services, features, and functionality</li>
			<li>Communicate with you, either directly or through one of our partners, including for customer service, to provide you with updates and other information relating to the webste, and for marketing and promotional purposes</li>
			<li>Send you emails</li>
			<li>Find and prevent fraud</li>
			</ul>

			<h2>Log Files</h2>

			<p>rismosch.com follows a standard procedure of using log files. These files log visitors when they visit websites. All hosting companies do this and a part of hosting services' analytics. The information collected by log files include internet protocol (IP) addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring/exit pages, and possibly the number of clicks. These are not linked to any information that is personally identifiable. The purpose of the information is for analyzing trends, administering the site, tracking users' movement on the website, and gathering demographic information. My Privacy Policy was created with the help of the <a href="https://www.privacypolicygenerator.info">Privacy Policy Generator</a> and the <a href="https://www.privacypolicyonline.com/privacy-policy-generator/">Online Privacy Policy Generator</a>.</p>

			<h2>Advertising Partners Privacy Policies</h2>

			<P>You may consult this list to find the Privacy Policy for each of the advertising partners of rismosch.com.</p>

			<p>Third-party ad servers or ad networks uses technologies like cookies, JavaScript, or Web Beacons that are used in their respective advertisements and links that appear on Rismosch, which are sent directly to users' browser. They automatically receive your IP address when this occurs. These technologies are used to measure the effectiveness of their advertising campaigns and/or to personalize the advertising content that you see on websites that you visit.</p>

			<p>Note that Rismosch has no access to or control over these cookies that are used by third-party advertisers.</p>

			<h2>Third Party Privacy Policies</h2>

			<p>rismosch.com's Privacy Policy does not apply to other advertisers or websites. Thus, I am advising you to consult the respective Privacy Policies of these third-party ad servers for more detailed information. It may include their practices and instructions about how to opt-out of certain options. </p>

			<p>You can choose to disable cookies through your individual browser options. To know more detailed information about cookie management with specific web browsers, it can be found at the browsers' respective websites.</p>

			<h2>CCPA Privacy Rights (Do Not Sell My Personal Information)</h2>

			<p>Under the CCPA, among other rights, California consumers have the right to:</p>
			<p>Request that a business that collects a consumer's personal data disclose the categories and specific pieces of personal data that a business has collected about consumers.</p>
			<p>Request that a business delete any personal data about the consumer that a business has collected.</p>
			<p>Request that a business that sells a consumer's personal data, not sell the consumer's personal data.</p>
			<p>If you make a request, I have one month to respond to you. If you would like to exercise any of these rights, please contact me.</p>

			<h2>GDPR Data Protection Rights</h2>

			<p>I would like to make sure you are fully aware of all of your data protection rights. Every user is entitled to the following:</p>
			<p>The right to access – You have the right to request copies of your personal data. I may charge you a small fee for this service.</p>
			<p>The right to rectification – You have the right to request that I correct any information you believe is inaccurate. You also have the right to request that we complete the information you believe is incomplete.</p>
			<p>The right to erasure – You have the right to request that I erase your personal data, under certain conditions.</p>
			<p>The right to restrict processing – You have the right to request that I restrict the processing of your personal data, under certain conditions.</p>
			<p>The right to object to processing – You have the right to object to our processing of your personal data, under certain conditions.</p>
			<p>The right to data portability – You have the right to request that I transfer the data that I have collected to another organization, or directly to you, under certain conditions.</p>
			<p>If you make a request, I have one month to respond to you. If you would like to exercise any of these rights, please contact me.</p>

			<h2>Children's Information</h2>

			<p>Another part of my priority is adding protection for children while using the internet. I encourage parents and guardians to observe, participate in, and/or monitor and guide their online activity.</p>

			<p>rismosch.com does not knowingly collect any Personal Identifiable Information from children under the age of 13. If you think that your child provided this kind of information on my website, I strongly encourage you to contact me immediately and I will do my best efforts to promptly remove such information from my records.</p>
		</div>
		
		<div class="foot" id="foot">
			
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
				<a title="Twitter" href="https://twitter.com/Rismosch">
					<img class="social_icon" src="assets/icon_social/twitter.png">
				</a>
			</div>
			
			<div class="foot_links">
				<p><a href="https://www.rismosch.com/privacy">Privacy Policy</a> &nbsp; | &nbsp; <a href="https://www.rismosch.com/licenses">Licenses</a> &nbsp; | &nbsp; <a href="https://www.rismosch.com/contact">Contact</a></p>
			</div>
			
			<div class="foot_links">
				<p>Copyright &#169; 2020 Simon Sutoris</p>
			</div>
			
		</div>
		
		<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top">Top</button>
	</div>
</body>
</html>