package com.jahepi.activemq.database;

import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.Date;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database.DBPreparedStatement;
import com.jahepi.activemq.dto.QueueMessagesalesorders;

import com.jahepi.activemq.loader.Config.ConfigData;

public class ConsumersalesordersDBHelper {
	
	final static Logger logger = Logger.getLogger(ConsumerSalesDetailsDBHelper.class);
	
	private Database database;
	private ConfigData config;

	public ConsumersalesordersDBHelper(Database database, ConfigData config) {
		this.database = database;
		this.config = config;
	}

	public boolean saveMessage(QueueMessagesalesorders msg) {
		String sql = "", sqlLog = "";
		String finalLogSql = "";
		DBPreparedStatement dbPreparedStatement;
		PreparedStatement ps;
		boolean success = true;
		
		switch(msg.getStatusamq()) {
			case "1":
				sql = "UPDATE salesorders SET "
						+ "debtorno = ?,"
						+ "branchcode = ?, "
						+ "customerref = ?, "
						+ "buyername = ?, "
						+ "comments = ?, "
						+ "orddate = ?, "
						+ "ordertype = ?, "
						+ "shipvia = ?, "
						+ "deladd1 = ?, "
						+ "deladd2 = ?, "
						+ "deladd3 = ?, "
						+ "deladd4 = ?, "
						+ "deladd5 = ?, "
						+ "deladd6 = ?, "
						+ "contactphone = ?, "
						+ "contactemail = ?, "
						+ "deliverto = ?, "
						+ "deliverblind = ?, "
						+ "freightcost = ?, "
						+ "fromstkloc = ?, "
						+ "deliverydate = ?, "
						+ "quotedate = ?, "
						+ "confirmeddate = ?, "
						+ "printedpackingslip = ?, "
						+ "datepackingslipprinted = ?, "
						+ "quotation = ?, "
						+ "placa = ?, "
						+ "serie = ?, "
						+ "kilometraje = ?, "
						+ "salesman = ?, "
						+ "tagref = ?, "
						+ "taxtotal = ?, "
						+ "totaltaxret = ?, "
						+ "currcode = ?, "
						+ "paytermsindicator = ?, "
						+ "advance = ?, "
						+ "UserRegister = ?, "
						+ "vehicleno = ?, "
						+ "idtarea = ?, "
						+ "codigobarras = ?, "
						+ "contid = ?, "
						+ "idprospect = ?, "
						+ "nopedido = ?, "
						+ "noentrada = ?, "
						+ "extratext = ?, "
						+ "noremision = ?, "
						+ "contract_type = ?, "
						+ "typeorder = ?, "
						+ "refundpercentsale = ?, "
						+ "puestaenmarcha = ?, "
						+ "paymentname = ?, "
						+ "nocuenta = ?, "
						+ "deliverytext = ?, "
						+ "totalrefundpercentsale = ?, "
						+ "serviceorder = ?, "
						+ "usetype = ?, "
						+ "fromcr = ?, "
						+ "estatusprocesing = ?, "
						+ "statuscancel = ?, "
						+ "identifieramq = ?, "
						+ "statusamq = ? "
						+ "WHERE orderno = ? AND tagref = ?";
				sqlLog = "UPDATE salesorders SET "
						+ "debtorno = '%s',"
						+ "branchcode = '%s', "
						+ "customerref = '%s', "
						+ "buyername = '%s', "
						+ "comments = '%s', "
						+ "orddate = '%s', "
						+ "ordertype = '%s', "
						+ "shipvia = '%s', "
						+ "deladd1 = '%s', "
						+ "deladd2 = '%s', "
						+ "deladd3 = '%s', "
						+ "deladd4 = '%s', "
						+ "deladd5 = '%s', "
						+ "deladd6 = '%s', "
						+ "contactphone = '%s', "
						+ "contactemail = '%s', "
						+ "deliverto = '%s', "
						+ "deliverblind = '%s', "
						+ "freightcost = '%s', "
						+ "fromstkloc = '%s', "
						+ "deliverydate = '%s', "
						+ "quotedate = '%s', "
						+ "confirmeddate = '%s', "
						+ "printedpackingslip = '%s', "
						+ "datepackingslipprinted = '%s', "
						+ "quotation = '%s', "
						+ "placa = '%s', "
						+ "serie = '%s', "
						+ "kilometraje = '%s', "
						+ "salesman = '%s', "
						+ "tagref = '%s', "
						+ "taxtotal = '%s', "
						+ "totaltaxret = '%s', "
						+ "currcode = '%s', "
						+ "paytermsindicator = '%s', "
						+ "advance = '%s', "
						+ "UserRegister = '%s', "
						+ "vehicleno = '%s', "
						+ "idtarea = '%s', "
						+ "codigobarras = '%s', "
						+ "contid = '%s', "
						+ "idprospect = '%s', "
						+ "nopedido = '%s', "
						+ "noentrada = '%s', "
						+ "extratext = '%s', "
						+ "noremision = '%s', "
						+ "contract_type = '%s', "
						+ "typeorder = '%s', "
						+ "refundpercentsale = '%s', "
						+ "puestaenmarcha = '%s', "
						+ "paymentname = '%s', "
						+ "nocuenta = '%s', "
						+ "deliverytext = '%s', "
						+ "totalrefundpercentsale = '%s', "
						+ "serviceorder = '%s', "
						+ "usetype = '%s', "
						+ "fromcr = '%s', "
						+ "estatusprocesing = '%s', "
						+ "statuscancel = '%s', "
						+ "identifieramq = '%s', "
						+ "statusamq = '%s' "
						+ "WHERE orderno = '%s' AND tagref = '%s'";
				break;
			case "2":
				sql = "INSERT INTO `salesorders` ("
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
						+ "statusamq, "
						+ "orderno "						
						+ ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				sqlLog = "INSERT INTO `salesorders` ("
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
						
						+ "statusamq, "	
						+ "orderno "	
						+ ") VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";
				break;
		}
		
		
		dbPreparedStatement = this.database.getPreparedStatement(sql);
		ps = dbPreparedStatement.getPreparedStatement();
		if (ps != null) {
			try {
				
				switch(msg.getStatusamq()) {
					case "1":
				    	ps.setString(1, msg.getDebtorno());
				    	ps.setString(2, msg.getBranchcode());
				    	ps.setString(3, msg.getCustomerref());
				    	ps.setString(4, msg.getBuyername());
				    	ps.setString(5, msg.getComments());
				    	ps.setString(6, msg.getOrddate());
				    	ps.setString(7, msg.getOrdertype());
				    	ps.setString(8, msg.getShipvia());
				    	ps.setString(9, msg.getDeladd1());
				    	ps.setString(10, msg.getDeladd2());
				    	ps.setString(11, msg.getDeladd3());
				    	ps.setString(12, msg.getDeladd4());
				    	ps.setString(13, msg.getDeladd5());
				    	ps.setString(14, msg.getDeladd6());
				    	ps.setString(15, msg.getContactphone());
				    	ps.setString(16, msg.getContactemail());
				    	ps.setString(17, msg.getDeliverto());
				    	ps.setString(18, msg.getDeliverblind());
				    	ps.setString(19, msg.getFreightcost());
				    	ps.setString(20, msg.getFromstkloc());
				    	ps.setString(21, msg.getDeliverydate());
				    	ps.setString(22, msg.getQuotedate());
				    	ps.setString(23, msg.getConfirmeddate());
				    	ps.setString(24, msg.getPrintedpackingslip());
				    	ps.setString(25, msg.getDatepackingslipprinted());
				    	ps.setString(26, msg.getQuotation());
				    	ps.setString(27, msg.getPlaca());
				    	ps.setString(28, msg.getSerie());
				    	ps.setString(29, msg.getKilometraje());
				    	ps.setString(30, msg.getSalesman());
				    	ps.setString(31, msg.getTagref());
				    	ps.setString(32, msg.getTaxtotal());
				    	ps.setString(33, msg.getTotaltaxret());
				    	ps.setString(34, msg.getCurrcode());
				    	ps.setString(35, msg.getPaytermsindicator());
				    	ps.setString(36, msg.getAdvance());
				    	ps.setString(37, msg.getUserRegister());
				    	ps.setString(38, msg.getVehicleno());
				    	ps.setString(39, msg.getIdtarea());
				    	ps.setString(40, msg.getCodigobarras());
				    	ps.setString(41, msg.getContid());
				    	ps.setString(42, msg.getIdprospect());
				    	ps.setString(43, msg.getNopedido());
				    	ps.setString(44, msg.getNoentrada());
				    	ps.setString(45, msg.getExtratext());
				    	ps.setString(46, msg.getNoremision());
				    	ps.setString(47, msg.getContract_type());
				    	ps.setString(48, msg.getTypeorder());
				    	ps.setString(49, msg.getRefundpercentsale());
				    	ps.setString(50, msg.getPuestaenmarcha());
				    	ps.setString(51, msg.getPaymentname());
				    	ps.setString(52, msg.getNocuenta());
				    	ps.setString(53, msg.getDeliverytext());
				    	ps.setString(54, msg.getTotalrefundpercentsale());
				    	ps.setString(55, msg.getServiceorder());
				    	ps.setString(56, msg.getUsetype());
				    	ps.setString(57, msg.getFromcr());
				    	ps.setString(58, msg.getEstatusprocesing());
				    	ps.setString(59, msg.getStatuscancel());
				    	ps.setString(60, msg.getIdentifieramq());
				    	ps.setString(61, "0");
				    	ps.setString(62, msg.getOrderno());
						
						finalLogSql = String.format(
							sqlLog,
							msg.getDebtorno(),
					    	msg.getBranchcode(),
					    	msg.getCustomerref(),
					    	msg.getBuyername(),
					    	msg.getComments(),
					    	msg.getOrddate(),
					    	msg.getOrdertype(),
					    	msg.getShipvia(),
					    	msg.getDeladd1(),
					    	msg.getDeladd2(),
					    	msg.getDeladd3(),
					    	msg.getDeladd4(),
					    	msg.getDeladd5(),
					    	msg.getDeladd6(),
					    	msg.getContactphone(),
					    	msg.getContactemail(),
					    	msg.getDeliverto(),
					    	msg.getDeliverblind(),
					    	msg.getFreightcost(),
					    	msg.getFromstkloc(),
					    	msg.getDeliverydate(),
					    	msg.getQuotedate(),
					    	msg.getConfirmeddate(),
					    	msg.getPrintedpackingslip(),
					    	msg.getDatepackingslipprinted(),
					    	msg.getQuotation(),
					    	msg.getPlaca(),
					    	msg.getSerie(),
					    	msg.getKilometraje(),
					    	msg.getSalesman(),
					    	msg.getTagref(),
					    	msg.getTaxtotal(),
					    	msg.getTotaltaxret(),
					    	msg.getCurrcode(),
					    	msg.getPaytermsindicator(),
					    	msg.getAdvance(),
					    	msg.getUserRegister(),
					    	msg.getVehicleno(),
					    	msg.getIdtarea(),
					    	msg.getCodigobarras(),
					    	msg.getContid(),
					    	msg.getIdprospect(),
					    	msg.getNopedido(),
					    	msg.getNoentrada(),
					    	msg.getExtratext(),
					    	msg.getNoremision(),
					    	msg.getContract_type(),
					    	msg.getTypeorder(),
					    	msg.getRefundpercentsale(),
					    	msg.getPuestaenmarcha(),
					    	msg.getPaymentname(),
					    	msg.getNocuenta(),
					    	msg.getDeliverytext(),
					    	msg.getTotalrefundpercentsale(),
					    	msg.getServiceorder(),
					    	msg.getUsetype(),
					    	msg.getFromcr(),
					    	msg.getEstatusprocesing(),
					    	msg.getStatuscancel(),
					    	msg.getIdentifieramq(),
					    	"0",
					    	msg.getOrderno()
						);
						break;
					case "2":
						Date ahora = new Date();
				        SimpleDateFormat formateador = new SimpleDateFormat("yyyy-MM-dd");
				         
				        
				        
						ps.setString(1, msg.getDebtorno());
				    	ps.setString(2, msg.getBranchcode());
				    	ps.setString(3, msg.getCustomerref());
				    	ps.setString(4, msg.getBuyername());
				    	ps.setString(5, msg.getComments());
				    	ps.setString(6, msg.getOrddate());
				    	ps.setString(7, msg.getOrdertype());
				    	ps.setString(8, msg.getShipvia());
				    	ps.setString(9, msg.getDeladd1());
				    	ps.setString(10, msg.getDeladd2());
				    	ps.setString(11, msg.getDeladd3());
				    	ps.setString(12, msg.getDeladd4());
				    	ps.setString(13, msg.getDeladd5());
				    	ps.setString(14, msg.getDeladd6());
				    	ps.setString(15, msg.getContactphone());
				    	ps.setString(16, msg.getContactemail());
				    	ps.setString(17, msg.getDeliverto());
				    	ps.setString(18, msg.getDeliverblind());
				    	ps.setString(19, msg.getFreightcost());
				    	ps.setString(20, msg.getFromstkloc());
				    	ps.setString(21, formateador.format(ahora));
				    	ps.setString(22, formateador.format(ahora));
				    	ps.setString(23, formateador.format(ahora));
				    	ps.setString(24, msg.getPrintedpackingslip());
				    	ps.setString(25, formateador.format(ahora));
				    	ps.setString(26, msg.getQuotation());
				    	ps.setString(27, msg.getPlaca());
				    	ps.setString(28, msg.getSerie());
				    	ps.setString(29, msg.getKilometraje());
				    	ps.setString(30, msg.getSalesman());
				    	ps.setString(31, msg.getTagref());
				    	ps.setString(32, msg.getTaxtotal());
				    	ps.setString(33, msg.getTotaltaxret());
				    	ps.setString(34, msg.getCurrcode());
				    	ps.setString(35, msg.getPaytermsindicator());
				    	ps.setString(36, msg.getAdvance());
				    	ps.setString(37, msg.getUserRegister());
				    	ps.setString(38, msg.getVehicleno());
				    	ps.setString(39, msg.getIdtarea());
				    	ps.setString(40, msg.getCodigobarras());
				    	ps.setString(41, msg.getContid());
				    	ps.setString(42, msg.getIdprospect());
				    	ps.setString(43, msg.getNopedido());
				    	ps.setString(44, msg.getNoentrada());
				    	ps.setString(45, msg.getExtratext());
				    	ps.setString(46, msg.getNoremision());
				    	ps.setString(47, msg.getContract_type());
				    	ps.setString(48, msg.getTypeorder());
				    	ps.setString(49, msg.getRefundpercentsale());
				    	ps.setString(50, msg.getPuestaenmarcha());
				    	ps.setString(51, msg.getPaymentname());
				    	ps.setString(52, msg.getNocuenta());
				    	ps.setString(53, msg.getDeliverytext());
				    	ps.setString(54, msg.getTotalrefundpercentsale());
				    	ps.setString(55, msg.getServiceorder());
				    	ps.setString(56, msg.getUsetype());
				    	ps.setString(57, msg.getFromcr());
				    	ps.setString(58, msg.getEstatusprocesing());
				    	ps.setString(59, msg.getStatuscancel());
				    	ps.setString(60, msg.getIdentifieramq());
				    	ps.setString(61, msg.getStatusamq());
				    	ps.setString(62, msg.getOrderno());
						
						finalLogSql = String.format(
							sqlLog,
							msg.getDebtorno(),
					    	msg.getBranchcode(),
					    	msg.getCustomerref(),
					    	msg.getBuyername(),
					    	msg.getComments(),
					    	msg.getOrddate(),
					    	msg.getOrdertype(),
					    	msg.getShipvia(),
					    	msg.getDeladd1(),
					    	msg.getDeladd2(),
					    	msg.getDeladd3(),
					    	msg.getDeladd4(),
					    	msg.getDeladd5(),
					    	msg.getDeladd6(),
					    	msg.getContactphone(),
					    	msg.getContactemail(),
					    	msg.getDeliverto(),
					    	msg.getDeliverblind(),
					    	msg.getFreightcost(),
					    	msg.getFromstkloc(),
					    	msg.getDeliverydate(),
					    	msg.getQuotedate(),
					    	msg.getConfirmeddate(),
					    	msg.getPrintedpackingslip(),
					    	msg.getDatepackingslipprinted(),
					    	msg.getQuotation(),
					    	msg.getPlaca(),
					    	msg.getSerie(),
					    	msg.getKilometraje(),
					    	msg.getSalesman(),
					    	msg.getTagref(),
					    	msg.getTaxtotal(),
					    	msg.getTotaltaxret(),
					    	msg.getCurrcode(),
					    	msg.getPaytermsindicator(),
					    	msg.getAdvance(),
					    	msg.getUserRegister(),
					    	msg.getVehicleno(),
					    	msg.getIdtarea(),
					    	msg.getCodigobarras(),
					    	msg.getContid(),
					    	msg.getIdprospect(),
					    	msg.getNopedido(),
					    	msg.getNoentrada(),
					    	msg.getExtratext(),
					    	msg.getNoremision(),
					    	msg.getContract_type(),
					    	msg.getTypeorder(),
					    	msg.getRefundpercentsale(),
					    	msg.getPuestaenmarcha(),
					    	msg.getPaymentname(),
					    	msg.getNocuenta(),
					    	msg.getDeliverytext(),
					    	msg.getTotalrefundpercentsale(),
					    	msg.getServiceorder(),
					    	msg.getUsetype(),
					    	msg.getFromcr(),
					    	msg.getEstatusprocesing(),
					    	msg.getStatuscancel(),
					    	msg.getIdentifieramq(),
					    	msg.getStatusamq(),
					    	msg.getOrderno()
						);
						break;
				}				

				
				logger.debug(finalLogSql);

				ps.executeUpdate();
				
				//loccode = msg.getLoccode();
				//stockid = msg.getStockid();

				// success = this.database.executeUpdate(msg.getSql());
				success = true;

			} catch (SQLException e) {

				success = false;
				if (this.config.get("onErrorSaveFile").equals("1")) {
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
