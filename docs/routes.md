# LISTE DES ROUTES

## GENERAL

| URL           | HTTP | Controller         | Method     | Content                                         |
|---------------|------|--------------------|------------|-------------------------------------------------|
| /api/         | GET  | MainController     | home       | Page d'accueil                                  |
| /api/ilogged/ | POST | SecurityController | isLogged   | Vérification si l'utilisateur est déjà connecté |

## USER

| URL                           | HTTP   | Controller     | Method       | Content                      |
|-------------------------------|--------|----------------|--------------|------------------------------|
| /api/admin/users/             | GET    | UserController | listAdmin    | Liste Admin                  |
| /api/users/                   | GET    | UserController | list         | Liste                        |
| /api/user/{id}/detail/        | GET    | UserController | detail       | Détails                      |
| /api/user/profile/            | GET    | UserController | profile      | Profil                       |
| /api/user/new/                | POST   | UserController | new          | Ajout                        |
| /api/user/{id}/edit/          | POST   | UserController | edit         | Modification                 |
| /api/user/{id}/edit/password/ | POST   | UserController | editPassword | Modification du mot de passe |
| /api/user/{id}/delete/        | DELETE | UserController | delete       | Suppression                  |

## TRAVEL

| URL                            | HTTP   | Controller       | Method      | Content                       |
|--------------------------------|--------|------------------|-------------|-------------------------------|
| /api/admin/travels/            | GET    | TravelController | listAdmin   | Liste Admin                   |
| /api/admin/travel/{id}/detail/ | GET    | TravelController | detailAdmin | Détails Admin                 |
| /api/my-travels-list/          | GET    | TravelController | privateList | Liste de l'utilisateur actuel |
| /api/travels/                  | GET    | TravelController | list        | Liste                         |
| /api/travel/{id}/detail/       | GET    | TravelController | detail      | Détails                       |
| /api/travel/new/               | POST   | TravelController | new         | Ajout                         |
| /api/travel/{id}/edit/         | POST   | TravelController | edit        | Modification                  |
| /api/travel/{id}/delete/       | DELETE | TravelController | delete      | Suppression                   |

## STEP

| URL                                 | HTTP   | Controller     | Method      | Content       |
|-------------------------------------|--------|----------------|-------------|---------------|
| /api/admin/travel/{id}/steps/       | GET    | StepController | listAdmin   | Liste Admin   |
| /api/admin/travel/step/{id}/detail/ | GET    | StepController | detailAdmin | Détails Admin |
| /api/travel/{id}/steps/             | GET    | StepController | list        | Liste         |
| /api/travel/step/{id}/detail/       | GET    | StepController | detail      | Détails       |
| /api/travel/{id}/step/new/          | POST   | StepController | new         | Ajout         |
| /api/travel/step/{id}/edit/         | POST   | StepController | edit        | Modification  |
| /api/travel/step/{id}/delete/       | DELETE | StepController | delete      | Suppression   |

## IMAGE

| URL                      | HTTP   | Controller      | Method      | Content      |
|--------------------------|--------|-----------------|-------------|--------------|
| /admin/images/           | GET    | ImageController | listAdmin   | Liste Admin  |
| /step/{id}/images/       | GET    | ImageController | list        | Liste        |
| /step/{id}/image/new/    | POST   | ImageController | new         | Ajout        |
| /step/image/{id}/edit/   | POST   | ImageController | edit        | Modification |
| /step/image/{id}/delete/ | DELETE | ImageController | delete      | Suppression  |

## COUNTRY

| URL                             | HTTP   | Controller        | Method      | Content       |
|---------------------------------|--------|-------------------|-------------|---------------|
| /api/countries/                 | GET    | CountryController | list        | Liste         |
| /api/country/{id}/detail/       | GET    | CountryController | detail      | Détails       |
| /api/admin/countries/           | GET    | CountryController | listAdmin   | Liste Admin   |
| /api/admin/country/{id}/detail/ | GET    | CountryController | detailAdmin | Détails Admin |
| /api/admin/country/new/         | POST   | CountryController | new         | Ajout         |
| /api/admin/country/{id}/edit/   | POST   | CountryController | edit        | Modification  |
| /api/admin/country/{id}/delete/ | DELETE | CountryController | delete      | Suppression   |

## CATEGORY

| URL                              | HTTP   | Controller         | Method      | Content       |
|----------------------------------|--------|--------------------|-------------|---------------|
| /api/categories/                 | GET    | CategoryController | list        | Liste         |
| /api/category/{id}/detail/       | GET    | CategoryController | detail      | Détails       |
| /api/admin/categories/           | GET    | CategoryController | listAdmin   | Liste Admin   |
| /api/admin/category/{id}/detail/ | GET    | CategoryController | detailAdmin | Détails Admin |
| /api/admin/category/new/         | POST   | CategoryController | new         | Ajout         |
| /api/admin/category/{id}/edit/   | POST   | CategoryController | edit        | Modification  |
| /api/admin/category/{id}/delete/ | DELETE | CategoryController | delete      | Suppression   |
