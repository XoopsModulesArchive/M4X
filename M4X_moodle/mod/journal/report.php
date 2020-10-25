<?php

// $Id: report.php,v 1.26.2.1 2004/08/30 06:28:41 moodler Exp $

require_once '../../config.php';
require_once 'lib.php';

require_variable($id);   // course module

if (!$cm = get_record('course_modules', 'id', $id)) {
    error('Course Module ID was incorrect');
}

if (!$course = get_record('course', 'id', $cm->course)) {
    error('Course module is misconfigured');
}

require_login($course->id);

if (!isteacher($course->id)) {
    error('Only teachers can look at this page');
}

if (!$journal = get_record('journal', 'id', $cm->instance)) {
    error('Course module is incorrect');
}

// make some easy ways to access the entries.
if ($eee = get_records('journal_entries', 'journal', $journal->id)) {
    foreach ($eee as $ee) {
        $entrybyuser[$ee->userid] = $ee;

        $entrybyentry[$ee->id] = $ee;
    }
} else {
    $entrybyuser = [];

    $entrybyentry = [];
}

$strentries = get_string('entries', 'journal');
$strjournals = get_string('modulenameplural', 'journal');

print_header_simple(
    (string)$strjournals,
    '',
    "<a href=\"index.php?id=$course->id\">$strjournals</a> ->
                  <a href=\"view.php?id=$cm->id\">$journal->name</a> -> $strentries",
    '',
    '',
    true
);

/// Check to see if groups are being used in this journal
if ($groupmode = groupmode($course, $cm)) {   // Groups are being used
    $currentgroup = setup_and_print_groups($course, $groupmode, "report.php?id=$cm->id");
} else {
    $currentgroup = false;
}

/// Process incoming data if there is any
if ($data = data_submitted()) {
    $feedback = [];

    $data = (array)$data;

    // Peel out all the data from variable names.

    foreach ($data as $key => $val) {
        if ('id' != $key) {
            $type = mb_substr($key, 0, 1);

            $num = mb_substr($key, 1);

            $feedback[$num][$type] = $val;
        }
    }

    $timenow = time();

    $count = 0;

    foreach ($feedback as $num => $vals) {
        $entry = $entrybyentry[$num];

        // Only update entries where feedback has actually changed.

        if (($vals['r'] != $entry->rating) || ($vals['c'] != addslashes($entry->comment))) {
            $newentry->rating = $vals['r'];

            $newentry->comment = $vals['c'];

            $newentry->teacher = $USER->id;

            $newentry->timemarked = $timenow;

            $newentry->mailed = 0;           // Make sure mail goes out (again, even)

            $newentry->id = $num;

            if (!update_record('journal_entries', $newentry)) {
                notify("Failed to update the journal feedback for user $entry->userid");
            } else {
                $count++;
            }

            $entrybyuser[$entry->userid]->rating = $vals['r'];

            $entrybyuser[$entry->userid]->comment = $vals['c'];

            $entrybyuser[$entry->userid]->teacher = $USER->id;

            $entrybyuser[$entry->userid]->timemarked = $timenow;
        }
    }

    add_to_log($course->id, 'journal', 'update feedback', "report.php?id=$cm->id", "$count users", $cm->id);

    notify(get_string('feedbackupdated', 'journal', (string)$count), 'green');
} else {
    add_to_log($course->id, 'journal', 'view responses', "report.php?id=$cm->id", (string)$journal->id, $cm->id);
}

/// Print out the journal entries

if ($currentgroup) {
    $users = get_group_users($currentgroup);
} else {
    $users = get_course_students($course->id);
}

if (!$users) {
    print_heading(get_string('nousersyet'));
} else {
    $grades = make_grades_menu($journal->assessed);

    $teachers = get_course_teachers($course->id);

    $allowedtograde = (VISIBLEGROUPS != $groupmode or isteacheredit($course->id) or ismember($currentgroup));

    if ($allowedtograde) {
        echo '<form action="report.php" method="post">';
    }

    if ($usersdone = journal_get_users_done($journal)) {
        foreach ($usersdone as $user) {
            if ($currentgroup) {
                if (!ismember($currentgroup, $user->id)) {    /// Yes, it's inefficient, but this module will die
                    continue;
                }
            }

            journal_print_user_entry($course, $user, $entrybyuser[$user->id], $teachers, $grades);

            unset($users[$user->id]);
        }
    }

    foreach ($users as $user) {       // Remaining users
        journal_print_user_entry($course, $user, null, $teachers, $grades);
    }

    if ($allowedtograde) {
        echo '<center>';

        echo "<input type=hidden name=id value=\"$cm->id\">";

        echo '<input type=submit value="' . get_string('saveallfeedback', 'journal') . '">';

        echo '</center>';

        echo '</form>';
    }
}

print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------



