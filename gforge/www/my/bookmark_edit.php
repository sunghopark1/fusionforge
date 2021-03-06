<?php
/**
 * SourceForge User's bookmark editing Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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


require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'include/bookmarks.php';

$bookmark_id = getIntFromRequest('bookmark_id');
if (!$bookmark_id) {
	exit_missing_param();
}

if (getStringFromRequest('submit')) {
	$bookmark_url = getStringFromRequest('bookmark_url');
	$bookmark_title = getStringFromRequest('bookmark_title');

	if ($bookmark_url && $bookmark_title && bookmark_edit($bookmark_id, $bookmark_url, $bookmark_title)) {
		$feedback = _('Bookmark Updated');
	} else {
		$feedback = _('Failed to update bookmark.');
	}
}

site_user_header(array('title'=>_('Edit Bookmark')));

$result = db_query("SELECT * from user_bookmarks where "
	. "bookmark_id='".$bookmark_id."' and user_id='".user_getid()."'");
if ($result) {
	$bookmark_url = db_result($result,0,'bookmark_url');
	$bookmark_title = db_result($result,0,'bookmark_title');
}
?>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="bookmark_id" value="<?php echo $bookmark_id; ?>" />
<p><?php echo _('Bookmark URL') ?>:<br />
<input type="text" name="bookmark_url" value="<?php echo $bookmark_url; ?>" />
</p>
<p><?php echo _('Bookmark Title') ?>:<br />
<input type="text" name="bookmark_title" value="<?php echo $bookmark_title; ?>" />
</p>
<p><input type="submit" name="submit" value=" <?php echo _('Submit') ?> " /></p>
</form>
<?php

print "<p><a href=\"/my/\">"._('Return')."</a></p>";

site_user_footer(array());

?>
