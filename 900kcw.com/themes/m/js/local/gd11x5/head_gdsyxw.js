var lotCode = lotCode.gdsyxw;
var headMethod = {};
headMethod.loadHeadData = function(issue, id) {
	pubmethod.ajaxHead.gd11x5(issue, boxId);
}
headMethod.headData = function(jsondata, id) { 
	pubmethod.creatHead.syx5(jsondata, id);
	tools.setTimefun_shiyixw();
} 