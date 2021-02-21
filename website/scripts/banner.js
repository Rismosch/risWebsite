var animationID;

var animationIsPlaying = false;
var currentFrame = 0;

const shimmerFrame = 50;
const maxFrame = 77;
const frameHeight = 21;
const sheetHeight = 1638;
const interval = 25;

function onloadBanner(){
	var bannerImage = document.querySelector('#banner');
	
	if(bannerImage.complete)
		playStartupAnimation();
	else
		bannerImage.addEventListener('load',playStartupAnimation);
}

function playStartupAnimation(){
	if (isContinuousSession)
		playAnimation(shimmerFrame)
	else
		playAnimation(0);
};

function playHoverAnimation(){
	if(!userAgentIsMobile())
		playAnimation(shimmerFrame);
}

function playAnimation(frame){
	if(animationIsPlaying)
		return;
	
	animationIsPlaying = true;
	currentFrame = frame;
	animationID = setInterval(advanceAnimation,interval);
}

function advanceAnimation(){
	setFrame(++currentFrame);
	
	if(currentFrame >= maxFrame){
		clearInterval(animationID);
		animationIsPlaying = false;
	}
}

function setFrame(frame){
	document.getElementById("banner").style.backgroundPosition = "0px " + (sheetHeight - frameHeight * frame) + "px";
}