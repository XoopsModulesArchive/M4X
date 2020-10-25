<?php

declare(strict_types=1);

// $Id: site.php,v 1.30.2.5 2004/10/06 13:42:57 moodler Exp $

require_once '../config.php';

if ($site = get_site()) {
    require_login();

    if (!isadmin()) {
        error('You need to be admin to edit this page');
    }

    $site->format = 'social';   // override
}

/// If data submitted, then process and store.

if ($form = data_submitted()) {
    if (!empty($USER->id)) {             // Additional identity check
        if (!confirm_sesskey()) {
            error(get_string('confirmsesskeybad', 'error'));
        }
    }

    validate_form($form, $err);

    if (0 == count($err)) {
        set_config('frontpage', $form->frontpage);

        $form->timemodified = time();

        if ($form->id) {
            if (update_record('course', $form)) {
                redirect("$CFG->wwwroot/", get_string('changessaved'));
            } else {
                error("Serious Error! Could not update the site record! (id = $form->id)");
            }
        } else {
            // [pj] We are about to create the site, so let's add some blocks...

            // calendar_month is included as a Moodle feature advertisement ;-)

            require_once $CFG->dirroot . '/lib/blocklib.php';

            $form->blockinfo = blocks_get_default_blocks(null, BLOCKS_DEFAULT_SITE);

            if ($newid = insert_record('course', $form)) {
                $cat->name = get_string('miscellaneous');

                if (insert_record('course_categories', $cat)) {
                    redirect("$CFG->wwwroot/$CFG->admin/index.php", get_string('changessaved'), 1);
                } else {
                    error('Serious Error! Could not set up a default course category!');
                }
            } else {
                error('Serious Error! Could not set up the site!');
            }
        }

        die;
    }

    foreach ($err as $key => $value) {
        $focus = "form.$key";
    }
}

/// Otherwise fill and print the form.

if ($site and empty($form)) {
    $form = $site;

    $course = $site;

    $firsttime = false;
} else {
    $form->fullname = '';

    $form->shortname = '';

    $form->summary = '';

    $form->newsitems = 3;

    $form->numsections = 0;

    $form->id = '';

    $form->category = 0;

    $form->format = 'social';

    $form->teacher = get_string('defaultcourseteacher');

    $form->teachers = get_string('defaultcourseteachers');

    $form->student = get_string('defaultcoursestudent');

    $form->students = get_string('defaultcoursestudents');

    $firsttime = true;
}

if (isset($CFG->frontpage)) {
    $form->frontpage = $CFG->frontpage;
} else {
    if ($form->newsitems > 0) {
        $form->frontpage = 0;
    } else {
        $form->frontpage = 1;
    }

    set_config('frontpage', $form->frontpage);
}

if (empty($focus)) {
    $focus = 'form.fullname';
}

$stradmin = get_string('administration');
$strconfiguration = get_string('configuration');
$strsitesettings = get_string('sitesettings');

if ($firsttime) {
    print_header();

    print_heading($strsitesettings);

    print_simple_box(get_string('configintrosite'), 'center', '50%');

    echo '<br>';
} else {
    print_header(
        "$site->shortname: $strsitesettings",
        (string)$site->fullname,
        "<a href=\"index.php\">$stradmin</a> -> " . "<a href=\"configure.php\">$strconfiguration</a> -> $strsitesettings",
        (string)$focus
    );

    print_heading($strsitesettings);
}

if (empty($USER->id)) {  // New undefined admin user
    $USER->htmleditor = true;

    $sesskey = '';
} else {
    $sesskey = $USER->sesskey;
}
$usehtmleditor = can_use_html_editor();
$defaultformat = FORMAT_HTML;

print_simple_box_start('center', '', (string)$THEME->cellheading);
include 'site.html';
print_simple_box_end();

if ($usehtmleditor) {
    use_html_editor();
}

if (!$firsttime) {
    print_footer();

    //--------------------------------------------

    // MOODLE4XOOPS - J. BAUDIN

    //--------------------------------------------

    require_once "$CFG->dirroot/footer.php";

    //--------------------------------------------
}

exit;

/// Functions /////////////////////////////////////////////////////////////////

function validate_form($form, &$err)
{
    if (empty($form->fullname)) {
        $err['fullname'] = get_string('missingsitename');
    }

    if (empty($form->shortname)) {
        $err['shortname'] = get_string('missingshortsitename');
    }
}
