<?php
    /**
     *  G.E.D. = Generate Enormous Data
     *      Not GOD, it's GED. A php script for creating MSSQL data using classic INSERT statement or STORED PROCEDURES.
     *      Supply some data and GOD, GED! will create them for you! ;) 
     */
    
    // CONFIG hehe
    
    // type of query to generate
    $method = 'stored_procedure'; // classic_insert, stored_procedure
    $procedure_name = 'spInitialEmployees';
    DEFINE('STORED_PROCEDURE', $procedure_name);
    // database MSSQL, MYSQL, POSTGRESQL
    # TODO: evaluate escaper depending on DB AND supply connection string depending on DB
    $database   = 'MSSQL';
    // your database table_name
    $table_name = 'acct_client_tbl';
    // columns on your table
    $columns    = ['@first_name', '@last_name', '@gender', '@birthdate', 
        '@email', '@mobile', '@address', '@password', '@user_type_id', '@created_by'];
    // added columns that doesn't exists on your raw file/data eg. created_by, default_password values
    $added_values = ['password', 2, 1];
    // raw file to format
    $raw_sins_file_name = 'raw_employees.txt';
    // raw data delimiter
    $delimiter = ','; // ||
    // output filename
    $god_creations_file_name = 'new_employees.sql';


    // calculating calculation to be calculated
    $column_count = count($columns) - count($added_values);
    // break data into array
    $raw_data_contents = file_get_contents($raw_sins_file_name);
    $raw_data_contents = explode($delimiter, $raw_data_contents);
    // validate and sanitize each data
    $sanitize_data = [];
    foreach($raw_data_contents as $item){
        # TODO: build a function for this sht
        $item = trim(str_replace("'","''", $item));
        array_push($sanitize_data, $item);
    }
    
    // evaluate method to use
    $grouped_data = $method == 'classic_insert' ? 'Under development'
        :$method == 'stored_procedure' ? storedProcedure($sanitize_data, $columns, $column_count, $added_values)
        :'Method is invalid';

    $final_queries = bindValues($columns, $grouped_data);

    function bindValues($columns, $sets){
        $query = '';
        $i = 0;
        foreach($sets as $set){
            foreach($set as $item){
                if($i == 0){
                    $query .= 'EXEC '.STORED_PROCEDURE.' ';
                }
                
                if($i == count($columns)-1){
                    $query .= $columns[$i]." = '".$item."';\n";
                    $i = 0;
                }else{
                    $query .= $columns[$i]." = '".$item."', ";
                    $i += 1;
                }
            }
        }
        
        return $query;
    }

    function storedProcedure($array = [], $columns, $column_count, $added_values){

        $array_container = [];
        $row_array = [];
        $set = 1;
        
        foreach($array as $item){
            // group each item
            array_push($row_array, $item);
            if($set % $column_count == 0){
                // combine with added values
                $merged = array_merge($row_array, $added_values);
                // put each group on container
                array_push($array_container, $merged);
                // reset values
                $row_array = [];
                $set = 1;
            }else{
                $set += 1;
            }
        }
        return $array_container;
    }

    // save into file
    file_put_contents($god_creations_file_name, $final_queries);

?>