<div class="form-inline">
	<div class="row" id="modalMsg"></div>
	<div class="row">
		<div class="col-sm-12">
			<div class="col-sm-6">
				<span>Descargar Layout para el Ánexo Técnico</span>
				<component-button type="button" id="btn-download" class="glyphicon glyphicon-download-alt"></component-button>
			</div>
			<div class="col-sm-6">
				<!-- <span>Cargar archivo</span> -->
				<!-- <component-label-text for="btn-upload" label="Cargar archivo" classC="glyphicon glyphicon-file" value=" "></component-label-text> -->
				<!-- <component-administrador-archivos></component-administrador-archivos> -->
				<label for="inpt-upload" class="btn btn-primary botonVerde glyphicon glyphicon-file" id="label-inot-upload"> Seleccionar archivo</label>
				<component-text type="file" id="inpt-upload" class="hidden" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, .csv" ></component-text>
				<component-button type="button" id="btn-upload" class="glyphicon glyphicon-file" value="Cargar"></component-button>
				<!-- <component-text type="file" id="btn-upload" class="hidden" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, cvs"></component-text> -->
			</div>
		</div>
	</div><!-- .row-->
	<div class="row hidden" id="showFiles">
		<div class="col-sm-12">
			<table class="table table-striped border" id="tbl-filesToUp" style="border:solid 1px #eee;">
				<thead class="bgc8" style="color:#fff;">
					<tr>
						<th>Nombre</th>
						<th>Tamaño</th>
						<th>Tipo Archivo </th>
						<th> </th> 
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div><!-- .row-->
	<div class="row">
		<div class="col-sm-12">
			<div id="content-files">
				<div id="files-upload"></div>
			</div>
		</div>
	</div>
</div><!-- .form-inline -->