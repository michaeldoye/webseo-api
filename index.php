<?php
header('Access-Control-Allow-Origin: *');

// Include flight PHP (routing)
require 'flight/Flight.php';
// Include DB connection Details
include 'connection.php';
// GET Params
$site          = $_GET['url'];
$search_engine = $_GET['se'];
$search_volume = $_GET['sv'];
$category      = $_GET['cat'];
$action        = $_GET['action'];

// Instantiate class
$clientRankings = new RankTrackerKeywords();

// Routes: Default
Flight::route( '/import/@client/@searchengine/@category', array( $clientRankings, 'dirToArray' ) );
// Routes: Get all for (@client)
Flight::route( '/all/@client', array( $clientRankings, 'getAll' ) );
// Routes: Get increased (@client)
Flight::route( '/increased/@client/@device(/@category)/@fromDate/@toDate', array( $clientRankings, 'getIncreased' ) );
// Routes: Get increased all (@client)
Flight::route( '/history/@client(/@device)(/@category)/@keyword(/@fromDate)(/@toDate)', array( $clientRankings, 'getKeywordHistory' ) );

// Class definition
class RankTrackerKeywords {

    function __construct() {}   

    // dirToArray
    public function dirToArray( $dir, $se, $cat ) { 

        $result = array(); 
        $cdir   = scandir( 'clients/'.$dir ); 
        $arr1   = array();

        foreach ( $cdir as $key => $value ) {

            $file_info = pathinfo( $value );

            if ( ! in_array( $value,array( ".", ".." ) ) ) { 
                if ( is_dir( $dir . DIRECTORY_SEPARATOR . $value ) ) { 

                    $result[ $value ] = $this->dirToArray( $dir . DIRECTORY_SEPARATOR . $value ); 

                } 
                else { 

                    if ( $file_info['extension'] == 'xml' ) {
                        if ( isset( $se ) && $se !== 'undefined' ) {

                            if ( strpos( $value, $se ) !== false ) {

                                $xmlstring     = file_get_contents( $dir.'/'.$value );
                                $temp          = preg_replace( '/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $xmlstring );
                                $xml           = simplexml_load_string( $temp );
                                $result['xml'] = $this->xmlToArray( $xml );
                            } 

                        } 
                        elseif ( isset( $cat ) && $cat !== 'undefined' ) {

                            if ( strpos( $value, $cat ) !== false ) {

                                $xmlstring     = file_get_contents( $dir.'/'.$value );
                                $temp          = preg_replace( '/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $xmlstring );
                                $xml           = simplexml_load_string( $temp );
                                $result['xml'] = $this->xmlToArray( $xml );

                            }

                        } 
                        else {
                            if ( strpos( $value, '_' ) !== false ) {

                                $xmlstring     = file_get_contents( $dir.'/'.$value );
                                $temp          = preg_replace( '/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $xmlstring );
                                $xml           = simplexml_load_string( $temp );
                                $result['xml'] = $this->xmlToArray( $xml );

                            }
                        }
                    } 

                    if ( $file_info['extension'] == 'txt' ) {

                        $file  = fopen( $dir.'/'.$value, 'r' );
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

        switch ($_GET['action']) {
            case 'import':
                return $this->doDbImport( $resultArray );
                break;
            
            default:
                return json_encode( $resultArray );
                break;
        }

        //return json_encode( $resultArray );
        // DB Insert
        // $this->doDbInsert($resultArray);
    }

    // doDbInsert
    public function doDbInsert( $dataArray ) {

        // Table Name (from client URL)
        $table_name = str_replace('.', '_', $_GET['url']);
        // Open connection to DB
        $link = new PDO('mysql:host=localhost;dbname=websexyo_api;charset=utf8', 'websexyo_social', 'lv$uSwg3n,O3');
        // Show errors
        $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
                $data = array(
                    $rankObj['keyword'], 
                    $_GET['cat'], 
                    $rankObj['SearchEngine'], 
                    date('Y-m-d', strtotime( $value['CheckDate'] ) ),
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
        // Commit transaction
        $link->commit();
        // If the transaction was a success
        if ( $statement ) {
            // Tell the user
            $message = "success";
            // Close connection
            $link = null;
        }
        else {
            $message = 'failed';
        }

        echo json_encode( array( 'response' => $message ) );

    }

    // doDbImport
    public function doDbImport( $dataArray ) {

        try {
            // Table Name (from client URL)
            $table_name = str_replace('.', '_', $_GET['url']);
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

            $this->doDbInsert( $dataArray );

        } catch( PDOException $e ) {
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }

    }

    // getAll
    public function getAll( $url ) {

        try {
            // Table Name (from client URL)
            $table_name = str_replace( '.', '_', $url );
            // Open connection to DB
            $link = new PDO(HOST, USER, PASS);
            // Show errors
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // SQL query
            $sql ="SELECT * FROM ".$table_name;
            // Prepare query
            $statement = $link->prepare( $sql );
            // Execute query
            $statement->execute( $data );
            // Result
            $result = $statement->fetchAll(PDO::FETCH_OBJ);
            // Return response
            echo json_encode( array( 'response' => $result ) );

        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }

    }

    // getIncreased
    public function getIncreased( $url, $device, $category, $fromDate, $toDate ) {

        try {
        	// Search Type
        	$search_type = $device === 'desktop' ? '(Mobile)' : '(Mobile)';
        	// Conditional for WHERE clause
        	$not = $device === 'desktop' ? 'NOT' : '';
            // Table Name (from client URL)
            $table_name = str_replace( '.', '_', $url );
            // Open connection to DB
            $link = new PDO(HOST, USER, PASS);
            // Show errors
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // SQL query
            $sql = "SELECT * 
            		FROM {$table_name} 
            		WHERE url_found LIKE '%{$url}%' 
            		AND check_date BETWEEN '{$fromDate}' AND '{$toDate}' 
            		AND category LIKE '%{$category}%' 
            		AND search_engine {$not} LIKE '%{$search_type}' 
            		AND ranking_change LIKE '%+%' 
            		GROUP BY keyword 
            		ORDER BY check_date DESC";
            // Prepare query
            $statement = $link->prepare( $sql );
            // Execute query
            $statement->execute( $data );
            // Result
            $result = $statement->fetchAll(PDO::FETCH_OBJ);
            // Return response
            echo json_encode( array( 'response' => $result ) );

        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }

    }

	// getKeywordHistory
    public function getKeywordHistory( $url, $device, $category, $keyword, $fromDate, $toDate ) {
        try {
        	// Default to 'desktop'
        	if ( ! $device || $device === '' ) { $device = 'desktop'; };

        	if ( ( ! $fromDate && ! $toDate ) || ( $fromDate === '' && $toDate === '' ) ) {
        		$fromDate = date("Y-m-d", strtotime("-10 months"));
        		$toDate = date("Y-m-d");
        	}
        	// Search Type
        	$search_type = $device === 'desktop' ? '(Mobile)' : '(Mobile)';
        	 // Conditional for WHERE clause
        	$not = $device === 'desktop' ? 'NOT' : '';
            // Table Name (from client URL)
            $table_name = str_replace( '.', '_', $url );
            // Open connection to DB
            $link = new PDO(HOST, USER, PASS);
            // Show errors
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // SQL query
            $sql = "SELECT * 
            		FROM {$table_name} 
            		WHERE url_found LIKE '%{$url}%'
            		AND keyword = '{$keyword}' 
            		AND check_date BETWEEN '{$fromDate}' AND '{$toDate}' 
            		AND category LIKE '%{$category}%' 
            		AND search_engine {$not} LIKE '%{$search_type}'  
            		ORDER BY check_date DESC";
            // Prepare query
            $statement = $link->prepare( $sql );
            // Execute query
            $statement->execute( $data );
            // Result
            $result = $statement->fetchAll(PDO::FETCH_OBJ);
            // Return response
            echo json_encode( array( 'response' => $result ) );

        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }
    }

    // xmlToArray
    public function xmlToArray( $xml, $options = array() ) {
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
                $childArray = $this->xmlToArray($childXml, $options);
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

}

// Start api router
Flight::start();

?>