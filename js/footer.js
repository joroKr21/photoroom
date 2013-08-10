/* 
 * This script contains functions for the contact popup page.
 * 
 * Note that we do not use event handlers for these functions.
 * Instead, inline scripting is used.
 * The arguments for this are provided in the contact.php page.
 */

// attach event handler to submit form with ajax
$('#contactForm').live('submit', submitContactForm);
// create modal popup
$(document).ready(function(){
    var width=800,
    align='center',
    top=50,
    padding=10,
    backgroundColor='#ffffff',
    borderColor='#000000',
    borderWeight=3,
    borderRadius=10,
    fadeOutTime=300,
    disableColor='#666666',
    disableOpacity=40,
    loadingImage='images/popup-loading.gif';
	 
    $('footer a, #conditions').not('#homepage, #humans').click(function(e){
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
            loadingImage);
    });	
	
    $(document).keyup(function(e){
        if(e.keyCode==27)closePopup(fadeOutTime);
    });
});
    
// submit contact form with ajax
function submitContactForm(e) {
    // prevent default event
    e.preventDefault();
    // retrieve data
    var name = $.trim($('#contactName').val());
    var email = $.trim($('#contactEmail').val());
    var subject = $.trim($('#contactSubject').val());
    var message = $.trim($('#contactMessage').val());
    var captcha = $.trim($('#code').val());
    // ajax
    $.ajax({
        url: "ajax/ajaxContact.php",
        type: 'POST',
        dataType: 'html',
        data: { // data
            contact: 1,
            name: name,
            email: email,
            subject: subject,
            message: message,
            captcha: captcha
        },
        beforeSend: function() { // before send
            $('#contactFeedback').html('Sending message <img src="images/popup-loading.gif" alt="..." />');
            $('#contactForm input, #contactForm textarea').attr('disabled', 'disabled');
        }, 
        success: function(data) { // success
            $('#contactFeedback').html(data);
            
            if (data.search('captcha') >= 0) {
                $('#refresh').trigger('click');
            } else if (data.search('success') >= 0) {
                $('#contactForm')[0].reset();
                setTimeout(function() {
                    closePopup(300);
                }, 2000);
            }
        }, 
        error: function() { // error
            $('#contactFeedback').html('<span class="error">An error has occured<br/>Please try again later</span>');
        }, 
        complete: function() { // complete
            $('#contactForm input, #contactForm textarea').removeAttr('disabled');
            charsRemaining();
        }
    });
}

// compute how many characters remain
function charsRemaining() {
    var max = 1000;
    var current = $('#contactMessage').val().length;
    var remaining = max - current;
    $('#characters').text(' [' + remaining + ' characters remaining]');
}

// refresh captcha image
function refreshCaptcha() {
    var d = new Date();
    $('#captcha').attr('src', 'ajax/captcha.php?' + d.getTime());
}