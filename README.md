# LocalHub

Plateforme de quartier : les voisins publient des nouvelles, prêtent des outils, demandent de l'aide, vendent ou échangent des objets, organisent des événements et des rencontres. L'interface est bilingue français/anglais et le contenu reste affiché dans la langue de son auteur. Le projet vise d'abord le marché canadien.

## Présentation

LocalHub est une application Laravel monolithique dont MySQL est l'unique source de vérité. Elle expose deux surfaces partageant la même couche de services :

- une interface Web Blade avec authentification par session, destinée aux voisins ;
- une API versionnée `/api/v1` avec jetons Sanctum, préparée pour un client Android. La version officiel Android consomme déjà les endpoints de lecture des publications ; ce contrat de lecture doit donc rester stable.

Le produit est centré sur la géographie locale : `pays` → `region` → `ville` → `quartier`, avec geohash, coordonnées et périmètres de visibilité.

## État du projet

| Phase | État | Contenu |
|---|---|---|
| Phase 0 | Terminée | Refonte du schéma, tables géo et métier, Sanctum et squelette API |
| Phase 1 | Terminée | MVP Québec : fil de quartier, bibliothèque d'outils, prêts, notifications |
| Phase 2 | À faire | Aide, marché, événements, rencontres, confiance/sécurité, recherche, union du fil |
| Phase 3 | À faire | Finition de l'interface (animations, design), sécurité, corrections, mise en ligne et plan Android |

La feuille de route détaillée se trouve dans [PLAN.md](PLAN.md).

## Pile technique

- Laravel 12 / PHP 8.2
- MySQL 8.4 (tests sur SQLite)
- Blade, Vite, session pour le Web
- Sanctum pour l'API `/api/v1`
- PHPUnit 11 pour les tests, Pint pour le formatage

## Démarrage avec Docker

Prérequis : Docker et Docker Compose.

```bash
cp .env.example .env            # si le fichier .env n'existe pas encore
docker compose up -d --build
docker compose exec -T app php artisan key:generate   # uniquement si APP_KEY est vide
docker compose exec -T app php artisan migrate:fresh --seed --force
```

L'application est ensuite disponible sur `http://localhost:8080`.

Services Compose :

| Service | Rôle | Accès |
|---|---|---|
| `app` | Application Web et API | port hôte `8080` |
| `scheduler` | Planificateur Laravel (`schedule:work`) | balayage horaire des prêts en retard |
| `db` | MySQL 8.4 | port hôte `3307` |

> Les migrations de base ayant été modifiées, utilisez `migrate:fresh --seed` sur une base existante plutôt qu'une migration incrémentale. Les données de démonstration sont jetables par décision de conception.

## Comptes de démonstration

| Courriel | Mot de passe | Rôle |
|---|---|---|
| `admin@localhub.test` | `admin1234` | Gestionnaire (valeurs `ADMIN_*` du `.env`) |
| `alex.tremblay@localhub.test` | `password` | Voisin, Montréal / Le Plateau-Mont-Royal |
| `sarah.gagnon@localhub.test` | `password` | Voisine, Montréal / Le Plateau-Mont-Royal |
| `thomas.roy@localhub.test` | `password` | Voisin, Laval (sans quartier) |
| `emma.bouchard@localhub.test` | `password` | Voisine, Montréal / Verdun |
| `maxime.cote@localhub.test` | `password` | Voisin, Québec / La Cité-Limoilou |

## Configuration

Variables notables du `.env` :

| Variable | Rôle |
|---|---|
| `APP_URL` | `http://localhost:8080` |
| `APP_LOCALE`, `APP_FALLBACK_LOCALE` | Langue de l'interface (`en` par défaut dans l'exemple, interface FR/EN disponible) |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT` | `mysql`, `db`, `3306` (port `3307` côté hôte) |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | `localhub`, `user-docker`, `docker_password` |
| `SESSION_DRIVER`, `QUEUE_CONNECTION`, `CACHE_STORE` | `database` |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ADMIN_NOM`, `ADMIN_PRENOM`, `ADMIN_VILLE` | Compte gestionnaire créé par le seeder |

## Fonctionnalités disponibles

### Fil de quartier

- Publications avec titre, description, catégorie, prix, date et médias multiples.
- Commentaires et réactions (`j_aime`, `utile`, `merci`).
- Téléversement de plusieurs images via la table polymorphe `media`.
- Filtre de périmètre (`scope` : `quartier`, `ville`, `pays`) et recherche texte (`q`).

### Bibliothèque d'outils et prêts

- Création d'outils avec catégorie, état, caution et géolocalisation.
- Cycle de vie des prêts : `demande → approuve → emprunte → retourne`, avec `refuse`, `annule`, `en_retard`, `litige` et `resoudre`.
- Contrôle des rôles (propriétaire, emprunteur, gestionnaire, système) et verrou optimiste `version`.
- Contrainte `cle_active` : un seul prêt actif par outil.
- En-tête `Idempotency-Key` sur les écritures de prêts : un retry rejoue la réponse au lieu de dupliquer l'opération.
- Messagerie de prêt via la table `message` et balayage automatique des retards.
- Notifications intégrées avec compteur de non-lus et marquage comme lu.

### Langue

L'interface est traduite en français et en anglais via `lang/fr` et `lang/en`. Le contenu créé par les voisins n'est ni traduit ni filtré par langue : il est affiché dans la langue choisie par son auteur. Aucune traduction, automatique ou à la demande, n'est prévue.

## Architecture

### Conventions de données

- MySQL est la seule source de vérité ; aucune projection dans une autre base.
- Tables au singulier en français, clés étrangères préfixées `id_*` (`id_utilisateur`, `id_ville`, `id_quartier`…).
- Une table par entité ; le fil agrège les entités visibles (union prévue en phase 2).
- Géo : `pays` → `region` → `ville` → `quartier`, avec centroïdes, `geohash` et table `categorie` optionnelle.

### Règle de visibilité

La `portee` de l'auteur (`quartier` / `ville` / `pays`) est un plafond ; le paramètre `scope` demandé par le lecteur est le rayon de recherche (par défaut `quartier`). Une publication est visible lorsque le rang de sa portée est inférieur ou égal à celui du scope et que l'identifiant géographique du niveau concerné correspond à celui du lecteur. Un utilisateur sans quartier se replie sur sa ville ; un visiteur non connecté ne voit que le contenu au niveau `pays`.

### Couche de services

Les contrôleurs Web (`App\Http\Controllers`) et API (`App\Http\Controllers\Api`) appellent les mêmes services et n'écrivent pas directement dans les modèles pour ces flux.

| Service | Responsabilité |
|---|---|
| `FeedService` | Périmétrage géographique et recherche du fil |
| `PublicationService` | CRUD des publications, commentaires, réactions, médias |
| `OutilService` | CRUD des outils, géolocalisation, médias |
| `LoanService` | Machine à états des prêts, rôles, verrou optimiste, messagerie, retards |
| `IdempotencyService` | Rejeu des requêtes identifiées par `Idempotency-Key` |
| `NotificationService` | Création et lecture des notifications |
| `LocalisationService` | Résolution coordonnées → ville/quartier et geohash |

## API v1

URL de base : `/api/v1`. Les réponses sont en JSON. Les routes d'écriture exigent un jeton Sanctum (`Authorization: Bearer <token>`) ; la lecture du fil et des outils est publique mais limitée au contenu au niveau `pays` pour les visiteurs non authentifiés.

Points d'accès principaux :

- authentification : `POST /auth/token`, `GET /me`, `POST /auth/logout`, `GET /health` ;
- publications : `GET/POST /publications`, `GET/PUT/PATCH/DELETE /publications/{id}`, commentaires et réactions ;
- outils : `GET/POST /outils`, `GET/PUT/PATCH/DELETE /outils/{id}` ;
- prêts : `GET /prets`, `POST /outils/{id}/prets`, `GET /prets/{id}`, `POST /prets/{id}/transition`, messages ;
- notifications : `GET /notifications`, marquage comme lu.

Le contrat complet, les paramètres et les codes d'erreur sont documentés dans [docs/api-v1.md](docs/api-v1.md).

## Tests et vérification

```bash
docker compose up -d --build
docker compose exec -T app php artisan migrate:fresh --seed --force
docker compose exec -T app php vendor/bin/phpunit --do-not-cache-result
docker compose exec -T app php artisan schedule:list
docker compose exec -T app php vendor/bin/pint --test
```

La suite actuelle comporte 43 tests et 164 assertions couvrant les parcours critiques : publication, périmétrage, prêts concurrents, cycle de vie des prêts et API.

## Licence

MIT, héritée du squelette Laravel.
