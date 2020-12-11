<?php
  
class Stockloctransferdao {
    
    private $pathprefix = "../.././";
    
    /**
     * 
     * @param JSONObject $params - Objeto json con los valores para procesar
     *      - loccodeold => Parametro con el valor del almacen origen
     *      - loccodenew => Parametro con el valor del almacen destino
     *      - products   => Parametro con un arreglo tipo json que incluya los datos del producto y cantidad
     */
    function setTranferReceive($params){
        require_once $this->pathprefix . 'core/ModeloBase.php';
        require_once $this->pathprefix . 'dao/Stockmasterdao.php';
        require_once $this->pathprefix . 'model/Stockmastershort.php';
        require_once $this->pathprefix . 'dao/Locationsdao.php';
        
        $modelo = new ModeloBase();
        
        //$origen= $params->loccodeold;
        $destino= $params->loccodenew;
        $cliente= $params->debtorno;
        $productos= $params->products;  // lista de productos
        $referenciasTraspaso = $params->referenciasTraspaso;
        
        // variables locales
        $success= true;
        $message= "";
        $msgerror = "";
        $typeerror = "";
        $codeerror = "";
        $tagref = 0;
        $legalid = 0;
        $costo= 0;
        $almacenorigen= array();
        $almacendestino= array();
        $fechaEntrega = date('Y-m-d');
        $transactiondate = date("d") . "/" .  date("m") . "/" . date("Y");
        $periodNo= 0;
        $origen= "";
        
        $sql = "SELECT tags.tagref, tags.legalid 
                FROM locations
                INNER JOIN tags ON locations.tagref = tags.tagref
                INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
                WHERE loccode = '".$destino."'";
        
        $resp = $modelo->ejecutarSql($sql);
        
        if ($resp and !is_array($resp)){
            $success = false;
            $message = _('No existen unidad de negocio y razon social para el almacen origen '.$destino);
        }else{
            if (!$resp){
                $msgerror = "ERROR EN LA CONSULTA";
                $typeerror = "MYSQL ERROR";
                $codeerror = "001";
            }else{
                for($xx = 0; $xx < count($resp); $xx++){
                    $tagref = $resp[$xx]['tagref'];
                    $legalid = $resp[$xx]['legalid'];
                }
            }
        }
        
        // determinar periodo contable
        //$periodNo = $modelo->getperiodnumber($transactiondate, $tagref);
        $conexion= $modelo->getLink();
        $periodNo= GetPeriod($transactiondate, $conexion, $tagref);

        
        // folio de la transferencia
        $reference = "";
        if (!empty($referenciasTraspaso)) {
            foreach ($referenciasTraspaso as $refTraspaso){
                $reference = $refTraspaso->reference;
            }
        }else{
            $reference = $modelo->getdocumentnumber(16); 
        }
        
        // recorrer listado de productos
        foreach ($productos as $producto){
            $origen= $producto->loccodeold; // almacen de donde se va a traspasar
            
            $sql = "SELECT avgcost FROM stockcostsxlegal WHERE legalid = '$legalid' AND stockid = '$producto->stockid'";
            $resp = $modelo->ejecutarSql ($sql);
           
            if ($resp and !is_array($resp)){
                $success = false;
                $message = _('No existe costo promedio para el producto '.$producto->stockid);
            }else{
                if (!$resp){
                    $msgerror = "ERROR EN LA CONSULTA";
                    $typeerror = "MYSQL ERROR";
                    $codeerror = "001";
                }else{
                    for($xx = 0; $xx < count($resp); $xx++){
                        $costo = $resp[$xx]['avgcost'];
                    }
                }
            }
            
            $transno = $modelo->getdocumentnumber(300); // folio del tipo de movimiento 300
            
            $stockmasterdao = new Stockmasterdao();
            $detalleproducto= new Stockmastershort();
            $Locationsdao= new Locationsdao();
            
            $UserID = $_SESSION["UserID"];
            
            $detalleproducto= $stockmasterdao->Getstockbyid($producto->stockid, $modelo);
            $almacenorigen= $Locationsdao->getLocationbyId($origen);
            $almacendestino= $Locationsdao->getLocationbyId($destino);
            
            $narrative = 'Traspaso automatico @ Producto: ' . $producto->stockid . ' ' . $producto->quantity . ' ' . $detalleproducto->getUnits() . ' - Origen: [' . $origen . '] ' . $almacenorigen["data"][0]["locationname"] . ' Destino: [' . $destino . ']' . $almacendestino["data"][0]["locationname"];
           
            // ******************* Moviemientos de Emision de Producto ******\\\\\\\\\\\\\\\\\\\\
            $sql = "INSERT INTO loctransfers (
                        reference,
                        stockid,
                        shipqty,
                        recqty,
                        shipdate,
                        recdate,
                        shiploc,
                        recloc,
                        comments,
                        userregister,
                        statustransfer,
                        debtorno
                    ) VALUES (
                        '$reference',
                        '$producto->stockid',
                        '$producto->quantity',
                        '$producto->quantity',
                        '$fechaEntrega',
                        '$fechaEntrega',
                        '$origen',
                        '$destino',
                        '$narrative',
                        '$UserID',
                        'Active',
                        '$cliente'
                    )";
            
            $modelo->ejecutarSql($sql);
        
            $narrative_stk = "Transferencia Automatica";

            // Sacar producto de Almacen Origen
            $sql = "INSERT INTO stockmoves (
                        type,
                        transno,
                        loccode,
                        stockid,
                        trandate,
                        reference,
                        qty,
                        standardcost,
                        narrative,
                        avgcost,
                        tagref,
                        prd
                    ) VALUES (
                        16,
                        '$transno',
                        '$origen',
                        '$producto->stockid',
                        '" . date("Y-m-d H:i:s") . "',
                        '$reference',
                        '-$producto->quantity',
                        '$costo',
                        '$narrative',
                        '$costo',
                        '$tagref',
                        '$periodNo'
                    )";

            $modelo->ejecutarSql($sql);

            // Restar la cantidad traspasada del disponible del almacen Origen
            $sql = "UPDATE locstock SET quantity = quantity - ".$producto->quantity." 
                    WHERE stockid = '$producto->stockid' AND loccode = '$origen'";

            $modelo->ejecutarSql($sql);

            // Agregar producto para Almacen Destino
            $sql = "INSERT INTO stockmoves (
                        type,
                        transno,
                        loccode,
                        stockid,
                        trandate,
                        reference,
                        qty,
                        standardcost,
                        narrative,
                        avgcost,
                        tagref,
                        prd
                    ) VALUES (
                        16,
                        '$transno',
                        '$destino',
                        '$producto->stockid',
                        '" . date("Y-m-d H:i:s") . "',
                        '$reference',
                        '$producto->quantity',
                        '$costo',
                        '$narrative',
                        '$costo',
                        '$tagref',
                        '$periodNo'
                        )";

            $modelo->ejecutarSql($sql);

            // Agregar la cantidad traspasada al disponible del Almacen Destino
            $sql = "UPDATE locstock 
                    SET quantity = quantity + ".$producto->quantity."
                    WHERE stockid = '$producto->stockid' AND loccode = '$destino'";

            $modelo->ejecutarSql($sql);

            $QuerySQL = "SELECT stockact,
                            adjglact,
                            purchpricevaract,
                            materialuseagevarac,
                            wipact,
                            adjglacttransf,
                            internaluse,
                            stockconsignmentact,
                            glcodebydelivery,
                            stockshipty,
                            accounttransfer
                        FROM stockmaster
                        INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
                        WHERE stockmaster.stockid = '" . $producto->stockid . "'";

            $resp= $modelo->ejecutarSql($QuerySQL);

            if ($resp and !is_array($resp)){
                $success = false;
                $message = _('No existe cuentas contables configuradas para el producto '.$producto->stockid);
            }else{
                if (!$resp){
                    $msgerror = "ERROR EN LA CONSULTA";
                    $typeerror = "MYSQL ERROR";
                    $codeerror = "001";
                }else{
                    for($xx = 0; $xx < count($resp); $xx++){
                        $cuentainventario = $resp[$xx]['stockact'];
                        $cuentatransfer= $resp[$xx]['accounttransfer'];
                    }
                }
            }

            $sql = "INSERT INTO gltrans
                        (type,
                        typeno,
                        trandate,
                        periodno,
                        account,
                        narrative,
                        amount,
                        tag,
                        stockid) 
                    VALUES (
                        '16',
                        '$transno',
                        '" . date("Y-m-d H:i:s") . "',
                        '$periodNo',
                        '$cuentainventario',
                        '$narrative',
                        (-$costo*$producto->quantity),
                        '$tagref',
                        '$producto->stockid'
                        )";

            $modelo->ejecutarSql($sql);

            $sql = "INSERT INTO gltrans
                        (type,
                        typeno,
                        trandate,
                         periodno,
                        account,
                        narrative,
                        amount,
                        tag,
                        stockid) 
                    VALUES (
                        '16',
                        '$transno',
                        '" . date("Y-m-d H:i:s") . "',
                        '$periodNo',
                        '$cuentatransfer',
                        '$narrative',
                        ($costo*$producto->quantity),
                        '$tagref',
                        '$producto->stockid'
                        )";

            $modelo->ejecutarSql($sql);
        }

        $response['success'] = $success;
        $response['error']['msgerror'] = $msgerror;
        $response['error']['typeerror'] = $typeerror;
        $response['error']['codeerror'] = $codeerror;
        $response['data']['message'] = $message;
        $response['data']['reference'] = $reference;

        return $response;
    }
    
}

