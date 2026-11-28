# LocalHub — API v1

Base URL : `/api/v1`

Toutes les réponses sont en JSON. Les routes d'écriture exigent un jeton Sanctum
(`Authorization: Bearer <token>`).

## Authentification

| Méthode | Route | Description |
|---|---|---|
| `POST` | `/auth/token` | Échange `courriel` + `mot_de_passe` contre un jeton |
| `GET` | `/me` | Profil du porteur du jeton |
| `POST` | `/auth/logout` | Révoque le jeton courant |
| `GET` | `/health` | Sonde de disponibilité (publique) |

```bash
curl -X POST http://localhost:8080/api/v1/auth/token \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"courriel":"alex.tremblay@localhub.test","mot_de_passe":"password"}'
```

## Fil de quartier

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/publications` | Fil paginé, lecture publique |
| `GET` | `/publications/{id}` | Détail d'une publication |
| `POST` | `/publications` | Créer une publication |
| `PUT/PATCH` | `/publications/{id}` | Modifier (propriétaire) |
| `DELETE` | `/publications/{id}` | Supprimer (propriétaire) |
| `POST` | `/publications/{id}/commentaires` | Commenter |
| `POST` | `/publications/{id}/reactions` | Basculer une réaction (`type` : `j_aime`, `utile`, `merci`) |
| `DELETE` | `/medias/{id}` | Supprimer une image (propriétaire de la cible) |

Paramètres de `GET /publications` :

- `scope` : `quartier` (défaut), `ville`, `pays`
- `lang` : accepté mais **ignoré** (compatibilité avec l'ancien client Android). Le contenu est toujours affiché dans la langue de l'auteur ; aucune traduction automatique n'est appliquée côté API.
- `q` : recherche texte sur le titre et la description
- `par_page` : 1 à 50 (défaut 15)

Règle de portée : la `portee` choisie par l'auteur est un plafond ; `scope` est le
rayon demandé par le lecteur. Les deux doivent concorder, et l'identifiant
géographique du niveau concerné doit correspondre.

Visibilité publique : une requête sans jeton Sanctum ne reçoit que les
publications `portee = pays`, aussi bien sur `GET /publications` que sur
`GET /publications/{id}`. Les publications de ville ou de quartier renvoient
`404` sans authentification.

Création (multipart pour les images) :

```bash
curl -X POST http://localhost:8080/api/v1/publications \
  -H 'Authorization: Bearer <token>' \
  -H 'Accept: application/json' \
  -F 'titre=Perceuse à prêter' \
  -F 'description=Disponible samedi.' \
  -F 'portee=quartier' \
  -F 'images[]=@/chemin/photo1.jpg' \
  -F 'images[]=@/chemin/photo2.jpg'
```

## Bibliothèque d'outils

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/outils` | Liste paginée, lecture publique |
| `GET` | `/outils/{id}` | Détail |
| `POST` | `/outils` | Ajouter un outil |
| `PUT/PATCH` | `/outils/{id}` | Modifier (propriétaire) |
| `DELETE` | `/outils/{id}` | Supprimer (propriétaire) |

`GET /outils` accepte les mêmes `scope`, `q`, `par_page` que le fil. Le paramètre `lang` n'est plus pris en charge (le contenu est dans la langue de l'auteur).

## Prêts

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/prets` | Mes prêts (emprunteur ou propriétaire) |
| `POST` | `/outils/{id}/prets` | Créer une demande de prêt |
| `GET` | `/prets/{id}` | Détail (participants) |
| `POST` | `/prets/{id}/transition` | Appliquer une transition |
| `GET` | `/prets/{id}/messages` | Messages du prêt |
| `POST` | `/prets/{id}/messages` | Envoyer un message |

Demande :

```json
{
  "date_debut": "2026-12-01",
  "date_fin": "2026-12-04",
  "message": "Disponible lundi ?"
}
```

Transition :

```json
{
  "action": "approuver",
  "version": 1
}
```

Actions valides selon l'état :

```
demande   → approuver | refuser | annuler
approuve  → emprunter | annuler
emprunte  → retourner | ouvrir_litige
en_retard → retourner | ouvrir_litige
litige    → resoudre
```

Protection contre les courses :

- index unique nullable `pret.cle_active` : un seul prêt actif par outil ;
- verrou optimiste `version` : une version périmée renvoie `409` ;
- en-tête `Idempotency-Key` sur `POST /outils/{id}/prets` et
  `POST /prets/{id}/transition` : un retry avec la même clé rejoue la réponse
  d'origine (`Idempotent-Replayed: true`) au lieu de dupliquer l'opération.

## Fil unifié

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/fil` | Fil unifié de toutes les entités visibles (publications, outils, aide, marché, événements, rencontres) |

Paramètres : `scope`, `q`, `par_page`. Chaque élément expose `type`, `id`, `titre`,
`description`, `date`, `portee`, `auteur` et `categorie`.

## Demandes d'aide

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/demandes-aide` | Liste paginée, lecture publique |
| `GET` | `/demandes-aide/{id}` | Détail |
| `POST` | `/demandes-aide` | Créer une demande |
| `PUT/PATCH` | `/demandes-aide/{id}` | Modifier (auteur) |
| `DELETE` | `/demandes-aide/{id}` | Supprimer (auteur) |
| `POST` | `/demandes-aide/{id}/offres` | Proposer une aide |

`urgent` (booléen) et `date_limite` sont optionnels. Les listes acceptent `scope`, `q`, `par_page`.

## Marché

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/annonces` | Liste paginée, lecture publique |
| `GET` | `/annonces/{id}` | Détail |
| `POST` | `/annonces` | Créer une annonce |
| `PUT/PATCH` | `/annonces/{id}` | Modifier (auteur) |
| `DELETE` | `/annonces/{id}` | Supprimer (auteur) |
| `POST` | `/annonces/{id}/offres` | Faire une offre |
| `POST` | `/annonces/{id}/messages` | Envoyer un message (chat de l’annonce) |

`prix = 0` ⇒ échange gratuit : la réponse expose `gratuite: true` et `type_annonce`
est ramené à `don` (sauf pour une `recherche`).

## Événements

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/evenements` | Liste paginée, lecture publique |
| `GET` | `/evenements/{id}` | Détail |
| `POST` | `/evenements` | Créer un événement |
| `PUT/PATCH` | `/evenements/{id}` | Modifier (auteur) |
| `DELETE` | `/evenements/{id}` | Supprimer (auteur) |
| `POST` | `/evenements/{id}/inscriptions` | S’inscrire (RSVP) |
| `DELETE` | `/evenements/{id}/inscriptions` | Se désinscrire |

Les dates (`date_debut`, `date_fin`) sont stockées en **UTC** ; `timezone_id` est un
identifiant IANA (`America/Toronto`, …). La capacité est vérifiée à l’inscription
(`422` si complet).

## Rencontres sociales

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/rencontres` | Liste paginée, lecture publique |
| `GET` | `/rencontres/{id}` | Détail |
| `POST` | `/rencontres` | Créer une rencontre |
| `PUT/PATCH` | `/rencontres/{id}` | Modifier (auteur) |
| `DELETE` | `/rencontres/{id}` | Supprimer (auteur) |
| `POST` | `/rencontres/{id}/membres` | Rejoindre |
| `DELETE` | `/rencontres/{id}/membres` | Quitter |

`interets` est une liste de chaînes ; `capacite` est optionnelle et vérifiée à l’adhésion.

## Recherche

Toutes les listes et le fil unifié acceptent `q`. Sur MySQL, la recherche utilise les
index **FULLTEXT** (`MATCH … AGAINST` sur `titre` et `description`, fr/en) ; sous SQLite
(tests), un repli `LIKE` est utilisé. Seul le texte de l’auteur est recherché, sans
recherche interlangue.

## Notifications

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/notifications` | Mes notifications |
| `POST` | `/notifications/{id}/read` | Marquer comme lue |
| `POST` | `/notifications/read-all` | Tout marquer comme lu |

## Confiance et sécurité

| Méthode | Route | Description |
|---|---|---|
| `POST` | `/signalements` | Signaler un contenu (`cible_type`, `cible_id`, `motif`, `description`) |
| `GET` | `/moderation` | File de modération (gestionnaire/modérateur) |
| `POST` | `/moderation/{id}/traiter` | Traiter un signalement (`action` : `rejeter`, `masquer`, `avertir`, `supprimer`) |
| `POST` | `/utilisateurs/{id}/bloquer` | Bloquer un utilisateur |
| `DELETE` | `/utilisateurs/{id}/bloquer` | Débloquer |
| `POST` | `/prets/{id}/avis` | Noter un prêt terminé (`note` 1–5, `commentaire`) |

`cible_type` accepte : `publication`, `outil`, `demande_aide`, `annonce`, `evenement`,
`rencontre`, `utilisateur`. Chaque action de modération est journalisée dans
`moderation_action`. Les contenus des utilisateurs bloqués sont exclus du fil et des
listes du bloqueur. Un avis de prêt met à jour le score `fiabilite` (0–100) du voisin noté.

## Messagerie privée

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/messages` | Mes conversations |
| `POST` | `/messages` | Démarrer une conversation (`destinataire`) |
| `GET` | `/messages/{id}` | Détail d’une conversation (membres) |
| `POST` | `/messages/{id}` | Envoyer un message (`contenu`) |

## Suivi de publication

| Méthode | Route | Description |
|---|---|---|
| `POST` | `/publications/{id}/suivre` | Suivre une publication |
| `DELETE` | `/publications/{id}/suivre` | Ne plus suivre |

Les suiveurs reçoivent une notification lors d’un nouveau commentaire.

## Codes d'erreur notables

| Code | Signification |
|---|---|
| `401` | Jeton absent ou invalide |
| `403` | Action non autorisée (non-propriétaire, tiers au prêt) |
| `409` | Conflit de prêt (outil déjà réservé, version périmée) |
| `422` | Validation ou transition invalide |

## Vérification locale

```bash
docker compose exec -T app php artisan migrate:fresh --seed --force
docker compose exec -T app php vendor/bin/phpunit --do-not-cache-result
docker compose exec -T app php artisan schedule:list
```
