# RESULTATS DES ROUTES USER

## listAdmin

Route: `/api/admin/users/`
Méthode: **GET**

REPONSE:

```json
{
  "userList": [
    {
      "id": 8,
      "email": "admin@admin.com",
      "roles": [
        "ROLE_ADMIN",
        "ROLE_USER"
      ],
      "firstname": "Brunet",
      "lastname": "Brunet",
      "pseudo": "Brunet",
      "presentation": "Brunet",
      "avatar": "brunet-123456.png",
      "cover": "brunet-123456.png",
      "created_at": "2009-12-31T00:00:00+01:00",
      "updated_at": "2009-12-31T00:00:00+01:00",
      "country": {
        "id": 1,
        "name": "France"
      }
    },
  ]
}
```

## list

Route: `/api/users/`
Méthode: **GET**

REPONSE:

```json
{
  "userList": [
    {
      "id": 8,
      "pseudo": "Brunet",
      "presentation": "Brunet",
      "avatar": "brunet-123456.png",
      "country": {
        "id": 1,
        "name": "France"
      }
    },
  ]
}
```

## detail

Route: `/api/user/{id}/detail/`
Méthode: **GET**

Paramètre: `id` = l'id de l'utilisateur

REPONSE:

```json
{
  "userDetail": {
    "id": 24,
    "pseudo": "testtt1",
    "presentation": "text",
    "avatar": "va-61a27f41e70af.png",
    "cover": "brunet-123456.png",
    "country": {
      "id": 1,
      "name": "France"
    }
  },
  "travelsList": [
    {
      "id": 31,
      "title": "travel45",
      "cover": "brunet-123456.png"
    },
  ]
}
```

## profile

Route: `/api/user/profile/`
Méthode: **GET**

REPONSE:

```json
{
  "id": 9,
  "email": "test@test.com",
  "firstname": "Neveu",
  "lastname": "Neveu",
  "pseudo": "Neveu",
  "presentation": "text",
  "avatar": "brunet-123456.png",
  "cover": "brunet-123456.png",
  "country": {
    "id": 1,
    "name": "France"
  }
}
```

## new

Route: `/api/user/new/`
Méthode: **POST**

Content type: `multipart/form-data`

| Name                | Type            | Mandatory |
|---------------------|-----------------|-----------|
| \_ne_rien_ajouter\_ | string (hidden) | true      |
| email               | string          | true      |
| password            | string          | true      |
| roles               | string (array)  |           |
| firstname           | string          |           |
| lastname            | string          |           |
| pseudo              | string          |           |
| presentation        | string          |           |
| avatar              | string (file)   |           |
| cover               | string (file)   |           |
| country             | integer         |           |

EXEMPLE:

```json
{
  "email": "test@test.com",
  "password": "mdp",
  "roles": ['ROLE_USER'],
  "firstname": "Neveu",
  "lastname": "Neveu",
  "pseudo": "Neveu",
  "presentation": "Neveu",
  "country": 2,
  "_ne_rien_ajouter_": null,
}
```

REPONSE:

```json
{
  "code": 201,
  "message": {
    "user_id": 53
  }
}
```

## edit

Route: `/api/user/{id}/edit/`
Méthode: **POST**

Content type: `multipart/form-data`

Paramètre: `id` = l'id de l'utilisateur

| Name                | Type            | Mandatory |
|---------------------|-----------------|-----------|
| \_ne_rien_ajouter\_ | string (hidden) | true      |
| checkPassword       | string          | true      |
| email               | string          |           |
| roles               | string (array)  |           |
| firstname           | string          |           |
| lastname            | string          |           |
| pseudo              | string          |           |
| presentation        | string          |           |
| avatar              | string (file)   |           |
| cover               | string (file)   |           |
| country             | integer         |           |
| deleteAvatar        | boolean         |           |
| deleteCover         | boolean         |           |

EXEMPLE:

```json
{
  "email": "test@test.com",
  "roles": ['ROLE_USER'],
  "firstname": "Neveu",
  "lastname": "Neveu",
  "pseudo": "Neveu",
  "presentation": "Neveu",
  "country": 12,
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

## editPassword

Route: `/api/user/{id}/edit/password/`
Méthode: **POST**

Content type: `multipart/form-data`

Paramètre: `id` = l'id de l'utilisateur

| Name                | Type            | Mandatory |
|---------------------|-----------------|-----------|
| oldPassword         | string          | true      |
| password            | string          | true      |
| \_ne_rien_ajouter\_ | string (hidden) | true      |

EXEMPLE:

```json
{
  "password": "mdp",
  "oldPassword": "mdp2",
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

Route: `/api/user/{id}/delete/`
Méthode: **DELETE**

Content type: `application/json`

Paramètre: `id` = l'id de l'utilisateur

| Name                | Type            | Mandatory |
|---------------------|-----------------|-----------|
| \_ne_rien_ajouter\_ | string (hidden) | true      |
| checkPassword       | string          | true      |

EXEMPLE:

```json
{
  "checkPassword" : "test",
  "_ne_rien_ajouter_" : null,
}
```

REPONSE:

```json
{
  "code": 200,
  "message": "deleted"
}
```
