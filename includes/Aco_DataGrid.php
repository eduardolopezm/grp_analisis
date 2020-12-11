<?php

class Aco_DataGrid
{

    private $sql;
    private $campos     = array();
    private $classCSS   = '';
    private $nombreGrid = '';
    private static $nombreGridAleatorio;
    private $separador       = '-{-';
    private $separadorLength = 3;
    private $conexion;
    private $arrayCampos = array();


    private $cellpadding  = 0;
    private $cellspacing  = 0;
    private $width        = 0;
    private $height       = 0;
    private $bgColor      = array('#FFFFFF');
    private $bgColorTh    = '#FFFFFF';
    private $bgColorTabla = '#FFFFFF';
    private $border       = 0;
    private $borderColor  = '#FFFFFF';
    private $background   = '';
    private $align        = 'center';

    private $contenidoF;
    private $alignF;
    private $colspanF;

    private $contenidoF2;
    private $alignF2;
    private $colspanF2;

    private $rem_columna      = array();
    private $rem_SortAcolumna = array();

    private $pgUbicacion = 3;
    private $pgCantidad;
    private $pgDespliegue;
    private $pgLang = array();
    public $gridInfo = array();

    public function iniciar($sql, $conexion = '', $campos, $classCSS = '', $pagina = '', $nombreGrid = '')
    {

        if (empty($classCSS) && empty($nombreGrid)) {
            self::$nombreGridAleatorio += 1;
            $this->nombreGrid = 'grid' . self::$nombreGridAleatorio;
        } else {
            list($this->nombreGrid) = explode(' ', (empty($nombreGrid) ? $classCSS : $nombreGrid));
        }

        if (!empty($pagina)) {
            $limit = $this->_grid_Pagina($pagina, $sql);
        }

        $this->sql      = $sql . $limit;
        $this->conexion = $conexion;
        $this->campos   = $campos;

        $this->classCSS = $classCSS;

        if (is_resource($this->conexion)) {
            $this->gridInfo['filas_todo'] = $this->_grid_filas(mysql_query($this->sql, $this->conexion));
            $consulta                     = mysql_query($this->sql, $this->conexion) or die(mysql_error());
        } else {
            $this->gridInfo['filas_todo'] = $this->_grid_filas(mysql_query($sql));
            $consulta                     = mysql_query($this->sql) or die(mysql_error());
        }

        $this->gridInfo['filas_por_pagina'] = $this->_grid_filas($consulta);
        if ($this->gridInfo['filas_todo'] > 0) {
            foreach ($this->campos as $key => $valor) {
                while ($fila = mysql_fetch_assoc($consulta)) {
                    $$valor .= $fila[$valor] . $this->separador;
                }

                $$valor                  = substr($$valor, 0, strlen($$valor) - $this->separadorLength);
                $this->arrayCampos[$key] = $$valor;
                $$valor                  = '';

                @mysql_data_seek($consulta, 0);
            }
        } else {
            return false;
        }
    }
    private function _explodeC($array)
    {
        return explode($this->separador, $array);
    }
    private function _implodeC($array)
    {
        return implode($this->separador, $array);
    }
    private function _explorador($contenido = '', $camposEscogido = '')
    {
        if (is_array($camposEscogido)) {
            foreach ($camposEscogido as $clave => $var) {
                $filasSeleccionadas[$clave] = $this->_explodeC($this->arrayCampos[$clave]);
            }
        } else {
            $filasSeleccionadas[$camposEscogido] = $this->_explodeC($this->arrayCampos[$camposEscogido]);
        }
        if (is_array($camposEscogido)) {
            $elemento = array_keys($camposEscogido);
        }
        $max = is_array($camposEscogido) ?
        count($filasSeleccionadas[$elemento[0]]) :
        count($filasSeleccionadas[$camposEscogido]);

        if (is_array($camposEscogido)) {
            for ($n = 0; $n < $max; $n++) {
                $cadena = $contenido;
                foreach ($camposEscogido as $clave => $valor) {
                    if (preg_match_all('/\{' . $valor . '\}/', $contenido, $m) || preg_match_all('/\&' . $valor . '\&/', $contenido, $m)) {
                        $cadena = preg_replace('/\{' . $valor . '\}/', $filasSeleccionadas[$clave][$n], $cadena);
                        $cadena = preg_replace('/\&' . $valor . '\&/', urlencode($filasSeleccionadas[$clave][$n]), $cadena);
                    } else {
                        continue;
                    }
                }

                $elementoIndivudual[$n] = $cadena;
            }
        } else {
            for ($n = 0; $n < $max; $n++) {
                $cadena                 = preg_replace('/\{\}/', $filasSeleccionadas[$camposEscogido][$n], $contenido);
                $cadena                 = preg_replace('/\&\&/', urlencode($filasSeleccionadas[$camposEscogido][$n]), $cadena);
                $elementoIndivudual[$n] = $cadena;
            }
        }

        for ($n = 0; $n < $max; $n++) {
            $cadenaFinal .= $elementoIndivudual[$n] . $this->separador;
        }

        return substr($cadenaFinal, 0, -$this->separadorLength);
    }

    public function add_InfoAcampo($contenido = '', $camposEscogido = '', $valoresDe = '')
    {

        $nuevoElemento     = $this->_explorador($contenido, $valoresDe);
        $nuevoElemento     = $this->_explodeC($nuevoElemento);
        $campoSeleccionado = $this->arrayCampos[$camposEscogido];
        $campoSeleccionado = $this->_explodeC($campoSeleccionado);

        for ($n = 0; $n < count($campoSeleccionado); $n++) {
            $nuevoElemento[$n] = preg_replace('/\$\$/', $campoSeleccionado[$n], $nuevoElemento[$n]);
        }

        $this->arrayCampos[$camposEscogido] = $this->_implodeC($nuevoElemento);
    }

    public function add_ColumnaAntesDe($contenido, $campoEscogido, $antesDe, $titulo)
    {

        $nuevoElemento = $this->_explorador($contenido, $campoEscogido);
        $nuevoCampo    = array();

        foreach ($this->arrayCampos as $key => $valor) {
            if ($antesDe == $key) {
                $nuevoCampo[$titulo] = $nuevoElemento;
                each($nuevoCampo);
            }

            $nuevoCampo[$key] = $valor;
        }

        $this->arrayCampos = $nuevoCampo;
    }

    public function add_ColumnaDespuesDe($contenido, $campoEscogido, $despuesDe, $titulo)
    {
        $nuevoElemento = $this->_explorador($contenido, $campoEscogido);
        $nuevoCampo    = array();
        foreach ($this->arrayCampos as $key => $valor) {
            $nuevoCampo[$key] = $valor;

            if ($despuesDe == $key) {
                $nuevoCampo[$titulo] = $nuevoElemento;
            }
        }
        $this->arrayCampos = $nuevoCampo;
    }

    public function add_FilaArriba($contenidoF = '', $alignF = 'center', $colspanF = null)
    {


        $this->contenidoF = ($contenidoF == '' ? $this->nombreGrid : $contenidoF);

        $this->alignF = $alignF;

        $this->colspanF = ($colspanF == null ? count($this->arrayCampos) : $colspanF);

        return $this;
    }
    public function add_FilaAbajo($contenidoF = '', $alignF = 'center', $colspanF = null)
    {


        $this->contenidoF2 = ($contenidoF == '' ? $this->nombreGrid : $contenidoF);

        $this->alignF2 = $alignF;

        $this->colspanF2 = ($colspanF == null ? count($this->arrayCampos) : $colspanF);

        return $this;
    }

    public function add_FuncionA($columna, $funcion)
    {

        if (is_callable(array($funcion[0], $funcion[1])) || function_exists($funcion)) {
            if (!is_array($columna)) {
                $valores = $this->_explodeC($this->arrayCampos[$columna]);
                $nuevos  = array_map($funcion, $valores);

                $this->arrayCampos[$columna] = $this->_implodeC($nuevos);
            } else {
                for ($n = 0; $n < count($columna); $n++) {
                    $valores                         = $this->_explodeC($this->arrayCampos[$columna[$n]]);
                    $nuevos[$n]                      = array_map($funcion, $valores);
                    $this->arrayCampos[$columna[$n]] = $this->_implodeC($nuevos[$n]);
                }
            }
        } else {
            $nombre = (is_array($funcion) ? $funcion[1] : $funcion);
            echo "La funcion : " . $nombre . " No se encuentra definida.";
        }
    }

    public function add_Relacion($relaciones = array())
    {

        $columnas = array_keys($relaciones);
        for ($n = 0; $n < count($columnas); $n++) {
            $valores = $this->_explodeC($this->arrayCampos[$columnas[$n]]);
            unset($valores_nuevos);

            for ($v = 0; $v < count($valores); $v++) {
                $compara = false;

                $claves = array_keys($relaciones[$columnas[$n]]);
                for ($k = 0; $k < count($claves); $k++) {
                    if ($claves[$k] == $valores[$v]) {
                        $valores_nuevos[] = $relaciones[$columnas[$n]][$claves[$k]];
                        $compara          = true;
                    }
                }
                if (!$compara) {
                    $valores_nuevos[] = $valores[$v];
                }
            }
            $this->arrayCampos[$columnas[$n]] = $this->_implodeC($valores_nuevos);
        }
    }

    public function add_SortAColumna($valores = array())
    {
        for ($n = 0; $n < count($valores); $n++) {
            $this->campos[$valores[$n]] = $valores[$n];
        }
    }

    public function rem_Columna($columna = array())
    {

        return $this->rem_columna = $columna;
    }
    private function _rem_ColumnaOculta()
    {


        $nuevoElemento = array();
        foreach ($this->arrayCampos as $key => $valor) {
            $si = false;

            for ($n = 0; $n < count($this->rem_columna); $n++) {
                if ($key == $this->rem_columna[$n]) {
                    $si = true;
                }
            }

            if (!$si) {
                $nuevoElemento[$key] = $valor;
            }
        }

        return $nuevoElemento;
    }

    public function rem_SortAcolumna($valores = array())
    {
        return $this->rem_SortAcolumna = $valores;
    }


    private function _thOrdenamiento($key)
    {


        $url = $_SERVER['REQUEST_URI'];

        $patron[0] = preg_match('/&colum=[\w]+/', $url) ? '/&colum=[\w]+/' : '/&colum=[\w]+/';
        $patron[1] = '/&orden=[\w]+/';
        $patron[2] = '/&sort=[\w]+/';


        $url = preg_replace($patron, '', $url);

        $url = strrchr($url, '?') ? $url . '&amp;' : $url . '?';

        $orden = (isset($_GET['orden']) ? $_GET['orden'] : 'asc');
        $orden = ($orden == 'asc' ? 'desc' : 'asc');


        if (in_array($key, array_keys($this->campos)) && !in_array($key, $this->rem_SortAcolumna)) {
            $url = '<a href="' . $url . 'colum=' . $this->campos[$key] .
            '&amp;orden=' . $orden . '&amp;sort=' . $this->nombreGrid . '">' . $key . '</a>';
        } else {
            $url = $key;
        }
        return $url;
    }


    private function _thSort()
    {
        if ($_GET['sort'] != $this->nombreGrid) {
            return false;
        }

        $columna = $_GET['colum'];
        $orden   = $_GET['orden'];

        if (isset($columna) && isset($orden) && $orden == 'asc' || $orden == 'desc') {
            $campos = $this->arrayCampos;

            $key = array_keys($this->campos, $columna);
            $key = $key[0];

            $escogido = $this->_explodeC($this->arrayCampos[$key]);
            $tipOrden = ($orden == 'asc' ? 'arsort' : 'asort');

            $tipOrden($escogido);

            $keys = array_keys($escogido);

            for ($n = 0; $n < count($keys); $n++) {
                $posicion = $keys[$n];

                foreach ($this->arrayCampos as $clave => $valor) {
                    if ($key == $clave) {
                        continue;
                    }

                    $arrayValores = $this->_explodeC($valor);

                    $posicionadoEn = $arrayValores[$posicion];

                    $valores[$clave] .= $posicionadoEn . $this->separador;
                }
            }

            foreach ($valores as $vari => $valo) {
                if ($vari == $key) {
                    continue;
                }
                $valores[$vari] = substr($valo, 0, strlen($valo) - $this->separadorLength);
            }
            foreach ($this->arrayCampos as $cl => $vl) {
                if ($cl != $key) {
                    $this->arrayCampos[$cl] = $valores[$cl];
                }
            }
            $this->arrayCampos[$key] = $this->_implodeC($escogido);
        }
    }

    public function _grid_filas($consulta)
    {
        $consulta = mysql_num_rows($consulta);
        return $consulta;
    }

    public function _grid_Pagina($args, $sql)
    {

        list($ubicacion, $nroMaximo, $despliegue, $mensaje) = $args;
        $pg                                                 = $_GET['pg'];
        if (!isset($pg) || empty($pg) || $pg < 0 || $this->nombreGrid != $_GET['pagina']) {
            $pg = 1;
        }
        $this->pgUbicacion  = $ubicacion;
        $this->pgDespliegue = $despliegue;


        if (empty($mensaje)) {
            $this->pgLang['pg']['Pagina']    = 'Pagina ';
            $this->pgLang['pg']['De']        = ' de ';
            $this->pgLang['pg']['Anterior']  = '  Anterior  ';
            $this->pgLang['pg']['Siguiente'] = '  Siguiente  ';
        } else {
            $this->pgLang = $mensaje;
        }


        $totalRegistros = mysql_num_rows(mysql_query($sql));

        $this->pgCantidad                = ceil($totalRegistros / $nroMaximo);
        $this->gridInfo['total_paginas'] = $this->pgCantidad;


        $pg = $pg > $this->pgCantidad ? 1 : $pg;

        $inicio = ($pg * $nroMaximo) - $nroMaximo;

        $limit = ' LIMIT ' . $inicio . ',' . $nroMaximo;

        return $limit;
    }
    private function _grid_PaginaNumeros()
    {

        $pg = $_GET['pg'];
        if (!isset($pg) || empty($pg) || $pg < 0 || $pg > $this->pgCantidad) {
            $pg = 1;
        }
        $inicio = $pg - $this->pgDespliegue;
        if ($inicio <= 1) {
            while ($inicio <= 1) {
                $inicio += 1;
            }
        }
        $fin = $pg + $this->pgDespliegue;

        if ($fin > $this->pgCantidad) {
            while ($fin > $this->pgCantidad) {
                $fin -= 1;
            }
        }

        $url = $_SERVER['REQUEST_URI'];

        $url = preg_replace('/&pagina=[\w]+/', '', $url);

        if (preg_match('/\?pg=[\w]+&/', $url)) {
            $url = preg_replace('/pg=[\w]+&/', '', $url);
        } elseif (preg_match('/\?pg=[\w]+/', $url)) {
            $url = preg_replace('/\?pg=[\w]+/', '', $url);
        } elseif (preg_match('/&pg=[\w]+/', $url)) {
            $url = preg_replace('/\&pg=[\w]+/', '', $url);
        }
        $url = strrchr($url, '?') ? $url . '&amp;pg=' : $url . '?pg=';

        $actual = $this->nombreGrid == $_GET['pagina'] ? $pg : 1;
        echo $this->pgLang['pg']['Pagina'] . $actual . $this->pgLang['pg']['De'] . $this->pgCantidad;

        if ($pg != 1) {
            echo "<a href=" . $url . ($pg - 1) . '&pagina=' . $this->nombreGrid . ">" . $this->pgLang['pg']['Anterior'] . "</a>";
        }
        if ($pg == 1 && $this->nombreGrid == $_GET['pagina']) {
            $resaltaInicio = '<strong>';
            $resaltaFinal  = '</strong>';
        }

        echo $resaltaInicio . "  <a href=" . $url . 1 . '&pagina=' . $this->nombreGrid . ">1</a> " . $resaltaFinal;

        for ($n = $inicio; $n <= $fin; $n++) {
            if ($pg == $n && $this->nombreGrid == $_GET['pagina']) {
                $resaltaInicio = '<strong>';
                $resaltaFinal  = '</strong>';
            } else {
                $resaltaInicio = '';
                $resaltaFinal  = '';
            }
            echo $resaltaInicio . "<a href=" . $url . $n . '&pagina=' . $this->nombreGrid . "> $n </a>" . $resaltaFinal;
        }
        if ($pg != $this->pgCantidad) {
            echo "<a href=" . $url . ($pg + 1) . '&pagina=' . $this->nombreGrid . ">" . $this->pgLang['pg']['Siguiente'] . "</a>";
        }
    }

    public function grid_PacingAndPadding($cellspacing, $cellpadding)
    {
        $this->cellspacing = $cellspacing;
        $this->cellpadding = $cellpadding;
        return $this;
    }

    public function grid_BgColorFC($colorTagTh, $colores)
    {
        $this->bgColor   = $colores;
        $this->bgColorTh = $colorTagTh;
        return $this;
    }

    public function grid_WidthAndHeight($width, $height)
    {
        $this->width  = $width;
        $this->height = $height;
        return $this;
    }

    public function grid_AtributosTabla($border, $borderColor, $bgColor, $background, $align)
    {
        $this->border       = $border;
        $this->borderColor  = $borderColor;
        $this->bgColorTabla = $bgColor;
        $this->background   = $background;
        $this->align        = $align;
        return $this;
    }

    public function gridMostrar()
    {
        $this->_thSort();
        $valoresCampos = (empty($this->rem_columna) ? $this->arrayCampos : $this->_rem_ColumnaOculta());

        $datos = array();
        echo '<div class="centrar">';
        echo '
                 <table align="' . $this->align . '"
                 	border="' . $this->border . '"
		 			bordercolor="' . $this->borderColor . '"
                 	background="' . $this->background . '"
		 			cellspacing="' . $this->cellspacing . '"
                 	cellpadding="' . $this->cellpadding . '"
		 			bgcolor="' . $this->bgColorTabla . '"
                 	class="' . $this->classCSS . '"
		 			height="' . $this->height . '"
					 width="' . $this->width . '">';

        ob_start();
        $this->_grid_PaginaNumeros();
        $salida = ob_get_clean();

        if ($this->pgUbicacion == 1 || $this->pgUbicacion == 2) {
            echo '<tr><td colspan="' . count($this->arrayCampos) . '" align="right">' . $salida . '</td></tr>';
        }
        if (!empty($this->contenidoF)) {
            echo '<tr><td colspan="' . $this->colspanF . '" align="' . $this->alignF . '">' . $this->contenidoF . '</td></tr>';
        }
        echo '<tr bgcolor="' . $this->bgColorTh . '">';

        foreach ($valoresCampos as $key => $valor) {
            echo '<th align="center">' .
            $this->_thOrdenamiento($key) . '</th>';

            $datos[$key] = $this->_explodeC($valoresCampos[$key]);
            $iteraciones = count($datos[$key]);
        }
        echo '</tr>';
        $v   = 0;
        $tam = count($this->bgColor);
        for ($n = 0; $n < $iteraciones; $n++) {
            $v = ($v == $tam ? 0 : $v);
            echo '<tr bgcolor="' . $this->bgColor[$v] . '">';
            foreach ($valoresCampos as $key => $valor) {
                echo '<td align="center">' . $datos[$key][$n] . '</td>';
            }
            echo '</tr>';
            $v++;
        }
        if (!empty($this->contenidoF2)) {
            echo '<tr><td colspan="' . $this->colspanF2 . '" align="' . $this->alignF2 . '">' . $this->contenidoF2 . '</td></tr>';
        }
        if ($this->pgUbicacion == 0 || $this->pgUbicacion == 2) {
            echo '<tr><td colspan="' . count($this->arrayCampos) . '" align="right">' . $salida . '</td></tr>';
        }
        echo '</table>';
        echo '</div>';
    }
}
