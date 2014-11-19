<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account extends CI_Controller {

  function __construct() {
    parent::__construct();
    $this->load->library(array('session', 'recaptcha'));
    $this->load->model(array('CI_auth', 'CI_encrypt', 'Logs'));
  }

  public function index() {


  }

  public function login() {

    if ($this->input->post('email') !== FALSE && $this->input->post('password') !== FALSE && $this->input->post('captcha') !== FALSE) {
      
      $captcha = $this->input->post('captcha');
      $this->recaptcha->recaptcha_check_answer($_SERVER['REMOTE_ADDR'], $captcha['challenge'], $captcha['response']);
      
      if ($this->recaptcha->getIsValid()) {
        
        $login_array = array($this->input->post('email'), $this->input->post('password'));

        if($this->CI_auth->process_login($login_array)) {
  
          $login_log_array = array($_SERVER['REMOTE_ADDR'], $this->session->userdata('email'), 'LOGIN');
          $this->Logs->createLoginLog($login_log_array);
          $data = array(
            "status" => true,
            "message" => 'Login Successfull! Welcome',
            "session_id" => $this->session->userdata('session_id')
            );
            echo json_encode($data);

          }else {
            if(isset($_COOKIE['login'])) {
              if($_COOKIE['login'] < 1) {
                $attempts = $_COOKIE['login'] + 1;
                setcookie('login', $attempts, time()+60*10); //set the cookie for 10 minutes with the number of attempts stored

              }else {
                $this->blockIp();
                exit;
              }
            }else {
              setcookie('login', 1, time()+60*30); //set the cookie for 10 minutes with the initial value of 1

            }

            $data = array(
              "status" => false,
              "message" => 'Invalid email or password'
              );
              echo json_encode($data);
            }

        }else {
          $data = array(
            "status" => false,
            "message" => 'error in captcha'
            );
            echo json_encode($data);                
          }
    } else {
      $data = array(
        "status" => false,
        "message" => 'Missing username or password or captcha'
        );
      echo json_encode($data);
    }
  }

  

  public function checkLoggedIn() {
    echo $this->CI_auth->check_logged();
  }

  public function logout() {
    $user_data = $this->session->all_userdata();
    foreach ($user_data as $key => $value) {
        if ($key != 'session_id' && $key != 'ip_address' && $key != 'user_agent' && $key != 'last_activity') {
            $this->session->unset_userdata($key);
        }
    }
    $this->session->sess_destroy(); 
  }

  public function blockIp() {

    $ip = $_SERVER['REMOTE_ADDR'];
    $htaccess = $_SERVER['DOCUMENT_ROOT'].'/.htaccess';
    $htaccess_api = '.htaccess';

    $contents = file_get_contents($htaccess, TRUE) OR exit('Unable to open .htaccess');
    $contents_api = file_get_contents($htaccess_api, TRUE) OR exit('Unable to open .htaccess');
    $exists = !stripos($contents, 'deny from ' . $ip . "\n") OR exit('Already banned, nothing to do here.');
    $exists_api_baned = !stripos($contents_api, 'deny from ' . $ip . "\n") OR exit('Already banned, nothing to do here.');

    $date   = date('Y-m-d H:i:s');
    $uri    = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES);
    $agent  = htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES);
    $agent  = str_replace(array("\n", "\r"), '', $agent);
    $email  = 'deepjyoti941@gmail.com';

    $whitelist = array(
      // '123.123.123.123',
      // '123.123.123.123',
      // '123.123.123.123',
    );
     
    if (in_array($ip, $whitelist)) {
      echo "Hello user! Because your IP address ({$ip}) is in our whitelist, you were not banned for attempting to visit this page. End of line.";
   
    } else {
      $ban =  "\n# The IP below was banned on $date for trying to access {$uri}\n";
      $ban .= "# Agent: {$agent}\n";
      $ban .= "Deny from {$ip}\n";
   
      file_put_contents($htaccess, $ban, FILE_APPEND) OR exit('Cannot append rule to .htaccess');
      
      file_put_contents($htaccess_api, $ban, FILE_APPEND) OR exit('Cannot append rule to .htaccess');

      if (!empty($email)) {
        $message = "IP Address: {$ip}\n";
        $message .= "Date/Time: {$date}\n";
        $message .= "User Agent: {$agent}\n";
        $message .= "URL: {$uri}";

        $this->load->library('email');

            $config['protocol']    = 'smtp';

            $config['smtp_host']    = 'ssl://smtp.gmail.com';

            $config['smtp_port']    = '465';

            $config['smtp_timeout'] = '7';

            $config['smtp_user']    = 'avirajsaikia@gmail.com';

            $config['smtp_pass']    = '';

            $config['charset']    = 'utf-8';

            $config['newline']    = "\r\n";

            $config['mailtype'] = 'text'; // or html

            $config['validation'] = TRUE; // bool whether to validate email or not      

            $this->email->initialize($config);


            $this->email->from('avirajsaikia@gmail.com', 'Aviraj Saikia');
            $this->email->to($email); 


            $this->email->subject('Anonymous login attempts to Brightpod Admin panel ');

            $this->email->message($message);  

            $this->email->send();
        // $this->email->from('avirajsaikia@gmail.com', 'Deepjyoti Khakhlary');
        // $this->email->to($email); 
        // $this->email->subject('Anonymous login attempts to Brightpod Admin panel ');
        // $this->email->message($message); 
        // $this->email->send();
     
      }
      $data = array(
      "status" => false,
      "message" => 'Your ip has been blocked'
      );
      echo json_encode($data);
    }
  }

/*
SELECT `group_id`,`user_id`, DATE_FORMAT(`last_login`,'%Y-%m-%d') as login_last FROM `user_client_mapping`
*/

}

/* End of file account.php */
/* Location: ./application/controllers/account.php */
