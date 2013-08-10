/* 
 * This script handles the client side for image uploading and photo editing with ajax
 * and some other utilities for the upload page.
 */

// This variable holds a reference to the currently selected photo.
var selectedPhoto;
// This variable holds the number of successfully uploaded photos.
var uploaded = 0;
// This folder holds the number of failed photos.
var failed = 0;
// attach event handlers
$(document).ready(function() {
    // upload photos
    $('#up').submit(function(e) {
        e.preventDefault();
        $('#display').html('<hr/>');
        // retrieve data
        var album = $('#album').val();
        var category = $('#categoryAll').val();
        var files = $('#photos')[0].files;
        var size = 0;
        // get the size of all files
        for (var i = 0; i < files.length; i++) {
            size += files[i].size;
        }
        // reset variables
        uploaded = 0;
        failed = 0;
        var progress = (files.length > 4) ? '<br/><progress id="progress" value="0" max="' + files.length + '"></progress>' : '';
        // uploading...
        $('#upFeedback').html('Uploading <span id="number"></span>/' + files.length +
            ' <img src="images/popup-loading.gif" alt="..."/>' + progress);
        $('#up input, #up select').attr('disabled', 'disabled');
        // submit photos recursively
        submitPhoto(0, files, album, category);
    });
    // edit photo
    $('#editPic').submit(editPhoto);
    // delete photo
    $('#deletePic').click(deletePhoto);
    // check for album
    $('#toAlbum').change(checkAlbum);
    // select photo
    $('img.photo').live('click', function(e) {
        selectPhoto(this, e);
    });
    // drag & drop animation
    $('#photos')[0].addEventListener('dragenter', function(e) {
        $('#photosWrap').css('background-color', '#8ad459');
    }, false);
    $('#photos')[0].addEventListener('dragleave', function(e) {
        $('#photosWrap').css('background-color', 'rgba(255,183,183,0.75)');
    }, false);
    $('#photos')[0].addEventListener('drop', function(e) {
        $('#photosWrap').css('background-color', 'rgba(255,183,183,0.75)');
    }, false);
});

// check album title
function checkAlbum() {
    if ($('#new').attr('selected')) {
        $('#album').removeAttr('disabled').val('');
    } else {
        $('#album').attr('disabled', 'disabled').val($('#toAlbum option:selected').text());
    }
}

// submit photo with ajax
function submitPhoto(index, files, album, category) {
    // check if all photos have been submitted
    if (index >= files.length) {
        $('#up input, #up select').removeAttr('disabled');
        if ($('#toAlbum').html().search(album) < 0) {
            $('#new').after('<option>' + album + '</option>');
        }
        document.forms['up'].reset();
        checkAlbum();
        var yes = (uploaded) ? '<span class="success">Successfully uploaded: ' + uploaded + ' photos</span><br/>' : '';
        var no = (failed) ? '<span class="error">Failed to upload: ' + failed + ' photos</span><br/>' : '';
        $('#upFeedback').html(yes + no);
        return;
    }
    // retrieve data
    var data = new FormData();
    data.append('photo', files[index]);
    data.append('album', album);
    data.append('category', category);
    // create xhr object
//        var xhr = new XMLHttpRequest();
//        // before send handler
//        xhr.addEventListener('loadstart', function(e) {
//            $('#display').append('<img id="' + index + '" class="loading" src="images/load.gif" title="Uploading..." alt="Uploading..."/>');
//        }, false);
//        // success handler
//        xhr.addEventListener('load', function(e) {
//            // check for errors
//            if (e.target.responseText.slice(12, 17) == 'error') {
//                failed++;
//            } else {
//                uploaded++;
//            }
//            
//            $('img#' + index).replaceWith(e.target.responseText);
//        }, false);
//        // error handler
//        xhr.addEventListener('error', function(e) {
//            failed++;
//            $('img#' + index).replaceWith('<img class="error" src="images/error.png" title="Error" alt="Error"/>');
//        }, false);
//        // abort handler
//        xhr.addEventListener('abort', function(e) {
//            failed++;
//            $('img#' + index).replaceWith('<img class="error" src="images/error.png" title="Aborted" alt="Aborted"/>');
//        }, false);
//        // complete handler
//        xhr.addEventListener('loadend', function(e) {
//            submitPhoto(index + 1, files, album, category);
//        }, false);
//        // send xhr
//        xhr.open('POST', 'ajax/ajaxUpload.php', true);
//        xhr.send(data);
    // ajax
    $.ajax({
        url: 'ajax/ajaxUpload.php',
        type: 'POST',
        beforeSend: function() { // before send
            $('#display').append('<img id="'
                + index + '" class="loading" src="images/load.gif" title="Uploading '
                + files[index].name + '..." alt="Uploading..."/>');
            $('#number').text(index + 1);
        }, 
        success: function(data) { // success
            // check for errors
            if (data.search('error') >= 0) {
                failed++;
            } else {
                uploaded++;
            }
            
            $('img#' + index).replaceWith(data);
        }, 
        error: function() { // error
            failed++;
            $('img#' + index).replaceWith('<img class="error" src="images/error.png" title="Error uploading file '
                + files[index].name + '" alt="Error"/>');
        }, 
        complete: function() { // complete
            $('#progress').attr('value', index + 1);
            // asynchronous recursion
            submitPhoto(index + 1, files, album, category);
        }, 
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'html'
    });
}

// select photo
function selectPhoto(photo, e) {
    $('#editPicFeedback').html('');
    // unselect the currently selected photo
    if (selectedPhoto) {
        $(selectedPhoto).removeClass('selected');
        $('#category, #description').val('');
    }
    // unselecting a photo
    if (!photo) {
        $('#sidebar').hide();
        selectedPhoto = null;
        return;
    }
    // if clicking the selected photo, unselect it
    if ($(photo).attr('id') == $(selectedPhoto).attr('id')) {
        $('#sidebar').hide();
        selectedPhoto = null;
    } else { // else select a new photo
        // compute position
        var x = e.clientX + 20;
        var y = e.clientY + 20;
        if (x + $('#sidebar').width() > $(window).width() - 30) {
            x = e.clientX - $('#sidebar').width() - 20;
        }    
        if (y + $('#sidebar').height() > $(window).height() - 20) {
            y = e.clientY - $('#sidebar').height() - 20;
        }
        
        $('#sidebar').delay(500).css('top', y).css('left', x).show();
        selectedPhoto = photo;
        $(selectedPhoto).addClass('selected');
        $('#category').val($(selectedPhoto).attr('category'));
        $('#description').val($(selectedPhoto).attr('description'));
    }
}

// edit photo with ajax
function editPhoto(e) {
    // prevent default event
    e.preventDefault();
    // check if there is a photo selected
    if(!selectedPhoto) {
        $('#editPicFeedback').html('<span class="error">No photo selected</span>');
        return;
    }
    // retrieve data
    var profilePic = $('#profilePic')[0].checked ? 1 : 0;
    var albumCover = $('#albumCover')[0].checked ? 1 : 0;
    var src = $(selectedPhoto).attr('src');
    var pid = $(selectedPhoto).attr('id');
    var category = $.trim($('#category').val());
    var description = $.trim($('#description').val());
    // ajax
    $.ajax({
        url: 'ajax/ajaxEdit.php',
        type: 'POST',
        dataType: 'html',
        data: {
            savePic: 1,
            profilePic: profilePic,
            albumCover: albumCover,
            src: src,
            pid: pid,
            category: category,
            description: description
        }, 
        success: function(data) { // success
            $('#editPicFeedback').html(data);
            $(selectedPhoto).attr({
                category: category, 
                description: description, 
                title: $(selectedPhoto).attr('title').slice(0, 10) + ':[' + category + ']: ' + description
            });
        
            $('#profilePic').removeAttr('checked');
            $('#albumCover').removeAttr('checked');
            selectPhoto(null, null);
        }, 
        beforeSend: function(){ // before send
            $('#editPicFeedback').html('Submitting <img src="images/popup-loading.gif" alt="..."/>');
            $('#editPic input').attr('disabled', 'disabled');
        }, 
        complete: function() { // complete
            $('#editPic input').removeAttr('disabled');
        }, 
        error: function() { // error
            $('#editPicResult').html('<span class="error">Error</span>');
        }
    });
}

// delete photo with ajax
function deletePhoto() {
    // check if there is a photo selected
    if(!selectedPhoto) {
        $('#editPicFeedback').html('<span class="error">No photo selected</span>');
        return;
    }
    // retrieve ID
    var pid = $(selectedPhoto).attr('id');
    // ajax
    $.ajax({
        url: 'ajax/ajaxEdit.php',
        type: 'POST',
        dataType: 'html',
        data: {
            delPic: 1,
            pid: pid
        }, 
        success: function(data) { // success
            $('#sidebar').hide();
            $('#category, #description').val('');
            $('#profilePic').removeAttr('checked');
            $('#albumCover').removeAttr('checked');
            $('#editPicFeedback').html(data);
            $('img.photo#' + pid).remove();
            selectedPhoto = null;
        }, 
        beforeSend: function() { // before send
            $('#editPicFeedback').html('Submitting <img src="images/popup-loading.gif" alt="..." />');
            $('#editPic input').attr('disabled', 'disabled');
        }, 
        complete: function() { // complete
            $('#editPic input').removeAttr('disabled');
        }, 
        error: function() { // error
            $('#editPicFeedback').html('<span class="error">Error</span>');
        }
    });
}