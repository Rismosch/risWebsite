<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include 'secret/secret.php';
include 'php/util.php';

require '3rd_party_libraries/PHPMailer/Exception.php';
require '3rd_party_libraries/PHPMailer/OAuth.php';
require '3rd_party_libraries/PHPMailer/PHPMailer.php';
require '3rd_party_libraries/PHPMailer/POP3.php';
require '3rd_party_libraries/PHPMailer/SMTP.php';

$errors = [];

$contact_successful = false;

if(!empty($_POST))
{
	$nameUnsafe = $_POST['name'];
	//$nameSanitized = filter_var($nameUnsafe,FILTER_SANITIZE_ENCODED);
	$nameSanitized = $nameUnsafe;
	if(empty($nameSanitized)) {
		$errorName = 'Name is empty';
		$errors[] = $errorName;
	}
	else if (strlen($nameSanitized) > 99){
		$errorName = "Name is too long (max 99 characters)";
		$errors[] = $errorName;
	}
	
	
	$emailUnsafe = $_POST['email'];
	$emailSanitized = filter_var($emailUnsafe,FILTER_SANITIZE_EMAIL);
	if (empty($emailSanitized)) {
		$errorEmail = 'Email is empty';
		$errors[] = $errorEmail;
	}
	else if (!filter_var($emailSanitized, FILTER_VALIDATE_EMAIL)) {
		$errorEmail = 'Email is invalid';
		$errors[] = $errorEmail;
	}
	else if (strlen($emailSanitized) > 320){
		$errorEmail = "Email is too long";
		$errors[] = $errorEmail;
	}
	
	
	$subjectUnsafe = $_POST['subject'];
	//$subjectSanitized = filter_var($subjectUnsafe,FILTER_SANITIZE_ENCODED);
	$subjectSanitized = $subjectUnsafe;
	if (empty($subjectSanitized)) {
		$errorSubject = 'Subject is empty';
		$errors[] = $errorSubject;
	}
	else if (strlen($subjectSanitized) > 99){
		$errorSubject = "Subject is too long";
		$errors[] = $errorSubject;
	}
	
	
	$messageUnsafe = $_POST['message'];
	//$messageSanitized = filter_var($messageUnsafe,FILTER_SANITIZE_ENCODED);
	$messageSanitized = $messageUnsafe;
	if (empty($messageSanitized)) {
		$errorMessage = 'Message is empty';
		$errors[] = $errorMessage;
	}
	else if (strlen($messageSanitized) > 999){
		$errorMessage = "Message is too long";
		$errors[] = $errorMessage;
	}
	
	
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
	
	if (empty($errors)) {
		$mail = new PHPMailer(true);
		
		try {
			// Server settings
			// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
			$mail->isSMTP();
			$mail->Host = 'localhost';
			$mail->SMTPAuth = false;
			$mail->SMTPAutoTLS = false;
			$mail->Port = 25;
			
			// Recipients
			$mail->setFrom($contactEmail, 'Rismosch');
			$mail->AddReplyTo($contactEmail,'Rismosch');
			$mail->addAddress($contactEmail, 'Rismosch');

			// Content
			$mail->CharSet = 'UTF-8';
			$mail->Encoding = 'base64';
			$mail->Subject = 'new message from contact form!';
			$mail->Body    = "Name: \"{$nameSanitized}\"\nEmail: \"{$emailSanitized}\"\nSubject: \"{$subjectSanitized}\"\nMessage:\n\n{$messageSanitized}";

			$mail->send();
			
			// Success
			$contact_successful = true;
			$nameUnsafe = "";
			$emailUnsafe = "";
			$messageUnsafe = "";
			
		} catch (Exception $e) {
			//$errorContact = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			$errorContact = "Something went wrong. Please try again later.";
		}
	}

	if ($contact_successful) {
		header("Location: https://www.rismosch.com/contact_success");
		exit();
	}
}

echo_head();

?>
	<script src="https://www.google.com/recaptcha/api.js"></script>
	
	<title>Contact</title>
	<meta name="description" content="Contact Simon Sutoris">
	<meta name="keywords" content="contact, email">

	<meta name="robots" content="all">

	<meta property="og:title" content="Contact" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/contact" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x10.png" />

	<meta name="author" content="Simon Sutoris">

</head>
<body>
	<div class="background">
		<?php
			echo_banner();
			echo_selector(-1);
		?>
		
		<div class="content" id="content">
			
			<h1>Contact Me</h1>
			
			<div style="display:none" id="javascript_content">
				
				<form action="contact" method="POST" id="contact-form">
					<div>
						
						<p>Name <span class="contact_error" id="display_error_name"><?php if(isset($errorName)) echo $errorName; ?></span></p>
						<input name="name" class="contact_input" id="contact_namefield" type="text" value = "<?php if(isset($nameUnsafe)) echo $nameUnsafe; ?>">
						
						<p>Email <span class="contact_error" id="display_error_email"><?php if(isset($errorEmail)) echo $errorEmail; ?></span></p>
						<input name="email" class="contact_input" id="contact_emailfield" type="text" value = "<?php if(isset($emailUnsafe)) echo $emailUnsafe; ?>">
						
						<p>Subject <span class="contact_error" id="display_error_subject"><?php if(isset($errorSubject)) echo $errorSubject; ?></span></p>
						<input name="subject" class="contact_input" id="contact_subjectfield" type="text" value = "<?php if(isset($subjectUnsafe)) echo $subjectUnsafe; ?>">
						
						<p>Message <span class="contact_error" id="display_error_message"><?php if(isset($errorMessage)) echo $errorMessage; ?></span></p>
						<textarea name="message" class="contact_input" id="contact_textarea" rows="10" cols="35"><?php if(isset($messageUnsafe)) echo $messageUnsafe; ?></textarea>
						<p id="contact_textarea_count">0/999</p>
						
						<p>
							<table>
								<tr>
									<td><img id="privacy_checkbox" class="checkbox" src="assets/icon_8bit/checkbox_inactive.png" onclick="onPrivacyCheckboxToggle()"></td>
									<td>I have read and accept the <a href="https://www.rismosch.com/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy</a></td>
								</tr>
							</table>
						</p>
						
						<p><span class="contact_error"><?php if(isset($errorContact)) echo $errorContact; ?></span></p>
						<p>
							<button
								class="g-recaptcha"
								id="submit_button"
								type="submit"
								style="display:none;"
								data-sitekey="<?php echo $reCAPTCHA_web_key;?>"
								data-callback='onRecaptchaSuccess'
							>
							Send
							</button>
							
							<a class="button button_inactive" id="submit_button_inactive">Send</a>
						</p>
						
						<img id="loading_animation" class="loading_animation pixel_image invisible" src="assets/icon_8bit/loading.gif">
					</div>
				</form>
			
			</div>

			<noscript>
				<p>Unfortunately, this page only works if JavaScript is enabled :(</p>
			</noscript>
			
		</div>
		
		<?php echo_foot(true); ?>
	</div>
	
	<script>
		
		document.getElementById("javascript_content").style.display = "block";

		var privacyAccepted = false;
		var isSubmitting = false;
		function onPrivacyCheckboxToggle()
		{
			if(isSubmitting)
				return;
			
			privacyAccepted = !privacyAccepted;
			
			if(privacyAccepted)
			{
				document.getElementById("privacy_checkbox").src="assets/icon_8bit/checkbox_active.png";
				document.getElementById("submit_button").style.display = "inline-block";
				document.getElementById("submit_button_inactive").style.display = "none";
			}
			else
			{
				document.getElementById("privacy_checkbox").src="assets/icon_8bit/checkbox_inactive.png";
				document.getElementById("submit_button").style.display = "none";
				document.getElementById("submit_button_inactive").style.display = "inline-block";
			}
		}
		
		function onRecaptchaSuccess() {
			return new Promise(function(resolve, reject){
				
				isSubmitting = true;
				
				document.getElementById('loading_animation').classList.remove('invisible');
				document.getElementById('submit_button').style.display = "none";
				
				document.getElementById('contact-form').submit();
				
				resolve;
			});
		}
		
		messageCount(document.getElementById('contact_textarea').value.length)
		document.getElementById('contact_textarea').onkeyup = function()
		{
			messageCount(this.value.length);
		}
		
		function messageCount(characterCount){
			var contact_textarea_count = document.getElementById('contact_textarea_count');
			contact_textarea_count.innerHTML = "Characters: " + characterCount + "/999";
			
			if(characterCount > 999)
				contact_textarea_count.style.color = "var(--pico-8-red)";
			else
				contact_textarea_count.style.color = "var(--pico-8-black)";
		}
		
	</script>
</body>
</html>
