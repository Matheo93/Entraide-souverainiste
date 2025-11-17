# 🐳 Docker + CI/CD - Guide Complet

## 📋 Vue d'ensemble

Ce guide explique comment utiliser Docker pour rendre l'application portable sur toutes les machines et configurer le CI/CD avec GitHub Actions pour un déploiement automatique.

---

## 🐳 Configuration Docker

### Architecture Docker

L'application est divisée en plusieurs containers :

1. **app** : Symfony + Nginx + PHP-FPM (multi-stage build)
2. **mysql** : Base de données MariaDB 10.5
3. **redis** : Cache et sessions
4. **discord-bot** : Bot Discord Node.js

### Fichiers Docker créés

```
.
├── Dockerfile.prod              # Image production optimisée
├── docker-compose.prod.yml      # Orchestration des services
└── docker/
    ├── php/
    │   ├── php.ini             # Configuration PHP
    │   └── opcache.ini         # Optimisation OPcache
    ├── nginx/
    │   ├── nginx.conf          # Configuration Nginx
    │   └── default.conf        # Virtual host
    └── supervisor/
        └── supervisord.conf    # Gestion Nginx + PHP-FPM
```

---

## 🚀 Utilisation Docker

### Démarrage en local

```bash
# 1. Créer le fichier .env.local
cp .env.prod .env.local

# 2. Éditer .env.local avec vos identifiants
nano .env.local

# 3. Build et démarrer les containers
docker-compose -f docker-compose.prod.yml up -d --build

# 4. Vérifier que tout tourne
docker-compose -f docker-compose.prod.yml ps

# 5. Accéder au site
http://localhost:8080
```

### Commandes utiles

```bash
# Voir les logs
docker-compose -f docker-compose.prod.yml logs -f

# Logs d'un service spécifique
docker-compose -f docker-compose.prod.yml logs -f app

# Entrer dans un container
docker-compose -f docker-compose.prod.yml exec app sh

# Exécuter des commandes Symfony
docker-compose -f docker-compose.prod.yml exec app php bin/console cache:clear

# Arrêter les containers
docker-compose -f docker-compose.prod.yml down

# Supprimer les volumes (ATTENTION: supprime les données!)
docker-compose -f docker-compose.prod.yml down -v
```

### Exécuter les migrations

```bash
docker-compose -f docker-compose.prod.yml exec app php bin/console doctrine:migrations:migrate --no-interaction
```

### Vider le cache

```bash
docker-compose -f docker-compose.prod.yml exec app php bin/console cache:clear --env=prod
```

---

## ⚙️ CI/CD avec GitHub Actions

### Workflows créés

1. **`.github/workflows/ci.yml`** - Tests et validation
   - Déclenché sur les Pull Requests
   - Exécute les tests PHPUnit
   - Valide le code (PHP CS Fixer, PHPStan)
   - Build de l'image Docker

2. **`.github/workflows/deploy.yml`** - Déploiement automatique
   - Déclenché sur push vers `master`/`main`
   - Build des assets
   - Déploiement SSH vers o2switch
   - Exécution des migrations
   - Redémarrage du bot Discord

---

## 🔐 Configuration des secrets GitHub

### Étape 1 : Générer une clé SSH

```bash
# Sur votre machine locale
ssh-keygen -t ed25519 -C "github-actions@entraidesouverainiste.fr" -f ~/.ssh/o2switch_deploy

# Vous obtenez :
# - ~/.ssh/o2switch_deploy (clé privée)
# - ~/.ssh/o2switch_deploy.pub (clé publique)
```

### Étape 2 : Ajouter la clé publique sur o2switch

```bash
# Se connecter en SSH
ssh roju9688@kitty.o2switch.net

# Ajouter la clé publique
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys
# Coller le contenu de o2switch_deploy.pub
chmod 600 ~/.ssh/authorized_keys
```

### Étape 3 : Configurer les secrets GitHub

1. Aller sur GitHub : `https://github.com/Matheo93/Entraide-souverainiste`
2. **Settings** → **Secrets and variables** → **Actions**
3. Cliquer sur **"New repository secret"**

Ajouter ces secrets :

```
SSH_PRIVATE_KEY
→ Coller le contenu de ~/.ssh/o2switch_deploy (TOUTE la clé privée)

REMOTE_HOST
→ kitty.o2switch.net

REMOTE_USER
→ roju9688

REMOTE_PATH
→ /home/roju9688/entraidesouverainiste.fr
```

### Étape 4 : Tester le déploiement

```bash
# Push vers master pour déclencher le déploiement
git add .
git commit -m "feat: configure Docker + CI/CD"
git push origin master

# Aller sur GitHub → Actions
# Vérifier que le workflow "Deploy to o2switch" s'exécute correctement
```

---

## 🎯 Workflow de développement

### 1. Développement en local

```bash
# Utiliser Docker Compose local
docker-compose up -d

# Ou utiliser l'environnement existant (sans Docker)
php bin/console server:start
```

### 2. Créer une feature branch

```bash
git checkout -b feature/nouvelle-fonctionnalite
# Faire vos modifications
git add .
git commit -m "feat: ajout nouvelle fonctionnalité"
git push origin feature/nouvelle-fonctionnalite
```

### 3. Créer une Pull Request

1. Aller sur GitHub
2. Créer une Pull Request vers `master`
3. **GitHub Actions CI** s'exécute automatiquement :
   - ✅ Tests PHPUnit
   - ✅ Validation du code
   - ✅ Build Docker

### 4. Merge vers master

Une fois la PR approuvée :

1. Merge vers `master`
2. **GitHub Actions Deploy** s'exécute automatiquement :
   - 🔨 Build des assets
   - 📤 Upload vers o2switch via SSH
   - 🗄️ Exécution des migrations
   - 🔄 Vidage du cache
   - 🤖 Redémarrage du bot Discord

---

## 🐋 Dockerfile Production (Multi-stage)

### Stage 1 : Build Node.js

```dockerfile
FROM node:18-alpine AS node-builder
# Install npm dependencies
# Build production assets
```

### Stage 2 : Composer dependencies

```dockerfile
FROM composer:2 AS composer-builder
# Install PHP dependencies (production only)
```

### Stage 3 : Image finale

```dockerfile
FROM php:8.2-fpm-alpine
# Copy from stages 1 & 2
# Configure Nginx + PHP-FPM + Supervisor
```

**Avantages** :
- ✅ Image finale légère (~150 MB vs 800 MB)
- ✅ Pas de outils de build dans l'image finale
- ✅ Optimisée pour la production

---

## 📊 Monitoring et Logs

### Logs des containers

```bash
# Tous les containers
docker-compose -f docker-compose.prod.yml logs -f

# App Symfony
docker-compose -f docker-compose.prod.yml logs -f app

# MySQL
docker-compose -f docker-compose.prod.yml logs -f mysql

# Bot Discord
docker-compose -f docker-compose.prod.yml logs -f discord-bot
```

### Logs Symfony (dans le container)

```bash
docker-compose -f docker-compose.prod.yml exec app tail -f var/log/prod.log
```

### Healthchecks

Tous les containers ont des healthchecks configurés :

```bash
# Vérifier le statut des healthchecks
docker-compose -f docker-compose.prod.yml ps
```

---

## 🔧 Troubleshooting

### Le build Docker échoue

```bash
# Nettoyer le cache Docker
docker system prune -a

# Rebuild sans cache
docker-compose -f docker-compose.prod.yml build --no-cache
```

### Les assets ne se chargent pas

```bash
# Rebuild les assets dans le container
docker-compose -f docker-compose.prod.yml exec app sh -c "cd /var/www/html && npm run build"
```

### Erreur de connexion MySQL

```bash
# Vérifier que MySQL est bien démarré
docker-compose -f docker-compose.prod.yml ps mysql

# Voir les logs MySQL
docker-compose -f docker-compose.prod.yml logs mysql

# Tester la connexion
docker-compose -f docker-compose.prod.yml exec app php bin/console doctrine:schema:validate
```

### Le bot Discord ne démarre pas

```bash
# Voir les logs
docker-compose -f docker-compose.prod.yml logs discord-bot

# Vérifier les variables d'environnement
docker-compose -f docker-compose.prod.yml exec discord-bot printenv

# Redémarrer le bot
docker-compose -f docker-compose.prod.yml restart discord-bot
```

---

## 🚀 Déploiement manuel sur o2switch

Si le CI/CD ne fonctionne pas, déploiement manuel :

```bash
# 1. Se connecter en SSH
ssh roju9688@kitty.o2switch.net

# 2. Aller dans le répertoire
cd ~/entraidesouverainiste.fr

# 3. Pull dernières modifications
git pull origin master

# 4. Installer dépendances
composer install --no-dev --optimize-autoloader
npm install --legacy-peer-deps

# 5. Build assets
npm run build

# 6. Migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# 7. Vider cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# 8. Redémarrer bot
pm2 restart entraide-bot
```

---

## 📋 Checklist de configuration CI/CD

### GitHub

- [ ] Repository créé : `https://github.com/Matheo93/Entraide-souverainiste`
- [ ] Secrets configurés (SSH_PRIVATE_KEY, REMOTE_HOST, etc.)
- [ ] Workflows créés (`.github/workflows/*.yml`)

### o2switch

- [ ] Clé SSH publique ajoutée (`~/.ssh/authorized_keys`)
- [ ] Repository cloné dans `/home/roju9688/entraidesouverainiste.fr`
- [ ] `.env.local` configuré

### Test

- [ ] Push vers `master` déclenche le déploiement
- [ ] Workflow GitHub Actions s'exécute sans erreurs
- [ ] Site accessible après déploiement
- [ ] Bot Discord redémarre correctement

---

## 🎯 Avantages de cette architecture

### Docker

- ✅ **Portabilité** : Fonctionne sur toutes les machines (Windows, Mac, Linux)
- ✅ **Isolation** : Chaque service dans son container
- ✅ **Reproductibilité** : Même environnement dev/prod
- ✅ **Scalabilité** : Facile d'ajouter des replicas

### CI/CD

- ✅ **Automatisation** : Déploiement automatique sur push
- ✅ **Tests automatiques** : Validation avant merge
- ✅ **Zero-downtime** : Pas d'interruption de service
- ✅ **Rollback facile** : Revert Git = rollback

---

## 📚 Ressources

### Documentation

- Docker : https://docs.docker.com
- Docker Compose : https://docs.docker.com/compose
- GitHub Actions : https://docs.github.com/en/actions
- Symfony Docker : https://symfony.com/doc/current/setup/docker.html

### Commandes de référence

```bash
# Docker
docker ps                         # Liste containers
docker logs <container>           # Voir logs
docker exec -it <container> sh    # Entrer dans container
docker system prune -a            # Nettoyer tout

# Docker Compose
docker-compose up -d              # Démarrer
docker-compose down               # Arrêter
docker-compose logs -f            # Logs temps réel
docker-compose ps                 # Statut

# GitHub Actions
# Voir : https://github.com/Matheo93/Entraide-souverainiste/actions
```

---

**Date de création** : 2025-11-17
**Version** : 1.0
**Projet** : Entraide Souverainiste
**Docker** : Multi-stage build optimisé
**CI/CD** : GitHub Actions

🎉 **Prêt pour le développement et le déploiement automatique !**
