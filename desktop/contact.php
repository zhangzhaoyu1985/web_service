<?php

if($_SERVER['REQUEST_METHOD']=='POST'){
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $email = $_POST['email'];
  $message = $_POST['message'];
  $action = $_POST['action'];
  $to = "qing.li@tagtalk.co,s810434@gmail.com,s810011@gmail.com"; 
  $subject = $first_name . " " . $last_name . " (" . $email . ")";
  if ($action == "Request Demo") {
    $subject .= ' requested demo';
  } else {
    $subject .= ' contacted TagTalk';
  }
  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\r\n";
  $headers .= 'From: support@tagtalk.co' . "\r\n" .
  $headers .= 'Reply-To: support@tagtalk.co' . "\r\n" .
  $headers .= "Return-Path: support@tagtalk.co\r\n";
  mail($to, $subject, $message, $headers);
}
?>

