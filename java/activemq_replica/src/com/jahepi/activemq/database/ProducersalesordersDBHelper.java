package com.jahepi.activemq.database;

import java.sql.ResultSet;
import java.util.ArrayList;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database.DBResultSet;

import com.jahepi.activemq.dto.QueueMessagesalesorders;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ProducersalesordersDBHelper {
	
	final static Logger logger = Logger.getLogger(ProducersalesordersDBHelper.class);
	
	private Database database;
	private ConfigData config;

	public ProducersalesordersDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public ArrayList<QueueMessagesalesorders> getMessages() {
		ArrayList<QueueMessagesalesorders> messages = new ArrayList<QueueMessagesalesorders>();
		String sql = "";
		DBResultSet result;
		ResultSet rs;
		
		sql = "SELECT "
				+ "orderno, "
				+ "debtorno,"
				+ "branchcode, "
				+ "customerref, "
				+ "buyername, "
				+ "comments, "
				+ "orddate, "
				+ "ordertype, "
				+ "shipvia, "
				+ "deladd1, "
				+ "deladd2, "
				+ "deladd3, "
				+ "deladd4, "
				+ "deladd5, "
				+ "deladd6, "
				+ "contactphone, "
				+ "contactemail, "
				+ "deliverto, "
				+ "deliverblind, "
				+ "freightcost, "
				+ "fromstkloc, "
				+ "deliverydate, "
				+ "quotedate, "
				+ "confirmeddate, "
				+ "printedpackingslip, "
				+ "datepackingslipprinted, "
				+ "quotation, "
				+ "placa, "
				+ "serie, "
				+ "kilometraje, "
				+ "salesman, "
				+ "tagref, "
				+ "taxtotal, "
				+ "totaltaxret, "
				+ "currcode, "
				+ "paytermsindicator, "
				+ "advance, "
				+ "UserRegister, "
				+ "vehicleno, "
				+ "idtarea, "
				+ "codigobarras, "
				+ "contid, "
				+ "idprospect, "
				+ "nopedido, "
				+ "noentrada, "
				+ "extratext, "
				+ "noremision, "
				+ "contract_type, "
				+ "typeorder, "
				+ "refundpercentsale, "
				+ "puestaenmarcha, "
				+ "paymentname, "
				+ "nocuenta, "
				+ "deliverytext, "
				+ "totalrefundpercentsale, "
				+ "serviceorder, "
				+ "usetype, "
				+ "fromcr, "
				+ "estatusprocesing, "
				+ "statuscancel, "
				+ "identifieramq, "
				+ "statusamq "
				+ "FROM salesorders";
		
		
		switch(this.config.get("type")) {
			case "central":
				sql += " WHERE tagref = '"+ this.config.get("unidad") +"' AND statusamq IN (1,2)";
				break;
			case "satelite":
				sql += " WHERE statusamq IN (1,2)";
				break;
		}
		
		
		logger.debug(sql);
		
		result = this.database.executeQuery(sql);
		rs = result.getResultSet();
		try {
			while (rs.next()) {
				
				QueueMessagesalesorders msg = new QueueMessagesalesorders();
				
				msg.setOrderno(rs.getString("orderno"));
				msg.setDebtorno(rs.getString("debtorno"));
				msg.setBranchcode(rs.getString("branchcode"));
				msg.setCustomerref(rs.getString("customerref"));
				msg.setBuyername(rs.getString("buyername"));
				msg.setComments(rs.getString("comments"));
				msg.setOrddate(rs.getString("orddate"));
				msg.setOrdertype(rs.getString("ordertype"));
				msg.setShipvia(rs.getString("shipvia"));
				msg.setDeladd1(rs.getString("deladd1"));
				msg.setDeladd2(rs.getString("deladd2"));
				msg.setDeladd3(rs.getString("deladd3"));
				msg.setDeladd4(rs.getString("deladd4"));
				msg.setDeladd5(rs.getString("deladd5"));
				msg.setDeladd6(rs.getString("deladd6"));
				msg.setContactphone(rs.getString("contactphone"));
				msg.setContactemail(rs.getString("contactemail"));
				msg.setDeliverto(rs.getString("deliverto"));
				msg.setDeliverblind(rs.getString("deliverblind"));
				msg.setFreightcost(rs.getString("freightcost"));
				msg.setFromstkloc(rs.getString("fromstkloc"));
				msg.setDeliverydate(rs.getString("deliverydate"));
				msg.setQuotedate(rs.getString("quotedate"));
				msg.setConfirmeddate(rs.getString("confirmeddate"));
				msg.setPrintedpackingslip(rs.getString("printedpackingslip"));
				msg.setDatepackingslipprinted(rs.getString("datepackingslipprinted"));
				msg.setQuotation(rs.getString("quotation"));
				msg.setPlaca(rs.getString("placa"));
				msg.setSerie(rs.getString("serie"));
				msg.setKilometraje(rs.getString("kilometraje"));
				msg.setSalesman(rs.getString("salesman"));
				msg.setTagref(rs.getString("tagref"));
				msg.setTaxtotal(rs.getString("taxtotal"));
				msg.setTotaltaxret(rs.getString("totaltaxret"));
				msg.setCurrcode(rs.getString("currcode"));
				msg.setPaytermsindicator(rs.getString("paytermsindicator"));
				msg.setAdvance(rs.getString("advance"));
				msg.setUserRegister(rs.getString("UserRegister"));
				msg.setVehicleno(rs.getString("vehicleno"));
				msg.setIdtarea(rs.getString("idtarea"));
				msg.setCodigobarras(rs.getString("codigobarras"));
				msg.setContid(rs.getString("contid"));
				msg.setIdprospect(rs.getString("idprospect"));
				msg.setNopedido(rs.getString("nopedido"));
				msg.setNoentrada(rs.getString("noentrada"));
				msg.setExtratext(rs.getString("extratext"));
				msg.setNoremision(rs.getString("noremision"));
				msg.setContract_type(rs.getString("contract_type"));
				msg.setTypeorder(rs.getString("typeorder"));
				msg.setRefundpercentsale(rs.getString("refundpercentsale"));
				msg.setPuestaenmarcha(rs.getString("puestaenmarcha"));
				msg.setPaymentname(rs.getString("paymentname"));
				msg.setNocuenta(rs.getString("nocuenta"));
				msg.setDeliverytext(rs.getString("deliverytext"));
				msg.setTotalrefundpercentsale(rs.getString("totalrefundpercentsale"));
				msg.setServiceorder(rs.getString("serviceorder"));
				msg.setUsetype(rs.getString("usetype"));
				msg.setFromcr(rs.getString("fromcr"));
				msg.setEstatusprocesing(rs.getString("estatusprocesing"));
				msg.setStatuscancel(rs.getString("statuscancel"));
				msg.setIdentifieramq(rs.getString("identifieramq"));
				msg.setStatuscancel(rs.getString("statuscancel"));
				msg.setStatusamq(rs.getString("statusamq"));
				
				
				messages.add(msg);
			}
		} catch (Exception e) {
			logger.error("Error desconocido", e);
		} 
		result.close();
		return messages;
	}

	public void updateMessageAsSent(QueueMessagesalesorders msg) {
		String sql = "";
		sql = "UPDATE salesorders SET  statusamq = 0 "
				+ "WHERE tagref = '"+ this.config.get("unidad") +"' AND statusamq IN (1,2)";
		
		this.database.executeUpdate(sql);
		
		logger.debug(sql);
	}

	public void disconnect() {
		this.database.disconnect();
	}
}
