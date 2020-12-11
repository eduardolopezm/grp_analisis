package com.jahepi.activemq.database;

import java.sql.ResultSet;
import java.util.ArrayList;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.dto.QueueMessageSalesDetails;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ProducerSalesDetailsDBHelper {
	
	final static Logger logger = Logger.getLogger(ProducerSalesDetailsDBHelper.class);
	
	private Database database;
	private ConfigData config;

	public ProducerSalesDetailsDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public ArrayList<QueueMessageSalesDetails> getMessages() {
		ArrayList<QueueMessageSalesDetails> messages = new ArrayList<QueueMessageSalesDetails>();
		String sql = "";
		DBResultSet result;
		ResultSet rs;
		
		sql = "SELECT "
				+ "salesorders.tagref, "
				+ "salesorderdetails.orderlineno, "
				+ "salesorderdetails.orderno, "
				+ "salesorderdetails.stkcode, "
				+ "salesorderdetails.fromstkloc, "
				+ "salesorderdetails.qtyinvoiced, "
				+ "salesorderdetails.unitprice, "
				+ "salesorderdetails.quantity, "
				+ "salesorderdetails.estimate, "
				+ "salesorderdetails.discountpercent, "
				+ "salesorderdetails.discountpercent1, "
				+ "salesorderdetails.discountpercent2, "
				+ "salesorderdetails.actualdispatchdate, "
				+ "salesorderdetails.completed, "
				+ "salesorderdetails.narrative, "
				+ "salesorderdetails.itemdue, "
				+ "salesorderdetails.poline, "
				+ "salesorderdetails.warranty, "
				+ "salesorderdetails.pocost, "
				+ "salesorderdetails.idtarea, "
				+ "salesorderdetails.servicestatus, "
				+ "salesorderdetails.showdescrip, "
				+ "salesorderdetails.salestype, "
				+ "salesorderdetails.quantitydispatched, "
				+ "salesorderdetails.refundpercent, "
				+ "salesorderdetails.totalrefundpercent, "
				+ "salesorderdetails.saletype, "
				+ "salesorderdetails.cashdiscount, "
				+ "salesorderdetails.readOnlyValues, "
				+ "salesorderdetails.modifiedpriceanddiscount, "
				+ "salesorderdetails.qtylost, "
				+ "salesorderdetails.datelost, "
				+ "salesorderdetails.woline, "
				+ "salesorderdetails.stkmovid, "
				+ "salesorderdetails.userlost, "
				+ "salesorderdetails.ADevengar, "
				+ "salesorderdetails.Facturado, "
				+ "salesorderdetails.Devengado, "
				+ "salesorderdetails.XFacturar, "
				+ "salesorderdetails.AFacturar, "
				+ "salesorderdetails.XDevengar, "
				+ "salesorderdetails.nummes, "
				+ "salesorderdetails.alto, "
				+ "salesorderdetails.ancho, "
				+ "salesorderdetails.largo, "
				+ "salesorderdetails.calculatepricebysize, "
				+ "salesorderdetails.localidad "
				+ "FROM salesorderdetails "
				+ "INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno "
				+ "WHERE salesorderdetails.statusamq IN (1,2) AND salesorders.tagref='" + this.config.get("unidad") + "'";

		
		logger.debug(sql);
		
		result = this.database.executeQuery(sql);
		rs = result.getResultSet();
		try {
			while (rs.next()) {		
				QueueMessageSalesDetails msg = new QueueMessageSalesDetails();
				msg.setOrderlineno(rs.getString("orderlineno"));	
				msg.setOrderno(rs.getString("orderno"));	
				msg.setStkcode(rs.getString("stkcode"));	
				msg.setFromstkloc(rs.getString("fromstkloc"));	
				msg.setQtyinvoiced(rs.getString("qtyinvoiced"));	
				msg.setUnitprice(rs.getString("unitprice"));
				msg.setQuantity(rs.getString("unitprice"));	
				msg.setEstimate(rs.getString("estimate"));
				msg.setDiscountpercent(rs.getString("discountpercent"));
				msg.setDiscountpercent1(rs.getString("discountpercent1"));
				msg.setDiscountpercent2(rs.getString("discountpercent2"));
				msg.setActualdispatchdate(rs.getString("actualdispatchdate"));
				msg.setCompleted(rs.getString("completed"));
				msg.setNarrative(rs.getString("narrative"));
				msg.setItemdue(rs.getString("itemdue"));
				msg.setPoline(rs.getString("poline"));	
				msg.setWarranty(rs.getString("warranty"));	
				msg.setPocost(rs.getString("pocost"));	
				msg.setIdtarea(rs.getString("idtarea"));
				msg.setServicestatus(rs.getString("servicestatus"));
				msg.setShowdescrip(rs.getString("showdescrip"));
				msg.setSalestype(rs.getString("salestype"));
				msg.setQuantitydispatched(rs.getString("quantitydispatched"));
				msg.setRefundpercent(rs.getString("refundpercent"));
				msg.setTotalrefundpercent(rs.getString("totalrefundpercent"));
				msg.setSalestype(rs.getString("saletype"));
				msg.setCashdiscount(rs.getString("cashdiscount"));
				msg.setReadOnlyValues(rs.getString("readOnlyValues"));
				msg.setModifiedpriceanddiscount(rs.getString("modifiedpriceanddiscount"));
				msg.setQtylost(rs.getString("qtylost"));
				msg.setDatelost(rs.getString("datelost"));
				msg.setWoline(rs.getString("woline"));
				msg.setStkmovid(rs.getString("stkmovid"));
				msg.setUserlost(rs.getString("userlost"));
				msg.setADevengar(rs.getString("ADevengar"));
				msg.setFacturado(rs.getString("Facturado"));
				msg.setDevengado(rs.getString("Devengado"));
				msg.setXFacturar(rs.getString("XFacturar"));
				msg.setAFacturar(rs.getString("AFacturar"));
				msg.setXDevengar(rs.getString("XDevengar"));
				msg.setNummes(rs.getString("nummes"));
				msg.setAlto(rs.getString("alto"));
				msg.setAncho(rs.getString("ancho"));
				msg.setLargo(rs.getString("ancho"));
				msg.setCalculatepricebysize(rs.getString("calculatepricebysize"));
				msg.setLocalidad(rs.getString("localidad"));
				
				messages.add(msg);
			}
		} catch (Exception e) {
			logger.error("Error desconocido", e);
		} 
		result.close();
		return messages;
	}

	public void updateMessageAsSent(QueueMessageSalesDetails msg) {
		String sql = "";
		sql = "UPDATE salesorderdetails SET statusamq = 0 WHERE orderlineno = '"
			+ msg.getOrderlineno() + "' AND orderno = '" + msg.getOrderno() + "'";
		
		this.database.executeUpdate(sql);
		
		logger.debug(sql);
	}

	public void disconnect() {
		this.database.disconnect();
	}
}
