<?php

declare(strict_types=1);

//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
require_once __DIR__ . '/class/textsanitizer.php';

/**
 * setting manager for Moodle4Xoops installer
 * based on setting manager for XOOPS installer
 *
 * @author  Haruki Setoyama  <haruki@planewave.org>
 * @version $Id: settingmanager.php,v 1.10 2003/09/26 07:11:21 okazu Exp $
 **/
class setting_manager
{
    public $database;

    public $dbhost;

    public $dbuname;

    public $dbpass;

    public $dbname;

    public $prefix;

    public $dbpconnect;

    public $moodle4xoops_root_path;

    public $root_path;

    public $moodle4xoops_url;

    public $xoops_url;

    public $root_path_admin;

    public $root_path_dataroot;

    public $root_directory_permissions;

    public $sanitizer;

    public function __construct($post = false)
    {
        global $HTTP_SERVER_VARS;

        $this->sanitizer = &TextSanitizer::getInstance();

        if ($post) {
            $this->readPost();
        } else {
            $this->database = 'mysql';

            $this->dbhost = 'localhost';

            $this->prefix = 'mdl_';

            $this->db_pconnect = 0;

            $this->root_path_admin = 'admin';

            $this->root_directory_permissions = '0777';

            $this->moodle4xoops_root_path = str_replace('\\', '/', getcwd()); // "

            $this->moodle4xoops_root_path = str_replace('/install', '/moodle', $this->moodle4xoops_root_path);

            $this->root_path = str_replace('\\', '/', getcwd()); // "

            $this->root_path = str_replace('/install', '', $this->root_path);

            $this->root_path_dataroot = str_replace('\\', '/', getcwd()); // "

            $this->root_path_dataroot = str_replace('/install', '/moodle/moodledata', $this->root_path_dataroot);

            $filepath = (!empty($HTTP_SERVER_VARS['REQUEST_URI'])) ? dirname($HTTP_SERVER_VARS['REQUEST_URI']) : dirname($HTTP_SERVER_VARS['SCRIPT_NAME']);

            $filepath = str_replace('\\', '/', $filepath); // "

            $filepath = str_replace('/install', '/moodle', $filepath);

            if ('/' == mb_substr($filepath, 0, 1)) {
                $filepath = mb_substr($filepath, 1);
            }

            if ('/' == mb_substr($filepath, -1)) {
                $filepath = mb_substr($filepath, 0, -1);
            }

            //$protocol = ($HTTP_SERVER_VARS['HTTP'] == 'on') ? 'https://' : 'http://';

            $protocol = (isset($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) ? 'https://' : 'http://';

            $this->moodle4xoops_url = (!empty($filepath)) ? $protocol . $HTTP_SERVER_VARS['HTTP_HOST'] . '/' . $filepath : $protocol . $HTTP_SERVER_VARS['HTTP_HOST'];
        }
    }

    public function readPost()
    {
        global $_POST;

        if (isset($_POST['database'])) {
            $this->database = $this->sanitizer->stripSlashesGPC($_POST['database']);
        }

        if (isset($_POST['dbhost'])) {
            $this->dbhost = $this->sanitizer->stripSlashesGPC($_POST['dbhost']);
        }

        if (isset($_POST['dbuname'])) {
            $this->dbuname = $this->sanitizer->stripSlashesGPC($_POST['dbuname']);
        }

        if (isset($_POST['dbpass'])) {
            $this->dbpass = $this->sanitizer->stripSlashesGPC($_POST['dbpass']);
        }

        if (isset($_POST['dbname'])) {
            $this->dbname = $this->sanitizer->stripSlashesGPC($_POST['dbname']);
        }

        if (isset($_POST['prefix'])) {
            $this->prefix = $this->sanitizer->stripSlashesGPC($_POST['prefix']);
        }

        if (isset($_POST['dbpconnect'])) {
            $this->dbpconnect = (int)$_POST['dbpconnect'] > 0 ? 1 : 0;
        }

        if (isset($_POST['moodle4xoops_root_path'])) {
            $this->moodle4xoops_root_path = $this->sanitizer->stripSlashesGPC($_POST['moodle4xoops_root_path']);
        }

        if (isset($_POST['moodle4xoops_url'])) {
            $this->moodle4xoops_url = $this->sanitizer->stripSlashesGPC($_POST['moodle4xoops_url']);
        }

        if (isset($_POST['root_path'])) {
            $this->root_path = $this->sanitizer->stripSlashesGPC($_POST['root_path']);
        }

        if (isset($_POST['xoops_url'])) {
            $this->xoops_url = $this->sanitizer->stripSlashesGPC($_POST['xoops_url']);
        }

        if (isset($_POST['root_path_admin'])) {
            $this->root_path_admin = $this->sanitizer->stripSlashesGPC($_POST['root_path_admin']);
        }

        if (isset($_POST['root_path_dataroot'])) {
            $this->root_path_dataroot = $this->sanitizer->stripSlashesGPC($_POST['root_path_dataroot']);
        }

        if (isset($_POST['root_directory_permissions'])) {
            $this->root_directory_permissions = $this->sanitizer->stripSlashesGPC($_POST['root_directory_permissions']);
        }
    }

    public function readConstant()
    {
        //if(defined('XOOPS_DB_TYPE'))

        //$this->database = XOOPS_DB_TYPE;

        if (defined('XOOPS_DB_HOST')) {
            $this->dbhost = XOOPS_DB_HOST;
        }

        if (defined('XOOPS_DB_USER')) {
            $this->dbuname = XOOPS_DB_USER;
        }

        if (defined('XOOPS_DB_PASS')) {
            $this->dbpass = XOOPS_DB_PASS;
        }

        if (defined('XOOPS_DB_NAME')) {
            $this->dbname = XOOPS_DB_NAME;
        }

        if (defined('XOOPS_DB_PREFIX')) {
            $this->prefix = XOOPS_DB_PREFIX;
        }

        if (defined('MDF_DB_PCONNECT')) {
            $this->dbpconnect = (int)MDL_DB_PCONNECT > 0 ? 1 : 0;
        }

        if (defined('XOOPS_ROOT_PATH')) {
            $this->root_path = XOOPS_ROOT_PATH;
        }

        if (defined('XOOPS_URL')) {
            $this->xoops_url = XOOPS_URL;
        }

        if (defined('MDL_URL')) {
            $this->moodle4xoops_url = MDL_URL;
        }

        if (defined('MDL_ROOT_PATH')) {
            $this->moodle4xoops_root_path = str_replace('\\', '/', getcwd());
        }

        $this->moodle4xoops_root_path = str_replace('/install', '/moodle', $this->moodle4xoops_root_path);

        if (defined('MDL_ROOT_PATH_ADMIN')) {
            $this->root_path_admin = MDL_ROOT_PATH_ADMIN;
        }

        if (defined('MDL_DATAROOT')) {
            $this->root_path_dataroot = str_replace('\\', '/', getcwd());
        } // "

        $this->root_path_dataroot = str_replace('/install', '/moodle/moodledata', $this->root_path_dataroot);

        if (defined('MDL_DIRECTORYPERMISSIONS')) {
            $this->root_directory_permissions = MDL_DIRECTORYPERMISSIONS;
        }
    }

    public function checkData()
    {
        $ret = '';

        $error = [];

        if (empty($this->dbhost)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L27);
        }

        if (empty($this->dbname)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L29);
        }

        if (empty($this->dbuname)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L28);
        }

        if (empty($this->dbpass)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L52);
        }

        if (empty($this->prefix)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L30);
        }

        if (empty($this->moodle4xoops_root_path)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L552);
        }

        if (empty($this->moodle4xoops_url)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L562);
        }

        if (empty($this->root_path)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L55);
        }

        if (empty($this->xoops_url)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L56);
        }

        if (empty($this->root_path_admin)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L950);
        }

        if (empty($this->root_path_dataroot)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L952);
        }

        if (empty($this->root_directory_permissions)) {
            $error[] = sprintf(_INSTALL_L57, _INSTALL_L954);
        }

        if (!empty($error)) {
            foreach ($error as $err) {
                $ret .= "<p><span style='color:#ff0000;'><b>" . $err . "</b></span></p>\n";
            }
        }

        return $ret;
    }

    public function editform()
    {
        $ret = "<table width='100%' class='outer' cellspacing='5'>
                <tr>
                    <th colspan='2'></th>
                </tr>
                <tr valign='top' align='left'>
                    <td class='head'>";

        $ret .= '  </td>
                </tr>
                ';

        $ret .= $this->editform_sub(_INSTALL_L27, _INSTALL_L67, 'dbhost', $this->sanitizer->htmlSpecialChars($this->dbhost));

        $ret .= $this->editform_sub(_INSTALL_L28, _INSTALL_L65, 'dbuname', $this->sanitizer->htmlSpecialChars($this->dbuname));

        $ret .= $this->editform_sub(_INSTALL_L52, _INSTALL_L68, 'dbpass', $this->sanitizer->htmlSpecialChars($this->dbpass));

        $ret .= $this->editform_sub(_INSTALL_L29, _INSTALL_L64, 'dbname', $this->sanitizer->htmlSpecialChars($this->dbname));

        $ret .= $this->editform_sub(_INSTALL_L30, _INSTALL_L63, 'prefix', $this->sanitizer->htmlSpecialChars($this->prefix));

        $ret .= "<tr valign='top' align='left'>
                    <td class='head'>
                        <b>" . _INSTALL_L54 . "</b><br>
                        <span style='font-size:85%;'>" . _INSTALL_L69 . "</span>
                    </td>
                    <td class='even'>
                        <input type='radio' name='dbpconnect' value='1'" . (1 == $this->dbpconnect ? ' checked' : '') . '>' . _INSTALL_L23 . "
                        <input type='radio' name='dbpconnect' value='0'" . (1 != $this->dbpconnect ? ' checked' : '') . '>' . _INSTALL_L24 . '
                    </td>
                </tr>
                ';

        $ret .= $this->editform_sub(_INSTALL_L552, _INSTALL_L592, 'moodle4xoops_root_path', $this->moodle4xoops_root_path);

        $ret .= $this->editform_sub(_INSTALL_L562, _INSTALL_L582, 'moodle4xoops_url', $this->sanitizer->htmlSpecialChars($this->moodle4xoops_url));

        $ret .= $this->editform_sub(_INSTALL_L55, _INSTALL_L59, 'root_path', $this->sanitizer->htmlSpecialChars($this->root_path));

        $ret .= $this->editform_sub(_INSTALL_L56, _INSTALL_L58, 'xoops_url', $this->sanitizer->htmlSpecialChars($this->xoops_url));

        $ret .= $this->editform_sub(_INSTALL_L950, _INSTALL_L951, 'root_path_admin', $this->sanitizer->htmlSpecialChars($this->root_path_admin));

        $ret .= $this->editform_sub(_INSTALL_L952, _INSTALL_L953, 'root_path_dataroot', $this->sanitizer->htmlSpecialChars($this->root_path_dataroot));

        $ret .= $this->editform_sub(_INSTALL_L954, _INSTALL_L955, 'root_directory_permissions', $this->sanitizer->htmlSpecialChars($this->root_directory_permissions));

        $ret .= '</table>';

        return $ret;
    }

    public function editform_sub($title, $desc, $name, $value)
    {
        return "<tr valign='top' align='left'>
                    <td class='head'>
                        <b>" . $title . "</b><br>
                        <span style='font-size:85%;'>" . $desc . "</span>
                    </td>
                    <td class='even'>
                        <input type='text' name='" . $name . "' id='" . $name . "' size='30' maxlength='100' value='" . htmlspecialchars($value, ENT_QUOTES | ENT_HTML5) . "'>
                    </td>
                </tr>
                ";
    }

    public function confirmForm()
    {
        $yesno = 0 == $this->dbpconnect ? _INSTALL_L24 : _INSTALL_L23;

        $ret = "<table border='0' cellpadding='0' cellspacing='0' valign='top' width='90%'><tr><td class='bg2'>
                <table width='100%' border='0' cellpadding='4' cellspacing='1'>";

        $ret .= "            <tr>
                        <td class='bg3'><b>" . _INSTALL_L27 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->dbhost) . "</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>" . _INSTALL_L28 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->dbuname) . "</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>" . _INSTALL_L52 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->dbpass) . "</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>" . _INSTALL_L29 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->dbname) . "</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>" . _INSTALL_L30 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->prefix) . "</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>" . _INSTALL_L54 . "</b></td>
                        <td class='bg1'>" . $yesno . "</td>
                    </tr>
          	    <tr>
                        <td class='bg3'><b>" . _INSTALL_L552 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->moodle4xoops_root_path) . "</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>" . _INSTALL_L56 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->moodle4xoops_url) . "</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>" . _INSTALL_L55 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->root_path) . "</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>" . _INSTALL_L56 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->xoops_url) . "</td>
                    </tr>
                    <tr>
                        <td class='bg3'><b>" . _INSTALL_L950 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->root_path_admin) . "</td>
                    </tr>
                   <tr>
                        <td class='bg3'><b>" . _INSTALL_L952 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->root_path_dataroot) . "</td>
                    </tr>
                   <tr>
                        <td class='bg3'><b>" . _INSTALL_L954 . "</b></td>
                        <td class='bg1'>" . $this->sanitizer->htmlSpecialChars($this->root_directory_permissions) . "</td>
                    </tr>

                </table></td></tr>
            </table>
            <input type='hidden' name='dbhost' value='" . $this->sanitizer->htmlSpecialChars($this->dbhost) . "'>
            <input type='hidden' name='dbuname' value='" . $this->sanitizer->htmlSpecialChars($this->dbuname) . "'>
            <input type='hidden' name='dbpass' value='" . $this->sanitizer->htmlSpecialChars($this->dbpass) . "'>
            <input type='hidden' name='dbname' value='" . $this->sanitizer->htmlSpecialChars($this->dbname) . "'>
            <input type='hidden' name='dbpconnect' value='" . $this->dbpconnect . "'>
            <input type='hidden' name='prefix' value='" . $this->sanitizer->htmlSpecialChars($this->prefix) . "'>
            <input type='hidden' name='moodle4xoops_root_path' value='" . $this->sanitizer->htmlSpecialChars($this->moodle4xoops_root_path) . "'>
            <input type='hidden' name='moodle4xoops_url' value='" . $this->sanitizer->htmlSpecialChars($this->moodle4xoops_url) . "'>
            <input type='hidden' name='root_path' value='" . $this->sanitizer->htmlSpecialChars($this->root_path) . "'>
            <input type='hidden' name='xoops_url' value='" . $this->sanitizer->htmlSpecialChars($this->xoops_url) . "'>
            <input type='hidden' name='root_path_admin' value='" . $this->sanitizer->htmlSpecialChars($this->root_path_admin) . "'>
            <input type='hidden' name='root_path_dataroot' value='" . $this->sanitizer->htmlSpecialChars($this->root_path_dataroot) . "'>
            <input type='hidden' name='root_directory_permissions' value='" . $this->sanitizer->htmlSpecialChars($this->root_directory_permissions) . "'>

            ";

        //<input type='hidden' name='database' value='".$this->sanitizer->htmlSpecialChars($this->database)."'>

        //<input type='hidden' name='db_pconnect' value='".intval($this->db_pconnect)."'>

        return $ret;
    }
}
