<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/include/cp_header.php';

if (file_exists('../language/' . $xoopsConfig['language'] . '/modinfo.php')) {
    include '../language/' . $xoopsConfig['language'] . '/modinfo.php';
} else {
    include '../language/english/modinfo.php';
}

require_once XOOPS_ROOT_PATH . '/class/xoopstree.php';
require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
require_once XOOPS_ROOT_PATH . '/include/xoopscodes.php';
require_once XOOPS_ROOT_PATH . '/class/module.errorhandler.php';
$myts = MyTextSanitizer::getInstance();
$eh = new ErrorHandler();
$mytree = new XoopsTree($xoopsDB->prefix('moodle4xoops_cat'), 'cid', 'pid');

xoops_cp_header();

echo "<SCRIPT language=\"JavaScript\"> 
		    function popup_M4X() {
		      window.open('../install/index.php');
		    }
		  </SCRIPT>";

echo '<h4>' . _MI_MOO_NAME . '</h4>';
echo ' - <a href="javascript:popup_M4X()">' . _MI_MOO_ADMENU1 . '</a><br>';

xoops_cp_footer();
