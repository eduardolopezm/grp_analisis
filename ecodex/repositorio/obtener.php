<?php
        if(isset ($_POST['obtener']))
        {
            include 'includes/obtenerRepositorio.php';
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
            <legend>Obtener Comprobante</legend>
        <form method="post" action="obtener.php">
            <label>RFC</label><input type="text" name="rfc" id="rfc" value="<?php if(isset($_POST['rfc'])){echo $_POST['rfc'];} ?>" /><br />
            <label>UUID</label><input type="text" name="UUID" id="UUID" value="<?php if(isset($_POST['UUID'])){echo $_POST['UUID'];} ?>" /><br />
            <label>Transaction ID</label><input type="text" name="trsID" id="trsID" value="<?php if(isset($_POST['trsID'])){echo $_POST['trsID'];} ?>" /><br />
            <input type="submit" name="obtener" id="obtener" value="Obtener Timbrado" />
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
