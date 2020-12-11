var intGetFolio = "";
var tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
//
$(document).ready(function(){

	//Configuracion select
	$('#selectEmpleadotab2, #selectUnidadEjecutora, #selectUnidadNegocio_modal, #selectUnidadEjecutora_modal').multiselect({
             enableFiltering: true,
             filterBehavior: 'text',
             enableCaseInsensitiveFiltering: true,
             buttonWidth: '100%',
             numberDisplayed: 1,
             includeSelectAllOption: true
    });
	
	// Estilos para el diseño del select  
	$('.multiselect-container').css({ 'max-height': "200px" });
	$('.multiselect-container').css({ 'overflow-y': "scroll" });

	// VALIDACIONES INICIALES
	intGetFolio = $('#txtFolioResguardo').val();

	if(intGetFolio != ''){
		fnCargarResguardo(intGetFolio);
		
	}else{
		fnCargarInicio();
        if($('#selectUnidadNegocio').val() != "-1"){
            fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora');
        }

        if($('#selectUnidadEjecutora').val() != "-1"){
            fnObtenerOptionEmpleados(fnObtenerOption('selectUnidadNegocio'),fnObtenerOption('selectUnidadEjecutora'),'selectEmpleadotab2');
        }

	}

	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//!!                 EVENTOS CHANGE.               !!
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

	$('#selectUnidadNegocio').change(function(){
		fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora');
	});

	$('#selectUnidadEjecutora').change(function(){
		fnObtenerOptionEmpleados(fnObtenerOption('selectUnidadNegocio'),fnObtenerOption('selectUnidadEjecutora'),'selectEmpleadotab2');
	});

	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//!!                 EVENTOS CLICK.                !!
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $('#btnAgregarResguardo').click(function(){
    	fnAgregarResguardo();
    });

    $('#btnAgregarActivo').click(function(){
    	fnAgregarPartidaTabla();
    });

    $('#guardarResguardo').click(function(){
    	fnGuardarResguardo();

    });

    $('#btnCancelarCampos').click(function(){
    	fnLimpiarCamposActivos();
    });

    $('#btnBajaActivo').click(function(){

    	var contenido ="";
    	var lineid="";
    	var contador =0;
    	var numInvetario="";
    	var numFila="";
    	var numInventario="";
    	$('#divContenidoBaja').empty();

        var kmInicial=0;
        var kmFinal=0;

    	var msjVehiculo="";
    	$("#idDivActivosFijos").find(".clsVehiculoOld").each(function(index){
    		row = $(this).data('rowvehiculo');
    		
    		if($('#chkSelectActivo'+row).prop('checked')){
    			if($('#txtKMInicial'+row).val()==""){
    				msjVehiculo+='<p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> En el renglon '+row+' es necesario capturar el Km. Inicial. </p>';
    			}
    			if($('#txtKMFinal'+row).val()==""){
    				msjVehiculo+='<p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> En el renglon '+row+' es necesario capturar el Km. Final. </p>';
    			}


                if($('#txtKMFinal'+row).val()!=""){
                    kmInicial = parseFloat($('#txtKMInicial'+row).val());
                    kmFinal = parseFloat($('#txtKMFinal'+row).val());

                    if(kmFinal < kmInicial){
                        msjVehiculo+='<p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> En el renglon '+row+' el Km. Final no puede ser menor al Km. Inicial.</p>';
                    }
                }
    		}

    	});

    	if(msjVehiculo !=""){
    		muestraModalGeneral(3,tituloGeneral,msjVehiculo);
    		return false;
    	}

    	$("#idDivActivosFijos").find(".chkSelectActivo").each(function(index){
			if($(this).prop('checked')){
				lineid="roActivoBaja"+contador;
				numFila=$(this).data('filas');
				numInventario=$(this).data('activo');

				contenido += '<div id="'+lineid+'" class="row borderGray p0 m0 text-center w100p form-group">';
				contenido += '	<div class="col-lg-12 col-md-12 col-sm-12 p0 m0">';
				contenido += '		<div class="w25p fl "><input class="txtIdActivoBaja" id="txtIdActivoBaja'+contador+'" type ="hidden" value="'+$(this).val()+'"><label style="margin-top:20px;">'+numInventario+'</label></div>';
				contenido += '		<div class="w75p fl "><textarea id="txtObservacionesBaja'+contador+'" name="txtObservacionesBaja'+contador+'" class="form-control txtObservacionesBaja" placeholder="Observaciones" style="resize: vertical;">'+$('#txtAgregarObservaciones'+numFila).val()+'</textarea></div>';
				contenido += '	</div>';
				contenido += '</div>';

				contador++;
			}
		});
		
    	if(contenido !=""){
    		$('#divContenidoBaja').html(contenido);
    		fnEjecutarVueGeneral('divContenidoBaja');
    		$('#modalBajaActivo').modal('show');
    	}else{
			muestraModalGeneral(3,'Advertencia de datos','Debe seleccionar un activo fijo.','','');
    	}

    });

    $('#btnConfirmarBajaActivoFijo').click(function(){
    	fnBajaActivos();
    });

    $("#btnUploadFile").click(function(e) {
        e.preventDefault(); // to stop  load  to form
        fnLoadFiles();
    });


});

// <Archivos>
$(document).on('click','#cargarMultiples',function(){
	$("#tablaDetallesArchivos .newRow").remove();
});

$(document).on('change','#cargarMultiples',function(){
    //var cols = [];
    archivosNopermitidos= new Array();
    archivosNopermitidos=[];
    archivosTotales=0;
    estilo='text-center w100p';

    //var filasArchivos='';

    $("#tablaDetallesArchivos .newRow").remove();
    for(var ad=0; ad< this.files.length; ad++){
        var file = this.files[ad];
        nombre = file.name;
        tamanio = file.size;
        tipo = file.type;
        
        cols = [];
        // filasArchivos+='<tr class="filasArchivos"> <td>'+ nombre+'</td> <td> <b>Tamaño:</b>'+ tamanio+'</td> <td> <b>Tipo:</b> '+ tipo+'</td> <td class="text-center"> <span class="quitarArchivos"><input type="hidden" name="nombrearchivo" value="'+nombre+'" >    <span  class="btn bgc8" style="color:#fff;">    <span class="glyphicon glyphicon-remove"></span></sapn> </span> </td></tr> ';
       
        nombre = generateItem('span', {html:file.name});
        cols.push(generateItem('td', {
            style: estilo
        }, nombre));

        observacion = generateItem('form',{name:"flieCLC"+ad}, generateItem('textarea', {id:'txtObservacion'+ad,name:'txtObservacion'+ad,rows:'1',style:'resize: vertical;',class: 'form-control  selectNewObservacionFile'}));
        cols.push(generateItem('td', {
            style: 'w300p'
        }, observacion));

        quitar = generateItem('span', {class:'quitarArchivos glyphicon glyphicon-remove btn btn-xs bgc8',style:'color:#fff;display:none; '},generateItem('input',{type:'hidden',val:file.name}));
        cols.push(generateItem('td', {
            style: estilo
        }, quitar));
        
        tr = generateItem('tr', {
        class: 'text-center w100p newRow'
        }, cols);

       $("#tablaDetallesArchivos").find('tbody').append(tr);
       archivosTotales++;
    }

    
    $('#muestraAntesdeEnviar').show();
    $('#enviarArchivosMultiples').show();
});

function fnLoadFiles(){
    $("#tipoInputFile").empty();
    var m=$("#esMultiple").val();
    opts={
            type: 'file',
            onpaste: 'return false',
            class: 'btn bgc8 form-control text-center',
            id: 'cargarMultiples',
            name: 'archivos[]',
            style:'display: none'
        };
   
    if(m!=0){
        type="multiple";
        opts['multiple']='multiple';
    }
    
    data= generateItem('input', opts);
    $("#tipoInputFile").append(data);
    
    $("#cargarMultiples").click(); // click to  new  element to trigger finder Dialog to  get files
}

// </Archivos>


function fnBajaActivos(){
	var fd = new FormData();
	var i =0;
	var j =0;

	fnGuardarResguardo('0');

	fd.append('option','bajaActivoFijo');
	fd.append('folio',$('#txtFolioResguardo').val());
	fd.append('observaciones',$('#txtObservaciones').val());

	var contadorInput = 1;
	$("#idDivActivosFijos").find(".txtAgregarObservaciones").each(function(index){
		fd.append('observaciones'+index,$(this).val());
		fd.append('txtKMInicial'+index,$("#txtKMInicial"+contadorInput).val());
		fd.append('txtKMFinal'+index,$("#txtKMFinal"+contadorInput).val());
		contadorInput++;
	});

	$("#divContenidoBaja").find(".txtIdActivoBaja").each(function(index){
		fd.append('chkSelectActivo'+i,$(this).val());
		i++;
	});

	$("#divContenidoBaja").find(".txtObservacionesBaja").each(function(index){
		fd.append('txtObservacionesBaja'+j,$(this).val());
		j++;
	});

	fd.append('numActivosBaja',i);

	$.ajax({
	    async:false,
	    url:"modelo/resguardo_detalles_modelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		$('#modalBajaActivo').modal('hide');
	    		var dataJson=data.contenido;
	    		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

	    		for( var info in dataJson){
	    			muestraModalGeneral(3,titulo,data.Mensaje,'','fnRecargarPaginaResguardo(\''+dataJson[info].urlFolio+'\')');
	    		}
	    	}
	    }
	}); 
}

function fnLimpiarCamposActivos(){
	$('#idDivActivosFijos').empty();
    fnLimpiarCamposForm('frmResguardoEncabezado');

    var d = new Date();
    var mes =(d.getMonth()+1);

    if(String(mes).length ==1){
    	mes= '0'+String(mes);
    }
    
    $('#txtFechaInicial').val(''+d.getDate()+'-'+mes+'-'+d.getFullYear());
}

function fnCargarInicio(){
	var txtUR = fnObtenerOption('selectUnidadNegocio');
	if(txtUR==""){
		txtUR="'-1'";
	}

	fnCargarUnidadEjecutoraReguardo(txtUR,'selectUnidadEjecutora','1');

	fnObtenerOptionEmpleados(fnObtenerOption('selectUnidadNegocio'),fnObtenerOption('selectUnidadEjecutora'),'selectEmpleadotab2');
}

function fnCargarResguardo(intFolio){
	$("#btnCancelarCampos").addClass('hide');
	fnObetenerResguardo();
}

function fnCargarUnidadEjecutoraReguardo(ur,componente,multiple){
	muestraCargandoGeneral();
    var seleccionado = "";
    var contenido = "";
    var contenidoDependencia = "";
    $('#' + componente).empty();
    $('#' + componente).multiselect({
        disableIfEmpty: true,
    });
    //Opcion para operacion
    dataObj = {
        option: 'mostrarUnidadEjecutora',
        tagref: ur,
        multiple: multiple
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

            var opcionDefault = 1;
            if ($("#"+componente).prop("multiple")) {
                var opcionDefault = 0;
            }

            if (dataJson.length == 1) {
                seleccionado = " selected ";
            } else if (opcionDefault == 1) {
                // Si tiene mas opciones mostrar opcion de seleccion
                
            }

            contenido += "<option value='-1'>Sin Seleccion</option>";

            for (var info in dataJson) {
                contenido += "<option value='" + dataJson[info].value + "'" + seleccionado + ">" + dataJson[info].texto + "</option>";
            }
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
        // console.log("ERROR");
        // console.log( result );
        ocultaCargandoGeneral();
    });
}

function fnObtenerOption(componenteSelect){
	var valores = "";
    var select = document.getElementById(''+componenteSelect);

    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (select.selectedOptions[i].value != '-1') {
            // Que no se opcion por default
            if (i == 0) {
                valores = "'"+select.selectedOptions[i].value+"'";
            }else{
                valores = valores+", '"+select.selectedOptions[i].value+"'";
            }
        }
    }

    return valores;
}

function fnObetenerResguardo(){
	var folio_resguardo=$('#txtFolioResguardo').val();

	var fd = new FormData();
	fd.append("option","obtenerResguardo");
	fd.append("intFolio",folio_resguardo);

	$.ajax({
	    async:false,
	    url:"modelo/resguardo_detalles_modelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		dataencabezado = data.contenido.encabezado;
	    		dataActivosJason = data.contenido.datos;
		        columnasNombres = data.contenido.columnasNombres;
		        columnasNombresGrid = data.contenido.columnasNombresGrid;
		        var strOptionEmpleado="";
		        var strOptionUR = "";
		        var strOptionUE= "";
                var estatusRes="";
		        var idEstatus=1;


		        for (var index in dataencabezado) {
		        	$("#txtFechaInicial").val(dataencabezado[index].Fecha_Registro);
		        	$("#txtFechaFinal").val(dataencabezado[index].Fecha_Ultima);
		        	strOptionEmpleado = "<option value='"+dataencabezado[index].idEmpleado+"' selected>"+dataencabezado[index].Empleado+"</option>";
		        	strOptionUR = "<option value='"+dataencabezado[index].UR+"' selected>"+dataencabezado[index].URDescripcion+"</option>";
		        	strOptionUE = "<option value='"+dataencabezado[index].UE+"' selected>"+dataencabezado[index].UEDescripcion+"</option>";
                    $('#txtObservaciones').val(dataencabezado[index].Observaciones);
		        	$('#txtUbicacion').val(dataencabezado[index].ln_ubicacion);

		        	estatusRes="Actual";
                    idEstatus=dataencabezado[index].estatus;
		        	if(dataencabezado[index].estatus=='0'){
                        fnBloquearDivs("divCloseTab");
                        fnBloquearDivs("PanelAddarchivo");
                        fnBloquearDivs("rowActivo");
                        
                        
						$("#guardarResguardo").addClass('hide');
		        		estatusRes="Historico";
		        		$("#btnAgregarActivo").addClass('hide');
		        	}else{
		        		$('#btnBajaActivo').removeClass('hide')
		        	}
		        	
		        	$("#txtEstatusRes").val(''+dataencabezado[index].nameEstatus);
		        }

		        $('#selectUnidadNegocio').empty();
		        $('#selectUnidadNegocio').append(strOptionUR);
		        $('#selectUnidadNegocio').multiselect('rebuild');

		        $('#selectUnidadNegocio_modal').empty();
		        $('#selectUnidadNegocio_modal').append(strOptionUR);
		        $('#selectUnidadNegocio_modal').multiselect('rebuild');

		        $('#selectUnidadEjecutora').empty();
		        $('#selectUnidadEjecutora').append(strOptionUE);
		        $('#selectUnidadEjecutora').multiselect('rebuild');

		        $('#selectUnidadEjecutora_modal').empty();
		        $('#selectUnidadEjecutora_modal').append(strOptionUE);
		        $('#selectUnidadEjecutora_modal').multiselect('rebuild');

		        $('#selectEmpleadotab2').empty();
		        $('#selectEmpleadotab2').append(strOptionEmpleado);
		        $('#selectEmpleadotab2').multiselect('rebuild');

		        $('#selectEmpleados_modal').empty();
		        $('#selectEmpleados_modal').append(strOptionEmpleado);
		        $('#selectEmpleados_modal').multiselect('rebuild');
                fnBloquearCampos();
		        contador=1;
		        for( var nFilas in dataActivosJason){
		        	var strHTML="";
		        	var lineid = "idElementA"+contador;
					var lineTipoBien = "txtTipoBien"+contador;
					var lineActivo = "txtDescripcionActivo"+contador;
					var lineDivActivo = "divSelectActivo"+contador;
					var lineCheck ="chkSelectActivo"+contador;
		        	var cssReadOnly="";
		        	var cssReadOnlyTipo="readonly";
		        	var datavehiculo="";
		        	var classvehiculo="";

					var style="-webkit-transform: scale(2);-moz-transform: scale(2);-ms-transform: scale(2);-o-transform: scale(2);transform: scale(2);";
					
					strHTML += '<div id="'+lineid+'" class="row borderGray p0 m0 text-center w100p fl form-group rowActivo">';
					strHTML += '	<div class="col-lg-12 col-md-12 col-sm-12 p0 m0">';
					strHTML += '		<div class="w3p  fl">';
					strHTML += '			<div id="idContentAnexoTecnico" class="row pt10 m0">';
		            strHTML += '				<div class="w10p text-center"></div>';
						
					if(dataActivosJason[nFilas].estatus !="Baja"){
			            strHTML += '				<input class="w20p chkSelectActivo" type="checkbox" id="'+lineCheck+'" name="'+lineCheck+'" style="'+style+'" value="'+dataActivosJason[nFilas].idActivo+'" data-activo="'+dataActivosJason[nFilas].barcode+'" data-filas="'+contador+'">';
					}else{
						cssReadOnly="readonly";


					}

					//Tipo vehiculo
					if(dataActivosJason[nFilas].idtipoBien == "3"){
						cssReadOnlyTipo ="";
						datavehiculo=" data-rowvehiculo='"+contador+"'";
						classvehiculo="clsVehiculoOld";
                        if(dataActivosJason[nFilas].estatus =="Baja"){
                            cssReadOnlyTipo="readonly";
                        }
					}

		           	strHTML += '			</div>';
		           	strHTML += '		</div>';
					strHTML += '		<div class="w15p fl">'+dataActivosJason[nFilas].barcode+'</div>';
					strHTML += '		<div id="'+lineTipoBien+'" class="w7p fl text-center" >'+dataActivosJason[nFilas].tipoBien+'</div>';
					strHTML += '		<div id="'+lineActivo+'" class="w20p fl text-left" >'+dataActivosJason[nFilas].description+'</div>';
					strHTML += '		<div class="w7p fl">'+dataActivosJason[nFilas].estatus+'</div>';
					strHTML += '		<div class="w7p fl text-center">'+dataActivosJason[nFilas].fecha+'</div>';
					strHTML += '		<div class="w7p fl text-center">'+dataActivosJason[nFilas].fecha_baja+'</div>';
					strHTML += '		<div class="w7p fl text-center '+classvehiculo+'" '+datavehiculo+'><component-text class="txtKMInicial" type="text" id="txtKMInicial'+contador+'" name="txtKMInicial'+contador+'" placeholder="Km. Inicial" value="'+dataActivosJason[nFilas].kmInicial+'" disabled></component-text></div>';
					strHTML += '		<div class="w7p fl text-center"><component-text class="txtKMFinal"  type="text" id="txtKMFinal'+contador+'" name="txtKMFinal'+contador+'" placeholder="Km. Final" value="'+dataActivosJason[nFilas].kmFinal+'" '+cssReadOnlyTipo+'></component-text></div>';
					strHTML += '		<div class="w20p fl">';
					strHTML += '			<input type="hidden" id="txtidPartidas'+contador+'" name="txtidPartidas'+contador+'" class="txtidPartidas" value="'+dataActivosJason[nFilas].idActivo+'">';
					strHTML += '			<textarea id="txtAgregarObservaciones'+contador+'" name="txtAgregarObservaciones'+contador+'" data-nuevo="0" rows="1" class="w100p form-control txtAgregarObservaciones" placeholder="Observaciones" '+cssReadOnly+' style="resize: vertical;">'+dataActivosJason[nFilas].observaciones+'</textarea>';
					strHTML += '		</div>';
					strHTML += '	</div>';
					strHTML += '</div>';
					contador++;

                    $("#idDivActivosFijos").empty; 
					$("#idDivActivosFijos").append(strHTML); 
					fnEjecutarVueGeneral(lineid);
		        }

                fnObtenerArchivos(folio_resguardo);

                if(idEstatus =="0"){
                    $(".quitarArchivos").addClass('hide');
                    $(".chkSelectActivo").addClass('hide');
                    $("#btnUploadFile").addClass('hide');

                    $('.txtAgregarObservaciones').prop('disabled', true);
                    $('.txtKMInicial').prop('disabled', true);
                    $('.txtKMFinal').prop('disabled', true);
                    
                    
                }
		        
		      
		        // fnLimpiarTabla('divTabla', 'divDetalle');

		        // var nombreExcel = data.contenido.nombreExcel;
		        // var columnasExcel= [0,1,2,3,4];
		        // var columnasVisuales= [0,1,2,3,4,5,6];
		        // fnAgregarGrid_Detalle(dataActivosJason, columnasNombres, columnasNombresGrid, 'divDetalle', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
       
	        }
	    }
	}); 
}

function fnAgregarResguardo(){

	var fd = new FormData(document.getElementById('frmAgregarResguardo'));
	fd.append('option','agregarResguardo');
	fd.append('folio',$('#txtFolioResguardo').val());

	$.ajax({
	    async:false,
	    url:"modelo/resguardo_detalles_modelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	$('#ModalUR').modal('hide');

	    	if(data.result){
            	muestraMensaje(data.Mensaje, 1, 'divMensajeOperacion', 5000);
	    		fnObetenerResguardo();
	        }else{
              muestraMensaje(data.Mensaje, 3, 'divMensajeOperacion', 5000);
          	}
	    }
	});
}

function fnModificar(idDetalleResguardo){
	$('#modalModificar').modal('show');
}

function fnAgregarPartidaTabla(){

	var nFilas = $("#idDivActivosFijos").find("div[id].rowActivo").length;
	var strHTML = "";
	var msjErr = "";

	if(fnObtenerOption('selectUnidadNegocio') ==""){
		msjErr+="<p>Es necesario seleccionar una <strong>UR</strong>, Para continuar.</p>";
	}

	if(fnObtenerOption('selectUnidadEjecutora') ==""){
		msjErr+="<p>Es necesario seleccionar una <strong>UE</strong>, Para continuar.</p>";
	}

	if(fnObtenerOption('selectEmpleadotab2') ==""){
		msjErr+="<p>Es necesario seleccionar un <strong>Empleado</strong>, Para continuar.</p>";
	}

	if (msjErr !=""){
		muestraModalGeneral(3,'Advertencia de datos',msjErr);
		return false;
	}

	nFilas=nFilas+1;

	var lineid = "idElementA"+nFilas;
	var lineTipoBien = "txtTipoBien"+nFilas;
	var lineActivo = "txtDescripcionActivo"+nFilas;
	var lineDivActivo = "divSelectActivo"+nFilas;
	
	strHTML += '<div id="'+lineid+'" class="row borderGray p0 m0 text-center w100p fl form-group renglonNuevoActivo rowActivo">';
	strHTML += '	<div class="col-lg-12 col-md-12 col-sm-12 p0 m0">';
	strHTML += '		<div class="w3p pt2 fl"><button class="btn btn-xs btn-danger" onclick="fnBorrarPartida(\''+lineid+'\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></div>';
	strHTML += '		<div class="w15p fl"><select id="selectActivoFijo' + nFilas + '" name="selectActivoFijo' + nFilas + '"  class="form-control selectActivoFijo" data-todos="true"  required onchange="fnSleccionarActivo(this,' + nFilas + ')"></select></div>';
	strHTML += '		<div id="'+lineTipoBien+'" class="w7p fl text-center" >&nbsp</div>';
	strHTML += '		<div id="'+lineActivo+'" class="w20p fl text-left" >&nbsp</div>';
	strHTML += '		<div class="w7p fl">Activo</div>';
	strHTML += '		<div class="w7p fl text-center">&nbsp</div>';
	strHTML += '		<div class="w7p fl text-center">&nbsp</div>';
	strHTML += '		<div class="w7p fl text-center"><component-text type="text" id="txtKMInicial'+nFilas+'" name="txtKMInicial'+nFilas+'" placeholder="Km. Inicial" disabled></component-text></div>';
	strHTML += '		<div class="w7p fl text-center"><component-text type="text" id="txtKMFinal'+nFilas+'" name="txtKMFinal'+nFilas+'" placeholder="Km. Final" disabled></component-text></div>';
	strHTML += '		<div class="w20p fl"><textarea  style="resize: vertical;" id="txtAgregarObservaciones'+nFilas+'" data-nuevo="1" name="txtAgregarObservaciones'+nFilas+'" rows="1" class="w100p form-control txtAgregarObservaciones" placeholder="Observaciones" ></textarea></div>';
	strHTML += '	</div>';
	strHTML += '</div>';

	$("#idDivActivosFijos").append(strHTML); 
	fnEjecutarVueGeneral(lineid);
	fnFormatoSelectGeneral('#selectActivoFijo' + nFilas );
	fnObtenerActivoFijos(fnObtenerOption('selectUnidadNegocio'),fnObtenerOption('selectUnidadEjecutora'),'selectActivoFijo' + nFilas);
	fnDesabilitarCombos();
}

function fnBorrarPartida(div){
	$('#'+div).remove();

    var leghPartidas = $("#idDivActivosFijos").find(".renglonNuevoActivo").length;
    if($('#txtFolioResguardo').val() ==""){
        if(leghPartidas == 0){
            $('#selectUnidadEjecutora').multiselect('enable');
            $('#selectUnidadNegocio').multiselect('enable');
            $('#selectEmpleadotab2').multiselect('enable');
        }

    }
}

function fnDesabilitarCombos(){
    $('#selectEmpleadotab2').multiselect('disable');
    $('#selectUnidadEjecutora').multiselect('disable');
    $('#selectUnidadNegocio').multiselect('disable');
}

function fnSleccionarActivo(option,fila){

    var assetid = option.value;

    //console.log(fnValidarSeleccionActivo(assetid));

    if(fnValidarSeleccionActivo(assetid) == false){
    	muestraModalGeneral(3,'Advertencia de datos','El activo ya fue seleccionado.');
    	$('#selectActivoFijo'+fila).multiselect('select','-1');
    	$('#selectActivoFijo'+fila).multiselect('refresh');
    	$('#txtDescripcionActivo'+fila).text('');
    	$('#txtTipoBien'+fila).text('');
    	return false;
    }

    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data:{option:'muestraActivoEspecifico',assetid:assetid}
    }).done(function(res){
        if(!res.result){return;}

        $.each(res.contenido,function(index, el) {
            $('#txtDescripcionActivo'+fila).text(el.descripcion);
            $('#txtTipoBien'+fila).text(el.tipoBien);
            
            if(el.idtipoBien == "3"){
            	$('#txtKMInicial'+fila).prop('disabled',false);
            	//$('#txtKMFinal'+fila).prop('disabled',false);
            	$('#txtKMInicial'+fila).addClass( "clsVehiculoInicial" );
            	$( '#txtKMInicial'+fila ).attr('data-rowvehiculo', fila);
            }else{
            	$('#txtKMInicial'+fila).prop('disabled',true);
            	$('#txtKMFinal'+fila).prop('disabled',true);
            	$('#txtKMInicial'+fila).removeClass( "clsVehiculoInicial" );
            	$( '#txtKMInicial'+fila ).attr('data-rowvehiculo', fila);

            }
        });
        
    }).fail(function(res){
    	console.log('Error enssdf');
        throw new Error('No se logro cargar la información del componente activos fijos');
    });

}

function fnValidarSeleccionActivo(assetid){
	var conincidencias = 0;

    $("#idDivActivosFijos").find(".selectActivoFijo").each(function(index){
    	option = fnObtenerOption($(this).attr('id'));
		option = option.replace("'", "");
		option = option.replace("'", "");

    	if(assetid == option){
    		conincidencias = parseInt(conincidencias) + 1;
    	}

    });

    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/componentes_modelo.php",
        data:{option:'existeEnResguardo',assetid:assetid , 'empleado':fnObtenerOption('selectEmpleadotab2')}
    }).done(function(res){
        if(!res.result){return;}
        
        
    }).fail(function(res){
        throw new Error('No se logro cargar la información del componente activos fijos');
    });

    if(parseInt(conincidencias)>=2){
    	return false;
    }else{
    	return true;
    }

}

function fnValidarUsuario(){
    var blnResult = true;
    var fd = new FormData();

    fd.append('empleado',fnObtenerOption('selectEmpleadotab2'));
    fd.append('option','verificarEmpleado');



    $.ajax({
        async:false,
        url:"modelo/resguardo_detalles_modelo.php",
        type:'POST',
        data: fd, 
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (data) {
            if(data.result){
                $.each(data.contenido.datos,function(index, el) {
                    if(el.blnActivo != "1"){
                        blnResult = false;
                        muestraModalGeneral(3,tituloGeneral,'El empleado no esta activo');
                    }
                });
                
            }
        }
    });


    return blnResult;
}

function fnGuardarResguardo( mostrar = '1'){

    if(fnValidarCampos()== false){return false;}

	if(fnValidarUsuario()== false){return false;}

	var fd = new FormData();

	
	if(intGetFolio !=""){
		fd.append('option','modificarResguardo');
	}else{
		fd.append('option','nuevoResguardo');
	}

	fd.append('ur',fnObtenerOption('selectUnidadNegocio'));
	fd.append('ue',fnObtenerOption('selectUnidadEjecutora'));
	fd.append('empleado',fnObtenerOption('selectEmpleadotab2'));
    fd.append('observaciones',$('#txtObservaciones').val());
	fd.append('txtUbicacion',$('#txtUbicacion').val());
	fd.append('numActivos',$("#idDivActivosFijos").find(".selectActivoFijo").length);
	fd.append('numActivosObservaciones',$("#idDivActivosFijos").find(".txtAgregarObservaciones").length);
	fd.append('numPartidasObs',$("#idDivActivosFijos").find(".txtidPartidas").length);
	fd.append('numObservacionesOldFile',$("#PanelAddarchivo").find(".selectOldObservacionFile").length);

	contadorInput=1;
	$("#idDivActivosFijos").find(".selectActivoFijo").each(function(index){

		if($(this).attr('id')){
			fd.append($(this).attr('id'),fnObtenerOption($(this).attr('id')));
		}
	});	

	$("#idDivActivosFijos").find(".txtAgregarObservaciones").each(function(index){
		fd.append('observaciones'+index,$(this).val());
		fd.append('txtKMInicial'+index,$("#txtKMInicial"+contadorInput).val());
		fd.append('txtKMFinal'+index,$("#txtKMFinal"+contadorInput).val());

        fd.append('parNuevo'+index,$(this).data('nuevo'));
		contadorInput++;
	});	

	$("#PanelAddarchivo").find(".selectNewObservacionFile").each(function(index){
		fd.append('observacionFile'+index,$(this).val());
	});


	$("#PanelAddarchivo").find(".selectOldObservacionFile").each(function(index){
		fd.append('observacionOldFile'+index,$(this).val());
		fd.append('OldIdFile'+index,$(this).data('idfile'));
	});	

	$("#idDivActivosFijos").find(".txtidPartidas").each(function(index){
		fd.append('idPartida'+index,$(this).val());
	});	

	if(($("#cargarMultiples").length) &&($("#cargarMultiples").val()!='') ){
	    var nFile = document.getElementById('cargarMultiples').files.length;
	     if(nFile>0){
	        for (var x = 0; x < nFile; x++) {
	            fd.append("archivos[]", document.getElementById('cargarMultiples').files[x]);
	        }
	    }
    }

	$.ajax({
	    async:false,
	    url:"modelo/resguardo_detalles_modelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	$('#cargarMultiples').val('');
	    	if(data.result){
	    		if(mostrar=='1'){
	    			var dataJson= data.contenido;
		    		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    		if(data.Mensaje == "1"){
		    			$('#selectEmpleadotab2').prop('disabled',false);
		    			muestraModalGeneral(3,titulo,'Ya existe un resguardo para el empleado seleccionado');
		    		}else{
		    			for( var info in dataJson){
			    			muestraModalGeneral(3,titulo,data.Mensaje,'','fnRecargarPaginaResguardo(\''+dataJson[info].urlFolio+'\')');
			    		}
		    		}
                    fnBloquearCampos();
		    		
	    		}
	    		
	    	}
	    }
	}); 
}


function fnBloquearCampos(){

    $('#selectUnidadNegocio').multiselect('disable');
    $('#selectUnidadEjecutora').multiselect('disable');
    $('#selectEmpleadotab2').multiselect('disable');

}

function fnObtenerArchivos(folio){
	
	var fd = new FormData();
	fd.append("option","obtenerArchivos");
	fd.append("funcion",'2308');
	fd.append("type_mov",'1002');
	fd.append("transno_mov",folio);

	$.ajax({
	    async:false,
	    url:"modelo/resguardo_detalles_modelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		//Mostrar Archivos 
    			var trArchivos="";
    			var cols = [];
    			var estilo='text-center w100p';
    			var archivos=0;
    			var datosDetalleArchivos = data.contenido.datos;
    			
    			$("#tablaDetallesArchivos >tbody").empty();

    			for(var index3 in datosDetalleArchivos){
    				cols = [];

			        nombre = generateItem('a', {href:datosDetalleArchivos[index3].urlFile,target:'_blank', html:datosDetalleArchivos[index3].nameFile});
			        cols.push(generateItem('td', {
			            style: estilo
			        }, nombre));

			        textareaObs='<textarea id="txtObservacion'+index3+'" name="txtObservacion'+index3+'" rows="1" data-idFile="'+datosDetalleArchivos[index3].idFile+'" style="resize: vertical;" class="form-control  selectOldObservacionFile" value="rewerwer" autocomplete="off">'+datosDetalleArchivos[index3].txt_descripcion+'</textarea>';

			        observacion = generateItem('form',{name:"flieCLC"+index3},textareaObs);
			        cols.push(generateItem('td', {
			            style: 'w300p'
			        }, observacion));

			        quitar = generateItem('span', {class:'quitarArchivos glyphicon glyphicon-remove btn btn-xs bgc8',style:'color:#fff', onclick:'fnMensajeConfirmacion('+datosDetalleArchivos[index3].idFile+');'},generateItem('input',{type:'hidden',val:datosDetalleArchivos[index3].idFile}));
			        cols.push(generateItem('td', {
			            style: estilo
			        }, quitar));
			        
			        tr = generateItem('tr', {
			        class: 'text-center w100p',
			        id:'row'+datosDetalleArchivos[index3].idFile
			        }, cols);

			       $("#tablaDetallesArchivos").find('tbody').append(tr);
			       archivos++;

    			}

    			if(archivos >0){
    				$('#muestraAntesdeEnviar').show();
    			}
	        }
	    }
	});
}


function fnMensajeConfirmacion(idArchivo){
	muestraModalGeneralConfirmacion(3,tituloGeneral,'¿Desea eliminar el archivo?','','fnRemoverPartida('+idArchivo+')');
}

function fnRemoverPartida(idArchivo){
	var fd = new FormData();
	fd.append("option","removerArchivo");
	fd.append("idArchivo",idArchivo);

	$.ajax({
	    async:false,
	    url:"modelo/solicitudMinistracionModelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		var parent = document.getElementById('row'+idArchivo).parentNode;
        		parent.removeChild(document.getElementById('row'+idArchivo));
	    		muestraModalGeneral(3,tituloGeneral,data.Mensaje);
	        }
	    }
	}); 
}


function fnRecargarPaginaResguardo(urlEncryption){
	window.location.href=urlEncryption;
}

function fnValidarCampos(){

	var option="";
	var msjErr="";
	var vacios=0;

	if(fnObtenerOption('selectUnidadNegocio') == ""){
		msjErr += '<p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> El campo UR es obligatorio. </p>';
	}

	if(fnObtenerOption('selectUnidadEjecutora') == ""){
		msjErr += '<p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> El campo UE es obligatorio. </p>';
	}

	if(fnObtenerOption('selectEmpleadotab2') == ""){
		msjErr += '<p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> El campo Empleado es obligatorio. </p>';
	}

	$("#idDivActivosFijos").find(".clsVehiculoInicial").each(function(index){
	    if($(this).val() == "" || $(this).val() == "0"){
	    	msjErr += '<p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> En el renglo '+$(this).data('rowvehiculo')+' es necesario capturar el Km. Inicial.</p>';
	    }
	    
	});

	if(msjErr!=""){
		muestraModalGeneral(3,'Advertencia de datos',msjErr);
		return false;
	}

	$("#idDivActivosFijos").find(".selectActivoFijo").each(function(index){
	    
	    option = fnObtenerOption($(this).attr('id'));

	    option = option.replace("'", "");
		option = option.replace("'", "");
		//console.log(option);
	    if(option =="" || option =="-1"){
	    	vacios= parseInt(vacios)+1;
	    	
	    }
	});


	if(parseInt(vacios)>=1){
		muestraModalGeneral(3,'Advertencia de datos','<p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Existen campo sin selección de activo fijo.</p>');
	    return false;
	}else{
		return true;
	}

}


