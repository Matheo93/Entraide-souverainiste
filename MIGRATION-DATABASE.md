# 🗄️ Guide de Migration de Base de Données

## 📋 Vue d'ensemble

Ce guide vous aide à migrer votre base de données MySQL locale vers o2switch pour **entraidesouverainiste.fr**.

---

## 🎯 Méthodes disponibles

1. **Export/Import SQL** (Recommandé pour première migration)
2. **Migrations Doctrine** (Pour mises à jour futures)
3. **phpMyAdmin** (Interface graphique)
4. **SSH + MySQL CLI** (Pour experts)

---

## 🚀 Méthode 1 : Export/Import SQL (Recommandé)

### Étape 1 : Exporter depuis votre environnement local

#### Option A : Via phpMyAdmin (Docker)

1. Accédez à phpMyAdmin local : `http://localhost:8081`
2. Sélectionnez la base `actionsociale`
3. Onglet **"Exporter"**
4. **Format** : SQL
5. **Options d'exportation** :
   - Cochez "Ajouter DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT / TRIGGER"
   - Cochez "Structure et données"
   - Cochez "Ajouter CREATE DATABASE / USE"
6. Cliquez sur **"Exécuter"**
7. Fichier téléchargé : `actionsociale.sql`

#### Option B : Via ligne de commande

```bash
# Depuis WSL/Ubuntu
docker-compose exec mysql mysqldump -u actionsociale -p actionsociale > actionsociale_backup_$(date +%Y%m%d_%H%M%S).sql

# Entrer le mot de passe : x7LtDGJ6A6wzWitvHswd
```

**Résultat** : Fichier `actionsociale_backup_20251117_153045.sql` créé

### Étape 2 : Préparer le fichier pour o2switch

Ouvrez le fichier `.sql` et modifiez :

```sql
-- Rechercher et remplacer (si présent)
CREATE DATABASE IF NOT EXISTS `actionsociale`;
USE `actionsociale`;

-- Par :
-- CREATE DATABASE IF NOT EXISTS `o2switch_entraide_souverainiste`;
-- USE `o2switch_entraide_souverainiste`;

-- OU simplement supprimer ces lignes si la base existe déjà
```

**Important** : Vérifiez que le fichier ne contient PAS :
- `DROP DATABASE` (sauf si vous voulez vraiment tout écraser)
- Références à des utilisateurs MySQL (`GRANT`, `CREATE USER`)

### Étape 3 : Créer la base de données sur o2switch

#### Via cPanel

1. Connectez-vous à cPanel o2switch
2. **"Bases de données MySQL"**
3. **Créer une nouvelle base de données** :
   ```
   Nom de la base : o2switch_entraide_souverainiste
   ```
4. **Créer un nouvel utilisateur** :
   ```
   Nom d'utilisateur : o2switch_entraide_admin
   Mot de passe : [Cliquez sur "Générer un mot de passe fort"]
   ```
5. **Ajouter l'utilisateur à la base** :
   - Sélectionnez l'utilisateur créé
   - Sélectionnez la base créée
   - Cochez **"TOUS LES PRIVILÈGES"**
   - Cliquez sur **"Apporter des modifications"**

6. **Notez les informations** :
   ```
   Nom de la base : votreuser_entraide (préfixe ajouté automatiquement)
   Utilisateur : votreuser_admin
   Mot de passe : [celui généré]
   Hôte : localhost
   ```

### Étape 4 : Importer sur o2switch

#### Option A : Via phpMyAdmin (Recommandé si < 50 MB)

1. cPanel → **"phpMyAdmin"**
2. Sélectionnez votre base `votreuser_entraide`
3. Onglet **"Importer"**
4. **Choisir un fichier** : `actionsociale.sql`
5. **Format** : SQL
6. **Jeu de caractères** : utf8mb4_unicode_ci
7. Cliquez sur **"Exécuter"**
8. Attendez (peut prendre 1-5 minutes)

#### Option B : Via SSH (Si > 50 MB ou timeout phpMyAdmin)

```bash
# 1. Se connecter en SSH
ssh VOTRE_USER@VOTRE_DOMAINE.o2switch.net

# 2. Uploader le fichier SQL via SCP (depuis votre machine locale)
# Sur votre machine locale :
scp actionsociale.sql VOTRE_USER@VOTRE_DOMAINE.o2switch.net:~/

# 3. Sur le serveur o2switch (via SSH) :
cd ~/

# 4. Importer
mysql -u votreuser_admin -p votreuser_entraide < actionsociale.sql

# Entrer le mot de passe de la BDD
```

### Étape 5 : Vérifier l'import

```bash
# Via SSH sur o2switch
mysql -u votreuser_admin -p votreuser_entraide -e "SHOW TABLES;"

# Compter les lignes de quelques tables
mysql -u votreuser_admin -p votreuser_entraide -e "SELECT COUNT(*) FROM user;"
mysql -u votreuser_admin -p votreuser_entraide -e "SELECT COUNT(*) FROM announces;"
```

**Résultat attendu** : Liste de toutes vos tables

---

## 🔄 Méthode 2 : Migrations Doctrine (Mises à jour futures)

### Une fois la structure initiale importée

```bash
# Via SSH sur o2switch
cd ~/entraidesouverainiste.fr

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

### Créer une nouvelle migration (lors de changements futurs)

```bash
# En local
php bin/console make:migration

# Vérifier le fichier généré dans /migrations/
# Puis pusher sur Git

# Sur le serveur
git pull
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

---

## 📊 Méthode 3 : Export/Import avec compression (Grandes bases)

### Export avec compression

```bash
# En local
docker-compose exec mysql mysqldump -u actionsociale -p actionsociale | gzip > actionsociale_backup.sql.gz
```

### Upload compressé

```bash
scp actionsociale_backup.sql.gz VOTRE_USER@VOTRE_DOMAINE.o2switch.net:~/
```

### Import avec décompression

```bash
# Via SSH sur o2switch
gunzip < actionsociale_backup.sql.gz | mysql -u votreuser_admin -p votreuser_entraide
```

---

## 🔐 Sécurité et Bonnes Pratiques

### Avant la migration

1. **Backup local** :
   ```bash
   docker-compose exec mysql mysqldump -u actionsociale -p actionsociale > backup_local_$(date +%Y%m%d).sql
   ```

2. **Vérifier l'intégrité** :
   ```bash
   # Compter les tables
   docker-compose exec mysql mysql -u actionsociale -p actionsociale -e "SHOW TABLES;" | wc -l

   # Compter les lignes importantes
   docker-compose exec mysql mysql -u actionsociale -p actionsociale -e "SELECT COUNT(*) FROM user;"
   ```

### Après la migration

1. **Backup o2switch** :
   ```bash
   # Via SSH
   mysqldump -u votreuser_admin -p votreuser_entraide > backup_post_migration_$(date +%Y%m%d).sql
   ```

2. **Vérifier les données** :
   ```bash
   # Comparer le nombre de lignes
   mysql -u votreuser_admin -p votreuser_entraide -e "SELECT COUNT(*) FROM user;"
   mysql -u votreuser_admin -p votreuser_entraide -e "SELECT COUNT(*) FROM announces;"
   mysql -u votreuser_admin -p votreuser_entraide -e "SELECT COUNT(*) FROM conversations;"
   ```

3. **Tester la connexion depuis Symfony** :
   ```bash
   cd ~/entraidesouverainiste.fr
   php bin/console doctrine:schema:validate --env=prod
   ```

---

## 🧹 Nettoyage des données sensibles (Optionnel)

### Avant de migrer en production

Si vous avez des données de test à supprimer :

```sql
-- Supprimer les utilisateurs de test
DELETE FROM user WHERE email LIKE '%@test.com';

-- Réinitialiser les mots de passe (optionnel)
UPDATE user SET password = '$2y$13$HASH_TEMPORAIRE';

-- Supprimer les anciennes annonces
DELETE FROM announces WHERE created_at < '2024-01-01';

-- Supprimer les logs (si table de logs)
TRUNCATE TABLE logs;
```

---

## 📋 Checklist de migration

### Pré-migration

- [ ] Backup local créé
- [ ] Fichier SQL exporté
- [ ] Base de données o2switch créée
- [ ] Utilisateur MySQL créé avec tous les privilèges
- [ ] Informations de connexion notées

### Migration

- [ ] Fichier SQL uploadé sur o2switch
- [ ] Import réussi sans erreurs
- [ ] Tables vérifiées (SHOW TABLES)
- [ ] Comptage des lignes cohérent

### Post-migration

- [ ] `.env.local` mis à jour avec les bons identifiants
- [ ] `doctrine:schema:validate` OK
- [ ] Test de connexion Symfony réussi
- [ ] Backup post-migration créé
- [ ] Site accessible et fonctionnel

---

## 🗄️ Structure de la base de données

### Tables principales

```
user                        → Utilisateurs
announces                   → Annonces
categories                  → Catégories
conversations               → Conversations (chat)
conversation_messages       → Messages du chat
announces_requests          → Anciennes réponses (avant chat)
reset_password_request      → Réinitialisation mot de passe
ip_bans                     → IPs bannies
stats_*                     → Tables de statistiques
settings_*                  → Paramètres
```

### Vérifier l'intégrité référentielle

```bash
php bin/console doctrine:schema:validate --env=prod
```

**Résultat attendu** :
```
[OK] The database schema is in sync with the mapping files.
```

---

## 🆘 Troubleshooting

### Erreur : "Table doesn't exist"

**Cause** : Import incomplet ou échoué

**Solution** :
```bash
# Ré-importer
mysql -u votreuser_admin -p votreuser_entraide < actionsociale.sql
```

### Erreur : "Access denied for user"

**Cause** : Mauvais identifiants dans `.env.local`

**Solution** :
```bash
# Vérifier les credentials
mysql -u votreuser_admin -p votreuser_entraide

# Si ça fonctionne, vérifier DATABASE_URL dans .env.local
```

### Erreur : "Packet too large"

**Cause** : Fichier SQL trop gros

**Solution** :
```bash
# Augmenter la limite (via SSH)
mysql -u votreuser_admin -p votreuser_entraide -e "SET GLOBAL max_allowed_packet=1073741824;"

# Ou splitter le fichier
split -l 50000 actionsociale.sql actionsociale_part_

# Puis importer chaque partie
for file in actionsociale_part_*; do
    mysql -u votreuser_admin -p votreuser_entraide < "$file"
done
```

### Erreur : "Unknown collation: utf8mb4_unicode_ci"

**Cause** : Version MySQL/MariaDB trop ancienne

**Solution** :
```sql
-- Remplacer dans le fichier .sql
utf8mb4_unicode_ci → utf8mb4_general_ci
```

---

## 🔄 Migration incrémentale (pour mises à jour futures)

### Workflow recommandé

```bash
# 1. En local : Créer la migration
php bin/console make:migration

# 2. Vérifier le fichier généré
cat migrations/VersionXXXXXXXXXXXXXX.php

# 3. Tester en local
php bin/console doctrine:migrations:migrate

# 4. Commit et push
git add migrations/
git commit -m "feat: add new column to user table"
git push

# 5. Sur le serveur
ssh VOTRE_USER@o2switch
cd ~/entraidesouverainiste.fr
git pull
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

---

## 💾 Script de backup automatique (Optionnel)

Créez `/home/VOTRE_USER/backup_db.sh` :

```bash
#!/bin/bash

BACKUP_DIR="/home/VOTRE_USER/backups"
DB_NAME="votreuser_entraide"
DB_USER="votreuser_admin"
DB_PASS="VOTRE_PASSWORD"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Garder seulement les 7 derniers backups
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: backup_$DATE.sql.gz"
```

### Rendre exécutable

```bash
chmod +x /home/VOTRE_USER/backup_db.sh
```

### Automatiser avec cron

```bash
crontab -e

# Ajouter cette ligne (backup tous les jours à 3h du matin)
0 3 * * * /home/VOTRE_USER/backup_db.sh >> /home/VOTRE_USER/backup.log 2>&1
```

---

## 📊 Statistiques après migration

### Requêtes utiles

```sql
-- Nombre d'utilisateurs
SELECT COUNT(*) as total_users FROM user;

-- Nombre d'annonces actives
SELECT COUNT(*) as active_announces FROM announces WHERE is_active = 1;

-- Nombre de conversations
SELECT COUNT(*) as total_conversations FROM conversations;

-- Taille de la base
SELECT
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'votreuser_entraide'
GROUP BY table_schema;

-- Top 10 tables par taille
SELECT
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'votreuser_entraide'
ORDER BY (data_length + index_length) DESC
LIMIT 10;
```

---

**Date de création** : 2025-11-17
**Base source** : actionsociale (local Docker)
**Base cible** : o2switch_entraide_souverainiste (o2switch)
**Méthode recommandée** : Export SQL + Import phpMyAdmin

🎉 **Bonne migration !**
