package com.jahepi.activemq.dto;

import java.io.Serializable;

public class QueueMessageLockStock implements Serializable {
	
	private static final long serialVersionUID = 1;
	
	private String loccode;
	private String stockid;
	private String quantity;
	private String reorderlevel;
	private String ontransit;
	private String quantityv2;
	private String localidad;
	private String minimumlevel;
	private String timefactor;
	private String delay;
	private String qtybysend;
	private String quantityprod;
	private String loccode_aux;
	private String secondfactorconversion;
	private String activemq;
	
	public String getActivemq() {
		return activemq;
	}
	public void setActivemq(String activemq) {
		this.activemq = activemq;
	}
	public String getLoccode() {
		return loccode;
	}
	public void setLoccode(String loccode) {
		this.loccode = loccode;
	}
	public String getStockid() {
		return stockid;
	}
	public void setStockid(String stockid) {
		this.stockid = stockid;
	}
	public String getQuantity() {
		return quantity;
	}
	public void setQuantity(String quantity) {
		this.quantity = quantity;
	}
	public String getReorderlevel() {
		return reorderlevel;
	}
	public void setReorderlevel(String reorderlevel) {
		this.reorderlevel = reorderlevel;
	}
	public String getOntransit() {
		return ontransit;
	}
	public void setOntransit(String ontransit) {
		this.ontransit = ontransit;
	}
	public String getQuantityv2() {
		return quantityv2;
	}
	public void setQuantityv2(String quantityv2) {
		this.quantityv2 = quantityv2;
	}
	public String getLocalidad() {
		return localidad;
	}
	public void setLocalidad(String localidad) {
		this.localidad = localidad;
	}
	public String getMinimumlevel() {
		return minimumlevel;
	}
	public void setMinimumlevel(String minimumlevel) {
		this.minimumlevel = minimumlevel;
	}
	public String getTimefactor() {
		return timefactor;
	}
	public void setTimefactor(String timefactor) {
		this.timefactor = timefactor;
	}
	public String getDelay() {
		return delay;
	}
	public void setDelay(String delay) {
		this.delay = delay;
	}
	public String getQtybysend() {
		return qtybysend;
	}
	public void setQtybysend(String qtybysend) {
		this.qtybysend = qtybysend;
	}
	public String getQuantityprod() {
		return quantityprod;
	}
	public void setQuantityprod(String quantityprod) {
		this.quantityprod = quantityprod;
	}
	public String getLoccode_aux() {
		return loccode_aux;
	}
	public void setLoccode_aux(String loccode_aux) {
		this.loccode_aux = loccode_aux;
	}
	public String getSecondfactorconversion() {
		return secondfactorconversion;
	}
	public void setSecondfactorconversion(String secondfactorconversion) {
		this.secondfactorconversion = secondfactorconversion;
	}

}
