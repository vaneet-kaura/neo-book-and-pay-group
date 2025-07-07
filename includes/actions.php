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
	
	add_action('nbap_staff_form_service_item', function ($model, $id_prefix, $idx, $service_id, $service, $existing_service) {
		
		$existing_service_group = array_values(array_filter($model->service_groups, fn($item) => $item->service_id == $service_id));
		$existing_service_group = $existing_service_group[0] ?? null;
		
		$capacity_min = 0; $capacity_max = 0;
		if(is_object($existing_service_group)) {
			$capacity_min = $existing_service_group->capacity_min;
			$capacity_max = $existing_service_group->capacity_max;
		} else {
			$service_group_obj = nbap_object( "NBAP\Services\ServiceGroupService" )->get_by_service_id($service_id);
			if(is_object($service_group_obj)) {
				$capacity_min = $service_group_obj->capacity_min;
				$capacity_max = $service_group_obj->capacity_max;
			}
		}
		?>
		<div class="col form-group mb-3">
			<?php nbap_object("NBAP\Helpers\Components\FormLabel")->render( __( "Min Capacity", "neo-book-and-pay-group" ), $id_prefix."capacity_min", array("class" => "d-lg-none"))?>
			<?php nbap_object("NBAP\Helpers\Components\FormInputNumber")->render("services[".$idx."][capacity_min]", $capacity_min, array_merge((is_object($existing_service) ? array() : array("disabled" => "disabled")), array("id" => $id_prefix."capacity_min", "class" => "form-control no-spinner", "min" => 1)), array("required" => __("Please enter Capacity Min", "neo-book-and-pay-group"), "range" => __("Capacity min should be greater than or equals 1", "neo-book-and-pay-group"), "range-min" => 1))?>
			<?php nbap_object("NBAP\Helpers\Components\FormValidation")->render("services[".$idx."][capacity_min]")?>
		</div>
		<div class="col form-group mb-3">
			<?php nbap_object("NBAP\Helpers\Components\FormLabel")->render( __( "Max Capacity", "neo-book-and-pay-group" ), $id_prefix."capacity_max", array("class" => "d-lg-none"))?>
			<?php nbap_object("NBAP\Helpers\Components\FormInputNumber")->render("services[".$idx."][capacity_max]", $capacity_max, array_merge((is_object($existing_service) ? array() : array("disabled" => "disabled")), array("id" => $id_prefix."capacity_max", "class" => "form-control no-spinner", "min" => 1)), array("required" => __("Please enter Capacity Max", "neo-book-and-pay-group"), "range" => __("Capacity max should be greater than or equals 1", "neo-book-and-pay-group"), "range-min" => 1))?>
			<?php nbap_object("NBAP\Helpers\Components\FormValidation")->render("services[".$idx."][capacity_max]")?>
		</div>
		
		<?php 
	},5,6);
	
	
	add_action('nbap_staff_model_save_after', function ($model) {
		if(count($model->services) > 0){
			$staff_service_service_group = nbap_object("NBAP\Services\StaffServiceGroupService");
			$add_services = []; $remove_services = [];
			foreach($model->services as $tmp_service){
				if(isset($tmp_service['service_id']) && isset($tmp_service['capacity_min']) && isset($tmp_service['capacity_max'])) {
					if(intval($tmp_service['capacity_min']) > intval($tmp_service['capacity_max'])) {
						nbap_object( "NBAP\Helpers\Components\InfoMessage" )->return_message(__( 'Max capacity should be greater than min capacity', 'neo-book-and-pay-group' ), "error" );						
						return;
					}
					array_push($add_services, array("id" => isset($tmp_service['id'])?$tmp_service['id']:0,
						"staff_id" => $model->id,
						"service_id" => $tmp_service['service_id'],
						"capacity_min" => $tmp_service['capacity_min'],
						"capacity_max" => $tmp_service['capacity_max']
					));
				} else if(isset($tmp_service['id']) && intval($tmp_service['id']) > 0) 
					array_push($remove_services, $tmp_service['id']);
			}
			
			$result = true;
			if($result !== FALSE && count($remove_services) > 0){
				$result = $staff_service_service_group->delete_ids($remove_services);
				if($result === FALSE)
					$this->response->error($this->db->last_error);					
			}
			
			if($result !== FALSE && count($add_services) > 0){
				$result = $staff_service_service_group->insert_batch($add_services);
				if($result === FALSE)
					$this->response->error($this->db->last_error);
			}
		}
	},5,1);
	
	add_action('nbap_booking_form_after_staff', function ($model) {
		$min_capacity = min(array_column($model->view_bag->staff_service_groups, "capacity_min"));
		$max_capacity = max(array_column($model->view_bag->staff_service_groups, "capacity_max"));		
		?>
		<div class="col form-group mb-3">
			<?php nbap_object("NBAP\Helpers\Components\FormLabel")->renderModel($model,"capacity", array("class" => "fw-bold"))?>
			<?php nbap_object("NBAP\Helpers\Components\FormSelect")->renderModel($model,"capacity", range($min_capacity, $max_capacity), "")?>
			<?php nbap_object("NBAP\Helpers\Components\FormValidation")->renderModel($model,"capacity")?>
		</div>
		<?php
	},5,1);
	
	add_action('nbap_save_booking_after', function ($model, $appointment_id){
		$appointment_group_model = nbap_object( "NBAP\Models\Frontend\AppointmentGroupModel", nbap_post_data());
		$appointment_group_model->id = 0;
		$appointment_group_model->appointment_id = $appointment_id;
		$appointment_group_model->detail_data = "";
		$result = nbap_object( "NBAP\Services\AppointmentGroupService" )->add_update($appointment_group_model);
		if( $result->is_error() ){
			nbap_object("NBAP\Helpers\Components\InfoMessage")->return_message( $result->get_message(), "error");
			exit;
		}
	}, 5, 2);