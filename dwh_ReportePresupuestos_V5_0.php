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
    }else
        alert('Debe introducir un nombre para el filtro');
}

function ExportaExcel(){
    window.open("dwh_ReportePresupuestosExcel.php");
}

function selAll(obj){
    var I = document.getElementById('totrows').value;

    alert("valor de :" + obj.name);

    for(i=1; i<=I; i++) {
        var concatenar = "chk" + i;
        chkobj = document.getElementById(concatenar);
        if(chkobj != null) {
            chkobj.checked = obj.checked;
        }
    }

    // alert(document.getElementById('selmultiple').value);
}

function invsel(obj){
    var I = document.getElementById('totrows').value;
    
    for(i=1; i<=I; i++) {
        var concatenar = "chk" + i;
        chkobj = document.getElementById(concatenar);
        if(chkobj != null) {
            chkobj.checked = !chkobj.checked; 
        }
    }
}


</script>
<?php
$Ordenar = "";
$namecol = false;
$groupbysecondvalue ="";
$fijoant = 0;
//if (!isset($_SESSION['valorfijo']))
 $_SESSION['valorfijo']="";
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

$permiso_filtrocuentapresupuestal = 1;//Havepermission($_SESSION['UserID'], 107, $db);
$permiso_filtrocuentacontable = 1;//Havepermission($_SESSION['UserID'], 108, $db);
$permiso_filtrodocumento = 1;//Havepermission($_SESSION['UserID'], 109, $db);
$permiso_segundadimension = 1;//Havepermission($_SESSION['UserID'], 104, $db);


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
2 => 'unidadejecutora',
3 => 'finalidad',
4 => 'funcion',
5 => 'subfuncion',
6 => 'reasignacion',
7 => 'actividadinstitucional',
8 => 'programa',
9 => 'partidaespecifica',
10 => 'tipodegasto',
11 => 'fuentedefinanciamiento',
12 => 'espaciogeografico',
13 => 'proyecto',
14 => 'estrategias',
15 => 'obra',
16 => 'rubrodeingresos',
17 => 'objetodelgasto',
18 => 'beneficiario',
19 => 'mision',
20 => 'propositoinstitucional',
21 => 'problema',
22 => 'dimensiondelproblema',
23 => 'metadelindicador',
24 => 'genero',
25 => 'grupo',
//25 => 'rubro',
26 => 'cuenta',
27 => 'subcuenta',
28 => 'sscuenta',
29 => 'ssscuenta',
30 => 'sssscuenta',
31 => 'sujetocontable',
32 => 'numeropoliza'
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
//13 => 'rubro',
13 => 'cuenta',
14 => 'subcuenta',
15 => 'sscuenta',
16 => 'ssscuenta',
17 => 'sssscuenta',
18 => 'sujetocontable',
19 => 'tipopoliza',
20 => 'numeropoliza'
);
$arrparametrosintercambiodwhcontacvepresupuestal = array(0 => 'ramo',
1 => 'organosuperior',
2 => 'unidadejecutora',
3 => 'finalidad',
4 => 'funcion',
5 => 'subfuncion',
6 => 'reasignacion',
7 => 'actividadinstitucional',
8 => 'programa',
9 => 'partidaespecifica',
10 => 'tipodegasto',
11 => 'fuentedefinanciamiento',
12 => 'espaciogeografico',
13 => 'proyecto',
14 => 'estrategias',
15 => 'obra',
16 => 'rubrodeingresos',
17 => 'objetodelgasto',
18 => 'beneficiario',
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

if (isset($_SESSION['valorfijo'])) {
    if (strpos($condicion, $_SESSION['valorfijo'])===false) {
        $condicion= $_SESSION['valorfijo'].' '.$condicion;
    }
}
    
//verificar si selecciono condiciones adicionales
$wherecond="";
$arrwherecond=array();
if (isset($_POST['wherecond'])) {
    $wherecond = " AND (";
    $pent = 0;
    for ($ds=0; $ds<count($_POST['wherecond']); $ds++) {
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
 
if (isset($_POST['filtro'])) {
    $filtro= $_POST['filtro'];
} elseif (isset($_GET['filtro'])) {
    $filtro = $_GET['filtro'];
} else {
    $filtro = "";
};

//salvar filtro 
if (isset($_POST['opcsavefilter'])) {
    if ($_POST['opcsavefilter']=="1") {
        $nombrefiltro = $_POST['namenewfilter'];
        $sql = "Select * from DWH_userfilters
        WHERE proyecto='" . $funcion . "' and filtername = '$nombrefiltro'
        and userid = '".$_SESSION['UserID']."'";
        $rs = DB_query($sql, $dbDataware);
        if (DB_num_rows($rs) > 0) {
            prnMsg(_('El nombre '.$nombrefiltro.' ya existe en su lista de filtros'), 'error');
        } else {
             $lista="";
            for ($i=1; $i<=count($_SESSION['chkcol']); $i++) {
                if ($_SESSION['chkcol'][$i]) {
                    $lista.=$i.",";
                }
            }

            $lista = substr($lista, 0, strlen($lista)-1);
            $sql = "Insert DWH_userfilters VALUES ('".$_SESSION['UserID']."','$nombrefiltro','$condicion','$groupby','$groupbysecond','$lista','" . $funcion . "')";
            $rs = DB_query($sql, $dbDataware);
        }
    }
}
//eliminar filtro
if (isset($_POST['opcdelfilter'])) {
    if ($_POST['opcdelfilter'] == "1") {
        $nombrefiltro = $_POST['filterlist'];
        $sql = "DELETE from DWH_userfilters
            WHERE proyecto='" . $funcion . "' and filtername = '$nombrefiltro'
                 and userid = '".$_SESSION['UserID']."'";
        $r=DB_query($sql, $dbDataware);
    }
}

if (isset($_GET['OrdenarPor'])) {
    $OrdenarPor = $_GET['OrdenarPor'];
} else {
    $OrdenarPor = $groupby;
};

if (isset($_POST['procesar'])) {
    $OrdenarPor="header";
}



if ($Ordenar == "asc") {
    $sigOrdenar = "desc";
} else {
    $sigOrdenar = "asc";
};

if (isset($_POST['filtro'])) {
    $filtro= $_POST['filtro'];
} else {
    if (isset($_GET['filtro'])) {
        $filtro = $_GET['filtro'];
    } else {
        $filtro = "";
    }
}

if (isset($_POST["filtroexcluir"])) {
    $filtro= $_POST["filtroexcluir"];
}

if (isset($_POST['valor'])) {
    $valor= $_POST['valor'];
} else {
    if (isset($_GET['valor'])) {
        $valor = $_GET['valor'];
    } else {
        $valor = "";
    }
}

if (isset($_POST['condicionante'])) {
    $valorcondicionante= $_POST['condicionante'];
} else {
    if (isset($_GET['condicionante'])) {
        $valorcondicionante = $_GET['condicionante'];
    } else {
        $valorcondicionante = "=";
    };
};



//echo "valor: ".$valormultiple;

$valorcondicionante=str_replace('x|', '=', $valorcondicionante);
$valorcondicionante=str_replace('^', '!=', $valorcondicionante);
$IdFiltroExcluir= "";

//echo var_dump($_POST);
//echo "<br>";
//echo var_dump($_GET);


if ($filtro<>'' and $valor<>'') {
    if ($_GET['Excluir'] == 1) {
        $_SESSION['condiciones'] = $_SESSION['condiciones']." AND " . $filtro . $valorcondicionante. "'" . $valor . "'";
        //$_SESSION['condname']= $_SESSION['condname']." Excluir <img src='part_pics/Fill-Right.png' border=0> ".$valor;
        $_SESSION['condname']= $_SESSION['condname']." Excluir => ".$valor . ";";
        $_SESSION['condname'] = $_SESSION['condname']."@";
    }
}

if (isset($_POST['excluir'])) {
    $arraycheckbox = $_POST['excluye'];
        
    for ($contmod = 0; $contmod < count($arraycheckbox); $contmod++) {
            $_GET['Excluir']= 1;
            $IdFiltroExcluir= $arraycheckbox[$contmod];
            $_SESSION['condiciones']= $_SESSION['condiciones']." AND " . $filtro . " != '" . $IdFiltroExcluir . "'";
            //$_SESSION['condname']= $_SESSION['condname']." Excluir <img src='part_pics/Fill-Right.png' border=0> ".$IdFiltroExcluir;
            $_SESSION['condname']= $_SESSION['condname']." Excluir => ".$IdFiltroExcluir . ";";
            $_SESSION['condname'] = $_SESSION['condname']."@";
    }
}

if (isset($_SESSION['condiciones'])) {
    $wherecond .= $_SESSION['condiciones'];
} else {
    $wherecond = "";
}
//echo $wherecond;

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
        $sig_groupby = "unidadejecutora";
        break;
    case 'unidadejecutora':
        $titulo2 = _('Unidad').'<br>'. _('Ejecutora');
        $sig_groupby = "finalidad";
        break;
    case 'finalidad':
        $titulo2 = _('Finalidad');
        $sig_groupby = "funcion";
        break;
    case 'funcion':
        $titulo2 = _('Funcion');
        $sig_groupby = "subfuncion";
        break;
    case 'subfuncion':
        $titulo2 = _('SubFuncion');
        $sig_groupby = "reasignacion";
        break;
    case 'reasignacion':
        $titulo2 = _('Reasignación');
        $sig_groupby = "actividadinstitucional";
        break;
    case 'actividadinstitucional':
        $titulo2 = _('actividadinstitucional');
        $sig_groupby = "programa";
        break;
    case 'programa':
        $titulo2 = _('Programa');
        $sig_groupby = "partidaespecifica";
        break;
    case 'partidaespecifica':
        $titulo2 = _('Partida específica');
        $sig_groupby = "tipodegasto";
        break;
    case 'tipodegasto':
        $titulo2 = _('Tipo de').'<br>'. _('Gasto');
        $sig_groupby = "fuentedefinanciamiento";
        break;
    case 'fuentedefinanciamiento':
        $titulo2 = _('Fuente de Financiamiento');
        $sig_groupby = "espaciogeografico";
        break;
    case 'espaciogeografico':
        $titulo2 = _('Espacio').'<br>'. _('Geografico');
        $sig_groupby = "genero"; //"proyecto_estrategias";
        break;
    case 'proyecto_estrategias':
        $titulo2 = _('Proyecto - Estrategias');
        $sig_groupby = "rubrodeingreso";
        break;
    case 'rubrodeingreso':
        $titulo2 = _('Rubro de').'<br>'. _('Ingreso');
        $sig_groupby = "tipodegasto";
        break;
    
    case 'objetodelgasto':
        $titulo2 = _('Objeto').'<br>'. _('del Gasto');
        $sig_groupby = "obra";
        break;
    case 'obra':
        $titulo2 = _('Obra');
        $sig_groupby = "beneficiario";
        break;
    case 'beneficiario':
        $titulo2 = _('Beneficiario');
        
        $sig_groupby = "genero";
        break;
    
    case 'genero':
        $titulo2 = _('Genero');
        $sig_groupby = "grupo";
        break;
    case 'grupo':
        $titulo2 = _('Grupo');
        /*$sig_groupby = "rubro";
        break;
    case 'rubro':
        $titulo2 = _('Rubro');*/
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
        echo '<HEAD><TITLE> :: Dataware de Presupuestos de Egresos</TITLE></HEAD>';

        if ($groupbysecond) {
            $dim=0;
        } else {
            $dim=1;
        }

        if (!isset($_POST['filterlist'])) {
            $_POST['filterlist'] = "";
        }
            echo '<form method="post"  name="FDatosB">
        <input type="hidden" name="loadedfilter" value="'.$_POST['filterlist'].'">
        <input type="hidden" name="opcsavefilter" value="0">
        <input type="hidden" name="opcdelfilter" value="0">
        <input type="hidden" name="allcolumns" value="0">
        <input type="hidden" name="dimensionuno" value="'.$dim.'">';
        

    
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
            //echo "<pre>CONSULTA: $qry";
            $rstopcol = DB_query($qry, $dbDataware);
            $index=1;
            while ($rsmyrow = DB_fetch_array($rstopcol)) {
                $_SESSION['topcolumns'][$index++] = $rsmyrow['colname'];
                $_SESSION['titulocolumna'][$index] = $rsmyrow['titulocolumna'];
            }
        } else {
            $_SESSION['topcolumns'][]="";
        }
    
        echo '<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%" >';
        echo '<tr>
                <td class="texto_status2" colspan=2>
                    <img src="images/d_presupuestos.png" height="25" width="25" title="' . _('Reporte Dataware Presupuestos de Egresos') . '" alt=""> ' . $title . '<br>
                </td>
             </tr>
            <tr><td colspan=2>&nbsp;</td></tr>';
        echo "<tr><td class='texto_normal' colspan='2'>";
            echo '<table border="0" cellpadding="0" cellspacing="0" style="margin:auto;">';
                echo '<tr>';
                    echo '<td class="texto_normal">' . _('Desde : ') . '</td>';
                        echo'<td><select Name="FromDia">';
                            $sql = "SELECT * FROM cat_Days";
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
                        echo '</td>';
                        echo'<td>';
                            echo'<select Name="FromMes">';
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
                            echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
                        echo '</td>';
                        echo '<td class="texto_normal">' . _('Hasta:') . '</td>';
                        echo'<td><select Name="ToDia">';
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
                        echo '</td>';
                        echo'<td class="texto_normal" align="center">';
                            echo'<select Name="ToMes">';
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
                            echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
                        echo'</td>';
                    echo '</tr>';
                echo '</table>';
            echo '</td></tr>';
            echo '<tr><td align="center" colspan=2>&nbsp;</td></tr>';
            echo '<tr valign="top">';
                //echo '<td class="texto_normal" align="center">';
                $chktiempo=validacheck('tiempo', $groupby);
                $chkcvepresupuestal=validacheck('clavepresupuestal', $groupby);
                $chkcontab=validacheck('contabilidad', $groupby);
                $chkdocumento=validacheck('documento', $groupby);
                $tabla= '<td  align="left" cellpadding="1" colspan=1><table><tr><td nowrap>
                            <input type=radio name=groupbysecond value="" checked onclick="if(this.checked) document.FDatosB.allcolumns.value=1;">
                            <img title="Una dimension" src="images/1d.png" width=20 border=0 >
                        </td>
                        <td class="texto_normal" align="center"> <input type=radio name=groupby value="tiempo" '.$chktiempo.'>x Tiempo<br>
                            <select name="tiempo" onchange="actualizaGroupBy(this);">
                                <option value="AnioFiscal" ';
        if ($groupby=="AnioFiscal") {
            $tabla.= 'selected';
            $chktiempo="checked";
        } $tabla.='>A&ntilde;o Fiscal</option>
                                <option value="TrimestreFiscal" ';
        if ($groupby=="TrimestreFiscal") {
            $tabla.= 'selected';
            $chktiempo="checked";
        } $tabla.='>Trimestre Fiscal</option>
                                <option value="CuatrimestreFiscal" ';
        if ($groupby=="CuatrimestreFiscal") {
            $tabla.= 'selected';
            $chktiempo="checked";
        } $tabla.='>Cuatrimestre Fiscal</option>
                                <option value="PeriodoFiscal" ';
        if ($groupby=="PeriodoFiscal") {
            $tabla.= 'selected';
            $chktiempo="checked";
        } $tabla.='>Periodo Fiscal</option>
                            </select>   
                        </td>';
                        
        if ($permiso_filtrocuentapresupuestal==1) {
            $tabla= $tabla . '<td class="texto_normal" align="center"><input type=radio name=groupby value="clavepresupuestal" '.$chkcvepresupuestal.'>x Clave Pres.
                                <select name="clavepresupuestal" onchange="actualizaGroupBy(this);">
                                    <option value="ramo" '; $tabla.='>***Administrativa***</option>
                                    <option value="ramo" ';
            if ($groupby=="ramo") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Ramo</option>
                                    <option value="organosuperior" ';
            if ($groupby=="organosuperior") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Organo Superior</option>
                                    <option value="unidadejecutora" ';
            if ($groupby=="unidadejecutora") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Unidad Ejecutora</option>
                                    
                                    <option value="finalidad" '; $tabla.='>***Funcional***</option>
                                    <option value="finalidad" ';
            if ($groupby=="finalidad") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Finalidad</option>
                                    <option value="funcion" ';
            if ($groupby=="funcion") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Funcion</option>
                                    <option value="subfuncion" ';
            if ($groupby=="subfuncion") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>SubFuncion</option>
                                    <option value="reasignacion" ';
            if ($groupby=="reasignacion") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Reasignación</option>
                                    <option value="actividadinstitucional" ';
            if ($groupby=="actividadinstitucional") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>actividad institucional</option>
                                    <option value="programa" ';
            if ($groupby=="programa") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Programa</option>
                                    <option value="partidaespecifica" ';
            if ($groupby=="partidaespecifica") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Partida específica</option>
                                    <option value="tipodegasto" ';
            if ($groupby=="tipodegasto") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Tipo de Gasto</option><option value="fuentedefinanciamiento" ';
            if ($groupby=="fuentedefinanciamiento") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Fuente De Financiamiento</option>
                                        <option value="espaciogeografico" ';
            if ($groupby=="espaciogeografico") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Espacio Geografico</option> ';
            /*
                                    <option value="proyecto_estrategias" ';
            if ($groupby=="proyecto_estrategias") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Proyecto-Estra</option>
                                    <option value="estrategias" ';
            if ($groupby=="estrategias") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Estrategias</option>
                                    <option value="obra" ';
            if ($groupby=="obra") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Obra</option>
            <option value="rubrodeingreso" '; $tabla.='>***Economica***</option>
                                    <option value="rubrodeingreso" ';
            if ($groupby=="rubrodeingresos") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Rubro de Ingresos</option>
                                    
                                    <option value="objetodelgasto" ';
            if ($groupby=="objetodelgasto") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Objeto del Gasto</option>
                                    <option value="beneficiario" '; $tabla.='>***Cobertura***</option>
                                    <option value="beneficiario" ';
            if ($groupby=="beneficiario") {
                $tabla.= 'selected';
                $chkcvepresupuestal='checked';
            } $tabla.='>Beneficiario</option> */
                                    
                            $tabla.= '  </select>
                            </td>';
        }
        if ($permiso_filtrocuentacontable==1) {
            $tabla= $tabla . '<td class="texto_normal" align="center"><input type=radio name=groupby value="contabilidad" '.$chkcontab.'>x Cuenta
                                <select name="contabilidad" onchange="actualizaGroupBy(this);">
                                    <option value="genero" ';
            if ($groupby=="genero") {
                $tabla.= 'selected';
                $chkcontab='checked';
            } $tabla.='>Genero</option>
                                    <option value="grupo" ';
            if ($groupby=="grupo") {
                $tabla.= 'selected';
                $chkcontab='checked';
            } $tabla.='>Grupo</option>
                                    ';/*<option value="rubro" ';
            if ($groupby=="rubro") {
                $tabla.= 'selected';
                $chkcontab='checked';
            } $tabla.='>Rubro</option>'*/
                                    $tabla.'<option value="cuenta" ';
            if ($groupby=="cuenta") {
                $tabla.= 'selected';
                $chkcontab='checked';
            } $tabla.='>Cuenta</option>
                                    <option value="subcuenta" ';
            if ($groupby=="subcuenta") {
                $tabla.= 'selected';
                $chkcontab='checked';
            } $tabla.='>SubCuenta</option>
                                    <option value="sscuenta" ';
            if ($groupby=="sscuenta") {
                $tabla.= 'selected';
                $chkcontab='checked';
            } $tabla.='>SSCuenta</option>
                                    <option value="ssscuenta" ';
            if ($groupby=="ssscuenta") {
                $tabla.= 'selected';
                $chkcontab='checked';
            } $tabla.='>SSSCuenta</option>
                                    <option value="sssscuenta" ';
            if ($groupby=="sssscuenta") {
                $tabla.= 'selected';
                $chkcontab='checked';
            } $tabla.='>SSSSCuenta</option>
                                    <option value="sujetocontable" ';
            if ($groupby=="sujetocontable") {
                $tabla.= 'selected';
                $chkcontab='checked';
            } $tabla.='>Sujeto Contable</option>
                                </select>
                            </td>';
        }
        if ($permiso_filtrodocumento==1) {
            $tabla= $tabla . '
                            <td class="texto_normal" align="center"><input type=radio name=groupby value="documento" '.$chkdocumento.'>x Documento<br>
                                <select name="documento" onchange="actualizaGroupBy(this);">
                                    <option value="tipopoliza" ';
            if ($groupby=="tipopoliza") {
                $tabla.= 'selected';
                $chkdocumento='checked';
            } $tabla.='>Tipo</option>
                                    <option value="numeropoliza" ';
            if ($groupby=="numeropoliza") {
                $tabla.= 'selected';
                $chkdocumento='checked';
            } $tabla.='>Numero Docto</option>
                                    <option value="Fechacaptura" ';
            if ($groupby=="Fechacaptura") {
                $tabla.= 'selected';
                $chkdocumento='checked';
            } $tabla.='>FechaCaptura</option>
                                    <option value="Aniocaptura" ';
            if ($groupby=="Aniocaptura") {
                $tabla.= 'selected';
                $chktiempo='checked';
            } $tabla.='>A&ntilde;o Captura</option>
                                    <option value="Mescaptura" ';
            if ($groupby=="Mescaptura") {
                $tabla.= 'selected';
                $chktiempo="checked";
            } $tabla.='>Mes Captura</option>
                                    <option value="Semanacaptura" ';
            if ($groupby=="Semanacaptura") {
                $tabla.= 'selected';
                $chktiempo="checked";
            } $tabla.='>Semana Captura</option>
                                    <option value="Diacaptura" ';
            if ($groupby=="Diacaptura") {
                $tabla .= 'selected';
                $chktiempo = "checked";
            } $tabla.='>Dia Captura</option>
                                    <option value="NombreDiacaptura" ';
            if ($groupby=="NombreDiacaptura") {
                $tabla.= 'selected';
                $chktiempo="checked";
            } $tabla.='>Nombre Dia Captura</option>
                                    
                                </select>
                            </td>';
        }
                    $tabla= $tabla . '</tr>             
                </table>
                </td>';
                    
                $tablaH= '<table align="center" cellpadding="3" cellspacing="0" border="0">
                    <tr>
                        <td class="texto_normal"><input type=radio name=groupby value="tiempo" '.$chktiempo.'>x Tiempo</td>
                        <td class="texto_normal"><input type=radio name=groupby value="clavepresupuestal" '.$chkcvepresupuestal.'>x Clave<br>Presupuestal</td>
                        <td class="texto_normal"><input type=radio name=groupby value="contabilidad" '.$chkcontab.'>x Contabilidad</td>
                        <td class="texto_normal" colspan=2><input type=radio name=groupby value="documento" '.$chkdocumento.'>x Documento</td>
                    </tr>';
                echo $tabla;
        if ($permiso_segundadimension==1) {
            echo '<td style="text-align:center;">';
                    $chktiempo="";
                    $chkcontab = "";
                    $chkdocumento="";
                    $chkcvepresupuestal="";
                                
                    $chktiempo=validacheck('tiempo', $groupbysecond);
                    $chkcvepresupuestal = validacheck('clavepresupuestal', $groupbysecond);
                    $chkcontab=validacheck('contabilidad', $groupbysecond);
                    $chkdocumento=validacheck('documento', $groupbysecond);
                                
                    $tabla= '<table border=0 class="titulos_sub_principales" bordercolor="B2B2B2"><tr><td colspan=1>
                                                                                <td class="titulos_sub_principales">
                                                                                                     <img title="2da dimension" src="images/2d.png" width=20 border=0 >
                                                                                        </td>
                                    <td> <input type=radio name=groupbysecond value="tiempoSD" '.$chktiempo.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Tiempo
                                        <select name="tiempoSD" onchange="actualizaGroupBySecond(this);">
                                            <option value="AnioFiscal" ';
            if ($groupbysecond=="AnioFiscal") {
                $tabla.= 'selected';
                $chktiempo="checked";
            } $tabla.='>A&ntilde;o Fiscal</option>
                                            <option value="TrimestreFiscal" ';
            if ($groupbysecond=="TrimestreFiscal") {
                $tabla.= 'selected';
                $chktiempo="checked";
            } $tabla.='>Trimestre Fiscal</option>
                                            <option value="CuatrimestreFiscal" ';
            if ($groupbysecond=="CuatrimestreFiscal") {
                $tabla.= 'selected';
                $chktiempo="checked";
            } $tabla.='>Cuatrimestre Fiscal</option>
                                            <option value="PeriodoFiscal" ';
            if ($groupbysecond=="PeriodoFiscal") {
                $tabla.= 'selected';
                $chktiempo="checked";
            } $tabla.='>Periodo Fiscal</option>
                                        </select>   
                                    </td>';
                                
                                
                                
            if ($permiso_filtrocuentapresupuestal==1) {
                $tabla= $tabla . '<td> 
                                            <input type=radio name=groupbysecond value="clavepresupuestalSD" '.$chkcvepresupuestal.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Clave Pres.
                                            <select name="clavepresupuestalSD" onchange="actualizaGroupBySecond(this);">
                                            <option value="ramo" '; $tabla.='>***Administrativa***</option>
                                            <option value="ramo" ';
                if ($groupbysecond=="ramo") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Ramo</option>
                                            <option value="organosuperior" ';
                if ($groupbysecond=="organosuperior") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Organo Superior</option>
                                            <option value="unidadejecutora" ';
                if ($groupbysecond=="unidadejecutora") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Unidad Ejecutora</option>
                    <option value="finalidad" '; $tabla.='>***Funcional***</option>
                    <option value="finalidad" ';
                if ($groupbysecond=="finalidad") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Finalidad</option>
                                            <option value="funcion" ';
                if ($groupbysecond=="funcion") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Funcion</option>
                                            <option value="subfuncion" ';
                if ($groupbysecond=="subfuncion") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>SubFuncion</option>
                                            <option value="reasignacion" ';
                if ($groupbysecond=="reasignacion") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Reasignación</option>
                                            <option value="actividadinstitucional" ';
                if ($groupbysecond=="actividadinstitucional") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Actividad Institucional</option>
                                            <option value="programa" ';
                if ($groupbysecond=="programa") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Programa</option>
                                            <option value="partidaespecifica" ';
                if ($groupbysecond=="partidaespecifica") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Partida específica</option>
                                        <option value="tipodegasto" ';
                if ($groupby=="tipodegasto") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Tipo de Gasto</option>
                                            <option value="fuentedefinanciamiento" ';
                if ($groupbysecond=="fuentedefinanciamiento") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Fuente de Financiamiento</option>
                                        <option value="espaciogeografico" ';
                if ($groupbysecond=="espaciogeografico") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Espacio Geografico</option> ';
                /*
                                            <option value="proyecto_estrategias" ';
                if ($groupbysecond=="proyecto_estrategias") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Proyecto-Estra</option>
                                            <option value="obra" ';
                if ($groupbysecond=="obra") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Obra</option>
                <option value="rubrodeingreso" '; $tabla.='>***Economica***</option>
                                            <option value="rubrodeingreso" ';
                if ($groupbysecond=="rubrodeingresos") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Rubro de Ingresos</option>
                                            <option value="tipodegasto" ';
                if ($groupbysecond=="tipodegasto") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Tipo de Gasto</option>
                                            <option value="objetodelgasto" ';
                if ($groupbysecond=="objetodelgasto") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Objeto del Gasto</option>
                                            <option value="beneficiario" '; $tabla.='>***Cobertura***</option>
                                            <option value="beneficiario" ';
                if ($groupbysecond=="beneficiario") {
                    $tabla.= 'selected';
                    $chkcvepresupuestal='checked';
                } $tabla.='>Beneficiario</option> */
                                            
                                $tabla.='       </select>
                                    </td>';
            }
            if ($permiso_filtrocuentacontable==1) {
                $tabla= $tabla . '<td> <input type=radio name=groupbysecond value="contabilidadSD" '.$chkcontab.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Cuenta
                                            <select name="contabilidadSD" onchange="actualizaGroupBySecond(this);">
                                                <option value="genero" ';
                if ($groupbysecond=="genero") {
                    $tabla.= 'selected';
                    $chkcontab='checked';
                } $tabla.='>Genero</option>
                                                <option value="grupo" ';
                if ($groupbysecond=="grupo") {
                    $tabla.= 'selected';
                    $chkcontab='checked';
                } $tabla.='>Grupo</option> ';
                                            /*  <option value="rubro" ';
                if ($groupbysecond=="rubro") {
                    $tabla.= 'selected';
                    $chkcontab='checked';
                } $tabla.='>Rubro</option> */
                                                $tabla.='<option value="cuenta" ';
                if ($groupbysecond=="cuenta") {
                    $tabla.= 'selected';
                    $chkcontab='checked';
                } $tabla.='>Cuenta</option>
                                                <option value="subcuenta" ';
                if ($groupbysecond=="subcuenta") {
                    $tabla.= 'selected';
                    $chkcontab='checked';
                } $tabla.='>SubCuenta</option>
                                                <option value="sscuenta" ';
                if ($groupbysecond=="sscuenta") {
                    $tabla.= 'selected';
                    $chkcontab='checked';
                } $tabla.='>SSCuenta</option>
                                                <option value="ssscuenta" ';
                if ($groupbysecond=="ssscuenta") {
                    $tabla.= 'selected';
                    $chkcontab='checked';
                } $tabla.='>SSSCuenta</option>
                                                <option value="sssscuenta" ';
                if ($groupbysecond=="sssscuenta") {
                    $tabla.= 'selected';
                    $chkcontab='checked';
                } $tabla.='>SSSSCuenta</option>
                                                <option value="sujetocontable" ';
                if ($groupbysecond=="sujetocontable") {
                    $tabla.= 'selected';
                    $chkcontab='checked';
                } $tabla.='>Sujeto Contable</option>
                                            </select>
                                        </td>';
            }
            if ($permiso_filtrodocumento==1) {
                $tabla= $tabla . '<td><input type=radio name=groupbysecond value="documentoSD" '.$chkdocumento.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Documento
                                            <select name="documentoSD" onchange="actualizaGroupBySecond(this);">
                                                <option value="tipopoliza" ';
                if ($groupbysecond=="tipopoliza") {
                    $tabla.= 'selected';
                    $chkdocumento='checked';
                } $tabla.='>Tipo</option>
                                                <option value="numeropoliza" ';
                if ($groupbysecond=="numeropoliza") {
                    $tabla.= 'selected';
                    $chkdocumento='checked';
                } $tabla.='>Numero Docto</option>
                                                <option value="Fechacaptura" ';
                if ($groupbysecond=="Fechacaptura") {
                    $tabla.= 'selected';
                    $chkdocumento='checked';
                } $tabla.='>FechaCaptura</option>
                                                <option value="Aniocaptura" ';
                if ($groupbysecond=="Aniocaptura") {
                    $tabla.= 'selected';
                    $chktiempo='checked';
                } $tabla.='>A&ntilde;o Captura</option>
                                                <option value="Mescaptura" ';
                if ($groupbysecond=="Mescaptura") {
                    $tabla.= 'selected';
                    $chktiempo="checked";
                } $tabla.='>Mes Captura</option>
                                                <option value="Semanacaptura" ';
                if ($groupbysecond=="Semanacaptura") {
                    $tabla.= 'selected';
                    $chktiempo="checked";
                } $tabla.='>Semana Captura</option>
                                                <option value="Diacaptura" ';
                if ($groupbysecond=="Dicaptura") {
                    $tabla.= 'selected';
                    $chktiempo="checked";
                } $tabla.='>Dia Captura</option>
                                                <option value="NombreDiacaptura" ';
                if ($groupbysecond=="NombreDiacaptura") {
                    $tabla.= 'selected';
                    $chktiempo="checked";
                } $tabla.='>Nombre Dia Captura</option>
                                            </select>
                                        </td>';
            }
                                
                        $tabla= $tabla . '</tr>
                                    </table>
                                        </td>
                                    </tr>';
                        
                                
                        $tablaH= '<table align="center" border=0 cellpadding="3">
                                        <tr>
                                            <td class="texto_status" colspan="4"><input type=radio name=groupbysecond value="" checked onclick="if(this.checked) document.FDatosB.allcolumns.value=1;">Una Dimension</td>
                                        </tr>
                                        <tr>
                                            <td class="texto_status" colspan="4">**** Filtros Segunda Dimension ****</td>
                                        </tr>
                                        <tr>
                                            <td class="texto_normal"><input type=radio name=groupbysecond value="tiempoSD" '.$chktiempo.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Tiempo</td>
                                            <td class="texto_normal"><input type=radio name=groupbysecond value="clavepresupuestalSD" '.$chkcvepresupuestal.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Clave Presupuestal</td>
                                            <td class="texto_normal"><input type=radio name=groupbysecond value="contabilidadSD" '.$chkcontab.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Contablidad</td>
                                            <td class="texto_normal"><input type=radio name=groupbysecond value="documentoSD" '.$chkdocumento.' onclick="if(this.checked) document.FDatosB.allcolumns.value=0;">x Documento</td>
                                        </tr>';
                        echo $tabla;
                                
                                    
            echo'</td>';
        }//TERMINA PERMISO  104
        echo '</tr>';
        echo '<tr><td colspan=2>&nbsp;</td></tr>';
  
        
        if (!isset($_POST['excel'])) {
            echo '<tr><td colspan=2>&nbsp;</td></tr>';
            
            echo '<tr>';//
            
            echo'<td colspan=2 class="texto_lista" style="text-align:center;" nowrap>';
            if ($groupbysecond == "") {
                echo'Tipo de Grafica:
                <select name="tipografica">';
                    
                // Establecer los tipos de graficas que el usuario puede seleccionar
                    
                $sql= "Select id, chartname, flaggeneral
                        From DW_tiposgraficas
                        Where active= 1
                        Order by chartname";
                    
                $datos= DB_query($sql, $dbDataware);
                    
                while ($registro= DB_fetch_array($datos)) {
                    if ($_POST["tipografica"] == $registro["id"]) {
                        echo '<option value="'.$registro["id"].'" selected>'.$registro["chartname"];
                        $muestragrafica= $registro["flaggeneral"];
                    } else {
                        echo '<option value="'.$registro["id"].'">'.$registro["chartname"];
                    }
                }
                echo "</select>";
            }
            
        
            $sql = "Select * from DWH_userfilters
            WHERE userid = '".$_SESSION['UserID']."'
                and proyecto=".$funcion;
            $rs = DB_query($sql, $dbDataware);
            
            if (DB_num_rows($rs) > 0) {
                echo '&nbsp;&nbsp;&nbsp;Filtros Almacenados: &nbsp; <select name="filterlist">
                    <option value="">Sin filtro...</option>';
        
                while ($rsrows = DB_fetch_array($rs)) {
                    $filtername = $rsrows['filtername'];
                    $sel="";
                    if ($filtername==$_REQUEST['filterlist']) {
                        $sel="selected";
                    }
        
                    echo '<option value="'.$filtername.'" '.$sel.'>'.$filtername.'</option>';
                }
        
                echo '</select>';
                if (Havepermission($_SESSION['UserID'], 105, $db)==1) {//PERMISO PARA GUARDAR FILTRO, SE USA PARA TAMBIEN PARA ELIMIAR FILTRO
                    echo '&nbsp;&nbsp;<a href="javascript:DeleteFilter();"><img src="part_pics/Delete.png" border=0 title="Eliminar filtro guardado"></a>';
                }
                if (Havepermission($_SESSION['UserID'], 106, $db)==1) {
                    echo "&nbsp;&nbsp;<a href='javascript:SendFilter(" . $funcion . ");'><img src='part_pics/Mail-Forward.png' border='0' title='Enviar filtro a otros usuarios'></a>";
                }
                if (Havepermission($_SESSION['UserID'], 105, $db)==1) {
                    echo '<input type="text" id="namenewfilter" name="namenewfilter" size="40">
                        <button style="border:0; background-color:transparent;" value="Guardar Filtro" name="btnfilter" onclick="saveNewFilter();">
                            <img src="images/guardar.png" width=60 ALT="Guardar"></button>';
                }
            } else {
                //echo '<tr>
                //  <td style="text-align:center;" colspan="2">';
                //echo '<table border="0" align="center">
                //          <tr>
                    //      <td>
                                echo '&nbsp;&nbsp;&nbsp;<input type="text" id="namenewfilter" name="namenewfilter" size="60">';
                        //  </td>
                            //<td>&nbsp;
                                echo '<button style="border:0; background-color:transparent;" value="Guardar Filtro" name="btnfilter" onclick="saveNewFilter();"><img src="images/guardar.png" width=60 ALT="Guardar"></button>';
                            //</td>
                        //</tr>
                //</table>';
            }
            //poner link para eliminar filtros almacenados         boton para guardar filtro <input type="button" value="Guardar Filtro" name="btnfilter" onclick="saveNewFilter();">
        
            echo '</td></tr>';
        }
        
        echo '<tr><td colspan=2>&nbsp;</td></tr>';
        
        
        echo '<tr>';
                echo '<td style="text-align:center;" colspan="2">';
                    echo '<button style="border:0; background-color:transparent;" name="procesar" value="GENERAR">
                                    <img src="images/buscar.png" height="40" ALT="Procesar">
                            </button>&nbsp;
                            <button style="border:0; background-color:transparent;" value="REINICIAR" name="reiniciar">
                                    <img src="images/reiniciar2.png" height="40" ALT="Reinicia">
                            </button>&nbsp;
                            <button style="cursor:pointer; border:0; background-color:transparent;" name="excel" value="EXCEL">
                                    <img src="images/exportar1.png" height="40" ALT="Exportar a Excel" title="Exportar Tablero a Excel">
                            </button>';
                    //echo'<input type="submit" value="GENERAR" name="procesar">&nbsp;<input type="submit" value="REINICIAR" name="reiniciar">';
                    //echo '<button style="cursor:pointer; border:0; background-color:transparent;" name="excel" value="EXCEL"><img src="images/exportar.png" width="100" ALT="Exportar a Excel" title="Exportar Tablero a Excel"></button>';
                    //echo'&nbsp;<input type="submit"  value="EXCEL" name="excel">'; //onclick="ExportaExcel();"
                echo '</td>';
          echo '</tr>';
          //echo "<br>1: " . $condicion;
        $condicion = str_replace("like '%", "like $", $condicion);
        //echo "<br>2: " . $condicion;
        $condicion = str_replace("'", "$", $condicion);
        echo '</table>';
    }//noexcel


    $esexcel=false;
    if (isset($_POST['excel'])) {
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
    for ($j=1; $j<=$totCol; $j++) {
        if ($_SESSION['chkcol'][$j]) {
            $colVisibles++;
        }
    }


    $arrparametrosfijos = array(0 => 'genero',
        1 => 'grupo'
    );


    echo "<br>";//
    echo '<table width=95% border=1  bordercolor=lightgray align="center" cellspacing=0 cellpadding=3>';
    $colsp = ((((count($_SESSION['topcolumns'])+1)*$colVisibles)+3)>$totCol)?(((count($_SESSION['topcolumns'])+1)*$colVisibles)+3):$totCol;
    

    if (!isset($_GET['Excluir'])) {
        $_GET['Excluir'] = 0;
    }

    if ($_GET['Excluir'] == 1 or isset($_SESSION['condname'])) {
        $confname = explode("@", $_SESSION['condname']);
        $conarray =array();
        $confnamedesplegar = "";
        $y=0;
        foreach ($confname as $confvalor) {
            if (in_array($confvalor, $conarray) == false) {
                $y = $y +1;
                $confnamedesplegar = $confnamedesplegar.$confvalor;
                
                $conarray[$y] = $confvalor;
            }
        }
        echo '<td colspan='.$colsp.' ><span title="' . $confnamedesplegar . '"><u>Excluir</u></span>';
    } else {
        echo '<td colspan='.$colsp.' >';
    }
    //echo '<td colspan='.$colsp.' >';
    //
    ?>
    <table width=95% border=0 cellspacing=0 cellpadding=3>
        <tr valign="top">
            <td class="td_style">
                <table border="0" bordercolor=lightgray cellspacing="0" cellpadding="0">

    <?php


    if (strlen($wherecond)>0) {
        //$condicion.=" ".str_replace("'","$",$wherecond);
    }
    if (strlen($condicion)>0 and !$esexcel) {
        if (!isset($_SESSION['valorfijo'])) {
            $_SESSION['valorfijo']="";
        }
            //echo "<pre>$condicion";
        $x=array();
        $x=explode('AND', $condicion);
        $savefilter=false;
                
        for ($z=1; $z<count($x); $z++) {
            $fijo = $x[$z];
            if ($fijo != $fijoant) {
                $savefilter=true;
                $sesionfijo = str_replace("$", "", $_SESSION['valorfijo']);
                $sesionfijo = str_replace("AND", "", $sesionfijo);
                $fijover = str_replace("$", "", $fijo);
                    
                if (trim($sesionfijo) == trim($fijover)) {
                    $newcondicion = str_replace($_SESSION['valorfijo'], "", $condicion);
                    $ligados='dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.
                        '&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
                        .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' . $groupby.'&keepvisible=1&groupbysecond='.$groupbysecond.'&novalorfijo=1&Ordenar=asc&condicion='.$newcondicion;
                    $src="images/cancel.gif";
                                            echo '<tr style="background-color:#9da791;"><td><a href="'.$ligados.'">';
                                                echo '<img title="quitar" src="'.$src.'" border=0></a>';
                } else {
                    $fijo2 = "AND".$fijo;
                    $newcondicion = str_replace($fijo2, "", $condicion);
                    $ligados='dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.
                        '&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
                        .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' . $groupby.'&keepvisible=1&groupbysecond='.$groupbysecond.'&Ordenar=asc&condicion='.$newcondicion;
                    $src="images/cancel.gif";
                    echo '<tr><td class="td_style2"><a href="'.$ligados.'">';
                        echo '<img title="quitar" src="'.$src.'" border=0></a>&nbsp;&nbsp;';
                    $ligados='dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.
                        '&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
                        .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' . $groupby.'&keepvisible=1&groupbysecond='.$groupbysecond.'&valorfijo=$AND '.$fijover. '$&Ordenar=asc' ;
                    $src="part_pics/Fill-Right.png";
                    echo '<a href="'.$ligados.'">';
                        echo '<img title="fijar" src="'.$src.'" border=0></a>&nbsp;';
                }
                                        //echo "<pre>valorfijo=\$AND $fijover \$";
                                    //  echo "<br>-->" . $fijover . "<--<br>";//
                echo $fijover." - ";
                $fijover = TraeTitulo($fijover, $dbDataware);
                $textover = "";
                echo $textover.' &nbsp '.$fijover;
                $_SESSION["filtrodw"].= "&nbsp;[&nbsp;<b>".$fijover. "</b>&nbsp;]&nbsp;<b>=></b>";
            }
            echo '</td>';
            echo '</tr>';
            $fijoant=$fijo;
        }
        ?>
                </table>
            </td>
        </tr>
    </table>
    <?php
    }
                            echo '</td></tr>';
                            
                            //echo var_dump($_SESSION["filtrodw"]);
                            
                            echo '<tr>';
                                echo '<td style="background-color:#3a9143;" align="center" nowrap>
                                        <font color="white"><b>#</b></font>&nbsp;&nbsp;
                                        <input type="checkbox" name="chkExcluirTodos" onclick="javascript:selAll(this);">';
                                    echo "<br><a href='javascript:invsel(document.FDatosB.chkExcluirTodos);'>I</a>";
//                                  echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
//                                          .$ToMes. '&ToYear=' . $ToYear .'&OrdenarPor=' .$groupby. '&Excluir=1&Ordenar=asc&filtro='.$groupby.'&condicionante=^'.
//                                          '&valor=Multiple&condicion='. $condicion .'">
//                                          <br><br>&nbsp;&nbsp;&nbsp;&nbsp;';
                                    echo "<br><br>&nbsp;&nbsp;&nbsp;&nbsp;<button style='cursor:pointer; border:0; background-color:transparent;' name='excluir'><img src='images/cancel.gif' ALT='Agregar Nuevo' title='Excluir Seleccionados' tabindex=0></button></a>";
                                                        
                                echo '</td>';
                                
                                            $colspanini=2;
                                
                                        echo '<td  class="titulos_principales" nowrap >';
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr>';
                                 
                                                        if ($muestragrafica == 1){
                                                            echo '<td style="background-color:#3a9143;" align="center">';
                                                            if($groupbysecond == ""){
                                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Todos&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'">
                                                                    <img src="images/grafica3.png" border=0 title="Grafica De Presupuestos" width="20" height="25"></a>&nbsp;';
                                                            }
                                                            echo '</td>';
                                                        }
                                
                                                                echo '<td style="background-color:#3a9143;" align="center"><font color="white">';   
                                                                        if ($esexcel)
                                                                            echo '<b><u>' . $titulo2;
                                                                        else{
                                                                            echo ' </a>';
                                                                            //  echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' .$groupby. '&Ordenar='.$sigOrdenar . '&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                                                            echo '<b><u><font color="white">' . $titulo2 ;
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
                                    
                                echo '</td></tr></table>';
                                echo '</td>';
                                
                                if(!(array_search($groupby,$arrcolscondescripcion)===FALSE)){
                                    
                                    echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo _('Descripcion');
                                        else{
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3><tr>';
                                                    echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(1,$db) . "'></td>";
                                                echo "<tr>";
                                                    echo '<td class="titulos_principales">';
                                                        echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=' . "txt" . $groupby . '&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                                        echo '<b><u><font color="white">'._('Descripcion');
                                                        echo '</a>';
                                                    echo '</td>';
                                                echo '</tr>';
                                            echo '</table>';
                                        }
                                    echo '</td>';
                                    
                                    
                                    
                                    
                                    
                                }
                                
                                if ($groupbysecond){
                                    foreach($_SESSION['titulocolumna'] as $namecol){
                                        if (($groupbysecond=='Mescaptura') or ($groupbysecond == 'PeriodoFiscal')){
                                            $nombrecol = glsnombremeslargo($namecol);
                                        }elseif ($groupbysecond=='Fechacaptura'){
                                            $nombrecol = substr($namecol, 0,10);
                                        }else{
                                            $nombrecol = $namecol; 
                                        }
                                        
                                        echo '<td class="titulos_principales" colspan="'.$colVisibles.'" nowrap>'.$nombrecol.'</td>';
    
                                    }
                                    //totales por filas
                                    echo '<td class="pie_derecha" colspan="'.$colVisibles.'" >TOTALES</td>';
            
                                }else{
                                    if ($_SESSION['chkcol'][1]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel) 
                                            echo '<b>'._('Presupuesto') . "<br>" . _('Original');
                                        else{
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(1,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                                echo '<input type="checkbox" name="chkcol1" checked >';
                                            echo '</td>';
                                            echo '<td class="titulos_principales">';
                                                echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=presupuestoaprobado&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                                echo '<b><u><font color="white">'._('Presupuesto') . "<br>" . _('Original');
                                                echo '</a>';                      
                                            echo '</td>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Aprobado&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                            
                                            $colsconsulta.= ", sum(montopresupuestoaprobado*-1) as presupuestoaprobado";
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as presupuestoaprobado"; }
                                    
                                    if ($_SESSION['chkcol'][20]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('Ampliaciones') . "<br>" . _('Reducciones');
                                        else{
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(2,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                            echo '<input type="checkbox" name="chkcol20" checked >';
                                            echo '</td>';
                                            echo '<td class="titulos_principales">';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=presupuestomodificado&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">'._('Ampliaciones') . "<br>" . _('Reducciones');
                                            echo '</a>';
                                            echo '</td>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Modificado&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                    
                                            $colsconsulta.= ", sum(montopresupuestoampliacion) as presupuestoampliacion";
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as presupuestomodificado"; }
                                    
                                    
                                    if ($_SESSION['chkcol'][2]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel) 
                                            echo '<b>'._('Presupuesto') . "<br>" . _('Modificado');
                                        else{
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(2,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                            echo '<input type="checkbox" name="chkcol2" checked >';
                                            echo '</td>';
                                            echo '<td class="titulos_principales">';
                                                echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=presupuestomodificado&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                                echo '<b><u><font color="white">'._('Presupuesto') . "<br>" . _('Modificado');
                                                echo '</a>';
                                            echo '</td>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Modificado&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                            
                                            $colsconsulta.= ", sum(montopresupuestomodificado*-1) as presupuestomodificado";
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as presupuestomodificado"; }
                                    /*
                                    if ($_SESSION['chkcol'][3]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('%PM');
                                        else{
                                            echo "<img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(3,$db) . "'><br>";
                                            echo '<input type="checkbox" name="chkcol3" checked >';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=porcpresupuestomodificado&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">'._('%PM');
                                            echo '</a>';
                                            
                                        }
                                        echo '</td>';
                                    } */
                                    
                                    if ($_SESSION['chkcol'][4]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel) 
                                            echo '<b>'._('Historico') . "<br>" . _('Comprometido');
                                        else{
                                            
                                            echo '</a>';
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                        <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(21,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                                echo '<input type="checkbox" name="chkcol4" checked >';
                                            echo '</td>';
                                            echo '<td class="titulos_principales">';
                                                echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=historicocomprometido&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                                echo '<b><u><font color="white">'._('Historico') . "<br>" . _('Comprometido');
                                                echo '</a>';
                                            echo '</td>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Comprometido&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                            
                                            $colsconsulta.= ", (sum(montocomprometido)+sum(montodevengado)+sum(montoejercido)+sum(montopagado)) as historicocomprometido";
                                            
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as historicocomprometido"; }
                                    /*
                                    if ($_SESSION['chkcol'][5]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('%HC');
                                        else{
                                            echo "<img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(22,$db) . "'><br>";
                                            echo '<input type="checkbox" name="chkcol5" checked >';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=porchistoricocomprometido&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">'._('%HC');
                                            echo '</a>';
                                            
                                        }
                                        echo '</td>';
                                    } */
                                    
                                    if ($_SESSION['chkcol'][6]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('Historico') . "<br>" . _('Devengado');
                                        else{
                                            echo '<table border=0 width=100% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(23,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                                echo '<input type="checkbox" name="chkcol6" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                                echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=historicodevengado&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                                echo '<b><u><font color="white">'._('Historico') . "<br>" . _('Devengado');
                                                echo '</a>';
                                            echo '</td>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Devengado&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                            
                                            $colsconsulta.= ", (sum(montodevengado)+sum(montoejercido)+sum(montopagado)) as historicodevengado";
                                        
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as historicodevengado"; }
                                    
                                    /*
                                    if ($_SESSION['chkcol'][7]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('%HD');
                                        else{
                                            echo '<table border=0 width=100% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(24,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                            echo '<input type="checkbox" name="chkcol7" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=porchistoricodevengado&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">'._('%HD');
                                            echo '</a>';
                                            echo '</td>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            //***echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Comprometer&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            echo '</td></tr></table>';
                                    
                                        }
                                        echo '</td>';
                                    }
                                    */
                                    
                                    if ($_SESSION['chkcol'][8]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('Historico') . "<br>" . _('Ejercido');
                                        else{
                                            echo '<table border=0 width=100% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(25,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                            echo '<input type="checkbox" name="chkcol8" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=historicoejercido&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">'._('Historico') . "<br>" . _('Ejercido');
                                            echo '</a>';
                                            echo '</td>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Ejercido&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                            
                                            $colsconsulta.= ", (sum(montoejercido)+sum(montopagado)) as historicoejercido";
                                    
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as historicoejercido"; }
                                    
                                    /*
                                    if ($_SESSION['chkcol'][9]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('%HE');
                                        else{
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(26,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                                echo '<input type="checkbox" name="chkcol9" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                                echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=porchistoricoejercido&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                                echo '<b><u><font color="white">'._('%HE');
                                                echo '</a>';
                                            echo '</td>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            //***echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=NoDevengado&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            echo '</td></tr></table>';
                                        }
                                        echo '</td>';
                                    } 
                                    */
                                    
                                    if ($_SESSION['chkcol'][10]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'. _('Historico') . "<br>" . _('Pagado');
                                        else{
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                        <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(13,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                                echo '<input type="checkbox" name="chkcol10" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                                echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=pagado&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                                echo '<b><u><font color="white">'.  _('Historico') . "<br>" . _('Pagado');
                                                echo '</a>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Pagado&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                            
                                            $colsconsulta.= ", sum(montopagado) as pagado";
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as pagado"; }
                                    
                                    
                                    
                                    /*
                                    if ($_SESSION['chkcol'][11]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('%PG');
                                        else{
                                                echo "<img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(14,$db) . "'><br>";
                                                echo '<input type="checkbox" name="chkcol11" checked >';
                                                echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=porcpagado&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                                echo '<b><u><font color="white">'._('%PG');
                                                echo '</a>';
                                            }
                                        echo '</td>';
                                    }*/
                                    
                                    if ($_SESSION['chkcol'][12]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('Presupuesto') . "<br>" . _('Comprometido');
                                        else{
                                            echo '</a>';
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                            <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(4,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                            echo '<input type="checkbox" name="chkcol12" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=comprometido&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">'._('Presupuesto') . "<br>" . _('Comprometido');
                                            echo '</a>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=GastoComprometido&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                                
                                            $colsconsulta.= ", (sum(montocomprometido)) as comprometido";
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as comprometido"; }
                                    
                                    if ($_SESSION['chkcol'][13]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>' . _('Comprometido por') . "<br>" . _('Etiquetar Gasto') . "<br>" . _('de Inversion');
                                        else{
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(39,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                            echo '<input type="checkbox" name="chkcol13" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=gastodeinversion&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">' . _('Comprometido por') . "<br>" . _('Etiquetar Gasto') . "<br>" . _('de Inversion');
                                            echo '</a>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=DisponibleInversion&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                    
                                            $colsconsulta.= ", (sum(case when cuentaconcentradora = 1 then montocomprometido else 0 end)
                                                                + sum(case when cuentaconcentradora = 1 then montodevengado else 0 end)
                                                                + sum(case when cuentaconcentradora = 1 then montoejercido else 0 end)
                                                                + sum(case when cuentaconcentradora = 1 then montopagado else 0 end)) as gastodeinversion";
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as gastodeinversion"; }
                                    
                                    
                                    
                                    
                                    
                                    $colsconsulta.= ", null as gastodeoperacion";
                                    
                                    if ($_SESSION['chkcol'][14]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('Disponible') . "<br>" . _('Para Comprometer');
                                        else{
                                            echo '</a>';
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(6,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                                echo '<input type="checkbox" name="chkcol14" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                                echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=disponibleparacomprometer&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                                echo '<b><u><font color="white">'._('Disponible') . "<br>" . _('Para Comprometer');
                                                echo '</a>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Comprometer&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                            
                                            $colsconsulta.= ", sum(montopresupuestomodificado*-1) - (sum(montocomprometido)+sum(montodevengado)+sum(montoejercido)+sum(montopagado)) as disponibleparacomprometer";
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as disponibleparacomprometer"; }

                                    /*
                                    
                                    if ($_SESSION['chkcol'][15]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('%DC');
                                        else{
                                            echo '</a>';
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                        <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(5,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                                echo '<input type="checkbox" name="chkcol15" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                                echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=porcdisponibleparacomprometer&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                                echo '<b><u><font color="white">'._('%DC');
                                                echo '</a>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                                //***echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=DeudaEjercicio&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a><br>';
                                            echo '</td></tr></table>';
                                                
                                        }
                                        echo '</td>';
                                    } */
                                    
                                    if ($_SESSION['chkcol'][16]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('Presupuesto') . "<br>" . _('Devengado');
                                        else{
                                            echo '</a>';
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                        <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(7,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                            echo '<input type="checkbox" name="chkcol16" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=devengado&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">'._('Presupuesto') . "<br>" . _('Devengado');
                                            echo '</a>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=GastoDevengado&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                            
                                            $colsconsulta.= ", sum(montodevengado) as devengado";
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as devengado"; } 
                                    
                                    if ($_SESSION['chkcol'][17]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('Presupuesto') . "<br>" . _('Ejercido');
                                        else{
                                            echo '</a>';
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(12,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                            echo '<input type="checkbox" name="chkcol17" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=ejercido&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">'._('Presupuesto') . "<br>" . _('Ejercido');
                                            echo '</a>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=GastoEjercido&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                            
                                            $colsconsulta.= ", sum(montoejercido) as ejercido";
                                                
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as ejercido"; }
                                    
                                    if ($_SESSION['chkcol'][18]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'._('Deuda del') . "<br>" . _('Ejercicio');
                                        else{
                                            echo '</a>';
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(15,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                            echo '<input type="checkbox" name="chkcol18" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=deudaejercicio&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">'._('Deuda del') . "<br>" . _('Ejercicio');
                                            echo '</a>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=DeudaEjercicio&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                            
                                            $colsconsulta.= ", sum(montodevengado) +  sum(montoejercido) as deudaejercicio";
                                    
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as deudaejercicio"; }
                                    
                                    if ($_SESSION['chkcol'][19]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'. _('Pagado');
                                        else{
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(13,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                            echo '<input type="checkbox" name="chkcol19" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=pagado&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">'.  _('Pagado');
                                            echo '</a>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Pagado&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                                
                                            $colsconsulta.= ", sum(montopagado) as pagado";
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as pagado"; }
                                    
                                    if ($_SESSION['chkcol'][21]){
                                        echo '<td style="background-color:#3a9143;" align="center"><font color="white">';
                                        if ($namecol || $esexcel)
                                            echo '<b>'. _('SubEjercicio');
                                        else{
                                            echo '<table border=0 width=80% valign="center" cellspacing=0 cellpadding=3>
                                                    <tr style="background-color:#3a9143;">';
                                            echo "<td colspan='2' style='text-align:center;'><img src='/erpgubernamental/images/ayuda.png' width='16' height='16' title='" . gettooltip(13,$db) . "'></td>";
                                            echo '<tr style="background-color:#3a9143;">';
                                            echo '<td>';
                                            echo '<input type="checkbox" name="chkcol21" checked >';
                                            echo '</td>';
                                            echo '<td>';
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes=' .$ToMes. '&ToYear=' . $ToYear . '&OrdenarPor=pagado&Ordenar='.$sigOrdenar . '&keepvisible=1&condicion='.$condicion.'&filterlist='.$_REQUEST['filterlist'].'" >';
                                            echo '<b><u><font color="white">'.  _('SubEjercicio');
                                            echo '</a>';
                                            echo '</tr><tr style="background-color:#3a9143;">';
                                            echo ' <td  colspan=2 style="text-align:center;">';
                                            if($groupbysecond == ""){
                                                echo '<a target="_blank" href="PDFGraficaDWcontabilidad_1D.php?dato=Pagado&fechaini='.$fechaini.'&fechafin='.$fechafin.'&tipofiltro='.$titulografica.'&tipografica='.$_POST["tipografica"].'&groupby='.$groupby.'"><img src="images/grafica2.png" border=0 title="Grafica De Presupuestos" width="20" height="10"></a>';
                                            }
                                            echo '</td></tr></table>';
                                    
                                            $colsconsulta.= ", sum(montosubejercicio) as subejercicio";
                                        }
                                        echo '</td>';
                                    } else { $colsconsulta.= ", null as pagado"; }
                                    
                                    
                                }
                                
                            echo '</tr>';
            ////
                            if ($groupbysecond){
                                echo '<tr>';
                                if(!(array_search($groupby,$arrcolscondescripcion)===FALSE)){
                                        echo '<td class="texto_normal" colspan="' . ($colspanini+1) . '">&nbsp;</td>';
                                    }else{
                                        echo '<td class="texto_normal" colspan="'.$colspanini.'">&nbsp;</td>';
                                    }               
                                    
                                    for($ititle=1;$ititle <= count($_SESSION['topcolumns'])+1;$ititle++){ //count + 1 para poner totales a la derecha
                                        if ($_SESSION['chkcol'][1]){
                                            echo '<td style="text-align:center;" width="10%" class="titulos_sub_principales">';
                                            echo '<b>'._('Presupuesto') . "<br>" . _('Original');                       
                                            echo '</td>';
                                        }
                                        if ($_SESSION['chkcol'][20]){
                                            echo '<td style="text-align:center;" width="10%" class="titulos_sub_principales">';
                                            echo '<b>'._('Ampliaciones') . "<br>" . _('Reducciones');
                                            echo '</td>';
                                        }
                                        if ($_SESSION['chkcol'][2]){                    
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                                 echo '<b>'._('Presupuesto') . "<br>" . _('Modificado');
                                            echo '</td>';
                                        }
                                        /*
                                        if ($_SESSION['chkcol'][3]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('%PM');
                                            echo '</td>';
                                        }*/
                                        if ($_SESSION['chkcol'][4]){                
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                                 echo '<b>'._('Historico') . "<br>" . _('Comprometido');
                                            echo '</td>';
                                        }
                                        /*if ($_SESSION['chkcol'][5]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('%HC');
                                            echo '</td>';
                                        }*/
                                        if ($_SESSION['chkcol'][6]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('Historio') . "<br>" . _('Devengado');
                                            echo '</td>';
                                        }
                                        /*if ($_SESSION['chkcol'][7]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('%HD');
                                            echo '</td>';
                                        }*/
                                        if ($_SESSION['chkcol'][8]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('Historico') . "<br>" . _('Ejercido');
                                            echo '</td>';
                                        }
                                        /*if ($_SESSION['chkcol'][9]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('%HE');
                                            echo '</td>';
                                        }*/
                                        if ($_SESSION['chkcol'][10]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'. _('Historico') . "<br>" . _('Pagado');
                                            echo '</td>';
                                        }
                                        
                                        /*if ($_SESSION['chkcol'][11]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('%PG');
                                            echo '</td>';
                                        }*/
                                        
                                        
                                        
                                        if ($_SESSION['chkcol'][12]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'. _('Presupuesto') . "<br>" . _('Comprometido');
                                            echo '</td>';
                                        }
                                        
                                        if ($_SESSION['chkcol'][13]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>' . _('Comprometido por') . "<br>" . _('Etiquetar Gasto') . "<br>" . _('de Inversion');
                                            echo '</td>';
                                        }
                                        /*
                                        if ($_SESSION['chkcol'][14]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'. _('Disponible') . "<br>" . _('Gasto de ') . "<br>" . _('Operacion');
                                            echo '</td>';
                                            }*/
                                        if ($_SESSION['chkcol'][14]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('Disponible') . "<br>" . _('Para Comprometer');
                                            echo '</td>';
                                        }
                                        /*if ($_SESSION['chkcol'][15]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('%DC');
                                            echo '</td>';
                                        }*/
                                        if ($_SESSION['chkcol'][16]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('Presupuesto') . "<br>" . _('Devengado');
                                            echo '</td>';
                                        }
                                        if ($_SESSION['chkcol'][17]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('Presupuesto') . "<br>" . _('Ejercido');
                                            echo '</td>';
                                        }
                                        if ($_SESSION['chkcol'][18]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('Deuda del ') . "<br>" . _('Ejercicio');
                                            echo '</td>';
                                        }
                                        if ($_SESSION['chkcol'][19]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('Pagado');
                                            echo '</td>';
                                        }
                                        if ($_SESSION['chkcol'][21]){
                                            echo '<td style="text-align:center;" width="15%" class="titulos_sub_principales">';
                                            echo '<b>'._('Subejercicio');
                                            echo '</td>';
                                        }
                                    }
                                echo '</tr>';
                            }

                            
                            $condicion = str_replace("like $","like '%",$condicion);
                            $condicion = str_replace("$","'",$condicion);
                            if ($groupbysecond){
                                $groupbysecondvalue = $groupbysecond;
                                $groupbysecond=",".$groupbysecond;
                                $OrdenarPor = "header";
                            }
                            
                            $sqlgrafica = "SELECT " . $groupby." as header";
                            $sqlgrafica .=  $groupbysecond . $colsconsulta;
                            


                            
                            $sql = "SELECT " . $groupby." as header";
                            $sql .= $groupbysecond . "
                                , sum(montopresupuestoaprobado*-1) as 'presupuestoaprobado'
                                , sum(montopresupuestoampliacion*-1) as 'presupuestoampliacion'
                                , sum(montopresupuestomodificado*-1) as 'presupuestomodificado'
                                , ((sum(montopresupuestomodificado) * 100) / sum(montopresupuestoaprobado)) - 100 as 'porcpresupuestomodificado'
                                , (sum(montocomprometido)+sum(montodevengado)+sum(montoejercido)+sum(montopagado)) as 'historicocomprometido'
                                , ((sum(montocomprometido)+sum(montodevengado)+sum(montoejercido)+sum(montopagado))*100)/sum(montopresupuestomodificado*-1) as 'porchistoricocomprometido'  
                                , (sum(montodevengado)+sum(montoejercido)+sum(montopagado)) as 'historicodevengado'
                                , ((sum(montodevengado)+sum(montoejercido)+sum(montopagado))*100)/(sum(montocomprometido)+sum(montodevengado)+sum(montoejercido)+sum(montopagado)) as 'porchistoricodevengado'  
                                , (sum(montoejercido)+sum(montopagado)) as 'historicoejercido'
                                , ((sum(montoejercido)+sum(montopagado))*100)/(sum(montodevengado)+sum(montoejercido)+sum(montopagado)) as 'porchistoricoejercido'
                                , sum(montopagado) as 'pagado'
                                , sum(montosubejercicio) as 'subejercicio'              
                                , (sum(montopagado) * 100) / (sum(montoejercido)+sum(montopagado)) as 'porcpagado'
                                , (sum(montocomprometido)) as 'comprometido'
                                , sum(montopresupuestomodificado*-1) - (sum(montocomprometido)+sum(montodevengado)+sum(montoejercido)+sum(montopagado)) as 'disponibleparacomprometer'
                                ,((sum(montopresupuestomodificado*-1) - (sum(montocomprometido)+sum(montodevengado)+sum(montoejercido)+sum(montopagado)))*100)/sum(montopresupuestomodificado*-1) as porcdisponibleparacomprometer
                                , sum(montodevengado) as 'devengado'
                                , sum(montoejercido) as 'ejercido'
                                , sum(montodevengado) +  sum(montoejercido) as 'deudaejercicio'
                                , (sum(case when IFNULL(cuentaconcentradora,0) = 1 then montocomprometido else 0 end)
                                    + sum(case when IFNULL(cuentaconcentradora,0) = 1 then montodevengado else 0 end)
                                    + sum(case when IFNULL(cuentaconcentradora,0) = 1 then montoejercido else 0 end)
                                    + sum(case when IFNULL(cuentaconcentradora,0) = 1 then montopagado else 0 end)) as 'gastodeinversion'
                                , (sum(case when IFNULL(cuentaconcentradora,0) = 0 then montocomprometido else 0 end)
                                    + sum(case when IFNULL(cuentaconcentradora,0) = 0 then montodevengado else 0 end)
                                    + sum(case when IFNULL(cuentaconcentradora,0) = 0 then montoejercido else 0 end)
                                    + sum(case when IFNULL(cuentaconcentradora,0) = 0 then montopagado else 0 end)) as 'gastodeoperacion'";
                            if ($groupby=='numerodocumento'){
                                $sql .= ", numerodocumento";
                                $sqlgrafica.= ", numerodocumento";
                            }
                            
                            if(!(array_search($groupby,$arrcolscondescripcion)===FALSE)){
                                $sql .= ", " . $groupby . ", txt" . $groupby . " as txtheader";
                                $sqlgrafica .= ", " . $groupby . ", txt" . $groupby . " as txtheader";
                            } else {
                                if ($groupby == 'AnioFiscal' || $groupby == 'TrimestreFiscal' || $groupby == 'PeriodoFiscal'){
                                    $case= ", Case When ".$groupby." = '1' Then 'Enero'
                                                    When ".$groupby." = '2' Then 'Febrero'
                                                    When ".$groupby." = '3' Then 'Marzo'
                                                    When ".$groupby." = '4' Then 'Abril'
                                                    When ".$groupby." = '5' Then 'Mayo'
                                                    When ".$groupby." = '6' Then 'Junio'
                                                    When ".$groupby." = '7' Then 'Julio'
                                                    When ".$groupby." = '8' Then 'Agosto'
                                                    When ".$groupby." = '9' Then 'Septiembre'
                                                    When ".$groupby." = '10' Then 'Octubre'
                                                    When ".$groupby." = '11' Then 'Noviembre'
                                                    When ".$groupby." = '12' Then 'Diciembre'
                                                Else ".$groupby." End as txtheader";
                                    
                                } else {
                                    $case= ", " . $groupby . ", " . $groupby . " as txtheader";
                                }
                                $sql.= $case;
                                $sqlgrafica.= $case;
                            } 
                                                
                            if ($groupby=='genero'){
                                $sql .= ", genero";
                                $sqlgrafica.= ", genero";
                            }
                            if ($groupby=='grupo'){
                                $sql .= ", grupo";
                                $sqlgrafica.= ", grupo";
                            }    
                            if ($groupby=='rubro'){
                                $sql .= ", rubro";
                                $sqlgrafica.= ", rubro";
                            }
                            if ($groupby=='cuenta'){
                                $sql .= ", cuenta";
                                $sqlgrafica.= ", cuenta";
                            }
                            if ($groupby=='subcuenta'){
                                $sql .= ", subcuenta";
                                $sqlgrafica.= ", subcuenta";
                            }
                            if ($groupby=='sscuenta'){
                                $sql .= ", sscuenta";
                                $sqlgrafica.= ", sscuenta";
                            }
                            if ($groupby=='ssscuenta'){
                                $sql .= ", ssscuenta";
                                $sqlgrafica.= ", ssscuenta";
                            }
                            if ($groupby=='sssscuenta'){
                                $sql .= ", sssscuenta";
                                $sqlgrafica.= ", sssscuenta";
                            }
            
                            $sql .= " FROM DW_Presupuestos d 
                                        INNER JOIN DWD_TiempoFiscal t ON d.u_tiempo = t.u_tiempo
                                        INNER JOIN DWD_Tiempo t2 ON d.u_tiempocaptura = t2.u_tiempo";
                            
                            $sqlgrafica .= " FROM DW_Presupuestos d
                                        INNER JOIN DWD_TiempoFiscal t ON d.u_tiempo = t.u_tiempo
                                        INNER JOIN DWD_Tiempo t2 ON d.u_tiempocaptura = t2.u_tiempo";
                                            
                            $sql .= " WHERE Fecha between '" .$fechaini."' AND '".$fechafin."'";
                            $sqlgrafica .= " WHERE Fecha between '" .$fechaini."' AND '".$fechafin."'";
                            $sql .= $condicion." ".$wherecond;
                            $sqlgrafica .= $condicion." ".$wherecond;
                            $sql .= " GROUP BY " . $groupby.$groupbysecond ;
                            $sqlgrafica .= " GROUP BY " . $groupby.$groupbysecond ;
                            $sql .= " ORDER BY ".$OrdenarPor." ". $Ordenar." ".$groupbysecond;
                            $sqlgrafica .= " ORDER BY ".$OrdenarPor." ". $Ordenar." ".$groupbysecond;


                            //echo $sql;
                            
                            $_SESSION["consultareporte"]= $sqlgrafica;
                            echo "<input type=hidden name='filtroexcluir' value='".$groupby."'>";
                            
                            //echo"<pre>: " . $sql;
                            
                            $result = DB_query($sql, $dbDataware);
                            $i=0;
                            $condicion = str_replace("like '%","like $",$condicion);
                            $condicion = str_replace("'","$",$condicion);
                            $header="--";
                            $indSegDim = 1;
                            $arrTotales=array();
                            $arrRowTotal=array();
                            $arrRowTotalT=array();
                            $tottopcol = count($_SESSION['topcolumns']);
                            
                            while ($myrow=DB_fetch_array($result)){
                                if ($header != $myrow['header']){
                                    if ($header!="--"){
                                        //revisar que esten escritas todas las topcolumnas
                                        for($k=$indSegDim;$k<=$tottopcol;$k++){
                                            echo "<td colspan='".$colVisibles."'>&nbsp;</td>";
                                        }
                                        //poner totales por filas
                                        if ($groupbysecond){        
                                            if ($_SESSION['chkcol'][1]){
                                                $arrRowTotalT[1]+=$arrRowTotal[$i][1];
                                                echo '<td style="text-align:right;" class="numero_celda">';
                                                     echo '<b>'.number_format($arrRowTotal[$i][1],0);                       
                                                echo '</td>';
                                            }
                                            if ($_SESSION['chkcol'][20]){
                                                $arrRowTotalT[20]+=$arrRowTotal[$i][20];
                                                echo '<td style="text-align:right;" class="numero_celda">';
                                                echo '<b>'.number_format($arrRowTotal[$i][20],0);
                                                echo '</td>';
                                            }
                                            if ($_SESSION['chkcol'][2]){                    
                                                $arrRowTotalT[2]+=$arrRowTotal[$i][2];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                     echo '<b>'.number_format($arrRowTotal[$i][2],0);
                                                echo '</td>';
                                            }
                                            /*if ($_SESSION['chkcol'][3]){
                                                $arrRowTotalT[3]+=$arrRowTotal[$i][3];
                                                $arrRowTotal[$i][3] = (($arrRowTotal[$i][2]*100)/$arrRowTotal[$i][1])-100;
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format($arrRowTotal[$i][3],0);
                                                echo '%</td>';
                                            }*/
                                            if ($_SESSION['chkcol'][4]){                
                                                $arrRowTotalT[4]+=$arrRowTotal[$i][4];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                     echo '<b>'.number_format(($arrRowTotal[$i][4]),2);
                                                echo '</td>';
                                            }
                                            /*if ($_SESSION['chkcol'][5]){
                                                $arrRowTotalT[5]+=$arrRowTotal[$i][5];
                                                $arrRowTotal[$i][5] = (($arrRowTotal[$i][4]*100)/$arrRowTotal[$i][2]); 
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][5]),0);
                                                echo '%</td>';
                                            }*/
                                            if ($_SESSION['chkcol'][6]){
                                                $arrRowTotalT[4]+=$arrRowTotal[$i][6];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][6]),0);
                                                echo '</td>';
                                            }
                                            /*if ($_SESSION['chkcol'][7]){
                                                $arrRowTotalT[7]+=$arrRowTotal[$i][7];
                                                $arrRowTotal[$i][7] = (($arrRowTotal[$i][6]*100)/$arrRowTotal[$i][4]);
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][7]),0);
                                                echo '%</td>';
                                            }*/
                                            if ($_SESSION['chkcol'][8]){
                                                $arrRowTotalT[8]+=$arrRowTotal[$i][8];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][8]),0);
                                                echo '</td>';
                                            }
                                            /*if ($_SESSION['chkcol'][9]){
                                                $arrRowTotalT[9]+=$arrRowTotal[$i][9];
                                                $arrRowTotal[$i][9] = (($arrRowTotal[$i][8]*100)/$arrRowTotal[$i][6]);
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][9]),0);
                                                echo '%</td>';
                                            }*/
                                            if ($_SESSION['chkcol'][10]){
                                                $arrRowTotalT[10]+=$arrRowTotal[$i][10];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][10]),0);
                                                echo '</td>';
                                            }
                                            
                                            /*if ($_SESSION['chkcol'][11]){
                                                $arrRowTotalT[11]+=$arrRowTotal[$i][11];
                                                $arrRowTotal[$i][11] = (($arrRowTotal[$i][10]*100)/$arrRowTotal[$i][8]);
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][11]),0);
                                                echo '%</td>';
                                            }*/
                                            
                                            if ($_SESSION['chkcol'][12]){
                                                $arrRowTotalT[12]+=$arrRowTotal[$i][12];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][12]),0);
                                                echo '</td>';
                                            }
                                            
                                            if ($_SESSION['chkcol'][13]){
                                                $arrRowTotalT[13]+=$arrRowTotal[$i][13];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][13]),0);
                                                echo '</td>';
                                            }
                                            /*if ($_SESSION['chkcol'][14]){
                                                $arrRowTotalT[14]+=$arrRowTotal[$i][14];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][14]),0);
                                                echo '</td>';
                                            }*/
                                            if ($_SESSION['chkcol'][14]){
                                                $arrRowTotalT[14]+=$arrRowTotal[$i][14];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][15]),0);
                                                echo '</td>';
                                            }
                                            /*if ($_SESSION['chkcol'][15]){
                                                $arrRowTotalT[15]+=$arrRowTotal[$i][15];
                                                $arrRowTotal[$i][15] = (($arrRowTotal[$i][14]*100)/$arrRowTotal[$i][2]);
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][15]),0);
                                                echo '%</td>';
                                            }*/
                                            if ($_SESSION['chkcol'][16]){
                                                $arrRowTotalT[16]+=$arrRowTotal[$i][16];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][16]),0);
                                                echo '</td>';
                                            }
                                            if ($_SESSION['chkcol'][17]){
                                                $arrRowTotalT[17]+=$arrRowTotal[$i][17];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][17]),0);
                                                echo '</td>';
                                            }
                                            if ($_SESSION['chkcol'][18]){
                                                $arrRowTotalT[18]+=$arrRowTotal[$i][18];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][18]),0);
                                                echo '</td>';
                                            }
                                            if ($_SESSION['chkcol'][19]){
                                                $arrRowTotalT[19]+=$arrRowTotal[$i][19];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][19]),0);
                                                echo '</td>';
                                            }
                                            if ($_SESSION['chkcol'][21]){
                                                $arrRowTotalT[21]+=$arrRowTotal[$i][21];
                                                echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                                echo '<b>'.number_format(($arrRowTotal[$i][21]),0);
                                                echo '</td>';
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
                                    echo "<td align='center'><font size=3>";
                                    $ver=strtoupper($myrow['header']);
                                    $v = $ver;
                                    if (($groupby=='Mescaptura') or ($groupby == 'PeriodoFiscal')){
                                        $nombremes=glsnombremeslargo($myrow['header']);
                                        $ver=$nombremes;
                                    }
                                    if ($groupby=='Fechacaptura'){
                                        $ver=substr($myrow['header'], 0,10);
                                    }
                                    /*
                                    $concat = "";
                                    if (($groupby=="genero" or $groupby=="grupo") and $myrow[16]!=""){
                                        $concat = "[".$myrow[16]."]";
                                    }
                                    */

                                    if ($esexcel)
                                        echo "<u>" . $ver;  
                                    else{
                                        $cond=" AND $groupby=\$".$myrow['header']."\$";
                                        echo '<table border=0 width=100%><tr><td width="15%">';
                                        if ($_REQUEST['keepvisible']!=1){
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
                                                    .$ToMes. '&ToYear=' . $ToYear .
                                                    '&OrdenarPor=' .$groupby. '&Excluir=1&Ordenar=asc&filtro='.$groupby.'&condicionante=^'.
                                                    '&valor='.$myrow[0].'&condicion='. $condicion .'" >';
                                        }else{
                                            echo '<a href="dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby='.$groupby.'&FromDia='.$FromDia.'&FromMes=' .$FromMes. '&FromYear=' . $FromYear .'&ToDia='.$ToDia.'&ToMes='
                                                    .$ToMes. '&ToYear=' . $ToYear .
                                                    '&OrdenarPor=' .$groupby. '&Excluir=1&Ordenar=asc&filtro='.$groupby.'&condicionante=^'.
                                                    '&valor='.$myrow[0].'&condicion='. $condicion .'&keepvisible=1" >';
                                        }
                                        
                                        echo '<img src="images/cancel.gif" WIDTH=12 HEIGHT=12  alt="Excluir"></a>';
                                        echo '</td>';
                                            
                                        echo '<td style="text-align:left;" width="85%">&nbsp;&nbsp;';
                                        echo "<a href='dwh_ReportePresupuestos_V5_0.php?procesar=GENERAR&groupby=".$sig_groupby."&FromDia=".$FromDia."&FromMes=" .$FromMes. "&FromYear=" . $FromYear ."&ToDia=".$ToDia."&ToMes="
                                                .$ToMes. "&ToYear=" . $ToYear .
                                                "&OrdenarPor=" .$sig_groupby. "&Ordenar=asc&filtro=".$groupby."&groupbysecond=".$groupbysecondvalue."&condicion=". $condicion.$cond ."&keepvisible=1' >";
                                        echo "<u>" . $ver;
                                        echo "</a>";
                                        echo '</td>';
                                        echo '</tr></table>';
                                    }
                                    echo "</td>";
                                    if(!(array_search($groupby,$arrcolscondescripcion)===FALSE)){
                                        echo "<td align='left'>";  //a�o fiscal
                                            echo $myrow['txtheader'];
                                        echo "</td>";
                                    }
                                    
                                }
                
                                if ($groupbysecond){                    
                                    //buscar que columna trae el registro
                                    for($index=$indSegDim;$index<=count($_SESSION['topcolumns']);$index++){
                                        if ($myrow[1] == $_SESSION['topcolumns'][$index]){
                                            if ($_SESSION['chkcol'][1]){
                                                echo "<td style='text-align:right;' width='10%' class='numero_celda'>" . number_format($myrow['presupuestoaprobado'],0) . "</td>";
                                                $arrRowTotal[$i][1] += $myrow['presupuestoaprobado'];
                                            }
                                            if ($_SESSION['chkcol'][20]){
                                                echo "<td style='text-align:right;' width='10%' class='numero_celda'>" . number_format($myrow['presupuestoampliacion'],0) . "</td>";
                                                $arrRowTotal[$i][20] += $myrow['presupuestoampliacion'];
                                            }
                                            if ($_SESSION['chkcol'][2]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['presupuestomodificado'],0) . "</td>";
                                                $arrRowTotal[$i][2] += $myrow['presupuestomodificado'];
                                            }
                                            /*if ($_SESSION['chkcol'][3]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porcpresupuestomodificado'],0) . "%</td>";
                                                $arrRowTotal[$i][3] += $myrow['porcpresupuestomodificado'];
                                            }*/
                                            if ($_SESSION['chkcol'][4]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['historicocomprometido'],0) . "</td>";
                                                $arrRowTotal[$i][4] += $myrow['historicocomprometido'];
                                            }
                                            /*if ($_SESSION['chkcol'][5]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porchistoricocomprometido'],0) . "%</td>";
                                                $arrRowTotal[$i][5] += $myrow['porchistoricocomprometido'];
                                            }*/
                                            if ($_SESSION['chkcol'][6]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['historicodevengado'],0) . "</td>";
                                                $arrRowTotal[$i][6] += $myrow['historicodevengado'];
                                            }
                                            /*if ($_SESSION['chkcol'][7]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porchistoricodevengado'],0) . "%</td>";
                                                $arrRowTotal[$i][7] += $myrow['porchistoricodevengado'];
                                            }*/
                                            if ($_SESSION['chkcol'][8]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['historicoejercido'],0) . "</td>";
                                                $arrRowTotal[$i][8] += $myrow['historicoejercido'];
                                            }
                                            /*if ($_SESSION['chkcol'][9]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porchistoricoejercido'],0) . "%</td>";
                                                $arrRowTotal[$i][9] += $myrow['porchistoricoejercido'];
                                            }*/
                                            if ($_SESSION['chkcol'][10]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['pagado'],0) . "</td>";
                                                $arrRowTotal[$i][10] += $myrow['pagado'];
                                            }
                                            
                                            /*if ($_SESSION['chkcol'][11]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porcpagado'],0) . "%</td>";
                                                $arrRowTotal[$i][11] += $myrow['porcpagado'];
                                            }*/
                                            
                                            if ($_SESSION['chkcol'][12]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['comprometido'],0) . "</td>";
                                                $arrRowTotal[$i][12] += $myrow['comprometido'];
                                            }
                                            if ($_SESSION['chkcol'][13]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['gastodeinversion'],0) . "</td>";
                                                $arrRowTotal[$i][13] += $myrow['gastodeinversion'];
                                            }
                                            /*
                                            if ($_SESSION['chkcol'][14]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['gastodeoperacion'],0) . "</td>";
                                                $arrRowTotal[$i][14] += $myrow['gastodeoperacion'];
                                            }*/
                                            if ($_SESSION['chkcol'][14]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['disponibleparacomprometer'],0) . "</td>";
                                                $arrRowTotal[$i][14] += $myrow['disponibleparacomprometer'];
                                            }
                                            
                                            /*if ($_SESSION['chkcol'][15]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porcdisponibleparacomprometer'],0) . "%</td>";
                                                $arrRowTotal[$i][15] += $myrow['porcdisponibleparacomprometer'];
                                            }*/
                                            if ($_SESSION['chkcol'][16]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['devengado'],0) . "</td>";
                                                $arrRowTotal[$i][16] += $myrow['devengado'];
                                            }
                                            if ($_SESSION['chkcol'][17]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['ejercido'],0) . "</td>";
                                                $arrRowTotal[$i][17] += $myrow['ejercido'];
                                            }
                                            if ($_SESSION['chkcol'][18]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['deudaejercicio'],0) . "</td>";
                                                $arrRowTotal[$i][18] += $myrow['deudaejercicio'];
                                            }
                                            if ($_SESSION['chkcol'][19]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['pagado'],0) . "</td>";
                                                $arrRowTotal[$i][19] += $myrow['pagado'];
                                            }
                                            if ($_SESSION['chkcol'][21]){
                                                echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['subejercicio'],0) . "</td>";
                                                $arrRowTotal[$i][21] += $myrow['subejercicio'];
                                            }
                                            break;
                                        }else{ 
                                            echo "<td colspan='".$colVisibles."'>&nbsp;</td>";
                                        }
                                    }//for
                                    $indSegDim = $index;
                                }else{
                                    if ($_SESSION['chkcol'][1])
                                        echo "<td style='text-align:right;' width='10%' class='numero_celda'>" . number_format($myrow['presupuestoaprobado'],0) . "</td>";
                                    if ($_SESSION['chkcol'][20])
                                        echo "<td style='text-align:right;' width='10%' class='numero_celda'>" . number_format($myrow['presupuestoampliacion'],0) . "</td>";
                                    if ($_SESSION['chkcol'][2])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['presupuestomodificado'],0) . "</td>";
                                    /*if ($_SESSION['chkcol'][3])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porcpresupuestomodificado'],0) . "%</td>";*/
                                    if ($_SESSION['chkcol'][4])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['historicocomprometido'],0) . "</td>";
                                    /*if ($_SESSION['chkcol'][5])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porchistoricocomprometido'],0) . "%</td>";*/
                                    if ($_SESSION['chkcol'][6])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['historicodevengado'],0) . "</td>";
                                    /*if ($_SESSION['chkcol'][7])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porchistoricodevengado'],0) . "%</td>";*/
                                    if ($_SESSION['chkcol'][8])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['historicoejercido'],0) . "</td>";
                                    /*if ($_SESSION['chkcol'][9])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porchistoricoejercido'],0) . "%</td>";*/
                                    if ($_SESSION['chkcol'][10])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['pagado'],0) . "</td>";
                                    
                                    /*if ($_SESSION['chkcol'][11])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porcpagado'],0) . "%</td>";*/
                                    if ($_SESSION['chkcol'][12])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['comprometido'],0) . "</td>";
                                    if ($_SESSION['chkcol'][13])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['gastodeinversion'],0) . "</td>";
                                    /*if ($_SESSION['chkcol'][14])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['gastodeoperacion'],0) . "</td>";
                                    */
                                    if ($_SESSION['chkcol'][14])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['disponibleparacomprometer'],0) . "</td>";
                                    /*if ($_SESSION['chkcol'][15])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['porcdisponibleparacomprometer'],0) . "%</td>";*/
                                    if ($_SESSION['chkcol'][16])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['devengado'],0) . "</td>";
                                    if ($_SESSION['chkcol'][17])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['ejercido'],0) . "</td>";
                                    if ($_SESSION['chkcol'][18])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['deudaejercicio'],0) . "</td>";
                                    if ($_SESSION['chkcol'][19])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['pagado'],0) . "</td>";
                                    if ($_SESSION['chkcol'][21])
                                        echo "<td style='text-align:right;' width='15%' class='numero_celda'>" . number_format($myrow['subejercicio'],0) . "</td>";
                                    
                                }
                                //echo "</tr>";

                                if (count($arrTotales) > 0) {
                
                                $arrTotales[$indSegDim]['sumapresupuestoaprobado'] += $myrow['presupuestoaprobado'];
                                $arrTotales[$indSegDim]['sumapresupuestoampliacion'] += $myrow['presupuestoampliacion'];
                                $arrTotales[$indSegDim]['sumapresupuestomodificado'] += $myrow['presupuestomodificado'];
                                $arrTotales[$indSegDim]['sumahistoricocomprometido'] += $myrow['historicocomprometido'];
                                $arrTotales[$indSegDim]['sumahistoricodevengado'] += $myrow['historicodevengado'];
                                $arrTotales[$indSegDim]['sumahistoricoejercido'] += $myrow['historicoejercido'];
                                $arrTotales[$indSegDim]['sumapagado'] += $myrow['pagado'];
                                $arrTotales[$indSegDim]['sumasubejercicio'] += $myrow['subejercicio'];
                                $arrTotales[$indSegDim]['sumagastodeinversion'] += $myrow['gastodeinversion'];
                                $arrTotales[$indSegDim]['sumagastodeoperacion'] += $myrow['gastodeoperacion'];
                                $arrTotales[$indSegDim]['sumacomprometido'] += $myrow['comprometido'];
                                $arrTotales[$indSegDim]['sumadisponibleparacomprometer'] += $myrow['disponibleparacomprometer'];
                                $arrTotales[$indSegDim]['sumadevengado'] += $myrow['devengado'];
                                $arrTotales[$indSegDim]['sumaejercido'] += $myrow['ejercido'];
                                $arrTotales[$indSegDim]['sumadeudaejercicio'] += $myrow['deudaejercicio'];
                                }
                                $indSegDim++;
                            }//while
                            $totrows = $i;
                            
                            //revisar que esten escritas todas las topcolumnas
                            for($k=$indSegDim;$k<=count($_SESSION['topcolumns']);$k++){         
                                    echo "<td colspan='".$colVisibles."'>&nbsp;</td>";
                            }
                            //poner totales por filas
                            if ($groupbysecond){
                                if ($_SESSION['chkcol'][1]){
                                    $arrRowTotalT[1]+=$arrRowTotal[$i][1];                  
                                    echo '<td style="text-align:right;" width="10%" class="numero_celda">';
                                         echo '<b>'.number_format($arrRowTotal[$i][1],0);                       
                                    echo '</td>';
                                }
                                if ($_SESSION['chkcol'][20]){
                                    $arrRowTotalT[20]+=$arrRowTotal[$i][20];
                                    echo '<td style="text-align:right;" width="10%" class="numero_celda">';
                                    echo '<b>'.number_format($arrRowTotal[$i][20],0);
                                    echo '</td>';
                                }
                                if ($_SESSION['chkcol'][2]){                    
                                    $arrRowTotalT[2]+=$arrRowTotal[$i][2];                  
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                         echo '<b>'.number_format($arrRowTotal[$i][2],0);
                                    echo '</td>';
                                }
                                /*if ($_SESSION['chkcol'][3]){                
                                    $arrRowTotalT[3]+=$arrRowTotal[$i][3];
                                    $arrRowTotal[$i][3] = (($arrRowTotal[$i][2]*100)/$arrRowTotal[$i][1])-100;
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                         echo '<b>'.number_format(($arrRowTotal[$i][3]),0);
                                    echo '%</td>';
                                }*/
                                if ($_SESSION['chkcol'][4]){
                                    $arrRowTotalT[4]+=$arrRowTotal[$i][4];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][4]),0);
                                    echo '</td>';
                                }
                                /*if ($_SESSION['chkcol'][5]){
                                    $arrRowTotalT[5]+=$arrRowTotal[$i][5];
                                    $arrRowTotal[$i][5] = (($arrRowTotal[$i][4]*100)/$arrRowTotal[$i][2]);
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][5]),0);
                                    echo '%</td>';
                                }*/
                                if ($_SESSION['chkcol'][6]){
                                    $arrRowTotalT[6]+=$arrRowTotal[$i][6];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][6]),0);
                                    echo '</td>';
                                }
                                /*if ($_SESSION['chkcol'][7]){
                                    $arrRowTotalT[7]+=$arrRowTotal[$i][7];
                                    $arrRowTotal[$i][7] = (($arrRowTotal[$i][6]*100)/$arrRowTotal[$i][4]);
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][7]),0);
                                    echo '%</td>';
                                }*/
                                if ($_SESSION['chkcol'][8]){
                                    $arrRowTotalT[8]+=$arrRowTotal[$i][8];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][8]),0);
                                    echo '</td>';
                                }
                               /* if ($_SESSION['chkcol'][9]){
                                    $arrRowTotalT[9]+=$arrRowTotal[$i][9];
                                    $arrRowTotal[$i][9] = (($arrRowTotal[$i][8]*100)/$arrRowTotal[$i][6]);
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][9]),0);
                                    echo '%</td>';
                                }*/
                                if ($_SESSION['chkcol'][10]){
                                    $arrRowTotalT[10]+=$arrRowTotal[$i][10];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][10]),0);
                                    echo '</td>';
                                }
                                /*if ($_SESSION['chkcol'][11]){
                                    $arrRowTotalT[11]+=$arrRowTotal[$i][11];
                                    $arrRowTotal[$i][11] = (($arrRowTotal[$i][10]*100)/$arrRowTotal[$i][8]);
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][11]),0);
                                    echo '%</td>';
                                }*/
                                if ($_SESSION['chkcol'][12]){
                                    $arrRowTotalT[12]+=$arrRowTotal[$i][12];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][12]),0);
                                    echo '</td>';
                                        
                                }
                                
                                if ($_SESSION['chkcol'][13]){
                                    $arrRowTotalT[13]+=$arrRowTotal[$i][13];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][13]),0);
                                    echo '</td>';
                                }
                                /*
                                if ($_SESSION['chkcol'][14]){
                                    $arrRowTotalT[14]+=$arrRowTotal[$i][14];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][14]),0);
                                    echo '</td>';
                                }
                                */
                                if ($_SESSION['chkcol'][14]){
                                    $arrRowTotalT[14]+=$arrRowTotal[$i][14];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][14]),0);
                                    echo '</td>';
                                }
                                
                                /*if ($_SESSION['chkcol'][15]){
                                    $arrRowTotalT[15]+=$arrRowTotal[$i][15];
                                    $arrRowTotal[$i][15] = (($arrRowTotal[$i][14]*100)/$arrRowTotal[$i][2]);
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][14]),0);
                                    echo '%</td>';
                                }*/
                                if ($_SESSION['chkcol'][16]){
                                    $arrRowTotalT[16]+=$arrRowTotal[$i][16];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][16]),0);
                                    echo '</td>';
                                }
                                if ($_SESSION['chkcol'][17]){
                                    $arrRowTotalT[17]+=$arrRowTotal[$i][17];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][17]),0);
                                    echo '</td>';
                                }
                                if ($_SESSION['chkcol'][18]){
                                    $arrRowTotalT[18]+=$arrRowTotal[$i][18];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][18]),0);
                                    echo '</td>';
                                }
                                if ($_SESSION['chkcol'][19]){
                                    $arrRowTotalT[19]+=$arrRowTotal[$i][19];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][19]),0);
                                    echo '</td>';
                                }
                                if ($_SESSION['chkcol'][21]){
                                    $arrRowTotalT[21]+=$arrRowTotal[$i][21];
                                    echo '<td style="text-align:right;" width="15%" class="numero_celda">';
                                    echo '<b>'.number_format(($arrRowTotal[$i][21]),0);
                                    echo '</td>';
                                }
                            }//if dobledim
                            echo "</tr>";
                            echo "<tr style='background-color:#3a9143;'>";
                                if(!(array_search($groupby,$arrcolscondescripcion)===FALSE)){
                                    echo "<td colspan='" . ($colspanini+1) . "' class='pie_derecha'>";
                                        echo "<b>TOTALES: ";
                                }else{
                                    echo "<td colspan='2' style='background-color:#3a9143;' align='right'><font color='white'>";
                                    echo "<b>TOTALES : ";
                                }
                                echo "<input type='hidden' name='renglones' id='totrows' value=". $totrows.">";
                                echo "</td>";
                                
                                $ind=1;


                                foreach($_SESSION['topcolumns'] as $namecol){

                                    if ( count($arrTotales) == 0) exit;


                                    $arrTotales[$ind]['sumaporcpresupuestomodificado']  = (($arrTotales[$ind]['sumapresupuestomodificado'] * 100) / $arrTotales[$ind]['sumapresupuestoaprobado']) - 100;
                                    $arrTotales[$ind]['sumaporchistoricocomprometido']  = ($arrTotales[$ind]['sumahistoricocomprometido'] * 100) / $arrTotales[$ind]['sumapresupuestomodificado'];
                                    $arrTotales[$ind]['sumaporchistoricodevengado']  = ($arrTotales[$ind]['sumahistoricodevengado'] * 100) / $arrTotales[$ind]['sumahistoricocomprometido'];
                                    $arrTotales[$ind]['sumaporchistoricoejercido']  = ($arrTotales[$ind]['sumahistoricoejercido'] * 100) / $arrTotales[$ind]['sumahistoricodevengado'];
                                    $arrTotales[$ind]['sumaporcpagado']  = ($arrTotales[$ind]['sumapagado'] * 100) / $arrTotales[$ind]['sumahistoricoejercido'];
                                    $arrTotales[$ind]['sumaporcdisponibleparacomprometer']  = ($arrTotales[$ind]['sumadisponibleparacomprometer'] * 100) / $arrTotales[$ind]['sumapresupuestomodificado'];
                                    
                                    if ($_SESSION['chkcol'][1]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format($arrTotales[$ind]['sumapresupuestoaprobado'],0);
                                        echo "</td>";
                                    }
                                    if ($_SESSION['chkcol'][20]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format($arrTotales[$ind]['sumapresupuestoampliacion'],0);
                                        echo "</td>";
                                    }
                                    if ($_SESSION['chkcol'][2]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumapresupuestomodificado']),0);
                                        echo "</td>";
                                    }
                                    /*if ($_SESSION['chkcol'][3]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumaporcpresupuestomodificado']),0);
                                        echo "%</td>";
                                    }*/
                                    if ($_SESSION['chkcol'][4]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumahistoricocomprometido']),0);
                                        echo "</td>";
                                    }
                                    /*if ($_SESSION['chkcol'][5]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumaporchistoricocomprometido']),0);
                                        echo "%</td>";
                                    }*/
                                    if ($_SESSION['chkcol'][6]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumahistoricodevengado']),0);
                                        echo "</td>";
                                    }
                                    /*if ($_SESSION['chkcol'][7]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumaporchistoricodevengado']),0);
                                        echo "%</td>";
                                    }*/
                                    if ($_SESSION['chkcol'][8]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumahistoricoejercido']),0);
                                        echo "</td>";
                                    }
                                    /*if ($_SESSION['chkcol'][9]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumaporchistoricoejercido']),0);
                                        echo "%</td>";
                                    }*/
                                    if ($_SESSION['chkcol'][10]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumapagado']),0);
                                        echo "</td>";
                                    }
                                    
                                    /*if ($_SESSION['chkcol'][11]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumaporcpagado']),0);
                                        echo "%</td>";
                                    }*/
                                    
                                    if ($_SESSION['chkcol'][12]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumacomprometido']),0);
                                        echo "</td>";
                                    }
                                    if ($_SESSION['chkcol'][13]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumagastodeinversion']),0);
                                        echo "</td>";
                                    }/*
                                    if ($_SESSION['chkcol'][14]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumagastodeoperacion']),0);
                                        echo "</td>";
                                    }
                                    */
                                    
                                    if ($_SESSION['chkcol'][14]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumadisponibleparacomprometer']),0);
                                        echo "</td>";
                                    }
                                    
                                    /*if ($_SESSION['chkcol'][15]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumaporcdisponibleparacomprometer']),0);
                                        echo "%</td>";
                                    }*/
                                    
                                    if ($_SESSION['chkcol'][16]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumadevengado']),0);
                                        echo "</td>";
                                    }
                                    if ($_SESSION['chkcol'][17]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumaejercido']),0);
                                        echo "</td>";
                                    }
                                    if ($_SESSION['chkcol'][18]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumadeudaejercicio']),0);
                                        echo "</td>";
                                    }
                                    if ($_SESSION['chkcol'][19]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumapagado']),0);
                                        echo "</td>";
                                    }
                                    if ($_SESSION['chkcol'][21]){
                                        echo "<td style='background-color:#3a9143;' align='right'><font color='white'>";
                                        echo "<b>" .number_format(($arrTotales[$ind]['sumasubejercicio']),0);
                                        echo "</td>";
                                    }
                                    $ind++;
                                }//foreach
                                //poner totales de totales de filas si es segunda dimension
                                if ($groupbysecond){
                                    if ($_SESSION['chkcol'][1]){
                                        echo '<td class="pie_derecha" width="10%" >';
                                        echo '<b>'.number_format($arrRowTotalT[1],0);                       
                                        echo '</td>';
                                    }
                                    if ($_SESSION['chkcol'][20]){
                                        echo '<td class="pie_derecha" width="10%" >';
                                        echo '<b>'.number_format($arrRowTotalT[20],0);
                                        echo '</td>';
                                    }
                                    if ($_SESSION['chkcol'][2]){                    
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format($arrRowTotalT[2],0);
                                        echo '</td>';
                                    }
                                    
                                    /*if ($_SESSION['chkcol'][3]){                
                                        $arrRowTotalT[3] = (($arrRowTotalT[2]*100)/$arrRowTotalT[1])-100;
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[3]),0);
                                        echo '%</td>';
                                    }*/
                                    if ($_SESSION['chkcol'][4]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[4]),0);
                                        echo '</td>';
                                    }
                                    /*if ($_SESSION['chkcol'][5]){
                                        $arrRowTotalT[5] = (($arrRowTotalT[4]*100)/$arrRowTotalT[2]);
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[5]),0);
                                        echo '%</td>';
                                    }*/
                                    if ($_SESSION['chkcol'][6]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[6]),0);
                                        echo '</td>';
                                    }
                                   /* if ($_SESSION['chkcol'][7]){
                                        $arrRowTotalT[7] = (($arrRowTotalT[6]*100)/$arrRowTotalT[4]);
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[7]),0);
                                        echo '%</td>';
                                    }*/
                                    if ($_SESSION['chkcol'][8]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[8]),0);
                                        echo '</td>';
                                    }
                                    /*if ($_SESSION['chkcol'][9]){
                                        $arrRowTotalT[9] = (($arrRowTotalT[8]*100)/$arrRowTotalT[6]);
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[9]),0);
                                        echo '%</td>';
                                    }*/
                                    if ($_SESSION['chkcol'][10]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[10]),0);
                                        echo '</td>';
                                    }
                                    
                                    /*if ($_SESSION['chkcol'][11]){
                                        $arrRowTotalT[11] = (($arrRowTotalT[10]*100)/$arrRowTotalT[8]);
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[11]),0);
                                        echo '%</td>';
                                    }*/
                                    
                                    if ($_SESSION['chkcol'][12]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[12]),0);
                                        echo '</td>';
                                    }
                                    
                                    if ($_SESSION['chkcol'][13]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[13]),0);
                                        echo '</td>';
                                    }
                                    /*
                                    if ($_SESSION['chkcol'][14]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[14]),0);
                                        echo '</td>';
                                    }*/
                                    
                                    if ($_SESSION['chkcol'][14]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[14]),0);
                                        echo '</td>';
                                    }
                                    
                                    /*if ($_SESSION['chkcol'][15]){
                                        $arrRowTotalT[15] = (($arrRowTotalT[14]*100)/$arrRowTotalT[2]);
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[15]),0);
                                        echo '%</td>';
                                    }*/
                                    if ($_SESSION['chkcol'][16]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[16]),0);
                                        echo '</td>';
                                    }
                                    if ($_SESSION['chkcol'][17]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[17]),0);
                                        echo '</td>';
                                    }
                                    if ($_SESSION['chkcol'][18]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[18]),0);
                                        echo '</td>';
                                    }
                                    if ($_SESSION['chkcol'][19]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[19]),0);
                                        echo '</td>';
                                    }
                                    if ($_SESSION['chkcol'][21]){
                                        echo '<td class="pie_derecha" width="15%">';
                                        echo '<b>'.number_format(($arrRowTotalT[21]),0);
                                        echo '</td>';
                                    }
                                }
                            echo "</tr>";
                        echo "</table>";
                        // echo "</td>";
                        //echo "</tr>";
                        //echo "</table>";  ***
echo "</form>";
}

if ($debug==1) {
    //
}
exit;

include('includes/footer_Index.inc');
?>




if (isset($_GET['Ordenar'])) {
    $Ordenar = $_GET['Ordenar'];
} else {
    $Ordenar = "asc";
};

    


