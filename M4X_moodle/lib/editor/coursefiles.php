<?php

// $Id: coursefiles.php,v 1.7.2.6 2004/11/04 16:56:29 moodler Exp $

//  Manage all uploaded files in a course file area

//  This file is a hack to files/index.php that removes
//  the headers and adds some controls so that images
//  can be selected within the Richtext editor.

//  All the Moodle-specific stuff is in this top section
//  Configuration and access control occurs here.
//  Must define:  USER, basedir, baseweb, html_header and html_footer
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
//  USER is a persistent variable using sessions

require '../../config.php';
require '../../files/mimetypes.php';

$id = required_param('id', PARAM_INT);
$file = optional_param('file', '', PARAM_PATH);
$wdir = optional_param('wdir', '', PARAM_PATH);
$action = optional_param('action', '', PARAM_ACTION);
$name = optional_param('name', '', PARAM_FILE);
$oldname = optional_param('oldname', '', PARAM_FILE);
$usecheckboxes = optional_param('usecheckboxes', 1, PARAM_INT);

if (!$course = get_record('course', 'id', $id)) {
    error("That's an invalid course id");
}

require_login($course->id);

if (!isteacheredit($course->id)) {
    error('Only teachers can edit files');
}

function html_footer()
{
    //--------------------------------------------

    // MOODLE4XOOPS - J. BAUDIN

    //--------------------------------------------

    require_once "$CFG->dirroot/footer.php";

    //--------------------------------------------

    echo "\n\n</body>\n</html>";
}

function html_header($course, $wdir, $formfield = '')
{
    global $CFG; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=<?php print_string('thischarset'); ?>">
    <title>coursefiles</title>
    <script language="javscript" type="text/javascript">
        <!--
        function set_value(params) {
            /// function's argument is an object containing necessary values
            /// to export parent window (url,isize,itype,iwidth,iheight, imodified)
            /// set values when user click's an image name.
            var upper = window.parent;
            var insimg = upper.document.getElementById('f_url');

            try {
                if (insimg != null) {
                    if (params.itype.indexOf("image/gif") == -1 && params.itype.indexOf("image/jpeg") == -1 && params.itype.indexOf("image/png") == -1) {
                        alert("<?php print_string('notimage', 'editor'); ?>");
                        return false;
                    }
                    for (field in params) {
                        var value = params[field];
                        switch (field) {
                            case "url"   :
                                upper.document.getElementById('f_url').value = value;
                                upper.ipreview.location.replace('popups/preview.php?id=' + <?php print($course->id); ?> +'&imageurl=' + value);
                                break;
                            case "isize" :
                                upper.document.getElementById('isize').value = value;
                                break;
                            case "itype" :
                                upper.document.getElementById('itype').value = value;
                                break;
                            case "iwidth":
                                upper.document.getElementById('f_width').value = value;
                                break;
                            case "iheight":
                                upper.document.getElementById('f_height').value = value;
                                break;
                        }
                    }
                } else {
                    for (field in params) {
                        var value = params[field];
                        switch (field) {
                            case "url" :
                                //upper.document.getElementById('f_href').value = value;
                                upper.opener.document.getElementById('f_href').value = value;
                                upper.close();
                                break;
                            //case "imodified" : upper.document.getElementById('imodified').value = value; break;
                            //case "isize" : upper.document.getElementById('isize').value = value; break;
                            //case "itype" : upper.document.getElementById('itype').value = value; break;
                        }
                    }
                }
            } catch (e) {
                alert("Something odd just occurred!!!");
            }
            return false;
        }

        function set_dir(strdir) {
            // sets wdir values
            var upper = window.parent.document;
            if (upper) {
                for (var i = 0; i < upper.forms.length; i++) {
                    var f = upper.forms[i];
                    try {
                        f.wdir.value = strdir;
                    } catch (e) {

                    }
                }
            }
        }

        function set_rename(strfile) {
            if (window.parent.document.irename != null) {
                window.parent.document.irename.file.value = strfile;
            }
            return true;
        }

        function reset_value() {
            var upper = window.parent.document;
            for (var i = 0; i < upper.forms.length; i++) {
                var f = upper.forms[i];
                for (var j = 0; j < f.elements.length; j++) {
                    var e = f.elements[j];
                    if (e.type != "submit" && e.type != "button" && e.type != "hidden") {
                        try {
                            e.value = "";
                        } catch (e) {
                        }
                    }
                }
            }

            var ren = upper.getElementById('irename');
            if (ren != null) {
                upper.irename.file.value = "";
            }
            var prev = window.parent.ipreview;
            if (prev != null) {
                prev.location.replace('about:blank');
            }
            var uploader = window.parent.document.forms['uploader'];
            if (uploader != null) {
                uploader.reset();
            }
            set_dir('<?php print(!empty($_REQUEST['wdir'])) ? $_REQUEST['wdir'] : ''; ?>');
            return true;
        }

        -->
    </script>
    <style type="text/css">
        <!--
        body {
            background-color: white;
            margin-top: 2px;
            margin-left: 4px;
            margin-right: 4px;
        }

        body, p, table, td, input, select, a {
            font-family: Tahoma, sans-serif;
            font-size: 11px;
        }

        select {
            position: absolute;
            top: -20px;
            left: 0px;
        }

        -->
    </style>
</head>
<body onload="reset_value();">

<?php
}

if (!$basedir = make_upload_directory((string)$course->id)) {
    error('The site administrator needs to fix the file permissions');
}

$baseweb = $CFG->wwwroot;

//  End of configuration and access control

if (!$wdir) {
    $wdir = '/';
}

if (('/' != $wdir and detect_munged_arguments($wdir, 0)) or ('' != $file and detect_munged_arguments($file, 0))) {
    $message = 'Error: Directories can not contain ".."';

    $wdir = '/';

    $action = '';
}

switch ($action) {
    case 'upload':
        html_header($course, $wdir);
        require_once $CFG->dirroot . '/lib/uploadlib.php';

        if (!empty($save) and confirm_sesskey()) {
            $um = new upload_manager('userfile', false, false, $course, false, 0);

            $dir = "$basedir$wdir";

            if ($um->process_file_uploads($dir)) {
                notify(get_string('uploadedfile'));
            }

            // um will take care of error reporting.

            displaydir($wdir);
        } else {
            $upload_max_filesize = get_max_upload_file_size($CFG->maxbytes);

            $filesize = display_size($upload_max_filesize);

            $struploadafile = get_string('uploadafile');

            $struploadthisfile = get_string('uploadthisfile');

            $strmaxsize = get_string('maxsize', '', $filesize);

            $strcancel = get_string('cancel');

            echo "<p>$struploadafile ($strmaxsize) --> <strong>$wdir</strong>";

            echo "<table border=\"0\"><tr><td colspan=\"2\">\n";

            echo "<form enctype=\"multipart/form-data\" method=\"post\" action=\"coursefiles.php\">\n";

            upload_print_form_fragment(1, ['userfile'], null, false, null, $course->maxbytes, 0, false);

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

            echo " <input type=\"hidden\" name=\"action\" value=\"upload\">\n";

            echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">\n";

            echo ' </td><tr><td align="right">';

            echo " <input type=\"submit\" name=\"save\" value=\"$struploadthisfile\">\n";

            echo "</form>\n";

            echo "</td>\n<td>\n";

            echo "<form action=\"coursefiles.php\" method=\"get\">\n";

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

            echo " <input type=\"hidden\" name=\"action\" value=\"cancel\">\n";

            echo " <input type=\"submit\" value=\"$strcancel\">\n";

            echo "</form>\n";

            echo "</td>\n</tr>\n</table>\n";
        }
        html_footer();
        //--------------------------------------------
        // MOODLE4XOOPS - J. BAUDIN
        //--------------------------------------------
        require_once "$CFG->dirroot/footer.php";
        //--------------------------------------------
        break;
    case 'delete':
        if (!empty($confirm) and confirm_sesskey()) {
            html_header($course, $wdir);

            foreach ($USER->filelist as $file) {
                $fullfile = $basedir . $file;

                if (!fulldelete($fullfile)) {
                    echo "<br>Error: Could not delete: $fullfile";
                }
            }

            clearfilelist();

            displaydir($wdir);

            html_footer();

            //--------------------------------------------

            // MOODLE4XOOPS - J. BAUDIN

            //--------------------------------------------

            require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------
        } else {
            html_header($course, $wdir);

            if (setfilelist($_POST)) {
                echo '<p align=center>' . get_string('deletecheckwarning') . ':</p>';

                print_simple_box_start('center');

                printfilelist($USER->filelist);

                print_simple_box_end();

                echo '<br>';

                notice_yesno(
                    get_string('deletecheckfiles'),
                    "coursefiles.php?id=$id&amp;wdir=$wdir&amp;action=delete&amp;confirm=1&amp;sesskey=$USER->sesskey",
                    "coursefiles.php?id=$id&amp;wdir=$wdir&amp;action=cancel"
                );
            } else {
                displaydir($wdir);
            }

            html_footer();

            //--------------------------------------------

            // MOODLE4XOOPS - J. BAUDIN

            //--------------------------------------------

            require_once "$CFG->dirroot/footer.php";

            //--------------------------------------------
        }
        break;
    case 'move':
        html_header($course, $wdir);
        if ($count = setfilelist($_POST) and confirm_sesskey()) {
            $USER->fileop = $action;

            $USER->filesource = $wdir;

            echo '<p align="center">';

            print_string('selectednowmove', 'moodle', $count);

            echo '</p>';
        }
        displaydir($wdir);
        html_footer();
        //--------------------------------------------
        // MOODLE4XOOPS - J. BAUDIN
        //--------------------------------------------
        require_once "$CFG->dirroot/footer.php";
        //--------------------------------------------
        break;
    case 'paste':
        html_header($course, $wdir);
        if (isset($USER->fileop) and 'move' == $USER->fileop and confirm_sesskey()) {
            foreach ($USER->filelist as $file) {
                $shortfile = basename($file);

                $oldfile = $basedir . $file;

                $newfile = $basedir . $wdir . '/' . $shortfile;

                if (!rename($oldfile, $newfile)) {
                    echo "<p>Error: $shortfile not moved";
                }
            }
        }
        clearfilelist();
        displaydir($wdir);
        html_footer();
        //--------------------------------------------
        // MOODLE4XOOPS - J. BAUDIN
        //--------------------------------------------
        require_once "$CFG->dirroot/footer.php";
        //--------------------------------------------
        break;
    case 'rename':
        if (!empty($name) and confirm_sesskey()) {
            html_header($course, $wdir);

            $name = clean_filename($name);

            $oldname = clean_filename($oldname);

            if (file_exists($basedir . $wdir . '/' . $name)) {
                echo "Error: $name already exists!";
            } elseif (!rename($basedir . $wdir . '/' . $oldname, $basedir . $wdir . '/' . $name)) {
                echo "Error: could not rename $oldname to $name";
            }

            displaydir($wdir);
        } else {
            $strrename = get_string('rename');

            $strcancel = get_string('cancel');

            $strrenamefileto = get_string('renamefileto', 'moodle', $file);

            html_header($course, $wdir, 'form.name');

            echo "<p>$strrenamefileto:";

            echo "<table border=\"0\">\n<tr>\n<td>\n";

            echo "<form action=\"coursefiles.php\" method=\"post\" name=\"form\">\n";

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

            echo " <input type=\"hidden\" name=\"action\" value=\"rename\">\n";

            echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">\n";

            echo " <input type=\"hidden\" name=oldname value=\"$file\">\n";

            echo " <input type=\"text\" name=\"name\" size=\"35\" value=\"$file\">\n";

            echo " <input type=\"submit\" value=\"$strrename\">\n";

            echo "</form>\n";

            echo "</td><td>\n";

            echo "<form action=\"coursefiles.php\" method=\"get\">\n";

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

            echo " <input type=\"hidden\" name=\"action\" value=\"cancel\">\n";

            echo " <input type=\"submit\" value=\"$strcancel\">\n";

            echo '</form>';

            echo "</td></tr>\n</table>\n";
        }
        html_footer();
        //--------------------------------------------
        // MOODLE4XOOPS - J. BAUDIN
        //--------------------------------------------
        require_once "$CFG->dirroot/footer.php";
        //--------------------------------------------
        break;
    case 'mkdir':
        if (!empty($name) and confirm_sesskey()) {
            html_header($course, $wdir);

            $name = clean_filename($name);

            if (file_exists("$basedir$wdir/$name")) {
                echo "Error: $name already exists!";
            } elseif (!make_upload_directory("$course->id/$wdir/$name")) {
                echo "Error: could not create $name";
            }

            displaydir($wdir);
        } else {
            $strcreate = get_string('create');

            $strcancel = get_string('cancel');

            $strcreatefolder = get_string('createfolder', 'moodle', $wdir);

            html_header($course, $wdir, 'form.name');

            echo "<p>$strcreatefolder:";

            echo "<table border=\"0\">\n<tr><td>\n";

            echo "<form action=\"coursefiles.php\" method=\"post\" name=\"form\">\n";

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

            echo " <input type=\"hidden\" name=\"action\" value=\"mkdir\">\n";

            echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">\n";

            echo " <input type=\"text\" name=\"name\" size=\"35\">\n";

            echo " <input type=\"submit\" value=\"$strcreate\">\n";

            echo "</form>\n";

            echo "</td><td>\n";

            echo "<form action=\"coursefiles.php\" method=\"get\">\n";

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

            echo " <input type=\"hidden\" name=\"action\" value=\"cancel\">\n";

            echo " <input type=\"submit\" value=\"$strcancel\">\n";

            echo "</form>\n";

            echo "</td>\n</tr>\n</table>\n";
        }
        html_footer();
        //--------------------------------------------
        // MOODLE4XOOPS - J. BAUDIN
        //--------------------------------------------
        require_once "$CFG->dirroot/footer.php";
        //--------------------------------------------
        break;
    case 'edit':
        html_header($course, $wdir);
        if (isset($text) and confirm_sesskey()) {
            $fileptr = fopen($basedir . $file, 'wb');

            fwrite($fileptr, stripslashes($text));

            fclose($fileptr);

            displaydir($wdir);
        } else {
            $streditfile = get_string('edit', '', "<strong>$file</strong>");

            $fileptr = fopen($basedir . $file, 'rb');

            $contents = fread($fileptr, filesize($basedir . $file));

            fclose($fileptr);

            print_heading((string)$streditfile);

            echo "<table><tr><td colspan=\"2\">\n";

            echo "<form action=\"coursefiles.php\" method=\"post\" name=\"form\" $onsubmit>\n";

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

            echo " <input type=\"hidden\" name=file value=\"$file\">";

            echo " <input type=\"hidden\" name=\"action\" value=\"edit\">\n";

            echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">\n";

            print_textarea(false, 25, 80, 680, 400, 'text', $contents);

            echo "</td>\n</tr>\n<tr>\n<td>\n";

            echo ' <input type="submit" value="' . get_string('savechanges') . "\">\n";

            echo "</form>\n";

            echo "</td>\n<td>\n";

            echo "<form action=\"coursefiles.php\" method=\"get\">\n";

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

            echo " <input type=\"hidden\" name=\"action\" value=\"cancel\">\n";

            echo ' <input type="submit" value="' . get_string('cancel') . "\">\n";

            echo "</form>\n";

            echo "</td></tr></table>\n";

            if ($usehtmleditor) {
                print_richedit_javascript('form', 'text', 'yes');
            }
        }
        html_footer();
        //--------------------------------------------
        // MOODLE4XOOPS - J. BAUDIN
        //--------------------------------------------
        require_once "$CFG->dirroot/footer.php";
        //--------------------------------------------
        break;
    case 'zip':
        if (!empty($name) and confirm_sesskey()) {
            html_header($course, $wdir);

            $name = clean_filename($name);

            $files = [];

            foreach ($USER->filelist as $file) {
                $files[] = "$basedir/$file";
            }

            if (!zip_files($files, "$basedir/$wdir/$name")) {
                error(get_string('zipfileserror', 'error'));
            }

            clearfilelist();

            displaydir($wdir);
        } else {
            html_header($course, $wdir, 'form.name');

            if (setfilelist($_POST)) {
                echo '<p align="center">' . get_string('youareabouttocreatezip') . ':</p>';

                print_simple_box_start('center');

                printfilelist($USER->filelist);

                print_simple_box_end();

                echo '<br>';

                echo '<p align="center">' . get_string('whattocallzip');

                echo "<table border=\"0\">\n<tr>\n<td>\n";

                echo "<form action=\"coursefiles.php\" method=\"post\" name=\"form\">\n";

                echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

                echo " <input type=\"hidden\" name=\"action\" value=\"zip\">\n";

                echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">\n";

                echo " <INPUT TYPE=text name=name SIZE=35 value=\"new.zip\">\n";

                echo ' <input type="submit" value="' . get_string('createziparchive') . '">';

                echo "</form>\n";

                echo "</td>\n<td>\n";

                echo "<form action=\"coursefiles.php\" method=\"get\">\n";

                echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

                echo " <input type=\"hidden\" name=\"action\" value=\"cancel\">\n";

                echo ' <input type="submit" value="' . get_string('cancel') . "\">\n";

                echo "</form>\n";

                echo "</td>\n</tr>\n</table>\n";
            } else {
                displaydir($wdir);

                clearfilelist();
            }
        }
        html_footer();
        //--------------------------------------------
        // MOODLE4XOOPS - J. BAUDIN
        //--------------------------------------------
        require_once "$CFG->dirroot/footer.php";
        //--------------------------------------------
        break;
    case 'unzip':
        html_header($course, $wdir);
        if (!empty($file) and confirm_sesskey()) {
            $strok = get_string('ok');

            $strunpacking = get_string('unpacking', '', $file);

            echo "<p align=\"center\">$strunpacking:</p>";

            $file = basename($file);

            if (!unzip_file("$basedir/$wdir/$file")) {
                error(get_string('unzipfileserror', 'error'));
            }

            echo "<center><form action=\"coursefiles.php\" method=\"get\">\n";

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

            echo " <input type=\"hidden\" name=\"action\" value=\"cancel\">\n";

            echo " <input type=\"submit\" value=\"$strok\">\n";

            echo "</form>\n";

            echo "</center>\n";
        } else {
            displaydir($wdir);
        }
        html_footer();
        //--------------------------------------------
        // MOODLE4XOOPS - J. BAUDIN
        //--------------------------------------------
        require_once "$CFG->dirroot/footer.php";
        //--------------------------------------------
        break;
    case 'listzip':
        html_header($course, $wdir);
        if (!empty($file) and confirm_sesskey()) {
            $strname = get_string('name');

            $strsize = get_string('size');

            $strmodified = get_string('modified');

            $strok = get_string('ok');

            $strlistfiles = get_string('listfiles', '', $file);

            echo "<p align=\"center\">$strlistfiles:</p>";

            $file = basename($file);

            require_once dirname(__DIR__) . '/pclzip/pclzip.lib.php';

            $archive = new PclZip("$basedir/$wdir/$file");

            if (!$list = $archive->listContent("$basedir/$wdir")) {
                notify($archive->errorInfo(true));
            } else {
                echo "<table cellpadding=\"4\" cellspacing=\"2\" border=\"0\">\n";

                echo "<tr>\n<th align=\"left\">$strname</th><th align=\"right\">$strsize</th><th align=\"right\">$strmodified</th></tr>";

                foreach ($list as $item) {
                    echo '<tr>';

                    print_cell('left', $item['filename']);

                    if (!$item['folder']) {
                        print_cell('right', display_size($item['size']));
                    } else {
                        echo "<td>&nbsp;</td>\n";
                    }

                    $filedate = userdate($item['mtime'], get_string('strftimedatetime'));

                    print_cell('right', $filedate);

                    echo "</tr>\n";
                }

                echo "</table>\n";
            }

            echo "<br><center><form action=\"coursefiles.php\" method=\"get\">\n";

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

            echo " <input type=\"hidden\" name=\"action\" value=\"cancel\">\n";

            echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">\n";

            echo " <input type=\"submit\" value=\"$strok\">\n";

            echo "</form>\n";

            echo "</center>\n";
        } else {
            displaydir($wdir);
        }
        html_footer();
        //--------------------------------------------
        // MOODLE4XOOPS - J. BAUDIN
        //--------------------------------------------
        require_once "$CFG->dirroot/footer.php";
        //--------------------------------------------
        break;
    case 'cancel':
        clearfilelist();

        // no break
    default:
        html_header($course, $wdir);
        displaydir($wdir);
        html_footer();
        //--------------------------------------------
        // MOODLE4XOOPS - J. BAUDIN
        //--------------------------------------------
        require_once "$CFG->dirroot/footer.php";
        //--------------------------------------------
        break;
}

/// FILE FUNCTIONS ///////////////////////////////////////////////////////////

function fulldelete($location)
{
    if (is_dir($location)) {
        $currdir = opendir($location);

        while ($file = readdir($currdir)) {
            if ('..' != $file && '.' != $file) {
                $fullfile = $location . '/' . $file;

                if (is_dir($fullfile)) {
                    if (!fulldelete($fullfile)) {
                        return false;
                    }
                } else {
                    if (!unlink($fullfile)) {
                        return false;
                    }
                }
            }
        }

        closedir($currdir);

        if (!rmdir($location)) {
            return false;
        }
    } else {
        if (!unlink($location)) {
            return false;
        }
    }

    return true;
}

function setfilelist($VARS)
{
    global $USER;

    $USER->filelist = [];

    $USER->fileop = '';

    $count = 0;

    foreach ($VARS as $key => $val) {
        if ('file' == mb_substr($key, 0, 4)) {
            $count++;

            $USER->filelist[] = rawurldecode($val);
        }
    }

    return $count;
}

function clearfilelist()
{
    global $USER;

    $USER->filelist = [];

    $USER->fileop = '';
}

function printfilelist($filelist)
{
    global $basedir, $CFG;

    foreach ($filelist as $file) {
        if (is_dir($basedir . $file)) {
            echo "<img src=\"$CFG->pixpath/f/folder.gif\" height=\"16\" width=\"16\" alt=\"\"> $file<br>";

            $subfilelist = [];

            $currdir = opendir($basedir . $file);

            while ($subfile = readdir($currdir)) {
                if ('..' != $subfile && '.' != $subfile) {
                    $subfilelist[] = $file . '/' . $subfile;
                }
            }

            printfilelist($subfilelist);
        } else {
            $icon = mimeinfo('icon', $file);

            echo "<img src=\"$CFG->pixpath/f/$icon\"  height=\"16\" width=\"16\" alt=\"\"> $file<br>";
        }
    }
}

function print_cell($alignment = 'center', $text = '&nbsp;')
{
    echo "<td align=\"$alignment\" nowrap=\"nowrap\">\n";

    echo (string)$text;

    echo "</td>\n";
}

function get_image_size($filepath)
{
    /// This function get's the image size

    /// Check if file exists

    if (!file_exists($filepath)) {
        return false;
    }

    /// Get the mime type so it really an image.

    if ('image.gif' != mimeinfo('icon', basename($filepath))) {
        return false;
    }

    $array_size = getimagesize($filepath);

    return $array_size;
    unset($filepath, $array_size);
}

function displaydir($wdir)
{
    //  $wdir == / or /a or /a/b/c/d  etc

    global $basedir;

    global $usecheckboxes;

    global $id;

    global $USER, $CFG;

    $fullpath = $basedir . $wdir;

    $directory = opendir($fullpath);             // Find all files

    while ($file = readdir($directory)) {
        if ('.' == $file || '..' == $file) {
            continue;
        }

        if (is_dir($fullpath . '/' . $file)) {
            $dirlist[] = $file;
        } else {
            $filelist[] = $file;
        }
    }

    closedir($directory);

    $strfile = get_string('file');

    $strname = get_string('name');

    $strsize = get_string('size');

    $strmodified = get_string('modified');

    $straction = get_string('action');

    $strmakeafolder = get_string('makeafolder');

    $struploadafile = get_string('uploadafile');

    $strwithchosenfiles = get_string('withchosenfiles');

    $strmovetoanotherfolder = get_string('movetoanotherfolder');

    $strmovefilestohere = get_string('movefilestohere');

    $strdeletecompletely = get_string('deletecompletely');

    $strcreateziparchive = get_string('createziparchive');

    $strrename = get_string('rename');

    $stredit = get_string('edit');

    $strunzip = get_string('unzip');

    $strlist = get_string('list');

    $strchoose = get_string('choose');

    echo "<form action=\"coursefiles.php\" method=\"post\" name=\"dirform\">\n";

    echo "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\" width=\"100%\">\n";

    if ('/' == $wdir) {
        $wdir = '';
    } else {
        $bdir = str_replace('/' . basename($wdir), '', $wdir);

        if ('/' == $bdir) {
            $bdir = '';
        }

        print "<tr>\n<td colspan=\"5\">";

        print "<a href=\"coursefiles.php?id=$id&amp;wdir=$bdir&amp;usecheckboxes=$usecheckboxes\" onclick=\"return reset_value();\">";

        print "<img src=\"$CFG->wwwroot/lib/editor/images/folderup.gif\" height=\"14\" width=\"24\" border=\"0\" alt=\"Move up\">";

        print "</a></td>\n</tr>\n";
    }

    $count = 0;

    if (!empty($dirlist)) {
        asort($dirlist);

        foreach ($dirlist as $dir) {
            $count++;

            $filename = $fullpath . '/' . $dir;

            $fileurl = rawurlencode($wdir . '/' . $dir);

            $filesafe = rawurlencode($dir);

            $filedate = userdate(filemtime($filename), '%d %b %Y, %I:%M %p');

            echo '<tr>';

            if ($usecheckboxes) {
                print_cell('center', "<input type=\"checkbox\" name=\"file$count\" value=\"$fileurl\" onclick=\"return set_rename('$filesafe');\">");
            }

            print_cell(
                'left',
                "<a href=\"coursefiles.php?id=$id&amp;wdir=$fileurl\" onclick=\"return reset_value();\"><img src=\"$CFG->pixpath/f/folder.gif\" height=\"16\" width=\"16\" border=\"0\" alt=\"folder\"></a> <a href=\"coursefiles.php?id=$id&amp;wdir=$fileurl&amp;usecheckboxes=$usecheckboxes\" onclick=\"return reset_value();\">"
                . htmlspecialchars($dir, ENT_QUOTES | ENT_HTML5)
                . '</a>'
            );

            print_cell('right', '&nbsp;');

            print_cell('right', $filedate);

            echo '</tr>';
        }
    }

    if (!empty($filelist)) {
        asort($filelist);

        foreach ($filelist as $file) {
            $icon = mimeinfo('icon', $file);

            $imgtype = mimeinfo('type', $file);

            $count++;

            $filename = $fullpath . '/' . $file;

            $fileurl = "$wdir/$file";

            $filesafe = rawurlencode($file);

            $fileurlsafe = rawurlencode($fileurl);

            $filedate = userdate(filemtime($filename), '%d %b %Y, %I:%M %p');

            $dimensions = get_image_size($filename);

            if ($dimensions) {
                $imgwidth = $dimensions[0];

                $imgheight = $dimensions[1];
            } else {
                $imgwidth = 'Unknown';

                $imgheight = 'Unknown';
            }

            unset($dimensions);

            echo "<tr>\n";

            if ($usecheckboxes) {
                print_cell('center', "<input type=\"checkbox\" name=\"file$count\" value=\"$fileurl\" onclick=\"return set_rename('$filesafe');\">");
            }

            echo '<td align="left" nowrap="nowrap">';

            if ($CFG->slasharguments) {
                $ffurl = "/file.php/$id$fileurl";
            } else {
                $ffurl = "/file.php?file=/$id$fileurl";
            }

            link_to_popup_window(
                $ffurl,
                'display',
                "<img src=\"$CFG->pixpath/f/$icon\" height=\"16\" width=\"16\" border=\"0\" align=\"middle\" alt=\"$strfile\">",
                480,
                640
            );

            $file_size = filesize($filename);

            echo "<a onclick=\"return set_value(info = {url: '" . $CFG->wwwroot . $ffurl . "',";

            echo " isize: '" . $file_size . "', itype: '" . $imgtype . "', iwidth: '" . $imgwidth . "',";

            echo " iheight: '" . $imgheight . "', imodified: '" . $filedate . "' })\" href=\"#\">$file</a>";

            echo "</td>\n";

            if ('zip.gif' == $icon) {
                $edittext = "<a href=\"coursefiles.php?id=$id&amp;wdir=$wdir&amp;file=$fileurl&amp;action=unzip&amp;sesskey=$USER->sesskey\">$strunzip</a>&nbsp;";

                $edittext .= "<a href=\"coursefiles.php?id=$id&amp;wdir=$wdir&amp;file=$fileurl&amp;action=listzip&amp;sesskey=$USER->sesskey\">$strlist</a> ";
            } else {
                $edittext = '&nbsp;';
            }

            print_cell('right', "$edittext ");

            print_cell('right', $filedate);

            echo "</tr>\n";
        }
    }

    echo "</table>\n";

    if (empty($wdir)) {
        $wdir = '/';
    }

    echo "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\">\n";

    echo "<tr>\n<td>";

    echo "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";

    echo "<input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

    echo "<input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">\n";

    $options = [
        'move' => (string)$strmovetoanotherfolder,
        'delete' => (string)$strdeletecompletely,
        'zip' => (string)$strcreateziparchive,
    ];

    if (!empty($count)) {
        choose_from_menu($options, 'action', '', "$strwithchosenfiles...", 'javascript:document.dirform.submit()');
    }

    if (!empty($USER->fileop) and ('move' == $USER->fileop) and ($USER->filesource != $wdir)) {
        echo "<form action=\"coursefiles.php\" method=\"get\">\n";

        echo " <input type=\"hidden\" name=\"id\" value=\"$id\">\n";

        echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">\n";

        echo " <input type=\"hidden\" name=\"action\" value=\"paste\">\n";

        echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">\n";

        echo " <input type=\"submit\" value=\"$strmovefilestohere\">\n";

        echo '</form>';
    }

    echo "</td></tr>\n";

    echo "</table>\n";

    echo "</form>\n";
}

?>
