# Base utilisée par Doctrine en environnement "test" (dbname_suffix: _test dans doctrine.yaml).
# Exécuté par l'image MariaDB une seule fois, à la création du volume.
# Ce fichier ne doit PAS être exécutable : l'image le "source" et fournit alors docker_process_sql.
docker_process_sql <<EOSQL
CREATE DATABASE IF NOT EXISTS \`${MARIADB_DATABASE}_test\`;
GRANT ALL PRIVILEGES ON \`${MARIADB_DATABASE}_test\`.* TO '${MARIADB_USER}'@'%';
EOSQL
