<h2 id="the-two-rules">0. The two rules</h2>
<p>Every advice in this manifesto is based on two rules.</p>
<blockquote style="background-color:var(--pico-8-white); border: 5px solid var(--pico-8-cyan); padding: 20px;">
Rule 1: Good code is understandable.
</blockquote>
<p>Sooner or later you will realize that code is more often read than
written. May your code be read by strangers on the internet, your
coworkers, or you yourself in a couple of weeks, months or years. When
requirements change, new features need to be added, or bugs to be fixed,
you need to go read and <em>understand</em> the actual code. You can’t
do your job if you can’t understand the code.</p>
<p>If the code you are writing is not understandable, it is not good
code.</p>
<blockquote style="background-color:var(--pico-8-white); border: 5px solid var(--pico-8-cyan); padding: 20px;">
Rule 2: Good code works.
</blockquote>
<p>Whether you like it or not, you will eventually break rule 1. May
that be because of the complexity of the problem, the API of a library
you are using, or simply lack of ability, you <strong>WILL</strong>
write code that is difficult to understand. In these cases, it’s
preferable to get things working.</p>
<p>I’ve observed many instances in my career, where despite a working
understandable prototype exists, someone insisted on a specific
programming pattern, which then caused problems in the short and
longterm. I’ve even seen systems break because of hastily applied
programming patterns.</p>
<p>Rule 2 always holds. Use solutions that you are familiar with, and
that you know are working. Don’t mindlessly follow what a book, a
blogpost (including this one) or an AI said. My ego isn’t big enough to
claim that my advice will work in every case. You will certainly find
exceptions. My perspective comes mainly from graphics programming. I
value readability, reliability and performance over everything else.</p>
<h2 id="use-strong-naming">1. Use strong naming</h2>
<p>If you have the choice, always pick the programming language with
statically strong typing. By that I mean, choose languages that have to
be compiled, and which spit out errors if you use types incorrectly. The
stronger and stricter the type system, the better. While beginners and
Python devs often see a strong type system as a major annoyance, it will
always pay off in the long term. Always. The compiler is a tool, which
can check the correctness of your program before it even runs. This
reduces mental overhead and prevents bugs.</p>
<p>But no matter how strong your type system is, there will always be
ambiguity. Take the humble <code class="code">int</code> for example. The
<code class="code">int</code> alone can have many different meanings according to the
context. These usages include but are not limited to:</p>
<ul>
<li>a number, for calculations</li>
<li>an index into an array</li>
<li>a counter</li>
<li>an id</li>
<li>a hash</li>
<li>a score in a game</li>
<li>a position on the screen</li>
<li>bitflags and masks</li>
<li>a pointer</li>
<li>and probably much more.</li>
</ul>
<p>Another favorite example of mine is the file path. Many operating
systems use strings for paths. Rust in particular decided to have a
seperate type for paths, to differentiate them from strings. But
ambiguity still exists:</p>
<ul>
<li>Is the path absolute or relative?</li>
<li>Does the path point to a file, directory, link or something
else?</li>
<li>Is the path just a name?</li>
<li>Does it have an extension?</li>
<li>Is it just the extension?</li>
</ul>
<p>Sure, these can be checked at runtime, but the compiler is not
preventing you to pass a filename to a function that expects a
directory.</p>
<p>No matter how strong your type system is, there will be ambiguity. To
reduce this ambiguity you should use strong naming. By that I mean, your
variables, functions, structs, and everything else that can have a name,
should have a proper, describable name. The name of a thing should
clearly tell the reader what that thing is doing.</p>
<h3 id="variables">1.1 Variables</h3>
<h4 id="avoid-single-letter-names">1.1.1 Avoid single letter names</h4>
<p>First things first, single letter names are almost always
discouraged. There are exactly 2 notable exceptions: Mathematical
formulas and small scoped variables.</p>
<p>If you implement functions from a mathematical text, a book or online
reference, sure, use the same names as used in the reference.</p>
<p>If you have variables that exist in a very small scope, single letter
names are fine as well. These are your i, j, k variables in for-loops,
or x in an iterator callback. For example</p>
<pre><code class="code code_block">// this is fine<br>
for (int i = 0; i &lt; 100; ++i) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;do_something_with(i);<br>
}</code></pre>
<p>or</p>
<pre><code class="code code_block">// this is also fine<br>
my_objects.foreach(x =&gt; x.do_something())</code></pre>
<p>These instances of single letter names are acceptable, because their
scope is small enough to easily reason about what that variable holds
and does.</p>
<p>Other than these two exceptions, your variable should never have a
one letter name. Instead, the name of a variable should be a full word
or a combination of words that clearly indicate what is being stored and
how it is going to be used.</p>
<h4 id="be-careful-with-abbreviations">1.1.2 Be careful with
abbreviations</h4>
<p>Names should be descriptive full words. Abbreviations are
discouraged, but often okay, if the abbreviation is used very often and
is clearly understood. For examble <code class="code">Vec3</code> for
<code class="code">Vector3</code> is acceptable, because in a graphics programming
environment this type will be found everywhere in the codebase. Or when
you are frequently dealing with a file system, using <code class="code">dir</code>
instead of <code class="code">directory</code> may also be acceptable. But always
keep in mind that a new programmer looking at your codebase may not be
familiar with the abbreviation.</p>
<h4 id="use-pre--and-posfixing">1.1.3 Use pre- and posfixing</h4>
<p>Let’s go back at the <code class="code">int</code> example. If it is used as an
index, postfix it with <code class="code">index</code>:</p>
<pre><code class="code code_block">int apple_index = get_apple_index();<br>
Apple apple = apples[apple_index];</code></pre>
<p>If you are using it as a counter, postfix it with
<code class="code">counter</code>. If you use it as a score, postfix it with
<code class="code">score</code>. And the same for the other examples.</p>
<p>Postfixing is also helpful in the filepath example: Let’s say you
have a path to a configuration file. It’s absolute path would be stored
in a variable called <code class="code">configuration_filepath</code>, it’s name
<code class="code">configuration_filename</code> and it’s directory
<code class="code">configuration_directory</code>. So if you see a function signature
like this:</p>
<pre><code class="code code_block">Configuration[] get_all_configurations(string directory)</code></pre>
<p>You know that the function requires a path that points to a
directory, not a file.</p>
<p>You may notice that this kinda resembles Hungarian notation, but more verbose, and
that’s exactly the idea. For the uninitiated, Hungarian notation was a
naming convention to indicate how a given variable is to be used. It
fell out of fashion, because people were misusing it. In the past,
people denoted <em>every</em> variable with it’s type. For example every
string must start or end with <code class="code">s</code> and every int with
<code class="code">i</code>. If your programming language uses a statically strong
typesystem, this is redundant and ultimately useless. The type system
already denotes what type a variable is. So people were rightfully
annoyed when code guidelines forced them to use this everywhere.</p>
<p>But I want to highlight that postfixing is exactly useful when the
typesystem is not sufficient. Whenever ambiguity arises, postfixing is
very valuable, as demonstrated in the paragraphs above and the examples to come. So let’s
continue.</p>
<h4 id="be-explicit-about-units">1.1.4 Be explicit about units</h4>
<p>You should be explicit when using units.</p>
<p>When dealing with time, prefer a dedicated type. For example <a
href="https://learn.microsoft.com/en-us/dotnet/api/system.timespan?view=net-8.0">TimeSpan</a>
in C# allows you to construct a time interval <a
href="https://learn.microsoft.com/en-us/dotnet/api/system.timespan.fromseconds?view=net-8.0#system-timespan-fromseconds(system-double)">from
seconds</a>, <a
href="https://learn.microsoft.com/en-us/dotnet/api/system.timespan.fromseconds?view=net-8.0#system-timespan-fromseconds(system-double)">minutes</a>
and so on. If your programming language doesn’t have types like this, or
you decide to store a time interval in a <code class="code">float</code>, denote it
with it’s unit. <code class="code">send_interval</code> for example is nondescriptive,
but <code class="code">send_interval_in_seconds</code> clearly denotes how long a
single sending interval is.</p>
<p>Often times postfixing units can be annoying, when you deal with many
variables. When you consistently use the same unit everywhere, for
example meters for distances, then you can omit them in your variable
names. But in that case I encourage you to write a comment on
declaration, what kind of unit this variable holds.</p>
<h4 id="d-math">1.1.5 3d math</h4>
<p>Postfixing is also helpful in 3d math.</p>
<p>In mathematics, there is a difference between a point and a
direction. Both can be expressed as a vector, an array of floats. Thus
many math libraries only implement the vector, with no differentiation
between points and directions. This leads to confusion. Take for example
the following function:</p>
<pre><code class="code code_block">Vector3 rotate(Vector3 value, Rotation rotation);</code></pre>
<p><code class="code">value</code> is nondiscriptive. Mathematically, a direction can
be rotated, but rotating points is nonsensical. And as the function
stands now, the compiler does not hinder you to pass a point into it. I
usually postfix directions with <code class="code">direction</code> or
<code class="code">dir</code>, while points with <code class="code">point</code>,
<code class="code">position</code> or <code class="code">pos</code>.</p>
<p>Essential to 3d math are rotations. Rotations are finicky. My advice
here is to go learn what <a
href="https://en.wikipedia.org/wiki/Quaternion">quaternions</a> are and
prefer them over everything else. <a
href="https://en.wikipedia.org/wiki/Euler_angles">Euler angles</a> are
common, especially in user facing UI, but they are deceptively
counterintuitive. I’ve experienced so many difficult rotation problems
directly caused by euler angles, that today I avoid them at all
costs.</p>
<p>Often you want to rotate around a single axis anyway. Use an angle
axis rotation in that case. Also refrain from using <code class="code">x</code>,
<code class="code">y</code> or <code class="code">z</code> in your variable names, like
<code class="code">angle_x</code> or <code class="code">y_rotation</code>. When rotating around a
single axis, use <code class="code">pitch</code>, <code class="code">yaw</code> and
<code class="code">roll</code> instead.</p>
<figure>
<img src="https://www.rismosch.com/articles/the-good-code-manifesto/Yaw_Axis_Corrected.svg" alt="picture of pitch yaw and roll" style="display: block; margin: auto; max-width: 100%;"/>
<figcaption aria-hidden="true">By
<a href="https://commons.wikimedia.org/wiki/File:Yaw_Axis.svg" title="File:Yaw Axis.svg">Yaw_Axis.svg</a>:
<a href="https://commons.wikimedia.org/wiki/User:Auawise" title="User:Auawise">Auawise</a>
derivative work:
<a href="https://commons.wikimedia.org/w/index.php?title=User:Jrvz&amp;action=edit&amp;redlink=1" class="new" title="User:Jrvz (page does not exist)">Jrvz</a>
(<a href="https://commons.wikimedia.org/w/index.php?title=User_talk:Jrvz&amp;action=edit&amp;redlink=1" class="new" title="User talk:Jrvz (page does not exist)"><span
class="signature-talk">talk</span></a>) -
<a href="https://commons.wikimedia.org/wiki/File:Yaw_Axis.svg" title="File:Yaw Axis.svg">Yaw_Axis.svg</a>,
<a href="https://creativecommons.org/licenses/by-sa/3.0" title="Creative Commons Attribution-Share Alike 3.0">CC
BY-SA 3.0</a>,
<a href="https://commons.wikimedia.org/w/index.php?curid=9441238">Link</a></figcaption>
</figure>
<p>When working with coordinate systems, often you are faced with wildly
different ones. Most commonly you will deal with local vs world space,
but there are so many more, especially between libraries that don’t know
each other. The only standard is that there exists no standard. Y may
point up, or it may point down, sometimes Z is up. Sometimes coordinate
systems are left handed, sometimes they are right handed. Pretty much
every direction for X, Y and Z is fair game.</p>
<p>When dealing with directions, refrain from using <code class="code">x</code>,
<code class="code">y</code> or <code class="code">z</code> as well. If you have a vector that
points towards "X", chances are that X is not
where you think it is. Rather use relative directions like
<code class="code">left</code>, <code class="code">right</code>, <code class="code">up</code>,
<code class="code">down</code>, <code class="code">forward</code> and <code class="code">backward</code> and
combine it with the object. For example <code class="code">camera_right</code> is the
right direction relative to the camera, <code class="code">car_up</code> is the up
direction relative to the car, and <code class="code">world_forward</code> is the
forward direction relative to the world coordinate system.</p>
<h4 id="tangent-magic-values">1.1.6 Tangent: Magic values</h4>
<p>Magic values are plain values with no variables or names associated
with them.</p>
<pre><code class="code code_block">Timestamp _time_since_last_bump = unix_epoch();<br>
<br>
void update() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;Timestamp now = get_now();<br>
&nbsp;&nbsp;&nbsp;&nbsp;Timespan diff = now - _time_since_last_bump<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;// bad<br>
&nbsp;&nbsp;&nbsp;&nbsp;if (diff &lt; 42) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;_time_since_last_bump = now;<br>
&nbsp;&nbsp;&nbsp;&nbsp;bump()<br>
}</code></pre>
<p>Here, 42 comes out of nowhere and gives no explanation on why it is
42. This is especially egregious, as per <strong>1.1.4</strong>, we
don’t even know what units we are using. A better solution is to provide
a variable, local or global, and store your value there:</p>
<pre><code class="code code_block">float _bump_interval_in_seconds = 42; // magic value is now a variable<br>
Timestamp _time_since_last_bump = unix_epoch();<br>
<br>
void update() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;Timestamp now = get_now();<br>
&nbsp;&nbsp;&nbsp;&nbsp;Timespan diff = now - _time_since_last_bump<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;// good<br>
&nbsp;&nbsp;&nbsp;&nbsp;if (diff &lt; _bump_interval_in_seconds) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;_time_since_last_bump = now;<br>
&nbsp;&nbsp;&nbsp;&nbsp;bump()<br>
}</code></pre>
<p>This is better, because as a variable, its name now indicates what
the value actually represents.</p>
<p>Now, I did cheat a bit with this example, as a proper time library
shouldn’t allow comparison between a <code class="code">Timespan</code> and a number,
but you get the point.</p>
<h3 id="functions">1.2 Functions</h3>
<p>In different programming languages, functions are referred to by
different names. Functions, methods, lambdas, closures, procedures,
subroutines or whatever. Semantically they are all the same, they just
differ in scope and access to their surroundings. When I talk about
“functions”, I mean any callable code. Functions are asociated with a
block of code, which is executed when the function is called. As such, a
function does stuff, and thus its name should be a <em>verb</em>. Like
with strongly named variables, strongly named functions should have
descriptive full word names.</p>
<h4 id="denote-dangerous-functions">1.2.1 Denote dangerous
functions</h4>
<p>Depending on how the function is implemented, its name must be more
specific. For example, functions can be asynchronous. From the
perspective of the client, asynchronous functions return immediately,
but its code block is executed in parallel, finishing in an unspecified
point in the future. Postfix such functions with <code class="code">async</code>.</p>
<p>Async functions should return an object that allows client code to
wait on the result, may that be a <code class="code">Task</code>,
<code class="code">Future</code>, <code class="code">Promise</code> or whatever.</p>
<pre><code class="code code_block">Future send_message_async(string message)&nbsp;// good<br>
void send_message_async(string message)&nbsp;&nbsp;&nbsp;// bad, not awaitable<br>
Future send_message(string message)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;// bad, not named async</code></pre>
<p>Many modern programming languages come with syntax sugar to aid the
programmer in generating awaitable objects. But if your programming
language can’t provide such an object, implement a blocking alternative
to your function. Ideally, client code should be in charge whether a
given operation blocks or not.</p>
<p>Also, recursive functions should be postfixed with
<code class="code">recursive</code>:</p>
<pre><code class="code code_block">GameObject find_game_object_recursive(GameObject game_object, Id id) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;if (game_object.id() == id) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return game_object;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;foreach (GameObject child in game_object.children()) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GameObject result = find_game_object_recursive(child, id); // recursive call<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if (result != null) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return result;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;return null;<br>
}</code></pre>
<p>If your data is cyclical, or your exit condition is never met, then
recursive functions will loop forever. Hopefully you will overflow the
callstack and crash. Crashing is good, because it obviously tells you
that something is wrong. But in bad cases you loop forever. This is
almost always undesireable. Sometimes, recursive functions are not
obviously recursive. For example function <code class="code">A</code> may call
function <code class="code">B</code>, which calls function <code class="code">C</code>, which
calls function <code class="code">A</code>. By postfixing it with
<code class="code">recursive</code>, it makes recursion easier to spot and tells the
reader of your code, they must take care when handling them.</p>
<p>Recursion is so problematic in fact, that avoiding recursion is rule
number one in <a
href="https://en.wikipedia.org/wiki/The_Power_of_10%3A_Rules_for_Developing_Safety-Critical_Code">“The
Power of 10: Rules for Developing Safety-Critical Code” by NASA</a>.
Assuming you are not writing code for satalites or rovers, recursion is
fine and a useful tool. Still, you should label it clearly when using
it.</p>
<h4 id="out-parameters">1.2.2 Out parameters</h4>
<p>Some functions have out-parameters, which are usually pointers to be
written to. Languages like C# even have a specific keyword for that. In
cases where you don’t have a keyword for out-parameters, it’s a good
idea to prefix out parameters as such:</p>
<pre><code class="code code_block">bool try_create_file(string filepath, File* out_file)</code></pre>
<h4 id="mark-failable-operations">1.2.3 Mark failable operations</h4>
<p>The function in the example of <strong>1.2.2</strong> is what I call
a “try-function”. Functions can fail. And failable functions should be
clearly annotated in some way. Languages like Rust allow failure states
within it’s typesystem, via the <a
href="https://doc.rust-lang.org/std/option/">Option</a> and <a
href="https://doc.rust-lang.org/std/result/">Result</a> types. But in
languages where this is not possible, prefer to use try-functions.</p>
<p>A try-function does not return it’s result, instead it returns a
bool, int or an enum, to indicate if the operation was successful. The
actual return value is returned via an out parameter. If the operation
failed, the out parameter may not be initialized and must not be
used.</p>
<p>Also, as rule 7 of <a
href="https://en.wikipedia.org/wiki/The_Power_of_10%3A_Rules_for_Developing_Safety-Critical_Code">“The
Power of 10: Rules for Developing Safety-Critical Code” by NASA</a>
states, return values must always be checked, or explicitly discarded.
This is especially true for try-functions, as a failed operation can
break the surrounding code if not handled properly.</p>
<p>You would use a try-function like this:</p>
<pre><code class="code code_block">File file;<br>
if (try_create_file(&quot;some path&quot;, &amp;file)) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;// success<br>
} else {<br>
&nbsp;&nbsp;&nbsp;&nbsp;// failure<br>
}</code></pre>
<h4 id="avoid-default-parameters">1.2.4 Avoid default parameters</h4>
<p>Some programming languages allow default parameters in functions. A
default parameter declares a constant value, which then can be omitted by
the caller.</p>
<pre><code class="code code_block">// bad<br>
void draw_aabb(Vec3 min, Vec3 max, Rgb color = null);<br>
<br>
void main() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;// the third parameter was omitted<br>
&nbsp;&nbsp;&nbsp;&nbsp;draw_aabb(Vec3::zero, Vec3::one);<br>
}</code></pre>
<p>This is always bad design, because being explicit is better than
being implicit. With default parameters you encourage client code to
avoid passing arguments into the function, leading to hidden
dependencies.</p>
<p>Such hidden dependencies often break when refactored. Client code may
have made assumptions that after refactoring don’t hold true anymore.
This introduces bugs that a compiler may have cought. Also, if client
code did pass a value, and a parameter may be removed due to
refactoring, chances are that now the client code passes the value into
a different parameter. The compiler may not catch this, which again
introduces a bug. These kinds of issues are especially true when using
implicit type coercion.</p>
<p>By avoiding default parameters like this, you avoid these refactoring
headaches and end up with more stable code. If you still absolutely need
default parameters, see <strong>1.2.5</strong>.</p>
<h4 id="use-structs-when-dealing-with-many-parameters">1.2.5 Use structs
when dealing with many parameters</h4>
<p>If your function is complicated enough, it may bloat to ten or more
arguments. In this case, consider declaring a struct that holds all
parameters and pass that as an argument. This drastically reduces the
amount of parameters of your function, which improves readability.</p>
<p>As an example I give you the <a
href="https://registry.khronos.org/vulkan/specs/latest/man/html/VkGraphicsPipelineCreateInfo.html">VkGraphicsPipelineCreateInfo</a>
structure from the Vulkan graphics API, which is passed into the <a
href="https://registry.khronos.org/vulkan/specs/latest/man/html/vkCreateGraphicsPipelines.html">vkCreateGraphicsPipelines</a>
function:</p>
<pre><code class="code code_block">typedef struct VkGraphicsPipelineCreateInfo {<br>
&nbsp;&nbsp;&nbsp;&nbsp;VkStructureType&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;sType;<br>
&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;void*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pNext;<br>
&nbsp;&nbsp;&nbsp;&nbsp;VkPipelineCreateFlags&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;flags;<br>
&nbsp;&nbsp;&nbsp;&nbsp;uint32_t&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;stageCount;<br>
&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;VkPipelineShaderStageCreateInfo*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pStages;<br>
&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;VkPipelineVertexInputStateCreateInfo*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pVertexInputState;<br>
&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;VkPipelineInputAssemblyStateCreateInfo*&nbsp;&nbsp;&nbsp;&nbsp;pInputAssemblyState;<br>
&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;VkPipelineTessellationStateCreateInfo*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pTessellationState;<br>
&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;VkPipelineViewportStateCreateInfo*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pViewportState;<br>
&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;VkPipelineRasterizationStateCreateInfo*&nbsp;&nbsp;&nbsp;&nbsp;pRasterizationState;<br>
&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;VkPipelineMultisampleStateCreateInfo*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pMultisampleState;<br>
&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;VkPipelineDepthStencilStateCreateInfo*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pDepthStencilState;<br>
&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;VkPipelineColorBlendStateCreateInfo*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pColorBlendState;<br>
&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;VkPipelineDynamicStateCreateInfo*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pDynamicState;<br>
&nbsp;&nbsp;&nbsp;&nbsp;VkPipelineLayout&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;layout;<br>
&nbsp;&nbsp;&nbsp;&nbsp;VkRenderPass&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;renderPass;<br>
&nbsp;&nbsp;&nbsp;&nbsp;uint32_t&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;subpass;<br>
&nbsp;&nbsp;&nbsp;&nbsp;VkPipeline&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;basePipelineHandle;<br>
&nbsp;&nbsp;&nbsp;&nbsp;int32_t&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;basePipelineIndex;<br>
} VkGraphicsPipelineCreateInfo;<br>
<br>
VkResult vkCreateGraphicsPipelines(<br>
&nbsp;&nbsp;&nbsp;&nbsp;VkDevice&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;device,<br>
&nbsp;&nbsp;&nbsp;&nbsp;VkPipelineCache&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pipelineCache,<br>
&nbsp;&nbsp;&nbsp;&nbsp;uint32_t&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;createInfoCount,<br>
&nbsp;&nbsp;&nbsp;&nbsp;const VkGraphicsPipelineCreateInfo*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pCreateInfos,<br>
&nbsp;&nbsp;&nbsp;&nbsp;const VkAllocationCallbacks*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pAllocator,<br>
&nbsp;&nbsp;&nbsp;&nbsp;VkPipeline*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pPipelines);</code></pre>
<p>These “CreateInfo”, or “Args” structs can hold a ludicrous amount of
information, without losing organization. A function with comparably
many parameters are difficult to call, because parameter 6 and 7 are
difficult to distinguish, leading you to count them each and every
time.</p>
<p>Also, args structs may or may not have default initialization, which
isn’t a problem here, because each field has a name. There is no
ambiguity on constructing such an object, especially in languages like
Rust where everything must be initialized.</p>
<p>An args struct can also be built step by step. Then, when the
preparations are done, you can pass the arguments elegantly in a single
call.</p>
<h4 id="avoid-side-effects">1.2.6 Avoid side effects</h4>
<p>A function produces a side effect, if it modifies something outside
of its declared signature. For example a function <code class="code">foo()</code> may
change a global variable, or a function of a class modifies a dependency
that the class stores internally.</p>
<p>Side effects are bad, because client code may not be aware of them.
This produces implicit dependencies. I repeat myself, but being explicit
is better than being implicit. If the dependency changes, now client
code breaks.</p>
<p>Thus, implicit dependencies tend to produce bugs and they are often
difficult to debug.</p>
<h3 id="structs-and-classes">1.3 Structs and classes</h3>
<p>Structs and classes store data. As such their names should be
<em>nouns</em>. As should be clear by now, their names should consist of
descriptive whole words.</p>
<p>Now when it comes to classes, many programmers are really tempted to
use undiscriptive names. Names like <code class="code">InputManager</code> or
<code class="code">InputHandler</code> are a common occurance in the wild. The issue:
<em>Every</em> piece of code manages and handles data. That’s a hard
fact. As such, names like “Manager” or “Handler” are absolutely
undiscriptive and meaningless.</p>
<p>Sticking with the input example, you should name it depending on what
your input system does and how it is implemented. For example, if it is
run in a game engine which has a main loop, name it
<code class="code">InputCollector</code>, as it collects the input and produces an
<code class="code">InputState</code> object that is used later in the loop. Or name
it <code class="code">InputMapper</code> when it maps buttons to actions like “Jump”
or “Walk”. Or maybe your program is an event based GUI application, in
which case <code class="code">InputEventProducer</code> or
<code class="code">InputEventInvoker</code> is more fitting.</p>
<p>Also order your fields. Alphabetically, semantically or both. Makes
them easier to find when you have a lot of them.</p>
<h3 id="conditions">1.4 Conditions</h3>
<p>When it comes to conditions, mostly used in <code class="code">if</code>
statements, rules <strong>1.1</strong> to <strong>1.3</strong> are
somewhat unfitting. Thus I dedicated an entire section just to
conditions.</p>
<h4 id="use-strong-naming-1">1.4.1 Use strong naming</h4>
<p>Variables and functions used in an <code class="code">if</code> statement should
resemble questions that are answerable with “yes” or “no”. Here is a bad
example:</p>
<pre><code class="code code_block">// bad<br>
if (check_input(input)) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;...<br>
}</code></pre>
<p>Even though <code class="code">check_input</code> satisfies the advice in previous
sections, the code above is bad. Yes, “checking” is what it does, but
what is it checking? Whether the input is valid? Maybe if
the <code class="code">input</code> object has missing fields that must be added, or
does it determine whether a button was pressed? The code does not tell
you anything. Better code would look like this:</p>
<pre><code class="code code_block">// good<br>
if (input.is_valid()) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;...<br>
}</code></pre>
<p>or</p>
<pre><code class="code code_block">// good<br>
if (b_button.is_pressed()) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;...<br>
}</code></pre>
<p>If your programming language doesn’t support the <code class="code">.</code>
operator, the same can easily be accomplished with variables:</p>
<pre><code class="code code_block">bool input_is_valid = validate_input(input);<br>
if (input_is_valid) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;...<br>
}</code></pre>
<p>or</p>
<pre><code class="code code_block">bool b_button_is_pressed = button_is_pressed(b_button);<br>
if (b_button_is_pressed) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;...<br>
}</code></pre>
<h4 id="avoid-negative-names">1.4.2 Avoid negative names</h4>
<p>Negative names for variables and functions are bad and should be
avoided. By negative names I mean names that include words like “no”,
“not”, “isnt”, “doesnt”, “wasnt”, “cannot” and so forth. For
example:</p>
<pre><code class="code code_block">// very bad<br>
if (input.is_not_valid()) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;...<br>
}</code></pre>
<p>Eventually you need to negate statements. Maybe not now, but maybe in
the future. If you negate such a statement you get:</p>
<pre><code class="code code_block">// yikes<br>
if (!input.is_not_valid()) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;...<br>
}</code></pre>
<p>Double negative. This is logically sound, but it is difficult to
process for our human brains. Instead, always stick with positive names,
even if you need to immediately negate them:</p>
<pre><code class="code code_block">bool input_is_valid = validate_input(input);<br>
if (!input_is_valid) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;...<br>
}</code></pre>
<p>Negation is pretty much free, especially in compiled languages.</p>
<h4 id="avoid-complicated-conditions">1.4.3 Avoid complicated
conditions</h4>
<pre><code class="code code_block">// oh god<br>
if (input.is_valid() &amp;&amp; !ctrl_button.is_pressed() &amp;&amp; !shift_button.is_pressed() &amp;&amp; (a_button.is_pressed() || b_button.is_pressed())) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;character.jump();<br>
}</code></pre>
<p>If you are using a compiled programming language, variables are free.
Even scripting languages like JavaScript and Python come with compiled
runtimes nowadays. The compiler optimizes variables out if they are not
needed. So I encourage you to avoid one liners (like the condition
above) and use as many variables as possible (like the code below). The
code below builds the condition step by step, which greatly improves
readability.</p>
<pre><code class="code code_block">bool input_is_valid = input.is_valid();<br>
bool modifier_botton_is_pressed = ctrl_button.is_pressed() || shift_button.is_pressed();<br>
bool can_jump = input_is_valid &amp;&amp; !modifier_botton_is_pressed;<br>
bool jump_button_pressed = a_button.is_pressed() || b_button.is_pressed();<br>
<br>
if (can_jump &amp;&amp; jump_button_pressed) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;character.jump();<br>
}</code></pre>
<p>Code like this comes with a small caveat however: Lazy evaluation.
For example the buttons may have a hidden dependency on
<code class="code">input</code>. If the input is invalid,
<code class="code">ctrl_button.is_pressed()</code> may throw an exception. The
complicated conditions from the previous example does not run into this
issue, because <code class="code">&amp;&amp;</code> is lazily evaluated. When
<code class="code">input.is_valid()</code> returns <code class="code">false</code>,
<code class="code">ctrl_button.is_pressed()</code> is not called. Issues like these
are handled on a case by case basis. But when no lazy evaluation is
necessary, many variables are preferable.</p>
<p>Besides, hidden dependencies are discouraged. See
<strong>1.2.6</strong>.</p>
<h2 id="formatting">2. Formatting</h2>
<h3 id="style">2.1 Style</h3>
<p>Since I am telling you how you should name your code, I should also
tell you how to format it. Yes, I am talking about the dreaded tabs vs
spaces debate. snake_case vs CamelCase. Should <code class="code">{</code> go in the
same line of the function declaration, or should it go in the line
below? There is only one objectively correct answer. And you can quote
me on that:</p>
<p>It doesn’t matter.</p>
<p>The only thing that DOES matter is consistency. Whatever style you
choose, you should enforce it everywhere. If you and your team adhere to
the principles of this manifesto, bad code will stick out because it
will simply look different. But if your formatting is all over the
place, then it’s more difficult to spot bad code. Consistent formatting
makes it easier for yourself and your team.</p>
<p>I recommend you get in the habit of using an automatic code
formatter, and run it frequently.</p>
<h3 id="attempt-to-decrease-indentation">2.2 Attempt to decrease
indentation</h3>
<p>A new scope is commonly indented:</p>
<pre><code class="code code_block">// global scope<br>
<br>
int main() {<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// outer local scope<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;// inner local scope<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}</code></pre>
<p>But too much indentation is bad. Whitespaces denote little to no
information, and too much indentation leaves your screen blank. When the
indentation becomes bad enough, the code may exceed the width of your
editor. Some text editors wrap lines; some go offscreen, forcing you to
scroll horizontally. Both these options suck when trying to navigate
code.</p>
<p>To avoid indentation, early returns are your friend. Instead of
this:</p>
<pre><code class="code code_block">// bad<br>
void do_stuff() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;if (condition_is_true) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}</code></pre>
<p>you can do this:</p>
<pre><code class="code code_block">// good<br>
void do_stuff() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;if (!condition_is_true) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;...<br>
}</code></pre>
<p>Notice that because of the early return, the <code class="code">...</code>
(potentially many many lines of code) has one less level of
indentation.</p>
<p>Early returns can also be used in loops, but you would use
<code class="code">continue</code> instead. Instead of this:</p>
<pre><code class="code code_block">// bad<br>
for (int i = 0; i &lt; 100; ++i) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;if (can_do_something_with(i)) { <br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}</code></pre>
<p>you can do this:</p>
<pre><code class="code code_block">// good<br>
for (int i = 0; i &lt; 100; ++i) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;if (!can_do_something_with(i)) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;continue;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;...<br>
}</code></pre>
<p>Each early return reduces the indentation level by one, freeing up
your screen to actually display code instead of whitespaces.</p>
<h3 id="avoid-wide-code">2.3 Avoid wide code</h3>
<p>Indentation can lead to your code to exceed the width of your editor,
but so can wide code. This includes functions with a lot of parameters or
the builder pattern found in Rust or <a href="">LINQ</a> in C#. In these
cases, prefer to split your code into multiple lines of code:</p>
<pre><code class="code code_block">// bad<br>
CreateWindow(param1, param2, param3, param4, param5, param6, param7, param8, param9);<br>
Fruit[] red_fruit = fruit_list.Where(fruit =&gt; fruit.is_ripe()).Select(fruit =&gt; fruit.color() == Color.Red).ToArray();<br>
<br>
// good<br>
CreateWindow(<br>
&nbsp;&nbsp;&nbsp;&nbsp;param1,<br>
&nbsp;&nbsp;&nbsp;&nbsp;param2,<br>
&nbsp;&nbsp;&nbsp;&nbsp;param3,<br>
&nbsp;&nbsp;&nbsp;&nbsp;param4,<br>
&nbsp;&nbsp;&nbsp;&nbsp;param5,<br>
&nbsp;&nbsp;&nbsp;&nbsp;param6,<br>
&nbsp;&nbsp;&nbsp;&nbsp;param7,<br>
&nbsp;&nbsp;&nbsp;&nbsp;param8,<br>
&nbsp;&nbsp;&nbsp;&nbsp;param9<br>
);<br>
Fruit[] red_fruit = fruit_list<br>
&nbsp;&nbsp;&nbsp;&nbsp;.Where(fruit =&gt; fruit.is_ripe())<br>
&nbsp;&nbsp;&nbsp;&nbsp;.Select(fruit =&gt; fruit.color() == Color.Red)<br>
&nbsp;&nbsp;&nbsp;&nbsp;.ToArray();</code></pre>
<p>Too many function parameters are discouraged anyway. Follow the
advice in <strong>1.2.5</strong> to reduce the number of your function
parameters.</p>
<p>Chances are that the bad examples above are rendered poorly, forcing
you to scroll horizontally. This demonstrates my point that wide code is
bad.</p>
<p>Also important to note, is that code is read in many different
applications: IDEs, text editors, in your browser, merge tools and other
side by side views on a single screen. As such the width of the actual
view into your code is smaller than you might expect. Some modern IDEs
provide you with a vertical line and I heavily encourage everyone to not
overstep that line.</p>
<img src="https://www.rismosch.com/articles/the-good-code-manifesto/rider.png" alt="screenshot of rider" style="display: block; margin: auto; max-width: 100%;"/>
<h3 id="keep-branches-close-to-each-other">2.4 Keep branches close to
each other</h3>
<p>If you have an if-else construct, consider negating the condition, if
that puts the smaller branch ontop of the other:</p>
<pre><code class="code code_block">// bad<br>
if (condition_is_true) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;do_operation_a();<br>
&nbsp;&nbsp;&nbsp;&nbsp;do_operation_b();<br>
&nbsp;&nbsp;&nbsp;&nbsp;do_operation_c();<br>
} else {<br>
&nbsp;&nbsp;&nbsp;&nbsp;do_operation_z();<br>
}<br>
<br>
// good<br>
if (!condition_is_true) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;do_operation_z();<br>
} else {<br>
&nbsp;&nbsp;&nbsp;&nbsp;do_operation_a();<br>
&nbsp;&nbsp;&nbsp;&nbsp;do_operation_b();<br>
&nbsp;&nbsp;&nbsp;&nbsp;do_operation_c();<br>
}</code></pre>
<p>Doing so puts the <code class="code">else</code> closer to the <code class="code">if</code>.
This makes it easier to find. Thus control flow is easier to determine
and follow.</p>
<h2 id="comments">3. Comments</h2>
<p>Comments are equally decisive as formatting. They are hotly debated
in online communities. Their main problem stems from the fact that they
are not checked by the compiler, and thus are by definition dead
code.</p>
<p>Comments make it very easy to temporarily remove code from execution.
But this inherently means any information in the comment may or may not
be true. Commented code may not compile when commented back in. The code
may have been correct when it was written, but since then things have
changed, making the comment untrue in the process.</p>
<p>From my experience there are 4 valid reasons to use comments. If a
comment does not fulfill any of the following 4 reasons, it’s a bad
comment and must be deleted.</p>
<h3
id="comments-to-quickly-remove-code-from-execution-without-deleting-it">3.1
Comments to quickly remove code from execution without deleting it</h3>
<p>Removing code without deleting it is helpful in prototyping. But once
you are done with your prototype and want to ship this code, you should
remove <strong>ALL</strong> outcommented code. No one knows how long
this code will stay commented out and it may age very badly.</p>
<p>In rare cases you can keep outcommented code, when it shows some
implementation that was difficult to figure out, but isn’t needed right
now. But other than these rare occations, commented code should be
removed.</p>
<h3 id="comments-to-aid-in-structure">3.2 Comments to aid in
structure</h3>
<p>Inside your class you can have sections that denote different
things.</p>
<pre><code class="code code_block">class FruitCollection {<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Constants<br>
&nbsp;&nbsp;&nbsp;&nbsp;const int THE_ANSWER = 42;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Members<br>
&nbsp;&nbsp;&nbsp;&nbsp;List&lt;Fruit&gt; _fruit;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Constructor<br>
&nbsp;&nbsp;&nbsp;&nbsp;public FruitCollection() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Public Methods<br>
&nbsp;&nbsp;&nbsp;&nbsp;public void AddApple(Apple apple) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;public List&lt;Fruit&gt; GetAllApples() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;RemoveBadFruits();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return _fruit.copy();<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Private Method<br>
&nbsp;&nbsp;&nbsp;&nbsp;private void RemoveBadFruits() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}</code></pre>
<p>Here, comments function as headers, or section titles. Proper
structure and organization into regions makes it much easier to navigate
your code, especially when your file begins to bloat to hundreds and
thousands lines of code. When all your teams classes/functions look like
that, you will find that you can easily navigate your code, even code
which has been written by other people.</p>
<p>Modern IDEs come with pragma, region or section statements that help
you structure your code. But if your programming language or IDE does
not support them, comments can do that job just as well.</p>
<p>Comments also help with structuring large functions:</p>
<pre><code class="code code_block">int main() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;// start system timer<br>
&nbsp;&nbsp;&nbsp;&nbsp;Timer timer = Timer::start();<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// get cli args<br>
&nbsp;&nbsp;&nbsp;&nbsp;string[] raw_args = get_cli_args();<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// parse cli args<br>
&nbsp;&nbsp;&nbsp;&nbsp;Args args = parse_args(raw_args);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// execute program<br>
&nbsp;&nbsp;&nbsp;&nbsp;run(args)<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// print duration<br>
&nbsp;&nbsp;&nbsp;&nbsp;TimeSpan elapsed = timer.elapsed();<br>
&nbsp;&nbsp;&nbsp;&nbsp;println(&quot;program finished in &quot; + elapsed);<br>
}</code></pre>
<p>To keep the example brief, the code between each comment is just a
single line. But when individual steps take 10-20 lines of code,
comments like this provide a great visual anchor.</p>
<p>Tangentially related: Prefer to structure blocks of code depending on
what they do. Instead of writing a wall of text:</p>
<pre><code class="code code_block">// bad<br>
do_operation_a();<br>
do_operation_b();<br>
do_operation_c();<br>
do_operation_x();<br>
do_operation_y();<br>
do_operation_z();</code></pre>
<p>do this:</p>
<pre><code class="code code_block">// good<br>
do_operation_a();<br>
do_operation_b();<br>
do_operation_c();<br>
<br>
do_operation_x();<br>
do_operation_y();<br>
do_operation_z();</code></pre>
<p>Sometimes it helps to squint your eyes and look at your code with
blurry vision. If your code fades into a massive blob, chances are that
it’s difficult to read. Thus, try to divide your code into discrete
blocks. A lone newline here and there can make your code much more
readable.</p>
<h3 id="comments-as-documentation">3.3 Comments as documentation</h3>
<p>One reason to use comments is documentation, in the form of
documentation comments. Syntax differs between programming languages and
doc systems, but in most cases you put a specially formatted comment
above a struct, class or function. This comment describes what the given
code does, which may be displayed in your IDE or may be extracted into a
separate document by an external tool.</p>
<p>From my experience, good documentation takes A LOT of time and
effort. Chances are you are underestimating how much work goes into good
documentation, so let me elaborate.</p>
<p>If you write documentation comments, it falls victim to the problem I
outlined at the start of this section: If behaviour changes, then the
documentation is outdated and potentially untrue. As such, proper
documentation must be maintained, just like the code they describe. And
maintenance costs time and money.</p>
<p>If the API implementation is private, for example a C API just
provides a headerfile with no source code, documentation makes sense.
Documentation also makes sense if the library you are writing is
intended to be used by external people, i.e. strangers on the internet,
customers or members of another team. But if the code is public and
accessible, any programmer can just look at the source code and simply
read what it does.</p>
<h3 id="comments-to-explain-confusing-code">3.4 Comments to explain
confusing code</h3>
<p>Let me remind you that that you will eventually break rule 1: “Good
code is understandable”. Rule 2: “Good code works” always holds. You
should write comments for code that breaks rule 1.</p>
<p>With comments you can explain why the code is the way it is.</p>
<p>Here’s some code, that is inspired by a real implementation of some
node generation code I have once worked with:</p>
<pre><code class="code code_block">List&lt;Node&gt; Deserialize(Data toDeserialize) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;var callbacks = new List&lt;Action&gt;();<br>
&nbsp;&nbsp;&nbsp;&nbsp;var nodes = new List&lt;Node&gt;();<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;BuildNodesRecursive(toDeserialize, callbacks, nodes);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;var mainThreadTask = _mainThreadDispatcher.Enqueue(() =&gt; {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;foreach(var callback in callbacks) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;callback();<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;});<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;mainThreadTask.Wait();<br>
&nbsp;&nbsp;&nbsp;&nbsp;return nodes;<br>
}<br>
<br>
void BuildNodesRecursive(Data data, List&lt;Action&gt; callbacks, List&lt;Node&gt; nodes) {<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// `BuildNodeFromData` must be run on the main thread.<br>
&nbsp;&nbsp;&nbsp;&nbsp;// however, benchmarking proved that `BuildNodesRecursive`<br>
&nbsp;&nbsp;&nbsp;&nbsp;// is called very often, which leads to contention in the<br>
&nbsp;&nbsp;&nbsp;&nbsp;// dispatcher. simply the act of enqueuing  many jobs leads<br>
&nbsp;&nbsp;&nbsp;&nbsp;// to a performance decrease. to increase performance, we<br>
&nbsp;&nbsp;&nbsp;&nbsp;// collect the actions instead, and enqueue them only once<br>
&nbsp;&nbsp;&nbsp;&nbsp;var buildNodeCallback = new Action(() =&gt; {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;var node = BuildNodeFromData(data);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;nodes.Add(node);<br>
&nbsp;&nbsp;&nbsp;&nbsp;});<br>
&nbsp;&nbsp;&nbsp;&nbsp;callbacks.Add(buildNodeCallback);<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;var children = FindChildren(data);<br>
&nbsp;&nbsp;&nbsp;&nbsp;foreach (Data child in children) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BuildNodesRecursive(child, callbacks, nodes);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}</code></pre>
<p>A specific function must be run on the main thread. So why wouldn’t we
just enqueue it immediately? Why go through the hassle to allocate a new
List and hand them through the functions? Well, the comment tells you
why.</p>
<p>Also note that such comments require discipline from you as a
programmer. If you modify code and find a comment nearby, you must
evaluate the comment. If it isn’t valid, you must correct it. If it
isn’t necessary anymore, you must remove it.</p>
<p>If you don’t remove invalid comments, then you have an invalid
comment in your codebase. Invalid comments will confuse the next
programmer that comes across it.</p>
<h2 id="miscellaneous">4. Miscellaneous</h2>
<h3 id="inheritance-is-evil">4.1 Inheritance is evil</h3>
<p>The OOP vs functional programming debate has been beaten to death. My
stance on it is that both have their strengths. Good programming
languages are multi paradigm and combine the best of both worlds.</p>
<p>But inheritance in particular is bad, and I discourage everyone from
using it.</p>
<p>Inheritance has a single good usecase: Polymorphism. If you require
polymorphism, having a single base class/interface with no
implementation is fine. But other than that, inheritance is to be
avoided at all costs. Use composition instead.</p>
<pre><code class="code code_block">// bad<br>
class Base {<br>
&nbsp;&nbsp;&nbsp;&nbsp;void say_hello();<br>
}<br>
<br>
class Child : Base {<br>
&nbsp;&nbsp;&nbsp;&nbsp;void say_hello() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;base.say_hello();<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}<br>
<br>
// good<br>
class Base {<br>
&nbsp;&nbsp;&nbsp;&nbsp;void say_hello();<br>
}<br>
<br>
class Child {<br>
&nbsp;&nbsp;&nbsp;&nbsp;Base _base;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;void say_hello() {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_base.say_hello();<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}</code></pre>
<p>This goes back to the idea that being explicit is better than
implicit.</p>
<p>Inheritance allows a lot of implicit behaviour, with keywords
allowing inheriting code to overwrite methods, hide or ignore them. When
you have a given type and call a method on it, it isn’t inherintly clear
what method of what type is now being called. If you have a
<code class="code">Child</code> stored in a <code class="code">Base</code> variable and call
<code class="code">say_hello()</code> on it, what method will be called? Depending on
the syntax and keywords, and your programming language of choice,
results may vary wildly.</p>
<p>With composition, you make classes reference each other, instead of
allowing one to be used in place of another. This completely removes any
ambiguity. Calling <code class="code">say_hello()</code> on <code class="code">Base</code> will
call the method on <code class="code">Base</code>. Calling it on <code class="code">Child</code>
will call it on <code class="code">Child</code>. How <code class="code">Child</code> decides to
route the call to <code class="code">Base</code>, if at all, is clearly stated in the
method body.</p>
<h3 id="encapsulate-behaviour-not-structure">4.2 Encapsulate behaviour,
not structure</h3>
<p>One of the most damaging “clean code” advice I see floating around
the web and in my office is this: Files shouldn’t have more than 100
lines of code, and functions not more than 10 lines of code. The actual
numbers change from source to source, but the idea is that a piece of
code shouldn’t be too long. This advice is bullshit and results in
unreadable garbage.</p>
<p>If the problem is complex enough, you will write a lot of code. Any
form of encapsulation is scattering your code. In very bad cases you
scatter your code over multiple files.</p>
<p>Here’s why this is a problem: When you go back and try to understand
how things work, you now need to take multiple files into account. You
require a mental map, which drastically increases the mental overhead
required to debug it.</p>
<p>Contrast that to a single big function that runs from top to bottom.
You know a code block on top runs before a code block below. The call
order is obvious. When the code is scattered, the call order is not that
obvious.</p>
<p>A good rule of thumb: If your function is used only a single time,
and it is private (meaning it is only accessible in the same
class/module/file were it is defined), it’s probably a good idea to
inline it. Follow the points described in <strong>3.2</strong> to
properly structure big functions.</p>
<p>Encapsulation should not be used because of structural reasons.
Encapsulation should be used because of behavioural reasons.</p>
<p>A good example for encapsulation is code reuse. If you have a piece
of code that is used more than once, you probably want to encapsulate it
in a dedicated function and call that, even if it is private.</p>
<p>Another good example is a public facing function. For example an
asset compiler may recursively iterate through a directory, copy each
file, assign each file an id, resolve the ids internally and write them
all into a single file. Client code shouldn’t care what these steps are
or how these work. All the client code cares about is that if it calls
<code class="code">compile()</code>, it does what it’s name is suggesting it
does.</p>
<h3 id="fix-warnings-and-lints">4.3 Fix warnings and lints</h3>
<p>Warnings try to make you aware that you are doing something
incorrectly. Yes, technically it compiles, and technically your code
appears to work, but in the edgecase it will break. As such, warnings
are to be taken seriously and they should be fixed. If your programming
language allows to disable warnings, <strong>NEVER</strong> do so. If
your programming language allows to enable even more pedantic warnings,
always do so, as rule 10 of <a
href="https://en.wikipedia.org/wiki/The_Power_of_10%3A_Rules_for_Developing_Safety-Critical_Code">“The
Power of 10: Rules for Developing Safety-Critical Code” by NASA</a>
states.</p>
<p>Fixing your warnings does not only make your code more stable, it
also helps to keep your development environment clean. Let me elaborate.
Whether you work with a terminal or IDE with a GUI, warnings and lints
attempt to catch your attention in one way or another.</p>
<p>For example you use a compiler in the terminal and your code doesn’t
compile. In that case it can take extra time to find and read the error,
when it is buried under hundreds of warnings.</p>
<p>In another example, when working with a GUI IDE, selecting a word
usually highlights all occurances of that word. This makes it easier to
find their usages in the same file. If your IDE is really fancy it even
highlights your scrollbar, as some sort of minimap, which allows you to
get some spatial idea of your file. However, if this view is cluttered
with warnings, features similar to this become unusable. And even if
your IDE GUI manages to seperate hints and warnings, they still use
valueable screenspace calling attention to themselves. This may hinder
your concentration.</p>
<p>Lints are not as problematic, but they hint at a better coding style.
When implemented, they often improve readibility and structure. Chances
are that you will come across some of the points I have discussed in
this blogpost as lints thrown by your linter. Depending on the
programming language and linter, lints can be somewhat subjective at
times, but it’s always good practice to consider their usefulness. If
they are useful, implement them. If they are not, manually disable the
lint.</p>
