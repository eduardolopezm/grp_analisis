$( document ).ready(function(){
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

