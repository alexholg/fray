<?php

include "includes.php";

Configuration::getControlPanel()->accessOrRedirect();

// Load the main template
$mainT = Configuration::getControlPanel()->getMainTemplate();
$mainT->pagetitle = l10n("blog_title", "Blog");
//$mainT->stylesheets = array("css/comments.css");
$mainT->content = "";
$contentT = new Template("templates/common/box.php");
$contentT->cssClass = "blog";
$contentT->content = "";

// Show the category and post selectors
$selectorsT = new Template("templates/blog/selectors.php");
$selectorsT->categories = $imSettings['blog']['posts_cat'];
$selectorsT->selectedCategory = @$_GET['category'];
$selectorsT->posts = $imSettings['blog']['posts'];
if (isset($_GET['category'])) {
    foreach ($imSettings['blog']['posts_cat'] as $category => $posts) {
        if (str_replace(' ', '_', $category) === $_GET['category']) {
            $selectorsT->categoryPosts = $posts;
        }
    }
	$selectorsT->selectedPost = @$_GET['post'];
}
$contentT->content .= $selectorsT->render();

$topic = false;
if (isset($_GET['category']) && isset($_GET['post'])) {
	$data = $imSettings['blog'];
	$topic = new ImTopic($data['file_prefix'] . 'pc' . $_GET['post'], "../");
	$posturl = 'blog.php?category=' . urlencode($_GET['category']) . '&post=' . $_GET['post'];
	$topic->setPostUrl($posturl);

	switch($data['sendmode']) {
		case "file":
			$topic->loadXML($data['folder']);
		break;
	}

	// Take care of the actions
	if (isset($_GET['disable'])) {
        $n = (int)$_GET['disable'];
        $c = $topic->comments->get($n);
        if (count($c) != 0) {
            $c['approved'] = "0";
            $topic->comments->edit($n, $c);
            $topic->save();
        }
    }

    if (isset($_GET['enable'])) {
        $n = (int)$_GET['enable'];
        $c = $topic->comments->get($n);
        if (count($c) != 0) {
            $c['approved'] = "1";
            $topic->comments->edit($n, $c);
            $topic->save();
        }
    }

    if (isset($_GET['delete'])) {
        $topic->comments->delete((int)$_GET['delete']);
        $topic->save();
    }

    if (isset($_GET['unabuse'])) {
        $n = (int)$_GET['unabuse'];
        $c = $topic->comments->get($n);
        if (count($c)) {
            $c['abuse'] = "0";
            $topic->comments->edit($n, $c);
            $topic->save();
        }
    }

    if (isset($_GET['disable']) || isset($_GET['enable']) || isset($_GET['delete']) || isset($_GET['unabuse'])) {
        echo "<script>window.location.href='" . $posturl . "';</script>\n";
        exit();
    }

	// Show the summary
	$rating = $topic->getRating();
	$ratingT = new Template("templates/comments/summary.php");
	$ratingT->vote = $rating["rating"];
	$ratingT->count = $rating["count"];
	$ratingT->hasRating = $data['comment_type'] != "comment";
	$contentT->content .= $ratingT->render();
	if ($topic->hasComments()) {
		// Show the comments
		$commentsT = new Template("templates/comments/comments.php");
		$commentsT->comments = $topic->comments->comments;
		$commentsT->siteUrl = $imSettings['general']['url'];
		$commentsT->posturl = $posturl . "&";
		$commentsT->rating = $data['comment_type'] != "comment";
		$contentT->content .= $commentsT->render();
	}

}

$mainT->content = $contentT->render();
echo $mainT->render();
