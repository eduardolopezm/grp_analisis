<link rel="stylesheet" href="css/v3/librerias/jquery-ui/jquery-ui-1.11.4/jquery-ui.min.css">

    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
    <script src="ckeditor/ckeditor.js"></script>

<?php


$permiso_filtrocuentapresupuestal = 1;//Havepermission($_SESSION['UserID'], 107, $db);
$permiso_filtrocuentacontable = 1;//Havepermission($_SESSION['UserID'], 108, $db);
$permiso_filtrodocumento = 1;//Havepermission($_SESSION['UserID'], 109, $db);
$permiso_segundadimension = 1;//Havepermission($_SESSION['UserID'], 104, $db);



function fn1dy2d($unoOdos)
{
    $permiso_filtrocuentapresupuestal = 1;

    $imagenunoodos = "";

    if ($unoOdos=="uno") {
        $imagenunoodos = "1d";
        $clase = "texto_normal";
    }
    if ($unoOdos=="dos") {
        $imagenunoodos = "2d";
        $clase = "titulos_sub_principales";
    }
    $tabla= '<br><td  align="left" cellpadding="1" colspan=1><table><tr><td class='.$clase.'>
                            <input type=radio name=unoodos value="" checked onclick="if(this.checked) document.FDatosB.allcolumns.value=1;">
                            <img title="Una dimension" src="images/'.$imagenunoodos.'.png" width=20 border=0 >
                        </td>
                        <td class="'.$clase.'" align="center"> <input type=radio name='.$unoOdos.' value="tiempo">x Tiempo<br>
                            <select name="tiempo'.$unoOdos.'" onchange="actualizaGroupBy(this);">
                                <option value="AnioFiscal">A&ntilde;o Fiscal</option>
                                <option value="TrimestreFiscal">Trimestre Fiscal</option>
                                <option value="CuatrimestreFiscal" >Cuatrimestre Fiscal</option>
                                <option value="PeriodoFiscal">Periodo Fiscal</option>
                                <option value="mes">Mes</option>
                            </select>   
                        </td>';
                        
        
            $tabla= $tabla . '<td class="'.$clase.'" align="center"><input type=radio name='.$unoOdos.' value="clavepresupuestal">x Clave Pres.
                                <select name="clavepresupuestal'.$unoOdos.'" onchange="actualizaGroupBy(this);">
                                    <option value="ramo">***Administrativa***</option>
                                    <option value="ramo" >Ramo</option>
                                    <option value="organosuperior">Organo Superior</option>
                                    <option value="unidadpresupuestal" >Unidad Presupuestal</option>
                                    <option value="rubrodeingreso" >***Economica***</option>
                                    <option value="rubrodeingreso">Rubro de Ingresos</option>
                                    <option value="tipodegasto">Tipo de Gasto</option>
                                    <option value="objetodelgasto">Objeto del Gasto</option>
                                    <option value="finalidad_funcion" '; $tabla.='>***Funcional***</option>
                                    <option value="finalidad_funcion">Finalidad-Funcion</option>
                                    <option value="subfuncion">SubFuncion</option>
                                    <option value="ejetematico">Eje Tematico</option>
                                    <option value="sector">Sector</option>
                                    <option value="programa">Programa</option>
                                    <option value="subprograma">SubPrograma</option>
                                    <option value="objetivos">Objetivos</option>
                                    <option value="proyecto_estrategias">Proyecto-Estra</option>
                                    <option value="estrategias">Estrategias</option>
                                    <option value="obra">Obra</option>
                                    <option value="beneficiario" '; $tabla.='>***Cobertura***</option>
                                    <option value="beneficiario" >Beneficiario</option>
                                    <option value="espaciogeografico">Espacio Geografico</option>
                                </select>
                            </td>';
        
        
            $tabla= $tabla . '<td class="'.$clase.'" align="center"><input type=radio name='.$unoOdos.' value="contabilidad">x Cuenta
                                <select name="contabilidad'.$unoOdos.'" onchange="actualizaGroupBy(this);">
                                    <option value="genero">Genero</option>
                                    <option value="grupo">Grupo</option>
                                    <option value="rubro">Rubro</option>
                                    <option value="cuenta">Cuenta</option>
                                    <option value="subcuenta">SubCuenta</option>
                                    <option value="sscuenta">SSCuenta</option>
                                    <option value="ssscuenta">SSSCuenta</option>
                                    <option value="sssscuenta">SSSSCuenta</option>
                                    <option value="sujetocontable">Sujeto Contable</option>
                                </select>
                            </td>';
        
        
            $tabla= $tabla . ' <td class="'.$clase.'" align="center"><input type=radio name='.$unoOdos.' value="documento">x Documento<br>
                                <select name="documento'.$unoOdos.'" onchange="actualizaGroupBy(this);">
                                    <option value="tipopoliza" >Tipo</option>
                                    <option value="numeropoliza" >Numero Docto</option>
                                    <option value="Fechacaptura" >FechaCaptura</option>
                                    <option value="Aniocaptura" >A&ntilde;o Captura</option>
                                    <option value="Mescaptura" >Mes Captura</option>
                                    <option value="Semanacaptura" >Semana Captura</option>
                                    <option value="Diacaptura" >Dia Captura</option>
                                    <option value="NombreDiacaptura" >Nombre Dia Captura</option>
                                    
                                </select>
                            </td>';
        
                    $tabla= $tabla . '</tr>             

                </td>';

                $tabla  = $tabla . "</table>";
                    
/*                $tablaH= '<table align="center" cellpadding="3" cellspacing="0" border="0">
                    <tr>
                        <td class="texto_normal"><input type=radio name=groupby value="tiempo">x Tiempo</td>
                        <td class="texto_normal"><input type=radio name=groupby value="clavepresupuestal">x Clave<br>Presupuestal</td>
                        <td class="texto_normal"><input type=radio name=groupby value="contabilidad">x Contabilidad</td>
                        <td class="texto_normal" colspan=2><input type=radio name=groupby value="documento">x Documento</td>
                    </tr>';*/
                echo $tabla;
}


$Ordenar = "";
$namecol = false;
/*
error_reporting(E_ALL);
ini_set('display_errors', '1');
*/
include('includes/session.inc');

$funcion=1567;
//$funcion=91;
include('includes/SecurityFunctions.inc');
//include('includes/ConnectDB_Dataware.inc');
$title=_('Dataware de Presupuesto de Egresos');





if (isset($_POST['reiniciar'])) {
    $_SESSION['valoreshist'] = "";
    $_SESSION['valorfijo']="";
    $_SESSION['chkcol']=array();
    $_SESSION['topcolumns']=array();
    $_SESSION['condiciones']="";
    $_SESSION['condname']="";
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
//

$arrcolscondescripcion = array(0 => 'ramo',
1 => 'organosuperior',
2 => 'unidadpresupuestal',
3 => 'rubrodeingresos',
4 => 'tipodegasto',
5 => 'objetodelgasto',
6 => 'finalidad',
7 => 'funcion',
8 => 'subfuncion',
9 => 'ejetematico',
10 => 'sector',
11 => 'programa',
12 => 'subprograma',
13 => 'objetivos',
14 => 'proyecto',
15 => 'estrategias',
16 => 'obra',
17 => 'beneficiario',
18 => 'espaciogeografico',
19 => 'mision',
20 => 'propositoinstitucional',
21 => 'problema',
22 => 'dimensiondelproblema',
23 => 'metadelindicador',
24 => 'genero',
25 => 'grupo',
26 => 'rubro',
27 => 'cuenta',
28 => 'subcuenta',
29 => 'sscuenta',
30 => 'ssscuenta',
31 => 'sssscuenta',
32 => 'sujetocontable',
33 => 'numeropoliza'
);

$arrparametrosintercambio = array(0 => 'AnioFiscal',
1 => 'TrimestreFiscal',
2 => 'rubrodeingresos',
3 => 'CuatrimestreFiscal',
4 => 'PeriodoFiscal',
5 => 'Fechacaptura',
6 => 'Aniocaptura',
7 => 'Mescaptura',
8 => 'Semanacaptura',
9 => 'Diacaptura',
10 => 'NombreDiacaptura'
);


$arrparametrosintercambiodwhconta = array(0 => 'AnioFiscal',
1 => 'TrimestreFiscal',
3 => 'CuatrimestreFiscal',
4 => 'PeriodoFiscal',
5 => 'Fechacaptura',
6 => 'Aniocaptura',
7 => 'Mescaptura',
8 => 'Semanacaptura',
9 => 'Diacaptura',
10 => 'NombreDiacaptura',
11 => 'genero',
12 => 'grupo',
13 => 'rubro',
14 => 'cuenta',
15 => 'subcuenta',
16 => 'sscuenta',
17 => 'ssscuenta',
18 => 'sssscuenta',
19 => 'sujetocontable',
20 => 'tipopoliza',
21 => 'numeropoliza'
);
$arrparametrosintercambiodwhcontacvepresupuestal = array(0 => 'ramo',
1 => 'organosuperior',
2 => 'unidadpresupuestal',
3 => 'rubrodeingresos',
4 => 'tipodegasto',
5 => 'objetodelgasto',
6 => 'finalidad',
7 => 'funcion',
8 => 'subfuncion',
9 => 'ejetematico',
10 => 'sector',
11 => 'programa',
12 => 'subprograma',
13 => 'objetivos',
14 => 'proyecto',
15 => 'estrategias',
16 => 'obra',
17 => 'beneficiario',
18 => 'espaciogeografico',
19 => 'mision',
20 => 'propositoinstitucional',
21 => 'problema',
22 => 'dimensiondelproblema',
23 => 'metadelindicador'

);




// variables de sesion para la grafica
$_SESSION["consultareporte"]= "";
$_SESSION["filtrodw"]= "";

if (isset($_GET['novalorfijo']) && ($_GET['novalorfijo']==1)) {
    $_SESSION['valorfijo']="";
}

//Ver config
$DatawareDB = $_SESSION['DwDatabase'];
$host       = $_SESSION['DwHost'];
$dbuser     = $_SESSION['DwUser'];
$dbpassword = $_SESSION['DwPass'];
$mysqlport  = $_SESSION['DwDBPort'];
$dbsocket   = $_SESSION['DwSock'];

$dbDataware=mysqli_connect('23.111.130.190', 'desarrollo', 'p0rtAli70s', 'DatawareGubernamental_DES');

//echo ":".$host.".".$dbuser.".".$dbpassword.".".$DatawareDB.".".$mysqlport.".".$dbsocket;

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

# **********************************************************************
# ***** RECUPERA VALORES DE FECHAS *****

if (isset($_POST['FromYear'])) {
    $FromYear = $_POST['FromYear'];
} elseif (isset($_GET['FromYear'])) {
    $FromYear = $_GET['FromYear'];
} else {
    $FromYear=date('Y');
};

if (isset($_POST['FromMes'])) {
    $FromMes= $_POST['FromMes'];
} elseif (isset($_GET['FromMes'])) {
    $FromMes = $_GET['FromMes'];
} else {
    $FromMes="01";//date('m');
};

if (isset($_POST['FromDia'])) {
    $FromDia= $_POST['FromDia'];
} elseif (isset($_GET['FromDia'])) {
    $FromDia = $_GET['FromDia'];
} else {
    $FromDia="01";
};

if (isset($_POST['ToYear'])) {
    $ToYear= $_POST['ToYear'];
} elseif (isset($_GET['ToYear'])) {
    $ToYear = $_GET['ToYear'];
} else {
    $ToYear=date('Y');
};

if (isset($_POST['ToMes'])) {
    $ToMes= $_POST['ToMes'];
} elseif (isset($_GET['ToMes'])) {
    $ToMes = $_GET['ToMes'];
} else {
    $ToMes=date('m');
};

if (isset($_POST['ToDia'])) {
    $ToDia= $_POST['ToDia'];
} elseif (isset($_GET['ToDia'])) {
    $ToDia = $_GET['ToDia'];
} else {
    $ToDia=date('d');
};

$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
$fechafin= rtrim($ToYear).'-'.rtrim($ToMes).'-'.rtrim($ToDia) . ' 23:59:59';
     
     
$InputError = 0;
if ($fechaini > $fechafin) {
    include('includes/header.inc');
    $pivot = true;
    include('javascripts/libreriasGrid3.inc');
    ?>
    <script type="text/javascript" src="javascripts/dwh_ReportePresupuestario.js"></script>';
    <?php

    
    $InputError = 1;
    prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'), 'error');
    exit;
} else {
    $InputError = 0;
}
     
# ******************************************************************************

if (isset($_POST['procesar'])) {
    $procesar = $_POST['procesar'];
} else {
    if (isset($_GET['procesar'])) {
        $procesar = $_GET['procesar'];
    }
};

if (isset($_POST['groupby'])) {
    $groupby= $_POST[$_POST['groupby']];
} elseif (isset($_GET['groupby'])) {
    $groupby = $_GET['groupby'];
} else {
    $groupby = "AnioFiscal";
};

if (isset($_POST['groupbysecond'])) {
    $groupbysecond= $_POST[$_POST['groupbysecond']];
} elseif (isset($_GET['groupbysecond'])) {
    $groupbysecond = $_GET['groupbysecond'];
} else {
    $groupbysecond = "";
};


if (isset($_POST['condicion'])) {
    $condicion= $_POST['condicion'];
} elseif (isset($_GET['condicion'])) {
    $condicion = $_GET['condicion'];
} else {
    $condicion = "";
};


if (isset($_GET['valorfijo'])) {
    if (strlen($_GET['valorfijo'])>5) {
        unset($_SESSION['valorfijo']);
        $_SESSION['valorfijo']=str_replace('$', '', $_GET['valorfijo']);
        $_SESSION['valorfijo']=str_replace('=', '=$', $_SESSION['valorfijo']);
        $_SESSION['valorfijo']=trim($_SESSION['valorfijo'])."$";
    }
}

    





//echo "valor: ".$valormultiple;




//columnas visibles
$totCol = 21;


if (!isset($_POST['opcdelfilter'])) {
    $_POST['opcdelfilter'] = "";
}
if (!isset($_POST['opcsavefilter'])) {
    $_POST['opcsavefilter'] = "";
}
if (!isset($_REQUEST['keepvisible'])) {
    $_REQUEST['keepvisible'] = "";
}


if (($_POST['opcdelfilter']!="1" and $_POST['opcsavefilter']!="1" and !isset($_POST['procesar']) and $_REQUEST['keepvisible']!=1) || ($_POST['allcolumns']==1)) {
    //echo "<br>ENTRA AKI 1";
    $_SESSION['chkcol'] = array();
    for ($j=1; $j<=$totCol; $j++) {
        $_SESSION['chkcol'][$j] = "checked";
    }
} else {
    //echo "<br>ENTRA AKI 2";
    if ((isset($_POST['procesar']) and $_POST['allcolumns']!=1) || $_POST['opcsavefilter']=="1" || $_POST['opcdelfilter']=="1") {
        //echo "<br>ENTRA AKI 3";
        if ($_POST['dimensionuno']) {
            for ($j=1; $j<=$totCol; $j++) {
                $_SESSION['chkcol'][$j] = ($_POST['chkcol'.$j]=="on")?"checked":"";
            }
        }
    }
}


if (!isset($_REQUEST['filterlist'])) {
    $_REQUEST['filterlist'] = "";
}

//verificar si carga filtro guardado
if ($_REQUEST['filterlist'] and $_POST['opcdelfilter']!="1") {
    $sql = "Select * from DWH_userfilters
        WHERE proyecto='" . $funcion . "' and filtername = '".$_POST['filterlist']."' 
            and userid = '".$_SESSION['UserID']."'";
    
    $rs = DB_query($sql, $dbDataware);
    $rows = DB_fetch_array($rs);
    $condicion = $rows['filter'];
    $groupby = $rows['dimension1'];
    $groupbysecond = $rows['dimension2'];
    $arrcolumnas = explode(",", $rows['columns']);
    
    for ($j=1; $j<=$totCol; $j++) {
        $_SESSION['chkcol'][$j] = (in_array($j, $arrcolumnas))?"checked":"";
    }

    $Ordenar = "header";
    $Ordenar = "asc";
    $_GET['filtrar']=0;
}


$titulo = trim($groupby);
switch ($titulo) {
    case 'AnioFiscal':
        $titulo2 = 'A&#209;o<br>'. _('Fiscal');
        $sig_groupby = "TrimestreFiscal";
        break;
    case 'TrimestreFiscal':
        $titulo2 = _('Trimestre').'<br>'. _('Fiscal');
        $sig_groupby = "CuatrimestreFiscal";
        break;
    case 'CuatrimestreFiscal':
        $titulo2 = _('Cuatrimestre').'<br>'. _('Fiscal');
        $sig_groupby = "PeriodoFiscal";
        break;
    case 'PeriodoFiscal':
        $titulo2 = _('Periodo').'<br>'. _('Fiscal');
        $sig_groupby = "ramo";
        break;
    case 'ramo':
        $titulo2 = _('Ramo');
        $sig_groupby = "organosuperior";
        break;
    case 'organosuperior':
        $titulo2 = _('Organo').'<br>'. _('Superior');
        $sig_groupby = "unidadpresupuestal";
        break;
    case 'unidadpresupuestal':
        $titulo2 = _('Unidad').'<br>'. _('Presupuestal');
        $sig_groupby = "rubrodeingreso";
        break;
    case 'rubrodeingreso':
        $titulo2 = _('Rubro de').'<br>'. _('Ingreso');
        $sig_groupby = "tipodegasto";
        break;
    case 'tipodegasto':
        $titulo2 = _('Tipo de').'<br>'. _('Gasto');
        $sig_groupby = "objetodelgasto";
        break;
    case 'objetodelgasto':
        $titulo2 = _('Objeto').'<br>'. _('del Gasto');
        $sig_groupby = "finalidad_funcion";
        break;
    case 'finalidad_funcion':
        $titulo2 = _('Finalidad - Funcion');
        $sig_groupby = "subfuncion";
        break;
    case 'subfuncion':
        $titulo2 = _('SubFuncion');
        $sig_groupby = "ejetematico";
        break;
    case 'ejetematico':
        $titulo2 = _('Eje').'<br>'. _('Tematico');
        $sig_groupby = "sector";
        break;
    case 'sector':
        $titulo2 = _('Sector');
        $sig_groupby = "programa";
        break;
    case 'programa':
        $titulo2 = _('Programa');
        $sig_groupby = "subprograma";
        break;
    case 'subprograma':
        $titulo2 = _('Subprograma');
        $sig_groupby = "objetivos";
        break;
    case 'objetivos':
        $titulo2 = _('Objetivos');
        $sig_groupby = "proyecto_estrategias";
        break;
    case 'proyecto_estrategias':
        $titulo2 = _('Proyecto - Estrategias');
        $sig_groupby = "obra";
        break;
    case 'obra':
        $titulo2 = _('Obra');
        $sig_groupby = "beneficiario";
        break;
    case 'beneficiario':
        $titulo2 = _('Beneficiario');
        $sig_groupby = "espaciogeografico";
        break;
    case 'espaciogeografico':
        $titulo2 = _('Espacio').'<br>'. _('Geografico');
        $sig_groupby = "genero";
        break;
    
    case 'genero':
        $titulo2 = _('Genero');
        $sig_groupby = "grupo";
        break;
    case 'grupo':
        $titulo2 = _('Grupo');
        $sig_groupby = "rubro";
        break;
    case 'rubro':
        $titulo2 = _('Rubro');
        $sig_groupby = "cuenta" ;
        break;
    case 'cuenta':
        $titulo2 = _('Cuenta');
        $sig_groupby = "subcuenta";
        break;
    case 'subcuenta':
        $titulo2 = _('Subcuenta');
         $sig_groupby = "sscuenta";
        break;
    case 'sscuenta':
        $titulo2 = _('SSCuenta');
        $sig_groupby = "ssscuenta";
        break;
    case 'ssscuenta':
        $titulo2 = _('SSSCuenta');
        $sig_groupby = "sssscuenta";
        break;
    case 'sssscuenta':
        $titulo2 = _('SSSSCuenta');
        $sig_groupby = "sujetocontable";
        break;
    case 'sujetocontable':
        $titulo2 = _('Sujeto Contable');
        $sig_groupby = "tipopoliza";
        break;
    case 'tipopoliza':
        $titulo2 = _('Tipo');
        $sig_groupby = "numeropoliza";
        break;
    case 'numeropoliza':
        $titulo2 = _('Num<br>Docto');
        $sig_groupby = "Fechacaptura";
        break;
    case 'Fechacaptura':
        $titulo2 = _('Fecha<br>Captura');
        $sig_groupby = "Aniocaptura";
        break;
    case 'Aniocaptura':
        $titulo2 = 'A&#209;o<br>Captura';
        $sig_groupby = "Mescaptura";
        break;
    case 'Mescaptura':
        $titulo2 = _('Mes<br>Captura');
        $sig_groupby = "Semanacaptura";
        break;
    case 'Semanacaptura':
        $titulo2 = _('Semana<br>Captura');
        $sig_groupby = "Diacaptura";
        break;
    case 'Diacaptura':
        $titulo2 = _('Dia<br>Captura');
        $sig_groupby = "NombreDiacaptura";
        break;
   
    case 'NombreDiacaptura':
        $titulo2 = _('Nombre').'<br>'. _('Dia Captura');
        $sig_groupby = "AnioFiscal";
        break;
}


$titulografica= html_entity_decode(strtoupper(str_replace('<br>', ' ', $titulo2)));
$titulo2 = strtoupper($titulo2);
$colsconsulta= "";

if (isset($_POST["tipografica"])) {
    $_POST["tipografica"]= $_POST["tipografica"];
} else {
    $_POST["tipografica"]= 7;
}

$inprocess=false;
$sql = "select fechaultimomovimiento,ADDDATE(fechaultimomovimiento, INTERVAL 1 DAY) as fechaFin
        from DW_Status
        where nombre='DatawareEgresos' and estado <> 'OK'";
$rs = DB_query($sql, $dbDataware);
if (DB_num_rows($rs) > 0) {
    $inprocess=true;
    $rows = DB_fetch_array($rs);
    $fechaFinDw= $rows['fechaFin'];
}

//Si el DW esta en actualizaci�n no busca nada
if ($inprocess) {
    include('includes/header.inc');
    include('javascripts/libreriasGrid3.inc');
    ?>
    <script type="text/javascript" src="javascripts/dwh_ReportePresupuestario.js"></script>';
    <?php
    
    echo '<HEAD><TITLE> :: Dataware de Presupuestos de Egresos</TITLE></HEAD>';

    echo '<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%" >';
    echo '  <tr>
                <td class="texto_status2" colspan=2>
                    <img src="images/d_presupuestos.png" height="25" width="25" title="' . _('Reporte Dataware Contabilidad') . '" alt="">
                    Dataware de Presupuestos de Egresos<br>
                    <br>
                    En Proceso de Actualizaci&oacute;n..., tiempo estimado de finalizaci&oacute;n: '.$fechaFinDw.'
                    <br>
                </td>
            </tr>';
    echo '</table>';
} else {
    if (!isset($_POST['excel'])) {
        include('includes/header.inc');
        include('javascripts/libreriasGrid3.inc');
        ?>
    <script type="text/javascript" src="javascripts/dwh_ReportePresupuestario.js"></script>';
    <?php
        
        echo '<HEAD><TITLE> :: Dataware de Presupuestos de Egresos</TITLE></HEAD>';

        if ($groupbysecond) {
            $dim=0;
        } else {
            $dim=1;
        }

        if (!isset($_POST['filterlist'])) {
            $_POST['filterlist'] = "";
        }
            
        

    
        //verificar segunda dimension
        $_SESSION['topcolumns'] = array();
        $_SESSION['titulocolumna'] = array();
        if ($groupbysecond) {
            $condact = str_replace("$", "'", $condicion);
        
        
            if (!(array_search($groupbysecond, $arrcolscondescripcion)===false)) {
                $nombrecampo = "txt" . $groupbysecond;
            } else {
                $nombrecampo = $groupbysecond;
            }
        
            $qry = "SELECT distinct $groupbysecond as colname, $nombrecampo as titulocolumna
            FROM DW_Presupuestos d 
                INNER JOIN DWD_TiempoFiscal t ON d.u_tiempo = t.u_tiempo
                INNER JOIN DWD_Tiempo t2 ON d.u_tiempocaptura = t2.u_tiempo
                 
            WHERE Fecha between '" .$fechaini."' AND '".$fechafin."'
            $condact  
            GROUP BY $groupbysecond ORDER BY $groupbysecond
            ";
            echo "<pre>CONSULTA: $qry";
            $rstopcol = DB_query($qry, $dbDataware);
            $index=1;
            while ($rsmyrow = DB_fetch_array($rstopcol)) {
                $_SESSION['topcolumns'][$index++] = $rsmyrow['colname'];
                $_SESSION['titulocolumna'][$index] = $rsmyrow['titulocolumna'];
            }
        } else {
            $_SESSION['topcolumns'][]="";
        }





        ?>

<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
        <div class="col-md-3 col-xs-3">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
            Búsqueda
          </a>
        </div>
      </h4>
    </div>


<div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="col-md-6">
          <div class="form-inline row">
              <div class="col-md-9">


        <?php
    

        echo '<td class="texto_normal">' . _('Desde : ') . '</td>';
                        echo'<td><select id="FromDia" Name="FromDia">';
                            $sql = "SELECT * FROM cat_Days";
                            echo "<pre>CONSULTA: $sql";
                            $Todias = DB_query($sql, $db);
                            //$result = DB_query($sql, $dbDataware);
        while ($myrowTodia=DB_fetch_array($Todias, $dbDataware)) {
            $Todiabase=$myrowTodia['DiaId'];
            if (rtrim(intval($FromDia))==rtrim(intval($Todiabase))) {
                echo '<option  VALUE="' . $myrowTodia['Dia'] .  '" selected>' .$myrowTodia['Dia'];
            } else {
                echo '<option  VALUE="' . $myrowTodia['Dia'] .  '" >' .$myrowTodia['Dia'];
            }
        }
        echo "</select>";


                            echo'<select ID="FromMes" Name="FromMes">';
                            $sql = "SELECT * FROM cat_Months";
                            $ToMeses = DB_query($sql, $db);
        while ($myrowToMes=DB_fetch_array($ToMeses, $dbDataware)) {
            $ToMesbase=$myrowToMes['u_mes'];
            if (rtrim(intval($FromMes))==rtrim(intval($ToMesbase))) {
                echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" selected>' .$myrowToMes['mes'];
            } else {
                echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
            }
        }
                            echo '</select>';
                            echo '&nbsp;<input ID="FromYear" name="FromYear" type="text" size="4" value='.$FromYear.'>';
                        echo '</td>';

?>

    </div>
</div>

<br>
<div class="form-inline row">
              <div class="col-md-9">

<?php


                        echo '<td class="texto_normal">' . _('Hasta:') . '</td>';
                        echo'<td><select id="ToDia" Name="ToDia">';
                            $sql = "SELECT * FROM cat_Days";
                            $Todias = DB_query($sql, $db, '', '');
while ($myrowTodia=DB_fetch_array($Todias, $dbDataware)) {
    $Todiabase=$myrowTodia['DiaId'];
    if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))) {
        echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" selected>' .$myrowTodia['Dia'];
    } else {
        echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
    }
}
         echo '</select>';
                        echo '</td>';
                        echo'<td class="texto_normal" align="center">';
                            echo'<select id="ToMes" Name="ToMes">';
                            $sql = "SELECT * FROM cat_Months";
                            $ToMeses = DB_query($sql, $db, '', '');
while ($myrowToMes=DB_fetch_array($ToMeses, $dbDataware)) {
    $ToMesbase = $myrowToMes['u_mes'];
    if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))) {
        echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" selected>' .$myrowToMes['mes'];
    } else {
        echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
    }
}
        echo '</select>';
                            
                            echo '&nbsp;<input Id="ToYear" name="ToYear" type="text" size="4" value='.$ToYear.'>';
                        echo'</td>';
                    echo '</tr>';
                echo '</table>';
            echo '</td></tr>';

            echo "<br>";

            echo "<br>";


            //fn1dy2d('uno');
            //fn1dy2d('dos');
    }
}
?>

 </div>
</div>
</div>

</div>
<component-button type="button" id="btnBuscar" name="btnBuscar" onclick="fnAbrirReporte()" value="Buscar"></component-button>
</div>

          

                    
          <br>


</div>




 <!-- <select id="select1D" name="select1D[]" class="form-control select1D" multiple="multiple"> </select>  -->
    
    <!--  <select id="select2D"> </select> -->


 <div id="divPivotGridDesigner" style="height: 400px; width: 1200;">
                </div>
                <br>
<div id="tabladinamica" name="tabladinamica" style="height: 400px; width: 1200; background-color: white;"></div>

<!-- <div id="filtro" name="filtro" style="height: 400px; width: 1800px; background-color: white;"></div>  -->

<!-- <button id="btnFiltrar" onclick="fnFiltar()"></button> -->


               


<div id="grid" name="grid" style="height: 400px; width: 1800px; background-color: white;"></div>
<?php





?>
</div>
</div>
</div>

<?php
include('includes/footer_Index.inc');

