<?php

/*
 * This script is used to directly execute SQL queries for administration purposes
 */
session_start();
require_once '../core.inc.php';
require_once DB_PATH . '/db.php';
// allow only administrators to execute this script
if ((!isset($_SESSION['uid']) || $_SESSION['level'] <= 0)) {
    exit('<span class="error">You do not have permission to execute this script</span>');
} else if (isset($_POST['go'])) {
    executeSQL($_POST['sql']);
}

// directly execute an sql query
function executeSQL($query) {
    global $db;
    try {
        // prepare query
        $statement = $db->prepare($query);
        // get array of all function parameters
        $args = func_get_args();
        // drop first element (this is the $query itself)
        array_shift($args);
        // pass array of parameters for the query
        $statement->execute($args);
        // fetch results
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $result = $statement->fetchAll();
        // success
        echo '<span class="success">Query executed successfully</span><br/>Last inserted ID: ', $db->lastInsertId(),
        '<br/>Rows: ', $statement->rowCount(), '; Columns: ', $statement->columnCount(),
        '<br/><textarea rows="15" style="width:900px;background-color:white" disabled="disabled">';
        // print results if any
        print_r($result);
        echo '</textarea>';
    } catch (PDOException $e) {
        die('<span class="error">Database error: ' . $e->getMessage() . '</span>');
    }
}

?>
