<?php
/*
 * This is the standard header for every page.
 * It includes the logo and menu of the web site.
 * It also includes basic javascript and css files.
 *
 * TODO: extend keywords
 */
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>PhotoRoom</title>
        <meta name="description" content="PhotoRoom is an online photo gallery where you can easily upload and manage your photos,
              organize them in albums, share them with your friends and family or simply search and view the photos of other users."/>
        <!-- Needs extension -->
        <meta name="keywords" content="photo, image, picture, pic, gallery, album, room"/>
        <meta name="author" content="Georgi Krastev"/>
        <link rel="author" href="humans.txt"/>
        <link rel="shortcut icon" href="images/photo-icon.ico"/>
        <link rel="stylesheet" type="text/css" href="<?= $css; ?>"/>
        <script type="text/javascript" src="<?= $js; ?>"></script>
        <?php // determine the selected color scheme
        if (isset($_SESSION['style'])) {
            echo '<link id="style" rel="stylesheet" type="text/css" href="css/' . $_SESSION['style'] . '.min.css"/>';
        } else {
            echo '<link id="style" rel="stylesheet" type="text/css" href="css/dark.min.css"/>';
        }
        ?>
    </head><?php flush(); ?>
    <body>
        <!-- Big wrapper to center the site -->
        <div id="center">
            <!-- Header -->
            <header id="header">
                <!-- Logo -->
                <a id="home" href="index.php">
                    <img id="logo" src="images/logo.png" alt="PhotoRoom" title="Home"/>
                    <img id="logoGlow" src="images/logoGlow.png" alt="PhotoRoom" title="Home"/>
                </a>
                <?php // if the user is logged in, display logout link
                if (isset($_SESSION['uid'])) {
                    echo '<a id="logout" href="login.php?logout=1">Logout</a>';
                    // if the user is an admin, display link to admin page
                    if ($_SESSION['level'] > 0) {
                        echo '<a id="admin" href="admin.php">Admin</a>';
                    }
                } else {
                    echo '<a id="logout" href="login.php">Login | Register</a>';
                }
                ?><!-- Menu -->
                <nav id="menu">
                    <ul>
                        <li><a id="upload" href="upload.php">Upload</a></li>
                        <li><a id="search" href="search.php">Search</a></li>
                        <li><a id="view" href="view.php">View</a></li>
                        <li><a id="edit" href="edit.php">Edit</a></li>
                        <li><a id="profile" href="profile.php">Profile</a></li>
                    </ul>
                </nav>
            </header><hr/>
            <script type="text/javascript">
                //<!--
                $(document).ready(function() {
                    $('#home').mouseenter(function(){
                        $('#logo, #logoGlow').toggle();
                    }).mouseleave(function(){
                        $('#logo, #logoGlow').toggle();
                    });
                });//-->
            </script>