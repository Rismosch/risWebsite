<p>One thing, that I struggled with more than I should have, is making my website look nice on mobile. Problem is, the text on mobile was super inconsistent. Some text was big, some text was small.</p>

<p>Fixing the inconsistency is pretty easy with this CSS:</p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan)">body</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">-webkit-text-size-adjust</span>: none;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">-moz-text-size-adjust</span>: none;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">-ms-text-size-adjust</span>: none;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">text-size-adjust</span>: none;<br>
}
</p>

<p>But now that everything is consistent, when you view your website on mobile, the text is too small. As it turns out, most mobile browsers &#34;boost&#34; the text size, so that text remains readable. But the Problem with this boosting is, that it is super inconsistent, and you are probably better off by disabling it with the code I posted above.</p>

<p>I tried many things to fix the text size issue, from detecting if you are a using a mobile client or not with JavaScript, to sending the browser a different CSS with PHP (more on PHP in a later chapter), none of which worked in a satisfying way. Then, I found a header tag, which solved this issue immediately. I may present you, the most important HTML Header Tag, which you should probably implement in your website:</p>

<p class="code code_block"><span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">name</span>=<span style="color:var(--pico-8-purple)">&#34;viewport&#34;</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">&#34;width=device-width,initial-scale=1&#34;</span> <span style="color:var(--pico-8-cyan)">&#47;&gt;</span></p>

<p>So what does it do and how does it fix the text-size issue? CSS has a quirk which I seriously don’t understand: CSS resolution is independent from your device resolution. Let’s say for simplicity’s sake, your desktop pc display is 200 pixels wide. Now you want to implement a button which is 100 pixels wide. It’s only logical to conclude, that this button will fit half of your screen. Now you have a mobile device which is 50 pixels wide, you would assume that the button will the fill the whole screen from left to right. But for some reason, the CSS resolution is larger than the resolution of your mobile device. Thus the button is displayed way smaller than you would expect.</p>

<p>In the image below, left is desktop and right is mobile. Because CSS- and device resolution doesn't match, the Button is smaller on mobile.</p>

<?php late_image(get_source("picture_1.png"),"pixel_image","max-width:100%; margin:auto; display: block;"); ?>

<p>The meta tag posted above fixes this, by basically telling the browser that the CSS resolution is the same as the device resolution. If you implement this tag, this button will be properly displayed.</p>

<?php late_image(get_source("picture_2.png"),"pixel_image","max-width:100%; margin:auto; display: block;"); ?>

<p>Depending how your website should look like, this may actually be not that important. But if you are going to use one single HTML file for both desktop and mobile (like I do), this tag should do the trick. And once that works, you can now properly work with media queries.</p>

<p>Media queries allow you to adapt your CSS, depending on the devices resolution. Take for example the following CSS:</p>

<p class="code code_block">
.<span style="color:var(--pico-8-red)">myContainer</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">background-color</span>: var(--pico-8-red);<br>
}<br>
<br>
@media only screen and (max-width: 500px) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;.<span style="color:var(--pico-8-red)">myContainer</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">background-color</span>: var(--pico-8-yellow);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</p>

<p>What this tells your browser, is that the container should only be yellow, if the screen is at maximum 500 pixels wide. Otherwise it will be red. To demonstrate, consider this rectangle:</p>

<div class="myContainer" style="width:300px; height:100px; display:block; margin:auto;"></div>

<p>When you are on desktop, this rectangle is probably red. But when you take your browser into windowed mode and make it smaller, you will notice that it will change its color to yellow. If you are on mobile, it is yellow, if you view this page in vertical. But it will be red, if you view this page in horizontal.</p>

<p><b>Disclaimer:</b> This demo <i>only</i> works on mobile, if your device is less than 500 pixels wide and more than 500 pixels high. If your resolution is outside of these criteria, then the rectangle will not change color.</p>

<p>This is pretty neat, because this allows us to have different UI elements, depending if the device is wide enough or not. Take for example the Selector-Tabs again. These are way too wide to be displayed on most mobile phones. I have two media queries, which look a bit like this:</p>

<p class="code code_block">
@media only screen and (min-width: 681px) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;.<span style="color:var(--pico-8-red)">desktop</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">display</span>: block;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;.<span style="color:var(--pico-8-red)">mobile</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">display</span>: none;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}<br>
<br>
@media only screen and (max-width: 680px) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;.<span style="color:var(--pico-8-red)">desktop</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">display</span>: none;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;.<span style="color:var(--pico-8-red)">mobile</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey)">display</span>: block;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}<br>
</p>

<p>Basically, what this does is, as long as your device width is larger than 680 pixels, every tag with the class <span class="code">desktop</span> will be displayed and every tag with the class <span class="code">mobile</span> will be hidden. If your screen is smaller than that, <span class="code">mobile</span> will be displayed and <span class="code">desktop</span> will be hidden. When you take a look at the source code of my website, you will notice that the selector tabs are implemented twice, once as the tabs, and once as a dropdown menu. One has the <span class="code">desktop</span> class, and the other the <span class="code">mobile</span> class, so depending on how wide your screen is, a different selector will be displayed. Pretty neat.</p>

<p class="code code_block" style="color:var(--pico-8-cyan)">
&lt;div <span style="color:var(--pico-8-red)">class</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector"</span> <span style="color:var(--pico-8-red)">id</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector"</span>&gt;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;ul <span style="color:var(--pico-8-red)">class</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector_tabs desktop"</span> <span style="color:var(--pico-8-red)">id</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector_tabs"</span>&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li <span style="color:var(--pico-8-red)">class</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector_tab active_tab"</span> <span style="color:var(--pico-8-red)">id</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector_tab"</span>&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;a <span style="color:var(--pico-8-red)">href</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"https://www.rismosch.com/"</span>&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div&gt;&lt;b&gt;<span style="color:var(--pico-8-black)">Home</span>&lt;/b&gt;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/a&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/li&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li <span style="color:var(--pico-8-red)">class</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector_tab "</span> <span style="color:var(--pico-8-red)">id</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector_tab"</span>&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;a <span style="color:var(--pico-8-red)">href</span><span style="color:var(--pico-8-black)">=</span>"<span style="color:var(--pico-8-purple)">https://www.rismosch.com/blog"</span>&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div&gt;&lt;b&gt;<span style="color:var(--pico-8-black)">Blog</span>&lt;/b&gt;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/a&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/li&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li <span style="color:var(--pico-8-red)">class</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector_tab "</span> <span style="color:var(--pico-8-red)">id</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector_tab"</span>&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;a <span style="color:var(--pico-8-red)">href</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"https://www.rismosch.com/projects"</span>&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div&gt;&lt;b&gt;<span style="color:var(--pico-8-black)">Projects</span>&lt;/b&gt;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/a&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/li&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li <span style="color:var(--pico-8-red)">class</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector_tab "</span> <span style="color:var(--pico-8-red)">id</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"selector_tab"</span>&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;a <span style="color:var(--pico-8-red)">href</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"https://www.rismosch.com/about"</span>&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div&gt;&lt;b&gt;<span style="color:var(--pico-8-black)">About</span>&lt;/b&gt;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/a&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/li&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/ul&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;div <span style="color:var(--pico-8-red)">class</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"mobile"</span>&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div <span style="color:var(--pico-8-red)">onclick</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"showDropdown('dropdownSelector')"</span> <span style="color:var(--pico-8-red)">class</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"dropdownButton dropdownSelector selector_menu pixel_image"</span> <span style="color:var(--pico-8-red)">id</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"dropdownButton"</span>&gt;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div <span style="color:var(--pico-8-red)">class</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"dropdownContent dropdownSelector dropdown"</span>&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;a <span style="color:var(--pico-8-red)">class</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"dropdown_selected"</span> <span style="color:var(--pico-8-red)">href</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"https://www.rismosch.com/"</span>&gt;<span style="color:var(--pico-8-black)">Home</span>&lt;/a&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;a <span style="color:var(--pico-8-red)">href</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"https://www.rismosch.com/blog"</span>&gt;<span style="color:var(--pico-8-black)">Blog</span>&lt;/a&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;a <span style="color:var(--pico-8-red)">href</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"https://www.rismosch.com/projects"</span>&gt;<span style="color:var(--pico-8-black)">Projects</span>&lt;/a&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;a <span style="color:var(--pico-8-red)">href</span><span style="color:var(--pico-8-black)">=</span><span style="color:var(--pico-8-purple)">"https://www.rismosch.com/about"</span>&gt;<span style="color:var(--pico-8-black)">About</span>&lt;/a&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&lt;/div&gt;
</p>

<p>Now since I touched on meta tags with the most important HTML meta tag, it’s probably also a good idea to also talk about the other header tags. Here’s the header tags of the website you are viewing right now:</p>

<p class="code code_block"><span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">charset</span>=<span style="color:var(--pico-8-purple)">"utf-8"</span><span style="color:var(--pico-8-cyan)">&#47;&gt;</span></p>

<p>This tag defines the encoding of the text on your site. <del>UTF-8 basically means Unicode, and for most purposes this will be good enough.</del><br><br><b>(Jan 09th, 2022) EDIT:</b> This is incorrect. For a detailed explanation check out this <a href="http://www.joelonsoftware.com/articles/Unicode.html" target="_blank" rel="noopener noreferrer">this</a> blogpost by Joel Spolsky. Read it. Now. No excuses.</p>

<p class="code code_block"><span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">name</span>=<span style="color:var(--pico-8-purple)">"viewport"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"width=device-width,initial-scale=1"</span> <span style="color:var(--pico-8-cyan)">&#47;&gt;</span></p>

<p>This is the already mentioned most important HTML header tag.</p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan)">&lt;link</span> <span style="color:var(--pico-8-red)">rel</span>=<span style="color:var(--pico-8-purple)">"apple-touch-icon"</span> <span style="color:var(--pico-8-red)">sizes</span>=<span style="color:var(--pico-8-purple)">"180x180"</span> <span style="color:var(--pico-8-red)">href</span>=<span style="color:var(--pico-8-purple)">"apple-touch-icon.png"</span><span style="color:var(--pico-8-cyan)">&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;link</span> <span style="color:var(--pico-8-red)">rel</span>=<span style="color:var(--pico-8-purple)">"icon"</span> <span style="color:var(--pico-8-red)">type</span>=<span style="color:var(--pico-8-purple)">"image/png"</span> <span style="color:var(--pico-8-red)">href</span>=<span style="color:var(--pico-8-purple)">"favicon.png"</span> <span style="color:var(--pico-8-red)">sizes</span>=<span style="color:var(--pico-8-purple)">"32x32"</span><span style="color:var(--pico-8-cyan)">&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;link</span> <span style="color:var(--pico-8-red)">rel</span>=<span style="color:var(--pico-8-purple)">"shortcut icon"</span> <span style="color:var(--pico-8-red)">href</span>=<span style="color:var(--pico-8-purple)">"favicon.ico"</span><span style="color:var(--pico-8-cyan)">&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">name</span>=<span style="color:var(--pico-8-purple)">"msapplication-TileImage"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"mstile-144x144.png"</span><span style="color:var(--pico-8-cyan)">&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">name</span>=<span style="color:var(--pico-8-purple)">"msapplication-TileColor"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"#00aba9"</span><span style="color:var(--pico-8-cyan)">&gt;</span>
</p>

<p>These basically define the small icon on the tab at the very top of your browser. Each browser follows different standards, so I implemented them multiple times to confine to all of them. I do not have every single browser in existence, but from what I have found, this should cover all mainstream browsers, like Firefox, Chrome, Edge, Safari and so on.</p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan)">&lt;link</span> <span style="color:var(--pico-8-red)">rel</span>=<span style="color:var(--pico-8-purple)">"stylesheet"</span> <span style="color:var(--pico-8-red)">href</span>=<span style="color:var(--pico-8-purple)">"css/desktop.css"</span><span style="color:var(--pico-8-cyan)">&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;link</span> <span style="color:var(--pico-8-red)">rel</span>=<span style="color:var(--pico-8-purple)">"stylesheet"</span> <span style="color:var(--pico-8-red)">href</span>=<span style="color:var(--pico-8-purple)">"css/main.css"</span><span style="color:var(--pico-8-cyan)">&gt;</span>
</p>

<p>These are basically the CSS files, that define the style of my website. They need to be included, so that they can be used by your browser.</p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan)">&lt;script</span> <span style="color:var(--pico-8-red)">src</span>=<span style="color:var(--pico-8-purple)">"3rd_party_libraries/disqusloader.js"</span><span style="color:var(--pico-8-cyan)">&gt;&lt;/script&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;script</span> <span style="color:var(--pico-8-red)">src</span>=<span style="color:var(--pico-8-purple)">"javascript/util.js"</span><span style="color:var(--pico-8-cyan)">&gt;&lt;/script&gt;</span>
</p>

<p>These are javascript files, which are used on this site. Like CSS, JavaScript can be outsourced to another file and be imported like this.</p>

<p class="code code_block"><span style="color:var(--pico-8-cyan)">&lt;title&gt;</span>CSS on Mobile, and The Most Important Header Tag<span style="color:var(--pico-8-cyan)">&lt;/title&gt;</span></p>

<p>This is the title, which is displayed on your browser tab.</p>

<p class="code code_block"><span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">name</span>=<span style="color:var(--pico-8-purple)">"robots"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"all"</span><span style="color:var(--pico-8-cyan)">&gt;</span></p>

<p>This tag is very important for search engines. It basically tells search engines what to do with your page, when they find it. <span class="code">all</span> means, the search engine will display it without a problem in their search result. Setting it to <span class="code">noindex</span> will prevent them from displaying that site. This is most helpful for redirect pages. When someone subscribes/unsubscribes to my newsletter, there are sites to handle that. With <span class="code">noindex</span>, they won’t be displayed in the search results. That is good, because a normal user probably isn’t searching for these sites and most likely doesn’t care. Thus, displaying them does probably more harm than good.</p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">property</span>=<span style="color:var(--pico-8-purple)">"og:title"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"Mobile, and The Most Important Header Tag"</span> <span style="color:var(--pico-8-cyan)">/&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">property</span>=<span style="color:var(--pico-8-purple)">"og:type"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"article"</span> <span style="color:var(--pico-8-cyan)">/&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">property</span>=<span style="color:var(--pico-8-purple)">"og:url"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"https://www.rismosch.com/article?id=16"</span> <span style="color:var(--pico-8-cyan)">/&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">property</span>=<span style="color:var(--pico-8-purple)">"og:image"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"https://www.rismosch.com/articles/16/thumbnail.jpg"</span> <span style="color:var(--pico-8-cyan)">/&gt;</span>
</p>

<p>These are data used by external sites and programs. For example, if you post a link in any social media, usually a title, thumbnail and whatever will also be displayed. These things can be set with these tags.</p>

<p class="code code_block"><span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">name</span>=<span style="color:var(--pico-8-purple)">"author"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"Simon Sutoris"</span><span style="color:var(--pico-8-cyan)">&gt;</span></p>

<p>And at last, you probably want to tell the world who actually wrote the source code of your website &#128521;</p>

<p>And that’s it. This is pretty much everything you need to know to display something in your browser. HTML, CSS and JavaScript will cover most of what you need in a website. Sure, I elided a lot of details, but that’s why search engines exist. Good resources on these things are w3schools and the Mozilla Development Network.</p>

<p class="auto-break">
	<a href="https://www.w3schools.com/" target="_blank" rel="noopener noreferrer">https://www.w3schools.com/</a><br>
	<a href="https://developer.mozilla.org/de/" target="_blank" rel="noopener noreferrer">https://developer.mozilla.org/de/</a>
</p>

<p>Read the next chapters only if you dare…</p>