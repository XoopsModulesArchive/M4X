<?php

declare(strict_types=1);

// $Id: config.php,v 1.21.8.3 2004/10/06 00:03:31 stronk7 Exp $
// config.php - allows admin to edit all configuration variables

require_once '../config.php';

if ($site = get_site()) {   // If false then this is a new installation
    require_login();

    if (!isadmin()) {
        error('Only the admin can use this page');
    }
}

/// This is to overcome the "insecure forms paradox"
if (isset($secureforms) and 0 == $secureforms) {
    $match = 'nomatch';
} else {
    $match = '';
}

/// If data submitted, then process and store.

if ($config = data_submitted($match)) {
    if (!empty($USER->id)) {             // Additional identity check
        if (!confirm_sesskey()) {
            error(get_string('confirmsesskeybad', 'error'));
        }
    }

    validate_form($config, $err);

    if (0 == count($err)) {
        print_header();

        foreach ($config as $name => $value) {
            if ('sessioncookie' == $name) {
                $value = eregi_replace('[^a-zA-Z]', '', $value);
            }

            unset($conf);

            $conf->name = $name;

            $conf->value = $value;

            if ($current = get_record('config', 'name', $name)) {
                $conf->id = $current->id;

                if (!update_record('config', $conf)) {
                    notify("Could not update $name to $value");
                }
            } else {
                if (!insert_record('config', $conf)) {
                    notify("Error: could not add new variable $name !");
                }
            }
        }

        redirect('index.php', get_string('changessaved'), 1);

        exit;
    }

    foreach ($err as $key => $value) {
        $focus = "form.$key";
    }
}

/// Otherwise fill and print the form.

if (empty($config)) {
    $config = $CFG;

    if (!$config->locale = get_field('config', 'value', 'name', 'locale')) {
        $config->locale = $CFG->lang;
    }
}
if (empty($focus)) {
    $focus = '';
}

$stradmin = get_string('administration');
$strconfiguration = get_string('configuration');
$strconfigvariables = get_string('configvariables');

if ($site) {
    print_header(
        "$site->shortname: $strconfigvariables",
        $site->fullname,
        "<a href=\"index.php\">$stradmin</a> -> " . "<a href=\"configure.php\">$strconfiguration</a> -> $strconfigvariables",
        $focus
    );

    print_heading($strconfigvariables);
} else {
    print_header();

    print_heading($strconfigvariables);

    print_simple_box(get_string('configintro'), 'center', '50%');

    echo '<br>';
}

$sesskey = !empty($USER->id) ? $USER->sesskey : '';

print_simple_box_start('center', '', (string)$THEME->cellheading);
include 'config.html';
print_simple_box_end();

/// Lock some options

$httpsurl = str_replace('http://', 'https://', $CFG->wwwroot);
if ($httpsurl != $CFG->wwwroot) {
    if ((false === ($fh = @fopen($httpsurl, 'rb'))) and (0 == $config->loginhttps)) {
        echo '<script>' . "\n";

        echo '<!--' . "\n";

        echo "eval('document.form.loginhttps.disabled=true');\n";

        echo '-->' . "\n";

        echo '</script>' . "\n";
    }
}

if ($site) {
    print_footer();

    //--------------------------------------------

    // MOODLE4XOOPS - J. BAUDIN

    //--------------------------------------------

    require_once "$CFG->dirroot/footer.php";

    //--------------------------------------------
}

exit;

/// Functions /////////////////////////////////////////////////////////////////

function validate_form(&$form, &$err)
{
    // Currently no checks are needed ...

    return true;
}
?>
