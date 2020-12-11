package com.jahepi.activemq.database;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.dto.QueueMessageStocks;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ProducerStockDBHelper {
	
	final static Logger logger = Logger.getLogger(ProducerStockDBHelper.class);

	private Database database;
	public ProducerStockDBHelper(Database database, ConfigData config) {
		this.database = database;
	}

	public ArrayList<QueueMessageStocks> getMessages() {
		ArrayList<QueueMessageStocks> messages = new ArrayList<QueueMessageStocks>();
		String sql = "";
		DBResultSet result;
		ResultSet rs;
		
		sql = "SELECT stockid,categoryid,description,longdescription,manufacturer,units,mbflag,"
				+ "actualcost,lastcost,materialcost,labourcost,overheadcost,lowestlevel,discontinued,"
				+ "controlled,eoq,volume,kgs,barcode,discountcategory,taxcatid,taxcatidret,serialised,"
				+ "appendfile,perishable,decimalplaces,netweight,idclassproduct,fecha_modificacion,"
				+ "height,width,large,factorsecondary,stockneodata,purchgroup,idjerarquia,addunits,"
				+ "secuunits,recipeunits,factorrecipe,addcategory,deliverydays,tolerancedays,estatusstock,"
				+ "eq_conversion_costo,unitstemporal,SAPActualiza, spes, stockautor, lastcurcostdate, "
				+ "nextserialno, pansize, shrinkfactor,stocksupplier, securitypoint, pkg_type, "
				+ "idetapaflujo, flagcommission, fijo, stockupdate, isbn, grade, subject, "
				+ "deductibleflag, u_typeoperation, typeoperationdiot, fichatecnica, percentfactorigi, "
				+ "OrigenCountry, OrigenDate, inpdfgroup, flagadvance, eq_stockid, unitequivalent, "
				+ "factorconversionpaq, factorconversionpz, unitssec, unitstthree, factorthree, factorprimary "
				+ "FROM stockmaster WHERE SAPActualiza <> 0";
		
		
		logger.debug(sql);

		result = this.database.executeQuery(sql);
		rs = result.getResultSet();
		try {
			while (rs.next()) {
				
				QueueMessageStocks msg = new QueueMessageStocks();
				msg.setStockid(rs.getString("stockid"));
				msg.setCategoryid(rs.getString("categoryid"));
				msg.setDescription(rs.getString("description"));
				msg.setLongdescription(rs.getString("longdescription"));
				msg.setManufacturer(rs.getString("manufacturer"));
				msg.setUnits(rs.getString("units"));
				msg.setMbflag(rs.getString("mbflag"));
				msg.setActualcost(rs.getString("actualcost"));
				msg.setLastcost(rs.getString("lastcost"));
				msg.setMaterialcost(rs.getString("materialcost"));
				msg.setLabourcost(rs.getString("labourcost"));
				msg.setOverheadcost(rs.getString("overheadcost"));
				msg.setLowestlevel(rs.getString("lowestlevel"));
				msg.setDiscontinued(rs.getString("discontinued"));
				msg.setControlled(rs.getString("controlled"));
				msg.setEoq(rs.getString("eoq"));
				msg.setVolume(rs.getString("volume"));
				msg.setKgs(rs.getString("kgs"));
				msg.setBarcode(rs.getString("barcode"));
				msg.setDiscountcategory(rs.getString("discountcategory"));
				msg.setTaxcatid(rs.getString("taxcatid"));
				msg.setTaxcatidret(rs.getString("taxcatidret"));
				msg.setSerialised(rs.getString("serialised"));
				msg.setAppendfile(rs.getString("appendfile"));
				msg.setPerishable(rs.getString("perishable"));
				msg.setDecimalplaces(rs.getString("decimalplaces"));
				msg.setNetweight(rs.getString("netweight"));
				msg.setIdclassproduct(rs.getString("idclassproduct"));
				msg.setFecha_modificacion(rs.getString("fecha_modificacion"));
				msg.setHeight(rs.getString("height"));
				msg.setWidth(rs.getString("width"));
				msg.setLarge(rs.getString("large"));
				msg.setFactorsecondary(rs.getString("factorsecondary"));
				msg.setStockneodata(rs.getString("stockneodata"));
				msg.setPurchgroup(rs.getString("purchgroup"));
				msg.setIdjerarquia(rs.getString("idjerarquia"));
				msg.setAddunits(rs.getString("addunits"));
				msg.setSecuunits(rs.getString("secuunits"));
				msg.setRecipeunits(rs.getString("recipeunits"));
				msg.setFactorrecipe(rs.getString("factorrecipe"));
				msg.setAddcategory(rs.getString("addcategory"));
				msg.setDeliverydays(rs.getString("deliverydays"));
				msg.setTolerancedays(rs.getString("tolerancedays"));
				msg.setEstatusstock(rs.getString("estatusstock"));
				msg.setEq_conversion_costo(rs.getString("eq_conversion_costo"));
				msg.setUnitstemporal(rs.getString("unitstemporal"));
				msg.setSAPActualiza(rs.getInt("SAPActualiza"));
				msg.setTipo("stock");
				msg.setSpes(rs.getString("spes"));
				msg.setStockautor(rs.getString("stockautor"));
				msg.setLastcurcostdate(rs.getString("lastcurcostdate"));
				msg.setNextserialno(rs.getString("nextserialno"));
				msg.setPansize(rs.getString("pansize"));
				msg.setShrinkfactor(rs.getString("shrinkfactor"));
				msg.setStocksupplier(rs.getString("stocksupplier"));
				msg.setSecuritypoint(rs.getString("securitypoint"));
				msg.setPkg_type(rs.getString("pkg_type"));
				msg.setIdetapaflujo(rs.getString("idetapaflujo"));
				msg.setFlagcommission(rs.getString("flagcommission"));
				msg.setFijo(rs.getString("fijo"));
				msg.setStockupdate(rs.getString("stockupdate"));
				msg.setIsbn(rs.getString("isbn"));
				msg.setGrade(rs.getString("grade"));
				msg.setSubject(rs.getString("subject"));
				msg.setDeductibleflag(rs.getString("deductibleflag"));
				msg.setU_typeoperation(rs.getString("u_typeoperation"));
				msg.setTypeoperationdiot(rs.getString("typeoperationdiot"));
				msg.setFichatecnica(rs.getString("fichatecnica"));
				msg.setPercentfactorigi(rs.getString("percentfactorigi"));
				msg.setOrigenCountry(rs.getString("OrigenCountry"));
				msg.setOrigenDate(rs.getString("OrigenDate"));
				msg.setInpdfgroup(rs.getString("inpdfgroup"));
				msg.setFlagadvance(rs.getString("flagadvance"));
				msg.setEq_stockid(rs.getString("eq_stockid"));
				msg.setUnitequivalent(rs.getString("unitequivalent"));
				msg.setFactorconversionpaq(rs.getString("factorconversionpaq"));
				msg.setFactorconversionpz(rs.getString("factorconversionpz"));
				msg.setUnitssec(rs.getString("unitssec"));
				msg.setUnitstthree(rs.getString("unitstthree"));
				msg.setFactorthree(rs.getString("factorthree"));
				msg.setFactorprimary(rs.getString("factorprimary"));
				
				messages.add(msg);
			}
		} catch (SQLException e) {
			logger.error("Error de base de datos", e);
		}
		result.close();
		return messages;
	}

	public void updateMessagesAsSent(QueueMessageStocks msg, String str_stocks ) {
		String sql = "";
	
		String[] stocks = str_stocks.split(",");
		if(stocks.length > 0) {
			for(int i= 0; i < stocks.length; i++) {
				sql = "UPDATE stockmaster SET SAPActualiza = 0 WHERE stockid = '"
						+ stocks[i] + "'";
				this.database.executeUpdate(sql);
				logger.debug(sql);
			}
		}
		else {
			sql = "UPDATE stockmaster SET SAPActualiza = 0 WHERE stockid = '"
					+ msg.getStockid() + "'";
			this.database.executeUpdate(sql);
			
			logger.debug(sql);
		}
	}
	
	public void updateMessagesAsSent(QueueMessageStocks msg) {
		String sql = "UPDATE stockmaster SET SAPActualiza =0 WHERE stockid = '"
				+ msg.getStockid() + "'";
		this.database.executeUpdate(sql);
		
		logger.debug(sql);
	}

	public void disconnect() {
		this.database.disconnect();
	}
}
