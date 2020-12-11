<?php
/**
 * Recepcción de Productos
 *
 * @category Clase
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/09/2017
 * Fecha Modificación: 01/09/2017
 * Proceso de recepcción de productos
 */

$PageSecurity = 2;
$PricesSecurity = 9;
include('includes/session.inc');
$title = _('Busqueda de Productos para Transferencias');
include('includes/header.inc');
$funcion=81;
include('includes/SecurityFunctions.inc');
$msg = '';
if (isset($_GET['StockID'])) {
    //The page is called with a StockID
    $_GET['StockID'] = trim(strtoupper($_GET['StockID']));
    $_POST['Select'] = trim(strtoupper($_GET['StockID']));
}
//---Recibe el id del campo al que se le va  asignar la clave del producto
if (isset($_GET['cajaID'])) {
		$_SESSION['cajaID'] = $_GET['cajaID'];
}
$idcaja = $_SESSION['cajaID'];

if (isset($_GET['cadena'])) {
		$_SESSION['cadena'] = $_GET['cadena'];
}
$Cadena = $_SESSION['cadena'];

if (isset($_GET['LinesCounter'])){
	$_SESSION['LinesCounter'] = $_GET['LinesCounter'];
}
$LinesCounter = $_SESSION['LinesCounter'];

//-------------------------

if (isset($_GET['NewSearch'])) {
    unset($StockID);
    unset($_SESSION['SelectedStockItem']);
    unset($_POST['Select']);
}

if (!isset($_POST['PageOffset'])) {
    $_POST['PageOffset'] = 1;
} else {
    if ($_POST['PageOffset'] == 0) {
        $_POST['PageOffset'] = 1;
    }
}

if (isset($_POST['StockCode'])) {
    $_POST['StockCode'] = trim(strtoupper($_POST['StockCode']));
}

#$SQL='SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription';

/*$SQL='SELECT sto.categoryid, categorydescription';
$SQL.=' FROM stockcategory sto, sec_stockcategory sec';
$SQL.=' WHERE sto.categoryid=sec.categoryid';
$SQL.=' AND userid="'.$_SESSION['UserID'].'"';
$SQL.=' ORDER BY categorydescription';

$result1 = DB_query($SQL,$db);
if (DB_num_rows($result1) == 0) {
    echo '<p><font size=4 color=red>' . _('Reporte de Problema') . ':</font><br>' . _('No hay categorias de inventario definidas en el sistema. favor de ir a la siguiente liga para configurarlas');
    echo '<br><a href="' . $rootpath . '/StockCategories.php?' . SID .'">' . _('Definir Categorias de Inventario') . '</a>';
    exit;
}*/

//----------------INICIA FORM DE BUSCADOR DE PRODUCTOS
echo '<form action="'. $_SERVER['PHP_SELF'] . '?' . SID .'" method=post>';
echo '<b>' . $msg . '</b>';
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . _('Busqueda de Productos'); 
echo '<table><tr>';
//--------------combo categoria
echo '<td>'. _('X Categoria de Inventarios') . ':</td><td>';
echo '<select name="StockCat">';

if (!isset($_POST['StockCat'])) {
	$_POST['StockCat'] = "";
}

if ($_POST['StockCat'] == "All") {
	echo '<option selected value="All">' . _('Todas');
} else {
	echo '<option value="All">' . _('Todas');
}

while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected VALUE="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
	} else {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
	}
}
echo '</select></td></tr>';//------------------------------

//--------definicion caja descripción
echo '<tr><td><b> ' . _('X Descripcion') . '</b>:</td><td>';


if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size=20 maxlength=25>';
} else {
	echo '<input type="text" name="Keywords" size=20 maxlength=25>';
}

echo '</td></tr><tr>';//-----------------------------

//------------------definicion caja clave de producto
echo '<td><b>'. _('X Clave de Producto') . '</b>:</td>';
echo '<td>';

if (isset($_POST['StockCode'])) {
	echo '<input type="text" name="StockCode" value="'. $_POST['StockCode'] . '" size=15 maxlength=18>';
} else {
	echo '<input type="text" name="StockCode" size=15 maxlength=18>';
}//-----------------------------------------------------------

//-----------check box
echo '<input type="checkbox" name="busquedaexacta"> * para busqueda exacta de la clave.';

echo '</td></tr></table><br>';

echo '<div class="centre"><input type=submit name="Search" value="'. _('Realiza Busqueda') . '"></div><hr>';
//---------------------------FIN DEL FORM BUSQUEDA DE PRODUCTOS------------------------------------------------

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    
    
    if ($_POST['StockCat']=='All' and $_POST['Keywords']=='' AND $_POST['StockCode']=='')
    {
	prnMsg("Debes seleccionar una categoría o capturar la descripción o código del producto que deseas localizar...",'error');
	exit;
    }

    if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
        // if Search then set to first page
        $_POST['PageOffset'] = 1;
    }

    if ($_POST['Keywords'] AND $_POST['StockCode']) {
        $msg=_('Descripcion del producto ha sido utilizado en vez de la clave del producto capturada');
    }
    
    $charfindstockid = '%';
    if (isset($_POST['busquedaexacta'])) {
	$charfindstockid = '';
    }
    
    if ($_POST['Keywords']) {
        //insert wildcard characters in spaces
        $_POST['Keywords'] = strtoupper($_POST['Keywords']);
        $i = 0;
        $SearchString = '%';
        while (strpos($_POST['Keywords'], ' ', $i)) {
            $wrdlen = strpos($_POST['Keywords'], ' ', $i) - $i;
            $SearchString = $SearchString . substr($_POST['Keywords'], $i, $wrdlen) . '%';
            $i = strpos($_POST['Keywords'], ' ', $i) + 1;
        }
        $SearchString = $SearchString. substr($_POST['Keywords'], $i) . '%';

        if ($_POST['StockCat'] == 'All'){
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces,
		    cat.categorydescription
                FROM stockmaster,
                    locstock,
		    stockcategory cat
                WHERE stockmaster.stockid=locstock.stockid
                AND stockmaster.description " . LIKE . " '$SearchString'
		AND stockmaster.categoryid=cat.categoryid
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces, cat.categorydescription
                ORDER BY qoh desc, cat.categorydescription, stockmaster.description";
        } else {
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces,
		    cat.categorydescription
                FROM stockmaster,
                    locstock,
		    stockcategory cat
                WHERE stockmaster.stockid=locstock.stockid
                AND description " .  LIKE . " '$SearchString'
                AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
		AND stockmaster.categoryid=cat.categoryid
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces, cat.categorydescription
                ORDER BY qoh desc, cat.categorydescription, stockmaster.description";
        }
    } elseif (isset($_POST['StockCode'])) {


        $_POST['StockCode'] = strtoupper($_POST['StockCode']);
        if ($_POST['StockCat'] == 'All') {
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.mbflag,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.decimalplaces,
		    cat.categorydescription
                FROM stockmaster,
                    locstock,
		    stockcategory cat
                WHERE stockmaster.stockid=locstock.stockid
                AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . $charfindstockid ."'
		AND stockmaster.categoryid=cat.categoryid
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces, cat.categorydescription
                ORDER BY qoh desc, cat.categorydescription, stockmaster.description";

        } else {
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.mbflag,
                    sum(locstock.quantity) as qoh,
                    stockmaster.units,
                    stockmaster.decimalplaces,
		    cat.categorydescription
                FROM stockmaster,
                    locstock,
		    stockcategory cat
                WHERE stockmaster.stockid=locstock.stockid
                AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . $charfindstockid ."'
                AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
		AND stockmaster.categoryid=cat.categoryid
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces, cat.categorydescription
                ORDER BY qoh desc, cat.categorydescription, stockmaster.description";
        }

    } elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
        if ($_POST['StockCat'] == 'All'){
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.mbflag,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.decimalplaces,
		    cat.categorydescription
                FROM stockmaster,
                    locstock,
		    stockcategory cat
                WHERE stockmaster.stockid=locstock.stockid
		AND stockmaster.categoryid=cat.categoryid
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces, cat.categorydescription
                ORDER BY qoh desc, cat.categorydescription, stockmaster.description";
        } else {
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.mbflag,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.decimalplaces,
		    cat.categorydescription
                FROM stockmaster,
                    locstock,
		    stockcategory cat
                WHERE stockmaster.stockid=locstock.stockid
                AND categoryid='" . $_POST['StockCat'] . "'
		AND stockmaster.categoryid=cat.categoryid
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces, cat.categorydescription
                ORDER BY qoh desc, cat.categorydescription, stockmaster.description";
        }
    }
    
    //echo $SQL;
    $ErrMsg = _('Ningun codigo de Producto fue encontrado porque');
    $DbgMsg = _('El SQL que regreso el error fue');
    $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

    if (DB_num_rows($result) == 0) {
        prnMsg(_('Ningun Producto de almacen fue encontrado por esta busqueda, favor de recapturar el criterio de busqueda para tratar otra vez'),'info');
    } elseif (DB_num_rows($result) == 1) {
        /* autoselect it
         * to avoid user hitting another keystroke */
        $myrow = DB_fetch_row($result);
        $_POST['Select'] = $myrow[0];
    }
    unset($_POST['Search']);
}

if (isset($result) AND !isset($_POST['Select'])) 
{

    $ListCount = DB_num_rows($result);
    if ($ListCount > 0) {
        $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);

        if (isset($_POST['Next'])) {
            if ($_POST['PageOffset'] < $ListPageMax) {
                $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
            }
        }

        if (isset($_POST['Previous'])) {
            if ($_POST['PageOffset'] > 1) {
                $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
            }
        }

        if ($_POST['PageOffset'] > $ListPageMax) {
            $_POST['PageOffset'] = $ListPageMax;
        }
        if ($ListPageMax > 1) {
            echo "<div class='centre'><p>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('paginas') . '. ' . _('Ir a pagina') . ': ';

            echo '<select name="PageOffset">';

            $ListPage=1;
            while ($ListPage <= $ListPageMax) {
                if ($ListPage == $_POST['PageOffset']) {
                    echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
                } else {
                    echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
                }
                $ListPage++;
            }
            echo '</select>
                <input type=submit name="Go" value="' . _('Ir') . '">
                <input type=submit name="Previous" value="' . _('Anterior') . '">
                <input type=submit name="Next" value="' . _('Siguiente') . '">';
            echo '<p></div>';
        }

        echo '<table cellpadding=2 colspan=7 border=1>';
        $tableheader = '<tr>
		    <th>' . _('Categoria') . '</th>
                    <th>' . _('Clave') . '</th>
                    <th>' . _('Descripcion') . '</th>
                    <th>' . _('Disp.<br>Total') . '</th>
                    <th>' . _('Unid.') . '</th>
                </tr>';
        echo $tableheader;

        $j = 1;

        $k = 0; //row counter to determine background colour

    $RowIndex = 0;

    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
    }

        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

            if ($k == 1) {
                echo '<tr>';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k++;
            }

            if ($myrow['mbflag'] == 'D') {
                $qoh = 'N/A';
            } else {
                $qoh = number_format($myrow["qoh"],$myrow['decimalplaces']);
            }

            printf("<td>%s</td><td><input type=submit name='Select' value='%s'></td>
                <td>%s</td>
                <td class='number'>%s</td>
                <td>%s</td>
                </tr>",
		        $myrow['categorydescription'],
                $myrow['stockid'],
                $myrow['description'],
                $qoh,
                $myrow['units']);

            $j++;
            if ($j == 20 AND ($RowIndex+1 != $_SESSION['DisplayRecordsMax'])) {
                $j = 1;
                echo $tableheader;

            }
            $RowIndex = $RowIndex + 1;
            //end of page full new headings if
        }
        //end of while loop

        echo '</table><br>';
				
    }
}
if (!isset($_POST['Search']) AND (isset($_POST['Select']))) 
{
   $_GET['StockID'] = $_POST['Select'];
   $idproducto = $_GET['StockID'];
   
  // echo 'cadena original'.$arrCadena;
   $arrCadena = explode('!',$Cadena);
   $temp = '';   
   for($i=0; $i < $LinesCounter; $i++)
   {
      if($i == $idcaja)
	  {
	     $temp = $temp. $idproducto.'!';
	  }
	  else
	    $temp = $temp.$arrCadena[$i].'!';
   }
   echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/StockLocTransfer.php?' . SID.'idProducto='.$idproducto.'&cajaId=' .$idcaja.'&cadenaDestino=' . $temp . '&LinesCounter=' . $LinesCounter . '">';
}


echo '<script  type="text/javascript">defaultControl(document.forms[0].StockCode);</script>';
echo '</form>';

include('includes/footer_Index.inc');
?>
