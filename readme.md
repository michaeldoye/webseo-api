# API Documentation

All repsonses return the same format: - an array of objects in the following format:

- `id`: row id
- `keyword`: the ranking keyword
- `category`: the keyword category, `undefined` if not provided
- `search_engine`: device search engine (desktop/mobile)
- `check_date`: the date this history object was checked
- `ranking_change`: change in position since last check
- `current_position`: current ranking position
- `url_found`: the ranking URL found for this histroy object
- `competitor`: the name of the competitor else `null` for own rankings
- `search_volume`: the ammount of monthly searches for this keyword

## Endpoints

### getAll( $url )

_get_ -  `https://webseo.co.za/api/all/@client`

- Accepts: `string` - client URL (required)
- Returns: `array` of history objects  

#### Example:

Get all rankings (includes competitors, desktop & mobile rankings) for all available dates.

`get: https://webseo.co.za/api/all/webseo.co.za`

```json
{
  "response": [
    {
      "id": "6097",
      "keyword": "ranking keyword",
      "category": "somecategory",
      "search_engine": "Google.co.za",
      "check_date": "2017-05-06",
      "ranking_change": "+ 9",
      "current_position": "16",
      "url_found": "https://webseo.co.za/someuri",
      "competitor": null,
      "search_volume": "260"
    }
  ] 
}
```
<hr>

### getIncreased( $url, $device, $category, $fromDate, $toDate )

_get_ - `https://webseo.co.za/api/increased/@client/@device/@category/@fromDate/@toDate`

- Accepts: 
  - `string` - client URL (required)
  - `string` - device (desktop/mobile) (required)
  - `string` - keyword category name (optional)
  - `string` - from date ('Y-MM-DD') format (required)
  - `string` - to date ('Y-MM-DD') format (required)
- Returns: `array` of history objects

#### Example:
Get desktop keyword rankings that have increased between _2017-01-01_ and _2017-01-30_

`get: https://webseo.co.za/api/increased/webseo.co.za/desktop/2017-01-01/2017-01-30`

```json
{
  "response": [
    {
      "id": "6097",
      "keyword": "ranking keyword",
      "category": "somecategory",
      "search_engine": "Google.co.za",
      "check_date": "2017-05-06",
      "ranking_change": "+ 9",
      "current_position": "16",
      "url_found": "https://webseo.co.za/someuri",
      "competitor": null,
      "search_volume": "260"
    }
  ] 
}
```

<hr>

### getKeywordHistory( $url, $device, $category, $keyword, $fromDate, $toDate )

_get_ - `https://webseo.co.za/api/history/@client(/@device)(/@category)/@keyword(/@fromDate)(/@toDate)`

- Accepts: 
  - `string` - client URL (required)
  - `string` - device (desktop/mobile) (optional) - default: desktop
  - `string` - keyword category name (optional)
  - `string` - the keyword to get history for (required)  
  - `string` - from date ('Y-m-d') format (optional) - default: all history
  - `string` - to date ('Y-m-d') format (optional) - default: all history
- Returns: `array` of history objects

#### Example:
Get keyword ranking history for keyword _'seo'_

`get: https://webseo.co.za/api/history/webseo.co.za/seo`

```json
{
  "response": [
    {
      "id": "6097",
      "keyword": "seo",
      "category": "somecategory",
      "search_engine": "Google.co.za",
      "check_date": "2017-05-06",
      "ranking_change": "+ 9",
      "current_position": "7",
      "url_found": "https://webseo.co.za",
      "competitor": null,
      "search_volume": "2400"
    }
  ] 
}
```

<hr>

### getTableData( $url, $device, $category )

_get_ - `https://webseo.co.za/api/tabledata/@client(/@device)(/@category)`

- Accepts: 
  - `string` - client URL (required)
  - `string` - device (desktop/mobile) (optional) - default: desktop
  - `string` - keyword category name (optional)
- Returns: `array` of objects

#### Example:
Get keyword ranking history for keyword _'seo'_

`get: https://webseo.co.za/api/tabledata/webseo.co.za`

```json
{
  "response": [
    {
      "keyword": "somekeyword",
      "volume": 500,
      "myrankings": {
        "change": "+ 10",
        "currentPosition": 1,
        "name": "competitor_name"
      },
      "competitor1": {
        "change": "- 5",
        "currentPosition": 10,
        "name": "competitor_name"
      },
      "competitor2": {
        "change": "stays out",
        "currentPosition": 50,
        "name": "competitor_name"
      },
      "competitor3": {
        "change": "entered",
        "currentPosition": 5,
        "name": "competitor_name"
      }                    
    }
  ] 
}
```

<hr>

### All Endpoints
```PHP
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
```