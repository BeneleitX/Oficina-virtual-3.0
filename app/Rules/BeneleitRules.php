<?php

namespace App\Rules; 

class BeneleitRules
{
    public function curp( $value ): bool
    {
        if(strlen( $value ) != 18)
            return false;

        $pattern = "/^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/";
        $validate = preg_match($pattern, strtoupper( $value ) );
        return $validate;
    }

    public function curp_existe( $value ): bool
    {
        $db = db_connect();
        return !( $db->query("select * from t_usuarios where curp = '".strtoupper( $value )."' and substring(estatus_codigo, 1, 3) > 200" )->getNumRows() > 0 );
    }

    public function correo_existe( $value ): bool
    {
        $db = db_connect();
        return !( $db->query("select * from t_usuarios where correo = '".strtoupper( $value )."' and substring(estatus_codigo, 1, 3) > 200" )->getNumRows() > 0 );
    }

    public function celular_existe( $value ): bool
    {
        $db = db_connect();
        return !( $db->query("select * from t_usuarios where telefono = '".strtoupper( $value )."' and substring(estatus_codigo, 1, 3) > 200" )->getNumRows() > 0 );
    }

    public function patrocinador_activo( $value ): bool
    {
        $db = db_connect();
        return $db->query("select * from t_usuarios where id = '".strtoupper( $value )."' and substring(estatus_codigo, 1, 3) > 200" )->getNumRows() > 0;
    }
}
