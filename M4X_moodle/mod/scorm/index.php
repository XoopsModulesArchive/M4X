<?php

// $Id: index.php,v 1.2 2004/07/28 12:01:42 moodler Exp $

require_once '../../config.php';

require_variable($id);   // course

if (!$course = get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

if ($course->category) {
    require_login($course->id);

    $navigation = "<A HREF=\"../../course/view.php?id=$course->id\">$course->shortname</A> ->";
} else {
    $navigation = '';
}

add_to_log($course->id, 'scorm', 'view all', "index.php?id=$course->id", '');

$strscorm = get_string('modulename', 'scorm');
$strscorms = get_string('modulenameplural', 'scorm');
$strweek = get_string('week');
$strtopic = get_string('topic');
$strname = get_string('name');
$strsummary = get_string('summary');
$strlastmodified = get_string('lastmodified');

print_header(
    "$course->shortname: $strscorms",
    (string)$course->fullname,
    "$navigation $strscorms",
    '',
    '',
    true,
    '',
    navmenu($course)
);

if ('weeks' == $course->format or 'topics' == $course->format) {
    $sortorder = 'cw.section ASC';
} else {
    $sortorder = 'm.timemodified DESC';
}

if (!$scorms = get_all_instances_in_course('scorm', $course)) {
    notice('There are no scorms', "../../course/view.php?id=$course->id");

    exit;
}

if ('weeks' == $course->format) {
    $table->head = [$strweek, $strname, $strsummary];

    $table->align = ['CENTER', 'LEFT', 'LEFT'];
} elseif ('topics' == $course->format) {
    $table->head = [$strtopic, $strname, $strsummary];

    $table->align = ['CENTER', 'LEFT', 'LEFT'];
} else {
    $table->head = [$strlastmodified, $strname, $strsummary];

    $table->align = ['LEFT', 'LEFT', 'LEFT'];
}

foreach ($scorms as $scorm) {
    $tt = '';

    if ('weeks' == $course->format or 'topics' == $course->format) {
        if ($scorm->section) {
            $tt = (string)$scorm->section;
        }
    } else {
        $tt = '<FONT SIZE=1>' . userdate($scorm->timemodified);
    }

    if (!$scorm->visible) {
        //Show dimmed if the mod is hidden

        $table->data[] = [
            $tt,
            "<A class=\"dimmed\" HREF=\"view.php?id=$scorm->coursemodule\">$scorm->name</A>",
            text_to_html($scorm->summary),
        ];
    } else {
        //Show normal if the mod is visible

        $table->data[] = [
            $tt,
            "<A HREF=\"view.php?id=$scorm->coursemodule\">$scorm->name</A>",
            text_to_html($scorm->summary),
        ];
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



