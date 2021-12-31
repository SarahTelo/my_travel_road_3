# RESULTATS DES ROUTES CATEGORY

## list

Route: `/api/categories/`
Méthode: **GET**

REPONSE:

```json
{
  "categoryList": [
    {
      "id": 1,
      "name": "autem"
    },
  ]
}
```

## detail

Route: `/api/category/{id}/detail/`
Méthode: **GET**

Paramètre: `id` => l'id de la catégorie

REPONSE:

```json
{
  "categroyDetail": [
    {
      "id": 1,
      "name": "autem",
      "travels": [
        {
          "id": 14,
          "title": "Sed porro iste illo perspiciatis consequuntur saepe beatae.",
          "cover": "image.png",
          "user": {
            "id": 11,
            "pseudo": "Fischer",
            "avatar": "avatar.png"
          }
        },
      ]
    }
  ]
}
```

## listAdmin

Route: `/api/admin/categories/`
Méthode: **GET**

REPONSE:

```json
{
  "categoryList": [
    {
      "id": 1,
      "name": "autem",
      "created_at": "1982-06-18T23:41:29+02:00",
      "updated_at": "2009-04-05T23:41:29+02:00"
    },
  ]
}
```

## detailAdmin

Route: `/api/admin/category/{id}/detail/`
Méthode: **GET**

Paramètre: `id` => l'id de la catégorie

REPONSE:

```json
{
  "categroyDetail": {
    "id": 1,
    "name": "autem",
    "created_at": "1982-06-18T23:41:29+02:00",
    "updated_at": "2009-04-05T23:41:29+02:00",
    "travels": [
      {
        "id": 10,
        "title": "Necessitatibus non sed aut numquam culpa.",
        "user": {
          "id": 11
        }
      },
    ]
  }
}
```

## new

Route: `/api/admin/category/new/`
Méthode: **POST**

Content type: `multipart/form-data`

| Name                | Type            | Mandatory |
|---------------------|-----------------|-----------|
| \_ne_rien_ajouter\_ | string (hidden) | true      |
| name                | string          | true      |

EXEMPLE:

```json
{
  "name": "category",
  "taken_at": "19-06-1987",
}
```

REPONSE:

```json
{
  "code": 201,
  "message": {
    "category_id": 53
  }
}
```

## edit

Route: `/api/admin/category/{id}/edit/`
Méthode: **POST**

Paramètre: `id` => l'id de la catégorie

Content type: `multipart/form-data`

| Name                | Type            | Mandatory |
|---------------------|-----------------|-----------|
| \_ne_rien_ajouter\_ | string (hidden) | true      |
| name                | string          | true      |

EXEMPLE:

```json
{
  "name": "category",
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

Route: `/api/admin/category/{id}/delete/`
Méthode: **DELETE**

REPONSE:

```json
{
  "code": 200,
  "message": "deleted"
}
```
