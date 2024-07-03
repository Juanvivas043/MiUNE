<?php 
class Forms_Preinscripcionproyecto extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
		/*
		 * Asignamos los eventos a los SELECT para que llene a otros SELECT en
		 * forma de cascada.
		 */
		$SwapBytes_Jquery = new SwapBytes_Jquery();

//                $changeTutorinstitucion = $SwapBytes_Jquery->fillSelect('fk_tutor_institucion', 'tutor', array('proyecto' => 'fk_asignacionproyecto'));

        $this->setMethod('get');
        $this->setName('preinscripcionproyecto');

        $id = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
                   ->removeDecorator('HtmlTag');
		  
          
//		 * Campos del formulario.
        
        $proyecto = new Zend_Form_Element_Select('pk_asignacionproyecto');
        $proyecto->setLabel('Desea registrar la pre-inscripción del proyecto:')
                   ->setAttrib('style', 'width: 400px');

        $this->addElements(array($id,
                                 $proyecto));
    }
}
