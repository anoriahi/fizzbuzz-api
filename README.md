# fizzbuzz-api

API REST Symfony 7.4 / PHP 8.2, servie par php-fpm + nginx, avec une base MariaDB 11.8.9.

## Exigences

- [Docker Engine](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/) (`docker compose`)
- [Git](https://git-scm.com/)

## Installation

### DEV

Le code de `app/` est monté dans le conteneur : les modifications sont visibles sans reconstruire l'image.

1. Cloner le dépôt :

   ```sh
   git clone git@github.com:anoriahi/fizzbuzz-api.git fizzbuzz-api
   cd fizzbuzz-api
   ```

2. Construire les images :

   ```sh
   make build
   ```

3. Démarrer l'environnement :

   ```sh
   make up
   ```

4. Créer le schéma de la base :

   ```sh
   make console c="doctrine:migrations:migrate --no-interaction"
   ```

5. L'API répond sur <http://localhost:8080>.

#### Configuration

Les valeurs par défaut conviennent au développement. Pour les changer, créer un fichier `.env` à la racine du projet (lu par Docker Compose, à ne pas versionner) :

| Variable           | Défaut     | Rôle                                                 |
| ------------------ | ---------- | ---------------------------------------------------- |
| `HTTP_PORT`        | `8080`     | Port de l'API sur la machine                         |
| `DB_PORT`          | `3306`     | Port de MariaDB sur la machine                       |
| `DB_NAME`          | `fizzbuzz` | Nom de la base (la base de test est `fizzbuzz_test`) |
| `DB_USER`          | `fizzbuzz` | Utilisateur MariaDB                                  |
| `DB_PASSWORD`      | `fizzbuzz` | Mot de passe de l'utilisateur                        |
| `DB_ROOT_PASSWORD` | `root`     | Mot de passe root de MariaDB                         |
| `XDEBUG_MODE`      | `off`      | Mode Xdebug (`debug`, `coverage`…)                   |

#### Commandes utiles

| Commande                                | Action                                        |
| --------------------------------------- | --------------------------------------------- |
| `make help`                             | Liste toutes les commandes                    |
| `make logs`                             | Suit les logs des conteneurs                  |
| `make sh`                               | Ouvre un shell dans le conteneur PHP          |
| `make composer c="require symfony/uid"` | Lance Composer                                |
| `make console c="debug:router"`         | Lance `bin/console`                           |
| `make db`                               | Client MariaDB sur la base de l'application   |
| `make test`                             | Lance les tests sur la base `fizzbuzz_test`   |
| `make qa`                               | Lance Rector, PHP-CS-Fixer et PHPStan à blanc |
| `make down`                             | Arrête l'environnement                        |

Les données MariaDB sont conservées entre deux `make down` / `make up`. Pour repartir d'une base vide (et recréer la base de test) :

```sh
docker compose down -v
```

### PROD

En production, le code et les dépendances (sans les paquets de dev) sont embarqués dans les images : rien n'est monté depuis la machine. Le cache Symfony est préchauffé et OPcache ne vérifie plus les fichiers.

1. Cloner le dépôt sur le serveur :

   ```sh
   git clone git@github.com:anoriahi/fizzbuzz-api.git fizzbuzz-api
   cd fizzbuzz-api
   ```

2. Définir les secrets. Ils n'ont pas de valeur par défaut : toute commande `make prod-*` échoue s'ils sont absents.

   La méthode recommandée est un fichier `.env` à la racine du projet, que Docker Compose lit automatiquement. Le créer en remplaçant les valeurs par les vôtres :

   ```dotenv
   APP_SECRET=<chaine-aleatoire-de-64-caracteres>
   DB_PASSWORD=<mot-de-passe-fort>
   DB_ROOT_PASSWORD=<autre-mot-de-passe-fort>

   # Optionnel (valeurs par défaut indiquées)
   # HTTP_PORT=8080
   # DB_NAME=fizzbuzz
   # DB_USER=fizzbuzz
   ```

   Puis le protéger :

   ```sh
   chmod 600 .env
   ```

3. Construire les images :

   ```sh
   make prod-build
   ```

4. Démarrer les services :

   ```sh
   make prod-up
   ```

5. Appliquer les migrations :

   ```sh
   docker compose -f compose.yaml -f compose.prod.yaml exec php bin/console doctrine:migrations:migrate --no-interaction
   ```

6. L'API répond sur le port `HTTP_PORT` (8080 par défaut), en HTTP.

#### Mettre à jour

```sh
git pull
make prod-build
make prod-up
docker compose -f compose.yaml -f compose.prod.yaml exec php bin/console doctrine:migrations:migrate --no-interaction
```

`make prod-up` recrée uniquement les conteneurs dont l'image a changé ; les données MariaDB sont conservées dans le volume `database_data`.

Pour arrêter : `make prod-down`.

## Documentation de l'API

La documentation interactive (NelmioApiDocBundle, interface Stoplight Elements) est servie à la racine de l'API :

| URL                              | Contenu                              |
| -------------------------------- | ------------------------------------ |
| <http://localhost:8080>          | Documentation interactive            |
| <http://localhost:8080/doc.json> | Spécification OpenAPI au format JSON |

En PROD, remplacer `8080` par la valeur de `HTTP_PORT`.
