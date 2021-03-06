<?php

/**
 * GForge Search Engine
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2004 (c) Guillaume Smet / Open Wide
 *
 * http://gforge.org
 *
 * @version $Id$
 */

require_once $gfwww.'search/include/renderers/RssSearchRenderer.class.php';
require_once $gfcommon.'search/ExportProjectSearchQuery.class.php';

/**
 * callback function used during the RSS export
 *
 * @param array $dataRow array containing data for the current row
 * @return string additionnal information added in the RSS document
 */
function rssProjectCallback($dataRow) {
	// $default_trove_cat defined in local.inc
	$result = db_query('SELECT trove_cat.fullpath '
		.'FROM trove_group_link, trove_cat '
		.'WHERE trove_group_link.trove_cat_root='.$GLOBALS['default_trove_cat'].' '
		.'AND trove_group_link.trove_cat_id=trove_cat.trove_cat_id '
		.'AND group_id=\''.$dataRow['group_id'].'\'');
	
	$return = '';
	$return .= ' | date registered: '.date('M jS Y', $dataRow['register_time']);
	$return .= ' | category: '.str_replace(' ', '', implode(',', util_result_column_to_array($result)));
	$return .= ' | license: '.$dataRow['license'];
	
	return $return;
}

class ProjectRssSearchRenderer extends RssSearchRenderer {

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function ProjectRssSearchRenderer($words, $offset, $isExact) {
		
		$this->callbackFunction = 'rssProjectCallback';
		
		$searchQuery = new ExportProjectSearchQuery($words, $offset, $isExact);
		
		$this->RssSearchRenderer(SEARCH__TYPE_IS_SOFTWARE, $words, $isExact, $searchQuery);
	}
}

?>
