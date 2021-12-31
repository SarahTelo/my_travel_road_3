# RESULTATS DES ROUTES TRAVEL

## listAdmin

Route: `/api/admin/travels/`
Méthode: **GET**

RESPONSE:

```json
{
  "travelList": [
    {
      "id": 10,
      "title": "Necessitatibus non sed aut numquam culpa",
      "status": 0,
      "visibility": false,
      "created_at": "2013-10-14T10:45:41+02:00",
      "updated_at": "1989-11-25T10:45:41+01:00",
      "user": {
        "id": 11
      },
      "categories": [
        {
          "id": 1,
          "name": "autem"
        }
      ]
    },
  ]
}
```

## detailAdmin

Route: `/api/admin/travel/{id}/detail/`
Méthode: **GET**

Paramètre: `id` => l'id du voyage

REPONSE:

```json
{
  "id": 57,
  "title": "travelb",
  "cover": "badge-61af68f8a7359.png",
  "description": "aefaef",
  "start_at": "2012-02-22T10:25:50+01:00",
  "end_at": "2013-05-14T00:00:00+02:00",
  "status": 0,
  "visibility": true,
  "created_at": "2021-12-07T15:00:24+01:00",
  "updated_at": null,
  "user": {
    "id": 9
  },
  "categories": [
    {
      "id": 2,
      "name": "eveniet"
    },
  ],
  "steps": [
    {
      "id": 51,
      "sequence": 1
    }
  ]
}
```

## privateList

Route: `/api/my-travels-list/`
Méthode: **GET**

REPONSE:

```json
{
  "travelList": [
    {
      "id": 31,
      "title": "travel45",
      "cover": "brunet-123456.png",
      "description": "text",
      "start_at": "2012-12-12T00:00:00+01:00",
      "end_at": "2012-12-12T00:00:00+01:00",
      "status": 2,
      "visibility": true,
      "categories": [
        {
          "id": 1,
          "name": "autem"
        },
      ]
    },
  ]
}
```

## list

Route: `/api/travels/`
Méthode: **GET**

REPONSE:

```json
{
  "travelList": [
    {
      "id": 46,
      "title": "travel",
      "cover": "brunet-123456.png",
      "description": "text",
      "status": 2,
      "user": {
        "id": 9,
        "pseudo": "Neveu",
        "avatar": "brunet-123456.png",
      },
      "categories": [
        {
          "id": 1,
          "name": "autem"
        },
      ]
    },
  ]
}
```

## detail

Route: `/api/travel/{id}/detail/`
Méthode: **GET**

Paramètre: `id` => l'id du voyage

REPONSE:

```json
{
  "id": 53,
  "title": "travelb",
  "cover": "imagetest-copie-61aa62ae3c6fa.png",
  "description": "aefaef",
  "start_at": "2010-12-22T00:00:00+01:00",
  "end_at": "2013-05-14T00:00:00+02:00",
  "status": 2,
  "visibility": false,
  "categories": [
    {
      "id": 2,
      "name": "eveniet"
    },
  ],
  "steps": [
    {
      "id": 44,
      "title": "Départ",
      "sequence": 1,
      "start_coordinate": "-46a48/eafaf5464",
      "start_at": "2010-12-22T00:00:00+01:00"
    }
  ]
}
```

## new

Route: `/api/travel/new/`
Méthode: **POST**

Content type: `multipart/form-data`

| Name                | Type            | Mandatory |
|---------------------|-----------------|-----------|
| \_ne_rien_ajouter\_ | string (hidden) | true      |
| title               | string          | true      |
| cover               | string (file)   |           |
| description         | string          |           |
| start_at            | string|datetime |           |
| end_at              | string|datetime |           |
| status              | integer         | true      |
| visibility          | boolean         | true      |
| categories          | string (array)  |           |

EXEMPLE:

```json
{
  "title": "travelb",
  "description": "aefaef",
  "start_at": "2010-12-22T00:00:00+01:00",
  "end_at": "2013-05-14",
  "status": 2,
  "visibility": false,
  "categories": [1,2,3],
  "_ne_rien_ajouter_": null,
}
```

REPONSE:

```json
{
  "code": 201,
  "message": {
    "travel_id": 53
  }
}
```

## edit

Route: `/api/travel/{id}/edit/`
Méthode: **POST**

Content type: `multipart/form-data`

Paramètre: `id` => l'id du voyage

| Name                | Type            | Mandatory |
|---------------------|-----------------|-----------|
| \_ne_rien_ajouter\_ | string (hidden) | true      |
| title               | string          |           |
| cover               | string (file)   |           |
| description         | string          |           |
| start_at            | string|datetime |           |
| end_at              | string|datetime |           |
| status              | integer         |           |
| visibility          | boolean         |           |
| categories          | string (array)  |           |
| deleteCover         | boolean         |           |

EXEMPLE:

```json
{
  "title": "travelb",
  "description": "aefaef",
  "start_at": "2010-12-22T00:00:00+01:00",
  "end_at": "2013-05-14",
  "status": 2,
  "visibility": false,
  "categories": [1,2,3],
  "deleteCover": true,
  "_ne_rien_ajouter_": null,
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

Route: `/api/travel/{id}/delete/`
Méthode: **DELETE**

Content type: `application/json`

Paramètre: `id` => l'id du voyage

| Name                | Type            | Mandatory |
|---------------------|-----------------|-----------|
| \_ne_rien_ajouter\_ | string (hidden) | true      |
| checkPassword       | string          | true      |

EXEMPLE:

```json
{
  "_ne_rien_ajouter_" : null,
  "checkPassword" : "test",
}
```

REPONSE:

```json
{
  "code": 200,
  "message": "deleted"
}
```
