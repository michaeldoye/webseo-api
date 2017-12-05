<?php

// Class definition
class GoogleAdwordsAPI {
	
  function __construct() {}

	/**
	 * Initializes an Analytics Reporting API V4 service object.
	 *
	 * @return An authorized Analytics Reporting API V4 service object.
	 */
	public function initAnalytics() {
		
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
	* Get adwords Cost per click for adgroups
	* 
	* @param analytics view id     
	* @param start date
	* @param end date        
	*               
	* @return Array
	*/
	public function getMetricByAdGroup( $viewId, $metric, $dim, $fromDate, $toDate ) {
		$analytics  = $this->initAnalytics();            
		
		$now = new DateTime('NOW');
		$end = $now->format('Y-m-d');
		$last = date_sub( $now, date_interval_create_from_date_string( '30 days' ) );
		$start = $last->format('Y-m-d'); 
		$dateType = $dateType ? $dateType : 'date';
		$START_DATE = $fromDate ? $fromDate : $start;
		$END_DATE = $toDate ? $toDate : $end;            

		// Replace with your view ID, for example XXXX.
		$VIEW_ID = $viewId;

		// Create the DateRange object.
		$dateRangeThisMonth = new Google_Service_AnalyticsReporting_DateRange();
		$dateRangeThisMonth->setStartDate( $START_DATE );
		$dateRangeThisMonth->setEndDate( $END_DATE );

		// Create the Metrics object.
		$metricToSet = new Google_Service_AnalyticsReporting_Metric();
		$metricToSet->setExpression( $metric );
		$metricToSet->setAlias("value");

		// Create the Dimensions object.
		$gadates = new Google_Service_AnalyticsReporting_Dimension();
		$gadates->setName( $dim );       

		// Create the ReportRequest object.
		$request = new Google_Service_AnalyticsReporting_ReportRequest();
		$request->setViewId( $VIEW_ID );
		$request->setDateRanges( array( $dateRangeThisMonth ) );
		$request->setDimensions( array( $gadates ) );
		$request->setMetrics( array( $metricToSet ) );

		$body = new Google_Service_AnalyticsReporting_GetReportsRequest();
		$body->setReportRequests( array( $request) );
		$returnBody = $analytics->reports->batchGet( $body );
		
		echo json_encode( array( 'response' => $this->formatAnalytics( $returnBody ) ) );
	}


	/**
	* Formats analytics data
	* 
	* @param analytics reporting object         
	*               
	* @return Array
	*/
	public function formatAnalytics( $reports ) {
		
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
						$responseArray[$rowIndex][$k][$entry->getName()] = $values[$k];
					}
				}
			}
		}

		return array_map(function($v){ return $v[0]; }, $responseArray);
	}	

}