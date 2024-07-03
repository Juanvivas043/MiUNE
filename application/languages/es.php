<?php
return array(
    Zend_Validate_Alpha::NOT_ALPHA                 => "'%value%' no contiene caracteres alfabeticos.",
    Zend_Validate_Between::NOT_BETWEEN             => "'%value%' no es un valor entre '%min%' y '%max%'.",
    Zend_Validate_Date::INVALID                    => "Inválido el tipo de dato, debe ser una cadena o un entero.",
    Zend_Validate_Date::FALSEFORMAT                => "'%value%' no corresponde al formato YYYY-MM-DD.",
    Zend_Validate_Date::INVALID_DATE               => "'%value%' no es una fecha valida.",
    Zend_Validate_Date::FALSEFORMAT                => "'%value%' no se ajusta al formato de la fecha.",
    Zend_Validate_Digits::STRING_EMPTY             => "'%value%' se encuentra vacío.",
    Zend_Validate_Digits::NOT_DIGITS               => "El campo solo debe contener dígitos, no '%value%'.",
    Zend_Validate_StringLength::TOO_LONG           => 'El campo debe contener un máximo de %max% caracteres.',
    Zend_Validate_StringLength::TOO_SHORT          => 'El campo debe contener por lo menos %min% caracteres.',
    Zend_Validate_EmailAddress::INVALID            => 'La dirección de correo no es válida.',
    Zend_Validate_EmailAddress::QUOTED_STRING      => "'%localPart%' no concuerda con el formato de comillas.",
    Zend_Validate_EmailAddress::DOT_ATOM           => "'%localPart%' no concuerda con el formato de punto.",
    Zend_Validate_EmailAddress::INVALID_HOSTNAME   => "'%hostname%' no es un nombre de dominio válido.",
    Zend_Validate_EmailAddress::INVALID_LOCAL_PART => "'%localPart%' no es una parte local válida.",
    Zend_Validate_EmailAddress::INVALID_MX_RECORD  => "'%hostname%' no tiene un dominio de correo asignado.",
    Zend_Validate_NotEmpty::IS_EMPTY               => 'El campo no puede estar vacío.',
	Zend_Validate_GreaterThan::NOT_GREATER         => "'%value%' no es un valor mayor a '%min%'.",
	Zend_Validate_Identical::NOT_SAME              => "Los dos elementos dados no coinciden.",
    Zend_Validate_CedulaMatch::NOT_MATCH           => "La cedula del estudiante no existe."

);