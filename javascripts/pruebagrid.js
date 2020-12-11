var contenidosCvePartidaEspecificaArray = ["", "a"];
var lineas = ["a", "b"];
	var url = "modelo/datosgrid.php";
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'firstname' },
                    { name: 'amount', type: 'number' },
                    { name: 'productNames'},
                    { name: 'trandate', type: 'datetime' }
                ],
                updaterow: function (rowid, newdata, commit) {
                    // synchronize with the server - send update command
                    // call commit with parameter true if the synchronization with the server is successful 
                    // and with parameter false if the synchronization failed.ç
                    
                    
                },
                
                id: 'id',
                url: url,
                data: {tagref:"n", account: "b"},
                root: 'data'
            };
var dataAdapter = new $.jqx.dataAdapter(source);

function generarGrid(){
	     
            
            
        
}

function fnGrabarConfiguracionSituacionFinanciera (){
	
	Todos = $("#selectTodos").val();
	tagref = $("#selectUnidadNegocio").val();

	if (Todos==null) { alert("selecciona una cuenta contable"); return; }
	if (tagref=="-1") { alert("selecciona una UE Gasto"); return; }
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/agregarGLTRANS_modelo.php",
	      data:{ 
	        option: 'agregar',
	        cuentaContable: Todos,
	        tagref: tagref
	      }
	  })
	.done(function( data ) {
		var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
	    if(data.result){
	    	cargarDatosGrid();
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

function cargarDatosGrid() {
	if (($("#selectTodos").val()!=null) && ($("#selectUnidadNegocio").val()!="-1")) {
		source.data.tagref = $("#selectUnidadNegocio").val();
		source.data.account = $("#selectTodos").val();
		dataAdapter.dataBind();
	}



}

 function fnSeleccionarValorDelReporte() {


	cargarDatosGrid();
	
}




function fnFiltrarPorCuenta(aInfo, aFiltro, aControl)
{
	contenido = "";
	for (x in info) 
	{ 
		if (aFiltro == null)
			contenido += '<option value="'+aInfo[x].id_cuenta+'">' +aInfo[x].descripcion +'</option>';
		else
		if (aInfo[x].id_cuenta.startsWith(aFiltro))
	       contenido += '<option value="'+aInfo[x].id_cuenta+'">' +aInfo[x].descripcion +'</option>';
 	} 

 	$(aControl).html(contenido);
		 	$(aControl).multiselect({
		            enableFiltering: true,
		            filterBehavior: 'text'
		        });
}
 function fnMostrarDatos(ur){
	console.log("fnMostrarDatos");

	contenido = "";

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogoDeCuentas',
	        ur: ur
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/grp_configuracion_reportes_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;

	    	//construye todos las opciones del catalogo de cuentas
	    	

 

 			//fnFiltrarPorCuenta(info, null, '#selectTodos');

 
 			
	    	
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});



	generarGrid();

	

 	//}
 	
}


function fnCambioRazonSocial() {
	//console.log("fnObtenerUnidadNegocio");
	// Inicio Unidad de Negocio
	legalid = $("#selectRazonSocial").val();
	//Opcion para operacion
	dataObj = { 
	      option: 'mostrarUnidadNegocio',
	      legalid: legalid
	    };
	//Obtener datos de las bahias
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/imprimirreportesconac_modelo.php",
	    data:dataObj
	})
	.done(function( data ) {
	  //console.log("Bien");
	  if(data.result){
	      //Si trae informacion
	      
	      dataJson = data.contenido.datos;
	      //console.log( "dataJson: " + JSON.stringify(dataJson) );
	      //alert(JSON.stringify(dataJson));
	      var contenido = "<option value='0'>Seleccionar...</option>";
	      for (var info in dataJson) {
	        contenido += "<option value='"+dataJson[info].tagref+"'>"+dataJson[info].tagdescription+"</option>";
	      }
		$('#selectUnidadNegocio').empty();
		$('#selectUnidadNegocio').append(contenido);
		$('#selectUnidadNegocio').multiselect('rebuild');

	  }else{
	      // console.log("ERROR Modelo");
	      // console.log( JSON.stringify(data) ); 
	  }
	})
	.fail(function(result) {
	  // console.log("ERROR");
	  // console.log( result );
	});
	// Fin Unidad de Negocio
}





$( document ).ready(function() {
	//Mostrar Catalogo

	
	fnMostrarDatos('');


 var linkrenderer = function (row, column, value) {
                if (value.indexOf('#') != -1) {
                    value = value.substring(0, value.indexOf('#'));
                }
                var format = { target: '"_blank"' };
                //$.jqx.dataFormat.formatlink(value, format)
                var html = "<div selectTodos> asfdasdf</div>";

                return html;
            }

$("#gridDatos").jqxGrid(
            {
                width: 850,
                source: dataAdapter,
                columnsresize: true,
                editable: true,
                selectionmode: 'singlecell',
                editmode: 'click',
                columns: [
                  { text: 'firstname', dataField: 'firstname', width: 200, columntype: 'checkbox', createeditor: function (row, column, editor) {
                            // assign a new data source to the combobox.
                            lineas.push("a");
                            editor.jqxComboBox({ autoDropDownHeight: true, source: contenidosCvePartidaEspecificaArray, promptText: "Please Choose:" });
                            //editor.html("<b>a</b>")
                        },
                        initeditor: function (row, column, editor) {
                            // assign a new data source to the combobox.
                            lineas.push("a");
                            //editor.jqxComboBox({ autoDropDownHeight: true, source: contenidosCvePartidaEspecificaArray, promptText: "Please Choose:" });
                            //editor.html("<b>a</b>")
                        }},
                  
                  
                  { text: 'Cuenta', dataField: 'productNames', width: 180 , columntype: 'combobox', initeditor: function (row, column, editor) {
                            // assign a new data source to the combobox.
                            
                            editor.jqxComboBox({ autoDropDownHeight: true, source: lineas, promptText: "Please Choose:" });
                            //editor.html("<b>a</b>")
                        }},
                  { text: 'Fecha Transacción', dataField: 'trandate', width: 140}
                ]
            });

ocultaCargandoGeneral();

	
});