<?php 
class Forms_Horario extends Zend_Form {
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

		$changeDia      = $SwapBytes_Jquery->fillSelect('fk_estructura', 'aula'   , array('periodo'  => 'selPeriodo',
			                                                                              'edificio' => 'fk_edificio',
			                                                                              'dia'      => 'fk_dia',
			                                                                              'horario'  => 'fk_horario'));
		$changeHorario  = $SwapBytes_Jquery->fillSelect('fk_estructura', 'aula'   , array('periodo'  => 'selPeriodo',
			                                                                              'edificio' => 'fk_edificio',
			                                                                              'dia'      => 'fk_dia',
			                                                                              'horario'  => 'fk_horario'));
		$changeEdificio = $SwapBytes_Jquery->fillSelect('fk_estructura', 'aula'   , array('periodo'  => 'selPeriodo',
			                                                                              'edificio' => 'fk_edificio',
			                                                                              'dia'      => 'fk_dia',
			                                                                              'horario'  => 'fk_horario'));
		$changeSemestre = $SwapBytes_Jquery->fillSelect('fk_asignatura', 'materia', array('semestre' => 'fk_semestre',
					                                                                      'pensum'   => 'selPensum'));

//		$changeDia      = addslashes($changeDia);
//		$changeHorario  = addslashes($changeHorario);
//		$changeEdificio = addslashes($changeEdificio);
//		$changeSemestre = addslashes($changeSemestre);

        $this->setMethod('post');
        $this->setName('horario');

        $id = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');
		
        $filtro = new Zend_Form_Element_Hidden('filters');
		$filtro->removeDecorator('label')
               ->removeDecorator('HtmlTag');

        $page = new Zend_Form_Element_Hidden('page');
		$page->removeDecorator('label')
             ->removeDecorator('HtmlTag');

		/*
		 * Campos ocultos de semestre y materia (asignatura), debido a que los
		 * campos normales se desabilitan para ser editados, se requiere de estos
		 * valores en dicha acción y no se puede presindir de ellos.
		 */
        $semestreID = new Zend_Form_Element_Hidden('semestre');
		$semestreID->removeDecorator('label')
                   ->removeDecorator('HtmlTag');

        $asignaturaID = new Zend_Form_Element_Hidden('asignatura');
		$asignaturaID->removeDecorator('label')
                     ->removeDecorator('HtmlTag');

		/*
		 * Campos del formulario.
		 */
        $dia = new Zend_Form_Element_Select('fk_dia');
        $dia->setLabel('Día:')
			->setAttrib('onchange', $changeDia)
            ->setAttrib('style', 'width: 150px');

        $horario = new Zend_Form_Element_Select('fk_horario');
        $horario->setLabel('Horario:')
				->setAttrib('onchange', $changeHorario)
                ->setAttrib('style', 'width: 150px');

        $semestre = new Zend_Form_Element_Select('fk_semestre');
        $semestre->setLabel('Semestre:')
				 ->setAttrib('onchange', $changeSemestre)
                 ->setAttrib('style', 'width: 150px');

        $materia = new Zend_Form_Element_Select('fk_asignatura');
        $materia->setLabel('Materia:')
                ->setAttrib('style', 'width: 150px');

        $seccion = new Zend_Form_Element_Select('fk_seccion');
        $seccion->setLabel('Sección:')
                ->setAttrib('style', 'width: 150px');

        $profesor = new Zend_Form_Element_Select('fk_usuariogrupo');
        $profesor->setLabel('Profesor:')
                 ->setAttrib('style', 'width: 150px');

        $edificio = new Zend_Form_Element_Select('fk_edificio');
        $edificio->setLabel('Edificio:')
				 ->setAttrib('onchange', $changeEdificio)
                 ->setAttrib('style', 'width: 150px');

        $aula = new Zend_Form_Element_Select('fk_estructura');
        $aula->setLabel('Aula:')
             ->setAttrib('style', 'width: 150px');

        $nota = new Zend_Form_Element_Text('nota');
        $nota->setLabel('Nota:')
             ->setRequired(false)
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addValidator('StringLength', true, array(1, 20))
             ->setAttrib('size', 16)
             ->setAttrib('maxlength', 20);

        $cupo = new Zend_Form_Element_Text('cupos');
        $cupo->setLabel('Cupos:')
			 ->setValue(45)
             ->setRequired(true)
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addValidator('StringLength', true, array(1, 3))
             ->addValidator('GreaterThan', true, array('min' => 0))
             ->setAttrib('size', 3)
             ->setAttrib('maxlength', 3);

        $cupo_max = new Zend_Form_Element_Text('cupos_max');
        $cupo_max->setLabel('Cupos Max.:')
			 ->setValue(45)
             ->setRequired(true)
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addValidator('StringLength', true, array(1,3))
             ->addValidator('GreaterThan', true, array('min' => 0))
             ->setAttrib('size', 3)
             ->setAttrib('maxlength', 3);
        $this->addElements(array($id,
			$filtro,
            $page,
			$semestreID,
			$asignaturaID,
            $dia,
            $horario,
            $semestre,
            $materia,
            $seccion,
            $profesor,
            $edificio,
            $aula,
			$nota,
         $cupo,
         $cupo_max
      ));
    }
}
