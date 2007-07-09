<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2003-03-16
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('common/reporting/Report.class.php');

class ReportUserTime extends Report {

function ReportUserTime($user_id,$type,$start=0,$end=0) {
	$this->Report();

	if (!$start) {
		$start=mktime(0,0,0,date('m'),1,date('Y'));;
	}
	if (!$end) {
		$end=time();
	} else {
		$end--;
	}

	if (!$user_id) {
		$this->setError('No User_id');
		return false;
	}

	//
	//	Task report
	//
	if (!$type || $type=='tasks') {

		$res=db_query("SELECT pt.summary,sum(rtt.hours) AS hours 
			FROM rep_time_tracking rtt, project_task pt
			WHERE rtt.user_id='$user_id' 
			AND rtt.report_date BETWEEN '$start' AND '$end' 
			AND rtt.project_task_id=pt.project_task_id
			GROUP BY pt.summary
			ORDER BY hours DESC");

	//
	//	Category report
	//
	} elseif ($type=='category') {

		$res=db_query("SELECT rtc.category_name, sum(rtt.hours) AS hours 
			FROM rep_time_tracking rtt, rep_time_category rtc
			WHERE rtt.user_id='$user_id' 
			AND rtt.report_date BETWEEN '$start' AND '$end' 
			AND rtt.time_code=rtc.time_code
			GROUP BY rtc.category_name
			ORDER BY hours DESC");

	//
	//	Percentage this user spent on a specific subproject
	//
	} elseif ($type=='subproject') {

		$res=db_query("SELECT pgl.project_name, sum(rtt.hours) AS hours 
			FROM rep_time_tracking rtt, project_task pt, project_group_list pgl
			WHERE rtt.user_id='$user_id' 
			AND rtt.report_date BETWEEN '$start' AND '$end' 
			AND rtt.project_task_id=pt.project_task_id
			AND pt.group_project_id=pgl.group_project_id
			GROUP BY pgl.project_name
			ORDER BY hours DESC");

	}

	$this->start_date=$start;
	$this->end_date=$end;

	if (!$res || db_error()) {
		$this->setError('ReportUserAct:: '.db_error());
		return false;
	}

	$this->labels = util_result_column_to_array($res,0);
	$this->setData($res,1);
	return true;
}

}

?>