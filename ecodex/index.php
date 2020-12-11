<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Servicio Sella/Timbra XML</title>
    </head>
    <body>
        <fieldset><legend>Timbrado/Sellado de XML</legend>
            <form action="process.php" method="post" enctype="multipart/form-data">
                <label>Sella-Timbra</label><input type="radio" name="opcion" value="1" checked="checked">
                <label>Timbra</label><input type="radio" name="opcion" value="2">
                <br>
                 <input type="hidden" name="carga_archivo" value="1">
                 <b>Seleccione el archivo XML: </b>
                 <br>
                 <input name="userfile" type="file">
                 <br>
                 <input type="submit" value="Enviar">
            </form>
        </fieldset>
        <fieldset><legend>Opciones</legend>
            <label><a href="timbrado">Opciones de Timbrado</a></label>
            <label><a href="repositorio">Opciones de Repositorio</a></label>
            <label><a href="cliente">Opciones de Cliente</a></label>
        </fieldset>
    </body>
</html>
