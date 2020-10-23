<?php

include "includes.php";
include "imadmintest.php";

Configuration::getControlPanel()->accessOrRedirect();

$results = imAdminTest::testWsx5Configuration();
 
if (isset($_POST['send'])) {
	$attachment = array();
	if (is_uploaded_file($_FILES['attachment']['tmp_name']) && file_exists($_FILES['attachment']['tmp_name'])) {
		$attachment = array(array(
			"name" => $_FILES['attachment']['name'],
			"mime" => $_FILES['attachment']['type'],
			"content" => file_get_contents($_FILES['attachment']['tmp_name'])
		));
	}
	$mailer = new ImSendEmail();
	if ($_POST['type'] == 'phpmailer' || $_POST['type'] == 'phpmailer-smtp') {
		$mailer->emailType = 'phpmailer';
		if ($_POST['type'] == 'phpmailer-smtp') {
			$mailer->use_smtp = true;
			$mailer->smtp_port = $_POST['smtpport'];
			$mailer->smtp_host = $_POST['smtphost'];
			$mailer->smtp_encryption = $_POST['smtpencryption'];
			if (@$_POST['smtpauth'] == "1") {
				$mailer->use_smtp_auth = true;
				$mailer->smtp_username = $_POST['smtpusername'];
				$mailer->smtp_password = $_POST['smtppassword'];
			}
		}					
	} else {
		$mailer->emailType = $_POST['type'];
	}
	$result = $mailer->send($_POST['from'], $_POST['to'], $_POST['subject'], strip_tags($_POST['body']), nl2br($_POST['body']), $attachment);

	// Save the test data for this session
	$_SESSION['form_test_type'] = $_POST['type'];
	$_SESSION['form_test_from'] = $_POST['from'];
	$_SESSION['form_test_to'] = $_POST['to'];
	$_SESSION['form_test_subject'] = $_POST['subject'];
	$_SESSION['form_test_body'] = $_POST['body'];
	$_SESSION['form_test_smtphost'] = $_POST['smtphost'];
	$_SESSION['form_test_smtpport'] = $_POST['smtpport'];
	$_SESSION['form_test_smtpencryption'] = $_POST['smtpencryption'];
	$_SESSION['form_test_smtpauth'] = @$_POST['smtpauth'];
	$_SESSION['form_test_smtpusername'] = $_POST['smtpusername'];
	echo "<script>window.top.location.href = 'sitetest.php?result=" . ($result ? 1 : 0) . "';</script>";
	exit();
}

// Load the main template
$mainT = Configuration::getControlPanel()->getMainTemplate();
$mainT->pagetitle = l10n('admin_test', 'Website Test');
$mainT->stylesheets = array("css/sitetest.css");
$mainT->content = "";
$contentT = new Template("templates/common/box.php");
$contentT->cssClass = "sitetest";
$contentT->content = "";

// Show the system tests
$testT = new Template("templates/sitetest/system-tests.php");
$testT->tests = $results;
$contentT->content .= $testT->render();

// Show the email form
$emailT = new Template("templates/sitetest/email-form.php");
$contentT->content .= $emailT->render();

// Read the log
$path = pathCombine(array("../", $imSettings['general']['public_folder'], "email_log.txt"));
if (file_exists($path) && function_exists("file_get_contents")) {
	$logT = new Template("templates/sitetest/log.php");
	$logT->log = file_get_contents($path);
	$contentT->content .= $logT->render();
}

$mainT->content = $contentT->render();
echo $mainT->render();
