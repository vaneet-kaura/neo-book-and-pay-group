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
	
	/*add_filter('nbap_staff_model_init', function($model) {		
		/*$model->staff_service_group_id = nbap_post_var("staff_service_group_id", 0);
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
				
		/		
		return $model;
	}, 5, 2);
	
	
	add_filter('nbap_staff_model_form_view', function($model) {
		/*if($model->id > 0) {
			$service_obj = nbap_object( "NBAP\Services\ServiceGroupService" )->get_by_service_id($model->id);
			if(is_object($service_obj)) {
				$model->staff_service_group_id = $service_obj->id;
				$model->capacity_min = $service_obj->capacity_min;
				$model->capacity_max = $service_obj->capacity_max;
			}			
		}	
		/		
		return $model;
	}, 5, 2);
	*/
