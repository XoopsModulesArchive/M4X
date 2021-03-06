<?php

declare(strict_types=1);

// $Id: view.php,v 1.66.2.4 2004/10/27 05:57:34 moodler Exp $

//  Display profile for a particular user

require_once '../config.php';
require_once '../mod/forum/lib.php';

optional_variable($id);
optional_variable($course);
optional_variable($enable, '');
optional_variable($disable, '');

if (empty($id)) {         // See your own profile by default
    require_login();

    $id = $USER->id;
}

if (empty($course)) {     // See it at site level by default
    $course = SITEID;
}

if (!$user = get_record('user', 'id', $id)) {
    error('No such user in this course');
}

if (!$course = get_record('course', 'id', $course)) {
    error('No such course id');
}

if ($course->category) {
    require_login($course->id);
} elseif ($CFG->forcelogin or !empty($CFG->forceloginforprofiles)) {
    if (isguest()) {
        redirect("$CFG->wwwroot/login/index.php");
    }

    require_login();
}

add_to_log($course->id, 'user', 'view', "view.php?id=$user->id&course=$course->id", (string)$user->id);

if (SITEID != $course->id) {
    if ($student = get_record('user_students', 'userid', $user->id, 'course', $course->id)) {
        $user->lastaccess = $student->timeaccess;
    } elseif ($teacher = get_record('user_teachers', 'userid', $user->id, 'course', $course->id)) {
        $user->lastaccess = $teacher->timeaccess;
    }
}

$fullname = fullname($user, isteacher($course->id));
$personalprofile = get_string('personalprofile');
$participants = get_string('participants');

if (empty($USER->id)) {
    $currentuser = false;
} else {
    $currentuser = ($user->id == $USER->id);
}

if (SEPARATEGROUPS == groupmode($course) and !isteacheredit($course->id)) {   // Groups must be kept separate
    require_login();

    if (!isteacheredit($course->id, $user->id) and !ismember(mygroupid($course->id), $user->id)) {
        print_header(
            "$personalprofile: ",
            "$personalprofile: ",
            "<a href=\"../course/view.php?id=$course->id\">$course->shortname</a> ->
                          <a href=\"index.php?id=$course->id\">$participants</a>",
            '',
            '',
            true,
            '&nbsp;',
            navmenu($course)
        );

        error(get_string('groupnotamember'), "../course/view.php?id=$course->id");
    }
}

if (!$course->category and !$currentuser) {  // To reduce possibility of "browsing" userbase at site level
    if (!isteacher() and !isteacher(0, $user->id)) {  // Teachers can browse and be browsed at site level
        print_header(
            "$personalprofile: ",
            "$personalprofile: ",
            "<a href=\"index.php?id=$course->id\">$participants</a>",
            '',
            '',
            true,
            '&nbsp;',
            navmenu($course)
        );

        print_heading(get_string('usernotavailable', 'error'));

        print_footer($course);

        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------

        die;
    }
}

if ($course->category) {
    print_header(
        "$personalprofile: $fullname",
        "$personalprofile: $fullname",
        "<a href=\"../course/view.php?id=$course->id\">$course->shortname</a> ->
                      <a href=\"index.php?id=$course->id\">$participants</a> -> $fullname",
        '',
        '',
        true,
        '&nbsp;',
        navmenu($course)
    );
} else {
    print_header(
        "$course->fullname: $personalprofile: $fullname",
        (string)$course->fullname,
        (string)$fullname,
        '',
        '',
        true,
        '&nbsp;',
        navmenu($course)
    );
}

if ($course->category and !isguest()) {   // Need to have access to a course to see that info
    if (!isstudent($course->id, $user->id) && !isteacher($course->id, $user->id)) {
        print_heading(get_string('notenrolled', '', $fullname));

        print_footer($course);

        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------

        die;
    }
}

if ($user->deleted) {
    print_heading(get_string('userdeleted'));
}

echo '<table width="80%" align="center" border="0" cellpadding="1" cellspacing="1" class="userinfobox">';
echo '<tr>';
echo '<td width="100" valign="top" class="userinfoboxside">';
print_user_picture($user->id, $course->id, $user->picture, true, false, false);
echo "</td><td width=\"100%\" bgcolor=\"$THEME->cellcontent\" class=\"userinfoboxcontent\">";

// Print name and edit button across top

echo '<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td nowrap>';
echo "<h3>$fullname</h3>";
echo '</td><td align="right">';
if (($currentuser and !isguest()) or isadmin()) {
    if (empty($CFG->loginhttps)) {
        $wwwroot = $CFG->wwwroot;
    } else {
        $wwwroot = str_replace('http', 'https', $CFG->wwwroot);
    }

    echo "<p><form action=\"$wwwroot/user/edit.php\" method=\"get\">";

    echo "<input type=\"hidden\" name=\"id\" value=\"$id\">";

    echo "<input type=\"hidden\" name=\"course\" value=\"$course->id\">";

    echo '<input type="submit" value="' . get_string('editmyprofile') . '">';

    echo '</form></p>';
}
echo '</td></tr></table>';

// Print the description

if ($user->description) {
    echo '<p>' . format_text($user->description, FORMAT_MOODLE) . '</p><hr>';
}

// Print all the little details in a list

echo '<table border="0" cellpadding="5" cellspacing="2">';

if ($user->city or $user->country) {
    $countries = get_list_of_countries();

    print_row(get_string('location') . ':', "$user->city, " . $countries[(string)$user->country]);
}

if (isteacher($course->id)) {
    if ($user->address) {
        print_row(get_string('address') . ':', (string)$user->address);
    }

    if ($user->phone1) {
        print_row(get_string('phone') . ':', (string)$user->phone1);
    }

    if ($user->phone2) {
        print_row(get_string('phone') . ':', (string)$user->phone2);
    }
}

if (1 == $user->maildisplay or (2 == $user->maildisplay and $course->category and !isguest()) or isteacher($course->id)) {
    $emailswitch = '';

    if (isteacheredit($course->id) or $currentuser) {   /// Can use the enable/disable email stuff
        if (!empty($_GET['enable'])) {     /// Recieved a parameter to enable the email address
            set_field('user', 'emailstop', 0, 'id', $user->id);

            $user->emailstop = 0;
        }

        if (!empty($_GET['disable'])) {     /// Recieved a parameter to disable the email address
            set_field('user', 'emailstop', 1, 'id', $user->id);

            $user->emailstop = 1;
        }
    }

    if (isteacheredit($course->id)) {   /// Can use the enable/disable email stuff
        if ($user->emailstop) {
            $switchparam = 'enable';

            $switchtitle = get_string('emaildisable');

            $switchclick = get_string('emailenableclick');

            $switchpix = 'emailno.gif';
        } else {
            $switchparam = 'disable';

            $switchtitle = get_string('emailenable');

            $switchclick = get_string('emaildisableclick');

            $switchpix = 'email.gif';
        }

        $emailswitch = "&nbsp<a title=\"$switchclick\" " . "href=\"view.php?id=$user->id&course=$course->id&$switchparam=1\">" . "<img border=\"0\" width=11 height=11 src=\"$CFG->pixpath/t/$switchpix\"></a>";
    } elseif ($currentuser) {         /// Can only re-enable an email this way
        if ($user->emailstop) {   // Include link that tells how to re-enable their email
            $switchparam = 'enable';

            $switchtitle = get_string('emaildisable');

            $switchclick = get_string('emailenableclick');

            $emailswitch = "&nbsp(<a title=\"$switchclick\" " . "href=\"view.php?id=$user->id&course=$course->id&enable=1\">$switchtitle</a>)";
        }
    }

    print_row(get_string('email') . ':', obfuscate_mailto($user->email, '', $user->emailstop) . (string)$emailswitch);
}

if ($user->url) {
    print_row(get_string('webpage') . ':', "<a href=\"$user->url\">$user->url</a>");
}

if ($user->icq) {
    print_row('ICQ:', "<a href=\"http://web.icq.com/wwp?uin=$user->icq\">$user->icq <img src=\"http://web.icq.com/whitepages/online?icq=$user->icq&img=5\" width=18 height=18 border=0></a>");
}

if (isteacher($course->id)) {
    if ($mycourses = get_my_courses($user->id)) {
        $courselisting = '';

        foreach ($mycourses as $mycourse) {
            if ($mycourse->visible and $mycourse->category) {
                $courselisting .= "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&course=$mycourse->id\">$mycourse->fullname</a>, ";
            }
        }

        print_row(get_string('courses') . ':', rtrim($courselisting, ', '));
    }
}

if ($user->lastaccess) {
    $datestring = userdate($user->lastaccess) . '&nbsp (' . format_time(time() - $user->lastaccess) . ')';
} else {
    $datestring = get_string('never');
}
print_row(get_string('lastaccess') . ':', $datestring);

echo '</table>';

echo '</td></tr></table>';

$internalpassword = false;
if (is_internal_auth()) {
    if (empty($CFG->loginhttps)) {
        $internalpassword = "$CFG->wwwroot/login/change_password.php";
    } else {
        $internalpassword = str_replace('http', 'https', $CFG->wwwroot . '/login/change_password.php');
    }
}

//  Print other functions
echo '<center><table align=center><tr>';
if ($currentuser and !isguest()) {
    if ($internalpassword) {
        echo "<td nowrap><p><form action=\"$internalpassword\" method=get>";

        echo "<input type=hidden name=id value=\"$course->id\">";

        echo '<input type=submit value="' . get_string('changepassword') . '">';

        echo '</form></p></td>';
    } elseif (mb_strlen($CFG->changepassword) > 1) {
        echo "<td nowrap><p><form action=\"$CFG->changepassword\" method=get>";

        echo '<input type=submit value="' . get_string('changepassword') . '">';

        echo '</form></p></td>';
    }
}
if ($course->category and ((isstudent($course->id) and ($user->id == $USER->id) and !isguest() and $CFG->allowunenroll) or (isteacheredit($course->id) and isstudent($course->id, $user->id)))) {
    echo '<td nowrap><p><form action="../course/unenrol.php" method=get>';

    echo "<input type=hidden name=id value=\"$course->id\">";

    echo "<input type=hidden name=user value=\"$user->id\">";

    echo '<input type=submit value="' . get_string('unenrolme', '', $course->shortname) . '">';

    echo '</form></p></td>';
}
if (isteacher($course->id) or ($course->showreports and $USER->id == $user->id)) {
    echo '<td nowrap><p><form action="../course/user.php" method=get>';

    echo "<input type=hidden name=id value=\"$course->id\">";

    echo "<input type=hidden name=user value=\"$user->id\">";

    echo '<input type=submit value="' . get_string('activityreport') . '">';

    echo '</form></p></td>';
}
if (isteacher($course->id) and ($USER->id != $user->id) and !iscreator($user->id)) {
    echo '<td nowrap><p><form action="../course/loginas.php" method=get>';

    echo "<input type=hidden name=id value=\"$course->id\">";

    echo "<input type=hidden name=user value=\"$user->id\">";

    echo '<input type=submit value="' . get_string('loginas') . '">';

    echo '</form></p></td>';
}
echo "</tr></table></center>\n";

$isseparategroups = (SEPARATEGROUPS == $course->groupmode and $course->groupmodeforce and !isteacheredit($course->id));

$groupid = $isseparategroups ? get_current_group($course->id) : null;

forum_print_user_discussions($course->id, $user->id, $groupid);

print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------

/// Functions ///////

function print_row($left, $right)
{
    echo "<tr><td nowrap align=right valign=top><p>$left</td><td align=left valign=top><p>$right</p></td></tr>";
}
