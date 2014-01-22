Phapp
=====

A simple but scalable object-oriented web application framework.

Hello World
-----------

A simple example:

	<?php

	require_once 'Phapp.php';

	class Hello extends PhappView
	{
		public function response()
		{
			return 'Hello World.';
		}
	}

	$app = new Phapp();

	echo $app->process( 'Hello' );

Find this and more complex examples in the
[samples branch](https://github.com/markusfisch/Phapp/tree/samples).

How it works
------------

The idea is to define a set of view objects that get processed by the main
application object.

Every view object inherits from PhappView which has two main methods you
should overwrite. The first is request():

	public function request()
	{
		if( !$_REQUEST['order_number'] )
			return 'Home';

		return null;
	}

The purpose of this method is to handle the request and, if required, return
a name of another view that should take over.

The first view that returns _null_ will get its response() method called to
respond to the request:

	public function response()
	{
		return '<h1>Hello</h1>';
	}

Here's a little graph that shows how everything works together:

                +----------------------------+
                | Name of a PhappView object | <-------------------------+
                +----------------------------+                           |
                    |                                                    |
                    |                                                    |
    +-- Phapp ------|-------+                                            |
    |               |       |                                            |
    |               V       |                                            |
    | +------------------------+           +-- PhappView ---------+      |
    | |    Construct object    |  ------>  |                      |      |
    | +------------------------+           |    Object derived    |      |
    |               |       |              |    from PhappView    |      |
    |               V       |              |                      |      |
    | +------------------------+           +-- request() ---------+      |
    | |    Call request() on   |           |                      |      |
    | |       that object      |  ------>  |     Determine if     |      |
    | +------------------------+           |      this object     |      |
    |                       |              |      can handle      |      |
    | +------------------------+    yes?   |     this request     |  no? |
    | |   request() returned   |  <------  |                      | -----+
    | |          null          |           +-- response() --------+
    | |   so call response()   |  ------>  |                      |
    | |    to get the output   |  <------  |  Generate output for |
    | +------------------------+    html   |     this request     |
    |               |       |              |                      |
    +---------------|-------+              +----------------------+
                    |
                    V
               +----------+
               | Web page |
               +----------+

Phapp is very minimal and very small.
Just have a look at Phapp.php to fully understand its concept.

Subclass to extend
------------------

Subclass Phapp and PhappView to add methods for data base access,
internationalization and stuff like that.

It's a good idea to set up a base view which contains common methods
and derive your views from that.

PDO support
-----------

If you're using [PDO](http://php.net/manual/en/book.pdo.php)'s,
you may inherit your views from PhappPDOView and call query() like this:

	if( ($result = $this->query(
		"SELECT
			first,
			last
			FROM members
			WHERE last_login = ?
			ORDER BY last",
		$lastLogin )) )
	{
		while( ($row = $result->fetch()) )
		{
			$contents .= "<li>{$row['first']} {$row['last']}</li>";
		}
	}

CuPhapp extension
---------------
Cu stands for [Clean URL](http://en.wikipedia.org/wiki/Clean_URL). 
So CuPhapp extends Phapp with the possibility to use clean urls instead of query strings.

### Server setup
To use CuPhapp you will need to modifiy your .htaccess file first:

	RewriteEngine On

	# excluded the most commonly used file extensions 
	# everything else is forwarded to Phapp entry index.php
	RewriteRule !\.(gif|jpg|png|css|js|html|ico|zip|rar|pdf|xml|mp4|mpg|flv|swf|mkv|ogg|avi|woff|svg|eot|ttf|jar)$ index.php

	# strip multi slashes
	RewriteCond %{REQUEST_URI} ^(.*)//(.*)$
	RewriteRule . %1/%2 [R=301,L]

### Sample 
File 1: index.php
	
	<?php
	require_once 'phapp/CuPhapp.php';
	require_once 'SampleCuApp.php';
	// also include your views here

	$app = new SampleCuApp();
	echo $app->work();

File 2: SampleCuApp.php
	
	<?php
	class SampleCuApp extends CuPhapp
	{
		public function work()
		{
			$cu = $this->cleanUrl();	
			// total blank url redirect to default view.
			if( count($cu['params']) == 0 ) 
				return $this->process('DefaultView');
			
			// redirect to an existing view
			if( class_exists($cu["params"][0]) )
				return $this->process($cu["params"][0]);	
			
			// show error or if you want to your default view
			return $this->process('ErrorView');
		}
	}

Snippet for parameter evaluation in your derived CuPhappView class:

	//Overrides PhappView request()
	public function request()
	{
		$p = $this->params();

		// check parameters are mandatory
		if( count($p) == 0 ) 
			return "ErrorView";

		// parameter redirects to another view
		if( $p[0] == "foo" )
			return "FooView";

		// everything is ok stay here
		return null;
	}

Stay up to date
---------------

It's probably best to add Phapp as submodule or
[subtree](https://blogs.atlassian.com/2013/05/alternatives-to-git-submodule-git-subtree/)
to your project's repository to be able to update fast and easily.
