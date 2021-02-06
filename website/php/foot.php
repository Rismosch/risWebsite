<?php

echo '
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
		<p>
			<a class="foot_link" href="https://www.rismosch.com/privacy">Privacy Policy</a>
			<a class="foot_link_divider">&nbsp; | &nbsp;</a>
			<a class="foot_link" href="https://www.rismosch.com/references">References</a>
			<a class="foot_link_divider">&nbsp; | &nbsp;</a>
			<a class="foot_link" href="https://www.rismosch.com/contact">Contact</a>
		</p>
	</div>
	
	<div class="foot_copyright">
		<p>Copyright &#169; 2020 Simon Sutoris</p>
	</div>
	
</div>
';

if(isset($uses_captcha) && $uses_captcha == true)
	echo '<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top scroll_captcha_offset">Top</button>';
else
	echo '<button onclick="scrollToTop()" id="scroll_to_top" class="scroll_to_top">Top</button>';

?>