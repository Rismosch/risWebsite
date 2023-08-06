<p>Maybe you are programming your own engine, and the API you are using to get buttons just returns whether a button is pressed or not. But more often than not, you only care about the single frame the button is pressed down, or released. Maybe you are already using an engine, that provides these features, but you want to implement a virtual button, and now you need to implement that behavior yourself.</p>

<p>Whatever your reason, you have a button that is either on or off, and you want to compute button down, up and hold. Here's a full implementation in Rust:</p>

<p class="code code_block">
#<span style="color:var(--pico-8-cyan)">[</span>derive<span style="color:var(--pico-8-washed-grey)">(Default)</span><span style="color:var(--pico-8-cyan)">]</span><br>
<span style="color:var(--pico-8-cyan)">pub struct</span> <span style="color:var(--pico-8-washed-grey)">Buttons</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;up: <span style="color:var(--pico-8-washed-grey)">u32</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;down: <span style="color:var(--pico-8-washed-grey)">u32</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;hold: <span style="color:var(--pico-8-washed-grey)">u32</span>,<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">pub trait</span> <span style="color:var(--pico-8-washed-grey)">IButtons</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">up</span><span style="color:var(--pico-8-washed-grey)">(</span><span style="color:var(--pico-8-cyan)">&self</span><span style="color:var(--pico-8-washed-grey)">)</span> -> <span style="color:var(--pico-8-washed-grey)">u32</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">down</span><span style="color:var(--pico-8-washed-grey)">(</span><span style="color:var(--pico-8-cyan)">&self</span><span style="color:var(--pico-8-washed-grey)">)</span> -> <span style="color:var(--pico-8-washed-grey)">u32</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">hold</span><span style="color:var(--pico-8-washed-grey)">(</span><span style="color:var(--pico-8-cyan)">&self</span><span style="color:var(--pico-8-washed-grey)">)</span> -> <span style="color:var(--pico-8-washed-grey)">u32</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">update</span><span style="color:var(--pico-8-washed-grey)">(</span>&<span style="color:var(--pico-8-cyan)">mut self</span>, new_state: &<span style="color:var(--pico-8-washed-grey)">u32)</span>;<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">impl</span> <span style="color:var(--pico-8-washed-grey)">IButtons</span> <span style="color:var(--pico-8-pink)">for</span> <span style="color:var(--pico-8-washed-grey)">Buttons</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">up</span><span style="color:var(--pico-8-washed-grey)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-washed-grey)">)</span> -> <span style="color:var(--pico-8-washed-grey)">u32 {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.up<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">}</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">down</span><span style="color:var(--pico-8-washed-grey)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-washed-grey)">)</span> -> <span style="color:var(--pico-8-washed-grey)">u32 {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.down<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">}</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">hold</span><span style="color:var(--pico-8-washed-grey)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-washed-grey)">)</span> -> <span style="color:var(--pico-8-washed-grey)">u32 {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.hold<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">}</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">update</span><span style="color:var(--pico-8-washed-grey)">(</span>&<span style="color:var(--pico-8-cyan)">mut self</span>, new_state: &<span style="color:var(--pico-8-washed-grey)">u32) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.up = !new_state & <span style="color:var(--pico-8-cyan)">self</span>.hold;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.down = new_state & !<span style="color:var(--pico-8-cyan)">self</span>.hold;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.hold = *new_state;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span><br>
</p>

<p>You may or may not be able to read Rust. That's fine. The key things you need is the struct and its update function. The rest is just boilerplate stuff, to provide an interface to client code. So here's the solution again, with the unimportant stuff stripped away:</p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan)">pub struct</span> <span style="color:var(--pico-8-washed-grey)">Buttons</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;up: <span style="color:var(--pico-8-washed-grey)">u32</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;down: <span style="color:var(--pico-8-washed-grey)">u32</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;hold: <span style="color:var(--pico-8-washed-grey)">u32</span>,<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">update</span><span style="color:var(--pico-8-washed-grey)">(</span>&<span style="color:var(--pico-8-cyan)">mut self</span>, new_state: &<span style="color:var(--pico-8-washed-grey)">u32) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.up = !new_state & <span style="color:var(--pico-8-cyan)">self</span>.hold;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.down = new_state & !<span style="color:var(--pico-8-cyan)">self</span>.hold;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.hold = *new_state;<br>
<span style="color:var(--pico-8-washed-grey)">}</span><br>
</p>

<p>Note: This wont compile. As I said, the boilerplate was removed. Treat it as pseudo code.</p>

<p>Let's dissect this, so you can adapt it to your programming language and use case. The <span class="code">Buttons</span> struct stores 3 values. Each value is a 32 bit unsigned integer. Each bit represents a single button, and whether it is pressed (1) or not (0). We need 3 ints, one for button down, button up and button hold. This means this struct stores 32 different buttons, and their down, up and hold state.</p>

<p>Depending on your usecase, 32 buttons may be too much or too little. So feel free to change it to a <span class="code">bool</span> or an integer with more bits.</p>

<p>Now let's talk about the update function. It takes 2 parameters: <span class="code">self</span> and <span class="code">new_state</span>. <span class="code">self</span> in Rust is literally the same as the <span class="code">this</span> pointer in C/C++ and many other programming languages; it references the object itself, in this case an object of the <span class="code">Buttons</span> struct. <span class="code">new_state</span> is a snapshot of the signal, being fed into the buttons. This is simply your on/off signal.</p>

<p>The three lines in the update function are where the magic happens. The trick is, that <span class="code">self.hold</span> is updated last. Thus, when computing <span class="code">self.up</span> and <span class="code">self.down</span>, <span class="code">self.hold</span> stores the button value of the previous frame. If the previous frame was 1 and the current frame is 0, <span class="code">self.up</span> will be 1, otherwise 0. If the previous frame was 0 and the current frame is 1, <span class="code">self.down</span> will be 1, otherwise 0.</p>

<p>Note 1: This implementation uses the bitwise-AND operator <span class="code">&</span>. If you plan to use a <span class="code">bool</span> for your system, I recommend using logic-AND <span class="code">&&</span> instead, because <span class="code">&&</span> is lazy and thus <i>can</i> be a tiny bit faster (Potentionally. Maybe. Maybe not. I give no guarantees.)</p>

<p>Note 2: In Rust, the bitwise-NOT operator is <span class="code">!</span>. In your programming language it's probably <span class="code">~</span> (Potentionally. Maybe. Maybe not. I give no guarantees.)</p>

<?php late_image(get_source("diagram.png"),"pixel_image","display: block; margin: auto; width: 441px; max-width: 100%;");?>

<p>Above is a diagram, visualizing the output of the code, run for 6 frames. The button is pressed on frame 2, 3 and 4 (<span class="code">new state</span>). Notice that <span class="code">button hold</span> has the same value as <span class="code">new state</span>. <span class="code">button down</span> is 1 only at frame 2, the first frame the button was pressed. And <span class="code">button up</span> is 1 only at frame 5, the first frame the button was released.</p>

<p>If you implement an interface like this implementation, you would call <span class="code">update()</span> each frame, where your input is handled. Then, wherever you have a reference to a <span class="code">Buttons</span> object, you can simply call <span class="code">down</span>, <span class="code">up</span> or <span class="code">hold</span> to get the desired value.</p>

<p>And that's literally it. Have a good one 😊</p>