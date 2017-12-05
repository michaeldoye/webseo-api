<?php

// Class definition
class GoogleAnalyticsAPI {
	
	function __construct() {}


	/**
	 * Initializes an Analytics Reporting API V4 service object.
	 *
	 * @return An authorized Analytics Reporting API V4 service object.
	 */
	public function initializeAnalytics() {

		// Use the developers console and download your service account
		// credentials in JSON format. Place them in this directory or
		// change the key file location if necessary.
		$KEY_FILE_LOCATION = 'googleapi/API Project-ebeb6a7292d2.json';

		// Create and configure a new client object.
		$client = new Google_Client();
		$client->setApplicationName("Hello Analytics Reporting");
		$client->setAuthConfig( $KEY_FILE_LOCATION );
		$client->setScopes( ['https://www.googleapis.com/auth/analytics.readonly'] );
		$analytics = new Google_Service_AnalyticsReporting( $client );

		return $analytics;
	}
		
		
	/**
	* Gets all defined analytics data for dashbord widgets
	* 
	* @param analytics view id   
	* @param start date
	* @param end date
	*               
	* @return Array containing analytics objects.
	*/
	public function getAllAnlyticsWidgetChartData( $viewId, $dateType, $startDate, $endDate ) {
		try {

			$now = new DateTime('NOW');
			$end = $now->format('Y-m-d');
			$last = date_sub( $now, date_interval_create_from_date_string( '30 days' ) );
			$start = $last->format('Y-m-d');
			$dateType = $dateType ? $dateType : 'date';        

			$START_DATE = $startDate ? $startDate : $start;
			$END_DATE = $endDate ? $endDate : $end;

			$toDiff_s = new DateTime( $START_DATE );
			$toDiff_e = new DateTime( $END_DATE );
			$DATE_DIFF = $toDiff_s->diff( $toDiff_e )->format("%a");

			$analytics           = $this->initializeAnalytics();
			// Session Comparison
			$sessionComparison   = $this->getAnalyticsComaprisonReport( $analytics, $viewId, 'ga:sessions', $START_DATE, $END_DATE );
			// Session Chart
			$sessionChart        = $this->getAnalyticsWidgetChartData( $analytics, $viewId, 'ga:sessions', $START_DATE, $END_DATE, $dateType );
			// Organic Comparison        
			$organicComparison   = $this->getAnalyticsComaprisonReport( $analytics, $viewId, 'ga:organicSearches', $START_DATE, $END_DATE );
			// Organic Chart
			$organicChart        = $this->getAnalyticsWidgetChartData( $analytics, $viewId, 'ga:organicSearches', $START_DATE, $END_DATE, $dateType );
			// goalCompletionsAll Comparison
			$allGoalsCcomparison = $this->getAnalyticsComaprisonReport( $analytics, $viewId, 'ga:goalCompletionsAll', $START_DATE, $END_DATE );
			// goalCompletionsAll Chart
			$allGoalsChart       = $this->getAnalyticsWidgetChartData( $analytics, $viewId, 'ga:goalCompletionsAll', $START_DATE, $END_DATE, $dateType );           

			// All Data
			$analytics = array(
				"sessionComparison" => $this->formatAnalyticsComaprisonReport( $sessionComparison ),
				"sessionChart" => array(
					"key" => 'Sessions',
					"diff" => $DATE_DIFF,
					"area" => true,
					"color" => '#81C683', 
					"values" => $this->formatAnalyticsWidgetChartDataDimsTest( $sessionChart, $dateType )
				),
				"organicComparison" => $this->formatAnalyticsComaprisonReport( $organicComparison ),
				"organicChart" => array(
					"key" => 'Organic Searches',
					"diff" => $DATE_DIFF,
					"area" => true,
					"color" => '#FFD54F',
					"values" => $this->formatAnalyticsWidgetChartDataDimsTest( $organicChart, $dateType )
				),
				"allGoalsCcomparison" => $this->formatAnalyticsComaprisonReport( $allGoalsCcomparison ),
				"allGoalsChart" => array(
					(object) array(
						"key" => 'Total Goal Completions',
						"diff" => $DATE_DIFF,
						"area" => true,
						"color" => '#7986CC',
						"values" => $this->formatAnalyticsWidgetChartDataDimsTest( $allGoalsChart, $dateType )
					)
				)                             
			);

			// Return Response
			echo json_encode( array( 'response' => $analytics ) );
			
		} catch (Exception $e) {
			echo json_encode( array( 'response' => $e ) );
		}
	}

	/**
	* Gets all defined analytics data for dashbord widgets
	* 
	* @param analytics view id   
	* @param start date
	* @param end date
	*               
	* @return Array containing analytics objects.
	*/
	public function getAllAnlyticsWidget1( $viewId, $dateType, $metric, $startDate, $endDate ) {
		try {

			$now = new DateTime('NOW');
			$end = $now->format('Y-m-d');
			$last = date_sub( $now, date_interval_create_from_date_string( '30 days' ) );
			$start = $last->format('Y-m-d');
			$dateType = $dateType ? $dateType : 'date';        

			$START_DATE = $startDate ? $startDate : $start;
			$END_DATE = $endDate ? $endDate : $end;

			$toDiff_s = new DateTime( $START_DATE );
			$toDiff_e = new DateTime( $END_DATE );
			$DATE_DIFF = $toDiff_s->diff( $toDiff_e )->format("%a");

			$analytics           = $this->initializeAnalytics();
			// Session Comparison
			$sessionComparison   = $this->getAnalyticsComaprisonReport( $analytics, $viewId, $metric, $START_DATE, $END_DATE );
			// Session Chart
			$sessionChart        = $this->getAnalyticsWidgetChartData( $analytics, $viewId, $metric, $START_DATE, $END_DATE, $dateType );         

			// All Data
			$analytics = array(
				"sessionComparison" => $this->formatAnalyticsComaprisonReport( $sessionComparison ),
				"sessionChart" => array(
					"key" => ucwords( str_replace( 'ga:', '', $metric ) ),
					"icon" => $this->getMdIconsForCharts( $metric ),					
					"diff" => $DATE_DIFF,
					"area" => true,
					"color" => '#81C683', 
					"values" => $this->formatAnalyticsWidgetChartDataDimsTest( $sessionChart, $dateType )
				)                            
			);

			// Return Response
			echo json_encode( array( 'response' => $analytics ) );
			
		} catch (Exception $e) {
			echo json_encode( array( 'response' => $e ) );
		}
	}	


	/**
	* Gets all defined analytics data for dashbord widgets
	* 
	* @param analytics view id   
	* @param start date
	* @param end date
	*               
	* @return Array containing analytics objects.
	*/
	public function getAllAnlyticsWidget2( $viewId, $dateType, $metric, $startDate, $endDate ) {
		try {

			$now = new DateTime('NOW');
			$end = $now->format('Y-m-d');
			$last = date_sub( $now, date_interval_create_from_date_string( '30 days' ) );
			$start = $last->format('Y-m-d');
			$dateType = $dateType ? $dateType : 'date';        

			$START_DATE = $startDate ? $startDate : $start;
			$END_DATE = $endDate ? $endDate : $end;

			$toDiff_s = new DateTime( $START_DATE );
			$toDiff_e = new DateTime( $END_DATE );
			$DATE_DIFF = $toDiff_s->diff( $toDiff_e )->format("%a");

			$analytics           = $this->initializeAnalytics();
			// Organic Comparison        
			$organicComparison   = $this->getAnalyticsComaprisonReport( $analytics, $viewId, $metric, $START_DATE, $END_DATE );
			// Organic Chart
			$organicChart        = $this->getAnalyticsWidgetChartData( $analytics, $viewId, $metric, $START_DATE, $END_DATE, $dateType );        

			// All Data
			$analytics = array(
				"organicComparison" => $this->formatAnalyticsComaprisonReport( $organicComparison ),
				"organicChart" => array(
					"key" => ucwords( str_replace( 'ga:', '', $metric ) ),
					"icon" => $this->getMdIconsForCharts( $metric ),					
					"diff" => $DATE_DIFF,
					"area" => true,
					"color" => '#FFD54F',
					"values" => $this->formatAnalyticsWidgetChartDataDimsTest( $organicChart, $dateType )
				)                            
			);

			// Return Response
			echo json_encode( array( 'response' => $analytics ) );
			
		} catch (Exception $e) {
			echo json_encode( array( 'response' => $e ) );
		}
	}


	/**
	* Gets all defined analytics data for dashbord widgets
	* 
	* @param analytics view id   
	* @param start date
	* @param end date
	*               
	* @return Array containing analytics objects.
	*/
	public function getAllAnlyticsWidget3( $viewId, $dateType, $metric, $startDate, $endDate ) {
		try {

			$now = new DateTime('NOW');
			$end = $now->format('Y-m-d');
			$last = date_sub( $now, date_interval_create_from_date_string( '30 days' ) );
			$start = $last->format('Y-m-d');
			$dateType = $dateType ? $dateType : 'date';        

			$START_DATE = $startDate ? $startDate : $start;
			$END_DATE = $endDate ? $endDate : $end;

			$toDiff_s = new DateTime( $START_DATE );
			$toDiff_e = new DateTime( $END_DATE );
			$DATE_DIFF = $toDiff_s->diff( $toDiff_e )->format("%a");

			$analytics           = $this->initializeAnalytics();
			// goalCompletionsAll Comparison
			$allGoalsCcomparison = $this->getAnalyticsComaprisonReport( $analytics, $viewId, $metric, $START_DATE, $END_DATE );
			// goalCompletionsAll Chart
			$allGoalsChart       = $this->getAnalyticsWidgetChartData( $analytics, $viewId, $metric, $START_DATE, $END_DATE, $dateType );      

			// All Data
			$analytics = array(
				"allGoalsCcomparison" => $this->formatAnalyticsComaprisonReport( $allGoalsCcomparison ),
				"allGoalsChart" => array(
					(object) array(
						"key" => ucwords( str_replace( 'ga:', '', $metric ) ),
						"icon" => $this->getMdIconsForCharts( $metric ),
						"diff" => $DATE_DIFF,
						"area" => true,
						"color" => '#7986CC',
						"values" => $this->formatAnalyticsWidgetChartDataDimsTest( $allGoalsChart, $dateType )
					)
				)                             
			);;

			// Return Response
			echo json_encode( array( 'response' => $analytics ) );
			
		} catch (Exception $e) {
			echo json_encode( array( 'response' => $e ) );
		}
	}		
		

	//Charts
	/**
	* Gets chart data for analytics dashboard widgets
	* 
	* @param analytics view id   
	* @param start date
	* @param end date
	* @param the metric to check ie, sessions, goals, etc     
	*               
	* @return Array containing single chart data for given metric.// REMOVE THIS FUNCTION
	*/
	public function getAnalyticsWidgetChart( $viewId, $fromDate, $toDate, $metric ) {
		try {

			$analytics  = $this->initializeAnalytics();
			$reportData = $this->getAnalyticsWidgetChartData( $analytics, $viewId, $metric, $fromDate, $toDate );
			$response   = $this->formatAnalyticsWidgetChartData( $reportData );         

			$responseArr = array(
				"key" => $metric,
				"area" => true,
				"color" => '#FFD54F',
				"values" => $response
			);

			// Return response
			echo json_encode( array( 'response' => $responseArr ) );   
						
		} catch( Exception $e ) {
			// Return error
			echo json_encode( array( 'response' => $e ) );
		}            
	}
		
		
	/**
	* Internal method to define metrics and dimensions for chart widget data
	* 
	* @param analytics reporting object       
	* @param analytics view id
	* @param the metric to check ie, sessions, goals, etc       
	* @param start date
	* @param end date    
	*               
	* @return Analytics reporting object.
	*/
	public function getAnalyticsWidgetChartData( $analytics, $viewId, $metric, $startDate, $endDate, $dateType ) {
		// Replace with your view ID, for example XXXX.
		$VIEW_ID = $viewId;
		$METRIC = $metric;
		$START_DATE = $startDate;
		$END_DATE = $endDate;

		// Create the DateRange object.
		$dateRangeThisMonth = new Google_Service_AnalyticsReporting_DateRange();
		$dateRangeThisMonth->setStartDate( $START_DATE );
		$dateRangeThisMonth->setEndDate( $END_DATE );

		// Create the Metrics object.
		$metricToSet = new Google_Service_AnalyticsReporting_Metric();
		$metricToSet->setExpression( $METRIC );
		$metricToSet->setAlias("value");

		// Create the Dimensions object.
		$gadates = new Google_Service_AnalyticsReporting_Dimension();
		$gadates->setName("ga:".$dateType);       

		// Create the ReportRequest object.
		$request = new Google_Service_AnalyticsReporting_ReportRequest();
		$request->setViewId( $VIEW_ID );
		$request->setDateRanges( array( $dateRangeThisMonth ) );
		$request->setDimensions( array( $gadates ) );
		$request->setMetrics( array( $metricToSet ) );

		$body = new Google_Service_AnalyticsReporting_GetReportsRequest();
		$body->setReportRequests( array( $request) );
		return $analytics->reports->batchGet( $body );         
	}
		
		
	/**
	* Formats analytics chart data for dashboard widgets
	* 
	* @param analytics reporting object         
	*               
	* @return Array containing single chart data for given metric.// REMOVE THIS FUNCTION
	*/
	public function formatAnalyticsWidgetChartData( $reports ) {

		$responseArray = array();

		for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
			$report = $reports[ $reportIndex ];
			$header = $report->getColumnHeader();
			$dimensionHeaders = $header->getDimensions();
			$metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
			$rows = $report->getData()->getRows();

			for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
				$row = $rows[ $rowIndex ];
				$dimensions = $row->getDimensions();
				$metrics = $row->getMetrics();
				for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
					$responseArray[$rowIndex][$i][str_replace( 'ga:', '', $dimensionHeaders[$i] )] = strtotime($dimensions[$i]);
				}

				for ($j = 0; $j < count($metrics); $j++) {
					$values = $metrics[$j]->getValues();
					for ($k = 0; $k < count($values); $k++) {
						$entry = $metricHeaders[$k];
						$responseArray[$rowIndex][$k][$entry->getName()] = (int)$values[$k];
					}
				}
			}
		}

		return array_map(function($v){ return $v[0]; }, $responseArray);
	}
		
		
	// Comparisons
	public function getAnalyticsComparisonReport( $viewId, $fromDate, $toDate, $metric ) {
		try {

			$analytics  = $this->initializeAnalytics();
			$reportData = $this->getOrganicComaprisonReport( $analytics, $viewId, $metric, $fromDate, $toDate );
			$response   = $this->formatAnalyticsComaprisonReport( $reportData );  
						
			echo json_encode( array( 'response' => $response ) );

		} catch( Exception $e ) {
			// Return error
			echo json_encode( array( 'response' => $e ) );
		}
	} 
		
		
	public function getAnalyticsComaprisonReport( $analytics, $viewId, $metric, $startDate, $endDate ) {

		// Replace with your view ID, for example XXXX.
		$VIEW_ID = $viewId;
		$METRIC = $metric;
		$START_DATE = new DateTime( $startDate );
		$END_DATE = new DateTime( $endDate );

		$DATE_DIFF = $START_DATE->diff( $END_DATE )->format("%a");

		$start = DateTime::createFromFormat( 'Y-m-d', $START_DATE->format('Y-m-d') );
		$end = DateTime::createFromFormat( 'Y-m-d', $END_DATE->format('Y-m-d') );
	
		$START_DATE_2 =  date_sub( $start, date_interval_create_from_date_string( $DATE_DIFF .' days' ) );
		$END_DATE_2 =  date_sub( $end, date_interval_create_from_date_string( $DATE_DIFF .' days' ) );

		// Create the DateRange object.
		// Last 30 Days
		$dateRangeThisMonth = new Google_Service_AnalyticsReporting_DateRange();
		$dateRangeThisMonth->setStartDate( $START_DATE->format('Y-m-d') );
		$dateRangeThisMonth->setEndDate( $END_DATE->format('Y-m-d') );
		// Previous 30 days
		$dateRangeLastMonth = new Google_Service_AnalyticsReporting_DateRange();
		$dateRangeLastMonth->setStartDate( $START_DATE_2->format('Y-m-d') );
		$dateRangeLastMonth->setEndDate( $END_DATE_2->format('Y-m-d') );

		// Create the Metrics object.
		// Organic Searches
		$organicSearches = new Google_Service_AnalyticsReporting_Metric();
		$organicSearches->setExpression( $METRIC );
		$organicSearches->setAlias( $METRIC );

		// Create the ReportRequest object.
		$request = new Google_Service_AnalyticsReporting_ReportRequest();
		$request->setViewId( $VIEW_ID );
		$request->setDateRanges( array( $dateRangeThisMonth, $dateRangeLastMonth ) );
		$request->setMetrics( array( $organicSearches ) );

		$body = new Google_Service_AnalyticsReporting_GetReportsRequest();
		$body->setReportRequests( array( $request) );
		return $analytics->reports->batchGet( $body );
	}
		
		
	public function formatAnalyticsComaprisonReport( $reports ) {

		$testArray = array();

		for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
			$report = $reports[ $reportIndex ];
			$header = $report->getColumnHeader();
			$dimensionHeaders = $header->getDimensions();
			$metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
			$rows = $report->getData()->getRows();

			for ( $rowIndex = 0; $rowIndex < count( $rows ); $rowIndex++ ) {
				$row = $rows[ $rowIndex ];
				$dimensions = $row->getDimensions();
				$metrics = $row->getMetrics();
				for ( $i = 0; $i < count( $dimensionHeaders ) && $i < count( $dimensions ); $i++ ) {
					//$testArray['dims'][$i]['name'] = $dimensionHeaders[$i];
					//$testArray['dims'][$i]['value'] = $dimensions[$i];
					//print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "\n");
				}

				for ( $j = 0; $j < count( $metrics ); $j++ ) {
					$values = $metrics[$j]->getValues();
					for ($k = 0; $k < count( $values ); $k++) {
						$entry = $metricHeaders[$k];
						$testArray[$j][$k]['name'] = $entry->getName();
						$testArray[$j][$k]['value'] = $values[$k];
						//print($entry->getName() . ": " . $values[$k] . "\n");
					}
				}
			}
		};

		$diff_01_val = (int)$testArray[0][0]['value'] - (int)$testArray[1][0]['value'];

		$diff_01 = number_format( ( 1 - (int)$testArray[1][0]['value'] / (int)$testArray[0][0]['value'] ) * 100, 2);

		$testArray[ count( $testArray )+1 ]['value'] = $diff_01_val;

		$testArray[ count( $testArray ) ]['percent'] = $diff_01;

		return $testArray;
	}	
			
	/**
	* Get data for adwords graph on traffic page
	*        
	* @param analytics view id     
	* @param start date
	* @param end date    
	*               
	* @return Analytics reporting object.
	*/
	public function getAdwordsStats( $viewId, $dateType, $fromDate, $toDate ) {
		try {

			$analytics  = $this->initializeAnalytics();            

			$now = new DateTime('NOW');
			$end = $now->format('Y-m-d');
			$last = date_sub( $now, date_interval_create_from_date_string( '30 days' ) );
			$start = $last->format('Y-m-d'); 
			$dateType = $dateType ? $dateType : 'date';           

			$START_DATE = $fromDate ? $fromDate : $start;
			$END_DATE = $toDate ? $toDate : $end;            

			// Replace with your view ID, for example XXXX.
			$VIEW_ID = $viewId;

			$metrics = array('ga:impressions', 'ga:adClicks', 'ga:adCost');

			$responses =  array();

			foreach ($metrics as $key => $value) {
				// Create the DateRange object.
				$dateRangeThisMonth = new Google_Service_AnalyticsReporting_DateRange();
				$dateRangeThisMonth->setStartDate( $START_DATE );
				$dateRangeThisMonth->setEndDate( $END_DATE );

				// Create the Metrics object.
				$metricToSet = new Google_Service_AnalyticsReporting_Metric();
				$metricToSet->setExpression( $value );
				$metricToSet->setAlias("value");

				// Create the Dimensions object.
				$gadates = new Google_Service_AnalyticsReporting_Dimension();
				$gadates->setName("ga:".$dateType);       

				// Create the ReportRequest object.
				$request = new Google_Service_AnalyticsReporting_ReportRequest();
				$request->setViewId( $VIEW_ID );
				$request->setDateRanges( array( $dateRangeThisMonth ) );
				$request->setDimensions( array( $gadates ) );
				$request->setMetrics( array( $metricToSet ) );

				$body = new Google_Service_AnalyticsReporting_GetReportsRequest();
				$body->setReportRequests( array( $request) );
				$returnBody = $analytics->reports->batchGet( $body );
				
				$responses[$key] = array(
					"key" => ucwords( str_replace( 'ga:', '', $value ) ),
					"area" => true,
					"color" => '#00BCD4',
					"values" => $this->formatAnalyticsWidgetChartDataDimsTest( $returnBody, $dateType ),
					"description" => 'Shows Total ad impressions against clicks and the total cost'
				);                
			}

			$arrCount = count($responses[0]['values']);
			$responses[1]['values'] = array_pad($responses[1]['values'], $arrCount, (object)array('date' => strtotime('now'), 'value' => 1));
			$responses[2]['values'] = array_pad($responses[2]['values'], $arrCount, (object)array('date' => strtotime('now'), 'value' => 1));

			$responses[1]['color'] = '#9C27B0';
			$responses[2]['color'] = '#FFC107';

			// Return response
			echo json_encode( array( 'response' => $responses ) );   
						
		} catch( Exception $e ) {
			// Return error
			echo json_encode( array( 'response' => $e ) );
		}            
	}
			
		
	/**
	* Get data for traffic sources on traffic page
	*        
	* @param analytics view id     
	* @param start date
	* @param end date    
	*               
	* @return Analytics reporting object.
	*/    
	public function getTrafficSourceData( $viewId, $fromDate, $toDate ) {
		try {

			$analytics  = $this->initializeAnalytics();            

			$now = new DateTime('NOW');
			$end = $now->format('Y-m-d');
			$last = date_sub( $now, date_interval_create_from_date_string( '30 days' ) );
			$start = $last->format('Y-m-d');            

			$START_DATE = $fromDate ? $fromDate : $start;
			$END_DATE = $toDate ? $toDate : $end;            

			// Replace with your view ID, for example XXXX.
			$VIEW_ID = $viewId;

			$metrics = array('ga:sessions');
			$dimensions = array('ga:medium', 'ga:source');

			$responses = array();

			foreach ($dimensions as $key => $value) {
				// Create the DateRange object.
				$dateRangeThisMonth = new Google_Service_AnalyticsReporting_DateRange();
				$dateRangeThisMonth->setStartDate( $START_DATE );
				$dateRangeThisMonth->setEndDate( $END_DATE );

				// Create the Metrics object.
				$metricToSet = new Google_Service_AnalyticsReporting_Metric();
				$metricToSet->setExpression( 'ga:sessions' );
				$metricToSet->setAlias("value");

				// Create the Dimensions object.
				$gadates = new Google_Service_AnalyticsReporting_Dimension();
				$gadates->setName( $value );       

				// Create the ReportRequest object.
				$request = new Google_Service_AnalyticsReporting_ReportRequest();
				$request->setViewId( $VIEW_ID );
				$request->setDateRanges( array( $dateRangeThisMonth ) );
				$request->setDimensions( array( $gadates ) );
				$request->setMetrics( array( $metricToSet ) );

				$body = new Google_Service_AnalyticsReporting_GetReportsRequest();
				$body->setReportRequests( array( $request) );
				$returnBody = $analytics->reports->batchGet( $body );
				
				$responses[$key] = array(
					"key" => ucwords( str_replace( 'ga:', '', $value ) ),
					"area" => true,
					"color" => '#00BCD4',
					"values" => $this->formatTrafficSourcesData( $returnBody ),
					"description" => 'Shows how visitors got to your website'
				);                
			}

			// Return response
			echo json_encode( array( 'response' => $responses ) );   
						
		} catch( Exception $e ) {
			// Return error
			echo json_encode( array( 'response' => $e ) );
		} 
	}
		
	/**
	* Get data for traffic sources on traffic page
	*        
	* @param analytics view id     
	* @param start date
	* @param end date    
	*               
	* @return Analytics reporting object.
	*/    
	public function getAudienceData( $viewId, $fromDate, $toDate ) {
		try {

			$analytics  = $this->initializeAnalytics();            

			$now = new DateTime('NOW');
			$end = $now->format('Y-m-d');
			$last = date_sub( $now, date_interval_create_from_date_string( '30 days' ) );
			$start = $last->format('Y-m-d');            

			$START_DATE = $fromDate ? $fromDate : $start;
			$END_DATE = $toDate ? $toDate : $end;            

			// Replace with your view ID, for example XXXX.
			$VIEW_ID = $viewId;

			$metrics = array('ga:sessions');
			$dimensions = array('ga:userAgeBracket', 'ga:userGender', 'ga:country');

			$responses = array();

			foreach ($dimensions as $key => $value) {
				// Create the DateRange object.
				$dateRangeThisMonth = new Google_Service_AnalyticsReporting_DateRange();
				$dateRangeThisMonth->setStartDate( $START_DATE );
				$dateRangeThisMonth->setEndDate( $END_DATE );

				// Create the Metrics object.
				$metricToSet = new Google_Service_AnalyticsReporting_Metric();
				$metricToSet->setExpression( 'ga:sessions' );
				$metricToSet->setAlias("value");

				// Create the Dimensions object.
				$gadates = new Google_Service_AnalyticsReporting_Dimension();
				$gadates->setName( $value );       

				// Create the ReportRequest object.
				$request = new Google_Service_AnalyticsReporting_ReportRequest();
				$request->setViewId( $VIEW_ID );
				$request->setDateRanges( array( $dateRangeThisMonth ) );
				$request->setDimensions( array( $gadates ) );
				$request->setMetrics( array( $metricToSet ) );

				$body = new Google_Service_AnalyticsReporting_GetReportsRequest();
				$body->setReportRequests( array( $request) );
				$returnBody = $analytics->reports->batchGet( $body );
				
				$responses[$key] = array(
					"key" => ucwords( str_replace( 'ga:', '', $value ) ),
					"area" => true,
					"color" => '#00BCD4',
					"values" => $this->formatTrafficSourcesData( $returnBody ),
					"description" => 'Shows how visitors got to your website'
				);                
			}

			// Return response
			echo json_encode( array( 'response' => $responses ) );   
						
		} catch( Exception $e ) {
			// Return error
			echo json_encode( array( 'response' => $e ) );
		} 
	}    
			
			
	public function formatTrafficSourcesData( $reports ) {
		
		$responseArray = array();

		for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
			$report = $reports[ $reportIndex ];
			$header = $report->getColumnHeader();
			$dimensionHeaders = $header->getDimensions();
			$metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
			$rows = $report->getData()->getRows();

			for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
				$row = $rows[ $rowIndex ];
				$dimensions = $row->getDimensions();
				$metrics = $row->getMetrics();
				for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
					$responseArray[$rowIndex][$i]['label'] = $dimensions[$i];
				}

				for ($j = 0; $j < count($metrics); $j++) {
					$values = $metrics[$j]->getValues();
					for ($k = 0; $k < count($values); $k++) {
						$entry = $metricHeaders[$k];
						$responseArray[$rowIndex][$k][$entry->getName()] = (int)$values[$k];
					}
				}
			}
		}

		return array_map(function($v){ return $v[0]; }, $responseArray);
	}    


	/**
	* Get data for graph on traffic page
	*        
	* @param analytics view id     
	* @param start date
	* @param end date    
	*               
	* @return Analytics reporting object.
	*/
	public function getMiscStats( $viewId, $dateType, $fromDate, $toDate ) {
		try {

			$analytics  = $this->initializeAnalytics();            

			$now = new DateTime('NOW');
			$end = $now->format('Y-m-d');
			$last = date_sub( $now, date_interval_create_from_date_string( '30 days' ) );
			$start = $last->format('Y-m-d');
			$dateType = $dateType ? $dateType : 'date';     

			$START_DATE = $fromDate ? $fromDate : $start;
			$END_DATE = $toDate ? $toDate : $end;            

			// Replace with your view ID, for example XXXX.
			$VIEW_ID = $viewId;

			$metrics = array('ga:pageviews', 'ga:sessions', 'ga:uniquePageviews');

			$responses =  array();

			foreach ($metrics as $key => $value) {
				// Create the DateRange object.
				$dateRangeThisMonth = new Google_Service_AnalyticsReporting_DateRange();
				$dateRangeThisMonth->setStartDate( $START_DATE );
				$dateRangeThisMonth->setEndDate( $END_DATE );

				// Create the Metrics object.
				$metricToSet = new Google_Service_AnalyticsReporting_Metric();
				$metricToSet->setExpression( $value );
				$metricToSet->setAlias("value");

				// Create the Dimensions object.
				$gadates = new Google_Service_AnalyticsReporting_Dimension();
				$gadates->setName("ga:".$dateType);			

				// Create the ReportRequest object.
				$request = new Google_Service_AnalyticsReporting_ReportRequest();
				$request->setViewId( $VIEW_ID );
				$request->setDateRanges( array( $dateRangeThisMonth ) );
				$request->setDimensions( array( $gadates ) );
				$request->setMetrics( array( $metricToSet ) );

				$body = new Google_Service_AnalyticsReporting_GetReportsRequest();
				$body->setReportRequests( array( $request) );
				$returnBody = $analytics->reports->batchGet( $body );
				
				$responses[$key] = array(
					"key" => ucwords( str_replace( 'ga:', '', $value ) ),
					"area" => true,
					"color" => '#FFD54F',
					"values" => $this->formatAnalyticsWidgetChartDataDimsTest( $returnBody, $dateType ),
					"description" => 'Shows Total pageviews against bounces and unique pageviews'
				);                
			}

			$responses[1]['color'] = '#e15c74';
			$responses[2]['color'] = '#2196F3';

			// Return response
			echo json_encode( array( 'response' => $responses ) );   
						
		} catch( Error $e ) {
			// Return error
			echo json_encode( array( 'response' => $e ) );
		}            
	}

	/**
	* Formats analytics chart data for dashboard widgets
	* 
	* @param analytics reporting object         
	*               
	* @return Array containing single chart data for given metric.
	*/
	public function formatAnalyticsWidgetChartDataDimsTest( $reports, $dateType ) {
		
		$responseArray = array();

		for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
			$report = $reports[ $reportIndex ];
			$header = $report->getColumnHeader();
			$dimensionHeaders = $header->getDimensions();
			$metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
			$rows = $report->getData()->getRows();

			for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
				$row = $rows[ $rowIndex ];
				$dimensions = $row->getDimensions();
				$metrics = $row->getMetrics();
				for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
					if ($dateType === 'date') {
						$responseArray[$rowIndex][$i][str_replace( 'ga:', '', $dimensionHeaders[$i] )] = strtotime($dimensions[$i]);
					} else {
						$responseArray[$rowIndex][$i]['date'] = (int)$dimensions[$i];
					}
				}

				for ($j = 0; $j < count($metrics); $j++) {
					$values = $metrics[$j]->getValues();
					for ($k = 0; $k < count($values); $k++) {
						$entry = $metricHeaders[$k];
						$responseArray[$rowIndex][$k][$entry->getName()] = (int)$values[$k] === 0 ? (int)$values[$k]+1 : (int)$values[$k];
					}
				}
			}
		}

		return array_map(function($v){ return $v[0]; }, $responseArray);
	}	

	public function getMdIconsForCharts( $metric ) {
		switch ($metric) {
			case 'ga:sessions':
				$icon = 'person';
				break;
			case 'ga:organicSearches':
				$icon = 'find_in_page';
				break;
			case 'ga:goalCompletionsAll':
				$icon = 'check_circle';
				break;
			case 'ga:bounces':
				$icon = 'error';
				break;
			case 'ga:uniquePageviews':
				$icon = 'pageview';
				break;
			case 'ga:pageviews':
				$icon = 'pageview';
				break;
			case 'ga:newUsers':
				$icon = 'fiber_new';
				break;
			// case 'ga:sessions':
			// 	$icon = '';
			// 	break;
			// case 'ga:sessions':
			// 	$icon = '';
			// 	break;
			// case 'ga:sessions':
			// 	$icon = '';
			// 	break;
			// case 'ga:sessions':
			// 	$icon = '';
			// 	break;																																								
			
			default:
				# code...
				break;
		}
		return $icon;
	}
		
}