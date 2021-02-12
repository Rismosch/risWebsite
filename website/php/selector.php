<?php

$tab_count = 4;

$tab_selection = array_fill(0,$tab_count,"");
$tab_selection_dropdown = array_fill(0,$tab_count,"");

if(isset($active_tab) && $active_tab >= 0 && $active_tab < $tab_count)
{
	$tab_selection[$active_tab] = "active_tab";
	$tab_selection_dropdown[$active_tab] = "class=\"dropdown_selected\" ";
}
else
{
	$active_tab = -1;
}

echo '
<div class="selector" id="selector">

	<ul class="selector_tabs desktop" id="selector_tabs">
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
	
	<div class="mobile">
		<div onclick="showDropdown(\'dropdownSelector\')" class="dropdownButton dropdownSelector selector_menu pixel_image" id="dropdownButton"></div>
		<div class="dropdownContent dropdownSelector dropdown">
			<a '.$tab_selection_dropdown[0].'href="https://www.rismosch.com/">Home</a>
			<a '.$tab_selection_dropdown[1].'href="https://www.rismosch.com/blog">Blog</a>
			<a '.$tab_selection_dropdown[2].'href="https://www.rismosch.com/projects">Projects</a>
			<a '.$tab_selection_dropdown[3].'href="https://www.rismosch.com/about">About</a>
		</div>
	</div>
	
</div>
';

?>