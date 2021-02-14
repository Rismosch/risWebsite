<?php

include 'php/head.php';

$errorEmail = "Under Construction..."

?>
	
	<title>Newsletter</title>
	<meta name="description" content="Contact Simon Sutoris">
	<meta name="keywords" content="newsletter, subscribe, email">

	<meta name="robots" content="all">

	<meta property="og:title" content="Newsletter" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/newsletter" />
	<meta property="og:image" content="https://www.rismosch.com/assets/meta_image_x20.png" />

	<meta name="author" content="Simon Sutoris">

</head>
<body>
	<div class="background">
		<?php include 'php/banner.php'; ?>
		
		<?php include 'php/selector.php'; ?>
		
		<div class="content" id="content">
			
			<h1>Newsletter</h1>
			
			<p>Subscribe to my newsletter to be notified when I upload a new blogpost or project :)</p>
			<p>Newsletter can be unsubscribed at any time.</p>
			<br>
			
			<form action="newsletter" method="POST" id="contact-form">
				
				<p>Email <span class="contact_error" id="display_error"><?php if(isset($errorEmail)) echo $errorEmail; ?></span></p>
				<input name="email" class="contact_input" id="newsletter_emailfield" type="text" value = "<?php if(isset($emailUnsafe)) echo $emailUnsafe; ?>">
				
				<p><span class="contact_error"><?php if(isset($errorContact)) echo $errorContact; ?></span></p>
				<p>
					<button
						class="g-recaptcha"
						type="submit"
						data-sitekey="<?php echo $reCAPTCHA_web_key;?>"
						data-callback='onRecaptchaSuccess'
					>
					Subscribe
					</button>
				</p>
				
			</form>
		</div>
		
		<?php $uses_captcha = true; include 'php/foot.php'; ?>
	</div>
	
</body>
<script>
	function onRecaptchaSuccess () {
		document.getElementById('contact-form').submit();
	}
</script>
</html>
