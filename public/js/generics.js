var alrdyFill = new Array();

function fillSelect(name, values) {
    if(values != null && values.length > 0) {
        $(name).html('');
        for (var i = 0; i < values.length; i++) {
            $(name).append("<option value='" + values[i].optionValue + "'>" + values[i].optionDisplay + "</option>");
            
            if(values[i].optionStyle != null) {
                $.each(values[i].optionStyle, function(style, property) {
                    $(name + ' option:eq(0)').css(style, property);
                });
            }
        }
        $(name + ' option:first').attr('selected', 'selected');
        $(name).attr("disabled", "");
    } else {
        clearSelect(name);
    }
}

function fillSelectRecursive(Path, Selects, Index) {
    var Params = "";
    var JS     = "";

    if(Index > 0 && Index < arraySelects.length) {
        for(i = 0; i < arraySelects.length - 1; i++) {
            Params += Selects[i] + ":$('select#sel" + Selects[i] + "').val(),";
        }

        Params = Params.substr(0, Params.length - 1);
    }

    JS  = "$.getJSON('" + Path + Selects[Index] + "',";

    if(Params.length > 0) {
        JS += "{" + Params + "},";
    }
    JS += "function(j){";
    JS += "fillSelect('select#sel" +  Selects[Index] + "', j);";
    if((Index + 1) < arraySelects.length) {
        JS += fillSelectRecursive(Path, Selects, Index + 1);
    }
    JS += "});";
    return JS;
}

function clearSelect(name) {
    $(name).html('');
    $(name).attr("disabled", "disabled");
}

function clearTextAndDisable(name) {
    $(name).val('');
    $(name).attr("disabled", "disabled");
}

function executeCmdsFromJSON(j) {
    if(j != null && j.length > 0) {
        for (var i = 0; i < j.length; i++) {

        $.globalEval('' + j[i] + '');
                    
        }
    }
}

function form_elements_clear(ele) {
    $(ele).find(':input').each(function() {
        switch(this.type) {
            case 'password':
            case 'select-multiple':
            case 'select-one':
            case 'text':
            case 'textarea':
                $(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
        }
    });
}
