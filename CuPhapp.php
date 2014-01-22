<?php
require_once 'Phapp.php';

/**
 * Application template 
 *
 * extended with clean/friendly URL parsing.
 */
class CuPhapp extends Phapp
{
	/**
	 * Clean url array 
	 * 
	 * Associative Keys:
	 *		'docroot' 	- String file on server
	 *		'urlroot' 	- String file on web (only path)
	 *		'protocol' 	- http or https
	 *		'basepath' 	- String protocol + domain + urlroot
	 *						useful for linking assets.
	 *		'params' 	- Array of cleaned url parameters
	 *
	 * Possible Usage Scenarios for 'params':
	 *
	 * 1) Basic view usage www.myurl.com/Foo/
	 *		cu['params'][0] - view name ('Foo')
	 *
	 * 2) I18n sample www.myurl.com/en/Foo/
	 *		cu['params'][0] - language code ('en')
	 *		cu['params'][1] - view name ('Foo')
	 *
	 * 3) View sample with additional parameter www.myurl.com/Foo/Bar
	 *		cu['params'][0] - view name ('Foo')
	 *		cu['params'][1] - param used by view ('Bar')
	 *
	 * 'params' is empty if only the base path was send.
	 */
	private $cu = null;
	
	/**
	 * Parses the request and returns the $cu array (see above).
	 *
	 * @return Array 
	 */
	public function cleanUrl() 
	{
		if( $this->cu != null )
			return $this->cu;

		// init cu array
		$this->cu = array();
		
		$uri = $_SERVER['REQUEST_URI'];
		
		if( $offset = strpos($uri, '?') )
		{
			// strip get requests
			$uri = substr($uri, 0, $offset);
		} else if( $offset = strpos($uri, '#') ) {
			// strip hashes
			$uri = substr($uri, 0, $offset);
		}
		
		$chopcount = -strlen(basename($_SERVER['SCRIPT_NAME']));
		$this->cu['docroot'] = substr($_SERVER['SCRIPT_FILENAME'], 0, $chopcount);
		$this->cu['urlroot'] = substr($_SERVER['SCRIPT_NAME'], 0, $chopcount);
		// off is used by IIS
		$this->cu['protocol'] = 
				(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http') . '://';
		$this->cu['basepath'] = 
				$this->cu['protocol'] . $_SERVER['SERVER_NAME'] . $this->cu['urlroot'];

		// strip the url root from REQUEST_URI
		if( $this->cu['urlroot'] != '/' ) 
			$uri = substr($uri, strlen($this->cu['urlroot']));

		// strip excess slashes
		$uri = trim($uri,'/');

		// if $url is empty of default value, set action to empty array
		if( ($uri != '') 
				&& ($uri != 'index.php') 
				&& ($uri != 'index.html')
			)
		{
			$this->cu['params'] = explode('/', html_entity_decode($uri));
		}
		
		return $this->cu;
	}
}

/**
 * View template
 *
 * extended with comfort functions for using CuPhapp
 */
class CuPhappView extends PhappView
{
	/**
	 * Returns the protocol string
	 *
	 * @return String http:// or https://
	 */
	public function protocol()
	{
		return $this->app->cleanUrl()['protocol'];
	}
	
	/**
	 * Returns the base path for easy absolute linkage.
	 * 
	 * @return String http(s) base path 
	 */
	public function basePath()
	{
		return $this->app->cleanUrl()['basepath'];
	}
	
	/**
	 * Returns the parameters for the view.
	 * Strip off every entry before and inclusive the view.
	 * Since in your view implementation you want quickly access the
	 * parameters the view needs.
	 * 
	 * @return Array of parameters
	 */
	public function params()
	{
		$arr = $this->app->cleanUrl()['params'];
		$c = get_class($this);
		
		while( in_array($c,$arr) )
			array_shift($arr);
		
		return $arr;
	}
}