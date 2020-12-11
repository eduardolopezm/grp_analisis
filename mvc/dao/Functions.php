<?php
////
class Functions{
    private $pathprefix = "../.././";
        
    function SendQuotation($params){
        $pathprefix = "../.././";

        require_once $pathprefix . 'core/ModeloBase.php';

        $UserID = $_SESSION["UserID"];
        $success = true;
        $msgerror = "";
        $typeerror = "";
        $codeerror = 0;
        $sql = "";
        $message = "";

        $tagref = $params->tagref;
        $transno = $params->transno;
        $debtorno = $params->debtorno;
        $legalid = $params->legalid;
        $tipodocto = $params->tipodocto;
        $tipocotizacion = $params->tipocotizacion;
        $emails = $params->emails;

        $modelo = new ModeloBase;
        
        //Variables para construir pdf
        $_GET['legalid'] = $legalid;
        $_GET['Tagref'] = $tagref;
        $_GET['TransNo'] = $transno;
        $_GET['tipodocto'] = $tipocotizacion;

        $pdfcode = $modelo->getPDFQuotation($legalid, $tagref, $transno, $tipocotizacion);
        
        //$doc = new pdfCotizacionTemplate();

        //$pdfcode = $doc->exportPDF(1); 

        //$emailFrom = $_SESSION['FactoryManagerEmail'];
        //$fromName = ucwords($_SESSION['DatabaseName']);
        
        //obtener mail del usuario logeado
        $realname="";
        $department="";
        /*if ($_SESSION['SendSalesOrderFromEmailUser']==1){
            $sql = "SELECT * FROM www_users WHERE userid = '".$_SESSION['UserID']."'"; 

            $resp = $modelo->ejecutarsql($sql);
            $message = "";

            if ($resp == true and !is_array($resp)){
                $success = false;
                $message = _('No datos para el Usuario ' . $_SESSION["UserID"]);
            }else{
                if ($resp == false){
                    $msgerror = "ERROR EN LA CONSULTA";
                    $typeerror = "MYSQL ERROR";
                    $codeerror = "001";
                }else{
                    for($xx = 0; $xx < count($resp); $xx++){
                        if (IsEmailAddress($resp[$xx]['email'])) {
                            $emailFrom = $resp[$xx]['email'];
                        }
                        $realname = $resp[$xx]['realname'];
                        $department = $resp[$xx]['department'];
                        $fromName = $realname;
                    }
                }
            }
        }*/
        
        $arrdebtor = array(
                        "dato" => "abcd"
                    );
        
        $response['data'][] = $arrdebtor;

        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $codeerror;
        $response['error']['codeerror'] = $sql;
        $response['data']['message'] = $message;

        //header('Content-type: application/json; charset=utf-8');
        //return json_encode($response, JSON_FORCE_OBJECT);

        return $response;
    }

    function send_email($legalid,$db,$to,$from,$serdername,$subject,$message,$funcion_id=152,$debug=0,$reply_to="",$attachments="",$message_erp="",$message_erp_error=""){
        /* 
         * send_email($legalid,$db,$to,$from,$serdername,$subject,$message[,$funcion_id,$debug,$reply_to,$attachments,$message_erp,$message_erp_error]);
         * donde:
         * $legalid     - id de legalbusiness
         * $db          - conexion de base de datos degault
         * $to          - remitente a quien va dirigido el email, separado por comas
         * $from        - email del que envia , se utilizara para responder en caso que no se especifique un $reply_to
         * $serdername  - nombre del emisor
         * $subject     - Titulo del email
         * $message     - Mensaje
         * $funcion_id  - id de la pagina utilizada, se enviará conforme a la configuracion de la empresa y modufo
         * [opcionales]
         * $debug       - So se manda = 1 se mostrará el detalle de la conexion (habilitar solamente para rastrear errores)
         * $reply_to    - email al que se espera la respuesta
         * $attachments - Array de adjuntos y tipos de datos
         * $message_erp - Mensaje a mostrar al enviar el email , como complemento cuando se envia de forma correcta
         * $message_erp_error   - Mensaje a mostrar al enviar el email , como complemento cuando hay error en el envio
         
         $attachments[] = array(
         'archivo' => $archivoPDF,
         'nombre'  => $nombre,
         'encoding'=> 'base64',
         'type'    => 'application/pdf'
         );

         $attachments[] = array(
         'archivo' => $archivoXML,
         'nombre'  => $nombre,
         'encoding'=> 'base64',
         'type'    => 'application/xml'
         );
         
         Para configuración en aplicación 
            Servidor SMTP:
            Puerto:
            Tipo de cifrado: (ssl / tls )
            Requiere autenticación ?: (si/no)
            Usuario: 
            Contraseña :
        Ingresar registro en tabla:
            sec_submodules_email_methods
        
         */
        $to_original=$to;
        $requisitos_error=false;
        $errors=array();
        {  // --- Verificación de requisitos --- //
            $tipo=gettype($to);
            $c_emails_orig=0;
            $c_emails_validos=0;
            $c_emails_invalidos=0;
            //verificación de tipo de campo de to
            switch($tipo){
                case "array":
                    {
                        $to_tmp="";
                        $c_emails_orig2=0;
                        foreach ($to_original as $email) {
                            $c_emails_orig2++;
                            if($c_emails_orig2!=1)
                            {
                                $to_tmp.=",";
                            }
                            $to_tmp.=$email;
                        }
                        $to_original=$to_tmp;
                        
                        $to_tmp="";
                        foreach ($to as $email) {
                            $c_emails_orig++;
                            
                            if(isValidEmail($email)){
                                $c_emails_validos++;
                                if($c_emails_validos!=1)
                                {
                                    $to_tmp.=",";
                                }
                                $to_tmp.=$email;
                            }else{
                                $errors[]="Email incorrecto: '".$email."'";
                                $c_emails_invalidos++;
                            }
                        }
                        $to=$to_tmp;
                    }
                    break;
                case "string":
                    {
                        $to=$to_original;
                    }
                    break;
            }
                
            if($c_emails_orig==$c_emails_invalidos && $c_emails_invalidos!=0){
                $errors[]="Todos Los email enviados son invalidos";
                $requisitos_error=true;
            }elseif($c_emails_orig!=$c_emails_validos){
                $errors[]="Uno o varios de email enviados son Invalidos '".$to_original."'";
                prnMsg("Uno o varios de email enviados son Invalidos '".$to_original."'","warn");
            }
        }
        
        if(!$requisitos_error){
            try{
                //require_once("PHPMailer/class.phpmailer.php");
                //require_once('PHPMailer/class.smtp.php'); 
                include_once("class.phpmailer.php");
                
                $mail = new PHPMailer();

                $secFunctionTable = "sec_functions";
                if (!empty($_SESSION['SecFunctionTable'])) {
                    $secFunctionTable = $_SESSION['SecFunctionTable'];
                }
            
                $SQL="SELECT submoduleid
                        FROM ".$secFunctionTable."
                        WHERE functionid='".$funcion_id."'
                                limit 1";
                $rs = DB_query($SQL,$db);
                //echo $SQL;
                $funcion_encontrada=DB_fetch_array($rs);
                if(DB_num_rows($rs)>0){ 
                
                    $SQL="SELECT
                            metodo,cifrado,desde,requiere_autenticacion,servidor,puerto,usuario,contrasena ,
                            sem.id_metodo,sem.submoduleid,
                            e.id_smtp
                            FROM legalbusiness_email_methods e
                            JOIN sec_submodules_email_methods sem ON e.id_smtp=sem.id_smtp
                            WHERE legalid='".$legalid."' 
                                    and (submoduleid='".$funcion_encontrada['submoduleid']."' or submoduleid = -1)";
                    //prnMsg($SQL,"info");;
                    $rs = DB_query($SQL,$db);
                    $metodos=DB_fetch_array($rs);
            
                    //Se establece el metodo.
                    //Si existe metodo por mmódulo lo utiliza
                    //Si no busca metodo global por empresa
                    //Si no utiliza metodo base de portalito 
                    if(DB_num_rows($rs)>0){
                        //Faltan por especificar: desde,requiere_autenticacion
                        $mail->setmetod($metodos['metodo'],$metodos['servidor'],$metodos['puerto'],$metodos['usuario'],$metodos['contrasena'],$metodos['cifrado'],$metodos['desde'],$metodos['requiere_autenticacion'],30);
                    }else{
                        $SQL="SELECT metodo,cifrado,desde,requiere_autenticacion,servidor,puerto,usuario,contrasena
                            FROM legalbusiness_email_methods e
                            WHERE legalid='".$legalid."' and metodo='smtp_all'";
                        $rs = DB_query($SQL,$db);
                        $metodos=DB_fetch_array($rs);
                        if(DB_num_rows($rs)>0){
                            //Faltan por especificar: desde,requiere_autenticacion
                            $mail->setmetod($metodos['metodo'],$metodos['servidor'],$metodos['puerto'],$metodos['usuario'],$metodos['contrasena'],$metodos['cifrado'],$metodos['desde'],$metodos['requiere_autenticacion'],30);
                        }else{
                            $mail->setmetod("smtp_base", "",25,"","","","","",30);
                        }
                    }
                    
                    //array_unique(explode ( ',', $to ));
                    $emails_ = explode ( ',', $to );
                    foreach($emails_ as $email_address){
                        if($email_address!=""){
                            if(isValidEmail($email_address))
                            {
                                    //prnMsg("email ".$email_address,"info");
                                $mail->AddAddress($email_address); // Dirección a la que llegaran los mensajes.

                            }else{
                                prnMsg("Error de email ".$email_address,"error");
                            }   
                        }
                    }
            
                    // Aquí van los datos que apareceran en el correo que reciba ,,,
                    $mail->CharSet      = 'UTF-8';
                    $mail->WordWrap     = 50;
                    $mail->IsHTML(true);
                    //$mail->From       = $from; //Dirección desde la que se enviarán los mensajes. Debe ser la misma de los datos de el servidor SMTP.
                    $mail->FromName     = $serdername;
                    $mail->Subject      = $subject;
                    $mail->Body         = $message;
                    $mail->SMTPDebug    = $debug;           
            
                    if(isValidEmail($reply_to)){
                        $mail->AddReplyTo($reply_to, $serdername);
                    }else {
                        if(isValidEmail($from)){
                            $mail->AddReplyTo($from, $serdername);
                        }
                    }
            
                    if(is_array($attachments))
                    {
                        foreach($attachments as $adjunto)
                        {
                            if(file_exists($adjunto['archivo']))
                            {
                                if(!$mail->AddAttachment($adjunto['archivo'], $adjunto['nombre'])) {
                                    prnMsg("No se pudo adjuntar el adjuntar el archivo: '".$adjunto['archivo']."'","error");    
                                }

                            }else{
                                prnMsg("No se purede adjuntar archivo al email, el archivo no existe '".$adjunto['archivo']."'","error");
                            }
                        }
                    }
            
                    if(count($errors)>0){
                        $errores_desc="";
                        foreach($errors as $error){
                            $errores_desc.="<br/>* - ".$error;
                        }
                        prnMsg("-- Errores en envio de email --".$errores_desc,"warn");
                    }
                    
                    if ($mail->Send())
                    {
                        prnMsg("Email enviado: '".implode($emails_, ',')."' ".$message_erp,"success");
                    }else{
                        prnMsg("Error en envio de email a: '".implode($emails_, ',')."' ".$message_erp_error,"error");
                        prnMsg("ErrorInfo <br/>".$mail->ErrorInfo);
                        if("SMTP Error: Could not connect to SMTP host."==$mail->ErrorInfo){
                            prnMsg("Timeout:".$mail->Timeout);
                        }
                        unset($mail);
                        if(isset($_SESSION['SMTP_LOG']) && $_SESSION['SMTP_LOG']=="true"){
                            $SQL="INSERT into legalbusiness_email_log 
                                    (id_smtp,desde,para,cc,cco,estado,smtp_server,smtp_msg,functionid,userid,fecha_registro) 
                                    values('".$metodos['id_smtp']."',".
                                            "'".$metodos['usuario']."',".
                                            "'".$emails_."','','',".
                                            "'ERROR',".
                                            "'".$metodos['servidor']."',".
                                            "'".$mail->ErrorInfo."',".
                                            "'".$funcion_id."',".
                                            "'".$_SESSION['UserID']."',".
                                            "now() )";
                            $rs_email_log = DB_query($SQL,$db);
                        }
                        //$metodos['id_smtp'],$metodos['servidor'],$metodos['puerto'],$metodos['usuario'],$metodos['contrasena'],$metodos['cifrado'],$metodos['desde'],$metodos['requiere_autenticacion']
                        return false;
                    }
                }else{
                    prnMsg("Esta Funcion (".$funcion_id.") no tiene privilegios de envio, el email no se ha enviado","error");
                    unset($mail);
                    return false;
                }
                unset($mail);
                return true;
            } catch (phpmailerException $e) {
                $errors[] = $e->errorMessage(); //Pretty error messages from PHPMailer
                foreach($errors as $error){
                    prnMsg("Fallo en envio: '".$error."'","warn");
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage(); //Boring error messages from anything else!
                foreach($errors as $error){
                    prnMsg("Fallo en envio: '".$error."'","warn");
                }
            }
        }else{
            $errores_desc="";
            foreach($errors as $error){
                $errores_desc.="<br/>* - ".$error;
            }
            prnMsg("-- Errores en envio de email --".$errores_desc,"error");
            return false;
        }
    }

    function fnObtenerAnticiposClientePV($sql){
        $pathprefix = "../.././";
        require_once $pathprefix . 'core/ModeloBase.php';

        $arrInformacion = array();
        
        $modelo = new ModeloBase;

        $resp = $modelo->ejecutarsql($sql);
        $message = "";

        if ($resp == true and !is_array($resp)){
            // $success = false; 
            // $message = _('No existe registros para la busqueda de Anticipos de Cliente');
        }else{
            if ($resp == false){
                // $success = false;
                // $msgerror = "ERROR EN LA CONSULTA";
                // $typeerror = "MYSQL ERROR";
                // $codeerror = "006";
            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    $folio = "";
                    $tasa = "";
                    if($resp[$xx]['IVA'] == 1){
                        $tasa = ", Tasa IVA 16%";
                    }else{
                        $tasa = ", Tasa IVA 0%";
                    }
                    $folio = 'ERP: '.$resp[$xx]['transno'].", Factura Anticipo: ".$resp[$xx]['factura'].$tasa;
                    $arrDatos = array(
                        "type" => ($resp[$xx]['type']),
                        "transno" => ($resp[$xx]['transno']),
                        "id" => ($resp[$xx]['id']),
                        "factura" => ($resp[$xx]['factura']),
                        "idfactura" => ($resp[$xx]['idfactura']),
                        "IVA" => ($resp[$xx]['IVA']),
                        "pendiente" => ($resp[$xx]['pendiente']),
                        "MontoAnticipo" => 0,
                        "typename" => ($resp[$xx]['typename']),
                        "invtext" => ($resp[$xx]['invtext']),
                        "monto" => ($resp[$xx]['monto']),
                        "folio" => ($folio)
                    );
                    array_push($arrInformacion, $arrDatos);

                }
            }
            
        }
        
        return $arrInformacion;
    }
}
?>