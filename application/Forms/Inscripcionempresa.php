<?php 
class Forms_Inscripcionempresa extends Zend_Form {
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
        $changeTutorempresa = $SwapBytes_Jquery->fillSelect('fk_tutor_institucion', 'tutor', array('empresa' => 'fk_institucion'));

        $this->setMethod('post');
        $this->setName('inscripcionempresa');

        $id = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
                   ->removeDecorator('HtmlTag');
		
        $filtro = new Zend_Form_Element_Hidden('filters');
		$filtro->removeDecorator('label')
                       ->removeDecorator('HtmlTag');

        $page = new Zend_Form_Element_Hidden('page');
        $page->removeDecorator('label')
             ->removeDecorator('HtmlTag');
  
//		 * Campos del formulario.
        
        $estudiante = new Zend_Form_Element_Select('fk_recordacademico');
        $estudiante->setLabel('Estudiantes:')
                   ->setAttrib('style', 'width: 400px');

        $empresa = new Zend_Form_Element_Select('fk_institucion');
        $empresa->setLabel('Empresas:')
                 ->setAttrib('onchange', $changeTutorempresa) 
                 ->setAttrib('style', 'width: 400px');
      
        $tutoracademico = new Zend_Form_Element_Select('fk_tutor_academico');
        $tutoracademico->setLabel('Tutores Academicos:')
                       ->setAttrib('style', 'width: 300px');

        $tutorinstitucion = new Zend_Form_Element_Select('fk_tutor_institucion');
        $tutorinstitucion->setLabel('Tutores Empresariales:')
                         ->setAttrib('style', 'width: 300px');

        $this->addElements(array($id,
                                 $filtro,
                                 $page,
                                 $estudiante,
                                 $empresa,
                                 $tutorinstitucion,
                                 $tutoracademico));
    }
}
