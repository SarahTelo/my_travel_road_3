# Dictionnaire des données

## USER

|    Field     |   Type   |                 Specific feature              |     Description     |
|--------------|----------|-----------------------------------------------|---------------------|
| id           | INT      | PRIMARY KEY, UNSIGNED, AUTO_INCREMENT, UNIQUE | Identifiant         |
| email        | VARCHAR  | UNIQUE                                        | Email               |
| password     | VARCHAR  |                                               | Mot de passe        |
| roles        | LONGTEXT | DC2Type: json, DEFAULT = ['ROLE_USER']        | Rôles               |
| firstname    | VARCHAR  | NULL                                          | Prénom              |
| lastname     | VARCHAR  | NULL                                          | Nom                 |
| pseudo       | VARCHAR  |                                               | Pseudo              |
| presentation | VARCHAR  | NULL                                          | Présentation        |
| avatar       | VARCHAR  | NULL                                          | Avatar              |
| cover        | VARCHAR  | NULL                                          | Image de couverture |
| created_at   | DATETIME | IMMUTABLE DEFAULT CURRENT_TIMESTAMP           | Date de création    |
| updated_at   | DATETIME | DEFAULT CURRENT_TIMESTAMP, NULL               | Date de mise à jour |

## TRAVEL

|     Field     |   Type   |                 Specific feature              |           Description         |
|---------------|----------|-----------------------------------------------|-------------------------------|
| id            | INT      | PRIMARY KEY, UNSIGNED, AUTO_INCREMENT, UNIQUE | Identifiant                   |
| title         | VARCHAR  |                                               | Titre                         |
| cover         | VARCHAR  | NULL                                          | Image de couverture           |
| description   | VARCHAR  | NULL                                          | Description                   |
| start_at      | DATETIME | DEFAULT CURRENT_TIMESTAMP, NULL               | Date de départ                |
| end_at        | DATETIME | DEFAULT CURRENT_TIMESTAMP, NULL               | Date de fin                   |
| status        | INT      | DEFAULT = 2                                   | 0: fini, 1: en cours 2: futur |
| visibility    | BOOLEAN  | DEFAULT = 0                                   | 0: invisible, 1: visible      |
| created_at    | DATETIME | IMMUTABLE DEFAULT CURRENT_TIMESTAMP           | Date de création              |
| updated_at    | DATETIME | DEFAULT CURRENT_TIMESTAMP, NULL               | Date de mise à jour           |

A voir si je rajoute ou si c'est du ..._id

```md
| start_country | VARCHAR  |                                               | Pays de départ                |
| end_country   | VARCHAR  | NULL                                          | Pays de fin                   |
```

## STEP

|      Field       |   Type   |                 Specific feature              |       Description     |
|------------------|----------|-----------------------------------------------|-----------------------|
| id               | INT      | PRIMARY KEY, UNSIGNED, AUTO_INCREMENT, UNIQUE | Identifiant           |
| title            | VARCHAR  |                                               | Titre                 |
| sequence         | INT      | NULL                                          | Numéro d'étape        |
| cover            | VARCHAR  | NULL                                          | Image de couverture   |
| description      | VARCHAR  | NULL                                          | Description           |
| start_coordinate | VARCHAR  | NULL                                          | Coordonnées de départ |
| start_at         | DATETIME | DEFAULT CURRENT_TIMESTAMP, NULL               | Date de départ        |
| created_at       | DATETIME | IMMUTABLE DEFAULT CURRENT_TIMESTAMP           | Date de création      |
| updated_at       | DATETIME | DEFAULT CURRENT_TIMESTAMP, NULL               | Date de mise à jour   |

## IMAGE

|    Field    |   Type   |                 Specific feature              |      Description    |
|-------------|----------|-----------------------------------------------|---------------------|
| id          | INT      | PRIMARY KEY, UNSIGNED, AUTO_INCREMENT, UNIQUE | Identifiant         |
| path        | VARCHAR  | UNIQUE                                        | Chemin de l'image   |
| name        | VARCHAR  |                                               | Nom                 |
| description | VARCHAR  | NULL                                          | Description         |
| taken_at    | DATETIME | DEFAULT CURRENT_TIMESTAMP, NULL               | Date de prise       |
| created_at  | DATETIME | IMMUTABLE DEFAULT CURRENT_TIMESTAMP           | Date de création    |
| updated_at  | DATETIME | DEFAULT CURRENT_TIMESTAMP, NULL               | Date de mise à jour |

## CATEGORY

|    Field    |   Type   |                 Specific feature              |      Description    |
|-------------|----------|-----------------------------------------------|---------------------|
| id          | INT      | PRIMARY KEY, UNSIGNED, AUTO_INCREMENT, UNIQUE | Identifiant         |
| name        | VARCHAR  | UNIQUE                                        | Nom                 |
| created_at  | DATETIME | IMMUTABLE DEFAULT CURRENT_TIMESTAMP           | Date de création    |
| updated_at  | DATETIME | DEFAULT CURRENT_TIMESTAMP, NULL               | Date de mise à jour |

## COUNTRY

|    Field    |   Type   |                 Specific feature              |      Description    |
|-------------|----------|-----------------------------------------------|---------------------|
| id          | INT      | PRIMARY KEY, UNSIGNED, AUTO_INCREMENT, UNIQUE | Identifiant         |
| name        | VARCHAR  | UNIQUE                                        | Nom                 |
| coordinate  | VARCHAR  | NULL                                          | Coordonnées GPS     |
| created_at  | DATETIME | IMMUTABLE DEFAULT CURRENT_TIMESTAMP           | Date de création    |
| updated_at  | DATETIME | DEFAULT CURRENT_TIMESTAMP, NULL               | Date de mise à jour |
