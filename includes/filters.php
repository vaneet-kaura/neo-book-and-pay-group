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
	
	
	add_filter('nbap_booking_model_view', function($model) {
		$model->capacity = 1;
		$model->view_bag->staff_service_groups = nbap_object( "NBAP\Services\StaffServiceGroupService" )->get_all()['rows'];
		$model->obj_validator->rule_for( 'capacity' )
					->with_label( __( 'Number of persons', 'neo-book-and-pay' ) )
					->numeric()->with_message( __( 'Invalid number of persons.', 'neo-book-and-pay' ) )
					;
		return $model;
	}, 5, 2);
	
	add_filter('nbap_appointments_additional_data', function ($appointments) {
		$appointment_ids = array_column($appointments, 'id');
		$appointment_groups = nbap_object( "NBAP\Services\AppointmentGroupService" )->get_data($appointment_ids);
		foreach($appointments as $appointment) {
			$filtered = array_filter($appointment_groups, function ($item) use($appointment) {
			  return $item->appointment_id === $appointment->id;
			});
			$appointment->capacity = $filtered[0]->capacity;
		}		
		return $appointments;
	}, 5, 1);
	
	add_filter('nbap_day_slots', function ($day_slots, $date, $staff_id, $service_id) {
		$slots=[];
		$staff_service_group = nbap_object( "NBAP\Services\StaffServiceGroupService" )->get_service_frontend($staff_id, $service_id);
		if(!is_object($staff_service_group))
			$staff_service_group = nbap_object( "NBAP\Services\ServiceGroupService" )->get_by_service_id($service_id);
		
		$capacity = nbap_get_var('capacity', 1);
		foreach($day_slots as $time => $label) {
			$min = is_object($staff_service_group) ? $staff_service_group->capacity_min : 1;
			$max = is_object($staff_service_group) ? $staff_service_group->capacity_max : 1;
			$booked = 0;
			$available = $max - $booked; 
			$label['capacity_booked'] = $booked;
			$label['capacity_available'] = $available;
			$label['capacity_min'] = $min;
			$label['capacity_max'] = $max;
			if($capacity <= $available)
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
	
	add_filter('nbap_is_slot_booked', function ($is_booked, $appointment, $slot) {
		return $is_booked;
	}, 5, 3);
	
	add_filter('nbap_booking_slot_paging_limit', function ($limit) {
		return 4;
	}, 5, 1);
	