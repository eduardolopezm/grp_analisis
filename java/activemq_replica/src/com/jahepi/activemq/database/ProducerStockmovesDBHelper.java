package com.jahepi.activemq.database;

import java.sql.ResultSet;
import java.util.ArrayList;

import org.apache.log4j.Logger;

import com.jahepi.activemq.database.Database.DBResultSet;
import com.jahepi.activemq.dto.QueueMessageStockMoves;
import com.jahepi.activemq.loader.Config.ConfigData;

public class ProducerStockmovesDBHelper {
	
	final static Logger logger = Logger.getLogger(ProducerStockmovesDBHelper.class);
	
	private Database database;
	public ProducerStockmovesDBHelper(Database database, ConfigData config) {
		this.database = database;
	}

	public ArrayList<QueueMessageStockMoves> getMessages() {
		ArrayList<QueueMessageStockMoves> messages = new ArrayList<QueueMessageStockMoves>();
		String sql = "";
		DBResultSet result;
		ResultSet rs;
		
		sql = "SELECT stockmoves.stkmoveno, "
				+ "stockmoves.stockid, "
				+ "stockmoves.type, "
				+ "stockmoves.transno, "
				+ "stockmoves.loccode, "
				+ "stockmoves.trandate, "
				+ "stockmoves.debtorno, "
				+ "stockmoves.branchcode, "
				+ "stockmoves.price, "
				+ "stockmoves.prd, "
				+ "stockmoves.reference, "
				+ "stockmoves.qty, "
				+ "stockmoves.discountpercent, "
				+ "stockmoves.standardcost, "
				+ "stockmoves.show_on_inv_crds, "
				+ "stockmoves.newqoh, "
				+ "stockmoves.hidemovt, "
				+ "stockmoves.narrative, "
				+ "stockmoves.warranty, "
				+ "stockmoves.tagref, "
				+ "stockmoves.discountpercent1, "
				+ "stockmoves.discountpercent2, "
				+ "stockmoves.totaldescuento, "
				+ "stockmoves.avgcost, "
				+ "stockmoves.standardcostv2, "
				+ "stockmoves.showdescription, "
				+ "stockmoves.refundpercentmv, "
				+ "stockmoves.nuevocosto, "
				+ "stockmoves.ref1, "
				+ "stockmoves.ref2, "
				+ "stockmoves.ref3, "
				+ "stockmoves.ref4, "
				+ "stockmoves.qty2, "
				+ "stockmoves.qtyinvoiced, "
				+ "stockmoves.qty_sent, "
				+ "stockmoves.ratemov, "
				+ "stockmoves.useridmov, "
				+ "stockmoves.FlagValExistencias, "
				+ "stockmoves.stkmovid, "
				+ "stockmoves.nomes, "
				+ "stockmoves.stockclie, "
				+ "stockmoves.localidad, "
				+ "stockmoves.qty_excess, "
				+ "stockmoves.secondfactorconversion, "
				+ "stockmoves.register, "
				+ "stockmoves.reasonid, "
				+ "stockmoves.serviceid, "
				+ "stockmoves.factorprimary, "
				+ "stockmoves.factorsecondary, "
				+ "stockmoves.factorthree, "
				+ "stockmoves.equivalentqty, "
				+ "stockmoves.pietablon, "
				+ "stockmoves.activemq "
				+ "FROM stockmoves WHERE TYPE IN (26, 28) AND activemq = 1";
		
		logger.debug(sql);
		
		result = this.database.executeQuery(sql);
		rs = result.getResultSet();
		try {
			while (rs.next()) {		
				QueueMessageStockMoves msg = new QueueMessageStockMoves();
				
				msg.setStkmoveno(rs.getString("stkmoveno"));
				msg.setStockid(rs.getString("stockid"));
				msg.setType(rs.getString("type"));
				msg.setTransno(rs.getString("transno"));
				msg.setLoccode(rs.getString("loccode"));
				msg.setTrandate(rs.getString("trandate"));
				msg.setDebtorno(rs.getString("debtorno"));
				msg.setBranchcode(rs.getString("branchcode"));
				msg.setPrice(rs.getString("price"));
				msg.setPrd(rs.getString("prd"));
				msg.setReference(rs.getString("reference"));
				msg.setQty(rs.getString("qty"));
				msg.setDiscountpercent(rs.getString("discountpercent"));
				msg.setStandardcost(rs.getString("standardcost"));
				msg.setShow_on_inv_crds(rs.getString("show_on_inv_crds"));
				msg.setNewqoh(rs.getString("newqoh"));
				msg.setHidemovt(rs.getString("hidemovt"));
				msg.setNarrative(rs.getString("narrative"));
				msg.setWarranty(rs.getString("warranty"));
				msg.setTagref(rs.getString("tagref"));
				msg.setDiscountpercent1(rs.getString("discountpercent1"));
				msg.setDiscountpercent2(rs.getString("discountpercent2"));
				msg.setTotaldescuento(rs.getString("totaldescuento"));
				msg.setAvgcost(rs.getString("avgcost"));
				msg.setStandardcostv2(rs.getString("standardcostv2"));
				msg.setShowdescription(rs.getString("showdescription"));
				msg.setRefundpercentmv(rs.getString("refundpercentmv"));
				msg.setNuevocosto(rs.getString("nuevocosto"));
				msg.setRef1(rs.getString("ref1"));
				msg.setRef2(rs.getString("ref2"));
				msg.setRef3(rs.getString("ref3"));
				msg.setRef4(rs.getString("ref4"));
				msg.setQty2(rs.getString("qty2"));
				msg.setQtyinvoiced(rs.getString("qtyinvoiced"));
				msg.setQty_sent(rs.getString("qty_sent"));
				msg.setRatemov(rs.getString("ratemov"));
				msg.setUseridmov(rs.getString("useridmov"));
				msg.setFlagValExistencias(rs.getString("FlagValExistencias"));
				msg.setStkmovid(rs.getString("stkmovid"));
				msg.setNomes(rs.getString("nomes"));
				msg.setStockclie(rs.getString("stockclie"));
				msg.setLocalidad(rs.getString("localidad"));
				msg.setQty_excess(rs.getString("qty_excess"));
				msg.setSecondfactorconversion(rs.getString("secondfactorconversion"));
				msg.setRegister(rs.getString("register"));
				msg.setReasonid(rs.getString("reasonid"));
				msg.setServiceid(rs.getString("serviceid"));
				msg.setFactorprimary(rs.getString("factorprimary"));
				msg.setFactorsecondary(rs.getString("factorsecondary"));
				msg.setFactorthree(rs.getString("factorthree"));
				msg.setEquivalentqty(rs.getString("equivalentqty"));
				msg.setPietablon(rs.getString("pietablon"));
				msg.setActivemq(rs.getString("activemq"));
				
				messages.add(msg);
			}
		} catch (Exception e) {
			logger.error("Error desconocido", e);
		} 
		result.close();
		return messages;
	}

	public void updateMessageAsSent(QueueMessageStockMoves msg) {
		String sql = "";
		sql = "UPDATE stockmoves SET statusamq = 0 WHERE stkmoveno = '"
			+ msg.getStkmoveno() + "''";
		
		this.database.executeUpdate(sql);
		
		logger.debug(sql);
	}

	public void disconnect() {
		this.database.disconnect();
	}

}
