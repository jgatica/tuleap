<?php
//
// Copyright (c) Xerox Corporation, CodeX Team, 2001-2003. All rights reserved
//
// $Id$
//
//
//  Written for CodeX by Stephane Bouhet
//

$LANG->loadLanguageMsg('tracker/tracker');

if ( !user_isloggedin() ) {
	exit_not_logged_in();
	return;
}

if ( !$ath->userIsAdmin() ) {
	exit_permission_denied();
	return;
}

// Check if this tracker is valid (not deleted)
if ( !$ath->isValid() ) {
	exit_error($LANG->getText('global','error'),$LANG->getText('tracker_add','invalid'));
}

$ath->adminHeader(array('title'=>$LANG->getText('tracker_admin_field_usage','tracker_admin').$LANG->getText('tracker_admin_field_values_details','values_admin'),'help' => 'TrackerAdministration.html#TrackerFieldValuesManagement'));

echo '<H2>'.$LANG->getText('tracker_import_admin','tracker').' \'<a href="/tracker/admin/?group_id='.$group_id.'&atid='.$atid.'">'.$ath->getName().'</a>\' '.$LANG->getText('tracker_admin_field_values_details','values_admin').'</H2>';
$ath->displayFieldValuesEditList();

$ath->footer(array());

?>
