<?php
/**
 * FusionForge plugin system
 *
 * Copyright 2002, Roland Mas
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

class Plugin extends Error {
	var $name ;
	var $hooks ;

	/**
	 * Plugin() - constructor
	 *
	 */
	function Plugin () {
		$this->Error() ;
		$this->name = false ;
		$this->hooks = array () ;
	}

	/**
	 * GetHooks() - get list of hooks to subscribe to
	 *
	 * @return List of strings
	 */
	function GetHooks () {
		return $this->hooks ;
	}

	/**
	 * GetName() - get plugin name
	 *
	 * @return the plugin name
	 */
	function GetName () {
		return $this->name ;
	}

	/**
	 * GetInstallDir() - get installation dir for the plugin
	 *
	 * @return the directory where the plugin should be linked.
	 */
	function GetInstallDir () {
		if (isset($this->installdir) && $this->installdir)
			return $this->installdir;
		else
			return 'plugins/'.$this->name ;
	}

	/**
	 * CallHook() - call a particular hook
	 *
	 * @param hookname - the "handle" of the hook
	 * @param params - array of parameters to pass the hook
	 */
	function CallHook ($hookname, $params) {
		return true ; 
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
