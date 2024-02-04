<p>If at any time you want to see the actual source code, just press [Ctrl]+[Shift]+[i] or [Cmd]+[Opt]+[i] for macOS while your browser is open. This key combination will display the inspector of your browser. You can then simply hover over the HTML elements and see how things are actually build up.</p>

<?php late_image(get_source("picture_1.webp"),"","max-width:100%; margin:auto; display: block;"); ?>

<p>Alternatively, you can just type <span class="code">view-source:</span> before the URL, for example like this:</p>

<code>
<p class="code code_block">view-source:https://rismosch.com/</p>
</code>

<p>With this, you can see the actual HTML. This even works on mobile! By the way, you can do these things with every website, not just mine. So if you ever want to see how Googles HTML looks like, there is literally nothing stopping you to check that out. If you don’t want to do any of that, you can also just check out my GitHub, in which I made all the source code of this website open source.</p>

<p class="auto-break"><a href="https://github.com/Rismosch/risWebsite" target="_blank" rel="noopener noreferrer">https://github.com/Rismosch/risWebsite</a></p>

<p>But since I don’t expect you to read the code and immediately understand everything right away, let’s talk about what I actually build &#128579; Every page is built exactly the same: The body’s background is set to a dark-blue and contains a black <span class="code">&lt;div&gt;</span>. In this <span class="code">&lt;div&gt;</span> there are 4 Elements:</p>

<ol>
<li>The Rismosch Banner</li>
<li>The Selector Tabs</li>
<li>The Actual Content and</li>
<li>Some Foot Links</li>
</ol>

<?php late_image(get_source("picture_2.png"),"pixel_image","max-width:100%; margin:auto; width: 455px; display: block;"); ?>

<p>The easiest of these 4 to implement, are the Foot Links. These are literally just a GIF, 4 pictures and 3 links. CSS makes them look nice. That’s literally it.</p>

<p>The next easiest, is the Content, which is just a <span class="code">&lt;div&gt;</span> with grey background, white border and some padding. Again, that’s literally it. Depending on which site you are viewing, different content is displayed in this <span class="code">&lt;div&gt;</span>.</p>

<p>The Banner and Selector tabs are quite a bit more complicated. Let’s start with the Tabs, because these are just pretty CSS magic. I briefly mentioned in the CSS chapter, that HTML Tags can be defined with classes and ids.</p>

<code>
<p class="code code_block">
	<span style="color:var(--pico-8-cyan);">&lt;div</span> <span style="color:var(--pico-8-red);">class</span>=<span style="color:var(--pico-8-purple);">&#34;myClass1 myClass2&#34;</span> <span style="color:var(--pico-8-red);">id</span>=<span style="color:var(--pico-8-purple);">&#34;myId&#34;</span><span style="color:var(--pico-8-cyan);">&gt;&lt;&#47;div&gt;</span>
</p>
</code>

<p>The main difference between a class and an id is, that a tag can have multiple classes, but only one id. You can easily add and remove, even toggle classes. The Selector Tabs abuse this feature. The tabs are just a horizontal list. Each element has the class <span class="code">selector_tab</span>, which gives them a white border, a dark grey background, white text, and most importantly: A small margin on top. This margin makes it so, that there is a little space above the tab. You will notice that one of the tabs isn’t dark grey though, that is because additionally to the other class, it also has the <span class="code">active_tab</span> class, which overwrites the colors. It also removes the margin on top and makes the tab a little larger. Since the other tabs have this margin while the active one doesn’t, it makes it appear that the active tab is sticking out. Additionally, the margin is also removed, when you hover over the tab, thus making the tab stick out when you hover over them.</p>

<p>The banner is the most complicated UI Element on my website, and it’s using JavaScript. Essentially, it’s just an <span class="code">&lt;img&gt;</span> tag. This image, has a background image, which is this sprite-sheet:</p>

<?php late_image(get_source("picture_3.png"),"","max-width:100%; margin:auto; display: block;"); ?>

<p>By making the width and height smaller than the whole sheet, the image only shows a small section. Then, with some JavaScript magic, the Background-Position offset is adjusted each frame, thus scrolling through the whole sprite sheet and playing the animation.</p>

<p>What you may have noticed, is that the banner uses pixel art. And when using pixel art, it’s a good idea to only use the resolution required, otherwise your images become unnecessarily big. Setting the width and height of the image bigger than it actually is, or using <span class="code">transform:scale(5)</span> for the banner, which makes the image 5 times larger, we can scale up low resolution pixel art. However, the problem with that is, that scaled up images end up blurred. That is because the browser needs to generate new pixels to make the image bigger, and it usually does this by taking the average of surrounding pixels, which blurs it. In most cases this is the preferred way to upscale images, but not for pixelart, where you want crisp and clearly defined edges. To fix that I have the following selector in my main CSS file:</p>

<code>
<p class="code code_block">
.<span style="color:var(--pico-8-red)">pixel_image</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">image-rendering</span>: -moz-crisp-edges;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">&#47;* Firefox *&#47;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">image-rendering</span>: -o-crisp-edges;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">&#47;* Opera *&#47;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">image-rendering</span>: -webkit-optimize-contrast;<span style="color:var(--pico-8-green)">&#47;* Webkit (non-standard naming) *&#47;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">image-rendering</span>: pixelated;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">-ms-interpolation-mode</span>: nearest-neighbor;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">&#47;* IE (non-standard property) *&#47;</span><br>
}
</p>
</code>

<p>Now adding the class <span class="code">pixel_image</span> to <i>any</i> upscaled pixelart, creates the result that we want. Below is the same picture twice. However, one has and the other has not the class <span class="code">pixel_image</span>.</p>

<?php late_image(get_source("picture_4.png"),"","width: 365px; max-width:100%; margin:auto; display: block;"); ?>
<?php late_image(get_source("picture_4.png"),"pixel_image","width: 365px; max-width:100%; margin:auto; display: block;"); ?>

<p>The final thing I want to mention is the color scheme that I used. You can actually use variable-like colors with CSS. I wanted to use the PICO-8 color scheme, not that I ever worked with the PICO-8, but because I just like the colors. On the very top of my main CSS I have the following code:</p>

<code>
<p class="code code_block">
:<span style="color:var(--pico-8-orange)">root</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-black</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#000000;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-blue</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#1d2b53;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-purple</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#7e2553;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-green</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#008751;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-brown</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#ab5236;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-dark-grey</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#5f574f;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-light-grey</span>:&nbsp;&nbsp;&nbsp;&nbsp;#c2c3c7;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-white</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#fff1e8;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-red</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#ff004d;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-orange</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#ffa300;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-yellow</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#ffec27;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-lime</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#00e436;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-cyan</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#29adff;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-washed-grey</span>:&nbsp;&nbsp;&nbsp;#83769c;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-pink</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#ff77a8;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">--pico-8-flesh</span>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#ffccaa;<br>
}
</p>
<code>

<p>And then in the rest of the CSS I can use these like this:</p>

<code>
<p class="code code_block">
<span style="color:var(--pico-8-cyan)">p</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">color</span>: var(--pico-8-red);<br>
}
</p>
<code>

<p>When you take a look at my source code, you will notice that every single color in every HTML and CSS uses var(), thus creating a consistent color scheme.</p>