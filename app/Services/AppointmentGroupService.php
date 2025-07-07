<?php 
namespace NBAP\Services;
use NBAP\Db\DatabaseQuery;

class AppointmentGroupService extends DatabaseQuery {
	protected string $table_name = NBAP_TB_APPOINTMENT_GROUP;
	private object $response;
	
	public function __construct(...$args) {
		parent::__construct($this->table_name);
		$this->response = nbap_object("NBAP\Helpers\Functions\ServiceResponse");
	}
	
	public function add_update(object $model): object {
		$data = $model->get_data();		
		if(intval($model->id) == 0){
			$id = $this->insert($data);
			if($id > 0)				
				$this->response->insert_success($id, __("Appointment Group booked successfully", "neo-book-and-pay"));
			else
				$this->response->error($this->db->last_error);
		} else {
			$id = $model->id;
			$updated = $this->update($data, $id);
			if($updated === FALSE)
				$this->response->error($this->db->last_error);
			else
				$this->response->update_success($id, __("Appointment Group updated successfully", "neo-book-and-pay"));
		}
		return $this->response;
	}
	
	public function remove(int $id ): object {		
		$deleted = $this->delete($id);
		if($deleted === FALSE) 
			$this->response->error($this->db->last_error);
		elseif($deleted===0)
			$this->response->error("No such record found");
		else
			$this->response->delete_success($id, __("Appointment deleted successfully", "neo-book-and-pay"));
			
		return $this->response;
	}
}