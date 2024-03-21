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
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_STRING);
    $prenomAthlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_STRING);
    $paysAthlete = filter_input(INPUT_POST, 'paysAthlete', FILTER_SANITIZE_STRING);
    $genreAthlete = filter_input(INPUT_POST, 'genreAthlete', FILTER_SANITIZE_STRING);

    // Vérifiez si le nom du sport est vide
    if (empty($nomAthlete)) {
        $_SESSION['error'] = "Le nom de l'athlète ne peut pas être vide.";
        header("Location: add-athletes.php");
        exit();
    }

    if (empty($prenomAthlete)) {
        $_SESSION['error'] = "Le prénom de l'athlète ne peut pas être vide.";
        header("Location: add-athletes.php");
        exit();
    }

    if (empty($paysAthlete)) {
        $_SESSION['error'] = "Le pays de l'athlète ne peut pas être vide.";
        header("Location: add-athletes.php");
        exit();
    }

    if (empty($genreAthlete)) {
        $_SESSION['error'] = "Le genre de l'athlète ne peut pas être vide.";
        header("Location: add-athletes.php");
        exit();
    }

    try {
        // Vérifiez si l'athlète existe déjà
        $queryCheckAthlete = "SELECT * FROM ATHLETE WHERE nom_athlete = :nomAthlete AND prenom_athlete = :prenomAthlete";
            $statementCheckAthlete = $connexion->prepare($queryCheckAthlete);
            $statementCheckAthlete->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
            $statementCheckAthlete->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
            $statementCheckAthlete->execute();


        if ($statementCheckAthlete->rowCount() > 0) {
            $_SESSION['error'] = "L'athlète existe déjà.";
            header("Location: add-athletes.php");
            exit();
        } else {

             // Requête pour ajouter un athlète
            $queryInsertAthlete = "INSERT INTO ATHLETE (nom_athlete, prenom_athlete, id_pays, id_genre) VALUES (:nomAthlete, :prenomAthlete, :paysAthlete, :genreAthlete)";
            $statementInsertAthlete = $connexion->prepare($queryInsertAthlete);
            $statementInsertAthlete->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
            $statementInsertAthlete->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
            $statementInsertAthlete->bindParam(":paysAthlete", $paysAthlete, PDO::PARAM_STR);
            $statementInsertAthlete->bindParam(":genreAthlete", $genreAthlete, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statementInsertAthlete->execute()) {
                $_SESSION['success'] = "L'athlète a été ajouté avec succès.";
                header("Location: manage-athletes.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'athlète.";
                header("Location: add-athletes.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-athletes.php");
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
    <title>Ajouter un Sport - Jeux Olympiques 2024</title>
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
        <h1>Ajouter un athlète</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-athletes.php" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet athlète?')"">
            <label for=" nomAthlete">Nom de l'athlète :</label>
            <input type="text" name="nomAthlete" id="nomAthlete" required>

            <label for=" prenomAthlete">Prenom de l'athlète :</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete" required>

            <label for="paysAthlete">Pays de l'athlète :</label>
            <select name="paysAthlete" id="paysAthlete">
                <?php
                // Requête pour récupérer la liste des épreuves
                $query = "SELECT id_pays, nom_pays FROM PAYS";
                $statement = $connexion->prepare($query);
                $statement->execute();

                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['id_pays']}'>{$row['nom_pays']}</option>";
                }
                ?>
            </select>

            <label for="genreAthlete">Genre de l'athlète :</label>
            <select name="genreAthlete" id="genreAthlete">
                <?php
                // Requête pour récupérer la liste des épreuves
                $query = "SELECT id_genre, nom_genre FROM GENRE";
                $statement = $connexion->prepare($query);
                $statement->execute();

                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['id_genre']}'>{$row['nom_genre']}</option>";
                }
                ?>
            </select>


            <input type="submit" value="Ajouter l'athlète">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-athletes.php">Retour à la gestion des athlètes</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>