<p>Bjarne Stoustroup famously said:</p>

<p style="background-color:var(--pico-8-white); border: 5px solid var(--pico-8-cyan); padding: 20px;">There are only two kinds of languages: the ones people complain about and the ones nobody uses.</p>

<p>I use Rust and I plan to continue, so I've earned the right to complain about Rust. But this won't be your average Rust vs Go blogpost. (Why are there so many of those? What the hell?) I won't be complaining about the borrow checker. Neither about traits, lifetimes, macros or compile times. These are fine. No, I will be complaining about <span class="code">Result</span>. That is right. I will be complaining about one of Rusts most beloved features.</p> 

<p>Rust prides itself on its robust and strong type system. If a function returns an object, you can be 99.99998...% sure that this object actually exists. (I am not saying 100%, because unsafe Rust exists. And who knows if some dependency you are using is doing some funky C++ shit under the hood.) If this object may not exist, it will be wrapped in an <span class="code">Option</span>. Before you can access it however, you must first check, whether this <span class="code">Option</span> actually holds your object. And if it doesn't, your object really doesn't exist and you cannot access it.</p>

<p><span class="code">Result</span> is quite similar, but a notch above <span class="code">Option</span>. Like in <span class="code">Option</span>, your object may or may not exist, but if it doesn't, it holds an error. This error (hopefully&#8482;) describes why the function couldn't return your object. And just like <span class="code">Option</span>, before you can access your object, you must first check whether it actually exists or not.</p>

<p>Everything is safe and sound.</p>

<p>But then you start to use <span class="code">Result</span>, and it turns out it's pretty clunky to use and just plain annoying.</p>

<h2>The problems emerge</h2>

<p>First of all, a lot of things can fail. Let me repeat: A LOT OF THINGS CAN FAIL. And you need to handle each and every <span class="code">Result</span> you encounter. This means, everyone gets a <span class="code">match</span>-statement, and bloated boilerplate ensues.</p>

<img src='https://www.rismosch.com/articles/my-most-hated-feature-in-rust/opera.webp' style='"display: block; margin: auto; max-width: 100%;"'>

<p>"Aha!" says the Rust evangelist, "Simply use the <span class="code">?</span> operator!" Ah yes. That removes the <span class="code">match</span>. But it introduces more complexity somewhere else: Now the client function must also return a <span class="code">Result</span>, and its error type must be the same as the calling function. This introduces problems the moment you call functions that return different errors. Now you'll have to convert errors to other errors, otherwise the compiler won't be happy.</p>

<p>"Well, just use <span class="code">From</span>!" says the evangelist. Sure, let's hide the boiler plate code somewhere else, because this obviously fixes the issue. Also, do you really want to implement <span class="code">From</span> between all the hundreds of error states, that your program can find itself in? I don't think so.</p>

<p>"I hear you, I hear you. What about <span class="code">map_err()</span> then?" I mean yes, that works. It's obvious, straightforward and understandable. But it becomes quite annoying when you have to type it hundreds of times.</p>

<p>"Ehh, <span class="code">unwrap</span>?" Cool. Why even use Rust in the first place?</p>

<p>Ultimately there is no silver bullet, and you will most likely use a combination of these solutions.</p>

<h2>It keeps on giving...</h2>

<p>Okay, usability aside, what else is there to hate about <span class="code">Result</span>? Here's something: They give little information about what actually went wrong. Ideally, you want the error to implement <span class="code">std::error::Error</span>, such that you can log it or something. You probably also want it to be an <span class="code">enum</span>, such that it gives you a human readable and computer friendly way to tell what went wrong. Boilerplate over boilerplate.</p>

<p>Look, for the API of your library, it makes sense to cover all possible failure states. But from my perspective, building a game engine, an executable, I only care whether the given operation succeeded or not. And when it fails, it should tell me where and why. <span class="code">Result</span> simply doesn't provide that information. The uncool thing about <span class="code">Result</span> is, that <i>anything</i> can be an error. The error may not implement <span class="code">std::error::Error</span>, and it may not be an <span class="code">enum</span>. The <a href="https://docs.rs/sdl2/latest/sdl2/" target="_blank" rel="noopener noreferrer">SDL2</a> crate for example made the amazingly annoying decision, to only return <span class="code">String</span>s as errors.</p>

<p>Compare that to exceptions in C#, which I am very familiar with. When an exception is thrown, you get an entire stack trace. You know which function called which, and it's usually fairly easy to pinpoint where and why an exception was thrown. Not so with Rust errors. When a "stream read failed" error bubbles up to my entry function, my first reaction is annoyance, because I have no idea where it occurred. It's not like I don't read streams in like 100 different places, no?</p>

<img src='https://www.rismosch.com/articles/my-most-hated-feature-in-rust/do_you_have_the_slightest_idea.webp' style='display: block; margin: auto; max-width: 100%;'>

<p>In Rusts defense however, when building C# as Release, most debug symbols are stripped away, and you get error messages that are undecipherable and less useful than C++ linker errors. Because you know, the linker at least <i>tries</i> to tell you what went wrong. Exceptions in C# Release builds just spit digital vomit at you.</p>

<h2>The antidote?</h2>

<p>Nonetheless, all problems I've described thus far, drove me to the point where I attempted to solve them by my own custom error type: <span class="code">RisError</span>. It stores a message, it's file and line where it was created, and the <span class="code">std::error::Error</span> that caused it. The file and line are not as useful as an entire stack trace, but it's better than nothing. I also created a few macros, that make creating and converting errors into it easier. Now, everything that fails returns an <span class="code">RisResult&lt;T&gt;</span>, which essentially is just a <span class="code">Result&lt;T, RisError&gt;</span>. Thus I can use <span class="code">?</span> everywhere, and use macros whenever something is not an <span class="code">RisError</span>.</p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">foo</span><span style="color:var(--pico-8-cyan)">(</span>i: <span style="color:var(--pico-8-washed-grey)">usize</span><span style="color:var(--pico-8-cyan)">)</span> -&gt; <span style="color:var(--pico-8-washed-grey)">RisResult</span>&lt;<span style="color:var(--pico-8-washed-grey)">Bar</span>&gt; <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">ris_util</span>::<span style="color:var(--pico-8-brown)">unroll!</span><span style="color:var(--pico-8-green)">(</span> <span style="color:var(--pico-8-green)">// this right here is the golden goose</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">im_a_dangerous_function(</span>i<span style="color:var(--pico-8-brown)">)</span>,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">"failed to call function with {}"</span>,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;i<br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">)</span>?;<br/>
<br/>
&nbsp;&nbsp;&nbsp;&nbsp;...<br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>
<br/>
<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">im_a_dangerous_function</span><span style="color:var(--pico-8-cyan)">(</span>i: <span style="color:var(--pico-8-washed-grey)">usize</span><span style="color:var(--pico-8-cyan)">)</span> -&gt; <span style="color:var(--pico-8-washed-grey)">Result</span>&lt;<span style="color:var(--pico-8-cyan)">()</span>, <span style="color:var(--pico-8-washed-grey)">SomeError</span>&gt; <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;...<br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>
</p>

<p>At this point, you may notice that this looks awfully like exceptions. Abort execution at anytime... Possibly (hopefully&#8482;) catch it somewhere up the call stack... Pretty much a <span class="code">goto</span>, but god knows where it will end up... And you may ask, why even go through the trouble and not just use exceptions in the first place? To that I say: RAII still holds. And RAII is very useful.</p>

<p>With this system, if an error occurs, I can still execute code. This allows me to gather system information, properly log failure states and ensure that the current state of the game is saved, such that no progress is lost. A <span class="code">try-catch</span> (or <span class="code">std::panic::catch_unwind</span> as the Rust people call it) doesn't work: Building as Release, exceptions are disabled and they are turned into aborts.</p>

<p>Even if I enable exceptions in Release, or are somehow able to catch aborts, working with exceptions in memory managed languages is infamously difficult. It's so difficult in fact, that people coined the term <a href="https://en.wikipedia.org/wiki/Exception_safety" target="_blank" rel="noopener noreferrer">exception safety</a>. If I keep avoiding exceptions, everything that implements <span class="code">Drop</span> will be cleaned up properly.</p>

<p>And it is now, where I have to mention, that I am absolutely annoyed by the fact that the Rust people decided to rename exceptions to "panics". When talking to non Rust people, I always have to preface: "Ackchyually, Rust has exceptions, they just call it something different".</p>

<img src="https://www.rismosch.com/articles/my-most-hated-feature-in-rust/actually.webp" style="display: block; margin: auto; max-width: 100%;">

<p>I understand why the Rust people decided to rename them. Because exceptions are rarely exceptional. If you are anticipating an error with <span class="code">try-catch</span>, is it really an "exception"? But the irony is, in my case, exceptions now really represent exceptional state! It's state that I did not account for. State that allows the engine to crash without properly shutting down. It's so backwards, and I hate it. I hate it so much.</p>

<h2>Conclusion</h2>

<p>If there will be a successor of Rust, I'd wish for an error system in between of <span class="code">Result</span> and exceptions, which goes alongside the other two. Something that generates a stack trace and unrolls like an exception, but allows RAII to properly free all held resources.</p>

<p>The compiler is smart. Couldn't the compiler generate a stack trace at compile time? I mean like generics, it could just figure out all the functions that call an error, and just place that function path as a compile constant string into the error, no? But I guess recursion must then be forbidden, because that damn halting problem exist. God fucking dammit. I curse you, halting problem!</p>

<img src="https://www.rismosch.com/articles/my-most-hated-feature-in-rust/turing.webp" style="display: block; margin: auto; max-width: 100%;">

<p>Nevertheless. As much as I hate my current solution, it is usable. And that is all I need for now....</p>

<p>Because people will be asking, below is the implementation of <span class="code">RisError</span>. I called the conversion macro <span class="code">unroll</span>, to differentiate it from <span class="code">unwrap</span>, and because <span class="code">RisError</span>s can be chained, like exceptions unrolling a stack.</p>

<p><a href="https://github.com/Rismosch/ris_engine/blob/2c94f080fe45bf0f7d3fbd4da0fbc439e76a8717/crates/ris_util/src/error.rs" target="_blank" rel="noopener noreferrer">GitHub permalink</a></p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan)">use</span> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">error</span>::<span style="color:var(--pico-8-washed-grey)">Error</span>;<br/>
<br/>
<span style="color:var(--pico-8-cyan)">pub type</span> <span style="color:var(--pico-8-washed-grey)">RisResult</span>&lt;<span style="color:var(--pico-8-washed-grey)">T</span>&gt; = <span style="color:var(--pico-8-washed-grey)">Result</span>&lt;<span style="color:var(--pico-8-washed-grey)">T</span>, <span style="color:var(--pico-8-washed-grey)">RisError</span>&gt;;<br/>
<br/>
<span style="color:var(--pico-8-cyan)">pub type</span> <span style="color:var(--pico-8-washed-grey)">SourceError</span> = <span style="color:var(--pico-8-washed-grey)">Option</span>&lt;std::sync::<span style="color:var(--pico-8-washed-grey)">Arc</span>&lt;<span style="color:var(--pico-8-cyan)">dyn</span> <span style="color:var(--pico-8-washed-grey)">Error</span> + '<span style="color:var(--pico-8-washed-grey)">static</span>&gt;&gt;;<br/>
<br/>
#<span style="color:var(--pico-8-cyan)">[</span>derive<span style="color:var(--pico-8-green)">(<span style="color:var(--pico-8-washed-grey)">Clone</span>)</span><span style="color:var(--pico-8-cyan)">]</span><br/>
<span style="color:var(--pico-8-cyan)">pub struct <span style="color:var(--pico-8-washed-grey)">RisError</span> {</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;source: <span style="color:var(--pico-8-washed-grey)">SourceError</span>,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;message: <span style="color:var(--pico-8-washed-grey)">String</span>,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;file: <span style="color:var(--pico-8-washed-grey)">String</span>,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;line: <span style="color:var(--pico-8-washed-grey)">u32</span>,<br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>
<br/>
<span style="color:var(--pico-8-cyan)">impl <span style="color:var(--pico-8-washed-grey)">RisError</span> {</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span>source: <span style="color:var(--pico-8-washed-grey)">SourceError</span>, message: <span style="color:var(--pico-8-washed-grey)">String</span>, file: <span style="color:var(--pico-8-washed-grey)">String</span>, line: <span style="color:var(--pico-8-washed-grey)">u32</span><span style="color:var(--pico-8-green)">)</span> -&gt; <span style="color:var(--pico-8-cyan)">Self</span> <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">Self</span> <span style="color:var(--pico-8-brown)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;source,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;message,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;file,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;line,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br/>
<br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">message</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-green)">)</span> -&gt; &<span style="color:var(--pico-8-washed-grey)">String</span> <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&<span style="color:var(--pico-8-cyan)">self</span>.message<br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br/>
<br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">file</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-green)">)</span> -&gt; &<span style="color:var(--pico-8-washed-grey)">String</span> <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&<span style="color:var(--pico-8-cyan)">self</span>.file<br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br/>
<br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">line</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-green)">)</span> -&gt; &<span style="color:var(--pico-8-washed-grey)">u32</span> <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&<span style="color:var(--pico-8-cyan)">self</span>.line<br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>
<br/>
<span style="color:var(--pico-8-cyan)">impl</span> <span style="color:var(--pico-8-washed-grey)">Error</span> <span style="color:var(--pico-8-pink)">for</span> <span style="color:var(--pico-8-washed-grey)">RisError</span> <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">source</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-green)">)</span> -&gt; <span style="color:var(--pico-8-washed-grey)">Option</span>&lt;&<span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-cyan)">dyn</span> <span style="color:var(--pico-8-washed-grey)">Error</span> + '<span style="color:var(--pico-8-washed-grey)">static</span><span style="color:var(--pico-8-green)">)</span>&gt; <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.source.<span style="color:var(--pico-8-brown)">as_ref()</span>.<span style="color:var(--pico-8-brown)">map(</span>|e| e.<span style="color:var(--pico-8-brown)">as_ref</span><span style="color:var(--pico-8-cyan)">()</span><span style="color:var(--pico-8-brown)">)</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>
<br/>
<span style="color:var(--pico-8-cyan)">impl</span> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Display</span> <span style="color:var(--pico-8-pink)">for</span> <span style="color:var(--pico-8-washed-grey)">RisError</span> <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">fmt</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span>, f: &<span style="color:var(--pico-8-cyan)">mut</span> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Formatter</span>&lt;'<span style="color:var(--pico-8-green)">_</span>&gt;<span style="color:var(--pico-8-green)">)</span> -&gt; <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Result</span> <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-brown)">(</span>source<span style="color:var(--pico-8-brown)">)</span> = <span style="color:var(--pico-8-cyan)">self</span>.<span style="color:var(--pico-8-brown)">source() {</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">write!</span><span style="color:var(--pico-8-cyan)">(</span>f, <span style="color:var(--pico-8-brown)">"{}<span style="color:var(--pico-8-red)">\n</span>&nbsp;&nbsp;&nbsp;&nbsp;"</span>, source<span style="color:var(--pico-8-cyan)">)</span>?;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br/>
<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">write!(</span>f, <span style="color:var(--pico-8-brown)">"<span style="color:var(--pico-8-red)">\"</span>{}<span style="color:var(--pico-8-red)">\"</span>, {}:{}"</span>, <span style="color:var(--pico-8-cyan)">self</span>.message, <span style="color:var(--pico-8-cyan)">self</span>.file, <span style="color:var(--pico-8-cyan)">self</span>.line<span style="color:var(--pico-8-brown)">)</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>
<br/>
<span style="color:var(--pico-8-cyan)">impl</span> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Debug</span> <span style="color:var(--pico-8-pink)">for</span> <span style="color:var(--pico-8-washed-grey)">RisError</span> <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">fmt</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span>, f: &<span style="color:var(--pico-8-cyan)">mut</span> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Formatter</span>&lt;'<span style="color:var(--pico-8-green)">_</span>&gt;<span style="color:var(--pico-8-green)">)</span> -&gt; <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Result</span> <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> source_string = <span style="color:var(--pico-8-pink)">match</span> &<span style="color:var(--pico-8-cyan)">self</span>.source <span style="color:var(--pico-8-brown)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span>source<span style="color:var(--pico-8-cyan)">)</span> =&gt; <span style="color:var(--pico-8-brown)">format!</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-brown)">"Some ({:?})"</span>, source<span style="color:var(--pico-8-cyan)">)</span>,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">None</span> =&gt; <span style="color:var(--pico-8-washed-grey)">String</span>::<span style="color:var(--pico-8-brown)">from</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-brown)">"None"</span><span style="color:var(--pico-8-cyan)">)</span>,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span>;<br/>
<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">write!(</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;f,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">"RisError {{inner: {}, message: {}, file: {}, line: {}}}"</span>,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;source_string, <span style="color:var(--pico-8-cyan)">self</span>.message, <span style="color:var(--pico-8-cyan)">self</span>.file, <span style="color:var(--pico-8-cyan)">self</span>.line<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">)</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>
<br/>
#<span style="color:var(--pico-8-cyan)">[</span>derive<span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">Debug</span><span style="color:var(--pico-8-green)">)</span><span style="color:var(--pico-8-cyan)">]</span><br/>
<span style="color:var(--pico-8-cyan)">pub struct</span> <span style="color:var(--pico-8-washed-grey)">OptionError</span>;<br/>
<br/>
<span style="color:var(--pico-8-cyan)">impl</span> <span style="color:var(--pico-8-washed-grey)">Error</span> <span style="color:var(--pico-8-pink)">for</span> <span style="color:var(--pico-8-washed-grey)">OptionError</span> <span style="color:var(--pico-8-cyan)">{}</span><br/>
<br/>
<span style="color:var(--pico-8-cyan)">impl</span> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Display</span> <span style="color:var(--pico-8-pink)">for</span> <span style="color:var(--pico-8-washed-grey)">OptionError</span> <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">fmt</span>(&<span style="color:var(--pico-8-cyan)">self</span>, f: &<span style="color:var(--pico-8-cyan)">mut</span> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Formatter</span>&lt;'<span style="color:var(--pico-8-green)">_</span>&gt;<span style="color:var(--pico-8-green)">)</span> -&gt; <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Result</span> <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">write!(</span>f, <span style="color:var(--pico-8-brown)">"Option was None")</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>
<br/>
#<span style="color:var(--pico-8-cyan)">[</span>macro_export<span style="color:var(--pico-8-cyan)">]</span><br/>
<span style="color:var(--pico-8-brown)">macro_rules! unroll</span> <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">(</span>$result:expr, $<span style="color:var(--pico-8-brown)">(</span>$arg:tt<span style="color:var(--pico-8-brown)">)</span>*<span style="color:var(--pico-8-green)">)</span> =&gt; <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> $result <span style="color:var(--pico-8-brown)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-cyan)">(</span>value<span style="color:var(--pico-8-cyan)">)</span> =&gt; <span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-cyan)">(</span>value<span style="color:var(--pico-8-cyan)">)</span>,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-cyan)">(</span>error<span style="color:var(--pico-8-cyan)">)</span> =&gt; <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> source: <span style="color:var(--pico-8-washed-grey)">ris_util<span style="color:var(--pico-8-black)">::</span>error<span style="color:var(--pico-8-black)">::</span>SourceError <span style="color:var(--pico-8-black)">=</span> Some<span style="color:var(--pico-8-green)">(</span>std<span style="color:var(--pico-8-black)">::</span>sync<span style="color:var(--pico-8-black)">::</span>Arc</span>::<span style="color:var(--pico-8-brown)">new(<span style="color:var(--pico-8-black)">error</span>)</span><span style="color:var(--pico-8-green)">)</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> message = <span style="color:var(--pico-8-brown)">format!</span><span style="color:var(--pico-8-green)">(</span>$<span style="color:var(--pico-8-brown)">(</span>$arg<span style="color:var(--pico-8-brown)">)</span>*<span style="color:var(--pico-8-green)">)</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> file = <span style="color:var(--pico-8-washed-grey)">String</span>::<span style="color:var(--pico-8-brown)">from</span><span style="color:var(--pico-8-green)">(<span style="color:var(--pico-8-brown)">file!()</span>)</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> line = <span style="color:var(--pico-8-brown)">line!</span><span style="color:var(--pico-8-green)">()</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> result = <span style="color:var(--pico-8-washed-grey)">ris_util</span>::<span style="color:var(--pico-8-washed-grey)">error</span>::<span style="color:var(--pico-8-washed-grey)">RisError</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span>source, message, file, line<span style="color:var(--pico-8-green)">)</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-green)">(</span>result<span style="color:var(--pico-8-green)">)</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span>;<br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>
<br/>
#<span style="color:var(--pico-8-cyan)">[</span>macro_export<span style="color:var(--pico-8-cyan)">]</span><br/>
<span style="color:var(--pico-8-brown)">macro_rules! unroll_option</span> <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">(</span>$result:expr, $<span style="color:var(--pico-8-brown)">(</span>$arg:tt<span style="color:var(--pico-8-brown)">)</span>*<span style="color:var(--pico-8-green)">)</span> =&gt; <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> $result <span style="color:var(--pico-8-brown)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span>value<span style="color:var(--pico-8-cyan)">)</span> =&gt; <span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-cyan)">(</span>value<span style="color:var(--pico-8-cyan)">)</span>,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">None</span> =&gt; <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> source: <span style="color:var(--pico-8-washed-grey)">ris_util</span>::<span style="color:var(--pico-8-washed-grey)">error</span>::<span style="color:var(--pico-8-washed-grey)">SourceError</span> = <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">sync</span>::<span style="color:var(--pico-8-washed-grey)">Arc</span>::<span style="color:var(--pico-8-brown)">new(</span><span style="color:var(--pico-8-washed-grey)">ris_util</span>::<span style="color:var(--pico-8-washed-grey)">error</span>::<span style="color:var(--pico-8-washed-grey)">OptionError</span><span style="color:var(--pico-8-brown)">)</span><span style="color:var(--pico-8-green)">)</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> message = <span style="color:var(--pico-8-brown)">format!</span><span style="color:var(--pico-8-green)">(</span>$<span style="color:var(--pico-8-brown)">(</span>$arg<span style="color:var(--pico-8-brown)">)</span>*<span style="color:var(--pico-8-green)">)</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> file = <span style="color:var(--pico-8-washed-grey)">String</span>::<span style="color:var(--pico-8-brown)">from</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-brown)">file!()</span><span style="color:var(--pico-8-green)">)</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> line = <span style="color:var(--pico-8-brown)">line!</span><span style="color:var(--pico-8-green)">()</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> result = <span style="color:var(--pico-8-washed-grey)">ris_util</span>::<span style="color:var(--pico-8-washed-grey)">error</span>::<span style="color:var(--pico-8-washed-grey)">RisError</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span>source, message, file, line<span style="color:var(--pico-8-green)">)</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-green)">(</span>result<span style="color:var(--pico-8-green)">)</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span>,<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span>;<br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>
<br/>
#<span style="color:var(--pico-8-cyan)">[</span>macro_export<span style="color:var(--pico-8-cyan)">]</span><br/>
<span style="color:var(--pico-8-brown)">macro_rules! new_err</span> <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">(</span>$<span style="color:var(--pico-8-brown)">(</span>$arg:tt<span style="color:var(--pico-8-brown)">)</span>*<span style="color:var(--pico-8-green)">)</span> =&gt; <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> source: <span style="color:var(--pico-8-washed-grey)">ris_util</span>::<span style="color:var(--pico-8-washed-grey)">error</span>::<span style="color:var(--pico-8-washed-grey)">SourceError</span> = <span style="color:var(--pico-8-washed-grey)">None</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> message = <span style="color:var(--pico-8-brown)">format!</span><span style="color:var(--pico-8-cyan)">(</span>$<span style="color:var(--pico-8-green)">(</span>$arg<span style="color:var(--pico-8-green)">)</span>*<span style="color:var(--pico-8-cyan)">)</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> file = <span style="color:var(--pico-8-washed-grey)">String</span>::<span style="color:var(--pico-8-brown)">from</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-brown)">file!</span><span style="color:var(--pico-8-green)">()</span><span style="color:var(--pico-8-cyan)">)</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> line = <span style="color:var(--pico-8-brown)">line!</span><span style="color:var(--pico-8-cyan)">()</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">ris_util</span>::<span style="color:var(--pico-8-washed-grey)">error</span>::<span style="color:var(--pico-8-washed-grey)">RisError</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-cyan)">(</span>source, message, file, line<span style="color:var(--pico-8-cyan)">)</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp<span style="color:var(--pico-8-green)">}</span>;<br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>
<br/>
#<span style="color:var(--pico-8-cyan)">[</span>macro_export<span style="color:var(--pico-8-cyan)">]</span><br/>
<span style="color:var(--pico-8-brown)">macro_rules! result_err</span> <span style="color:var(--pico-8-cyan)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">(</span>$<span style="color:var(--pico-8-brown)">(</span>$arg:tt<span style="color:var(--pico-8-brown)">)</span>*<span style="color:var(--pico-8-green)">)</span> =&gt; <span style="color:var(--pico-8-green)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">{</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> result = <span style="color:var(--pico-8-washed-grey)">ris_util</span>::<span style="color:var(--pico-8-brown)">new_err!</span><span style="color:var(--pico-8-cyan)">(</span>$<span style="color:var(--pico-8-green)">(</span>$arg<span style="color:var(--pico-8-green)">)</span>*<span style="color:var(--pico-8-cyan)">)</span>;<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-cyan)">(</span>result<span style="color:var(--pico-8-cyan)">)</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span>;<br/>
<span style="color:var(--pico-8-cyan)">}</span><br/>

</p>