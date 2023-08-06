<h2>The Problem</h2>

<p>Assume we have a method called <span class="code">UnsafeMethod()</span>, which throws an exception upon calling it. What we want to do is to debug this method and find out where this exception comes from. The na&#239;ve approach is to simply surround it with a <span class="code">try-catch</span>, and set a debug point in the <span class="code">catch</span>-block:</p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan);">try</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;UnsafeMethod<span style="color:var(--pico-8-blue);">(</span><span style="color:var(--pico-8-blue);">);</span><br>
}<br>
<span style="color:var(--pico-8-cyan);">catch</span> <span style="color:var(--pico-8-blue);">(</span><span style="color:var(--pico-8-purple);">Exception</span> e<span style="color:var(--pico-8-blue);">)</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;Console.WriteLine<span style="color:var(--pico-8-blue);">(</span>e<span style="color:var(--pico-8-blue);">);</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">throw</span>;<br>
}
</p>

<p>When the debugger jumps into the <span class="code">catch</span>-block, we know that an exception has been thrown, and we get the exception-object which has been generated. While this may seem okay at first, it isn’t ideal. If the exception comes from lesser written code, the exception itself may proof to be very unhelpful to diagnose the problem. More importantly: Even though we know that <span class="code">UnsafeMethod()</span> threw the exception, if the method is hundreds of lines long and it calls various other methods, we still don’t know where that exception came from.</p>

<p>The latter reason happens by design. When an exception is thrown, execution of the program is stopped and an exception-object is handed up the callstack, until some code handles it via a <span class="code">try-catch</span>. This means an exception naturally collapses the callstack. When the program reaches the <span class="code">catch</span>-block for whatever reason, everything that happened in the <span class="code">try</span>-block is now collapsed and impossible to retrieve. But there is a way to look into the state of the <span class="code">try</span>-block, before the <span class="code">catch</span>-block is even executed.</p>

<h2>The Solution</h2>

<p class="code code_block">
<span style="color:var(--pico-8-cyan);">try</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;UnsafeMethod<span style="color:var(--pico-8-blue);">(</span><span style="color:var(--pico-8-blue);">);</span><br>
}<br>
<span style="color:var(--pico-8-cyan);">catch</span> <span style="color:var(--pico-8-blue);">(</span><span style="color:var(--pico-8-purple);">Exception</span> e<span style="color:var(--pico-8-blue);">)</span> <span style="color:var(--pico-8-cyan);">when</span> <span style="color:var(--pico-8-blue);">(<span style="color:var(--pico-8-cyan);">new</span> <span style="color:var(--pico-8-black);">Func</span>&lt;<span style="color:var(--pico-8-cyan);">bool</span>&gt;<bool>(() => <span style="color:var(--pico-8-cyan);">true</span>)())</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;Console.WriteLine<span style="color:var(--pico-8-blue);">(</span>e<span style="color:var(--pico-8-blue);">);</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">throw</span>;<br>
}
</p>

<p>If we use the <span class="code">when</span>-statement, put inside a delegate which returns a bool and set a debug point inside that delegate, then we can look into the <span class="code">try</span>-block. When the program reaches the delegate, then we can simply use the debugger and look into the callstack, and see where exactly the exception came from.</p>

<p>So, what is the <span class="code">when</span>-statement and why does this work? The <span class="code">when</span>-statement allows the <span class="code">catch</span>-block to be executed conditionally. If the condition evaluates to <span class="code">true</span>, the exception is caught and the <span class="code">catch</span>-block is executed. If the condition evaluates to false, then the exception is NOT caught and continues to be handed up the callstack.</p>

<p>Because of the <span class="code">when</span>-statement, two different scenarios can happen: Either the exception is being caught, or it is being thrown at the original position. Because the <span class="code">when</span>-statement can cause these two different behaviors, the callstack has to be kept intact during the evaluation of the <span class="code">when</span>-statement, just in case the exception is not being caught. And this is why our trick works: Because we put a delegate as the condition in the <span class="code">when</span>-statement, we can halt the evaluation of the <span class="code">when</span>-statement and then look into the still intact callstack. Thus, if the debugger reaches our delegate, the state of the <span class="code">try</span>-block is conserved.</p>

<p>Alternatively, you may want to write a method, which makes it a bit easier for you:</p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan);">public</span> <span style="color:var(--pico-8-purple);">bool</span> LogException<span style="color:var(--pico-8-blue);">(</span><span style="color:var(--pico-8-purple);">Exception</span> e, <span style="color:var(--pico-8-purple);">bool</span> shouldCatch = <span style="color:var(--pico-8-cyan);">false</span><span style="color:var(--pico-8-blue);">)</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;Console.WriteLine<span style="color:var(--pico-8-blue);">(</span>e<span style="color:var(--pico-8-blue);">);</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">return</span> shouldCatch<span style="color:var(--pico-8-blue);">;</span><br>
<span style="color:var(--pico-8-blue);">}</span>
</p>

<p>And then use it like this:</p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan);">try</span><br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;UnsafeMethod<span style="color:var(--pico-8-blue);">();</span><br>
}<br>
<span style="color:var(--pico-8-cyan);">catch</span> <span style="color:var(--pico-8-blue);">(</span><span style="color:var(--pico-8-purple);">Exception</span> e<span style="color:var(--pico-8-blue);">)</span> <span style="color:var(--pico-8-cyan);">when</span> <span style="color:var(--pico-8-blue);">(</span>LogException<span style="color:var(--pico-8-blue);">(</span>e<span style="color:var(--pico-8-blue);">)) { }</span>
</p>

<p>With this, the exception is always logged. If you hand the method <span class="code">true</span> via the method parameter, the exception will be suppressed. And whenever you want to debug the exception, you can put a debug point in the body of <span class="code">LogException()</span> and look easily into the callstack.</p>

<p>And that's it. It's really a quite neat and easy to use trick, and my goto way of debugging exceptions. Have fun debugging &#128522;</p>