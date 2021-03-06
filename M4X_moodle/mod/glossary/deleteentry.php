<?php

declare(strict_types=1);

// $Id: deleteentry.php,v 1.14.2.4 2004/08/29 22:46:31 stronk7 Exp $

require_once '../../config.php';
require_once 'lib.php';

require_variable($id);    // course module ID
optional_variable($confirm);  // commit the operation?
optional_variable($entry);  // entry id
require_variable($prevmode);  //  current frame
optional_variable($hook);         // pivot id

$prevmode = strip_tags(urldecode($prevmode));  //XSS
$hook = strip_tags(urldecode($hook));  //XSS

$strglossary = get_string('modulename', 'glossary');
$strglossaries = get_string('modulenameplural', 'glossary');
$stredit = get_string('edit');
$entrydeleted = get_string('entrydeleted', 'glossary');

if (!$cm = get_record('course_modules', 'id', $id)) {
    error('Course Module ID was incorrect');
}

if (!$course = get_record('course', 'id', $cm->course)) {
    error('Course is misconfigured');
}

if (!$entry = get_record('glossary_entries', 'id', $entry)) {
    error('Entry ID was incorrect');
}

require_login($course->id);

if (isguest()) {
    error('Guests are not allowed to edit or delete entries', $_SERVER['HTTP_REFERER']);
}

if (!$glossary = get_record('glossary', 'id', $cm->instance)) {
    error('Glossary is incorrect');
}

if (!isteacher($course->id) and !$glossary->studentcanpost) {
    error('You are not allowed to edit or delete entries');
}

$strareyousuredelete = get_string('areyousuredelete', 'glossary');

print_header_simple(
    (string)$glossary->name,
    '',
    "<A HREF=index.php?id=$course->id>$strglossaries</A> -> $glossary->name",
    '',
    '',
    true,
    update_module_button($cm->id, $course->id, $strglossary),
    navmenu($course, $cm)
);

if (($entry->userid != $USER->id) and !isteacher($course->id)) {
    error("You can't delete other people's entries!");
}
$ineditperiod = ((time() - $entry->timecreated < $CFG->maxeditingtime) || $glossary->editalways);
if (!$ineditperiod and !isteacher($course->id)) {
    error("You can't delete this. Time expired!");
}

/// If data submitted, then process and store.

if ($confirm) { // the operation was confirmed.
    // if it is an imported entry, just delete the relation

    if ($entry->sourceglossaryid) {
        $entry->glossaryid = $entry->sourceglossaryid;

        $entry->sourceglossaryid = 0;

        if (!update_record('glossary_entries', $entry)) {
            error('Could not update your glossary');
        }
    } else {
        if ($entry->attachment) {
            glossary_delete_old_attachments($entry);
        }

        delete_records('glossary_comments', 'entryid', $entry->id);

        delete_records('glossary_alias', 'entryid', $entry->id);

        delete_records('glossary_ratings', 'entryid', $entry->id);

        delete_records('glossary_entries', 'id', $entry->id);
    }

    add_to_log($course->id, 'glossary', 'delete entry', "view.php?id=$cm->id&mode=$prevmode&hook=$hook", $entry->id, $cm->id);

    redirect("view.php?id=$cm->id&mode=$prevmode&hook=$hook", $entrydeleted);
} else {        // the operation has not been confirmed yet so ask the user to do so
    notice_yesno(
        "<b>$entry->concept</b><p>$strareyousuredelete</p>",
        "deleteentry.php?id=$cm->id&mode=delete&confirm=1&entry=" . s($entry->id) . "&prevmode=$prevmode&hook=$hook",
        "view.php?id=$cm->id&mode=$prevmode&hook=$hook"
    );
}

print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
