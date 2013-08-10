/* 
 * This script handles the client side for registration with ajax
 * and some other utilities for the login page
 */

// attach event handlers
$(document).ready(function() {
    // register
    $('#regForm').submit(submitRegistration);
    // to check for password match
    $('#regPass, #regRepeat').bind('input', checkPass);
    // change the stylesheet
    $('#lights').change(function() {
        if ($(this)[0].checked) {
            $('#style').attr('href', 'css/light.min.css');
        } else {
            $('#style').attr('href', 'css/dark.min.css');
        }
    });
});

// create a modal popup
$(document).ready(function() {
    // Change these values to style your modal popup.
    var width = 800;
    var align = 'center';
    var top = 50;
    var padding = 10;
    var backgroundColor = '#FFFFFF';
    var borderColor = '#000000';
    var borderWeight = 3;
    var borderRadius = 10;
    var fadeOutTime = 300;
    var disableColor = '#666666';
    var disableOpacity = 40;
    var loadingImage = 'images/popup-loading.gif';
	 
    // This method initialises the modal popup.
    $('#forgotten').click(function(e) {
        e.preventDefault();
        modalPopup(
            align,
            top,
            width,
            padding,
            disableColor,
            disableOpacity,
            backgroundColor,
            borderColor,
            borderWeight,
            borderRadius,
            fadeOutTime,
            $(this).attr('href'),
            loadingImage );
    });	
	 
    // This method hides the popup when the escape key is pressed.
    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            closePopup(fadeOutTime);
        }
    });
});

// check if repeated password matches
function checkPass() {
    var p1 = $('#regPass')[0];
    var p2 = $('#regRepeat')[0];
    if (p1.value != p2.value) {
        p2.setCustomValidity('Please repeat your password.');
    } else {
        p2.setCustomValidity('');
    }
}

// submit registration with ajax
function submitRegistration(e) {
    // prevent default event
    e.preventDefault();
    // retrieve data
    var uname = $('#regUname').val();
    var email = $.trim($('#regEmail').val());
    var upass = $('#regPass').val();
    var ufirst = $.trim($('#regFname').val());
    var ulast = $.trim($('#regLname').val());
    // ajax
    $.ajax({
        url: 'ajax/register.php',
        type: 'POST',
        dataType: 'html',
        data: {
            register: 1,
            username: uname,
            email: email,
            password: upass,
            firstname: ufirst,
            lastname: ulast
        },
        beforeSend: function() { // before send
            $('#regFeedback').html('Submitting <img src="images/popup-loading.gif" alt="..."/>');
            $('#regForm input').attr('disabled', 'disabled');
        },
        success: function(data) { // success
            $('#regFeedback').html(data);
        },
        error: function() { // error
            $('#regFeedback').html('<span class="error">An error occured<br/>Please try again later</span>')
        },
        complete: function() { // complete
            $('#regForm')[0].reset();
            $('#regForm input').removeAttr('disabled');
        }
    });
}