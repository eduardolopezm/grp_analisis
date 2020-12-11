/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
window.modelo="modelo/almacenModelo.php";
window.accion='';
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth() + 1; //January is 0!

var yyyy = today.getFullYear();
if (dd < 10) {
    dd = '0' + dd;
}
if (mm < 10) {
    mm = '0' + mm;
}
var today1 = dd + '-' + mm + '-' + yyyy;


$(document).ready(function() {

    $("#dateDesde").val("" + today1);
    $("#dateHasta").val("" + today1);
    var anio = new Date().getFullYear();
    var d = new Date();
    var n = d.getMonth();
    n += 1;

    for (i = 1; i <= 9; i++) {
        if (n == i) {
            n = "0" + i;
        }
    }

    mes = n;
    var today = new Date();
    var ultimodia = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    ultimodia = ultimodia.getDate();

    // $("#dateDesde").val('01-' + mes + '-' + anio);
    // $("#dateHasta").val(ultimodia+'-' + mes + '-' + anio);
    //fnFijarFecha();

    fnConsultar_Datos();
    fnObtenerBotones('divBotones');

    $('#imprimirFormatoSolicitud').click(function() {
        muestraCargandoGeneral();
        var griddata = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getdatainformation');
        var ur = '';
        var fecha = '';
        var almacen = ''; //$("#selectAlmacen option:selected").text();
        var dependencia = $('#selectRazonSocial option:selected').text();
        var salida = '';
        var nu_folio = '';
        var idsolicitud = '';
        var datosreporte = [];

        for (var i = 0; i < griddata.rowscount; i++) {
            id = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'id1');
            if (id == true) {
                idsolicitud = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'idsolicitudsinliga');
                ur = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'tag');
                fecha = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'fecha');
                //idsolicitud = $('<div>').append(idsolicitud).find('u:first').html();
            }
        }

        datosreporte = fnDatosReporte(idsolicitud);
        //alert(idsolicitud);
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        console.log(datosreporte[0]);
        if (!datosreporte[0].includes("No existen")) {


            datos = '';
            datos = "almacenImprimirSolicitud.php?PrintPDF=1&solicitud=" + idsolicitud + "&nu_folio=" + salida + "&ur=" + ur + "&fechasolicitud=" + fecha + "&almacen=" + almacen + "&dependencia=" + dependencia + "&todos=1&usuariosolicitud=" + datosreporte[1];

            ocultaCargandoGeneral();
            muestraModalGeneral(4, titulo, '<object data="' + datos + '" width="100%" height="350px" type="application/pdf"><embed src="' + datos + '" type="application/pdf" />     </object>');
            //$("#viewSolicitud").html('<object data="'+datos+'" width="60%" height="800px" type="application/pdf"><embed src="'+datos+'" type="application/pdf" />     </object>');
        } else {

            ocultaCargandoGeneral();
            muestraModalGeneral(4, titulo, datosreporte[0]);

        }
    });

    $('#nuevaSolicitud').click(function() {
        window.open("solicitudAlmacen.php", "_self");
    });

    $('#filtrar').click(function() {
        fnConsultar_Datos();
    });

 $("#confirmacionModalGeneral1").click(function() {
    nombreEstatus=$("#estatusText").val();
    estatus=$("#estatusVal").val();
    $("#ModalGeneral1").modal('hide');

        var mensaje = '';
        var estatusM = '';
        var solicitudes = [];
        var enviarSoli = [];
        var datos = [];
        var objeto = new Object();
        // muestraCargandoGeneral();

        datosSel = fnChecarSeleccionados();
        solicitudes = datosSel[0];

        if (solicitudes.length > 0) {
            datos = fnChecarDisponibleAntesDeAvanzar(0, solicitudes, datos);

            for (a = 0; a < solicitudes.length; a++) {
                if (datos[a].mensaje == '') {
                    estatusM += fnMuestraMensajeAvanzar(nombreEstatus, solicitudes[a],$("#estatusBtn").val());
                    enviarSoli.push(solicitudes[a]);
                } else {
                    mensaje = datos[a].mensaje;
                    estatusM += "<div style='max-height: 400px; overflow-y: scroll;'><br><i class='glyphicon glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> El folio " + solicitudes[a] + " " + 'No tiene disponible en lo siguiente:' + datos[a].mensaje+"</div>";
                }
            }

            if (enviarSoli.length > 0) {
                dataObj = {
                    proceso: 'avanzar',
                    solicitudes: enviarSoli,
                    estatus: estatus,
                    async: false,
                    cache: false,
                    nombreEstatus: nombreEstatus
                };

                $.ajax({
                        method: "POST",
                        dataType: "json",
                        url: modelo,
                        data: dataObj
                    })
                    .done(function(data) {
                        if (data.result) {
                            //fnEliminarFilas();
                            ocultaCargandoGeneral();
                            fnConsultar_Datos();

                            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                            muestraModalGeneral(4, titulo, estatusM);
                        } else {
                            ocultaCargandoGeneral();
                        }
                    })
                    .fail(function(result) {
                        console.log("ERROR");
                        console.log(result);
                        ocultaCargandoGeneral();
                    });
            } else if (mensaje != '') {
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                
                muestraModalGeneral(4, titulo, estatusM);
                ocultaCargandoGeneral();
            } else {
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(4, titulo, 'Seleccioné  solo solicitudes manuales  que no esten en almacén,cancelada o avanzada.');
                ocultaCargandoGeneral();
            }
            ocultaCargandoGeneral();
            //}else{
            //   fnAvanzarAux(estatus, nombreEstatus);
            // 
        } else {

            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo, 'Seleccioné  solo solicitudes manuales  que no esten en almacén,cancelada o avanzada.');
        }

    });

});

$(document).on('cellbeginedit', '#datosAlmacen', function(event) {

    $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'idsolicitud', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'tag', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'numeroart', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'cantidad', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'estatus', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'observaciones', 'editable', false);

});

//no funciona con la nueva version del grid donde sale  el logo de
$(document).on('cellselect', '#tablaAlmacen > #datosAlmacen', function(event) {
    solicitudEnlace = event.args.datafield;

    if (solicitudEnlace == 'idsolicitud') {
        fila = event.args.rowindex;
        enlace = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', fila, 'idsolicitud');

        //alert(enlace);
        //enlace1=jQuery(enlace).find('a').attr('href');
        if ($('#ligasolicitud' + fila).length > 0) {
            href = $('<div>').append(enlace).find('u:first').html();
            numeroestatus = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', fila, 'numeroestatus');
            //para acceder al enlace
            //var href = $('<div>').append(enlace).find('a:first').attr('href');

            fnCargaDetalle(href, numeroestatus);
        }
    }
});

$(document).on('click', '.escondeDetalle', function() {
    id = $(this).attr('id');
    //alert(id);
    id = id.replace("menos", "");

    //alert('#extra'+id);
    $('#extra' + id).toggle();
});

function fnDetallePedido(solicitud, filaTabla) {
    if ($('#extra' + filaTabla).length > 0) {
        $('#extra' + filaTabla).toggle();
    } else {
        dataObj = {
            proceso: 'detalleSolicitud',
            solicitud: solicitud,
            ur: $('#selectUnidadNegocio').val()
        };
        muestraCargandoGeneral();

        $.ajax({
                method: "POST",
                dataType: "json",
                url: modelo,
                data: dataObj
            })
            .done(function(data) {

                if (data.result) {
                    var html = '';
                    // alert('dentro clave');
                    detalle = data.contenido.detalle;

                    if (!detalle.includes("No hay")) {


                        html += '<tr  id="extra' + filaTabla + '"> <td colspan="3">';
                        html += '<div class="escondeDetalle" id="menos' + filaTabla + '"> <span class="glyphicon glyphicon-minus" style="font-size:14px" > Detalle</span><br> </div> ';
                        html += '<table class="bgc8 table table-striped table-bordered ">';
                        html += '<thead><th>Clave artículo</th>' +
                            '<th>Artículo</th>' +
                            '<th>CAMS</th>' +
                            '<th>Partida</th>' +
                            '<th>Cantidad</th>' +
                            '</thead><tbody>';


                        for (i in detalle) {
                            html += '<tr>';

                            html += '<td>' + detalle[i].clave + '</td>';
                            html += '<td>' + detalle[i].descripcion + '</td>';
                            html += '<td>' + detalle[i].cams + '</td>';
                            html += '<td>' + detalle[i].partida + '</td>';
                            html += '<td>' + detalle[i].cantidad + '</td>';
                            html += '</tr>';
                        }

                        html += '</tbody></table></td></tr>';

                        //$('#almacensolicitudes > tbody > tr').eq(i-1).after('<tr>prueba </tr>');
                        //
                        //$('#almacensolicitudes').addTableRowAfter(0, '<tr><td> 00001</td><td>John</td ></tr>');
                        $('#almacensolicitudes #fila' + filaTabla + ':last').after(html);
                    } else {
                        $('#almacensolicitudes #fila' + filaTabla + ':last').after('<tr id="extra' + filaTabla + '"><td colspan="3"><h4 style="text-align:center">' + detalle + '</h4></td></tr>');
                    }

                    ocultaCargandoGeneral();
                } else {
                    ocultaCargandoGeneral();
                }

            })
            .fail(function(result) {
                console.log("ERROR");
                console.log(result);
                ocultaCargandoGeneral();
            });
    }
}

function fnAgregarFilaGrid() {
    //alert();
    var row = {};
    //$("#tablaAlmacen > #datosAlmacen").jqxGrid('addrow', null, []);
    //$("#tablaAlmacen > #datosAlmacen").jqxGrid('addrow', null,row, 'top');
    ad = $("#tablaAlmacen > #datosAlmacen").jqxGrid('selectedrowindex');
    alert(ad);
    $(ad).append('<div>hola</div>');

}

// para ir porder ir la tabla del grid al desgloce de la solicitud
function fnCargaDetalle(solicitud, estatus) {
    //alert($('#selectUnidadNegocio').val());
    //$('<form method="post" action="solicitud_almacen.php"> <input type="hidden" name="detallesolicitud" value="'+solicitud+'"></form>').submit();
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "solicitudAlmacen.php");
    form.setAttribute("target", "_self");

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "detallesolicitud");
    hiddenField.setAttribute("value", solicitud);
    form.appendChild(hiddenField);

    var hiddenField1 = document.createElement("input");
    hiddenField1.setAttribute("type", "hidden");
    hiddenField1.setAttribute("name", "estatus");
    hiddenField1.setAttribute("value", estatus);
    form.appendChild(hiddenField1);

    var hiddenField2 = document.createElement("input");
    hiddenField2.setAttribute("type", "hidden");
    hiddenField2.setAttribute("name", "ur");
    hiddenField2.setAttribute("value", $('#selectUnidadNegocio').val());
    form.appendChild(hiddenField2);

    document.body.appendChild(form);
    //window.open('', '_self');
    form.submit();
    //window.open(,"_self");
}

function fnDatosReporte(solicitud) { //carga datos a los select
    var retorno = [];
    dataObj = {
        proceso: 'datosReporte',
        solicitud: solicitud
    };
    $.ajax({
            method: "POST",
            dataType: "json",
            url: modelo,
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                if (!data.contenido.datos[0].fecha.includes("No existen")) {
                    retorno.push(data.contenido.datos[0].fecha);
                    retorno.push(data.contenido.datos[0].usuario);
                } else {
                    retorno.push(data.contenido.datos[0].fecha);
                }

            } else {
                // ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });

    return retorno;
}

function fnObtenerBotones(divMostrar) {
    //Opcion para operacion
    dataObj = {
        proceso: 'obtenerBotones',
        type: ''
    };

    $.ajax({
            async: false,
            cache: false,
            method: "POST",
            dataType: "json",
            url: modelo,
            data: dataObj
        })
        .done(function(data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion
                info = data.contenido.datos;
                //console.log("presupuesto: "+JSON.stringify(info));
                nombreEstatus = '';
                var contenido = '';

                for (var key in info) {
                    var funciones = '';
                    /*if (info[key].statusid == 24) { //guardar cap
                     funciones = 'fnGuardarDatosSolicitud('+info[key].statusnext+')';
                    }else*/
                    console.log("botones" + info[key].statusid);
                    if (info[key].statusid == 27) { //autorizador val
                        funciones = 'fnAvanzar(' + info[key].statusnext + ',\'' + info[key].statusname + '\',\''+ info[key].namebutton+ '\')';
                    } else if (info[key].statusid == 30) { //avanzar aut
                        funciones = 'fnAvanzar(' + info[key].statusnext + ',\'' + info[key].statusname + '\',\''+ info[key].namebutton + '\')';
                    } else if (info[key].statusid == 31) { //rechazar
                        funciones = 'fnAvanzar(' + info[key].statusnext + ',\'' + info[key].statusname + '\',\''+ info[key].namebutton + '\')';
                    }
                    /*else if (info[key].statusid == 33) { //surtir pedido almacenista
                                        //fnNodejarVacios();
                                        funciones="fnGuardarSalida('<h3>¿Desea surtir los artículos?</h3>')";
                                       

                                    }else if (info[key].statusid == 34) { //cerrar peido almacenista
                                    funciones="fnGuardarSalida('<h3>¿Desea cerrar las solicitud?</h3>')";

                                    }*/
                    else if (info[key].statusid == 0) { //cancelar pedido almacenista // 35
                        funciones = 'fnAvanzar(' + info[key].statusnext + ',\'' + info[key].statusname + '\',\''+ info[key].namebutton + '\')';
                    }
                    /*else if (info[key].statusid == 36) { //imprimir
                                    
                                              // sol=$('#idDetalleSolicitud').val();
                                               //funciones ='fnImprimir(\''+sol+'\')';
                                    }*/

                    if (info[key].statusid == 33) { //cuando sea surtir
                        if (info[key].statusid != 24 && (info[key].statusid != 33) && (info[key].statusid != 34) && (info[key].statusid != 36)) {
                            contenido += '&nbsp;&nbsp;&nbsp; \
                        <button type="button" id="' + info[key].namebutton + '" name="' + info[key].namebutton + '" onclick="' + funciones + '" class="ptb3 btn btn-primary ' + info[key].clases + '"> ' + info[key].namebutton + '</button>';
                        }
                    } else {
                        if (info[key].statusid != 24 && (info[key].statusid != 33) && (info[key].statusid != 34) && (info[key].statusid != 36)) {
                            contenido += '&nbsp;&nbsp;&nbsp; \
                        <button type="button" id="' + info[key].namebutton + '" name="' + info[key].namebutton + '" onclick="' + funciones + '" class="btn btn-default botonVerde ' + info[key].clases + '"> ' + info[key].namebutton + '</button>';
                        }
                    }
                }
                $('#' + divMostrar).append(contenido);
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
}

function fnMuestraMensajeAvanzar(nombreEstatus, folio, btn) {
    var mensaje = '';

    if (nombreEstatus.includes('Cancelada')) {
        mensaje = "<br> <i class='glyphicon glyphicon glyphicon-ok text-success' aria-hidden='true'></i> Se ha cancelado la solicitud al almacén " + folio;
    } else if (btn=='Avanzar') {
        nombreEstatus = nombreEstatus.replace("Avanzada al", "");
        mensaje = "<br> <i class='glyphicon glyphicon glyphicon-ok text-success' aria-hidden='true'></i>Se avanzó la solicitud al almacén " + folio ;
    } else if (btn=='Rechazar') {
        nombreEstatus = nombreEstatus.replace("Rechazada por", "");
        perfil = fnPerfilAnterior(nombreEstatus);
        mensaje = "<br><i class='glyphicon glyphicon glyphicon-ok text-success' aria-hidden='true'></i>Se ha rechazado la solicitud al almacén " + folio ;
    } else if (nombreEstatus.includes('En almacén')) {
        mensaje = "<br><i class='glyphicon glyphicon glyphicon-ok text-success' aria-hidden='true'></i> La solicitud al almacén " + folio + " ha sido autorizada y enviada al Almacén";
    } else if (nombreEstatus.includes('Autoriza')) {
        mensaje = "<br><i class='glyphicon glyphicon glyphicon-ok text-success' aria-hidden='true'></i> La solicitud al almacén " + folio + " ha sido autorizada y enviada al Almacén";
    }else if (nombreEstatus.includes('Capturada')) {
        mensaje = "<br><i class='glyphicon glyphicon glyphicon-ok text-success' aria-hidden='true'></i> Se avanzó la solicitud al almacén " + folio ;
    }

    return mensaje;
}

function fnBoton(nombreEstatus, btn) {
    var mensaje = '';
    if(btn!="Rechazar"){
        if (nombreEstatus.includes('Cancelada')) {
            mensaje = "<b>cancelar</b>";
        } else if (nombreEstatus.includes('Por')) {
            mensaje = '<b>avanzar</b>';
        } else if (nombreEstatus.includes('Rechazada')) {
            mensaje = '<b>rechazar</b>';
        } else if (nombreEstatus.includes('En almacén')) {
            mensaje = '<b>autorizar</b>';
        } else if (nombreEstatus.includes('Autoriza')) {
            mensaje = '<b>autorizar</b>';
        }
    }else{
        mensaje = '<b>rechazar</b>';
    }

    return mensaje;
}

function fnPerfilAnterior(perfil) {
    var perfilAnterior = '';
    switch (perfil) {

        case ' capturista':
            perfilAnterior = '';
            break;

        case ' validador':
            perfilAnterior = ' capturista'
            break;

        case ' autorizador':
            perfilAnterior = ' validador';
            break;
    }

    return perfilAnterior;
}

function fnAvanzar(estatus, nombreEstatus, nombreBtn) {
    accion = fnBoton(nombreEstatus, nombreBtn);
    $("#ModalGeneral1").modal('show');
    $("#ModalGeneral1_Mensaje").empty();
    $("#ModalGeneral1_Mensaje").append('¿Está seguro de ' + accion + ' las  Solicitudes al almacén seleccionadas?');
    $("#estatusText").val(nombreEstatus);
    $("#estatusVal").val(estatus);
    $("#estatusBtn").val(nombreBtn);
}

function fnAvanzarAux(estatus, nombreEstatus) {
    var estatusM = '';
    datosSel = fnChecarSeleccionados();
    solicitudes = datosSel[0];
    console.log(estatus + " avanzaraux");
    for (a = 0; a < solicitudes.length; a++) {

        estatusM += fnMuestraMensajeAvanzar(nombreEstatus, solicitudes[a],$("#estatusBtn").val());


    }

    dataObj = {
        proceso: 'avanzar',
        solicitudes: solicitudes,
        estatus: estatus,
        async: false,
        cache: false,
        nombreEstatus: nombreEstatus
    };

    $.ajax({
            method: "POST",
            dataType: "json",
            url: modelo,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                //fnEliminarFilas();
                ocultaCargandoGeneral();
                fnConsultar_Datos();

                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(4, titulo, estatusM);
            } else {
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });

}

function fnChecarDisponibleAntesDeAvanzar(contador = 0, solicitudes = [], datos = []) {
    var mensaje = '';
    if (contador < solicitudes.length) {
        dataObj = {
            proceso: 'checarDisponibleAntesDeAvanzar',
            solicitud: solicitudes[contador]
        };

        $.ajax({
                method: "POST",
                dataType: "json",
                url: modelo,
                async: false,
                cache: false,
                data: dataObj
            })
            .done(function(data) {
                if (data.result) {
                    for (a in data.contenido.articulos) {
                        if (data.contenido.articulos[a].disponible == 'NO DISPONIBLE') {
                            mensaje += "<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> En el renglon " + data.contenido.articulos[a].renglon + " con clave "+data.contenido.articulos[a].descripcion+"<b>" + data.contenido.articulos[a].clave + "</b> ";
                        }
                    }

                    //console.log(contador);
                    //console.log("folio:"+ solicitudes[contador], "mensaje:"+ mensaje);
                    datos.push({
                        "folio": solicitudes[contador],
                        "mensaje": mensaje
                    });
                }

                fnChecarDisponibleAntesDeAvanzar(contador + 1, solicitudes, datos);
            })
            .fail(function(result) {
                console.log("ERROR");
                console.log(result);
                ocultaCargandoGeneral();
            });
    }

    return datos;
}

function fnTraerSeleccionados(id, numeroestatus) {
    var solicitudes = [];


    return solicitudes;
}

function fnChecarSeleccionados() {
    var griddata = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getdatainformation');
    var j = 0;
    var solicitudes = [];
    var valor = 0;
    var tipoSoli = '';
    var retorno = [];

    for (var i = 0; i < griddata.rowscount; i++) {
        id = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'id1');
        numeroestatus = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'numeroestatus');

        /*if (visible == 1) {
                  
                                      
                  }*/
        if (ed1 == 1 && (numeroestatus < 41)) { //40

            if (id == true && numeroestatus != 0 && numeroestatus != 45 && numeroestatus != 47 && numeroestatus != 30) {
                valor = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'idsolicitudsinliga');
                tipoSoli = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'tipoSol');
                console.log(valor + " s" + numeroestatus);
                //console.log();
                if (tipoSoli == 'Manual') {
                    solicitudes.push(valor);
                }

                //j++;
            }
        }
        if (ed2 == 1 && (numeroestatus < 43)) { //40
            if (id == true && numeroestatus != 0 && numeroestatus != 45 && numeroestatus != 47 && numeroestatus != 30) {
                valor = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'idsolicitudsinliga');
                tipoSoli = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'tipoSol');
                console.log(valor + " s" + numeroestatus);
                //console.log();
                if (tipoSoli == 'Manual') {
                    solicitudes.push(valor);

                }

                //j++;
            }
        }
        //40
        if (ed3 == 1) {
            if (id == true && numeroestatus != 0 && numeroestatus != 45 && numeroestatus != 47 && numeroestatus != 30) {
                valor = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'idsolicitudsinliga');
                tipoSoli = $('#tablaAlmacen > #datosAlmacen').jqxGrid('getcellvalue', i, 'tipoSol');
                console.log(valor + " s" + numeroestatus);
                //console.log();
                if (tipoSoli == 'Manual') {
                    solicitudes.push(valor);
                }else if(tipoSoli == 'Automática'){
                    solicitudes.push(valor);
                }

                //j++;
            }

        }

        console.log("p" + ed1 + " " + ed2 + " " + ed3);

    }
    /* para solo permitir una
    if (j == 0) {
        solicitud = -2;
    } else if (j == 1) {
        solicitud = valor;
    } else {
        solicitud = -1;
    } */

    //alert(solicitud);
    retorno.push(solicitudes);
    retorno.push(tipoSoli);
    console.log(retorno,"checarseleccionados");
    return retorno;
}

function fnConsultar_Datos() {
    muestraCargandoGeneral();

    var dateDesde = $('#dateDesde').val();
    var dateHasta = $('#dateHasta').val();
    var dependencia = $("#selectRazonSocial").val();
    var unidad_resp = $("#selectUnidadNegocio").val();
    var unidad_eje = $("#selectUnidadEjecutora").val();
    var solicitud = $("#txtNumeroRequisicion").val();
    
    var tipoSol=$("#tipoSol").val();
    var estatus=$("#estatusSel").val();

    dataObj = {
        proceso: 'filtrado',
        dependencia: dependencia,
        unidadresp: unidad_resp,
        unidadeje: unidad_eje,
        solicitud: solicitud,
        estatus: estatus,
        dateDesde: dateDesde,
        dateHasta: dateHasta,
        tipoSol:tipoSol
    };

    $.ajax({
            async: false,
            cache: false,
            method: "POST",
            dataType: "json",
            url: modelo,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {

                fnLimpiarTabla('tablaAlmacen', 'datosAlmacen');
                //fnAgregarGrid_Detalle(data.contenido.datos, data.contenido.columnasNombres, data.contenido.columnasNombresGrid, 'datosAlmacen', ' ', 1, columnasExcel, false,true, '', columnasVisuales, nombreExcel);
                // Columnas para el GRID
                var colRtotal = ", aggregates: [{'<b>Total</b>' :" +
                    "function (aggregatedValue, currentValue) {" +
                    "var total = currentValue;" +
                    "return aggregatedValue + total;" +
                    "}" +
                    "}] ";

                columnasNombres = '';
                columnasNombres += "[";
                columnasNombres += "{ name: 'id1', type: 'bool'},";
                columnasNombres += "{ name: 'tag', type: 'string' },";
                columnasNombres += "{ name: 'ue', type: 'string' },";
                columnasNombres += "{ name: 'idsolicitud', type: 'string' },";
                columnasNombres += "{ name: 'idsolicitudsinliga',type:'string'},";
                columnasNombres += "{ name: 'fecha', type: 'string' },";
                columnasNombres += "{ name: 'numeroart', type: 'number' },";
                columnasNombres += "{ name: 'cantidad', type: 'number' },";
                columnasNombres += "{ name: 'estatus', type: 'string' },";
                columnasNombres += "{ name: 'tipoSol', type: 'string' },";

                columnasNombres += "{ name: 'observaciones', type: 'string' },";
                columnasNombres += "{ name: 'numeroestatus', type: 'number' }";

                columnasNombres += "]";
                //Columnas para el GRID
                columnasNombresGrid = '';
                columnasNombresGrid += "[";
                columnasNombresGrid += " { text: '', datafield: 'id1', width: '4%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
                columnasNombresGrid += " { text: 'UR', datafield: 'tag', width: '9%', cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: 'UE', datafield: 'ue', width: '9%', cellsalign: 'center', align: 'center', hidden: false, editable:false },";
                columnasNombresGrid += " { text: 'Folio',datafield: 'idsolicitud', width: '6%', align: 'center',hidden: false,cellsalign: 'center' },";
                columnasNombresGrid += " { text: 'Folio',datafield: 'idsolicitudsinliga', width: '1%', align: 'center',hidden: true,cellsalign: 'center' },";
                columnasNombresGrid += " { text: 'Fecha', datafield: 'fecha', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: 'Art. Sol. ', datafield: 'numeroart', width: '6%', cellsalign: 'center', align: 'center', hidden: false " + colRtotal + "},";
                columnasNombresGrid += " { text: 'Total Sol. ', datafield: 'cantidad', width: '7%', cellsalign: 'center', align: 'center', hidden: false " + colRtotal + "},";
                columnasNombresGrid += " { text: 'Estatus', datafield: 'estatus', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: 'Tipo Solicitud', datafield: 'tipoSol', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";

                columnasNombresGrid += " { text: 'Observaciones', datafield: 'observaciones', width: '24%', cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: 'numero', datafield: 'numeroestatus', width: '1%', cellsalign: 'center', align: 'center', hidden: true },";

                //columnasNombresGrid += " { text: 'Total', datafield: 'cantidad', width: '19%', cellsalign: 'right', align: 'center',cellsformat: 'C2', hidden:false"+colRtotal+"}";
                columnasNombresGrid += "]";

                var columnasExcel = [1,2, 4, 5, 6, 7, 8, 9,10];
                var columnasVisuales = [0, 1, 2,3, 5, 6, 7, 8, 9,10];
                nombreExcel = data.contenido.nombreExcel;

                fnAgregarGrid_Detalle(data.contenido.datos, columnasNombres, columnasNombresGrid, 'datosAlmacen', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
                //if(esconde==1){
                // $("#tablaAlmacen > #datosAlmacen").jqxGrid('setcolumnproperty','id1','hidden',true);
                //$('#tablaAlmacen > #datosAlmacen').jqxGrid({width:'85%'});
                // $("#tablaAlmacen > #datosAlmacen").jqxGrid('setcolumnproperty','estatus','width','20%');
                // }
                var rowscount = $("#tablaAlmacen > #datosAlmacen").jqxGrid('getdatainformation').rowscount;
                //$('#tablaAlmacen > #datosAlmacen').jqxGrid({ pagesizeoptions: ['50', '100', rowscount]});
                // pagesize: 10,
                //$('#tablaAlmacen > #datosAlmacen').jqxGrid({ pagesize: 10});

                ocultaCargandoGeneral();

            } else {
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });
}

$(document).on('click', '#btnCerrarModalGeneral', function() {
    ocultaCargandoGeneral();
    $('div').removeClass("modal-backdrop");

});