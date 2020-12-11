<?php
/*
ini_set('display_errors', 1);;
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);
*/
include('includes/session.inc');
$funcion=1171;
include('includes/SecurityFunctions.inc');
//include('includes/ConnectDB_Dataware.inc');
$title= _('Dataware Flujo de Efectivo');


if (isset($_POST['reiniciar'])){
	$_SESSION['valoreshist'] = "";
    $_SESSION['valorfijo']="";
	$_SESSION['chkcol']=array();
	$_SESSION['topcolumns']=array();
	$_SESSION['condiciones']="";
	$_SESSION['condname']="";
    //echo '<meta http-equiv="Refresh" content="0; url=' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
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

// variables de sesion para la grafica
$_SESSION["consultareporte"]= "";
$_SESSION["filtrodw"]= "";

if ($_GET['novalorfijo']==1)
	$_SESSION['valorfijo']="";



$DatawareServillantas=$_SESSION['BaseDataware'];
$hostDataware=$host;
if (strlen($_SESSION['HostDataware'])>0){
	$hostDataware=$_SESSION['HostDataware'];
}

$dbDataware=mysqli_connect($hostDataware , $dbuser, $dbpassword,$DatawareServillantas, $mysqlport,$dbsocket);
//$dbDataware= mysqli_connect($host , $dbuser, $dbpassword, $DatawareServillantas, $mysqlport, $dbsocket);

# **********************************************************************
# ***** RECUPERA VALORES DE FECHAS *****

    if (isset($_POST['FromYear']))
    {
    	$FromYear= $_POST['FromYear'];
    }
    else
    {
    	if (isset($_GET['FromYear']))
            {
                    $FromYear = $_GET['FromYear'];
            }
            else{
                $FromYear=date('Y');
            };
    };

    if (isset($_POST['FromMes']))
    {
            $FromMes= $_POST['FromMes'];
    }
    else{
            if (isset($_GET['FromMes']))
            {
                    $FromMes = $_GET['FromMes'];
            }
            else{
                $FromMes=date('m');
            };
    };

    if (isset($_POST['FromDia']))
    {
            $FromDia= $_POST['FromDia'];

    }
    else{
            if (isset($_GET['FromDia']))
            {
                    $FromDia = $_GET['FromDia'];
            }
            else{
                $FromDia="01";
            };
    };

    if (isset($_POST['ToYear']))
    {
            $ToYear= $_POST['ToYear'];
    }
    else{
            if (isset($_GET['ToYear']))
            {
                    $ToYear = $_GET['ToYear'];
            }
            else{
                $ToYear=date('Y');
            };
    };

    if (isset($_POST['ToMes']))
    {
            $ToMes= $_POST['ToMes'];
    }
    else{
            if (isset($_GET['ToMes']))
            {
                    $ToMes = $_GET['ToMes'];
            }
            else{
                $ToMes=date('m');
            };
    };

    if (isset($_POST['ToDia']))
    {
            $ToDia= $_POST['ToDia'];
    }
    else{
            if (isset($_GET['ToDia']))
            {
                    $ToDia = $_GET['ToDia'];
            }
            else{
                $ToDia=date('d');
            };
    };

     $fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);

     $fechafin= rtrim($ToYear).'-'.rtrim($ToMes).'-'.rtrim($ToDia) . ' 23:59:59';
     $fechafin2 = rtrim($ToYear).'-'.rtrim($ToMes).'-'.rtrim($ToDia);
     
     $fechainitime = strtotime($fechaini);
     $fechafintime = strtotime($fechafin2);
     $timediff = $fechainitime - $fechafintime;
     
	 $InputError=0;
     
     if ($timediff > 0){
     	
		 include('includes/header.inc');
         $InputError = 1;
	     prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
		 exit;
     } else {
          $InputError = 0;
     }

# ******************************************************************************

if (isset($_POST['procesar']))
{
    $procesar= $_POST['procesar'];
}
else{
    $procesar = $_GET['procesar'];
};

if (isset($_POST['groupby']))
{
	$groupby= $_POST[$_POST['groupby']];
}
else{
	if (isset($_GET['groupby']))
	{
		$groupby = $_GET['groupby'];
	}
	else{
		$groupby = "Anio";
	};
};


if (isset($_POST['groupbysecond']))
{
	$groupbysecond= $_POST[$_POST['groupbysecond']];
}
else{
	if (isset($_GET['groupbysecond']))
	{
		$groupbysecond = $_GET['groupbysecond'];
	}
	else{
		$groupbysecond = "";
	};
};


if (isset($_POST['condicion']))
{
	$condicion= $_POST['condicion'];
}
else{
	if (isset($_GET['condicion']))
	{
		$condicion = $_GET['condicion'];
	}
	else{
		$condicion = "";
	};
};


if (isset($_GET['valorfijo'])){
    if (strlen($_GET['valorfijo'])>5){
        unset($_SESSION['valorfijo']);
        $_SESSION['valorfijo']=str_replace('$','',$_GET['valorfijo']);
        $_SESSION['valorfijo']=str_replace('=','=$',$_SESSION['valorfijo']);
        $_SESSION['valorfijo']=trim($_SESSION['valorfijo'])."$";
    }
}
//if (strpos($condicion,$_SESSION['valorfijo'])===false)
	$condicion= $_SESSION['valorfijo'].' '.$condicion;


//verificar si selecciono condiciones adicionales
 $wherecond="";
 $arrwherecond=array();
 if (isset($_POST['wherecond'])) {
		$wherecond = " AND (";
		$pent = 0;
		for ($ds=0;$ds<count($_POST['wherecond']);$ds++) {
				  $pent = $pent + 1;
				  if ($pent == 1) {
							$wherecond .= " $groupby = '"  . $_POST['wherecond'][$ds] . "'";
				  } else {
							$wherecond .= " OR $groupby = '"  . $_POST['wherecond'][$ds] . "'";
				  }
				  $arrwherecond[] = $_POST['wherecond'][$ds];
		}
		$wherecond .= ") ";
  }

//echo "<pre>where cond: $wherecond";

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


// Funcion Excluir 

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
//echo 'fitrlo'.$filtro;
//echo 'valor'.$valorcondicionante;
if ($filtro<>'' and $valor<>'')
{
	if($_GET['Excluir'] == 1){
		$_SESSION['condiciones'] = $_SESSION['condiciones']." AND " . $filtro . $valorcondicionante."'"  . $valor . "'";
		$_SESSION['condname']= $_SESSION['condname']." Excluir <img src='part_pics/Fill-Right.png' border=0> ".$valor;
		$_SESSION['condname'] = $_SESSION['condname']."@";
	}//
}

if(isset($_POST['excluir']))
{
	$arraycheckbox = $_POST['excluye'];

	for ($contmod = 0; $contmod < count($arraycheckbox); $contmod++)
	{
		$_GET['Excluir']= 1;
		$IdFiltroExcluir= $arraycheckbox[$contmod];
		$_SESSION['condiciones']= $_SESSION['condiciones']." AND " . $filtro . " != '" . $IdFiltroExcluir . "'";
		$_SESSION['condname']= $_SESSION['condname']." Excluir <img src='part_pics/Fill-Right.png' border=0> ".$IdFiltroExcluir;
		$_SESSION['condname'] = $_SESSION['condname']."@";
	}
}

$wherecond .= $_SESSION['condiciones'];

//salvar filtro
if ($_POST['opcsavefilter']=="1"){

	$nombrefiltro = $_POST['namenewfilter'];

	$sql = "Select * from DWH_userfilters
			WHERE filtername = '$nombrefiltro'
			and userid = '".$_SESSION['UserID']."'";
	$rs = DB_query($sql,$dbDataware);
    if (DB_num_rows($rs) > 0 ){
		prnMsg(_('El nombre '.$nombrefiltro.' ya existe en su lista de filtros'),'error');
	}
	else{
		$lista="";
		for($i=1;$i<=count($_SESSION['chkcol']);$i++)
			if ($_SESSION['chkcol'][$i])
				$lista.=$i.",";

		$lista = substr($lista,0,strlen($lista)-1);
		$sql = "Insert DWH_userfilters VALUES ('".$_SESSION['UserID']."','$nombrefiltro','$condicion','$groupby','$groupbysecond','$lista','".$funcion."')";
		$rs = DB_query($sql,$dbDataware);
	}
}
//eliminar filtro////
if ($_POST['opcdelfilter']=="1"){
	$nombrefiltro = $_POST['filterlist'];

	$sql = "DELETE from DWH_userfilters
			WHERE filtername = '$nombrefiltro'
			and userid = '".$_SESSION['UserID']."'";
	$r=DB_query($sql,$dbDataware);
}

if (isset($_GET['OrdenarPor']))
{
	$OrdenarPor = $_GET['OrdenarPor'];
}
else{
	$OrdenarPor = $groupby;
};


if (isset($_POST['procesar']))
	$OrdenarPor="header";


if (isset($_GET['Ordenar']))
{
	$Ordenar = $_GET['Ordenar'];
}
else{

	$Ordenar = "asc";
};

if ($Ordenar == "asc") {
	$sigOrdenar = "desc";
}
else{
	$sigOrdenar = "asc";
};

//columnas visibles
 //$totCol = 4;
 //if (Havepermission($_SESSION['UserID'],592, $db)==1)

$totCol = 9;


if (($_POST['opcdelfilter']!="1" and $_POST['opcsavefilter']!="1" and !isset($_POST['procesar']) and $_REQUEST['keepvisible']!=1) || ($_POST['allcolumns']==1)){
	$_SESSION['chkcol'] = array();
	for($j=1;$j<=$totCol;$j++)
		$_SESSION['chkcol'][$j] = "checked";
}
else
{
	  if ((isset($_POST['procesar']) and $_POST['allcolumns']!=1) || $_POST['opcsavefilter']=="1" || $_POST['opcdelfilter']=="1")
	  	if ($_POST['dimensionuno'])
	  		for($j=1;$j<=$totCol;$j++)
	  			$_SESSION['chkcol'][$j] = ($_POST['chkcol'.$j]=="on")?"checked":"";
}


//verificar si carga filtro guardado
if ($_REQUEST['filterlist'] and $_POST['opcdelfilter']!="1"){

  	$sql = "Select * from DWH_userfilters
			WHERE filtername = '".$_POST['filterlist']."'
			and userid = '".$_SESSION['UserID']."'";

	$rs = DB_query($sql,$dbDataware);
	$rows = DB_fetch_array($rs);
	$condicion = $rows['filter'];
	$groupby = $rows['dimension1'];
	$groupbysecond = $rows['dimension2'];
	$arrcolumnas = explode(",",$rows['columns']);

	for($j=1;$j<=$totCol;$j++)
		$_SESSION['chkcol'][$j] = (in_array($j,$arrcolumnas))?"checked":"";


	$Ordenar = "header";
	$Ordenar = "asc";

	$_GET['filtrar']=0;
}


$titulo = trim($groupby);

switch ($titulo)
{
	case 'Anio' :
		$titulo2 = _('Anio');
		$sig_groupby = "Cuatrimestre";
		break;
	case 'Cuatrimestre' :
		$titulo2 = _('Cuatrimestre');
		$sig_groupby = "Trimestre";
		break;
	case 'Trimestre' :
		$titulo2 = _('Trimestre');
		$sig_groupby = "Mes";
		break;
	case 'Mes' :
		$titulo2 = _('Mes');
		$sig_groupby = "Semana";
		break;
		
	case 'Semana' :
		$titulo2 = _('Semana');
		$sig_groupby = "Fecha";
		break;
	case 'Fecha' :
		$titulo2 = _('Fecha');
		$sig_groupby = "Dia";
		break;
	case 'Dia' :
		$titulo2 = _('Dia');
		$sig_groupby = "NombreDia";
		break;
	case 'NombreDia' :
		$titulo2 = _('Nombre').'<br>'. _('Dia');
		$sig_groupby = "FinDeSemana";
		break;
	case 'FinDeSemana' :
		$titulo2 = _('Fin').'<br>'. _('Semana');
		$sig_groupby = "legalbusiness";
		break;
	case 'legalbusiness' :
		$titulo2 = _('Empresa');
		$sig_groupby = "areadescription";
		break;
	case 'areadescription' :
		$titulo2 = _('Area');
		$sig_groupby = "regiondescription";
		break;
	case 'regiondescription' :
		$titulo2 = _('Matriz');
		$sig_groupby = "tagdescription";
		break;
	case 'tagdescription' :
		$titulo2 = _('Unidad').'<br>'. _('Negocio');
		$sig_groupby = "department";
		break;
	case 'department' :
		$titulo2 = _('Departamento');
		$sig_groupby = "typedocument";
		break;
	case 'typedocument' :
		$titulo2 = _('Tipo<br>Movimiento');
		$sig_groupby = "beneficiario";
		break;
	case 'beneficiario' :
		$titulo2 = _('Nombre<br>Beneficiario');
		$sig_groupby = "proveedor";
		break;
	case 'proveedor' :
		$titulo2 = _('Nombre<br>Proveedor');
		$sig_groupby = "cliente";
		break;
	case 'cliente' :
		$titulo2 = _('Nombre<br>Cliente');
		$sig_groupby = "nombrecuenta";
		break;
	case 'nombrecuenta' :
		$titulo2 = _('Cuenta<br>Cheque');
		$sig_groupby = "typename";
		break;
	case 'typename' :
		$titulo2 = _('Tipo<br>Documento');
		$sig_groupby = "currency";
		break;
	case 'currency' :
		$titulo2 = _('Moneda');
		$sig_groupby = "folio";
		break;
	case 'folio' :
		$titulo2 = _('folio');
		$sig_groupby = "chequeno";
		break;
	case 'chequeno' :
		$titulo2 = _('No Cheque');
		$sig_groupby = "Anio";
		break;
}

$titulografica=strtoupper(str_replace('<br>',' ',$titulo2));
$titulo2 = strtoupper($titulo2);
$colsconsulta= "";

if (isset($_POST["tipografica"])){
	$_POST["tipografica"]= $_POST["tipografica"];
} else {
	$_POST["tipografica"]= 7;
}

if (!isset($_POST['excel'])){
	include('includes/header.inc');

	echo '<HEAD><TITLE><center> :: Dataware Flujo efectivo</center> </TITLE></HEAD>';
	/*echo '<HEAD><title>
    		<p class="texto_lista" align="center">
    		<img src="images/panel_xml.png" height="25" width="25" title="' . _('Reporte Dataware Solicitudes') . '" alt="">' . $title . '<br>
    		</title>
    		</HEAD>';
*/

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

 if ($groupbysecond){

	$condact = str_replace("$","'",$condicion);

	$qry = "SELECT distinct $groupbysecond as colname
			FROM DWH_FlujoEfectivo d, DWH_Tiempo t
			WHERE d.u_tiempo = t.u_tiempo
			AND Fecha between '" .$fechaini."' AND '".$fechafin."'
			$condact
			GROUP BY $groupbysecond ORDER BY $groupbysecond
			";
	//echo "<pre>$qry";
	$rstopcol = DB_query($qry,$dbDataware);
	$index=1;
	while ($rsmyrow = DB_fetch_array($rstopcol)){
		$_SESSION['topcolumns'][$index++] = $rsmyrow['colname'];
	}

 }
 else
 	$_SESSION['topcolumns'][]="";
/*
 if($_SESSION['ShowIndex']==0){
 	echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] . '/default.css" rel="stylesheet" type="text/css"/>';
 }else{
 	echo '<link href="' . $rootpath . '/css/css_lh.css" rel="stylesheet" type="text/css"/>';
 }
*/



  echo '<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">';
  			echo '<tr>
  					<td class="texto_status2" colspan=8>
  						<img src="images/imgs/flujo.png" height="25" width="25" title="' . _('Dataware Flujo efectivo') . '" alt=""> ' . $title . '<br>
  			 		</td>
  		  		  </tr>';

	 		 echo "<tr>
	 		 		<td style='text-align:center;' colspan='8' >";
	     				echo '<table border=0 align="center" style="margin:auto;">
	      							 	 <tr>
                    					 	<td class="texto_normal">
	     										' . _('Desde : ') . '
	     									</td>&nbsp;&nbsp;&nbsp;&nbsp;';
		   										 echo'<td>
		   										 		<select Name="FromDia">';
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
																		 echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>
															 </td>
									<td class="texto_normal">
													' . _('Hasta:') . '
												</td>';
													 echo'<td>
															<select Name="ToDia">';
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
														 	echo'<td>';
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
														   echo '</td>
										 </tr>																            		
							
									  </table>
														   		</td>
						  </tr>
							<tr><td colspan=8>&nbsp;</td></tr>
						 ';
  //echo '</td></tr>';
  					//echo '<tr><td colspan=2>&nbsp;</td></tr>';
											echo '<tr valign="top">';
   													// echo '<td>';
   															//$tabla= '<tr>';
   															$chktiempo=validacheck('tiempo',$groupby);
   															$chknegocio=validacheck('negocio',$groupby);
   															$chkproveedor=validacheck('proveedor',$groupby);
   															$chkdocumento=validacheck('documento',$groupby);
   															
   															
   															$tabla= '<td  align="right" cellpadding="1" colspan=4><table><tr><td>
    																			<input type=radio name=groupbysecond value="" checked onclick="if(this.checked) document.FDatosB.allcolumns.value=1;">
    																				 <img title="Una dimension" src="images/1d.png" width=20 border=0 >
    																		</td>';		
				  											$tabla=$tabla. '<td class="texto_normal"><input type=radio name=groupby value="tiempo" '.$chktiempo.'>Tiempo<br>
																			<select name="tiempo" onchange="actualizaGroupBy(this);">
																				<option value="Anio" '; if($groupby=="Anio"){ $tabla.= 'selected';$chktiempo='checked';} $tabla.='>Anio</option>
																				<option value="Trimestre" '; if($groupby=="Trimestre"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Trimestre</option>
																				<option value="Mes" '; if($groupby=="Mes"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Mes</option>
																				<option value="Semana" '; if($groupby=="Semana"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Semana</option>
																				<option value="Fecha" '; if($groupby=="Fecha"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Fecha</option>
																				<option value="Dia" '; if($groupby=="Dia"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Dia</option>
																				<option value="NombreDia" '; if($groupby=="NombreDia"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Nombre Dia</option>
																				<option value="Feriado" '; if($groupby=="Feriado"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Feriado</option>
																				<option value="FinDeSemana" '; if($groupby=="FinDeSemana"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Fin De Semana</option>
																			</select>
																		</td>
																			<td class="texto_normal" nowrap>
																						<input type=radio name=groupby value="negocio" '.$chknegocio.'>U. de Negocio<br>
																					<select name="negocio" onchange="actualizaGroupBy(this);">
																						<option value="legalbusiness" '; if($groupby=="legalbusiness"){ $tabla.= 'selected';$chknegocio='checked';} $tabla.='>Empresa</option>
																						<option value="regiondescription" '; if($groupby=="regiondescription"){ $tabla.= 'selected';$chknegocio='checked';} $tabla.='>Matriz</option>
																						<option value="areadescription" '; if($groupby=="areadescription"){ $tabla.= 'selected';$chknegocio='checked';} $tabla.='>Area</option>
																						<option value="tagdescription" '; if($groupby=="tagdescription"){ $tabla.= 'selected';$chknegocio='checked';} $tabla.='>Unidad Negocio</option>
																						<option value="department" '; if($groupby=="department"){ $tabla.= 'selected';$chknegocio='checked';} $tabla.='>Departamento</option>
																					</select>
																			</td>
																								
																								
																				<td class="texto_normal">
																					<input type=radio name=groupby value="proveedor" '.$chkproveedor.'>Movimiento<br>
																					<select name="proveedor" onchange="actualizaGroupBy(this);">
																						<option value="typedocument" '; if($groupby=="typedocument"){ $tabla.= 'selected';$chkproveedor='checked';} $tabla.='>Tipo Movimiento</option>
																						<option value="beneficiario" '; if($groupby=="beneficiario"){ $tabla.= 'selected';$chkproveedor='checked';} $tabla.='>Nombre Beneficiario</option>
																						<option value="proveedor" '; if($groupby=="proveedor"){ $tabla.= 'selected';$chkproveedor='checked';} $tabla.='>Nombre Proveedor</option>
																						<option value="cliente" '; if($groupby=="cliente"){ $tabla.= 'selected';$chkproveedor='checked';} $tabla.='>Nombre Cliente</option>
																						<option value="nombrecuenta" '; if($groupby=="nombrecuenta"){ $tabla.= 'selected';$chkproveedor='checked';} $tabla.='>Cuenta Chequera</option>
																					</select>
																				</td>
																				<td class="texto_normal">
																				   <input type=radio name=groupby value="documento" '.$chkdocumento.'>Documento<br>
																					<select name="documento" onchange="actualizaGroupBy(this);"> 
																						<option value="typename" '; if($groupby=="typename"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>Tipo Documento</option>
																						<option value="currency" '; if($groupby=="currency"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>Moneda Documento</option>
																						<option value="folio" '; if($groupby=="folio"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>Folio</option>
																						<option value="chequeno" '; if($groupby=="chequeno"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>No Cheque</option>		
																					</select>
																				</td>	
																								';
																			if ($_GET['filtrar']==1 and $chktiempo=="checked") {
																				$sql = "SELECT $groupby as header ";
																				$sql .= " FROM DWH_FlujoEfectivo d JOIN DWH_Tiempo t ON d.u_tiempo = t.u_tiempo";
																				$sql .= " WHERE ";
																				$sql .= " Fecha between '" .$fechaini."' AND '".$fechafin."'";
																				$sql .= " GROUP BY $groupby";
																				$sql .= " ORDER BY $groupby";
																				$tabla.= '<td style="text-align:center;">
																								<select Name="wherecond[]" multiple style="height:80px">';
																				                    $resultDim = DB_query($sql, $dbDataware);
																				                    while ($myrowDim=DB_fetch_array($resultDim,$dbDataware)){
																				                          $tabla.= '<option  VALUE="' . $myrowDim['header'] .  '" >' .$myrowDim['header'].'</option>';
																				                    }
                    																  $tabla.= '</select><br>
																									<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&groupbysecond='.$groupbysecond.
																													  '&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.
																													  $ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&condicion='.$condicion.'&filtrar=0&keepvisible=1" >
																								   					  <img title="Quitar filtros234" src="part_pics/Delete.png" border=0 >&nbsp;
																								    </a>
																						  </td>';
																					          } else
																							  		if ($chktiempo=="checked")
																										/*$tabla.= '<td>
																													<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&groupbysecond='.$groupbysecond.
																																		  '&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.
																																		  $ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&condicion='.$condicion.'&filtrar=1&keepvisible=1" >
																													   <img src="images/info_16x32.gif" alt="Filtrar">&nbsp;
																													</a>
																													</td>';
																									else
																										$tabla.= '<td>&nbsp;</td>';*/
																					
																								if ($_GET['filtrar']==1 and $chknegocio=="checked") {
																					                    $tabla.= '<td style="text-align:center;">
																													<select Name="wherecond[]" multiple style="height:80px" id="idwherecond">';
																									                    $sql = "SELECT $groupby as header ";
																									                    $sql .= " FROM DWH_FlujoEfectivo d JOIN DWH_Tiempo t ON d.u_tiempo = t.u_tiempo";
																									                    $sql .= " WHERE ";
																									                    $sql .= " Fecha between '" .$fechaini."' AND '".$fechafin."'";
																									                    //$sql .= str_replace("$","",$condicion);
																									                    $sql .= " GROUP BY $groupby";
																									                    $sql .= " ORDER BY $groupby";
																									
																									                    $resultDim = DB_query($sql, $dbDataware);
																									                    while ($myrowDim=DB_fetch_array($resultDim,$dbDataware)){
																									                          $tabla.= '              <option  VALUE="' . $myrowDim['header'] .  '" >' .$myrowDim['header'].'</option>';
																									                    }
                    																					$tabla.= '</select><br>
																													<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&groupbysecond='.$groupbysecond.
																																	  '&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.
																																	  $ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&condicion='.$condicion.'&filtrar=0&keepvisible=1" >
																												                      <img title="Quitar filtros123" src="images/cancel.gif" border=0 >&nbsp;
																												    </a>
																												  </td>';
																											          } else
																													  		if ($chknegocio=="checked")
																																$tabla.= '<td>
																																			<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&groupbysecond='.$groupbysecond.
																																								  '&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.
																																								  $ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&condicion='.$condicion.'&filtrar=1&keepvisible=1" >
																																			                      <img src="images/filtrar.GIF" alt="Filtrar">&nbsp;
																																			</a>
																																		  </td>';
																																else
																																	$tabla.= '';
																																				if ($_GET['filtrar']==1 and $chkdocumento=="checked") {
																																	                    $tabla.= '<td style="text-align:center;">
																																									<select Name="wherecond[]" multiple style="height:80px">';
																																					                    $sql = "SELECT $groupby as header ";
																																					                    $sql .= " FROM DWH_FlujoEfectivo d JOIN DWH_Tiempo t ON d.u_tiempo = t.u_tiempo";
																																					                    $sql .= " WHERE ";
																																					                    $sql .= " Fecha between '" .$fechaini."' AND '".$fechafin."'";
																																					                    //$sql .= str_replace("$","",$condicion);
																																					                    $sql .= " GROUP BY $groupby";
																																					                    $sql .= " ORDER BY $groupby";
																																					
																																					                    $resultDim = DB_query($sql, $dbDataware);
																																					                    while ($myrowDim=DB_fetch_array($resultDim,$dbDataware)){
																																					                           $tabla.= '              <option  VALUE="' . $myrowDim['header'] .  '" >' .$myrowDim['header'].'</option>';
																																					                    }
                   																																		 $tabla.= '</select><br>
																																									<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&groupbysecond='.$groupbysecond.
																																													  '&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.
																																													  $ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&condicion='.$condicion.'&filtrar=0&keepvisible=1" >
																																								                      <img title="Quitar filtros" src="images/cancel.gif" border=0 >&nbsp;
																																								    </a>
																																								  </td>';
																																							          } else
																																									  		if ($chkdocumento=="checked")
																																												$tabla.= '<td>
																																															<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&groupbysecond='.$groupbysecond.
																																																				  '&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.
																																																				  $ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&condicion='.$condicion.'&filtrar=1&keepvisible=1" >
																																															                      <img src="images/filtrar.GIF" alt="Filtrar">&nbsp;
																																															</a>
																																														  </td>';
																																											else
																																												$tabla.= '';
																																															if ($_GET['filtrar']==1 and $chkproducto=="checked") {
																																												                    $tabla.= '<td style="text-align:center;">
																																																				<select Name="wherecond[]" multiple style="height:80px">';
																																																                    $sql = "SELECT $groupby as header ";
																																																                    $sql .= " FROM DWH_FlujoEfectivo d JOIN DWH_Tiempo t ON d.u_tiempo = t.u_tiempo";
																																																                    $sql .= " WHERE ";
																																																                    $sql .= " Fecha between '" .$fechaini."' AND '".$fechafin."'";
																																																                    //$sql .= str_replace("$","",$condicion);
																																																                    $sql .= " GROUP BY $groupby";
																																																                    $sql .= " ORDER BY $groupby";
																																																
																																																                    $resultDim = DB_query($sql, $dbDataware);
																																																                    while ($myrowDim=DB_fetch_array($resultDim,$dbDataware)){
																																																                       		$tabla.= ' <option  VALUE="' . $myrowDim['header'] .  '" >' .$myrowDim['header'].'</option>';
																																																																		    }
																																																			                    $tabla.= '</select><br>
																																																												<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&groupbysecond='.$groupbysecond.
																																																																  '&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.
																																																																  $ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&condicion='.$condicion.'&filtrar=0&keepvisible=1" >
																																																											                      <img title="Quitar filtros" src="images/cancel.gif" border=0 >&nbsp;
																																																											    </a>
																																																			  </td>';
																																																		          } else
																																																				  		if ($chkproducto=="checked")
																																																							$tabla.= '<td>
																																																										<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&groupbysecond='.$groupbysecond.
																																																															  '&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.
																																																															  $ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&condicion='.$condicion.'&filtrar=1&keepvisible=1" >
																																																										                      <img src="images/filtrar.GIF" alt="Filtrar">&nbsp;
																																																										</a>
																																																									  </td>';
																																																						else
																																																							$tabla.= '';
															$tabla.= '</td></tr></table>
																	<td class="titulos_sub_principales">
																	<table border=0 class="titulos_sub_principales" bordercolor="B2B2B2"><tr><td colspan=4>
																	<td class="titulos_sub_principales">
    																					 <img title="2da dimension" src="images/2d.png" width=20 border=0 >
    																		</td>';

   
    echo $tabla;
/*
 	<td class="texto_lista"></td>  
					<td class="texto_lista"></td>
					<td class="texto_lista"></td>
				 
 */

  echo '
	 ';
	 	$chktiempo="";
		$chknegocio="";
		$chkdocumento="";
		$chkproducto="";
		$chkproveedor='';
		$chktiempo=validacheck('tiempo',$groupbysecond);
		$chknegocio=validacheck('negocio',$groupbysecond);
		$chkproveedor=validacheck('proveedor',$groupbysecond);
		$chkdocumento=validacheck('documento',$groupbysecond);
		
		$tabla= '
				  <td class="titulos_sub_principales"> <input type=radio name=groupbysecond value="tiempoSD" '.$chktiempo.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Tiempo<br>
					<select name="tiempoSD" onchange="actualizaGroupBySecond(this);">
						<option value="Anio" '; if($groupbysecond=="Anio"){ $tabla.= 'selected';$chktiempo='checked';} $tabla.='>Anio</option>
						<option value="Trimestre" '; if($groupbysecond=="Trimestre"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Trimestre</option>
						<option value="Mes" '; if($groupbysecond=="Mes"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Mes</option>
						<option value="Semana" '; if($groupbysecond=="Semana"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Semana</option>
						<option value="Fecha" '; if($groupbysecond=="Fecha"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Fecha</option>
						<option value="Dia" '; if($groupbysecond=="Dia"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Dia</option>
						<option value="NombreDia" '; if($groupbysecond=="NombreDia"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Nombre Dia</option>
						<option value="Feriado" '; if($groupbysecond=="Feriado"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Feriado</option>
						<option value="FinDeSemana" '; if($groupbysecond=="FinDeSemana"){ $tabla.= 'selected';$chktiempo="checked";} $tabla.='>Fin De Semana</option>
					</select>
				</td>
								
				<td class="titulos_sub_principales"><input type=radio name=groupbysecond value="negocioSD" '.$chknegocio.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x U. Negocio<br>
					<select name="negocioSD" onchange="actualizaGroupBySecond(this);">
						<option value="legalbusiness" '; if($groupbysecond=="legalbusiness"){ $tabla.= 'selected';$chknegocio='checked';} $tabla.='>Empresa</option>
						<option value="regiondescription" '; if($groupbysecond=="regiondescription"){ $tabla.= 'selected';$chknegocio='checked';} $tabla.='>Matriz</option>
						<option value="areadescription" '; if($groupbysecond=="areadescription"){ $tabla.= 'selected';$chknegocio='checked';} $tabla.='>Area</option>
						<option value="tagdescription" '; if($groupbysecond=="tagdescription"){ $tabla.= 'selected';$chknegocio='checked';} $tabla.='>Unidad Negocio</option>
						<option value="department" '; if($groupbysecond=="department"){ $tabla.= 'selected';$chknegocio='checked';} $tabla.='>Departamento</option>
					</select>
				</td>

				<td class="titulos_sub_principales"><input type=radio name=groupbysecond value="proveedorSD" '.$chkproveedor.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Movimiento<br>
					<select name="proveedorSD" onchange="actualizaGroupBySecond(this);">
						<option value="typedocument" '; if($groupbysecond=="typedocument"){ $tabla.= 'selected';$chkproveedor='checked';} $tabla.='>Tipo Movimiento</option>
						<option value="beneficiario" '; if($groupbysecond=="beneficiario"){ $tabla.= 'selected';$chkproveedor='checked';} $tabla.='>Nombre Beneficiario</option>
						<option value="proveedor" '; if($groupbysecond=="proveedor"){ $tabla.= 'selected';$chkproveedor='checked';} $tabla.='>Nombre Proveedor</option>
						<option value="cliente" '; if($groupbysecond=="cliente"){ $tabla.= 'selected';$chkproveedor='checked';} $tabla.='>Nombre Cliente</option>
						<option value="nombrecuenta" '; if($groupbysecond=="nombrecuenta"){ $tabla.= 'selected';$chkproveedor='checked';} $tabla.='>Cuenta Chequera</option>
					</select>
				</td>	
				<td>
				   <input type=radio name=groupbysecond value="documentoSD" '.$chkdocumento.'>x Documento<br>
					<select name="documentoSD" onchange="actualizaGroupBySecond(this);"> 
						<option value="typename" '; if($groupbysecond=="typename"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>Tipo Documento</option>
						<option value="currency" '; if($groupbysecond=="currency"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>Moneda Documento</option>
						<option value="folio" '; if($groupbysecond=="folio"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>Folio</option>
						<option value="chequeno" '; if($groupbysecond=="chequeno"){ $tabla.= 'selected';$chkdocumento='checked';} $tabla.='>No Cheque</option>		
																			
					</select>
				</td>						
				</tr></table></td>
			
		';
  
    echo $tabla;


  echo'</td></tr>';
  
if(!isset($_POST['excel'])){
  echo '<tr><td colspan=8>&nbsp;</td></tr>';
  
  echo "<tr>";
  echo '<td colspan=8 class="texto_normal" style="text-align:center;" nowrap>Tipo de Grafica:';
  echo '<select name="tipografica">';
  // Establecer los tipos de graficas que el usuario puede seleccionar
  
	  $sql= "Select id, chartname, flaggeneral
							From DW_tiposgraficas
							Where active= 1
							Order by chartname";
  
  $datos= DB_query($sql, $db);
  
  while ($registro= DB_fetch_array($datos)){
  	if ($_POST["tipografica"] == $registro["id"]){
  		echo '<option value="'.$registro["id"].'" selected>'.$registro["chartname"];
  		$muestragrafica= $registro["flaggeneral"];
  	} else {
  		echo '<option value="'.$registro["id"].'">'.$registro["chartname"];
  	}
  }
  echo "</select>&nbsp;&nbsp;";

  	$sql = "Select * from DWH_userfilters
			WHERE userid = '".$_SESSION['UserID']."'
				and proyecto=".$funcion;
  	
	$rs = DB_query($sql,$dbDataware);
	
	if (DB_num_rows($rs) > 0){
		echo '&nbsp;&nbsp;Filtros Almacenados: &nbsp; 
				<select name="filterlist">
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
		echo '&nbsp;&nbsp;<a href="javascript:SendFilter();"><img src="part_pics/Mail-Forward.png" border=0 title="Enviar filtro a otros usuarios"></a>';
		echo '<input type="text" id="namenewfilter" name="namenewfilter" size="40">
  		      <button style="border:0; background-color:transparent;" value="Guardar Filtro" name="btnfilter" onclick="saveNewFilter();">
			  <img src="images/b_guardar.png" width=60 ALT="Guardar"></button>';
		} 
		else 
		{
			echo '<input type="text" id="namenewfilter" name="namenewfilter" size="60">';
			echo '<button style="border:0; background-color:transparent;" value="Guardar Filtro" name="btnfilter" onclick="saveNewFilter();"><img src="images/b_guardar.png" width=60 ALT="Guardar"></button>';
// 			echo '
// 			<table border="1">
//   		   		<tr>
//   					<td nowrap>
// 						<input type="text" id="namenewfilter" name="namenewfilter" size="60">
//   		    		</td>
//   					<td>&nbsp;
// 						<button style="border:0; background-color:transparent;" value="Guardar Filtro" name="btnfilter" onclick="saveNewFilter();"><img src="images/guardar.png" width=60 ALT="Guardar"></button>
//   					</td>
//            		</tr>
//   		      </table>
		}
		//poner link para eliminar filtros almacenados         boton para guardar filtro <input type="button" value="Guardar Filtro" name="btnfilter" onclick="saveNewFilter();">
		echo "</td></tr>";
}
 


  echo '<tr><td colspan=8>&nbsp;</td></tr>';


  echo '<tr>';
    echo '<td style="text-align:center;" colspan="8">';
    echo '<button style="border:0; background-color:transparent;" name="procesar" value="GENERAR"><img src="images/b_buscar.png" height="35" ALT="Procesar"></button>&nbsp;<button style="border:0; background-color:transparent;" value="REINICIAR" name="reiniciar"><img src="images/b_reiniciar.png" height="35" ALT="Reinicia"></button>&nbsp;<button style="cursor:pointer; border:0; background-color:transparent;" name="excel" value="EXCEL"><img src="images/b_excel.png" height="35" ALT="Exportar a Excel" title="Exportar Tablero a Excel"></button>';
	//echo'<input type="submit" value="GENERAR" name="procesar">&nbsp;<input type="submit" value="REINICIAR" name="reiniciar">';
    //echo '<button style="cursor:pointer; border:0; background-color:transparent;" name="excel" value="EXCEL"><img src="images/exportar.png" width="100" ALT="Exportar a Excel" title="Exportar Tablero a Excel"></button>';
    //echo'&nbsp;<input type="submit"  value="EXCEL" name="excel">'; //onclick="ExportaExcel();"
    echo '</td>';
  echo '</tr>';
  $condicion = str_replace("'","$",$condicion);
	//echo "<pre>3. $condicion";

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


    //echo '<tr><td colspan=2>&nbsp;</td></tr>';
    //echo '<tr>';//
     // echo '<td align="center" colspan="2">';

		$colVisibles=0;
		for($j=1;$j<=$totCol;$j++)
			if($_SESSION['chkcol'][$j])
				$colVisibles++;


        echo '<br><table width=95% border=1  bordercolor=lightgray align="center" cellspacing=0 cellpadding=3>';
        $colsp = ((((count($_SESSION['topcolumns'])+1)*$colVisibles)+3)>$totCol)?(((count($_SESSION['topcolumns'])+1)*$colVisibles)+3):$totCol;
        
        if($_GET['Excluir'] == 1 or isset($_SESSION['condname'])){
        	$confname = explode("@", $_SESSION['condname']);
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
        
// 	if($_GET['Excluir'] == 1){
// 		echo '<tr valign="top"><td colspan='.$colsp.' style="text-align:left;">'.$_SESSION['condname'];
// 	}else{
// 		 echo '<tr valign="top"><td class="td_style"  colspan='.$colsp.' style="text-align:left;">';
// 	}
         
		  	  echo '		<table width="100%">
						<tr valign="top">
							<td class="td_style">
								<table>';
			if (strlen($wherecond)>0){
				//$condicion.=" ".str_replace("'","$",$wherecond);

			}

            if (strlen($condicion)>0 and !$esexcel){
				//echo "<pre>$condicion";
                $x=array();
                $x=explode('AND',$condicion);
				$savefilter=false;

				  for ($z=1;$z<count($x);$z++){
					$fijo=$x[$z];

					if ($fijo!=$fijoant) {

							$savefilter=true;

							$sesionfijo	= str_replace("$","",$_SESSION['valorfijo']);
							$sesionfijo	= str_replace("AND","",$sesionfijo);
							$fijover=str_replace("$","",$fijo);


							if (trim($sesionfijo) == trim($fijover)){
								$newcondicion = str_replace($_SESSION['valorfijo'],"",$condicion);

								$ligados='dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.
									'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
									.$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' . $groupby.'&keepvisible=1&groupbysecond='.$groupbysecond.'&novalorfijo=1&Ordenar=asc&condicion='.$newcondicion;

								$src="part_pics/Delete.png";

								echo '<tr  style="background-color:#9da791;"><td><a href="'.$ligados.'">';
								echo '<img title="quitar" src="'.$src.'" border=0></a>';


							}
							else{
								$fijo2 = "AND".$fijo;
								$newcondicion = str_replace($fijo2,"",$condicion);

								$ligados='dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.
									'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
									.$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' . $groupby.'&keepvisible=1&groupbysecond='.$groupbysecond.'&Ordenar=asc&condicion='.$newcondicion;

								$src="images/cancel.gif";

								echo '<tr><td class="td_style2"><a href="'.$ligados.'">'; 
								echo '<img title="quitar" src="'.$src.'" border=0></a>&nbsp;&nbsp;';

								$ligados='dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.
								'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
								.$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' . $groupby.'&keepvisible=1&groupbysecond='.$groupbysecond.'&valorfijo=$AND '.$fijover. '$&Ordenar=asc' ;

								$src="part_pics/Fill-Right.png";
								echo '<a href="'.$ligados.'">';
								echo '<img title="fijar" src="'.$src.'" border=0></a>&nbsp;';



							}
							//echo "<pre>valorfijo=\$AND $fijover \$";

							$fijover=TraeTitulo($fijover);
							echo $textover.' &nbsp '.$fijover;
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

		  echo '</td></tr>
		  		';

          echo '<tr>';
            echo '<td class="titulos_principales"><font color="white"><b>#</td>';
			$colspanini=2;
            if ($groupby=="namesupplier"){
				$colspanini=3;
                echo '<td class="titulos_principales">';
				if ($esexcel)
					echo '<b><u>'._('Codigo');
				else{
	                echo '<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=unitsordenadas&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                    echo '<b><u><font color="white">'._('codigo');
                 	echo '</a>';
				}
                echo '</td>';
            }

            echo '<td class="titulos_principales">';
            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3><tr>';
            
            if ($muestragrafica == 1){
	            echo '<td class="titulos_principales">';
	            echo '<a target="_blank" href="PDFGraficaDWFlujoEfectivo.php?dato=Todos&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica3.png" border=0 title="Grafica De Solicitudes" width="20" height="25"></a>&nbsp;';
	            echo '</td>';
            }
            
            echo '<td class="titulos_principales"><font color="white">';
            
			  if ($esexcel){
			  	echo '<b><u>' . $titulo2;
			  }else{
              	echo '<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' .$groupby. '&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                echo '<b><u><font color="white">' . $titulo2 ;
              	echo '</a>';
			  }
              echo '&nbsp;&nbsp;&nbsp;';
            // apartado de la grafica
			if (!$esexcel){
				/*$ligados='GraficaDWH_FlujoEfectivo.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' .$groupby.'&valorfijo=AND '.$_SESSION['valorfijo']. '&Ordenar='.$sigOrdenar .'&condicion='.$condicion;
				echo '<a href="'.$ligados.'" target="_blank" >';
						echo '<img src="part_pics/Chart.png" border=0 alt="Grafica De Ventas">';
				echo '</a>';*/
			}
			
			echo '</td></tr></table>';
            echo '</td>';
            
            $SQL= "Select * from DWH_Tiempo WHERE Fecha=DATE_FORMAT('".$fechaini."','%Y-%m-%d')";
            //echo '<pre>sql:'.$SQL.'<br><br>';
            $result = DB_query($SQL, $dbDataware);
            $utiempoini= 0;
            
            if (DB_num_rows($result)>0) {
            	$myrow= DB_fetch_array($result);
            	$utiempoini= $myrow[0];
            } 
            
            $SQL="Select * from DWH_Tiempo WHERE Fecha=DATE_FORMAT('".$fechafin."','%Y-%m-%d')";
            //echo '<pre>sql:'.$SQL.'<br><br>';
            $result = DB_query($SQL, $dbDataware);
            $utiempofin= 0;
            
            if (DB_num_rows($result)>0) {
            	$myrow= DB_fetch_array($result);
            	$utiempofin= $myrow[0];
            }

			if ($groupbysecond){

				foreach($_SESSION['topcolumns'] as $namecol){
					echo '<td class="titulos_principales" colspan="'.$colVisibles.'" nowrap>'.$namecol.'</td>';

				}
				//totales por filas
				echo '<td class="titulos_principales" colspan="'.$colVisibles.'" >TOTALES</td>';

			}
			else{
				if ($_SESSION['chkcol'][1]){
		            echo '<td class="titulos_principales">';
		            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>';
										
					 if ($namecol || $esexcel){
					 	echo "<tr>";
					 	echo "<td class='titulos_principales'><font color='white'>";
					 	echo '<b>'._('Saldo Inicial');
					 	echo "</td>";
					 	echo "</tr>";
					 }else{
					 	echo "<tr>";
					 	echo "<td class='titulos_principales'>";
					 		echo '<input type="checkbox" name="chkcol1" checked >';
				 		echo "</td>";
				 		echo "<td class='titulos_principales'>";
					 		echo '<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=saldoinicial&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
							echo '<b><u><font color="white">'._('Saldo Inicial');
							echo '</font></a>';
						echo "</td>";
						echo "</tr>";
						echo "<tr>";
						echo "<td colspan=2 class='titulos_principales'>";
						echo '<a target="_blank" href="PDFGraficaDWFlujoEfectivo.php?dato=saldoinicial&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 alt="Grafica De Presupuestos" width="20" height="10"></a><br>';
						echo "</td>";
						echo "</tr>";
						
						$colsconsulta.= ", sum(case when d.u_tiempo<".$utiempoini." then saldo/rate else 0 end) as saldoinicial";
					 }
					echo "</table>";
					echo '</td>';//
				} else { $colsconsulta.= ", null as saldoinicial"; }

				if ($_SESSION['chkcol'][2]){
					echo '<td class="titulos_principales">';
					echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>';
					 if ($namecol || $esexcel){
					 	echo "<tr>";
					 	echo "<td class='titulos_principales'><font color='white'>";
					 	 	echo '<b>'._('Cargos');///
					 	echo "</td>";
					 	echo "</tr>";
					 }else{
					 	echo "<tr>";
					 	echo "<td class='titulos_principales'>";
					 		echo '<input type="checkbox" name="chkcol2" checked >';
				 		echo "</td>";
				 		echo "<td class='titulos_principales'>";
						    echo '<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=cargos&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
							echo '<b><u><font color="white">'._('Cargos');
						   	echo '</font></a>';
						echo "</td>";
						echo "</tr>";
						echo "<tr>";
						echo "<td colspan=2 class='titulos_principales'>";
							echo '<a target="_blank" href="PDFGraficaDWFlujoEfectivo.php?dato=cargos&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 alt="Grafica De Presupuestos" width="20" height="10"></a><br>';
						
						echo "</td>";
						echo "</tr>";
						
						$colsconsulta.= ", sum(case when d.u_tiempo>=".$utiempoini." and saldo>0 then saldo/rate else 0 end) as cargos";
					 }
					 echo "</table>";
					echo '</td>';
				} else { $colsconsulta.= ", null as cargos"; }

				if ($_SESSION['chkcol'][3]){

					echo '<td class="titulos_principales">';
					echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>';
					 if ($namecol || $esexcel){
					 	echo "<tr>";
					 	echo "<td class='titulos_principales'><font color='white'>";
					 	 	echo '<b>'._('Abonos');
					 	 echo "</td>";
					 	 echo "</tr>";
					 }else{
					 	echo "<tr>";
					 	echo "<td class='titulos_principales'>";
					 		echo '<input type="checkbox" name="chkcol3" checked >';
				 		echo "</td>";
				 		echo "<td class='titulos_principales'>";
					  		echo '<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=abonos&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
							echo '<b><u><font color="white">'._('Abonos');
							echo '</font></a>';
						echo "</td>";
						echo "</tr>";
						echo "<tr>";
						echo "<td colspan=2 class='titulos_principales'>";
						echo '<a target="_blank" href="PDFGraficaDWFlujoEfectivo.php?dato=abonos&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 alt="Grafica De Presupuestos" width="20" height="10"></a><br>';
						
						echo "</td>";
						echo "</tr>";
						
						$colsconsulta.= ", sum(case when d.u_tiempo>=".$utiempoini." and saldo<0 then (saldo/rate)*-1 else 0 end) as abonos";
					  	
					 }
					 echo "</table>";
					echo '</td>';
				} else { $colsconsulta.= ", null as abonos"; }

				if ($_SESSION['chkcol'][4]){

					echo '<td class="titulos_principales">';
					echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>';
					 if ($namecol || $esexcel){
					 	echo "<tr>";
					 	echo "<td class='titulos_principales'><font color='white'>";
					 		echo '<b>'._('Total Movimientos');
					 	echo "</td>";
					 	echo "</tr>";
					 }else{
					 	echo "<tr>";
					 	echo "<td class='titulos_principales'>";
					 		echo '<input type="checkbox" name="chkcol4" checked >';
					 	echo "</td>";
					 	echo "<td class='titulos_principales'>";
					  	echo '<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=totalmovimiento&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
						echo '<b><u><font color="white">'._('Total Movimientos');
					  		echo '</font></a>';
						echo "</td>";
						echo "</tr>";
						echo "<tr>";
						echo "<td colspan=2 class='titulos_principales'>";
						echo '<a target="_blank" href="PDFGraficaDWFlujoEfectivo.php?dato=totalmov&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 alt="Grafica De Presupuestos" width="20" height="10"></a><br>';
						
						echo "</td>";
						echo "</tr>";
						
						$colsconsulta.= ", sum(case when d.u_tiempo>=".$utiempoini." and d.u_tiempo<=".$utiempofin." then (saldo/rate) else 0 end) as totalmovimiento";
						
					 }
					 echo "</table>";
					echo '</td>';
				} else { $colsconsulta.= ", null as totalmovimiento"; }
					
						if ($_SESSION['chkcol'][5]){

							echo '<td class="titulos_principales">';
							echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>';
							 if ($namecol || $esexcel){
							 	echo "<tr>";
							 	echo "<td class='titulos_principales'><font color='white'>";
								 	echo '<b>'._('Saldo Final');
								echo "</td>";
								echo "</tr>";
							 }else{
							 	echo "<tr>";
							 	echo "<td class='titulos_principales'>";
							 	echo '<input type="checkbox" name="chkcol5" checked >';
							 	echo "</td>";
							 	echo "<td class='titulos_principales'>";
								echo '<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=saldofinal&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
								echo '<b><u><font color="white">'._('Saldo Final');
								echo '</font></a>';
								echo "</td>";
								echo "</tr>";
								echo "<tr>";
								echo "<td colspan=2 class='titulos_principales'>";
								echo '<a target="_blank" href="PDFGraficaDWFlujoEfectivo.php?dato=saldofinal&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 alt="Grafica De Presupuestos" width="20" height="10"></a><br>';
								
								echo "</td>";
								echo "</tr>";
								
								$colsconsulta.= ", sum(case when d.u_tiempo<=".$utiempofin." then saldo/rate else 0 end) as saldofinal";
								
							 }
							echo '</td>';
							echo "</table>";
						} else { $colsconsulta.= ", null as saldofinal"; }
						
			}

			echo '</tr>';

			if ($groupbysecond){
				echo '<tr>';
				echo '<td class="titulos_principales" colspan="'.$colspanini.'">&nbsp;</td>';

				for($ititle=1;$ititle <= count($_SESSION['topcolumns'])+1;$ititle++){ //count + 1 para poner totales a la derecha

					if ($_SESSION['chkcol'][1]){
						echo '<td class="titulos_sub_principales" width="10%" >';
							 echo '<b>'._('Saldo Inicial');
						echo '</td>';
					}

					if ($_SESSION['chkcol'][2]){
						echo '<td class="titulos_sub_principales" width="15%">';
							 echo '<b>'._('Cargos');
						echo '</td>';
					}

					if ($_SESSION['chkcol'][3]){
						echo '<td class="titulos_sub_principales" width="15%">';
							 echo '<b>'._('Abonos');
						echo '</td>';
					}

					if ($_SESSION['chkcol'][4]){
						echo '<td class="titulos_sub_principales" width="15%">';
							 echo '<b>'._('Total Movimientos');
						echo '</td>';
					}
						
					if ($_SESSION['chkcol'][5]){
						echo '<td class="titulos_sub_principales" width="15%">';
							 echo '<b>'._('Saldo Final');
						echo '</td>';
					}
							
				}//fin de for
				echo '</tr>';
			}
			
		

            $condicion = str_replace("$","'",$condicion);
			if ($groupbysecond){
				$groupbysecondvalue = $groupbysecond;
				$groupbysecond=",".$groupbysecond;
				$OrdenarPor = "header";
			}
			
			if($groupby=='Fecha'){
            	$sql = "SELECT left(" . $groupby.",10) as header
            					/*left(" . $groupby.",10) as txtheader,*/
						".$groupbysecond."
						";
            	
            	$sqlgrafica = "SELECT left(" . $groupby.",10) as header
            					/*left(" . $groupby.",10) as txtheader,*/
						".$groupbysecond."
						";
            	
			}else{
				
				$sql = "SELECT " . $groupby." as header
						/*" . $groupby." as txtheader*/
						".$groupbysecond."
						";
				
				$sqlgrafica = "SELECT " . $groupby." as header
						/*" . $groupby." as txtheader*/
						".$groupbysecond."
						";
					
			}//
			
			$sqlgrafica .= $colsconsulta;
			
            $sql .= ", sum(case when d.u_tiempo<".$utiempoini." then saldo/rate else 0 end) as saldoinicial";
            $sql .= ", sum(case when d.u_tiempo>=".$utiempoini." and saldo>0 then saldo/rate else 0 end) as cargos";
            $sql .= ", sum(case when d.u_tiempo>=".$utiempoini." and saldo<0 then (saldo/rate)*-1 else 0 end) as abonos";
            $sql .= ", sum(case when d.u_tiempo>=".$utiempoini." and d.u_tiempo<=".$utiempofin." then (saldo/rate) else 0 end) as totalmovimiento";
            $sql .= ", sum(case when d.u_tiempo<=".$utiempofin." then saldo/rate else 0 end) as saldofinal";
            $sql .= ", min(t.u_tiempo) as mintiempo";
         
            if($groupby=='Fecha'){
            	$sql .= ",left(" . $groupby.",10) as txtheader ";
            	$sqlgrafica .= ",left(" . $groupby.",10) as txtheader ";
            }else{
            	$sql .= "," . $groupby." as txtheader";
            	$sqlgrafica .= "," . $groupby." as txtheader";
            }
           if ($groupby=='namedebtor')
            {
                $sql .= ", debtorno";
                $sqlgrafica .= ", debtorno";
            }

            if ($groupby=='stockdescription')
            {
                $sql .= ", stockid";
                $sqlgrafica .= ", stockid";
            }

 			if ($groupby=='namesupplier')
            {
                $sql .= ", supplierno";
                $sqlgrafica .= ", supplierno";
            }    
            
            if ($groupby=='wo')
            {
            	$sql .= ", wodescription";
            	$sqlgrafica .= ", wodescription";
            }
            
            $sql .= " FROM DWH_FlujoEfectivo d, DWH_Tiempo t";
            $sqlgrafica .= " FROM DWH_FlujoEfectivo d, DWH_Tiempo t";
            $sql .= " WHERE d.u_tiempo = t.u_tiempo";
            $sqlgrafica .= " WHERE d.u_tiempo = t.u_tiempo";
            //$sql .= " AND Fecha between '" .$fechaini."' AND '".$fechafin."' /* AND abs(total) > 0 */ ";
            $sql .= " AND Fecha <='".$fechafin."'";
            $sqlgrafica .= " AND Fecha <='".$fechafin."'";
            $sql .= $condicion." ".$wherecond;
            $sqlgrafica .= $condicion." ".$wherecond;

            $sql .= " GROUP BY " . $groupby.$groupbysecond ;
            $sqlgrafica .= " GROUP BY " . $groupby.$groupbysecond ;
            
            if ($groupby=='stockdescription')
            {
                $sql .= ", stockid";
                $sqlgrafica .= ", stockid";
            }
            if ($groupby=='namesupplier')
            {
            	$sql .= ", supplierno";
            	$sqlgrafica .= ", supplierno";
            }
            if ($groupby=='wo')
            {
            	$sql .= ", wodescription";
            	$sqlgrafica .= ", wodescription";
            }
            
            $sql .= " ORDER BY ".$OrdenarPor." ". $Ordenar." ".$groupbysecond;
            $sqlgrafica .= " ORDER BY ".$OrdenarPor." ". $Ordenar." ".$groupbysecond;

            $_SESSION["consultareporte"]= $sqlgrafica;
            
           //echo '<pre>sql:'.$sql;
            $result = DB_query($sql, $dbDataware);
            $i=0;
            $condicion = str_replace("'","$",$condicion);
			$header="";
			$indSegDim = 1;
			$arrTotales=array();
			$arrRowTotal=array();
			$arrRowTotalT=array();
			//echo 'segunda:'.var_dump($_POST);
            while ($myrow=DB_fetch_array($result))
            {
            	if($groupbysecond=='Fecha'){
            		$myrow['header']=left($myrow['header'],10);
            		//echo 'entra';
            	}

				if ($header != $myrow['header']){
					if ($header!=""){
						
						//revisar que esten escritas todas las topcolumnas
						for($k=$indSegDim;$k<=count($_SESSION['topcolumns']);$k++){

							echo "<td colspan='".$colVisibles."'>&nbsp;</td>";

						}
							//poner totales por filas
							if ($groupbysecond){
								if ($_SESSION['chkcol'][1]){
									$arrRowTotalT[1]+=$arrRowTotal[$i][1];
									echo '<td style="text-align:right;" >';
										 echo '<b>'.number_format($arrRowTotal[$i][1],2);
									echo '</td>';
								}

								if ($_SESSION['chkcol'][2]){
									$arrRowTotalT[2]+=$arrRowTotal[$i][2];
									echo '<td style="text-align:right;" width="15%">';
										 echo '<b>'.number_format($arrRowTotal[$i][2],2);
									echo '</td>';
								}

								if ($_SESSION['chkcol'][3]){
									$arrRowTotalT[3]+=$arrRowTotal[$i][3];
									echo '<td style="text-align:right;" width="15%">';
										 echo '<b>$'.number_format($arrRowTotal[$i][3],2);
									echo '</td>';
								}

								if ($_SESSION['chkcol'][4]){
									$arrRowTotalT[4]+=$arrRowTotal[$i][4];
									echo '<td style="text-align:right;" width="15%">';
										 echo '<b>$'.number_format($arrRowTotal[$i][4],2);
									echo '</td>';
								}
									
								if ($_SESSION['chkcol'][5]){
									$arrRowTotalT[5]+=$arrRowTotal[$i][5];
									echo '<td style="text-align:right;" width="15%">';
										 echo '<b>$'.number_format($arrRowTotal[$i][5],2).'';
									echo '</td>';
								}
							}//if dobledim
						echo "</tr>";
					}
					$i=$i+1;
                	$indSegDim=1;
					echo "<tr>";
					  echo "<td style='text-align:center;'>";
						echo $i;
					  echo "</td>";

					$header = $myrow['header'];

                  if ($groupby=="namesupplier"){
                    echo "<td style='text-align:left;'>";
                    echo $myrow['supplierno'];
                    echo "</td>";
                  }

				  echo "<td nowrap style='text-align:left;'><font size=3>";
				  echo '<a href="dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
				  		.$ToMes. '&ToYear=' . $ToYear .
				  		'&OrdenarPor=' .$groupby. '&Excluir=1&Ordenar=asc&filtro='.$groupby.'&condicionante=^'.
				  		'&valor='.$myrow[0].'&condicion='. $condicion .'" >';
				  echo '<u>'  ;
				  echo '<img src="images/cancel.gif" WIDTH=10 HEIGHT=10  alt="Excluir">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				  echo '</a>';
				  
				  if ($groupby=='namedebtor'){
						if (!$esexcel){
							echo "<a target='_blank' href='CustomerInquiry.php?CustomerID=".$myrow['debtorno']."' >";
							echo "<img src='part_pics/Report.png' WIDTH=10 HEIGHT=10>";
							echo "</a>";
						}
				  }
				 					$ver=strtoupper($myrow['header']);
					$v = $ver;
					if ($groupby=='Mes'){
						$nombremes=glsnombremeslargo($myrow['header']);
						$ver=$nombremes;
					}

				   	if ($esexcel)
						echo "<u>" . $ver;
					else{
						$cond=" AND $groupby=\$".$myrow['header']."\$";
						echo "<a href='dwh_ReporteFlujoEfectivo_V6_0.php?procesar=GENERAR&groupby=".$sig_groupby."&FromDia=".$FromDia."&FromMes=" .$FromMes. "&FromYear=" . $FromYear ."&ToDia=".$ToDia."&ToMes="
						.$ToMes. "&ToYear=" . $ToYear .
						"&OrdenarPor=" .$sig_groupby. "&Ordenar=asc&filtro=".$groupby."&groupbysecond=".$groupbysecondvalue."&condicion=". $condicion.$cond ."&keepvisible=1' >";
						  echo "<u>" . $ver;

						echo "</a>";
					}

				  echo "</td>";
				}


					if ($groupbysecond){
					  //buscar que columna trae el registro
					  for($index=$indSegDim;$index<=count($_SESSION['topcolumns']);$index++){
							if ($myrow[1] == $_SESSION['topcolumns'][$index]){

								 if ($_SESSION['chkcol'][1]){
									echo "<td style='text-align:right;' width='10%'>" . number_format($myrow['saldoinicial'],2) . "</td>";
								 	$arrRowTotal[$i][1] += $myrow['saldoinicial'];
								 }
								 if ($_SESSION['chkcol'][2]){
									echo "<td style='text-align:right;' width='15%'>" . number_format($myrow['cargos'],2) . "</td>";
									$arrRowTotal[$i][2] += $myrow['cargos'];
								 }
								 if ($_SESSION['chkcol'][3]){
									echo "<td style='text-align:right;' width='15%'>$" . number_format($myrow['abonos'],2) . "</td>";
									$arrRowTotal[$i][3] += $myrow['abonos'];
								 }
								 if ($_SESSION['chkcol'][4]){
									echo "<td style='text-align:right;' width='15%'>\$" . number_format($myrow['totalmovimiento'],2) . "</td>";
									$arrRowTotal[$i][4] += $myrow['totalmovimiento'];
								 }
								 
								
							 
								 if ($_SESSION['chkcol'][5]){
									echo "<td style='text-align:right;' width='15%'>$" . number_format($myrow['saldofinal'],2) . "</td>";
								 	$arrRowTotal[$i][5] += $myrow['saldofinal'];
								 }
								

								break;
							}
							else{

								echo "<td colspan='".$colVisibles."'>&nbsp;</td>";

							}

					  }//for
					  $indSegDim = $index;
					}
					else{
						
						if (($myrow['totalordenado'])<>0) {
							$margenporc=($myrow['totalrecibido']/abs($myrow['totalordenado']))*100;
								
						}else{
							$margenporc=0;
						}
						
						if (($myrow['totalrecibido'])<>0) {
							$margenporcf=($myrow['totalfacturado']/abs($myrow['totalrecibido']))*100;
						
						}else{
							$margenporcf=0;
						}

						 if ($_SESSION['chkcol'][1])
							echo "<td style='text-align:right;' width='10%'>" . number_format($myrow['saldoinicial'],2) . "</td>";

						 if ($_SESSION['chkcol'][2])
							echo "<td style='text-align:right;' width='15%'>" . number_format($myrow['cargos'],2) . "</td>";

						 if ($_SESSION['chkcol'][3])
							echo "<td style='text-align:right;' width='15%'>$" . number_format($myrow['abonos'],2) . "</td>";

						 if ($_SESSION['chkcol'][4])
							echo "<td style='text-align:right;' width='15%'>\$" . number_format($myrow['totalmovimiento'],2) . "</td>";

						  if ($_SESSION['chkcol'][5])
							echo "<td style='text-align:right;' width='15%'>$" . number_format($myrow['saldofinal'],2) . "</td>";
						 
					}




                //echo "</tr>";

                $arrTotales[$indSegDim]['sumaSaldoInicial'] += $myrow['saldoinicial'];
                $arrTotales[$indSegDim]['sumaCargos'] += $myrow['cargos'];
                $arrTotales[$indSegDim]['sumaAbonos'] += $myrow['abonos'];
                $arrTotales[$indSegDim]['sumaMovimientos'] += $myrow['totalmovimiento'];
                $arrTotales[$indSegDim]['sumaSaldoFinal'] += $myrow['saldofinal'];
               				 $indSegDim++;
            }//while

			//revisar que esten escritas todas las topcolumnas
			for($k=$indSegDim;$k<=count($_SESSION['topcolumns']);$k++){
					echo "<td colspan='".$colVisibles."'>&nbsp;</td>";
			}

			//poner totales por filas
			if ($groupbysecond){
				if ($_SESSION['chkcol'][1]){
					$arrRowTotalT[1]+=$arrRowTotal[$i][1];
					echo '<td style="text-align:right;" width="10%" >';
						 echo '<b>'.number_format($arrRowTotal[$i][1],2);
					echo '</td>';
				}

				if ($_SESSION['chkcol'][2]){
					$arrRowTotalT[2]+=$arrRowTotal[$i][2];
					echo '<td style="text-align:right;" width="15%">';
						 echo '<b>'.number_format($arrRowTotal[$i][2],2);
					echo '</td>';
				}

				if ($_SESSION['chkcol'][3]){
					$arrRowTotalT[3]+=$arrRowTotal[$i][3];
					echo '<td style="text-align:right;" width="15%">';
						 echo '<b>$'.number_format($arrRowTotal[$i][3],2);
					echo '</td>';
				}

				if ($_SESSION['chkcol'][4]){
					$arrRowTotalT[4]+=$arrRowTotal[$i][4];
					echo '<td style="text-align:right;" width="15%">';
						 echo '<b>$'.number_format($arrRowTotal[$i][4],2);
					echo '</td>';
				}
			
				if ($_SESSION['chkcol'][5]){
					$arrRowTotalT[5]+=$arrRowTotal[$i][5];
					echo '<td style="text-align:right;" width="15%">';
						 echo '<b>$'.number_format($arrRowTotal[$i][5],2).'';
					echo '</td>';
				}
			
			}//if dobledim

			echo "</tr>";


                echo "<tr>";
                  echo "<td colspan='2' class='pie_derecha'><font color='white'>";
                    echo "<b>TOTALES : ";
                  echo "</td>";

				$ind=1;
	  			foreach($_SESSION['topcolumns'] as $namecol){

					if ($arrTotales[$ind]['sumaSaldoVencido1_30']>0){
						$PorcTotal=($arrTotales[$ind]['sumaSaldoVencido61_90'])/$arrTotales[$ind]['sumaSaldoVencido1_30'];
					}else{
						$PorcTotal=0;
					}
						

					if ($arrTotales[$ind]['sumaSaldoVencido61_90']>0){
						$PorcTotalf=($arrTotales[$ind]['sumaSaldoVencido91'])/$arrTotales[$ind]['sumaSaldoVencido61_90'];
					}else{
						$PorcTotalf=0;
					}
							

  					 if ($_SESSION['chkcol'][1]){
					  	echo "<td class='pie_tabla'><font color='white'>";
						echo "<b>" .number_format($arrTotales[$ind]['sumaSaldoInicial'],2);
						echo "</td>";
					 }

  					 if ($_SESSION['chkcol'][2]){
					  	echo "<td class='pie_tabla'><font color='white'>";
						echo "<b>" .number_format($arrTotales[$ind]['sumaCargos'],2);
					  	echo "</td>";
					 }

  					 if ($_SESSION['chkcol'][3]){
						echo "<td class='pie_tabla'><font color='white'>";
						echo "<b>$" .number_format($arrTotales[$ind]['sumaAbonos'],2);
						echo "</td>";
					 }

  					 if ($_SESSION['chkcol'][4]){
						  echo "<td class='pie_tabla'><font color='white'>";
						  echo "<b>\$" .number_format($arrTotales[$ind]['sumaMovimientos'],2);
						  echo "</td>";
					 }
					 
  					 if ($_SESSION['chkcol'][5]){
						echo "<td class='pie_tabla'><font color='white'>";
						  echo "<b>$" .number_format($arrTotales[$ind]['sumaSaldoFinal'],2).'';
						echo "</td>";
					 }
					$ind++;
				}//foreach

				//poner totales de totales de filas si es segunda dimension
				if ($groupbysecond){
					
					if ($arrRowTotalT[2]>0){
						$PorcTotal=($arrRowTotalT[4])/$arrRowTotalT[2];
					}else{
						$PorcTotal=0;
					}
					
					if ($arrRowTotalT[4]>0){
						$PorcTotalf=($arrRowTotalT[7])/$arrRowTotalT[4];
					}else{
						$PorcTotalf=0;
					}
					
					if ($_SESSION['chkcol'][1]){
						echo '<td class="pie_derecha" width="10%" >';
							 echo '<b>'.number_format($arrRowTotalT[1],2);
						echo '</td>';
					}

					if ($_SESSION['chkcol'][2]){
						echo '<td class="pie_derecha" width="15%">';
							 echo '<b>'.number_format($arrRowTotalT[2],2);
						echo '</td>';
					}

					if ($_SESSION['chkcol'][3]){
						echo '<td class="pie_derecha" width="15%">';
							 echo '<b>$'.number_format($arrRowTotalT[3],2);
						echo '</td>';
					}

					if ($_SESSION['chkcol'][4]){
						echo '<td class="pie_derecha" width="15%">';
							 echo '<b>$'.number_format($arrRowTotalT[4],2);
						echo '</td>';
					}
						
					if ($_SESSION['chkcol'][5]){
						echo '<td class="pie_derecha" width="15%">';
							 echo '<b>$'.number_format($arrRowTotalT[5],2).'';
						echo '</td>';
					}
				}
                echo "</tr>";
        echo "</table>";
echo "</form>";
if ($debug==1){
}
if (isset($_POST['excel'])){
	exit;
}
include('includes/footer.inc');
?>
<script>

	function SendFilter(){
		var filter = document.FDatosB.filterlist.value;
		if (filter!=""){
			window.open("SendFilterToUser.php?filtername="+filter,"USUARIOS","width=400,height=300,scrollbars=NO");
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

	function actualizaGroupBy(obj){

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
		}
		else
			alert('Debe introducir un nombre para el filtro');

	}

    function ExportaExcel(){

        window.open("dwh_ReporteVentasV2Excel.php");

    }

</script>
