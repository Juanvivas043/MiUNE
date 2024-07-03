<?php
class Forms_Retirarmaterias extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        $this->setMethod('post');
        $this->setName('retirar_materias');
		$this->setOptions(array('escape' => true));


                $displaymateria = "$.getJSON(urlAjax + 'exists/data/' + escape($('#retirar_materias').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";

        $id   = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $fkugs = new Zend_Form_Element_Hidden('fk_usuariogruposolicitud');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $helper = new Zend_Form_Element_Hidden('hidden-helper');
        $helper->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'DIV', 'id'=>'helper'))
                ));

        $materia = new Zend_Form_Element_Select('pk_record');
        $materia->setLabel('Materia:')
                   ->setAttrib('style', 'width: 200px;')
                   ->setAttrib('onchange', $displaymateria)
                   ->setAttrib('onfocus', $displaymateria);

        $error_helper = new Zend_Form_Element_Hidden('hidden-error_helper');
        $error_helper->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'dd', 'id'=>'error_helper'))
                ));

        $this->addElements(array($id,$helper,$materia,$error_helper
            ));
    }
}
