<?php

declare(strict_types=1);

// $Id: lib.php,v 1.1 2002/09/26 07:03:22 martin Exp $
// No authentication at all.  This method approves everything!
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
function auth_user_login($username, $password, $MDL_xoopsUser)
{
    // Returns true if the username doesn't exist yet

    // Returns true if the username and password work

    if (!$user = get_user_info_from_db('username', $username)) {
        return true;
    }

    //--------------------------------------------

    // MOODLE4XOOPS - J. BAUDIN

    //--------------------------------------------

    if ('' == $MDL_xoopsUser) {
        return ($user->password == md5($password));
    }

    return ($user->password == $password);
    //--------------------------------------------
}
