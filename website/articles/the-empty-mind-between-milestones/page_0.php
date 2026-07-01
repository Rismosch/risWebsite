<p>Everyone says don’t make a game engine. It’s too much work. But no
one is telling you how much work it actually is. I am not going to argue
why you would write a game engine in the first place; I’ve done that
already <a
href="https://www.rismosch.com/article?id=why-i-make-my-own-game-engine" target="_blank" rel="noopener noreferrer">here</a>.</p>
<p>Overall, the progress feels fractal-like.</p>
<p>Making a custom game is easy actually! You just need two steps:</p>
<p>First, make the engine!<br />
Second, make the game!</p>
<p>So you get to work and consider the structure of your main loop. You
work out how startup and shutdown happens. You are feeling fancy and
make a restart mechanism. Nice. The mainloop runs 3 things: input,
logic, output. And you gotta find out how these 3 talk to each
other.</p>
<p>Okay. Let’s do one by one.</p>
<p>In the input you figure out how to handle HIDs and how to remap them
to usable actions. You even implement some custom rebind system, that
allows the user to rebind controls if they choose to do so. Then you
might also think about handling multiple controllers. You might also
think to implement an input buffer, for combos and such. Maybe even
consider rollback code. But that’s a lot of work, and since you are not
planning to make anything multiplayer, let alone competitive
multiplayer, any further consideration is out of scope and wont be
touched whatsoever.</p>
<p>Then you do logic. You want gameobjects. People talk about ECS a lot.
It’s where the money is at. But to this day you don’t understand it and
you think it’s a buzzword. Same with anything procedural. Man, the
internet loves the word “procedural”. Here’s a fun game: Spend some time
in the indiedev-wannabe circles and see how far you come before
encountering the word.</p>
<p>Anyway, you set your requirements. You want your gameobjects to be
flexible, extensible and performant. You go with flat arrays internally,
for cache locality. Ints as ids, for the fastest lookup possible. But
this is were it bites you, because of course you are one of the weird
kids and chose to build the engine in Rust. And Rust really doesn’t like
what concept you have cooked up. But after much pain you have a working
prototype, which (hopefully) is as expansible as you think it is. But
you won’t test it now. You’ll maybe get to it in 2 years.</p>
<p>Oh, what about output? Ah, that’s were you draw them pixels. You
already spent the better part of a year working on this thing and have
yet to display something. Because you have a phobia of external
libraries, you are going to implement all the math yourself. Vectors,
matrices and the beloved quaternions. Especially because you did not
choose C++, and all the Rust libraries today are ass anyway, you
convince yourself that writing it yourself is better. You make a flying
camera, a single cube, that’ll do it.</p>
<p>You want debugging, so you implement Dear ImGUI. A bit hacked
together. Can’t use any existing bindings, because your renderer is
custom. So the ImGUI implementation has to be custom as well. Anyway,
let’s draw some gizmos. Like primitive lines, that’ll make debugging a
later game very easy. Also write a gizmo for 3d text. You say to
yourself this is absolutely necessary. The renderer you have written a
year ago is ass, so it needs to be rewritten. No biggy, gives you the
opportunity to use a better Vulkan wrapper. You hate the previous Vulkan
wrapper you used, surely Vulkan wrapper 2.0 is better. Once the new
wrapper is implemented, you get back to the gizmos, and implement them.
They are ugly as hell, but they do their job. You imagine yourself using
that in the upcoming game you will write. But we will see about that in
2 years.</p>
<p>Also write a benchmarking tool. It produces HTML. It looks beautiful.
This will definitely be helpful. In 2 years.</p>
<p>But then you realize that all this debugging takes a lot of
performance. A shipped game is not going to require all of this, and
stripping it out will massively improve performance. So you look into
conditional compiling and guard every debug feature behind some
preprocessor. It works, and you are proud of how many frames you get in
an empty scene.</p>
<p>Anyway, time to display some meshes. Fuck, we need to load these in
first. So you roll up your sleeves and write an asset system. Turns out
all common file formats suck and none are optimized for fast loading.
So, you make your own serialization and tinker on an entire asset
pipeline, which is capable to convert editor assets to fast loading
runtime assets.</p>
<p>Also write an asset compiler. Because you see, IO is expensive, and
having a single handle to a file is faster. At least that’s what is
claimed, you have yet to do benchmarks. Damn, if only you had a
benchmarking tool to test that. Oh well. The compiled thing is a single
huge file that holds everything. It has a lookup! Which makes indexing
very very fast. Flat arrays for the win.</p>
<p>After some time later you have a barely working asset system. So lets
get back to meshes and load them. What kind of format are you going to
implement anyway? glTF. That seems to be the standard. So you read the
glTF spec from start to finish and write an importer that converts glTF
to your mesh format. It’s like a year since you first wanted to render
meshes, but now you finally have all the systems to do so. Hurray!</p>
<p>Eventually you say to yourself that you should start to make the
game, no? But what’s missing? Oh, not much:</p>
<ul>
<li>any gameplay logic</li>
<li>level loading</li>
<li>character movement controllers</li>
<li>skinned meshes</li>
<li>skeletons</li>
<li>animation systems</li>
<li>lighting (bruh)</li>
<li>sound</li>
</ul>
<p>Wait, sound? You produced songs in the past! You literally describe
yourself as a music enthusiast and you have yet to do anything sound
related? Well… it isn’t important for the prototype, so it’s shelved.
It’s been shelved for so long actually, that you are starting to wonder
if your musical ability took a hit. Anyway, that’s not important for
now, so let’s start making the game.</p>
<p>But oh no, you want the character to stand on something. We gotta
make the floor first. Prototype is shelved, it’s time for terrain
generation baby! This can’t be that difficult! 4 months later you
finally have a terrain generator finished, and have yet to render it.
God dammit. Time to finally implement materials and lighting. Why now?
Because if you were to write the terrain renderer now, you will have to
rewrite it <em>again</em> once your entire render pipeline has to be
adapted for materials. So the terrain has to wait.</p>
<p>You look back at the goal you set this year. You wanted to have a
playable prototype and yet you spent half of the year on a fancy <a
href="https://github.com/Rismosch/ris_terrain_generator" target="_blank" rel="noopener noreferrer">picture
generator</a>.</p>
<p>Anyway, how does the engine look like?</p>
<img src="https://rismosch.com/articles/the-empty-mind-between-milestones/engine_screenshot.png" alt="screenshot of the engine" style="display: block; margin: auto; max-width: 100%;"/>
<p><em>Sigh…</em></p>
<p>Looking at the screenshot fills you with dread. It encapsulates the
last 3 years of your life. How can so much work look like nothing? You
went the walk, you know all the shit that is required to display that
damn image above. Getting to this point is not easy. And yet, it’s
demoralizing. To be fair, you have a fulltime job and thus not much
freetime. But still.</p>
<p>You check online, to see how others are doing. People are posting
their game engines, and most often you just scoff at them. There exist
only 3 emotions:</p>
<ol type="1">
<li>The poster did not post their source code. You feel apathy and
disrespect towards them.</li>
<li>The poster did post their source code, but you quickly realize it’s
just an ECS library glued to a renderer library. Sometimes it even uses
ImGUI. How nice. You clone the repo, remove all third party libraries,
run it through a sloc counter and reveal that the repo has at most 2000
lines of code. Your engine has 39k, many of which have been beaten to
death, rewritten multiple times, so you are obviously better. Their
screenshots look nice, better than yours. But <em>actually</em> you are
superior, because <em>technically</em> they haven’t written their
“engine”.</li>
<li>The poster actually has something to show, and this is their
thousandth update. You give it an upvote, nod in approval, but spend no
longer than 10 seconds before going back to your own shit.</li>
</ol>
<hr />
<p>The progress really feels like a fractal.</p>
<p>Every tangent, no matter how small it seems, is a major rabbit hole,
with tangents on its own. I have written the paragraphs above in the
you-narrator, but it’s basically me. I’ve described my journey in a not
so chronological order. At every step in the journey, you think you just
need to take one step further, only to reveal that between your current
position and the step you wanted to take are 100 more steps that you
were oblivious to previously.</p>
<p>After all that work I have many loosely connected systems, which by
some sort of miracle have not yet collapsed under their own weight. This
is either a testament to my software architecture skills, or luck.
Anyway, everything still needs to be stresstested, which I still haven’t
done, because there is always a new system to write.</p>
<p>There is one somewhat common kind of post you’ll find in game engine
dev circles: People rewriting their <em>entire</em> engine. I just can’t
relate to that. There is too much stuff going on. Besides, <a
href="https://www.joelonsoftware.com/2000/04/06/things-you-should-never-do-part-i/" target="_blank" rel="noopener noreferrer">rewriting
your code is one of the worst things you can do as a software
developer</a> (if we are considering that you are not doing some
crypto/AI bullshit, scamming and actually hurting people). Like, sure, a
rewrite can make things easier when implementing new features. For
example when I am finally getting to implement skinned meshes,
materials, lighting, MDI, then my scene renderer is going to face a lot
of refactoring. But you know what doesn’t have to change? My gizmo and
ImGUI renderers. My startup-, shutdown- and restart logic does not need
to be touched. My gameobjects will function exactly the same.</p>
<p>Often I’ve heard the saying that this kind of work is a marathon, not
a sprint. However, I don’t agree with that idea. More fitting is like
that meme of the guy who climbs a mountain, only to realize it goes up
further:</p>
<img src="https://rismosch.com/articles/the-empty-mind-between-milestones/climbing.jpg" alt="climbing meme" style="display: block; margin: auto; max-width: 100%; width: 400px;" />
<p>Each dip is a finished system. And reaching one is what I think
enlightenment feels like. Months of constant thoughts about a specific
problem. All the branching ideas I’ve tried, all the decisions I’ve
made. And when the system is done, the thoughts disappear. When before
my head was full of voices, now it’s dead silent. It’s these moments
where there’s not a single thought in my mind. I am literally thinking
of nothing. It is bliss.</p>
<img src="https://rismosch.com/articles/the-empty-mind-between-milestones/now_what.jpg" alt="sisyphus reached the top meme" style="display: block; margin: auto; max-width: 100%; width: 400px;" />
<p>But the journey is not over, and after a few days the climb
continues. You take the courage, touch the boulder again and start
pushing. A new system is started or work on a current one proceeds, and
my head begins to fill with thoughts again.</p>
<img src="https://rismosch.com/articles/the-empty-mind-between-milestones/sisyphus.png" alt="sisyphus meme" style="display: block; margin: auto; max-width: 100%; width: 400px;" />
<p>One last thought: “I have to write everything on my own” is a
delusional idea. Let’s get back down to reality. This year at SIGGRAPH
2025 I met someone who is writing a Vulkan driver. I thought I was low
level, and yet this person does me one better. But after much
discussion, even they had to admit that you truly can’t write everything
on your own.</p>
<p>You are working with a programming language and its tooling. Someone
had to make that. You are working with a text editor and operating
system. And you are working with a computer, someone had to build that.
And I don’t mean stick the parts together. No, I mean someone had to
make the chips on these circuit boards. But even if you are doing a <a
href="https://www.youtube.com/@BenEater" target="_blank" rel="noopener noreferrer">Ben Eater</a> and literally
build your own computer, you still have to admit one crucial detail: You
are building on top of the <em>knowledge</em> of the ones before you.
You are not going to reinvent the graphics pipeline. You will learn how
it’s done and then give your own spin on it.</p>
<p>We are really standing on the shoulders of giants.</p>
<p>I am not saying that as an argument against building an engine. My
journey gave me understandings and proficiencies most other developers
don’t have. But it’s an anchor in reality that one should not lose.</p>
