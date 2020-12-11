<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
	if(isset ($_POST['asignar']))
        {
            include 'includes/asignarFoliosCliente.php';

        }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <fieldset id="status">
            <legend>Obtener Timbrado</legend>
        <form method="post" action="asignarFolios.php">
            <label>RFC</label><input type="text" name="rfc" id="rfc" value="<?php if(isset($_POST['rfc'])){echo $_POST['rfc'];} ?>" /><br />
            <label>Folios a Asignar</label><input type="text" name="timbresAsignar" id="timbresAsignar" value="<?php if(isset($_POST['timbresAsignar'])){echo $_POST['timbresAsignar'];} ?>" /><br />
            <label>Transaction ID</label><input type="hidden" name="trsID" id="trsID" value="<?php if(isset($_POST['trsID'])){echo $_POST['trsID'];} ?>" /><br />
            <input type="submit" name="asignar" id="asignar" value="Asignar Folios" />
        </form>
            <fieldset>
                <legend>Log</legend>
                <?php
                    if(isset ($log))
                    echo $log;
                ?>
            </fieldset>
            <fieldset>
                <legend>Timbre</legend>
                <textarea rows="20" cols="80"><?php if(isset ($timbre)) echo $timbre ?></textarea>
                
            </fieldset>


        </fieldset> 
    </body>
</html>
