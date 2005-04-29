<?php
/**
 * SOAP Tracker Include - this file contains wrapper functions for the SOAP interface
 *
 * Copyright 2004 (c) GForge, LLC
 * http://gforge.org
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once('common/include/Error.class');
require_once('common/tracker/ArtifactType.class');
require_once('common/tracker/Artifact.class');
require_once('common/tracker/ArtifactFactory.class');
require_once('common/tracker/ArtifactTypeFactory.class');
require_once('common/tracker/Artifacts.class');
require_once('common/tracker/ArtifactFile.class');
require_once('common/tracker/ArtifactMessage.class');

//
//	ArtifactType
//
$server->wsdl->addComplexType(
	'ArtifactType',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'group_artifact_id' => array('name'=>'group_artifact_id', 'type' => 'xsd:int'),
	'group_id' => array('name'=>'group_id', 'type' => 'xsd:int'),
	'name' => array('name'=>'name', 'type' => 'xsd:string'),
	'description' => array('name'=>'description', 'type' => 'xsd:string'),
	'is_public' => array('name'=>'is_public', 'type' => 'xsd:int'),
	'allow_anon' => array('name'=>'allow_anon', 'type' => 'xsd:int'),
	'due_period' => array('name'=>'due_period', 'type' => 'xsd:int'),
	'datatype' => array('name'=>'datatype', 'type' => 'xsd:int'),
	'status_timeout' => array('name'=>'status_timeout', 'type' => 'xsd:int')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfArtifactType',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactType[]')),
	'tns:ArtifactType');

$server->register(
	'getArtifactTypes',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:int'),
	array('getArtifactTypesResponse'=>'tns:ArrayOfArtifactType'),
	$uri,
	$uri.'#getArtifactTypes','rpc','encoded'
);
// 
// Artifact Extra Fields 
// By remo on 08-Mar-2005

$server->wsdl->addComplexType(
	'ArtifactExtraFields',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'extra_field_id' => array('name'=>'extra_field_id', 'type' => 'xsd:int'),
	'field_data' => array('name'=>'field_data', 'type' => 'xsd:string')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfArtifactExtraFields',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactExtraFields[]')),
	'tns:ArtifactExtraFields'
);


//
//	Artifacts
//
$server->wsdl->addComplexType(
	'Artifact',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'artifact_id' => array('name'=>'artifact_id', 'type' => 'xsd:int'),
	'group_artifact_id' => array('name'=>'group_artifact_id', 'type' => 'xsd:int'),
	'status_id' => array('name'=>'status_id', 'type' => 'xsd:int'),
	'priority' => array('name'=>'priority', 'type' => 'xsd:int'),
	'submitted_by' => array('name'=>'submitted_by', 'type' => 'xsd:int'),
	'assigned_to' => array('name'=>'assigned_to', 'type' => 'xsd:int'),
	'open_date' => array('name'=>'open_date', 'type' => 'xsd:int'),
	'close_date' => array('name'=>'close_date', 'type' => 'xsd:int'),
	'summary' => array('name'=>'summary', 'type' => 'xsd:string'),
	'details' => array('name'=>'details', 'type' => 'xsd:string'),
	'extra_fields'=>array('name'=>'extra_fields', 'type' => 'tns:ArrayOfArtifactExtraFields')
	)
);
//ArrayOfArtifactExtraFields
$server->wsdl->addComplexType(
	'ArrayOfArtifact',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Artifact[]')),
	'tns:Artifact'
);

//getArtifact
$server->register(
	'getArtifacts',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_artifact_id'=>'xsd:int',
		'assigned_to'=>'xsd:int',
		'status'=>'xsd:int'),
	array('getArtifactsResponse'=>'tns:ArrayOfArtifact'),
	$uri,$uri.'#getArtifacts','rpc','encoded');


//addArtifact
$server->register(
	'addArtifact',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_artifact_id'=>'xsd:int',
		'status_id'=>'xsd:int',
		'priority'=>'xsd:int',
		'assigned_to'=>'xsd:int',
		'summary'=>'xsd:string',
		'details'=>'xsd:string',
		'extra_fields'=>'tns:ArrayOfArtifactExtraFields'
	),
	array('addArtifactResponse'=>'xsd:int'),
	$uri,$uri.'#addArtifact','rpc','encoded'
);


//updateArtifact
$server->register(
	'updateArtifact',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_artifact_id'=>'xsd:int',
		'artifact_id'=>'xsd:int',
		'status_id'=>'xsd:int',
		'priority'=>'xsd:int',
		'assigned_to'=>'xsd:int',
		'summary'=>'xsd:string',
		'details'=>'xsd:string',
		'extra_fields'=>'tns:ArrayOfArtifactExtraFields'
	),
	array('addArtifactResponse'=>'xsd:int'),
	$uri,$uri.'#updateArtifact','rpc','encoded'
);

//
//	ArtifactFile
//
$server->wsdl->addComplexType(
	'ArtifactFile',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'id' => array('name'=>'id', 'type' => 'xsd:int'),
	'artifact_id' => array('name'=>'artifact_id', 'type' => 'xsd:int'),
	'description' => array('name'=>'description', 'type' => 'xsd:string'),
	'filesize' => array('name'=>'filesize', 'type' => 'xsd:int'),
	'filetype' => array('name'=>'filetype', 'type' => 'xsd:string'),
	'adddate' => array('name'=>'adddate', 'type' => 'xsd:int'),
	'submitted_by' => array('name'=>'submitted_by', 'type' => 'xsd:int')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfArtifactFile',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactFile[]')),
	'tns:ArtifactFile'
);

$server->register(
	'getArtifactFiles',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:int','group_artifact_id'=>'xsd:int','artifact_id'=>'xsd:int'),
	array('getArtifactFilesResponse'=>'tns:ArrayOfArtifactFile'),
	$uri,$uri.'#getArtifactFiles','rpc','encoded'
);

//TODO - FINISH ADD FILE
$server->register(
	'addArtifactFile',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:int','group_artifact_id'=>'xsd:int','artifact_id'=>'xsd:int','file_path'=>'xsd:string','description'=>'xsd:string','filename'=>'xsd:string','filetype'=>'xsd:string'),
	array('addArtifactFileResponse'=>'xsd:int'),
	$uri,$uri.'#addArtifactFile','rpc','encoded'
);


//
//	ArtifactMessage
//
$server->wsdl->addComplexType(
	'ArtifactMessage',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'id' => array('name'=>'id', 'type' => 'xsd:int'),
	'artifact_id' => array('name'=>'artifact_id', 'type' => 'xsd:int'),
	'body' => array('name'=>'body', 'type' => 'xsd:string'),
	'adddate' => array('name'=>'adddate', 'type' => 'xsd:int'),
	'user_id' => array('name'=>'user_id', 'type' => 'xsd:int')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfArtifactMessage',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactMessage[]')),
	'tns:ArtifactMessage'
);

$server->register(
	'getArtifactMessages',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:int','group_artifact_id'=>'xsd:int','artifact_id'=>'xsd:int'),
	array('getArtifactMessagesResponse'=>'tns:ArrayOfArtifactMessage'),
	$uri,$uri.'#getArtifactMessages','rpc','encoded'
);

//add
$server->register(
	'addArtifactMessage',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:int','group_artifact_id'=>'xsd:int','artifact_id'=>'xsd:int','body'=>'xsd:string'),
	array('addArtifactMessageResponse'=>'xsd:int'),
	$uri,$uri.'#addArtifactMessage','rpc','encoded'
);

//
//	ArtifactTechnician
//
//	Array of Users
//
$server->register(
	'getArtifactTechnicians',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:int','group_artifact_id'=>'xsd:int'),
	array('getArtifactTechniciansResponse'=>'tns:ArrayOfUser'),
	$uri,$uri.'#getArtifactTechnicians','rpc','encoded'
);


//
//	getArtifactTypes
//
function &getArtifactTypes($session_ser,$group_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getArtifactTypes','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifactTypes','$grp->getErrorMessage()',$grp->getErrorMessage());
	}

	$atf = new ArtifactTypeFactory($grp);
	if (!$atf || !is_object($atf)) {
		return new soap_fault ('','getArtifactTypes','Could Not Get ArtifactTypeFactory','Could Not Get ArtifactTypeFactory');
	} elseif ($atf->isError()) {
		return new soap_fault ('','getArtifactTypes',$atf->getErrorMessage(),$atf->getErrorMessage());
	}

	return artifacttype_to_soap($atf->getArtifactTypes());
}

//
//	convert array of artifact types to soap data structure
//
function artifacttype_to_soap($at_arr) {
	$return = array();

	for ($i=0; $i<count($at_arr); $i++) {
		if ($at_arr[$i]->isError()) {
			//skip if error
		} else {
			$return[]=array(
				'group_artifact_id'=>$at_arr[$i]->data_array['group_artifact_id'],
				'group_id'=>$at_arr[$i]->data_array['group_id'],
				'name'=>$at_arr[$i]->data_array['name'],
				'description'=>$at_arr[$i]->data_array['description'],
				'is_public'=>$at_arr[$i]->data_array['is_public'],
				'allow_anon'=>$at_arr[$i]->data_array['allow_anon'],
				'due_period'=>$at_arr[$i]->data_array['due_period'],
				'datatype'=>$at_arr[$i]->data_array['datatype'],
				'status_timeout'=>$at_arr[$i]->data_array['status_timeout']
			);
		}
	}
	return $return;
}

//Arrange the ExtraFields
function arrangeExtraFields($extra_fields) {
	$efields=array();
	$fieldsdata=array();
	if (is_array($extra_fields)) {
		while(list($eky,)=each($extra_fields)) {
			$efields=$extra_fields[$eky];
			$fieldsdata[$efields['extra_field_id']]=$efields['field_data'];
		}
	}
	return $fieldsdata;
}

//
//	addArtifact
//

function &addArtifact($session_ser,$group_id,$group_artifact_id,$status_id,
	$priority,$assigned_to,$summary,$details,$extra_fields) {

	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','addArtifact','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addArtifact',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','addArtifact','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','addArtifact',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new Artifact($at);
	if (!$a || !is_object($a)) {
		return new soap_fault ('','addArtifact','Could Not Get Artifact','Could Not Get Artifact');
	} elseif ($a->isError()) {
		return new soap_fault ('','addArtifact',$a->getErrorMessage(),$a->getErrorMessage());
	}
	$extra_flds=arrangeExtraFields($extra_fields);
//	if (!$a->create($artifact_group_id,$summary,$details,$assigned_to,$priority,$extra_flds)) {
	if (!$a->create($summary,$details,$assigned_to,$priority,$extra_flds)) {
		return new soap_fault ('','addArtifact',$a->getErrorMessage(),$a->getErrorMessage());
	} else {
		return $a->getID();
	}
}

//
//	Update Artifact
//
function &updateArtifact($session_ser,$group_id,$group_artifact_id,$artifact_id,$status_id,
	$priority,$assigned_to,$summary,$details,$new_artifact_type_id,$extra_fields) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','addArtifact','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addArtifact',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','addArtifact','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','addArtifact',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new Artifact($at,$artifact_id);
	if (!$a || !is_object($a)) {
		return new soap_fault ('','addArtifact','Could Not Get Artifact','Could Not Get Artifact');
	} elseif ($a->isError()) {
		return new soap_fault ('','addArtifact',$a->getErrorMessage(),$a->getErrorMessage());
	}
//NOT DONE - $new_artifact_type_id missing, extra_fields missing
	$extra_flds=arrangeExtraFields($extra_fields);
	if (!$a->update($priority,$status_id,$assigned_to,
		$summary,$canned_response,$details,$new_artifact_type_id,$extra_flds)) {
		return new soap_fault ('','addArtifact',$a->getErrorMessage(),$a->getErrorMessage());
	} else {
		return $a->getID();
	}
}

//
//	getArtifactTechnicians
//
function &getArtifactTechnicians($session_ser,$group_id,$group_artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getArtifactTechnicians','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifactTechnicians',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','getArtifactTechnicians','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','getArtifactTechnicians',$at->getErrorMessage(),$at->getErrorMessage());
	}

	return users_to_soap($at->getTechnicianObjects());
}

//
//	getArtifacts
//
function &getArtifacts($session_ser,$group_id,$group_artifact_id,$assigned_to,$status) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getArtifacts','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifacts',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','getArtifacts','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','getArtifacts',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$af = new ArtifactFactory($at);
	if (!$af || !is_object($af)) {
		return new soap_fault ('','getArtifacts','Could Not Get ArtifactFactory','Could Not Get ArtifactFactory');
	} elseif ($af->isError()) {
		return new soap_fault ('','getArtifacts',$af->getErrorMessage(),$af->getErrorMessage());
	}

//MAY HAVE TO CHANGE PARAMS TO FORCE RETRIEVAL OF RIGHT RECORDS - FOR INSTANCE $set='Custom'
//NEEDS TO BE TESTED
	$af->setup(0,'','',0,false,$assigned_to,$status);
	return artifacts_to_soap($af->getArtifacts());

}

//
//	Get artifact by ID
//
function getArtifact($session_ser,$group_id,$group_artifact_id,$artifact_id) {

}

//
//	convert array of artifacts to soap data structure
//
function artifacts_to_soap($at_arr) {
	$return = array();
	for ($i=0; $i<count($at_arr); $i++) {
		if ($at_arr[$i]->isError()) {
			//skip if error
		} else {
//NEEDS THOROUGH COMMENTS AND EXPLANATION
	//***********
	// Retrieving the artifact details
	//**checks whether there is any artifact details exists for this object, if not continue with next loop

			if(count($at_arr[$i]) < 1) { continue; }
			$flddata=array();
			$fldelementdata=array();
			$extrafieldsdata=array();
			$extrafieldsdata=$at_arr[$i]->getExtraFieldData();

			//********
			//** Retrieving the extra field data and the element data
			//** checks whether there is any extra fields data available for this artifact
			//** and checks for the extra element data for the multiselect and checkbox type
			if(is_array($extrafieldsdata) && count($extrafieldsdata)>0) {
				while(list($ky,$vl)=each($extrafieldsdata)) {
					$fldarr=array();
					if(is_array($extrafieldsdata[$ky])) {
						//** Retrieving the multiselect and checkbox type data element
						$fldarr=array('extra_field_id'=>$ky,'field_data'=>implode(",",$extrafieldsdata[$ky]));
					} else {
						//** Retrieving the extra field data
						$fldarr=array('extra_field_id'=>$ky,'field_data'=>$vl);
					}
					$flddata[]=$fldarr;
					unset($fldarr);
				}
			}
			$return[]=array(
				'artifact_id'=>$at_arr[$i]->data_array['artifact_id'],
				'group_artifact_id'=>$at_arr[$i]->data_array['group_artifact_id'],
				'status_id'=>$at_arr[$i]->data_array['status_id'],
				'priority'=>$at_arr[$i]->data_array['priority'],
				'submitted_by'=>$at_arr[$i]->data_array['submitted_by'],
				'assigned_to'=>$at_arr[$i]->data_array['assigned_to'],
				'open_date'=>$at_arr[$i]->data_array['open_date'],
				'close_date'=>$at_arr[$i]->data_array['close_date'],
				'summary'=>$at_arr[$i]->data_array['summary'],
				'details'=>$at_arr[$i]->data_array['details'],
				'extra_fields'=>$flddata
			);
		}
	}
	return $return;
}

//
//	getArtifactFiles
//
function &getArtifactFiles($session_ser,$group_id,$group_artifact_id,$artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getArtifactFiles','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifactFiles',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','getArtifactFiles','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','getArtifactFiles',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new Artifact($at,$artifact_id);
	if (!$a || !is_object($a)) {
		return new soap_fault ('','getArtifactFiles','Could Not Get Artifact','Could Not Get Artifact');
	} elseif ($a->isError()) {
		return new soap_fault ('','getArtifactFiles',$a->getErrorMessage(),$a->getErrorMessage());
	}

	return $a->getFiles();
}

//
//	convert array of artifact files to soap data structure
//
function artifactfiles_to_soap($at_arr) {
	for ($i=0; $i<count($at_arr); $i++) {
		if ($at_arr[$i]->isError()) {
			//skip if error
		} else {
//TODO FINISH
			$return[]=array(
				'id'=>$at_arr[$i]->data_array['id'],
				'group_artifact_id'=>$at_arr[$i]->data_array['group_artifact_id'],
				'resolution_name'=>$at_arr[$i]->data_array['resolution_name']
			);
		}
	}
	return $return;
}

//
//
//	addArtifactFile
// 
/*
function &addArtifactFile($session_ser,$group_id,$group_artifact_id,$artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','addArtifactFile','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addArtifactFile',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','addArtifactFile','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','addArtifactFile',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new Artifact($at,$artifact_id);
	if (!$a || !is_object($a) {
		return new soap_fault ('','addArtifactFile','Could Not Get Artifact','Could Not Get Artifact');
	} elseif ($a->isError()) {
		return new soap_fault ('','addArtifactFile',$a->getErrorMessage(),$a->getErrorMessage());
	}

	return $a->getFiles();
}
*/
//
//
//	getArtifactMessages
//
function &getArtifactMessages($session_ser,$group_id,$group_artifact_id,$artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getArtifactMessages','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifactMessages',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','getArtifactMessages','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','getArtifactMessages',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new Artifact($at,$artifact_id);
	if (!$a || !is_object($a)) {
		return new soap_fault ('','getArtifactMessages','Could Not Get Artifact','Could Not Get Artifact');
	} elseif ($a->isError()) {
		return new soap_fault ('','getArtifactMessages',$a->getErrorMessage(),$a->getErrorMessage());
	}

	return artifactmessages_to_soap($a->getMessageObjects());
}

//
//	convert array of artifact messages to soap data structure
//
function artifactmessages_to_soap($at_arr) {
	for ($i=0; $i<count($at_arr); $i++) {
		if ($at_arr[$i]->isError()) {
			//skip if error
		} else {
			$return[]=array(
				'id'=>$at_arr[$i]->data_array['id'],
				'artifact_id'=>$at_arr[$i]->data_array['artifact_id'],
				'body'=>$at_arr[$i]->data_array['body'],
				'adddate'=>$at_arr[$i]->data_array['adddate'],
				'user_id'=>$at_arr[$i]->data_array['user_id']
			);
		}
	}
	return $return;
}

//
//	addArtifactMessage
//
function &addArtifactMessage($session_ser,$group_id,$group_artifact_id,$artifact_id,$body) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','addArtifactMessage','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addArtifactMessage',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','addArtifactMessage','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','addArtifactMessage',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new Artifact($at,$artifact_id);
	if (!$a || !is_object($a)) {
		return new soap_fault ('','addArtifactMessage','Could Not Get Artifact','Could Not Get Artifact');
	} elseif ($a->isError()) {
		return new soap_fault ('','addArtifactMessage',$a->getErrorMessage(),$a->getErrorMessage());
	}

	$am = new ArtifactMessage($a);
	if (!$am || !is_object($am)) {
		return new soap_fault ('','addArtifactMessage','Could Not Get ArtifactMessage','Could Not Get ArtifactMessage');
	} elseif ($am->isError()) {
		return new soap_fault ('','addArtifactMessage',$am->getErrorMessage(),$am->getErrorMessage());
	}

	if (!$am->create($body)) {
		return new soap_fault ('','addArtifactMessage',$am->getErrorMessage(),$am->getErrorMessage());
	} else {
		return $am->getID();
	}
}

//*****************************
//By Remo on 24-03-2005
//for getProjectData() function

$server->wsdl->addComplexType(
	'ArtifactTypeData',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'ArtifactType' => array('name'=>'ArtifactType', 'type' => 'tns:ArtifactType'),
	'technicians' => array('name'=>'technicians', 'type' => 'tns:ArrayOfUser'),
	'artifacts' => array('name'=>'artifacts', 'type' => 'tns:ArrayOfArtifact')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfArtifactTypeData',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactTypeData[]')),
	'tns:ArtifactTypeData'
);

$server->wsdl->addComplexType(
	'GroupData',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'group' => array('name'=>'groups', 'type' => 'tns:Group'),
//THIS IS NOT RIGHT - IT WOULD PROBABLY BE ARRAY OF ARTIFACT_TYPE 
	'artifacttypes' => array('name'=>'artifacttypes', 'type' => 'tns:ArrayOfArtifactTypeData')
	)
);

$server->wsdl->addComplexType(
	'ProjectData',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:GroupData[]')),
	'tns:GroupData'
);

$server->register(
	'getProjectData',
	array('session_ser'=>'xsd:string'),
	array('getProjectDataResponse'=>'tns:ProjectData'),
	$uri,$uri.'#getProjectData','rpc','encoded');


//********************************************************************
//
// getProjectData function is used to retrieve the information like
// Group details and their related Artifact details, and extra field
// data and element details which is related to the artifact
//
//*******************************************************************

function getProjectData($session_ser) {
	continue_session($session_ser); 
	$uid =& user_getid(); // Get the user id
	if (!$uid) {
		return new soap_fault ('','getProjectData','Could Not Get User','Could Not Get User');
	}
	if($uid) {
		$usrobj=& session_get_user(); // Get the user object for the current session
		if (!$usrobj || !is_object($usrobj)) {
			return new soap_fault ('','getProjectData','Could Not Get User','Could Not Get User');
		} elseif ($usrobj->isError()) {
			return new soap_fault ('','getProjectData',$usrobj->getErrorMessage(),$usrobj->getErrorMessage());
		}
	}

//NEEDS LOTS OF COMMENTS THROUHOUT
	//*********
	//** Retrieve the group objects from the user object for the current session using $usrobj->getGroups() function

	$grpsobj=$usrobj->getGroups(); 
	reset($grpsobj);
	$return=array(); //initialize the return array

	//*******
	// Loop through the group array object
	// and retrive the ArtifactTypeFactory object from the group object
	while(list($gky,) = each($grpsobj)) {
		if (!is_object($grpsobj[$gky])) { 
			continue; 
		}
		if ($grpsobj[$gky]->isError()) {
		   return new soap_fault('','getProjectData',$grpsobj[$gky]->getErrorMessage(),$grpsobj[$gky]->getErrorMessage());
			//skip it if it had an error
		} else {						
			$grpobj=$grpsobj[$gky];
			$returnGroup=array();
			$atobjs=array();
			$atfobj = new ArtifactTypeFactory($grpobj); //Initialize the ArtifactTypeFactory object from group object
			if (!$atfobj || !is_object($atfobj)) {
				return new soap_fault ('','getProjectData','Could Not Get ArtifactTypeFactory','Could Not Get ArtifactTypeFactory');
			} elseif ($atfobj->isError()) {
				return new soap_fault ('','getProjectData',$atfobj->getErrorMessage(),$atf->getErrorMessage());
			}
			//********
			// Retreive the ArtifactType object from the ArtifactTypeFactory object
			$atobjs=$atfobj->getArtifactTypes();
			if(!$atobjs) { continue; }
			$j=0;
			reset($atobjs);
			$returnATdata=array();
			while(list($atky1, )=each($atobjs)) {
				$returnTechnicians=array();
				$tmpartifactlist=array();
				$i=$atky1;
				if ($atobjs[$i]->isError()) {	
				   	return new soap_fault('','getProjectData',$atobjs[$i]->getErrorMessage(),$atobjs[$i]->getErrorMessage());
				} else {
					$afobj = new ArtifactFactory($atobjs[$i]);
					if (!$afobj || !is_object($afobj)) {
						return new soap_fault ('','getProjectData','Could Not Get ArtifactFactory','Could Not Get ArtifactFactory');
					} elseif ($afobj->isError()) {
						return new soap_fault ('','getProjectData',$afobj->getErrorMessage(),$afobj->getErrorMessage());
					}		
					$afobj->setup(0,'','',0,false,false,false);
					$tmpartifactlist=$afobj->getArtifacts();
					//$returnArtifacts=array();
					$returnTechnicians=users_to_soap($atobjs[$i]->getTechnicianObjects());
					$returnArtifacts=artifacts_to_soap($tmpartifactlist);

					$returnArtifactType=array(
						'group_artifact_id'=>$atobjs[$i]->data_array['group_artifact_id'],
						'group_id'=>$atobjs[$i]->data_array['group_id'],
						'name'=>$atobjs[$i]->data_array['name'],
						'description'=>$atobjs[$i]->data_array['description'],
						'is_public'=>$atobjs[$i]->data_array['is_public'],
						'allow_anon'=>$atobjs[$i]->data_array['allow_anon'],
						'due_period'=>$atobjs[$i]->data_array['due_period'],
						'datatype'=>$atobjs[$i]->data_array['datatype'],
			       			'status_timeout'=>$atobjs[$i]->data_array['status_timeout']);
					$returnATdata[] = array(
						'ArtifactType' => $returnArtifactType,
						'technicians' => $returnTechnicians,
						'artifacts' => $returnArtifacts);
					$returnTechnicians=array();
					$returnArtifacts=array();
					$returnArtifactType=array();
				}
			}
		
			//mail("remo@wcogs.com","getProjectData-ArtifactType",count($artifact_list1));
			//$artifact_list1=$tmpartifactlist;
			//$returnArtifacts=artifacts_to_soap($artifact_list1);
			
			$returnGroup = array('group_id'=>$grpobj->data_array['group_id'],
				'group_name'=>$grpobj->data_array['group_name'],
				'homepage'=>$grpobj->data_array['homepage'],
				'is_public'=>$grpobj->data_array['is_public'],
				'status'=>$grpobj->data_array['status'],
				'unix_group_name'=>$grpobj->data_array['unix_group_name'],
				'short_description'=>$grpobj->data_array['short_description'],
				'scm_box'=>$grpobj->data_array['scm_box'],
				'register_time'=>$grpobj->data_array['register_time']);

			$return[] = array(
				'group' => $returnGroup,
				'artifacttypes' => $returnATdata
			);
			$returnATdata=array();
		}
	}
	//$ar = array(array("group"=>array(),"artifacttypes"=>array(array("ArtifactType"=>array(),"artifacts"=>array()))));
	//return $ar;
	return $return;
}

?>
