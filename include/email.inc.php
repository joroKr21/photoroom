<?php

/*
 * function to send an email
 * NOTE: Must include the phpmailer class
 */

function email($fromAddr, $fromName, $toAddr, $toName, $html, $message = '', $subject = '', $alt = '') {
    $mail = new PHPMailer();
    $mail->IsSMTP(); // send via SMTP
    $mail->Host = EMAIL_HOST; // SSL host
    $mail->Port = EMAIL_PORT; // SMTP port
    $mail->SMTPAuth = true; // turn on SMTP authentication
    $mail->Username = EMAIL_ADDR; // SMTP username
    $mail->Password = EMAIL_PASS; // SMTP password
    $mail->From = $fromAddr; // send from this address
    $mail->FromName = $fromName; // send from this name
    $mail->AddAddress($toAddr, $toName); // send to this address
    $mail->AddReplyTo($fromAddr, $fromName); // reply to this address
    $mail->WordWrap = 70; // set word wrap
    $mail->IsHTML($html); // send as HTML or as plain text
    $mail->Subject = $subject; // subject
    $mail->Body = $message; // HTML Body
    $mail->AltBody = $html ? $alt : ''; // text Body
    // send the mail
    if (!$mail->Send()) {
        return false;
    } else {
        return true;
    }
}

?>
