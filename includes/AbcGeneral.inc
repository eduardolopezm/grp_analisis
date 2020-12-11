<?php
/**
 * grp
 *
 * @category Inicio
 * @package  grp
 * @author   grp <[<email address>]>
 * @license  [<url>] [name]
 * @version  1.0
 * @link     (target, link)
 * Fecha Creación: 31/10/2018
 * Fecha Modificación: 31/10/2018
 */
function generateHtml($db, $fun)
{
    '<div align="center">
	  <component-button
	  	type="button"
	  	id="btnAgregar"
	  	name="btnAgregar"
	  	onclick="fnAgregarCatalogoModal()"
	  	value="Nuevo"
	  	class="glyphicon glyphicon-plus">
	  	</component-button>
	  <br>
	  <br>
	</div>';
    $str = "[
		'div'=>[
			'prop'=>[
				'align'=>'center',
				'class'=>'row'
			],
			'cont'=>[
				'component-button'=>[
					'prop'=>[

					]
				]
			]
		]
	]";
    $srt = "[
	    'label'=>[
	        'prop'=>[
	            'for'=>'prueba'
	        ],
	        'cont'=>'contenido prueba'
	     ],
	    'input'=>[
	        'prop'=>[
	            'class'=>'form-control w10p',
	            'id'=>'prueba',
	            'name'=>'prueba'
	        ]
	     ]
	]";

    var_dump($srt);
    echo '<br>';
    eval('$arr = ' . $srt . ';');
    // $arr = eval(''$srt);
    var_dump($arr);
}
function obtencionPanel($db, $fun)
{
    $data = ['htmlPanel' => '', 'tblObj' => '', 'tblTitulo' => '', 'tblVisual' => '', 'tblExcel' => ''];
    if (!Havepermission($_SESSION['UserID'], $fun, $db)) {return $data;}
    $sql = "SELECT  `ln_configuracion`,`ln_grid`, `ln_grid_col`, `ln_col_visual`, `ln_col_excel`
		FROM `tb_cat_panel_catalogo`
		WHERE `ind_activo` = 1
		AND `id_nu_funcion` = '$fun'";
    $result = DB_query($sql, $db);
    while ($rs = DB_fetch_array($result)) {
        $data['htmlPanel'] = $rs['ln_configuracion'];
        $data['tblObj']    = $rs['ln_grid'];
        $data['tblTitulo'] = $rs['ln_grid_col'];
        $data['tblVisual'] = $rs['ln_col_visual'];
        $data['tblExcel']  = $rs['ln_col_excel'];
    }
    return $data;
}
