#!/bin/bash

##############################################
# Script de déploiement automatique
# Entraide Souverainiste - o2switch
# Version: 1.0
##############################################

set -e  # Arrêter en cas d'erreur

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🚀 Déploiement Entraide Souverainiste"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Configuration
PROJECT_DIR="$(pwd)"
ENV_FILE=".env.local"
SYMFONY_ENV="prod"

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonctions d'affichage
function success() {
    echo -e "${GREEN}✓ $1${NC}"
}

function error() {
    echo -e "${RED}✗ $1${NC}"
}

function info() {
    echo -e "${YELLOW}→ $1${NC}"
}

# Vérification de l'environnement
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📋 Vérifications préalables"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "composer.json" ]; then
    error "composer.json introuvable. Êtes-vous dans le bon répertoire ?"
    exit 1
fi
success "Répertoire du projet validé"

# Vérifier que .env.local existe
if [ ! -f "$ENV_FILE" ]; then
    error "Le fichier $ENV_FILE n'existe pas. Créez-le d'abord !"
    exit 1
fi
success "Fichier .env.local trouvé"

# Vérifier PHP
if ! command -v php &> /dev/null; then
    error "PHP n'est pas installé ou pas dans le PATH"
    exit 1
fi
success "PHP installé : $(php -v | head -n 1)"

# Vérifier Composer
if ! command -v composer &> /dev/null; then
    error "Composer n'est pas installé"
    exit 1
fi
success "Composer installé : $(composer --version)"

# Vérifier Node.js
if ! command -v node &> /dev/null; then
    error "Node.js n'est pas installé"
    exit 1
fi
success "Node.js installé : $(node -v)"

# Vérifier npm
if ! command -v npm &> /dev/null; then
    error "npm n'est pas installé"
    exit 1
fi
success "npm installé : $(npm -v)"

echo ""

# Étape 1 : Pull des modifications Git
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📥 Étape 1/8 : Mise à jour du code source"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
info "Récupération des dernières modifications..."

if [ -d ".git" ]; then
    git pull origin master || git pull origin main
    success "Code source mis à jour"
else
    info "Pas de repository Git, passage à l'étape suivante"
fi

echo ""

# Étape 2 : Installation des dépendances Composer
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📦 Étape 2/8 : Installation des dépendances PHP"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
info "Installation de Composer..."

composer install --no-dev --optimize-autoloader --no-interaction
success "Dépendances PHP installées"

echo ""

# Étape 3 : Installation des dépendances npm
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📦 Étape 3/8 : Installation des dépendances Node.js"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
info "Installation de npm..."

npm install --legacy-peer-deps
success "Dépendances Node.js installées"

echo ""

# Étape 4 : Build des assets
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔨 Étape 4/8 : Compilation des assets"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
info "Build en production..."

npm run build
success "Assets compilés avec succès"

echo ""

# Étape 5 : Migrations de base de données
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🗄️  Étape 5/8 : Migrations base de données"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
info "Exécution des migrations..."

php bin/console doctrine:migrations:migrate --no-interaction --env=prod
success "Migrations exécutées"

echo ""

# Étape 6 : Optimisation de l'autoloader
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "⚡ Étape 6/8 : Optimisation de l'autoloader"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
info "Optimisation..."

composer dump-autoload --optimize --no-dev --classmap-authoritative
success "Autoloader optimisé"

echo ""

# Étape 7 : Cache Symfony
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔥 Étape 7/8 : Gestion du cache"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
info "Vidage du cache..."

php bin/console cache:clear --env=prod --no-debug
success "Cache vidé"

info "Préchauffage du cache..."
php bin/console cache:warmup --env=prod
success "Cache préchauffé"

echo ""

# Étape 8 : Permissions
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔐 Étape 8/8 : Permissions"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
info "Configuration des permissions..."

chmod -R 755 var/
chmod -R 755 public/build/
[ -d "public/uploads" ] && chmod -R 755 public/uploads/
success "Permissions configurées"

echo ""

# Étape 9 : Redémarrage du bot Discord (optionnel)
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🤖 Bot Discord"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if command -v pm2 &> /dev/null; then
    info "Redémarrage du bot Discord..."
    pm2 restart entraide-bot 2>/dev/null || info "Bot Discord non configuré avec PM2"
    success "Bot Discord redémarré"
else
    info "PM2 non installé, bot Discord non redémarré automatiquement"
    info "Pour redémarrer manuellement: pm2 restart entraide-bot"
fi

echo ""

# Vérifications finales
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔍 Vérifications finales"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Vérifier la configuration Symfony
info "Vérification de la configuration Symfony..."
php bin/console about --env=prod
success "Configuration Symfony OK"

echo ""

# Résumé
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Déploiement terminé avec succès !"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📝 Prochaines étapes :"
echo "   1. Vérifier que le site fonctionne : https://entraidesouverainiste.fr"
echo "   2. Tester les fonctionnalités principales"
echo "   3. Vérifier les logs si nécessaire : tail -f var/log/prod.log"
echo ""
echo "🎉 Bon déploiement !"
echo ""
