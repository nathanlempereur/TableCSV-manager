<?php
// ==========================================================
// 1. LOGIQUE DE SAUVEGARDE CÔTÉ SERVEUR (PHP)
// ==========================================================

$csvFile = 'users.csv';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csv_data'])) {
    
    $csvData = $_POST['csv_data'];

    // Tentative d'écriture du fichier
    $result = file_put_contents($csvFile, $csvData);

    if ($result !== false) {
        // Succès
        echo "Sauvegarde automatique réussie !"; 
    } else {
        // Échec (Souvent dû aux permissions)
        http_response_code(500); 
        echo "ÉCHEC CRITIQUE DE SAUVEGARDE. Vérifiez les permissions d'écriture sur {$csvFile}.";
    }
    
    // Arrêter l'exécution pour que le JavaScript reçoive la réponse PHP uniquement
    exit;
}

// ==========================================================
// 2. LOGIQUE D'AFFICHAGE CÔTÉ CLIENT (HTML/JavaScript)
// ==========================================================
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion et Affichage des Données CSV (Autosave)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Nouvelle Palette de Couleurs */
        :root {
            --primary-color: #3f51b5; /* Bleu Indigo */
            --primary-light: #7986cb;
            --secondary-color: #f57c00; /* Orange Profond */
            --success-color: #4caf50;
            --error-color: #f44336;
            --background-light: #fafafa;
            --text-dark: #212121;
            --text-light: #ffffff;
            --border-color: #e0e0e0;
        }

        /* Styles de base */
        body {
            font-family: 'Roboto', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            background-color: var(--background-light);
            color: var(--text-dark);
        }

        .container {
            max-width: 1300px;
            margin: auto;
            background: var(--text-light);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-light);
            padding-bottom: 12px;
            margin-bottom: 30px;
            font-weight: 500;
        }

        /* Styles des actions et boutons */
        .actions {
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
        }

        button {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s, box-shadow 0.3s;
            text-transform: uppercase;
        }

        .add-row {
            background-color: var(--success-color);
            color: var(--text-light);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
        }

        .add-row:hover {
            background-color: #388e3c;
            box-shadow: 0 6px 12px rgba(76, 175, 80, 0.4);
        }
        
        .delete-row {
            background-color: var(--error-color);
            color: var(--text-light);
            padding: 8px 12px;
            font-size: 0.8em;
            text-transform: none;
        }

        .delete-row:hover {
            background-color: #d32f2f;
        }

        /* Styles du Tableau */
        table {
            width: 100%;
            border-collapse: separate; /* Permet un border-radius */
            border-spacing: 0;
            margin-top: 20px;
            overflow: hidden; /* Assure que le border-radius s'applique */
            border-radius: 8px;
        }

        th,
        td {
            border: 1px solid var(--border-color);
            padding: 15px;
            text-align: left;
        }

        th {
            background-color: var(--primary-color);
            color: var(--text-light);
            text-transform: uppercase;
            font-size: 0.9em;
            font-weight: 500;
            border-color: var(--primary-color);
        }
        /* Style spécifique pour la première et dernière colonne d'en-tête */
        th:first-child { border-top-left-radius: 8px; }
        th:last-child { border-top-right-radius: 8px; }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e3f2fd; /* Bleu très clair au survol */
            transition: background-color 0.3s;
        }

        td {
            cursor: text;
        }
        /* Mettre en évidence la cellule éditée */
        td:focus {
            background-color: #fffde7; /* Jaune très pâle */
            outline: 2px solid var(--secondary-color);
            border-radius: 4px;
        }

        /* Message de statut */
        #statusMessage {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            min-height: 20px;
            display: flex;
            align-items: center;
        }

        .success {
            background-color: #e8f5e9; /* Vert très clair */
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .error {
            background-color: #ffebee; /* Rouge très clair */
            color: var(--error-color);
            border: 1px solid var(--error-color);
        }

        .saving {
            background-color: #fff3e0; /* Orange très clair */
            color: var(--secondary-color);
            border: 1px solid var(--secondary-color);
        }
        
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>
</head>

<body>
    <div class="container">
        <h1><i class="fa fa-table"></i> Gestionnaire de Données CSV (Autosave)</h1>

        <div class="actions">
            <button class="add-row" onclick="addRow()"><i class="fa fa-plus-circle"></i> Ajouter une ligne</button>
        </div>

        <div id="csvTable">
            Chargement des données...
        </div>
        
        <div id="statusMessage">Prêt.</div>
        
    </div>

    <script>
        const serverScriptPath = window.location.pathname; 
        const csvFilePath = '<?php echo $csvFile; ?>'; 
        
        const tableContainer = document.getElementById('csvTable');
        const statusMessage = document.getElementById('statusMessage');
        let currentFields = [];
        let saveTimeout; // Pour gérer le délai de l'autosave

        // --- Fonctions DOM (Ajout/Suppression) ---

        function addRow() {
            const tbody = document.querySelector('#csvTable tbody');
            if (!tbody) return;

            let newRow = document.createElement('tr');
            
            currentFields.forEach(field => {
                let cell = document.createElement('td');
                cell.setAttribute('contenteditable', 'true');
                cell.textContent = '';
                newRow.appendChild(cell);
            });

            newRow.appendChild(createDeleteCell());
            tbody.appendChild(newRow);

            // Déclencher la sauvegarde immédiatement après l'ajout
            saveData();
        }

        function deleteRow(button) {
            const row = button.closest('tr');
            if (row) {
                row.remove();
                // Déclencher la sauvegarde immédiatement après la suppression
                saveData(); 
            }
        }

        function createDeleteCell() {
            let deleteCell = document.createElement('td');
            deleteCell.style.width = '100px'; 
            let deleteButton = document.createElement('button');
            deleteButton.className = 'delete-row';
            deleteButton.innerHTML = '<i class="fa fa-trash"></i> Supprimer';
            deleteButton.onclick = function() { deleteRow(this); }; 
            deleteCell.appendChild(deleteButton);
            return deleteCell;
        }

        // --- Fonction de Sauvegarde Automatique ---

        /**
         * Déclenche la sauvegarde après un délai pour regrouper les modifications.
         */
        function autoSave() {
            // Annule tout sauvegarde en attente
            clearTimeout(saveTimeout);
            // Programme une nouvelle sauvegarde dans 500ms
            saveTimeout = setTimeout(saveData, 500);
            showStatus('<i class="fa fa-hourglass-half"></i> Modification détectée, préparation de la sauvegarde...', 'saving');
        }


        // --- Fonctions de Traitement des Données ---

        function getTableDataAsCSV() {
            const table = document.querySelector('#csvTable table');
            if (!table) return null;

            let data = [];
            const headerRow = table.querySelector('thead tr');
            const dataRows = table.querySelectorAll('tbody tr');

            currentFields = [];
            headerRow.querySelectorAll('th').forEach((th, index) => {
                // Évite la colonne 'Actions'
                if (index < headerRow.querySelectorAll('th').length - 1) { 
                    currentFields.push(th.textContent);
                }
            });

            dataRows.forEach(row => {
                let rowData = {};
                row.querySelectorAll('td').forEach((td, index) => {
                    if (index < currentFields.length) { 
                        rowData[currentFields[index]] = td.textContent;
                    }
                });
                data.push(rowData);
            });
            
            return Papa.unparse({
                fields: currentFields,
                data: data
            });
        }

        /**
         * Envoie les données CSV au script serveur (index.php) pour l'écriture.
         */
        function saveData() {
            const csvData = getTableDataAsCSV();
            if (!csvData) {
                showStatus('<i class="fa fa-times-circle"></i> Erreur: Aucun tableau de données trouvé pour la sauvegarde.', 'error');
                return;
            }

            // Mettre à jour le statut avant l'envoi
            showStatus('<i class="fa fa-save"></i> Sauvegarde en cours...', 'saving');

            fetch(serverScriptPath, { // Envoie au script lui-même (index.php)
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csv_data=${encodeURIComponent(csvData)}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.text();
            })
            .then(responseText => {
                // Le PHP renvoie le message de statut
                if (responseText.includes('Sauvegarde automatique réussie')) {
                    showStatus(`<i class="fa fa-check-circle"></i> ${responseText}`, 'success');
                } else {
                    showStatus(`<i class="fa fa-exclamation-triangle"></i> ${responseText}`, 'error');
                }
            })
            .catch(error => {
                showStatus(`<i class="fa fa-plug"></i> Erreur réseau/serveur: ${error.message}`, 'error');
            });
        }

        function showStatus(message, type) {
            statusMessage.innerHTML = message;
            statusMessage.className = '';
            statusMessage.classList.add(type);
            
            if (type === 'success' || type === 'error') {
                setTimeout(() => {
                    statusMessage.innerHTML = 'Prêt.';
                    statusMessage.className = '';
                }, 4000);
            }
        }

        // --- Fonction de Chargement et Initialisation ---

        function parseAndBuildTable() {
             Papa.parse(csvFilePath, {
                download: true,
                header: true,
                skipEmptyLines: true,
                complete: function (results) {
                    if (results.data.length === 0 && results.meta.fields.length === 0) {
                        tableContainer.innerHTML = '<p>Le fichier CSV est vide ou introuvable.</p>';
                        return;
                    }
                    
                    currentFields = results.meta.fields;

                    let tableHTML = '<table>';
                    tableHTML += '<thead><tr>';
                    results.meta.fields.forEach(field => {
                        tableHTML += `<th>${field}</th>`;
                    });
                    tableHTML += `<th style="width: 100px;">Actions</th>`;
                    tableHTML += '</tr></thead>';

                    tableHTML += '<tbody>';
                    results.data.forEach(row => {
                        tableHTML += '<tr>';
                        results.meta.fields.forEach(field => {
                            tableHTML += `<td contenteditable="true">${row[field]}</td>`;
                        });
                        tableHTML += `<td><button class="delete-row" onclick="deleteRow(this)"><i class="fa fa-trash"></i> Supprimer</button></td>`;
                        tableHTML += '</tr>';
                    });
                    tableHTML += '</tbody>';
                    tableHTML += '</table>';

                    tableContainer.innerHTML = tableHTML;
                    
                    // Une fois le tableau construit, nous ajoutons l'écouteur d'événement global
                    addEventListeners();
                },
                error: function (error, file) {
                    tableContainer.innerHTML = `<p class="error"><i class="fa fa-exclamation-circle"></i> Erreur lors du chargement : ${error.code} - ${error.message}.</p>`;
                }
            });
        }

        function addEventListeners() {
            // Écoute l'événement 'input' sur le conteneur du tableau pour détecter les modifications de texte dans n'importe quelle cellule éditée.
            tableContainer.addEventListener('input', autoSave);
        }

        // Exécuter la fonction principale au chargement
        parseAndBuildTable();
    </script>

</body>

</html>