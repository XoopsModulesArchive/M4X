<?php

declare(strict_types=1);

// $Id: view.php,v 1.25 2004/08/22 14:38:38 gustav_delius Exp $

require_once '../../config.php';
require_once 'lib.php';

optional_variable($id);    // Course Module ID
optional_variable($a);    // Assignment ID

if ($id) {
    if (!$cm = get_record('course_modules', 'id', $id)) {
        error('Course Module ID was incorrect');
    }

    if (!$course = get_record('course', 'id', $cm->course)) {
        error('Course is misconfigured');
    }

    if (!$assignment = get_record('assignment', 'id', $cm->instance)) {
        error('Course module is incorrect');
    }
} else {
    if (!$assignment = get_record('assignment', 'id', $a)) {
        error('Course module is incorrect');
    }

    if (!$course = get_record('course', 'id', $assignment->course)) {
        error('Course is misconfigured');
    }

    if (!$cm = get_coursemodule_from_instance('assignment', $assignment->id, $course->id)) {
        error('Course Module ID was incorrect');
    }
}

require_course_login($course);

add_to_log($course->id, 'assignment', 'view', "view.php?id=$cm->id", $assignment->id, $cm->id);

$strassignments = get_string('modulenameplural', 'assignment');
$strassignment = get_string('modulename', 'assignment');

print_header_simple(
    $assignment->name,
    '',
    "<A HREF=index.php?id=$course->id>$strassignments</A> -> $assignment->name",
    '',
    '',
    true,
    update_module_button($cm->id, $course->id, $strassignment),
    navmenu($course, $cm)
);

if (isteacher($course->id)) {
    echo '<p align="right">';

    if (OFFLINE == $assignment->type) {
        echo "<a href=\"submissions.php?id=$assignment->id\">" . get_string('viewfeedback', 'assignment') . '</a>';
    } else {
        $currentgroup = get_current_group($course->id);

        if ($currentgroup and isteacheredit($course->id)) {
            $group = get_record('groups', 'id', $currentgroup);

            $groupname = " ($group->name)";
        } else {
            $groupname = '';
        }

        $count = assignment_count_real_submissions($assignment, $currentgroup);

        echo "<a href=\"submissions.php?id=$assignment->id\">" . get_string('viewsubmissions', 'assignment', $count) . "</a>$groupname";
    }

    echo '</p>';
} elseif (!$cm->visible) {
    notice(get_string('activityiscurrentlyhidden'));
}

print_simple_box_start('CENTER');
print_heading($assignment->name, 'CENTER');

$timedifference = $assignment->timedue - time();
if ($timedifference < 31536000) {      // Don't bother showing dates over a year in the future
    $strdifference = format_time($timedifference);

    if ($timedifference < 0) {
        $strdifference = "<font color=\"red\">$strdifference</font>";
    }

    $strduedate = userdate($assignment->timedue) . " ($strdifference)";

    echo '<b>' . get_string('duedate', 'assignment') . "</b>: $strduedate<br>";
}

if ($assignment->grade < 0) {
    $scaleid = -($assignment->grade);

    if ($scale = get_record('scale', 'id', $scaleid)) {
        $scalegrades = make_menu_from_list($scale->scale);

        echo '<b>' . get_string('grade') . "</b>: $scale->name ";

        print_scale_menu_helpbutton($course->id, $scale);

        echo '<br>';
    }
} elseif ($assignment->grade > 0) {
    echo '<b>' . get_string('maximumgrade') . "</b>: $assignment->grade<br>";
}

echo '<br>';
echo format_text($assignment->description, $assignment->format);
print_simple_box_end();
echo '<br>';

if (isstudent($course->id)) {
    $submission = assignment_get_submission($assignment, $USER);

    if (OFFLINE == $assignment->type) {
        if ($submission->timemarked) {
            if (isset($scalegrades)) {
                $submission->grade = $scalegrades[$submission->grade];
            }

            assignment_print_feedback($course, $submission, $assignment);
        }
    } else {
        if ($submission and $submission->timemodified) {
            print_simple_box_start('center');

            echo '<center>';

            print_heading(get_string('yoursubmission', 'assignment') . ':', 'center');

            echo '<p><font size=-1><b>' . get_string('lastmodified') . '</b>: ' . userdate($submission->timemodified) . '</font></p>';

            assignment_print_user_files($assignment, $USER);

            print_simple_box_end();
        } else {
            print_heading(get_string('notsubmittedyet', 'assignment'));
        }

        echo '<hr size=1 noshade>';

        if ($submission and $submission->timemarked) {
            print_heading(get_string('submissionfeedback', 'assignment') . ':', 'center');

            if (isset($scalegrades)) {
                $submission->grade = $scalegrades[$submission->grade];
            }

            assignment_print_feedback($course, $submission, $assignment);
        }

        if (!$submission->timemarked or $assignment->resubmit) {
            if ($submission and $submission->timemodified) {
                echo '<p align="center">' . get_string('overwritewarning', 'assignment') . '</p>';
            }

            print_heading(get_string('submitassignment', 'assignment') . ':', 'center');

            assignment_print_upload_form($assignment);
        }
    }
}

print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
