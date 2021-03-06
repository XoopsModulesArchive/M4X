<?php

declare(strict_types=1);

// $Id: blocks.php,v 1.6.4.2 2004/10/02 23:29:44 stronk7 Exp $

// Allows the admin to configure blocks (hide/show, delete and configure)

require_once dirname(__DIR__) . '/config.php';
require_once $CFG->libdir . '/blocklib.php';

optional_variable($_GET['hide']);
optional_variable($_GET['show']);
optional_variable($_GET['delete']);
optional_variable($_GET['confirm'], 0);
$delete = $_GET['delete']; // Dependency remover

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
$strmanageblocks = get_string('manageblocks');
$strdelete = get_string('delete');
$strversion = get_string('version');
$strhide = get_string('hide');
$strshow = get_string('show');
$strsettings = get_string('settings');
$strcourses = get_string('courses');
$strname = get_string('name');

print_header(
    "$site->shortname: $strmanageblocks",
    (string)$site->fullname,
    "<a href=\"index.php\">$stradministration</a> -> " . "<a href=\"configure.php\">$strconfiguration</a> -> $strmanageblocks"
);

print_heading($strmanageblocks);

/// If data submitted, then process and store.

if (!empty($_GET['hide']) and confirm_sesskey()) {
    if (!$block = get_record('blocks', 'id', $_GET['hide'])) {
        error("Block doesn't exist!");
    }

    set_field('blocks', 'visible', '0', 'id', $block->id);      // Hide block
}

if (!empty($_GET['show']) and confirm_sesskey()) {
    if (!$block = get_record('blocks', 'id', $_GET['show'])) {
        error("Block doesn't exist!");
    }

    set_field('blocks', 'visible', '1', 'id', $block->id);      // Show block
}

if (!empty($delete) and confirm_sesskey()) {
    if (!$block = get_record('blocks', 'id', $delete)) {
        error("Block doesn't exist!");
    }

    $blockobject = block_instance($block->name, $site);

    $strblockname = $blockobject->get_title();

    if (!$_GET['confirm']) {
        notice_yesno(
            get_string('blockdeleteconfirm', '', $strblockname),
            'blocks.php?delete=' . $block->id . '&amp;confirm=1&sesskey=' . $USER->sesskey,
            'blocks.php'
        );

        print_footer();

        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------

        exit;
    }

    // Delete block

    if (!delete_records('blocks', 'id', $block->id)) {
        notify("Error occurred while deleting the $strblockname record from blocks table");
    }

    blocks_update_every_block_by_id($block->id, 'delete');                        // Delete blocks in all courses by id

    // Then the tables themselves

    if ($tables = $db->Metatables()) {
        $prefix = $CFG->prefix . $block->name;

        foreach ($tables as $table) {
            if (0 === mb_strpos($table, $prefix)) {
                if (!execute_sql("DROP TABLE $table", false)) {
                    notify("ERROR: while trying to drop table $table");
                }
            }
        }
    }

    $a->block = $strblockname;

    $a->directory = $CFG->dirroot . '/blocks/' . $block->name;

    notice(get_string('blockdeletefiles', '', $a), 'blocks.php');
}

/// Main display starts here

/// Get and sort the existing blocks

if (false === ($blocks = get_records('blocks'))) {
    error('No blocks found!');  // Should never happen
}

foreach ($blocks as $block) {
    if (false === ($blockobject = block_instance($block->name, null))) {
        // Failed to load

        continue;
    }

    $blockbyname[$blockobject->get_title()] = $block->id;

    $blockobjects[$block->id] = $blockobject;
}

if (empty($blockbyname)) {
    error('One or more blocks are registered in the database, but they all failed to load!');
}

ksort($blockbyname);

/// Print the table of all blocks

if (empty($THEME->custompix)) {
    $pixpath = '../pix';

// [pj] This is not used anywhere, but I'm leaving it in for the future
    //$modpixpath = '../mod';
} else {
    $pixpath = '../theme/' . $CFG->theme . '/pix';

    // [pj] This is not used anywhere, but I'm leaving it in for the future
    //$modpixpath = '../theme/'.$CFG->theme.'/pix/mod';
}

$table->head = [$strname, $strcourses, $strversion, $strhide . '/' . $strshow, $strdelete, $strsettings];
$table->align = ['LEFT', 'RIGHT', 'LEFT', 'CENTER', 'CENTER', 'CENTER'];
$table->wrap = ['NOWRAP', '', '', '', '', ''];
$table->size = ['100%', '10', '10', '10', '10', '12'];
$table->width = '100';

foreach ($blockbyname as $blockname => $blockid) {
    // [pj] This is not used anywhere, but I'm leaving it in for the future

    //$icon = "<img src=\"$modpixpath/$block->name/icon.gif\" hspace=10 height=16 width=16 border=0>";

    $blockobject = $blockobjects[$blockid];

    $delete = '<a href="blocks.php?delete=' . $blockid . '&sesskey=' . $USER->sesskey . '">' . $strdelete . '</a>';

    $settings = ''; // By default, no configuration

    if ($blockobject->has_config()) {
        $settings = '<a href="block.php?block=' . $blockid . '&sesskey=' . $USER->sesskey . '">' . $strsettings . '</a>';
    }

    $count = blocks_get_courses_using_block_by_id($blockid);

    $class = ''; // Nothing fancy, by default

    if ($blocks[$blockid]->visible) {
        $visible = '<a href="blocks.php?hide=' . $blockid . '&sesskey=' . $USER->sesskey . '" title="' . $strhide . '">' . '<img src="' . $pixpath . '/i/hide.gif" style="height: 16px; width: 16px;"></a>';
    } else {
        $visible = '<a href="blocks.php?show=' . $blockid . '&sesskey=' . $USER->sesskey . '" title="' . $strshow . '">' . '<img src="' . $pixpath . '/i/show.gif" style="height: 16px; width: 16px;"></a>';

        $class = ' class="dimmed_text"'; // Leading space required!
    }

    $table->data[] = ['<p' . $class . '>' . $blockobject->get_title() . '</p>', $count, $blockobject->get_version(), $visible, $delete, $settings];
}
echo '<p>';
print_table($table);
echo '</p>';
print_footer();
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
