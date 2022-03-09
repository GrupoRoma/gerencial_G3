<?php

namespace App\Models\Utils;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Utilitarios extends Model
{
    use HasFactory;

    /**
     *  carrega os dados da tabela de parâmetros do gerencial 2.0
     */
    public function getParametrosG2() {

        return DB::select("SELECT * FROM GAMA..SGA_PAR_GRUPO WHERE mes_ano = '08/2021' AND COD_SEGM = '5'");
    }

    /**
     *  Limpa a tabela de dados informada
     * 
     *  @param  string  tablename
     * 
     */
    public function clearTable($tableName) {
        return DB::table('gerencial'.$tableName)->truncate();
    }

    /**
     *  Inclui o registro na tabela de G3_gerencialParametroRateio
     *  
     *  @param  array   ['columnName'   => 'insertValue', ...]
     * 
     *  @return boolean 
     */
    public function saveParametro($data) {
        // Prepara a string sql
        $columns    = '';
        $valuesInt  = '';
        $valuesPar  = [];
        foreach ($data as $columnName => $insertValue) {
            $columns    .= $columnName.',';
            $valuesInt  .= '?,';
            $valuesPar[] = $insertValue;
        }

        return DB::insert('insert into GAMA..G3_gerencialParametroRateio ('.substr($columns,0,-1).') 
                                    values ('.substr($valuesInt,0,-1).')', $valuesPar);
    }


    public function validateMessage($errorsFound, $customMessages) {

        if (isset($customMessages) && !empty($customMessages)) {
            foreach ($errorsFound as $column => $errors) {
                $validate[]   =  $customMessages[$column];
            }
        }
        else $validate[]    = 'Ocorreu um erro desconhecido, favor entrar em contato com o administrador do sistema';

        return $validate;
    }

    /**
     *  Identifica todas as opções cadastradas para o usuário logado
     *  Se o usuário for do tipo OPE (Operador), ele terá acesso a todas as opções
     *  Se o usuário for do tipo GST (Gestor), ele terá acesso apenas às opções cadastradas no ROMA APPS
     * 
     *  APP 14 =  Gerencial
     * 
     *  @param  session     userID
     *  
     *  @return Array       menu struct
     * 
     */
    public function getMenuOptions()
    {
        $profileRoutes  = '';
        $userRoutes     = '';
        $whereUser      = '';
        if (session('_GER_tipoUsuarioGerencial') != 'OPE') {
            $profileRoutes  = " JOIN	GAMA..GRA_userApps			(nolock) ON GRA_userApps.idApp			= GRA_appsRoutes.idApp
                                JOIN	GAMA..GRA_userProfiles		(nolock) ON GRA_userProfiles.idUser		= GRA_userApps.idUser
                                JOIN	GAMA..GRA_profileRoutes		(nolock) ON GRA_profileRoutes.idRoute	= GRA_appsRoutes.id";

            $userRoutes     = " JOIN	GAMA..GRA_userApps			(nolock) ON GRA_userApps.idApp			= GRA_appsRoutes.idApp
                                JOIN	GAMA..GRA_userRoutes		(nolock) ON GRA_userRoutes.idUser		= GRA_userApps.idUser";

            $whereUser      = "AND		GRA_userApps.idUser			= ".session('userID');
        }
        
        $dbData     = DB::select("  -- PROFILES
                                    SELECT GRA_appsRoutes.*
                                    FROM gama..GRA_appsRoutes			(nolock)

                                    ".$profileRoutes."

                                    WHERE	GRA_appsRoutes.idApp		= 14
                                    AND		GRA_appsRoutes.routeActive	= 1
                                    
                                    ".$whereUser."
                                    
                                    UNION 
                                    
                                    -- USER ROUTES
                                    SELECT GRA_appsRoutes.*
                                    FROM gama..GRA_appsRoutes			(nolock)
                                    
                                    ".$userRoutes."

                                    WHERE	GRA_appsRoutes.idApp		= 14
                                    AND		GRA_appsRoutes.routeActive	= 1
                                    ".$whereUser."
                                
                                    ORDER BY GRA_appsRoutes.routeGroup, GRA_appsRoutes.routeLabel");

        /* $dbData = DB::select("  SELECT * 
                                FROM gama..GRA_appsRoutes		(nolock)
                                ".$restrictUser."
                                WHERE	GRA_appsRoutes.idApp		= 14
                                AND		GRA_appsRoutes.routeActive	= 1
                                ORDER BY GRA_appsRoutes.routeGroup, GRA_appsRoutes.routeLabel"); */
        
        $menuData   = [];
        foreach ($dbData as $row => $data) {
            $menuData[$data->routeGroup][$data->routeLabel] = [ 'name'          => $data->routeName,
                                                                'description'   => $data->routeDescription,
                                                                'controller'    => $data->routeController,
                                                                'class'         => $data->routeClass,
                                                                'targetType'    => $data->routeTarget,
                                                                'targetElement' => $data->routeTargetId,
                                                                'param'         => $data->routeParam];
        }

        return $menuData;
    }
}
