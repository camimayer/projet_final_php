<?php
    session_start();
    require_once '../databasemanager.php';

    // Vérifie si l'utilisateur est connecté
    if (!isset($_SESSION['Courriel'])) {
        header("Location: login.php");
        exit();
    }

    $email = $_SESSION['Courriel'];
    $databaseManager = new DatabaseManager();
    $userData = $databaseManager->getUserData($email);

    $stNoTelMaison = isset($userData['NoTelMaison']) ? substr($userData['NoTelMaison'], 0, 14) : '';
    $stNoTelTravail = isset($userData['NoTelTravail']) ? substr($userData['NoTelTravail'], 0, 14) : '';
    $stNoTelCellulaire = isset($userData['NoTelCellulaire']) ? substr($userData['NoTelCellulaire'], 0, 14) : '';

    // Fonction pour vérifier si le numéro de téléphone est privé
    function isPrivate($phoneNumber) {
        $phoneNumber = isset($phoneNumber) ? $phoneNumber : '';
        return substr($phoneNumber, -1) === 'N';
    }

    // Fonction pour extraire le numéro de poste
    function getPoste($phoneNumber) {
        $phoneNumber = isset($phoneNumber) ? $phoneNumber : '';
        if (strpos($phoneNumber, '#') !== false) {
            $parts = explode('#', $phoneNumber);
            return substr($parts[1], 0, 4); 
        }
        return '';
    }
?>

<!DOCTYPE html>
<html>
<?php    
    require_once 'header.php';
?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
    <br><br>
    <div class="container-fluid my-4">
        <div id="divMAJProfile" class="col-4 m-auto">
            <h1 class="text-center" id="titreMAJProfile">Mise à jour du profile</h1>
            <br />
            <br />
            <form id="formMAJProfile" action="EnvoieMAJProfile.php" method="POST">
                <div class="form-group row">
                    <label class="col-4 col-form-label" for="tbEmail">Email</label>
                    <div class="col-6">
                        <input type="text" readonly class="form-control" id="tbEmail" name="tbEmail" value="<?= htmlspecialchars($userData['Courriel']) ?>">
                    </div>
                    <p id="errEmail" class="text-danger font-weight-bold"></p>
                </div>

                <div class="form-group row">
                    <label for="tbMdp" class="col-4 col-form-label">Nouveaux mot de passe</label>
                    <div class="col-6">
                        <a href="ModifierMdP.php">Accédez à la modification de mot de passe ici</a>
                    </div>
                    <p id="errMdp" class="text-danger font-weight-bold"></p>
                </div>

                <div class="form-group row">
                    <label for="tbStatut" class="col-4 col-form-label">Statut</label>
                    <div class="col-6">
                        <select class="form-control" id="tbStatut" name="tbStatut">
                            <option value="2" <?= $userData['Statut'] == 2 ? 'selected' : '' ?>>Cadre</option>
                            <option value="3" <?= $userData['Statut'] == 3 ? 'selected' : '' ?>>Employé de soutien</option>
                            <option value="4" <?= $userData['Statut'] == 4 ? 'selected' : '' ?>>Enseignant</option>
                            <option value="5" <?= $userData['Statut'] == 5 ? 'selected' : '' ?>>Professionnel</option>
                        </select>
                        
                    </div>
                    <p id="errStatut" class="text-danger font-weight-bold"></p>
                </div>

                <div class="form-group row">
                    <label for="tbNoEmp" class="col-4 col-form-label">Numéro Emplois</label>
                    <div class="col-6">
                        <input type="text" class="form-control" id="tbNoEmp" name="tbNoEmp" value="<?= $userData['NoEmpl'] ?>">
                    </div>
                    <p id="errNoEmp" class="text-danger font-weight-bold"></p>
                </div>

                <div class="form-group row">
                    <label for="tbNom" class="col-4 col-form-label">Nom</label>
                    <div class="col-6">
                        <input type="text" class="form-control" id="tbNom" name="tbNom" value="<?= htmlspecialchars($userData['Nom']) ?>" required>
                    </div>
                    <p id="errNom" class="text-danger font-weight-bold"></p>
                </div>

                <div class="form-group row">
                    <label for="tbPrenom" class="col-4 col-form-label">Prénom</label>
                    <div class="col-6">
                        <input type="text" class="form-control" id="tbPrenom" name="tbPrenom" value="<?= htmlspecialchars($userData['Prenom']) ?>" required>
                    </div>
                    <p id="errPrenom" class="text-danger font-weight-bold"></p>
                </div>

                <div class="form-group row">
                    <label for="tbTelM" class="col-4 col-form-label">Numéro Téléphone Maison</label>
                    <div class="col-6">
                        <input type="text" class="form-control" id="tbTelM" name="tbTelM" pattern="\([0-9]{3}\) [0-9]{3}\-[0-9]{4}" value="<?= htmlspecialchars($stNoTelMaison) ?>">
                        <label for="cbTelMP" class="col-5 col-form-label">Privé ?</label>
                        <input type="checkbox" class="" id="cbTelMP" name="cbTelMP" <?= isPrivate($userData['NoTelMaison']) ? 'checked' : '' ?>>
                    </div>
                    <p id="errTelM" class="text-danger font-weight-bold"></p>
                </div>

                <div class="form-group row">
                    <label for="tbTelT" class="col-4 col-form-label">Numéro Téléphone Bureau</label>
                    <div class="col-6">
                        <input type="text" class="form-control" id="tbTelT" name="tbTelT" pattern="\([0-9]{3}\) [0-9]{3}\-[0-9]{4}" value="<?= htmlspecialchars($stNoTelTravail) ?>">
                        <div class="col row mt-3">
                            <label for="tbTelTPoste" class="col-4 col-form-label">Poste</label>
                            <input type="text" class="col-4 form-control" id="tbTelTPoste" name="tbTelTPoste" pattern="[0-9]{4}" value="<?= getPoste($userData['NoTelTravail']) ?>">
                        </div>                       
                        <label for="cbTelTP" class="col-5 col-form-label">Privé ?</label>
                        <input type="checkbox" class="" id="cbTelTP" name="cbTelTP" <?= isPrivate($userData['NoTelTravail']) ? 'checked' : '' ?>>
                    </div>
                    <p id="errTelT" class="text-danger font-weight-bold"></p>
                </div>

                <div class="form-group row">
                    <label for="tbTelC" class="col-4 col-form-label">Numéro Téléphone Cellulaire</label>
                    <div class="col-6">
                        <input type="text" class="form-control" id="tbTelC" name="tbTelC" pattern="\([0-9]{3}\) [0-9]{3}\-[0-9]{4}" value="<?= htmlspecialchars($stNoTelCellulaire) ?>">
                        <label for="cbTelCP" class="col-5 col-form-label">Privé ?</label>
                        <input type="checkbox" class="" id="cbTelCP" name="cbTelCP" <?= isPrivate($userData['NoTelCellulaire']) ? 'checked' : '' ?>>
                    </div>
                    <p id="errTelC" class="text-danger font-weight-bold"></p>
                </div>

                <div class="d-flex">
                    <button type="submit" class="btn btn-primary" id="btnMAJProfile">Enregistrer</button>
                </div>
            </form>
            <br />
            <p><a href="ListeAnnonces.php">Retour à la liste des annonces</a></p>
        </div>
    </div>
    <br>
</body>
</html>
