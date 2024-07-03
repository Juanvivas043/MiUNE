<?php
/*
 * User: Enrique Reyes
 * Date: 13/09/16
 * Time: 04:51 PM
 */ 
class Forms_Registrarpago extends Zend_Form { 
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() { 
        $this->setMethod('post'); 
        $this->setName('registrodepago');
        $this->setAttrib('class','registrodepago');

        $btnAgregar 	= "<span class=\"ui-button-text\">Agregar</span>";
        $btnVerificar	= "<span class=\"ui-button-text\">Verificar estudiante</span>";
        $btnModificar 	= "<span class=\"ui-button-text\">Modificar</span>";
        $btnEliminar 	= "<span class=\"ui-button-text\">Eliminar</span>";
        
        $periodo = new Zend_Form_Element_Select('periodo');
        $periodo->setLabel('Periodo:')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->setAttrib('style', 'width: 180px');

        $ci = new Zend_Form_Element_Text('pk_usuario');
        $ci->setLabel('C.I.:')
            ->setAttrib('size', 8)
            ->setAttrib('maxlength', 8)
            ->setAttrib('id','cedula');

        $Verificar = new Zend_Form_Element_Button('Verificar');
        $Verificar->setLabel($btnVerificar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only btnVerificar')
                ->setAttrib('role', 'button')
                ->setAttrib('escape', false)
                ->setAttrib('id', 'Verifi'); 

        $nombre = new Zend_Form_Element_Text('nombre');
        $nombre->setLabel('Nombre del estudiante:')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 45)
            ->setAttrib('id', 'nombreEstudiante')
            ->setAttrib('disable', true);

        $numPago = new Zend_Form_Element_Text('numPago');
        $numPago->setLabel('Numero de pago:')
            ->setAttrib('size', 20)
            ->setAttrib('disable', false)
            ->setAttrib('class', 'numPago')
            ->setValue(0)
            ->setAttrib('disable', true);

        $UCA = new Zend_Form_Element_Text('UCA');
        $UCA->setLabel('Creditos adicionales:')
            ->setAttrib('size', 10)
            ->setAttrib('maxlength', 2)
            ->setAttrib('class', 'UCA')
            ->setValue(0)
            ->setAttrib('disable', true);

        $sede = new Zend_Form_Element_Select('sede');
        $sede->setLabel('Sede:')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->setAttrib('style', 'width: 150px')
             ->setAttrib('disable', true);  

        $escuela = new Zend_Form_Element_Select('escuela');
        $escuela->setLabel('Escuela:')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->setAttrib('style', 'width: 150px')
             ->setAttrib('disable', true);    

        $pensum = new Zend_Form_Element_Select('pensum');
        $pensum->setLabel('Pensum:')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->setAttrib('style', 'width: 150px')
             ->setAttrib('disable', true); 

        $Agregar = new Zend_Form_Element_Button('Agregar');
        $Agregar->setLabel($btnAgregar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only disabled')
                ->setAttrib('role', 'button')
                ->setAttrib('aria', 'disable')
                ->setAttrib('disable', true)
                ->setAttrib('escape', false);   

        $Modificar = new Zend_Form_Element_Button('Modificar');
        $Modificar->setLabel($btnModificar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only disabled')
                ->setAttrib('role', 'button')
                ->setAttrib('aria', 'disable')
                ->setAttrib('disable', true)
                ->setAttrib('escape', false);   

        $Eliminar = new Zend_Form_Element_Button('Eliminar');
        $Eliminar->setLabel($btnEliminar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only disabled')
                ->setAttrib('role', 'button')
                ->setAttrib('aria', 'disable')
                ->setAttrib('disable', true)
                ->setAttrib('escape', false);   
           
          $this->addElements(array($periodo,
          	$ci,
          	$Verificar,
            $nombre,
            $numPago,
            $UCA,
            $periodo,
            $sede,
            $escuela,
            $pensum,
            $Agregar,
            $Modificar,
            $Eliminar
            ));
    }
}
