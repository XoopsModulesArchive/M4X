<?php

// $Id: index.php,v 1.47.2.12 2004/11/10 15:47:31 moodler Exp $

//  Manage all uploaded files in a course file area

//  All the Moodle-specific stuff is in this top section
//  Configuration and access control occurs here.
//  Must define:  USER, basedir, baseweb, html_header and html_footer
//  USER is a persistent variable using sessions

require '../config.php';
require 'mimetypes.php';

$id = required_param('id', PARAM_INT);
$file = optional_param('file', '', PARAM_PATH);
$wdir = optional_param('wdir', '', PARAM_PATH);
$action = optional_param('action', '', PARAM_ACTION);
$name = optional_param('name', '', PARAM_FILE);
$oldname = optional_param('oldname', '', PARAM_FILE);
$choose = optional_param('choose', '', PARAM_CLEAN);

if ($choose) {
    if (2 != substr_count($choose, '.') + 1) {
        error('Incorrect format for choose parameter');
    }
}

if (!$course = get_record('course', 'id', $id)) {
    error("That's an invalid course id");
}

require_login($course->id);

if (!isteacheredit($course->id)) {
    error('You need to be a teacher with editing privileges');
}

function html_footer()
{
    global $course, $choose;

    if ($choose) {
        echo '</td></tr></table></body></html>';
    } else {
        echo '</td></tr></table></body></html>';

        print_footer($course);
    }
}

function html_header($course, $wdir, $formfield = '')
{
    global $CFG, $THEME, $ME, $choose;

    if (!$site = get_site()) {
        error('Invalid site!');
    }

    if ($course->id == $site->id) {
        $strfiles = get_string('sitefiles');
    } else {
        $strfiles = get_string('files');
    }

    if ('/' == $wdir) {
        $fullnav = (string)$strfiles;
    } else {
        $dirs = explode('/', $wdir);

        $numdirs = count($dirs);

        $link = '';

        $navigation = '';

        for ($i = 1; $i < $numdirs - 1; $i++) {
            $navigation .= ' -> ';

            $link .= '/' . urlencode($dirs[$i]);

            $navigation .= '<a href="' . $ME . "?id=$course->id&amp;wdir=$link&amp;choose=$choose\">" . $dirs[$i] . '</a>';
        }

        $fullnav = '<a href="' . $ME . "?id=$course->id&amp;wdir=/&amp;choose=$choose\">$strfiles</a> $navigation -> " . $dirs[$numdirs - 1];
    }

    if ($choose) {
        print_header();

        $chooseparts = explode('.', $choose); ?>
        <script language="javascript" type="text/javascript">
            <!--
            function set_value(txt) {
                opener.document.forms['<?php echo $chooseparts[0] . "']." . $chooseparts[1] ?>.value = txt;
                window.close();
            }

            -->
        </script>

        <?php
        $fullnav = str_replace('->', '&raquo;', "$course->shortname -> $fullnav");

        echo '<table border="0" cellpadding="3" cellspacing="0" width="100%">';

        echo '<tr>';

        echo '<td bgcolor="' . $THEME->cellheading . '" class="navbar">';

        echo '<font size="2"><b>' . $fullnav . '</b></font>';

        echo '</td>';

        echo '</tr>';

        echo '</table>';

        if ($course->id == $site->id) {
            print_heading(get_string('publicsitefileswarning'), 'center', 2);
        }
    } else {
        if ($course->id == $site->id) {
            print_header(
                "$course->shortname: $strfiles",
                (string)$course->fullname,
                "<a href=\"../$CFG->admin/index.php\">" . get_string('administration') . "</a> -> $fullnav",
                $formfield
            );

            print_heading(get_string('publicsitefileswarning'), 'center', 2);
        } else {
            print_header(
                "$course->shortname: $strfiles",
                (string)$course->fullname,
                "<a href=\"../course/view.php?id=$course->id\">$course->shortname" . "</a> -> $fullnav",
                $formfield
            );
        }
    }

    echo '<table border=0 align=center cellspacing=3 cellpadding=3 width=640>';

    echo '<tr>';

    echo '<td colspan="2">';
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

if ('/backupdata' == $wdir) {
    if (!make_upload_directory("$course->id/backupdata")) {   // Backup folder
        error('Could not create backupdata folder.  The site administrator needs to fix the file permissions');
    }
}

switch ($action) {
    case 'upload':
        html_header($course, $wdir);
        require_once $CFG->dirroot . '/lib/uploadlib.php';

        if (!empty($save) and confirm_sesskey()) {
            $course->maxbytes = 0;  // We are ignoring course limits

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

            echo "<p>$struploadafile ($strmaxsize) --> <b>$wdir</b>";

            echo '<table><tr><td colspan="2">';

            echo '<form enctype="multipart/form-data" method="post" action="index.php">';

            echo ' <input type="hidden" name="choose" value="' . $choose . '">';

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

            echo ' <input type="hidden" name="action" value="upload">';

            echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">";

            upload_print_form_fragment(1, ['userfile'], null, false, null, $upload_max_filesize, 0, false);

            echo ' </td><tr><td width="10">';

            echo " <input type=\"submit\" name=\"save\" value=\"$struploadthisfile\">";

            echo '</form>';

            echo '</td><td width="100%">';

            echo '<form action="index.php" method="get">';

            echo ' <input type="hidden" name="choose" value="' . $choose . '">';

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

            echo ' <input type="hidden" name="action" value="cancel">';

            echo " <input type=\"submit\" value=\"$strcancel\">";

            echo '</form>';

            echo '</td></tr></table>';
        }
        html_footer();
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
        } else {
            html_header($course, $wdir);

            if (setfilelist($_POST)) {
                echo '<p align="center">' . get_string('deletecheckwarning') . ':</p>';

                print_simple_box_start('center');

                printfilelist($USER->filelist);

                print_simple_box_end();

                echo '<br>';

                notice_yesno(
                    get_string('deletecheckfiles'),
                    "index.php?id=$id&amp;wdir=$wdir&amp;action=delete&amp;confirm=1&amp;sesskey=$USER->sesskey",
                    "index.php?id=$id&amp;wdir=$wdir&amp;action=cancel"
                );
            } else {
                displaydir($wdir);
            }

            html_footer();
        }
        break;
    case 'move':
        html_header($course, $wdir);
        if (($count = setfilelist($_POST)) and confirm_sesskey()) {
            $USER->fileop = $action;

            $USER->filesource = $wdir;

            echo '<p align="center">';

            print_string('selectednowmove', 'moodle', $count);

            echo '</p>';
        }
        displaydir($wdir);
        html_footer();
        break;
    case 'paste':
        html_header($course, $wdir);
        if (isset($USER->fileop) and ('move' == $USER->fileop) and confirm_sesskey()) {
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
        break;
    case 'rename':
        if (!empty($name) and confirm_sesskey()) {
            html_header($course, $wdir);

            $name = clean_filename($name);

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

            echo '<table><tr><td>';

            echo '<form action="index.php" method="post" name="form">';

            echo ' <input type="hidden" name="choose" value="' . $choose . '">';

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

            echo ' <input type="hidden" name="action" value="rename">';

            echo " <input type=\"hidden\" name=\"oldname\" value=\"$file\">";

            echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">";

            echo " <input type=\"text\" name=\"name\" size=\"35\" value=\"$file\">";

            echo " <input type=\"submit\" value=\"$strrename\">";

            echo '</form>';

            echo '</td><td>';

            echo '<form action="index.php" method="get">';

            echo ' <input type="hidden" name="choose" value="' . $choose . '">';

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

            echo ' <input type="hidden" name="action" value="cancel">';

            echo " <input type=\"submit\" value=\"$strcancel\">";

            echo '</form>';

            echo '</td></tr></table>';
        }
        html_footer();
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

            echo '<table><tr><td>';

            echo '<form action="index.php" method="post" name="form">';

            echo ' <input type="hidden" name="choose" value="' . $choose . '">';

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

            echo ' <input type="hidden" name="action" value="mkdir">';

            echo ' <input type="text" name="name" size="35">';

            echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">";

            echo " <input type=\"submit\" value=\"$strcreate\">";

            echo '</form>';

            echo '</td><td>';

            echo '<form action="index.php" method="get">';

            echo ' <input type="hidden" name="choose" value="' . $choose . '">';

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

            echo ' <input type="hidden" name="action" value="cancel">';

            echo " <input type=\"submit\" value=\"$strcancel\">";

            echo '</form>';

            echo '</td></tr></table>';
        }
        html_footer();
        break;
    case 'edit':
        html_header($course, $wdir);
        if (isset($text) and confirm_sesskey()) {
            $fileptr = fopen($basedir . $file, 'wb');

            fwrite($fileptr, stripslashes($text));

            fclose($fileptr);

            displaydir($wdir);
        } else {
            $streditfile = get_string('edit', '', "<b>$file</b>");

            $fileptr = fopen($basedir . $file, 'rb');

            $contents = fread($fileptr, filesize($basedir . $file));

            fclose($fileptr);

            if ('text/html' == mimeinfo('type', $file)) {
                $usehtmleditor = can_use_html_editor();
            } else {
                $usehtmleditor = false;
            }

            $usehtmleditor = false;    // Always keep it off for now

            print_heading((string)$streditfile);

            echo '<table><tr><td colspan="2">';

            echo '<form action="index.php" method="post" name="form">';

            echo ' <input type="hidden" name="choose" value="' . $choose . '">';

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

            echo " <input type=\"hidden\" name=\"file\" value=\"$file\">";

            echo ' <input type="hidden" name="action" value="edit">';

            echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">";

            print_textarea($usehtmleditor, 25, 80, 680, 400, 'text', $contents);

            echo '</td></tr><tr><td>';

            echo ' <input type="submit" value="' . get_string('savechanges') . '">';

            echo '</form>';

            echo '</td><td>';

            echo '<form action="index.php" method="get">';

            echo ' <input type="hidden" name="choose" value="' . $choose . '">';

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

            echo ' <input type="hidden" name="action" value="cancel">';

            echo ' <input type="submit" value="' . get_string('cancel') . '">';

            echo '</form>';

            echo '</td></tr></table>';

            if ($usehtmleditor) {
                use_html_editor();
            }
        }
        html_footer();
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

                echo '<table><tr><td>';

                echo '<form action="index.php" method="post" name="form">';

                echo ' <input type="hidden" name="choose" value="' . $choose . '">';

                echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

                echo ' <input type="hidden" name="action" value="zip">';

                echo ' <input type="text" name="name" size="35" value="new.zip">';

                echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">";

                echo ' <input type="submit" value="' . get_string('createziparchive') . '">';

                echo '</form>';

                echo '</td><td>';

                echo '<form action="index.php" method="get">';

                echo ' <input type="hidden" name="choose" value="' . $choose . '">';

                echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

                echo ' <input type="hidden" name="action" value="cancel">';

                echo ' <input type="submit" value="' . get_string('cancel') . '">';

                echo '</form>';

                echo '</td></tr></table>';
            } else {
                displaydir($wdir);

                clearfilelist();
            }
        }
        html_footer();
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

            echo '<center><form action="index.php" method="get">';

            echo ' <input type="hidden" name="choose" value="' . $choose . '">';

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

            echo ' <input type="hidden" name="action" value="cancel">';

            echo " <input type=\"submit\" value=\"$strok\">";

            echo '</form>';

            echo '</center>';
        } else {
            displaydir($wdir);
        }
        html_footer();
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

            require_once "$CFG->libdir/pclzip/pclzip.lib.php";

            $archive = new PclZip(cleardoubleslashes("$basedir/$wdir/$file"));

            if (!$list = $archive->listContent(cleardoubleslashes("$basedir/$wdir"))) {
                notify($archive->errorInfo(true));
            } else {
                echo '<table cellpadding="4" cellspacing="2" border="0" width="640">';

                echo "<tr><th align=\"left\">$strname</th><th align=\"right\">$strsize</th><th align=\"right\">$strmodified</th></tr>";

                foreach ($list as $item) {
                    echo '<tr>';

                    print_cell('left', $item['filename']);

                    if (!$item['folder']) {
                        print_cell('right', display_size($item['size']));
                    } else {
                        echo '<td>&nbsp;</td>';
                    }

                    $filedate = userdate($item['mtime'], get_string('strftimedatetime'));

                    print_cell('right', $filedate);

                    echo '</tr>';
                }

                echo '</table>';
            }

            echo '<br><center><form action="index.php" method="get">';

            echo ' <input type="hidden" name="choose" value="' . $choose . '">';

            echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

            echo ' <input type="hidden" name="action" value="cancel">';

            echo " <input type=\"submit\" value=\"$strok\">";

            echo '</form>';

            echo '</center>';
        } else {
            displaydir($wdir);
        }
        html_footer();
        break;
    case 'restore':
        html_header($course, $wdir);
        if (!empty($file) and confirm_sesskey()) {
            echo '<p align="center">' . get_string('youaregoingtorestorefrom') . ':</p>';

            print_simple_box_start('center');

            echo $file;

            print_simple_box_end();

            echo '<br>';

            echo '<p align=center>' . get_string('areyousuretorestorethisinfo') . '</p>';

            $restore_path = "$CFG->wwwroot/backup/restore.php";

            notice_yesno(
                get_string('areyousuretorestorethis'),
                $restore_path . '?id=' . $id . '&file=' . cleardoubleslashes($id . $wdir . '/' . $file),
                "index.php?id=$id&wdir=$wdir&action=cancel"
            );
        } else {
            displaydir($wdir);
        }
        html_footer();
        break;
    case 'cancel':
        clearfilelist();

        // no break
    default:
        html_header($course, $wdir);
        displaydir($wdir);
        html_footer();
        break;
}

//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------

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

            $val = rawurldecode($val);

            if (!detect_munged_arguments($val, 0)) {
                $USER->filelist[] = $val;
            }
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
    global $CFG, $basedir;

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
    echo "<td align=\"$alignment\" nowrap=\"nowrap\">";

    echo '<font size="-1" face="Arial, Helvetica">';

    echo (string)$text;

    echo '</font>';

    echo "</td>\n";
}

function displaydir($wdir)
{
    //  $wdir == / or /a or /a/b/c/d  etc

    global $basedir;

    global $id;

    global $USER, $CFG;

    global $choose;

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

    $strrestore = get_string('restore');

    $strchoose = get_string('choose');

    echo '<form action="index.php" method="post" name="dirform">';

    echo '<input type="hidden" name="choose" value="' . $choose . '">';

    echo '<hr width="640" align="center" noshade="noshade" size="1">';

    echo '<table border="0" cellspacing="2" cellpadding="2" width="640">';

    echo '<tr>';

    echo '<th width="5"></th>';

    echo "<th align=\"left\">$strname</th>";

    echo "<th align=\"right\">$strsize</th>";

    echo "<th align=\"right\">$strmodified</th>";

    echo "<th align=\"right\">$straction</th>";

    echo "</tr>\n";

    if ('/' == $wdir) {
        $wdir = '';
    }

    $count = 0;

    if (!empty($dirlist)) {
        asort($dirlist);

        foreach ($dirlist as $dir) {
            $count++;

            $filename = $fullpath . '/' . $dir;

            $fileurl = rawurlencode($wdir . '/' . $dir);

            $filesafe = rawurlencode($dir);

            $filesize = display_size(get_directory_size("$fullpath/$dir"));

            $filedate = userdate(filemtime($filename), '%d %b %Y, %I:%M %p');

            echo '<tr>';

            print_cell('center', "<input type=\"checkbox\" name=\"file$count\" value=\"$fileurl\">");

            print_cell(
                'left',
                "<a href=\"index.php?id=$id&amp;wdir=$fileurl&amp;choose=$choose\"><img src=\"$CFG->pixpath/f/folder.gif\" height=\"16\" width=\"16\" border=\"0\" alt=\"Folder\"></a> <a href=\"index.php?id=$id&amp;wdir=$fileurl&amp;choose=$choose\">" . htmlspecialchars($dir, ENT_QUOTES | ENT_HTML5)
                . '</a>'
            );

            print_cell('right', "<b>$filesize</b>");

            print_cell('right', $filedate);

            print_cell('right', "<a href=\"index.php?id=$id&amp;wdir=$wdir&amp;file=$filesafe&amp;action=rename&amp;choose=$choose\">$strrename</a>");

            echo '</tr>';
        }
    }

    if (!empty($filelist)) {
        asort($filelist);

        foreach ($filelist as $file) {
            $icon = mimeinfo('icon', $file);

            $count++;

            $filename = $fullpath . '/' . $file;

            $fileurl = "$wdir/$file";

            $filesafe = rawurlencode($file);

            $fileurlsafe = rawurlencode($fileurl);

            $filedate = userdate(filemtime($filename), '%d %b %Y, %I:%M %p');

            if ('/' == mb_substr($fileurl, 0, 1)) {
                $selectfile = mb_substr($fileurl, 1);
            } else {
                $selectfile = $fileurl;
            }

            echo '<tr>';

            print_cell('center', "<input type=\"checkbox\" name=\"file$count\" value=\"$fileurl\">");

            echo '<td align="left" nowrap="nowrap">';

            if ($CFG->slasharguments) {
                $ffurl = "/file.php/$id$fileurl";
            } else {
                $ffurl = "/file.php?file=/$id$fileurl";
            }

            link_to_popup_window(
                $ffurl,
                'display',
                "<img src=\"$CFG->pixpath/f/$icon\" height=\"16\" width=\"16\" border=\"0\" alt=\"File\">",
                480,
                640
            );

            echo '<font size="-1" face="Arial, Helvetica">';

            link_to_popup_window(
                $ffurl,
                'display',
                htmlspecialchars($file, ENT_QUOTES | ENT_HTML5),
                480,
                640
            );

            echo '</font></td>';

            $file_size = filesize($filename);

            print_cell('right', display_size($file_size));

            print_cell('right', $filedate);

            if ($choose) {
                $edittext = "<b><a onMouseDown=\"return set_value('$selectfile')\" href=\"\">$strchoose</a></b>&nbsp;";
            } else {
                $edittext = '';
            }

            if ('text.gif' == $icon || 'html.gif' == $icon) {
                $edittext .= "<a href=\"index.php?id=$id&amp;wdir=$wdir&amp;file=$fileurl&amp;action=edit&amp;choose=$choose\">$stredit</a>";
            } elseif ('zip.gif' == $icon) {
                $edittext .= "<a href=\"index.php?id=$id&amp;wdir=$wdir&amp;file=$fileurl&amp;action=unzip&amp;sesskey=$USER->sesskey&amp;choose=$choose\">$strunzip</a>&nbsp;";

                $edittext .= "<a href=\"index.php?id=$id&amp;wdir=$wdir&amp;file=$fileurl&amp;action=listzip&amp;sesskey=$USER->sesskey&amp;choose=$choose\">$strlist</a> ";

                if (!empty($CFG->backup_version) and isteacheredit($id)) {
                    $edittext .= "<a href=\"index.php?id=$id&amp;wdir=$wdir&amp;file=$filesafe&amp;action=restore&amp;sesskey=$USER->sesskey&amp;choose=$choose\">$strrestore</a> ";
                }
            }

            print_cell('right', "$edittext <a href=\"index.php?id=$id&amp;wdir=$wdir&amp;file=$filesafe&amp;action=rename&amp;choose=$choose\">$strrename</a>");

            echo '</tr>';
        }
    }

    echo '</table>';

    echo '<hr width="640" align="center" noshade="noshade" size="1">';

    if (empty($wdir)) {
        $wdir = '/';
    }

    echo '<table border="0" cellspacing="2" cellpadding="2" width="640">';

    echo '<tr><td>';

    echo "<input type=\"hidden\" name=\"id\" value=\"$id\">";

    echo '<input type="hidden" name="choose" value="' . $choose . '">';

    echo "<input type=\"hidden\" name=\"wdir\" value=\"$wdir\"> ";

    echo "<input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">";

    $options = [
        'move' => (string)$strmovetoanotherfolder,
        'delete' => (string)$strdeletecompletely,
        'zip' => (string)$strcreateziparchive,
    ];

    if (!empty($count)) {
        choose_from_menu($options, 'action', '', "$strwithchosenfiles...", 'javascript:document.dirform.submit()');
    }

    echo '</form>';

    echo '<td align="center">';

    if (!empty($USER->fileop) and ('move' == $USER->fileop) and ($USER->filesource != $wdir)) {
        echo '<form action="index.php" method="get">';

        echo ' <input type="hidden" name="choose" value="' . $choose . '">';

        echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

        echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

        echo ' <input type="hidden" name="action" value="paste">';

        echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\">";

        echo " <input type=\"submit\" value=\"$strmovefilestohere\">";

        echo '</form>';
    }

    echo '<td align="right">';

    echo '<form action="index.php" method="get">';

    echo ' <input type="hidden" name="choose" value="' . $choose . '">';

    echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

    echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

    echo ' <input type="hidden" name="action" value="mkdir">';

    echo " <input type=\"submit\" value=\"$strmakeafolder\">";

    echo '</form>';

    echo '</td>';

    echo '<td align="right">';

    echo '<form action="index.php" method="get">';

    echo ' <input type="hidden" name="choose" value="' . $choose . '">';

    echo " <input type=\"hidden\" name=\"id\" value=\"$id\">";

    echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\">";

    echo ' <input type="hidden" name="action" value="upload">';

    echo " <input type=\"submit\" value=\"$struploadafile\">";

    echo '</form>';

    echo '</td></tr>';

    echo '</table>';

    echo '<hr width="640" align="center" noshade="noshade" size="1">';
}

?>
