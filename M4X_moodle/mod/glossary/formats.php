<?php

// $Id: formats.php,v 1.5.2.2 2004/10/03 09:44:58 stronk7 Exp $
/// This file allows to manage the default behave of the display formats

require_once '../../config.php';
require_once 'lib.php';
global $CFG, $THEME;

require_variable($id);
optional_variable($mode);

$mode = strip_tags(urldecode($mode));  //XSS

require_login();
if (!isadmin()) {
    error('You must be an admin to use this page.');
}
if (!$site = get_site()) {
    error("Site isn't defined!");
}

if (!$displayformat = get_record('glossary_formats', 'id', $id)) {
    error('Invalid Glossary Format');
}

$form = data_submitted();
if ('visible' == $mode) {
    if ($displayformat) {
        if ($displayformat->visible) {
            $displayformat->visible = 0;
        } else {
            $displayformat->visible = 1;
        }

        update_record('glossary_formats', $displayformat);
    }

    redirect("../../admin/module.php?sesskey=$USER->sesskey&module=glossary#formats");

    die;
} elseif ('edit' == $mode and $form) {
    $displayformat->popupformatname = $form->popupformatname;

    $displayformat->showgroup = $form->showgroup;

    $displayformat->defaultmode = $form->defaultmode;

    $displayformat->defaulthook = $form->defaulthook;

    $displayformat->sortkey = $form->sortkey;

    $displayformat->sortorder = $form->sortorder;

    update_record('glossary_formats', $displayformat);

    redirect("../../admin/module.php?sesskey=$USER->sesskey&module=glossary#formats");

    die;
}

$stradmin = get_string('administration');
$strconfiguration = get_string('configuration');
$strmanagemodules = get_string('managemodules');
$strmodulename = get_string('modulename', 'glossary');
$strdisplayformats = get_string('displayformats', 'glossary');

print_header(
    "$strmodulename: $strconfiguration",
    $site->fullname,
    "<a href=\"../../admin/index.php\">$stradmin</a> -> "
    . "<a href=\"../../admin/configure.php\">$strconfiguration</a> -> "
    . "<a href=\"../../admin/modules.php\">$strmanagemodules</a> -> <a href=\"../../admin/module.php?module=glossary&sesskey=$USER->sesskey\">$strmodulename</a> -> $strdisplayformats"
);

print_heading($strmodulename . ': ' . get_string('displayformats', 'glossary'));

echo '<table width="90%" align="center" bgcolor="#FFFFFF" class="generaltab" style="border-color: #000000; border-style: solid; border-width: 1px;">';
echo '<tr><td align=center>';
echo get_string('configwarning');
echo '</td></tr></table>';

$yes = get_string('yes');
$no = get_string('no');

echo '<form method="post" action="formats.php" name="form">';
echo '<table width="90%" align="center" bgcolor="' . $THEME->cellheading . '" class="generalbox">';
?>
<tr>
    <td colspan=3 align=center><strong>
            <?php echo get_string('displayformat' . $displayformat->name, 'glossary'); ?>
        </strong></td>
</tr>
<tr valign=top>
    <td align="right" width="20%"><?php print_string('popupformat', 'glossary'); ?></td>
    <td>
        <?php
        //get and update available formats
        $recformats = glossary_get_available_formats();

        $formats = [];

        //Take names
        foreach ($recformats as $format) {
            $formats[$format->name] = get_string("displayformat$format->name", 'glossary');
        }
        //Sort it
        asort($formats);

        choose_from_menu($formats, 'popupformatname', $displayformat->popupformatname);
        ?>
    </td>
    <td width="60%">
        <?php print_string('cnfrelatedview', 'glossary') ?><br><br>
    </td>
</tr>
<tr valign=top>
    <td align="right" width="20%"><?php print_string('defaultmode', 'glossary'); ?></td>
    <td>
        <SELECT size=1 name=defaultmode>
            <?php
            $sletter = '';
            $scat = '';
            $sauthor = '';
            $sdate = '';
            switch (mb_strtolower($displayformat->defaultmode)) {
                case 'letter':
                    $sletter = ' SELECTED ';
                    break;
                case 'cat':
                    $scat = ' SELECTED ';
                    break;
                case 'date':
                    $sdate = ' SELECTED ';
                    break;
                case 'author':
                    $sauthor = ' SELECTED ';
                    break;
            }
            ?>
            <OPTION value="letter" <?php p($sletter) ?>>letter</OPTION>
            <OPTION value="cat" <?php p($scat) ?>>cat</OPTION>
            <OPTION value="date" <?php p($sdate) ?>>date</OPTION>
            <OPTION value="author" <?php p($sauthor) ?>>author</OPTION>
        </SELECT>
    </td>
    <td width="60%">
        <?php print_string('cnfdefaultmode', 'glossary') ?><br><br>
    </td>
</tr>
<tr valign=top>
    <td align="right" width="20%"><?php print_string('defaulthook', 'glossary'); ?></td>
    <td>
        <SELECT size=1 name=defaulthook>
            <?php
            $sall = '';
            $sspecial = '';
            $sallcategories = '';
            $snocategorised = '';
            switch (mb_strtolower($displayformat->defaulthook)) {
                case 'all':
                    $sall = ' SELECTED ';
                    break;
                case 'special':
                    $sspecial = ' SELECTED ';
                    break;
                case '0':
                    $sallcategories = ' SELECTED ';
                    break;
                case '-1':
                    $snocategorised = ' SELECTED ';
                    break;
            }
            ?>
            <OPTION value="ALL" <?php p($sall) ?>><?php p(get_string('allentries', 'glossary')) ?></OPTION>
            <OPTION value="SPECIAL" <?php p($sspecial) ?>><?php p(get_string('special', 'glossary')) ?></OPTION>
            <OPTION value="0" <?php p($sallcategories) ?>><?php p(get_string('allcategories', 'glossary')) ?></OPTION>
            <OPTION value="-1" <?php p($snocategorised) ?>><?php p(get_string('notcategorised', 'glossary')) ?></OPTION>
        </SELECT>
    </td>
    <td width="60%">
        <?php print_string('cnfdefaulthook', 'glossary') ?><br><br>
    </td>
</tr>
<tr valign=top>
    <td align="right" width="20%"><?php print_string('defaultsortkey', 'glossary'); ?></td>
    <td>
        <SELECT size=1 name=sortkey>
            <?php
            $sfname = '';
            $slname = '';
            $supdate = '';
            $screation = '';
            switch (mb_strtolower($displayformat->sortkey)) {
                case 'firstname':
                    $sfname = ' SELECTED ';
                    break;
                case 'lastname':
                    $slname = ' SELECTED ';
                    break;
                case 'creation':
                    $screation = ' SELECTED ';
                    break;
                case 'update':
                    $supdate = ' SELECTED ';
                    break;
            }
            ?>
            <OPTION value="CREATION" <?php p($screation) ?>><?php p(get_string('sortbycreation', 'glossary')) ?></OPTION>
            <OPTION value="UPDATE" <?php p($supdate) ?>><?php p(get_string('sortbylastupdate', 'glossary')) ?></OPTION>
            <OPTION value="FIRSTNAME" <?php p($sfname) ?>><?php p(get_string('firstname')) ?></OPTION>
            <OPTION value="LASTNAME" <?php p($slname) ?>><?php p(get_string('lastname')) ?></OPTION>
        </SELECT>
    </td>
    <td width="60%">
        <?php print_string('cnfsortkey', 'glossary') ?><br><br>
    </td>
</tr>
<tr valign=top>
    <td align="right" width="20%"><?php print_string('defaultsortorder', 'glossary'); ?></td>
    <td>
        <SELECT size=1 name=sortorder>
            <?php
            $sasc = '';
            $sdesc = '';
            switch (mb_strtolower($displayformat->sortorder)) {
                case 'asc':
                    $sasc = ' SELECTED ';
                    break;
                case 'desc':
                    $sdesc = ' SELECTED ';
                    break;
            }
            ?>
            <OPTION value="asc" <?php p($sasc) ?>><?php p(get_string('ascending', 'glossary')) ?></OPTION>
            <OPTION value="desc" <?php p($sdesc) ?>><?php p(get_string('descending', 'glossary')) ?></OPTION>
        </SELECT>
    </td>
    <td width="60%">
        <?php print_string('cnfsortorder', 'glossary') ?><br><br>
    </td>
</tr>
<tr valign=top>
    <td align="right" width="20%"><p>Include Group Breaks:</td>
    <td>
        <SELECT size=1 name=showgroup>
            <?php
            $yselected = '';
            $nselected = '';
            if ($displayformat->showgroup) {
                $yselected = ' SELECTED ';
            } else {
                $nselected = ' SELECTED ';
            }
            ?>
            <OPTION value=1 <?php p($yselected) ?>><?php p($yes) ?></OPTION>
            <OPTION value=0 <?php p($nselected) ?>><?php p($no) ?></OPTION>
        </SELECT>
    </td>
    <td width="60%">
        <?php print_string('cnfshowgroup', 'glossary') ?><br><br>
    </td>
</tr>
<tr>
    <td colspan=3 align=center>
        <input type="submit" value="<?php print_string('savechanges') ?>"></td>
</tr>
<input type="hidden" name=id value="<?php p($id) ?>">
<input type="hidden" name=mode value="edit">
<?php

print_simple_box_end();
echo '</form>';

print_footer();
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
?>
