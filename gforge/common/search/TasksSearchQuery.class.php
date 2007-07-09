<?php
/**
 * GForge Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 *
 * http://gforge.org
 *
 * @version $Id$
 */

require_once('common/search/SearchQuery.class.php');

class TasksSearchQuery extends SearchQuery {
	
	/**
	* group id
	*
	* @var int $groupId
	*/
	var $groupId;
	
	/**
	* flag if non public items are returned
	*
	* @var boolean $showNonPublic
	*/	
	var $showNonPublic;
	
	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param array $sections sections to search in
	 * @param boolean $showNonPublic flag if private sections are searched too
	 */
	function TasksSearchQuery($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS, $showNonPublic=false) {	
		$this->groupId = $groupId;
		$this->showNonPublic = $showNonPublic;
		
		$this->SearchQuery($words, $offset, $isExact);
				
		$this->setSections($sections);
	}

	/**
	 * getQuery - get the sql query built to get the search results
	 *
	 * @return string sql query to execute
	 */
	function getQuery() {
		global $sys_use_fti;
		if ($sys_use_fti) {
			if(count($this->words)) {
				$tsquery0 = "headline(project_task.summary, q) AS summary,";
				$words = $this->getFormattedWords();
				$tsquery = ", to_tsquery('$words') AS q, project_task_idx";
				$tsmatch = "vectors @@ q";
				$rankCol = "";
				$tsjoin = ' AND project_task.project_task_id = project_task_idx.project_task_id';
				$orderBy = "ORDER BY project_group_list.project_name, rank(vectors, q) DESC, project_task.project_task_id";
				$phraseOp = $this->getOperator();
			} else {
				$tsquery0 = "summary, ";
				$tsquery = "";
				$tsmatch = "";
				$tsjoin = "";
				$rankCol = "";
				$orderBy = "ORDER BY project_group_list.project_name, project_task.project_task_id";
				$phraseOp = "";
			}
			$phraseCond = '';
			if(count($this->phrases)) {
				$phraseCond .= $phraseOp.'('
					. ' ('.$this->getMatchCond('summary', $this->phrases).')'
					. ' OR ('.$this->getMatchCond('details', $this->phrases).'))';
			}
			$sql = 'SELECT project_task.project_task_id,project_task.percent_complete,'
			    .  $tsquery0
				. ' project_task.start_date,project_task.end_date,users.firstname||\' \'||users.lastname AS realname, project_group_list.project_name, project_group_list.group_project_id ' 
				. ' FROM project_task, users, project_group_list '
				. $tsquery
				. ' WHERE project_task.created_by = users.user_id'
				. $tsjoin
				. ' AND project_task.group_project_id = project_group_list.group_project_id '
				. ' AND project_group_list.group_id  ='.$this->groupId.' ';
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$sql .= 'AND project_group_list.group_project_id in ('.$this->sections.') ';
			}
			if (!$this->showNonPublic) {
				$sql .= 'AND project_group_list.is_public = 1 ';
			}
			$sql .= "AND ($tsmatch $phraseCond) $orderBy";
		} else {
			$sql = 'SELECT project_task.project_task_id,project_task.summary,project_task.percent_complete,'
				. ' project_task.start_date,project_task.end_date,users.firstname||\' \'||users.lastname AS realname, project_group_list.project_name, project_group_list.group_project_id ' 
				. ' FROM project_task, users, project_group_list' 
				. ' WHERE project_task.created_by = users.user_id'
				. ' AND project_task.group_project_id = project_group_list.group_project_id '
				. ' AND project_group_list.group_id  ='.$this->groupId.' ';
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$sql .= 'AND project_group_list.group_project_id in ('.$this->sections.') ';
			}
			if (!$this->showNonPublic) {
				$sql .= 'AND project_group_list.is_public = 1 ';
			}
			$sql .= 'AND(('.$this->getIlikeCondition('summary', $this->words).')' 
				. ' OR ('.$this->getIlikeCondition('details', $this->words).'))' 
				. ' ORDER BY project_group_list.project_name, project_task.project_task_id';
		}
		return $sql;
	}
	
	/**
	 * getSections - returns the list of available subprojects
	 *
	 * @param $groupId int group id
	 * @param $showNonPublic boolean if we should consider non public sections
	 */
	function getSections($groupId, $showNonPublic=false) {
		$sql = 'SELECT group_project_id, project_name FROM project_group_list WHERE group_id = '.$groupId.'';
		if (!$showNonPublic) {
			$sql .= ' AND is_public = 1';
		}
		$sql .= ' ORDER BY project_name';
		
		$sections = array();
		$res = db_query($sql);
		while($data = db_fetch_array($res)) {
			$sections[$data['group_project_id']] = $data['project_name'];
		}
		return $sections;
	}
}

?>