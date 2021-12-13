# RESULTATS DES ROUTES GENERALES

## home

Route: `/api/`
Méthode: **GET**

RESPONSE:

```json
{
  "currentUserHomeDetail": {
    "id": 9,
    "pseudo": "Neveu",
    "avatar": "avatar.png"
  },
  "usersHomeList": {
    "id": 11,
    "pseudo": "Fischer",
    "avatar": "avatar.png"
  },
  "travelsHomeList": {
    "id": 33,
    "title": "travel45",
    "cover": "cover.png",
    "status": 2,
    "user": {
      "id": 24,
      "pseudo": "test1",
      "avatar": "va-61a27f41e70af.png"
    },
    "categories": [
      {
        "id": 1,
        "name": "autem"
      },
      {
        "id": 3,
        "name": "quo"
      }
    ]
  }
}
```

## isLogged

Route: `/api/ilogged/`
Méthode: **GET**

RESPONSE:

```json
{
  "code": 200,
  "message": "valid"
}
```
