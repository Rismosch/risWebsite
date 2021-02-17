const continuousCookieName = "continuousSession";

var isContinuousSession = false;

function checkContinuousSession()
{
	var cookie = getCookie(continuousCookieName);
	setCookie(continuousCookieName, true, 900000) // 15 minutes * 60 * 1000 = 300000 milliseconds
	
	if(cookie) // returning the cookie doesn't work, because the cookie is either "true" or ""
		isContinuousSession = true;
	else
		isContinuousSession = false;
}