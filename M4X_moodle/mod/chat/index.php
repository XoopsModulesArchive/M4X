<?php

declare(strict_types=1);

// $Id: index.php,v 1.8 2004/08/22 14:38:39 gustav_delius Exp $

require_once '../../config.php';
require_once 'lib.php';

require_variable($id);   // course

if (!$course = get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

require_course_login($course);

add_to_log($course->id, 'chat', 'view all', "index.php?id=$course->id", '');

/// Get all required strings

$strchats = get_string('modulenameplural', 'chat');
$strchat = get_string('modulename', 'chat');

/// Print the header

print_header_simple($strchats, '', $strchats, '', '', true, '', navmenu($course));

/// Get all the appropriate data

if (!$chats = get_all_instances_in_course('chat', $course)) {
    notice('There are no chats', "../../course/view.php?id=$course->id");

    die;
}

/// Print the list of instances (your module will probably extend this)

$timenow = time();
$strname = get_string('name');
$strweek = get_string('week');
$strtopic = get_string('topic');

if ('weeks' == $course->format) {
    $table->head = [$strweek, $strname];

    $table->align = ['center', 'left'];
} elseif ('topics' == $course->format) {
    $table->head = [$strtopic, $strname];

    $table->align = ['center', 'left', 'left', 'left'];
} else {
    $table->head = [$strname];

    $table->align = ['left', 'left', 'left'];
}

$currentsection = '';
foreach ($chats as $chat) {
    if (!$chat->visible) {
        //Show dimmed if the mod is hidden

        $link = "<a class=\"dimmed\" href=\"view.php?id=$chat->coursemodule\">$chat->name</a>";
    } else {
        //Show normal if the mod is visible

        $link = "<a href=\"view.php?id=$chat->coursemodule\">$chat->name</a>";
    }

    $printsection = '';

    if ($chat->section !== $currentsection) {
        if ($chat->section) {
            $printsection = $chat->section;
        }

        if ('' !== $currentsection) {
            $table->data[] = 'hr';
        }

        $currentsection = $chat->section;
    }

    if ('weeks' == $course->format or 'topics' == $course->format) {
        $table->data[] = [$printsection, $link];
    } else {
        $table->data[] = [$link];
    }
}

echo '<br>';

print_table($table);

/// Finish the page

print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
