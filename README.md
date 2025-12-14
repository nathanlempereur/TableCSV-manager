# TableCSV-manager

## 📝 Éditeur de Fichier CSV en Temps Réel avec Sauvegarde Automatique

Ce projet est une solution légère et efficace pour éditer le contenu de petits fichiers CSV directement via une interface web, avec une fonctionnalité de sauvegarde automatique côté serveur (PHP).

Il est idéal pour les environnements de petite production ou les projets de formation (BTS SIO/SISR) nécessitant une gestion simple de données tabulaires sans base de données SQL.

---

## Fonctionnalités :

* **Chargement Dynamique :** Lecture du fichier `users.csv` via JavaScript (PapaParse).
* **Édition en Ligne :** Modification directe des cellules du tableau (`contenteditable="true"`).
* **Gestion des Lignes :**
    * Ajout de nouvelles lignes.
    * Suppression instantanée de lignes existantes.
* **Sauvegarde Automatique (Autosave) :**
    * Les modifications, ajouts et suppressions sont détectés immédiatement.
    * La sauvegarde est déclenchée 500ms après la dernière modification.
    * La persistance des données est gérée par un script PHP (`file_put_contents`) qui réécrit le fichier `users.csv`.
* **Design Professionnel :** Interface utilisateur propre et réactive.

---

## Installation et Prérequis :

Ce projet est une application **Serveur + Client** et ne peut pas être exécuté simplement en double-cliquant sur le fichier dans votre navigateur.

### Prérequis :

* **Un Serveur Web :** Apache, Nginx, ou tout autre serveur capable d'exécuter PHP.
* **PHP (version 7.0+) :** Nécessaire pour la logique de sauvegarde (`index.php`).

### Étapes d'Installation :

1.  **Cloner le Dépôt :**
    ```bash
    git clone [https://github.com/votre_nom_utilisateur/csv-editor-autosave-php.git](https://github.com/votre_nom_utilisateur/csv-editor-autosave-php.git)
    cd csv-editor-autosave-php
    ```

2.  **Placer les Fichiers :**
    * Placez `index.php` et `users.csv` dans le répertoire racine de votre hôte virtuel ou de votre serveur web (par exemple, `/var/www/html/`).

3.  **Créer le Fichier de Données :**
    * Créez un fichier nommé **`users.csv`** dans le même répertoire ou prenez celui déja présent.

    ```csv
    Nom,Prénom,Email
    Durand,Paul,paul.durand@example.com
    Martin,Sophie,sophie.martin@example.com
    ```

4.  **Configurer les Permissions (Étape CRUCIALE !) :**
    Le script PHP doit avoir les droits d'écriture sur le fichier `users.csv`. Sans cela, la sauvegarde échouera.

    Identifiez l'utilisateur de votre serveur web (souvent `www-data` ou `apache`).

    ```bash
    # Option recommandée : Donner l'écriture au groupe du serveur (ex: www-data)
    sudo chown :www-data users.csv
    sudo chmod 664 users.csv
    
    # Ou la méthode la plus simple mais moins sécurisée
    # chmod 666 users.csv
    ```

---

## Utilisation :

1.  Accédez à l'application via votre navigateur : `http://localhost/` (ou l'URL de votre hôte virtuel).
2.  Modifiez n'importe quelle cellule, ajoutez ou supprimez des lignes.
3.  Observez la zone de statut sous le tableau. Le message **"Sauvegarde automatique réussie !"** confirmera que vos changements ont été écrits dans le fichier `users.csv`.

---

## 🤝 Contribution

Les contributions sont les bienvenues ! Si vous trouvez un bug ou souhaitez ajouter une fonctionnalité (comme l'exportation ou la gestion des erreurs de formatage CSV), n'hésitez pas à soumettre une *Pull Request*.

---

## 📄 Licence

Ce projet est sous licence Libre !
