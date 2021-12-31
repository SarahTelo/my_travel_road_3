# MLD

## Mocodo

[https://www.mocodo.net/](https://www.mocodo.net/)

```md
HAS, 0N step, 11 image
step: id, title, sequence, cover, description, start_coordinate, start_at, created_at, updated_at
STEP COUNTRIES, 0N step, 0N country
country: id, name, coordinate, created_at, updated_at
COME FROM, 0N country, 01 user

image: id, name, path, description, taken_at, created_at, updated_at
CONTAINS / BELONGS TO, 1N travel, 11 step
travel: id, title, cover, description, start_country, end_country, start_at, end_at, status, visibility, created_at, updated_at
TRAVEL COUNTRIES, 0N country, 12 travel
user: id, email, password, roles, firstname, lastname, pseudo, presentation, avatar, cover, created_at, updated_at

:
category: id, name, created_at, updated_at
CAN HAVE, 0N category, 0N travel
HAS / OWNED BY, 0N user, 11 travel
:
```

## Ecriture

user ( <ins>id</ins>, email, password, roles, firstname, lastname, pseudo, presentation, avatar, cover, created_at, updated_at )<br>
country ( <ins>id</ins>, name, coordinate, created_at, updated_at )<br>
travel ( <ins>id</ins>, title, cover, description, start_country, end_country, start_at, end_at, status, visibility, created_at, updated_at )<br>
step ( <ins>id</ins>, title, sequence, cover, description, start_coordinate, start_at, created_at, updated_at )<br>
image ( <ins>id</ins>, name, path, description, taken_at, created_at, updated_at )<br>
category ( <ins>id</ins>, name, created_at, updated_at )<br>
