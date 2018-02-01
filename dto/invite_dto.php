<?php

class invite_dto{
  public $id;
  public $admin;
  public $token;
  public $email;
  public $date_sent;
  public $date_used;

  public function __construct($admin, $email) {
    $this->admin = $admin;
    $this->email = $email;
    $this->token = uniqid();
  }
  
  function getInviteUrl() {
    return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . "/signup.php?token=" . $this->token;
   
  }

}