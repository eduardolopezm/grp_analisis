var tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

$(document).ready(function (){

    $('#btn-search').click(function(){
        fnObtenerUsuarios();
    });

    fnObtenerUsuarios();

    $('#btnNuevo').click(function(){
        window.location.href ="usuarioDetalle.php";
    });

});


function fnObtenerUsuarios(){
	var fd = new FormData();
	fd.append("option","obtenerUsuarios");
	fd.append("selectUnidadNegocio",fnObtenerOption("selectUnidadNegocio"));
	fd.append("selectEstatusUsuario",fnObtenerOption("selectEstatusUsuario"));
	fd.append("txtNombreUsuario", $('#txtNombreUsuario').val());
	fd.append("selectPerfilUsuario",fnObtenerOption('selectPerfilUsuario'));

	$.ajax({
	    async:false,
	    url:"modelo/www_users_modelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {
	    	if(data.result){
	    		datosJason = data.contenido.datos;
	            columnasNombres = data.contenido.columnasNombres;
	            columnasNombresGrid = data.contenido.columnasNombresGrid;

	            var nombreExcel = data.contenido.nombreExcel;

	            var columnasExcel= [0,1,2,3,4,5];
	            var columnasVisuales= [0,1,2,3,4,5,6];
	            
	            fnLimpiarTabla('divtabla', 'divDatos');
	            
	        	fnAgregarGrid_Detalle(datosJason, columnasNombres, columnasNombresGrid, 'divDatos', ' ', 1, columnasExcel, false, true, "", columnasVisuales, nombreExcel);
	    		
	        }
	    }
	}); 
}

function fnObtenerOption(componenteSelect, intComillas = 0){
    var valores = "";
    var comillas="'";
    var select = document.getElementById(''+componenteSelect);
    for ( var i = 0; i < select.selectedOptions.length; i++) {
        if (select.selectedOptions[i].value != '-1') {
            if(intComillas == 1){
                comillas="";
            }
            if (i == 0) {
                valores = ""+comillas+select.selectedOptions[i].value+comillas+"";
            }else{
                valores = valores+", "+comillas+select.selectedOptions[i].value+comillas+"";
            }
        }
    }
    return valores;
}