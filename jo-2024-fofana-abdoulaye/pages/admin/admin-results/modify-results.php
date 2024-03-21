<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'ID de l'athlète et de l'épreuve est fourni dans l'URL
if (!isset($_GET['id_athlete']) || !isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID du résultat manquant.";
    header("Location: manage-results.php");
    exit();
}

$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);
$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'athlète et de l'épreuve sont des entiers valides
if (!$id_athlete && $id_athlete !== 0) {
    $_SESSION['error'] = "ID de l'athlète invalide.";
    header("Location: manage-results.php");
    exit();
} elseif (!$id_epreuve && $id_epreuve !== 0) {
    $_SESSION['error'] = "ID de l'épreuve invalide.";
    header("Location: manage-results.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées

    $resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_STRING);
    $prenomAthlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_STRING);
    $nomPays = filter_input(INPUT_POST, 'nomPays', FILTER_SANITIZE_STRING);
    $nomSport = filter_input(INPUT_POST, 'nomSport', FILTER_SANITIZE_STRING);
    $nomEpreuve = filter_input(INPUT_POST, 'nomEpreuve', FILTER_SANITIZE_STRING);




    try {


        // Requête pour mettre à jour le résultat
        $query = "UPDATE PARTICIPER SET resultat = :resultat WHERE id_athlete = :id_athlete AND id_epreuve = :id_epreuve";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":resultat", $resultat, PDO::PARAM_STR);
        $statement->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);
        $statement->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le résultat a été modifié avec succès.";
            header("Location: manage-results.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du résultat.";
            header("Location: modify-results.php?id_athlete=$id_athlete&id_epreuve=$id_epreuve");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-results.php?id_athlete=$id_athlete&id_epreuve=$id_epreuve");
        exit();
    }
}

// Récupérez les informations du résultat pour affichage dans le formulaire
try {
    // Requête pour récupérer le sport, l'épreuve et le pays associés à l'athlète et à l'épreuve
    $queryDetails = "SELECT SPORT.id_sport, SPORT.nom_sport, EPREUVE.id_epreuve, EPREUVE.nom_epreuve, PAYS.id_pays, PAYS.nom_pays
                    FROM PARTICIPER
                    INNER JOIN ATHLETE ON PARTICIPER.id_athlete = ATHLETE.id_athlete
                    INNER JOIN EPREUVE ON PARTICIPER.id_epreuve = EPREUVE.id_epreuve
                    INNER JOIN SPORT ON EPREUVE.id_sport = SPORT.id_sport
                    INNER JOIN PAYS ON ATHLETE.id_pays = PAYS.id_pays
                    WHERE PARTICIPER.id_athlete = :idAthlete AND PARTICIPER.id_epreuve = :idEpreuve";

    $statementDetails = $connexion->prepare($queryDetails);
    $statementDetails->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
    $statementDetails->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);
    $statementDetails->execute();

    if ($statementDetails->rowCount() > 0) {
        $details = $statementDetails->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Détails non trouvés.";
        header("Location: manage-results.php");
        exit();
    }


    // Récupérez les informations du résultat
    $queryParticiper = "SELECT PARTICIPER.*, ATHLETE.nom_athlete, ATHLETE.prenom_athlete
                        FROM PARTICIPER
                        INNER JOIN ATHLETE ON PARTICIPER.id_athlete = ATHLETE.id_athlete
                        WHERE PARTICIPER.id_athlete = :idAthlete AND PARTICIPER.id_epreuve = :idEpreuve";

    $statementParticiper = $connexion->prepare($queryParticiper);
    $statementParticiper->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
    $statementParticiper->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);
    $statementParticiper->execute();

    if ($statementParticiper->rowCount() > 0) {
        $resultatDB = $statementParticiper->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Résultat non trouvé.";
        header("Location: manage-results.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-results.php");
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
    <title>Modifier un Résultat - Jeux Olympiques 2024</title>
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
        <h1>Modifier un Résultat</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-results.php?id_epreuve=<?php echo $id_epreuve; ?>&id_athlete=<?php echo $id_athlete; ?>"
            method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce résultat?')">

            <label for="nomAthlete">Athlète :</label>
            <select name="nomAthlete" id="nomAthlete" required>
                <?php
                // Requête pour récupérer la liste des athlètes
                $query_athletes = "SELECT id_athlete, nom_athlete, prenom_athlete FROM ATHLETE";
                $statement_athletes = $connexion->prepare($query_athletes);
                $statement_athletes->execute();

                while ($row_athlete = $statement_athletes->fetch(PDO::FETCH_ASSOC)) {
                    // Vérifie si l'athlète actuel est sélectionné
                    $selected = ($row_athlete['id_athlete'] == $resultatDB['id_athlete']) ? 'selected' : '';
                    // Affiche le nom et le prénom de l'athlète dans une option de la liste déroulante
                    echo "<option value='{$row_athlete['id_athlete']}' $selected>{$row_athlete['nom_athlete']} {$row_athlete['prenom_athlete']}</option>";
                }
                ?>
            </select>


            <!-- Ajoutez d'autres options ici si nécessaire -->
            </select>

            <label for="nomSport">Sport :</label>
            <select name="nomSport" id="nomSport" required>
                <?php
                // Requête pour récupérer la liste des sports
                $query_sport = "SELECT id_sport, nom_sport FROM SPORT";
                $statement_sport = $connexion->prepare($query_sport);
                $statement_sport->execute();

                while ($row_sport = $statement_sport->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($row_sport['id_sport'] == $details['id_sport']) ? 'selected' : '';
                    echo "<option value='{$row_sport['id_sport']}' $selected>{$row_sport['nom_sport']}</option>";
                }
                ?>
            </select>


            <label for="nomEpreuve">Epreuve :</label>
            <select name="nomEpreuve" id="nomEpreuve" required>
                <?php
                // Requête pour récupérer la liste des sports
                $query_epreuve = "SELECT id_epreuve, nom_epreuve FROM EPREUVE";
                $statement_epreuve = $connexion->prepare($query_epreuve);
                $statement_epreuve->execute();

                while ($row_epreuve = $statement_epreuve->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($row_epreuve['id_epreuve'] == $details['id_epreuve']) ? 'selected' : '';
                    echo "<option value='{$row_epreuve['id_epreuve']}' $selected>{$row_epreuve['nom_epreuve']}</option>";
                }
                ?>
            </select>




            <label for="resultat">Résultat :</label>
            <input type="text" name="resultat" id="resultat"
                value="<?php echo htmlspecialchars($resultatDB['resultat']); ?>" required>


            <input type="submit" value="Modifier le Résultat">
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