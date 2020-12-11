package com.jahepi.activemq.dto;

import java.io.Serializable;

public class QueueMessageAdjustment implements Serializable {

	private static final long serialVersionUID = 1;
	
	private String orderno;
	private String descripcion;
	private String stockid;
	private String loccode;
	private String origtrandate;
	private String trandate;
	private String fechaconsumo;
	private String narrative;
	private String qty;
	private String quotation;
	private String url;
	private String userregister;
	private String userprocess;
	private String userauthorized;
	private String type;
	private String reasonid;
	private String service;
	private String massiveadjustment;
	private String factor;
	
	public String getFactor() {
		return factor;
	}
	public void setFactor(String factor) {
		this.factor = factor;
	}
	public String getOrderno() {
		return orderno;
	}
	public void setOrderno(String orderno) {
		this.orderno = orderno;
	}
	public String getDescripcion() {
		return descripcion;
	}
	public void setDescripcion(String descripcion) {
		this.descripcion = descripcion;
	}
	public String getStockid() {
		return stockid;
	}
	public void setStockid(String stockid) {
		this.stockid = stockid;
	}
	public String getLoccode() {
		return loccode;
	}
	public void setLoccode(String loccode) {
		this.loccode = loccode;
	}
	public String getOrigtrandate() {
		return origtrandate;
	}
	public void setOrigtrandate(String origtrandate) {
		this.origtrandate = origtrandate;
	}
	public String getTrandate() {
		return trandate;
	}
	public void setTrandate(String trandate) {
		this.trandate = trandate;
	}
	public String getFechaconsumo() {
		return fechaconsumo;
	}
	public void setFechaconsumo(String fechaconsumo) {
		this.fechaconsumo = fechaconsumo;
	}
	public String getNarrative() {
		return narrative;
	}
	public void setNarrative(String narrative) {
		this.narrative = narrative;
	}
	public String getQty() {
		return qty;
	}
	public void setQty(String qty) {
		this.qty = qty;
	}
	public String getQuotation() {
		return quotation;
	}
	public void setQuotation(String quotation) {
		this.quotation = quotation;
	}
	public String getUrl() {
		return url;
	}
	public void setUrl(String url) {
		this.url = url;
	}
	public String getUserregister() {
		return userregister;
	}
	public void setUserregister(String userregister) {
		this.userregister = userregister;
	}
	public String getUserprocess() {
		return userprocess;
	}
	public void setUserprocess(String userprocess) {
		this.userprocess = userprocess;
	}
	public String getUserauthorized() {
		return userauthorized;
	}
	public void setUserauthorized(String userauthorized) {
		this.userauthorized = userauthorized;
	}
	public String getType() {
		return type;
	}
	public void setType(String type) {
		this.type = type;
	}
	public String getReasonid() {
		return reasonid;
	}
	public void setReasonid(String reasonid) {
		this.reasonid = reasonid;
	}
	public String getService() {
		return service;
	}
	public void setService(String service) {
		this.service = service;
	}
	public String getMassiveadjustment() {
		return massiveadjustment;
	}
	public void setMassiveadjustment(String massiveadjustment) {
		this.massiveadjustment = massiveadjustment;
	}
	
}
