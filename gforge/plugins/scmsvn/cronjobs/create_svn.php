#! /usr/bin/php5 -f
<?php
/**
 * create_svn.php 
 *
 * Francisco Gimeno <kikov@fco-gimeno.com>
 *
 * @version   $Id
 */

require dirname(__FILE__).'/../../env.inc.php';
require $gfwww.'include/squal_pre.php';
require_once $gfcommon.'include/cron_utils.php';

//	/path/to/svn/bin/
$svn_path='/usr/bin';

if (is_file($svn_path.'svnadmin')) {

} elseif (is_file('/usr/bin/svnadmin')) {
	$svn_path='/usr/bin';
} else {
	echo "\nsvn path is not set right in this script!!";
}


//	Owner of files - apache
$file_owner=$sys_apache_user.':'.$sys_apache_group;

//	Where is the SVN repository?
$svn=$svndir_prefix;

//	Whether to separate directories by first letter like /m/mygroup /a/apple
$first_letter = false;

// Whether to have all projects in a single repository
$one_repository = false;

//if everything is in one repository, we need a working checkout to use
$repos_co = '/var/svn-co';

//type of repository, whether filepassthru or bdb
//$repos_type = ' --fs-type fsfs ';
$repos_type = '';

//the name of the access_file
$access_file = $sys_var_path.'/svnroot-access';
$password_file = $sys_var_path.'/svnroot-authfile';

/*
	This script create the gforge dav/svn/docman repositories
*/



$err = "Creating Groups at ". $svn."\n";

if (empty($sys_apache_user) || empty($sys_apache_group)) {
	$err .=  "Error! sys_apache_user Is Not Set Or sys_apache_group Is Not Set!";
	echo $err;
	cron_entry(21,$err);
	exit;
}

if (empty($svn) || util_is_root_dir($svn)) {
	$err .=  "Error! svndir_prefix Is Not Set Or Points To The Root Directory!";
	echo $err;
	cron_entry(21,$err);
	exit;
}

$res = db_query("SELECT is_public,enable_anonscm,unix_group_name,groups.group_id 
	FROM groups, plugins, group_plugin 
	WHERE groups.status != 'P' 
	AND groups.group_id=group_plugin.group_id
	AND group_plugin.plugin_id=plugins.plugin_id
	AND plugins.plugin_name='scmsvn'");

if (!$res) {
	$err .=  "Error! Database Query Failed: ".db_error();
	echo $err;
	cron_entry(21,$err);
	exit;
}

//
//	If using a single large repository, create the checkout if necessary
//


if ($one_repository && !is_dir($repos_co)) {
	$err .= "Error! Checkout Repository Does Not Exist!";
	echo $err;
	cron_entry(21,$err);
	exit;
} elseif (!is_dir($svn)) {
	passthru ("mkdir $svn");
}

// The content of the access file used by svn authz apache2 module
$access_file_content = "";

while ( $row =& db_fetch_array($res) ) {	
	if ($one_repository) {
		if ($first_letter) {
			//
			//	Create the repository
			//
			passthru ("[ ! -d $repos_co/".$row["unix_group_name"][0]."/ ] && mkdir -p $repos_co/".$row["unix_group_name"][0]."/ && $svn_path/svn add $repos_co/".$row["unix_group_name"][0]."/");
			passthru ("[ ! -d $repos_co/".$row["unix_group_name"][0]."/".$row["unix_group_name"]."/ ] && mkdir -p $repos_co/".$row["unix_group_name"][0]."/".$row["unix_group_name"]."/ && $svn_path/svn add $repos_co/".$row["unix_group_name"][0]."/".$row["unix_group_name"]."/");
		} else {
			passthru ("[ ! -d $repos_co/".$row["unix_group_name"]." ] && mkdir -p $repos_co/".$row["unix_group_name"]."/ && $svn_path/svn add $repos_co/".$row["unix_group_name"]);
		}
		$cmd = 'chown -R '.$file_owner.' '.$repos_co;
		passthru ($cmd);
	} else {
		$project = &group_get_object($row["group_id"]); // get the group object for the current group
		if ( (!$project) || (!is_object($project))  )  {
			echo "Error Getting Group." . " Id : " . $row["group_id"] . " , Name : " . $row["unix_group_name"];
			break; // continue to the next project
		}		
		if ($first_letter) {
			//
			//	Create the repository
			//
			passthru ("[ ! -d $svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]." ] && mkdir -p $svn/".$row["unix_group_name"][0]."/ && $svn_path/svnadmin create $repos_type $svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
 			if ($project->usesPlugin('svncommitemail')) {
 				check_svn_mail($row["unix_group_name"], $svn."/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
 			}
 			if ($project->usesPlugin('svntracker')) {
 				check_svn_tracker($row["unix_group_name"], $svn."/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
 			}
		} else {
			passthru ("[ ! -d $svn/".$row["unix_group_name"]." ] &&  $svn_path/svnadmin create $repos_type $svn/".$row["unix_group_name"]);
			$cmd = 'chown -R '.$file_owner.' '.$svn.'/'.$row["unix_group_name"];
			passthru($cmd); // svn dir owned by apache or viewcvs doesn't work 
			if ($project->usesPlugin('svncommitemail')) {
 				check_svn_mail($row["unix_group_name"], $svn."/".$row["unix_group_name"]);
			}
			if ($project->usesPlugin('svntracker')) {
				check_svn_tracker($row["unix_group_name"], $svn."/".$row["unix_group_name"]);
			}
		}
		$access_file_content .= add2AccessFile($row["group_id"]);
		$cmd = 'chown -R '.$file_owner.' '.$svn;
		passthru ($cmd);
	}
}

// Now generate the contents for the password file
$password_file_contents = '';
$res = db_query("SELECT * FROM users WHERE user_id IN (SELECT DISTINCT user_id FROM user_group ug, group_plugin gp, plugins p
	WHERE ug.group_id=gp.group_id AND gp.plugin_id=p.plugin_id AND p.plugin_name='scmsvn')");
$output = "";
if (!$res) {
	$err .=  "Error! Database Query Failed: ".db_error();
	echo $err;
	cron_entry(21,$err);
	exit;
}

while ( $row =& db_fetch_array($res) ) {
	if (!empty($row["unix_pw"]))
		$password_file_contents .= $row["user_name"].":".$row["unix_pw"]."\n";
}


writeAccessFile($access_file, $access_file_content);
writePasswordFile($password_file, $password_file_contents);

//
// Move SVN repositories for deleted groups
//

// First make sure that the .deleted dir exists
if (!is_dir($svndir_prefix."/.deleted")) {
	system("mkdir ".$svndir_prefix."/.deleted");
}

$res = db_query("SELECT unix_group_name FROM deleted_groups WHERE isdeleted = 0;");
$err .= db_error();
$rows = db_numrows($res);
for($k = 0; $k < $rows; $k++) {
	$deleted_group_name = db_result($res,$k,'unix_group_name');
	
	$repos_dir = $svndir_prefix.'/'.$deleted_group_name;
	if (is_dir($repos_dir)) {
		// repository exists
		system("mv -f $repos_dir $svndir_prefix/.deleted/");
		system("chown -R root:root $svndir_prefix/.deleted/$deleted_group_name");
		system("chmod -R o-rwx $svndir_prefix/.deleted/$deleted_group_name");
	}

	$res2 = db_query("UPDATE deleted_groups set isdeleted = 1 WHERE unix_group_name = '$deleted_group_name';" );
	$err .= db_error();
}



function add2AccessFile($group_id) {
	$result = "";
	$project = &group_get_object($group_id);
	$result = "[". $project->getUnixName(). ":/]\n";
	$users= &$project->getMembers();
	if(isset ($users) ) {
		foreach($users as $user ) {
			$perm = &$project->getPermission($user);
			if ( $perm->isCVSWriter() ) {
				$result.= $user->getUnixName() . "= rw\n";
			} else if ( $perm->isCVSReader() ) {
				$result.= $user->getUnixName() . "= r\n";
			}
		}
	}
	if ( $project->enableAnonSCM() ) {
		$result.="anonsvn= r\n";
		$result.="* = r\n";

	}
	$result.="\n";
	return $result;
}

function writeAccessFile($fileName, $access_file_content) {
	$myFile= fopen( $fileName, "w" );
	fwrite ( $myFile, $access_file_content );
	fclose($myFile);
}

function writePasswordFile($fileName, $password_file_contents) {
	$myFile = fopen( $fileName, "w" );
	fwrite ( $myFile, $password_file_contents );
	fwrite ( $myFile, 'anonsvn:$apr1$Kfr69/..$J08mbyNpD81y42x7xlFDm.'."\n");
	fclose($myFile);
}

function check_svn_tracker($project, $repos) {
	
	$contents = @file_get_contents($repos."/hooks/post-commit");	
	if ( strstr($contents, "svntracker") == FALSE ) {
		add_svn_tracker_to_repository($project,$repos);
	}
}

function add_svn_tracker_to_repository($project,$repos) {
	global $sys_plugins_path,$file_owner;
	
	if (file_exists($repos.'/hooks/post-commit')) {
		$FOut = fopen($repos.'/hooks/post-commit', "a+");
	} else {
		$FOut = fopen($repos.'/hooks/post-commit', "w");
		$Line = '#!/bin/sh'."\n"; // add this line to first line or else the script fails
	}
	if($FOut) {
		$Line .= '
#begin added by svntracker'.
"\n/usr/bin/php -d include_path=".ini_get('include_path').
				" ".$sys_plugins_path. "/svntracker/bin/post.php".  ' "'.$repos.'" "$2"
#end added by svntracker';
		fwrite($FOut,$Line);
		`chmod +x $repos'/hooks/post-commit'`;
		`chmod 700 $repos'/hooks/post-commit'`;
		`chown $file_owner $repos'/hooks/post-commit'`;
		fclose($FOut);
	}
}

function check_svn_mail($project, $repos) {
	$contents = @file_get_contents($repos."/hooks/post-commit");
	if ( strstr($contents, "svncommitemail") == FALSE ) {
		add_svn_mail_to_repository($project,$repos);
	}
}

function add_svn_mail_to_repository($unix_group_name,$repos) {
	global $sys_lists_host,$file_owner,$sys_plugins_path;
	
	if (file_exists($repos.'/hooks/post-commit')) {
		$FOut = fopen($repos.'/hooks/post-commit', "a+");
	} else {
		$FOut = fopen($repos.'/hooks/post-commit', "w");
		$Line = '#!/bin/sh'."\n"; // add this line to first line or else the script fails
	}
	
	if($FOut) {
		$Line .= '
#begin added by svncommitemail
php '.$sys_plugins_path.'/svncommitemail/bin/commit-email.php '.$repos.' "$2" '.$unix_group_name.'-commits@'.$sys_lists_host.'
#end added by svncommitemail';
		fwrite($FOut,$Line);
		`chmod +x $repos'/hooks/post-commit'`;
		`chmod 700 $repos'/hooks/post-commit'`;
		`chown $file_owner $repos'/hooks/post-commit'`;
		fclose($FOut);
	}
}

if ($one_repository) {
	passthru ("cd $repos_co && $svn_path/svn commit -m\"\"");
}
system("chown $file_owner -R $svn");
#system("cd $svn/ && find -type d -exec chmod 700 {} \;");
#system("cd $svn/ && find -type f -exec chmod 600 {} \;");

cron_entry(21,$err);
?>
