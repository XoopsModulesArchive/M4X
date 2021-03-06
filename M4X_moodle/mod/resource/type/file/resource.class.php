<?php

declare(strict_types=1);

// $Id: resource.class.php,v 1.13.2.2 2004/10/19 09:28:17 moodler Exp $

/**
 * Extend the base resource class for file resources
 *
 * Extend the base resource class for file resources
 */
class resource_file extends resource_base
{
    public function __construct($cmid = 0)
    {
        parent::resource_base($cmid);
    }

    public $parameters;

    public $maxparameters = 5;

    /**
     * Sets the parameters property of the extended class
     *
     * Sets the parameters property of the extended file resource class
     *
     * @param USER  global object
     * @param CFG   global object
     */
    public function set_parameters()
    {
        global $USER, $CFG;

        if (!empty($this->course->lang)) {
            $CFG->courselang = $this->course->lang;
        }

        $site = get_site();

        $this->parameters = [
            'label1' => [
                'langstr' => get_string('user'),
                'value' => 'optgroup',
            ],

            'userid' => [
                'langstr' => 'id',
                'value' => $USER->id,
            ],
            'userusername' => [
                'langstr' => get_string('username'),
                'value' => $USER->username,
            ],
            'userpassword' => [
                'langstr' => get_string('password'),
                'value' => $USER->password,
            ],
            'useridnumber' => [
                'langstr' => get_string('idnumber'),
                'value' => $USER->idnumber,
            ],
            'userfirstname' => [
                'langstr' => get_string('firstname'),
                'value' => $USER->firstname,
            ],
            'userlastname' => [
                'langstr' => get_string('lastname'),
                'value' => $USER->lastname,
            ],
            'userfullname' => [
                'langstr' => get_string('fullname'),
                'value' => fullname($USER),
            ],
            'useremail' => [
                'langstr' => get_string('email'),
                'value' => $USER->email,
            ],
            'usericq' => [
                'langstr' => get_string('icqnumber'),
                'value' => $USER->icq,
            ],
            'userphone1' => [
                'langstr' => get_string('phone') . ' 1',
                'value' => $USER->phone1,
            ],
            'userphone2' => [
                'langstr' => get_string('phone') . ' 2',
                'value' => $USER->phone2,
            ],
            'userinstitution' => [
                'langstr' => get_string('institution'),
                'value' => $USER->institution,
            ],
            'userdepartment' => [
                'langstr' => get_string('department'),
                'value' => $USER->department,
            ],
            'useraddress' => [
                'langstr' => get_string('address'),
                'value' => $USER->address,
            ],
            'usercity' => [
                'langstr' => get_string('city'),
                'value' => $USER->city,
            ],
            'usertimezone' => [
                'langstr' => get_string('timezone'),
                'value' => get_user_timezone(),
            ],
            'userurl' => [
                'langstr' => get_string('webpage'),
                'value' => $USER->url,
            ],

            'label2' => [
                'langstr' => '',
                'value' => '/optgroup',
            ],
            'label3' => [
                'langstr' => get_string('course'),
                'value' => 'optgroup',
            ],

            'courseid' => [
                'langstr' => 'id',
                'value' => $this->course->id,
            ],
            'coursefullname' => [
                'langstr' => get_string('fullname'),
                'value' => $this->course->fullname,
            ],
            'courseshortname' => [
                'langstr' => get_string('shortname'),
                'value' => $this->course->shortname,
            ],
            'courseidnumber' => [
                'langstr' => get_string('idnumber'),
                'value' => $this->course->idnumber,
            ],
            'coursesummary' => [
                'langstr' => get_string('summary'),
                'value' => $this->course->summary,
            ],
            'courseformat' => [
                'langstr' => get_string('format'),
                'value' => $this->course->format,
            ],
            'courseteacher' => [
                'langstr' => get_string('wordforteacher'),
                'value' => $this->course->teacher,
            ],
            'courseteachers' => [
                'langstr' => get_string('wordforteachers'),
                'value' => $this->course->teachers,
            ],
            'coursestudent' => [
                'langstr' => get_string('wordforstudent'),
                'value' => $this->course->student,
            ],
            'coursestudents' => [
                'langstr' => get_string('wordforstudents'),
                'value' => $this->course->students,
            ],

            'label4' => [
                'langstr' => '',
                'value' => '/optgroup',
            ],
            'label5' => [
                'langstr' => get_string('miscellaneous'),
                'value' => 'optgroup',
            ],

            'lang' => [
                'langstr' => get_string('preferredlanguage'),
                'value' => current_language(),
            ],
            'sitename' => [
                'langstr' => get_string('fullsitename'),
                'value' => $site->fullname,
            ],
            'currenttime' => [
                'langstr' => get_string('time'),
                'value' => time(),
            ],
            'encryptedcode' => [
                'langstr' => get_string('encryptedcode'),
                'value' => md5($_SERVER['REMOTE_ADDR'] . $CFG->resource_secretphrase),
            ],

            'label6' => [
                'langstr' => '',
                'value' => '/optgroup',
            ],
        ];
    }

    /**
     * Add new instance of file resource
     *
     * Create alltext field before calling base class function.
     *
     * @param mixed $resource
     * @return
     */
    public function add_instance($resource)
    {
        $optionlist = [];

        for ($i = 0; $i < $this->maxparameters; $i++) {
            $parametername = "parameter$i";

            $parsename = "parse$i";

            if (!empty($resource->$parsename) and $resource->$parametername != '-') {
                $optionlist[] = $resource->$parametername . '=' . $resource->$parsename;
            }
        }

        $resource->alltext = implode(',', $optionlist);

        return parent::add_instance($resource);
    }

    /**
     * Update instance of file resource
     *
     * Create alltext field before calling base class function.
     *
     * @param mixed $resource
     * @return
     */
    public function update_instance($resource)
    {
        $optionlist = [];

        for ($i = 0; $i < $this->maxparameters; $i++) {
            $parametername = "parameter$i";

            $parsename = "parse$i";

            if (!empty($resource->$parsename) and $resource->$parametername != '-') {
                $optionlist[] = $resource->$parametername . '=' . $resource->$parsename;
            }
        }

        $resource->alltext = implode(',', $optionlist);

        return parent::update_instance($resource);
    }

    /**
     * Display the file resource
     *
     * Displays a file resource embedded, in a frame, or in a popup.
     * Output depends on type of file resource.
     *
     * @param CFG     global object
     * @param THEME   global object
     */
    public function display()
    {
        global $CFG, $THEME;

        /// Set up generic stuff first, including checking for access

        parent::display();

        /// Set up some shorthand variables

        $cm = $this->cm;

        $course = $this->course;

        $resource = $this->resource;

        $this->set_parameters(); // set the parameters array

        ///////////////////////////////////////////////

        /// Possible display modes are:

        /// File displayed in a frame in a normal window

        /// File displayed embedded in a normal page

        /// File displayed in a popup window

        /// File displayed emebedded in a popup window

        /// First, find out what sort of file we are dealing with.

        require_once "$CFG->dirroot/files/mimetypes.php";

        $querystring = '';

        $resourcetype = '';

        $embedded = false;

        $mimetype = mimeinfo('type', $resource->reference);

        $pagetitle = strip_tags($course->shortname . ': ' . $resource->name);

        if ('frame' != $resource->options) {
            if (in_array($mimetype, ['image/gif', 'image/jpeg', 'image/png'], true)) {  // It's an image
                $resourcetype = 'image';

                $embedded = true;
            } elseif ('audio/mp3' == $mimetype) {    // It's an MP3 audio file
                $resourcetype = 'mp3';

                $embedded = true;
            } elseif ('video/x-ms' == mb_substr($mimetype, 0, 10)) {   // It's a Media Player file
                $resourcetype = 'mediaplayer';

                $embedded = true;
            } elseif ('video/quicktime' == $mimetype) {   // It's a Quicktime file
                $resourcetype = 'quicktime';

                $embedded = true;
            } elseif ('text/html' == $mimetype) {    // It's a web page
                $resourcetype = 'html';
            }
        }

        /// Form the parse string

        if (!empty($resource->alltext)) {
            $querys = [];

            $parray = explode(',', $resource->alltext);

            foreach ($parray as $fieldstring) {
                $field = explode('=', $fieldstring);

                $querys[] = urlencode($field[1]) . '=' . urlencode($this->parameters[$field[0]]['value']);
            }

            $querystring = implode('&', $querys);
        }

        /// Set up some variables

        $inpopup = !empty($_GET['inpopup']);

        if (resource_is_url($resource->reference)) {
            $fullurl = $resource->reference;

            if (!empty($querystring)) {
                $urlpieces = parse_url($resource->reference);

                if (empty($urlpieces['query'])) {
                    $fullurl .= '?' . $querystring;
                } else {
                    $fullurl .= '&' . $querystring;
                }
            }
        } else {
            if ($CFG->slasharguments) {
                $relativeurl = "/file.php/{$course->id}/{$resource->reference}";

                if ($querystring) {
                    $relativeurl .= '?' . $querystring;
                }
            } else {
                $relativeurl = "/file.php?file=/{$course->id}/{$resource->reference}";

                if ($querystring) {
                    $relativeurl .= '&' . $querystring;
                }
            }

            $fullurl = "$CFG->wwwroot$relativeurl";
        }

        /// Check whether this is supposed to be a popup, but was called directly

        if ($resource->popup and !$inpopup) {    /// Make a page and a pop-up window
            print_header($pagetitle, $course->fullname, "$this->navigation {$resource->name}", '', '', true, update_module_button($cm->id, $course->id, $this->strresource), navmenu($course, $cm));

            echo "\n<script language=\"Javascript\">";

            echo "\n<!--\n";

            echo "openpopup('/mod/resource/view.php?inpopup=true&id={$cm->id}','resource{$resource->id}','{$resource->popup}');\n";

            echo "\n-->\n";

            echo '</script>';

            if (trim(strip_tags($resource->summary))) {
                $formatoptions->noclean = true;

                print_simple_box(format_text($resource->summary, FORMAT_MOODLE, $formatoptions), 'center');
            }

            $link = "<a href=\"$CFG->wwwroot/mod/resource/view.php?inpopup=true&id={$cm->id}\" target=\"resource{$resource->id}\" onClick=\"return openpopup('/mod/resource/view.php?inpopup=true&id={$cm->id}', 'resource{$resource->id}','{$resource->popup}');\">{$resource->name}</a>";

            echo '<p>&nbsp</p>';

            echo '<p align="center">';

            print_string('popupresource', 'resource');

            echo '<br>';

            print_string('popupresourcelink', 'resource', $link);

            echo '</p>';

            print_footer($course);

            //--------------------------------------------

            // MOODLE4XOOPS - J. BAUDIN

            //--------------------------------------------

            require_once "$CFG->dirroot/footer.php";

            //--------------------------------------------

            exit;
        }

        /// Now check whether we need to display a frameset

        if (empty($_GET['frameset']) and !$embedded and !$inpopup and 'frame' == $resource->options) {
            echo "<head><title>{$course->shortname}: {$resource->name}</title></head>\n";

            echo "<frameset rows=\"$CFG->resource_framesize,*\" border=\"2\">";

            echo "<frame src=\"view.php?id={$cm->id}&type={$resource->type}&frameset=top\">";

            echo "<frame src=\"$fullurl\">";

            echo '</frameset>';

            exit;
        }

        /// We can only get here once per resource, so add an entry to the log

        add_to_log($course->id, 'resource', 'view', "view.php?id={$cm->id}", $resource->id, $cm->id);

        /// If we are in a frameset, just print the top of it

        if (!empty($_GET['frameset']) and 'top' == $_GET['frameset']) {
            print_header($pagetitle, $course->fullname, "$this->navigation <a target=\"$CFG->framename\" href=\"$fullurl\">{$resource->name}</a>", '', '', true, update_module_button($cm->id, $course->id, $this->strresource), navmenu($course, $cm, 'parent'));

            echo '<center><font size=-1>' . text_to_html($resource->summary, true, false) . '</font></center>';

            echo '</body></html>';

            exit;
        }

        /// Display the actual resource

        if ($embedded) {       // Display resource embedded in page
            $strdirectlink = get_string('directlink', 'resource');

            if ($inpopup) {
                print_header($pagetitle);
            } else {
                print_header($pagetitle, $course->fullname, "$this->navigation <a title=\"$strdirectlink\" target=\"$CFG->framename\" href=\"$fullurl\"> {$resource->name}</a>", '', '', true, update_module_button($cm->id, $course->id, $this->strresource), navmenu($course, $cm, 'self'));
            }

            if ('image' == $resourcetype) {
                echo '<center><p>';

                echo "<img title=\"{$resource->name}\" class=\"resourceimage\" src=\"$fullurl\">";

                echo '</p></center>';
            } elseif ('mp3' == $resourcetype) {
                echo '<center><p>';

                echo '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';

                echo '        codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" ';

                echo '        width="600" height="70" id="mp3player" align="">';

                echo "<param name=movie value=\"$CFG->wwwroot/lib/mp3player/mp3player.swf?src=$fullurl&autostart=yes\">";

                echo '<param name=quality value=high>';

                echo '<param name=bgcolor value="#333333">';

                echo "<embed src=\"$CFG->wwwroot/lib/mp3player/mp3player.swf?src=$fullurl&autostart=yes\" ";

                echo ' quality=high bgcolor="#333333" width="600" height="70" name="mp3player" ';

                echo ' type="application/x-shockwave-flash" ';

                echo ' pluginspage="http://www.macromedia.com/go/getflashplayer">';

                echo '</embed>';

                echo '</object>';

                echo '</p></center>';
            } elseif ('mediaplayer' == $resourcetype) {
                echo '<center><p>';

                echo '<object classid="CLSID:22D6f312-B0F6-11D0-94AB-0080C74C7E95"';

                echo '        codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701" ';

                echo '        standby="Loading Microsoft® Windows® Media Player components..." ';

                echo '        id="msplayer" align="" type="application/x-oleobject">';

                echo "<param name=\"Filename\" value=\"$fullurl\">";

                echo '<param name="ShowControls" value=true>';

                echo '<param name="AutoRewind" value=true>';

                echo '<param name="AutoStart" value=true>';

                echo '<param name="Autosize" value=true>';

                echo '<param name="EnableContextMenu" value=true>';

                echo '<param name="TransparentAtStart" value=false>';

                echo '<param name="AnimationAtStart" value=false>';

                echo '<param name="ShowGotoBar" value=false>';

                echo '<param name="EnableFullScreenControls" value=true>';

                echo "\n<embed src=\"$fullurl\" name=\"msplayer\" type=\"$mimetype\" ";

                echo ' ShowControls="1" AutoRewind="1" AutoStart="1" Autosize="0" EnableContextMenu="1"';

                echo ' TransparentAtStart="0" AnimationAtStart="0" ShowGotoBar="0" EnableFullScreenControls="1"';

                echo ' pluginspage="http://www.microsoft.com/Windows/Downloads/Contents/Products/MediaPlayer/">';

                echo '</embed>';

                echo '</object>';

                echo '</p></center>';
            } elseif ('quicktime' == $resourcetype) {
                echo '<center><p>';

                echo '<object classid="CLSID:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"';

                echo '        codebase="http://www.apple.com/qtactivex/qtplugin.cab" ';

                echo '        height="450" width="600"';

                echo '        id="quicktime" align="" type="application/x-oleobject">';

                echo "<param name=\"src\" value=\"$fullurl\">";

                echo '<param name="autoplay" value=true>';

                echo '<param name="loop" value=true>';

                echo '<param name="controller" value=true>';

                echo '<param name="scale" value="aspect">';

                echo "\n<embed src=\"$fullurl\" name=\"quicktime\" type=\"$mimetype\" ";

                echo ' height="450" width="600" scale="aspect"';

                echo ' autoplay="true" controller="true" loop="true" ';

                echo ' pluginspage="http://quicktime.apple.com/">';

                echo '</embed>';

                echo '</object>';

                echo '</p></center>';
            }

            if (trim($resource->summary)) {
                $formatoptions->noclean = true;

                print_simple_box(format_text($resource->summary, FORMAT_MOODLE, $formatoptions, $course->id), 'center');
            }

            if ($inpopup) {
                echo "<center><p>(<a href=\"$fullurl\">$strdirectlink</a>)</p></center>";
            } else {
                print_spacer(20, 20);

                print_footer($course);

                //--------------------------------------------

                // MOODLE4XOOPS - J. BAUDIN

                //--------------------------------------------

                require_once "$CFG->dirroot/footer.php";

                //--------------------------------------------
            }
        } else {              // Display the resource on it's own
            redirect($fullurl);
        }
    }

    /**
     * Setup a new file resource
     *
     * Display a form to create a new or edit an existing file resource
     *
     * @param mixed $form
     */
    public function setup($form)
    {
        global $CFG, $usehtmleditor, $RESOURCE_WINDOW_OPTIONS;

        parent::setup($form);

        $this->set_parameters(); // set the parameter array for the form

        $strfilename = get_string('location');

        $strnote = get_string('note', 'resource');

        $strchooseafile = get_string('chooseafile', 'resource');

        $strnewwindow = get_string('newwindow', 'resource');

        $strnewwindowopen = get_string('newwindowopen', 'resource');

        $strsearch = get_string('searchweb', 'resource');

        foreach ($RESOURCE_WINDOW_OPTIONS as $optionname) {
            $stringname = "str$optionname";

            $$stringname = get_string("new$optionname", 'resource');

            $window->$optionname = '';

            $jsoption[] = "\"$optionname\"";
        }

        $frameoption = '"framepage"';

        $popupoptions = implode(',', $jsoption);

        $jsoption[] = $frameoption;

        $alloptions = implode(',', $jsoption);

        if ($form->instance) {     // Re-editing
            if (!$form->popup) {
                $windowtype = 'page';   // No popup text => in page

                foreach ($RESOURCE_WINDOW_OPTIONS as $optionname) {
                    $defaultvalue = "resource_popup$optionname";

                    $window->$optionname = $CFG->$defaultvalue;
                }
            } else {
                $windowtype = 'popup';

                $rawoptions = explode(',', $form->popup);

                foreach ($rawoptions as $rawoption) {
                    $option = explode('=', trim($rawoption));

                    $optionname = $option[0];

                    $optionvalue = $option[1];

                    if ('height' == $optionname or 'width' == $optionname) {
                        $window->$optionname = $optionvalue;
                    } elseif ($optionvalue) {
                        $window->$optionname = 'checked';
                    }
                }
            }
        } else {
            foreach ($RESOURCE_WINDOW_OPTIONS as $optionname) {
                $defaultvalue = "resource_popup$optionname";

                $window->$optionname = $CFG->$defaultvalue;
            }

            $windowtype = ($CFG->resource_popup) ? 'popup' : 'page';

            if (empty($form->options)) {
                $form->options = 'frame';

                $form->reference = $CFG->resource_defaulturl;
            }
        }

        if (empty($form->reference)) {
            $form->reference = $CFG->resource_defaulturl;
        }

        /// set the 5 parameter defaults

        $alltextfield = [];

        for ($i = 0; $i < $this->maxparameters; $i++) {
            $alltextfield[] = [
                'parameter' => '',
                'parse' => '',
            ];
        }

        /// load up any stored parameters

        if (!empty($form->alltext)) {
            $parray = explode(',', $form->alltext);

            foreach ($parray as $key => $fieldstring) {
                $field = explode('=', $fieldstring);

                $alltextfield[$key]['parameter'] = $field[0];

                $alltextfield[$key]['parse'] = $field[1];
            }
        }

        include "$CFG->dirroot/mod/resource/type/file/file.html";

        parent::setup_end();
    }
}
