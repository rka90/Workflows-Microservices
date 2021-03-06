<?php

/**
 * This file is necessary to include to use all the in-built libraries of /opt/fmc_repository/Reference/Common
 */
require_once '/opt/fmc_repository/Process/Reference/Common/common.php';

/**
 * list all the parameters required by the task
 */
function list_args() {
	create_var_def ( 'id', 'String' );
	create_var_def ( 'src_ip', 'String' );
	create_var_def ( 'dst_port', 'String' );
}
/**
 * iterate through the array of devices in order to apply the policy for each device
 */
foreach ( $context ['devices'] as $deviceidRow ) {
	/**
	 * extract the device database identifier from the device ID
	 */
	$devicelongid = substr ( $deviceidRow ['id'], 3 );
	logToFile ( "***************************" );
	logToFile ( "update device $devicelongid" );
	
	/**
	 * build the Microservice JSON params for the CREATE operation of the microservice
	 */
	$micro_service_vars_array = array ();
	$micro_service_vars_array ['object_id'] = $context ['id'];
	$micro_service_vars_array ['src_ip'] = $context ['src_ip'];
	$micro_service_vars_array ['dst_port'] = $context ['dst_port'];
	
	$object_id = $context ['id'];
	
	$simple_firewall = array (
			'simple_firewall' => array (
					$object_id => $micro_service_vars_array 
			) 
	);
	
	/**
	 * call the CREATE for simple_firewall MS for each device
	 */
	$response = execute_command_and_verify_response ( $devicelongid, CMD_CREATE, $simple_firewall, "CREATE simple_firewall" );
	$response = json_decode ( $response, true );
	if ($response ['wo_status'] === ENDED) {
		if (isset ( $context ['rules'] )) {
			$index = count ( $context ['rules'] );
		} else {
			$index = 0;
		}
		/**
		 * add the firewall policy rule to the array of rules applied on the devices
		 */
		$context ['rules'] [$index] ['delete'] = false;
		$context ['rules'] [$index] ['id'] = $context ['id'];
		$context ['rules'] [$index] ['src_ip'] = $context ['src_ip'];
		$context ['rules'] [$index] ['dst_port'] = $context ['dst_port'];
		
		$response = prepare_json_response ( $response ['wo_status'], $response ['wo_comment'], $context, true );
		echo $response;
	} else {
		task_exit ( FAILED, "Task FAILED" );
	}
}

/**
 * End of the task do not modify after this point
 */
task_exit ( ENDED, "Task OK" );

?>