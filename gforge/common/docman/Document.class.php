<?php
/**
 * FusionForge document manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
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
require_once $gfcommon.'docman/Parsedata.class.php';

class Document extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var	 array   $data_array.
	 */
	var $data_array;

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	  /**
       * The Search engine path.
       *
       * @var  string $engine_path
       */
       var $engine_path;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this document is associated.
	 *  @param  int	 The docid.
	 *  @param  array	The associative array of data.
	 *	@return	boolean	success.
	 */
	function Document(&$Group, $docid=false, $arr=false, $engine = "") {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setNotValidGroupObjectError();
			return false;
		}
		if ($Group->isError()) {
			$this->setError('Document:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		if ($docid) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($docid)) {
					return false;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError('Group_id in db result does not match Group Object');
					$this->data_array = null;
					return false;
				}
			}
			if (!$this->isPublic()) {
				$perm =& $this->Group->getPermission( session_get_user() );

				if (!$perm || !is_object($perm) || !$perm->isMember()) {
					$this->setPermissionDeniedError();
					$this->data_array = null;
					return false;
				}
			}
		}
		$this->engine_path = $engine;
		return true;
	}

	/**
	 *	create - use this function to create a new entry in the database.
	 *
	 *	@param	string	The filename of this document. Can be a URL.
	 *	@param	string	The filetype of this document. If filename is URL, this should be 'URL';
	 *	@param	string	The contents of this document (should be addslashes()'d before entry).
	 *	@param	int	The doc_group id of the doc_groups table.
	 *	@param	string	The title of this document.
	 *	@param	int	The language id of the supported_languages table.
	 *	@param	string	The description of this document.
	 *	@return	boolean	success.
	 */
	function create($filename,$filetype,$data,$doc_group,$title,$language_id,$description) {
		if (strlen($title) < 5) {
			$this->setError(_('Title Must Be At Least 5 Characters'));
			return false;
		}
		if (strlen($description) < 10) {
			$this->setError(_('Document Description Must Be At Least 10 Characters'));
			return false;
		}

/*
		$perm =& $this->Group->getPermission( session_get_user() );
		if (!$perm || !is_object($perm) || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}
*/
		$user_id = ((session_loggedin()) ? user_getid() : 100);

		$doc_initstatus = '3';
		// If Editor - uploaded Documents are ACTIVE
		if ( session_loggedin() ) {
			$perm =& $this->Group->getPermission( session_get_user() );
			if ($perm && is_object($perm) && $perm->isDocEditor()) {
				$doc_initstatus = '1';
			}
		}

		// If $filetype is "text/plain", $body convert UTF-8 encoding.
		if (strcasecmp($filetype,"text/plain") === 0 &&
			function_exists('mb_convert_encoding') &&
			function_exists('mb_detect_encoding')) {
			$data = mb_convert_encoding($data,'UTF-8',mb_detect_encoding($data));
		}
		$data1 = $data;

         // key words for in-document search
         $kw = new Parsedata ($this->engine_path);
         $kwords = $kw->get_parse_data (stripslashes($data1), htmlspecialchars($title1), htmlspecialchars($description), $filetype);
         // $kwords = "";

		$filesize = strlen($data);

		$sql="INSERT INTO doc_data (group_id,title,description,createdate,doc_group,
			stateid,language_id,filename,filetype,filesize,data,data_words,created_by)
			VALUES ('".$this->Group->getId()."',
			'". htmlspecialchars($title) ."',
			'". htmlspecialchars($description) ."',
			'". time() ."',
			'$doc_group',
			'$doc_initstatus',
			'$language_id',
			'$filename',
			'$filetype',
			'$filesize',
			'". base64_encode(stripslashes($data)) ."',
			'$kwords',
			'$user_id')";

		db_begin();
		$result=db_query($sql);
		if (!$result) {
			$this->setError('Error Adding Document: '.db_error());
			db_rollback();
			return false;
		}
		$docid=db_insertid($result,'doc_data','docid');
		if (!$this->fetchData($docid)) {
			db_rollback();
			return false;
		}
		$this->sendNotice(true);
		db_commit();
		return true;
	}

	/**
	 *  fetchData() - re-fetch the data for this document from the database.
	 *
	 *  @param  int	 The document id.
	 *	@return	boolean	success
	 */
	function fetchData($docid) {
		$res=db_query("SELECT * FROM docdata_vw
			WHERE docid='$docid'
			AND group_id='". $this->Group->getID() ."'");
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Document:: Invalid docid'));
			return false;
		}
		$this->data_array =& db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getGroup - get the Group object this Document is associated with.
	 *
	 *	@return	Object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getID - get this docid.
	 *
	 *	@return	int	The docid.
	 */
	function getID() {
		return $this->data_array['docid'];
	}

	/**
	 *	getName - get the name of this document.
	 *
	 *	@return string	The name of this document.
	 */
	function getName() {
		return $this->data_array['title'];
	}

	/**
	 *	getDescription - the description of this document.
	 *
	 *	@return string	The description.
	 */
	function getDescription() {
		return $this->data_array['description'];
	}

	/**
	 *	isURL - whether this document is a URL and not a local file.
	 *
	 *	@return	boolean	is_url.
	 */
	function isURL() {
		return ($this->data_array['filetype'] == 'URL');
	}
	
	/**
	 *	isText - whether this document is a text document and not a binary one.
	 *
	 *	@return	boolean	is_text.
	 */
	function isText() {
		$doctype = $this->data_array['filetype'];
		if (preg_match('/text/i',$doctype)) { // text plain, text html, text x-p�tch, etc
			return true;
		}	
		return false;
	}
	
	/**
	 *	isHtml - whether this document is a html document.
	 *
	 *	@return	boolean	is_html.
	 */
	function isHtml() {
		$doctype = $this->data_array['filetype'];
		if (preg_match('/html/i',$doctype)) {
			return true;
		}	
		return false;
	}	

	/**
	 *	isPublic - whether this document is available to the general public.
	 *
	 *	@return	boolean	is_public.
	 */
	function isPublic() {
		return (($this->data_array['stateid'] == 1) ? true  : false);
	}

	/**
	 *	getStateID - get this stateid.
	 *
	 *	@return	int	The stateid.
	 */
	function getStateID() {
		return $this->data_array['stateid'];
	}

	/**
	 *	getStateName - the statename of this document.
	 *
	 *	@return string	The statename.
	 */
	function getStateName() {
		return $this->data_array['state_name'];
	}

	/**
	 *	getLanguageID - get this language_id.
	 *
	 *	@return	int	The language_id.
	 */
	function getLanguageID() {
		return $this->data_array['language_id'];
	}

	/**
	 *	getLanguageName - the language_name of this document.
	 *
	 *	@return string	The language_name.
	 */
	function getLanguageName() {
		return $this->data_array['language_name'];
	}

	/**
	 *	getDocGroupID - get this doc_group_id.
	 *
	 *	@return	int	The doc_group_id.
	 */
	function getDocGroupID() {
		return $this->data_array['doc_group'];
	}

	/**
	 *	getDocGroupName - the doc_group_name of this document.
	 *
	 *	@return string	The docgroupname.
	 */
	function getDocGroupName() {
		return $this->data_array['group_name'];
	}

	/**
	 *	getCreatorID - get this creator's user_id.
	 *
	 *	@return	int	The user_id.
	 */
	function getCreatorID() {
		return $this->data_array['created_by'];
	}

	/**
	 *	getCreatorUserName - the unix name of the person who created this document.
	 *
	 *	@return string	The unix name of the creator.
	 */
	function getCreatorUserName() {
		return $this->data_array['user_name'];
	}

	/**
	 *	getCreatorRealName - the real name of the person who created this document.
	 *
	 *	@return string	The real name of the creator.
	 */
	function getCreatorRealName() {
		return $this->data_array['realname'];
	}

	/**
	 *	getCreatorEmail - the email of the person who created this document.
	 *
	 *	@return string	The email of the creator.
	 */
	function getCreatorEmail() {
		return $this->data_array['email'];
	}

	/**
	 *	getFileName - the filename of this document.
	 *
	 *	@return string	The filename.
	 */
	function getFileName() {
		return $this->data_array['filename'];
	}

	/**
	 *	getFileType - the filetype of this document.
	 *
	 *	@return string	The filetype.
	 */
	function getFileType() {
		return $this->data_array['filetype'];
	}

	/**
	 *	getFileData - the filedata of this document.
	 *
	 *	@return string	The filedata.
	 */
	function getFileData() {
		//
		//	Because this could be a large string, we only fetch if we actually need it
		//
		$res=db_query("SELECT data FROM doc_data WHERE docid='".$this->getID()."'");
		return base64_decode(db_result($res,0,'data'));
	}
	
	/**
	* getFileSize - Return the size of the document
	*
	* @return	int	The file size
	*/
	function getFileSize() {
		return $this->data_array['filesize'];
	}
	/**
	 *	getUpdated - get the time this document was updated.
	 *
	 *	@return int	The epoch date this document was updated.
	 */
	function getUpdated() {
		return $this->data_array['updatedate'];
	}

	/**
	 *	getCreated - get the time this document was created.
	 *
	 *	@return int	The epoch date this document was created.
	 */
	function getCreated() {
		return $this->data_array['createdate'];
	}

	/**
	 *	update - use this function to update an existing entry in the database.
	 *
	 *	@param	string	The filename of this document. Can be a URL.
	 *	@param	string	The filetype of this document. If filename is URL, this should be 'URL';
	 *	@param	string	The contents of this document (should be addslashes()'d before entry).
	 *	@param	int	The doc_group id of the doc_groups table.
	 *	@param	string	The title of this document.
	 *	@param	int	The language id of the supported_languages table.
	 *	@param	string	The description of this document.
	 *	@param	int	The state id of the doc_states table.
	 *	@return	boolean	success.
	 */
	function update($filename,$filetype,$data,$doc_group,$title,$language_id,$description,$stateid) {
		if (strlen($title) < 5) {
			$this->setError(_('Title Must Be At Least 5 Characters'));
			return false;
		}
		if (strlen($description) < 10) {
			$this->setError(_('Document Description Must Be At Least 10 Characters'));
			return false;
		}

		$perm =& $this->Group->getPermission( session_get_user() );

		if (!$perm || !is_object($perm) || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}
		if ($data) {
			$filesize = strlen($data);
			$datastr="data='". base64_encode(stripslashes($data)) ."', filesize='".$filesize."',";
		}

		$res=db_query("UPDATE doc_data SET
			title='". htmlspecialchars($title) ."',
			description='". htmlspecialchars($description) ."',
			stateid='$stateid',
			doc_group='$doc_group',
			filetype='$filetype',
			filename='$filename',
			$datastr
			language_id='$language_id',
			updatedate='". time() ."'
			WHERE group_id='".$this->Group->getID()."'
			AND docid='".$this->getID()."'");

		if (!$res || db_affected_rows($res) < 1) {
			$this->setOnUpdateError(db_error());
			return false;
		}
		$this->sendNotice(false);
		return true;
	}

	/**
	*   sendNotice - Notifies of document submissions
	*/
	function sendNotice ($new=true) {
		$BCC = $this->Group->getDocEmailAddress();
		if (strlen($BCC) > 0) {
			$subject = '['.$this->Group->getPublicName().'] New document - '.$this->getName();
			$body = "Project: ".$this->Group->getPublicName()."\n";
			$body .= "Group: ".$groupname."\n";
			$body .= "Document title: ".$this->getName()."\n";
			$body .= "Document description: ".util_unconvert_htmlspecialchars( $this->getDescription() )."\n";
			$body .= "Submitter: ".$this->getCreatorRealName()." (".$this->getCreatorUserName().") \n";
			$body .= "\n\n-------------------------------------------------------".
				"\nFor more info, visit:".
				"\n\n" . util_make_url('/docman/index.php?group_id='.$this->Group->getID());

			util_send_message('',$subject,$body,'',$BCC);
		}

		return true;
	}
	
	function delete() {
		$perm =& $this->Group->getPermission( session_get_user() );
		if (!$perm || !is_object($perm) || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}
		
		$sql = 'DELETE FROM doc_data WHERE docid='.$this->getID();
		$result = db_query($sql);
		if (!$result) {
			$this->setError('Error Deleting Document: '.db_error());
			db_rollback();
			return false;
		}
		
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
