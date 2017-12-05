<?php
header('Access-Control-Allow-Origin: *');

// Include flight PHP (routing)
require 'flight/Flight.php';
// Include DB connection Details
include 'connection.php';
// Google API
require_once 'googleapi/vendor/autoload.php';
// Facebook API
require_once 'facebookapi/src/Facebook/autoload.php';
// Analytics class
require_once 'google-api.php';
// Facebook class
require_once 'facebook-api.php';
// Facebook class
require_once 'adwords-api.php';
// GET Params
$site          = $_GET['url'];
$search_engine = $_GET['se'];
$search_volume = $_GET['sv'];
$category      = $_GET['cat'];
$action        = $_GET['action'];

// Instantiate classes
$clientRankings = new RankTrackerKeywords();
$gaApi = new GoogleAnalyticsAPI();
$fbApi = new FacebookInsightsAPI();
$awApi = new GoogleAdwordsAPI();

// Routes: Default
Flight::route( '/import/@client/@searchengine/@category', array( $clientRankings, 'dirToArray' ) );
// Routes: Get all for (@client)
Flight::route( '/all/@client', array( $clientRankings, 'getAll' ) );
// Routes: Get increased (@client)
Flight::route( '/increased/@client/@device(/@category)/@fromDate/@toDate', array( $clientRankings, 'getIncreased' ) );
// Routes: Get decreased (@client)
Flight::route( '/decreased/@client/@device(/@category)/@fromDate/@toDate', array( $clientRankings, 'getDecreased' ) );
// Routes: Get positions (@client)
Flight::route( '/positions/@client/@device(/@category)/@fromDate/@toDate/@position', array( $clientRankings, 'getTopPositions' ) );
// Routes: Get positions (@client)
Flight::route( '/positionsdist/@client/@device(/@category)/@fromDate/@toDate', array( $clientRankings, 'getTopPositionsDistribution' ) );
// Routes: Get increased all (@client)
Flight::route( '/kwordcount/@client(/@device)', array( $clientRankings, 'getAll' ) );
// Routes: Get increased all (@client)
Flight::route( '/history/@client(/@device)(/@category)/@keyword(/@fromDate)(/@toDate)', array( $clientRankings, 'getKeywordHistory' ) );
// Routes: Get tabledata for (@client)
Flight::route( '/tabledata/@client(/@device)(/@fromDate)(/@toDate)(/@category)', array( $clientRankings, 'getTableData' ) );
// Routes: Get Min and Max dates
Flight::route( '/daterange/@client', array( $clientRankings, 'getMinMaxDates' ) );
// Routes: Get competitor names
Flight::route( '/competitors/@client', array( $clientRankings, 'getCompetitorNames' ) );
// Routes: Get Min and Max dates
Flight::route( '/messages/@key/@from(/@subject)(/@message)(/@name)', array( $clientRankings, 'sendMail' ) );
// Routes: getAllAnlyticsWidgetChartData
Flight::route( '/allanalytics/@client(/@dateType)(/@metric)(/@fromDate)(/@toDate)', array( $gaApi, 'getAllAnlyticsWidgetChartData' ) );
// Routes: getAllAnlyticsWidget1
Flight::route( '/chartwidget1/@client(/@dateType)(/@metric)(/@fromDate)(/@toDate)', array( $gaApi, 'getAllAnlyticsWidget1' ) );
// Routes: getAllAnlyticsWidget2
Flight::route( '/chartwidget2/@client(/@dateType)(/@metric)(/@fromDate)(/@toDate)', array( $gaApi, 'getAllAnlyticsWidget2' ) );
// Routes: getAllAnlyticsWidget3
Flight::route( '/chartwidget3/@client(/@dateType)(/@metric)(/@fromDate)(/@toDate)', array( $gaApi, 'getAllAnlyticsWidget3' ) );
// getMiscStats
Flight::route( '/miscanalytics/@client(/@dateType)(/@fromDate)(/@toDate)', array( $gaApi, 'getMiscStats' ) );
// getAdwordsStats
Flight::route( '/adwords/@client(/@dateType)(/@fromDate)(/@toDate)', array( $gaApi, 'getAdwordsStats' ) );
// getTrafficSourceData
Flight::route( '/trafficsource/@client(/@fromDate)(/@toDate)', array( $gaApi, 'getTrafficSourceData' ) );
// getAudienceData
Flight::route( '/audience/@client(/@fromDate)(/@toDate)', array( $gaApi, 'getAudienceData' ) );
// fbGetPageImpressions
Flight::route( '/fbimpressions/@pageid', array( $fbApi, 'fbGetPageImpressions' ) );
// fbGetLifeTimePageLikes
Flight::route( '/fblikes/@pageid', array( $fbApi, 'fbGetLifeTimePageLikes' ) );
// fbGetLifeTimePageLikesByCountry
Flight::route( '/fblikescountry/@pageid', array( $fbApi, 'fbGetLifeTimePageLikesByCountry' ) );
// fbGetPagePosts
Flight::route( '/fbposts/@pageid', array( $fbApi, 'fbGetPagePosts' ) );
// fbGetPostData
Flight::route( '/fbpost/@postid', array( $fbApi, 'fbGetPostData' ) );
// fbGetTotalPageViews
Flight::route( '/fbpageviews/@pageid', array( $fbApi, 'fbGetTotalPageViews' ) );
// fbGetEngagedUsers
Flight::route( '/fbengaged/@pageid', array( $fbApi, 'fbGetEngagedUsers' ) );
// fbGetPageViews
Flight::route( '/fballpageviews/@pageid', array( $fbApi, 'fbGetPageViews' ) );
// cpcByGroup
Flight::route( '/adwordsmbag/@client/@metric/@dim(/@fromDate)(/@toDate)', array( $awApi, 'getMetricByAdGroup' ) );

// Class definition
class RankTrackerKeywords {

    function __construct() {}   

    /**
     * dirToArray
     * 
     * @param $dir
     * @param $se
     * @param $cat
     *
     * @return todo
     */    
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


    /**
     * doDbInsert
     * 
     * @param $dataArray
     *
     * @return todo
     */      
    public function doDbInsert( $dataArray ) {

        // Table Name (from client URL)
        $table_name = str_replace('.', '_', $_GET['url']);
        // Open connection to DB
        $link = new PDO(HOST, USER, PASS);
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
            // (insert keyword and volume here, into seperate table)
            foreach ( $dataArray[ $key ]['history'] as $value ) {
                // Array to insert
                // (insert into history table)
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


    /**
     * doDbImport
     * 
     * @param $dataArray
     *
     * @return todo
     */     
    public function doDbImport( $dataArray ) {

        try {
            // Table Name (from client URL)
            $table_name = str_replace('.', '_', $_GET['url']);
            // Open connection to DB
            $link = new PDO(HOST, USER, PASS);
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


    /**
     * Get all keyword rankings for a client
     * 
     * @param client URL
     *
     * @return Database object containing all rankings.
     */        
    public function getAllInternal( $url, $device ) {
        
        try {
            $search_type = $device === 'desktop' ? '(Mobile)' : '(Mobile)';
            $not = $device === 'desktop' ? 'NOT' : '';
            // Table Name (from client URL)
            $table_name = str_replace( '.', '_', $url );
            // Open connection to DB
            $link = new PDO(HOST, USER, PASS);
            // Show errors
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // SQL query
            $sql = "SELECT * 
                    FROM  {$table_name}
                    WHERE `competitor` IS NULL
                    AND search_engine {$not} LIKE '%{$search_type}' 
                    GROUP BY keyword";
            // Prepare query
            $statement = $link->prepare( $sql );
            // Execute query
            $statement->execute( $data );
            // Result
            $result = $statement->fetchAll(PDO::FETCH_OBJ);
            // Return response
            return count( $result );

        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }

    }    

    /**
     * Get all keyword rankings for a client
     * 
     * @param client URL
     *
     * @return Database object containing all rankings.
     */        
    public function getAll( $url, $device ) {

        try {
            $search_type = $device === 'desktop' ? '(Mobile)' : '(Mobile)';
            $not = $device === 'desktop' ? 'NOT' : '';
            // Table Name (from client URL)
            $table_name = str_replace( '.', '_', $url );
            // Open connection to DB
            $link = new PDO(HOST, USER, PASS);
            // Show errors
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // SQL query
            $sql = "SELECT * 
                    FROM  {$table_name}
                    WHERE url_found LIKE '%{$url}%' 
                    AND search_engine {$not} LIKE '%{$search_type}' 
                    GROUP BY keyword";
            // Prepare query
            $statement = $link->prepare( $sql );
            // Execute query
            $statement->execute( $data );
            // Result
            $result = $statement->fetchAll(PDO::FETCH_OBJ);
            // Return response
            echo json_encode( array( 'response' => count( $result ) ) );

        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }

    }


    /**
     * Get all keywords which have increased for given date range
     * 
     * @param client URL
     * @param device to check (desktop/mobile)
     * @param keyword category 
     * @param start date
     * @param end date
     *               
     * @return Database object containing all increased keyword rankings.
     */    
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
            $sql = "SELECT `keyword`, `current_position`, `search_volume` 
            		FROM {$table_name}
            		WHERE url_found LIKE '%{$url}%' 
            		AND check_date BETWEEN '{$fromDate}' AND '{$toDate}' 
            		AND (category LIKE '%{$cat}%' OR category IS NULL) 
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

    /**
     * Get all keywords which have decreased for given date range
     * 
     * @param client URL
     * @param device to check (desktop/mobile)
     * @param keyword category 
     * @param start date
     * @param end date
     *               
     * @return Database object containing all decreased keyword rankings.
     */    
    public function getDecreased( $url, $device, $category, $fromDate, $toDate ) {
        
        try {
            // Search Type
            $search_type = $device === 'desktop' ? '(Mobile)' : '(Mobile)';
            // Conditional for WHERE clause
            $not = $device === 'desktop' ? 'NOT' : '';
            // Table Name (from client URL)
            $table_name = str_replace( '.', '_', $url );
            // Open connection to DB
            $link = new PDO( HOST, USER, PASS );
            // Show errors
            $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            // SQL query
            $sql = "SELECT * 
                    FROM {$table_name} 
                    WHERE url_found LIKE '%{$url}%' 
                    AND check_date BETWEEN '{$fromDate}' AND '{$toDate}' 
                    AND (category LIKE '%{$cat}%' OR category IS NULL) 
                    AND search_engine {$not} LIKE '%{$search_type}' 
                    AND ranking_change LIKE '%-%' 
                    GROUP BY keyword 
                    ORDER BY check_date DESC";
            // Prepare query
            $statement = $link->prepare( $sql );
            // Execute query
            $statement->execute( $data );
            // Result
            $result = $statement->fetchAll( PDO::FETCH_OBJ );
            // Return response
            echo json_encode( array( 'response' => empty( $result ) ? array( 'status' => 'No Data To Show' ) : $result ) );

        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }

    }

    /**
     * Get all keywords which have decreased for given date range
     * 
     * @param client URL
     * @param device to check (desktop/mobile)
     * @param keyword category 
     * @param start date
     * @param end date
     *               
     * @return Database object containing all decreased keyword rankings.
     */    
    public function getTopPositions( $url, $device, $category, $fromDate, $toDate, $position ) {
        
        try {
            $position = (int)$position;
            // Search Type
            $search_type = $device === 'desktop' ? '(Mobile)' : '(Mobile)';
            // Conditional for WHERE clause
            $not = $device === 'desktop' ? 'NOT' : '';
            // Table Name (from client URL)
            $table_name = str_replace( '.', '_', $url );
            // Open connection to DB
            $link = new PDO( HOST, USER, PASS );
            // Show errors
            $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            // SQL query
            $sql = "SELECT * 
                    FROM {$table_name} 
                    WHERE url_found LIKE '%{$url}%' 
                    AND check_date BETWEEN '{$fromDate}' AND '{$toDate}' 
                    AND (category LIKE '%{$cat}%' OR category IS NULL) 
                    AND search_engine {$not} LIKE '%{$search_type}' 
                    AND current_position <= {$position} 
                    GROUP BY keyword 
                    ORDER BY check_date DESC";
            // Prepare query
            $statement = $link->prepare( $sql );
            // Execute query
            $statement->execute( $data );
            // Result
            $result = $statement->fetchAll( PDO::FETCH_OBJ );
            // Return response
            echo json_encode( array( 'response' => $result ) );

        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }

    }


    public function getTopPositionsDistribution( $url, $device, $category, $fromDate, $toDate ) {

        $topPositions = array( 1, 3, 5, 10, 20 );
        $finalResult = array();
        $total = $this->getAllInternal( $url, $device ); 
        try {
            foreach ($topPositions as $key => $value) {
                // Search Type
                $search_type = $device === 'desktop' ? '(Mobile)' : '(Mobile)';
                // Conditional for WHERE clause
                $not = $device === 'desktop' ? 'NOT' : '';
                // Table Name (from client URL)
                $table_name = str_replace( '.', '_', $url );
                // Open connection to DB
                $link = new PDO( HOST, USER, PASS );
                // Show errors
                $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                // SQL query
                $sql = "SELECT * 
                        FROM {$table_name} 
                        WHERE url_found LIKE '%{$url}%' 
                        AND check_date BETWEEN '{$fromDate}' AND '{$toDate}' 
                        AND (category LIKE '%{$cat}%' OR category IS NULL) 
                        AND search_engine {$not} LIKE '%{$search_type}' 
                        AND current_position <= {$value} 
                        GROUP BY keyword 
                        ORDER BY check_date DESC";
                // Prepare query
                $statement = $link->prepare( $sql );
                // Execute query
                $statement->execute( $data );
                // Result
                $label = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                $finalResult[$key] = array(
                    "label" => "top " . $label->format( $value ),
                    "value" => count( $statement->fetchAll( PDO::FETCH_OBJ ) )
                ); 
            }
            $finalResult[0]['value'] = ( $finalResult[0]['value'] / $total ) * 100;
            $finalResult[1]['value'] = ( $finalResult[1]['value'] / $total ) * 100;
            $finalResult[2]['value'] = ( $finalResult[2]['value'] / $total ) * 100;
            $finalResult[3]['value'] = ( $finalResult[3]['value'] / $total ) * 100;
            $finalResult[4]['value'] = ( $finalResult[4]['value'] / $total ) * 100;
            $finalResult[0]['color'] = '#9C27B0';
            $finalResult[1]['color'] = '#FFC107';
            $finalResult[2]['color'] = '#00BCD4';
            $finalResult[3]['color'] = '#2196F3';
            $finalResult[4]['color'] = '#e15c74';            
            // Return response
            echo json_encode( array( 'response' => $finalResult ) );
    
        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }
    }


    /**
     * Get ranking history for a given keyword 
     * 
     * @param client URL
     * @param device to check (desktop/mobile)
     * @param keyword category
     * @param keyword to check     
     * @param start date
     * @param end date
     *               
     * @return Database object containing keyword history.
     */     
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
            $link = new PDO( HOST, USER, PASS );
            // Show errors
            $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            // SQL query
            $sql = "SELECT * 
            		FROM {$table_name} 
            		WHERE keyword = '{$keyword}' 
            		AND check_date BETWEEN '{$fromDate}' AND '{$toDate}' 
            		AND (category LIKE '%{$cat}%' OR category IS NULL)
            		AND search_engine {$not} LIKE '%{$search_type}'  
            		ORDER BY check_date DESC";
            // Prepare query
            $statement = $link->prepare( $sql );
            // Execute query
            $statement->execute();
            // Result
            $result = $statement->fetchAll( PDO::FETCH_OBJ );
            // Return response
            echo json_encode( array( 'response' => $result ) );

        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }

    }


    /**
     * Get main keyword table data 
     * 
     * @param client URL
     * @param device to check (desktop/mobile)
     * @param keyword category    
     * @param start date
     * @param end date
     *               
     * @return Database object containing all keyword rankings.
     */      
    public function getTableData( $url, $device, $fromDate, $toDate, $category ) {

        try {
            // Default to 'desktop'
            //if ( ! $device || $device === '' ) { $device = 'desktop'; };

            if ( ( ! $fromDate && ! $toDate ) || ( $fromDate === '' && $toDate === '' ) ) {
                $fromDate = date("Y-m-d", strtotime("-10 months"));
                $toDate = date("Y-m-d");
            }           
            // Keyword Catgory
            $cat = isset($category) ? $category : '';
        	// Search Type
        	$search_type = $device === 'desktop' ? '(Mobile)' : '(Mobile)';
        	// Conditional for WHERE clause
        	$not = $device === 'desktop' ? 'NOT' : '';      	
            // Table Name (from client URL)
            $table_name = str_replace( '.', '_', $url );
            // Open connection to DB
            $link = new PDO( HOST, USER, PASS );
            // Show errors
            $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            // SQL query
            if ($cat === '') {
                $sql = "SELECT competitor 
                        FROM {$table_name} 
                        GROUP BY competitor";
            }
            else {
                $sql = "SELECT competitor 
                        FROM {$table_name} 
                        WHERE category = '{$cat}'
                        GROUP BY competitor";                
            }

            // Prepare query
            $statement = $link->prepare( $sql );
            // Execute query
            $statement->execute();
            // Result
            $result = $statement->fetchAll( PDO::FETCH_OBJ );

            $resultNew = array();

            foreach ( $result as $key => $value ) {

            	$valToUse = $value->competitor === null ? $url : $value->competitor;

            	if ( ! $value->competitor || $value->competitor === null ) {
                    
            		$sql2 = "SELECT ranking_change AS `change`, current_position AS `currentPosition`, competitor AS `name`, keyword, search_volume, check_date, search_engine, url_found
	        				FROM {$table_name} 
	        				WHERE competitor IS NULL
                            AND check_date BETWEEN '{$fromDate}' AND '{$toDate}'                              
	        				AND search_engine {$not} LIKE '%{$search_type}' 
                            AND (category LIKE '%{$cat}%' OR category IS NULL) 
	        				GROUP BY keyword 
	        				ORDER BY check_date DESC";
            	}
            	else {
					$sql2 = "SELECT ranking_change AS `change`, current_position AS `currentPosition`, competitor AS `name`, keyword, search_volume, check_date, search_engine, url_found
							FROM {$table_name} 
							WHERE competitor = '{$valToUse}' 
                            AND check_date BETWEEN '{$fromDate}' AND '{$toDate}'                                                       
							AND search_engine {$not} LIKE '%{$search_type}' 
                            AND (category LIKE '%{$cat}%' OR category IS NULL)
							GROUP BY keyword 
							ORDER BY check_date DESC";            		
            	}
            	
	            // Prepare query
	            $statement = $link->prepare( $sql2 );
	            // Execute query
	            $statement->execute();
	            // Result
	            $resultNew[] = $statement->fetchAll( PDO::FETCH_OBJ );	            		
            }

            $resultArray = array();

            foreach ( $resultNew as $key => $value ) {
            	foreach ( $value as $n_key => $n_value ) {

            		$resultArray[ $n_key ]['keyword'] 	  = $n_value->keyword;
                    $resultArray[ $n_key ]['searchType']  = $n_value->search_engine;
                    $resultArray[ $n_key ]['checkDate']   = $n_value->check_date;                                          
                    $resultArray[ $n_key ]['volume'] 	  = ( int )$n_value->search_volume;
                    $resultArray[ $n_key ]['myrankingscurrent']  = $resultNew[0][$n_key]->currentPosition;
                    $resultArray[ $n_key ]['myrankingschange']  = $resultNew[0][$n_key]->change;
            		$resultArray[ $n_key ]['myrankingsurlfound']  = $resultNew[0][$n_key]->url_found;                 
                    $resultArray[ $n_key ]['myrankings']  = $resultNew[0][ $n_key ];
                    
                    $resultArray[ $n_key ]['competitor1current'] = $resultNew[1][$n_key]->currentPosition;
                    $resultArray[ $n_key ]['competitor1change'] = $resultNew[1][$n_key]->change;
                    $resultArray[ $n_key ]['competitor1urlfound'] = $resultNew[1][$n_key]->url_found;
                    $resultArray[ $n_key ]['competitor1name'] = $resultNew[1][$n_key]->name;
                    $resultArray[ $n_key ]['competitor1'] = $resultNew[1][ $n_key ];

                    $resultArray[ $n_key ]['competitor2current'] = $resultNew[2][$n_key]->currentPosition;
                    $resultArray[ $n_key ]['competitor2change'] = $resultNew[2][$n_key]->change;
                    $resultArray[ $n_key ]['competitor2urlfound'] = $resultNew[2][$n_key]->url_found;
                    $resultArray[ $n_key ]['competitor2name'] = $resultNew[2][$n_key]->name;
                    $resultArray[ $n_key ]['competitor2'] = $resultNew[2][ $n_key ];

                    $resultArray[ $n_key ]['competitor3current'] = $resultNew[3][$n_key]->currentPosition;
                    $resultArray[ $n_key ]['competitor3change'] = $resultNew[3][$n_key]->change;
                    $resultArray[ $n_key ]['competitor3urlfound'] = $resultNew[3][$n_key]->url_found;
                    $resultArray[ $n_key ]['competitor3name'] = $resultNew[3][$n_key]->name;
            		$resultArray[ $n_key ]['competitor3'] = $resultNew[3][ $n_key ];
            	}
            }        
           
            // Return response
            echo json_encode( array( 'response' => $resultArray ) );

        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }

    }


    /**
     * Get max/min date range for given client
     * 
     * @param client URL
     *               
     * @return Database object containing max/min dates.
     */     
    public function getMinMaxDates( $url ) {
        try {
            // Table Name (from client URL)
            $table_name = str_replace( '.', '_', $url );            
            // Open connection to DB
            $link = new PDO( HOST, USER, PASS );
            // Show errors
            $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            // SQL query
            $sql = "SELECT MAX(check_date) AS `maxDate`, MIN(check_date) AS `minDate` 
                    FROM {$table_name} 
                    ORDER BY check_date DESC";
            // Prepare query
            $statement = $link->prepare( $sql );
            // Execute query
            $statement->execute();
            // Result
            $result = $statement->fetchAll( PDO::FETCH_OBJ );
            // Return response
            echo json_encode( array( 'response' => $result ) );

        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'response' => $e->getMessage() ) );
        }
    }      



    /**
     * Get max/min date range for given client
     * 
     * @param client URL
     *               
     * @return Database object containing max/min dates.
     */     
    public function getCompetitorNames( $url ) {
        try {
            // Table Name (from client URL)
            $table_name = str_replace( '.', '_', $url );            
            // Open connection to DB
            $link = new PDO( HOST, USER, PASS );
            // Show errors
            $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            // SQL query
            $sql = "SELECT `competitor` 
                    FROM {$table_name} 
                    GROUP BY competitor DESC";
            // Prepare query
            $statement = $link->prepare( $sql );
            // Execute query
            $statement->execute();
            // Result
            $result = $statement->fetchAll( PDO::FETCH_OBJ );
            // Return response
            echo json_encode( $result );

        } catch( PDOException $e ) {
            // Return error
            echo json_encode( array( 'error' => $e->getMessage() ) );
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


    /**
     * Send my via PHP mail() 
     * 
     * @param client key
     * @param from address
     * @param message subject    
     * @param message content
     * @param sender name
     *               
     * @return success or failure response message.
     */ 
    public function sendMail( $clientKey, $from, $subject, $message, $name ) {
        $errors = '';
        $key = 'ce43259f2792e045c5099df7fbd8f61c';
        
        if( empty( $errors ) && $clientKey === $key) {
        
            $from_email = filter_var( base64_decode( $from ), FILTER_VALIDATE_EMAIL );
            $message    = filter_var( $message, FILTER_SANITIZE_STRING );
            $from_name  = filter_var( $name, FILTER_SANITIZE_STRING );
            $m_subject  = filter_var( $subject, FILTER_SANITIZE_STRING );
            $to_email   = 'affiliate@mintbet.com';
            
            $contact = "<p><strong>Name:</strong> $from_name</p>
                        <p><strong>Email:</strong> $from_email</p>";
            $content = "<p>$message</p>";
        
            $website = 'MintBet.com Affiliate';
            $email_subject = $m_subject;
        
            $email_body = '<html><body>';
            $email_body .= "$contact $content";
            $email_body .= '</body></html>';
        
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $headers .= "From: $from_name\n";
            $headers .= "Reply-To: $from_email";
        
            mail( $to_email, $email_subject, $email_body, $headers );
        
            $response_array['status'] = 'success';
            echo json_encode( $response_array );
       } else {
           $response_array['status'] = 'error';
           echo json_encode( $response_array );
       }        
    }


}

// Start api router
Flight::start();

?>