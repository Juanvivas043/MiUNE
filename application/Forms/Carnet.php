<?php 
class Forms_Carnet extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        $this->setMethod('post');
        $this->setName('estudiante');
		$this->setOptions(array('escape' => true));
		
		$changeCI = "$.getJSON(urlAjax + 'exists/data/' + escape($('#estudiante').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
//		$changeCI = addslashes($changeCI);


        $id   = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');
        //$page = new Zend_Form_Element_Hidden('page');

		$foto = new Zend_Form_Element_Image('foto');
		$foto->setLabel('Foto:')
		     ->setAttrib('width', 120)
		     ->setAttrib('height', 161)
			 ->setAttrib('src', '../images/empty_profile.jpg')
			 ->setAttrib('disabled', 'disabled');

        $ci = new Zend_Form_Element_Text('pk_usuario');
        $ci->setLabel('C.I.:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('Digits')
            ->addValidator('StringLength', true, array(4, 8))
            ->setAttrib('size', 9)
            ->setAttrib('maxlength', 8)
            ->setAttrib('onchange', $changeCI);
        
        $fk_emision = new Zend_Form_Element_Select('fk_emision');
        $fk_emision->setLabel('Emisiones:')
                ->setAttrib('style', 'width: 150px');
        
        $fk_autorizacion = new Zend_Form_Element_Select('fk_autorizacion');
        $fk_autorizacion->setLabel('Autorización:')
                ->setAttrib('style', 'width: 150px');
        
        $fk_afinidad = new Zend_Form_Element_Select('fk_afinidad');
        $fk_afinidad->setLabel('Afinidad:')
                ->setAttrib('style', 'width: 150px');
        

        $this->addElements(array($id,
                                $foto,
                                $ci,
                                $fk_emision,
                                $fk_autorizacion,
                                $fk_afinidad
                                ));
    }
}