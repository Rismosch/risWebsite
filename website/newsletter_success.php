<?php
include 'php/util.php';

echo_head();

?>
	<script src="https://www.google.com/recaptcha/api.js"></script>
	
	<title>Newsletter</title>
	<meta name="description" content="Contact Simon Sutoris">
	<meta name="keywords" content="newsletter, subscribe, email">

	<meta name="robots" content="noindex">

	<meta property="og:title" content="Newsletter" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="https://www.rismosch.com/newsletter" />
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
			
			<h1>Newsletter</h1>

			<p style="color: var(--pico-8-green);">Success &#10003;</p>
			<p>Thanks for signing up! :)<br>I have sent you a message to confirm your email.</p>
			<p>This may take up to 5 minutes. Please make sure that you also check your spam folder!</p>
			<p>The confirmation email will expire in 24 hours. Once it's expired, you will be automatically unsubscribed from my newsletter.</p>
			
		</div>
		
		<?php echo_foot(true); ?>
	</div>
</body>
</html>
