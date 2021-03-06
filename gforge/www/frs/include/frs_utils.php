<?php
/**
 * FRS HTML Utilities
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
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

/*

	Standard header to be used on all /project/admin/* pages

*/

function frs_admin_header($params) {
	global $group_id;

	/*
		Are they logged in?
	*/
	if (!session_loggedin()) {
		exit_not_logged_in();
	}

	$project =& group_get_object($group_id);
	if (!$project || !is_object($project)) {
		return;
	}

	$perm =& $project->getPermission( session_get_user() );
	if (!$perm || !is_object($perm)) {
		return;
	}

	/*
		Are they a release technician?
	*/
	if (!$perm->isReleaseTechnician()) {
		exit_permission_denied();
	}

	frs_header($params);
}

function frs_admin_footer() {
	site_project_footer(array());
}

function frs_header($params) {
	global $group_id,$HTML,$sys_use_frs;

	/*
		Does this site use FRS?
	*/
	if (!$sys_use_frs) {
		exit_disabled();
	}

	$project =& group_get_object($group_id);
	if (!$project || !is_object($project)) {
		exit_no_group();
	}

	$params['toptab']='frs';
	$params['group']=$group_id;
	site_project_header($params);

	if (session_loggedin()) {
		$perm =& $project->getPermission(session_get_user());
		if ($perm && is_object($perm) && !$perm->isError() && $perm->isReleaseTechnician()) {
			echo $HTML->subMenu(
				array(
					_('Files'),
					_('Admin')
				),
				array(
					'/frs/?group_id='.$group_id,
					'/frs/admin/?group_id='.$group_id
				)
			);
		}
	}
}

function frs_footer() {
	site_project_footer(array());
}


/*


	The following functions are for the FRS (File Release System)


*/


/*

	pop-up box of supported frs statuses

*/

function frs_show_status_popup ($name='status_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of statuses
	*/
	global $FRS_STATUS_RES;
	if (!isset($FRS_STATUS_RES)) {
		$FRS_STATUS_RES=db_query("SELECT * FROM frs_status");
	}
	return html_build_select_box ($FRS_STATUS_RES,$name,$checked_val,false);
}

/*

	pop-up box of supported frs filetypes

*/

function frs_show_filetype_popup ($name='type_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of the available filetypes
	*/
	global $FRS_FILETYPE_RES;
	if (!isset($FRS_FILETYPE_RES)) {
		$FRS_FILETYPE_RES=db_query("SELECT * FROM frs_filetype");
	}
	return html_build_select_box ($FRS_FILETYPE_RES,$name,$checked_val,true,_('Must Choose One'));
}

/*

	pop-up box of supported frs processor options

*/

function frs_show_processor_popup ($name='processor_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of the available processors 
	*/
	global $FRS_PROCESSOR_RES;
	if (!isset($FRS_PROCESSOR_RES)) {
		$FRS_PROCESSOR_RES=db_query("SELECT * FROM frs_processor");
	}
	return html_build_select_box ($FRS_PROCESSOR_RES,$name,$checked_val,true,_('Must Choose One'));
}

/*

	pop-up box of packages:releases for this group

*/


function frs_show_release_popup ($group_id, $name='release_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of releases for the project
	*/
	global $FRS_RELEASE_RES, $sys_database_type;

	if (!$group_id) {
		return 'ERROR - GROUP ID REQUIRED';
	} else {
		if (!isset($FRS_RELEASE_RES)) {
			if ($sys_database_type == "mysql") {
				$sql = "SELECT frs_release.release_id,concat(frs_package.name,' : ',frs_release.name) ";
			} else {
				$sql = "SELECT frs_release.release_id,(frs_package.name || ' : ' || frs_release.name) ";
			}
			$sql .=
				"FROM frs_release,frs_package ".
				"WHERE frs_package.group_id='$group_id' ".
				"AND frs_release.package_id=frs_package.package_id";

			$FRS_RELEASE_RES = db_query($sql);
			echo db_error();
		}
		return html_build_select_box($FRS_RELEASE_RES,$name,$checked_val,false);
	}
}

/*

	pop-up box of packages for this group

*/

function frs_show_package_popup ($group_id, $name='package_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of packages for this project
	*/
	global $FRS_PACKAGE_RES;
	if (!$group_id) {
		return 'ERROR - GROUP ID REQUIRED';
	} else {
		if (!isset($FRS_PACKAGE_RES)) {
			$FRS_PACKAGE_RES=db_query("SELECT package_id,name 
				FROM frs_package WHERE group_id='$group_id'");
			echo db_error();
		}
		return html_build_select_box ($FRS_PACKAGE_RES,$name,$checked_val,false);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
