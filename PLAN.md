# LocalHub — Feuille de route de développement et plan d'implémentation

> Document vivant. À mettre à jour au fur et à mesure des phases.
> Dernière mise à jour : **2026-11-27**
> Statut : **Phase 0 ✅ · Phase 1 ✅ (MVP livrable) · Phase 2 ✅ · Phase 3 ⏳**

---

## 1. Vision

Transformer l'application Laravel existante en une plateforme propre, centrée sur le quartier, avec MySQL comme unique source de vérité, une interface Web Blade pour les voisins et une surface `/api/v1` versionnée préparée pour un futur client Android (pas une priorité actuelle).

Le produit converge vers six types de fonctionnalités : fil de quartier, prêt d'outils, demandes d'aide, marché, événements et rencontres sociales — ainsi que des outils de confiance/sécurité et des notifications intégrées.

---

## 2. Décisions verrouillées

| Décision | Choix |
|---|---|
| Stack | Laravel 12 / PHP 8.2 / MySQL 8.4 |
| Source de vérité | MySQL uniquement. Pas de Firebase, ni de second stockage accessible en écriture |
| Web | Blade + authentification par session (existant) |
| API | `routes/api.php`, préfixe `/api/v1`, jetons Sanctum — futur Android uniquement |
| Nommage | **Tables au singulier en français**, clés étrangères `id_*` (`id_utilisateur`, `id_ville`, `id_quartier`…) |
| Décomposition | Une table par entité ; le fil agrège les entités visibles |
| Géo | `geohash` + tables de référence `ville`/`quartier` + coordonnées |
| Données héritées | Jetables : `migrate:fresh --seed` plutôt qu'une migration de données |
| Marché cible | Le Canada d'abord : aucune expansion internationale tant que le produit n'a pas réussi sur le marché canadien |
| Langue du contenu | L'auteur écrit dans la langue de son choix ; aucun champ `lang`, aucun filtre `lang`, aucune traduction automatique. Une traduction **à la demande** (bouton sous chaque publication) est proposée via un service pluggable ; le résultat est mis en cache dans le navigateur, jamais en base |
| Compatibilité Android | Android consomme déjà les endpoints de **lecture des publications** de `/api/v1` : le paramètre `lang` reste accepté (mais ignoré) pour ne pas casser les requêtes existantes. La clé de réponse `lang` a été retirée à la demande explicite de l'utilisateur. Toutes les autres surfaces sont modifiables |

> Écart par rapport à la feuille de route initiale accepté par l'utilisateur : la feuille de route
> écrivait `villes`/`quartiers` et `ville_id`/`quartier_id` ; la décision de nommage verrouillée
> et le schéma existant utilisent des tables au singulier et des clés étrangères `id_*`.

---

## 3. Architecture actuelle (après les phases 0 et 1)

### Modèle de données

**Géo / référentiel (terminé) :** `pays` → `region` → `ville` → `quartier` avec `nom_fr`/`nom_en`, centroïdes, `geohash`, `actif`. `GeoSeeder` amorce Canada → Québec et les grandes municipalités. `LocalisationService` + `App\Support\Geohash` résolvent les coordonnées en ville/quartier.

**Identité (terminé) :** `utilisateur` (étendue avec `locale`, `id_ville`, `id_quartier`, `latitude`, `longitude`, `geohash`, `precision_pref`, `fiabilite`), `parametre_utilisateur`, `personal_access_tokens` (Sanctum).

**Contenu (phase 2 opérationnelle) :** `publication`, `commentaire`, `reaction`, `media`, `outil`, `pret`, `message`, `demande_aide`, `offre_aide`, `evenement`, `inscription_evenement`, `annonce`, `offre_annonce`, `rencontre`, `rencontre_membre`.

**Confiance, messagerie et géo (phase 2) :** `signalement`, `moderation_action`, `blocage`, `avis_pret`, `conversation`, `conversation_membre`, `suivi`, et la colonne `quartier.polygon` (GeoJSON).

**Opérations (terminé) :** `notification` (table au singulier avec morph `notifiable_*` et `lu_at`), `idempotence`.

**Protection contre les accès concurrents sur `pret` :** une colonne unique nullable `cle_active` (définie à `outil:<id_outil>` pour les prêts actifs, effacée aux états terminaux) ainsi qu'un verrou optimiste `version`. C'est l'équivalent portable de l'« index partiel » de la feuille de route — MySQL 8.4 ne prend pas en charge les index partiels en SQL, et les tests s'exécutent sur SQLite.

> Le fil d'accueil (`/`) est désormais **unifié** : il agrège les publications, outils,
> demandes d'aide, annonces, événements et rencontres visibles, via une union SQL paginée.
> Chaque entité conserve sa propre liste filtrée par périmètre et recherche.

### Couche de services (partagée entre le Web et l'API)

| Service | Responsabilité |
|---|---|
| `FeedService` | Périmétrage géo, recherche, exclusion des bloqués, fil unifié (`filUnifie`) et enrichissement auteur/catégorie |
| `RechercheService` | Recherche plein texte MySQL (`MATCH … AGAINST`) avec repli `LIKE` sous SQLite |
| `ContenuService` | Base partagée des entités de la phase 2 (pagination géo + recherche + blocage) |
| `PublicationService` | CRUD des publications, commentaires, réactions, médias, notifications aux suiveurs |
| `OutilService` | CRUD des outils, géolocalisation, médias |
| `LoanService` | Machine à états des prêts, contrôles d'acteur, verrou optimiste, chat, balayage des retards |
| `DemandeAideService` | Demandes d'aide et offres (`urgent`, `date_limite`) |
| `AnnonceService` | Annonces et offres, chat, règle `prix = 0` ⇒ gratuit |
| `EvenementService` | Événements (UTC + `timezone_id` IANA), RSVP et capacité |
| `RencontreService` | Rencontres sociales (intérêts, capacité, membres) |
| `SignalementService` | Signalements, file de modération, audit `moderation_action` |
| `BlocageService` | Blocage/déblocage et exclusion des contenus bloqués |
| `AvisPretService` + `FiabiliteService` | Avis après prêt et recalcul du score `fiabilite` |
| `ConversationService` | Messagerie privée 1-à-1 et notifications |
| `SuiviService` | Suivi de publication et notifications aux suiveurs |
| `IdempotencyService` | Rejeu des requêtes avec `Idempotency-Key` |
| `NotificationService` | Lignes de notifications intégrées |
| `LocalisationService` | Coordonnées → ville/quartier + geohash |

Les contrôleurs Web et les contrôleurs `App\Http\Controllers\Api\*` appellent tous ces services ; aucun n'écrit directement dans les modèles pour ces flux.

### Règle de visibilité

La `portee` de l'auteur (`quartier` / `ville` / `pays`) est un **plafond** ; le paramètre `scope` demandé par le lecteur est le **rayon de recherche** (par défaut `quartier`). Une publication est visible lorsque `rang(portee) <= rang(scope)` et que l'identifiant géographique au niveau concerné correspond à celui du lecteur. Les utilisateurs sans quartier se replient sur leur ville ; les invités ne voient que le contenu au niveau `pays`.

### Machine à états des prêts

```
demande   → approuver | refuser | annuler
approuve  → emprunter | annuler
emprunte  → retourner | ouvrir_litige
en_retard → retourner | ouvrir_litige
litige    → resoudre
```

`StatutPret` détient la table de transition ; `LoanService` vérifie le rôle de l'acteur (`proprietaire`, `emprunteur`, `gestionnaire`, `system`) et applique un `UPDATE … WHERE version = ?` conditionnel. Les écritures sont encapsulées dans `DB::transaction`, et `cle_active` garantit un seul prêt actif par outil.

---

## 4. Phase 0 — Refonte de la base de données ✅

**Objectif :** remplacer le schéma à 4 tables par le modèle cible sans casser l'application Web en cours d'exécution.

- [x] Tables géo + amorçage : `pays`, `region`, `ville`, `quartier`, Canada → Québec, grandes municipalités, prise en charge du geohash *(déjà présentes dans le commit `df10258`)*
- [x] Extension de `utilisateur` : `locale`, `id_ville`, `id_quartier`, `latitude`, `longitude`, `geohash`, `precision_pref`, `fiabilite` ; suppression du champ `ville` en texte libre ; l'inscription résout une vraie `ville`/`quartier`
- [x] Création des tables de contenu : `publication`, `commentaire`, `reaction`, `media`, `demande_aide`, `offre_aide`, `evenement`, `inscription_evenement`, `annonce`, `offre_annonce`, `outil`, `pret`, `rencontre`, `rencontre_membre`, `message`
- [x] Reconversion de `categorie` en taxonomie optionnelle (`slug`, `actif` ; meubles, outils, vêtements, services, électronique, livres, autres amorcés)
- [x*] Migration des données : **adaptée** — les données sources étaient jetables, donc aucun découpage ligne par ligne ni mapping `reservation → pret` n'a été effectué. `migrate:fresh --seed` charge des données de démonstration propres ; la table, le modèle et la factory `reservation` ont été supprimés
- [x] Installation de Sanctum + squelette `/api/v1` : health, token, me, logout ; configuration CORS
- [x] Bascule : `migrate:fresh --seed`, déploiement des modèles/routes, vérification du fil Web et de l'authentification

**Critères de sortie**

- [x] Toutes les tables cibles existent avec FK + index, y compris la contrainte d'un seul prêt actif sur `pret`
- [~] Reconciliation des publications/réservations existantes dans les nouvelles tables — **non applicable** : données intentionnellement supprimées par décision
- [x] Le fil Web et l'authentification fonctionnent sur le nouveau schéma
- [x] `GET /api/v1/health` et `POST /api/v1/auth/token` répondent

---

## 5. Phase 1 — MVP Québec ✅

**Objectif :** le plus petit produit de quartier livrable en FR/EN.

### Tranche 1 — Fil de quartier

- [x] DB : `publication`, `commentaire`, `reaction`, `media`
- [x] Modèles + relations + casts + factories
- [x] Périmétrage `FeedService` (quartier → ville → pays, `lang`, `q`)
- [x] Web : vues Blade `index` / `show` / `create` / `edit`, suppression, formulaire de commentaire, boutons de réaction, téléversement multi-images
- [x] API : liste, lecture, création, mise à jour, suppression, commentaire, réaction, suppression de média
- [x] Créer/modifier/supprimer des publications, commenter, réagir, téléverser plusieurs images
- [x] Filtre de langue (`lang`) et interface bilingue via `lang/fr` + `lang/en`

### Tranche 2 — Bibliothèque d'outils et prêts

- [x] DB : `outil`, `pret`, `message` (chat de prêt)
- [x] Modèles + relations + casts + factories
- [x] Machine à états `LoanService` avec contrôles d'acteur
- [x] Web : bibliothèque d'outils, création/édition/lecture d'outil, formulaire d'emprunt, liste/détail des prêts avec actions et chat
- [x] API : `/outils`, `/prets`, `/prets/{id}/transition`, `/prets/{id}/messages`
- [x] Cycle de vie complet : `demande → approuve → emprunte → retourne`, plus `refuse`, `annule`, `en_retard`, `litige` et `resoudre`
- [x] Protection contre la concurrence : `cle_active` unique, verrou optimiste `version`, `Idempotency-Key`
- [x] Balayage des retards : `prets:marquer-en-retard`, planifié chaque heure + sidecar `scheduler`
- [x] Notifications intégrées (+ badge de non-lus, page de notifications, marquage comme lu sur le Web et l'API)

### Critères de sortie

- [x] Un voisin peut publier, commenter et réagir en FR et en EN
- [x] Un voisin peut lister un outil, l'emprunter et le rendre de bout en bout
- [x] Deux tentatives d'emprunt simultanées → une seule réussit (`409`)
- [x] Le fil respecte correctement la portée quartier vs ville
- [x] Le Web et l'API passent tous deux par la même couche de services pour les mutations

**Vérification :** 43 tests / 164 assertions au vert ; `migrate:fresh --seed` réussit ; pages Web et endpoints health/token de l'API testés manuellement.

---

## 6. Phase 2 — V2 (toute la valeur canadienne) ✅

Objectif : compléter les six types de fonctionnalités et ajouter les outils opérationnels de confiance.

- [x] Demandes d'aide — `demande_aide` + `offre_aide` ; priorité `urgent`
- [x] Marché — `annonce` + `offre_annonce` + chat ; `prix = 0` ⇒ échange gratuit
- [x] Événements — `evenement` + `inscription_evenement` ; UTC + `timezone_id` IANA ; RSVP
- [x] Rencontres sociales — `rencontre` + `rencontre_membre` ; tags d'intérêts, capacité
- [x] Confiance/sécurité — file `signalement`, audit `moderation_action`, `blocage`, score `fiabilite`, `avis_pret`
- [x] Langue du contenu — contenu affiché dans la langue de l'auteur : colonne `lang` supprimée partout, filtre et formulaires retirés ; traduction **à la demande** via `TraductionService` pluggable (driver factice par défaut, LibreTranslate configurable), résultat mis en cache dans le navigateur. La clé de réponse `lang` a été retirée de l'API de lecture ; le paramètre `lang` reste accepté mais ignoré. La recherche interlangue est hors périmètre
- [x] Recherche — index FULLTEXT MySQL (fr/en) sur toutes les entités, `MATCH … AGAINST` avec repli `LIKE` sous SQLite ; recherche uniquement dans le texte de l'auteur
- [x] Polygones officiels de quartier — colonne `quartier.polygon` (GeoJSON) + commande `quartiers:importer-polygones` + polygone de démonstration
- [x] Union du fil — `/` agrège publications, outils, aide, marché, événements et rencontres visibles (`FeedService::filUnifie`)
- [x] Système de messagerie et de suivi de publication — conversations 1-à-1 (`conversation`/`conversation_membre`), suivi (`suivi`) + notifications enrichies

**Critères de sortie**

- [x] Les six types de fonctionnalités sont opérationnels avec une interface FR/EN et un contenu dans la langue de l'auteur
- [x] La file de modération est opérationnelle (`signalement` → `moderation_action`)
- [x] Les événements sont corrects aux changements d'heure (stockage UTC, `timezone_id` IANA ; tests été/hiver)
- [x] Marché/aide/rencontres suivent les mêmes conventions de périmétrage et de services (`ContenuService`, `FeedService::appliquerPortee`)
- [x] Les utilisateurs peuvent communiquer par un espace de messagerie dédié avec des notifications améliorées
- [~] L'API de lecture des publications reste compatible : `lang` est toujours accepté en entrée (ignoré), mais la clé de réponse a été retirée sur décision explicite de l'utilisateur ; à valider avec le client Android

**Vérification :** 67 tests / 280 assertions au vert ; `migrate:fresh --seed` réussit (incl. index FULLTEXT) ; Pint OK ; build Vite OK.

---

## 7. Phase 3 — Finition et mise en ligne ⏳

Objectif : polir le produit jusqu'à ce qu'il soit prêt à être mis en ligne, sans élargir son périmètre géographique ni modifier la logique de la base de données.

- [ ] Refonte de l'interface — animations, nouveau look and feel plus convivial et attractif ; changement de la mise en page **sans modification de la logique de la base de données**
- [ ] Sécurité — durcissement de l'autorisation et des policies, validation des entrées, en-têtes HTTP, gestion des secrets et revue des endpoints
- [ ] Corrections et finition — résolution des erreurs connues, nettoyage des vues historiques, accessibilité, performances perçues et cohérence visuelle
- [ ] Mise en ligne — préparation du déploiement de production : configuration, surveillance, sauvegardes et documentation opérationnelle
- [ ] Plan Android séparé consommant l'`/api/v1` déjà construit — Android utilise déjà l'API de lecture des publications, donc ce contrat ne peut pas changer ; le reste est modifiable
- [ ] Si du temps réel/du push est ajouté : Firebase comme projection en lecture seule + livraison FCM uniquement ; MySQL reste la source de vérité

---

## 8. Travaux transversaux

| Suivi | Statut | Notes |
|---|---|---|
| Tests | [~] | 67 tests / 280 assertions couvrent les chemins critiques (fil, prêts, aide, marché, événements, rencontres, confiance, messagerie) ; la matrice exhaustive des transitions et certains tests CRUD d'endpoints restent à faire |
| Autorisation | [~] | `PublicationPolicy`, `OutilPolicy`, `DemandeAidePolicy`, `AnnoncePolicy`, `EvenementPolicy`, `RencontrePolicy` ; rôles des prêts appliqués dans `LoanService` ; modération réservée aux gestionnaires/modérateurs (`EnsureModerateur`) |
| Interface bilingue | [~] | L'interface est FR/EN : les nouvelles vues (fil, aide, marché, événements, rencontres, modération, messagerie) sont traduites ; les anciennes vues auth/compte/admin contiennent encore du français codé en dur. Le contenu reste dans la langue de l'auteur, avec traduction à la demande |
| Contrat d'API | [x] | Versionné et documenté dans `docs/api-v1.md` (fil unifié, aide, marché, événements, rencontres, confiance, messagerie, suivi) |
| Observabilité | [ ] | Logs structurés, surveillance des requêtes et santé des files non implémentés |

---

## 9. Points suivants avant de déclarer le plan littéralement terminé

1. Ajouter des policies pour les entités restantes (ou formaliser l'autorisation des prêts dans une `PretPolicy`).
2. Traduire les vues historiques restantes (`auth/login`, `account/show`, `admin/show`, certaines parties de `register`).
3. Migrer le panneau d'administration « Images » de l'ancien `publication.image_path` vers la table `media`.
4. Étendre la matrice de tests : toutes les combinaisons transition/rôle et la couverture CRUD complète des endpoints `/outils`.
5. Ajouter les bases de l'observabilité (logs structurés, surveillance des requêtes lentes, santé des files).
6. Documenter l'exigence `migrate:fresh` pour les bases de données existantes (migrations de base modifiées).
7. Valider avec le client Android que l'absence de la clé `lang` en réponse et l'ignorance du paramètre `lang` sont sans effet de bord (la clé a été retirée sur décision explicite de l'utilisateur).

---

## 10. Commandes de vérification

```bash
docker compose up -d --build
docker compose exec -T app php artisan migrate:fresh --seed --force
docker compose exec -T app php vendor/bin/phpunit --do-not-cache-result
docker compose exec -T app php artisan schedule:list
docker compose exec -T app php vendor/bin/pint --test
```

Connexion de démonstration : `alex.tremblay@localhub.test` / `password` (ainsi que `sarah.gagnon@localhub.test`, etc.).

---

## 11. Journal des commits d'implémentation (phases 0 + 1 + 2)

| Commit | Message |
|---|---|
| `6b01ec8` | `feat!: suprimer la table réservation` |
| `bfd900b` | `feat : restructuration du schéma et création des tables métier` |
| `dbf30fc` | `feat : authentification Sanctum et squelette de l'API v1` |
| `8fc80e8` | `feat : fil de quartier avec publications, commentaires, réactions et images` |
| `f1b661e` | `feat : bibliothèque d'outils, prêts et notifications` |
| `5277f47` | `style : mise en forme Pint des fichiers existants` |
| `5165f46` | `feat! : contenu dans la langue de l'auteur (retrait de lang) et traduction à la demande` |
| `f91875f` | `feat : aide, marché, événements, rencontres, fil unifié et recherche plein texte` |
| *(ce commit)* | `feat : confiance, messagerie privée, suivi et polygones de quartier` |

---

## 12. Définition de « terminé » (par phase)

- [x] Code fusionné
- [x] Tests réussis (`php artisan test` / PHPUnit dans Docker)
- [x] `migrate:fresh --seed` fonctionne
- [x] API documentée (si l'API a été modifiée)
- [x] Traductions mises à jour (FR + EN)
- [x] Aucune référence aux modèles/tables supprimés
