<p>If the hero were the phrase <i>"premature optimization is the root of all evil"</i>, then I'd be its villain. I might be an uninteresting and boring villain, but its villain nonetheless.</p>

<p>Anyway, there is one idea that really caught me on fire, ever since I first came across it. Pretty much most game engines that you will come across, as well as any tutorials that may claim to teach you how to program one, they all hinge on the same basic, single threaded main loop.</p>

<img src="https://www.rismosch.com/articles/building-a-job-system/single_threaded_gameloop.webp" style="display: block; margin: auto; max-width: 100%;" />

<p>This is fine, until you realize that today's machines have multiple processors. If the entire engine runs only on one thread, then no matter how many cores your processor has, only the speed of one actually determines how fast your game is running.</p>

<video loop="true" autoplay="autoplay" muted="true" style="max-width:100%; display: block; margin: auto;" loading='lazy'>
<source src="https://www.rismosch.com/articles/building-a-job-system/multithreading_meme.mp4" type="video/mp4">
</video>

<p>Na&#239;ve me, and multiple people that I've seen online, all seem to come to the same idea eventually: Simply divide the engine into different threads! For example, have one thread that runs the input and logic, and have another thread that runs the output. But this is not an ideal solution. One job will be more expensive than the other. Either the logic, or the output (most definitely the rendering) will take a larger amount of time to compute. So to have things synced up properly, one must wait for the other, wasting CPU cycles. To solve this, maybe you think you simply have to divide the engine into even more threads. But this becomes very complicated very quickly, with diminishing returns.</p>

<p>No. What I want is what Jason Gregory proposes in his book "Game Engine Architecture": A Job System.</p>

<p>The idea doesn't sound so difficult at the first glance: You spawn a thread for every available core. You have a queue where you can push jobs to. The worker threads then constantly try to dequeue jobs from it, and run them. A job is simply a piece of code, usually a pointer to a function. The potential of this idea is huge: In the best case, it gives you access to 100% of the CPU 100% of the time. Sure, we need additional job system logic, which overall is more work. But since we can do multiple things at once, this overhead is definitely worth the effort, and reduces the computation time in total.</p>

<h2>Thread Pool Theory</h2>

<p>One of the best books about programming I've ever read, is "C++ Concurrency in Action" by Anthony Williams. Even though I made the <a href="https://www.rismosch.com/article?id=crisis" target="_blank" rel="noopener noreferrer">switch to Rust a few months ago</a>, the book was still immensely helpful for understanding and designing multithreaded stuff. And the job system that I've implemented is inspired by the thread pool that Anthony proposed in his book. So before we dig into my spaghetti code, it's probably a good idea to first understand how a thread pool works. By that I mean, we'll go and look at Anthony's thread pool.</p>

<h3>Na&#239;ve Thread Pool</h3>

<p>In the most na&#239;ve way possible, a thread pool is fairly easy to implement: Have one concurrent queue and share it among every worker thread.</p>

<img src="https://www.rismosch.com/articles/building-a-job-system/naive_system.webp" style="display: block; margin: auto; max-width: 100%;" />

<p>This is primitive, but it should work. Nonetheless, this design has a huge problem: Congestion. This na&#239;ve solution has multiple threads that are all constantly trying to dequeue jobs from the job queue. This means that the queue must be absolutely thread safe. This begs the question, how does something become thread safe? Simply put, it prevents simultaneous accesses from different threads. There are various techniques to accomplish this. There exist blocking and non-blocking methods, but ultimately, a thread safe object turns multithreaded accesses into "single threaded" ones. (My explanation isn't quite correct, concurrent accesses on a thread safe object still happen on different threads. But since all accesses happen in order and never at the same time, they may as well be single threaded. You get my point.) This is a huge huge problem, because we wanted a system that could run things in parallel, and now this queue alone is preventing that entirely.</p>

<p>To fix this, we somehow need to avoid threads attempting to access the same data. Luckily, I don't have to be smart to figure this one out, and Anthony proposes a better solution.</p>

<h3>Local Job Buffers</h3>

<p>The key thing to realize, is that jobs can submit other jobs. Jobception. If we enqueue these to the shared job queue, we have an even bigger congestion problem. No. Instead, each worker thread has its own job queue, where it can submit and dequeue jobs.</p>

<img src="https://www.rismosch.com/articles/building-a-job-system/local_buffers.webp" style="display: block; margin: auto; max-width: 100%;" />

<p>Now, a worker thread only attempts to dequeue from the global job queue, if its own local queue is empty. Also notice that only the worker thread has access to its queue, so the queue doesn't even need to be thread safe!</p>

<p>This is a much better solution, but it is still prone to problems. Imagine again that we have a logic job and an output job, that are both submitted to this job system. One worker may receive the logic job, and another receives the output job. Since jobs can push other jobs, and these are enqueued to the local buffer, logic jobs will always stay on thread A, while render jobs will always stay on thread B. But just like before, one job is much more expensive than the other one. And even though we have enough jobs for everyone, because they never leave their worker thread, we get this situation again:</p>

<video loop="true" autoplay="autoplay" muted="true" style="max-width:100%; display: block; margin: auto;" loading='lazy'>
<source src="https://www.rismosch.com/articles/building-a-job-system/multithreading_meme.mp4" type="video/mp4">
</video>

<p>Anthony comes to the rescue one more time!</p>

<h3>Stealing Jobs</h3>

<p>The final design proposes that every worker thread actually has a reference to the local queues of the other threads, such that workers may <i>steal</i> jobs from each other.</p>

<img src="https://www.rismosch.com/articles/building-a-job-system/steal_buffers.webp" style="display: block; margin: auto; max-width: 100%;" />

<p>This implies that the local job queues must be thread safe again. Also, we stop talking about queues, and more about buffers. More precisely, a deque. If the container that holds the jobs where a simple queue, we would run into the congestion problem again. But to allow for maximum parallel work, jobs are pushed and popped from the same end of the buffer, while stealing is done from the opposite end. This ensures that even though multiple threads access the same buffer, they happen at different locations on the buffer, so stealing and popping can happen simultaneously. The only way we could run into congestion, is if the buffer were empty. But in this edge case we simply don't care, because what is a worker thread going to do if it is empty? Wait? Since there isn't anything to do anyway, we can absolutely afford to congest.</p>

<img src="https://www.rismosch.com/articles/building-a-job-system/deque.webp" style="display: block; margin: auto; max-width: 100%;" />

<p>While a bit more complicated, this solution is much more sophisticated. Full credit to Anthony. And it is this thread pool, which I decided to build upon.</p>

<h2>My Job System</h2>

<p>Now that we have a basic understanding of how a sophisticated job system looks like, let me present you my altered version. In total, my system is built from 5 parts, which we will tackle in order:</p>

<ol>
<li><b>Job.</b>&nbsp;&nbsp;Simple wrapper around a function pointer. Until I figure out how to store function pointers in a collection, a wrapper is required.</li>

<li><b>JobBuffer.</b>&nbsp;&nbsp;Buffer that holds Jobs. It isn't really a queue, neither is it really a deque, it's more akin to a weird stack, but at the same time it isn't. It's specially designed for this job system, and isn't even thread safe if used incorrectly, but we'll talk about it later.</li>

<li><b>JobSystem.</b>&nbsp;&nbsp;Collection of globally available functions, to setup worker threads, submit jobs and run pending jobs.</li>

<li><b>JobFuture.</b>&nbsp;&nbsp;Utility to wait and return values from jobs.</li>

<li><b>JobCell.</b>&nbsp;&nbsp;Utility, to pass references into a job. Rusts ownership rules made this absolutely necessary and a real headache to design.</li>
</ol>

<p>Without further ado, let's jump into some Rust code &#128522;</p>

<h2>Job</h2>

<p>As stated above, I honestly don't know how to store function pointers in a container elegantly. In Rust, every function is unique. Even if two functions were to have the same signature, they are still considered different from each other. Thus, such two similar functions cannot be stored in the same collection, unless you put them into some sort of wrapper. To make my life easier, I simply created a wrapper that stores a single function pointer. Since every <code class="code">Job</code> is syntactically the same, they can be effortlessly stored in a container.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">pub struct <span style="color:var(--pico-8-washed-grey)">Job</span> {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;to_invoke: <span style="color:var(--pico-8-washed-grey)">Option</span><<span style="color:var(--pico-8-washed-grey)">Box</span><<span style="color:var(--pico-8-cyan)">dyn</span> <span style="color:var(--pico-8-washed-grey)">FnOnce()</span>>>,<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">impl <span style="color:var(--pico-8-washed-grey)">Job</span> {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">new</span><<span style="color:var(--pico-8-washed-grey)">F</span>: <span style="color:var(--pico-8-washed-grey)">FnOnce</span><span style="color:var(--pico-8-green)">()</span> + <span style="color:var(--pico-8-cyan)">'static</span>><span style="color:var(--pico-8-green)">(</span>to_invoke: <span style="color:var(--pico-8-washed-grey)">F</span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-cyan)">Self</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> to_invoke: <span style="color:var(--pico-8-washed-grey)">Option</span><<span style="color:var(--pico-8-washed-grey)">Box</span><<span style="color:var(--pico-8-cyan)">dyn</span> <span style="color:var(--pico-8-washed-grey)">FnOnce</span><span style="color:var(--pico-8-brown)">()</span>>> = <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-brown)">(</span><span style="color:var(--pico-8-washed-grey)">Box</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-cyan)">(</span>to_invoke<span style="color:var(--pico-8-cyan)">)</span><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">Self</span> <span style="color:var(--pico-8-brown)">{</span> to_invoke <span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)"><u>invoke</u></span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">mut <u>self</u></span><span style="color:var(--pico-8-green)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-brown)">(</span>to_invoke<span style="color:var(--pico-8-brown)">)</span> = <span style="color:var(--pico-8-cyan)"><u>self</u></span>.to_invoke.<span style="color:var(--pico-8-brown)"><u>take</u>() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;to_invoke<span style="color:var(--pico-8-cyan)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">impl</span> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Debug</span> <span style="color:var(--pico-8-cyan)">for</span> <span style="color:var(--pico-8-washed-grey)">Job</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">fmt</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span>, <u>f</u>: &<span style="color:var(--pico-8-cyan)">mut</span> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Formatter</span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">fmt</span>::<span style="color:var(--pico-8-washed-grey)">Result</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> result = <span style="color:var(--pico-8-pink)">match</span> <span style="color:var(--pico-8-cyan)">self</span>.to_invoke <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span>_<span style="color:var(--pico-8-cyan)">)</span> => <span style="color:var(--pico-8-brown)">"Some"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">None</span> => <span style="color:var(--pico-8-brown)">"None"</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">write!</span><span style="color:var(--pico-8-brown)">(</span><u>f</u>, <span style="color:var(--pico-8-brown)">"{{ to_invoke: <span style="color:var(--pico-8-cyan)">{}</span> }}"</span>, result<span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span><br>
</code>

<p>To be able to invoke the function pointer, it is stored in an <code class="code">Option</code>, such that when calling it, we can take ownership of it. It should go without saying, that this <code class="code">Job</code> can only be invoked once, because once we moved the function pointer out of <code class="code">Option</code>, there is no function pointer left.</p>

<h2>JobBuffer</h2>

<p>There is one thing that you must know, when designing interfaces for thread safe constructs: The more flexible the public interface, the more race conditions you introduce. While impractical, assume the following example: A worker thread only wants to pop jobs of the buffer, if they are for example render jobs. To program something like this, you would have a peak method, that checks the next job without popping it off the buffer. The caller then can check the job. If it's the right kind, pop it. Client code would look something like this:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">let</span> job = <span style="color:var(--pico-8-cyan)">job_system</span>::<span style="color:var(--pico-8-brown)">peak()</span>;<br>
<span style="color:var(--pico-8-pink)">if</span> job.kind == <span style="color:var(--pico-8-washed-grey)">JobKind</span>::Render <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> render_job = <span style="color:var(--pico-8-cyan)">job_system</span>::<span style="color:var(--pico-8-brown)">pop()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;render_job.<span style="color:var(--pico-8-brown)">invoke()</span>;<br>
<span style="color:var(--pico-8-green)">}</span>
</code>

<p>This code would be fine in a single threaded context, but this introduces a race condition. Between <code class="code">job_system::peak()</code> and <code class="code">job_system::pop()</code>, another thread may pop off a job. Thus, even though the if-statement may be <code class="code">true</code>, a different job is popped off than what was peaked at. This means, <code class="code">render_job</code> may actually not be a render job.</p>

<p>The simple solution is to keep your interface to an absolute minimum. Besides its constructor, my job buffer only exposes 3 methods: Push, pop and steal. Peaking and indexing are not implemented, because they cause race conditions and aren't used by my system.</p>

<p>An iterator may be helpful, but iterators are notoriously difficult to make thread safe. Whatever mechanism you use to prevent other threads to access your collection, it somehow must live at least as long as the iterator. This introduces many difficult questions, like who owns the locking mechanism, or how to sync it with the iterator? It's probably best to just not implement it.</p>

<p><code class="code">is_empty()</code> may be useful, and I even have the use case for it, but ultimately it's redundant. Because <code class="code">steal()</code> and <code class="code">pop()</code> can simply return <code class="code">None</code> when no job exists, <code class="code">is_empty()</code> provides truly redundant information.</p>

<p><code class="code">count</code> or <code class="code">length</code> are also useless: Either there are jobs in the buffer that can be popped off, or there are none. There will never be a case, where I would want to know how many jobs are currently stored in the buffer.</p>

<br><br>

<p>It should become a bit clearer now, why I am calling it a buffer, and not queue, deque or stack. It is lacking seriously in features. Also, it isn't technically a queue, nor stack, because jobs can be popped from both sides. It isn't a deque either, because it can push only from one side.</p>

<p>Okay, that were a lot of preambles, but how to actually build such a thing? Hang in there, we gotta talk collections first. We will see code soon enough.</p>

<br><br>

<p>I was quite surprised how many different designs there are. I went through at least 10 different prototypes before I landed on the buffer that I am currently using. Initially, I wanted to write a doubly linked list. Those come with a hefty number of drawbacks and they are stupidly difficult to write in Rust. But I wanted one, because it can hold infinite items in theory. When a buffer is full, and a job is pushed, what should happen? For a long time, I didn't want to answer this question, hence I was only considering a buffer with theoretical infinite size.</p>

<p>But eventually, while going for a walk, I realized something: When a buffer is full, I can simply invoke the job. The caller shouldn't care whether the submit function blocks or not. All that matters is that the job system is making progress <i>somehow</i>. If the caller needs to be interrupted to invoke another job, then this is totally fine. Even though the caller may not make progress, another job will be, therefore no resources are wasted.</p>

<img src="https://www.rismosch.com/articles/building-a-job-system/friendship_ended.webp" style="display: block; margin: auto; max-width: 100%;" />

<p>My choice to implement the <code class="code">JobBuffer</code> fell onto the ring buffer. A ring buffer is just an array or vector, with one or two cursors, keeping track where the current head and tail are. It's called a ring buffer, because the cursors wrap around the array, thus it effectively has no start and end, like a ring. Once allocated, no further allocations are necessary to use it. Also, no nasty pointer issues arise. And to top it all off, a ring buffer can be entirely coded in safe Rust, which is always a big bonus.</p>

<p>To familiarize yourself with it, here's a simple playground I've built in JavaScript, where you can familiarize yourself with the concept:</p>

<noscript>&#129299; <i>actually, you have to enable JavaScript, if you want to play on the playground</i> &#129299;</noscript>

<div id="ring_buffer_playground" style="display:none; width:300px; margin:auto; border: 5px solid var(--pico-8-cyan); background-color: var(--pico-8-white);">

<div style="display:block; padding: 5px;">
    <input id="ring_buffer_playground_input" type="text" value="input" style="margin-bottom: 1.3em;font-family: Arial, sans-serif;font-size: 1em;background-color: var(--pico-8-white); width: 200px;">
    <button onclick="playground_push()">push</button>
</div>

<table id="ring_buffer_playground_table">
    <tr>
        <td id="ring_buffer_playground_head_0" class="ring_buffer_playground_head">head</td>
        <td id="ring_buffer_playground_cell_0" class="ring_buffer_playground_cell"></td>
        <td id="ring_buffer_playground_tail_0" class="ring_buffer_playground_tail">tail</td>
    </tr>
    <tr>
        <td id="ring_buffer_playground_head_1" class="ring_buffer_playground_head ring_buffer_invisible">head</td>
        <td id="ring_buffer_playground_cell_1" class="ring_buffer_playground_cell"></td>
        <td id="ring_buffer_playground_tail_1" class="ring_buffer_playground_tail ring_buffer_invisible">tail</td>
    </tr>
    <tr>
        <td id="ring_buffer_playground_head_2" class="ring_buffer_playground_head ring_buffer_invisible">head</td>
        <td id="ring_buffer_playground_cell_2" class="ring_buffer_playground_cell"></td>
        <td id="ring_buffer_playground_tail_2" class="ring_buffer_playground_tail ring_buffer_invisible">tail</td>
    </tr>
    <tr>
        <td id="ring_buffer_playground_head_3" class="ring_buffer_playground_head ring_buffer_invisible">head</td>
        <td id="ring_buffer_playground_cell_3" class="ring_buffer_playground_cell"></td>
        <td id="ring_buffer_playground_tail_3" class="ring_buffer_playground_tail ring_buffer_invisible">tail</td>
    </tr>
    <tr>
        <td id="ring_buffer_playground_head_4" class="ring_buffer_playground_head ring_buffer_invisible">head</td>
        <td id="ring_buffer_playground_cell_4" class="ring_buffer_playground_cell"></td>
        <td id="ring_buffer_playground_tail_4" class="ring_buffer_playground_tail ring_buffer_invisible">tail</td>
    </tr>
</table>

<div style="display:block; padding: 5px; width: 128px; margin:auto;">
    <button onclick="playground_pop()">pop</button>
    <button onclick="playground_steal()">steal</button>
</div>

<span style="background-color: var(--pico-8-white); border: 5px solid var(--pico-8-red); padding: 5px; width: 280px; display: block; overflow: hidden;" id="ring_buffer_playground_output">output</span>

</div>

<script>
let playground = document.getElementById("ring_buffer_playground");
let playground_input = document.getElementById("ring_buffer_playground_input");
let playground_output = document.getElementById("ring_buffer_playground_output");

playground.style.display="block";

let head = 0;
let tail = 0;

function playground_push() {
    let node = document.getElementById(`ring_buffer_playground_cell_${head}`);

    if (node.textContent.length === 0) {
        let to_push = playground_input.value;
        if (to_push.length === 0) {
            playground_output.textContent = "cannot push empty";
        } else {
            node.textContent = to_push;

            let old_head_cursor = document.getElementById(`ring_buffer_playground_head_${head}`);
            head = (head + 1) % 5;
            let new_head_cursor = document.getElementById(`ring_buffer_playground_head_${head}`);

            old_head_cursor.classList.add("ring_buffer_invisible");
            new_head_cursor.classList.remove("ring_buffer_invisible");

            playground_output.textContent = `pushed \"${to_push}\"`;
        }
    } else {
        playground_output.textContent = "buffer full";
    }
}

function playground_pop() {
    let new_head;
    if (head <= 0) {
        new_head = 4;
    } else {
        new_head = head - 1;
    }

    let node = document.getElementById(`ring_buffer_playground_cell_${new_head}`);

    if (node.textContent.length === 0) {
        playground_output.textContent = "buffer empty";
    } else {
        let popped = node.textContent;
        node.textContent = "";

        let old_head_cursor = document.getElementById(`ring_buffer_playground_head_${head}`);
        head = new_head;
        let new_head_cursor = document.getElementById(`ring_buffer_playground_head_${head}`);

        old_head_cursor.classList.add("ring_buffer_invisible");
        new_head_cursor.classList.remove("ring_buffer_invisible");

        playground_output.textContent = `popped \"${popped}\"`;
    }
}

function playground_steal() {
    let old_tail = tail;

    let node = document.getElementById(`ring_buffer_playground_cell_${old_tail}`);

    if (node.textContent.length === 0) {
        playground_output.textContent = "buffer empty";
    } else {
        let stole = node.textContent;
        node.textContent = "";

        let old_tail_cursor = document.getElementById(`ring_buffer_playground_tail_${tail}`);
        tail = (tail + 1) % 5;
        let new_tail_cursor = document.getElementById(`ring_buffer_playground_tail_${tail}`);

        old_tail_cursor.classList.add("ring_buffer_invisible");
        new_tail_cursor.classList.remove("ring_buffer_invisible");

        playground_output.textContent = `stole \"${stole}\"`;
    }
}
</script>



<p>We're finally done with the preamble, let's jump into code:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">pub struct <span style="color:var(--pico-8-washed-grey)">JobBuffer</span> {</span></br>
&nbsp;&nbsp;&nbsp;&nbsp;head: <span style="color:var(--pico-8-washed-grey)">UnsafeCell</span><<span style="color:var(--pico-8-washed-grey)">usize</span>>,</br>
&nbsp;&nbsp;&nbsp;&nbsp;tail: <span style="color:var(--pico-8-washed-grey)">Mutex</span><<span style="color:var(--pico-8-washed-grey)">usize</span>>,</br>
&nbsp;&nbsp;&nbsp;&nbsp;jobs: <span style="color:var(--pico-8-washed-grey)">Vec</span><<span style="color:var(--pico-8-washed-grey)">Mutex</span><<span style="color:var(--pico-8-washed-grey)">Option</span><<span style="color:var(--pico-8-washed-grey)">Job</span>>>,</br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>This is the entire struct. it stores the current head and tail, as well as a vector of jobs.</p>

<p>This definition may confuse you. Why is the head an <code class="code">UnsafeCell&lt;usize&gt;</code>? Why not a <code class="code">Mutex&ltusize&gt;</code>, like the tail? Why does the job vector also store mutexes? Isn't locking the head and tail safe enough?</p>

<p>Pushing and popping will happen on the head. Stealing will happen on the tail. I hope it should be fairly obvious why the tail needs to be a mutex. Multiple worker threads may steal from the same buffer. As such, the tail needs to be protected. Okay, but doesn't this also apply to the head? Well, no. Notice that only the worker thread that owns the buffer is pushing and popping jobs. Because a <i>single</i> thread is calling push and pop, there will never ever be the case, that <code class="code">push()</code> or <code class="code">pop()</code> will be called simultaneously. This means, as an optimization, we can simply leave the head unprotected. This is what I meant earlier, that this buffer isn't thread-safe, if you use it incorrectly. But since we aren't using it incorrectly, we are perfectly safe and sound.</p>

<p>Okay, but why do the items in the vector need to be protected? Doesn't that mean that we have to lock 2 mutexes to access a single entry? Yes, and that's one downside of this design. But till now, this didn't cause too much performance issues, so I am not worrying about it <i>yet</i>. While I can't get rid of this extra mutex, you should still understand why it is needed: When the buffer is full or empty, the tail and the head point to the same node, and thus two different threads may access the same node simultaneously. This needs to be prevented. We can't lock both head and tail, because that would mean if one thread were to call <code class="code">steal()</code>, a second thread cannot call <code class="code">pop()</code>. We want that 2 threads can work on the same buffer at the same time. Thus, the node itself must be stored within a mutex.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span> <span style="color:var(--pico-8-washed-grey)">JobBuffer</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span>capacity: <span style="color:var(--pico-8-washed-grey)">usize</span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-washed-grey)">Arc</span><<span style="color:var(--pico-8-cyan)">Self</span>> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>jobs</u> = <span style="color:var(--pico-8-washed-grey)">Vec</span>::<span style="color:var(--pico-8-brown)">with_capacity(</span>capacity<span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">for</span> _ <span style="color:var(--pico-8-pink)">in</span> <span style="color:var(--pico-8-green)">0</span>..capacity <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>jobs</u>.<span style="color:var(--pico-8-brown)"><u>push</u></span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">Mutex</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">None</span><span style="color:var(--pico-8-green)">)</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Arc</span>::<span style="color:var(--pico-8-brown)">new(</span><span style="color:var(--pico-8-cyan)">Self {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;head: <span style="color:var(--pico-8-washed-grey)">UnsafeCell</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(0)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;tail: <span style="color:var(--pico-8-washed-grey)">Mutex</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(0)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;jobs,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>The constructor should now be very straightforward. We create a vector with a fixed capacity, and insert empty nodes. Notice that we return an <code class="code">Arc&lt;Self&gt;</code>. This makes the client code a bit tidier, because this buffer is never used on its own, it will always be duplicated somehow.

</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span> <span style="color:var(--pico-8-washed-grey)">JobBuffer</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">push</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span>, job: <span style="color:var(--pico-8-washed-grey)">Job</span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-washed-grey)">Result</span><<span style="color:var(--pico-8-washed-grey)">()</span>, <span style="color:var(--pico-8-washed-grey)">BlockedOrFull</span>> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> <u>head</u> = <span style="color:var(--pico-8-cyan)">unsafe</span> <span style="color:var(--pico-8-brown)">{</span> &<span style="color:var(--pico-8-cyan)">mut</span> *<span style="color:var(--pico-8-cyan)">self</span>.head.<span style="color:var(--pico-8-brown)">get</span><span style="color:var(--pico-8-cyan)">()</span> <span style="color:var(--pico-8-brown)">}</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>node</u> = <span style="color:var(--pico-8-pink)">match</span> <span style="color:var(--pico-8-cyan)">self</span>.jobs<span style="color:var(--pico-8-brown)">[</span>*<u>head</u><span style="color:var(--pico-8-brown)">]</span>.<span style="color:var(--pico-8-brown)">try_lock() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-cyan)">(</span>node<span style="color:var(--pico-8-cyan)">)</span> => node,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">sync</span>::<span style="color:var(--pico-8-washed-grey)">TryLockError</span>::<span style="color:var(--pico-8-washed-grey)">WouldBlock</span><span style="color:var(--pico-8-cyan)">)</span> => <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">return</span> <span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">BlockedOrFull</span> <span style="color:var(--pico-8-brown)">{</span> not_pushed: job <span style="color:var(--pico-8-brown)">}</span><span style="color:var(--pico-8-green)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">sync</span>::<span style="color:var(--pico-8-washed-grey)">TryLockError</span>::<span style="color:var(--pico-8-washed-grey)">Poisoned</span><span style="color:var(--pico-8-green)">(</span>e<span style="color:var(--pico-8-green)">)</span><span style="color:var(--pico-8-cyan)">)</span> => <span style="color:var(--pico-8-cyan)">throw!(</span><span style="color:var(--pico-8-brown)">"mutex is poisoned: <span style="color:var(--pico-8-cyan)">{}</span>"</span>, e<span style="color:var(--pico-8-cyan)">)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> *<u>node</u> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span>_<span style="color:var(--pico-8-cyan)">)</span> => <span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">BlockedOrFull</span> <span style="color:var(--pico-8-green)">{</span> not_pushed: job <span style="color:var(--pico-8-green)">}</span><span style="color:var(--pico-8-cyan)">)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">None</span => <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*<u>node</u> = <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-green)">(</span>job<span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*<u>head</u> = <span style="color:var(--pico-8-green)">(</span>*<u>head</u> + <span style="color:var(--pico-8-green)">1)</span> % <span style="color:var(--pico-8-cyan)">self</span>.jobs.<span style="color:var(--pico-8-brown)">capacity</span><span style="color:var(--pico-8-green)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-green)">(<span style="color:var(--pico-8-brown)">()</span>)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Push is a bit more complicated. Notice the unsafe block in the first line of this method. As I've explained earlier, this doesn't cause problems, because this will only ever be accessed by a single thread. We then lock the node at the current head. Notice how this is a <code class="code">try_lock</code>, not a <code class="code">lock</code>. This avoids blocking, meaning the job system can progress, even if the push failed. If we managed to lock the node, we can then see if there is a job inside or not. If there is a job already, the buffer is full. If there is no job, then we can overwrite it and update the head.</p>

<p>Note that the <code class="code">BlockedOrFull</code> error takes ownership of the job. This allows the not-pushed job to be returned to the caller, so that they may invoke it.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span> <span style="color:var(--pico-8-washed-grey)">JobBuffer</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">wait_and_pop</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-washed-grey)">Result</span><<span style="color:var(--pico-8-washed-grey)">Job</span>, <span style="color:var(--pico-8-washed-grey)">IsEmpty</span>> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> <u>head</u> = <span style="color:var(--pico-8-cyan)">unsafe</span> <span style="color:var(--pico-8-brown)">{</span> &<span style="color:var(--pico-8-cyan)">mut</span> *<span style="color:var(--pico-8-cyan)">self</span>.head.<span style="color:var(--pico-8-brown)">get</span><span style="color:var(--pico-8-cyan)">()</span> <span style="color:var(--pico-8-brown)">}</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> new_head = <span style="color:var(--pico-8-pink)">if</span> *<u>head</u> == <span style="color:var(--pico-8-green)">0</span> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.jobs.<span style="color:var(--pico-8-brown)">capacity</span><span style="color:var(--pico-8-cyan)">()</span> - <span style="color:var(--pico-8-green)">1</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span> <span style="color:var(--pico-8-pink)">else</span> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*<u>head</u> - <span style="color:var(--pico-8-green)">1</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>node</u> = <span style="color:var(--pico-8-cyan)">unwrap_or_throw!</span><span style="color:var(--pico-8-brown)">(</span><span style="color:var(--pico-8-cyan)">self</span>.jobs<span style="color:var(--pico-8-cyan)">[</span>new_head<span style="color:var(--pico-8-cyan)">]</span>.<span style="color:var(--pico-8-brown)">lock</span><span style="color:var(--pico-8-cyan)">()</span>, <span style="color:var(--pico-8-brown)">"mutex is poisoned")</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> <u>node</u>.<span style="color:var(--pico-8-brown)"><u>take</u>() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">None</span> => <span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">IsEmpty</span><span style="color:var(--pico-8-cyan)">)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span>job<span style="color:var(--pico-8-cyan)">)</span> => <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*<u>head</u> = new_head;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-green)">(</span>job<span style="color:var(--pico-8-green)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Pop is very similar, but the new head is computed first and then the node is locked. Note that it uses <code class="code">lock</code>, not <code class="code">try_lock</code>. A worker thread attempting to pop a job literally has nothing else to do. So to make progress, the best option is to block until we can access the node. Then, whether or not there is a job, we return the job or an <code class="code">IsEmpty</code> error.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span> <span style="color:var(--pico-8-washed-grey)">JobBuffer</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">steal</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-washed-grey)">Result</span><<span style="color:var(--pico-8-washed-grey)">Job</span>, <span style="color:var(--pico-8-washed-grey)">BlockedOrEmpty</span>> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> tail = <span style="color:var(--pico-8-cyan)">self</span>.tail.<span style="color:var(--pico-8-brown)">try_lock()</span>.<span style="color:var(--pico-8-brown)">map_err(to_steal_error)</span>?;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> old_tail = *<u>tail</u>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>node</u> = <span style="color:var(--pico-8-cyan)">self</span>.jobs<span style="color:var(--pico-8-brown)">[</span>old_tail<span style="color:var(--pico-8-brown)">]</span>.<span style="color:var(--pico-8-brown)">try_lock()</span>.<span style="color:var(--pico-8-brown)">map_err(to_steal_error)</span>?;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> <u>node</u>.<span style="color:var(--pico-8-brown)"><u>take</u>() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">None</span> => <span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">BlockedOrEmpty</span><span style="color:var(--pico-8-cyan)">)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span>job<span style="color:var(--pico-8-cyan)">)</span> => <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*<u>tail</u> = <span style="color:var(--pico-8-green)">(</span>old_tail + <span style="color:var(--pico-8-green)">1)</span> % <span style="color:var(--pico-8-cyan)">self</span>.jobs.<span style="color:var(--pico-8-brown)">capacity</span><span style="color:var(--pico-8-green)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-green)">(</span>job<span style="color:var(--pico-8-green)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Again, steal is very similar. But unlike the head in push and pop, the tail needs to be locked, because it is protected by a mutex. Notice how this one is using a <code class="code">try_lock</code> and not a <code class="code">lock</code>. This is due to there being <code class="code">number of CPUS - 1</code> amount of buffers to steal from. If we can't steal from this buffer, we simply steal from the next one. There is no need to block. After that, we then lock the node. If we were successful, we compute the next tail and return the job, otherwise we return an error.</p>

<p>At last, but not least, we need some utility stuff, so that this buffer compiles and can actually be shared between threads:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">unsafe impl</span> <span style="color:var(--pico-8-washed-grey)">Send</span> <span style="color:var(--pico-8-cyan)">for</span> <span style="color:var(--pico-8-washed-grey)">JobBuffer</span> <span style="color:var(--pico-8-cyan)">{}</span><br>
<span style="color:var(--pico-8-cyan)">unsafe impl</span> <span style="color:var(--pico-8-washed-grey)">Sync</span> <span style="color:var(--pico-8-cyan)">for</span> <span style="color:var(--pico-8-washed-grey)">JobBuffer</span> <span style="color:var(--pico-8-cyan)">{}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">to_steal_error</span><<span style="color:var(--pico-8-washed-grey)">T</span>><span style="color:var(--pico-8-green)">(</span>error: <span style="color:var(--pico-8-washed-grey)">TryLockError</span><<span style="color:var(--pico-8-washed-grey)">T</span>><span style="color:var(--pico-8-cyan)">)</span> -> <span style="color:var(--pico-8-washed-grey)">BlockedOrEmpty</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> error <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">sync</span>::<span style="color:var(--pico-8-washed-grey)">TryLockError</span>::<span style="color:var(--pico-8-washed-grey)">WouldBlock</span> => <span style="color:var(--pico-8-washed-grey)">BlockedOrEmpty</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">sync</span>::<span style="color:var(--pico-8-washed-grey)">TryLockError</span>::<span style="color:var(--pico-8-washed-grey)">Poisoned</span><span style="color:var(--pico-8-brown)">(</span>e<span style="color:var(--pico-8-brown)">)</span> => <span style="color:var(--pico-8-cyan)">throw!</span><span style="color:var(--pico-8-brown)">(</span><span style="color:var(--pico-8-brown)">"mutex is poisoned: <span style="color:var(--pico-8-cyan)">{}</span>"</span>, e<span style="color:var(--pico-8-brown)">)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span><br>
</code>

<p>And there we have it. A highly specialized buffer to store jobs.</p>

<p>This <code class="code">JobBuffer</code> is about 100 lines of code, but to test it I wrote 1180 lines! (including whitespaces)</p>

<p>This buffer is the core of my job system, which is the core of my game engine. So it must be <i>water tight</i>. I really can't afford this buffer to blow up, under any circumstance. As such, Anthony Williams recommended himself, to write as much tests as possible, to test every thinkable edge case, no matter how rare. What if one thread is pushing while another is stealing? What if 100 threads are stealing on an empty buffer? What if 1 thread is popping from an empty buffer? What if one thread is pushing and popping on a full buffer, while 100 threads are stealing?</p>

<p>But the painful, repetitive work was absolutely worth it. To this day, I haven't encountered a single issue with the <code class="code">JobBuffer</code>, and it's been <?php

$now = time();
$your_date = strtotime("2022-09-18");
$datediff = $now - $your_date;

echo round($datediff / (60 * 60 * 24));

?> days, since its implementation. And I am quite confident that it does its job properly. <i>Maybe</i> I will encounter a bug in the future, but I seriously don't think I will encounter a serious problem, ever.</p>

<p style="color:var(--pico-8-dark-grey)"><i><b>Narrator:</b> The moment the buffer blows up, he will regret to have said this.</i></p>

<h2>JobSystem</h2>

<p>You may or may not have figured out, that the <code class="code">JobBuffer</code> described in the previous section is the local <code class="code">JobBuffer</code>, which is owned by a worker thread. What about the globally shared <code class="code">JobBuffer</code>? For better or for worse, I got rid of it.</p>

<p>Anthony Williams' thread pool seems fine, but I was always asking myself: Who is pushing onto the global buffer? I feel like Anthony's thread pool is designed for a general purpose. It most likely finds use in some enterprise project. Maybe a user is requesting a search or find feature, and then the program would initiate a thread pool to do just that. Maybe there will be additional threads that also push onto the thread-pool. Nonetheless, I really want to do what Naughty Dog did with their engine: Jobify the entire thing. EVERYTHING will run on this job system, with a few significant exceptions: Startup, shutdown, logging and IO. Everything that runs on the job system will push local jobs.</p>

<p>I also thought about who would own the main game loop. This too went through many different design iterations, but I realized soon enough that the main thread can also be a worker thread. The main thread can own a local <code class="code">JobBuffer</code> too, and it would execute a "GodJob", that lives for the entirety of the program. When the god job ends, it waits for all currently enqueued jobs. After all workers have been ended, the engine is allowed to shut down.</p>

<img src="https://www.rismosch.com/articles/building-a-job-system/startup_shutdown.webp" style="display: block; margin: auto; max-width: 100%;" />

<p>Let's start with some structs:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">thread_local! {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">static</span> <span style="color:var(--pico-8-washed-grey)">WORKER_THREAD</span>: <span style="color:var(--pico-8-washed-grey)">RefCell</span><<span style="color:var(--pico-8-washed-grey)">Option</span><<span style="color:var(--pico-8-washed-grey)">WorkerThread</span>>> = <span style="color:var(--pico-8-washed-grey)">RefCell</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(<span style="color:var(--pico-8-washed-grey)">None</span>)</span>;<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">struct <span style="color:var(--pico-8-washed-grey)">WorkerThread</span> {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;local_buffer: <span style="color:var(--pico-8-washed-grey)">Arc</span><<span style="color:var(--pico-8-washed-grey)">JobBuffer</span>>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;steal_buffers: <span style="color:var(--pico-8-washed-grey)">Vec</span><<span style="color:var(--pico-8-washed-grey)">Arc</span><<span style="color:var(--pico-8-washed-grey)">JobBuffer</span>>>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;index: <span style="color:var(--pico-8-washed-grey)">usize</span>,<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">pub struct <span style="color:var(--pico-8-washed-grey)">JobSystemGuard</span> {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;handles: <span style="color:var(--pico-8-washed-grey)">Option</span><<span style="color:var(--pico-8-washed-grey)">Vec</span><<span style="color:var(--pico-8-washed-grey)">JoinHandle</span><<span style="color:var(--pico-8-green)">()</span>>>>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;done: <span style="color:var(--pico-8-washed-grey)">Arc</span><<span style="color:var(--pico-8-washed-grey)">AtomicBool</span>>,<br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>First, we have a thread local variable. It stores an <code class="code">Option</code>, because a thread that wasn't initialized as a worker thread cannot serve as a worker thread.</p>

<p>The <code class="code">WorkerThread</code> currently stores 3 things: The <code class="code">JobBuffer</code> that it owns, the <code class="code">JobBuffer</code>s from the other workers, and an index, that uniquely identifies this thread. The index is mainly used for debug purposes, and it's a utility that directly comes with my job system. As such, the index isn't linked to the platform, the OS, or anything of that nature.</p>

<p><code class="code">JobSystemGuard</code> is the struct that will be returned when the job system is initialized. If this guard is dropped, the job system will be shut down. Due to its singleton-like behavior, it didn't feel right to have a struct that represents the job system, similar of how you would design an OOP singleton. Instead, it gives the caller the opportunity to drop it at a specified time. The <code class="code">JobSystemGuard</code> stores all handles of every spawned worker thread, and an <code class="code">AtomicBool</code> to signal when worker threads should be shut down.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">init</span><span style="color:var(--pico-8-cyan)">(</span>buffer_capacity: <span style="color:var(--pico-8-washed-grey)">usize</span>, threads: <span style="color:var(--pico-8-washed-grey)">usize</span><span style="color:var(--pico-8-cyan)">)</span> -> <span style="color:var(--pico-8-washed-grey)">JobSystemGuard</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">// estimate workthreads and according affinities</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> cpu_count = <span style="color:var(--pico-8-brown)">cpu_info</span><span style="color:var(--pico-8-green)">()</span>.cpu_count <span style="color:var(--pico-8-cyan)">as</span> <span style="color:var(--pico-8-washed-grey)">usize</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> threads = <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">cmp</span>::<span style="color:var(--pico-8-brown)">min</span><span style="color:var(--pico-8-green)">(</span>cpu_count, threads<span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>affinities</u> = <span style="color:var(--pico-8-washed-grey)">Vec</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-pink)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">for</span> _ <span style="color:var(--pico-8-pink)">in</span> <span style="color:var(--pico-8-green)">0</span>..threads <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>affinities</u>.<span style="color:var(--pico-8-brown)"><u>push</u>(</span><span style="color:var(--pico-8-washed-grey)">Vec</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-cyan)">()</span><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">for</span> i <span style="color:var(--pico-8-pink)">in</span> <span style="color:var(--pico-8-green)">0</span>..cpu_count <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>affinities</u><span style="color:var(--pico-8-brown)">[</span>i % threads<span style="color:var(--pico-8-brown)">]</span>.<span style="color:var(--pico-8-brown)">push</u>(<span style="color:var(--pico-8-black)">i</span>)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">// setup job buffers</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>buffers</u> = <span style="color:var(--pico-8-washed-grey)">Vec</span>::<span style="color:var(--pico-8-brown)">with_capacity</span><span style="color:var(--pico-8-green)">(</span>threads<span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">for</span> _ <span style="color:var(--pico-8-pink)">in</span> <span style="color:var(--pico-8-green)">0</span>..threads <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>buffers</u>.<span style="color:var(--pico-8-brown)"><u>push</u>(</span><span style="color:var(--pico-8-washed-grey)">JobBuffer</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-cyan)">(</span>buffer_capacity<span style="color:var(--pico-8-cyan)">)</span><span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> done = <span style="color:var(--pico-8-washed-grey)">Arc</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">AtomicBool</span>::<span style="color:var(--pico-8-brown)">new(<span style="color:var(--pico-8-cyan)">false</span>)</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">// setup worker threads</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>handles</u> = <span style="color:var(--pico-8-washed-grey)">Vec</span>::<span style="color:var(--pico-8-brown)">with_capacity</span><span style="color:var(--pico-8-green)">(</span>threads - <span style="color:var(--pico-8-green)">1)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">for</span> <span style="color:var(--pico-8-green)">(</span>i, core_ids<span style="color:var(--pico-8-green)">)</span> <span style="color:var(--pico-8-pink)">in</span> <u>affinities</u>.<span style="color:var(--pico-8-brown)">iter</span><span style="color:var(--pico-8-green)">()</span>.<span style="color:var(--pico-8-brown)">enumerate</span><span style="color:var(--pico-8-green)">()</span>.<span style="color:var(--pico-8-brown)">take</span><span style="color:var(--pico-8-green)">(</span>threads<span style="color:var(--pico-8-green)">)</span>.<span style="color:var(--pico-8-brown)">skip</span><span style="color:var(--pico-8-green)">(1) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> core_ids = core_ids.<span style="color:var(--pico-8-brown)">clone()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> buffers = <span style="color:var(--pico-8-brown)">duplicate_buffers(</span>&<u>buffers</u><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> done_copy = done.<span style="color:var(--pico-8-brown)">clone()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>handles</u>.<span style="color:var(--pico-8-brown)"><u>push</u>(</span><span style="color:var(--pico-8-washed-grey)">thread</span>::<span style="color:var(--pico-8-brown)">spawn</span><span style="color:var(--pico-8-cyan)">(move</span> || <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">setup_worker_thread(</span>&core_ids, buffers, i<span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">run_worker_thread(</span>i, done_copy<span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><span style="color:var(--pico-8-cyan)">)</span><span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">debug!</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-brown)">"spawned <span style="color:var(--pico-8-cyan)">{}</span> additional worker threads"</span>, <u>handles</u>.<span style="color:var(--pico-8-brown)">len()</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> handles = <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-green)">(</span><u>handles</u><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">// setup main worker thread (this thread)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> core_ids = <u>affinities</u><span style="color:var(--pico-8-green)">[0]</span>.<span style="color:var(--pico-8-brown)">clone</span><span style="color:var(--pico-8-green)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> buffers = <span style="color:var(--pico-8-brown)">duplicate_buffers</span><span style="color:var(--pico-8-green)">(</span>&<u>buffers</u><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">setup_worker_thread</span><span style="color:var(--pico-8-green)">(</span>&core_ids, buffers, <span style="color:var(--pico-8-green)">0)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">JobSystemGuard</span> <span style="color:var(--pico-8-green)">{</span> handles, done <span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>The <code class="code">init</code> method may look daunting at first, but it's actually very straight forward. It takes 2 values: The size of the <code class="code">JobBuffer</code>s and how many threads should be spawned. First, we clamp the threads to the number of processors that your machine has. This is to prevent spawning more threads than cores.</p>

<p>Then we calculate the affinities. Affinity allows you to lock a thread only to a specific core. For example, if a thread has affinity 0, 1 and 2, then it will run only on core 0, 1 and 2. Every worker thread should run on a different core, otherwise worker threads can interrupt each other, which may result in a performance loss.</p>

<p>Assume your PC has 12 cores. If you were to spawn 5 worker threads, my code would estimate the affinities like so:</p>

<table class="affinity_table">
    <tr>
        <td class="affinity_table_th">Thread</td>
        <td class="affinity_table_th">Affinity</td>
    </tr>
    <tr>
        <td class="affinity_table_td_left">0</td>
        <td class="affinity_table_td_right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0, 5, 10</td>
    </tr>
    <tr>
        <td class="affinity_table_td_left">1</td>
        <td class="affinity_table_td_right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1, 6, 11</td>
    </tr>
    <tr>
        <td class="affinity_table_td_left">2</td>
        <td class="affinity_table_td_right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2, 7</td>
    </tr>
    <tr>
        <td class="affinity_table_td_left">3</td>
        <td class="affinity_table_td_right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3, 8</td>
    </tr>
    <tr>
        <td class="affinity_table_td_left">4</td>
        <td class="affinity_table_td_right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4, 9</td>
    </tr>
</table>

<p>After we've calculated the affinities, we simply create one <code class="code">JobBuffer</code> for every worker thread. We also create the <code class="code">done</code> flag.</p>

<p>Since the main thread is also a worker thread, we will be spawning <code class="code">threads - 1</code> number of workers. For each we clone the affinities, the buffers and the <code class="code">done</code> flag. Once spawned, each thread will first set itself up and then run itself.</p>

<p>Just right after that, the main thread is doing the same, but it also collects the handles and puts them into the <code class="code">JobSystemGuard</code>. The job system is now setup and already running!</p>

<p>Now let's see how it's dropped:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl <span style="color:var(--pico-8-washed-grey)">Drop</span> for <span style="color:var(--pico-8-washed-grey)">JobSystemGuard</span> {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)"><u>drop</u></span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">mut <u>self</u></span><span style="color:var(--pico-8-green)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">debug!</span><span style="color:var(--pico-8-brown)">("dropping job system...")</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)"><u>self</u></span>.done.<span style="color:var(--pico-8-brown)">store(</span><span style="color:var(--pico-8-cyan)">true</span>, <span style="color:var(--pico-8-washed-grey)">Ordering</span>::<span style="color:var(--pico-8-washed-grey)">SeqCst</span><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">empty_buffer(<span style="color:var(--pico-8-green)">0</span>)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> <span style="color:var(--pico-8-cyan)"><u>self</u></span>.handles.<span style="color:var(--pico-8-brown)"><u>take</u>() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span>handles<span style="color:var(--pico-8-cyan)">)</span> => <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>i</u> = <span style="color:var(--pico-8-green)">0</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">for</span> handle <span style="color:var(--pico-8-pink)">in</span> handles <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>i</u> <u>+=</u> <span style="color:var(--pico-8-green)">1</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> handle.<span style="color:var(--pico-8-brown)">join() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-cyan)">(<span style="color:var(--pico-8-green)">()</span>)</span> => <span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">trace!(</span><span style="color:var(--pico-8-brown)">"joined thread <span style="color:var(--pico-8-cyan)">{}</span>"</span>, i<span style="color:var(--pico-8-cyan)">)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-cyan)">(</span>_<span style="color:var(--pico-8-cyan)">)</span> => <span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">fatal!(</span><span style="color:var(--pico-8-brown)">"failed to join thread <span style="color:var(--pico-8-cyan)">{}</span>"</span>, i<span style="color:var(--pico-8-cyan)">)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">None</span> => <span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">debug!(<span style="color:var(--pico-8-brown)">"handles already joined"</span>)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">debug!</span><span style="color:var(--pico-8-brown)">("job system finished")</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>First, we set the <code class="code">done</code> flag to <code class="code">true</code>, so every worker thread knows it's time to stop. The main thread then empties its local buffer, meaning it is popping all jobs left in the buffer and running them. Then we take the <code class="code">handles</code>, and join each worker thread. With some logging thrown in, that's all that <code class="code">drop</code> does.</p>

<p>Let's take a look at all the utility functions, that the previous two code snippets were using:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">duplicate_buffers</span><span style="color:var(--pico-8-cyan)">(</span>buffers: &<span style="color:var(--pico-8-washed-grey)">Vec</span><<span style="color:var(--pico-8-washed-grey)">Arc</span><<span style="color:var(--pico-8-washed-grey)">JobBuffer</span>>>) -> <span style="color:var(--pico-8-washed-grey)">Vec</span><<span style="color:var(--pico-8-washed-grey)">Arc</span><<span style="color:var(--pico-8-washed-grey)">JobBuffer</span>>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>result</u> = <span style="color:var(--pico-8-washed-grey)">Vec</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">for</span> buffer <span style="color:var(--pico-8-pink)">in</span> buffers <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>result</u>.<span style="color:var(--pico-8-brown)"><u>push</u>(</span>buffer.<span style="color:var(--pico-8-brown)">clone<span style="color:var(--pico-8-cyan)">()</span>)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<u>result</u><br>
<span style="color:var(--pico-8-cyan)">}</span><br>
</code>

<p>This method duplicates the array of <code class="code">JobBuffer</code>s, thus every worker thread can have a copy of all available buffers.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">fn <span style="color:var(--pico-8-brown)">setup_worker_thread</span>(</span>core_ids: &<span style="color:var(--pico-8-green)">[</span><span style="color:var(--pico-8-washed-grey)">usize</span><span style="color:var(--pico-8-green)">]</span>, buffers: <span style="color:var(--pico-8-washed-grey)">Vec</span><<span style="color:var(--pico-8-washed-grey)">Arc</span><<span style="color:var(--pico-8-washed-grey)">JobBuffer</span>>>, index: <span style="color:var(--pico-8-washed-grey)">usize</span><span style="color:var(--pico-8-cyan)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> <span style="color:var(--pico-8-washed-grey)">ris_os</span>::<span style="color:var(--pico-8-washed-grey)">affinity</span>::<span style="color:var(--pico-8-brown)">set_affinity</span><span style="color:var(--pico-8-green)">(</span>core_ids<span style="color:var(--pico-8-green)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-brown)">(<span style="color:var(--pico-8-cyan)">()</span>)</span> => <span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">trace!</span><span style="color:var(--pico-8-brown)">("set affinity <span style="color:var(--pico-8-cyan)">{:?}</span> for thread <span style="color:var(--pico-8-cyan)">{}</span>"</span>, core_ids, index<span style="color:var(--pico-8-brown)">)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-brown)">(</span>error<span style="color:var(--pico-8-brown)">)</span> => <span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">error!</span><span style="color:var(--pico-8-brown)">("couldn't set affinity for thread <span style="color:var(--pico-8-cyan)">{}</span>: <span style="color:var(--pico-8-cyan)">{}</span>"</span>, index, error<span style="color:var(--pico-8-brown)">)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> local_buffer = buffers<span style="color:var(--pico-8-green)">[</span>index<span style="color:var(--pico-8-green)">]</span>.<span style="color:var(--pico-8-brown)">clone</span><span style="color:var(--pico-8-green)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>steal_buffers</u> = <span style="color:var(--pico-8-washed-grey)">Vec</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">for</span> buffer <span style="color:var(--pico-8-pink)">in</span> buffers.<span style="color:var(--pico-8-brown)">iter</span><span style="color:var(--pico-8-green)">()</span>.<span style="color:var(--pico-8-brown)">skip</span><span style="color:var(--pico-8-green)">(</span>index + <span style="color:var(--pico-8-green)">1) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>steal_buffers</u>.<span style="color:var(--pico-8-brown)"><u>push</u>(</span>buffer.<span style="color:var(--pico-8-brown)">clone<span style="color:var(--pico-8-cyan)">()</span>)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">for</span> buffer <span style="color:var(--pico-8-pink)">in</span> buffers.<span style="color:var(--pico-8-brown)">iter</span><span style="color:var(--pico-8-green)">()</span>.<span style="color:var(--pico-8-brown)">take</span><span style="color:var(--pico-8-green)">(</span>index<span style="color:var(--pico-8-green)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>steal_buffers</u>.<span style="color:var(--pico-8-brown)"><u>push</u>(</span>buffer.<span style="color:var(--pico-8-brown)">clone<span style="color:var(--pico-8-cyan)">()</span>)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">WORKER_THREAD</span>.<span style="color:var(--pico-8-brown)">with</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-cyan)">move</span> |worker_thread| <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*worker_thread.<span style="color:var(--pico-8-brown)">borrow_mut</span><span style="color:var(--pico-8-cyan)">()</span> = <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">WorkerThread</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;local_buffer,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;steal_buffers,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;index,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><span style="color:var(--pico-8-green)">)</span>;<br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Setting up a worker thread is a bit more involved. First, we set the affinity. This is simply a wrapper around the Windows API, nothing special.</p>

<p>Then, we choose our local buffer. This is simply the buffer at the worker threads index. Meaning, the local buffer of thread 0 will be at index 0. The local buffer of thread 42 will be at index 42.</p>

<p>Using 2 for loops, we then get the buffers from which will be stolen from. We start at <code class="code">index + 1</code> and then wrap around. This diagram illustrates how the buffers will be arranged, for worker thread 2 on a machine with 5 cores:</p>

<img src="https://www.rismosch.com/articles/building-a-job-system/local_steal_buffers.webp" style="display: block; margin: auto; max-width: 100%;" />

<p>After the busy work, we then simply set the thread-local variable with the necessary information.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">run_worker_thread</span> <span style="color:var(--pico-8-cyan)">(</span>index: <span style="color:var(--pico-8-washed-grey)">usize</span>, done: <span style="color:var(--pico-8-washed-grey)">Arc</span><<span style="color:var(--pico-8-washed-grey)">AtomicBool</span>><span style="color:var(--pico-8-cyan)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">while</span> !done.<span style="color:var(--pico-8-brown)">load</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">Ordering</span>::<span style="color:var(--pico-8-washed-grey)">SeqCst</span><span style="color:var(--pico-8-green)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">run_pending_job()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">empty_buffer</span><span style="color:var(--pico-8-green)">(</span>index<span style="color:var(--pico-8-green)">)</span>;<br>
<span style="color:var(--pico-8-washed-grey)">}</span>
</code>

<p>Running the worker thread is very straight forward. While the <code class="code">done</code> flag is <code class="code">false</code>, we are stuck in an endless loop, running jobs that are still waiting to be executed. If the <code class="code">done</code> flag is <code class="code">true</code>, we empty the buffer, like the main thread does when <code class="code">JobSystemGuard</code> is dropped.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">pub fn <span style="color:var(--pico-8-brown)">run_pending_job</span>() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> <span style="color:var(--pico-8-brown)">pop_job</span><span style="color:var(--pico-8-green)">() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-brown)">(</span><span style="color:var(--pico-8-cyan)">mut</span> <u>job</u><span style="color:var(--pico-8-brown)">)</span> => <u>job</u>.<span style="color:var(--pico-8-brown)"><u>invoke</u>()</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-brown)">(</span><span style="color:var(--pico-8-washed-grey)">IsEmpty</span><span style="color:var(--pico-8-brown)">)</span> => <span style="color:var(--pico-8-pink)">match</span> <span style="color:var(--pico-8-brown)">steal_job() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-cyan)">(mut</span> <u>job</u><span style="color:var(--pico-8-cyan)">)</span> => <u>job</u>.<span style="color:var(--pico-8-brown)"><u>invoke</u></span><span style="color:var(--pico-8-cyan)">()</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">BlockedOrEmpty</span><span style="color:var(--pico-8-cyan)">)</span> => <span style="color:var(--pico-8-washed-grey)">thread</span>::<span style="color:var(--pico-8-brown)">yield_now</span><span style="color:var(--pico-8-cyan)">()</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>To run a pending job, we first pop from our local buffer. If it is successful, we invoke it. If our local buffer is empty, we steal from the other buffers instead. If this is successful, we just stole a job and can invoke it. If no job was popped or stolen, we yield, to give room for other threads.</p>

<p><code class="code">run_pending_job()</code> is public, because as you will see in the sections <i>JobFuture</i> and <i>JobCell</i>, it is quite useful to let others run jobs as well.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">empty_buffer</span><span style="color:var(--pico-8-cyan)">(</span>index: <span style="color:var(--pico-8-washed-grey)">usize</span><span style="color:var(--pico-8-cyan)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">loop</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">trace!</span><span style="color:var(--pico-8-brown)">("emptying <span style="color:var(--pico-8-cyan)">{}</span>"</span>, index<span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> <span style="color:var(--pico-8-brown)">pop_job() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-cyan)">(mut</span> <u>job</u><span style="color:var(--pico-8-cyan)">)</span> => <u>job</u>.<span style="color:var(--pico-8-brown)"><u>invoke</u></span><span style="color:var(--pico-8-cyan)">()</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">IsEmpty</span><span style="color:var(--pico-8-cyan)">)</span> => <span style="color:var(--pico-8-pink)">break</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Emptying the local buffer is also very straight forward. We are stuck in a loop, popping jobs from our local buffer and running them, until it's empty.</p>

<p>Now we've seen several functions that pop and steal jobs. So now it's time to show what these functions are doing in detail:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">pop_job</span><span style="color:var(--pico-8-cyan)">()</span> -> <span style="color:var(--pico-8-washed-grey)">Result</span><<span style="color:var(--pico-8-washed-grey)">Job</span>, <span style="color:var(--pico-8-washed-grey)">IsEmpty</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>result</u> = <span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">IsEmpty</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">WORKER_THREAD</span>.<span style="color:var(--pico-8-brown)">with</span><span style="color:var(--pico-8-green)">(</span>|worker_thread| <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span><u>worker_thread</u><span style="color:var(--pico-8-cyan)">)</span> = worker_thread.<span style="color:var(--pico-8-brown)">borrow_mut</span><span style="color:var(--pico-8-cyan)">()</span>.<span style="color:var(--pico-8-brown)"><u>as_mut</u></span><span style="color:var(--pico-8-cyan)">() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>result</u> = <u>worker_thread</u>.local_buffer.<span style="color:var(--pico-8-brown)">wait_and_pop</span><span style="color:var(--pico-8-green)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">} <span style="color:var(--pico-8-pink)">else</span> {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">error!</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-brown)">"couldn't pop job, calling thread isn't a worker thread"</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<u>result</u><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Popping literally does nothing except calling <code class="code">wait_and_pop()</code> of its local buffer. If for whatever reason this method is called from a non-worker thread, then this method returns nothing and prints an error instead.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">steal_job</span><span style="color:var(--pico-8-cyan)">()</span> -> <span style="color:var(--pico-8-washed-grey)">Result</span><<span style="color:var(--pico-8-washed-grey)">Job</span>, <span style="color:var(--pico-8-washed-grey)">BlockedOrEmpty</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>result</u> = <span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">BlockedOrEmpty</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">WORKER_THREAD</span>.<span style="color:var(--pico-8-brown)">with</span><span style="color:var(--pico-8-green)">(</span>|worker_thread| <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span><u>worker_thread</u><span style="color:var(--pico-8-cyan)">)</span> = worker_thread.<span style="color:var(--pico-8-brown)">borrow_mut</span><span style="color:var(--pico-8-brown)">()</span>.<span style="color:var(--pico-8-brown)"><u>as_mut</u></span><span style="color:var(--pico-8-cyan)">() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">for</span> buffer <span style="color:var(--pico-8-pink)">in</span> &<u>worker_thread</u>.steal_buffers<> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>result</u> = buffer.<span style="color:var(--pico-8-brown)">steal()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <u>result</u>.<span style="color:var(--pico-8-brown)">is_ok() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">break</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">} <span style="color:var(--pico-8-pink)">else</span> {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">error!</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-brown)">"couldn't steal job, calling thread isn't a worker thread"</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<u>result</u><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Stealing is pretty much the same, except that because we have a vector of buffers, we iterate through them. The first steal that succeeds breaks the iteration. Just like <code class="code">pop_job()</code>, if this is called from a non-worker thread somehow, this method will print an error.</p>

<p>With all the setting up, tearing down and dequeuing taken care of, let's look at the thread index:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">pub fn <span style="color:var(--pico-8-brown)">thread_index</span>()</span> -> <span style="color:var(--pico-8-washed-grey)">i32</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>result</u> = <span style="color:var(--pico-8-green)">-1</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">WORKER_THREAD</span>.<span style="color:var(--pico-8-brown)">with</span><span style="color:var(--pico-8-green)">(</span>|worker_thread| <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span>worker_thread<span style="color:var(--pico-8-cyan)">)</span> = worker_thread.<span style="color:var(--pico-8-brown)">borrow</span><span style="color:var(--pico-8-cyan)">()</span>.<span style="color:var(--pico-8-brown)">as_ref</span><span style="color:var(--pico-8-cyan)">() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>result</u> = worker_thread.index <span style="color:var(--pico-8-cyan)">as</span> <span style="color:var(--pico-8-washed-grey)">i32</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">} <span style="color:var(--pico-8-pink)">else</span> {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">error!</span><span style="color:var(--pico-8-green)">(<span style="color:var(--pico-8-brown)">"calling thread isn't a worker thread"</span>)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<u>result</u><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>This simply returns the thread-local index. If the calling thread is not a worker thread, <code class="code">-1</code> is returned and an error is printed.</p>

<p>I reserved the submit function for last, because it uses <code class="code">JobFuture</code>s, which I'll explain in the next section. But here it finally is:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">submit</span><<span style="color:var(--pico-8-washed-grey)">ReturnType</span>: <span style="color:var(--pico-8-cyan)">'static</span>, <span style="color:var(--pico-8-washed-grey)">F</span>: <span style="color:var(--pico-8-washed-grey)">FnOnce</span><span style="color:var(--pico-8-cyan)">()</span> -> <span style="color:var(--pico-8-washed-grey)">ReturnType</span> + <span style="color:var(--pico-8-cyan)">'static</span>><span style="color:var(--pico-8-cyan)">(</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;job: <span style="color:var(--pico-8-washed-grey)">F</span>,<br>
<span style="color:var(--pico-8-cyan)">)</span> -> <span style="color:var(--pico-8-washed-grey)">JobFuture</span><<span style="color:var(--pico-8-washed-grey)">ReturnType</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>not_pushed</u> = <span style="color:var(--pico-8-washed-grey)">None</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-green)">(</span>settable_future, future<span style="color:var(--pico-8-green)">)</span> = <span style="color:var(--pico-8-washed-grey)">SettableJobFuture</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> job = <span style="color:var(--pico-8-washed-grey)">Job</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-cyan)">move</span> || <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> result = job<span style="color:var(--pico-8-cyan)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;settable_future.<span style="color:var(--pico-8-brown)">set</span><span style="color:var(--pico-8-cyan)">(</span>result<span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">WORKER_THREAD</span>.<span style="color:var(--pico-8-brown)">with</span><span style="color:var(--pico-8-green)">(</span>|worker_thread| <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span><u>worker_thread</u><span style="color:var(--pico-8-cyan)">)</span> = worker_thread.<span style="color:var(--pico-8-brown)">borrow_mut</span><span style="color:var(--pico-8-cyan)">()</span>.<span style="color:var(--pico-8-brown)"><u>as_mut</u></span><span style="color:var(--pico-8-cyan)">() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> <u>worker_thread</u>.local_buffer.<span style="color:var(--pico-8-brown)">push</span><span style="color:var(--pico-8-green)">(</span>job<span style="color:var(--pico-8-green)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-brown)">(<span style="color:var(--pico-8-cyan)">()</span>) <span style="color:var(--pico-8-black)">=></span> ()</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-brown)">(</span>blocked_or_full<span style="color:var(--pico-8-brown)">)</span> => <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>not_pushed</u> = <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span>blocked_or_full.not_pushed<span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">} <span style="color:var(--pico-8-pink)">else</span> {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">ris_log</span>::<span style="color:var(--pico-8-cyan)">error!</span><span style="color:var(--pico-8-green)">(<span style="color:var(--pico-8-brown)">"couldn't submit job, calling thread isn't a worker thread"</span>)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-cyan)">mut</span> <u>to_invoke</u><span style="color:var(--pico-8-green)">)</span> = <u>not_pushed</u> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>to_invoke</u>.<span style="color:var(--pico-8-brown)"><u>invoke</u>()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;future<br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>This function takes a closure, that can return a value. This is the job to be run. And it returns a <code class="code">JobFuture</code>, which can return the jobs returned value. You may remember, that <code class="code">Job</code> was a very very simple wrapper around a function pointer, <b>which doesn't even return anything</b>. That is intentional, to make my life easier. Instead of handing the closure directly into the job, and thus forcing me to somehow accommodate for its return value, it's much easier to simply wrap the closure into another closure, that doesn't return anything.</p>

<p><code class="code">submit()</code> first creates the future. This includes a <code class="code">SettableJobFuture</code> and a <code class="code">JobFuture</code>. Soon you will see, that they share a mutex, but the split between "settable" and "non-settable" prevents client code to set the future themselves.</p>

<p>Then it creates said closure, which sets the future with its return value. After that, we simply call <code class="code">push()</code> from our thread-local local buffer. Recall that pushing returns the job, when the buffer is full. In that case, we store it in <code class="code">not_pushed</code> and invoke it later. At the very end, we return the non-settable <code class="code">JobFuture</code>, so that the client may wait on the job that was just submitted.</p>

<p>If you read this far, and understood everything, I have to commemorate you. To come to this point, I've read several books already. Even though this blogpost is already incredibly long, it still condenses a lot of information, and I seriously applaud you if you could follow along with everything. But we aren't done yet, there are still 2 things that we need to talk about.</p>

<h2>JobFuture</h2>

<p>Submitting a job is an asynchronous operation. Submitting takes O(1) time, but the job itself will be executed on any core in some arbitrary point in the future. But what if you need to wait for the job to continue? For example, my Input system runs mouse, keyboard and gamepad in parallel, but my remapping system requires that these 3 systems are already computed. So before any remapping can take place, the input job needs to wait for the 3 children.</p>

<p>This is what the <code class="code">JobFuture</code> is for. It gives client code a handle, which can be awaited. As you've already seen, it is split into a <code class="code">SettableJobFuture</code> and a non-settable <code class="code">JobFuture</code>. Let's just jump straight into it!</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">struct</span> <span style="color:var(--pico-8-washed-grey)">Inner</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;is_ready: <span style="color:var(--pico-8-washed-grey)">bool</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;data: <span style="color:var(--pico-8-washed-grey)">Option</span><<span style="color:var(--pico-8-washed-grey)">T</span>>,<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">type</span> <span style="color:var(--pico-8-washed-grey)">InnerPtr</span><<span style="color:var(--pico-8-washed-grey)">T</span>> = <span style="color:var(--pico-8-washed-grey)">Arc</span><<span style="color:var(--pico-8-washed-grey)">Mutex</span><<span style="color:var(--pico-8-washed-grey)">Inner</span><<span style="color:var(--pico-8-washed-grey)">T</span>>>>;<br>
<br>
<span style="color:var(--pico-8-cyan)">pub struct</span> <span style="color:var(--pico-8-washed-grey)">SettableJobFuture</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;inner: <span style="color:var(--pico-8-washed-grey)">InnerPtr</span><<span style="color:var(--pico-8-washed-grey)">T</span>>,<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">pub struct</span> <span style="color:var(--pico-8-washed-grey)">JobFuture</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;inner: <span style="color:var(--pico-8-washed-grey)">InnerPtr</span><<span style="color:var(--pico-8-washed-grey)">T</span>>,<br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p><code class="code">Inner</code> is simply the data to be stored. It stores a bool <code class="code">is_ready</code>, which indicates whether the job is finished or not. <code class="code">data</code> is the value, which was returned by the job. It is stored inside an <code class="code">Option</code>, because sooner or later we need to move the data out. The Future can be in these 4 states:</p>

<table class="future_table">
    <tr>
        <td class="future_table_th">is_ready</td>
        <td class="future_table_th">data</td>
        <td class="future_table_th">state</td>
    </tr>
    <tr>
        <td class="future_table_td">false</td>
        <td class="future_table_td">None</td>
        <td class="future_table_td">Job is not done</td>
    </tr>
    <tr>
        <td class="future_table_td">true</td>
        <td class="future_table_td">Some</td>
        <td class="future_table_td">Job is done</td>
    </tr>
    <tr>
        <td class="future_table_td">true</td>
        <td class="future_table_td">None</td>
        <td class="future_table_td">Job is done, and data was moved out</td>
    </tr>
    <tr>
        <td class="future_table_td">false</td>
        <td class="future_table_td">Some</td>
        <td class="future_table_td">close eyes, cover ears and run screaming in circles</td>
    </tr>
</table>

<p><code class="code">SettableJobFuture</code> and <code class="code">JobFuture</code> literally store the same: An <code class="code">Arc&lt;Mutex&lt;Inner&lt;T&gt;&gt;&gt;</code>. Thus, they are virtually identical. Because they are different structs however, they can implement different methods. For example, only <code class="code">SettableJobFuture</code> has a constructor, and it returns both a <code class="code">SettableJobFuture</code> and a <code class="code">JobFuture</code>. Also, only <code class="code">SettableJobFuture</code> can set its value. <code class="code">JobFuture</code> can wait on a value, but <code class="code">SettableJobFuture</code> cannot. Let's see:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-washed-grey)">SettableJobFuture</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">()</span> -> <span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">SettableJobFuture</span><<span style="color:var(--pico-8-washed-grey)">T</span>>, <span style="color:var(--pico-8-washed-grey)">JobFuture</span><<span style="color:var(--pico-8-washed-grey)">T</span>><span style="color:var(--pico-8-green)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> inner = <span style="color:var(--pico-8-washed-grey)">Arc</span>::<span style="color:var(--pico-8-brown)">new(</span><span style="color:var(--pico-8-washed-grey)">Mutex</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">Inner</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;is_ready: <span style="color:var(--pico-8-cyan)">false</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;data: <span style="color:var(--pico-8-washed-grey)">None</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><span style="color:var(--pico-8-cyan)">)</span><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> settable_job_future = <span style="color:var(--pico-8-washed-grey)">SettableJobFuture</span> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;inner: inner.<span style="color:var(--pico-8-brown)">clone</span><span style="color:var(--pico-8-cyan)">()</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> job_future = <span style="color:var(--pico-8-washed-grey)">JobFuture</span> <span style="color:var(--pico-8-brown)">{</span> inner <span style="color:var(--pico-8-brown)">}</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">(</span>settable_job_future, job_future<span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">set</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-cyan)">self</span>, result: <span style="color:var(--pico-8-washed-grey)">T</span><span style="color:var(--pico-8-green)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>inner</u> = <span style="color:var(--pico-8-cyan)">unwrap_or_throw!</span>(<span style="color:var(--pico-8-cyan)">self</span>.inner.<span style="color:var(--pico-8-brown)">lock</span><span style="color:var(--pico-8-cyan)">()</span>, <span style="color:var(--pico-8-brown)">"couldn't set job future")</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>inner</u>.is_ready = <span style="color:var(--pico-8-cyan)">true</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>inner</u>.data = <span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-brown)">(</span>result<span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p><code class="code">SettableJobFuture</code> is very straight forward. It initializes the <code class="code">Arc&lt;Mutex&lt;Inner&lt;T&gt;&gt;&gt;</code> which will be shared between the two structs. It then creates each struct and returns them. <code class="code">set()</code> simply locks the mutex, sets <code class="code">is_ready</code> to <code class="code">true</code>, and overwrites <code class="code">data</code>. Notice how <code class="code">set()</code> consumes <code class="code">self</code>. This directly means that once <code class="code">SettableJobFuture</code> is changed, it might as well be dead.</p>

<p>Let's look at the await-able <code class="code">JobFuture</code> next:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-washed-grey)">JobFuture</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">wait</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-cyan)">mut <u>self</u></span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-washed-grey)">T</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> <span style="color:var(--pico-8-cyan)"><u>self</u></span>.<span style="color:var(--pico-8-brown)"><u>wait_and_take</u>() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Some</span><span style="color:var(--pico-8-cyan)">(</span>value<span style="color:var(--pico-8-cyan)">)</span> => value,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">None</span> => <span style="color:var(--pico-8-cyan)">unreachable!()</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)"><u>wait_and_take</u></span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">mut <u>self</u></span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-washed-grey)">Option</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">loop</span> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">match</span> <span style="color:var(--pico-8-cyan)"><u>self</u></span>.inner.<span style="color:var(--pico-8-brown)">try_lock</span><span style="color:var(--pico-8-cyan)">() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ok</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-cyan)">mut</span> <u>inner</u><span style="color:var(--pico-8-green)">)</span> => <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <u>inner</u>.is_ready <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">return</span> <u>inner</u>.data.<span style="color:var(--pico-8-brown)"><u>take</u></span><span style="color:var(--pico-8-cyan)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Err</span><span style="color:var(--pico-8-green)">(</span>e<span style="color:var(--pico-8-green)">)</span> => <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">if</span> <span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-washed-grey)">TryLockError</span>::<span style="color:var(--pico-8-washed-grey)">Poisoned</span><span style="color:var(--pico-8-brown)">(</span>e<span style="color:var(--pico-8-brown)">)</span> = e <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">throw!(</span><span style="color:var(--pico-8-brown)">"couldn't take job future: <span style="color:var(--pico-8-cyan)">{}</span>"</span>, e<span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">job_system</span>::<span style="color:var(--pico-8-brown)">run_pending_job</span><span style="color:var(--pico-8-cyan)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Just like <code class="code">set()</code> in <code class="code">SettableJobFuture</code>, <code class="code">wait()</code> also consumes self. This results in the fact, that taking <code class="code">data</code> will always succeed. We can't <code class="code">wait()</code> a second time, when it was awaited already. This is why the <code class="code">None</code> branch of <code class="code">wait()</code> includes an <code class="code">unreachable!()</code> and why <code class="code">wait()</code> can afford to return the value directly, instead of returning an <code class="code">Option</code>.</p>

<p><code class="code">wait()</code> calls <code class="code">wait_and_take()</code>, which spinlocks until <code class="code">data</code> is set. Considering the reactions I got from a <a href="https://www.rismosch.com/article?id=running-test-in-series" target="_blank" rel="noopener noreferrer">previous blogpost</a>, people somehow seem to hate spinlocks and immediately consider them unsafe. But let me assure you, that without a spinlock, this future wouldn't work.</p>

<p>Assume for a second, that <code class="code">wait()</code> uses a locking mechanism, like a condition variable or a mutex. In that case, <b>waiting for the future would block the entire thread</b>. This is problematic, because a blocked worker thread cannot run jobs. Ideally, the worker thread would do other things while the calling job is waiting. As such, a spinlock-like construct is the best choice for implementing such behavior.</p>

<p>This is what <code class="code">wait_and_take()</code> does: It first calls <code class="code">try_lock()</code> on the mutex, which isn't a blocking operation. If the lock was successful and the future was set, we can take the <code class="code">data</code> inside. If the lock was not successful, because it was poisoned, then this is unrecoverable and we throw an error. If the future was not set, or the lock resulted in a <code class="code">TryLockError::WouldBlock</code> error, then we run a pending job and attempt the whole procedure again.</p>

<p>In a nutshell, <code class="code">wait()</code> will be stuck in a loop, running pending jobs, as long as the job is not completed, thus effectively blocking the calling job and progressing the entire job system. With this taken care of, what else might be missing from this job system? Well...</p>

<h2>JobCell</h2>

<p>At this point, I thought I was done with the job system. I seriously didn't expect that Rusts borrow checker would be a major killjoy. I've spent already a lot of time and effort to get to this point. But then I tried using it in my main loop, and the compiler simply said no.</p>

<img src="https://www.rismosch.com/articles/building-a-job-system/borrow_checker_meme.webp" style="display: block; margin: auto; max-width: 100%;" />

<p>The entire purpose of jobs is that they can take stuff, like objects or references, and then do work on them. A job will probably mutate data, or simply just immutably reference it to generate new state. But here's the last obstacle: Because the entire job system stores everything statically, thanks to its singleton-like nature, everything that is moved into a job must be statically borrowed. Something that is locally owned or referenced cannot be borrowed for a static lifetime. As much as the compiler is concerned, if a reference is moved into a job, it enters the nirvana, never to return. And the compiler simply doesn't allow that.</p>

<p>A workaround must be found.</p>

<p>To overcome this problem, I really had to scrape the bottom of the barrel. I have tested numerous solutions, all of which weren't satisfactory. Either I'd took a major performance hit, or the solution would panic randomly, or I'd introduce undefined behavior. Nothing worked, and prototype after prototype failed.</p>

<p>Before you say it, no, <code class="code">Arc&lt;Mutex&lt;T&gt;&gt;</code> does <b>NOT</b> work. Remember what I've written in the previous section <i>JobFuture</i>: If a mutex were to block, then the entire worker thread blocks, meaning no progress will be made. I could write a spinlock around <code class="code">try_lock()</code>, but this really isn't practical. Even if I were to create an elegant interface to spinlock a mutex, a spinlock still adds major overhead, which simply isn't worth it for just passing a single value into a job. This is especially true when I plan to spawn numerous jobs, all of which may reference their outside in one way or another.</p>

<p>What I want is an easy way to pass things into a job, with no overhead at all. Eventually, this led me to <code class="code">UnsafeCell</code>, and a much much deeper understanding of how Rusts ownership rules work.</p>

<p>First, let's tackle mutable references, because as you will see shortly, these are actually quite easy to work around. I was expecting that these are the difficult ones, but with a little hack they were pretty trivial to solve. Instead of passing a mutable reference into a job, you can simply pass <i>ownership</i> into the job.</p>

<p>Test your knowledge! Does the following code compile?</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">struct <span style="color:var(--pico-8-washed-grey)">MyInt</span> {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;value: <span style="color:var(--pico-8-washed-grey)">i32</span>,<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">struct</span> <span style="color:var(--pico-8-washed-grey)">Wrapper</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">MyInt</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
<br>
<span style="color:var(--pico-8-cyan)">fn <span style="color:var(--pico-8-brown)">consume</span>(</span>_: <span style="color:var(--pico-8-washed-grey)">MyInt</span><span style="color:var(--pico-8-cyan)">) {}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">fn <span style="color:var(--pico-8-brown)">main</span>() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> wrapper = <span style="color:var(--pico-8-washed-grey)">Wrapper</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">MyInt</span> <span style="color:var(--pico-8-brown)">{</span> value: <span style="color:var(--pico-8-washed-grey)">42 <span style="color:var(--pico-8-brown)">}</span>)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">consume</span><span style="color:var(--pico-8-green)">(</span>wrapper.0<span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">println!</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-brown)">"my current value is: <span style="color:var(--pico-8-cyan)">{}</span>"</span>, wrapper.0.value<span style="color:var(--pico-8-green)">)</span>;<br>
<span style="color:var(--pico-8-washed-grey)">}</span>
</code>

<p>Hover to reveal the answer:</p>

<p class="spoiler">No, this doesn't compile. That should come as no surprise. We are moving a value out of <code class="code">wrapper</code>! If you are accustomed to Rust, this should stick out like a sore thumb, and you would immediately realize that passing a <code class="code">&mut</code> into <code class="code">consume()</code> is probably what you wanted in the first place.</p>

<p>But what about this one? Does this compile?</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">struct <span style="color:var(--pico-8-washed-grey)">MyInt</span> {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;value: <span style="color:var(--pico-8-washed-grey)">i32</span>,<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">struct</span> <span style="color:var(--pico-8-washed-grey)">Wrapper</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">MyInt</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
<br>
<span style="color:var(--pico-8-cyan)">fn <span style="color:var(--pico-8-brown)">consume</span>(</span>_: <span style="color:var(--pico-8-washed-grey)">MyInt</span><span style="color:var(--pico-8-cyan)">) {}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">fn <span style="color:var(--pico-8-brown)">main</span>() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> wrapper = <span style="color:var(--pico-8-washed-grey)">Wrapper</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">MyInt</span> <span style="color:var(--pico-8-brown)">{</span> value: <span style="color:var(--pico-8-washed-grey)">42 <span style="color:var(--pico-8-brown)">}</span>)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">consume</span><span style="color:var(--pico-8-green)">(</span>wrapper.0<span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;wrapper.0 = <span style="color:var(--pico-8-washed-grey)">MyInt</span> <span style="color:var(--pico-8-green)">{</span> value: <span style="color:var(--pico-8-green)">-13 }</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">println!</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-brown)">"my current value is: <span style="color:var(--pico-8-cyan)">{}</span>"</span>, wrapper.0.value<span style="color:var(--pico-8-green)">)</span>;<br>
<span style="color:var(--pico-8-washed-grey)">}</span>
</code>

<p>Hover to reveal the answer:</p>

<p class="spoiler">Yes, this does actually compile. This surprised me when I first saw this. We are still moving a value out of <code class="code">wrapper</code>, just like the first code snippet, so why does it compile? The reason why the first one didn't compile is not really because we moved something out. Rather, we tried to <i>use</i> a value which was already moved. If you remove the <code class="code">println!()</code> in the first example, we are not using <code class="code">wrapper</code> after its move and the code compiles just fine. This implies that it's A-okay to move child values out from a parent. We just gotta replace it before we use it again. Instead of replacing it with an arbitrary value, we can replace it with the value we moved out! Remember that the <code class="code">JobFuture</code> allows us to return something from a job. Any ownership that we may pass into a job we can return afterwards.</p>

<p>To illustrate this technique, here's an excerpt of my input logic, showcasing the part of the keyboard job:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)"><u>run</u></span><span style="color:var(--pico-8-green)">(</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&<span style="color:var(--pico-8-cyan)">mut <u>self</u></span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">mut</span> <u>current</u>: <span style="color:var(--pico-8-washed-grey)">InputData</span>, <span style="color:var(--pico-8-green)">//&#127312;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;previous: <span style="color:var(--pico-8-washed-grey)">Ref</span><<span style="color:var(--pico-8-washed-grey)">InputData</span>>, <span style="color:var(--pico-8-green)">//&#127313;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;_frame: <span style="color:var(--pico-8-washed-grey)">Ref</span><<span style="color:var(--pico-8-washed-grey)">FrameData</span>>,<br>
<span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">InputData</span>, <span style="color:var(--pico-8-washed-grey)">GameloopState</span><span style="color:var(--pico-8-green)">) {</span> <span style="color:var(--pico-8-green)">//&#127314;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> current_keyboard = <u>current</u>.keyboard; <span style="color:var(--pico-8-green)">//&#127315;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> keyboard_future = <span style="color:var(--pico-8-washed-grey)">job_system</span>::<span style="color:var(--pico-8-brown)">submit(</span><span style="color:var(--pico-8-cyan)">move</span> || <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>keyboard</u> = current_keyboard; <span style="color:var(--pico-8-green)">//&#127316;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> gameloop_state = <span style="color:var(--pico-8-brown)">update_keyboard</span><span style="color:var(--pico-8-green)">(</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&<span style="color:var(--pico-8-cyan)">mut</span> <u>keyboard</u>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">(<span style="color:var(--pico-8-black)"><u>keyboard</u>, gameloop_state</span>)</span> <span style="color:var(--pico-8-green)">//&#127317;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-brown)">(</span>new_keyboard, new_gameloop_state<span style="color:var(--pico-8-brown)">)</span> = keyboard_future.<span style="color:var(--pico-8-brown)">wait()</span>; <span style="color:var(--pico-8-green)">//&#127318;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<u>current</u>.keyboard = new_keyboard; <span style="color:var(--pico-8-green)">//&#127319;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">(</span><u>current</u>, new_gameloop_state<span style="color:var(--pico-8-brown)">)</span><br>
<span style="color:var(--pico-8-green)">}</span>
</code>

<p>Ignore <code class="code">Ref</code> for a moment &#127313;. It most definitely is not the <code class="code">Ref</code> you are currently thinking about. We will talk about it in a second.</p>

<p>Notice how the <code class="code">run()</code> function takes ownership of an <code class="code">InputData</code> object &#127312;. It also returns an <code class="code">InputData</code> object &#127314;. This method alone is already demonstrating the technique. It takes ownership of <code class="code">InputData</code> and later returns it. The first thing we do is to pass ownership of <code class="code">current.keyboard</code> to a local variable &#127315;. Then we move the local variable and ownership into the job &#127316;. Now the job owns the keyboard and can mutate it however it wants. With an additional <code class="code">gameloop_state</code>, the job also returns the keyboard &#127317;, such that when the job is awaited, the caller once again has the ownership of <code class="code">keyboard</code> in a local variable &#127318;. To satisfy the compiler, we move this local variable <code class="code">new_keyboard</code> back into <code class="code">current.keyboard</code> &#127319;.</p>

<img src="https://www.rismosch.com/articles/building-a-job-system/ownership_diagram.webp" style="display: block; margin: auto; max-width: 100%;" />

<p>No ownership rules have been broken, and yet a job was fully able to mutate the value. Notice how in the diagram above, no green "owns keyboard" blocks overlap on the y axis. At every single point in time, there exists exactly one owner of <code class="code">keyboard</code>, and as such the compiler is happy with this code.</p>

<br><br>

<p>This is cool, but why is this section called <code class="code">JobCell</code>? Apparently, we don't even need a <code class="code">Cell</code>-like structure to move things into a job! Up until this point I've talked about moving mutable values. But immutable references ain't so easy.</p>

<p>Immutable references are implicitly <code class="code">Copy</code>. That means that if a single immutable reference exists, you can make as many immutable references as you like. We may pass a single immutable reference into a job, but returning it is fruitless. The job may create God knows how many references from it. A single return can never accommodate for all references that have been created. To say it bluntly: <code class="code">&</code> is forbidden. No amount of ownership tricks can change this.</p>

<p>So we need a <code class="code">Cell</code>-like struct afterall, that keeps track of all references somehow. Let me introduce my <code class="code">JobCell</code>:</p>

<img src="https://www.rismosch.com/articles/building-a-job-system/jobcell.webp" style="display: block; margin: auto; max-width: 100%;" />

<p>This <code class="code">JobCell</code> can be in two states: Either simply <code class="code">JobCell</code>, which allows you to make as many immutable references as you like. Or <code class="code">MutableJobCell</code>, which allows you to mutate its content. To switch between the two states, some extra logic is required:</p>

<p>To switch from <code class="code">JobCell</code> to <code class="code">MutableJobCell</code>, all immutable references must be dead. To do this, we spinlock and call pending jobs as long as immutable references exist. This already imposes a restriction: Immutable references, created by the <code class="code">JobCell</code>, are only allowed to be passed into a Job. If you were to create an immutable reference in the same thread where you want to mutate data, or attempt to store the reference somewhere indefinitely, then switching to the mutable state will <i>livelock</i>. A livelock is similar to a deadlock, except it is using up resources by spinning, instead of just going to sleep like a deadlock.</p>

<p>But if this restriction is taken care of, the spinlock ends and a <code class="code">MutableJobCell</code> is created. To switch back to <code class="code">JobCell</code>, all you have to do is to drop <code class="code">MutableJobCell</code>. Now here comes a trick: Since <code class="code">MutableJobCell</code> borrows <code class="code">JobCell</code> mutably, the Rust compiler forbids you to use <code class="code">JobCell</code> while <code class="code">MutableJobCell</code> lives. This means as long as the <code class="code">JobCell</code> is in its mutable state, no immutable references can be created. Not via <code class="code">JobCell</code>s interface, nor via Rusts default reference mechanics.</p>

<p>Enough theory, let's look at some code:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">pub struct</span> <span style="color:var(--pico-8-washed-grey)">JobCell</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;value: <span style="color:var(--pico-8-washed-grey)">UnsafeCell</span><<span style="color:var(--pico-8-washed-grey)">T</span>>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;refs: <span style="color:var(--pico-8-washed-grey)">Arc</span><<span style="color:var(--pico-8-washed-grey)">AtomicUsize</span>>,<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">pub struct</span> <span style="color:var(--pico-8-washed-grey)">MutableJobCell</span><<span style="color:var(--pico-8-cyan)">'a</span>, <span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;value: &<span style="color:var(--pico-8-cyan)">'a</span> <span style="color:var(--pico-8-washed-grey)">UnsafeCell</span><<span style="color:var(--pico-8-washed-grey)">T</span>>,<br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">pub struct</span> <span style="color:var(--pico-8-washed-grey)">Ref</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;value: <span style="color:var(--pico-8-washed-grey)">NonNull</span><<span style="color:var(--pico-8-washed-grey)">T</span>>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;refs: <span style="color:var(--pico-8-washed-grey)">Arc</span><<span style="color:var(--pico-8-washed-grey)">AtomicUsize</span>>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;_boo: <span style="color:var(--pico-8-washed-grey)">PhantomData</span><<span style="color:var(--pico-8-washed-grey)">T</span>>,<br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Here we have all structs to make the magic work. <code class="code">JobCell</code> is a simple wrapper around <code class="code">UnsafeCell</code>, but it additionally keeps count of its current references via an <code class="code">AtomicUsize</code>. As mentioned previously, <code class="code">MutableJobCell</code> stores a <i>reference</i> to an <code class="code">UnsafeCell</code>, instead of straight up owning it. This directly leads to the mechanism, that <code class="code">JobCell</code> cannot be used while <code class="code">MutableJobCell</code> lives. Finally, we need to mimic <code class="code">&</code>, by wrapping a pointer into a struct called <code class="code">Ref</code>. Since this wrapper is its own thing, and syntactically independent from <code class="code">JobCell</code>, the compiler thinks that client code owns it. To ensure that <code class="code">Ref</code> mimics an immutable reference, only <code class="code">Deref</code> will be implemented on it. It also doesn't implement any methods that could mutate its state in any way. Additionally, <code class="code">Ref</code> stores a counter of all alive references. This counter is increased when <code class="code">Ref</code> is being cloned, or decreased when <code class="code">Ref</code> is being dropped. And because of some compiler optimization aliasing safety shenanigans tic-tac-toe i-don't-know-what-i-am-talking-about, <code class="code">Ref</code> must also store a <code class="code">PhantomData</code>.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-washed-grey)">JobCell</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">/// ⚠️ don't put this in an `Rc<T>` or `Arc<T>` ⚠️</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">///</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">/// this cell is intended to have only one owner, who can mutate it</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span>value: <span style="color:var(--pico-8-washed-grey)">T</span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-cyan)">Self</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">Self</span> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;value: <span style="color:var(--pico-8-washed-grey)">UnsafeCell</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-cyan)">(</span>value<span style="color:var(--pico-8-cyan)">)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;refs: <span style="color:var(--pico-8-washed-grey)">Arc</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">AtomicUsize</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(0)</span><span style="color:var(--pico-8-cyan)">)</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">/// ⚠️ this method **WILL** livelock, when not all created `Ref<T>`s are dropped ⚠️</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)"><u>as_mut</u></span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-washed-grey)">mut <u>self</u></span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-washed-grey)">MutableJobCell</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-washed-grey)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">while</span> <span style="color:var(--pico-8-washed-grey)"><u>self</u></span>.refs.<span style="color:var(--pico-8-brown)">load(</span><span style="color:var(--pico-8-washed-grey)">Ordering</span>::<span style="color:var(--pico-8-washed-grey)">SeqCst</span><span style="color:var(--pico-8-brown)">)</span> > <span style="color:var(--pico-8-green)">0</span> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">job_system</span>::<span style="color:var(--pico-8-brown)">run_pending_job</span><span style="color:var(--pico-8-cyan)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">MutableJobCell</span> <span style="color:var(--pico-8-brown)">{</span> value: &<span style="color:var(--pico-8-cyan)"><u>self</u></span>.value <span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">borrow</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-washed-grey)">Ref</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.refs.<span style="color:var(--pico-8-brown)">fetch_add(</span><span style="color:var(--pico-8-green)">1</span>, <span style="color:var(--pico-8-washed-grey)">Ordering</span>::<span style="color:var(--pico-8-washed-grey)">SeqCst</span><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> value = <span style="color:var(--pico-8-cyan)">unsafe</span> <span style="color:var(--pico-8-brown)">{</span> <span style="color:var(--pico-8-washed-grey)">NonNull</span>::<span style="color:var(--pico-8-brown)">new_unchecked</span><span style="color:var(--pico-8-cyan)">(self</span>.value.<span style="color:var(--pico-8-brown)">get</span><span style="color:var(--pico-8-green)">()</span><span style="color:var(--pico-8-cyan)">)</span> <span style="color:var(--pico-8-brown)">}</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">Ref</span> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;value,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;refs: <span style="color:var(--pico-8-cyan)">self</span>.refs.clone<span style="color:var(--pico-8-cyan)">()</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_boo: <span style="color:var(--pico-8-washed-grey)">PhantomData</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Like I have said a thousand times already, the constructor should be fairly self-explanatory. It creates the <code class="code">UnsafeCell</code> and the reference counter.</p>

<p>Since we went over the theory already, <code class="code">as_mut()</code> should also be fairly straight forward. In a spinlock it constantly checks whether any references exist, and it runs pending jobs if they do. Only when no references exist, a <code class="code">MutableJobCell</code> is created and returned.</p>

<p>If you've worked with Rusts pointers before, <code class="code">borrow()</code> shouldn't be that difficult to understand either. But before we do any pointer magic, we increase the reference count by 1, indicating that from now on, an immutable reference exists. Then, we create a pointer to the data of the <code class="code">UnsafeCell</code>. This value is never modified, or dereferenced mutably, so this is safe. Then we simply construct the <code class="code">Ref</code> struct and return it.</p>

<p>To make my life easier, <code class="code">MutableJobCell</code> implements one additional function:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-washed-grey)">MutableJobCell</span><<span style="color:var(--pico-8-cyan)">'_</span>, <span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)"><u>replace</u></span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">mut <u>self</u></span>, value: <span style="color:var(--pico-8-washed-grey)">T</span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-washed-grey)">T</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-washed-grey)">mem</span>::<span style="color:var(--pico-8-brown)"><u>replace</u></span>(&<span style="color:var(--pico-8-cyan)">mut</span> *<span style="color:var(--pico-8-cyan)"><u>self</u></span>, value<span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>This is a simple wrapper to replace the inner value of the <code class="code">JobCell</code>.</p>

<p>Now let's implement some traits!</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-washed-grey)">Deref</span> <span style="color:var(--pico-8-cyan)">for</span> <span style="color:var(--pico-8-washed-grey)">MutableJobCell</span><<span style="color:var(--pico-8-cyan)">'_</span>, <span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">type</span> <span style="color:var(--pico-8-washed-grey)">Target</span> = <span style="color:var(--pico-8-washed-grey)">T</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">deref</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-green)">)</span> -> &<span style="color:var(--pico-8-cyan)">Self</span>::<span style="color:var(--pico-8-washed-grey)">Target</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">unsafe</span> <span style="color:var(--pico-8-brown)">{</span> &*<span style="color:var(--pico-8-cyan)">self</span>.value.<span style="color:var(--pico-8-brown)">get</span><span style="color:var(--pico-8-cyan)">()</span> <span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">impl</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-washed-grey)">DerefMut</span> <span style="color:var(--pico-8-cyan)">for</span> <span style="color:var(--pico-8-washed-grey)">MutableJobCell</span><<span style="color:var(--pico-8-cyan)">'_</span>, <span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)"><u>deref_mut</u></span>(&<span style="color:var(--pico-8-cyan)">mut <u>self</u></span><span style="color:var(--pico-8-green)">)</span> -> &<span style="color:var(--pico-8-cyan)">mut Self</span>::<span style="color:var(--pico-8-washed-grey)">Target</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">unsafe</span> <span style="color:var(--pico-8-brown)">{</span> &<span style="color:var(--pico-8-cyan)">mut</span> *<span style="color:var(--pico-8-cyan)"><u>self</u></span>.value.<span style="color:var(--pico-8-brown)">get</span><span style="color:var(--pico-8-cyan)">()</span> <span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p><code class="code">Deref</code> and <code class="code">DerefMut</code> for <code class="code">MutableJobCell</code> are quite obvious, because we want to access and mutate the underlying data.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-washed-grey)">Deref</span> <span style="color:var(--pico-8-cyan)">for</span> <span style="color:var(--pico-8-washed-grey)">Ref</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">type</span> <span style="color:var(--pico-8-washed-grey)">Target</span> = <span style="color:var(--pico-8-washed-grey)">T</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">deref</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-green)">)</span> -> &<span style="color:var(--pico-8-cyan)">Self</span>::<span style="color:var(--pico-8-washed-grey)">Target</span> <span style="color:var(--pico-8-green)">{</span></><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">unsafe</span> <span style="color:var(--pico-8-brown)">{</span> <span style="color:var(--pico-8-cyan)">self</span>.value.<span style="color:var(--pico-8-brown)">as_ref</span><span style="color:var(--pico-8-cyan)">()</span> <span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p><code class="code">Deref</code> for <code class="code">Ref</code> is also a no brainer. Very important: <code class="code">Ref</code> does NOT implement <code class="code">DerefMut</code>, otherwise this would be undefined behavior galore!</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-washed-grey)">Clone</span> <span style="color:var(--pico-8-cyan)">for</span> <span style="color:var(--pico-8-washed-grey)">Ref</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">clone</span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">self</span><span style="color:var(--pico-8-green)">)</span> -> <span style="color:var(--pico-8-cyan)">Self</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">self</span>.refs.<span style="color:var(--pico-8-brown)">fetch_add(</span><span style="color:var(--pico-8-green)">1</span>, <span style="color:var(--pico-8-washed-grey)">Ordering</span>::<span style="color:var(--pico-8-washed-grey)">SeqCst</span><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">Self</span> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;value: <span style="color:var(--pico-8-cyan)">self</span>.value,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;refs: <span style="color:var(--pico-8-cyan)">self</span>.refs.<span style="color:var(--pico-8-brown)">clone</span><span style="color:var(--pico-8-cyan)">()</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_boo: <span style="color:var(--pico-8-washed-grey)">PhantomData</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>To clone a <code class="code">Ref</code>, we simply increase the reference counter and return a new <code class="code">Ref</code> with the same data.</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">impl</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-washed-grey)">Drop</span> <span style="color:var(--pico-8-cyan)">for</span> <span style="color:var(--pico-8-washed-grey)">Ref</span><<span style="color:var(--pico-8-washed-grey)">T</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)"><u>drop</u></span><span style="color:var(--pico-8-green)">(</span>&<span style="color:var(--pico-8-cyan)">mut <u>self</u></span><span style="color:var(--pico-8-green)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)"><u>self</u></span>.refs.<span style="color:var(--pico-8-brown)">fetch_sub(</span><span style="color:var(--pico-8-green)">1</span>, <span style="color:var(--pico-8-washed-grey)">Ordering</span>::<span style="color:var(--pico-8-washed-grey)">SeqCst</span><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Dropping a <code class="code">Ref</code> decreases the reference counter. We don't need to free the pointer, because <code class="code">Ref</code> doesn't semantically own it.</p>

<p>And that is basically it! To see the <code class="code">JobCell</code> in action, here's some excerpt from my god job / main game loop:</p>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">run</span><span style="color:var(--pico-8-cyan)">(mut</span <u>god_object</u>: <span style="color:var(--pico-8-washed-grey)">GodObject</span><span style="color:var(--pico-8-cyan)">)</span> -> <span style="color:var(--pico-8-washed-grey)">Result</span><<span style="color:var(--pico-8-washed-grey)">i32</span>, <span style="color:var(--pico-8-washed-grey)">String</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>frame</u> = <span style="color:var(--pico-8-washed-grey)">JobCell</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">FrameData</span>::<span style="color:var(--pico-8-brown)">default()</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>current_input</u> = <span style="color:var(--pico-8-washed-grey)">InputData</span>::<span style="color:var(--pico-8-brown)">default</span><span style="color:var(--pico-8-green)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>previous_input</u> = <span style="color:var(--pico-8-washed-grey)">JobCell</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">InputData</span>::<span style="color:var(--pico-8-brown)">default()</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>current_logic</u> = <span style="color:var(--pico-8-washed-grey)">LogicData</span>::<span style="color:var(--pico-8-brown)">default</span><span style="color:var(--pico-8-green)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>previous_logic</u> = <span style="color:var(--pico-8-washed-grey)">JobCell</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">LogicData</span>::<span style="color:var(--pico-8-brown)">default()</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>current_output</u> = <span style="color:var(--pico-8-washed-grey)">OutputData</span::<span style="color:var(--pico-8-brown)">default</span><span style="color:var(--pico-8-green)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let mut</span> <u>previous_output</u> = <span style="color:var(--pico-8-washed-grey)">JobCell</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-green)">(</span><span style="color:var(--pico-8-washed-grey)">OutputData</span>::<span style="color:var(--pico-8-brown)">default()</span><span style="color:var(--pico-8-green)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">loop</span> <span style="color:var(--pico-8-green)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">// update frame</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>frame</u>.<span style="color:var(--pico-8-brown)">as_mut()</span>.<span style="color:var(--pico-8-brown)">bump()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">// swap buffers</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>current_input</u> = <u>previous_input</u>.<span style="color:var(--pico-8-brown)">as_mut()</span>.<span style="color:var(--pico-8-brown)">replace(</span><u>current_input</u><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>current_logic</u> = <u>previous_logic</u>.<span style="color:var(--pico-8-brown)">as_mut()</span>.<span style="color:var(--pico-8-brown)">replace(</span><u>current_logic</u><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>current_output</u> = <u>previous_output</u>.<span style="color:var(--pico-8-brown)">as_mut()</span>.<span style="color:var(--pico-8-brown)">replace(</span><u>current_output</u><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">// create references</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> frame_for_input = <u>frame</u>.<span style="color:var(--pico-8-brown)">borrow()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> frame_for_logic = <u>frame</u>.<span style="color:var(--pico-8-brown)">borrow()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> frame_for_output = <u>frame</u>.<span style="color:var(--pico-8-brown)">borrow()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> previous_input_for_input = <u>previous_input</u>.<span style="color:var(--pico-8-brown)">borrow()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> previous_input_for_logic = <u>previous_input</u>.<span style="color:var(--pico-8-brown)">borrow()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> previous_logic_for_logic = <u>previous_logic</u>.<span style="color:var(--pico-8-brown)">borrow()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> previous_logic_for_output = <u>previous_logic</u>.<span style="color:var(--pico-8-brown)">borrow()</span>;<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> previous_output_for_output = <u>previous_output</u>.<span style="color:var(--pico-8-brown)">borrow()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">// submit jobs</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> output_future = <span style="color:var(--pico-8-washed-grey)">job_system</span>::<span style="color:var(--pico-8-brown)">submit(</span><span style="color:var(--pico-8-cyan)">move</span> || <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">output_frame</span>::<span style="color:var(--pico-8-brown)">run</span><span style="color:var(--pico-8-green)">(</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>current_output</u>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;previous_output_for_output,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;previous_logic_for_output,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;frame_for_output,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> logic_future = <span style="color:var(--pico-8-washed-grey)">job_system</span>::<span style="color:var(--pico-8-brown)">submit(</span><span style="color:var(--pico-8-cyan)">move</span> || <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">logic_frame</span>::<span style="color:var(--pico-8-brown)">run</span><span style="color:var(--pico-8-green)">(</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>current_logic</u>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;previous_logic_for_logic,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;previous_input_for_logic,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;frame_for_logic,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">}</span><span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-brown)">(</span>new_input_data, input_state<span style="color:var(--pico-8-brown)">)</span> =<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>god_object</u><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.input_frame<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.<span style="color:var(--pico-8-brown)">run(</span><u>current_input</u>, previous_input_for_input, frame_for_input<span style="color:var(--pico-8-brown)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">// wait for jobs</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-brown)">(</span>new_logic_data, logic_state<span style="color:var(--pico-8-brown)">)</span> = logic_future.<span style="color:var(--pico-8-brown)">wait()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> <span style="color:var(--pico-8-brown)">(</span>new_output_data, output_state<span style="color:var(--pico-8-brown)">)</span> = output_future.<span style="color:var(--pico-8-brown)">wait()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">// update buffers</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>current_input</u> = new_input_data;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>current_logic</u> = new_logic_data;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>current_output</u> = new_output_data;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">...</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<p>Every buffer exists 2 times: Once as owned and once as <code class="code">JobCell</code>. It's pretty much a front- and back-buffer system. The jobs that own a buffer, can modify them as much as they want. The jobs that only borrow the <code class="code">JobCell</code> cannot mutate the buffer, and can only create new state which follows from the previous state.</p>

<p>Each iteration of the loop does the same: First, it switches each <code class="code">JobCell</code> to <code class="code">MutableJobCell</code> by calling <code class="code">as_mut()</code> on them. If any immutable references exist at this point, these calls will spinlock and run all leftover jobs. Then the owned buffer and the <code class="code">JobCell</code> buffer will be swapped. After that, all necessary <code class="code">Ref</code>s are created. Then these references are passed into the according jobs. Because SDL2 can only pump window events on the thread that spawned the window (which is my main thread), the input frame is not put into a job. And since we need to await it anyway, we might as well call it synchronously. After each job is done, we simply move ownership back, and another iteration of the loop is ready to be run.</p>

<h2>Conclusion</h2>

<p>What a buttload of garbage that I have written here. Does this junk even work?</p>

<p>Yes, surprisingly well actually. It was a lot of work to get this to a running state and I am quite amazed how stable it turned out in the end. There is no undefined behavior to my knowledge. How about performance? I get about the same number of frames as the previous single threaded prototype. This may sound bad, but this is probably due to the fact that this engine is very early in development. I simply don't have enough jobs yet to fully utilize its parallel ability. I have what? 4 jobs in total?! On my 12-core machine, that's hardly parallel at all! Anyway, be rest assured that this job system does indeed use 100% of CPU resources:</p>

<video loop="true" autoplay="autoplay" muted="true" style="max-width:100%; display: block; margin: auto;" loading='lazy'>
<source src="https://www.rismosch.com/articles/building-a-job-system/100_cpu_usage.mp4" type="video/mp4">
</video>

<p>I have nothing else to say. My engine works steadily, and it didn't offer any nasty surprises. It simply brings me joy. But alas, only time will tell how well this system works. Remember: I am still the villain of the phrase <i>"premature optimization is the root of all evil"</i>. This whole blogpost is the exemplification of <i>premature optimization</i>.</p>

<p>As for now, this job system is only a <i>proof of concept</i>. Now all I have to do is to use it, so I can experience firsthand how well it stacks up in the real world.</p>

<p>But until then, we'll hear from each other &#127926;</p>