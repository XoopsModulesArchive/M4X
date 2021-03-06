<?php

declare(strict_types=1);

// $Id: report.php,v 1.25.2.1 2004/09/29 07:01:33 moodler Exp $

// This script uses installed report plugins to print quiz reports

require_once '../../config.php';
require_once 'lib.php';

optional_variable($id);    // Course Module ID, or
optional_variable($q);     // quiz ID

optional_variable($mode, 'overview');        // Report mode

if ($id) {
    if (!$cm = get_record('course_modules', 'id', $id)) {
        error('Course Module ID was incorrect');
    }

    if (!$course = get_record('course', 'id', $cm->course)) {
        error('Course is misconfigured');
    }

    if (!$quiz = get_record('quiz', 'id', $cm->instance)) {
        error('Course module is incorrect');
    }
} else {
    if (!$quiz = get_record('quiz', 'id', $q)) {
        error('Course module is incorrect');
    }

    if (!$course = get_record('course', 'id', $quiz->course)) {
        error('Course is misconfigured');
    }

    if (!$cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id)) {
        error('Course Module ID was incorrect');
    }
}

require_login($course->id);

if (!isteacher($course->id)) {
    error('You are not allowed to use this script');
}

add_to_log($course->id, 'quiz', 'report', "report.php?id=$cm->id", (string)$quiz->id, (string)$cm->id);

/// Print the page header
if (empty($noheader)) {
    $strquizzes = get_string('modulenameplural', 'quiz');

    $strquiz = get_string('modulename', 'quiz');

    $strreport = get_string('report', 'quiz');

    print_header_simple(
        (string)$quiz->name,
        '',
        "<A HREF=index.php?id=$course->id>$strquizzes</A> 
                      -> <a href=\"view.php?id=$cm->id\">$quiz->name</a> -> $strreport",
        '',
        '',
        true,
        update_module_button($cm->id, $course->id, $strquiz),
        navmenu($course, $cm)
    );

    print_heading($quiz->name);

    /// Print list of available quiz reports

    $allreports = get_list_of_plugins('mod/quiz/report');

    $reportlist = ['overview', 'regrade'];   // Standard reports we want to show first

    foreach ($allreports as $report) {
        if (!in_array($report, $reportlist, true)) {
            $reportlist[] = $report;
        }
    }

    echo '<table cellpadding=10 align=center><tr>';

    foreach ($reportlist as $report) {
        $strreport = get_string("report$report", 'quiz');

        if ($report == $mode) {
            echo "<td><u>$strreport</u></td>";
        } else {
            echo "<td><a href=\"report.php?id=$cm->id&mode=$report\">$strreport</a></td>";
        }
    }

    echo '</tr></table><hr size="1" noshade="noshade">';
}

/// Open the selected quiz report and display it

$mode = clean_filename($mode);

if (!is_readable("report/$mode/report.php")) {
    error('Report not known (' . clean_text($mode) . ')');
}

include 'report/default.php';  // Parent class
include "report/$mode/report.php";

$report = new quiz_report();

if (!$report->display($quiz, $cm, $course)) {             // Run the report!
    error('Error occurred during pre-processing!');
}

if (empty($noheader)) {
    print_footer($course);

    //--------------------------------------------

    // MOODLE4XOOPS - J. BAUDIN

    //--------------------------------------------

    require_once "$CFG->dirroot/footer.php";

    //--------------------------------------------
}
