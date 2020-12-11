<?php
        if(isset ($_POST['obtener']))
        {
            include 'includes/qrRepositorio.php';
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
            <legend>Datos</legend>
        <form method="post" action="qr.php">
            <label>RFC</label><input type="text" name="rfc" id="rfc" value="<?php if(isset($_POST['rfc'])){echo $_POST['rfc'];} ?>" /><br />
            <label>UUID</label><input type="text" name="UUID" id="UUID" value="<?php if(isset($_POST['UUID'])){echo $_POST['UUID'];} ?>" /><br />
            <input type="submit" name="obtener" id="obtener" value="Obtener QR" />
        </form>
            <fieldset>
                <legend>Log</legend>
                <?php
                    if(isset($log))
                    echo $log;
                ?>
            </fieldset>
            <fieldset>
                <legend>QR</legend>
                <?php
                if(isset($qr))
                {
                    /*
                     * Mostrar imagen QR
                     */
                    if(isset($s_Filename))
                    echo "<img src='$s_Filename'></img>";
                }

                ?>


            </fieldset>


        </fieldset>
    </body>
</html>
