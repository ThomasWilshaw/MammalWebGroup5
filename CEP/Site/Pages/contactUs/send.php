<?php 
if(isset($_POST['submit'])){
    $to = "t.w.wilshaw@durham.ac.uk"; // this is your Email address
    $from = $_POST['email']; // this is the sender's Email address
    $date = $_POST['date'];
    $name = $_POST['name'];
    $number = $_POST['number'];
    $subject = "Form submission";
    $subject2 = "Copy of your form submission";
    $message = $name . " wrote the following:" . "\n\n" . "Dear RT Projects" . "\n\n" . "I would be grateful if you could get back to me please.";
    $message2 = "Here is a copy of your message " . $name . "\n\n" . "Dear RT Projects" . "\n\n" . "I would be grateful if you could get back to me please.";

    $headers = "From:" . $from;
    $headers2 = "From:" . $to;
    mail($to,$subject,$message,$headers);
    mail($from,$subject2,$message2,$headers2); // sends a copy of the message to the sender
    echo "Mail Sent. Thank you " . $name . ", we will contact you shortly.";
    // You can also use header('Location: thank_you.php'); to redirect to another page.
    }
?>