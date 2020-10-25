<?php

declare(strict_types=1);

// $Id: lib.php,v 1.1 2003/04/24 15:01:15 moodler Exp $
// manual method - Just does a simple check against the database
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
function auth_user_login($username, $password, $MDL_xoopsUser)
{
    // Returns false if the username doesn't exist yet

    // Returns true if the username and password work

    if ($user = get_user_info_from_db('username', $username)) {
        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        if ('' == $MDL_xoopsUser) {
            return ($user->password == md5($password));
        }

        return ($user->password == $password);
        //--------------------------------------------
    }

    return false;
}
