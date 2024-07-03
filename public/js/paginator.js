function goPage(id){
	$.ajax({
      dataType: 'json',
      type: 'POST',
      url: urlAjax + 'list/',
      data: { 
        page: id
    },
      success: function(data){executeCmdsFromJSON(data);}
    });


    /*$.getJSON('/transactions/estudiantes/list/buscar//filters/txtBuscar%3D/page/10', 
	function(data){executeCmdsFromJSON(data)}
	); */
}