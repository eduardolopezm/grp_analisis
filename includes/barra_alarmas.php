<?php
    echo '<table border="0" cellpadding="0" cellspacing="0">';
    echo '<tr>';
    
    /****************************************************************************************************/
    /* ALARMA DE NUMERO DE REGISTROS EN CUSTALLOCNS DE ORIGEN DE APLICACION SIN REGISTRO EN DEBTORTRANS */
    
    $sql = "select COALESCE(count(*),0) as aplicacionSinOrigen 
            from custallocns LEFT JOIN debtortrans ON custallocns.transid_allocfrom = debtortrans.id
            where debtortrans.id is null";
                              
    $RSIndicador = DB_query($sql, $db);
    if ($ResultIndicador = DB_fetch_array($RSIndicador)) {
       echo '<td class="main_menu_alarmas">';
       echo "XCALLOCFROM:";
       echo "<a href=". $rootpath ."/detalleIndicador.php?". SID ." title='Numero de Registros de aplicaciones de pagos del documento origen que no tienen registro en tabla de transacciones de clientes'>";
       echo $ResultIndicador['aplicacionSinOrigen'];
       echo "</a>";
       echo '</td>';
    }
    /* FIN *** ALARMA DE NUMERO DE REGISTROS EN CUSTALLOCNS DE ORIGEN DE APLICACION SIN REGISTRO EN DEBTORTRANS */
    /************************************************************************************************************/
    
    
    /****************************************************************************************************/
    /* ALARMA DE NUMERO DE REGISTROS EN CUSTALLOCNS DE DESTINO DE APLICACION SIN REGISTRO EN DEBTORTRANS */
    $sql = "select COALESCE(count(*),0) as aplicacionSinOrigen 
            from custallocns LEFT JOIN debtortrans ON custallocns.transid_allocto = debtortrans.id
            where debtortrans.id is null";
                              
    $RSIndicador = DB_query($sql, $db);
    if ($ResultIndicador = DB_fetch_array($RSIndicador)) {
       echo '<td class="main_menu_alarmas">';
       echo "XCALLOCTO:";
       echo "<a href=". $rootpath ."/detalleIndicador.php?". SID ." title='Numero de Registros de aplicaciones de pagos del documento destino que no tienen registro en tabla de transacciones de clientes'>";
       echo $ResultIndicador['aplicacionSinOrigen'];
       echo "</a>";
       echo '</td>';
    }
    /* FIN *** ALARMA DE NUMERO DE REGISTROS EN CUSTALLOCNS DE DESTINO DE APLICACION SIN REGISTRO EN DEBTORTRANS */
    /************************************************************************************************************/
    
    /*************************************************************************************************************/
    /* ALARMA DE DESCUADRE DE TRANSACCIONES DE CLIENTES CON SALDO APLICADO SIN REGISTRO EN TABLA DE APLICACIONES */
    $sql = "select COALESCE(count(*),0) as transSinAplicacion 
            from debtortrans LEFT JOIN custallocns ON debtortrans.id = custallocns.transid_allocfrom
            where debtortrans.type in (11,12,13,60,80,420,430,450,460) and
                            debtortrans.alloc > 0 and
                            custallocns.transid_allocfrom is null";
                              
    $RSIndicador = DB_query($sql, $db);
    if ($ResultIndicador = DB_fetch_array($RSIndicador)) {
       echo '<td class="main_menu_alarmas">';
       echo "XDOCNOAPPL:";
       echo "<a href=". $rootpath ."/detalleIndicador.php?". SID ." title='TRANSACCIONES DE CLIENTES CON SALDO APLICADO SIN REGISTRO EN TABLA DE APLICACIONES !'>";
       echo $ResultIndicador['transSinAplicacion'];
       echo "</a>";
       echo '</td>';
    }
    /* FIN ** ALARMA DE DESCUADRE DE TRANSACCIONES DE CLIENTES CON SALDO APLICADO SIN REGISTRO EN TABLA DE APLICACIONES */
    /********************************************************************************************************************/
    
    /*************************************************************************************************************/
    /* ALARMA DE DESCUADRE DE TRANSACCIONES DE CLIENTES CON SALDO APLICADO SIN REGISTRO EN TABLA DE APLICACIONES */
    $sql = "select COALESCE(count(*),0) as transSinAplicacion 
            from debtortrans LEFT JOIN custallocns ON debtortrans.id = custallocns.transid_allocto
            where debtortrans.type in (10,110,70,21,400,410,440) and
                            debtortrans.alloc > 0 and
                            custallocns.transid_allocfrom is null";
                              
    $RSIndicador = DB_query($sql, $db);
    if ($ResultIndicador = DB_fetch_array($RSIndicador)) {
       echo '<td class="main_menu_alarmas">';
       echo "XDOCSINAPPL:";
       echo "<a href=". $rootpath ."/detalleIndicador.php?". SID ." title='TRANSACCIONES DE CLIENTES CON SALDO APLICADO SIN REGISTRO EN TABLA DE APLICACIONES !'>";
       echo $ResultIndicador['transSinAplicacion'];
       echo "</a>";
       echo '</td>';
    }
    /* FIN ** ALARMA DE DESCUADRE DE TRANSACCIONES DE CLIENTES CON SALDO APLICADO SIN REGISTRO EN TABLA DE APLICACIONES */
    /********************************************************************************************************************/
    
    echo '</tr>';
    echo '</table>';
?>