<?php

declare(strict_types=1);

// $Id: index.php,v 1.10 2004/08/21 20:20:58 gustav_delius Exp $

require '../../config.php';
require 'lib.php';
require 'locallib.php';

require_variable($id);   // course

if (!$course = get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

require_login($course->id);
add_to_log($course->id, 'workshop', 'view all', "index.php?id=$course->id", '');

$strworkshops = get_string('modulenameplural', 'workshop');
$strworkshop = get_string('modulename', 'workshop');
$strweek = get_string('week');
$strtopic = get_string('topic');
$strname = get_string('name');
$strphase = get_string('phase', 'workshop');
$strdeadline = get_string('deadline', 'workshop');
$strsubmitted = get_string('submitted', 'assignment');

print_header_simple((string)$strworkshops, '', (string)$strworkshops, '', '', true, '', navmenu($course));

if (!$workshops = get_all_instances_in_course('workshop', $course)) {
    notice('There are no workshops', "../../course/view.php?id=$course->id");

    die;
}

$timenow = time();

if ('weeks' == $course->format) {
    $table->head = [$strweek, $strname, $strphase, $strsubmitted, $strdeadline];

    $table->align = ['CENTER', 'LEFT', 'LEFT', 'LEFT', 'LEFT'];
} elseif ('topics' == $course->format) {
    $table->head = [$strtopic, $strname, $strphase, $strsubmitted, $strdeadline];

    $table->align = ['CENTER', 'LEFT', 'left', 'LEFT', 'LEFT'];
} else {
    $table->head = [$strname, $strphase, $strsubmitted, $strdeadline];

    $table->align = ['LEFT', 'LEFT', 'LEFT', 'LEFT'];
}

foreach ($workshops as $workshop) {
    switch ($workshop->phase) {
        case 0:
        case 1:
            $phase = get_string('phase1short', 'workshop');
            break;
        case 2:
            $phase = get_string('phase2short', 'workshop');
            break;
        case 3:
            $phase = get_string('phase3short', 'workshop');
            break;
        case 4:
            $phase = get_string('phase4short', 'workshop');
            break;
        case 5:
            $phase = get_string('phase5short', 'workshop');
            break;
        case 6:
            $phase = get_string('phase6short', 'workshop');
            break;
    }

    if ($submissions = workshop_get_user_submissions($workshop, $USER)) {
        foreach ($submissions as $submission) {
            if ($submission->timecreated <= $workshop->deadline) {
                $submitted = userdate($submission->timecreated);
            } else {
                $submitted = '<FONT COLOR=red>' . userdate($submission->timecreated) . '</FONT>';
            }

            $due = userdate($workshop->deadline);

            if (!$workshop->visible) {
                //Show dimmed if the mod is hidden

                $link = "<A class=\"dimmed\" HREF=\"view.php?id=$workshop->coursemodule\">$workshop->name</A><br>" . "($submission->title)";
            } else {
                //Show normal if the mod is visible

                $link = "<A HREF=\"view.php?id=$workshop->coursemodule\">$workshop->name</A><br>" . "($submission->title)";
            }

            if ('weeks' == $course->format or 'topics' == $course->format) {
                $table->data[] = [$workshop->section, $link, $phase, $submitted, $due];
            } else {
                $table->data[] = [$link, $phase, $submitted, $due];
            }
        }
    } else {
        $submitted = get_string('no');

        $due = userdate($workshop->deadline);

        if (!$workshop->visible) {
            //Show dimmed if the mod is hidden

            $link = "<A class=\"dimmed\" HREF=\"view.php?id=$workshop->coursemodule\">$workshop->name</A>";
        } else {
            //Show normal if the mod is visible

            $link = "<A HREF=\"view.php?id=$workshop->coursemodule\">$workshop->name</A>";
        }

        if ('weeks' == $course->format or 'topics' == $course->format) {
            $table->data[] = [$workshop->section, $link, $phase, $submitted, $due];
        } else {
            $table->data[] = [$link, $phase, $submitted, $due];
        }
    }
}
echo '<br>';

print_table($table);

print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
