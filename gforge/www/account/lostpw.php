<?php
/**
 * Request recovery of the lost password
 *
 * This page sends confirmation email with link to reset password
 * for account.
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
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


require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}

	$loginname = getStringFromRequest('loginname');

	$u = user_get_object_by_name($loginname);

	if (!$u || !is_object($u)){
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('Invalid user'),_('That user does not exist.'));
	}

	// First, we need to create new confirm hash

	$confirm_hash = md5($session_hash . strval(time()) . strval(rand()));

	$u->setNewEmailAndHash($u->getEmail(), $confirm_hash);
	if ($u->isError()) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error('Error',$u->getErrorMessage());
	} else {

		$message = stripcslashes(sprintf(_('Someone (presumably you) on the %1$s site requested a
password change through email verification. If this was not you,
ignore this message and nothing will happen.

If you requested this verification, visit the following URL
to change your password:

<%2$s>

 -- the %1$s staff
'), util_make_url ($GLOBALS['sys_name'], "/account/lostlogin.php?ch=_".$confirm_hash)));

		util_send_message($u->getEmail(),sprintf(_('%1$s Verification'), $GLOBALS['sys_name']),$message);

		$HTML->header(array('title'=>"Lost Password Confirmation"));

		echo '<p>'.printf(_('An email has been sent to the address you have on file. Follow the instructions in the email to change your account password.').'</p><p><a href="%1$s">Home</a>', util_make_url ('/')).'</p>';

		$HTML->footer(array());
		exit();
	}
}


$HTML->header(array('title'=>"Lost Account Password"));

echo _('<p>Hey... losing your password is serious business. It compromises the security of your account, your projects, and this site.</p><p>Clicking "Send Lost PW Hash" below will email a URL to the email address we have on file for you. In this URL is a 128-bit confirmation hash for your account. Visiting the URL will allow you to change your password online and login.</p>');
?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/> <p>
<?php echo _('Login name:'); ?>
<br />
<input type="text" name="loginname" />
<br />
<br />
<input type="submit" name="submit" value="<?php echo _('Send Lost PW Hash'); ?>" />
</p>
</form>

	<p><?php echo util_make_link ("/", _('Return')); ?></p>

<?php

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
