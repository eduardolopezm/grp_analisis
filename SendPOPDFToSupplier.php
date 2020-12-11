<?php
/**
 * Enviar Datos Proveedor
 *
 * @category Proceso
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 10/10/2017
 * Fecha Modificación: 10/10/2017
 * Enviar la Orden de Compra al Proveedor
 */

include "includes/SecurityUrl.php";
include('includes/SQL_CommonFunctions.inc');
include('includes/session.inc');
include('includes/header.inc');
$funcion=29;
include('includes/SecurityFunctions.inc');
include('Numbers/Words.php');
include('includes/XSAInvoicing.inc');

if (!isset($_GET['OrderNo'])) {
    prnMsg('Es necesario definir el numero de orden de compra (por GET) para poder enviar email al proveedor ');
    include('includes/footer_Index.inc');
    exit;
}

$OrderNoCompra = $_GET['OrderNo'];
$emailProveedor = 1;
include('includes/SendEmailCompra.inc');
include('includes/footer_Index.inc');
