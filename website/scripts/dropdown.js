function showDropdown()
{
	document.getElementById("dropdownList").classList.toggle("show");
}

function closeDropdown(event)
{
	if (!event.target.matches('.dropdownButton'))
	{
		var dropdowns = document.getElementsByClassName("dropdownContent");
		for (var i = 0; i < dropdowns.length; ++i)
		{
			var openDropdown = dropdowns[i];
			if (openDropdown.classList.contains('show')) {
				openDropdown.classList.remove('show');
			}
		}
	}
}