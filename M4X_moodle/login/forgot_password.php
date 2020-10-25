<?php

declare(strict_types=1);

// $Id: forgot_password.php,v 1.14.2.1 2004/10/04 15:47:28 moodler Exp $

require_once '../config.php';

optional_variable($p, '');
optional_variable($s, '');

if (!empty($p) and !empty($s)) {  // User trying to authenticate change password routine
    update_login_count();

    $user = get_user_info_from_db('username', (string)$s);

    if (!empty($user)) {
        if ($user->secret == $p) {   // They have provided the secret key to get in
            if (isguest($user->id)) {
                error("Can't change guest password!");
            }

            $user->emailstop = 0;    // Send mail even if sending mail was forbidden

            if (!reset_password_and_mail($user)) {
                error('Could not reset password and mail the new one to you');
            }

            reset_login_count();

            print_header(get_string('passwordsent'), get_string('passwordsent'), get_string('passwordsent'));

            $a->email = $user->email;

            $a->link = "$CFG->wwwroot/login/change_password.php";

            notice(get_string('emailpasswordsent', '', $a), $a->link);
        }
    }

    error(get_string('error'));
}

if ($frm = data_submitted()) {    // Initial request for new password
    validate_form($frm, $err);

    if (0 == count((array)$err)) {
        if (!$user = get_user_info_from_db('email', $frm->email)) {
            error("No such user with this address:  $frm->email");
        }

        if (empty($user->confirmed)) {
            error(get_string('confirmednot'));
        }

        $user->secret = random_string(15);

        if (!set_field('user', 'secret', $user->secret, 'id', $user->id)) {
            error('Could not set user secret string!');
        }

        $user->emailstop = 0;    // Send mail even if sending mail was forbidden

        if (!send_password_change_confirmation_email($user)) {
            error('Could not send you an email to confirm the password change');
        }

        print_header(get_string('passwordconfirmchange'), get_string('passwordconfirmchange'));

        notice(get_string('emailpasswordconfirmsent', '', $user->email), "$CFG->wwwroot/");
    }
}

if (empty($frm->email)) {
    if ($username = get_moodle_cookie()) {
        $frm->email = get_field('user', 'email', 'username', (string)$username);
    }
}

print_header(
    get_string('senddetails'),
    get_string('senddetails'),
    "<A HREF=\"$CFG->wwwroot/login/index.php\">" . get_string('login') . '</A> -> ' . get_string('senddetails'),
    'form.email'
);
include 'forgot_password_form.html';
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
    if (empty($frm->email)) {
        $err->email = get_string('missingemail');
    } elseif (!validate_email($frm->email)) {
        $err->email = get_string('invalidemail');
    } elseif (!record_exists('user', 'email', $frm->email)) {
        $err->email = get_string('nosuchemail');
    }
}
