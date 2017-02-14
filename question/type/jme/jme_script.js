function getSmilesEdit(buttonname){
    var buttonnumber = buttonname.slice(7,-1);
	textfieldid = 'id_answer_' + buttonnumber;
	document.getElementById(textfieldid).value = document.JME1.smiles();
}

function  setJSMEoptions() {
    var options = document.getElementById("id_jmeoptions").value;
    document.JME1.options(options);
}

function jsmeOnLoad() {
    setJSMEoptions();
}