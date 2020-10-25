<?php

declare(strict_types=1);

// $Id: configure.php,v 1.7.2.3 2004/10/03 00:28:22 stronk7 Exp $

require_once '../config.php';

require_login();

if (!isadmin()) {
    error('Only admins can access this page');
}

if (!$site = get_site()) {
    redirect('index.php');
}

$stradministration = get_string('administration');
$strconfiguration = get_string('configuration');

print_header(
    "$site->shortname: $stradministration: $strconfiguration",
    (string)$site->fullname,
    "<a href=\"index.php\">$stradministration</a> -> $strconfiguration"
);

print_heading($strconfiguration);

$table->align = ['right', 'left'];

$table->data[] = [
    '<b><a href="config.php">' . get_string('configvariables') . '</a></b>',
    get_string('adminhelpconfigvariables'),
];
$table->data[] = [
    '<b><a href="site.php">' . get_string('sitesettings') . '</a></b>',
    get_string('adminhelpsitesettings'),
];
$table->data[] = [
    '<b><a href="../theme/index.php">' . get_string('themes') . '</a></b>',
    get_string('adminhelpthemes'),
];
$table->data[] = [
    '<b><a href="lang.php">' . get_string('language') . '</a></b>',
    get_string('adminhelplanguage'),
];
$table->data[] = [
    '<b><a href="modules.php">' . get_string('managemodules') . '</a></b>',
    get_string('adminhelpmanagemodules'),
];
$table->data[] = [
    '<b><a href="blocks.php">' . get_string('manageblocks') . '</a></b>',
    get_string('adminhelpmanageblocks'),
];
$table->data[] = [
    "<b><a href=\"filters.php?sesskey=$USER->sesskey\">" . get_string('managefilters') . '</a></b>',
    get_string('adminhelpmanagefilters'),
];
if (!isset($CFG->disablescheduledbackups)) {
    $table->data[] = [
        "<b><a href=\"backup.php?sesskey=$USER->sesskey\">" . get_string('backup') . '</a></b>',
        get_string('adminhelpbackup'),
    ];
}

$table->data[] = [
    "<b><a href=\"editor.php?sesskey=$USER->sesskey\">" . get_string('editorsettings') . '</a></b>',
    get_string('adminhelpeditorsettings'),
];

print_table($table);

print_footer($site);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
