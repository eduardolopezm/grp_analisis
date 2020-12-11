<?php

define('FPDF_FONTPATH', './fonts/');
include ('fpdf.php');

//ini_set('display_errors', 1);
/*ini_set('log_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');*/

class VariableStream
{
    private $varname;
    private $position;

    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->varname = $url['host'];
        if(!isset($GLOBALS[$this->varname]))
        {
            trigger_error('Global variable '.$this->varname.' does not exist', E_USER_WARNING);
            return false;
        }
        $this->position = 0;
        return true;
    }

    function stream_read($count)
    {
        $ret = substr($GLOBALS[$this->varname], $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    function stream_eof()
    {
        return $this->position >= strlen($GLOBALS[$this->varname]);
    }

    function stream_tell()
    {
        return $this->position;
    }

    function stream_seek($offset, $whence)
    {
        if($whence==SEEK_SET)
        {
            $this->position = $offset;
            return true;
        }
        return false;
    }

    function stream_stat()
    {
        return array();
    }
}

class pdfpurchorder extends FPDF
{
    // Atributo de clase agregado, ya que en el header se saca esta informacion y la requiero en el llamado del metodo export
    var $leyendaPiePagina = '';
    var $paginaBarcode = 0;

    function __construct($orientation='P', $unit='mm', $format='A4')
    {
        parent::__construct($orientation, $unit, $format);
        // Register var stream protocol
        stream_wrapper_register('var', 'VariableStream');
    }

    function MemImage($data, $x=null, $y=null, $w=0, $h=0, $link='')
    {
        // Display the image contained in $data
        $v = 'img'.md5($data);
        $GLOBALS[$v] = $data;
        $a = getimagesize('var://'.$v);
        if(!$a)
            $this->Error('Invalid image data');
        $type = substr(strstr($a['mime'],'/'),1);
        $this->Image('var://'.$v, $x, $y, $w, $h, $type, $link);
        unset($GLOBALS[$v]);
    }

    function GDImage($im, $x=null, $y=null, $w=0, $h=0, $link='')
    {
        // Display the GD image associated with $im
        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        $this->MemImage($data, $x, $y, $w, $h, $link);
    }

    function printPDF($archivo = "") {
        global $db;
        
        global $arrubicacion;

        $arrubicacion = array("Footer", "Footer1", "Footer2", "Footer3", "Footer4");

        $this->FPDF('P', 'mm', 'Letter');//checar lo del constructor nuevo de php 7 no funciona con function __construct
        $this->SetAutoPageBreak(true, 10);

        $this->AddPage();

        $this->SetFont('helvetica', '', 7);

        $sql = "SELECT itemcode,
                CASE purchorderdetails.deliverydate
                WHEN '0000-00-00' THEN purchorders.orddate
                ELSE purchorderdetails.deliverydate
                END AS deliverydate,
                itemdescription,
                unitprice,
                units,
                decimalplaces,
                purchorderdetails.discountpercent1,
                purchorderdetails.discountpercent2,
                purchorderdetails.discountpercent3,
                ifnull(taxauthrates.taxrate,0) as taxrate,
                case when purchorderdetails.narrative='' then justification else  purchorderdetails.narrative end  as narrative,
                justification,
                purchorders.currcode,
                barcode,stockautor,stockmanufacturer.manufacturer,
                purchorders.supplierno,
                suppliers.taxgroupid,
                stockcategory.cattipodescripcion,
                stockcategory.MensajeOC,
                stockmaster.longdescription as DescipcionProd,
                taxgroups.taxrate AS taxgrouprate,
                stockmaster.taxcatid,
                stockmaster.barcode as stockbarcode,
                stockcategory.MensajeOC,
                purchorders.wo,
                sum(quantityord) as quantityord
                FROM purchorderdetails
                LEFT JOIN stockmaster ON purchorderdetails.itemcode=stockmaster.stockid
                LEFT JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
                LEFT JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno
                INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
                LEFT JOIN stockmanufacturer ON stockmanufacturer.manufacturerid=stockmaster.manufacturer
                LEFT JOIN taxauthrates ON stockmaster.taxcatid=taxauthrates.taxcatid
                AND taxauthrates.dispatchtaxprovince=suppliers.taxgroupid
                LEFT JOIN taxgroups ON suppliers.taxgroupid = taxgroups.taxgroupid
                WHERE purchorderdetails.orderno = '" . $_GET['OrderNo']."' 
                GROUP BY itemcode,
                CASE purchorderdetails.deliverydate WHEN '0000-00-00' THEN purchorders.orddate ELSE purchorderdetails.deliverydate END,
                itemdescription,
                unitprice,
                units,                    
                decimalplaces,
                purchorderdetails.discountpercent1,
                purchorderdetails.discountpercent2,
                purchorderdetails.discountpercent3,
                ifnull(taxauthrates.taxrate,0),
                purchorderdetails.narrative,
                justification,
                purchorders.currcode,
                barcode,stockautor,stockmanufacturer.manufacturer,
                purchorders.supplierno,
                suppliers.taxgroupid,
                stockcategory.cattipodescripcion,
                stockcategory.MensajeOC,
                stockmaster.longdescription,
                taxgroups.taxrate,
                stockmaster.taxcatid,
                stockmaster.barcode,
                stockcategory.MensajeOC,
                purchorders.wo";

        $result = DB_query($sql, $db);
        $taxtotal = 0;
        $subtotal = 0;
        $detalleObrasSalto = 0;
        $barcodeTrae = 0;

        while ($rows = DB_fetch_array($result))
        {
            $currcode = $rows['currcode'];
            $MensajeOC = $rows['MensajeOC'];
            $d1 = $rows['discountpercent1'];
            $d2 = $rows['discountpercent2'];
            $d3 = $rows['discountpercent3'];
            $taxgroupid = $rows['taxgroupid'];
            $taxcatid = $rows['taxcatid'];
            $taxrate = $rows['taxrate'];
            $cadena_activos= "";

            if ($taxcatid == null) {
                 // si viene nulo ir sobre el rate del proveedor
                $taxrate = $rows['taxgrouprate'];
            }
            
            $totalline = ($rows['unitprice'] * $rows['quantityord']) * (1 - ($d1 / 100)) * (1 - ($d2 / 100)) * (1 - ($d3 / 100));
            $subtotal+= $totalline;

            $LineTax = ($rows['unitprice'] * $rows['quantityord']) * (1 - ($d1 / 100)) * (1 - ($d2 / 100)) * (1 - ($d3 / 100)) * $taxrate;
            $taxtotal+= $LineTax;

            if ($rows['cattipodescripcion'] == 1) {
                $rows['itemdescription'] = $rows['DescipcionProd'] . chr(13) . chr(10) . $rows['narrative'] . chr(13) . chr(10);
            }
            else {
                $rows['itemdescription'] = $rows['itemdescription'] . chr(13) . chr(10) . $rows['narrative'] . chr(13) . chr(10);
            }

            $this->cell(15, 5, $rows['quantityord'], 'L', 0, 'L');

            $instruccion= "SHOW TABLES LIKE 'fixedassets%'";
            $resultado= DB_query($instruccion, $db);

            if (DB_num_rows($resultado) != 0){
                // consultar activos agregados en la OC (si aplica)
                 $consulta= "SELECT fixedassets.description, fixedassets.serialno
                            FROM fixedassetsservices
                            INNER JOIN fixedassets USING (assetid)
                            WHERE orderno='".$_GET['OrderNo']."' AND stockidpo= '".$rows['itemcode']."'";

                $resultado= DB_query($consulta, $db);

                while ($registro= DB_fetch_array($resultado)){
                    $cadena_activos.= $registro["description"]." [".$registro["serialno"]."], ";
                }

                if (!empty($cadena_activos)){
                    $cadena_activos= trim(str_replace('\r\n', chr(13) . chr(10), trim($cadena_activos)));
                    $cadena_activos= substr($cadena_activos, 0, strlen($cadena_activos)-1);
                }
            }

            //$this->cell(25,3,$cod,0,0,'L');
            //$this->cell(40, 24, $rows['itemcode'], 1, 0, 'L'); //Contorno de la info
            $this->cell(40, 5, $rows['itemcode'], 0, 0, 'L');

            $actX = $this->GetX();
            $actY = $this->GetY();

            $rows['itemdescription'] = trim(str_replace('\r\n', chr(13) . chr(10), trim($rows['itemdescription'])));
            $MensajeOC = trim(str_replace('\r\n', chr(13) . chr(10), trim($MensajeOC)));
            $this->SetFont('helvetica', '', 6);
            $descripcionnorma = $rows['itemdescription'].chr(13).chr(10).$cadena_activos;
            $this->MultiCell(70, 3, html_entity_decode(htmlspecialchars_decode($descripcionnorma)), 0, 'L');

            $desp = $this->GetY();
            $posYMultiCell = $this->GetY();

            if ($actY < $posYMultiCell) {
                $y = $actY + 3;
                while ($y < $posYMultiCell) {
                    $this->SetY($y);
                    $this->cell(195, 5, '', 'LR', 1, 'L');
                    $y+= 3;
                }
            }
            $this->SetXY($actX + 70, $actY);
             // para regresar a la posicion sigte
            //$this->MultiCell(100,4,$comments,0,'L',1);
            //valida si el proveedor tiene unidad de medida configurado
            $sqldetail = "SELECT *
                            FROM purchdata
                            WHERE stockid = '" . $rows['itemcode'] . "'
                              AND supplierno='" . $rows['supplierno'] . "'";
            $resultdetail = DB_query($sqldetail, $db);
            if (DB_num_rows($resultdetail) > 0) {
                $rowdetail = DB_fetch_array($resultdetail);
                if (strlen(trim($rowdetail['suppliersuom'])) > 0) {
                    $rows['units'] = $rowdetail['suppliersuom'];
                }
            }

            $this->SetFont('helvetica', '', 7);
            $this->cell(20, 5, ($rows['units']), 0, 0, 'L');

            $this->cell(20, 5, number_format($rows['unitprice'], 2), 0, 0, 'R');

            if (Havepermission($_SESSION["UserID"], 1939, $db) != 1){
                $this->cell(10, 5, number_format($d1, 0) . " %", 0, 0, 'R');
            } else {
                $this->cell(10, 5, " ", 0, 0, 'R');
            }

            $this->cell(20, 5, number_format($totalline, 2), 'R', 1, 'R');

            if (preg_match("/gruposervillantas/", $_SESSION['DatabaseName'])) {
                //Fila codigo de barra ------------
                if (!empty($rows['stockbarcode'])) {
                    $barcodeTrae = 1;

                    $yFilaBarcode = 7;
                    $ybarcode = 29;

                    $this->cell(195, 18, '', 'LR', 0, 'L');

                    require_once('code128barcode.class.php');

                    //$code = '801678139180'; //Codigo para hacer pruebas de correcto

                    $barcode = new code128barcode();

                    $code = $barcode->output(trim($rows['stockbarcode']));

                    if ($code) {
                        $height = intval(strlen($code) / 3);
                        $width = strlen($code);
                        $im = imagecreate($width,intval(($height * 1.33) + 6));
                        $white = ImageColorAllocate ($im, 255, 255, 255);
                        $black = ImageColorAllocate ($im, 0,0,0);

                        $code = preg_split('##',$code);
                        array_pop($code);
                        array_shift($code);
                        for($i = 0 ; array_key_exists($i,$code) ; $i++)
                            if ($code[$i] == 0)
                                imageline($im,$i,0,$i,$height,$white);
                            else
                                imageline($im,$i,0,$i,$height,$black);

                        /*$font=ImagePsLoadFont("css/URWGothicL-Demi.pfb");
                        imagepsencodefont ($font,'css/latin1.enc');
                        $size = ImagePSBbox($string,$font,($height/3));
                        $h = ($width - $size[2]) / 2;
                        $v = ($height + 18);
                        ImagePsText($im, $string,$font, $height/3, $black, $white, $h, $v);*/

                        //header ("Content-type: image/png");
                        //header ("Content-disposition: inline; filename=barcode.png");
                        //imagejpeg ($im);
                        $posy_Barcode = $this->GetY();

                        $this->GDImage($im, 25, $posy_Barcode, 40);

                        imagedestroy($im);
                    }

                    $this->Ln(12); //Espacio imagen codigo de barras

                    $this->cell(15, $yFilaBarcode, '', 'LB', 0, 'L');
                    $this->cell(40, $yFilaBarcode, $rows['stockbarcode'], 'B', 0, 'C');
                    $this->cell(140, $yFilaBarcode, '', 'BR', 0, 'C');

                    $this->Ln(8);
                }elseif ($barcodeTrae == 1) {
                    $this->cell(195, 5, '', 'LBR', 0, 'L');
                    $this->Ln(7);
                }
                //-----------------
            }

            //$this->cell(30,3,number_format($stockprecio,2),0,0,'R');
            //$this->Ln(6);
            if ($desp > $actY) {
                $despln = $desp - $actY;
                $this->Ln($despln - 4);
            }
        }

        if ($barcodeTrae == 1) {
            //Espacio para imagen barcode, ultima partida
            $posYMultiCell = $posYMultiCell + 14;
            $posYMultiCell = $posYMultiCell - 3;
            $this->SetY($posYMultiCell);
            $this->cell(195, 5, '', 0, 1, 'C');
        } else if($detalleObrasSalto > 0) {
            $this->Ln($detalleObrasSalto);
            $posYMultiCell = $posYMultiCell + 5;
            $this->SetY($posYMultiCell);
        } else {
            $posYMultiCell = $posYMultiCell - 3;
            $this->SetY($posYMultiCell);
            $this->cell(195, 5, '', 'LRB', 1, 'C');
        }

        //
        //$this->Ln(4);
        $posY = $this->GetY();

        if ($barcodeTrae == 1) {
            //Espacio desplazar el subtotal
            $this->Ln(15);
        }

        //$this->SetX(30);
        //$this->cell(150,6,'Norma '.$MensajeOC,0,0,'R');
        $this->cell(180, 6, 'Subtotal', 0, 0, 'R');
        $this->cell('', 6, number_format($subtotal, 2), 0, 1, 'R');
        if ($taxgroupid == 2) {
        }
        else {
            // $this->cell(180, 6, 'IVA', 0, 0, 'R');
            // $this->cell('', 6, number_format($taxtotal, 2), 0, 1, 'R');
        }
        $this->SetFont('helvetica', 'B', 8);
        $this->SetFillColor(205, 205, 205);
        $sqldetail = "SELECT sum(quantityord) as total
                            FROM purchorderdetails
                            WHERE purchorderdetails.orderno = '" . $_GET['OrderNo'] . "'";
        $resultdetail = DB_query($sqldetail, $db);
        $rowdetail = DB_fetch_array($resultdetail);
        $this->cell(35, 5, 'Productos Solicitados', 0, 0, 'R', 1);
        $this->SetFillColor(254, 154, 46);
        $this->cell(20, 5, number_format($rowdetail['total'], 2), 0, 1, 'R', 1);
        $this->SetFillColor(254, 154, 46);
        $this->SetX($this->GetX() + 140);
        $this->cell(35, 5, 'Total', 0, 0, 'R', 1);
        $this->SetFillColor(254, 154, 46);
        //$this->cell(20, 5, number_format($subtotal + $taxtotal, 2), 0, 1, 'R', 1);
        $this->cell(20, 5, number_format($subtotal, 2), 0, 1, 'R', 1);

        //$montoctvs1 = round($subtotal + $taxtotal, 2);
        $montoctvs1 = round($subtotal, 2);
        $separa = explode(".", $montoctvs1);
        $montoctvs2 = $separa[1];

        if ($montoctvs2 == "") $montoctvs2 = "00";

        if (left($montoctvs2, 3) > 956) {
            $montoctvs2 = 0;
        }
        if (strlen($montoctvs2) == 1) {
            $montoctvs2.= "0";
        }

        if ($currcode == "MXN") {
            $montoletra = Numbers_Words::toWords($separa[0], 'es');
            $montocentavos = Numbers_Words::toWords($montoctvs2, 'es');
            $montoletra = ucwords($montoletra) . " Pesos " . $montoctvs2 . " /100 M.N.";
        }
        else {
            $qry = "SELECT currency
                    FROM currencies
                    WHERE currabrev = '$currcode'";
            $rmon = DB_query($qry, $db);
            $row = DB_fetch_array($rmon);
            $nombremoneda = " " . $row[0] . " ";

            $montoletra = Numbers_Words::toWords($separa[0], 'en_US');
            $montocentavos = Numbers_Words::toWords($montoctvs2, 'en_US');
            $montoletra = ucwords($montoletra) . $nombremoneda . $montoctvs2 . "/100 " . $currcode;
        }

        $this->SetFont('helvetica', '', 7);
        $this->cell('', 4, $montoletra, 0, 1, 'R');
        $lastY = $this->GetY();

        $this->Ln();
        $this->SetY($posY);
        $leyenda = $this->leyendaPiePagina;
        $this->MultiCell(100, 4, $leyenda, 0, 'L');

        //comments
        $this->SetY($lastY);
        $this->Ln(2);
        $sql = "SELECT comments
                FROM purchorders
                WHERE orderno = " . $_GET['OrderNo'];
        $res = DB_query($sql, $db);
        $reg = DB_fetch_array($res);
        if ($reg[0] != "") {
            $this->SetFillColor(205, 205, 205);
            $this->SetFont('helvetica', 'B', 8);
            $comments = "Comentarios: " . chr(13) . chr(10) . $reg[0];
            $this->MultiCell(100, 4, $comments, 0, 'L', 1);
            $this->SetFont('helvetica', '', 8);
        }

        $arrubicacion = array("Footer", "Footer1", "Footer2", "Footer3", "Footer4");
        $tope = 245;

        //foreach ($arrubicacion as $ubicacion){

        foreach ($arrubicacion as $ubicacion) {
            $sqlfooter = "SELECT *
                            FROM  PDFTemplates
                            WHERE tipodocto='" . $_GET['tipodocto'] . "'
                            AND (legalid = '" . $_GET['legalid'] . "' or legalid = 0)
                            AND (tagref = '" . $_GET['Tagref'] . "' or tagref = 0)
                            AND Ubicacion='$ubicacion'
                            AND Orden > 0
                            ORDER BY Orden";

            //echo '<pre>sql:'.$sqlfooter;
            $resulttextf = DB_query($sqlfooter, $db, $ErrMsg);
            $hayregistros = false;

            if (DB_num_rows($resulttextf) > 0) {
                $hayregistros = true;
            }

            while ($myrowfooter = DB_fetch_array($resulttextf)) {
                $fill = $myrowfooter['filled'];
                if ($this->GetY() >= $tope) {
                    $this->AddPage();
                }

                if (strlen($myrowfooter['Titulo']) > 0) {
                    $this->SetFont('helvetica', 'B', 9);
                    $posY = $this->GetY();
                    $wx = $this->GetStringWidth($myrowfooter['Titulo']);
                    $this->MultiCell('', 4, $myrowfooter['Titulo'], 0, 'L', $fil);
                    if ($myrowfooter['consulta'] == '' && $myrowfooter['Texto'] == '');
                     //$this->Ln();
                    else {
                        $this->SetXY($this->GetX() + $wx + 2, $posY);
                    }
                }

                $txt = $myrowfooter['Texto'];
                if ($txt <> "") {
                    if ($myrowfooter['bold'] == 1) $this->SetFont('helvetica', 'B', 8);
                    else $this->SetFont('helvetica', '', 8);

                    if (strlen($myrowfooter['consulta']) > 0) {
                        $SQL = $myrowfooter['consulta'] . $_GET['OrderNo'];
                       // echo $SQL;
                        $resultcliente = DB_query($SQL, $db, $ErrMsg);
                        $myrowcliente = DB_fetch_array($resultcliente);
                        $txt.= " " . $myrowcliente[0];
                    }
                    if (strlen($txt) > 0) {
                        if ($fill == 1) $this->SetTextColor(255, 255, 255);

                        $this->MultiCell('', 4, $txt, 0, 'J', $fill);
                        $this->SetTextColor(0, 0, 0);

                        //$this->Ln();

                    }
                }
                else {

                    //echo 'entra';
                    $PosY = $this->GetY();
                    if (strlen($myrowfooter['consulta']) > 0) {
                        $SQL = $myrowfooter['consulta'] . $_GET['OrderNo'];

                        //echo '<PRE>'.$SQL;
                        $resultimagen = DB_query($SQL, $db);
                        $imagen = DB_fetch_array($resultimagen);
                        if ($imagen['ImagenUsuario'] <> "") {
                            $this->Image($imagen['ImagenUsuario'], 10, $PosY, trim($_SESSION['AnchoBannerUser']), trim($_SESSION['AltoBannerUser']));

                            $posY = $this->GetY() + 20;
                            $this->SetY($posY);
                            $this->Ln(6);
                        }
                    }
                }
            }

            if ($hayregistros) $this->Ln(6);
        }

        if ($archivo == "") {
            $this->Output('', 'I');
        }
        else {
            $this->Output($archivo, 'F');
        }
    }

    function Footer() {
        global $db;
        $this->SetFillColor(205, 205, 205);
        $this->Ln(4);
        $this->SetFont('helvetica', '', 5);
        $sqlleyen = "SELECT distinct stockcategory.MensajeOC
                         FROM purchorderdetails
                            LEFT JOIN stockmaster ON purchorderdetails.itemcode=stockmaster.stockid
                            LEFT JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
                            WHERE purchorderdetails.orderno = '" . $_GET['OrderNo'] . "'";
        $resultleyen = DB_query($sqlleyen, $db);
        while ($myrowleyen = DB_fetch_array($resultleyen)) {
            $leyenda = $leyenda . $myrowleyen['MensajeOC'] . chr(13) . chr(10);
        }
        $this->MultiCell(200, 4, $leyenda, 0, 'L');
        $this->Ln(4);
        $this->SetFont('helvetica', 'B', 10);
        $this->cell(25, 6, 'Elaboro', 0, 0, 'L');



        $sqluser = "SELECT www_users.realname
                    FROM purchorders
                    INNER JOIN www_users ON purchorders.initiator = www_users.userid
                    WHERE purchorders.orderno = '" . $_GET['OrderNo'] . "'";

        $resultuser = DB_query($sqluser, $db);
        while ($myrowuser = DB_fetch_array($resultuser)) {
            $user = $myrowuser['realname'];
        }
        $sqluserauto = "SELECT www_users.realname
                            FROM logpurchorderstatus
                            INNER JOIN www_users ON www_users.userid = logpurchorderstatus.userid
                            WHERE logpurchorderstatus.orderno = '" . $_GET['OrderNo'] . "'
                            AND logpurchorderstatus.status = 'Authorised'";
        $resultuserauto = DB_query($sqluserauto, $db);
        while ($myrowuserauto = DB_fetch_array($resultuserauto)) {
            $userauto = $myrowuserauto['realname'];
        }

        // consultar quien da el visto bueno
        $sqlvistobueno = "SELECT www_users.realname
                        FROM logpurchorderstatus
                        INNER JOIN www_users ON www_users.userid = logpurchorderstatus.userid
                        WHERE logpurchorderstatus.orderno = '" . $_GET['OrderNo'] . "'
                        AND logpurchorderstatus.status = 'Approbal'";

        $resultadovobo = DB_query($sqlvistobueno, $db);
        $uservobo= "";

        while ($myrowuservobo = DB_fetch_array($resultadovobo)) {
            $uservobo = $myrowuservobo['realname'];
            $this->SetFont('helvetica', 'B', 10);
            $this->cell(50, 6, 'VoBo       ', 0, 0, 'R');
            break;
        }

        $sqluser2 = "SELECT www_users.realname
                        FROM purchorders
                            INNER JOIN www_users ON purchorders.autorizausuario = www_users.userid
                        WHERE purchorders.orderno = '" . $_GET['OrderNo'] . "'";

        $resultuser2 = DB_query($sqluser2, $db);

        while ($myrowuser2 = DB_fetch_array($resultuser2)) {
            $userauto2 = $myrowuser2['realname'];
        }

        if (DB_num_rows($resultuserauto) > 0) {
            $this->cell(90, 6, 'Autorizo', 0, 1, 'R');
        }
        elseif (DB_num_rows($resultuser2) > 0) {
            $this->cell(90, 6, 'Autorizo', 0, 1, 'R');
        }

        $this->SetFillColor(205, 205, 205);
        $this->SetFont('helvetica', 'B', 10);
        $this->Ln(4);
        $this->cell(25, 6, $user, 0, 0, 'L');

        if (!empty($uservobo)){
            $this->cell(50, 6, $uservobo, 0, 0, 'R');
        }

        if (DB_num_rows($resultuserauto) > 0) {
            $this->cell(110, 6, $userauto, 0, 1, 'R');
        }
        elseif (DB_num_rows($resultuser2) > 0) {

            $this->cell(110, 6, $userauto2, 0, 1, 'R');
             //

        }
        $this->Ln(8);
        if (preg_match("/erpmg/", $_SESSION['DatabaseName'])) {
            //Mensaje solo para MG
            $this->SetTextColor(193, 0, 0);
            $this->SetFont('helvetica', 'B', 9);
            $this->cell(25, 6, 'PROVEEDOR, FAVOR DE REALIZAR LA ENTREGAR DE LOS MATERIALES CON UNA COPIA DE ESTE DOCUMENTO;', 0, 0, 'L');
            $this->Ln(5);
            $this->cell(25, 6, 'CON LA FINALIDAD DE FACILITAR LA RECEPCIÓN DEL MISMO. ES UN REQUISITO INDISPENSABLE.', 0, 0, 'L');
        }
    }
    function Header() {
        global $db;

        //$funcion=595;
        //include_once('SecurityFunctions.inc');

        //Ininicio de barcode
        $this->paginaBarcode = $this->paginaBarcode + 1;

        $sql = "SELECT terms,
                    shippers.shippername,
                    suppliers.*,
                    purchorders.*
                 FROM purchorders
                 INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
                 INNER JOIN paymentterms ON paymentterms.termsindicator = suppliers.paymentterms
                 LEFT JOIN shippers ON purchorders.deliveryby = shippers.shipper_id
                 WHERE orderno=" . $_GET['OrderNo'];

        $res = DB_query($sql, $db);
        $reg = DB_fetch_array($res);
        $paymentterm = $reg[0];
        $suppname = $reg['suppname'];
        $address1 = $reg['address1'];
        $address2 = $reg['address2'];
        $address3 = $reg['address3'];
        $address4 = $reg['address4'];
        $address5 = $reg['address5'];
        $address6 = $reg['address6'];
        $fecha = $reg['orddate'];
        $shipname = $reg['shippername'];

        if (!empty($reg['nombre_comercial'])){
            $suppname= $reg['nombre_comercial'];
        }

        $supplierdata = $suppname . chr(13) . chr(10) . $address1 . " " . $address2 . chr(13) . chr(10) . $address3 . chr(13) . chr(10) . $address4 . chr(13) . chr(10) . $address5 . chr(13) . chr(10) . $address6 . chr(13) . chr(10) . " " . chr(13) . chr(10) . " ";

        if (strlen($reg['noag_ad']) == 0 or $reg['noag_ad'] == 0) {
            $deliverydata = $reg['deladd6'] . chr(13) . chr(10) . $reg['deladd1'] . chr(13) . chr(10) . $reg['deladd2'] . chr(13) . chr(10) . $reg['deladd3'] . " " . $reg['deladd4'] . chr(13) . chr(10) . $reg['deladd5'] . chr(13) . chr(10) . "Contacto " . $reg['contact'] . chr(13) . chr(10) . "Telefono " . $reg['telephoneContact'];
        }
        else {
            $SqlSupplier = "SELECT suppliers.address1,
                                   suppliers.address2,
                                   suppliers.address3,
                                   suppliers.address4,
                                   suppliers.address5,
                                   suppliers.address6
                            FROM suppliers
                            WHERE suppliers.supplierid = '" . $reg['noag_ad'] . "'";
            $result = DB_query($SqlSupplier, $db);
            while ($myrow = DB_fetch_array($result)) {
                $supaddress1 = $myrow['address1'];
                $supaddress2 = $myrow['address2'];
                $supaddress3 = $myrow['address3'];
                $supaddress4 = $myrow['address4'];
                $supaddress5 = $myrow['address5'];
                $supaddress6 = $myrow['address6'];
            }
            $deliverydata = $supaddress1 . " " . $supaddress2 . " " . $supaddress3 . chr(13) . chr(10) . $supaddress4 . chr(13) . chr(10) . "C.P. " . $supaddress5 . chr(13) . chr(10) . "Phone " . $supaddress6;
        }

        $sql = "SELECT tags.tagdescription as nombre,
                            tags.address1 as direccion,
                            tags.address2 as numero,
                            tags.address3 as colonia,
                            tags.address4 as municipio,
                            tags.address5 as estado,
                            tags.cp as codigo,
                            tags.tagref
                    FROM tags
                    WHERE tags.tagref = '" . $_GET['Tagref'] . "'";
        $resulttag = DB_query($sql, $db);
        $rowtag = DB_fetch_array($resulttag);

        $sql = "SELECT l.loccode,
                       l.locationname,
                       l.email AS contact
                FROM purchorders p
                INNER JOIN locations l ON p.intostocklocation= l.loccode
                AND p.tagref= l.tagref
                WHERE orderno='" . $_GET['OrderNo'] . "'";

        $resultado = DB_query($sql, $db);
        $renglonalm = DB_fetch_array($resultado);

        $tagdata = $rowtag['nombre'] . chr(13) . chr(10) . $rowtag['direccion'] . " " . $rowtag['numero'] . chr(13) . chr(10) . $rowtag['colonia'] . chr(13) . chr(10) . $rowtag['municipio'] . chr(13) . chr(10) . $rowtag['estado'] . chr(13) . chr(10) . $rowtag['codigo'] . chr(13) . chr(10) . "Almacen: " . $renglonalm["locationname"];

        $entregatagdata = "Unidad Negocio: " . $rowtag['tagref'] . " - " . $rowtag['nombre'] . chr(13) . chr(10);

        $sql = "SELECT legalbusinessunit.address1 AS calle,
                    legalbusinessunit.address2 AS colonia,
                    legalbusinessunit.address3 AS region,
                    legalbusinessunit.address4 AS estado,
                    legalbusinessunit.address5 AS cp ,
                    legalbusinessunit.logo,
                    legalbusinessunit.legalname,
                    legalbusinessunit.email,
                    legalbusinessunit.comments,
                    legalbusinessunit.telephone,
                    legalbusinessunit.fax,
                    legalbusinessunit.taxid,
                    tags.pofootertext,
                    legalbusinessunit.legalurl,
                    legalbusinessunit.anchologo,
                    legalbusinessunit.altologo,
                    purchorders.requisitionno, purchorders.wo, workorders.wodescription
                 FROM purchorders
                 INNER JOIN tags ON purchorders.tagref = tags.tagref
                 INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
                 LEFT JOIN workorders ON purchorders.wo = workorders.wo
                 WHERE purchorders.orderno = '" . $_GET['OrderNo']."'";

        $resultlegal = DB_query($sql, $db);
        $myrowlegal = DB_fetch_array($resultlegal);

        $posY = $this->GetY() - 3;

        $logo = "./" . $myrowlegal['logo'];

        if ($_SESSION["FlagLogo"] == 1) {
            $this->Image($logo, 10, 3, 70, 20);
        }
        else {
            $this->Image($logo, 5, $posY, $myrowlegal['anchologo'], $myrowlegal['altologo']);
        }

        //$this->Image($myrowlegal['logo'] ,10,3,130,31);
        $this->SetY($posY);

        //datos de la empresa
        $legalname = $myrowlegal['legalname'];

        $this->SetFont('helvetica', 'B', 9);
        $this->cell('', 4, $legalname, 0, 1, 'R');
        $this->cell('', 4, 'RFC. ' . $myrowlegal['taxid'], 0, 1, 'R');
        $this->SetTextColor(254, 154, 46);
        $this->cell('', 4, $myrowlegal['legalurl'], 0, 1, 'R');
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->cell('', 4, $myrowlegal['calle'] . ' ' . $myrowlegal['colonia'], 0, 1, 'R');
        $this->cell('', 4, $myrowlegal['region'] . ', ' . $myrowlegal['estado'] . ', CP. ' . $myrowlegal['cp'], 0, 1, 'R');
        $this->cell('', 4, 'Tel.: ' . $myrowlegal['telephone'] . ' Fax: ' . $myrowlegal['fax'], 0, 1, 'R');
        $this->cell('', 4, $myrowlegal['comments'], 0, 1, 'R');
        $this->leyendaPiePagina = $myrowlegal['pofootertext'];
        $emailconfig = "";
        if (isset($_SESSION['POPDFMail'])) {
            $emailconfig = $_SESSION['POPDFMail'];
        }
        else {

            if ($_SESSION['POPDFLegalEmail'] == '1') {
                $emailconfig = '' . $myrowlegal['email'];
            } else {

                if (!empty($renglonalm['contact'])) {
                    $emailconfig = '' . $renglonalm['contact'];
                }
                elseif (!empty($myrowlegal['email'])) {
                    $emailconfig = '' . $myrowlegal['email'];
                }
            }
        }
        $this->cell('', 4, $emailconfig, 0, 1, 'R');
        $this->Ln();

        $this->SetFillColor(0, 0, 0);
        $this->cell('', 2, '', 'B', 1, 'L', 1);
        $this->Ln();
        $this->SetFont('helvetica', '', 12);
        $this->cell(50, 4, nombremeslargo(substr($fecha, 5, 2)) . " " . substr($fecha, 8, 2) . ", " . substr($fecha, 0, 4), 0, 0, 'L');
        $this->SetFont('helvetica', 'B', 11);
        $this->cell('', 4, 'Orden Compra', 0, 1, 'R');
        $this->SetFont('helvetica', 'B', 10);
        $this->cell(20, 5, 'Pagina ' . $this->PageNo(), 0, 0, 'L');
        $this->SetFont('helvetica', 'B', 10);
        $this->cell('', 4, $_GET['OrderNo'], 0, 1, 'R');


        if($reg['status'] == 'Cancelled'){
            $this->Image('jasper/facturacion/cancelado.jpeg' , 10 ,80, 180 , 130,'JPEG');
        }



        //if (Havepermission($_SESSION['UserID'], 1314, $db)) {
        if (empty($myrowlegal['requisitionno']) == false) {
            $this->SetFont('helvetica', 'B', 11);
            $this->cell('', 4, 'Pedido Venta', 0, 1, 'R');
            $this->SetFont('helvetica', 'B', 10);
            $this->cell('', 4, $myrowlegal['requisitionno'], 0, 1, 'R');
        }

        // mostrar orden de trabajo cuando trae el dato

        $ordentrabajo = '';
        if($myrowlegal['wo'] == -1) {
            $ordentrabajo = 'Ver Detalle';
        } else if ($myrowlegal['wo'] > 0) {
            $ordentrabajo = $myrowlegal['wo'] . " - " . $myrowlegal['wodescription'];
        }


        if (!empty($ordentrabajo)) {
            $this->SetFont('helvetica', 'B', 11);
            $this->cell('', 4, 'Orden Trabajo', 0, 1, 'R');
            $this->SetFont('helvetica', 'B', 10);
            $this->cell('', 4, $ordentrabajo, 0, 1, 'R');
        }

        //Imprimir el folio
        if (isset($myrowlegal['requisitionno']) and empty($myrowlegal['requisitionno']) == false) {
            $SQLFolios = "SELECT folio
                    FROM debtortrans
                    WHERE debtortrans.order_ = '" . $myrowlegal['requisitionno'] . "'
                    AND debtortrans.invtext NOT LIKE '%cancelad%' AND debtortrans.type IN (10, 110, 119)";

            $rsFolios = DB_query($SQLFolios, $db);

            $foliostmp = '';
            while ($rowFolios = DB_fetch_array($rsFolios)) {
                $foliostmp = $rowFolios['folio'] . ' ';
            }
        }
        if (Havepermission($_SESSION['UserID'], 1400, $db)) {
            if (isset($foliostmp) & $foliostmp != '') {
                $this->SetFont('helvetica', 'B', 11);
                $this->cell('', 4, 'Folio', 0, 1, 'R');
                $this->SetFont('helvetica', 'B', 10);
                $this->cell('', 4, $foliostmp, 0, 1, 'R');
            }
        }

        //
        $this->SetFont('helvetica', 'B', 8);
        $this->Ln();
        $sqles = "SELECT purchorders.status
                    FROM purchorders
                    WHERE purchorders.orderno = '" . $_GET['OrderNo'] . "'";

        $resultsales = DB_query($sqles, $db);
        $rowestatus = DB_fetch_array($resultsales);
        if ($rowestatus['status'] == "Pending" OR $rowestatus['status'] =='Delivered') {
            $this->SetFont('helvetica', 'B', 8);
            $this->cell('', 4, 'ESTA ORDEN NO ESTA AUTORIZADA, FAVOR DE NO SURTIRLA', 0, 1, 'R');
        }

        // mensaje de que no se encuentra autorizada
        if ($_SESSION['DespliegaInfoUNCompra'] == 1) {
            $this->Ln();
            $this->SetFillColor(205, 205, 205);
            $this->cell(66, 5, 'Proveedor', 1, 0, 'L', 1);
            $this->cell(1, 5, '', 0, 0, 'C');
            $this->cell(66, 5, 'Unidad Negocio', 1, 0, 'L', 1);
            $this->cell(66, 5, 'Direccion Entrega', 1, 1, 'L', 1);
            $actY = $this->GetY();
            $actX = $this->GetX();
            $this->SetFont('helvetica', '', 7);
            $this->MultiCell(66, 4, htmlspecialchars_decode(str_replace("&AMP;QUOT;&q", "\"", $supplierdata)), 1, 'L');
            $finalY = $this->GetY();
            $this->SetXY($actX + 66, $actY);
            $this->cell(1, 5, '', 0, 0, 'C');
            $this->MultiCell(66, 4, htmlspecialchars_decode(str_replace("&AMP;QUOT;&q", "\"", $tagdata)), 1, 'L');

            $this->SetXY($actX + 132, $actY);
            $this->cell(1, 5, '', 0, 0, 'C');
            $this->MultiCell(66, 4, htmlspecialchars_decode(str_replace("&AMP;QUOT;&q", "\"", $deliverydata)), 1, 'L');
            if ($this->GetY() > $finalY) $finalY = $this->GetY();
        }
        else {
            $this->Ln();
            $this->SetFillColor(205, 205, 205);
            $this->cell(97, 5, 'Proveedor', 1, 0, 'L', 1);
            $this->cell(1, 5, '', 0, 0, 'C');
            $this->cell(97, 5, 'Direccion Entrega', 1, 1, 'L', 1);
            $actY = $this->GetY();
            $actX = $this->GetX();
            $this->SetFont('helvetica', '', 7);
            $this->MultiCell(97, 4, htmlspecialchars_decode(str_replace("&AMP;QUOT;&q", "\"", $supplierdata)), 1, 'L');
            $finalY = $this->GetY();
            $this->SetXY($actX + 97, $actY);
            $this->cell(1, 5, '', 0, 0, 'C');
            $this->MultiCell(97, 4, htmlspecialchars_decode(str_replace("&AMP;QUOT;&q", "\"", $entregatagdata . $deliverydata)), 1, 'L');
            if ($this->GetY() > $finalY) $finalY = $this->GetY();
        }

        $this->SetY($finalY);
        $this->Ln(4);
        $this->SetFillColor(254, 154, 46);
        $this->SetX(30);
        $this->SetFont('helvetica', 'B', 8);
        $this->cell(60, 5, 'Metodo entrega', 1, 0, 'C', 1);
        $this->SetX(130);
        $this->cell(60, 5, 'Metodo pago', 1, 1, 'C', 1);

        $this->SetX(30);
        $this->SetFont('helvetica', 'B', 7);
        $this->cell(60, 5, $shipname, 1, 0, 'C');
        $this->SetX(130);
        $this->cell(60, 5, $paymentterm, 1, 1, 'C');

        $this->Ln(3);
        $this->SetFillColor(0, 0, 0);
        $this->cell('', 1, '', 'B', 1, 'L', 1);
        $this->Ln(2);

        $this->SetFillColor(205, 205, 205);
        $this->cell(15, 4, 'Cantidad', 1, 0, 'C', 1);
        $this->cell(40, 4, 'Producto', 1, 0, 'C', 1);
        $this->cell(70, 4, 'Descripcion', 1, 0, 'C', 1);
        $this->cell(20, 4, 'U.M.', 1, 0, 'C', 1);

        $this->cell(20, 4, 'Precio', 1, 0, 'C', 1);

        if (Havepermission($_SESSION["UserID"], 1939, $db) != 1){
            $this->cell(10, 4, 'Desc.', 1, 0, 'C', 1);
        } else {
            $this->cell(10, 4, ' ', 1, 0, 'C', 1);
        }

        $this->cell(20, 4, 'Importe', 1, 1, 'C', 1);
    }
}

function WriteHTML($html) {

    // HTML parser

    $html = str_replace("\n", ' ', $html);
    $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($a as $i => $e) {
        if ($i % 2 == 0) {

            // Text
            if ($this->HREF) $this->PutLink($this->HREF, $e);
            else $this->Write(4, $e);
        }
        else {

            // Tag
            if ($e[0] == '/') $this->CloseTag(strtoupper(substr($e, 1)));
            else {

                // Extract attributes
                $a2 = explode(' ', $e);
                $tag = strtoupper(array_shift($a2));
                $attr = array();
                foreach ($a2 as $v) {
                    if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3)) $attr[strtoupper($a3[1]) ] = $a3[2];
                }
                $this->OpenTag($tag, $attr);
            }
        }
    }
}

?>
