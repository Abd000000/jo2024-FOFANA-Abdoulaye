<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID du sport est fourni dans l'URL
if (!isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "ID de l'athlete manquant.";
    header("Location: manage-athletes.php");
    exit();
}

$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifiez si l'ID du athlete est un entier valide
if (!$id_athlete && $id_athlete !== 0) {
    $_SESSION['error'] = "ID du athlete invalide.";
    header("Location: manage-athletes.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_STRING);
    $prenomAthlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_STRING);
    $paysAthlete = filter_input(INPUT_POST, 'paysAthlete', FILTER_SANITIZE_STRING);
    $genreAthlete = filter_input(INPUT_POST, 'genreAthlete', FILTER_SANITIZE_STRING);

    // Vérifiez si le nom du athlete est vide
    if (empty($nomAthlete)) {
        $_SESSION['error'] = "Le nom de l'athlete ne peut pas être vide.";
        header("Location: modify-athletes.php?id_athlete=$id_athlete");
        exit();
    }

    try {
        // Vérifiez si le sport existe déjà
        $queryCheck = "SELECT * FROM ATHLETE WHERE nom_athlete = :nomAthlete AND prenom_athlete = :prenomAthlete";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statementCheck->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'athlete existe déjà.";
            header("Location: modify-athletes.php?id_athlete=$id_athlete");
            exit();
        }

        // Requête pour mettre à jour le athlete
        $query = "UPDATE ATHLETE SET nom_athlete = :nomAthlete, prenom_athlete = :prenomAthlete WHERE id_athlete = :idAthlete";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'athlete a été modifié avec succès.";
            header("Location: manage-athletes.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'athlete.";
            header("Location: modify-athletes.php?id_athlete=$id_athlete");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-athletes.php?id_athlete=$id_athlete");
        exit();
    }
}

// Récupérez les informations du sport pour affichage dans le formulaire
try {
    $queryAthlete = "SELECT nom_athlete, prenom_athlete FROM ATHLETE WHERE id_athlete = :idAthlete";
    $statementAthlete = $connexion->prepare($queryAthlete);
    $statementAthlete->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
    $statementAthlete->execute();

    if ($statementAthlete->rowCount() > 0) {
        $athlete = $statementAthlete->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Athlete non trouvé.";
        header("Location: manage-athletes.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-athletes.php");
    exit();
}
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
    <title>Modifier un athlete - Jeux Olympiques 2024</title>
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
        <h1>Modifier un athlete</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-athletes.php?id_athlete=<?php echo $id_athlete; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet athlete?')">

            <label for=" nomAthlete">Nom de l'athlete :</label>
            <input type="text" name="nomAthlete" id="nomAthlete"
                value="<?php echo htmlspecialchars($athlete['nom_athlete']); ?>" required>

            <label for=" prenomAthlete">Prenom de l'athlète :</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete" 
            value="<?php echo htmlspecialchars($athlete['prenom_athlete']); ?>" required>

            <label for="paysAthlete">Pays de l'athlète :</label>
            <select name="paysAthlete" id="paysAthlete">
                <?php
                // Requête pour récupérer la liste des épreuves
                $query = "SELECT id_pays, nom_pays FROM PAYS";
                $statement = $connexion->prepare($query);
                $statement->execute();

                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($row['id_pays'] == $athlete['id_pays']) ? 'selected' : '';
                    echo "<option value='{$row['id_pays']}' $selected>{$row['nom_pays']}</option>";
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
                    $selected = ($row['id_genre'] == $epreuve['id_genre']) ? 'selected' : '';
                    echo "<option value='{$row['id_genre']}'$selected>{$row['nom_genre']}</option>";
                }
                ?>
            </select>

            <input type="submit" value="Modifier l'athlete'">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-athletes.php">Retour à la gestion des athletes</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>