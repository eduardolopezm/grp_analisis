//soliticitud arturo lopez peña
/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña
 * @version 0.1
 */
function fnCargaVariables() {
    window.cuentaFilas = 1;
    window.nombreTabla = "tablaArticulosSolicitud";
    window.tabla = $('#' + nombreTabla);
    window.body;
    window.tbody = $(tabla.find('tbody'));
    window.modelo = "modelo/almacenModelo.php";
    window.partidas = new Array();
    window.renglon = 1;
    window.titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    //window.disponiblesElementos=["numero","clave","cantidadSolicitada"];
    window.elementos = ["numero", "partida", "clave", "descripArt", "um", "cantidadSolicitada"];


}

function fnAgregarFila(fila,flagInit=0) {

    var eliminar;
    var clase = '';
    //var elementos = ["btnAgregar","numero","partida","clave","descripArt","um","cantidadSolicitada"];
    eliminar = generateItem('div', {id: ''}, generateItem('div', { class: 'text-center pt15'}, generateItem('span', {class: 'btn btn-danger btn-xs glyphicon glyphicon-remove filaQuitar',title: 'eliminar',id: "btnEliminar" + cuentaFilas,type: "button"})));
    //renglon =  generateItem('div',{id: ''}, generateItem('div',{class: 'text-center'}, generateItem('span',{class:'numeroFila'}, generateItem('input',{class:'renglon', type:'hidden'})))) ;
    var elementos = {

        "numero": {
            tag: 'span',
            opts: {
                class: 'numeroFila'
            }
        },
        "partida": {
            tag: 'select',
            opts: {
                class: 'form-control partida',
                change: cambioSel
            }
        },
        "clave": {
            tag: 'select',
            opts: {
                class: 'form-control clave',
                change: cambioSel
            }
        },
        "descripArt": {
            tag: 'select',
            opts: {
                class: 'form-control desp',
                change: cambioSel
            }
        },
        "um": {
            tag: 'input',
            opts: {
                class: 'form-control text-center um',
                type: 'text',
                val: '',
                readonly: true
            }
        },
        "cantidadSolicitada": {
            tag: 'input',
            opts: {
                type: 'text',
                onpaste: 'return false',
                class: 'form-control soloNumeros',
                onkeypress: 'return soloNumeros(event)',
                min: 0,
                max: 10
            }
        },
        "selEscondida": {
            tag: 'select',
            opts: {
                class: 'selEsc hide'
            }
        },

    };


    var cols = [];
    var fila;

    cols.push(eliminar);
    //cols.push(renglon);

    $.each(elementos, function(i, v) {

        var clase = '';
        var n = '';
        opts = v.opts;
        opts.id = i + cuentaFilas;
        opts.name = i;
        tag = v.tag
        contenido = generateItem(tag, opts);
        //if(tag=='select'){ selects.push(index); }	
        cols.push(generateItem('td', {
            style: 'max-width:300px'
        }, contenido));
    });

    tr = generateItem('tr', {
        class: 'text-center w100p',
        id: 'fila' + cuentaFilas
    }, cols);
    window.tbody.append(tr);
    fnActualizarRenglon(nombreTabla);

    //options=fnCrearSelect(partidas);

    fnFormatoSelect("#partida" + cuentaFilas, partidas);
    $('#selEscondida' + cuentaFilas).parent('td').css({
        "display": "none"
    });
    cuentaFilas++;
    if(flagInit==0){
       rowCount = $('#' + nombreTabla + ' >tbody >tr').length;
        if(rowCount>0){
            $('#selectUnidadEjecutora').multiselect('disable');
            $('#selectAlmacen').multiselect('disable');
            $('#selectUnidadNegocio').multiselect('disable');
        }else{
           $('#selectUnidadEjecutora').multiselect('enable');
            $('#selectAlmacen').multiselect('enable');
            $('#selectUnidadNegocio').multiselect('enable');
        }
 
    }
    

}

function fnAgregarFilaInputs(fila, almacenista = 0) {

    var eliminar;
    var clase = '';
    var elementos = {};
    if (almacenista == 0) {


        elementos = {

            "numero": {
                tag: 'span',
                opts: {
                    class: 'numeroFila'
                }
            },
            "partida": {
                tag: 'input',
                opts: {
                    class: 'form-control partida text-center',
                    type: 'text',
                    val: '',
                    readonly: true
                }
            },
            "clave": {
                tag: 'input',
                opts: {
                    class: 'form-control clave',
                    type: 'text',
                    val: '',
                    readonly: true
                }
            },
            "descripArt": {
                tag: 'input',
                opts: {
                    class: 'form-control desp',
                    type: 'text',
                    val: '',
                    readonly: true
                }
            },
            "um": {
                tag: 'input',
                opts: {
                    class: 'form-control text-center um',
                    type: 'text',
                    val: '',
                    readonly: true
                }
            },
            "cantidadSolicitada": {
                tag: 'input',
                opts: {
                    type: 'text',
                    onpaste: 'return false',
                    class: 'form-control text-right soloNumeros',
                    val: '',
                    readonly: true
                }
            },


        };
    } else {

        elementos = {

            "numero": {
                tag: 'span',
                opts: {
                    class: 'numeroFila'
                }
            },
            "partida": {
                tag: 'input',
                opts: {
                    class: 'form-control partida text-center',
                    type: 'text',
                    val: '',
                    readonly: true
                }
            },
            "clave": {
                tag: 'input',
                opts: {
                    class: 'form-control clave',
                    type: 'text',
                    val: '',
                    readonly: true
                }
            },
            "descripArt": {
                tag: 'input',
                opts: {
                    class: 'form-control desp',
                    type: 'text',
                    val: '',
                    readonly: true
                }
            },
            "um": {
                tag: 'input',
                opts: {
                    class: 'form-control text-center um',
                    type: 'text',
                    val: '',
                    readonly: true
                }
            },
            "cantidadSolicitada": {
                tag: 'input',
                opts: {
                    type: 'text',
                    onpaste: 'return false',
                    class: 'form-control text-right',
                    val: '',
                    readonly: true
                }
            },
            "cantidadDisponible": {
                tag: 'input',
                opts: {
                    type: 'text',
                    onpaste: 'return false',
                    class: 'form-control text-right',
                    val: '',
                    readonly: true
                }
            },
            "cantidadEntregada": {
                tag: 'input',
                opts: {
                    type: 'text',
                    onpaste: 'return false',
                    class: 'form-control text-right',
                    val: '',
                    readonly: true
                }
            },
            "cantidadFaltante": {
                tag: 'input',
                opts: {
                    type: 'text',
                    onpaste: 'return false',
                    class: 'form-control text-right faltante',
                    val: '',
                    readonly: true
                }
            },
            "cantidadAentregar": {
                tag: 'input',
                opts: {
                    type: 'text',
                    onpaste: 'return false',
                    class: 'form-control cantidadEntregaAlmacen text-right',
                    val: '',
                    onkeypress: 'return soloNumeros(event)'
                }
            },

        };
    }




    var cols = [];
    var fila;

    $.each(elementos, function(i, v) {

        var clase = '';
        var n = '';
        opts = v.opts;
        opts.id = i + cuentaFilas;
        opts.name = i;
        tag = v.tag
        contenido = generateItem(tag, opts);


        cols.push(generateItem('td', {
            style: 'max-width:300px'
        }, contenido));


    });

    tr = generateItem('tr', {
        class: 'text-center w100p',
        id: 'fila' + cuentaFilas
    }, cols);
    window.tbody.append(tr);

    cuentaFilas++;
    // rowCount = $('#' + nombreTabla + ' >tbody >tr').length;
    // if(rowCount>0){
    //     $('.selectUnidadEjecutora').multiselect('disable');
    //     $('.selectAlmacen').multiselect('disable');
    //     $('.selectUnidadNegocio').multiselect('disable');
    // }

}

function fnActualizarRenglon(tablaSel) {
    renglon = 1;
    $("#" + tablaSel + " tbody tr").each(function() {
        fila = $(this).find(".numeroFila").empty();
        fila.html("" + renglon);
        //fila=$(this).find(".renglon").val(""+cuentaFilas);
        renglon++;
    });
    
    if(renglon==1){
        $('#selectUnidadEjecutora').multiselect('enable');
        $('#selectAlmacen').multiselect('enable');
        
    }
}

function getPartida() {
    var partidas = new Array();
    dataObj = {
        proceso: 'getPartida'
    };
    $.ajax({
            method: "POST",
            dataType: "json",
            url: modelo,
            data: dataObj,
            async: false
        })
        .done(function(data) {
            if (data.result) {
                partidas = data.contenido.partidas;

            } else {
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error getPartida");
            ocultaCargandoGeneral();

        });
    return partidas;

}

function fnCrearSelect(datos) {

    optionsLista = [{
        label: ' Seleccionar...',
        title: ' Seleccionar...',
        value: '-1'
    }];
    $.each(datos, function(index, val) {
        optionsLista.push({
            label: val.texto,
            title: val.texto,
            value: val.value
        });
    });

    return optionsLista;

}

function fnCrearSelectDetalle(val, texto) {
    optionsLista = [];
    //optionsLista=[{label:' Seleccionar...', title: ' Seleccionar...', value:'-1'}];
    optionsLista.push({
        label: val,
        title: texto,
        value: val
    });

    return optionsLista;

}

function fnFormatoSelect(fila, options) {
    $(fila).multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });
    $(fila).multiselect('dataprovider', options);

    $('.multiselect-container').css({
        'max-height': "220px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });
    //$(fila).trigger('change');
}

function cambioPartida(proceso, seleccionado) {
    var clave = [];
    var des = [];
    var units = [];
    var rt = [];
    dataObj = {
        proceso: proceso, //'cambioPartida',
        dato: seleccionado
    };
    $.ajax({
            method: "POST",
            dataType: "json",
            url: modelo,
            data: dataObj,
            async: false,
            cache: false
        })
        .done(function(data) {
            if (data.result) {
                clave = data.contenido.retorno[0];
                des = data.contenido.retorno[1];
                units = data.contenido.retorno[2];

            } else {
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error al " + proceso + " .");
            ocultaCargandoGeneral();


        });
    rt.push(clave);
    rt.push(des);
    rt.push(units);

    return tr;

}

function cambioSel() {

    var selects = ["partida", "clave", "descripArt"];
    var id = $(this).attr('id');
    n = id;

    var sel = '';
    $.each(selects, function(i, v) {

        rgxp = new RegExp(v, "g");
        c = id.match(rgxp);
        if (c != null) {
            sel = c[0];
        }

    });
    n = n.replace(sel, "");
    proceso = 'cambio' + sel;
    var seleccionado = $(this).val();

    if (seleccionado != -1) {
        muestraCargandoGeneral();

        setTimeout(function() {

            switch (sel) {

                case 'partida':
                    var clave = [];
                    var des = [];
                    var units = [];

                    dataObj = {
                        proceso: proceso, //'cambioPartida',
                        dato: seleccionado
                    };
                    $.ajax({
                            method: "POST",
                            dataType: "json",
                            url: modelo,
                            data: dataObj,
                            async: false,
                            cache: false
                        })
                        .done(function(data) {
                            if (data.result) {
                                clave = data.contenido.retorno[0];
                                des = data.contenido.retorno[1];
                                units = data.contenido.retorno[2];

                            } else {
                                ocultaCargandoGeneral();
                            }
                        })
                        .fail(function(result) {
                            console.log("Error al " + proceso + " .");
                            ocultaCargandoGeneral();
                        });


                    //options=fnCrearSelect(clave);
                    fnFormatoSelect("#clave" + n, clave);

                    //options=fnCrearSelect(des);
                    fnFormatoSelect("#descripArt" + n, des);

                    datos = fnCrearSelectNormal(units);

                    $('#selEscondida' + n).empty();
                    $('#selEscondida' + n).append(datos);

                    $('#selEscondida' + n).prev().css("class", "hide");


                    break;

                case 'clave':
                    index = -1;
                    $('#clave' + n + ' option').each(function(i, v) {
                        if (this.selected) index = i;

                    });
                    existe = fnVerificarNoexista(seleccionado, sel);
                    if (existe) {
                        muestraModalGeneral(4, titulo, 'Ya se seleccionó la clave ' + seleccionado);
                        //$("#fila"+n).remove();

                    } else {

                        $('#descripArt' + n).selectpicker('val', seleccionado);
                        $('#descripArt' + n).multiselect('refresh');
                        $('.desp').hide();
                        $('#selEscondida' + n).val(seleccionado);
                        $('#um' + n).val($('#selEscondida' + n + ' option:selected').text());
                    }
                    break;

                case 'descripArt':
                    existe = fnVerificarNoexista(seleccionado, sel);
                    if (existe) {
                        muestraModalGeneral(4, titulo, 'Ya se seleccionó el artículo ' + seleccionado);
                        $("#fila" + n).remove();

                    } else {
                        $('#clave' + n).selectpicker('val', seleccionado);
                        $('#clave' + n).multiselect('refresh');
                        $('.clave').hide();
                        $('#selEscondida' + n).val(seleccionado);
                        $('#um' + n).val($('#selEscondida' + n + ' option:selected').text());

                    }


                    break;

            }



            retorno = [];
            ocultaCargandoGeneral();

        }, 100);
    } else {
        ocultaCargandoGeneral();
        setTimeout(function() {
            muestraModalGeneral(4, titulo, 'No puede dejar sin selección el renglon');
        }, 50);
    }

}

function cambioClave() {
    var sel = $(this).val();

    existe = fnVerificarNoexista(sel, "clave");
    if (existe) {
        muestraModalGeneral(4, titulo, '<h4>Ya existe la partida.</h4>');
        $(this).attr('id');

    }

}

function fnVerificarNoexista(val, donde) {
    var valor = '',
        count = 0,
        flag = false;
    $("#" + nombreTabla + " tbody tr").each(function() {
        fila = $(this).attr('id');
        fila = fila.replace("fila", "");

        valor = $("#" + donde + fila).val();
        if (valor == val) {
            count++;
        }

    });
    if (count > 1) {
        flag = true;
    }

    return flag;
}

function fnVerificarSeleccion() {
    var valor = '',
        count = 0,
        flag = false,
        msg = '';
    $("#" + nombreTabla + " tbody tr").each(function() {
        fila = $(this).attr('id');
        fila = fila.replace("fila", "");


        partidaVal = $("#partida" + fila).val();
        claveVal = $("#clave" + fila).val();
        desVal = $("#descripArt" + fila).val();

        if ((partidaVal == -1) || (claveVal == -1) || (desVal == -1)) {
            count++;
            msg += "<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> En  el renglón " + $("#numero" + fila).html() + " no tiene Partida y/o artículo seleccionado.";

        }

        valor = $("#cantidadSolicitada" + fila).val();
        //
        if ((valor == 0) || (valor == '')) {
            count++;
            msg += "<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>" + ' El renglón ' + $("#numero" + fila).html() + ' no tiene cantidad capturada y/o cantidades en cero';
        }

    });
    if (count >= 1) {
        flag = true;
        muestraModalGeneral(4, titulo, msg);
    } else {
        flag = false;
    }

    return flag;
}

function fnChecarSalidas() {
    var valor = '',
        count = 0,
        flag = false,
        msg = '';
    $("#" + nombreTabla + " tbody tr").each(function() {
        fila = $(this).attr('id');
        fila = fila.replace("fila", "");


        valor = $("#cantidadAentregar" + fila).val();
        if (valor == '') {
            count++;
            msg += "<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>" + ' El renglón ' + $("#numero" + fila).html() + ' no tiene cantidad capturada';
        }

    });
    if (count >= 1) {
        flag = true;
        muestraModalGeneral(4, titulo, msg);
    } else {
        flag = false;
    }

    return flag;
}

function fnValidarCantidadEntrega() {

    var valor = '',
        count = 0,
        flag = false,
        msg = '';
    $("#" + nombreTabla + " tbody tr").each(function() {
        fila = $(this).attr('id');
        fila = fila.replace("fila", "");

        valor = $("#cantidadAentregar" + fila).val();
        //
        if ((valor == 0) || (valor == '') || (isNaN(valor))) {
            count++;
            msg += "<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i>" + ' El renglón ' + $("#numero" + fila).html() + ' no tiene cantidad de entrega captura y/o cantidades en cero';
        }

    });
    if (count >= 1) {
        flag = true;
        muestraModalGeneral(4, titulo, msg);
    } else {
        flag = false;
    }

    return flag;

}

function getDatos(tablaSel, elementos, selectText = 0) {
    datosSend = new Array();
    datosSend2 = new Array();

    existeFaltantes = 0;
    var filas = {};
    var renglones = [];
    var valor;
    var nombre = '';


    $("#" + tablaSel + " tbody tr").each(function() {
        fila = $(this).attr('id');
        fila = fila.replace("fila", "");
        $.each(elementos, function(index, v) {

            if (index == 0) {
                //valor=	$("#"+fila).find("#"+v).html();
                valor = $("#" + v + fila).html();
                //filas[v]=valor; //assoativo
            } else if (index == 3) {
                if (selectText == 0) {
                    valor = $("#" + v + fila + " option:selected").text();
                } else {
                    valor = $("#" + v + fila).val();
                }

            } else {
                //valor=	$("#"+fila).find("#"+v).val();
                valor = $("#" + v + fila).val();
                //filas[v]=valor; ////assoativo
            }
            renglones.push(valor);


        });
        // tipo de entrega por artículo
        if (selectText != 0) {
            faltan = $('#cantidadFaltante' + fila).val();
            if (faltan > 0) {
                renglones.push('parcial');
                existeFaltantes++;
            } else {
                renglones.push('total');
            }
        }

        datosSend.push(renglones);
        //filas=[];//assoativo
        renglones = [];
    });
    //console.log(datosSend);
    // tipo de salida
    if (selectText != 0) {
        tipoEntrega = '';

        if (existeFaltantes > 0) {
            tipoEntrega = 'parcial';
        } else {
            tipoEntrega = 'total';
        }
        datosSend2.push(datosSend);
        datosSend2.push(tipoEntrega);

        return datosSend2;

    } else {
        return datosSend;
    }

}

function fnCrearSelectNormal(datos) {

    var lista = '';
    $.each(datos, function(i, v) {
        lista += '<option  value="' + v.value + '">' + v.texto + '</option>';
    });
    return lista;
}

//quitar fila
$(document).on('click', '.filaQuitar', function() {

    var btn = $(this).attr('id');
    var id = btn.replace("btnEliminar", "fila");
    $("#" + id).remove();
    fnActualizarRenglon(nombreTabla);
});

function fnEnviarDatos(datos, disponible = false) {

    var dataObj = {};
    var mensaje = '';
    var proceso = '';
    if ($(".folius").length) {
        proceso = 'modificarDatos';
        dataObj['folio'] = $(".folius").html();
    } else {
        proceso = 'guardarDatos';
    }
    muestraCargandoGeneral();
    setTimeout(function() {

        dataObj['proceso'] = proceso; //'cambioPartida',
        dataObj['datos'] = datos;
        dataObj['disponible'] = disponible;
        dataObj['almacen'] = $('#selectAlmacen').val();
        dataObj['tag'] = $('#selectUnidadNegocio').val();
        dataObj['obs'] = $('#txtAreaObs').val();
        dataObj['ue'] = $('#selectUnidadEjecutora').val();

        $.ajax({
                method: "POST",
                dataType: "json",
                url: modelo,
                data: dataObj,
                async: false,
                cache: false
            })
            .done(function(data) {
                if (data.result) {
                    ocultaCargandoGeneral();
                    mensaje = data.contenido.mensaje;
                    if (mensaje != '') {

                        muestraModalGeneral(4, titulo, data.contenido.confirmacion + "<div style='max-height: 400px; overflow-y: scroll;'>" + mensaje + "</div>");
                    } else {

                        muestraModalGeneral(4, titulo, data.contenido.confirmacion);
                    }
                    if (!$(".folius").length) {
                        $("#numeroFolio").empty();
                        $("#numeroFolio").append('Folio:<span class="folius">' + data.contenido.folio + '</span>');
                    }


                } else {
                    ocultaCargandoGeneral();
                }
            })
            .fail(function(result) {
                console.log("Error al " + proceso + " .");
                ocultaCargandoGeneral();
            });
    }, 100);

}
// function fnEnviarDatos (form,action) {

//     // $("#"+form).on("submit" ,function(event){

//         event.preventDefault();
//         var formData = new FormData(document.getElementById(form));
//         //formData.append("dato", "valor");
//         console.log(formData);
//         $.ajax({
//             url: "modelo/almacen_modelo.php",
//             type: "post",
//             dataType: "json",
//             data: formData,
//             cache: false,
//             contentType: false,
//             processData: false
//         })
//             .done(function(data){
//                 if(data.result){
//                     console.log(data.content);
//                 }else{
//                     console.log(data.errorMsg);
//                 }


//             });


//     // });
// }


$(document).on("keyup", '.cantidadEntregaAlmacen', function(event) {

    //$(this).val($(this).val().replace(/[^\d].+/, ""));
    $(this).val($(this).val().replace(/[^0-9\.]/g, ''));
    /*if ((event.which < 48 || event.which > 57)) {
        event.preventDefault();
    }*/
    //  $(this).val($(this).val().replace(/[^\d].+/, ""));
    /*if ((event.which < 48 || event.which > 57)) {
        event.preventDefault();
    }*/
    //id de entrega

    // cantidadentregados
    var cantidad1 = 0;
    var limite = 0;
    var disponible1 = 0;
    var entrega = 0;

    numero1 = $(this).attr('id');
    numero1 = numero1.replace("cantidadAentregar", "");

    entrega = $(this).val();
    cantidad1 = $('#cantidadSolicitada' + numero1).val();
    disponible1 = $("#cantidadDisponible" + numero1).val();


    if ($("#cantidadEntregada" + numero1).val() > 0) {

        entregados = $("#cantidadEntregada" + numero1).val();
        narticuclos = $('#cantidadSolicitada' + numero1).val();
        //disponible1=$("#faltaparcial"+numero1).val();
        narticuclos = parseInt(narticuclos);
        entregados = parseInt(entregados);
        limite = narticuclos - entregados;

        if (limite > disponible1) {
            cantidad1 = disponible1;
        } else {
            cantidad1 = limite;
        }


    } else { // 

        limite = disponible1;
    }

    //alert(cantidad1);
    cantidad1 = parseInt(cantidad1);
    limite = parseInt(limite);
    disponible1 = parseInt(disponible1);
    entrega = parseInt(entrega);

    if (cantidad1 > disponible1) {

        /*if ($( ".cantidadentregados" ).length) {
          
         alert(limite);
        }else{
            
        }*/

        if (entrega > limite) {

            /*if(limite>disponible1){
                 $(this).val(disponible1);
                 $("#faltan"+numero1).val((disponible1));
            }else{ */


            $(this).val(limite);
            $("#cantidadFaltante" + numero1).val((cantidad1 - limite));
            // }

        } else {
            $("#cantidadFaltante" + numero1).val((cantidad1 - entrega));
        }

    } else {

        if (entrega > cantidad1) {
            $(this).val(cantidad1);
            $("#cantidadFaltante" + numero1).val("0");
        } else {
            $("#cantidadFaltante" + numero1).val((cantidad1 - entrega));
        }

    }

    if (!$(this).val()) {
        var nan = false;
        nan = isNaN($("#cantidadFaltante" + numero1).val());
        if (nan == true) {

            cant = $("#cantidadSolicitada" + numero1).val();
            entr = $("#cantidadEntregada" + numero1).val();
            v = parseInt(cant) - parseInt(entr);

            $("#cantidadFaltante" + numero1).val(v);

        }
    }


});

function fnObtenerBotones(divMostrar) {
    dataObj = {
        proceso: 'obtenerBotones',
        type: ''
    };
    $.ajax({
            async: false,
            cache: false,
            method: "POST",
            dataType: "json",
            url: "modelo/almacenModelo.php",
            data: dataObj
        })
        .done(function(data) {

            if (data.result) {

                info = data.contenido.datos;

                nombreEstatus = '';
                var contenido = '';
                for (var key in info) {
                    var funciones = '';


                    /*if (info[key].statusid == 24) { //guardar cap
                     funciones = 'fnGuardarDatosSolicitud('+info[key].statusnext+')';
                    }else*/
                    if (info[key].statusid == 33) { //surtir pedido almacenista
                        //fnNodejarVacios();
                        //funciones = "fnGuardarSalida('<h6>¿Desea surtir los artículos?</h6>')";


                    } else if (info[key].statusid == 34) { //cerrar peido almacenista
                        funciones = "fnGuardarSalida('<h6>¿Desea cerrar las solicitud?</h6>',0,1)";

                    } else if (info[key].statusid == 36) { //imprimir

                        sol = $('#idDetalleSolicitud').val();
                        funciones = 'fnImprimir(\'' + sol + '\')';
                    }

                    if (info[key].statusid == 37) { //cuando sea surtir
                        contenido += '&nbsp;&nbsp;&nbsp; \
                <button style="display:none;"></button>';

                    }

                    if (info[key].statusid == 33) { //cuando sea surtir
                        contenido += '&nbsp;&nbsp;&nbsp; \
                <button type="button" id="' + info[key].namebutton + '" name="' + info[key].namebutton + '" onclick="' + funciones + '" class="ptb3 btn btn-primary ' + info[key].clases + '"> ' + info[key].namebutton + '</button>';

                    } else {
                        contenido += '&nbsp;&nbsp;&nbsp; \
                <button type="button" id="' + info[key].namebutton + '" name="' + info[key].namebutton + '" onclick="' + funciones + '" class="btn btn-default botonVerde ' + info[key].clases + '"> ' + info[key].namebutton + '</button>';

                    }
                }
                $('#' + divMostrar).append(contenido);

            }
        })
        .fail(function(result) {
            console.log("Error  al  obtener  botones");
            //console.log(ErrMsg);
        });
}

function fnSalidaAlmacen() {
    ele = ["numero", "partida", "clave", "descripArt", "um", "cantidadSolicitada", "cantidadFaltante", "cantidadAentregar"];
    datos = getDatos(nombreTabla, ele, 1);
    console.log(datos);
    dataObj = {
        proceso: 'salidas',
        ur: $("#selectUnidadNegocio").val(),
        ue: $("#selectUnidadEjecutora").val(),
        datos: datos[0],
        tipoEntrega: datos[1],
        idsolicitud: $(".folius").html(),
        almacen: $("#selectAlmacen").val(),


    };

    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacenModelo.php",
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                $articulos = data.contenido.articulos;
                $salidas = data.contenido.salidas;
                console.log();
                if ($salidas.length > 0) {

                    for (ad in $articulos) {

                        $("#" + nombreTabla + " tbody tr").each(function() {
                            fila = $(this).find(".numeroFila");
                            var id = $(this).attr('id');
                            id = id.replace("fila", "");

                            clave = "'" + $("#clave" + id).val() + "'";


                            if (clave == $articulos[ad]) {
                                val = $("#cantidadEntregada" + id).val();
                                /// dependiendo del tipo de unidad mas. adelante se ttendria que cambiar el. cast
                                entregados = parseInt(val) + parseInt($salidas[ad]);
                                $("#cantidadEntregada" + id).val(entregados)
                                $("#cantidadAentregar" + id).val("");
                            }
                        });
                    }
                }

                muestraModalGeneral(4, titulo, data.contenido.msj);

            } else {
                ocultaCargandoGeneral();
                muestraModalGeneral(4, titulo, 'No se hizo la salida al almacén intente mas tarde');
            }
        })
        .fail(function(result) {
            console.log("Error al guardad salida");
            ocultaCargandoGeneral();
            muestraModalGeneral(4, titulo, 'No se hizo la salida al almacén intente mas tarde');
        });
}

function fnGuardarSalida1(mensaje, confirmacion = 0, cancelar = 0) {

    idsolicitud = $('#idDetalleSolicitud').val();
    estatusNumero = $('#numeroestatus').val();
    //fnCargaDetalleDeNuevo(idsolicitud);
    if (confirmacion == 1) {

        // folio=fnSalidaFolio();

        $("#Surtir").prop('disabled', true);
        if (!fnValidarCantidadEntrega()) {

            var data = '';
            var datosSalida;
            var detalle;
            var datos;
            var articulos = '';
            var cantidades = '';
            var ur = $('#selectUnidadNegocio').val();
            var almacen = $('#selectAlmacen').val();

            if ($(".cantidadentregados").length > 0) { // quiere decir que es parcial

                detalle = detalleSolicitud;


                for (i in detalle) {
                    //if(detalle[i].estatus=="parcial"){// solo guarda lo parcial
                    faltan = (detalle[i].cantidad - detalle[i].cantidadentregada);
                    if (faltan > 0) {
                        entrega = $("#entrega" + i).val();
                        faltan = $("#faltan" + i).val();
                        if (entrega > 0) {
                            data += "('" + idsolicitud + "','" + detalle[i].cantidad + "','" + entrega + "','" + faltan + "','" + detalle[i].clave + "','" + detalle[i].descripcion + "','" + detalle[i].cams + "','" + detalle[i].partida + "','" + tipoEntregaPorArticulo[i] + "','" + folio + "','" + detalle[i].unidad_medida + "','" + detalle[i].renglon + "'),";
                            articulos += "'" + detalle[i].clave + "',";
                            cantidades += "'" + entrega + "',";
                        }
                    }

                }



            } else { //si no es entrega parcial
                detalle = detalleSolicitud;

                for (i in detalle) {
                    entrega = $("#entrega" + i).val();
                    faltan = $("#faltan" + i).val();
                    data += "('" + idsolicitud + "','" + detalle[i].cantidad + "','" + entrega + "','" + faltan + "','" + detalle[i].clave + "','" + detalle[i].descripcion + "','" + detalle[i].cams + "','" + detalle[i].partida + "','" + tipoEntregaPorArticulo[i] + "','" + folio + "','" + detalle[i].unidad_medida + "','" + detalle[i].renglon + "'),";
                    articulos += "'" + detalle[i].clave + "',";
                    cantidades += "'" + entrega + "',";
                }
            }
            datosSalida = data.slice(0, -1);
            articulos = articulos.slice(0, -1);
            cantidades = cantidades.slice(0, -1);
            //articulos+=')';
            //alert(articulos);
            dataObj = {
                proceso: 'salidas',
                datos: datosSalida,
                estatus: datos[0], //tipo entrega solicitud
                idsolicitud: idsolicitud,
                articulos: articulos,
                cantidades: cantidades,
                ur: ur,
                folio: folio,
                almacen: almacen,
                cerrar: cancelar
            };

            $.ajax({
                    method: "POST",
                    dataType: "json",
                    url: "modelo/almacenModelo.php",
                    data: dataObj
                })
                .done(function(data) {
                    if (data.result) {
                        // alert("hoal");
                        // 
                        ocultaCargandoGeneral();


                        // fnCargaDetalleDeNuevo(idsolicitud, estatusNumero,1);



                        ocultaCargandoGeneral();
                    } else {
                        ocultaCargandoGeneral();
                    }
                })
                .fail(function(result) {
                    console.log("Error al guardad salida");
                    //console.log(ErrMsg);
                    ocultaCargandoGeneral();
                });

        } // si las validaciones pasaron
        if (cancelar == 1) {
            $("#Surtir").hide();
            $("#Cerrar").hide();
        }
        $("#Surtir").prop('disabled', false);
    } else {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnGuardarSalida(\'' + mensaje + '\',\'' + 1 + '\')');
        ocultaCargandoGeneral();
        if (cancelar == 0) {
            muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnGuardarSalida(\'' + mensaje + '\',\'' + 1 + '\')');
            ocultaCargandoGeneral();
        } else if (cancelar == 1) {
            muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnGuardarSalida(\'' + mensaje + '\',\'' + 1 + '\',\'' + 1 + '\')');
            ocultaCargandoGeneral();
        }
    }
}

function fnImprimir(idsolicitud) {
    var salidas;
    dataObj = {
        proceso: 'existeSalida',
        idsolicitud: idsolicitud
    };
    //muestraCargandoGeneral();
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                ocultaCargandoGeneral();
                salidas = data.contenido.salidas;
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';

                if (!salidas.includes('No existen')) {

                    //muestraModalGeneral(4, titulo,'<object data="'+datos+'" width="100%" height="450px" type="application/pdf"><embed src="'+datos+'" type="application/pdf" />     </object>');
                    muestraModalGeneral(4, titulo, '<div id="tablaSalidas"> <div id="datosSalidas"> </div> </div><div id="mostrarImpresion"> </div>');

                    fnTablaSalidas(salidas, data.contenido.nombreExcel);
                    ocultaCargandoGeneral();
                } else {

                    muestraModalGeneral(4, titulo, salidas);
                    ocultaCargandoGeneral();

                }
                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error al imprimir 1360");
            //console.log(ErrMsg);
            ocultaCargandoGeneral();

        });

    //fnImprimirSalida(51,idsolicitud);
    ocultaCargandoGeneral();

}

function fnImprimirSalida(salida, idsolicitud) {
    // impresion

    datos = '';
    //idsolicitud
    dependencia = $('#selectRazonSocial option:selected').text(); //$('#selectRazonSocial').val();
    ur = $("#selectUnidadNegocio option:selected").text(); //$('#selectUnidadNegocio').val();
    almacen = $("#selectAlmacen option:selected").text(); //$("#selectAlmacen").val();

    datosreporte = fnDatosReporte(idsolicitud);
    datos = "almacenImprimirSolicitud.php?PrintPDF=1&solicitud=" + idsolicitud + "&nu_folio=" + salida + "&ur=" + ur + "&fechasolicitud=" + datosreporte[0] + "&almacen=" + almacen + "&dependencia=" + dependencia + "&todos=0&usuariosolicitud=" + datosreporte[1] + "&usuarioentrega=" + usuarioEntrega;

    $("#tablaSalidas").hide();
    $("#mostrarImpresion").empty();
    $("#mostrarImpresion").append('<div class="text-center"><button id="regresaTablaSalidas" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;"> Regresar</button></div><object data="' + datos + '" width="100%" height="450px" type="application/pdf"><embed src="' + datos + '" type="application/pdf" />     </object>');

}

function fnTablaSalidas(salidas, nombreExcel) {
    fnLimpiarTabla('tablaSalidas', 'datosSalidas');

    columnasNombres = '';
    columnasNombres += "[";
    //columnasNombres += "{ name: 'id1', type: 'bool'},";
    columnasNombres += "{ name: 'tag', type: 'string' },";
    columnasNombres += "{ name: 'idsolicitud', type: 'string' },";
    columnasNombres += "{ name: 'idsolicitudsinliga',type:'string'},";
    columnasNombres += "{ name: 'fecha', type: 'string' },";

    columnasNombres += "]";
    // Columnas para el GRID
    columnasNombresGrid = '';
    columnasNombresGrid += "[";
    //columnasNombresGrid += " { text: '', datafield: 'id1', width: '5%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
    columnasNombresGrid += " { text: 'UR', datafield: 'tag', width: '40%', cellsalign: 'center', align: 'center', hidden: false },";
    columnasNombresGrid += " { text: 'Salida',datafield: 'idsolicitud', width: '20%', align: 'center',hidden: false,cellsalign: 'center' },";
    columnasNombresGrid += " { text: 'Folio',datafield: 'idsolicitudsinliga', width: '40%', align: 'center',hidden: true,cellsalign: 'center' },";
    columnasNombresGrid += " { text: 'Fecha', datafield: 'fecha', width: '40%', cellsalign: 'center', align: 'center', hidden: false },";

    columnasNombresGrid += "]";

    var columnasExcel = [0, 2, 3];
    var columnasVisuales = [0, 1, 3];


    fnAgregarGrid_Detalle(salidas, columnasNombres, columnasNombresGrid, 'datosSalidas', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
    // $('#tablaSalidas > #datosSalidas').jqxGrid({width:'70%'});
}

$(document).on('cellbeginedit', '#datosSalidas', function(event) {

    $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'idsolicitud', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'tag', 'editable', false);

});

function fnDatosReporte(solicitud) {
    var retorno = [];
    dataObj = {
        proceso: 'datosReporte',
        solicitud: solicitud
    };
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {

                retorno.push(data.contenido.datos[0].fecha); // fecha solicitud
                retorno.push(data.contenido.datos[0].usuario); //usuario solicitud
            } else {
                // ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error al generar  datos reporte 2067");
            //console.log(ErrMsg);
            ocultaCargandoGeneral();
        });

    return retorno;

}

$(document).ready(function() {
    if (visible == 1) {
        fnObtenerBotones('divBotones');
    }
    fnCargaVariables();
    partidas = getPartida();

    $("#btnAgregarFila").click(function() {
        if ($('#selectUnidadEjecutora option:selected').val() != -1) {
        fnAgregarFila(window.cuentaFilas);
        }
        else {
            muestraModalGeneral(4, titulo, '<h5>Seleccione UE.</h5>');
        }

    });

    $('#home1').click(function() {
        window.open("almacen.php", "_self");
    });
    $('#home2').click(function() {
        window.open("almacen.php", "_self");
    });

    $("#btnGuardar").click(function() {
        rowCount = $('#' + nombreTabla + ' >tbody >tr').length;
        //console.log(rowCount);
        if (rowCount > 0) {

            flagS = fnVerificarSeleccion();
            if (!flagS) {

                if ($('#selectUnidadEjecutora option:selected').val() != -1) {
                    datos = getDatos(nombreTabla, elementos); // validar disponible
                    fnEnviarDatos(datos);
                } else {
                    muestraModalGeneral(4, titulo, '<h5>Seleccione UE.</h5>');
                }

            }


        } else {
            muestraModalGeneral(4, titulo, 'No puede guardar no ha agregado productos.');
        } // fin verificacion de columnas

    });

    $("#Surtir").click(function() {
        flag = fnChecarSalidas();
        if (!flag) {
            $("#ModalGeneral1").modal('show');
            $("#ModalGeneral1_Mensaje").empty();
            $("#ModalGeneral1_Mensaje").append('¿Está seguro de surtir la solicitud <b>' + $(".folius").html() + "</b> ?");
            $("#confirmacionModalGeneral1").prop('onclick', null);


        }

    });
    $("#confirmacionModalGeneral1").click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        fnSalidaAlmacen();
        $("#ModalGeneral1").modal('hide');

    });


});

$(document).on('keypress', '.soloNumeros', function(event) {

        valor = $(this).val();
        valorantes = 0;
        max = $(this).attr('max');

        $(this).val($(this).val().replace(/[^0-9\.]/g, ''));
        valorantes = valor;

        if (valor.length > max) {

            $(this).val(valorantes);

            event.preventDefault();
            $(this).val((valor.slice(0, -1))); // quita el ultimo  numero para que no se bloquee

            return false;
        }

        if ((valor) < 0) {
            $(this).val(0);
        }

});