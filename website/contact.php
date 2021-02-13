<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include 'php/head.php';

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
	$nameSanitized = filter_var(utf8_decode($nameUnsafe),FILTER_SANITIZE_ENCODED);
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
	else if (!filter_var(utf8_decode($emailSanitized), FILTER_VALIDATE_EMAIL)) {
		$errorEmail = 'Email is invalid';
		$errors[] = $errorEmail;
	}
	else if (strlen($emailSanitized) > 99){
		$errorEmail = "Email is too long";
		$errors[] = $errorEmail;
	}
	
	
	$messageUnsafe = $_POST['message'];
	$messageSanitized = filter_var($messageUnsafe,FILTER_SANITIZE_ENCODED);
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
			//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
			$mail->isSMTP();
			$mail->Host = 'localhost';
			$mail->SMTPAuth = false;
			$mail->SMTPAutoTLS = false;
			$mail->Port = 25;
			
			// Recipients
			$mail->setFrom($contactEmail, 'Rismosch');
			$mail->addAddress($contactEmail, 'Rismosch');
			
			// Content
			$mail->isHTML(true);
			$mail->Subject = 'Contact Form';
			$mail->Body    = "Name: {$nameSanitized}<br>Email: {$emailSanitized}<br>Message:<br><br>{$messageSanitized}";
			$mail->AltBody = "Name: {$nameSanitized}\nEmail: {$emailSanitized}\nMessage:\n\n{$messageSanitized}";
			
			$mail->send();
			
			// Success
			$contact_successful = true;
			$nameUnsafe = "";
			$emailUnsafe = "";
			$messageUnsafe = "";
			
		} catch (Exception $e) {
			$errorContact = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}
}

?>

	<title>Contact</title>
	<meta name="description" content="Contact Simon Sutoris">
	<meta name="keywords" content="contact, email">

	<meta name="robots" content="all">

	<meta property="og:title" content="Contact" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/contact" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x20.png" />

	<meta name="author" content="Simon Sutoris">

</head>
<body>
	<div class="background">
		<?php include 'php/banner.php'; ?>
		
		<?php include 'php/selector.php'; ?>
		
		<div class="content" id="content">
			
			<h1>Contact me</h1>
			
			<div style="display:<?php if($contact_successful) echo "block"; else echo "none"?>;">
				<p style="color: var(--pico-8-green);">Success &#10003;</p>
				
				<p>I have received your message! I will try to come back too you as soon as possible :)</p>
			</div>
			
			<div style="display:<?php if($contact_successful) echo "none"; else echo "block"?>;">
				<form action="contact" method="POST" id="contact-form">
					<div>
						<p>Name <span class="contact_error" id="display_error_name"><?php if(isset($errorName)) echo $errorName; ?></span></p>
						<input name="name" class="contact_input" id="contact_namefield" type="text" value = "<?php if(isset($nameUnsafe)) echo $nameUnsafe; ?>">
						
						<p>Email <span class="contact_error" id="display_error_email"><?php if(isset($errorEmail)) echo $errorEmail; ?></span></p>
						<input name="email" class="contact_input" id="contact_emailfield" type="text" value = "<?php if(isset($emailUnsafe)) echo $emailUnsafe; ?>">
						
						<p>Message <span class="contact_error" id="display_error_message"><?php if(isset($errorMessage)) echo $errorMessage; ?></span></p>
						<textarea name="message" class="contact_input" id="contact_textarea" rows="10" cols="35"><?php if(isset($messageUnsafe)) echo $messageUnsafe; ?></textarea>
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
			
		</div>
		
		<?php $uses_captcha = true; include 'php/foot.php'; ?>
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
