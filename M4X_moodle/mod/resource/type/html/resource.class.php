<?php

declare(strict_types=1);

// $Id: resource.class.php,v 1.10.2.3 2004/10/25 20:33:27 gustav_delius Exp $

class resource_html extends resource_base
{
    public function __construct($cmid = 0)
    {
        parent::resource_base($cmid);
    }

    public function add_instance($resource)
    {
        // Given an object containing all the necessary data,

        // (defined by the form in mod.html) this function

        // will create a new instance and return the id number

        // of the new instance.

        global $RESOURCE_WINDOW_OPTIONS;

        $resource->timemodified = time();

        if (isset($resource->windowpopup)) {
            $optionlist = [];

            foreach ($RESOURCE_WINDOW_OPTIONS as $option) {
                if (isset($resource->$option)) {
                    $optionlist[] = $option . '=' . $resource->$option;
                }
            }

            $resource->popup = implode(',', $optionlist);
        } elseif (isset($resource->windowpage)) {
            $resource->popup = '';
        }

        if (isset($resource->parametersettingspref)) {
            set_user_preference('resource_parametersettingspref', $resource->parametersettingspref);
        }

        if (isset($resource->windowsettingspref)) {
            set_user_preference('resource_windowsettingspref', $resource->windowsettingspref);
        }

        return insert_record('resource', $resource);
    }

    public function update_instance($resource)
    {
        // Given an object containing all the necessary data,

        // (defined by the form in mod.html) this function

        // will update an existing instance with new data.

        global $RESOURCE_WINDOW_OPTIONS;

        $resource->id = $resource->instance;

        $resource->timemodified = time();

        if (isset($resource->windowpopup)) {
            $optionlist = [];

            foreach ($RESOURCE_WINDOW_OPTIONS as $option) {
                if (isset($resource->$option)) {
                    $optionlist[] = $option . '=' . $resource->$option;
                }
            }

            $resource->popup = implode(',', $optionlist);
        } elseif (isset($resource->windowpage)) {
            $resource->popup = '';
        }

        if (isset($resource->parametersettingspref)) {
            set_user_preference('resource_parametersettingspref', $resource->parametersettingspref);
        }

        if (isset($resource->windowsettingspref)) {
            set_user_preference('resource_windowsettingspref', $resource->windowsettingspref);
        }

        return update_record('resource', $resource);
    }

    public function display()
    {
        global $CFG, $THEME;

        /// Set up generic stuff first, including checking for access

        parent::display();

        /// Set up some shorthand variables

        $cm = $this->cm;

        $course = $this->course;

        $resource = $this->resource;

        $pagetitle = strip_tags($course->shortname . ': ' . $resource->name);

        $formatoptions->noclean = true;

        $inpopup = !empty($_GET['inpopup']);

        if ($resource->popup) {
            if ($inpopup) {                    /// Popup only
                add_to_log($course->id, 'resource', 'view', "view.php?id={$cm->id}", $resource->id, $cm->id);

                print_header();

                print_simple_box(
                    format_text($resource->alltext, FORMAT_HTML, $formatoptions, $course->id),
                    'center',
                    '',
                    (string)$THEME->cellcontent,
                    '20'
                );

                print_footer();

                //--------------------------------------------

                // MOODLE4XOOPS - J. BAUDIN

                //--------------------------------------------

                require_once "$CFG->dirroot/footer.php";

            //--------------------------------------------
            } else {                           /// Make a page and a pop-up window
                print_header(
                    $pagetitle,
                    $course->fullname,
                    "$this->navigation {$resource->name}",
                    '',
                    '',
                    true,
                    update_module_button($cm->id, $course->id, $this->strresource),
                    navmenu($course, $cm)
                );

                echo "\n<script language=\"Javascript\">";

                echo "\n<!--\n";

                echo "openpopup('/mod/resource/view.php?inpopup=true&id={$cm->id}','resource{$resource->id}','{$resource->popup}');\n";

                echo "\n-->\n";

                echo '</script>';

                if (trim(strip_tags($resource->summary))) {
                    print_simple_box(format_text($resource->summary, FORMAT_MOODLE, $formatoptions, $course->id), 'center');
                }

                $link = "<a href=\"$CFG->wwwroot/mod/resource/view.php?inpopup=true&id={$cm->id}\" target=\"resource{$resource->id}\" onClick=\"return openpopup('/mod/resource/view.php?inpopup=true&id={$cm->id}', 'resource{$resource->id}','{$resource->popup}');\">{$resource->name}</a>";

                echo '<p>&nbsp</p>';

                echo '<p align="center">';

                print_string('popupresource', 'resource');

                echo '<br>';

                print_string('popupresourcelink', 'resource', $link);

                echo '</p>';

                print_footer($course);

                //--------------------------------------------

                // MOODLE4XOOPS - J. BAUDIN

                //--------------------------------------------

                require_once "$CFG->dirroot/footer.php";

                //--------------------------------------------
            }
        } else {    /// not a popup at all
            add_to_log($course->id, 'resource', 'view', "view.php?id={$cm->id}", $resource->id, $cm->id);

            print_header(
                $pagetitle,
                $course->fullname,
                "$this->navigation {$resource->name}",
                '',
                '',
                true,
                update_module_button($cm->id, $course->id, $this->strresource),
                navmenu($course, $cm)
            );

            print_simple_box(format_text($resource->alltext, FORMAT_HTML, $formatoptions, $course->id), 'center', '', (string)$THEME->cellcontent, '20');

            $strlastmodified = get_string('lastmodified');

            echo "<center><p><font size=1>$strlastmodified: " . userdate($resource->timemodified) . '</p></center>';

            print_footer($course);

            //--------------------------------------------

            // MOODLE4XOOPS - J. BAUDIN

            //--------------------------------------------

            require_once "$CFG->dirroot/footer.php";

            //--------------------------------------------
        }
    }

    public function setup($form)
    {
        global $CFG, $usehtmleditor, $RESOURCE_WINDOW_OPTIONS;

        parent::setup($form);

        $strfilename = get_string('filename', 'resource');

        $strnote = get_string('note', 'resource');

        $strchooseafile = get_string('chooseafile', 'resource');

        $strnewwindow = get_string('newwindow', 'resource');

        $strnewwindowopen = get_string('newwindowopen', 'resource');

        $strsearch = get_string('searchweb', 'resource');

        foreach ($RESOURCE_WINDOW_OPTIONS as $optionname) {
            $stringname = "str$optionname";

            $$stringname = get_string("new$optionname", 'resource');

            $window->$optionname = '';

            $jsoption[] = "\"$optionname\"";
        }

        $frameoption = '"framepage"';

        $popupoptions = implode(',', $jsoption);

        $jsoption[] = $frameoption;

        $alloptions = implode(',', $jsoption);

        if ($form->instance) {     // Re-editing
            if (!$form->popup) {
                $windowtype = 'page';   // No popup text => in page

                foreach ($RESOURCE_WINDOW_OPTIONS as $optionname) {
                    $defaultvalue = "resource_popup$optionname";

                    $window->$optionname = $CFG->$defaultvalue;
                }
            } else {
                $windowtype = 'popup';

                $rawoptions = explode(',', $form->popup);

                foreach ($rawoptions as $rawoption) {
                    $option = explode('=', trim($rawoption));

                    $optionname = $option[0];

                    $optionvalue = $option[1];

                    if ('height' == $optionname or 'width' == $optionname) {
                        $window->$optionname = $optionvalue;
                    } elseif ($optionvalue) {
                        $window->$optionname = 'checked';
                    }
                }
            }
        } else {
            foreach ($RESOURCE_WINDOW_OPTIONS as $optionname) {
                $defaultvalue = "resource_popup$optionname";

                $window->$optionname = $CFG->$defaultvalue;
            }

            $windowtype = ($CFG->resource_popup) ? 'popup' : 'page';

            if (!isset($form->options)) {
                $form->options = '';
            }
        }

        include "$CFG->dirroot/mod/resource/type/html/html.html";

        parent::setup_end();
    }
}
