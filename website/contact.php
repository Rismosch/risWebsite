<?php

include 'php/head.php';

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
</head>
<body>
	<div class="background">
		<?php include 'php/banner.php'; ?>
		
		<?php include 'php/selector.php'; ?>
		
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
