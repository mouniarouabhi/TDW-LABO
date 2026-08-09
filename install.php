<?php
// install.php - À exécuter UNE SEULE FOIS pour créer la BDD

require_once 'config/Database.php';

echo "<h2>Installation de la base de données...</h2>";

try {
    // ⭐ CETTE LIGNE EST CRUCIALE - C'est elle qui crée vraiment la base !
    DatabaseConf::install();
    
    echo "<p style='color: green;'>✅ Base de données créée avec succès !</p>";
    echo "<p><strong>Informations de connexion :</strong></p>";
    echo "<ul>";
    echo "<li>Administrateur : <strong>admin</strong> / <strong>admin</strong></li>";
    echo "<li>Utilisateur test : <strong>user</strong> / <strong>user</strong></li>";
    echo "</ul>";
    echo "<p><a href='index.php'>Accéder au site</a></p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>