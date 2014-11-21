<?php
class Clients extends CI_Model {

  function __construct() {
    parent::__construct();
    $this->load->database();

  }

  public function getClients() {
    return $this->db->get('clients');
  }

  public function clientById($client_id = NULL) {
    $query = "SELECT client_id, first_name, last_name, client_email, DATE_FORMAT(date_registration,'%b %d %Y') AS registration_date, plan_end_date, plan_id, active  FROM clients WHERE client_id = ?";
    return $this->db->query($query, array('client_id' => $client_id));  
  }

  public function inactiveClients($fromDate = NULL, $untillDate = NULL) {
    $query = "SELECT cl.client_id, cl.first_name, cl.last_name, cl.client_email, DATE_FORMAT(cl.date_registration,'%b %d %Y') AS registration_date, cl.plan_end_date, cl.plan_id, cl.active  FROM clients as cl STRAIGHT_JOIN user_client_mapping AS cm WHERE cl.client_id = cm.client_id
              AND cm.last_login BETWEEN ? AND ? AND cl.active = 0";
    return $this->db->query($query, array($fromDate, $untillDate));
  }

  public function disableClient($client_id = NULL) {
    $query = "UPDATE clients SET active = 0 WHERE client_id = ?";
    return $this->db->query($query, array($client_id));
  }

  public function enableClient($client_id = NULL) {
    $query = "UPDATE clients SET active = 1 WHERE client_id = ?";
    return $this->db->query($query, array($client_id));
  }

  public function extendTrial($days = NULL, $client_id = NULL) {
    $query = "UPDATE clients SET plan_end_date = DATE_ADD(plan_end_date, INTERVAL ? DAY) WHERE client_id = ?";
    return $this->db->query($query, array($days, $client_id));
  }
}
