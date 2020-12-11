<?php
/**
 * Funciones Generales del Sistema
 *
 * @category Funciones
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/06/2017
 * Fecha Modificación: 01/06/2017
 * Funciones Generales del Sistema
 */

function prnMsg($Msg, $Type = 'info', $Prefix = '')
{
    echo getMsg($Msg, $Type, $Prefix);
}

function GetUrlToPrint($tagref, $typeid, $db)
{
    $sql="SELECT *
	      FROM sysDocumentIndex s
	      WHERE s.tagprint in(".  $tagref . ",0)
		AND s.typeid = '" . $typeid. "'
	      order by tagprint desc ";
    $QUERYURL = DB_query($sql, $db, '', '');
    $myrowURL = DB_fetch_array($QUERYURL);
    return $myrowURL['typeabbrev'];
}

function ZeroGuion($valor)
{
    if ($valor<=0) {
        $valret='-';
    } else {
        $valret=$valor;
    }

    return $valret;
}

//Funcion para agregar dateadd
function dateadd_dias($date, $dd = 0, $mm = 0, $yy = 0, $hh = 0, $mn = 0, $ss = 0)
{
    $date_r = getdate(strtotime($date));
    $date_result = date("Y/m/d ", mktime(($date_r["hours"]+$hh), ($date_r["minutes"]+$mn), ($date_r["seconds"]+$ss), ($date_r["mon"]+$mm), ($date_r["mday"]+$dd), ($date_r["year"]+$yy)));
    return $date_result;
}



function GetUrlToPrintNu($tagref, $areacode, $legalid, $typeid, $db)
{
    $sql="SELECT *
	      FROM sysDocumentIndex s
	      WHERE s.tagprint in(".  $tagref . ",0)
	       AND areacode in('".$areacode."','0')
	       AND legalid in('".$legalid."',0)
	       AND s.typeid = " . $typeid;
    $QUERYURL = DB_query($sql, $db, '', '');
    $myrowURL = DB_fetch_array($QUERYURL);
    return $myrowURL['typeabbrev'];
}
function GetUrlToPrint2($tagref, $typeid, $db)
{
    
    $sql="SELECT *
	      FROM sysDocumentIndex s
	      WHERE s.tagref in( ".  $tagref . ",0) and s.typeid = " . $typeid;
    $QUERYURL = DB_query($sql, $db, '', '');
    $myrowURL = DB_fetch_array($QUERYURL);
    return $myrowURL['typeurl'];
}

function GetUrlToPrintNu2($tagref, $areacode, $legalid, $typeid, $db)
{
    $sql="SELECT *
	      FROM sysDocumentIndex s
	      WHERE s.tagprint in(".  $tagref . ",0)
	       AND s.typeid in( " . $typeid.",0)
	       AND areacode in('".$areacode."',0)
	       AND legalid in('".$legalid."',0)";
    $QUERYURL = DB_query($sql, $db, '', '');
    $myrowURL = DB_fetch_array($QUERYURL);
    return $myrowURL['typeurl'];
}

function ExistsNote($invoice, $typeid, $tagref, $db)
{
    $sql="SELECT *
	      FROM notesorders s
	      WHERE s.Invoicetype = " . $typeid."
	      AND s.Invoice=".$invoice ;
    //	echo $sql;
    $ResultQ = DB_query($sql, $db, '', '');
    if (DB_num_rows($ResultQ)>=1) {
        //return 1;
        //Valida si existe nota y aun tiene partidas por procesar
        
        $myrowcliente=DB_fetch_array($ResultQ);
        $Orderno=$myrowcliente['orderno'];
        $SQL="select * from notesorderdetails where completed=0 and orderno=".$Orderno;
        $ResultQ = DB_query($SQL, $db, '', '');
        if (DB_num_rows($ResultQ)>=1) {
            return $Orderno;
        } else {
            return -1;
        }
    } else {
        return 0;
    }
}

function ExistsNoteCredit($invoice, $typeid, $tagref, $db)
{
    $sql="SELECT *
	      FROM salesorders s
	      WHERE s.tagref = ".  $tagref . " and s.CreditType = " . $typeid."
	      AND s.CreditNumber=".$invoice ;
    $ResultQ = DB_query($sql, $db, '', '');
    
    if (DB_num_rows($ResultQ)>1) {
        return 1;
    } else {
        return 0;
    }
}


function getMsg($Msg, $Type = 'info', $Prefix = '')
{
    $Colour='';
    switch ($Type) {
        case 'error':
            $Class = 'error';
            $Class = 'alert-danger';
            $Prefix = $Prefix ? $Prefix : _('ERROR') . ' ' ._(':');
            break;
        case 'warn':
            $Class = 'warn';
            $Class = 'alert-warning';
            $Prefix = $Prefix ? $Prefix : _('ADVERTENCIA') . ' ' . _(':');
            break;
        case 'success':
            $Class = 'success';
            $Class = 'alert-success';
            $Prefix = $Prefix ? $Prefix : _('ÉXITO') . ' ' . _(':');
            break;
        case 'info':
        default:
            $Prefix = $Prefix ? $Prefix : _('INFO') . ' ' ._(':');
            $Class = 'info';
            $Class = 'alert-info';
    }
    //return '<DIV class="'.$Class.'"><B>' . $Prefix . '</B> ' .$Msg . '</DIV>';
    return '<div class="alert '.$Class.' alert-dismissable">' . '<button id="idBtnClose" type="button" class="close" data-dismiss="alert">&times;</button>' . '<p>' . $Prefix . ' ' . $Msg . '</p>' . '</div>';
}

function enviaEmail($toEmail, $subjectEmail, $messageEmail)
{
    $to = $toEmail; //"somebody@example.com, somebodyelse@example.com";
    $subject = $subjectEmail; //"HTML email";
    
    $message = $messageEmail;
    /*"
	<html>
	<head>
	<title>HTML email</title>
	</head>
	<body>
	<p>This email contains HTML Tags!</p>
	<table>
	<tr>
	<th>Firstname</th>
	<th>Lastname</th>
	</tr>
	<tr>
	<td>John</td>
	<td>Doe</td>
	</tr>
	</table>
	</body>
	</html>
	";*/

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
    
    $headers .= 'From: <webmaster@tecnoaplicada.com>' . "\r\n";
    
    mail($to, $subject, $message, $headers);
}

function IsEmailAddress($TestEmailAddress)
{
    if (function_exists('preg_match')) {
        if (preg_match("/^(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}$/", $TestEmailAddress)) {
            return true;
        } else {
            return false;
        }
    } else {
        if (strlen($TestEmailAddress)>5 and strstr($TestEmailAddress, '@')>2 and (strstr($TestEmailAddress, '.co')>3 or strstr($TestEmailAddress, '.org')>3 or strstr($TestEmailAddress, '.net')>3 or strstr($TestEmailAddress, '.edu')>3 or strstr($TestEmailAddress, '.biz')>3)) {
            return true;
        } else {
            return false;
        }
    }
}

function ContainsIllegalCharacters($CheckVariable)
{

    if (strstr($CheckVariable, "'")
        or strstr($CheckVariable, '+')
        or strstr($CheckVariable, "\"")
        or strstr($CheckVariable, '&')
        or strstr($CheckVariable, "\\")
        or strstr($CheckVariable, '"')) {
        return true;
    } else {
        return false;
    }
}

function pre_var_dump(&$var)
{
    echo "<div align=left><pre>";
    var_dump($var);
    echo "</pre></div>";
}

class XmlElement
{
    var $name;
    var $attributes;
    var $content;
    var $children;
}

function GetECBCurrencyRatesDLS()
{
    $xml = file_get_contents('http://www.sat.gob.mx');
    $moneda=substr($xml, strpos($xml, 'sitio_internet/asistencia_contribuyente/informacion_frecuente/tipo_cambio'), 600);
    $monedavalor=explode('>TC', $moneda);
    $valormonedax=trim($monedavalor[1]);
    $newvalor=explode('UDIS', $valormonedax);
    $valormoneda=substr($newvalor[0], 0, -158);
    $valormoneda=str_replace('</strong></a></td><td>', '', $valormoneda);
    $ratemoneda=1/$valormoneda;
    return $ratemoneda;
}

function GetECBCurrencyRates()
{
    $xml = file_get_contents('http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml');
    //$xml = file_get_contents('http://www.sat.gob.mx');

    //echo substr($xml,strpos($xml,'sitio_internet/asistencia_contribuyente/informacion_frecuente/tipo_cambio'),600);
    //exit;
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $xml, $tags);
    xml_parser_free($parser);

    $elements = array();
    $stack = array();
    
    foreach ($tags as $tag) {
        $index = count($elements);
        if ($tag['type'] == "complete" || $tag['type'] == "open") {
            $elements[$index] = new XmlElement;
            if (isset($index)) {
                $elements[$index]->name = $tag['tag'];
            }
            if (isset($tag['attributes'])) {
                $elements[$index]->attributes = $tag['attributes'];
            }
            if (isset($tag['value'])) {
                $elements[$index]->content = $tag['value'];
            }
            if ($tag['type'] == "open") {  // push
                $elements[$index]->children = array();
                $stack[count($stack)] = &$elements;
                $elements = &$elements[$index]->children;
            }
        }
        if ($tag['type'] == "close") {  // pop
            $elements = &$stack[count($stack) - 1];
            unset($stack[count($stack) - 1]);
        }
    }
    
    $Currencies = array();

    if (!empty($elements)) {
        foreach ($elements[0]->children[2]->children[0]->children as $CurrencyDetails) {
            $Currencies[$CurrencyDetails->attributes['currency']]= $CurrencyDetails->attributes['rate'] ;
        }
    }
    $Currencies['EUR']=1;
    return $Currencies;
}
    
function GetCurrencyRate($CurrCode, $CurrenciesArray)
{
    if ((!isset($CurrenciesArray[$CurrCode]) or !isset($CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']]))) {
        return quote_oanda_currency($CurrCode);
    } elseif ($CurrCode=='EUR') {
        if ($CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']]==0) {
            return 0;
        } else {
            return 1/$CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']];
        }
    } else {
        if ($CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']]==0) {
            return 0;
        } else {
            return $CurrenciesArray[$CurrCode]/$CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']];
        }
    }
}

function quote_oanda_currency($CurrCode)
{
    $page = file('http://www.oanda.com/convert/fxdaily?value=1&redirected=1&exch=' . $CurrCode .  '&format=CSV&dest=Get+Table&sel_list=' . $_SESSION['CompanyRecord']['currencydefault']);
    $match = array();
    preg_match('/(.+),(\w{3}),([0-9.]+),([0-9.]+)/i', implode('', $page), $match);

    if (sizeof($match) > 0) {
        return $match[3];
    } else {
        return false;
    }
}


function AddCarriageReturns($str)
{
    return str_replace('\r\n', chr(10), $str);
}


function wikiLink($type, $id)
{
    if ($_SESSION['WikiApp']==_('WackoWiki')) {
        echo '<a target="_blank" href="../' . $_SESSION['WikiPath'] . '/' . $type .  $id . '">' . _('Wiki ' . $type . ' Knowlege Base') . '</A><BR>';
    } elseif ($_SESSION['WikiApp']==_('MediaWiki')) {
        echo '<a target="_blank" href="../' . $_SESSION['WikiPath'] . '/index.php/' . $type . '/' .  $id . '">' . _('Wiki ' . $type . ' Knowlege Base') . '</A><BR>';
    }
}

function add_ceros($numero, $ceros)
{
    $insertar_ceros =0;
    $order_diez = explode(".", $numero);
    $dif_diez = $ceros - strlen($order_diez[0]);
    for ($m = 0; $m < $dif_diez; $m++) {
        @$insertar_ceros .= 0;
    }
    return $insertar_ceros .= $numero;
}

function add_cerosstring($numero, $ceros)
{
    $dif_diez = $ceros - strlen($numero);
    for ($m = 0; $m < $dif_diez; $m++) {
        @$insertar_ceros .= 0;
    }
    return $insertar_ceros .= $numero;
}

function add_spacesstring($numero, $ceros)
{
    $dif_diez = $ceros - strlen($numero);
    for ($m = 0; $m < $dif_diez; $m++) {
        @$insertar_ceros .= '+';
    }
    return $numero .= $insertar_ceros ;
}

function left($string, $count)
{
    return substr($string, 0, $count);
}

function glsnombremescorto($idmes)
{
    $nombremescorto = "";
    switch ($idmes) {
        case 1:
            $nombremescorto = "ENE";
            break;
        case 2:
            $nombremescorto = "FEB";
            break;
        case 3:
            $nombremescorto = "MAR";
            break;
        case 4:
            $nombremescorto = "ABR";
            break;
        case 5:
            $nombremescorto = "MAY";
            break;
        case 6:
            $nombremescorto = "JUN";
            break;
        case 7:
            $nombremescorto = "JUL";
            break;
        case 8:
            $nombremescorto = "AGO";
            break;
        case 9:
            $nombremescorto = "SEP";
            break;
        case 10:
            $nombremescorto = "OCT";
            break;
        case 11:
            $nombremescorto = "NOV";
            break;
        case 12:
            $nombremescorto = "DIC";
            break;
    }
    return $nombremescorto;
}

function glsnombremeslargo($idmes)
{
    $nombremescorto = "";
    switch ($idmes) {
        case 1:
            $nombremescorto = "ENERO";
            break;
        case 2:
            $nombremescorto = "FEBRERO";
            break;
        case 3:
            $nombremescorto = "MARZO";
            break;
        case 4:
            $nombremescorto = "ABRIL";
            break;
        case 5:
            $nombremescorto = "MAYO";
            break;
        case 6:
            $nombremescorto = "JUNIO";
            break;
        case 7:
            $nombremescorto = "JULIO";
            break;
        case 8:
            $nombremescorto = "AGOSTO";
            break;
        case 9:
            $nombremescorto = "SEPTIEMBRE";
            break;
        case 10:
            $nombremescorto = "OCTUBRE";
            break;
        case 11:
            $nombremescorto = "NOVIEMPRE";
            break;
        case 12:
            $nombremescorto = "DICIEMBRE";
            break;
    }
    return $nombremescorto;
}

function translatedate($date)
{
    $arrfecha = explode(' ', $date);
    
    switch ($arrfecha[0]) {
        case 'January':
            $tfecha = 'Enero ' . $arrfecha[1];
            break;
        case 'February':
            $tfecha = 'Febrero ' . $arrfecha[1];
            break;
        case 'March':
            $tfecha = 'Marzo ' . $arrfecha[1];
            break;
        case 'April':
            $tfecha = 'Abril ' . $arrfecha[1];
            break;
        case 'May':
            $tfecha = 'Mayo ' . $arrfecha[1];
            break;
        case 'June':
            $tfecha = 'Junio ' . $arrfecha[1];
            break;
        case 'July':
            $tfecha = 'Julio ' . $arrfecha[1];
            break;
        case 'August':
            $tfecha = 'Agosto ' . $arrfecha[1];
            break;
        case 'September':
            $tfecha = 'Septiembre ' . $arrfecha[1];
            break;
        case 'October':
            $tfecha = 'Octubre ' . $arrfecha[1];
            break;
        case 'November':
            $tfecha = 'Noviembre ' . $arrfecha[1];
            break;
        case 'December':
            $tfecha = 'Diciembre ' . $arrfecha[1];
            break;
    }
    
    return $tfecha;
}

function nombrexid($clave, $campo, $tabla, $db)
{
    $xsql = "SELECT txt" . $campo . "
		FROM " . $tabla . "
		WHERE " . $campo . " = '" . $clave . "' LIMIT 1";

    $xresult = mysqli_query($db, $xsql);
    if ($xmyrow=DB_fetch_array($xresult)) {
        return $xmyrow[0];
    } else {
        return "sin nombre";
    }
}


function validacheck($dimension, $groupby)
{
    $chk='';
    if ($dimension=='tiempo') {
        switch ($groupby) {
            case 'Anio':
                $chk='checked';
                break;
            case 'Cuatrimestre':
                $chk='checked';
                break;
            case 'Cuatrimestre':
                $chk='checked';
                break;
            case 'Trimestre':
                $chk='checked';
                break;
            case 'Mes':
                $chk='checked';
                break;
            case 'Semana':
                $chk='checked';
                break;
            case 'Fecha':
                $chk='checked';
                break;
            case 'Dia':
                $chk='checked';
                break;
            case 'NombreDia':
                $chk='checked';
                break;
            case 'Feriado':
                $chk='checked';
                break;
            case 'FinDeSemana':
                $chk='checked';
                break;
            case 'AnioFiscal':
                $chk='checked';
                break;
            case 'TrimestreFiscal':
                $chk='checked';
                break;
            case 'PeriodoFiscal':
                $chk='checked';
                break;
        }
    } elseif ($dimension=='negocio') {
        switch ($groupby) {
            case 'legalbusiness':
                $chk='checked';
                break;
            case 'regiondescription':
                $chk='checked';
                break;
            case 'areadescription':
                $chk='checked';
                break;
            case 'department':
                $chk='checked';
                break;
            case 'tagdescription':
                $chk='checked';
                break;
            case 'proyecto':
                    $chk='checked';
                break;
        }
    } elseif ($dimension=='proveedor') {
        switch ($groupby) {
            case 'typedocument':
                $chk='checked';
                break;
            case 'beneficiario':
                $chk='checked';
                break;
            case 'proveedor':
                $chk='checked';
                break;
            case 'cliente':
                $chk='checked';
                break;
            case 'namedebtor':
                    $chk='checked';
                break;
            case 'nombrecuenta':
                $chk='checked';
                break;
        }
    } elseif ($dimension=='vendedor') {
        switch ($groupby) {
            case 'salesman':
                $chk='checked';
                break;
            case 'userregister':
                $chk='checked';
                break;
            case 'debtorno':
                $chk='checked';
                break;
            case 'contacto':
                $chk='checked';
                break;
            case 'statusopportunity':
                $chk='checked';
                break;
            case 'opportunity':
                $chk='checked';
                break;
            case 'namedebtor':
                $chk='checked';
                break;
        }
    } elseif ($dimension=='documento') {
        switch ($groupby) {
            case 'typename':
                $chk='checked';
                break;
            case 'currency':
                $chk='checked';
                break;
            case 'folio':
                $chk='checked';
                break;
            case 'chequeno':
                $chk='checked';
                break;
        }
    } elseif ($dimension=='contabilidad') {
        switch ($groupby) {
            case 'genero':
                $chk='checked';
                break;
            case 'rubro':
                $chk='checked';
                break;
            case 'grupo':
                $chk='checked';
                break;
            case 'cuenta':
                $chk='checked';
                break;
            case 'subcuenta':
                $chk='checked';
                break;
            case 'sscuenta':
                $chk='checked';
                break;
            case 'ssscuenta':
                $chk='checked';
                break;
            case 'sssscuenta':
                $chk='checked';
                break;
            case 'sujetocontable':
                $chk='checked';
                break;
        }
    } elseif ($dimension=='cliente') {
        switch ($groupby) {
            case 'typeclient':
                $chk='checked';
                break;
            case 'namedebtor':
                $chk='checked';
                break;
            case 'emailssucursal':
                $chk='checked';
                break;
            case 'emailscliente':
                $chk='checked';
                break;
        }
    } elseif ($dimension=='producto') {
        switch ($groupby) {
            case 'groupdescription':
                $chk='checked';
                break;
            case 'linedescription':
                $chk='checked';
                break;
            case 'categorydescripcion':
                $chk='checked';
                break;
            case 'stockdescription':
                $chk='checked';
                break;
        }
    } elseif ($dimension=='vehiculo') {
        switch ($groupby) {
            case 'mark':
                $chk='checked';
                break;
            case 'model':
                $chk='checked';
                break;
            case 'aniovehicle':
                $chk='checked';
                break;
            case 'plate':
                $chk='checked';
                break;
            case 'serie':
                $chk='checked';
                break;
        }
    } elseif ($dimension=='clavepresupuestal') {
        switch ($groupby) {
            case 'ramo':
                $chk='checked';
                break;
            case 'organosuperior':
                $chk='checked';
                break;
            case 'unidadejecutora':
                $chk='checked';
                break;
            case 'rubrodeingresos':
                $chk='checked';
                break;
            case 'tipodegasto':
                $chk='checked';
                break;
            case 'objetodelgasto':
                $chk='checked';
                break;
            case 'finalidad':
                $chk='checked';
                break;
            case 'funcion':
                $chk='checked';
                break;
            case 'subfuncion':
                $chk='checked';
                break;
            case 'reasignacion':
                $chk='checked';
                break;
            case 'actividadinstitucional':
                $chk='checked';
                break;
            case 'programa':
                $chk='checked';
                break;
            case 'subprograma':
                $chk='checked';
                break;
            case 'objetivos':
                $chk='checked';
                break;
            case 'proyecto_estrategia':
                $chk='checked';
                break;
            case 'obra':
                $chk='checked';
                break;
            case 'beneficiario':
                $chk='checked';
                break;
            case 'espaciogeografico':
                $chk='checked';
                break;
            case 'rubrocp':
                $chk='checked';
                break;
            case 'tipo':
                $chk='checked';
                break;
            case 'clase':
                $chk='checked';
                break;
            case 'concepto':
                $chk='checked';
                break;
            case 'fuentedecontribucion':
                $chk='checked';
                break;
            case 'clavepresupuestal':
                $chk='checked';
                break;
        }
    }
        
    return  $chk;
}

function TraeTitulo($ver, $db)
{
    $fijoverdos=explode('=', $ver);
    $fijoverx=trim($fijoverdos[0]);
    $fijoverx=trim(str_replace('!', '', $fijoverdos[0]));


    switch ($fijoverx) {
        case 'Anio':
            $fijover = "A�o = ".$fijoverdos[1];
            break;
        case 'Mes':
            $fijover = "Mes = ".glsnombremeslargo($fijoverdos[1]);
            break;
        case 'Trimestre':
            $fijover = "Trimestre = ".$fijoverdos[1];
            break;
        case 'Cuatrimestre':
            $fijover = "Cuatrimestre = ".$fijoverdos[1];
            break;
        case 'MesAnio':
            $fijover = "Mes-A�o = ".$fijoverdos[1];
            break;
        case 'Dia':
            $fijover = "Dia = ".$fijoverdos[1];
            break;
        case 'Fecha':
            $fijover = "Fecha = ".$fijoverdos[1];
            break;
        case 'NombreDia':
            $fijover = "Nombre Dia =".$fijoverdos[1];
            break;
        case 'FinDeSemana':
            $fijover="Fin De Semana = ".$fijoverdos[1];
            break;
        case 'legalbusiness':
            $fijover = "Empresa = ".$fijoverdos[1];
            break;
        case 'areadescription':
            $fijover = "Sucursal = ".$fijoverdos[1];
            break;
        case 'regiondescription':
            $fijover = "Matriz = ".$fijoverdos[1];
            break;
        case 'region':
            $fijover = "Matriz = ".$fijoverdos[1];
            break;
        case 'tagdescription':
            $fijover = "Unidad Negocio = ".$fijoverdos[1];
            break;
        case 'unidadnegocio':
            $fijover = "Unidad Negocio = ".$fijoverdos[1];
            break;
        case 'location':
            $fijover = "Almacen = ".$fijoverdos[1];
            break;
        case 'salestype':
            $fijover = "Tipo Venta = ".$fijoverdos[1];
            break;
        case 'custsalesman':
            $fijover = "Vendedor Cliente= ".$fijoverdos[1];
            break;
        case 'salesman':
            $fijover = "Vendedor Factura= ".$fijoverdos[1];
            break;
        case 'regiondescription':
            $fijover = "Matriz = ".$fijoverdos[1];
            break;
        case 'userregister':
            $fijover = "Usuario = ".$fijoverdos[1];
            break;
        case 'paymentterm':
            $fijover = "Termino De Pago = ".$fijoverdos[1];
            break;
        case 'currency':
            $fijover = "Moneda = ".$fijoverdos[1];
            break;
        case 'typeclient':
            $fijover = "Tipo Cliente = ".$fijoverdos[1];
            break;
        case 'namedebtor':
            $fijover = "Cliente = ".$fijoverdos[1];
            break;
        case 'folio':
            $fijover = "Factura = ".$fijoverdos[1];
            break;
        case 'linedescription':
            $fijover = "Linea = ".$fijoverdos[1];
            break;
        case 'groupdescription':
            $fijover = "Grupo = ".$fijoverdos[1];
            break;
        case 'categorydescripcion':
            $fijover = "Categoria = ".$fijoverdos[1];
            break;
        case 'stockdescription':
            $fijover = "Producto = ".$fijoverdos[1];
            break;
        case 'userprocess':
            $fijover = "Usuario = ".$fijoverdos[1];
            break;
        case 'department':
            $fijover = "Departamento = ".$fijoverdos[1];
            break;
        case 'namesupplier':
            $fijover = "Proveedor = ".$fijoverdos[1];
            break;
        case 'typesupplier':
            $fijover = "Tipo Proveedor = ".$fijoverdos[1];
            break;
        case 'orderno':
            $fijover = "Orden Compra = ".$fijoverdos[1];
            break;
        case 'typename':
            $fijover = "Tipo Cliente = ".$fijoverdos[1];
            break;
        case 'legalname':
            $fijover = "Empresa = ".$fijoverdos[1];
            break;
        case 'departamento':
            $fijover = "Depto = ".$fijoverdos[1];
            break;
        case 'cliente':
            $fijover = "Cliente = ".$fijoverdos[1];
            break;
        case 'edad':
            $fijover = "Edad = ".$fijoverdos[1];
            break;
        case 'estado':
            $fijover = "Estado = ".$fijoverdos[1];
            break;
        case 'ciudad':
            $fijover = "Ciudad = ".$fijoverdos[1];
            break;
        case 'cp':
            $fijover = "C.P. = ".$fijoverdos[1];
            break;
        case 'vehiculos':
            $fijover = "No. Vehiculos = ".$fijoverdos[1];
            break;
        case 'Fecha':
            $fijover = "Fecha = ".left($fijoverdos[1], 10);
            break;
        case 'periodico':
              $fijover = "Periodico = ".left($fijoverdos[1], 10);
            break;
        case 'tema':
            $fijover = "Tema = ".left($fijoverdos[1], 10);
            break;
        case 'personaje':
              $fijover = "Personaje = ".left($fijoverdos[1], 10);
            break;
        case 'palabra':
            $fijover = "Palabra Clave = ".left($fijoverdos[1], 10);
            break;
        case 'institucion':
            $fijover = "Institucion = ".left($fijoverdos[1], 10);
            break;
        case 'partido':
            $fijover = "Partido Politico = ".left($fijoverdos[1], 10);
            break;
        case 'seccion':
            $fijover = "Seccion= ".left($fijoverdos[1], 10);
            break;
        case 'indice':
            $fijover = "Indice= ".left($fijoverdos[1], 10);
            break;
        case 'wodescription':
            $fijover = "Orden Trabajo= ".left($fijoverdos[1], 10);
            break;
        case 'pedidoventa':
            $fijover = "Pedido Venta= ".left($fijoverdos[1], 10);
            break;
        case 'namedebtor':
            $fijover = "Cliente Venta= ".left($fijoverdos[1], 10);
            break;
        case 'womasterid':
            $fijover = "Producto Maestro= ".left($fijoverdos[1], 10);
            break;
        case 'wocomponent':
            $fijover = "Producto Nivel Compra= ".left($fijoverdos[1], 10);
            break;
        case 'typedocument':
            $fijover = "Tipo Movimiento= ".left($fijoverdos[1], 10);
            break;
        case 'beneficiario':
            $fijover = "Nombre Beneficiario= ".left($fijoverdos[1], 10);
            break;
        case 'nombrecuenta':
            $fijover = "Cuenta Cheque= ".left($fijoverdos[1], 10);
            break;
        case 'chequeno':
            $fijover = "No Cheque= ".left($fijoverdos[1], 10);
            break;
        case 'proyecto':
            $fijover = " Proyecto= ".left($fijoverdos[1], 10);
            break;
        case 'salesman':
            $fijover = " Vendedor= ".left($fijoverdos[1], 10);
            break;
     
        case 'contacto':
            $fijover = " Contacto= ".left($fijoverdos[1], 10);
            break;
        case 'statusopportunity':
            $fijover = " Status Oportunidad= ".left($fijoverdos[1], 10);
            break;
        case 'opportunity':
            $fijover = " No Oportunidad= ".left($fijoverdos[1], 10);
            break;
        case 'genero':
            $fijover = "Genero = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'genero', 'DW_Presupuestos', $db);
            break;
        case 'grupo':
            $fijover = "Grupo = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'grupo', 'DW_Presupuestos', $db);
            break;
        case 'rubro':
            $fijover = "Rubro = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'rubro', 'DW_Presupuestos', $db);
            break;
        case 'cuenta':
            $fijover = "Cuenta = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'cuenta', 'DW_Presupuestos', $db);
            break;
        case 'subcuenta':
            $fijover = "Subcuenta = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'subcuenta', 'DW_Presupuestos', $db);
            break;
        case 'sscuenta':
            $fijover = "SSCuenta = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'sscuenta', 'DW_Presupuestos', $db);
            break;
        case 'ssscuenta':
            $fijover = "SSSCuenta = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'ssscuenta', 'DW_Presupuestos', $db);
            break;
        case 'sssscuenta':
            $fijover = "SSSSCuenta = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'sssscuenta', 'DW_Presupuestos', $db);
            break;
        case 'numeropoliza':
            $fijover = "Num Docto = ".$fijoverdos[1];
            break;
        case 'tipopoliza':
            $fijover = "Tipo = ".$fijoverdos[1];
            break;
        case 'sujetocontable':
            $fijover = "Sujeto Contable = ".$fijoverdos[1];
            break;
        case 'AnioFiscal':
            $fijover = "Anio Fiscal = ".$fijoverdos[1];
            break;
        case 'TrimestreFiscal':
            $fijover = "Trimestre Fiscal = ".$fijoverdos[1];
            break;
        case 'CuatrimestreFiscal':
            $fijover = "Cuatrimestre Fiscal = ".$fijoverdos[1];
            break;
        case 'PeriodoFiscal':
            $fijover = "Periodo Fiscal = ".$fijoverdos[1];
            break;
        case 'ramo':
            $fijover = "Ramo = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'ramo', 'DW_Presupuestos', $db);
            break;
        case 'organosuperior':
            $fijover = "Organo Superior = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'organosuperior', 'DW_Presupuestos', $db);
            break;
        case 'unidadejecutora':
            $fijover = "Unidad Ejecutora = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'unidadejecutora', 'DW_Presupuestos', $db);
            break;
        case 'rubrodeingreso':
            $fijover = "Rubro de Ingreso = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'rubrodeingresos', 'DW_Presupuestos', $db);
            break;
        case 'tipodegasto':
            $fijover = "Tipo de Gasto = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'tipodegasto', 'DW_Presupuestos', $db);
            break;
        case 'objetodelgasto':
            $fijover = "Objetivo del Gasto = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'objetodelgasto', 'DW_Presupuestos', $db);
            break;
        case 'finalidad':
            $fijover = "Finalidad = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'finalidad', 'DW_Presupuestos', $db);
            break;
        case 'funcion':
            $fijover = "Funcion = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'funcion', 'DW_Presupuestos', $db);
            break;
        case 'subfuncion':
            $fijover = "Subfuncion = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'subfuncion', 'DW_Presupuestos', $db);
            break;
        case 'reasignacion':
            $fijover = "Reasignación = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'reasignacion', 'DW_Presupuestos', $db);
            break;
        case 'actividadinstitucional':
            $fijover = "Actividad Institucional = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'actividadinstitucional', 'DW_Presupuestos', $db);
            break;
        case 'programa':
            $fijover = "Programa = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'programa', 'DW_Presupuestos', $db);
            break;
        case 'partidaespecifica':
            $fijover = "Partida Específica = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'partidaespecifica', 'DW_Presupuestos', $db);
            break;
        case 'fuentedefinanciamiento':
            $fijover = "Fuente de Finaciamiento = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'fuentedefinanciamiento', 'DW_Presupuestos', $db);
            break;
        case 'proyecto_estrategias':
            $fijover = "Proyecto = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'proyecto_estrategias', 'DW_Presupuestos', $db);
            break;
        case 'obra':
            $fijover = "Obra = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'obra', 'DW_Presupuestos', $db);
            break;
        case 'beneficiario':
            $fijover = "Beneficiario = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'beneficiario', 'DW_Presupuestos', $db);
            break;
        case 'espaciogeografico':
            $fijover = "Espacio Geografico = ".$fijoverdos[1] . " - " . nombrexid($fijoverdos[1], 'espaciogeografico', 'DW_Presupuestos', $db);
            break;
        case 'Fechacaptura':
            $fijover = "Fecha Captura= ".$fijoverdos[1];
            break;
        case 'Aniocaptura':
            $fijover = "A�o Captura= ".$fijoverdos[1];
            break;
        case 'Mescaptura':
            $fijover = "Mes Captura = ".$fijoverdos[1];
            break;
        case 'Semanacaptura':
            $fijover = "Semana Captura = ".$fijoverdos[1];
            break;
        case 'Diacaptura':
            $fijover = "Dia Captura= ".$fijoverdos[1];
            break;
        case 'NombreDiacaptura':
            $fijover = "Nombre Dia Captura=".$fijoverdos[1];
            break;
    }
    return $fijover;
}

function number_format_NoZero($numero)
{
    if ($numero == 0) {
        return '';
    } else {
        return number_format($numero);
    }
}

function CharClean($texto)
{
    $textolimpio = $texto;
    
    while (!(strpos($textolimpio, '"') === false)) {
        $textolimpio = substr($textolimpio, 0, strpos($textolimpio, '"')).substr($textolimpio, strpos($textolimpio, '"')+1);
    }
    
    return $textolimpio;
}
/*
function validaRfc($rfc) {
	$okRfc = 0;
	$rfc = str_replace("-", "", $rfc);
	if(strlen($rfc) >= 10 and strlen($rfc) <= 13) {
		$subsLetras = substr($rfc, 0, 4);
		$subsNumeros = substr($rfc, 4, 10);
		if(!is_numeric($subsLetras)) {
			if(is_numeric($subsNumeros)) {
				$okRfc = 1;
			} else {
				$okRfc = 0;
			}
		} else {
			$okRfc = 0;
		}
	} else {
		$okRfc = 0;
	}
	
	return $okRfc;
}*/

function validaRFC($rfc)
{
    if (preg_match("/^[a-zA-Z&]{3,4}(\d{6})((\D|\d){3})?$/i", $rfc)) {
        return 1;
    } else {
        return 0;
    }
}
function limpiarString($string)
{
    $string = strip_tags($string);
    $string = htmlentities($string);
    return stripslashes($string);
}


function getDiasEnSemana($numeroSemana, $anio)
{
    $tiempoSemana = strtotime($anio . '0104 +' . ($numeroSemana - 1) . ' weeks');

    $lunesSemana = strtotime('-' . (date('w', $tiempoSemana) - 1) . ' days', $tiempoSemana);

    $diasSemana = array ();
    for ($i = 0; $i < 7; ++$i) {
        $diasSemana[] = date('d-m-Y', strtotime('+' . $i . ' days', $lunesSemana));
    }
    
    return $diasSemana;
}

function diaSemana($dia)
{
    switch ($dia) {
        case "01":
            $nombredia= "Lunes";
            break;
        case "02":
            $nombredia= "Martes";
            break;
        case "03":
            $nombredia= "Miercoles";
            break;
        case "04":
            $nombredia= "Jueves";
            break;
        case "05":
            $nombredia= "Viernes";
            break;
        case "06":
            $nombredia= "Sabado";
            break;
        default:
            $nombredia= "Domingo";
            ;
            break;
    }
    return $nombredia;
}

function gettooltip($id, $db)
{
    $sql="SELECT *
	      FROM tooltips t
	      WHERE u_tooltip = '" . $id . "'";
    $ResultQ = DB_query($sql, $db, '', '');

    $myrow = DB_fetch_array($ResultQ);
    return $myrow['descripcion'];
}

/**
 * funcion para convertir un numero a decimal con X digitos
 * @param String $number
 * @param Int $digitos cantidad de digitos a mostrar
 * @return Float
 */
function truncateFloat($number, $digitos)
{
    $raiz = 10;
    $multiplicador = pow($raiz, $digitos);
    $resultado = ((int)($number * $multiplicador)) / $multiplicador;
    //return number_format($resultado, $digitos);
    return $resultado;
}
