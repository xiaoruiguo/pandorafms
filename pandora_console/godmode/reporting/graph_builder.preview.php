<?php

// Pandora FMS - http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2009 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation for version 2.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

if (is_ajax ()) {
	$search_agents = (bool) get_parameter ('search_agents');
	
	if ($search_agents) {
		
		require_once ('include/functions_agents.php');
		
		$id_agent = (int) get_parameter ('id_agent');
		$string = (string) get_parameter ('q'); /* q is what autocomplete plugin gives */
		$id_group = (int) get_parameter('id_group');
		
		$filter = array ();
		$filter[] = '(nombre COLLATE utf8_general_ci LIKE "%'.$string.'%" OR direccion LIKE "%'.$string.'%" OR comentarios LIKE "%'.$string.'%")';
		$filter['id_grupo'] = $id_group; 
		
		$agents = get_agents ($filter, array ('nombre', 'direccion'));
		if ($agents === false)
			return;
		
		foreach ($agents as $agent) {
			echo $agent['nombre']."|".$agent['direccion']."\n";
		}
		
		return;
 	}
 	
 	return;
}

if ($config['flash_charts']) {
	require_once ('include/fgraph.php');
}

check_login ();

if (! give_acl ($config['id_user'], 0, "IW")) {
	audit_db ($config['id_user'], $_SERVER['REMOTE_ADDR'], "ACL Violation",
		"Trying to access graph builder");
	include ("general/noaccess.php");
	exit;
}

$id = (integer) get_parameter('id');
$graphRows = get_db_all_rows_sql("SELECT t1.*,
	(SELECT t3.nombre 
		FROM tagente AS t3 
		WHERE t3.id_agente = 
			(SELECT t2.id_agente 
				FROM tagente_modulo AS t2
				WHERE t2.id_agente_modulo = t1.id_agent_module)) 
	AS agent_name
	FROM tgraph_source AS t1
	WHERE t1.id_graph = " . $id);
$module_array = array();
$weight_array = array();
$agent_array = array();

foreach ($graphRows as $graphRow) {
	$module_array[] = $graphRow['id_agent_module'];
	$weight_array[] = $graphRow['weight'];
	$agent_array[] = $graphRow['agent_name'];
}

$graphInTgraph = get_db_row_sql("SELECT * FROM tgraph WHERE id_graph = " . $id);
$stacked = $graphInTgraph['stacked'];
$events = $graphInTgraph['events'];
$period = $graphInTgraph['period'];

$modules = implode(',', $module_array);
$weights = implode(',', $weight_array);

	echo "<table class='databox'>";
	echo "<tr><td>";
	if ($config['flash_charts']) {
		echo graphic_combined_module (explode (',', $modules), explode (',', $weights), $period, $width, $height,
				'Combined%20Sample%20Graph', '', $events, 0, 0, $stacked);
	} else {
		echo "<img src='include/fgraph.php?tipo=combined&id=$modules&weight_l=$weights&label=Combined%20Sample%20Graph&height=$height&width=$width&stacked=$stacked&period=$period' border=1 alt=''>";
	}
	echo "</td></tr></table>";

