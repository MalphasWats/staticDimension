#staticDimension

[subDimension Projects Page](http://subdimension.co.uk/2011/04/17/projects.html)

staticDimension is a static HTML blogging and publishing platform, written in PHP.

##Installation

Unpack the files in the tar file into a public folder on your web server.

Make sure that your PHP user has write access to the folder

You will need to edit the files `_users.php` and `_settings.php` in the `controlPanel/` folder, you'll find detailed instructions in the files themselves.

Visit http://yourwebsite/install.php to complete installation.

By default, the user *Admin*, password of *password* (all case sensitive) is created. I strongly recommend that you change this as soon as possible!

Done! Start publishing!

##Upgrading an existing instance
If you're upgrading to a newer version of staticDimension, simply extract the archive to a folder somewhere on your server and copy the contents of the `controlPanel` folder into the `controlPanel` folder of your installation, articles, pages and uploaded files will remain untouched. 

You don't need to run `install.php` after the first time - if you do, your settings and users will be overwritten with default values.

##Features

staticDimension stores articles and pages as text files and uses these files to generate HTML files from templates you provide.

You can use [markdown](http://daringfireball.net/projects/markdown/) in your writing.

Templates are *very* simple, staticDimension will simply replace `$PAGE_CONTENT` with your HTML text and `$PAGE_TITLE` with the title of the page. Any navigation you need must be built into the templates or page content. staticDimension has a very basic default template - there are 4 template pages: `_home.html`, `_article.html`, `_page.html`, `_archive.html`. The only one that is mandatory is `_home.html`. You can also create individual templates for specific pages simply by making a template file with the same name as the page.

[More about templates](http://subdimension.co.uk/2011/03/31/staticDimension_templates.html)

There are no comments - if you want to use comments, you could use [facebook comments](https://developers.facebook.com/docs/reference/plugins/comments/) or [disqus](http://disqus.com/) and simply add their code to your template.

You can use external publishing applications such as [MarsEdit](http://www.red-sweater.com/marsedit/) via the MetaWeblog API. The API endpoint is at:

    http://yourwebsite/controlPanel/xml-rpc.php

This feature has been tested with:

* [MarsEdit](http://www.red-sweater.com/marsedit/)
* [MacJournal](http://www.marinersoftware.com/products/macjournal/)

##Archives

There are two choices for creating the archives for your posts. The default format organizes posts by year, month and date. 

A new option enables a single-page archive, which lists all posts by title and byline (author, date and time) in reverse order of posting.

You can set the archive format in `_settings.php` via the singlePageArchive option. A value of 'true' enables single-page archives. Any other value currently uses the default archive format.

##Licensing

I haven't added any formal licensing restrictions to version 1.0, this may change in the future, but to be honest, I'm not convinced they mean a great deal - if people want to steal my code, they will! 

I'm happy for anyone to use my code for their own personal use.

A commercial license for staticDimension is £180.

I'm resident in the UK, so that's pounds sterling - [contact me](http://subdimension.co.uk/2011/04/05/about_me.html) and we'll work something out.

##Questions

Please don't hesitate to [contact me](http://subdimension.co.uk/2011/04/05/about_me.html) if you have any further questions.