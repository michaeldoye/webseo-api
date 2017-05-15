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

- Accepts: `string` - client URL
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
  - `string` - client URL
  - `string` - device (desktop/mobile)
  - `string` - keyword category name
  - `string` - from date ('Y-m-d') format
  - `string` - to date ('Y-m-d') format
- Returns: `array` of history objects

#### Example:
Get desktop keyword rankings that have increased for "category" between _2017-01-01_ and _2017-01-30_

`get: https://api.webseo.co.za/increased/webseo.co.za/desktop/category/2017-01-01/2017-01-30`

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