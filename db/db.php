<?

/*
 * Since we use a single database for this website, this file capsulates all db functionality.
 * This is useful for abstracting the database access.
 * If we later wanna change the underlying database engine,
 * we only need to change this file and not the rest of our website.
 * There are many approaches to this.
 * Here, we are preparing a set of functions called dbXXX that are mostly just interface to the regular PDO methods.
 */
// Here we create the PDO object and set the error mode to throw exceptions.
try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=UTF-8', DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    prettyDie('Connection error: ' . $e->getMessage());
}

// This is like the regular die function, it only checks if DEBUG is set as true.
// If yes, it prints the message we submitted (usually a detailed error message).
// If not (when we have already tested the site and put it on the production server),
// we just print a general error message.
function prettyDie($text) {
    if (DEBUG) {
        die("<div class=\"error\">$text</div>");
    } else {
        die('<div class="error">Connection error</div>');
    }
}

// close the database connection
function dbClose() {
    global $db;
    $db = null;
}

// get ID of last inserted item
function dbInsertID() {
    global $db;
    return $db->lastInsertId();
}

// We use this function to escape our user input.
// It is recommended to use prepared statements instead of escaping, though.
function dbEscape($input) {
    global $db;
    return $db->quote($input);
}

// This function accepts a query and a list of parameters.
// We will use variable argument list.
// This allows us to use as many params as we want.
// No need to use any special syntax.
function dbQuery($query) {
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
        $statement->setFetchMode(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        prettyDie('Database error: ' . $e->getMessage());
    }
    return $statement;
}

/*
 * A few additional notes:
 * 
 * 1. Don't forget to call $statement->closeCursor() before a new call the execute().
 * 2. PDO also supports transactions.
 * 3. PDO also supports the fetchAll() method - it retrieves all the rows in one two-dimensional array.
 */
?>
