<p>As a little joke, in my <a href="https://www.rismosch.com/article?id=building-a-job-system" target="_blank" rel="noopener noreferrer">post about how I built my JobSystem</a>, I included a little PHP script that counts the days since it was posted.</p>

<img src="https://www.rismosch.com/articles/i-found-a-bug-in-my-job-system/screenshot.webp" style="display: block; margin: auto; max-width: 100%;">

<p>Unfortunately, I did run into a very severe bug, which is able to deadlock the entire system. Considering I made my JobSystem a big deal, I feel obligated to share you this rather interesting bug &#128522;</p>

<h2>The Bug</h2>

<p>Without going into the nitty gritty, here's a basic rundown of what the JobSystem does and how it works:</p>

<p>The JobSystem allows "Jobs" (function pointers, closures, lambdas, anonymous methods, or whatever your language of choice calls them) to be submitted to a buffer. Worker threads dequeue Jobs and execute them. With one worker per CPU core, this system is able to utilize all cores of your CPU, without the user ever needing to spawn additional threads for concurrency.</p>

<p>The submit function is global, and thus easily accessible. The pseudocode looks something like this:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">submit</span><span style="color:var(--pico-8-cyan)">(</span>job: <span style="color:var(--pico-8-washed-grey)">Job</span><span style="color:var(--pico-8-cyan)">)</span> -> <span style="color:var(--pico-8-washed-grey)">JobFuture</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> future = <span style="color:var(--pico-8-washed-grey)">JobFuture</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">()</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> wrapped_job = <span style="color:var(--pico-8-washed-grey)">Job</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-brown)">()</span> => <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> result = <span style="color:var(--pico-8-brown)">job</span><span style="color:var(--pico-8-cyan)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;future.<span style="color:var(--pico-8-brown)">set</span><span style="color:var(--pico-8-cyan)">(</span>result<span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><span style="color:var(--pico-8-green)">)</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> success = <span style="color:var(--pico-8-brown)">enqueue_job</span><span style="color:var(--pico-8-green)">(</span>wrapped_job<span style="color:var(--pico-8-green)">)</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <span style="color:var(--pico-8-green)">(</span>!success<span style="color:var(--pico-8-green)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">wrapped_job()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">return</span> future;<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
</code>

<p>First, a future is created. A future is a utility, that allows client code to wait and retrieve a Jobs return value, once it's done. The job is wrapped into another job, such that the future can be set. Then the wrapped job is enqueued. If the enqueue failed, the Job is simply invoked. Finally, the future is returned.</p>

<p>Unfortunately, this code is based on a flawed assumption: If a Job failed to be enqueued, then calling it immediately may lead to a deadlock.</p>

<p>Let me elaborate: Enqueuing can fail for 2 reasons: Either because the buffer is full, or because another thread holds a lock inside the buffer. These failures are by design. They enable a lock free behaviour and allow predictable performance. However, this comes with the consequence, that we may have a Job which was not enqueued. And now we need to decide what to do with that Job. When I wrote this code, I naively assumed I can just execute the Job. Afterall, as long as the system is making progress somehow, everything is fine. The client code should not care whether the Job is executed now or later.</p>

<p>But the following code breaks: </p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">let</span> future = <span style="color:var(--pico-8-washed-grey)">JobFuture</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-cyan)">()</span>;<br>
<br>
<span style="color:var(--pico-8-brown)">submit</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-green)">()</span> => <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;future.<span style="color:var(--pico-8-brown)">wait()</span>;<br>
<span style="color:var(--pico-8-green)">}</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
<br>
future.<span style="color:var(--pico-8-brown)">set</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-green)">42</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
</code>

<p>If an enqueue fails, this leads to the Job being executed directly. It's as if the call to submit isn't even there. The thread responsible for setting the future, the thread itself, will never set the future, because it's waiting on the future being set. Deadlock.</p>

<h2>The Fix</h2>

<p>Unfortunately, this has no satisfying solution. My engine is written in Rust, and to say it mildly, I am really struggling to get safe global state working. My latest prototype produces client code like this. It could be argued that the client code is unsound, and it shouldn't be legal in the first place, but because my latest prototype is somewhat promising, compromises have to be taken.</p>

<p>My current fix looks like this:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">submit</span><span style="color:var(--pico-8-cyan)">(</span>job: <span style="color:var(--pico-8-washed-grey)">Job</span><span style="color:var(--pico-8-cyan)">)</span> -> <span style="color:var(--pico-8-washed-grey)">JobFuture</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> future = <span style="color:var(--pico-8-washed-grey)">JobFuture</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">()</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> wrapped_job = <span style="color:var(--pico-8-washed-grey)">Job</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-brown)">()</span> => <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> result = <span style="color:var(--pico-8-brown)">job</span><span style="color:var(--pico-8-cyan)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;future.<span style="color:var(--pico-8-brown)">set</span><span style="color:var(--pico-8-cyan)">(</span>result<span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><span style="color:var(--pico-8-green)">)</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">while</span> <span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-cyan)">true</span><span style="color:var(--pico-8-green)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> success = <span style="color:var(--pico-8-brown)">enqueue_job(</span>wrapped_job<span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <span style="color:var(--pico-8-brown)">(</span>success<span style="color:var(--pico-8-brown)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">break</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">else</span> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">run_pending_job</span><span style="color:var(--pico-8-cyan)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">return</span> future;<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
</code>

<p>It's very similar to the original code. Basically, submit the wrapped job until it succeeds. When the enqueue fails, call <code class="code">run_pending_job()</code>. This function is a utility, allowing client code to run an enqueued Job. This is helpful, because if the current thread cannot make progress, the entire system can still make progress by working on something different. If no enqueued job exists, this function yields, freeing up the core for another thread.</p>

<p>This solution is not ideal, because this can still deadlock. It is still invoking Jobs, which may block the calling thread. There is always the possibility to simply <i>not</i> invoke a pending Job. But that would lead to a busy spinlock, using CPU resources without making progress. Admittedly, it would be safe, but there is the potential for a performance hit. Considering that my flawed clientcode only exists in debug builds of my engine, I am preferring the risk of a potential deadlock over a potential performance hit.</p>

<p>An more sophisticated solution would be to have a dedicated dispatcher. It would assign each Job an ID, telling them on what threads a Job is allowed to run on. But this requires more time and effort. And at the moment this bug is not justification enough for a dispatcher to exist. So I am sticking with the less likely, potential deadlock.</p>

<h2>Conclusion</h2>

<p>I am kind of amazed that it took this long until I found a major bug in my job system. In <a href="https://www.rismosch.com/article?id=building-a-job-system" target="_blank" rel="noopener noreferrer">my previous post about the JobSystem</a>, I emphasized how important it is to stress test something as critical and central as this. It only goes to show, how much good and extensive tests prevent bugs. Since then, I've also integrated <a href="https://github.com/rust-lang/miri" target="_blank" rel="noopener noreferrer">miri</a> in my testing, which gives me even more confidence in the stability of my system.</p>

<p>It also highlights once again how reliable Rust is. When I am working with C#, implementing something similar, I can't tell you how many times my code breaks just a few weeks or days after implementation. But nevertheless, my Rust code did in fact produce a bug. And unfortunately, that means I must take down my little PHP script on my original post &#129394;</p>