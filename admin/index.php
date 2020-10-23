<?php

include "includes.php";
include "imadmintest.php";

// Redirect to a specific section
$redirect = Configuration::getControlPanel()->getRedirectFromArray($_GET);
if ($redirect) {
    header("Location: " . $redirect);
    exit(0);
}
// Otherwise attempt the login
Configuration::getControlPanel()->accessOrRedirect();

$main = Configuration::getControlPanel()->getMainTemplate();
$main->pagetitle = l10n("admin_dashboard", "Dashboard");
$main->stylesheets = array("css/dashboard.css");
$main->content = "";


// ----------
// Statistics
// ----------

if (isset($imSettings['analytics']) && $imSettings['analytics']['type'] == 'wsx5analytics') {
    $analytics = Configuration::getAnalytics();

    // Visitors Count
    $visitsPlotT = new Template("templates/dashboard/simple-plot.php");
    $visitsPlotT->title = l10n('admin_analytics_visitorscount', 'Visitors Count') . " - " . l10n("admin_lastdays", "Last 7 days");
    $visitsPlotT->type = "line";
    $visitsPlotT->colorIndex = 0;
    $visitsPlotT->data = $analytics->getTotalSiteVisitors(date("Y-m-d H:i:s", strtotime("-7 days")), date("Y-m-d H:i:s"));

    // Views count
    $viewsPlotT = new Template("templates/dashboard/simple-plot.php");
    $viewsPlotT->title = l10n('admin_analytics_pageviews', 'Page Views') . " - " . l10n("admin_lastdays", "Last 7 days");
    $viewsPlotT->type = "line";
    $viewsPlotT->colorIndex = 1;
    $viewsPlotT->data = $analytics->getPageViews(date("Y-m-d H:i:s", strtotime("-7 days")), date("Y-m-d H:i:s"));

    // Main render
    $boxT = new Template("templates/dashboard/box.php");
    $boxT->title = l10n("admin_analytics_title", "Statistics");
    $boxT->content = $visitsPlotT->render();
    $boxT->content .= $viewsPlotT->render();
    $boxT->image = "images/analytics_black.png";
    $main->content .= $boxT->render();
}




// ---------------
// Blog
// ---------------

if (isset($imSettings['blog']) && $imSettings['blog']['comments_source'] == 'wsx5') {
    $blog = Configuration::getBlog();
    $comments = $blog->getComments(date("Y-m-d", strtotime("-7 days")) . " 00:00:01", date("Y-m-d") . " 23:59:59", true);

    $boxT = new Template("templates/dashboard/box.php");
    $boxT->title = l10n("blog_title", "Blog");
    $boxT->content = "";
    $boxT->image = "images/blog_black.png";

    // Summary
    $rowT = new Template("templates/dashboard/summary-row.php");
    $rowT->icon = "fa-comment";
    $rowT->iconColoredClass = "background-color-6";
    $rowT->iconEmptyClass = "background-mute";
    $rowT->value = count($comments);
    $rowT->caption = ucfirst(l10n('blog_comments', 'Comments')) . " - " . l10n("admin_lastdays", "Last 7 days");
    $boxT->content .= $rowT->render();

    // Latest comments
    $commentT = new Template("templates/dashboard/comment-row.php");
    for ($i = 0; $i < count($comments) && $i < 3; $i++) {
        $commentT->name = $comments[$i]['name'];
        $commentT->body = $comments[$i]['body'];
        $commentT->title = $comments[$i]['title'];
        $commentT->timestamp = $comments[$i]['timestamp'];
        $boxT->content .= $commentT->render();
    }

    // Count of messages to be validated
    $rowT = new Template("templates/dashboard/summary-row.php");
    $rowT->icon = "fa-exclamation-triangle";
    $rowT->iconColoredClass = "background-color-3";
    $rowT->iconEmptyClass = "background-mute";
    $rowT->value = count($blog->getCommentsToValidate("", ""));
    $rowT->caption = ucfirst(l10n("blog_waiting_approval", "Waiting for approval"));
    $boxT->bottom = $rowT->render();

    $main->content .= $boxT->render();
}




// -----------------
// Guestbook
// -----------------

if (count($imSettings['guestbooks']) > 0) {
    $validatedComments = ImGuestbook::getAllComments(date("Y-m-d", strtotime("-7 days")) . " 00:00:01", date("Y-m-d") . " 23:59:59", true);
    $waitingComments = ImGuestbook::getAllComments("", "", false);

    $boxT = new Template("templates/dashboard/box.php");
    $boxT->title = l10n("admin_guestbook", "Comments and Ratings");
    $boxT->content = "";
    $boxT->image = "images/guestbook_black.png";

    // Summary
    $rowT = new Template("templates/dashboard/summary-row.php");
    $rowT->icon = "fa-comment";
    $rowT->iconColoredClass = "background-color-6";
    $rowT->iconEmptyClass = "background-mute";
    $rowT->value = count($validatedComments);
    $rowT->caption = ucfirst(l10n('guestbook_messages', 'Messages')) . " - " . l10n("admin_lastdays", "Last 7 days");
    $boxT->content .= $rowT->render();

    // Latest comments
    $commentT = new Template("templates/dashboard/comment-row.php");
    for ($i = 0; $i < count($validatedComments) && $i < 3; $i++) {
        $commentT->name = $validatedComments[$i]['name'];
        $commentT->body = $validatedComments[$i]['body'];
        $commentT->title = $validatedComments[$i]['title'];
        $commentT->timestamp = $validatedComments[$i]['timestamp'];
        $boxT->content .= $commentT->render();
    }

    $rowT = new Template("templates/dashboard/summary-row.php");
    $rowT->icon = "fa-exclamation-triangle";
    $rowT->iconColoredClass = "background-color-3";
    $rowT->iconEmptyClass = "background-mute";
    $rowT->value = count($waitingComments);
    $rowT->caption = ucfirst(l10n("blog_waiting_approval", "Waiting for approval"));
    $boxT->bottom = $rowT->render();

    $main->content .= $boxT->render();
}


// -----------------
// Tests
// -----------------

// Results list
$results = imAdminTest::testWsx5Configuration();
$list = "";
$count = 0;
$testT = new Template("templates/dashboard/test-row.php");
foreach ($results as $result) {
    if (!$result['success']) {
        $count++;
        $testT->name = $result['name'];
        $list .= $testT->render();
    }
}

if ($count > 0) {
    $boxT = new Template("templates/dashboard/box.php");
    $boxT->title = l10n("admin_test_title", "Website Test");
    $boxT->content = "";
    $boxT->image = "images/test_black.png";

    // Summary
    $rowT = new Template("templates/dashboard/summary-row.php");
    $rowT->icon = "fa-exclamation-triangle";
    $rowT->iconColoredClass = "background-color-3";
    $rowT->iconEmptyClass = "background-mute";
    $rowT->value = $count;
    $rowT->caption = ucfirst(l10n("admin_test_notpassed", "Not passed"));
    $boxT->content .= $rowT->render();

    // Errors list
    $boxT->content .= $list;

    $main->content .= $boxT->render();
}


// ----------------------
// Optional Objects boxes
// ----------------------

$settings = Configuration::getSettings();
$boxT = new Template("templates/dashboard/box.php");

foreach ($settings['admin']['extra-dashboard'] as $key => $item) {
    $boxT->title = $item['title'];
    $boxT->image = "../" . $item['icon'];
    // Contents
    ob_start();
    include("../" . $item['path']);
    $boxT->content = ob_get_contents();
    ob_end_clean();
    // Append the box to the main template
    $main->content .= $boxT->render();
}

echo $main->render();

