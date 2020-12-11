<?php
/**
 * Consulta Transacciones Contables Detalladas
 *
 * @category ABC
 * @package ap_grp
 * @author Jorge Cesar Garcia Baltazar <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/07/2017
 * Fecha Modificación: 31/07/2017
 * Se realizan Consulta Transacciones Contables Detalladas
 */

error_reporting(E_ALL ^ E_NOTICE);

ob_start("ob_gzhandler");

$PageSecurity = 8;

include('includes/session.inc');
$title = _('Reporte de Transacciones Contables');
$funcion=195;
$title = traeNombreFuncion($funcion, $db);
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['tag'])) {
    $_POST['tag']          =$_GET['tag'];
}
if (isset($_GET['ue'])) {
    $_POST['ue']          =$_GET['ue'];
}
if (isset($_GET['xRegion'])) {
    $_POST['xRegion']      =$_GET['xRegion'];
}
if (isset($_GET['xArea'])) {
    $_POST['xArea']        =$_GET['xArea'];
}
if (isset($_GET['xDepto'])) {
    $_POST['xDepto']       =$_GET['xDepto'];
}
if (isset($_GET['legalid'])) {
    $_POST['legalid']      =$_GET['legalid'];
}
if (isset($_GET['FromPeriod'])) {
    $_POST['FromPeriod']   =$_GET['FromPeriod'];
}
if (isset($_GET['ToPeriod'])) {
    $_POST['ToPeriod']     =$_GET['ToPeriod'];
}
if (isset($_GET['cbotipopoliza'])) {
    $_POST['cbotipopoliza']    =$_GET['cbotipopoliza'];
}
if (isset($_GET['cbotipopoliza'])) {
    $_POST['cbotipopoliza']    =$_GET['cbotipopoliza'];
}
if (isset($_GET['poliza'])) {
    $_POST['poliza']       =$_GET['poliza'];
}

$exportar= "";
if (isset($_POST["Excel"])) {
    $exportar= "Excel";
}

if (isset($_POST["CSV"])) {
    $exportar= "CSV";
}

if (isset($_POST['FromPeriod'])) {
    $desdePeriodo = $_POST['FromPeriod'];
} else {
    $desdePeriodo = date("d-m-Y");
}

if (isset($_POST['ToPeriod'])) {
    $hastaPeriodo = $_POST['ToPeriod'];
} else {
    $hastaPeriodo = date("d-m-Y");
}

if (isset($_POST['FromPeriod']) and isset($_POST['ToPeriod'])) {
    $fechainicial = fechaParaComparar('', '-', $_POST['FromPeriod']);
    $fechafinal = fechaParaComparar('', '-', $_POST['ToPeriod']);
}

if (isset($_POST['FromPeriod']) and isset($_POST['ToPeriod']) and $fechainicial > $fechafinal) {
    prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'), 'error');
    $_POST['SelectADifferentPeriod']=_('Selecciona diferentes fechas');
}

if ((!isset($_POST['FromPeriod']) and !isset($_POST['ToPeriod'])) or isset($_POST['SelectADifferentPeriod'])) {

    include('includes/header.inc');
    ?>
    
    <script type="text/javascript" src="javascripts/T_GLTransReport.js"></script>

    <?php
    echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
    ?>

<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="headingOne">
        <h4 class="panel-title row">
          <div class="col-md-3 col-xs-3 text-left">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
             <b>Criterios de filtrado</b>
            </a>
          </div>
        </h4>
      </div>
      <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
        <div class="panel-body">
          <div class="col-md-4">
              <div class="form-inline row" style="display: none;">
                <div class="col-md-3">
                    <span><label>Dependencia: </label></span>
                </div>
                <div class="col-md-9">
                    <select id="legalid" name="legalid" class="form-control">
                        <?php
                        ///Pinta las razones sociales
                        $SQL = "SELECT legalbusinessunit.legalid, legalbusinessunit.legalname";
                        $SQL = $SQL .   " FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
                        $SQL = $SQL .   " WHERE u.tagref = t.tagref ";
                        $SQL = $SQL .   " and u.userid = '" . $_SESSION['UserID'] . "'
                                  GROUP BY legalbusinessunit.legalid, legalbusinessunit.legalname ORDER BY legalbusinessunit.legalid";

                        $result=DB_query($SQL, $db);
                        while ($myrow=DB_fetch_array($result)) {
                            if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]) {
                                echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
                            } else {
                                echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
                            }
                        }
                        ?>
                    </select>
                </div>
              </div>
              <!-- <br> -->
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>UR: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="tag" name="tag" class="form-control tag" onchange="fnCambioUnidadResponsableGeneral('tag','ue')"> 
                        <?php
                        //Pinta las unidades de negocio por usuario
                        $SQL = "SELECT t.tagref,t.tagdescription";
                        $SQL = $SQL .   " FROM sec_unegsxuser u,tags t ";
                        $SQL = $SQL .   " WHERE u.tagref = t.tagref ";
                        $SQL = $SQL .   " and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagref";
                            
                        $result=DB_query($SQL, $db);
                        
                        echo '<option selected value=0>Seleccionar...</option>';
                        
                        while ($myrow=DB_fetch_array($result)) {
                            if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']) {
                                echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'].'</option>';
                            } else {
                                echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'].'</option>';
                            }
                        }
                        ?>
                      </select>
                  </div>
              </div>
              <br>
              <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>UE: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="ue" name="ue[]" class="form-control ue" multiple="multiple">
                        <?php
                        //Pinta las unidades de negocio por usuario
                        $SQL = "SELECT tce.ue,tce.desc_ue as uedescription";
                        $SQL = $SQL .   " FROM sec_unegsxuser u";
                        $SQL = $SQL .   " INNER JOIN tags t on (u.tagref = t.tagref) ";
                        $SQL = $SQL .   " INNER JOIN tb_cat_unidades_ejecutoras tce on  (tce.ur = t.tagref) ";
                        $SQL = $SQL .   " WHERE tce.active = 1 ";
                        $SQL = $SQL .   " and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY tce.ue, uedescription ASC";
                            
                        $result=DB_query($SQL, $db);
                        
                        $cbmPostUE = $_POST['ue'];

                        while ($myrow=DB_fetch_array($result)) {
                            $selected="";
                            if (!empty($cbmPostUE)) {
                                foreach ($cbmPostUE as $key => $value) {
                                    if ($value != -1) {
                                        if ($myrow['ue'] == $value) {
                                            $selected="selected";
                                            break;
                                        }
                                    }
                                }
                            }
                            echo "<option ".$selected." value='" . $myrow['ue'] . "'>" .$myrow['ue']." ".$myrow['uedescription'] . "</option>";
                        }
                        ?>
                      </select>
                  </div>
              </div>
          </div>
          <div class="col-md-4">
              <div class="form-inline row">
                <div class="col-md-3">
                    <span><label>Tipo Póliza: </label></span>
                </div>
                <div class="col-md-9">
                  <select id="cbotipopoliza" name="cbotipopoliza[]" class="form-control cbotipopoliza" multiple="multiple">
                    <?php
                    // echo "<option value='-1'>Seleccionar...</option>";
                    $SQL = "SELECT id, ln_nombre FROM tb_cat_poliza_visual WHERE nu_activo = 1 ORDER BY ln_nombre ASC";
                    
                    $ErrMsg = _('No se obtuvieron los tipos de póliza visual');
                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $cbotipopoliza = $_POST['cbotipopoliza'];

                    while ($myrow=DB_fetch_array($TransResult)) {
                        $selected="";
                        if (!empty($cbotipopoliza)) {
                            foreach ($cbotipopoliza as $key => $value) {
                                if ($value != -1) {
                                    if ($myrow['id'] == $value) {
                                        $selected="selected";
                                        break;
                                    }
                                }
                            }
                        }
                        echo "<option ".$selected." value='" . $myrow['id'] . "'>" . $myrow['ln_nombre'] . "</option>";
                    }
                    ?>
                  </select>
              </div>
            </div>
            <br>
            <?php
            if (!isset($_POST['poliza'])) {
                $_POST['poliza'] = '*';
            }
            if ($_POST['poliza'] == '') {
                $_POST['poliza'] = '*';
            }
            ?>
            <component-number-label label="Folio Póliza: " id="poliza" name="poliza" placeholder="Folio Póliza" title="Folio Póliza" value="<?php echo $_POST['poliza']; ?>"></component-number-label>
          </div>
          <div class="col-md-4">
              <component-date-label label="Desde: " id="FromPeriod" name="FromPeriod" value="<?php echo $desdePeriodo; ?>"></component-date-label>
              <br>
              <component-date-label label="Hasta: " id="ToPeriod" name="ToPeriod" value="<?php echo $hastaPeriodo; ?>"></component-date-label>
          </div>
          <div class="row"></div>
          <div align="center"> 
            <component-button type="submit" id="ShowTB" name="ShowTB" class="glyphicon glyphicon-search" value="Filtrar"></component-button>
          </div>
        </div>
      </div>
  </div>
</div>

    <?php
    echo '<table class=tuneadoseparacion>';
    /* SELECCION DEL REGION */
    echo '<tr style="display: none;"><td><label>' . _('Por Región') . ':' . "</label></td>
        <td><select tabindex='4' name='xRegion' id='xRegion'>";

    $sql = "SELECT regions.regioncode, CONCAT(regions.regioncode,' - ',regions.name) as name FROM regions JOIN areas ON areas.regioncode = regions.regioncode
            JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
          WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
          GROUP BY regions.regioncode, regions.name";
    
    $result=DB_query($sql, $db);
    
    echo "<option selected value='0'>Todas las regiones...</option>";

    while ($myrow=DB_fetch_array($result)) {
        if ($myrow['regioncode'] == $_POST['xRegion']) {
            echo "<option selected value='" . $myrow["regioncode"] . "'>" . $myrow['name'];
        } else {
            echo "<option value='" . $myrow['regioncode'] . "'>" . $myrow['name'];
        }
    }
    echo '</select></td></tr>';
    
    /* SELECCION DEL AREA */
    echo '<tr style="display: none;"><td><label>' . _('Por Área') . ':' . "</label></td>
        <td><select tabindex='4' name='xArea' id='xArea'>";

    $sql = "SELECT areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
            FROM areas 
            JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
          WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
          GROUP BY areas.areacode, areas.areadescription";
    
    $result=DB_query($sql, $db);
    
    echo "<option selected value='0'>Todas las areas...</option>";

    while ($myrow=DB_fetch_array($result)) {
        if ($myrow['areacode'] == $_POST['xArea']) {
            echo "<option selected value='" . $myrow["areacode"] . "'>" . $myrow['name'];
        } else {
            echo "<option value='" . $myrow['areacode'] . "'>" . $myrow['name'];
        }
    }
    echo '</select></td></tr>';

    /* SELECCION DEL DEPARTAMENT0 */
    echo '<tr style="display: none;"><td><label>' . _('Por Departamento') . ':' . "</label></td>
        <td><select tabindex='4' name='xDepto' id='xDepto'>";

    $sql = "SELECT department as departamento, departments.u_department
            FROM departments
            JOIN tags ON tags.u_department = departments.u_department
            JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
          WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
          GROUP BY departments.department";
    
    $result=DB_query($sql, $db);
    
    echo "<option selected value='0'>Todos los departamentos...</option>";

    while ($myrow=DB_fetch_array($result)) {
        if ($myrow['u_department'] == $_POST['xDepto']) {
            echo "<option selected value='" . $myrow["u_department"] . "'>" . $myrow['departamento'];
        } else {
            echo "<option value='" . $myrow['u_department'] . "'>" . $myrow['departamento'];
        }
    }
    echo '</select></td></tr>';
    /************************************/
    
    echo "</table>";
} else {
    if ($exportar == 'Excel') {
        header('Content-type: application/vnd.ms-excel; charset=UTF-8');
        header("Content-Disposition: attachment; filename=transacciones.xls");
        header('Pragma: no-cache');
        header("Expires: 0");
        echo "\xEF\xBB\xBF"; //UTF-8 BOM
    }

    if (empty($exportar)) {
        include('includes/header.inc');
        echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
        echo '<input type=hidden name="FromPeriod" VALUE="' . $_POST['FromPeriod'] . '"><input type=hidden name="ToPeriod" VALUE="' . $_POST['ToPeriod'] . '">';

        echo "<input type='hidden' name='tag' value='".$_POST['tag']."'>";
        echo "<input type='hidden' name='ue' value='".$_POST['ue']."'>";
        echo "<input type='hidden' name='xRegion' value='".$_POST['xRegion']."'>";
        echo "<input type='hidden' name='xArea' value='".$_POST['xArea']."'>";
        echo "<input type='hidden' name='xDepto' value='".$_POST['xDepto']."'>";
        echo "<input type='hidden' name='legalid' value='".$_POST['legalid']."'>";
        echo "<input type='hidden' name='FromPeriod' value='".$_POST['FromPeriod']."'>";
        echo "<input type='hidden' name='ToPeriod' value='".$_POST['ToPeriod']."'>";
        echo "<input type='hidden' name='poliza' value='".$_POST['poliza']."'>";

        echo '<select name="ue[]" id="ue" class="ue" multiple="multiple" style="display:none">';
        $SQL = "SELECT tce.ue,tce.desc_ue as uedescription";
        $SQL = $SQL .   " FROM sec_unegsxuser u";
        $SQL = $SQL .   " INNER JOIN tags t on (u.tagref = t.tagref) ";
        $SQL = $SQL .   " INNER JOIN tb_cat_unidades_ejecutoras tce on  (tce.ur = t.tagref) ";
        $SQL = $SQL .   " WHERE tce.active = 1 ";
        $SQL = $SQL .   " and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY tce.ue, uedescription ASC";
            
        $result=DB_query($SQL, $db);
        
        $cbmPostUE = $_POST['ue'];

        while ($myrow=DB_fetch_array($result)) {
            $selected="";
            if (!empty($cbmPostUE)) {
                foreach ($cbmPostUE as $key => $value) {
                    if ($value != -1) {
                        if ($myrow['ue'] == $value) {
                            $selected="selected";
                            break;
                        }
                    }
                }
            }
            echo "<option ".$selected." value='" . $myrow['ue'] . "'>" .$myrow['ue']." ". $myrow['uedescription'] . "</option>";
        }
        echo '</select>';

        echo "<select name='cbotipopoliza[]' id='cbotipopoliza' class='cbotipopoliza' style='font-size:8pt;display:none;' data-todos='true' multiple='true'>";
        $SQL = "SELECT id, ln_nombre FROM tb_cat_poliza_visual WHERE nu_activo = 1 ORDER BY ln_nombre ASC";
        
        $ErrMsg = _('No se obtuvieron los tipos de póliza visual');
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $cbotipopoliza = $_POST['cbotipopoliza'];

        while ($myrow=DB_fetch_array($TransResult)) {
            $selected="";
            if (!empty($cbotipopoliza)) {
                foreach ($cbotipopoliza as $key => $value) {
                    if ($value != -1) {
                        if ($myrow['id'] == $value) {
                            $selected="selected";
                            break;
                        }
                    }
                }
            }
            echo "<option ".$selected." value='" . $myrow['id'] . "'>" . $myrow['ln_nombre'] . "</option>";
        }

        echo "</select>";
    }

    $NumberOfMonths = 1;

    $RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

    $sqlWhere = "";

    if (is_array($_POST['cbotipopoliza'])&&count($_POST['cbotipopoliza'])) {
        $sqlWhere = " AND gltrans.type IN (SELECT typeid FROM systypescat WHERE nu_poliza_visual in ('".implode("','", $_POST['cbotipopoliza'])."'))";
    }

    $QueryUE = "";

    if (is_array($_POST['ue'])&&count($_POST['ue'])) {
        $QueryUE = " AND ( gltrans.ln_ue = '".implode("' OR gltrans.ln_ue = '", $_POST['ue'])."' ) ";
    }
    
    $SQL = "
    SELECT 
    counterindex, 
    gltrans.typeno, 
    DATE_FORMAT(gltrans.trandate,'%Y/%m/%d') as trandate,
    periodno, 
    narrative, 
    SUM(gltrans.amount) as amount, 
    tag, 
    typename, 
    gltrans.type,
    case when chartmaster.accountname is null then concat(gltrans.account,' <b>Verifique configuracion de Cuenta Erronea</b>') else chartmaster.accountname  end  as accountname, 
    naturaleza, 
    nombremayor, 
    legalbusinessunit.legalname, 
    tags.tagname, 
    chartmaster.accountcode,
    gltrans.account,
    CASE WHEN gltrans.amount < 0 THEN 'Abono' ELSe 'Cargo' END as operacion,
    gltrans.ln_ue AS ue,
    tce.desc_ue AS textoUE,
    gltrans.nu_folio_ue as folioUe,
    tb_cat_poliza_visual.ln_nombre as polizaUe
    FROM gltrans 
    left join tb_cat_unidades_ejecutoras AS tce ON tce.ue = gltrans.ln_ue AND tce.ur = gltrans.tag
    left join chartmaster on gltrans.account = chartmaster.accountcode
    left join chartTipos on chartmaster.tipo = chartTipos.tipo , 
    systypescat 
    LEFT JOIN tb_cat_poliza_visual ON tb_cat_poliza_visual.id = systypescat.nu_poliza_visual,
    sec_unegsxuser 
    join tags ON sec_unegsxuser.tagref = tags.tagref
    join legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
    WHERE (gltrans.tag = '".$_POST['tag']."' or '0' = '".$_POST['tag']."') $QueryUE AND 
    gltrans.tag = sec_unegsxuser.tagref and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' and
    gltrans.trandate >= '".fechaParaSQL('/', '-', $_POST['FromPeriod'])."' and
    gltrans.trandate <= '".fechaParaSQL('/', '-', $_POST['ToPeriod'])."' and
    gltrans.type = systypescat.typeid 
    and (gltrans.typeno =  '". $_POST['poliza'] ."' OR '*' = '". $_POST['poliza'] ."')
    ".$sqlWhere."
    GROUP BY type, typeno, operacion, account, periodno
    ";
    // and (gltrans.type =  ". $_POST['cbotipopoliza'] ." OR '-1' = '". $_POST['cbotipopoliza'] ."')
    // print_r($SQL);
    $orden=' order by ';
    if (!isset($_GET['orden'])) {
            $orden.=' gltrans.trandate,counterindex, type, gltrans.typeno';
    }
    if (isset($_GET['orden'])) {
        switch ($_GET['orden']) {
            case "type":
                $orden.=' type, gltrans.trandate,  gltrans.typeno';
                break;
            case "Folio":
                $orden.=' gltrans.typeno, gltrans.trandate, counterindex, type';
                break;
            case "indice":
                $orden.=' counterindex, gltrans.trandate,counterindex, type, gltrans.typeno';
                break;
            case "lasttrandate":
                $orden.=' gltrans.trandate,gltrans.lasttrandate,counterindex, type, gltrans.typeno';
                break;
            case "naturaleza":
                $orden.=' naturaleza, gltrans.trandate,counterindex, type, gltrans.typeno';
                break;
            case "accountname":
                $orden.=' accountname, gltrans.trandate,counterindex, type, gltrans.typeno';
                break;
            case "tagname":
                 $orden.=' tags.tagname, gltrans.trandate,counterindex, type, gltrans.typeno';
                break;
            case "narrative":
                 $orden.=' narrative, type, gltrans.typeno,gltrans.trandate, counterindex';
                break;
            default:
                    $orden.=' gltrans.trandate,counterindex, type, gltrans.typeno';
                break;
        }
    }
     // echo "<pre>".$SQL."</pre>";
     // exit();
    $SQL.=$orden;

    //echo '<pre>';var_dump($SQL);
    if ($_SESSION['UserID'] == "desarrollo") {
        // echo '<br>';
        // echo '<pre>'.$SQL;
        // echo '<br>';
    }

    $AccountsResult = DB_query(
        $SQL,
        $db,
        _('No fueron entregados registros del SQL por'),
        _('El SQL que fallo fue el siguiente:')
    );
    //Despliega razon social
    $SQL = "SELECT legalbusinessunit.legalname";
    $SQL = $SQL .   " FROM legalbusinessunit ";
    $SQL = $SQL .   " WHERE legalid = '" . $_POST['tag'] . "'";
    $result=DB_query($SQL, $db);
    if ($myrow=DB_fetch_array($result)) {
        echo '<div class="centre"><font size=4 color=BLUE><b>' . $myrow['legalname'];
        echo '</b></font></div><br>';
    }

    if ($_POST['xDepto'] != 0) {
        ///Pinta las unidades de negocio por usuario
        $SQL = "SELECT departments.department";
        $SQL = $SQL .   " FROM departments";
        $SQL = $SQL .   " WHERE u_department = ". $_POST['xDepto'];

        $result=DB_query($SQL, $db);
        if ($myrow=DB_fetch_array($result)) {
            echo '<div class="centre"><font size=4 color=BLUE><b>' . $myrow['department'];
            echo '</b></font></div><br>';
        }
    } else {
        // echo '<div class="centre"><font size=4 color=BLUE><b>TODOS LOS DEPARTAMENTOS QUE TENGO ACCESO...';
        // echo '</b></font></div><br>';
    }

    if ($_POST['tag'] != 0) {
        ///Pinta las unidades de negocio por usuario
        $SQL = "SELECT t.tagref,t.tagdescription";
        $SQL = $SQL .   " FROM sec_unegsxuser u,tags t ";
        $SQL = $SQL .   " WHERE u.tagref = t.tagref ";
        $SQL = $SQL .   " and u.userid = '" . $_SESSION['UserID'] . "' and t.tagref = " . $_POST['tag'];
        $SQL = $SQL .   " ORDER BY t.tagref";

        $result=DB_query($SQL, $db);
        if ($myrow=DB_fetch_array($result)) {
            echo '<div class="centre"><font size=4 color=BLUE><b>' . $myrow['tagdescription'];
            echo '</b></font></div><br>';
        }
    } else {
        // echo '<div class="centre"><font size=4 color=BLUE><b>TODAS LAS UNIDADES DE NEGOCIOS PARA ESTE USUARIO';
        // echo '</b></font></div><br>';
    }

    echo '<div class="centre"><font size=3 color=BLUE><b>'. _('Reporte desde: ') . $_POST['FromPeriod'] . _(' hasta: ') . $_POST['ToPeriod'] .'</b></font></div><br>';

    if (empty($exportar)) {
        // echo "<br>";
        echo "<div style='text-align:center;'>";
        echo "<input class='botonVerde' type='submit' name='Excel' value='Exportar a Excel'>";
        echo "&nbsp;&nbsp;";
        //echo "<input type='submit' name='CSV' value='Exportar en CSV'>";
        echo "</div>";
        echo "<br>";
    }

    //echo $SQL;

    //muetra la tabla con los resultados obtenidos por la consulta SQL
    $resto="";
    if (isset($_POST['tag'])) {
        $resto.="&tag=".$_POST['tag'];
    }
    if (isset($_POST['ue'])) {
        $resto.="&ue=".$_POST['ue'];
    }
    if (isset($_POST['xRegion'])) {
        $resto.="&xRegion=".$_POST['xRegion'];
    }
    if (isset($_POST['xArea'])) {
        $resto.="&xArea=".$_POST['xArea'];
    }
    if (isset($_POST['xDepto'])) {
        $resto.="&xDepto=".$_POST['xDepto'];
    }
    if (isset($_POST['legalid'])) {
        $resto.="&legalid=".$_POST['legalid'];
    }
    if (isset($_POST['FromPeriod'])) {
        $resto.="&FromPeriod=".$_POST['FromPeriod'];
    }
    if (isset($_POST['ToPeriod'])) {
        $resto.="&ToPeriod=".$_POST['ToPeriod'];
    }
    if (isset($_POST['cbotipopoliza'])) {
        $resto.="&cbotipopoliza=".$_POST['cbotipopoliza'];
    }
    if (isset($_POST['cbotipopoliza'])) {
        $resto.="&cbotipopoliza=".$_POST['cbotipopoliza'];
    }
    if (isset($_POST['poliza'])) {
        $resto.="&poliza=".$_POST['poliza'];
    }

    echo '<table class="table table-striped table-bordered" >';
    
    if (empty($exportar)) {
        $TableHeader = "<tr class='tableHeaderVerde'>
        <th class='text-center'>". _('Fecha') . "</th>
        <th class='text-center'>". _('UR') . "</th>
        <th class='text-center'>". _('UE') . "</th>
        <th class='text-center'>". _('Indice') . "</th>
        <th class='text-center'>". _('Póliza No.') . "</th>
        <th class='text-center'>". _('Tipo Póliza') . "</th>
        <th class='text-center'>". _('No. Operación') . "</th>
        <th class='text-center' >". _('Tipo Operación') . "</th>
        <th class='text-center'>". _('Cuenta Contable') . "</th>
        <th class='text-center'>". _('Cargos') . "</th>
        <th class='text-center'>". _('Abonos') . "</th>
        <th class='text-center'>". _('Concepto') . "</th>
        </tr>";
    } else {
        $TableHeader = "<tr class='tableHeaderVerde'>
        <th class='text-center'>". _('Fecha') . "</th>
        <th class='text-center'>". _('UR') . "</th>
        <th class='text-center'>". _('UE') . "</th>
        <th class='text-center'>". _('Indice') . "</th>
        <th class='text-center'>". _('Póliza No.') . "</th>
        <th class='text-center'>". _('Tipo Póliza') . "</th>
        <th class='text-center'>". _('No. Operación') . "</th>
        <th class='text-center' >". _('Tipo Operación') . "</th>
        <th class='text-center'>". _('Cuenta Contable') . "</th>
        <th class='text-center'>". _('Cargos') . "</th>
        <th class='text-center'>". _('Abonos') . "</th>
        <th class='text-center'>". _('Concepto') . "</th>
        </tr>";
    }

    $j = 1;
    $k = 0;

    echo $TableHeader;

    $AcumBalancePerFolio = 0;

    $AcumCargosPerFolio = 0;
    $AcumAbonosPerFolio = 0;
    $TAcumCargosPerFolio = 0;
    $TAcumAbonosPerFolio = 0;

    $UNFolioAnterior = '';

    $TotalBalance = 0;
    $naturaleza1=array();
    $cargos1=array();
    $abonos1=array();
    $cargo1='';
    $abono1='';
    while ($myrow=DB_fetch_array($AccountsResult)) {
        if ($myrow['tag'].'/'.$myrow['typeno'] != $UNFolioAnterior and $j > 1) {
            echo '<tr>';

            /*DESPLIEGA VALOR DE SUBTOTAL X UN Y FOLIO*/
            printf(
                '<td style="text-align: right;" colspan="9" >%s</td>
                <td class="pequenum"><b>%s</b></td>
                <td class="pequenum"><b>%s</b></td>
                <td class="pequenum">%s</td>
                </tr>',
                'Total',
                number_format($AcumCargosPerFolio, 2),
                number_format($AcumAbonosPerFolio, 2),
                ''
            );

            /*INICIALIZA SUBTOTAL Y COMIENZA A CONTAR PARA NUEVA COMBINACION*/
            $AcumBalancePerFolio = 0;
            $AcumCargosPerFolio = 0;
            $AcumAbonosPerFolio = 0;
        }

        if (strpos($myrow['accountname'], 'Verifique configuracion de Cuenta Erronea')==true) {
            //if($myrow['accountname']=='Verifique configuracion de Cuenta Erronea'){
            $bgcolor='bgcolor=red';
        } else {
            $bgcolor='';
        }

        echo '<tr '.$bgcolor.'>';

        if ($myrow['type'] == '259') {
            // Si es compromiso agregar información
            $SQL = "SELECT
            DISTINCT
            CONCAT(tb_compromiso.nu_tipo, '-', tb_tipo_compromiso_cat.sn_nombre) as tipoCompromiso,
            tb_compromiso.nu_id_compromiso
            FROM gltrans
            JOIN tb_compromiso ON tb_compromiso.nu_type = gltrans.type AND tb_compromiso.nu_transno = gltrans.typeno
            JOIN tb_tipo_compromiso_cat ON tb_tipo_compromiso_cat.nu_tipo = tb_compromiso.nu_tipo
            WHERE
            gltrans.type = '".$myrow['type']."'
            AND gltrans.typeno = '".$myrow['typeno']."'";
            $ErrMsg = "No se obtuvo información del compromiso";
            $resultCompromiso = DB_query($SQL, $db, $ErrMsg);
            $myrowCompromiso = DB_fetch_array($resultCompromiso);
            $myrow['typename'] = $myrow['typename'].' '.$myrowCompromiso['tipoCompromiso'].' '.$myrowCompromiso['nu_id_compromiso'];
        }

        if ($myrow['amount'] >= 0) {
            printf(
                '<td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="pequenum">%s</td>
                <td '.$bgcolor.' class="peque" >%s</td>
                <td class="pequenum">%s</td>
                <td class="pequenum">%s</td>
                <td class="peque" >%s </td>
                </tr>',
                $myrow['trandate'],
                $myrow['tag'].'-'.$myrow['tagname'],
                $myrow['ue'].'-'.$myrow['textoUE'],
                $myrow['counterindex'],
                $myrow['folioUe'],
                $myrow['polizaUe'],
                $myrow['typeno'],
                $myrow['typename'],
                $myrow['accountcode'].' - '.$myrow['accountname'],
                number_format($myrow['amount'], 2),
                '',
                $myrow['narrative']
            ); //    $myrow['naturaleza'],
        } else {
            printf(
                '<td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="peque" >%s</td>
                <td class="pequenum">%s</td>
                <td '.$bgcolor.'  class="peque" >%s</td>
                <td class="pequenum">%s</td>
                <td class="pequenum">%s</td>
                <td class="peque" >%s </td>
                </tr>',
                $myrow['trandate'],
                $myrow['tag'].'-'.$myrow['tagname'],
                $myrow['ue'].'-'.$myrow['textoUE'],
                $myrow['counterindex'],
                $myrow['folioUe'],
                $myrow['polizaUe'],
                $myrow['typeno'],
                $myrow['typename'],
                $myrow['accountcode'].' - '.$myrow['accountname'],
                '',
                number_format($myrow['amount']*-1, 2),
                $myrow['narrative']
            ); // $myrow['naturaleza'],
        }
        $TotalBalance = $TotalBalance + $myrow['amount'];
        $AcumBalancePerFolio = $AcumBalancePerFolio + $myrow['amount'];

        if ($myrow['amount'] >= 0) {
            $AcumCargosPerFolio = $AcumCargosPerFolio + $myrow['amount'];
            $TAcumCargosPerFolio = $TAcumCargosPerFolio + $myrow['amount'];
        } else {
            $AcumAbonosPerFolio = $AcumAbonosPerFolio  + $myrow['amount']*-1;
            $TAcumAbonosPerFolio = $TAcumAbonosPerFolio  + $myrow['amount']*-1;
        }

        $UNFolioAnterior = $myrow['tag'].'/'.$myrow['typeno'];

        $j++;
    }

    echo '<tr class="EvenTableRows">';

    /*DESPLIEGA VALOR DE SUBTOTAL X UN Y FOLIO*/
    printf(
        '<td style="text-align: right;" colspan="9">%s</td>
        <td class="pequenum"><b>%s</b></td>
        <td class="pequenum"><b>%s</b></td>
        <td class="pequenum">%s</td>
        </tr>',
        'Total',
        number_format($AcumCargosPerFolio, 2),
        number_format($AcumAbonosPerFolio, 2),
        ''
    );

    printf(
        '<tr class="tableHeaderVerde">
        <th style="text-align: right;" colspan="9"><font color=WHITE><b>' . _('TOTALES') . '</b></font></th>
        <th class=number>%s</th>
        <th class=number>%s</th>
        <th class=number></th>
        </tr>',
        number_format($TAcumCargosPerFolio, 2),
        number_format($TAcumAbonosPerFolio, 2)
    );

    echo '</table>';

    if (empty($exportar)) {
        echo '<div align="center">
        <button class="botonVerde glyphicon glyphicon-search" type=submit Name="SelectADifferentPeriod" >Cambiar filtros</button></div>';
    }
}

if (empty($exportar)) {
    echo '</form>';
    include('includes/footer_Index.inc');
}
ob_end_flush();
?>