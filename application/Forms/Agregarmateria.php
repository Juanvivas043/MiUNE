<?php
class Forms_Agregarmateria extends Zend_Form {
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

		$changeMateria = $SwapBytes_Jquery->fillSelect('fk_seccion', 'seccion', array('semestre' => 'fk_semestre'
            ,'pensum'   => 'Pensum'
            ,'materia'   => 'fk_asignatura'
            ,'periodo'   => 'Periodo'
            ,'sede'   => 'Sede'
            ,'cedula' => 'pk_usuario'
        ));

		$changeSemestre = $SwapBytes_Jquery->fillSelectRecursive('fk_asignatura', 'materia', array('semestre' => 'fk_semestre',
            'pensum'   => 'Pensum')
            , $changeMateria);

		$changeCI = "$.getJSON(urlAjax + 'usuario/cedula/' + escape($('#pk_usuario').val()) + '/periodo/' + escape($('#Periodo').val()) + '/escuela/' + escape($('#Escuela').val()), function(data){executeCmdsFromJSON(data)});";

        $this->setMethod('post');
        $this->setName('materias');

        $ci = new Zend_Form_Element_Text('pk_usuario');
        $ci->setLabel('C.I.:')
            ->setAttrib('size', 8)
            ->setAttrib('onchange', $changeCI.$changeSemestre)
            ->setAttrib('maxlength', 8);

        $nombre = new Zend_Form_Element_Text('nombre');
        $nombre->setLabel('Nombre:')
            ->setAttrib('size', 45)
            ->setAttrib('maxlength', 45)
            ->setAttrib('disable', 'disable')
            ->setAttrib('id', 'nombre');

        $apellido = new Zend_Form_Element_Text('apellido');
        $apellido->setLabel('Apellido:')
            ->setAttrib('size', 45)
            ->setAttrib('disable', 'disable')
            ->setAttrib('maxlength', 45);

        $id = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $pago = new Zend_Form_Element_Hidden('pago');
		$pago->removeDecorator('label')
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

        $semestre = new Zend_Form_Element_Select('fk_semestre');
        $semestre->setLabel('Semestre:')
				 ->setAttrib('onchange', $changeSemestre)
                 ->setAttrib('style', 'width: 150px');

        $materia = new Zend_Form_Element_Select('fk_asignatura');
        $materia->setLabel('Materia:')
				->setAttrib('onchange', $changeMateria)
                ->setAttrib('style', 'width: 250px');

        $seccion = new Zend_Form_Element_Select('fk_seccion');
        $seccion->setLabel('Sección:')
                ->setAttrib('style', 'width: 250px');

        $calificacion = new Zend_Form_Element_Text('calificacion');
        $calificacion->setLabel('Calificación:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('Digits')
            ->addValidator('Between', true, array(0, 20))
            ->addValidator('StringLength', true, array(1, 2))
            ->setAttrib('size', 2)
            ->setAttrib('maxlength', 2);

        $estado = new Zend_Form_Element_Select('estado');
        $estado->setLabel('Estado:')
            ->setRequired(true)
            ->setAttrib('style', 'width: 150px');

        $this->addElements(array($id,
            $pago,
            $ci,
            $nombre,
            $apellido,
			$filtro,
            $page,
			$semestreID,
			$asignaturaID,
            $semestre,
            $materia,
            $seccion,
            $estado,
            $calificacion,
      ));
    }
}
