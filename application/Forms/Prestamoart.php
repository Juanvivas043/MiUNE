<?php 

class Forms_Prestamoart extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
  public function init() { 
        
        //$SwapBytes_Jquery = new SwapBytes_Jquery();
                
        $this->setMethod('post');
        $this->setName('prestamoart');
	$this->setOptions(array('escape' => true));
       		
		
       //$clik      = "$.getJSON(urlAjax+'Nuevo/', function(data){executeCmdsFromJSON(data)});";
       $tipo_pres = "$.getJSON(urlAjax+'Nuevo/id/' + $('#tipo_prestamo').val(), function(data){executeCmdsFromJSON(data)});"; 
       $changeCota = "$.getJSON(urlAjax + 'exists/data/' + escape($('#prestamoart').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
       
       $id   = new Zend_Form_Element_Hidden('id');
       $id->removeDecorator('label')
          ->removeDecorator('HtmlTag');

        
       $cota = new Zend_Form_Element_Text('cota');
       $cota->setLabel('Cota')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array(3, 30))
            ->setAttrib('size', 20)
            ->setAttrib('maxlength', 50)
            ->setAttrib('onchange', $changeCota);
       
       $titulo = new Zend_Form_Element_Text('fk_libro'); 
       $titulo->setLabel('Titulo:')
              ->setAttrib('style', 'width: 350px');
       
       $autor = new Zend_Form_Element_Text('fk_autor'); 
       $autor->setLabel('Autor:')
              ->setAttrib('style', 'width: 350px');
       
       $editorial = new Zend_Form_Element_Text('editorial');
       $editorial->setLabel('Editorial')
            ->setAttrib('size', 26)
         /*   ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'editorial' . '-element'))
             ->addDecorator('Label', array('tag' => 'dt'))   */; 
       
       $edicion = new Zend_Form_Element_Text('edicion');
       $edicion->setLabel('Edicion')
            ->setAttrib('size', 8)
        /*    ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'edicion' . '-element'))
             ->addDecorator('Label', array('tag' => 'dd'))  */; 
       
       $ano = new Zend_Form_Element_Text('fk_ano');
       $ano->setLabel('Año')
            ->setAttrib('size', 8)
        /*    ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'fk_ano' . '-element'))
             ->addDecorator('Label', array('tag' => 'dt'))  */ ; 
       
       $pagina = new Zend_Form_Element_Text('pagina');
       $pagina->setLabel('Pagina')
            ->setAttrib('size', 8)
      /*      ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'pagina' . '-element'))
             ->addDecorator('Label', array('tag' => 'dd'))  */;
       
       $volumen = new Zend_Form_Element_Text('volumen');
       $volumen->setLabel('Volumen')
            ->setAttrib('size', 8)
       /*     ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'volumen' . '-element'))
             ->addDecorator('Label', array('tag' => 'dd'))*/ ;
       
       
       $ejemplar = new Zend_Form_Element_Text('ejemplar');
       $ejemplar->setLabel('Ejemplar')
            ->setAttrib('size', 8)
      /*      ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'ejemplar' . '-element'))
             ->addDecorator('Label', array('tag' => 'dt'))   */; 
       
       $nota = new Zend_Form_Element_Text('nota');
       $nota->setLabel('Nota')
            ->setAttrib('size', 8)
     /*       ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'nota' . '-element'))
             ->addDecorator('Label', array('tag' => 'dd')) */ ;
       
       $coleccion = new Zend_Form_Element_Text('coleccion');
       $coleccion->setLabel('Coleccion')
            ->setAttrib('size', 8)
      /*      ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'coleccion' . '-element'))
             ->addDecorator('Label', array('tag' => 'dd'))  */;
       
     
       $comentario = new Zend_Form_Element_Textarea('comentario');
       $comentario->setLabel('Comentario:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('rows', 4)
            ->setAttrib('cols', 39);
           
        $fechadevolucion = new Zend_Form_Element_Text('fecha_devolucion');
        $fechadevolucion->setLabel('Fecha devolucion:')
                    ->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->addValidator('Date', true, array('dd/MM/YYYY'))
                    ->setAttrib('size', 11)
                    ->setAttrib('maxlength', 10);
        
        $fechasolicitud = new Zend_Form_Element_Text('fecha_solicitud');
        $fechasolicitud->setLabel('Fecha Solicitud:')
                    ->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->addValidator('Date', true, array('dd/MM/YYYY'))
                    ->setAttrib('size', 11)
                    ->setAttrib('maxlength', 10);
        
        $fechaestimada = new Zend_Form_Element_Text('fecha_estimada');
        $fechaestimada->setLabel('Fecha Estimada de la devolucion:')
                    ->setRequired(true)
                    ->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->addValidator('Date', true, array('dd/MM/YYYY'))
                    ->setAttrib('size', 11)
                    ->setAttrib('maxlength', 10);
        
        
         $fecha_hide   = new Zend_Form_Element_Hidden('fecha_hide');
         $fecha_hide ->removeDecorator('label')
                    ->removeDecorator('HtmlTag')
                    ->addValidator('Date', true, array('MM/dd/YYYY'))
                    ->setAttrib('size', 11)
                    ->setAttrib('maxlength', 10);
         
        $tipo_prestamo = new Zend_Form_Element_Select('tipo_prestamo'); // cambiar a perfil
        $tipo_prestamo->setLabel('Tipo:')
                 ->setAttrib('style', 'width: 110px')
                  ->clearValidators()
                  ->removeValidator(NULL)
                  ->setAttrib('onclick',$tipo_pres ); 
        
        $prestamo_interno = new Zend_Form_Element_Select('fk_tipo_interno'); // cambiar a perfil
        $prestamo_interno->setLabel('Ubicacion:')
                 ->setAttrib('style', 'width: 110px')
                  ->clearValidators()
                  ->removeValidator(NULL);
             
        $this->addElements(array($id,
                                 $cota,
                                 $titulo,
                                 $autor,
                                // $editorial,
                                // $edicion,
                                 //$ano,
                                 $pagina,
                                 //$volumen,
                                 //$ejemplar,
                                 //$nota,
                                 //$coleccion,
                                 
                                 $fechasolicitud,
                                 $tipo_prestamo,
                                 $prestamo_interno,
                                 $fechaestimada,
                                 $fechadevolucion,
                                 $comentario,
                                 $fecha_hide
                                ));

    }

}
