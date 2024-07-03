<?php
/**
 * User: Carlos Rivero Theoktisto 
 * Date: fecha
 * Time: hora 
 * @author kioskito
 **/ 

class Forms_Cuotasperiodo extends Zend_Form { 
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() { 
        $this-> setMethod('post'); 
        $this-> setName('cuotasperiodo');
        $this-> setAttrib('class','cuotasperiodo');

        $btnAgregar 	= "<span class=\"ui-button-text\">Agregar</span>";
        $btnVerificar	= "<span class=\"ui-button-text\">Verificar </span>";
        $btnModificar 	= "<span class=\"ui-button-text\">Modificar</span>";
        $btnReiniciar 	= "<span class=\"ui-button-text\">Reiniciar</span>";

                //FILTROS 
        $periodo = new Zend_Form_Element_Select('periodo');
        $periodo-> setLabel('Periodo:')
                -> setRequired(true)
                -> addValidator('NotEmpty')
                -> setAttrib('style', 'width: 180px')
                -> setAttrib('disable', false);


        $sede = new Zend_Form_Element_Select('sede');
        $sede-> setLabel('Sede:')
             -> setRequired(true)
             -> addValidator('NotEmpty')
             -> setAttrib('style', 'width: 150px')
             -> setAttrib('disable', false);

        $NuevoIngreso = new Zend_Form_Element_Select('NuevoIngreso');
        $NuevoIngreso -> setLabel('Nuevo Ingreso:')
                      -> setRequired(true)
                      -> addValidator('NotEmpty')
                      -> setAttrib('disable', false)
                      -> setAttrib('style', 'width: 150px');


                        //CAMPOS
        $montocuota = new Zend_Form_Element_Text('montocuota');
        $montocuota-> setLabel('Monto de Cuota:')
                   -> setAttrib('size', 20)
                   -> setAttrib('disable', false)
                   -> setAttrib('class', 'montocuota')
                   -> setValue(0)
                   -> setAttrib('disable', true);

        $montoinscri = new Zend_Form_Element_Text('montoinscri');
        $montoinscri-> setLabel('Monto de Inscripcion:')
                    -> setAttrib('size', 20)
                    -> setAttrib('disable', false)
                    -> setAttrib('class', 'montoinscri')
                    -> setValue(0)
                    -> setAttrib('disable', true);

        $montoinscriNew = new Zend_Form_Element_Text('montoinscriNew');
        $montoinscriNew-> setLabel('Monto Inscripcion Nuevo Ingreso:')
                    -> setAttrib('size', 20)
                    -> setAttrib('disable', false)
                    -> setAttrib('class', 'montoinscriNew')
                    -> setValue(0)
                    -> setAttrib('disable', true);   
                                     
        $montocuotaNew = new Zend_Form_Element_Text('montocuotaNew');
        $montocuotaNew-> setLabel('Monto Cuota Nuevo Ingreso:')
                    -> setAttrib('size', 20)
                    -> setAttrib('disable', false)
                    -> setAttrib('class', 'montocuotaNew')
                    -> setValue(0)
                    -> setAttrib('disable', true);

                    //BOTONES 
        $Verificar = new Zend_Form_Element_Button('Verificar');
        $Verificar-> setLabel($btnVerificar)
                  -> setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only btnVerificar')
                  -> setAttrib('role', 'button')
                  -> setAttrib('escape', false)
                  -> setAttrib('id', 'Verifi'); 

        $Agregar = new Zend_Form_Element_Button('Agregar');
        $Agregar-> setLabel($btnAgregar)
                -> setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only disabled')
                -> setAttrib('role', 'button')
                -> setAttrib('aria', 'disable')
                -> setAttrib('disable', true)
                -> setAttrib('escape', false);   

        $Modificar = new Zend_Form_Element_Button('Modificar');
        $Modificar-> setLabel($btnModificar)
                  -> setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only disabled')
                  -> setAttrib('role', 'button')
                  -> setAttrib('aria', 'disable')
                  -> setAttrib('disable', true)
                  -> setAttrib('escape', false); 
        
        $Reiniciar = new Zend_Form_Element_Button('Reiniciar');
        $Reiniciar-> setLabel($btnReiniciar)
                  -> setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only btnReiniciar')
                  -> setAttrib('role', 'button')
                  -> setAttrib('escape', false)
                  -> setAttrib('id', 'Reiniciar');    

          $this-> addElements(array(
            $periodo,
            $sede,
            $NuevoIngreso,
          	$Verificar,
            $montoinscri,
            $montoinscriNew,
            $montocuota,
            $montocuotaNew,            
            $Agregar,
            $Modificar,
            $Reiniciar
            ));
    }
}
