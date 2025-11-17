# 🌐 Configuration DNS - entraidesouverainiste.fr

## 📋 Vue d'ensemble

Ce document explique comment configurer les DNS pour votre domaine **entraidesouverainiste.fr** avec o2switch.

---

## 🔍 Informations nécessaires

Avant de commencer, récupérez ces informations depuis votre compte o2switch :

1. **IP du serveur o2switch** :
   - Connectez-vous à votre cPanel
   - Regardez dans la barre latérale droite : "Informations sur le serveur"
   - Notez l'adresse IP (ex: `217.182.xxx.xxx`)

2. **Serveurs de noms o2switch** (nameservers) :
   - Par défaut : `ns1.o2switch.net` et `ns2.o2switch.net`
   - Ou : `dns200.anycast.me` et `ns200.anycast.me`

---

## 🎯 Option 1 : Utiliser les nameservers o2switch (Recommandé)

### Avantages
- Configuration la plus simple
- Gestion DNS directement dans cPanel
- Mises à jour automatiques
- Support o2switch pour les problèmes DNS

### Configuration chez votre registrar

1. Connectez-vous à votre registrar (Gandi, OVH, Namecheap, etc.)
2. Trouvez la section **"Serveurs DNS"** ou **"Nameservers"**
3. Remplacez les nameservers actuels par :

```
Nameserver 1: ns1.o2switch.net
Nameserver 2: ns2.o2switch.net
```

**OU** (selon votre configuration o2switch) :

```
Nameserver 1: dns200.anycast.me
Nameserver 2: ns200.anycast.me
```

4. Sauvegardez
5. **Délai de propagation** : 24-48 heures (souvent 1-2 heures en pratique)

### Configuration dans cPanel o2switch

1. Connectez-vous à votre cPanel o2switch
2. Allez dans **"Domaines"** → **"Domaines supplémentaires"**
3. Ajoutez votre domaine :

```
Nouveau nom de domaine: entraidesouverainiste.fr
Sous-domaine: (laissez vide ou "www")
Racine du document: /home/VOTRE_USER/entraidesouverainiste.fr/public
```

4. Cliquez sur **"Ajouter un domaine"**
5. Le DNS est automatiquement configuré !

---

## 🎯 Option 2 : Gérer les DNS chez votre registrar

### Avantages
- Plus de contrôle
- Changements plus rapides
- Certains registrars offrent des fonctionnalités avancées (Cloudflare, etc.)

### Configuration des enregistrements DNS

#### A - Enregistrements de base

```
Type: A
Nom: @ (ou vide)
Valeur: XXX.XXX.XXX.XXX (IP de votre serveur o2switch)
TTL: 3600
```

```
Type: A
Nom: www
Valeur: XXX.XXX.XXX.XXX (même IP)
TTL: 3600
```

#### B - CNAME (optionnel, alternative au www)

```
Type: CNAME
Nom: www
Valeur: entraidesouverainiste.fr
TTL: 3600
```

#### C - MX Records (pour les emails)

Si vous utilisez les emails o2switch :

```
Type: MX
Nom: @ (ou vide)
Valeur: mail.entraidesouverainiste.fr
Priorité: 10
TTL: 3600
```

**OU** si o2switch vous donne d'autres MX :

```
Type: MX
Nom: @ (ou vide)
Valeur: mx1.mail.ovh.net
Priorité: 10
TTL: 3600
```

```
Type: MX
Nom: @ (ou vide)
Valeur: mx2.mail.ovh.net
Priorité: 20
TTL: 3600
```

#### D - SPF (anti-spam)

```
Type: TXT
Nom: @ (ou vide)
Valeur: v=spf1 include:_spf.mx.cloudflare.net ~all
TTL: 3600
```

**OU** pour o2switch :

```
Type: TXT
Nom: @ (ou vide)
Valeur: v=spf1 a mx ip4:XXX.XXX.XXX.XXX ~all
TTL: 3600
```

#### E - DKIM (optionnel, pour les emails)

À configurer depuis cPanel → "Authentification de l'e-mail"

#### F - DMARC (optionnel, sécurité emails)

```
Type: TXT
Nom: _dmarc
Valeur: v=DMARC1; p=quarantine; rua=mailto:admin@entraidesouverainiste.fr
TTL: 3600
```

---

## 📧 Configuration Email

### Créer les comptes emails dans cPanel

1. cPanel → **"Comptes de messagerie"**
2. Créez ces comptes :

```
Email: contact@entraidesouverainiste.fr
Mot de passe: [généré automatiquement]
Quota: 1000 MB
```

```
Email: noreply@entraidesouverainiste.fr
Mot de passe: [généré automatiquement]
Quota: 500 MB
```

```
Email: admin@entraidesouverainiste.fr
Mot de passe: [généré automatiquement]
Quota: 1000 MB
```

```
Email: webmaster@entraidesouverainiste.fr
Mot de passe: [généré automatiquement]
Quota: 500 MB
```

### Configurer SMTP dans .env.local

Une fois les comptes créés, mettez à jour `.env.local` sur le serveur :

```bash
APP_EMAIL_TEMP=noreply@entraidesouverainiste.fr
APP_PSW_TEMP=VOTRE_MOT_DE_PASSE_GENERE

APP_EMAIL_HOSTNAME=mail.entraidesouverainiste.fr
# OU selon o2switch:
APP_EMAIL_HOSTNAME=ssl0.ovh.net
```

### Tester l'envoi d'emails

```bash
php bin/console mailer:test contact@entraidesouverainiste.fr
```

---

## 🔒 Configuration SSL/TLS (HTTPS)

### Activer Let's Encrypt

1. cPanel → **"SSL/TLS Status"**
2. Cochez `entraidesouverainiste.fr` et `www.entraidesouverainiste.fr`
3. Cliquez sur **"Run AutoSSL"**
4. Attendez 2-5 minutes

### Forcer HTTPS

Déjà configuré dans `/public/.htaccess` :

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Vérifier le certificat

```bash
curl -I https://entraidesouverainiste.fr
```

Vous devez voir : `HTTP/2 200`

---

## ✅ Checklist de vérification

### Étape 1 : DNS propagés ?

```bash
# Vérifier l'enregistrement A
dig entraidesouverainiste.fr +short

# Vérifier le www
dig www.entraidesouverainiste.fr +short

# Vérifier les MX
dig entraidesouverainiste.fr MX +short
```

**Résultat attendu** :
```
XXX.XXX.XXX.XXX (votre IP o2switch)
```

### Étape 2 : Site accessible ?

- [ ] `http://entraidesouverainiste.fr` → Redirige vers HTTPS
- [ ] `https://entraidesouverainiste.fr` → Site s'affiche
- [ ] `https://www.entraidesouverainiste.fr` → Fonctionne (ou redirige vers non-www)
- [ ] Cadenas vert (certificat SSL valide)

### Étape 3 : Emails fonctionnels ?

- [ ] Envoi d'email de test : `php bin/console mailer:test`
- [ ] Réception d'email : Tester avec inscription utilisateur

### Étape 4 : Performance

```bash
# Test de vitesse
curl -o /dev/null -s -w "Time: %{time_total}s\n" https://entraidesouverainiste.fr
```

---

## 🆘 Troubleshooting DNS

### Le site ne se charge pas après 48h

**Causes possibles** :
1. DNS mal configurés
2. Domaine non ajouté dans cPanel
3. Cache DNS local

**Solutions** :
```bash
# Vider le cache DNS (Windows)
ipconfig /flushdns

# Vider le cache DNS (Linux/Mac)
sudo dscacheutil -flushcache

# Tester avec un autre DNS (Google)
dig @8.8.8.8 entraidesouverainiste.fr
```

### Erreur "ERR_SSL_VERSION_OR_CIPHER_MISMATCH"

**Cause** : Certificat SSL non installé ou invalide

**Solution** :
1. cPanel → SSL/TLS Status
2. Re-run AutoSSL
3. Attendre 5 minutes

### Emails ne partent pas

**Causes** :
1. Mauvais mot de passe SMTP
2. Port bloqué
3. SPF non configuré

**Solutions** :
```bash
# Tester la connexion SMTP
telnet mail.entraidesouverainiste.fr 587

# Vérifier les logs Symfony
tail -f var/log/prod.log | grep -i mail
```

### "Too many redirects"

**Cause** : Boucle de redirection HTTP → HTTPS

**Solution** :
Vérifier `/public/.htaccess` et supprimer les règles de redirection en double

---

## 🌍 Exemples de configuration par registrar

### Gandi.net

1. Connectez-vous à Gandi
2. **"Mes services"** → Sélectionnez `entraidesouverainiste.fr`
3. **"Serveurs de noms"** → **"Modifier les serveurs de noms"**
4. Choisir : **"Serveurs de noms externes"**
5. Entrer :
   ```
   ns1.o2switch.net
   ns2.o2switch.net
   ```

### OVH

1. Connectez-vous à OVH
2. **"Web Cloud"** → **"Noms de domaine"**
3. Sélectionnez `entraidesouverainiste.fr`
4. Onglet **"Serveurs DNS"**
5. **"Modifier les serveurs DNS"**
6. Entrer :
   ```
   ns1.o2switch.net
   ns2.o2switch.net
   ```

### Namecheap

1. Connectez-vous à Namecheap
2. **"Domain List"** → Sélectionnez `entraidesouverainiste.fr`
3. **"Nameservers"** → **"Custom DNS"**
4. Entrer :
   ```
   ns1.o2switch.net
   ns2.o2switch.net
   ```

### Cloudflare (avec proxy)

1. Ajoutez votre domaine sur Cloudflare
2. Cloudflare vous donnera ses nameservers :
   ```
   xxx.ns.cloudflare.com
   yyy.ns.cloudflare.com
   ```
3. Configurez les DNS dans Cloudflare :
   - Type A : `@` → `IP_O2SWITCH` (nuage orange activé)
   - Type A : `www` → `IP_O2SWITCH` (nuage orange activé)

---

## 📊 Outils de diagnostic

### En ligne

- **DNS Checker** : https://dnschecker.org/
- **SSL Test** : https://www.ssllabs.com/ssltest/
- **WhatsMyDNS** : https://www.whatsmydns.net/

### Ligne de commande

```bash
# Whois
whois entraidesouverainiste.fr

# Dig
dig entraidesouverainiste.fr ANY

# NSLookup
nslookup entraidesouverainiste.fr

# Traceroute
traceroute entraidesouverainiste.fr

# cURL avec détails
curl -Iv https://entraidesouverainiste.fr
```

---

## 🎯 Configuration finale recommandée

```
Zone DNS complète:

@ (root)          A       XXX.XXX.XXX.XXX        3600
www               A       XXX.XXX.XXX.XXX        3600
mail              A       XXX.XXX.XXX.XXX        3600

@                 MX      mail.entraidesouverainiste.fr  10    3600

@                 TXT     "v=spf1 a mx ip4:XXX.XXX.XXX.XXX ~all"    3600
_dmarc            TXT     "v=DMARC1; p=quarantine; rua=mailto:admin@entraidesouverainiste.fr"    3600

@ (root)          CAA     0 issue "letsencrypt.org"    3600
```

---

**Date de création** : 2025-11-17
**Domaine** : entraidesouverainiste.fr
**Hébergeur** : o2switch
**Délai de propagation** : 24-48 heures (souvent 1-2h)

🎉 **Bonne configuration DNS !**
