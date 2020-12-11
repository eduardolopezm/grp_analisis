<?php
/**
 * Reporte Invetarios
 *
 * @category Reporte
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Reporte de Inventarios con diferente nivel de detalle
 */

//Cambios para demo

$PageSecurity = 8;
include('includes/session.inc');
$funcion=703;
$title = traeNombreFuncion($funcion, $db);
include "includes/SecurityUrl.php";
//include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$showalltags            = Havepermission($_SESSION['UserID'], 1158, $db);
$permisoProcObsoleto    = Havepermission($_SESSION['UserID'], 1742, $db);
$PermisoColumCantPiezas = Havepermission($_SESSION['UserID'], 1832, $db); //permiso columna Compras Autorizadas Y COMPRAS PENDIENTES PÒR RECIBIR EN COMPRAS
$permiso_traspaso = Havepermission($_SESSION['UserID'], 1732, $db); //Permiso para datos serializadso vitallantas
$partidaE = "";
/* partidas */
$datos='';

$clavesProd= array();
$descripcionProd=array();
$partidasArt= array();
$sqlAutoCompletar="SELECT  stockid,description,tb_partida_articulo.partidaEspecifica FROM stockmaster 
INNER JOIN tb_partida_articulo  ON  stockmaster.eq_stockid =  tb_partida_articulo.eq_stockid
WHERE  
stockmaster.mbflag='B'";
$respuesta= DB_query($sqlAutoCompletar, $db);

while ( $myrow = DB_fetch_array($respuesta) ){
       
       $clavesProd[]=$myrow['stockid'];
       $descripcionProd[]=$myrow['description'];
       $partidasArt[]=$myrow['partidaEspecifica'];
}

/*fin partidas*/
/*if (isset($_POST ['FromYear'])) {
    $FromYear = $_POST ['FromYear'];
} else {
    $FromYear = date('Y');
}

if (isset($_POST ['FromMes'])) {
    $FromMes = $_POST ['FromMes'];
} else {
    $FromMes = date('m');
}

if (isset($_POST ['FromDia'])) {
    $FromDia = $_POST ['FromDia'];
} else {
    $FromDia = date('d');
}*/

if (isset($_POST['dateDesde'])) {
//$fechaini=$_POST['dateDesde'];
    $fechaini= date("Y-m-d", strtotime($_POST['dateDesde']));
} else {
// $fechaini= date("d-m-Y");
    $fechaini= date("Y-m-d");
}

if (isset($_POST['fechaHasta'])) {
    $fechaFin = date("Y-m-d", strtotime($_POST['fechaHasta']));
} else {
    $fechaFin = date("Y-m-d");
}

if (isset($_POST ['ToYear'])) {
    $ToYear = $_POST ['ToYear'];
} else {
    $ToYear = date('Y');
}

if (isset($_POST ['ToMes'])) {
    $ToMes = $_POST ['ToMes'];
} else {
    $ToMes = date('m');
}
if (isset($_POST ['ToDia'])) {
    $ToDia = $_POST ['ToDia'];
} else {
    $ToDia = date('d');
}

if (!isset($_POST['xGrupo'])) {
    $_POST['xGrupo'] = 0;
}

if (!isset($_POST['AlGrupo'])) {
    $_POST['AlGrupo'] = 0;
}

if (!isset($_POST['xLinea'])) {
    $_POST['xLinea'] = 0;
}

if (!isset($_POST['xCategoria'])) {
    $_POST['xCategoria'] = 0;
}

if (!isset($_POST['razonsocial'])) {
    $_POST['razonsocial'] = 0;
}

if (!isset($_POST['xRegion'])) {
    $_POST['xRegion'] = 0;
}

if (!isset($_POST['xArea'])) {
    $_POST['xArea'] = 0;
}

if (!isset($_POST['unidadnegocio'])) {
    $_POST['unidadnegocio'] = '0';
}

if (!isset($_POST['xDepto'])) {
    $_POST['xDepto'] = 0;
}

if (!isset($_POST['almacen'])) {
    $_POST['almacen'] = 0;
}

if (!isset($_POST['stocktypeflag'])) {
    $_POST['stocktypeflag'] = array();
}

if (!isset($_POST['xOptimo'])) {
    $_POST['xOptimo'] = "";
}

if (!isset($_POST['costocero'])) {
    $_POST['costocero'] = "";
}
if (!isset($_POST['numreq'])) {
    $_POST['numreq'] ='';
}

/*$fechaini = rtrim($FromYear) . '-' . str_pad(rtrim($FromMes), 2, "0", STR_PAD_LEFT) . '-' . str_pad(rtrim($FromDia), 2, "0", STR_PAD_LEFT); */

// valores para metodos de costeo
$costeoxrazonsocial   = 1;
$costeoxunidadnegocio = 2;
$costeogeneral        = 3;
$costeoxserie         = 4;
$Linkdocto            = '<a target="_blank" href="ABC_TypesbyReport.php?functionid=' . $funcion . '">';

//if ($_SESSION['AgregaLocalidad'] == 1) {
//}
echo '<link rel="stylesheet" href="css/listabusqueda.css" />';
if (!isset($_POST['PrintEXCEL'])) {
    $title = _('Reporte General de Estado de Existencias');
    include 'includes/header.inc';
    /*echo '<p class="page_title_text">' . $Linkdocto . '<img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' <h2>' . $title . '</h2><br></a>'; */
    echo '<div class="panel panel-default"><!-- Datos de la empresa -->
    <div role="tab" id="headingOne" class="panel-heading text-left">
        <h4 class="panel-title row">
            <div class="col-md-3 col-xs-3">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelInformacionEmpresa" aria-expanded="false" aria-controls="collapse" class="collapsed">
                   <b>Criterios de filtrado</b>
                </a>
            </div>
        </h4>
    </div>
    <div id="PanelInformacionEmpresa" name="PanelInformacionEmpresa" role="tabpanel" aria-labelledby="headingOne" class="panel-collapse collapse in"><br>

        <div class="text-left container">';

    echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";
    echo '<fieldset class="cssfieldset" style="margin:auto; color:#000" >'; //color:#28139a; border:1px solid #c9ccc9"

    //inicio
    echo '<div class="row">';

    // Col 1
    echo '<div class="col-xs-12 col-md-4">';
    echo '<div class="form-inline row" style="display: none;">
              <div class="col-md-3">
                  <span><label>Dependencia: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="razonsocial" name="razonsocial" class="form-control razonsocial">';
    if ($showalltags == 0) {
        $SQL = "SELECT legalbusinessunit.legalid,
                                 legalbusinessunit.legalname
                          FROM sec_unegsxuser u,
                               tags t
                          JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
                          WHERE u.tagref = t.tagref
                            AND u.userid = '" . $_SESSION['UserID'] . "'
                          GROUP BY legalbusinessunit.legalid,
                                   legalbusinessunit.legalname
                          ORDER BY t.tagref";
    } else {
        $SQL = "SELECT legalbusinessunit.legalid,
                                 legalbusinessunit.legalname
                          FROM legalbusinessunit
                          ORDER BY legalbusinessunit.legalid,
                                   legalbusinessunit.legalname";
    }
          $ErrMsg      = _('No transactions were returned by the SQL because');
          $TransResult = DB_query($SQL, $db, $ErrMsg);
          echo '<option selected value="0">Seleccionar Todos</option>';
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($myrow['legalid'] == $_POST['razonsocial']) {
            echo '<option selected value="' . $myrow['legalid'] . '">' . $myrow['legalname'] . '</option>';
        } else {
            echo '<option value="' . $myrow['legalid'] . '">' . $myrow['legalname'] . '</option>';
        }
    }

    echo '</select>
              </div>
          </div>';
    //echo '<br>';
    echo '<div class="form-inline row">
              <div class="col-md-3">
                  <span><label>UR: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="unidadnegocio" name="unidadnegocio" class="form-control unidadnegocio" onchange="fnCambioUnidadResponsableGeneral(\'unidadnegocio\',\'selectUnidadEjecutora\')">';
    echo "<option selected value='0'>Seleccionar Todos</option>";
    $SQL = "SELECT t.tagref, CONCAT(t.tagref,' - ',t.tagdescription) AS tagdescription2, t.tagdescription
                FROM sec_unegsxuser u, tags t
                LEFT JOIN areas ON t.areacode = areas.areacode
                WHERE u.tagref = t.tagref AND u.userid = '" . $_SESSION['UserID'] . "'
                ORDER BY t.tagdescription, areas.areacode";
    $ErrMsg      = _('No transactions were returned by the SQL because');
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $selUnRegistro = '';
    if (DB_num_rows($TransResult)==1) {
        $selUnRegistro = 'selected';
    }
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($myrow['tagref'] == $_POST['unidadnegocio']) {
            echo "<option selected value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription2'] . "</option>";
        } else {
            echo "<option ".$selUnRegistro." value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription2'] . "</option>";
        }
    }
    echo '</select>
              </div>
          </div>';
    echo '<br>';
    echo '<div class="form-inline row">
              <div class="col-md-3" style="vertical-align: middle;">
                  <span><label>UE: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control UnidadEjecutora"> 
                  <option selected value="-1">Seleccionar Todos</option>
                  ';
    $condicion = "";
    if (trim($_POST['unidadnegocio']) != '0' && trim($_POST['unidadnegocio']) != '') {
        $condicion = " AND t.tagref = '".$_POST['unidadnegocio']."'";
    }

    $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription,  tce.ue as ue, CONCAT(tce.ue, ' - ', tce.desc_ue) as uedescription 
    FROM sec_unegsxuser u
    INNER JOIN tags t on (u.tagref = t.tagref)
    INNER JOIN tb_cat_unidades_ejecutoras tce on  (tce.ur = t.tagref)
    WHERE tce.active = 1 and u.userid = '" . $_SESSION['UserID'] . "' ".$condicion."
    ORDER BY t.tagref, tce.ue ASC";

    $ErrMsg      = _('No transactions were returned by the SQL because');
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $selUnRegistro = '';
    if (DB_num_rows($TransResult)==1) {
        $selUnRegistro = 'selected';
    }
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($myrow['ue'] == $_POST['selectUnidadEjecutora']) {
            echo "<option selected value='" . $myrow['ue'] . "'>" . $myrow['uedescription'] . "</option>";
        } else {
            echo "<option ".$selUnRegistro." value='" . $myrow['ue'] . "'>" . $myrow['uedescription'] . "</option>";
        }
    }
    echo '        </select>
              </div>
          </div>';
          echo '<br>';
          echo '<div class="form-inline row">
                    <div class="col-md-3">
                        <span><label>Solo con Existencias: </label></span>
                    </div>
                    <div class="col-md-9">
                        <select id="SoloExistencias" name="SoloExistencias" class="form-control SoloExistencias">';
          echo '<option value="0" '.($_POST['SoloExistencias'] == '0' ? 'selected' : '').'>Todas las Existencias</option>';
          echo '<option value="1" '.($_POST['SoloExistencias'] == '1' ? 'selected' : '').'>Con Existencias</option>';
          echo '<option value="2" '.($_POST['SoloExistencias'] == '2' ? 'selected' : '').'>Sin Existencias</option>';
          echo '</select>
                    </div>
                </div>';
    echo '</div>';
    // Col 1

    // Col 2
    echo '<div class="col-xs-12 col-md-4">';
    if (!isset($_POST['claveprod'])) {
       // $_POST['claveprod'] = '*';
    }
    // echo '<div class="form-inline row">
    //       <div class="col-md-3 col-xs-12">
    //       <span><label>Clave producto: </label></span>
    //       </div>
    //        <div class="col-md-9 col-xs-12"><input type="text" id="claveprod" name="claveprod" placeholder="Clave producto" title="" onkeyup="" onkeypress="" maxlength="100" onpaste="return false" class="form-control" style="width: 100%;">
    //        </div>
    //      </div>';
     

    echo ' <div class="form-inline row" style="text-align: left;">
                                <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                                    <span><label>Partida Especifica: </label></span>
                                </div>
                                <div class="col-xs-9 col-md-9">
                                    <select id="selPartida" name="selPartida" class="selPartida form-control">;
                                    </select>
                                </div>
                            </div><br>';

      echo '<div class="form-inline row">
          <div class="col-md-3 col-xs-12">
          <span><label>Clave producto: </label></span>
          </div>
           <div class="col-md-9 col-xs-12">
           <input type="text"  id="buscarProd"  placeholder="Clave producto" class="form-control" style="width:100%" />
        <input type="hidden" name="claveprod" id="claveprod" value="">
        <div id="sugerencia-articulo" style="position:absolute; z-index:999; display:block;"></div> 
           </div>
         </div>';
    
    // multiselect  de producto
    // echo '<div class="form-inline row">
    //           <div class="col-md-3">
    //               <span><label>Clave de Artículo: </label></span>
    //           </div>
    //           <div class="col-md-9">
    //               <select id="claveprod" name="claveprod" class="form-control claveprod">';
    // echo '<option value="*" selected>Seleccione clave...</option>';
    // $SQLStockMaster = "SELECT DISTINCT eq_stockid, stockid FROM stockmaster WHERE mbflag IN ('B', 'D')";
    // $ErrMsgStockMaster  = _('Error en la consulta de los productos');
    // $TransResultStockMaster = DB_query($SQLStockMaster, $db, $ErrMsgStockMaster);

    // while ($myrowStockMaster = DB_fetch_array($TransResultStockMaster)) {
    //     if ($_POST["claveprod"] == $myrowStockMaster["stockid"]) {
    //         echo "<option value='" . $myrowStockMaster['stockid'] . "' selected>" . $myrowStockMaster['stockid'] . "</option>";
    //     } else {
    //         echo "<option value='" . $myrowStockMaster['stockid'] . "'>" . $myrowStockMaster['stockid'] . "</option>";
    //     }
    // }
    // echo '</select>
    //           </div>
    //       </div>';
    echo '<br>';
    /*
    echo '<component-number-label label="Número de Requisición:" id="numreq" name="numreq" value="'.$_POST['numreq'].'"  placeholder="" maxlength="50"></component-number-label>'; */
    echo '<br>';
    // if ((isset($_POST['SoloExistencias'])) and ($_POST['SoloExistencias'] != "")) {
    //     echo "<input type='checkbox' name='SoloExistencias' value='1' checked>";
    // } else {
    //     echo "<input type='checkbox' name='SoloExistencias' value='1'>";
    // }
    echo '</div>';
    // Col 2

    // Col 3
    echo '<div class="col-xs-12 col-md-4">';
    // echo '<component-date-label label="Desde: " id="dateDesde" name="dateDesde" placeholder="Desde Fecha" title="Desde Fecha" value='.(date("d-m-Y", strtotime($fechaini))).'"></component-date-label>';
    //echo '<br>';
    echo '<component-date-label label="Hasta: " id="fechaHasta" name="fechaHasta" placeholder="Hasta Fecha" title="Hasta Fecha" value='.(date("d-m-Y", strtotime($fechaFin))).'"></component-date-label>';
    echo '<br>';
    echo '<div class="form-inline row" style="display: none;">
              <div class="col-md-3">
                  <span><label>Detalle: </label></span>
              </div>
              <div class="col-md-9">
                  <select id="DetailedReport" name="DetailedReport" class="form-control DetailedReport">';
    // if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'No') {
    //     echo "<option selected value='No'>" . _('Por Dependencia');
    // } else {
    //     echo "<option value='No'>" . _('Por Dependencia');
    // }

    // if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Yes') {
    //     echo "<option selected value='Yes'>" . _('Por Unidad Responsanble');
    // } else {
    //     echo "<option value='Yes'>" . _('Por Unidad Responsanble');
    // }

    //if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Cat') {
      //  echo "<option selected value='Cat'>" . _('Por Categoría de Producto');
    //} else {
      //  echo "<option value='Cat'>" . _('Por Categoría de Producto');
    //}

    if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'ProdSum') {
        echo "<option selected value='ProdSum'>" . _('Por Clave de Producto');
    } else {
        echo "<option value='ProdSum'>" . _('Por Clave de Producto');
    }
    echo '</select>
              </div>
          </div>';
    echo '</div>';
    // Col 3
    
    echo '<div class="row"></div>';

    // Filtros ocultos
    echo '<div class="col-xs-12 col-md-6" style="display: none">';
    echo '<select id="stocktypeflag"  name="stocktypeflag[]" class="stocktypeflag">';
    $SQL = "SELECT *
            FROM stocktypeflag
            ORDER BY orden";

    $TransResult = DB_query($SQL, $db);

    if (in_array('0', $_POST['stocktypeflag'])) {
        echo "<option selected='selected' value='0'>Todos los tipos</option>";
    } else {
        echo "<option value='0'>Todos los tipos...</option>";
    }

    while ($myrow = DB_fetch_array($TransResult)) {
        if (in_array($myrow['stockflag'], $_POST['stocktypeflag'])) {
            echo "<option selected value='" . $myrow['stockflag'] . "'>" . $myrow['stocknameflag'] . "</option>";
        } else {
            echo "<option value='" . $myrow['stockflag'] . "'>" . $myrow['stocknameflag'] . "</option>";
        }
    }
    echo "</select>";

    echo '<select id="almacen" tabindex="13" name="almacen" class="almacen">';
    if ($showalltags == 0) {
        $SQL = "SELECT l.loccode,
                       l.locationname
                FROM sec_loccxusser s,
                     locations l
                WHERE s.userid = '" . $_SESSION['UserID'] . "'
                  AND s.loccode = l.loccode";
    } else {
        $SQL = "SELECT l.loccode,
                       l.locationname
                FROM locations l
                ORDER BY l.loccode,
                         l.locationname";
    }
    $ErrMsg      = _('No transactions were returned by the SQL because');
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    echo "<option selected value='0'>Todas a las que tengo accceso...</option>";
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($myrow['loccode'] === $_POST['almacen']) {
            echo "<option selected value='" . $myrow['loccode'] . "'>" . $myrow['locationname'] . "</option>";
        } else {
            echo "<option value='" . $myrow['loccode'] . "'>" . $myrow['locationname'] . "</option>";
        }
    }
    echo "</select>";

    echo '<select id="tipoinventario" tabindex="13" name="tipoinventario" class="tipoinventario">';
    $sqlXtipoInventario = "";
    if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'todos') {
        echo "<option selected value='todos'>" . _('Todos...');
        $sqlXtipoInventario = "";
    } else {
        echo "<option value='todos'>" . _('Todos');
    }
    if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'normales') {
        echo "<option selected value='normales'>" . _('almacen productos');
        $sqlXtipoInventario = " and locations.temploc = 0 ";
    } else {
        echo "<option value='normales'>" . _('almacen productos');
    }
    if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'servicios') {
        echo "<option selected value='servicios'>" . _('almacen servicios');
        $sqlXtipoInventario = " and locations.temploc = 1 ";
    } else {
        echo "<option value='servicios'>" . _('almacen servicios');
    }
    echo '</select>';

    echo '<select id="xOptimo" tabindex="13" name="xOptimo" class="xOptimo">';
    if ($_POST['xOptimo'] == "0") {
        echo '<option value="-1">' . _('Todos los productos');
        echo '<option selected value="0">' . _('Con Optimo');
        echo '<option value="1">' . _('Sin Optimo');
    } elseif ($_POST['xOptimo'] == "1") {
        echo '<option value="-1">' . _('Todos los productos');
        echo '<option value="0">' . _('Con Optimo');
        echo '<option selected value="1">' . _('Sin Optimo');
    } else {
        echo '<option value="-1" selected>' . _('Todos los productos');
        echo '<option value="0">' . _('Con Optimo');
        echo '<option value="1">' . _('Sin Optimo');
    }
    echo '</select>';

    echo "<div><div class='col-xs-12 col-md-6'><br><b>Costo Cero:</b></div><div class='col-xs-12 col-md-6 text-center'>";
    if ((isset($_POST['costocero'])) and ($_POST['costocero'] != "")) {
        echo "<input type='checkbox' name='costocero' value='1' checked>";
    } else {
        echo "<input type='checkbox' name='' value='1'>";
    }
    echo "</div></div>";
    echo "<div><div class='col-xs-12 col-md-6'><br><b>Existencias < Optimo :</b></div><div class='col-md-6 text-center'>";
    if ((isset($_POST['OptimoMenor'])) and ($_POST['OptimoMenor'] != "")) {
        echo "<input type='checkbox' name='OptimoMenor' value='1' checked>";
    } else {
        echo "<input type='checkbox' name='OptimoMenor' value='1'>";
    }
    echo "</div></div>";

    echo "</div>";
    // Filtros ocultos

    //************************//

    echo '<div class="col-xs-12 col-md-6" >';
    //col 1
    echo '<span style="display:none;"><b>PRODUCTOS</b><br></span>';
    echo   '<div >
              <div class="col-md-6" style="display:none;">
                  <b>Por Categoría: </b>
              </div> 
           <div class="col-md-6" style="display:none;">
                  <select id="xCategoria" tabindex="13" name="xCategoria" class="xCategoria">';

    $sql = 'SELECT sto.categoryid, IF(categorydescription="", "SIN DESCRIPCION", categorydescription) AS categorydescription
              FROM stockcategory sto, sec_stockcategory sec
              WHERE sto.categoryid=sec.categoryid AND userid="' . $_SESSION['UserID'] . '"
              ORDER BY categorydescription';

    $result = DB_query($sql, $db);

    echo "<option selected value='0'>Todas las categorías...</option>";

    while ($myrow = DB_fetch_array($result)) {
        if ($myrow['categoryid'] === $_POST['xCategoria']) {
            echo "<option selected value='" . $myrow["categoryid"] . "'>" . $myrow['categorydescription'];
        } else {
            echo "<option value='" . $myrow['categoryid'] . "'>" . $myrow['categorydescription'];
        }
    }
    echo '</select></div></div>';

    /**
    * *********************************
    */
    /* SELECCION DE REGION */
    echo '<div style="display:none;">' . _('Por Región') . ':' . "</div>
        <div style='display:none;'><select tabindex='4' name='xRegion'>";

    /*$sql    = "SELECT regioncode, CONCAT(regioncode,' - ',name) as name FROM regions";
    $result = DB_query($sql, $db); */

    echo "<option selected value='0'>Todas las regiones...</option>";

    /* while ($myrow = DB_fetch_array($result)) {
         if ($myrow['regioncode'] == $_POST['xRegion']) {
             echo "<option selected value='" . $myrow["regioncode"] . "'>" . $myrow['name'];
         } else {
             echo "<option value='" . $myrow['regioncode'] . "'>" . $myrow['name'];
         }
     }*/
    echo '</select></div>';
    /**
     * *********************************
     */
    /**
     * *********************************
     */
    /* SELECCION DE AREA */
    echo '<div style="display:none;">' . _('X Area') . ':' . "</div>
        <div style='display:none;'><select tabindex='4' name='xArea'>";
    /*$sql    = "SELECT areacode, CONCAT(areacode,' - ',areadescription) as name FROM areas";
    $result = DB_query($sql, $db);*/
    echo "<option selected value='0'>Todas las areas...</option>";
    /*while ($myrow = DB_fetch_array($result)) {
        if ($myrow['areacode'] == $_POST['xArea']) {
            echo "<option selected value='" . $myrow["areacode"] . "'>" . $myrow['name'];
        } else {
            echo "<option value='" . $myrow['areacode'] . "'>" . $myrow['name'];
        }
    }*/
    echo '</select></div>';
    /**
     * *********************************
     */
    /**
     * *********************************
     */
    /* SELECCION DE LA UNIDAD DE NEGOCIO DE PRODUCTOS */
    /*echo "<tr><td>" . _('X Unidad Responsable') . ":</td><td>";
    echo "<select name='unidadnegocio'>"; */

    /**
     * *********************************
     */
    /* SELECCION DE DEPARTAMENTO */
    echo '<div style="display:none;">' . _('X Departamento') . ':' . "</div>
        <div style='display:none;'><select tabindex='4' name='xDepto'>";
    /*$sql    = "SELECT u_department, CONCAT(u_department,' - ',department) as name FROM departments";
    $result = DB_query($sql, $db); */
    echo "<option selected value='0'>Todos los departamentos...</option>";
    /*while ($myrow = DB_fetch_array($result)) {
        if ($myrow['u_department'] == $_POST['xDepto']) {
            echo "<option selected value='" . $myrow["u_department"] . "'>" . $myrow['name'];
        } else {
            echo "<option value='" . $myrow['u_department'] . "'>" . $myrow['name'];
        }
    }*/
    echo '</select></div>';

    if (!isset($_POST['localidad']) or $_POST['localidad'] == '') {
        $_POST['localidad'] = '*';
    }
    if (isset($_POST['almacen'])) {
        $SQL = "SELECT DISTINCT locstock.localidad
                FROM locstock
                WHERE locstock.loccode = '" . $_POST['almacen'] . "'
                  AND locstock.localidad <> ''";
        $TransResult = DB_query($SQL, $db);

        echo '<div style="display:none;"> ' . _('Localidad') . '</div>';
        echo '<div style="display:none;"> <select name=localidad>';
        //if ($_POST['localidad'] == '*') {
        echo "<option selected value='*'>Todas las localidades</option>";
        //echo "<option value='+'>No tengan localidades</option>";
        /*} elseif ($_POST['localidad'] == '+') {
        echo "<option value='*'>Todas las localidades</option>";
        echo "<option selected value='+'>No tengan localidades</option>";
        } else {
        echo "<option value='*'>Todas las localidades</option>";
        echo "<option value='+'>No tengan localidades</option>";
        } */
        /*while ($myrow = DB_fetch_array($TransResult)) {
        if ($_POST['localidad'] == $myrow['localidad']) {
            echo "<option selected value='" . $myrow['localidad'] . "'>" . $myrow['localidad'] . "</option>";
        } else {
            echo "<option value='" . $myrow['localidad'] . "'>" . $myrow['localidad'] . "</option>";
        }
        }*/
        echo "</select></div>";
    }

    //col 1 fin
    echo'</div>';
    /*echo '<div class="col-xs-12 col-md-" style="background-color:lavenderblush;">dos</div>';*/


    echo '<div class="col-xs-12 col-md-6" >';

    //col 3
    if (!isset($_POST['txtLocalidad'])) {
        $_POST['txtLocalidad'] = '*';
    }

    echo '</div>';
    //col 3 fin

    echo '</div>';
    //fin row

    echo '<div class="col-xs-12 col-md-12 text-center">
    <div class="row">';
    echo "<div  class='col-md-12' style='text-align:center;color:#fff;'>";
    echo '<component-button type="submit" id="ReportePantalla" name="ReportePantalla" class="glyphicon glyphicon-search" value="Filtrar"></component-button>';
    echo "<span class='mx-auto' style='width: 50px;'> </span>";
    echo '<component-button type="submit" id="PrintEXCEL" name="PrintEXCEL" class="glyphicon glyphicon-th" value="Exportar a Excel"></component-button>';
    echo "</div>";
    echo '</div></div>';

    echo "</fieldset>";
    echo '</form>';
    echo '  </div><!--container-->
    </div><!--fin contenido -->
    </div><!-- fin panel datos -->';
} else {
    $sqlXtipoInventario = "";
    if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'todos') {
        $sqlXtipoInventario = "";
    }

    if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'normales') {
        $sqlXtipoInventario = " and locations.temploc = 0 ";
    }

    if (isset($_POST['tipoinventario']) and $_POST['tipoinventario'] == 'servicios') {
        $sqlXtipoInventario = " and locations.temploc = 1 ";
    }
}

if (isset($_POST['ReportePantalla']) or isset($_POST['PrintEXCEL'])) {
    if (isset($_POST['PrintEXCEL'])) {
        header("Content-type: application/ms-excel");
        // replace excelfile.xls with whatever you want the filename to default to
        header("Content-Disposition: attachment; filename=excelreport.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

        echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
        echo '<link rel="shortcut icon" href="' . $rootpath . '/favicon.ico" />';
        echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
    }

    $TypeWhereCond = "";
    if (empty($_POST['stocktypeflag']) == false) {
        if (in_array('0', $_POST['stocktypeflag']) == false) {
            $suppliers = array();
            foreach ($_POST['stocktypeflag'] as $supplierId) {
                $typesflag[] = "'$supplierId'";
            }
            $TypeWhereCond .= " and mbflag IN (" . implode(',', $typesflag) . ")";
            // $LegalidWhereCond2.= " legalid IN (" . implode(',', $typesflag). ")";
        }
    }

    //Localidad
    $sqlXLocalidad = 0;
    if (trim($_POST['txtLocalidad']) != '*') {
        $sqlXLocalidad = $_POST['txtLocalidad'];
    }

    //Codigo de producto
    $sqlXStockid = "";
    $sqlXStockidVentas = "";
    if (trim($_POST['claveprod']) != '*') {
        $sqlXStockid = " AND (stockmoves.stockid like '%".$_POST['claveprod']."%') ";
    }

    //Tipo de Producto
    $TypeWhereCond = "";
    if (empty($_POST['stocktypeflag']) == false) {
        if (in_array('0', $_POST['stocktypeflag']) == false) {
            $suppliers = array();
            foreach ($_POST['stocktypeflag'] as $supplierId) {
                $typesflag[] = "'$supplierId'";
            }
            $TypeWhereCond .= " AND mbflag IN (" . implode(',', $typesflag) . ") ";
            // $LegalidWhereCond2.= " legalid IN (" . implode(',', $typesflag). ")";
        }
    }

    //Por optimo definido
    $sqlXOptimoDefinido = "";
    if ($_POST['xOptimo'] == 0) {
        $sqlXOptimoDefinido =  " AND locstock.reorderlevel>0 ";
    } elseif ($_POST['xOptimo'] == 1) {
        $sqlXOptimoDefinido =  " AND locstock.reorderlevel=0 ";
    }

    //Solo existencias
    $sqlSoloExistencias = "";
    if ((isset($_POST['SoloExistencias'])) and ($_POST['SoloExistencias'] != "")) {
        if ($_POST['DetailedReport'] == 'No' or $_POST['DetailedReport'] == 'Grupo' or $_POST['DetailedReport'] == 'Linea'
            or $_POST['DetailedReport'] == 'Cat') {
            $sqlSoloExistencias=" HAVING (Existencias) <> 0 ";
        } elseif ($_POST['DetailedReport'] == 'Yes') {
            $sqlSoloExistencias=" HAVING (Existencias + EnTransito) <> 0 ";
        } elseif ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum') {
            $sqlSoloExistencias=" HAVING (Existencias) <> 0 ";
        }
    }

    //Costo cero
    $sqlXCostoCero = "";
    if ($_POST['costocero'] != "") {
        $sqlXCostoCero = " AND stockmaster.materialcost = 0 ";
    }
    
    // Por Producto
    // $SQL = "SELECT
    //     tpa.eq_stockid as cabms,
    //     tpa.partidaEspecifica,
    //     stockmaster.stockid,
    //     stockmaster.units,
    //     stockmaster.description,
    //     stockmoves.loccode as loccode,
    //     locations.locationname,
    //     '' as localidad,
    //     -- stockcategory.categorydescription as tipo,
    //     '' as tipo,
    //     areas.areadescription,
    //     CASE
    //     WHEN sum(stockmoves.qty) BETWEEN -0.01 AND 0.01 THEN 0.00
    //     ELSE sum(stockmoves.qty)
    //     END AS Existencias,
    //     locstock.ontransit as EnTransito,
    //     (case when ventas.enventa is null then 0 else ventas.enventa end ) as pedventa,
    //     Embarque.cantidadEmbarque as Embarque,
    //     locstock.reorderlevel as Autorizado,
    //     (case when compras.ENCOMPRA is null then 0 else compras.ENCOMPRA end ) as pedcompra,
    //     (case when compras.PiezasOrden is null then 0 else compras.PiezasOrden end ) as PiezasOrden ,
    //     (case when PiezasComprasPendientes.CantPiezasOrden is null then 0 else PiezasComprasPendientes.CantPiezasOrden end ) as CantPiezasPendientesOrden,
    //     stockcostsxlegal.lastcost as LastCosto,
    //     max(stockcostsxlegal.highercost) as MaxCosto,

    //     CASE 
    //         WHEN
    //             CASE 
    //                 WHEN sum(stockmoves.qty) BETWEEN -0.01 AND 0.01 
    //                 THEN 0.00
    //             ELSE 
    //                 sum(CASE WHEN stockmoves.type IN (31,35,590,591) THEN stockmoves.standardcost ELSE stockmoves.qty*stockmoves.standardcost END) 
    //                 / 
    //                 sum(stockmoves.qty)  
    //             END 
    //             != 
    //             stockcostsxlegal.avgcost 
    //         THEN stockcostsxlegal.avgcost
    //     ELSE
    //         CASE 
    //             WHEN sum(stockmoves.qty) BETWEEN -0.01 AND 0.01 
    //             THEN 0.00
    //         ELSE 
    //             sum(CASE WHEN stockmoves.type IN (31,35,590,591) THEN stockmoves.standardcost ELSE stockmoves.qty*stockmoves.standardcost END) 
    //             / 
    //             sum(stockmoves.qty)  
    //         END
    //     END AS CostoPromedio,
    //     stockcostsxlegal.avgcost,
    //     CASE WHEN inventarioInicial.total IS NOT NULL THEN inventarioInicial.total ELSE 0 END as totalInicial,
    //     CASE WHEN inventarioEntradas.total <> '' THEN inventarioEntradas.total ELSE 0 END as totalEntradas,
    //     CASE WHEN inventarioSalidas.total <> '' THEN abs(inventarioSalidas.total) ELSE 0 END as totalSalidas
    //     ";

    // $SQL .= "
    //     FROM stockmoves
    //     LEFT JOIN(
    //         SELECT  
    //         locstock.stockid,
    //         locstock.loccode,
    //         SUM(locstock.ontransit) as ontransit,
    //         SUM(locstock.reorderlevel) as reorderlevel
    //         FROM locstock
    //         GROUP BY locstock.loccode, locstock.stockid
    //     ) as locstock ON locstock.stockid = stockmoves.stockid and locstock.loccode = stockmoves.loccode
    //     JOIN stockmaster ON stockmoves.stockid = stockmaster.stockid
    //     JOIN locations ON stockmoves.loccode = locations.loccode
    //     JOIN tags ON locations.tagref = tags.tagref
    //     LEFT JOIN stockcostsxlegal ON tags.legalid = stockcostsxlegal.legalid AND stockmoves.stockid = stockcostsxlegal.stockid
    //     INNER JOIN tb_partida_articulo tpa on (stockmaster.eq_stockid = tpa.eq_stockid)
    //     LEFT JOIN departments ON tags.u_department=departments.u_department
    //     JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    //     LEFT JOIN areas ON tags.areacode = areas.areacode
    //     LEFT JOIN regions ON areas.regioncode = regions.regioncode
    //     -- LEFT JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
    //     LEFT JOIN ProdLine ON 'stockcategory.ProdLineId' = ProdLine.Prodlineid
    //     LEFT JOIN ProdGroup ON ProdLine.Prodgroupid = ProdGroup.Prodgroupid
    //     LEFT JOIN stockmanufacturer ON stockmanufacturer.manufacturerid = stockmaster.manufacturer
    //     LEFT JOIN(
    //         SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS enventa, salesorderdetails.fromstkloc AS almacenventa, salesorderdetails.stkcode AS productoventa
    //         FROM  salesorderdetails
    //         INNER JOIN salesorders ON salesorderdetails.orderno=salesorders.orderno
    //         INNER JOIN salesstatus ON salesstatus.statusid=salesorders.quotation
    //         WHERE flagstock=1
    //         GROUP BY salesorderdetails.fromstkloc ,salesorderdetails.stkcode
    //     ) AS ventas ON ventas.almacenventa=stockmoves.loccode AND ventas.productoventa= stockmoves.stockid
    //     LEFT JOIN (
    //         SELECT purchorderdetails.itemcode as producto,
    //         sum(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS CantPiezasOrden, 'purchorders.intosectorlocation' as intosectorlocation
    //         FROM purchorderdetails
    //         INNER JOIN purchorders ON purchorderdetails.orderno=purchorders.orderno
    //         WHERE purchorders.status = 'Authorised'
    //         GROUP BY purchorderdetails.itemcode, intosectorlocation
    //     ) as PiezasComprasPendientes ON PiezasComprasPendientes.producto = stockmoves.stockid /*and PiezasComprasPendientes.localidad = stockmoves.localidad*/
    //     LEFT JOIN(
    //         SELECT SUM(purchorderdetails.quantityord) AS ENCOMPRA, purchorders.intostocklocation as almacencompra, purchorderdetails.itemcode as producto,
    //         'purchorders.intosectorlocation' as localidad, sum(case when  purchorders.status = 'Authorised' then purchorderdetails.quantityord  else 0 end ) as PiezasOrden
    //             FROM  purchorderdetails
    //             INNER JOIN purchorders ON purchorderdetails.orderno=purchorders.orderno
    //             WHERE purchorders.status not  in ('Cancelled')
    //             GROUP BY purchorders.intostocklocation,purchorderdetails.itemcode
    //             HAVING SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd)>0
    //     ) AS compras ON compras.almacencompra=stockmoves.loccode and compras.producto = stockmoves.stockid /*and compras.localidad = stockmoves.localidad*/
    //     LEFT JOIN(
    //         SELECT shippingorderdetails.stockid as codigoEmbarque, SUM(shippingorderdetails.qty-shippingorderdetails.qty_sent) AS cantidadEmbarque
    //         FROM shippingorderdetails
    //         JOIN shippingorders ON shippingorderdetails.shippingno= shippingorders.shippingno
    //         WHERE shippingorders.cancelled <> 1
    //         -- AND shippingorderdetails.stockid = stockmaster.stockid
    //         GROUP BY shippingorderdetails.stockid ORDER BY shippingorderdetails.stockid ASC
    //     ) AS Embarque ON Embarque.codigoEmbarque =stockmaster.stockid
        
    //       LEFT JOIN( SELECT stockmoves.stockid, stockmoves.loccode, SUM(qty) AS total 
    //       FROM stockmoves WHERE DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') <='".$fechaini."' 
    //        GROUP BY stockmoves.stockid, stockmoves.loccode ) AS inventarioInicial ON inventarioInicial.stockid = stockmoves.stockid AND inventarioInicial.loccode = stockmoves.loccode


    //     LEFT JOIN(
    //     SELECT SUM(qty) as total, stockmoves.stockid, stockmoves.loccode FROM stockmoves 
    //     WHERE stockmoves.type in (SELECT systypescat.typeid FROM systypescat WHERE systypescat.nu_inventario_entrada = 1) 
    //     GROUP BY stockmoves.stockid, stockmoves.loccode
    //     ) as inventarioEntradas ON inventarioEntradas.stockid = stockmoves.stockid AND inventarioEntradas.loccode = stockmoves.loccode
    //     LEFT JOIN(
    //     SELECT SUM(qty) as total, stockmoves.stockid, stockmoves.loccode FROM stockmoves 
    //     WHERE stockmoves.type in (SELECT systypescat.typeid FROM systypescat WHERE systypescat.nu_inventario_salida = 1) 
    //     GROUP BY stockmoves.stockid, stockmoves.loccode
    //     ) as inventarioSalidas ON inventarioSalidas.stockid = stockmoves.stockid AND inventarioSalidas.loccode = stockmoves.loccode ";

    //     $SQLAgrupacion = "
    //     GROUP BY
    //     cabms,
    //     partidaEspecifica,
    //     stockid,
    //     units,
    //     description,
    //     loccode,
    //     locationname,
    //     localidad,
    //     tipo,
    //     areadescription,
    //     EnTransito,
    //     Autorizado,
    //     pedventa,
    //     Embarque,
    //     pedcompra,
    //     PiezasOrden ,
    //     CantPiezasPendientesOrden,
    //     LastCosto,
    //     avgcost,
    //     totalInicial,
    //     totalEntradas,
    //     totalSalidas
    //     ";
    
    // $SQLreq='';
    // if (isset($_POST['numreq'])) {
    //     if (!empty(trim($_POST['numreq']))) {
    //         $SQLreq=" AND (stockmoves.stockid IN (SELECT distinct purchorderdetails.itemcode FROM purchorderdetails JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno WHERE purchorders.requisitionno = '".$_POST['numreq']."')) ";
    //     }
    // }

    // $sqlUnidadEjecutora = "";
    // if ($_POST['selectUnidadEjecutora'] != '-1') {
    //     $sqlUnidadEjecutora = " AND stockmoves.ln_ue = '".$_POST['selectUnidadEjecutora']."' ";
    // }

    // $SQL .= "
    //         WHERE
    //         DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') >= '".$fechaini."'
    //         AND DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') <= '".$fechaFin."'
    //         AND (stockmoves.loccode = '".$_POST['almacen']."' or '".$_POST['almacen']."'='0')
    //         AND (legalbusinessunit.legalid = '".$_POST['razonsocial']."' or '".$_POST['razonsocial']."'='0')
    //         AND (areas.regioncode = '".$_POST['xRegion']."' or '".$_POST['xRegion']."'='0')
    //         AND (locations.tagref = '".$_POST['unidadnegocio']."' or '".$_POST['unidadnegocio']."'='0')
    //         AND (areas.areacode = '".$_POST['xArea']."' or '".$_POST['xArea']."'='0')
    //         AND (departments.u_department = '".$_POST['xDepto']."' or '".$_POST['xDepto']."'='0')
    //         AND (ProdGroup.Prodgroupid >= '".$_POST['xGrupo']."' or '".$_POST['xGrupo']."'='0')
    //         AND (ProdGroup.Prodgroupid <= '".$_POST['AlGrupo']."' or '".$_POST['AlGrupo']."'='0')
    //         AND (ProdLine.ProdLineId = '" . $_POST['xLinea'] . "' or '" . $_POST['xLinea'] . "'='0')
    //         AND (stockmaster.categoryid = '" . $_POST['xCategoria'] . "' or '".$_POST['xCategoria']."'='0')
    //         AND stockmaster.discontinued NOT IN (3)
    //         " . $sqlXStockid . $TypeWhereCond . $sqlXtipoInventario . $sqlXOptimoDefinido . $sqlXCostoCero .$SQLreq . $sqlUnidadEjecutora . "";

    // $SQL = $SQL . $SQLAgrupacion . $sqlSoloExistencias;
    
    // echo "<br>detalle: ".$_POST['DetailedReport'];
    // echo "<br>SQL001: <pre>";
    // print_r($SQL);
    // echo "<br>";
    // exit();

$sqlUnidadEjecutora = "";
$sqlUnidadEjecutoraTransito = "";

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!       Hasta que definan almacen por UE.       !!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

// if ($_POST['selectUnidadEjecutora'] != '-1') {
//     $sqlUnidadEjecutora = " AND stockmoves.ln_ue = '".$_POST['selectUnidadEjecutora']."' ";
//     $sqlUnidadEjecutoraTransito = " AND sa.ln_ue = '".$_POST['selectUnidadEjecutora']."' ";
// }


$SQL="SELECT tpa.eq_stockid as cabms, tpa.partidaEspecifica, stockmaster.stockid, stockmaster.units, stockmaster.description, stockmoves.loccode as loccode, locations.locationname, '' as localidad, 
'' as tipo, stockmoves.carga_inicial,stockmoves.Existencias, 
dtOntransit.cantidad_transito + stockmoves.qty_salida_solicitud as /*locstock.ontransit*/ EnTransito, 

(case when ventas.enventa is null then 0 else ventas.enventa end ) as pedventa, Embarque.cantidadEmbarque as Embarque, locstock.reorderlevel as Autorizado, (case when compras.ENCOMPRA is null then 0 else compras.ENCOMPRA end ) as pedcompra, (case when compras.PiezasOrden is null then 0 else compras.PiezasOrden end ) as PiezasOrden , (case when PiezasComprasPendientes.CantPiezasOrden is null then 0 else PiezasComprasPendientes.CantPiezasOrden end ) as CantPiezasPendientesOrden, stockcostsxlegal.lastcost as LastCosto, max(stockcostsxlegal.highercost) as MaxCosto, 

CASE WHEN stockmoves.CostoPromedio != stockcostsxlegal.avgcost THEN stockcostsxlegal.avgcost ELSE stockmoves.CostoPromedio END AS CostoPromedio,

stockcostsxlegal.avgcost, 
CASE WHEN inventarioInicial.total IS NOT NULL 
  THEN inventarioInicial.total 
  ELSE 0 
END as totalInicial, 
CASE WHEN inventarioEntradas.total <> '' THEN inventarioEntradas.total ELSE 0 END as totalEntradas, CASE WHEN inventarioSalidas.total <> '' THEN abs(inventarioSalidas.total) ELSE 0 END as totalSalidas 

FROM stockmaster 
LEFT JOIN (
            SELECT stockmoves.tagref, stockmoves.stockid, stockmoves.loccode, 
            CASE WHEN sum(stockmoves.qty) BETWEEN -0.01 AND 0.01 THEN 0.00 ELSE sum(stockmoves.qty) END AS Existencias, 
            sum(case when   stockmoves.type in (300) then (stockmoves.qty) else 0.00 end) as carga_inicial,
            CASE WHEN sum(stockmoves.qty) BETWEEN -0.01 AND 0.01 THEN 0.00 ELSE sum(CASE WHEN stockmoves.type IN (31,35,590,591) THEN stockmoves.standardcost ELSE stockmoves.qty*stockmoves.standardcost END) / sum(stockmoves.qty) END AS CostoPromedio,ln_ue,
            sum(case when stockmoves.type in (1001) then (stockmoves.qty) else 0.00 end) as qty_salida_solicitud
            FROM stockmoves 
            WHERE stockid like '%".$_POST['claveprod']."%' AND
            DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') <= '".$fechaFin."' ".$sqlUnidadEjecutora."
             AND (stockmoves.loccode = '0' or '0'='0') AND (tagref = '".$_POST['unidadnegocio']."' OR '".$_POST['unidadnegocio']."'='0')
            GROUP BY stockmoves.stockid, stockmoves.loccode
) AS stockmoves ON stockmoves.stockid = stockmaster.stockid 

LEFT JOIN( SELECT locstock.stockid, locstock.loccode, SUM(locstock.ontransit) as ontransit, SUM(locstock.reorderlevel) as reorderlevel FROM locstock GROUP BY locstock.loccode, locstock.stockid ) as locstock ON locstock.stockid = stockmaster.stockid and locstock.loccode= stockmoves.loccode

LEFT JOIN (SELECT sa.nu_tag,sa.ln_almacen,sad.ln_clave_articulo,sum(sad.nu_cantidad) AS cantidad_transito
            FROM tb_solicitudes_almacen sa
            LEFT JOIN  tb_solicitudes_almacen_detalle sad ON sa.`nu_folio` = sad.`nu_id_solicitud`
            WHERE (sa.nu_tag = '".$_POST['unidadnegocio']."' or '".$_POST['unidadnegocio']."' = '0') ".$sqlUnidadEjecutoraTransito." AND DATE_FORMAT(dtm_fecharegistro, '%Y-%m-%d') <= '".$fechaFin."' and sad.ln_clave_articulo like '%".$_POST['claveprod']."%' and estatus in (30)
            GROUP BY sa.ln_almacen, sad.ln_clave_articulo) AS dtOntransit on stockmaster.stockid = dtOntransit.ln_clave_articulo AND stockmoves.loccode = dtOntransit.ln_almacen

LEFT JOIN locations ON locstock.loccode = locations.loccode  
LEFT JOIN tags ON locations.tagref = tags.tagref 
LEFT JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid 
LEFT JOIN stockcostsxlegal ON tags.legalid = stockcostsxlegal.legalid AND stockmoves.stockid = stockcostsxlegal.stockid 
LEFT JOIN tb_partida_articulo tpa on (stockmaster.eq_stockid = tpa.eq_stockid)

LEFT JOIN( SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS enventa, salesorderdetails.fromstkloc AS almacenventa, salesorderdetails.stkcode AS productoventa FROM salesorderdetails 
INNER JOIN salesorders ON salesorderdetails.orderno=salesorders.orderno 
INNER JOIN salesstatus ON salesstatus.statusid=salesorders.quotation WHERE flagstock=1 
GROUP BY salesorderdetails.fromstkloc ,salesorderdetails.stkcode ) AS ventas ON ventas.almacenventa=stockmoves.loccode AND ventas.productoventa= stockmoves.stockid 

LEFT JOIN ( SELECT purchorderdetails.itemcode as producto, sum(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS CantPiezasOrden, 'purchorders.intosectorlocation' as intosectorlocation FROM purchorderdetails INNER JOIN purchorders ON purchorderdetails.orderno=purchorders.orderno WHERE purchorders.status = 'Authorised' GROUP BY purchorderdetails.itemcode, intosectorlocation ) as PiezasComprasPendientes ON PiezasComprasPendientes.producto = stockmoves.stockid 

LEFT JOIN( SELECT SUM(purchorderdetails.quantityord) AS ENCOMPRA, purchorders.intostocklocation as almacencompra, purchorderdetails.itemcode as producto, 'purchorders.intosectorlocation' as localidad, sum(case when purchorders.status = 'Authorised' then purchorderdetails.quantityord else 0 end ) as PiezasOrden FROM purchorderdetails INNER JOIN purchorders ON purchorderdetails.orderno=purchorders.orderno WHERE purchorders.status not in ('Cancelled') GROUP BY purchorders.intostocklocation,purchorderdetails.itemcode HAVING SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd)>0 ) AS compras ON compras.almacencompra=stockmoves.loccode and compras.producto = stockmoves.stockid 

LEFT JOIN( SELECT shippingorderdetails.stockid as codigoEmbarque, SUM(shippingorderdetails.qty-shippingorderdetails.qty_sent) AS cantidadEmbarque FROM shippingorderdetails JOIN shippingorders ON shippingorderdetails.shippingno= shippingorders.shippingno 
WHERE shippingorders.cancelled <> 1 
GROUP BY shippingorderdetails.stockid ORDER BY shippingorderdetails.stockid ASC ) AS Embarque ON Embarque.codigoEmbarque =stockmaster.stockid 

LEFT JOIN( SELECT stockmoves.stockid, stockmoves.loccode, SUM(qty) AS total FROM stockmoves WHERE DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') <= '".$fechaFin."' GROUP BY stockmoves.stockid, stockmoves.loccode ) AS inventarioInicial ON inventarioInicial.stockid = stockmaster.stockid AND inventarioInicial.loccode = locations.loccode 

LEFT JOIN( SELECT SUM(qty) as total, stockmoves.stockid, stockmoves.loccode FROM stockmoves WHERE stockmoves.type in (SELECT systypescat.typeid FROM systypescat WHERE systypescat.nu_inventario_entrada = 1 and systypescat.typeid !='300') 

AND DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') <= '".$fechaFin."' GROUP BY stockmoves.stockid, stockmoves.loccode ) as inventarioEntradas ON inventarioEntradas.stockid = stockmoves.stockid AND inventarioEntradas.loccode = stockmoves.loccode 

LEFT JOIN( SELECT SUM(qty) as total, stockmoves.stockid, stockmoves.loccode 
            FROM stockmoves 
            WHERE stockmoves.type in (SELECT systypescat.typeid FROM systypescat WHERE systypescat.nu_inventario_salida = 1) AND DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') <= '".$fechaFin."' GROUP BY stockmoves.stockid, stockmoves.loccode ) as inventarioSalidas ON inventarioSalidas.stockid = stockmoves.stockid AND inventarioSalidas.loccode = stockmoves.loccode 

WHERE stockmaster.discontinued NOT IN (3) 

AND (stockmaster.categoryid = '0' or '0'='0') ";


$SQL .=" AND (stockmoves.tagref = '".$_POST['unidadnegocio']."' or '".$_POST['unidadnegocio']."'='0')" ;
$SQL .=$sqlUnidadEjecutora;

if(isset($_POST['claveprod'])){
 $SQL.= " AND (stockmaster.stockid like '%".$_POST['claveprod']."%')"; 
}
if(isset($_POST['selPartida'])){
  if($_POST['selPartida']!='-1'){
     $SQL.= " AND (tpa. partidaEspecifica like '%".$_POST['selPartida']."%')"; 
  }

}



//DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') >= '".$fechaini."' AND
//-- AND DATE_FORMAT(stockmoves.trandate, '%Y-%m-%d') >= '.$fechaini.' 

$SQL.=" GROUP BY cabms, partidaEspecifica, stockid, units, description, loccode, locationname, localidad, tipo, EnTransito, Autorizado, pedventa, Embarque, pedcompra, PiezasOrden , CantPiezasPendientesOrden, LastCosto, Existencias, CostoPromedio, avgcost, totalInicial, totalEntradas, totalSalidas;";
    if($_SESSION ['UserID']=='desarrollo'){
      echo "<pre>".$SQL."</pre>";
    }
    $ReportResult = DB_query($SQL, $db, '', '', false, false); /* dont trap errors */

    if (DB_error_no($db) != 0) {
        $title = _('Estado General de Inventarios') . ' - ' . _('Reporte de Problema');
        // include("includes/header.inc");
        prnMsg(_('Los detalles del inventarios no se pudieron recuperar porque el SQL fallo') . ' ' . DB_error_msg($db), 'error');

        if ($debug == 1) {
            // echo "<br>$SQL";
        }
        exit();
    }

    $ProdTotInicial = 0;
    $ProdTotEntradas = 0;
    $ProdTotSalidas = 0;
    $ProdTotExistencias = 0;
    $ProdTotEnTransito  = 0;
    $ProdTotAutorizado  = 0;
    $ProdTotEmbarque    = 0;
    $ProdTotPedVenta    = 0;
    $ProdTotPedCompra   = 0;
    $ProdTotCosto       = 0;
    $ProdTotCostoNoAsig = 0;
    $ProdTotComprasAutori = 0;
    $ProdTotCantPiezasPendientesOrden = 0;

    $TipoTotInicial = 0;
    $TipoTotEntradas = 0;
    $TipoTotSalidas = 0;
    $TipoTotExistencias = 0;
    $TipoTotEnTransito  = 0;
    $TipoTotAutorizado  = 0;
    $TipoTotEmbarque    = 0;
    $TipoTotPedVenta    = 0;
    $TipoTotPedCompra   = 0;
    $TipoTotCosto       = 0;
    $TipoTotCostoNoAsig = 0;
    $TipoTotComprasAutori = 0;
    $TipoTotCantPiezasPendientesOrden = 0;

    $LineaTotInicial = 0;
    $LineaTotEntradas = 0;
    $LineaTotSalidas = 0;
    $LineaTotExistencias = 0;
    $LineaTotEnTransito  = 0;
    $LineaTotAutorizado  = 0;
    $LineaTotEmbarque    = 0;
    $LineaTotPedVenta    = 0;
    $LineaTotPedCompra   = 0;
    $LineaTotCosto       = 0;
    $LineaTotCostoNoAsig = 0;
    $LineaTotComprasAutori = 0;
    $LineaTotCantPiezasPendientesOrden = 0;

    $GrupoTotInicial = 0;
    $GrupoTotEntradas = 0;
    $GrupoTotSalidas = 0;
    $GrupoTotExistencias = 0;
    $GrupoTotEnTransito  = 0;
    $GrupoTotAutorizado  = 0;
    $GrupoTotEmbarque    = 0;
    $GrupoTotPedVenta    = 0;
    $GrupoTotPedCompra   = 0;
    $GrupoTotCosto       = 0;
    $GrupoTotCostoNoAsig = 0;
    $GrupoTotComprasAutori = 0;
    $GrupoTotCantPiezasPendientesOrden = 0;

    $TotExistencias   = 0;
    $TotEnTransito    = 0;
    $TotAutorizado    = 0;
    $TotEmbarque      = 0;
    $TotPedVenta      = 0;
    $TotPedCompra     = 0;
    $TipoTotPedCompra = 0;
    $TotCosto         = 0;
    $TotCostoNoAsig   = 0;
    $TotComprasAutori = 0; // Total Compras Autorizadas
    $TotCantPiezasPendientesOrden = 0; //Cantidad de piezas pendientes en orden

    echo '<div class="table-responsive">'; // Div Tabla Responsive
    echo "<table class='table table-bordered table-striped'>";

    $headerLineaProductos = '<thead class="header-verde"> <tr>';

    if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Yes') {
        $headerLineaProductos = $headerLineaProductos . '
            <td  rowspan="2" colspan="4" style="text-align:center;"><b>' . _('Unidad Responsanble') . '</b></td>';
    } elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'No') {
        $headerLineaProductos = $headerLineaProductos . '
            <td  rowspan="2" colspan="4" style="text-align:center;"><b>' . _('Dependencia') . '</b></td>';
    } elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Grupo') {
        $headerLineaProductos = $headerLineaProductos . '
            <td  rowspan="2" colspan="4" style="text-align:center;"><b>' . _('Grupo') . '</b></td>';
    } elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Linea') {
        $headerLineaProductos = $headerLineaProductos . '
            <td  rowspan="2" colspan="4" style="text-align:center;"><b>' . _('Linea') . '</b></td>';
    } elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'Cat') {
        $headerLineaProductos = $headerLineaProductos . '
            <td  rowspan="2" colspan="4" style="text-align:center;"><b>' . _('Categoría') . '</b></td>';
    } elseif (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'ProdSum') {
        $headerLineaProductos = $headerLineaProductos . '
            <td rowspan="2" style="text-align:center;"><b>' . _('Partida Especifica') . '</b></td> 
            <td rowspan="2" style="text-align:center;"><b>' . _('Clave') . '</b></td>
            <td rowspan="2" style="text-align:center;"><b>' . _('Descripción') . '</b></td>';
        //if ($_SESSION['AgregaLocalidad'] == 1) {
            $headerLineaProductos = $headerLineaProductos . '<td rowspan=2><b>' . _('Almacén') . '</b></td>';
        //}
        // <td rowspan="2" style="text-align:center;"><b>' . _('Categoría') . '</b></td>
        $headerLineaProductos = $headerLineaProductos . '
            <td rowspan="2" style="text-align:center;"><b>' . _('Unidad Medida') . '</b></td>';
    } else {
        $headerLineaProductos = $headerLineaProductos . '
            <td rowspan="2" style="text-align:center;"><b>' . _('Partida Especifica') . '</b></td> 
            <td rowspan="2" style="text-align:center;"><b>' . _('Clave') . '</b></td>
            <td rowspan="2" style="text-align:center;"><b>' . _('Descripción') . '</b></td>';
        //if ($_SESSION['AgregaLocalidad'] == 1) {
            $headerLineaProductos = $headerLineaProductos . '<td rowspan=2><b>' . _('Almacén') . '</b></td>';
        //}
        $headerLineaProductos = $headerLineaProductos . '
            <td rowspan="2" style="text-align:center;"><b>' . _('Categoría') . '</b></td>
            <td rowspan="2" style="text-align:center;"><b>' . _('UR') . '</b></td>';
    }
    //permiso Col Compras Autorizadas
    if ($PermisoColumCantPiezas == 1) {
        // <td rowspan=2 ><b>' . _('Embarque') . '</b></td>
        // <td rowspan=2><b>' . _('Pedido Venta.') . '</b></td>
        // <td rowspan="2" style="text-align:center;"><b>' . _('Optimo') . '</b></td>
        // <td rowspan="2" style="text-align:center;"><b>' . _('Orden Compra') . '</b></td>
        // <td rowspan="2" style="text-align:center;"><b>' . _('Compras Autorizadas') . '</b></td>
        // <td rowspan="2" style="text-align:center;"><b>' . _('Compras Pendientes') . '</b></td>
        // <td rowspan="2" style="text-align:center;"><b>' . _('Valor<br>No Asignado') . '</b></td>
        $headerLineaProductos = $headerLineaProductos . '
        <td rowspan="2" style="text-align:center;"><b>' . _('Inventario Inicial') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Entradas') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Salidas') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Exis') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Trans') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Disp') . '</b></td>
        <td rowspan="1" colspan="3" style="text-align:center;"><b>' . _('Costo') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Valor<br>Inv') . '</b></td>
        ';
    } else {
        // <td rowspan=2 ><b>' . _('Embarque') . '</b></td>
        // <td rowspan=2><b>' . _('Pedido Venta.') . '</b></td>
        // <td rowspan="2" style="text-align:center;"><b>' . _('Optimo') . '</b></td>
        // <td rowspan="2" style="text-align:center;"><b>' . _('Orden Compra') . '</b></td>
        // <td rowspan="2" style="text-align:center;"><b>' . _('Valor<br>No Asignado') . '</b></td>
        $headerLineaProductos = $headerLineaProductos . '
        <td rowspan="2" style="text-align:center;"><b>' . _('Inventario Inicial') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Entradas') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Salidas') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Exis') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Trans') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Disp') . '</b></td>
        <td rowspan="1" style="text-align:center;" colspan="3"><b>' . _('Costo') . '</b></td>
        <td rowspan="2" style="text-align:center;"><b>' . _('Valor<br>Inv') . '</b></td>
        ';
    }

    /*if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
        if (isset($_POST['almacen']) and $_POST['almacen'] != '0') {
            $headerLineaProductos = $headerLineaProductos . "<td rowspan=2><b>" . _('Localidad') . "</b></td>";
        }
    }*/

    $headerLineaProductos = $headerLineaProductos . '</tr>';

    $headerLineaProductos = $headerLineaProductos . '   <tr>
            <td style="text-align:center;"><b>' . _('Promedio') . '</b></td>
            <td style="text-align:center;"><b>' . _('Ultimo') . '</b></td>
            <td style="text-align:center;"><b>' . _('+ Alto') . '</b></td>
            ';

    $headerLineaProductos = $headerLineaProductos . '</tr> </thead>';

    $i = 0;

    $antGrupo = '';
    $antLinea = '';
    $antTipo  = '';
    $antProd  = '';

    $lineasConMismoProducto = 0;
    $primeraEntrada         = 1;

    $encabezadocada = 0;
    echo $headerLineaProductos;
    $decimalplaces = 2;

    while ($InvAnalysis = DB_fetch_array($ReportResult, $db)) {
        $encabezadocada = $encabezadocada + 1;
        if ($encabezadocada >= 30) {
            echo $headerLineaProductos;
            $encabezadocada = 0;
        }
        $thisGrupo = $InvAnalysis['grupo'];
        $thisLinea = $InvAnalysis['linea'];
        $thisTipo  = $InvAnalysis['tipo'];
        $thisProd  = $InvAnalysis['stockid'];
        $thisPartdia  = $InvAnalysis['partidaEspecifica'];

        $lineasConMismoProducto = $lineasConMismoProducto + 1;

        //$numCol = 4;
        //if ($_SESSION['AgregaLocalidad'] == 1) {
            $numCol = 5;
        //}

        if ($antProd != $thisProd) {
            if ($primeraEntrada == 0 and $lineasConMismoProducto > 1) {
                echo '<tr>';
                // <td class=normnum>' . number_format($ProdTotEmbarque, $decimalplaces) . '</td>
                // <td class=normnum>' . number_format($ProdTotPedVenta, $decimalplaces) . '</td>
                // <td class=normnum>' . number_format($ProdTotAutorizado, $decimalplaces) . '</td>
                // <td class=normnum>' . number_format($ProdTotPedCompra, $decimalplaces) . '</td>
                echo '<td class=norm colspan="'.$numCol.'"><b>TOTAL ' . $antProd . '</b></td>
                <td class=normnum>' . number_format($ProdTotInicial, $decimalplaces) . '</td>
                <td class=normnum>' . number_format($ProdTotEntradas, $decimalplaces) . '</td>
                <td class=normnum>' . number_format($ProdTotSalidas, $decimalplaces) . '</td>
                <td class=normnum>' . number_format($ProdTotExistencias, $decimalplaces) . '</td>
                <td class=normnum>' . number_format($ProdTotEnTransito, $decimalplaces) . '</td>
                <td class=normnum>' . number_format(($ProdTotExistencias - ($ProdTotEnTransito + $ProdTotEmbarque + $ProdTotPedVenta)), 2) . '</td>';
                //Columna Compras Autorizadas
                if ($PermisoColumCantPiezas == 1) {
                    // echo '<td class=pequenum>' . number_format($ProdTotComprasAutori, 2) . '</td>';
                    // echo '<td class=pequenum>' . number_format($ProdTotCantPiezasPendientesOrden, 2) . '</td>';
                }

                // <td class=normnum>' . number_format($ProdTotCostoNoAsig, 2) . '</td>
                echo '<td></td>
                <td></td>
                <td></td>
                <td class=normnum>' . number_format($ProdTotCosto, 2) . '</td>
               ';

                if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
                    if (isset($_POST['almacen']) and $_POST['almacen'] != '0') {
                        echo "<td></td>";
                    }
                }

                echo "</tr>";
                $ProdTotInicial = 0;
                $ProdTotEntradas = 0;
                $ProdTotSalidas = 0;
                $ProdTotExistencias = 0;
                $ProdTotEnTransito  = 0;
                $ProdTotAutorizado  = 0;
                $ProdTotPedVenta    = 0;
                $ProdTotPedVenta    = 0;
                $ProdTotCosto       = 0;
                $ProdTotCostoNoAsig = 0;
                $ProdTotEmbarque    = 0;
                $ProdTotComprasAutori = 0;
                $ProdTotCantPiezasPendientesOrden = 0;
            }
            $lineasConMismoProducto = 0;
        }

        if ($antTipo != $thisTipo) {
            if ($primeraEntrada == 0) {
                echo '<tr>';
                // <td class=pequenum>' . number_format($TipoTotEmbarque, $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($TipoTotPedVenta, $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($TipoTotAutorizado, $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($TipoTotPedCompra, $decimalplaces) . '</td>
                echo '<td class=norm colspan="'.$numCol.'"><b>- - TOTAL ' . $antTipo . '</b></td>
                <td class=pequenum>' . number_format($TipoTotInicial, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($TipoTotEntradas, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($TipoTotSalidas, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($TipoTotExistencias, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($TipoTotEnTransito, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format(($TipoTotExistencias - ($TipoTotEnTransito + $TipoTotEmbarque + $TipoTotPedVenta)), $decimalplaces) . '</td>';
                //Columna Compras Autorizadas
                if ($PermisoColumCantPiezas == 1) {
                    // echo '<td class=pequenum>' . number_format($TipoTotComprasAutori, 2) . '</td>';
                    // echo '<td class=pequenum>' . number_format($TipoTotCantPiezasPendientesOrden, 2) . '</td>';
                }
                // <td class=pequenum>' . number_format($TipoTotCostoNoAsig, 2) . '</td>
                echo '<td></td>
                        <td></td>
                        <td></td>
                        <td class=pequenum>' . number_format($TipoTotCosto, 2) . '</td>';

                if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
                    if (isset($_POST['almacen']) and $_POST['almacen'] != '0') {
                        echo "<td></td>";
                    }
                }
                echo "</tr>";
                $TipoTotInicial = 0;
                $TipoTotEntradas = 0;
                $TipoTotSalidas = 0;
                $TipoTotExistencias = 0;
                $TipoTotEnTransito  = 0;
                $TipoTotAutorizado  = 0;
                $TipoTotPedVenta    = 0;
                $TipoTotPedCompra   = 0;
                $TipoTotCosto       = 0;
                $TipoTotCostoNoAsig = 0;
                $TipoTotEmbarque    = 0;
                $TipoTotComprasAutori = 0;
                $TipoTotCantPiezasPendientesOrden = 0;
            }
        }

        if ($antLinea != $thisLinea) {
            if ($primeraEntrada == 0) {
                echo '<tr>';
                // <td class=pequenum>' . number_format($LineaTotEmbarque, $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($LineaTotPedVenta, $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($LineaTotAutorizado, $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($LineaTotPedCompra, $decimalplaces) . '</td>
                echo '<td class=norm colspan="'.$numCol.'"><b>- TOTAL ' . $antLinea . '</b></td>
                <td class=pequenum>' . number_format($LineaTotInicial, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($LineaTotEntradas, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($LineaTotSalidas, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($LineaTotExistencias, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($LineaTotEnTransito, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format(($LineaTotExistencias - ($LineaTotEnTransito + $LineaTotEmbarque + $LineaTotPedVenta)), $decimalplaces) . '</td>';
                //Columna Compras Autorizadas
                if ($PermisoColumCantPiezas == 1) {
                    // echo '<td class=pequenum>' . number_format($LineaTotComprasAutori, 2) . '</td>';
                    // echo '<td class=pequenum>' . number_format($LineaTotCantPiezasPendientesOrden, 2) . '</td>';
                }
                // <td class=pequenum>' . number_format($LineaTotCostoNoAsig, 2) . '</td>
                echo '  <td></td>
                        <td></td>
                        <td></td>
                        <td class=pequenum>' . number_format($LineaTotCosto, 2) . '</td>';

                if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
                    if (isset($_POST['almacen']) and $_POST['almacen'] != '0') {
                        echo "<td></td>";
                    }
                }
                echo "</tr>";

                $LineaTotInicial = 0;
                $LineaTotEntradas = 0;
                $LineaTotSalidas = 0;
                $LineaTotExistencias = 0;
                $LineaTotEnTransito  = 0;
                $LineaTotAutorizado  = 0;
                $LineaTotPedVenta    = 0;
                $LineaTotPedCompra   = 0;
                $LineaTotCosto       = 0;
                $LineaTotCostoNoAsig = 0;
                $LineaTotEmbarque    = 0;
                $LineaTotComprasAutori = 0;
                $LineaTotCantPiezasPendientesOrden = 0;
            }
        }

        if ($antGrupo != $thisGrupo) {
            if ($primeraEntrada == 0) {
                echo '<tr>';
                // <td class=pequenum>' . number_format($GrupoTotEmbarque, $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($GrupoTotPedVenta, $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($GrupoTotAutorizado, $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($GrupoTotPedCompra, $decimalplaces) . '</td>
                echo '<td class=norm colspan='.$numCol.'><b>TOTAL ' . $antGrupo . '</b></td>
                <td class=pequenum>' . number_format($GrupoTotInicial, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($GrupoTotEntradas, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($GrupoTotSalidas, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($GrupoTotExistencias, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($GrupoTotEnTransito, $decimalplaces) . '</td>
                <td class=pequenum>' . number_format(($GrupoTotExistencias - ($GrupoTotEnTransito + $GrupoTotEmbarque + $GrupoTotPedVenta)), $decimalplaces) . '</td>';
                //Columna Compras Autorizadas
                if ($PermisoColumCantPiezas == 1) {
                    // echo '<td class=pequenum>' . number_format($GrupoTotComprasAutori, 2) . '</td>';
                    // echo '<td class=pequenum>' . number_format($GrupoTotCantPiezasPendientesOrden, 2) . '</td>';
                }
                // <td class=pequenum>' . number_format($GrupoTotCostoNoAsig, 2) . '</td>
                echo '
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class=pequenum>' . number_format($GrupoTotCosto, 2) . '</td>';

                if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
                    if (isset($_POST['almacen']) and $_POST['almacen'] != '0') {
                        echo "<td></td>";
                    }
                }
                echo "</tr>";

                $GrupoTotInicial = 0;
                $GrupoTotEntradas = 0;
                $GrupoTotSalidas = 0;
                $GrupoTotExistencias = 0;
                $GrupoTotEnTransito  = 0;
                $GrupoTotAutorizado  = 0;
                $GrupoTotPedVenta    = 0;
                $GrupoTotPedCompra   = 0;
                $GrupoTotCosto       = 0;
                $GrupoTotCostoNoAsig = 0;
                $GrupoTotEmbarque    = 0;
                $GrupoTotComprasAutori = 0;
                $GrupoTotCantPiezasPendientesOrden = 0;
            }
        }

        if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
            $colspan = 11; //17;
        } else {
            $colspan = 14; //16;
        }

        //Cantidad de columnas en grupos
        if ($PermisoColumCantPiezas == 1) {
            $colspan = $colspan + 1;
        }

        echo "</tr>";
        if ($antGrupo != $thisGrupo) {
            echo '<tr>';
            echo '<td class=GroupTableRows colspan=' . $colspan . '><b>' . $thisGrupo . '</b></td>
                    </tr>';
            $antGrupo = $thisGrupo;
        }

        if ($antLinea != $thisLinea) {
            echo '<tr>';
            echo '<td class=GroupTableRows colspan=' . $colspan . '><b>- ' . $thisLinea . '</b></td>
                    </tr>';
            $antLinea = $thisLinea;
        }

        if ($antTipo != $thisTipo) {
            echo '<tr>';
            echo '<td class=GroupTableRows colspan=' . $colspan . '><b>- - ' . $thisTipo . '</b></td>
                    </tr>';
            $antTipo = $thisTipo;
        }

        $antProd = $thisProd;

        $primeraEntrada = 0;

        echo '<tr>';

        //$ProdTotInicial = $ProdTotInicial + $InvAnalysis['totalInicial'];
        $ProdTotInicial = $ProdTotInicial + $InvAnalysis['carga_inicial'];
        $ProdTotEntradas = $ProdTotEntradas + $InvAnalysis['totalEntradas'];
        $ProdTotSalidas = $ProdTotSalidas + $InvAnalysis['totalSalidas'];
        $ProdTotExistencias = $ProdTotExistencias + $InvAnalysis['Existencias'];
        $ProdTotEnTransito  = $ProdTotEnTransito + $InvAnalysis['EnTransito'];
        $ProdTotAutorizado  = $ProdTotAutorizado + $InvAnalysis['Autorizado'];
        $ProdTotPedVenta    = $ProdTotPedVenta + $InvAnalysis['pedventa'];
        $ProdTotPedCompra   = $ProdTotPedCompra + $InvAnalysis['pedcompra'];
        $ProdTotEmbarque    = $ProdTotEmbarque + $InvAnalysis['Embarque'];
        $ProdTotComprasAutori = $ProdTotComprasAutori + $InvAnalysis ['PiezasOrden'];
        $ProdTotCantPiezasPendientesOrden = $ProdTotCantPiezasPendientesOrden + $InvAnalysis['CantPiezasPendientesOrden'];
        // $ProdTotCosto = $ProdTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']));
        // $ProdTotCostoNoAsig = $ProdTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']-$InvAnalysis['pedventa']));

        //$TipoTotInicial = $TipoTotInicial + $InvAnalysis['totalInicial'];
        $TipoTotInicial = $TipoTotInicial + $InvAnalysis['carga_inicial'];
        $TipoTotEntradas = $TipoTotEntradas + $InvAnalysis['totalEntradas'];
        $TipoTotSalidas = $TipoTotSalidas + $InvAnalysis['totalSalidas'];
        $TipoTotExistencias = $TipoTotExistencias + $InvAnalysis['Existencias'];
        $TipoTotEnTransito  = $TipoTotEnTransito + $InvAnalysis['EnTransito'];
        $TipoTotAutorizado  = $TipoTotAutorizado + $InvAnalysis['Autorizado'];
        $TipoTotPedVenta    = $TipoTotPedVenta + $InvAnalysis['pedventa'];
        $TipoTotPedCompra   = $TipoTotPedCompra + $InvAnalysis['pedcompra'];
        $TipoTotEmbarque    = $TipoTotEmbarque + $InvAnalysis['Embarque'];
        $TipoTotComprasAutori = $TipoTotComprasAutori + $InvAnalysis ['PiezasOrden'];
        $TipoTotCantPiezasPendientesOrden = $TipoTotCantPiezasPendientesOrden + $InvAnalysis['CantPiezasPendientesOrden'];
        // $TipoTotCosto = $TipoTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']));
        // $TipoTotCostoNoAsig = $TipoTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']-$InvAnalysis['pedventa']));

        //$LineaTotInicial = $LineaTotInicial + $InvAnalysis['totalInicial'];
        $LineaTotInicial = $LineaTotInicial + $InvAnalysis['carga_inicial'];
        $LineaTotEntradas = $LineaTotEntradas + $InvAnalysis['totalEntradas'];
        $LineaTotSalidas = $LineaTotSalidas + $InvAnalysis['totalSalidas'];
        $LineaTotExistencias = $LineaTotExistencias + $InvAnalysis['Existencias'];
        $LineaTotEnTransito  = $LineaTotEnTransito + $InvAnalysis['EnTransito'];
        $LineaTotAutorizado  = $LineaTotAutorizado + $InvAnalysis['Autorizado'];
        $LineaTotPedVenta    = $LineaTotPedVenta + $InvAnalysis['pedventa'];
        $LineaTotPedCompra   = $LineaTotPedCompra + $InvAnalysis['pedcompra'];
        $LineaTotEmbarque    = $LineaTotEmbarque + $InvAnalysis['Embarque'];
        $LineaTotComprasAutori = $LineaTotComprasAutori + $InvAnalysis ['PiezasOrden'];
        $LineaTotCantPiezasPendientesOrden = $LineaTotCantPiezasPendientesOrden + $InvAnalysis ['CantPiezasPendientesOrden'];
        // $LineaTotCosto = $LineaTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']));
        // $LineaTotCostoNoAsig = $LineaTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']-$InvAnalysis['pedventa']));

        //$GrupoTotInicial = $GrupoTotInicial + $InvAnalysis['totalInicial'];
        $GrupoTotInicial = $GrupoTotInicial + $InvAnalysis['carga_inicial'];
        $GrupoTotEntradas = $GrupoTotEntradas + $InvAnalysis['totalEntradas'];
        $GrupoTotSalidas = $GrupoTotSalidas + $InvAnalysis['totalSalidas'];
        $GrupoTotExistencias = $GrupoTotExistencias + $InvAnalysis['Existencias'];
        $GrupoTotEnTransito  = $GrupoTotEnTransito + $InvAnalysis['EnTransito'];
        $GrupoTotAutorizado  = $GrupoTotAutorizado + $InvAnalysis['Autorizado'];
        $GrupoTotPedVenta    = $GrupoTotPedVenta + $InvAnalysis['pedventa'];
        $GrupoTotPedCompra   = $GrupoTotPedCompra + $InvAnalysis['pedcompra'];
        $GrupoTotEmbarque    = $GrupoTotEmbarque + $InvAnalysis['Embarque'];
        $GrupoTotComprasAutori = $GrupoTotComprasAutori + $InvAnalysis ['PiezasOrden'];
        $GrupoTotCantPiezasPendientesOrden = $GrupoTotCantPiezasPendientesOrden + $InvAnalysis ['CantPiezasPendientesOrden'];
        // $GrupoTotCosto = $GrupoTotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']));
        // $GrupoTotCostoNoAsig = $GrupoTotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']-$InvAnalysis['pedventa']));

        //$TotInicial = $TotInicial + $InvAnalysis['totalInicial'];
        $TotInicial = $TotInicial + $InvAnalysis['carga_inicial'];
        $TotEntradas = $TotEntradas + $InvAnalysis['totalEntradas'];
        $TotSalidas = $TotSalidas + $InvAnalysis['totalSalidas'];
        $TotExistencias = $TotExistencias + $InvAnalysis['Existencias'];
        $TotEnTransito  = $TotEnTransito + $InvAnalysis['EnTransito'];
        $TotAutorizado  = $TotAutorizado + $InvAnalysis['Autorizado'];
        $TotPedVenta    = $TotPedVenta + $InvAnalysis['pedventa'];
        $TotPedCompra   = $TotPedCompra + $InvAnalysis['pedcompra'];
        $TotEmbarque    = $TotEmbarque + $InvAnalysis['Embarque'];

        //$partidaE = fnBuscarPartidaEspecifica($db, $InvAnalysis['stockid']);

        // $TotCosto = $TotCosto + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']));
        // $TotCostoNoAsig = $TotCostoNoAsig + ($InvAnalysis['CostoPromedio']*($InvAnalysis['Existencias']+$InvAnalysis['EnTransito']-$InvAnalysis['pedventa']));
        // echo $_POST['DetailedReport'];
        if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
            if ($InvAnalysis['controlled'] == 1) {
                echo '<td class=peque >' . $InvAnalysis['partidaEspecifica'] . '</td>';
                if ($_POST['DetailedReport'] == 'Prod') {
                    echo '<td class=peque>' . $InvAnalysis['stockid'] . '<b> (' . $InvAnalysis['units'] . ') </b></td>';
                } else {
                    echo '<td class=peque>' . $InvAnalysis['stockid'] . '</td>';
                }

                //echo '<td class=peque >' . $InvAnalysis['partidaEspecifica'] . '</td>';
                //echo '<td class=peque >Partida Especifica</td>';

                echo '<td class=peque >' . $InvAnalysis['description'] . '</td>';
                //if ($_SESSION['AgregaLocalidad'] == 1) {
                    echo '<td class=peque>'.$InvAnalysis['loccode']." - ".$InvAnalysis['locationname'] . (empty($InvAnalysis['localidad']) ? "": " - " . $InvAnalysis['localidad'] ) .'</td>';
                //
                // echo '<td class=pequenum>' . $InvAnalysis['tipo'] . '</td>';
                if (empty($InvAnalysis['areadescription'])) {
                    $InvAnalysis['areadescription'] = "";
                }
                if ($_POST['DetailedReport'] == 'Prod') {
                    echo '      <td class=pequenum>' . $InvAnalysis['areadescription'] . '</td>';
                } elseif ($_POST['DetailedReport'] == 'ProdSum') {
                    echo '      <td class=pequenum>' . $InvAnalysis['units'] . '</td>';
                }
                // <td class=pequenum>' . number_format($InvAnalysis['Embarque'], $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($InvAnalysis['pedventa'], $decimalplaces) . '</td>
                echo ' <td class=pequenum>' . number_format($InvAnalysis['Existencias'], $decimalplaces) . '</td>
                        <td class=pequenum>' . number_format($InvAnalysis['EnTransito'], $decimalplaces) . '</td>
                        <td class=pequenum>' . number_format(($InvAnalysis['Existencias'] - ($InvAnalysis['EnTransito'] + $InvAnalysis['Embarque'] + $InvAnalysis['pedventa'])), $decimalplaces) . '</td>
                        <td class=pequenum>' . number_format($InvAnalysis['Autorizado'], $decimalplaces) . '</td>
                        <td class=pequenum>' . number_format($InvAnalysis['pedcompra'], $decimalplaces) . '</td>';
                //Columna Compras Autorizadas
                if ($PermisoColumCantPiezas == 1) {
                    echo '<td class=pequenum>----</td>';
                    echo '<td class=pequenum>----</td>';
                }
                echo '<td class=pequenum>----</td>
                        <td class=pequenum>----</td>
                        <td class=pequenum>----</td>
                        <td class=pequenum>----</td>
                        <td class=pequenum>----</td>
                        ';

                if (isset($_POST['almacen']) and $_POST['almacen'] != '0') {
                    echo '<td class=pequenum>' . $InvAnalysis['localidad'] . '</td>';
                }

                echo '</tr>';

                /*
                Se cambio consulta para obtener costo promedio por lote
                $SQL = "SELECT *,
                               stockserialitems.quantity AS cantidad
                        FROM locstock
                        JOIN stockmaster ON locstock.stockid = stockmaster.stockid
                        JOIN locations ON locstock.loccode = locations.loccode
                        JOIN tags ON locations.tagref = tags.tagref
                        JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
                        AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
                        JOIN areas ON tags.areacode = areas.areacode
                        JOIN regions ON areas.regioncode = regions.regioncode
                        JOIN stockserialitems ON locstock.stockid = stockserialitems.stockid
                        AND locstock.loccode = stockserialitems.loccode
                        WHERE stockserialitems.quantity>0
                          AND locstock.stockid = '" . $InvAnalysis['stockid'] . "'
                          AND areas.areacode= '" . $InvAnalysis['areacode'] . "'
                          AND stockserialitems.loccode = '" . $InvAnalysis['loccode'] . "'
                          AND (areas.regioncode = " . $_POST['xRegion'] . "
                               OR " . $_POST['xRegion'] . "=0)
                          AND (locations.tagref = " . $_POST['unidadnegocio'] . "
                               OR " . $_POST['unidadnegocio'] . "=0) " . $sqlXtipoInventario;
                */

                //Se modifico el query para poder traer el ultimo costo usado por el lote
                $SQL = "SELECT DT1.*,stockmoves.standardcost
                        FROM(
                        SELECT stockserialitems.serialno,
                        sum(stockserialmoves.moveqty) AS Existencias,
                        sum(stockserialmoves.standardcost/stockserialmoves.moveqty) AS standardcost1,
                        sectorlocations.location,
                        stockserialitems.quantity,
                        locations.loccode,
                        locations.locationname,
                        max(stockmoves.stkmoveno) as stkmoveno
                        FROM stockserialmoves
                        INNER JOIN stockmoves ON stockserialmoves.stockmoveno=stockmoves.stkmoveno
                        JOIN locations ON locations.loccode = stockmoves.loccode
                        LEFT JOIN sectorlocations ON sectorlocations.seccode=stockmoves.localidad
                        LEFT JOIN stockserialitems ON stockserialitems.stockid = stockserialmoves.stockid and stockserialitems.serialno = stockserialmoves.serialno
                        JOIN tags ON stockmoves.tagref = tags.tagref
                        JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
                        AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
                        JOIN areas ON tags.areacode = areas.areacode
                        JOIN regions ON areas.regioncode = regions.regioncode
                        WHERE stockserialmoves.stockid='" . $InvAnalysis ['stockid'] . "'
                          AND (areas.regioncode = " . $_POST ['xRegion'] . "
                               OR " . $_POST ['xRegion'] . "=0)
                          AND (tags.tagref = " . $_POST ['unidadnegocio'] . "
                               OR " . $_POST ['unidadnegocio'] . "=0) " . $sqlXtipoInventario;
                if ($InvAnalysis ['loccode'] != "") {
                    $SQL = $SQL . " AND stockmoves.loccode in ('" . $InvAnalysis ['loccode'] . "')";
                }
                if ((strlen(trim($_POST ['almacen'])) > 0) and ($_POST ['almacen'] != "0")) {
                    $SQL = $SQL . " AND stockmoves.loccode  in ('" . $_POST ['almacen'] . "')";
                }
                $SQL = $SQL . " GROUP BY stockserialmoves.serialno,stockserialmoves.stockid";
                //Antes se pidio que solo validara la existencia
                //$SQL = $SQL . "  HAVING Existencias <> 0 OR abs(standardcost) >= 1";
                //
                
                if (preg_match("/erptycqsa/", $_SESSION['DatabaseName'])) {
                    $SQL = $SQL . "  HAVING stockserialitems.quantity <> 0  ) DT1 left join stockmoves on DT1.stkmoveno = stockmoves.stkmoveno";
                } else {
                    $SQL = $SQL . "  HAVING Existencias <> 0 OR abs(standardcost1) >= 1) DT1 left join stockmoves on DT1.stkmoveno = stockmoves.stkmoveno";
                }
                

                if ($_SESSION["UserID"] == "desarrollo") {
                    //echo '<pre><br>'.$SQL;
                }

                $result = DB_query($SQL, $db);

                while ($myrow = DB_fetch_array($result)) {
                    echo '<tr>';
                    echo '<td colspan=1></td>
                            <td class="peque">' . $myrow['serialno'] . '</td>
                            <td class="peque">' . $myrow['loccode'] . " - " . $myrow['locationname'] . '</td>
                            <td colspan=2 class=normnum></td>';
                    $Existencias = 0;
                    if (preg_match("/erptycqsa/", $_SESSION['DatabaseName'])) {
                        //Si es tubos poner cantidad de stockserialitems
                        $Existencias = $myrow ['quantity'];
                    } else {
                        $Existencias = $myrow ['Existencias'];
                    }
                    //<td class="peque">' . $myrow['cantidad'] . '</td>
                    echo '<td class=peque>' . number_format($Existencias, $decimalplaces) . '</td>';
                    echo '<td colspan=3 class=normnum></td>
                            <td class=normnum></td>
                            <td class=normnum></td>
                            <td class=normnum></td>';
                    //Columna Compras Autorizadas
                    if ($PermisoColumCantPiezas == 1) {
                        echo '<td class=pequenum>----</td>';
                        echo '<td class=pequenum>----</td>';
                    }
                    echo '
                    <td class=pequenum>' . number_format($myrow['standardcost'], 2) . '</td>
                    <td class=pequenum></td>
                    <td class=pequenum></td>
                    <td class=pequenum>' . number_format($myrow['standardcost'] * $Existencias, 2) . '</td>
                    <td class=pequenum></td>
                    </tr>';

                    $costoxserie         = $myrow['standardcost'] * $Existencias;
                    $ProdTotCosto        = $ProdTotCosto + ($costoxserie);
                    //$ProdTotCostoNoAsig  = $ProdTotCostoNoAsig + ($costoxserie);
                    $TipoTotCosto        = $TipoTotCosto + ($costoxserie);
                    //$TipoTotCostoNoAsig  = $TipoTotCostoNoAsig + ($costoxserie);
                    $LineaTotCosto       = $LineaTotCosto + ($costoxserie);
                    //$LineaTotCostoNoAsig = $LineaTotCostoNoAsig + ($costoxserie);
                    $GrupoTotCosto       = $GrupoTotCosto + ($costoxserie);
                    //$GrupoTotCostoNoAsig = $GrupoTotCostoNoAsig + ($costoxserie);
                    $TotCosto            = $TotCosto + ($costoxserie);
                    //$TotCostoNoAsig      = $TotCostoNoAsig + ($costoxserie);
                }
            } else {
                echo '<td class=peque >' . $InvAnalysis['partidaEspecifica'] . '</td>';
                if ($_POST['DetailedReport'] == 'Prod') {
                    echo '<td class=peque>' . $InvAnalysis['stockid'] . '<b> (' . $InvAnalysis['units'] . ') </b></td>';
                } else {
                    echo '<td class=peque>' . $InvAnalysis['stockid'] . '</td>';
                }
                //echo '<td class=peque >' . $InvAnalysis['partidaEspecifica'] . '</td>';
                //echo '<td class=peque >Partida Especifica</td>';

                echo '<td class=peque >' . $InvAnalysis['description'] . '</td>';
                //if ($_SESSION['AgregaLocalidad'] == 1) {
                    echo '<td class=peque>'.$InvAnalysis['loccode']." - ".$InvAnalysis['locationname'] . ( empty($InvAnalysis['localidad'])  ? "" :" -  " . $InvAnalysis['localidad']) .'</td>';
                //
                // echo '<td class=pequenum>' . $InvAnalysis['tipo'] . '</td>';
                // <td class=pequenum>'.$InvAnalysis['areadescription'].'</td>
                if ($_POST['DetailedReport'] == 'ProdSum') {
                    echo '      <td class=pequenum>' . $InvAnalysis['units'] . '</td>';
                } else {
                    echo '      <td class=pequenum>' . $InvAnalysis['areadescription'] . '</td>';
                }
                // <td class=pequenum>' . number_format($InvAnalysis['Embarque'], $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($InvAnalysis['pedventa'], $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($InvAnalysis['Autorizado'], $decimalplaces) . '</td>
                // <td class=pequenum>' . number_format($InvAnalysis['pedcompra'], $decimalplaces) . '</td>

                //<td class=pequenum>' . number_format($InvAnalysis['totalInicial'], $decimalplaces) . '  </td>
                echo '
                
                <td class=pequenum>' . number_format($InvAnalysis['carga_inicial'], $decimalplaces) . '  </td>
                <td class=pequenum>' . number_format($InvAnalysis['totalEntradas'], $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($InvAnalysis['totalSalidas'], $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($InvAnalysis['Existencias'], $decimalplaces) . '</td>
                <td class=pequenum>' . number_format($InvAnalysis['EnTransito'], $decimalplaces) . '</td>
                <td class=pequenum>' . number_format(($InvAnalysis['Existencias'] - ($InvAnalysis['EnTransito'] + $InvAnalysis['pedventa'] + $InvAnalysis['Embarque'])), $decimalplaces) . '</td>';
                //columna Compras Autorizadas
                if ($PermisoColumCantPiezas == 1) {
                    // echo '<td class=pequenum>' . number_format($InvAnalysis ['PiezasOrden'], 2) . '</td>';
                    // echo '<td class=pequenum>' . number_format($InvAnalysis['CantPiezasPendientesOrden'], 2) . '</td>';
                }
                // <td class=pequenum>' . '$' . number_format($InvAnalysis['CostoPromedio'] * ( /* $InvAnalysis['EnTransito']+ */$InvAnalysis['Existencias'] - $InvAnalysis['pedventa']), 2) . '</td>
                echo '<td class=pequenum nowrap>' . '$' . number_format($InvAnalysis['CostoPromedio'], 2) . '</td>
                <td class=pequenum nowrap >' . '$' . number_format($InvAnalysis['LastCosto'], 2) . '</td>
                <td class=pequenum nowrap>' . '$' . number_format($InvAnalysis['MaxCosto'], 2) . '</td>
                <td class=pequenum>' . '$' . number_format($InvAnalysis['CostoPromedio'] * ( /* $InvAnalysis['EnTransito']+$ */$InvAnalysis['Existencias']), 2) . '</td>';

                if (isset($_POST['almacen']) and $_POST['almacen'] != '0') {
                    echo '<td class=pequenum>' . $InvAnalysis['localidad'] . '</td>';
                }
                echo "</tr>";
                $ProdTotCosto        = $ProdTotCosto + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */));
                $ProdTotCostoNoAsig  = $ProdTotCostoNoAsig + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */ - $InvAnalysis['pedventa']));
                $TipoTotCosto        = $TipoTotCosto + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */));
                $TipoTotCostoNoAsig  = $TipoTotCostoNoAsig + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */ - $InvAnalysis['pedventa']));
                $LineaTotCosto       = $LineaTotCosto + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */));
                $LineaTotCostoNoAsig = $LineaTotCostoNoAsig + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */ - $InvAnalysis['pedventa']));
                $GrupoTotCosto       = $GrupoTotCosto + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */));
                $GrupoTotCostoNoAsig = $GrupoTotCostoNoAsig + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */ - $InvAnalysis['pedventa']));
                $TotCosto            = $TotCosto + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */));
                $TotCostoNoAsig      = $TotCostoNoAsig + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */ - $InvAnalysis['pedventa']));

                //total Compras Autorizadasss
                $TotComprasAutori = $TotComprasAutori + $InvAnalysis ['PiezasOrden'];
                $TotCantPiezasPendientesOrden = $TotCantPiezasPendientesOrden + $InvAnalysis['CantPiezasPendientesOrden'];
            }
        } else {
            //echo '<td class=peque >' . $InvAnalysis['partidaEspecifica'] . '</td>';
            //echo '<td class=peque >Partida Especifica</td>';
            // <td class=peque>' . $InvAnalysis['stockid'] . '</td>
            // <td class=peque >' . $InvAnalysis['description'] . '</td>
            // <td class=pequenum>' . $InvAnalysis['tipo'] . '</td>
            echo '<td class=pequenum colspan=4>' . $InvAnalysis['description'] . '</td>';
            
            // <td class=pequenum>' . number_format($InvAnalysis['Embarque'], $decimalplaces) . '</td>
            // <td class=pequenum>' . number_format($InvAnalysis['pedventa'], $decimalplaces) . '</td>
            echo '<td class=pequenum>' . number_format($InvAnalysis['Existencias'], $decimalplaces) . '</td>
            <td class=pequenum>' . number_format($InvAnalysis['EnTransito'], $decimalplaces) . '</td>
            <td class=pequenum>' . number_format(($InvAnalysis['Existencias'] - ($InvAnalysis['EnTransito'] + $InvAnalysis['pedventa'] + $InvAnalysis['Embarque'])), $decimalplaces) . '</td>
            <td class=pequenum>' . number_format($InvAnalysis['Autorizado'], $decimalplaces) . '</td>
            <td class=pequenum>' . number_format($InvAnalysis['pedcompra'], $decimalplaces) . '</td>';
            //columna Compras Autorizadas
            if ($PermisoColumCantPiezas == 1) {
                echo '<td class=pequenum>' . number_format($InvAnalysis ['PiezasOrden'], 2) . '</td>';
                echo '<td class=pequenum>' . number_format($InvAnalysis['CantPiezasPendientesOrden'], 2) . '</td>';
            }

            echo '<td class=pequenum nowrap>' . '$' . number_format($InvAnalysis['CostoPromedio'], 2) . '</td>
            <td class=pequenum nowrap>' . '$' . number_format($InvAnalysis['LastCosto'], 2) . '</td>
            <td class=pequenum nowrap>' . '$' . number_format($InvAnalysis['MaxCosto'], 2) . '</td>
            <td class=pequenum>' . '$' . number_format($InvAnalysis['CostoPromedio'] * ( /* $InvAnalysis['EnTransito']+ */$InvAnalysis['Existencias']), 2) . '</td>
            <td class=pequenum>' . '$' . number_format($InvAnalysis['CostoPromedio'] * ( /* $InvAnalysis['EnTransito']+ */$InvAnalysis['Existencias'] - $InvAnalysis['pedventa']), 2) . '</td>
            </tr>';

            $ProdTotCosto        = $ProdTotCosto + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */));
            $ProdTotCostoNoAsig  = $ProdTotCostoNoAsig + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */ - $InvAnalysis['pedventa']));
            $TipoTotCosto        = $TipoTotCosto + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */));
            $TipoTotCostoNoAsig  = $TipoTotCostoNoAsig + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */ - $InvAnalysis['pedventa']));
            $LineaTotCosto       = $LineaTotCosto + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */));
            $LineaTotCostoNoAsig = $LineaTotCostoNoAsig + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */ - $InvAnalysis['pedventa']));
            $GrupoTotCosto       = $GrupoTotCosto + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */));
            $GrupoTotCostoNoAsig = $GrupoTotCostoNoAsig + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */ - $InvAnalysis['pedventa']));
            $TotCosto            = $TotCosto + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */));
            $TotCostoNoAsig      = $TotCostoNoAsig + ($InvAnalysis['CostoPromedio'] * ($InvAnalysis['Existencias']/* +$InvAnalysis['EnTransito'] */ - $InvAnalysis['pedventa']));
            //total Compras Autorizadasss
            $TotComprasAutori = $TotComprasAutori + $InvAnalysis ['PiezasOrden'] ;
            $TotCantPiezasPendientesOrden = $TotCantPiezasPendientesOrden + $InvAnalysis['CantPiezasPendientesOrden'];
        }
    }

    $TotExistenciasDisp = $TotExistencias;
    if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
        //$numCol = 4;
        //if ($_SESSION['AgregaLocalidad'] == 1) {
            $numCol = 5;
        //}
        if ($lineasConMismoProducto >= 1) {
            if ($primeraEntrada == 0) {
                echo '<tr>';
                // <td class=normnum>' . number_format($ProdTotEmbarque, $decimalplaces) . '</td>
                // <td class=normnum>' . number_format($ProdTotPedVenta, $decimalplaces) . '</td>
                // <td class=normnum>' . number_format($ProdTotAutorizado, $decimalplaces) . '</td>
                // <td class=normnum>' . number_format($ProdTotPedCompra, $decimalplaces) . '</td>
                echo '
                <td class=norm colspan='.$numCol.'><b>TOTAL ' . $antProd . '</b></td>
                <td class=normnum>' . number_format($ProdTotInicial, $decimalplaces) . '</td>
                <td class=normnum>' . number_format($ProdTotEntradas, $decimalplaces) . '</td>
                <td class=normnum>' . number_format($ProdTotSalidas, $decimalplaces) . '</td>
                <td class=normnum>' . number_format($ProdTotExistencias, $decimalplaces) . '</td>
                <td class=normnum>' . number_format($ProdTotEnTransito, $decimalplaces) . '</td>
                <td class=normnum>' . number_format(($ProdTotExistencias - ($ProdTotEnTransito + $ProdTotEmbarque + $ProdTotPedVenta)), $decimalplaces) . '</td>';
                //columna Compras Autorizadas
                if ($PermisoColumCantPiezas == 1) {
                    // echo ' <td class=pequenum>' . number_format($ProdTotComprasAutori, $decimalplaces) . '</td> ';
                    // echo ' <td class=pequenum>' . number_format($ProdTotCantPiezasPendientesOrden, $decimalplaces) . '</td> ';
                }
                // <td class=normnum>' . number_format($ProdTotCostoNoAsig, 2) . '</td>
                echo '<td class=normnum></td>
                <td class=normnum></td>
                <td class=normnum></td>
                <td class=normnum>' . number_format($ProdTotCosto, 2) . '</td>';

                if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
                    if (isset($_POST['almacen']) and $_POST['almacen'] != '0') {
                        echo "<td></td>";
                    }
                }
                echo "</tr>";
            }
        }

        //$numCol = 4;
        //if ($_SESSION['AgregaLocalidad'] == 1) {
            $numCol = 5;
        //}
        if ($primeraEntrada == 0 && 1 == 2) {
          // No mostrar informacion ya que no muestra tabla de stockcategory
            echo '<tr>';
            // <td class=pequenum>' . number_format($TipoTotEmbarque, $decimalplaces) . '</td>
            // <td class=pequenum>' . number_format($TipoTotPedVenta, $decimalplaces) . '</td>
            // <td class=pequenum>' . number_format($TipoTotAutorizado, $decimalplaces) . '</td>
            // <td class=pequenum>' . number_format($TipoTotPedCompra, $decimalplaces) . '</td>
            echo '
            <td class=norm colspan='.$numCol.'><b>- - TOTAL ' . $antTipo . '</b></td>
            <td class=pequenum>' . number_format($TipoTotInicial, $decimalplaces) . '</td>
            <td class=pequenum>' . number_format($TipoTotEntradas, $decimalplaces) . '</td>
            <td class=pequenum>' . number_format($TipoTotSalidas, $decimalplaces) . '</td>
            <td class=pequenum>' . number_format($TipoTotExistencias, $decimalplaces) . '</td>
            <td class=pequenum>' . number_format($TipoTotEnTransito, $decimalplaces) . '</td>
            <td class=pequenum>' . number_format(($TipoTotExistencias - ($TipoTotEnTransito + $TipoTotEmbarque + $TipoTotPedVenta)), $decimalplaces) . '</td>';
            //columna Compras Autorizadas
            if ($PermisoColumCantPiezas == 1) {
                // echo ' <td class=pequenum>' . number_format($TipoTotComprasAutori, $decimalplaces) . '</td> ';
                // echo ' <td class=pequenum>' . number_format($TipoTotCantPiezasPendientesOrden, $decimalplaces) . '</td> ';
            }
            // <td class=pequenum>' . number_format($TipoTotCostoNoAsig, 2) . '</td>
            echo '<td class=pequenum></td>
            <td class=pequenum></td>
            <td class=pequenum></td>
            <td class=pequenum>' . number_format($TipoTotCosto, 2) . '</td>';

            if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
                if (isset($_POST['almacen']) and $_POST['almacen'] != '0') {
                    echo "<td></td>";
                }
            }
            echo "</tr>";
        }

        // if ($primeraEntrada == 0) {
        //     echo '<tr>';
        //     // <td class=pequenum>' . number_format($LineaTotEmbarque, $decimalplaces) . '</td>
        //     // <td class=pequenum>' . number_format($LineaTotPedVenta, $decimalplaces) . '</td>
        //     echo '<td class=norm colspan='.$numCol.'><b>- TOTAL ' . $antLinea . '</b></td>
        //             <td class=pequenum>' . number_format($LineaTotExistencias, $decimalplaces) . '</td>
        //             <td class=pequenum>' . number_format($LineaTotEnTransito, $decimalplaces) . '</td>
        //             <td class=pequenum>' . number_format(($LineaTotExistencias - ($LineaTotEnTransito + $LineaTotEmbarque + $LineaTotPedVenta)), $decimalplaces) . '</td>
        //             <td class=pequenum>' . number_format($LineaTotAutorizado, $decimalplaces) . '</td>
        //             <td class=pequenum>' . number_format($LineaTotPedCompra, $decimalplaces) . '</td>';
        //     //columna Compras Autorizadas
        //     if ($PermisoColumCantPiezas == 1) {
        //         echo ' <td class=pequenum>' . number_format($LineaTotComprasAutori, $decimalplaces) . '</td> ';
        //         echo ' <td class=pequenum>' . number_format($LineaTotCantPiezasPendientesOrden, $decimalplaces) . '</td> ';
        //     }
        //     echo '<td class=pequenum></td>
        //             <td class=pequenum></td>
        //             <td class=pequenum></td>
        //             <td class=pequenum>' . number_format($LineaTotCosto, 2) . '</td>
        //             <td class=pequenum>' . number_format($LineaTotCostoNoAsig, 2) . '</td>';

        //     if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
        //         if (isset($_POST['almacen']) and $_POST['almacen'] != '0') {
        //             echo "<td></td>";
        //         }
        //     }
        //     echo "</tr>";
        // }

        // if ($primeraEntrada == 0) {
        //     echo '<tr>';
        //     // <td class=pequenum>' . number_format($GrupoTotEmbarque, $decimalplaces) . '</td>
        //     // <td class=pequenum>' . number_format($GrupoTotPedVenta, $decimalplaces) . '</td>
        //     echo '<td class=norm colspan='.$numCol.'><b>TOTAL ' . $antGrupo . '</b></td>
        //             <td class=pequenum>' . number_format($GrupoTotExistencias, $decimalplaces) . '</td>
        //             <td class=pequenum>' . number_format($GrupoTotEnTransito, $decimalplaces) . '</td>
        //             <td class=pequenum>' . number_format(($GrupoTotExistencias - ($GrupoTotEnTransito + $GrupoTotEmbarque + $GrupoTotPedVenta)), $decimalplaces) . '</td>
        //             <td class=pequenum>' . number_format($GrupoTotAutorizado, $decimalplaces) . '</td>
        //             <td class=pequenum>' . number_format($GrupoTotPedCompra, $decimalplaces) . '</td>';
        //     //columna Compras Autorizadas
        //     if ($PermisoColumCantPiezas == 1) {
        //         echo ' <td class=pequenum>' . number_format($GrupoTotComprasAutori, $decimalplaces) . '</td> ';
        //         echo ' <td class=pequenum>' . number_format($GrupoTotCantPiezasPendientesOrden, $decimalplaces) . '</td> ';
        //     }
        //     echo '<td class=pequenum></td>
        //             <td class=pequenum></td>
        //             <td class=pequenum></td>
        //             <td class=pequenum>' . number_format($GrupoTotCosto, 2) . '</td>
        //             <td class=pequenum>' . number_format($GrupoTotCostoNoAsig, 2) . '</td>';
        //     if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
        //         if (isset($_POST['almacen']) and $_POST['almacen'] != '0') {
        //             echo "<td></td>";
        //         }
        //     }
        //     echo "</tr>";
        // }
    }

    echo '<tr class="EvenTableRows">';

    //$numCol = 4;
    //if ($_SESSION['AgregaLocalidad'] == 1) {
    if (isset($_POST['DetailedReport']) and ($_POST['DetailedReport'] == 'Prod' or $_POST['DetailedReport'] == 'ProdSum')) {
        $numCol = 5;
    }
    //}

    /* DESPLIEGA VALOR DE SUBTOTAL X UN Y FOLIO */
    //columna Compras Autorizadas
    if ($PermisoColumCantPiezas == 1) {
        // <td class="normnum" style="text-align: right;"><b>%s</b></td>
        // <td class="normnum" style="text-align: right;"><b>%s</b></td>
        // number_format($TotEmbarque, $decimalplaces),
        // number_format($TotPedVenta, $decimalplaces),
        // <td class="normnum" style="text-align: right;"><b>%s</b></td>
        // <td class="normnum" style="text-align: right;"><b>%s</b></td>
        // <td class="normnum" style="text-align: right;"><b>%s</b></td>
        // <td class="normnum" style="text-align: right;"><b>%s</b></td>
        // <td class="normnum" style="text-align: right;" ><b>%s</b></td>
        // number_format($TotAutorizado, $decimalplaces),
        // number_format($TotPedCompra, $decimalplaces),
        // number_format($TotComprasAutori, 2),
        // number_format($TotCantPiezasPendientesOrden, 2),
        // '$' . number_format($TotCostoNoAsig, 2)
        printf(
            '<td colspan="'.$numCol.'" ><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td colspan="3" class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            </tr>',
            'TOTALES',
            number_format($TotInicial, $decimalplaces),
            number_format($TotEntradas, $decimalplaces),
            number_format($TotSalidas, $decimalplaces),
            number_format($TotExistencias, $decimalplaces),
            number_format($TotEnTransito, $decimalplaces),
            number_format($TotExistencias - ($TotEnTransito + $TotEmbarque + $TotPedVenta), $decimalplaces),
            '',
            '$' . number_format($TotCosto, 2)
        );
    } else {
        // <td class="normnum" style="text-align: right;"><b>%s</b></td>
        // <td class="normnum" style="text-align: right;"><b>%s</b></td>
        // number_format($TotEmbarque, $decimalplaces),
        // number_format($TotPedVenta, $decimalplaces),
        // <td class="normnum" style="text-align: right;"><b>%s</b></td>
        // <td class="normnum" style="text-align: right;"><b>%s</b></td>
        // <td class="normnum" style="text-align: right;" ><b>%s</b></td>
        // number_format($TotAutorizado, $decimalplaces),
        // number_format($TotPedCompra, $decimalplaces),
        // '$' . number_format($TotCostoNoAsig, 2)
        printf(
            '<td colspan="'.$numCol.'"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            <td colspan="3" class="normnum" style="text-align: right;"><b>%s</b></td>
            <td class="normnum" style="text-align: right;"><b>%s</b></td>
            </tr>',
            'TOTALES',
            number_format($TotInicial, $decimalplaces),
            number_format($TotEntradas, $decimalplaces),
            number_format($TotSalidas, $decimalplaces),
            number_format($TotExistencias, $decimalplaces),
            number_format($TotEnTransito, $decimalplaces),
            number_format($TotExistencias - ($TotEnTransito + $TotEmbarque + $TotPedVenta), $decimalplaces),
            '',
            '$' . number_format($TotCosto, 2)
        );
    }
    
    echo '</table>';
    echo "</div>"; // Div Tabla Responsive

    if (isset($_POST['PrintEXCEL'])) {
        exit();
    }
} elseif (isset($_POST['PrintPDF']) and isset($_POST['FromCriteria']) and strlen($_POST['FromCriteria']) >= 1 and isset($_POST['ToCriteria']) and strlen($_POST['ToCriteria']) >= 1) {
    include 'includes/PDFStarter.php';

    $FontSize = 12;
    $pdf->addinfo('Title', _('Listado Antiguedad de Saldos'));
    $pdf->addinfo('Subject', _('Antiguedad Saldos Proveedores'));

    $PageNumber  = 0;
    $line_height = 12;

    /* Now figure out the aged analysis for the Supplier range under review */

    if ($_POST['All_Or_Overdues'] == 'All') {
        $SQL = "SELECT suppliers.supplierid, suppliers.suppname, currencies.currency, paymentterms.terms,
                SUM(supptrans.ovamount + supptrans.ovgst  - supptrans.alloc) as balance,
                SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                ELSE
                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                END) AS due,
                Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                ELSE
                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                END) AS overdue1,
                Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                ELSE
                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                END) AS overdue2
                FROM suppliers, paymentterms, currencies,  supptrans JOIN sec_unegsxuser ON supptrans.tagref = sec_unegsxuser.tagref JOIN tags ON sec_unegsxuser.tagref=tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
                WHERE suppliers.paymentterms = paymentterms.termsindicator
                AND suppliers.currcode = currencies.currabrev
                AND suppliers.supplierid = supptrans.supplierno
                AND suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
                AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
                AND  suppliers.currcode ='" . $_POST['Currency'] . "'
                AND (supptrans.tagref =" . $_POST['unidadnegocio'] . " or " . $_POST['unidadnegocio'] . "=0)
                GROUP BY suppliers.supplierid,
                    suppliers.suppname,
                    currencies.currency,
                    paymentterms.terms,
                    paymentterms.daysbeforedue,
                    paymentterms.dayinfollowingmonth
                HAVING Sum(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) <>0";
    } else {
        $SQL = "SELECT suppliers.supplierid,
                suppliers.suppname,
                currencies.currency,
                paymentterms.terms,
                SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
                SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                ELSE
                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                END) AS due,
                Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                ELSE
                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                END) AS overdue1,
                SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                ELSE
                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                END) AS overdue2
                FROM suppliers,
                    paymentterms,
                    currencies,
                    supptrans JOIN sec_unegsxuser ON supptrans.tagref = sec_unegsxuser.tagref JOIN tags ON sec_unegsxuser.tagref=tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
                WHERE suppliers.paymentterms = paymentterms.termsindicator
                AND suppliers.currcode = currencies.currabrev
                and suppliers.supplierid = supptrans.supplierno
                AND suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
                AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
                AND suppliers.currcode ='" . $_POST['Currency'] . "'
                AND (supptrans.tagref =" . $_POST['unidadnegocio'] . " or " . $_POST['unidadnegocio'] . "=0)
                GROUP BY suppliers.supplierid,
                    suppliers.suppname,
                    currencies.currency,
                    paymentterms.terms,
                    paymentterms.daysbeforedue,
                    paymentterms.dayinfollowingmonth
                HAVING Sum(IF (paymentterms.daysbeforedue > 0,
                CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END,
                CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END)) > 0";
    }

    $SupplierResult = DB_query($SQL, $db, '', '', false, false); /* dont trap errors */

    if (DB_error_no($db) != 0) {
        $title = _('Analisis de Antiguedad de Saldos Proveedores') . ' - ' . _('Reporte de Problema');
        include "includes/header.inc";
        prnMsg(_('The Supplier details could not be retrieved by the SQL because') . ' ' . DB_error_msg($db), 'error');
        echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Regrsar al Menu...') . '</a>';
        if ($debug == 1) {
            echo "<br>$SQL";
        }
        include 'includes/footer.inc';
        exit();
    }

    include 'includes/PDFAgedSuppliersPageHeader.inc';
    $TotBal  = 0;
    $TotDue  = 0;
    $TotCurr = 0;
    $TotOD1  = 0;
    $TotOD2  = 0;

    while ($AgedAnalysis = DB_fetch_array($SupplierResult, $db)) {
        $DisplayDue      = number_format($AgedAnalysis['due'] - $AgedAnalysis['overdue1'], 2);
        $DisplayCurrent  = number_format($AgedAnalysis['balance'] - $AgedAnalysis['due'], 2);
        $DisplayBalance  = number_format($AgedAnalysis['balance'], 2);
        $DisplayOverdue1 = number_format($AgedAnalysis['overdue1'] - $AgedAnalysis['overdue2'], 2);
        $DisplayOverdue2 = number_format($AgedAnalysis['overdue2'], 2);

        $TotBal += $AgedAnalysis['balance'];
        $TotDue += ($AgedAnalysis['due'] - $AgedAnalysis['overdue1']);
        $TotCurr += ($AgedAnalysis['balance'] - $AgedAnalysis['due']);
        $TotOD1 += ($AgedAnalysis['overdue1'] - $AgedAnalysis['overdue2']);
        $TotOD2 += $AgedAnalysis['overdue2'];

        $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 220 - $Left_Margin, $FontSize, $AgedAnalysis['supplierid'] . ' - ' . $AgedAnalysis['suppname'], 'left');
        $LeftOvers = $pdf->addTextWrap(220, $YPos, 60, $FontSize, $DisplayBalance, 'right');
        $LeftOvers = $pdf->addTextWrap(280, $YPos, 60, $FontSize, $DisplayCurrent, 'right');
        $LeftOvers = $pdf->addTextWrap(340, $YPos, 60, $FontSize, $DisplayDue, 'right');
        $LeftOvers = $pdf->addTextWrap(400, $YPos, 60, $FontSize, $DisplayOverdue1, 'right');
        $LeftOvers = $pdf->addTextWrap(460, $YPos, 60, $FontSize, $DisplayOverdue2, 'right');

        $YPos -= $line_height;
        if ($YPos < $Bottom_Margin + $line_height) {
            include 'includes/PDFAgedSuppliersPageHeader.inc';
        }

        if ($_POST['DetailedReport'] == 'Yes') {
            $FontSize = 6;
            /* draw a line under the Supplier aged analysis */
            $pdf->line($Page_Width - $Right_Margin, $YPos + 10, $Left_Margin, $YPos + 10);

            $sql = "SELECT systypescat.typename, supptrans.suppreference, supptrans.trandate,
                   (supptrans.ovamount + supptrans.ovgst - supptrans.alloc) as balance,
                   CASE WHEN paymentterms.daysbeforedue > 0 THEN
                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                   ELSE
                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                   END AS due,
                   CASE WHEN paymentterms.daysbeforedue > 0 THEN
                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue    AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                   ELSE
                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                   END AS overdue1,
                   CASE WHEN paymentterms.daysbeforedue > 0 THEN
                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                   ELSE
                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
                   END AS overdue2
                   FROM suppliers,
                    paymentterms,
                    systypescat, supptrans JOIN sec_unegsxuser ON supptrans.tagref = sec_unegsxuser.tagref JOIN tags ON sec_unegsxuser.tagref=tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
                   WHERE systypescat.typeid = supptrans.type
                   AND (supptrans.tagref =" . $_POST['unidadnegocio'] . " or " . $_POST['unidadnegocio'] . "=0)
                   AND suppliers.paymentterms = paymentterms.termsindicator
                   AND suppliers.supplierid = supptrans.supplierno
                   AND ABS(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) >0.009
                   AND supptrans.settled = 0
                   AND supptrans.supplierno = '" . $AgedAnalysis["supplierid"] . "'";

            $DetailResult = DB_query($sql, $db, '', '', false, false); /* dont trap errors - trapped below */
            if (DB_error_no($db) != 0) {
                $title = _('Aged Supplier Account Analysis - Problem Report');
                include 'includes/header.inc';
                echo '<br>' . _('The details of outstanding transactions for Supplier') . ' - ' . $AgedAnalysis['supplierid'] . ' ' . _('could not be retrieved because') . ' - ' . DB_error_msg($db);
                echo "<br><a href='$rootpath/index.php'>" . _('Back to the menu') . '</a>';
                if ($debug == 1) {
                    echo '<br>' . _('The SQL that failed was') . '<br>' . $sql;
                }
                include 'includes/footer.inc';
                exit();
            }

            while ($DetailTrans = DB_fetch_array($DetailResult)) {
                $LeftOvers       = $pdf->addTextWrap($Left_Margin + 5, $YPos, 60, $FontSize, $DetailTrans['typename'], 'left');
                $LeftOvers       = $pdf->addTextWrap($Left_Margin + 65, $YPos, 50, $FontSize, $DetailTrans['suppreference'], 'left');
                $DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);
                $LeftOvers       = $pdf->addTextWrap($Left_Margin + 105, $YPos, 70, $FontSize, $DisplayTranDate, 'left');

                $DisplayDue      = number_format($DetailTrans['due'] - $DetailTrans['overdue1'], 2);
                $DisplayCurrent  = number_format($DetailTrans['balance'] - $DetailTrans['due'], 2);
                $DisplayBalance  = number_format($DetailTrans['balance'], 2);
                $DisplayOverdue1 = number_format($DetailTrans['overdue1'] - $DetailTrans['overdue2'], 2);
                $DisplayOverdue2 = number_format($DetailTrans['overdue2'], 2);

                $LeftOvers = $pdf->addTextWrap(220, $YPos, 60, $FontSize, $DisplayBalance, 'right');
                $LeftOvers = $pdf->addTextWrap(280, $YPos, 60, $FontSize, $DisplayCurrent, 'right');
                $LeftOvers = $pdf->addTextWrap(340, $YPos, 60, $FontSize, $DisplayDue, 'right');
                $LeftOvers = $pdf->addTextWrap(400, $YPos, 60, $FontSize, $DisplayOverdue1, 'right');
                $LeftOvers = $pdf->addTextWrap(460, $YPos, 60, $FontSize, $DisplayOverdue2, 'right');

                $YPos -= $line_height;
                if ($YPos < $Bottom_Margin + $line_height) {
                    $PageNumber++;
                    include 'includes/PDFAgedSuppliersPageHeader.inc';
                    $FontSize = 6;
                }
            } /* end while there are detail transactions to show */
            /* draw a line under the detailed transactions before the next Supplier aged analysis */
            $pdf->line($Page_Width - $Right_Margin, $YPos + 10, $Left_Margin, $YPos + 10);
            $FontSize = 8;
        } /* Its a detailed report */
    } /* end Supplier aged analysis while loop */

    $YPos -= $line_height;
    if ($YPos < $Bottom_Margin + (2 * $line_height)) {
        $PageNumber++;
        include 'includes/PDFAgedSuppliersPageHeader.inc';
    } elseif ($_POST['DetailedReport'] == 'Yes') {
        // dont do a line if the totals have to go on a new page
        $pdf->line($Page_Width - $Right_Margin, $YPos + 10, 220, $YPos + 10);
    }

    $DisplayTotBalance  = number_format($TotBal, 2);
    $DisplayTotDue      = number_format($TotDue, 2);
    $DisplayTotCurrent  = number_format($TotCurr, 2);
    $DisplayTotOverdue1 = number_format($TotOD1, 2);
    $DisplayTotOverdue2 = number_format($TotOD2, 2);

    $LeftOvers = $pdf->addTextWrap(220, $YPos, 60, $FontSize, $DisplayTotBalance, 'right');
    $LeftOvers = $pdf->addTextWrap(280, $YPos, 60, $FontSize, $DisplayTotCurrent, 'right');
    $LeftOvers = $pdf->addTextWrap(340, $YPos, 60, $FontSize, $DisplayTotDue, 'right');
    $LeftOvers = $pdf->addTextWrap(400, $YPos, 60, $FontSize, $DisplayTotOverdue1, 'right');
    $LeftOvers = $pdf->addTextWrap(460, $YPos, 60, $FontSize, $DisplayTotOverdue2, 'right');

    $YPos -= $line_height;
    $pdf->line($Page_Width - $Right_Margin, $YPos, 220, $YPos);

    $buf = $pdf->output();
    $len = strlen($buf);
    header('Content-type: application/pdf');
    header("Content-Length: $len");
    header('Content-Disposition: inline; filename=AgedSuppliers.pdf');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    $pdf->stream();
} else {
    /* The option to print PDF was not hit */
} /* end of else not PrintPDF */



include 'includes/footer_Index.inc';
?>
<script type="text/javascript">
function fnSelectProd(valor='') {

  $("#sugerencia-articulo").hide();
  $("#sugerencia-articulo").empty();
  $("#claveprod").val(""+valor);
  $("#buscarProd").val(""+valor);

}
$(function(){

  window.partidas= new Array();
  window.clavesProd= new Array();
  window.descripcionProd= new Array();
  window.partidasArt= new Array();

   <?php 

      foreach ($clavesProd as $ad => $val) {
  ?>
      window.clavesProd.push(<?php echo "'".$val."'"; ?>);
      window.descripcionProd.push(<?php echo "'".$descripcionProd[$ad]."'"; ?>);
      window.partidasArt.push(<?php echo "'".$partidasArt[$ad]."'"; ?>);
  <?php
    }

  ?>
  
  partidas.push({'label':'Seleccionar', 'title':'Seleccionar', 'value':-1 });

<?php 
 
$SQL="SELECT DISTINCT 
                tb_partida_articulo.partidaEspecifica AS partida  
                FROM  tb_partida_articulo 
                INNER JOIN stockmaster ON  tb_partida_articulo.eq_stockid=stockmaster.eq_stockid 
                WHERE partidaEspecifica NOT LIKE '5%'
                AND stockmaster.mbflag='B'
                ORDER BY partidaEspecifica ";

$TransResult = DB_query($SQL, $db);

while ($myrow = DB_fetch_array($TransResult)) {
   $datos= "label:".$myrow ['partida'].",title:".$myrow ['partida'].",value:".$myrow ['partida'];
   ?>
partidas.push({<?php echo $datos; ?>});
<?php
    //array( 'value' => $myrow ['partida'], 'texto' => $myrow ['partida'] );
}


?>
    

    fnFormatoSelectGeneral(".xCategoria");
    fnFormatoSelectGeneral(".razonsocial");
    fnFormatoSelectGeneral(".unidadnegocio");
    fnFormatoSelectGeneral(".almacen");
    fnFormatoSelectGeneral(".DetailedReport");
    fnFormatoSelectGeneral(".tipoinventario");
    fnFormatoSelectGeneral(".xOptimo");
    fnFormatoSelectGeneral(".stocktypeflag");
    fnFormatoSelectGeneral(".claveprod");
    fnFormatoSelectGeneral(".SoloExistencias");
    fnFormatoSelectGeneral(".UnidadEjecutora");

     $("#selPartida").multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });
   
    $("#selPartida").multiselect('dataprovider', window.partidas);

    $('.multiselect-container').css({
        'max-height': "220px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });

<?php if (isset( $_POST['selPartida'])){?>  
  val= new Array();
  val.push(<?php echo $_POST['selPartida'];?>);
  $("#selPartida").selectpicker('val',val);
  $("#selPartida").multiselect('refresh');
  $('.selPartida').css("display", "none");
<?php }

?>


   $('#form-search .componenteCalendarioClase').on('dp.change',function(e){
        var $that = $(this),
            $input = $that.find('input[id]'),
            $fechaTermino = $('#form-search .componenteCalendarioClase').eq(1);
        if($input.attr('id') == 'dateDesde'){
            
            window.minDate = e;
            $fechaTermino.data('DateTimePicker').minDate(window.minDate.date);

        }else if($input.attr('id') == 'dateDesde1'){
            // var fechaDesdeVal = $('#form-search #fechaIni').val(), fechaHastaVal = $('#form-search #fechaFin').val(), formatoDate = e.date._f;
            // var fechaDesde = moment(fechaDesdeVal, formatoDate), fechaHasta = moment(fechaHastaVal, formatoDate);
            // diferencias = fechaHasta.diff(fechaDesde,'days');
            // if(diferencias < 0){
            //     muestraModalGeneral(3,'Error de Datos','La fecha <strong> Hasta </strong>, no puede ser <strong>menor</strong> a la fecha <strong> Desde</strong>');
            //     $fechaTermino.find('input[id]').val(e.date._i);
            //     return false;
            // }
        }

    });

    <?php
      if(isset($_POST['claveprod'])){ ?>
        $("#claveprod").val("<?php echo $_POST['claveprod']; ?>");
        $("#buscarProd").val("<?php echo $_POST['claveprod']; ?>");
    <?php
     }?>



         //inicio  fin autoacompletador de input
    $("#buscarProd").keyup(function(){
        var retorno="<ul id='articulos-lista-consolida'>";
        if($(this).val()!=''){
            // dataObj = {
            //     proceso: 'buscar-articulo',
            //     articulorequisicion: $(this).val(),
            // };

            // $.ajax({
            //     type: "POST",
            //     dataType:"json",
            //     url: "",
            //     data: dataObj,

            //     success: function(res){
            //         $("#sugerencia-articulo").show();
            //         $("#sugerencia-articulo").empty();
            //         $("#sugerencia-articulo").append(res.contenido);

            //     }

            // });
           
            var buscar = $(this).val(); 
            var buscarCoicidencia = new RegExp(buscar , "i");
            var arr = jQuery.map(window.clavesProd, function (value,index) {
                
                return value.match(buscarCoicidencia) ? index : null;
            });
            
            for (a=0; a<arr.length;a++){
              val=arr[a];
              valPartida=$("#selPartida").val();

              if(valPartida==window.partidasArt[val]){
            
              retorno+="<li onClick='fnSelectProd(\""+window.clavesProd[val]+"\")'><a href='#'>"+window.clavesProd[val]+" - "+window.descripcionProd[val]+"</a></li>";
                }
            }
            retorno+="</ul>"
            $("#sugerencia-articulo").show();
            $("#sugerencia-articulo").empty();
            $("#sugerencia-articulo").append(retorno);
            
        }
    });// fin autoacompletador de input

    $(document).on('change','#selPartida',function(){
      $("#sugerencia-articulo").hide();
      $("#sugerencia-articulo").empty();
      $("#buscarProd").val("");
    });


  });


</script>
