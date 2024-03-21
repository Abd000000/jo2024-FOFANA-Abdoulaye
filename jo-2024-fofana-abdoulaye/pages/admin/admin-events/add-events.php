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
    $nomEpreuve = filter_input(INPUT_POST, 'nomEpreuve', FILTER_SANITIZE_STRING);
    $dateEpreuve = filter_input(INPUT_POST, 'dateEpreuve', FILTER_SANITIZE_STRING);
    $heureEpreuve = filter_input(INPUT_POST, 'heureEpreuve', FILTER_SANITIZE_STRING);
    $lieuEpreuve = filter_input(INPUT_POST, 'lieuEpreuve', FILTER_SANITIZE_STRING);
    $sportEpreuve = filter_input(INPUT_POST, 'sportEpreuve', FILTER_SANITIZE_STRING);

    // Vérifiez si le nom du sport est vide
    if (empty($nomEpreuve)) {
        $_SESSION['error'] = "Le nom de l'épreuve ne peut pas être vide.";
        header("Location: add-events.php");
        exit();
    }

    try {
        // Vérifiez si le sport existe déjà
        $queryCheck = "SELECT id_epreuve FROM EPREUVE WHERE nom_epreuve = :nomEpreuve";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'epreuve existe déjà.";
            header("Location: add-events.php");
            exit();
        } else {

            // Requête pour ajouter un sport
            $query = "INSERT INTO EPREUVE (nom_epreuve, date_epreuve, heure_epreuve, id_lieu, id_sport ) VALUES (:nomEpreuve, :dateEpreuve, :heureEpreuve, :lieuEpreuve, :sportEpreuve)";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":dateEpreuve", $dateEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":heureEpreuve", $heureEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":lieuEpreuve", $lieuEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":sportEpreuve", $sportEpreuve, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'epreuve a été ajouté avec succès.";
                header("Location: manage-events.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout de l'epreuve.";
                header("Location: add-events.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-events.php");
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
    <title>Ajouter une épreuve - Jeux Olympiques 2024</title>
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
        <h1>Ajouter une épreuve</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-events.php" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet epreuve ?')"">
            <label for=" nomEpreuve">Nom de l'epreuve :</label>
            <input type="text" name="nomEpreuve" id="nomEpreuve" required>

            <label for="dateEpreuve">Date de l'épreuve :</label>
            <input type="date" name="dateEpreuve" id="dateEpreuve" required>

            <label for="heureEpreuve">Heure de l'épreuve :</label>
            <input type="time" name="heureEpreuve" id="heureEpreuve" required>

            <label for="lieuEpreuve">Lieu :</label>
            <select name="lieuEpreuve" id="lieuEpreuve">
                <?php
                // Requête pour récupérer la liste des sports
                $query_lieu = "SELECT id_lieu, nom_lieu FROM LIEU";
                $statement_lieu = $connexion->prepare($query_lieu);
                $statement_lieu->execute();

                while ($row_lieu = $statement_lieu->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row_lieu['id_lieu']}'>{$row_lieu['nom_lieu']}</option>";
                }
                ?>
            </select>

            <label for="sportEpreuve">Sport :</label>
            <select name="sportEpreuve" id="sportEpreuve">
                <?php
                // Requête pour récupérer la liste des sports
                $query_sport = "SELECT id_sport, nom_sport FROM SPORT";
                $statement_sport = $connexion->prepare($query_sport);
                $statement_sport->execute();

                while ($row_sport = $statement_sport->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row_sport['id_sport']}'>{$row_sport['nom_sport']}</option>";
                }
                ?>
            </select>

            


            <input type="submit" value="Ajouter l'epreuve">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-events.php">Retour à la gestion des épreuves</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>