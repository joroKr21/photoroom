<?php

/*
 * This script is used to import the database a from file.
 * NOTE: Once import is complete, delete this file from site location.
 */
session_start();
require_once '../core.inc.php';
// allow only administrators to execute this script
if ((!isset($_SESSION['uid']) || $_SESSION['level'] <= 0)) {
    exit('<span class="error">You do not have permission to execute this script</span>');
} else if (isset($_POST['backup'])) {
    if (backupTables(DB_HOST, DB_USER, DB_PASS, DB_NAME, $_POST['tables'])) {
        $tables = is_string($_POST['tables']) ? 'all' : implode(', ', $_POST['tables']);
        exit("<span class=\"success\">Successfully backed up tables: $tables</span>");
    } else {
        exit('<span class="error">Something went wrong</span>');
    }
}

/* backup the db OR just a table */

function backupTables($host, $user, $pass, $name, $tables = '*') {

    $link = mysql_connect($host, $user, $pass);
    mysql_select_db($name, $link);

    //get all of the tables
    if ($tables == '*') {
        $tables = array();
        $result = mysql_query('SHOW TABLES');
        while ($row = mysql_fetch_row($result)) {
            $tables[] = $row[0];
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',', $tables);
    }

    //cycle through
    foreach ($tables as $table) {
        $result = mysql_query('SELECT * FROM ' . $table);
        $num_fields = mysql_num_fields($result);

        $return.= 'DROP TABLE ' . $table . ';';
        $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE ' . $table));
        $return.= "\n\n" . $row2[1] . ";\n\n";

        for ($i = 0; $i < $num_fields; $i++) {
            while ($row = mysql_fetch_row($result)) {
                $return.= 'INSERT INTO ' . $table . ' VALUES(';
                for ($j = 0; $j < $num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = ereg_replace("\n", "\\n", $row[$j]);
                    if (isset($row[$j])) {
                        $return.= '"' . $row[$j] . '"';
                    } else {
                        $return.= '""';
                    }
                    if ($j < ($num_fields - 1)) {
                        $return.= ',';
                    }
                }
                $return.= ");\n";
            }
        }
        $return.="\n\n\n";
    }

    //save file
    if (!$handle = fopen(DB_PATH . '/backup.' . time() . '.sql', 'w+')) {
        return false;
    } else if (!fwrite($handle, $return)) {
        fclose($handle);
        return false;
    } else {
        fclose($handle);
        return true;
    }
}

?>
