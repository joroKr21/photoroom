<?php
/*
 * This is the upload page.
 */
session_start();
require_once 'core.inc.php';
require_once DB_PATH . '/db.php';
require_once INCLUDE_PATH . '/categories.inc.php';
$_SESSION['location'] = $_SERVER['REQUEST_URI'];
$css .= 'main.css,upload.css';
$js .= 'jquery-min.js,modal-popup.js,footer.js,upload.js';
// only allow registered users to upload images
if (isset($_SESSION['uid'])) {
    include INCLUDE_PATH . '/header.inc.php';
    ?>
    <!--Context menu-->
    <aside id="sidebar">
        <form id="editPic" name="editPic" method="POST" action="ajax/ajaxEdit.php">
            <label for="category">Category:</label><br/>
            <select id="category" name="category"><?= listCategories(); ?></select><br/>
            <label for="description">Description:</label><br/>
            <textarea id="description" name="description" rows="3" placeholder="Description"></textarea><br/>
            <input type="checkbox" id="profilePic" name="profilePic"/>
            <label for="profilePic">Profile picture:</label><br/>
            <input type="checkbox" id="albumCover" name="albumCover"/>
            <label for="albumCover">Album cover:</label><br/>
            <input type="button" id="deletePic" name="deletePic" value="Delete"/>
            <input type="submit" id="savePic" name="savePic" value="Save"/>
            <div class="feedback" id="editPicFeedback"></div>
        </form>
    </aside>
    <!--Main content-->
    <section id="content">
        <h1>Upload Photos</h1>
        <!--Upload form-->
        <form enctype="multipart/form-data" id="up" name="up" method="POST" action="ajax/ajaxUpload.php">
            <div id="photosWrap"><input name="photos[]" id="photos" type="file" multiple="multiple" accept="image/*" required="required"/></div>
            <input type="hidden" name="MAX_FILE_SIZE" value="10485760"/><br/>
            <label for="toAlbum">To Album</label>   
            <input type="text" id="album" name="album" required="required" maxlength="32" pattern="^.*\S.*$" autocomplete="on" list="albumList" placeholder="Create new or choose  ->"/>
            <datalist id="albumList"><? listAlbums($_SESSION['uid']); ?></datalist>
            <select id="toAlbum" name="toAlbum">
                <option id="new" selected="selected"></option>
                <? listAlbums($_SESSION['uid']); ?>
            </select><br/>
            <label for="categoryAll">Category</label>
            <select id="categoryAll" name="categoryAll"><?= listCategories(); ?></select>
            <input type="submit" id="upload" name="upload" value="Upload"/>
            <span class="small">Max. file size: 10 MB</span><br/>
        </form>
        <div id="upFeedback" class="feedback"></div>
        <div id="display"><!--Display uploaded photos here--></div>
    </section>
    <?php
    include INCLUDE_PATH . '/footer.inc.php';
} else { // if the user is not logged in, redirect to login page
    header('Location: login.php');
    exit;
}

// list albums by user ID as select options
function listAlbums($uid) {
    // SELECT query
    $asel = "SELECT title FROM albums WHERE author_id=? ORDER BY title COLLATE utf8_unicode_ci";
    $asel = dbQuery($asel, $uid);
    $aresult = $asel->fetchAll();
    // display options
    foreach ($aresult as $arow) {
        $atitle = htmlentities($arow['title']);
        echo "<option>$atitle</option>";
    }
}
?>