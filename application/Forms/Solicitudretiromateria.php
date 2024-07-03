<?php
class Forms_Solicitudretiromateria extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        $this->setMethod('post');
        $this->setName('Solicitud de Retiro de Materias');
		$this->setOptions(array('escape' => true));


                 $changeForm = "$.getJSON(urlAjax + 'modform/data/' + escape($('#fk_tipo-element').children().val()), function(data){executeCmdsFromJSON(data)});";

        $id   = new Zend_Form_Element_Hidden('pk_usuariogrupo');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');


        $helper = new Zend_Form_Element_Hidden('hidden-helper');
        $helper->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'DIV', 'id'=>'helper'))
                ));

        $tipo = new Zend_Form_Element_Select('fk_tipo');
        $tipo->setLabel('Tipo de Solicitud:')
                   ->setAttrib('style', 'width: 150px;')
                   ->setAttrib('onchange', $changeForm);

        $error_helper = new Zend_Form_Element_Hidden('hidden-error_helper');
        $error_helper->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'dd', 'id'=>'error_helper'))
                ));

        $this->addElements(array($id,$helper,$tipo,$error_helper
            ));
    }
}
