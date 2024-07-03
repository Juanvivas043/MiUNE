function destino_usuarios(){
        if(typeof $.mensajes == 'undefined'){
    $.getJSON(urlAjax + "usuarios/", function(d){
    $.mensajes = {
            usuarios: d
                    };

    autocompleteUsuarios()

});
    }else{
        if(typeof $.mensajes.usuarios == 'undefined'){
    $.getJSON(urlAjax + "usuarios/", function(d){
    $.mensajes = {
            usuarios: d
                    };

    autocompleteUsuarios()

});
        }else{
        autocompleteUsuarios()
        }
    }
}

function destino_grupos(){
    if(typeof $.mensajes == 'undefined'){
    $.getJSON(urlAjax + "grupos/", function(d){
    $.mensajes = {
            grupos: d
                    };

    autocompleteGrupos()

});
    }else{
        if(typeof $.mensajes.grupos == 'undefined'){
    $.getJSON(urlAjax + "grupos/", function(d){
    $.mensajes = {
            grupos: d
                    };

    autocompleteGrupos()

});
        }else{
        autocompleteGrupos()
        }
    }
}

function destino_asignaciones(){
    if(typeof $.mensajes == 'undefined'){
    $.getJSON(urlAjax + "asignaciones/", function(d){
    $.mensajes = {
            asignaciones: d
                    };

    autocompleteAsignaciones()

});
    }else{
        if(typeof $.mensajes.asignaciones == 'undefined'){
    $.getJSON(urlAjax + "asignaciones/", function(d){
    $.mensajes = {
            asignaciones: d
                    };

    autocompleteAsignaciones()

});
        }else{
        autocompleteAsignaciones()
        }
    }
}

function autocompleteUsuarios(){
    console.log('usuarios load');
    $("#usuarios").autocomplete($.mensajes.usuarios, {
            multiple: true,
            multipleSeparator: "/ ",
            minChars: 0,
            width: 310,
            matchContains: false,
            autoFill: false,
            formatItem: function(row, i, max) {
                    return i + "/" + max + ": \"" + row.nombre + "\" (" + row.cedula + ")";
            },
            formatMatch: function(row, i, max) {
                    return row.nombre + " " + row.cedula;
            },
            formatResult: function(row) {
                    return row.cedula;
            }
    });
}

function autocompleteGrupos(){
    console.log('grupos load');

    $("#grupos").autocomplete($.mensajes.grupos, {
            multiple: true,
            multipleSeparator: "/ ",
            minChars: 0,
            width: 310,
            matchContains: false,
            autoFill: false,
            formatItem: function(row, i, max) {
                    return i + "/" + max + ": \"" + row.nombre + "\"";
            },
            formatMatch: function(row, i, max) {
                    return row.nombre;
            },
            formatResult: function(row) {
                    return row.id;
            }
    });
}

function autocompleteAsignaciones(){
    console.log('asignaciones load');

    $("#asignaciones").autocomplete($.mensajes.asignaciones, {
            multiple: true,
            multipleSeparator: "/ ",
            minChars: 0,
            width: 310,
            matchContains: false,
            autoFill: false,
            formatItem: function(row, i, max) {
                    return i + "/" + max + ": " + row.codigo + " \"" + row.materia + "\"";
            },
            formatMatch: function(row, i, max) {
                    return row.materia;
            },
            formatResult: function(row) {
                    return row.id;
            }
    });
}

function responder(id){
    $.getJSON(urlAjax + "addoreditload?fk_mensaje=" + id + "", function(d){
        executeCmdsFromJSON(d)
    });  
}

function reenviar(id){
    $.getJSON(urlAjax + "addoreditload?pk_mensaje=" + id + "&reenviar=1", function(d){
        executeCmdsFromJSON(d)
    });  
}

function enviar(id){
    $.getJSON(urlAjax + "addoreditload?pk_mensaje=" + id + "&reenviar=0", function(d){
        executeCmdsFromJSON(d)
    });  
}