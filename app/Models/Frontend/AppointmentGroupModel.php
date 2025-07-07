<?php
namespace NBAP\Models\Frontend;
use NBAP\Models\FrontendModel;

class AppointmentGroupModel extends FrontendModel {
	
	public int $id = 0;
	public int $appointment_id = 0;
	public int $capacity = 1;
	public string $detail_data = '';
		
	public function __construct( $values = array() ){
		parent::__construct();
		$this->prepare_input( $values );
	}
}