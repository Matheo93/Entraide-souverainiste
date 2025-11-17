<?php

// Script pour créer des données de test basiques
require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->loadEnv('.env');

// Configuration PDO pour SQLite
$pdo = new PDO($_ENV['DATABASE_URL']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Création de données de test...\n";

try {
    // Créer quelques catégories
    $categories = [
        ['name' => 'Juridique', 'slug' => 'juridique'],
        ['name' => 'Médical', 'slug' => 'medical'], 
        ['name' => 'Éducation', 'slug' => 'education'],
        ['name' => 'Emploi', 'slug' => 'emploi'],
        ['name' => 'Immobilier', 'slug' => 'immobilier'],
        ['name' => 'Aide sociale', 'slug' => 'aide-sociale']
    ];

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO categories (name, slug) VALUES (?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute([$cat['name'], $cat['slug']]);
        echo "Catégorie créée: " . $cat['name'] . "\n";
    }

    // Créer un utilisateur de test
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO user (email, roles, password, is_verified, refered_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        'test@exemple.com',
        '["ROLE_USER"]',
        password_hash('test123', PASSWORD_DEFAULT),
        1,
        1
    ]);
    echo "Utilisateur de test créé: test@exemple.com / test123\n";

    echo "\n✅ Données de test créées avec succès !\n";
    echo "Vous pouvez maintenant accéder au site.\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}