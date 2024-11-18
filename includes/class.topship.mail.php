<?php 
class Class_topship_mail{
    public static function send_html_email($to, $subject, $message) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    
        // Additional headers (optional)
        $headers .= "From: Your Name <your_email@example.com>" . "\r\n";
    
        // Send email
        if (mail($to, $subject, $message, $headers)) {
            return true; // Email sent successfully
        } else {
            return false; // Email sending failed
        }
    }
}