<?php

declare(strict_types=1);

// $Id: block.php,v 1.1.8.1 2004/10/02 23:07:22 stronk7 Exp $

// block.php - allows admin to edit all local configuration variables for a block

require_once dirname(__DIR__) . '/config.php';
require_once $CFG->libdir . '/blocklib.php';

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

require_variable($_REQUEST['block']);
$blockid = (int)$_REQUEST['block'];

if (false === ($blockrecord = get_record('blocks', 'id', $blockid))) {
    error('This block does not exist');
}

$block = block_instance($blockrecord->name, null);
if (false === $block) {
    error('Problem in instantiating block object');
}

/// If data submitted, then process and store.

if ($config = data_submitted()) {
    unset($config->block); // This will always be set if we have reached this point

    $block->handle_config($config);

    print_header();

    redirect("$CFG->wwwroot/$CFG->admin/blocks.php", get_string('changessaved'), 1);

    exit;
}

/// Otherwise print the form.

$stradmin = get_string('administration');
$strconfiguration = get_string('configuration');
$strmanageblocks = get_string('manageblocks');
$strblockname = $block->get_title();

print_header(
    $site->shortname . ': ' . $strblockname . ": $strconfiguration",
    $site->fullname,
    "<a href=\"index.php\">$stradmin</a> -> " . "<a href=\"configure.php\">$strconfiguration</a> -> " . "<a href=\"blocks.php\">$strmanageblocks</a> -> " . $strblockname
);

print_heading($strblockname);

print_simple_box('<center>' . get_string('configwarning') . '</center>', 'center', '50%');
echo '<br>';

$block->print_config();

print_footer();
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
