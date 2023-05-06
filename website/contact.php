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
	
	if (!array_key_exists('privacy_accepted', $_POST) || $_POST['privacy_accepted'] != true)
	{
		$errorPrivacy = 'Privay Policy must be read and accepted';
		$errors[] = $errorPrivacy;
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
		try
		{
			// Send to myself
			{
				$mail_self = new PHPMailer(true);

				// Server settings
				// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
				$mail_self->isSMTP();
				$mail_self->Host = 'localhost';
				$mail_self->SMTPAuth = false;
				$mail_self->SMTPAutoTLS = false;
				$mail_self->Port = 25;
				
				// Recipients
				$mail_self->setFrom($contactformEmail, 'noreply');
				$mail_self->AddReplyTo($contactformEmail,'noreply');
				$mail_self->addAddress($myEmail, 'Rismosch');

				// Content
				$mail_self->CharSet = 'UTF-8';
				$mail_self->Encoding = 'base64';
				$mail_self->Subject = 'new message from contact form!';
				$mail_self->Body    = "Name: {$nameSanitized}\nEmail: {$emailSanitized}\nSubject: {$subjectSanitized}\nMessage:\n\n{$messageSanitized}";

				$mail_self->send();
			}

			// Send to Sender
			{
				$mail_sender = new PHPMailer(true);
				// Server settings
				// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
				$mail_sender->isSMTP();
				$mail_sender->Host = 'localhost';
				$mail_sender->SMTPAuth = false;
				$mail_sender->SMTPAutoTLS = false;
				$mail_sender->Port = 25;
				
				// Recipients
				$mail_sender->setFrom($noreplyEmail, 'noreply');
				$mail_sender->AddReplyTo($noreplyEmail,'noreply');
				$mail_sender->addAddress($emailSanitized, 'Rismosch');

				// Content
				$mail_sender->CharSet = 'UTF-8';
				$mail_sender->Encoding = 'base64';
				$mail_sender->Subject = "Rismosch: I received your message!";
				$mail_sender->Body    = "Hello!\n\nI just received a new message from you! If this is not a scam and you don't try to sell me something, I will try to come back to you as soon as I've read it :)\n\nSincerely,\nSimon Sutoris\n\n---\n\nDO NOT REPLY TO THIS EMAIL.\nIf you have any questions, contact me here: https://www.rismosch.com/contact\n\n<ORIGINAL MESSAGE>\nName: {$nameSanitized}\nEmail: {$emailSanitized}\nSubject: {$subjectSanitized}\nMessage:\n\n{$messageSanitized}";

				$mail_sender->send();
			}
			
			// Success
			$contact_successful = true;
			$nameUnsafe = "";
			$emailUnsafe = "";
			$messageUnsafe = "";
			
		} catch (Exception $e)
		{
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

			<!--<p>&#128119; Under Construction... &#128296;</p>-->
			
			<div style="display:none" id="javascript_content">
				
				<form action="contact" method="POST" id="contact-form">
					<div>
						
						<label>Name <span class="contact_error" id="display_error_name"><?php if(isset($errorName)) echo $errorName; ?></span><br>
						<input name="name" class="contact_input" id="contact_namefield" type="text" value = "<?php if(isset($nameUnsafe)) echo $nameUnsafe; ?>"><br>
						</label>

						<label>Email <span class="contact_error" id="display_error_email"><?php if(isset($errorEmail)) echo $errorEmail; ?></span><br>
						<input name="email" class="contact_input" id="contact_emailfield" type="text" value = "<?php if(isset($emailUnsafe)) echo $emailUnsafe; ?>"><br>
						</label>
						
						<label>Subject <span class="contact_error" id="display_error_subject"><?php if(isset($errorSubject)) echo $errorSubject; ?></span><br>
						<input name="subject" class="contact_input" id="contact_subjectfield" type="text" value = "<?php if(isset($subjectUnsafe)) echo $subjectUnsafe; ?>"><br>
						</label>
						
						<label>Message <span class="contact_error" id="display_error_message"><?php if(isset($errorMessage)) echo $errorMessage; ?></span><br>
						<textarea name="message" class="contact_input" id="contact_textarea" rows="10" cols="35"><?php if(isset($messageUnsafe)) echo $messageUnsafe; ?></textarea>
						</label>
						<p id="contact_textarea_count" style="margin-top: -1.3em;">0/999</p>
						
						<br>

						<label><span class="contact_error" id="display_error_subject"><?php if(isset($errorPrivacy)) echo $errorPrivacy; ?></span><br>
						<input name="privacy_accepted" style="width: 2em; height: 2em; position: relative; top: 4px;" type="checkbox">
						I have read and accept the <a href="https://www.rismosch.com/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy</a>
						</label>
						
						<p><span class="contact_error"><?php if(isset($errorContact)) echo $errorContact; ?></span></p>
						<button
							class="g-recaptcha"
							id="submit_button"
							type="submit"
							data-sitekey="<?php echo $reCAPTCHA_web_key;?>"
							data-callback='onRecaptchaSuccess'
						>
						Send
						</button>
						
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
		
		function onRecaptchaSuccess() {
			return new Promise(function(resolve, reject){
				
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
