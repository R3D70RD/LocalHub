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
- `lang` : `fr` ou `en`
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
  -F 'lang=fr' \
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

`GET /outils` accepte les mêmes `scope`, `lang`, `q`, `par_page` que le fil.

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

## Notifications

| Méthode | Route | Description |
|---|---|---|
| `GET` | `/notifications` | Mes notifications |
| `POST` | `/notifications/{id}/read` | Marquer comme lue |
| `POST` | `/notifications/read-all` | Tout marquer comme lu |

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
