<p>When writing a game engine, one thing you start to appreciate is the
simple UI of existing engines, like Unity. For example, adding a new
render target is as simple as attaching a camera to any game object.
Knowing how this stuff works under the hood, supporting multiple cameras
seems to me like an absolute nightmare to implement. The logistics are
the main problem: You require a render pass for each camera. Each one is
probably configured differently. A single hardcoded pass is already
difficult enough to set up. But having it be dynamically controlled?
That's something you don't build on a whim.</p>
<img src="https://rismosch.com/articles/the-case-for-porting-to-wii/unity.png" style="display: block; margin: auto; max-width: 100%;">
<p>Don't get me wrong, I like my engine. But at times I get lost in the
details, and I yearn for simpler times.</p>
<p>An idea that I've had last year, was to port my engine to the
Nintendo Wii. This appears to be a brainfart, but the more I think about
it, the more serious I am considering it.</p>
<p>My current biggest headache is the fact that I have no system
requirements. You see, if my engine were ever to produce a game, I would
like to have it run on as many machines as possible. No matter how
garbage your hardware, my game should be able to run on that.</p>
<p>I won't be able to go as low as the original Doom. It is a
masterpiece of programming, and it smartly took a lot of shortcuts to be
able to run on pretty much everything that has a processor. But exactly
because of these shortcuts, the graphics are too low fidelity for what I
want to do. I assume everyone who would like to play my game has a
machine that is capable to render much more complicated scenes.</p>
<img src="https://rismosch.com/articles/the-case-for-porting-to-wii/doom.jpg" style="display: block; margin: auto; max-width: 100%;">
<p>Anyway, I still want to depend on as little system requirements as
possible. If that sounds vague, well, that is because it is. I repeat
myself: I have no system requirements. I have no idea on what hardware I
need, to run my future game. And this causes me mental pain.</p>
<p>I could lean on <a
href="https://en.wikipedia.org/wiki/Wirth%27s_law" target="_blank" rel="noopener noreferrer">Wirth's law</a>, but
where's the fun in that?</p>
<p>I am reading up on cool rendering techniques all the time. Expensive
ones, that create high fidelity with little or no required consideration
for asset creation. But many new cool features require compute shaders.
If your GPU can't run these, then your out of luck. Virtually all modern
GPUs support compute shaders, but embedded systems often don't. And I
simply don't have the foresight to predict how platforms are going to
look like in 10 or more years. Especially with AI currently ruining
everything.</p>
<p>At this moment, I am betting everything on Vulkan. Its main criticism
is its verbosity. And even though I like the control, the complexity is
starting to drain on me. It is so much work to get something to render.
And this is motivation enough for people to leave. If Vulkan gets too
unpopular, support will end and future hardware won't run anything that
uses it. OpenGL is still kicking, but it is approaching its end more and
more, and I fear Vulkan will face the same fate.</p>
<p>It's a similar story with the OS. You can completely forget Apple and
Android, as they require binaries to be built for specific hardware
versions. Longevity is not achievable there. Linux isn't better, as its
OS protocols are in constant flux, meaning any binary you distribute
won't work on different/newer distros. Windows is stable, and Microsoft
has put in great effort into backwards compatibility. However,
considering how much slop that company has produced in the last year, I
have no trust that Windows will keep being stable.</p>
<p>With such unstable platforms, and me having no idea about system
requirements, I am seriously doubting whether PC should be my primary
target.</p>
<img src="https://rismosch.com/articles/the-case-for-porting-to-wii/wii.jpeg" style="display: block; margin: auto; max-width: 100%; width: 300px">
<p>Enter console gaming. Consoles have fixed hardware. Their chipset is
known and well defined. As long as hardware of a console exists, code
for it will keep runnning. People aren't ditching consoles, like they
are changing parts in a PC. People even go to great efforts to preserve
them. Also, emulators exist, making console games playable everywhere
where an emulator runs. And people are building new hardware,
specifically to physically emulate the original stuff.</p>
<p>So why exactly the Wii? Simply put: Nostalgia. I have very fond
memories on playing on the Wii. And I think it's capable enough for what
I have in mind.</p>
<p>I've been following <a href="https://www.youtube.com/@KazeN64" target="_blank" rel="noopener noreferrer">Kaze
Emanuar</a> and his rewrite of Super Mario 64. He is a prime example how
non-dead development for the Nintendo 64 is. I don't see why the Wiis
story would be different. This is especially true, since the Wii sold
more units than the Nintendo 64.</p>
<p>But, porting to the Wii comes with great drawbacks.</p>
<p>First, I need to rewrite my entire engine, or most of it. This would
be a lot of work, but a part of me thinks this would be manageable. Much
of the work has been gone in learning game engine architecture, not
writing it. By that I mean, if I were to start anew, reaching the point
I am standing now will take significantly less time, than all the time I
have already spend to get here.</p>
<p>The second and probably bigger drawback is, that I would need to
switch to C. <a href="https://github.com/rust-wii/ogc-rs" target="_blank" rel="noopener noreferrer">A Rust port
for Wii libraries exists</a>, but over a weekend I only managed to run a
Hello World triangle on the <a href="https://dolphin-emu.org/" target="_blank" rel="noopener noreferrer">Dolphin
Emulator</a>, not on original hardware. The error surely is on my side
somewhere, but the <a href="https://wiibrew.org/wiki/DevkitPPC" target="_blank" rel="noopener noreferrer">homebrew
compiler</a> just worked out of the box.</p>
<video style="width: 300px; max-width:100%; display: block; margin: auto;" loading='lazy' controls>
<source src="https://rismosch.com/articles/the-case-for-porting-to-wii/hello_world.mp4" type="video/mp4">
</video>
<p>This whole situation is kind of ironic. I previously <a
href="https://www.rismosch.com/article?id=rust-is-the-future" target="_blank" rel="noopener noreferrer">glazed
Rust</a> and talked about how <a
href="https://www.rismosch.com/article?id=the-empty-mind-between-milestones" target="_blank" rel="noopener noreferrer">I
don't understand why someone would rewrite their engine from
scratch</a>. And now I find myself exactly in the situation where I am
considering to drop Rust and rewrite everything. Fucking hypocrite.</p>
<p>Now, I haven't written C since university, and I am definitely
looking at it through rose tinted glasses. Ultimately I think Rust is
the better language, because of it's very strict typing and compiler
guarantees. But at the very least I imagine C to be much better than
C++. I hate C++. I dread it everytime I have to use it.</p>
<p>In any case, I am standing at a crossroad. Continuing with Rust would
be the easy choice. I already have a somewhat working engine, even if it
isn't feature complete. Working on that is the way of least resistance,
and it wouldn't require me to throw my entire progress into the garbage
bin. However, having a game run on the Wii would be hella cool.</p>
<p>These thoughts sprung into my head recently, probably because I
haven't written a single line of code this year. This gives me time to
think. Actually, I have been learning Blender, and I started to make
actual assets for my game. Here's a character I am currently
modelling:</p>
<img src="https://rismosch.com/articles/the-case-for-porting-to-wii/progress.png" style="display: block; margin: auto; max-width: 100%;">
<p>The closer I get to being feature complete, the less excuses I have
to finally start working on assets. For example, I previously postponed
the game object system almost an entire year. I did so, because of the
fear that with it completed, I had to actually make something
presentable. Working on a game engine is a very safe space to be in
mentally. I can have a superiority complex, without actually producing a
sellable product.</p>

<blockquote style="background-color:var(--pico-8-white); border: 5px solid var(--pico-8-cyan); padding: 20px;">
    <p>🤓 "Since I am making an engine and you don't, I am automatically better than you" \s</p>
</blockquote>

<p>But I did actually finish the game object system, and assets are
still waiting to be made. Turns out there is much more to a working game
than just game objects.</p>
<p>My current roadmap involves finishing to model the character in
Blender, including rigging, UV mapping and texturing. Then I'd like to
implement materials, followed by bones, skinned meshes and animations.
Somewhere I'd also like to work on lighting and shadows. And the terrain
renderer should finally be implemented.</p>
<p>This roadmap is absolutley subject to change. (Which it already has
as I am working through the C book…) And if I am going to switch to Wii,
then it will definitely delay everything else. But once the character is
done, we'll see if I actually try to switch, or if I'm too big of a
pussy to try.</p>
<p>Wish me luck!</p>
