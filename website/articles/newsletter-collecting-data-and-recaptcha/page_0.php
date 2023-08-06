<p>Ok, let’s discuss my least favorite part of this entire blogpost: Collecting Data &#128532; There is no nice way of saying this, but yes, I am collecting your data. While I don’t use cookies, if you use my <a href="https://www.rismosch.com/contact" target="_blank" rel="noopener noreferrer">contact form</a>, an email will be send to me, containing your name, email and the subject+body of the message itself. If you <a href="https://www.rismosch.com/newsletter" target="_blank" rel="noopener noreferrer">subscribe to my newsletter</a>, your email will be saved in my database. While I can only dream of having a filled email list, and my dad being the only one who is subscribed at this very moment, I like to have the option. Also I use third party services, which may or may not collect data.</p>

<p>Because of all that ugly data collecting, I need a <a href="https://www.rismosch.com/privacy" target="_blank" rel="noopener noreferrer">privacy policy</a>, which I got from a freelancer on fiverr. Also it might be a good idea to say this:</p>

<p style="background-color:var(--pico-8-white); border: 5px solid var(--pico-8-cyan); padding: 20px;"><b>I AM NOT A LAWYER. THIS IS NOT LEGAL ADVICE. THIS CHAPTER IS MEANT AS AN EXAMPLE. FOR LEGAL QUESTIONS, CONTACT A PROFESSIONAL.</b></p>

<p>While I don’t like the fact that I am collecting data, there is no way around it. When you contact me, your email <i>must</i> be stored in my email client. And if I really want to have a newsletter, I <i>must</i> store emails somewhere. I need to accept, that I will be collecting data. As for third party services, it’s all free game. I have absolutely no idea how and what they collect. So if you are serious about privacy, it’s probably a good idea to read the privacy policies of the third party services that you are going to use.</p>

<p>Now that the ugly stuff is out of the way, let’s talk about the fun stuff again: How my contact and newsletter form actually work. My contact form is mainly inspired from this blog post:<br><a class="auto-break" href="https://mailtrap.io/blog/php-email-contact-form/" target="_blank" rel="noopener noreferrer">https://mailtrap.io/blog/php-email-contact-form/</a></p>

<p>If you read it, (which I expect you didn’t) I want to mention that I modified it a bit. Mainly I use PHPMailer to send an email directly to me, instead of google docs:<br><a class="auto-break" href="https://github.com/PHPMailer/PHPMailer" target="_blank" rel="noopener noreferrer">https://github.com/PHPMailer/PHPMailer</a></p>

<p>Also I ditched the client side data validation. At last but not least, because I deviated quite a bit from the error management on that blogpost, the source code for my form is quite a mess. Because my source code is so unwieldy, it’s difficult to post code here, without making this blogpost unreadable. So I excluded pretty much every code in this chapter. But if you are brave enough, you can look into my GitHub and try to read along:<br><a class="auto-break" href="https://github.com/Rismosch/risWebsite/blob/main/website/contact.php" target="_blank" rel="noopener noreferrer">https://github.com/Rismosch/risWebsite/blob/main/website/contact.php</a></p>

<p>For those who didn’t read the blogpost above, (which I expect none of you did) let me walk you through the whole form. When fully implemented, the contact form does these things in that order:</p>

<ol>
<li>Once the user filled out the form and pressed the send button, a reCAPTCHA request is send to Googles servers.</li>
<li>If the reCAPTCHA is successful, the inserted data will be posted to my webserver.</li>
<li>The data is validated.</li>
<li>The webserver also checks if the reCAPTCHA is actually successful.</li>
<li>If the data is valid and reCAPTCHA was successful, an email will be generated and be send to me.</li>
<li>A success HTML will be created, which is displayed in the users browser.</li>
</ol>

<p>Ha! reCAPTCHA is used in almost every step here, isn’t it? So what is reCAPTCHA and why do I use it? Simply put, reCAPTCHA is a technology to check, if your user is an actual human and not a bot. This is quite important, because this contact form directly sends an email to me. Someone could easily program a bot to fill out this form and then spam me with it. reCAPTCHA prevents this.</p>

<p>How it does detect if you are a bot or not, is kinda a mystery. If we would know how reCAPTCHA works, malicious users could easily break it and circumvent it. But on the most abstract level, reCAPTCHA gives you a score, of how human you are. If it suspects that you are a bot, it will give you that infamous picture puzzle.</p>

<?php late_image(get_source("picture_1.webp"),"","max-width:100%; margin:auto; display: block;"); ?>

<p>After reCAPTCHA thinks that you are a human, or you solved the picture puzzle, reCAPTCHA fires a callback, which will be used to actually submit our data. Once the data is posted to our server, we can do whatever we want with it. But beware: User inputs are very dangerous. At any point of your website where you handle user data, a user can insert stuff which can break your entire system, either by deleting everything, stealing your saved data or more. So it is a must to validate data and sanitize it.</p>

<p>For example, in the case of reCAPTCHA you might think: When the HTML only posts the data when reCAPTCHA is successful, can’t I just circumvent reCAPTCHA by writing my own malicious HTML and use that to post to my server? Yes, you can do that. So it is an absolute must to validate data and sanitize it.</p>

<p>The very first thing my PHP checks is, if <span class="code">$_POST</span> is empty. <span class="code">$_POST</span> is simply the header data of the form, that the user has send to my webserver. If it is empty, the user hasn’t inserted anything and validating it is useless. If it isn’t empty, we can access different fields with <span class="code">$_POST[field name]</span>, check if they are filled out and sanitize them. At last but not least, we check if reCAPTCHA is successful. If the data was submitted via reCAPTCHA and not a malicious HTML, reCAPTCHA attaches it’s response to our form data. With this response we connect to the server of reCAPTCHA and ask, <i>“hey, is this response the user send me actually legit?”</i>. If it was, then we can proceed and generate an email, which is finally send to me. If at the data validation any errors occur, the process is terminated and displayed in the HTML. If it was successful, then just a success message will be displayed. The diagram below further visualizes the process:</p>

<?php late_image(get_source("picture_2.webp"),"","max-width:100%; margin:auto; display: block;"); ?>

<p>Okay, this is quite a hazzle, but this is nothing compared to what comes next &#128579;</p>

<p>The newsletter is implemented on the same basis, but it has one thing that makes this process even more complicated: Emails should be verified. Technically there is no legal reason to verify Emails, but I don’t want my email list to be filled with spam. Also I MUST include a feature, that allows the user to delete their email on demand. The user has the right to request that I delete their data. To accommodate this, my newsletter deviates from my contact form in 2 ways: One, it sends an email containing the confirmation link to the user. And two, I have 2 more php files which confirm or delete emails.</p>

<p>Once the user fills out the newsletter form, their email will be inserted into my database, confirmed or not. An id with random characters will be created for each inserted email. On top of that, a timestamp will be inserted, and a bool to track if the email was confirmed or not. The id is very important, because as I mentioned in the previous paragraph, I have 2 more php files which confirm or delete the email. Somehow, I need to tell the server, which email to confirm/delete. I do this via an URL Parameter.</p>

<p>For example, this URL will confirm the email with the id 123456:<br><a class="auto-break" href="https://www.rismosch.com/newsletter_confirm?id=123456" target="_blank" rel="noopener noreferrer">https://www.rismosch.com/newsletter_confirm?id=123456</a></p>

<p>And this URL will delete the email:<br><a class="auto-break" href="https://www.rismosch.com/newsletter_delete?id=123456" target="_blank" rel="noopener noreferrer">https://www.rismosch.com/newsletter_delete?id=123456</a></p>

<p>If I wouldn’t use an id and just use a plain email, a user could easily check or delete emails they choose. With randomly generated ids, which contain 32 characters each, it is ludicrously difficult for a malicious user to enter/delete emails. And even if they succeed, they have no way of telling which email actually got confirmed/deleted, thus user data is protected.</p>

<p>Of course, the two PHP files which handle email confirmation/deletion, <span class="code">newsletter_confirm.php</span> and <span class="code">newsletter_delete.php</span>, are also protected with reCAPTCHA, preventing bots that try to spam these PHP files and brute force delete all my emails. But this works automatically and doesn’t require the user to push a button. There is a JavaScript function which will be called when each site is loaded. This function will only be called when you first visit it. Then, the PHP does the same as the contact form: validate the data, check if reCAPTCHA was successful and then does whatever it wants.</p>

<p><span class="code">newsletter_confirm.php</span> checks if the id exists. If it exists, it checks if the timestamp is older than a day. If the id doesn’t exist, or the timestamp is older than a day, then an error will be displayed. If it isn’t expired, simply the bool of the email in the database will be switched from false to true.</p>

<p><span class="code">newsletter_delete.php</span> on the other hand simply deletes the email from the database. In my case it just runs <span class="code">DELETE FROM Emails WHERE id='123456'</span> with our id 123456 as an example. Even when the id is not found and thus nothing is deleted, the SQL query will be successful, and thus a success message will be displayed. So chances are, if you thought you deleted an email by visiting the delete-link above, you actually deleted nothing &#128522;</p>

<p>Okay, now we have a database full of emails, with a bool that tracks if the email was confirmed or not. We finally can send out newsletters to each and every email. But there is a huge problem though: Every email needs to be specific to the user we send to. That is because each email MUST contain a link to unsubscribe the newsletter. Again I preface, the user has the right to delete their data if they choose to do so. And it is my philosophy to make that as easy as possible. Honestly, I only know how to do that with Microsoft Word, Excel and Outlook. For the rest of this chapter I will be assuming that you own these programs. Otherwise you need to look for another solution to mass send individual emails.</p>

<p>But before we send emails, maybe we want to look at the emails that we actually got. For this, I wrote an offline email manager, which connects to my database and retrieves all emails. It is a simple PHP and runs on my local machine, using <a href="https://www.apachefriends.org/index.html" target="_blank" rel="noopener noreferrer">xampp</a>. The manager looks like this:</p>

<?php late_image(get_source("picture_3.webp"),"","max-width:100%; margin:auto; display: block;"); ?>

<p>It isn’t pretty, but since I am the only one interacting with it, it will suffice. There are a bunch of things displayed here. It allows me to reload the page, it shows my total emails, how many are confirmed, how many are expired and then all emails which are stored in my database. With a click of a button, I can delete all expired emails. The export button exports all confirmed emails into an Excel spreadsheet.</p>

<p>By the way, all offline tools I wrote for my website can be found here:<br><a class="auto-break" href="https://github.com/Rismosch/risWebsite/tree/main/offline_utility" target="_blank" rel="noopener noreferrer">https://github.com/Rismosch/risWebsite/tree/main/offline_utility</a></p>

<p>Once the emails are exported into an Excel spread sheet, we can start sending mass emails. In Word, I have created a template, which is then filled with data. It looks like this:</p>

<?php late_image(get_source("picture_4.webp"),"","max-width:100%; margin:auto; display: block;"); ?>

<p>Obviously, the "INSERT CONTENT HERE" part will be replaced by whatever content I am deciding to send. The important thing in this template, is the little <span class="code">&lt;&lt;id&gt;&gt;</span> at the end of the email-deletion link. This string will be replaced by whatever data is standing in the id-column of the Excel table. So if the mass email will be generated, each user will have an unsubscribe link at the bottom which contains their specific id.</p>

<p>Then we can use the mass email feature. There is a handy wizard, which generates one email for each entry in the Excel table. The generated emails are then send to Outlook, which in turn sends them to each email in the Excel table. Huzza, individual emails are being send!</p>

<p>And that's basically it. If you are more interested in this, you probably want to look at the official documentation on how to send Mass Emails with Microsoft Office Products:<br><a class="auto-break" href="https://support.microsoft.com/en-us/office/use-mail-merge-to-send-bulk-email-messages-0f123521-20ce-4aa8-8b62-ac211dedefa4" target="_blank" rel="noopener noreferrer">https://support.microsoft.com/en-us/office/use-mail-merge-to-send-bulk-email-messages-0f123521-20ce-4aa8-8b62-ac211dedefa4</a></p>