<?php
class CI_auth extends CI_Model {

  function __construct() {

    parent::__construct();
    $this->load->library('session');
    $this->load->database();
    $this->load->helper('url');
    $this->load->model(array('CI_encrypt'));
  }

  function process_login($login_array_input = NULL) {

    if(!isset($login_array_input) OR count($login_array_input) != 2)
    return false;
    //set its variable
    $email = $login_array_input[0];
    $password = $login_array_input[1];
    // select data from database to check user exist or not?
    $query = $this->db->query("SELECT * FROM `admin` WHERE `email`= '".$email."' LIMIT 1");
    if ($query->num_rows() > 0) {
      $row = $query->row();
      $user_id = $row->id;
      $user_pass = $row->password;
      $user_salt = $row->salt;
      if($this->CI_encrypt->encryptUserPwd( $password,$user_salt) === $user_pass) {
        $data = array(
            'email'     => $email,
            'logged_user'=> $user_id,
            'logged_in' => TRUE
          );
        $this->session->set_userdata($data);
        return true;
      }
      return false;
    }
    return false;
  }

  function check_logged() {
    return ($this->session->userdata('logged_user'))?TRUE:FALSE;
  }


  function logged_id() {
    return ($this->check_logged())?$this->session->userdata('logged_user'):'';

  }
}

