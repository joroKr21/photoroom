/* 
 * This script handles the client side for saving profile changes with ajax
 * and some other utilities for the profile page
 */

// attach event handlers
$(document).ready(function() {
    // profile changes
    $('#profileForm').submit(submitChanges);
    // to check for password match
    $('#newPass, #repeat').bind('input', checkPass);
    // delete account
    $('#delete').click(deleteAcc);
});

// check if repeated password matches
function checkPass() {
    var p1 = $('#newPass')[0];
    var p2 = $('#repeat')[0];
    if (p1.value != p2.value) {
        p2.setCustomValidity('Please repeat your password');
    } else {
        p2.setCustomValidity('');
    }
}

// delete account with ajax
function deleteAcc() {
    var upass = $('#pass').val();
    // if no password is entered
    if(upass === '') {
        $('#profileFeedback').html('<span class="error">Please enter your password</span>');
        return;
    } else {
        $('#profileFeedback').html('');
    }
    // confirm action
    if(confirm('Are you sure you want to close your account?')) {
        // ajax
        $.ajax({
            url: 'ajax/ajaxProfile.php',
            type: 'POST',
            dataType: 'html',
            data: {
                deleteAcc: 1,
                password: upass
            },
            beforeSend: function() { // before send
                $('#profileFeedback').html('Submitting <img src="images/popup-loading.gif" alt="..."/>');
                $('#profileForm input').attr('disabled', 'disabled');
            }, 
            success: function(data) { // success
                $('#profileFeedback').html(data);
                // if the account is successfully deleted, reload the page
                if (data.search('success') >= 0) {
                    window.location.reload();
                }
            },
            error: function() { // error
                $('#profileFeedback').html('<span class="error">An error occured<br/>Please try again later</span>');
                $('#profileForm input').removeAttr('disabled');
            }
        });
    }
}

// submit profile changes with ajax
function submitChanges(e) {
    // prevent default event
    e.preventDefault();
    // retrieve data
    var upass = $('#pass').val();
    var unew = $('#newPass').val();
    var email = $.trim($('#email').val());
    var ufirst = $.trim($('#fname').val());
    var ulast = $.trim($('#lname').val());
    // ajax
    $.ajax({
        url: 'ajax/ajaxProfile.php',
        type: 'POST',
        dataType: 'html',
        data: {
            save: 1,
            password: upass,
            newPass: unew,
            email: email,
            firstname: ufirst,
            lastname: ulast
        },
        beforeSend: function() { // before send
            $('#profileFeedback').html('Submitting <img src="images/popup-loading.gif" alt="..."/>');
            $('#profileForm input').attr('disabled', 'disabled');
        },
        success: function(data) { // success
            $('#profileFeedback').html(data);
        }, 
        error: function() { // error
            $('#profileFeedback').html('<span class="error">An error occured<br/>Please try again later</span>');
        },
        complete: function() { // complete
            $('#profileForm input').removeAttr('disabled');
        }
    });
}