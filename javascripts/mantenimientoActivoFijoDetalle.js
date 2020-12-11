/*/
	Variables Globales
/*/
var dataJsonMttoDetalle = new Array();
var nuevoRegistro = true;
var tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

/*/
	Funcion de documente ready
/*/
$(document).ready(function (){
	/*/
		Configuracion atributo multiselect
	/*/
	$('#selectAlmacen, #selectClaveCABMS').multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });

    $('.multiselect-container').css({
        'max-height': "200px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });

    /*/ 
        Carga Inicial
    /*/
    $('#txtNoCaptura').text('');
    if(folioMtto > 0){
        nuevoRegistro = false;
        $('#txtNoCaptura').text(folioMtto);
        fnCargarMantenimiento(folioMtto);
    }else{
        if(fnObtenerOption('selectUnidadNegocio') !="" && fnObtenerOption('selectUnidadEjecutora') !=""){
            fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'),fnObtenerOption('selectUnidadEjecutora') , 'selectAlmacen');
        }
    }


	/*/
		Eventos Change
	/*/

	// $('#selectUnidadNegocio').change(function (){
	// 	fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'), fnObtenerOption('selectUnidadEjecutora') ,  'selectAlmacen');
	// });


    $('#selectUnidadEjecutora').change(function (){
        fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'),fnObtenerOption('selectUnidadEjecutora') , 'selectAlmacen');
        fnObtenerPresupuestoBusqueda();

    });

    $("#selectCategoriaActivo").change(function (){
        fnCargarCAMBS();
    });

    /*/
        Eventos Click
    /*/

    $('#btnObetenerPatrimonio').click(function (){

        if($("#tablaActivoFijo").find(".rowPatrimonio").length > 0){
            muestraModalGeneralConfirmacion(3,tituloGeneral,'Se borrara el contenido capturado actualmente en la tabla, ¿Desea continuar?','','fnObtenerPatrimonio()');
        }else{
            fnObtenerPatrimonio();
        }
        
    });

    $('#btnGuardar').click(function (){
        fnGuardarMantenimiento();
    });

    $('#btnFinalizar').click(function (){
        var msjInicio='<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>El Campo ';
        var msjFin=' es obligatorio.</p>';
        var msjErr="";

        if($('#txtRequisicion').val() == ""){
            msjErr += msjInicio +"Requisición"+ msjFin;
        }

        if(msjErr != ""){
            muestraMensaje(''+msjErr, 3, 'mensajesValidaciones', 5000);
            return false;
        }

        muestraModalGeneralConfirmacion(3,tituloGeneral,'¿Está seguro de finalizar el mantenimiento?','','fnFinalizarMantenimiento('+folioMtto+')');
        //fnFinalizarMantenimiento(folioMtto);
    });

    $('#btnCancelar').click(function(){
        fnLimpiarCampos();
    });


    fnObtenerPresupuestoBusqueda();

});

function fnLimpiarCampos(){

    fnLimpiarCamposForm('formSearch');
    fnLimpiarCamposForm('divClavePresupuestal');
    fnLimpiarCamposForm('PanelAgregarActivoFijo');

    var d = new Date();
    var strDate = d.getDate() + "-" + (d.getMonth()+1) + "-" + d.getFullYear() ;

    $('#dpFechaCaptura').val(strDate);
    //Limpiar tabla
    $("#tablaActivoFijo >tbody").empty();
}


function fnCargarCAMBS(){
  var sqlCAMBS="SELECT eq_stockid as valor, concat(eq_stockid,' - ', descPartidaEspecifica) as descripcion FROM tb_partida_articulo WHERE partidaEspecifica in("+fnObtenerOption("selectCategoriaActivo")+");";
    fnLlenarSelect(sqlCAMBS,'selectClaveCABMS');  
}

function fnFinalizarMantenimiento(folioMtto){

    fnGuardarMantenimiento('1');

    dataObj = {
        option : 'cambiarEstatusMantenimiento',
        folio : folioMtto,
        estatus : '2',
        leyenda : 'finalizó',
        requisicion: $('#txtRequisicion').val()
    };

    $.ajax({
        async: false,
        method: "POST",
        dataType: "json",
        url: "modelo/mantenimientoActivoFijoDetalleModelo.php",
        data: dataObj
    })
    .done(function(data) {
        if (data.result) {
            fnEstatusCampos(true,'disable');
            fnValidarEstatus('2');
            muestraModalGeneral(3,tituloGeneral,''+data.Mensaje);
        }
    })
    .fail(function(result) {
        muestraMensaje('<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Error al finalizar mantenimiento<p>', 3, 'mensajesValidaciones', 5000);
        console.log("ERROR");
        console.log(result);
    }); 
}


function fnCargarMantenimiento(folioMtto){

    dataObj = {
        option : 'obtenerMantenimiento',
        folio : folioMtto
    };

    $.ajax({
        async: false,
        method: "POST",
        dataType: "json",
        url: "modelo/mantenimientoActivoFijoDetalleModelo.php",
        data: dataObj
    })
    .done(function(data) {
        if (data.result) {

            dataObjEncabezado = data.contenido.datosEncabezado;
            dataObjDetalle = data.contenido.datosDetalle;

            $.each(dataObjEncabezado,function(index, el) {

                /*==== UR ====*/
                strOptionUR = "<option value='"+el.ur+"' selected>"+el.urDescription+"</option>";
                $('#selectUnidadNegocio').empty();
                $('#selectUnidadNegocio').append(strOptionUR);
                $('#selectUnidadNegocio').multiselect('rebuild');

                /*==== UE ====*/
                $("#selectUnidadEjecutora").val(el.ue);
                $('#selectUnidadEjecutora').multiselect('rebuild');

                fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'), fnObtenerOption('selectUnidadEjecutora') ,  'selectAlmacen');
                
                /*==== Almacen ====*/
                $("#selectAlmacen").val(el.loccode);
                $('#selectAlmacen').multiselect('rebuild');

                /*==== Tipo bien ====*/
                $("#selectTipoBien").val(el.tipoBien);
                $('#selectTipoBien').multiselect('rebuild');

                /*==== Tipo de Mantenimiento ====*/
                $("#selectTipoMantenimiento").val(el.tipoMtto);
                $('#selectTipoMantenimiento').multiselect('rebuild');

                /*==== Fecha Captura ====*/
                $("#dpFechaCaptura").val(el.datetimeup);

                /*==== Fecha Mtto ====*/
                $("#dpProgramacionMtto").val(el.dateMtto);

                /*==== Observaciones ====*/
                $("#txtObservacion").val(el.observacion);

                /*==== Clave presupuestal ====*/
                $("#txtBuscarPresupuesto").val(el.clave);

                /*==== Requisicion ====*/
                $("#txtRequisicion").val(el.requisicion);

                /*==== partida especifica ====*/
                var pEspecifica = el.partidaEspecifica;
                var arrpEspecifica = pEspecifica.split(",");
                $("#selectCategoriaActivo").val(arrpEspecifica);
                $('#selectCategoriaActivo').multiselect('rebuild');

                fnCargarCAMBS();

                /*==== cambs ====*/
                if(el.cabms != ""){
                    var cabmsMtto = el.cabms;
                    var arrCabmsMtto = cabmsMtto.split(",");
                    $("#selectClaveCABMS").val(arrCabmsMtto);
                    $('#selectClaveCABMS').multiselect('rebuild');
                }
                

                fnEstatusCampos(true,'disable');
                fnValidarEstatus(el.estatus);
                
            });


            var strTabla="";
            $("#tablaActivoFijo >tbody").empty();

            $.each(dataObjDetalle,function(index, el) {
                strTabla +="<tr class='rowPatrimonio' data-assetid='"+el.assetid+"' data-row='"+index+"'>";
                strTabla +="    <td style='text-align:center;' ><button class='glyphicon glyphicon-remove btn-xs btn-danger' onclick='fnEliminarRow(this)'></button></td></td>";
                strTabla +="    <td style='text-align:center;' class='idNumeracion'>"+ ( parseFloat(index) + 1 )+"</td>";
                strTabla +="    <td style='text-align:center;' >"+el.barcode+"</td>";
                strTabla +="    <td style='text-align:center;' >"+el.clavebien+"</td>";
                strTabla +="    <td style='text-align:center;' >"+el.marca+"</td>";
                strTabla +="    <td nowrap>"+el.description+"</td>";
                strTabla +='    <td><textarea id="txtObservacion'+index+'" name="txtObservacion'+index+'" class="form-control" rows="1" style="width:100%; resize: vertical;">'+el.observaciones+'</textarea></td>';
                strTabla +="</tr>";
            });

            $("#tablaActivoFijo >tbody").append(strTabla);

        }
    })
    .fail(function(result) {
        muestraMensaje('<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Error al cargar<p>', 3, 'mensajesValidaciones', 5000);
        console.log("ERROR");
        console.log(result);
    });  

}

function fnGuardarMantenimiento(mostrarmsj = 0){

    if(fnValidacionGauardarMtto()){
       return false; 
    }

    var obj = new Object();
    var datos = new Array();

    var contador = 0;
    var recorrer = 2;

    var option ="guardarMantenimiento";
     var folio ="";
    dataJsonMttoDetalle = new Array();

    $("#tablaActivoFijo").find(".rowPatrimonio").each(function(index){
        datos = [$(this).data('assetid'), $("#txtObservacion"+$(this).data('row')).val()];
        fnAgregarDatosDetalle(datos.length,datos);
    });

    // console.log(dataJsonMttoDetalle);

    if(dataJsonMttoDetalle.length <= 0){ muestraMensaje(' No hay bienes capturados', 3, 'mensajesValidaciones', 5000); return false;}


    if(folioMtto != ""){
        option = 'modificarMantenimiento';
        folio=folioMtto;
    }

    dataObj = {
        option : option,
        ur : fnObtenerOption('selectUnidadNegocio',1),
        ue : fnObtenerOption('selectUnidadEjecutora',1),
        tipo_bien : fnObtenerOption('selectTipoBien',1),
        tipo_mtto : fnObtenerOption('selectTipoMantenimiento',1),
        almacen : fnObtenerOption('selectAlmacen',1),
        pe : fnObtenerOption('selectCategoriaActivo',1),
        cabms : fnObtenerOption('selectClaveCABMS',1),
        fecha_captura : $('#dpFechaCaptura').val(),
        fecha_mtto : $('#dpProgramacionMtto').val(),
        clave : $('#txtBuscarPresupuesto').val(),
        requisicion : $('#txtRequisicion').val(),
        observaciones : $('#txtObservacion').val(),
        dataJsonMttoDetalle : dataJsonMttoDetalle,
        folio:folio
    };

    $.ajax({
        async: false,
        method: "POST",
        dataType: "json",
        url: "modelo/mantenimientoActivoFijoDetalleModelo.php",
        data: dataObj
    })
    .done(function(data) {

        if (data.result) {
            dataObj = data.contenido.datos;

            $.each(dataObj,function(index, el) {
                folioMtto=el.folio;
            });

            if(folioMtto != ""){
                $('#txtNoCaptura').text(folioMtto);
            }
            if(mostrarmsj == '0'){
                muestraMensaje(''+data.Mensaje, 3, 'mensajesValidaciones', 5000);

                fnEstatusCampos(true,'disable');
                fnValidarEstatus('1');
            }
            
        }
    })
    .fail(function(result) {
        muestraMensaje('<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Error al guardar<p>', 3, 'mensajesValidaciones', 5000);
        console.log("ERROR");
        console.log(result);
    });
}

function fnEstatusCampos(estatusInput = true, statusCombo='disable'){
    $('#formSearch input[type="text"], input[type="hidden"]').each(
        function(index){  
            var input = $(this);
            input.prop('disabled', estatusInput);
        }
    );

    $('#formSearch select').each(
        function(index){  
            var combo = $(this);
            combo.multiselect(statusCombo);
        }
    );
}

function fnValidarEstatus(estatus){
    //console.log(estatus);
    switch (estatus) {
        case '1':
            $('#btnCancelar').remove();
            $('#txtObservacion').prop('disabled', false);
            $('#dpProgramacionMtto').prop('disabled', false);
            $('#txtBuscarPresupuesto').prop('disabled', false);
            $('#selectTipoMantenimiento').multiselect('enable');
            $( "#btnFinalizar" ).removeClass( "hide" );
            $('#txtRequisicion').prop('disabled', false);
            $('#selectCategoriaActivo').multiselect('enable');
            $('#selectClaveCABMS').multiselect('enable');
            break;
        case '2':
        case '3':
            $('#txtBuscarPresupuesto').prop('disabled', true);
            $('#txtObservacion').prop('disabled', true);
            $('#btnGuardar').remove();
            $('#btnCancelar').remove();
            $('#btnFinalizar').remove();
            $('#btnObetenerPatrimonio').remove();
            fnBloquearDivs("PanelAgregarActivoFijo");
            $('#PanelAgregarActivoFijo textarea').each(
                function(index){  
                    var input = $(this);
                    input.prop('disabled', true);
                }
            );
            $('#selectCategoriaActivo').multiselect('enable');
            $('#selectClaveCABMS').multiselect('enable');
            break;
        default: 
            break;
    }
}

function fnAgregarDatosDetalle(numElementos, data) {
    var obj = new Object();

    for (var x= 0; x < numElementos; x++) {
        obj['val'+x] = data[x];
    }

    dataJsonMttoDetalle.push(obj);
}

function fnValidacionGauardarMtto(){
    var msjError ="";
    var blnReturn = false;
    var msjInicio='<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>El Campo ';
    var msjFin=' es obligatorio.</p>';

    if ($("#selectUnidadNegocio").val() == "" || $("#selectUnidadNegocio").val() == "0" || $("#selectUnidadNegocio").val() == "-1" ) msjError += msjInicio +'UR'+msjFin;
    if ($("#selectUnidadEjecutora").val() == "" || $("#selectUnidadEjecutora").val() == "0" || $("#selectUnidadEjecutora").val() == "-1" ) msjError += msjInicio +'UE'+msjFin;
    if ($("#selectTipoBien").val() == "" || $("#selectTipoBien").val() == "0" || $("#selectTipoBien").val() == "-1" ) msjError += msjInicio +'Tipo de Bien'+msjFin;
    if ($("#selectTipoMantenimiento").val() == "" || $("#selectTipoMantenimiento").val() == "0" || $("#selectTipoMantenimiento").val() == "-1" ) msjError += msjInicio +'Tipo de Mantenimiento'+msjFin;
    if ($("#dpProgramacionMtto").val() == "" || $("#dpProgramacionMtto").val() == "0" || $("#dpProgramacionMtto").val() == "-1" ) msjError += msjInicio +'Programación de Mtto'+msjFin;
    if ($("#selectAlmacen").val() == "" || $("#selectAlmacen").val() == "0" || $("#selectAlmacen").val() == "-1" ) msjError += msjInicio +'Almacén'+msjFin;
    if ($("#txtBuscarPresupuesto").val() == "" || $("#txtBuscarPresupuesto").val() == "0" || $("#txtBuscarPresupuesto").val() == "-1" ) msjError += msjInicio +'Clave Presupuestal'+msjFin;
    //if ($("#selectCategoriaActivo").val() == "" || $("#selectCategoriaActivo").val() == "0" || $("#selectCategoriaActivo").val() == "-1" ) msjError += msjInicio +'Partida Especifica'+msjFin;
    //if ($("#selectClaveCABMS").val() == "" || $("#selectClaveCABMS").val() == "0" || $("#selectClaveCABMS").val() == "-1" ) msjError += msjInicio +'CABMS'+msjFin;

    if(msjError != ""){
        
        muestraMensaje(''+msjError, 3, 'mensajesValidaciones', 5000);
        blnReturn = true;
        
    }

    return blnReturn;
}

function fnObtenerPatrimonio(){

    if(fnValidacionObtenerPatrimoio()){
        return false;
    }

    dataObj = {
        option: 'obtenerPatrimonioMatto',
        ur: fnObtenerOption('selectUnidadNegocio'),
        ue: fnObtenerOption('selectUnidadEjecutora'),
        tipo_bien : fnObtenerOption('selectTipoBien'),
        almacen : fnObtenerOption('selectAlmacen'),
        pe : fnObtenerOption('selectCategoriaActivo'),
        cabms : fnObtenerOption('selectClaveCABMS')
    };

    $.ajax({
        async: false,
        method: "POST",
        dataType: "json",
        url: "modelo/mantenimientoActivoFijoDetalleModelo.php",
        data: dataObj
    })
    .done(function(data) {
        if (data.result) {
            dataObj = data.contenido.datos;

            var strTabla="";
            $("#tablaActivoFijo >tbody").empty();

            if($.isEmptyObject(dataObj)){
                muestraMensaje('No se encontraron registros', 3, 'mensajesValidaciones', 5000);
                return false;
            }

            $.each(dataObj,function(index, el) {
                strTabla +="<tr class='rowPatrimonio' data-assetid='"+el.assetid+"' data-row='"+index+"'>";
                strTabla +="    <td style='text-align:center;' ><button class='glyphicon glyphicon-remove btn-xs btn-danger' onclick='fnEliminarRow(this)'></button></td></td>";
                strTabla +="    <td style='text-align:center;' class='idNumeracion' >"+ ( parseFloat(index) + 1 )+"</td>";
                strTabla +="    <td style='text-align:center;' >"+el.barcode+"</td>";
                strTabla +="    <td style='text-align:center;' >"+el.clavebien+"</td>";
                strTabla +="    <td style='text-align:center;' >"+el.marca+"</td>";
                strTabla +="    <td nowrap>"+el.description+"</td>";
                strTabla +='    <td><textarea id="txtObservacion'+index+'" name="txtObservacion'+index+'" class="form-control" rows="1" style="width:100%; resize: vertical;"></textarea></td>';
                strTabla +="</tr>";
            });

            $("#tablaActivoFijo >tbody").append(strTabla);
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
}

function fnEliminarRow(btnEliminar){
    var trRowPadre = btnEliminar.parentNode.parentNode;
    var trRowIndex = trRowPadre.rowIndex;
    var tabla  = trRowPadre.parentNode; 
    //tabla.deleteRow(trRowIndex);
    document.getElementById("tablaActivoFijo").deleteRow(trRowIndex);

    fnNumeracionTabla('tablaActivoFijo');
}

function fnNumeracionTabla(componenteTabla){
    var cont = 1;
    $("#"+componenteTabla).find("tbody").find("td").each(function(){ 
        if ($(this).attr("class") == "idNumeracion") {
           $(this).text(cont);
           cont++;
        }

    }); 
}

function fnValidacionObtenerPatrimoio(){

    var msjError ="";
    var blnReturn = false;
    var msjInicio='<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>El Campo ';
    var msjFin=' es obligatorio.</p>';

    if ($("#selectUnidadNegocio").val() == "" || $("#selectUnidadNegocio").val() == "0" || $("#selectUnidadNegocio").val() == "-1" ) msjError += msjInicio +'UR'+msjFin;
    if ($("#selectUnidadEjecutora").val() == "" || $("#selectUnidadEjecutora").val() == "0" || $("#selectUnidadEjecutora").val() == "-1" ) msjError += msjInicio +'UE'+msjFin;
    if ($("#selectTipoBien").val() == "" || $("#selectTipoBien").val() == "0" || $("#selectTipoBien").val() == "-1" ) msjError += msjInicio +'Tipo de Bien'+msjFin;
    if ($("#selectCategoriaActivo").val() == "" || $("#selectCategoriaActivo").val() == "0" || $("#selectCategoriaActivo").val() == "-1" ) msjError += msjInicio +'Partida Especifica'+msjFin;
    if ($("#selectClaveCABMS").val() == "" || $("#selectClaveCABMS").val() == "0" || $("#selectClaveCABMS").val() == "-1" ) msjError += msjInicio +'CABMS'+msjFin;

    if(msjError != ""){
        
        muestraMensaje(''+msjError, 3, 'mensajesValidaciones', 5000);
        blnReturn = true;
        
    }

    return blnReturn;

}

function fnObtenerOption(componenteSelect, intComillas = 0){
	//console.log(componenteSelect);
    var valores = "";
    var comillas="'";
    var select = document.getElementById(''+componenteSelect);

    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (select.selectedOptions[i].value != '-1') {
            if(intComillas == 1){
                comillas="";
            }

            if (i == 0) {
                valores = ""+comillas+select.selectedOptions[i].value+comillas+"";
            }else{
                valores = valores+","+comillas+select.selectedOptions[i].value+comillas+"";
            }
        }
    }

    return valores;
}

function fnObtenerPresupuestoBusqueda(idPMinistracion = "") {

	var legalidBus = "";
	var dataJson = new Array();

	var ur = $('#selectUnidadNegocio').val();
	var ue = fnObtenerOption('selectUnidadEjecutora');

    if(fnObtenerOption('selectUnidadNegocio') =="" && fnObtenerOption('selectUnidadEjecutora') ==""){
        return false;
    }

	dataObj = { 
	        option: 'obtenerCapituloPartida',
	        legalid: legalidBus,
	        ur: ur,
	        ue: ue
	      };
    $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/mantenimientoActivoFijoDetalleModelo.php",
      data: dataObj,
      async:false,
      cache:false
    })
    .done(function( data ) {
        if(data.result) {
        	var dataJson = data.contenido.datos;
        	var listClaves ="";

        	fnBusquedaAutoComplete(dataJson);
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

function fnBusquedaAutoComplete(jsonData) {
    // console.log("busqueda Reducciones");
    // console.log("jsonData: "+JSON.stringify(jsonData));
    $( "#txtBuscarPresupuesto").autocomplete({
        source: jsonData,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.texto + "");
            //$( "#txtBuscarPresupuesto" ).val( ui.item.accountcode );
            $( "#txtBuscarPresupuesto" ).val( "" );
            $( "#txtBuscarPresupuesto" ).val( ui.item.texto );

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
        .append( "<a>" + item.texto + "</a>" )
        .appendTo( ul );

    };
}

function fnLlenarSelect(SQL,componente){
    $('#' + componente).empty();
    $('#' + componente).multiselect('rebuild');

    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/componentes_modelo.php",
        data: {
            option: 'llenarSelect',
            strSQL: SQL
        },
        async: false

    }).done(function(data) {
        if(!data.result){return;}
        var options='';
        
        //options = '<option value="-1">Seleccionar...</option>';

        $.each(data.contenido,function(index, el) {
            options += '<option value="'+el.val+'">'+el.text+'</option>';
        });

        // console.log(options);
        // console.log(componente);
        $('#' + componente).empty();
        $('#' + componente).append(options);
        $('#' + componente).multiselect('rebuild');
    });
}

