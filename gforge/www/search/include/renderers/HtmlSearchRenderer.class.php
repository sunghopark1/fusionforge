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

require_once $gfwww.'search/include/renderers/SearchRenderer.class.php';

class HtmlSearchRenderer extends SearchRenderer {

	/**
	 * Headers of the HTML results table
	 *
	 * @var array $tableHeaders
	 */
	var $tableHeaders = array();

	/**
	 * Constructor
	 *
	 * @param string $typeOfSearch type of the search (Software, Forum, People and so on)
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param object $searchQuery SearchQuery instance
	 */
	function HtmlSearchRenderer($typeOfSearch, $words, $isExact, $searchQuery) {
		$this->SearchRenderer($typeOfSearch, $words, $isExact, $searchQuery);
	}

	/**
	 * flush - flush the html output
	 */
	function flush() {
		$searchQuery =& $this->searchQuery;
		if($searchQuery->isError()) {
			$this->writeHeader();
			echo '<h2>'.$searchQuery->getErrorMessage().'</h2>';
			$this->writeFooter();
		} else {
			$searchQuery->executeQuery();
			if($searchQuery->getResult() && ($searchQuery->getRowsTotalCount() == 1 && $searchQuery->getOffset() == 0) && $this->implementsRedirectToResult()) {
				$this->redirectToResult();
			} else {
				$this->writeHeader();
				$this->writeBody();
				$this->writeFooter();
			}
		}
	}

	/**
	 * writeHeader - write the header of the output
	 */
	function writeHeader() {
		echo '<div align="center">';
		echo $GLOBALS['HTML']->searchBox();
		echo '</div>';
	}

	/**
	 * writeBody - write the body
	 */
	function writeBody() {
		echo $this->writeResults();
	}

	/**
	 * writeFooter - write the footer
	 */
	function writeFooter() {
		$GLOBALS['HTML']->footer(array());
	}
	
	/**
	 * getResults - get the html output which will display the search results
	 *
	 * @return string html output
	 */
	function writeResults() {
		$searchQuery =& $this->searchQuery;
		$query =& $this->query;
		
		$html = '';
		
		if(!$searchQuery->getResult() || $searchQuery->getRowsCount() < 1) {
			$html .= '<h2>'.sprintf(_('No matches found for <em>%1$s</em>'), htmlspecialchars($query['words'])).'</h2>';
			$html .= db_error();
		} else {
			$html .= '<h3>'.sprintf(_('Search results for <em>%1$s</em>'), htmlspecialchars($query['words'])).'</h3>';
		
			$html .= $GLOBALS['HTML']->listTableTop($this->tableHeaders);
			$html .= $this->getRows();
			$html .= $GLOBALS['HTML']->listTableBottom();
		}
		
		if($searchQuery->getRowsCount() > 0 && ($searchQuery->getRowsTotalCount() > $searchQuery->getRowsCount() || $searchQuery->getOffset() != 0 )) {
			$html .= $this->getNavigationPanel();
		}
		
		return $html;
	}

	/**
	 * getNavigationPanel - get the html output for the navigation panel
	 *
	 * @return string html output
	 */ 
	function getNavigationPanel() {
		$searchQuery =& $this->searchQuery;
		
		$html = '';
		$html .= '<br />';
		$html .= '<table class="tablecontent" width="100%" cellpadding="5" cellspacing="0">';
		$html .= '<tr>';
		$html .= '<td>';
		if ($searchQuery->getOffset() != 0) {
			$html .= '<a href="'.$this->getPreviousResultsUrl().'" class="prev">'
				. html_image('t2.png', '15', '15', array('border'=>'0','align'=>'middle'))
				. ' '._('Previous Results').'</a>';
		} else {
			$html .= '&nbsp;';
		}
		$html .= '</td><td align="right">';
		if ($searchQuery->getRowsTotalCount() > $searchQuery->getRowsCount()) {
			$html .= '<a href="'.$this->getNextResultsUrl().'" class="next">'
				._('Next Results').' '
				. html_image('t.png', '15', '15', array('border'=>'0','align'=>'middle')) . '</a>';
		} else {
			$html .= '&nbsp;';
		}
		$html .= '</td></tr>';
		$html .= '</table>';
		return $html;
	}
	
	/**
	 * getPreviousResultsUrl - get the url to go to see the previous results
	 *
	 * @return string url to previous results page
	 */
	function getPreviousResultsUrl() {
		$offset = $this->searchQuery->getOffset() - $this->searchQuery->getRowsPerPage();
		$query =& $this->query;
		
		$url = '/search/?type='.$query['typeOfSearch'].'&amp;exact='.$query['isExact'].'&amp;q='.urlencode($query['words']);
		if($offset > 0) {
			$url .= '&amp;offset='.$offset;
		}
		return $url;
	}
	
	/**
	 * getNextResultsUrl - get the url to go to see the next results
	 *
	 * @return string url to next results page
	 */
	function getNextResultsUrl() {
		$query =& $this->query;
		return '/search/?type='.$query['typeOfSearch'].'&amp;exact='.$query['isExact'].'&amp;q='.urlencode($query['words']).'&amp;offset='.($this->searchQuery->getOffset() + $this->searchQuery->getRowsPerPage());
	}

	/**
	 * highlightTargetWords - highlight the words we are looking for
	 *
	 * @param string $text text
	 * @return string text with keywords highlighted
	 */
	function highlightTargetWords($text) {
		if (empty($text)) {
			return '&nbsp;';
		}
		$regexp = implode($this->searchQuery->getWords(), '|');
		return preg_replace('/('.str_replace('/', '\/', $regexp).')/i','<span class="selected">\1</span>', $text);
	}

	/**
	 * implementsRedirectToResult - check if the current object implements the redirect to result feature by having a redirectToResult method
	 *
	 * @return boolean true if our object implements search by id, false otherwise.
	 */
	function implementsRedirectToResult() {
		return method_exists($this, 'redirectToResult');
	}

	/**
	 * getResultId - get the field value for the first row of a result handle
	 *
	 * @param string $fieldName field name
	 * @return string value of the field
	 */
	function getResultId($fieldName) {
		return db_result($this->searchQuery->getResult(), 0, $fieldName);
	}

}

?>
