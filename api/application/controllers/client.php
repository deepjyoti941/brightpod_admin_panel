<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Client extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library(array('session'));
		$this->load->model(array('CI_auth', 'CI_encrypt'));
		$this->load->helper('file');
	}

	public function clients() {
		if ($this->CI_auth->check_logged()) {
			$query = $this->db->get_where('clients', array('active' => 1));
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
			$query = $this->db->get_where('clients', array('client_id' => $this->input->post('client_id')));
			$data = array(
				"status" => true,
				"data" => $query->row()
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
	
		$query = "SELECT * FROM clients as cl STRAIGHT_JOIN user_client_mapping AS cm WHERE cl.client_id = cm.client_id
		 					AND cm.last_login BETWEEN ? AND ? AND cl.active = 1";
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
			// mkdir('./backups/' .$this->input->post('client_id'), 0777, TRUE);
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