<?php // $Id: version.php,v 1.1.2.20 2011/09/23 16:38:21 mchurch Exp $

/**
 * Code fragment to define the version of adobeconnect
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author  Akinsaya Delamarre adelamarre@remote-learner.net
 * @version $Id: version.php,v 1.1.2.20 2011/09/23 16:38:21 mchurch Exp $
 * @package mod/adobeconnect
 */

$module->version  = 2011072100;  // The current module version (Date: YYYYMMDDXX)
$module->requires = 2007101509;  // Requires this Moodle version
$module->cron     = 0;           // Period for cron to check this module (secs)

?>