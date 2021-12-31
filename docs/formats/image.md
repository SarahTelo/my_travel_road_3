# RESULTATS DES ROUTES IMAGE

## listAdmin

Route: `/admin/images/`
Méthode: **GET**

REPONSE:

```json
{
  "imageList": [
    {
      "id": 11,
      "path": "image1.png",
      "name": "Perspiciatis",
      "description": "Suscipit hic quo rerum voluptas ut. Voluptates sit et vel cupiditate vel nisi eum.",
      "taken_at": "1987-06-19T23:47:47+02:00",
      "created_at": "1990-11-09T23:47:47+01:00",
      "updated_at": "1982-10-06T23:47:47+01:00",
      "step": {
        "id": 47,
        "travel": {
            "id": 53,
            "title": "travelb"
        }
      }
    },
  ]
}
```

## list

Route: `/step/{id}/images/`
Méthode: **GET**

Paramètre: `id` => l'id de l'image

REPONSE:

```json
{
  "imageList": [
    {
      "id": 11,
      "path": "image1.png",
      "name": "Perspiciatis non",
      "description": "Suscipit hic quo rerum voluptas ut. Voluptates sit et vel cupiditate vel nisi eum.",
      "taken_at": "1987-06-19T23:47:47+02:00",
      "step": {
        "id": 47,
        "title": "Départ",
        "cover": "image1.png",
        "travel": {
          "id": 53,
          "title": "travelb",
          "user": {
            "id": 9,
            "pseudo": "Neveu",
            "avatar": "avatar.png"
          }
        }
      },
    }
  ]
}
```

## new

Route: `/step/{id}/image/new/`
Méthode: **POST**

Paramètre: `id` => l'id de l'étape

Content type: `multipart/form-data`

| Name                | Type            | Mandatory |
|---------------------|-----------------|-----------|
| \_ne_rien_ajouter\_ | string (hidden) | true      |
| path                | string (file)   | true      |
| name                | string          |           |
| description         | string          |           |
| taken_at            | string|datetime |           |

EXEMPLE:

```json
{
  "name": "Perspiciatis non",
  "description": "Suscipit hic quo rerum voluptas ut. Voluptates sit et vel cupiditate vel nisi eum.",
  "taken_at": "19-06-1987",
  "_ne_rien_ajouter_": null
}
```

REPONSE:

```json
{
  "code": 201,
  "message": {
    "image_id": 53
  }
}
```

## edit

Route: `/step/image/{id}/edit/`
Méthode: **POST**

Paramètre: `id` => l'id de l'image

Content type: `multipart/form-data`

| Name                | Type            | Mandatory |
|---------------------|-----------------|-----------|
| \_ne_rien_ajouter\_ | string (hidden) | true      |
| path                | string (file)   |           |
| name                | string          |           |
| description         | string          |           |
| taken_at            | string|datetime |           |

EXEMPLE:

```json
{
  "name": "Perspiciatis non dolores",
  "description": "Suscipit hic quo rerum voluptas ut. Voluptates sit et vel cupiditate vel nisi eum.",
  "taken_at": "19-06-1987",
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

Route: `/step/image/{id}/delete/`
Méthode: **DELETE**

Paramètre: `id` => l'id de l'image

REPONSE:

```json
{
  "code": 200,
  "message": "deleted"
}
```
