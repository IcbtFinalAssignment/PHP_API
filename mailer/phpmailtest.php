<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Set the SMTP server details
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'sasitechsolutions@gmail.com';
    $mail->Password   = 'P56BSEMnJkbspH9A';
    $mail->SMTPSecure = 'tls'; // Use 'tls' or 'ssl' based on your server configuration
    $mail->Port       = 587;

    // Set sender and recipient
    $mail->setFrom('info@bodima.top', 'top bodima');
    $mail->addAddress('sasithachamith@gmail.com', 'Recipient Name');

    // Set email subject and body
    $mail->Subject = 'Test Email';
    $mail->Body    = 'This is a test email sent using PHPMailer with Brevo SMTP server.';

    // Send the email
    $mail->send();

    echo 'Email sent successfully';
} catch (Exception $e) {
    echo "Error: {$mail->ErrorInfo}";
}
