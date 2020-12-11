//establecer bloqueo de pantalla
// $(document).ajaxStart(muestraCargandoGeneral()).ajaxStop(ocultaCargandoGeneral());
// 
window.PruebaArticulos={};
//$( document ).ajaxStart(muestraCargandoGeneral);
// $( document ).ajaxStart(function() {
//     //console.log('comprobacion de bloqueo');
//     //muestraCargandoGeneral();
// });

//$( document ).ajaxStop(ocultaCargandoGeneral);
$( document ).ajaxStop(function() {
    //console.log('comprobacion de dispercion de bloqueo');
    ocultaCargandoGeneral();
});

$(document).ready(function (){
  
  /**
   * Terminar sesion por tiempo de inactividad
   */
  $(document).idleTimeout({
    //redireccionar:
    //redirectUrl: 'Logout.php'
    //redirectUrl: false para desabilitar el redireccionamiento
    redirectUrl: 'Logout.php',
    //establecer tiempo de inactividad despues de n segundos (1500 = 25 Minutes)
    idleTimeLimit: 1500,
    //establecer frecuencia para reiniciar tiempo de inactividad (2 segundos)
    idleCheckHeartbeat: 2,
    // set to false for no customCallback
    customCallback: false,
    //separar cada evento por un espacio
    activityEvents: 'click keypress scroll wheel mousewheel mousemove', 
    //Configuracion del mensaje de advertencia
    //enableDialog: true //habilita mensaje
    //enableDialog: false //deshabilita mensaje
    enableDialog: true,
    //tiempo que permancera el mensaje antes de redireccionar la pagina
    dialogDisplayLimit: 30,
    //mostrar en la barra de titulo
    dialogTitle: 'Alerta de Fin de Sesión ', 
    dialogText: 'Tu sesi&oacute;n esta por expirar, debido a un gran tiempo de inactividad. ¿Qu&eacute; deseas hacer?',
    dialogTimeRemaining: 'Tiempo restante',
    dialogStayLoggedInButton: 'Permanecer',
    dialogLogOutNowButton: 'Salir ahora',
    //mensaje
    errorAlertMessage: 'Tal vez tu navegador no soporte la funcionalidad de esta p&aacute;gina, verifica tu versi&oacute;n o comunicate con tu &aacute;rea de soporte.',
    // server-side session keep-alive timer
    // ping the server at this interval in seconds. 600 = 10 Minutes. Set to false to disable pings
    sessionKeepAliveTimer: 600,
     // set URL to ping - does not apply if sessionKeepAliveTimer: false
    sessionKeepAliveUrl: window.location.href
    });

    $(document).on('focus', ':input', function(){
        $(this).attr('autocomplete', 'off');
    });

    // Operaciones para campos de listado, sólo se ejecutan cuando existe el objeto de configuración para listados, el objeto puede tener estas propiedades:
    // Propiedades obligatorias:
    //      origenDatos (propiedad obligatoria, que indica en qué variable están los valores del listado)
    // Propiedades opcionales:
    //      cuentasAprobadas (un arreglo que indica qué cuentas contables pueden mostrarse en el listado, si no se declara se muestran todas las cuentas)
    //      arregloEstatico (variable booleana que indica si el listado se autoactualiza o no, si no se declara el listado se toma como un campo autoactualizable)
    //      valoresAlfanumericos (variable booleana que indica si el campo recibe valores alfanuméricos, si no se declara el campo de búsqueda sólo recibirá números y puntos)
    //      valorAMostrar (variable de texto que indica lo que se mostrará en el campo de texto del listado, las opciones son 
    //          concatenado [que mostrará "id - texto"]
    //          etiqueta [que mostrará "texto"]
    //          id [que mostrará "id"], esta última es a opción por default si no se declara la propiedad valorAMostrar)
    //      tipoBusqueda (variable de texto que indica cómo se van a buscar los registros a mostar en el listado, las opciones son
    //          incluye [equivalente a "LIKE '%valorBuscado%'" en MySQL]
    //          terminaEn [equivalente a "LIKE '%valorBuscado'" en MySQL]
    //          comienzaCon [equivalente a "LIKE 'valorBuscado%'" en MySQL], esta última es a opción por default si no se declara la propiedad tipoBusqueda)
    if(!!window.buscadores){
        if(window.buscadores.length){
            if(!!window.uePorUsuario){
                if(!window.uePorUsuario.length){
                    $.ajaxSetup({async: false, cache: false});
                    $.post("modelo/componentes_modelo.php", {option: 'uePorUsuario'}).then(function(res){
                        if(!!res.registrosEncontrados){
                            window.uePorUsuario = res.registrosEncontrados;
                            //window.localStorage.uePorUsuario = JSON.stringify(window.uePorUsuario);
                        }
                    });
                }
            }
            $.each(buscadores,function(index, valor){
                if(!!window.buscadoresConfiguracion){
                    if(!!window.buscadoresConfiguracion[valor]){
                        if(!!window.buscadoresConfiguracion[valor].origenDatos){
                            if(!!window[window.buscadoresConfiguracion[valor].origenDatos]){
                                if(!window[window.buscadoresConfiguracion[valor].origenDatos].length){
                                    $.ajaxSetup({async: false, cache: false});
                                    $.post("modelo/componentes_modelo.php", {option: 'datosLista'+window.buscadoresConfiguracion[valor].origenDatos.substr(0,1).toUpperCase()+window.buscadoresConfiguracion[valor].origenDatos.substr(1)}).then(function(res){
                                        if(!!res.cuentasEncontradas){
                                            window[window.buscadoresConfiguracion[valor].origenDatos] = res.cuentasEncontradas;
                                            window[window.buscadoresConfiguracion[valor].origenDatos].sort(ordenamientoDinamico("valor"));
                                            //window.localStorage[window.buscadoresConfiguracion[valor].origenDatos] = JSON.stringify(window[window.buscadoresConfiguracion[valor].origenDatos]);
                                        }
                                    });
                                }
                            }
                        }
                    }
                }
            });
        }
        // comportamiento de inputs de búsqueda
        var objetosCampoListado = 0,
            objetosCampoListadoEvento = 0;
        $(".campoListado").each(function(){
            objetosCampoListado++;
            if(!(getEvents($(this)[0])['focusout']===undefined)){
                objetosCampoListadoEvento++;
            }
        });
        if(objetosCampoListado!=0&&objetosCampoListado!=objetosCampoListadoEvento){
            $(".campoListado").focusout(function(event){
                if(!$(this).attr('id')){ return; }
                var id = $(this).attr('id').replace("textoVisible__","");

                fnConfirmarConfiguracionDeListado(id);

                if(!window.listadoHabilitado){
                    return;
                }

                if($(this).val()!=$("#"+id).val()){
                    var buscarCoincidencia = new RegExp('^'+$(this).val()+'$' , "i"),
                        arregloBusqueda = window[window.buscadoresConfiguracion[id].origenDatos],
                        arr = jQuery.map(arregloBusqueda, function (value,index) {
                        return fnListadoValorMostrado(value,id).match(buscarCoincidencia) ? index : null;
                    });

                    fnSeleccionaDesdeListado(( arr.length ? arr[0] : "vacio" ),id,true);
                }
            });
        }
        var objetosCampoListado = 0,
            objetosCampoListadoEvento = 0;
        $(".campoListado").each(function(){
            objetosCampoListado++;
            if(!(getEvents($(this)[0])['keypress']===undefined)){
                objetosCampoListadoEvento++;
            }
        });
        if(objetosCampoListado!=0&&objetosCampoListado!=objetosCampoListadoEvento){
            $(".campoListado").keypress(function(event){
                if(!$(this).attr('id')){ return; }
                var id = $(this).attr('id').replace("textoVisible__","");

                fnConfirmarConfiguracionDeListado(id);

                if(!window.listadoHabilitado){
                    return;
                }

                if(!window.buscadoresConfiguracion[id].valoresAlfanumericos){
                    return fnSoloNumeros(event);
                }
            });
        }
        var objetosCampoListado = 0,
            objetosCampoListadoEvento = 0;
        $(".campoListado").each(function(){
            objetosCampoListado++;
            if(!(getEvents($(this)[0])['keyup']===undefined)){
                objetosCampoListadoEvento++;
            }
        });
        if(objetosCampoListado!=0&&objetosCampoListado!=objetosCampoListadoEvento){
            $(".campoListado").keyup(function(){
                if(!$(this).attr('id')){ return; }
                var id = $(this).attr('id').replace("textoVisible__",""),
                    idDiv = "#sugerencia-"+id;

                fnConfirmarConfiguracionDeListado(id);

                if(!window.listadoHabilitado){
                    return;
                }

                if($(this).val()!=''){
                    var buscar = $(this).val();
                    if(window.buscadoresConfiguracion[id].origenDatos.substr(0,7)=="cuentas"&&(!window.buscadoresConfiguracion[id].valorAMostrar||window.buscadoresConfiguracion[id].valorAMostrar=="id")){
                        while(buscar.indexOf("..")>0){
                            buscar = buscar.split("..").join(".");
                        }
                    }

                    var retorno = "<ul id='articulos-lista-consolida'>",
                        buscarCoincidencia = fnListadoTipoBusqueda(buscar,id),
                        arregloBusqueda = window[window.buscadoresConfiguracion[id].origenDatos];

                    if(!window.buscadoresConfiguracion[id].arregloEstatico&&window.buscadoresConfiguracion[id].origenDatos.substr(0,7)=="cuentas"&&(!window.buscadoresConfiguracion[id].valorAMostrar||window.buscadoresConfiguracion[id].valorAMostrar=="id")){
                        administraArregloBusqueda(buscar,id);
                    }

                    var arr = jQuery.map(arregloBusqueda, function (value,index) {
                        var esValido = ( !window.buscadoresConfiguracion[id].cuentasAprobadas||(window.buscadoresConfiguracion[id].cuentasAprobadas&&!!window.buscadoresConfiguracion[id].valorAMostrar&&window.buscadoresConfiguracion[id].valorAMostrar!="id") ? true : false );
                        if(!esValido){
                            $.each(window.buscadoresConfiguracion[id].cuentasAprobadas,function(ind, valorAprobado){
                                var comienzaCon = value.valor.length>valorAprobado.length ? valorAprobado : value.valor,
                                    contiene = value.valor.length>valorAprobado.length ? value.valor : valorAprobado;
                                esValido = ( contiene.substr(0,comienzaCon.length)==comienzaCon ? true : esValido );
                            });
                        }
                        return esValido&&fnListadoValorMostrado(value,id).match(buscarCoincidencia) ? ( nivelDeCuenta(value.valor)<6 ? index : window.uePorUsuario.includes(value.valor.split('.')[5]) ? index : null ) : null;
                    });

                    for(a=0;a<arr.length;a++){
                        val = arr[a];
                        retorno+="<li onClick='fnSeleccionaDesdeListado(\""+val+"\",\""+id+"\")'><a"+( !window.buscadoresConfiguracion[id].sinHREF ? " href='#'" : "" )+">"+arregloBusqueda[val].valor+" - "+arregloBusqueda[val].texto+"</a></li>";
                    }

                    retorno+="</ul>";

                    $.each(window.buscadores,function(index, valor){
                        if(idDiv!="#sugerencia-"+valor){
                            $("#sugerencia-"+valor).hide();
                            $("#sugerencia-"+valor).empty();
                        }
                    });

                    $(idDiv).show();
                    $(idDiv).empty();
                    $(idDiv).append(retorno);
                }else{
                    $(idDiv).hide();
                    $(idDiv).empty();
                }
            });
        }
        $("body").click(function(evt){
            if(evt.target.id.substr(0,8)=="textoVisible__"||evt.target.id.substr(0,11)=="sugerencia-"){
                var divActivo = "";
                divActivo = ( evt.target.id.substr(0,8)=="textoVisible__" ? evt.target.id.substr(8) : divActivo );
                divActivo = ( evt.target.id.substr(0,11)=="sugerencia-" ? evt.target.id.substr(11) : divActivo );
                $.each(window.buscadores,function(index, valor){
                    if($("#sugerencia-"+valor).is(":visible")&&valor!=divActivo){
                        $("#sugerencia-"+valor).hide();
                        $("#sugerencia-"+valor).empty();
                    }
                });
                return;
            }
            $.each(window.buscadores,function(index, valor){
                if($("#sugerencia-"+valor).is(":visible")){
                    $("#sugerencia-"+valor).hide();
                    $("#sugerencia-"+valor).empty();
                }
            });
        });
    }

    // Función para el resize automático de grids con el cambio de tamaño del navegador
    if(!!window.propiedadesResize){
        if(!window.onresize){
            window.onresize = function(){
                if($("[role=grid]").length){
                    $("[role=grid]").each(function(){
                        if($(this).is(":visible")){
                            fnGridResize($(this).attr('id'),window.propiedadesResize);
                        }
                    });
                }
            }
        }
    }
});

function setTextAlign(object, alineacion) {

}

function defaultControl(c) {
    c.select();
    c.focus();
}

function ReloadForm(fB) {
    fB.click();
}

function rTN(event) {
    if (window.event) k = window.event.keyCode;
    else if (event) k = event.which;
    else return true;
    kC = String.fromCharCode(k);
    if ((k == null) || (k == 0) || (k == 8) || (k == 9) || (k == 13) || (k == 27)) return true;
    else if ((("0123456789.-").indexOf(kC) > -1)) return true;
    else return false;
}
function assignComboToInput(c, i) {
    i.value = c.value;
}

function inArray(v, tA, m) {
    for (i = 0; i < tA.length; i++)
        if (v == tA[i].value) return true;
    alert(m);
    return false;
}

function isDate(dS, dF) {
    var mA = dS.match(/^(\d{1,2})(\/|-|.)(\d{1,2})(\/|-|.)(\d{4})$/);
    if (mA == null) {
        alert("Please enter the date in the format " + dF);
        return false;
    }
    if (dF == "d/m/Y") {
        d = mA[1];
        m = mA[3];
    } else {
        d = mA[3];
        m = mA[1];
    }
    y = mA[5];
    if (m < 1 || m > 12) {
        alert("Month must be between 1 and 12");
        return false;
    }
    if (d < 1 || d > 31) {
        alert("Day must be between 1 and 31");
        return false;
    }
    if ((m == 4 || m == 6 || m == 9 || m == 11) && d == 31) {
        alert("Month " + m + " doesn`t have 31 days");
        return false;
    }
    if (m == 2) {
        var isleap = (y % 4 == 0);
        if (d > 29 || (d == 29 && !isleap)) {
            alert("February " + y + " doesn`t have " + d + " days");
            return false;
        }
    }
    return true;
}

function eitherOr(o, t) {
    if (o.value != '') t.value = '';
    else if (o.value == 'NaN') o.value = '';
}
/*Renier & Louis (info@tillcor.com) 25.02.2007
Copyright 2004-2007 Tillcor International
*/
days = new Array('Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa');
months = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
dateDivID = "calendar";

function Calendar(md, dF) {
    iF = document.getElementsByName(md).item(0);
    pB = iF;
    x = pB.offsetLeft;
    y = pB.offsetTop + pB.offsetHeight;
    var p = pB;
    while (p.offsetParent) {
        p = p.offsetParent;
        x += p.offsetLeft;
        y += p.offsetTop;
    }
    dt = convertDate(iF.value, dF);
    nN = document.createElement("div");
    nN.setAttribute("id", dateDivID);
    nN.setAttribute("style", "visibility:hidden;");
    document.body.appendChild(nN);
    cD = document.getElementById(dateDivID);
    cD.style.position = "absolute";
    cD.style.left = x + "px";
    cD.style.top = y + "px";
    cD.style.visibility = (cD.style.visibility == "visible" ? "hidden" : "visible");
    cD.style.display = (cD.style.display == "block" ? "none" : "block");
    cD.style.zIndex = 10000;
    drawCalendar(md, dt.getFullYear(), dt.getMonth(), dt.getDate(), dF);
}

function drawCalendar(md, y, m, d, dF) {
    var tD = new Date();
    if ((m >= 0) && (y > 0)) tD = new Date(y, m, 1);
    else {
        d = tD.getDate();
        tD.setDate(1);
    }
    TR = "<tr>";
    xTR = "</tr>";
    TD = "<td class='dpTD' onMouseOut='this.className=\"dpTD\";' onMouseOver='this.className=\"dpTDHover\";'";
    xTD = "</td>";
    html = "<table class='dpTbl'>" + TR + "<th colspan=3>" + months[tD.getMonth()] + " " + tD.getFullYear() + "</th>" + "<td colspan=2>" + getButtonCode(md, tD, -1, "&lt;", dF) + xTD + "<td colspan=2>" + getButtonCode(md, tD, 1, "&gt;", dF) + xTD + xTR + TR;
    for (i = 0; i < days.length; i++) html += "<th>" + days[i] + "</th>";
    html += xTR + TR;
    for (i = 0; i < tD.getDay(); i++) html += TD + "&nbsp;" + xTD;
    do {
        dN = tD.getDate();
        TD_onclick = " onclick=\"postDate('" + md + "','" + formatDate(tD, dF) + "');\">";
        if (dN == d) html += "<td" + TD_onclick + "<div class='dpDayHighlight'>" + dN + "</div>" + xTD;
        else html += TD + TD_onclick + dN + xTD;
        if (tD.getDay() == 6) html += xTR + TR;
        tD.setDate(tD.getDate() + 1);
    } while (tD.getDate() > 1)
    if (tD.getDay() > 0)
        for (i = 6; i > tD.getDay(); i--) html += TD + "&nbsp;" + xTD;
    html += "</table>";
    document.getElementById(dateDivID).innerHTML = html;
}

function getButtonCode(mD, dV, a, lb, dF) {
    nM = (dV.getMonth() + a) % 12;
    nY = dV.getFullYear() + parseInt((dV.getMonth() + a) / 12, 10);
    if (nM < 0) {
        nM += 12;
        nY += -1;
    }
    return "<button onClick='drawCalendar(\"" + mD + "\"," + nY + "," + nM + "," + 1 + ",\"" + dF + "\");'>" + lb + "</button>";
}

function formatDate(dV, dF) {
    ds = String(dV.getDate());
    ms = String(dV.getMonth() + 1);
    d = ("0" + dV.getDate()).substring(ds.length - 1, ds.length + 1);
    m = ("0" + (dV.getMonth() + 1)).substring(ms.length - 1, ms.length + 1);
    y = dV.getFullYear();
    switch (dF) {
        case "d/m/Y":
            return d + "/" + m + "/" + y;
        case "d.m.Y":
            return d + "." + m + "." + y;
        case "Y/m/d":
            return y + "/" + m + "/" + d;
        default:
            return m + "/" + d + "/" + y;
    }
}

function convertDate(dS, dF) {
    var d, m, y;
    if (dF == "d.m.Y") dA = dS.split(".")
    else dA = dS.split("/");
    switch (dF) {
        case "d/m/Y":
            d = parseInt(dA[0], 10);
            m = parseInt(dA[1], 10) - 1;
            y = parseInt(dA[2], 10);
            break;
        case "Y/m/d":
            d = parseInt(dA[2], 10);
            m = parseInt(dA[1], 10) - 1;
            y = parseInt(dA[0], 10);
            break;
        default:
            //d=parseInt(dA[1],10);
            //m=parseInt(dA[0],10)-1;
            //y=parseInt(dA[2],10);
            d = parseInt(dA[0], 10);
            m = parseInt(dA[1], 10) - 1;
            y = parseInt(dA[2], 10);
            break;
    }
    return new Date(y, m, d);
}

function postDate(mydate, dS) {
    var iF = document.getElementsByName(mydate).item(0);
    iF.value = dS;
    var cD = document.getElementById(dateDivID);
    cD.style.visibility = "hidden";
    cD.style.display = "none";
    iF.focus();
}

function clickDate() {
    Calendar(this.name, this.alt);
}

function changeDate() {
    isDate(this.value, this.alt);
}

function initial() {
    if (document.getElementsByTagName) {
        var as = document.getElementsByTagName("a");
        for (i = 0; i < as.length; i++) {
            var a = as[i];
            if (a.getAttribute("href") && a.getAttribute("rel") == "external") a.target = "_blank";
        }
    }
    var ds = document.getElementsByTagName("input");
    for (i = 0; i < ds.length; i++) {
        if (ds[i].className == "date") {
            ds[i].onclick = clickDate;
            ds[i].onchange = changeDate;
        }
        if (ds[i].className == "number") ds[i].onkeypress = rTN;
    }
}

function muestraMensaje(mensaje, opc, site, time, myhref="") {
    if (!$('#ModalGeneral').is(':hidden')) {
        muestraMensajeTiempo(mensaje, 1, 'ModalGeneral_Advertencia', 5000);
    }else{
        var titulo = '<h3><p><i class="glyphicon glyphicon-list-alt text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, mensaje);
    }
    // var titulo = '<h3><p><i class="glyphicon glyphicon-list-alt text-success" aria-hidden="true"></i> Información</p></h3>';
    // muestraModalGeneral(3, titulo, mensaje);
    if (myhref != "") {
        $('#btnCerrarModalGeneral').click(function() {
             window.location.href = myhref;
        });
    }
}

function muestraMensajeTiempo(mensaje, opc, site, time) {
    //mensajeOK
    if (opc == 1) {
        notificacion = '<div class="alert alert-success alert-dismissable">' + '<button id="idBtnClose" type="button" class="close" data-dismiss="alert">&times;</button>' + '<p>' + mensaje + '</p>' + '</div>';
    }
    //mensaje alerta
    if (opc == 2) {
        notificacion = '<div class="alert alert-warning alert-dismissable">' + '<button type="button" class="close" data-dismiss="alert">&times;</button>' + '<p>' + mensaje + '</p>' + '</div>';
    }
    //mensaje error
    if (opc == 3) {
        notificacion = '<div class="alert alert-danger alert-dismissable">' + '<button type="button" class="close" data-dismiss="alert">&times;</button>' + '<p>' + mensaje + '</p>' + '</div>';
    }
    $('#' + site).html('');
    $('#' + site).html(notificacion);
    //$(“#notificaciones”).append(notificacion);
    $('#' + site).show();
    // se comenta la desaparición del mensaje en un dado tiempo 10.03.18
    // $('#' + site).delay(time).fadeOut("slow");
}

/**
* Funcion general para mostrar modal con mensaje
* parametro Size integer 1-4
* parametro titulo string - dato que muestra en el titulo del modal
* parametro mensaje string - dato que muestra el mensaje en el cuerpo del modal
* parametro pie string - dato que puede ser una instruccion html para mostrar elementos de botones
*/
function muestraModalGeneral(Size = 4, titulo = "", mensaje = "", pie = "", funcion = "") {
    //Modal para mesajes general
    var classSize = "modal-dialog modal-lg";
    if (Size == 1) {
        classSize = "modal-dialog modal-xs";
    } else if (Size == 2) {
        classSize = "modal-dialog modal-sm";
    } else if (Size == 3) {
        classSize = "modal-dialog modal-md";
    }
    if (pie == "") {
        //pie = '<component-button class="btn btn-default" data-dismiss="modal" value="Cerrar"></component-button>';
        pie = '<button class="btn btn-default botonVerde" data-dismiss="modal" id="btnCerrarModalGeneral" name="btnCerrarModalGeneral" onclick="' + funcion + '">Cerrar</button>';
    }
    $('#ModalGeneralTam').addClass(classSize);
    $('#ModalGeneral_Titulo').empty();
    $('#ModalGeneral_Titulo').append(titulo);
    $("#ModalGeneral_Mensaje").empty();
    $('#ModalGeneral_Mensaje').append(mensaje);
    $("#ModalGeneral_Pie").empty();
    $('#ModalGeneral_Pie').append(pie);
    //fnEjecutarVueGeneral();
    $('#ModalGeneral').modal('show');
}

function muestraModalGeneralConfirmacion(Size = 4, titulo = "", mensaje = "", pie = "", funcion = "", parametrosdefuncion = "", ajustar = 0) {
    //Modal para mesajes general
    var classSize = "modal-dialog modal-lg";
    if (Size == 1) {
        classSize = "modal-dialog modal-xs";
    } else if (Size == 2) {
        classSize = "modal-dialog modal-sm";
    } else if (Size == 3) {
        classSize = "modal-dialog modal-md";
    }
    if (pie == "") {
        // pie = '\
        // <div class="input-group pull-right">\
        //     <component-button class="btn btn-default" onclick="'+funcion+'" data-dismiss="modal" value="Si">\
        //     </component-button><component-button class="btn btn-default" data-dismiss="modal" value="No"></component-button>\
        // </div>';
        if (parametrosdefuncion == "") {
            pie = '\
            <div class="input-group pull-right">\
                <button class="btn btn-default botonVerde" onclick="' + funcion + '" data-dismiss="modal" id="btnYesModalConfi">Si</button>\
                <button class="btn btn-default botonVerde" data-dismiss="modal" id="btnCerrarModalGeneral" name="btnCerrarModalGeneral">No</button>\
            </div>';
        } else {
             pie = '\
            <div class="input-group pull-right">\
                <button class="btn btn-default botonVerde" onclick="' + funcion+"(["+parametrosdefuncion+"])" + '" data-dismiss="modal" id="btnYesModalConfi">Si</button>\
                <button class="btn btn-default botonVerde" data-dismiss="modal" id="btnCerrarModalGeneral" name="btnCerrarModalGeneral">No</button>\
            </div>';
        }
    }

    $('#ModalGeneralTam').addClass(classSize);
    $('#ModalGeneral_Titulo').empty();
    $('#ModalGeneral_Titulo').append(titulo);
    $("#ModalGeneral_Mensaje").empty();
    $('#ModalGeneral_Mensaje').append(mensaje);
    $("#ModalGeneral_Pie").empty();
    $('#ModalGeneral_Pie').append(pie);
    //fnEjecutarVueGeneral();
    $('#ModalGeneral').modal('show');

    if (ajustar == 1) {
        var numero = document.documentElement.clientHeight;
        numero = Number(numero) - Number(300);
        $("#ModalGeneral_Mensaje").css("overflow-x", "scroll"); 
        $("#ModalGeneral_Mensaje").css("overflow-y", "scroll");
        $("#ModalGeneral_Mensaje").css("height", numero+"px"); 
    } else {
        $("#ModalGeneral_Mensaje").css("overflow-x", ""); 
        $("#ModalGeneral_Mensaje").css("overflow-y", "");
        $("#ModalGeneral_Mensaje").css("height", ""); 
    }
}

function muestraCargandoGeneral() {
    //Mostrar Spiner de Cargando
    
        //$("#SpinerLoading").show();
        //se comentan las lineas siguiente para probar bloqueo de pantalla
        $('#ModalSpinerGeneral').modal({
            backdrop: 'static',
            keyboard: false
        });
        document.getElementById('divProcesandoGeneral').style.display = 'inline';
        $('#ModalSpinerGeneral').modal('show');
    

    //05.04.2018
    //se agrega funcion para bloqueo de pantalla
    //console.log('prueba inicial de bloqueo');
    // $.blockUI({ message: '<i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span>' });
    // $(".blockMsg").css({"background-color": "","border":"0px"});
    // console.log('prueba final de bloqueo');
}

function ocultaCargandoGeneral() {
    //Ocultar Spiner de Cargando
    setTimeout(function() {
        //$("#SpinerLoading").hide();
        //se comentan las lineas siguiente para probar bloqueo de pantalla
        document.getElementById('divProcesandoGeneral').style.display = 'none';
        $('#ModalSpinerGeneral').modal('hide');
    },1500);
    //05.04.2018
    //Se agrega funcion para desbloqueo de pantalla
    // setTimeout(function (){
        //console.log('fin de transaccion');
        // $.unblockUI();
        // console.log('terminacion de transaccion');
    // }, 2000);
}

function mostrarTabla() {}

function validarNum(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    if (tecla == 8) return true;
    patron = /\d/;
    te = String.fromCharCode(tecla);
    return patron.test(te);
}

/**
* Validar que la entrada sea solo numeros
**/
function validarSiNumero(e){
    var tecla = (document.all) ? e.keyCode : e.which;
    //console.log("entra con el dato:"+e);

    //Tecla de retroceso para borrar, siempre la permite
    if (tecla==8){
        return true;
    }

    if (tecla == 45 || tecla== 43 || tecla==231) {
        return false;
    }

    if (/[!òó+ç]/.test(tecla)) {
        return false;
    }

    // Patron de entrada, en este caso solo acepta numeros
    var patron =/[0-9-.]/;
    var tecla_final = String.fromCharCode(tecla);

    return patron.test(tecla_final);
}


function primeroLetra(evt, element) {
    if (window.event) { //asignamos el valor de la tecla a keynum
        keynum = evt.keyCode; //IE
    } else {
        keynum = evt.which; //FF
    }
    //comprobamos si se encuentra en el rango numérico y que teclas no recibirá.
    // 46 = .


    if (element.value.length>=1) {

    if ((keynum > 47 && keynum < 58) || keynum == 8 || keynum == 13 || keynum == 6 || keynum == 46) {
        return true;
    } else {
        return false;
    }
    } else true;
}

function soloNumeros(e) {
    var tecla = (document.all) ? e.keyCode : e.which;
    //console.log("entra con el dato:"+e);

    //Tecla de retroceso para borrar y tabulador, siempre la permite
    if (tecla == 8 || tecla == 0){
        // 8 - Borrar
        // 0 - Tabulador
        return true;
    }

    if (tecla == 45 || tecla== 43 || tecla==231 || tecla==46) {
        return false;
    }

    if (/[!òó+ç]/.test(tecla)) {
        return false;
    }

    // Patron de entrada, en este caso solo acepta numeros
    var patron =/[0-9-.]/;
    var tecla_final = String.fromCharCode(tecla);

    return patron.test(tecla_final);
}

function validaLetras(e) {
    key = e.keyCode || e.which;
    tecla = String.fromCharCode(key).toString();
    letras = " áéíóúabcdefghijklmnñopqrstuvwxyzÁÉÍÓÚABCDEFGHIJKLMNÑOPQRSTUVWXYZ"; //Se define todo el abecedario que se quiere que se muestre.
    especiales = [8, 37, 39, 46, 6]; //Es la validación del KeyCodes, que teclas recibe el campo de texto.
    tecla_especial = false
    for (var i in especiales) {
        if (key == especiales[i]) {
            tecla_especial = true;
            break;
        }
    }
    if (letras.indexOf(tecla) == -1 && !tecla_especial) return false;
}
//Función para validar un RFC
// Devuelve el RFC sin espacios ni guiones si es correcto
// Devuelve false si es inválido
// (debe estar en mayúsculas, guiones y espacios intermedios opcionales)
function rfcValido(rfc, aceptarGenerico = true) {
    //const re       = /^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$/;
    const re = /^([A-ZÑ&]{3,4})?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01]))?([A-Z\d]{2})([A\d])$/;
    var validado = rfc.match(re);
    if (!validado) //Coincide con el formato general del regex?
        return false;
    //Separar el dígito verificador del resto del RFC
    const digitoVerificador = validado.pop(),
        rfcSinDigito = validado.slice(1).join(''),
        len = rfcSinDigito.length,
        //Obtener el digito esperado
        diccionario = "0123456789ABCDEFGHIJKLMN&OPQRSTUVWXYZ Ñ",
        indice = len + 1;
    var suma,
        digitoEsperado;
    if (len == 12) suma = 0
    else suma = 481; //Ajuste para persona moral
    for (var i = 0; i < len; i++) suma += diccionario.indexOf(rfcSinDigito.charAt(i)) * (indice - i);
    digitoEsperado = 11 - suma % 11;
    if (digitoEsperado == 11) digitoEsperado = 0;
    else if (digitoEsperado == 10) digitoEsperado = "A";
    //El dígito verificador coincide con el esperado?
    // o es un RFC Genérico (ventas a público general)?
    if ((digitoVerificador != digitoEsperado) && (!aceptarGenerico || rfcSinDigito + digitoVerificador != "XAXX010101000")) return false;
    else if (!aceptarGenerico && rfcSinDigito + digitoVerificador == "XEXX010101000") return false;
    return rfcSinDigito + digitoVerificador;
}
/*$("#datepicker").datepicker({
    showButtonPanel: true,
    changeMonth: true,
    changeYear: true,
    showOn: "button",
    buttonImage: "images/calendar.gif",
    buttonImageOnly: true,
    buttonText: "Select date",
    showWeek: true,
    firstDay: 1,
    dateFormat: "yy-mm-dd",
    defaultDate: "Now"
});

$("#datepicker2").datepicker({
    showButtonPanel: true,
    changeMonth: true,
    changeYear: true,
    showOn: "button",
    buttonImage: "images/calendar.gif",
    buttonImageOnly: true,
    buttonText: "Select date",
    showWeek: true,
    firstDay: 1,
    dateFormat: "yy-mm-dd",
    defaultDate: "Now"
});*/
/*  Validaciones Generales */
function fnSoloLetras(e) {
    key = e.keyCode || e.which;
    tecla = String.fromCharCode(key).toString();
    //Se define todo el abecedario que se quiere que se muestre.
    letras = " áéíóúabcdefghijklmnñopqrstuvwxyzÁÉÍÓÚABCDEFGHIJKLMNÑOPQRSTUVWXYZ";
    //Es la validación del KeyCodes, que teclas recibe el campo de texto.
    especiales = [8, 37, 39, 46, 6];
    tecla_especial = false
    for (var i in especiales) {
        if (key == especiales[i]) {
            tecla_especial = true;
            break;
        }
    }
    if (letras.indexOf(tecla) == -1 && !tecla_especial) return false;
}

function fnSoloNumeros(evt) {
    if (window.event) {
        //asignamos el valor de la tecla a keynum
        keynum = evt.keyCode; //IE
    } else {
        keynum = evt.which; //FF
    }
    //comprobamos si se encuentra en el rango numérico y que teclas no recibirá.
    if ((keynum > 47 && keynum < 58) || keynum == 8 || keynum == 13 || keynum == 6 || keynum == 46) {
        return true;
    } else {
        return false;
    }
}

function fnCleanN() {
    var n = document.getElementsByClassName('num');
    var numVal = n[0].value;
    var size = numVal.length;
    for (i = 0; i < size; i++) {
        if (!isNaN(numVal[i])) {
            n[0].value = numVal;
        } else {
            n[0].value = '';
        }
    }
}
function fnSoloBorrar(evt) {
    if (window.event) {
        //asignamos el valor de la tecla a keynum
        keynum = evt.keyCode; //IE
    } else {
        keynum = evt.which; //FF
    }
    //comprobamos si se encuentra en el rango numérico y que teclas no recibirá.
    if ( keynum == 8 ) {
        return true;
    } else {
        return false;
    }
}

function fnEmail() {
    var email = document.getElementsByClassName('email');
    expr = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if (email.length > 0) {
        var mail = email[0].value;
        if (mail == '') {
            document.getElementsByClassName('email')[0].style.border = "solid red 1px";
        } else {
            if (!expr.test(mail)) {
                document.getElementsByClassName('email')[0].style.border = "solid red 1px";
            } else {
                document.getElementsByClassName('email')[0].style.border = "solid green 1px";
            }
        }
    }
}

function fnAgregarGrid(aDatos, aColumnas, aColumnasGrid, aDiv, aInputSearch, aTipoBusqueda) {
    //var data = aDatos;
    var data = aDatos;
    $('#' + aDiv).html(aColumnasGrid);
    var aColumnasSource = eval(aColumnas);
    var source = {
        datatype: "json",
        datafields: aColumnasSource,
        localdata: data
    };
    //eval(aColumnasGrid);
    var dataAdapter = new $.jqx.dataAdapter(source);
    //alert(source);
    $('#' + aDiv).jqxGrid({
        width: "100%",
        source: dataAdapter,
        theme: 'estilogrp',
        pageable: true,
        filterable: true,
        sortable: true,
        columnsresize: false,
        columns: eval(aColumnasGrid),
        showtoolbar: true,
        autoheight: true,
        editmode: 'click',

        selectionmode: 'singlecell',
        pagermode: 'simple',
        altRows: true,
        rendertoolbar: function(toolbar) {
            var me = this;
            var container = $("<div style='margin: 5px;'></div>");
            if (aInputSearch != "") {
                var span = $("<span style='float: left; margin-top: 5px; margin-right: 4px;'>Buscar: </span>");
                var input = $("<input class='jqx-input jqx-widget-content jqx-rc-all' id='searchField' type='text' style='height: 23px; float: left; width: 223px;' />");
            }
            var exportexcel = $("<input class='jqx-button jqx-widget-content jqx-rc-all' value='Exportar a excel' id='exportExcel' type='button' style='height: 23px; float: right; width: 223px;' />");
            toolbar.append(container);
            container.append(span);
            container.append(input);
            container.append(exportexcel);
            var oldVal = "";
            exportexcel.on('click', function(event) {
                $("#" + aDiv).jqxGrid('beginupdate');
                var cols = $("#" + aDiv).jqxGrid("columns");
                for (var i = 0; i < cols.records.length; i++) {
                    //Si son las columnas que no exportará las oculta
                    if (cols.records[i].datafield.includes("noexportar")) $("#" + aDiv).jqxGrid('hidecolumn', cols.records[i].datafield);
                }
                $("#" + aDiv).jqxGrid('endupdate');
                $("#" + aDiv).jqxGrid('exportdata', 'xls', aDiv, true, null, false, "modelo/save-file.php");
                var cols = $("#" + aDiv).jqxGrid("columns");
                for (var i = 0; i < cols.records.length; i++) {
                    //Si son las columnas qhe no exportará las vuelve a mostrar
                    if (cols.records[i].datafield.includes("noexportar")) $("#" + aDiv).jqxGrid('showcolumn', cols.records[i].datafield);
                }
                $("#" + aDiv).jqxGrid('endupdate');
            });
            if (aInputSearch != "") {
                input.on('keydown', function(event) {
                    if (me.timer) {
                        clearTimeout(me.timer);
                    }
                    if (oldVal != input.val()) {
                        me.timer = setTimeout(function() {
                            var filtergroup = new $.jqx.filter();
                            filtervalue = input.val();
                            if (aTipoBusqueda == 1) filtercondition = 'contains';
                            if (aTipoBusqueda == 2) filtercondition = 'starts_with';
                            var filter2 = filtergroup.createfilter('stringfilter', filtervalue, filtercondition);
                            var filter_or_operator = 1;
                            filtergroup.addfilter(filter_or_operator, filter2); // add the filters.
                            filtergroup.operator = 'or';
                            var cols = $("#" + aDiv).jqxGrid("columns");
                            for (var i = 0; i < cols.records.length; i++) {
                                //Si son las columnas modificar y elminar se las salta (no filtrar)
                                if (cols.records[i].datafield.includes("Modificar")) continue;
                                if (cols.records[i].datafield.includes("Eliminar")) continue;
                                if (cols.records[i].datafield.includes("nofiltrar")) continue;
                                $("#" + aDiv).jqxGrid('addfilter', cols.records[i].datafield, filtergroup);
                            }
                            //$("#"+aDiv).jqxGrid('addfilter', aInputSearch, filtergroup);
                            //$("#"+aDiv).jqxGrid('addfilter', "partida_esp,10%,Partida Específica,", filtergroup);
                            // apply the filters.
                            $("#" + aDiv).jqxGrid('applyfilters');
                        }, 1000);
                        oldVal = input.val();
                    }
                });
            }
        }
    });
    var localizationobj = {};
    localizationobj.pagergotopagestring = "Ir a la página:";
    localizationobj.pagershowrowsstring = "Renglones a mostrar:";
    localizationobj.pagerrangestring = " de ";
    localizationobj.pagernextbuttonstring = "siguiente";
    localizationobj.pagerpreviousbuttonstring = "anterior";
    localizationobj.pagerfirstbuttonstring = "primera página",
    localizationobj.pagerlastbuttonstring = "última página",
    localizationobj.sortascendingstring = "Orden ascendente";
    localizationobj.sortdescendingstring = "Orden descendente";
    localizationobj.sortremovestring = "Quitar ordenamiento";
    localizationobj.emptydatastring = "No hay información a desplegar";
    localizationobj.filtershowrowstring = "Mostrar renglones donde:";
    localizationobj.filterorconditionstring = "O";
    localizationobj.filterandconditionstring = "Y";
    localizationobj.filterclearstring = "Limpiar";
    localizationobj.filterstring = "Filtrar",
        localizationobj.filterstringcomparisonoperators = ['vacío', 'no vacío', 'contiene', 'contiene(exactamente por Mayus./Minus.)', 'no contiene', 'no contiene(exactamente por Mayus./Minus.)', 'comienza con', 'comienza con(exactamente por Mayus./Minus.)', 'termina con', 'termina con(exactamente por Mayus./Minus.)', 'igual', 'igual(exactamente por Mayus./Minus.)', 'nulo', 'no nulo'];
    // apply localization.
    $('#' + aDiv).jqxGrid('localizestrings', localizationobj);
}

function fnAgregarGrid_Detalle_nostring(aDatos, aColumnas, aColumnasGrid, aDiv, aInputSearch, aTipoBusqueda, aColumnasExcel, bDetalle= false, editable1=false, initrowdetails= "", aColumnasVisuales, strNombreExcel= "Reporte") {
    var data = aDatos;
    //console.log("dataGrid :"+data);
    $('#' + aDiv).html(aColumnasGrid);
    var aColumnasSource = aColumnas;

    var source = {
        datatype: "json",
        datafields: aColumnasSource,
        localdata: data
    };

    if (!Array.isArray(aColumnasExcel)) {
        aColumnasExcel= [];
    }

    if (!Array.isArray(aColumnasVisuales)) {
        aColumnasVisuales= [];
    }

    if (bDetalle && initrowdetails.length==0) {
        bDetalle= false;
    }

    //eval(aColumnasGrid);
    var dataAdapter = new $.jqx.dataAdapter(source);

    var tooltiprenderer = function (element) {
        $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
    }

    //alert(source);
    $('#' + aDiv).jqxGrid({
        width: "100%",
        source: dataAdapter,
        theme: 'estilogrp',
        pageable: true,
        filterable: true,
        sortable: true,
        columnsresize: false,
        columns: aColumnasGrid,
        showtoolbar: true,
        autoheight: false,
        editmode: 'click',
        editable:editable1,
        selectionmode: 'singlecell',
        autorowheight: true,
        showstatusbar: true,
        statusbarheight: 30,
        showaggregates: true,
        altrows: true,
        rowdetails: bDetalle,
        pagerheight: 40,
        rowsheight: 30,
        pagermode: 'simple',
        rowdetailstemplate: { rowdetails: "<div style='margin: 10px;'><ul style='margin-left: 30px;'><li class='title'></li><li>Detalle Requisicion</li></ul><div class='information'></div><div class='notes'></div></div>", rowdetailsheight: 200 },

        initrowdetails: initrowdetails,

        rendertoolbar: function(toolbar) {
            var me = this;
            var container = $("<div style='margin: 5px;'></div>");

            if (aInputSearch != "") {
                var span = $("<span style='float: left; margin-top: 5px; margin-right: 4px;'>Buscar: </span>");
                var input = $("<input class='jqx-input-estilogrp jqx-widget-content-estilogrp jqx-rc-all-estilogrp' id='searchField' type='text' style='height: 23px; float: left; width: 223px;' />");
            }

            var exportexcel = $("<input class='jqx-button-estilogrp jqx-widget-content-estilogrp jqx-rc-all-estilogrp' value='Exportar a excel' id='exportExcel' type='button' style='height: 23px; float: right; width: 223px;' />");

            toolbar.append(container);
            container.append(span);
            container.append(input);
            container.append(exportexcel);
            var oldVal = "";

            exportexcel.on('click', function(event) {
                // extraccion de datos en la tabla 23.01.18
                var existenciaDatos = $("#" + aDiv).jqxGrid('getrowdata',0);
                // comprobacion de contenido encaso de no contener informacion manda mensaje
                // caso contrario procesa la solicitd de exportacion 23.01.18
                if($.isEmptyObject(existenciaDatos)){
                    // envio mensaje
                    muestraModalGeneral(3,'<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>','No se encontró información para ser exportada.');
                }
                else{
                    $("#" + aDiv).jqxGrid('beginupdate');
                    var cols = $("#" + aDiv).jqxGrid("columns");

                    for (var i = 0; i < cols.records.length; i++) {
                        if (aColumnasExcel.indexOf(i) != -1) {
                            $("#" + aDiv).jqxGrid('showcolumn', cols.records[i].datafield);
                        } else {
                            $("#" + aDiv).jqxGrid('hidecolumn', cols.records[i].datafield);
                        }
                    }

                    //var value = $("#" + aDiv).jqxGrid('addrow', null, [{"uno", "dos", "tres"}], "first");

                    $("#" + aDiv).jqxGrid('endupdate');
                    $("#" + aDiv).jqxGrid('exportdata', 'xls', strNombreExcel, true, null, false, "modelo/save-file.php");

                    // se agrega inicio de modificacion para correcccion de movimiento de columnas 23.01.18
                    $("#" + aDiv).jqxGrid('beginupdate');

                    for (var i = 0; i < cols.records.length; i++) {
                        if (aColumnasVisuales.indexOf(i) != -1) {
                            $("#" + aDiv).jqxGrid('showcolumn', cols.records[i].datafield);
                        } else {
                            $("#" + aDiv).jqxGrid('hidecolumn', cols.records[i].datafield);
                        }
                    }

                    /*if ($('#'+aDiv).jqxGrid('rowdetails')) {
                        for (var i = 1; i < cols.records.length; i++) {
                            if (aColumnasExcel.indexOf(i) != -1 && cols.records[i] !== 'null' && cols.records[i] !== 'undefined') {
                                //Si son las columnas qhe no exportará las vuelve a mostrar
                                $("#" + aDiv).jqxGrid('showcolumn', cols.records[i].datafield);
                            }
                        }
                    }

                    for (var i = 0; i < cols.records.length; i++) {
                        if (aColumnasExcel.indexOf(i) != -1 && cols.records[i] !== 'null' && cols.records[i] !== 'undefined') {
                            //Si son las columnas que no exportará las oculta
                            $("#" + aDiv).jqxGrid('hidecolumn', cols.records[i].datafield);
                        }
                    }*/

                    //$("#" + aDiv).jqxGrid('endupdate');
                    //$("#" + aDiv).jqxGrid('exportdata', 'xls', aDiv, true, null, false, "modelo/save-file.php");

                    /*var cols = $("#" + aDiv).jqxGrid("columns");

                    for (var i = 1; i < cols.records.length; i++) {
                        if (aColumnasExcel.indexOf(i) != -1 && cols.records[i] !== 'null' && cols.records[i] !== 'undefined') {
                            //Si son las columnas qhe no exportará las vuelve a mostrar
                            if (cols.records[i].datafield.includes("noexportar")) $("#" + aDiv).jqxGrid('showcolumn', cols.records[i].datafield);
                        }
                    }*/

                    $("#" + aDiv).jqxGrid('endupdate');
                } // fin comprobacion de exportacion 23.01.18
            });

            if (aInputSearch != "") {
                input.on('keyup', function(event) {
                    if (me.timer) {
                        clearTimeout(me.timer);
                    }

                    if (oldVal != input.val()) {
                        // me.timer = setTimeout(function() {
                            var filtergroup = new $.jqx.filter();
                            filtervalue = input.val();

                            if (aTipoBusqueda == 1) filtercondition = 'contains';
                            if (aTipoBusqueda == 2) filtercondition = 'starts_with';

                            var filter2 = filtergroup.createfilter('stringfilter', filtervalue, filtercondition);
                            var filter_or_operator = 1;
                            filtergroup.addfilter(filter_or_operator, filter2); // add the filters.
                            filtergroup.operator = 'or';
                            var cols = $("#" + aDiv).jqxGrid("columns");

                            for (var i = 0; i < cols.records.length; i++) {
                                //Si son las columnas modificar y elminar se las salta (no filtrar)
                                if (cols.records[i].datafield.includes("Modificar")) continue;
                                if (cols.records[i].datafield.includes("Eliminar")) continue;
                                if (cols.records[i].datafield.includes("nofiltrar")) continue;
                                $("#" + aDiv).jqxGrid('addfilter', cols.records[i].datafield, filtergroup);
                            }

                            //$("#"+aDiv).jqxGrid('addfilter', aInputSearch, filtergroup);
                            //$("#"+aDiv).jqxGrid('addfilter', "partida_esp,10%,Partida Específica,", filtergroup);
                            // apply the filters.
                            $("#" + aDiv).jqxGrid('applyfilters');
                        // }, 1000);
                        oldVal = input.val();
                        if(typeof fnGridResize==="function"&&!!window.propiedadesResize){
                            $("#"+aDiv).trigger("pagechanged");
                        }
                    }
                });
            }
        }
    });

    var localizationobj = {};
    localizationobj.pagergotopagestring = "Ir a la página:";
    localizationobj.pagershowrowsstring = "Renglones a mostrar:";
    localizationobj.pagerrangestring = " de ";
    localizationobj.pagernextbuttonstring = "siguiente";
    localizationobj.pagerpreviousbuttonstring = "anterior";
    localizationobj.pagerfirstbuttonstring = "primera página",
    localizationobj.pagerlastbuttonstring = "última página",
    localizationobj.sortascendingstring = "Orden ascendente";
    localizationobj.sortdescendingstring = "Orden descendente";
    localizationobj.sortremovestring = "Quitar ordenamiento";
    localizationobj.emptydatastring = "No hay información a desplegar";
    localizationobj.filtershowrowstring = "Mostrar renglones donde:";
    localizationobj.filterorconditionstring = "O";
    localizationobj.filterandconditionstring = "Y";
    localizationobj.filterclearstring = "Limpiar";
    localizationobj.filterstring = "Filtrar",
    localizationobj.filterstringcomparisonoperators = ['vacío', 'no vacío', 'contiene', 'contiene(exactamente por Mayus./Minus.)', 'no contiene', 'no contiene(exactamente por Mayus./Minus.)', 'comienza con', 'comienza con(exactamente por Mayus./Minus.)', 'termina con', 'termina con(exactamente por Mayus./Minus.)', 'igual', 'igual(exactamente por Mayus./Minus.)', 'nulo', 'no nulo'];
    // apply localization.
    $('#' + aDiv).jqxGrid('localizestrings', localizationobj);
}

function fnAgregarGrid_Detalle(aDatos, aColumnas, aColumnasGrid, aDiv, aInputSearch, aTipoBusqueda, aColumnasExcel, bDetalle= false, editable1=false, initrowdetails= "", aColumnasVisuales, strNombreExcel= "Reporte") {
    var data = aDatos;
    $('#' + aDiv).html(aColumnasGrid);
    var aColumnasSource = eval(aColumnas);

    var source = {
        datatype: "json",
        datafields: aColumnasSource,
        localdata: data
    };

    if (!Array.isArray(aColumnasExcel)) {
        aColumnasExcel= [];
    }

    if (!Array.isArray(aColumnasVisuales)) {
        aColumnasVisuales= [];
    }

    if (bDetalle && initrowdetails.length==0) {
        bDetalle= false;
    }

    //eval(aColumnasGrid);
    var dataAdapter = new $.jqx.dataAdapter(source);

    var tooltiprenderer = function (element) {
        $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
    }

    //alert(source);
    $('#' + aDiv).jqxGrid({
        width: "100%",
        source: dataAdapter,
        theme: 'estilogrp',
        pageable: true,
        filterable: true,
        sortable: true,
        columnsresize: false,
        columns: eval(aColumnasGrid),
        showtoolbar: true,
        autoheight: false,
        editmode: 'click',
        editable:editable1,
        selectionmode: 'singlecell',
        autorowheight: true,
        showstatusbar: true,
        statusbarheight: 30,
        showaggregates: true,
        altrows: true,
        rowdetails: bDetalle,
        pagerheight: 40,
        rowsheight: 30,
        pagermode: 'simple',
        rowdetailstemplate: { rowdetails: "<div style='margin: 10px;'><ul style='margin-left: 30px;'><li class='title'></li><li>Detalle Requisicion</li></ul><div class='information'></div><div class='notes'></div></div>", rowdetailsheight: 200 },

        initrowdetails: initrowdetails,

        rendertoolbar: function(toolbar) {
            var me = this;
            var container = $("<div style='margin: 5px;'></div>");

            if (aInputSearch != "") {
                var span = $("<span style='float: left; margin-top: 5px; margin-right: 4px;'>Buscar: </span>");
                var input = $("<input class='jqx-input-estilogrp jqx-widget-content-estilogrp jqx-rc-all-estilogrp' id='searchField"+aDiv+"' type='text' style='height: 23px; float: left; width: 223px;' />");
            }

            var exportexcel = $("<input class='jqx-button-estilogrp jqx-widget-content-estilogrp jqx-rc-all-estilogrp' value='Exportar a excel' id='exportExcel' type='button' style='height: 23px; float: right; width: 223px;' />");

            toolbar.append(container);
            container.append(span);
            container.append(input);
            container.append(exportexcel);
            var oldVal = "";

            exportexcel.on('click', function(event) {
                // extraccion de datos en la tabla 23.01.18
                var existenciaDatos = $("#" + aDiv).jqxGrid('getrowdata',0);
                // comprobacion de contenido encaso de no contener informacion manda mensaje
                // caso contrario procesa la solicitd de exportacion 23.01.18
                if($.isEmptyObject(existenciaDatos)){
                    // envio mensaje
                    muestraModalGeneral(3,'<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>','No se encontró información para ser exportada.');
                }
                else{

                    $("#" + aDiv).jqxGrid('beginupdate');
                    var cols = $("#" + aDiv).jqxGrid("columns");

                    for (var i = 0; i < cols.records.length; i++) {
                        if (aColumnasExcel.indexOf(i) != -1) {
                            $("#" + aDiv).jqxGrid('showcolumn', cols.records[i].datafield);
                        } else {
                            $("#" + aDiv).jqxGrid('hidecolumn', cols.records[i].datafield);
                        }
                    }

                    //var value = $("#" + aDiv).jqxGrid('addrow', null, [{"uno", "dos", "tres"}], "first");

                    $("#" + aDiv).jqxGrid('endupdate');
                    $("#" + aDiv).jqxGrid('exportdata', 'xls', strNombreExcel, true, null, false, "modelo/save-file.php");

                    // se agrega inicio de modificacion para correcccion de movimiento de columnas 23.01.18
                    $("#" + aDiv).jqxGrid('beginupdate');

                    for (var i = 0; i < cols.records.length; i++) {
                        if (aColumnasVisuales.indexOf(i) != -1) {
                            $("#" + aDiv).jqxGrid('showcolumn', cols.records[i].datafield);
                        } else {
                            $("#" + aDiv).jqxGrid('hidecolumn', cols.records[i].datafield);
                        }
                    }

                    /*if ($('#'+aDiv).jqxGrid('rowdetails')) {
                        for (var i = 1; i < cols.records.length; i++) {
                            if (aColumnasExcel.indexOf(i) != -1 && cols.records[i] !== 'null' && cols.records[i] !== 'undefined') {
                                //Si son las columnas qhe no exportará las vuelve a mostrar
                                $("#" + aDiv).jqxGrid('showcolumn', cols.records[i].datafield);
                            }
                        }
                    }

                    for (var i = 0; i < cols.records.length; i++) {
                        if (aColumnasExcel.indexOf(i) != -1 && cols.records[i] !== 'null' && cols.records[i] !== 'undefined') {
                            //Si son las columnas que no exportará las oculta
                            $("#" + aDiv).jqxGrid('hidecolumn', cols.records[i].datafield);
                        }
                    }*/

                    //$("#" + aDiv).jqxGrid('endupdate');
                    //$("#" + aDiv).jqxGrid('exportdata', 'xls', aDiv, true, null, false, "modelo/save-file.php");

                    /*var cols = $("#" + aDiv).jqxGrid("columns");

                    for (var i = 1; i < cols.records.length; i++) {
                        if (aColumnasExcel.indexOf(i) != -1 && cols.records[i] !== 'null' && cols.records[i] !== 'undefined') {
                            //Si son las columnas qhe no exportará las vuelve a mostrar
                            if (cols.records[i].datafield.includes("noexportar")) $("#" + aDiv).jqxGrid('showcolumn', cols.records[i].datafield);
                        }
                    }*/

                    $("#" + aDiv).jqxGrid('endupdate');
                } // fin comprobacion de exportacion 23.01.18
            });
            // console.log("antes if");
            // console.log("aInputSearch: "+aInputSearch);
            if (aInputSearch != "") {
                // console.log("entra if");
                input.on('keyup', function(event) {
                    if (me.timer) {
                        clearTimeout(me.timer);
                    }
                    // console.log("**********");
                    // console.log("oldVal: "+oldVal);
                    // console.log("input: "+input.val());
                    if (oldVal != input.val()) {
                        // me.timer = setTimeout(function() {
                            var filtergroup = new $.jqx.filter();
                            filtervalue = input.val();

                            if (aTipoBusqueda == 1) filtercondition = 'contains';
                            if (aTipoBusqueda == 2) filtercondition = 'starts_with';

                            var filter2 = filtergroup.createfilter('stringfilter', filtervalue, filtercondition);
                            var filter_or_operator = 1;
                            filtergroup.addfilter(filter_or_operator, filter2); // add the filters.
                            filtergroup.operator = 'or';
                            var cols = $("#" + aDiv).jqxGrid("columns");

                            for (var i = 0; i < cols.records.length; i++) {
                                //Si son las columnas modificar y elminar se las salta (no filtrar)
                                if (cols.records[i].datafield.includes("Modificar")) continue;
                                if (cols.records[i].datafield.includes("Eliminar")) continue;
                                if (cols.records[i].datafield.includes("nofiltrar")) continue;
                                $("#" + aDiv).jqxGrid('addfilter', cols.records[i].datafield, filtergroup);
                            }

                            //$("#"+aDiv).jqxGrid('addfilter', aInputSearch, filtergroup);
                            //$("#"+aDiv).jqxGrid('addfilter', "partida_esp,10%,Partida Específica,", filtergroup);
                            // apply the filters.
                            $("#" + aDiv).jqxGrid('applyfilters');
                        // }, 1000);
                        oldVal = input.val();
                    }
                });
            }
        }
    });

    var localizationobj = {};
    localizationobj.pagergotopagestring = "Ir a la página:";
    localizationobj.pagershowrowsstring = "Renglones a mostrar:";
    localizationobj.pagerrangestring = " de ";
    localizationobj.pagernextbuttonstring = "siguiente";
    localizationobj.pagerpreviousbuttonstring = "anterior";
    localizationobj.pagerfirstbuttonstring = "primera página",
    localizationobj.pagerlastbuttonstring = "última página",
    localizationobj.sortascendingstring = "Orden ascendente";
    localizationobj.sortdescendingstring = "Orden descendente";
    localizationobj.sortremovestring = "Quitar ordenamiento";
    localizationobj.emptydatastring = "No hay información a desplegar";
    localizationobj.filtershowrowstring = "Mostrar renglones donde:";
    localizationobj.filterorconditionstring = "O";
    localizationobj.filterandconditionstring = "Y";
    localizationobj.filterclearstring = "Limpiar";
    localizationobj.filterstring = "Filtrar",
    localizationobj.filterstringcomparisonoperators = ['vacío', 'no vacío', 'contiene', 'contiene(exactamente por Mayus./Minus.)', 'no contiene', 'no contiene(exactamente por Mayus./Minus.)', 'comienza con', 'comienza con(exactamente por Mayus./Minus.)', 'termina con', 'termina con(exactamente por Mayus./Minus.)', 'igual', 'igual(exactamente por Mayus./Minus.)', 'nulo', 'no nulo'];
    // apply localization.
    $('#' + aDiv).jqxGrid('localizestrings', localizationobj);
}
/**
 *
 */
function fnAgregarGridv2(aDatos, aDiv, aInputSearch = "", aTipoBusqueda = 1) {
    var columnasNombres = "";
    var columnasNombresGrid = "";
    var columnasJSON = eval(aDatos);
    if (columnasJSON.length > 0) {
        var columnsIn = columnasJSON[0];
        for (var key in columnsIn) {
            columnaSize = 250; //si no tiene tamaño definido entonces es 250px
            columnasNombres = columnasNombres + "{ name: '" + key + "', type: 'string' },"; // here is your column name you are looking for
            //separa el nombre de la columna porque contiene integrado el tamaño
            var columnasSizeArray = key.split(",");
            if (columnasSizeArray.length > 1) columnaSize = columnasSizeArray[1];
            if (columnasSizeArray.length > 1) columnaTitulo = columnasSizeArray[2];
            if (columnasSizeArray.length > 1) columnaCampo = columnasSizeArray[0];
            else columnaTitulo = key;
            if (columnasSizeArray.length > 1) columnaHidden = (columnasSizeArray[3] == "h") ? "true" : "false";
            else columnaHidden = "false";
            columnasNombresGrid = columnasNombresGrid + " { text: '" + columnaTitulo + "', datafield: '" + key + "', width: '" + columnaSize + "', hidden: " + columnaHidden + " },";
        }
    } else {
        console.log("No hay columnas");
    }
    //le quita el ultimo caracter porque es una coma
    columnasNombres = columnasNombres.substring(0, columnasNombres.length - 1);
    columnasNombresGrid = columnasNombresGrid.substring(0, columnasNombresGrid.length - 1);
    podereditar = "";
    //le agrega [ al principio y ] al final para que sea como arreglo
    columnasNombres = "[" + columnasNombres + "]";
    columnasNombresGrid = "[" + columnasNombresGrid + podereditar + "]";
    fnAgregarGrid(aDatos, columnasNombres, columnasNombresGrid, aDiv, aInputSearch, aTipoBusqueda);
}

function fnAlerta(aDatos) {
    alert(aDatos);
}

function fnLimpiarTabla(divTablaPadre, divTabla) {
    if (document.getElementById('' + divTablaPadre)) { // Validar  si existe elemento
        document.getElementById('' + divTablaPadre).innerHTML = '';
        $('#' + divTablaPadre).append('<div name="' + divTabla + '" id="' + divTabla + '"></div>');
    }
}

function fnCrearDatosSelect(dataJson, elementoClase = "", valor = "", valorInicial = 1) {
    var contenido = "";
    if (valorInicial == 1) {
        var contenido = "<option value='0'>Seleccionar...</option>";
    }
    for (var info in dataJson) {
        var selected = "";
        if (dataJson[info].value == valor) {
            selected = "selected";
        }
        contenido += "<option value='" + dataJson[info].value + "' " + selected + ">" + dataJson[info].texto + "</option>";
    }
    if (elementoClase == "") {
        return contenido;
    } else {
        // Si trae nombre para los datos
        $(elementoClase).empty();
        $(elementoClase).append(contenido);
        $(elementoClase).multiselect('rebuild');
    }
}

function fnSeleccionarDatosSelect(elementoClase = "", valor = "") {
    $('#'+elementoClase).selectpicker('val', '' + valor);
    $('#'+elementoClase).multiselect('refresh');
    $('.'+elementoClase).css("display", "none");
}

function fnFormatoSelectGeneral(idClase) {
    $("" + idClase).multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });
    // Estilos para el diseño del select
    $('.multiselect-container').css({
        'max-height': "200px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });
}

function fnSelectGeneralDatosAjax(nombreSelect, options, modelo, valorInicial = 1, valorSelect = "") {
    
    var mensajeError = '<option value="">Sin Datos</option>';
    // Aplicar formato para select
    fnFormatoSelectGeneral(nombreSelect);

    $(nombreSelect).empty();
    $(nombreSelect).multiselect({
        disableIfEmpty: true,
        disabledText: "Cargando datos..."
    });

    //Opcion para operacion
    dataObj = {
        option: options
    };
    //Obtener datos de las bahias
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: modelo,
        data: options
    })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            $(nombreSelect).append( fnCrearDatosSelect(data.contenido.datos, '', valorSelect, valorInicial) );
        }else{
            $(nombreSelect).append( mensajeError );
        }

        $(nombreSelect).multiselect({
            disableIfEmpty: false,
            disabledText: ""
        });

        $(nombreSelect).multiselect('rebuild');
    })
    .fail(function(result) {
        // Mensaje Error
        $(nombreSelect).append( mensajeError );

        $(nombreSelect).multiselect({
            disableIfEmpty: false,
            disabledText: ""
        });

        $(nombreSelect).multiselect('rebuild');
    });
}

function fnGeneralAutorizarAdecuacion(noCaptura, type, nameSicop, nameMap, nameFecha, tipoAdecuacion, tipoSolicitud, datosAdecuaciones=1) {
    var pSicop = $("#"+nameSicop).val();
    var fMap = $("#"+nameMap).val();
    var fecha = $("#"+nameFecha).val();
    var errorVal = 0;
    var mensaje = "";

    console.log("pSicop: "+pSicop+" - fMap: "+fMap+" - noCaptura: "+noCaptura+" - type: "+type);

    // console.log("tipoAdecuacion: "+tipoAdecuacion+" - tipoSolicitud: "+tipoSolicitud);
    if ((tipoAdecuacion == 6 || tipoAdecuacion == 7) && (pSicop.trim() == "" || fMap.trim() == "")) {
        // Validacion 6 y 7, Clase (Externas e Internas)
        mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Completar la información (P SICOP, F MAP) </p>';
        errorVal = 1;
    }else if ((tipoAdecuacion == 8 || tipoAdecuacion == 9) && (pSicop.trim() == "")) {
        // Validacion 8 y 9, Clase (Sin notificación, Movto. sólo GRP)
        mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Completar la información (P SICOP) </p>';
        errorVal = 1;
    }else if(fecha.trim() == "") {
        mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Completar la información (Fecha APL) </p>';
        errorVal = 1;
    }

    if (errorVal == 0) {
        // Validar si los registros no existen en la base de datos
        dataObj = {
            option: 'ValidarFolioSicopMap',
            pSicop: pSicop,
            fMap: fMap,
            noCaptura: noCaptura
        };
        $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType:"json",
            url: "modelo/GLBudgetsByTagV2_Panel_modelo.php",
            data:dataObj
        })
        .done(function( data ) {
            if(data.result){
                if (data.contenido.mensaje != '') {
                    mensaje = data.contenido.mensaje;
                    errorVal = 1;
                }
            }else{
                errorVal = 1;
                mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ocurrio un problema al validar P SICOP y F MAP </p>';
            }
        })
        .fail(function(result) {
            //console.log("ERROR");
            //console.log( result );
            errorVal = 1;
            mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ocurrio un problema al validar P SICOP y F MAP </p>';
        });
    }

    //console.log("errorVal: "+errorVal);
    if (errorVal == 1) {
        // if (!$('#ModalGeneral').is(':hidden')) {
        //     muestraMensajeTiempo(mensaje, 1, 'ModalGeneral_Advertencia', 5000);
        // }else{
        //     var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        //     muestraModalGeneral(3, titulo, mensaje);
        // }
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, mensaje);
        return true;
    }

    var dataJsonNoCaptura = new Array();
    var obj = new Object();
    obj.transno = noCaptura;
    obj.type = type;
    dataJsonNoCaptura.push(obj);

    //Opcion para operacion
    dataObj = {
        option: 'autorizarAdecuacion',
        pSicop: pSicop,
        fMap: fMap,
        fecha: fecha,
        noCaptura: dataJsonNoCaptura
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/GLBudgetsByTagV2_Panel_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        if (datosAdecuaciones == 1) {
            if(data.result){
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(4, titulo, data.contenido.mensaje);
            }else{
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(4, titulo, data.contenido.mensaje);
            }

            // Datos de las adecuaciones
            fnObtenerAdecuaciones();
        }else{
            var Link_Adecuaciones = document.getElementById("linkPanelAdecuaciones");
            Link_Adecuaciones.click();
        }
    })
    .fail(function(result) {
        //console.log("ERROR");
        //console.log( result );
    });
}

/**
 * Función para obtener las dependencia seleccionada y mostrar las unidades responsables
 * @param  String dependencia       Nombre del Elemento de la dependencia
 * @param  String unidadResponsable Nombre del Elemento de la unidad responsable
 * @return {[type]}                   [description]
 */
function fnCambioDependeciaGeneral(dependencia, unidadResponsable) {
    var legalid = [];
    var selectRazonSocial = document.getElementById(''+dependencia);
    for ( var i = 0; i < selectRazonSocial.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        /*if (i == 0) {
            legalid = "'"+selectRazonSocial.selectedOptions[i].value+"'";
        }else{
            legalid = legalid+", '"+selectRazonSocial.selectedOptions[i].value+"'";
        }*/
        legalid.push(selectRazonSocial.selectedOptions[i].value);
    }

    fnTraeUnidadesResponsables(legalid.join(",,,"), unidadResponsable);
}

// funcion que regresa las unidades responsables de acuerdo a la
// dependencia seleccionada
function fnTraeUnidadesResponsables(dependencia, componente) {
    muestraCargandoGeneral();
    var seleccionado = "";
    var contenido = "";
    $('#' + componente).empty();
    $('#' + componente).multiselect({
        disableIfEmpty: true,
        disabledText: "Cargando datos..."
    });
    //Opcion para operacion
    dataObj = {
        option: 'mostrarUnidadNegocio',
        legalid: dependencia
    };
    //Obtener datos de las bahias
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/GLBudgetsByTagV2_modelo.php",
        data: dataObj,
        async:false,
        cache:false
    }).done(function(data) {
        //console.log("Bien");
        if (data.result) {
            //Si trae informacion
            dataJson = data.contenido.datos;
            //console.log( "dataJson: " + JSON.stringify(dataJson) );
            //alert(JSON.stringify(dataJson));
            if (dataJson.length == 1) {
                seleccionado = " selected ";
            }
            for (var info in dataJson) {
                contenido += "<option value='" + dataJson[info].value + "'" + seleccionado + ">" + dataJson[info].texto + "</option>";
            }
            $('#' + componente).append(contenido);
            $('#' + componente).multiselect({
                disableIfEmpty: false
            });
            $('#' + componente).multiselect('rebuild');
            ocultaCargandoGeneral();
        } else {
            ocultaCargandoGeneral();
            // console.log("ERROR Modelo");
            // console.log( JSON.stringify(data) );
        }
    }).fail(function(result) {
        // console.log("ERROR");
        // console.log( result );
        ocultaCargandoGeneral();
    });
    // Fin Unidad de Negocio
}

/**
 * Función para obtener la unidad responsable seleccionada y mostrar las unidades ejecutoras
 * @param  String unidadResponsable       Nombre del Elemento de la unidad responsable
 * @param  String unidadEjecutora Nombre del Elemento de la unidad ejecutora
 * @return {[type]}                   [description]
 */
function fnCambioUnidadResponsableGeneral(unidadResponsable, unidadEjecutora) {
    var valores = [];
    var select = document.getElementById(''+unidadResponsable);
    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
            // Que no se opcion por default
        /*if (i == 0) {
            valores = "'"+select.selectedOptions[i].value+"'";
        }else{
            valores = valores+", '"+select.selectedOptions[i].value+"'";
        }*/
        valores.push(select.selectedOptions[i].value);
    }

    fnTraeUnidadesEjecutoras(valores.join(",,,"), unidadEjecutora, 1);
}

function fnTraeUnidadesEjecutoras(ur, componente, multiple=0) {

    //console.log("entro 1");
    muestraCargandoGeneral();
    var seleccionado = "";
    var contenido = "";
    var contenidoDependencia = "";
    $('#' + componente).empty();
    $('#' + componente).multiselect({
        disableIfEmpty: true,
        // disabledText: "Cargando datos..."
    });
    //Opcion para operacion
    dataObj = {
        option: 'mostrarUnidadEjecutoraGeneral',
        tagref: ur,
        multiple: multiple
    };
    //Obtener datos de las bahias
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/componentes_modelo.php",
        data: dataObj,
        async:false,
        cache:false

    }).done(function(data) {
        console.log("data.contenido.datos: "+data.contenido.datos);
        if (data.result) {
            //Si trae informacion
            dataJson = data.contenido.datos;
            //console.log( "dataJson: " + JSON.stringify(dataJson) );
            //alert(JSON.stringify(dataJson));

            var opcionDefault = 1;
            if ($("#"+componente).prop("multiple")) {
                var opcionDefault = 0;
            }

            if (dataJson.length == 1) {
                seleccionado = " selected ";
            } else if (opcionDefault == 1) {
                // Si tiene mas opciones mostrar opcion de seleccion
                contenido += "<option value='-1'>Seleccionar...</option>";
            }
            for (var info in dataJson) {
                //console.log("data info: "+dataJson[info]);
                contenidoDependencia += "<option value='" + dataJson[info].dependencia + "'>" + dataJson[info].dependencia + "</option>";
                contenido += "<option value='" + dataJson[info].value + "'" + seleccionado + ">" + dataJson[info].texto + "</option>";
            }
            console.log("contenido html UE: "+contenido);
            //$('#selectRazonSocial').html(contenidoDependencia);
            $('#' + componente).append(contenido);
            $('#' + componente).multiselect({
                disableIfEmpty: false
            });
            $('#' + componente).multiselect('rebuild');
            ocultaCargandoGeneral();
        } else {
            ocultaCargandoGeneral();
            // console.log("ERROR Modelo");
            // console.log( JSON.stringify(data) );
        }
    }).fail(function(result) {

        console.log("ERROR cargando UE");
        // console.log( result );
        ocultaCargandoGeneral();
    });
    // Fin Unidad de Negocio
}


/**
 * Función para obtener la unidad responsable seleccionada y mostrar las unidades ejecutoras
 * @param  String unidadResponsable       Nombre del Elemento de la unidad responsable
 * @param  String unidadEjecutora Nombre del Elemento de la unidad ejecutora
 * @return {[type]}                   [description]
 */
function fnCambioObjetoPrincipalGeneral(objetoPrincipal, objetoParcial) {
    var valores = [];
    var select = document.getElementById(''+objetoPrincipal);
    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
            // Que no se opcion por default
        /*if (i == 0) {
            valores = "'"+select.selectedOptions[i].value+"'";
        }else{
            valores = valores+", '"+select.selectedOptions[i].value+"'";
        }*/
        valores.push(select.selectedOptions[i].value);
    }
    console.log("valores: "+JSON.stringify(valores));
    fnTraeObjetosParcialesGeneral(valores.join(",,,"), objetoParcial);
}

function fnTraeObjetosParcialesGeneral(objPrincipal, componente,) {
    //console.log("entro 1");
    muestraCargandoGeneral();
    var seleccionado = "";
    var contenido = "";
    var contenidoDependencia = "";
    $('#' + componente).empty();
    $('#' + componente).multiselect({
        disableIfEmpty: true,
        // disabledText: "Cargando datos..."
    });
    //Opcion para operacion
    dataObj = {
        option: 'mostrarObjetoParcialGeneral',
        objPrincipal: objPrincipal
    };
    //Obtener datos de las bahias
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/componentes_modelo.php",
        data: dataObj,
        async:false,
        cache:false

    }).done(function(data) {
        if (data.result) {
            dataJson = data.contenido.datos;
            // console.log("dataJson: "+JSON.stringify(dataJson));
            var opcionDefault = 1;
            if ($("#"+componente).prop("multiple")) {
                var opcionDefault = 0;
            }

            if (dataJson.length == 1) {
                seleccionado = " selected ";
            } else if (opcionDefault == 1) {
                // Si tiene mas opciones mostrar opcion de seleccion
                contenido += "<option value='-1'>Seleccionar...</option>";
            }
            for (var info in dataJson) {
                //console.log("data info: "+dataJson[info]);
                contenido += "<option value='" + dataJson[info].value + "'" + seleccionado + ">" + dataJson[info].texto + "</option>";
            }
            
            $('#' + componente).append(contenido);
            $('#' + componente).multiselect({
                disableIfEmpty: false
            });
            $('#' + componente).multiselect('rebuild');
            ocultaCargandoGeneral();
        } else {
            ocultaCargandoGeneral();
        }
    }).fail(function(result) {
        console.log( result );
        ocultaCargandoGeneral();
    });
}

// funcion general para mostrar los botones de accion
// de acuerdo a los permisos y funcion seleccionada
function fnObtenerBotones_Funcion(divMostrar, fnFuncion) {
    //Opcion para operacion
    dataObj = {
            option: 'obtenerBotones',
            type: '',
            funcionid: fnFuncion
          };
    //Obtener datos de las bahias
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data:dataObj
      })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            info = data.contenido.datos;
            dataObjDatosBotones = data.contenido.datos;
            //console.log("presupuesto: "+JSON.stringify(info));
            var contenido = '';

            for (var key in info) {
                var funciones = '';
                funciones = 'fnCambiarEstatus('+info[key].statusid+')';

                contenido += '&nbsp;&nbsp;&nbsp; \
                <button type="button" id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" \
                onclick="'+funciones+'" class="btn btn-default botonVerde '+info[key].clases+'">&nbsp;'+
                info[key].namebutton+'</button>';
            }

            $('#'+divMostrar).append(contenido);
            //fnEjecutarVueGeneral();
        }else{
            muestraMensaje('No se obtuvieron los botones para realizar las operaciones', 3, 'divMensajeOperacion', 5000);
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

function fnVentana(id){
    alert(id);

    $( "#"+id ).dialog(

                {
    resizable: false,
    height: 200,
    autoOpen: false,
    modal: true,
    buttons: {
        "Delete":function(){
            __doPostBack("delete", "");
            $(this).dialog("close");
            },
        Cancel: function() {
            $(this).dialog("close");
        }
    }}
        );


}

// funcion para validar datos de la requisicion
function fnValidarRequisicion(idr, ur){
    var validaReq = "";

    dataObj = {
            option: 'validarRequisicion',
            idReq: idr,
            ur: ur
        };

    $.ajax({
        method: "POST",
        dataType: "json",
        url: "./modelo/PO_SelectOSPurchOrder_modelo.php",
        async:false,
        cache:false,
        data: dataObj
    }).done(function(data) {
        if(data.result){
            validaReq = true;
        }else{
            validaReq = false;
            //muestraMensaje(data.contenido, 3, 'divMensajeOperacion');
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', data.contenido);
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });

    return validaReq;
}

function fnCerrarTableRequi(){
    location.replace("./PO_SelectOSPurchOrder?");
}

/*$(document).on('click','#btnGeneraNoExistencia',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalGenerarNoExist');
});
$(document).on('click','#btnActualizaNoExistencia',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalGenerarNoExist');
});

$(document).on('click','.cerrarModalGenerarNoExist',function(){
    //location.replace("./panel_no_existencias.php?");
    var encUrl = $('#urlEncriptadaRequisicionInput').val();
    //console.log(encUrl);
    //location.replace("./Captura_Requisicion_V_3.php?"+encUrl);
    $("#btnGeneraNoExistencia").prop("readonly", true);
    $('#btnGeneraNoExistencia').attr('disabled', 'disabled');
    $('#btnGeneraNoExistencia').removeAttr("onclick");
    window.open('./panel_no_existencias.php?','_blank');
});

$(document).on('click','#btnGeneraSolAlmacen',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalGenerarSolAlmacen');
});
$(document).on('click','#btnActualizaSolAlmacen',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalGenerarSolAlmacen');
});

$(document).on('click','.cerrarModalGenerarSolAlmacen',function(){
    //location.replace("./almacen.php");
    var encUrl = $('#urlEncriptadaRequisicionInput').val();
    //console.log(encUrl);
    location.replace("./Captura_Requisicion_V_3.php?"+encUrl);
    window.open('./almacen.php','_blank');
    window.open('./panel_no_existencias.php','_blank');
});

$(document).on('click','.closeTable',function(){
    $("#idTableReqValidacion").css('display','none');
    $("#idTableHeader").empty();
    $("#idTableContReq").empty();
    $("#idTableReqBotones").empty();
});
$(document).on('click','#idBtnCloseTable',function(){
    $("#idTableHeader").empty();
    $("#idTableContReq").empty();
    $("#idTableReqBotones").empty();
});
*/
function isEmpty(val){
    return (val === undefined || val == null || val === "" || val === null || val == "" || isNaN(val)) ? true : false;
}
function fnCompararPresupuesto(dataReq =[]){
    var clavepresupuestal=[];
    var presupuestoActual=[];
    var datosverificar=[];
    var descuento=[];
    var nuevo=[];
    var descuento=[];
    var disponibleRestante=0;
    var contador=0;
    var existe=0;
    for (a in dataReq){
       clavepresupuestal.push(dataReq[a].clavepre);
       presupuestoActual.push( dataReq[a].pptoActual);
       descuento.push(dataReq[a].tot);
    }

    for(a in clavepresupuestal){
        cantidaddescontar=0;
        disponibleRestante=0;

        /*for (d in clavepresupuestal) {
                        if (clavepresupuestal[d] == clavepresupuestal[a]) {
                        //cantidaddesconta=  descuento[d];

                        if(contador==0){
                        disponibleRestante=(presupuestoActual[d]-descuento[d]);
                           }else{
                            disponibleRestante=(disponibleRestante-descuento[d]);
                           }

                        contador++;

                    }

                }*/
           //console.log(clavepresupuestal+"clavepresupuestal");
           //console.log(clavepresupuestal[a]+"clavepresupuestal");

           existe=     fnChecarExistencia(clavepresupuestal,clavepresupuestal[a] );
           if(existe[0]==1){
             resta=presupuestoActual[a]-descuento[a];
             //console.log(resta +"RESTA antes de comparar");
             //if(resta>0){
                resta=resta;
             /*}else{
                resta=0;
             } */ //evita cantidades negativas
             //nuevo.push(clavepresupuestal[a]+"|pre"+presupuestoActual[a]+"|res2-- "+(resta)+"|"+descuento[a]);
             nuevo.push((resta)+"|"+a);

             //console.log(resta +"RESTA");

         }else{
            posiciones=existe[1];
            for(f in posiciones){
                    posicion=posiciones[f];
               if(f==0){
                        disponibleRestante=(presupuestoActual[posicion]-descuento[posicion]);

                }else{
                            disponibleRestante=(disponibleRestante-descuento[posicion]);

                }

                if(disponibleRestante>0){disponibleRestante=disponibleRestante}else{disponibleRestante=0;}


                           /*existe2=  fnChecarExistencia2(nuevo,(clavepresupuestal[posicion]+"|pre"+presupuestoActual[posicion]+"|res2-- "+(disponibleRestante)+"|"+descuento[posicion]));

                          if(existe2==0){
                            nuevo.push(clavepresupuestal[posicion]+"|pre"+presupuestoActual[posicion]+"|res2-- "+(disponibleRestante)+"|"+descuento[posicion]);

                          }*/
                nuevo.push( (disponibleRestante)+"|"+posicion);
          //nuevo.push(clavepresupuestal[posicion]+"|pre"+presupuestoActual[posicion]+"|res2-- "+(disponibleRestante)+"|"+descuento[posicion]);

            }

         }

    }

    var presupuestalesUnicas = [];
    $.each(nuevo, function(i, el){
        if($.inArray(el,presupuestalesUnicas) === -1) presupuestalesUnicas.push(el);
    });
    for(d in presupuestalesUnicas){
        console.log(presupuestalesUnicas[d]);

    }

    /*for (d in nuevo){
        console.log(nuevo[d]);

    }*/

    return presupuestalesUnicas;
}


function fnChecarExistencia(arreglo, valor) {
    var contarexistencia = 0;
    var posiciones=[];
    var retorno=[];
    for (var x in arreglo) {
        if (arreglo[x] == valor) {

            contarexistencia++;
            posiciones.push(x);
        }
    }
    retorno.push(contarexistencia,posiciones);
    return retorno;
}
function fnChecarExistencia2(arreglo, valor) {
    var contarexistencia = 0;
    for (var x in arreglo) {
        if (arreglo[x] == valor) {

            contarexistencia++;
        }
    }
    return contarexistencia;
}

/**
 * Función que valida si el registro es un número
 * @param  integer Parametro para validar
 * @return boolean Regresa true si es número si no false
 */
function fnValidarSiEsNumero(numero){
    var RE = /^\d*\.?\d*$/;
    if (RE.test(numero)) {
        return true;
    } else {
        return false;
    }
}

/**
 * [fnsoloDecimalesGeneral Funcion para validar solo numeros decimales]
 * @param  {[type]} evt   Evento onkeypress
 * @param  {[type]} input Caja de Texto
 */
function fnsoloDecimalesGeneral(evt,input){
    // Backspace = 8, Enter = 13, ‘0′ = 48, ‘9′ = 57, ‘.’ = 46, ‘-’ = 43
    // Eliminar caracteres seleccionados del input
    var text = input.value;
    text = text.slice(0, input.selectionStart) + text.slice(input.selectionEnd);
    input.value = text;
    var key = window.Event ? evt.which : evt.keyCode;
    var chark = String.fromCharCode(key);
    var tempValue = input.value+chark;

    if ( $( input ).hasClass( "porcentaje" ) ) {
        // Es el elemento es para porcentaje, no pasar de 100
        if (Number(tempValue) > Number(100)) {
            // Es mayor a 100
            return false;
        }
    }
    
    if(key >= 48 && key <= 57){
        if(fnFiltrarDecimalesGeneral(tempValue)=== false){
            return false;
        }else{
            return true;
        }
    }else{
          if(key == 8 || key == 13 || key == 0) {
              return true;
          }else if(key == 46){
                if(fnFiltrarDecimalesGeneral(tempValue)=== false){
                    return false;
                }else{
                    return true;
                }
          }else{
              return false;
          }
    }
}

/**
 * [fnFiltrarDecimalesGeneral Validar el Formato]
 * @param  {[type]} __val__ Recibe el Valor de la Caja de Texto
 */
function fnFiltrarDecimalesGeneral(__val__){
    var preg = /^(([0-9]{0,17})+\.?[0-9]{0,2})$/;
    if(preg.test(__val__) === true){
        return true;
    }else{
       return false;
    }
}

window.onload = initial;

// prevencion de submit de las formas en los botones
//$(document).on('click','button',function(e){ e.preventDefault(); });

/*
 * Funcion para la obtencion de los paramatros de una forma donde es necesario
 * que cada uno de los datos que se desea obtener contengan nombre.
 * @params: id Identificador de la o lugar de donde se sacaran los datos.
 * @return: Object datos obtenidos de la forma.
 * @date: 26.12.2017
 * @author: JP
 */
function getParams(id) {
    var params = {};
    $('#'+id).find('input[name], select[name], textarea[name]').each(function(index, el) {
        var $self = $(this);
        //if($self.val() !== undefined) {
            params[$self.attr('name')] = $self.val();
        //}    
    });
    return params;
}


function getParamsSeveralIds(arrIds) {
    var params = {};
    var newArrIds = arrIds.join(",");
    $(newArrIds).find('input[name], select[name], textarea[name]').each(function(index, el) {
        var $self = $(this);
        if($self.val()=="on") {
            $self.val(1);
        }
        if($self.val() !== undefined) {
           params[$self.attr('name')] = $self.val().trim();
        }   
    });
    return params;
}

/*
 * Funcion para la obtencion de los campos que se pueden comparar de un formulario dado.
 * @params: id Identificador de la o lugar de donde se sacaran los datos.
 * @return: Object datos obtenidos de la forma.
 * @date: 26.12.2017
 * @author: JP
 */
function getMatchets(id) {
    var params = [];
    $('#'+id).find('input[name], select[name], textarea[name]').each(function(index, el) {
        var $self = $(this);
        params.push($self.attr('name'));
    });
    return params;
}

/*
 * Función que realiza la validación de los datos de una forma contra lo que se espera evaluar
 * es decir la existencia de datos mas no de tipos de datos o contenido en especifico.
 * @param: form Objeto con los datos que serán comparados
 * @param: matchets objeto que se toma como referencia para la existencia de datos.
 * @return: flag Indicador de existencia de algún incidente o inconsistencia.
 * @date: 26.12.2017
 * @author: JP
 */
function validForm(form, matchets) {
    if($.isEmptyObject(form) || $.isEmptyObject(matchets))
        return false;
    var flag = true;
    $.each(form,function(index, el) {
        if(matchets.indexOf(index) != -1){
            if($.isEmptyObject(el)){
                flag = false;
                $('#'+index).addClass('form-control-error');
                $('#'+index).parents('.form-group').removeClass('has-success').addClass('has-error');
            }else if($.isArray(el) && el.length == 1){
                if($.isEmptyObject(el[0])){
                    flag = false;
                    $('#'+index).addClass('form-control-error');
                    $('#'+index).parents('.form-group').removeClass('has-success').addClass('has-error');
                }
            }else{
                $('#'+index).removeClass('form-control-error').addClass('form-control-success');
                $('#'+index).parents('.form-group').removeClass('has-error')/*.addClass('has-success')*/;
            }
        }
    });
    return flag;
}

/**
 * Funcion para la generacion de codigo html en un archivo javascript
 * Ejemplo:
 * 1.- generateItem('a',{href:'http://youtube.com',html:'ir a sitio'})
 *     esta ejecucion generaria una etiqueta <a href="http://youtube.com">ir a sitio</a>
 * 2.-
 *   var elementos = ['Cras','justo','odio'], lis = [];// variable contenedora de los <li></li>
 *   // se itera en los elementos que se quiere generar
 *   $.each(elmentos,function(index,elemento){
 *     // se genera el elemento <li></li>
 *     var li = generateItem('li', {css : 'list-group-item',html : elemento});
 *     // se agrega el <li></li> que se genero al arreglo
 *     lis.push(li);
 *   });
 *   // se genera el <ul>{elementos}</ul>
 *   generateItem('ul',{css:'list-group'},lis);
 *
 *   Resultado de la ejecucion
 *   <ul class="list-group">
 *      <li class="list-group-item">Cras</li>
 *      <li class="list-group-item">justo</li>
 *      <li class="list-group-item">odio</li>
 *    </ul>
 * @param  string tag     Etiqueta que se quiere generar
 * @param  object options Objeto con las opciones deceadas
 * @param  array append   Arreglo con los elementos que se desean agregar
 * @return jQueryElement  Elemento generado con las caracteristicas necesarias
 */
function generateItem(tag,options,append='',prepend=false) {
  var $el = $('<'+tag+'>',options);
  if(!$.isEmptyObject(append)){
    if($.isArray(append)){
      append.forEach(function(el, index){
        if(prepend){
          $el.prepend(el);
        }
        else{
          $el.append(el);
        }
      });
    }
    else{
      if(prepend){
        $el.prepend(append);
      }else{
        $el.append(append);
      }
    }
  }
  return $el;
}

/**
 * Funnción para la aplicación de estilos de multiselect
 * ademas de la consulta y carga de los datos que corresponden
 * segun lo indicado en el metodo.
 * @param  string element   Elemento al que se plicara la información obtenida
 * @param  string method    De donde se obtiene la información
 * @param  string params    información necesaria para la extraccion de los datos
 * NOTA: Es necesario que la informacion regresada contenga la
 * siguiente estructura:
 *     response :{
 *         succes : true||false,
 *         content : [
 *             {
 *                 label : 'lo que se muestra en el option',
 *                 value : 'valor del option a generar',
 *                 title : 'titulo que se le pondra al hover del select'
 *             }
 *         ]
 *     }
 * @date: 23.03.18
 * @author Desarrollo
 */
function aplicaSelectVue() {
    var data=arguments||[];
    if($.isEmptyObject(data) && data.length < 3){ return false; }
    data[2] = $.extend({}, { method:'obtenContenidoSelect' }, data[2]);

    var $el = $(data[0]);
    $.post(data[1],data[2])
    .then(function(res){
        fnFormatoSelectGeneral(data[0]);
        if(!res.success){ return false; }
        $el.multiselect('dataprovider',res.content);
    });
}


function telefono(e) {
    var tecla = (document.all) ? e.keyCode : e.which;
    //console.log("entra con el dato:"+e);

    //Tecla de retroceso para borrar, siempre la permite
    if (tecla==8){
        return true;
    }

    if (tecla == 45 || tecla== 43 || tecla==231 || tecla==46) {
        return false;
    }

    if (/[!òó+ç]/.test(tecla)) {
        return false;
    }

    // Patron de entrada, en este caso solo acepta numeros
    var patron =/[0-9-.]/;
    var tecla_final = String.fromCharCode(tecla);

    return patron.test(tecla_final);
}
function fnValidarPrimeraPosicionLetra(e) {
   
   var anterior='';
   anterior=e.target.value;
   var longitud=anterior.length;
   var flag=true;
   var tecla = (document.all) ? e.keyCode : e.which;
    
    if (tecla==8){
        return true;
    }

    if ( tecla== 45 ||  tecla== 43 || tecla==231 || tecla==46) {
        return false;
    }

    if (/[!òó+ç]/.test(tecla)) {
        return false;
    }
    if(anterior!='' && longitud==1){
    // Patron de entrada, en este caso solo acepta numeros
    var patron = /^[a-zA-Z]/; 
    var tecla_final =e.target.value; 
    
    flag=patron.test(tecla_final);
    console.log(flag);
    if(flag==true){
        flag=true;
      
    }else{
     
       e.target.value="";
       flag=false;
     }
    }else if(anterior!='' && longitud>=1){
       var patron = /[0-9]/; 
    var tecla_final =String.fromCharCode(tecla);
    
    flag=patron.test(tecla_final);
    }
    return flag;
}

/**
 * Funcion para redondear a dos o mas decimales segun los parametros enviados
 * @param  {[type]} cantidad    Cantidad que quiere ser redondeada
 * @param  {[type]} decimales   Cantidad de decimales alos que sera redondeada la cantidad
 * @return {[type]}             Cantidad procesada
 */
function redondeaDecimal(cantidad, decimales) {
    var decimales = decimales||2;
    return Number(parseFloat(cantidad).toFixed(decimales));
}
/**
 * Funcion para restringir la cantidad de caracteres numericos
 * que puede admitir un campo.
 * @NOTE: Es necesario colocar la función en el evento key up
 * @param  {Interface Event} e    Evento que acciona la función
 * @param  {Integer}         len  Longitud de los caracteres aceptados
 * @return {String}               Cadena formateada
 */
function maxLongDecUp(e,len) {
    var elemento = e.target ,tecla = e.hasOwnProperty('keyCode') ? e.keyCode : e.which , valor = elemento.value.replace(/\,/g, "")
        ,valLength = valor.length ,oldText = valor.split('') ,len = len||8 ,reg;
    oldText.splice(valLength-1,1);
    oldText = oldText.join('');
    if(tecla==8){ return true; }
    var reg = new RegExp("^([0-9]{0,"+len+"}\\.?[0-9]{0,2})$");
    elemento.value = reg.test(valor)?valor:oldText;
    return reg.test(valor);
}
/**
 * Funcion para el formateo de una cantidad dada.
 * @param  {String} str Cadena que sera formateada
 * @return {String}     Cadena con el formato aplicado
 */
function formatoComas(str) {
    return parseFloat(str).toLocaleString('en',{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    });
}
/**
 * Funcion para la colocar dos decimales a una cantidad dada.
 * @param  {Integer} cantidad  Cantidad que se desea formatear
 * @param  {Integer} decimales Numero de decimales que se le colocaran
 * @return {String}            Dato formateado
 */
function fixDecimales(cantidad,decimales) {
    var cantidad = cantidad||0,
        decimales = decimales||2;
        cantidad = cantidad.replace(/\,/g,'');
    return parseFloat(cantidad).toFixed(decimales);
}

/**
 * Función para obtener la descripción de la partida genérica
 * @param  {[type]} partidaGenerica Partida Genérica a 3
 * @return {[type]}                 Descripción de la Partida Genérica
 */
function fnInformacionPartidaGenericaGeneral(partidaGenerica) {
    // Obtiene la descripcion de la partida generica
    var descripcionPartida = '';
    dataObj = { 
        option: 'muestraDesPartidaGenerica',
        partidaGenerica: partidaGenerica
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            descripcionPartida = data.contenido;
        }else{
            descripcionPartida = '';
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Partida Genérica no se encuentra en el Catálogo</p>');
        }
    })
    .fail(function(result) {
        //console.log("ERROR");
        //console.log( result );
    });

    return descripcionPartida;
}

/**
 * Función para obtener el número de niveles de una cuenta contable
 * @date: 16.05.18
 * @param  string accountcode Cuenta Contable
 * @return [type]              Número de niveles
 */
function fnNivelesCuentaContableGeneral(accountcode = '') {
    // Se regresa el numero de niveles
    return accountcode.split(".").length;
}

/**
 * Función que regresa la cuenta contable al nivel que se requiera
 * @date: 16.05.18
 * @param  string accountcode Cuenta Contable
 * @param  int niveles Número del Nivel
 * @return [type]              Cuenta Contable
 */
function fnObtenerCuentaContableNivel(accountcode, niveles = 4) {
    // Funcion que regresa la cuenta contable al nivel que se requiera
    var separacion = accountcode.split(".");
    var num = 1;
    var claveFormada = '';
    for (var i in separacion) {
        if (claveFormada == '') {
            claveFormada = separacion[i];
        } else {
            claveFormada += '.'+separacion[i];
        }

        if (num == niveles) {
            break;
        }

        num ++;
    }

    return claveFormada;
}

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!                                                                           !!
//!!                             PATRIMONIO                                    !!
//!!                                                                           !!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

function fnObtenerOptionEmpleados(ur,ue,componente){
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data:{option:'muestraEmpleados',ur:ur,ue:ue}
    }).done(function(res){
        if(!res.result){return;}
        var options='';

        options = '<option value="-1">Sin Seleccion...</option>';

        $.each(res.contenido,function(index, el) {
            options += '<option value="'+el.val+'">'+el.text+'</option>';
        });

        $('#' + componente).empty();
        $('#' + componente).append(options);
        $('#' + componente).multiselect('rebuild');
    }).fail(function(res){
        throw new Error('No se logro cargar la información del componente empleados');
    });
}

function fnObtenerActivoFijos(ur,ue,componente){
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data:{option:'muestraPatrimonio',ur:ur,ue:ue}
    }).done(function(res){
        if(!res.result){return;}
        var options='';
        
        options = `<option value='-1'>Sin Seleccion...</option>`;

        $.each(res.contenido,function(index, el) {
            options += '<option value="'+el.val+'">'+el.text+'</option>';
        });

        $('#' + componente).empty();
        $('#' + componente).append(options);
        $('#' + componente).multiselect('rebuild');
    }).fail(function(res){
        throw new Error('No se logro cargar la información del componente activos fijos');
    });
}

function fnObtenerAlmacenes(ur,ue,componente){
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data:{option:'mostrarAlmacen',ur:ur,ue:ue}
    }).done(function(res){
        if(!res.result){return;}
        var options='';
        
        if(! $("#"+componente).prop("multiple")){
            options = '<option value="-1">Seleccionar...</option>';
        }
        

        $.each(res.contenido.datos,function(index, el) {
            options += '<option value="'+el.loccode+'">'+el.locationname+'</option>';
        });

        console.log(options);
        console.log(componente);
        $('#' + componente).empty();
        $('#' + componente).append(options);
        $('#' + componente).multiselect('rebuild');
    }).fail(function(res){
        throw new Error('No se logro cargar la información del componente activos fijos');
    });
}

function fnBloquearDivs(componente){
    $('#' + componente).block({
        message:null, overlayCSS : { 
            opacity: '.01',
            cursor: 'default'
        } 
    });
}

function fnLimpiarCamposForm(componente){
    $('#'+componente+' input[type="text"], textarea').each(
        function(index){  
            var input = $(this);
            input.val('');
        }
    );

    $('#'+componente+' select').each(
        function(index){  
            var input = $(this);
            input.val('-1');
            input.multiselect('rebuild');
        }
    );
}

// Alternativa a la función anterior pero incluyendo también los campos hidden
function fnLimpiarCamposFormConHidden(componente){
    $('#'+componente+' input[type="text"], input[type="hidden"], textarea').each(
        function(index){  
            var input = $(this);
            input.val('');
        }
    );

    $('#'+componente+' select').each(
        function(index){  
            var input = $(this);
            input.val('-1');
            input.multiselect('rebuild');
        }
    );
}

// funcion que regresa los productos y los regresa en arreglo de tipo json
function fnTraeProductosBusqueda () {
    var valores= new Array();
   
    dataObj = { 
        option: 'traeProductosBusqueda'
    };

    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data: dataObj
    })
    .done(function(data) {
        if (data.result) {
            valores=data.contenido;
        } else {
                           
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log(result);
        ocultaCargandoGeneral();
    });
   
    return valores;
}

/**
 * Función para generar y descargar csv
 * Ejemplo:
 * = '[{"Id":1,"UserName":"Sam Smith"},{"Id":2,"UserName":"Fred Frankly"},{"Id":1,"UserName":"Zachary Zupers"}]';
 * @param  {[type]} objArray Información a recorrer
 * @param  {String} name     Nombe de archivo
 * @return {[type]}          [description]
 */
function fnGenerarCsvGeneral(objArray, name = 'csv') {
    // console.log("objArray: "+objArray);
    var csv = fnGeneraCsvJsonGeneral(objArray);
    var downloadLink = document.createElement("a");
    var blob = new Blob(["\ufeff", csv]);
    var url = URL.createObjectURL(blob);
    downloadLink.href = url;
    downloadLink.download = name+".csv";

    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

/**
 * Función para generar estructura csv
 * @param  {[type]} objArray Informacion a recorrer
 * @return {[type]}          [description]
 */
function fnGeneraCsvJsonGeneral(objArray) {
    // console.log("objArray: "+JSON.stringify(objArray));
    var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
    var str = '';
    var line = '';
    // console.log("array: "+JSON.stringify(array));
    if ($("#labels").is(':checked')) {
        var head = array[0];
        if ($("#quote").is(':checked')) {
            for (var index in array[0]) {
                var value = index + "";
                line += '"' + value.replace(/"/g, '""') + '",';
            }
        } else {
            for (var index in array[0]) {
                line += index + ',';
            }
        }

        line = line.slice(0, -1);
        str += line + '\r\n';
    }

    for (var i = 0; i < array.length; i++) {
        var line = '';

        if ($("#quote").is(':checked')) {
            for (var index in array[i]) {
                var value = array[i][index] + "";
                line += '"' + value.replace(/"/g, '""') + '",';
            }
        } else {
            for (var index in array[i]) {
                line += array[i][index] + ',';
            }
        }

        line = line.slice(0, -1);
        str += line + '\r\n';
    }
    return str;
}

/**
 * Función para generar el Excel del presupuesto
 * @param  {Number} type    Tipo de documento
 * @param  {Number} transno Folio de la operación
 * @return {[type]}         [description]
 */
function fnExcelPresupuestoGeneral(type = '', transno = '', tipo = 'Pdf') {
    // Función para generar excel con informacion del presupuesto
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

    if (type == '' || transno == '') {
        // Se ejecuta función sin parametros
        muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No es posible mostrar el '+tipo+'</p>');
        return true;
    }

    // Realizar operaciones para generar Excel
    var urlLink = fnGenerarUrlEncriptada(type, transno, tipo);
    if (urlLink != '') {
        // Si trae URL
        window.open(""+urlLink, "_blank");
    }
}

/**
 * Función para generar la URL encriptada y poder mostrar PDF o EXCEL
 * @param  {[type]} type    Tipo de docuemento
 * @param  {[type]} transno Folio de operación
 * @param  {[type]} tipo    Tipo de impresion (Excel o Pdf)
 * @return {[type]}         URL para mostrar información
 */
function fnGenerarUrlEncriptada(type, transno, tipo) {
    // Función para encriptar url y mostrar url para descarga
    var mensaje = '';
    dataObj = { 
        option: 'urlPresupuesto',
        type: type,
        transno: transno,
        tipo: tipo
    };
    //Obtener datos de las bahias
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            mensaje = data.contenido;
        }else{
            //ocultaCargandoGeneral();
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo, data.contenido.mensaje);
        }
    })
    .fail(function(result) {
        ocultaCargandoGeneral();
        //console.log("ERROR");
        //console.log( result );
    });

    return mensaje;
}

/**
 * Función para mostrar un listado de los proveedores
 * @param  {String} inputSeleccion Id del input donde se va a código del proveedor
 * @param  {String} inputLista     Id del input donde se va a poner la información de la lista
 * @param  {String} tipo           Tipo de información a obtener
 * @return {[type]}                [description]
 */
function fnListaInformacionGeneral(inputSeleccion = "", inputLista = "", tipo = "") {
    dataObj = { 
            option: 'obtenerInfoListaGeneral',
            tipo: tipo
          };
    $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/componentes_modelo.php",
      data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        if(data.result) {
            fnListaGeneralMostrar(data.contenido.datos, inputSeleccion, inputLista);
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

/**
 * Función para poner lista de información en un input, General
 * @param  {[type]} jsonData       Json con la información a visualizar
 * @param  {String} inputSeleccion Id del input donde se va a código del proveedor
 * @param  {String} inputLista     Id del input donde se va a poner la información de la lista
 * @return {[type]}                [description]
 */
function fnListaGeneralMostrar(jsonData, inputSeleccion = "", inputLista = "") {
    $( ""+inputLista ).autocomplete({
        source: jsonData,
        select: function( event, ui ) {
            $( this ).val( ui.item.texto + "");
            $( ""+inputSeleccion ).val( ui.item.value );
            $( ""+inputLista ).val( ui.item.texto );
            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
        .append( "<a>" + item.texto + "</a>" )
        .appendTo( ul );
    };
}

/**
 * Función para cargar las configuraciones del tipo del presupuesto
 * @param  {String} tipoPresup      Tipo de presupuesto
 * @param  {String} idElementConfig Id del elemento a cargar las configuraciones
 * @return {[type]}                 [description]
 */
function fnConfigClavePresupuesto(tipoPresup = '', idElementConfig = '') {
    if(idElementConfig == '' || idElementConfig == null || idElementConfig == 'undefined') {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Se encuentra vacío el parametro para visualizar la información</p>';
        muestraModalGeneral(3, titulo, mensaje);
    } else {
        muestraCargandoGeneral();
        $("#"+idElementConfig).empty();
        $("#"+idElementConfig).append('<option value="-1">Seleccionar</option>');
        
        dataObj = { 
            option: 'mostrarConfiguracionClave',
            tipo: tipoPresup
        };

        $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType:"json",
            url: "modelo/componentes_modelo.php",
            data:dataObj
        })
        .done(function( data ) {
            //console.log("Bien");
            if(data.result){
                //Si trae informacion
                dataJsonConfiguracionClave = data.contenido.datos;
                //console.log("datos: "+JSON.stringify(dataJsonConfiguracionClave));
                
                $("#"+idElementConfig).append( fnCrearDatosSelect(dataJsonConfiguracionClave, "", "", 0) );
                // fnFormatoSelectGeneral("#"+idElementConfig);
                $("#"+idElementConfig).multiselect('rebuild');
            }else{
                console.log("ERROR Modelo");
                console.log( JSON.stringify(data) );
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ocurrio un problema al traer la configuración de la clave presupuestal</p>';
                muestraModalGeneral(3, titulo, mensaje);
            }
            ocultaCargandoGeneral();
        })
        .fail(function(result) {
            ocultaCargandoGeneral();
            console.log("ERROR");
            console.log( result );
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ocurrio un problema al traer la configuración de la clave presupuestal</p>';
            muestraModalGeneral(3, titulo, mensaje);
        });
    }
}

/**
 * Función para limpiar el contenido de un div
 * @param  {String} idDiv Id del elemento
 * @return {[type]}       [description]
 */
function fnLimpiarContenidoDiv(idDiv = '') {
    if(idDiv.trim() != '' && idDiv.trim() != null && idDiv.trim() != 'undefined') {
        $("#"+idDiv).empty();
    }
}

/**
 * Función para cambiar el tamaño de las columnas de los grids de forma automática
 * @param   {[string]}  tabla               Nombre del DIV que contiene el grid
 * @param   {[object]}  propiedadesResize   Objeto con la siguiente estructura:
 * var propiedadesResize = {
                            widthsEspecificos: {
                                // Nombre del datafield y el ancho que se desea que tenga
                                categoryid:     "7%",
                                nu_tipo_gasto:  "5%",
                                modificar:      "7%"
                            },
                            encabezadosADosRenglones: {
                                // Nombre del datafield y el texto que se desea mostrar en dos renglones
                                categoryid:         "Partida<br />Genérica",
                                nu_tipo_gasto:      "Tipo<br />Gasto"
                            },
                            camposConWidthAdicional: [
                                // Nombre del datafield al que se desea agregar tamaño adicional para que el ancho del grid llegue a 100%
                                "stockact",
                                "accountegreso",
                                "adjglact",
                                "ln_abono_salida"
                            ]
                        };
 */
function fnGridResize(tabla,propiedadesResize){
    // Se aumenta la altura del header para los encabezados a doble línea y se llama al auto resize de columnas
    $("#"+tabla).jqxGrid('columnsheight', '33px');
    $("#"+tabla).jqxGrid('autoresizecolumns');

    // Se aplican los widths específicos para columnas en caso de que los haya
    if(propiedadesResize.hasOwnProperty("widthsEspecificos")){
        $.each(propiedadesResize.widthsEspecificos,function(index, val) {
            $("#"+tabla).jqxGrid('setcolumnproperty', index, 'width', val);
        });
    }

    // Se aplican los renderers específicos para columnas en caso de que los haya
    if(propiedadesResize.hasOwnProperty("encabezadosADosRenglones")){
        $.each(propiedadesResize.encabezadosADosRenglones,function(index, val) {
            $("#"+tabla).jqxGrid('setcolumnproperty', index, 'renderer', function () {
                return '<div style="margin-top: 0px; margin-left: 0px; text-align: center;">'+val+'</div>';
            });
        });
    }

    // Resize adicional para los casos cuando el ancho de las columnas sea menor al 100%
    setTimeout(function() {
        var anchoGrid = $("#"+tabla).innerWidth(),
            columnas = $("#"+tabla).jqxGrid('columns').records,
            anchoColumnas = 0,
            camposWidthAdicional = 0;
        $.each(columnas,function(index, el) {
            if(!$("#"+tabla).jqxGrid('getcolumnproperty', el['datafield'], 'hidden')){
                anchoColumnas += $("#"+tabla).jqxGrid('getcolumnproperty', el['datafield'], 'width');
            }
            if(Array.isArray(propiedadesResize.camposConWidthAdicional)){
                if(propiedadesResize.camposConWidthAdicional.indexOf(el['datafield'])>=0){
                    camposWidthAdicional++;
                }
            }
        });
        if(anchoColumnas<anchoGrid&&camposWidthAdicional){
            for(var c=0;c<camposWidthAdicional;c++){
                var nuevoAnchoColumna = $("#"+tabla).jqxGrid('getcolumnproperty', propiedadesResize.camposConWidthAdicional[c], 'width')+((anchoGrid-anchoColumnas)/camposWidthAdicional);
                $("#"+tabla).jqxGrid('setcolumnproperty', propiedadesResize.camposConWidthAdicional[c], 'width', nuevoAnchoColumna);
            }
        }
    }, 500);
}

// Funciones para campos de listado
function ordenamientoDinamico(propiedadAOrdenar) {
    var ordenClasificacion = 1;
    if(propiedadAOrdenar[0] === "-") {
        ordenClasificacion = -1;
        propiedadAOrdenar = propiedadAOrdenar.substr(1);
    }
    return function (a,b) {
        var resultado = (a[propiedadAOrdenar] < b[propiedadAOrdenar]) ? -1 : (a[propiedadAOrdenar] > b[propiedadAOrdenar]) ? 1 : 0;
        return resultado * ordenClasificacion;
    }
}

function nivelDeCuenta(cuenta){
    cuenta += "";
    subString = ".";
    if (subString.length <= 0) return (cuenta.length + 1);

    var n = 0,
        pos = 0,
        step = subString.length;

    while (true) {
        pos = cuenta.indexOf(subString, pos);
        if (pos >= 0) {
            ++n;
            pos += step;
        } else break;
    }
    return n+1;
}

function administraArregloBusqueda(buscar,idObjeto){
    var arregloBusqueda = window[window.buscadoresConfiguracion[idObjeto].origenDatos],
        registrosEncontrados = jQuery.map(arregloBusqueda, function (value,index) {
        return fnListadoValorMostrado(value,idObjeto).match(fnListadoTipoBusqueda(buscar,idObjeto))&&value.nivel==nivelDeCuenta(buscar)+1 ? index : null;
    }).length;

    if(registrosEncontrados==0&&nivelDeCuenta(buscar)<9&&buscar.substring(buscar.length-1)=="."){
        $.ajaxSetup({async: false, cache: false});
        $.post("modelo/componentes_modelo.php", {option: 'consultaDinamicaListado'+window.buscadoresConfiguracion[idObjeto].origenDatos.substr(0,1).toUpperCase()+window.buscadoresConfiguracion[idObjeto].origenDatos.substr(1), cuenta: buscar, nivel: nivelDeCuenta(buscar)+1, cuentasEnObjeto: registrosEncontrados}).then(function(res){
            if(res.success){
                window[window.buscadoresConfiguracion[idObjeto].origenDatos] = window[window.buscadoresConfiguracion[idObjeto].origenDatos].concat(res.cuentasEncontradas);
                window[window.buscadoresConfiguracion[idObjeto].origenDatos].sort(ordenamientoDinamico("valor"));
                //window.localStorage[window.buscadoresConfiguracion[idObjeto].origenDatos] = JSON.stringify(window[window.buscadoresConfiguracion[idObjeto].origenDatos]);
            }
        });
    }
}

function fnListadoValorMostrado(registro,idObjeto){
    var valorMostrado = "";

    valorMostrado = ( !window.buscadoresConfiguracion[idObjeto].valorAMostrar||window.buscadoresConfiguracion[idObjeto].valorAMostrar=="id" ? registro.valor : valorMostrado );
    valorMostrado = ( window.buscadoresConfiguracion[idObjeto].valorAMostrar=="etiqueta" ? registro.texto : valorMostrado );
    valorMostrado = ( window.buscadoresConfiguracion[idObjeto].valorAMostrar=="concatenado" ? registro.valor+" - "+registro.texto : valorMostrado );

    return valorMostrado;
}

function fnListadoTipoBusqueda(textoABuscar,idObjeto){
    var tipoBusqueda = "";

    tipoBusqueda = ( !window.buscadoresConfiguracion[idObjeto].tipoBusqueda||window.buscadoresConfiguracion[idObjeto].tipoBusqueda=="comienzaCon" ? '^'+textoABuscar : tipoBusqueda );
    tipoBusqueda = ( window.buscadoresConfiguracion[idObjeto].tipoBusqueda=="terminaEn" ? textoABuscar+'$' : tipoBusqueda );
    tipoBusqueda = ( window.buscadoresConfiguracion[idObjeto].tipoBusqueda=="incluye" ? textoABuscar : tipoBusqueda );

    return new RegExp(tipoBusqueda,"i");
}

function fnConfirmarConfiguracionDeListado(idObjeto){
    if(!window.buscadoresConfiguracion[idObjeto]){
        window.listadoHabilitado = false;
        return;
    }

    if(!window.buscadoresConfiguracion[idObjeto].origenDatos){
        window.listadoHabilitado = false;
        return;
    }

    if(!window[window.buscadoresConfiguracion[idObjeto].origenDatos]){
        window.listadoHabilitado = false;
        return;
    }
}

if(typeof(fnSeleccionaDesdeListado)!=="function"){
    function fnSeleccionaDesdeListado(idArreglo='vacio',idObjeto,enFocusOut=false) {
        var arregloBusqueda = window[window.buscadoresConfiguracion[idObjeto].origenDatos],
            textoVisible = "";

        textoVisible = ( idArreglo!='vacio'&&( !window.buscadoresConfiguracion[idObjeto].valorAMostrar||window.buscadoresConfiguracion[idObjeto].valorAMostrar=="id" ) ? arregloBusqueda[idArreglo].valor : textoVisible );
        textoVisible = ( idArreglo!='vacio'&&window.buscadoresConfiguracion[idObjeto].valorAMostrar=="etiqueta" ? arregloBusqueda[idArreglo].texto : textoVisible );
        textoVisible = ( idArreglo!='vacio'&&window.buscadoresConfiguracion[idObjeto].valorAMostrar=="concatenado" ? arregloBusqueda[idArreglo].valor+" - "+arregloBusqueda[idArreglo].texto : textoVisible );

        $("#textoVisible__"+idObjeto).val(( idArreglo!='vacio' ? textoVisible : "" ));
        $("#"+idObjeto).val(( idArreglo!='vacio' ? arregloBusqueda[idArreglo].valor : "" ));
        $("#textoOculto__"+idObjeto).val(( idArreglo!='vacio' ? arregloBusqueda[idArreglo].texto : "" ));

        setTimeout(function(){
            if(enFocusOut){
                $("#sugerencia-"+idObjeto).hide();
                $("#sugerencia-"+idObjeto).empty();
            }
        }, 500);
    }
}

function getEvents(element){
    var elemEvents = $._data(element, "events");
    var allDocEvnts = $._data(document, "events");
    for(var evntType in allDocEvnts){
        if(allDocEvnts.hasOwnProperty(evntType)){
            var evts = allDocEvnts[evntType];
            for(var i = 0; i < evts.length; i++){
                if($(element).is(evts[i].selector)){
                    if(elemEvents == null){
                        elemEvents = {};
                    }
                    if(!elemEvents.hasOwnProperty(evntType)){
                        elemEvents[evntType] = [];
                    }
                    elemEvents[evntType].push(evts[i]);
                }
            }
        }
    }
    return elemEvents;
}
