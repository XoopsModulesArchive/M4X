<?php

declare(strict_types=1);

// $Id: module.php,v 1.5.8.2 2004/10/03 09:44:57 stronk7 Exp $
// module.php - allows admin to edit all local configuration variables for a module

require_once '../config.php';

require_login();

if (!isadmin()) {
    error('Only an admin can use this page');
}

if (!$site = get_site()) {
    error("Site isn't defined!");
}

if (!confirm_sesskey()) {
    error(get_string('confirmsesskeybad', 'error'));
}

/// If data submitted, then process and store.

if ($config = data_submitted()) {
    print_header();

    foreach ($config as $name => $value) {
        set_config($name, $value);
    }

    redirect("$CFG->wwwroot/$CFG->admin/modules.php", get_string('changessaved'), 1);

    exit;
}

/// Otherwise print the form.

require_variable($module);

$module = clean_filename($module);
require_once "$CFG->dirroot/mod/$module/lib.php";

$stradmin = get_string('administration');
$strconfiguration = get_string('configuration');
$strmanagemodules = get_string('managemodules');
$strmodulename = get_string('modulename', $module);

print_header(
    "$site->shortname: $strmodulename: $strconfiguration",
    $site->fullname,
    "<a href=\"index.php\">$stradmin</a> -> " . "<a href=\"configure.php\">$strconfiguration</a> -> " . "<a href=\"modules.php\">$strmanagemodules</a> -> $strmodulename"
);

print_heading($strmodulename);

print_simple_box('<center>' . get_string('configwarning') . '</center>', 'center', '50%');
echo '<br>';

print_simple_box_start('center', '', (string)$THEME->cellheading);
include "$CFG->dirroot/mod/$module/config.html";
print_simple_box_end();

print_footer();
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
