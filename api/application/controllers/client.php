<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Client extends CI_Controller {

  function __construct() {
    parent::__construct();
    $this->load->library(array('session'));
    $this->load->model(array('CI_auth', 'CI_encrypt', 'Logs'));
    $this->load->helper('file');
  }

  public function clients() {
    if ($this->CI_auth->check_logged()) {
      $query = $this->db->get('clients');
      $data = array(
        "status" => true,
        "data" => $query->result()
      );
      echo json_encode($data);
    }else {
      $data = array(
        "status" => false,
        "message" => 'You must login'
      );
      echo json_encode($data);
    }
  }

  public function clientsDetailsById() {
    if ($this->CI_auth->check_logged()) {
      $query = "SELECT client_id, first_name, last_name, client_email, DATE_FORMAT(date_registration,'%b %d %Y') AS registration_date, plan_end_date, plan_id, active  FROM clients WHERE client_id = ?";
      $res = $this->db->query($query, array('client_id' => $this->input->post('client_id')));
      $data = array(
        "status" => true,
        "data" => $res->row()
      );
      echo json_encode($data);
    }else {
      $data = array(
        "status" => false,
        "message" => 'You must login'
      );
      echo json_encode($data);
    }
  }

  public function inactiveClientsByDate() {
  
    $query = "SELECT cl.client_id, cl.first_name, cl.last_name, cl.client_email, DATE_FORMAT(cl.date_registration,'%b %d %Y') AS registration_date, cl.plan_end_date, cl.plan_id, cl.active  FROM clients as cl STRAIGHT_JOIN user_client_mapping AS cm WHERE cl.client_id = cm.client_id
              AND cm.last_login BETWEEN ? AND ? AND cl.active = 0";
    $res = $this->db->query($query, array($this->input->post('fromDate'), $this->input->post('untilDate')));
    //'2014-11-01', '2014-11-03'
    $data = array(
      "status" => true,
      "data" => $res->result()
    );
    echo json_encode($data);
  }

  public function disableClient() {
    if ($this->CI_auth->check_logged()) {
      $query = "UPDATE clients SET active = 0 WHERE client_id = ?";
      $res = $this->db->query($query, array($this->input->post('client_id')));
      if ($res == 1) {
        $db_log_array = array($_SERVER['REMOTE_ADDR'], $this->session->userdata('email'), 'DISABLED CLIENT', $this->input->post('client_firstname').' '.$this->input->post('client_lastname'));
        $this->Logs->createDbLog($db_log_array);
        $data = array(
          "status" => true,
          "message" => 'Client Disabled Successfully'
        );
        echo json_encode($data);
      }
    }else {
      $data = array(
        "status" => false,
        "message" => 'You must login'
      );
      echo json_encode($data);
    }
  }

// if it is anything else and 0000-00-00 that means he is a paid customer

// is_in_trial for Trial customer

// 1 = is in trial

// 0 for not in trial combined with 6 or anything else logic i mention above

  public function enableClient() {
    if ($this->CI_auth->check_logged()) {
      $query = "UPDATE clients SET active = 1 WHERE client_id = ?";
      $res = $this->db->query($query, array($this->input->post('client_id')));
      if ($res == 1) {
        $db_log_array = array($_SERVER['REMOTE_ADDR'], $this->session->userdata('email'), 'ENABLED CLIENT', $this->input->post('client_firstname').' '.$this->input->post('client_lastname'));
        $this->Logs->createDbLog($db_log_array);
        $data = array(
          "status" => true,
          "message" => 'Client Enabled Successfully'
        );
        echo json_encode($data);
      }
    }else {
      $data = array(
        "status" => false,
        "message" => 'You must login'
      );
      echo json_encode($data);
    }
  }

  public function deleteClient() {
    if ($this->CI_auth->check_logged()) {
      mkdir($_SERVER['DOCUMENT_ROOT'].'/backups/' .$this->input->post('client_id'), 0777, TRUE);
      mkdir($_SERVER['DOCUMENT_ROOT'].'/backups/' .$this->input->post('client_id').'/full_backup', 0777, TRUE);

      $dir = $_SERVER['DOCUMENT_ROOT'].'/backups/' .$this->input->post('client_id').'/';
      $full_backup = $dir.'full_backup/full_back_up.sql';
      $tables = array("clients", "attachments", "comments", "companies", "editorial", "events", "focus", "groups", "history", "messages", "milestones", "priority", "projects", "project_emails", "project_user_mapping", "roundup", "tasks", "task_lists", "task_notifications", "times", "transaction", "user_options");

      foreach ($tables as &$table) {
        $command = "C:\wamp\bin\mysql\mysql5.6.17\bin\mysqldump.exe --no-create-info --host=".$this->db->hostname." --user=".$this->db->username." --password=".$this->db->password." ".$this->db->database." ".$table." --where=client_id=".$this->input->post('client_id')." > ".$dir."$table".".sql";
        //$command = "mysqldump --no-create-info --host=".$this->db->hostname." --user=".$this->db->username." --password=".$this->db->password." ".$this->db->database." ".$table." --where=client_id=".$this->input->post('client_id')." > ".$dir."$table".".sql";
        
        $result = exec($command);
      }

      foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $filename) {
        write_file($full_backup, file_get_contents($filename));
      }

      foreach ($tables as &$table) {
        $this->db->where('client_id', $this->input->post('client_id'));
        $this->db->delete($table); 
      }

      $db_log_array = array($_SERVER['REMOTE_ADDR'], $this->session->userdata('email'), 'REMOVED CLIENT', $this->input->post('client_firstname').' '.$this->input->post('client_lastname'));
      $this->Logs->createDbLog($db_log_array);

    }else {
      $data = array(
        "status" => false,
        "message" => 'You must login'
      );
      echo json_encode($data);
    }
  }

  public function rollBackClient() {
    if ($this->CI_auth->check_logged()) {

    }else {
      $data = array(
        "status" => false,
        "message" => 'You must login'
      );
      echo json_encode($data);
    } 
  }

  public function extendTrial() {
    if ($this->CI_auth->check_logged()) {
      $query = "UPDATE clients SET plan_end_date = DATE_ADD(plan_end_date, INTERVAL ? DAY) WHERE client_id = ?";
      $res = $this->db->query($query, array($this->input->post('days'), $this->input->post('client_id')));
      if ($res == 1) {
        $data = array(
          "status" => true,
          "message" => 'Trial Period Extended Successfully'
        );
        echo json_encode($data);
      }
    }else {
      $data = array(
        "status" => false,
        "message" => 'You must login'
      );
      echo json_encode($data);
    } 
  }
}