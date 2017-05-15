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

- Accepts: `String` - client URL
- Returns: `array` of history objects  

#### Example:

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
    },
    { ... }
  ] 
}
```

