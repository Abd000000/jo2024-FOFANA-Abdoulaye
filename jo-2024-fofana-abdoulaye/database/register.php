<?php
session_start(); // Assurez-vous que la session est démarrée au tout début.

require_once("database.php"); // Assurez-vous que ce chemin est correct.

// Ce bloc ne s'exécute que si la méthode de la requête est POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupère et nettoie les données soumises.
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $password = $_POST["password"];
    $nom_utilisateur = filter_input(INPUT_POST, 'nom_utilisateur', FILTER_SANITIZE_STRING);
    $prenom_utilisateur = filter_input(INPUT_POST, 'prenom_utilisateur', FILTER_SANITIZE_STRING);

    // Vérification de l'existence du login.
    $checkQuery = "SELECT COUNT(*) FROM UTILISATEUR WHERE login = :login";
    $checkStmt = $connexion->prepare($checkQuery);
    $checkStmt->bindParam(":login", $login, PDO::PARAM_STR);
    $checkStmt->execute();
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['error'] = "Le login existe déjà.";
        header("location: ../pages/admin/admin-users/manage-users.php");
        exit;
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $insertQuery = "INSERT INTO UTILISATEUR (nom_utilisateur, prenom_utilisateur, login, password) VALUES (:nom_utilisateur, :prenom_utilisateur, :login, :password)";
        $insertStmt = $connexion->prepare($insertQuery);
        $insertStmt->bindParam(":nom_utilisateur", $nom_utilisateur, PDO::PARAM_STR);
        $insertStmt->bindParam(":prenom_utilisateur", $prenom_utilisateur, PDO::PARAM_STR);
        $insertStmt->bindParam(":login", $login, PDO::PARAM_STR);
        $insertStmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);

        if ($insertStmt->execute()) {
            $_SESSION['success'] = "Utilisateur ajouté avec succès.";
            header("location: ../pages/admin/admin-users/manage-users.php");
            exit;
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'utilisateur.";
            header("location: ../pages/admin/admin-users/manage-users.php");
            exit;
        }
    }
} else {
    // Si la méthode de la requête n'est pas POST, redirigez vers le formulaire.
    header("location: ../pages/admin/admin-users/manage-users.php");
    exit;
}
?>
