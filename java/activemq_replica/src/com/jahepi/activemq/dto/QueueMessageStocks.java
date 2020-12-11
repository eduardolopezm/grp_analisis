package com.jahepi.activemq.dto;
import java.io.Serializable;

public class QueueMessageStocks implements Serializable {
	
	private static final long serialVersionUID = 1;
	
	private String stockid;
	private String categoryid;
	private String description;
	private String longdescription;
	private String manufacturer;
	private String units;
	private String mbflag;
	private String actualcost;
	private String lastcost;
	private String materialcost;
	private String labourcost;
	private String overheadcost;
	private String lowestlevel;
	private String discontinued;
	private String controlled;
	private String eoq;
	private String volume;
	private String kgs;
	private String barcode;
	private String discountcategory;
	private String taxcatid;
	private String taxcatidret;
	private String serialised;
	private String appendfile;
	private String perishable;
	private String decimalplaces;
	private String netweight;
	private String idclassproduct;
	private String fecha_modificacion;
	private String height;
	private String width;
	private String large;
	private String factorconversionpaq;
	private String factorconversionpz;
	private String stockneodata;
	private String purchgroup;
	private String idjerarquia;
	private String addunits;
	private String secuunits;
	private String recipeunits;
	private String factorrecipe;
	private String addcategory;
	private String deliverydays;
	private String tolerancedays;
	private String estatusstock;
	private String eq_conversion_costo;
	private String unitstemporal;
	private Integer SAPActualiza;
	private String tipo;
	private String spes;
	private String stockautor;
	private String lastcurcostdate;
	private String nextserialno;
	private String pansize;
	private String shrinkfactor;
	private String stocksupplier;
	private String securitypoint;
	private String pkg_type;
	private String idetapaflujo;
	private String flagcommission;
	private String fijo;
	private String stockupdate;
	private String isbn;
	private String grade;
	private String subject;
	private String deductibleflag;
	private String u_typeoperation;
	private String typeoperationdiot;
	private String fichatecnica;
	private String percentfactorigi;
	private String OrigenCountry;
	private String OrigenDate;
	private String inpdfgroup;
	private String flagadvance;
	private String eq_stockid;
	private String unitequivalent;
	private String unitssec;
	private String unitstthree;
	private String factorthree;
	private String factorprimary;
	private String factorsecondary;
	
	public String getFactorsecondary() {
		return factorsecondary;
	}
	public void setFactorsecondary(String factorsecondary) {
		this.factorsecondary = factorsecondary;
	}
	public String getSpes() {
		return spes;
	}
	public void setSpes(String spes) {
		this.spes = spes;
	}
	public String getStockautor() {
		return stockautor;
	}
	public void setStockautor(String stockautor) {
		this.stockautor = stockautor;
	}
	public String getLastcurcostdate() {
		return lastcurcostdate;
	}
	public void setLastcurcostdate(String lastcurcostdate) {
		this.lastcurcostdate = lastcurcostdate;
	}
	public String getNextserialno() {
		return nextserialno;
	}
	public void setNextserialno(String nextserialno) {
		this.nextserialno = nextserialno;
	}
	public String getPansize() {
		return pansize;
	}
	public void setPansize(String pansize) {
		this.pansize = pansize;
	}
	public String getShrinkfactor() {
		return shrinkfactor;
	}
	public void setShrinkfactor(String shrinkfactor) {
		this.shrinkfactor = shrinkfactor;
	}
	public String getStocksupplier() {
		return stocksupplier;
	}
	public void setStocksupplier(String stocksupplier) {
		this.stocksupplier = stocksupplier;
	}
	public String getSecuritypoint() {
		return securitypoint;
	}
	public void setSecuritypoint(String securitypoint) {
		this.securitypoint = securitypoint;
	}
	public String getPkg_type() {
		return pkg_type;
	}
	public void setPkg_type(String pkg_type) {
		this.pkg_type = pkg_type;
	}
	public String getIdetapaflujo() {
		return idetapaflujo;
	}
	public void setIdetapaflujo(String idetapaflujo) {
		this.idetapaflujo = idetapaflujo;
	}
	public String getFlagcommission() {
		return flagcommission;
	}
	public void setFlagcommission(String flagcommission) {
		this.flagcommission = flagcommission;
	}
	public String getFijo() {
		return fijo;
	}
	public void setFijo(String fijo) {
		this.fijo = fijo;
	}
	public String getStockupdate() {
		return stockupdate;
	}
	public void setStockupdate(String stockupdate) {
		this.stockupdate = stockupdate;
	}
	public String getIsbn() {
		return isbn;
	}
	public void setIsbn(String isbn) {
		this.isbn = isbn;
	}
	public String getGrade() {
		return grade;
	}
	public void setGrade(String grade) {
		this.grade = grade;
	}
	public String getSubject() {
		return subject;
	}
	public void setSubject(String subject) {
		this.subject = subject;
	}
	public String getDeductibleflag() {
		return deductibleflag;
	}
	public void setDeductibleflag(String deductibleflag) {
		this.deductibleflag = deductibleflag;
	}
	public String getU_typeoperation() {
		return u_typeoperation;
	}
	public void setU_typeoperation(String u_typeoperation) {
		this.u_typeoperation = u_typeoperation;
	}
	public String getTypeoperationdiot() {
		return typeoperationdiot;
	}
	public void setTypeoperationdiot(String typeoperationdiot) {
		this.typeoperationdiot = typeoperationdiot;
	}
	public String getFichatecnica() {
		return fichatecnica;
	}
	public void setFichatecnica(String fichatecnica) {
		this.fichatecnica = fichatecnica;
	}
	public String getPercentfactorigi() {
		return percentfactorigi;
	}
	public void setPercentfactorigi(String percentfactorigi) {
		this.percentfactorigi = percentfactorigi;
	}
	public String getOrigenCountry() {
		return OrigenCountry;
	}
	public void setOrigenCountry(String origenCountry) {
		OrigenCountry = origenCountry;
	}
	public String getOrigenDate() {
		return OrigenDate;
	}
	public void setOrigenDate(String origenDate) {
		OrigenDate = origenDate;
	}
	public String getInpdfgroup() {
		return inpdfgroup;
	}
	public void setInpdfgroup(String inpdfgroup) {
		this.inpdfgroup = inpdfgroup;
	}
	public String getFlagadvance() {
		return flagadvance;
	}
	public void setFlagadvance(String flagadvance) {
		this.flagadvance = flagadvance;
	}
	public String getEq_stockid() {
		return eq_stockid;
	}
	public void setEq_stockid(String eq_stockid) {
		this.eq_stockid = eq_stockid;
	}
	public String getUnitequivalent() {
		return unitequivalent;
	}
	public void setUnitequivalent(String unitequivalent) {
		this.unitequivalent = unitequivalent;
	}
	public String getUnitssec() {
		return unitssec;
	}
	public void setUnitssec(String unitssec) {
		this.unitssec = unitssec;
	}
	public String getUnitstthree() {
		return unitstthree;
	}
	public void setUnitstthree(String unitstthree) {
		this.unitstthree = unitstthree;
	}
	public String getFactorthree() {
		return factorthree;
	}
	public void setFactorthree(String factorthree) {
		this.factorthree = factorthree;
	}
	public String getFactorprimary() {
		return factorprimary;
	}
	public void setFactorprimary(String factorprimary) {
		this.factorprimary = factorprimary;
	}
	public String getTipo() {
		return tipo;
	}
	public void setTipo(String tipo) {
		this.tipo = tipo;
	}
	public String getStockid() {
		return stockid;
	}
	public void setStockid(String stockid) {
		this.stockid = stockid;
	}
	public String getCategoryid() {
		return categoryid;
	}
	public void setCategoryid(String categoryid) {
		this.categoryid = categoryid;
	}
	public String getDescription() {
		return description;
	}
	public void setDescription(String description) {
		this.description = description;
	}
	public String getLongdescription() {
		return longdescription;
	}
	public void setLongdescription(String longdescription) {
		this.longdescription = longdescription;
	}
	public String getManufacturer() {
		return manufacturer;
	}
	public void setManufacturer(String manufacturer) {
		this.manufacturer = manufacturer;
	}
	public String getUnits() {
		return units;
	}
	public void setUnits(String units) {
		this.units = units;
	}
	public String getMbflag() {
		return mbflag;
	}
	public void setMbflag(String mbflag) {
		this.mbflag = mbflag;
	}
	public String getActualcost() {
		return actualcost;
	}
	public void setActualcost(String actualcost) {
		this.actualcost = actualcost;
	}
	public String getLastcost() {
		return lastcost;
	}
	public void setLastcost(String lastcost) {
		this.lastcost = lastcost;
	}
	public String getMaterialcost() {
		return materialcost;
	}
	public void setMaterialcost(String materialcost) {
		this.materialcost = materialcost;
	}
	public String getLabourcost() {
		return labourcost;
	}
	public void setLabourcost(String labourcost) {
		this.labourcost = labourcost;
	}
	public String getOverheadcost() {
		return overheadcost;
	}
	public void setOverheadcost(String overheadcost) {
		this.overheadcost = overheadcost;
	}
	public String getLowestlevel() {
		return lowestlevel;
	}
	public void setLowestlevel(String lowestlevel) {
		this.lowestlevel = lowestlevel;
	}
	public String getDiscontinued() {
		return discontinued;
	}
	public void setDiscontinued(String discontinued) {
		this.discontinued = discontinued;
	}
	public String getControlled() {
		return controlled;
	}
	public void setControlled(String controlled) {
		this.controlled = controlled;
	}
	public String getEoq() {
		return eoq;
	}
	public void setEoq(String eoq) {
		this.eoq = eoq;
	}
	public String getVolume() {
		return volume;
	}
	public void setVolume(String volume) {
		this.volume = volume;
	}
	public String getKgs() {
		return kgs;
	}
	public void setKgs(String kgs) {
		this.kgs = kgs;
	}
	public String getBarcode() {
		return barcode;
	}
	public void setBarcode(String barcode) {
		this.barcode = barcode;
	}
	public String getDiscountcategory() {
		return discountcategory;
	}
	public void setDiscountcategory(String discountcategory) {
		this.discountcategory = discountcategory;
	}
	public String getTaxcatid() {
		return taxcatid;
	}
	public void setTaxcatid(String taxcatid) {
		this.taxcatid = taxcatid;
	}
	public String getTaxcatidret() {
		return taxcatidret;
	}
	public void setTaxcatidret(String taxcatidret) {
		this.taxcatidret = taxcatidret;
	}
	public String getSerialised() {
		return serialised;
	}
	public void setSerialised(String serialised) {
		this.serialised = serialised;
	}
	public String getAppendfile() {
		return appendfile;
	}
	public void setAppendfile(String appendfile) {
		this.appendfile = appendfile;
	}
	public String getPerishable() {
		return perishable;
	}
	public void setPerishable(String perishable) {
		this.perishable = perishable;
	}
	public String getDecimalplaces() {
		return decimalplaces;
	}
	public void setDecimalplaces(String decimalplaces) {
		this.decimalplaces = decimalplaces;
	}
	public String getNetweight() {
		return netweight;
	}
	public void setNetweight(String netweight) {
		this.netweight = netweight;
	}
	public String getIdclassproduct() {
		return idclassproduct;
	}
	public void setIdclassproduct(String idclassproduct) {
		this.idclassproduct = idclassproduct;
	}
	public String getFecha_modificacion() {
		return fecha_modificacion;
	}
	public void setFecha_modificacion(String fecha_modificacion) {
		this.fecha_modificacion = fecha_modificacion;
	}
	public String getHeight() {
		return height;
	}
	public void setHeight(String height) {
		this.height = height;
	}
	public String getWidth() {
		return width;
	}
	public void setWidth(String width) {
		this.width = width;
	}
	public String getLarge() {
		return large;
	}
	public void setLarge(String large) {
		this.large = large;
	}
	public String getFactorconversionpaq() {
		return factorconversionpaq;
	}
	public void setFactorconversionpaq(String factorconversionpaq) {
		this.factorconversionpaq = factorconversionpaq;
	}
	public String getFactorconversionpz() {
		return factorconversionpz;
	}
	public void setFactorconversionpz(String factorconversionpz) {
		this.factorconversionpz = factorconversionpz;
	}
	public String getStockneodata() {
		return stockneodata;
	}
	public void setStockneodata(String stockneodata) {
		this.stockneodata = stockneodata;
	}
	public String getPurchgroup() {
		return purchgroup;
	}
	public void setPurchgroup(String purchgroup) {
		this.purchgroup = purchgroup;
	}
	public String getIdjerarquia() {
		return idjerarquia;
	}
	public void setIdjerarquia(String idjerarquia) {
		this.idjerarquia = idjerarquia;
	}
	public String getAddunits() {
		return addunits;
	}
	public void setAddunits(String addunits) {
		this.addunits = addunits;
	}
	public String getSecuunits() {
		return secuunits;
	}
	public void setSecuunits(String secuunits) {
		this.secuunits = secuunits;
	}
	public String getRecipeunits() {
		return recipeunits;
	}
	public void setRecipeunits(String recipeunits) {
		this.recipeunits = recipeunits;
	}
	public String getFactorrecipe() {
		return factorrecipe;
	}
	public void setFactorrecipe(String factorrecipe) {
		this.factorrecipe = factorrecipe;
	}
	public String getAddcategory() {
		return addcategory;
	}
	public void setAddcategory(String addcategory) {
		this.addcategory = addcategory;
	}
	public String getDeliverydays() {
		return deliverydays;
	}
	public void setDeliverydays(String deliverydays) {
		this.deliverydays = deliverydays;
	}
	public String getTolerancedays() {
		return tolerancedays;
	}
	public void setTolerancedays(String tolerancedays) {
		this.tolerancedays = tolerancedays;
	}
	public String getEstatusstock() {
		return estatusstock;
	}
	public void setEstatusstock(String estatusstock) {
		this.estatusstock = estatusstock;
	}
	public String getEq_conversion_costo() {
		return eq_conversion_costo;
	}
	public void setEq_conversion_costo(String eq_conversion_costo) {
		this.eq_conversion_costo = eq_conversion_costo;
	}
	public String getUnitstemporal() {
		return unitstemporal;
	}
	public void setUnitstemporal(String unitstemporal) {
		this.unitstemporal = unitstemporal;
	}
	
	public Integer getSAPActualiza() {
		return SAPActualiza;
	}
	public void setSAPActualiza(Integer sAPActualiza) {
		SAPActualiza = sAPActualiza;
	}

}
