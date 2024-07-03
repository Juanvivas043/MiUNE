<?php 
class Forms_Proyecto extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        $SwapBytes_Jquery = new SwapBytes_Jquery();
        
        $this->setMethod('post');
        $this->setName('proyecto');
	$this->setOptions(array('escape' => true));
        

        $id   = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
                   ->removeDecorator('HtmlTag');

        $institucion = new Zend_Form_Element_Select('fk_institucion');
        $institucion->setLabel('Institucion:')
                    ->setAttrib('style', 'width: 200px');

        $nombre = new Zend_Form_Element_Text('nombre');
        $nombre->setLabel('Nombre:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 50);

        $descripcion = new Zend_Form_Element_Textarea('descripcion');
        $descripcion->setLabel('Descripcion:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('rows', 4)
            ->setAttrib('cols', 39);
            
       
        $this->addElements(array($id,
                                 $institucion,                     
                                 $nombre,
                                 $descripcion));

    }

}