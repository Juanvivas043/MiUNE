<?php

/**
 * Created by PhpStorm.
 * User: andreas
 * Date: 31/03/14
 * Time: 11:08 AM
 */
class Models_DbView_actualizacionvnzla extends Zend_Db_Table
{

    public function init()
    {
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
        // $this->logger = Zend_Registry::get('logger');
    }

    public function setSearch($searchData)
    {
        $this->searchData = $searchData;
    }

    public function updateUser(array $datos)
    {

        if($datos['segundo_apellido'] == null)
        {
            $datos['segundo_apellido'] = " ";
        }

        $sql = "UPDATE tbl_tmp_banco_vzla
                SET nombre_completo = '{$datos['nombre']} {$datos['primer_apellido']} {$datos['segundo_apellido']}',
                nombres = '{$datos['nombre']}',
                primer_apellido = '{$datos['primer_apellido']}',
                segundo_apellido = '{$datos['segundo_apellido']}',
                fecha_nacimiento = '{$datos['fechanacimiento']}',
                correo =  '{$datos['correo']}',
                codigo_area = '{$datos['codigo_telefono']}',
                telefono = '{$datos['telefono']}',
                telefono_movil = '{$datos['telefono_movil']}',
                ocupacion = '{$datos['tipo_ocupacion']}',
                tipo_via = '{$datos['tipo_via']}',
                descripcion_via = '{$datos['descripcion_via']}',
                tipo_vivienda = '{$datos['tipo_vivienda']}',
                nombre_vivienda = '{$datos['nombre_vivienda']}',
                punto_referencia_vivienda = '{$datos['punto_referencia']}',
                tipo_nucleo = '{$datos['tipo_nucleo']}',
                descripcion_nucleo = '{$datos['descripcion_nucleo']}',
                estado_vivienda = '{$datos['estado']}',
                cuidad_vivienda = '{$datos['ciudad']}',
                municipio_vivienda = '{$datos['municipio']}',
                zona_postal_vivienda = '{$datos['zona_postal']}',
                codigo_area_fax = '{$datos['codigo_fax']}',
                fax = '{$datos['numero_fax']}',
                cargo_empresa = '{$datos['cargo_empresa']}',
                actividad_economica =  '{$datos['actividad_economica']}',
                tipo_empleado = '{$datos['tipo_empleado']}',
                codigo_area_oficina = '{$datos['codigo_area_telf_oficina']}',
                telefono_oficina = '{$datos['numero_oficina']}',
                codigo_oficina = '{$datos['codigo_oficina_banco']}',
                actualizado = true
                where pk_usuario = {$datos['cedula']}";

        $result = $this->_db->query($sql);

        return $result;

    }

    public function getOtherInfo($id)
    {
        $sql = "SELECT pk_usuario, nombres, primer_apellido, segundo_apellido, to_char(fecha_nacimiento, 'DD/MM/YYYY') as fecha_nacimiento, correo, codigo_area, telefono, telefono_movil,
        ocupacion, tipo_via, descripcion_via, tipo_vivienda, nombre_vivienda,
        punto_referencia_vivienda, tipo_nucleo, descripcion_nucleo, estado_vivienda,
        cuidad_vivienda, municipio_vivienda, zona_postal_vivienda, codigo_area_fax, tipo_empleado,
        fax, cargo_empresa, actividad_economica, codigo_area_oficina, telefono_oficina,
        codigo_oficina, actualizado
        FROM tbl_tmp_banco_vzla
        WHERE pk_usuario = {$id}";

        $result = $this->_db->query($sql);

        return $result->fetchAll();
    }

    public function getActualizado($id)
    {
        $sql = "SELECT actualizado
        FROM tbl_tmp_banco_vzla
        WHERE pk_usuario = {$id}";

        $result = $this->_db->query($sql);

        return $result->fetchAll();
    }

}

