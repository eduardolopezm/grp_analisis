<?php
/**
 * Reporte cuenta contables
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Reporte de Modificacion
 */

$PageSecurity = 8;
include('includes/session.inc');
$funcion=503;
include('includes/SecurityFunctions.inc');
$title = _('Consulta Transacciones de una Cuenta Contable');
if (isset($_POST['PrintEXCEL'])) {
} else {
    include('includes/header.inc');
}
include('includes/GLPostings.inc');
    
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
//-----Biviana---
if ($_POST['cbRangoDe']!=''  and $_POST['cbRangoA']!='') {
    $rangoDe = $_POST['cbRangoDe'];
    $rangoA = $_POST['cbRangoA'];
}
//------

if (isset($_POST['legalid'])) {
    $SelectedLegal = $_POST['legalid'];
} elseif (isset($_GET['legalid'])) {
    $SelectedLegal = $_GET['legalid'];
    $_POST['legalid'] = $SelectedLegal;
}

if (isset($_POST['xRegion'])) {
    $SelectedRegion = $_POST['xRegion'];
} elseif (isset($_GET['xRegion'])) {
    $SelectedRegion = $_GET['xRegion'];
    $_POST['xRegion'] = $SelectedRegion;
}

if (isset($_POST['tag'])) {
    $SelectedTag = $_POST['tag'];
} elseif (isset($_GET['tag'])) {
    $SelectedTag = $_GET['tag'];
    $_POST['tag'] = $SelectedTag;
}

//calcular periodos
if ($_POST['FromYear']) {
    $fromanio = $_POST['FromYear'];
    $frommes =  $_POST['FromMes'];
    $qry = "Select * FROM periods
			WHERE year(lastdate_in_period)=$fromanio
			and month(lastdate_in_period)=$frommes
			";
    $res = DB_query($qry, $db);
    //$reg = DB_fetch_array($res);
    if ($reg = DB_fetch_array($res)) {
        $_POST['FromPeriod'] = $reg['periodno'];
    } else {
        prnMsg(_('No existe periodo para ' . $frommes . '-' . $fromanio), 'warn');
        $_POST['FromPeriod'] = 0;
    }

    $toanio = $_POST['ToYear'];
    $tomes =  $_POST['ToMes'];
    $qry = "Select * FROM periods
			WHERE year(lastdate_in_period)=$toanio
			and month(lastdate_in_period)=$tomes
			";
    $res = DB_query($qry, $db);
    if ($reg = DB_fetch_array($res)) {
        $_POST['ToPeriod'] = $reg['periodno'];
    } else {
        prnMsg(_('No existe periodo para ' . $tomes . '-' . $toanio), 'warn');
        $_POST['ToPeriod'] = 0;
    }
}


if (isset($_POST['FromPeriod'])) {
    $SelectedPeriod = $_POST['FromPeriod'];
} elseif (isset($_GET['FromPeriod'])) {
    $SelectedPeriod = $_GET['FromPeriod'];
}

if (isset($_POST['FromPeriod'])) {
    $SelectedFromPeriod = $_POST['FromPeriod'];
} elseif (isset($_GET['FromPeriod'])) {
    $SelectedFromPeriod = $_GET['FromPeriod'];
    $_POST['FromPeriod'] = $SelectedFromPeriod;
}

if (isset($_POST['ToPeriod'])) {
    $SelectedToPeriod = $_POST['ToPeriod'];
} elseif (isset($_GET['ToPeriod'])) {
    $SelectedToPeriod = $_GET['ToPeriod'];
    $_POST['ToPeriod'] = $SelectedToPeriod;
    $_POST['Show']= "MostrarPantalla";
}

if (!isset($_POST['Tipo'])) {
    $_POST['Tipo']= "TODOS";
}

if (!isset($_POST['TipoPoliza'])) {
    $_POST['TipoPoliza']= "TODOS";
}


if (!isset($_POST['PrintEXCEL'])) {
    echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Consulta de Cuentas Contables') . '" alt="">' . ' ' . _('Consulta de Cuentas Contables') . '</p>';
    
    echo '<div class="page_help_text">' . _('Utiliza la tecla shift presionada para seleccionar varios periodos...') . '</div><br>';
    
    echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
    
    /*Dates in SQL format for the last day of last month*/
    $DefaultPeriodDate = Date('Y-m-d', Mktime(0, 0, 0, Date('m'), 0, Date('Y')));
    
    /*Show a form to allow input of criteria for TB to show */
    echo '<table style="margin:auto;">';
    /********************************************/
    /* SECCION DE BUSQUEDA DE CUENTAS CONTABLES */
    //echo '<tr><td></td>';
    // End select tag

    echo '<tr><td align=right><b>Busca Cta x Nombre:</b></td><td><input
		type=Text Name="GLManualSearch" Maxlength=40 size=40 VALUE='. $_POST['GLManualSearch'] .'  >
		<input type=submit name="SearchAccount" value="'._('Buscar').'"></td>';
    echo '</tr>';
    /********************************************/
    
    // End select tag

    if (!isset($_POST['GLManualCode'])) {
        $_POST['GLManualCode']='';
    }
    /*echo '<td  colspan=2><input class="number" type=Text Name="GLManualCode" Maxlength=17 size=17 onChange="inArray(this.value, GLCode.options,'.
		"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')"' .
			' VALUE='. $_POST['GLManualCode'] .'  > � -> </td>';*/

    $sql='SELECT accountcode,
				accountname, group_ as padre
			FROM chartmaster
			WHERE accountname like "%'.$_POST['GLManualSearch'].'%"
			ORDER BY group_, accountcode';
    
    $result=DB_query($sql, $db);
    //echo '<td></td><td><select name="GLCode" onChange="return assignComboToInput(this,'.'GLManualCode'.')">';
    echo '<td></td><td><select name="GLCode" >';
    echo "<option selected value=''>Seleccionar Cuenta...</option>";
    $cambioGrupo= "";
    
    while ($myrow=DB_fetch_array($result)) {
        if ($cambioGrupo <> $myrow['padre']) {
            echo '<option  value=0>****** ' .$myrow['padre'] . '</option>';
        }
        
        if (isset($_POST['GLCode']) and $_POST['GLCode']==$myrow['accountcode']) {
            echo '<option selected value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
        } else {
            echo '<option value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
        }
        
        $cambioGrupo = $myrow['padre'];
    }
    echo '</select></td>';
    /*********************************/
    echo '<tr><td align=right><b>O Num. de Cuenta</b></td></tr>';
        echo '<tr>
         <td>'._('Numero de Cuenta').":</td>
         <td><input type=text Name='Account' value='".$_POST['Account']."'></td></tr>";

    //***************-Rango De:----------------
    echo '<tr><td align=right><b>O Rango</b></td></tr>';
    echo '<tr><td>' . _('De') . ':</td><td><select name="cbRangoDe">';
    
    $sql='SELECT accountcode,
				accountname, group_ as padre
			FROM chartmaster
			ORDER BY group_, accountcode';
    
    $result=DB_query($sql, $db);
    $cambioGrupo= "";
    echo "<option selected value=''>Seleccionar Cuenta...</option>";
    
    while ($myrow=DB_fetch_array($result)) {
        if ($cambioGrupo <> $myrow['padre']) {
            echo '<option  value=0>****** ' .$myrow['padre'] . '</option>';
        }
        
        if ($myrow['accountcode'] == $_POST['cbRangoDe']) {
            echo '<option selected value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
        } else {
            echo '<option value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
        }
        
        $cambioGrupo= $myrow['padre'];
    }
    echo '</select></td></tr>';
    
    //---------------A:--cbRangoA---------------------
    echo '<tr><td>' . _('A') . ':</td><td><select name="cbRangoA" >';
    $sql='SELECT accountcode,
				accountname, group_ as padre
			FROM chartmaster
			ORDER BY group_, accountcode';

    $result=DB_query($sql, $db);
    $cambioGrupo= "";
    echo "<option selected value=''>Seleccionar Cuenta...</option>";
    
    while ($myrow=DB_fetch_array($result)) {
        if ($cambioGrupo <> $myrow['padre']) {
            echo '<option  value=0>****** ' .$myrow['padre'] . '</option>';
        }
        
        if ($myrow['accountcode'] == $_POST['cbRangoA']) {
            echo '<option selected value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
        } else {
            echo '<option value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
        }
        
        $cambioGrupo= $myrow['padre'];
    }
    
    echo '</select></td></tr>';

    //------------------Combo Tipo----------------------
    $sql = 'SELECT tipo,nombreMayor FROM chartTipos ORDER BY tipo';
    $result = DB_query($sql, $db);

    echo '<TR><TD>' . _('Tipo') . ':</TD><TD><SELECT NAME=Tipo>';
    echo "<option selected value='TODOS'>TODOS</option>";
    while ($myrow = DB_fetch_array($result)) {
        if (isset($_POST['Tipo']) and $myrow[0]==$_POST['Tipo']) {
            echo "<OPTION SELECTED VALUE='";
        } else {
            echo "<OPTION VALUE='";
        }
        echo $myrow[0] . "'>" . $myrow[1];
    }
    echo '</select></TD</TR>';
    //----------------------------
    
    //------------------Combo Tipo Polizas----------------------
    $sql = 'SELECT typeid, typename FROM systypescat ORDER BY typeid';
    $result = DB_query($sql, $db);

    echo '<TR><TD>' . _('Polizas Tipo') . ':</TD><TD><SELECT NAME="TipoPoliza">';
    echo "<option selected value='TODOS'>TODOS</option>";
    while ($myrow = DB_fetch_array($result)) {
        if (isset($_POST['TipoPoliza']) and $myrow[0]==$_POST['TipoPoliza']) {
            echo "<OPTION SELECTED VALUE='";
        } else {
            echo "<OPTION VALUE='";
        }
        echo $myrow[0] . "'>" . $myrow[0].' - '.$myrow[1].'</option>';
    }
    echo '</select></TD</TR>';
    //----------------------------
    
    //Select the razon social
    echo '<tr><td>'._('Seleccione Una Razon Social:').'<td><select name="legalid">';
        
    ///Pinta las razones sociales
    $SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
    $SQL = $SQL .   " FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
    $SQL = $SQL .   " WHERE u.tagref = t.tagref ";
    $SQL = $SQL .   " and u.userid = '" . $_SESSION['UserID'] . "'
			  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY legalbusinessunit.legalname";

    $result=DB_query($SQL, $db);
    
    while ($myrow=DB_fetch_array($result)) {
        if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]) {
            echo '<option selected value=' . $myrow['legalid'] . '>' .$myrow['legalname'];
        } else {
            echo '<option value=' . $myrow['legalid'] . '>'.$myrow['legalname'];
        }
    }
    echo '</select></td>';
    // End select tag
    
    /************************************/
    /* SELECCION DEL REGION */
    echo '<tr><td>' . _('X Region') . ':' . "</td>
		<td><select tabindex='4' name='xRegion'>";

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
    /************************************/
    /* SELECCION DEL AREA */
    echo '<tr><td>' . _('X Area') . ':' . "</td>
		<td><select tabindex='4' name='xArea'>";

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
    
    /************************************/
    /* SELECCION DEL DEPARTAMENTO       */
    echo '<tr><td>' . _('X Departamento') . ':' . "</td>
		<td><select tabindex='4' name='xDepartamento'>";

    $sql = "SELECT u_department, CONCAT(u_department,' - ',department) as name FROM departments";
    
    $sql = "Select distinct d.u_department, CONCAT(d.u_department,' - ',d.department) as name 
			From departments d 
			Inner Join tags t on d.u_department= t.u_department 
			Inner Join sec_unegsxuser u on t.tagref= t.tagref 
			Where u.userid= '".$_SESSION['UserID']."' 
			Order by d.department";
    
    $result=DB_query($sql, $db);
    
    echo "<option selected value='0'>Todos los departamentos...</option>";

    while ($myrow=DB_fetch_array($result)) {
        if ($myrow['u_department'] == $_POST['xDepartamento']) {
            echo "<option selected value='" . $myrow["u_department"] . "'>" . $myrow['name'];
        } else {
            echo "<option value='" . $myrow['u_department'] . "'>" . $myrow['name'];
        }
    }
    echo '</select></td></tr>';
    /************************************/
    
    /************************************/
    
    //Select the tag
    echo '<tr><td>' . _('Unidad de Negocio') . ':</td><td><select name="tag">';

    ///Pinta las unidades de negocio por usuario
    $SQL = "SELECT t.tagref,t.tagdescription";
    $SQL = $SQL .   " FROM sec_unegsxuser u,tags t ";
    $SQL = $SQL .   " WHERE u.tagref = t.tagref ";
    $SQL = $SQL .   " and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagdescription, t.tagref";
        
    
    $result=DB_query($SQL, $db);
    $tagmultiple= $result;
    
    echo '<option selected value=all>Todas Las Unidades de Negocio...';
    while ($myrow=DB_fetch_array($result)) {
        if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']) {
            echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
        } else {
            echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
        }
    }
    echo '</select></td></TR>';
    // End select tag
    
// 	if(isset($_GET["TagMultiple"])){
// 		$tagmultiple= explode(",", $_GET["TagMultiple"]);
// 	}
    
// 	// tag multiple
// 	echo "<tr>";
// 	echo "<td>&nbsp;</td>";
// 	echo "<td><select name='tagmultiple' multiple size=10>";
    
// 	foreach ($tagmultiple as $elemento){
        
// 	}
    
    if (isset($_POST['noMovCancel'])) {
        $checkedmov='checked';
    } else {
        $checkedmov='';
    }
    echo "<TR><TD align='right'>"._('Excluir Movimientos Cancelados').":</TD><TD align='left'>";
        echo "<INPUT type='checkbox' NAME='noMovCancel' VALUE='noMovCancel' ".$checkedmov." >";
        echo '</TD></TR>';

         echo '<tr>';
     
     
    if (isset($_POST['yesMovCancel'])) {
        $checkedmov='checked';
    } else {
        $checkedmov='';
    }
    echo "<TR><TD align='right'>"._('Solo Movimientos Cancelados').":</TD><TD align='left'>";
        echo "<INPUT type='checkbox' NAME='yesMovCancel' VALUE='yesMovCancel' ".$checkedmov." >";
        echo '</TD></TR>';
    if (Havepermission($_SESSION['UserID'], 666, $db)==1) {
        if (isset($_POST['CierreAnual'])) {
            $checkedmov='checked';
        } else {
            $checkedmov='';
        }
        echo "<TR><TD align='right'>"._('Incluye Polizas de Cierre Anual').":</TD><TD align='left'>";
            echo "<INPUT type='checkbox' NAME='CierreAnual' VALUE='CierreAnual' ".$checkedmov." >";
            echo '</TD></TR>';
    }
        /* echo '<tr>
         <td>'._('Rango de periodos').':</td>
         <td><select style="height:120px" Name=Period[] multiple>';
	 $sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC';
	 $Periods = DB_query($sql,$db);
         $id=0;
         while ($myrow=DB_fetch_array($Periods,$db)){

            if((isset($SelectedPeriod[$id]) and $myrow['periodno'] == $SelectedPeriod[$id]) or (isset($SelectedFromPeriod) and $myrow['periodno'] >= $SelectedFromPeriod
													and $myrow['periodno'] <= $SelectedToPeriod)){
              echo '<option selected VALUE=' . $myrow['periodno'] . '>' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period']));
            $id++;
            } else {
	      if (!isset($SelectedFromPeriod)) {
		echo '<option VALUE=' . $myrow['periodno'] . '>' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period']));
	      }
            }

         }
         echo "</select></td>
        </tr>";
	
       */
    
    
    echo '<TR><TD>' . _('Periodo Desde:') . '</TD>
			<TD>A&nacuteo &nbsp;<SELECT Name="FromYear">';
    $nextYear = date("Y-m-d", strtotime("+1 Year"));
    $sql = "SELECT distinct(year(lastdate_in_period)) as anio FROM periods where lastdate_in_period < '$nextYear' ORDER BY year(lastdate_in_period) DESC";
    $Periods = DB_query($sql, $db);
    
    $sql= "Select lastdate_in_period as desde, year(lastdate_in_period) as aniodesde, month(lastdate_in_period) as mesdesde,
			(Select lastdate_in_period From periods Where periodno In (".$SelectedToPeriod.")) as hasta, year((Select lastdate_in_period From periods Where periodno In (".$SelectedToPeriod."))) as aniohasta,
			month((Select lastdate_in_period From periods Where periodno In (".$SelectedToPeriod."))) as meshasta
			From periods
			Where periodno In (".$SelectedFromPeriod.")";
    
    $PeriodsDS = DB_query($sql, $db);
    $rowperiods= DB_fetch_array($PeriodsDS);
    $anioActual=date("Y");
    $mesActual=date("m");
    while ($myrow=DB_fetch_array($Periods, $db)) {
            //if($rowperiods["aniodesde"]== $myrow['anio']){
        if ($rowperiods["aniodesde"]== $anioActual) {
            echo '<OPTION SELECTED VALUE="' . $myrow['anio'] . '">' .$myrow['anio'];
        } else {
            echo '<OPTION VALUE="' . $myrow['anio'] . '">' . $myrow['anio'];
        }
        echo '</OPTION>';
    }

    echo '</SELECT>&nbsp;&nbsp;Mes &nbsp;<SELECT Name="FromMes">';
    $sql = "Select * FROM cat_Months";
    $rsmes = DB_query($sql, $db);
    while ($myrow=DB_fetch_array($rsmes, $db)) {
            //if( $rowperiods["mesdesde"] == $myrow['u_mes']){
        if ($rowperiods["mesdesde"] == $mesActual) {
            echo '<OPTION SELECTED VALUE="' . $myrow['u_mes'] . '">' .$myrow['mes'];
        } else {
            echo '<OPTION VALUE="' . $myrow['u_mes'] . '">' .$myrow['mes'];
        }
            echo '</OPTION>';
    }
    echo '</SELECT>';
    
    echo'</TD></TR>';

    if (!isset($_POST['ToPeriod']) or $_POST['ToPeriod']=='') {
        $lastDate = date("Y-m-d", mktime(0, 0, 0, Date('m')+1, 0, Date('Y')));
        $sql = "SELECT periodno FROM periods where lastdate_in_period = '$lastDate'";
        $MaxPrd = DB_query($sql, $db);
        $MaxPrdrow = DB_fetch_row($MaxPrd);
        $DefaultToPeriod = (int) ($MaxPrdrow[0]);
    } else {
        $DefaultToPeriod = $_POST['ToPeriod'];
    }

    echo '<TR><TD>' . _('Periodo Hasta:') .'</TD>
		<TD>A�o &nbsp;<SELECT Name="ToYear">';

    $RetResult = DB_data_seek($Periods, 0);

    while ($myrow=DB_fetch_array($Periods, $db)) {
            //if( $rowperiods["aniohasta"]== $myrow['anio']){
        if ($rowperiods["aniohasta"]== $anioActual) {
            echo '<OPTION SELECTED VALUE="' . $myrow['anio'] . '">' .$myrow['anio'];
        } else {
            echo '<OPTION VALUE="' . $myrow['anio'] . '">' . $myrow['anio'];
        }
            echo '</OPTION>';
    }

    echo '</SELECT>&nbsp;&nbsp;Mes &nbsp;<SELECT Name="ToMes">';
    $RetResult = DB_data_seek($rsmes, 0);
    while ($myrow=DB_fetch_array($rsmes, $db)) {
            //if( $rowperiods["meshasta"]== $myrow['u_mes']){
        if ($rowperiods["meshasta"]== $mesActual) {
            echo '<OPTION SELECTED VALUE="' . $myrow['u_mes'] . '">' .$myrow['mes'];
        } else {
            echo '<OPTION VALUE="' . $myrow['u_mes'] . '">' .$myrow['mes'];
        }
            echo '</OPTION>';
    }
    echo '</SELECT>';
    
    echo'</TD></TR>';
    
    
    echo "<tr>";
        echo "<td colspan='2'>";
            echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
                echo "<tr>";
                    echo "<td style='text-align:center;'>";
                        echo "<INPUT TYPE=SUBMIT Name='Show' Value='"._('Mostrar en Pantalla')."'></CENTER>";
                    echo "</td>";
                    echo "<td style='text-align:center;'>";
                        
                        echo "<input tabindex='7' type='hidden' name='PrintPDF' value='" . _('Imprime Archivo PDF') . "'>";
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "</td>";
                    echo "<td style='text-align:center;'>";
                        echo '<INPUT TYPE=SUBMIT Name="PrintEXCEL" Value="' . _('Exportar a Excel') .'">';
                    echo "</td>";
                echo "</tr>";
            echo "</table>";
        echo "</td>";
    echo "</tr>";
    
    echo "</table></form>";
/* End of the Form  rest of script is what happens if the show button is hit*/
}

if (isset($_POST['Show']) or isset($_POST['PrintEXCEL'])) {
    if (isset($_POST['PrintEXCEL'])) {
        header("Content-type: application/ms-excel");
        # replace excelfile.xls with whatever you want the filename to default to
        header("Content-Disposition: attachment; filename=AuxiliarContable.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    
        echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
        echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
        echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
    }

    if (!isset($SelectedPeriod)) {
        prnMsg(_('Seleccione un periodo de la lista'), 'info');
        include('includes/footer_Index.inc');
        exit;
    }
    
//	if ((!isset($SelectedAccount)) || (strlen($SelectedAccount) < 3) || (!isset($rangoDe)) || ($rangoDe=='') || (!isset($rangoA)) || ($rangoA=='')){

    if ((!isset($SelectedAccount)) || (strlen($SelectedAccount) < 3)) {
        if ((!isset($rangoDe)) || ($rangoDe=='') || (!isset($rangoA)) || ($rangoA=='')) {
            prnMsg(_('Selecciona una cuenta, o escribe los tres primeros carecteres de la cuenta...'), 'info');
            include('includes/footer_Index.inc');
            exit;
        }
    }
    
    
    /*Is the account a balance sheet or a profit and loss account */
    $sql = "SELECT pandl
				FROM accountgroups
				INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_";
    if ($SelectedAccount != '') {
        $sql = $sql ." WHERE chartmaster.accountcode like '".$SelectedAccount."'";
    } else {
        $sql= $sql ." WHERE chartmaster.accountcode between '".$rangoDe."' and '".$rangoA."'";
    }
    if ($_POST['Tipo'] != 'TODOS') {
        $sql= $sql ." AND chartmaster.tipo = ".$_POST['Tipo']."";
    }
    $result = DB_query($sql, $db);
    $PandLRow = DB_fetch_row($result);
    
    if ($PandLRow[0]==1) {
        $PandLAccount = true;
    } else {
        $PandLAccount = false; /*its a balance sheet account */
    }

    $FirstPeriodSelected = $SelectedPeriod;
    $LastPeriodSelected = $_POST['ToPeriod'];
    // si tiene permiso se muestra la poliza de cierre
    if (isset($_POST['CierreAnual'])) {
        $LastPeriodSelected=$LastPeriodSelected+.5;
    }
    if ($_POST['tag']=='all') {
        $sql= "SELECT gltrans.account, chartmaster.accountname, gltrans.type, typename, gltrans.typeno,
			gltrans.trandate,
			gltrans.narrative as narrativeOrig,
			CASE WHEN gltrans.type in(10,11,12,13,21,70,110)
			THEN concat(gltrans.narrative,' @ ',debtorsmaster.name) ELSE
			CASE WHEN gltrans.type in(20,22) THEN concat(gltrans.narrative,' @ ',suppliers.suppname)
			ELSE 'showorig' END end AS narrative,
			amount, periodno, tag,debtortrans.folio,
			debtortrans.order_, chartmaster.naturaleza,
			gltrans.posted
			FROM gltrans 
            LEFT JOIN tags ON gltrans.tag = tags.tagref
			LEFT join systypescat on gltrans.type=systypescat.typeid
			LEFT JOIN areas ON tags.areacode = areas.areacode
			LEFT JOIN regions ON areas.regioncode = regions.regioncode
			LEFT JOIN departments ON tags.u_department=departments.u_department
			JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
			JOIN chartmaster ON gltrans.account = chartmaster.accountcode
			LEFT JOIN debtortrans ON gltrans.type = debtortrans.type and gltrans.typeno = debtortrans.transno and gltrans.tag = debtortrans.tagref
			LEFT join debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
			LEFT JOIN supptrans ON gltrans.type = supptrans.type and gltrans.typeno = supptrans.transno
			left join suppliers ON supptrans.supplierno=suppliers.supplierid";
        
        if (isset($_POST['noMovCancel']) or isset($_POST['yesMovCancel'])) {
            $sql= $sql." left join (
				select sum(amount) as monto, type as tipo, typeno as notipo
				from gltrans ";
            if ($SelectedAccount != '') {
                $sql= $sql." WHERE gltrans.account = '".$SelectedAccount."'";
            } else {
                $sql= $sql." WHERE gltrans.account between '".$rangoDe."' and '".$rangoA."'";
            }
            $sql= $sql." 	AND narrative like '%cancelad%'
				AND periodno>=".$FirstPeriodSelected."
				AND periodno<=".$LastPeriodSelected;
            $sql= $sql."
				group by type,typeno
				) as gltranscancel on gltrans.type=gltranscancel.tipo and gltrans.typeno=gltranscancel.notipo
				";
        }
        if ($SelectedAccount != '') {
            $sql= $sql." WHERE gltrans.account = '".$SelectedAccount."'";
        } else {
            $sql= $sql." WHERE gltrans.account between '".$rangoDe."' and '".$rangoA."'";
        }
        if (isset($_POST['noMovCancel'])) {
            $sql= $sql." and gltranscancel.notipo is null";
        }
        if (!isset($_POST['CierreAnual'])) {
            $sql= $sql." and  right(gltrans.periodno,2)<>.5";
        }
        if (isset($_POST['yesMovCancel'])) {
            $sql= $sql." and gltranscancel.notipo is not null";
        }
        $sql= $sql." and (areas.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			and (areas.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			and (departments.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			
		AND systypescat.typeid=gltrans.type
		AND periodno>=$FirstPeriodSelected
		AND periodno<=$LastPeriodSelected
		AND tags.legalid = ".$_POST['legalid']."";
                
        if ($_POST['Tipo'] != 'TODOS') {
            $sql= $sql." AND chartmaster.tipo = ".$_POST['Tipo']."";
        }
        if ($_POST['TipoPoliza'] != 'TODOS') {
            $sql= $sql." AND gltrans.type = '".$_POST['TipoPoliza']."'";
        }
        
        $sql= $sql." ORDER BY gltrans.account, periodno, gltrans.trandate, gltrans.typeno, counterindex";
    } else {
        $sql= "SELECT gltrans.account,
			chartmaster.accountname, gltrans.type,debtortrans.debtorno,debtorsmaster.name,suppliers.suppname
			typename,
			gltrans.typeno,
			gltrans.trandate,
			gltrans.narrative as narrativeOrig,
			CASE WHEN gltrans.type in(10,11,12,13,21,70,110) THEN concat(gltrans.narrative,' @ ',debtorsmaster.name)
			ELSE
			CASE WHEN gltrans.type in(20,22) THEN concat(gltrans.narrative,' @ ',suppliers.suppname)
			ELSE 'showorig' END END AS narrative,
			amount,
			periodno,
			tag,debtortrans.folio, debtortrans.order_, chartmaster.naturaleza,
			gltrans.posted
		FROM gltrans JOIN tags ON gltrans.tag = tags.tagref
			INNER JOIN systypescat on gltrans.type=systypescat.typeid
			JOIN chartmaster ON gltrans.account = chartmaster.accountcode
			LEFT JOIN debtortrans ON gltrans.type = debtortrans.type and gltrans.typeno = debtortrans.transno  and gltrans.tag = debtortrans.tagref
			LEFT JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
			LEFT JOIN supptrans ON gltrans.type = supptrans.type and gltrans.typeno = supptrans.transno
			LEFT JOIN suppliers ON supptrans.supplierno=suppliers.supplierid";
        if (isset($_POST['noMovCancel']) or isset($_POST['yesMovCancel'])) {
            $sql= $sql." left join (
				select sum(amount) as monto, type as tipo, typeno as notipo
				from gltrans ";
            if ($SelectedAccount != '') {
                $sql= $sql." WHERE gltrans.account = '".$SelectedAccount."'";
            } else {
                $sql= $sql." WHERE gltrans.account between '".$rangoDe."' and '".$rangoA."'";
            }
            if (!isset($_POST['CierreAnual'])) {
                $sql= $sql." and  right(gltrans.periodno,2)<>.5";
            }
            $sql= $sql." 	AND narrative like '%cancela%'
				AND periodno>=".$FirstPeriodSelected."
				AND periodno<=".$LastPeriodSelected;
            $sql= $sql."
				group by type,typeno
				) as gltranscancel on gltrans.type=gltranscancel.tipo and gltrans.typeno=gltranscancel.notipo
				";
        }
        
        if ($SelectedAccount != '') {
            $sql= $sql." WHERE gltrans.account = '".$SelectedAccount."'";
        } else {
            $sql= $sql." WHERE gltrans.account between '".$rangoDe."' and '".$rangoA."'";
        }
        if (isset($_POST['noMovCancel'])) {
            $sql= $sql." and gltranscancel.notipo is null";
        }
        
        
        if (isset($_POST['yesMovCancel'])) {
            $sql= $sql." and gltranscancel.notipo is not null";
        }
        $sql= $sql." 	AND systypescat.typeid=gltrans.type
		AND periodno>=$FirstPeriodSelected
		AND periodno<=$LastPeriodSelected
		AND tag='".$_POST['tag']."'
		AND legalid = ".$_POST['legalid']."";
        if ($_POST['Tipo'] != 'TODOS') {
            $sql= $sql." AND chartmaster.tipo = ".$_POST['Tipo']."";
        }
        if ($_POST['TipoPoliza'] != 'TODOS') {
            $sql= $sql." AND gltrans.type = '".$_POST['TipoPoliza']."'";
        }
        $sql= $sql." ORDER BY gltrans.account, periodno, gltrans.trandate, gltrans.typeno, counterindex";
    }
    
    /*if ($_SESSION['UserID']=="galvarez" or $_SESSION['UserID']=='desarrollo' or $_SESSION['UserID']=='admin'){
		echo '<br><pre><br>'.$sql;
	}*/
    //echo "<br><br>sql2".$sql;
    if ($SelectedAccount != '') {
        $ErrMsg = _('Las transacciones para la cuenta') . ' ' . $SelectedAccount . ' ' . _('no pudieron ser recuperadas porque') ;
    } else {
        $ErrMsg = _('Las transacciones para la cuenta') . ' ' . $rangoDe . ' ' . $rangoA . ' ' . _('no pudieron ser recuperadas porque') ;
    }
    $LastPeriodSelected=$LastPeriodSelected-.5;
    //echo "<pre>".$sql;
    $TransResult = DB_query($sql, $db, $ErrMsg);

    echo '<table>';

    $TableHeader = "<tr>
			<th>" . _('Fecha') . "</th>
			<th>" . _('Tipo') . "</th>
			<th>" . _('Numero') . "</th>
			<th>" . _('Folio') . "</th>
			<th>" . _('Concepto') . "</th>
			<th>" . _('Cargos') . "</th>
			<th>" . _('Abonos') . "</th>
			<th>" . _('Saldo') . "</th>
			<th>" . _('Unidad Negocio') . "</th>
			<th>" . _('POSTEADA') . '</th>
			</tr>';

    echo $TableHeader;

    if ($PandLAccount==true) {
        $RunningTotal = 0;
    } else {
           // added to fix bug with Brought Forward Balance always being zero
        $sql = "SELECT sum(bfwd) as bfwd,
			sum(actual) as actual,
			period, naturaleza
		FROM chartdetails JOIN tags ON chartdetails.tagref = tags.tagref
					JOIN chartmaster ON chartdetails.accountcode = chartmaster.accountcode";
        if ($SelectedAccount != '') {
            $sql= $sql." WHERE chartdetails.accountcode = '".$SelectedAccount."'";
        } else {
            $sql= $sql." WHERE chartdetails.accountcode between '".$rangoDe."' and '".$rangoA."'";
        }
        $sql = $sql ." AND chartdetails.period=" . $FirstPeriodSelected."
		AND (chartdetails.tagref='".$_POST['tag']."' or 'all'='".$_POST['tag']."')
		AND legalid = ".$_POST['legalid']."";
        if ($_POST['Tipo'] != 'TODOS') {
            $sql= $sql ." AND chartmaster.tipo = ".$_POST['Tipo']."";
        }
        $sql = $sql ." GROUP BY period, naturaleza";

        if ($SelectedAccount != '') {
            $ErrMsg = _('El detalle de la cuenta') . ' ' . $SelectedAccount . ' ' . _('no pudo ser recuperado de la BD');
        } else {
            $ErrMsg = _('El detalle de la cuenta') . ' De: ' . $rangoDe . ' A: ' . $rangoA . ' ' . _('no pudo ser recuperado de la BD');
        }
        
        //echo $sql;
        
        $ChartDetailsResult = DB_query($sql, $db, $ErrMsg);
        $ChartDetailRow = DB_fetch_array($ChartDetailsResult);
        // --------------------
                
        $RunningTotal =$ChartDetailRow['bfwd'];
        
        echo "<tr bgcolor='#FDFEEF'>
			<td></td><td></td><td></td><td></td>
			<td colspan=3><b>" . _('SALDO INICIAL:') . '</b></td>
			<td class=number><b>' . number_format($RunningTotal, 2) . '</b></td>
			<td colspan=3></td>
			</tr>';
    }
    
    $PeriodTotal = 0;
    $PeriodNo = -9999;
    $ShowIntegrityReport = false;
    $j = 1;
    $k=0; //row colour counter

    $totalcuentacargos = 0;
    $totalcuentaabonos = 0;
    $totalcuenta = 0;


    while ($myrow=DB_fetch_array($TransResult)) {
        if ($j == 1) {
            // DIBUJA ENCABEZADO DE INICIO DE CADA CUENTA CONTABLE
            
            $cuentacontable = $myrow['account'];
            $nombrecuenta = $myrow['accountname'];
            
            
            $sql = "SELECT sum(bfwd) as bfwd,
				sum(actual) as actual,
				period,naturaleza
			FROM chartdetails JOIN tags ON chartdetails.tagref = tags.tagref
					JOIN chartmaster ON chartdetails.accountcode = chartmaster.accountcode
			WHERE chartdetails.accountcode = '".$cuentacontable."'
			AND chartdetails.period=" . $FirstPeriodSelected."
			AND (chartdetails.tagref='".$_POST['tag']."' or 'all'='".$_POST['tag']."')
			AND legalid = ".$_POST['legalid']."
			GROUP BY period";
    
            $ErrMsg = _('El detalle de la cuenta') . ' ' . $cuentacontable . ' ' . _('no pudo ser recuperado de la BD');
            
            $ChartDetailsResult = DB_query($sql, $db, $ErrMsg);
            $ChartDetailRow = DB_fetch_array($ChartDetailsResult);
            
            echo "<tr bgcolor='#FDFEEF'>
					<td colspan=6><b>I:" . $cuentacontable . ' '. $nombrecuenta . '</b></td>';
                
            echo '<td></td>
				<td class=number nowrap><b>I:' . number_format($ChartDetailRow['bfwd'], 2) . '</b></td>
				<td></td><td></td>
				</tr>';
            $saldoxcuenta = $ChartDetailRow['bfwd'];
            $saldoxcuentacargos = 0;
            $saldoxcuentaabonos = 0;
        }
        
        $j = $j + 1;

    
        
        //IMPRIMIR SUB TOTALES POR CUENTA
        if (trim($myrow['account']) != trim($cuentacontable)) {
    /*calcula el acumulado de las cuentas*/
            $totalcuenta = $totalcuenta + $saldoxcuenta;
            $totalcuentacargos = $totalcuentacargos + $saldoxcuentacargos;
            $totalcuentaabonos = $totalcuentaabonos + $saldoxcuentaabonos;
            
            echo "<tr bgcolor='#FDFEEF'>
					<td colspan=5><b>T:" . $cuentacontable . ' '. $nombrecuenta . '</b></td>';
                
                
            echo '  <td class=number nowrap><b>T:' . number_format($saldoxcuentacargos, 2) . '</b></td>
				<td class=number nowrap><b>T:' . number_format($saldoxcuentaabonos, 2) . '</b></td>
				<td class=number nowrap><b>T:' . number_format($saldoxcuenta, 2) . '</b></td>
				<td></td><td></td>
				</tr>';
                
            $sql = "SELECT sum(bfwd) as bfwd,
				sum(actual) as actual,
				period
			FROM chartdetails  JOIN tags ON chartdetails.tagref = tags.tagref
			WHERE chartdetails.accountcode = '".$myrow['account']."'
			AND chartdetails.period=" . $FirstPeriodSelected."
			AND (chartdetails.tagref='".$_POST['tag']."' or 'all'='".$_POST['tag']."')
			AND legalid = ".$_POST['legalid']."
			/* GROUP BY period */ ";
            
            //echo '<br><pre>sql:'.$sql;
            
            $ErrMsg = _('El detalle de la cuenta') . ' ' . $cuentacontable . ' ' . _('no pudo ser recuperado de la BD');
            $ChartDetailsResult = DB_query($sql, $db, $ErrMsg);
            $ChartDetailRow = DB_fetch_array($ChartDetailsResult);
            
            $cuentacontable = $myrow['account'];
            $nombrecuenta = $myrow['accountname'];
            
            $saldoxcuenta = $ChartDetailRow[0];//*$myrow['naturaleza'];
            //echo '<br>valor:'.$ChartDetailRow[0];
            $saldoxcuentacargos = 0;
            $saldoxcuentaabonos = 0;
            
            echo "<tr bgcolor='#FFFFFF'>
					<td colspan=6><b><br><br></b></td>";
                
            echo '<td></td>
				<td class=number><b></b></td>
				<td></td><td></td>
				</tr>';
                
            echo "<tr bgcolor='#FDFEEF'>
					<td colspan=6><b>" . $cuentacontable . ' '. $nombrecuenta . '(INICIAL)</b></td>';
                
            echo '<td></td>
				<td class=number nowrap><b>' . number_format($saldoxcuenta, 2) . '</b></td>
				<td></td><td></td>
				</tr>';
        }
        

        if ($k==1) {
            echo '<tr class="EvenTableRows">';
            $k=0;
        } else {
            echo '<tr class="OddTableRows">';
            $k++;
        }

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
    

        $FormatedTranDate = ConvertSQLDate($myrow['trandate']);
        $URL_to_TransDetail = $rootpath . '/GLTransInquiryV2.php?' . SID . '&TypeID=' . $myrow['type'] . '&TransNo=' . $myrow['typeno'];
        

        //SOLO APLICA PARA TACSA
                
        if ($myrow['type'] == 110) {
            $tipo = 10;
        } else {
            $tipo = $myrow['type'];
        }
        
        //if ($myrow['typeno'] == '1707'){
            //echo "<br>" . $myrow['tag'] . "-" . $tipo . "-" . $myrow['order_'] . "-" .  $myrow['typeno'];
        //}
        
        // $liga = GetUrlToPrint($myrow['tag'], $tipo, $db);
        $liga= 'PDFInvoice.php?';
        //$PrintDispatchNote = $rootpath . '/' . $liga  . '&' . SID . 'PrintPDF=Yes&TransNo='.$myrow['orderno'].'&Tagref='.$tagref;
        
        //SE PUSO LA CONDICION POR QUE CUANDO ES RECIBO RECIBE UN PARAMETRO DIFERENTE InvoiceNo.
        if ($tipo==70) {
            $parametro = $myrow['order_'];
        } else {
            $parametro = $myrow['typeno'];
        }
        
        if ($tipo == 12) {
            $URL_to_TransFolio = $rootpath . '/' . $liga . '&InvoiceNo=' . $parametro .  '&Tagref='.$myrow['tag']. '&Type=' .$myrow['type'] ;
        } else {
            $URL_to_TransFolio = $rootpath . '/' . $liga . '&TransNo=' . $parametro .  '&Tagref='.$myrow['tag']. '&Type=' .$myrow['type'] ;
        }
        //link de impresion para pagos contables
        if ($tipo == 1) {
            $liga='PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=1';
            $URL_to_TransFolio = $rootpath . '/' . $liga . '&TransNo=' . $parametro . '&periodo='.$ChartDetailRow['period'] .  '&trandate='.$myrow['trandate'] ;
        }
        //link de impresion para entrega orden de compra
        if ($tipo == 25) {
            $liga='PDFGrn2.php?';
            //PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=1&TransNo=583&periodo=37&trandate=2010-06-02
            $URL_to_TransFolio = $rootpath . '/' . $liga . '&PONo=' .$parametro  ;
        }
        //link de impresion para ajuste de existencias
        if ($tipo == 17) {
            $liga='PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes';
            $URL_to_TransFolio = $rootpath . '/' . $liga . '&type=' .$myrow['type']. '&TransNo=' .$parametro .'&periodo='.$ChartDetailRow['period'] . '&trandate='.$myrow['trandate'];
        }
        //link de impresion para factura de compra
        if ($tipo == 20) {
            $liga='PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes';
            $URL_to_TransFolio = $rootpath . '/' . $liga . '&type=' .$myrow['type']. '&TransNo=' .$parametro .'&periodo='.$ChartDetailRow['period'] . '&trandate='.$myrow['trandate'];
        }
        
        if ($myrow['folio'] == '') {
            $folio = 'IMPRIMIR';
        } else {
            $folio = $myrow['folio'];
        }
        
        
        $tagsql="SELECT tagdescription FROM tags WHERE tagref='".$myrow['tag']."'";
        $tagresult=DB_query($tagsql, $db);
        $tagrow = DB_fetch_array($tagresult);
        
        /*
		    if($myrow['type']='10' or $myrow['type']='11' or $myrow['type']='12' or $myrow['type']='13' or $myrow['type']='21' or $myrow['type']='110'){
			$narrative=$myrow['narrative'].'@'.$myrow['name']
		}*/
        
        $txt = $myrow['narrative'];
        if ($txt=="showorig") {
            $txt = $myrow['narrativeOrig'];
        }
        
        
        $txt = str_replace('Pago a Cta:', '', $txt);
        
        echo "<td>$FormatedTranDate</td>
			<td>".$myrow['typename']."</td>
			<td class=number><a href='$URL_to_TransDetail'>".$myrow['typeno']."</a></td>
			<td><a target='BLANK_' href='$URL_to_TransFolio'>$folio</a></td>
			<td>".$txt."</td>
			<td class=number>$DebitAmount</td>
			<td class=number>$CreditAmount</td>
			<td class=number nowrap><b>".number_format($saldoxcuenta, 2)."</b></td>
			<td>".$tagrow['tagdescription']."</td><td>".$myrow['posted']."</td>
			</tr>";
    }
    
    //******************************************
    //******IMPRIME ULTIMO TOTAL DE CUENTA
    
    echo "<tr bgcolor='#FDFEEF'>
			<td colspan=5><b>T:" . $cuentacontable . ' '. $nombrecuenta . '</b></td>';
        
        
        echo '	<td class=number nowrap><b>T:' . number_format($saldoxcuentacargos, 2) . '</b></td>
			<td class=number nowrap><b>T:' . number_format($saldoxcuentaabonos, 2) . '</b></td>
			<td class=number nowrap><b>T:' . number_format($saldoxcuenta, 2) . '</b></td>
			<td></td><td></td>
			</tr>';
    
    echo "<tr bgcolor='#FFFFFF'>
			<td colspan=6><b><br><br></b></td>";
        
    echo '<td></td>
		<td class=number><b></b></td>
		<td></td><td></td>
		</tr>';
    //******************************************
    

    echo "<tr bgcolor='#FDFEEF'><td></td><td></td><td></td><td colspan=3><b>";
    if ($PandLAccount==true) {
        echo _('Total Movimientos del Periodo');
    } else { /*its a balance sheet account*/
        echo _('Saldo Acumulado');
    }
    echo '</b></td>';

    echo '<td></td><td align=right nowrap><b>' . number_format(($RunningTotal), 2) . '</b></td><td colspan=2></td><td></td></tr>';


    //**************Imprimie un total general****************
    $totalcuenta = $totalcuenta + $saldoxcuenta;
    $totalcuentacargos = $totalcuentacargos + $saldoxcuentacargos;
    $totalcuentaabonos = $totalcuentaabonos + $saldoxcuentaabonos;
    echo "<tr><td colspan=6><br></td></tr>";
    echo "<tr bgcolor='#FDFEEF'><td></td><td></td><td></td>
			<td colspan=2 style='text-align:center'><b>"._('Total Acumulado: ')."</b></td>";
        
        echo '<td class=number nowrap><b>T:' . number_format($totalcuentacargos, 2) . '</b></td>
			<td class=number nowrap><b>T:' . number_format($totalcuentaabonos, 2) . '</b></td>
			<td class=number nowrap><b>T:' . number_format($totalcuenta, 2) . '</b></td>
			<td></td><td></td>
			</tr>';
    
    echo "<tr bgcolor='#FFFFFF'>
			<td colspan=6><b><br><br></b></td>";
    echo '<td></td>
		<td class=number><b></b></td>
		<td></td><td></td>
		</tr>';
    //******************************************

    echo '</table>';
} /* end of if Show button hit */


if (isset($ShowIntegrityReport) and $ShowIntegrityReport==true) {
    if (!isset($IntegrityReport)) {
        $IntegrityReport='';
    }
    prnMsg(_('Existen diferencias entre el detalle de las transacciones y la informacion del detalle de acumulados de la cuenta en ChartDetails') . '. ' . _('Un registro de las diferencias se muestra abajo'), 'warn');
    echo '<p>'.$IntegrityReport;
}

if (isset($_POST['PrintEXCEL'])) {
        exit;
}

include('includes/footer_Index.inc');
