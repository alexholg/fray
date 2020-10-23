<?php

if (!defined('PHP_MIN_VERSION')) {
    define('PHP_MIN_VERSION', '5.6.0');
}
include "includes.php";

/**
 * Server Test Class
 * @access public
 */
class imAdminTest {

    /**
     * Test the current WSX5 configuration
     *
     * @return Array An array containing the test results as array("name" => string, "success" => bool, "message" => string)
     */
    static public function testWsx5Configuration()
    {
        global $imSettings;

        $testedFolders = array();
        $results = array();

        $results[] = array(
            "name" => str_replace(array('[PHP_MIN_VERSION]', '[PHP_ACTUAL_VERSION]'), array(PHP_MIN_VERSION, PHP_VERSION), l10n('admin_test_php_version')),
            "message" => l10n('admin_test_version_suggestion'),
            "success" => imTest::php_version_test()
        );

        $results[] = array(
            "name"    => l10n('admin_test_session'),
            "message" => l10n('admin_test_session_suggestion'),
            "success" => imTest::session_test()
        );

        @chdir("../.");

        // Generic public folder
        if (isset($imSettings['general']['public_folder'])) {
            $testedFolders[] = $imSettings['general']['public_folder'];
            $results[] = array(
                "name"    => l10n('admin_test_folder') . ($imSettings['general']['public_folder'] != "" ? " (" . $imSettings['general']['public_folder'] . ")": " (site root folder)"),
                "message" => l10n("admin_test_folder_suggestion"),
                "success" => imTest::writable_folder_test($imSettings['general']['public_folder'])
            );
        }

        // Blog public folder
        if (isset($imSettings['blog']) && $imSettings['blog']['comments_source'] == 'wsx5' && $imSettings['blog']['sendmode'] == 'file' && !in_array($imSettings['blog']['folder'], $testedFolders)) {
            $testedFolders[] = $imSettings['blog']['folder'];
            $results[] = array(
                "name"    => l10n('admin_test_folder') . ($imSettings['blog']['folder'] != "" ? " (" . $imSettings['blog']['folder'] . ")": " (site root folder)"),
                "message" => l10n("admin_test_folder_suggestion"),
                "success" => imTest::writable_folder_test($imSettings['blog']['folder'])
            );
        }

        // Guestbooks public folder
        if (isset($imSettings['guestbooks'])) {
            foreach($imSettings['guestbooks'] as $gb) {
                if ($gb['sendmode'] == 'file') {
                    // Check this folder only if it's different from the blog's one
                    if (!in_array($gb['folder'], $testedFolders)) {
                        $testedFolders[] = $gb['folder'];
                        $results[] = array(
                            "name"    => l10n('admin_test_folder') . ($gb['folder'] != "" ? " (" . $gb['folder'] . ")" :  " (site root folder)"),
                            "message" => l10n("admin_test_folder_suggestion"),
                            "success" => imTest::writable_folder_test($gb['folder'])
                        );
                    }
                }
            }
        }


        @chdir("admin");

        // Databases
        if (isset($imSettings['databases'])) {
            foreach($imSettings['databases'] as $db) {
                $database = new ImDb($db['host'], $db['user'], $db['password'], $db['database']);
                $results[] = array(
                    "name"    => l10n('admin_test_database') . " (" . $db['description'] . ")",
                    "message" => l10n("admin_test_database_suggestion"),
                    "success" => imTest::mysql_test($database)
                );
            }
        }

        return $results;
    }

}

// End of file

