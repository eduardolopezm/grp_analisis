<?php
/*
error_reporting(E_ALL);
ini_set('display_errors', '1');
*/
/*
 * AHA 17-Oct-2014 Se corrigio problema en el filtro x empresa ya que al seleccionarlo y hacer clic en buscar no se mantenia seleccionado
 */

include('includes/session.inc');
$funcion=1366;
include('includes/SecurityFunctions.inc');

$title=_('Dataware de Contabilidad');
$report="dwh_ReporteContabilidad_V1.php";

if (isset($_POST['reiniciar'])){
	$_SESSION['valoreshist'] = "";
	$_SESSION['valorfijo']="";
	$_SESSION['chkcol']=array();
	$_SESSION['topcolumns']=array();
	$_SESSION['condicionesconta']="";
	$_SESSION['condnameconta']="";
    
    echo '<meta http-equiv="Refresh" content="0; url=' . $_SERVER['PHP_SELF'] . '?' . SID .
		'&FromYear='.$_POST['FromYear'].
        '&FromMes='.$_POST['FromMes'].
        '&FromDia='.$_POST['FromDia'].
        '&ToYear='.$_POST['ToYear'].
        '&ToMes='.$_POST['ToMes'].
        '&ToDia='.$_POST['ToDia'].
        '">';
	exit;
}
		
$arrcolscondescripcion = array(
		0 => 'genero',
		1 => 'grupo',
		2 => 'nombrerubro',
		3 => 'nombrecuenta',
		4 => 'nombreunidadnegocio',
		5 => 'trimestre',
		6 => 'cuatrimestre',
		7 => 'nombredocto',
		8 => 'mes',
		9 => 'foliodocto',
		10 => 'periodofiscal',
		11 => 'nombrerazonsocial',
		12 => 'nombreregion',
		13 => 'nombredepartamento',
		14 => 'nombrearea',
		15 => 'tipocuenta',
		16 => 'seccioncuenta',
);

$arrcolscontables = array(
		0 => 'genero',
		1 => 'grupo',
		2 => 'nombrerubro',
		3 => 'nombrecuenta',
		4 => 'nombreunidadnegocio',
		5 => 'nombredocto',
		6 => 'foliodocto',
		7 => 'periodofiscal',
		8 => 'sujetocontable',
		9 => 'numeropoliza',
		10 => 'tipopoliza'
);

$_SESSION["cadenafiltro"]="";
$_SESSION["consultareportecontable"]="";
$_SESSION["filtrodw"]= "";

if ($_GET['novalorfijo']==1)
	$_SESSION['valorfijo']="";


//Ver config
// $DatawareDB = $_SESSION['DwDatabase'];
// $host       = $_SESSION['DwHost'];
// $dbuser     = $_SESSION['DwUser'];
// $dbpassword = $_SESSION['DwPass'];
// $mysqlport  = $_SESSION['DwDBPort'];
// $dbsocket   = $_SESSION['DwSock'];

// $dbDataware=mysqli_connect($host , $dbuser, $dbpassword, $DatawareDB, $mysqlport, $dbsocket);

$DatawareServillantas=$_SESSION['BaseDataware'];
$hostDataware=$host;
if (strlen($_SESSION['HostDataware'])>0){
	$hostDataware=$_SESSION['HostDataware'];
}

$dbDataware=mysqli_connect($hostDataware , $dbuser, $dbpassword,$DatawareServillantas, $mysqlport,$dbsocket);

// $DatawareServillantas= $_SESSION['BaseDataware'];
// $hostDataware= $host;
// $mysqlport= 3306;
// $dbpassword= 'pr*mysql013';

// if (strlen($_SESSION['HostDataware'])>0){
// 	$hostDataware=$_SESSION['HostDataware'];
// }

// $dbDataware=mysqli_connect($hostDataware , $dbuser, $dbpassword, $DatawareServillantas, $mysqlport,$dbsocket);

//echo ":".$hostDataware."-".$dbuser."-".$dbpassword."-".$DatawareServillantas."-".$mysqlport."-".$dbsocket;

if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

# **********************************************************************
# ***** RECUPERA VALORES DE FECHAS *****

if (isset($_POST['FromYear'])){
	$FromYear= $_POST['FromYear'];
}elseif (isset($_GET['FromYear'])){
	$FromYear = $_GET['FromYear'];
}else{
	$FromYear=date('Y');
};

if (isset($_POST['FromMes'])){
	$FromMes= $_POST['FromMes'];
}elseif (isset($_GET['FromMes'])){
	$FromMes = $_GET['FromMes'];
}else{
	$FromMes="01";//date('m');	
}; 

if (strlen($FromMes)==1){
	$FromMes = "0" . $FromMes;
}

if (isset($_POST['FromDia'])){
	$FromDia= $_POST['FromDia'];
}elseif (isset($_GET['FromDia'])){
	$FromDia = $_GET['FromDia'];
}else{
	$FromDia="01";
};  

if (strlen($FromDia)==1){
	$FromDia = "0" . $FromDia;
}

if (isset($_POST['ToYear'])){
	$ToYear= $_POST['ToYear'];
}elseif (isset($_GET['ToYear'])){
	$ToYear = $_GET['ToYear'];
}else{
	$ToYear=date('Y');
};        

if (isset($_POST['ToMes'])){
	$ToMes= $_POST['ToMes'];
}elseif (isset($_GET['ToMes'])){
	$ToMes = $_GET['ToMes'];
}else{
	$ToMes=date('m');
};  

if (strlen($ToMes)==1){
	$ToMes = "0" . $ToMes;
}

if (isset($_POST['ToDia'])){
	$ToDia= $_POST['ToDia'];
}elseif (isset($_GET['ToDia'])){
	$ToDia = $_GET['ToDia'];
}else{
	$ToDia=date('d');
};  

if (strlen($ToDia)==1){
	$ToDia = "0" . $ToDia;
}

$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia) . ' 00:00:00';
$fechafin= rtrim($ToYear).'-'.rtrim($ToMes).'-'.rtrim($ToDia) . ' 23:59:59';
//echo "<br>" . $fechaini;
//echo "<br>" . $fechafin;      
     
$InputError = 0;
if ($fechaini > $fechafin){
	include('includes/header.inc');
	$InputError = 1;
	prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
	exit;	
}else{
	$InputError = 0;
}
     
# ******************************************************************************

if (isset($_POST['procesar'])){
	$procesar= $_POST['procesar'];
}else{
	$procesar = $_GET['procesar'];
};

if (isset($_POST['groupby'])){
	$groupby= $_POST[$_POST['groupby']];
}elseif (isset($_GET['groupby'])){
	$groupby = $_GET['groupby'];
}else{
	$groupby = "anio";
};

if (isset($_POST['groupbysecond'])){
	$groupbysecond= $_POST[$_POST['groupbysecond']];
}elseif (isset($_GET['groupbysecond'])){
	$groupbysecond = $_GET['groupbysecond'];
}else{
	$groupbysecond = "";
};


if (isset($_POST['condicion'])){
	$condicion= $_POST['condicion'];
}elseif (isset($_GET['condicion'])){
	$condicion = $_GET['condicion'];
}else{
	$condicion = "";
};


if (isset($_GET['valorfijo'])){
	if (strlen($_GET['valorfijo'])>5){
		unset($_SESSION['valorfijo']);
        $_SESSION['valorfijo']=str_replace('$','',$_GET['valorfijo']);  
        $_SESSION['valorfijo']=str_replace('=','=$',$_SESSION['valorfijo']);
        $_SESSION['valorfijo']=trim($_SESSION['valorfijo'])."$";
    }
}
if (strpos($condicion,$_SESSION['valorfijo'])===false)
	$condicion= $_SESSION['valorfijo'].' '.$condicion;
	
//verificar si selecciono condiciones adicionales
$wherecond="";
$arrwherecond=array();

if (isset($_POST['wherecond'])) {
	$wherecond = " AND (";
	$pent = 0;
	for ($ds=0;$ds<count($_POST['wherecond']);$ds++) {
		$pent = $pent + 1;
		if ($pent == 1){
			$wherecond .= " $groupby = '"  . $_POST['wherecond'][$ds] . "'";          
		}else{
			$wherecond .= " OR $groupby = '"  . $_POST['wherecond'][$ds] . "'";
		}
		$arrwherecond[] = $_POST['wherecond'][$ds];
	}
	$wherecond .= ") ";
}
 
if (isset($_POST['filtro'])){
	$filtro= $_POST['filtro'];
}elseif (isset($_GET['filtro'])){
	$filtro = $_GET['filtro'];
}else{
	$filtro = "";
};

//salvar filtro 
if ($_POST['opcsavefilter']=="1") 
{
	$nombrefiltro = $_POST['namenewfilter'];
	
	// buscar si existen un filtro guardado
	$sql = "Select * from DWH_userfilters
		WHERE proyecto='" . $funcion . "' and filtername = '$nombrefiltro'
		and userid = '".$_SESSION['UserID']."'";
	
	$rs = DB_query($sql,$dbDataware);
	
	if (DB_num_rows($rs) > 0 ){
		prnMsg(_('El nombre '.$nombrefiltro.' ya existe en su lista de filtros'),'error');
	}else{
		$lista="";
		
		for($i=1; $i <= Count($_SESSION['chkcol']); $i++){
			if ($_SESSION['chkcol'][$i])
				$lista.=$i.",";
		}

		$lista = substr($lista, 0, strlen($lista)-1);
		$sql = "Insert DWH_userfilters VALUES ('".$_SESSION['UserID']."','$nombrefiltro','$condicion','$groupby','$groupbysecond','$lista','" . $funcion . "')";
		$rs = DB_query($sql,$dbDataware);
	}
}

//eliminar filtro
if ($_POST['opcdelfilter']=="1"){
	$nombrefiltro = $_POST['filterlist'];
	$sql = "DELETE from DWH_userfilters
		WHERE proyecto='" . $funcion . "' and filtername = '$nombrefiltro'
			and userid = '".$_SESSION['UserID']."'";
	$r=DB_query($sql,$dbDataware);
}

if (isset($_GET['OrdenarPor'])){
	$OrdenarPor = $_GET['OrdenarPor'];
}else{
	$OrdenarPor = $groupby;
};

if (isset($_POST['procesar']))
	$OrdenarPor="header";

if (isset($_GET['Ordenar'])){
	$Ordenar = $_GET['Ordenar'];
}else{
	$Ordenar = "asc";
};

if ($Ordenar == "asc") {
	$sigOrdenar = "desc";	
}else{
	$sigOrdenar = "asc";	
};
if (isset($_POST['filtro']))
{
	$filtro= $_POST['filtro'];
}
else{
	if (isset($_GET['filtro']))
	{
		$filtro = $_GET['filtro'];
	}
	else{
		$filtro = "";
	}
}

if (isset($_POST["filtroexcluir"])){
	$filtro= $_POST["filtroexcluir"];
}

if (isset($_POST['valor']))
{
	$valor= $_POST['valor'];
}
else{
	if (isset($_GET['valor']))
	{
		$valor = $_GET['valor'];
	}
	else{
		$valor = "";
	}
}

if (isset($_POST['condicionante']))
{
	$valorcondicionante= $_POST['condicionante'];
}
else{
	if (isset($_GET['condicionante']))
	{
		$valorcondicionante = $_GET['condicionante'];

	}
	else{
		$valorcondicionante = "=";
	};
};

$valorcondicionante=str_replace('|','=',$valorcondicionante);
$valorcondicionante=str_replace('^','!=',$valorcondicionante);

if ($filtro<>'' and $valor<>'')
{
	if($_GET['Excluir'] == 1)
	{
		$_SESSION['condicionesconta'] = $_SESSION['condicionesconta']." AND " . $filtro . $valorcondicionante."'"  . $valor . "'";
		$_SESSION['condnameconta']= $_SESSION['condnameconta']." Excluir <img src='part_pics/Fill-Right.png' border=0> ".$valor;
		$_SESSION['condnameconta'] = $_SESSION['condnameconta']."@";
	}
}

if(isset($_POST['excluir'])) 
{
	$arraycheckbox = $_POST['excluye'];
	
	for ($contmod = 0; $contmod < count($arraycheckbox); $contmod++)
	{
		$_GET['Excluir']= 1;
		$IdFiltroExcluir= $arraycheckbox[$contmod];
		$_SESSION['condicionesconta']= $_SESSION['condicionesconta']." AND " . $filtro . " != '" . $IdFiltroExcluir . "'";
		$_SESSION['condnameconta']= $_SESSION['condnameconta']." Excluir <img src='part_pics/Fill-Right.png' border=0> ".$IdFiltroExcluir;
		$_SESSION['condnameconta'] = $_SESSION['condnameconta']."@";
	}
}

$wherecond .= $_SESSION['condicionesconta'];

//columnas visibles
$totCol = 5;

if (($_POST['opcdelfilter']!="1" and $_POST['opcsavefilter']!="1" and !isset($_POST['procesar']) and $_REQUEST['keepvisible']!=1) || ($_POST['allcolumns']==1)){
	$_SESSION['chkcol'] = array();
	for($j=1;$j<=$totCol;$j++)
		$_SESSION['chkcol'][$j] = "checked";
}else{
	if ((isset($_POST['procesar']) and $_POST['allcolumns']!=1) || $_POST['opcsavefilter']=="1" || $_POST['opcdelfilter']=="1")
		if ($_POST['dimensionuno'])
			for($j=1;$j<=$totCol;$j++)
				$_SESSION['chkcol'][$j] = ($_POST['chkcol'.$j]=="on")?"checked":"";
}

//verificar si carga filtro guardado
if ($_REQUEST['filterlist'] and $_POST['opcdelfilter']!="1"){
	$sql = "Select * from DWH_userfilters
			WHERE proyecto='" . $funcion . "' and filtername = '".$_POST['filterlist']."' 
			and userid = '".$_SESSION['UserID']."'";
	
	$rs = DB_query($sql,$dbDataware);
	$rows = DB_fetch_array($rs);
	$condicion = $rows['filter'];
	$groupby = $rows['dimension1'];
	$groupbysecond = $rows['dimension2'];
	$arrcolumnas = explode(",",$rows['columns']);
	
	for($j=1; $j<=$totCol; $j++)
		$_SESSION['chkcol'][$j] = (in_array($j,$arrcolumnas))?"checked":"";

	$Ordenar = "header";
	$Ordenar = "asc";
	$_GET['filtrar']=0;
}

$titulo = trim($groupby);
switch ($titulo){
	case 'anio' :
		$titulo2 = 'A&#209;o<br>'. _('');
		$sig_groupby = "mes";
		break;
	case 'mes' :
		$titulo2 = _('Mes').'<br>'. _('');
		$sig_groupby = "trimestre";
		break;
	case 'trimestre' :
		$titulo2 = _('Trimestre').'<br>'. _('');
		$sig_groupby = "cuatrimestre";
		break;
	case 'cuatrimestre' :
		$titulo2 = _('Cuatrimestre');
		$sig_groupby = "periodofiscal";
		break;
	case 'periodofiscal' :
		$titulo2 = _('Periodo Fiscal');
		$sig_groupby = "nombrerazonsocial";
		break;
	case 'nombrerazonsocial' :
		$titulo2 = _('Razon Social');
		$sig_groupby = "nombreregion";
		break;
	case 'nombreregion' :
		$titulo2 = _('Region');
		$sig_groupby = "nombredepartamento";
		break;
	case 'nombredepartamento' :
		$titulo2 = _('Departamento');
		$sig_groupby = "nombrearea";
		break;
	case 'nombrearea' :
		$titulo2 = _('Area');
		$sig_groupby = "nombreunidadnegocio";
		break;
	case 'nombreunidadnegocio' :
		$titulo2 = _('Unidad de Negocio');
		$sig_groupby = "grupo";
		break;
	case 'grupo' :
		$titulo2 = _('Grupo');
		$sig_groupby = "nombrerubro" ;
		break;
	case 'nombrerubro' :
		$titulo2 = _('Seccion');
		$sig_groupby = "nombrecuenta";
		break;
	case 'nombrecuenta' :
		$titulo2 = _('Cuenta Contable');
		$sig_groupby = "tipocuenta";
		break;
	case 'tipocuenta' :
		$titulo2 = _('Tipo de Cuenta');
		$sig_groupby = "seccioncuenta";
		break;
	case 'seccioncuenta' :
		$titulo2 = _('Seccion Cuenta');
		$sig_groupby = "nombredocto";
		break;
	case 'nombredocto' :
		$titulo2 = _('Tipo Documento');
		$sig_groupby = "foliodocto";
		break;
	case 'foliodocto' :
		$titulo2 = _('Folio Documento');
		$sig_groupby = "anio";
		break;
}

$titulografica= strtoupper(str_replace('<br>',' ',$titulo2));
$titulo2 = strtoupper($titulo2);
$colsconsulta= "";

if (isset($_POST["tipografica"])){
	$_POST["tipografica"]= $_POST["tipografica"];
} else {
	$_POST["tipografica"]= 7;
}

$inprocess=false;
/*
$sql = "select fechaultimomovimiento,ADDDATE(fechaultimomovimiento, INTERVAL 1 DAY) as fechaFin  
		from DW_Status 
		where nombre='DatawareContable' and estado <> 'OK'";
$rs = DB_query($sql,$dbDataware);
if (DB_num_rows($rs) > 0 )
	{
		$inprocess=true;
		$rows = DB_fetch_array($rs);
		$fechaFinDw= $rows['fechaFin'];
	}
	*/
//Si el DW esta en actualizaci�n no busca nada
if( $inprocess ){
	include('includes/header.inc');
	echo '<HEAD><TITLE> :: Dataware de Presupuestos de Contabilidad</TITLE></HEAD>';
	
	echo '<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%" >';
		echo '	<tr>
				<td class="texto_status2" colspan=2>
					<img src="images/d_presupuestos.png" height="25" width="25" title="' . _('Reporte Dataware Contabilidad') . '" alt="">
					Reporte Dataware Contabilidad<br>
					<br>
					En Proceso de Actualizaci&oacute;n..., tiempo estimado de finalizaci&oacute;n: '.$fechaFinDw.' 
					<br>
				</td>
			</tr>';
	echo '</table>';			
}
else
{
 if (!isset($_POST['excel'])){
	include('includes/header.inc');
	echo '<HEAD><TITLE> :: Dataware de Presupuestos de Egresos</TITLE></HEAD>';

	if ($groupbysecond)
		$dim=0;
	else
		$dim=1;
		
	echo '<form method="post"  name="FDatosB">
		<input type="hidden" name="loadedfilter" value="'.$_POST['filterlist'].'">
		<input type="hidden" name="opcsavefilter" value="0">
		<input type="hidden" name="opcdelfilter" value="0">
		<input type="hidden" name="allcolumns" value="0">
		<input type="hidden" name="dimensionuno" value="'.$dim.'">';
		
	//verificar segunda dimension 
 	$_SESSION['topcolumns'] = array();
	if ($groupbysecond)
	{
		$condact = str_replace("$","'",$condicion);
		
		
		$qry = "SELECT distinct $groupbysecond as colname
				FROM DWH_Contabilidad d 
				WHERE Fecha between '" .$fechaini."' AND '".$fechafin."' 
				$condact   
				 ORDER BY $groupbysecond";
		
		$rstopcol = DB_query($qry,$dbDataware);
		
		$index=1;
		while ($rsmyrow = DB_fetch_array($rstopcol)){
			$_SESSION['topcolumns'][$index++] = $rsmyrow['colname'];
		}
	}else
 		$_SESSION['topcolumns'][]="";
	
	//echo var_dump($_SESSION['topcolumns']);
	
	echo '<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%" >';
		echo '<tr>
				<td class="texto_status2" colspan=2>
					<img src="images/i_contab2_30x30.png" height="25" width="25" title="' . _('Reporte Dataware Contabilidad') . '" alt=""> ' . $title . '<br>
				</td>
  			 	</tr>
				<tr><td colspan=2>&nbsp;</td></tr>			
		';
		
		echo "<tr><td class='texto_normal' colspan='2'>";
			echo '<table border="0" cellpadding="0" cellspacing="0" style="margin:auto;">';
				echo '<tr>';
					echo '<td class="texto_normal">' . _('Desde : ') . '</td>';
					echo'<td><select Name="FromDia">';
						$sql = "SELECT * FROM cat_Days";
						$Todias = DB_query($sql,$db);
		    			//$result = DB_query($sql, $dbDataware);
						while ($myrowTodia=DB_fetch_array($Todias,$dbDataware)){
							$Todiabase=$myrowTodia['DiaId'];
							if (rtrim(intval($FromDia))==rtrim(intval($Todiabase))){ 
								echo '<option  VALUE="' . $myrowTodia['Dia'] .  '" selected>' .$myrowTodia['Dia'];
							}else{
								echo '<option  VALUE="' . $myrowTodia['Dia'] .  '" >' .$myrowTodia['Dia'];
							}
						}
					echo '</td>';
					echo'<td>';
						echo'<select Name="FromMes">';
						$sql = "SELECT * FROM cat_Months";
						$ToMeses = DB_query($sql,$db);
						while ($myrowToMes=DB_fetch_array($ToMeses,$dbDataware)){
							$ToMesbase=$myrowToMes['u_mes'];
							if (rtrim(intval($FromMes))==rtrim(intval($ToMesbase))){ 
								echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" selected>' .$myrowToMes['mes'];
							}else{
								echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
						 	}
						}
						echo '</select>';
						echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
					echo '</td>';
		            echo '<td class="texto_normal">' . _('Hasta:') . '</td>';
					echo'<td><select Name="ToDia">'; 
						$sql = "SELECT * FROM cat_Days";
						$Todias = DB_query($sql,$db,'','');
						while ($myrowTodia=DB_fetch_array($Todias,$dbDataware)){
							$Todiabase=$myrowTodia['DiaId'];
						  	if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))){ 
								echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '  " selected>' .$myrowTodia['Dia'];
						  	}else{
								echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
						  	}
						}
					echo '</td>';
				    echo'<td class="texto_normal" align="center">';
						echo'<select Name="ToMes">';
						$sql = "SELECT * FROM cat_Months";
						$ToMeses = DB_query($sql,$db,'','');
					 	while ($myrowToMes=DB_fetch_array($ToMeses,$dbDataware)){
							$ToMesbase=$myrowToMes['u_mes'];
							if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){ 
						 		echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '  " selected>' .$myrowToMes['mes'];
					     	}else{
						 		echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
					     	}
					 	}
					 	echo '</select>';
					 	echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
					echo'</td>';
				echo '</tr>';
			echo '</table>';
		echo '</td></tr>';
		
  		echo '<tr><td align="center" colspan=2>&nbsp;</td></tr>';
  		
  		
  		echo '<tr valign="top">';
    		//echo '<td class="texto_normal" align="center">';
	  		$chktiempo=validacheck('tiempo',$groupby);
	  		$chkempresa=validacheck('empresa',$groupby);
	  		$chkcontab=validacheck('contabilidad',$groupby);
	  		$chkdocumento=validacheck('documento',$groupby);
	  		
				$tabla= '
					<td  align="right" cellpadding="1" colspan=1><table><tr><td nowrap>
							<input type=radio name=groupbysecond value="" checked onclick="if(this.checked) document.FDatosB.allcolumns.value=1;">
    						<img title="Una dimension" src="images/1d.png" width=20 border=0 >
    				</td>
					<td class="texto_normal" align="center" nowrap><input type=radio name=groupby value="tiempo" '.$chktiempo.'>x Tiempo<br>
						<select name="tiempo" onchange="actualizaGroupBy(this);">
							<option value="anio" '; if($groupby=="anio"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>A&#209;o </option>
							<option value="mes" '; if($groupby=="mes"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Mes</option>
							<option value="trimestre" '; if($groupby=="trimestre"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Trimestre</option>
							<option value="cuatrimestre" '; if($groupby=="cuatrimestre"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Cuatrimestre</option>
							<option value="periodofiscal" '; if($groupby=="periodofiscal"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Periodo Fiscal</option>
						</select>	
					</td>
					<td class="texto_normal" align="center" nowrap><input type=radio name=groupby value="empresa" '.$chkempresa.'>x Empresa<br>
						<select name="empresa" onchange="actualizaGroupBy(this);">
							<option value="nombrerazonsocial" '; if($groupby=="nombrerazonsocial"){ $tabla.= 'selected';$chkempresa='checked';} $tabla.='>Razon Social</option>
							<option value="nombreregion" '; if($groupby=="nombreregion"){ $tabla.= 'selected';$chkempresa='checked';} $tabla.='>Region</option>
							<option value="nombredepartamento" '; if($groupby=="nombredepartamento"){ $tabla.= 'selected';$chkempresa='checked';} $tabla.='>Departamento</option>
							<option value="nombrearea" '; if($groupby=="nombrearea"){ $tabla.= 'selected';$chkempresa='checked';} $tabla.='>Area</option>
							<option value="nombreunidadnegocio" '; if($groupby=="nombreunidadnegocio"){ $tabla.= 'selected';$chkempresa='checked';} $tabla.='>Unidad de Negocio</option>				
						</select>
					</td>
					<td class="texto_normal" align="center" nowrap><input type=radio name=groupby value="contabilidad" '.$chkcontab.'>x Cuenta<br>
						<select name="contabilidad" onchange="actualizaGroupBy(this);">
							<option value="grupo" '; if($groupby=="grupo"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Grupo</option>
							<option value="nombrerubro" '; if($groupby=="nombrerubro"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Seccion</option>
							<option value="cuenta_contable" '; if($groupby=="cuenta_contable"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Cuenta Contable</option>
							<option value="tipocuenta" '; if($groupby=="tipocuenta"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Tipo de Cuenta</option>
							<option value="seccioncuenta" '; if($groupby=="seccioncuenta"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Seccion Cuenta</option>
						</select>
					</td>
					<td class="texto_normal" align="center" nowrap><input type=radio name=groupby value="documento" '.$chkdocumento.'>x Documento<br>
						<select name="documento" onchange="actualizaGroupBy(this);">
							<option value="nombredocto" '; if($groupby=="nombredocto"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>Tipo Documento</option>
							<option value="foliodocto" '; if($groupby=="foliodocto"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>Folio Documento</option>
						</select>
					</td>
					
					</tr>				
				</table>
				</td>';
				$tablaH= '<table align="center" cellpadding="3" cellspacing="0" border="0">
    				<tr>
			    		<td class="texto_normal"><input type=radio name=groupby value="tiempo" '.$chktiempo.'>x Tiempo</td>
			    		<td class="texto_normal"><input type=radio name=groupby value="empresa" '.$chkempresa.'>x Empresa</td>
			    		<td class="texto_normal"><input type=radio name=groupby value="contabilidad" '.$chkcontab.'>x Contabilidad</td>
						<td class="texto_normal" colspan=2><input type=radio name=groupby value="documento" '.$chkdocumento.'>x Documento</td>
					</tr>';
    			echo $tabla;
			echo '
	 			<td style="text-align:center;">';
		 			$chktiempo="";
		 			$chkcontab = "";
		 			$chkdocumento="";
		 			
		 			$chktiempo=validacheck('tiempo',$groupbysecond);
		 			$chkcontab=validacheck('contabilidad',$groupbysecond);
		 			$chkdocumento=validacheck('documento',$groupbysecond);
		 			$chkempresa=validacheck('empresa',$groupbysecond);
		 			
					$tabla= '
							<table border=0 class="titulos_sub_principales" bordercolor="B2B2B2"><tr><td colspan=1>
								<td class="titulos_sub_principales">
    							<img title="2da dimension" src="images/2d.png" width=20 border=0 >
    																		</td>
						<td> <input type=radio name=groupbysecond value="tiempoSD" '.$chktiempo.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Tiempo
							<select name="tiempoSD" onchange="actualizaGroupBySecond(this);">
								<option value="anio" '; if($groupbysecond=="anio"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>A&#209;o</option>
								<option value="mes" '; if($groupbysecond=="mes"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Mes</option>
								<option value="trimestre" '; if($groupbysecond=="trimestre"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Trimestre</option>
								<option value="cuatrimestre" '; if($groupbysecond=="cuatrimestre"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Cuatrimestre</option>
								<option value="periodofiscal" '; if($groupbysecond=="periodofiscal"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Periodo Fiscal</option>
							</select>	
						</td>
						<td> <input type=radio name=groupbysecond value="empresaSD" '.$chkempresa.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Empresa
							 <select name="empresaSD" onchange="actualizaGroupBySecond(this);">
								<option value="nombrerazonsocial" '; if($groupbysecond=="nombrerazonsocial"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Razon Social</option>
								<option value="nombreregion" '; if($groupbysecond=="nombreregion"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Region</option>
								<option value="nombredepartamento" '; if($groupbysecond=="nombredepartamento"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Departamento</option>
								<option value="nombrearea" '; if($groupbysecond=="nombrearea"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Area</option>
								<option value="nombreunidadnegocio" '; if($groupbysecond=="nombreunidadnegocio"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Unidad de Negocio</option>
							</select>
						</td>
						<td> <input type=radio name=groupbysecond value="contabilidadSD" '.$chkcontab.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Cuenta
							<select name="contabilidadSD" onchange="actualizaGroupBySecond(this);">
								<option value="grupo" '; if($groupbysecond=="grupo"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Grupo</option>
								<option value="nombrerubro" '; if($groupbysecond=="nombrerubro"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Seccion</option>
								<option value="cuenta_contable" '; if($groupbysecond=="cuenta_contable"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Cuenta Contable</option>
								<option value="tipocuenta" '; if($groupbysecond=="tipocuenta"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Tipo de Cuenta</option>
								<option value="seccioncuenta" '; if($groupbysecond=="seccioncuenta"){ $tabla.= 'selected';$chkcontab='checked';} $tabla.='>Seccion Cuenta</option>
							</select>
						</td>
						<td><input type=radio name=groupbysecond value="documentoSD" '.$chkdocumento.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Documento
							<select name="documentoSD" onchange="actualizaGroupBySecond(this);">
								<option value="nombredocto" '; if($groupbysecond=="nombredocto"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>Tipo Documento</option>
								<option value="foliodocto" '; if($groupbysecond=="foliodocto"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>Folio Documento</option>
							</select>
						</td>
						</tr>
						</table>
						</td>
						</tr>				
										
					';
			
    				$tablaH= '<table align="center" border=0 cellpadding="3">
						<tr>
							<td class="texto_status" colspan="4"><input type=radio name=groupbysecond value="" checked onclick="if(this.checked) document.FDatosB.allcolumns.value=1;">Una Dimension</td>
						</tr>
						<tr>
							<td class="texto_status" colspan="4">**** Filtros Segunda Dimension ****</td>
						</tr>
		    			<tr>
		    				<td class="texto_normal"><input type=radio name=groupbysecond value="tiempoSD" '.$chktiempo.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Tiempo</td>
		    				<td class="texto_normal"><input type=radio name=groupbysecond value="contabilidadSD" '.$chkcontab.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Contablidad</td>
							<td class="texto_normal"><input type=radio name=groupbysecond value="documentoSD" '.$chkdocumento.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Documento</td>
						</tr>';
    				echo $tabla;
    		echo'</td>'; 
    	echo '</tr>';      	
		echo '<tr><td colspan=2>&nbsp;</td></tr>';	  
  
		
		if(!isset($_POST['excel']))
		{
			echo "<tr>";
			echo '<td colspan=2 class="texto_normal" style="text-align:center;" nowrap>';
			
			/*
			echo '<td colspan=2 class="texto_normal" style="text-align:center;" nowrap>Tipo de Grafica:';
			
			if ($groupbysecond){
				echo '<select name="tipografica" disabled>';
			} else {
				echo '<select name="tipografica">';
			
				// Establecer los tipos de graficas que el usuario puede seleccionar
				
				$sql= "Select id, chartname, flaggeneral
							From DW_tiposgraficas
							Where active= 1
							Order by chartname";
				
				$datos= DB_query($sql, $dbDataware);
				
				while ($registro= DB_fetch_array($datos)){
					if ($_POST["tipografica"] == $registro["id"]){
						echo '<option value="'.$registro["id"].'" selected>'.$registro["chartname"];
						$muestragrafica= $registro["flaggeneral"];
					} else {
						echo '<option value="'.$registro["id"].'">'.$registro["chartname"];
					}
				}
			}
			
			echo "</select>&nbsp;&nbsp;";
			*/
		
			$sql = "Select * from DWH_userfilters
					WHERE userid = '".$_SESSION['UserID']."'
						and proyecto='".$funcion."'";
			
			$rs = DB_query($sql,$dbDataware);
			
			if (DB_num_rows($rs) > 0){
				echo '&nbsp;&nbsp;Filtros Almacenados: &nbsp; <select name="filterlist">
					<option value="">Sin filtro...</option>';
		
				while ($rsrows = DB_fetch_array($rs)){
					$filtername = $rsrows['filtername'];
					$sel="";
					
					if ($filtername==$_REQUEST['filterlist'])
						$sel="selected";
		
					echo '<option value="'.$filtername.'" '.$sel.'>'.$filtername.'</option>';
				}
		
				echo '</select>';
		
				echo '&nbsp;&nbsp;<a href="javascript:DeleteFilter();"><img src="part_pics/Delete.png" border=0 title="Eliminar filtro guardado"></a>';
				echo "&nbsp;&nbsp;<a href='javascript:SendFilter(" . $funcion . ");'><img src='part_pics/Mail-Forward.png' border='0' title='Enviar filtro a otros usuarios'></a>";
				echo '<input type="text" id="namenewfilter" name="namenewfilter" size="40">
					  <button style="border:0; background-color:transparent;" value="Guardar Filtro" name="btnfilter" onclick="saveNewFilter();"><img src="images/guardar.png" width=60 ALT="Guardar"></button>';
			}
			else
			{
				echo '<input type="text" id="namenewfilter" name="namenewfilter" size="60">';
				echo '<button style="border:0; background-color:transparent;" value="Guardar Filtro" name="btnfilter" onclick="saveNewFilter();"><img src="images/guardar.png" width=60 ALT="Guardar"></button>';
			}
			//poner link para eliminar filtros almacenados         boton para guardar filtro <input type="button" value="Guardar Filtro" name="btnfilter" onclick="saveNewFilter();">
			echo '</td></tr>';
		}
		
		echo '<tr><td colspan=2>&nbsp;</td></tr>';
		
		
		echo '<tr>';
		echo '<td style="text-align:center;" colspan="2">';
		echo '<button style="border:0; background-color:transparent;" name="procesar" value="GENERAR"><img src="images/buscar.png" height="40" ALT="Procesar"></button>&nbsp;<button style="border:0; background-color:transparent;" value="REINICIAR" name="reiniciar"><img src="images/reiniciar2.png" height="40" ALT="Reinicia"></button>&nbsp;<button style="cursor:pointer; border:0; background-color:transparent;" name="excel" value="EXCEL"><img src="images/exportar1.png" height="40" ALT="Exportar a Excel" title="Exportar Tablero a Excel"></button>';
		//echo'<input type="submit" value="GENERAR" name="procesar">&nbsp;<input type="submit" value="REINICIAR" name="reiniciar">';
		//echo '<button style="cursor:pointer; border:0; background-color:transparent;" name="excel" value="EXCEL"><img src="images/exportar.png" width="100" ALT="Exportar a Excel" title="Exportar Tablero a Excel"></button>';
		//echo'&nbsp;<input type="submit"  value="EXCEL" name="excel">'; //onclick="ExportaExcel();"
		echo '</td>';
  echo '</tr>';
  $condicion = str_replace("'","$",$condicion);
  echo '</table>';
			
		
}//noexcel

$esexcel=false;
if (isset($_POST['excel'])){
	$esexcel=true;
	header("Content-type: application/ms-excel");
	# replace excelfile.xls with whatever you want the filename to default to
	header("Content-Disposition: attachment; filename=DatawareVentas2D.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';

}

$colVisibles=0;
for($j=1;$j<=$totCol;$j++)
	if($_SESSION['chkcol'][$j])
		$colVisibles++;
	
// echo var_dump($_SESSION['chkcol']);

echo "<br>";
echo '<table width=95% border=1  bordercolor=lightgray align="center" cellspacing=0 cellpadding=3>';
$colsp = ((((count($_SESSION['topcolumns'])+1)*$colVisibles)+3)>$totCol)?(((count($_SESSION['topcolumns'])+1)*$colVisibles)+3):$totCol;
	
	if($_GET['Excluir'] == 1 or isset($_SESSION['condnameconta'])){
		$confname = explode("@", $_SESSION['condnameconta']);
		$conarray =array();
		$confnamedesplegar = "";
		$y=0;
		foreach($confname as $confvalor){
			if(in_array($confvalor, $conarray) == false){
				$y = $y +1;
				$confnamedesplegar = $confnamedesplegar.$confvalor;
	
				$conarray[$y] = $confvalor;
			}
		}
		echo '<td colspan='.$colsp.' >'.$confnamedesplegar;
	
	}else{
		echo '<td colspan='.$colsp.' >';
	}

		//echo '<td colspan='.$colsp.' >';
			echo '<table width=95% border=0 cellspacing=0 cellpadding=3 >';
				echo '<tr valign="top">';
					echo '<td class="td_style">';
						echo '<table border="0" bordercolor=lightgray cellspacing="0" cellpadding="0">';
							if (strlen($wherecond)>0){
								//$condicion.=" ".str_replace("'","$",$wherecond);
							}
							if (strlen($condicion)>0 and !$esexcel){
								//echo "<pre>$condicion";
				                $x=array();
				                $x=explode('AND',$condicion);
								$savefilter=false;
				
				  				for ($z=1; $z<count($x); $z++)
				  				{
									$fijo=$x[$z];
									if ($fijo!=$fijoant) 
									{
										$savefilter=true;
										$sesionfijo	= str_replace("$","",$_SESSION['valorfijo']);
										$sesionfijo	= str_replace("AND","",$sesionfijo);
										$fijover=str_replace("$","",$fijo);
										
										if (trim($sesionfijo) == trim($fijover))
										{
											$newcondicion = str_replace($_SESSION['valorfijo'],"",$condicion);
											$ligados="".$report.'?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.
												'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
												.$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' . $groupby.'&keepvisible=1&groupbysecond='.$groupbysecond.'&novalorfijo=1&Ordenar=asc&condicion='.$newcondicion;
											$src="images/cancel.gif";
											echo '<tr style="background-color:#9da791;"><td><a href="'.$ligados.'">';
												echo '<img title="quitar" src="'.$src.'" border=0></a>';
										} else {
											$fijo2 = "AND".$fijo;	
											$newcondicion = str_replace($fijo2,"",$condicion);
											
											$ligados="".$report.'?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.
												'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
												.$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' . $groupby.'&keepvisible=1&groupbysecond='.$groupbysecond.'&Ordenar=asc&condicion='.$newcondicion;
											
											$src="images/cancel.gif";
											echo '<tr><td class="td_style2"><a href="'.$ligados.'">';
											echo '<img title="quitar" src="'.$src.'" border=0></a>&nbsp;&nbsp;';
											
											$ligados="".$report.'?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.
												'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
												.$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' . $groupby.'&keepvisible=1&groupbysecond='.$groupbysecond.'&valorfijo=$AND '.$fijover. '$&Ordenar=asc' ;
											$src="part_pics/Fill-Right.png";
											
											echo '<a href="'.$ligados.'">';
												echo '<img title="fijar" src="'.$src.'" border=0></a>&nbsp;';
										}
										//echo "<pre>valorfijo=\$AND $fijover \$";
										//echo "<br>-->" . $fijover . "<--<br>";
										$fijover=TraeTitulo($fijover);
										
										echo $textover.' &nbsp '.$fijover;
										$_SESSION["cadenafiltro"].= $textover.' &nbsp '.$fijover."<br>";
										$_SESSION["filtrodw"].= "&nbsp;[&nbsp;<b>".$fijover. "</b>&nbsp;]&nbsp;<b>=></b>";
									}
									echo '</td>';						
									echo '</tr>';
									$fijoant=$fijo;
								}
				  				echo '</table>
				  					</td>';
								echo '</tr>
				  					</table>';  
							}
							
							echo '</td></tr>';
          					echo '<tr>';
            					echo '<td class="titulos_principales"><b>#</b>&nbsp;&nbsp;
            							<input type="checkbox" name="chkExcluirTodos" onclick="javascript:selAll(this);">';
// 									echo '<a href="dwh_ReportePresupuestos_V3_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
// 											.$ToMes. '&ToYear=' . $ToYear .'&OrdenarPor=' .$groupby. '&Excluir=1&Ordenar=asc&filtro='.$groupby.'&condicionante=^'.
// 											'&valor=Multiple&condicion='. $condicion .'">
// 									 		<br><br>&nbsp;&nbsp;&nbsp;&nbsp;';
									echo "<br><br>&nbsp;&nbsp;&nbsp;&nbsp;<button style='cursor:pointer; border:0; background-color:transparent;' name='excluir'><img src='images/cancel.gif' ALT='Agregar Nuevo' title='Agregar Nuevo Objetivo' tabindex=0></button></a>";
									echo'</td>';
									
								$colspanini=1;

								echo '<td class="titulos_principales">';
				  					if ($esexcel)
				  						echo '<b><u>' . $titulo2;
				  					else
				  					{
				  						echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3><tr>';
				  						
// 				  						if ($muestragrafica == 1){
// 				  							echo '<td>';
// 				  							echo '<a target="_blank" href="PDFGraficaDWContable_1D.php?dato=Todos&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'"><img src="images/grafica3.png" border=0 title="Grafica De Presupuestos" width="20" height="25"></a>&nbsp;';
// 				  							echo '</td>';
// 				  						}
				  						
				  						echo '<td class="titulos_principales">';
	              						echo '<a href="'.$report.'?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' .$groupby. '&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
	                					echo '<b><u><font color="white">' . $titulo2 ;
	              						echo '</a>';
	              						echo '</td></tr></table>';
	              						
	              					}
	              					/*echo '&nbsp;&nbsp;&nbsp;';*/
	            					// apartado de la grafica
		            				if (!$esexcel){
										/*
										$ligados='GraficaDWH_Documentos.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' .$groupby.'&valorfijo=AND '.$_SESSION['valorfijo']. '&Ordenar='.$sigOrdenar .'&condicion='.$condicion;
										echo '<a href="'.$ligados.'" target="_blank" >';
												echo '<img src="part_pics/Chart.png" border=0 alt="Grafica De Ventas">';       
										echo '</a>';
										*/
									}
              					echo '</td>';
              					
              					//echo "groupbysecond:".$groupbysecond;
              					
								if ($groupbysecond){
									foreach($_SESSION['topcolumns'] as $namecol){
										if (($groupbysecond=='mes')){
											$nombrecol = glsnombremeslargo($namecol);
										}elseif ($groupbysecond=='Fechacaptura'){
											$nombrecol = substr($namecol, 0,10);
										}else{
											$nombrecol = $namecol; 
										}
										
										echo '<td class="titulos_principales" colspan="'.$colVisibles.'" nowrap>'.$nombrecol.'</td>';
	
									}
									//totales por filas
									echo '<td class="pie_derecha" colspan="'.$colVisibles.'" style="text-align:center;">TOTALES</td>';
			
								}else{
									/*Saldo  Inicial*/
									if ($_SESSION['chkcol'][1]){
										echo '<td class="titulos_principales"><font color="white">';
										if ($namecol || $esexcel)
											echo '<b>'._('Saldo Inicial');
										else{
											echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>';
											echo "<tr class='titulos_principales'>";
											echo '<td>';
											echo '<input type="checkbox" name="chkcol1" checked>';
											echo '</td>';
											echo '<td>';
											echo '<a href="'.$report.'?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=saldoinicial&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
											echo '<b><u><font color="white">'._('Saldo Inicial');
											echo '</a>';
											echo '</td>';
											echo '</tr></table>';
										}
										echo '</td>';
									} 
									
									if ($_SESSION['chkcol'][2]){
										echo '<td  class="titulos_principales"> <font color="white">';
										if ($namecol || $esexcel)
											echo '<b>'. _('Cargos');
										else{
											echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>';
											echo "<tr class='titulos_principales'>";
											echo '<td>';
											echo '<input type="checkbox" name="chkcol2" checked >';
											echo '</td>';
											echo '<td>';
											echo '<a href="'.$report.'?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=cargo&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
											echo '<b><u><font color="white">'. _('Cargos');
											echo '</a>';
											echo '</td>';
											echo '</tr>';
											echo '</table>';
										}
										echo '</td>';
									}
									
									if ($_SESSION['chkcol'][3]){
										echo '<td class="titulos_principales"> <font color="white">';
										if ($namecol || $esexcel) 
											echo '<b>'._('Abonos');
										else{
											echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>';
											echo "<tr class='titulos_principales'>";
											echo '<td>';
											echo '<input type="checkbox" name="chkcol3" checked >';
											echo '</td>';
											echo '<td>';
											echo '<a href="'.$report.'?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=abono&Ordenar='.$sigOrdenar. '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
											echo '<b><u><font color="white">'._('Abonos');
											echo '</a>';
											echo '</td>';
											echo '</tr>';
											echo '</table>';
										}
										echo '</td>';
									}
									
									if ($_SESSION['chkcol'][4]){
										echo '<td class="titulos_principales"> <font color="white">';
										if ($namecol || $esexcel)
											echo '<b>'._('Saldo Periodo');
										else{
											echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>';
											echo "<tr class='titulos_principales'>";
											echo '<td>';
											echo '<input type="checkbox" name="chkcol4" checked >';
											echo '</td>';
											echo '<td nowrap>';
											echo '<a href="'.$report.'?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=saldofinal&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
											echo '<b><u><font color="white">'._('Saldo Periodo');
											echo '</a>';
											echo '</td>';
											echo '</tr>';
											echo '</table>';
										}
										echo '</td>';
									} 
									
									if ($_SESSION['chkcol'][5]){
										echo '<td class="titulos_principales"> <font color="white">';
										if ($namecol || $esexcel)
											echo '<b>'._('Saldo Final');
										else{
											echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>';
											echo "<tr class='titulos_principales'>";
											echo '<td>';
											echo '<input type="checkbox" name="chkcol5" checked >';
											echo '</td>';
											echo '<td>';
											echo '<a href="'.$report.'?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=saldofinal&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
											echo '<b><font color="white">'._('Saldo Final');
											echo '</a>';
											echo '</td>';
											echo '</tr>';
											echo '</table>';
										}
										echo '</td>';
									}
								}
								
							echo '</tr>';
			
							if ($groupbysecond){
								echo '<tr>';
								//if(!(array_search($groupby,$arrcolscondescripcion)===FALSE)){
										echo '<td class="texto_normal" colspan="' . ($colspanini+1) . '">&nbsp;</td>';
// 									}else{
// 										echo '<td class="texto_normal" colspan="'.$colspanini.'">&nbsp;</td>';
// 									}				
									
									for($ititle=1;$ititle <= count($_SESSION['topcolumns'])+1;$ititle++){ //count + 1 para poner totales a la derecha
										
										if ($_SESSION['chkcol'][1]){
											echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
											echo '<b>'._('Saldo Inicial');
											echo '</td>';
										}
										
										if ($_SESSION['chkcol'][2]){
											echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
											echo '<b>'._('Cargo');
											echo '</td>';
										}
										if ($_SESSION['chkcol'][3]){				
											echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
												 echo '<b>'._('Abono');
											echo '</td>';
										}
										if ($_SESSION['chkcol'][4]){
											echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
											echo '<b>'._('Saldo');
											echo '</td>';
										}
										if ($_SESSION['chkcol'][5]){
											echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
											echo '<b>'._('Saldo Final');
											echo '</td>';
										}
										
									}
								echo '</tr>';
							}
			
	        				$condicion = str_replace("$","'",$condicion);
	        				
	        				// echo "<br>condicion:".$condicion;
							
							if ($groupbysecond){
								$groupbysecondvalue = $groupbysecond;
								$groupbysecond=",".$groupbysecond;
								$OrdenarPor = "header";
							}
							
							$columnatipodocto="";
							if ($groupby == "foliodocto") {
								$columnatipodocto= ", d.tipodocto";
							}
							
							$agrupador2= $groupbysecond;
							
							if(empty($groupbysecond)) {
								$agrupador2= ", null as header_2";
							}

				            /* Calcula montos iniciales */////
			            	$dsql = "DELETE FROM DW_ContabilidadSaldosIniciales WHERE usuario= '".$_SESSION["UserID"]."'";
			            	$dresult = DB_query($dsql, $dbDataware);
			            	
			            	if ($groupby != "foliodocto") {
				            	$isql = "INSERT INTO DW_ContabilidadSaldosIniciales(header, usuario, montoinicial)
				            			SELECT " . $groupby." as header, '".$_SESSION["UserID"]."' 
											, SUM(cargos-abonos) as saldoinicial
				            			FROM DWH_Contabilidad d
										WHERE d.fecha < '" . $fechaini . "'" . $condicion." ".$wherecond . " 
										GROUP BY " . $groupby;
				            	
				            	//echo "<pre>".$isql."<br>";
				            	
				            	$iresult = DB_query($isql, $dbDataware);
			            	}
				            
            				// Formar consulta que genera el tablero
            				if($groupby == "cuenta_contable"){
            					$sql = "SELECT nombrecuenta as header";
            				}else{
            					$sql = "SELECT " . $groupby ." as header";
            				}
            				if($groupbysecond == "cuenta_contable"){
            					$sql .= "SELECT nombrecuenta";
            				}else{
            					$sql .= " ".$groupbysecond." ";
            				}
            					$sql .=" ,Case When h.montoinicial Is Null Then 0 Else h.montoinicial End as 'saldoinicial'
								, SUM(cargos) as cargo
								, SUM(abonos) as abono
								, SUM(cargos-abonos) as saldo
								, (Case When h.montoinicial Is Null Then 0 Else h.montoinicial End) + SUM(cargos-abonos) as saldofinal
            					, -1 as naturaleza";
            				
            				$sql.= ", " . $groupby . ", " . $groupby . " as txtheader".$columnatipodocto;
            				
            				$agrupador2= "";
            				
            				if(!empty($groupbysecond)) { 
            					$agrupador2= " AND d.".str_replace(",","", $groupbysecond)." = h.header_2";
            				} 
            				
            				$sql .= " FROM DWH_Contabilidad d
            						  LEFT JOIN DW_ContabilidadSaldosIniciales h ON h.header = d." . $groupby;	
            				
            				$sql .= " WHERE d.fecha between '" .$fechaini."' AND '".$fechafin."'";
            				
				            $sql .= $condicion." ".$wherecond;
				            $sql .= " GROUP BY " . $groupby.$groupbysecond ;
				            
				            // Unir consulta con los datos faltantes de acuerdo al filtro excepto por folio del documento
				            if ($groupby != "foliodocto") {
					            $sql .= " UNION ";
					            
					            $columna2dimension= "";
					            $condicion2dimension="";
					            if(!empty($groupbysecond)) {
					            	$columna2dimension= ", (Select Distinct ".str_replace(",","",$groupbysecond)." FROM DWH_Contabilidad WHERE fecha between '".$fechaini."' AND '".$fechafin."' Order by ".str_replace(",","",$groupbysecond)." Limit 1) as header_2";
					            }
					            
						        $sql .= "Select d.header".$columna2dimension.", d.montoinicial as saldoinicial, 0 as cargo, 0 as abono, 0 as saldo, 
						        			d.montoinicial as saldofinal, -1 as naturaleza, d.header as ".$groupby.", d.header as txtheader
							            From DW_ContabilidadSaldosIniciales d
							            Left Join (Select Distinct ".$groupby."
							            FROM DWH_Contabilidad
							            WHERE fecha between '".$fechaini."' AND '".$fechafin."' "; 
							    
						        $sql .= $condicion." ".$wherecond.") as faltante on d.header= faltante.".$groupby;
							           	
						        $sql .= " Where faltante.".$groupby." Is Null ";
				            }
				            
				            $sql .= " ORDER BY ".$OrdenarPor." ". $Ordenar." ".$groupbysecond;
            
				            $result = DB_query($sql, $dbDataware);  // ejecuta la consulta
				            
            				//echo "<pre>".$sql;
				            echo "<input type=hidden name='filtroexcluir' value='".$groupby."'>";
				            
				            $i=0;
				            $condicion = str_replace("'","$",$condicion);
							$header="--";
							$indSegDim = 1;
							$arrTotales=array();
							$arrRowTotal=array();
							$arrRowTotalT=array();
							$arrSumaNaturaleza = array();
							$arrContNaturaleza = array();
							
							$tottopcol = count($_SESSION['topcolumns']);  // total de columnas para la segunda dimension
							
							$sumanaturaleza = 0;
							$contnaturaleza = 0;
							$naturalezaant = -1;
							
            				while ($myrow=DB_fetch_array($result))
            				{
                				if ($header != $myrow['header'])
                				{
									if ($header!="--")
									{
										//revisar que esten escritas todas las topcolumnas
										for($k=$indSegDim;$k<=$tottopcol;$k++){
											echo "<td colspan='".$colVisibles."'>&nbsp;</td>";
										}
										
										//poner totales por filas
										if ($groupbysecond)
										{
											if ($_SESSION['chkcol'][1]){
												$arrRowTotalT[1]+=$arrRowTotal[$i][1];
												if(!(array_search($groupby,$arrcolscontables)===FALSE)){
													if ($naturalezaant <= 0.5 ){
														if($arrRowTotal[$i][1] < 0) {
															echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'><b>(" . number_format(abs($arrRowTotal[$i][1]),0) . ")" . "</td>";
														}else{
															echo "<td style='text-align:right;' width='15%' class='numero_celda'><b>" . number_format($arrRowTotal[$i][1],0) . "</td>";
														}
													}else{//
														if(($arrRowTotal[$i][1]) < 0){
															echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'><b>(" . number_format($arrRowTotal[$i][1],0) . ")</td>";
														}else{
															echo "<td style='text-align:right;' width='15%' class='numero_celda'><b>" . number_format(abs($arrRowTotal[$i][1]),0) . "</td>";
														}
													}
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'><b>" . number_format($arrRowTotal[$i][1],0) . "</td>";
												}
											}
											if ($_SESSION['chkcol'][2]){
												$arrRowTotalT[2]+=$arrRowTotal[$i][2];
												
													echo '<td style="text-align:right;" class="numero_celda">';
														 echo '<b>'.number_format($arrRowTotal[$i][2],0);					 	
													echo '</td>';
												
											}
											if ($_SESSION['chkcol'][3]){
												$arrRowTotalT[3]+=$arrRowTotal[$i][3];
													echo '<td style="text-align:right;" width="15%" class="numero_celda">';
														 echo '<b>'.number_format($arrRowTotal[$i][3],0);
													echo '</td>';
												
											}
											if ($_SESSION['chkcol'][4]){
												$arrRowTotalT[4]+=$arrRowTotal[$i][4];
												if(!(array_search($groupby,$arrcolscontables)===FALSE)){
													if ($naturalezaant <= 0.5 ){
														if($arrRowTotal[$i][4] < 0){
															echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'><b>(" . number_format(abs($arrRowTotal[$i][4]),0) . ")" . "</td>";
														}else{
															echo "<td style='text-align:right;' width='15%' class='numero_celda'><b>" . number_format($arrRowTotal[$i][4],0) . "</td>";
														}
													}else{
														if(($arrRowTotal[$i][4]) < 0){
															echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'><b>(" . number_format($arrRowTotal[$i][4],0) . ")</td>";
														}else{
															echo "<td style='text-align:right;' width='15%' class='numero_celda'><b>" . number_format(abs($arrRowTotal[$i][4]),0) . "</td>";
														}
													}
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'><b>" . number_format($arrRowTotal[$i][4],0) . "</td>";
												}
											}
											if ($_SESSION['chkcol'][5]){
												$arrRowTotalT[5]+=$arrRowTotal[$i][5];
												if(!(array_search($groupby,$arrcolscontables)===FALSE)){
													if ($naturalezaant <= 0.5 ){
														if($arrRowTotal[$i][5] < 0){
															echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'><b>(" . number_format(abs($arrRowTotal[$i][5]),0) . ")" . "</td>";
														}else{
															echo "<td style='text-align:right;' width='15%' class='numero_celda'><b>" . number_format($arrRowTotal[$i][5],0) . "</td>";
														}
													}else{
														if(($arrRowTotal[$i][5]) < 0){
															echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'><b>(" . number_format($arrRowTotal[$i][5],0) . ")</td>";
														}else{
															echo "<td style='text-align:right;' width='15%' class='numero_celda'><b>" . number_format(abs($arrRowTotal[$i][5]),0) . "</td>";
														}
													}
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'><b>" . number_format($arrRowTotal[$i][5],0) . "</td>";
												}
											}
											
										}//if dobledim
										echo "</tr>";
									}
									
									$i=$i+1;
                					$indSegDim=1;
                					
									echo "<tr>";
				  					echo "<td style='text-align:center;' nowrap>";
									echo $i;
									echo '&nbsp;&nbsp;<input type="checkbox" name="excluye[]" id="chk'.$i.'" value="'.$myrow[0].'">';
				  					echo "</td>";
					  					
					  				$header = $myrow['header'];
					  					
									echo "<td nowrap><font size=3>";
									
									if ($groupby == "mes") {
										$ver= strtoupper(glsnombremeslargo($myrow['header']));
									} else {
					  					$ver= strtoupper($myrow['header']);
									}
									
									$v = $ver;
									
									/*
									$concat = "";
									if (($groupby=="genero" or $groupby=="grupo") and $myrow[16]!=""){
										$concat = "[".$myrow[16]."]";
									}
									*/
									$naturalezaant = $myrow['naturaleza'];
									
									if ($esexcel)
										echo "<u>" . $ver;	
									else 
									{
										$cond=" AND $groupby=\$".$myrow['header']."\$";
										
										echo '<table border=0 width=100%>';
										echo "<tr>";
										
										echo '<td width="10%">';
										echo '<a href="'.$report.'?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
												.$ToMes. '&ToYear=' . $ToYear .
												'&OrdenarPor=' .$groupby. '&Excluir=1&Ordenar=asc&filtro='.$groupby.'&condicionante=^'.
												'&valor='.$myrow[0].'&condicion='. $condicion .'" >';
										echo '<img src="images/cancel.gif" WIDTH=12 HEIGHT=12  alt="Excluir">&nbsp;&nbsp;</a>';
										echo '</td>';
										
										echo '<td style="text-align:left;" width="80%">&nbsp;&nbsp;';
										echo "<a href='".$report."?procesar=GENERAR&groupby=".$sig_groupby."&FromDia=".$FromDia."&FromMes=" .$FromMes. "&FromYear=" . $FromYear ."&ToDia=".$ToDia."&ToMes="
											.$ToMes. "&ToYear=" . $ToYear .
											"&OrdenarPor=" .$sig_groupby. "&Ordenar=asc&filtro=".$groupby."&groupbysecond=".$groupbysecondvalue."&condicion=". $condicion.$cond ."&keepvisible=1' >";
										echo "<u>" . $ver;
										echo "</a>";
										echo '</td>';
										
										if ($groupby == "foliodocto") {
											echo "<td width='10%'>";
											echo "<a target='blank' href='PrintJournal.php?PrintPDF=1&TransNo=".$myrow['header']."&type=".$myrow["tipodocto"]."'><img src='images/detalle.png' width=12 height=12 alt='Imprimir Documento'></a>";
											echo "</td>";
										}
										
										echo '</tr>';
										echo '</table>';
									}
				  					echo "</td>";
				  				} 
				
								if ($groupbysecond)
								{			
					  				//buscar que columna trae el registro
					  				for($index=$indSegDim; $index<=count($_SESSION['topcolumns']); $index++)
					  				{
										if ($myrow[1] == $_SESSION['topcolumns'][$index])
										{
											if ($_SESSION['chkcol'][1])
											{
												if($index==1){
													$saldoinicial = $myrow['saldoinicial'];
													$saldofinal = ($myrow['saldoinicial'] + $myrow['saldo']);
													$arrRowTotal[$i][1] =  $myrow['saldoinicial'];
												}else{
													$saldoinicial = $saldofinal;
													$saldofinal = ($saldoinicial + $myrow['saldo']);
												}
												
												if(($saldoinicial) < 0){
													echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format(abs($saldoinicial),0) . ")</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($saldoinicial,0) . "</td>";
												}
												
												// $arrRowTotal[$i][1] =  $saldofinal;
											}
											
											if ($_SESSION['chkcol'][2]){
												echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format(abs($myrow['cargo']),0) . "</td>";
												$arrRowTotal[$i][2] += $myrow['cargo'];
											}
											
											if ($_SESSION['chkcol'][3]){
												echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['abono'],0) . "</td>";
												$arrRowTotal[$i][3] += $myrow['abono'];
											}
											
											if ($_SESSION['chkcol'][4])
											{
												if(($myrow['saldo']) < 0){
													echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format(abs($myrow['saldo']),0) . ")</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format(abs($myrow['saldo']),0) . "</td>";
												}
												
												$arrRowTotal[$i][4] += $myrow['saldo'];
											}
											
											if ($_SESSION['chkcol'][5])
											{
												if(($myrow['saldo']+$saldoinicial) < 0){
													echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format(abs($myrow['saldo']+$saldoinicial),0) . ")</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format(abs(($myrow['saldo']+$saldoinicial)),0) . "</td>";
												}
												
												$arrRowTotal[$i][5] = ($myrow['saldo']+$saldoinicial);
											}
											
											break;
										}else{ 
											echo "<td colspan='".$colVisibles."'>&nbsp;</td>";
										}
									} // Fin de ciclo que recorre las columnas de la segunda dimension
					  				
									$indSegDim = $index;
									// acumula saldo inicial de segunda dimension
									$arrTotales[$indSegDim]['sumasaldoinicial'] += $saldoinicial;
									$arrTotales[$indSegDim]['sumasaldofinal'] += ($myrow['saldo']+$saldoinicial);
								}
								else
								{
									$sumanaturaleza = $sumanaturaleza + $myrow['naturaleza'];
									$contnaturaleza++;
									
									if ($_SESSION['chkcol'][1])
										if(!(array_search($groupby,$arrcolscontables)===FALSE)){
											if ($myrow['naturaleza'] <= 0.5 ){
												if($myrow['saldoinicial'] < 0){
													echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format(abs($myrow['saldoinicial']),0) . ")" . "</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['saldoinicial'],0) . "</td>";
												}
											}else{
												if($myrow['saldoinicial'] < 0){
													echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format($myrow['saldoinicial'],0) . ")</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format(abs($myrow['saldoinicial']),0) . "</td>";
												}	
											}
										}else{
											echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['saldoinicial'],0) . "</td>";
										}
									
									if ($_SESSION['chkcol'][2])
										echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['cargo'],0) . "</td>";
									
									if ($_SESSION['chkcol'][3])
										echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['abono'],0) . "</td>";
									
									if ($_SESSION['chkcol'][4])
										if(!(array_search($groupby,$arrcolscontables)===FALSE)){
											if ($myrow['naturaleza'] <= 0.5 ){
												if($myrow['saldo'] < 0){
													echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format(abs($myrow['saldo']),0) . ")" . "</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['saldo'],0) . "</td>";
												}
											}else{
												if($myrow['saldo'] < 0){
													echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format($myrow['saldo'],0) . ")</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format(abs($myrow['saldo']),0) . "</td>";
												}
											}
										}else{
											echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['saldo'],0) . "</td>";
										}
										
									if ($_SESSION['chkcol'][5])
									{
										if(!(array_search($groupby,$arrcolscontables)===FALSE)){
											if ($myrow['naturaleza'] <= 0.5 ){
												if(($myrow['saldo']+$myrow['saldoinicial']) < 0){
													echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format(abs(($myrow['saldo']+$myrow['saldoinicial'])),0) . ")" . "</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format(($myrow['saldo']+$myrow['saldoinicial']),0) . "</td>";
												}
											}else{
												if((($myrow['saldo']+$myrow['saldoinicial'])) < 0){
													echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format(($myrow['saldo']+$myrow['saldoinicial']),0) . ")</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format(abs(($myrow['saldo']+$myrow['saldoinicial'])),0) . "</td>";
												}
											}
										}else{
											echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format(($myrow['saldo']+$myrow['saldoinicial']),0) . "</td>";
										}
									}
									// acumula saldo inicial de una dimension
									$arrTotales[$indSegDim]['sumasaldoinicial'] += $myrow['saldoinicial'];
									$arrTotales[$indSegDim]['sumasaldofinal'] += ($myrow['saldo']+$myrow['saldoinicial']);
								}
                
				                $arrTotales[$indSegDim]['sumacargo'] += $myrow['cargo'];
				                $arrTotales[$indSegDim]['sumaabono'] += $myrow['abono'];
				                $arrTotales[$indSegDim]['sumasaldo'] += $myrow['saldo'];
				                
				                
				                $arrSumaNaturaleza[$indSegDim][1] += $myrow['naturaleza'];
				                $arrContNaturaleza[$indSegDim][1]++;
				                
				                $arrSumaNaturaleza[$indSegDim][4] += $myrow['naturaleza'];
				                $arrContNaturaleza[$indSegDim][4]++;
				                
				                $arrSumaNaturaleza[$indSegDim][5] += $myrow['naturaleza'];
				                $arrContNaturaleza[$indSegDim][5]++;
				                
				 				$indSegDim++;
				 			} //while
				 			
							$totrows = $i;
							
							//revisar que esten escritas todas las topcolumnas
							for($k=$indSegDim;$k<=count($_SESSION['topcolumns']);$k++){
								echo "<td colspan='".$colVisibles."'>&nbsp;</td>";
							}
							
							//poner totales por filas
							if ($groupbysecond){		
								if ($_SESSION['chkcol'][1]){
									$arrRowTotalT[1]+=$arrRowTotal[$i][1];
									if(!(array_search($groupby,$arrcolscontables)===FALSE)){
										if ($naturalezaant <= 0.5 ){
											if($arrRowTotal[$i][1] < 0){
												echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format(abs($arrRowTotal[$i][1]),0) . ")" . "</td>";
											}else{
												echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($arrRowTotal[$i][1],0) . "</td>";
											}
										}else{
											if(($arrRowTotal[$i][1]) < 0){
												echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format($arrRowTotal[$i][1],0) . ")</td>";
											}else{
												echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format(abs($arrRowTotal[$i][1]),0) . "</td>";
											}
										}
									}else{
										echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($arrRowTotal[$i][1],0) . "</td>";
									}
									
								}
								if ($_SESSION['chkcol'][2]){
									$arrRowTotalT[2]+=$arrRowTotal[$i][2];					
									echo '<td style="text-align:right;" width="10%" class="numero_celda">';
										 echo '<b>'.number_format($arrRowTotal[$i][2],0);					 	
									echo '</td>';
								}
								if ($_SESSION['chkcol'][3]){					
									$arrRowTotalT[3]+=$arrRowTotal[$i][3];					
									echo '<td style="text-align:right;" width="15%" class="numero_celda">';
										 echo '<b>'.number_format($arrRowTotal[$i][3],0);
									echo '</td>';
								}
								
								if ($_SESSION['chkcol'][4]){
									$arrRowTotalT[4]+=$arrRowTotal[$i][4];
									if(!(array_search($groupby,$arrcolscontables)===FALSE)){
										if ($naturalezaant <= 0.5 ){
											if($arrRowTotal[$i][4] < 0){
												echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format(abs($arrRowTotal[$i][4]),0) . ")" . "</td>";
											}else{
												echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($arrRowTotal[$i][4],0) . "</td>";
											}
										}else{
											if(($arrRowTotal[$i][4]) < 0){
												echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format($arrRowTotal[$i][4],0) . ")</td>";
											}else{
												echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format(abs($arrRowTotal[$i][4]),0) . "</td>";
											}
										}
									}else{
										echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($arrRowTotal[$i][4],0) . "</td>";
									}
								}
								
								if ($_SESSION['chkcol'][5]){
									$arrRowTotalT[5]+=$arrRowTotal[$i][5];
									if(!(array_search($groupby,$arrcolscontables)===FALSE)){
										if ($naturalezaant <= 0.5 ){
											if($arrRowTotal[$i][5] < 0){
												echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format(abs($arrRowTotal[$i][5]),0) . ")" . "</td>";
											}else{
												echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($arrRowTotal[$i][5],0) . "</td>";
											}
										}else{
											if(($arrRowTotal[$i][5]) < 0){
												echo "<td style='text-align:right; color:red;' width='15%' class='numero_celda'>(" . number_format($arrRowTotal[$i][5],0) . ")</td>";
											}else{
												echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format(abs($arrRowTotal[$i][5]),0) . "</td>";
											}
										}
									}else{
										echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($arrRowTotal[$i][5],0) . "</td>";
									}
								}
							} //if dobledim
							
							echo "</tr>";
							echo "<tr style='background-color:#3a9143;'>";
							
								//if(!(array_search($groupby,$arrcolscondescripcion)===FALSE)){
	                  				echo "<td colspan='" . ($colspanini+1) . "' class='pie_derecha'>";
	                    				echo "<b>TOTALES: ";
// 								}else{
// 									echo "<td colspan='".$colspanini."' class='pie_derecha'>";
// 									echo "<b>TOTALES : ";
// 								}
								
								echo "<input type='hidden' name='renglones' id='totrows' value=". $totrows.">";
								echo "</td>";
								
								$ind=1;
								$arrSumaNaturalezaT = array();
								$arrContNaturalezaT = array();
								
	  							foreach($_SESSION['topcolumns'] as $namecol)
	  							{
	  								if ($_SESSION['chkcol'][1]){
	  									$avgnaturaleza = ($arrSumaNaturaleza[$ind][1]/$arrContNaturaleza[$ind][1]);
	  									if ($avgnaturaleza <= 0.5 ){
	  										$arrSumaNaturalezaT[1] = $arrSumaNaturalezaT[1] + 0;
	  										$arrContNaturalezaT[1]++;
	  									}else{
	  										$arrSumaNaturalezaT[1] = $arrSumaNaturalezaT[1] + 1;
	  										$arrContNaturalezaT[1]++;
	  									}
	  									if(!(array_search($groupby,$arrcolscontables)===FALSE)){
	  										 
	  										if ($avgnaturaleza <= 0.5 ){
	  											if($arrTotales[$ind]['sumasaldoinicial'] < 0){
	  												echo "<td style='text-align:right; color:#791818;' width='15%' class='pie_derecha'>(" . number_format(abs($arrTotales[$ind]['sumasaldoinicial']),0) . ")" . "</td>";
	  											}else{
	  												echo "<td style='text-align:right;' width='15%' class='pie_derecha'>" .number_format($arrTotales[$ind]['sumasaldoinicial'],0) . "</td>";
	  											}
	  										}else{
	  											if(($arrTotales[$ind]['sumasaldoinicial']) < 0){
	  												echo "<td style='text-align:right; color:#791818;' width='15%' class='pie_derecha'>(" . number_format($arrTotales[$ind]['sumasaldoinicial'],0) . ")</td>";
	  											}else{
	  												echo "<td style='text-align:right;' width='15%' class='pie_derecha'>" . number_format(abs($arrTotales[$ind]['sumasaldoinicial']),0) . "</td>";
	  											}
	  										}
	  									}else{
	  										echo "<td style='text-align:right;' width='15%' class='pie_derecha'>" . number_format($arrTotales[$ind]['sumasaldoinicial'],0) . "</td>";
	  									}
	  									
	  								}
									if ($_SESSION['chkcol'][2]){
										echo "<td style='text-align:right;' class='pie_derecha' width='15%'>";
										echo "<b>" .number_format(($arrTotales[$ind]['sumacargo']),0);
										echo "</td>";
									}
									if ($_SESSION['chkcol'][3]){
										echo "<td style='text-align:right;' class='pie_derecha' width='15%'>";
										echo "<b>" .number_format(($arrTotales[$ind]['sumaabono']),0);
										echo "</td>";
									}
									if ($_SESSION['chkcol'][4]){
										$avgnaturaleza = ($arrSumaNaturaleza[$ind][4]/$arrContNaturaleza[$ind][4]);
										
										if ($avgnaturaleza <= 0.5 ){
											$arrSumaNaturalezaT[4] = $arrSumaNaturalezaT[1] + 0;
											$arrContNaturalezaT[4]++;
										}else{
											$arrSumaNaturalezaT[4] = $arrSumaNaturalezaT[1] + 1;
											$arrContNaturalezaT[4]++;
										}
										if(!(array_search($groupby,$arrcolscontables)===FALSE)){
											if ($avgnaturaleza <= 0.5 ){
												if($arrTotales[$ind]['sumasaldo'] < 0){
													echo "<td style='text-align:right; color:#791818;' width='15%' class='pie_derecha'>(" . number_format(abs($arrTotales[$ind]['sumasaldo']),0) . ")" . "</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='pie_derecha'>" . number_format($arrTotales[$ind]['sumasaldo'],0) . "</td>";
												}
											}else{
												if(($arrTotales[$ind]['sumasaldo']) < 0){
													echo "<td style='text-align:right; color:#791818;' width='15%' class='pie_derecha'>(" . number_format($arrTotales[$ind]['sumasaldo'],0) . ")</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='pie_derecha'>" . number_format(abs($arrTotales[$ind]['sumasaldo']),0) . "</td>";
												}
											}
										}else{
											echo "<td style='text-align:right;' width='15%' class='pie_derecha'>" . number_format($arrTotales[$ind]['sumasaldo'],0) . "</td>";
										}
									}
									if ($_SESSION['chkcol'][5]){
										$avgnaturaleza = ($arrSumaNaturaleza[$ind][5]/$arrContNaturaleza[$ind][5]);
										if ($avgnaturaleza <= 0.5 ){
											$arrSumaNaturalezaT[5] = $arrSumaNaturalezaT[5] + 0;
											$arrContNaturalezaT[5]++;
										}else{
											$arrSumaNaturalezaT[5] = $arrSumaNaturalezaT[5] + 1;
											$arrContNaturalezaT[5]++;
										}
										if(!(array_search($groupby,$arrcolscontables)===FALSE)){
											if ($avgnaturaleza <= 0.5 ){
												if($arrTotales[$ind]['sumasaldofinal'] < 0){
													echo "<td style='text-align:right; color:#791818;' width='15%' class='pie_derecha'>(" . number_format(abs($arrTotales[$ind]['sumasaldofinal']),0) . ")" . "</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='pie_derecha'>" . number_format($arrTotales[$ind]['sumasaldofinal'],0) . "</td>";
												}
											}else{
												if(($arrTotales[$ind]['sumasaldofinal']) < 0){
													echo "<td style='text-align:right; color:#791818;' width='15%' class='pie_derecha'>(" . number_format($arrTotales[$ind]['sumasaldofinal'],0) . ")</td>";
												}else{
													echo "<td style='text-align:right;' width='15%' class='pie_derecha'>" . number_format(abs($arrTotales[$ind]['sumasaldofinal']),0) . "</td>";
												}
											}
										}else{
											echo "<td style='text-align:right;' width='15%' class='pie_derecha'>" . number_format($arrTotales[$ind]['sumasaldofinal'],0) . "</td>";
										}
										
									}
									$ind++;
								} //foreach

								//poner totales de totales de filas si es segunda dimension
								if ($groupbysecond)
								{
									if ($_SESSION['chkcol'][1]){
										$avgnaturaleza = $arrSumaNaturalezaT[1]/$arrContNaturalezaT[1];
										if(!(array_search($groupby,$arrcolscontables)===FALSE)){
											if ($avgnaturaleza <= 0.5 ){
												if($arrRowTotalT[1] < 0){
													echo "<td style='text-align:right; color:#791818;' width='10%' class='pie_derecha'>(" . number_format(abs($arrRowTotalT[1]),0) . ")" . "</td>";
												}else{
													echo "<td style='text-align:right;' width='10%' class='pie_derecha'>" . number_format($arrRowTotalT[1],0) . "</td>";
												}
											}else{
												if(($arrRowTotalT[1]) < 0){
													echo "<td style='text-align:right; color:#791818;' width='10%' class='pie_derecha'>(" . number_format($arrRowTotalT[1],0) . ")</td>";
												}else{
													echo "<td style='text-align:right;' width='10%' class='pie_derecha'>" . number_format(abs($arrRowTotalT[1]),0) . "</td>";
												}
											}
										}else{
											echo "<td style='text-align:right;' width='10%' class='pie_derecha'>" . number_format($arrRowTotalT[1],0) . "</td>";
										}
									}
									
									if ($_SESSION['chkcol'][2]){
										echo '<td style="text-align:right;" class="pie_derecha" width="10%" >';
							 			echo '<b>'.number_format($arrRowTotalT[2],0);					 	
										echo '</td>';
									}
						
									if ($_SESSION['chkcol'][3]){					
										echo '<td style="text-align:right;" class="pie_derecha" width="15%">';
										echo '<b>'.number_format($arrRowTotalT[3],0);
										echo '</td>';
									}
									
									if ($_SESSION['chkcol'][4]){
										$avgnaturaleza = $arrSumaNaturalezaT[4]/$arrContNaturalezaT[4];
										if(!(array_search($groupby,$arrcolscontables)===FALSE)){
											if ($avgnaturaleza <= 0.5 ){
												if($arrRowTotalT[4] < 0){
													echo "<td style='text-align:right; color:#791818;' width='10%' class='pie_derecha'>(" . number_format(abs($arrRowTotalT[4]),0) . ")" . "</td>";
												}else{
													echo "<td style='text-align:right;' width='10%' class='pie_derecha'>" . number_format($arrRowTotalT[4],0) . "</td>";
												}
											}else{
												if(($arrRowTotalT[1]) < 0){
													echo "<td style='text-align:right; color:#791818;' width='10%' class='pie_derecha'>(" . number_format($arrRowTotalT[4],0) . ")</td>";
												}else{
													echo "<td style='text-align:right;' width='10%' class='pie_derecha'>" . number_format(abs($arrRowTotalT[4]),0) . "</td>";
												}
											}
										}else{
											echo "<td style='text-align:right;' width='10%' class='pie_derecha'>" . number_format($arrRowTotalT[4],0) . "</td>";
										}
									}
									
									if ($_SESSION['chkcol'][5]){
										$avgnaturaleza = $arrSumaNaturalezaT[5]/$arrContNaturalezaT[5];
										if(!(array_search($groupby,$arrcolscontables)===FALSE)){
											if ($avgnaturaleza <= 0.5 ){
												if($arrRowTotalT[5] < 0){
													echo "<td style='text-align:right; color:#791818;' width='10%' class='pie_derecha'>(" . number_format(abs($arrRowTotalT[5]),0) . ")" . "</td>";
												}else{
													echo "<td style='text-align:right;' width='10%' class='pie_derecha'>" . number_format($arrRowTotalT[5],0) . "</td>";
												}
											}else{
												if(($arrRowTotalT[5]) < 0){
													echo "<td style='text-align:right; color:#791818;' width='10%' class='pie_derecha'>(" . number_format($arrRowTotalT[5],0) . ")</td>";
												}else{
													echo "<td style='text-align:right;' width='10%' class='pie_derecha'>" . number_format(abs($arrRowTotalT[5]),0) . "</td>";
												}
											}
										}else{
											echo "<td style='text-align:right;' width='10%' class='pie_derecha'>" . number_format($arrRowTotalT[5],0) . "</td>";
										}
									}
								}
								
						echo "</tr>";
						echo "</table>";
				     	// echo "</td>";
				    	//echo "</tr>";
				  		//echo "</table>";  
echo "</form>";
echo "<br>";
} 

if (!isset($_POST['excel'])) {
	include('includes/footer.inc');
}

?>

<script>

function SendFilter(funcion){
	var filter = document.FDatosB.filterlist.value;
	if (filter!=""){
		window.open("SendFilterToUser.php?funcion=" + funcion + "&filtername="+filter,"USUARIOS","width=400,height=300,scrollbars=NO");		
	}
	else
		alert('Debe seleccionar un filtro para utilizar esta opcion');
}


function DeleteFilter(){
	if (document.FDatosB.filterlist.value!=""){
	
		if (confirm('Esta seguro de eliminar el filtro seleccionado ?')){
			document.FDatosB.opcdelfilter.value="1";
			document.FDatosB.submit();
		}
	}
	else
		alert('Debe seleccionar un filtro para utilizar esta opcion');
}

function HideWhereSelect(){
		document.getElementById("idwherecond").style.display="none";
}

function actualizaGroupBy(obj)
{
	for (i=0;i<document.FDatosB.groupby.length;i++){ 
    	if (document.FDatosB.groupby[i].value==obj.name){ 
        	document.FDatosB.groupby[i].checked=true;
			break; 
		}
    } 
}

function actualizaGroupBySecond(obj){
	for (i=0;i<document.FDatosB.groupbysecond.length;i++){ 
    	if (document.FDatosB.groupbysecond[i].value==obj.name){ 
        	document.FDatosB.groupbysecond[i].checked=true;
			break; 
		}
    } 
}


function saveNewFilter(){
	if (document.getElementById("namenewfilter").value!=""){
		document.FDatosB.opcsavefilter.value="1";
		document.FDatosB.submit();
	}else
		alert('Debe introducir un nombre para el filtro');
}

function ExportaExcel(){
	window.open("dwh_ReporteContabilidadExcel.php");
}

function selAll(obj){
    var I = document.getElementById('totrows').value;

    //alert("valor de :" + I);

    for(i=1; i<=I; i++) {
        var concatenar = "chk" + i;
        chkobj = document.getElementById(concatenar);
        if(chkobj != null) {
            chkobj.checked = obj.checked;
        }
    }
}

</script>
