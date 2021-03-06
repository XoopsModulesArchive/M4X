<?php

declare(strict_types=1);

// $Id: lib.php,v 1.296.2.7 2004/10/20 23:22:16 stronk7 Exp $
// Library of useful functions

if (defined('COURSE_MAX_LOG_DISPLAY')) {  // Being included again - should never happen!!
    return;
}

define('COURSE_MAX_LOG_DISPLAY', 150);       // days

define('COURSE_MAX_LOGS_PER_PAGE', 1000);    // records

define('COURSE_LIVELOG_REFRESH', 60);        // Seconds

define('COURSE_MAX_RECENT_PERIOD', 172800);  // Two days, in seconds

define('COURSE_MAX_SUMMARIES_PER_PAGE', 10); // courses

define('FRONTPAGENEWS', 0);
define('FRONTPAGECOURSELIST', 1);
define('FRONTPAGECATEGORYNAMES', 2);

function print_recent_selector_form(
    $course,
    $advancedfilter = 0,
    $selecteduser = 0,
    $selecteddate = 'lastlogin',
    $mod = '',
    $modid = 'activity/All',
    $modaction = '',
    $selectedgroup = '',
    $selectedsort = 'default'
) {
    global $USER, $CFG;

    $isteacher = isteacher($course->id);

    if ($advancedfilter) {
        // Get all the possible users

        $users = [];

        if ($courseusers = get_course_users($course->id, '', '', 'u.id, u.firstname, u.lastname')) {
            foreach ($courseusers as $courseuser) {
                $users[$courseuser->id] = fullname($courseuser, $isteacher);
            }
        }

        if ($guest = get_guest()) {
            $users[$guest->id] = fullname($guest);
        }

        if (isadmin()) {
            if ($ccc = get_records('course', '', '', 'fullname')) {
                foreach ($ccc as $cc) {
                    if ($cc->category) {
                        $courses[(string)$cc->id] = (string)$cc->fullname;
                    } else {
                        $courses[(string)$cc->id] = " $cc->fullname (Site)";
                    }
                }
            }

            asort($courses);
        }

        $activities = [];

        $selectedactivity = $modid;

        if ($modinfo = unserialize($course->modinfo)) {
            $section = 0;

            if ('weeks' == $course->format) {  // Body
                $strsection = get_string('week');
            } else {
                $strsection = get_string('topic');
            }

            $activities['activity/All'] = 'All activities';

            $activities['activity/Assignments'] = 'All assignments';

            $activities['activity/Chats'] = 'All chats';

            $activities['activity/Forums'] = 'All forums';

            $activities['activity/Quizzes'] = 'All quizzes';

            $activities['activity/Workshops'] = 'All workshops';

            $activities['section/individual'] = '------------- Individual Activities --------------';

            foreach ($modinfo as $mod) {
                if ('label' == $mod->mod) {
                    continue;
                }

                if (!$mod->visible and !$isteacher) {
                    continue;
                }

                if ($mod->section > 0 and $section != $mod->section) {
                    $activities["section/$mod->section"] = "-------------- $strsection $mod->section --------------";
                }

                $section = $mod->section;

                $mod->name = strip_tags(urldecode($mod->name));

                if (mb_strlen($mod->name) > 55) {
                    $mod->name = mb_substr($mod->name, 0, 50) . '...';
                }

                if (!$mod->visible) {
                    $mod->name = '(' . $mod->name . ')';
                }

                $activities[(string)$mod->cm] = $mod->name;

                if ($mod->cm == $modid) {
                    $selectedactivity = (string)$mod->cm;
                }
            }
        }

        $strftimedate = get_string('strftimedate');

        $strftimedaydate = get_string('strftimedaydate');

        asort($users);

        // Get all the possible dates

        // Note that we are keeping track of real (GMT) time and user time

        // User time is only used in displays - all calcs and passing is GMT

        $timenow = time(); // GMT

        // What day is it now for the user, and when is midnight that day (in GMT).

        $timemidnight = $today = usergetmidnight($timenow);

        $dates = [];

        $dates[(string)$USER->lastlogin] = get_string('lastlogin') . ', ' . userdate($USER->lastlogin, $strftimedate);

        $dates[(string)$timemidnight] = get_string('today') . ', ' . userdate($timenow, $strftimedate);

        if (!$course->startdate or ($course->startdate > $timenow)) {
            $course->startdate = $course->timecreated;
        }

        $numdates = 1;

        while ($timemidnight > $course->startdate and $numdates < 365) {
            $timemidnight -= 86400;

            $timenow -= 86400;

            $dates[(string)$timemidnight] = userdate($timenow, $strftimedaydate);

            $numdates++;
        }

        if ('lastlogin' == $selecteddate) {
            $selecteddate = $USER->lastlogin;
        }

        echo '<form action="recent.php" method="get">';

        echo '<input type=hidden name=chooserecent value="1">';

        echo '<center>';

        echo '<table>';

        if (isadmin()) {
            echo '<tr><td><b>' . get_string('courses') . '</b></td><td>';

            choose_from_menu($courses, 'id', $course->id, '');

            echo '</td></tr>';
        } else {
            echo "<input type=hidden name=id value=\"$course->id\">";
        }

        $sortfields = ['default' => get_string('bycourseorder'), 'dateasc' => get_string('datemostrecentlast'), 'datedesc' => get_string('datemostrecentfirst')];

        echo '<tr><td><b>' . get_string('participants') . '</b></td><td>';

        choose_from_menu($users, 'user', $selecteduser, get_string('allparticipants'));

        echo '</td>';

        echo "<td align='right'><b>" . get_string('since') . '</b></td><td>';

        choose_from_menu($dates, 'date', $selecteddate, get_string('alldays'));

        echo '</td></tr>';

        echo '<tr><td><b>' . get_string('activities') . '</b></td><td>';

        choose_from_menu($activities, 'modid', $selectedactivity, '');

        echo '</td>';

        echo "<td align='right'><b>" . get_string('sortby') . '</b></td><td>';

        choose_from_menu($sortfields, 'sortby', $selectedsort, '');

        echo '</td></tr>';

        echo '<tr>';

        $groupmode = groupmode($course);

        if (VISIBLEGROUPS == $groupmode or ($groupmode and isteacheredit($course->id))) {
            if ($groups = get_records_menu('groups', 'courseid', $course->id, 'name ASC', 'id,name')) {
                echo '<td><b>';

                if (VISIBLEGROUPS == $groupmode) {
                    print_string('groupsvisible');
                } else {
                    print_string('groupsseparate');
                }

                echo ':</b></td><td>';

                choose_from_menu($groups, 'selectedgroup', $selectedgroup, get_string('allgroups'), '', '');

                echo '</td>';
            }
        }

        echo "<td colspan='2' align='right'>";

        echo '<input type=submit value="' . get_string('showrecent') . '">';

        echo '</td></tr>';

        echo '</table>';

        $advancedlink = "<a href=\"$CFG->wwwroot/course/recent.php?id=$course->id&advancedfilter=0\">" . get_string('normalfilter') . '</a>';

        print_heading($advancedlink);

        echo '</center>';

        echo '</form>';
    } else {
        $day_list = ['1', '7', '14', '21', '30'];

        $strsince = get_string('since');

        $strlastlogin = get_string('lastlogin');

        $strday = get_string('day');

        $strdays = get_string('days');

        $heading = '';

        foreach ($day_list as $count) {
            if ('1' == $count) {
                $day = $strday;
            } else {
                $day = $strdays;
            }

            $tmpdate = time() - ($count * 3600 * 24);

            $heading .= "<a href=\"$CFG->wwwroot/course/recent.php?id=$course->id&date=$tmpdate\"> $count $day</a> | ";
        }

        $heading = $strsince . ": <a href=\"$CFG->wwwroot/course/recent.php?id=$course->id\">$strlastlogin</a>" . ' | ' . $heading;

        print_heading($heading);

        $advancedlink = "<a href=\"$CFG->wwwroot/course/recent.php?id=$course->id&advancedfilter=1\">" . get_string('advancedfilter') . '</a>';

        print_heading($advancedlink);
    }
}

function print_log_selector_form(
    $course,
    $selecteduser = 0,
    $selecteddate = 'today',
    $modname = '',
    $modid = 0,
    $modaction = '',
    $selectedgroup = -1
) {
    global $USER, $CFG;

    /// Setup for group handling.

    $isteacher = isteacher($course->id);

    $isteacheredit = isteacheredit($course->id);

    if (SEPARATEGROUPS == $course->groupmode and !$isteacheredit) {
        $selectedgroup = get_current_group($course->id);

        $showgroups = false;
    } elseif ($course->groupmode) {
        $selectedgroup = (-1 == $selectedgroup) ? get_current_group($course->id) : $selectedgroup;

        $showgroups = true;
    } else {
        $selectedgroup = 0;

        $showgroups = false;
    }

    // Get all the possible users

    $users = [];

    if ($course->category) {
        /// If using a group, only get users in that group.

        if ($selectedgroup) {
            $sql = 'SELECT u.id as id, u.firstname, u.lastname, u.lastaccess '
                           . 'FROM '
                           . $CFG->prefix
                           . 'user u,'
                           . $CFG->prefix
                           . 'user_students us,'
                           . $CFG->prefix
                           . 'groups_members gm, '
                           . $CFG->prefix
                           . 'user_teachers ut '
                           . 'WHERE us.course='
                           . $course->id
                           . ' AND gm.groupid='
                           . $selectedgroup
                           . ' AND (gm.userid=us.userid OR gm.userid=ut.userid) AND gm.userid=u.id';

            $courseusers = get_records_sql($sql);
        } else {
            $courseusers = get_course_users($course->id, '', '', 'u.id, u.firstname, u.lastname');
        }
    } else {
        $courseusers = get_site_users('u.lastaccess DESC', 'u.id, u.firstname, u.lastname');
    }

    if ($courseusers) {
        foreach ($courseusers as $courseuser) {
            $users[$courseuser->id] = fullname($courseuser, $isteacher);
        }
    }

    if ($guest = get_guest()) {
        $users[$guest->id] = fullname($guest);
    }

    if (isadmin()) {
        if ($ccc = get_records('course', '', '', 'fullname')) {
            foreach ($ccc as $cc) {
                if ($cc->category) {
                    $courses[(string)$cc->id] = (string)$cc->fullname;
                } else {
                    $courses[(string)$cc->id] = " $cc->fullname (Site)";
                }
            }
        }

        asort($courses);
    }

    $activities = [];

    $selectedactivity = '';

    if ($modinfo = unserialize($course->modinfo)) {
        $section = 0;

        if ('weeks' == $course->format) {  // Bodgy
            $strsection = get_string('week');
        } else {
            $strsection = get_string('topic');
        }

        foreach ($modinfo as $mod) {
            if ('label' == $mod->mod) {
                continue;
            }

            if ($mod->section > 0 and $section != $mod->section) {
                $activities["section/$mod->section"] = "-------------- $strsection $mod->section --------------";
            }

            $section = $mod->section;

            $mod->name = urldecode($mod->name);

            if (mb_strlen($mod->name) > 55) {
                $mod->name = mb_substr($mod->name, 0, 50) . '...';
            }

            if (!$mod->visible) {
                $mod->name = '(' . $mod->name . ')';
            }

            $activities[(string)$mod->cm] = $mod->name;

            if ($mod->cm == $modid) {
                $selectedactivity = (string)$mod->cm;
            }
        }

        if (isadmin() && !$course->category) {
            $activities['site_errors'] = get_string('siteerrors');

            if ('site_errors' === $modid) {
                $selectedactivity = 'site_errors';
            }
        }
    }

    $strftimedate = get_string('strftimedate');

    $strftimedaydate = get_string('strftimedaydate');

    asort($users);

    // Get all the possible dates

    // Note that we are keeping track of real (GMT) time and user time

    // User time is only used in displays - all calcs and passing is GMT

    $timenow = time(); // GMT

    // What day is it now for the user, and when is midnight that day (in GMT).

    $timemidnight = $today = usergetmidnight($timenow);

    // Put today up the top of the list

    $dates = [(string)$timemidnight => get_string('today') . ', ' . userdate($timenow, $strftimedate)];

    if (!$course->startdate or ($course->startdate > $timenow)) {
        $course->startdate = $course->timecreated;
    }

    $numdates = 1;

    while ($timemidnight > $course->startdate and $numdates < 365) {
        $timemidnight -= 86400;

        $timenow -= 86400;

        $dates[(string)$timemidnight] = userdate($timenow, $strftimedaydate);

        $numdates++;
    }

    if ('today' == $selecteddate) {
        $selecteddate = $today;
    }

    echo '<center>';

    echo '<form action="log.php" method="get">';

    echo '<input type=hidden name=chooselog value="1">';

    if (isadmin()) {
        choose_from_menu($courses, 'id', $course->id, '');
    } else {
        echo "<input type=hidden name=id value=\"$course->id\">";
    }

    if ($showgroups) {
        $cgroups = get_groups($course->id);

        foreach ($cgroups as $cgroup) {
            $groups[$cgroup->id] = $cgroup->name;
        }

        choose_from_menu($groups, 'group', $selectedgroup, get_string('allgroups'));
    }

    choose_from_menu($users, 'user', $selecteduser, get_string('allparticipants'));

    choose_from_menu($dates, 'date', $selecteddate, get_string('alldays'));

    choose_from_menu($activities, 'modid', $selectedactivity, get_string('allactivities'), '', '');

    echo '<input type=submit value="' . get_string('showtheselogs') . '">';

    echo '</form>';

    echo '</center>';
}

function make_log_url($module, $url)
{
    switch ($module) {
        case 'user':
        case 'course':
        case 'user':
        case 'file':
        case 'login':
        case 'lib':
        case 'admin':
            return "/$module/$url";
            break;
        case 'library':
        case '':
            return '/';
            break;
        default:
            return "/mod/$module/$url";
            break;
    }
}

function print_log(
    $course,
    $user = 0,
    $date = 0,
    $order = 'l.time ASC',
    $page = 0,
    $perpage = 100,
    $url = '',
    $modname = '',
    $modid = 0,
    $modaction = '',
    $groupid = 0
) {
    // It is assumed that $date is the GMT time of midnight for that day,

    // and so the next 86400 seconds worth of logs are printed.

    global $CFG, $db;

    /// Setup for group handling.

    $isteacher = isteacher($course->id);

    $isteacheredit = isteacheredit($course->id);

    /// If the group mode is separate, and this user does not have editing privileges,

    /// then only the user's group can be viewed.

    if (SEPARATEGROUPS == $course->groupmode and !$isteacheredit) {
        $groupid = get_current_group($course->id);
    } /// If this course doesn't have groups, no groupid can be specified.

    elseif (!$course->groupmode) {
        $groupid = 0;
    }

    $joins = [];

    if ($course->category) {
        $joins[] = "l.course='$course->id'";
    } else {
        $courses[0] = '';

        if ($ccc = get_courses('all', 'c.id ASC', 'c.id,c.shortname')) {
            foreach ($ccc as $cc) {
                $courses[$cc->id] = (string)$cc->shortname;
            }
        }
    }

    if ($modname) {
        $joins[] = "l.module = '$modname'";
    }

    if ('site_errors' === $modid) {
        $joins[] = "l.action='error'";
    } elseif ($modid) {
        $joins[] = "l.cmid = '$modid'";
    }

    if ($modaction) {
        $joins[] = "l.action = '$modaction'";
    }

    /// Getting all members of a group.

    if ($groupid and !$user) {
        if ($gusers = get_records('groups_members', 'groupid', $groupid)) {
            $first = true;

            foreach ($gusers as $guser) {
                if ($first) {
                    $gselect = '(l.userid=' . $guser->userid;

                    $first = false;
                } else {
                    $gselect .= ' OR l.userid=' . $guser->userid;
                }
            }

            if (!$first) {
                $gselect .= ')';
            }

            $joins[] = $gselect;
        }
    } elseif ($user) {
        $joins[] = "l.userid = '$user'";
    }

    if ($date) {
        $enddate = $date + 86400;

        $joins[] = "l.time > '$date' AND l.time < '$enddate'";
    }

    $selector = '';

    for ($i = 0, $iMax = count($joins); $i < $iMax; $i++) {
        $selector .= $joins[$i] . (($i == count($joins) - 1) ? ' ' : ' AND ');
    }

    $totalcount = 0;  // Initialise

    if (!$logs = get_logs($selector, $order, $page * $perpage, $perpage, $totalcount)) {
        notify('No logs found!');

        print_footer($course);

        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------

        exit;
    }

    $count = 0;

    $ldcache = [];

    $tt = getdate(time());

    $today = mktime(0, 0, 0, $tt['mon'], $tt['mday'], $tt['year']);

    $strftimedatetime = get_string('strftimedatetime');

    $isteacher = isteacher($course->id);

    echo '<p align=center>';

    print_string('displayingrecords', '', $totalcount);

    echo '</p>';

    print_paging_bar($totalcount, $page, $perpage, "$url&perpage=$perpage&");

    echo '<table border=0 align=center cellpadding=3 cellspacing=3>';

    foreach ($logs as $log) {
        if (isset($ldcache[$log->module][$log->action])) {
            $ld = $ldcache[$log->module][$log->action];
        } else {
            $ld = get_record('log_display', 'module', $log->module, 'action', $log->action);

            $ldcache[$log->module][$log->action] = $ld;
        }

        if ($ld) {
            $log->info = get_field($ld->mtable, $ld->field, 'id', $log->info);
        }

        $log->url = strip_tags(urldecode($log->url));   // Some XSS protection
        $log->info = strip_tags(urldecode($log->info));  // Some XSS protection

        echo '<tr nowrap="nowrap">';

        if (!$course->category) {
            echo '<td nowrap="nowrap"><font size=2><a href="view.php?id=' . $log->course . '">' . $courses[$log->course] . '</a></td>';
        }

        echo '<td nowrap align=right><font size=2>' . userdate($log->time, '%a') . '</td>';

        echo '<td nowrap><font size=2>' . userdate($log->time, $strftimedatetime) . '</td>';

        echo '<td nowrap><font size=2>';

        link_to_popup_window("/lib/ipatlas/plot.php?address=$log->ip&user=$log->userid", 'ipatlas', $log->ip, 400, 700);

        echo '</td>';

        $fullname = fullname($log, $isteacher);

        echo '<td nowrap><font size=2><a href="../user/view.php?id=' . "$log->userid&course=$log->course" . '"><b>' . $fullname . '</b></td>';

        echo '<td nowrap><font size=2>';

        link_to_popup_window(make_log_url($log->module, $log->url), 'fromloglive', "$log->module $log->action", 400, 600);

        echo '</td>';

        echo '<td nowrap><font size=2>' . $log->info . '</td>';

        echo '</tr>';
    }

    echo '</table>';

    print_paging_bar($totalcount, $page, $perpage, "$url&perpage=$perpage&");
}

function print_log_graph($course, $userid = 0, $type = 'course.png', $date = 0)
{
    global $CFG;

    if (empty($CFG->gdversion)) {
        echo '(' . get_string('gdneed') . ')';
    } else {
        echo "<IMG BORDER=0 SRC=\"$CFG->wwwroot/course/loggraph.php?id=$course->id&user=$userid&type=$type&date=$date\">";
    }
}

function print_recent_activity($course)
{
    // $course is an object

    // This function trawls through the logs looking for

    // anything new since the user's last login

    global $CFG, $USER, $THEME, $SESSION;

    $isteacher = isteacher($course->id);

    $timestart = time() - COURSE_MAX_RECENT_PERIOD;

    if (!empty($USER->timeaccess[$course->id])) {
        if ($USER->timeaccess[$course->id] > $timestart) {
            $timestart = $USER->timeaccess[$course->id];
        }
    }

    echo '<center><font size="1">';

    echo get_string('activitysince', '', userdate($timestart));

    echo '<p><a href="' . $CFG->wwwroot . '/course/recent.php?id=' . $course->id . '">' . get_string('recentactivityreport') . '</a></p>';

    echo '</font></center>';

    // Firstly, have there been any new enrolments?

    $heading = false;

    $content = false;

    $users = get_recent_enrolments($course->id, $timestart);

    if ($users) {
        echo '<p>';

        foreach ($users as $user) {
            if (!$heading) {
                print_headline(get_string('newusers') . ':');

                $heading = true;

                $content = true;
            }

            $fullname = fullname($user, $isteacher);

            echo '<font size=1><a href="' . $CFG->wwwroot . "/user/view.php?id=$user->id&course=$course->id\">$fullname</a></font><br>";
        }

        echo '</p>';
    }

    // Next, have there been any modifications to the course structure?

    $logs = get_records_select(
        'log',
        "time > '$timestart' AND course = '$course->id' AND
                                       module = 'course' AND action LIKE '% mod'",
        'time ASC'
    );

    if ($logs) {
        foreach ($logs as $key => $log) {
            $info = preg_split(' ', $log->info);

            if ('label' == $info[0]) {     // Labels are special activities
                continue;
            }

            $modname = get_field($info[0], 'name', 'id', $info[1]);

            //Create a temp valid module structure (course,id)

            $tempmod->course = $log->course;

            $tempmod->id = $info[1];

            //Obtain the visible property from the instance

            $modvisible = instance_is_visible($info[0], $tempmod);

            //Only if the mod is visible

            if ($modvisible) {
                switch ($log->action) {
                    case 'add mod':
                        $stradded                       = get_string('added', 'moodle', get_string('modulename', $info[0]));
                        $changelist[(string)$log->info] = ['operation' => 'add', 'text' => "$stradded:<BR><A HREF=\"$CFG->wwwroot/course/$log->url\">$modname</A>"];
                        break;
                    case 'update mod':
                        $strupdated = get_string('updated', 'moodle', get_string('modulename', $info[0]));
                        if (empty($changelist[(string)$log->info])) {
                            $changelist[(string)$log->info] = ['operation' => 'update', 'text' => "$strupdated:<BR><A HREF=\"$CFG->wwwroot/course/$log->url\">$modname</A>"];
                        }
                        break;
                    case 'delete mod':
                        if (!empty($changelist[(string)$log->info]['operation']) and 'add' == $changelist[(string)$log->info]['operation']) {
                            $changelist[(string)$log->info] = null;
                        } else {
                            $strdeleted = get_string('deletedactivity', 'moodle', get_string('modulename', $info[0]));

                            $changelist[(string)$log->info] = ['operation' => 'delete', 'text' => $strdeleted];
                        }
                        break;
                }
            }
        }
    }

    if (!empty($changelist)) {
        foreach ($changelist as $changeinfo => $change) {
            if ($change) {
                $changes[$changeinfo] = $change;
            }
        }

        if (isset($changes)) {
            if (count($changes) > 0) {
                print_headline(get_string('courseupdates') . ':');

                $content = true;

                foreach ($changes as $changeinfo => $change) {
                    echo '<p><font size=1>' . $change['text'] . '</font></p>';
                }
            }
        }
    }

    // If this site uses Library module, then print recent items

    if (!empty($CFG->librarypath)) {
        if (file_exists("$CFG->dirroot/$CFG->librarypath/librarylib.php")) {
            require_once "$CFG->dirroot/$CFG->librarypath/librarylib.php";

            if (librarysummarize(5, '', date('YmdHis', $USER->lastlogin))) {
                $content = true;
            }
        }
    }

    // Now display new things from each module

    $mods = get_records('modules', 'visible', '1', 'name', 'id, name');

    foreach ($mods as $mod) {      // Each module gets it's own logs and prints them
        require_once "$CFG->dirroot/mod/$mod->name/lib.php";

        $print_recent_activity = $mod->name . '_print_recent_activity';

        if (function_exists($print_recent_activity)) {
            $modcontent = $print_recent_activity($course, $isteacher, $timestart);

            if ($modcontent) {
                $content = true;
            }
        }
    }

    if (!$content) {
        echo '<font size=2>' . get_string('nothingnew') . '</font>';
    }
}

function get_array_of_activities($courseid)
{
    // For a given course, returns an array of course activity objects

    // Each item in the array contains he following properties:

    //  cm - course module id

    //  mod - name of the module (eg forum)

    //  section - the number of the section (eg week or topic)

    //  name - the name of the instance

    //  visible - is the instance visible or not

    //  extra - contains extra string to include in any link

    global $CFG;

    $mod = [];

    if (!$rawmods = get_course_mods($courseid)) {
        return null;
    }

    if ($sections = get_records('course_sections', 'course', $courseid, 'section ASC')) {
        foreach ($sections as $section) {
            if (!empty($section->sequence)) {
                $sequence = explode(',', $section->sequence);

                foreach ($sequence as $seq) {
                    if (empty($rawmods[$seq])) {
                        continue;
                    }

                    $mod[$seq]->cm = $rawmods[$seq]->id;

                    $mod[$seq]->mod = $rawmods[$seq]->modname;

                    $mod[$seq]->section = $section->section;

                    $mod[$seq]->name = urlencode(get_field($rawmods[$seq]->modname, 'name', 'id', $rawmods[$seq]->instance));

                    $mod[$seq]->visible = $rawmods[$seq]->visible;

                    $mod[$seq]->extra = '';

                    $modname = $mod[$seq]->mod;

                    $functionname = $modname . '_get_coursemodule_info';

                    require_once "$CFG->dirroot/mod/$modname/lib.php";

                    if (function_exists($functionname)) {
                        if ($info = $functionname($rawmods[$seq])) {
                            if (!empty($info->extra)) {
                                $mod[$seq]->extra = $info->extra;
                            }

                            if (!empty($info->icon)) {
                                $mod[$seq]->icon = $info->icon;
                            }
                        }
                    }
                }
            }
        }
    }

    return $mod;
}

function get_all_mods($courseid, &$mods, &$modnames, &$modnamesplural, &$modnamesused)
{
    // Returns a number of useful structures for course displays

    $mods = null;    // course modules indexed by id
    $modnames = null;    // all course module names (except resource!)
    $modnamesplural = null;    // all course module names (plural form)
    $modnamesused = null;    // course module names used

    if ($allmods = get_records('modules')) {
        foreach ($allmods as $mod) {
            if ($mod->visible) {
                $modnames[$mod->name] = get_string('modulename', (string)$mod->name);

                $modnamesplural[$mod->name] = get_string('modulenameplural', (string)$mod->name);
            }
        }

        asort($modnames);
    } else {
        error('No modules are installed!');
    }

    if ($rawmods = get_course_mods($courseid)) {
        foreach ($rawmods as $mod) {    // Index the mods
            $mods[$mod->id] = $mod;

            $mods[$mod->id]->modfullname = $modnames[$mod->modname];

            if ($mod->visible or isteacher($courseid)) {
                $modnamesused[$mod->modname] = $modnames[$mod->modname];
            }
        }

        if ($modnamesused) {
            asort($modnamesused);
        }
    }

    unset($modnames['resource']);

    unset($modnames['label']);
}

function get_all_sections($courseid)
{
    return get_records(
        'course_sections',
        'course',
        (string)$courseid,
        'section',
        'section, id, course, summary, sequence, visible'
    );
}

function course_set_display($courseid, $display = 0)
{
    global $USER;

    if (empty($USER->id)) {
        return false;
    }

    if ('all' == $display or empty($display)) {
        $display = 0;
    }

    if (record_exists('course_display', 'userid', $USER->id, 'course', $courseid)) {
        set_field('course_display', 'display', $display, 'userid', $USER->id, 'course', $courseid);
    } else {
        $record->userid = $USER->id;

        $record->course = $courseid;

        $record->display = $display;

        if (!insert_record('course_display', $record)) {
            notify('Could not save your course display!');
        }
    }

    return $USER->display[$courseid] = $display;  // Note: = not ==
}

function set_section_visible($courseid, $sectionnumber, $visibility)
{
    /// For a given course section, markes it visible or hidden,

    /// and does the same for every activity in that section

    if ($section = get_record('course_sections', 'course', $courseid, 'section', $sectionnumber)) {
        set_field('course_sections', 'visible', (string)$visibility, 'id', $section->id);

        if (!empty($section->sequence)) {
            $modules = explode(',', $section->sequence);

            foreach ($modules as $moduleid) {
                if ($visibility) {
                    show_course_module($moduleid);
                } else {
                    hide_course_module($moduleid);
                }
            }
        }

        rebuild_course_cache($courseid);
    }
}

function print_section($course, $section, $mods, $modnamesused, $absolute = false, $width = '100%')
{
    /// Prints a section full of activity modules

    global $CFG, $USER;

    static $groupbuttons;

    static $groupbuttonslink;

    static $isteacher;

    static $isediting;

    static $ismoving;

    static $strmovehere;

    static $strmovefull;

    $labelformatoptions = new stdClass();

    if (!isset($isteacher)) {
        $groupbuttons = ($course->groupmode or (!$course->groupmodeforce));

        $groupbuttonslink = (!$course->groupmodeforce);

        $isteacher = isteacher($course->id);

        $isediting = isediting($course->id);

        $ismoving = ismoving($course->id);

        if ($ismoving) {
            $strmovehere = get_string('movehere');

            $strmovefull = strip_tags(get_string('movefull', '', "'$USER->activitycopyname'"));
        }
    }

    $labelformatoptions->noclean = true;

    $modinfo = unserialize($course->modinfo);

    echo '<table width="' . $width . '" cellpadding="1" cellspacing="0">';

    if (!empty($section->sequence)) {
        $sectionmods = explode(',', $section->sequence);

        foreach ($sectionmods as $modnumber) {
            if (empty($mods[$modnumber])) {
                continue;
            }

            $mod = $mods[$modnumber];

            if ($mod->visible or $isteacher) {
                echo "<tr><td class=\"activity$mod->modname\">";

                if ($ismoving) {
                    if ($mod->id == $USER->activitycopy) {
                        continue;
                    }

                    echo "<a title=\"$strmovefull\"" . " href=\"$CFG->wwwroot/course/mod.php?moveto=$mod->id&amp;sesskey=$USER->sesskey\">" . "<img height=\"16\" width=\"80\" src=\"$CFG->pixpath/movehere.gif\" " . " alt=\"$strmovehere\" border=\"0\"></a><br>\n";
                }

                $instancename = urldecode($modinfo[$modnumber]->name);

                if (!empty($CFG->filterall)) {
                    $instancename = filter_text("<nolink>$instancename</nolink>", $course->id);
                }

                if (!empty($modinfo[$modnumber]->extra)) {
                    $extra = urldecode($modinfo[$modnumber]->extra);
                } else {
                    $extra = '';
                }

                if (!empty($modinfo[$modnumber]->icon)) {
                    $icon = "$CFG->pixpath/" . urldecode($modinfo[$modnumber]->icon);
                } else {
                    $icon = "$CFG->modpixpath/$mod->modname/icon.gif";
                }

                if ($mod->indent) {
                    print_spacer(12, 20 * $mod->indent, false);
                }

                if ('label' == $mod->modname) {
                    if (!$mod->visible) {
                        echo '<span class="dimmed_text">';
                    }

                    echo format_text($extra, FORMAT_HTML, $labelformatoptions);

                    if (!$mod->visible) {
                        echo '</span>';
                    }
                } else { // Normal activity
                    $linkcss = $mod->visible ? '' : ' class="dimmed" ';

                    echo "<img src=\"$icon\"" . " height=16 width=16 alt=\"$mod->modfullname\">" . " <font size=2><a title=\"$mod->modfullname\" $linkcss $extra" . " href=\"$CFG->wwwroot/mod/$mod->modname/view.php?id=$mod->id\">$instancename</a></font>";
                }

                if ($isediting) {
                    if ($groupbuttons) {
                        if (!$mod->groupmodelink = $groupbuttonslink) {
                            $mod->groupmode = $course->groupmode;
                        }
                    } else {
                        $mod->groupmode = false;
                    }

                    echo '&nbsp;&nbsp;';

                    echo make_editing_buttons($mod, $absolute, true, $mod->indent);
                }

                echo '</td>';

                echo '</tr>';
            }
        }
    }

    if ($ismoving) {
        echo "<tr><td><a title=\"$strmovefull\"" . " href=\"$CFG->wwwroot/course/mod.php?movetosection=$section->id&amp;sesskey=$USER->sesskey\">" . "<img height=\"16\" width=\"80\" src=\"$CFG->pixpath/movehere.gif\" " . " alt=\"$strmovehere\" border=\"0\"></a></td></tr>\n";
    }

    echo "</table>\n\n";
}

function print_section_add_menus($course, $section, $modnames, $vertical = false, $return = false)
{
    // Prints the menus to add activities and resources

    global $CFG, $USER;

    static $straddactivity, $stractivities, $straddresource, $resources;

    if (!isset($straddactivity)) {
        $straddactivity = get_string('addactivity');

        $straddresource = get_string('addresource');

        /// Standard resource types

        require_once "$CFG->dirroot/mod/resource/lib.php";

        $resourceraw = resource_get_resource_types();

        foreach ($resourceraw as $type => $name) {
            $resources["resource&type=$type"] = $name;
        }

        $resources['label'] = get_string('resourcetypelabel', 'resource');
    }

    $output = '';

    $output .= '<div align="right"><table align="right"><tr><td>';

    $output .= popup_form(
        "$CFG->wwwroot/course/mod.php?id=$course->id&amp;section=$section&amp;sesskey=$USER->sesskey&add=",
        $resources,
        "ressection$section",
        '',
        $straddresource,
        'resource/types',
        $straddresource,
        true
    );

    $output .= '</td>';

    if ($vertical) {
        $output .= '</tr><tr>';
    }

    $output .= '<td>';

    $output .= popup_form(
        "$CFG->wwwroot/course/mod.php?id=$course->id&amp;section=$section&amp;sesskey=$USER->sesskey&add=",
        $modnames,
        "section$section",
        '',
        $straddactivity,
        'mods',
        $straddactivity,
        true
    );

    $output .= '</td></tr></table>';

    $output .= '</div>';

    if ($return) {
        return $output;
    }

    echo $output;
}

function rebuild_course_cache($courseid = 0)
{
    // Rebuilds the cached list of course activities stored in the database

    // If a courseid is not specified, then all are rebuilt

    if ($courseid) {
        $select = "id = '$courseid'";
    } else {
        $select = '';
    }

    if ($courses = get_records_select('course', $select)) {
        foreach ($courses as $course) {
            $modinfo = serialize(get_array_of_activities($course->id));

            if (!set_field('course', 'modinfo', $modinfo, 'id', $course->id)) {
                notify("Could not cache module information for course '$course->fullname'!");
            }
        }
    }
}

function print_heading_block($heading, $width = '100%', $class = 'headingblock')
{
    global $THEME;

    echo '<table width="100%" border="0" cellspacing="0" cellpadding="5">';

    echo "<tr><td bgcolor=\"$THEME->cellheading\" class=\"$class\">";

    echo stripslashes($heading);

    echo '</td></tr></table>';
}

function make_categories_list(&$list, &$parents, $category = null, $path = '')
{
    /// Given an empty array, this function recursively travels the

    /// categories, building up a nice list for display.  It also makes

    /// an array that list all the parents for each category.

    if ($category) {
        if ($path) {
            $path = "$path / $category->name";
        } else {
            $path = (string)$category->name;
        }

        $list[$category->id] = $path;
    } else {
        $category->id = 0;
    }

    if ($categories = get_categories((string)$category->id)) {   // Print all the children recursively
        foreach ($categories as $cat) {
            if (!empty($category->id)) {
                if (isset($parents[$category->id])) {
                    $parents[$cat->id] = $parents[$category->id];
                }

                $parents[$cat->id][] = $category->id;
            }

            make_categories_list($list, $parents, $cat, $path);
        }
    }
}

function print_whole_category_list($category = null, $displaylist = null, $parentslist = null, $depth = -1)
{
    /// Recursive function to print out all the categories in a nice format

    /// with or without courses included

    global $CFG;

    if (isset($CFG->max_category_depth) && ($depth >= $CFG->max_category_depth)) {
        return;
    }

    if (!$displaylist) {
        make_categories_list($displaylist, $parentslist);
    }

    if ($category) {
        if ($category->visible or iscreator()) {
            print_category_info($category, $depth);
        } else {
            return;  // Don't bother printing children of invisible categories
        }
    } else {
        $category->id = '0';
    }

    if ($categories = get_categories($category->id)) {   // Print all the children recursively
        $countcats = count($categories);

        $count = 0;

        $first = true;

        $last = false;

        foreach ($categories as $cat) {
            $count++;

            if ($count == $countcats) {
                $last = true;
            }

            $up = $first ? false : true;

            $down = $last ? false : true;

            $first = false;

            print_whole_category_list($cat, $displaylist, $parentslist, $depth + 1);
        }
    }
}

function print_category_info($category, $depth)
{
    /// Prints the category info in indented fashion

    /// This function is only used by print_whole_category_list() above

    global $CFG;

    static $strallowguests, $strrequireskey, $strsummary;

    if (empty($strsummary)) {
        $strallowguests = get_string('allowguests');

        $strrequireskey = get_string('requireskey');

        $strsummary = get_string('summary');
    }

    $catlinkcss = $category->visible ? '' : ' class="dimmed" ';

    if (FRONTPAGECOURSELIST == $CFG->frontpage) {
        $catimage = "<img src=\"$CFG->pixpath/i/course.gif\" width=16 height=16 border=0>";
    } else {
        $catimage = '&nbsp';
    }

    echo "\n\n<table border=0 cellpadding=3 cellspacing=0 width=\"100%\"><tr>";

    if (FRONTPAGECOURSELIST == $CFG->frontpage) {
        $courses = get_courses($category->id);

        echo '<tr>';

        if ($depth) {
            $indent = $depth * 30;

            $rows = count($courses) + 1;

            echo "<td rowspan=\"$rows\" valign=\"top\" width=\"$indent\">";

            print_spacer(10, $indent);

            echo '</td>';
        }

        echo "<td valign=\"top\">$catimage</td>";

        echo '<td valign="top" width="100%" class="categoryname">';

        echo "<a $catlinkcss href=\"$CFG->wwwroot/course/category.php?id=$category->id\">$category->name</a>";

        echo '</td>';

        echo '<td class="categoryname">&nbsp;</td>';

        echo "</tr>\n";

        if ($courses && !(isset($CFG->max_category_depth) && ($depth >= $CFG->max_category_depth - 1))) {
            foreach ($courses as $course) {
                $linkcss = $course->visible ? '' : ' class="dimmed" ';

                echo '<tr><td valign="top" width="30">&nbsp;';

                echo "</td>\n<td valign=\"top\" width=\"100%\" class=\"coursename\">";

                echo "<a $linkcss href=\"$CFG->wwwroot/course/view.php?id=$course->id\">$course->fullname</a>";

                echo "</td>\n<td align=\"right\" valign=\"top\" nowrap class=\"coursename\">";

                if ($course->guest) {
                    echo "<a title=\"$strallowguests\" href=\"$CFG->wwwroot/course/view.php?id=$course->id\">";

                    echo "<img hspace=1 alt=\"$strallowguests\" height=16 width=16 border=0 src=\"$CFG->pixpath/i/guest.gif\"></a>";
                } else {
                    echo "<img alt=\"\" height=16 width=18 border=0 src=\"$CFG->pixpath/spacer.gif\">";
                }

                if ($course->password) {
                    echo "<a title=\"$strrequireskey\" href=\"$CFG->wwwroot/course/view.php?id=$course->id\">";

                    echo "<img hspace=1 alt=\"$strrequireskey\" height=16 width=16 border=0 src=\"$CFG->pixpath/i/key.gif\"></a>";
                } else {
                    echo "<img alt=\"\" height=16 width=18 border=0 src=\"$CFG->pixpath/spacer.gif\">";
                }

                if ($course->summary) {
                    link_to_popup_window(
                        "/course/info.php?id=$course->id",
                        'courseinfo',
                        "<img hspace=1 alt=\"$strsummary\" height=16 width=16 border=0 src=\"$CFG->pixpath/i/info.gif\">",
                        400,
                        500,
                        $strsummary
                    );
                } else {
                    echo "<img alt=\"\" height=16 width=18 border=0 src=\"$CFG->pixpath/spacer.gif\">";
                }

                echo "</td></tr>\n";
            }
        }
    } else {
        if ($depth) {
            $indent = $depth * 20;

            echo "<td valign=\"top\" width=\"$indent\">";

            print_spacer(10, $indent);

            echo '</td>';
        }

        echo '<td valign="top" width="100%" class="categoryname">';

        echo "<a $catlinkcss href=\"$CFG->wwwroot/course/category.php?id=$category->id\">$category->name</a>";

        echo '</td>';

        echo "<td valign=\"top\" class=\"categorynumber\">$category->coursecount</td></tr>";
    }

    echo "\n</table>\n";
}

function print_courses($category, $width = '100%')
{
    /// Category is 0 (for all courses) or an object

    global $CFG, $THEME;

    if (empty($category)) {
        $categories = get_categories(0);  // Parent = 0   ie top-level categories only

        if (1 == count($categories)) {
            $category = array_shift($categories);

            $courses = get_courses($category->id);
        } else {
            $courses = get_courses('all');
        }

        unset($categories);
    } else {
        $categories = get_categories($category->id);  // sub categories

        $courses = get_courses($category->id);
    }

    if ($courses) {
        foreach ($courses as $course) {
            print_course($course, $width);

            echo "<br>\n";
        }
    } else {
        print_heading(get_string('nocoursesyet'));
    }
}

function print_course($course, $width = '100%')
{
    global $CFG, $THEME;

    static $enrol;

    if (empty($enrol)) {
        require_once "$CFG->dirroot/enrol/$CFG->enrol/enrol.php";

        $enrol = new enrolment_plugin();
    }

    if (!$site = get_site()) {
        error('Could not find a site!');
    }

    print_simple_box_start('center', (string)$width, $THEME->cellcontent, 5, 'coursebox');

    $linkcss = $course->visible ? '' : ' class="dimmed" ';

    echo '<table width="100%">';

    echo '<tr valign=top>';

    echo '<td valign=top width="50%" class="courseboxinfo">';

    echo '<p><font size=3><b><a title="' . get_string('entercourse') . "\"
              $linkcss href=\"$CFG->wwwroot/course/view.php?id=$course->id\">$course->fullname</a></b></font></p>";

    if ($teachers = get_course_teachers($course->id)) {
        echo "<p><font size=\"1\">\n";

        foreach ($teachers as $teacher) {
            if ($teacher->authority > 0) {
                if (!$teacher->role) {
                    $teacher->role = $course->teacher;
                }

                $fullname = fullname($teacher, isteacher($course->id)); // is the USER a teacher of that course

                echo "$teacher->role: <a href=\"$CFG->wwwroot/user/view.php?id=$teacher->id&course=$site->id\">$fullname</a><br>";
            }
        }

        echo '</font></p>';
    }

    echo $enrol->get_access_icons($course);

    echo '</td><td valign=top width="50%" class="courseboxsummary">';

    echo '<p><font size=2>' . filter_text(text_to_html($course->summary), $course->id) . '</font></p>';

    echo '</td></tr>';

    echo '</table>';

    print_simple_box_end();
}

function print_my_moodle()
{
    /// Prints custom user information on the home page.

    /// Over time this can include all sorts of information

    global $USER, $CFG;

    if (!isset($USER->id)) {
        error("It shouldn't be possible to see My Moodle without being logged in.");
    }

    if ($courses = get_my_courses($USER->id)) {
        foreach ($courses as $course) {
            if (!$course->category) {
                continue;
            }

            print_course($course, '100%');

            echo "<br>\n";
        }

        if (count_records('course') > (count($courses) + 1)) {  // Some courses not being displayed
            echo '<table width="100%"><tr><td align="center">';

            print_course_search('', false, 'short');

            echo '</td><td align="center">';

            print_single_button("$CFG->wwwroot/course/index.php", null, get_string('fulllistofcourses'), 'get');

            echo "</td></tr></table>\n";
        }
    } else {
        if (count_records('course_categories') > 1) {
            print_simple_box_start('center', '100%', '#FFFFFF', 5, 'categorybox');

            print_whole_category_list();

            print_simple_box_end();
        } else {
            print_courses(0, '100%');
        }
    }
}

function print_course_search($value = '', $return = false, $format = 'plain')
{
    global $CFG;

    $strsearchcourses = get_string('searchcourses');

    if ('plain' == $format) {
        $output = '<center><p align="center" class="coursesearchbox">';

        $output .= "<form name=\"coursesearch\" action=\"$CFG->wwwroot/course/search.php\" method=\"get\">";

        $output .= "<input type=\"text\" size=30 name=\"search\" value=\"$value\">";

        $output .= "<input type=\"submit\" value=\"$strsearchcourses\">";

        $output .= '</form></p></center>';
    } elseif ('short' == $format) {
        $output = '<center><p align="center" class="coursesearchbox">';

        $output .= "<form name=\"coursesearch\" action=\"$CFG->wwwroot/course/search.php\" method=\"get\">";

        $output .= "<input type=\"text\" size=12 name=\"search\" value=\"$value\">";

        $output .= "<input type=\"submit\" value=\"$strsearchcourses\">";

        $output .= '</form></p></center>';
    } elseif ('navbar' == $format) {
        $output = '<table border=0 cellpadding=0 cellspacing=0><tr><td nowrap>';

        $output .= "<form name=\"coursesearch\" action=\"$CFG->wwwroot/course/search.php\" method=\"get\">";

        $output .= "<input type=\"text\" size=20 name=\"search\" value=\"$value\">";

        $output .= "<input type=\"submit\" value=\"$strsearchcourses\">";

        $output .= '</form>';

        $output .= '</td></tr></table>';
    }

    if ($return) {
        return $output;
    }

    echo $output;
}

/// MODULE FUNCTIONS /////////////////////////////////////////////////////////////////

function add_course_module($mod)
{
    $mod->added = time();

    unset($mod->id);

    return insert_record('course_modules', $mod);
}

function add_mod_to_section($mod, $beforemod = null)
{
    /// Given a full mod object with section and course already defined

    /// If $before is specified, then this is an existing ID which we

    /// will insert the new module before

    ///

    /// Returns the course_sections ID where the mod is inserted

    if ($section = get_record('course_sections', 'course', (string)$mod->course, 'section', (string)$mod->section)) {
        $section->sequence = trim($section->sequence);

        if (empty($section->sequence)) {
            $newsequence = (string)$mod->coursemodule;
        } elseif ($beforemod) {
            $modarray = explode(',', $section->sequence);

            if ($key = array_keys($modarray, $beforemod->id, true)) {
                $insertarray = [$mod->id, $beforemod->id];

                array_splice($modarray, $key[0], 1, $insertarray);

                $newsequence = implode(',', $modarray);
            } else {  // Just tack it on the end anyway
                $newsequence = "$section->sequence,$mod->coursemodule";
            }
        } else {
            $newsequence = "$section->sequence,$mod->coursemodule";
        }

        if (set_field('course_sections', 'sequence', $newsequence, 'id', $section->id)) {
            return $section->id;     // Return course_sections ID that was used.
        }

        return 0;
    }    // Insert a new record

    $section->course = $mod->course;

    $section->section = $mod->section;

    $section->summary = '';

    $section->sequence = $mod->coursemodule;

    return insert_record('course_sections', $section);
}

function set_groupmode_for_module($id, $groupmode)
{
    return set_field('course_modules', 'groupmode', $groupmode, 'id', $id);
}

function hide_course_module($mod)
{
    $cm = get_record('course_modules', 'id', $mod);

    $modulename = get_field('modules', 'name', 'id', $cm->module);

    if ($events = get_records_select('event', "instance = '$cm->instance' AND modulename = '$modulename'")) {
        foreach ($events as $event) {
            hide_event($event);
        }
    }

    return set_field('course_modules', 'visible', 0, 'id', $mod);
}

function show_course_module($mod)
{
    $cm = get_record('course_modules', 'id', $mod);

    $modulename = get_field('modules', 'name', 'id', $cm->module);

    if ($events = get_records_select('event', "instance = '$cm->instance' AND modulename = '$modulename'")) {
        foreach ($events as $event) {
            show_event($event);
        }
    }

    return set_field('course_modules', 'visible', 1, 'id', $mod);
}

function delete_course_module($mod)
{
    return set_field('course_modules', 'deleted', 1, 'id', $mod);
}

function delete_mod_from_section($mod, $section)
{
    if ($section = get_record('course_sections', 'id', (string)$section)) {
        $modarray = explode(',', $section->sequence);

        if ($key = array_keys($modarray, $mod, true)) {
            array_splice($modarray, $key[0], 1);

            $newsequence = implode(',', $modarray);

            return set_field('course_sections', 'sequence', $newsequence, 'id', $section->id);
        }

        return false;
    }

    return false;
}

function move_section($course, $section, $move)
{
    /// Moves a whole course section up and down within the course

    if (!$move) {
        return true;
    }

    $sectiondest = $section + $move;

    if ($sectiondest > $course->numsections or $sectiondest < 1) {
        return false;
    }

    if (!$sectionrecord = get_record('course_sections', 'course', $course->id, 'section', $section)) {
        return false;
    }

    if (!$sectiondestrecord = get_record('course_sections', 'course', $course->id, 'section', $sectiondest)) {
        return false;
    }

    if (!set_field('course_sections', 'section', $sectiondest, 'id', $sectionrecord->id)) {
        return false;
    }

    if (!set_field('course_sections', 'section', $section, 'id', $sectiondestrecord->id)) {
        return false;
    }

    return true;
}

function moveto_module($mod, $section, $beforemod = null)
{
    /// All parameters are objects

    /// Move the module object $mod to the specified $section

    /// If $beforemod exists then that is the module

    /// before which $modid should be inserted

    /// Remove original module from original section

    if (!delete_mod_from_section($mod->id, $mod->section)) {
        notify('Could not delete module from existing section');
    }

    /// Update module itself if necessary

    if ($mod->section != $section->id) {
        $mod->section = $section->id;

        if (!update_record('course_modules', $mod)) {
            return false;
        }
    }

    /// Add the module into the new section

    $mod->course = $section->course;

    $mod->section = $section->section;  // need relative reference

    $mod->coursemodule = $mod->id;

    if (!add_mod_to_section($mod, $beforemod)) {
        return false;
    }

    return true;
}

function move_module($cm, $move)
{
    /// Moves an activity module up and down within the course

    if (!$move) {
        return true;
    }

    if (!$thissection = get_record('course_sections', 'id', $cm->section)) {
        error("This course section doesn't exist");
    }

    $mods = explode(',', $thissection->sequence);

    $len = count($mods);

    $pos = array_keys($mods, $cm->id, true);

    $thepos = $pos[0];

    if (0 == $len || 0 == count($pos)) {
        error('Very strange. Could not find the required module in this section.');
    }

    if (1 == $len) {
        $first = true;

        $last = true;
    } else {
        $first = (0 == $thepos);

        $last = ($thepos == $len - 1);
    }

    if ($move < 0) {    // Moving the module up
        if ($first) {
            if (0 == $thissection->section) {  // First section, do nothing
                return true;
            }                 // Push onto end of previous section

            $prevsectionnumber = $thissection->section - 1;

            if (!$prevsection = get_record(
                'course_sections',
                'course',
                (string)$thissection->course,
                'section',
                (string)$prevsectionnumber
            )) {
                error("Previous section ($prevsection->id) doesn't exist");
            }

            if (!empty($prevsection->sequence)) {
                $newsequence = "$prevsection->sequence,$cm->id";
            } else {
                $newsequence = (string)$cm->id;
            }

            if (!set_field('course_sections', 'sequence', $newsequence, 'id', $prevsection->id)) {
                error('Previous section could not be updated');
            }

            if (!set_field('course_modules', 'section', $prevsection->id, 'id', $cm->id)) {
                error('Module could not be updated');
            }

            array_splice($mods, 0, 1);

            $newsequence = implode(',', $mods);

            if (!set_field('course_sections', 'sequence', $newsequence, 'id', $thissection->id)) {
                error('Module could not be updated');
            }

            return true;
        }          // move up within this section

        $swap = $mods[$thepos - 1];

        $mods[$thepos - 1] = $mods[$thepos];

        $mods[$thepos] = $swap;

        $newsequence = implode(',', $mods);

        if (!set_field('course_sections', 'sequence', $newsequence, 'id', $thissection->id)) {
            error('This section could not be updated');
        }

        return true;
    }              // Moving the module down

    if ($last) {
        $nextsectionnumber = $thissection->section + 1;

        if ($nextsection = get_record(
            'course_sections',
            'course',
            (string)$thissection->course,
            'section',
            (string)$nextsectionnumber
        )) {
            if (!empty($nextsection->sequence)) {
                $newsequence = "$cm->id,$nextsection->sequence";
            } else {
                $newsequence = (string)$cm->id;
            }

            if (!set_field('course_sections', 'sequence', $newsequence, 'id', $nextsection->id)) {
                error('Next section could not be updated');
            }

            if (!set_field('course_modules', 'section', $nextsection->id, 'id', $cm->id)) {
                error('Module could not be updated');
            }

            array_splice($mods, $thepos, 1);

            $newsequence = implode(',', $mods);

            if (!set_field('course_sections', 'sequence', $newsequence, 'id', $thissection->id)) {
                error('This section could not be updated');
            }

            return true;
        }          // There is no next section, so just return

        return true;
    }        // move down within this section

    $swap = $mods[$thepos + 1];

    $mods[$thepos + 1] = $mods[$thepos];

    $mods[$thepos] = $swap;

    $newsequence = implode(',', $mods);

    if (!set_field('course_sections', 'sequence', $newsequence, 'id', $thissection->id)) {
        error('This section could not be updated');
    }

    return true;
}

function make_editing_buttons($mod, $absolute = false, $moveselect = true, $indent = -1)
{
    global $CFG, $THEME, $USER;

    static $str;

    if (!isset($str)) {
        $str->delete = get_string('delete');

        $str->move = get_string('move');

        $str->moveup = get_string('moveup');

        $str->movedown = get_string('movedown');

        $str->moveright = get_string('moveright');

        $str->moveleft = get_string('moveleft');

        $str->update = get_string('update');

        $str->duplicate = get_string('duplicate');

        $str->hide = get_string('hide');

        $str->show = get_string('show');

        $str->clicktochange = get_string('clicktochange');

        $str->forcedmode = get_string('forcedmode');

        $str->groupsnone = get_string('groupsnone');

        $str->groupsseparate = get_string('groupsseparate');

        $str->groupsvisible = get_string('groupsvisible');
    }

    if ($absolute) {
        $path = "$CFG->wwwroot/course";
    } else {
        $path = '.';
    }

    if (empty($THEME->custompix)) {
        $pixpath = "$path/../pix";
    } else {
        $pixpath = "$path/../theme/$CFG->theme/pix";
    }

    if ($mod->visible) {
        $hideshow = "<a title=\"$str->hide\" href=\"$path/mod.php?hide=$mod->id&amp;sesskey=$USER->sesskey\"><img" . " src=\"$pixpath/t/hide.gif\" hspace=\"2\" height=\"11\" width=\"11\" border=\"0\" alt=\"$str->hide\"></a> ";
    } else {
        $hideshow = "<a title=\"$str->show\" href=\"$path/mod.php?show=$mod->id&amp;sesskey=$USER->sesskey\"><img" . " src=\"$pixpath/t/show.gif\" hspace=\"2\" height=\"11\" width=\"11\" " . "border=\"0\" alt=\"$str->show\"></a> ";
    }

    if (false !== $mod->groupmode) {
        if (SEPARATEGROUPS == $mod->groupmode) {
            $grouptitle = $str->groupsseparate;

            $groupimage = "$pixpath/t/groups.gif";

            $grouplink = "$path/mod.php?id=$mod->id&groupmode=0&amp;sesskey=$USER->sesskey";
        } elseif (VISIBLEGROUPS == $mod->groupmode) {
            $grouptitle = $str->groupsvisible;

            $groupimage = "$pixpath/t/groupv.gif";

            $grouplink = "$path/mod.php?id=$mod->id&groupmode=1&amp;sesskey=$USER->sesskey";
        } else {
            $grouptitle = $str->groupsnone;

            $groupimage = "$pixpath/t/groupn.gif";

            $grouplink = "$path/mod.php?id=$mod->id&groupmode=2&amp;sesskey=$USER->sesskey";
        }

        if ($mod->groupmodelink) {
            $groupmode = "<a title=\"$grouptitle ($str->clicktochange)\" href=\"$grouplink\">" . "<img src=\"$groupimage\" hspace=\"2\" height=\"11\" width=\"11\" " . "border=\"0\" alt=\"$grouptitle\"></a>";
        } else {
            $groupmode = "<img title=\"$grouptitle ($str->forcedmode)\" " . " src=\"$groupimage\" hspace=\"2\" height=\"11\" width=\"11\" " . "border=\"0\" alt=\"$grouptitle\">";
        }
    } else {
        $groupmode = '';
    }

    if ($moveselect) {
        $move = "<a title=\"$str->move\" href=\"$path/mod.php?copy=$mod->id&amp;sesskey=$USER->sesskey\"><img" . " src=\"$pixpath/t/move.gif\" hspace=\"2\" height=\"11\" width=\"11\" " . " border=\"0\" alt=\"$str->move\"></a>";
    } else {
        $move = "<a title=\"$str->moveup\" href=\"$path/mod.php?id=$mod->id&move=-1&amp;sesskey=$USER->sesskey\"><img"
                . " src=\"$pixpath/t/up.gif\" hspace=\"2\" height=\"11\" width=\"11\" "
                . " border=\"0\" alt=\"$str->moveup\"></a>"
                . "<a title=\"$str->movedown\" href=\"$path/mod.php?id=$mod->id&move=1&amp;sesskey=$USER->sesskey\"><img"
                . " src=\"$pixpath/t/down.gif\" hspace=\"2\" height=\"11\" width=\"11\" "
                . " border=\"0\" alt=\"$str->movedown\"></a>";
    }

    $leftright = '';

    if ($indent > 0) {
        $leftright .= "<a title=\"$str->moveleft\" href=\"$path/mod.php?id=$mod->id&indent=-1&amp;sesskey=$USER->sesskey\"><img" . " src=\"$pixpath/t/left.gif\" hspace=\"2\" height=\"11\" width=\"11\" " . " border=\"0\" alt=\"$str->moveleft\"></a>";
    }

    if ($indent >= 0) {
        $leftright .= "<a title=\"$str->moveright\" href=\"$path/mod.php?id=$mod->id&indent=1&amp;sesskey=$USER->sesskey\"><img" . " src=\"$pixpath/t/right.gif\" hspace=\"2\" height=\"11\" width=\"11\" " . " border=\"0\" alt=\"$str->moveright\"></a>";
    }

    return "$leftright$move"
           . "<a title=\"$str->update\" href=\"$path/mod.php?update=$mod->id&amp;sesskey=$USER->sesskey\"><img"
           . " src=\"$pixpath/t/edit.gif\" hspace=\"2\" height=\"11\" width=\"11\" border=\"0\" "
           . " alt=\"$str->update\"></a>"
           . //   Following line is commented out until this feature is more definite -- martin
           //     "<a title=\"$str->duplicate\" href=\"$path/mod.php?duplicate=$mod->id&amp;sesskey=$USER->sesskey\"> 2 </a>".
           "<a title=\"$str->delete\" href=\"$path/mod.php?delete=$mod->id&amp;sesskey=$USER->sesskey\"><img"
           . " src=\"$pixpath/t/delete.gif\" hspace=\"2\" height=\"11\" width=\"11\" border=\"0\" "
           . " alt=\"$str->delete\"></a>$hideshow$groupmode";
}
