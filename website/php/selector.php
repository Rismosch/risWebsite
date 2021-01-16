<?php

$tab_count = 4;

$tab_selection = array_fill(0,$tab_count,"");
if(isset($active_tab) && $active_tab >= 0 && $active_tab < $tab_count)
	$tab_selection[$active_tab] = "active_tab";

echo '
<div class="selector" id="selector">
	<ul class="selector_tabs" id="selector_tabs">
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
		<li class="selector_tab '. $tab_selection[3] .'" id="selector_tab active_tab">
			<a href="https://www.rismosch.com/about">
				<div><b>About</b></div>
			</a>
		</li>
	</ul>
</div>
';

?>