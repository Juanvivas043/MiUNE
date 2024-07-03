/**
*
* Validacion de para los Formularios
*
*@category Une
*@package validator
*@version 0.1
*@author Alton Bell-Smythe abellsmythe@gmail.com, Alan Manuitt alansmanuittb@gmail.com
*@require JQuery & /css/validator.css
*/
var validator = new Object();
(function (){

	validator.insertValidation = function (array){
		$.each(array,function(key,value){
			//Definicion de Datos
			var id 			= "#" + value.input;
			var type 		= value.type;
			var typeEvent 	= "change";
			if(value.fn == 'undefined') { var inject = false; }
			else { inject = true; var fn = value.fn; }
			switch(type) {
				case "numeric":
					var regex = new RegExp("^([0-9]+)$");
					break;
				case "alphabetic":
					var regex = new RegExp("([^\\u0000-\\u0040\\u005B-\\u0060\\u007B-\\u00BF\\u02B0-\\u036F\\u00D7\\u00F7\\u2000-\\u2BFF])+");
					break;
				case "alpha-numeric":
					var regex = new RegExp("^[a-zA-Z]+[a-zA-Z0-9\\s]+[a-zA-Z]$");
					break;
				case "mail":
					var regex = new RegExp("^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\\.[a-zA-Z0-9-]+)+");
					break;
				case "url":
					var regex = new RegExp("([-a-zA-Z0-9^\\p{L}\\p{C}\\u00a1-\\uffff@:%_\\+.~#?&//=]{2,256}){1}(\\.[a-z]{2,4}){1}(\\:[0-9]*)?(\\/[-a-zA-Z0-9\\u00a1-\\uffff\\(\\)@:%,_\\+.~#?&//=]*)?([-a-zA-Z0-9\\(\\)@:%,_\\+.~#?&//=]*)?");
					break;
				case "empty":
					var regex = new RegExp("^[\\w\\s-_(),.;:'\"&ZùÙüÜäàáëèéïìíöòóüùúÄÀÁËÈÉÏÌÍÖÒÓÜÚñÑ]+$");
				break;
				case "phone":
					var regex = new RegExp("^[0-9-.()+]{15}");
				break;
				case "credit-card":
					var regex = new RegExp("^(([0-9]{4})[-|\\s| ]([0-9]{4})[-|\\s| ]([0-9]{4})[-|\\s| ]([0-9]{4}))|(([0-9]{4})[-|\\s| ]([0-9]{6})[-|\\s| ]([0-9]{5}))");
				break;
				case "password":
					var regex = new RegExp("(?=[#$-/:-?{-~!\"^_`\\[\\]a-zA-Z]*([0-9#$-/:-?{-~!\"^_`\\[\\]]))(?=[#$-/:-?{-~!\"^_`\\[\\]a-zA-Z0-9]*[a-zA-Z])[#$-/:-?{-~!\"^_`\\[\\]a-zA-Z0-9]{4,}");
				break;
			}
			//Aplicacion de Validacion 
			$("body").delegate(id,typeEvent,function(){
				var input = $(this);
				var match = regex.test(input.val());
				if(match){input.removeClass("invalid").addClass("valid");}
				else{input.removeClass("valid").addClass("invalid");}
			});
			//Delegamos Funcion
			if(inject){
				$("body").delegate(id,typeEvent,function(){
					fn();
				});
			}

		});
	}

})();