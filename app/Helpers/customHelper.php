<?php

function DBDataTypeForm($dbType, $dbDriver = 'sqlsrv') {
        $formType = [
                'sqlsrv'   => ['bigint'            => 'number',
                                'binary'            => 'number',
                                'bit'               => 'checkbox',
                                'char'              => 'text',
                                'date'              => 'date',
                                'datetime'          => 'datetime-local',
                                'datetime2'         => 'datetime-local',
                                'datetimeoffset'    => 'datetime-local',
                                'decimal'           => 'number',
                                'float'             => 'number',
                                'geography'         => 'text',
                                'geometry'          => 'text',
                                'hierarchyid'       => 'text',
                                'image'             => 'file',
                                'int'               => 'number',
                                'money'             => 'number',
                                'nvarchar'          => 'text',
                                'real'              => 'number',
                                'smalldatetime'     => 'datetime-local',
                                'smallint'          => 'number',
                                'smallmoney'        => 'number',
                                'sql_variant'       => 'text',
                                'sysname'           => 'text',
                                'text'              => 'area',
                                'time'              => 'time',
                                'timestamp'         => 'time',
                                'tinyint'           => 'number',
                                'uniqueidentifier'  => 'number',
                                'varbinary'         => 'number',
                                'varchar'           => 'text',
                                'xml'               => 'area'
                        ],
                'mysql' => []
                ];
        
        return ($formType[$dbDriver][$dbType] ?? 'text');

}

function stringMask($string, $mask) {
        $maskared = '';
        $k = 0;

        for($i = 0; $i <= strlen($mask)-1; $i++) {
                if($mask[$i] == '#') {
                         if(isset($string[$k]))    $maskared .= $string[$k++];
                }
                else {
                        if(isset($mask[$i]))    $maskared .= $mask[$i];
                }
        }
        
        return $maskared;
}
