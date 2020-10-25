<?php

// $Id: index.php,v 1.11 2004/08/09 14:49:15 moodler Exp $

require_once '../../config.php';

require_variable($id);   // course

if (!empty($CFG->forcelogin)) {
    require_login();
}

if (!$course = get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

if ($course->category) {
    require_login($course->id);

    $navigation = "<A HREF=\"../../course/view.php?id=$course->id\">$course->shortname</A> ->";
} else {
    $navigation = '';
}

add_to_log($course->id, 'resource', 'view all', "index.php?id=$course->id", '');

$strresource = get_string('modulename', 'resource');
$strresources = get_string('modulenameplural', 'resource');
$strweek = get_string('week');
$strtopic = get_string('topic');
$strname = get_string('name');
$strsummary = get_string('summary');
$strlastmodified = get_string('lastmodified');

print_header(
    "$course->shortname: $strresources",
    (string)$course->fullname,
    "$navigation $strresources",
    '',
    '',
    true,
    '',
    navmenu($course)
);

if (!$resources = get_all_instances_in_course('resource', $course)) {
    notice('There are no resources', "../../course/view.php?id=$course->id");

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

$currentsection = '';
foreach ($resources as $resource) {
    if ('weeks' == $course->format or 'topics' == $course->format) {
        $printsection = '';

        if ($resource->section !== $currentsection) {
            if ($resource->section) {
                $printsection = $resource->section;
            }

            if ('' !== $currentsection) {
                $table->data[] = 'hr';
            }

            $currentsection = $resource->section;
        }
    } else {
        $printsection = '<span class="smallinfo">' . userdate($resource->timemodified) . '</span>';
    }

    if (!empty($resource->extra)) {
        $extra = urldecode($resource->extra);
    } else {
        $extra = '';
    }

    if (!$resource->visible) {      // Show dimmed if the mod is hidden
        $table->data[] = [
            $printsection,
            "<a class=\"dimmed\" $extra href=\"view.php?id=$resource->coursemodule\">$resource->name</a>",
            format_text($resource->summary),
        ];
    } else {                        //Show normal if the mod is visible
        $table->data[] = [
            $printsection,
            "<a $extra href=\"view.php?id=$resource->coursemodule\">$resource->name</a>",
            format_text($resource->summary),
        ];
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



