<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Client extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library(array('session'));
		$this->load->model(array('CI_auth', 'CI_encrypt'));
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

}