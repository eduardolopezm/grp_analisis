/*
  agrego: Jesus Santos 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesorders
*/
CREATE TABLE `salesorders` (
  `orderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob,
  `orddate` date NOT NULL DEFAULT '0000-00-00',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT '0',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT '1',
  `freightcost` double NOT NULL DEFAULT '0',
  `fromstkloc` varchar(50) NOT NULL,
  `deliverydate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `quotedate` date NOT NULL DEFAULT '0000-00-00',
  `confirmeddate` date NOT NULL DEFAULT '0000-00-00',
  `printedpackingslip` tinyint(4) NOT NULL DEFAULT '0',
  `datepackingslipprinted` date NOT NULL DEFAULT '0000-00-00',
  `quotation` tinyint(4) NOT NULL DEFAULT '0',
  `placa` varchar(50) DEFAULT '''''',
  `serie` varchar(50) DEFAULT '''''',
  `kilometraje` double DEFAULT '0',
  `salesman` varchar(200) DEFAULT '''''',
  `tagref` int(11) unsigned DEFAULT '0',
  `taxtotal` double DEFAULT '0',
  `totaltaxret` double DEFAULT '0',
  `currcode` varchar(45) DEFAULT NULL,
  `paytermsindicator` varchar(45) DEFAULT '''''',
  `contract_type` int(11) DEFAULT '0' COMMENT 'Tipo de contrato',
  `advance` double DEFAULT '0',
  `UserRegister` varchar(255) DEFAULT '''',
  `typeorder` int(11) DEFAULT '0' COMMENT 'Tipo de pedido de venta 0 venta normal 1 contrato',
  `refundpercentsale` double DEFAULT '0' COMMENT 'Porcentaje de Devolucion de Venta',
  `vehicleno` int(11) DEFAULT '0',
  `idtarea` int(11) DEFAULT '0',
  `contid` int(11) DEFAULT NULL,
  `codigobarras` varchar(255) DEFAULT NULL,
  `idprospect` int(11) DEFAULT NULL,
  `nopedido` varchar(255) DEFAULT NULL,
  `noentrada` varchar(255) DEFAULT NULL,
  `extratext` varchar(255) DEFAULT NULL,
  `noremision` varchar(255) DEFAULT NULL,
  `totalrefundpercentsale` double DEFAULT '0',
  `puestaenmarcha` varchar(500) DEFAULT '',
  `paymentname` varchar(255) DEFAULT NULL,
  `nocuenta` varchar(255) DEFAULT NULL,
  `deliverytext` varchar(255) DEFAULT NULL,
  `estatusprocesing` int(11) DEFAULT '0',
  `serviceorder` varchar(100) DEFAULT NULL,
  `usetype` int(11) DEFAULT NULL,
  `statuscancel` tinyint(1) DEFAULT '0' COMMENT 'Estada para indicar si la factura esta en proceso de cancelacion',
  `fromcr` int(1) DEFAULT NULL COMMENT 'Identifica cuando un pedido de venta/cotizacion fue creada desde el cotizado rápido (cr)',
  `ordenprioridad` int(11) DEFAULT '0',
  `discountcard` varchar(75) DEFAULT NULL,
  `payreference` varchar(50) DEFAULT NULL,
  `app_cotizador` smallint(6) DEFAULT '0' COMMENT '1= generado desde app cotizadorç',
  PRIMARY KEY (`orderno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `OrdDate` (`orddate`),
  KEY `OrderType` (`ordertype`),
  KEY `LocationIndex` (`fromstkloc`),
  KEY `BranchCode` (`branchcode`,`debtorno`),
  KEY `ShipVia` (`shipvia`),
  KEY `quotation` (`quotation`),
  KEY `curcode` (`currcode`),
  KEY `fromstkloc` (`fromstkloc`),
  KEY `orderno` (`orderno`),
  KEY `tagref` (`tagref`),
  KEY `idprospect` (`idprospect`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Santos 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesstatus
*/
CREATE TABLE `salesstatus` (
  `statusid` int(11) NOT NULL,
  `statusname` varchar(255) NOT NULL,
  `namebutton` varchar(255) DEFAULT NULL,
  `percentfunnel` double NOT NULL,
  `bgcolor` varchar(20) NOT NULL,
  `functionid` int(11) DEFAULT NULL,
  `showfunctionid` int(11) DEFAULT NULL COMMENT 'Permiso Asociado a estatus',
  `openfunctionid` int(11) DEFAULT NULL COMMENT 'Permiso de apertura de pedido',
  `cancelfunctionid` int(11) DEFAULT NULL,
  `cancelextrafunctionid` int(11) DEFAULT NULL,
  `nextstatus` varchar(50) DEFAULT NULL COMMENT 'Siguientes Status',
  `invoice` int(11) DEFAULT NULL COMMENT 'Genera CXC',
  `flagrfc` int(11) NOT NULL,
  `flagopen` int(11) DEFAULT NULL COMMENT 'Bandera de apertura',
  `flagcancel` int(11) DEFAULT NULL,
  `flagwo` int(11) DEFAULT NULL,
  `flagcredit` int(11) DEFAULT '0' COMMENT 'Validacion de limite de credito',
  `flagdocument` int(11) DEFAULT '0' COMMENT 'Bandera de validacion para documentos vencidos',
  `flagprovision` int(11) DEFAULT '0' COMMENT 'Movimiento contable a provision de ingresos',
  `flagelectronic` int(11) DEFAULT NULL COMMENT 'Bandera para generar sello y/o timbre fiscal',
  `ordenby` int(11) DEFAULT NULL,
  `typedocument` int(11) DEFAULT NULL,
  `templateid` int(11) DEFAULT NULL,
  `templateidadvance` int(11) DEFAULT NULL,
  `flaginicial` int(11) DEFAULT NULL,
  `flagdesplegar` int(11) DEFAULT NULL,
  `flagdateinv` int(11) DEFAULT '0' COMMENT 'valor de status para pedidos, 1 para facturas,2 para remisiones',
  `logo` varchar(255) DEFAULT NULL,
  `flagnegativestock` int(11) DEFAULT '0',
  `flagstock` int(11) DEFAULT '0' COMMENT 'Valida inventarios en rojo',
  `flaganticipo` int(11) DEFAULT '0' COMMENT 'Valida cuando se genere un pedido remisionado no contenga un ainticipo',
  `flagventaperdida` int(11) DEFAULT '0' COMMENT 'Valida la venta de perdida',
  `flagcancelwo` int(11) DEFAULT '0',
  `flagfactintcosto` int(11) DEFAULT '0' COMMENT 'Valida que agregue el costo de la factura interna',
  `flagvalexistanticipo` int(11) DEFAULT '0',
  `flagvalexistalmacen` int(11) DEFAULT '0' COMMENT 'Valida en almacen por default existencia de producto',
  `flagvalidarazonsocialdiferente` int(11) DEFAULT '0' COMMENT 'Valida si la razon social es diferente de los almacenes seleccionados',
  `flagcarteracredito` int(11) DEFAULT '0' COMMENT 'Valida si tiene mas de 90 dias docmentos vencidos y si tiene mas 6 meses inactividad',
  `flagordencompra` int(11) DEFAULT '0',
  `flagrepeco` int(1) DEFAULT '0',
  `flagnegativserial` int(11) DEFAULT '0',
  `flagdespegar` int(11) DEFAULT NULL,
  `flagcancelawo` int(11) DEFAULT NULL,
  `centroservicio` int(11) DEFAULT NULL COMMENT 'Bandera Centor de Servicio 1982',
  PRIMARY KEY (`statusid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Santos 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesfielddate
*/

CREATE TABLE `salesfielddate` (
  `salesfield` varchar(50) DEFAULT NULL,
  `statusid` int(11) DEFAULT NULL,
  `flagdate` int(11) DEFAULT NULL,
  `flagupdate` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Santos 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesdate
*/

CREATE TABLE `salesdate` (
  `orderno` int(11) DEFAULT NULL,
  `fecha_solicitud` datetime DEFAULT NULL,
  `usersolicitud` varchar(50) DEFAULT NULL,
  `fecha_solicitudmod` datetime DEFAULT NULL,
  `usersolicitudmod` varchar(50) DEFAULT NULL,
  `fecha_cotizacion` datetime DEFAULT NULL,
  `usercotizacion` varchar(50) DEFAULT NULL,
  `fecha_cotizacionmod` datetime DEFAULT NULL,
  `usercotizacionmod` varchar(50) DEFAULT NULL,
  `fecha_abierto` datetime DEFAULT NULL,
  `userabierto` varchar(50) DEFAULT NULL,
  `fecha_abiertomod` datetime DEFAULT NULL,
  `userabiertomod` varchar(50) DEFAULT NULL,
  `fecha_cerrado` datetime DEFAULT NULL,
  `usercerrado` varchar(50) DEFAULT NULL,
  `fecha_cerradomod` datetime DEFAULT NULL,
  `usercerradomod` varchar(50) DEFAULT NULL,
  `fecha_cancelado` datetime DEFAULT NULL,
  `usercancelado` varchar(50) DEFAULT NULL,
  `fecha_canceladomod` datetime DEFAULT NULL,
  `usercanceladomod` varchar(50) DEFAULT NULL,
  `fecha_facturado` datetime DEFAULT NULL,
  `userfacturado` varchar(50) DEFAULT NULL,
  `fecha_facturadomod` datetime DEFAULT NULL,
  `fecha_remisionado` datetime DEFAULT NULL,
  `userremisionado` varchar(50) DEFAULT NULL,
  `fecha_remisionadomod` datetime DEFAULT NULL,
  `autoincrement` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
 agrego: Jesus Santos 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla log_cancelacion_sustitucion
*/

CREATE TABLE `log_cancelacion_sustitucion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transNo` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `rfcEmisor` varchar(13) DEFAULT NULL,
  `fechaEmision` datetime DEFAULT NULL,
  `UUID` varchar(50) DEFAULT NULL,
  `folio` varchar(15) DEFAULT NULL,
  `xmlSat` mediumtext,
  `xmlImpresion` mediumtext,
  `fechatimbrado` datetime DEFAULT NULL,
  `cadenatimbre` varchar(1000) DEFAULT NULL,
  `fiscal` int(1) DEFAULT '0',
  `codesat` varchar(15) DEFAULT NULL,
  `paymentname` varchar(15) DEFAULT NULL,
  `c_paymentid` varchar(15) DEFAULT NULL,
  `fechamov` datetime DEFAULT NULL,
  `c_UsoCFDI` varchar(10) DEFAULT NULL,
  `termsindicator` varchar(45) DEFAULT NULL,
  `userid` varchar(30) DEFAULT NULL,
  `estatus` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index2` (`transNo`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Santos 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesorderdetails
*/

CREATE TABLE `salesorderdetails` (
  `orderlineno` int(11) NOT NULL DEFAULT '0',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `stkcode` varchar(255) NOT NULL,
  `fromstkloc` varchar(50) NOT NULL,
  `qtyinvoiced` double DEFAULT '0',
  `unitprice` double NOT NULL DEFAULT '0',
  `quantity` float NOT NULL DEFAULT '0',
  `alto` double DEFAULT '0' COMMENT 'Alto del item',
  `ancho` double DEFAULT '0' COMMENT 'Largo del item',
  `calculatepricebysize` tinyint(1) DEFAULT '0' COMMENT 'Bandera para el calculo del precio usando las medidas',
  `largo` double DEFAULT '0' COMMENT 'Ancho del item',
  `quantitydispatched` float DEFAULT '0' COMMENT 'Cantidad Ordenada al inicio',
  `ADevengar` double DEFAULT '0' COMMENT 'Monto a Devengar',
  `Facturado` double DEFAULT '0' COMMENT 'Monto Facturado',
  `Devengado` double DEFAULT '0' COMMENT 'Monto Devengado',
  `XFacturar` double DEFAULT '0' COMMENT 'Monto X Facturar',
  `AFacturar` double DEFAULT '0' COMMENT 'Monto a Facturar',
  `XDevengar` double DEFAULT '0' COMMENT 'Monto X Devengar',
  `nummes` int(11) NOT NULL DEFAULT '0',
  `refundpercent` double DEFAULT '0' COMMENT 'Porcentaje de Devolucion',
  `saletype` int(10) unsigned DEFAULT '0' COMMENT 'Tipo de venta, 1 si es factura de 1 pedido y muchas facturas',
  `estimate` tinyint(4) NOT NULL DEFAULT '0',
  `discountpercent` double NOT NULL DEFAULT '0',
  `discountpercent1` double DEFAULT '0' COMMENT 'descuento2',
  `discountpercent2` double DEFAULT '0' COMMENT 'descuento3',
  `actualdispatchdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `narrative` text,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  `warranty` int(11) DEFAULT '0' COMMENT 'Garantia',
  `salestype` varchar(45) DEFAULT '' COMMENT 'Tipo de lista de precios',
  `servicestatus` int(11) DEFAULT '0' COMMENT 'Estatus del servicio',
  `pocost` double DEFAULT NULL COMMENT 'Costo desde orden de compra',
  `idtarea` int(10) unsigned DEFAULT '0' COMMENT 'Tarea de produccion',
  `cashdiscount` double DEFAULT '0',
  `showdescrip` varchar(100) DEFAULT NULL,
  `readOnlyValues` tinyint(4) DEFAULT '0',
  `modifiedpriceanddiscount` tinyint(4) DEFAULT '0',
  `totalrefundpercent` double DEFAULT '0',
  `qtylost` float DEFAULT NULL,
  `datelost` datetime DEFAULT NULL,
  `woline` int(11) DEFAULT NULL,
  `stkmovid` int(11) DEFAULT '0' COMMENT 'codigo de anticipo asociado a factura de anticipo',
  `userlost` varchar(20) DEFAULT NULL,
  `localidad` varchar(255) DEFAULT NULL,
  `stockidKIT` varchar(25) DEFAULT NULL COMMENT 'Para KIT',
  `anticipo` double DEFAULT NULL,
  `numPredial` varchar(150) DEFAULT '' COMMENT 'Campo para el nodo cuenta predial del xml',
  PRIMARY KEY (`orderlineno`,`orderno`),
  KEY `OrderNo` (`orderno`),
  KEY `StkCode` (`stkcode`),
  KEY `Completed` (`completed`),
  KEY `woline` (`woline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Santos 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/

CREATE TABLE `salesman` (
  `salesmancode` char(6) NOT NULL DEFAULT '',
  `salesmanname` char(30) NOT NULL DEFAULT '',
  `smantel` char(20) NOT NULL DEFAULT '',
  `smanfax` char(20) NOT NULL DEFAULT '',
  `commissionrate1` double NOT NULL DEFAULT '0',
  `breakpoint` decimal(10,0) NOT NULL DEFAULT '0',
  `commissionrate2` double NOT NULL DEFAULT '0',
  `glaccountsalesprov` varchar(45) DEFAULT NULL COMMENT 'Cuenta de comisiones de vendedores provisionadas',
  `glaccountsales` varchar(45) DEFAULT NULL COMMENT 'Cuenta de comisiones de vendedores',
  `area` char(10) NOT NULL DEFAULT '0',
  `type` int(4) NOT NULL DEFAULT '1',
  `usersales` varchar(255) DEFAULT NULL COMMENT 'usuario de vendedor',
  `status` varchar(255) DEFAULT 'Active',
  `planid` int(10) unsigned DEFAULT NULL COMMENT 'plan de comisiones',
  `commissionrate3` float DEFAULT NULL,
  `commissionrate4` float DEFAULT NULL,
  `expenses` float DEFAULT NULL,
  `riesgo` float DEFAULT NULL,
  PRIMARY KEY (`salesmancode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Santos 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla debtorsmaster
*/

CREATE TABLE `debtorsmaster` (
  `debtorno` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `name1` varchar(255) DEFAULT NULL,
  `name2` varchar(40) DEFAULT NULL,
  `name3` varchar(40) DEFAULT NULL,
  `curp` varchar(255) DEFAULT '',
  `address1` varchar(150) NOT NULL DEFAULT '',
  `address2` varchar(50) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `currcode` char(3) NOT NULL DEFAULT '',
  `salestype` char(2) NOT NULL DEFAULT '',
  `clientsince` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `holdreason` smallint(6) NOT NULL DEFAULT '0',
  `paymentterms` char(2) NOT NULL DEFAULT 'f',
  `discount` double NOT NULL DEFAULT '0',
  `pymtdiscount` double NOT NULL DEFAULT '0',
  `lastpaid` double NOT NULL DEFAULT '0',
  `lastpaiddate` datetime DEFAULT NULL,
  `creditlimit` double NOT NULL DEFAULT '1000',
  `invaddrbranch` tinyint(4) NOT NULL DEFAULT '0',
  `discountcode` char(2) NOT NULL DEFAULT '',
  `ediinvoices` tinyint(4) NOT NULL DEFAULT '0',
  `ediorders` tinyint(4) NOT NULL DEFAULT '0',
  `edireference` varchar(20) NOT NULL DEFAULT '',
  `editransport` varchar(5) NOT NULL DEFAULT 'email',
  `ediaddress` varchar(50) NOT NULL DEFAULT '',
  `ediserveruser` varchar(20) NOT NULL DEFAULT '',
  `ediserverpwd` varchar(20) NOT NULL DEFAULT '',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `customerpoline` tinyint(1) NOT NULL DEFAULT '0',
  `typeid` tinyint(4) NOT NULL DEFAULT '1',
  `daygrace` char(2) NOT NULL,
  `coments` varchar(500) DEFAULT NULL,
  `blacklist` int(2) DEFAULT '0',
  `ruta` int(11) DEFAULT '0',
  `nameextra` varchar(255) DEFAULT '' COMMENT 'nombre comercial de cliente',
  `fechanacimiento` varchar(15) DEFAULT NULL,
  `lugarnacimiento` varchar(50) DEFAULT NULL,
  `telefonocelular` varchar(15) DEFAULT NULL,
  `ingresosmensuales` int(11) DEFAULT NULL,
  `estadocivil` varchar(50) DEFAULT NULL,
  `mediocontacto` varchar(255) DEFAULT NULL,
  `NacionalidadId` int(11) DEFAULT NULL,
  `CapacidadCompraId` varchar(10) DEFAULT NULL,
  `razoncompra` varchar(255) DEFAULT NULL,
  `pagpersonal` varchar(150) DEFAULT NULL,
  `idCapComIngresos` int(11) DEFAULT NULL,
  `companyprospect` varchar(255) DEFAULT NULL,
  `prospectsince` datetime DEFAULT NULL,
  `userprospect` varchar(20) DEFAULT NULL,
  `usoCFDI` varchar(11) DEFAULT NULL,
  `NumRegIdTrib` varchar(40) DEFAULT NULL,
  `RIF` tinyint(1) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `app` smallint(6) DEFAULT '0' COMMENT '0 = ingreso opr el erp, 1 = ingreso desde la app cotizacion',
  PRIMARY KEY (`debtorno`),
  KEY `Currency` (`currcode`),
  KEY `HoldReason` (`holdreason`),
  KEY `Name` (`name`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SalesType` (`salestype`),
  KEY `EDIInvoices` (`ediinvoices`),
  KEY `EDIOrders` (`ediorders`),
  KEY `debtorsmaster_ibfk_5` (`typeid`),
  KEY `debtorno` (`debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Santos 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla custbranch
*/

CREATE TABLE `custbranch` (
  `branchcode` varchar(20) NOT NULL DEFAULT '',
  `debtorno` varchar(20) NOT NULL DEFAULT '',
  `brname` varchar(80) NOT NULL DEFAULT '',
  `taxid` varchar(15) NOT NULL DEFAULT '',
  `braddress1` varchar(100) NOT NULL DEFAULT '',
  `braddress2` varchar(60) NOT NULL DEFAULT '',
  `braddress3` varchar(60) NOT NULL DEFAULT '',
  `braddress4` varchar(50) NOT NULL DEFAULT '',
  `braddress5` varchar(20) NOT NULL DEFAULT '',
  `braddress6` varchar(100) DEFAULT NULL,
  `lat` float(10,6) NOT NULL DEFAULT '0.000000',
  `lng` float(10,6) NOT NULL DEFAULT '0.000000',
  `estdeliverydays` smallint(6) NOT NULL DEFAULT '1',
  `area` char(3) NOT NULL,
  `salesman` varchar(6) NOT NULL DEFAULT '',
  `fwddate` smallint(6) NOT NULL DEFAULT '0',
  `phoneno` varchar(20) NOT NULL DEFAULT '',
  `faxno` varchar(20) NOT NULL DEFAULT '',
  `contactname` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `lineofbusiness` varchar(255) DEFAULT '' COMMENT 'giro del negocio',
  `flagworkshop` int(11) DEFAULT '0' COMMENT 'Cuenta con taller propio',
  `defaultlocation` varchar(30) NOT NULL DEFAULT '',
  `taxgroupid` tinyint(4) NOT NULL DEFAULT '1',
  `defaultshipvia` int(11) NOT NULL DEFAULT '1',
  `deliverblind` tinyint(1) DEFAULT '1',
  `disabletrans` tinyint(4) NOT NULL DEFAULT '0',
  `brpostaddr1` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr2` varchar(50) NOT NULL DEFAULT '',
  `brpostaddr3` varchar(30) NOT NULL DEFAULT '',
  `brpostaddr4` varchar(20) NOT NULL DEFAULT '',
  `brpostaddr5` varchar(20) NOT NULL DEFAULT '',
  `brpostaddr6` varchar(15) NOT NULL DEFAULT '',
  `specialinstructions` text NOT NULL,
  `custbranchcode` varchar(30) NOT NULL DEFAULT '',
  `creditlimit` double NOT NULL DEFAULT '1000',
  `custdata1` int(4) NOT NULL DEFAULT '0',
  `custdata2` int(4) NOT NULL DEFAULT '0',
  `custdata3` int(4) NOT NULL DEFAULT '0',
  `custdata4` int(4) NOT NULL DEFAULT '0',
  `custdata5` int(4) NOT NULL DEFAULT '0',
  `custdata6` int(4) NOT NULL DEFAULT '0',
  `ruta` int(11) DEFAULT '0',
  `paymentname` varchar(100) DEFAULT NULL,
  `nocuenta` varchar(20) DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT '1900-01-01 00:00:00',
  `namebank` varchar(255) DEFAULT NULL,
  `brnumint` varchar(100) DEFAULT NULL,
  `brnumext` varchar(100) DEFAULT NULL,
  `movilno` varchar(100) DEFAULT NULL,
  `nextelno` varchar(100) DEFAULT NULL,
  `logocliente` varchar(255) DEFAULT NULL,
  `descclientecomercial` double(10,2) DEFAULT '0.00',
  `descclientepropago` double(10,2) DEFAULT '0.00',
  `welcomemail` varchar(1) DEFAULT '0',
  `custpais` varchar(50) DEFAULT NULL,
  `SectComClId` int(4) DEFAULT NULL,
  `NumeAsigCliente` int(11) DEFAULT NULL,
  `descclienteop` float DEFAULT NULL,
  `typeaddenda` int(11) DEFAULT '0',
  `idprospecmedcontacto` varchar(255) DEFAULT NULL,
  `idproyecto` int(4) DEFAULT NULL,
  `braddress7` varchar(100) DEFAULT NULL,
  `DiasRevicion` int(11) DEFAULT NULL,
  `DiasPago` int(11) DEFAULT NULL,
  `prefer` int(11) unsigned DEFAULT '0',
  `discountcard` varchar(75) DEFAULT NULL,
  `typecomplement` int(11) DEFAULT '0',
  PRIMARY KEY (`branchcode`,`debtorno`),
  KEY `BrName` (`brname`),
  KEY `DebtorNo` (`debtorno`),
  KEY `Salesman` (`salesman`),
  KEY `Area` (`area`),
  KEY `DefaultLocation` (`defaultlocation`),
  KEY `DefaultShipVia` (`defaultshipvia`),
  KEY `taxgroupid` (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Santos 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla valueaddenda
*/

CREATE TABLE `valueaddenda` (
  `debtorid` int(11) DEFAULT NULL,
  `fieldid` int(11) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  KEY `debtorid` (`debtorid`),
  KEY `fieldid` (`fieldid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: Nombre 04/11/2019
  proceso: Busqueda De Recibos de Pago
  Se crea tabla custallocns
*/

CREATE TABLE `custallocns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amt` double NOT NULL DEFAULT '0',
  `datealloc` date NOT NULL DEFAULT '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL DEFAULT '0',
  `rate_from` double DEFAULT NULL,
  `currcode_from` varchar(5) DEFAULT NULL,
  `transid_allocto` int(11) NOT NULL DEFAULT '0',
  `rate_to` double DEFAULT NULL,
  `currcode_to` varchar(5) DEFAULT NULL,
  `ratealloc` double DEFAULT NULL,
  `diffonexch_alloc` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `DateAlloc` (`datealloc`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`)
) ENGINE=InnoDB AUTO_INCREMENT=405416 DEFAULT CHARSET=latin1;

/*
  agrego: Jesus Santos 04/11/2019
 proceso: Busqueda De Recibos de Pago
  Se crea tabla usrcortecaja
*/

CREATE TABLE `usrcortecaja` (
  `u_cortecaja` int(11) NOT NULL AUTO_INCREMENT,
  `fechacorte` datetime DEFAULT NULL,
  `u_status` int(4) DEFAULT NULL,
  `tag` int(11) DEFAULT NULL,
  `userid` varchar(20) DEFAULT NULL,
  `noprocess` int(10) unsigned NOT NULL DEFAULT '1',
  `trandate` datetime DEFAULT NULL,
  PRIMARY KEY (`u_cortecaja`),
  KEY `fechacorte` (`fechacorte`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM AUTO_INCREMENT=6342 DEFAULT CHARSET=latin1;

/*
  agrego: Jesus Santos 5/11/2019
  proceso: Unidades de Medida
  Se crea tabla unitsofmeasure
*/

CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `unitname` varchar(15) NOT NULL DEFAULT '',
  `unitdecimal` int(11) DEFAULT '0',
  `mbflag` char(1) DEFAULT NULL,
  `flagdefault` int(11) DEFAULT NULL,
  `c_ClaveUnidad` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`unitid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Santos 05/11/2019
  proceso: Unidades de Medida
  Se crea tabla sat_unitsofmeasure
*/
CREATE TABLE `sat_unitsofmeasure` (
  `c_ClaveUnidad` varchar(50) NOT NULL,
  `Nombre` varchar(100) NOT NULL DEFAULT '',
  `Descripcion` varchar(500) DEFAULT NULL,
  `VigenciaInicio` date DEFAULT NULL,
  `VigenciaTermino` date DEFAULT NULL,
  `Simbolo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`c_ClaveUnidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: Eduardo López 05/11/2019
  proceso: Punto de Venta
  Se crea tabla salesversionespuntoventa para las versiones del punto de venta
*/
CREATE TABLE `salesversionespuntoventa` (
  `id` varchar(5) NOT NULL,
  `version` varchar(30) NOT NULL DEFAULT '',
  `active` tinyint(4) DEFAULT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `versionFactura` varchar(5) DEFAULT NULL COMMENT 'Versión del CFDI para Facturar',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: Eduardo López 05/11/2019
  proceso: Facturación
  Se agregan tablas para el proceso de facturación
*/
CREATE TABLE `sat_usocfdi` (
  `c_UsoCFDI` varchar(5) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `fisica` int(11) DEFAULT NULL,
  `moral` int(11) DEFAULT NULL,
  `VigenciaInicio` date DEFAULT NULL,
  `VigenciaTermino` date DEFAULT NULL,
  `invoiceuse` int(11) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`c_UsoCFDI`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sat_unitsofmeasure` (
  `c_ClaveUnidad` varchar(50) NOT NULL,
  `Nombre` varchar(100) NOT NULL DEFAULT '',
  `Descripcion` varchar(500) DEFAULT NULL,
  `VigenciaInicio` date DEFAULT NULL,
  `VigenciaTermino` date DEFAULT NULL,
  `Simbolo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`c_ClaveUnidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sat_tiposrelacion` (
  `c_TipoRelacion` varchar(5) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`c_TipoRelacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sat_tiposcomprobante` (
  `c_TipoDeComprobante` varchar(5) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `valormaximo` int(11) DEFAULT NULL,
  `VigenciaInicio` date DEFAULT NULL,
  `VigenciaTermino` date DEFAULT NULL,
  `invoiceuse` int(11) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  `receiptuse` int(11) DEFAULT NULL COMMENT 'Alta de recibos de pago',
  `creditnoteuse` smallint(150) DEFAULT '0' COMMENT 'Uso en Notas de Credito',
  PRIMARY KEY (`c_TipoDeComprobante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sat_tasas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(200) DEFAULT NULL,
  `minimo` double DEFAULT NULL,
  `maximo` double DEFAULT NULL,
  `c_Impuesto` varchar(5) DEFAULT NULL,
  `c_TipoFactor` varchar(25) DEFAULT NULL,
  `retencion` tinyint(4) DEFAULT NULL,
  `traslado` tinyint(4) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

CREATE TABLE `sat_regimenfiscal` (
  `c_RegimenFiscal` varchar(5) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `Fisica` int(11) DEFAULT NULL,
  `Moral` int(11) DEFAULT NULL,
  `VigenciaInicio` date DEFAULT NULL,
  `VigenciaTermino` date DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`c_RegimenFiscal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sat_regimen` (
  `c_Regimen` varchar(5) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`c_Regimen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sat_paymentmethodssat` (
  `paymentid` varchar(5) NOT NULL,
  `paymentname` varchar(50) DEFAULT NULL COMMENT 'Descripcion',
  `invoiceuse` tinyint(4) DEFAULT NULL COMMENT '1 para que salga en pedido de venta',
  `active` tinyint(4) DEFAULT NULL,
  `complementoPago` int(11) DEFAULT NULL COMMENT 'Complemento de Pago (Parcialidades)',
  `pv_active` int(11) DEFAULT NULL COMMENT 'Configuración para el Punto de Venta',
  PRIMARY KEY (`paymentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sat_paises` (
  `c_Pais` varchar(5) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `agrupaciones` varchar(200) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`c_Pais`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sat_incoterm` (
  `c_incoterm` varchar(3) NOT NULL,
  `description` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`c_incoterm`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `sat_impuestos` (
  `c_Impuesto` varchar(5) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `retencion` tinyint(4) DEFAULT NULL,
  `traslado` tinyint(4) DEFAULT NULL,
  `tipo` varchar(200) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`c_Impuesto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sat_fraccionArancelaria` (
  `c_fraccionArancelaria` varchar(20) DEFAULT NULL,
  `descripcion` longtext,
  `unidad_medida` varchar(10) DEFAULT NULL,
  `impuesto_imp` varchar(1) DEFAULT NULL,
  `impuesto_exp` varchar(10) DEFAULT NULL,
  `registroid` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`registroid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `sat_factor` (
  `c_TipoFactor` varchar(25) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`c_TipoFactor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sat_entidades` (
  `registroID` varchar(2) NOT NULL DEFAULT '',
  `entidad` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`registroID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `sat_cadenapago` (
  `c_TipoCadena` varchar(5) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`c_TipoCadena`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sat_stock` (
  `c_ClaveProdServ` varchar(50) NOT NULL,
  `Descripcion` varchar(500) NOT NULL DEFAULT '',
  `VigenciaInicio` date DEFAULT NULL,
  `VigenciaTermino` date DEFAULT NULL,
  `Iva` varchar(50) DEFAULT NULL,
  `Ieps` varchar(50) DEFAULT NULL,
  `Complemento` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`c_ClaveProdServ`),
  FULLTEXT KEY `Descripcion` (`Descripcion`),
  FULLTEXT KEY `Descripcion_2` (`Descripcion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Santos  5/11/2019
  proceso: Unidades de Medida
  Se crea tabla contracts
*/

CREATE TABLE `contracts` (
  `contractref` varchar(20) NOT NULL DEFAULT '',
  `contractdescription` varchar(50) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `status` varchar(10) NOT NULL DEFAULT 'Quotation',
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `quotedpricefx` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `margin` double NOT NULL DEFAULT '1',
  `woref` varchar(20) NOT NULL DEFAULT '',
  `requireddate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `canceldate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `quantityreqd` double NOT NULL DEFAULT '1',
  `specifications` longblob NOT NULL,
  `datequoted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `units` varchar(15) NOT NULL DEFAULT 'Each',
  `drawing` longblob NOT NULL,
  `rate` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contractref`),
  KEY `OrderNo` (`orderno`),
  KEY `CategoryID` (`categoryid`),
  KEY `Status` (`status`),
  KEY `TypeAbbrev` (`typeabbrev`),
  KEY `WORef` (`woref`),
  KEY `DebtorNo` (`debtorno`,`branchcode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*
  agrego: Eduardo López 05/11/2019
  proceso: Punto de Venta
  Tablas para vehículos de los contibuyentes
*/
CREATE TABLE `vehiclesbycostumer` (
  `vehicleno` int(11) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(255) DEFAULT NULL,
  `branchcode` varchar(255) DEFAULT NULL,
  `plate` varchar(255) DEFAULT NULL COMMENT 'Placa',
  `serie` varchar(255) DEFAULT NULL,
  `idmodel` int(255) DEFAULT NULL COMMENT 'Modelo',
  `idmark` int(11) DEFAULT NULL COMMENT 'Marca',
  `numeco` varchar(255) DEFAULT NULL COMMENT 'Numero economico',
  `description` varchar(255) DEFAULT NULL COMMENT 'descripcion general de auto',
  `numwheels` double DEFAULT NULL COMMENT 'Numero de llantas del auto',
  `annualmilage` double DEFAULT NULL COMMENT 'kilometraje promedio anual',
  `lastmilage` double DEFAULT NULL COMMENT 'ultimo kilometraje',
  `color` varchar(255) DEFAULT NULL,
  `registerdate` date DEFAULT NULL COMMENT 'fecha alta de vehiculo',
  `lastservice` date DEFAULT NULL COMMENT 'ultimo servicio',
  `lastordeninvoice` date DEFAULT NULL COMMENT 'ultima orden facturada',
  `yearvehicle` int(11) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `celular` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `userregister` varchar(255) DEFAULT NULL,
  `usermodify` varchar(255) DEFAULT NULL,
  `modifydate` datetime DEFAULT NULL,
  `operator` varchar(100) DEFAULT NULL,
  `appointmentdate` date DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`vehicleno`),
  KEY `Debtorno` (`debtorno`),
  KEY `BranchCode` (`branchcode`),
  KEY `Plate` (`plate`),
  KEY `serie` (`serie`),
  KEY `numeco` (`numeco`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `vehiclemarks` (
  `idmark` int(11) NOT NULL AUTO_INCREMENT,
  `mark` varchar(255) NOT NULL DEFAULT '',
  `active` int(11) DEFAULT NULL,
  PRIMARY KEY (`idmark`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vehiclemodels` (
  `idmodel` int(11) NOT NULL AUTO_INCREMENT,
  `idmark` int(11) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  PRIMARY KEY (`idmodel`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*
  agrego: Eduardo López 06/11/2019
  proceso: Punto de Venta
  Tabla para lista de precio por contribuyente
*/
CREATE TABLE `salestypesxcustomer` (
  `typeabbrev` varchar(255) DEFAULT NULL,
  `typeclient` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*
  agrego: Eduardo López 07/11/2019
  proceso: Punto de Venta
  Se crea tabla chartdebtortype
*/
CREATE TABLE `chartdebtortype` (
  `typedebtorid` int(11) NOT NULL DEFAULT '0',
  `gl_accountsreceivable` varchar(255) DEFAULT NULL,
  `gl_notesreceivable` varchar(255) DEFAULT NULL,
  `gl_debtoradvances` varchar(255) DEFAULT NULL,
  `gl_debtormoratorio` varchar(255) DEFAULT NULL,
  `gl_accountcontado` varchar(255) DEFAULT NULL,
  `gl_accountprovisional` varchar(45) DEFAULT NULL COMMENT 'Cuenta de facturas provisionales',
  `gl_noterepayment` varchar(45) DEFAULT NULL,
  `gl_notemachinery` varchar(45) DEFAULT NULL,
  `gl_accountret` varchar(255) DEFAULT NULL COMMENT 'Cuenta para retenciones',
  `gl_taxdebtoradvances` varchar(255) DEFAULT NULL,
  `gl_debitnote` varchar(255) DEFAULT NULL,
  `gl_categorydiscount` varchar(45) DEFAULT '',
  `gl_categirydiscount` varchar(45) DEFAULT '',
  `gl_deudordiverso` varchar(255) DEFAULT NULL,
  `gl_acreedordiverso` varchar(255) DEFAULT NULL,
  `gl_debtorfacfinanciero` varchar(45) DEFAULT NULL,
  `gl_bridgeaccountadvances` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`typedebtorid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: Eduardo López 08/11/2019
  proceso: Campo facturacion
  Se agrega campo código del sat
*/
ALTER TABLE stockmaster add sat_stock_code varchar(50) DEFAULT '01010101' COMMENT 'Codigo de producto usado en la version 3.3 del CFDI desde su catalogo';

/*
  agrego: Eduardo López 08/11/2019
  proceso: Clave presupuestal
  Campo para el tipo de configuacion de la clave
*/
ALTER TABLE budgetConfigClave add tipo_config int(11) DEFAULT '0' COMMENT 'Tipo de configuración: 1 Egreso, 2 Ingreso';

/*
  agrego: Eduardo López 11/11/2019
  proceso: Estado del Ejercicio
  Campo para identificar si es un estado del presupuesto del ingreso
*/
ALTER TABLE systypescat add nu_estado_presupuesto_ingreso int(11) DEFAULT NULL COMMENT '1 - Si pertenece a un Estado del Presupuesto de Ingreso';
ALTER TABLE systypescat add nu_usar_disponible_ingreso int(11) DEFAULT NULL COMMENT '1 - Si pertenece a un Estado del Presupuesto y se va a tomar en cuenta para el disponible del ingreso';

/*
  agrego:Jesús Santos 19/11/2019
  proceso: Detalle de Objeto Parcial
  Se crea tabla tb_cat_objeto_detalle
*/

CREATE TABLE `tb_cat_objeto_detalle` (
  `id_nu_objeto_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `loccode` varchar(255) DEFAULT NULL,
  `stockid` varchar(255) DEFAULT NULL,
  `ano` varchar(255) DEFAULT NULL,
  `clave_presupuestal` varchar(255) DEFAULT NULL,
  `cuenta_banco` varchar(255) DEFAULT NULL,
  `cuenta_abono` varchar(255) DEFAULT NULL,
  `cuenta_cargo` varchar(255) DEFAULT NULL,
  `estatus` int(10) unsigned NOT NULL DEFAULT '1',
  `fecha_efectiva` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nu_objeto_detalle`),
  KEY `loccode` (`loccode`),
  KEY `stockid` (`stockid`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Reyes 19/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla log_cancelInvoice
*/

CREATE TABLE `log_cancelInvoice` (
  `id` int(11) NOT NULL,
  `origtrandate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UUIDstatus` varchar(25) DEFAULT NULL,
  `cancelStatus` varchar(50) DEFAULT NULL,
  `canceltype` tinyint(1) DEFAULT NULL,
  `ovamount` double NOT NULL DEFAULT '0',
  `ovgst` double NOT NULL DEFAULT '0',
  `invtext` text,
  `cancelFlag` tinyint(1) NOT NULL COMMENT '0 - Proceso, 1 - No canceldo, 2 - Cancelado',
  `reg` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) DEFAULT NULL,
  `cancelxsust` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`reg`)
) ENGINE=InnoDB AUTO_INCREMENT=2119 DEFAULT CHARSET=utf8;

/*
  agrego: Eduardo López 20/11/2019
  proceso: Punto de Venta
  Se crea tablas para proceso
*/
CREATE TABLE `prospect_movimientos` (
  `u_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `u_proyecto` int(11) DEFAULT NULL,
  `dia` int(11) DEFAULT NULL,
  `mes` int(11) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `concepto` varchar(2000) DEFAULT NULL,
  `descripcion` text,
  `cargo` decimal(18,2) DEFAULT NULL,
  `civa` decimal(18,0) DEFAULT NULL,
  `cretencion` decimal(18,0) DEFAULT NULL,
  `abono` decimal(18,0) DEFAULT NULL,
  `aiva` decimal(18,0) DEFAULT NULL,
  `aretencion` decimal(18,0) DEFAULT NULL,
  `referencia` varchar(50) DEFAULT NULL,
  `prioridad` int(11) DEFAULT NULL,
  `u_user` varchar(40) DEFAULT NULL,
  `u_movimiento_rec` int(11) DEFAULT NULL,
  `confirmado` int(11) DEFAULT NULL,
  `UserId` varchar(40) DEFAULT NULL,
  `TipoMovimientoId` int(11) DEFAULT NULL,
  `estimado` int(11) DEFAULT NULL,
  `convenio` int(11) DEFAULT NULL,
  `IVA` int(11) DEFAULT NULL,
  `vencimiento` datetime DEFAULT NULL,
  `u_entidad` int(11) DEFAULT NULL,
  `u_cajaChica` int(11) DEFAULT NULL,
  `periodo_dev` int(11) DEFAULT NULL,
  `erp` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `activo` int(11) NOT NULL DEFAULT '1',
  `grupo_contable` varchar(60) NOT NULL DEFAULT '0',
  `catcode` char(6) DEFAULT NULL,
  `idstatus` int(11) DEFAULT NULL,
  `currcode` varchar(10) DEFAULT NULL,
  `fecha_compromiso` date DEFAULT NULL,
  `fecha_alta` datetime DEFAULT NULL,
  `debtorno` varchar(20) DEFAULT '',
  `branchcode` varchar(20) DEFAULT NULL,
  `areacod` varchar(20) DEFAULT NULL,
  `idpropiedad` int(11) DEFAULT NULL,
  `clientcontactid` varchar(10) DEFAULT NULL,
  `orderno` int(11) DEFAULT '0',
  `stockid` varchar(50) DEFAULT NULL,
  `typeabbrev` char(2) DEFAULT NULL,
  `rejectionreasonid` int(11) DEFAULT NULL,
  `movprevious` int(11) DEFAULT NULL,
  `tagref` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`u_movimiento`),
  KEY `u_prospect` (`u_movimiento`),
  KEY `debtorno` (`debtorno`,`branchcode`),
  KEY `fechaalta` (`fecha_alta`),
  KEY `debtor` (`debtorno`),
  KEY `user_` (`u_user`),
  KEY `userid` (`UserId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `prospect_status` (
  `idstatus` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `idsigstatus` varchar(20) DEFAULT NULL,
  `nombrealterno` varchar(50) DEFAULT NULL,
  `comentarios` int(11) DEFAULT NULL,
  `logo` varchar(90) DEFAULT NULL,
  `logoalterno` varchar(90) DEFAULT NULL,
  `cambiaetapa` int(11) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `marcainicial` int(11) DEFAULT NULL,
  `prospectexception` int(11) DEFAULT NULL,
  `flagfactura` int(11) DEFAULT '0' COMMENT 'Bandera de cambio de status cuando se termina de facturar pedido',
  `porcefect` double DEFAULT NULL,
  `mostrarenreporte` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`idstatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `phoneno2` varchar(20) DEFAULT NULL,
  `emailcontact` varchar(50) DEFAULT NULL,
  `CustLeadSourceId` int(11) DEFAULT NULL,
  `SinceCustcontactd` datetime DEFAULT NULL,
  `phoneno3` varchar(15) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `contactsmensid` int(11) DEFAULT NULL,
  `estadocivil` varchar(50) DEFAULT NULL,
  `idCapComIngresos` int(11) DEFAULT NULL,
  `companyprospect` varchar(255) DEFAULT NULL,
  `datecontact` datetime DEFAULT NULL COMMENT 'Fecha alta de contacto',
  `usercontact` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`contid`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=latin1;

CREATE TABLE `PDFTemplates` (
  `idtexto` int(11) NOT NULL AUTO_INCREMENT,
  `Titulo` varchar(255) DEFAULT NULL,
  `Texto` varchar(9999) DEFAULT NULL,
  `Ubicacion` varchar(50) DEFAULT NULL,
  `Orden` int(11) DEFAULT NULL,
  `tipodocto` int(11) DEFAULT NULL,
  `consulta` text,
  `centrarTitulo` varchar(1) DEFAULT '0',
  `filled` char(1) DEFAULT '0',
  `bold` char(1) DEFAULT '0',
  `posX` char(4) DEFAULT '0',
  `noColumns` int(1) NOT NULL DEFAULT '0',
  `tagref` int(11) DEFAULT '0',
  `legalid` int(11) DEFAULT '0',
  `imagename` varchar(100) DEFAULT NULL,
  `visible` varchar(1) DEFAULT '1',
  `xsize` int(11) DEFAULT '0',
  `ysize` int(11) DEFAULT '0',
  `linesunder` int(11) DEFAULT '0',
  PRIMARY KEY (`idtexto`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

INSERT INTO `PDFTemplates` (`idtexto`, `Titulo`, `Texto`, `Ubicacion`, `Orden`, `tipodocto`, `consulta`, `centrarTitulo`, `filled`, `bold`, `posX`, `noColumns`, `tagref`, `legalid`, `imagename`, `visible`, `xsize`, `ysize`, `linesunder`)
VALUES
  (64, NULL, '\n\n', 'Footer2', 10, 2, 'select salesmanname from salesman inner join salesorders on salesorders.salesman=salesman.salesmancode where orderno=', '0', '0', '1', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (59, '', 'Moneda: ', 'Footer2', 5, 2, 'Select salesorders.currcode From salesorders where salesorders.orderno =', '0', '0', '1', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (60, NULL, 'Condiciones de pago: ', 'Footer2', 6, 2, 'select paymentterms.terms from salesorders \ninner join paymentterms on salesorders.paytermsindicator= paymentterms.termsindicator and salesorders.orderno=', '0', '0', '1', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (65, 'InformacionBancaria', NULL, NULL, NULL, 1, 'select bankname,tagnamebank,accountbank,labelbank from bankinvoice where tagref=', '0', '0', '0', '0', 4, 0, 0, NULL, '1', 0, 0, 0),
  (66, 'InformacionComercial', 'Condiciones de pago: ', NULL, NULL, 1, 'select paymentterms.terms from salesorders  inner join paymentterms on salesorders.paytermsindicator= paymentterms.termsindicator and salesorders.orderno=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (67, 'InformacionComercial', 'Vendedor: ', NULL, NULL, 0, 'select salesmanname from salesman inner join salesorders on salesorders.salesman=salesman.salesmancode where orderno=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (68, 'InfoCliente', 'ID', 'Header', 1, 1, 'select debtortrans.debtorno from debtortrans where type in (10,110,119,66) AND order_=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (69, 'InfoEmisor', 'FAX', 'Footer2', 8, 1, 'SELECT legalbusinessunit.fax FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.order_ =', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (70, 'InfoEmisor', 'TELEFONO', 'Footer2', 9, 1, 'SELECT legalbusinessunit.telephone FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.order_ = ', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (71, 'InformacionComercial', 'Cotizo:', 'Footer2', 10, 1, 'SELECT www_users.realname FROM salesorders LEFT JOIN www_users ON www_users.userid = salesorders.UserRegister WHERE salesorders.orderno=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (72, 'InformacionComercial', 'Email:', 'Footer2', 11, 1, 'SELECT www_users.email FROM salesorders INNER JOIN salesman ON salesman.salesmancode = salesorders.salesman INNER JOIN www_users ON www_users.userid = salesman.usersales WHERE salesorders.orderno=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (134, NULL, '6) El material de Pedidos Especiales requiere un 50% de Anticipo', 'Footer', 8, 2, NULL, '0', '0', '0', '0', 0, NULL, 0, NULL, '1', 0, 0, 0),
  (73, 'InfoRemision', 'REMISION', NULL, NULL, 1, 'select concat(\"Cliente: \",REM.debtorno,\", Fecha: \",REM.origtrandate,\", Folio: \",REM.folio) from invoicetoremision INNER JOIN debtortrans ON invoicetoremision.idinvoice=debtortrans.id INNER JOIN debtortrans REM ON invoicetoremision.idremision=REM.id where ', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (74, 'InfoImpresion', 'SHOW_REMISION', NULL, 1, 1, 'select  IF(debtortrans.type=\"119\",\"1\",\"0\") from debtortrans where order_=', '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (75, 'InformacionComercial', 'Chofer:', NULL, NULL, 1, 'select www_users.realname\nfrom debtortrans\ninner join www_users on www_users.userid = debtortrans.nopedidof\nwhere order_ =', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (76, 'InformacionComercial', 'Facturo:', NULL, NULL, 1, 'SELECT www_users.realname FROM debtortrans LEFT JOIN www_users ON www_users.userid = debtortrans.userid WHERE debtortrans.order_=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (77, 'InfoImpresion', 'SHOW_DYNAMIC', '', 0, 1, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (78, 'InfoCliente', 'ID', 'Header', 1, 13, 'select debtortrans.debtorno from debtortrans where id=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (79, 'InfoEmisor', 'FAX', 'Header', 8, 13, 'SELECT legalbusinessunit.fax FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.id=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (80, 'InfoEmisor', 'TELEFONO', 'Header', 9, 13, 'SELECT legalbusinessunit.telephone FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.id= ', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (81, 'InfoImpresion', 'SHOW_DYNAMIC', '', 0, 13, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (82, 'InfoCliente', 'ID', 'Header', 1, 21, 'select debtortrans.debtorno from debtortrans where id=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (83, 'InfoEmisor', 'FAX', 'Header', 8, 21, 'SELECT legalbusinessunit.fax FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.id=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (84, 'InfoEmisor', 'TELEFONO', 'Header', 9, 21, 'SELECT legalbusinessunit.telephone FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.id= ', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (85, 'InformacionComercial', 'Condiciones de pago:', 'Footer3', 6, 21, 'select paymentterms.terms from paymentterms LEFT JOIN debtorsmaster ON debtorsmaster.paymentterms = paymentterms.termsindicator LEFT JOIN  debtortrans ON debtorsmaster.debtorno = debtortrans.debtorno where debtortrans.id=', '0', '0', '0', '0', 5, 0, 0, NULL, '1', 0, 0, 0),
  (86, 'InformacionBancaria', '', 'Footer3', 1, 21, 'select bankname,tagnamebank,accountbank,labelbank,concat(\"REF-\",replace(debtortrans.folio,\"|\",\"\")) as referencia from bankinvoice INNER JOIN debtortrans ON debtortrans.tagref=bankinvoice.tagref where debtortrans.id=', '0', '0', '0', '0', 5, 0, 0, NULL, '1', 0, 0, 0),
  (87, 'InfoImpresion', 'SHOW_DYNAMIC', '', 0, 21, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (88, 'InfoImpresion', 'SHOW_DYNAMIC', '', 0, 13, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (89, 'InformacionComercial', 'Condiciones de pago: ', 'Footer2', 6, 13, 'select paymentterms.terms from paymentterms LEFT JOIN debtorsmaster ON debtorsmaster.paymentterms = paymentterms.termsindicator LEFT JOIN  debtortrans ON debtorsmaster.debtorno = debtortrans.debtorno where debtortrans.id=', '0', '0', '1', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (90, 'InfoCliente', 'ID', 'Header', 1, 11, 'select debtortrans.debtorno from debtortrans where type in (11) AND order_=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (91, 'InfoEmisor', 'FAX', 'Header', 8, 11, 'SELECT legalbusinessunit.fax FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.order_=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (92, 'InfoEmisor', 'TELEFONO', 'Header', 9, 11, 'SELECT legalbusinessunit.telephone FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.order_= ', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (93, 'InformacionBancaria', '', 'Footer3', 1, 11, 'select bankname,tagnamebank,accountbank,labelbank,concat(\"REF-\",replace(debtortrans.folio,\"|\",\"\")) as referencia from bankinvoice INNER JOIN debtortrans ON debtortrans.tagref=bankinvoice.tagref where debtortrans.order_=', '0', '0', '0', '0', 5, 0, 0, NULL, '1', 0, 0, 0),
  (94, 'InformacionComercial', 'Condiciones de pago: ', 'Footer2', 6, 11, 'select paymentterms.terms from notesorders inner join paymentterms on notesorders.paytermsindicator= paymentterms.termsindicator and notesorders.orderno=', '0', '0', '1', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (95, 'InformacionComercial', 'Vendedor:', 'Footer2', 10, 11, 'SELECT distinct(salesman.salesmanname) FROM debtortrans INNER JOIN notesorders ON notesorders.orderno = debtortrans.order_ INNER JOIN salesman ON salesman.salesmancode = notesorders.salesman WHERE debtortrans.order_=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (96, 'InformacionComercial', 'Email:', 'Footer2', 11, 11, 'SELECT DISTINCT(www_users.email) FROM debtortrans INNER JOIN notesorders ON notesorders.orderno = debtortrans.order_ INNER JOIN salesman ON salesman.salesmancode = notesorders.salesman INNER JOIN www_users ON www_users.userid = salesman.usersales WHERE debtortrans.order_=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (97, 'InformacionComercial', 'Factura de Venta:', '', 7, 11, 'select nopedidof from debtortrans where type in (11) AND order_=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (110, 'InfoImpresion', 'HIDE_EMBARQUE', '', 0, 11, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (99, 'InformacionComercial', 'Condiciones de pago: ', NULL, NULL, 22, 'select paymentterms.terms from salesorders  inner join paymentterms on salesorders.paytermsindicator= paymentterms.termsindicator and salesorders.orderno=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (100, 'InfoCliente', 'ID', 'Header', 1, 22, 'select debtortrans.debtorno from debtortrans where type in (10,110,119,66) AND id=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (101, 'InfoEmisor', 'FAX', 'Footer2', 8, 22, 'SELECT legalbusinessunit.fax FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.id =', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (102, 'InfoEmisor', 'TELEFONO', 'Footer2', 9, 22, 'SELECT legalbusinessunit.telephone FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.id = ', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (103, 'InformacionComercial', 'Vendedor:', 'Footer2', 10, 22, 'SELECT distinct(salesman.salesmanname) FROM debtortrans INNER JOIN salesorders ON salesorders.orderno = debtortrans.order_ INNER JOIN salesman ON salesman.salesmancode = salesorders.salesman WHERE debtortrans.id=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (104, 'InformacionComercial', 'Email:', 'Footer2', 11, 22, 'SELECT www_users.email FROM debtortrans INNER JOIN salesorders ON salesorders.orderno = debtortrans.order_ INNER JOIN salesman ON salesman.salesmancode = salesorders.salesman INNER JOIN www_users ON www_users.userid = salesman.usersales WHERE debtortrans.', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (105, 'InfoRemision', 'REMISION', NULL, NULL, 22, 'select concat(\"Cliente: \",REM.debtorno,\", Fecha: \",REM.origtrandate,\", Folio: \",REM.folio) from invoicetoremision INNER JOIN debtortrans ON invoicetoremision.idinvoice=debtortrans.id INNER JOIN debtortrans REM ON invoicetoremision.idremision=REM.id where ', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (106, 'InfoImpresion', 'SHOW_REMISION', NULL, 1, 22, 'select  IF(debtortrans.type=\"119\",\"1\",\"0\") from debtortrans where id=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (107, 'InformacionComercial', 'Chofer:', NULL, NULL, 22, 'select www_users.realname from debtortrans inner join www_users on www_users.userid = debtortrans.nopedidof where id=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (108, 'InformacionComercial', 'Camion:', NULL, NULL, 22, 'SELECT debtortrans.noentradaf FROM debtortrans where id =', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (109, 'InfoImpresion', 'SHOW_DYNAMIC', '', 0, 22, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (111, 'InfoImpresion', 'HIDE_EMBARQUE', '', 0, 13, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (112, 'InfoImpresion', 'HIDE_EMBARQUE', '', 0, 21, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (113, 'InformacionComercial', 'Comentario:', NULL, 9, 11, 'SELECT debtortrans.invtext FROM debtortrans Where type=11 and order_ =', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (114, 'InfoImpresion', 'AJUSTE_DECIMALES', '', 0, 1, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (115, 'InfoImpresion', 'AJUSTE_DECIMALES', '', 0, 11, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (116, 'InfoImpresion', 'AJUSTE_DECIMALES', '', 0, 13, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (117, 'InfoImpresion', 'AJUSTE_DECIMALES', '', 0, 22, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (118, 'CONDICIONES DE VENTA', NULL, 'Footer', 1, 2, NULL, '0', '0', '1', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (119, NULL, '1) Tiempos de entrega salvo previa venta', 'Footer', 2, 2, NULL, '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (135, NULL, '2) ', NULL, NULL, NULL, NULL, '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (122, '', '2) Precios sujetos a cambios sin previo aviso', 'Footer', 5, 2, '', '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (123, '', '3) Toda devolucion que no sea por garantia causara un cargo del 10%', 'Footer', 6, 2, '', '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (124, '', '4) Precio con el 16% de IVA', 'Footer', 7, 2, '', '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (125, '', 'Tiempo de entrega:', 'Footer', 9, 2, 'Select deliverytext FROM salesorders WHERE orderno = ', '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (126, NULL, '1.- Entregar productos/servicios con copia de OC y con la factura.\n', 'Footer', 1, 25, NULL, '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (127, NULL, '2.- Ingresar esta OC según acuerdos en precios, método de entrega, términos de pago y fecha de entrega según información de la presente OC.', 'Footer', 2, 25, NULL, '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (128, NULL, '3.- Favor de notificar de inmediato si alguno de estos acuerdos no se pueden cumplir.', 'Footer', 3, 25, NULL, '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (129, NULL, '4.- Gracias de antemano por darnos un buen servicio..!!!', 'Footer', 4, 25, NULL, '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (130, 'InformacionComercial', 'Aviso:', '', 1, 1, '', '0', '0', '1', '1', 1, 0, 0, NULL, '1', 0, 0, 0),
  (136, NULL, 'Vendedor:', 'Header', 1, 3, 'Select CONCAT(\'Vendedor: \', www_users.realname) From salesorders left join www_users on www_users.userid = salesorders.UserRegister where salesorders.orderno =', '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (34, 'InfoPagare', 'MSG4', NULL, NULL, 0, 'SELECT CONCAT(\"MSG4\",\" \")  from debtortrans where order_=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (35, 'InfoPagare', 'TOTAL', NULL, NULL, 1, 'select (ovamount+ovgst) as total from debtortrans where order_=', '0', '0', '0', '0', 1, 0, 0, '0', '1', 0, 0, 0),
  (31, 'InfoPagare', 'MSG1', NULL, NULL, 0, 'SELECT CONCAT(\"MSG1\",\" \")  from debtortrans where order_=', '0', '0', '0', '0', 1, 0, 0, '0', '1', 0, 0, 0),
  (32, 'InfoPagare', 'MSG2', NULL, NULL, 0, 'SELECT CONCAT(\"MSG2\",\" \")  from debtortrans where order_=', '0', '0', '0', '0', 1, 0, 0, '0', '1', 0, 0, 0),
  (33, 'InfoPagare', 'MSG3', NULL, NULL, 0, 'SELECT CONCAT(\"MSG3\",\" \")  from debtortrans where order_=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (131, 'Telefono:', NULL, 'Header2', 3, 2, 'SELECT phone FROM salesorders INNER JOIN www_users ON www_users.userid = salesorders.UserRegister WHERE orderno =', '0', '0', '0', '0', 0, NULL, 0, NULL, '1', 0, 0, 0),
  (132, NULL, '5) En Iluminacion y Electronicos solo se aceptan devoluciones en garantia', 'Footer', 7, 2, NULL, '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (133, NULL, '5.-', 'Footer', 5, 25, 'select concat(\'Si no se cumple con la fecha promesa de entrega \',max(dateship), \', Automation Warehouse no está obligado a recibir el material.\') from purchorderdetails where orderno=', '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (137, NULL, 'Comentarios:', 'Header', 2, 3, 'SELECT CASE WHEN salesorders.comments <> \'\' THEN CONCAT(\'Comentarios: \', salesorders.comments) ELSE \'\' END AS comentearios FROM salesorders WHERE salesorders.orderno =', '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (138, 'InfoImpresion', 'SHOW_DYNAMIC', '', 0, 11, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (288, 'InformacionComercial', 'Aviso:', '', 1, 4, '', '0', '0', '1', '1', 1, 0, 0, NULL, '1', 0, 0, 0),
  (289, 'InfoPagare', 'TOTAL', NULL, NULL, 4, 'select (ovamount+ovgst) as total from debtortrans where order_=', '0', '0', '0', '0', 1, 0, 0, '0', '1', 0, 0, 0),
  (286, 'InfoImpresion', 'SHOW_DYNAMIC', '', 0, 4, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (287, 'InfoImpresion', 'AJUSTE_DECIMALES', '', 0, 4, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (285, 'InformacionComercial', 'Facturo:', NULL, NULL, 4, 'SELECT www_users.realname FROM debtortrans LEFT JOIN www_users ON www_users.userid = debtortrans.userid WHERE debtortrans.order_=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (282, 'InfoRemision', 'REMISION', NULL, NULL, 4, 'select concat(\"Cliente: \",REM.debtorno,\", Fecha: \",REM.origtrandate,\", Folio: \",REM.folio) from invoicetoremision INNER JOIN debtortrans ON invoicetoremision.idinvoice=debtortrans.id INNER JOIN debtortrans REM ON invoicetoremision.idremision=REM.id where debtortrans.order_ =', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (283, 'InfoImpresion', 'SHOW_REMISION', NULL, 1, 4, 'select  IF(debtortrans.type=\"119\",\"1\",\"0\") from debtortrans where order_=', '0', '0', '0', '0', 0, 0, 0, NULL, '1', 0, 0, 0),
  (284, 'InformacionComercial', 'Chofer:', NULL, NULL, 4, 'select www_users.realname from debtortrans inner join www_users on www_users.userid = debtortrans.nopedidof where debtortrans.order_ =', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (274, 'InfoImpresion', 'SHOW_ALMACEN', '', 0, 1, 'select distinct(1) from debtortrans where 0=0 or 1=', '', '', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (275, 'InformacionBancaria', NULL, NULL, NULL, 4, 'select bankname,tagnamebank,accountbank,labelbank from bankinvoice where tagref=', '0', '0', '0', '0', 4, 0, 0, NULL, '1', 0, 0, 0),
  (276, 'InformacionComercial', 'Condiciones de pago: ', NULL, NULL, 4, 'select paymentterms.terms from salesorders  inner join paymentterms on salesorders.paytermsindicator= paymentterms.termsindicator and salesorders.orderno=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (277, 'InfoCliente', 'ID', 'Header', 1, 4, 'select debtortrans.debtorno from debtortrans where type in (10,110,119,66) AND order_=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (278, 'InfoEmisor', 'FAX', 'Footer2', 8, 4, 'SELECT legalbusinessunit.fax FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.order_ =', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (279, 'InfoEmisor', 'TELEFONO', 'Footer2', 9, 4, 'SELECT legalbusinessunit.telephone FROM debtortrans INNER JOIN tags ON debtortrans.tagref = tags.tagref INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid WHERE debtortrans.order_ = ', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (280, 'InformacionComercial', 'Cotizo:', 'Footer2', 10, 4, 'SELECT www_users.realname FROM salesorders LEFT JOIN www_users ON www_users.userid = salesorders.UserRegister WHERE salesorders.orderno=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0),
  (281, 'InformacionComercial', 'Email:', 'Footer2', 11, 4, 'SELECT www_users.email FROM salesorders INNER JOIN salesman ON salesman.salesmancode = salesorders.salesman INNER JOIN www_users ON www_users.userid = salesman.usersales WHERE salesorders.orderno=', '0', '0', '0', '0', 1, 0, 0, NULL, '1', 0, 0, 0);

ALTER TABLE salesorders MODIFY tagref varchar(5);

CREATE TABLE `salesorderdetailsgroups` (
  `orderno` bigint(20) NOT NULL DEFAULT '0',
  `orderlineno` int(10) unsigned NOT NULL DEFAULT '0',
  `groupname` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  `showcontent` char(1) DEFAULT NULL,
  `required` int(10) unsigned DEFAULT '1',
  `hideprice` char(1) DEFAULT '0',
  `hidepart` char(1) DEFAULT '0',
  PRIMARY KEY (`orderno`,`orderlineno`,`groupname`),
  KEY `idx_group` (`groupname`),
  KEY `idx_showcontent` (`showcontent`),
  KEY `idx_lineno` (`orderlineno`),
  KEY `idx_orderno` (`orderno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `shippers` (
  `shipper_id` int(11) NOT NULL AUTO_INCREMENT,
  `shippername` char(40) NOT NULL DEFAULT '',
  `mincharge` double NOT NULL DEFAULT '0',
  `onshipping` char(1) DEFAULT '0',
  `national_account` varchar(50) DEFAULT '',
  `international_account` varchar(50) DEFAULT '',
  `userid` varchar(20) DEFAULT NULL,
  `FlagEnvios` int(11) DEFAULT '0',
  `FlagEmbarqueMostrador` int(11) DEFAULT '0',
  `ComPorcenViajes` double DEFAULT NULL,
  `ComMontoViajes` double DEFAULT NULL,
  `ComExtraPorcenViajes` double DEFAULT NULL,
  `ComExtraMontoViajes` double DEFAULT NULL,
  `FlagValExistencias` int(11) DEFAULT '1',
  PRIMARY KEY (`shipper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `shippingorders` (
  `shippingno` int(11) NOT NULL DEFAULT '0',
  `shippingstatusid` int(11) DEFAULT '0',
  `folio` varchar(50) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `debtortransid` int(11) DEFAULT NULL,
  `comments` varchar(500) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `trackingnumber` varchar(255) DEFAULT NULL,
  `userid` varchar(100) DEFAULT NULL,
  `deliverydate` datetime DEFAULT NULL,
  `shippingdate` datetime DEFAULT NULL,
  `tagref` int(11) DEFAULT NULL,
  `cancelled` char(1) DEFAULT '0',
  `shippingparent` int(11) DEFAULT '0',
  `totalpiezas` int(11) DEFAULT '0',
  `totalpeso` double(10,4) DEFAULT '0.0000',
  `flagembarquemostrador` int(11) DEFAULT NULL,
  PRIMARY KEY (`shippingno`),
  KEY `orderno` (`orderno`),
  KEY `shippingstatusid` (`shippingstatusid`),
  KEY `debtortransid` (`debtortransid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `shippingstatus` (
  `shippingstatusid` int(11) NOT NULL AUTO_INCREMENT,
  `shippingstatusname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`shippingstatusid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

ALTER TABLE legalbusinessunit add c_RegimenFiscal varchar(5) DEFAULT NULL COMMENT 'Regimen Fiscal';
ALTER TABLE legalbusinessunit add c_Regimen varchar(5) DEFAULT NULL COMMENT 'Tipo de Persona';

ALTER TABLE taxauthorities add c_Impuesto varchar(5) DEFAULT NULL;
ALTER TABLE taxcategories add id_Tasa int(11) DEFAULT NULL;

CREATE TABLE `systypesinvoice` (
  `typeid` smallint(6) NOT NULL DEFAULT '0',
  `typename` char(50) NOT NULL DEFAULT '',
  `typeno` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`typeid`),
  KEY `TypeNo` (`typeno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `systypesinvoice` (`typeid`, `typename`, `typeno`)
VALUES
  (0, 'Poliza Contable - GL', 1),
  (1, 'Pago Contable - GL', 1),
  (2, 'Recibo Contable - GL', 1),
  (3, 'Standing Journal', 1),
  (10, 'Factura de Venta', 1),
  (11, 'Nota de Credito', 1),
  (12, 'Recibo', 1),
  (13, 'Nota de Credito Directa', 1),
  (15, 'Poliza - Deudores', 1),
  (16, 'Transferencia de Almacen', 1),
  (17, 'Ajuste de Existencias', 1),
  (18, 'Orden de Compra', 1),
  (20, 'Factura de Compra', 1),
  (21, 'Nota de Cargo', 1),
  (22, 'Pago a Acreedores', 1),
  (23, 'Poliza Acreedores', 1),
  (24, 'Pago Porveedores Varios', 1),
  (25, 'Entrega de Orden de Compra', 1),
  (26, 'Work Order Receipt', 1),
  (28, 'Work Order Issue', 1),
  (29, 'Work Order Variance', 1),
  (30, 'Orden de Ventas', 1),
  (31, 'Cierre de Embarque', 1),
  (32, 'Nota De Credito Proveedor Directa', 1),
  (33, 'Nota de Credito Proveedor Full', 1),
  (34, 'Nota de Cargo Proveedor Directa', 1),
  (35, 'Actualizacion de Costo', 1),
  (36, 'Diferencia Cambiaria', 1),
  (37, 'Orden Nota \r\n\r\nCredito Proveedor', 1),
  (40, 'Orden de Trabajo', 1),
  (41, 'Ajuste de Existencias s/Contabilidad', 1),
  (45, 'Concentrado Reembolsos', 1),
  (50, 'Balance Inicial', 1),
  (55, 'Transferencias Manuales De A Matriz', 1),
  (60, 'Devolucion Venta', 1),
  (70, 'Pagare', 1),
  (80, 'Anticipo Clientes', 1),
  (90, 'Ingreso Caja', 1),
  (95, 'Orden de nota de credito', 1),
  (96, 'Nota Cargo Cheque Devuelto', 1),
  (100, 'Egreso Caja', 1),
  (105, 'Factura Provisional', 1),
  (106, 'Factura Contrato', 1),
  (110, 'Factura de Contado', 1),
  (111, 'Factura Interna', 1),
  (112, 'Correcion Credito', 1),
  (113, 'Correcion Cargo', 1),
  (116, 'Correccion Credito Proveedor', 1),
  (117, 'Correccion Debito Proveedor', 1),
  (119, 'Remision', 1),
  (120, 'Cierre de Caja', 1),
  (121, 'Anticipo Proveedores', 1),
  (122, 'Aplicacion Anticipo Proveedor', 1),
  (123, 'DesAplicacion Anticipo Proveedor', 1),
  (130, 'Saldo anticipo', 1),
  (200, 'Abono Directo de Cliente a Bancos', 1),
  (300, 'H - Inventario Inicial', 1),
  (400, 'H - Pagare', 1),
  (410, 'H - Factura', 1),
  (420, 'H - Nota de Credito', 1),
  (430, 'H - Devolucion  Sobre Venta', 1),
  (440, 'H - Nota de Cargo', 1),
  (450, 'H - Abono del Cliente', 1),
  (460, 'H - Pago del Cliente', 1),
  (470, 'H - Factura Proveedor', 1),
  (480, 'H - Abono Proveedor', 1),
  (500, 'Auto Debtor Number', 1),
  (501, 'Reembolso', 1),
  (550, 'Clave Comisionistas', 1),
  (560, 'Venta Pagares', 1),
  (570, 'Aplicacion', 1),
  (571, 'DesAplicacion', 1),
  (999, 'Cierre Anual', 1),
  (1000, 'Reembolso de Pago de Compra', 1);

drop table paymentmethods;
CREATE TABLE `paymentmethods` (
  `paymentid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `paymentname` varchar(25) NOT NULL DEFAULT '',
  `paymenttype` int(11) NOT NULL DEFAULT '1',
  `receipttype` int(11) NOT NULL DEFAULT '1',
  `codesat` varchar(10) DEFAULT NULL,
  `namesat` varchar(150) DEFAULT NULL,
  `receiptuse` tinyint(4) DEFAULT NULL,
  `invoiceuse` tinyint(4) DEFAULT NULL,
  `flagdocfiscal` int(11) DEFAULT NULL,
  `satcode` varchar(50) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`paymentid`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

INSERT INTO `paymentmethods` (`paymentid`, `paymentname`, `paymenttype`, `receipttype`, `codesat`, `namesat`, `receiptuse`, `invoiceuse`, `flagdocfiscal`, `satcode`, `active`)
VALUES
  (1, 'Efectivo', 1, 1, '01', 'Efectivo', 1, 1, NULL, NULL, 1),
  (2, 'Cheque', 1, 1, '02', 'Cheque nominativo', 1, 1, NULL, NULL, 1),
  (3, 'Transferencia', 1, 1, '03', 'Transferencia electrónica de fondos', 1, 1, NULL, NULL, 1),
  (4, 'Tarjetas de credito', 1, 1, '04', 'Tarjetas de credito', 1, 1, NULL, NULL, 1),
  (5, 'Monederos electronicos', 1, 1, '05', 'Monederos electronicos', 1, 1, NULL, NULL, 1),
  (6, 'Dinero electronico', 1, 1, '06', 'Dinero electronico', 1, 1, NULL, NULL, 1),
  (7, 'Tarjetas digitales', 1, 1, '07', 'Tarjetas digitales', 1, 0, NULL, NULL, 0),
  (8, 'Vales de despensa', 1, 1, '08', 'Vales de despensa', 1, 1, NULL, NULL, 1),
  (9, 'Bienes', 1, 1, '09', 'Bienes', 1, 0, NULL, NULL, 0),
  (10, 'Servicio', 1, 1, '10', 'Servicio', 1, 0, NULL, NULL, 0),
  (11, 'Por cuenta de tercero', 1, 1, '11', 'Por cuenta de tercero', 1, 0, NULL, NULL, 0),
  (12, 'Dacion en pago', 1, 1, '12', 'Dación en pago', 1, 0, NULL, NULL, 1),
  (13, 'Pago por subrogacion', 1, 1, '13', 'Pago por subrogacion', 1, 0, NULL, NULL, 1),
  (14, 'Pago por consignacion', 1, 1, '14', 'Pago por consignacion', 1, 0, NULL, NULL, 1),
  (15, 'Condonacion', 1, 1, '15', 'Condonacion', 1, 0, NULL, NULL, 1),
  (16, 'Cancelacion', 1, 1, '16', 'Cancelacion', 1, 0, NULL, NULL, 0),
  (17, 'Compensacion', 1, 1, '17', 'Compensacion', 0, 1, NULL, NULL, 1),
  (18, 'NA', 1, 1, '98', 'NA', 0, 0, NULL, NULL, 0),
  (19, 'Por Definir', 1, 1, '99', 'Por Definir', 1, 1, NULL, NULL, 1),
  (20, 'Tarjeta de debito', 1, 1, '28', 'Tarjeta de Debito', 0, 1, NULL, NULL, 1),
  (23, 'Novación', 1, 1, '23', 'Novación', 1, 1, NULL, NULL, 1),
  (24, 'Confusión', 1, 1, '24', 'Confusión', 1, 1, NULL, NULL, 1),
  (25, 'Remisión de deuda', 1, 1, '25', 'Remisión de deuda', 1, 1, NULL, NULL, 1),
  (26, 'Prescripción o caducidad', 1, 1, '26', 'Prescripción o caducidad', 1, 1, NULL, NULL, 1),
  (27, 'A satisfacción del acreed', 1, 1, '27', 'A satisfacción del acreedor', 1, 1, NULL, NULL, 1),
  (30, 'Aplicación de anticipos', 1, 1, '30', 'Aplicación de anticipos', 1, 1, NULL, NULL, 1);

ALTER TABLE currencies add decimalplaces int(11) DEFAULT NULL; 
ALTER TABLE currencies add variacion varchar(25) DEFAULT NULL;

UPDATE currencies SET currency = 'Euro', decimalplaces = '2', variacion = '0.40' WHERE currabrev = 'EUR';
UPDATE currencies SET currency = 'Peso Mexicano', decimalplaces = '2', variacion = '0.35' WHERE currabrev = 'MXN';
UPDATE currencies SET currency = 'Dolar americano', decimalplaces = '2', variacion = '0.35' WHERE currabrev = 'USD';

CREATE TABLE `stockmovestaxes` (
  `stkmoveno` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0',
  `taxontax` tinyint(4) NOT NULL DEFAULT '0',
  `taxcalculationorder` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkmoveno`,`taxauthid`,`taxcalculationorder`),
  KEY `taxauthid` (`taxauthid`),
  KEY `calculationorder` (`taxcalculationorder`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `debtortranstaxesclient` (
  `iddoc` varchar(20) NOT NULL,
  `idtax` int(4) NOT NULL,
  `amount` double(10,2) NOT NULL,
  `percent` int(4) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `accountcode` varchar(50) DEFAULT NULL,
  KEY `iddoc` (`iddoc`),
  KEY `idtax` (`idtax`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `sec_taxes` (
  `idtax` int(4) NOT NULL AUTO_INCREMENT,
  `nametax` varchar(50) NOT NULL,
  `percent` double(10,2) NOT NULL DEFAULT '0.00',
  `nature` int(4) NOT NULL DEFAULT '-1',
  `typetax` int(11) DEFAULT '1' COMMENT '1 para impuesto federal y 2 para impuesto local',
  `fieldaplica` int(11) DEFAULT '1' COMMENT '1 aplica sobre subtotal y 2 aplica sobre impuesto de factura',
  `flagimpuestolocal` int(1) DEFAULT '0' COMMENT '1 si el impuesto el local',
  `accountcode` varchar(50) DEFAULT NULL,
  `flageditdescription` tinyint(4) DEFAULT NULL,
  `c_Impuesto` varchar(5) DEFAULT NULL COMMENT 'Código del Presupuesto',
  `c_TipoFactor` varchar(25) DEFAULT NULL COMMENT 'Tipo de Factor',
  `accountCodePaid` varchar(30) DEFAULT NULL COMMENT 'Cuenta que afecta el pago',
  `accountCodeGet` varchar(30) DEFAULT NULL COMMENT 'Cuenta de impuesto de cobrado - pago ',
  `flagPaid` tinyint(1) DEFAULT '0' COMMENT 'Bandera para aplicacion de pagos a banco',
  PRIMARY KEY (`idtax`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `salesinvoiceadvance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transidinvoice` int(11) unsigned zerofill DEFAULT NULL,
  `transidncredito` int(11) unsigned zerofill DEFAULT NULL,
  `montoncredito` decimal(20,2) DEFAULT NULL,
  `transidncargo` int(11) DEFAULT NULL,
  `transidanticipo` int(11) DEFAULT NULL,
  `montoncargo` decimal(20,2) DEFAULT NULL,
  `trandate` datetime DEFAULT '0000-00-00 00:00:00' COMMENT 'Fecha de registro',
  `userid` varchar(30) DEFAULT '' COMMENT 'usuario que registro',
  `tiporelacion_relacion` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `leasingCharges` (
  `idleasincharges` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idleasingdetail` int(11) DEFAULT NULL,
  `orderno` int(11) NOT NULL COMMENT 'numero de la orden de renta',
  `purchaseorder` varchar(20) DEFAULT NULL COMMENT 'Orden de compra del cliente',
  `debtorno` varchar(20) NOT NULL COMMENT 'Cliente al que se le rento el activo',
  `trandate` date NOT NULL COMMENT 'fecha en la que se genero el registro(fecha de corte)',
  `epep` varchar(100) DEFAULT NULL COMMENT 'Elemento pep',
  `serialno` varchar(20) DEFAULT NULL COMMENT 'Serie del activo fijo',
  `barcode` varchar(20) DEFAULT NULL COMMENT 'Codigo del activo fijo',
  `datefrom` date NOT NULL COMMENT 'fecha de inicio del periodo de cobro',
  `timefrom` time DEFAULT NULL COMMENT 'hora de inicio de renta en caso de tener',
  `dateto` date DEFAULT NULL COMMENT 'fecha de fin del periodo de cobro',
  `timeto` time DEFAULT NULL COMMENT 'hora de fin de renta en caso de tener',
  `days` int(11) DEFAULT NULL COMMENT 'total de dias cobrados',
  `hours` float DEFAULT NULL COMMENT 'Total de horas de la renta',
  `unitprice` float DEFAULT NULL COMMENT 'Precio unitario del cobro',
  `amount` float DEFAULT NULL COMMENT 'monto del cobro ',
  `flagauthorized` int(11) DEFAULT '0' COMMENT 'Bandera para indicar si el cliente autorizo o no el cobro',
  `status` int(11) DEFAULT '1',
  `idorigin` int(11) DEFAULT NULL COMMENT 'Campo para indicar el id del cobro origen, cuando se genero un nuevo registro redimensionado',
  `flagperiodactive` int(11) DEFAULT '1' COMMENT 'Bandera para indicar cual es el cobro activo',
  `duedate` datetime DEFAULT NULL COMMENT 'Fecha de corte',
  `salesordernumber` int(11) DEFAULT '0',
  PRIMARY KEY (`idleasincharges`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `salesstockproperties` (
  `stkcatpropid` int(11) NOT NULL DEFAULT '0',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `valor` varchar(255) NOT NULL DEFAULT '',
  `complemento` varchar(255) DEFAULT NULL COMMENT 'se agrega para agregar un valor que complemeta o es dependiento del valor inicial configurado',
  `orderlineno` int(11) unsigned NOT NULL DEFAULT '0',
  `InvoiceValue` varchar(100) DEFAULT '',
  `typedocument` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkcatpropid`,`orderno`,`valor`,`orderlineno`,`typedocument`),
  KEY `orderno` (`orderno`),
  KEY `valor` (`valor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stockcatproperties` (
  `stkcatpropid` int(11) NOT NULL AUTO_INCREMENT,
  `categoryid` char(6) NOT NULL,
  `label` text NOT NULL,
  `controltype` tinyint(4) NOT NULL DEFAULT '0',
  `defaultvalue` varchar(100) NOT NULL DEFAULT '''''',
  `reqatsalesorder` tinyint(4) NOT NULL DEFAULT '0',
  `reqatpurshorder` tinyint(4) DEFAULT NULL,
  `allowduplicate` tinyint(4) DEFAULT '0' COMMENT 'Permite duplicar estar propiedad al duplicar un pedido',
  `reqatworkorder` tinyint(4) DEFAULT '0',
  `idPadre` int(11) DEFAULT '0',
  `reqatprint` tinyint(1) DEFAULT '1',
  `Ordenar` int(11) DEFAULT NULL,
  `addressref` int(11) DEFAULT '0',
  `addresslink` char(1) DEFAULT '0',
  `requiredtoprocess` char(1) DEFAULT '0',
  `requiredtoday` char(1) DEFAULT '0',
  `requiredphoto` char(1) DEFAULT '0',
  `requiredtocalendar` char(1) DEFAULT '0',
  `dayweek` int(11) DEFAULT NULL,
  PRIMARY KEY (`stkcatpropid`),
  KEY `categoryid` (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `stockclass` (
  `idclass` varchar(50) DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE stockmoves add COLUMN anticipo DOUBLE;

INSERT INTO `config` (`confname`, `confvalue`, `confcomentarios`, `confshow`, `confmodulo`, `confshowfil`)
VALUES
  ('FormatoDeComentarioEnPV', '0', 'Formato de Comentario en Pedido de Venta con Saltos de Linea los comentarios', 0, NULL, 1);


CREATE TABLE `invoicetoremision` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `amt` double(11,2) DEFAULT NULL,
  `registerdate` datetime DEFAULT NULL,
  `idinvoice` bigint(20) DEFAULT NULL,
  `idremision` bigint(20) DEFAULT NULL,
  `idcorrecion` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_idinvoice` (`idinvoice`),
  KEY `idx_idremision` (`idremision`),
  KEY `idx_idcorr` (`idcorrecion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `workorders` (
  `wo` int(11) NOT NULL,
  `loccode` varchar(50) NOT NULL,
  `requiredby` date NOT NULL DEFAULT '0000-00-00',
  `startdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `proposeddate` date DEFAULT '0000-00-00',
  `costissued` double NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `orderno` bigint(20) DEFAULT '0',
  `idstatus` int(11) DEFAULT '1',
  `idstatusplanit` int(11) DEFAULT NULL COMMENT 'idstatus planit obtiene su valor por default de un trigger',
  `u_user` varchar(50) DEFAULT NULL COMMENT 'Recurso asignado en panel de control',
  `prioridad` int(11) DEFAULT '5',
  `fecha_alta` datetime DEFAULT NULL,
  `estimado` int(11) DEFAULT NULL,
  `lineno` int(11) DEFAULT NULL,
  `wodescription` varchar(255) DEFAULT NULL COMMENT 'Descripcion especificada por usuario',
  `confirmado` int(11) DEFAULT '0',
  `wonivel` int(11) DEFAULT '0' COMMENT 'Variable para realizar compras, valores aceptados 1 para Grupo y 2 para Capitulo',
  `wolevel` int(11) DEFAULT '0' COMMENT 'Nivel de agrupacion',
  `typeproduct` varchar(10) DEFAULT NULL COMMENT 'Tipo de los productos maquinado o chapado',
  `haspackages` int(1) DEFAULT NULL COMMENT 'Campo que indica si tiene paquete ',
  `kit` varchar(50) DEFAULT NULL COMMENT 'Campo para identificar si la orde de trabajoo es un kit',
  `flagfecha` int(1) DEFAULT NULL,
  `wo_relacion_estimacion` int(11) DEFAULT NULL COMMENT 'columna para relacionar la orden de trabajo de cosot con la orden de trabajo de contrato para las estimaciones',
  PRIMARY KEY (`wo`),
  KEY `LocCode` (`loccode`),
  KEY `StartDate` (`startdate`),
  KEY `RequiredBy` (`requiredby`),
  KEY `u_user` (`u_user`),
  KEY `idstatus` (`idstatus`),
  KEY `wo` (`wo`),
  KEY `orderno` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `worequirements` (
  `wo` int(11) NOT NULL,
  `parentstockid` varchar(255) NOT NULL,
  `stockid` varchar(255) NOT NULL,
  `qtypu` double NOT NULL DEFAULT '1',
  `stdcost` double NOT NULL DEFAULT '0',
  `autoissue` tinyint(4) NOT NULL DEFAULT '0',
  `directparentid` varchar(100) CHARACTER SET latin1 NOT NULL,
  `worequirements_id` int(11) NOT NULL AUTO_INCREMENT,
  `qtyissued` double DEFAULT NULL,
  `ispercent` varchar(1) DEFAULT '0',
  `masterparentid` varchar(100) NOT NULL DEFAULT '',
  `flagautoemision` int(11) DEFAULT NULL,
  `bom_category_id` int(11) DEFAULT '0',
  `cantidadtransferida` double DEFAULT '0',
  `qtyex` double DEFAULT '0',
  `mastercomponent` varchar(100) DEFAULT NULL COMMENT 'Campo para agregar el codigo del destajo principal',
  UNIQUE KEY `worequirements_id` (`worequirements_id`),
  KEY `stockid` (`stockid`),
  KEY `worequirements_ibfk_3` (`parentstockid`),
  KEY `bom_category_id` (`bom_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plcdata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wo` int(11) DEFAULT NULL,
  `variable` varchar(255) DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `transno` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `stockid` varchar(255) DEFAULT NULL,
  `driver` varchar(255) DEFAULT NULL,
  `truck` varchar(255) DEFAULT NULL,
  `quantity` double DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order` (`wo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `complementsine` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `typeprocess` varchar(250) DEFAULT NULL,
  `typecommittee` varchar(250) DEFAULT NULL,
  `entity` varchar(3) DEFAULT NULL,
  `idaccounting` varchar(25) DEFAULT NULL,
  `orderno` int(15) DEFAULT NULL,
  `Scope` int(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

truncate table taxauthrates;
INSERT INTO `taxauthrates` (`taxauthority`, `dispatchtaxprovince`, `taxcatid`, `taxrate`)
VALUES
  (1, 1, 1, 0.15),
  (1, 1, 2, 0),
  (1, 1, 3, 0.15),
  (1, 1, 4, 0.16),
  (1, 1, 5, 0);

CREATE TABLE `AprobacionFolios` (
  `rfc` tinytext,
  `noAprobacion` varchar(255) DEFAULT NULL,
  `anioAprobacion` varchar(255) DEFAULT NULL,
  `serie` varchar(255) DEFAULT NULL,
  `Inicial` varchar(255) DEFAULT NULL,
  `Final` varchar(255) DEFAULT NULL,
  `idaprobacion` int(11) NOT NULL AUTO_INCREMENT,
  `certificado` varchar(255) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT '1900-01-01',
  PRIMARY KEY (`idaprobacion`),
  KEY `Index_2` (`serie`,`Inicial`,`Final`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;


INSERT INTO `AprobacionFolios` (`rfc`, `noAprobacion`, `anioAprobacion`, `serie`, `Inicial`, `Final`, `idaprobacion`, `certificado`, `fecha_vencimiento`)
VALUES
  ('EKU9003173C9', '123456', '2016', 'A', '1', '99999999999999999', 1, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123456', '2016', 'B', '1', '999999999999999999999', 2, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123456', '2016', 'C', '1', '999999999999999999', 3, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123456', '2016', 'D', '1', '99999999999999999999', 4, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123456', '2016', 'E', '1', '999999999999999999', 5, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123456', '2016', 'NCA', '1', '999999999999999999', 6, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123456', '2016', 'NCB', '1', '999999999999999999', 7, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123456', '2016', 'NCC', '1', '999999999999999999', 8, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123456', '2016', 'NCD', '1', '999999999999999999', 9, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123456', '2016', 'NCE', '1', '999999999999999999', 10, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123', '2016', 'CF', '1', '999999999999999', 11, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123', '2016', 'CG', '1', '999999999999999', 12, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123', '2016', 'CA', '1', '999999999999999', 13, '30001000000400002434', '2021-06-16'),
  ('EKU9003173C9', '123456', '2016', 'B', '39457', '99999999999999999', 14, '30001000000400002434', '2021-06-14'),
  ('EKU9003173C9', '123456', '2016', 'BR', '1', '99999999999999999', 15, '30001000000400002434', '2021-06-14'),
  ('EKU9003173C9', '123456', '2016', 'BNC', '726', '99999999999999999', 16, '30001000000400002434', '2021-06-14'),
  ('EKU9003173C9', '123456', '2016', '', '1', '99999999999999999', 17, '30001000000400002434', '2021-11-07'),
  ('EKU9003173C9', '123456', '2016', 'RI', '1', '99999999999999999', 18, '30001000000400002434', '2021-11-07'),
  ('EKU9003173C9', '123456', '2016', 'NCI', '1', '99999999999999999', 19, '30001000000400002434', '2021-11-07'),
  ('EKU9003173C9', NULL, NULL, NULL, NULL, NULL, 20, '30001000000400002434', '1900-01-01');

ALTER TABLE sysDocumentIndex MODIFY tagref varchar(5);

CREATE TABLE `estado_sat` (
  `c_Estado` varchar(10) DEFAULT NULL,
  `c_Pais` varchar(10) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `idRegistro` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`idRegistro`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

INSERT INTO `estado_sat` (`c_Estado`, `c_Pais`, `estado`, `idRegistro`)
VALUES
  ('AGU', 'MEX', 'Aguascalientes', 1),
  ('BCN', 'MEX', 'Baja California', 2),
  ('BCS', 'MEX', 'Baja California Sur', 3),
  ('CAM', 'MEX', 'Campeche', 4),
  ('CHP', 'MEX', 'Chiapas', 5),
  ('CHH', 'MEX', 'Chihuahua', 6),
  ('COA', 'MEX', 'Coahuila', 7),
  ('COL', 'MEX', 'Colima', 8),
  ('DIF', 'MEX', 'Ciudad de México', 9),
  ('DUR', 'MEX', 'Durango', 10),
  ('GUA', 'MEX', 'Guanajuato', 11),
  ('GRO', 'MEX', 'Guerrero', 12),
  ('HID', 'MEX', 'Hidalgo', 13),
  ('JAL', 'MEX', 'Jalisco', 14),
  ('MEX', 'MEX', 'Estado de México', 15),
  ('MIC', 'MEX', 'Michoacán', 16),
  ('MOR', 'MEX', 'Morelos', 17),
  ('NAY', 'MEX', 'Nayarit', 18),
  ('NLE', 'MEX', 'Nuevo León', 19),
  ('OAX', 'MEX', 'Oaxaca', 20),
  ('PUE', 'MEX', 'Puebla', 21),
  ('QUE', 'MEX', 'Querétaro', 22),
  ('ROO', 'MEX', 'Quintana Roo', 23),
  ('SLP', 'MEX', 'San Luis Potosí', 24),
  ('SIN', 'MEX', 'Sinaloa', 25),
  ('SON', 'MEX', 'Sonora', 26),
  ('TAB', 'MEX', 'Tabasco', 27),
  ('TAM', 'MEX', 'Tamaulipas', 28),
  ('TLA', 'MEX', 'Tlaxcala', 29),
  ('VER', 'MEX', 'Veracruz', 30),
  ('YUC', 'MEX', 'Yucatán', 31),
  ('ZAC', 'MEX', 'Zacatecas', 32),
  ('AL', 'USA', 'Alabama', 33),
  ('AK', 'USA', 'Alaska', 34),
  ('AZ', 'USA', 'Arizona', 35),
  ('AR', 'USA', 'Arkansas', 36),
  ('CA', 'USA', 'California', 37),
  ('NC', 'USA', 'Carolina del Norte', 38),
  ('SC', 'USA', 'Carolina del Sur', 39),
  ('CO', 'USA', 'Colorado', 40),
  ('CT', 'USA', 'Connecticut', 41),
  ('ND', 'USA', 'Dakota del Norte', 42),
  ('SD', 'USA', 'Dakota del Sur', 43),
  ('DE', 'USA', 'Delaware', 44),
  ('FL', 'USA', 'Florida', 45),
  ('GA', 'USA', 'Georgia', 46),
  ('HI', 'USA', 'Hawái', 47),
  ('ID', 'USA', 'Idaho', 48),
  ('IL', 'USA', 'Illinois', 49),
  ('IN', 'USA', 'Indiana', 50),
  ('IA', 'USA', 'Iowa', 51),
  ('KS', 'USA', 'Kansas', 52),
  ('KY', 'USA', 'Kentucky', 53),
  ('LA', 'USA', 'Luisiana', 54),
  ('ME', 'USA', 'Maine', 55),
  ('MD', 'USA', 'Maryland', 56),
  ('MA', 'USA', 'Massachusetts', 57),
  ('MI', 'USA', 'Míchigan', 58),
  ('MN', 'USA', 'Minnesota', 59),
  ('MS', 'USA', 'Misisipi', 60),
  ('MO', 'USA', 'Misuri', 61),
  ('MT', 'USA', 'Montana', 62),
  ('NE', 'USA', 'Nebraska', 63),
  ('NV', 'USA', 'Nevada', 64),
  ('NJ', 'USA', 'Nueva Jersey', 65),
  ('NY', 'USA', 'Nueva York', 66),
  ('NH', 'USA', 'Nuevo Hampshire', 67),
  ('NM', 'USA', 'Nuevo México', 68),
  ('OH', 'USA', 'Ohio', 69),
  ('OK', 'USA', 'Oklahoma', 70),
  ('OR', 'USA', 'Oregón', 71),
  ('PA', 'USA', 'Pensilvania', 72),
  ('RI', 'USA', 'Rhode Island', 73),
  ('TN', 'USA', 'Tennessee', 74),
  ('TX', 'USA', 'Texas', 75),
  ('UT', 'USA', 'Utah', 76),
  ('VT', 'USA', 'Vermont', 77),
  ('VA', 'USA', 'Virginia', 78),
  ('WV', 'USA', 'Virginia Occidental', 79),
  ('WA', 'USA', 'Washington', 80),
  ('WI', 'USA', 'Wisconsin', 81),
  ('WY', 'USA', 'Wyoming', 82),
  ('ON', 'CAN', 'Ontario ', 83),
  ('QC', 'CAN', ' Quebec ', 84),
  ('NS', 'CAN', ' Nueva Escocia', 85),
  ('NB', 'CAN', 'Nuevo Brunswick ', 86),
  ('MB', 'CAN', ' Manitoba', 87),
  ('BC', 'CAN', ' Columbia Británica', 88),
  ('PE', 'CAN', ' Isla del Príncipe Eduardo', 89),
  ('SK', 'CAN', ' Saskatchewan', 90),
  ('AB', 'CAN', ' Alberta', 91),
  ('NL', 'CAN', ' Terranova y Labrador', 92),
  ('NT', 'CAN', ' Territorios del Noroeste', 93),
  ('YT', 'CAN', ' Yukón', 94),
  ('UN', 'CAN', ' Nunavut', 95);

CREATE TABLE `orderdetailscomplement` (
  `orderlineno` int(11) NOT NULL DEFAULT '0',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `fieldid` int(11) NOT NULL COMMENT 'fieldid de fielddetailscomplement',
  `stkcode` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL COMMENT 'valor del campo de fielddetailscomplement',
  PRIMARY KEY (`orderlineno`,`orderno`),
  KEY `OrderNo` (`orderno`),
  KEY `fieldid` (`fieldid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `fielddetailscomplement` (
  `fieldid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idcomplement` int(11) DEFAULT NULL,
  `xmlnode` varchar(255) DEFAULT NULL,
  `formname` varchar(255) DEFAULT NULL,
  `jsevent` varchar(255) DEFAULT NULL,
  `jsfunction` varchar(255) DEFAULT NULL,
  `placeholder` varchar(255) DEFAULT NULL,
  `fdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fregexp` varchar(500) DEFAULT NULL,
  `required` tinyint(1) DEFAULT NULL,
  `typeinput` varchar(20) DEFAULT NULL COMMENT 'tipo de input',
  `sqlrelacional` text COMMENT 'consulta que se realaciona con un select',
  `charlog` int(11) DEFAULT NULL COMMENT 'longitud del campo',
  PRIMARY KEY (`fieldid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `debtorcomplement` (
  `debtorno` varchar(60) NOT NULL DEFAULT '' COMMENT 'Codigo cliente ',
  `idcomplement` int(4) NOT NULL COMMENT 'Id de la tabla cfdicomplements',
  `valor` varchar(200) DEFAULT NULL COMMENT 'valor del input',
  `registroid` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`registroid`),
  KEY `debtorno` (`debtorno`),
  KEY `idcomplement` (`idcomplement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `fieldcomplement` (
  `fieldid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idcomplement` int(11) DEFAULT NULL,
  `xmlnode` varchar(255) DEFAULT NULL,
  `formname` varchar(255) DEFAULT NULL,
  `jsevent` varchar(255) DEFAULT NULL,
  `jsfunction` varchar(255) DEFAULT NULL,
  `placeholder` varchar(255) DEFAULT NULL,
  `fdate` date DEFAULT NULL,
  `fregexp` varchar(500) DEFAULT NULL,
  `required` tinyint(1) DEFAULT NULL,
  `typeinput` varchar(20) DEFAULT NULL COMMENT 'tipo de input',
  `sqlrelacional` text COMMENT 'consulta que se realaciona con un select',
  `charlog` int(11) DEFAULT NULL COMMENT 'longitud del campo',
  PRIMARY KEY (`fieldid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `cfdicomplement` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `complement` varchar(100) NOT NULL DEFAULT '' COMMENT 'Nombre del complemento CFDI',
  `complementfile` varchar(50) NOT NULL DEFAULT '' COMMENT 'Archivo que contiene la generacion del complemento',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Bandera para definir si esta activo',
  `condicion` text COMMENT 'Consulta para validar los complementos',
  `sufijo` varchar(5) DEFAULT NULL COMMENT 'sufijo del complemento para form',
  `variable` varchar(50) DEFAULT NULL COMMENT 'variable de la condición',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `usrcortecaja` (
  `u_cortecaja` int(11) NOT NULL AUTO_INCREMENT,
  `fechacorte` datetime DEFAULT NULL,
  `u_status` int(4) DEFAULT NULL,
  `tag` varchar(5) DEFAULT NULL,
  `userid` varchar(20) DEFAULT NULL,
  `noprocess` int(10) unsigned NOT NULL DEFAULT '1',
  `trandate` datetime DEFAULT NULL,
  PRIMARY KEY (`u_cortecaja`),
  KEY `fechacorte` (`fechacorte`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `debtortransmovs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tagref` int(11) NOT NULL,
  `transno` int(11) NOT NULL DEFAULT '0',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `origtrandate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trandate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `prd` smallint(6) NOT NULL DEFAULT '0',
  `settled` tinyint(4) NOT NULL DEFAULT '0',
  `reference` varchar(60) NOT NULL DEFAULT '',
  `tpe` char(2) NOT NULL DEFAULT '',
  `order_` int(11) NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '0',
  `ovamount` double NOT NULL DEFAULT '0',
  `ovgst` double NOT NULL DEFAULT '0',
  `ovfreight` double NOT NULL DEFAULT '0',
  `ovdiscount` double NOT NULL DEFAULT '0',
  `diffonexch` double NOT NULL DEFAULT '0',
  `alloc` double NOT NULL DEFAULT '0',
  `invtext` text,
  `shipvia` int(11) NOT NULL DEFAULT '0',
  `edisent` tinyint(4) NOT NULL DEFAULT '0',
  `consignment` varchar(15) NOT NULL DEFAULT '',
  `folio` varchar(50) DEFAULT NULL,
  `ref1` varchar(50) DEFAULT NULL,
  `ref2` varchar(50) DEFAULT NULL,
  `currcode` varchar(45) NOT NULL,
  `idgltrans` int(11) DEFAULT NULL,
  `userid` varchar(20) DEFAULT NULL,
  `interesxdevengar` double DEFAULT '0',
  `taxinteresxdevengar` double DEFAULT '0',
  `interesdevengado` double DEFAULT '0',
  `taxinteresdevengado` double DEFAULT '0',
  `totransno` int(11) DEFAULT '0',
  `totype` int(11) DEFAULT '0',
  `tofolio` varchar(45) DEFAULT '',
  `toorigtrandate` datetime DEFAULT NULL,
  `toduedate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  KEY `Order_` (`order_`),
  KEY `Prd` (`prd`),
  KEY `Tpe` (`tpe`),
  KEY `Type` (`type`),
  KEY `Settled` (`settled`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type_2` (`type`,`transno`),
  KEY `EDISent` (`edisent`),
  KEY `reference` (`reference`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*
  agrego: Jesus Santos 21/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se agrega el campo activo a tabla salesstatus
*/
ALTER TABLE salesstatus add activo int(11) DEFAULT 1 COMMENT '1 - Para estatus activo';

/*
  agrego: Jesus Santos 21/11/2019
  proceso: Balanza de comprobacion
  Tabla para sección de cuentas en balanza de comprobación
*/
CREATE TABLE `accountsection` (
  `sectionid` varchar(10) NOT NULL DEFAULT '0',
  `sectionname` text NOT NULL,
  `sectiontype` int(4) NOT NULL DEFAULT '1',
  `groupcode` varchar(10) DEFAULT NULL,
  `sectionnameing` text,
  `formula` varchar(500) DEFAULT NULL,
  `nivel` int(11) DEFAULT NULL,
  `charttipo` varchar(10) DEFAULT NULL,
  `naturaleza` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`sectionid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*
  agrego: Jesús Santos 22/11/2019
  proceso: Enviar Cotización
  Se cambia el tipo de campo tagref a tabla tags
*/
ALTER TABLE debtortrans MODIFY tagref varchar(5);
ALTER TABLE debtortransmovs MODIFY tagref varchar(5);
ALTER TABLE tags MODIFY tagref varchar(5);
/*
  agrego: Jesús Santos 22/11/2019
  proceso: Enviar Cotización
  Se cambia el tipo de campo tagref a tabla PDFTemplates
*/
ALTER TABLE PDFTemplates MODIFY tagref varchar(5);
/*
  agrego: Jesús Santos 22/11/2019
  proceso: Enviar Cotización
  Se crea tabla legalbusiness_email_methods
*/
CREATE TABLE `legalbusiness_email_methods` (
  `id_smtp` int(11) NOT NULL AUTO_INCREMENT,
  `legalid` int(11) NOT NULL,
  `metodo` varchar(10) NOT NULL,
  `cifrado` varchar(10) DEFAULT NULL,
  `desde` varchar(254) DEFAULT NULL,
  `requiere_autenticacion` smallint(1) DEFAULT '1',
  `servidor` varchar(254) DEFAULT NULL,
  `puerto` varchar(10) DEFAULT NULL,
  `usuario` varchar(254) DEFAULT NULL,
  `contrasena` varchar(254) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `userid` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`id_smtp`)
/*
  agrego: Jesús Santos 22/11/2019
  proceso: Enviar Cotización
  Se crea tabla sec_submodules_email_methods
*/

CREATE TABLE `sec_submodules_email_methods` (
  `id_metodo` int(11) NOT NULL AUTO_INCREMENT,
  `submoduleid` int(11) DEFAULT NULL,
  `id_smtp` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `userid` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id_metodo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/
alter table debtortrans add `nu_ue` varchar(10) NULL COMMENT 'Unidad Ejecutora';


/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/
CREATE TABLE `notesorders_invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transid` int(11) unsigned zerofill DEFAULT NULL,
  `transid_relacion` int(11) unsigned zerofill DEFAULT NULL,
  `monto` decimal(20,2) DEFAULT NULL,
  `transid_devolucion` int(11) DEFAULT NULL,
  `trandate` datetime DEFAULT '0000-00-00 00:00:00' COMMENT 'Fecha de registro',
  `userid` varchar(30) DEFAULT '' COMMENT 'usiaro que registro',
  `tiporelacion_relacion` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20582 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/

CREATE TABLE `salesorderstockserialsinvoiced` (
  `idinvoiced` int(11) NOT NULL AUTO_INCREMENT,
  `transno` int(11) NOT NULL DEFAULT '0',
  `type` smallint(6) DEFAULT NULL,
  `stockid` varchar(255) NOT NULL,
  `serialno` varchar(50) NOT NULL,
  `stockidparent` varchar(50) NOT NULL,
  `moveqty` double NOT NULL DEFAULT '0',
  `standardcost` double NOT NULL DEFAULT '0',
  `orderno` int(11) DEFAULT '0',
  `orderdetailno` int(11) DEFAULT '0',
  `stkmoveno` int(11) DEFAULT NULL,
  `localidad` varchar(50) DEFAULT NULL,
  `loccode` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idinvoiced`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/

CREATE TABLE `stockserialitems` (
  `stockid` varchar(255) NOT NULL,
  `loccode` varchar(6) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `serialno` varchar(50) CHARACTER SET utf8 NOT NULL,
  `expirationdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `quantity` double NOT NULL DEFAULT '0',
  `qualitytext` text NOT NULL,
  `ontransit` int(10) unsigned DEFAULT '0',
  `standardcost` double NOT NULL DEFAULT '0',
  `lastcostshipping` double DEFAULT NULL COMMENT 'Ultimo costo de embarque',
  `customs` varchar(150) DEFAULT NULL,
  `customs_number` int(11) DEFAULT NULL,
  `customs_date` date DEFAULT NULL,
  `pedimento` varchar(50) DEFAULT '',
  `wo` int(11) DEFAULT NULL,
  `fechainicio` datetime DEFAULT NULL,
  `secondfactorconversion` double DEFAULT NULL,
  `width` decimal(10,2) DEFAULT NULL,
  `large` decimal(10,2) DEFAULT NULL,
  `thickness` decimal(10,2) DEFAULT NULL,
  `flagstatus` int(11) DEFAULT '1' COMMENT '1 para paquete abierto, 0 para paquete cerrado',
  `qty_excess` double DEFAULT NULL,
  `localidad` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`stockid`,`serialno`,`loccode`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  KEY `serialno` (`serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/
CREATE TABLE `stockserialmoves` (
  `stkitmmoveno` int(11) NOT NULL AUTO_INCREMENT,
  `stockmoveno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(255) NOT NULL,
  `serialno` varchar(50) CHARACTER SET utf8 NOT NULL,
  `moveqty` double NOT NULL DEFAULT '0',
  `standardcost` double NOT NULL DEFAULT '0',
  `orderno` int(11) DEFAULT '0',
  `orderdetailno` int(11) DEFAULT '0',
  `secondfactorconversion` double DEFAULT NULL,
  `qty_excess` double DEFAULT NULL,
  `wo` int(11) DEFAULT NULL,
  `localidad` varchar(50) DEFAULT NULL,
  `masterparentid` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`stkitmmoveno`),
  KEY `StockMoveNo` (`stockmoveno`),
  KEY `StockID_SN` (`stockid`,`serialno`),
  KEY `serialno` (`serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/
CREATE TABLE `log_complemento_sustitucion` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `transno` int(11) NOT NULL DEFAULT '0',
  `folio` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `uuid` varchar(50) CHARACTER SET utf8 NOT NULL,
  `origtrandate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Fecha en la que se hizo la sustitucion',
  `sustitucion_from` int(11) DEFAULT NULL COMMENT 'Id del documento por el que se sustituyo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/
CREATE TABLE `usrdetallecortecaja` (
  `u_detallecortecaja` int(11) NOT NULL AUTO_INCREMENT,
  `referencia` varchar(100) DEFAULT NULL,
  `cuentapuente` varchar(50) DEFAULT NULL,
  `cuentacheques` varchar(50) DEFAULT NULL,
  `monto` double DEFAULT NULL,
  `fechacorte` datetime DEFAULT NULL,
  `u_status` int(4) DEFAULT NULL,
  `u_cortecaja` int(11) DEFAULT NULL,
  `fechadeposito` datetime DEFAULT NULL,
  `userid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`u_detallecortecaja`)
) ENGINE=MyISAM AUTO_INCREMENT=51643 DEFAULT CHARSET=latin1;


/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/

CREATE TABLE `usrdetallecortecaja_prepoliza` (
  `u_detallecortecaja` int(11) NOT NULL AUTO_INCREMENT,
  `referencia` varchar(100) DEFAULT NULL,
  `cuentapuente` varchar(50) DEFAULT NULL,
  `cuentacheques` varchar(50) DEFAULT NULL,
  `monto` double DEFAULT NULL,
  `fechacorte` datetime DEFAULT NULL,
  `u_status` int(4) DEFAULT NULL,
  `u_cortecaja` int(11) DEFAULT NULL,
  `fechadeposito` datetime DEFAULT NULL,
  `userid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`u_detallecortecaja`)
) ENGINE=MyISAM AUTO_INCREMENT=28238 DEFAULT CHARSET=latin1;

UPDATE sec_functions
SET 
title='Permiso Pase de Cobro Caja', 
shortdescription='Permiso Pase de Cobro Caja', 
comments='Permiso Pase de Cobro Caja', 
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='170'
;

UPDATE sec_functions_new
SET 
title='Permiso Pase de Cobro Caja', 
shortdescription='Permiso Pase de Cobro Caja', 
comments='Permiso Pase de Cobro Caja', 
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='170'
;

update salesstatus set statusname = 'Pase de Cobro' where statusid = 1;

UPDATE sec_functions
SET 
title='Permiso Recibo de Pago Caja', 
shortdescription='Permiso Recibo de Pago Caja', 
comments='Permiso Recibo de Pago Caja', 
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='1773'
;

UPDATE sec_functions_new
SET 
title='Permiso Recibo de Pago Caja', 
shortdescription='Permiso Recibo de Pago Caja', 
comments='Permiso Recibo de Pago Caja', 
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='1773'
;

update salesstatus set statusname = 'Recibo de Pago' where statusid = 13;

ALTER TABLE tb_sec_users_ue MODIFY ln_aux1 varchar(10) NOT NULL DEFAULT '' COMMENT 'Concatenacion ur ue';

delete from systypescat where typeid in (49,52);

INSERT INTO `systypescat` (`typeid`, `typename`, `typeno`, `naturalezacontable`, `fiscal`, `EnvioFiscal`, `flaglastinv`, `nu_estado_presupuesto`, `nu_usar_disponible`, `nu_activo`, `ln_descripcion_corta`, `nu_inventario_inicial`, `nu_inventario_entrada`, `nu_inventario_salida`, `nu_usar_disponible_radicado`, `nu_gestion_polizas`, `nu_poliza_ingreso`, `nu_poliza_egreso`, `nu_poliza_diario`, `nu_poliza_visual`, `nu_estado_ministrado`, `nu_estado_radicado`, `nu_usar_modificado`, `nu_usar_por_liberar`, `nu_usar_por_radicar`, `nu_panel_pagos`, `nu_tesoreria_pagos`, `nu_rectificaciones_pagos`, `nu_usar_liberado_disp`, `nu_usar_radicado_disp`, `nu_mega_poliza`, `nu_estado_presupuesto_ingreso`)
VALUES
  (49, 'Alta Presupuesto Egreso', 1, 0, 1, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
  (52, 'Alta Presupuesto Ingreso', 1, 0, 1, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL);

ALTER TABLE tb_ue_folio_poliza MODIFY ln_aux1 varchar(10) DEFAULT NULL COMMENT 'Auxiliar 1';

INSERT INTO `config_reportes_` (`reporte`, `parametro`, `valor`, `tagref`, `grupo`, `ordengrupo`, `ind_activo`, `dtm_fecha_alta`)
VALUES
  ('SituacionFinanciera', 'RSituacionFinancieraPReservas', '3.2.5', '10', 'grupo', 1, 1, '2018-04-18 13:48:43');

/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/
CREATE TABLE `tb_contratos_contribuyentes` (
  `id_contratos` int(11) NOT NULL AUTO_INCREMENT,
  `sn_periodicidad` varchar(255) NOT NULL DEFAULT '',
  `nu_estatus` int(10) unsigned NOT NULL,
  `nu_recargos` int(10) unsigned NOT NULL,
  `nu_multa` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_contratos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
  agrego: Eduardo López 05/12/2019
  proceso: Ingresos
  Configuración ingresos, números de función y accesos
*/
delete from sec_funxprofile where functionid in (170, 1968, 1773);
delete from sec_favxuser where functionid in (170, 1968, 1773);
delete from sec_funxuser where functionid in (170, 1968, 1773);

UPDATE sec_functions 
SET 
title='Pase de Cobro', 
shortdescription='Pase de Cobro', 
comments='Pase de Cobro', 
active='1'
WHERE functionid='1968'
;
UPDATE sec_functions_new
SET 
title='Pase de Cobro', 
shortdescription='Pase de Cobro', 
comments='Pase de Cobro', 
active='1'
WHERE functionid='1968'
;

UPDATE sec_functions 
SET 
title='Caja', 
shortdescription='Caja', 
comments='Caja', 
active='1',
url='pos/index.html',
type='Funcion',
submoduleid=1,
categoryid=1
WHERE functionid='1773'
;
UPDATE sec_functions_new
SET 
title='Caja', 
shortdescription='Caja', 
comments='Caja', 
active='1',
url='pos/index.html',
type='Funcion',
submoduleid=1,
categoryid=1
WHERE functionid='1773'
;

UPDATE sec_functions 
SET 
title='Panel de Pases de Cobro', 
shortdescription='Panel de Pases de Cobro', 
comments='Panel de Pases de Cobro', 
active='1'
WHERE functionid='602'
;
UPDATE sec_functions_new 
SET 
title='Panel de Pases de Cobro', 
shortdescription='Panel de Pases de Cobro', 
comments='Panel de Pases de Cobro', 
active='1'
WHERE functionid='602'
;

UPDATE sec_functions 
SET 
title='Panel de Recibos de Pago', 
shortdescription='Panel de Recibos de Pago', 
comments='Panel de Recibos de Pago', 
active='1'
WHERE functionid='205'
;
UPDATE sec_functions_new
SET 
title='Panel de Recibos de Pago', 
shortdescription='Panel de Recibos de Pago', 
comments='Panel de Recibos de Pago', 
active='1'
WHERE functionid='205'
;

/*
  agrego: Eduardo López 05/12/2019
  proceso: Ingresos
  Estatus corte de caja
*/
CREATE TABLE `tb_cortecaja_estatus` (
  `nu_tipo` int(11) NOT NULL,
  `sn_nombre` varchar(250) DEFAULT NULL COMMENT 'Nombre del Estatus',
  PRIMARY KEY (`nu_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tb_cortecaja_estatus` (`nu_tipo`, `sn_nombre`)
VALUES
  (0, 'Abierto'),
  (1, 'Prepoliza'),
  (2, 'Cerrado');

/*
  agrego: Eduardo López 06/12/2019
  proceso: Ingresos
  Configuración para contratos ingresos
*/
delete from sec_funxprofile where functionid in (2510);
delete from sec_favxuser where functionid in (2510);
delete from sec_funxuser where functionid in (2510);

delete from sec_funxprofile where functionid in (2509);
delete from sec_favxuser where functionid in (2509);
delete from sec_funxuser where functionid in (2509);

UPDATE sec_functions 
SET 
title='Panel de Contratos de Contribuyentes', 
shortdescription='Panel de Contratos de Contribuyentes', 
comments='Panel de Contratos de Contribuyentes', 
active='1',
submoduleid='1',
categoryid='1',
type='Funcion'
WHERE functionid='2510'
;
UPDATE sec_functions_new
SET 
title='Panel de Contratos de Contribuyentes', 
shortdescription='Panel de Contratos de Contribuyentes', 
comments='Panel de Contratos de Contribuyentes', 
active='1',
submoduleid='1',
categoryid='1',
type='Funcion'
WHERE functionid='2510'
;

UPDATE sec_functions 
SET 
title='Configuración de Contratos de Contribuyentes', 
shortdescription='Configuración de Contratos de Contribuyentes', 
comments='Configuración de Contratos de Contribuyentes', 
active='1',
submoduleid='1',
categoryid='1',
type='Funcion'
WHERE functionid='2509'
;
UPDATE sec_functions_new 
SET 
title='Configuración de Contratos de Contribuyentes', 
shortdescription='Configuración de Contratos de Contribuyentes', 
comments='Configuración de Contratos de Contribuyentes', 
active='1',
submoduleid='1',
categoryid='1',
type='Funcion'
WHERE functionid='2509'
;

ALTER TABLE `tb_viaticos` MODIFY `id_nu_ue` varchar(10) DEFAULT NULL COMMENT 'Identificador de la unidad ejecutora';

ALTER TABLE `tb_proceso_compra` MODIFY `id_nu_ue` varchar(10) DEFAULT NULL COMMENT 'Folio alfanumérico de la compra en proceso';

ALTER TABLE debtortrans ADD cuenta_banco varchar(50) DEFAULT NULL COMMENT 'Cuenta de banco de recibo de pago para corte de caja';

ALTER TABLE `tb_solicitudes_almacen` MODIFY `ln_ue` varchar(10) DEFAULT NULL COMMENT 'Relación a unidad ejecutora';

/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/

CREATE TABLE `tb_propiedades_atributos` (
  `id_propiedades_atributos` int(11) NOT NULL AUTO_INCREMENT,
  `id_folio_contrato` int(11) NOT NULL,
  `id_folio_configuracion` int(11) NOT NULL,
  `id_etiqueta_atributo` int(11) NOT NULL,
  `ln_valor` varchar(255) DEFAULT NULL,
  `nu_activo` varchar(1) DEFAULT '1',
  `dtm_fecha_efectiva` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_propiedades_atributos`),
  KEY `id_folio_contrato` (`id_folio_contrato`),
  KEY `id_folio_configuracion` (`id_folio_configuracion`),
  KEY `id_etiqueta_atributo` (`id_etiqueta_atributo`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;


/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/

CREATE TABLE `tb_contratos_contribuyentes` (
  `id_contratos` int(11) NOT NULL AUTO_INCREMENT,
  `id_loccode` varchar(50) NOT NULL,
  `nu_estatus` int(10) unsigned NOT NULL,
  `nu_recargos` int(10) unsigned NOT NULL,
  `nu_multa` int(10) unsigned NOT NULL,
  `userid` varchar(20) NOT NULL DEFAULT '',
  `dtm_fecha_efectiva` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contratos`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*
  agrego: Nombre 16/12/2019
  proceso: Claves presupuestales
  Modificacion de campos
*/
ALTER TABLE chartdetailsbudgetbytag MODIFY ln_aux1 varchar(20) DEFAULT NULL COMMENT 'Campo para el auxiliar 1 de la clave presupuestal';

ALTER TABLE tb_ante_claves MODIFY ln_aux1 varchar(20) DEFAULT NULL COMMENT 'Campo para el auxiliar 1 de la clave presupuestal';

ALTER TABLE tb_cat_unidades_ejecutoras MODIFY ln_aux1 varchar(20) DEFAULT NULL COMMENT 'Campo para el auxiliar 1 de la clave presupuestal';

ALTER TABLE tb_sec_users_ue MODIFY ln_aux1 varchar(20) NOT NULL DEFAULT '' COMMENT 'Concatenacion ur ue';

ALTER TABLE tb_ue_folio_poliza MODIFY ln_aux1 varchar(20) DEFAULT NULL COMMENT 'Auxiliar 1';

ALTER TABLE purchorders ADD ln_codigo_expediente varchar(20) DEFAULT NULL COMMENT 'Código Expediente';

ALTER TABLE salesorders add ln_ue varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora';

ALTER TABLE debtortrans ADD nu_cortecaja smallint(1) DEFAULT '0' COMMENT 'Bandera proceso de corte de caja';

ALTER TABLE usrdetallecortecaja add nu_foliocorte int(11) DEFAULT '0' COMMENT 'Folio de la póliza de cierre';

ALTER TABLE debtortrans ADD nu_foliocorte int(11) DEFAULT '0' COMMENT 'Folio de la póliza de cierre';

CREATE TABLE `tb_debtortrans_forma_pago` (
  `nu_mov` int(11) NOT NULL AUTO_INCREMENT,
  `dtm_fecha` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
  `sn_userid` varchar(255) DEFAULT NULL,
  `nu_type` int(11) NOT NULL DEFAULT '0' COMMENT 'Tipo de Movimiento',
  `nu_transno` int(11) NOT NULL DEFAULT '0' COMMENT 'Numero de Movimiento',
  `ln_paymentid` varchar(5) DEFAULT NULL COMMENT 'Código de la forma de pago',
  `nu_cantidad` double NOT NULL DEFAULT '0' COMMENT 'Cantidad de la forma de pago',
  KEY `nu_mov` (`nu_mov`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE usrcortecaja add ln_ue varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora'

ALTER TABLE purchorders ADD ln_UsoCFDI varchar(5) DEFAULT NULL COMMENT 'Uso del CFDI';

CREATE TABLE `tb_reportes_contratos` (
  `nu_tipo` int(11) NOT NULL AUTO_INCREMENT,
  `sn_nombre` varchar(250) DEFAULT NULL COMMENT 'Nombre del Reporte',
  `sn_reporte` varchar(250) DEFAULT NULL COMMENT 'Nombre del Reporte Jasper',
  `sn_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`nu_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tb_reportes_contratos` (`nu_tipo`, `sn_nombre`, `sn_reporte`, `sn_activo`)
VALUES
  (1, 'Boleta de Estacionamiento', 'boleta_estacionamiento', '1');

CREATE TABLE `sec_contratoxuser` (
  `userid` varchar(255) NOT NULL DEFAULT '',
  `id_contratos` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`userid`,`id_contratos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sec_contratoxuser` (`userid`, `id_contratos`)
SELECT www_users.userid, tb_contratos_contribuyentes.id_contratos
FROM www_users
JOIN tb_contratos_contribuyentes
;

/*
  agrego: Nombre 01/11/2019
  proceso: Busqueda De Pedidos De Venta
  Se crea tabla salesman
*/
ALTER TABLE www_users Add ln_ue varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora Default';

ALTER TABLE salesorders Add ln_tagref_pase varchar(5) DEFAULT NULL COMMENT 'Unidad Responsable Pase de Cobro';
ALTER TABLE salesorders Add ln_ue_pase varchar(10) DEFAULT NULL COMMENT 'Unidad Ejecutora Pase de Cobro';

UPDATE sec_functions 
SET 
title='Permiso para modificar descuento en ingresos', 
shortdescription='Permiso para modificar descuento en ingresos', 
comments='Permiso para modificar descuento en ingresos', 
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='1777'
;
UPDATE sec_functions_new 
SET 
title='Permiso para modificar descuento en ingresos', 
shortdescription='Permiso para modificar descuento en ingresos', 
comments='Permiso para modificar descuento en ingresos', 
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='1777'
;

ALTER TABLE salesorders Add txt_pagador TEXT DEFAULT NULL COMMENT 'Pagador del Pase de Cobro';

ALTER TABLE bankaccounts Add nu_orden int(11) DEFAULT 0 COMMENT 'Orden al visualizar';

ALTER TABLE tb_contratos_contribuyentes Add nu_val_atributo1 int(11) DEFAULT 0 COMMENT 'Validar atributo 1';
ALTER TABLE tb_contratos_contribuyentes Modify nu_val_atributo1 int(11) DEFAULT 0 COMMENT 'Número de atributo a validar al capturar nuevo';

/*
  agrego: Nombre 22/11/2019
  proceso: Reportes LDF
  Tablas - Estado Analítico de Ingresos Detallado - LDF
*/
CREATE TABLE `tb_ldf_conf_ingresos_detallado1` (
  `idDeta1` int(11) NOT NULL AUTO_INCREMENT,
  `ln_etiqueta` text DEFAULT NULL COMMENT 'Etiqueta',
  `ln_etiqueta2` text DEFAULT NULL COMMENT 'Etiqueta 2',
  `nu_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`idDeta1`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tb_ldf_conf_ingresos_detallado2` (
  `idDeta2` int(11) NOT NULL AUTO_INCREMENT,
  `idDeta1` int(11) DEFAULT NULL COMMENT 'Id del detalle 1',
  `ln_etiqueta` text DEFAULT NULL COMMENT 'Etiqueta',
  `nu_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`idDeta2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tb_ldf_conf_ingresos_detallado3` (
  `idDeta3` int(11) NOT NULL AUTO_INCREMENT,
  `idDeta2` int(11) DEFAULT NULL COMMENT 'Id del detalle 2',
  `ln_etiqueta` text DEFAULT NULL COMMENT 'Etiqueta',
  `nu_activo` int(11) DEFAULT NULL COMMENT 'Activo o Inactivo',
  PRIMARY KEY (`idDeta3`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tb_ldf_detalle2_cri` (
  `idDeta2` varchar(255) NOT NULL DEFAULT '',
  `rtc` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idDeta2`,`rtc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tb_ldf_detalle3_cri` (
  `idDeta3` varchar(255) NOT NULL DEFAULT '',
  `rtc` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idDeta3`,`rtc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


UPDATE sec_functions 
SET 
title='Permiso Cambiar Fecha Recibo', 
shortdescription='Permiso Cambiar Fecha Recibo', 
comments='Permiso Cambiar Fecha Recibo',
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='218'
;
UPDATE sec_functions_new
SET 
title='Permiso Cambiar Fecha Recibo', 
shortdescription='Permiso Cambiar Fecha Recibo', 
comments='Permiso Cambiar Fecha Recibo',
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='218'
;

/*
  agrego: Nombre 22/11/2019
  proceso: Documento de cancelacion
  Documento para cancelación de recibos de pago
*/
INSERT INTO `systypescat` (`typeid`, `typename`, `typeno`, `naturalezacontable`, `fiscal`, `EnvioFiscal`, `flaglastinv`, `nu_estado_presupuesto`, `nu_usar_disponible`, `nu_activo`, `ln_descripcion_corta`, `nu_inventario_inicial`, `nu_inventario_entrada`, `nu_inventario_salida`, `nu_usar_disponible_radicado`, `nu_gestion_polizas`, `nu_poliza_ingreso`, `nu_poliza_egreso`, `nu_poliza_diario`, `nu_poliza_visual`, `nu_estado_ministrado`, `nu_estado_radicado`, `nu_usar_modificado`, `nu_usar_por_liberar`, `nu_usar_por_radicar`, `nu_panel_pagos`, `nu_tesoreria_pagos`, `nu_rectificaciones_pagos`, `nu_usar_liberado_disp`, `nu_usar_radicado_disp`, `nu_mega_poliza`, `nu_estado_presupuesto_ingreso`, `nu_usar_disponible_ingreso`)
VALUES
  (14, 'Cancelación de Recibo de Pago', 0, -1, 1, 1, 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL);

INSERT INTO `systypescusttrans` (`typeid`, `typename`, `typeno`)
VALUES
  (14, 'Cancelación de Recibo de Pago', 0);

INSERT INTO `systypesinvoice` (`typeid`, `typename`, `typeno`)
VALUES
  (14, 'Cancelación de Recibo de Pago', 0);

INSERT INTO `systypesinvtrans` (`typeid`, `typename`, `typeno`)
VALUES
  (14, 'Cancelación de Recibo de Pago', 0);

UPDATE sec_functions 
SET 
title='Permiso Cancelar Recibo', 
shortdescription='Permiso Cancelar Recibo', 
comments='Permiso Cancelar Recibo', 
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='206'
;
UPDATE sec_functions_new 
SET 
title='Permiso Cancelar Recibo', 
shortdescription='Permiso Cancelar Recibo', 
comments='Permiso Cancelar Recibo', 
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='206'
;

UPDATE sec_functions
SET
title='Cancelar Pase de Cobro',
shortdescription='Cancelar Pase de Cobro',
comments='Cancelar Pase de Cobro',
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='196'
;
UPDATE sec_functions_new
SET
title='Cancelar Pase de Cobro',
shortdescription='Cancelar Pase de Cobro',
comments='Cancelar Pase de Cobro',
active='1',
submoduleid='1',
categoryid='1'
WHERE functionid='196'
;

/*
  agrego: Nombre 17/04/2020
  proceso: Reportes Ingresos
  Tablas - Configurar objetos principales por reporte
*/
CREATE TABLE `sec_objetoprincipalxreporte` (
  `functionid` int(10) unsigned NOT NULL,
  `loccode` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`functionid`,`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*
  agrego: Nombre 29/04/2020
  proceso: Ingresos
  Campo para identificar si el pase de cobro proviene del servicio web
*/
ALTER TABLE salesorders Add sn_servicio_web int(11) DEFAULT NULL COMMENT 'Identificador Servicio Web';

/*
  agrego: Nombre 12/05/2020
  proceso: Ingresos
  Tabla para el registro del ingreso de enero y febrero de predial, 
  ya que no existe información de pases de cobro y recibos. Se agrego
  y se toma en cuenta en varios reportes de ingresos
*/
CREATE TABLE `tb_predial_montos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(255) DEFAULT NULL COMMENT 'Objeto Parcial',
  `amt_monto` double NOT NULL DEFAULT 0 COMMENT 'Monto',
  `dtm_fecha` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
  agrego: Nombre 13/11/2020
  proceso: Ingresos
  Campo para bandera de filtro en el panel de documentos
*/
ALTER TABLE tb_cat_atributos_contrato Add sn_filtro_panel int(11) DEFAULT 0 COMMENT 'Bandera filtro panel';






