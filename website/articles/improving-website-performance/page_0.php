<p>Phew. That was quite a lot of information. Congratulations for making it this far &#127881;</p>

<p>Now there is only one thing left to tackle: Performance.</p>

<p>If you remember all the way back, to the start of this blogpost, one of the reasons why I wanted to program a website from scratch, opposed to just using a website builder, is being able to get the best possible performance out of it. So, did writing it from scratch actually did perform better? No. Not in the slightest.</p>

<p>I mean, what did I expect? I jumped into this, knowing absolutely nothing about web dev. While my site may look nice in the end, it was so obvious that my website wouldn't perform good.</p>

<p>BUT there's light at the end of the tunnel. You are reading this blogpost on my page. At this. Very. Moment. And I am sure you can tell that it runs rather smoothly. Furthermore, if you go to the PageSpeed Insights (a tool by google to measure your website performance) of my website, you will see that it does have a pretty good result:</p>

<img src="https://www.rismosch.com/articles/improving-website-performance/picture_1.webp" style="max-width:100%; margin:auto; display: block;" />

<p><a class="auto-break" href="https://developers.google.com/speed/pagespeed/insights/?url=rismosch.com" target="_blank" rel="noopener noreferrer">https://developers.google.com/speed/pagespeed/insights/?url=rismosch.com</a></p>

<p>The only reason why this isn't 100 points is because of that huge ass Bandcamp widget. If you take a look at a blogpost, it is full on 100 points:</p>

<img src="https://www.rismosch.com/articles/improving-website-performance/picture_2.webp" style="max-width:100%; margin:auto; display: block;" />

<p><a class="auto-break" href="https://developers.google.com/speed/pagespeed/insights/?url=http%3A%2F%2Frismosch.com%2Farticle%3Fid%3D11" target="_blank" rel="noopener noreferrer">https://developers.google.com/speed/pagespeed/insights/?url=http%3A%2F%2Frismosch.com%2Farticle%3Fid%3D11</a></p>

<p>So how was I able to get such a good performance out of it? Well to make it simple: Another reason why I made the website from scratch is, to have full control over the whole code. So to get the best possible performance, all I needed to do was to apply all the tips that PageSpeed Insight told me and presto, I have a well-functioning website.</p>

<p>But this isn't as trivial as you might expect, which is why I am writing this last chapter.</p>

<h2>Overhead</h2>

<p>The very first thing you should do, is pack up all CSS, JavaScript and PHP files into one. While this may or may not be a good organization practice, it does help improve performance. You see, when files are requested and being send, not just the data itself is transferred. Each request and file are wrapped into multiple protocols, which contain header data, for example the sender and receiver identification, what type of data is being send, status codes, how large the actual package is and so much more. While this data is small, it can add up when multiple files are being transferred.</p>

<img src="https://www.rismosch.com/articles/improving-website-performance/picture_3.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;" />

<p>If you just have one big file, this header data is only required a single time, thus reducing the overall data being transferred and increasing performance. Such data is often called overhead, and it's desirable to reduce it. On a tangent note and fun fact, a similar issue arises when rendering with your graphics cards. Always batch render and reduce render calls as much as possible &#128521;</p>

<h2>WebP</h2>

<p>Another performance gain that is relatively easy to implement, is using the WebP image format. WebP is an image format by Google, which is able to drastically reduce the size of your images. Whatever you do, don't use paid internet services to convert pngs into webps; it's actually really easy to do it yourself.</p>

<p>More details and how to use it can be found here: <a class="auto-break" href="https://developers.google.com/speed/webp" target="_blank" rel="noopener noreferrer">https://developers.google.com/speed/webp</a></p>

<p>If this isn't easy enough to you, let me walk you through it. I am going to show you how to do it on Windows. How to do it on Linux or Mac, I leave it as an exercise up to the reader.</p>

<p>First, you head to this link: <a class="auto-break" href="https://storage.googleapis.com/downloads.webmproject.org/releases/webp/index.html" target="_blank" rel="noopener noreferrer">https://storage.googleapis.com/downloads.webmproject.org/releases/webp/index.html</a></p>

<p>This is Googles download repository for precompiled WebP executables. You just pick the latest package for your operating system and the file format that you can extract, and then just download and extract it. Inside the package you will find a bunch of files:</p>

<img src="https://www.rismosch.com/articles/improving-website-performance/picture_4.webp" style="max-width:100%; margin:auto; display: block;" />

<p>The interesting folder is the “bin” folder, because this one includes the program which can translate your pictures into WebP files. Head into the bin folder and click “File” in the top left corner. From the popup you should be able to open a blue console window, called the “PowerShell”.</p>

<img src="https://www.rismosch.com/articles/improving-website-performance/picture_5.webp" style="max-width:100%; margin:auto; display: block;" />
<img src="https://www.rismosch.com/articles/improving-website-performance/picture_6.webp" style="max-width:100%; margin:auto; display: block;" />

<p>In the PowerShell, you type in following command:</p>

<code class="code code_block">./cwebp.exe "&lt;source file&gt;" -o "&lt;target file&gt;"</code>

<p><code class="code">&lt;source file&gt;</code> is the image that you want to convert, for example a png or jpg. <code class="code">&lt;target file&gt;</code> is the generated WebP file. Make sure that you include the whole file path with the file extensions. Also be careful of your target path, because the program will overwrite whatever target you choose.</p>

<p>An example could look something like this:</p>

<code class="code code_block">./cwebp.exe "C:\Users\Rismosch\Desktop\ugly_mug.jpg" -o "C:\Users\Rismosch\Desktop\ugly_mug.webp"</code>

<p>Press Enter and you should see the following thing:</p>

<img src="https://www.rismosch.com/articles/improving-website-performance/picture_7.webp" style="max-width:100%; margin:auto; display: block;" />

<p>Here's the result:</p>

<table>
<tr>
<td><img src="https://www.rismosch.com/articles/improving-website-performance/ugly_mug.jpg" style="max-width:100%; margin:auto; display: block;" /></td>
<td><img src="https://www.rismosch.com/articles/improving-website-performance/ugly_mug.webp" style="max-width:100%; margin:auto; display: block;" /></td>
</tr>
</table>

<p>Left is the original, right is the WebP</p>

<p>They look awfully similar, but the original is 288 KB and the WebP 52 KB in size. This means the WebP image is about 20% of the size of the original, which is absolutely insane. Thanks to that original picture, which is about 40% of the transmitted data of this very page, chances are that this article loads a bit more poorly than the others &#128579;</p>

<p>However, I don't recommend WebP for smaller pictures, like pixel art. I don't recommend it for 2 reasons: One, the compression ruins small pictures. And two, if the files are small enough, the added overhead of the compression will make the file itself bigger actually. For example, in the pictures below, I have the results and properties of a very small 75x42 picture. As you can clearly tell, the compression reduced the quality of the picture and ontop of that the WebP version is actually bigger. It's really a lose lose situation.</p>

<table style="width: 100%;">
<tr>
<td><img src="https://www.rismosch.com/articles/improving-website-performance/small_image.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;
" /></td>
<td><img src="https://www.rismosch.com/articles/improving-website-performance/small_image.webp" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;
" /></td>
</tr>
</table>

<img src="https://www.rismosch.com/articles/improving-website-performance/picture_8.webp" style="max-width:100%; margin:auto; display: block;" />

<p>So, just use WebP for everything but pixel art.</p>

<h2>Cached Resources</h2>

<p>One thing to massively improve performance is to cache files. Your browser is able to save files, so when you visit a website, your browser doesn't need to request them again, thus massively reducing the files that need to be transferred. In my home directory I simply put a .htaccess file with the following content:</p>

<code class="code code_block">
&lt;filesMatch ".(css|jpg|jpeg|png|gif|webp|js|ico)$"&gt;<br>
Header set Cache-Control "max-age=2419200, public"<br>
&lt;/filesMatch&gt;
</code>

<p>What this basically means, when your browser requests a css, jpg, jpeg, png, webp, js or ico file, then my webserver tells it to cache that for 2419200 seconds (4 weeks). You can see the effect of this yourself when you head to a browser you don't care (like Edge for example), delete all browser data and then navigate rismosch.com. At first it takes a second or two to load each page, but once they were loaded, if you navigate back to them, they are loaded in a split second.</p>

<h2>Loading Pictures Late</h2>

<p>The most important image on every page, is my banner on top and its animation. Because I want this animation to be seen by everyone, it is super important that the sprite sheet will be loaded first. To guarantee that, I just need to load every other image later. Consider the following HTML:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">&lt;img</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">src</span>=<span style="color:var(--pico-8-purple)">'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">id</span>=<span style="color:var(--pico-8-purple)">'late_image'</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">alt</span>=<span style="color:var(--pico-8-purple)">''</span><br>
<span style="color:var(--pico-8-cyan)">&gt;</span><br>
<br>
<span style="color:var(--pico-8-cyan)">&lt;script&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;document.getElementById(<span style="color:var(--pico-8-dark-grey)">"late_image"</span>).src = <span style="color:var(--pico-8-dark-grey)">"some_image.png"</span>;<br>
<span style="color:var(--pico-8-cyan)">&lt;/script&gt;</span><br>
</code>

<p>The source of the image is literally the entire data of a 1x1 gif. Because the data is literally stored in the HTML, your browser doesn't request an image file. But once the HTML is fully downloaded and displayed in your browser, the script executes. It finds an image with the id <code class="code">late_image</code> and replaces its source with the actual image URL. While this doesn't reduce the overall data transmitted, it makes sure that all other pictures are loaded <i>after</i> my banner spritesheet has been loaded.</p>

<p>To make the process so much easier, I've written following PHP code:</p>

<code class="code code_block" style="color:var(--pico-8-purple)">
<span style="color:var(--pico-8-black)">$img_sources</span> = [];<br>
<span style="color:var(--pico-8-black)">$img_count</span> = <span style="color:var(--pico-8-orange)">0</span>;<br>
<span style="color:var(--pico-8-cyan)">function</span> <span style="color:var(--pico-8-black)">late_image</span>(<span style="color:var(--pico-8-black)">$source</span>, <span style="color:var(--pico-8-black)">$class</span>, <span style="color:var(--pico-8-black)">$style</span>)<br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">global</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black)">$img_sources</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black)">$img_count</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">echo</span> <span style="color:var(--pico-8-dark-grey)">"<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;img<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;src='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;class='<span style="color:var(--pico-8-black)">{$class}</span>'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;style='<span style="color:var(--pico-8-black)">{$style}</span>'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;id='img<span style="color:var(--pico-8-black)">{$img_count}</span>'<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;alt=''<br>
&nbsp;&nbsp;&nbsp;&nbsp;&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;"</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black)">$img_sources</span>[] = <span style="color:var(--pico-8-black)">$source</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;++<span style="color:var(--pico-8-black)">$img_count</span>;<br>
}
</code>

<p>Where I want the image to appear in the HTML, I just call <code class="code">late_image()</code> with the correct source, class and style. It automatically creates this pseudo image tag. It also stores the URL for later. Then finally in my <code class="code">echo_foot()</code> function, I have this piece of code:</p>

<code class="code code_block" style="color:var(--pico-8-purple)">
<span style="color:var(--pico-8-cyan)">global</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black)">$img_sources</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black)">$img_count</span>;<br>
<br>
<span style="color:var(--pico-8-cyan)">echo</span> <span style="color:var(--pico-8-dark-grey)">"\n&lt;script&gt;\n"</span>;<br>
<span style="color:var(--pico-8-cyan)">for</span>(<span style="color:var(--pico-8-black)">$i</span> = <span style="color:var(--pico-8-orange)">0</span>; <span style="color:var(--pico-8-black)">$i</span> &lt; <span style="color:var(--pico-8-black)">$img_count</span>; ++<span style="color:var(--pico-8-black)">$i</span>)<br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">echo</span> <span style="color:var(--pico-8-dark-grey)">"document.getElementById(\"img<span style="color:var(--pico-8-black)">{$i}</span>\").src = \"<span style="color:var(--pico-8-black)">{$img_sources[$i]}</span>\";\n"</span>;<br>
}<br>
<span style="color:var(--pico-8-cyan)">echo</span> <span style="color:var(--pico-8-dark-grey)">"&lt;/script&gt;\n"</span>;<br>
</code>

<p>This generates the JavaScript to replace all URLs of the pseudo images.</p>

<p>With using this for every picture, except the banner, all images generated by <code class="code">late_image()</code> will be loaded late, except the banner. In the picture below, you see the network analysis of one of my blogposts. You see after the HTML and a bunch of CSS and JavaScript files are loaded, the 1x1 placeholder gif is the first thing to be "downloaded" and right after my sprite sheet. All other pngs or webps are loaded somewhere after.</p>

<img src="https://www.rismosch.com/articles/improving-website-performance/picture_9.webp" style="max-width:100%; margin:auto; display: block;" />

<h2>Third Party Services</h2>

<p>Now at the very end, take a guess which are the 3 least performant elements on my website. In no particular order, these are:</p>

<ul>
<li>The bandcamp widget on my homepage</li>
<li>Disqus on every article and</li>
<li>reCAPTCHA on my Newsletter and Contact page.</li>
</ul>

<p>What do these 3 have in common? Right, these are all 3rd party services. The bandcamp widget makes more than half of the size of my homepage, the Disqus widget also is notoriously bloated and reCAPTCHA results in my newsletter-page to have the worst PageSpeed Insight rating out of all of my pages.</p>

<p>While I am definitely not popular enough to have people comment on my posts, I like to have the option, just in case. I don't want to go through the programming and privacy/legal hell of making a comment section, thus a 3rd party service has to be used. reCAPTCHA is also essential, because as discussed in the previous chapter, I want my collected data to be protected from bots. The only non-essential 3rd party service I use, is the bandcamp widget, which I only use because it looks cool. Depending on how old this blogpost is, the bandcamp widget is most probably gone already.</p>

<p>Disqus can be optimized a bit though, by just loading it when it is visible on the screen. I've used the disqusLoader from Osvaldas Valutis: <a class="auto-break" href="https://css-tricks.com/lazy-loading-disqus-comments/" target="_blank" rel="noopener noreferrer">https://css-tricks.com/lazy-loading-disqus-comments/</a></p>

<p>Their code makes it possible to only load comments, when the user scrolls down far enough. While the amount of data is the same, it improves the browsing experience, to not block the entire website when the user isn't even looking at the comments yet. Also, I have another trick up my sleeve: I display a message which hides the Disqus widget. It warns the user that Disqus does collect their data. If you don't accept that and don't press the button, Disqus literally won't be loaded. Thus, increasing performance by a lot and also preventing Disqus to track your data. That is quite neat.</p>

<p>These 3 services that I use are such a performance drain alone, I can't think of why someone would voluntarily use even more services, like ads or literally tracking your data. I made a bad habit of checking every single blog-post made on r/programming and check their size (I'm not even reading them lol). I am absolutely dumbfounded at the size of some of these blogs! I mean dude, your blog has 500 words and 3 pictures, how the &#9829;&#9829;&#9829;&#9829; is it 2 MB in size?! But well, the best I can do is silently judge people for their bad performing websites and feel intellectually superior to them /s</p>

<h2>The End</h2>

<p>So, at last you have reached the very end of my <i>little</i> blog post. I hope you found it entertaining and learned something from it. Obviously, I skimmed over so many details, but it should've given you a good overview of all the stuff that you can, need or should do when you make a website completely from scratch.</p>

<p>Was it worth it though? Well, it definitely took me a lot longer than I expected. The repository on GitHub may tell you that I needed 2-3 months to code the website, it actually took at least twice as long, because I accidentally pushed my private reCAPTCHA key and database passwords multiple times, which is probably a bad idea when strangers on the internet have access to these things. So I had to nuke the repository multiple times.</p>

<p>I learned a lot from it though, more so than I learned in college. Pulling up a project like this in the real world is actually quite a bit different than running a website in the simulated servers of a school. I can recommend that you write your own page from scratch, because it is a good learning experience, but be aware that it takes a lot of time and work.</p>

<p>Thanks for reading and have a good day &#128540;</p>