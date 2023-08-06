<p>If you have come this far in my blogpost, I have to admit that I lied. In the last chapter I said you only need HTML, CSS and JavaScript to display a website, but there is actually a lot more to it. If you are still here, then there is one deceptively complicated question left: What if you want to dynamically change the HTML that is send to the browser? For example, you are reading this article on the Article page. And depending on what id is entered in the URL, a different HTML is displayed. Crazy! Then there is my blog. I didn’t hardcode the selection of projects and posts into the HTML. As a matter of fact, the data lies in a database, and the HTML is dynamically created, depending on what is actually stored in the database. And then there is my Newsletter and Contact form.</p>

<p>That’s a lot of stuff which I still haven’t covered, and the painful truth is, HTML, CSS and JavaScript are absolutely not capable of implementing these things. What we need, is some sort of backend which generates an HTML, before it is send to the users’ browser.</p>

<p>There are many ways to implement a backend. I chose PHP, because it was just plug and play and it worked on the spot. If you believe what Reddit tells you, then PHP is the most horrendous programming language ever conceived. My opinion? I don’t mind it. Sure, it has some inconsistencies and is poorly designed, but like with any programming language, you face obstacles which you need to overcome. With Node and Ruby and whatever, you need to install extra stuff. For PHP I just needed to make a PHP file and it just works.</p>

<h2>PHP</h2>

<p>So what actually is PHP? PHP stands for Hypertext Preprocessor (it’s actually a backronym, previously it meant “Personal Home Page Tools”) and it exactly solves the problem which I mentioned in the intro of this chapter: When the user requests a PHP file, the webserver runs the code of the PHP file and spits out an HTML, which is then send to the user.</p>

<?php late_image(get_source("picture_3.webp"),"","max-width:100%; margin:auto; display: block;"); ?>

<p>During the execution of the PHP file, you can do whatever you want. You can handle the request, talk to a database, and generate an HTML. Below is a simple PHP code example:</p>

<p class="code code_block">
<span style="color:var(--pico-8-red)">&lt;?php</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">echo</span> <span style="color:var(--pico-8-dark-grey)">“&lt;h1&gt;Hello World&lt;/h1&gt;”</span><span style="color:var(--pico-8-purple)">;</span><br>
<span style="color:var(--pico-8-red)">?&gt;</span>
</p>

<p><span class="code">echo</span> is the command, which spits out HTML. After running that file, you simply get:</p>

<p class="code code_block">
<span style="color:var(--pico-8-cyan)">&lt;h1&gt;</span>Hello World<span style="color:var(--pico-8-cyan)">&lt;/h1&gt;</span>
</p>

<p>Pretty easy. The cool thing about PHP is, that you write it just like regular HTML, except where you want backend code to be executed. You simply need to insert a <span class="code">&lt;?php?&gt;</span> tag and that’s it. The PHP tag can be inserted EVERYWHERE, meaning you can not only create HTML tags, but just any string you can imagine.</p>

<p>Take for example the <i>actual</i> source code of my blog-page:</p>

<p class="code code_block">
<span style="color:var(--pico-8-red)">&lt;?php</span><br>
<br>
$article_type_id <span style="color:var(--pico-8-purple)">=</span> <span style="color:var(--pico-8-orange)">0</span><span style="color:var(--pico-8-purple)">;</span><br>
<br>
<span style="color:var(--pico-8-cyan)">include</span> <span style="color:var(--pico-8-dark-grey)">'secret/secret.php'</span><span style="color:var(--pico-8-purple)">;</span><br>
<span style="color:var(--pico-8-cyan)">include</span> <span style="color:var(--pico-8-dark-grey)">'php/articles_database.php'</span><span style="color:var(--pico-8-purple)">;</span><br>
<span style="color:var(--pico-8-cyan)">include</span> <span style="color:var(--pico-8-dark-grey)">'php/util.php'</span><span style="color:var(--pico-8-purple)">;</span><br>
<br>
echo_head<span style="color:var(--pico-8-purple)">();</span><br>
<br>
<span style="color:var(--pico-8-red)">?&gt;</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;title&gt;</span>Blog<span style="color:var(--pico-8-cyan)">&lt;/title&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">name</span>=<span style="color:var(--pico-8-purple)">"description"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"Blog of Simon Sutoris"</span><span style="color:var(--pico-8-cyan)">&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">name</span>=<span style="color:var(--pico-8-purple)">"keywords"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"blog"</span><span style="color:var(--pico-8-cyan)">&gt;</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">name</span>=<span style="color:var(--pico-8-purple)">"robots"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"all"</span><span style="color:var(--pico-8-cyan)">&gt;</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;meta</span> property=<span style="color:var(--pico-8-purple)">"og:title"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"Blog"</span> <span style="color:var(--pico-8-cyan)">/&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;meta</span> property=<span style="color:var(--pico-8-purple)">"og:type"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"website"</span> <span style="color:var(--pico-8-cyan)">/&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;meta</span> property=<span style="color:var(--pico-8-purple)">"og:url"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"https://www.rismosch.com/blog"</span> <span style="color:var(--pico-8-cyan)">/&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;meta</span> property=<span style="color:var(--pico-8-purple)">"og:image"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"https://www.rismosch.com/assets/meta_image_x10.png"</span> <span style="color:var(--pico-8-cyan)">/&gt;</span><br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;meta</span> <span style="color:var(--pico-8-red)">name</span>=<span style="color:var(--pico-8-purple)">"author"</span> <span style="color:var(--pico-8-red)">content</span>=<span style="color:var(--pico-8-purple)">"Simon Sutoris"</span><span style="color:var(--pico-8-cyan)">&gt;</span><br>
<br>
<span style="color:var(--pico-8-cyan)">&lt;/head&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;body&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;div</span> <span style="color:var(--pico-8-red)">class</span>=<span style="color:var(--pico-8-purple)">"background"</span><span style="color:var(--pico-8-cyan)">&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">&lt;?php</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo_banner<span style="color:var(--pico-8-purple)">();</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo_selector<span style="color:var(--pico-8-purple)">(</span><span style="color:var(--pico-8-orange)">1</span><span style="color:var(--pico-8-purple)">);</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">?&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;div</span> <span style="color:var(--pico-8-red)">class</span>=<span style="color:var(--pico-8-purple)">"content"</span> <span style="color:var(--pico-8-red)">id</span>=<span style="color:var(--pico-8-purple)">"content"</span><span style="color:var(--pico-8-cyan)">&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;h1&gt;</span>Blog<span style="color:var(--pico-8-cyan)">&lt;/h1&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">&lt;?php</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$dbSelectConnection <span style="color:var(--pico-8-purple)">=</span> <span style="color:var(--pico-8-cyan)">mysqli_connect</span><span style="color:var(--pico-8-purple)">(</span>$dbHost<span style="color:var(--pico-8-purple)">,</span> $dbSelectUsername<span style="color:var(--pico-8-purple)">,</span> $dbSelectPassword<span style="color:var(--pico-8-purple)">,</span> $dbName<span style="color:var(--pico-8-purple)">);</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">if</span><span style="color:var(--pico-8-purple)">(</span>$dbSelectConnection)<span style="color:var(--pico-8-purple)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$pageName <span style="color:var(--pico-8-purple)">=</span> <span style="color:var(--pico-8-dark-grey)">"blog"</span><span style="color:var(--pico-8-purple)">;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;printDropdown<span style="color:var(--pico-8-purple)">(</span>$dbSelectConnection<span style="color:var(--pico-8-purple)">,</span> $pageName<span style="color:var(--pico-8-purple)">);</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;printArticles<span style="color:var(--pico-8-purple)">(</span>$dbSelectConnection<span style="color:var(--pico-8-purple)">,</span> $pageName<span style="color:var(--pico-8-purple)">);</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;printSelector<span style="color:var(--pico-8-purple)">(</span>$dbSelectConnection<span style="color:var(--pico-8-purple)">,</span> $pageName<span style="color:var(--pico-8-purple)">);</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">else</span><span style="color:var(--pico-8-purple)">{</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">echo</span> <span style="color:var(--pico-8-dark-grey)">"&lt;h1&gt;:(&lt;/h1&gt;&lt;p&gt;Error while loading articles.&lt;/p&gt;"</span><span style="color:var(--pico-8-purple)">;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-purple)">}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">?&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;/div&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red)">&lt;?php</span> echo_foot<span style="color:var(--pico-8-purple)">(</span><span style="color:var(--pico-8-cyan)">false</span><span style="color:var(--pico-8-purple)">);</span> <span style="color:var(--pico-8-red)">?&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan)">&lt;/div&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;/body&gt;</span><br>
<span style="color:var(--pico-8-cyan)">&lt;/html&gt;</span>
</p>

<p>So this looks quite different from the HTML which will be displayed in your browser, but it is apparent that the site is dynamically created. So let’s talk about what this code is actually doing. The <span class="code">echo_x()</span> methods should be fairly self-explanatory: They echo the specific part of the HTML. All my pages contain a similar header. Also, the banner, selector and foot links are pretty much always the same. Implementing the same thing on every page is a pain in the butt, because if I want to make changes to the banner for example, I need to change it in every single file. It’s easier if I have one PHP file, which contains the method to print the banner, so that I can change the banner in just one file.</p>

<p>The methods differ a little bit from each other, for example the banner and the header take no inputs, the <span class="code">echo_selector($active_tab)</span> method takes an <span class="code">int</span>, to know what selector tab needs to be active. The <span class="code">echo_foot($uses_captcha)</span> takes a <span class="code">bool</span>, which displaces the “back to top” button, when reCAPTCHA is used. You may know that if a site uses reCAPTCHA, a small UI window in the bottom right is displayed. I don’t want that overlapping with the button, so I displace the button. More to reCAPTCHA in a following chapter.</p>

<p>And this is basically it. Once you wrap your head around it, PHP is actually very simple. If you take a look at my <a href="https://github.com/Rismosch/risWebsite/blob/main/website/php/util.php" target="_blank" rel="noopener noreferrer">util.php</a> code, you will find the code you expect: Just simple echoes which echo the specific HTML. Nothing special.</p>

<h2>Databases</h2>

<p>Okay, so now that we understand what PHP is and how to use it, let’s talk about how I used it to make the blog on my website. Here’s the main idea: No matter how many blogposts I will create, the surrounding website will always look the same. Wouldn’t it be nice if we could store the individual blogposts somewhere, and then use PHP to display them on our page?</p>

<p>Yes, that would be nice. The solution to this answer is simply a database. A database is an organized storing system, that stores data efficiently, consistently and persistently. While there are other storing systems that may also work for a blog, like using a file, a database has a fundamental advantage: As a client, I can get exactly the data that I specify. Why this is such an advantage, we’ll see in a moment. But first it may be a good idea to explain how a database actually works.</p>

<p>A database stores data in tables. A table is defined by its columns. A data entry is a simply a row in such a table. A table usually contains a key, which uniquely identifies a row. In most cases, such a key is simply an id. Here’s an example how the articles table looks like in my database:</p>

<?php late_image(get_source("picture_4.webp"),"","max-width:100%; margin:auto; display: block;"); ?>

<p>You may notice, that this table doesn’t directly store its type (blog/project) or category (music, blog, etc.). Instead it stores an id of the specific column. The actual types and categories are stored in a separate table. This is to make usage easier and more importantly to reduce redundance. Storing the same data multiple times may eat a lot of space. So by storing it in a separate table, every entry will be stored only once, and then only the id needs to be referenced. Such ids which are referenced by another table, are called foreign keys.</p>

<p>So in a nutshell, a database is just a complicated way to store stuff. Great. Now after storing stuff in a database, you somehow need to retrieve it. This can be done with a language called SQL. SQL stands for Structured Query Language and it is the main way that you will talk to your database. Consider the following SQL statement:</p>

<p class="code code_block">
<span style="color:var(--pico-8-purple);">SELECT</span> <span style="color:var(--pico-8-red);">*</span> <span style="color:var(--pico-8-purple);">FROM</span> Articles
</p>

<p>This statement retrieves all data from the table Articles. So this pretty much results into the screenshot I have posted above. But things get a little bit more exciting when we add conditions:</p>

<p class="code code_block">
<span style="color:var(--pico-8-purple);">SELECT</span> <span style="color:var(--pico-8-red);">*</span> <span style="color:var(--pico-8-purple);">FROM</span> Articles <span style="color:var(--pico-8-purple);">WHERE</span> id<span style="color:var(--pico-8-red);">=</span><span style="color:var(--pico-8-cyan);">'good-enough-is-sometimes-not-good-enough'</span>
</p>

<p>This only retrieves the rows, with the id of "good-enough-is-sometimes-not-good-enough". Since the id is unique, only one row will be returned: <a href="https://www.rismosch.com/article?id=good-enough-is-sometimes-not-good-enough" target="_blank" rel="noopener noreferrer">The gamejam that broke me</a>. With further conditions we can combine tables, sort them or only retrieve a small section of the entire database. Definitely the most complicated SQL statement which I use on my website is this:</p>

<p class="code code_block">
<span style="color:var(--pico-8-purple);">SELECT</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;Articles.<span style="color:var(--pico-8-cyan);">id</span> <span style="color:var(--pico-8-purple);">AS</span> id,<br>
&nbsp;&nbsp;&nbsp;&nbsp;Article_Types.<span style="color:var(--pico-8-cyan);">name</span> <span style="color:var(--pico-8-purple);">AS</span> type,<br>
&nbsp;&nbsp;&nbsp;&nbsp;Article_Categories.<span style="color:var(--pico-8-cyan);">name</span> <span style="color:var(--pico-8-purple);">AS</span> category,<br>
&nbsp;&nbsp;&nbsp;&nbsp;Articles.<span style="color:var(--pico-8-cyan);">title</span> <span style="color:var(--pico-8-purple);">AS</span> title,<br>
&nbsp;&nbsp;&nbsp;&nbsp;Articles.<span style="color:var(--pico-8-cyan);">timestamp</span> <span style="color:var(--pico-8-purple);">AS</span> timestamp,<br>
&nbsp;&nbsp;&nbsp;&nbsp;Articles.<span style="color:var(--pico-8-cyan);">link</span> <span style="color:var(--pico-8-purple);">AS</span> link,<br>
&nbsp;&nbsp;&nbsp;&nbsp;Articles.<span style="color:var(--pico-8-cyan);">thumbnail_path</span> <span style="color:var(--pico-8-purple);">AS</span> thumbnail_path<br>
<span style="color:var(--pico-8-purple);">FROM</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;Articles,<br>
&nbsp;&nbsp;&nbsp;&nbsp;Article_Categories,<br>
&nbsp;&nbsp;&nbsp;&nbsp;Article_Types<br>
<span style="color:var(--pico-8-purple);">WHERE</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;Articles.<span style="color:var(--pico-8-cyan);">category_id</span> <span style="color:var(--pico-8-red);">=</span> Article_Categories.<span style="color:var(--pico-8-cyan);">id</span> <span style="color:var(--pico-8-purple);">AND</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;Articles.<span style="color:var(--pico-8-cyan);">type_id</span> <span style="color:var(--pico-8-red);">=</span> Article_Types.<span style="color:var(--pico-8-cyan);">id</span> <span style="color:var(--pico-8-purple);">AND</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;Articles.<span style="color:var(--pico-8-cyan);">type_id</span> <span style="color:var(--pico-8-red);">=</span> <span style="color:var(--pico-8-cyan);">0</span><br>
<span style="color:var(--pico-8-purple);">ORDER BY</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;Articles.<span style="color:var(--pico-8-cyan);">timestamp</span> <span style="color:var(--pico-8-purple);">DESC</span><br>
<span style="color:var(--pico-8-purple);">LIMIT</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">0</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">10</span>
</p>

<p>I select from 3 tables: <span class="code">Articles</span>, <span class="code">Article_Categories</span> and <span class="code">Article_Types</span>. With the first two <span class="code">WHERE</span>-statements, I make sure that each row only appears once and correct. With <span class="code">ORDER BY</span> I order them from newest to oldest. With <span class="code">LIMIT</span> I specify to only get 10 results and finally with <span class="code">SELECT</span> I select all columns that I actually want to use.</p>

<p>Why do we do that complicated stuff again? Because the conditions allow us to filter the data. For example by changing the <span class="code">LIMIT</span>, I can display more or less blogposts on my site. By changing the third condition <span class="code">“Articles.type_id = 0”</span> I can either display blogs or projects. I can even add another condition to only filter for music, or programming, or whatever. This filtering is super powerful, which is the main reason why I am using a database.</p>

<p>Now that we understand that, we need to implement this into PHP:</p>

<p class="code code_block" style="color:var(--pico-8-purple);">
<span style="color:var(--pico-8-green);">// connect to database</span><br>
<span style="color:var(--pico-8-black);">$dbConn</span> = <span style="color:var(--pico-8-cyan);">mysqli_connect</span>(<span style="color:var(--pico-8-black);">$dbHost</span>, <span style="color:var(--pico-8-black);">$dbUsername</span>, <span style="color:var(--pico-8-black);">$dbPassword</span>, <span style="color:var(--pico-8-black);">$dbName</span>);<br>
<br>
<span style="color:var(--pico-8-green);">// test if connection is successful</span><br>
if(<span style="color:var(--pico-8-black);">$dbConn</span>)<br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green);">// execute sql query and get the total amount of rows</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$result</span> = <span style="color:var(--pico-8-cyan);">mysqli_query</span>(<span style="color:var(--pico-8-black);">$dbConn</span>,<span style="color:var(--pico-8-black);">$sql</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$numRows</span> = <span style="color:var(--pico-8-cyan);">mysqli_num_rows</span>(<span style="color:var(--pico-8-black);">$result</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">if</span>(<span style="color:var(--pico-8-black);">$numRows</span> > <span style="color:var(--pico-8-orange);">0</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green);">// fetch the result</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">while</span>(<span style="color:var(--pico-8-black);">$row</span> = <span style="color:var(--pico-8-cyan);">mysqli_fetch_assoc</span>(<span style="color:var(--pico-8-black);">$result</span>))<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-green);">// do stuff with each row</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">echo</span> <span style="color:var(--pico-8-black);">$row</span>[<span style="color:var(--pico-8-dark-grey);">'id'</span>] . " " . <span style="color:var(--pico-8-black);">$row</span>[<span style="color:var(--pico-8-dark-grey);">'title'</span>] . <span style="color:var(--pico-8-dark-grey);">"&lt;br&gt;"</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</p>

<p>This code connects to a database, then if the connection is successful, executes the query, and then fetches each row and finally simply prints the data of each row. <span class="code">$sql</span> is the SQL which I explained before. As you see <span class="code">$row</span> is simply an array with the column names as keys. With this, you can then use the data however you like. You can put it in a list, or table, or generate links. You can really do whatever with your data.</p>

<p>Now that you are a bit better equipped to understand PHP and SQL, you can take a look at my PHP code for various files again and see, that it’s just a blown-up case of the code above, just with HTML and CSS to make everything look nice.</p>

<h3>part of blog.php</h3>

<a href="https://github.com/Rismosch/risWebsite/blob/main/website/blog.php" target="_blank" rel="noopener noreferrer"><i>view file on GitHub</i></a>

<p class="code code_block">
<span style="color:var(--pico-8-cyan);">&lt;div</span> <span style="color:var(--pico-8-red);">class</span>=<span style="color:var(--pico-8-purple);">"content"</span> <span style="color:var(--pico-8-red);">id</span>=<span style="color:var(--pico-8-purple);">"content"</span><span style="color:var(--pico-8-cyan);">&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">&lt;h1&gt;</span>Blog&lt;<span style="color:var(--pico-8-cyan);">/h1&gt;</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-red);">&lt;?php</span><span style="color:var(--pico-8-purple);"><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$dbSelectConnection</span> = <span style="color:var(--pico-8-cyan);">mysqli_connect</span>(<span style="color:var(--pico-8-black);">$dbHost</span>, <span style="color:var(--pico-8-black);">$dbSelectUsername</span>, <span style="color:var(--pico-8-black);">$dbSelectPassword</span>, <span style="color:var(--pico-8-black);">$dbName</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">if</span>(<span style="color:var(--pico-8-black);">$dbSelectConnection</span>){<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$pageName</span> = <span style="color:var(--pico-8-dark-grey);">"blog"</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">printDropdown</span>(<span style="color:var(--pico-8-black);">$dbSelectConnection</span>, <span style="color:var(--pico-8-black);">$pageName</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">printArticles</span>(<span style="color:var(--pico-8-black);">$dbSelectConnection</span>, <span style="color:var(--pico-8-black);">$pageName</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">printSelector</span>(<span style="color:var(--pico-8-black);">$dbSelectConnection</span>, <span style="color:var(--pico-8-black);">$pageName</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">else</span>{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">echo</span> <span style="color:var(--pico-8-dark-grey);">"&lt;h1&gt;:(&lt;/h1&gt;&lt;p&gt;Error while loading articles.&lt;/p&gt;"</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color:var(--pico-8-red);">?&gt;</span><br>
<span style="color:var(--pico-8-cyan);">&lt;/div&gt;</span><br>
</p>

<h3>part of php/articles_database.php</h3>

<a href="https://github.com/Rismosch/risWebsite/blob/main/website/php/articles_database.php" target="_blank" rel="noopener noreferrer"><i>view file on GitHub</i></a>

<p class ="code code_block" style="color:var(--pico-8-purple);">
<span style="color:var(--pico-8-cyan);">function</span> <span style="color:var(--pico-8-black);">printArticles</span>(<span style="color:var(--pico-8-black);">$dbConn</span>, <span style="color:var(--pico-8-black);">$pageName</span>)<br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">global</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$categoryFilterString</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$show</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$offset</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$article_type_id</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$sqlArticles</span> = <span style="color:var(--pico-8-dark-grey);">"<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SELECT<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Articles.id AS id,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Article_Types.name AS type,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Article_Categories.name AS category,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Articles.title AS title,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Articles.timestamp AS timestamp,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Articles.link AS link,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Articles.thumbnail_path AS thumbnail_path<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;FROM<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Articles,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Article_Categories,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Article_Types<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;WHERE<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Articles.category_id = Article_Categories.id AND<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Articles.type_id = Article_Types.id AND<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Articles.type_id = <span style="color:var(--pico-8-black);">{$article_type_id}</span> AND<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">{$categoryFilterString}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ORDER BY<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Articles.timestamp DESC<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LIMIT<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">{$offset}</span>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">{$show}</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;"</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$result</span> = <span style="color:var(--pico-8-cyan);">mysqli_query</span>(<span style="color:var(--pico-8-black);">$dbConn</span>,<span style="color:var(--pico-8-black);">$sqlArticles</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$numRows</span> = <span style="color:var(--pico-8-cyan);">mysqli_num_rows</span>(<span style="color:var(--pico-8-black);">$result</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$totalRowsResult</span> = <span style="color:var(--pico-8-cyan);">mysqli_query</span>(<span style="color:var(--pico-8-black);">$dbConn</span>,<span style="color:var(--pico-8-dark-grey);">"SELECT COUNT(id) as count FROM Articles WHERE type_id=<span style="color:var(--pico-8-black);">{$article_type_id}</span> AND <span style="color:var(--pico-8-black);">{$categoryFilterString}</span>"</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$row</span> = <span style="color:var(--pico-8-cyan);">mysqli_fetch_assoc</span>(<span style="color:var(--pico-8-black);">$totalRowsResult</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">echo</span> <span style="color:var(--pico-8-dark-grey);">"<span style="color:var(--pico-8-black);">{$numRows}</span> of total <span style="color:var(--pico-8-black);">{$row['count']}</span> posts&lt;/p&gt;"</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">if</span>(<span style="color:var(--pico-8-black);">$numRows</span> &gt; <span style="color:var(--pico-8-orange);">0</span>)<br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">echo</span> <span style="color:var(--pico-8-dark-grey);">"<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;table style=\"width: 100%;\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr class=\"row_empty\"&gt;&lt;td&gt;&lt;/td&gt;&lt;/tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr class=\"row_empty row_devider\"&gt;&lt;td&gt;&lt;/td&gt;&lt;/tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">while</span>(<span style="color:var(--pico-8-black);">$row</span> = <span style="color:var(--pico-8-cyan);">mysqli_fetch_assoc</span>(<span style="color:var(--pico-8-black);">$result</span>))<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$timestamp</span> = <span style="color:var(--pico-8-cyan);">strtotime</span>(<span style="color:var(--pico-8-black);">$row</span>[<span style="color:var(--pico-8-dark-grey);">'timestamp'</span>]);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$newTimestampFormat</span> = <span style="color:var(--pico-8-cyan);">date</span>(<span style="color:var(--pico-8-dark-grey);">'M jS, Y'</span>,<span style="color:var(--pico-8-black);">$timestamp</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">if</span>(!<span style="color:var(--pico-8-cyan);">is_null</span>(<span style="color:var(--pico-8-black);">$row</span>[<span style="color:var(--pico-8-dark-grey);">'link'</span>]))<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$link</span> = <span style="color:var(--pico-8-black);">$row</span>[<span style="color:var(--pico-8-dark-grey);">'link'</span>];<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">else</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$link</span> = <span style="color:var(--pico-8-dark-grey);">"https://www.rismosch.com/article?id={<span style="color:var(--pico-8-black);">$row['id']}</span>"</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-black);">$thumbnail</span> = <span style="color:var(--pico-8-black);">GetThumbnailPath</span>(<span style="color:var(--pico-8-black);">$row</span>);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">echo</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-dark-grey);">"&lt;tr&gt;&lt;td&gt;&lt;a title=\"<span style="color:var(--pico-8-black);">{$row['title']}</span>\" href=\"<span style="color:var(--pico-8-black);">{$link}</span>\" class=\"articles_entry_link\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class=\"articles_mobile\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;table class=\"articles_entry\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class=\"articles_thumbnail_wrapper_outside\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class=\"articles_thumbnail_wrapper_inside\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"<span style="color:var(--pico-8-purple);">; <span style="color:var(--pico-8-black);">late_image</span>(<span style="color:var(--pico-8-black);">$thumbnail</span>, <span style="color:var(--pico-8-dark-grey);">"articles_thumbnail"</span>, <span style="color:var(--pico-8-dark-grey);">""</span>); <span style="color:var(--pico-8-cyan);">echo</span> </span>"<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class=\"articles_thumbnail_information\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;h3&gt;<span style="color:var(--pico-8-black);">{$row['title']}</span>&lt;/h3&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;p&gt;<span style="color:var(--pico-8-black);">{$row['category']}</span> &#38;&#35;183; <span style="color:var(--pico-8-black);">{$newTimestampFormat}</span>&lt;/p&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/table&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class=\"articles_desktop\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;table class=\"articles_entry\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td class=\"articles_thumbnail_row_desktop\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class=\"articles_thumbnail_wrapper\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"<span style="color:var(--pico-8-purple);">; <span style="color:var(--pico-8-black);">late_image</span>(<span style="color:var(--pico-8-black);">$thumbnail</span>, <span style="color:var(--pico-8-dark-grey);">"articles_thumbnail"</span>, <span style="color:var(--pico-8-dark-grey);">""</span>); <span style="color:var(--pico-8-cyan);">echo</span> </span>"<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class=\"articles_thumbnail_information\"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;h3&gt;<span style="color:var(--pico-8-black);">{$row['title']}</span>&lt;/h3&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;br&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;p&gt;<span style="color:var(--pico-8-black);">{$row['category']}</span> &#38;&#35;183; <span style="color:var(--pico-8-black);">{$newTimestampFormat}</span>&lt;/p&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/table&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/a&gt;&lt;/td&gt;&lt;/tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr class=\"row_empty\"&gt;&lt;td&gt;&lt;/td&gt;&lt;/tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr class=\"row_empty row_devider\"&gt;&lt;td&gt;&lt;/td&gt;&lt;/tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">echo</span> <span style="color:var(--pico-8-dark-grey);">"<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/table&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">else</span><br>
&nbsp;&nbsp;&nbsp;&nbsp;{<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">echo</span> <span style="color:var(--pico-8-dark-grey);">"&lt;p&gt;no articles found &#38;&#35;175;&#38;&#35;92;&#38;&#35;95;&#38;&#35;40;&#38;&#35;12484;&#38;&#35;41;&#38;&#35;95;&#38;&#35;47;&#38;&#35;175;&lt;/p&gt;"</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
}
</p>

<h2>article.php</h2>

<p>Now we got an article selection, cool. But how do I actually display an article? Well, glad you ask. Just like with the other PHP files, the container around the article is always the same, just the content changes. Each article has a title, a date, links to the next and previous posts, and a comment section. How to change these things and what content to display is handled by one PHP file, the aforementioned database and a file system.</p>

<p>When you access <span class="code">article.php</span>, it gets the URL parameter “id”. The “id” of this very post is “php-databases-and-how-my-blog-works”. With SQL it retrieves the correct row of the article table. In the row I get the title of the post, its timestamp and its category. Then with the timestamp, I gather the article with the next highest and next lowest timestamp, thus retrieving the next and previous post.</p>

<p>All of this will simply be displayed around the article. I have a directory “articles”, which contains contents of each blog. It gets the directory, which has the same name as the id of the post and checks if it contains a file called <span class="code">page_0.php</span>. If it contains this file, then it will simply be included, thus its content being displayed on the page. If <span class="code">article.css</span> exists, it will be included. This allows me to have individual CSS for every article. Finally, <span class="code">article.php</span> contains a method, which allows <span class="code">page_0.php</span> to easily access assets, like pictures and other files.</p>

<p>Putting everything together, it looks like this:</p>

<?php late_image(get_source("picture_5.webp"),"","max-width:100%; margin:auto; display: block;"); ?>

<p>This is the articles-directory, which contains all articles.</p>

<?php late_image(get_source("picture_6.webp"),"","max-width:100%; margin:auto; display: block;"); ?>

<p>These are the files for this very post.</p>
<p>The method to access an asset looks like this:</p>

<p class="code code_block" style="color: var(--pico-8-purple);">
<span style="color:var(--pico-8-cyan);">function</span> <span style="color:var(--pico-8-black);">get_source</span>(<span style="color:var(--pico-8-black);">$file</span>)<br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">global</span> <span style="color:var(--pico-8-black);">$article_id</span>;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--pico-8-cyan);">return</span> <span style="color:var(--pico-8-dark-grey);">"https://www.rismosch.com/articles/<span style="color:var(--pico-8-black);">{$article_id}</span>/<span style="color:var(--pico-8-black);">{$file}</span>"</span>;<br>
}
</p>

<p>And it is easily called like this:</p>

<p class="code code_block"><span style="color: var(--pico-8-red);">&lt;?php <span style="color: var(--pico-8-purple);"><span style="color:var(--pico-8-black);">late_image</span>(<span style="color:var(--pico-8-black);">get_source</span>(<span style="color:var(--pico-8-dark-grey);">"picture_1.webp"</span>),<span style="color:var(--pico-8-dark-grey);">""</span>,<span style="color:var(--pico-8-dark-grey);">"max-width:100%; margin:auto; display:block;"</span>);</span> ?&gt;</span></p>

<p>More on <span class="code">late_image()</span> in a later chapter.</p>

<p>And that is basically everything regarding my blog. From it's PHP, how each article is stored in the database, how it is build up with multiple files, and how an article is actually displayed.</p>