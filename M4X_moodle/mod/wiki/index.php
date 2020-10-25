<?php

declare(strict_types=1);

// $Id: index.php,v 1.3 2004/08/22 14:38:46 gustav_delius Exp $

/// This page lists all the instances of wiki in a particular course
/// Replace wiki with the name of your module

require_once '../../config.php';
require_once 'lib.php';

require_variable($id);   // course

if (!$course = get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

require_course_login($course);

add_to_log($course->id, 'wiki', 'view all', "index.php?id=$course->id", '');

/// Get all required strings

$strwikis = get_string('modulenameplural', 'wiki');
$strwiki = get_string('modulename', 'wiki');

/// Print the header

print_header_simple((string)$strwikis, '', (string)$strwikis, '', '', true, '', navmenu($course));

/// Get all the appropriate data

if (!$wikis = get_all_instances_in_course('wiki', $course)) {
    notice('There are no wikis', "../../course/view.php?id=$course->id");

    die;
}

/// Print the list of instances (your module will probably extend this)

$timenow = time();
$strname = get_string('wikiname', 'wiki');
$strsummary = get_string('summary');
$strtype = get_string('wikitype', 'wiki');
$strlastmodified = get_string('lastmodified');
$strweek = get_string('week');
$strtopic = get_string('topic');

if ('weeks' == $course->format) {
    $table->head = [$strweek, $strname, $strsummary, $strtype, $strlastmodified];

    $table->align = ['CENTER', 'LEFT', 'LEFT', 'LEFT', 'LEFT'];
} elseif ('topics' == $course->format) {
    $table->head = [$strtopic, $strname, $strsummary, $strtype, $strlastmodified];

    $table->align = ['CENTER', 'LEFT', 'LEFT', 'LEFT', 'LEFT'];
} else {
    $table->head = [$strname, $strsummary, $strtype, $strlastmodified];

    $table->align = ['LEFT', 'LEFT', 'LEFT', 'LEFT'];
}

foreach ($wikis as $wiki) {
    if (!$wiki->visible) {
        //Show dimmed if the mod is hidden

        $link = '<A class="dimmed" HREF="view.php?id=' . $wiki->coursemodule . '">' . $wiki->name . '</A>';
    } else {
        //Show normal if the mod is visible

        $link = '<A HREF="view.php?id=' . $wiki->coursemodule . '">' . $wiki->name . '</A>';
    }

    $timmod = '<span class="smallinfo">' . userdate($wiki->timemodified) . '</span>';

    $summary = '<span class="smallinfo">' . $wiki->summary . '</span>';

    $site = get_site();

    switch ($wiki->wtype) {
        case 'teacher':
            $wtype = $site->teacher;
            break;
        case 'student':
            $wtype = $site->student;
            break;
        case 'group':
        default:
            $wtype = get_string('group');
            break;
    }

    $wtype = '<span class="smallinfo">' . $wtype . '</span>';

    if ('weeks' == $course->format or 'topics' == $course->format) {
        $table->data[] = [$wiki->section, $link, $summary, $wtype, $timmod];
    } else {
        $table->data[] = [$link, $summary, $wtype, $timmod];
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
