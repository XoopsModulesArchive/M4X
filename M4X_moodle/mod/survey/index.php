<?php

declare(strict_types=1);

// $Id: index.php,v 1.15 2004/08/22 14:38:45 gustav_delius Exp $

require_once '../../config.php';
require_once 'lib.php';

require_variable($id);   // course

if (!$course = get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

require_course_login($course);

add_to_log($course->id, 'survey', 'view all', "index.php?id=$course->id", '');

$strsurveys = get_string('modulenameplural', 'survey');
$strweek = get_string('week');
$strtopic = get_string('topic');
$strname = get_string('name');
$strstatus = get_string('status');
$strdone = get_string('done', 'survey');
$strnotdone = get_string('notdone', 'survey');

print_header_simple(
    (string)$strsurveys,
    '',
    (string)$strsurveys,
    '',
    '',
    true,
    '',
    navmenu($course)
);

if (!$surveys = get_all_instances_in_course('survey', $course)) {
    notice('There are no surveys.', "../../course/view.php?id=$course->id");
}

if ('weeks' == $course->format) {
    $table->head = [$strweek, $strname, $strstatus];

    $table->align = ['CENTER', 'LEFT', 'LEFT'];
} elseif ('topics' == $course->format) {
    $table->head = [$strtopic, $strname, $strstatus];

    $table->align = ['CENTER', 'LEFT', 'LEFT'];
} else {
    $table->head = [$strname, $strstatus];

    $table->align = ['LEFT', 'LEFT'];
}

$currentsection = '';

foreach ($surveys as $survey) {
    if (isset($USER->id) and survey_already_done($survey->id, $USER->id)) {
        $ss = $strdone;
    } else {
        $ss = $strnotdone;
    }

    $printsection = '';

    if ($survey->section !== $currentsection) {
        if ($survey->section) {
            $printsection = $survey->section;
        }

        if ('' !== $currentsection) {
            $table->data[] = 'hr';
        }

        $currentsection = $survey->section;
    }

    //Calculate the href

    if (!$survey->visible) {
        //Show dimmed if the mod is hidden

        $tt_href = "<A class=\"dimmed\" HREF=\"view.php?id=$survey->coursemodule\">$survey->name</A>";
    } else {
        //Show normal if the mod is visible

        $tt_href = "<A HREF=\"view.php?id=$survey->coursemodule\">$survey->name</A>";
    }

    if ('weeks' == $course->format or 'topics' == $course->format) {
        $table->data[] = [$printsection, $tt_href, "<A HREF=\"view.php?id=$survey->coursemodule\">$ss</A>"];
    } else {
        $table->data[] = [$tt_href, "<A HREF=\"view.php?id=$survey->coursemodule\">$ss</A>"];
    }
}

echo '<BR>';
print_table($table);
print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
