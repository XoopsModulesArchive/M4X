<?php

declare(strict_types=1);

// $Id: user.php,v 1.60.2.4 2004/11/04 07:14:16 moodler Exp $

require_once '../config.php';

optional_variable($newuser, '');
optional_variable($delete, '');
optional_variable($confirm, '');
optional_variable($confirmuser, '');
optional_variable($sort, 'name');
optional_variable($dir, 'ASC');
optional_variable($page, 0);
optional_variable($search, '');
optional_variable($lastinitial, '');     // only show students with this last initial
optional_variable($firstinitial, '');    // only show students with this first initial
optional_variable($perpage, '30');       // how many per page

unset($user);
unset($admin);
unset($teacher);

$search = trim($search);

if (!record_exists('user_admins')) {   // No admin user yet
    $user->firstname = get_string('admin');

    $user->lastname = get_string('user');

    $user->username = 'admin';

    $user->password = md5('admin');

    $user->email = 'root@localhost';

    $user->confirmed = 1;

    $user->lang = $CFG->lang;

    $user->maildisplay = 1;

    $user->timemodified = time();

    if (!$user->id = insert_record('user', $user)) {
        error('SERIOUS ERROR: Could not create admin user record !!!');
    }

    $admin->userid = $user->id;

    if (!insert_record('user_admins', $admin)) {
        error("Could not make user $user->id an admin !!!");
    }

    if (!$user = get_record('user', 'id', $user->id)) {     // Double check
        error("User ID was incorrect (can't find it)");
    }

    if (!$site = get_site()) {
        error('Could not find site-level course');
    }

    $teacher->userid = $user->id;

    $teacher->course = $site->id;

    $teacher->authority = 1;

    if (!insert_record('user_teachers', $teacher)) {
        error("Could not make user $id a teacher of site-level course !!!");
    }

    $USER = $user;

    $USER->loggedin = true;

    $USER->sesskey = random_string(10); // for added security, used to check script parameters

    $USER->site = $CFG->wwwroot;

    $USER->admin = true;

    $USER->teacher[(string)$site->id] = true;

    $USER->newadminuser = true;

    redirect("$CFG->wwwroot/user/edit.php?id=$user->id&course=$site->id");

    exit;
}
    if (!$site = get_site()) {
        error('Could not find site-level course');
    }

require_login();

if (!isadmin()) {
    error('You must be an administrator to edit users this way.');
}

if ($newuser and confirm_sesskey()) {                 // Create a new user
    $user->auth = 'manual';

    $user->firstname = '';

    $user->lastname = '';

    $user->username = 'changeme';

    $user->password = '';

    $user->email = '';

    $user->lang = $CFG->lang;

    $user->confirmed = 1;

    $user->timemodified = time();

    if (!$user->id = insert_record('user', $user)) {
        if (!$user = get_record('user', 'username', 'changeme')) {   // half finished user from another time
            error('Could not start a new user!');
        }
    }

    redirect("$CFG->wwwroot/user/edit.php?id=$user->id&course=$site->id");
} else {                        // List all users for editing
    $stredituser = get_string('edituser');

    $stradministration = get_string('administration');

    $strusers = get_string('users');

    $stredit = get_string('edit');

    $strdelete = get_string('delete');

    $strdeletecheck = get_string('deletecheck');

    $strsearch = get_string('search');

    $strshowallusers = get_string('showallusers');

    if ($firstinitial or $lastinitial or $search or $page) {
        print_header(
            "$site->shortname: $stredituser",
            $site->fullname,
            "<a href=\"index.php\">$stradministration</a> -> " . "<a href=\"users.php\">$strusers</a> -> " . "<a href=\"user.php\">$stredituser</a>"
        );
    } else {
        print_header(
            "$site->shortname: $stredituser",
            $site->fullname,
            "<a href=\"index.php\">$stradministration</a> -> " . "<a href=\"users.php\">$strusers</a> -> $stredituser"
        );
    }

    if ($confirmuser and confirm_sesskey()) {
        if (!$user = get_record('user', 'id', (string)$confirmuser)) {
            error('No such user!');
        }

        unset($confirmeduser);

        $confirmeduser->id = $confirmuser;

        $confirmeduser->confirmed = 1;

        $confirmeduser->timemodified = time();

        if (update_record('user', $confirmeduser)) {
            notify(get_string('userconfirmed', '', fullname($user, true)));
        } else {
            notify(get_string('usernotconfirmed', '', fullname($user, true)));
        }
    } elseif ($delete and confirm_sesskey()) {              // Delete a selected user, after confirmation
        if (!$user = get_record('user', 'id', (string)$delete)) {
            error('No such user!');
        }

        $primaryadmin = get_admin();

        if ($user->id == $primaryadmin->id) {
            error('You are not allowed to delete the primary admin user!');
        }

        if ($confirm != md5($delete)) {
            $fullname = fullname($user, true);

            notice_yesno(
                get_string('deletecheckfull', '', "'$fullname'"),
                "user.php?delete=$delete&confirm=" . md5($delete) . "&sesskey=$USER->sesskey",
                'user.php'
            );

            exit;
        } elseif (!$user->deleted) {
            unset($updateuser);

            $updateuser->id = $user->id;

            $updateuser->deleted = '1';

            $updateuser->username = "$user->email." . time();  // Remember it just in case
            $updateuser->email = '';               // Clear this field to free it up
            $updateuser->timemodified = time();

            if (update_record('user', $updateuser)) {
                unenrol_student($user->id);  // From all courses
                remove_teacher($user->id);   // From all courses
                remove_admin($user->id);

                notify(get_string('deletedactivity', '', fullname($user, true)));
            } else {
                notify(get_string('deletednot', '', fullname($user, true)));
            }
        }
    }

    // Carry on with the user listing

    $columns = ['firstname', 'lastname', 'email', 'city', 'country', 'lastaccess'];

    foreach ($columns as $column) {
        $string[$column] = get_string((string)$column);

        if ($sort != $column) {
            $columnicon = '';

            if ('lastaccess' == $column) {
                $columndir = 'DESC';
            } else {
                $columndir = 'ASC';
            }
        } else {
            $columndir = 'ASC' == $dir ? 'DESC' : 'ASC';

            if ('lastaccess' == $column) {
                $columnicon = 'ASC' == $dir ? 'up' : 'down';
            } else {
                $columnicon = 'ASC' == $dir ? 'down' : 'up';
            }

            $columnicon = " <img src=\"$CFG->pixpath/t/$columnicon.gif\">";
        }

        $$column = "<a href=\"user.php?sort=$column&dir=$columndir&search=$search&firstinitial=$firstinitial&lastinitial=$lastinitial\">" . $string[$column] . "</a>$columnicon";
    }

    if ('name' == $sort) {
        $sort = 'firstname';
    }

    $users = get_users_listing($sort, $dir, $page * $perpage, $perpage, $search, $firstinitial, $lastinitial);

    $usercount = get_users(false);

    $usersearchcount = get_users(false, $search, true, '', '', $firstinitial, $lastinitial);

    if ($search or $firstinitial or $lastinitial) {
        print_heading("$usersearchcount / $usercount " . get_string('users'));

        $usercount = $usersearchcount;
    } else {
        print_heading("$usercount " . get_string('users'));
    }

    $alphabet = explode(',', get_string('alphabet'));

    $strall = get_string('all');

    /// Bar of first initials

    echo '<center><p align="center">';

    echo get_string('firstname') . ' : ';

    if ($firstinitial) {
        echo ' <a href="user.php?sort=firstname&dir=ASC&' . "perpage=$perpage&lastinitial=$lastinitial\">$strall</a> ";
    } else {
        echo " <b>$strall</b> ";
    }

    foreach ($alphabet as $letter) {
        if ($letter == $firstinitial) {
            echo " <b>$letter</b> ";
        } else {
            echo ' <a href="user.php?sort=firstname&dir=ASC&' . "perpage=$perpage&lastinitial=$lastinitial&firstinitial=$letter\">$letter</a> ";
        }
    }

    echo '<br>';

    /// Bar of last initials

    echo get_string('lastname') . ' : ';

    if ($lastinitial) {
        echo ' <a href="user.php?sort=lastname&dir=ASC&' . "perpage=$perpage&firstinitial=$firstinitial\">$strall</a> ";
    } else {
        echo " <b>$strall</b> ";
    }

    foreach ($alphabet as $letter) {
        if ($letter == $lastinitial) {
            echo " <b>$letter</b> ";
        } else {
            echo ' <a href="user.php?sort=lastname&dir=ASC&' . "perpage=$perpage&firstinitial=$firstinitial&lastinitial=$letter\">$letter</a> ";
        }
    }

    echo '</p>';

    echo '</center>';

    print_paging_bar(
        $usercount,
        $page,
        $perpage,
        "user.php?sort=$sort&dir=$dir&perpage=$perpage&firstinitial=$firstinitial&lastinitial=$lastinitial&search=$search&"
    );

    flush();

    if (!$users) {
        $match = [];

        if ($search) {
            $match[] = $search;
        }

        if ($firstinitial) {
            $match[] = get_string('firstname') . ": $firstinitial" . '___';
        }

        if ($lastinitial) {
            $match[] = get_string('lastname') . ": $lastinitial" . '___';
        }

        $matchstring = implode(', ', $match);

        print_heading(get_string('nousersmatching', '', $matchstring));
    } else {
        $countries = get_list_of_countries();

        foreach ($users as $key => $user) {
            if (!empty($user->country)) {
                $users[$key]->country = $countries[$user->country];
            }
        }

        if ('country' == $sort) {  // Need to resort by full country name, not code
            foreach ($users as $user) {
                $susers[$user->id] = $user->country;
            }

            asort($susers);

            foreach ($susers as $key => $value) {
                $nusers[] = $users[$key];
            }

            $users = $nusers;
        }

        $table->head = ["$firstname / $lastname", $email, $city, $country, $lastaccess, '', '', ''];

        $table->align = ['left', 'left', 'left', 'left', 'left', 'center', 'center', 'center'];

        $table->width = '95%';

        foreach ($users as $user) {
            if ($user->id == $USER->id or 'changeme' == $user->username) {
                $deletebutton = '';
            } else {
                $deletebutton = "<a href=\"user.php?delete=$user->id&sesskey=$USER->sesskey\">$strdelete</a>";
            }

            if ($user->lastaccess) {
                $strlastaccess = format_time(time() - $user->lastaccess);
            } else {
                $strlastaccess = get_string('never');
            }

            if (0 == $user->confirmed) {
                $confirmbutton = "<a href=\"user.php?confirmuser=$user->id&sesskey=$USER->sesskey\">" . get_string('confirm') . '</a>';
            } else {
                $confirmbutton = '';
            }

            $fullname = fullname($user, true);

            $table->data[] = [
                "<a href=\"../user/view.php?id=$user->id&course=$site->id\">$fullname</a>",
                (string)$user->email,
                (string)$user->city,
                (string)$user->country,
                $strlastaccess,
                "<a href=\"../user/edit.php?id=$user->id&course=$site->id\">$stredit</a>",
                $deletebutton,
                $confirmbutton,
            ];
        }

        echo '<table align=center cellpadding=10><tr><td>';

        echo '<form action=user.php method=post>';

        echo "<input type=text name=search value=\"$search\" size=20>";

        echo "<input type=submit value=\"$strsearch\">";

        if ($search) {
            echo "<input type=\"button\" onclick=\"document.location='user.php';\" value=\"$strshowallusers\">";
        }

        echo '</form>';

        echo '</td></tr></table>';

        if (is_internal_auth()) {
            print_heading("<a href=\"user.php?newuser=true&sesskey=$USER->sesskey\">" . get_string('addnewuser') . '</a>');
        }

        print_table($table);

        print_paging_bar(
            $usercount,
            $page,
            $perpage,
            "user.php?sort=$sort&dir=$dir&perpage=$perpage" . "&firstinitial=$firstinitial&lastinitial=$lastinitial&search=$search&"
        );
    }

    if (is_internal_auth()) {
        print_heading("<a href=\"user.php?newuser=true&sesskey=$USER->sesskey\">" . get_string('addnewuser') . '</a>');
    }

    print_footer();

    //--------------------------------------------

    // MOODLE4XOOPS - J. BAUDIN

    //--------------------------------------------

    require_once "$CFG->dirroot/footer.php";

    //--------------------------------------------
}
