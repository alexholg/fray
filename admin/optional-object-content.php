<?php

include "includes.php";

Configuration::getControlPanel()->accessOrRedirect();
$settings = Configuration::getSettings();

// Load the main template
$mainT = Configuration::getControlPanel()->getMainTemplate();
$contentT = new Template("templates/common/box.php");
$contentId = @$_GET['id'];

// Valid content ID
if (isset($settings['admin']['extra-links'][$contentId])) {
    $entry = $settings['admin']['extra-links'][$contentId];
    $mainT->pagetitle = $entry['title'];
    $stylesheets = array();
    $scripts = array();

    // Scripts / css
    foreach ($entry['resources'] as $resource) {
        $ext = substr(strtolower($resource), strlen($resource) - 3);
        if ($ext == "css") {
            $stylesheets[] = "../" . $resource;
        }
        else if ($ext == ".js") {
            $scripts[] = "../". $resource;
        }
    }
    $mainT->scripts = $scripts;
    $mainT->stylesheets = $stylesheets;

    // Contents
    ob_start();
    include("../" . $entry['path']);
    $contentT->content = ob_get_contents();
    ob_end_clean();
}
// Invalid content ID
else {
    $mainT->pagetitle = "Invalid contents";
    $contentT->content = "The content with specified ID '" . htmlspecialchars($contentId, ENT_QUOTES) . "' was not found.";
}

// Output the HTML
$mainT->content = $contentT->render();
echo $mainT->render();
