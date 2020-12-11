package com.jahepi.activemq.database;

import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.Date;

import org.apache.log4j.Logger;

import com.jahepi.activemq.Utils;
import com.jahepi.activemq.database.Database.DBPreparedStatement;
import com.jahepi.activemq.dto.QueueMessageSalesDetails;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ConsumerSalesDetailsDBHelper {
	
	final static Logger logger = Logger.getLogger(ConsumerSalesDetailsDBHelper.class);
	
	private Database database;
	private ConfigData config;

	public ConsumerSalesDetailsDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public boolean saveMessage(QueueMessageSalesDetails msg) {
		String sql = "", sqlLog = "", orderno = "", orderlineno = "";
		String finalLogSql = "";
		DBPreparedStatement dbPreparedStatement;
		PreparedStatement ps;
		boolean success = true;
		
		switch(this.config.get("type")) {
			case "satelite":
				
				sql = "UPDATE `salesorderdetails` SET "
						+ "`stkcode` = ?, "
						+ "`fromstkloc` = ?, "
						+ "`qtyinvoiced` = ?, "
						+ "`unitprice` = ?, "
						+ "`quantity` = ?, "
						+ "`estimate` = ?, "
						+ "`discountpercent` = ?, "
						+ "`discountpercent1` = ?, "
						+ "`discountpercent2` = ?, "
						+ "`actualdispatchdate` = ?, "
						+ "`completed` = ?, "
						+ "`narrative` = ?, "
						+ "`itemdue` = ?, "
						+ "`poline` = ?, "
						+ "`warranty` = ?, "
						+ "`pocost` = ?, "
						+ "`idtarea` = ?, "
						+ "`servicestatus` = ?, "
						+ "`showdescrip` = ?, "
						+ "`salestype` = ?, "
						+ "`quantitydispatched` = ?, "
						+ "`refundpercent` = ?, "
						+ "`totalrefundpercent` = ?, "
						+ "`saletype` = ?, "
						+ "`cashdiscount` = ?, "
						+ "`readOnlyValues` = ?, "
						+ "`modifiedpriceanddiscount` = ?, "
						+ "`qtylost` = ?, "
						+ "`datelost` = ?, "
						+ "`woline` = ?, "
						+ "`stkmovid` = ?, "
						+ "`userlost` = ?, "
						+ "`ADevengar` = ?, "
						+ "`Facturado` = ?, "
						+ "`Devengado` = ?, "
						+ "`XFacturar` = ?, "
						+ "`AFacturar` = ?, "
						+ "`XDevengar` = ?, "
						+ "`nummes` = ?, "
						+ "`alto` = ?, "
						+ "`ancho` = ?, "
						+ "`largo` = ?, "
						+ "`calculatepricebysize` = ?, "
						+ "`localidad` = ?, "
						+ "`statusamq` = ? "
						+ "WHERE "
						+ "`orderlineno` = ? AND `orderno` = ?";
				
				sqlLog = "UPDATE `salesorderdetails` SET "
						+ "`stkcode` = '%s', "
						+ "`fromstkloc` = '%s', "
						+ "`qtyinvoiced` = '%s', "
						+ "`unitprice` = '%s', "
						+ "`quantity` = '%s', "
						+ "`estimate` = '%s', "
						+ "`discountpercent` = '%s', "
						+ "`discountpercent1` = '%s', "
						+ "`discountpercent2` = '%s', "
						+ "`actualdispatchdate` = '%s', "
						+ "`completed` = '%s', "
						+ "`narrative` = '%s', "
						+ "`itemdue` = '%s', "
						+ "`poline` = '%s', "
						+ "`warranty` = '%s', "
						+ "`pocost` = '%s', "
						+ "`idtarea` = '%s', "
						+ "`servicestatus` = '%s', "
						+ "`showdescrip` = '%s', "
						+ "`salestype` = '%s', "
						+ "`quantitydispatched` = '%s', "
						+ "`refundpercent` = '%s', "
						+ "`totalrefundpercent` = '%s', "
						+ "`saletype` = '%s', "
						+ "`cashdiscount` = '%s', "
						+ "`readOnlyValues` = '%s', "
						+ "`modifiedpriceanddiscount` = '%s', "
						+ "`qtylost` = '%s', "
						+ "`datelost` = '%s', "
						+ "`woline` = '%s', "
						+ "`stkmovid` = '%s', "
						+ "`userlost` = '%s', "
						+ "`ADevengar` = '%s', "
						+ "`Facturado` = '%s', "
						+ "`Devengado` = '%s', "
						+ "`XFacturar` = '%s', "
						+ "`AFacturar` = '%s', "
						+ "`XDevengar` = '%s', "
						+ "`nummes` = '%s', "
						+ "`alto` = '%s', "
						+ "`ancho` = '%s', "
						+ "`largo` = '%s', "
						+ "`calculatepricebysize` = '%s', "
						+ "`localidad` = '%s', "
						+ "`statusamq` = '%s' "
						+ "WHERE "
						+ "`orderlineno` = '%s' AND `orderno` = '%s'";
				
				break;
			case "central":
				
				sql = "INSERT INTO `salesorderdetails` "
						+ "(`orderlineno`, "
						+ "`orderno`, "
						+ "`stkcode`, "
						+ "`fromstkloc`, "
						+ "`qtyinvoiced`, "
						+ "`unitprice`, "
						+ "`quantity`, "
						+ "`estimate`, "
						+ "`discountpercent`, "
						+ "`discountpercent1`, "
						+ "`discountpercent2`, "
						+ "`actualdispatchdate`, "
						+ "`completed`, "
						+ "`narrative`, "
						+ "`itemdue`, "
						+ "`poline`, "
						+ "`warranty`, "
						+ "`pocost`, "
						+ "`idtarea`, "
						+ "`servicestatus`, "
						+ "`showdescrip`, "
						+ "`salestype`, "
						+ "`quantitydispatched`, "
						+ "`refundpercent`, "
						+ "`totalrefundpercent`, "
						+ "`saletype`, "
						+ "`cashdiscount`, "
						+ "`readOnlyValues`, "
						+ "`modifiedpriceanddiscount`, "
						+ "`qtylost`, "
						+ "`datelost`, "
						+ "`woline`, "
						+ "`stkmovid`, "
						+ "`userlost`, "
						+ "`ADevengar`, "
						+ "`Facturado`, "
						+ "`Devengado`, "
						+ "`XFacturar`, "
						+ "`AFacturar`, "
						+ "`XDevengar`, "
						+ "`nummes`, "
						+ "`alto`, "
						+ "`ancho`, "
						+ "`largo`, "
						+ "`calculatepricebysize`, "
						+ "`localidad`) "
				
						+ "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				
				sqlLog = "INSERT INTO `salesorderdetails` "
						+ "(`orderlineno`, "
						+ "`orderno`, "
						+ "`stkcode`, "
						+ "`fromstkloc`, "
						+ "`qtyinvoiced`, "
						+ "`unitprice`, "
						+ "`quantity`, "
						+ "`estimate`, "
						+ "`discountpercent`, "
						+ "`discountpercent1`, "
						+ "`discountpercent2`, "
						+ "`actualdispatchdate`, "
						+ "`completed`, "
						+ "`narrative`, "
						+ "`itemdue`, "
						+ "`poline`, "
						+ "`warranty`, "
						+ "`pocost`, "
						+ "`idtarea`, "
						+ "`servicestatus`, "
						+ "`showdescrip`, "
						+ "`salestype`, "
						+ "`quantitydispatched`, "
						+ "`refundpercent`, "
						+ "`totalrefundpercent`, "
						+ "`saletype`, "
						+ "`cashdiscount`, "
						+ "`readOnlyValues`, "
						+ "`modifiedpriceanddiscount`, "
						+ "`qtylost`, "
						+ "`datelost`, "
						+ "`woline`, "
						+ "`stkmovid`, "
						+ "`userlost`, "
						+ "`ADevengar`, "
						+ "`Facturado`, "
						+ "`Devengado`, "
						+ "`XFacturar`, "
						+ "`AFacturar`, "
						+ "`XDevengar`, "
						+ "`nummes`, "
						+ "`alto`, "
						+ "`ancho`, "
						+ "`largo`, "
						+ "`calculatepricebysize`, "
						+ "`localidad`)"
					
						+ "VALUES ("
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
						+ "'%s', "
				
						+ "'%s')";
				break;
		}
		
		
		dbPreparedStatement = this.database.getPreparedStatement(sql);
		ps = dbPreparedStatement.getPreparedStatement();
		if (ps != null) {
			try {
				
				switch(this.config.get("type")) {
					case "satelite":

						ps.setString(1, msg.getStkcode());	
						ps.setString(2, msg.getFromstkloc());	
						ps.setString(3, msg.getQtyinvoiced());	
						ps.setString(4, msg.getUnitprice());
						ps.setString(5, msg.getQuantity());	
						ps.setString(6, msg.getEstimate());
						ps.setString(7, msg.getDiscountpercent());
						ps.setString(8, msg.getDiscountpercent1());
						ps.setString(9, msg.getDiscountpercent2());
						ps.setString(10, msg.getActualdispatchdate());
						ps.setString(11, msg.getCompleted());
						ps.setString(12, msg.getNarrative());
						ps.setString(13, msg.getItemdue());
						ps.setString(14, msg.getPoline());	
						ps.setString(15, msg.getWarranty());	
						ps.setString(16, msg.getPocost());	
						ps.setString(17, msg.getIdtarea());
						ps.setString(18, msg.getServicestatus());
						ps.setString(19, msg.getShowdescrip());
						ps.setString(20, msg.getSalestype());
						ps.setString(21, msg.getQuantitydispatched());
						ps.setString(22, msg.getRefundpercent());
						ps.setString(23, msg.getTotalrefundpercent());
						ps.setString(24, msg.getSalestype());
						ps.setString(25, msg.getCashdiscount());
						ps.setString(26, msg.getReadOnlyValues());
						ps.setString(27, msg.getModifiedpriceanddiscount());
						ps.setString(28, msg.getQtylost());
						ps.setString(29, msg.getDatelost());
						ps.setString(30, msg.getWoline());
						ps.setString(31, msg.getStkmovid());
						ps.setString(32, msg.getUserlost());
						ps.setString(33, msg.getADevengar());
						ps.setString(34, msg.getFacturado());
						ps.setString(35, msg.getDevengado());
						ps.setString(36, msg.getXFacturar());
						ps.setString(37, msg.getAFacturar());
						ps.setString(38, msg.getXDevengar());
						ps.setString(39, msg.getNummes());
						ps.setString(40, msg.getAlto());
						ps.setString(41, msg.getAncho());
						ps.setString(42, msg.getLargo());
						ps.setString(43, msg.getCalculatepricebysize());
						ps.setString(44, msg.getLocalidad());
						ps.setString(45, msg.getOrderlineno());	
						ps.setString(46, msg.getOrderno());
								
								
						finalLogSql = String.format(
							sqlLog,	
							msg.getStkcode(),	
							msg.getFromstkloc(),	
							msg.getQtyinvoiced(),	
							msg.getUnitprice(),
							msg.getQuantity(),	
							msg.getEstimate(),
							msg.getDiscountpercent(),
							msg.getDiscountpercent1(),
							msg.getDiscountpercent2(),
							msg.getActualdispatchdate(),
							msg.getCompleted(),
							msg.getNarrative(),
							msg.getItemdue(),
							msg.getPoline(),	
							msg.getWarranty(),	
							msg.getPocost(),	
							msg.getIdtarea(),
							msg.getServicestatus(),
							msg.getShowdescrip(),
							msg.getSalestype(),
							msg.getQuantitydispatched(),
							msg.getRefundpercent(),
							msg.getTotalrefundpercent(),
							msg.getSalestype(),
							msg.getCashdiscount(),
							msg.getReadOnlyValues(),
							msg.getModifiedpriceanddiscount(),
							msg.getQtylost(),
							msg.getDatelost(),
							msg.getWoline(),
							msg.getStkmovid(),
							msg.getUserlost(),
							msg.getADevengar(),
							msg.getFacturado(),
							msg.getDevengado(),
							msg.getXFacturar(),
							msg.getAFacturar(),
							msg.getXDevengar(),
							msg.getNummes(),
							msg.getAlto(),
							msg.getAncho(),
							msg.getLargo(),
							msg.getCalculatepricebysize(),
							msg.getLocalidad(),
							msg.getOrderlineno(),	
							msg.getOrderno()
						);
						break;
					case "central":
						Date ahora = new Date();
				        SimpleDateFormat formateador = new SimpleDateFormat("yyyy-MM-dd");
						ps.setString(1, msg.getOrderlineno());	
						ps.setString(2, msg.getOrderno());	
						ps.setString(3, msg.getStkcode());	
						ps.setString(4, msg.getFromstkloc());	
						ps.setString(5, msg.getQtyinvoiced());	
						ps.setString(6, msg.getUnitprice());
						ps.setString(7, msg.getQuantity());	
						ps.setString(8, msg.getEstimate());
						ps.setString(9, msg.getDiscountpercent());
						ps.setString(10, msg.getDiscountpercent1());
						ps.setString(11, msg.getDiscountpercent2());
						ps.setString(12, formateador.format(ahora));
						ps.setString(13, msg.getCompleted());
						ps.setString(14, msg.getNarrative());
						ps.setString(15, msg.getItemdue());
						ps.setString(16, msg.getPoline());	
						ps.setString(17, msg.getWarranty());	
						ps.setString(18, msg.getPocost());	
						ps.setString(19, msg.getIdtarea());
						ps.setString(20, msg.getServicestatus());
						ps.setString(21, msg.getShowdescrip());
						ps.setString(22, msg.getSalestype());
						ps.setString(23, msg.getQuantitydispatched());
						ps.setString(24, msg.getRefundpercent());
						ps.setString(25, msg.getTotalrefundpercent());
						ps.setString(26, msg.getSalestype());
						ps.setString(27, msg.getCashdiscount());
						ps.setString(28, msg.getReadOnlyValues());
						ps.setString(29, msg.getModifiedpriceanddiscount());
						ps.setString(30, msg.getQtylost());
						ps.setString(31, msg.getDatelost());
						ps.setString(32, msg.getWoline());
						ps.setString(33, msg.getStkmovid());
						ps.setString(34, msg.getUserlost());
						ps.setString(35, msg.getADevengar());
						ps.setString(36, msg.getFacturado());
						ps.setString(37, msg.getDevengado());
						ps.setString(38, msg.getXFacturar());
						ps.setString(39, msg.getAFacturar());
						ps.setString(40, msg.getXDevengar());
						ps.setString(41, msg.getNummes());
						ps.setString(42, msg.getAlto());
						ps.setString(43, msg.getAncho());
						ps.setString(44, msg.getLargo());
						ps.setString(45, msg.getCalculatepricebysize());
						ps.setString(46, msg.getLocalidad());
						
						finalLogSql = String.format(
							sqlLog,
							msg.getOrderlineno(),	
							msg.getOrderno(),	
							msg.getStkcode(),	
							msg.getFromstkloc(),	
							msg.getQtyinvoiced(),	
							msg.getUnitprice(),
							msg.getQuantity(),	
							msg.getEstimate(),
							msg.getDiscountpercent(),
							msg.getDiscountpercent1(),
							msg.getDiscountpercent2(),
							msg.getActualdispatchdate(),
							msg.getCompleted(),
							msg.getNarrative(),
							msg.getItemdue(),
							msg.getPoline(),	
							msg.getWarranty(),	
							msg.getPocost(),	
							msg.getIdtarea(),
							msg.getServicestatus(),
							msg.getShowdescrip(),
							msg.getSalestype(),
							msg.getQuantitydispatched(),
							msg.getRefundpercent(),
							msg.getTotalrefundpercent(),
							msg.getSalestype(),
							msg.getCashdiscount(),
							msg.getReadOnlyValues(),
							msg.getModifiedpriceanddiscount(),
							msg.getQtylost(),
							msg.getDatelost(),
							msg.getWoline(),
							msg.getStkmovid(),
							msg.getUserlost(),
							msg.getADevengar(),
							msg.getFacturado(),
							msg.getDevengado(),
							msg.getXFacturar(),
							msg.getAFacturar(),
							msg.getXDevengar(),
							msg.getNummes(),
							msg.getAlto(),
							msg.getAncho(),
							msg.getLargo(),
							msg.getCalculatepricebysize(),
							msg.getLocalidad()
						);
						break;
				}				

				
				logger.debug(finalLogSql);

				ps.executeUpdate();
				
				success = true;
				
				orderno = msg.getOrderno();
				orderlineno = msg.getOrderlineno();

			} catch (SQLException e) {

				success = false;
				if (this.config.get("onErrorSaveFile").equals("1")) {
					success = Utils.saveFile(config, finalLogSql, "salesorderdetails", orderno, orderlineno);
				}
				
				logger.error("Error de base de datos", e);
			} finally {
				dbPreparedStatement.close();
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
