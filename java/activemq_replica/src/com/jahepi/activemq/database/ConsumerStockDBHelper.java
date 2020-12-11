package com.jahepi.activemq.database;

import java.sql.PreparedStatement;

import org.apache.log4j.Logger;

import com.jahepi.activemq.Utils;
import com.jahepi.activemq.database.Database.DBPreparedStatement;
import com.jahepi.activemq.dto.QueueMessageStocks;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ConsumerStockDBHelper {
	
	final static Logger logger = Logger.getLogger(ConsumerStockDBHelper.class);

	private Database database;
	private ConfigData config;

	public ConsumerStockDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public boolean saveMessage(QueueMessageStocks msg) {
		String sql = "", sqlLog = "", stockid = "", line = "";
		String finalLogSql = "";
		DBPreparedStatement dbPreparedStatement;
		PreparedStatement ps;
		boolean success = true;
		if(msg.getSAPActualiza() == 1){
			sql = "UPDATE stockmaster " +
					" SET categoryid = ?,description = ?,longdescription = ?,manufacturer = ?, units = ?,mbflag = ?,"
					+ "actualcost = ?,lastcost = ?, materialcost = ?,labourcost = ?,overheadcost = ?,lowestlevel = ?,"
					+ "discontinued = ?,controlled = ?,eoq = ?,volume = ?,kgs = ?,barcode = ?,discountcategory = ?,"
					+ "taxcatid = ?,taxcatidret = ?,serialised = ?,appendfile = ?,perishable = ?,decimalplaces = ?,"
					+ "netweight = ?,idclassproduct = ?,fecha_modificacion = ?,height = ?,width = ?,large = ?,"
					+ "factorsecondary = ?,stockneodata = ?, purchgroup = ?,idjerarquia = ?,addunits = ?,secuunits = ?,"
					+ "recipeunits = ?,factorrecipe = ?,addcategory = ?,deliverydays = ?,tolerancedays = ?,estatusstock = ?, "
					+ "eq_conversion_costo = ?,unitstemporal = ?,spes = ?,stockautor = ?,lastcurcostdate = ?,nextserialno = ?,"
					+ "pansize = ?,shrinkfactor = ?,stocksupplier = ?,securitypoint = ?,pkg_type = ?,idetapaflujo = ?,"
					+ "flagcommission = ?,fijo = ?,stockupdate = ?,isbn = ?,grade = ?,subject = ?,deductibleflag = ?,"
					+ "u_typeoperation = ?,typeoperationdiot = ?,fichatecnica = ?,percentfactorigi = ?,OrigenCountry = ?,"
					+ "OrigenDate = ?,inpdfgroup = ?,flagadvance = ?,eq_stockid = ?,unitequivalent = ?,factorconversionpaq = ?,"
					+ "factorconversionpz = ?,unitssec = ?,unitstthree = ?,factorthree = ?,factorprimary = ?" +
					" WHERE stockid = ?";
			
			sqlLog = "UPDATE stockmaster " +
					" SET categoryid = '%s',description = '%s',longdescription = '%s',manufacturer = '%s', units = '%s',mbflag = '%s',"
					+ "actualcost = '%s',lastcost = '%s', materialcost = '%s',labourcost = '%s',overheadcost = '%s',lowestlevel = '%s',"
					+ "discontinued = '%s',controlled = '%s',eoq = '%s',volume = '%s',kgs = '%s',barcode = '%s',discountcategory = '%s',"
					+ "taxcatid = '%s',taxcatidret = '%s',serialised = '%s',appendfile = '%s',perishable = '%s',decimalplaces = '%s',"
					+ "netweight = '%s',idclassproduct = '%s',fecha_modificacion = '%s',height = '%s',width = '%s',large = '%s',"
					+ "factorsecondary = '%s',stockneodata = '%s', purchgroup = '%s',idjerarquia = '%s',addunits = '%s',secuunits = '%s',"
					+ "recipeunits = '%s',factorrecipe = '%s',addcategory = '%s',deliverydays = '%s',tolerancedays = '%s',estatusstock = '%s',"
					+ " eq_conversion_costo = '%s',unitstemporal = '%s',spes = '%s',stockautor = '%s',lastcurcostdate = '%s',nextserialno = '%s',"
					+ "pansize = '%s',shrinkfactor = '%s',stocksupplier = '%s',securitypoint = '%s',pkg_type = '%s',idetapaflujo = '%s',"
					+ "flagcommission = '%s',fijo = '%s',stockupdate = '%s',isbn = '%s',grade = '%s',subject = '%s',deductibleflag = '%s',"
					+ "u_typeoperation = '%s',typeoperationdiot = '%s',fichatecnica = '%s',percentfactorigi = '%s',OrigenCountry = '%s',"
					+ "OrigenDate = '%s',inpdfgroup = '%s',flagadvance = '%s',eq_stockid = '%s',unitequivalent = '%s',factorconversionpaq = '%s',"
					+ "factorconversionpz = '%s',unitssec = '%s',unitstthree = '%s',factorthree = '%s',factorprimary = '%s'" +
				" WHERE stockid = '%s'";
			
			dbPreparedStatement = this.database.getPreparedStatement(sql);
			ps = dbPreparedStatement.getPreparedStatement();
			if (ps != null) {
				try {
					
					ps.setString(1, msg.getCategoryid());
					ps.setString(2, msg.getDescription());
					ps.setString(3, msg.getLongdescription());
					ps.setString(4, msg.getManufacturer()); 
					ps.setString(5, msg.getUnits());
					ps.setString(6, msg.getMbflag());
					ps.setString(7, msg.getActualcost());
					ps.setString(8, msg.getLastcost()); 
					ps.setString(9, msg.getMaterialcost());
					ps.setString(10, msg.getLabourcost());
					ps.setString(11, msg.getOverheadcost());
					ps.setString(12, msg.getLowestlevel());
					ps.setString(13, msg.getDiscontinued());
					ps.setString(14, msg.getControlled());
					ps.setString(15, msg.getEoq());
					ps.setString(16, msg.getVolume());
					ps.setString(17, msg.getKgs());
					ps.setString(18, msg.getBarcode());
					ps.setString(19, msg.getDiscountcategory());
					ps.setString(20, msg.getTaxcatid());
					ps.setString(21, msg.getTaxcatidret());
					ps.setString(22, msg.getSerialised());
					ps.setString(23, msg.getAppendfile());
					ps.setString(24, msg.getPerishable());
					ps.setString(25, msg.getDecimalplaces());
					ps.setString(26, msg.getNetweight());
					ps.setString(27, msg.getIdclassproduct());
					ps.setString(28, msg.getFecha_modificacion());
					ps.setString(29, msg.getHeight());
					ps.setString(30, msg.getWidth());
					ps.setString(31, msg.getLarge());
					ps.setString(32, msg.getFactorsecondary());
					ps.setString(33, msg.getStockneodata()); 
					ps.setString(34, msg.getPurchgroup());
					ps.setString(35, msg.getIdjerarquia());
					ps.setString(36, msg.getAddunits());
					ps.setString(37, msg.getSecuunits());
					ps.setString(38, msg.getRecipeunits());
					ps.setString(39, msg.getFactorrecipe());
					ps.setString(40, msg.getAddcategory());
					ps.setString(41, msg.getDeliverydays());
					ps.setString(42, msg.getTolerancedays());
					ps.setString(43, msg.getEstatusstock()); 
					ps.setString(44, msg.getEq_conversion_costo());
					ps.setString(45, msg.getUnitstemporal());
					ps.setString(46, msg.getSpes());
					ps.setString(47, msg.getStockautor());
					ps.setString(48, msg.getLastcurcostdate());
					ps.setString(49, msg.getNextserialno());
					ps.setString(50, msg.getPansize());
					ps.setString(51, msg.getShrinkfactor());
					ps.setString(52, msg.getStocksupplier());
					ps.setString(53, msg.getSecuritypoint());
					ps.setString(54, msg.getPkg_type());
					ps.setString(55, msg.getIdetapaflujo());
					ps.setString(56, msg.getFlagcommission());
					ps.setString(57, msg.getFijo());
					ps.setString(58, msg.getStockupdate());
					ps.setString(59, msg.getIsbn());
					ps.setString(60, msg.getGrade());
					ps.setString(61, msg.getSubject());
					ps.setString(62, msg.getDeductibleflag());
					ps.setString(63, msg.getU_typeoperation());
					ps.setString(64, msg.getTypeoperationdiot());
					ps.setString(65, msg.getFichatecnica());
					ps.setString(66, msg.getPercentfactorigi());
					ps.setString(67, msg.getOrigenCountry());
					ps.setString(68, msg.getOrigenDate());
					ps.setString(69, msg.getInpdfgroup());
					ps.setString(70, msg.getFlagadvance());
					ps.setString(71, msg.getEq_stockid());
					ps.setString(72, msg.getUnitequivalent());
					ps.setString(73, msg.getFactorconversionpaq());
					ps.setString(74, msg.getFactorconversionpz());
					ps.setString(75, msg.getUnitssec());
					ps.setString(76, msg.getUnitstthree());
					ps.setString(77, msg.getFactorthree());
					ps.setString(78, msg.getFactorprimary());
					ps.setString(79, msg.getStockid());

					// SQL Log
					finalLogSql = String.format(sqlLog,
							msg.getCategoryid(),
							msg.getDescription(),
							msg.getLongdescription(),
							msg.getManufacturer(),
							msg.getUnits(),
							msg.getMbflag(),
							msg.getActualcost(),
							msg.getLastcost(),
							msg.getMaterialcost(),
							msg.getLabourcost(),
							msg.getOverheadcost(),
							msg.getLowestlevel(),
							msg.getDiscontinued(),
							msg.getControlled(),
							msg.getEoq(),
							msg.getVolume(),
							msg.getKgs(),
							msg.getBarcode(),
							msg.getDiscountcategory(),
							msg.getTaxcatid(),
							msg.getTaxcatidret(),
							msg.getSerialised(),
							msg.getAppendfile(),
							msg.getPerishable(),
							msg.getDecimalplaces(),
							msg.getNetweight(),
							msg.getIdclassproduct(),
							msg.getFecha_modificacion(),
							msg.getHeight(),
							msg.getWidth(),
							msg.getLarge(),
							msg.getFactorsecondary(),
							msg.getStockneodata(),
							msg.getPurchgroup(),
							msg.getIdjerarquia(),
							msg.getAddunits(),
							msg.getSecuunits(),
							msg.getRecipeunits(),
							msg.getFactorrecipe(),
							msg.getAddcategory(),
							msg.getDeliverydays(),
							msg.getTolerancedays(),
							msg.getEstatusstock(),
							msg.getEq_conversion_costo(),
							msg.getUnitstemporal(),
							msg.getSpes(),
							msg.getStockautor(),
							msg.getLastcurcostdate(),
							msg.getNextserialno(),
							msg.getPansize(),
							msg.getShrinkfactor(),
							msg.getStocksupplier(),
							msg.getSecuritypoint(),
							msg.getPkg_type(),
							msg.getIdetapaflujo(),
							msg.getFlagcommission(),
							msg.getFijo(),
							msg.getStockupdate(),
							msg.getIsbn(),
							msg.getGrade(),
							msg.getSubject(),
							msg.getDeductibleflag(),
							msg.getU_typeoperation(),
							msg.getTypeoperationdiot(),
							msg.getFichatecnica(),
							msg.getPercentfactorigi(),
							msg.getOrigenCountry(),
							msg.getOrigenDate(),
							msg.getInpdfgroup(),
							msg.getFlagadvance(),
							msg.getEq_stockid(),
							msg.getUnitequivalent(),
							msg.getFactorconversionpaq(),
							msg.getFactorconversionpz(),
							msg.getUnitssec(),
							msg.getUnitstthree(),
							msg.getFactorthree(),
							msg.getFactorprimary(),
							msg.getStockid());
					
					stockid = msg.getStockid();
					
					logger.debug(finalLogSql);

					ps.executeUpdate();

					// success = this.database.executeUpdate(msg.getSql());
					success = true;

				} catch (Exception e) {
					
					success = false;
					if (this.config.get("onErrorSaveFile").equals("1")) {
						success = Utils.saveFile(config, finalLogSql, "Productos", stockid, line);
					}
					
					logger.error("Error desconocido", e);
				} finally {
					dbPreparedStatement.close();
				}
			}
		}else if(msg.getSAPActualiza() == 2){
			sql = "INSERT INTO stockmaster (stockid,categoryid,description,longdescription,manufacturer, "
					+ "units,mbflag,actualcost,lastcost, materialcost,labourcost,overheadcost,lowestlevel,"
					+ "discontinued,controlled,eoq,volume,kgs,barcode,discountcategory,taxcatid,taxcatidret,"
					+ "serialised,appendfile,perishable,decimalplaces,netweight,idclassproduct,fecha_modificacion,"
					+ "height,width,large,factorsecondary,stockneodata, purchgroup,idjerarquia,"
					+ "addunits,secuunits,recipeunits,factorrecipe,addcategory,deliverydays,tolerancedays,estatusstock, "
					+ "eq_conversion_costo,unitstemporal,SAPActualiza, spes,stockautor,lastcurcostdate,nextserialno,"
					+ "pansize,shrinkfactor,stocksupplier,securitypoint,pkg_type,idetapaflujo,flagcommission,fijo,"
					+ "stockupdate,isbn,grade,subject,deductibleflag,u_typeoperation,typeoperationdiot,fichatecnica,"
					+ "percentfactorigi,OrigenCountry,OrigenDate,inpdfgroup,flagadvance,eq_stockid,unitequivalent,"
					+ "factorconversionpaq,factorconversionpz,unitssec,unitstthree,factorthree,factorprimary) "	+
					 " VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,"
					 + "?,?,?,?,?,?,0,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			sqlLog = "INSERT INTO stockmaster (stockid,categoryid,description,longdescription,manufacturer, "
					+ "units,mbflag,actualcost,lastcost, materialcost,labourcost,overheadcost,lowestlevel,"
					+ "discontinued,controlled,eoq,volume,kgs,barcode,discountcategory,taxcatid,taxcatidret,"
					+ "serialised,appendfile,perishable,decimalplaces,netweight,idclassproduct,fecha_modificacion,"
					+ "height,width,large,factorsecondary,stockneodata, purchgroup,idjerarquia,"
					+ "addunits,secuunits,recipeunits,factorrecipe,addcategory,deliverydays,tolerancedays,estatusstock, "
					+ "eq_conversion_costo,unitstemporal,SAPActualiza, spes,stockautor,lastcurcostdate,nextserialno,"
					+ "pansize,shrinkfactor,stocksupplier,securitypoint,pkg_type,idetapaflujo,flagcommission,fijo,"
					+ "stockupdate,isbn,grade,subject,deductibleflag,u_typeoperation,typeoperationdiot,fichatecnica,"
					+ "percentfactorigi,OrigenCountry,OrigenDate,inpdfgroup,flagadvance,eq_stockid,unitequivalent,"
					+ "factorconversionpaq,factorconversionpz,unitssec,unitstthree,factorthree,factorprimary) "	+
					 " VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',"
					 + "'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',"
					 + "'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',0,'%s','%s','%s',"
					 + "'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',"
					 + "'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')";
			
			dbPreparedStatement = this.database.getPreparedStatement(sql);
			ps = dbPreparedStatement.getPreparedStatement();
			if (ps != null) {
				try {
					
					ps.setString(1, msg.getStockid());
					ps.setString(2, msg.getCategoryid());
					ps.setString(3, msg.getDescription());
					ps.setString(4, msg.getLongdescription());
					ps.setString(5, msg.getManufacturer()); 
					ps.setString(6, msg.getUnits());
					ps.setString(7, msg.getMbflag());
					ps.setString(8, msg.getActualcost());
					ps.setString(9, msg.getLastcost()); 
					ps.setString(10, msg.getMaterialcost());
					ps.setString(11, msg.getLabourcost());
					ps.setString(12, msg.getOverheadcost());
					ps.setString(13, msg.getLowestlevel());
					ps.setString(14, msg.getDiscontinued());
					ps.setString(15, msg.getControlled());
					ps.setString(16, msg.getEoq());
					ps.setString(17, msg.getVolume());
					ps.setString(18, msg.getKgs());
					ps.setString(19, msg.getBarcode());
					ps.setString(20, msg.getDiscountcategory());
					ps.setString(21, msg.getTaxcatid());
					ps.setString(22, msg.getTaxcatidret());
					ps.setString(23, msg.getSerialised());
					ps.setString(24, msg.getAppendfile());
					ps.setString(25, msg.getPerishable());
					ps.setString(26, msg.getDecimalplaces());
					ps.setString(27, msg.getNetweight());
					ps.setString(28, msg.getIdclassproduct());
					ps.setString(29, msg.getFecha_modificacion());
					ps.setString(30, msg.getHeight());
					ps.setString(31, msg.getWidth());
					ps.setString(32, msg.getLarge());
					ps.setString(33, msg.getFactorsecondary());
					ps.setString(34, msg.getStockneodata()); 
					ps.setString(35, msg.getPurchgroup());
					ps.setString(36, msg.getIdjerarquia());
					ps.setString(37, msg.getAddunits());
					ps.setString(38, msg.getSecuunits());
					ps.setString(39, msg.getRecipeunits());
					ps.setString(40, msg.getFactorrecipe());
					ps.setString(41, msg.getAddcategory());
					ps.setString(42, msg.getDeliverydays());
					ps.setString(43, msg.getTolerancedays());
					ps.setString(44, msg.getEstatusstock()); 
					ps.setString(45, msg.getEq_conversion_costo());
					ps.setString(46, msg.getUnitstemporal());
					ps.setString(47, msg.getSpes());
					ps.setString(48, msg.getStockautor());
					ps.setString(49, msg.getLastcurcostdate());
					ps.setString(50, msg.getNextserialno());
					ps.setString(51, msg.getPansize());
					ps.setString(52, msg.getShrinkfactor());
					ps.setString(53, msg.getStocksupplier());
					ps.setString(54, msg.getSecuritypoint());
					ps.setString(55, msg.getPkg_type());
					ps.setString(56, msg.getIdetapaflujo());
					ps.setString(57, msg.getFlagcommission());
					ps.setString(58, msg.getFijo());
					ps.setString(59, msg.getStockupdate());
					ps.setString(60, msg.getIsbn());
					ps.setString(61, msg.getGrade());
					ps.setString(62, msg.getSubject());
					ps.setString(63, msg.getDeductibleflag());
					ps.setString(64, msg.getU_typeoperation());
					ps.setString(65, msg.getTypeoperationdiot());
					ps.setString(66, msg.getFichatecnica());
					ps.setString(67, msg.getPercentfactorigi());
					ps.setString(68, msg.getOrigenCountry());
					ps.setString(69, msg.getOrigenDate());
					ps.setString(70, msg.getInpdfgroup());
					ps.setString(71, msg.getFlagadvance());
					ps.setString(72, msg.getEq_stockid());
					ps.setString(73, msg.getUnitequivalent());
					ps.setString(74, msg.getFactorconversionpaq());
					ps.setString(75, msg.getFactorconversionpz());
					ps.setString(76, msg.getUnitssec());
					ps.setString(77, msg.getUnitstthree());
					ps.setString(78, msg.getFactorthree());
					ps.setString(79, msg.getFactorprimary());

					// SQL Log
					finalLogSql = String.format(sqlLog,
							msg.getStockid(),
							msg.getCategoryid(),
							msg.getDescription(),
							msg.getLongdescription(),
							msg.getManufacturer(), 
							msg.getUnits(),
							msg.getMbflag(),
							msg.getActualcost(),
							msg.getLastcost(), 
							msg.getMaterialcost(),
							msg.getLabourcost(),
							msg.getOverheadcost(),
							msg.getLowestlevel(),
							msg.getDiscontinued(),
							msg.getControlled(),
							msg.getEoq(),
							msg.getVolume(),
							msg.getKgs(),
							msg.getBarcode(),
							msg.getDiscountcategory(),
							msg.getTaxcatid(),
							msg.getTaxcatidret(),
							msg.getSerialised(),
							msg.getAppendfile(),
							msg.getPerishable(),
							msg.getDecimalplaces(),
							msg.getNetweight(),
							msg.getIdclassproduct(),
							msg.getFecha_modificacion(),
							msg.getHeight(),
							msg.getWidth(),
							msg.getLarge(),
							msg.getFactorsecondary(),
							msg.getStockneodata(), 
							msg.getPurchgroup(),
							msg.getIdjerarquia(),
							msg.getAddunits(),
							msg.getSecuunits(),
							msg.getRecipeunits(),
							msg.getFactorrecipe(),
							msg.getAddcategory(),
							msg.getDeliverydays(),
							msg.getTolerancedays(),
							msg.getEstatusstock(), 
							msg.getEq_conversion_costo(),
							msg.getUnitstemporal(),
							msg.getSpes(),
							msg.getStockautor(),
							msg.getLastcurcostdate(),
							msg.getNextserialno(),
							msg.getPansize(),
							msg.getShrinkfactor(),
							msg.getStocksupplier(),
							msg.getSecuritypoint(),
							msg.getPkg_type(),
							msg.getIdetapaflujo(),
							msg.getFlagcommission(),
							msg.getFijo(),
							msg.getStockupdate(),
							msg.getIsbn(),
							msg.getGrade(),
							msg.getSubject(),
							msg.getDeductibleflag(),
							msg.getU_typeoperation(),
							msg.getTypeoperationdiot(),
							msg.getFichatecnica(),
							msg.getPercentfactorigi(),
							msg.getOrigenCountry(),
							msg.getOrigenDate(),
							msg.getInpdfgroup(),
							msg.getFlagadvance(),
							msg.getEq_stockid(),
							msg.getUnitequivalent(),
							msg.getFactorconversionpaq(),
							msg.getFactorconversionpz(),
							msg.getUnitssec(),
							msg.getUnitstthree(),
							msg.getFactorthree(),
							msg.getFactorprimary());
					
					stockid = msg.getStockid();
					
					logger.debug(finalLogSql);

					ps.executeUpdate();
					
					String locsql="INSERT locstock (loccode,stockid,quantity) "
							+ "SELECT locations.loccode,stockid,0 "
							+ "FROM locations CROSS JOIN stockmaster WHERE stockmaster.stockid='" + msg.getStockid() + "'";
					logger.debug("NUEVOPRODUCTO: " + locsql);
					this.database.executeUpdate(locsql);
					
					String costosql="INSERT stockcostsxlegal (legalid,stockid,lastcost,avgcost) "
							+ "SELECT legalbusinessunit.legalid,stockid,0,0 "
							+ "FROM stockmaster CROSS JOIN legalbusinessunit WHERE stockid='" + msg.getStockid() + "'";
					logger.debug("NUEVOPRODUCTO: " + costosql);
					this.database.executeUpdate(costosql);
					
					success = true;

				} catch (Exception e) {
					success = false;
					if (this.config.get("onErrorSaveFile").equals("1")) {
						success = Utils.saveFile(config, finalLogSql, "productos", stockid, line);
					}
					
					logger.error("Error desconocido", e);
				} finally {
					dbPreparedStatement.close();
				}
			}
		}
		
		return success;
	}

	public void disconnect() {
		if(this.database.isConnected()) {
			this.database.disconnect();
		}
	}
}
