<?php 

class Forms_Tipohorario extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        
        $SwapBytes_Jquery = new SwapBytes_Jquery();
                
        $this->setMethod('post');
        $this->setName('tipohorario');
	$this->setOptions(array('escape' => true));
        	
        $id   = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
                   ->removeDecorator('HtmlTag');
                
        $atributotipoID = new Zend_Form_Element_Hidden('fk_atributotipo');
	$atributotipoID->removeDecorator('label')
                       ->removeDecorator('HtmlTag');       

        $atributotipo = new Zend_Form_Element_Select('fk_atributotipo');
        $atributotipo->setLabel('Tipo:')
                        ->setAttrib('style', 'width: 300px');        
                
        $horario = new Zend_Form_Element_Text('valor');
        $horario->setLabel('Horario:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
 

        $this->addElements(array($id,
                                 $atributotipoID,
                                 $atributotipo,
                                 $horario));

    }

}