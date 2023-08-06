<p>In my previous blogpost, I mentioned how I considered throwing in the towel, and build my game engine in Rust instead of C++. Two months later, how are things going?</p>

<p>Honestly, things are going fairly well. I've read "The Rust Programming Language" by Steve Klabnik and Carol Nichols, as well as "Rust for Rustaceans" by Jon Gjengset. The former is a good crash course on the basics of Rust, but too beginner friendly in my taste. I still recommend it, if you know nothing about Rust and want to learn it. However, the latter is absolutely amazing and it's exactly what I want in a programming book. But it definitely covers more advanced topics, and you need some programming experience to better understand the topics in the book.</p>

<?php late_image(get_source("pic_1.webp"),"","display: block; margin: auto; width: 400px; max-width: 100%;");?>

<p>C++ is powerful, but compared to Rust it feels clunky and, as I mentioned in my last blogpost, full of garbage that you aren't supposed to use. Every book about C++ that I have read so far introduced design patterns and concepts, that you as a programmer must uphold. Otherwise, you get unhandled exceptions and undefined behavior. However, Rust implements these concepts into the compiler and simply doesn't compile your program if you decide to break them. In Rust, these concepts are rules, not suggestions.</p>

<p>Programming in Rust feels very different than any other language that I have experienced so far. Usually, programming goes like this: You write some code and run it through your compiler or interpreter immediately afterwards. Then, while your program is running, you check if it works and then go back to programming. More often than not, some exception is thrown, or some UI element is displayed incorrectly, or something just behaves weirdly. You go back to programming, and fix any issues that you have encountered.</p>

<p>It's a different story in Rust however. Rust doesn't compile your code, if you don't please the burrow checker. And it can be rather frustrating to appease it, as designing data structures around the burrow checker can be very tough. Nevertheless, once your code does compile, it simply works. I can't tell you how blissfully satisfying running Rust code is. The compiler is telling you exactly what not to do. All the hardship you experience comes before you even run your code. With all other programming languages however, you experience misery while running your program, and you are left alone when things break.</p>

<p>Okay, besides falling in love with Rust, what did I actually program? For starters, here's the things that my engine can already do:</p>

<ul>
<li>Basic main loop</li>
<li>Framebuffer, to store data over multiple frames</li>
<li>Random Number Generator based on <a href="https://www.pcg-random.org/index.html" target="_blank" rel="noopener noreferrer">PCG</a></li>
<li>Simple SDL2 window</li>
<li>SDL2 event pump integration</li>
<li>Implementation of Mouse, Keyboard and Gamepad controls</li>
<li>REBINDABLE controls</li>
</ul>

<p>I will definitely make a blogpost about the last point, because I've came up with a solution, that is both easy to use, yet stupidly powerful, and I haven't seen anyone talk about it, so I guess it's somewhat original! Also, the whole repository is (in my opinion) very clean and I've written unit tests wherever I could, with custom testing utility. It's amazing, if I do say so myself &#128522;</p>

<?php late_image(get_source("pic_2.png"),"","display: block; margin: auto; width: 729px; max-width: 100%;");?>

<h2>What's coming up next?</h2>

<p>I am taking a break from the engine. The next feature planned would be a multithreaded job system. That's complicated though, and I have a book in my pipeline which I want to read before I tackle multithreading. After that, some logging utility and finally some rendering. Let's see if it's going to be OpenGL or Vulkan; either way I am definitely going to break my neck with it.</p>

<p>Also, there are two other reasons why I want to take a break from the engine.</p>

<p>One: There are some issues on my websites which have been bugging me for quite a while now. I want to take some time to actually fix them.</p>

<p>And two: I want to make music again. After all, I describe myself as a programmer <b>and a music producer</b>. It's been quite a while since I actually made a song. Since then, I've been accumulating music gear. One year ago, I purchased the Arturia Polybrute. It's an amazing instrument and I love everything about it, but I have yet to make a song with it. The time flies whenever I decide to play with it. It's so easy to get lost in the soundscape….</p>

<?php late_image(get_source("pic_3.webp"),"","display: block; margin: auto; width: 100%;");?>

<p>Also, this just arrived in my mail: The Dirtywave M8 Tracker! I've heard amazing things about it and I literally waited half a year for it to arrive, and last week it finally came! As of writing this post, I have absolutely no idea how to handle it. It definitely isn't plug and play and it seems to have a learning curve to it. Still, I am so stoked right now &#128522;</p>

<?php late_image(get_source("pic_4.webp"),"","display: block; margin: auto; width: 400px; max-width: 100%;");?>

<p>To sum up everything: 2 months ago, I felt absolutely miserable and I wasn't happy how my project is going. Today, everything is awesome<a href="https://youtu.be/StTqXEQ2l-Y" target="_blank" rel="noopener noreferrer">!</a> I feel very good about the game engine and its progress, and I am very very satisfied with Rust. Though, I decided to take a break from the engine, to fix things on the website and to make music &#127925;</p>