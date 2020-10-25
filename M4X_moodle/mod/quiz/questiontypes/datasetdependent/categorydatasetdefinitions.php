<?php

declare(strict_types=1);

// $Id: categorydatasetdefinitions.php,v 1.2 2004/08/22 14:38:44 gustav_delius Exp $

/////////////////////////////////////////////////////////////////////
///// This page offers a way to define category level datasets  /////
/////////////////////////////////////////////////////////////////////

require_once '../../../../config.php';

require_variable($category);
optional_variable($question);

if (!$category = get_record('quiz_categories', 'id', $category)) {
    error("This wasn't a valid category!");
}

if (!$course = get_record('course', 'id', $category->course)) {
    error("This category doesn't belong to a valid course!");
}

require_login($course->id);

if (!isteacheredit($course->id)) {
    error('Only the teacher can import quiz questions!');
}

$DATASET_TYPES = [
    '1' => get_string('literal', 'quiz'),
    '2' => get_string('file', 'quiz'),
    '3' => get_string('link', 'quiz'),
];

$streditingquiz = get_string('editingquiz', 'quiz');
$strdefinedataset = get_string('datasetdefinitions', 'quiz', $category->name);
$strquestions = get_string('questions', 'quiz');

print_header_simple(
    (string)$strdefinedataset,
    (string)$strdefinedataset,
    "<A HREF=\"../../edit.php\">$streditingquiz</A> -> $strdefinedataset"
);

if ($form = data_submitted()) {   /// Filename
    $definition->category = $category->id;

    foreach ($form->name as $key => $name) {
        $definition->name = $name;

        $definition->id = $form->id[$key];

        $definition->type = $form->type[$key];

        if ($definition->id) {
            if (!update_record('quiz_dataset_definitions', $definition)) {
                notify('Could not update dataset item definition');
            }
        } elseif ($definition->name) {
            if (!insert_record('quiz_dataset_definitions', $definition)) {
                notify('Could not insert dataset item defintion');
            }
        }

        // No action
    }

    if ($form->question) {
        redirect("../../question.php?id=$question");
    } else {
        redirect('../../edit.php');
    }
}

/// Print form

print_heading_with_help($strdefinedataset, 'datasets', 'quiz');

print_simple_box_start('center', '', (string)$THEME->cellheading);
echo '<form method="post" action="categorydatasetdefinitions.php">';
echo "<input type=\"hidden\" name=\"category\" value=\"$category->id\">";
if ($question) {
    echo "<input type=\"hidden\" name=\"question\" value=\"$question\">";
}

echo '<table cellpadding=5>';

$definitions = get_records(
    'quiz_dataset_definitions',
    'category',
    $category->id
);
for ($idef = 1, $total = max(5, count($definitions)); $idef <= $total; ++$idef) {
    if ($definitions) {
        $definition = array_shift($definitions);
    } else {
        $definition = null;
    }

    echo '<tr><td align=right>';

    print_string('itemdefinition', 'quiz');

    echo ':</td><td>';

    echo "<input name=\"name[]\" type=\"text\" size=\"20\" value=\"$definition->name\">";

    echo "<input type=\"hidden\" name=\"id[]\" value=\"$definition->id\">";

    echo ' </td><td> ';

    choose_from_menu($DATASET_TYPES, 'type[]', $definition->type, '');

    echo "</td></tr>\n";
}

echo '<tr><td align="CENTER" colspan="3"><input type=submit value="' . get_string('continue') . '"></td></tr>';
echo '</table>';
echo '</form>';
print_simple_box_end();

print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
