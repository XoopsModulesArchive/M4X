<?php

declare(strict_types=1);

// $Id: change_password.php,v 1.17 2004/02/21 01:24:07 stronk7 Exp $

require_once '../config.php';

optional_variable($id);

if ($id) {
    if (!$course = get_record('course', 'id', $id)) {
        error('No such course!');
    }
}

if ($frm = data_submitted()) {
    validate_form($frm, $err);

    check_for_restricted_user($frm->username);

    update_login_count();

    if (!count((array)$err)) {
        $username = $frm->username;

        $password = md5($frm->newpassword1);

        $user = get_user_info_from_db('username', $username);

        if (isguest($user->id)) {
            error("Can't change guest password!");
        }

        if (set_field('user', 'password', $password, 'username', $username)) {
            $user->password = $password;
        } else {
            error('Could not set the new password');
        }

        $USER = $user;

        $USER->loggedin = true;

        $USER->site = $CFG->wwwroot;   // for added security

        set_moodle_cookie($USER->username);

        reset_login_count();

        $strpasswordchanged = get_string('passwordchanged');

        if ($course->id) {
            add_to_log($course->id, 'user', 'change password', "view.php?id=$user->id&course=$course->id", (string)$user->id);

            $fullname = fullname($USER, true);

            print_header(
                $strpasswordchanged,
                $strpasswordchanged,
                "<A HREF=\"$CFG->wwwroot/course/view.php?id=$course->id\">$course->shortname</A> ->
                              <A HREF=\"$CFG->wwwroot/user/index.php?id=$course->id\">" . get_string('participants') . "</A> ->
                              <A HREF=\"$CFG->wwwroot/user/view.php?id=$USER->id&course=$course->id\">$fullname</A> -> $strpasswordchanged",
                $focus
            );

            notice($strpasswordchanged, "$CFG->wwwroot/user/view.php?id=$USER->id&course=$id");
        } else {
            $site = get_site();

            add_to_log($site->id, 'user', 'change password', "view.php?id=$user->id&course=$site->id", (string)$course->id);

            print_header($strpasswordchanged, $strpasswordchanged, $strpasswordchanged, '');

            notice($strpasswordchanged, "$CFG->wwwroot/");
        }

        print_footer();

        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------

        exit;
    }
}

if ($course->id) {
    $frm->id = $id;
}

if (empty($frm->username)) {
    $frm->username = get_moodle_cookie();
}

if (!empty($frm->username)) {
    $focus = 'form.password';
} else {
    $focus = 'form.username';
}

$strchangepassword = get_string('changepassword');
if (!empty($course->id)) {
    $fullname = fullname($USER, true);

    print_header(
        $strchangepassword,
        $strchangepassword,
        "<A HREF=\"$CFG->wwwroot/course/view.php?id=$course->id\">$course->shortname</A> ->
                      <A HREF=\"$CFG->wwwroot/user/index.php?id=$course->id\">" . get_string('participants') . "</A> ->
                      <A HREF=\"$CFG->wwwroot/user/view.php?id=$USER->id&course=$course->id\">$fullname</A> -> $strchangepassword",
        $focus
    );
} else {
    print_header($strchangepassword, $strchangepassword, $strchangepassword, $focus);
}

print_simple_box_start('center', '', $THEME->cellheading);
include 'change_password_form.html';
print_simple_box_end();
print_footer();
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------

/******************************************************************************
 * FUNCTIONS
 ****************************************************************************
 * @param $frm
 * @param $err
 */
function validate_form($frm, $err)
{
    if (empty($frm->username)) {
        $err->username = get_string('missingusername');
    } elseif (empty($frm->password)) {
        $err->password = get_string('missingpassword');
    } elseif (!authenticate_user_login($frm->username, $frm->password)) {
        $err->password = get_string('wrongpassword');
    }

    if (empty($frm->newpassword1)) {
        $err->newpassword1 = get_string('missingnewpassword');
    }

    if (empty($frm->newpassword2)) {
        $err->newpassword2 = get_string('missingnewpassword');
    } elseif ($frm->newpassword1 != $frm->newpassword2) {
        $err->newpassword2 = get_string('passwordsdiffer');
    }
}
