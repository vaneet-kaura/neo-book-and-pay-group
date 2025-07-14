<?php

	add_filter('nbap_service_model_init', function($model) {		
		$model->service_group_id = nbap_post_var("service_group_id", 0);
		$model->capacity_min = (int)nbap_post_var("capacity_min", 1);
		$model->capacity_max = (int)nbap_post_var("capacity_max", 10);
		$model->obj_validator->rule_for( 'capacity_min' )
				->with_label( __( 'Min Capacity', 'neo-book-and-pay-group' ) )
				->numeric()->with_message( __( 'Invalid min capacity.', 'neo-book-and-pay-group' ) )
				;				
		$model->obj_validator->rule_for( 'capacity_max' )
				->with_label( __( 'Max Capacity', 'neo-book-and-pay-group' ) )
				->numeric()->with_message( __( 'Invalid max capacity.', 'neo-book-and-pay-group' ) )
				->greater_than_equal_other('capacity_min')->with_message( __( 'Max capacity should be greater than min capacity', 'neo-book-and-pay-group' ) )
				;
				
		return $model;
	}, 5, 2);
	
	
	add_filter('nbap_service_model_form_view', function($model) {
		if($model->id > 0) {
			$service_obj = nbap_object( "NBAP\Services\ServiceGroupService" )->get_by_service_id($model->id);
			if(is_object($service_obj)) {
				$model->service_group_id = $service_obj->id;
				$model->capacity_min = $service_obj->capacity_min;
				$model->capacity_max = $service_obj->capacity_max;
			}			
		}		
		return $model;
	}, 5, 2);
		
	
	add_filter('nbap_staff_model_form_view', function($model) {
		$model->service_groups = nbap_object( "NBAP\Services\StaffServiceGroupService" )->get_data($model->id);
		return $model;
	}, 5, 2);
	
	
	add_filter('nbap_staff_listing_model_view', function($model) {
		$staff_ids = array_column($model->staff_members, "id");
		$model->staff_service_groups = nbap_object( "NBAP\Services\StaffServiceGroupService" )->get_services_frontend($staff_ids);
		$model->capacity = 1;
		return $model;
	}, 5, 2);
	
	
	add_filter('nbap_booking_steps_model_view', function($model) {
		$model->capacity = 1;
		$model->view_bag->staff_service_groups = nbap_object( "NBAP\Services\StaffServiceGroupService" )->get_all()['rows'];
		$model->obj_validator->rule_for( 'capacity' )
					->with_label( __( 'Number of persons', 'neo-book-and-pay-group' ) )
					->numeric()->with_message( __( 'Invalid number of persons.', 'neo-book-and-pay-group' ) )
					;
		foreach($model->view_bag->staff_services as $item) {
			$item->price_formatted = "";
			$item->deposit_formatted = "";
		}			
		return $model;
	}, 5, 2);
	
	add_filter('nbap_booking_calendar_model_view', function($model) {
		$model->capacity = 1;
		$model->view_bag->staff_service_groups = nbap_object( "NBAP\Services\StaffServiceGroupService" )->get_service_frontend($model->staff_id, $model->id);
		$model->obj_validator->rule_for( 'capacity' )
					->with_label( __( 'Number of persons', 'neo-book-and-pay-group' ) )
					->numeric()->with_message( __( 'Invalid number of persons.', 'neo-book-and-pay-group' ) )
					;		
		return $model;
	}, 5, 2);
	
	add_filter('nbap_appointments_additional_data', function ($appointments) {
		$appointment_ids = array_column($appointments, 'id');
		$appointment_groups = nbap_object( "NBAP\Services\AppointmentGroupService" )->get_data($appointment_ids);
		foreach($appointments as $appointment) {
			$filtered = array_values(array_filter($appointment_groups, function ($item) use($appointment) {
				return $item->appointment_id === $appointment->id;
			}));
			if(count($filtered) == 1) 
				$appointment->capacity = (int)$filtered[0]->capacity;
			else
				$appointment->capacity = 1;
		}		
		return $appointments;
	}, 5, 1);
	
	add_filter('nbap_day_slots', function ($day_slots, $date, $staff_id, $service_id) {
		$slots=[];
		$staff_service_groups = nbap_object( "NBAP\Services\StaffServiceGroupService" )->get_service_frontend($staff_id, $service_id);
		$staff_service_group = count($staff_service_groups) == 1 ? $staff_service_groups[0] : null;
		if(!is_object($staff_service_group))
			$staff_service_group = nbap_object( "NBAP\Services\ServiceGroupService" )->get_by_service_id($service_id);
		
		$capacity = nbap_get_var('capacity', 1);
		$min = is_object($staff_service_group) ? $staff_service_group->capacity_min : 1;
		$max = is_object($staff_service_group) ? $staff_service_group->capacity_max : 1;
		$capacity = $capacity < $min ? $min : $capacity;
		$capacity = $capacity > $max ? $max : $capacity;
		
		foreach($day_slots as $time => $label) {			
			$booked = $label['status'] == 'booked' ? array_reduce($label['appointments'], fn($sum, $appointment) => $sum + $appointment->capacity, 0) : 0;
			$available = $max - $booked; 
			$label['capacity_booked'] = $booked;
			$label['capacity_available'] = $available;
			$label['capacity_min'] = $min;
			$label['capacity_max'] = $max;
			if($capacity <= $available && $label['status'] == 'booked') 
				$label['status'] = 'available';
			else if($capacity > $available && $label['status'] == 'available') 
				$label['status'] = 'not-available';
			$slots[$time] = $label;
		}		
		return $slots;
	}, 5, 4);
	
	add_filter('nbap_display_slot_label', function ($label, $data, $time) {
		$label .= " [".$data['capacity_booked']."/".$data['capacity_max']."]";
		return $label;
	}, 5, 3);
	
	add_filter('nbap_display_slot_info', function ($label, $data, $time) {
		$capacity = nbap_get_var('capacity', 1);
		$label .= " for ".$capacity." persons";
		return $label;
	}, 5, 3);
	
	add_filter('nbap_booking_steps_slots_paging_limit', function ($limit) {
		return 4;
	}, 5, 1);
	
	add_filter('nbap_booking_sub_total', function ($sub_total, $model, $obj_staff_service) {
		$capacity = nbap_post_var('capacity', 1);
		$sub_total = $obj_staff_service->price * $capacity;
		return $sub_total;
	}, 5, 3);
	
	add_filter('nbap_get_booking_steps_slots', function ($model) {
		$capacity = nbap_get_var('capacity', 1);
		$price = floatval($model["price"]) * $capacity;
		$deposit = floatval($model["deposit"]);
		$deposit = $price * $deposit * 0.01;
		$obj_format = nbap_object('NBAP\Helpers\Functions\Format');
		$model["price_formatted"] = $obj_format->currency($price);
		$model["deposit_formatted"] = $obj_format->currency($deposit);
		return $model;
	}, 5, 3);
	
	add_filter('nbap_get_booking_calendar_slots', function ($model) {
		foreach($model as $date => $slots):
			foreach($slots as $time => $slot_info):
				$capacity_min = intval($slot_info['staff']['staff_'.$slot_info['staff_id']]['capacity_min']);
				$capacity_max = intval($slot_info['staff']['staff_'.$slot_info['staff_id']]['capacity_max']);
				$capacity_booked = intval($slot_info['staff']['staff_'.$slot_info['staff_id']]['capacity_booked']);
				$capacity_available = intval($slot_info['staff']['staff_'.$slot_info['staff_id']]['capacity_available']);
				$capacity_available = $capacity_available < $capacity_min ? 0 : $capacity_available;
				$slot_info['capacity_booked'] = $capacity_booked;
				$slot_info['capacity_min'] = $capacity_min;
				$slot_info['capacity_max'] = $capacity_max;
				$slot_info['available_count'] = $capacity_available;
				$slot_info['onClick'] .= "selectCapacityTimeSlot(this);";
				$slots[$time] = $slot_info;
			endforeach;
			$model[$date] = $slots;
		endforeach;
		return $model;
	}, 5, 3);
	
	add_filter('add_appointment_data', function ($data) {
		$capacity = nbap_post_var('capacity', 1);
		$data['sub_total'] = floatval($data['service_price']) * $capacity;
		$data['total'] = floatval($data['service_price']) * $capacity;
		return $data;
	}, 5, 3);
	
	add_filter('nbap_enqueue_scripts', function ($scripts) {
		$plugin_dir = basename(constant("NBAP_GRP_LOCATION_PATH"));
		$scripts['booking-calendar-capacity'] = array( "../../{$plugin_dir}/public/frontend/booking-calendar-capacity.js", array("booking-calendar"), "filetime", true);
		return $scripts;
	}, 5, 1);
	
	add_filter('nbap_booking_calendar_enqueue_scripts', function ($scripts) {
		$scripts[] = 'booking-calendar-capacity';
		return $scripts;
	}, 5, 1);
	
	add_filter('nbap_appointment_model_view_view', function ($model) {
		$appointment_groups = nbap_object( "NBAP\Services\AppointmentGroupService" )->get_data([$model->id]);
		$model->capacity = count($appointment_groups) == 1 ? $appointment_groups[0]->capacity : 1;
		return $model;
	}, 5, 1);
	
	