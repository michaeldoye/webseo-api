<?php

// Class definition
class FacebookInsightsAPI {
	
	function __construct() {}

		
	/**
	* Initializes an Insights Reporting API V5 service object.
	*
	* @return fb: An authorized FB Insights Reporting API V5 service object.
	*/
	public function initFaceBook() {
		return $fb = new \Facebook\Facebook([
			'app_id' => '1091642577577897',
			'app_secret' => 'b157e2375bfb8e22175c8479a3e0a2d1',
			'default_graph_version' => 'v2.10',
			'default_access_token' => 'EAAPg19gO56kBAH161N4v4mUeQHReACNcM7dpImCZAoMYT58zfq9hVE28ZBJBAoVkXqWEbeSs7FsY72zHSxlLyvQGvKGQSgEcWHaM35de51YbmtlyQZBgZCNMPwn9G3FvY6G7f5oML1mxlYY4WxQkiIzGq6csZCdUZD', // optional
		]);
	}


	/**
	* Gets all page impressions
	* 
	* @param pageId FB page id  
	*               
	* @return graphNode: Object containing insights objects.
	*/	
  	public function fbGetPageImpressions( $pageId ) {

		$fb = $this->initFaceBook();
		$now = new DateTime('NOW');
		$end = $now->format('Y-m-d');
		$last = date_sub( $now, date_interval_create_from_date_string( '30 days' ) );
		$start = $last->format('Y-m-d');

		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
				'/'.$pageId.'/insights/page_impressions?since='.$start.'&until='.$end,
				$fb->default_access_token
			);
		} catch( FacebookExceptionsFacebookResponseException $e ) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch( FacebookExceptionsFacebookSDKException $e ) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		$graphNode = $response->getBody();      
		
		echo $graphNode;
	}


	/**
	* Gets all page impressions
	* 
	* @param pageId FB page id  
	*               
	* @return graphNode: Object containing insights objects.
	*/	
	public function fbGetPageViews( $pageId ) {

		$fb = $this->initFaceBook();
		$now = new DateTime('NOW');
		$end = $now->format('Y-m-d');
		$last = date_sub( $now, date_interval_create_from_date_string( '30 days' ) );
		$start = $last->format('Y-m-d');

		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
				'/'.$pageId.'/insights/page_views_total?since='.$start.'&until='.$end,
				$fb->default_access_token
			);
		} catch( FacebookExceptionsFacebookResponseException $e ) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch( FacebookExceptionsFacebookSDKException $e ) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		$graphNode = $response->getBody();      
		
		echo $graphNode;
	}


	/**
	* Gets all page views by date
	* 
	* @param pageId FB page id
	*               
	* @return graphNode: Object containing insights objects.
	*/		  
  	public function fbGetLifeTimePageLikes( $pageId ) {
		$fb = $this->initFaceBook();
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
				'/'.$pageId.'/insights/page_fans',
				$fb->default_access_token
			);
		} catch( FacebookExceptionsFacebookResponseException $e ) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch( FacebookExceptionsFacebookSDKException $e ) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		
		$graphNode = $response->getBody();      
		
		echo $graphNode;        
	} 
	  

	/**
	* Gets lifetime page likes per country
	* 
	* @param pageId FB page id
	*               
	* @return graphNode: Object containing insights objects.
	*/		  
	public function fbGetLifeTimePageLikesByCountry( $pageId ) {
		$fb = $this->initFaceBook();
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
				'/'.$pageId.'/insights/page_fans_country',
				$fb->default_access_token
			);
		} catch( FacebookExceptionsFacebookResponseException $e ) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch( FacebookExceptionsFacebookSDKException $e ) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		
		$graphNode = $response->getBody();      
		
		echo $graphNode;        
    }  


	/**
	* Gets posts for a page
	* 
	* @param pageId FB page id
	*               
	* @return graphNode: Object containing insights objects.
	*/		  
	public function fbGetPagePosts( $pageId ) {
		$fb = $this->initFaceBook();
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
				'/'.$pageId.'/posts',
				$fb->default_access_token
			);
		} catch( FacebookExceptionsFacebookResponseException $e ) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch( FacebookExceptionsFacebookSDKException $e ) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		
		$graphNode = $response->getBody();      
		
		echo $graphNode;        
	}

	  
	/**
	* Gets posts for a page
	* 
	* @param postId FB page post id
	*               
	* @return graphNode: Object containing insights objects.
	*/
	public function fbGetPostData( $postId ) {

		$fb = $this->initFaceBook();
		$allMetrics = array();

		$metrics = array(
			'post_negative_feedback_by_type',
			'post_impressions',
			'post_fan_reach',
			'post_reactions_like_total',
			'post_reactions_by_type_total',
			'post_consumptions'
		);

		foreach ($metrics as $key => $metric) {
			try {
				// Returns a `FacebookFacebookResponse` object
				$response = $fb->get(
					'/'.$postId.'/insights/'.$metric,
					$fb->default_access_token
				);
			} catch( FacebookExceptionsFacebookResponseException $e ) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			} catch( FacebookExceptionsFacebookSDKException $e ) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			}
			
			$graphNode = $response->getBody();
			
			$unwrappedNodes = json_decode($graphNode);
			
			$allMetrics[$key] = $unwrappedNodes->data[0];
		}
		
		echo json_encode( $allMetrics );
	}

	
	/**
	* Gets total page views 
	* 
	* @param pageId FB page id
	*               
	* @return graphNode: Object containing insights objects.
	*/		  
	public function fbGetTotalPageViews( $pageId ) {
		$fb = $this->initFaceBook();
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
				'/'.$pageId.'/insights/page_views_total',
				$fb->default_access_token
			);
		} catch( FacebookExceptionsFacebookResponseException $e ) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch( FacebookExceptionsFacebookSDKException $e ) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		
		$graphNode = $response->getBody();      
		
		echo $graphNode;        
	} 

	
	/**
	* Gets engaged users
	* 
	* @param pageId FB page id
	*               
	* @return graphNode: Object containing insights objects.
	*/		  
	public function fbGetEngagedUsers( $pageId ) {
		$fb = $this->initFaceBook();
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
				'/'.$pageId.'/insights/page_engaged_users',
				$fb->default_access_token
			);
		} catch( FacebookExceptionsFacebookResponseException $e ) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch( FacebookExceptionsFacebookSDKException $e ) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		
		$graphNode = $response->getBody();      
		
		echo $graphNode;        
	} 	
}