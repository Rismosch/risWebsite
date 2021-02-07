function showDropdown(id)
{
	var dropdowns = document.getElementsByClassName("dropdownContent");
	for (var i = 0; i < dropdowns.length; ++i)
	{
		var dropdown = dropdowns[i];
		
		if (!dropdown.classList.contains(id))
		{
			continue;
		}
		
		dropdown.classList.toggle("show");
	}
}

function closeDropdown(event)
{
	if (event.target.matches('.dropdownButton'))
	{
		closeOtherDropdowns(event.target.classList[1]);
	}
	else
	{
		closeOtherDropdowns("");
	}
}

function closeOtherDropdowns(id)
{
	var dropdowns = document.getElementsByClassName("dropdownContent");
	for (var i = 0; i < dropdowns.length; ++i)
	{
		var dropdown = dropdowns[i];
		
		if (dropdown.classList.contains(id))
		{
			continue;
		}
		
		if (dropdown.classList.contains('show'))
		{
			dropdown.classList.remove('show');
		}
	}
}