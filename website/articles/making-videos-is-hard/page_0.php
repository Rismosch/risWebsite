<p>Quaternions are easy. I am not saying this to flex my intelligence. I am saying this because I genuinely believe that they are easy. The problem is just that there aren't many beginner friendly resources out there. I mean there is <a href="https://eater.net/quaternions" target="_blank" rel="noopener noreferrer">this</a>, by Grant Sanderson and Ben Eater, but Grant is a mathematician, not a programmer. So while the videos are <i>theoretically</i> correct, they aren't particularly helpful in your 3d programming day to day life.</p>

<p>I was trying to change this, by actually doing a beginner friendly intro to Quaternions, but it turned out to be much more work than anticipated. I made <a href="https://www.rismosch.com/article?id=quaternion-playground" target="_blank" rel="noopener noreferrer">this visualization tool</a>, which is something. It's inspired by the book "Visualizing Quaternions" by Andrew J. Hanson and it's in my opinion better than any other visualization that I've found online.</p>

<?php late_image(get_source("obama.webp"),"","display: block; margin: auto; max-width: 100%;");?>

<p>Unfortunately, the tool is rather worthless if you don't have a mentor that explains it to you. So the idea was that I also make a video to specifically explain the tool and give a good, simple explanation of quaternions. An explanation that isn't riddled with formulas, doesn't require an understanding of the 4th dimension, and doesn't ask of a deep understanding of mathematics. Such an explanation is indeed possible: I've held a presentation at my job to do exactly that. But the slides are rather low quality, and use many questionable pictures which violate copyright. (On that note, fuck copyright.)</p>

<p>If I wanted to make something that would go on YouTube, instead I had to make custom pictures and diagrams, write a proper script, record and edit a video. Problem is, the visualization tool already took a month of work, and this video will take probably another. Also also, I advocate to use <a href="https://docs.unity3d.com/ScriptReference/Quaternion.AngleAxis.html" target="_blank" rel="noopener noreferrer">Quaternion.AngleAxis</a> and <a href="https://docs.unity3d.com/ScriptReference/Quaternion.LookRotation.html" target="_blank" rel="noopener noreferrer">Quaternion.LookRotation</a> and I discourage everyone to use Euler Angles ever again. But I want to keep things as general as possible. I don't want to say <i>"just use the provided functions by Unity"</i>. Instead I want to provide proper formulas, because 1: Not everyone is using Unity. And 2: People may want to write their own quaternion library, and this just doesn't fly.</p>

<p>This is all a lot of work.</p>

<?php late_image(get_source("screenshot.webp"),"","display: block; margin: auto; max-width: 100%;");?>

<p>It is at this moment, where I truly realized the difference between people who make, and people who talk. On YouTube you find a lot of people who talk about how to program, how to make a game the "proper way". But often the advice these people preach is shallow and meaningless. The worst kind of these people are video essayists and critics, who talk about what makes a game good, but never made or even considered making one. On this note, huge respect for Mark Brown from Game Maker's Toolkit, who actually tried to put his knowledge to the test and tried making one.</p>

<p>This whole makers vs talkers thing reminds me about the book "Clean Code" by Robert Martin, which is full of questionable advice, which the author himself doesn't actually follow. It also reminds me about No Man's Sky, how it was ruined by the fact that they talked about it too much, but then fixed it by shutting up and actually fixing it: <a href="https://youtu.be/O5BJVO3PDeQ" target="_blank" rel="noopener noreferrer">https://youtu.be/O5BJVO3PDeQ</a></p>

<p>I decided that I want to be a maker, not a talker. As such I scrapped the idea of a video, and instead focus more on my engine. So here's what I've been up to: I implemented a basic Vulkan based 3d renderer into my engine :)</p>

<video muted="true" style="max-width:100%; display: block; margin: auto;" loading='lazy' controls>
<source src="<?php echo get_source("backface_culling_compressed.mp4");?>" type="video/mp4">
</video>

<p>Because of this, my brain is now full of transformation matrices instead of quaternions. Next steps would be build on top of it more. At the time of writing, I am missing index buffers and depth testing. Then I would also want to start with my resource system, such that I can actually import 3d files. Then I would also want to put my shaders into files, such that I can recompile and hotswap them at runtime. Nice stuff.</p>

<?php late_image(get_source("photo.webp"),"","display: block; margin: auto; max-width: 100%;");?>

<p>Until then, radio silence &#128586;</p>
