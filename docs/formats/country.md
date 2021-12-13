# RESULTATS DES ROUTES COUNTRY

## list

Route: `/api/countries/`
Méthode: **GET**

REPONSE:

```json
{
  "countryList": [
    {
      "id": 1,
      "name": "autem",
      "coordinate": "77,53545\/90,33548"
    },
  ]
}
```

## detail

Route: `/api/country/{id}/detail/`
Méthode: **GET**

Paramètre: `id` => l'id du pays

REPONSE:

```json
{
  "id": 3,
  "name": "Djibouti",
  "coordinate": "8.63964\/-139.642673",
  "users": [
    {
      "id": 18,
      "pseudo": "test",
      "avatar": "avatar.png"
    },
  ]
}
```

## listAdmin

Route: `/api/admin/countries/`
Méthode: **GET**

REPONSE:

```json
{
  "countryList": [
    {
      "id": 1,
      "name": "autem",
      "coordinate": "-88.876243\/-61.858128",
      "created_at": "1982-06-18T23:41:29+02:00",
      "updated_at": "2009-04-05T23:41:29+02:00"
    },
  ]
}
```

## detailAdmin

Route: `/api/admin/country/{id}/detail/`
Méthode: **GET**

Paramètre: `id` => l'id du pays

REPONSE:

```json
{
  "id": 3,
  "name": "Djibouti",
  "coordinate": "8.63964\/-139.642673",
  "users": [
    {
      "id": 18,
      "pseudo": "test"
    }
  ]
}
```

## new

Route: `/api/admin/country/new/`
Méthode: **POST**

Content type: `multipart/form-data`

| Name              | Type            | Mandatory |
|-------------------|-----------------|-----------|
| _ne_rien_ajouter_ | hidden          | true      |
| name              | string          | true      |
| coordinate        | string          | true      |

EXEMPLE:

```json
{
  "name": "country",
  "coordinate": "8.63964/-139.642673",
  "taken_at": "19-06-1987",
}
```

REPONSE:

```json
{
  "code": 201,
  "message": {
    "country_id": 53
  }
```

## edit

Route: `/api/admin/country/{id}/edit/`
Méthode: **POST**

Paramètre: `id` => l'id du pays

Content type: `multipart/form-data`

| Name              | Type            | Mandatory |
|-------------------|-----------------|-----------|
| _ne_rien_ajouter_ | hidden          | true      |
| name              | string          |           |
| coordinate        | string          |           |

EXEMPLE:

```json
{
  "name": "country",
  "coordinate": "8.63964/-139.642673",
  "_ne_rien_ajouter_": null
}
```

REPONSE:

```json
{
  "code": 200,
  "message": "updated"
}
```

## delete

Route: `/api/admin/country/{id}/delete/`
Méthode: **DELETE**

REPONSE:

```json
{
  "code": 200,
  "message": "deleted"
}
```
