<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8">
        <title>Editor de Captura de Requisición</title>
        <link href="css/Captura_Requisicion.css" rel="stylesheet" type="text/css" />
        <!-- Make sure the path to CKEditor is correct. -->
        <script src="ckeditor/ckeditor.js"></script>
    </head>
    <body>
        <form id="idEditorRequisicion">
            <textarea name="editor1" id="editor1" rows="500" cols="80">
                El listado de Articulos o Servicios se ve aqui ...
            </textarea>
            <script>
                // Replace the <textarea id="editor1"> with a CKEditor
                // instance, using default configuration.
                //CKEDITOR.replace( 'editor1' );
                CKEDITOR.replace( 'editor1', {
                    width: '90%',
                    height: 500
                } );
            </script>
        </form>
    </body>
</html>