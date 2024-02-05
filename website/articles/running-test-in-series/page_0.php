<p style="background-color:var(--pico-8-white); border: 5px solid var(--pico-8-cyan); padding: 20px;">If you don't care about motivation and just want a solution, simply jump to the code below or <a href="https://github.com/Rismosch/ris_engine/blob/6f210fc0956f446370ed752c6bb23354c3aac69d/crates/ris_util/src/test_lock.rs" target="_blank" rel="noopener noreferrer">view it on GitHub</a>.<br><br>Have fun &#128521;</p>

<br>

<p>Cargos test runner runs your tests concurrently. In a nutshell, that means <code class="code">cargo test</code> runs <b>multiple tests at the same time</b>. This is a double-edged sword though: On one hand, running all your tests may be significantly faster, because it literally runs more at less time. But the downside is, that your tests need to be 100% thread safe and they need to be completely independent from each other. Most of the time, tests you write in Rust should be independent from the get go, especially since <code class="code">cargo test</code> doesn't provide setup or teardown methods, which are commonly used in other testing frameworks in other languages. But there are a few cases, where even in Rust, data is shared between tests. Most notably: Statically mutable variables, files or database connections.</p>

<br>

<p>Here's how shared data creates problems with <code class="code">cargo test</code>: One test may set it up to "hoi" while another sets it up to "poi". Test one expects it to be "hoi" while the second expects it to be "poi". One possible concurrent execution of the tests may look like this: Test one sets it up to "hoi", but oh no, before it can check the data, test two sets it to "poi". Test two checks and sees the data is "poi" and succeeds. Now test one continues to execute, sees that the data is "poi" instead of "hoi", and the test fails. Even though we implemented our behaviour 100% correctly&#8482;, the test fails, because it has a <i>race condition</i> with another test.</p>

<br>

<img src="https://www.rismosch.com/articles/running-test-in-series/failed.webp" style="display: block; max-width: 100%;" />

<br>

<p>So how do we fix this? Simple: Don't execute tests concurrently. <a href="https://doc.rust-lang.org/book/ch11-02-running-tests.html#running-tests-in-parallel-or-consecutively" target="_blank" rel="noopener noreferrer">The Book</a> even tells us how to run tests on only one thread. But this directly means, that our tests won't run as fast. Bummer.</p>

<br>

<p>Instead, what we want is to selectively run only specific tests in series, while all unrelated tests run concurrently. <a href="https://fdeantoni.medium.com/running-tests-sequentially-in-rust-eed7566f63f0" target="_blank" rel="noopener noreferrer">This blogpost</a> by Ferdinand de Antoni suggests to simply use the <a href="https://crates.io/crates/serial_test" target="_blank" rel="noopener noreferrer">serial_test</a> crate. This works I guess, but I am more of a Terry A. Davis kind of guy, with virtually none of his genius and twice his sanity. So, I want to keep 3rd-party stuff to minimum. Also spoiler: The solution is so quick and easy, it doesn't really deserve a separate crate.</p>

<br>

<p>Ok, so we want to come up with a solution ourselves. We could write our own test harness. Yes, in Rust you can actually write one yourself. But there are 2 strong reasons against it:</p>
<ol>
<li>I only know that you <i>can</i> write your own test harness, but not <i>how</i>. I am really unqualified to give you directions on that. Though Jon Gjengset mentioned how to, in his book "Rust for Rustaceans", if you seriously want to do this.</li>
<li>Secondly: It's absolute overkill. Sure, you can kill a single ant with a nuclear bomb, it gets the job done, but I think there is a simpler solution.</li>
</ol>

<br>

<p>A simple, working approach would be the following: Have some piece of code, which is shared between tests, so <b><i>it</i></b> ensures that no tests are run in parallel. Now we are thinking concurrently! The most obvious data structure would be a <a href="https://doc.rust-lang.org/std/sync/struct.Mutex.html" target="_blank" rel="noopener noreferrer">Mutex</a>. But a Mutex comes with a hefty drawback: <a href="https://doc.rust-lang.org/std/sync/struct.PoisonError.html" target="_blank" rel="noopener noreferrer">PoisonError</a>. Long story short: If a thread panics while holding a locked Mutex, other threads currently waiting to acquire the Mutex will panic too. That is no good, meaning a perfectly fine test will fail, when another using the same Mutex is failing. So we have to use something else.</p>

<br>

<p>What about <a href="https://doc.rust-lang.org/std/sync/atomic/" target="_blank" rel="noopener noreferrer">atomics</a>? Ah yes, that would work. We can set a flag at the start of our test, indicating that this test is running. Other threads trying to set that flag would see it as already set, and thus wait until it is free. We just need to reset that flag at the end of each test, such that other tests can run again.</p>

<br>

<p>Now this is promising, but it still isn't perfect: What if the test fails? Most of the time, your test fails because some panic occurred, whether by some <code class="code">assert!()</code>, <code class="code">unwrap()</code> or because you deliberately throw it in your code. This is bad, because a panic leads to the code afterwards to not be executed. This means a test holding the flag doesn't reset it after it failed. This in turn means, that every waiting test will wait forever, because no one is going to reset the flag.</p>

<br>

<p>But, what if we put it into a drop-guard?</p>

<br>

<p>...</p>

<br>

<p>Genius! Yes! Why didn't I think of that?! Let's put it into a file and ship it! Easy &#128526;</p>

<br>

<a href="https://github.com/Rismosch/ris_engine/blob/6f210fc0956f446370ed752c6bb23354c3aac69d/crates/ris_util/src/test_lock.rs" target="_blank" rel="noopener noreferrer">View on GitHub</a>

<code class="code code_block">
<span style="color:var(--pico-8-cyan)">use</span> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">sync</span>::<span style="color:var(--pico-8-washed-grey)">atomic</span>::<span style="color:var(--pico-8-lime)">{</span><span style="color:var(--pico-8-washed-grey)">AtomicBool</span>, <span style="color:var(--pico-8-washed-grey)">Ordering</span><span style="color:var(--pico-8-lime)">}</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">thread</span>,<br>
<span style="color:var(--pico-8-cyan)">}</span>;<br>
<br>
<span style="color:var(--pico-8-cyan)">pub struct</span> <span style="color:var(--pico-8-washed-grey)">TestLock</span><<span style="color:var(--pico-8-cyan)">'a</span>><span style="color:var(--pico-8-cyan)">(</span>&<span style="color:var(--pico-8-cyan)">'a</span> <span style="color:var(--pico-8-washed-grey)">AtomicBool</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
<br>
<span style="color:var(--pico-8-cyan)">impl</span><<span style="color:var(--pico-8-cyan)">'a</span>> <span style="color:var(--pico-8-washed-grey)">TestLock</span><<span style="color:var(--pico-8-cyan)">'a</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">pub fn</span> <span style="color:var(--pico-8-brown)">wait_and_lock</span><span style="color:var(--pico-8-lime)">(</span><u>lock</u>: &<span style="color:var(--pico-8-cyan)">'a</span> <span style="color:var(--pico-8-washed-grey)">AtomicBool</span><span style="color:var(--pico-8-lime)">)</span> -> <span style="color:var(--pico-8-cyan)">Self</span> <span style="color:var(--pico-8-lime)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-pink)">while</span> <u>lock</u><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.<span style="color:var(--pico-8-brown)">compare_exchange_weak(</span><span style="color:var(--pico-8-cyan)">false</span>, <span style="color:var(--pico-8-cyan)">true</span>, <span style="color:var(--pico-8-washed-grey)">Ordering</span>::<span style="color:var(--pico-8-washed-grey)">SeqCst</span>, <span style="color:var(--pico-8-washed-grey)">Ordering</span>::<span style="color:var(--pico-8-washed-grey)">SeqCst</span><span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.<span style="color:var(--pico-8-brown)">is_err()</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">thread</span>::<span style="color:var(--pico-8-brown)">yield_now</span><span style="color:var(--pico-8-cyan)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">Self</span><span style="color:var(--pico-8-brown)">(</span><u>lock</u><span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-lime)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
<span style="color:var(--pico-8-cyan)">impl</span><<span style="color:var(--pico-8-cyan)">'a</span>> <span style="color:var(--pico-8-washed-grey)">Drop</span> <span style="color:var(--pico-8-cyan)">for</span> <span style="color:var(--pico-8-washed-grey)">TestLock</span><<span style="color:var(--pico-8-cyan)">'a</span>> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)"><u>drop</u></span><span style="color:var(--pico-8-lime)">(</span>&<span style="color:var(--pico-8-cyan)">mut <u>self</u></span><span style="color:var(--pico-8-lime)">) {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)"><u>self</u></span>.0.<span style="color:var(--pico-8-brown)">store(</span><span style="color:var(--pico-8-cyan)">false</span>, <span style="color:var(--pico-8-washed-grey)">Ordering</span>::<span style="color:var(--pico-8-washed-grey)">SeqCst</span><span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-lime)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span><br>
<br>
#<span style="color:var(--pico-8-cyan)">[</span>cfg<span style="color:var(--pico-8-lime)">(</span>test<span style="color:var(--pico-8-lime)">)</span><span style="color:var(--pico-8-cyan)">]</span><br>
<span style="color:var(--pico-8-cyan)">mod</span> <span style="color:var(--pico-8-washed-grey)">examples</span> <span style="color:var(--pico-8-cyan)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">use</span> <span style="color:var(--pico-8-washed-grey)">std</span>::<span style="color:var(--pico-8-lime)">{</span><span style="color:var(--pico-8-washed-grey)">sync</span>::<span style="color:var(--pico-8-washed-grey)">atomic</span>::<span style="color:var(--pico-8-washed-grey)">AtomicBool</span>, <span style="color:var(--pico-8-washed-grey)">thread</span>, <span style="color:var(--pico-8-washed-grey)">time</span>::<span style="color:var(--pico-8-washed-grey)">Duration</span><span style="color:var(--pico-8-lime)">}</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">use super</span>::<span style="color:var(--pico-8-washed-grey)">TestLock</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">static mut</span> <u>UNSAFE_SHARED_DATA</u>: <span style="color:var(--pico-8-washed-grey)">String</span> = <span style="color:var(--pico-8-washed-grey)">String</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-lime)">()</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">static</span> <u>LOCK</u>: <span style="color:var(--pico-8-washed-grey)">AtomicBool</span> = <span style="color:var(--pico-8-washed-grey)">AtomicBool</span>::<span style="color:var(--pico-8-brown)">new</span><span style="color:var(--pico-8-lime)">(</span><span style="color:var(--pico-8-cyan)">false</span><span style="color:var(--pico-8-lime)">)</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;#<span style="color:var(--pico-8-lime)">[</span>test<span style="color:var(--pico-8-lime)">]</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">test_one</span><span style="color:var(--pico-8-lime)">() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> lock = <span style="color:var(--pico-8-washed-grey)">TestLock</span>::<span style="color:var(--pico-8-brown)">wait_and_lock(</span>&LOCK<span style="color:var(--pico-8-brown)">)</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">unsafe</span> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>UNSAFE_SHARED_DATA</u> = <span style="color:var(--pico-8-washed-grey)">String</span>::<span style="color:var(--pico-8-brown)">from</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-orange)">"hoi"</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-washed-grey)">thread</span>::<span style="color:var(--pico-8-brown)">sleep</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-washed-grey)">Duration</span>::<span style="color:var(--pico-8-brown)">from_millis</span><span style="color:var(--pico-8-lime)">(1)</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">assert_eq</span><span style="color:var(--pico-8-brown)">!</span><span style="color:var(--pico-8-cyan)">(</span><u>UNSAFE_SHARED_DATA</u>, <span style="color:var(--pico-8-orange)">"hoi"</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">drop(</span>lock<span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-lime)">}</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;#<span style="color:var(--pico-8-lime)">[</span>test<span style="color:var(--pico-8-lime)">]</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">fn</span> <span style="color:var(--pico-8-brown)">test_two</span><span style="color:var(--pico-8-lime)">() {</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">let</span> lock = <span style="color:var(--pico-8-washed-grey)">TestLock</span>::<span style="color:var(--pico-8-brown)">wait_and_lock(</span>&LOCK<span style="color:var(--pico-8-brown)">)</span>;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">unsafe</span> <span style="color:var(--pico-8-brown)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>UNSAFE_SHARED_DATA</u> = <span style="color:var(--pico-8-washed-grey)">String</span>::<span style="color:var(--pico-8-brown)">from</span><span style="color:var(--pico-8-cyan)">(</span><span style="color:var(--pico-8-orange)">"poi"</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">assert_eq</span><span style="color:var(--pico-8-brown)">!</span><span style="color:var(--pico-8-cyan)">(</span><u>UNSAFE_SHARED_DATA</u>, <span style="color:var(--pico-8-orange)">"poi"</span><span style="color:var(--pico-8-cyan)">)</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">}</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-brown)">drop(</span>lock<span style="color:var(--pico-8-brown)">)</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-lime)">}</span><br>
<span style="color:var(--pico-8-cyan)">}</span>
</code>

<br>

<p>Yes, it uses a spinlock. Yes, it uses <code class="code">Ordering::SeqCst</code>. But if I am honest, at this point I don't care. It is better than running the tests on one thread, and it gets the job done.</p>

<br>

<img src="https://www.rismosch.com/articles/running-test-in-series/succeess.webp" style="display: block; max-width: 100%;" />

<br>

<p>Initially, I wanted to explain how this code works in detail and why, for beginners you know. But this got quickly out of hand, and if I would have included it here, then this blog post would've been 5 times as long. Concurrency is a big topic. You can write a book about it, and people have. If you aren't shy of C++, I highly recommend "C++ Concurrency in Action" by Anthony Williams.</p>

<br>

<p>If you don't know why this code works, and are not interested in the book I just recommended, understand this simplified explanation: </p>

<ul>
<li>Atomics are variables that are thread safe.</li>
<li>The while loop attempts to lock the AtomicBool.</li>
<li>If another test holds the lock, setting the lock will fail and the loop executes again. This may happen very, very often. But unless we succeed on setting the lock, the code will never leave the loop. It effectively waits. We say it <i>spins</i>, and this programming pattern is called a <i>spinlock</i>.</li>
<li>Rust is smart and drops values as soon as they are not used anymore. Thus, if we don't use the lock, it will immediately be dropped and the lock will be freed. The manual call <code class="code">drop(lock)</code> at the end of each test prevents the lock of being freed too early.</li>
<li><code class="code">drop()</code> is called, whether because the value falls out of scope, or because of a panic. No matter what our code does, <code class="code">drop()</code> will be called guaranteed. Therefore, it is safe to put cleanup code into it, like freeing our lock. This pattern is called a <i>drop-guard</i>.</li>
</ul>

<br>

<p>And that's all there is about it. Using this solution really boils down to just copying and then using it. I hope this may be helpful for someone &#128521;</p>