<!-- Javascript  -->
<script language="javascript1.2" type="text/javascript">
<!--
function winopeng(url,wid) {
	nueva_ventana=open(url,wid,"width=580,height=250,status=no,toolbar=no,menubar=no");
	// WARNING !! Internet Explorer DOESNT SUPPORT "-" CARACTERS IN WINDOW HANDLE VARIABLE
	// I WAS 20 HOURS TESTING FOR THIS BUG >:( !!!
	status =wid;
}
function help_popup(help_id) {
	nueva_ventana=open("general/pandora_help.php?id=1","width=300,height=100,status=no,toolbar=no,menubar=no");
}
-->
</script>

<?php
// Pandora - The Free Monitoring System
// This code is protected by GPL license.
// Este codigo esta protegido por la licencia GPL.
// Sancho Lerena <slerena@gmail.com>, 2003-2006
// Raúl Mateos <raulofpandora@gmail.com>, 2005-2006

// Load global vars
require("include/config.php");
//require("include/functions.php");
//require("include/functions_db.php");
if (comprueba_login() == 0) {

	if (isset($_GET["id_agente"])){
		$id_agente = $_GET["id_agente"];
	}

	// Load icon index from ttipo_modulo
	$iconindex[]="";

	$sql_tm='SELECT id_tipo, icon FROM ttipo_modulo';
	$result_tm=mysql_query($sql_tm);
	while ($row_tm=mysql_fetch_array($result_tm)){
		$iconindex[$row_tm["id_tipo"]] = $row_tm["icon"];
	}
	
	// View last data packet		
	// Get timestamp of last packet
	$sql_t='SELECT * FROM tagente WHERE id_agente = '.$id_agente;
	$result_t=mysql_query($sql_t);
	$row_t=mysql_fetch_array($result_t);
	$timestamp_ref = $row_t["ultimo_contacto_remoto"];
	$timestamp_lof = $row_t["ultimo_contacto"];
	$intervalo_agente = $row_t["intervalo"];

	// Get last packet
	$sql3='SELECT * FROM tagente_modulo, tagente_estado WHERE tagente_modulo.id_agente = '.$id_agente.' AND tagente_modulo.id_agente_modulo = tagente_estado.id_agente_modulo ORDER BY id_module_group';
	//$sql3='SELECT * FROM tagente_modulo WHERE id_agente = '.$id_agente.' order by id_module_group';
	$label_group=0;
	$last_label = "";
	$result3=mysql_query($sql3);
	if (mysql_num_rows ($result3)) {
	echo "<h3>".$lang_label["last_data_chunk"]."<a href='help/chap3_en.php#3322' target='_help'><img src='images/ayuda.gif' border='0' class='help'></a></h3>";
	echo "<table width='750' cellpadding='3' cellspacing='3'><th>X<th>".$lang_label["module_name"];
	echo "<th>".$lang_label["type"]."<th>".$lang_label["int"]."<th>".$lang_label["description"]."<th>".$lang_label["data"]."<th>".$lang_label["graph"]."<th>".$lang_label["raw_data"]."<th>".$lang_label["timestamp"];
	$texto='';
	while ($row3=mysql_fetch_array($result3)){
			// Render module group names  (fixed code)
			$nombre_grupomodulo = dame_nombre_grupomodulo ($row3["id_module_group"]);
			if ($nombre_grupomodulo != ""){
				if (($label_group == 0) || ($last_label != $nombre_grupomodulo)){	// Show label module group
					$label_group = -1;
					$last_label = $nombre_grupomodulo;
					echo "<tr><td class='datos2' align='center' colspan=9><b>".$nombre_grupomodulo."</b>";
				}
			}

			echo "<tr><td class='datos'>";
			// Render network exec module button, only when
			// Agent Write for this module and group, is given
			// Is a network module 
			// Has flag = 0
			$id_grupo = $row_t["id_grupo"];
			$id_usuario=$_SESSION["id_usuario"];
			if (give_acl($id_usuario, $id_grupo, "AW")==1){
				if ($row3["id_tipo_modulo"] > 4){
					if ($row3["flag"] == 0){
						echo "<a href='index.php?sec=estado&sec2=operation/agentes/ver_agente&id_agente=".$id_agente."&id_agente_modulo=".$row3["id_agente_modulo"]."&flag=1&refr=60'><img src='images/target.gif' border=0></a>";
					} else {
						echo "<a href='index.php?sec=estado&sec2=operation/agentes/ver_agente&id_agente=".$id_agente."&id_agente_modulo=".$row3["id_agente_modulo"]."&flag=1&refr=60'><img src='images/refresh.gif' border=0></a>";
					}
				} 				
			} 
			$nombre_grupomodulo = dame_nombre_grupomodulo ($row3["id_module_group"]);
			if ($nombre_grupomodulo != ""){
				if (($label_group == 0) || ($last_label != $nombre_grupomodulo)){	// Show label module group
					$label_group = -1;
					$last_label = $nombre_grupomodulo;
					$texto = $texto. "<td class='datos2' align='center' colspan=7><b>".$nombre_grupomodulo."</b>";
				}
			}
			
			$nombre_tipo_modulo = dame_nombre_tipo_modulo($row3["id_tipo_modulo"]);
			echo "<td class='datos_id'>";
			echo salida_limpia(substr($row3["nombre"],0,15));
			echo "<td class='datos'>";
			echo "<img src='images/".$iconindex[$row3["id_tipo_modulo"]]."' border=0>";
			echo "<td class='datos'>";
			if ($row3["module_interval"] != 0)
				echo $row3["module_interval"];
			else
				echo $intervalo_agente;
			//echo $nombre_tipo_modulo;
			echo "<td class='f9'>";
			echo salida_limpia(substr($row3["descripcion"],0,32));
			if (strlen($row3["descripcion"]) > 32){
				echo "...";
			}
			// For types not string type (3 data_string, 9 tcp_string, 14 snmp_string)
			if (($row3["id_tipo_modulo"] != 3) AND ($row3["id_tipo_modulo"]!=10) AND ($row3["id_tipo_modulo"]!=17)){

				echo "<td class=datos>";
				echo substr($row3["datos"],0,5);
				$handle = "stat".$nombre_tipo_modulo."_".$nombre_agente;
				$url = 'reporting/procesos.php?agente='.$nombre_agente;
				$win_handle=dechex(crc32($nombre_agente.$row3["nombre"]));
				echo "<td class=datos width='78'>";
				
				echo "<a href='javascript:winopeng(\"reporting/stat_win.php?tipo=mes&id=".$row3["id_agente_modulo"]."\", \"mes_".$win_handle."\")'><img  border=0  src='images/grafica_m.gif'></a>&nbsp;";
				
				$link ="winopeng('reporting/stat_win.php?tipo=semana&id=".$row3["id_agente_modulo"]."','sem_".$win_handle."')";
				echo '<a href="javascript:'.$link.'"><img border=0 src="images/grafica_w.gif"></a>&nbsp;';
				
				$link ="winopeng('reporting/stat_win.php?tipo=dia&id=".$row3["id_agente_modulo"]."','dia_".$win_handle."')";
				echo '<a href="javascript:'.$link.'"><img border=0  src="images/grafica_d.gif"></a>&nbsp;';

				$link ="winopeng('reporting/stat_win.php?tipo=hora&id=".$row3["id_agente_modulo"]."','hora_".$win_handle."')";
				echo '<a href="javascript:'.$link.'"><img border=0 src="images/grafica_h.gif"</a>';
			}
			else { # Writing string data in different way :)
				echo "<td class='datosf9' colspan='2'>";
				echo salida_limpia(substr($row3["datos"],0,42));
			}
			
			echo "<td class=datos width=70>";
			echo "<a href='index.php?sec=estado&sec2=operation/agentes/datos_agente&tipo=mes&id=".$row3["id_agente_modulo"]."'><img border=0 src='images/data_m.gif'>&nbsp;&nbsp;";
			echo "<a href='index.php?sec=estado&sec2=operation/agentes/datos_agente&tipo=semana&id=".$row3["id_agente_modulo"]."'><img border=0 src='images/data_w.gif'>&nbsp;&nbsp;";
			echo "<a href='index.php?sec=estado&sec2=operation/agentes/datos_agente&tipo=dia&id=".$row3["id_agente_modulo"]."'><img border=0 src='images/data_d.gif'>";
			echo "<td class='datosf9'>".$row3["timestamp"];

 		//}
	}
	echo '<tr><td colspan="9"><div class="raya"></div></td></tr></table>';
}
else echo "- <font class='red'>".$lang_label["no_modules"]."</font>";
}
?>