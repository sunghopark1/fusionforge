<?php
//
//  get the Group object
//
$group =& group_get_object($group_id);
if (!$group || !is_object($group) || $group->isError()) {
	exit_no_group();
}

$atf = new ArtifactTypeFactory($group);
if (!$group || !is_object($group) || $group->isError()) {
	exit_error('Error','Could Not Get ArtifactTypeFactory');
}

$at_arr =& $atf->getArtifactTypes();

//required params for site_project_header();
$params['group']=$group_id;
$params['toptab']='tracker';
$params['pagename']='tracker';
$params['sectionvals']=array($group->getPublicName());

echo site_project_header($params);
echo $HTML->subMenu(
	array(
		$Language->getText('group','short_tracker'),
		$Language->getText('tracker','reporting'),
		$Language->getText('tracker','admin')
	),
	array(
		'/tracker/?group_id='.$group_id,
		'/tracker/reporting/?group_id='.$group_id,
		'/tracker/admin/?group_id='.$group_id
		
	)
);
/*
echo '<strong><a href="/tracker/reporting/?group_id='.$group_id.'">'.$Language->getText('tracker','reporting').'</a> | '
	 .'<a href="/tracker/admin/?group_id='.$group_id.'">'.$Language->getText('tracker','admin').'</a>'
	 .'</strong><p>';
*/

if (!$at_arr || count($at_arr) < 1) {
	echo "<h1>".$Language->getText('tracker','no_trackers')."</h1>";
	echo "<p><strong>".$Language->getText('tracker','no_trackers_text',array('<a href="/tracker/admin/?group_id='.$group_id.'">','</a>'))."</strong>";
	} else {

	echo '<p>'.$Language->getText('tracker', 'choose').'<p>';

	/*
		Put the result set (list of trackers for this group) into a column with folders
	*/
	$tablearr=array($Language->getText('group','short_tracker'),$Language->getText('general','open'),$Language->getText('general','total'),$Language->getText('tracker_admin_update_type','description'));
	echo $HTML->listTableTop($tablearr);

	for ($j = 0; $j < count($at_arr); $j++) {
		echo '
		<tr '. $HTML->boxGetAltRowStyle($j) . '>
			<td><a href="/tracker/?atid='.$at_arr[$j]->getID().'&group_id='.$group_id.'&func=browse">'.
				html_image("ic/tracker20w.png","20","20",array("border"=>"0")).' &nbsp;'.
				$at_arr[$j]->getName() .'</a>
			</td>
			<td align="center">'. (int) $at_arr[$j]->getOpenCount() . '
			</td>
			<td align="center">'. (int) $at_arr[$j]->getTotalCount() .'
			</td>
			<td>' .  $at_arr[$j]->getDescription() .'
			</td>
		</tr>';
	}
	echo $HTML->listTableBottom();
}

echo site_project_footer(array());

?>