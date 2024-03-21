<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $id_athlete = filter_input(INPUT_POST, 'id_athlete', FILTER_VALIDATE_INT);
    $id_sport = filter_input(INPUT_POST, 'id_sport', FILTER_VALIDATE_INT);
    $id_epreuve = filter_input(INPUT_POST, 'id_epreuve', FILTER_VALIDATE_INT);
    $resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);

  


    try {
        // Vérifiez si le résultat existe déjà
// Vérifiez si le résultat existe déjà
$queryCheck = "SELECT id_epreuve, id_athlete, resultat FROM PARTICIPER WHERE id_epreuve = :id_epreuve AND id_athlete = :id_athlete AND resultat = :resultat";
$statementCheck = $connexion->prepare($queryCheck);
$statementCheck->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);
$statementCheck->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);
$statementCheck->bindParam(":resultat", $resultat, PDO::PARAM_STR);
$statementCheck->execute();

if ($statementCheck->rowCount() > 0) {
    $_SESSION['error'] = "Le résultat existe déjà.";
    header("Location: add-results.php");
    exit();
}
 else {
            // Requête pour ajouter un résultat
            $query = "INSERT INTO PARTICIPER (id_athlete, id_epreuve, resultat) VALUES (:id_athlete, :id_epreuve, :resultat)";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);
            $statement->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);
            $statement->bindParam(":resultat", $resultat, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "Le résultat a été ajouté avec succès.";
                header("Location: manage-results.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout du résultat.";
                header("Location: add-results.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-results.php");
        exit();
    }
}

// Afficher les erreurs en PHP
// (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Ajouter un Résultat - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
            <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-gender.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter un Résultat</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-results.php" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce résultat?')">
            <!-- Ajoutez les listes déroulantes pour choisir l'athlète, le sport et l'épreuve -->
            <label for="id_athlete">Athlète :</label>
            <select name="id_athlete" id="id_athlete">
                <?php
                // Requête pour récupérer la liste des athlètes
                $query_athletes = "SELECT id_athlete, nom_athlete, prenom_athlete FROM ATHLETE";
                $statement_athletes = $connexion->prepare($query_athletes);
                $statement_athletes->execute();

                while ($row_athlete = $statement_athletes->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row_athlete['id_athlete']}'>{$row_athlete['nom_athlete']} {$row_athlete['prenom_athlete']}</option>";
                }
                ?>
            </select>

            <label for="id_sport">Sport :</label>
            <select name="id_sport" id="id_sport">
                <?php
                // Requête pour récupérer la liste des sports
                $query_sports = "SELECT id_sport, nom_sport FROM SPORT";
                $statement_sports = $connexion->prepare($query_sports);
                $statement_sports->execute();

                while ($row_sport = $statement_sports->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row_sport['id_sport']}'>{$row_sport['nom_sport']}</option>";
                }
                ?>
            </select>

            <label for="id_epreuve">Epreuve :</label>
            <select name="id_epreuve" id="id_epreuve">
                <?php
                // Requête pour récupérer la liste des sports
                $query_epreuve = "SELECT id_epreuve, nom_epreuve FROM EPREUVE";
                $statement_epreuve = $connexion->prepare($query_epreuve);
                $statement_epreuve->execute();

                while ($row_epreuve = $statement_epreuve->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row_epreuve['id_epreuve']}'>{$row_epreuve['nom_epreuve']}</option>";
                }
                ?>
            </select>

                
            <!-- Ajoutez un champ pour le résultat -->
            <label for="resultat">Résultat :</label>
            <input type="text" name="resultat" id="resultat" required>

            <input type="submit" value="Ajouter le Résultat">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-results.php">Retour à la gestion des résultats</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>