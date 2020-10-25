<?php

declare(strict_types=1);

//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------

require __DIR__ . '/moodle/config.php';
/*if ( file_exists(MDL_ROOT_PATH."/../language/".$xoopsConfig['language']."/modinfo.php") ) {
    include MDL_ROOT_PATH."/../language/".$xoopsConfig['language']."/modinfo.php";
} else {
    include MDL_ROOT_PATH."/../language/english/modinfo.php";
}

    if ( $CFG->moodle4xoops_check == '1' ) {

        $id_xoops = $id;
        require XOOPS_ROOT_PATH."/header.php";
        $id = $id_xoops;


?>
        <SCRIPT language="JavaScript">
            function popup() {
              window.open('./moodle/index.php');
            }

        setTimeout('popup()',5000);
          </SCRIPT>
        <A href="javascript:popup()"><?php echo _MI_MOO_WAIT_MESSAGE ?></A>

<?php
        require "$CFG->dirxoops/footer.php";
    } else {*/
redirect("$CFG->wwwroot/index.php");
//}
