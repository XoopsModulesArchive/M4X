<?php

declare(strict_types=1);

// $Id: index.php,v 1.5 2004/08/22 14:38:43 gustav_delius Exp $

/// This page lists all the instances of lesson in a particular course

require_once '../../config.php';
require_once 'lib.php';

require_variable($id);   // course

if (!$course = get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

require_course_login($course);

add_to_log($course->id, 'lesson', 'view all', "index.php?id=$course->id", '');

/// Get all required strings

$strlessons = get_string('modulenameplural', 'lesson');
$strlesson = get_string('modulename', 'lesson');

/// Print the header

print_header_simple((string)$strlessons, '', (string)$strlessons, '', '', true, '', navmenu($course));

/// Get all the appropriate data

if (!$lessons = get_all_instances_in_course('lesson', $course)) {
    notice('There are no lessons', "../../course/view.php?id=$course->id");

    die;
}

/// Print the list of instances (your module will probably extend this)

$timenow = time();
$strname = get_string('name');
$strgrade = get_string('grade');
$strdeadline = get_string('deadline', 'lesson');
$strweek = get_string('week');
$strtopic = get_string('topic');

if ('weeks' == $course->format) {
    $table->head = [$strweek, $strname, $strgrade, $strdeadline];

    $table->align = ['CENTER', 'LEFT', 'CENTER', 'CENTER'];
} elseif ('topics' == $course->format) {
    $table->head = [$strtopic, $strname, $strgrade, $strdeadline];

    $table->align = ['CENTER', 'LEFT', 'CENTER', 'CENTER'];
} else {
    $table->head = [$strname, $strgrade, $strdeadline];

    $table->align = ['LEFT', 'CENTER', 'CENTER'];
}

foreach ($lessons as $lesson) {
    if (!$lesson->visible) {
        //Show dimmed if the mod is hidden

        $link = "<A class=\"dimmed\" HREF=\"view.php?id=$lesson->coursemodule\">$lesson->name</A>";
    } else {
        //Show normal if the mod is visible

        $link = "<A HREF=\"view.php?id=$lesson->coursemodule\">$lesson->name</A>";
    }

    if ($lesson->deadline > $timenow) {
        $due = userdate($lesson->deadline);
    } else {
        $due = '<FONT COLOR="red">' . userdate($lesson->deadline) . '</FONT>';
    }

    $grade_value = '';

    if ('weeks' == $course->format or 'topics' == $course->format) {
        if (isteacher($course->id)) {
            $grade_value = $lesson->grade;
        } elseif (isstudent($course->id)) {
            // it's a student, show their mean or maximum grade

            if ($lesson->usemaxgrade) {
                $grade = get_record_sql(
                    "SELECT MAX(grade) as grade FROM {$CFG->prefix}lesson_grades 
                            WHERE lessonid = $lesson->id AND userid = $USER->id GROUP BY userid"
                );
            } else {
                $grade = get_record_sql(
                    "SELECT AVG(grade) as grade FROM {$CFG->prefix}lesson_grades 
                            WHERE lessonid = $lesson->id AND userid = $USER->id GROUP BY userid"
                );
            }

            if ($grade) {
                // grades are stored as percentages

                $grade_value = number_format($grade->grade * $lesson->grade / 100, 1);
            }
        }

        $table->data[] = [$lesson->section, $link, $grade_value, $due];
    } else {
        $table->data[] = [$link, $grade_value, $due];
    }
}

echo '<BR>';

print_table($table);

/// Finish the page

print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
