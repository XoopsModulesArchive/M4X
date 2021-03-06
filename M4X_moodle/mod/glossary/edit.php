<?php

declare(strict_types=1);

// $Id: edit.php,v 1.31.2.10 2004/10/01 09:46:08 stronk7 Exp $

require_once '../../config.php';
require_once 'lib.php';

global $CFG, $USER, $THEME;

require_variable($id);    // Course Module ID
optional_variable($e);    // EntryID
optional_variable($confirm, 0);    // proceed. Edit the edtry

optional_variable($mode);   // categories if by category?
optional_variable($hook);    // CategoryID

$mode = strip_tags(urldecode($mode));  //XSS
$hook = strip_tags(urldecode($hook));  //XSS

if (!$cm = get_record('course_modules', 'id', $id)) {
    error('Course Module ID was incorrect');
}

if (!$course = get_record('course', 'id', $cm->course)) {
    error('Course is misconfigured');
}

require_login($course->id);

if (isguest()) {
    error('Guests are not allowed to edit glossaries', $_SERVER['HTTP_REFERER']);
}

if (!$glossary = get_record('glossary', 'id', $cm->instance)) {
    error('Course module is incorrect');
}

if (!$glossary->studentcanpost && !isteacher($glossary->course)) {
    error("You can't add/edit entries to this glossary!");
}
if ($confirm) {
    $form = data_submitted();

    if (!isset($form->usedynalink)) {
        $form->usedynalink = 0;
    }

    if (!isset($form->casesensitive)) {
        $form->casesensitive = 0;
    }

    if (!isset($form->fullmatch)) {
        $form->fullmatch = 0;
    }

    $timenow = time();

    //$form->text = clean_text($form->text, $form->format);

    $newentry->course = $glossary->course;

    $newentry->glossaryid = $glossary->id;

    $newentry->concept = clean_text(trim($form->concept));

    $newentry->definition = $form->text;

    $newentry->format = $form->format;

    $newentry->usedynalink = $form->usedynalink;

    $newentry->casesensitive = $form->casesensitive;

    $newentry->fullmatch = $form->fullmatch;

    $newentry->timemodified = $timenow;

    $newentry->approved = 0;

    if ($glossary->defaultapproval or isteacher($course->id)) {
        $newentry->approved = 1;
    }

    if ('' == $form->concept or '' == trim($form->text)) {
        $errors = get_string('fillfields', 'glossary');

        $strglossary = get_string('modulename', 'glossary');

        $strglossaries = get_string('modulenameplural', 'glossary');

        $stredit = get_string('edit');

        if ($usehtmleditor = can_use_richtext_editor()) {
            $defaultformat = FORMAT_HTML;
        } else {
            $defaultformat = FORMAT_MOODLE;
        }

        print_header_simple(
            strip_tags((string)$glossary->name),
            '',
            "<A HREF=\"index.php?id=$course->id\">$strglossaries</A> ->
              <A HREF=\"view.php?id=$cm->id\">$glossary->name</A> -> $stredit",
            'form.text',
            '',
            true,
            '',
            navmenu($course, $cm)
        );

        print_heading($glossary->name);

        include 'edit.html';

        print_footer($course);

        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------

        die;
    }

    if ($e) {
        //We are updating an entry, so we compare current session user with

        //existing entry user to avoid some potential problems if secureforms=off

        //Perhaps too much security? Anyway thanks to skodak (Bug 1823)

        $old = get_record('glossary_entries', 'id', $e);

        $ineditperiod = ((time() - $old->timecreated < $CFG->maxeditingtime) || $glossary->editalways);

        if ((!$ineditperiod || $USER->id != $old->userid) and !isteacher($course->id) and $e) {
            if ($USER->id != $old->userid) {
                error("You can't edit other people's entries!");
            } elseif (!$ineditperiod) {
                error("You can't edit this. Time expired!");
            }

            die;
        }

        $newentry->id = $e;

        $permissiongranted = 1;

        if (!$glossary->allowduplicatedentries) {
            if ($dupentries = get_records('glossary_entries', 'UCASE(concept)', mb_strtoupper($newentry->concept))) {
                foreach ($dupentries as $curentry) {
                    if ($glossary->id == $curentry->glossaryid) {
                        if ($curentry->id != $e) {
                            $permissiongranted = 0;

                            break;
                        }
                    }
                }
            }
        }

        if ($permissiongranted) {
            $newentry->attachment = $_FILES['attachment'];

            if ($newfilename = glossary_add_attachment($newentry, $newentry->attachment)) {
                $newentry->attachment = $newfilename;
            } else {
                unset($newentry->attachment);
            }

            if (!update_record('glossary_entries', $newentry)) {
                error('Could not update your glossary');
            } else {
                add_to_log($course->id, 'glossary', 'update entry', "view.php?id=$cm->id&mode=entry&hook=$newentry->id", $newentry->id, $cm->id);
            }
        } else {
            error('Could not update this glossary entry because this concept already exist.');
        }
    } else {
        $newentry->userid = $USER->id;

        $newentry->timecreated = $timenow;

        $newentry->sourceglossaryid = 0;

        $newentry->teacherentry = isteacher($course->id);

        $permissiongranted = 1;

        if (!$glossary->allowduplicatedentries) {
            if ($dupentries = get_record('glossary_entries', 'UCASE(concept)', mb_strtoupper($newentry->concept), 'glossaryid', $glossary->id)) {
                $permissiongranted = 0;
            }
        }

        if ($permissiongranted) {
            if (!$newentry->id = insert_record('glossary_entries', $newentry)) {
                error('Could not insert this new entry');
            } else {
                $e = $newentry->id;

                $newentry->attachment = $_FILES['attachment'];

                if ($newfilename = glossary_add_attachment($newentry, $newentry->attachment)) {
                    $newentry->attachment = $newfilename;
                } else {
                    unset($newentry->attachment);
                }

                set_field('glossary_entries', 'attachment', $newfilename, 'id', $newentry->id);

                add_to_log($course->id, 'glossary', 'add entry', "view.php?id=$cm->id&mode=entry&hook=$newentry->id", $newentry->id, $cm->id);
            }
        } else {
            error('Could not insert this glossary entry because this concept already exist.');
        }
    }

    delete_records('glossary_entries_categories', 'entryid', $e);

    delete_records('glossary_alias', 'entryid', $e);

    if (isset($form->categories)) {
        $newcategory->entryid = $e;

        foreach ($form->categories as $category) {
            if ($category > 0) {
                $newcategory->categoryid = $category;

                insert_record('glossary_entries_categories', $newcategory);
            } else {
                break;
            }
        }
    }

    if (isset($form->aliases)) {
        if ($aliases = explode("\n", clean_text($form->aliases))) {
            foreach ($aliases as $alias) {
                $alias = trim($alias);

                if ($alias) {
                    unset($newalias);

                    $newalias->entryid = $e;

                    $newalias->alias = $alias;

                    insert_record('glossary_alias', $newalias);
                }
            }
        }
    }

    redirect("view.php?id=$cm->id&mode=entry&hook=$newentry->id");

    die;
}
    if ($e) {
        $form = get_record('glossary_entries', 'id', $e);

        $newentry->id = $form->id;

        $newentry->concept = $form->concept;

        $newentry->definition = $form->definition;

        $newentry->format = $form->format;

        $newentry->timemodified = time();

        $newentry->approved = $glossary->defaultapproval or isteacher($course->id);

        $newentry->usedynalink = $form->usedynalink;

        $newentry->casesensitive = $form->casesensitive;

        $newentry->fullmatch = $form->fullmatch;

        $newentry->aliases = '';

        $newentry->userid = $form->userid;

        $newentry->timecreated = $form->timecreated;

        if ($aliases = get_records('glossary_alias', 'entryid', $e)) {
            foreach ($aliases as $alias) {
                $newentry->aliases .= $alias->alias . "\n";
            }
        }
    }

//Fill and print the form.
//We check every field has a default values here!!
if (!isset($newentry->concept)) {
    $newentry->concept = '';
}
if (!isset($newentry->aliases)) {
    $newentry->aliases = '';
}
if (!isset($newentry->usedynalink)) {
    if (isset($CFG->glossary_linkentries)) {
        $newentry->usedynalink = $CFG->glossary_linkentries;
    } else {
        $newentry->usedynalink = 0;
    }
}
if (!isset($newentry->casesensitive)) {
    if (isset($CFG->glossary_casesensitive)) {
        $newentry->casesensitive = $CFG->glossary_casesensitive;
    } else {
        $newentry->casesensitive = 0;
    }
}
if (!isset($newentry->fullmatch)) {
    if (isset($CFG->glossary_fullmatch)) {
        $newentry->fullmatch = $CFG->glossary_fullmatch;
    } else {
        $newentry->fullmatch = 0;
    }
}
if (!isset($newentry->definition)) {
    $newentry->definition = '';
}
if (!isset($newentry->timecreated)) {
    $newentry->timecreated = 0;
}
if (!isset($newentry->userid)) {
    $newentry->userid = $USER->id;
}
$strglossary = get_string('modulename', 'glossary');
$strglossaries = get_string('modulenameplural', 'glossary');
$stredit = get_string('edit');

if ($usehtmleditor = can_use_richtext_editor()) {
    $defaultformat = FORMAT_HTML;
} else {
    $defaultformat = FORMAT_MOODLE;
}

print_header_simple(
    strip_tags((string)$glossary->name),
    '',
    "<A HREF=\"index.php?id=$course->id\">$strglossaries</A> ->
              <A HREF=\"view.php?id=$cm->id\">$glossary->name</A> -> $stredit",
    '',
    '',
    true,
    '',
    navmenu($course, $cm)
);

$ineditperiod = ((time() - $newentry->timecreated < $CFG->maxeditingtime) || $glossary->editalways);
if ((!$ineditperiod || $USER->id != $newentry->userid) and !isteacher($course->id) and $e) {
    if ($USER->id != $newentry->userid) {
        error("You can't edit other people's entries!");
    } elseif (!$ineditperiod) {
        error("You can't edit this. Time expired!");
    }

    die;
}

echo '<p align="center"><font size="3"><b>' . stripslashes_safe($glossary->name);
echo '</b></font></p>';

/// Info box

if ($glossary->intro) {
    print_simple_box_start('center', '70%');

    echo format_text($glossary->intro);

    print_simple_box_end();
}

/// Tabbed browsing sections
$tab = GLOSSARY_ADDENTRY_VIEW;
include 'tabs.html';

include 'edit.html';

echo '</center>';
glossary_print_tabbed_table_end();

// Lets give IE more time to load the whole page
// before trying to load the editor.
if ($usehtmleditor) {
    use_html_editor('text');
}

print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
