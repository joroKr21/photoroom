/* 
 * This script handles the client side for editing albums and photos with ajax
 * and some other utilities for the edit page
 */

// This variable holds the currently selected photo
var selectedPhoto;
// attach event handlers
$(document).ready(function() {
    // edit album
    $('#albumEdit').submit(editAlbum);
    // edit photo
    $('#editPic').submit(editPhoto);
    // delete photo
    $('#deletePic').click(deletePhoto);
    // delete album
    $('#deleteAlbum').click(deleteAlbum);
    // select photo
    $('img.editable').click(function(e) {
        selectPhoto(this, e);
    });
});

// select photo
function selectPhoto(photo, e) {
    // clear feedback div
    $('#editPicFeedback').html('');
    // if there is a photo currently selected, unselect it
    if (selectedPhoto) {
        $(selectedPhoto).removeClass('selected');
        $('#category, #description').val('');
    }
    // unselecting a photo
    if (!photo || $(photo).attr('id') == $(selectedPhoto).attr('id')) {
        $('#sidebar').hide();
        selectedPhoto = null;
        return;
    } else { // else select a new photo
        // calculate position
        var x = e.clientX + 20;
        var y = e.clientY + 20;
        if (x + $('#sidebar').width() > $(window).width() - 30) {
            x = e.clientX - $('#sidebar').width() - 20;
        }  
        if (y + $('#sidebar').height() > $(window).height() - 20) {
            y = e.clientY - $('#sidebar').height() - 20;
        }
        
        $('#sidebar').css('top', y).css('left', x).show();
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
    // if there is no photo selected, show an error message
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
        beforeSend: function() { // before send
            $('#editPicFeedback').html('<img src="images/popup-loading.gif" alt="..." />');
            $('#editPic input, #editPic textarea').attr('disabled', 'disabled');
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
            // change album cover if necessary
            if (albumCover) {
                $('img.album').attr('src', src);
            }
            // unselect the photo
            selectPhoto(null, null);
        },
        error: function() { // error
            $('#editPicResult').html('<span class="error">Error</span>');
        },
        complete: function() { // complete
            $('#editPic input, #editPic textarea').removeAttr('disabled');
        }
    });
}

// delete photo with ajax
function deletePhoto() {
    // if there is no photo selected, show an error message
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
        beforeSend: function() { // before send
            $('#editPicFeedback').html('<img src="images/popup-loading.gif" alt="..." />');
            $('#editPic input, #editPic textarea').attr('disabled', 'disabled');
        }, 
        success: function() { // success
            $('#sidebar').hide();
            $('#category, #description').val('');
            $('#profilePic').removeAttr('checked');
            $('#albumCover').removeAttr('checked');
            $('img.photo#' + pid).remove();
            selectedPhoto = null;
            // count how many photos are left
            var counts = $('#count').html();
            counts = counts.match(/\d*/g);
            
            if (counts.length == 3) {
                counts[1]--;
                counts[2]--;
                
                if (counts[0] < counts[1]) {
                    $('#count').html('<span class="error">No more photos on this page</span>');
                } else {
                    $('#count').html('[' + counts[0] + '-' + counts[1] +  ' of ' + counts[2] + ' photos]');
                }
            } else {
                $('#count').html('[' + counts[0] + ' photos]');
            }
        },
        error: function() { // error
            $('#editPicFeedback').html('<span class="error">Error</span>');
        },
        complete: function() { // complete
            $('#editPic input, #editPic textarea').removeAttr('disabled');
        }
    });
}

// edit album with ajax
function editAlbum(e) {
    // prevent default event
    e.preventDefault();
    // retrieve data
    var aid = $('div.album').attr('id');
    var title = $.trim($('#title').val());
    var description = $.trim($('#albumDesc').val());
    var visibility = $('#public')[0].checked ? 1 : 0;
    // ajax
    $.ajax({
        url: 'ajax/ajaxEdit.php',
        type: 'POST',
        dataType: 'html',
        data: {
            saveAlbum: 1,
            aid: aid,
            title: title,
            description: description,
            visibility: visibility
        }, 
        beforeSend: function() { // before send
            $('#albumFeedback').html('Submitting <img src="images/popup-loading.gif" alt="..."/>');
            $('#albumEdit input, #albumEdit textarea').attr('disabled', 'disabled');
        }, 
        success: function(data) { // success
            $('#albumFeedback').html(data);
        }, 
        error: function() { // error
            $('#albumFeedback').html('<span class="error">An error occured<br/>Please try again later</span>');
        }, 
        complete: function() { // complete
            $('#albumEdit input, #albumEdit textarea').removeAttr('disabled');
        }
    });
}

// delete album with ajax
function deleteAlbum() {
    // confirm action
    if(confirm('Are you sure you want to delete this album?')) {
        // retrieve ID
        var aid = $('div.album').attr('id');
        // ajax
        $.ajax({
            url: 'ajax/ajaxEdit.php',
            type: 'POST',
            dataType: 'html',
            data: {
                delAlbum: 1,
                aid: aid
            },
            beforeSend: function() { // before send
                $('#albumFeedback').html('Submitting <img src="images/popup-loading.gif" alt="..."/>');
                $('#albumEdit input, #albumEdit textarea').attr('disabled', 'disabled');
            }, 
            success: function() { // success
                // go to the edit page
                window.location = 'edit.php';
                window.location.reload();
            },
            error: function() { // error
                $('#albumFeedback').html('<span class="error">An error occured<br/>Please try again later</span>');
                $('#albumEdit input, #albumEdit textarea').removeAttr('disabled');
            }
        });
    }
}