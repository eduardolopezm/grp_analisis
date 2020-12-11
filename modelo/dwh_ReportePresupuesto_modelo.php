<?php

//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//error_reporting(E_ALL);
session_start();
$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
header('Content-type: text/html; charset=ISO-8859-1');

include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
//
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=1567;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

$ErrMsg = _('');

    $groupby = "AnioFiscal";
    $groupbysecond = "";
    $sqlgrafica = "";
    $wherecond = "";
    $OrdenarPor = "";
    $Ordenar = "asc";
    $RootPath = "";
    $Mensaje = "";
    $info = array();

header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";

$TransResult = DB_query($SQL, $db);


    $dbDataware=mysqli_connect('127.0.0.1', 'root', 'becerril', 'des');




if (isset($_GET['OrdenarPor'])) {
    $OrdenarPor = $_GET['OrdenarPor'];
} else {
    $OrdenarPor = $groupby;
};


if (isset($_POST['FromYear'])) {
    $FromYear = $_POST['FromYear'];
} elseif (isset($_GET['FromYear'])) {
    $FromYear = $_GET['FromYear'];
} else {
    $FromYear=date('Y');
};

//$FromYear = 2015;

if (isset($_POST['condicion'])) {
    $condicion= $_POST['condicion'];
} elseif (isset($_GET['condicion'])) {
    $condicion = $_GET['condicion'];
} else {
    $condicion = "";
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

    $arrparametrosintercambio = array(
        0 => 'AnioFiscal',
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

    $groupby = 'AnioFiscal';

    $groupbysecond = 'PeriodoFiscal';

    $sql = "SELECT " . $groupby;

    if ($groupbysecond!="")
        $sql .= ", ".$groupbysecond;


                     $sql .= " , sum(montopresupuestoaprobado*-1) as 'presupuestoaprobado'
                                , sum(montopresupuestarioporejercer*-1) as 'presupuestoporejercer'
                                , sum(montopresupuestoamplreduc*-1) as 'presupuestoamplreduc'
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
    if ($groupby=='numerodocumento') {
        $sql .= ", numerodocumento";
        $sqlgrafica.= ", numerodocumento";
    }

    if (!(array_search($groupby, $arrcolscondescripcion)===false)) {
        $sql .= ", " . $groupby . ", txt" . $groupby . " as txtheader";
        $sqlgrafica .= ", " . $groupby . ", txt" . $groupby . " as txtheader";
    } else {
        if ($groupby == 'AnioFiscal' || $groupby == 'TrimestreFiscal' || $groupby == 'PeriodoFiscal') {
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

    if ($groupby=='genero') {
        $sql .= ", genero";
        $sqlgrafica.= ", genero";
    }
    if ($groupby=='grupo') {
        $sql .= ", grupo";
        $sqlgrafica.= ", grupo";
    }
    if ($groupby=='rubro') {
        $sql .= ", rubro";
        $sqlgrafica.= ", rubro";
    }
    if ($groupby=='cuenta') {
        $sql .= ", cuenta";
        $sqlgrafica.= ", cuenta";
    }
    if ($groupby=='subcuenta') {
        $sql .= ", subcuenta";
        $sqlgrafica.= ", subcuenta";
    }
    if ($groupby=='sscuenta') {
        $sql .= ", sscuenta";
        $sqlgrafica.= ", sscuenta";
    }
    if ($groupby=='ssscuenta') {
        $sql .= ", ssscuenta";
        $sqlgrafica.= ", ssscuenta";
    }
    if ($groupby=='sssscuenta') {
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

                            if ($groupbysecond=="") {
                                $sql .= " GROUP BY " . $groupby ;
                                $sqlgrafica .= " GROUP BY " . $groupby;
                                $sql .= " ORDER BY ".$OrdenarPor." ". $Ordenar;
                                $sqlgrafica .= " ORDER BY ".$OrdenarPor." ". $Ordenar;
                            }
                            else {
                                $sql .= " GROUP BY " . $groupby.", ".$groupbysecond ;
                                $sqlgrafica .= " GROUP BY " . $groupby.", ".$groupbysecond ;
                                $sql .= " ORDER BY ".$OrdenarPor." ". $Ordenar.", ".$groupbysecond;
                                $sqlgrafica .= " ORDER BY ".$OrdenarPor." ". $Ordenar.", ".$groupbysecond;
                            }






                            //echo $sql;

                         //  $sql = " select * FROM DW_Presupuestos d
                           //             INNER JOIN DWD_TiempoFiscal t ON d.u_tiempo = t.u_tiempo
                             //           INNER JOIN DWD_Tiempo t2 ON d.u_tiempocaptura = t2.u_tiempo ";


    $result = DB_query($sql, $dbDataware);

    //'rubrodeingresos' => $myrow["rubrodeingresos"],
    //'finalidad' => $myrow["finalidad"], 'funcion' => $myrow["funcion"],
    //'proyecto' => $myrow["proyecto"],
    //
    //
        /*$info[] = array('AnioFiscal' => $myrow["AnioFiscal"] , 'clavepresupuestal' => $myrow["clavepresupuestal"], 'mes' => $myrow["mes"] ,'ramo' => $myrow["ramo"] , 'organosuperior' => $myrow["organosuperior"] , 'unidadpresupuestal' => $myrow["unidadpresupuestal"] ,
        'tipodegasto' => $myrow["tipodegasto"], 'objetodelgasto' => $myrow["objetodelgasto"], 'subfuncion' => $myrow["subfuncion"], 'ejetematico' => $myrow["ejetematico"],
        'genero' => $myrow["genero"],
         'grupo' => $myrow["grupo"], 'rubro' => $myrow["rubro"], 'subcuenta' => $myrow["subcuenta"],  'sscuenta' => $myrow["sscuenta"],
        'cuenta' => $myrow["cuenta"],
        'sector' => $myrow["sector"], 'programa' => $myrow["programa"], 'subprograma' => $myrow["subprograma"], 'objetivos' => $myrow["objetivos"],*/

    while ($myrow=DB_fetch_array($result)) {

        $info[] = array(
             $groupby => $myrow[$groupby],
             $groupbysecond => $myrow[$groupbysecond],
        'presupuestoaprobado'=>  (float) $myrow["presupuestoaprobado"],
        'presupuestoporejercer'=>  (float) $myrow["presupuestoporejercer"],
        'presupuestoamplreduc' => $myrow["presupuestoamplreduc"],
        'presupuestomodificado' => $myrow["presupuestomodificado"],
        'porcpresupuestomodificado' => $myrow["porcpresupuestomodificado"],
        'historicocomprometido' => $myrow["historicocomprometido"],
        'porchistoricocomprometido' => $myrow["porchistoricocomprometido"],
        'historicodevengado' => $myrow["historicodevengado"],
        'porchistoricodevengado' => $myrow["porchistoricodevengado"],
        'historicoejercido' => $myrow["historicoejercido"],
        'porchistoricoejercido' => $myrow["porchistoricoejercido"],
        'pagado' => $myrow["pagado"],
        'subejercicio' => $myrow["subejercicio"],
        'porcpagado' => $myrow["porcpagado"],
        'comprometido' => $myrow["comprometido"],
        'disponibleparacomprometer' => $myrow["disponibleparacomprometer"],
        'porcdisponibleparacomprometer' => $myrow["porcdisponibleparacomprometer"],
        'devengado' => $myrow["devengado"],
        'ejercido' => $myrow["ejercido"],
        'deudaejercicio' => $myrow["deudaejercicio"],
        'gastodeinversion' => $myrow["gastodeinversion"],
        'gastodeoperacion' => $myrow["gastodeoperacion"]);
    }

    $contenido = array('datos' => $info);

    //$dataObj = array('sql' => $SQL, 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);

    //echo json_encode($dataObj);
    echo json_encode($info);
