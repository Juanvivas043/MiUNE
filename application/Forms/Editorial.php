<?php 

class Forms_Editorial extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
  public function init() {
                
        $this->setMethod('post');
        $this->setName('Editorial');
	$this->setOptions(array('escape' => true));
        	
       
               // $changeEditorial = "$.getJSON(urlAjax + 'exists/data/' + escape($('#Editorial').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
       
                
      $id   = new Zend_Form_Element_Hidden('id');
      $id->removeDecorator('label')
         ->removeDecorator('HtmlTag');
                
        
       $editorial = new Zend_Form_Element_Text('editorial');
       $editorial->setLabel('Editorial :')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array(2, 100))
            ->setAttrib('size', 60)
            ->setAttrib('maxlength', 100);
            //->setAttrib('onchange', $changeEditorial);
                
        
        
        

        $this->addElements(array($id,
                                 $editorial,
                                ));

    }

}

