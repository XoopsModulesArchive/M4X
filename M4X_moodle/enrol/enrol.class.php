<?php

declare(strict_types=1);

/// $Id: enrol.class.php,v 1.8.2.2 2004/10/07 15:21:49 moodler Exp $
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 2004  Martin Dougiamas  http://moodle.com               //
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

/**
 * enrolment_base is the base class for enrolment plugins
 *
 * This class provides all the functionality for an enrolment plugin
 * In fact it includes all the code for the default, "internal" method
 * so that other plugins can override these as necessary.
 */
class enrolment_base
{
    public $errormsg;

    /**
     * Returns information about the courses a student has access to
     *
     * Set the $user->student course array
     * Set the $user->timeaccess course array
     *
     * @param user  referenced object, must contain $user->id already set
     */
    public function get_student_courses($user)
    {
        if ($students = get_records('user_students', 'userid', $user->id)) {
            $currenttime = time();

            foreach ($students as $student) {
                /// Is course visible?

                if (get_field('course', 'visible', 'id', $student->course)) {
                    /// Is the student enrolment active right now?

                    if ((0 == $student->timestart or ($currenttime > $student->timestart)) and (0 == $student->timeend or ($currenttime < $student->timeend))) {
                        $user->student[$student->course] = true;

                        $user->timeaccess[$student->course] = $student->timeaccess;
                    }
                }
            }
        }
    }

    /**
     * Returns information about the courses a student has access to
     *
     * Set the $user->teacher course array
     * Set the $user->teacheredit course array
     * Set the $user->timeaccess course array
     *
     * @param user  referenced object, must contain $user->id already set
     */
    public function get_teacher_courses($user)
    {
        if ($teachers = get_records('user_teachers', 'userid', $user->id)) {
            $currenttime = time();

            foreach ($teachers as $teacher) {
                /// Is teacher only teaching this course for a specific time period?

                if ((0 == $teacher->timestart or ($currenttime > $teacher->timestart)) and (0 == $teacher->timeend or ($currenttime < $teacher->timeend))) {
                    $user->teacher[$teacher->course] = true;

                    if ($teacher->editall) {
                        $user->teacheredit[$teacher->course] = true;
                    }

                    $user->timeaccess[$teacher->course] = $teacher->timeaccess;
                }
            }
        }
    }

    /**
     * Prints the entry form/page for this enrolment
     *
     * This is only called from course/enrol.php
     * Most plugins will probably override this to print payment
     * forms etc, or even just a notice to say that manual enrolment
     * is disabled
     *
     * @param mixed $course
     */
    public function print_entry($course)
    {
        global $CFG, $USER, $SESSION, $THEME;

        $strloginto = get_string('loginto', '', $course->shortname);

        $strcourses = get_string('courses');

        /// Automatically enrol into courses without password

        if ('' == $course->password) {   // no password, so enrol
            if (isguest()) {
                add_to_log($course->id, 'course', 'guest', "view.php?id=$course->id", (string)$USER->id);
            } elseif (empty($_GET['confirm'])) {
                print_header($strloginto, $course->fullname, "<a href=\".\">$strcourses</a> -> $strloginto");

                echo '<br>';

                notice_yesno(get_string('enrolmentconfirmation'), "enrol.php?id=$course->id&confirm=1", $CFG->wwwroot);

                print_footer();

                //--------------------------------------------

                // MOODLE4XOOPS - J. BAUDIN

                //--------------------------------------------

                require_once "$CFG->dirroot/footer.php";

                //--------------------------------------------

                exit;
            } else {
                if ($course->enrolperiod) {
                    $timestart = time();

                    $timeend = time() + $course->enrolperiod;
                } else {
                    $timestart = $timeend = 0;
                }

                if (!enrol_student($USER->id, $course->id, $timestart, $timeend)) {
                    error('An error occurred while trying to enrol you.');
                }

                $subject = get_string('welcometocourse', '', $course->fullname);

                $a->coursename = $course->fullname;

                $a->profileurl = "$CFG->wwwroot/user/view.php?id=$USER->id&course=$course->id";

                $message = get_string('welcometocoursetext', '', $a);

                if (!$teacher = get_teacher($course->id)) {
                    $teacher = get_admin();
                }

                email_to_user($USER, $teacher, $subject, $message);

                add_to_log($course->id, 'course', 'enrol', "view.php?id=$course->id", (string)$USER->id);

                $USER->student[$course->id] = true;

                if ($SESSION->wantsurl) {
                    $destination = $SESSION->wantsurl;

                    unset($SESSION->wantsurl);
                } else {
                    $destination = "$CFG->wwwroot/course/view.php?id=$course->id";
                }

                redirect($destination);
            }
        }

        $teacher = get_teacher($course->id);

        if (!isset($password)) {
            $password = '';
        }

        print_header($strloginto, $course->fullname, "<A HREF=\".\">$strcourses</A> -> $strloginto", 'form.password');

        print_course($course);

        include "$CFG->dirroot/enrol/internal/enrol.html";

        print_footer();

        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------
    }

    /**
     * The other half to print_entry, this checks the form data
     *
     * This function checks that the user has completed the task on the
     * enrolment entry page and then enrolls them.
     *
     * @param mixed $form
     * @param mixed $course
     */
    public function check_entry($form, $course)
    {
        global $CFG, $USER, $SESSION, $THEME;

        if ($form->password == $course->password) {
            if (isguest()) {
                add_to_log($course->id, 'course', 'guest', "view.php?id=$course->id", $_SERVER['REMOTE_ADDR']);
            } else {  /// Update or add new enrolment
                if ($course->enrolperiod) {
                    $timestart = time();

                    $timeend = $timestart + $course->enrolperiod;
                } else {
                    $timestart = $timeend = 0;
                }

                if (!enrol_student($USER->id, $course->id, $timestart, $timeend)) {
                    error('An error occurred while trying to enrol you.');
                }

                $subject = get_string('welcometocourse', '', $course->fullname);

                $a->coursename = $course->fullname;

                $a->profileurl = "$CFG->wwwroot/user/view.php?id=$USER->id&course=$course->id";

                $message = get_string('welcometocoursetext', '', $a);

                if (!$teacher = get_teacher($course->id)) {
                    $teacher = get_admin();
                }

                email_to_user($USER, $teacher, $subject, $message);

                add_to_log($course->id, 'course', 'enrol', "view.php?id=$course->id", (string)$USER->id);
            }

            $USER->student[$course->id] = true;

            if ($SESSION->wantsurl) {
                $destination = $SESSION->wantsurl;

                unset($SESSION->wantsurl);
            } else {
                $destination = "$CFG->wwwroot/course/view.php?id=$course->id";
            }

            redirect($destination);
        } else {
            $this->errormsg = get_string('enrolmentkeyhint', '', mb_substr($course->password, 0, 1));
        }
    }

    /**
     * Prints a form for configuring the current enrolment plugin
     *
     * This function is called from admin/enrol.php, and outputs a
     * full page with a form for defining the current enrolment plugin.
     *
     * @param mixed $page
     */
    public function config_form($page)
    {
    }

    /**
     * Processes and stored configuration data for the enrolment plugin
     *
     * Processes and stored configuration data for the enrolment plugin
     *
     * @param mixed $config
     * @return bool
     */
    public function process_config($config)
    {
        $return = true;

        foreach ($config as $name => $value) {
            if (!set_config($name, $value)) {
                $return = false;
            }
        }

        return $return;
    }

    /**
     * This function is run by admin/cron.php every time
     *
     * The cron function can perform regular checks for the current
     * enrollment plugin.  For example it can check a foreign database,
     * all look for a file to pull data in from
     */
    public function cron()
    {
        // Delete students from all courses where their enrolment period has expired

        $select = "timeend > '0' AND timeend < '" . time() . "'";

        if ($students = get_records_select('user_students', $select)) {
            foreach ($students as $student) {
                unenrol_student($student->userid, $student->course);
            }
        }

        if ($teachers = get_records_select('user_teachers', $select)) {
            foreach ($teachers as $teacher) {
                remove_teacher($teacher->userid, $teacher->course);
            }
        }
    }

    /**
     * Returns the relevant icons for a course
     *
     * Returns the relevant icons for a course
     *
     * @param mixed $course
     * @return string
     */
    public function get_access_icons($course)
    {
        global $CFG;

        $str = '';

        if ($course->guest) {
            $strallowguests = get_string('allowguests');

            $str .= "<a title=\"$strallowguests\" href=\"$CFG->wwwroot/course/view.php?id=$course->id\">";

            $str .= "<img vspace=4 alt=\"$strallowguests\" height=16 width=16 border=0 src=\"$CFG->pixpath/i/guest.gif\"></a>&nbsp;&nbsp;";
        }

        if ($course->password) {
            $strrequireskey = get_string('requireskey');

            $str .= "<a title=\"$strrequireskey\" href=\"$CFG->wwwroot/course/view.php?id=$course->id\">";

            $str .= "<img vspace=4 alt=\"$strrequireskey\" height=16 width=16 border=0 src=\"$CFG->pixpath/i/key.gif\"></a>";
        }

        return $str;
    }
} /// end of class
