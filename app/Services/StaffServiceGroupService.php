<?php 
namespace NBAP\Services;
use NBAP\Db\DatabaseQuery;

class StaffServiceGroupService extends DatabaseQuery {
	protected string $table_name = NBAP_TB_STAFF_SERVICE_GROUP;
	private object $response;
	
	public function __construct(...$args) {
		parent::__construct($this->table_name);
		$this->response = nbap_object("NBAP\Helpers\Functions\ServiceResponse");		
	}
	
	public function get_by_staff_service_id($staff_service_id): ?object {
		return $this->db->get_row( "SELECT * from ".$this->db->prepare(" %1s WHERE staff_service_id=%s", $this->table_name, $staff_service_id) );
	}	
	
	public function add_update(object $model): object {		
		if(intval($model->id) == 0){
			$id = $this->insert($model->get_data());			
			if($id > 0) 
				$this->response->insert_success($id, __("Staff Service Group added successfully", "neo-book-and-pay-group"));
			else
				$this->response->error($this->db->last_error);
		} else {
			$id = $model->id;
			$updated = $this->update($model->get_data(), $id);
			if($updated === FALSE)
				$this->response->error($this->db->last_error);
			else
				$this->response->update_success($id, __("Staff Service Group updated successfully", "neo-book-and-pay-group"));
		}
		
		return $this->response;
	}
	
	public function remove(int $staff_service_id): object {
		$deleted = $this->delete_where(['staff_service_id' => $staff_service_id]);
		if($deleted === FALSE) 
			$this->response->error($this->db->last_error);
		elseif($deleted===0)
			$this->response->error("No such record found");
		else
			$this->response->delete_success($id, __("Staff Service Group deleted successfully", "neo-book-and-pay-group"));
			
		return $this->response;
	}
}