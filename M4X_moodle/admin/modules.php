<?php

declare(strict_types=1);

// $Id: modules.php,v 1.8.4.2 2004/10/03 15:05:11 stronk7 Exp $
// Allows the admin to create, delete and rename course categories

require_once '../config.php';
require_once '../course/lib.php';

optional_variable($disable);
optional_variable($enable);
optional_variable($delete);
optional_variable($confirm);

require_login();

if (!isadmin()) {
    error('Only administrators can use this page!');
}

if (!$site = get_site()) {
    error("Site isn't defined!");
}

/// Print headings

$stradministration = get_string('administration');
$strconfiguration = get_string('configuration');
$strmanagemodules = get_string('managemodules');
$strdelete = get_string('delete');
$strversion = get_string('version');
$strhide = get_string('hide');
$strshow = get_string('show');
$strsettings = get_string('settings');
$stractivities = get_string('activities');
$stractivitymodule = get_string('activitymodule');

print_header(
    "$site->shortname: $strmanagemodules",
    (string)$site->fullname,
    "<a href=\"index.php\">$stradministration</a> -> " . "<a href=\"configure.php\">$strconfiguration</a> -> $strmanagemodules"
);

print_heading($strmanagemodules);

/// If data submitted, then process and store.

if (!empty($hide) and confirm_sesskey()) {
    if (!$module = get_record('modules', 'name', $hide)) {
        error("Module doesn't exist!");
    }

    set_field('modules', 'visible', '0', 'id', $module->id);            // Hide main module
    set_field('course_modules', 'visible', '0', 'module', $module->id); // Hide all related activity modules
}

if (!empty($show) and confirm_sesskey()) {
    if (!$module = get_record('modules', 'name', $show)) {
        error("Module doesn't exist!");
    }

    set_field('modules', 'visible', '1', 'id', $module->id);            // Show main module
    set_field('course_modules', 'visible', '1', 'module', $module->id); // Show all related activity modules
}

if (!empty($delete) and confirm_sesskey()) {
    $strmodulename = get_string('modulename', (string)$delete);

    if (empty($confirm)) {
        notice_yesno(
            get_string('moduledeleteconfirm', '', $strmodulename),
            "modules.php?delete=$delete&confirm=$delete&sesskey=$USER->sesskey",
            'modules.php'
        );

        print_footer();

        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------

        exit;
    }    // Delete everything!!

    if ('forum' == $delete) {
        error('You can not delete the forum module!!');
    }

    if (!$module = get_record('modules', 'name', $delete)) {
        error("Module doesn't exist!");
    }

    // OK, first delete all the relevant instances from all course sections

    if ($coursemods = get_records('course_modules', 'module', $module->id)) {
        foreach ($coursemods as $coursemod) {
            if (!delete_mod_from_section($coursemod->id, $coursemod->section)) {
                notify("Could not delete the $strmodulename with id = $coursemod->id from section $coursemod->section");
            }
        }
    }

    // Now delete all the course module records

    if (!delete_records('course_modules', 'module', $module->id)) {
        notify("Error occurred while deleting all $strmodulename records in course_modules table");
    }

    // Then delete all the logs

    if (!delete_records('log', 'module', $module->name)) {
        notify("Error occurred while deleting all $strmodulename records in log table");
    }

    // And log_display information

    if (!delete_records('log_display', 'module', $module->name)) {
        notify("Error occurred while deleting all $strmodulename records in log_display table");
    }

    // And the module entry itself

    if (!delete_records('modules', 'name', $module->name)) {
        notify("Error occurred while deleting the $strmodulename record from modules table");
    }

    // Then the tables themselves

    if ($tables = $db->Metatables()) {
        $prefix = $CFG->prefix . $module->name;

        foreach ($tables as $table) {
            if (0 === mb_strpos($table, $prefix)) {
                if (!execute_sql("DROP TABLE $table", false)) {
                    notify("ERROR: while trying to drop table $table");
                }
            }
        }
    }

    rebuild_course_cache();  // Because things have changed

    $a->module = $strmodulename;

    $a->directory = "$CFG->dirroot/mod/$delete";

    notice(get_string('moduledeletefiles', '', $a), 'modules.php');
}

/// Get and sort the existing modules

if (!$modules = get_records('modules')) {
    error('No modules found!!');        // Should never happen
}

foreach ($modules as $module) {
    $strmodulename = get_string('modulename', (string)$module->name);

    $modulebyname[$strmodulename] = $module;
}
ksort($modulebyname);

/// Print the table of all modules

if (empty($THEME->custompix)) {
    $pixpath = '../pix';

    $modpixpath = '../mod';
} else {
    $pixpath = "../theme/$CFG->theme/pix";

    $modpixpath = "../theme/$CFG->theme/pix/mod";
}

$table->head = [$stractivitymodule, $stractivities, $strversion, "$strhide/$strshow", $strdelete, $strsettings];
$table->align = ['LEFT', 'RIGHT', 'LEFT', 'CENTER', 'CENTER', 'CENTER'];
$table->wrap = ['NOWRAP', '', '', '', '', ''];
$table->size = ['100%', '10', '10', '10', '10', '12'];
$table->width = '100';

foreach ($modulebyname as $modulename => $module) {
    $icon = "<img src=\"$modpixpath/$module->name/icon.gif\" hspace=10 height=16 width=16 border=0>";

    $delete = "<a href=\"modules.php?delete=$module->name&sesskey=$USER->sesskey\">$strdelete</a>";

    if (file_exists("$CFG->dirroot/mod/$module->name/config.html")) {
        $settings = "<a href=\"module.php?module=$module->name&sesskey=$USER->sesskey\">$strsettings</a>";
    } else {
        $settings = '';
    }

    $count = count_records((string)$module->name);

    if ($module->visible) {
        $visible = "<a href=\"modules.php?hide=$module->name&sesskey=$USER->sesskey\" title=\"$strhide\">" . "<img src=\"$pixpath/i/hide.gif\" align=\"absmiddle\" height=16 width=16 border=0></a>";

        $class = '';
    } else {
        $visible = "<a href=\"modules.php?show=$module->name&sesskey=$USER->sesskey\" title=\"$strshow\">" . "<img src=\"$pixpath/i/show.gif\" align=\"absmiddle\" height=16 width=16 border=0></a>";

        $class = 'class="dimmed_text"';
    }

    if ('forum' == $module->name) {
        $delete = '';

        $visible = '';

        $class = '';
    }

    $table->data[] = ["<p $class>$icon $modulename</p>", $count, $module->version, $visible, $delete, $settings];
}
print_table($table);

echo '<br><br>';

print_footer();
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
