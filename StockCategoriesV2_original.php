<?php
/**
 * ABC Categoría de Inventario
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 25/09/2017
 * Fecha Modificación: 25/09/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */

$PageSecurity = 11;
include "includes/SecurityUrl.php";
include('includes/session.inc');
$funcion=137;
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include 'includes/SQL_CommonFunctions.inc';

// Variable Ocultar Formulario
$ocultoElemento = 'style="display: none;"';

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

// Validar Identicador
$validarIdentificador = 1;

if (isset($_POST['deductibleflag'])) {
    $_POST['deductibleflag'] = 1;
} else {
    $_POST['deductibleflag'] = 0;
}

if (!isset($_POST["caducidad"])) {
    $_POST["caducidad"]= 0;
}

if (isset($_GET['SelectedCategory'])) {
    $SelectedCategory = strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])) {
    $SelectedCategory = strtoupper($_POST['SelectedCategory']);
}

if (isset($_GET['id'])) {
    $_POST['txtRegistro'] = $_GET['id'];
} else if (isset($_POST['txtRegistro'])) {
    $_POST['txtRegistro'] = $_POST['txtRegistro'];
} else {
    $_POST['txtRegistro'] = "";
}

if (isset($_GET['accountegreso'])) {
    $_POST['accountegreso'] = strtoupper($_GET['accountegreso']);
}

if (isset($_GET['StockAct'])) {
    $_POST['StockAct'] = strtoupper($_GET['StockAct']);
}

if (isset($_POST['busquedaDesc'])) {
    $_POST['busquedaDesc'] = $_POST['busquedaDesc'];
} else if (isset($_GET['busquedaDesc'])) {
    $_POST['busquedaDesc'] = $_GET['busquedaDesc'];
} else {
    $_POST['busquedaDesc'] = "";
}

if (isset($_POST['lineaDesc'])) {
    $_POST['lineaDesc'] = $_POST['lineaDesc'];
} else if (isset($_GET['busquedaDesc'])) {
    $_POST['lineaDesc'] = $_GET['lineaDesc'];
} else {
    $_POST['lineaDesc'] = "";
}

if (isset($_POST['textimage'])) {
    $_POST['textimage'] = $_POST['textimage'];
} else if (isset($_GET['textimage'])) {
    $_POST['textimage'] = $_GET['textimage'];
} else {
    $_POST['textimage'] = "";
}

if (isset($_POST['margenaut'])) {
    $_POST['margenaut'] = $_POST['margenaut'];
} else if (isset($_GET['margenaut'])) {
    $_POST['margenaut'] = $_GET['margenaut'];
} else {
    $_POST['margenaut'] = "";
}

if (isset($_POST['margensales'])) {
    $_POST['margensales'] = $_POST['margensales'];
} else if (isset($_GET['margensales'])) {
    $_POST['margensales'] = $_GET['margensales'];
} else {
    $_POST['margensales'] = "";
}

if (isset($_POST['cashdiscount'])) {
    $_POST['cashdiscount'] = $_POST['cashdiscount'];
} else if (isset($_GET['cashdiscount'])) {
    $_POST['cashdiscount'] = $_GET['cashdiscount'];
} else {
    $_POST['cashdiscount'] = "";
}

// if (isset($_POST['warrantycost'])) {
//     $_POST['warrantycost'] = $_POST['warrantycost'];
// } else if (isset($_GET['warrantycost'])) {
//     $_POST['warrantycost'] = $_GET['warrantycost'];
// } else {
//     $_POST['warrantycost'] = 0;
// }
$_POST['warrantycost'] = 0;

// if (isset($_POST['optimo'])) {
//     $_POST['optimo'] = $_POST['optimo'];
// } else if (isset($_GET['optimo'])) {
//     $_POST['optimo'] = $_GET['optimo'];
// } else {
//     $_POST['optimo'] = 0;
// }
$_POST['optimo'] = 0;

// if (isset($_POST['minimo'])) {
//     $_POST['minimo'] = $_POST['minimo'];
// } else if (isset($_GET['minimo'])) {
//     $_POST['minimo'] = $_GET['minimo'];
// } else {
//     $_POST['minimo'] = 0;
// }
$_POST['minimo'] = 0;

// if (isset($_POST['maximo'])) {
//     $_POST['maximo'] = $_POST['maximo'];
// } else if (isset($_GET['maximo'])) {
//     $_POST['maximo'] = $_GET['maximo'];
// } else {
//     $_POST['maximo'] = 0;
// }
$_POST['maximo'] = 0;

if (isset($_POST['selcat'])) {
    $_POST['selcat'] = $_POST['selcat'];
} else if (isset($_GET['selcat'])) {
    $_POST['selcat'] = $_GET['selcat'];
} else {
    $_POST['selcat'] = "";
}

$discountInPriceListOnPrice = 0;
$discountInComercialOnPrice = 0;

if (isset($_POST['generaPublicacionAutomatica'])) {
    $_POST['generaPublicacionAutomatica'] = $_POST['generaPublicacionAutomatica'];
} else if (isset($_GET['generaPublicacionAutomatica'])) {
    $_POST['generaPublicacionAutomatica'] = $_GET['generaPublicacionAutomatica'];
} else {
    $_POST['generaPublicacionAutomatica'] = "";
}

if (isset($_POST['showmovil'])) {
    $_POST['showmovil'] = $_POST['showmovil'];
} else if (isset($_GET['showmovil'])) {
    $_POST['showmovil'] = $_GET['showmovil'];
} else {
    $_POST['showmovil'] = "";
}

if (isset($_POST['ordendesplegar'])) {
    $_POST['ordendesplegar'] = $_POST['ordendesplegar'];
} else if (isset($_GET['ordendesplegar'])) {
    $_POST['ordendesplegar'] = $_GET['ordendesplegar'];
} else {
    $_POST['ordendesplegar'] = "";
}

if (isset($_POST['MensajeOC'])) {
    $_POST['MensajeOC'] = $_POST['MensajeOC'];
} else if (isset($_GET['MensajeOC'])) {
    $_POST['MensajeOC'] = $_GET['MensajeOC'];
} else {
    $_POST['MensajeOC'] = "";
}

if (isset($_POST['MensajePV'])) {
    $_POST['MensajePV'] = $_POST['MensajePV'];
} else if (isset($_GET['MensajePV'])) {
    $_POST['MensajePV'] = $_GET['MensajePV'];
} else {
    $_POST['MensajePV'] = "";
}

// if (isset($_POST['factesquemadoalto'])) {
//     $_POST['factesquemadoalto'] = $_POST['factesquemadoalto'];
// } else if (isset($_GET['factesquemadoalto'])) {
//     $_POST['factesquemadoalto'] = $_GET['factesquemadoalto'];
// } else {
//     $_POST['factesquemadoalto'] = "";
// }
$_POST['factesquemadoalto'] = 0;

// if (isset($_POST['factesquemadoancho'])) {
//     $_POST['factesquemadoancho'] = $_POST['factesquemadoancho'];
// } else if (isset($_GET['factesquemadoancho'])) {
//     $_POST['factesquemadoancho'] = $_GET['factesquemadoancho'];
// } else {
//     $_POST['factesquemadoancho'] = 0;
// }
$_POST['factesquemadoancho'] = 0;

if (isset($_POST['SelectedCategory'])) {
    $_POST['SelectedCategory'] = $_POST['SelectedCategory'];
} else if (isset($_GET['SelectedCategory'])) {
    $_POST['SelectedCategory'] = $_GET['SelectedCategory'];
} else {
    $_POST['SelectedCategory'] = "";
}

// if (isset($_POST['margenautcost'])) {
//     $_POST['margenautcost'] = $_POST['margenautcost'];
// } else if (isset($_GET['margenautcost'])) {
//     $_POST['margenautcost'] = $_GET['margenautcost'];
// } else {
//     $_POST['margenautcost'] = 0;
// }
$_POST['margenautcost'] = 0;

if (isset($_POST['SelectedCategory'])) {
    $_POST['SelectedCategory'] = $_POST['SelectedCategory'];
} else if (isset($_GET['SelectedCategory'])) {
    $_POST['SelectedCategory'] = $_GET['SelectedCategory'];
} else {
    $_POST['SelectedCategory'] = "";
}

if (isset($_POST['typeoperationdiot'])) {
    $_POST['typeoperationdiot'] = $_POST['typeoperationdiot'];
} else if (isset($_GET['typeoperationdiot'])) {
    $_POST['typeoperationdiot'] = $_GET['typeoperationdiot'];
} else {
    $_POST['typeoperationdiot'] = "";
}

if (isset($_POST['PropReqSO0'])) {
    $_POST['PropReqSO0'] = $_POST['PropReqSO0'];
} else if (isset($_GET['PropReqSO0'])) {
    $_POST['PropReqSO0'] = $_GET['PropReqSO0'];
} else {
    $_POST['PropReqSO0'] = "";
}

if (isset($_POST['Prodlineid'])) {
    $_POST['Prodlineid'] = $_POST['Prodlineid'];
} else if (isset($_GET['Prodlineid'])) {
    $_POST['Prodlineid'] = $_GET['Prodlineid'];
} else {
    $_POST['Prodlineid'] = "";
}

if (isset($_POST['selectTipoGasto'])) {
    $_POST['selectTipoGasto'] = $_POST['selectTipoGasto'];
} else if (isset($_GET['selectTipoGasto'])) {
    $_POST['selectTipoGasto'] = $_GET['selectTipoGasto'];
} else {
    $_POST['selectTipoGasto'] = "";
}

if (isset($_POST['PurchPriceVarAct'])) {
    $_POST['PurchPriceVarAct'] = $_POST['PurchPriceVarAct'];
} else if (isset($_GET['PurchPriceVarAct'])) {
    $_POST['PurchPriceVarAct'] = $_GET['PurchPriceVarAct'];
} else {
    $_POST['PurchPriceVarAct'] = "";
}

if (isset($_POST['MaterialUseageVarAc'])) {
    $_POST['MaterialUseageVarAc'] = $_POST['MaterialUseageVarAc'];
} else if (isset($_GET['MaterialUseageVarAc'])) {
    $_POST['MaterialUseageVarAc'] = $_GET['MaterialUseageVarAc'];
} else {
    $_POST['MaterialUseageVarAc'] = "";
}

// if (isset($_POST['UseInternal'])) {
//     $_POST['UseInternal'] = $_POST['UseInternal'];
// } else if (isset($_GET['UseInternal'])) {
//     $_POST['UseInternal'] = $_GET['UseInternal'];
// } else {
//     $_POST['UseInternal'] = 0;
// }
$_POST['UseInternal'] = 0;

if (isset($_POST['stockshipty'])) {
    $_POST['stockshipty'] = $_POST['stockshipty'];
} else if (isset($_GET['stockshipty'])) {
    $_POST['stockshipty'] = $_GET['stockshipty'];
} else {
    $_POST['stockshipty'] = "";
}

if (isset($_GET['DeleteProperty'])) {
    $ErrMsg = _('No se pudo eliminar la propiedad') . ' ' . $_GET['DeleteProperty'] . ' ' . _('porque');
    $sql = "DELETE FROM stockitemproperties WHERE stkcatpropid=" . $_GET['DeleteProperty'];
    $result = DB_query($sql, $db, $ErrMsg);
    $sql = "DELETE FROM stockcatproperties WHERE stkcatpropid=" . $_GET['DeleteProperty'];
    $result = DB_query($sql, $db, $ErrMsg);
    prnMsg(_('Eliminar la propiedad') . ' ' . $_GET['DeleteProperty'], 'success');
}

if (isset($_POST['btnProcesar'])) {
    $InputError = 0;

    $_POST['CategoryID'] = strtoupper($_POST['CategoryID']);

    if (strlen($_POST['CategoryID']) != 3) {
        $InputError = 1;
        prnMsg(_('El código de Partida Genérica debe ser de 3 dígitos'), 'error');
    } elseif (strlen($_POST['CategoryDescription']) > 255) {
        $InputError = 1;
        prnMsg(_('La descripción de la Partida Genérica debe de ser menor de 250 caracteres'), 'error');
    } elseif ($_POST['StockType'] !='D' and $_POST['StockType'] !='L' and $_POST['StockType'] !='F' and $_POST['StockType'] !='M') {
        $InputError = 1;
        prnMsg(_('El tipo de Partida Genérica debe ser uno de ') . ' "D" - ' . _('elemento simulado') . ', "L" - ' . _('Trabajo articulo comun') . ', "F" - ' . _('producto terminado') . ' ' . _('o') . ' "M" - ' . _('Materias primas'), 'error');
    } elseif (empty(trim($_POST['CategoryID']))) {
        $InputError = 1;
        prnMsg(_('El código de Partida Genérica se encuentra vacío'), 'error');
    }

    $claveIden = "";
    // if ($validarIdentificador == 1) {
    if (trim($_POST['txtUR']) != '' || trim($_POST['txtUE']) != '' || trim($_POST['txtPP']) != '') {
        $ur = $_POST['txtUR'];
        $ue = $_POST['txtUE'];
        $pp = $_POST['txtPP'];
        // Validar UR
        $SQL = "SELECT tagref FROM tags WHERE tagref = '".trim($ur)."'";
        $ErrMsg = "No se obtuvo información de la UR";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $InputError = 1;
            prnMsg(_('No existe la UR '. $ur), 'error');
        }
        // Validar UE
        $SQL = "SELECT ue FROM tb_cat_unidades_ejecutoras WHERE ue = '".trim($ue)."'";
        $ErrMsg = "No se obtuvo información de la UE";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $InputError = 1;
            prnMsg(_('No existe la UE '. $ue), 'error');
        }
        // Validar Programa Presupuestario
        $SQL = "SELECT cppt FROM tb_cat_programa_presupuestario WHERE cppt = '".trim($pp)."'";
        $ErrMsg = "No se obtuvo información del Programa Presupuestario";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $InputError = 1;
            prnMsg(_('No existe el Programa Presupuestario '. $pp), 'error');
        }

        $claveIden = $ur."-".$ue."-".$pp;
    }

    if (!$SelectedCategory) {
        $SQL = "SELECT categoryid FROM stockcategory 
                WHERE 
                categoryid = '".$_POST['CategoryID']."'";
        $ErrMsg = "Validar Código de Categoría de Inventario";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) > 0) {
            //$InputError = 1;
            //prnMsg(_('El código de Partida Genérica '.$_POST['CategoryID'].' ya se encuentra en el sistema'), 'error');
        }
    }
    
    /*Validacion para saber si el valor de "afecto a inventario" fue habilitado o no*/
    if (isset($_POST['afecto']) and !empty($_POST['afecto'])) {
        $afecto = 1;
    } else {
        $afecto = 0;
    }
    if ($_POST['margensales']== "") {
        $_POST['margensales'] = 0;
    }
    if ($_POST['flujo']!= "") {
        $flujo = $_POST['flujo'];
    } else {
        $flujo = 0;
    }
    
    if (isset($_POST['cashdiscount']) and !empty($_POST['cashdiscount'])) {
        $cashdiscount = $_POST['cashdiscount'];
    } else {
        $cashdiscount = 0;
    }
    
    if (isset($_POST['changeprecio']) and !empty($_POST['changeprecio'])) {
        $changeprecio = 1;
    } else {
        $changeprecio = 0;
    }
    
    if (isset($_POST['warrantycost']) and !empty($_POST['warrantycost'])) {
        $_POST['warrantycost'] = 0;
    }
    
    $discountInPriceListOnPrice = 0;
    $discountInComercialOnPrice = 0;
    if (isset($_POST['descLPonPrice'])) {
        $discountInPriceListOnPrice = 1;
    }
    
    if (isset($_POST['descCOMonPrice'])) {
        $discountInComercialOnPrice = 1;
    }
    
    $_POST['image'] = '';
    if (empty($_FILES['image']['name']) == false) {
        if ($InputError != 1) {
            if ((($_FILES["image"]["type"] == "image/gif")
            || ($_FILES["image"]["type"] == "image/jpeg")
            || ($_FILES["image"]["type"] == "image/png")
            || ($_FILES["image"]["type"] == "image/pjpeg"))) {
                $dir = "./images";
                include "includes/UploadClass.php";
                $upload = new Upload();
                $upload->set_max_size(999000);
                $upload->set_directory($dir);
                $upload->set_tmp_name($_FILES['image']['tmp_name']);
                $upload->set_file_size($_FILES['image']['size']);
                $upload->set_file_type($_FILES['image']['type']);
                $upload->set_file_name($_FILES['image']['name']);
                $upload->start_copy();
                $upload->resize(150, 150);
                if ($upload->is_ok()) {
                    $_POST['image'] = $upload->user_full_name;
                } else {
                    prnMsg(_($upload->error()), 'error');
                }
            } else {
                prnMsg(_("El archivo que está intentando subir no es una imagen"), 'error');
            }
        }
    }

    // Validar Registros (Partida Genérica, Cuenta Cargo, Cuenta Abono, Identificador)
    $sqlWhere = "";
    if (trim($_POST['txtRegistro']) != "") {
        $sqlWhere .= " AND id <> '".$_POST['txtRegistro']."' ";
    }
    if ($validarIdentificador == 1) {
        $sqlWhere .= " AND ln_clave = '".$claveIden."' ";
    }
    $SQL = "SELECT categoryid FROM stockcategory 
    WHERE categoryid = '".$_POST['CategoryID']."' AND stockact = '".$_POST['StockAct']."'
    AND accountegreso = '".$_POST['accountegreso']."'
    AND adjglact = '".$_POST['AdjGLAct']."'
    AND ln_abono_salida = '".$_POST['ln_abono_salida']."' ".$sqlWhere;
    // echo "<br>SQL: ".$SQL;
    $ErrMsg = "Validar Código de La Partida Genérica";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $InputError = 1;
        $mensaje = "";
        if ($validarIdentificador == 1 && trim($claveIden) != '') {
            $mensaje = " - ".$claveIden;
        }
        prnMsg(_('La Partida Genérica ya existe: ').$_POST['CategoryID']." - ".$_POST['StockAct']." - ".$_POST['accountegreso']." - ".$_POST['AdjGLAct']." - ".$_POST['ln_abono_salida'].$mensaje, 'error');
    }

    if ($SelectedCategory and $InputError !=1) {
        $updateImageSQL = "";
        if (empty($_POST['image']) == false) {
            $updateImageSQL = "image='" . $_POST['image'] . "',";
        }
        
        // generaPublicacionAutomatica = '".$_POST['generaPublicacionAutomatica']."',
        $sql = "UPDATE stockcategory SET stocktype = '" . $_POST['StockType'] . "',
        categorydescription = '" . $_POST['CategoryDescription'] . "',
        textimage = '" . $_POST['textimage'] . "',
        $updateImageSQL
        stockact = '" . $_POST['StockAct'] . "',
        adjglact = '" . $_POST['AdjGLAct'] . "',
        purchpricevaract = '" . $_POST['PurchPriceVarAct'] . "',
        materialuseagevarac = '" . $_POST['MaterialUseageVarAc'] . "',
        internaluse = " . $_POST['UseInternal'] . ",
        wipact = '" . $_POST['WIPAct'] . "',
        allowNarrativePOLine = '". $_POST['allowNarrativePOLine'] ."',
        margenaut = '". $_POST['margenaut'] ."',
        margenautcost = ". $_POST['margenautcost'] .",
        warrantycost = ". $_POST['warrantycost'] .",
        minimummarginsales = '". $_POST['margensales'] ."',
        prodLineId='". $_POST['Prodlineid']."',
        redinvoice='".$afecto."',
        disabledprice='".$changeprecio."',
        idflujo='".$flujo."',
        cashdiscount = '".$cashdiscount."',
        deductibleflag = '" . $_POST['deductibleflag'] . "',
        u_typeoperation = '" . $_POST['u_typeoperation'] . "',
        typeoperationdiot = '" . $_POST['typeoperationdiot'] . "',
        discountInPriceListOnPrice = '".$discountInPriceListOnPrice ."',
        discountInComercialOnPrice	= '".$discountInComercialOnPrice."',
        showmovil = '".$_POST['showmovil']."',
        ordendesplegar = '".$_POST['ordendesplegar']."',
        minimo = ".$_POST['minimo'].",
        optimo = ".$_POST['optimo'].",
        maximo = ".$_POST['maximo'].",
        CodigoPanelControl = '".$_POST['CodigoPanelControl']."',
        stockshipty='".$_POST['stockshipty']."',
        MensajeOC = '".$_POST['MensajeOC']."',
        MensajePV = '".$_POST['MensajePV']."',
        factesquemadoancho = ".$_POST['factesquemadoancho'].",
        factesquemadoalto = '".$_POST['factesquemadoalto']."',
        diascaducidad= '".$_POST['caducidad']."',
        accounttransfer='".$_POST['accounttransfer']."',
        accountegreso='".$_POST['accountegreso']."',
        nu_tipo_gasto='".$_POST['selectTipoGasto']."',
        ln_clave = '".$claveIden."',
        categoryid = '".$_POST['CategoryID']."',
        ln_abono_salida = '".$_POST['ln_abono_salida']."'
        WHERE
        id = '".$_POST['txtRegistro']."'";
        //echo $sql;
        $ErrMsg = _('No se pudo actualizar la Partida Genérica') . $_POST['CategoryDescription'] . _('porque');
        $result = DB_query($sql, $db, $ErrMsg);

        if (!isset($_POST['PropertyCounter'])) {
            $_POST['PropertyCounter'] = "-1";
        }

        for ($i=0; $i<=$_POST['PropertyCounter']; $i++) {
            if (isset($_POST['PropReqIMP' . $i]) and $_POST['PropReqIMP' . $i] == true) {
                $_POST['PropReqIMP' .$i] = 1;
            } else {
                $_POST['PropReqIMP' .$i] = 0;
            }
            
            if (isset($_POST['PropReqPRO' . $i]) and $_POST['PropReqPRO' . $i] == true) {
                $_POST['PropReqPRO' .$i] = 1;
            } else {
                $_POST['PropReqPRO' .$i] = 0;
            }
            
            if (isset($_POST['PropReqFoto' . $i]) and $_POST['PropReqFoto' . $i] == true) {
                $_POST['PropReqFoto' .$i] = 1;
            } else {
                $_POST['PropReqFoto' .$i] = 0;
            }
            
            if (isset($_POST['requiredtocalendar' . $i]) and $_POST['requiredtocalendar' . $i] == true) {
                $_POST['requiredtocalendar' .$i] = 1;
            } else {
                $_POST['requiredtocalendar' .$i] = 0;
            }
            
            if (isset($_POST['PropAddrLink' . $i]) and $_POST['PropAddrLink' . $i] == true) {
                $_POST['PropAddrLink' .$i] = 1;
            } else {
                $_POST['PropAddrLink' .$i] = 0;
            }
            
            if (isset($_POST['PropReqToday' . $i]) and $_POST['PropReqToday' . $i] == true) {
                $_POST['PropReqToday' .$i] = 1;
            } else {
                $_POST['PropReqToday' .$i] = 0;
            }
            //
            if ($_POST['PropID' .$i] =='NewProperty' and strlen($_POST['PropLabel'.$i])>0) {
                $sql = "INSERT INTO stockcatproperties (categoryid,
        								label,
        								controltype,
        								defaultvalue,
        								reqatsalesorder,
        								reqatprint,
        								requiredtoprocess,
        								addresslink,
        								addressref,
        								requiredtoday,
        								requiredphoto,
        								requiredtocalendar,
        								Ordenar)
        							VALUES ('" . $SelectedCategory . "',
        								'" . $_POST['PropLabel' . $i] . "',
        								'" . $_POST['PropControlType' . $i] . "',
        								'" . $_POST['PropDefault' .$i] . "',
        								'" . $_POST['PropReqSO' .$i] . "',
        								'" . $_POST['PropReqIMP' .$i] . "',
        								'" . $_POST['PropReqPRO' .$i] . "',
        								'" . $_POST['PropAddrLink' .$i] . "',
        								'" . $_POST['PropAddrRef' .$i] . "',
        								'" . $_POST['PropReqToday' .$i] . "',
        								'" . $_POST['PropReqFoto' .$i] . "',
        								'".  $_POST['requiredtocalendar'.$i]."',
        								'".$_POST['Ordernarprop'.$i]."')";
                $ErrMsg = _('No se pudo insertar la propiedad de la Partida Genérica por') . $_POST['PropLabel' . $i];
                $result = DB_query($sql, $db, $ErrMsg);
            } elseif ($_POST['PropID' .$i] !='NewProperty') { //we could be amending existing properties
                $sql = "UPDATE stockcatproperties SET label ='" . $_POST['PropLabel' . $i] . "',
        											  controltype = " . $_POST['PropControlType' . $i] . ",
        											  defaultvalue = '" . $_POST['PropDefault' .$i] . "',
        											  reqatsalesorder = " . $_POST['PropReqSO' .$i] . ",
        											  requiredphoto = " . $_POST['PropReqFoto' .$i] . ",
        											  reqatprint = " . $_POST['PropReqIMP' .$i] . ",
        											  requiredtoprocess = " . $_POST['PropReqPRO' .$i] . ",
        											  addresslink = '" . $_POST['PropAddrLink' .$i] . "',
        											  addressref = '" . $_POST['PropAddrRef' .$i] . "',
        											  requiredtoday = '" . $_POST['PropReqToday' .$i] . "',
        											  requiredtocalendar = '".$_POST['requiredtocalendar'.$i]."',
        											  Ordenar = '".$_POST['Ordernarprop'.$i]."'
        				WHERE stkcatpropid =" . $_POST['PropID' .$i];
                //echo $sql;
                $ErrMsg = _('Se actualizo la propiedad de la Partida Genérica para') . ' ' . $_POST['PropLabel' . $i];
                $result = DB_query($sql, $db, $ErrMsg);
            }
        }
        
        // Se agrega validacion de listas de precios

        $SQL = 'DELETE FROM  salespricesbycategory WHERE categoryid="'.$SelectedCategory.'"';
        //echo $SQL;
        $Result = DB_query($SQL, $db);
        
        $sql="select typeabbrev
					from salestypes";
        $Resultprice = DB_query($sql, $db);
        while ($myrowprices=DB_fetch_array($Resultprice)) {
            if (isset($_POST['lista_' . $myrowprices['typeabbrev']])) {
                if ($_POST['lista_' . $myrowprices['typeabbrev']] == 1) {
                    $IncrementPercentage=$_POST[$myrowprices['typeabbrev']]/100;
                    if ($IncrementPercentage>0) {
                        $SQL = "INSERT INTO  salespricesbycategory(categoryid,percent,typeabbrev) 
		        				VALUES('".$SelectedCategory."','".$IncrementPercentage."','".$myrowprices['typeabbrev']."')";
                        //echo $SQL;
                        $Result = DB_query($SQL, $db);
                    }
                }
            }
        }

        prnMsg(_('Se modificó la Partida Genérica ') . $SelectedCategory . ' ' . $_POST['CategoryDescription'], 'success');
    } elseif ($InputError !=1) {
        if (isset($_POST['margenaut']) && $_POST['margenaut']!="") {
            $sql = "INSERT INTO stockcategory (categoryid,
            stocktype,
            categorydescription,
            textimage,
            image,
            stockact,
            adjglact,
            purchpricevaract,
            materialuseagevarac,
            internaluse,
            wipact,
            allowNarrativePOLine,
            margenaut,
            margenautcost,
            prodLineId,
            minimummarginsales,
            redinvoice,
            warrantycost,
            disabledprice ,
            idflujo,
            cashdiscount,
            deductibleflag,
            u_typeoperation,
            typeoperationdiot,
            discountInPriceListOnPrice,
            discountInComercialOnPrice,            
            showmovil,
            ordendesplegar,
            optimo,
            minimo,
            maximo,
            stockshipty,
            CodigoPanelControl,
            MensajeOC,
            MensajePV,
            factesquemadoancho,
            factesquemadoalto,
            diascaducidad,
            accounttransfer,
            accountegreso,
            nu_tipo_gasto,
            ln_clave,
            ln_abono_salida
            )
            VALUES (
            '" . $_POST['CategoryID'] . "',
            '" . $_POST['StockType'] . "',
            '" . $_POST['CategoryDescription'] . "',
            '" . $_POST['textimage'] . "',
            '" . $_POST['image'] . "',
            '" . $_POST['StockAct'] . "',
            '" . $_POST['AdjGLAct'] . "',
            '" . $_POST['PurchPriceVarAct'] . "',
            '" . $_POST['MaterialUseageVarAc'] . "',
            " . $_POST['UseInternal'] . ",
            '" . $_POST['WIPAct'] . "',
            '" . $_POST['allowNarrativePOLine'] . "',
            '" . $_POST['margenaut'] . "',
            ". $_POST['margenautcost'].",
            '" . $_POST['Prodlineid']."',
            '" . $_POST['margensales']."',
            '" .$afecto."',
            " . $_POST['warrantycost'].",
            '" .$changeprecio."',
            '" .$flujo."',
            '" .$cashdiscount."',
            '" .$_POST['deductibleflag']."',
            '" .$_POST['u_typeoperation']."',
            '" .$_POST['typeoperationdiot']."',
            '".$discountInPriceListOnPrice ."',
            '".$discountInComercialOnPrice."',
            '".$_POST['showmovil']."',
            '".$_POST['ordendesplegar']."',
            ".$_POST['optimo'].",
            ".$_POST['minimo'].",
            ".$_POST['maximo'].",
            '".$_POST['stockshipty']."',
            '".$_POST['CodigoPanelControl']."',
            '".$_POST['MensajeOC']."',
            '".$_POST['MensajePV']."',
            ".$_POST['factesquemadoancho'].",
            '".$_POST['factesquemadoalto']."',
            '".$_POST['caducidad']."',
            '".$_POST['accounttransfer']."',
            '".$_POST['accountegreso']."',
            '".$_POST['selectTipoGasto']."',
            '".$claveIden."',
            '".$_POST['ln_abono_salida']."'	
            )";//
            $ErrMsg = _('No se pudo insertar la nueva Partida Genérica ') . $_POST['CategoryDescription'] . _('porque');
            $result = DB_query($sql, $db, $ErrMsg);
            prnMsg(_('La Partida Genérica ') . $_POST['CategoryID'] . ' ' . $_POST['CategoryDescription'] . ' se agregó', 'success');
        } else {
            $sql = "INSERT INTO stockcategory (categoryid,
            stocktype,
            categorydescription,
            textimage,
            image,
            stockact,
            adjglact,
            purchpricevaract,
            materialuseagevarac,
            internaluse,
            wipact,
            allowNarrativePOLine,
            margenaut,
            margenautcost,
            prodLineId,
            minimummarginsales,
            warrantycost,
            redinvoice,
            disabledprice ,
            idflujo,
            cashdiscount,
            deductibleflag,
            u_typeoperation,
            typeoperationdiot,
            discountInPriceListOnPrice,
            discountInComercialOnPrice,
            ordendesplegar,
            optimo,
            minimo,
            maximo,
            stockshipty,
            CodigoPanelControl,
            MensajeOC,
            MensajePV,
            factesquemadoancho,
            factesquemadoalto, 
            diascaducidad,
            accounttransfer,
            accountegreso,
            nu_tipo_gasto,
            ln_clave,
            ln_abono_salida
            )
            VALUES (
            '" . $_POST['CategoryID'] . "',
            '" . $_POST['StockType'] . "',
            '" . $_POST['CategoryDescription'] . "',
            '" . $_POST['textimage'] . "',
            '" . $_POST['image'] . "',
            '" . $_POST['StockAct'] . "',
            '" . $_POST['AdjGLAct'] . "',
            '" . $_POST['PurchPriceVarAct'] . "',
            '" . $_POST['MaterialUseageVarAc'] . "',
            " . $_POST['UseInternal'] . ",
            '" . $_POST['WIPAct'] . "',
            '" . $_POST['allowNarrativePOLine'] . "',
            " . 0 . ",
            ". $_POST['margenautcost'].",		
            '" . $_POST['Prodlineid']."',
            '" . $_POST['margensales']."',
            " . $_POST['warrantycost'].",
            '" .$afecto."',
            '" .$changeprecio."',
            '" .$flujo."',
            '" .$cashdiscount."',
            '" .$_POST['deductibleflag']."',
            '" .$_POST['u_typeoperation']."',
            '" .$_POST['typeoperationdiot']."',
            '".$discountInPriceListOnPrice ."',
            '".$discountInComercialOnPrice."',
            '".$_POST['ordendesplegar']."',
            ".$_POST['optimo'].",
            ".$_POST['minimo'].",
            ".$_POST['maximo'].",
            '".$_POST['stockshipty']."',
            '".$_POST['CodigoPanelControl']."',
            '".$_POST['MensajeOC']."',
            '".$_POST['MensajePV']."',
            ".$_POST['factesquemadoancho'].",
            '".$_POST['factesquemadoalto']."',
            '".$_POST['caducidad']."',
            '".$_POST['accounttransfer']."',
            '".$_POST['accountegreso']."',
            '".$_POST['selectTipoGasto']."',
            '".$claveIden."',
            '".$_POST['ln_abono_salida']."'
            )";
            $ErrMsg = _('No se pudo insertar la nueva Partida Genérica ') . $_POST['CategoryDescription'] . _('porque');
            $result = DB_query($sql, $db, $ErrMsg);
            prnMsg(_('La Partida Genérica ') . $_POST['CategoryID'] . ' ' . $_POST['CategoryDescription'] . ' se agregó', 'success');
        }
        
        $_POST['txtRegistro'] = DB_Last_Insert_ID($db, 'stockcategory', 'id');

        $SQL = "SELECT userid, categoryid FROM sec_stockcategory 
                WHERE 
                userid = '".$_SESSION['UserID']."' 
                AND categoryid = '".$_POST['CategoryID']."' ";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            // Se agrego tabla de seguridad de categoria de inventarios
            $sql = "INSERT INTO sec_stockcategory (userid, 
                                                    categoryid)
                    VALUES('".$_SESSION['UserID']."',
                            '".$_POST['txtRegistro']."')";
            // Se comenta configuracion de usuario
            // $result = DB_query($sql, $db, $ErrMsg);
        }

        // Se agrega validacion de listas de precios
        $SQL = 'DELETE FROM  salespricesbycategory WHERE categoryid="'.$_POST['CategoryID'].'"';
        //echo $SQL;
        $Result = DB_query($SQL, $db);
        
        $sql="select typeabbrev from salestypes";
        $Resultprice = DB_query($sql, $db);
        while ($myrowprices=DB_fetch_array($Resultprice)) {
            if ($_POST['lista_' . $myrowprices['typeabbrev']] == 1) {
                $IncrementPercentage=$_POST[$myrowprices['typeabbrev']]/100;
                if ($IncrementPercentage>0) {
                    $SQL = "INSERT INTO  salespricesbycategory(categoryid,percent,typeabbrev)
	        				VALUES('".$_POST['CategoryID']."','".$IncrementPercentage."','".$myrowprices['typeabbrev']."')";
                    //echo $SQL;
                    $Result = DB_query($SQL, $db);
                }
            }
        }
    }

    if ($InputError !=1) {
        unset($SelectedCategory);
        unset($_POST['txtRegistro']);
        unset($_POST['CategoryID']);
        unset($_POST['StockType']);
        unset($_POST['CategoryDescription']);
        unset($_POST['textimage']);
        unset($_POST['image']);
        unset($_POST['StockAct']);
        unset($_POST['AdjGLAct']);
        unset($_POST['PurchPriceVarAct']);
        unset($_POST['MaterialUseageVarAc']);
        unset($_POST['WIPAct']);
        unset($_POST['allowNarrativePOLine']);
        unset($_POST['Prodlineid']);
        unset($_POST['UseInternal']);
        unset($_POST['margensales']);
        unset($_POST['warrantycost']);
        unset($_POST['deductibleflag']);
        unset($_POST['u_typeoperation']);
        //unset($_POST['typeoperationdiot']);
        unset($_POST['minimo']);
        unset($_POST['optimo']);
        unset($_POST['maximo']);
        unset($_POST['MensajeOC']);
        unset($_POST['MensajePV']);
        unset($_POST['factesquemadoancho']);
        unset($_POST['factesquemadoalto']);
        unset($_POST['accounttransfer']);
        unset($_POST['accountegreso']);
        unset($_POST['txtUR']);
        unset($_POST['txtUE']);
        unset($_POST['txtPP']);
        unset($_POST['ln_abono_salida']);
    }
} elseif (isset($_GET['delete'])) {
    $sql= "SELECT COUNT(*) FROM stockmaster WHERE stockmaster.categoryid='$SelectedCategory'";
    $result = DB_query($sql, $db);
    $myrow = DB_fetch_row($result);
    if ($myrow[0]>0) {
        prnMsg(_('No se pudo eliminar la Partida Genérica porque existen bienes con esta Partida') .
            '<br> ' . _('Existen') . ' ' . $myrow[0] . ' ' . _('Bienes haciendo referencia a esta Partida Genérica'), 'warn');
    } else {
        $sql = "SELECT COUNT(*) FROM salesglpostings WHERE stkcat='$SelectedCategory'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0]>0) {
            prnMsg(_('No se puede eliminar la Partida Genérica porque se esta utilizando por VENTAS') . ' - ' . _('Integracion Contable') . '. ' . _('Elimine los registros en la Interfase Contable de Ventas que utilizan esta Partida Genérica primero'), 'warn');
        } else {
            $sql = "SELECT COUNT(*) FROM cogsglpostings WHERE stkcat='$SelectedCategory'";
            $result = DB_query($sql, $db);
            $myrow = DB_fetch_row($result);
            if ($myrow[0]>0) {
                prnMsg(_('No se puede eliminar la Partida Genérica porque esta utilizandose por COSTO DE VENTAS') . ' - ' . _('Integracion Contable') . '. ' . _('Elimine los registros en la Interfase Contable de Costos que utilizan esta Partida Genérica primero'), 'warn');
            } else {
                $sql="DELETE FROM stockcategory WHERE id='".$_POST['txtRegistro']."'";
                $result = DB_query($sql, $db);
                prnMsg(_('Se eliminó la Partida Genérica ') . $SelectedCategory, 'success');
                unset($SelectedCategory);
            }
        }
    }
}

if (!isset($SelectedCategory)) {
    $sql = "SELECT categoryid,
    categorydescription,
    stocktype,
    stockact,
    adjglact,
    purchpricevaract,
    materialuseagevarac,
    wipact,
    adjglacttransf,
    allowNarrativePOLine,
    margenaut,
    stockcategory.prodLineId,
    ProdLine.Description,
    redinvoice,
    prdflujos.flujo as flujo,
    disabledprice,
    internaluse,
    cashdiscount,
    minimummarginsales,
    warrantycost,
    case when deductibleflag = 1 then 'SI' else 'NO' end as deductibleflag,
    stockcategory.u_typeoperation,
    accountingtransactiontype.typeoperation,
    IFNULL(typeoperationdiot.typeoperation,'') as typeoperationdiot,
    ProdLine.textimage,
    ProdLine.image,
    stockshipty,
    accountegreso,
    chartmaster.accountname as nombreCargo,
    chartmaster2.accountname as nombreAbono,
    stockcategory.nu_tipo_gasto,
    stockcategory.ln_clave,
    stockcategory.id,
    stockcategory.ln_abono_salida,
    chartmasterAlmCar.accountname as nombreCargoAlmacen,
    chartmasterAlmAbo.accountname as nombreAbonoAlmacen
    FROM stockcategory
    left join typeoperationdiot ON stockcategory.typeoperationdiot = typeoperationdiot.u_typeoperation
    left join ProdLine on stockcategory.prodLineId=ProdLine.Prodlineid
    left join prdflujos on stockcategory.idflujo=prdflujos.idflujo
    left join accountingtransactiontype ON stockcategory.u_typeoperation = accountingtransactiontype.u_typeoperation
    LEFT JOIN chartmaster ON chartmaster.accountcode = stockcategory.stockact
    LEFT JOIN chartmaster chartmaster2 ON chartmaster2.accountcode = stockcategory.accountegreso
    LEFT JOIN chartmaster chartmasterAlmCar ON chartmasterAlmCar.accountcode = stockcategory.adjglact
    LEFT JOIN chartmaster chartmasterAlmAbo ON chartmasterAlmAbo.accountcode = stockcategory.ln_abono_salida
    WHERE stocktype<>'A'
    ORDER BY categoryid asc";
    
    if (empty($_POST['busquedaDesc']) == false) {
        $sql .= " AND categorydescription LIKE '%" . $_POST['busquedaDesc'] . "%' ";
    }
        
    if (empty($_POST['lineaDesc']) == false) {
        $sql .= " AND ProdLine.Description LIKE '%" . $_POST['lineaDesc'] . "%' ";
    }
        
    //echo $sql;
    $result = DB_query($sql, $db);

    echo '<form method="post" enctype="multipart/form-data" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
    ?>
        <div class="panel-body">
            <div class="col-md-6 col-xs-12">
                <component-text-label label="Descripción:" id="busquedaDesc" name="busquedaDesc" 
                    placeholder="Descripción" title="Descripción" 
                    value="<?php echo $_POST['busquedaDesc']; ?>"></component-text-label>
            </div>
            <div class="col-md-6 col-xs-12">
                <div class="form-inline row">
                  <div class="col-md-3">
                      <span><label>Línea: </label></span>
                  </div>
                  <div class="col-md-9">
                      <select id="lineaDesc" name="lineaDesc" class="form-control lineaDesc">
                        <option value="">Todas la líneas...</option>
                        <?php
                            $SQL = "SELECT DISTINCT ProdGroup.Description, ProdGroup.Prodgroupid FROM ProdGroup
							INNER JOIN ProdLine USING(Prodgroupid) ORDER BY ProdGroup.Description";
                                $Result=  DB_query($SQL, $db);
                            
                        while ($grupo = DB_fetch_array($Result)) {
                            echo '<option value="">&bull; ' . strtoupper($grupo['Description']) . '</option>';
                                            
                            $sql = "SELECT Prodlineid, Description FROM ProdLine WHERE Prodgroupid = '" . $grupo['Prodgroupid'] . "' ORDER BY Description";
                            $rs = DB_query($sql, $db);
                                        
                            while ($row = DB_fetch_array($rs)) {
                                if ($_POST['lineaDesc'] == $row['Description']) {
                                    echo "<option selected='selected' value='" . $row['Description'] . "'>&nbsp;&nbsp;&nbsp;&nbsp;" . $row['Description'] . "</option>";
                                } else {
                                    echo "<option value='" . $row['Description'] . "'>&nbsp;&nbsp;&nbsp;&nbsp;" . $row['Description'] . "</option>";
                                }
                            }
                        }
                        ?>
                      </select>
                  </div>
              </div>
            </div>
            <div class="col-md-12 col-xs-12" align="center">
                <component-button type="submit" class="glyphicon glyphicon-search" id="Buscar" nama="Buscar" value="Buscar"></component-button>
            </div>
        </div>        
    <?
    echo "</form>";

    $ocultarIdentificador = "";
    if ($validarIdentificador == 0) {
        $ocultarIdentificador = ' style="display: none;" ';
    }

    echo '<div class="table-responsive">';

    echo "<table class='table table-bordered' border='1' style='text-align:center; margin:0 auto;'>";
    echo '<tr class="header-verde">
    <th align=center '.$ocultarIdentificador.'>' . _('Diferenciador') . '</th>
    <th style="text-align:center;">' . _('Partida Genérica') . '</th>
	<th style="text-align:center;">' . _('Descripción') . '</th>
	<th style="text-align:center;">' . _('Tipo Gasto') . '</th>
	<th style="text-align:center;">' . _('Cargo') . '</th>
	<th style="text-align:center;">' . _('Cuenta Cargo') . '</th>
	<th style="text-align:center;">' . _('Abono') . '</th>
	<th style="text-align:center;">' . _('Cuenta Abono') . '</th>
    <th style="text-align:center;">' . _('Cargo') . '</th>
    <th style="text-align:center;">' . _('Cuenta Cargo') . '</th>
    <th style="text-align:center;">' . _('Abono') . '</th>
    <th style="text-align:center;">' . _('Cuenta Abono') . '</th>
	<th '.$ocultoElemento.'>' . _('Cta FACT USO INTERNO') . '</th>
	<th '.$ocultoElemento.'>' . _('Linea Producto') . '</th>
	<th '.$ocultoElemento.'>' . _('Texto en Pedido Venta') . '</th>
	<th '.$ocultoElemento.'>' . _('Margen Aut') . '</th>
	<th '.$ocultoElemento.'>' . _('Facturar s/exist') . '</th>
	<th '.$ocultoElemento.'>' . _('Mod. Precio') . '</th>
	<th '.$ocultoElemento.'>' . _('Flujo') . '</th>
	<th '.$ocultoElemento.'>' . _('Margen Ventas') . '</th>
	<th '.$ocultoElemento.'>' . _('Aplica<br>IETU') . '</th>
	<th '.$ocultoElemento.'>' . _('Deduccion<br>Autorizada') . '</th>
	<th '.$ocultoElemento.'>' . _('Tipo<br>Operacion<br>DIOT') . '</th>
	<th '.$ocultoElemento.'>' . _('Texto Imagen') . '</th>
	<th '.$ocultoElemento.'>' . _('Imagen') . '</th>
	<th>Modificar</th>
    <th>Eliminar</th>
	</tr>';

    $k=0;

    while ($myrow = DB_fetch_array($result)) {
        //=$myrow['idflujo'];
        if ($k==1) {
            echo '<tr class="EvenTableRows">';
            $k=0;
        } else {
            echo '<tr class="OddTableRows">';
            $k=1;
        }
        if ($myrow[13]==1) {
            $redinvoice="Si";
        } else {
            $redinvoice="No";
        }
        if ($myrow[14]!= '') {
            $flujo=$myrow[14];
        } else {
            $flujo="No";
        }
        if ($myrow[15]== 1) {
            $changeprecio="Si";
        } else {
            $changeprecio="No";
        }

        // Modificar
        $enc = new Encryption;
        $url = "&SelectedCategory=>" . $myrow[0]."&StockAct=>".$myrow["stockact"]."&AdjGLAct=>".$myrow["adjglact"]."&id=>".$myrow['id'];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;

        // Eliminar
        $enc = new Encryption;
        $url = "&delete=>true&SelectedCategory=>" . $myrow[0]."&StockAct=>".$myrow["stockact"]."&AdjGLAct=>".$myrow["adjglact"]."&id=>".$myrow['id'];
        $url = $enc->encode($url);
        $liga2= "URL=" . $url;

        if ($myrow['adjglact'] == '-1' || $myrow['adjglact'] == '0') {
            // Si esta vacio no mostrar información
            $myrow['adjglact'] = '';
        }

        if ($myrow['ln_abono_salida'] == '-1' || $myrow['ln_abono_salida'] == '0') {
            // Si esta vacio no mostrar información
            $myrow['ln_abono_salida'] = '';
        }

        printf(
            "
            <td align=center ".$ocultarIdentificador.">%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td align=center>%s</td>
            <td align=center>%s</td>
            <td align=center>%s</td>
            <td align=center>%s</td>
            <td align=center>%s</td>
            <td align=center>%s</td>
            <td align=center>%s</td>
            <td align=center>%s</td>
            <td ".$ocultoElemento." align=right>%s</td>
            <td ".$ocultoElemento." align=right>%s</td>
            <td ".$ocultoElemento." align=right>%s</td>
            <td ".$ocultoElemento." style='text-align:center'>%s</td>
            <td ".$ocultoElemento." style='text-align:center'>%s</td>
            <td ".$ocultoElemento." align=right>%s</td>
            <td ".$ocultoElemento." align=right>%s</td>
            <td ".$ocultoElemento." align=center>%s</td>
            <td ".$ocultoElemento." align=center>%s</td>
            <td ".$ocultoElemento." align=center>%s</td>
            <td ".$ocultoElemento." align=center>%s</td>
            <td ".$ocultoElemento.">%s</td>
            <td ".$ocultoElemento.">%s</td>
            <td><a href=\"%s\">" . _('<span class="glyphicon glyphicon-edit"></span>') . "</td>
            <td><a href=\"%s\">" . _('<span class="glyphicon glyphicon-trash"></span>') . "</td>
            </tr>",
            $myrow['ln_clave'],
            $myrow[0],
            $myrow[1],
            $myrow['nu_tipo_gasto'],
            $myrow['stockact'],
            $myrow['nombreCargo'],
            $myrow['accountegreso'],
            $myrow['nombreAbono'],
            $myrow['adjglact'],
            $myrow['nombreCargoAlmacen'],
            $myrow['ln_abono_salida'],
            $myrow['nombreAbonoAlmacen'],
            $myrow['internaluse'],
            $myrow[12],
            $myrow[9],
            $myrow[10].' %',
            $redinvoice,
            $changeprecio,
            $flujo,
            $myrow[18].' %',
            $myrow[20],
            $myrow[22],
            $myrow[23],
            $myrow[24],
            empty($myrow[25]) ? 'Sin Imagen' : "<a target='_blank' href='" . $myrow[25] . "'>Imagen</a>",
            $_SERVER['PHP_SELF'] . '?' . $liga,
            $_SERVER['PHP_SELF'] . '?' . $liga2
        );
    }
    echo '</table>';

    echo '</div>';
}

?>

<p>
<?php
if (isset($SelectedCategory)) {  ?>
    <!-- <div class='centre'><a href="<?php echo $_SERVER['PHP_SELF'] . '?' . SID;?>"><?php echo _('Muestra Partidas Genéricas'); ?></a></div> -->
<?php } ?>

<p>

<?php

//if (! isset($_GET['delete'])) {
echo '<form name="CategoryForm" method="post" enctype="multipart/form-data" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

if (isset($SelectedCategory)) {
    if (!isset($_POST['UpdateTypes'])) {
        $sql = "SELECT id, categoryid,
        stocktype,
        categorydescription,
        textimage,
        image,
        stockact,
        adjglact,
        purchpricevaract,
        materialuseagevarac,
        wipact,
        allowNarrativePOLine,
        margenaut,
        margenautcost,
        prodLineId,
        redinvoice,
        idflujo,
        disabledprice,
        internaluse,
        cashdiscount,
        minimummarginsales,
        warrantycost,
        deductibleflag,
        u_typeoperation,
        discountInPriceListOnPrice,
        discountInComercialOnPrice,
        generaPublicacionAutomatica,
        showmovil,
        ordendesplegar,
        optimo,
        minimo,
        maximo,
        CodigoPanelControl,
        MensajeOC,
        MensajePV,
        stockshipty,
        factesquemadoancho,
        factesquemadoalto,
        diascaducidad,
        accounttransfer,
        accountegreso,
        nu_tipo_gasto,
        ln_clave,
        ln_abono_salida
        FROM stockcategory
        WHERE id='" . $_POST['txtRegistro'] . "'";
        $result = DB_query($sql, $db);
        $myrow = DB_fetch_array($result);

        $_POST['txtRegistro'] = $myrow['id'];
        $_POST['CategoryID'] = $myrow['categoryid'];
        $_POST['StockType']  = $myrow['stocktype'];
        $_POST['CategoryDescription']  = $myrow['categorydescription'];
        $_POST['textimage']  = $myrow['textimage'];
        $_POST['image']  = $myrow['image'];
        $_POST['StockAct']  = $myrow['stockact'];
        $_POST['AdjGLAct']  = $myrow['adjglact'];
        $_POST['PurchPriceVarAct']  = $myrow['purchpricevaract'];
        $_POST['MaterialUseageVarAc']  = $myrow['materialuseagevarac'];
        $_POST['WIPAct']  = $myrow['wipact'];
        $_POST['allowNarrativePOLine']  = $myrow['allowNarrativePOLine'];
        $_POST['margenaut']  = $myrow['margenaut'];
        $_POST['margenautcost']  = $myrow['margenautcost'];
        $_POST['Prodlineid'] = $myrow['prodLineId'];
        $_POST['afecto']= $myrow['redinvoice'];
        $_POST['changeprecio']= $myrow['disabledprice'];
        $_POST['flujo']= $myrow['idflujo'];
        $_POST['UseInternal']= $myrow['internaluse'];
        $_POST['cashdiscount']= $myrow['cashdiscount'];
        $_POST['margensales']= $myrow['minimummarginsales'];
        $_POST['warrantycost']= $myrow['warrantycost'];
        $_POST['deductibleflag']= $myrow['deductibleflag'];
        $_POST['u_typeoperation']= $myrow['u_typeoperation'];
        $_POST['generaPublicacionAutomatica'] = $myrow['generaPublicacionAutomatica'];
        $_POST['showmovil'] = $myrow['showmovil'];
        $discountInPriceListOnPrice = $myrow['discountInPriceListOnPrice'];
        $discountInComercialOnPrice = $myrow['discountInComercialOnPrice'];
        $_POST['ordendesplegar'] = $myrow['ordendesplegar'];
        $_POST['optimo'] = $myrow['optimo'];
        $_POST['minimo'] = $myrow['minimo'];
        $_POST['maximo'] = $myrow['maximo'];
        $_POST['CodigoPanelControl'] = $myrow['CodigoPanelControl'];
        $_POST['MensajeOC'] = $myrow['MensajeOC'];
        $_POST['MensajePV'] = $myrow['MensajePV'];
        $_POST['stockshipty']=$myrow['stockshipty'];
        $_POST['factesquemadoancho'] = $myrow['factesquemadoancho'];
        $_POST['factesquemadoalto']=$myrow['factesquemadoalto'];
        $_POST["caducidad"]= $myrow['diascaducidad'];
        $_POST['accounttransfer'] = $myrow['accounttransfer'];
        $_POST['accountegreso'] = $myrow['accountegreso'];
        $_POST['selectTipoGasto'] = $myrow['nu_tipo_gasto'];
        $_POST['ln_abono_salida'] = $myrow['ln_abono_salida'];

        $claveSep = explode('-', $myrow['ln_clave']);
        $_POST['txtUR'] = $claveSep[0];
        $_POST['txtUE'] = $claveSep[1];
        $_POST['txtPP'] = $claveSep[2];
    }
    //echo '<input type=hidden name="SelectedCategory" value="' . $SelectedCategory . '">';
    //echo '<input type=hidden name="CategoryID" value="' . $_POST['CategoryID'] . '">';
    //<tr><td>' . _('Codigo de Categoria de Inv.') . ':</td><td>' . $_POST['CategoryID'] . '</td></tr>
    echo '<table style="text-align:center; margin:0 auto;">';
} else {
    if (!isset($_POST['CategoryID'])) {
        $_POST['CategoryID'] = '';
    }
    // <tr><td>' . _('Codigo de Categoria de Inv.') . ':</td>
    // <td><input type="Text" name="CategoryID" size=7 maxlength=6 value="' . $_POST['CategoryID'] . '"></td></tr>
    echo '<table border="0" style="text-align:center; margin:0 auto;">';
}

//Mostrar cuentas de balance
$sql = "SELECT accountcode,
             concat(accountcode,' - ',accountname) as accountname 
             FROM chartmaster,
                  accountgroups
             WHERE chartmaster.group_=accountgroups.groupname and
                   accountgroups.pandl=0
             ORDER BY accountcode";

$BSAccountsResult = DB_query($sql, $db);
//Mostrar cuentas de resultados
$sql = "SELECT accountcode,
             concat(accountcode,' - ',accountname) as accountname 
             FROM chartmaster,
                  accountgroups
             WHERE chartmaster.group_=accountgroups.groupname and
                   accountgroups.pandl!=0
             ORDER BY accountcode";

$PnLAccountsResult = DB_query($sql, $db);

if (!isset($_POST['CategoryDescription'])) {
    $_POST['CategoryDescription'] = '';
}

?>
<div align="left">
  <!--Panel Busqueda-->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title row">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne" style="margin-left: 20px;">
            Información Agregar/Modificar
          </a>
      </h4>
    </div>
    <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <?php
        $ocultarIdentificador = "";
        if ($validarIdentificador == 0) {
            $ocultarIdentificador = 'style="display: none;"';
        }
        if (trim($_POST['txtUR']) == '') {
            $_POST['txtUR'] = fnURDefaultIdentificador($db);
        }
        ?>
        <div class="row clearfix" id="divIdentificador" name="divIdentificador" <?php echo $ocultarIdentificador; ?>>
            <div class="col-md-4 col-xs-12">
                <component-text-label label="UR:" id="txtUR" name="txtUR" maxlength="3" placeholder="UR" title="UR" value="<?php echo $_POST['txtUR']; ?>"></component-text-label>
            </div>
            <div class="col-md-4 col-xs-12">
                <component-number-label label="UE:" id="txtUE" name="txtUE" maxlength="2" placeholder="UE" title="UE" value="<?php echo $_POST['txtUE']; ?>"></component-number-label>
            </div>
            <div class="col-md-4 col-xs-12">
                <component-text-label label="Programa Presup:" id="txtPP" name="txtPP" maxlength="4" placeholder="Programa Presupuestario" title="Programa Presupuestario" value="<?php echo $_POST['txtPP']; ?>"></component-text-label>
            </div>
        </div>
        <br>
        <div class="row clearfix">
            <div class="col-md-6 col-xs-12">
                <?php
                if (isset($SelectedCategory)) {
                    echo '<input type=hidden name="SelectedCategory" value="' . $SelectedCategory . '">';
                }
                echo '<component-number-label label="Partida Genérica:" id="CategoryID" name="CategoryID" 
                        placeholder="Partida Genérica" title="Partida Genérica" value="'.$_POST['CategoryID'].'" maxlength="3" onchange="fnTraeInformacionPartida(this)"></component-number-label>';
                echo '<input type=hidden name="txtRegistro" id="txtRegistro" value="' . $_POST['txtRegistro'] . '" />';
                ?>
            </div>
            <div class="col-md-6 col-xs-12">
                <div class="form-inline row">
                    <div class="col-md-3">
                      <span><label>Tipo de Gasto: </label></span>
                    </div>
                    <div class="col-md-9">
                      <select id="selectTipoGasto" name="selectTipoGasto" class="form-control selectTipoGastoLocal">
                        <?php
                            $SQL = "SELECT ctga, concat(ctga,' - ',descripcion) as descripcion FROM g_cat_tipo_de_gasto WHERE activo = 'S' ORDER BY ctga ASC";
                                $Result=  DB_query($SQL, $db);
                            
                        while ($myrow = DB_fetch_array($Result)) {
                            $selected = "";
                            if (isset($_POST['selectTipoGasto']) and $myrow['ctga']==$_POST['selectTipoGasto']) {
                                $selected = "selected";
                            }
                            echo '<option value="'.$myrow['ctga'] . '" '.$selected.'>' . ($myrow['descripcion'])."</option>";
                        }
                            DB_data_seek($PnLAccountsResult, 0);
                            DB_data_seek($BSAccountsResult, 0);
                        ?>
                      </select>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row clearfix">
            <div class="col-md-6 col-xs-12">
                <!-- <span><label>Cuentas Recepción </label></span> -->
                <div class="form-inline row">
                    <div class="col-md-3">
                      <span><label>Recepción Cargo: </label></span>
                    </div>
                    <div class="col-md-9">
                      <select id="StockAct" name="StockAct" class="form-control StockAct">
                        <?php
                            // 5.1 Grupo - GASTOS DE FUNCIONAMIENTO
                            // 1.1.5 Rubro - Almacenes
                            // 1.2.4 Rubron - Bienes Muebles
                            $SQL="SELECT DISTINCT accountcode, concat(accountcode,' - ',accountname) AS accountname 
                            FROM chartmaster, accountgroups
                            WHERE chartmaster.group_=accountgroups.groupname 
                            AND (accountcode like '5%' OR accountcode like '1.1.5%' OR accountcode like '1.2.4%')
                            ORDER BY accountcode";
                            $Result=  DB_query($SQL, $db);
                        while ($myrow = DB_fetch_array($Result)) {
                            $selected = "";
                            if (isset($_POST['StockAct']) and $myrow['accountcode']==$_POST['StockAct']) {
                                $selected = "selected";
                            }
                            echo '<option value="'.$myrow['accountcode'] . '" '.$selected.'>' . ($myrow['accountname'])."</option>";
                        }
                            DB_data_seek($PnLAccountsResult, 0);
                            DB_data_seek($BSAccountsResult, 0);
                            ?>
                      </select>
                    </div>
                </div>
                <br>
                <div class="form-inline row">
                    <div class="col-md-3">
                      <span><label>Recepción Abono: </label></span>
                    </div>
                    <div class="col-md-9">
                      <select id="accountegreso" name="accountegreso" class="form-control AdjGLAct">
                        <?php
                            // 2.1.1 Rubro - Cuentas por pagar a corto plazo
                            $SQL="SELECT distinct accountcode, concat(accountcode,' - ',accountname) AS accountname 
                            FROM chartmaster, accountgroups
                            WHERE chartmaster.group_=accountgroups.groupname 
                            AND (accountcode like '2.1.1%')
                            ORDER BY accountcode";
                            $Result=  DB_query($SQL, $db);
                        while ($myrow = DB_fetch_array($Result)) {
                            $selected = "";
                            if (isset($_POST['accountegreso']) and $myrow['accountcode']==$_POST['accountegreso']) {
                                $selected = "selected";
                            }
                            echo '<option value="'.$myrow['accountcode'] . '" '.$selected.'>' . ($myrow['accountname'])."</option>";
                        }
                            DB_data_seek($PnLAccountsResult, 0);
                            DB_data_seek($BSAccountsResult, 0);
                        ?>
                      </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xs-12">
                <!-- <span><label>Cuentas Almacén </label></span> -->
                <div class="form-inline row">
                    <div class="col-md-3">
                      <span><label>Salida Cargo: </label></span>
                    </div>
                    <div class="col-md-9">
                      <select id="AdjGLAct" name="AdjGLAct" class="form-control AdjGLAct">
                        <option value="-1">Seleccionar</option>
                        <?php
                            // 2.1.1 Rubro - Cuentas por pagar a corto plazo
                            $SQL="SELECT distinct accountcode, concat(accountcode,' - ',accountname) AS accountname 
                            FROM chartmaster, accountgroups
                            WHERE chartmaster.group_=accountgroups.groupname 
                            AND (accountcode like '5%')
                            ORDER BY accountcode";
                            $Result=  DB_query($SQL, $db);
                        while ($myrow = DB_fetch_array($Result)) {
                            $selected = "";
                            if (isset($_POST['AdjGLAct']) and $myrow['accountcode']==$_POST['AdjGLAct']) {
                                $selected = "selected";
                            }
                            echo '<option value="'.$myrow['accountcode'] . '" '.$selected.'>' . ($myrow['accountname'])."</option>";
                        }
                            DB_data_seek($PnLAccountsResult, 0);
                            DB_data_seek($BSAccountsResult, 0);
                        ?>
                      </select>
                    </div>
                </div>
                <br>
                <div class="form-inline row">
                    <div class="col-md-3">
                      <span><label>Salida Abono: </label></span>
                    </div>
                    <div class="col-md-9">
                      <select id="ln_abono_salida" name="ln_abono_salida" class="form-control ln_abono_salida">
                        <option value="-1">Seleccionar</option>
                        <?php
                            // 5.1 Grupo - GASTOS DE FUNCIONAMIENTO
                            // 1.1.5 Rubro - Almacenes
                            // 1.2.4 Rubron - Bienes Muebles
                            $SQL="SELECT DISTINCT accountcode, concat(accountcode,' - ',accountname) AS accountname 
                            FROM chartmaster, accountgroups
                            WHERE chartmaster.group_=accountgroups.groupname 
                            AND (accountcode like '1.1.5%')
                            ORDER BY accountcode";
                            $Result=  DB_query($SQL, $db);
                        while ($myrow = DB_fetch_array($Result)) {
                            $selected = "";
                            if (isset($_POST['ln_abono_salida']) and $myrow['accountcode']==$_POST['ln_abono_salida']) {
                                $selected = "selected";
                            }
                            echo '<option value="'.$myrow['accountcode'] . '" '.$selected.'>' . ($myrow['accountname'])."</option>";
                        }
                            DB_data_seek($PnLAccountsResult, 0);
                            DB_data_seek($BSAccountsResult, 0);
                        ?>
                      </select>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row clearfix">
            <div class="col-md-6 col-xs-12">
                <div style="display: none;">
                    <component-textarea-label label="Descripción: " id="CategoryDescription" name="CategoryDescription" placeholder="Descripción" title="Descripción" cols="3" rows="4" maxlength="50" 
                value="<?php echo $_POST['CategoryDescription']; ?>"></component-textarea-label>
                </div>
                <component-textarea-label label="Descripción: " id="CategoryDescriptionVisual" name="CategoryDescriptionVisual" placeholder="Descripción" title="Descripción" cols="3" rows="4" maxlength="50" 
                value="<?php echo $_POST['CategoryDescription']; ?>"></component-textarea-label>
            </div>
            <div class="col-md-6 col-xs-12">
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php

echo '<tr '.$ocultoElemento.'><td>' . _('Texto Imagen') . ':</td>
        <td><textarea name="textimage" cols="30">' . $_POST['textimage'] . '</textarea></td></tr>';

echo '<tr '.$ocultoElemento.'><td>' . _('Imagen') . ':</td>
        <td>';

if (empty($_POST['image']) == false) {
    echo '<img src="' . $_POST['image'] . '" alt="imagen" />';
}
    
echo '<input type="file" name="image" size="32" /></td></tr>';

echo '<tr '.$ocultoElemento.'><td>' . _('Margen Automatico en precio') . ':</td>
        <td><input type="Text" name="margenaut" size=10 maxlength=20 value="' . $_POST['margenaut'] . '">'._(' (Valores del 0 al 100)').'</td></tr>';

echo '<tr '.$ocultoElemento.'><td><b>Seccion 1 </b></td><td><input type="button" value="Mostrar" onclick="mostrar1()">';
echo '<input type="button" value="Ocultar" onclick="ocultar1()"></tr>';

echo "<tr><td colspan='2'><table id='oculto' style='display:none;'>";

echo '<tr><td>' . _('Margen Automatico en costo') . ':</td>
<td><input type="Text" name="margenautcost" size=10 maxlength=20 value="' . $_POST['margenautcost'] . '">'._(' (Valores del 0 al 100)').'</td></tr>';

echo '<tr><td>' . _('Margen de Venta Minimo') . ':</td>
        <td><input type="Text" name="margensales" size=10 maxlength=20 value="' . $_POST['margensales'] . '">'._(' (Valores del 0 al 100)').'</td></tr>';
echo '<tr><td>' . _('Descuento De Remision') . ':</td>
        <td><input type="Text" name="cashdiscount" size=10 maxlength=20 value="' . $_POST['cashdiscount'] . '">'._(' (Valores del 0 al 100)').'</td></tr>';
echo '<tr><td>' . _('Incremento de Costo Por Garantia') . ':</td>
        <td><input type="Text" name="warrantycost" size=10 maxlength=20 value="' . $_POST['warrantycost'] . '">'._(' (Valores del 0 al 100)').'</td></tr>';
echo '<tr><td>' . _('Optimo') . ':</td>
        <td><input type="Text" name="optimo" size=10 maxlength=20 value="' . $_POST['optimo'] . '"></td></tr>';
echo '<tr><td>' . _('Minimo') . ':</td>
        <td><input type="Text" name="minimo" size=10 maxlength=20 value="' . $_POST['minimo'] . '"></td></tr>';
echo '<tr><td>' . _('Maximo') . ':</td>
        <td><input type="Text" name="maximo" size=10 maxlength=20 value="' . $_POST['maximo'] . '"></td></tr>';

echo '</table></td></tr>';

echo '<tr '.$ocultoElemento.'><td>' . _('Tipo Producto') . ':</td>
    <td><select name="StockType" onChange="ReloadForm(CategoryForm.UpdateTypes)" >';
if (isset($_POST['StockType']) and $_POST['StockType']=='F') {
    echo '<option selected value="F">' . _('Productos Terminados');
} else {
    echo '<option value="F">' . _('Productos Terminados');
}
if (isset($_POST['StockType']) and $_POST['StockType']=='M') {
    echo '<option selected value="M">' . _('Materia Prima');
} else {
    echo '<option value="M">' . _('Materia Prima');
}
if (isset($_POST['StockType']) and $_POST['StockType']=='D') {
    echo '<option selected value="D">' . _('Dummy Item - (No Movements)');
} else {
    echo '<option value="D">' . _('Dummy Item - (No Movements)');
}
if (isset($_POST['StockType']) and $_POST['StockType']=='L') {
    echo '<option selected value="L">' . _('Servicios');
} else {
    echo '<option value="L">' . _('Servicios');
}

echo '</select></td></tr>';

echo '<tr '.$ocultoElemento.'><td>' . _('Selecciona la Linea:') . '</td>
<td><select Name="Prodlineid">';
echo '<option VALUE="0"></option>';

$sql = "SELECT DISTINCT ProdGroup.Description, ProdGroup.Prodgroupid FROM ProdGroup 
	INNER JOIN ProdLine USING(Prodgroupid) ORDER BY ProdGroup.Description";
$grupos = DB_query($sql, $db);

while ($grupo = DB_fetch_array($grupos)) {
    echo '<option VALUE="0">&bull; ' . strtoupper($grupo['Description']) . '</option>';
    
    $sql = "SELECT * FROM ProdLine WHERE Prodgroupid = '" . $grupo['Prodgroupid'] . "' order by Description";
    $categoria = DB_query($sql, $db);
    while ($myrowcategoria=DB_fetch_array($categoria, $db)) {
            $categoria_base=$myrowcategoria['Prodlineid'];
        if ($_POST['Prodlineid']==$categoria_base) {
            echo '<option  VALUE="' . $myrowcategoria['Prodlineid'] .  '  " selected>&nbsp;&nbsp;&nbsp;&nbsp;' .ucwords(strtolower($myrowcategoria['Description']));
        } else {
            echo '<option  VALUE="' . $myrowcategoria['Prodlineid'] .  '" >&nbsp;&nbsp;&nbsp;&nbsp;' .ucwords(strtolower($myrowcategoria['Description']));
        }
    }
}
echo '</select>';
if (!isset($Prodlineid)) {
    echo ' <a href="ABCProductLines.php?pagina=Stock">Agregar nueva Linea</a>';
}
echo '</td></tr>';
echo '<tr '.$ocultoElemento.'><td>' . _('Descripcion en Pedidos Venta') . ':</td>
        <td><select name="allowNarrativePOLine">';
if (isset($_POST['allowNarrativePOLine']) and $_POST['allowNarrativePOLine']==1) {
    echo '<option selected value=1>' . _('Permitir Texto Narrativo en Pedido Venta');
} else {
    echo '<option value=1>' . _('Permitir Texto Narrativo en Pedido Venta');
}
if (isset($_POST['allowNarrativePOLine']) and $_POST['allowNarrativePOLine']==0) {
    echo '<option selected value=0>' . _('Sin Texto Narrativo');
} else {
    echo '<option value=0>' . _('Sin Texto Narrativo');
}

echo '</select></td></tr>';

echo '<input type="submit" name="UpdateTypes" style="visibility:hidden;width:1px" value="Not Seen">';
//	if (isset($_POST['StockType']) and $_POST['StockType']=='L') {
//		$Result = $PnLAccountsResult;
//		echo '<tr><td>' . _('Cuenta de Inventarios');//Cuenta de Recuperacion
//	} else {
//		$Result = $BSAccountsResult;
//
//	}
    
echo '<tr '.$ocultoElemento.'><td><b>Seccion 2 </b></td><td><input type="button" value="Mostrar" onclick="mostrar2()">';
echo '<input type="button" value="Ocultar" onclick="ocultar2()"></tr>';

echo "<tr><td colspan='2'><table id='oculto2' style='display:none;'>";
  
    
echo '<tr><td>' . _('Cuenta de Trabajos en Proceso') . ':</td><td><select name="WIPAct">';

while ($myrow = DB_fetch_array($BSAccountsResult)) {
    if (isset($_POST['WIPAct']) and $myrow['accountcode']==$_POST['WIPAct']) {
        echo '<option selected value=';
    } else {
        echo '<option value=';
    }
    echo $myrow['accountcode'] . '>' . $myrow['accountname'];
}
echo '</select></td></tr>';

DB_data_seek($BSAccountsResult, 0);

echo '<tr><td>' . _('Cuenta de Transferencia entre almacenes') . ':</td><td><select name="accounttransfer">';

while ($myrow = DB_fetch_array($BSAccountsResult)) {
    if (isset($_POST['accounttransfer']) and $myrow['accountcode']==$_POST['accounttransfer']) {
        echo '<option selected value=';
    } else {
        echo '<option value=';
    }
    echo $myrow['accountcode'] . '>' . $myrow['accountname'];
}
echo '</select></td></tr>';
DB_data_seek($BSAccountsResult, 0);

echo '<tr><td>' . _('Cuenta de Variaciones de Costo') ;

echo' </td><td style="text-align:center;font-size:1px">';
echo '(Utilizada en Ordenes de Trabajo y
   cuando el precio de la factura de compra es diferente que
           el costo de la recepcion de compra)' . '';
echo '<select name="PurchPriceVarAct">';

while ($myrow = DB_fetch_array($PnLAccountsResult)) {
    if (isset($_POST['PurchPriceVarAct']) and $myrow['accountcode']==$_POST['PurchPriceVarAct']) {
        echo '<option selected value=';
    } else {
        echo '<option value=';
    }
    echo $myrow['accountcode'] . '>' . $myrow['accountname'];
}

DB_data_seek($PnLAccountsResult, 0);

echo '</select></td></tr><tr><td>';
if (isset($_POST['StockType']) and $_POST['StockType']=='L') {
    echo  _('Cuenta de Variaciones de Eficiencia de Mano de Obra');
} else {
    echo  _('Cuenta de Variaciones de Uso Manufactura');
}
echo ':</td><td><select name="MaterialUseageVarAc">';

while ($myrow = DB_fetch_array($PnLAccountsResult)) {
    if (isset($_POST['MaterialUseageVarAc']) and $myrow['accountcode']==$_POST['MaterialUseageVarAc']) {
        echo '<option selected value=';
    } else {
        echo '<option value=';
    }
    echo $myrow['accountcode'] . '>' . $myrow['accountname'];
}
//DB_free_result($PnLAccountsResult);
echo '</select></td></tr>';

// Cuentas de gastos de uso interno
DB_data_seek($PnLAccountsResult, 0);
echo '<tr><td>' ;
echo  _('Cuenta de Facturas de Uso Interno');

echo ':</td><td><select name="UseInternal">';
    DB_data_seek($Result, 0);
while ($myrow = DB_fetch_array($Result)) {
    if (isset($_POST['UseInternal']) and $myrow['accountcode']==$_POST['UseInternal']) {
        echo '<option selected value=';
    } else {
        echo '<option value=';
    }
    echo $myrow['accountcode'] . '>' . $myrow['accountname'];
}

echo '</select></td></tr>';
DB_data_seek($PnLAccountsResult, 0);
// Cuentas de compelemento de costo
echo '<tr><td>' ;
echo  _('Cuenta de Complemento de costo en embarque de compra');

echo ':</td><td><select name="stockshipty">';

while ($myrow = DB_fetch_array($PnLAccountsResult)) {
    if (isset($_POST['stockshipty']) and $myrow['accountcode']==$_POST['stockshipty']) {
        echo '<option selected value=';
    } else {
        echo '<option value=';
    }
    echo $myrow['accountcode'] . '>' . $myrow['accountname'];
}

echo '</select></td></tr>';

DB_data_seek($PnLAccountsResult, 0);

echo '<tr><td>' ;
/*echo  _('Cuenta de Egreso');

echo ':</td><td><select name="accountegreso">';
while ($myrow = DB_fetch_array($BSAccountsResult)) {
    if (isset($_POST['accountegreso']) and $myrow['accountcode']==$_POST['accountegreso']) {
        echo '<option selected value=';
    } else {
        echo '<option value=';
    }
    echo $myrow['accountcode'] . '>' . $myrow['accountname']; // . ' ('.$myrow['accountcode'].')'; desarrollo LO QUITE PUES YA AGREGAMOS A DESCRIPCION EL CODIGO
} //end while loop
DB_free_result($BSAccountsResult);
echo '</select>';*/
echo '</td></tr>';

echo '<tr><td>' . _('Permite Facturacion en rojo') . '</td>';
echo '<td>';
if ($_POST['afecto']!="" and $_POST['afecto']=="1") {
    echo '<input type="checkbox" name="afecto" value="1" checked>';
} else {
    echo '<input type="checkbox" name="afecto" value="0">';
}
echo '</td></tr>';

echo '<tr><td>' . _('Permite Modificar Precios en Venta') . '</td>';
echo '<td>';
if ($_POST['changeprecio']!="" and $_POST['changeprecio']=="1") {
    echo '<input type="checkbox" name="changeprecio" value="1" checked>';
} else {
    echo '<input type="checkbox" name="changeprecio" value="0">';
}
echo '</td></tr>';

echo '</select></td></tr>';
echo '<tr><td>' . _('Flujo:') . '</td>';
echo'<td><select Name="flujo">';
$sql= "SELECT idflujo, flujo FROM prdflujos";
$selectflujo = DB_query($sql, $db);
echo '<option  VALUE="" selected>Ninguno ';
while ($myrowflujo=DB_fetch_array($selectflujo, $db)) {
       $idflujo=$myrowflujo['idflujo'];
    if ($_POST['flujo']==$idflujo) {
        echo '<option  VALUE="' . $myrowflujo['idflujo'] .  '  " selected>' .$myrowflujo['flujo'];
    } else {
        echo '<option  VALUE="' . $myrowflujo['idflujo'] .  '" >' .$myrowflujo['flujo'];
    }
}
echo '</td></tr>';

echo "<td>" . _('Deducible IETU') . ":</td>
	<td><input type='checkbox' name='deductibleflag' " ;
if ($_POST['deductibleflag'] == 1) {
    echo 'checked' ;
}
echo "></td></tr>";
echo '</table></td></tr>';

echo '<tr '.$ocultoElemento.'>
	<td>' . _('Deducciones Autorizadas') . ' :</td>
	<td>';
$SQL = "SELECT *
	FROM accountingtransactiontype
	ORDER BY typeoperation";
    
echo '<select name="u_typeoperation">';
echo "<option selected value='0'>SELECCIONA...</option>";
//echo $SQL;
$result=DB_query($SQL, $db);
while ($myrow=DB_fetch_array($result)) {
    if (isset($_POST['u_typeoperation']) and $_POST['u_typeoperation']==$myrow["u_typeoperation"]) {
        echo '<option selected value=' . $myrow['u_typeoperation'] . '>'. $myrow['typeoperation'] . '</option>';
    } else {
        echo '<option value=' . $myrow['u_typeoperation'] . '>'.$myrow['typeoperation'] . '</option>';
    }
}
echo '</select>
	</td>';
echo '</tr>';

echo '<tr '.$ocultoElemento.'>
	<td>' . _('Tipo de Operaci&oacute;n DIOT') . ' :</td>
	<td>';
                    
if (isset($_GET['SelectedCategory'])) {
    $selcat=$_GET['SelectedCategory'];
} else if (isset($_POST['typeoperationdiot'])) {
    $selcat=$_POST['typeoperationdiot'];
} else {
    $selcat = "";
}
$SQLdiot='SELECT typeoperationdiot
FROM stockcategory
WHERE categoryid="'.$selcat.'"';
$result = DB_query($SQLdiot, $db);
$rowsdiot=  DB_fetch_array($result);
$typeoperdiot=$rowsdiot['typeoperationdiot'];

$SQL = "SELECT *
FROM typeoperationdiot
ORDER BY u_typeoperation";

echo '<select name="typeoperationdiot">';
echo "<option selected value='0'>SELECCIONA...</option>";
//echo $SQL;
$result=DB_query($SQL, $db);
while ($myrow=DB_fetch_array($result)) {
    if ($typeoperdiot==$myrow["u_typeoperation"] or $_POST['typeoperationdiot']==$myrow["u_typeoperation"]) {
        echo '<option selected value=' . $myrow['u_typeoperation'] . '>'. $myrow['typeoperation'] . '</option>';
    } else {
        echo '<option value=' . $myrow['u_typeoperation'] . '>'.$myrow['typeoperation'] . '</option>';
    }
}
echo '</select>
</td>';
echo '</tr>';
/***FIN FCC 09-AGOSTO-2011
SECCION CONFIGURACION PARA IETU**/
$chk = "";
if ($discountInPriceListOnPrice==1) {
    $chk = "checked";
}

echo '<tr '.$ocultoElemento.'><td><b>Seccion 3</b> </td><td><input type="button" value="Mostrar" onclick="mostrar3()">';
echo '<input type="button" value="Ocultar" onclick="ocultar3()"></tr>';

echo "<tr><td colspan='2'><table id='oculto3' style='display:none;'>";



echo '<tr>
<td> '._('Descuento de Lista de Precio sobre el precio').'</td>
<td><input '.$chk.' type="checkbox" name="descLPonPrice"></td>
</tr>';

$chk = "";
if ($discountInComercialOnPrice==1) {
    $chk = "checked";
}

echo '<tr>
		<td> '._('Descuento Comercial sobre el precio').'</td>
		<td><input '.$chk.' type="checkbox" name="descCOMonPrice"></td>
	</tr>';

if ($_POST['generaPublicacionAutomatica'] == 1) {
    $chk = "checked";
} else {
    $chk = "";
}
echo '<tr>';
echo '<td>'._('Generacion Automatica').'</td>';
echo '<td>';

echo'<input type="checkbox" '.$chk.' name="generaPublicacionAutomatica" value="1">';

echo '</tr>';

if ($_POST['showmovil'] == 1) {
    $chk = "checked";
} else {
    $chk = "";
}
echo '<tr>';
echo '<td>'._('Mostrar en Movil').'</td>';
echo '<td>';

echo'<input type="checkbox" '.$chk.' name="showmovil" value="1">';

echo '</tr>';

echo '<td>'._('Fecha Caducidad').': (Prospectos)</td>';
echo '<td>';
echo'<input type="text" name="caducidad" value="'.$_POST["caducidad"].'" size="10">';
echo '</tr>';

echo '<tr>';
echo '<td>'.('# Orden a Desplegar').'</td>';
echo '<td><input type="text" name="ordendesplegar" value="'.$_POST['ordendesplegar'].'"></td>';
echo '</tr>';
echo '<tr>';

echo '<td>'._('Mensaje O.C').'</td>';
echo '<td><textarea name="MensajeOC" rows="4" cols="50">'.$_POST['MensajeOC'].'</textarea>';
echo '</tr>';

echo '<tr>';
echo '<td>'._('Mensaje P.V').'</td>';
echo '<td><textarea name="MensajePV" rows="4" cols="50">'.$_POST['MensajePV'].'</textarea>';
echo '</tr>';

echo '<tr><td>'._('Panel de Control').'</td>';
$PanelSQL = 'SELECT panelcontrolcategoria.CodigoPanelControl,
			panelcontrolcategoria.NombrePanelControl
		FROM panelcontrolcategoria
		WHERE ActivoPanelControl = 1';
$PanelResult = DB_query($PanelSQL, $db);
echo '<td><select name="CodigoPanelControl">';
echo '<option selected value="0">.:Seleccione el panel de control:.</option>';
while ($PanelRow = DB_fetch_array($PanelResult)) {
    if ($_POST['CodigoPanelControl'] == $PanelRow['CodigoPanelControl']) {
        echo '<option selected value="'.$PanelRow['CodigoPanelControl'].'">'.$PanelRow['NombrePanelControl'].'</option>';
    } else {
        echo '<option value="'.$PanelRow['CodigoPanelControl'].'">'.$PanelRow['NombrePanelControl'].'</option>';
    }
}
echo '</select></td></tr>';
echo "<tr>";
echo "<td>"._('Factor esquemado alto')."</td>";
echo "<td><input type=text name=factesquemadoalto value='".$_POST['factesquemadoalto']."'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>"._('Factor esquemado ancho')."</td>";
echo "<td><input type=text name=factesquemadoancho value='".$_POST['factesquemadoancho']."'></td>";
echo "</tr>";
echo '</table></td></tr>';

if (empty($SelectedCategory)) {
    $SelectedCategory = "";
}
$SQL = 'SELECT salestypes.sales_type, salestypes.typeabbrev,salespricesbycategory.percent*100 as percent,salespricesbycategory.typeabbrev as valida
		FROM salestypes left join salespricesbycategory
		ON salespricesbycategory.typeabbrev=salestypes.typeabbrev
		AND salespricesbycategory.categoryid="'.$SelectedCategory.'"
		order by  sales_type asc ';
//echo $SQL;
$PricesResult = DB_query($SQL, $db);

echo '<tr '.$ocultoElemento.'>
                        <td colspan=2 style=text-align:center;>
					<b>' . _('Listas de precios Aumento Porcentual (positivo) o decremento (negativo) de 0 a 100') .'</b>';
echo '<table border=1 align=center>';
echo '<tr align=center style="background-color:#f2fcbd;text-align:center;">';
while ($PriceLists=DB_fetch_array($PricesResult)) {
    echo "<td style='text-align:center;' title='".$PriceLists['sales_type']."'>";
    if (is_null($PriceLists['valida'])) {
        echo "<input type='checkbox' name='lista_" . $PriceLists['typeabbrev'] . "' value='1' >";
    } else {
        echo "<input type='checkbox' name='lista_" . $PriceLists['typeabbrev'] . "' value='1' checked>";
    }
    echo $PriceLists['typeabbrev'];
    echo "</td>";
}
DB_data_seek($PricesResult, 0);
echo '</tr>';
echo '<tr>';
while ($PriceLists=DB_fetch_array($PricesResult)) {
    if (!isset($_POST[$PriceLists['typeabbrev']])) {
        $_POST[$PriceLists['typeabbrev']]=0;
    }
    $_POST[$PriceLists['typeabbrev']]=$PriceLists['percent'];
    echo "<td><input type=text name='".$PriceLists['typeabbrev']."' class=number size=4 maxlength=4 VALUE=" . number_format($_POST[$PriceLists['typeabbrev']], 2) . "></td>";
}
DB_data_seek($PricesResult, 0);
echo '</tr>';


echo '</table>';
echo '</td></tr>';
echo '</table>';
    
echo '<div class="centre">';
echo '<component-button type="submit" id="btnRegresar" name="btnRegresar" value="Regresar" class="glyphicon glyphicon-share-alt" onclick="window.open(\'StockCategoriesV2.php\', \'_self\'); return false;"></component-button>';
echo '<component-button type="submit" id="btnProcesar" name="btnProcesar" value="Procesa Información" class="glyphicon glyphicon-floppy-disk" onclick="if (fnValidaDatos()) { return true; } else { return false; }"></component-button>';
echo '</div>';
echo '</form>';
//}

include 'includes/footer_Index.inc';
?>

<script type="text/javascript">

/**
 * Función para validar la información de captura
 * @return {[type]} [description]
 */
function fnValidaDatos() {
    var mensaje= "";
    var notifica= false;

    if ($("#CategoryID").val() == "" || $("#CategoryID").val() == "0") {
        mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Debe capturar código de Partida Genérica para poder continuar.</p>'; 

        notifica= true;
    }

    if ($("#CategoryDescription").val() == "" || $("#CategoryDescription").val() == "0") {
        mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Debe capturar descripción de Partida Genérica para poder continuar.</p>'; 
        notifica= true;
    }

    if ($("#StockAct").val() == $("#accountegreso").val()) {
        mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Las cuentas de cargo y abono no pueden ser iguales.</p>'; 

        notifica= true;
    }

    if (notifica) {
        muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', mensaje);    

        return false;
    } else {
        return true;
    }
}

/**
 * Función para obtener la descripción de la Partida Genérica
 * @param  {[type]} elemento Recibe la caja de texto
 * @return {[type]}          [description]
 */
function fnTraeInformacionPartida(elemento) {
    // Obtener la descripcion de la partida generica
    if (elemento.value.length == 3) {
        // Traer descripción de la partida generica
        var descripcionPartida = fnInformacionPartidaGenericaGeneral(elemento.value);
        $("#CategoryDescription").val(""+descripcionPartida);
        $("#CategoryDescriptionVisual").val(""+descripcionPartida);

        if (elemento.value.substring(0, 1) == 2) {
            // Si es capitulo 2 habilitar salida de almacén
            $('#AdjGLAct').multiselect('enable');
            $('#ln_abono_salida').multiselect('enable');
        } else {
            // Deshabilitar almacén
            fnDehabilitarAlmacen();
        }
    } else {
        // Si no es partida generica
        $("#CategoryDescription").val("");
        $("#CategoryDescriptionVisual").val("");
        // Deshabilitar almacén
        fnDehabilitarAlmacen();
    }
}

/**
 * Función para deshabulitar selección del almacén
 * cuando no se elige capitulo 2
 * @return {[type]} [description]
 */
function fnDehabilitarAlmacen() {
    // Funcion para dehabilitar la seleccion de almacen
    $('#AdjGLAct').val('-1');
    $("#AdjGLAct").multiselect('rebuild');
    $('#AdjGLAct').multiselect('disable');

    $('#ln_abono_salida').val('-1');
    $("#ln_abono_salida").multiselect('rebuild');
    $('#ln_abono_salida').multiselect('disable');
}

// Aplicar formato del SELECT
fnFormatoSelectGeneral(".StockAct");
fnFormatoSelectGeneral(".AdjGLAct");
fnFormatoSelectGeneral(".selectTipoGastoLocal");
fnFormatoSelectGeneral(".lineaDesc");
fnFormatoSelectGeneral(".ln_abono_salida");
function mostrar1(){
document.getElementById('oculto').style.display = 'block';}
function ocultar1(){
document.getElementById('oculto').style.display = 'none';}
function mostrar2(){
document.getElementById('oculto2').style.display = 'block';}
function ocultar2(){
document.getElementById('oculto2').style.display = 'none';}
function mostrar3(){
document.getElementById('oculto3').style.display = 'block';}
function ocultar3(){
document.getElementById('oculto3').style.display = 'none';}
// Deshabilitar descripción
$("#CategoryDescriptionVisual").prop("disabled", true);
</script>