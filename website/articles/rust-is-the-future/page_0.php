<p>Recently, I finished the first working prototype of my game object system. I can say with confidence, that up to this day, this is the most complicated thing I have ever worked on. The amount of moving parts is absolutely insane. Let me give you a brief overview.</p>

<img src="https://www.rismosch.com/articles/rust-is-the-future/screenshot.webp" style="display: block; margin: auto; max-width: 100%;">

<p>A game object is essentially a frame of reference. It has a position, a rotation and a scale. This is the easy part. The difficult part is this: A game object may have children. This makes game objects hierarchical. Now, a game object references its children, but it also references its parent, enabling the hierarchy tree to be traversed up and down. While convenient for client code, this immediately creates a problem for the implementation. If you've heard anything about Rust, then you know that circular references like this are Rusts kryptonite. Unfortunately, this is the first of many problems.</p>

<p>A game object on its own doesn't do much. To give it behavior, you can attach components to it. These can be a variety of different types. And since most of them directly affect the game object, they also need to reference it. Another circular reference!</p>

<p>It gets worse though: One of the components is the <code class="code">ScriptComponent</code>, which allows custom user code to hook into the engine. Any given script can reference whatever, including itself, its game object, its parent or children, and any other component, script, asset or currently loaded object. This is not only a dependency nightmare, but a polymorphism nightmare as well.</p>

<p>But oh, we are not done yet. My chosen solution has its own set of problems: I chose to use a single scene object, which owns and thus stores <i>all</i> loaded objects. Any references between the objects are solved via handles. These easily copyable handles are simple token objects, which store information on how to get the according object from the scene. But of course, this requires a custom reflection-like solution, such that you can resolve the type of the handle at runtime. And because Rust doesn't have reflection, I was forced to implement it! While a single handle struct might've been enough, I want it to be type safe. I want that when you are trying to get a <code class="code">MeshComponent</code> with a <code class="code">ScriptHandle</code>, the compiler refuses to compile the code. Thus, generics and <a href="https://doc.rust-lang.org/std/marker/struct.PhantomData.html" target="_blank" rel="noopener noreferrer"><code class="code">PhantomData</code></a>s everywhere. As a direct consequence, I also use one too many <a href="https://doc.rust-lang.org/std/mem/fn.transmute.html" target="_blank" rel="noopener noreferrer"><code class="code">transmute</code></a>s, sailing very close to the unsafe brink of undefined behavior.</p>

<p>And then, the scene must also be thread safe. Wrap the entire thing in a <a href="https://doc.rust-lang.org/std/sync/struct.Mutex.html" target="_blank" rel="noopener noreferrer"><code class="code">Mutex</code></a> and call it a day &#58;&#94;&#41; No, I chose interior mutability, to avoid congestion. For this, every object is stored in a custom thread safe <a href="https://doc.rust-lang.org/std/cell/struct.RefCell.html" target="_blank" rel="noopener noreferrer"><code class="code">RefCell</code></a>, with assertions removed for release builds, for maximum performance. (Premature optimization is the root of all evil you say? Never heard of it!) And the engine must hook into the scene in many different ways. All script callbacks need to be called from various locations in the game loop. And somehow the renderer must access the mesh components to render everything. To top it all off, the system must be flexible enough for extensions, such that more components can be added in the future.</p>

<p>Sheesh. That's a lot of stuff. But I've done it. And the API is squeaky clean. Here, take a look:</p>


<code class="code code_block">
<span style="color:var(--pico-8-dark-grey)">
<span style="color:var(--pico-8-washed-grey)">// create game object</span><br>
<span style="color:var(--pico-8-purple)">let</span> <span style="color:var(--pico-8-black)">game_object</span> = <span style="color:var(--pico-8-orange)">GameObjectHandle</span>::<span style="color:var(--pico-8-cyan)">new</span>(&<span style="color:var(--pico-8-black)">scene</span>)?;<br>
<br>
<span style="color:var(--pico-8-washed-grey)">// set name and position</span><br>
<span style="color:var(--pico-8-black)">game_object</span>.<span style="color:var(--pico-8-cyan)">set_name</span>(&<span style="color:var(--pico-8-black)">scene</span>, <span style="color:var(--pico-8-green)">"my awesome game object"</span>)?;<br>
<span style="color:var(--pico-8-black)">game_object</span>.<span style="color:var(--pico-8-cyan)">set_local_position</span>(&<span style="color:var(--pico-8-black)">scene</span>, <span style="color:var(--pico-8-cyan)">Vec3</span>(<span style="color:var(--pico-8-brown)">42.0</span>, <span style="color:var(--pico-8-brown)">-13.0</span>, <span style="color:var(--pico-8-brown)">123.0</span>))?;<br>
<br>
<span style="color:var(--pico-8-washed-grey)">// add script</span><br>
<span style="color:var(--pico-8-purple)">let</span> <span style="color:var(--pico-8-black)">my_script</span> = <span style="color:var(--pico-8-black)">game_object</span>.<span style="color:var(--pico-8-cyan)">add_script</span>::&lt;<span style="color:var(--pico-8-orange)">MyScript</span>&gt;(&<span style="color:var(--pico-8-black)">scene</span>)?;<br>
<br>
<span style="color:var(--pico-8-washed-grey)">// access and modify `MyScript::some_field`</span><br>
<span style="color:var(--pico-8-black)">my_script</span>.<span style="color:var(--pico-8-cyan)">script_mut</span>(&<span style="color:var(--pico-8-black)">scene</span>)?.some_field = <span style="color:var(--pico-8-green)">"hello world"</span>.<span style="color:var(--pico-8-pink)">to_string</span>();
</span>
</code>

<p>And the best part about it is, it just works. It runs, the unit tests succeed, and <a href="https://github.com/rust-lang/miri" target="_blank" rel="noopener noreferrer">miri</a> doesn't find undefined behavior. You can even double all of that, because all that runs without error in both debug and release builds! It simply works. It just does. And I really want you to appreciate that.</p>

<p>Every now and then, every couple of months, some indie dev team or company shares a hit piece on Rust, how they tried the language and had to abandon it because this, that and whatever reason. And the one reason that is always included is, that the borrow checker is stupidly difficult to work with. Having written the game objects I described above, I can sympathize with this argument. Anything sufficiently complicated is just such a pain in the ass. Rust evangelists will often tell you that <i>"you just don't understand how the borrow checker works"</i>, which is honestly such a farse. The borrow checker is easy. I believe any competent programmer will understand it in the first 10 minutes they spend with it. But the matter of fact is, its simple rules are very very hard to satisfy. And yet, if you manage to get your code to compile, it simply works. I can't be more clear than this. Pleasing the borrow checker results in less erroneous code.</p>

<img src="https://www.rismosch.com/articles/rust-is-the-future/C_Sharp_Logo_2023.svg" style="display: block; margin: auto; width: 200px; max-width: 100%;">

<p>This wasn't my first rodeo with hierarchical node systems. I've written one two years ago for my company. This version was written in C# and it is significantly simpler and more complex at the same time; it took me only a few days to get the first prototype working. But over time, problems started to creep up. I was tasked to include more features, which I were promised were absolutely necessary (spoiler alert, they weren't), resulting in messy implementations and spaghetti behind the scenes. Also some bullshit synchronization code was necessary, which needs to sync our nodes to 2 different node systems, and it must be easy to switch on and off, because of course it must. It also produces all kinds of events, whenever anything changes in the node, but this is unreliable and buggy as hell. No one fixes this, because nothing, and I mean NOTHING uses them. Yes, THE central node system, that sits in the center of our entire codebase has this huge complicated non-working deadweight attached to it.</p>

<p>These idiosyncrasies (and many more) make this C# node system such a pain to work with. But we can't fix it, because that requires time and money, which the company isn't willing to spent. So even though the initial implementation was a breeze to get working, over time the issues became apparent and years later we are still applying hacks to get around all the shortcomings. Compare that to the thing I've written in Rust: It was a major pain to get it to compile. But once it did, it just fucking works.</p>

<p>The Rust version frontloaded all the work, while the C# version revealed all the issues during the years.</p>

<img src="https://www.rismosch.com/articles/rust-is-the-future/rustacean-flat-happy.svg" style="display: block; margin: auto; width: 400px; max-width: 100%;">

<p>I don't want to dick ride Rust. As Bjarne Stoustroup famously said, I firmly believe that there are only two types of programming languages: The ones everyone complains about and the one no one uses. So here's a rundown of things I hate about Rust:</p>

<p><a href="https://www.rismosch.com/article?id=my-most-hated-feature-in-rust" target="_blank" rel="noopener noreferrer">Rust has no convenient base error type</a>; you need to download a crate or write your own. Speaking of errors, why can <a href="https://doc.rust-lang.org/std/sync/struct.PoisonError.html" target="_blank" rel="noopener noreferrer">mutexes be poisoned</a>? As far as I know, it's impossible to recover from this, so you might as well segfault. Yet, Rust forces me to handle poison errors anyway...</p>

<p>Rust is also infamous for its absolute dogshit syntax. It's already bad when you take a surface level glance at it, but have you seen the syntax for macros? At times it's worse than bash!</p>

<p>Anyway, who decided to use pipes for closure parameters?</p>

<code class="code code_block">
<span style="color:var(--pico-8-dark-grey)">
<span style="color:var(--pico-8-washed-grey)">
// this program prints:<br>
// i am a lambda<br>
// here's all the odd numbers: [2, 4, 6, 8]<br>
</span>
<span style="color:var(--pico-8-purple)">fn</span> <span style="color:var(--pico-8-cyan)">main</span>() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">// define and call lambda</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">let</span> <span style="color:var(--pico-8-black)">foo</span> = || <span style="color:var(--pico-8-pink)">println!</span>(<span style="color:var(--pico-8-green)">"i am a lambda"</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">foo</span>();<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">// iterator lambda example</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">let</span> <span style="color:var(--pico-8-black)">numbers</span> = [<span style="color:var(--pico-8-brown)">1</span>, <span style="color:var(--pico-8-brown)">2</span>, <span style="color:var(--pico-8-brown)">3</span>, <span style="color:var(--pico-8-brown)">4</span>, <span style="color:var(--pico-8-brown)">5</span>, <span style="color:var(--pico-8-brown)">6</span>, <span style="color:var(--pico-8-brown)">7</span>, <span style="color:var(--pico-8-brown)">8</span>, <span style="color:var(--pico-8-brown)">9</span>];<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">let</span> <span style="color:var(--pico-8-black)">odd_numbers</span> = <span style="color:var(--pico-8-black)">numbers</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.<span style="color:var(--pico-8-pink)">iter</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.<span style="color:var(--pico-8-pink)">filter</span>(|<span style="color:var(--pico-8-green)">x</span>| *<span style="color:var(--pico-8-green)">x</span> % <span style="color:var(--pico-8-brown)">2</span> == <span style="color:var(--pico-8-brown)">0</span>) <span style="color:var(--pico-8-washed-grey)">// yes, that's a lambda right here</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.<span style="color:var(--pico-8-pink)">collect</span>::&lt;<span style="color:var(--pico-8-orange)">Vec</span>&lt;<span style="color:var(--pico-8-orange)">_</span>&gt;&gt;();<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">println!</span>(<span style="color:var(--pico-8-green)">"here's all the odd numbers: {:?}"</span>, <span style="color:var(--pico-8-black)">odd_numbers</span>);<br>
}
</span>
</code>

<p>Also, why call it "closure"? Why not simply call it what every single other programming language calls it? Like "lambda", "anonymous function", "function pointer" or whatever?</p>

<p>Everything is so horribly nested! You have a match in a loop in a function in an impl in a mod and you are looking at 5-6 levels of indentation!</p>

<code class="code code_block">
<span style="color:var(--pico-8-dark-grey)">
<span style="color:var(--pico-8-pink)">mod</span> <span style="color:var(--pico-8-green)">MyPackage</span> {<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">struct</span> <span style="color:var(--pico-8-orange)">MyStruct</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">impl</span> <span style="color:var(--pico-8-orange)">MyStruct</span> {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">fn</span> <span style="color:var(--pico-8-cyan)">my_function</span>(&<span style="color:var(--pico-8-red)">self</span>) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">for</span> <span style="color:var(--pico-8-black)">option</span> <span style="color:var(--pico-8-purple)">in</span> <span style="color:var(--pico-8-cyan)">get_some_options</span>() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">match</span> <span style="color:var(--pico-8-black)">option</span> {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">Some</span>(<span style="color:var(--pico-8-black)">value</span>) =&gt; {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">println!</span>(<span style="color:var(--pico-8-green)">"6 levels of indentation!"</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">// some other code here</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_ =&gt; (),<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</span>
</code>

<p><code class="code">for</code> loops are nice at first glance, and they are, until you need a traditional one. For example when you conditionally want to increase <code class="code">i</code>. It gets worse in <code class="code">const</code> code, because <code class="code">for</code> isn't even supported there. The example below is legit code I had to write at one point. It's basically a desugared <code class="code">for</code> loop:</p>

<code class="code code_block">
<span style="color:var(--pico-8-dark-grey)">
<span style="color:var(--pico-8-orange)">const</span> <span style="color:var(--pico-8-brown)">HASH</span>: <span style="color:var(--pico-8-green)">u32</span> = {<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">let</span> <span style="color:var(--pico-8-orange)">mut</span> <span style="color:var(--pico-8-black)">hash</span>: <span style="color:var(--pico-8-green)">u32</span> = ...;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">let</span> <span style="color:var(--pico-8-orange)">mut</span> <span style="color:var(--pico-8-black)">i</span> = <span style="color:var(--pico-8-brown)">0</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">while</span> <span style="color:var(--pico-8-black)">i</span> < <span style="color:var(--pico-8-cyan)">get_length</span>() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black)">hash</span> = ...;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black)">i</span> += <span style="color:var(--pico-8-brown)">1</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black)">hash</span><br>
};
</span>
</code>

<p>Amazing.</p>

<p>Or why do <code class="code">if</code> statements avoid brackets <code class="code">()</code>? Sure, it's less to type and one can get used to it. But the problem is, that no other programming language does this! Because of it, I can never build any muscle memory in both Rust and non-Rust languages. Sure, it isn't mandatory and I <i>can</i> use brackets in Rust if I want to, but <a href="https://doc.rust-lang.org/stable/clippy/usage.html" target="_blank" rel="noopener noreferrer">clippy</a> flags them as bad style. Do I look like someone who configures a fucking linting tool?</p>

<p>And then there is the turbofish. First, really stupid name. Second, you can't convince me that one absolutely needs the two double colons <code class="code">::</code> to denote a generic type. To use my game objects as an example:</p>

<code class="code code_block">
<span style="color:var(--pico-8-dark-grey)">
<span style="color:var(--pico-8-washed-grey)">// correct</span><br>
<span style="color:var(--pico-8-purple)">let</span> <span style="color:var(--pico-8-black)">my_script</span> = <span style="color:var(--pico-8-black)">game_object</span>.<span style="color:var(--pico-8-cyan)">add_script</span>::&lt;<span style="color:var(--pico-8-orange)">MyScript</span>&gt;(&<span style="color:var(--pico-8-black)">scene</span>)?;<br>
<br>
<span style="color:var(--pico-8-washed-grey)">// syntax error</span><br>
<span style="color:var(--pico-8-purple)">let</span> <span style="color:var(--pico-8-black)">my_script</span> = <span style="color:var(--pico-8-black)">game_object</span>.<span style="color:var(--pico-8-cyan)">add_script</span>&lt;<span style="color:var(--pico-8-orange)">MyScript</span>&gt;(&<span style="color:var(--pico-8-black)">scene</span>)?;<br>
</span>
</code>

<p>And I can go on and on. I hate Rust. But I continue to use it, because well, the sunken cost fallacy, but moreover because the borrow checker just leads to correct code.</p>

<img src="https://www.rismosch.com/articles/rust-is-the-future/ISO_C++_Logo.svg" style="display: block; margin: auto; width: 200px; max-width: 100%;">

<p>While my professional job is mostly C#, I do get to touch C++ from time to time. And every time I do so, I feel like a gun is pointed to my head. If I make a single incorrect assumption, BOOM, undefined behavior. In the best case it blows up in your face and crashes the program. In the worst case it quietly keeps working, until a customer submits a bug. And then you can't replicate it! I've been there! It sucks! It sucks so much! It's one of the worst bugs you can encounter! You can say skill issue, sure, but not even the greatest C++ wizard can avoid undefined behavior.</p>

<img src="https://www.rismosch.com/articles/rust-is-the-future/cpp.webp" style="display: block; margin: auto; max-width: 100%;">

<p><i>"Modern C++ is safe"</i> &#129299; says the C++ enthusiast, unwilling to acknowledge that they are in an abusive relationship. Sure, the newest C++ standard includes safety features, if you find a compiler that actually supports it. But when C++98 is the standard in your enterprise codebase, you're out of luck. Here's the kicker: Even if you use very modern C++, safety is still opt-in. You purposefully need to use the new safety features. Safety is not the default. No one is stops you from doing pointer arithmetic.</p>

<p>In Rust, safety is opt-out. Safety must purposefully be turned off by using <code class="code">unsafe</code>. <i>"If you are using unsafe you are writing Rust incorrectly"</i> &#128545; says the degenerate Rust evangelist. I do it all the time. It's no big deal. It's easy and I don't care. My point is this though: Safety is enabled by default. You can tell that a piece of code may cause undefined behavior, because it is clearly labeled by its <code class="code">unsafe</code> block. So, when you do a code review, you can point to it and yell at the junior why the fuck they thought it was necessary to include <code class="code">unsafe</code> code.</p>
    
<p>In C++ I write undefined behavior by accident. In Rust I write undefined behavior on purpose.</p>

<p><i>"Rust isn't 100% safe"</i> &#129299; says the educated Rust hater, pointing at bugs in the compiler. I mean, do I even have to argue about that? <i>Theoretically</i> Rust is safe (according to their own definition, which may be different than what you are thinking), but our world tends to be messy and not ideal. However, did you know that seatbelts aren't 100% safe either? It turns out, it's already a huge improvement when a new solution is safer than the current alternative, even if it isn't perfect.</p>

<p><i>"But Rust is not OOP"</i> &#128553; says the clean code obsessed, code smell avoiding programmer. I swear, the paradigm of the programming language doesn't matter, like at all. If C people can do polymorphism, so can you. Yet, OOP bros are acting like everything that isn't OOP is the devil incarnate. I constantly run into videos and articles about "bad code" and "how to avoid it". And they always spew nonsense like "avoid switch statements" and "you shouldn't use bools". What's next, "don't use variables"?!</p>

<p>You think I exaggerate? I am sorry to disappoint you, but these are talking points from messiah Uncle Bob himself:</p>

<ul>
<li><a href="https://youtu.be/2IotTzClOAQ" target="_blank" rel="noopener noreferrer">https://youtu.be/2IotTzClOAQ</a></li>
<li><a href="https://youtu.be/2Q9GRPxqCAk" target="_blank" rel="noopener noreferrer">https://youtu.be/2Q9GRPxqCAk</a></li>
</ul>

<p>I swear to God, if I hear one more time that a class should have no more than 100 lines of code I WILL FUCKING EXPLODE! I consistently write code that exceeds 200 lines per file, I'd say around 500 is my average. But I do so because I avoid oneliners, use many, MANY variables, and I like to keep related code in one place. I do not want to spread my code over multiple files, so that future me doesn't need a huge mental map and 100 active tabs to debug it. But OOP bros just cannot shut up about how many lines of code a single method should have. I am seriously starting to question: Do you guys even write software? Or do you just glue frameworks together?</p>

<p>Look, I take great issue with OOP bros trying to sell me "clean code". I am sorry, but I have seen the absolute disaster that these "programmers" produce. I have witnessed widget-based GUI applications written in Python, that despite having less than 7 buttons, somehow struggle to run at 5 FPS. And you are telling me, I am supposed to listen to their disgusting "code smells" and how I am supposed to structure my code?! Come on person, get a grip!</p>

<!--<video loop="true" autoplay="autoplay" muted="true" style="max-width:100%; display: block; margin: auto;" loading='lazy'>
<source src="squidward.mp4" type="video/mp4">
</video>-->

<img src="https://www.rismosch.com/articles/rust-is-the-future/programmers.jpg" style="display: block; margin: auto; max-width: 100%;">

<p>Anyway, this post has absolutely gone off the rails, hasn't it? What was the title of this post? Ah yes, Rust is the future. While writing, ranting seemed natural for this kind of topic. Kudos if you've actually read my incoherent ramblings. Let's come to an end and wrap things up.</p>

<p>Rust has one killer feature: The borrow checker. All arguments that you throw against Rust are immediately invalidated, because your preferred programming language simply does not have a borrow checker. I am going to make the prediction that C++ is going to lose relevance over the next decades. It won't die, simply because of its historical significance and wide adoption. But C++ will fade, for sure. C will never die, because it has transcended into the true and only low-level language (above assembly). C has become the de facto standard for interopability between any two programming languages. Higher level languages like Python, JavaScript and Java will stay around, because they are easy to program in. Garbage collected languages are inherently safe without any hassle. But C++ in particular will lose significance. If it isn't Rust, then another programming language with a borrow checker will take the place of C++. But as it stands today, Rust is the only programming language which is mature enough to compete with C++.</p>