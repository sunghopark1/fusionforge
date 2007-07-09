<?php
/**
 * External search plugin
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id$
 */

require_once('www/search/include/engines/GroupSearchEngine.class.php');

class ExternalSearchEngine extends SearchEngine {
	
	/**
	* name of the external site
	*
	* @var string $name
	*/
	var $name;
	
	/**
	* url of the external site
	*
	* @var string $url
	*/
	var $url;
	
	function ExternalSearchEngine($type, $name, $url) {
		$this->name = $name;
		$this->url = $url;
		
		$this->SearchEngine($type, 'ExternalHtmlSearchRenderer', $name);
	}
	
	function isAvailable($parameters) {
		return true;
	}
	
	function & getSearchRenderer($words, $offset, $exact, $parameters) {
		require_once('ExternalHtmlSearchRenderer.class.php');
		$renderer = new ExternalHtmlSearchRenderer($type, $this->name, $this->url, $words);
		return $renderer;
	}
}

?>