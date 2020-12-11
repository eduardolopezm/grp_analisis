
function clickGenerarPoliza (){
	$.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/activofijo_modelo.php",
            data: {CommitDepreciation: true}
        })
        .done(function(data) {
            var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
            if (data.result) {
                muestraMensaje(data.contenido, 1, 'OperacionMensaje', 5000);
                fnLimpiarTabla('divTabla', 'divCatalogo');
                fnMostrarDatos('');
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
}