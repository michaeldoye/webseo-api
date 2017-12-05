<?php
//error_reporting(E_ALL);
//echo dirToArray( 'testing/');
$directory = '/home/websexyo/public_html/api/clients/';
$limit = 1;
$offset = 0;
$wait = 2;
$release_count = count( countDir( $directory) );
do {
    if ($offset != 0) sleep($wait);
    try {
        //echo $count;
        $arr = dirToArray( $directory, $limit, $offset );
        if( is_array( $arr ) ) {
            $url = array_keys( $arr );
            $url = str_replace( '.', '_', $arr[0]['sitename'] );
            //echo '<pre>';
            //echo $offset.'<br>';
            if( null !== $url[0] || $url[0] === '' || empty($url) || ! isset($url) || empty($arr) || !isset($arr) || is_array($arr) ) {
                //echo $url[0];
                doDbImport( $arr[0]['values'], $url  );
                //var_dump( $arr[0]['values'] );
            } 
            //echo '</pre>';
        }
    } catch ( Exception $e ) {
        print $e->getMessage();
    }
    $offset += $limit;
} while ( $offset <= $release_count+1 );


function dirToArray( $dir, $limit, $offset ) { 

    $result = array(); 
    $cdir   = scandir( $dir ); 

    foreach ( array_slice( $cdir, $offset, $limit ) as $key => $value ) {

        $file_info = pathinfo( $value );

        if ( ! in_array( $value,array( ".", ".." ) ) ) { 
            if ( is_dir( $dir . DIRECTORY_SEPARATOR . $value ) ) { 
                //echo $value.'<br>';
                $result[$key]['values'] = dirToArray2( $directory.$value.'/', 'undefined', 'undefined' );//$value; 
                $result[$key]['sitename'] = $value;
            } 
        } 
    }
    return empty( $result ) ? '' : $result;
}

function countDir( $dir ) { 
    
    $result = array(); 
    $cdir   = scandir( $dir ); 

    foreach ( $cdir as $key => $value ) {

        $file_info = pathinfo( $value );

        if ( ! in_array( $value,array( ".", ".." ) ) ) { 
            if ( is_dir( $dir . DIRECTORY_SEPARATOR . $value ) ) { 
                $result[] = $value; 
            } 
        } 
    }
    return $result;
}

// dirToArray
function dirToArray2( $dir, $se, $cat ) { 

    $directory = '/home/websexyo/public_html/api/clients/';
    // echo $directory.$dir;
    // exit();

    $result = array(); 
    $cdir   = scandir( $directory.$dir ); 
    $arr1   = array();

    foreach ( $cdir as $key => $value ) {

        $file_info = pathinfo( $value );

        if ( ! in_array( $value,array( ".", ".." ) ) ) { 
            if ( is_dir( $dir . DIRECTORY_SEPARATOR . $value ) ) { 

                $result[ $value ] = dirToArray2( $directory . $value.'/', 'undefined', 'undefined' ); 

            } 
            else { 

                if ( $file_info['extension'] == 'xml' ) {
                    if ( isset( $se ) && $se !== 'undefined' ) {

                        if ( strpos( $value, $se ) !== false ) {

                            $xmlstring     = file_get_contents( $directory.$dir.'/'.$value );
                            $temp          = preg_replace( '/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $xmlstring );
                            $xml           = simplexml_load_string( $temp );
                            $result['xml'] = xmlToArray( $xml );
                        } 

                    } 
                    elseif ( isset( $cat ) && $cat !== 'undefined' ) {

                        if ( strpos( $value, $cat ) !== false ) {

                            $xmlstring     = file_get_contents( $directory.$dir.'/'.$value );
                            $temp          = preg_replace( '/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $xmlstring );
                            $xml           = simplexml_load_string( $temp );
                            $result['xml'] = xmlToArray( $xml );

                        }

                    } 
                    else {
                        if ( strpos( $value, '_' ) !== false ) {

                            $xmlstring     = file_get_contents( $directory.$dir.'/'.$value );
                            $temp          = preg_replace( '/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $xmlstring );
                            $xml           = simplexml_load_string( $temp );
                            $result['xml'] = xmlToArray( $xml );

                        }
                    }
                } 

                if ( $file_info['extension'] == 'txt' ) {

                    $file  = fopen( $directory.$dir.'/'.$value, 'r' );
                    $count = 0;

                    while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
                        // $line is an array of the csv elements
                        $result['txt'][ $count++ ] = $line;
                    }
                    fclose( $file );

                }

            }
        } 
    }

    $xmlArray    = $result['xml']['records']['PositionRecords']['record'];//let
    $txtArray    = $result['txt'];//let
    $resultArray = array();

    if ( ! isset( $txtArray ) ) {

        foreach( $xmlArray as $key => $value ) {

            $resultArray[] = array(
                'keyword'      =>$value['Keyword'],
                'volume'       =>'n/a',
                'SearchEngine' =>$value['SearchEngine'],
                'history'      =>$value['HistoryRecord']
            ); 

        }
    }
    else {
        // loop for xml data
        foreach( $xmlArray as $key => $value ) {
            // get the keyword from xml
            $keyword = $value['Keyword'];
            // and search it in text file array
            foreach( $txtArray as $k => $v ) {
                // if something matched
                if( $keyword === $v[0] ){ 
                    // then add to the result array
                    $resultArray[] = array(
                        'keyword'      =>$keyword,
                        'volume'       =>$v[1],
                        'kvol'         =>$v[0],
                        'SearchEngine' =>$value['SearchEngine'],
                        'history'      =>$value['HistoryRecord']
                    );

                }
            }
        }
    } 
    return $resultArray;
}

// doDbInsert
function doDbInsert( $dataArray, $url ) {
    // var_dump($dataArray[0]);
    // exit();
    // Table Name (from client URL)
    $table_name = $url;//str_replace( '.', '_', $_GET['url'] );
    // Date to check
    $max_date_from_db = checkForDuplicates( $dataArray, $table_name );
    // Open connection to DB
    $link = new PDO( 'mysql:host=localhost;dbname=websexyo_api;charset=utf8', 'websexyo_social', 'lv$uSwg3n,O3' );
    // Show errors
    $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    // Start transaction
    $link->beginTransaction();
    // Prepare query
    $statement = $link->prepare("INSERT INTO ".$table_name."(keyword, category, search_engine, check_date, ranking_change, current_position, url_found, competitor, search_volume)
        VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // for each keyword
    foreach ( $dataArray as $key => $rankObj ) {
        // for each history record
        foreach ( $dataArray[ $key ]['history'] as $value ) {
            // Array to insert
            // check here for duplicate dates
            if ( $max_date_from_db < date( 'Y-m-d', strtotime( $value['CheckDate'] ) ) ) {
                $data = array(
                    $rankObj['keyword'], 
                    $_GET['cat'], 
                    $rankObj['SearchEngine'], 
                    date( 'Y-m-d', strtotime( $value['CheckDate'] ) ),
                    $value['Delta'], 
                    $value['Position'], 
                    $value['URLFound'], 
                    $value['@competitor'],
                    $rankObj['volume']            
                ); 
                // Execute query
                $statement->execute( $data );
            }
        }
    }
    // Commit transaction
    $link->commit();
    // If the transaction was a success
    if ( $statement ) {
        // Tell the user
        $message = "success for: " .$url;
        // Close connection
        $link = null;
    }
    else {
        $message = 'failed';
    }

    echo json_encode( array( 'response' => $message ) );

}

// check for duplicates
function checkForDuplicates( $data, $table ) {

    $db_arr = getAllForDupes( $table );
    $now = new DateTime('NOW');  

    if( is_array($db_arr) && count($db_arr) > 0 ) {
        $results = array(
            "db_date" => max( $db_arr ),
        );
    } else {
        $results = array(
            "db_date" => date_sub( $now, date_interval_create_from_date_string( '3000 days' ) )
        ); 
    }

    $db_date = $results['db_date']->check_date;

    return $db_date;
}

// doDbImport
function doDbImport( $dataArray, $url ) {

    try {

        // Table Name (from client URL)
        $table_name = $url; //str_replace('.', '_', $_GET['url']);
        // Open connection to DB
        $link = new PDO('mysql:host=localhost;dbname=websexyo_api;charset=utf8', 'websexyo_social', 'lv$uSwg3n,O3');
        // Show errors
        $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // SQL
        $sql ="CREATE TABLE IF NOT EXISTS ".$table_name." (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `keyword` varchar(255) NOT NULL,
                `category` varchar(255) DEFAULT NULL,
                `search_engine` varchar(255) NOT NULL,
                `check_date` varchar(255) NOT NULL,
                `ranking_change` varchar(255) NOT NULL,
                `current_position` varchar(255) NOT NULL,
                `url_found` varchar(255) NOT NULL,
                `competitor` varchar(255) DEFAULT NULL,
                `search_volume` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        $link->exec( $sql );

        doDbInsert( $dataArray, $url );

    } catch( PDOException $e ) {
        echo json_encode( array( 'response' => $e->getMessage() ) );
    }

}

// getAll
function getAllForDupes( $table_name ) {

    try {
        // Open connection to DB
        $link = new PDO('mysql:host=localhost;dbname=websexyo_api;charset=utf8', 'websexyo_social', 'lv$uSwg3n,O3');
        // Show errors
        $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // SQL query
        $sql = "SELECT `check_date` FROM {$table_name} GROUP BY `check_date`";
        // Prepare query
        $statement = $link->prepare( $sql );
        // Execute query
        $statement->execute( $data );
        // Result
        $result = $statement->fetchAll(PDO::FETCH_OBJ);
        // Return response
        return $result;

    } catch( PDOException $e ) {
        // Return error
        return $e->getMessage();
    }

}

// xmlToArray
function xmlToArray( $xml, $options = array() ) {
    $defaults = array(
        'namespaceSeparator' => ':',//you may want this to be something other than a colon
        'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
        'alwaysArray' => array(),   //array of xml tag names which should always become arrays
        'autoArray' => true,        //only create arrays for tags which appear more than once
        'textContent' => '$',       //key used for the text content of elements
        'autoText' => true,         //skip textContent key if node has no attributes or child nodes
        'keySearch' => false,       //optional search and replace on tag and attribute names
        'keyReplace' => false       //replace values for above search values (as passed to str_replace())
    );
    $options = array_merge($defaults, $options);
    $namespaces = $xml->getDocNamespaces();
    $namespaces[''] = null; //add base (empty) namespace
    
    //get attributes from all namespaces
    $attributesArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
            //replace characters in attribute name
            if ($options['keySearch']) $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
            $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
            $attributesArray[$attributeKey] = (string)$attribute;
        }
    }
    
    //get child nodes from all namespaces
    $tagsArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->children($namespace) as $childXml) {
            //recurse into child nodes
            $childArray = xmlToArray($childXml, $options);
            list($childTagName, $childProperties) = each($childArray);
    
            //replace characters in tag name
            if ($options['keySearch']) $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
            //add namespace prefix, if any
            if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
    
            if (!isset($tagsArray[$childTagName])) {
                //only entry with this key
                //test if tags of this type should always be arrays, no matter the element count
                $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                        ? array($childProperties) : $childProperties;
            } elseif (
                is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                === range(0, count($tagsArray[$childTagName]) - 1)
            ) {
                //key already exists and is integer indexed array
                $tagsArray[$childTagName][] = $childProperties;
            } else {
                //key exists so convert to integer indexed array with previous value in position 0
                $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
            }
        }
    }
    
    //get text content of node
    $textContentArray = array();
    $plainText = trim((string)$xml);
    if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;
    
    //stick it all together
    $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;
    
    //return node as array
    return array(
        $xml->getName() => $propertiesArray
    );
}