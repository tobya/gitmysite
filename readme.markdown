Git My Site 
===========


A simple script to maintain a git repository of your custom site via a php script.

This is different to having a fully blown git instance on your server.  It is essentially a readonly repo from outside the server.  The php script 'gitmysite.php' has a number of commands it can carry out primarily based around adding and committing (no branching, merging etc, thats too complex for this).  You can clone the repo to your own machine via http access, but you cant publish to the online repo.  

The purpose of the script and the git repository is purely to give you a record of what you have changed.  This is very useful if like me you are in a situation where you need to edit live / production files without using an intermediate source code repository.

Security
--------

Please Note:  If someone gets access to the password you have set they can very easily clone a copy of your entire website via http://www.mysite.com/.git, therefore it is important you understand the risks before installing this script.

When you first visit the script at http://www.yoursite.com/gitmysite.php you will be asked for a password.  This password should be set by you on the firstline of the script.  This is very basic security.  Once you have created the .git repository, you have the option of setting a password for the .git folder via a .htaccess file **This is Strongly Recommended** and will probably be forced soon.

Obviously you should pick a decent password since anyone who can guess your password can clone a copy of you site by doing the following.

     git clone http://username:password@www.yoursite.com/.git/ CloneIntoDirectory

The Script assumes you are the only one with access to the .git directory from the ftp or server side, so once a .htaccess file with a password has been created, it cannot be updated by the script.  You must delete the files via ftp first.  This prevents someone who gets access to the gitmysite.php script from changing the password to your repo. 

*Pick a good password.*


Installation
------------

The only file you need to upload to your site is the ['gitmysite.php'] (https://raw.github.com/tobya/gitmysite/master/gitmysite.php) file, the '.gitignore' file will be created for you.  I wanted to do this to make it as simple as possible to install, as a result the file might be a bit confusing to read, but I feel it is worth the tradeoff.

To install place the gitmysite.php script in the root directory you wish to control, change the default 'password' assigned to the $PASS variable and visit in your browser.

When  you first login you will need to create a git repository, then set security on the .git repository either via the script or manually.  

If you wish you can create and edit the .gitignore file. Next you can do an initial commit on the Add tab.

After setup, each time you do a change simply come and click the Add Changed files button and then Give a commit message and commit.

Hosting Provider
----

You will need your hosting provider to make git available on your server.  Some (such as Dreamhost) do and it works great and some don't (such as Blacknight).


Get Involved
------------

I would be delighted if anyone wanted to help out with this project.  It works nicely for my sites, but there may be things that need to work for your sites.  Some ideas I have below.

*  Create config file that sets username etc for .git
*  Investigate if script can use a differnt name for .git directory - for security reasons
*  Use Wizard like interface for initial git repo setup 
*  JQuery is being loaded anyway so it would be nice to use it a bit more, maybe ajax calls with dialogs showing results.
*  Allow users to change theme based on jQuery Theme Roller

 
Send me a message or just fork to get involved.

