<p>Okay, so we got a webserver, now what? You could be a professional and reasonable person and use a website-builder, if your host provides one, or install WphpordPress or whatever. But I wanted to make things myself, so I got cPanel, a Linux based platform for web hosting. And this is where the fun starts:</p>

<p>Did you know that your browser can display a lot of different files? Like PNG, JPEG, MP3, WAV, TXT and so much more. If you are on a desktop PC you can test that right now, make a TXT file, put in some words (e.g. “Hello World”), save and then just drag and drop it into your browser of choice. Voilà, your browser displays your txt-file. This works with the files I just mentioned. What you can also do is copy a path from your file explorer, paste it into the URL bar of your browser, and you will be able to navigate your files, but in a browser. And once you realize that, it’s easy to grasp how a webserver works. The webserver also contains a filesystem, like your file explorer, and you can simply upload files there. And once they are on there, you can view them by just entering your domain plus the correct path to that specific file.</p>

<img src="https://www.rismosch.com/articles/html-css-and-javascript/picture_1.webp" style="width:100%; display: block;" />

<p>So that’s pretty neat, but TXT and PNG files are rather dull. What we want are layouts, colors, animations, <i>extravaganza</i>, and a simple TXT file just doesn’t cut it. This is where HTML comes in. HTML stands for Hypertext Markup Language, and as its name suggest, it’s a language to describe markup, or in proper English: how your website looks.</p>

<p>HTML uses tags, which do very different things. For instance: </p>

<code>
<div style="background-color: var(--pico-8-white);" class="code code_block">
<p><span style="color:var(--pico-8-cyan);">&lt;html&gt;</span> contains the entire website</p>
<p><span style="color:var(--pico-8-cyan);">&lt;head&gt;</span> contains diverse data, like the title of the tab, or the little icon displayed next to it. Here comes usually important stuff to make the website work, but won’t be displayed <span style="color:var(--pico-8-cyan);">&lt;&#47;head&gt;</span></p>
<p><span style="color:var(--pico-8-cyan);">&lt;body&gt;</span> contains all visual stuff</p>
<p><span style="color:var(--pico-8-cyan);">&lt;h1&gt;</span> is a header <span style="color:var(--pico-8-cyan);">&lt;&#47;h1&gt;</span></p>
<p><span style="color:var(--pico-8-cyan);">&lt;p&gt;</span> is a paragraph <span style="color:var(--pico-8-cyan);">&lt;&#47;p&gt;</span></p>
<p><span style="color:var(--pico-8-cyan);">&lt;a </span><span  style="color:var(--pico-8-red);">href</span><span style="color:var(--pico-8-black);">=</span><span style="color:var(--pico-8-purple);">&#34;https://youtu.be/dQw4w9WgXcQ&#34;</span><span style="color:var(--pico-8-cyan);">&gt;</span> is a hyperlink <span style="color:var(--pico-8-cyan);">&lt;&#47;a&gt;</span></p>
<p><span style="color:var(--pico-8-cyan);">&lt;div&gt;</span> is a container, in which you can insert other tags <span  style="color:var(--pico-8-cyan);">&lt;&#47;div&gt;</span></p>
<p><span style="color:var(--pico-8-cyan);">&lt;ul&gt;</span> is a list, which contains <span style="color:var(--pico-8-cyan);">&lt;li&gt;</span> individual items <span style="color:var(--pico-8-cyan);">&lt;&#47;li&gt;</span><span style="color:var(--pico-8-cyan);">&lt;&#47;ul&gt;</span></p>
<p><span style="color:var(--pico-8-cyan);">&lt;table&gt;</span> is a table (duh)<br><span style="color:var(--pico-8-cyan);">&lt;tr&gt;</span> defines a row in a table, and<br><span style="color:var(--pico-8-cyan);">&lt;td&gt;</span> defines a single cell in a row <span style="color:var(--pico-8-cyan);">&lt;&#47;td&gt;</span><br><span style="color:var(--pico-8-cyan);">&lt;&#47;tr&gt;</span><br><span style="color:var(--pico-8-cyan);">&lt;&#47;table&gt;</span></p>
<p><span style="color:var(--pico-8-cyan);">&lt;img </span><span style="color:var(--pico-8-red);">href</span><span style="color:var(--pico-8-black);">=</span><span style="color:var(--pico-8-purple);">&#34;this is an image, and&#34;</span><span style="color:var(--pico-8-cyan);">&gt;</span></p>
<p><span style="color:var(--pico-8-cyan);">&lt;button&gt;</span> is simply a button <span style="color:var(--pico-8-cyan);">&lt;&#47;button&gt;</span></p>
<p><span style="color:var(--pico-8-cyan);">&lt;&#47;body&gt;</span></p>
<p><span style="color:var(--pico-8-cyan);">&lt;&#47;html&gt;</span></p>
</div>
</code>

<p>There are more tags than this, but these are the most essential ones. The whole layout of your website is pretty much just a creative combination of these tags. Since you already know that a webserver works like a filesystem, even if you don’t have a host, you can make an HTML file right now, throw in some HTML tags and display it in your browser.</p>

<p>Take for example the following HTML:</p>

<code>
<p class="code code_block">
&lt;!DOCTYPE html&gt;<br>
<span style="color:var(--pico-8-cyan);">&lt;html </span><span style="color:var(--pico-8-red);">lang</span>=<span style="color:var(--pico-8-purple);">&#34;en&#34;</span><span style="color:var(--pico-8-cyan);">&gt;</span><br>
<span style="color:var(--pico-8-cyan);">&lt;head&gt;</span><br>
<span style="color:var(--pico-8-cyan);">&nbsp;&nbsp;&nbsp;&nbsp;&lt;title&gt;</span>my first html<span style="color:var(--pico-8-cyan);">&lt;&#47;title&gt;</span><br>
<span style="color:var(--pico-8-cyan);">&lt;&#47;head&gt;</span><br>
<span style="color:var(--pico-8-cyan);">&lt;body&gt;</span><br>
<span style="color:var(--pico-8-cyan);">&nbsp;&nbsp;&nbsp;&nbsp;&lt;h1&gt;</span>Hello World<span style="color:var(--pico-8-cyan);">&lt;&#47;h1&gt;</span><br>
<span style="color:var(--pico-8-cyan);">&nbsp;&nbsp;&nbsp;&nbsp;&lt;p&gt;</span>this is some text<span style="color:var(--pico-8-cyan);">&lt;&#47;p&gt;</span><br>
<span style="color:var(--pico-8-cyan);">&lt;&#47;body&gt;</span><br>
<span style="color:var(--pico-8-cyan);">&lt;&#47;html&gt;</span>
</p>
</code>

<img src="https://www.rismosch.com/articles/html-css-and-javascript/picture_2.webp" style="max-width:100%; margin:auto; display: block;" />

<p>If you just tested it out for yourself, you may notice that everything is just black text on white background. BOOOORING. What we want is style, literally. Pretty much every tag inside and including <code class="code">&lt;body&gt;</code>, can be equipped with a style-parameter. In this parameter, you can define a multitude of things, like color, size, border, whether it’s left, center or right aligned, and so much more. If you want to do something on the display side, you can literally google &#34;css &lt;anything you want to display&gt;&#34; and you will find solutions, exactly what you need. For example &#34;css background color&#34;, &#34;css font&#34;, &#34;css align center&#34;. It’s so easy to gather information, that it’s almost plug and play, without much thinking required.</p>

<p>But hold on a second. Do I really need to implement style for every single tag? My website contains a homepage, and blog page, and an about page, and privacy policy, and newsletter and so on and on and on. Implementing style for every single page sucks. Well, what we need, is CSS.</p>

<p>CSS, or Cascading Style Sheets, allow you to put all your styles into one single file. With so called selectors, you can apply styles to tags.</p>

<code>
<p class="code code_block">
<span style="color:var(--pico-8-cyan);">p</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey);">color</span>: red;<br>
}<br>
</p>
</code>

<p>This selector for example, makes the text of all paragraphs red. HTML-Tags can be assigned classes and ids, so that CSS-Selectors can be more specific.</p>

<code>
<p class="code code_block">
<span style="color:var(--pico-8-cyan);">p</span>.<span style="color:var(--pico-8-red);">myClass</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey);">color</span>: blue;<br>
}<br>
</p>
</code>

<p>This selector only applies to paragraph tags, which have the class &#34;myClass&#34;.<br>Using the two CSS selectors above and the HTML below, we get something like this:</p>

<code>
<p class="code code_block">
<span style="color:var(--pico-8-cyan);">&lt;p&gt;</span>this is some text<span style="color:var(--pico-8-cyan);">&lt;&#47;p&gt;</span><br>
<span style="color:var(--pico-8-cyan);">&lt;p </span><span style="color:var(--pico-8-red);">class</span>=<span style="color:var(--pico-8-purple);">&#34;myClass&#34;</span><span style="color:var(--pico-8-cyan);">&gt;</span>this is some blue text<span style="color:var(--pico-8-cyan);">&lt;&#47;p&gt;</span><br>
</p>
</code>

<img src="https://www.rismosch.com/articles/html-css-and-javascript/picture_3.webp" style="max-width:100%; margin:auto; display: block;" />

<p>The last of the unholy trio is JavaScript, which essentially allows you to run code. With JavaScript, you can dynamically change the styling of your tags, calculate stuff and talk to other servers.</p>

<p>
	<button onclick="myFunction()">click me</button>
	&nbsp;click count: <span id="counter">0</span>
</p>

<script>
	var clickCount = 0;
	
	function myFunction(){
		clickCount++;
		var counterElement = document.getElementById("counter");
		counterElement.innerHTML = clickCount;
	}
</script>

<code>
<p class="code code_block">
<span style="color:var(--pico-8-cyan);">&lt;p&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">&lt;button </span><span style="color:var(--pico-8-red);">onclick</span>=<span style="color:var(--pico-8-purple);">&#34;myFunction()&#34;</span><span style="color:var(--pico-8-cyan);">&gt;</span>click me<span style="color:var(--pico-8-cyan);">&lt;&#47;button&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="background-color: var(--pico-8-yellow);"><i>&#38;nbsp&#59;</i></span> click count: <span style="color:var(--pico-8-cyan);">&lt;span </span><span style="color:var(--pico-8-red);">id</span>=<span style="color:var(--pico-8-purple);">&#34;counter&#34;</span><span style="color:var(--pico-8-cyan);">&gt;</span>0<span style="color:var(--pico-8-cyan);">&lt;&#47;span&gt;</span><br>
<span style="color:var(--pico-8-cyan);">&lt;&#47;p&gt;</span><br>
<br>
<span style="color:var(--pico-8-cyan);">&lt;script&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-blue);"><i>var</i></span> clickCount = <span style="color:var(--pico-8-red);">0</span>&#59;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-blue);"><i>function</i></span> myFunction(){<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;clickCount++&#59;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-blue);"><i>var</i></span> counterElement = document.getElementById(<span style="color:var(--pico-8-dark-grey);">&#34;counter&#34;</span>)&#59;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;counterElement.innerHTML = clickCount&#59;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<span style="color:var(--pico-8-cyan);">&lt;&#47;script&gt;</span><br>
</p>
</code>

<p>In this example, when you click the button, it increases the <code class="code">clickCount</code> by one, then it finds the element with the id <code class="code">counter</code> and finally replaces the HTML inside this element by the new value of <code class="code">clickCount</code>.</p>

<p>But while this works great, I myself found limited use for JavaScript. In my case it powers just 4 things:</p>

<ul>
<li>The banner animation on top</li>
<li>The “back to top” button in the lower right corner of each page</li>
<li>The dropdown menu, which you will find on my Blog and Project site and</li>
<li>A performance improvement, loading images late (more on that in the last chapter)</li>
</ul>

<p>Okay, so now that we have a solid grasp of the building-blocks which creates the stuff you see in your browser. Let’s talk about how I used them to build my website.</p>
