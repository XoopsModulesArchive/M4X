<?php

declare(strict_types=1);

// $Id: index.php,v 1.35 2003/11/07 20:16:11 okazu Exp $
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

//error_reporting (E_ALL);

define('MOODLE4XOOPS_CONFIG_INCLUDED', 0);

require_once __DIR__ . '/passwd.php';
if (INSTALL_USER != '' || INSTALL_PASSWD != '') {
    if (!isset($HTTP_SERVER_VARS['PHP_AUTH_USER'])) {
        header('WWW-Authenticate: Basic realm="MOODLE4XOOPS Installer"');

        header('HTTP/1.0 401 Unauthorized');

        echo 'You can not access this MOODLE4XOOPS installer.';

        exit;
    }

    if (INSTALL_USER != '' && INSTALL_USER != $HTTP_SERVER_VARS['PHP_AUTH_USER']) {
        header('HTTP/1.0 401 Unauthorized');

        echo 'You can not access this MOODLE4XOOPS installer.';

        exit;
    }

    if (INSTALL_PASSWD != $HTTP_SERVER_VARS['PHP_AUTH_PW']) {
        header('HTTP/1.0 401 Unauthorized');

        echo 'You can not access this MOODLE4XOOPS installer.';

        exit;
    }
}

require_once __DIR__ . '/class/textsanitizer.php';
$myts = &TextSanitizer::getInstance();

if (isset($_POST)) {
    foreach ($_POST as $k => $v) {
        $$k = $myts->stripSlashesGPC($v);
    }
}

$language = 'english';
if (!empty($_POST['lang'])) {
    $language = $_POST['lang'];
} else {
    if (isset($HTTP_COOKIE_VARS['install_lang'])) {
        $language = $HTTP_COOKIE_VARS['install_lang'];
    } else {
        $HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE'] = 'en-us;fr-fr';

        if (isset($HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE'])) {
            $accept_langs = explode(',', $HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE']);

            $language_array = ['en' => 'english', 'fr' => 'french'];

            foreach ($accept_langs as $al) {
                $al = mb_strtolower($al);

                $al_len = mb_strlen($al);

                if ($al_len > 2) {
                    if (preg_match('/([a-z]{2});q=[0-9.]+$/', $al, $al_match)) {
                        $al = $al_match[1];
                    } else {
                        continue;
                    }
                }

                if (isset($language_array[$al])) {
                    $language = $language_array[$al];

                    break;
                }
            }
        }
    }
}

if (file_exists('./language/' . $language . '/install.php')) {
    require_once './language/' . $language . '/install.php';
} elseif (file_exists('./language/english/install.php')) {
    require_once './language/english/install.php';

    $language = 'english';
} else {
    echo 'no language file.';

    exit();
}
setcookie('install_lang', $language);

define('_OKIMG', "<img src='img/yes.gif' width='6' height='12' border='0' alt=''> ");
define('_NGIMG', "<img src='img/no.gif' width='6' height='12' border='0' alt=''> ");

$b_back = '';
$b_reload = '';
$b_next = '';

// options for mainfile.php
$xoopsOption['nocommon'] = true;
define('MOODLE4XOOPS_INSTALL', 1);

if (!empty($_POST['op'])) {
    $op = $_POST['op'];
} elseif (!empty($_GET['op'])) {
    $op = $_GET['op'];
} else {
    $op = '';
}

///// main

switch ($op) {
    default:
    case 'langselect':
        $title = _INSTALL_L0;
        $content = '<p>' . _INSTALL_L128 . '</p>' . "<select name='lang'>";

        $langarr = getDirList('./language/');
        foreach ($langarr as $lang) {
            $content .= "<option value='" . $lang . "'";

            if (mb_strtolower($lang) == $language) {
                $content .= ' selected="selected"';
            }

            $content .= '>' . $lang . '</option>';
        }
        $content .= '</select>';

        $b_next = ['start', _INSTALL_L80];
        require __DIR__ . '/install_tpl.php';
        break;
    case 'start':
        $title = _INSTALL_L0;
        $content = "<table width='80%' align='center'><tr><td align='left'>\n";
        require __DIR__ . '/language/' . $language . '/welcome.php';
        $content .= "</td></tr></table>\n";
        $b_next = ['modcheck', _INSTALL_L81];
        require __DIR__ . '/install_tpl.php';
        break;
    case 'modcheck':
        $wok = '/moodle/config.php';
        $title = _INSTALL_L82;
        $content = "<table align='center'><tr><td align='left'>\n";
        $error = false;

        if (file_exists('../' . $wok)) {
            @chmod('../' . $wok, 0666);

            if (!is_writable('../' . $wok)) {
                $content .= _NGIMG . sprintf(_INSTALL_L83, $wok) . '<br>';

                $error = true;
            } else {
                $content .= _OKIMG . sprintf(_INSTALL_L84, $wok) . '<br>';
            }

            if (!$error) {
                $content .= '<p>' . _INSTALL_L87 . '</p>';

                $b_next = ['dbform', _INSTALL_L89];
            } else {
                $content .= '<p>' . _INSTALL_L46 . '</p>';

                $b_reload = true;
            }
        } else {
            $content .= '<p>' . _INSTALL_L40 . '</p>';
        }
        $content .= "</td></tr></table>\n";

        require __DIR__ . '/install_tpl.php';
        break;
    case 'dbform':
        require dirname(__DIR__) . '/moodle/config.php';
        require_once dirname(__DIR__, 3) . '/mainfile.php';
        require_once __DIR__ . '/class/settingmanager.php';
        $sm = new setting_manager();
        $sm->readConstant();
        $content = $sm->editform();
        $title = _INSTALL_L90;
        $b_next = ['dbconfirm', _INSTALL_L91];
        require __DIR__ . '/install_tpl.php';
        break;
    case 'dbconfirm':
        require_once __DIR__ . '/class/settingmanager.php';
        $sm = new setting_manager(true);

        $content = $sm->checkData();
        if (!empty($content)) {
            $content .= $sm->editform();

            $b_next = ['dbconfirm', _INSTALL_L91];

            require __DIR__ . '/install_tpl.php';

            break;
        }

        $title = _INSTALL_L53;
        $content = $sm->confirmForm();
        $b_next = ['dbsave', _INSTALL_L92];
        $b_back = ['', _INSTALL_L93];
        require __DIR__ . '/install_tpl.php';
        break;
    case 'dbsave':
        require_once './class/mainfilemanager.php';
        $title = _INSTALL_L88;
        $mm = new mainfile_manager('../moodle/config.php');

        $ret = $mm->copyDistFile();
        if (!$ret) {
            $content = _INSTALL_L60;

            require __DIR__ . '/install_tpl.php';

            exit();
        }

        $mm->setRewrite('MDL_ROOT_PATH_XOOPS', $myts->stripSlashesGPC($_POST['root_path']));

        //$mm->setRewrite('MDL_ROOT_PATH_XOOPS', $myts->stripSlashesGPC($_POST['xoops_url']));

        $mm->setRewrite('MDL_ROOT_PATH', $myts->stripSlashesGPC($_POST['moodle4xoops_root_path']));
        $mm->setRewrite('MDL_URL', $myts->stripSlashesGPC($_POST['moodle4xoops_url']));

        //$mm->setRewrite('XOOPS_DB_TYPE', $myts->stripSlashesGPC($_POST['database']));

        $mm->setRewrite('MDL_PREFIX', $myts->stripSlashesGPC($_POST['prefix']));
        $mm->setRewrite('MDL_DB_HOST', $myts->stripSlashesGPC($_POST['dbhost']));
        $mm->setRewrite('MDL_DB_USER', $myts->stripSlashesGPC($_POST['dbuname']));
        $mm->setRewrite('MDL_DB_PASS', $myts->stripSlashesGPC($_POST['dbpass']));
        $mm->setRewrite('MDL_DB_NAME', $myts->stripSlashesGPC($_POST['dbname']));
        $mm->setRewrite('MDL_ADMIN', $myts->stripSlashesGPC($_POST['root_path_admin']));

        $mm->setRewrite('MDL_DATAROOT', $myts->stripSlashesGPC($_POST['root_path_dataroot']));
        $mm->setRewrite('MDL_DIRECTORYPERMISSIONS', $myts->stripSlashesGPC($_POST['root_directory_permissions']));

        $mm->setRewrite('MDL_DB_PCONNECT', (int)$_POST['dbpconnect']);

        $ret = $mm->doRewrite();
        if (!$ret) {
            $content = _INSTALL_L60;

            require __DIR__ . '/install_tpl.php';

            exit();
        }

        $content = $mm->report();
        $content .= '<p>' . _INSTALL_L62 . "</p>\n";
        $b_next = ['modcheck_moodledata', _INSTALL_L956];
        require __DIR__ . '/install_tpl.php';
        break;
    case 'modcheck_moodledata':

        $content = "<table align='center'><tr><td align='left'>\n";
        $content .= _INSTALL_L563;
        $content .= "</td></tr></table>\n";
        $b_next = ['finish', _INSTALL_L117];
        require __DIR__ . '/install_tpl.php';
        break;
    case 'finish':

        $title = _INSTALL_L32;
        $content = "<table width='60%' align='center'><tr><td align='left'>\n";
        require __DIR__ . '/language/' . $language . '/finish.php';
        $content .= "</td></tr></table>\n";
        require __DIR__ . '/install_tpl.php';
        break;
}

/*
 * gets list of name of directories inside a directory
 */
function getDirList($dirname)
{
    $dirlist = [];

    if (is_dir($dirname) && $handle = opendir($dirname)) {
        while (false !== ($file = readdir($handle))) {
            if (!preg_match('/^[.]{1,2}$/', $file)) {
                if ('cvs' != mb_strtolower($file) && is_dir($dirname . $file)) {
                    $dirlist[$file] = $file;
                }
            }
        }

        closedir($handle);

        asort($dirlist);

        reset($dirlist);
    }

    return $dirlist;
}

/*
 * gets list of name of files within a directory
 */
function getImageFileList($dirname)
{
    $filelist = [];

    if (is_dir($dirname) && $handle = opendir($dirname)) {
        while (false !== ($file = readdir($handle))) {
            if (!preg_match('/^[.]{1,2}$/', $file) && preg_match('/[.gif|.jpg|.png]$/i', $file)) {
                $filelist[$file] = $file;
            }
        }

        closedir($handle);

        asort($filelist);

        reset($filelist);
    }

    return $filelist;
}

function moodle4xoops_module_gettemplate($dirname, $template, $block = false)
{
    if ($block) {
        $path = MOODLE4XOOPS_ROOT_PATH . '/modules/' . $dirname . '/templates/blocks/' . $template;
    } else {
        $path = MOODLE4XOOPS_ROOT_PATH . '/modules/' . $dirname . '/templates/' . $template;
    }

    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path);

    if (!$lines) {
        return false;
    }

    $ret = '';

    $count = count($lines);

    for ($i = 0; $i < $count; $i++) {
        $ret .= str_replace("\n", "\r\n", str_replace("\r\n", "\n", $lines[$i]));
    }

    return $ret;
}

function check_language($language)
{
    if (file_exists(__DIR__ . '/../modules/system/language/' . $language . '/modinfo.php')) {
        return $language;
    }

    return 'english';
}
