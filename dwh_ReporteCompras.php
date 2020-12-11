<?php
/*
 13-05-2014 alta de campos
 ALTER TABLE `DWH_Compras` 
 ADD COLUMN `wo` int AFTER `marca`, 
 ADD COLUMN `wodescription` varchar(255) AFTER `wo`; 
 
 
 ALTER TABLE `DWH_Compras` 
 ADD COLUMN `pedidoventa` int AFTER `wodescription`, 
 ADD COLUMN `debtorno` varchar(50) AFTER `pedidoventa`, 
 ADD COLUMN `namedebtor` varchar(250) AFTER `debtorno`, 
 ADD COLUMN `womasterid` varchar(250) AFTER `namedebtor`, 
 ADD COLUMN `wocomponent` varchar(250) AFTER `womasterid`;
 */
include('includes/session.inc');
if(!isset($_POST['PrintExcel'])){
	include('includes/header.inc');
}


$funcion=992;
include('includes/SecurityFunctions.inc');
//include('includes/ConnectDB_Dataware.inc');


    if (isset($_POST['reiniciar']))
    {
        $_SESSION['valoreshist'] = "";
        $_SESSION['valorfijo']="";
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


$DatawareServillantas=$_SESSION['BaseDataware'];    
$dbDataware=mysqli_connect($host , $dbuser, $dbpassword,$DatawareServillantas, $mysqlport, $dbsocket);

# **********************************************************************
# ***** RECUPERA VALORES DE FECHAS *****

    if (isset($_POST['FromYear']))
    {
            $FromYear= $_POST['FromYear'];
    }	
    else{
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
     
     
     $InputError = 0;
     /*if ($fechaini > $fechafin){
          $InputError = 1;
     prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
     } else {
          $InputError = 0;
     }*/
     
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
	$groupby= $_POST['groupby'];
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
        $_SESSION['valorfijo']=$_SESSION['valorfijo']."$";
    }
}

$condicion= $_SESSION['valorfijo'].' '.$condicion;

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
	};	
};

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
	};	
};

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

if (!isset($_POST['procesar']) and $valor<>'' )
{
    $_SESSION['valoreshist'] .= "-> " . $valor;    
}


$valorcondicionante=str_replace('|','=',$valorcondicionante);
$valorcondicionante=str_replace('^','!=',$valorcondicionante);

if ($filtro<>'' and $valor<>'')
{
    $condicion .= " AND " . $filtro . $valorcondicionante."'"  . $valor . "'";
   
}

if (isset($_GET['OrdenarPor']))
{
	$OrdenarPor = $_GET['OrdenarPor'];
}	
else{
	$OrdenarPor = $groupby;
};

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

$titulo = trim($groupby);

switch ($titulo)
{
    case 'Anio' :
        $titulo2 = _('A�o');
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
        
         $sig_groupby = "location";
        break;
    
    case 'location' :
        $titulo2 = _('Almacen');
        $sig_groupby = "typesupplier";
        break;
    case 'salestype' :
        $titulo2 = _('Tipo').'<br>'. _('Venta');
        $sig_groupby = "custsalesman";
        break;
    
    case 'custsalesman' :
        $titulo2 = _('Vendedor').'<br>'. _('Cliente');
        $sig_groupby = "salesman";
        break;
    case 'salesman' :
        $titulo2 = _('Vendedor').'<br>'. _('Factura');
        $sig_groupby = "userregister";
        break;
    
    case 'userregister' :
        $titulo2 = _('Usuario');
        $sig_groupby = "typedocument";
        break;
    
    case 'typedocument' :
        $titulo2 = _('Tipo').'<br>'. _('Documento');
        $sig_groupby = "paymentterm";
        break;
    case 'paymentterm' :
        $titulo2 = _('Termino').'<br>'. _('Pago');
        $sig_groupby = "currency";
        break;
    case 'currency' :
        $titulo2 = _('Moneda');
        $sig_groupby = "namedebtor";
        break;
    case 'typeclient' :
        $titulo2 = _('Tipo').'<br>'. _('Cliente');
        $sig_groupby = "namedebtor";
        break;
   /* case 'namedebtor' :
        $titulo2 = _('Cliente');
        $sig_groupby = "folio";
        break;*/
    case 'folio' :
        $titulo2 = _('Factura');
        $sig_groupby = "linedescription";
        break;
    case 'linedescription' :
        $titulo2 = _('Linea');
        $sig_groupby = "groupdescription";
        break;
    case 'groupdescription' :
        $titulo2 = _('Grupo');
        $sig_groupby = "categorydescripcion";
        break;
    
    case 'categorydescripcion' :
        $titulo2 = _('Categoria');
        $sig_groupby = "stockdescription";
        break;
    
    case 'stockdescription' :
        $titulo2 = _('Producto');
        $sig_groupby = "Anio";
        break;
    case 'typesupplier' :
        	$titulo2 = _('Tipo Proveevor');
        	$sig_groupby = "namesupplier";
        	break;
        
    case 'namesupplier' :
        $titulo2 = _('Proveevor');
        $sig_groupby = "pedidoventa";
        break;
    
    case 'pedidoventa' :
        	$titulo2 = _('Pedido Venta');
        	$sig_groupby = "namedebtor";
        	break;
    case 'namedebtor' :
        		$titulo2 = _('Cliente Venta');
        		$sig_groupby = "wodescription";
        		break;
     case 'wodescription' :
        	$titulo2 = _('Orden Trabajo');
        	$sig_groupby = "womasterid";
        	break;
     case 'womasterid' :
        		$titulo2 = _('Producto Maestro');
        		$sig_groupby = "wocomponent";
        		break;
     case 'wocomponent' :
        			$titulo2 = _('Producto Nivel Compra');
        			$sig_groupby = "orderno";
        			break;
    case 'orderno' :
        	$titulo2 = _('Orden Compra');
        	$sig_groupby = "linedescription";
        	break;
    
    
 
    
}

$titulo2 = strtoupper($titulo2);

echo '<HEAD><TITLE> :: DATAWARE DE COMPRAS </TITLE></HEAD>';
if(!isset($_POST['PrintExcel'])){
echo '<form method="post" name="FDatosB">';
  echo '<table width=95%>';
  echo '<tr><td style="text-align:center;"><font face=arial size=2><b>DATAWARE DE COMPRAS</b></td></tr>';
  echo '<tr><td align=center>';
     echo '<table>
	       <tr>
                    <td><font size=2>' . _('Al : ') . '</td>';
		    echo'<td><select Name="FromDia">';
			      $sql = "SELECT * FROM cat_Days";
			      $Todias = DB_query($sql,$db);
			      while ($myrowTodia=DB_fetch_array($Todias,$db)){
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
			 while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
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
                    <td>' . _('Hasta:') . '</td>';
		    echo'<td><select Name="ToDia">';
			      $sql = "SELECT * FROM cat_Days";
			      $Todias = DB_query($sql,$db,'','');
			      while ($myrowTodia=DB_fetch_array($Todias,$db)){
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
			 
			 while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
			     $ToMesbase=$myrowToMes['u_mes'];
			     if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){ 
				 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '  " selected>' .$myrowToMes['mes'];
			     }else{
				 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
			     }
			 }
			 echo '</select>';
			 echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
			 
		    echo'</td>
	       </tr>
               
	  </table>';  
  echo '</td></tr>';
  
  echo '<tr>';
    echo '<td>';
      echo '<table >';
        echo '<tr>';
            echo '<td>
                    <table align="left" >
                        <tr>';
                        echo '<td><font size=1><b>'._('X Tiempo').':</b></font></td>';
                        echo '<td>';
                        echo '<input type=radio name=groupby value="Anio"';
                                if ($groupby=="Anio"){ echo " checked";}
                        echo '><font size=1>'._('A�o').'</font>
                            </td>';
                        echo '<td>';
                        echo '<input type=radio name=groupby value="Cuatrimestre"';
                                if ($groupby=="Cuatrimestre"){ echo " checked";}
                        echo '><font size=1>'._('Cuatrimestre').'</font>
                            </td>';
                         echo '<td>';
                        echo '<input type=radio name=groupby value="Trimestre"';
                                if ($groupby=="Trimestre"){ echo " checked";}
                        echo '><font size=1>'._('Trimestre').'</font>
                            </td>';
                            
                        echo '<td>';
                        echo '<input type=radio name=groupby value="Mes"';
                                if ($groupby=="Mes"){ echo " checked";}
                        echo '><font size=1>'._('Mes').'</font>
                            </td>';
                        echo '<td>';
                        echo '<input type=radio name=groupby value="Semana"';
                                if ($groupby=="semana"){ echo " checked";}
                        echo '><font size=1>'._('Semana').'</font>
                            </td>';
                        echo '<td>';
                        echo '<input type=radio name=groupby value="Dia"';
                                if ($groupby=="Dia"){ echo " checked";}
                        echo '><font size=1>'._('Dia').'</font>
                            </td>';
                        echo '<td>';
                        echo '<input type=radio name=groupby value="NombreDia"';
                                if ($groupby=="NombreDia"){ echo " checked";}
                        echo '><font size=1>'._('Nombre Dia').'</font>
                            </td>';
                        echo '<td>';    
                        echo '<input type=radio name=groupby value="Feriado"';
                                if ($groupby=="Feriado"){ echo " checked";}
                        echo '><font size=1>'._('Feriado').'</font>
                            </td>';
                        echo '<td>';    
                        echo '<input type=radio name=groupby value="FinDeSemana"';
                                if ($groupby=="FinDeSemana"){ echo " checked";}
                        echo '><font size=1>'._('Fin De Semana').'</font>
                            </td>';
                  echo '</tr>';
              echo '</table>';
            echo '</td>';
        echo '</tr>';
        echo '<tr><td><hr></td></tr>';
        echo '<tr>';
            echo '<td>
                    <table align="left" >
                        <tr>';
                        echo '<td><font size=1><b>'._('X Unidad Negocio').':</b></font></td>';
                        echo '<td>';
                        echo '<input type=radio name=groupby value="legalbusiness"';
                                if ($groupby=="legalbusiness"){ echo " checked";}
                        echo '><font size=1>'._('Empresa').'</font>
                            </td>';
                        echo '<td>';
                        echo '<input type=radio name=groupby value="areadescription"';
                                if ($groupby=="areadescription"){ echo " checked";}
                        echo '><font size=1>'._('Area').'</font>
                            </td>';
                            
                        echo '<td>';
                        echo '<input type=radio name=groupby value="regiondescription"';
                                if ($groupby=="regiondescription"){ echo " checked";}
                        echo '><font size=1>'._('Region').'</font>
                            </td>';
                            
                        echo '<td>';
                        echo '<input type=radio name=groupby value="tagdescription"';
                                if ($groupby=="tagdescription"){ echo " checked";}
                        echo '><font size=1>'._('Unidad Negocio').'</font>
                            </td>';
                         echo '<td>';
                        echo '<input type=radio name=groupby value="department"';
                                if ($groupby=="department"){ echo " checked";}
                        echo '><font size=1>'._('Departamento').'</font>
                            </td>';
                            
                        echo '<td>';
                        echo '<input type=radio name=groupby value="location"';
                                if ($groupby=="location"){ echo " checked";}
                        echo '><font size=1>'._('Almacen').'</font>
                            </td>';
                        echo '<td>';
                  echo '</tr>';
              echo '</table>';
            echo '</td>';
        echo '</tr>';
        echo '<tr><td><hr></td></tr>';
        echo '<tr>';
            echo '<td  >
                    <table align="left"  border=0>
                        <tr >';
                        echo '<td align="center"> <font size=1><b>'._('X Proveedor').':</b></font></td>';
                        echo '<td>';
                            
                        
                          echo '<td align="center">';    
                        echo '<input type=radio name=groupby value="typesupplier"';
                                if ($groupby=="typesupplier"){ echo " checked";}
                        echo '><font size=1 style="text-align:center">'._('Tipo').' &nbsp;'._('Proveedor').'</font>
                            </td>';
                        echo '<td align="center" >';
                        echo '<input type=radio name=groupby value="namesupplier"';
                                if ($groupby=="namesupplier"){ echo " checked";}
                        echo '><font size=1 style="text-align:center">'._('Nombre').' &nbsp;'._('Proveedor').'</font>
                            </td>';
                          
                  echo '</tr>';
              echo '</table>';
            echo '</td>';
        echo '</tr>';
        
        
        echo '<tr><td><hr></td></tr>';
        echo '<tr>';
        echo '<td  >
                    <table align="left"  border=0>
                        <tr >';
        echo '<td align="center"> <font size=1><b>'._('X Documento').':</b></font></td>';
        echo '<td>';
	
        echo '<td align="center">';
        echo '<input type=radio name=groupby value="pedidoventa"';
        if ($groupby=="pedidoventa"){ echo " checked";}
        echo '><font size=1 style="text-align:center">'._('Pedido').' &nbsp;'._('Venta').'</font>
                            </td>';
        
        echo '<td align="center">';
        echo '<input type=radio name=groupby value="namedebtor"';
        if ($groupby=="namedebtor"){ echo " checked";}
        echo '><font size=1 style="text-align:center">'._('Cliente').' &nbsp;'._('Venta').'</font>
                            </td>';
        
        echo '<td align="center">';
        echo '<input type=radio name=groupby value="wodescription"';
        if ($groupby=="wodescription"){ echo " checked";}
        echo '><font size=1 style="text-align:center">'._('Orden').' &nbsp;'._('Trabajo').'</font>
                            </td>';
        
        echo '<td align="center">';
        echo '<input type=radio name=groupby value="womasterid"';
        if ($groupby=="womasterid"){ echo " checked";}
        echo '><font color=red>*</font><font size=1 style="text-align:center">'._('Producto').' &nbsp;'._('Maestro').'</font>
                            </td>';
        
        echo '<td align="center">';
        echo '<input type=radio name=groupby value="wocomponent"';
        if ($groupby=="wocomponent"){ echo " checked";}
        echo '><font color=red>*</font><font size=1 style="text-align:center">'._('Nivel').' &nbsp;'._('Compra').'</font>
                            </td>';
        
        
        echo '<td align="center">';
        echo '<input type=radio name=groupby value="orderno"';
        if ($groupby=="orderno"){ echo " checked";}
        echo '><font size=1 style="text-align:center">'._('Orden').' &nbsp;'._('Compra').'</font>
                            </td>';
        
        
        echo '</tr>';
        echo '</table>';
        echo '</td>';
        echo '</tr>';
        
        
        echo '<tr><td><hr></td></tr>';
        echo '<tr>';
            echo '<td>
                    <table align="left">
                        <tr>';
                        echo '<td> <font size=1><b>'._('X Producto').':</b></font></td>';
                        echo '<td>';
                        echo '<input type=radio name=groupby value="linedescription"';
                                if ($groupby=="linedescription"){ echo " checked";}
                        echo '><font size=1>'._('Linea').'</font>
                            </td>';
                        echo '<td>';
                        echo '<input type=radio name=groupby value="groupdescription"';
                                if ($groupby=="groupdescription"){ echo " checked";}
                        echo '><font size=1>'._('Grupo').'</font>
                            </td>';
                            
                        echo '<td>';
                        echo '<input type=radio name=groupby value="categorydescripcion"';
                                if ($groupby=="categorydescripcion"){ echo " checked";}
                        echo '><font size=1>'._('Categoria').'</font>
                            </td>';
                        echo '<td>';
                        echo '<input type=radio name=groupby value="stockdescription"';
                                if ($groupby=="stockdescription"){ echo " checked";}
                        echo '><font size=1>'._('Producto').'</font>
                            </td>';
                  echo '</tr>';
              echo '</table>';
            echo '</td>';
        echo '</tr>';
        echo '<tr><td><hr></td></tr>';
      echo '</table>';
    echo '</td>';
  echo '</tr>';
  
  echo '<tr>';
  echo '<td style="text-align:center;">';
	echo '<font  size=1px style="text-align:center" color=red>(*) Aplica solo para ordenes de trabajo</font>';
  
  echo '</td>';
  echo '</tr>';
  
  echo '<tr>';
    echo '<td style="text-align:center;">';
	echo'<input type="submit" value="GENERAR" name="procesar">&nbsp;<input type="submit" value="REINICIAR" name="reiniciar">&nbsp<input type=submit name="PrintExcel" value="Exportar Excel">';
        //echo'&nbsp;<input type="button" onclick="ExportaExcel();" value="EXCEL" name="excel">';
    echo '</td>';
  echo '</tr>';
    $condicion = str_replace("'","$",$condicion);

}
if(isset($_POST['PrintExcel'])){
	header("Content-type: application/ms-excel");
	# replace excelfile.xls with whatever you want the filename to default to
	header("Content-Disposition: attachment; filename=DWHCompras.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
}
    echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr>';
      echo '<td>';
        echo '<table width=80% border=1 cellspacing=0 cellpadding=3>';
        
          echo '<tr style="background-color:#FEFFD7;"><td colspan=11 style="text-align:left;">';
            if (strlen($_SESSION['valoreshist'])>0){
                $valor=$_SESSION['valoreshist'];
                $x=array();
                $session=array();
                $x=explode(' AND',$condicion);
                $session=explode('->',$valor);
                $fijoant="";
                $cumple=false;
                if ($_SESSION['valorfijo']>0){
                    $cumple=true;
                    
                }
              for ($z=1;$z<count($x);$z++){
                $fijo=$x[$z];
                
                if ($cumple==false){
                    if ($fijo!=$fijoant) {
                        $fijover=str_replace("$","",$fijo);
                        
                        if (strrpos($fijo,"!=")==true){
                            $condidiondos="^";
                            $textover="Excluir";
                        }else{
                            $condidiondos="|";
                            $textover="";
                        }
                        $ligados='dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.
                        '&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
                        .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' . $groupby.'&condicionante='.$condidiondos.
                        '&valorfijo=$AND '.$fijover. '$&Ordenar='.$sigOrdenar ;
                        
                        echo '<a href="'.$ligados.'">';
                        echo '<img src="part_pics/Fill-Right.png" border=0>';
                        
                          $fijover=TraeTitulo($fijover);
                          echo $textover.' '.$fijover;
                    
                        echo '</a>';
                    }
                }else{
                    
                    
                    if ($fijo!=$fijoant ) {
                        
                    $fijover=str_replace("$","",$fijo);
                    if (strrpos($fijo,"!=")==true){
                            $condidiondos="^";
                            $textover="Excluir";
                        }else{
                            $condidiondos="|";
                            $textover="";
                        }
                    $ligados='dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear
                    . '&OrdenarPor=' .$groupby. '&condicionante='.$condidiondos.
                    '&valorfijo=AND '.
                    $fijover. '&Ordenar='.$sigOrdenar ;
                    echo '<a href="'.$ligados.'">';
                    echo '<img src="part_pics/Fill-Right.png" border=0>';
                    $fijover=TraeTitulo($fijover);
                    echo $textover.' '.$fijover;
                
                    echo '</a>';
                }
                    
                }
                $fijoant=$fijo;
               
              }
              
            }else{
                
                
            }
          echo ' </th></tr>';
          
          echo '<tr style="background-color:#FEFFD7;">';
            echo '<td style="text-align:center;"><b>#</td>';
            if ($groupby=="stockdescription"){
                echo '<td style="text-align:center;">';
                  echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=Unidades&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'" >';
                    echo '<b><u>'._('Codigo');
                  echo '</a>';
                echo '</td>';
            }
            
            if ($groupby=="wodescription"){
            	echo '<td style="text-align:center;">';
            	echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=Unidades&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'" >';
            	echo '<b><u>'._('No OT');
            	echo '</a>';
            	echo '</td>';
            }
            
            echo '<td style="text-align:center;">';
              echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' .$groupby. '&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'" >';
                echo '<b><u>' . $titulo2 ;
              echo '</a>';
              echo '&nbsp;&nbsp;&nbsp;';
            echo '</td>';
            

            echo '<td style="text-align:center;">';
              echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=unitsordenadas&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'" >';
                echo '<b><u>'._('Unidades Ordenadas');
              echo '</a>';
            echo '</td>';
            
            
            echo '<td style="text-align:center;">';
              echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=totalordenado&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'" >';
                echo '<b><u>'._('Total Ordenado');
              echo '</a>';
            echo '</td>';

            echo '<td style="text-align:center;">';
              echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=totalrecibido&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'" >';
                echo '<b><u>'._('Unidades Recibidas');
              echo '</a>';
            echo '</td>';

            echo '<td style="text-align:center;">';
              echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=totalrecibido&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'" >';
                echo '<b><u>'._('Total Recibido');
              echo '</a>';
            echo '</td>';
            
            echo '<td style="text-align:center;">';
            echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=por_recibido&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'" >';
            echo '<b><u>'._('% Recibido');
            echo '</a>';
            echo '</td>';
            
            echo '<td style="text-align:center;">';
              echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=unitsfacturado&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'" >';
                echo '<b><u>'._('Unidades Facturadas');
              echo '</a>';
            echo '</td>';
            echo '<td style="text-align:center;">';
              echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=totalfacturado&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'" >';
                echo '<b><u>'._('Total Facturado').'<br>';
              echo '</a>';
            echo '</td>';
                
            echo '<td style="text-align:center;">';
            echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=por_facturado&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'" >';
            echo '<b><u>'._('% Facturado');
            echo '</a>';
            echo '</td>';
            
          echo '</tr>';

            $condicion = str_replace("$","'",$condicion);
            $sql = "SELECT " . $groupby;
            $sql .= ", sum(unitsordenadas) as 'unitsordenadas'";
            $sql .= ", sum(totalordenado) as 'totalordenado'";
            $sql .= ", sum(unitsrecibidos) as 'unitsrecibidos'";
            $sql .= ", sum(totalrecibido) as 'totalrecibido'";
            $sql .= ", sum(unitsfacturado) as 'unitsfacturado'";
            $sql .= ", sum(totalfacturado) as 'totalfacturado'";
            
            $sql .= ", case when sum(totalordenado)<>0 then (sum(totalrecibido)/sum(totalordenado))*100 else 0 end as 'por_recibido'";
            
            $sql .= ", case when sum(totalrecibido)<>0 then (sum(totalfacturado)/sum(totalrecibido))*100 else 0 end as 'por_facturado'";
            
            if ($groupby=='namesupplier')
            {
                $sql .= ", supplierno";
            }    
            
            
            if ($groupby=='stockdescription')
            {
                $sql .= ", stockid";
            }
            
            
            if ($groupby=='Cuatrimestre'){
                $sql.=", anio";
            }

            if ($groupby=='Trimestre'){
                $sql.=", anio";
            }
            if ($groupby=='wodescription')
            {
            	$sql .= ", wo";
            }
            
            $sql .= " FROM DWH_Compras d, DWH_Tiempo t";
            $sql .= " WHERE d.u_tiempo = t.u_tiempo";
            $sql .= " AND Fecha between '" .$fechaini."' AND '".$fechafin."'";
            $sql .= $condicion;
            $sql .= " GROUP BY " . $groupby ;
            if ($groupby=='stockdescription')
            {
                $sql .= ", stockid";
            }
            
            if ($groupby=='namesupplier')
            {
                $sql .= ", supplierno";
            }
            if ($groupby=='wo')
            {
            	$sql .= ", wodescription";
            }
            $sql .= " ORDER BY ".$OrdenarPor." ". $Ordenar;
            if ($OrdenarPor=='por_recibido')
            {
                $sql .= ", por_recibido desc";
            }
            if ($OrdenarPor=='por_facturado')
            {
            	$sql .= ", por_facturado desc";
            }
           //echo '<pre>sql:<br>'.$sql;
            $result = DB_query($sql, $dbDataware);
            $i=0;
            $condicion = str_replace("'","$",$condicion);
            while ($myrow=DB_fetch_array($result))
            {
                $i=$i+1;
                
                echo '<tr>';
                  echo '<td style="text-align:center;">';
                    echo $i;
                  echo '</td>';
                  if ($groupby=="stockdescription"){
                    echo '<td style="text-align:right;">';
                    echo $myrow[9];
                    echo '</td>';
                  }
                  if ($groupby=="wodescription"){
                  	// se agrega link para que abra la pagina de costeo de ot
                  	echo '<td style="text-align:right;">';
                  	
                  		$linkwo= '<a target="_blank" href="'. $rootpath . '/WorkOrderCosting.php?' . SID . '&WO=' . $myrow[9] . '">' . $myrow[9]. '</a>';
                  	 echo $linkwo;
                  	echo '</td>';
                  }
                  echo '<td nowrap><font size=1>';
                   echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$sig_groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
                    .$ToMes. '&ToYear=' . $ToYear .
                    '&OrdenarPor=' .$sig_groupby. '&Ordenar=asc&filtro='.$groupby. '&condicionante=^'.
                    '&valor='.$myrow[0].'&condicion='. $condicion .'" >';
                      echo '<u>'  ;
                       echo '<img src="part_pics/Delete.png" WIDTH=10 HEIGHT=10  alt="Excluir">&nbsp;&nbsp;&nbsp;';
                    echo '</a>';
                  
                    $ver=strtoupper($myrow[0]);
                    if ($groupby=='Mes'){
                        $nombremes=glsnombremeslargo($myrow[0]);
                        $ver=$nombremes;
                    }
                    if ($groupby=='Trimestre' or $groupby=='Cuatrimestre'){
                        
                        $ver.=' - '.$myrow['anio'];
                    }
                   
                    if ($groupby=='namesupplier')
                    {
                    echo '<a target="_blank" href="SupplierInquiry.php?SupplierID='.$myrow[9].'" >';
                      echo '<img src="part_pics/Report.png">';
                    echo '</a>&nbsp;';
                        $ver=$myrow[9].' - '.$ver;
                    }
                    
                    echo '<a href="dwh_ReporteCompras.php?procesar=GENERAR&groupby='.$sig_groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
                    .$ToMes. '&ToYear=' . $ToYear .
                    '&OrdenarPor=' .$sig_groupby. '&Ordenar=asc&filtro='.$groupby.'&condicionante=|'.
                    '&valor='.$myrow[0].'&condicion='. $condicion .'" >';
                      echo '<u>' . $ver;
                      
                    echo '</a>';
                    
                  
                  echo '</td>';
                  echo '<td style="text-align:right;">' . number_format($myrow[1],2) . '</td>';
            
                  echo '<td style="text-align:right;" nowrap>$' . number_format($myrow[2],2) . '</td>';
                  echo '<td style="text-align:right;">' . number_format($myrow[3],2) . '</td>';
                  echo '<td style="text-align:right;" nowrap>$' . number_format($myrow[4],2) . '</td>';
                  if (($myrow[2])<>0) {
                  	$margenporc=($myrow[4]/abs($myrow[2]))*100;
                  
                  }else{
                  	$margenporc=0;
                  }
                  
                  echo '<td style="text-align:right;">' . number_format($margenporc,2) . '%</td>';
                  
                  echo '<td style="text-align:right;" nowrap>' . number_format($myrow[5],2) . '</td>';
                  echo '<td style="text-align:right;" nowrap>$' . number_format($myrow[6],2) . '</td>';
                  
                  if (($myrow[4])<>0) {
                  	$margenporcf=($myrow[6]/abs($myrow[4]))*100;
                  
                  }else{
                  	$margenporcf=0;
                  }
                  
                  echo '<td style="text-align:right;">' . number_format($margenporcf,2) . '%</td>';
                  
                echo '</tr>';
                
                $sumaSaldoTotal = $sumaSaldoTotal + $myrow[1];
                $sumaSaldoVencido = $sumaSaldoVencido + $myrow[2];
                $sumaSaldoVencido1_30 = $sumaSaldoVencido1_30 + $myrow[3];
                $sumaSaldoVencido31_60 = $sumaSaldoVencido31_60 + $myrow[4];
                $sumaSaldoVencido61_90 = $sumaSaldoVencido61_90 + $myrow[5];
                $sumaSaldoVencido91 = $sumaSaldoVencido91 + $myrow[6];
            }
            if ($groupby=="stockdescription"){
                $colspan=3;
            }else{
                $colspan=2;
            }
            if ($groupby=="wodescription"){
            	$colspan=3;
            }
            
            if ($sumaSaldoVencido>0){
                $PorcTotal=($sumaSaldoVencido31_60)/$sumaSaldoVencido;
            }else{
                $PorcTotal=0;
            }
            if ($sumaSaldoVencido31_60>0){
            	$PorcTotalF=($sumaSaldoVencido91)/$sumaSaldoVencido31_60;
            }else{
            	$PorcTotalF=0;
            }
            
                echo '<tr style="background-color:#EBFEEA;">';
                  echo '<td colspan='.$colspan.' style="text-align:right;">';
                    echo '<b>TOTALES : ';
                  echo '</td>';
                  echo '<td style="text-align:right;">';
                    echo '<b>' .number_format($sumaSaldoTotal,2);
                  echo '</td>';


                  echo '<td nowrap style="text-align:right;">';
                    echo '<b>$' .number_format($sumaSaldoVencido,2);
                  echo '</td>';

                  echo '<td style="text-align:right;">';
                    echo '<b>' .number_format($sumaSaldoVencido1_30,2);
                  echo '</td>';

                  echo '<td nowrap style="text-align:right;">';
                    echo '<b>$' .number_format($sumaSaldoVencido31_60,2);
                  echo '</td>';
                  
                  echo '<td nowrap style="text-align:right;">';
                  echo '<b>' .number_format($PorcTotal*100,2).'%';
                  echo '</td>';
                  
                  echo '<td style="text-align:right;">';
                   echo '<b>' .number_format($sumaSaldoVencido61_90,2);
                  echo '</td>';
                  echo '<td style="text-align:right;">';
                   echo '<b>$' .number_format($sumaSaldoVencido91,2);
                  echo '</td>';
                  echo '<td nowrap style="text-align:right;">';
                  echo '<b>' .number_format($PorcTotalF*100,2).'%';
                  echo '</td>';
                echo '</tr>';
        echo '</table>';
      echo '</td>';
    echo '</tr>';
  echo '</table>';  
  if(!isset($_POST['PrintExcel'])){
echo '</form>';
  }


?>


<script>

    function ExportaExcel(){
        
        window.open("dwh_ReporteVentasExcel.php");
        
    }

</script>
