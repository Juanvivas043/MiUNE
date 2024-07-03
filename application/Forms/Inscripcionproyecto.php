<?php 
class Forms_Inscripcionproyecto extends Zend_Form {
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

        $this->setMethod('post');
        $this->setName('inscripcionproyecto');

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

//        $proyecto = new Zend_Form_Element_Select('fk_asignacionproyecto');
//        $proyecto->setLabel('Proyectos:')
//                 ->setAttrib('onchange', $changeTutorinstitucion) 
//                 ->setAttrib('style', 'width: 500px');
      
        $tutoracademico = new Zend_Form_Element_Select('fk_tutor_academico');
        $tutoracademico->setLabel('Tutores Academicos:')
                       ->setAttrib('style', 'width: 300px');

        $tutorinstitucion = new Zend_Form_Element_Select('fk_tutor_institucion');
        $tutorinstitucion->setLabel('Tutores Instituciones:')
                         ->setAttrib('style', 'width: 300px');

        $this->addElements(array($id,
                                 $filtro,
                                 $page,
                                 $estudiante,
//                                 $proyecto,
                                 $tutorinstitucion,
                                 $tutoracademico));
    }
}
