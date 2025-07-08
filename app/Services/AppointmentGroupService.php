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
	
	public function get_data(array $appointment_ids) : array {
		if(count($appointment_ids) == 0) return [];
		$columns = array(
			'm.appointment_id'	=> 'appointment_id',			
			'm.capacity'		=> 'capacity',			
		);
		$data = $this->select($columns)
			->where("appointment_id","IN", $appointment_ids)
			->get_paged_data();
		return $data['rows'];		
	}
	
	public function add_update(object $model): object {
		$data = $model->get_data();		
		if(intval($model->id) == 0){
			$id = $this->insert($data);
			if($id > 0)				
				$this->response->insert_success($id, __("Appointment Group booked successfully", "neo-book-and-pay-group"));
			else
				$this->response->error($this->db->last_error);
		} else {
			$id = $model->id;
			$updated = $this->update($data, $id);
			if($updated === FALSE)
				$this->response->error($this->db->last_error);
			else
				$this->response->update_success($id, __("Appointment Group updated successfully", "neo-book-and-pay-group"));
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
			$this->response->delete_success($id, __("Appointment deleted successfully", "neo-book-and-pay-group"));
			
		return $this->response;
	}
}