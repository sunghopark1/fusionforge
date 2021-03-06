<?php
/**
 * FusionForge project manager
 *
 * Copyright 1999-2000, Tim Perdue/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'pm/ProjectTask.class.php';

class ProjectTaskFactory extends Error {

	/**
	 * The ProjectGroup object.
	 *
	 * @var	 object  $ProjectGroup.
	 */
	var $ProjectGroup;

	/**
	 * The project_tasks array.
	 *
	 * @var  array  project_tasks.
	 */
	var $project_tasks;
	var $order;
	var $status;
	var $category;
	var $assigned_to;
	var $offset;
	var $max_rows;
	var $fetched_rows;
	var $view_type;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The ProjectGroup object to which this ProjectTask is associated.
	 *	@return	boolean	success.
	 */
	function ProjectTaskFactory(&$ProjectGroup) {
		$this->Error();
		if (!$ProjectGroup || !is_object($ProjectGroup)) {
			$this->setError('ProjectTask:: No Valid ProjectGroup Object');
			return false;
		}
		if ($ProjectGroup->isError()) {
			$this->setError('ProjectTask:: '.$ProjectGroup->getErrorMessage());
			return false;
		}
		$this->ProjectGroup =& $ProjectGroup;
		$this->order='project_task_id';
		$this->offset=0;

		return true;
	}

	/**
	 *	setup - sets up limits and sorts before you call getTasks().
	 *
	 *	@param	int	The offset - number of rows to skip.
	 *	@param	string	The way to order - ASC or DESC.
	 *	@param	int	The max number of rows to return.
	 *	@param	string	Whether to set these prefs into the user_prefs table - use "custom".
	 *	@param	int	Include this param if you want to limit to a certain assignee.
	 *	@param	int	Include this param if you want to limit to a certain category.
	 *	@param	string	What view mode the screen should be in.
	 */
	function setup($offset,$order,$max_rows,$set,$_assigned_to,$_status,$_category_id,$_view='') {
//echo "<br />offset: $offset| order: $order|max_rows: $max_rows|_assigned_to: $_assigned_to|_status: $_status|_category_id: $_category_id +";
		if ((!$offset) || ($offset < 0)) {
			$this->offset=0;
		} else {
			$this->offset=$offset;
		}

		if (session_loggedin()) {
			$u =& session_get_user();
		}

		if ($order) {
			if ($order=='project_task_id' || $order=='percent_complete'
				|| $order=='summary' || $order=='start_date' || $order=='end_date' || $order=='priority') {
				if (session_loggedin()) {
					$u->setPreference('pm_task_order', $order);
				}
			} else {
				$order = 'project_task_id';
			}
		} else {
			if (session_loggedin()) {
				$order = $u->getPreference('pm_task_order');
			}
		}
		if (!$order) {
			$order = 'project_task_id';
		}
		$this->order=$order;

		if ($set=='custom') {
			/*
				if this custom set is different than the stored one, reset preference
			*/
			$pref_=$_assigned_to.'|'.$_status.'|'.$_category_id.'|'.$_view;
			if (session_loggedin() && ($pref_ != $u->getPreference('pm_brow_cust'.$this->ProjectGroup->Group->getID()))) {
				//echo 'setting pref';
				$u->setPreference('pm_brow_cust'.$this->ProjectGroup->Group->getID(),$pref_);
			}
		} else {
			if (session_loggedin()) {
				if ($pref_=$u->getPreference('pm_brow_cust'.$this->ProjectGroup->Group->getID())) {
					$prf_arr=explode('|',$pref_);
					$_assigned_to=$prf_arr[0];
					$_status=$prf_arr[1];
					$_category_id=$prf_arr[2];
					$_view=$prf_arr[3];
				}
			}
		}
		$this->status=$_status;
		$this->assigned_to=$_assigned_to;
		$this->category=$_category_id;
		$this->view_type=$_view;

		if (!$max_rows || $max_rows < 5) {
			$max_rows=50;
		}
		$this->max_rows=$max_rows;
	}

	/**
	 *	getTasks - get an array of ProjectTask objects.
	 *
	 *	@return	array	The array of ProjectTask objects.
	 */
	function &getTasks() {
		if ($this->project_tasks) {
			return $this->project_tasks;
		}

		//if status selected, and more to where clause
		if ($this->status && ($this->status != 100)) {
			//for open tasks, add status=100 to make sure we show all
			$status_str="AND project_task_vw.status_id IN (".$this->status.(($this->status==1)?',100':'').")";
		} else {
			//no status was chosen, so don't add it to where clause
			$status_str='';
		}

		//if assigned to selected, and more to where clause
		if ($this->assigned_to) {
			if (is_array ($this->assigned_to)) {
				$assigned_str="AND project_assigned_to.assigned_to_id IN (".join ($this->assigned_to,', ').")";
			} else {
				$assigned_str="AND project_assigned_to.assigned_to_id='".$this->assigned_to."'";
			}
			$assigned_str2=',project_assigned_to';
			$assigned_str3='project_task_vw.project_task_id=project_assigned_to.project_task_id AND';

		} else {
			//no assigned to was chosen, so don't add it to where clause
			$assigned_str='';
			$assigned_str2='';
			$assigned_str3='';
		}

		if ($this->category) {
			$cat_str="AND project_task_vw.category_id='".$this->category."'";
		} else {
			$cat_str='';
		}

		//
		//	sort using an external ID useful only to something like MS Project
		//
		if ($this->order=='external_id') {
			$ext_str='natural left join project_task_external_order';
			$ext_fld_str=',project_task_external_order.external_id';
		} else {
			$ext_str='';
			$ext_fld_str='';
		}

/*
select project_task_vw.*,project_assigned_to.* FROM project_task_vw,project_assigned_to 
WHERE project_assigned_to.project_task_id=project_task_vw.project_task_id;
*/
		$sql="SELECT project_task_vw.* $ext_fld_str
			FROM project_task_vw $ext_str $assigned_str2 
			WHERE $assigned_str3 project_task_vw.group_project_id='". $this->ProjectGroup->getID() ."' 
			$assigned_str $status_str $cat_str 
			ORDER BY ".$this->order.(($this->order=='priority') ? ' DESC ':' ');

//echo $sql;
	
		$result=db_query($sql,($this->max_rows),$this->offset);
		$rows = db_numrows($result);
		$this->fetched_rows=$rows;
		if (db_error()) {
			$this->setError('Database Error: '.db_error().$sql);
			return false;
		}

		$this->project_tasks = array();
		while ($arr =& db_fetch_array($result)) {
			$this->project_tasks[] = new ProjectTask($this->ProjectGroup, $arr['project_task_id'], $arr);
		}
		return $this->project_tasks;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
