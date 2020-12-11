<?php
/**
 * Pagina de inicio de aplicacion
 *
 * @category Inicio
 * @package ap_grp
 * @author Armando Barrientos Martinez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link (target, link)
 * Fecha Creación: 01/08/2017
 * Fecha Modificación: 21/08/2017
 */

$PageSecurity =1;

include('includes/session.inc');
$funcion=152;
include('includes/SecurityFunctions.inc');
include('includes/token_generate.php');
?>
<html>
    <head>
        <title>GRP Seguridad</title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo _('ISO-8859-1'); ?>" />
        <link rel="stylesheet" href="css/silverwolf/login.css" type="text/css" />

        <link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-3.3.7/css/bootstrap.min.css">
    </head>

    <body >
        <div id="container">
            <div id="login_logo"></div>
            <div id="login_box"></div>
            <div class="container-fluid" align="center">
                <form action=" <?php echo $rootpath;?>/index.php" name="loginform" method="post">
                    <label><?php echo _('Gracias por usar GRP'); ?></label>
                    <br />
                    <div id="demo_text">
                        <p><?php echo $demo_text;?></p>
                    </div>

                    <button type="submit" id="SubmitUser" name="SubmitUser" class="btn btn-default" value="<?php echo _('Login'); ?>">&nbsp;Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </body>
    </html>

    <?php
        deleteToken($db);
        session_start();
        session_unset();
        session_destroy();
    ?>
    </body>
</html>