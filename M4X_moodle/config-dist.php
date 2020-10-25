<?php

declare(strict_types=1);

// $Id: config-dist.php,v 1.52 2004/03/12 07:34:14 moodler Exp $
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// Moodle configuration file                                             //
//                                                                       //
// This file should be renamed "config.php" in the top-level directory   //
//                                                                       //
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 1999-2004  Martin Dougiamas  http://dougiamas.com       //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////
if (!defined('MOODLE4XOOPS_CONFIG_INCLUDED')) {
    define('MOODLE4XOOPS_CONFIG_INCLUDED', 1);

    unset($CFG);  // Ignore this line

    //=========================================================================

    // 1. DATABASE SETUP

    //=========================================================================

    // First, you need to configure the database where all Moodle data       //

    // will be stored.  This database must already have been created         //

    // and a username/password created to access it.                         //

    //                                                                       //

    //   mysql      - the prefix is optional, but useful when installing     //

    //                into databases that already contain tables.            //

    //   postgres7  - the prefix is REQUIRED, regardless of whether the      //

    //                database already contains tables.                      //

    //                                                                       //

    // A special case exists when using PostgreSQL databases via sockets.    //

    // Define dbhost as follows, leaving dbname, dbuser, dbpass BLANK!:      //

    //    $CFG->dbhost = " user='muser' password='mpass' dbname='mdata'";    //

    //--------------------------------------------

    // MOODLE4XOOPS - J. BAUDIN

    //--------------------------------------------

    //=========================================================================

    // MOODLE4XOOPS Web Install

    //=========================================================================

    /*
    define('MDL_WWWROOT', 'http://example.com/moodle');
    define('MDL_DIRROOT', '/home/example/www/moodle');
    define('MDL_DIRXOOPS', '/home/example/www');
    define('MDL_DATAROOT', '/home/example/moodledata');
    define('MDL_DIRECTORYPERMISSIONS', '0777');
    define('MDL_ADMIN', 'admin');
    define('MDL_PREFIX', 'mdl_');
    */

    define('MDL_ROOT_PATH_XOOPS', '/home/example/www');

    define('MDL_ROOT_PATH', '/home/example/www/modules/moodle');

    define('MDL_URL', 'http://example.com/modules/moodle');

    define('MDL_DATAROOT', '/home/example/www/modules/moodle/dataroot');

    define('MDL_PREFIX', 'mdl_');

    define('MDL_DB_HOST', 'localhost');

    define('MDL_DB_NAME', 'dbname');

    define('MDL_DB_USER', 'username');

    define('MDL_DB_PASS', 'password');

    define('MDL_ADMIN', 'admin');

    define('MDL_DIRECTORYPERMISSIONS', '0777');

    define('MDL_DB_PCONNECT', '0');

    //=========================================================================

    //If you use the same database with Xoops :

    //=========================================================================

    $CFG->dirxoops = MDL_ROOT_PATH_XOOPS;

    require_once "$CFG->dirxoops/mainfile.php";

    global $xoopsDB, $xoopsConfig, $xoopsTheme, $xoopsUser;

    $CFG->wwwroot = MDL_URL;

    $CFG->dirroot = MDL_ROOT_PATH;

    $CFG->dataroot = MDL_DATAROOT;

    $CFG->directorypermissions = MDL_DIRECTORYPERMISSIONS;

    $CFG->admin = MDL_ADMIN;

    $CFG->prefix = MDL_PREFIX;

    $CFG->dbtype = XOOPS_DB_TYPE;

    $CFG->dbhost = MDL_DB_HOST;

    $CFG->dbname = MDL_DB_NAME;

    $CFG->dbuser = MDL_DB_USER;

    $CFG->dbpass = MDL_DB_PASS;

    $CFG->dbpersist = MDL_DB_PCONNECT;

    //=========================================================================

    //If you don't use the same database with Xoops :

    //=========================================================================

    //$CFG->dbtype    = 'mysql';       // mysql or postgres7 (for now)

    //$CFG->dbhost    = 'localhost';   // eg localhost or db.isp.com

    //$CFG->dbname    = 'moodle';      // database name, eg moodle

    //$CFG->dbuser    = 'username';    // your database username

    //$CFG->dbpass    = 'password';    // your database password

    //$CFG->dbpersist = false;         // Should database connections be reused?

    // "false" is the most stable setting

    // "true" can improve performance sometimes

    //--------------------------------------------

    //=========================================================================

    // 2. WEB SITE LOCATION

    //=========================================================================

    // Now you need to tell Moodle where it is located. Specify the full

    // web address to where moodle has been installed.  If your web site

    // is accessible via multiple URLs then choose the most natural one

    // that your students would use.  Do not include a trailing slash

    //$CFG->wwwroot   = 'http://example.com/moodle';

    //=========================================================================

    // 3. SERVER FILES LOCATION

    //=========================================================================

    // Next, specify the full OS directory path to this same location

    // Make sure the upper/lower case is correct.  Some examples:

    //    $CFG->dirroot = 'c:\FoxServ\www\moodle';    // Windows

    //    $CFG->dirroot = '/var/www/html/moodle';     // Redhat Linux

    //    $CFG->dirroot = '/home/example/www/moodle'; // Cpanel host

    //    $CFG->prefix  = 'mdl_';        // Prefix to use for all table names

    //$CFG->wwwroot   = '/home/example/www/moodle';

    //=========================================================================

    // 4. DATA FILES LOCATION

    //=========================================================================

    // Now you need a place where Moodle can save uploaded files.  This

    // directory should be readable AND WRITEABLE by the web server user

    // (usually 'nobody' or 'apache'), but it should not be accessible

    // directly via the web.

    // - On hosting systems you might need to make sure that your "group" has

    //   no permissions at all, but that "others" have full permissions.

    // - On Windows systems you might specify something like 'c:\moodledata'

    //$CFG->dataroot  = '/home/example/moodledata';

    //=========================================================================

    // 5. DATA FILES PERMISSIONS

    //=========================================================================

    // The following parameter sets the permissions of new directories

    // created by Moodle within the data directory.  The format is in

    // octal format (as used by the Unix utility chmod, for example).

    // The default is usually OK, but you may want to change it to 0750

    // if you are concerned about world-access to the files (you will need

    // to make sure the web server process (eg Apache) can access the files.

    // NOTE: the prefixed 0 is important, and don't use quotes.

    //$CFG->directorypermissions = 0777;

    //=========================================================================

    // 6. DIRECTORY LOCATION  (most people can just ignore this setting)

    //=========================================================================

    // A very few webhosts use /admin as a special URL for you to access a

    // control panel or something.  Unfortunately this conflicts with the

    // standard location for the Moodle admin pages.  You can fix this by

    // renaming the admin directory in your installation, and putting that

    // new name here.  eg "moodleadmin".  This will fix admin links in Moodle.

    //$CFG->admin = 'admin';

    //=========================================================================

    // 7. OTHER MISCELLANEOUS SETTINGS (ignore these for new installations)

    //=========================================================================

    // Prevent users from updating their profile images

    //      $CFG->disableuserimages = true;

    // Prevent scheduled backups from operating (and hide the GUI for them)

    // Useful for webhost operators who have alternate methods of backups

    //      $CFG->disablescheduledbackups = true;

    // Restrict certain usernames from doing things that may mess up a site

    // This is especially useful for demonstration teacher accounts

    //      $CFG->restrictusers = 'teacher,fred,jim';

    // Turning this on will make Moodle filter more than usual, including

    // forum subjects, activity names and so on (in ADDITION to the normal

    // texts like forum postings, journals etc).  This is mostly only useful

    // when using the multilang filter.   This feature may not be complete.

    //      $CFG->filterall = true;

    // Setting this to true will enable admins to edit any post at any time

    //      $CFG->admineditalways = true;

    //=========================================================================
    // ALL DONE!  To continue installation, visit your main page with a browser
    //=========================================================================
    if (file_exists("$CFG->dirroot/lib/setup.php")) {       // Do not edit
        require_once "$CFG->dirroot/lib/setup.php";
    }

    /* else {
        if ($CFG->dirroot == __DIR__) {
            echo "<p>Could not find this file: $CFG->dirroot/lib/setup.php</p>";
            echo "<p>Are you sure all your files have been uploaded?</p>";
        } else {
            echo "<p>Error detected in config.php</p>";
            echo "<p>Error in: \$CFG->dirroot = '$CFG->dirroot';</p>";
            echo "<p>Try this: \$CFG->dirroot = '".__DIR__."';</p>";
        }
        die;
    }*/

    // MAKE SURE WHEN YOU EDIT THIS FILE THAT THERE ARE NO SPACES, BLANK LINES,

    // RETURNS, OR ANYTHING ELSE AFTER THE TWO CHARACTERS ON THE NEXT LINE.

    //--------------------------------------------

    // MOODLE4XOOPS - J. BAUDIN

    //--------------------------------------------

    $id_xoops = $id;

    include "$CFG->dirxoops/header.php";

    $id = $id_xoops;

    //--------------------------------------------
}
