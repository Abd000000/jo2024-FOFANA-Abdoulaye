<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID du sport est fourni dans l'URL
if (!isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID de l'epreuve manquante.";
    header("Location: manage-events.php");
    exit();
}

$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

// Vérifiez si l'ID du sport est un entier valide
if (!$id_epreuve && $id_epreuve !== 0) {
    $_SESSION['error'] = "ID de l'epreuvre invalide.";
    header("Location: manage-events.php");
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
        $_SESSION['error'] = "Le nom de l'epreuve ne peut pas être vide.";
        header("Location: modify-events.php?id_epreuve=$id_epreuve");
        exit();
    }

    try {
        // Vérifiez si le sport existe déjà
        $queryCheck = "SELECT id_epreuve FROM EPREUVE WHERE nom_epreuve = :nomEpreuve AND id_epreuve <> :idEpreuve";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
        $statementCheck->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'epreuve existe déjà.";
            header("Location: modify-events.php?id_epreuve=$id_epreuve");
            exit();
        }

        // Requête pour mettre à jour le sport
        $query = "UPDATE EPREUVE SET nom_epreuve = :nomEpreuve, date_epreuve = :dateEpreuve, heure_epreuve = :heureEpreuve, id_lieu = :lieuEpreuve, id_sport = :sportEpreuve WHERE id_epreuve = :idEpreuve";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
        $statement->bindParam(":dateEpreuve", $dateEpreuve, PDO::PARAM_STR);
        $statement->bindParam(":heureEpreuve", $heureEpreuve, PDO::PARAM_STR);
        $statement->bindParam(":lieuEpreuve", $lieuEpreuve, PDO::PARAM_STR);
        $statement->bindParam(":sportEpreuve", $sportEpreuve, PDO::PARAM_STR);
        $statement->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le sport a été modifié avec succès.";
            header("Location: manage-events.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du sport.";
            header("Location: modify-events.php?id_epreuve=$id_epreuve");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-events.php?id_epreuve=$id_epreuve");
        exit();
    }
}

// Récupérez les informations du sport pour affichage dans le formulaire
try {
    $queryEpreuve = "SELECT nom_epreuve, date_epreuve, heure_epreuve, id_lieu, id_lieu FROM EPREUVE WHERE id_epreuve = :idEpreuve";
    $statementEpreuve = $connexion->prepare($queryEpreuve);
    $statementEpreuve->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);
    $statementEpreuve->execute();

    if ($statementEpreuve->rowCount() > 0) {
        $epreuve = $statementEpreuve->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Epreuve non trouvé.";
        header("Location: manage-events.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-events.php");
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
    <title>Modifier une épreuve - Jeux Olympiques 2024</title>
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
        <h1>Modifier une épreuve</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-events.php?id_epreuve=<?php echo $id_epreuve; ?>" method="post" 
        onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet épreuve ?');">

            <label for=" nomEpreuve">Nom de l'epreuve :</label>
            <input type="text" name="nomEpreuve" id="nomEpreuve" required value="<?php echo htmlspecialchars($epreuve['nom_epreuve']); ?>">

            <label for="dateEpreuve">Date de l'épreuve :</label>
            <input type="date" name="dateEpreuve" id="dateEpreuve" required value="<?php echo htmlspecialchars($epreuve['date_epreuve']); ?>">

            <label for="heureEpreuve">Heure de l'épreuve :</label>
            <input type="time" name="heureEpreuve" id="heureEpreuve" required value="<?php echo htmlspecialchars($epreuve['heure_epreuve']); ?>">

            <label for="lieuEpreuve">Lieu :</label>
            <select name="lieuEpreuve" id="lieuEpreuve">
                <?php
                // Requête pour récupérer la liste des sports
                $query_lieu = "SELECT id_lieu, nom_lieu FROM LIEU";
                $statement_lieu = $connexion->prepare($query_lieu);
                $statement_lieu->execute();

                while ($row_lieu = $statement_lieu->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($row_lieu['id_lieu'] == $epreuve['id_lieu']) ? 'selected' : ''; 
                    echo "<option value='{$row_lieu['id_lieu']}' $selected>{$row_lieu['nom_lieu']}</option>";
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
                    $selected = ($row_sport['id_sport'] == $epreuve['id_sport']) ? 'selected' : '';
                    echo "<option value='{$row_sport['id_sport']}' $selected>{$row_sport['nom_sport']}</option>";
                }
                ?>
            </select>
            
            <input type="submit" value="Modifier le Sport">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-events.php">Retour à la gestion des epreuves</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>