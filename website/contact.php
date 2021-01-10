<?php

include_once 'secret/contactEmail.php';
include_once 'secret/dbConn.php';
include_once 'secret/reCAPTCHA.php';

include_once 'Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;
$isMobile = $detect->isMobile() && !$detect->isTablet();

$errors = [];

if(!empty($_POST)) {
	$name = $_POST['name'];
	$email = $_POST['email'];
	$message = $_POST['message'];
	
	$filteredName = filter_var($name,FILTER_SANITIZE_ENCODED, FILTER_FLAG_ENCODE_LOW | FILTER_FLAG_STRIP_HIGH);
	$filteredEmail = filter_var($email,FILTER_SANITIZE_EMAIL);
	$filteredMessage = filter_var($message,FILTER_SANITIZE_ENCODED, FILTER_FLAG_ENCODE_LOW | FILTER_FLAG_STRIP_HIGH);
	
	
	if (isset($_POST['g-recaptcha-response']))
	{
		$recaptchaResponse = $_POST['g-recaptcha-response'];
		$recaptchaUrl = "https://www.google.com/recaptcha/api/siteverify?secret={$reCAPTCHA_secret_key}&response={$recaptchaResponse}";
		$verify = json_decode(file_get_contents($recaptchaUrl));
		
		if (!$verify->success) {
			$errors[] = 'Recaptcha failed';
		}
	}
	else
	{
		$errors[] = 'Recaptcha failed';
	}
	
	if(empty($name)) {
		$errorName = 'Name is empty';
		$errors[] = $errorName;
	}
	else if (strlen($name) > 99){
		$errorName = "Name is too long (max 99 characters)";
		$errors[] = $errorName;
	}
	
	if (empty($email)) {
		$errorEmail = 'Email is empty';
		$errors[] = $errorEmail;
	}
	else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errorEmail = 'Email is invalid';
		$errors[] = $errorEmail;
	}
	else if (strlen($email) > 99){
		$errorEmail = "Email is too long";
		$errors[] = $errorEmail;
	}
	
	if (empty($message)) {
		$errorMessage = 'Message is empty';
		$errors[] = $errorMessage;
	}
	else if (strlen($message) > 999){
		$errorMessage = "Message is too long";
		$errors[] = $errorMessage;
	}
	
	if (empty($errors)) {
		$emailSubject = 'Contact Form';
		$headers = ['From' => $filteredEmail, 'Reply-To' => $filteredEmail, 'Content-type' => 'text/plain; charset=utf-8'];
		
		$body = "Name: {$filteredName}\nEmail: {$filteredEmail}\nMessage:\n\n{$filteredMessage}";
		
		if (mail($contactEmail, $emailSubject, $body, $headers)) {
			header('Location: contact_successful');
		}
		else {
			$errorContact = 'Oops, something went wrong. Please try again later';
		}
	}
}

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
	<script src="scripts/validate.js"></script>
	
	<script src="https://www.google.com/recaptcha/api.js"></script>
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
				<li class="selector_tab" id="selector_tab active_tab">
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
		
		<div class="content" id="content">
			
			<h1>Contact me</h1>
			
			<form action="contact" method="POST" id="contact-form">
				<div>
					<p>Name <span class="contact_error" id="display_error_name"><?php if(isset($errorName)) echo $errorName; ?></span></p>
					<input name="name" class="contact_input" id="contact_namefield" type="text" value = "<?php if(isset($name)) echo $name; ?>">
					
					<p>Email <span class="contact_error" id="display_error_email"><?php if(isset($errorEmail)) echo $errorEmail; ?></span></p>
					<input name="email" class="contact_input" id="contact_emailfield" type="text" value = "<?php if(isset($email)) echo $email; ?>">
					
					<p>Message <span class="contact_error" id="display_error_message"><?php if(isset($errorMessage)) echo $errorMessage; ?></span></p>
					<textarea name="message" class="contact_input" id="contact_textarea" rows="10" cols="35"><?php if(isset($message)) echo $message; ?></textarea>
					<p id="contact_textarea_count">0/999</p>
					
					<p><span class="contact_error"><?php if(isset($errorContact)) echo $errorContact; ?></span></p>
					<p>
						<button
							class="g-recaptcha"
							type="submit"
							data-sitekey="<?php echo $reCAPTCHA_web_key;?>"
							data-callback='onRecaptchaSuccess'
						>
						Send
						</button>
					</p>
				</div>
			</form>
			
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
		
		<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top scroll_captcha_offset">Top</button>
	</div>
	
	<script>
		const constraints = {
			name: {
				presence: { allowEmpty: false }
			},
			email: {
				presence: { allowEmpty: false },
				email: true
			},
			message: {
				presence: { allowEmpty: false }
			}
		}
		
		const form = document.getElementById("contact-form");
		const display_error_name = document.getElementById("display_error_name");
		const display_error_email = document.getElementById("display_error_email");
		const display_error_message = document.getElementById("display_error_message");
		
		form.addEventListener('submit', function(event) {
			const formValues = {
				name: form.elements.name.value,
				email: form.elements.email.value,
				message: form.elements.message.value
			}
			
			const errors = validate(formValues, constraints);
			
			if(errors) {
				event.preventDefault();
				
				if(errors.name)
					display_error_name.innerHTML = errors.name[0];
				else
					display_error_name.innerHTML = "";
				
				if(errors.email)
					display_error_email.innerHTML = errors.email[0];
				else
					display_error_email.innerHTML = "";
				
				if(errors.message)
					display_error_message.innerHTML = errors.message[0];
				else
					display_error_message.innerHTML = "";
			}
		},false);
		
		function onRecaptchaSuccess () {
			document.getElementById('contact-form').submit();
		}
		
		messageCount($("#contact_textarea").val().length);
		
		$("#contact_textarea").keyup(function(){
			messageCount($(this).val().length);
		});
		
		function messageCount(characterCount){
			$("#contact_textarea_count").text("Characters: " + characterCount + "/999");
			
			if(characterCount > 999)
				$("#contact_textarea_count").css("color","var(--pico-8-red)");
			else
				$("#contact_textarea_count").css("color","var(--pico-8-black)");
		}
	</script>
</body>
</html>
