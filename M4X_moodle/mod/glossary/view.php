<?php

declare(strict_types=1);

// $Id: view.php,v 1.52.2.5 2004/10/05 22:51:48 stronk7 Exp $
/// This page prints a particular instance of glossary
require_once '../../config.php';
require_once 'lib.php';
require_once "$CFG->dirroot/rss/rsslib.php";

global $CFG, $THEME, $USER;
$debug = 0;
$CFG->startpagetime = microtime();

optional_variable($id);           // Course Module ID
optional_variable($g);            // Glossary ID

optional_variable($tab, GLOSSARY_NO_VIEW); // browsing entries by categories?

optional_variable($mode, '');  // [ "term"   | "entry"  | "cat"     | "date" |
//   "letter" | "search" | "author"  | "approval" ]
optional_variable($hook, '');  // the term, entry, cat, etc... to look for based on mode

optional_variable($fullsearch, 0); // full search (concept and definition) when searching?

optional_variable($sortkey, '');    // Sorted view:
//    [ CREATION | UPDATE | FIRSTNAME | LASTNAME |
//      concept | timecreated | ... ]
optional_variable($sortorder, '');  // it defines the order of the sorting (ASC or DESC)

optional_variable($offset, 0);      // entries to bypass (for paging purpouses)

optional_variable($show, '');       // [ concept | alias ] => mode=term hook=$show
optional_variable($displayformat, -1);  // override of the glossary display format

$mode = strip_tags(urldecode($mode));  //XSS
$hook = strip_tags(urldecode($hook));  //XSS
$fullsearch = strip_tags(urldecode($fullsearch));  //XSS
$sortkey = strip_tags(urldecode($sortkey));  //XSS
$sortorder = strip_tags(urldecode($sortorder));  //XSS
$offset = strip_tags(urldecode($offset));  //XSS
$show = strip_tags(urldecode($show));  //XSS

if (!empty($id)) {
    if (!$cm = get_record('course_modules', 'id', $id)) {
        error('Course Module ID was incorrect');
    }

    if (!$course = get_record('course', 'id', $cm->course)) {
        error('Course is misconfigured');
    }

    if (!$glossary = get_record('glossary', 'id', $cm->instance)) {
        error('Course module is incorrect');
    }
} elseif (!empty($g)) {
    if (!$glossary = get_record('glossary', 'id', $g)) {
        error('Course module is incorrect');
    }

    if (!$course = get_record('course', 'id', $glossary->course)) {
        error('Could not determine which course this belonged to!');
    }

    if (!$cm = get_coursemodule_from_instance('glossary', $glossary->id, $course->id)) {
        error('Could not determine which course module this belonged to!');
    }

    $id = $cm->id;
} else {
    error('Must specify glossary ID or course module ID');
}

if ($CFG->forcelogin) {
    require_login();
}

/// redirecting if adding a new entry
if (GLOSSARY_ADDENTRY_VIEW == $tab) {
    redirect("edit.php?id=$cm->id&mode=$mode");
}

/// setting the defaut number of entries per page if not set

if (!$entriesbypage = $glossary->entbypage) {
    $entriesbypage = $CFG->glossary_entbypage;
}

/// setting the default values for the display mode of the current glossary
/// only if the glossary is viewed by the first time
if ($dp = get_record('glossary_formats', 'name', $glossary->displayformat)) {
    $printpivot = $dp->showgroup;

    if ('' == $mode and '' == $hook and '' == $show) {
        $mode = $dp->defaultmode;

        $hook = $dp->defaulthook;

        $sortkey = $dp->sortkey;

        $sortorder = $dp->sortorder;
    }
} else {
    $printpivot = 1;

    if ('' == $mode and '' == $hook and '' == $show) {
        $mode = 'letter';

        $hook = 'ALL';
    }
}

if (-1 == $displayformat) {
    $displayformat = $glossary->displayformat;
}

if ($show) {
    $mode = 'term';

    $hook = $show;

    $show = '';
}
/// Processing standard security processes
$navigation = '';
if ($course->category) {
    $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";

    require_login($course->id);
}
if (!$cm->visible and !isteacher($course->id)) {
    notice(get_string('activityiscurrentlyhidden'));
}
add_to_log($course->id, 'glossary', 'view', "view.php?id=$cm->id&tab=$tab", $glossary->id, $cm->id);

/// stablishing flag variables
if ($sortorder = mb_strtolower($sortorder)) {
    if ('asc' != $sortorder and 'desc' != $sortorder) {
        $sortorder = '';
    }
}
if ($sortkey = mb_strtoupper($sortkey)) {
    if ('CREATION' != $sortkey and 'UPDATE' != $sortkey and 'FIRSTNAME' != $sortkey and 'LASTNAME' != $sortkey) {
        $sortkey = '';
    }
}

switch ($mode = mb_strtolower($mode)) {
    case 'search': /// looking for terms containing certain word(s)
        $tab = GLOSSARY_STANDARD_VIEW;

        $searchterms = explode(' ', $hook); // Search for words independently
        foreach ($searchterms as $key => $searchterm) {
            if (mb_strlen($searchterm) < 2) {
                unset($searchterms[$key]);
            }
        }
        $hook = trim(implode(' ', $searchterms));
        break;
    case 'entry':  /// Looking for a certain entry id
        $tab = GLOSSARY_STANDARD_VIEW;
        if ($dp = get_record('glossary_formats', 'name', $glossary->displayformat)) {
            $displayformat = $dp->popupformatname;
        }
        break;
    case 'cat':    /// Looking for a certain cat
        $tab = GLOSSARY_CATEGORY_VIEW;
        if ($hook > 0) {
            $category = get_record('glossary_categories', 'id', $hook);
        }
        break;
    case 'approval':    /// Looking for entries waiting for approval
        $tab = GLOSSARY_APPROVAL_VIEW;
        if (!$hook and !$sortkey and !$sortorder) {
            $hook = 'ALL';
        }
        break;
    case 'term':   /// Looking for entries that include certain term in its concept, definition or aliases
        $tab = GLOSSARY_STANDARD_VIEW;
        break;
    case 'date':
        $tab = GLOSSARY_DATE_VIEW;
        if (!$sortkey) {
            $sortkey = 'UPDATE';
        }
        if (!$sortorder) {
            $sortorder = 'desc';
        }
        break;
    case 'author':  /// Looking for entries, browsed by author
        $tab = GLOSSARY_AUTHOR_VIEW;
        if (!$hook) {
            $hook = 'ALL';
        }
        if (!$sortkey) {
            $sortkey = 'FIRSTNAME';
        }
        if (!$sortorder) {
            $sortorder = 'asc';
        }
        break;
    case 'letter':  /// Looking for entries that begin with a certain letter, ALL or SPECIAL characters
    default:
        $tab = GLOSSARY_STANDARD_VIEW;
        if (!$hook) {
            $hook = 'ALL';
        }
        break;
}

switch ($tab) {
    case GLOSSARY_IMPORT_VIEW:
    case GLOSSARY_EXPORT_VIEW:
    case GLOSSARY_APPROVAL_VIEW:
        $isuserframe = 0;
        break;
    default:
        $isuserframe = 1;
        break;
}

/// Printing the heading
$strglossaries = get_string('modulenameplural', 'glossary');
$strglossary = get_string('modulename', 'glossary');
$strallcategories = get_string('allcategories', 'glossary');
$straddentry = get_string('addentry', 'glossary');
$strnoentries = get_string('noentries', 'glossary');
$strsearchconcept = get_string('searchconcept', 'glossary');
$strsearchindefinition = get_string('searchindefinition', 'glossary');
$strsearch = get_string('search');

print_header(
    strip_tags("$course->shortname: $glossary->name"),
    (string)$course->fullname,
    "$navigation <A HREF=index.php?id=$course->id>$strglossaries</A> -> $glossary->name",
    '',
    '',
    true,
    update_module_button($cm->id, $course->id, $strglossary),
    navmenu($course, $cm)
);

//If rss are activated at site and glossary level and this glossary has rss defined, show link
if (isset($CFG->enablerssfeeds) && isset($CFG->glossary_enablerssfeeds)
    && $CFG->enablerssfeeds
    && $CFG->glossary_enablerssfeeds
    && $glossary->rsstype
    && $glossary->rssarticles) {
    echo '<table width="100%" border="0" cellpadding="3" cellspacing="0"><tr valign="top"><td align="right">';

    $tooltiptext = get_string('rsssubscriberss', 'glossary', $glossary->name);

    if (empty($USER->id)) {
        $userid = 0;
    } else {
        $userid = $USER->id;
    }

    rss_print_link($course->id, $userid, 'glossary', $glossary->id, $tooltiptext);

    echo '</td></tr></table>';
}

echo '<p align="center"><font size="3"><b>' . stripslashes_safe($glossary->name);
if ($isuserframe and 'search' != $mode) {
    /// the "Print" icon

    echo ' <a title ="' . get_string('printerfriendly', 'glossary') . "\" target=\"printview\" href=\"print.php?id=$cm->id&mode=$mode&hook=$hook&sortkey=$sortkey&sortorder=$sortorder&offset=$offset\">";

    echo '<img border=0 src="print.gif"></a>';
}
echo '</b></font></p>';

/// Info box
if ($glossary->intro) {
    echo '<table align="center" width="70%" bgcolor="#FFFFFF" class="generaltab"><tr><td>';

    echo format_text($glossary->intro);

    print_simple_box_end();
}

/// Search box
//    echo '<p>';
echo '<table align="center" width="70%" bgcolor="' . $THEME->cellheading . '" class="generalbox"><tr><td align=center>';

echo '<p align="center">';
echo '<form method="POST" action="view.php">';
echo '<input type="submit" value="' . $strsearch . '" name="searchbutton"> ';
if ('search' == $mode) {
    echo '<input type="text" name="hook" size="20" value="' . $hook . '"> ';
} else {
    echo '<input type="text" name="hook" size="20" value=""> ';
}
if ($fullsearch) {
    $fullsearchchecked = 'checked';
} else {
    $fullsearchchecked = '';
}
echo '<input type="checkbox" name="fullsearch" value="1" ' . $fullsearchchecked . '>';
echo '<input type="hidden" name="mode" value="search">';
echo '<input type="hidden" name="id" value="' . $cm->id . '">';
echo $strsearchindefinition;
echo '</form>';
echo '</p>';
print_simple_box_end();

include 'tabs.html';

require_once 'sql.php';

/// printing the entries
$entriesshown = 0;
$currentpivot = '';
$ratingsmenuused = null;
$paging = null;

if ($allentries) {
    /// printing the paging links

    $paging = get_string('allentries', 'glossary');

    if ($offset < 0) {
        $paging = '<strong>' . $paging . '</strong>';
    } else {
        $paging = "<a href=\"view.php?id=$id&mode=$mode&hook=$hook&offset=-1&sortkey=$sortkey&sortorder=$sortorder&fullsearch=$fullsearch\">" . $paging . '</a>';
    }

    if ($count > $entriesbypage) {
        for ($i = 0; ($i * $entriesbypage) < $count; $i++) {
            if ('' != $paging) {
                if (0 == $i % 20 and $i) {
                    $paging .= '<br>';
                } else {
                    $paging .= ' | ';
                }
            }

            $pagenumber = (string)($i + 1);

            if ($offset / $entriesbypage == $i) {
                $paging .= '<strong>' . $pagenumber . '</strong>';
            } else {
                $paging .= "<a href=\"view.php?id=$id&mode=$mode&hook=$hook&offset=" . ($i * $entriesbypage) . "&sortkey=$sortkey&sortorder=$sortorder&fullsearch=$fullsearch\">" . $pagenumber . '</a>';
            }
        }

        $paging = '<font size=1><center>' . get_string('jumpto') . " $paging</center></font>";
    } else {
        $paging = '';
    }

    echo $paging;

    $ratings = null;

    $ratingsmenuused = false;

    if ($glossary->assessed and !empty($USER->id)) {
        if ($ratings->scale = make_grades_menu($glossary->scale)) {
            $ratings->assesstimestart = $glossary->assesstimestart;

            $ratings->assesstimefinish = $glossary->assesstimefinish;
        }

        if (2 == $glossary->assessed and !isteacher($course->id)) {
            $ratings->allow = false;
        } else {
            $ratings->allow = true;
        }

        echo '<form name=form method=post action=rate.php>';

        echo "<input type=hidden name=id value=\"$course->id\">";
    }

    foreach ($allentries as $entry) {
        /// Setting the pivot for the current entry

        $pivot = $entry->pivot;

        if (!$fullpivot) {
            $pivot = $pivot[0];
        }

        /// if there's a group break

        if ($currentpivot != mb_strtoupper($pivot)) {
            // print the group break if apply

            if ($printpivot) {
                $currentpivot = mb_strtoupper($pivot);

                echo '<p>';

                echo '<table width="95%" border="0" class="generaltabselected" bgcolor="' . $THEME->cellheading2 . '">';

                echo '<tr>';

                $pivottoshow = $currentpivot;

                if (isset($entry->uid)) {
                    // printing the user icon if defined (only when browsing authors)

                    echo '<td align="left">';

                    $user = get_record('user', 'id', $entry->uid);

                    print_user_picture($user->id, $course->id, $user->picture);

                    $pivottoshow = fullname($user, isteacher($course->id));
                } else {
                    echo '<td align="center">';
                }

                echo "<strong> $pivottoshow</strong>";

                echo '</td></tr></table>';
            }
        }

        $concept = $entry->concept;

        $definition = $entry->definition;

        /// highlight the term if necessary

        if ('search' == $mode) {
            $entry->highlight = $hook;
        }

        /// and finally print the entry.

        if (glossary_print_entry($course, $cm, $glossary, $entry, $mode, $hook, 1, $displayformat, $ratings)) {
            $ratingsmenuused = true;
        }

        $entriesshown++;
    }
}
if (!$entriesshown) {
    print_simple_box('<center>' . get_string('noentries', 'glossary') . '</center>', 'center', '95%');
}

if ($ratingsmenuused) {
    echo '<p><center><input type="submit" value="' . get_string('sendinratings', 'glossary') . '">';

    if ($glossary->scale < 0) {
        if ($scale = get_record('scale', 'id', abs($glossary->scale))) {
            print_scale_menu_helpbutton($course->id, $scale);
        }
    }

    echo '</center>';

    echo '</form>';
}

if ($paging) {
    echo "<hr>$paging";
}
echo '<p>';
echo '</center>';
glossary_print_tabbed_table_end();
if ($debug and isadmin()) {
    echo '<p>';

    print_simple_box("$sqlselect<br> $sqlfrom<br> $sqlwhere<br> $sqlorderby<br> $sqllimit", 'center', '85%');

    echo '<p align=right><font size=-3>';

    echo microtime_diff($CFG->startpagetime, microtime());

    echo '</font></p>';
}

/// Finish the page
print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------
