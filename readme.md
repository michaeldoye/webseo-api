# API Documentation

All repsonses return the same format - an array of objects in the following format:

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

_get_ -  `https://api.webseo.co.za/all/@client`

- Accepts: `string` - client URL (required)
- Returns: `array` of history objects  

#### Example:

Get all rankings (includes competitors, desktop & mobile rankings) for all available dates.

`get: https://api.webseo.co.za/all/webseo.co.za`

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

_get_ - `https://api.webseo.co.za/increased/@client/@device/@category/@fromDate/@toDate`

- Accepts: 
  - `string` - client URL (required)
  - `string` - device (desktop/mobile) (required)
  - `string` - keyword category name (optional)
  - `string` - from date ('Y-MM-DD') format (required)
  - `string` - to date ('Y-MM-DD') format (required)
- Returns: `array` of history objects

#### Example:
Get desktop keyword rankings that have increased between _2017-01-01_ and _2017-01-30_

`get: https://api.webseo.co.za/increased/webseo.co.za/desktop/2017-01-01/2017-01-30`

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

_get_ - `https://api.webseo.co.za/history/@client(/@device)(/@category)/@keyword(/@fromDate)(/@toDate)`

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

`get: https://api.webseo.co.za/history/webseo.co.za/seo`

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