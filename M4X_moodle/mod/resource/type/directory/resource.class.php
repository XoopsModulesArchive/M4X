<?php

declare(strict_types=1);

// $Id: resource.class.php,v 1.10.2.2 2004/10/19 09:28:17 moodler Exp $

class resource_directory extends resource_base
{
    public function __construct($cmid = 0)
    {
        parent::resource_base($cmid);
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

        require_once '../../files/mimetypes.php';

        $subdir = $_GET['subdir'] ?? '';

        add_to_log($course->id, 'resource', 'view', "view.php?id={$cm->id}", $resource->id, $cm->id);

        if ($resource->reference) {
            $relativepath = "{$course->id}/{$resource->reference}";
        } else {
            $relativepath = (string)($course->id);
        }

        if ($subdir) {
            if (detect_munged_arguments($subdir, 0)) {
                error("The value for 'subdir' contains illegal characters!");
            }

            $relativepath = "$relativepath$subdir";

            $subs = explode('/', $subdir);

            array_shift($subs);

            $countsubs = count($subs);

            $count = 0;

            $subnav = "<a href=\"view.php?id={$cm->id}\">{$resource->name}</a>";

            $backsub = '';

            foreach ($subs as $sub) {
                $count++;

                if ($count < $countsubs) {
                    $backsub .= "/$sub";

                    $subnav .= " -> <a href=\"view.php?id={$cm->id}&subdir=$backsub\">$sub</a>";
                } else {
                    $subnav .= " -> $sub";
                }
            }
        } else {
            $subnav = $resource->name;
        }

        $pagetitle = strip_tags($course->shortname . ': ' . $resource->name);

        print_header(
            $pagetitle,
            $course->fullname,
            "$this->navigation $subnav",
            '',
            '',
            true,
            update_module_button($cm->id, $course->id, $this->strresource),
            navmenu($course, $cm)
        );

        if (isteacheredit($course->id)) {
            echo "<div align=\"right\"><img src=\"$CFG->pixpath/i/files.gif\" height=16 width=16 alt=\"\">&nbsp" . "<a href=\"$CFG->wwwroot/files/index.php?id={$course->id}&wdir=/{$resource->reference}$subdir\">" . get_string('editfiles') . '...</a></div>';
        }

        if (trim(strip_tags($resource->summary))) {
            $formatoptions->noclean = true;

            print_simple_box(format_text($resource->summary, FORMAT_MOODLE, $formatoptions, $course->id), 'center');

            print_spacer(10, 10);
        }

        $files = get_directory_list("$CFG->dataroot/$relativepath", 'moddata', false, true, true);

        if (!$files) {
            print_heading(get_string('nofilesyet'));

            print_footer($course);

            //--------------------------------------------

            // MOODLE4XOOPS - J. BAUDIN

            //--------------------------------------------

            require_once "$CFG->dirroot/footer.php";

            //--------------------------------------------

            exit;
        }

        print_simple_box_start('center', '', (string)$THEME->cellcontent, '0');

        $strftime = get_string('strftimedatetime');

        $strname = get_string('name');

        $strsize = get_string('size');

        $strmodified = get_string('modified');

        echo '<table cellpadding="4" cellspacing="1">';

        echo "<tr><th colspan=\"2\">$strname</th>" . "<th align=\"right\" colspan=\"2\">$strsize</th>" . "<th align=\"right\">$strmodified</th>" . '</tr>';

        foreach ($files as $file) {
            if (is_dir("$CFG->dataroot/$relativepath/$file")) {          // Must be a directory
                $icon = 'folder.gif';

                $relativeurl = '/view.php?blah';

                $filesize = display_size(get_directory_size("$CFG->dataroot/$relativepath/$file"));
            } else {
                $icon = mimeinfo('icon', $file);

                if ($CFG->slasharguments) {
                    $relativeurl = "/file.php/$relativepath/$file";
                } else {
                    $relativeurl = "/file.php?file=/$relativepath/$file";
                }

                $filesize = display_size(filesize("$CFG->dataroot/$relativepath/$file"));
            }

            echo '<tr>';

            echo '<td>';

            echo "<img src=\"$CFG->pixpath/f/$icon\" width=\"16\" height=\"16\">";

            echo '</td>';

            echo '<td nowrap="nowrap"><p>';

            if ('folder.gif' == $icon) {
                echo "<a href=\"view.php?id={$cm->id}&subdir=$subdir/$file\">$file</a>";
            } else {
                link_to_popup_window($relativeurl, "resourcedirectory{$resource->id}", (string)$file, 450, 600, '');
            }

            echo '</p></td>';

            echo '<td>&nbsp;</td>';

            echo '<td align="right" nowrap="nowrap"><p><font size="-1">';

            echo $filesize;

            echo '</font></p></td>';

            echo '<td align="right" nowrap="nowrap"><p><font size="-1">';

            echo userdate(filectime("$CFG->dataroot/$relativepath/$file"), $strftime);

            echo '</font></p></td>';

            echo '</tr>';
        }

        echo '</table>';

        print_simple_box_end();

        print_footer($course);

        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------
    }

    public function setup($form)
    {
        global $CFG;

        parent::setup($form);

        $rawdirs = get_directory_list("$CFG->dataroot/{$this->course->id}", 'moddata', true, true, false);

        $dirs = [];

        foreach ($rawdirs as $rawdir) {
            $dirs[$rawdir] = $rawdir;
        }

        include "$CFG->dirroot/mod/resource/type/directory/directory.html";

        parent::setup_end();
    }
}
