function selectCapacityTimeSlot(o) {
	$form = jQuery(o).closest('form');
	$unique_id = $form.find("[name=unique_id]").val();
	$booking_vars = eval($unique_id + '_vars');
	$currency_symbol = $booking_vars['currency_symbol'];
	$slot_info = jQuery(o).data("slot-info");
	$o_capacity = $form.find("[name=capacity]");
	$capacity = $o_capacity.val();
	
	console.log($slot_info)
	$capacity_min = parseInt($slot_info.capacity_min);
	$available_count = parseInt($slot_info.available_count);	
	
	$o_capacity.empty();
	for (let i = $capacity_min; i <= $available_count; i++)
		$o_capacity.append(jQuery('<option>', { value: i, text: i }));	
	if($capacity < $capacity_min || $capacity > $available_count)
		$capacity = $capacity_min;
	$o_capacity.val($capacity);
	
	$sub_total = parseFloat($slot_info.min_price) * parseInt($capacity);
	$vat = 0;
	$total = $sub_total + $vat;
	$deposit = $slot_info.deposit > 0 ? $slot_info.deposit * $total * 0.01 : 0;

	$form.find(".sub_total").html($currency_symbol + $sub_total.toFixed(2));
	$form.find(".tax_price").html($currency_symbol + $vat.toFixed(2));
	$form.find(".total_price").html($currency_symbol + $total.toFixed(2));
	
	if ($form.find(".PaymentModeContainer").length) {
		$form.find(".PaymentModeContainer").find(".form-check.full .form-check-label span").html($currency_symbol + $total.toFixed(2));
		$form.find(".PaymentModeContainer").find(".form-check.deposit .form-check-label span").html($currency_symbol + $deposit.toFixed(2));
	}
}

function updateCapacity(o) {
	$form = jQuery(o).closest('form');
    $form.find(".time-slot.active").trigger("click");
}