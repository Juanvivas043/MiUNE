<?php 

class Forms_Bibliotecalibros extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
  public function init() {
        
       
                
        $this->setMethod('post');
        $this->setName('bibliotecalibros');
	$this->setOptions(array('escape' => true));

        $SwapBytes_Jquery = new SwapBytes_Jquery();
         
         
        $changecota = "$.getJSON(urlAjax + 'exists/data/' + escape($('#bibliotecalibros').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
        $changePais = $SwapBytes_Jquery->fillSelect('fk_ciudad','cpais',array('pais'=>'fk_pais'));
               
                
      $id   = new Zend_Form_Element_Hidden('id');
      
      $id->removeDecorator('label')
         ->removeDecorator('HtmlTag');
        
       $cota = new Zend_Form_Element_Text('cota');
       $cota->setLabel('Cota :')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array(4, 20))
            ->setAttrib('size', 12)
            ->setAttrib('maxlength', 11)
            ->setAttrib('onchange', $changecota);
                
        $titulo = new Zend_Form_Element_Text('titulo');
        $titulo->setLabel('Titulo:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
            
   
       $editorial = new Zend_Form_Element_Select('fk_editorial'); 
       $editorial->setLabel('Editorial :')
                 ->setAttrib('style', 'width: 300px');
                  
             
       $pais = new Zend_Form_Element_Select('fk_pais'); 
       $pais->setLabel('Pais:')
                 ->setAttrib('style', 'width: 200px')
                  ->setAttrib('onclick', $changePais);
       
       
       $ciudad = new Zend_Form_Element_Select('fk_ciudad'); 
       $ciudad->setLabel('Ciudad:')
                 ->setAttrib('style', 'width: 200px');
       
       $ano = new Zend_Form_Element_Select('fk_ano'); 
       $ano->setLabel('Año:')
                 ->setAttrib('style', 'width: 200px')
                  ->clearValidators()
                  ->removeValidator();
       
       $edicion = new Zend_Form_Element_Select('edicion'); 
       $edicion->setLabel('Edicion :')
                 ->setAttrib('style', 'width: 300px');
       
       $pagina = new Zend_Form_Element_Text('pagina');
       $pagina->setLabel('Pagina :')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('Digits')   
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
       
       $nota= new Zend_Form_Element_Text('nota');
       $nota->setLabel('Nota :')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
       
       $ejemplar = new Zend_Form_Element_Text('ejemplar');
       $ejemplar->setLabel('Ejemplar :')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
       
       $volumen = new Zend_Form_Element_Text('volumen');
       $volumen->setLabel('Volumen :')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
       
       
       $coleccion = new Zend_Form_Element_Text('coleccion');
       $coleccion->setLabel('Coleccion :')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
       
       $numero = new Zend_Form_Element_Text('numero');
       $numero->setLabel('Numero :')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
        
        
        $this->addElements(array($id,
                                 $cota,
                                 $titulo,
                                 $editorial,
                                 $pais,
                                 $ciudad,
                                 $ano,
                                 $pagina,
                                 $nota,
                                 $ejemplar,
                                 $volumen,
                                 $coleccion,
                                 $numero,
            
                                ));

    }

}

