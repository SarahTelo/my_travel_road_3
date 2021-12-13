# RESULTATS DES ROUTES STEP

## listAdmin

Route: `/api/admin/travel/{id}/steps/`
Méthode: **GET**

Paramètre: `id` => l'id du voyage

RESPONSE:

```json
{
  "travel": {
    "id": 27,
    "title": "travel45"
  },
  "stepsList": [
    {
      "id": 1,
      "title": "Asperiores aperiam id sunt et.",
      "sequence": 1,
      "cover": "imagetest-12.png",
      "start_coordinate": "-80.584594\/-22.79805",
      "start_at": "1986-02-17T16:50:42+01:00",
      "created_at": "2018-05-18T16:50:42+02:00",
      "updated_at": "1978-07-22T16:50:42+01:00"
    },
  ]
}
```

## detailAdmin

Route: `/api/admin/travel/step/{id}/detail/`
Méthode: **GET**

Paramètre: `id` => l'id de l'étape

RESPONSE:

```json
{
  "travel": {
    "id": 27,
    "title": "travel45",
    "cover": "imagetest-12.png",
    "status": 2
  },
  "step": {
    "id": 7,
    "title": "etape2",
    "sequence": 3,
    "cover": "imagetest-12.png",
    "description": "Lorem ipsum",
    "start_coordinate": "-46a48\/eafaf5464",
    "start_at": "2021-12-12T00:00:00+01:00",
    "created_at": "2021-11-22T01:53:01+01:00",
    "updated_at": "2021-11-24T22:30:06+01:00"
  },
}
```

## list

Route: `/api/travel/{id}/steps/`
Méthode: **GET**

Paramètre: `id` => l'id du voyage

RESPONSE:

```json
{
  "travel": {
    "id": 41,
    "title": "travel",
    "cover": "imagetest-12.png",
    "status": 2,
    "visibility": false
  },
  "stepsList": [
    {
      "id": 20,
      "title": "Départ",
      "sequence": 1,
      "cover": "imagetest-12.png",
      "start_coordinate": "-46a48\/eafaf5464",
      "start_at": "2021-12-12T00:00:00+01:00"
    },
    {
      "id": 24,
      "title": "etape",
      "sequence": 3,
      "cover": "imagetest-12.png",
      "start_coordinate": "-46a48\/eafaf5464",
      "start_at": "2021-12-12T00:00:00+01:00"
    }
  ]
}
```

## detail

Route: `/api/travel/step/{id}/detail/`
Méthode: **GET**

Paramètre: `id` => l'id de l'étape

RESPONSE:

```json
{
  "travel": {
    "id": 27,
    "title": "travel45",
    "cover": "imagetest-12.png",
    "status": 2
  },
  "step": {
    "id": 7,
    "title": "etape2",
    "sequence": 3,
    "cover": "imagetest-12.png",
    "description": "Lorem ipsum",
    "start_coordinate": "-46a48\/eafaf5464",
    "start_at": "2021-12-12T00:00:00+01:00",
    "images": [
      {
        "id": 11,
        "path": "image1.png",
        "name": "Perspiciatis non dolores pariatur ratione magni vel cupiditate debitis.",
        "description": "Suscipit hic quo rerum voluptas ut. Voluptates sit et vel cupiditate vel",
        "taken_at": "1987-06-19T23:47:47+02:00"
      },
    ]
  },
}
```

## new

Route: `/api/travel/{id}/step/new/`
Méthode: **POST**

Paramètre: `id` => l'id du voyage

Content type: `multipart/form-data`

| Name              | Type            | Mandatory |
|-------------------|-----------------|-----------|
| _ne_rien_ajouter_ | hidden          | true      |
| title             | string          | true      |
| cover             | string (file)   |           |
| description       | string          |           |
| start_coordinate  | string          |           |
| start_at          | string|datetime |           |

EXEMPLE:

```json
{
  "title": "etape2",
  "sequence": 3,
  "description": "Lorem ipsum",
  "start_coordinate": "-46a48/eafaf5464",
  "start_at": "05-06-2016",
  "_ne_rien_ajouter_": null
}
```

REPONSE:

```json
{
  "code": 201,
  "message": {
    "step_id": 53
  }
}
```

## edit

Route: `/api/travel/step/{id}/edit/`
Méthode: **POST**

Paramètre: `id` => l'id de l'étape

Content type: `multipart/form-data`

| Name              | Type            | Mandatory |
|-------------------|-----------------|-----------|
| _ne_rien_ajouter_ | hidden          | true      |
| title             | string          |           |
| cover             | string (file)   |           |
| description       | string          |           |
| start_coordinate  | string          |           |
| start_at          | string|datetime |           |
| deleteCover       | boolean         |           |

EXEMPLE:

```json
{
  "title": "etape2",
  "sequence": 3,
  "description": "Lorem ipsum",
  "start_coordinate": "-46a48/eafaf5464",
  "start_at": "05-06-2016",
  "deleteCover" : true,
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

Route: `/api/travel/step/{id}/delete/`
Méthode: **DELETE**

Paramètre: `id` => l'id de l'étape

REPONSE:

```json
{
  "code": 200,
  "message": "deleted"
}
```
