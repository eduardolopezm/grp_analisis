/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
var contadorArchivoSubir = 1;
var archivosNopermitidos;
var archivosTotales=0;

$( document ).ready(function(){
    if ($(".cargarArchivosComponente")[0]){
    fnDatosDeArchivosSubidos();
    fnDatosArchivosMultiples();
    }
    if($(".soloCargarArchivos")[0]){
        fnDatosArchivosMultiples();
    }
    if ($(".layoutsGenerados")[0]){
        fnRecuperarLayouts();
    } 
});

function fnDatosArchivosMultiples(){
    //$(document).on('click','.datosArchivos',function(){
    $(document).on('change','#cargarMultiples',function(){
    //$('#cargarMultiples').change(function(){
        archivosNopermitidos= new Array();
        archivosNopermitidos=[];
        archivosTotales=0;
        var agregarFilas='<table class="table table-striped border" id="tablaDetallesArchivos" style="border:solid 1px #eee;"><thead class="bgc8" style="color:#fff;"><th>Nombre</th><th>Tamaño</th> <th>Tipo Archivo </th> <th> </th> </thead><tbody>';
        var filasArchivos='';
        for(var ad=0; ad< this.files.length; ad++){
            var file = this.files[ad];
            nombre = file.name;
            tamanio = file.size;
            tipo = file.type;
            filasArchivos+='<tr class="filasArchivos"> <td>'+ nombre+'</td> <td> <b>Tamaño:</b>'+ tamanio+'</td> <td> <b>Tipo:</b> '+ tipo+'</td> <td class="text-center"> <span class="quitarArchivos"><input type="hidden" name="nombrearchivo" value="'+nombre+'" >    <span  class="btn bgc8" style="color:#fff;">    <span class="glyphicon glyphicon-remove"></span></sapn> </span> </td></tr> ';
            archivosTotales++;
        }
        agregarFilas+=filasArchivos;
        agregarFilas+='   </tbody></table>';
        $('#muestraAntesdeEnviar').empty();
        $('#muestraAntesdeEnviar').append(agregarFilas);
        $('#enviarArchivosMultiples').show();
    });
}

function fnCargarArchivos(){
    $("#tipoInputFile").empty();
    var m=$("#esMultiple").val();
    if(m!=0){
    $("#tipoInputFile").append(' <input type="file"  class="btn bgc8"  name="archivos[]"  id="cargarMultiples"  multiple="multiple" style="display: none;" />');
    }else{
        $("#tipoInputFile").append(' <input type="file"  class="btn bgc8"  name="archivos[]"  id="cargarMultiples" style="display: none;" />');
    }
    $("#cargarMultiples").click();
    console.log("cargando archivos...");
}

// enviando archivos a servidor
$(function(){
    $("#enviarArchivosMultiples").on("click" ,function(e){
        muestraCargandoGeneral();
        var v=$("#cargarMultiples").val();
        if(v!=''){
            e.preventDefault();
            var form_data = new FormData();
            var ins = document.getElementById('cargarMultiples').files.length;
            for (var x = 0; x < ins; x++) {
                form_data.append("archivos[]", document.getElementById('cargarMultiples').files[x]);
            }

            form_data.append('nopermitidos',archivosNopermitidos);
            form_data.append('funcion',$("#funcionArchivos").val());
            form_data.append('tipo',$("#tipoArchivo").val());
            form_data.append('trans',$("#transnoArchivo").val());
            form_data.append('esmultiple',$("#esMultiple").val());

            var tipo =$("#tipoArchivo").val();
            
            if(tipo=='19'){
               
                //
                //cuando lo trae desde de una existente
                if (urGlobal != "" && urGlobal.length > 0 ) {
                    urGlobal=urGlobal.split(" ");
                    urGlobal=urGlobal[0];
                }

               //cuando  sea indefinido
                if (typeof(urGlobal) === "undefined") {
                    urGlobal= $("#selectUnidadNegocio").val();
                    urGlobal=urGlobal.split(" ");
                    urGlobal=urGlobal[0];

                    form_data.append('urGlobal',urGlobal);    
                }
                  if (urGlobal ==-1) {
                    urGlobal= $("#selectUnidadNegocio").val();
                    urGlobal=urGlobal.split(" ");
                    urGlobal=urGlobal[0];

                        
                }
                 idrequisicionGlobal=$('#idtxtRequisicion').val();
                 
                form_data.append('idanexoGlobal', "anexo");
                form_data.append('urGlobal', urGlobal);
                form_data.append('idrequisicionGlobal', idrequisicionGlobal);
                //alert(tipo+"Luis"+ur+"ID"+idrequisicionGlobal);
                if (idrequisicionGlobal == 0 || idrequisicionGlobal == '' || typeof idrequisicionGlobal === 'undefined'){
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(4, titulo, 'Primero se requiere agregar una partida de artículo o servicio para anexar archivo.');
                }
            }
             if(tipo=='600'){ // consolidacion bancaria

                form_data.append('mes', $("#selectMeses").val());
                form_data.append('anio',$("#selectAnio").val());
                form_data.append('bank',$("#bancoselect").val());
                
            }
            $.ajax({
                url: "includes/Subir_Archivos.php",
                dataType: 'json', //retorno servidor  recuerda que es json
                cache: false,
                scriptCharset: "iso-8859-1",
                contentType: false,//'application/json;charset=utf-8',
              // contentType: "application/x-www-form-urlencoded; charset=UTF-8",
               //ContentType : 'application/x-www-form-urlencoded; charset=UTF-8',
                //contentType: "text/html; charset=UTF-8",  
                processData: false,
                mimeType: "multipart/form-data",
                async:false,
                data: form_data,
                type: 'post',
            })
                .done(function(res){
                    if(res){
                        contadorArchivoSubir =0;
                        $('#cargarMultiples').val('');
                        $("#muestraAntesdeEnviar").empty();
                        datos =res.contenido;
                        /*var mensaje='';
                        for (i in datos){
                         mensaje+="Se subió correctamente "+datos[i]+"<br>";
                        } */
                        //muestraMensaje(datos,1, 'mensajeArchivos', 25000);
                         if(($(".datosCotizacionExcel").length>0)){ //si existe la clase datosCotizacionExcel
                            $('#enviarArchivosMultiples').hide();
                            provedor=datos[0];
                            nrequi=datos[2];
                            datosExcel=datos[1];
                            tabla='<table class="table table-striped table-bordered" id="datosTablaCotizacion"><thead>';
                            d=0;
                           
                        
                            for(a in datosExcel){
                               // console.log(datosExcel[a].partida);
                                if(d==0){
                                    tabla+='<th>'+datosExcel[a].partida+'</th>';
                                    tabla+='<th>'+datosExcel[a].codArt+'</th>';
                                    tabla+='<th>'+datosExcel[a].descripcion+'</th>';
                                      tabla+='<th>'+datosExcel[a].cotizacion+'</th>';
                                    tabla+='<tbody>';

                                }else{
                                    tabla+='<tr>';
                                    tabla+='<td class="cotizacionPartida">'+datosExcel[a].partida+'</td>';
                                    tabla+='<td class="cotizacionArticulo">'+datosExcel[a].codArt+'</td>';
                                    tabla+='<td class="cotizacionDescripcion">'+datosExcel[a].descripcion+'</td>';
                                    tabla+='<td class="cotizacionProve">'+datosExcel[a].cotizacion+'</td>';
                                    tabla+='</tr>';
                                }
                                d++;

                            }
                            tabla+='</tbody>';
                            tabla+='</table>';
                            //alert(tabla);
                            $(".datosCotizacionExcel").empty();
                            $(".datosCotizacionExcel").append('<b id="idProveedor">'+provedor+'</b><br><span >'+nrequi+' </span> <input type="hidden" value="'+nrequi+'" id="numeroRequi"><br>'+tabla);
                            $("#divGuardarCotizacion").removeAttr("style");

                         }else{
                            


                             if(tipo=='600'){ 

                            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                            muestraModalGeneral(4, titulo,datos.mensaje+"<br>"+datos.infoConsolidacion);
                            $('#enviarArchivosMultiples').hide();
                            fnDatosDeArchivosSubidos();
                           //alert(datos.cbtabla);
                                if(datos.cbtabla.length>0){
                                    atras='<br><div class="btn bgc8 cbclose" style="color:white;">'+
    '                               <span class="glyphicon glyphicon-home" ></span> Regresar </div>';
                            
                                    $("#divTablaArchivos").fadeOut("slow");
                                     if ($("#cbdetalle").length) {
                                        $("#cbdetalle").remove();
                                     }
                                    $("<div id='cbdetalle'> <b><h4>Detalle del estado de cuenta</h4></b><div class='table-responsive'> <table class='table table-striped border'><thead class='bgc8' style='color:#fff;'><th>Fecha</th><th>Concepto</th><th>Retiro</th><th>Deposito</th><th></th></thead><tbody>"+datos.cbtabla+ "</tbody></table><br>"+atras+"</div> </div>").insertAfter( $( "#divTablaArchivos" ) );
                                 }


                            }else{
                                  var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                            muestraModalGeneral(4, titulo,datos.mensaje);
                            $('#enviarArchivosMultiples').hide();
                            fnDatosDeArchivosSubidos();
                             
                            }
                            
                        }

                        ocultaCargandoGeneral();
                    }else{
                        ocultaCargandoGeneral();
                    }
                })
                  .fail(function(res) {
            console.log("ERROR");
            console.log( res);
            ocultaCargandoGeneral();
                });

        }else{
            ocultaCargandoGeneral();
           // muestraMensaje("Seleccione algún archivo.",3, 'mensajeArchivos', 5000);
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(4, titulo,"Seleccione algún archivo.");
        }
    });
});

function fnChecarExtencion(extencionArchivo){
    var esPermitida=0;
    var ext=['csv','jpge','pkg','png']; // cambiar por los posibles extraidos de las base de datos
    extensionesPermitidas=ext;
    for(i in ext){
        if( (extencionArchivo==extensionesPermitidas[i])){
            esPermitida+=1;
        }
    }
    return esPermitida;
}

$(document).on("change", ".queArchivoCargar",function() {
    numeroArchivo= $(this).attr('id');//numero de archivo ejemplo archivo2.. archivo3...
    var nArchivo = numeroArchivo.replace("archivo", ""); //quito palabra archivo
    var nombreArchivo = $("#"+numeroArchivo)[0].files[0].name;
    // var fileType = $("#"+idf)[0].files[0].type;
    var archivoExtension = nombreArchivo.substring(nombreArchivo.lastIndexOf('.') + 1);
    //alert(nombreArchivo);
    var permitido=fnChecarExtencion(archivoExtension)
    if(permitido>0){
        var iSize = ($("#"+numeroArchivo)[0].files[0].size / 1024);
        if (iSize / 1024 > 1){
            if (((iSize / 1024) / 1024) > 1){
                iSize = (Math.round(((iSize / 1024) / 1024) * 100) / 100);
                $("#"+numeroArchivo).val("");
                $("#info"+nArchivo).empty();
                $("#info"+nArchivo).html(" <b>Muy grande</b> Tamaño: " + iSize + " Gb ");
            }else{
                iSize = (Math.round((iSize / 1024) * 100) / 100)
                var t="";
                if(iSize>2.0){
                    $("#info"+nArchivo).empty();
                    $("#"+numeroArchivo).val("");
                    t=" <b style='background-color:yellow;color:red'>Cambia de imagen es muy grande</b>";
                }
                $("#info"+nArchivo).html(t+"Tamaño: " + iSize + " Mb ");
            }
        }else{
            iSize = (Math.round(iSize * 100) / 100)
            $("#info"+nArchivo).html( "Tamaño: " + iSize  + " kb ");
        }
    }else{
        $("#"+numeroArchivo).val("");
        $("#info"+nArchivo).empty();
        $('#info'+nArchivo).append("<b >El formato <span style='background-color:yellow;color:red'>"+ archivoExtension+"</span> no es permitido</b>");
    }

});

function fnDatosDeArchivosSubidos(){
    var funcionArchivo=$("#funcionArchivos").val();
    var tipo=$("#tipoArchivo").val();
    var trans=$("#transnoArchivo").val();
    if(tipo==19){
        trans= $("#idtxtRequisicion").val();
    }
    //alert(tipo);
    dataObj ={
        proceso: 'obtenerDatosArchivos',
        funcion: funcionArchivo,
        tipo:tipo,
        trans:trans
    };
    //muestraCargandoGeneral();
    $.ajax({
        method: "post",
        dataType:"json",
        url: "includes/Subir_Archivos.php",
        data:dataObj
    })
        .done(function(data){
            if(data.result){
                datosArchivos=data.contenido.DatosArchivos;
               
                //fnLimpiarTabla('divTablaArchivos', 'divDatosArchivos');
                //fnAgregarGridv2(datosArchivos,'divDatosArchivos','b');
            fnLimpiarTabla('divTablaArchivos', 'divDatosArchivos');   

            columnasNombres = '';
            columnasNombres += "[";
            //columnasNombres += "{ name: 'id1', type: 'bool'},";
            columnasNombres += "{ name: 'cajacheckbox', type: 'bool' },";
            columnasNombres += "{ name: 'id', type: 'string' },";
            columnasNombres += "{ name: 'tipo', type: 'string' },";
            columnasNombres += "{ name: 'nombre',type:'string'},";
            columnasNombres += "{ name: 'funcion',type:'string'},";
            columnasNombres += "{ name: 'tipo_doc',type:'string'},";
            columnasNombres += "{ name: 'usuario', type: 'string' },";
            columnasNombres += "{ name: 'fecha', type: 'string' }";
           

            columnasNombres += "]";
            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: '', datafield: 'cajacheckbox', width: '4%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
            columnasNombresGrid += " { text: 'id', datafield: 'id', width: '10%', cellsalign: 'center', align: 'center', hidden: true },";
            columnasNombresGrid += " { text: 'Extensión', datafield: 'tipo', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Nombre archivo',datafield: 'nombre', width: '19%', align: 'center',hidden: false,cellsalign: 'left' },";
            columnasNombresGrid += " { text: 'Función',datafield: 'funcion', width: '18%', align: 'center',hidden: false,cellsalign: 'left' },";
            columnasNombresGrid += " { text: 'Tipo documento',datafield: 'tipo_doc', width: '18%', align: 'center',hidden: false,cellsalign: 'left' },";
            columnasNombresGrid += " { text: 'Usuario',datafield: 'usuario', width: '18%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Fecha', datafield: 'fecha', width: '18%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += "]";

            var columnasExcel = [2,3,4,5];
            var columnasVisuales = [0,2,3,4,5];
            nombreExcel = data.contenido.nombreExcel;

            fnAgregarGrid_Detalle(data.contenido.DatosArchivos, columnasNombres, columnasNombresGrid, 'divDatosArchivos', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
                
              
            }else{
                //alert();
                //fnLimpiarTabla('divTablaArchivos', 'divDatosArchivos');
                //fnAgregarGridv2(d,'divDatosArchivos','b');
               ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log( result );
            //ocultaCargandoGeneral();
        });
}

function fnChecarSeleccionadosGrid() {

    var griddata = $('#divTablaArchivos > #divDatosArchivos').jqxGrid('getdatainformation');
    var datos = [];
    for (var i = 0; i < griddata.rowscount; i++) {
        caja = $('#divTablaArchivos > #divDatosArchivos').jqxGrid('getcellvalue', i, 'cajacheckbox');

        if (caja == true) {
            id = $('#divTablaArchivos > #divDatosArchivos').jqxGrid('getcellvalue', i, 'id');
            datos.push(id);
     
        }
    }
return datos;
}

function fnProcesosArchivosSubidos(tipoproceso,tipo=0){
    muestraCargandoGeneral();
    var datos = [];
    datos=fnChecarSeleccionadosGrid();
    /*$("input:checkbox[name=datoArchivo]:checked").each(function(){
        datos.push($(this).val());
    }); */


    var dataObj;
    if(datos.length > 0){
        switch(tipoproceso){
            case 'eliminar':
            if(tipo!=0 && tipo==19){
              idrequisicion=$('#idtxtRequisicion').val();
                }else{
                    idrequisicion='';
                }
                dataObj = {
                    proceso: 'eliminarArchivosSubidos',
                    archivos:datos,
                    requisicion: idrequisicion
                };
                break;
            case 'descargar':
                dataObj = {
                    proceso: 'descargarArchivos',
                    archivos:datos
                };
                break;
        }
        $.ajax({
            method: "POST",
            dataType:"json",
            url: "includes/Subir_Archivos.php",
            data:dataObj
        })
            .done(function( data ){
                if(data.result){
                    info=data.contenido;
                   

                    if(tipoproceso=='eliminar'){
                        $('#ModalBorrarArchivos').modal('hide');
                       // muestraMensaje(info,1, 'mensajeArchivos', 5000);
                       var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(4, titulo,info);
                         fnDatosDeArchivosSubidos();
                    }else{
                        $("#enlaceDescarga").empty();
                        $("#enlaceDescarga").append(info);
                    }

                    ocultaCargandoGeneral();
                    $("#accionesArchivos").hide();
                    $("#subirArchivos").show();
                }else{
                    ocultaCargandoGeneral();
                    $("#accionesArchivos").hide();
                    $("#subirArchivos").show();
                }

            })
            .fail(function(result) {
                console.log("ERROR");
                console.log( result );
                ocultaCargandoGeneral();
                $("#accionesArchivos").hide();
                $("#subirArchivos").show();
            });
    }else{
       // muestraMensaje('Seleccioné un archivo',3, 'mensajeArchivos', 5000);
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(4, titulo,'Seleccioné un archivo');
    }

}

$(document).on('click','#enlaceArchivo',function(){
    
        $('#enlaceDescarga').empty();
        $('#enlaceDescargaLayouts').empty();

    
});


$(document).on('click','.quitarArchivos',function(){
    archivosTotales--;
    if(archivosTotales<=0){
        $("#enviarArchivosMultiples").hide();
    }
    nombre=$(this).children('input[name=nombrearchivo]').val();
    var archivos= document.getElementById('cargarMultiples').files;
    for(var i=0;i<archivos.length;i++){
        if(archivos[i].name === nombre) {
            archivosNopermitidos.push(archivos[i].name);
            $(this).closest('tr').remove();
            break;
        }

    }
    if(archivosTotales==0){
      $("#muestraAntesdeEnviar").empty();
    }
   
});

function fnBorrarConfirmaArch(){
    var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span>Borrar archivos </h3>';
    $('#ModalBorrarArchivos_Titulo').empty();
    $('#ModalBorrarArchivos_Titulo').append(titulo);
    $('#ModalBorrarArchivos').modal('show');
}
function fnBorrarConfirmaLayout(){
    var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span>Borrar layouts </h3>';
    $('#ModalBorrarLayouts_Titulo').empty();
    $('#ModalBorrarLayouts_Titulo').append(titulo);
    $('#ModalBorrarLayouts').modal('show');
}

function fnChecarSeleccionados(){
   var contador=0;
     /*$("input:checkbox[name=datoArchivo]:checked").each(function() {

        if($(this).is(':checked')){
            contador++;
        }else{
            contador=0;
        }
    });

    if(contador>0){
        $("#accionesArchivos").show();
        $("#subirArchivos").hide();
    }else{
        $("#accionesArchivos").hide();$("#subirArchivos").show();
    } */
    var griddata = $('#divTablaArchivos > #divDatosArchivos').jqxGrid('getdatainformation');
    for (var i = 0; i < griddata.rowscount; i++) {
        caja = $('#divTablaArchivos > #divDatosArchivos').jqxGrid('getcellvalue', i, 'cajacheckbox');

        if (caja == true) {
             contador++;
        }
    }
    if(contador>0){
        $("#accionesArchivos").show();
        $("#subirArchivos").hide();
    }else{
        $("#accionesArchivos").hide();
        $("#subirArchivos").show();
    }
}
function fnSeleccionadosLayouts(){
    var contador=0;
    $("input:checkbox[name=datoLayout]:checked").each(function() {

        if($(this).is(':checked')){
            contador++;
        }else{
            contador=0;
        }
    });

    if(contador>0){
        $("#accionesLayouts").show();
       
    }else{
        $("#accionesLayouts").hide()
    }
}

$(document).click(function(e){
   //fnChecarSeleccionados();
    if ($(".layoutsGenerados")[0]){
        fnSeleccionadosLayouts();
    }
});

$(document).on('click','.datosArchivos',function(){
    /*var id;
    id=$(this).val();
    alert(id);

    $(".selMovimiento").each(function() {
        id=$(this).attr('id');
          val= $(this).val();
         //alert(id);
     }); */

    /*if ($('.coupon_question').is(':checked')){
    $(".answer").show();
  }else{
    $(".answer").hide();
  } */
});

function fnRecuperarLayouts(){
    var funcionLayout=$("#funcionLayout").val();
    var tipoLayout= $("#tipoLayout").val();
    var transnoLayout= $("#transnoLayout").val();
    //alert(funcionLayout + ' '+tipoLayout+' '+transnoLayout);
    dataObj ={
        proceso: 'recuperarLayouts',
        funcion: funcionLayout,
        tipo:tipoLayout,
        transno:transnoLayout
    };

    muestraCargandoGeneral();
    $.ajax({
        method: "post",
        dataType:"json",
        url: "includes/Subir_Archivos.php",
        data:dataObj
    })
        .done(function(data){
            if(data.result){
                datosArchivos=data.contenido.DatosLayouts;
                fnLimpiarTabla('tablaRecuperarLayouts', 'datosRecuperarLayouts');
                fnAgregarGridv2(datosArchivos,'datosRecuperarLayouts','b');
        
       /* setTimeout(function(){
        $(".datosLayouts").each(function(){
            var eliminar=$(this).find('.permitidoLayout').html();
            alert(eliminar);
            if(eliminar==0){

            $(this).find('.layoutRef').prop("disabled",true);
            }
        });
    }, 5000); */

                ocultaCargandoGeneral();


            }else{
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log( result );
            ocultaCargandoGeneral();
        });
}

function fnFila(id){
    setTimeout(function(){
        $(".datosLayouts").each(function(){
            var dato=$(this).find('.permitidoLayout').html();
         
            if(id==dato){
                var x= $(this).next("div.jqx-grid-cell jqx-item jqx-grid-cell-wrap").html();
                alert(x);
            } 
        });
    }, 10);
}

function fnProcesosLayouts(tipoproceso){
    muestraCargandoGeneral();
    var datos = new Array();
    $("input:checkbox[name=datoLayout]:checked").each(function(){
        datos.push($(this).val());
    });

    var dataObj;
    if(datos.length > 0){
        switch(tipoproceso){
            case 'eliminar':
                dataObj = {
                    proceso: 'eliminarLayouts',
                    archivos:datos
                };
                break;
            case 'descargar':
                dataObj = {
                    proceso: 'descargarArchivos',
                    archivos:datos
                };
                break;
        }
        $.ajax({
            method: "POST",
            dataType:"json",
            url: "includes/Subir_Archivos.php",
            data:dataObj
        })
            .done(function( data ){
                if(data.result){
                    info=data.contenido;
                    //fnDatosDeArchivosSubidos();
                 

                    if(tipoproceso=='eliminar'){
                        if(contenido.includes('No se')){

                        }else{
                              fnRecuperarLayouts();
                        }
                     
                        $('#ModalBorrarLayouts').modal('hide');
                    }else{
                        //alert(info);
                        $("#enlaceDescargaLayouts").empty();
                        $("#enlaceDescargaLayouts").append(info);
                    }

                    ocultaCargandoGeneral();
                    $("#accionesLayouts").hide();
                   
                }else{
                    $('#ModalBorrarLayouts').modal('hide');
                    ocultaCargandoGeneral();
                    $("#accionesLayouts").hide();
                   
                }

            })
            .fail(function(result) {
                console.log("ERROR");
                console.log( result );
                ocultaCargandoGeneral();
                $("#accionesLayouts").hide();
                $('#ModalBorrarLayouts').modal('hide');
                
            });
    }else{
       // muestraMensaje('Seleccioné un archivo',3, 'mensajeArchivos', 5000);
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(4, titulo,'Seleccioné un archivo');
    }
}

$(document).on('cellbeginedit', '#divDatosArchivos', function(event) {
    $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'id', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'usuario', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'nombre', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'tipo', 'editable', false);
    
     
});

$(document).on('cellvaluechanged', '#divTablaArchivos > #divDatosArchivos', function(event) {
 
    fnChecarSeleccionados();


});

$(document).on('click',".cbclose",function(event){

    $('#divTablaArchivos').fadeIn('slow');
    $('#cbdetalle').fadeOut('slow');
    $(this).remove();
});
