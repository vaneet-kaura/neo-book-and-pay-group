<?php
	add_action('nbap_service_form_time_after', function ($model) {
		$template_path = NBAP_GRP_LOCATION_PATH.'/templates/backend/service_group_form.php';
		if(file_exists($template_path))
			include( $template_path );
	},5,1);
	
	add_action('nbap_service_model_save_after', function ($model) {
		$group_model = nbap_object( "NBAP\Models\Backend\ServiceGroupModel", []);
		$group_model->id = (int)$model->service_group_id;
		$group_model->service_id = (int)$model->id;
		$group_model->capacity_min = (int)$model->capacity_min;
		$group_model->capacity_max = (int)$model->capacity_max;
		$result = nbap_object( "NBAP\Services\ServiceGroupService" )->add_update($group_model);
		if( $result->is_error() ){
			nbap_object("NBAP\Helpers\Components\InfoMessage")->return_message( $result->get_message(), "error");
			exit;
		}
	},5,1);
	
	add_action('nbap_staff_form_service_heading', function ($model) {
		?>
		<div class="col"><?php nbap_object("NBAP\Helpers\Components\FormLabel")->render( __( "Min Capacity", "neo-book-and-pay-group" ), "", array("class" => "d-none d-lg-block"))?></div>
		<div class="col"><?php nbap_object("NBAP\Helpers\Components\FormLabel")->render( __( "Max Capacity", "neo-book-and-pay-group" ), "", array("class" => "d-none d-lg-block"))?></div>
		<?php 
	},5,1);
	
	add_action('nbap_staff_form_service_item', function ($service_id, $id_prefix, $idx, $existing_service,$service) {
		$capacity_min = 0;
		$capacity_max = 0;
		if(is_object($existing_service)){
			$service_obj = nbap_object( "NBAP\Services\StaffServiceGroupService" )->get_by_staff_service_id($existing_service->id);
			$capacity_min = is_object($service_obj)?$service_obj->capacity_min:$capacity_min;
			$capacity_max = is_object($service_obj)?$service_obj->capacity_max:$capacity_max;
		}
		if($service_id > 0 || $capacity_min==0){
			$service_obj = nbap_object( "NBAP\Services\ServiceGroupService" )->get_by_service_id($service_id);
			$capacity_min = is_object($service_obj)?$service_obj->capacity_min:$capacity_min;
			$capacity_max = is_object($service_obj)?$service_obj->capacity_max:$capacity_max;
		}
		?>
		<div class="col form-group mb-3 mb-md-0">
			<?php nbap_object("NBAP\Helpers\Components\FormLabel")->render( __( "Min Capacity", "neo-book-and-pay-group" ), $id_prefix."capacity_min", array("class" => "d-lg-none"))?>
			<?php nbap_object("NBAP\Helpers\Components\FormInputNumber")->render("services[".$idx."][capacity_min]", $capacity_min, array_merge((is_object($existing_service) ? array() : array("disabled" => "disabled")), array("id" => $id_prefix."capacity_min", "class" => "form-control no-spinner", "min" => 1)), array("required" => __("Please enter Capacity Min", "neo-book-and-pay-group"), "range" => __("Capacity min should be greater than or equals 1", "neo-book-and-pay-group"), "range-min" => 1))?>
			<?php nbap_object("NBAP\Helpers\Components\FormValidation")->render("services[".$idx."][capacity_min]")?>
		</div>
		<div class="col form-group mb-3 mb-md-0">
			<?php nbap_object("NBAP\Helpers\Components\FormLabel")->render( __( "Max Capacity", "neo-book-and-pay-group" ), $id_prefix."capacity_max", array("class" => "d-lg-none"))?>
			<?php nbap_object("NBAP\Helpers\Components\FormInputNumber")->render("services[".$idx."][capacity_max]", $capacity_max, array_merge((is_object($existing_service) ? array() : array("disabled" => "disabled")), array("id" => $id_prefix."capacity_max", "class" => "form-control no-spinner", "min" => 1)), array("required" => __("Please enter Capacity Max", "neo-book-and-pay-group"), "range" => __("Capacity max should be greater than or equals 1", "neo-book-and-pay-group"), "range-min" => 1))?>
			<?php nbap_object("NBAP\Helpers\Components\FormValidation")->render("services[".$idx."][capacity_max]")?>
		</div>
		
		<?php 
	},5,5);
	
	add_action('nbap_staff_model_save_after', function ($model) {
		if(count($model->services) > 0){
				$add_services = []; $remove_services = [];
				foreach($model->services as $tmp_service){
					$service = array("staff_id"=>$id,	
									 "id"=>isset($tmp_service['id'])?$tmp_service['id']:NULL,		
									 "price"=>isset($tmp_service['price'])?$tmp_service['price']:NULL,		
									 "deposit"=>isset($tmp_service['deposit'])?$tmp_service['deposit']:NULL,	
									 "service_id"=>isset($tmp_service['service_id'])?$tmp_service['service_id']:NULL,	
									 );
					
					if(isset($service['service_id']))
						array_push($add_services, $service);
					else if(isset($service['id']) && intval($service['id']) > 0) 
						array_push($remove_services, $service['id']);
				}
				
				$result = true;
				if($result !== FALSE && count($remove_services) > 0){
					$result = $this->staff_service_service->delete_ids($remove_services);
					if($result === FALSE)
						$this->response->error($this->db->last_error);					
				}
				
				if($result !== FALSE && count($add_services) > 0){
					$result = $this->staff_service_service->insert_batch($add_services);
					var_dump($add_services);exit;
					if($result === FALSE)
						$this->response->error($this->db->last_error);
				}				
			}
			
		$result = nbap_object( "NBAP\Services\StaffServiceGroupService" )->insert_batch();
		if( $result->is_error() ){
			nbap_object("NBAP\Helpers\Components\InfoMessage")->return_message( $result->get_message(), "error");
			exit;
		}
	},5,1);
	