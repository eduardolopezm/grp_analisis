/*
  agrego: cendejas 01/08/2017
  proceso: Log de consultas
  Se agregan campo para almacenar el numero de funcion del registro
*/
ALTER TABLE audittrail add functionid int(11) NOT NULL

/*
  agrego: cendejas 01/08/2017
  proceso: Catalogos
  Se agrega tabla para catalogo Unidades Responsables
*/
CREATE TABLE `tb_cat_unidades_responsables` (
  `ur` varchar(10) DEFAULT NULL,
  `desc_ur` varchar(255) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  PRIMARY KEY (`ur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 01/08/2017
  proceso: Catalogos
  Se agrega tabla para catalogo Unidades Ejecutoras
*/
CREATE TABLE `tb_cat_unidades_ejecutoras` (
  `ue` varchar(10) DEFAULT NULL,
  `desc_ue` varchar(255) DEFAULT NULL,
  `active` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 07/08/2017
  Se agrega campo para preferente
*/
ALTER TABLE tags add preferential int(11) DEFAULT '0'

/*
  agrego: cendejas 07/08/2017
  Se agrega campo para activar o desactivar
*/
ALTER TABLE tags add tagactive tinyint(4) DEFAULT NULL

/*
  agrego: cendejas 07/08/2017
  Estructura para tabla presupuesto
*/
CREATE TABLE `chartdetailsbudgetbytag` (
  `budgetid` int(11) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(100) DEFAULT NULL,
  `budget` double DEFAULT NULL,
  `period` double DEFAULT NULL,
  `modified` double DEFAULT '0',
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `anho` varchar(4) DEFAULT NULL COMMENT 'Año',
  `cve_ramo` varchar(50) DEFAULT NULL COMMENT 'Clave Ramo',
  `tagref` varchar(5) NOT NULL COMMENT 'Unidad Responsable',
  `ue` varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora',
  `edo` varchar(5) NOT NULL COMMENT 'Estado',
  `id_finalidad` varchar(255) DEFAULT NULL COMMENT 'Finalidad',
  `id_funcion` int(11) DEFAULT NULL COMMENT 'Funcion',
  `id_subfuncion` int(11) DEFAULT NULL COMMENT 'Sub Funcion',
  `cprg` varchar(255) DEFAULT NULL COMMENT 'Reasignacion',
  `cain` varchar(50) DEFAULT NULL COMMENT 'Actividad Institucional',
  `cppt` varchar(50) DEFAULT NULL COMMENT 'Programa Presupuestario',
  `cp` varchar(3) DEFAULT NULL COMMENT 'Componente Presupuestario',
  `partida_esp` int(11) DEFAULT NULL COMMENT 'Partida',
  `ctga` int(11) DEFAULT NULL COMMENT 'Tipo de Gasto',
  `cfin` int(11) DEFAULT NULL COMMENT 'Fuente de Financiamiento',
  `cgeo` int(11) DEFAULT NULL COMMENT 'Geografico',
  `pyin` varchar(255) NOT NULL COMMENT 'PPI',
  `original` double NOT NULL DEFAULT '0' COMMENT 'Monto Presupuesto',
  `enero` double NOT NULL DEFAULT '0',
  `febrero` double NOT NULL DEFAULT '0',
  `marzo` double NOT NULL DEFAULT '0',
  `abril` double NOT NULL DEFAULT '0',
  `mayo` double NOT NULL DEFAULT '0',
  `junio` double NOT NULL DEFAULT '0',
  `julio` double NOT NULL DEFAULT '0',
  `agosto` double NOT NULL DEFAULT '0',
  `septiembre` double NOT NULL DEFAULT '0',
  `octubre` double NOT NULL DEFAULT '0',
  `noviembre` double NOT NULL DEFAULT '0',
  `diciembre` double NOT NULL DEFAULT '0',
  `folio` varchar(50) DEFAULT NULL,
  `numero_oficio` varchar(100) DEFAULT NULL,
  `estatus` varchar(50) DEFAULT NULL,
  `fecha_captura` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fecha_sistema` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `idClavePresupuesto` int(11) DEFAULT NULL,
  PRIMARY KEY (`budgetid`),
  KEY `index1` (`accountcode`,`tagref`,`period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 09/08/2017
  Se cambia el tipo y tamaño del campo ya que las URG llevan letras
*/
ALTER TABLE tags CHANGE legalid legalid varchar(5) DEFAULT NULL;

/*
  agrego: cendejas 11/08/2017
  Datos para los movimientos del presupuesto
*/
ALTER TABLE chartdetailsbudgetlog add type smallint(6) NOT NULL DEFAULT '0' COMMENT 'Tipo de Movimiento';
ALTER TABLE chartdetailsbudgetlog add transno int(11) NOT NULL DEFAULT '0' COMMENT 'Numero de Movimiento';
ALTER TABLE chartdetailsbudgetlog add numero_oficio varchar(100) DEFAULT NULL COMMENT 'Numero de Oficio';
ALTER TABLE chartdetailsbudgetlog add estatus varchar(50) DEFAULT NULL COMMENT 'Estatus del Movimiento';
ALTER TABLE chartdetailsbudgetlog add fecha_captura datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Fecha Captura del Movimiento';
ALTER TABLE chartdetailsbudgetlog add account varchar(45) DEFAULT NULL COMMENT 'Cuenta del Movimiento';
ALTER TABLE chartdetailsbudgetlog add tagref varchar(5) NOT NULL DEFAULT '0' COMMENT 'Unidad de Negocio (Dependecia)';
ALTER TABLE chartdetailsbudgetlog add sn_afectacion int(11) DEFAULT NULL COMMENT 'Tipo de Afectación';
ALTER TABLE chartdetailsbudgetlog add sn_adecuacion int(11) DEFAULT NULL COMMENT 'Tipo de Adecuación';
ALTER TABLE chartdetailsbudgetlog add partida_esp int(11) DEFAULT NULL COMMENT 'Partida Especifica de la Clave Presupuestal';
ALTER TABLE chartdetailsbudgetlog add sn_disponible int(11) DEFAULT NULL COMMENT 'Tomar en cuenta movimiento en el disponible';

/**
  Nueva tabla para el manejo de activos
  agrego: abarrientos
  fecha: 14/08/2017
**/
CREATE TABLE `fixedassetsservices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assetid` int(11) NOT NULL,
  `identifierpo` varchar(30) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `barcode` varchar(20) NOT NULL,
  `orderno` int(11) NOT NULL,
  `dateorder` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userorder` varchar(20) NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT 'Estatus del activo',
  `stockidpo` varchar(20) NOT NULL DEFAULT '' COMMENT 'Codigo de producto de la OC',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;            

/*
  agrego: cendejas 18/08/2017
  Validar disponible en le Presupuesto
*/
ALTER TABLE tb_botones_status add sn_flag_disponible int(11) DEFAULT NULL COMMENT 'Validar Disponible del Presupuesto';

/*
  agrego: cendejas 23/08/2017
  Tipos de afectacion por partida en la adecuacion presupuestal
*/
CREATE TABLE `tb_tipo_afectacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id del Registro',
  `nu_afectacion` int(11) NOT NULL COMMENT 'No. de Afectación',
  `txt_descripcion` varchar(255) NOT NULL COMMENT 'Descripción',
  `dt_efectiva` date DEFAULT NULL COMMENT 'Fecha Efectiva',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  `sn_ampliacion` smallint(6) DEFAULT NULL COMMENT 'Tipo para Ampliación',
  `sn_reduccion` smallint(6) DEFAULT NULL COMMENT 'Tipo para Reducción',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 23/08/2017
  Tipos de adecuacion presupuestal
*/
CREATE TABLE `tb_tipo_adecuacion` (
  `nu_adecuacion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'No. de Adecuación',
  `txt_descripcion` varchar(255) NOT NULL COMMENT 'Descripción',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`nu_adecuacion`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 23/08/2017
  Validaciones para la adecuacion presupuestal
*/
CREATE TABLE `tb_tipo_adecuacion_validaciones` (
  `nu_adecuacion` int(11) DEFAULT NULL COMMENT 'No. de Adecuación',
  `txt_tipo` varchar(20) DEFAULT NULL COMMENT 'Reducción y/o Ampliación',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  `txt_sql` text COMMENT 'SQL para Información'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 23/08/2017
  Campo para botones del requisicion
*/
ALTER TABLE tb_botones_status add sn_captura_requisicion int(11) DEFAULT NULL COMMENT 'Mostrar información en la Captura Requisición';

/**
  Nueva tabla para el manejo de estatus de requisiciones
  agrego: abarrientos
  fecha: 24/08/2017
**/
CREATE TABLE `purchorderstatus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(20) NOT NULL DEFAULT '' COMMENT 'Estatus que se almacena en la orden de compra',
  `buttonname` varchar(50) NOT NULL DEFAULT '' COMMENT 'Nombre del boton para envio al servidor',
  `showname` varchar(50) NOT NULL DEFAULT '' COMMENT 'Nombre del estatus para mostrar',
  `description` varchar(100) DEFAULT NULL COMMENT 'Descripcion del estatus',
  `_order` tinyint(4) NOT NULL COMMENT 'Orden para mostrar los estatus',
  `authprocess` tinyint(2) DEFAULT NULL COMMENT 'Orden con el cual se debe seguir el proceso para autorizar una orden de compra',
  `usernumber` tinyint(1) DEFAULT NULL COMMENT 'Numero de usarios requeridos para marcar el estatus como completo',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = Estatus Activo | 0 = Estatus Inactivo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

/*
  agrego: cendejas 29/08/2017
  Campo para identificar si pertenece a la clave corta y el orden de la clave
*/
ALTER TABLE budgetConfigClave add sn_clave_corta int(11) DEFAULT NULL COMMENT 'Si pertenece a la Clave Corta';
ALTER TABLE budgetConfigClave add nu_clave_corta_orden int(11) DEFAULT NULL COMMENT 'Orden Clave Corta';
ALTER TABLE budgetConfigClave add sn_clave_larga int(11) DEFAULT NULL COMMENT 'Si pertenece a la Clave Larga';
ALTER TABLE budgetConfigClave add nu_clave_larga_orden int(11) DEFAULT NULL COMMENT 'Orden Clave Larga';

/*
  agrego: cendejas 01/09/2017
  Campo para identificar si la clave es del presupuesto inicial o es creada
*/
ALTER TABLE chartdetailsbudgetbytag add sn_inicial int(11) DEFAULT NULL COMMENT '1 Presupuesto Inicial - 2 Clave Creada';

/*
  agrego: cendejas 01/09/2017
  Campo para activar o desactivar configuracion
*/
ALTER TABLE budgetConfigClave add sn_activo int(11) DEFAULT NULL COMMENT 'Activo o Inactivo';
UPDATE budgetConfigClave SET sn_activo = 1;
ALTER TABLE budgetConfigClave add txt_sql_nueva text DEFAULT NULL COMMENT 'Consulta para la información al agregar una nueva Clave. Contener solo value y texto para mostrar';

/*
  agrego: cendejas 04/09/2017
  Campo para agregar el usuario que agrego la clave presupuestal
*/
ALTER TABLE chartdetailsbudgetbytag add txt_userid varchar(255) DEFAULT NULL COMMENT 'Usuario que agrego la Clave Presupuestal';

/*
  agrego: cendejas 06/09/2017
  Campo para agregar al filtro de la clave presupuestal
*/
ALTER TABLE budgetConfigClave add sn_filtro_adecuacion int(11) DEFAULT NULL COMMENT 'Mostrar como filtro al realizar una Adecuación presupuestal';

/*
  agrego: cendejas 06/09/2017
  Campo para agregar el año de la confuguracion
*/
ALTER TABLE budgetConfigClave add nu_anio int(11) DEFAULT NULL COMMENT 'Año de la Configuración';


/*
  agrego: cendejas 06/09/2017
  Campo de identificador de la adecuacion (Clase)
*/
ALTER TABLE tb_tipo_adecuacion add sn_clave varchar(2) DEFAULT NULL COMMENT 'Identificador de la Adecuación';
ALTER TABLE tb_tipo_adecuacion add nu_anio varchar(2) DEFAULT NULL COMMENT 'Año del Tipo de Adecuación';
ALTER TABLE tb_tipo_adecuacion_validaciones add sn_clave varchar(2) DEFAULT NULL COMMENT 'Identificador de la Adecuación';

/*
  agrego: cendejas 06/09/2017
  Tabla para el tipo de solicitud del tipo de adecuacion
*/
CREATE TABLE `tb_tipo_solicitud` (
  `nu_tipo_solicitud` int(11) NOT NULL AUTO_INCREMENT,
  `txt_descripcion` varchar(255) NOT NULL COMMENT 'Descripción',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  `sn_clave` varchar(2) DEFAULT NULL COMMENT 'Identificador del Tipo de Solicitud',
  `nu_adecuacion` varchar(50) DEFAULT NULL COMMENT 'Identificador del Tipo de Adeucación',
  PRIMARY KEY (`nu_tipo_solicitud`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 07/09/2017
  Campos para:
  dtm_aplicacion - Fecha de Apliacion
  nu_centro_contable - Centro Contable
  nu_tipo_reg - Tipo de Registro
  nu_cat_jusr - Datos del catalogo CAT_JUSR
  txt_dictamen_upi - Dictamen UPI
  txt_control_interno - Control Interno
  txt_justificacion - Justificación de la Adecuación
  nu_tipo_solicitud - Número del Tipo de Solicitud
*/
ALTER TABLE chartdetailsbudgetlog add dtm_aplicacion datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Fecha de Aplicación';
ALTER TABLE chartdetailsbudgetlog add nu_centro_contable varchar(255) DEFAULT NULL COMMENT 'Centro Contable';
ALTER TABLE chartdetailsbudgetlog add nu_tipo_reg varchar(255) DEFAULT NULL COMMENT 'Tipo de Registro';
ALTER TABLE chartdetailsbudgetlog add nu_cat_jusr varchar(255) DEFAULT NULL COMMENT 'Datos del catalogo CAT_JUSR';
ALTER TABLE chartdetailsbudgetlog add txt_dictamen_upi text DEFAULT NULL COMMENT 'Dictamen UPI';
ALTER TABLE chartdetailsbudgetlog add txt_control_interno varchar(255) DEFAULT NULL COMMENT 'Control Interno';
ALTER TABLE chartdetailsbudgetlog add txt_justificacion text DEFAULT NULL COMMENT 'Justificación de la Adecuación';
ALTER TABLE chartdetailsbudgetlog add nu_tipo_solicitud int(11) DEFAULT NULL COMMENT 'Número del Tipo de Solicitud';

/*
  agrego: cendejas 14/09/2017
  Campos para agregar los datos auxiliares (1,2,3) de la clave presupuestal
*/
ALTER TABLE chartdetailsbudgetbytag add ln_aux1 varchar(10) DEFAULT NULL COMMENT 'Campo para el auxiliar 1 de la clave presupuestal';
ALTER TABLE chartdetailsbudgetbytag add ln_aux2 varchar(10) DEFAULT NULL COMMENT 'Campo para el auxiliar 2 de la clave presupuestal';
ALTER TABLE chartdetailsbudgetbytag add ln_aux3 varchar(10) DEFAULT NULL COMMENT 'Campo para el auxiliar 3 de la clave presupuestal';

/*
  agrego: cendejas 16/09/2017
  Campo para el tipo de gasto de la categoria
*/
ALTER TABLE stockcategory add nu_tipo_gasto int(11) DEFAULT NULL COMMENT 'Tipo de Gasto';

/*
  agrego: cendejas 19/09/2017
  Cambiar el tipo de campo de entero a varchar
*/
ALTER TABLE gltrans MODIFY tag varchar(5);

/*
  agrego: cendejas 22/09/2017
  Campo para almacenar el tipo de movimiento del presupuesto
*/
ALTER TABLE chartdetailsbudgetlog add nu_tipo_movimiento int(11) DEFAULT NULL COMMENT 'Número del Tipo de Movimiento';

/*
  agrego: cendejas 26/09/2017
  Campo para almacenar el tipo de unidad responsable (Central o Estatal)
*/
ALTER TABLE tags add ln_tipo varchar(50) DEFAULT NULL COMMENT 'Tipo de Unidad de Responsable';

/*
  agrego: cendejas 27/09/2017
  Extender el numero de caracteres para el plan de cuentas, se requiere
*/
ALTER TABLE chartTipos MODIFY nombreMayor varchar(50);
ALTER TABLE chartTipos MODIFY tipo varchar(2);
ALTER TABLE chartmaster MODIFY tipo varchar(2);

/*
  agrego: cendejas 27/09/2017
  Tablas para catalogos al formar una cuenta contable
  Genero: tb_gl_genero
  Grupo: tb_gl_grupo
  Rubro: tb_gl_rubro
  Cuenta: tb_gl_cuenta
*/
CREATE TABLE `tb_gl_genero` (
  `nu_clave` varchar(1) DEFAULT NULL COMMENT 'Identificador del Genero',
  `txt_descripcion` varchar(255) NOT NULL COMMENT 'Descripción',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`nu_clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tb_gl_grupo` (
  `nu_clave` varchar(3) DEFAULT NULL COMMENT 'Identificador del Grupo',
  `txt_descripcion` varchar(255) NOT NULL COMMENT 'Descripción',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`nu_clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tb_gl_rubro` (
  `nu_clave` varchar(5) DEFAULT NULL COMMENT 'Identificador del Rubro',
  `txt_descripcion` varchar(255) NOT NULL COMMENT 'Descripción',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`nu_clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tb_gl_cuenta` (
  `nu_clave` varchar(7) DEFAULT NULL COMMENT 'Identificador del Cuenta',
  `txt_descripcion` varchar(255) NOT NULL COMMENT 'Descripción',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`nu_clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 28/09/2017
  Campo para el auxiliar 1 combinacion
*/
ALTER TABLE tb_cat_unidades_ejecutoras add ln_aux1 varchar(10) DEFAULT NULL COMMENT 'Campo para el auxiliar 1 de la clave presupuestal';

/*
  agrego: cendejas 05/10/2017
  Número interior para la unidad responsable
*/
ALTER TABLE tags add nu_interior int(11) DEFAULT NULL COMMENT 'Número Interior';

/*
  agrego: abarrientos 05/10/2017
  Modificacion del campo para que acepte caracteres
*/
ALTER TABLE locations MODIFY tagref VARCHAR(5);

/*
  agrego: cendejas 09/10/2017
  Datos por default para evitar errores en el servidor de sagarpa
*/
ALTER TABLE gltrans MODIFY loccode varchar(50) DEFAULT NULL COMMENT 'Código de Almacén', MODIFY `trandate` date NOT NULL DEFAULT '1900-01-01';
ALTER TABLE gltrans MODIFY stockid varchar(255) DEFAULT NULL;
ALTER TABLE purchorders 
MODIFY tagref varchar(5) DEFAULT NULL, 
MODIFY orddate datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
MODIFY revised date NOT NULL DEFAULT '1900-01-01',
MODIFY deliverydate date NOT NULL DEFAULT '1900-01-01'
;

ALTER TABLE supptrans 
MODIFY trandate date NOT NULL DEFAULT '1900-01-01',
MODIFY origtrandate date NOT NULL DEFAULT '1900-01-01',
MODIFY duedate date NOT NULL DEFAULT '1900-01-01',
MODIFY promisedate date NOT NULL DEFAULT '1900-01-01'
;
ALTER TABLE supptrans 
MODIFY folio varchar(50) DEFAULT NULL,
MODIFY ref1 varchar(50) DEFAULT NULL,
MODIFY ref2 varchar(50) DEFAULT NULL
;
ALTER TABLE supptrans MODIFY sent int(11) DEFAULT NULL;

/*
  agrego: arturo lopez peña 09/10/2017
  Datos por default para evitar errores en el servidor de sagarpa
*/
alter table stockcostsxlegalnew 
modify lastpurchase date NOT NULL DEFAULT '1900-01-01',
modify lastupdatedate datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
modify lastpurchaseqty double NOT  NULL DEFAULT '0.00',
modify trandate datetime NOT NULL DEFAULT '1900-01-01 00:00:00';

/*
  agrego: arturo lopez peña 10/10/2017
  Datos por default para evitar errores en el servidor de sagarpa
*/
                                           
alter table banktrans
 modify  bankact varchar(50) NOT NULL DEFAULT '0', 
 modify  transdate date NOT NULL DEFAULT '1900-01-01';

/*
  agrego: cendejas 12/10/2017
  Relacion del tipo de adecuacion con tipo de solicitud
*/
CREATE TABLE `tb_adecuacion_solicitud` (
  `nu_adecuacion` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id Tipo de Adecuación',
  `nu_tipo_solicitud` int(11) unsigned NOT NULL COMMENT 'Id Tipo de Solicitud',
  PRIMARY KEY (`nu_adecuacion`,`nu_tipo_solicitud`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*
  agrego: cendejas 12/10/2017
  Catálogo tipo de reg para adecuaciones presupuestales
*/
CREATE TABLE `tb_treg` (
  `nu_tipo_reg` varchar(5) NOT NULL DEFAULT '0',
  `txt_descripcion` varchar(255) DEFAULT NULL COMMENT 'Descripción',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`nu_tipo_reg`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 12/10/2017
  Catálogo jusr para adecuaciones presupuestales
*/
CREATE TABLE `tb_jusr` (
  `nu_cat_jusr` varchar(5) NOT NULL DEFAULT '0',
  `txt_descripcion` varchar(255) DEFAULT NULL COMMENT 'Descripción',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`nu_cat_jusr`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 12/10/2017
  Catálogo CONC R23 para adecuaciones presupuestales
*/
CREATE TABLE `tb_conc_r23` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nu_r23` varchar(5) DEFAULT NULL COMMENT 'Clave',
  `txt_descripcion` varchar(255) DEFAULT NULL COMMENT 'Descripción',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  `nu_ciclo` varchar(4) DEFAULT NULL COMMENT 'Ciclo',
  `txt_descripcion_ciclo` varchar(255) DEFAULT NULL COMMENT 'Descripción Ciclo',
  PRIMARY KEY (`id`),
  KEY (`nu_r23`,`nu_ciclo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 12/10/2017
  Catálogo CONC R23 , proceso en sicop y folio map para adecuaciones presupuestale
*/
ALTER TABLE chartdetailsbudgetlog add nu_r23 varchar(5) DEFAULT NULL COMMENT 'Número Catálogo R23';
ALTER TABLE chartdetailsbudgetlog add txt_proceso_sicop varchar(255) DEFAULT NULL COMMENT 'Proceso Sicop';
ALTER TABLE chartdetailsbudgetlog add txt_folio_map varchar(255) DEFAULT NULL COMMENT 'Folio Map';

/*
  agrego: cendejas 12/10/2017
  Fecha por default para evitar error en sagarpa
*/
ALTER TABLE chartdetailsbudgetlog 
MODIFY fecha_captura datetime NOT NULL DEFAULT '1900-01-01',
MODIFY dtm_aplicacion datetime NOT NULL DEFAULT '1900-01-01'
;

/*agrego: arturo lopez peña 13/10/2017
*/
alter table www_users 
modify   `salesman` char(3)  default NULL,
modify   `defaultunidadNegocio` tinyint(4) DEFAULT  0

/*
  agrego: cendejas 15/10/2017
  Campo para el tipo de solicitud
*/
ALTER TABLE tb_tipo_adecuacion_validaciones add nu_tipo_solicitud int(11) DEFAULT NULL COMMENT 'Número Tipo de Solicitud';
ALTER TABLE tb_tipo_adecuacion_validaciones add nu_clase int(11) DEFAULT NULL COMMENT 'Número Clase';


/*
  agrego: Jorge Cesar Garcia 12/10/2017
  Catálogo clasificaciones programáticas
*/
CREATE TABLE `tb_cat_clasificacion_programatica` (
  `clasificacionid` int(11) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `padreid` int(11) DEFAULT NULL,
  `letra` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `tb_cat_clasificacion_programatica` (`clasificacionid`, `descripcion`, `padreid`, `letra`)
VALUES
  (1, 'Programas', NULL, ''),
  (2, 'Subsidios: Sector Social y Privado o Entidades Federativas y Municipios', 1, ''),
  (3, 'Sujetos a Reglas de Operación', 2, 'S'),
  (4, 'Otros Subsidios', 2, 'U'),
  (5, 'Desempeño de las Funciones', 1, ''),
  (6, 'Prestación de Servicios Públicos', 5, 'E'),
  (7, 'Provisión de Bienes Públicos', 5, 'B'),
  (8, 'Planeación, seguimiento y evaluación de políticas públicas', 5, 'P'),
  (9, 'Promoción y fomento ', 5, 'F'),
  (10, 'Regulación y supervisión ', 5, 'G'),
  (11, 'Funciones de las Fuerzas Armadas (Únicamente Gobierno Federal) ', 5, 'A'),
  (12, 'Específicos ', 5, 'R'),
  (13, 'Proyectos de Inversión ', 5, 'K'),
  (14, 'Administrativos y de Apoyo', 1, ''),
  (15, 'Apoyo al proceso presupuestario y para mejorar la eficiencia institucional', 14, 'M'),
  (16, 'Apoyo a la función pública y al mejoramiento de la gestión', 14, 'O'),
  (17, 'Operaciones ajenas', 14, 'W'),
  (18, 'Compromisos', 1, ''),
  (19, 'Obligaciones de cumplimiento de resolución jurisdiccional', 18, 'L'),
  (20, 'Desastres Naturales', 18, 'N'),
  (21, 'Obligaciones', 1, ''),
  (22, 'Pensiones y jubilaciones', 21, 'J'),
  (23, 'Aportaciones a la seguridad social', 21, 'T'),
  (24, 'Aportaciones a fondos de estabilización', 21, 'Y'),
  (25, 'Aportaciones a fondos de inversión y reestructura de pensiones', 21, 'Z'),
  (26, 'Programas de Gasto Federalizado (Gobierno Federal)', 1, ''),
  (27, 'Gasto Federalizado', 26, 'I'),
  (28, 'Participaciones a entidades federativas y municipios', NULL, 'C'),
  (29, 'Costo financiero, deuda o apoyos a deudores y ahorradores de la banca', NULL, 'D'),
  (30, 'Adeudos de ejercicios fiscales anteriores', NULL, 'H');

/*
  agrego: cendejas 24/10/2017
  Cambiar comentarios de los campos
*/
ALTER TABLE banktrans MODIFY exrate double NOT NULL DEFAULT '1' COMMENT 'Moneda de la Cuenta Bancaria';
ALTER TABLE banktrans MODIFY functionalexrate double NOT NULL DEFAULT '1' COMMENT 'Moneda de Cuenta Funcional';


/*
  agrego: cendejas 26/10/2017
  nu_generar_layout: Configurar si el estatus va a generar un layout en panel de presupuestos
  nu_tipo_layout: Tipo de layout a generar
*/
ALTER TABLE tb_botones_status add nu_generar_layout int(11) DEFAULT NULL COMMENT '1 - Genera Layout';
ALTER TABLE tb_botones_status add nu_tipo_layout int(11) DEFAULT NULL COMMENT '1 - Layout Individual, 2 - Layout General';

/*
  agrego: cendejas 01/11/2017
  Tabla para almacenar informacion de las suficiencias manuales y automaticas
*/
CREATE TABLE `tb_suficiencias` (
  `nu_mov` int(11) NOT NULL AUTO_INCREMENT,
  `dtm_fecha` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `sn_userid` varchar(100) DEFAULT NULL,
  `sn_description` varchar(255) DEFAULT NULL,
  `nu_type` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Tipo de Movimiento',
  `nu_transno` int(11) NOT NULL DEFAULT '0' COMMENT 'Numero de Movimiento',
  `nu_estatus` varchar(255) DEFAULT NULL COMMENT 'Estatus del Movimiento',
  `sn_tagref` varchar(5) NOT NULL DEFAULT '0' COMMENT 'Unidad Responsable (Dependecia)',
  `nu_tipo` int(11) NOT NULL DEFAULT '0' COMMENT 'Tipo (1 - Automática, 2 - Manual)',
  KEY `nu_mov` (`nu_mov`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*
  agrego: Luis Aguilar Sandoval 
  fecha: 01/10/17
  Campo para guardar la unidad ejecutora
*/
alter table purchorders add `nu_ue` varchar(255) NULL COMMENT 'unidad ejecutora';

/*
  agrego: cendejas 01/11/2017
  Campo para que el id de la depedencia sea automatico
*/
ALTER TABLE legalbusinessunit MODIFY legalid int(11) NOT NULL AUTO_INCREMENT;

/*
  agrego: cendejas 02/11/2017
  Número de Función del Estatus
*/
ALTER TABLE chartdetailsbudgetlog add sn_funcion_id int(11) DEFAULT NULL COMMENT 'Número de Función Estatus';
ALTER TABLE tb_suficiencias add sn_funcion_id int(11) DEFAULT NULL COMMENT 'Número de Función Estatus';

/*
  agrego: cendejas 02/11/2017
  Datos por default evitar error en el sistema
*/
ALTER TABLE chartdetailsbudgetbytag 
MODIFY edo varchar(5) DEFAULT NULL COMMENT 'Estado',
MODIFY fecha_captura datetime DEFAULT NULL,
MODIFY fecha_sistema datetime DEFAULT NULL
;

/*
  agrego: cendejas 07/11/2017
  Datos por default evitar error en el sistema
*/
ALTER TABLE banktrans 
MODIFY bankact varchar(50) NOT NULL DEFAULT '0',
MODIFY transdate date NOT NULL DEFAULT '1900-01-01'
;

/*
  agrego: armando.barrientos 07/11/2017
  Se amplio el campo de initiator para que pueda aceptar el dato de usuario mas largo
*/
ALTER TABLE purchorders MODIFY COLUMN initiator VARCHAR(60);

/*
  agrego: cendejas 08/11/2017
  Campo para unidad responsable receptora para Adeuaciones
*/
ALTER TABLE chartdetailsbudgetlog add sn_tagref_receptora varchar(5) DEFAULT NULL COMMENT 'Código UR de la Unidad Receptora (Adecuaciones)';

/*
  agrego: Luis Aguilar 08/11/2017
  Campo para unidad responsable receptora para Adeuaciones
*/
ALTER TABLE periodsXlegal_log add `periodno` smallint(6) NOT NULL DEFAULT '0', add `legalid` int(11) NULL ;

/*
  agrego: cendejas 10/11/2017
  Campos para configuracion de docuementos del estado del presupuesto
  y cuales se van a tomar en cuenta
*/
ALTER TABLE systypescat add nu_estado_presupuesto int(11) DEFAULT NULL COMMENT '1 - Si pertenece a un Estado del Presupuesto';
ALTER TABLE systypescat add nu_usar_disponible int(11) DEFAULT NULL COMMENT '1 - Si pertenece a un Estado del Presupuesto y se va a tomar en cuenta para el disponible';

/*
  agrego: cendejas 14/11/2017
  Agregar 3er nombre
*/
ALTER TABLE tb_botones_status add sn_mensaje_opcional varchar(50) DEFAULT NULL COMMENT 'Mensaje opcional para el estatus';
ALTER TABLE tb_botones_status add sn_mensaje_opcional2 varchar(50) DEFAULT NULL COMMENT 'Mensaje opcional 2 para el estatus';

/*
  agrego: cendejas 27/11/2017
  Campo para activar o desactivar tipo de documento
*/
ALTER TABLE systypescat add nu_activo int(11) DEFAULT NULL COMMENT 'Campo para activas o desactivar registro. 1 - Activo, 0 - Inactivo';

/*
  agrego: cendejas 20/12/2017
  Se agrega campo para orden de compra
*/
ALTER TABLE tb_suficiencias add sn_orderno int(11) DEFAULT NULL COMMENT 'Orden de Compra';
ALTER TABLE tb_suficiencias add sn_cancel int(11) DEFAULT NULL COMMENT 'Si se cancelo la Suficiencia';

/*
  agrego: cendejas 08/01/2018
  Se cambia campo a cadena para UR
*/
ALTER TABLE suppcontrarecibo MODIFY tagref VARCHAR(5);

/*
  agrego: cendejas 09/01/2018
  Se cambia tipo de int(11) a varchar(5)
*/
ALTER TABLE supptrans MODIFY tagref varchar(5) DEFAULT NULL COMMENT 'Unidadresponsable';

/*
  agrego: cendejas 11/01/2018
  Se agrega tamaño a la columna para mensaje opcional
*/
ALTER TABLE tb_botones_status MODIFY sn_mensaje_opcional varchar(100);

/*
  agrego: cendejas 12/01/2018
  Información de origen para matriz extrapresupuestal
*/
CREATE TABLE `tb_matriz_extraptal_origen` (
  `nu_reg` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id del Registro',
  `txt_descripcion` varchar(255) NOT NULL COMMENT 'Descripción',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`nu_reg`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 12/01/2018
  Información de proceso para matriz extrapresupuestal
*/
CREATE TABLE `tb_matriz_extraptal_proceso` (
  `nu_reg` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id del Registro',
  `txt_descripcion` varchar(255) NOT NULL COMMENT 'Descripción',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`nu_reg`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*
  agrego: cendejas 15/01/2018
  Campo para agregar abreviación para tipo de documento para estado del ejercicio
*/
ALTER TABLE systypescat add ln_descripcion_corta varchar(100) DEFAULT NULL COMMENT 'Abreviación del Tipo de Documento';

/*
  agrego: cendejas 18/01/2018
  Se cambia campo a cadena para UR
*/
ALTER TABLE bankaccounts MODIFY tagref VARCHAR(5);

/*
  agrego: cendejas 23/01/2018
  Agregar campo para Unidad Ejecutora al log del presupuesto
*/
ALTER TABLE chartdetailsbudgetlog add ln_ue varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora';

/*
  agrego: cendejas 23/01/2018
  Agregar campo para Unidad Ejecutora a las suficiencias
*/
ALTER TABLE tb_suficiencias add ln_ue varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora';

/*
  agrego: cendejas 24/01/2018
  Agregar campo para Unidad Ejecutora a las recepciones de productos,
  Se cambio el valor default del campo fecha
*/
ALTER TABLE grns add ln_ue varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora';
ALTER TABLE grns MODIFY deliverydate date NOT NULL DEFAULT '1900-01-01';

/*
  agrego: cendejas 25/01/2018
  Agregar campo para Unidad Ejecutora a las facturas y pagos
*/
ALTER TABLE supptrans add ln_ue varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora';

/*
  agrego: cendejas 30/01/2018
  Agregar campo para Unidad Ejecutora movimientos de inventario
*/
ALTER TABLE stockmoves add ln_ue varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora';
ALTER TABLE stockmoves MODIFY trandate date NOT NULL DEFAULT '1900-01-01';

/*
  agrego: cendejas 31/01/2018
  Agregar campo para Unidad Ejecutora movimientos de bancos
*/
ALTER TABLE banktrans add ln_ue varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora';
/*
  agrego: lopez peña 31/01/2018
  cambio de del tipo del tagref
*/
ALTER TABLE banktrans MODIFY tagref varchar(5) DEFAULT NULL COMMENT 'tagref de 5 posiciones';

/*
  agrego: cendejas 31/01/2018
  Campo para configuración de documentos
  nu_inventario_inicial: Inventario Inicial
  nu_inventario_entrada: Entrada de Inventario
  nu_inventario_salida: Salida de Inventario
*/
ALTER TABLE systypescat add nu_inventario_inicial int(11) DEFAULT NULL COMMENT 'Inventario Inicial';
ALTER TABLE systypescat add nu_inventario_entrada int(11) DEFAULT NULL COMMENT 'Entrada de Inventario';
ALTER TABLE systypescat add nu_inventario_salida int(11) DEFAULT NULL COMMENT 'Salida de Inventario';

/*
  agrego: cendejas 07/02/2018
  Campo para configuración del tamaño al visualizar en el Estado del Ejercicio
*/
ALTER TABLE budgetConfigClave add nu_tam_est_ejer int(11) DEFAULT NULL COMMENT 'Tamaño en % del Estado del Ejercicio';


/*
  agrego: Armando Barrientos 08/02/2018
  Campo para configuración del tamaño al visualizar en el Estado del Ejercicio
*/
ALTER TABLE stockmoves ADD COLUMN localidad VARCHAR(50) DEFAULT NULL COMMENT 'Campo para guardar localidad dentro del almacen'

/*
  agrego: Armando Barrientos 08/02/2018
  Campo para configuración del tamaño al visualizar en el Estado del Ejercicio
*/
ALTER TABLE stockmoves ADD COLUMN register DATETIME COMMENT 'Campo que registra la fecha y hora del registro';

/*
  agrego: cendejas 13/02/2018
  Campo para la clave de la cuenta para relacion con la dependencia
*/
ALTER TABLE chartmaster ADD COLUMN ln_clave VARCHAR(50) DEFAULT NULL COMMENT 'Campo para Identificar Cuenta con Dependencia';

/*
  agrego: cendejas 14/02/2018
  Campo para la clave de la cuenta para relacion con la dependencia
*/
ALTER TABLE stockcategory ADD COLUMN ln_clave VARCHAR(50) DEFAULT NULL COMMENT 'Campo para Identificador';

/*
  agrego: cendejas 14/02/2018
  cambio de del tipo del tagref
*/
ALTER TABLE config_reportes_ MODIFY tagref varchar(5) DEFAULT NULL COMMENT 'tagref de 5 posiciones';

/*
  agrego: cendejas 14/02/2018
  Campo para la clave del identificador matriz
*/
ALTER TABLE purchorderdetails ADD COLUMN ln_clave_iden VARCHAR(50) DEFAULT NULL COMMENT 'Campo para Identificador';
ALTER TABLE purchorderdetails MODIFY deliverydate date NOT NULL DEFAULT '1900-01-01';

/*
  agrego: cendejas 15/02/2018
  Campos para configuracion de documentos del estado del presupuesto (Radicado) para el disponible
*/
ALTER TABLE systypescat add nu_usar_disponible_radicado int(11) DEFAULT NULL COMMENT '1 - Si se va a tomar en cuenta para el disponible del Radicado';

/*
  agrego: ARTURO LOPEZ PEÑA 19/02/2018
  Se cambio el campo tagref de entero a varchar(5)
*/
ALTER TABLE estadoscuentabancarios MODIFY tagref VARCHAR (5) DEFAULT NULL COMMENT 'tagref equivale a la UR en el GRP';

/*
  agrego: cendejas 19/02/2018
  Campos para configuracion de documentos Gestión de Pólizas
*/
ALTER TABLE systypescat add nu_gestion_polizas int(11) DEFAULT NULL COMMENT '1 - Documento Gestión de Pólizas';

/*
  agrego: cendejas 21/02/2018
  Se cambia campo a cadena para UR
*/
ALTER TABLE chartdetails MODIFY tagref VARCHAR(5);
ALTER TABLE RePostGL MODIFY tagref VARCHAR(5);

/*
  agrego: cendejas 23/02/2018
  Campo para la clave de la cuenta para relacion con la dependencia
*/
ALTER TABLE tb_matriz_pagado ADD COLUMN ln_clave VARCHAR(50) DEFAULT NULL COMMENT 'Campo para Identificador';

/*
  agrego: cendejas 27/02/2018
  Campo para la clave de la cuenta para relacion con la dependencia
*/
ALTER TABLE tb_matriz_extraptal ADD COLUMN ln_clave VARCHAR(50) DEFAULT NULL COMMENT 'Campo para Identificador';

/*
  agrego: cendejas 05/03/2018
  Campo para activar o desactivar Tipo de Producto
*/
ALTER TABLE stocktypeflag add sn_activo int(11) DEFAULT NULL COMMENT 'Activo o Inactivo';

/*
  agrego: cendejas 05/03/2018
  Valores por default para tabla
*/
alter table stockcostsxlegal 
modify lastpurchase date NOT NULL DEFAULT '1900-01-01',
modify lastupdatedate datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
modify trandate datetime NOT NULL DEFAULT '1900-01-01 00:00:00';

alter table stockcostsxtag 
modify lastpurchase date NOT NULL DEFAULT '1900-01-01',
modify lastpurchaseqty double NOT NULL DEFAULT '0',
modify lastupdatedate datetime NOT NULL DEFAULT '1900-01-01 00:00:00';

ALTER TABLE stockcostsxtag MODIFY tagref VARCHAR(5);

/*
  agrego: cendejas 12/03/2018
  Campo para partida Anterior
*/
ALTER TABLE tb_partida_articulo add sn_partida_ant varchar(10) DEFAULT NULL;
ALTER TABLE tb_partida_articulo add sn_codigo varchar(10) DEFAULT NULL;

/*
  agrego: arturo lopez peña 15/03/2018
  Se cambio el tipo de dato para el campo  tagref
*/
ALTER TABLE   gltrans_files  modify tagref varchar(5);

/*
  agrego: cendejas 25/03/2018
  Campo para almacenar el orden original de la requisicón y mostrra información con ese orden
*/
ALTER TABLE purchorderdetails add nu_original int(11) DEFAULT NULL COMMENT 'Orden Original al Autorizar Requisición';


/*
  agrego: cendejas 28/03/2018
  Campos para configuración de estructuras para adiciones de claves
*/
ALTER TABLE budgetConfigClave add nu_programatica int(11) DEFAULT NULL COMMENT 'Estructura Programatica';
ALTER TABLE budgetConfigClave add nu_programatica_orden int(11) DEFAULT NULL COMMENT 'Orden de la Estructura Programatica';

ALTER TABLE budgetConfigClave add nu_economica int(11) DEFAULT NULL COMMENT 'Estructura Económica';
ALTER TABLE budgetConfigClave add nu_economica_orden int(11) DEFAULT NULL COMMENT 'Orden de la Estructura Económica';

ALTER TABLE budgetConfigClave add nu_administrativa int(11) DEFAULT NULL COMMENT 'Estructura Administrativa';
ALTER TABLE budgetConfigClave add nu_administrativa_orden int(11) DEFAULT NULL COMMENT 'Orden de la Estructura Administrativa';

ALTER TABLE budgetConfigClave add nu_relacion_partida int(11) DEFAULT NULL COMMENT 'Relación PP-Partida';
ALTER TABLE budgetConfigClave add nu_relacion_partida_orden int(11) DEFAULT NULL COMMENT 'Orden de la Relación PP-Partida';

/*
  agrego: cendejas 28/03/2018
  Campos para configuración de tipo de afectacion que lleve clave nueva
*/
ALTER TABLE tb_tipo_afectacion add nu_claveNueva int(11) DEFAULT NULL COMMENT 'Se genera Clave Nueva';

/*
  agrego: cendejas 29/03/2018
  Campo para folio de la adecuacion si es adicion
*/
ALTER TABLE chartdetailsbudgetbytag add nu_transno int(11) DEFAULT NULL COMMENT 'Campo para el folio de la adecuación si es adición';

/*
  agrego: cendejas 03/04/2018
  Tamaño de campo para partida
*/
ALTER TABLE tb_no_existencia_detalle MODIFY ln_partida_esp varchar(255) COMMENT 'partida especifica del articulo';

/*
  agrego: cendejas 04/04/2018
  Cambiar el tipo de campo de entero a varchar
*/
ALTER TABLE purchdata MODIFY tagref varchar(5);
ALTER TABLE stockmoves MODIFY tagref varchar(5);
ALTER TABLE supptransdetails MODIFY tagref_det varchar(5);

/*
  agrego: lopez peña 04/04/2018

*/
ALTER TABLE tb_solicitudes_almacen_detalle ADD COLUMN ln_clave_iden VARCHAR(50) DEFAULT NULL COMMENT 'Campo para Identificador';

/*
  agrego: cendejas 11/04/2018
  Cambiar campo a texto
*/
ALTER TABLE tb_no_existencias MODIFY txt_observaciones text COMMENT 'Obserbvaciones referente a la solicitud';

/*
  agrego: cendejas 12/04/2018
  Cambiar campo a texto
*/
ALTER TABLE chartmaster add nu_nivel int(11) DEFAULT NULL COMMENT 'Número del nivel de la cuenta';

/*
  agrego: Armando Barrientos 17/04/2018
  Campo para almacenar la referencia del id de purchorderdetails
*/
ALTER TABLE tb_no_existencia_detalle ADD COLUMN podetailitem INT(11) DEFAULT NULL COMMENT 'Campo que guarda referencia con el registro de purchorderdetails'

/*
  agrego: cendejas 26/04/2018
  Cambiar tamaño de campo para cuenta contable
*/
ALTER TABLE chartmaster MODIFY accountcode varchar(255) NOT NULL DEFAULT '0';

ALTER TABLE chartmaster_temp MODIFY accountcode varchar(255) NOT NULL DEFAULT '0';

ALTER TABLE chartmasterxlegal MODIFY accountcode varchar(255) DEFAULT NULL;

ALTER TABLE stockcategory MODIFY stockact varchar(255) NOT NULL DEFAULT '0';

ALTER TABLE stockcategory MODIFY adjglact varchar(255) NOT NULL DEFAULT '0';

ALTER TABLE stockcategory MODIFY accountegreso varchar(255) DEFAULT NULL;

ALTER TABLE stockcategory MODIFY accountingreso varchar(255) DEFAULT NULL;

ALTER TABLE stockcategory MODIFY ln_abono_salida varchar(255) DEFAULT NULL;

ALTER TABLE tb_matriz_pagado MODIFY stockact varchar(255) DEFAULT NULL;

ALTER TABLE tb_matriz_pagado MODIFY adjglact varchar(255) DEFAULT NULL;

ALTER TABLE tb_matriz_pagado MODIFY accountegreso varchar(255) DEFAULT NULL;

ALTER TABLE tb_matriz_pagado MODIFY accountingreso varchar(255) DEFAULT NULL;

ALTER TABLE tb_matriz_pagado MODIFY ln_abono_salida varchar(255) DEFAULT NULL;

ALTER TABLE tb_matriz_extraptal MODIFY stockact varchar(255) NOT NULL DEFAULT '0';

ALTER TABLE tb_matriz_extraptal MODIFY adjglact varchar(255) NOT NULL DEFAULT '0';

ALTER TABLE tb_matriz_extraptal MODIFY accountegreso varchar(255) DEFAULT NULL;

ALTER TABLE tb_matriz_extraptal MODIFY accountingreso varchar(255) DEFAULT NULL;

ALTER TABLE tb_matriz_extraptal MODIFY ln_abono_salida varchar(255) DEFAULT NULL;

ALTER TABLE gltrans MODIFY account varchar(255) NOT NULL DEFAULT '0';

ALTER TABLE accountxsupplier MODIFY accountcode varchar(255) DEFAULT NULL;

ALTER TABLE accountgroups MODIFY groupcodetb varchar(255) NOT NULL DEFAULT '';

ALTER TABLE bankaccounts MODIFY accountcode varchar(255) NOT NULL DEFAULT '0';

ALTER TABLE tagsxbankaccounts MODIFY accountcode varchar(255) DEFAULT NULL;

ALTER TABLE chartdetailsbudgetlog MODIFY account varchar(255) DEFAULT NULL COMMENT 'Cuenta del Movimiento';

/*
  agrego: cendejas 02/05/2018
  Campo para almancenar si ya se agrega
*/
ALTER TABLE chartdetailsbudgetlog add sn_reglas_validadas int(11) DEFAULT NULL COMMENT 'Campo para saber si cumple con las reglas';

/*
  agrego: cendejas 04/05/2018
  Se extiene campo para mensaje
*/
ALTER TABLE tb_botones_status MODIFY sn_mensaje_opcional2 varchar(100) DEFAULT NULL COMMENT 'Mensaje opcional 2 para el estatus';

/*
  agrego: cendejas 07/05/2018
  Campo para configuración de polizas de ingreso
*/
ALTER TABLE systypescat add nu_poliza_ingreso int(11) DEFAULT NULL COMMENT '1 - Póliza de Ingreso';

/*
  agrego: cendejas 07/05/2018
  Campo para configuración de polizas de egreso
*/
ALTER TABLE systypescat add nu_poliza_egreso int(11) DEFAULT NULL COMMENT '1 - Póliza de Egreso';

/*
  agrego: cendejas 07/05/2018
  Campo para configuración de polizas de diario
*/
ALTER TABLE systypescat add nu_poliza_diario int(11) DEFAULT NULL COMMENT '1 - Póliza de Diario';

/*
  agrego: cendejas 07/05/2018
  Modificar campo de la descripción de la clave CAMB
*/
ALTER TABLE   tb_partida_articulo  modify descPartidaEspecifica text DEFAULT NULL;

/*
  agrego: desarrollo 08/05/2018
  Modificar campo de familia (nu_cve_familia) varchar ANTES INT(5)
*/
ALTER TABLE stockmaster MODIFY nu_cve_familia VARCHAR(5) DEFAULT '';



/*
  agrego: desarrollo 08/05/2018
  Modificar campo units ya que esta el concepto de 'servicio de obra publica' y se tomo el valor de la tabla unitsofmeasure 
*/
ALTER TABLE stockmaster MODIFY units VARCHAR(255) DEFAULT '';


/*
  agregó: desarrollo 4 08/05/2018
  Modificar campo barcode de 21 caracteres a 30
 */
ALTER TABLE `fixedassets` CHANGE `barcode` `barcode` VARCHAR(30);


/*
  agregó: desarrollo 4 08/05/2018
  Se agrega campo para Unidad Ejecutora en fixedassets (Activo Fijo)
 */
ALTER TABLE `fixedassets` ADD `ue` VARCHAR(10) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Unidad Ejecutora' AFTER `tagrefowner`;


/*
  agrego: cendejas 09/05/2018
  Campo para configuración de tipo de póliza visual
*/
ALTER TABLE systypescat add nu_poliza_visual int(11) DEFAULT NULL COMMENT 'Tipo de Póliza Visual';

/*
  agrego: cendejas 09/05/2018
  Tabla para tipos de póliza visual
*/
CREATE TABLE `tb_cat_poliza_visual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ln_nombre` varchar(255) DEFAULT NULL COMMENT 'Nombre del Tipo de Póliza',
  `txt_descripcion` text COMMENT 'Descripción del Tipo de Póliza',
  `nu_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


/*
  agregó: desarrollo 11/05/2018
  Se agrega campo para clave de empleado, servirá de vínculo con la tabla de suppliers (Proveedores)
 */
ALTER TABLE `tb_empleados` ADD `sn_clave_empleado` VARCHAR(10) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Vínculo con supplierid' AFTER `id_nu_empleado`;

/*
  agrego: cendejas 15/05/2018
  Cambiar el tipo de campo de entero a varchar
*/
ALTER TABLE hs_stockcostsxtag MODIFY tagref VARCHAR(5);

/*
  agregó: desarrollo 15/05/2018
  Modificaciones para los catálogos 2357, 2359 y 2361 que manejan las variables del catálogo 1459
 */
UPDATE tb_cat_panel_catalogo SET 
`ln_configuracion` = '<div name=\"contenedorTabla\" id=\"contenedorTabla\"><div name=\"tablaGrid\" id=\"tablaGrid\"></div></div><div align=\"center\" class=\"row\" style=\"padding-bottom: 10px;\"><component-button type=\"button\" id=\"btnAgregar\" name=\"btnAgregar\" value=\"Nuevo\" class=\"glyphicon glyphicon-plus\"></component-button></div>', 
`ln_grid` = '[{ name: \"capitulo\", type: \"string\" },{ name: \"descripcion\", type: \"string\" },{ name: \"modificar\", type: \"string\" },{ name: \"eliminar\", type: \"string\" },{name:\"identificador\",type:\"string\"}]', 
`ln_grid_col` = '[{ text: \"ID\", datafield: \"capitulo\", width: \"6%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false },{ text: \"Descripción\", datafield: \"descripcion\", width: \"80%\", cellsalign: \"left\", align: \"center\", hidden: false, editable:false }, { text: \"Modificar\", datafield: \"modificar\", width: \"7%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false }, { text: \"Eliminar\", datafield: \"eliminar\", width: \"7%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false }]'
WHERE `id_nu_panel_catalogo` = '11';
UPDATE `tb_cat_panel_detalle` SET `ln_campo` = 'capitulo' WHERE `id_nu_panel_detalle` = 23;

UPDATE tb_cat_panel_catalogo SET 
`ln_configuracion` = '<div name=\"contenedorTabla\" id=\"contenedorTabla\"><div name=\"tablaGrid\" id=\"tablaGrid\"></div></div><div align=\"center\" class=\"row\" style=\"padding-bottom: 10px;\"><component-button type=\"button\" id=\"btnAgregar\" name=\"btnAgregar\" value=\"Nuevo\" class=\"glyphicon glyphicon-plus\"></component-button></div>',
`ln_compuesto` = 'SELECT DISTINCT cap.ccap as ccap, con.ccon as ccon, con.descripcion as descripcion, con.ccon as identificador FROM tb_cat_partidaspresupuestales_capitulo cap join tb_cat_partidaspresupuestales_concepto con on (cap.ccap = con.ccap) WHERE  cap.activo = 1 and con.activo = \'S\' ORDER BY cap.ccap, con.ccon asc', 
`ln_grid_col` = '[{ text: \"Capítulo\", datafield: \"ccap\", width: \"6%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false },{ text: \"Concepto\", datafield: \"ccon\", width: \"6%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false },{ text: \"Descripción\", datafield: \"descripcion\", width: \"74%\", cellsalign: \"left\", align: \"center\", hidden: false, editable:false }, { text: \"Modificar\", datafield: \"modificar\", width: \"7%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false }, { text: \"Eliminar\", datafield: \"eliminar\", width: \"7%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false }]', 
`ind_activo` = '1'
WHERE `id_nu_panel_catalogo` = '12';

UPDATE tb_cat_panel_catalogo SET 
`ln_configuracion` = '<div name=\"contenedorTabla\" id=\"contenedorTabla\"><div name=\"tablaGrid\" id=\"tablaGrid\"></div></div><div align=\"center\" class=\"row\" style=\"padding-bottom: 10px;\"><component-button type=\"button\" id=\"btnAgregar\" name=\"btnAgregar\" value=\"Nuevo\" class=\"glyphicon glyphicon-plus\"></component-button></div>', 
`ln_compuesto` = 'SELECT DISTINCT cap.ccap as ccap, con.ccon as ccon,pp.cparg as cparg, pp.descripcion as descripcion, con.ccon as identificador FROM tb_cat_partidaspresupuestales_capitulo cap JOIN tb_cat_partidaspresupuestales_concepto con on (cap.ccap = con.ccap) JOIN tb_cat_partidaspresupuestales_partidagenerica pp on (cap.ccap = pp.ccap AND con.ccon = pp.ccon) WHERE  cap.activo = 1 and con.activo = \'S\' and pp.activo = \'S\' ORDER BY cap.ccap, con.ccon, pp.cparg asc', 
`ln_grid_col` = '[{ text: \"Capítulo\", datafield: \"ccap\", width: \"6%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false },{ text: \"Concepto\", datafield: \"ccon\", width: \"6%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false },{ text: \". Genérica\", datafield: \"cparg\", width: \"6%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false },{ text: \"Descripción\", datafield: \"descripcion\", width: \"68%\", cellsalign: \"left\", align: \"center\", hidden: false, editable:false }, { text: \"Modificar\", datafield: \"modificar\", width: \"7%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false }, { text: \"Eliminar\", datafield: \"eliminar\", width: \"7%\", cellsalign: \"center\", align: \"center\", hidden: false, editable:false }]'
WHERE `id_nu_panel_catalogo` = '13';


/*
  agrego: desarrollo 18/05/2018
  Se cambiar tamaño de campo para descricion de la solicitudes al alamacen
*/
ALTER TABLE tb_solicitudes_almacen MODIFY txt_observaciones text DEFAULT NULL COMMENT 'Obserbvaciones referente a la solicitud';



/*
  agregó: desarrollo 24/05/2018
  Se agregan campos para Matrices: 137 (Categorías de Inventarios), 2325 (Matriz Pagado de Gastos) y 2326 (Matriz Extra Presupuestal)
 */
ALTER TABLE `stockcategory` ADD `ind_activo` int(1) DEFAULT 1 COMMENT 'Identificador de Activo (1) o Inactivo (0)' AFTER `ln_clave`;
ALTER TABLE `tb_matriz_pagado` ADD `ind_activo` int(1) DEFAULT 1 COMMENT 'Identificador de Activo (1) o Inactivo (0)' AFTER `ln_clave`;
ALTER TABLE `tb_matriz_extraptal` ADD `ind_activo` int(1) DEFAULT 1 COMMENT 'Identificador de Activo (1) o Inactivo (0)' AFTER `ln_clave`;


/*
  agregó: desarrollo 26/05/2018
  Cambios que se ocuparon para el modulo de patrimonio
 */

CREATE TABLE `fixedasset_Resguardos` (
  `idResguardo` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` varchar(60) NOT NULL DEFAULT '',
  `folio` varchar(150) NOT NULL DEFAULT '',
  `fecha` date DEFAULT NULL,
  `estatus` varchar(20) DEFAULT NULL,
  `fechaultimoresguardo` date DEFAULT NULL,
  `ur` varchar(10) DEFAULT NULL,
  `ue` varchar(10) DEFAULT NULL,
  `observaciones` longtext,
  PRIMARY KEY (`idResguardo`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `fixedasset_detalle_resguardos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `folio` varchar(150) NOT NULL DEFAULT '',
  `assetid` int(11) DEFAULT NULL,
  `estatus` varchar(20) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `ur` varchar(10) DEFAULT NULL,
  `ue` varchar(10) DEFAULT NULL,
  `fecha_desincorporacion` date DEFAULT NULL,
  `observaciones` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `fixedassetstatus` (
  `fixedassetstatusid` int(4) NOT NULL DEFAULT '0',
  `fixedassetstatus` varchar(50) DEFAULT NULL,
  `purchaseflag` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Bandera para seleccionar el estatus con el cual se van a actualizar los activos fijos al recibir y dar de alta facturas de compra',
  `image` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`fixedassetstatusid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `fixedassets` ADD `clavebien` VARCHAR(50) DEFAULT '' COMMENT 'clave bien del activo';

ALTER TABLE `fixedassets` ADD `ue` VARCHAR(10) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Unidad Ejecutora' AFTER `tagrefowner`;

ALTER TABLE `fixedassets` ADD `ue` VARCHAR(10) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Unidad Ejecutora' AFTER `tagrefowner`;

CREATE TABLE `fixedassetstatus` (
  `fixedassetstatusid` int(4) NOT NULL DEFAULT '0',
  `fixedassetstatus` varchar(50) DEFAULT NULL,
  `purchaseflag` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Bandera para seleccionar el estatus con el cual se van a actualizar los activos fijos al recibir y dar de alta facturas de compra',
  `image` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`fixedassetstatusid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

UPDATE sec_functions_new SET  title='Alta de Bienes Patrimoniales',  shortdescription='Alta de Bienes Patrimoniales',  comments='Alta de Bienes Patrimoniales' WHERE functionid ='2307';

UPDATE sec_functions SET  title='Alta de Bienes Patrimoniales',  shortdescription='Alta de Bienes Patrimoniales',  comments='Alta de Bienes Patrimoniales' WHERE functionid ='2307';

UPDATE sec_functions_new SET active=0 WHERE functionid ='2282';

UPDATE sec_functions SET active=0 WHERE functionid ='2282';

/*
  agrego: cendejas 28/05/2018
  Agregar campo para Unidad Ejecutora a los Almacenes
*/
ALTER TABLE locations add ln_ue varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora';


/*
  agrego: Desarrollo 30/05/2018
  Se agrego el tipo de movimieno 44
*/
INSERT INTO `systypesinvtrans` (`typeid`, `typename`, `typeno`)
VALUES(44, 'Depreciacion Activo Fijo', 0);

/*
  agregó: desarrollo 05/06/2018
  Se agrega campo ID para 2326 (Matriz Extra Presupuestal), para igualarlo a las Matrices: 137 (Matriz Devengado), 2325 (Matriz Pagado de Gastos)
 */
ALTER TABLE `tb_matriz_extraptal` ADD `id` int(11) NOT NULL COMMENT 'Identificador como en stockcategory y tb_matriz_pagado' FIRST;
SET @c = "z";
UPDATE `tb_matriz_extraptal` SET `id` = IF(@c='z', @c:=1, @c:=@c+1 ) WHERE `id` = 0;
ALTER TABLE `tb_matriz_extraptal` DROP PRIMARY KEY;
ALTER TABLE `tb_matriz_extraptal` ADD PRIMARY KEY (`id`);
ALTER TABLE `tb_matriz_extraptal` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador como en stockcategory y tb_matriz_pagado';



/*
  agregó: desarrollo 06/06/2018
  Creación de la tabla para almacenar los mantenimientos
 */
CREATE TABLE `fixedassetmaintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assetid` int(11) DEFAULT NULL,
  `mttoid` int(11) DEFAULT NULL,
  `userup` varchar(50) DEFAULT NULL,
  `datetimeup` datetime DEFAULT NULL,
  `userasig` varchar(50) DEFAULT NULL,
  `userresponsive` varchar(50) DEFAULT NULL,
  `prioridad` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `description` longtext,
  `descriptionend` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;




/*
  agregó: desarrollo 12/06/2018
  Insert para tipos de movimiento
 */
INSERT INTO `systypescat` (`typeid`, `typename`, `typeno`, `naturalezacontable`, `fiscal`, `EnvioFiscal`, `flaglastinv`, `nu_estado_presupuesto`, `nu_usar_disponible`, `nu_activo`, `ln_descripcion_corta`, `nu_inventario_inicial`, `nu_inventario_entrada`, `nu_inventario_salida`, `nu_usar_disponible_radicado`, `nu_gestion_polizas`, `nu_poliza_ingreso`, `nu_poliza_egreso`, `nu_poliza_diario`, `nu_poliza_visual`)
VALUES
  (287, 'Poliza Manual Contable - GL', 1, 1, 1, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3);
    INSERT INTO `systypescat` (`typeid`, `typename`, `typeno`, `naturalezacontable`, `fiscal`, `EnvioFiscal`, `flaglastinv`, `nu_estado_presupuesto`, `nu_usar_disponible`, `nu_activo`, `ln_descripcion_corta`, `nu_inventario_inicial`, `nu_inventario_entrada`, `nu_inventario_salida`, `nu_usar_disponible_radicado`, `nu_gestion_polizas`, `nu_poliza_ingreso`, `nu_poliza_egreso`, `nu_poliza_diario`, `nu_poliza_visual`)
VALUES
  (288, 'Poliza Extrapresupuestal Contable - GL', 1, 1, 1, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3);    
        
INSERT INTO `systypesinvtrans` (`typeid`, `typename`, `typeno`)
VALUES
  (287, 'Poliza Manual Contable - GL', 0);
  INSERT INTO `systypesinvtrans` (`typeid`, `typename`, `typeno`)
VALUES
  (288, 'Poliza Extrapresupuestal Contable - GL', 0); 

/*
  agregó: desarrollo 13/06/2018
  Se agrega campo para Ramo en g_cat_ppi (Programa Proyecto de Inversión)
 */
ALTER TABLE `g_cat_ppi` ADD `ramo` varchar(50) DEFAULT NULL COMMENT 'Ramo' AFTER `descripcion`;
UPDATE `g_cat_ppi` SET `ramo` = SUBSTR(`pyin`,3,2);

/*
 agregó: dessarrollo 14/06/2018
 Se agrega el campo homologar a la tabla tb_viaticos
*/

 ALTER TABLE `tb_viaticos` ADD `homologar` tinyint(1);


 /*
 agregó: dessarrollo 14/06/2018
 Se agrega el campo empleado_homologado a la tabla tb_viaticos
*/

 ALTER TABLE `tb_viaticos` ADD `empleado_homologado` varchar(100);


 /*

 Agregó: desarrollo 22/06/2018
 Se crea la tabla tb_cat_zonas_economicas

 */

CREATE TABLE `tb_cat_zonas_economicas` (

`id_nu_zona_economica`  int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificación de zona economica',
`ln_descripcion`  VARCHAR(255)  DEFAULT NULL COMMENT 'Descripción de la zona enconómica',
`ind_activo`      tinyint(4)   NOT NULL DEFAULT '1'  COMMENT 'Indicador de si está activo o no',
`nu_porcentaje_descuento` INT(11) NOT NULL DEFAULT '0' COMMENT 'Porcentaje de descuento', 
PRIMARY KEY (`id_nu_zona_economica`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


 /*
 agregó: dessarrollo 22/06/2018
 Se agrega el campo id_nu_zona_economica a la tabla tb_cat_entidad_federativa
*/

 ALTER TABLE `tb_cat_entidad_federativa` ADD `id_nu_zona_economica` int(11) NOT NULL  COMMENT 'Identificación de zona economica';


  /*
 agregó: dessarrollo 22/06/2018
 Se agrega el campo id_zona_economica a la tabla tb_monto_jerarquia
*/

 ALTER TABLE `tb_monto_jerarquia` ADD `id_zona_economica` int(11) COMMENT 'Identificación de zona economica';

 /*
  agregó: desarrollo 25/06/2018
  Se elimina la columna nu_porcentaje_descuento
 */

 ALTER TABLE `tb_cat_zonas_economicas` DROP COLUMN `nu_porcentaje_descuento`;

/*
  agregó: desarrollo 25/06/2018
  Se agrega campo de actividad en chartmaster (Plan de Cuentas)
 */
ALTER TABLE `chartmaster` ADD `ind_activo`      tinyint(4)   NOT NULL DEFAULT '1'  COMMENT 'Indicador de si está activo o no' AFTER `nu_nivel`;

/*
agrego: armando barrientos
fecha: 26/06/2018
Comentario: Campo para identificar el numero consecutivo de poliza por UE y por mes
*/
ALTER TABLE gltrans ADD COLUMN nu_folio_ue INT(11) DEFAULT NULL COMMENT "Campo numerico que guarda el folio consecutivo de la poliza por mes"

/*
  agregó: desarrollo 25/06/2018
  Se agrega campo para label en ABC General
 */
ALTER TABLE `tb_cat_panel_detalle` ADD `ln_etiqueta` text COLLATE utf8_bin NOT NULL COMMENT 'Etiqueta del campo a mostrar en la vista' AFTER `id_nu_funcion`;


  /*
 agregó: lisandro(desarrollo) 06/07/2018
 Se agrega el campo ch_zona_economica a la tabla tb_solicitud_itinerario
*/

 ALTER TABLE `tb_solicitud_itinerario` ADD `ch_zona_economica` char(1) COMMENT 'Indica la zona econmica';


/*
  agrego: cendejas 11/07/2018
  Se agregan valores por default a tablas de las matrices
*/
ALTER TABLE stockcategory MODIFY prodLineId char(6) NOT NULL DEFAULT '0';
ALTER TABLE stockcategory MODIFY idflujo int(4) NOT NULL DEFAULT '0';
ALTER TABLE stockcategory MODIFY cashdiscount double NOT NULL DEFAULT '0';
ALTER TABLE tb_matriz_pagado MODIFY prodLineId char(6) NOT NULL DEFAULT '0';
ALTER TABLE tb_matriz_pagado MODIFY idflujo int(4) NOT NULL DEFAULT '0';
ALTER TABLE tb_matriz_pagado MODIFY cashdiscount double NOT NULL DEFAULT '0';

/*
  agrego: Japheth Calzada 11/07/2018
  Se agrega la columna de activo para tener un estatus de registro
*/

ALTER TABLE clasprog ADD COLUMN activo INT(1) DEFAULT 1 NULL COMMENT 'Se agrega para tener un status del registro' AFTER idprog;

/*
  agregó: desarrollo 11/07/2018
  Se agrega función que hace uso del ABC General, 2403, ABC Tipo Transporte
 */
UPDATE `sec_functions` SET `title` = 'ABC Tipo Transporte', `url` = 'AbcGeneral.php', `shortdescription` = 'ABC Tipo Transporte', `comments` = 'ABC Tipo Transporte' WHERE `functionid` = "2403";
UPDATE `sec_functions_new` SET `title` = 'ABC Tipo Transporte', `url` = 'AbcGeneral.php', `shortdescription` = 'ABC Tipo Transporte', `comments` = 'ABC Tipo Transporte' WHERE `functionid` = "2403";

ALTER TABLE `tb_cat_panel_detalle` ADD `ln_select` text COLLATE utf8_bin;

ALTER TABLE `tb_cat_tipo_transporte` CHANGE  `id_nu_tipo_traspote` `id_nu_tipo_transporte` int(10) NOT NULL COMMENT 'Identificador del tipo de trasporte';

INSERT INTO `tb_cat_panel_catalogo` (`id_nu_funcion`, `ln_configuracion`, `ln_tbl_cat`, `ln_precondicion`, `ln_mensaje`, `ln_campo_activo`, `ln_compuesto`, `ln_grid`, `ln_grid_col`, `ln_col_visual`, `ln_col_excel`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (2403, '<div name="contenedorTabla" id="contenedorTabla"><div name="tablaGrid" id="tablaGrid"></div></div><div align="center" class="row" style="padding-bottom: 10px;"><component-button type="button" id="btnAgregar" name="btnAgregar" value="Nuevo" class="glyphicon glyphicon-plus"></component-button></div>', 'tb_cat_tipo_transporte', 'SELECT * FROM `tb_viaticos` WHERE `ind_tipo_transporte` = "%s"', '[ "error"=>"No puede eliminar el registro indicado", "store"=>"Se generó con éxito el registro", "update"=>"Se actualizó con éxito el registro", "destroy"=>"Se eliminó con éxito del registro"]', 'ind_activo', 'SELECT DISTINCT tt.`id_nu_tipo_transporte`, tt.`ln_nombre_descripcion`, tt.`id_nu_tipo_transporte` AS identificador, "id_nu_tipo_transporte" AS multiidentificadorcampo, tt.`id_nu_tipo_transporte` AS multiidentificadorvalor FROM `tb_cat_tipo_transporte` AS tt WHERE tt.`ind_activo` = 1 ORDER BY LENGTH(tt.`id_nu_tipo_transporte`) ASC, tt.`id_nu_tipo_transporte` ASC', '[{ name: "id", type: "string" },{ name: "descripcion", type: "string" },{ name: "modificar", type: "string" },{ name: "eliminar", type: "string" },{name:"identificador",type:"string"},{name:"multiidentificadorcampo",type:"string"},{name:"multiidentificadorvalor",type:"string"}]', '[{ text: "ID", datafield: "id", width: "6%", cellsalign: "center", align: "center", hidden: false, editable:false },{ text: "Descripción", datafield: "descripcion", width: "80%", cellsalign: "left", align: "center", hidden: false, editable:false }, { text: "Modificar", datafield: "modificar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Eliminar", datafield: "eliminar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }]', '[0,1,2,3]', '[0,1]', 1, NOW());

SET @c = "z";
SELECT IF(@c='z', @c:=`id_nu_panel_catalogo`, "z" ) FROM `tb_cat_panel_catalogo` WHERE `id_nu_funcion` = 2403;
INSERT INTO `tb_cat_panel_detalle` (`id_nu_panel_catalogo`, `id_nu_funcion`, `ln_etiqueta`, `ln_campo`, `ln_columna`, `ln_tipo`, `sn_longitud`, `ln_formato`, `ln_select`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (@c, 2403, 'ID', 'id', 'id_nu_tipo_transporte', 'string', 0, '', '', 1, NOW()),
  (@c, 2403, 'Descripción', 'descripcion', 'ln_nombre_descripcion', 'string', 0, '', '', 1, NOW()),
  (@c, 2403, '', 'identificador', 'id_nu_tipo_transporte', 'string', 0, '', '', 1, NOW()),
  (@c, 2403, '', 'multiidentificadorcampo', 'multiidentificadorcampo', 'string', 0, '', '', 1, NOW()),
  (@c, 2403, '', 'multiidentificadorvalor', 'multiidentificadorvalor', 'string', 0, '', '', 1, NOW());

/*
  agrego: Japheth Calzada 11/07/2018
  Se agrega la columna que se relacione la tabla tb_cat_programa_presupuestario con clasprog*/

ALTER TABLE tb_cat_programa_presupuestario
  ADD COLUMN id_clasprog INT(3) NOT NULL AFTER fecha_efectiva

  /*
  agrego: Japheth Calzada 11/07/2018
  datos en la tabla tb_cat_programa_presupuestario  relacion a  clasprog*/

  UPDATE `ap_grp`.`tb_cat_programa_presupuestario` SET `id_clasprog`='37' WHERE  `id_nu_programa_presupuestario`=15;
  UPDATE `ap_grp`.`tb_cat_programa_presupuestario` SET `id_clasprog`='38' WHERE  `id_nu_programa_presupuestario`=16;
  UPDATE `ap_grp`.`tb_cat_programa_presupuestario` SET `id_clasprog`='31' WHERE  `id_nu_programa_presupuestario`=17;
  UPDATE `ap_grp`.`tb_cat_programa_presupuestario` SET `id_clasprog`='27' WHERE  `id_nu_programa_presupuestario`=19;


  /*agrego: Japheth Calzada 16/07/2018
  Se agrega la tabla tb_reportes_conac_firmas para que almacenara la relacion de los reportes y las firmas*/

  CREATE TABLE `ap_grp`.`tb_reportes_conac_firmas`(  
  `id_nu_reportes_conac_firmas` INT NOT NULL AUTO_INCREMENT,
  `id_nu_reportes_conac` INT NOT NULL,
  `id_nu_detalle_firmas` INT NOT NULL,
  PRIMARY KEY (`id_nu_reportes_conac_firmas`),
  CONSTRAINT `fk_reportes_conac` FOREIGN KEY (`id_nu_reportes_conac`) REFERENCES `ap_grp`.`tb_cat_reportes_conac`(`id_nu_reportes_conac`)
);

CREATE TABLE `ap_grp`.`tb_detalle_firmas`(  
  `id_nu_detalle_firmas` INT NOT NULL AUTO_INCREMENT,
  `id_nu_empleado` INT NOT NULL,
  `titulo` VARCHAR(150) NULL,
  `informacion` VARCHAR(150) NULL,
  PRIMARY KEY (`id_nu_detalle_firmas`)
);

/*agrego: Japheth Calzada 17/07/2018
  Cambios a la tabla deacuerdo a la nueva reuinion*/
ALTER TABLE `ap_grp`.`tb_reportes_conac_firmas`   
  CHANGE `id_nu_detalle_firmas` `ur` VARCHAR(11) NOT NULL,
  ADD COLUMN `eu` VARCHAR(11) NULL AFTER `ur`;

  ALTER TABLE `ap_grp`.`tb_reportes_conac_firmas`   
  ADD COLUMN `ind_activo` INT(1) NULL AFTER `ue`;
                                 
   

/*agrego: Japheth Calzada 17/07/2018
 Se crea la tabla tb_reporte_firmas*/

  CREATE TABLE `ap_grp`.`tb_reporte_firmas`(  
  `id_nu_reporte_firmas` INT NOT NULL AUTO_INCREMENT,
  `id_nu_reportes_conac_firmas` INT NOT NULL,
  `id_nu_detalle_firmas` INT NOT NULL,
  PRIMARY KEY (`id_nu_reporte_firmas`),
  CONSTRAINT `fk_reportes` FOREIGN KEY (`id_nu_reportes_conac_firmas`) REFERENCES `ap_grp`.`tb_reportes_conac_firmas`(`id_nu_reportes_conac_firmas`),
  CONSTRAINT `fk_firmas` FOREIGN KEY (`id_nu_detalle_firmas`) REFERENCES `ap_grp`.`tb_detalle_firmas`(`id_nu_detalle_firmas`)
);

/*agrego: Japheth Calzada 17/07/2018
 Actualizar los datos de tb_cat_puesto*/
UPDATE tb_cat_puesto SET ln_descripcion = sn_codigo

/*agrego: Japheth Calzada 19/07/2018
Firmas tengan su propio ur y ue*/
ALTER TABLE `ap_grp`.`tb_detalle_firmas`   
  ADD COLUMN `ur` VARCHAR(10) NULL AFTER `informacion`,
  ADD COLUMN `ue` VARCHAR(10) NULL AFTER `ur`;


/*
  agregó: desarrollo 20/07/2018
  Se crea tabla `tb_matriz_viaticos` para Matriz de Viáticos, y se llena con los registros de la tabla `stockcategory` que tienen registrada la partida 370
 */
CREATE TABLE `tb_matriz_viaticos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryid` char(20) NOT NULL DEFAULT '',
  `categorydescription` varchar(255) NOT NULL DEFAULT '',
  `stocktype` char(1) NOT NULL DEFAULT 'F',
  `stockact` varchar(255) DEFAULT NULL,
  `adjglact` varchar(255) DEFAULT NULL,
  `purchpricevaract` varchar(25) NOT NULL DEFAULT '0',
  `materialuseagevarac` varchar(25) NOT NULL DEFAULT '0',
  `wipact` varchar(25) NOT NULL DEFAULT '0',
  `adjglacttransf` int(11) NOT NULL DEFAULT '0',
  `allowNarrativePOLine` int(4) NOT NULL DEFAULT '0',
  `margenaut` double NOT NULL DEFAULT '0',
  `prodLineId` char(6) NOT NULL,
  `redinvoice` int(4) unsigned DEFAULT '0',
  `minimummarginsales` double DEFAULT '0',
  `idflujo` int(4) unsigned NOT NULL,
  `disabledprice` int(4) unsigned NOT NULL DEFAULT '0',
  `internaluse` int(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Cuenta de Uso Interno',
  `warrantycost` double DEFAULT '0' COMMENT 'Porcentaje de incremento de costo por garantia',
  `cashdiscount` double NOT NULL,
  `stockconsignmentact` varchar(45) DEFAULT NULL,
  `margenautcost` double DEFAULT '0',
  `typeoperationdiot` int(11) DEFAULT '0',
  `deductibleflag` int(11) DEFAULT '0',
  `u_typeoperation` int(11) DEFAULT '0',
  `textimage` text,
  `image` varchar(255) DEFAULT NULL,
  `glcodebydelivery` varchar(50) DEFAULT '',
  `discountInPriceListOnPrice` tinyint(4) DEFAULT '0',
  `discountInComercialOnPrice` tinyint(4) DEFAULT '0',
  `cattipodescripcion` int(11) DEFAULT NULL,
  `countword` char(1) DEFAULT '0',
  `generaPublicacionAutomatica` int(11) DEFAULT NULL,
  `salesplanning` int(11) DEFAULT '0',
  `ordendesplegar` varchar(50) DEFAULT NULL,
  `optimo` double DEFAULT '0',
  `minimo` double DEFAULT '0',
  `maximo` double DEFAULT '0',
  `CodigoPanelControl` int(11) DEFAULT NULL,
  `MensajeOC` varchar(255) DEFAULT NULL,
  `MensajePV` varchar(255) DEFAULT NULL,
  `stockshipty` varchar(255) DEFAULT NULL COMMENT 'Cuenta de costo de venta en Cierre de embarques',
  `showmovil` char(1) DEFAULT '0',
  `factesquemadoancho` int(11) DEFAULT '0',
  `factesquemadoalto` int(11) DEFAULT '0',
  `disabledcosto` int(11) DEFAULT '0',
  `belowcost` varchar(1) DEFAULT '0',
  `accounttransfer` varchar(50) DEFAULT NULL,
  `diascaducidad` int(11) DEFAULT '0',
  `accountegreso` varchar(255) DEFAULT NULL,
  `accountingreso` varchar(255) DEFAULT NULL,
  `nu_tipo_gasto` int(11) DEFAULT NULL COMMENT 'Tipo de Gasto',
  `ln_abono_salida` varchar(255) DEFAULT NULL,
  `ln_clave` varchar(50) DEFAULT NULL COMMENT 'Campo para Identificador',
  `ind_activo` int(1) DEFAULT '1' COMMENT 'Identificador de Activo (1) o Inactivo (0)',
  `available` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `tb_matriz_viaticos` (`categoryid`,`categorydescription`,`stocktype`,`stockact`,`adjglact`,`purchpricevaract`,`materialuseagevarac`,`wipact`,`adjglacttransf`,`allowNarrativePOLine`,`margenaut`,`prodLineId`,`redinvoice`,`minimummarginsales`,`idflujo`,`disabledprice`,`internaluse`,`warrantycost`,`cashdiscount`,`stockconsignmentact`,`margenautcost`,`typeoperationdiot`,`deductibleflag`,`u_typeoperation`,`textimage`,`image`,`glcodebydelivery`,`discountInPriceListOnPrice`,`discountInComercialOnPrice`,`cattipodescripcion`,`countword`,`generaPublicacionAutomatica`,`salesplanning`,`ordendesplegar`,`optimo`,`minimo`,`maximo`,`CodigoPanelControl`,`MensajeOC`,`MensajePV`,`stockshipty`,`showmovil`,`factesquemadoancho`,`factesquemadoalto`,`disabledcosto`,`belowcost`,`accounttransfer`,`diascaducidad`,`accountegreso`,`accountingreso`,`nu_tipo_gasto`,`ln_abono_salida`,`ln_clave`,`ind_activo`)

SELECT `categoryid`,`categorydescription`,`stocktype`,`stockact`,`adjglact`,`purchpricevaract`,`materialuseagevarac`,`wipact`,`adjglacttransf`,`allowNarrativePOLine`,`margenaut`,`prodLineId`,`redinvoice`,`minimummarginsales`,`idflujo`,`disabledprice`,`internaluse`,`warrantycost`,`cashdiscount`,`stockconsignmentact`,`margenautcost`,`typeoperationdiot`,`deductibleflag`,`u_typeoperation`,`textimage`,`image`,`glcodebydelivery`,`discountInPriceListOnPrice`,`discountInComercialOnPrice`,`cattipodescripcion`,`countword`,`generaPublicacionAutomatica`,`salesplanning`,`ordendesplegar`,`optimo`,`minimo`,`maximo`,`CodigoPanelControl`,`MensajeOC`,`MensajePV`,`stockshipty`,`showmovil`,`factesquemadoancho`,`factesquemadoalto`,`disabledcosto`,`belowcost`,`accounttransfer`,`diascaducidad`,`accountegreso`,`accountingreso`,`nu_tipo_gasto`,`ln_abono_salida`,`ln_clave`,`ind_activo`
FROM `stockcategory` WHERE `categoryid` LIKE '37%';

/*
  agregó: desarrollo 20/07/2018
  Cambio implementado en desarrollo el 12/07/2018, para que la integridad referencial de Clasificación Programatica (1543) con Programa Presupuestario (2255) funcione correctamente
 */
UPDATE `tb_cat_programa_presupuestario` AS tcpp
LEFT JOIN `clasprog` AS cp ON cp.`clave` LIKE SUBSTR(tcpp.`cppt`,1,1)
SET tcpp.`id_clasprog` = IF(cp.`id` IS NOT NULL, cp.`id`, 0);

/*agrego: Japheth Calzada
Dar estatus de activo a los firmantes
**/

ALTER TABLE `ap_grp`.`tb_detalle_firmas`   
  ADD COLUMN `ind_activo` INT(1) DEFAULT 1 NULL AFTER `ue`;

ALTER TABLE `ap_grp`.`tb_reporte_firmas`   
  ADD COLUMN `ind_activo` INT(1) DEFAULT 1 NULL AFTER `id_nu_detalle_firmas`;

  /*agrego:Japheth Calzada
  Agregar función 
  */
  INSERT INTO sec_funxuser (userid,functionid,permiso ) VALUES('desarrollo',2404,1);
  INSERT INTO sec_funxuser (userid,functionid,permiso ) VALUES('desarrollo',2402,1);

/*
  agregó: desarrollo 24/07/2018
  Se agrega campo para guardar el país a visitar en comisiones internacionales (Viáticos)
 */
ALTER TABLE `tb_solicitud_itinerario` ADD `nu_destino_pais` int(11) DEFAULT NULL COMMENT 'País de la comision, sólo para Internacionales' AFTER `id_nu_solicitud_viaticos`;

/*
  agrego: desarrollo 26/07/2018
  Agregar campo para validar si se agrega justificacion a la captura de una anteproyecto
*/
ALTER TABLE tb_ante_principal add nu_val_justificacion int(11) NOT NULL DEFAULT '0' COMMENT 'Si valida justificación por clave';

/*
  agrego: cendejas 31/07/2018
  Agregar configuración a documentos que son parte de los estados del ministrado y radicado
*/
ALTER TABLE systypescat add nu_estado_ministrado int(11) DEFAULT NULL COMMENT '1 - Si pertenece a un Estado del Ministrado';
ALTER TABLE systypescat add nu_estado_radicado int(11) DEFAULT NULL COMMENT '1 - Si pertenece a un Estado del Radicado';
ALTER TABLE systypescat add nu_usar_modificado int(11) DEFAULT NULL COMMENT '1 - Si pertenece a un Estado del Presupuesto y se va a tomar en cuenta para el modificado autorizado';
ALTER TABLE systypescat add nu_usar_por_liberar int(11) DEFAULT NULL COMMENT '1 - Si se va a tomar en cuenta para el ministrado (por liberar)';
ALTER TABLE systypescat add nu_usar_por_radicar int(11) DEFAULT NULL COMMENT '1 - Si se va a tomar en cuenta para el radicado (por radicar)';

/*
  agrego: cendejas 09/08/2018
  Campo para agregar el número de compromiso al log presupuestal
*/
ALTER TABLE chartdetailsbudgetlog add nu_id_compromiso varchar(50) DEFAULT NULL COMMENT 'Campo para guardar el id del compromiso';

/*
  agregó: desarrollo 06/08/2018
  Se agrega campo para indicar el momento presupuestal del oficio de comisión
 */
ALTER TABLE `tb_viaticos` ADD `ind_momento_presupuestal` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Indicador de los momentos presupuestales que se han ejecutado' AFTER `systypeno`;

/*
  agregó: desarrollo 16/08/2018
  Se agrega campo para guardar el porcentaje de pernocta autorizado para el oficio de comisión
 */
ALTER TABLE `tb_viaticos` ADD `nu_porcentaje_pernocta` int(1) DEFAULT 0 COMMENT 'Campo para guardar el porcentaje de pernocta autorizado' AFTER `ln_informe`;

/*
  agrego: cendejas 17/08/2018
  Campo para agregar si pertenece al panel de pagos
*/
ALTER TABLE systypescat add nu_panel_pagos int(11) DEFAULT NULL COMMENT '1 - Si es documento de pago';
ALTER TABLE systypescat add nu_tesoreria_pagos int(11) DEFAULT NULL COMMENT '1 - Si es documento de pago en el tesoreria';

/*
  agrego: cendejas 17/08/2018
  Campo para agregar el número de devengado al log presupuestal
*/
ALTER TABLE chartdetailsbudgetlog add nu_id_devengado varchar(50) DEFAULT NULL COMMENT 'Campo para guardar el id del devengado';

/*
  agrego: cendejas 21/08/2018
  Campo para el periodo en el detalle del pago
*/
ALTER TABLE supptransdetails add period double DEFAULT NULL COMMENT 'Periodo del Movimiento';

/*
  agregó: desarrollo 21/08/2018
  Se agrega campo para guardar la cuota diaria
 */
ALTER TABLE `tb_solicitud_itinerario` ADD `amt_cuota_diaria` decimal(14,4) DEFAULT NULL COMMENT 'Campo para guardar la cuota diaria de cada registro del itineario' AFTER `dt_periodo_termino`;

/*
  agrego: cendejas 22/08/2018
  Campo para clabe bancaria
*/
ALTER TABLE supptrans add id_clabe int(11) DEFAULT NULL COMMENT 'Id clabe bancaria';

/*
  agrego: desarrollo 22/08/2018
  Tablas para el panel del devengado
  Inicio
*/
CREATE TABLE `tb_pagos` (
  `nu_mov` int(11) NOT NULL AUTO_INCREMENT,
  `dtm_fecha` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `sn_userid` varchar(100) DEFAULT NULL COMMENT 'Usuario de Registro',
  `txt_justificacion` text COMMENT 'Justificación del Compromiso',
  `nu_type` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Tipo de Movimiento',
  `nu_transno` int(11) NOT NULL DEFAULT '0' COMMENT 'Numero de Movimiento',
  `nu_id_compromiso` varchar(50) NOT NULL DEFAULT '0' COMMENT 'Id Compromiso',
  `nu_id_devengado` varchar(50) NOT NULL DEFAULT '0' COMMENT 'Id Compromiso',
  `sn_contrato` varchar(100) DEFAULT NULL COMMENT 'Contrato que se firmó',
  `nu_estatus` varchar(255) DEFAULT NULL COMMENT 'Estatus del Movimiento',
  `sn_tagref` varchar(5) NOT NULL DEFAULT '0' COMMENT 'Unidad Responsable',
  `supplierid` varchar(10) NOT NULL DEFAULT '' COMMENT 'Proveedor / Beneficiario',
  `sn_funcion_id` int(11) DEFAULT NULL COMMENT 'Número de Función Estatus',
  `ln_ue` varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora',
  `id_clabe` int(11) DEFAULT NULL COMMENT 'Id clabe bancaria',
  `sn_folio_solicitud` varchar(50) DEFAULT NULL COMMENT 'Folio alfanumerico de la solicitud de viaticos',
  KEY `nu_mov` (`nu_mov`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tb_pagos_retenciones` (
  `nu_mov` int(11) NOT NULL AUTO_INCREMENT,
  `dtm_fecha` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `sn_userid` varchar(100) DEFAULT NULL COMMENT 'Usuario de Registro',
  `txt_justificacion` text COMMENT 'Justificación del Compromiso',
  `nu_type` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Tipo de Movimiento',
  `nu_transno` int(11) NOT NULL DEFAULT '0' COMMENT 'Numero de Movimiento',
  `nu_id_compromiso` varchar(50) NOT NULL DEFAULT '0' COMMENT 'Id Compromiso',
  `nu_id_devengado` varchar(50) NOT NULL DEFAULT '0' COMMENT 'Id Compromiso',
  `sn_contrato` varchar(100) DEFAULT NULL COMMENT 'Contrato que se firmó',
  `nu_estatus` varchar(255) DEFAULT NULL COMMENT 'Estatus del Movimiento',
  `sn_tagref` varchar(5) NOT NULL DEFAULT '0' COMMENT 'Unidad Responsable',
  `supplierid` varchar(10) NOT NULL DEFAULT '' COMMENT 'Proveedor / Beneficiario',
  `sn_funcion_id` int(11) DEFAULT NULL COMMENT 'Número de Función Estatus',
  `ln_ue` varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora',
  `nu_idret` int(11) DEFAULT NULL COMMENT 'Id de la retención',
  `nu_id_log` int(11) DEFAULT NULL COMMENT 'Id del registro del log presupuestal',
  `ln_clavepresupuestal` varchar(100) DEFAULT NULL COMMENT 'Clave presupuestal',
  `nu_period` double DEFAULT NULL COMMENT 'Periodo',
  `nu_qty` double NOT NULL DEFAULT '0' COMMENT 'Cantidad',
  KEY `nu_mov` (`nu_mov`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tb_retenciones` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sn_clave` varchar(2) DEFAULT NULL COMMENT 'Clave de la retención del catálogo del sat',
  `txt_descripcion` text COMMENT 'Descripcion de la retención',
  `nu_suppliers` int(11) DEFAULT NULL COMMENT 'Si es de proveedores esta retención',
  `nu_active` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  `nu_porcentaje` double DEFAULT NULL COMMENT 'Porcentaje de la retención',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

CREATE TABLE `tb_suppliersRetencion` (
  `supplierid` varchar(60) NOT NULL COMMENT 'Id del proveedor',
  `idret` int(11) NOT NULL COMMENT 'Id de retencion',
  PRIMARY KEY (`supplierid`,`idret`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*
  agrego: desarrollo 22/08/2018
  Tablas para el panel del devengado
  Fin
*/

/*
  agrego: desarrollo 27/08/2018
  Campo para datos de la factura
*/
ALTER TABLE tb_pagos add sn_factura varchar(255) DEFAULT NULL COMMENT 'Factura de Pago';
ALTER TABLE tb_pagos add dtm_fecha_factura datetime NOT NULL DEFAULT '1900-01-01 00:00:00' COMMENT 'Fecha de Factura de Pago';
ALTER TABLE tb_pagos add dtm_fecha_inicio datetime NOT NULL DEFAULT '1900-01-01 00:00:00' COMMENT 'Fecha de Inicio para Impuestos';
ALTER TABLE tb_pagos add dtm_fecha_fin datetime NOT NULL DEFAULT '1900-01-01 00:00:00' COMMENT 'Fecha de Final para Impuestos';

/*
  agrego: Japheth Calzada 28/08/2018
  Se ingresa en bd etiqueta de Instrumental
*/
INSERT INTO stocktypeflag (stockflag,stocknameflag,orden,default,sn_activo) VALUES ('I','Instrumental',6,0,1)

/*
  agrego: Japheth Calzada 28/08/2018
  Se ingresa en la tabla tb_botones_status estatus para la transferencia de entrada
*/

INSERT INTO tb_botones_status (statusid,statusname,namebutton,
functionid,adecuacionPresupuestal,clases,
sn_flag_disponible,sn_panel_adecuacion_presupuestal,sn_adecuacion_presupuestal,
sn_captura_requisicion,sn_funcion_id,
sn_estatus_siguiente,sn_estatus_anterior,
sn_nombre_secundario,sn_mensaje_opcional)
VALUES (1,'Capturado','Guardar',
45,0,'glyphicon glyphicon-floppy-disk',
1,0,0,
1,45,2,
0,'Capturada','Capturada')


INSERT INTO tb_botones_status (statusid,statusname,namebutton,
functionid,adecuacionPresupuestal,clases,
sn_flag_disponible,sn_panel_adecuacion_presupuestal,sn_adecuacion_presupuestal,
sn_captura_requisicion,sn_funcion_id,
sn_estatus_siguiente,sn_estatus_anterior,
sn_nombre_secundario,sn_mensaje_opcional)
VALUES (2,'Validar','Validar',
45,0,'glyphicon glyphicon-check',
1,0,0,
1,45,3,
2,'Por Validar','Por Validar')

INSERT INTO tb_botones_status (statusid,statusname,namebutton,
functionid,adecuacionPresupuestal,clases,
sn_flag_disponible,sn_panel_adecuacion_presupuestal,sn_adecuacion_presupuestal,
sn_captura_requisicion,sn_funcion_id,
sn_estatus_siguiente,sn_estatus_anterior,
sn_nombre_secundario,sn_mensaje_opcional)
VALUES (3,'PorAutorizar','PorAutorizar',
45,0,'glyphicon glyphicon-forward',
1,0,0,
1,45,4,
2,'Por Autorizar','Por Autorizar')

INSERT INTO tb_botones_status (statusid,statusname,namebutton,
functionid,adecuacionPresupuestal,clases,
sn_flag_disponible,sn_panel_adecuacion_presupuestal,sn_adecuacion_presupuestal,
sn_captura_requisicion,sn_funcion_id,
sn_estatus_siguiente,sn_estatus_anterior,
sn_nombre_secundario,sn_mensaje_opcional)
VALUES (4,'PorEntregar','PorEntregar',
45,0,'glyphicon glyphicon-flag',
1,0,0,
1,45,4,
3,'Por Entregar','Por Entregar')

INSERT INTO tb_botones_status (statusid,statusname,namebutton,
functionid,adecuacionPresupuestal,clases,
sn_flag_disponible,sn_panel_adecuacion_presupuestal,sn_adecuacion_presupuestal,
sn_captura_requisicion,sn_funcion_id,
sn_estatus_siguiente,sn_estatus_anterior,
sn_nombre_secundario,sn_mensaje_opcional)
VALUES (6,'Cancelado','Cancelado',
45,0,'glyphicon glyphicon-trash',
1,0,0,
1,45,0,
0,'Cancelada','Cancelada')

INSERT INTO tb_botones_status (statusid,statusname,namebutton,
functionid,adecuacionPresupuestal,clases,
sn_flag_disponible,sn_panel_adecuacion_presupuestal,sn_adecuacion_presupuestal,
sn_captura_requisicion,sn_funcion_id,
sn_estatus_siguiente,sn_estatus_anterior,
sn_nombre_secundario,sn_mensaje_opcional)
VALUES (5,'Entregado','Entregado',
45,0,'glyphicon glyphicon-floppy-disk',
1,0,0,
1,45,5,
0,'Entregado','Entregado')

/*
  agrego: Japheth Calzada 28/08/2018
  Se ingresa en la tabla loctransfers los campos necesarios para los estatus de la transferencia de almacenes
*/

 ALTER TABLE `ap_grp`.`loctransfers`   
  ADD COLUMN `registerdate` TIMESTAMP NULL AFTER `recqty`,
  ADD COLUMN `validatedate` TIMESTAMP NULL AFTER `registerdate`,
  ADD COLUMN `uservalidate` VARCHAR(30) NULL AFTER `userregister`,
  ADD COLUMN `userauthorize` VARCHAR(50) NULL AFTER `uservalidate`;
  ADD COLUMN `updatedate` TIMESTAMP NULL AFTER `recdate`;


/*
  agrego: desarrollo 29/08/2018
  Campo para tipo de operación del cheque o transferencia
*/
ALTER TABLE banktrans add nu_type smallint(6) NOT NULL DEFAULT '0' COMMENT 'Tipo de Operación del Movimiento';

/*agrego: Japheth Calzada 29/08/2018
  Permisos para transferencia salida
*/
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (9,45);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (10,45);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (11,46);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (7,2450);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (10,2450);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (10,2451);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (7,2451);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (9,2450);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (9,2451);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (10,2452);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (7,2452);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (7,2453);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (11,45);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (11,2450);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (11,2451);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (11,2452);
INSERT INTO sec_funxprofile (profileid,functionid) VALUES (11,2453);

INSERT INTO sec_funxprofile (profileid,functionid) VALUES (14,46)

INSERT INTO sec_funxprofile (profileid,functionid) VALUES (14,2454)

/*
  agrego: desarrollo 30/08/2018
  Campo para almacenar el id de la retencion
*/
ALTER TABLE chartdetailsbudgetlog add nu_idret int(11) DEFAULT NULL COMMENT 'Id de la retención';
ALTER TABLE chartdetailsbudgetlog MODIFY nu_idret int(11) NOT NULL DEFAULT 0 COMMENT 'Id de la retención';

/*
  agrego: cendejas 30/08/2018
  Campos para el compromiso, devengado y retención en documentos de pago
*/
ALTER TABLE supptransdetails add nu_id_compromiso varchar(50) DEFAULT NULL COMMENT 'Campo para guardar el id del compromiso';
ALTER TABLE supptransdetails add nu_id_devengado varchar(50) DEFAULT NULL COMMENT 'Campo para guardar el id del devengado';
ALTER TABLE supptransdetails add nu_idret int(11) NOT NULL DEFAULT 0 COMMENT 'Id de la retención';

/*
  agrego: Japheth Calzada 01/09/2018
  Se agrege campo idtransfer para que pueda manejarse en un folio varias transferencias
*/

ALTER TABLE `ap_grp`.`loctransfers`   
  ADD COLUMN `idtransfer` INT NOT NULL AUTO_INCREMENT AFTER `reference`,
  ADD KEY(`idtransfer`);

/*
  agrego: Japheth Calzada 03/09/2018
  Darle al usuario Desarrollador permisos de almacenista
  */
 INSERT INTO sec_funxprofile (profileid,functionid) VALUES (7,2293);
  INSERT INTO sec_funxprofile (profileid,functionid) VALUES (7,2454);

/*
  agrego: Japheth Calzada 03/09/2018
  Agregar status a la funcion 46
  */
INSERT INTO tb_botones_status (statusid,statusname,namebutton,
functionid,adecuacionPresupuestal,clases,
sn_flag_disponible,sn_panel_adecuacion_presupuestal,sn_adecuacion_presupuestal,
sn_captura_requisicion,sn_funcion_id,
sn_estatus_siguiente,sn_estatus_anterior,
sn_nombre_secundario,sn_mensaje_opcional)
VALUES (4,'Por recibir','Por recibir',
46,0,'glyphicon glyphicon-flag',
1,0,0,
1,46,4,
3,'Por recibir','Por recibir')

INSERT INTO tb_botones_status (statusid,statusname,namebutton,
functionid,adecuacionPresupuestal,clases,
sn_flag_disponible,sn_panel_adecuacion_presupuestal,sn_adecuacion_presupuestal,
sn_captura_requisicion,sn_funcion_id,
sn_estatus_siguiente,sn_estatus_anterior,
sn_nombre_secundario,sn_mensaje_opcional)
VALUES (6,'Recibido','Recibido',
46,0,'glyphicon glyphicon-flag',
1,0,0,
1,46,4,
3,'Recibido','Recibido')

/*
  agrego: desarrollo 05/08/2018
  Campo para identificar si el proveedor es la tesofe
*/
ALTER TABLE suppliers add nu_tesofe int(11) NOT NULL DEFAULT 0 COMMENT '1 - Si es Tesofe para pagos de impuestos';

/*
  agregó: desarrollo 05/09/2018
  Se agrega campo para funciones post INSERT y post UPDATE del ABC General
 */
ALTER TABLE `tb_cat_panel_catalogo` ADD `ln_postmodificacion` text COLLATE utf8_bin NOT NULL COMMENT 'Query que se debe ejecutar después de cualquier INSERT o UPDATE' AFTER `ln_compuesto`;

/*
  agregó: desarrollo 05/09/2018
  Cambio en la redacción de las instrucciones de las tablas del ABC General
 */
ALTER TABLE `tb_cat_panel_catalogo`
CHANGE `id_nu_panel_catalogo` `id_nu_panel_catalogo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de la tabla',
CHANGE `id_nu_funcion` `id_nu_funcion` int(10) NOT NULL COMMENT 'Identificador de la función que representa el panel',
CHANGE `ln_configuracion` `ln_configuracion` text COLLATE utf8_bin NOT NULL COMMENT 'Configuración del contenido del catálogo',
CHANGE `ln_tbl_cat` `ln_tbl_cat` varchar(100) COLLATE utf8_bin NOT NULL COMMENT 'Tabla de donde se obtiene, registra y afecta el contenido',
CHANGE `ln_precondicion` `ln_precondicion` text COLLATE utf8_bin COMMENT 'Query que se debe ejecutar antes de la eliminación de algún elemento de la tabla indicada',
CHANGE `ln_mensaje` `ln_mensaje` text COLLATE utf8_bin COMMENT 'Mensaje que será mostrado en caso de que la ejecución de la precondición no se cumpla',
CHANGE `ln_campo_activo` `ln_campo_activo` varchar(100) COLLATE utf8_bin NOT NULL COMMENT 'Indicador de campo activo contra el que se comprobará el estatus',
CHANGE `ln_compuesto` `ln_compuesto` text COLLATE utf8_bin COMMENT 'Consulta compuesta para el muestreo de la información en pantalla',
CHANGE `ln_grid` `ln_grid` text COLLATE utf8_bin NOT NULL COMMENT 'Configuración del dataset del grid',
CHANGE `ln_grid_col` `ln_grid_col` text COLLATE utf8_bin NOT NULL COMMENT 'Configuración de las columnas que mostrará el grid',
CHANGE `ln_col_visual` `ln_col_visual` text COLLATE utf8_bin NOT NULL COMMENT 'Configuración del grid, columnas que se visualizan',
CHANGE `ln_col_excel` `ln_col_excel` text COLLATE utf8_bin NOT NULL COMMENT 'Configuración del grid, columnas a exportar',
CHANGE `ind_activo` `ind_activo` int(2) DEFAULT '1' COMMENT 'Estatus del panel. 1=activo, 0=inactivo',
CHANGE `dtm_fecha_alta` `dtm_fecha_alta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha alta del registro';

ALTER TABLE `tb_cat_panel_detalle`
CHANGE `id_nu_panel_detalle` `id_nu_panel_detalle` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de la tabla',
CHANGE `id_nu_panel_catalogo` `id_nu_panel_catalogo` int(11) NOT NULL COMMENT 'Identificador del panel tb_cat_panel_catalogo al que pertenece',
CHANGE `id_nu_funcion` `id_nu_funcion` int(10) NOT NULL COMMENT 'Identificador de la función que representa el panel',
CHANGE `ln_etiqueta` `ln_etiqueta` text COLLATE utf8_bin NOT NULL COMMENT 'Etiqueta del campo a mostrar en la vista',
CHANGE `ln_campo` `ln_campo` text COLLATE utf8_bin NOT NULL COMMENT 'Configuración del campo que representa en la vista',
CHANGE `ln_columna` `ln_columna` text COLLATE utf8_bin NOT NULL COMMENT 'Configuración de la columna a la que pertenece en base de datos',
CHANGE `ln_tipo` `ln_tipo` text COLLATE utf8_bin NOT NULL COMMENT 'Configuración del tipo de dato number, string, date',
CHANGE `sn_longitud` `sn_longitud` int(10) NOT NULL DEFAULT '100' COMMENT 'Longitud del campo indicado',
CHANGE `ln_formato` `ln_formato` text COLLATE utf8_bin NOT NULL COMMENT 'Configuración del formato que se aplicará al dato contenido',
CHANGE `ln_select` `ln_select` text COLLATE utf8_bin NOT NULL COMMENT 'Consulta que será ejecutada en caso de que el campo sea un elemento select',
CHANGE `ind_activo` `ind_activo` int(2) DEFAULT '1' COMMENT 'Estatus del campo, 1=activo, 0=inactivo',
CHANGE `dtm_fecha_alta` `dtm_fecha_alta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha alta del registro';

/*
  agregó: desarrollo 07/09/2018
  Se quito llave para conmbinación de tipo con folio de operacion
 */
ALTER TABLE supptrans DROP KEY TypeTransNo;

/*
  agrego: cendejas 12/09/2018
  Campo para agregar si pertenece al panel de rectificaciones
*/
ALTER TABLE systypescat add nu_rectificaciones_pagos int(11) DEFAULT NULL COMMENT '1 - Si es documento de rectificación';

/*
  agrego: cendejas 13/09/2018
  Agregar configuración a documentos que son parte de los estados del ministrado y radicado
*/
ALTER TABLE systypescat add nu_usar_liberado_disp int(11) DEFAULT NULL COMMENT '1 - Si se va a tomar en cuenta para el liberado disponible (Ministración)';
ALTER TABLE systypescat add nu_usar_radicado_disp int(11) DEFAULT NULL COMMENT '1 - Si se va a tomar en cuenta para el radicado disponible (Radicado)';

/*
  agregó: desarrollo 19/09/2018
  Se agrega ABC General para Retenciones
 */
INSERT INTO `tb_cat_panel_catalogo` (`id_nu_funcion`, `ln_configuracion`, `ln_tbl_cat`, `ln_precondicion`, `ln_mensaje`, `ln_campo_activo`, `ln_compuesto`, `ln_grid`, `ln_grid_col`, `ln_col_visual`, `ln_col_excel`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (
    2466, 
    '<div name="contenedorTabla" id="contenedorTabla"><div name="tablaGrid" id="tablaGrid"></div></div><div align="center" class="row" style="padding-bottom: 10px;"><component-button type="button" id="btnAgregar" name="btnAgregar" value="Nuevo" class="glyphicon glyphicon-plus"></component-button></div>', 
    'tb_retenciones', 
    'SELECT * FROM `tb_viaticos` WHERE `ind_tipo_transporte` = "%s"', 
    '[ "error"=>"No puede eliminar el registro indicado", "store"=>"Se generó con éxito el registro", "update"=>"Se actualizó con éxito el registro", "destroy"=>"Se eliminó con éxito del registro"]',
    'nu_active',
    'SELECT DISTINCT tt.`id`, tt.`txt_descripcion`, `nu_suppliers`, `nu_porcentaje`, tt.`id` AS identificador, "id" AS multiidentificadorcampo, tt.`id` AS multiidentificadorvalor FROM `tb_retenciones` AS tt WHERE tt.`nu_active` = 1 ORDER BY LENGTH(tt.`id`) ASC, tt.`id` ASC', 
    '[{ name: "id", type: "string" },{ name: "descripcion", type: "string" },{ name: "tipoProveedor", type: "string" },{ name: "porcentaje", type: "string" },{ name: "modificar", type: "string" },{ name: "eliminar", type: "string" },{name:"identificador",type:"string"},{name:"multiidentificadorcampo",type:"string"},{name:"multiidentificadorvalor",type:"string"}]', 
    '[{ text: "ID", datafield: "id", width: "6%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Descripción", datafield: "descripcion", width: "70%", cellsalign: "left", align: "center", hidden: false, editable:false }, { text: "Tipo Proveedor", datafield: "tipoProveedor", width: "35%", cellsalign: "left", align: "center", hidden: true, editable:false }, { text: "Porcentaje", datafield: "porcentaje", width: "10%", cellsalign: "left", align: "center", hidden: false, editable:false }, { text: "Modificar", datafield: "modificar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Eliminar", datafield: "eliminar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }]', 
    '[0,1,2,3,4,5]', 
    '[0,1,2,3]', 
    1, 
    NOW()
  );

SET @c = "z";
SELECT IF(@c='z', @c:=`id_nu_panel_catalogo`, "z" ) FROM `tb_cat_panel_catalogo` WHERE `id_nu_funcion` = 2466;
INSERT INTO `tb_cat_panel_detalle` (`id_nu_panel_catalogo`, `id_nu_funcion`, `ln_etiqueta`, `ln_campo`, `ln_columna`, `ln_tipo`, `sn_longitud`, `ln_formato`, `ln_select`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (@c, 2466, 'ID', 'id', 'id', 'number', 0, '', '', 1, NOW()),
  (@c, 2466, 'Descripción', 'descripcion', 'txt_descripcion', 'string', 0, '', '', 1, NOW()),
  (@c, 2466, 'Tipo Proveedor', 'tipoProveedor', 'nu_suppliers', 'select', 0, '', 'SELECT 1 AS `id`, "Proveedor" as `label`', 1, NOW()),
  (@c, 2466, 'Porcentaje', 'porcentaje', 'nu_porcentaje', 'decimal', 0, '', '', 1, NOW()),
  (@c, 2466, '', 'identificador', 'id', 'string', 0, '', '', 1, NOW()),
  (@c, 2466, '', 'multiidentificadorcampo', 'multiidentificadorcampo', 'string', 0, '', '', 1, NOW()),
  (@c, 2466, '', 'multiidentificadorvalor', 'multiidentificadorvalor', 'string', 0, '', '', 1, NOW());

/*
  agregó: desarrollo 19/09/2018
  Se agregan datos para botonera del Panel del Proceso de Compra (2455)
 */
INSERT INTO `tb_botones_status` (`statusid`, `statusname`, `namebutton`, `functionid`, `adecuacionPresupuestal`, `clases`, `sn_flag_disponible`, `sn_panel_adecuacion_presupuestal`, `sn_adecuacion_presupuestal`, `sn_captura_requisicion`, `sn_orden`, `sn_funcion_id`, `sn_estatus_siguiente`, `sn_estatus_anterior`, `sn_nombre_secundario`, `nu_generar_layout`, `nu_tipo_layout`, `sn_mensaje_opcional`, `sn_mensaje_opcional2`, `ind_anexo_tecnico`)
VALUES
  (1, '1', 'Inicio', 0, '0', NULL, NULL, '0', '0', NULL, 1, 2455, 2, 0, 'Estatus de Inicio', NULL, NULL, NULL, NULL, 1),
  (2, '2', 'En Proceso', 0, '0', NULL, NULL, '0', '0', NULL, 2, 2455, 3, 1, 'Estatus de En Proceso', NULL, NULL, NULL, NULL, 1),
  (3, '3', 'Concluido', 0, '0', NULL, NULL, '0', '0', NULL, 3, 2455, 99, 2, 'Estatus de Concluido', NULL, NULL, NULL, NULL, 1),
  (4, '4', 'Cancelado', 0, '0', NULL, NULL, '0', '0', NULL, 4, 2455, 99, 0, 'Estatus de Cancelado', NULL, NULL, NULL, NULL, 1),
  (1, 'Rechazar', 'Rechazar', 2462, '1', 'glyphicon glyphicon-floppy-remove .rechazar', 1, '1', '1', 1, 1, 2455, 99, 0, 'Rechazar', NULL, NULL, 'Rechazar', 'Rechazar', 1),
  (2, 'Avanzar', 'Avanzar', 2463, '1', 'glyphicon glyphicon-forward .avanzar', 1, '1', '1', 1, 2, 2455, 99, 0, 'Avanzar', NULL, NULL, 'Avanzar', 'Avanzar', 1),
  (3, 'Solicitar', 'Solicitar', 2464, '1', 'glyphicon glyphicon-flag .autorizar', 1, '1', '1', 1, 3, 2455, 99, 0, 'Solicitar', NULL, NULL, 'Solicitar', 'Solicitar', 1),
  (4, 'Cancelar', 'Cancelar', 2465, '1', 'glyphicon glyphicon-trash .cancelar', 1, '1', '1', 1, 4, 2455, 99, 0, 'Cancelar', NULL, NULL, 'Cancelar', 'Cancelar', 1);

/*
  agrego: desarrollo 20/09/2018
  Campo para agregar si se visualiza en el ABC de configuración
*/
ALTER TABLE config add nu_visual_config int(11) DEFAULT NULL COMMENT '1 - Si se muestra en la página de configuración';

/*
  agrego: Japheth 25/09/2018
  Se crea tabla para guardar el CSV al Panel de Servicios Personales
*/
CREATE TABLE `ap_grp`.`tb_cat_concepto_nomina`(  
  `id_concepto_nomina` INT NOT NULL AUTO_INCREMENT,
  `PP` VARCHAR(5) NOT NULL,
  `partida` INT,
  `clave_concepto` INT NOT NULL,
  `desc_concepto` VARCHAR(150),
  `tipo_concepto` VARCHAR(2),
  `activo` INT(1) NOT NULL,
  `cta_contable` VARCHAR(50),
  PRIMARY KEY (`id_concepto_nomina`)
) CHARSET=latin1;

CREATE TABLE `tb_file_nomina` (
  `UR` varchar(10) NOT NULL,
  `UE` varchar(10) NOT NULL,
  `PP` varchar(10) NOT NULL,
  `partida` int (10) not null, 
  `cve_concepto` int (10) not null, 
  `desc_concepto` varchar (150) not null, 
  `importe` double(14,2) DEFAULT '0.00',
  `tipo_concepto` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `tb_file_nomina_release` (
 `id_tipo_nom` VARCHAR(10) NOT NULL,
  `UR` VARCHAR(10) NOT NULL,
  `UE` VARCHAR(10) NOT NULL,
  `PP` VARCHAR(10) NOT NULL,
  `partida` INT (10) NOT NULL, 
  `cve_concepto` INT (10) NOT NULL, 
  `importe` DOUBLE(14,2) DEFAULT '0.00',
  `tipo_concepto` VARCHAR(2) NOT NULL
) ENGINE=INNODB DEFAULT CHARSET=latin1;


/*
  agrego: Japheth 25/09/2018
  Se crea store  para guardar el CSV al Panel de Servicios Personales
*/

DELIMITER $$

USE `ap_grp`$$

DROP PROCEDURE IF EXISTS `sp_valida_totales`$$

CREATE DEFINER=`desarrollo`@`%` PROCEDURE `sp_valida_totales`()
BEGIN
    declare 
	vPercep,vTotalPercep,vDeduc,vTotalDeduc,vGranTotal double(19,2);
    SELECT SUM(sueldo_base+prima_dominical+falta_injustificada+sancion_retardo+vacacion_finiquito+vacacion_disfrutada+
	prima_vacacional_gra+prima_vacacional_exe+aguinaldo_gravado+aguinaldo_exento+prima_antiguedad+indemnizac_3_meses+
	indemnizac_20_dias_anio+indemnizac_otros+deporte_cultura+anticipo_vacaciones+ayuda_guarderia+ayuda_adquisicion_de_an+
	ajuste_sueldos+pago_unica_vez+compensacion_garantizada+gastos_defuncion+ayuda_examen_profesional+premio_antiguedad+
	regalo_dia_del_nino+ayuda_canastilla_por_mate+ayuda_adquisicion_ut+becas_hijos+gratificacion_anual_exenta+
	gratificacion_anual_gravada+regalo_dia_madres+ayuda_despensa+prima_antiguedad_exenta+indemnizac_exenta)
    INTO vPercep 
    from ap_grp.tb_file_nomina;
    
    SELECT SUM(total_percepciones)
    INTO vTotalPercep 
    FROM ap_grp.tb_file_nomina;
    
    SELECT SUM(isr+imss+ispt_comp_separac+cuota_sindical_ordin+credito_infonavit+abono_fonacot+pension_alimenticia+
	1_125_c_y_vejez_sar+aportacion_voluntaria_sar+ajuste_infonavit+prestamo_empresa+cr_infonavit_mes_ant+subsidio_empleo+
	seguro_vehiculo+isr_anual+menos_pago+potenciacion_sgmm)
    INTO vDeduc 
    FROM ap_grp.tb_file_nomina;
    
    SELECT SUM(total_deducciones)
    INTO vTotalDeduc 
    FROM ap_grp.tb_file_nomina;
    
    SELECT SUM(neto_pagar)
    INTO vGranTotal 
    FROM ap_grp.tb_file_nomina;
    
    select 
    CASE WHEN vPercep != vTotalPercep THEN 'Error en suma de percepciones' 
	 WHEN vDeduc != vTotalDeduc   THEN 'Error en suma de deducciones'
	 WHEN (vTotalPercep-vTotalDeduc)!=vGranTotal THEN 'Error en suma de percepciones y deducciones'
	 ELSE 'Sin errores'
    END as vResp;
    /*
    if vPercep = vTotalPercep then
    select 'Error en suma de percepciones';
    end IF;
    
    IF vDeduc = vTotalDeduc THEN
    SELECT 'Error en suma de deducciones';
    END IF;
    
    IF (vTotalPercep-vTotalDeduc)=vGranTotal THEN
    SELECT 'Error en suma de percepciones y deducciones';
    END IF;
    */
    END$$

DELIMITER ;

/*
  agregó: desarrollo 26/09/2018
  Se agrega ABC General para Tipo de Expediente
 */
INSERT INTO `tb_cat_panel_catalogo` (`id_nu_funcion`, `ln_configuracion`, `ln_tbl_cat`, `ln_precondicion`, `ln_mensaje`, `ln_campo_activo`, `ln_compuesto`, `ln_grid`, `ln_grid_col`, `ln_col_visual`, `ln_col_excel`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (
    2457, 
    '<div name="contenedorTabla" id="contenedorTabla"><div name="tablaGrid" id="tablaGrid"></div></div><div align="center" class="row" style="padding-bottom: 10px;"><component-button type="button" id="btnAgregar" name="btnAgregar" value="Nuevo" class="glyphicon glyphicon-plus"></component-button></div>', 
    'tb_cat_tipo_compra_expediente', 
    'SELECT * FROM `tb_proceso_compra` WHERE `id_nu_tipo_expediente` = "%s"', 
    '[ "error"=>"No puede eliminar el registro indicado", "store"=>"Se generó con éxito el registro", "update"=>"Se actualizó con éxito el registro", "destroy"=>"Se eliminó con éxito del registro"]',
    'ind_activo',
    'SELECT DISTINCT `id_nu_tipo_compra_expediente`, `ln_nombre_descripcion`, `id_nu_tipo_compra_expediente` AS identificador, "id_nu_tipo_compra_expediente" AS multiidentificadorcampo, `id_nu_tipo_compra_expediente` AS multiidentificadorvalor FROM `tb_cat_tipo_compra_expediente` WHERE `ind_activo` = 1 ORDER BY LENGTH(`id_nu_tipo_compra_expediente`) ASC, `id_nu_tipo_compra_expediente` ASC', 
    '[{ name: "id", type: "string" },{ name: "descripcion", type: "string" },{ name: "modificar", type: "string" },{ name: "eliminar", type: "string" },{name:"identificador",type:"string"},{name:"multiidentificadorcampo",type:"string"},{name:"multiidentificadorvalor",type:"string"}]', 
    '[{ text: "ID", datafield: "id", width: "6%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Descripción", datafield: "descripcion", width: "80%", cellsalign: "left", align: "center", hidden: false, editable:false }, { text: "Modificar", datafield: "modificar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Eliminar", datafield: "eliminar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }]', 
    '[0,1,2,3]', 
    '[0,1]', 
    1, 
    NOW()
  );

SET @c = "z";
SELECT IF(@c='z', @c:=`id_nu_panel_catalogo`, "z" ) FROM `tb_cat_panel_catalogo` WHERE `id_nu_funcion` = 2457;
INSERT INTO `tb_cat_panel_detalle` (`id_nu_panel_catalogo`, `id_nu_funcion`, `ln_etiqueta`, `ln_campo`, `ln_columna`, `ln_tipo`, `sn_longitud`, `ln_formato`, `ln_select`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (@c, 2457, 'ID', 'id', 'id_nu_tipo_compra_expediente', 'number', 0, '', '', 1, NOW()),
  (@c, 2457, 'Descripción', 'descripcion', 'ln_nombre_descripcion', 'string', 0, '', '', 1, NOW()),
  (@c, 2457, '', 'identificador', 'id_nu_tipo_compra_expediente', 'string', 0, '', '', 1, NOW()),
  (@c, 2457, '', 'multiidentificadorcampo', 'multiidentificadorcampo', 'string', 0, '', '', 1, NOW()),
  (@c, 2457, '', 'multiidentificadorvalor', 'multiidentificadorvalor', 'string', 0, '', '', 1, NOW());

/*
  agregó: desarrollo 26/09/2018
  Se agrega ABC General para Tipo de Contratacion
 */
INSERT INTO `tb_cat_panel_catalogo` (`id_nu_funcion`, `ln_configuracion`, `ln_tbl_cat`, `ln_precondicion`, `ln_mensaje`, `ln_campo_activo`, `ln_compuesto`, `ln_grid`, `ln_grid_col`, `ln_col_visual`, `ln_col_excel`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (
    2458, 
    '<div name="contenedorTabla" id="contenedorTabla"><div name="tablaGrid" id="tablaGrid"></div></div><div align="center" class="row" style="padding-bottom: 10px;"><component-button type="button" id="btnAgregar" name="btnAgregar" value="Nuevo" class="glyphicon glyphicon-plus"></component-button></div>', 
    'tb_cat_tipo_compra_contratacion', 
    'SELECT * FROM `tb_proceso_compra` WHERE `id_nu_tipo_contratacion` = "%s"', 
    '[ "error"=>"No puede eliminar el registro indicado", "store"=>"Se generó con éxito el registro", "update"=>"Se actualizó con éxito el registro", "destroy"=>"Se eliminó con éxito del registro"]',
    'ind_activo',
    'SELECT DISTINCT `id_nu_tipo_compra_contratacion`, `ln_nombre_descripcion`, `id_nu_tipo_compra_contratacion` AS identificador, "id_nu_tipo_compra_contratacion" AS multiidentificadorcampo, `id_nu_tipo_compra_contratacion` AS multiidentificadorvalor FROM `tb_cat_tipo_compra_contratacion` WHERE `ind_activo` = 1 ORDER BY LENGTH(`id_nu_tipo_compra_contratacion`) ASC, `id_nu_tipo_compra_contratacion` ASC', 
    '[{ name: "id", type: "string" },{ name: "descripcion", type: "string" },{ name: "modificar", type: "string" },{ name: "eliminar", type: "string" },{name:"identificador",type:"string"},{name:"multiidentificadorcampo",type:"string"},{name:"multiidentificadorvalor",type:"string"}]', 
    '[{ text: "ID", datafield: "id", width: "6%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Descripción", datafield: "descripcion", width: "80%", cellsalign: "left", align: "center", hidden: false, editable:false }, { text: "Modificar", datafield: "modificar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Eliminar", datafield: "eliminar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }]', 
    '[0,1,2,3]', 
    '[0,1]', 
    1, 
    NOW()
  );

SET @c = "z";
SELECT IF(@c='z', @c:=`id_nu_panel_catalogo`, "z" ) FROM `tb_cat_panel_catalogo` WHERE `id_nu_funcion` = 2458;
INSERT INTO `tb_cat_panel_detalle` (`id_nu_panel_catalogo`, `id_nu_funcion`, `ln_etiqueta`, `ln_campo`, `ln_columna`, `ln_tipo`, `sn_longitud`, `ln_formato`, `ln_select`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (@c, 2458, 'ID', 'id', 'id_nu_tipo_compra_contratacion', 'number', 0, '', '', 1, NOW()),
  (@c, 2458, 'Descripción', 'descripcion', 'ln_nombre_descripcion', 'string', 0, '', '', 1, NOW()),
  (@c, 2458, '', 'identificador', 'id_nu_tipo_compra_contratacion', 'string', 0, '', '', 1, NOW()),
  (@c, 2458, '', 'multiidentificadorcampo', 'multiidentificadorcampo', 'string', 0, '', '', 1, NOW()),
  (@c, 2458, '', 'multiidentificadorvalor', 'multiidentificadorvalor', 'string', 0, '', '', 1, NOW());

/*
  agregó: desarrollo 26/09/2018
  Se agrega ABC General para Tipo de Anexo
 */
INSERT INTO `tb_cat_panel_catalogo` (`id_nu_funcion`, `ln_configuracion`, `ln_tbl_cat`, `ln_precondicion`, `ln_mensaje`, `ln_campo_activo`, `ln_compuesto`, `ln_grid`, `ln_grid_col`, `ln_col_visual`, `ln_col_excel`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (
    2459, 
    '<div name="contenedorTabla" id="contenedorTabla"><div name="tablaGrid" id="tablaGrid"></div></div><div align="center" class="row" style="padding-bottom: 10px;"><component-button type="button" id="btnAgregar" name="btnAgregar" value="Nuevo" class="glyphicon glyphicon-plus"></component-button></div>', 
    'tb_cat_tipo_compra_anexo', 
    'SELECT * FROM `tb_proceso_compra_documentos` WHERE `ind_tipo_anexo` = "%s"', 
    '[ "error"=>"No puede eliminar el registro indicado", "store"=>"Se generó con éxito el registro", "update"=>"Se actualizó con éxito el registro", "destroy"=>"Se eliminó con éxito del registro"]',
    'ind_activo',
    'SELECT DISTINCT `id_nu_tipo_compra_anexo`, `ln_nombre_descripcion`, `id_nu_tipo_carpeta` AS `tipoCarpeta`, IF(`id_nu_tipo_carpeta`=1,"Administrativos",IF(`id_nu_tipo_carpeta`=2,"Seguimiento",IF(`id_nu_tipo_carpeta`=3,"Otros",""))) AS `tipoCarpetaTexto`, `id_nu_tipo_compra_anexo` AS identificador, "id_nu_tipo_compra_anexo" AS multiidentificadorcampo, `id_nu_tipo_compra_anexo` AS multiidentificadorvalor FROM `tb_cat_tipo_compra_anexo` WHERE `ind_activo` = 1 ORDER BY LENGTH(`id_nu_tipo_compra_anexo`) ASC, `id_nu_tipo_compra_anexo` ASC', 
    '[{ name: "id", type: "string" },{ name: "descripcion", type: "string" },{ name: "tipoCarpeta", type: "string" },{ name: "tipoCarpetaTexto", type: "string" },{ name: "modificar", type: "string" },{ name: "eliminar", type: "string" },{name:"identificador",type:"string"},{name:"multiidentificadorcampo",type:"string"},{name:"multiidentificadorvalor",type:"string"}]', 
    '[{ text: "ID", datafield: "id", width: "6%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Descripción", datafield: "descripcion", width: "60%", cellsalign: "left", align: "center", hidden: false, editable:false }, { text: "Tipo de Carpeta", datafield: "tipoCarpeta", width: "20%", cellsalign: "left", align: "center", hidden: false, editable:false }, { text: "Modificar", datafield: "modificar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Eliminar", datafield: "eliminar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }]', 
    '[0,1,2,3,4]', 
    '[0,1,3]', 
    1, 
    NOW()
  );

SET @c = "z";
SELECT IF(@c='z', @c:=`id_nu_panel_catalogo`, "z" ) FROM `tb_cat_panel_catalogo` WHERE `id_nu_funcion` = 2459;
INSERT INTO `tb_cat_panel_detalle` (`id_nu_panel_catalogo`, `id_nu_funcion`, `ln_etiqueta`, `ln_campo`, `ln_columna`, `ln_tipo`, `sn_longitud`, `ln_formato`, `ln_select`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (@c, 2459, 'ID', 'id', 'id_nu_tipo_compra_anexo', 'number', 0, '', '', 1, NOW()),
  (@c, 2459, 'Descripción', 'descripcion', 'ln_nombre_descripcion', 'string', 0, '', '', 1, NOW()),
  (@c, 2459, 'Tipo de Carpeta', 'tipoCarpeta', 'id_nu_tipo_carpeta', 'select', 0, '', 'SELECT 1 AS `valor`, "Administrativos" as `label` UNION SELECT 2 AS `valor`, "Seguimiento" as `label` UNION SELECT 3 AS `valor`, "Otros" as `label`', 1, NOW()),
  (@c, 2459, '', 'identificador', 'id_nu_tipo_compra_anexo', 'string', 0, '', '', 1, NOW()),
  (@c, 2459, '', 'multiidentificadorcampo', 'multiidentificadorcampo', 'string', 0, '', '', 1, NOW()),
  (@c, 2459, '', 'multiidentificadorvalor', 'multiidentificadorvalor', 'string', 0, '', '', 1, NOW());

/*
  agregó: desarrollo 26/09/2018
  Se agrega ABC General para Tipo de Periodo de Contrato
 */
INSERT INTO `tb_cat_panel_catalogo` (`id_nu_funcion`, `ln_configuracion`, `ln_tbl_cat`, `ln_precondicion`, `ln_mensaje`, `ln_campo_activo`, `ln_compuesto`, `ln_grid`, `ln_grid_col`, `ln_col_visual`, `ln_col_excel`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (
    2460, 
    '<div name="contenedorTabla" id="contenedorTabla"><div name="tablaGrid" id="tablaGrid"></div></div><div align="center" class="row" style="padding-bottom: 10px;"><component-button type="button" id="btnAgregar" name="btnAgregar" value="Nuevo" class="glyphicon glyphicon-plus"></component-button></div>', 
    'tb_cat_tipo_compra_periodo_contrato', 
    'SELECT * FROM `tb_proceso_compra` WHERE `ind_periodo_contrato` = "%s"', 
    '[ "error"=>"No puede eliminar el registro indicado", "store"=>"Se generó con éxito el registro", "update"=>"Se actualizó con éxito el registro", "destroy"=>"Se eliminó con éxito del registro"]',
    'ind_activo',
    'SELECT DISTINCT `id_nu_tipo_compra_periodo_contrato`, `ln_nombre_descripcion`, `id_nu_tipo_compra_periodo_contrato` AS identificador, "id_nu_tipo_compra_periodo_contrato" AS multiidentificadorcampo, `id_nu_tipo_compra_periodo_contrato` AS multiidentificadorvalor FROM `tb_cat_tipo_compra_periodo_contrato` WHERE `ind_activo` = 1 ORDER BY LENGTH(`id_nu_tipo_compra_periodo_contrato`) ASC, `id_nu_tipo_compra_periodo_contrato` ASC', 
    '[{ name: "id", type: "string" },{ name: "descripcion", type: "string" },{ name: "modificar", type: "string" },{ name: "eliminar", type: "string" },{name:"identificador",type:"string"},{name:"multiidentificadorcampo",type:"string"},{name:"multiidentificadorvalor",type:"string"}]', 
    '[{ text: "ID", datafield: "id", width: "6%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Descripción", datafield: "descripcion", width: "80%", cellsalign: "left", align: "center", hidden: false, editable:false }, { text: "Modificar", datafield: "modificar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Eliminar", datafield: "eliminar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }]', 
    '[0,1,2,3]', 
    '[0,1]', 
    1, 
    NOW()
  );

SET @c = "z";
SELECT IF(@c='z', @c:=`id_nu_panel_catalogo`, "z" ) FROM `tb_cat_panel_catalogo` WHERE `id_nu_funcion` = 2460;
INSERT INTO `tb_cat_panel_detalle` (`id_nu_panel_catalogo`, `id_nu_funcion`, `ln_etiqueta`, `ln_campo`, `ln_columna`, `ln_tipo`, `sn_longitud`, `ln_formato`, `ln_select`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (@c, 2460, 'ID', 'id', 'id_nu_tipo_compra_periodo_contrato', 'number', 0, '', '', 1, NOW()),
  (@c, 2460, 'Descripción', 'descripcion', 'ln_nombre_descripcion', 'string', 0, '', '', 1, NOW()),
  (@c, 2460, '', 'identificador', 'id_nu_tipo_compra_periodo_contrato', 'string', 0, '', '', 1, NOW()),
  (@c, 2460, '', 'multiidentificadorcampo', 'multiidentificadorcampo', 'string', 0, '', '', 1, NOW()),
  (@c, 2460, '', 'multiidentificadorvalor', 'multiidentificadorvalor', 'string', 0, '', '', 1, NOW());


/*
  agregó: Japheth Calzada 26/09/2018
  Se agrega tabla para guardar el proceso de nomina
 */

CREATE TABLE `ap_grp`.`tb_proceso_nomina`(  
  `id_proceso_nomina` INT NOT NULL AUTO_INCREMENT,
  `tipo_nomina` VARCHAR(20) NOT NULL,
  `mes_nomina` VARCHAR(20) NULL AFTER ,
  `quincena` INT(2) NOT NULL,
  `id_tipo_nomina` VARCHAR(20) NOT NULL,
  `fecha_proceso_nomina` TIMESTAMP NOT NULL,
  `usuario_proceso_nomina` VARCHAR(30),
  PRIMARY KEY (`id_proceso_nomina`)
);

/*
  agrego: desarrollo 3 03/10/2019
  Campo para referencia al autorizar pago, y clave de rastreo al realizar el pago
*/
ALTER TABLE supptrans add txt_referencia text DEFAULT NULL COMMENT 'Referencia al autorizar el pago';
ALTER TABLE supptrans add txt_clave_rastreo text DEFAULT NULL COMMENT 'Clave de Rastreo al realizar el pago';


/*
  agrego: desarrollo 0 17/10/2018
  Campo en el plan de cuentas para identificar las cuentas contables por unidad ejecutoras
*/
ALTER TABLE chartmaster ADD COLUMN tagref VARCHAR(5) COMMENT 'Campo que guarda la unidad responsable';

UPDATE chartmaster 
SET ln_clave= RIGHT(SUBSTRING_INDEX(accountcode, '.', 6),2);

UPDATE chartmaster
INNER JOIN tb_cat_unidades_ejecutoras ON chartmaster.ln_clave= tb_cat_unidades_ejecutoras.ue
SET tagref=tb_cat_unidades_ejecutoras.ur;


/*
  agrego: desarrollo 3 18/10/2018
  Tablas para baja patrimonial
*/
CREATE TABLE `tb_Fixed_Baja_Patrimonial` (
  `nu_mov` int(11) NOT NULL AUTO_INCREMENT,
  `dtm_fecha` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `sn_userid` varchar(100) DEFAULT NULL COMMENT 'Usuario de Registro',
  `txt_justificacion` text COMMENT 'Justificación del Compromiso',
  `nu_type` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Tipo de Movimiento',
  `nu_transno` int(11) NOT NULL DEFAULT '0' COMMENT 'Numero de Movimiento',
  `nu_estatus` varchar(255) DEFAULT NULL COMMENT 'Estatus del Movimiento',
  `sn_tagref` varchar(5) NOT NULL DEFAULT '0' COMMENT 'Unidad Responsable',
  `sn_funcion_id` int(11) DEFAULT NULL COMMENT 'Número de Función Estatus',
  `ln_ue` varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora',
  `nu_tipo_bien` int(11) DEFAULT NULL COMMENT 'Tipo de Bien',
  `ln_partida` varchar(10) DEFAULT NULL COMMENT 'Partida Específica',
  KEY `nu_mov` (`nu_mov`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tb_Fixed_Baja_Patrimonial_Detalle` (
  `nu_mov` int(11) NOT NULL AUTO_INCREMENT,
  `dtm_fecha` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `sn_userid` varchar(100) DEFAULT NULL COMMENT 'Usuario de Registro',
  `nu_type` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Tipo de Movimiento',
  `nu_transno` int(11) NOT NULL DEFAULT '0' COMMENT 'Numero de Movimiento',
  `nu_assetid` int(11) NOT NULL DEFAULT '0' COMMENT 'Numero de Activo',
  KEY `nu_mov` (`nu_mov`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


/*
  agrego: desarrollo 0 18/10/2018
  Tabla para guardar los momentos presupuestales por Unidad ejecutora 
*/
CREATE TABLE `tb_momentos_presupuestales` (
  `ln_ur` varchar(10) NOT NULL DEFAULT '0' COMMENT 'Campo que guarda el identificador de unidad responsable',
  `ln_ue` varchar(10) NOT NULL DEFAULT '0' COMMENT 'Campo que guarda el identificador de unidad ejecutora',
  `ln_descripcion` varchar(50) NOT NULL DEFAULT '' COMMENT 'Campo que guarda la descripcion de la unidad ejecutora',
  `ln_presupuestalingreso` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  `ln_presupuestalingresoEjecutar` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  `ln_presupuestalingresoModificado` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  `ln_presupuestalingresoDevengado` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  `ln_presupuestalingresoRecaudado` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  `ln_presupuestalegreso` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  `ln_presupuestalegresoEjercer` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  `ln_presupuestalegresoModificado` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  `ln_presupuestalegresocomprometido` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  `ln_presupuestalegresodevengado` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  `ln_presupuestalegresoejercido` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  `ln_presupuestalegresopagado` varchar(100) DEFAULT NULL COMMENT 'Campo que guarda el momento contable de ingreso',
  PRIMARY KEY (`ln_ur`,`ln_ue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: desarrollo 3 24/10/2018
  Campo para unidad ejecutora en configuración de cuentas de bancos
*/
ALTER TABLE bankaccounts ADD ln_ue varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora';
ALTER TABLE bankaccounts ADD nu_activo int(11) DEFAULT NULL COMMENT '1 Activo - 0 Inactivo';

/*
  agrego: desarrollo 3 24/10/2018
  Campo para unidad ejecutora en configuración de cuentas de bancos
*/
ALTER TABLE tb_compromiso ADD nu_anio_fiscal int(11) DEFAULT NULL COMMENT 'Año fiscal de la captura';
ALTER TABLE tb_pagos ADD nu_anio_fiscal int(11) DEFAULT NULL COMMENT 'Año fiscal de la captura';
ALTER TABLE supptrans ADD nu_anio_fiscal int(11) DEFAULT NULL COMMENT 'Año fiscal de la captura';
ALTER TABLE banktrans ADD nu_anio_fiscal int(11) DEFAULT NULL COMMENT 'Año fiscal de la captura';
ALTER TABLE chartdetailsbudgetlog ADD nu_anio_fiscal int(11) DEFAULT NULL COMMENT 'Año fiscal de la captura';
ALTER TABLE tb_suficiencias ADD nu_anio_fiscal int(11) DEFAULT NULL COMMENT 'Año fiscal de la captura';
ALTER TABLE tb_rectificaciones ADD nu_anio_fiscal int(11) DEFAULT NULL COMMENT 'Año fiscal de la captura';

/*
  agregó desarrollo 4 31/10/2018
  Se agrega campo para indicar el año fiscal del oficio de comisión
 */
ALTER TABLE `tb_viaticos` ADD `nu_anio_fiscal` int(11) DEFAULT NULL COMMENT 'Año fiscal de la captura';

/*
  agregó desarrollo 4 31/10/2018
  Consulta para agregar el año fiscal del oficio de comisión en los registros que tengan valor NULL en ese campo
 */
UPDATE `tb_viaticos` SET `nu_anio_fiscal` = SUBSTR(`accountcode_general`,1,4) WHERE `nu_anio_fiscal` IS NULL;

/*
  agregó desarrollo 4 31/10/2018
  Se agrega campo para guardar el periodo en purchorders
 */
ALTER TABLE `purchorders` ADD `nu_periodo` smallint(6) NOT NULL DEFAULT '0' AFTER `fecha_modificacion`;

/*
  agrego: desarrollo 3 31/10/2018
  Campo para unidad ejecutora en configuración de cuentas de bancos
*/
ALTER TABLE chartdetailsbudgetlog add ln_ue_creadora varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora Creadora';

/*
  agrego: desarrollo 3 13/11/2018
  Campo para id de detalle de la poliza en tesoreria y identidicar registros de la matriz del devengado y pagado
*/
ALTER TABLE gltrans ADD nu_supptrans_detailid int(11) DEFAULT NULL COMMENT 'Id del detalle del pago en tesoreria';
ALTER TABLE gltrans ADD nu_devengado int(11) DEFAULT NULL COMMENT '1 - Si son registros de la matriz del devengado';
ALTER TABLE gltrans ADD nu_pagado int(11) DEFAULT NULL COMMENT '1 - Si son registros de la matriz del pagado';

/*
  agrego: desarrollo 3 13/11/2018
  Tipo de documento en historial de cheques
*/
ALTER TABLE tb_cheques_cr add nu_type smallint(6) NOT NULL DEFAULT '0' COMMENT 'Tipo de Operación del Movimiento';

/*
  agregó: desarrollo 4 13/11/2018
  Se agrega ABC General para Programas Extrapresupuestales
 */
INSERT INTO `tb_cat_panel_catalogo` (`id_nu_funcion`, `ln_configuracion`, `ln_tbl_cat`, `ln_precondicion`, `ln_mensaje`, `ln_campo_activo`, `ln_compuesto`, `ln_grid`, `ln_grid_col`, `ln_col_visual`, `ln_col_excel`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (
    2498, 
    '<div name="contenedorTabla" id="contenedorTabla"><div name="tablaGrid" id="tablaGrid"></div></div><div align="center" class="row" style="padding-bottom: 10px;"><component-button type="button" id="btnAgregar" name="btnAgregar" value="Nuevo" class="glyphicon glyphicon-plus"></component-button></div>', 
    'tb_cat_programa_extrapresupuestario', 
    'SELECT * FROM `tb_cat_convenio` WHERE `pp` LIKE "%s"', 
    '[ "error"=>"No puede eliminar el registro indicado", "store"=>"Se generó con éxito el registro", "update"=>"Se actualizó con éxito el registro", "destroy"=>"Se eliminó con éxito del registro"]',
    'activo',
    'SELECT DISTINCT `pe`, `descripcion`, `pe` AS identificador, "pe" AS multiidentificadorcampo, `pe` AS multiidentificadorvalor FROM `tb_cat_programa_extrapresupuestario` WHERE `activo` = 1 ORDER BY LENGTH(`pe`) ASC, `pe` ASC', 
    '[{ name: "pe", type: "string" },{ name: "descripcion", type: "string" },{ name: "modificar", type: "string" },{ name: "eliminar", type: "string" },{name:"identificador",type:"string"},{name:"multiidentificadorcampo",type:"string"},{name:"multiidentificadorvalor",type:"string"}]', 
    '[{ text: "PE", datafield: "pe", width: "6%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Descripción", datafield: "descripcion", width: "80%", cellsalign: "left", align: "center", hidden: false, editable:false }, { text: "Modificar", datafield: "modificar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Eliminar", datafield: "eliminar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }]', 
    '[0,1,2,3]', 
    '[0,1]', 
    1, 
    NOW()
  );

SET @c = "z";
SELECT IF(@c='z', @c:=`id_nu_panel_catalogo`, "z" ) FROM `tb_cat_panel_catalogo` WHERE `id_nu_funcion` = 2498;
INSERT INTO `tb_cat_panel_detalle` (`id_nu_panel_catalogo`, `id_nu_funcion`, `ln_etiqueta`, `ln_campo`, `ln_columna`, `ln_tipo`, `sn_longitud`, `ln_formato`, `ln_select`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (@c, 2498, 'PE', 'pe', 'pe', 'string', 0, '', '', 1, NOW()),
  (@c, 2498, 'Descripción', 'descripcion', 'descripcion', 'string', 0, '', '', 1, NOW()),
  (@c, 2498, '', 'identificador', 'pe', 'string', 0, '', '', 1, NOW()),
  (@c, 2498, '', 'multiidentificadorcampo', 'multiidentificadorcampo', 'string', 0, '', '', 1, NOW()),
  (@c, 2498, '', 'multiidentificadorvalor', 'multiidentificadorvalor', 'string', 0, '', '', 1, NOW());

/*
  agrego: desarrollo 3 03/12/2018
  Campo para id del grupo de la clasificación programatica
*/
ALTER TABLE clasprog ADD nu_id_grupo int(11) DEFAULT NULL COMMENT 'Id del grupo';

/*
  agregó: desarrollo 4 17/12/2018
  Se modifica tabla de ABC General para evitar error al insertar registros en ambiente QA y producción
 */
ALTER TABLE `tb_cat_panel_catalogo` CHANGE `ln_postmodificacion` `ln_postmodificacion` text COLLATE utf8_bin COMMENT 'Query que se debe ejecutar después de cualquier INSERT o UPDATE';

/*
  agregó: desarrollo 4 17/12/2018
  Se modifica tabla de Tipo de Convenio para darla de alta en el ABC General
 */

ALTER TABLE `tb_tipo_convenio` ADD `ind_activo` int(1) DEFAULT '1' COMMENT 'Identificador de la disponibilidad del registro';
UPDATE `sec_functions` SET `title` = 'ABC Tipo Convenio', `url` = 'AbcGeneral.php', `shortdescription` = 'ABC Tipo Convenio', `comments` = 'ABC Tipo Convenio' WHERE `functionid` = '2478';
UPDATE `sec_functions_new` SET `title` = 'ABC Tipo Convenio', `url` = 'AbcGeneral.php', `shortdescription` = 'ABC Tipo Convenio', `comments` = 'ABC Tipo Convenio' WHERE `functionid` = '2478';

/*
  agregó: desarrollo 4 17/12/2018
  Se agrega ABC General para Tipo de Convenio
 */

INSERT INTO `tb_cat_panel_catalogo` (`id_nu_funcion`, `ln_configuracion`, `ln_tbl_cat`, `ln_precondicion`, `ln_mensaje`, `ln_campo_activo`, `ln_compuesto`, `ln_grid`, `ln_grid_col`, `ln_col_visual`, `ln_col_excel`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (
    2478, 
    '<div name="contenedorTabla" id="contenedorTabla"><div name="tablaGrid" id="tablaGrid"></div></div><div align="center" class="row" style="padding-bottom: 10px;"><component-button type="button" id="btnAgregar" name="btnAgregar" value="Nuevo" class="glyphicon glyphicon-plus"></component-button></div>', 
    'tb_tipo_convenio', 
    'SELECT * FROM `tb_cat_convenio` WHERE `tipo_convenio` LIKE "%s"', 
    '[ "error"=>"No puede eliminar el registro indicado", "store"=>"Se generó con éxito el registro", "update"=>"Se actualizó con éxito el registro", "destroy"=>"Se eliminó con éxito del registro"]',
    'ind_activo',
    'SELECT DISTINCT `tipo_convenio`, `descripcion`, `tipo_convenio` AS identificador, "tipo_convenio" AS multiidentificadorcampo, `tipo_convenio` AS multiidentificadorvalor FROM `tb_tipo_convenio` WHERE `ind_activo` = 1 ORDER BY LENGTH(`tipo_convenio`) ASC, `tipo_convenio` ASC', 
    '[{ name: "tipo", type: "string" },{ name: "descripcion", type: "string" },{ name: "modificar", type: "string" },{ name: "eliminar", type: "string" },{ name: "identificador", type:"string" },{ name: "multiidentificadorcampo", type:"string" },{ name: "multiidentificadorvalor", type:"string" }]', 
    '[{ text: "Tipo", datafield: "tipo", width: "6%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Descripción", datafield: "descripcion", width: "80%", cellsalign: "left", align: "center", hidden: false, editable:false }, { text: "Modificar", datafield: "modificar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Eliminar", datafield: "eliminar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }]', 
    '[0,1,2,3]', 
    '[0,1]', 
    1, 
    NOW()
  );

SET @c = "z";
SELECT IF(@c='z', @c:=`id_nu_panel_catalogo`, "z" ) FROM `tb_cat_panel_catalogo` WHERE `id_nu_funcion` = '2478';
INSERT INTO `tb_cat_panel_detalle` (`id_nu_panel_catalogo`, `id_nu_funcion`, `ln_etiqueta`, `ln_campo`, `ln_columna`, `ln_tipo`, `sn_longitud`, `ln_formato`, `ln_select`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (@c, 2478, 'Tipo', 'tipo', 'tipo_convenio', 'string', 0, '', '', 1, NOW()),
  (@c, 2478, 'Descripción', 'descripcion', 'descripcion', 'string', 0, '', '', 1, NOW()),
  (@c, 2478, '', 'identificador', 'tipo_convenio', 'string', 0, '', '', 1, NOW()),
  (@c, 2478, '', 'multiidentificadorcampo', 'multiidentificadorcampo', 'string', 0, '', '', 1, NOW()),
  (@c, 2478, '', 'multiidentificadorvalor', 'multiidentificadorvalor', 'string', 0, '', '', 1, NOW());

/*
  agregó: desarrollo 4 17/12/2018
  Se modifica tabla de Componente Presupuestal para darla de alta en el ABC General
 */

ALTER TABLE `tb_componente_presupuestal` ADD `ind_activo` int(1) DEFAULT '1' COMMENT 'Identificador de la disponibilidad del registro';
UPDATE `sec_functions` SET `title` = 'ABC Componente Presupuestal', `url` = 'AbcGeneral.php', `shortdescription` = 'ABC Componente Presupuestal', `comments` = 'ABC Componente Presupuestal' WHERE `functionid` = '2479';
UPDATE `sec_functions_new` SET `title` = 'ABC Componente Presupuestal', `url` = 'AbcGeneral.php', `shortdescription` = 'ABC Componente Presupuestal', `comments` = 'ABC Componente Presupuestal' WHERE `functionid` = '2479';

/*
  agregó: desarrollo 4 17/12/2018
  Se agrega ABC General para Componente Presupuestal
 */

INSERT INTO `tb_cat_panel_catalogo` (`id_nu_funcion`, `ln_configuracion`, `ln_tbl_cat`, `ln_precondicion`, `ln_mensaje`, `ln_campo_activo`, `ln_compuesto`, `ln_grid`, `ln_grid_col`, `ln_col_visual`, `ln_col_excel`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (
    2479, 
    '<div name="contenedorTabla" id="contenedorTabla"><div name="tablaGrid" id="tablaGrid"></div></div><div align="center" class="row" style="padding-bottom: 10px;"><component-button type="button" id="btnAgregar" name="btnAgregar" value="Nuevo" class="glyphicon glyphicon-plus"></component-button></div>', 
    'tb_componente_presupuestal', 
    'SELECT * FROM `tb_cat_convenio` WHERE `cp` LIKE "%s"', 
    '[ "error"=>"No puede eliminar el registro indicado", "store"=>"Se generó con éxito el registro", "update"=>"Se actualizó con éxito el registro", "destroy"=>"Se eliminó con éxito del registro"]',
    'ind_activo',
    'SELECT DISTINCT `cp`, `descripcion`, `cp` AS identificador, "cp" AS multiidentificadorcampo, `cp` AS multiidentificadorvalor FROM `tb_componente_presupuestal` WHERE `ind_activo` = 1 ORDER BY LENGTH(`cp`) ASC, `cp` ASC', 
    '[{ name: "cp", type: "string" },{ name: "descripcion", type: "string" },{ name: "modificar", type: "string" },{ name: "eliminar", type: "string" },{ name: "identificador", type:"string" },{ name: "multiidentificadorcampo", type:"string" },{ name: "multiidentificadorvalor", type:"string" }]', 
    '[{ text: "CP", datafield: "cp", width: "6%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Descripción", datafield: "descripcion", width: "80%", cellsalign: "left", align: "center", hidden: false, editable:false }, { text: "Modificar", datafield: "modificar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }, { text: "Eliminar", datafield: "eliminar", width: "7%", cellsalign: "center", align: "center", hidden: false, editable:false }]', 
    '[0,1,2,3]', 
    '[0,1]', 
    1, 
    NOW()
  );

SET @c = "z";
SELECT IF(@c='z', @c:=`id_nu_panel_catalogo`, "z" ) FROM `tb_cat_panel_catalogo` WHERE `id_nu_funcion` = '2479';
INSERT INTO `tb_cat_panel_detalle` (`id_nu_panel_catalogo`, `id_nu_funcion`, `ln_etiqueta`, `ln_campo`, `ln_columna`, `ln_tipo`, `sn_longitud`, `ln_formato`, `ln_select`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  (@c, 2479, 'CP', 'cp', 'cp', 'string', 0, '', '', 1, NOW()),
  (@c, 2479, 'Descripción', 'descripcion', 'descripcion', 'string', 0, '', '', 1, NOW()),
  (@c, 2479, '', 'identificador', 'cp', 'string', 0, '', '', 1, NOW()),
  (@c, 2479, '', 'multiidentificadorcampo', 'multiidentificadorcampo', 'string', 0, '', '', 1, NOW()),
  (@c, 2479, '', 'multiidentificadorvalor', 'multiidentificadorvalor', 'string', 0, '', '', 1, NOW());






