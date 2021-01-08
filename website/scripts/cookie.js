function setCookie(name, value, expireTime)
{
	var date = new Date();
	date.setTime(date.getTime() + (expireTime));
	var expires = "expires="+date.toUTCString();
	document.cookie = name + "=" + value + ";" + expires + ";SameSite=Strict;path=/";
	
	return expires;
}

function getCookie(name)
{
	var cookieName = name + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var cookies = decodedCookie.split(';');
	
	var length = cookies.length;
	for ( var i = 0; i < length; ++i)
	{
		var cookie = cookies[i];
		while (cookie.charAt(0) == ' ')
			cookie = cookie.substring(1);
		
		if (cookie.indexOf(cookieName) == 0)
			return cookie.substring(cookieName.length, cookie.length);
	}
	
	return "";
}