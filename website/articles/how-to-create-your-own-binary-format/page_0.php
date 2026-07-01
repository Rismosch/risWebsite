<p>So you want to serialize data. Chances are you considered some formats already, like JSON, YAML, XAML or something of that sort. Well, what about binary? Don't be scared. A custom binary format isn't that difficult to implement! Sure, there is an initial learning bump, but at the end of this post, you can do this easily yourself. And in terms of size and speed, a custom written serializer easily beats a general one.</p>

<p>I wanted to write this post for quite some time now, but I never wrote my thoughts down. Then my coworker specifically asked me about serialization. So you can thank him for the existence of this post. Thank you Mark :)</p>

<h2>The problem</h2>

<p>What are we even trying to do? What even is a binary format? Simply put, a binary format is an array of bytes. Nothing more, nothing less. Classes, structs and objects cannot be saved as files. Neither can they be sent over a network. Why that is, is complicated and out of scope of this blog post. (They keyword here is ABI.) Regardless, what is important is that a byte array CAN be saved or sent. So our challenge will be to find a solution, that translates our object into a byte array and back.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/problem.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<p>Shouldn't be too difficult.</p>

<p></p>

<h2>What's a stream?</h2>

<p>Before we start with any kind of serialization, we should be talking about streams. We aren't going to modify the byte array directly. That would be cumbersome and difficult. Instead, we are going to use a container known as a "stream". It will make our life so much easier.</p>

<p>A stream consists of two things: Data and a cursor. The data can be anything: A file, a websocket, a port, whatever. But most importantly for us, the data can be a byte array. The cursor is simply an integer, and it represents the current position in the data. Think of it like the head of a <a href="https://en.wikipedia.org/wiki/Turing_machine" target="_blank" rel="noopener noreferrer">Turing machine</a>. Or the stylus of a <a href="https://en.wikipedia.org/wiki/Phonograph" target="_blank" rel="noopener noreferrer">record player</a>. I'll use "cursor" and "position" interchangeable from here on. They are pretty much the same concept.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/stream.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<p>I'll be using the visualization above for all further examples. The boxes represent the data, the byte array, where each cell is a single byte. The red arrow is the cursor, the current position of the stream.</p>

<p>A stream comes with 3 functions: <code class="code">Seek</code>, <code class="code">Read</code> and <code class="code">Write</code>. What these do and how they work in detail, we'll see in a moment. Streams come in many different flavors. Some are <i>read-only</i>, meaning they don't implement <code class="code">Write</code>. Some are <i>write-only</i>, meaning they don't implement <code class="code">Read</code>. For our purposes, we will be using a "MemoryStream". It stores bytes, and it implements all three methods.</p>

<p>Because we will be using a stream in pretty much every example, I want you to understand how a stream works. So we are going to implement one from scratch. It will be barebones, naive and straight forward, but for our use case it will suffice.</p>

<p>All code examples will be in C#, because C# is a language, which Mark can read. If you dear reader have never worked with C# before, then that is fine. We won't be using complicated syntax, so I hope you can follow along.</p>

<p>Let's start by defining our stream:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">private byte</span>[] <span style="color: var(--pico-8-green)">_data</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">private int</span> <span style="color: var(--pico-8-green)">_position</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_data</span> = <span style="color: var(--pico-8-brown)">Array</span>.<span style="color: var(--pico-8-purple)">Empty</span>&lt;<span style="color: var(--pico-8-cyan)">byte</span>&gt;();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_position</span> = <span style="color: var(--pico-8-pink)">0</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_data</span> = <span style="color: var(--pico-8-green)">value</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_position</span> = <span style="color: var(--pico-8-pink)">0</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-purple)">ToArray</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">_data</span>.<span style="color: var(--pico-8-purple)">ToArray</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// todo:</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// seek</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// read</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// write</span><br>
}
</code>

<p>I prefixed the stream with "Ris", because I am weird and most of the types in my public code are prefixed with the first syllable of my name. It also helps to distinguish my types from others. Anyway, as discussed, the stream consists of two parts: The data and the cursor or position. I also implemented two utility methods, that allow us to create a stream from existing bytes, or to retrieve the bytes once we are done modifying the stream.</p>

<p>We have three more methods to implement. Let's do this!</p>

<h2>Seek</h2>

<p><code class="code">Seek</code> does two things: It modifies the position, and then it returns the position <i>after</i> it has been modified. Modifying the position works by adding an offset to one of three locations in the stream:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public enum</span> <span style="color: var(--pico-8-brown)">SeekFrom</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">Begin</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">Current</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">End</span>,<br>
}
</code>

<p>With this, we can implement the <code class="code">Seek</code> method:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public int</span> <span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-cyan)">int</span> <span style="color: var(--pico-8-green)">offset</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span> <span style="color: var(--pico-8-green)">seekFrom</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">switch</span> (<span style="color: var(--pico-8-green)">seekFrom</span>)</br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">case</span> <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Begin</span>:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_position</span> = <span style="color: var(--pico-8-green)">offset</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">break</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">case</span> <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Current</span>:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_position</span> += <span style="color: var(--pico-8-green)">offset</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">break</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">case</span> <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">End</span>:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_position</span> = <span style="color: var(--pico-8-green)">_data</span>.<span style="color: var(--pico-8-green)">Length</span> + <span style="color: var(--pico-8-green)">offset</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">break</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">default</span>:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">throw new</span> <span style="color: var(--pico-8-brown)">ArgumentOutOfRangeException</span>(<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">nameof</span>(<span style="color: var(--pico-8-green)">seekFrom</span>),<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">seekFrom</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">null</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// clamp the position, in case it falls out of range</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (<span style="color: var(--pico-8-green)">_position</span> &lt; <span style="color: var(--pico-8-pink)">0</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_position</span> = <span style="color: var(--pico-8-pink)">0</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (<span style="color: var(--pico-8-green)">_position</span> &gt; <span style="color: var(--pico-8-green)">_data</span>.<span style="color: var(--pico-8-green)">Length</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_position</span> = <span style="color: var(--pico-8-green)">_data</span>.<span style="color: var(--pico-8-green)">Length</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">_position</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>There are a few things to note here. First, notice how the <code class="code">switch</code> treats <code class="code">SeekFrom</code> differently. When applying an offset to <code class="code">SeekFrom.Begin</code>, the offset is pretty much where you want the position to be. When using <code class="code">SeekFrom.Current</code>, we are simply adding the offset to the current position. When using <code class="code">SeekFrom.End</code>, we are adding the offset from the end of our currently held data.</p>

<p>Using positive and negative offsets, we can walk back and forth in the stream. Due to the clamp however, negative offsets on <code class="code">SeekFrom.Begin</code> and positive offsets on <code class="code">SeekFrom.End</code> have no effect.</p>

<p>Why are we clamping anyway? Well, the allowed range for the position is <code class="code">0</code> to <code class="code">_data.Length</code>. The bounds are inclusive. Everything outside that range is invalid. We want the stream to be valid in all cases, and thus we need to prevent the position from falling out of the valid range.</p>

<p>Hold on a sec, doesn't <code class="code">_position == _data.Count</code> mean that our cursor is now pointing outside of our array?</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/stream_end_1.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<p>Yes. In this case it does indeed point outside the array. But this is fully intentional. It allows us to get the current length of the stream by calling <code class="code">Seek(0, SeekFrom.End)</code>. It also makes reading and writing straight forward. And it also means that when <code class="code">_data</code> is empty, <code class="code">_position</code> being <code class="code">0</code> is a valid state.</p>

<p>Clamping is one way to keep the stream valid. Another way would be to throw exceptions, that's what <a href="https://learn.microsoft.com/en-us/dotnet/api/system.io.memorystream?view=net-9.0" target="_blank" rel="noopener noreferrer"><code class="code">System.IO.MemoryStream</code></a> does. But I don't like exceptions, so I clamp.</p>

<p>Most commonly, seeking is used like this:</p>

<ul>
<li><code class="code">Seek(0, SeekFrom.End)</code> to get to the end to the stream and/or to get its length,</li>
<li><code class="code">Seek(position, SeekFrom.Begin)</code> to set the position directly, and</li>
<li><code class="code">Seek(0, SeekFrom.Current)</code> to get the current position of the cursor</li>
</ul>

<h2>Read</h2>

<p>Now let's see how to read from the stream. As a parameter, <code class="code">Read</code> takes an <code class="code">int</code> that specifies how many bytes should be read. It then reads that many bytes, advances the position, and returns the bytes.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/stream_read.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<p>Here's the implementation:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-purple)">Read</span>(<span style="color: var(--pico-8-cyan)">int</span> <span style="color: var(--pico-8-green)">count</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// clamp to ensure enough bytes to read</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesLeftToRead</span> = <span style="color: var(--pico-8-green)">_data</span>.<span style="color: var(--pico-8-green)">Length</span> - <span style="color: var(--pico-8-green)">_position</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (<span style="color: var(--pico-8-green)">count</span> > <span style="color: var(--pico-8-green)">bytesLeftToRead</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// not enough bytes</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// only read whats left</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">count</span> = <span style="color: var(--pico-8-green)">bytesLeftToRead</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// read the bytes, by copying them to a new array</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytes</span> = <span style="color: var(--pico-8-cyan)">new byte</span>[<span style="color: var(--pico-8-green)">count</span>];<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">Array</span>.<span style="color: var(--pico-8-purple)">Copy</span>(<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_data</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// source</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_position</span>,&nbsp;<span style="color: var(--pico-8-dark-grey)">// source index</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">bytes</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// target</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-pink)">0</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// target index</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">count</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// count</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// advance the cursor</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_position</span> += <span style="color: var(--pico-8-green)">count</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">bytes</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>First, we are clamping <code class="code">count</code>. Then we copy the requested number of bytes into a new array. At last, but not least, we update the position and return the read bytes.</p>

<p>Copy operations can be a bit intimidating for new programmers. I hope the example below clarifies things a bit better:</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/copy_1.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<p>Let's talk about the clamp for a moment. It is there specifically to prevent client code to read more bytes than are actually left in the stream. Reading more bytes than are present is an invalid operation, and we must decide what to do in such a case. Clamping like this provides valid default behavior, but requires client code to validate, if as many bytes were read as expected. Another viable approach would be to throw an exception. The exception comes with the benefit, that no operation will take place. My implementation above will always allocate an array and copy into it, regardless if the operation is sensical or not. But clamping has the benefit that you can read all remaining bytes by calling <code class="code">Read(int.Max)</code> on our stream.</p>

<p>You may have also noticed that I am returning an array. You may find that common stream implementations pass an array as a parameter instead, expecting the read method to fill it. This is done to prevent unnecessary allocations. My implementation will allocate an array for every single read operation that you will make. But the tradeoff is that client code will be significantly simpler. If you want, as homework, you can try to rewrite the read method such that it will take an array as a parameter. Its signature should look like this:</p>

<code class="code code_block"><span style="color: var(--pico-8-cyan)">public int</span> <span style="color: var(--pico-8-purple)">Read</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">buffer</span>, <span style="color: var(--pico-8-cyan)">int</span> <span style="color: var(--pico-8-green)">count</span>)</code>

<p>The returned <code class="code">int</code> is to indicate how many bytes were actually read. Whether you opt for a clamped- or exception-based error behavior (to prevent too many bytes being read), even a successful read may not fill your entire array. So make sure to return an <code class="code">int</code> to notify the client code.</p>

<p>Before we continue, I again want to stress that <code class="code">_position == _data.Count</code> is a valid state, in which our stream can find itself in. Assume for a moment that it wasn't, how would you determine if there are bytes left to read?</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/stream_end_2.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<p>If we allow <code class="code">_position == _data.Count</code> to be valid, then things become very easy:</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/stream_end_3.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<h2>Write</h2>

<p>Two of three methods implemented. Now let's look at <code class="code">Write</code>. <code class="code">Write</code> puts bytes into the stream at the position of the cursor, and then advances the cursor.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/stream_write.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<p>Notice that writing can both grow the stream, as well as overwrite existing bytes.</p>

<p>I want to stress one last time that <code class="code">_position == _data.Count</code> is completely valid. If the cursor is at the very last position of the stream, then being outside the array allows us to append to the stream without overwriting anything:</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/stream_write_append.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<p>This also allows appending onto an empty stream, without compensating for any edge cases when implementing it. <code class="code">_position == _data.Count</code> is indeed a very useful hack and very useful behavior a stream can have.</p>

<p>Enough yapping, let's see the implementation:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public void</span> <span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ensure the capacity is big enough</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">requiredCapacity</span> = <span style="color: var(--pico-8-green)">_position</span> + <span style="color: var(--pico-8-green)">value</span>.<span style="color: var(--pico-8-green)">Length</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (<span style="color: var(--pico-8-green)">_data</span>.<span style="color: var(--pico-8-green)">Length</span> &lt; <span style="color: var(--pico-8-green)">requiredCapacity</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// capacity is not big enough</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// create an array that is big enough</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// copy the old into the new one</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">newDataArray</span> = <span style="color: var(--pico-8-cyan)">new byte</span>[<span style="color: var(--pico-8-green)">requiredCapacity</span>];<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">Array</span>.<span style="color: var(--pico-8-purple)">Copy</span>(<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_data</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// source</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">newDataArray</span>,&nbsp;<span style="color: var(--pico-8-dark-grey)">// target</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_data</span>.<span style="color: var(--pico-8-green)">Length</span>&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// count</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_data</span> = <span style="color: var(--pico-8-green)">newDataArray</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// write by copying the values into the array</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">Array</span>.<span style="color: var(--pico-8-purple)">Copy</span>(<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">value</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// source</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-pink)">0</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// source index</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_data</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// target</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_position</span>,&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// target index</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">value</span>.<span style="color: var(--pico-8-green)">Length</span>&nbsp;<span style="color: var(--pico-8-dark-grey)">// count</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// advance the cursor</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">_position</span> += <span style="color: var(--pico-8-green)">value</span>.<span style="color: var(--pico-8-green)">Length</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>First, we check whether our stream has enough bytes left to hold the data. If not, we must grow it. This is done by allocating a new array, which is big enough to hold all data, and then copying the old array into the new one. Then, we can copy the actual value into our data array.</p>

<p>Again, copy operations may intimidate the new programmer, so let's visualize them too. Here's an example for resizing the array:</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/copy_2.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<p>And here is an example for copying the value into the array:</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/copy_3.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<p>And that's it. This is everything that we are going need. A full implementation can be found here: <a href="https://github.com/Rismosch/RisSerialization/blob/main/RisSerialization/RisMemoryStream.cs" target="_blank" rel="noopener noreferrer">LINK</a></p>

<p>You may have noticed that I did not implement the <a href="https://learn.microsoft.com/en-us/dotnet/api/system.io.stream?view=net-9.0" target="_blank" rel="noopener noreferrer">base class</a>. This is on purpose. I am targeting the lowest denominator of readers, and <code class="code">System.IO.Stream</code> is a tad too complicated for my taste. Also, we don't need the entire thing for the following chapters. The methods we have are enough for our use case.</p>

<p>If you are feeling confident in your ability, I leave the implementation of <code class="code">System.IO.Stream</code> as homework for you. Implementing the base class comes with the obvious benefit, that you can use all utility methods for streams on your stream as well.</p>

<p>I am going to use <code class="code">RisMemoryStream</code> for the following chapters. You are allowed to use your stream from your homework. You are also allowed to use <a href="https://learn.microsoft.com/en-us/dotnet/api/system.io.memorystream?view=net-9.0" target="_blank" rel="noopener noreferrer"><code class="code">System.IO.MemoryStream</code></a> from the standard library, which has a much more sophisticated implementation, with all the bells and whistles.</p>

<h2>Serialization: Baby Steps</h2>

<p>Let me introduce you to a new static class:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public static class</span> <span style="color: var(--pico-8-brown)">RisIO</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static int</span> <span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">int</span> <span style="color: var(--pico-8-green)">offset</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span> <span style="color: var(--pico-8-green)">seekFrom</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">offset</span>, <span style="color: var(--pico-8-green)">seekFrom</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static byte</span>[] <span style="color: var(--pico-8-purple)">ReadUnchecked</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">int</span> <span style="color: var(--pico-8-green)">count</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">Read</span>(<span style="color: var(--pico-8-green)">count</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static byte</span>[] <span style="color: var(--pico-8-purple)">Read</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">int</span> <span style="color: var(--pico-8-green)">count</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-purple)">ReadUnchecked</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">count</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Length</span> != <span style="color: var(--pico-8-green)">count</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">throw new</span> <span style="color: var(--pico-8-brown)">ArgumentOutOfRangeException</span>(<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">nameof</span>(<span style="color: var(--pico-8-green)">count</span>),<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">count</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">null</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static void</span> <span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>You may be asking: Is it really necessary to wrap these methods? No, it isn't necessary. But I like to have a uniform API. By wrapping them, regardless if we are reading/writing raw bytes or values like <code class="code">int</code>s and <code class="code">float</code>s, the calls will always look very similar: <code class="code">RisIO.ReadX(stream)</code> or <code class="code">RisIO.WriteX(stream, value)</code>. (This will be especially true when we are modifying them a bit later.)</p>

<p>Notice however, that I am throwing an exception in the read method. That is because <code class="code">Read</code> represents a checked operation, meaning it must produce an error if things don't go as expected. Unfortunately, error handling is difficult. And C# is not Rust. So despite me not being the biggest fan of exceptions, I will be falling back to them when the situation calls for it. <code class="code">ReadUnchecked</code> does no checks, and thus client code is at the mercy of whatever error handling the underlying stream uses. <code class="code">Read</code> is a safe alternative. Also notice that <code class="code">Read</code> does not catch the exception of the underlying stream. That is intentional, because exception is exception, regardless of who threw it. In any case, the client is notified on an error, which is good enough for us.</p>

<p>For you C++ nerds out there, yes, this isn't strongly exception safe. If you know what I mean by this, then you are clearly above my target audience. I leave it as a challenge for the reader to argument why it doesn't fulfill the strong exception safety guarantee.</p>

<p>I think strong exception safety isn't of importance here, as I consider any exception a failed deserialization. I like my programs to burn and crash on a (truly) unexpected error. We will generate streams on the fly, and none should outlive a serialization operation. If this bothers you, you can write strong exception safe implementations for this and all the coming methods. I will stick with simpler implementations, even if they are less rigorous.</p>

<h2>Integers</h2>

<p>Now let's serialize some data! Took us quite a while.</p>

<p>Since classes and structs are made from smaller types, let's see how these smaller types are serialized. Starting with the humble integer. On most platforms, including C#, an <code class="code">int</code> is made up of 32 bits or 4 bytes. So all we need to do is to retrieve these 4 bytes and then put them into our read/write methods. That should be easy. Right?</p>

<p><b>Jump scare: Endianness</b></p>

<p>Immediately, even before we serialize our first thing, we are met with an obstacle. Unfortunately, this won't be our last &#128531;</p>

<p>So what is endianness? Let me answer that question with an example and another question: The <code class="code">int</code> <code class="code">730797131</code> is comprised of the bytes <code class="code">43</code>, <code class="code">143</code>, <code class="code">20</code> and <code class="code">75</code>. Now, in which order should we store it?</p>

<code class="code code_block">
43, 143, 20, 75
</code>

<p>Or?</p>

<code class="code code_block">
75, 20, 143, 43
</code>

<p>As it turns out, both approaches are valid and reasonable. So now we are left to choose. Of course we could also scramble them, but that assumes that we know in which order the unscrambled bytes should be. You see, this is a CPU problem, and endianness determines in what byte order different CPUs represent a number. We must solve this. Chances are, that our binary format is stored as a file or sent as a package over the internet, read by a different computer that has a different endianness.</p>

<p>Since there are two orders, there are two types of endianness: Big-endian and little-endian. To figure out which is which, we need to look at the first and the last byte of our number. Sticking with the example <code class="code">730797131</code>, then <code class="code">43</code> is the "most significant byte" (MSB) and <code class="code">75</code> is the "least significant byte" (LSB). <code class="code">43</code> is the MSB, because it has huge effect on the number. On the other hand, <code class="code">75</code> is the LSB, because it has little effect. Look at what happens to <code class="code">730797131</code> when you add <code class="code">1</code> to either the MSB or LSB:</p>

<code class="code code_block">
43, 143, 20, 75 =&gt; 730797131<br>
43, 143, 20, <span style="color: var(--pico-8-red)"><u>76</u></span> =&gt; 73079713<span style="color: var(--pico-8-red)"><u>2</u></span><br>
<span style="color: var(--pico-8-red)"><u>44</u></span>, 143, 20, 75 =&gt; 7<span style="color: var(--pico-8-red)"><u>47574347</u></span>
</code>

<p>Adding <code class="code">1</code> to the LSB only adds <code class="code">1</code> to the whole number. Adding <code class="code">1</code> to the MSB however adds <code class="code">2^24</code>, which equals <code class="code">16777216</code>!</p>

<p>Here's why that's important: If you store the MSB first, you are using big-endian. If you are storing the LSB first, then you are using little-endian. A good way to remember this: If you are storing the big byte first, you are using big-endian.</p>

<p style="text-align: center">: ^)</p>

<p>What you choose is up to you. But whatever you do, stay consistent. Most modern mainstream CPUs use little-endian. But there exist exceptions, notably embedded systems. Older hardware might also use big-endian.</p>

<p>Let's see how this actually looks like in code:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public static class</span> <span style="color: var(--pico-8-brown)">RisIO</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static int</span> <span style="color: var(--pico-8-purple)">ReadInt</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytes</span> = <span style="color: var(--pico-8-purple)">Read</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">4</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">FixEndianness</span>(<span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-brown)">BitConverter</span>.<span style="color: var(--pico-8-purple)">ToInt32</span>(<span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static void</span> <span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">int</span> <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytes</span> = <span style="color: var(--pico-8-brown)">BitConverter</span>.<span style="color: var(--pico-8-purple)">GetBytes</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">FixEndianness</span>(<span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static void</span> <span style="color: var(--pico-8-purple)">FixEndianness</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// we are using little-endian</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// if our cpu is not little-endian, flip the bytes</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (!<span style="color: var(--pico-8-brown)">BitConverter</span>.<span style="color: var(--pico-8-green)">IsLittleEndian</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">Array</span>.<span style="color: var(--pico-8-purple)">Reverse</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>Whenever you are dealing with raw bytes, you must keep endianness in mind. If you have ever read a spec before, which deals with raw bytes, then you know that they always mention what endianness their format uses. Well, at least every spec I've ever read mentioned it. Also, you can directly toggle endianness in most hex editors.</p>

<h2>Hex editors</h2>

<p>Speaking of hex editors, when writing your own binary format, you should probably start using one. To debug your binary format, you can simply write your serialized bytes into a file and then open that file with the hex editor of your choice. On windows I am using <a href="https://mh-nexus.de/en/hxd/" target="_blank" rel="noopener noreferrer">HxD</a>, and on Linux I am using <a href="https://apps.kde.org/okteta/" target="_blank" rel="noopener noreferrer">Okteta</a>.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/hxd_1.png" style="display: block; margin: auto; max-width: 100%;">

<p>Some hex editors have more features, some less, but most are split into 3 views. On the left you can view the actual bytes in hexadecimal. Also there are the offsets displayed in hexadecimal as well, to ease navigating large files and locating specific bytes at certain positions.</p>

<p>Right next to it, there is a decoded text interpretation. Most of the time, this is some form of ASCII. This decoding is mostly useful when your format uses text. If you are not storing text, the decoded text will be scrambled nonsense. But often the text can help you to spot patterns.</p>

<p>I assume that's why little-endian is practically the standard for modern CPUs. Small integers have their MSB set to <code class="code">0</code>. If you use little-endian, then it's easy to spot at a glance, where that integer begins. But that is pure conjecture on my part.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/hxd_2.png" style="display: block; margin: auto; max-width: 100%;">

<p>On the right side of the hex editor, you will usually find an inspector, which provides gazillions of interpretations of the currently selected byte. It may read multiple bytes to come to each interpretation. After all, the hex editor doesn't know how your binary format works, so it displays all the possibilities. You can toggle endianness and see how the values in the inspector change.</p>

<h2>Floats and other numbers</h2>

<p>Endianness was quite the big jump scare. But once we are aware of it, <code class="code">float</code>s are very straight forward:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public static class</span> <span style="color: var(--pico-8-brown)">RisIO</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static float</span> <span style="color: var(--pico-8-purple)">ReadFloat</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytes</span> = <span style="color: var(--pico-8-purple)">Read</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">4</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">FixEndianness</span>(<span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-brown)">BitConverter</span>.<span style="color: var(--pico-8-purple)">ToSingle</span>(<span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static void</span> <span style="color: var(--pico-8-purple)">WriteFloat</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">float</span> <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytes</span> = <span style="color: var(--pico-8-brown)">BitConverter</span>.<span style="color: var(--pico-8-purple)">GetBytes</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">FixEndianness</span>(<span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>As you can see, the implementation is very similar to that of the integer. I won't list the implementations for all the other numeric types here. If you need to read/write single <code class="code">byte</code>s, <code class="code">char</code>s, <code class="code">double</code>s, <code class="code">short</code>s or <code class="code">long</code>s, whether they are signed or not, I am sure you can figure out the correct implementation on your own.</p>

<p>Or you can ask an LLM to do that for you. I heard they are the hot craze right now. Not that I would endorse such steaming shit...</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/neil.webp" style="display: block; margin: auto; max-width: 100%;">


<h2>Enums</h2>

<p>As far as I am aware, most programming languages use integers for enums internally, including C#. A notable exception is Rust, where an enum is more akin to a union type, which we will come back to later.</p>

<p>An enum value is just a tag to the compiler, which improves readability for you the programmer. But behind the scenes it's just a number. As such, writing an enum is as easy as converting it to an <code class="code">int</code> and writing that:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public static class</span> <span style="color: var(--pico-8-brown)">RisIO</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static void</span> <span style="color: var(--pico-8-purple)">WriteEnum</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-brown)">Enum</span> <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">i</span> = <span style="color: var(--pico-8-brown)">Convert</span>.<span style="color: var(--pico-8-purple)">ToInt32</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">i</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>Since we are using <code class="code">WriteInt</code> to write the number, all endianness problems are already taken care of &lt;3</p>

<p>Reading is done like so:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public static class</span> <span style="color: var(--pico-8-brown)">RisIO</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">T</span> <span style="color: var(--pico-8-purple)">ReadEnum</span>&lt;<span style="color: var(--pico-8-brown)">T</span>&gt;(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>) <span style="color: var(--pico-8-cyan)">where</span> <span style="color: var(--pico-8-brown)">T</span> : <span style="color: var(--pico-8-brown)">Enum</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">i</span> = <span style="color: var(--pico-8-purple)">ReadInt</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (!<span style="color: var(--pico-8-brown)">Enum</span>.<span style="color: var(--pico-8-purple)">IsDefined</span>(<span style="color: var(--pico-8-cyan)">typeof</span>(<span style="color: var(--pico-8-brown)">T</span>), <span style="color: var(--pico-8-green)">i</span>))<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">throw new</span> <span style="color: var(--pico-8-brown)">FormatException</span>(<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-washed-grey)">$"<span style="color: var(--pico-8-black)">{<span style="color: var(--pico-8-green)">i</span>}</span> is not defined for enum <span style="color: var(--pico-8-black)">{<span style="color: var(--pico-8-cyan)">typeof</span>(<span style="color: var(--pico-8-brown)">T</span>)}</span>"</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = (<span style="color: var(--pico-8-brown)">T</span>)<span style="color: var(--pico-8-brown)">Enum</span>.<span style="color: var(--pico-8-purple)">ToObject</span>(<span style="color: var(--pico-8-cyan)">typeof</span>(<span style="color: var(--pico-8-brown)">T</span>), <span style="color: var(--pico-8-green)">i</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>The code above uses the most advanced syntax of all the examples in this post, because it makes use of generics. If you are not familiar with generics, think of them like this: This method accepts <i>any</i> enum. Now instead of using the specific enum type, <code class="code">T</code> represents every enum possible. No matter what enum you read from this method, the method will resolve it correctly.</p>

<p>Don't worry, we'll walk through this example: First, we read an <code class="code">int</code>. Then we check if the enum is actually defined for that <code class="code">int</code>. If it isn't, we throw an exception. If it is defined, we cast it to the enum and return it.</p>

<p>Instead of throwing an exception, you could return a default value. But I prefer the exception here, because a default value would obfuscate invalid data.</p>

<p>It should be noted, that this approach only works for enums that have unique values. Consider the following enum:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public enum</span> <span style="color: var(--pico-8-brown)">Fruit</span> {<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">Apple</span> = <span style="color: var(--pico-8-pink)">1</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">Banana</span> = <span style="color: var(--pico-8-pink)">2</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">Orange</span> = <span style="color: var(--pico-8-pink)">3</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">MyFavorite</span> = <span style="color: var(--pico-8-pink)">3</span>,<br>
}</code>

<p>In this case, when writing <code class="code">Fruit.MyFavorite</code> into the stream, you might read <code class="code">Fruite.Orange</code> out of it. This may be desirable, or it may not. In case it is not desirable, you need to read and write <code class="code">string</code>s instead of <code class="code">int</code>s. How to serialize a <code class="code">string</code> we'll see later.</p>

<h2>Booleans</h2>

<p>Now let's take a look at booleans. A <code class="code">bool</code> can only have 2 values! <code class="code">true</code> and <code class="code">false</code>! How hard can that be? This will be easy!</p>

<p><b>Narrator: <i>It will not be easy.</i></b></p>

<p>New programmers are often surprised to hear that booleans are stored as 1 or more bytes in memory, instead of 1 bit. If you didn't know this, now you know :) The reason why is, because the CPU cannot access single bits. A CPU operates in "words" which are 1 or multiple bytes long.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/the_more_you_know.jpg" style="display: block; margin: auto; max-width: 100%;">

<p>With our serialization, we face a similar reality: With our current API, we can only serialize bytes, not bits. So whenever we want to save a <code class="code">bool</code>, we waste at least 7 bits of space.</p>

<p>This may seem wasteful, but from my experience, this is a problem which you usually don't want to solve. As it stands, our serialization is "byte aligned", which means all chunks of information start and end with a byte. Once you introduce variable bitlength into your serialization, you face alignment issues. And I cannot stress enough that this is a major pain in the ass, and it really isn't worth solving.</p>

<p>To more effectively use all the space, there are 2 valid approaches: The first one is compression. We will talk about that at the very end of this post. The second is bitfields.</p>

<p>A byte consists of 8 bits. Since a <code class="code">bool</code> only requires 1 bit, it directly follows, that a single <code class="code">byte</code> can hold 8 <code class="code">bool</code>s. An <code class="code">int</code> has 32 bits, so it can hold 32 <code class="code">bool</code>s! A bitfield is a technique to store <code class="code">bools</code> in an integer. Using bit shift operations and masks you can set, clear and read specific bits and treat them as <code class="code">bool</code>s.</p>

<p>However, bitfields are tricky. And the syntax seems arcane for the uninitiated.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/operators_meme.webp" style="display: block; margin: auto; max-width: 100%;">

<p>I don't recommend a new programmer to deal with bitfields, as they are very easy to misuse. They are so tricky in fact, that Rust, the programming language with possibly the strictest type system of all available languages currently available, doesn't have a type for them. Instead, Rust users recommend external libraries that do bitfields for you.</p>

<p>You can read up on bitfields if you want to, but I will consider any further discussion to be out of scope for this post. If you want to read up on yourself, here's an <a href="https://en.wikipedia.org/wiki/Bit_field" target="_blank" rel="noopener noreferrer">Wikipedia article</a> and <a href="https://youtu.be/ZRNO-ewsNcQ" target="_blank" rel="noopener noreferrer">this amazing video by Creel</a>.</p>

<p>My recommendation is, even if it's wasteful, to store an entire byte for a single bool. So we will continue with that.</p>

<p>Ok, but oh no. We are not done yet with problems: What even is a <code class="code">bool</code>? Like, how does the CPU actually represent that in memory? The unfortunate answer is, that bools do not exist. Yes, you read that correctly. As far as the CPU is concerned, only words exist. At best, some special registers that store flags could be considered boolean, but they are used for a completely different use case, compared to the <code class="code">bool</code>s in your code. And unless you are writing assembly, you can't actually manipulate such flag registers.</p>

<p>Different programming languages compile <code class="code">bool</code>s differently. If you've ever tried to write interoperability between two programming languages, you might have run into this. But it gets worse: Not even the SAME programming language can agree on how a <code class="code">bool</code> should be represented. Different C compilers may produce different assembly. Newer C standards only define what behavior a <code class="code">bool</code> should have, not how it should be represented in memory.</p>

<p>Wild, isn't it?</p>

<video loop="true" autoplay="autoplay" muted="true" style="max-width:100%; display: block; margin: auto;" loading='lazy'>
<source src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/mind_blown.mp4" type="video/mp4">
</video>

<p>This absolute disaster has direct consequences for our serialization: Even if our programming language provides a method to convert a <code class="code">bool</code> to bytes and vice versa, we cannot rely on it. There is no guarantee that a different computer or a different program can understand our <code class="code">bool</code>. As such we must take the serialization of <code class="code">bool</code>s into our own hands. Luckily, this isn't difficult, as a <code class="code">bool</code> can hold only 2 different values.</p>

<p>There are many different strategies we could use here. We could use a bitfield, but as discussed, not recommended. Instead, I suggest to write a <code class="code">1</code> when the <code class="code">bool</code> is <code class="code">true</code>, and <code class="code">0</code> when the <code class="code">bool</code> is <code class="code">false</code>. When reading, <code class="code">1</code> means <code class="code">true</code>, <code class="code">0</code> means <code class="code">false</code>, and every other value leads to an exception.</p>

<p>If you want, you can use a different strategy. But whatever you do, make sure that it is consistent over all computers and programs that use your binary format.</p>

<p>Here's my implementation:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public static class</span> <span style="color: var(--pico-8-brown)">RisIO</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static bool</span> <span style="color: var(--pico-8-purple)">ReadBool</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytes</span> = <span style="color: var(--pico-8-purple)">Read</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">1</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">b</span> = <span style="color: var(--pico-8-green)">bytes</span>[<span style="color: var(--pico-8-pink)">0</span>];<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">switch</span> (<span style="color: var(--pico-8-green)">b</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">case</span> <span style="color: var(--pico-8-pink)">1</span>:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return true</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">case</span> <span style="color: var(--pico-8-pink)">0</span>:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return false</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">default</span>:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">throw new</span> <span style="color: var(--pico-8-brown)">FormatException</span>(<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-washed-grey)">$"<span style="color: var(--pico-8-black)">{<span style="color: var(--pico-8-green)">b</span>}</span> is not a valid bool"</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static void</span> <span style="color: var(--pico-8-purple)">WriteBool</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">bool</span> <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (<span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">new byte</span>[] { <span style="color: var(--pico-8-pink)">1</span> });<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">else</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">new byte</span>[] { <span style="color: var(--pico-8-pink)">0</span> });<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<h2>Classes and structs</h2>

<p>We have come quite far. So much so, that we can start to serialize our classes and structs! Classes are ultimately just a list of smaller types (we are not going into what methods are or how they work, for that you can look up vtables). So, reading and writing this "field list" in a predefined order is enough to serialize our class. This is very easy actually. Here's an example:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">MyAwesomeClass</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public int</span> <span style="color: var(--pico-8-green)">MyNumber</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public float</span> <span style="color: var(--pico-8-green)">MyOtherNumber</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public bool</span> <span style="color: var(--pico-8-green)">MightNotBeTrue</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">MyNumber</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteFloat</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">MyOtherNumber</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteBool</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">MightNotBeTrue</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">ToArray</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">MyAwesomeClass</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">MyAwesomeClass</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">MyNumber</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadInt</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">MyOtherNumber</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadFloat</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">MightNotBeTrue</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadBool</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>Very straight forward. Very simple. This is what I meant by "creating your own binary format is fast and easy". Sure, there was a somewhat steep learning curve. But once you understand these ideas, and have a collection of utilities like <code class="code">RisIO</code>, you can whip out a new binary format for everything in no time.</p>

<p>What do you say? I did not cover arrays? Strings? Pffff whaat? Who needs those? I certainly don't need arrays. That's for certain. No! Don't look at the scrollbar! We are practically done...</p>

<h2>Sized vs unsized types</h2>

<p>Welp. We are not, in fact, done. As it turns out there is quite a bit more to cover. But it is true though: Serializing classes and structs is as easy as calling the according IO methods in a predefined order. Everything from here on out covers special types that require specific strategies. It all boils down to sized vs unsized types.</p>

<p>Up until this point, we have only seen sized types. By that I mean, we know how big they are, how much memory they take up. A serialized <code class="code">int</code> has always a size of 4 bytes. A serialized <code class="code">bool</code> is always 1 byte. Last jump scare, I promise: There exist types with unknown sizes.</p>

<p>Let's take the array for example. How many bytes does an array take up? Think about it for a second. The answer is: It depends. If there are no elements in the array, then hurray, we don't need any memory actually. But if there are, idk, 10 items in it, then we require at least 10 times the size of whatever type we are storing.</p>

<p>This is what I mean by an unsized type. An unsized type is a type, that we don't know the size up front. To be specific: A type is unsized, if it's size cannot be determined at compile time.</p>

<p>Once our program runs and the array is actually loaded into memory, it does take up space. This means it does actually have a size, but it must be determined at runtime. To properly serialize an array, we need to store the number of elements, and then serialize each element individually.</p>

<p>Here's how that might look like:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">MyMostFavoriteBools</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">List</span>&lt;<span style="color: var(--pico-8-cyan)">bool</span>&gt; <span style="color: var(--pico-8-green)">Bools</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">Bools</span>.<span style="color: var(--pico-8-green)">Count</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">foreach</span> (<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">b</span> <span style="color: var(--pico-8-cyan)">in</span> <span style="color: var(--pico-8-green)">Bools</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteBool</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">b</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">ToArray</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">MyMostFavoriteBools</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">MyMostFavoriteBools</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Bools</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">List</span>&lt;<span style="color: var(--pico-8-cyan)">bool</span>&gt;();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">count</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-brown)">ReadInt</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">for</span> (<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">i</span> = <span style="color: var(--pico-8-pink)">0</span>; <span style="color: var(--pico-8-green)">i</span> &lt; <span style="color: var(--pico-8-green)">count</span>; ++<span style="color: var(--pico-8-green)">i</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">b</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadBool</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Bools</span>.<span style="color: var(--pico-8-purple)">Add</span>(<span style="color: var(--pico-8-green)">b</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}<br>
</code>

<p>You may notice that these are methods directly on the class itself, instead of <code class="code">RisIO</code>. Well, at the time of writing this post, I have not found a satisfying method signature that covers all the ergonomics that I want in a standalone IO method. As such, I typically serialize arrays like the example above, even if it may be verbose at times.</p>

<p>It should be noted, that often times, classes and structs are sized types as well. If you know the sizes of the fields, then you know the size of the entire class. For example, a class storing an <code class="code">int</code>, a <code class="code">float</code> and a <code class="code">bool</code> has always the size of 4 + 4 + 1 = 9 bytes. As such, it too can easily be stored in an array.</p>

<p>However, once a class stores even a single unsized type, the class will be unsized itself. More often than not, this is fine. Elements in an array are stored back-to-back. Reading one element after the other gives correct results, even if the array element is unsized. But it should be noted that you must deserialize ALL array elements in such a case. You cannot, for example, skip every second element via <code class="code">Seek(n, SeekFrom.Current)</code>, because you don't know the size of an element. If you absolutely need this behavior, then you can store the size first, and serialize the class second. The reader should read the size first, and then decide if it wants to skip or not.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/unsized_array.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<h2>Strings</h2>

<p>Now we can finally serialize strings. Took us long enough.</p>

<p>I often see new programmers, who work with modern programming languages, taking strings for granted. Some even consider them to be a primitive type!</p>

<p><i>"Come on man. How hard can Strings possibly be?"</i></p>

<p>As it turns out, strings are very difficult. If you've ever worked with strings in C before, you know how non-primitive they actually are. Rust has hundreds of different string types! Well, I am exaggerating, but Rust indeed has a lot of different types of strings.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/string_meme.webp" style="display: block; margin: auto; max-width: 100%;">

<p>But even if we stay in the realm of C#, strings might still be tricky. Plain text isn't as plain text as you might think. "Plain Text" by Dylan Beattie is a good talk I recommend: <a href="https://youtu.be/gd5uJ7Nlvvo" target="_blank" rel="noopener noreferrer">LINK</a>. Another good resource is "The Absolute Minimum Every Software Developer Absolutely, Positively Must Know About Unicode and Character Sets (No Excuses!)" by Joel Spolsky: <a href="https://www.joelonsoftware.com/2003/10/08/the-absolute-minimum-every-software-developer-absolutely-positively-must-know-about-unicode-and-character-sets-no-excuses/" target="_blank" rel="noopener noreferrer">LINK</a>. The rabbit hole goes quite deep, and strings are anything but simple.</p>

<p>But there are very good news for us: Practically, strings are a solved problem.</p>

<p>Thanks to Unicode, serialization of strings is actually very straight forward (as long as we stick with Unicode). To keep things brief, and to summarize Joels post, a string is a sequence of characters. Each character is defined by a codepoint. Codepoints are arbitrarily defined, and must be "encoded" before they can be stored in memory. As long as we know what encoding a given string uses, we can read and understand it.</p>

<p>There are many different encodings, but as long as we use the same encoding in the serialize and deserialize methods, we will be fine. I choose UTF-8, because it is ubiquitous. You don't need to know how UTF-8 works, but you can read up on it if you want. Here's a <a href="https://en.wikipedia.org/wiki/UTF-8" target="_blank" rel="noopener noreferrer">Wikipedia article</a>, here's the <a href="https://www.rfc-editor.org/rfc/rfc3629" target="_blank" rel="noopener noreferrer">official RFC</a> and here's <a href="https://youtu.be/MijmeoH9LT4" target="_blank" rel="noopener noreferrer">Tom Scott on Computerphile</a>.</p>

<p>Since strings are a solved problem, serialization is stupidly easy for us:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public static class</span> <span style="color: var(--pico-8-brown)">RisIO</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static string</span> <span style="color: var(--pico-8-purple)">ReadString</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">length</span> = <span style="color: var(--pico-8-purple)">ReadInt</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytes</span> = <span style="color: var(--pico-8-purple)">Read</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">length</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-brown)">Encoding</span>.<span style="color: var(--pico-8-green)">UTF8</span>.<span style="color: var(--pico-8-purple)">GetString</span>(<span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static void</span> <span style="color: var(--pico-8-purple)">WriteString</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">string</span> <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytes</span> = <span style="color: var(--pico-8-brown)">Encoding</span>.<span style="color: var(--pico-8-green)">UTF8</span>.<span style="color: var(--pico-8-purple)">GetBytes</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytes</span>.<span style="color: var(--pico-8-green)">Length</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>Instead of serializing characters, we first encode our string into UTF-8 bytes and then store that as a dynamic array. We are storing the length first and then the bytes. To deserialize a string we read the length first and read that many bytes, which are then interpreted as a UTF-8 string and converted into a C# string. Easy :)</p>

<p>In case it isn't obvious, strings are unsized types, because they can have arbitrary lengths.</p>

<h2>Dynamic types</h2>

<p>Another kind of unsized types are dynamic types, i.e. types that are only known during runtime. Notable examples include polymorphism, by which I mean multiple classes that implement the same base class. This is very common in OOP languages. In other languages union structs fall into the same problem, like Rust enums for example. Here's some code to work with:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">Fruit</span> { <span style="color: var(--pico-8-dark-grey)">/* ... */</span> }<br>
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">Apple</span> : <span style="color: var(--pico-8-brown)">Fruit</span> { <span style="color: var(--pico-8-dark-grey)">/* ... */</span> }<br>
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">Banana</span> : <span style="color: var(--pico-8-brown)">Fruit</span> { <span style="color: var(--pico-8-dark-grey)">/* ... */</span> }<br>
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">Orange</span> : <span style="color: var(--pico-8-brown)">Fruit</span> { <span style="color: var(--pico-8-dark-grey)">/* ... */</span> }<br>
<br>
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">FruitBasket</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">List</span>&lt;<span style="color: var(--pico-8-brown)">Fruit</span>&gt; <span style="color: var(--pico-8-green)">Fruits</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
}
</code>

<p>Here, <code class="code">Fruit</code> is an unsized type, because any derivation may serialize to a different length. An <code class="code">Apple</code> might serialize differently than an <code class="code">Orange</code> for example. But because of polymorphism, both can be stored in the same List of <code class="code">FruitBasket.Fruits</code>. So we don't know at compile time how much memory a <code class="code">FruitBasket</code> would take up.</p>

<p>To solve this, we store the type as an enum and serialize based on that. This works:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public enum</span> <span style="color: var(--pico-8-brown)">FruitKind</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">Apple</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">Banana</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">Orange</span>,<br>
}<br>
<br>
<span style="color: var(--pico-8-cyan)">public abstract class</span> <span style="color: var(--pico-8-brown)">Fruit</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public abstract</span> <span style="color: var(--pico-8-brown)">FruitKind</span> <span style="color: var(--pico-8-purple)">GetKind</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public abstract byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>();<br>
}<br>
<br>
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">Apple</span> : <span style="color: var(--pico-8-brown)">Fruit</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public override</span> <span style="color: var(--pico-8-brown)">FruitKind</span> <span style="color: var(--pico-8-purple)">GetKind</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-brown)">FruitKind</span>.<span style="color: var(--pico-8-green)">Apple</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public override byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">Apple</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}<br>
<br>
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">Banana</span> : <span style="color: var(--pico-8-brown)">Fruit</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public override</span> <span style="color: var(--pico-8-brown)">FruitKind</span> <span style="color: var(--pico-8-purple)">GetKind</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-brown)">FruitKind</span>.<span style="color: var(--pico-8-green)">Banana</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public override byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">Banana</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}<br>
<br>
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">Orange</span> : <span style="color: var(--pico-8-brown)">Fruit</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public override</span> <span style="color: var(--pico-8-brown)">FruitKind</span> <span style="color: var(--pico-8-purple)">GetKind</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-brown)">FruitKind</span>.<span style="color: var(--pico-8-green)">Orange</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public override byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">Orange</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}<br>
<br>
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">FruitBasket</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">List</span>&lt;<span style="color: var(--pico-8-brown)">Fruit</span>&gt; <span style="color: var(--pico-8-green)">Fruits</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">Fruits</span>.<span style="color: var(--pico-8-green)">Count</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">foreach</span> (<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">fruit</span> <span style="color: var(--pico-8-cyan)">in</span> <span style="color: var(--pico-8-green)">Fruits</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">kind</span> = <span style="color: var(--pico-8-green)">fruit</span>.<span style="color: var(--pico-8-purple)">GetKind</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteEnum</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">kind</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytes</span> = <span style="color: var(--pico-8-green)">fruit</span>.<span style="color: var(--pico-8-purple)">Serialize</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">ToArray</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FruitBasket</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">FruitBasket</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Fruits</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">List</span>&lt;<span style="color: var(--pico-8-brown)">Fruit</span>&gt;();<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">count</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadInt</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">for</span> (<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">i</span> = <span style="color: var(--pico-8-pink)">0</span>; <span style="color: var(--pico-8-green)">i</span> < <span style="color: var(--pico-8-green)">count</span>; ++<span style="color: var(--pico-8-green)">i</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">kind</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadEnum</span>&lt;<span style="color: var(--pico-8-brown)">FruitKind</span>&gt;(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">Fruit</span> <span style="color: var(--pico-8-green)">fruit</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">switch</span> (<span style="color: var(--pico-8-green)">kind</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">case</span> <span style="color: var(--pico-8-brown)">FruitKind</span>.<span style="color: var(--pico-8-green)">Apple</span>:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">fruit</span> = <span style="color: var(--pico-8-brown)">Apple</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">break</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">case</span> <span style="color: var(--pico-8-brown)">FruitKind</span>.<span style="color: var(--pico-8-green)">Banana</span>:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">fruit</span> = <span style="color: var(--pico-8-brown)">Banana</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">break</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">case</span> <span style="color: var(--pico-8-brown)">FruitKind</span>.<span style="color: var(--pico-8-green)">Orange</span>:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">fruit</span> = <span style="color: var(--pico-8-brown)">Orange</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">break</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">default</span>:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">throw new</span> <span style="color: var(--pico-8-brown)">ArgumentOutOfRangeException</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Fruits</span>.<span style="color: var(--pico-8-purple)">Add</span>(<span style="color: var(--pico-8-green)">fruit</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>Whenever a <code class="code">Fruit</code> is serialized, we first get its <code class="code">FruitKind</code> and store that first. Then we serialize the <code class="code">Fruit</code> afterwards. When reading, we read the <code class="code">FruitKind</code> first. Using a <code class="code">switch</code>, depending on the <code class="code">FruitKind</code> we are either deserializing an <code class="code">Apple</code>, a <code class="code">Banana</code> or an <code class="code">Orange</code>.</p>

<p>Notice how <code class="code">Deserialize</code> of each Fruit takes a <code class="code">RisMemoryStream</code> instead of a <code class="code">byte[]</code>. This is different from previous examples. The reason for that is because <code class="code">FruitBasket</code> does not know how big a serialized <code class="code">Fruit</code> is. By passing the stream into <code class="code">Fruit</code>s deserialization, the <code class="code">Fruit</code> can read as many bytes as it needs.</p>

<h2>An odd problem with a banger solution</h2>

<p>We are slowly inching closer to the end of this post. So let me present you with an odd, maybe somewhat complicated problem. Up until now, we haven't really used <code class="code">Seek</code>, did we? Well, in this chapter we are going to use it. And for a very cool reason actually. So cool in fact, that I hope this chapter blows your mind.</p>

<p>Let's say you have three classes: <code class="code">FooBase</code>, <code class="code">FooA</code> and <code class="code">FooB</code>. <code class="code">FooBase</code> stores a <code class="code">FooA</code> and a <code class="code">FooB</code>. Assume <code class="code">FooA</code> and <code class="code">FooB</code> have according serialize- and deserialize methods. <code class="code">FooBase</code> looks like this:</p>

<code class="code code_block">
<span style="color: var(--pico-8-green)">public class</span> <span style="color: var(--pico-8-brown)">FooBase</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">FooA</span> <span style="color: var(--pico-8-green)">A</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">FooB</span> <span style="color: var(--pico-8-green)">B</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesA</span> = <span style="color: var(--pico-8-green)">A</span>.<span style="color: var(--pico-8-purple)">Serialize</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesB</span> = <span style="color: var(--pico-8-green)">B</span>.<span style="color: var(--pico-8-purple)">Serialize</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytesA</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytesB</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">ToArray</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FooBase</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">FooBase</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">A</span> = <span style="color: var(--pico-8-brown)">FooA</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">B</span> = <span style="color: var(--pico-8-brown)">FooB</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>Nothing too complicated. Looks kinda like the example in the "Classes and structs" chapter.</p>

<p>But here's my request: I have a stream that contains a serialized <code class="code">FooBase</code>, but I <i>only</i> want to read <code class="code">FooB</code> out of it.</p>

<p>This may seem like an odd request, but let's roll with it. Maybe <code class="code">FooA</code> is very big and very costly to deserialize, so I only want to deserialize <code class="code">FooB</code> and deal with <code class="code">FooA</code> some time later. I don't want to deserialize the entire <code class="code">FooBase</code>, when I just need <code class="code">FooB</code>. How would you implement this?</p>

<p>Well, we definitely need to change both <code class="code">Serialize</code> and <code class="code">Deserialize</code>. As it stands now, our binary format stores no information on where <code class="code">FooB</code> starts in the stream. Currently we have to deserialize <code class="code">FooA</code>, which advances the stream in such a way, that <code class="code">FooB</code> can be deserialized.</p>

<p>One thing we can do, is to store the length of <code class="code">FooA</code>s bytes right before it:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">FooBase</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">FooA</span> <span style="color: var(--pico-8-green)">A</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">FooB</span> <span style="color: var(--pico-8-green)">B</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesA</span> = <span style="color: var(--pico-8-green)">A</span>.<span style="color: var(--pico-8-purple)">Serialize</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesB</span> = <span style="color: var(--pico-8-green)">B</span>.<span style="color: var(--pico-8-purple)">Serialize</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytesA</span>.<span style="color: var(--pico-8-green)">Length</span>); <span style="color: var(--pico-8-dark-grey)">// store length</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytesA</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytesB</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">ToArray</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FooBase</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">FooBase</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">4</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Current</span>); <span style="color: var(--pico-8-dark-grey)">// skip length</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">A</span> = <span style="color: var(--pico-8-brown)">FooA</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">B</span> = <span style="color: var(--pico-8-brown)">FooB</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>


<p>This way, we have information on how big <code class="code">FooA</code> actually is. So we can use it to skip <code class="code">FooA</code> entirely. Here's how that would look like:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">FooBase</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FooB</span> <span style="color: var(--pico-8-purple)">DeserializeOnlyB</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesLengthA</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadInt</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytesLengthA</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Current</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-brown)">FooB</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>Easy. Read the length, and skip that many bytes forward.</p>

<p>I am not satisfied with this solution however. What if we also have <code class="code">FooC</code>, <code class="code">FooD</code>, and so on? If we have thousands of <code class="code">Foo</code>s, and we only want to deserialize the last one, then we need to read and skip thousand lengths!</p>

<p>No, I have a better idea: Instead of storing the <i>length</i> of <code class="code">FooA</code>, let's store the <i>position</i> of <code class="code">FooB</code>:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">FooBase</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">FooA</span> <span style="color: var(--pico-8-green)">A</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">FooB</span> <span style="color: var(--pico-8-green)">B</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>();<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// placeholder</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">0</span>);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// serialize A</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesA</span> = <span style="color: var(--pico-8-green)">A</span>.<span style="color: var(--pico-8-purple)">Serialize</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytesA</span>);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// serialize B</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">positionB</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">0</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Current</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesB</span> = <span style="color: var(--pico-8-green)">B</span>.<span style="color: var(--pico-8-purple)">Serialize</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytesB</span>);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// go back to placeholder and write the actual position</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">0</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Begin</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">positionB</span>);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">ToArray</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FooBase</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">FooBase</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">4</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Current</span>); <span style="color: var(--pico-8-dark-grey)">// skip position</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">result</span>.<span style="color: var(--pico-8-green)">A</span> = <span style="color: var(--pico-8-brown)">FooA</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">result</span>.<span style="color: var(--pico-8-green)">B</span> = <span style="color: var(--pico-8-brown)">FooB</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
}
</code>

<p>With this, we can directly jump to <code class="code">FooB</code>:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">FooBase</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FooB</span> <span style="color: var(--pico-8-purple)">DeserializeOnlyB</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">positionB</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadInt</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">positionB</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Begin</span>); <span style="color: var(--pico-8-dark-grey)">// jump to B</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-brown)">FooB</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>This works. And it is actually quite useful. I use this technique in <a href="https://github.com/Rismosch/ris_engine" target="_blank" rel="noopener noreferrer">ris_engine</a>, in which I store all assets in a single file. At the beginning of the asset file, there is a lookup where each asset is stored. Loading an asset is as simple as seeking to the position of the according asset and reading from there. It's also quite helpful if you have quite complicated structs with many unsized types. Storing positions like this is quite the game changer.</p>

<p>And this is where I pull the rug from under you.</p>

<p>If you understood this concept of positions, then congratulations. You now understand pointers.</p>

<p>I am not kidding. This is no joke and I am fully serious. New programmers often struggle with pointers as a concept. From my experience, they even show pride in their unwillingness to understand what a pointer is. The argument I often hear is, that they don't intend to write C/C++, and therefore, pointers are not required to be understood.</p>

<p>But I want to stress again, this position concept we just implemented, is exactly what a pointer is. Somewhere in our stream <code class="code">FooB</code> is stored. At the start of the stream, we have written a pointer, which tells us exactly where <code class="code">FooB</code> is. To read <code class="code">FooB</code>, we use the <i>value</i> of the pointer to seek to the position where <code class="code">FooB</code> actually sits.</p>

<p>The same concept applies to actual pointers in languages like C/C++ and Rust. If you store a simple variable, you have direct access to it. You can read, modify and use it. No pointers required. If you have a pointer however, that variable sits somewhere in memory. Think of memory as a very long byte array, just like our stream. The pointer simply stores an index into that long byte array. To look up the value of our pointer, we look in the array at that index and voilà, we know what our pointer stores.</p>

<p>When people talk pointers, they often say "address" instead of "index". And people say "dereference" instead of "lookup".</p>

<table class="pointer" style="border-collapse: collapse; margin: auto">
<tr><th>pointer</th><th>stream</th><th>array</th></tr>
<tr><td>address</td><td>cursor / position</td><td>index</td></tr>
<tr><td>dereference</td><td>read / write</td><td>lookup</td></tr>
</table>

<p>I hope you could follow me. I think this was fun. And you can pat yourself on the back, for finally being able to understand pointers.</p>

<p>From now on, it's smooth sailing. The heavy lifting has been done. All that's left is to improve a bit on what we've learned and then discuss some useful strategies.</p>

<h2>Making it fat</h2>

<p>While we are at pointers now, I want to use the opportunity to widen your mind a little bit.</p>

<p>Right now, when storing a pointer, we are storing just a position. While this is sufficient, if misused, it can produce all kinds of headaches. Like, nothing stops the serializer to read and write outside our intended region. This may cause unintended bugs, but it can also be abused by mischievious actors. As a matter of fact, some of my previous deserialize code does allow such a bug. The astute reader may have caught it already.</p>

<p>Let me bring up the code again, so you have another chance to find it:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">FooBase</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FooB</span> <span style="color: var(--pico-8-purple)">DeserializeOnlyB</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">positionB</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadInt</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">positionB</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Begin</span>); <span style="color: var(--pico-8-dark-grey)">// jump to B</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-brown)">FooB</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>Did you catch it? It may be hard to spot, since we are missing a crucial implementation, but the problem is right there. The fruits example from earlier suffers from the same bug actually...</p>

<p>As it stands now, the code is assuming that <code class="code">FooB.Deserialize</code> never seeks, or at least seeks in a way that is invisible to <code class="code">FooBase</code>. Since <code class="code">FooB.Deserialize</code> takes the <i>entire</i> stream as an input, it can seek, read and write wherever it wants, even outside its intended range.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/out_of_range.png" style="width:100%; margin:auto; display: block; image-rendering: -moz-crisp-edges; image-rendering: -o-crisp-edges; image-rendering: -webkit-optimize-contrast; image-rendering: pixelated; -ms-interpolation-mode: nearest-neighbor;">

<p>Now we are reading in some place outside of our intended area, which is not good.</p>

<p>This error is called an out-of-bounds error, and it is one of the many reasons why C and C++ are considered unsafe. Pointers in C and C++ are just addresses, and nothing stops you from accessing <i>any</i> memory you want. You can dereference before and after your pointer willy nillingly.</p>

<p>To prevent such out-of-bound accesses, modern programming languages simply don't allow it. C# for example throws an exception if you try to access an array out of bounds. Not all hope is lost for C and C++ though, as modern operating systems utilize some clever tricks, like memory paging. But that's more of a band aid than an actual solution.</p>

<p>In our case, we want to prevent the deserializer to access an entire stream. We don't want it to modify the stream however it likes. The easiest way to accomplish this is to simply not give our serializer a stream, and only rely on byte arrays. But this implies that the one creating the array knows how many bytes a given deserializer needs. In one way or another, we have to store the length of our bytes somehow.</p>

<p>Let me introduce you to the <code class="code">FatPtr</code>. The <code class="code">FatPtr</code> is a fairly simple struct that stores two ints: An address and a length. The address is, well, the address. And the length describes how long the byte array at that address is. Since a normal pointer only stores a position, a <code class="code">FatPtr</code> is "fat", because it stores a length as well.</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public struct</span> <span style="color: var(--pico-8-brown)">FatPtr</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public int</span> <span style="color: var(--pico-8-green)">Address</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public int</span> <span style="color: var(--pico-8-green)">Length</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">FatPtr</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">Address</span> = <span style="color: var(--pico-8-pink)">0</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">Length</span> = <span style="color: var(--pico-8-pink)">0</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FatPtr</span> <span style="color: var(--pico-8-purple)">WithLength</span>(<span style="color: var(--pico-8-cyan)">int</span> <span style="color: var(--pico-8-green)">address</span>, <span style="color: var(--pico-8-cyan)">int</span> <span style="color: var(--pico-8-green)">length</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (<span style="color: var(--pico-8-green)">length</span> &lt; <span style="color: var(--pico-8-pink)">0</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">throw new</span> <span style="color: var(--pico-8-brown)">ArgumentOutOfRangeException</span>(<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">nameof</span>(<span style="color: var(--pico-8-green)">length</span>),<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">length</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">null</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">FatPtr</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Address</span> = <span style="color: var(--pico-8-green)">address</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Length</span> = <span style="color: var(--pico-8-green)">length</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FatPtr</span> <span style="color: var(--pico-8-purple)">WithEnd</span>(<span style="color: var(--pico-8-cyan)">int</span> <span style="color: var(--pico-8-green)">begin</span>, <span style="color: var(--pico-8-cyan)">int</span> <span style="color: var(--pico-8-green)">end</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (<span style="color: var(--pico-8-green)">begin</span> &gt; <span style="color: var(--pico-8-green)">end</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">throw new</span> <span style="color: var(--pico-8-brown)">ArgumentOutOfRangeException</span>(<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">nameof</span>(<span style="color: var(--pico-8-green)">end</span>),<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">end</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">null</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">FatPtr</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Address</span> = <span style="color: var(--pico-8-green)">begin</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Length</span> = <span style="color: var(--pico-8-green)">end</span> - <span style="color: var(--pico-8-green)">begin</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public int</span> <span style="color: var(--pico-8-purple)">End</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">Address</span> + <span style="color: var(--pico-8-green)">Length</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public bool</span> <span style="color: var(--pico-8-purple)">IsNull</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">Address</span> == <span style="color: var(--pico-8-pink)">0</span> && <span style="color: var(--pico-8-green)">Length</span> == <span style="color: var(--pico-8-pink)">0</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>This implementation also introduces some utility methods, which will be helpful later. For example, we can construct a <code class="code">FatPtr</code> via a beginning and an end. We also define that <code class="code">Address == 0 && Length == 0</code> means that we are dealing with a null pointer, which might be helpful in some instances. For example, if a field in our data class can be something or nothing, we can use a null pointer to indicate that the field stores nothing.</p>

<p>To support <code class="code">FatPtr</code>s, let's introduce two new methods to <code class="code">RisIO</code>:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public static class</span> <span style="color: var(--pico-8-brown)">RisIO</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FatPtr</span> <span style="color: var(--pico-8-purple)">ReadFatPtr</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">address</span> = <span style="color: var(--pico-8-purple)">ReadInt</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">length</span> = <span style="color: var(--pico-8-purple)">ReadInt</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-brown)">FatPtr</span>.<span style="color: var(--pico-8-purple)">WithLength</span>(<span style="color: var(--pico-8-green)">address</span>, <span style="color: var(--pico-8-green)">length</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static void</span> <span style="color: var(--pico-8-purple)">WriteFatPtr</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-brown)">FatPtr</span> <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">value</span>.<span style="color: var(--pico-8-green)">Address</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">value</span>.<span style="color: var(--pico-8-green)">Length</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>The <code class="code">FatPtr</code> only stores two fields, so to serialize a <code class="code">FatPtr</code> we only need to serialize these two fields. And while we are at it, let's modify the regular <code class="code">RisIO.Write</code> method like so:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public static class</span> <span style="color: var(--pico-8-brown)">RisIO</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FatPtr</span> <span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">begin</span> = <span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">0</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Current</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">end</span> = <span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">0</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Current</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">ptr</span> = <span style="color: var(--pico-8-brown)">FatPtr</span>.<span style="color: var(--pico-8-purple)">WithEnd</span>(<span style="color: var(--pico-8-green)">begin</span>, <span style="color: var(--pico-8-green)">end</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">ptr</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>We seek before and after the write, and construct a <code class="code">FatPtr</code> to it. This <code class="code">FatPtr</code> then points exactly at where we have just written some bytes. This significantly improves the ergonomics of client code, as we will see in the example. But before we take a look at that example, I want to add one last little helper to our <code class="code">RisIO</code> toolbox:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public static class</span> <span style="color: var(--pico-8-brown)">RisIO</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static byte</span>[] <span style="color: var(--pico-8-purple)">ReadAt</span>(<span style="color: var(--pico-8-brown)">RisMemoryStream</span> <span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-brown)">FatPtr</span> <span style="color: var(--pico-8-green)">fatPtr</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">fatPtr</span>.<span style="color: var(--pico-8-green)">Address</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Begin</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-purple)">Read</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">fatPtr</span>.<span style="color: var(--pico-8-green)">Length</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>This method takes a <code class="code">FatPtr</code>, which it uses to determine where to seek to and how many bytes should be read. This also will come in handy.</p> 

<p>With these tools under our belt, we can now implement a safe <code class="code">FooBase</code> like so:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">FooBase</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">FooA</span> <span style="color: var(--pico-8-green)">A</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public</span> <span style="color: var(--pico-8-brown)">FooB</span> <span style="color: var(--pico-8-green)">B</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>();<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// placeholders</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">fatPtrAPosition</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">0</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Current</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteFatPtr</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">FatPtr</span>());<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">fatPtrBPosition</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">0</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Current</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteFatPtr</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">FatPtr</span>());<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// serialize</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesA</span> = <span style="color: var(--pico-8-green)">A</span>.<span style="color: var(--pico-8-purple)">Serialize</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">fatPtrA</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytesA</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesB</span> = <span style="color: var(--pico-8-green)">B</span>.<span style="color: var(--pico-8-purple)">Serialize</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">fatPtrB</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">bytesB</span>);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// go back to placeholders and write actual fatptrs</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">fatPtrAPosition</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Begin</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteFatPtr</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">fatPtrA</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">fatPtrBPosition</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Begin</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteFatPtr</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">fatPtrB</span>);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">ToArray</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FooBase</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">FooBase</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">fatPtrA</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadFatPtr</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">fatPtrB</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadFatPtr</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesA</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadAt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">fatPtrA</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesB</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadAt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">fatPtrB</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">A</span> = <span style="color: var(--pico-8-brown)">FooA</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">bytesA</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">B</span> = <span style="color: var(--pico-8-brown)">FooB</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">bytesB</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">FooB</span> <span style="color: var(--pico-8-purple)">DeserializeOnlyB</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Seek</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-pink)">8</span>, <span style="color: var(--pico-8-brown)">SeekFrom</span>.<span style="color: var(--pico-8-green)">Current</span>); <span style="color: var(--pico-8-dark-grey)">// skip fatPtrA</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">fatPtrB</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadFatPtr</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytesB</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadAt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">fatPtrB</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-brown)">FooB</span>.<span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-green)">bytesB</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>Now, the deserialize methods of <code class="code">FooA</code> and <code class="code">FooB</code> take a <code class="code">byte[]</code> again. Because of that, <code class="code">FooA</code> and <code class="code">FooB</code> cannot do any out-of-bounds shenanigans.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/thumbs_up.webp" style="display: block; margin: auto; max-width: 100%;">

<h2>Some more strategies</h2>

<p>While we know enough now to serialize to our hearts desire, there are a few quality-of-life features that are very nice to have.</p>

<p>For example, we have a byte array, yes. But we would like to know what we are actually looking at, without deserializing the entire thing. Our binary format may be huge after all. If a user puts, idk, a PNG file into something that expects our custom format, it would be very nice to stop early before we are deserializing who knows what.</p>

<p>The easiest and most common solution to this problem is what's called a "magic value". A magic value consists of arbitrary bytes, which are written at the very beginning of our format. Thus, it's the first thing we can read.</p>

<p>For example, every PNG file starts with the bytes 137, 80, 78 and 71, which in escaped Unicode spells "\u0089PNG". As another example, every one of my assets in <a href="https://github.com/Rismosch/ris_engine" target="_blank" rel="noopener noreferrer">ris_engine</a> starts with the ASCII "ris_".</p>

<p>To serialize, we simply write the magic bytes first and then serialize after. To deserialize, we read the magic bytes and compare if they are as expected. If not, return an error. If they are as expected, continue deserialization. They may be called magic values, but there is no black magic to be found here.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/wizards.webp" style="display: block; margin: auto; max-width: 100%;">

<p>Another reason why a byte stream might not be as expected, is because of a version change. Maybe you are maintaining a program, which is in use for quite a while now and receives updates regularly. In that case the magic value of your format may stay the same, but you need to bump the version to prevent incompatible versions from breaking your program.</p>

<p>You can use different things for your version. A string is probably easiest, but the most wasteful. <a href="https://registry.khronos.org/glTF/specs/2.0/glTF-2.0.html#_asset_version" target="_blank" rel="noopener noreferrer">glTF</a> for example uses a string to identify its version. You can also use an integer, or multiple if you are going the <a href="https://semver.org/" target="_blank" rel="noopener noreferrer">semver</a> route. If you are clever, you can even put a MAJOR.MINOR.PATCH version in a single <code class="code">int</code>, like how <a href="https://registry.khronos.org/vulkan/specs/latest/man/html/VK_MAKE_API_VERSION.html" target="_blank" rel="noopener noreferrer">Vulkan</a> is doing.</p>

<p>But we may be overthinking things. The version is comparably small in relation to the rest of any format &#129300;</p>

<p>Another feature you may want is a header. A header consists of a number of bytes that are always there at the start of your format. The magic value and version is usually part of the header. But a header can hold additional information, for example the number of channels in an audio file or the dimensions of an image file.</p>

<p>Putting all these features into one code example, we might end up with something that looks like this:</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">MyCustomFormat</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">private static readonly byte</span>[] <span style="color: var(--pico-8-green)">ExpectedMagic</span> = { <span style="color: var(--pico-8-pink)">1</span>, <span style="color: var(--pico-8-pink)">2</span>, <span style="color: var(--pico-8-pink)">3</span>, <span style="color: var(--pico-8-pink)">4</span>, <span style="color: var(--pico-8-pink)">5</span> };<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">private const int</span> <span style="color: var(--pico-8-green)">ExpectedVersion</span> = <span style="color: var(--pico-8-pink)">42</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-green)">Magic</span> = <span style="color: var(--pico-8-green)">ExpectedMagic</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public int</span> <span style="color: var(--pico-8-green)">Version</span> = <span style="color: var(--pico-8-green)">ExpectedVersion</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public string</span> <span style="color: var(--pico-8-green)">Meta</span> = <span style="color: var(--pico-8-washed-grey)">"some meta data"</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Write</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">Magic</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteInt</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">Version</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">WriteString</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">Meta</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">ToArray</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">MyCustomFormat</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// magic</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">magic</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">Read</span>(<span style="color: var(--pico-8-green)">s</span>, <span style="color: var(--pico-8-green)">ExpectedMagic</span>.<span style="color: var(--pico-8-green)">Length</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">for</span> (<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">i</span> = <span style="color: var(--pico-8-pink)">0</span>; <span style="color: var(--pico-8-green)">i</span> < <span style="color: var(--pico-8-green)">ExpectedMagic</span>.<span style="color: var(--pico-8-green)">Length</span>; ++<span style="color: var(--pico-8-green)">i</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">left</span> = <span style="color: var(--pico-8-green)">ExpectedMagic</span>[<span style="color: var(--pico-8-green)">i</span>];<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">right</span> = <span style="color: var(--pico-8-green)">magic</span>[<span style="color: var(--pico-8-green)">i</span>];<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (<span style="color: var(--pico-8-green)">left</span> != <span style="color: var(--pico-8-green)">right</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">throw new</span> <span style="color: var(--pico-8-brown)">FormatException</span>(<span style="color: var(--pico-8-washed-grey)">"magic does not match"</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// version</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">version</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadInt</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">if</span> (<span style="color: var(--pico-8-green)">version</span> != <span style="color: var(--pico-8-green)">ExpectedVersion</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">throw new</span> <span style="color: var(--pico-8-brown)">FormatException</span>(<span style="color: var(--pico-8-washed-grey)">"version does not match"</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// meta</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">meta</span> = <span style="color: var(--pico-8-brown)">RisIO</span>.<span style="color: var(--pico-8-purple)">ReadString</span>(<span style="color: var(--pico-8-green)">s</span>);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// deserialize the rest</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">MyCustomFormat</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Magic</span> = <span style="color: var(--pico-8-green)">magic</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Version</span> = <span style="color: var(--pico-8-green)">version</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-green)">result</span>.<span style="color: var(--pico-8-green)">Meta</span> = <span style="color: var(--pico-8-green)">meta</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>A header may be of any type and can contain as many fields as you want. To keep the example short and simple, I chose to use a single string as meta data.</p>

<p>At last, but not least, you might want to be backwards and forwards compatible between different versions. This is a beast of a problem, especially when multiple people over multiple generations are maintaining such a format. Infamously, the .docx file format from Microsoft Word solved this by simply using a zipped XML container. If you have a .docx file lying around, you can literally unzip it. You will get a directory structure, which you can easily view with any file explorer.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/unzipped_docx.png" style="display: block; margin: auto; max-width: 100%;">

<p>I have yet to implement such a format in binary, but I did something like it in XML though. So I have no experience on how you would develop such a format in binary. But I can tell you what I would try!</p>

<p>I would heavily rely on chunks. By which I mean: <code class="code">FatPtr</code>s everywhere. At the start of each chunk, an enum or its "kind" is stored, which determines what exactly this chunk is doing. If a deserializer reads a kind of chunk that it does not recognize, the chunk is ignored. If you are not using <code class="code">FatPtr</code>s, store them right after each other. Each chunk would then also store its size, such that a deserializer, who doesn't recognize a chunk, can skip it.</p>

<h2>Compression and encryption</h2>

<p>As I've hinted in the chapter about <code class="code">bool</code>s, our implementation is quite naive and simplistic, which wastes quite a bit of space. We can try to decrease the size of our data by compressing it. But I have to mention that not all data compresses equally. Some data is resistant to compression. Also, you may be interested in encryption, because you might store sensitive data.</p>

<p>Well, compression and encryption are two entirely different disciplines. Explaining each one in detail is waaaayy beyond the scope of this post. Either one is a rabbit hole on its own. For our use case, only this counts: Both compression and encryption are methods, which take a byte array and spit out a new byte array. Compression will reduce the amount of bytes, and encryption will scramble the bytes.</p>

<p>Since we deserialize into a byte array, compressing and encrypting is as easy as calling the according method on our byte array.</p>

<code class="code code_block">
<span style="color: var(--pico-8-cyan)">public class</span> <span style="color: var(--pico-8-brown)">MySmallAndEncryptedData</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">private static byte</span>[] <span style="color: var(--pico-8-purple)">Compress</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">private static byte</span>[] <span style="color: var(--pico-8-purple)">Decompress</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">private static byte</span>[] <span style="color: var(--pico-8-purple)">Encrypt</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">private static byte</span>[] <span style="color: var(--pico-8-purple)">Decrypt</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public byte</span>[] <span style="color: var(--pico-8-purple)">Serialize</span>()<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">//...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">bytes</span> = <span style="color: var(--pico-8-green)">s</span>.<span style="color: var(--pico-8-purple)">ToArray</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">compressed</span> = <span style="color: var(--pico-8-purple)">Compress</span>(<span style="color: var(--pico-8-green)">bytes</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">encrypted</span> = <span style="color: var(--pico-8-purple)">Encrypt</span>(<span style="color: var(--pico-8-green)">compressed</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">encrypted</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">public static</span> <span style="color: var(--pico-8-brown)">MySmallAndEncryptedData</span> <span style="color: var(--pico-8-purple)">Deserialize</span>(<span style="color: var(--pico-8-cyan)">byte</span>[] <span style="color: var(--pico-8-green)">value</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">decrypted</span> = <span style="color: var(--pico-8-purple)">Decrypt</span>(<span style="color: var(--pico-8-green)">value</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">decompressed</span> = <span style="color: var(--pico-8-purple)">Decompress</span>(<span style="color: var(--pico-8-green)">decrypted</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">s</span> = <span style="color: var(--pico-8-cyan)">new</span> <span style="color: var(--pico-8-brown)">RisMemoryStream</span>(<span style="color: var(--pico-8-green)">decompressed</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">var</span> <span style="color: var(--pico-8-green)">result</span> = <span style="color: var(--pico-8-brown)">new</span> <span style="color: var(--pico-8-brown)">MySmallAndEncryptedData</span>();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-dark-grey)">// ...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: var(--pico-8-cyan)">return</span> <span style="color: var(--pico-8-green)">result</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</code>

<p>And that's all there is to it. There are many different compression and encryption algorithms out there, many of which have dedicated libraries. You can pick and choose whatever you want. The sky is the limit.</p>

<p>Usually, you don't want to compress or encrypt the entire format. Usually, you want to keep the magic value, version and sometimes even the entire header readable. The reason is simple: You want the receiver to be able to identify what they are looking at, and that will be difficult when the data is mangled.</p>

<p>To take <a href="https://github.com/Rismosch/ris_engine" target="_blank" rel="noopener noreferrer">ris_engine</a> again as an example, all my internal formats have a 16-byte magic value at the start of each asset. Each one starts with "ris_" to identify them as one of my assets. The remaining 12 characters indicate what kind of asset one is looking at. Below is a screenshot of my "ris_scene" format. By the mangled nature of the bytes, one can guess that it was compressed. But the "ris_scene" at the start remains readable.</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/hxd_3.png" style="display: block; margin: auto; max-width: 100%;">

<p>If you want to learn more about compression and encryption, I leave you with these Wikipedia articles:</p>

<ul>
<li><a href="https://en.wikipedia.org/wiki/Data_compression" target="_blank" rel="noopener noreferrer">Compression</a></li>
<li><a href="https://en.wikipedia.org/wiki/Encryption" target="_blank" rel="noopener noreferrer">Encryption</a></li>
</ul>

<h2>Conclusion</h2>

<p>Wow, what a journey. It took some work, but now you should have everything that you need to build your own binary format. I am sure I have written something that you disagree with, and that is fine. Now, you have the knowledge to make adjustments to my code and implement what you actually need or want. And with these tools under your belt, it shouldn't be too difficult to cook up your own binary format.</p>

<p>I claimed in the intro that a custom binary format is easily smaller and faster than JSON. So I will leave you with a benchmark. It compares my serializer with an equivalent implementation using <a href="https://www.newtonsoft.com/json" target="_blank" rel="noopener noreferrer">Json.NET by Newtonsoft</a>, a popular JSON library for C#. The benchmark results are below. </p>

<p>(lower is better)</p>

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/benchmark_1.webp" style="display: block; margin: auto; max-width: 100%;">

<img src="https://www.rismosch.com/articles/how-to-create-your-own-binary-format/benchmark_2.webp" style="display: block; margin: auto; max-width: 100%;">

<p>I hope it is evident that a custom serializer easily outperforms a general one.</p>

<p>A complete implementation of the code in this post, including this benchmark, can be found here: <a href="https://github.com/Rismosch/RisSerialization" target="_blank" rel="noopener noreferrer">LINK</a></p>