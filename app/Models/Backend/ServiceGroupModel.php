<?php
namespace NBAP\Models\Backend;
use NBAP\Models\BackendModel;
use NBAP\Helpers\Components\Grid as Grid;
use NBAP\Helpers\Functions\Format as Format;
use NBAP\Helpers\Functions\Filter as Filter;


class ServiceGroupModel extends BackendModel {
	public int $id = 0;
	public int $service_id = 0;
	public int $capacity_min = 0;
	public int $capacity_max = 0;
	
	public function __construct( $values = array() ) {			
		parent::__construct();		

		$this->prepare_input( $values );
	}
}