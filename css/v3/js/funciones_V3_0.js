

$(document).ready(function (){
  /*   sessionTimeOut  */
  $(document).idleTimeout({
      redirectUrl: '/sagarpa/Logout.php?modulosel=6;',      // redirect to this url on logout. Set to "redirectUrl: false" to disable redirect
      // idle settings
      idleTimeLimit: 1500,           // 'No activity' time limit in seconds. 1500 = 25 Minutes
      idleCheckHeartbeat: 2,       // Frequency to check for idle timeouts in seconds
      // optional custom callback to perform before logout
      customCallback: false,       // set to false for no customCallback
      // customCallback:    function () {    // define optional custom js function
          // perform custom action before logout
      // },
      // configure which activity events to detect
      // http://www.quirksmode.org/dom/events/
      // https://developer.mozilla.org/en-US/docs/Web/Reference/Events
      activityEvents: 'click keypress scroll wheel mousewheel mousemove', // separate each event with a space
      // warning dialog box configuration
      enableDialog: true,           // set to false for logout without warning dialog
      dialogDisplayLimit: 20,       // 20 seconds for testing. Time to display the warning dialog before logout (and optional callback) in seconds. 180 = 3 Minutes
      dialogTitle: 'Alerta de Fin de Sesión ', // also displays on browser title bar
      dialogText: 'Tu sesi&oacute;n esta por expirar, debido a un gran tiempo de inactividad. ¿Qu&eacute; deseas hacer?',
      dialogTimeRemaining: 'Tiempo restante',
      dialogStayLoggedInButton: 'Permanecer',
      dialogLogOutNowButton: 'Salir ahora',
      // error message if https://github.com/marcuswestin/store.js not enabled
      errorAlertMessage: 'Tal vez tu navegador no soporte la funcionalidad de esta p&aacute;gina, verifica tu versi&oacute;n o comunicate con tu &aacute;rea de soporte.',
      // server-side session keep-alive timer
      sessionKeepAliveTimer: 600,   // ping the server at this interval in seconds. 600 = 10 Minutes. Set to false to disable pings
      sessionKeepAliveUrl: window.location.href // set URL to ping - does not apply if sessionKeepAliveTimer: false
    });
});

$(window).load(function() {
  xajax_inicio();
  console.log('cargar funcion inicio');
});

function mostrarOcultarFavoritos()
{
  $('#NavPanel1').fadeToggle();
}

xajax.loadingFunction = function() {
	$.blockUI({ message: '<i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span>' });
	//xajax.$('msg_load').style.display = 'block';
};

function hideLoadingMessage() {
	//xajax.$('msg_load').style.display = 'none';
	$.unblockUI();
}

xajax.doneLoadingFunction = hideLoadingMessage;


function mostrarOcultarFavoritos(event)
{
	event.preventDefault();
	$('#NavPanel1').fadeToggle();
	console.log('ocultar');
}

/**
 * [showdiv description]
 * @param  {[type]} selected [description]
 * @param  {[type]} iconc    [description]
 * @return {[type]}          [description]
 */
function showdiv(selected,iconc)
{ 
  // Menu año
  var objShow = document.getElementById(selected);
  var estado = $(selected).css('display');

  $(iconc).empty();

  if (estado == 'none')
  {
    $(selected).slideDown("medium");

    $(iconc).html('<i class="fa fa-angle-up fa-lg" onclick="showdiv(panel1,iconc);" style="cursor:pointer"></i>');
    //$('._header-two a').addClass('selected');
  } else {
    $(selected).slideUp("medium");
    $(iconc).html('<i class="fa fa-angle-down fa-lg" onclick="showdiv(panel1,iconc);" style="cursor:pointer"></i>');
    //$('._header-two a').removeClass('selected');
  }
};

function Show1(selected,uclick) { // Menu año
    var objShow = document.getElementById(selected);
    var estado = $(selected).css('display');
    //var selector= $(uclick)document.getElementById;
    var selector = uclick.toString();
    var selectora = '#';
    var res = selectora.concat(selector);
    //alert(uclick);
    if (estado == 'none') {
        $(selected).slideDown("medium");
        $(res).addClass('selected');
    } else {
        $(selected).slideUp("medium");
        $(res).removeClass('selected');
    }     
};


// Controlamos que si pulsamos escape se cierre del modal
/**
 * Controlar el cierre del modal con la tecla ESC
 * @param  {[type]} event){               if(event.which [description]
 * @return {[type]}          [description]
 */
$(document).keyup(function(event){
  if(event.which==27)
  {
    $("#myModal").modal("hide");
  }
});