var arraySelects = ['periodo', 'sede', 'escuela', 'semestre', 'turno', 'seccion', 'subseccion'];
var Pagina1 = new Array('selperiodo');
var urlAjax ="/MiUNE2/reports/horarios/";
var primerfiltro = 0;
var creados = new Array();
var cantidades = new Array();
var paraquit = new Array();
var clases = {
	'Pagina': 'Contenedor',
        'Grupo': 'Pagina',
        'SubGrupo': 'Grupo',
        'Columna': 'SubGrupo',
        'SubColumna': 'Columna'
};
var tipos = new Array('Pagina', 'Grupo', 'SubGrupo', 'Columna', 'SubColumna');

$(document).ready(function(){

$("#loading").ajaxStart(function(){$(this).show();});
$("#loading").ajaxStop(function(){
    $(this).hide();
    alrdyFill = []; 
    primerfiltro = 0;
    segundofiltro = 0;
    });

AddTipo('Pagina','Pagina1', 'Contenedor');
AddTipo('Grupo','Grupo1-1', 'Pagina1');
AddTipo('SubGrupo','SubGrupo1-1-1', 'Grupo1-1');
AddTipo('Columna','Columna1-1-1-1', 'SubGrupo1-1-1');
AddTipo('SubColumna','SubColumna1-1-1-1-1', 'Columna1-1-1-1');

eval(fillSelectRecursive2(urlAjax, Pagina1, 0));
$('#selPeriodo').change(function(){eval(fillSelectRecursive2(urlAjax, Pagina1, 1));});
//$('#selSede').change(function(){eval(fillSelectRecursive2(urlAjax, Pagina1, 2));});
//$('#selEscuela').change(function(){eval(fillSelectRecursive2(urlAjax, Pagina1, 3));});
AsignarAccion(Pagina1, 1);

$("a").live('click', function() {
	if($(this).attr('class') == 'agregar'){
	Agregar($(this).parents('div:first'));
	}else if($(this).attr('class') == 'quitar'){
	Quitar($(this).parents('div:first'));
	}
});


});


function AddTipo (tipo, nombre, padre){
	$('<div id="'+ nombre + '" class="' + tipo + '">').appendTo($('#' + padre));
	AddControles('#' + nombre, tipo);
}

function AddArbolTipos (padre, index){
	primero = 0;
	num = 0;
	hnum = 0;
        swt = 0;	
	nhijos = $(padre).parents('div:first').children('div').length;
if(index > 0){
for(var u = 0; u < nhijos; u++)
{
//	console.log('hijo numero =' + u);
	ultimo = $(padre).parents('div:first').children('div:eq(' + u + ')').attr('id').replace($(padre).parents(':first').children('div:eq(' + u + ')').attr('class'), "");
	indiceultimo = ultimo.lastIndexOf('-');
	hnum = eval(ultimo.slice(indiceultimo + 1)); 
	//hnum = eval($(padre).parents(':first').children('div:eq(' + u + ')').attr('class').replace($(padre).parents(':first').children('div:eq(' + u + ')').attr('id'), ""));
	if(num < hnum)
	{
		num = hnum;
	}
}
//console.log("tiene hijos = " + nhijos + " y el mayor es =" + num);
//console.log(nhijos);
}
//console.log("Tipos a Crear:");
	for(var i = index; i< (tipos.length); i++)
	{
		if(i == 0)
		{
			num = nhijos;
			nombre = tipos[i] + (num + 1); 
			pagnum = num + 1;
			primero++;
			pnombre = eval('clases.' + tipos[i]);
			//$.globalEval('var Pagina' + (num + 1) + ' = new Array(\'selperiodo\', \'selsede\', \'selescuela\');')
		}else if(primero==0){
			//console.log($(padre).parents('div:first'));
			//console.log('tipo padre: ' + tipos[i-1]);
			//console.log($(padre).parents('div:first').attr('id'));
			pnum = $(padre).parents('div:first').attr('id').replace(tipos[i-1],"");
			pnombre =$(padre).parents('div:first').attr('id');
			//console.log("num padre: " + pnum);
			//console.log(nhijos);
			nombre = tipos[i] + pnum + '-' + (num+1);
			primero++;
		}else{
			pnombre = nombre;
			num = nombre.replace(tipos[i-1], "")
			nombre = tipos[i] + (num + '-' + 1); 
		}
		if(swt == 0){
		swt = 1;
		primernombre = nombre;
		primerpadre  = pnombre;
		}
		console.log('Tipo: ' + tipos[i]);
		console.log('Nombre: ' + nombre);
		console.log('Padre: ' + pnombre);
		console.log(primernombre);
		console.log(primerpadre);
		AddTipo (tipos[i], nombre, pnombre);
	}
	padreJS = '$("#' + $('#' + primerpadre).children('table').children().children().children().children('select').attr('id') + '").change(function(){eval(fillSelectRecursive2(urlAjax, Pagina1, Pagina1.indexOf(\'' + $('#' + primernombre).children('table').children().children().children().children('select').attr('id') + "')));console.log('elotro');});";
	console.log(padreJS);
	$.globalEval(padreJS);
	//console.log('Pruebaaaaaaaaa ' + $(padre).attr('id'));
	//console.log('Primer Nombre' + primernombre);
	//console.log($('#' + primernombre).children('table').children().children().children().children('select').attr('id'));
	primernombre = $('#' + primernombre).children('table').children().children().children().children('select').attr('id');
	//console.log(eval('Pagina' + pagnum + '.indexOf(\'' + primernombre + '\')'));		
	primernombre = eval('Pagina1.indexOf(\'' + primernombre + '\')');
	eval('AsignarAccion(Pagina1, ' + (primernombre) + ')');
}

function AddControles (padre, tipo){

$('<table class="horario_header"><tr class="horario_header_color"><td colspan="2"><h2>'+ $(padre).attr('id') + '</h2><a class="agregar"><img src="../images/icons/add.png"></a><a class="quitar"><img src="../images/icons/delete.png"></a></td></tr></table>').appendTo($(padre));
//console.log($(padre).attr('id').replace($(padre).attr('class'), ""));
pnum = $(padre).attr('id').replace($(padre).attr('class'), "");
pagnum = pnum.slice(0, pnum.indexOf('-')); 
switch(tipo)
{
	case 'Pagina':
eval('Pagina1.push("selsede' + pnum + '");');
$('<tr><td>Sede:<select id="selsede' + pnum + '" name="selsede' + pnum + '"></select></td><td>&nbsp;</td></tr>').appendTo($(padre).children('table:first > tbody'));
	break;

	case 'Grupo':
eval('Pagina1.push("selescuela' + pnum + '");');
$('<tr><td>Escuela:<select id="selescuela' + pnum +'" name="selescuela' + pnum +'"></select></td><td>&nbsp;</td></tr>').appendTo($(padre).children('table:first > tbody'));
	break;

	case 'SubGrupo':
eval('Pagina1.push("selturno' + pnum + '");');
$('<tr><td>Turno:<select id="selturno' + pnum +'" name="selturno' + pnum +'" width="80px"></select></td><td>&nbsp;</td></tr>').appendTo($(padre).children('table:first > tbody'));
	break;

	case 'Columna':
eval('Pagina1.push("selsemestre' + pnum + '");');
$('<tr><td>Semestre:<select id="selsemestre' + pnum +'" name="selsemestre' + pnum +'"></select></td><td>&nbsp;</td></tr>').appendTo($(padre).children('table:first > tbody'));
eval('Pagina1.push("selseccion' + pnum + '");');
$('<tr><td>Seccion:<select id="selseccion' + pnum +'" name="selseccion' + pnum +'"></select></td><td>&nbsp;</td></tr>').appendTo($(padre).children('table:first > tbody'));
	break;

	case 'SubColumna':
eval('Pagina1.push("selsubseccion' + pnum + '");');
$('<tr><td>SubSeccion:<select id="selsubseccion' + pnum +'" name="selsubseccion' + pnum +'"></select></td><td>&nbsp;</td></tr>').appendTo($(padre).children('table:first > tbody'));
	break;

	default:

}

}

function Quitar (padre){
//	console.log(padre);
//	console.log($(padre).parents(':first').children('div').length);
	if($(padre).parents(':first').children('div').length > 1){
	QuitarRecursivo(padre, $(padre).children('div').length);
	$(padre).remove();
	}
}

function QuitarRecursivo (padre, nhijos){
//	console.log('Padre -> ' + $(padre).children('table').children().children().children().children('select').attr('id'));
	nselects = $(padre).children('table').children().children().children().children('select').length;
//	console.log($(padre).children('table').children().children().children().children('select:eq(1)').attr('id'));
	pnum = $(padre).attr('id').replace($(padre).attr('class'), "");
	if(pnum.indexOf('-') == -1){
	pagnum = pnum;
	}else{
	pagnum = pnum.slice(0, pnum.indexOf('-')); 
	}
//	console.log($.inArray(selectquit, Pagina1));
	for(var e = 0; e < nselects; e++){
	paraquit.push($(padre).children('table').children().children().children().children('select:eq(' + e + ')').attr('id'));
	selectquit = $(padre).children('table').children().children().children().children('select:eq(' + e + ')').attr('id');
	nquit = $.inArray(selectquit, Pagina1);
//	console.log(Pagina1[nquit]);
	eval('Pagina1.splice(' + nquit + ',1);');
//	console.log(nquit);
	}
//	console.log('NHijos -> ' + nhijos);
	for(var i = 0; i < nhijos; i++){
//		console.log('Ciclo -> ' + i);
		QuitarRecursivo($(padre).children('div:eq(' + i + ')'), $(padre).children('div:eq(' + i + ')').children('div').length);
	}
}

function Agregar (padre){
//	console.log('Padre: ' + $(padre).attr('id'));
//	console.log('Index: ' + $.inArray(padre.attr('class'), tipos));
	AddArbolTipos(padre, $.inArray(padre.attr('class'), tipos));
	//AgregarAcciones(padre);
	//AsignarAccion(padre, Pagina1);
}

function AgregarAcciones(padre){
	selectname = $(padre).children('table').children().children().children().children('select').attr('id');
	pnum = $(padre).attr('id').replace($(padre).attr('class'), "");
	pagnum = pnum.slice(0, pnum.indexOf('-')); 
	valores = selectname.substring(selectname.indexOf(pagnum), selectname.length);
//	console.log(selectname + ' <<<<- valores ->>>> ' + valores);
	prueba = $.grep(Pagina1, function(valor, indice){
		//console.log('valores -> ' + valores);
		//console.log(valores.substring(0, 1));
		//console.log(valor.substring(valor.indexOf(valores.substring(0, 1)), valor.length));
		var1 = valor.substring(valor.indexOf(valores.substring(0, 1)), valor.length);
		var2 = var1.substring(0, valores.length);
		//console.log('valor -> ' + valor + ' indice -> ' + indice + ' var2 -> ' + var2);
	//	console.log(valor.indexOf(valores));
		if(valores == var2){
	//	console.log('true');
		ultind = indice;
		}else{
	//	console.log('false');
		}
	});
//	console.log('ultimo indice : ' + ultind);

}

function AsignarAccion(arreglo, indice){
	JS = '';
	for(var i = 0; i < ((arreglo.length-1) - indice); i++){
	if($.inArray(arreglo[indice+i],creados) == -1){
	//JS += '$("#' + arreglo[indice+i] + '").change(function(){eval(fillSelectRecursive2(urlAjax, Pagina1, ' + (indice+i+1) + "));console.log('hey');});";
	JS += '$("#' + arreglo[indice+i] + '").change(function(){eval(fillSelectRecursive2(urlAjax, Pagina1, Pagina1.indexOf(\'' + arreglo[indice+i+1] + "')));console.log('hey');});";
//	console.log(JS);
	//console.log(JS);
	//console.log(arreglo[indice+i]);
	creados.push(arreglo[indice+i]);
	//console.log(JS);
	}
	$.globalEval(JS);
	}
	eval(fillSelectRecursive2(urlAjax, arreglo, indice));
}

function ucfirst (str) {
    // Makes a string's first character uppercase  
    // 
    // version: 1009.2513
    // discuss at: http://phpjs.org/functions/ucfirst
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: ucfirst('kevin van zonneveld');
    // *     returns 1: 'Kevin van zonneveld'
    str += '';
    var f = str.charAt(0).toUpperCase();
    return f + str.substr(1);
}


function fillSelectRecursive2(Path, Selects, Index) {
    var Params = "";
    var JS     = "";
    if(Index == 1){
	    console.log(Selects[Index]);
	    primerfiltro = 1;
    }
    if(Index > 1 && Selects[Index-1].indexOf('-') == -1){
	    console.log('Sede');
	    segundofiltro = 1;
    }
    if(Index >= 1){
    PagNum   = eval('$(\'#' + Selects[Index] + '\').parents(\'.Pagina\').attr(\'id\').replace($(\'#' + Selects[Index] + '\').parents(\'.Pagina\').attr(\'class\'), "")');
    //console.log('Pagina para el fill : ' + PagNum);
    //console.log('Index -> ' + Selects[Index].indexOf(PagNum));
    NumPadre = Selects[Index].substring(Selects[Index].indexOf(PagNum), Selects[Index].length);
	    //console.log(Selects[Index] + ' <- NUMPADRE -> ' + NumPadre);
    //console.log('Numero Padre para el fill : ' + NumPadre);
    }
    if(Index > 0 && Index < Selects.length) {
	   // console.log('Ciclo para : ' + Selects[Index]);
        for(i = 0; i < Selects.length - 1; i++) {
	    if(i >= 1){
	    //console.log(NumPadre);
    	    SelPagNum   = eval('$(\'#' + Selects[i] + '\').parents(\'.Pagina\').attr(\'id\').replace($(\'#' + Selects[i] + '\').parents(\'.Pagina\').attr(\'class\'), "")');
	    //NumHijo = Selects[i].substring(Selects[i].indexOf(SelPagNum), Selects[i].length).substring(0, NumPadre.length); 
            //console.log(Selects[i]);
	    if(Selects[i].indexOf('-') != -1){
	    NumHijo = Selects[i].substring(Selects[i].length, (Selects[i].indexOf('-') - 1)).substring(0, NumPadre.length); 
	    //console.log('1 ' + Selects[i] + ' <- NUMHIJO -> ' + NumHijo);
	    }else{
	    NumHijo = Selects[i].substring(Selects[i].length, (Selects[i].indexOf(SelPagNum))).substring(0, NumPadre.length); 
	    }
	    //console.log(Selects[i] + ' <- NUMHIJO -> ' + NumHijo);
	    //console.log(i + ' NumHijo: ' + NumHijo + ' NumPadre: ' + Selects[Index].substring(Selects[Index].indexOf(SelPagNum), Selects[Index].length).substring(0, NumHijo.length));
	    if(NumHijo == NumPadre.substring(0, NumHijo.length)){
            //console.log(Index + ' de ' + Selects[Index] + ' rellenar ' + Selects[i] + ' num -> ' + NumHijo + ' numpadre -> ' + NumPadre);
	    dir =  Selects[i].substring(3, Selects[i].length).substring(0, Selects[i].substring(3, Selects[i].length).indexOf(SelPagNum));
	    //dir =  Selects[i].substring(3, Selects[i].length).substring(0, Selects[i].substring(3, Selects[i].length).indexOf(PagNum));
	    //console.log('DIR !!!!!!! _>>>>>>>>>' + dir + ' PAGNUM >>>>>>>' + SelPagNum);
            Params +=dir + ":$('select#" + Selects[i] + "').val(),";
	    //console.log(Index + ' ' + Params);
	    }else{
            //console.log(i + ' REBOTADO ' + Index + ' de ' + Selects[Index] + ' rellenar ' + Selects[i] + ' num -> ' + NumHijo + ' numpadre -> ' + NumPadre);
	    }
	    }else{
            Params += (Selects[i].substring(3, Selects[i].length)) + ":$('select#" + Selects[i] + "').val(),";
	    }
        }

        Params = Params.substr(0, Params.length - 1);
    }
    if(Index >= 1){
    nnombre = '#' + Selects[Index];
    //console.log('nnombre -> ' + nnombre); 
//    pnum = $(nnombre).parents('.Pagina').attr('id').replace($(nnombre).parents('.Pagina').attr('class'), "");
    pnum = eval('$(\'' + nnombre + '\').parents(\'.Pagina\').attr(\'id\').replace($(\'' + nnombre + '\').parents(\'.Pagina\').attr(\'class\'), "")');
    //console.log('Pagina num -> ' + pnum);
    nselect = Selects[Index].substring(3, Selects[Index].length);
    //console.log('or aqui ->>>' + nselect.indexOf(pnum));
    //console.log('nombre -> ' + nselect.substring(0, nselect.indexOf(pnum))); 
    dir =  nselect.substring(0, nselect.indexOf(pnum));
    //console.log(Selects[Index]);
    //console.log('dir ->> ' + dir);
    //console.log('Path ->> ' + dir);
    JS  = "$.getJSON('" + Path + dir + "',";
    }else{
    JS  = "$.getJSON('" + Path + (Selects[Index].substring(3, Selects[Index].length)) + "',";
    }

    if(Params.length > 0) {
        JS += "{" + Params + "},";
    }
    JS += "function(j){";
    JS += "fillSelect('select#" +  Selects[Index] + "', j);";
    if(Index == 0){
    if((Index + 1) < Selects.length && $.inArray(Selects[Index + 1],alrdyFill) == -1) {
        JS += fillSelectRecursive2(Path, Selects, Index + 1);
    alrdyFill.push(Selects[Index]);
    }
    }else{
    if((Index + 1) < Selects.length && $.inArray(Selects[Index + 1],alrdyFill) == -1) {
	SelnhPagNum   = eval('$(\'#' + Selects[Index + 1] + '\').parents(\'.Pagina\').attr(\'id\').replace($(\'#' + Selects[Index + 1] + '\').parents(\'.Pagina\').attr(\'class\'), "")');
    	SelpdPagNum   = eval('$(\'#' + Selects[Index] + '\').parents(\'.Pagina\').attr(\'id\').replace($(\'#' + Selects[Index] + '\').parents(\'.Pagina\').attr(\'class\'), "")');
	NumHijo = Selects[Index+1].substring(Selects[Index+1].indexOf(SelnhPagNum), Selects[Index+1].length).substring(0, NumPadre.length); 
	//console.log('NumHijo= ' + NumHijo + ' NumPadre= ' + Selects[Index].substring(Selects[Index].indexOf(PagNum), Selects[Index].length).substring(0, (NumHijo.length - 2))); 
	    if(NumHijo == NumPadre.substring(0, NumHijo.length) || primerfiltro == 1){
		    //console.log('llamar a ' + Selects[Index + 1]);
        JS += fillSelectRecursive2(Path, Selects, Index + 1);
    	alrdyFill.push(Selects[Index]);
	    }else if (segundofiltro == 1 && SelpdPagNum == SelnhPagNum){
        JS += fillSelectRecursive2(Path, Selects, Index + 1);
    	alrdyFill.push(Selects[Index]);
	    // }else if ( NumHijo.substring(0, NumHijo.length - 2) != '' && NumHijo.substring(0, NumHijo.length - 2) == NumPadre.substring(0, NumHijo.length - 2)){
	   // console.log('Rebotado ->>> ' + Selects[Index+1]); 
    } else if(buscarproximo(Selects[Index]) != ''){
        // JS += fillSelectRecursive2(Path, Selects, Index + 1);
        JS += fillSelectRecursive2(Path, Selects, Pagina1.indexOf(buscarproximo(Selects[Index])));
    	alrdyFill.push(Selects[Index]);
	    }else {
	    console.log('ahora aqui' + Selects[Index+1]);
	    }
    }else{
    //console.log(Selects[Index]);
    }
    }
    JS += "});";
    //console.log(JS);
    return JS;

}

function buscarproximo (nombre){
priubic = $.inArray(nombre, Pagina1);
if(priubic == -1){
   console.log('Nombre Invalido');
   return;
}else{
   console.log('Ubicacion del Primero: '+priubic);
   prinum = eval('$(\'#' + Pagina1[priubic] + '\').parents(\'.Pagina\').attr(\'id\').replace($(\'#' + Pagina1[priubic] + '\').parents(\'.Pagina\').attr(\'class\'), "")');
   numberspri = (Pagina1[priubic].substring(Pagina1[priubic].indexOf(prinum), Pagina1[priubic].length));
   console.log('Numero del Primero: '+prinum);
   console.log('Numeros del Primero: '+numberspri);
   for (var i = (priubic + 1); i < Pagina1.length; i++) {
   segnum = eval('$(\'#' + Pagina1[i] + '\').parents(\'.Pagina\').attr(\'id\').replace($(\'#' + Pagina1[i] + '\').parents(\'.Pagina\').attr(\'class\'), "")');
   numbersseg = (Pagina1[i].substring(Pagina1[i].indexOf(segnum), Pagina1[i].length));
   console.log(Pagina1[i]+' Numeros del Primero: '+numbersseg);
   console.log(numberspri+' -> '+numbersseg.substring(0,numberspri.length));
   if (numberspri.length == numbersseg.length) {
      console.log('mismo tamaño');
      console.log(numberspri.lastIndexOf('-',numberspri.length)+' -> '+numberspri.substring(0, numberspri.lastIndexOf('-',numberspri.length)));
      numberspri = numberspri.substring(0, numberspri.lastIndexOf('-',numberspri.length));
   }
   siguiente = Pagina1[i];
   if (numberspri == numbersseg.substring(0,numberspri.length)) {
      break;
   }else{
      siguiente = '';
   };
   };
   return siguiente;
};
};
