<?php
/**
 * Reporte de Cuentas Contables
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 27/11/2017
 * Fecha Modificación: 27/11/2017
 * Reporte que muestra las cuentas contables con su información de cargos y abonos con diferencias
 */

include('includes/session.inc');
$funcion=276;
$title = traeNombreFuncion($funcion, $db);
include('includes/SecurityFunctions.inc');
if (isset($_POST['PrintEXCEL'])) {
} else {
    include('includes/header.inc');
}
//include('includes/GLPostings.inc');
    
if ((isset($_GET['GLCode'])) and ($_GET['GLCode'] != "")) {
    $_POST['GLCode'] = $_GET['GLCode'];
}

if ((isset($_GET['Account'])) and ($_GET['Account'] != "")) {
    $_POST['Account'] = $_GET['Account'];
}

if ((isset($_GET['cbRangoDe'])) and ($_GET['cbRangoDe'] != "")) {
    $_POST['cbRangoDe'] = $_GET['cbRangoDe'];
}

if ((isset($_GET['cbRangoA'])) and ($_GET['cbRangoA'] != "")) {
    $_POST['cbRangoA'] = $_GET['cbRangoA'];
}


if ((isset($_POST['Account']) && ($_POST['Account'] != ""))) {
    $SelectedAccount = $_POST['Account'];
} elseif (isset($_GET['Account'])) {
    $SelectedAccount = $_GET['Account'];
    $_POST['Account'] = $SelectedAccount;
}

if ($_POST['GLCode'] != '') {
    $SelectedAccount = $_POST['GLCode'];
}

if ($_POST['cbRangoDe']!=''  and $_POST['cbRangoA']!='') {
    $rangoDe = $_POST['cbRangoDe'];
    $rangoA = $_POST['cbRangoA'];
}

if (isset($_POST['legalid'])) {
    $SelectedLegal = $_POST['legalid'];
} elseif (isset($_GET['legalid'])) {
    $SelectedLegal = $_GET['legalid'];
    $_POST['legalid'] = $SelectedLegal;
}

if (isset($_POST['tag'])) {
    $SelectedTag = $_POST['tag'];
} elseif (isset($_GET['tag'])) {
    $SelectedTag = $_GET['tag'];
    $_POST['tag'] = $SelectedTag;
}

if (!isset($_POST['txtFechaDesde'])) {
    $_POST['txtFechaDesde'] = date('d-m-Y');
}
if (!isset($_POST['txtFechaHasta'])) {
    $_POST['txtFechaHasta'] = date('d-m-Y');
}

if (!isset($_POST['PrintEXCEL'])) {
    //echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Consulta de Cuentas Contables X Tipo de Poliza') . '" alt="">' . ' ' . _('Consulta de Cuentas Contables X Tipo de Poliza') . '</p>';
    
    //echo '<div class="page_help_text">' . _('Utiliza la tecla shift presionada para seleccionar varios periodos...') . '</div><br>';
    
    echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
    ?>
    <div align="left">
      <!--Panel Busqueda-->
      <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
          <h4 class="panel-title row">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne" style="margin-left: 20px;">
                ENCABEZADO
              </a>
          </h4>
        </div>
        <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
          <div class="panel-body">
            <div class="col-md-4 col-xs-12">
                <div class="row" style="text-align: left;">
                    <div class="col-xs-12 col-md-12" >
                        <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                            <span><label>Tipo Póliza: </label></span>
                        </div>
                        <div class="col-xs-9 col-md-9">
                            <select id="TipoPoliza" name="TipoPoliza" class="form-control TipoPoliza">
                            <option value='TODOS'>Seleccionar...</option>
                            <?php
                            $SQL = "SELECT typeid as value, CONCAT(typeid, ' - ', typename) as texto FROM systypescat WHERE nu_activo = 1 ORDER BY typeid";
                            $result = DB_query($SQL, $db);
                            while ($myrow = DB_fetch_array($result)) {
                                if (isset($_POST['TipoPoliza']) and $myrow[0]==$_POST['TipoPoliza']) {
                                    echo "<option selected value='";
                                } else {
                                    echo "<option value='";
                                }
                                echo $myrow[0] . "'>".$myrow[1].'</option>';
                            }
                            ?>
                            </select>
                        </div>
                    </div>
                </div>
                <br>
                <component-date-label label="Desde: " id="txtFechaDesde" name="txtFechaDesde" value="<?php echo $_POST['txtFechaDesde']; ?>"></component-date-label>
            </div>
            <div class="col-md-4 col-xs-12">
                <div class="row" style="text-align: left;">
                    <div class="col-xs-12 col-md-12" >
                        <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                            <span><label>Dependencia: </label></span>
                        </div>
                        <div class="col-xs-9 col-md-9">
                            <select id="legalid" name="legalid" class="form-control legalid">
                            <?php
                            $SQL = "SELECT legalbusinessunit.legalid, CONCAT(legalbusinessunit.legalid, ' - ', legalbusinessunit.legalname) as legalname 
				            FROM sec_unegsxuser u, tags t 
				            JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid 
				            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' 
				            GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname 
				            ORDER BY legalbusinessunit.legalid ";
                            $result = DB_query($SQL, $db);
                            while ($myrow=DB_fetch_array($result)) {
                                if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]) {
                                    echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalname'] . '</option>';
                                } else {
                                    echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalname'] . '</option>';
                                }
                            }
                            ?>
                            </select>
                        </div>
                    </div>
                </div>
                <br>
                <component-date-label label="Hasta: " id="txtFechaHasta" name="txtFechaHasta" value="<?php echo $_POST['txtFechaHasta']; ?>"></component-date-label>
            </div>
            <div class="col-md-4 col-xs-12">
                <div class="row" style="text-align: left;">
                    <div class="col-xs-12 col-md-12" >
                        <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                            <span><label>UR: </label></span>
                        </div>
                        <div class="col-xs-9 col-md-9">
                            <select id="tag" name="tag" class="form-control tag">
                            <option value=0>Seleccionar...</option>
                            <?php
                            $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
				            FROM sec_unegsxuser u, tags t 
				            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' 
				            ORDER BY t.tagref ";
                            $result = DB_query($SQL, $db);
                            while ($myrow=DB_fetch_array($result)) {
                                if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']) {
                                    echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'] . '</option>';
                                } else {
                                    echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'] . '</option>';
                                }
                            }
                            ?>
                            </select>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row" style="text-align: left;">
                    <div class="col-xs-12 col-md-12" >
                        <div class="col-xs-3 col-md-3" style="vertical-align: middle;">
                            <span><label>Detalle: </label></span>
                        </div>
                        <div class="col-xs-9 col-md-9">
                            <select id="DetailedReport" name="DetailedReport" class="form-control DetailedReport">
                            <?php
                            if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'XCuenta') {
                                echo "<option selected value='XCuenta'>" . _('Por Cuenta contable');
                            } else {
                                echo "<option value='XCuenta'>" . _('Por Cuenta Contable');
                            }
                            // if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'XFolio')
                            // echo "<option selected value='XFolio'>" . _('X Folio');
                            // else
                            // echo "<option value='XFolio'>" . _('X Folio');
                            if (isset($_POST['DetailedReport']) and $_POST['DetailedReport'] == 'XGrupoContable') {
                                echo "<option selected value='XGrupoContable'>" . _('Por Grupo Contable');
                            } else {
                                echo "<option value='XGrupoContable'>" . _('Por Grupo Contable');
                            }
                            ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12 col-xs-12" align="center">
        <component-button type="submit" id="Show" name="Show" value="Filtrar" class="glyphicon glyphicon-search"></component-button>
        <!-- <component-button type="submit" id="PrintEXCEL" name="PrintEXCEL" value="Exportar Excel" class="glyphicon glyphicon-save"></component-button> -->
    </div>
    <?php
}

if (isset($_POST['Show'])or isset($_POST['PrintEXCEL'])) {
    if (isset($_POST['PrintEXCEL'])) {
        header("Content-type: application/ms-excel");
        header("Content-Disposition: attachment; filename=Consulta de Cuentas Contables");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
        echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
        echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
    }
    
    //	if ((!isset($SelectedAccount)) || (strlen($SelectedAccount) < 3) || (!isset($rangoDe)) || ($rangoDe=='') || (!isset($rangoA)) || ($rangoA=='')){

    if ($_POST['DetailedReport'] == 'XGrupoContable') {
        $PandLAccount=false;
        
        $sql= "SELECT chartmaster.group_, 
		sum(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as cargos,
		sum(CASE WHEN amount < 0 THEN amount ELSE 0 END) as abonos,
		sum(amount) as amount, periodno, chartmaster.naturaleza
		FROM gltrans 
		JOIN tags ON gltrans.tag = tags.tagref
		inner join systypescat on gltrans.type=systypescat.typeid
		JOIN areas ON tags.areacode = areas.areacode
		JOIN regions ON areas.regioncode = regions.regioncode
		JOIN departments ON tags.u_department=departments.u_department
		JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
		JOIN chartmaster ON gltrans.account = chartmaster.accountcode
		WHERE (tag = ".$_POST['tag']." or ".$_POST['tag']."=0)
		AND systypescat.typeid=gltrans.type
		AND tags.legalid = ".$_POST['legalid']."";

        if (!empty($_POST['txtFechaDesde']) && !empty($_POST['txtFechaHasta'])) {
            $sql .= " AND (DATE_FORMAT(gltrans.trandate, '%d-%m-%Y') >= '".$_POST['txtFechaDesde']."' AND DATE_FORMAT(gltrans.trandate, '%d-%m-%Y') <= '".$_POST['txtFechaHasta']."')";
        } elseif (!empty($_POST['txtFechaDesde'])) {
            $sql .= " AND DATE_FORMAT(gltrans.trandate, '%d-%m-%Y') >= '".$_POST['txtFechaDesde']."' ";
        } elseif (!empty($_POST['txtFechaHasta'])) {
            $sql .= " AND DATE_FORMAT(gltrans.trandate, '%d-%m-%Y') <= '".$_POST['txtFechaHasta']."' ";
        }

        if ($_POST['TipoPoliza'] != 'TODOS') {
            $sql= $sql." AND gltrans.type = '".$_POST['TipoPoliza']."'";
        }
        $sql= $sql . " GROUP BY chartmaster.group_, chartmaster.naturaleza";
        $sql= $sql." ORDER BY chartmaster.group_";
    
        if ($SelectedAccount != '') {
            $ErrMsg = _('Las transacciones para la cuenta') . ' ' . $SelectedAccount . ' ' . _('no pudieron ser recuperadas porque') ;
        } else {
            $ErrMsg = _('Las transacciones para la cuenta') . ' ' . $rangoDe . ' ' . $rangoA . ' ' . _('no pudieron ser recuperadas porque') ;
        }
        
        //echo $sql;
        $TransResult = DB_query($sql, $db, $ErrMsg);
    
        echo '<table class="table table-bordered">';
    
        $TableHeader = "<tr class='header-verde'>
				<th style='text-align: center;'>" . _('Grupo Contable') . "</th>
				<th style='text-align: center;'>" . _('Cargos') . "</th>
				<th style='text-align: center;'>" . _('Abonos') . "</th>
				<th style='text-align: center;'>" . _('Monto') . "</th>
				<th style='text-align: center;'>" . _('Saldo') . "</th>
				</tr>";
    
        echo $TableHeader;
    
        
        $RunningTotal = 0;
        
        
        $PeriodTotal = 0;
        $PeriodNo = -9999;
        $ShowIntegrityReport = false;
        $j = 1;
        $k=0;
    
        $totalcuentacargos = 0;
        $totalcuentaabonos = 0;
        $totalcuenta = 0;
        
        $saldoxcuentacargos = 0;
        $saldoxcuentaabonos = 0;
    
        while ($myrow=DB_fetch_array($TransResult)) {
            echo '<tr>';
    
            $RunningTotal += $myrow['amount'];
            $PeriodTotal  += $myrow['amount'];
            
            $saldoxcuenta += ($myrow['amount']);
    
            if ($myrow['amount']>=0) {
                $DebitAmount = number_format($myrow['amount'], 2);
                $CreditAmount = '';
                $saldoxcuentacargos = $saldoxcuentacargos + $myrow['amount'];
            } else {
                $CreditAmount = number_format(-$myrow['amount'], 2);
                $DebitAmount = '';
                $saldoxcuentaabonos = $saldoxcuentaabonos + ($myrow['amount']*-1);
            }
            
            //$FormatedTranDate = ConvertSQLDate($myrow['trandate']);
            
            //$URL_to_TransDetail = $rootpath . '/GLTransInquiryV2.php?' . SID . '&TypeID=' . $myrow['type'] . '&TransNo=' . $myrow['typeno'];
                    
            printf(
                "<td>%s</td>
				<td class=number style='text-align: right;'>%s</td>
				<td class=number style='text-align: right;'>%s</td>
				<td class=number nowrap style='text-align: right;'><b>%s</b></td>
				<td class=number nowrap style='text-align: right;'><b>%s</b></td>
				</tr>",
                $myrow['group_'],
                number_format($myrow['cargos'], 2),
                number_format($myrow['abonos'], 2),
                number_format($myrow['amount'], 2),
                number_format($saldoxcuenta, 2)
            );
        }
        
        printf(
            "<td>%s</td>
				
				<td class=number style='text-align: right;'>%s</td>
				<td class=number style='text-align: right;'>%s</td>
				<td class=number nowrap style='text-align: right;'><b></b></td>
				<td class=number nowrap style='text-align: right;'><b>%s</b></td>
				</tr>",
            "TOTALES",
            number_format($saldoxcuentacargos, 2),
            number_format($saldoxcuentaabonos, 2),
            number_format($saldoxcuentacargos-$saldoxcuentaabonos, 2)
        );
    
        echo '</table>';
    } else {
        $PandLAccount=false;
            
        $sql= "SELECT gltrans.account, chartmaster.accountname, 
		sum(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as cargos,
		sum(CASE WHEN amount < 0 THEN amount ELSE 0 END) as abonos,
		sum(amount) as amount, periodno, chartmaster.naturaleza
		FROM gltrans 
		JOIN tags ON gltrans.tag = tags.tagref
		inner join systypescat on gltrans.type=systypescat.typeid
		JOIN areas ON tags.areacode = areas.areacode
		JOIN regions ON areas.regioncode = regions.regioncode
		JOIN departments ON tags.u_department=departments.u_department
		JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
		JOIN chartmaster ON gltrans.account = chartmaster.accountcode
		WHERE (tag = ".$_POST['tag']." or ".$_POST['tag']."=0)
		AND systypescat.typeid=gltrans.type
		AND tags.legalid = ".$_POST['legalid']."";

        if (!empty($_POST['txtFechaDesde']) && !empty($_POST['txtFechaHasta'])) {
            $sql .= " AND (DATE_FORMAT(gltrans.trandate, '%d-%m-%Y') >= '".$_POST['txtFechaDesde']."' AND DATE_FORMAT(gltrans.trandate, '%d-%m-%Y') <= '".$_POST['txtFechaHasta']."')";
        } elseif (!empty($_POST['txtFechaDesde'])) {
            $sql .= " AND DATE_FORMAT(gltrans.trandate, '%d-%m-%Y') >= '".$_POST['txtFechaDesde']."' ";
        } elseif (!empty($_POST['txtFechaHasta'])) {
            $sql .= " AND DATE_FORMAT(gltrans.trandate, '%d-%m-%Y') <= '".$_POST['txtFechaHasta']."' ";
        }
            
        if ($_POST['TipoPoliza'] != 'TODOS') {
            $sql= $sql." AND gltrans.type = '".$_POST['TipoPoliza']."'";
        }
        $sql= $sql . " GROUP BY gltrans.account, chartmaster.accountname, chartmaster.naturaleza";
        $sql= $sql." ORDER BY gltrans.account, periodno, gltrans.trandate, gltrans.typeno";
    
        if ($SelectedAccount != '') {
            $ErrMsg = _('Las transacciones para la cuenta') . ' ' . $SelectedAccount . ' ' . _('no pudieron ser recuperadas porque') ;
        } else {
            $ErrMsg = _('Las transacciones para la cuenta') . ' ' . $rangoDe . ' ' . $rangoA . ' ' . _('no pudieron ser recuperadas porque') ;
        }
        
        //echo "<br><pre>sql:".$sql;
        $TransResult = DB_query($sql, $db, $ErrMsg);
    
        echo '<table class="table table-bordered">';
    
        $TableHeader = "<tr class='header-verde'>
				<th style='text-align: center;'>" . _('Cuenta') . "</th>
				<th style='text-align: center;'>" . _('Nombre') . "</th>
				<th style='text-align: center;'>" . _('Cargos') . "</th>
				<th style='text-align: center;'>" . _('Abonos') . "</th>
				<th style='text-align: center;'>" . _('Monto') . "</th>
				<th style='text-align: center;'>" . _('Saldo') . "</th>
				</tr>";
    
        echo $TableHeader;
    
        $RunningTotal = 0;
        
        $PeriodTotal = 0;
        $PeriodNo = -9999;
        $ShowIntegrityReport = false;
        $j = 1;
        $k=0;
    
        $totalcuentacargos = 0;
        $totalcuentaabonos = 0;
        $totalcuenta = 0;
        
        $saldoxcuentacargos = 0;
        $saldoxcuentaabonos = 0;
    
        while ($myrow=DB_fetch_array($TransResult)) {
            echo '<tr>';
    
            $RunningTotal += $myrow['amount'];
            $PeriodTotal  += $myrow['amount'];
            
            //LLEVA EL SALDO DE ESTA CUENTA...
            //$saldoxcuenta += ($myrow['amount']*$myrow['naturaleza']);
            $saldoxcuenta += ($myrow['amount']);
    
            if ($myrow['amount']>=0) {
                $DebitAmount = number_format($myrow['amount'], 2);
                $CreditAmount = '';
                $saldoxcuentacargos = $saldoxcuentacargos + $myrow['amount'];
            } else {
                $CreditAmount = number_format(-$myrow['amount'], 2);
                $DebitAmount = '';
                $saldoxcuentaabonos = $saldoxcuentaabonos + ($myrow['amount']*-1);
            }
            
            //$FormatedTranDate = ConvertSQLDate($myrow['trandate']);
            
            //$URL_to_TransDetail = $rootpath . '/GLTransInquiryV2.php?' . SID . '&TypeID=' . $myrow['type'] . '&TransNo=' . $myrow['typeno'];
                    
            printf(
                "<td>%s</td>
				<td>%s</td>
				<td class=number style='text-align: right;'>%s</td>
				<td class=number style='text-align: right;'>%s</td>
				<td class=number nowrap style='text-align: right;'><b>%s</b></td>
				<td class=number nowrap style='text-align: right;'><b>%s</b></td>
				</tr>",
                $myrow['account'],
                $myrow['accountname'], /*$DebitAmount, $CreditAmount,*/
                number_format($myrow['cargos'], 2),
                number_format($myrow['abonos'], 2),
                number_format($myrow['amount'], 2),
                number_format($saldoxcuenta, 2)
            );
        }
        
        printf(
            "<td>%s</td>
				<td>%s</td>
				<td class=number style='text-align: right;'>%s</td>
				<td class=number style='text-align: right;'>%s</td>
				<td class=number nowrap style='text-align: right;'><b></b></td>
				<td class=number nowrap style='text-align: right;'><b>%s</b></td>
				</tr>",
            "TOTALES",
            "",
            number_format($saldoxcuentacargos, 2),
            number_format($saldoxcuentaabonos, 2),
            number_format($saldoxcuentacargos-$saldoxcuentaabonos, 2)
        );
    
        echo '</table>';
    }
}

if (isset($ShowIntegrityReport) and $ShowIntegrityReport==true) {
    if (!isset($IntegrityReport)) {
        $IntegrityReport='';
    }
    prnMsg(_('Existen diferencias entre el detalle de las transacciones y la informacion del detalle de acumulados de la cuenta en ChartDetails') . '. ' . _('Un registro de las diferencias se muestra abajo'), 'warn');
    echo '<p>'.$IntegrityReport;
}

if (isset($_POST['PrintEXCEL'])) {
        exit();
}

include('includes/footer_Index.inc');

?>
<script type="text/javascript">
    // Aplicar formato del SELECT
    fnFormatoSelectGeneral(".TipoPoliza");
    fnFormatoSelectGeneral(".legalid");
    fnFormatoSelectGeneral(".tag");
    fnFormatoSelectGeneral(".DetailedReport");
</script>