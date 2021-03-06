<?php

/**
 * GForge Forum Admin Class
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   
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

/* forum admin class
	by Daniel Perez - 2005
*/

require_once $gfwww.'include/pre.php';

class ForumAdmin extends Error {
	var $group_id;
	var $p,$g;
	
	function ForumAdmin($gid=0) {
		global $group_id;
		$this->group_id = $group_id; 
		if ($gid) {
			$this->group_id = $group_id;
			$this->g =& group_get_object($group_id);
			$this->p =& $this->g->getPermission( session_get_user() );
		}
	}
	
	/**
	 *  PrintAdminMessageOptions - prints the different administrator options for a message
	 *
	 *	@param   integer	The Message ID
	 *	@param   integer	The Group ID
	 *	@param   integer	The Thread ID : to return to the message if the user cancels (forumhtml only, not message.php)
	 *	@param   integer	The Forum ID : to return to the message if the user cancels (forumhtml only, not message.php)
	 *	@return  The HTML output
	 */
	
	function PrintAdminMessageOptions($msg_id,$group_id,$thread_id=0,$forum_id=0) {
		global $HTML;
		
		$return = '<a href="admin/index.php?editmsg=' . $msg_id  . '&group_id=' . $group_id .  '&thread_id=' . $thread_id. '&forum_id=' . $forum_id . '">' . html_image('ic/forum_edit.gif','37','15',array('alt'=>"Edit")) . "</a>";
		$return .= '    <a href="admin/index.php?deletemsg=' . $msg_id  . '&group_id=' . $group_id . '&thread_id=' . $thread_id. '&forum_id=' . $forum_id . '">' . html_image('ic/forum_delete.gif','16','18',array('alt'=>"Delete")) . "</a>";
		$return .= "<br>";
		return $return;
	}
	
	
	/**
	 *  PrintAdminOptions - prints the different administrator option for the forums (heading).
	 *
	 */
	
	function PrintAdminOptions() {
		global $group_id,$forum_id;
		
		echo '
			<p>
			<a href="index.php?group_id='.$group_id.'&amp;add_forum=1">'._('Add forum').'</a>';
		echo '
			| <a href="pending.php?action=view_pending&group_id=' . $group_id . '">' . _('Manage Pending Messages').'</a><br /></p>';
	}
	
	/**
	 *  PrintAdminOptions - prints the administrator option for an individual forum, to link to the pending messages management
	 *
	 *	@param 	int		The Forum ID.
	 */
	
	function PrintAdminPendingOption($forum_id) {
		echo '
			<a href="pending.php?action=view_pending&group_id=' . $this->group_id . '&forum_id=' . $forum_id . '">' . _('Manage Pending Messages').'</a><br /></p>';
	}
	
	/**
	 *  GetPermission - Gets the permission for the user
	 *
	 *  @return  object	 The permission
	 */
	function &GetPermission() {
		return $this->p;
	}
	
	/**
	 *  GetGroupObject - Gets the group object of the forum
	 *
	 *  @return  object	 The group obj
	 */
	function &GetGroupObject() {
		return $this->g;
	}
	
	/**
	 *  isForumAdmin - checks whether the authorized user is a forum admin. The user must be authenticated
	 *
	 *	@param 	string	 The forum id
	 */
	function isForumAdmin($forum_id) {
		$f = new Forum ($this->g,$forum_id);
		if (!$f || !is_object($f)) {
			exit_error('Error','Could Not Get Forum Object');
		} elseif ($f->isError()) {
			exit_error('Error',$f->getErrorMessage());
		} elseif (!$f->userIsAdmin()) {
			return false;
		}
		return true;
	}
	
	/**
	 *  isGroupAdmin - checks whether the authorized user is a group admin for the forums. The user must be authenticated
	 *
	 */
	function isGroupAdmin() {	
		if ($this->p->isForumAdmin()) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 *  Authorized - authorizes and returns true if the user is authorized for the group, or false.
	 *
	 *  @param  string	 The group id.
	 */
	
	function Authorized($group_id) {
		if (!$group_id) {
			$this->setGroupIdError();
			return false;
		}
		if (!session_loggedin()) {
			$this->setPermissionDeniedError();
			return false;
		}
		$this->group_id = $group_id;
		$this->g =& group_get_object($group_id);
		if (!$this->g || !is_object($this->g) || $this->g->isError()) {
			$this->setGroupIdError();
			return false;
		}
		$this->p =& $this->g->getPermission( session_get_user() );
		if (!$this->p || !is_object($this->p) || $this->p->isError()) {
			$this->setPermissionDeniedError();
			return false;
		}
		return true;
	}
	
	/**
	 *  ExecuteAction - Executes the action passed as parameter
	 *
	 *  @param  string	 action to execute.
	 */
	function ExecuteAction ($action) {
		global $HTML;
		
		if ($action == "change_status") { //change a forum
			$forum_name = getStringFromRequest('forum_name');
			$description = getStringFromRequest('description');
			$send_all_posts_to = getStringFromRequest('send_all_posts_to');
			$allow_anonymous = getIntFromRequest('allow_anonymous');
			$is_public = getIntFromRequest('is_public');
			$moderation_level = getIntFromRequest('moderation_level');
			$group_forum_id = getIntFromRequest('group_forum_id');
			/*
				Change a forum
			*/
			$f=new Forum($this->g,$group_forum_id);
			if (!$f || !is_object($f)) {
				exit_error(_('Error'),_('Error getting Forum'));
			} elseif ($f->isError()) {
				exit_error(_('Error'),$f->getErrorMessage());
			}
			if (!$f->userIsAdmin()) {
				exit_permission_denied();
			}
			if (!$f->update($forum_name,$description,$allow_anonymous,$is_public,$send_all_posts_to,$moderation_level)) {
				exit_error(_('Error'),$f->getErrorMessage());
			} else {
				$feedback = _('Forum Info Updated Successfully');
			}
			return $feedback;
		}
		if ($action == "add_forum") { //add forum
			$forum_name = getStringFromRequest('forum_name');
			$description = getStringFromRequest('description');
			$is_public = getStringFromRequest('is_public');
			$send_all_posts_to = getStringFromRequest('send_all_posts_to');
			$allow_anonymous = getStringFromRequest('allow_anonymous');
			$moderation_level = getIntFromRequest('moderation_level');
			/*
				Adding forums to this group
			*/
			if (!$this->p->isForumAdmin()) {
				form_release_key(getStringFromRequest("form_key"));
				exit_permission_denied();
			}
			$f=new Forum($this->g);
			if (!$f || !is_object($f)) {
				form_release_key(getStringFromRequest("form_key"));
				exit_error(_('Error'),_('Error getting Forum'));
			} elseif ($f->isError()) {
				form_release_key(getStringFromRequest("form_key"));
				exit_error(_('Error'),$f->getErrorMessage());
			}
			if (!$f->create($forum_name,$description,$is_public,$send_all_posts_to,1,$allow_anonymous,$moderation_level)) {
				form_release_key(getStringFromRequest("form_key"));
				exit_error(_('Error'),$f->getErrorMessage());
			} else {
				$feedback = _('Forum created successfully');
			}
			return $feedback;
		}
		if ($action == "delete") { //Deleting messages or threads
			$msg_id = getStringFromRequest('deletemsg');
			$forum_id = getIntFromRequest('forum_id');
			$f=new Forum($this->g,$forum_id);
			if (!$f || !is_object($f)) {
				exit_error(_('Error'),_('Error getting Forum'));
			} elseif ($f->isError()) {
				exit_error(_('Error'),$f->getErrorMessage());
			}
			if (!$f->userIsAdmin()) {
				exit_permission_denied();
			}

			$fm=new ForumMessage($f,$msg_id);
			if (!$fm || !is_object($fm)) {
				exit_error(_('Error'),_('Error Getting ForumMessage'));
			} elseif ($fm->isError()) {
				exit_error(_('Error'),$fm->getErrorMessage());
			}
			$count=$fm->delete();
			if (!$count || $fm->isError()) {
				exit_error(_('Error'),$fm->getErrorMessage());
			} else {
				$feedback = sprintf(ngettext('%1$s message deleted', '%1$s messages deleted', $count), $count);
			}
			return $feedback;
		}
		if ($action == "delete_forum") { //delete the forum
			/*
				Deleting entire forum
			*/
			$group_forum_id = getIntFromRequest('group_forum_id');
			$f=new Forum($this->g,$group_forum_id);
			if (!$f || !is_object($f)) {
				exit_error(_('Error'),_('Error getting Forum'));
			} elseif ($f->isError()) {
				exit_error(_('Error'),$f->getErrorMessage());
			}
			if (!$f->userIsAdmin()) {
				exit_permission_denied();
			}
			if (!$f->delete(getStringFromRequest('sure'),getStringFromRequest('really_sure'))) {
				exit_error(_('Error'),$f->getErrorMessage());
			} else {
				$feedback = _('Successfully Deleted');
			}
			return $feedback;
		}
		if ($action=="view_pending") {
			//show the pending messages, awaiting moderation
			$group_id = $this->group_id;
			$forum_id = getStringFromRequest("forum_id");
			if ($this->isGroupAdmin()) {
				$this->PrintAdminOptions();
			}
			$sql = "SELECT forum_name, group_forum_id FROM forum_group_list WHERE group_id='$group_id' and moderation_level > 0";
			$res = db_query($sql);
			if (!$res) {
				echo db_error();
				return;			
			}
			
			global $sys_db_row_pointer;
			$moderated_forums = array();
			for ($i=0;$i<db_numrows($res);$i++) {
				$aux = db_fetch_array($res);
				$moderated_forums[$aux[1]] = $aux[0];
			}
			
			if (count($moderated_forums)==0) {
				echo $HTML->feedback(_('No forums are moderated for this group'));
				forum_footer(array());
				exit();
			}
			if (!$forum_id) {
				//get the first one
				$keys = array_keys($moderated_forums);
				$forum_id = $keys[0];
			}
			
			echo '
			<script language="JavaScript" type="text/javascript">
		
			function confirmDel() {
				var agree=confirm("Proceed? Actions are permanent!");
				if (agree) {
					return true;
				} else {
					return false;
				}
			}
			</script>
			<p><form name="pending" action="pending.php" method="post">
			<input type="hidden" name="action" value="update_pending" />
			<input type="hidden" name="form_key" value="' . form_generate_key() . '">
			<input type="hidden" name="group_id" value="' . getIntFromRequest("group_id") . '" />
			<input type="hidden" name="forum_id" value="' . $forum_id . '" />

			';
			
			//$moderated_forums["A"] = "All Forums for this group"; // to show all
			echo html_build_select_box_from_assoc($moderated_forums,forum_id,$forum_id);
			echo '    <input name="Go" type="submit" value="Go"><p>';
			
			$title = array();
			$title[] = _('Forum Name');
			$title[] = _('Message');
			$title[] = "Action";
			
			$sql = "SELECT msg_id,subject,pm.group_forum_id,gl.forum_name FROM forum_pending_messages pm, forum_group_list gl WHERE pm.group_forum_id='$forum_id' AND pm.group_forum_id=gl.group_forum_id AND gl.group_forum_id='$forum_id'";
			$res = db_query($sql);
			if (!$res) {
				echo db_error();
				return;			
			}

			$options = array("1" => "No action","2" => "Delete","3" => "Release"); //array with the supported actions
			//i�ll make a hidden variable, helps to determine when the user updates the info, which action corresponds to which msgID
			for($i=0;$i<db_numrows($res);$i++) {
				$ids .= db_result($res,$i,'msg_id') . ",";
			}
			
			$i = 2;
			echo $HTML->listTableTop($title);
			while ($onemsg = db_fetch_array($res)) {

				//$url = 'pendingmsgdetail.php?msg_id=' . $onemsg[msg_id];
				//<a href=\"javascript:msgdetail('$url');\">$onemsg[subject]</a>
				$url = "http://www.google.com";
				echo "
				<tr" . $HTML->boxGetAltRowStyle($i++). ">
					<td>$onemsg[forum_name]</td>	
					<td><a href=\"#\" OnClick=\"window.open('pendingmsgdetail.php?msg_id=$onemsg[msg_id]&forum_id=$onemsg[group_forum_id]&group_id=$group_id','PendingMessageDetail','width=800,height=600,status=no,resizable=yes');\">$onemsg[subject]</a></td>
					<td><div align=\"right\">" . html_build_select_box_from_assoc($options,"doaction[]",1) . "</div></td>
				</tr>";
			}
			
			echo $HTML->listTableBottom();
			echo '
			<p>
			<input type="hidden" name="msgids" value="' . $ids . '">
			<div align="right"><input type="submit" onClick="return confirmDel();" name="update" value="' . _('Update') . '"></div>
			</form>
			';
		}
		if ($action == "update_pending") {
			$group_id = getIntFromRequest("group_id");
			$forum_id = getIntFromRequest("forum_id");
			$msgids = getStringFromRequest("msgids");//the message ids to update
			$doaction = getArrayFromRequest("doaction"); //the actions for the messages
			
			$msgids = split(",",$msgids);
			array_pop($msgids);//this last one is empty
			
			/*if ($this->isGroupAdmin()) {
				$this->PrintAdminOptions();
			}*/
			
			$results = array(); //messages
			for($i=0;$i<count($msgids);$i++) {
				switch ($doaction[$i]) {
					case 1 : { 
						//no action
						break;
					}
					case 2 : { 
						//delete
						db_begin();
						$sql = "DELETE FROM forum_pending_attachment WHERE msg_id='$msgids[$i]'";
						if (!db_query($sql)) {
							$feedback .= "DB Error ";
							$feedback .= db_error() . "<br>";
							db_rollback();
							break;
						}
						$sql = "DELETE FROM forum_pending_messages WHERE msg_id='$msgids[$i]'";
						if (!db_query($sql)) {
							$feedback .= "DB Error ";
							$feedback .= db_error() . "<br>";
							db_rollback();
							break;
						}
						db_commit();
						$feedback .= _('Forum deleted');
						break;
					}
					case 3 : { 
						//release
						$sql = "SELECT * FROM forum_pending_messages WHERE msg_id='$msgids[$i]'";
						$res1 = db_query($sql);
						if (!$res1) {
							$feedback .= "DB Error " . db_error() . "<br>";
							break;
						}
						$sql = "SELECT * FROM forum_pending_attachment WHERE msg_id='$msgids[$i]'";
						$res2 = db_query($sql);
						if (!$res2) {
							$feedback .= "DB Error " . db_error() . "<br>";
							break;
						}
						$f = new Forum($this->g,$forum_id);
						if (!$f || !is_object($f)) {
							exit_error(_('Error'),_('Error getting new Forum'));
						} elseif ($f->isError()) {
							exit_error(_('Error'),$f->getErrorMessage());
						}
						$fm = new ForumMessage($f); // pending = false
						if (!$fm || !is_object($fm)) {
							exit_error(_('Error'), "Error getting new ForumMessage");
						} elseif ($fm->isError()) {
							exit_error(_('Error'),"Error getting new ForumMessage: ".$fm->getErrorMessage());
						}
						$group_forum_id = db_result($res1,0,"group_forum_id");
						$subject = db_result($res1,0,"subject");
						$body = db_result($res1,0,"body");
						$post_date = db_result($res1,0,"post_date");
						$thread_id = db_result($res1,0,"thread_id");
						$is_followup_to = db_result($res1,0,"is_followup_to");
						$posted_by = db_result($res1,0,"posted_by");
						$has_followups = db_result($res1,0,"has_followups");
						$most_recent_date = db_result($res1,0,"most_recent_date");
						if ($fm->insertreleasedmsg($group_forum_id,$subject, $body,$post_date, $thread_id, $is_followup_to,$posted_by,$has_followups,time())) {
							$feedback .= "( $subject ) " . _('Pending forum released') . "<br>";
							if (db_numrows($res2)>0) {
								//if there�s an attachment
								$am = NEW AttachManager();//object that will handle and insert the attachment into the db
								$am->SetForumMsg($fm);
								$userid = db_result($res2,0,"userid");
								$dateline = db_result($res2,0,"dateline");
								$filename = db_result($res2,0,"filename");
								$filedata = db_result($res2,0,"filedata");
								$filesize = db_result($res2,0,"filesize");
								$visible = db_result($res2,0,"visible");
								$msg_id = db_result($res2,0,"msg_id");
								$filehash = db_result($res2,0,"filehash");
								$mimetype = db_result($res2,0,"mimetype");
								$am->AddToDBOnly($userid, $dateline, $filename, $filedata, $filesize, $visible, $filehash, $mimetype);
								foreach ($am->Getmessages() as $item) {
									$feedback .= "$msg_id - " . $item . "<br>";
								}
							}
							$deleteok = true;
						} else {
							if ($fm->isError()) {
							    if ( $fm->getErrorMessage() == (_('Couldn\'t Update Master Thread parent with current time')) ) {
							    	//the thread which the message was replying to doesn�t exist any more
							    	$feedback .= "( " . $subject . " ) " . _('The thread which the message was posted to doesn\'t exist anymore, please delete the message.') . "<br>";
							    } else {
									$feedback .= "$msg_id - " . $fm->getErrorMessage() . "<br>";
							    }
								$deleteok = false;
							}
						}
								
						if ( isset($am) && (is_object($am)) ) {
							//if there was an attach, check if it was uploaded ok
							 if ((!$am->isError())) {
								$deleteok = true;
							 } else {
							 	//undo the changes to the forum table
								db_begin();
								$sql = "DELETE FROM forum WHERE msg_id='$fm->getID()'";
								if (!db_query($sql)) {
									$feedback .= "DB Error ";
									$feedback .= db_error() . "<br>";
									db_rollback();
									break;
								}
								db_commit();
								$deleteok = false;
							 }
						}
						
						if ($deleteok) {
							//delete the message and attach
							db_begin();
							$sql = "DELETE FROM forum_pending_attachment WHERE msg_id='$msgids[$i]'";
							if (!db_query($sql)) {
								$feedback .= "DB Error ";
								$feedback .= db_error() . "<br>";
								db_rollback();
								break;
							}
							$sql = "DELETE FROM forum_pending_messages WHERE msg_id='$msgids[$i]'";
							if (!db_query($sql)) {
								$feedback .= "DB Error ";
								$feedback .= db_error() . "<br>";
								db_rollback();
								break;
							}
							db_commit();
						}
					}
				}
			}
			html_feedback_top($feedback);
			$page = 0;
			$this->ExecuteAction("view_pending");
		}
	}
	
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
