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
	
	public function get_data(int $staff_id): array {
		return $this->get_all("*", "staff_id=".$staff_id)['rows'];
	}
	
	public function get_services_frontend(array $staff_ids): array {
		$columns = array(
			'm.id'			=> 'id',
			's.id'			=> 'service_id',
			'm.staff_id'	=> 'staff_id',
			's.title'		=> 'title',
			'm.capacity_min'=> 'capacity_min',
			'm.capacity_max'=> 'capacity_max',
		);
		$data = $this->select($columns)
			->join(array("s" => NBAP_TB_SERVICE), "s.id = m.service_id")
			->where("m.staff_id","IN", $staff_ids)
			->where("s.visibility","=", 1)
			->order_by("s.position")
			->get_paged_data();
		return $data['rows'];
	}
	
	public function get_service_frontend(int $staff_id, int $service_id): array {
		$columns = array(
			'm.id'			=> 'id',
			's.id'			=> 'service_id',
			'm.staff_id'	=> 'staff_id',
			's.title'		=> 'title',
			'm.capacity_min'=> 'capacity_min',
			'm.capacity_max'=> 'capacity_max',
			's.slot_length' => 'slot_length',
			's.duration' 	=> 'duration',
			's.description' => 'description',
			's.min_time_before_booking' => 'min_time_before_booking',
			's.min_time_before_cancel' => 'min_time_before_cancel',
		);
		$query = $this->select($columns)
			->join(array("s" => NBAP_TB_SERVICE), "s.id = m.service_id");
		if($staff_id > 0)
			$query = $query->where("m.staff_id","=", $staff_id);
		if($service_id > 0)
			$query = $query->where("m.service_id","=", $service_id);
		return $query->get_paged_data()['rows'];
	}
}