<?php

declare(strict_types=1);

// $Id: import.php,v 1.3.2.1 2004/09/29 06:56:55 moodler Exp $
// Import quiz questions into the given category

require_once '../../config.php';
require_once 'lib.php';

optional_variable($format);
require_variable($id);    // Course Module ID

if (!$cm = get_record('course_modules', 'id', $id)) {
    error('Course Module ID was incorrect');
}

if (!$course = get_record('course', 'id', $cm->course)) {
    error('Course is misconfigured');
}

if (!$lesson = get_record('lesson', 'id', $cm->instance)) {
    error('Course module is incorrect');
}

require_login($course->id);

if (!isteacher($course->id)) {
    error('Only the teacher can import questions!');
}

$strimportquestions = get_string('importquestions', 'lesson');
$strlessons = get_string('modulenameplural', 'lesson');

print_header_simple(
    (string)$strimportquestions,
    " $strimportquestions",
    "<A HREF=index.php?id=$course->id>$strlessons</A> -> <a href=\"view.php?id=$cm->id\">$lesson->name</a>-> $strimportquestions"
);

if ($form = data_submitted()) {   /// Filename
    $form->format = clean_filename($form->format); // For safety

    if (isset($form->filename)) {                 // file already on server
        $newfile['tmp_name'] = $form->filename;

        $newfile['size'] = filesize($form->filename);
    } elseif (!empty($_FILES['newfile'])) {      // file was just uploaded
        $newfile = $_FILES['newfile'];
    }

    if (empty($newfile)) {
        notify(get_string('uploadproblem'));
    } elseif (!isset($filename) and (!is_uploaded_file($newfile['tmp_name']) or 0 == $newfile['size'])) {
        notify(get_string('uploadnofilefound'));
    } else {  // Valid file is found
        if (!is_readable("../quiz/format/$form->format/format.php")) {
            error('Format not known (' . clean_text($form->format) . ')');
        }

        require 'format.php';  // Parent class
        require "$CFG->dirroot/mod/quiz/lib.php"; // for the constants used in quiz/format/<format>/format.php
        require "$CFG->dirroot/mod/quiz/format/$form->format/format.php";

        $format = new quiz_file_format();

        if (!$format->importpreprocess()) {             // Do anything before that we need to
            error('Error occurred during pre-processing!');
        }

        if (!$format->importprocess($newfile['tmp_name'], $lesson, $_POST['pageid'])) {    // Process the uploaded file
            error('Error occurred during processing!');
        }

        if (!$format->importpostprocess()) {                     // In case anything needs to be done after
            error('Error occurred during post-processing!');
        }

        echo '<hr>';

        print_continue("view.php?id=$cm->id");

        print_footer($course);

        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------

        exit;
    }
}

/// Print upload form

$fileformats = get_list_of_plugins('mod/quiz/format');
$fileformatname = [];
foreach ($fileformats as $key => $fileformat) {
    $formatname = get_string($fileformat, 'lesson');

    if ($formatname == "[[$fileformat]]") {
        $formatname = $fileformat;  // Just use the raw folder name
    }

    $fileformatnames[$fileformat] = $formatname;
}
natcasesort($fileformatnames);

print_heading_with_help($strimportquestions, 'import', 'lesson');

print_simple_box_start('center', '', (string)$THEME->cellheading);
echo '<form enctype="multipart/form-data" method="post" action=import.php>';
echo "<input type=\"hidden\" name=\"id\" value=\"$cm->id\">\n";
echo '<input type="hidden" name="pageid" value="' . $_GET['pageid'] . "\">\n";
echo '<table cellpadding=5>';

echo '<tr><td align=right>';
print_string('fileformat', 'lesson');
echo ':</td><td>';
choose_from_menu($fileformatnames, 'format', 'gift', '');
echo '</tr>';

echo '<tr><td align=right>';
print_string('upload');
echo ':</td><td>';
echo ' <input name="newfile" type="file" size="50">';
echo '</tr><tr><td>&nbsp;</td><td>';
echo ' <input type=submit name=save value="' . get_string('uploadthisfile') . '">';
echo '</td></tr>';

echo '</table>';
echo '</form>';
print_simple_box_end();

print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
