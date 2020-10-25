<?php

// $Id: search.php,v 1.29 2004/08/22 14:38:41 gustav_delius Exp $

require_once '../../config.php';
require_once 'lib.php';

require_variable($id);           // course id
optional_variable($search, '');  // search string
optional_variable($page, '0');   // which page to show
optional_variable($perpage, '20');   // which page to show

$search = trim(strip_tags($search));

if ($search) {
    $searchterms = explode(' ', $search);    // Search for words independently

    foreach ($searchterms as $key => $searchterm) {
        if (mb_strlen($searchterm) < 2) {
            unset($searchterms[$key]);
        }
    }

    $search = s(trim(implode(' ', $searchterms)));
}

if (!$course = get_record('course', 'id', $id)) {
    error('Course id is incorrect.');
}

if ($course->category or $CFG->forcelogin) {
    require_login($course->id);
}

add_to_log($course->id, 'forum', 'search', "search.php?id=$course->id&search=" . urlencode($search), $search);

$strforums = get_string('modulenameplural', 'forum');
$strsearch = get_string('search', 'forum');
$strsearchresults = get_string('searchresults', 'forum');
$strpage = get_string('page');

$searchform = forum_print_search_form($course, $search, true, 'plain');

if (!$search) {
    print_header_simple(
        (string)$strsearch,
        '',
        "<A HREF=\"index.php?id=$course->id\">$strforums</A> -> $strsearch",
        'search.search',
        '',
        '',
        '&nbsp;',
        navmenu($course)
    );

    print_simple_box_start('center');

    echo '<center>';

    echo '<br>';

    echo $searchform;

    echo '<br><p>';

    print_string('searchhelp');

    echo '</p>';

    echo '</center>';

    print_simple_box_end();
}

if ($search) {
    if (!$posts = forum_search_posts($searchterms, $course->id, $page * $perpage, $perpage, $totalcount)) {
        print_header_simple(
            (string)$strsearchresults,
            '',
            "<a href=\"index.php?id=$course->id\">$strforums</a> -> 
                      <a href=\"search.php?id=$course->id\">$strsearch</a> -> \"$search\"",
            'search.search',
            '',
            '',
            '&nbsp;',
            navmenu($course)
        );

        print_heading(get_string('nopostscontaining', 'forum', $search));

        print_simple_box_start('center');

        echo '<center>';

        echo '<br>';

        echo $searchform;

        echo '<br><p>';

        print_string('searchhelp');

        echo '</p>';

        echo '</center>';

        print_simple_box_end();

        print_footer($course);

        //--------------------------------------------

        // MOODLE4XOOPS - J. BAUDIN

        //--------------------------------------------

        require_once "$CFG->dirroot/footer.php";

        //--------------------------------------------

        exit;
    }

    print_header_simple(
        (string)$strsearchresults,
        '',
        "<a href=\"index.php?id=$course->id\">$strforums</a> -> 
                  <a href=\"search.php?id=$course->id\">$strsearch</a> -> \"$search\"",
        'search.search',
        '',
        '',
        $searchform,
        navmenu($course)
    );

    print_heading("$strsearchresults: $totalcount");

    echo '<center>';

    print_paging_bar($totalcount, $page, $perpage, "search.php?search=$search&id=$course->id&perpage=$perpage&");

    echo '</center>';

    foreach ($posts as $post) {
        if (!$discussion = get_record('forum_discussions', 'id', $post->discussion)) {
            error('Discussion ID was incorrect');
        }

        if (!$forum = get_record('forum', 'id', (string)$discussion->forum)) {
            error("Could not find forum $discussion->forum");
        }

        $post->subject = highlight((string)$search, $post->subject);

        $discussion->name = highlight((string)$search, $discussion->name);

        $fullsubject = "<a href=\"view.php?f=$forum->id\">$forum->name</a>";

        if ('single' != $forum->type) {
            $fullsubject .= " -> <a href=\"discuss.php?d=$discussion->id\">$discussion->name</a>";

            if (0 != $post->parent) {
                $fullsubject .= " -> <a href=\"discuss.php?d=$post->discussion&parent=$post->id\">$post->subject</a>";
            }
        }

        $post->subject = $fullsubject;

        $fulllink = "<p align=\"right\"><a href=\"discuss.php?d=$post->discussion#$post->id\">" . get_string('postincontext', 'forum') . '</a></p>';

        forum_print_post($post, $course->id, false, false, false, false, $fulllink, $search);

        echo '<br>';
    }

    echo '<center>';

    print_paging_bar($totalcount, $page, $perpage, 'search.php?search=' . urlencode($search) . "&id=$course->id&perpage=$perpage&");

    echo '</center>';
}

print_footer($course);
//--------------------------------------------
// MOODLE4XOOPS - J. BAUDIN
//--------------------------------------------
require_once "$CFG->dirroot/footer.php";
//--------------------------------------------



