<?php

declare(strict_types=1);

// $Id: datasetitems.php,v 1.2.2.1 2004/08/29 07:37:52 moodler Exp $

// Allows a teacher to create, edit and delete datasets

/// Print headings

$strdatasetnumber = get_string('datasetnumber', 'quiz');
$strnumberinfo = get_string('categoryinfo', 'quiz');
$strquestions = get_string('questions', 'quiz');
$strpublish = get_string('publish', 'quiz');
$strdelete = get_string('remove', 'quiz');
$straction = get_string('action');
$stradd = get_string('add');
$strcancel = get_string('cancel');
$strsavechanges = get_string('savechanges');
$strbacktoquiz = get_string('backtoquiz', 'quiz');

$streditingquiz = get_string('editingquiz', 'quiz');
$streditdatasets = get_string('editdatasets', 'quiz');
$strreuseifpossible = get_string('reuseifpossible', 'quiz');
$strforceregeneration = get_string('forceregeneration', 'quiz');

// Get datasetdefinitions:
$datasetdefs = get_records_sql(
    "SELECT a.* FROM {$CFG->prefix}quiz_dataset_definitions a,
                                 {$CFG->prefix}quiz_question_datasets b
                  WHERE a.id = b.datasetdefinition
                    AND b.question = $question->id"
);
if (empty($datasetdefs)) {
    redirect('edit.php');
}
foreach ($datasetdefs as $datasetdef) {
    if (!isset($maxnumber) || $datasetdef->itemcount < $maxnumber) {
        $maxnumber = $datasetdef->itemcount;
    }
}

/// Print heading

print_heading_with_help($streditdatasets, 'questiondatasets', 'quiz');

/// If data submitted, then process and store.
if ($form = data_submitted()) {
    if (isset($form->addbutton) && $form->addbutton
        && $maxnumber + 1 == $form->numbertoadd) { // This twisted condition should effectively stop resubmits caused by reloads
        $addeditem->number = $form->numbertoadd;

        foreach ($form->definition as $key => $itemdef) {
            $addeditem->definition = $itemdef;

            $addeditem->value = $form->value[$key];

            if ($form->itemid[$key]) {
                // Reuse an previously used record

                $addeditem->id = $form->itemid[$key];

                if (!update_record('quiz_dataset_items', $addeditem)) {
                    error('Error: Unable to update dataset item');
                }
            } else {
                unset($addeditem->id);

                if (!insert_record('quiz_dataset_items', $addeditem)) {
                    error('Error: Unable to insert dataset item');
                }
            }

            if ($datasetdefs[$itemdef]->itemcount <= $maxnumber) {
                $datasetdefs[$itemdef]->itemcount = $maxnumber + 1;

                if (!update_record(
                    'quiz_dataset_definitions',
                    $datasetdefs[$itemdef]
                )) {
                    error('Error: Unable to update itemcount');
                }
            }
        }

        // else Success:

        $maxnumber = $addeditem->number;
    } elseif (isset($form->deletebutton) && $form->deletebutton and $maxnumber == $form->numbertodelete) {
        // Simply decrease itemcount where == $maxnumber

        foreach ($datasetdefs as $datasetdef) {
            if ($datasetdef->itemcount == $maxnumber) {
                $datasetdef->itemcount--;

                if (!update_record(
                    'quiz_dataset_definitions',
                    $datasetdef
                )) {
                    error('Error: Unable to update itemcount');
                }
            }
        }

        --$maxnumber;
    }

    // Handle generator options...

    $olddatasetdefs = $datasetdefs;

    $datasetdefs = $qtypeobj->update_dataset_options($olddatasetdefs, $form);

    foreach ($datasetdefs as $key => $newdef) {
        if ($newdef->options != $olddatasetdefs[$key]->options) {
            // Save the new value for options

            update_record('quiz_dataset_definitions', $newdef);
        }
    }
}

make_upload_directory((string)$course->id);  // Just in case
$grosscoursefiles = get_directory_list(
    "$CFG->dataroot/$course->id",
    (string)$CFG->moddata
);

// Have $coursefiles indexed by file paths:
$coursefiles = [];
foreach ($grosscoursefiles as $coursefile) {
    $coursefiles[$coursefile] = $coursefile;
}

// Get question header if any
$strquestionheader = $qtypeobj->comment_header($question);

// Get the data set definition and items:
foreach ($datasetdefs as $key => $datasetdef) {
    $datasetdefs[$key]->items = get_records_sql( // Use number as key!!
        " SELECT number, definition, id, value
                      FROM {$CFG->prefix}quiz_dataset_items
                      WHERE definition = $datasetdef->id "
    );
}

$table->data = [];
for ($number = $maxnumber; $number > 0; --$number) {
    $columns = [];

    if ($maxnumber == $number) {
        $columns[] = "<INPUT TYPE=\"hidden\" name=\"numbertodelete\" value=\"$number\">
                     <INPUT TYPE=\"submit\" name=\"deletebutton\" value=\"$strdelete\">";
    } else {
        $columns[] = '';
    }

    $columns[] = $number;

    foreach ($datasetdefs as $datasetdef) {
        $columns[] = '<INPUT TYPE="hidden" name="itemid[]" value="' . $datasetdef->items[$number]->id . '">' . "<INPUT TYPE=\"hidden\" name=\"number[]\" value=\"$number\">
                    <INPUT TYPE=\"hidden\" name=\"definition[]\" value=\"$datasetdef->id\">" . // Set $data:
                     ($data[$datasetdef->name] = $datasetdef->items[$number]->value);
    }

    if ($strquestionheader) {
        $columns[] = $qtypeobj->comment_on_datasetitems($question, $data, $number);
    }

    $table->data[] = $columns;
}

$table->head = [$straction, $strdatasetnumber];
$table->align = ['CENTER', 'CENTER'];
$addtable->head = $table->head;
if ($qtypeobj->supports_dataset_item_generation()) {
    if (isset($form->forceregeneration) && $form->forceregeneration) {
        $force = ' checked ';

        $reuse = '';
    } else {
        $force = '';

        $reuse = ' checked ';
    }

    $forceregeneration = '<br><INPUT type="radio" name="forceregeneration" ' . $reuse . ' value="0">' . $strreuseifpossible . '<br><INPUT type="radio" name="forceregeneration" value="1" ' . $force . '>' . $strforceregeneration;
} else {
    $forceregeneration = '';
}
$addline = [
    '<INPUT TYPE="hidden" name="numbertoadd" value="' . ($maxnumber + 1) . "\"><INPUT TYPE=\"submit\" name=\"addbutton\" value=\"$stradd\">" . $forceregeneration,
    $maxnumber + 1,
];
foreach ($datasetdefs as $datasetdef) {
    if ($datasetdef->name) {
        $table->head[] = $datasetdef->name;

        $addtable->head[] = $datasetdef->name . ($qtypeobj->supports_dataset_item_generation() ? '<br>' . $qtypeobj->custom_generator_tools($datasetdef) : '');

        $table->align[] = 'CENTER';

        // THE if-statement IS FOR BUT ONE THING

        // - to determine an item value for the input field

        // - this is tried in a number of different way...

        if (isset($form->regenerateddefid) && $form->regenerateddefid) {
            // Regeneration clicked...

            if ($form->regenerateddefid == $datasetdef->id) {
                //...for this item...

                $itemvalue = $qtypeobj->generate_dataset_item($datasetdef->options);
            } else {
                // ...but not for this, keep unchanged!

                foreach ($form->definition as $key => $itemdef) {
                    if ($datasetdef->id == $itemdef) {
                        $itemvalue = $form->value[$key];

                        break;
                    }
                }
            }
        } elseif (isset($form->forceregeneration)
                  && $form->forceregeneration) {
            // Can only mean a an "Add operation with forced regeneration:

            $itemvalue = $qtypeobj->generate_dataset_item($datasetdef->options);
        } elseif (isset($datasetdef->items[$maxnumber + 1])) {
            // Looks like we do have an old value to use here:

            $itemvalue = $datasetdef->items[$maxnumber + 1]->value;
        } else {
            // We're getting getting desperate -

            // is there any chance to determine a value somehow

            // Let's just try anything now...

            $qtypeobj->supports_dataset_item_generation() and '' !== (// Generation could work if the options are alright:
            $itemvalue = $qtypeobj->generate_dataset_item($datasetdef->options)
            )

            or preg_match(
                '(.*)' . ($maxnumber) . '(.*)',
                $datasetdef->items[$maxnumber]->value,
                $valueregs
            )
            // Looks like this trivial generator does it: and $itemvalue = $valueregs[1] . ($maxnumber + 1) . $valueregs[2]

            or // Let's just pick the dataset number, better than nothing:
            $itemvalue = $maxnumber + 1;
        }

        $itemid = $datasetdef->items[$maxnumber + 1]->id ?? '';

        $addline[] = '<INPUT TYPE="hidden" name="itemid[]" value="' . $itemid . '">' . "<INPUT TYPE=\"hidden\" name=\"definition[]\" value=\"$datasetdef->id\">" . (2 != $datasetdef->type ? '<INPUT TYPE="text" size="20" name="value[]" value="' . $itemvalue . '">' : choose_from_menu(
            $coursefiles,
            'value[]',
            $itemvalue,
            '',
            '',
            '',
            true
        ));

        $data[$datasetdef->name] = $itemvalue;
    }
}
if ($strquestionheader) {
    $table->head[] = $strquestionheader;

    $addtable->head[] = $strquestionheader;

    $table->align[] = 'CENTER';

    $addline[] = $qtypeobj->comment_on_datasetitems($question, $data, $maxnumber + 1);
}

// Print form for adding one more dataset
$addtable->align = $table->align;
$addtable->data = [$addline];
echo "<FORM NAME=\"addform\" METHOD=\"post\" ACTION=\"question.php\">
            <INPUT TYPE=\"hidden\" NAME=\"regenerateddefid\" VALUE=\"0\">
            <INPUT TYPE=\"hidden\" NAME=\"id\" VALUE=\"$question->id\">
            <INPUT TYPE=\"hidden\" NAME=\"editdatasets\" VALUE=\"1\">";
print_table($addtable);
echo '</FORM>';

// Print form with current datasets
if ($table->data) {
    echo "<FORM METHOD=\"post\" ACTION=\"question.php\">
            <INPUT TYPE=\"hidden\" NAME=\"id\" VALUE=\"$question->id\">
            <INPUT TYPE=\"hidden\" NAME=\"editdatasets\" VALUE=\"1\">";

    print_table($table);

    echo '</FORM>';
}

echo "<center><BR><BR><FORM METHOD=\"get\" ACTION=\"edit.php\"><INPUT TYPE=\"hidden\" NAME=\"question\" VALUE=\"$question->id\"><INPUT TYPE=submit NAME=backtoquiz VALUE=\"$strbacktoquiz\"></FORM></center>\n";

print_footer();
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
