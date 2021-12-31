# CAHIER DES CHARGES

## Présentation

Un site de voyage social, orienté en priorité pour les grands voyageurs.
Organiser un carnet de bord interactif de voyage à faire, en cours ou déjà fait.
Suivre les différents voyages publiés sur le site.

## Besoins

Site à destination des grands voyageurs pour les aider à tracer leurs voyages et à visualiser plus facilement leur trajet. Ce site a aussi pour vocation de faire partager leurs aventures à leur communauté via le partage de photos, texte etc… En cliquant sur chaque étape inscrite sur le parcours du voyageur, les utilisateurs peuvent accéder aux photos et autres publications du voyageur.

## Spécifications fonctionnelles

### MVP

Le site doit être "responsive": accès via tout type d'écran tels que l'ordinateur, le mobile et la tablette.

En tant qu'utilisateur je veux:

- pouvoir afficher, créer, éditer, supprimer mon compte utilisateur
- pouvoir afficher, créer, éditer, supprimer mes voyages
- pouvoir afficher, créer, éditer, supprimer mes étapes de voyages
- pouvoir afficher, créer, éditer, supprimer mes photos
- pouvoir consulter les catégories
- pouvoir consulter le voyage et les étapes des autres utilisateurs
- pouvoir afficher toutes les étapes de mon voyage sous forme de tracé sur la carte du monde
- pouvoir afficher une pop-up des informations d'une étape en cliquant sur son pin sur la map du voyage sélectionné

En tant qu'administrateur je veux:

- avoir les actions d'un utilisateur et pouvoir faire ces actions sur n'importe quel compte
- pouvoir créer, éditer, supprimer une catégorie
- pouvoir créer, éditer, supprimer un pays

### En V2 et plus

- pouvoir afficher, créer, éditer, supprimer mes commentaires sur les voyages des autre utilisateurs
- pouvoir suivre les voyages des autre utilisateurs
- pouvoir rechercher un utilisateur ou un voyage via une barre de recherche
- pouvoir inviter d'autres utilisateurs afin qu'ils puissent me suivre
- pouvoir accéder à un convertisseur de monnaies du pays en cours
- pouvoir liker les voyages des autres utilisateurs
- pouvoir utiliser un système de tchat
- pouvoir autoriser d'autres utilisateurs à modifier certains de mes voyages (voyages en groupe)
- pouvoir signaler du contenu inapproprié
- pouvoir recevoir des notifications des autres utilisateurs que je suis

## Spécifications techniques

### Technologies

#### FRONT

React: bibliothèque Javascript pour créer des interfaces utilisateurs.

Webpack: bundler (outil) qui permet de compiler tous les fichiers créés pendant la phase de développement pour les regrouper en un seul
pour la phase de production. Il fournit au navigateur le fichier javascript nécessaire à l'exécution du projet.

Axios: bibliothèque Javascript qui fonctionne comme un client HTTP. Il permet de communiquer avec des API en utilisant des requêtes.

Redux: bibliothèque opensource de gestion d’état pour application web. Il vient en complément d’une autre bibliothèque Javascript comme React dans notre projet. Il permet d’organiser l’état (affichage) de l’application.

#### BACK

Symfony est un ensemble de composants PHP ainsi qu'un framework écrit en PHP. Il fournit des fonctionnalités modulables et adaptables qui permettent de faciliter et d’accélérer le développement d'un site web.

Doctrine est un ORM (Oriental relational Mapping), permettant de créer et définir la correspondance entre les entités (tables) en base de données, et les classes du programme.

## Spécifications générales

### Public visé

- les grands voyageurs (+ voyageurs internes dans un pays en v2)
- les personnes qui veulent suivre les voyages en cours

### Navigateurs

Tous

### Parcours utilisateur potentiel

- Page d'accueil qui présente le site
- Page de création de compte / connexion
- Page d’accueil post connexion
- Navigation via les liens présent sur la page d'accueil

### Liste des documents annexes

- Wireframes : [wireframe.png](./wireframe.png)
- MCD : [MCD.png](./MCD.png)
- MPD : [MPD.png](./MPD.png)
- MLD : [MLD.md](./MLD.md)
- Dictionnaire des données : [dictionnaire.md](./dictionnaire.md)
- Format des données afin d'échanger avec l'API : voir le dossier [formats](./formats/)
- Liste des routes : [routes.md](./routes.md)

### Rôles pour l'organisation du projet

Product Owner : Jérôme (react)

Scrum Master : Sarah (symfony)

Lead dev front: Aurélie (react)

Lead dev back : Jérémy (symfony)

Git master : Jérémy (symfony) et Mélodie (react)
