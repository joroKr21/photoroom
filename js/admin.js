/* 
 * This script handles ajax administration activities.
 */
$(document).ready(function(){
    $('#clean').click(function(){
        $('#adminFeedback').html('Cleaning <img src="images/popup-loading.gif" alt="..."/>');
        $('#query input, #backupForm input').attr('disabled', 'disabled');
        $('#adminFeedback').load('ajax/clean.php', {}, function(){
            $('#query input, #backupForm input').removeAttr('disabled');
        }).error(function(){
            $('#adminFeedback').html('<span class="error">Something went wrong</span>');
        });
    });
    
    $('#query').submit(function(e){
        e.preventDefault();
        var sql = $('#sql').val();
        $('#adminFeedback').html('Waiting <img src="images/popup-loading.gif" alt="..."/>');
        $('#query input, #backupForm input').attr('disabled', 'disabled');
        // POST AJAX request
        $.post('ajax/query.php', {
            go: 1,
            sql: sql
        }, function(data){
            $('#adminFeedback').html('<hr/>' + data);
            $('#query input, #backupForm input').removeAttr('disabled');
        }, 'html').error(function(){
            $('#adminFeedback').html('<span class="error">Something went wrong</span>');
        });
    });
    
    $('#backupForm').submit(function (e) {
        e.preventDefault();
        var tables = $('#tables').val();
        $('#adminFeedback').html('Backing up <img src="images/popup-loading.gif" alt="..."/>');
        $('#query input, #backupForm input').attr('disabled', 'disabled');
        // POST AJAX request
        $.post('ajax/backupDB.php', {
            backup: 1,
            tables: tables ? tables : '*'
        }, function(data){
            $('#adminFeedback').html(data);
            $('#query input, #backupForm input').removeAttr('disabled');
        }, 'html').error(function(){
            $('#adminFeedback').html('<span class="error">Something went wrong</span>');
        });
    });
});