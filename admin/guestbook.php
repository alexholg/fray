<?php

include "includes.php";

Configuration::getControlPanel()->accessOrRedirect();

// Load the main template
$mainT = Configuration::getControlPanel()->getMainTemplate();
$mainT->pagetitle = l10n("admin_guestbook", "Comments and Ratings");
//$mainT->stylesheets = array("css/comments.css");
$mainT->content = "";
$contentT = new Template("templates/common/box.php");
$contentT->cssClass = "guestbook";
$contentT->content = "";

$id = isset($_GET['id']) ? $_GET['id'] : "";

// If there's only one guestbook just show it and don't ask for more
if (!strlen($id) && count($imSettings['guestbooks']) < 2) {
	$keys = array_keys($imSettings['guestbooks']);
	$id = $imSettings['guestbooks'][$keys[0]]['id'];
}
// Otherwise show the selectors
else {
	$selectorsT = new Template("templates/guestbook/selectors.php");
	$selectorsT->guestbooks = $imSettings['guestbooks'];
	$selectorsT->id = $id;
	$contentT->content .= $selectorsT->render();
}
$gb = false;

if (strlen($id)) {
	$data = $imSettings['guestbooks'][$id];
	$gb = new ImTopic($id, "../");
	$posturl = 'guestbook.php?id=' . $id;
	$gb->setPostUrl($posturl);
	switch($data['sendmode'])
	{
		case "file":
			$gb->loadXML($data['folder']);
		break;
	}

	// Take care of the actions
	if (isset($_GET['disable'])) {
        $n = (int)$_GET['disable'];
        $c = $gb->comments->get($n);
        if (count($c) != 0) {
            $c['approved'] = "0";
            $gb->comments->edit($n, $c);
            $gb->save();
        }
    }

    if (isset($_GET['enable'])) {
        $n = (int)$_GET['enable'];
        $c = $gb->comments->get($n);
        if (count($c) != 0) {
            $c['approved'] = "1";
            $gb->comments->edit($n, $c);
            $gb->save();
        }
    }

    if (isset($_GET['delete'])) {
        $gb->comments->delete((int)$_GET['delete']);
        $gb->save();
    }

    if (isset($_GET['unabuse'])) {
        $n = (int)$_GET['unabuse'];
        $c = $gb->comments->get($n);
        if (count($c)) {
            $c['abuse'] = "0";
            $gb->comments->edit($n, $c);
            $gb->save();
        }
    }

    if (isset($_GET['disable']) || isset($_GET['enable']) || isset($_GET['delete']) || isset($_GET['unabuse'])) {
        echo "<script>window.top.location.href='" . $posturl . "';</script>\n";
        exit();
    }

	// Show the summary
	$rating = $gb->getRating();
	$ratingT = new Template("templates/comments/summary.php");
	$ratingT->vote = $rating["rating"];
	$ratingT->count = $rating["count"];
	$ratingT->hasRating = $data['rating'];
	$contentT->content .= $ratingT->render();
	if ($gb->hasComments())
	{
		$commentsT = new Template("templates/comments/comments.php");
		$commentsT->comments = $gb->comments->comments;
		$commentsT->siteUrl = $imSettings['general']['url'];
		$commentsT->posturl = $posturl . "&";
		$commentsT->rating = $data['rating'];
		$contentT->content .= $commentsT->render();
	}
}

$mainT->content = $contentT->render();
echo $mainT->render();
