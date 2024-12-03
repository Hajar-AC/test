
# Task Manager API

Ce projet est une API Symfony permettant de gérer des tâches. Il offre des fonctionnalités CRUD (Créer, Lire, Modifier, Supprimer) avec des réponses JSON, ainsi que la pagination et la recherche dans les tâches.

---

## **Instructions pour lancer l'application**

1. **Cloner le projet**
   git clone url du projet
   cd task-manager

2. **Installer les dépendances**
   composer install

3. **Configurer la base de données**
   - Ouvrez le fichier `.env` à la racine du projet et configurez la variable `DATABASE_URL` avec vos informations MySQL :
     
     DATABASE_URL="mysql://root:@127.0.0.1:3306/task_manager"
     

4. **Créer la base de données et exécuter les migrations**
   
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
  

5. **Lancer le serveur Symfony**
   
   symfony server:start
   
   - L'application sera disponible à l'adresse : [http://127.0.0.1:8000](http://127.0.0.1:8000).


## **Instructions pour exécuter les tests**

1. **Configurer la base de données de test**
   - Symfony utilise une base de données distincte pour les tests. Par défaut, le fichier `.env.test` contient la configuration suivante :
     
     DATABASE_URL="mysql://root:@127.0.0.1:3306/task_manager_test"
     
   - Créez la base de données de test :
     
     php bin/console doctrine:database:create --env=test
     php bin/console doctrine:migrations:migrate --env=test
     

2. **Exécuter les tests**
   - Lancer les tests avec PHPUnit :
     
     php bin/phpunit
     

---

## **Choix techniques**

### **1. Structure API Restful**
- Chaque fonctionnalité est exposée via des endpoints RESTful retournant des réponses JSON.
- Les méthodes HTTP utilisées respectent les standards REST :
  - `GET` : Lire les tâches ou une tâche spécifique.
  - `POST` : Créer une nouvelle tâche.
  - `PUT` : Modifier une tâche existante.
  - `DELETE` : Supprimer une tâche.

### **2. Gestion des données avec Doctrine**
- Utilisation de **Doctrine ORM** pour gérer les entités `Task` et effectuer les interactions avec la base de données.
- Une approche basée sur des migrations a été utilisée pour versionner et appliquer les modifications au schéma de base de données.

### **3. Pagination et Recherche**
- La pagination est implémentée avec **Doctrine Paginator** pour limiter le nombre de tâches renvoyées par page.
- La recherche permet de filtrer les tâches par titre ou description grâce à une requête SQL utilisant `LIKE`.

### **4. Validation des données**
- Des validations Symfony sont appliquées aux entités pour garantir l'intégrité des données :
  - Le titre doit contenir entre 3 et 255 caractères.
  - Le statut est limité à trois valeurs (`todo`, `in_progress`, `done`).

### **5. Tests**
- Des tests fonctionnels ont été écrits pour valider les fonctionnalités principales (CRUD, pagination, recherche) en utilisant PHPUnit et le client de test Symfony.

---

## **Endpoints de l'API**

### **1. Lister les tâches**
- **Méthode** : `GET`
- **URL** : `/api/task`
- **Paramètres optionnels** :
  - `page` : numéro de la page.
  - `search` : texte pour rechercher dans le titre ou la description.
- **Exemple** :
  
  GET /api/task?search=this&page=1
  

### **2. Créer une tâche**
- **Méthode** : `POST`
- **URL** : `/api/task/new`
- **Corps** (JSON) :
  ```json
  {
    "title": "Titre de la tâche",
    "description": "Description de la tâche",
    "status": "todo"
  }
  ```

### **3. Modifier une tâche**
- **Méthode** : `PUT`
- **URL** : `/api/task/{id}/edit`
- **Corps** (JSON) :
  ```json
  {
    "title": "Titre mis à jour",
    "description": "Description mise à jour",
    "status": "done"
  }
  ```

### **4. Supprimer une tâche**
- **Méthode** : `DELETE`
- **URL** : `/api/task/{id}`

---

## **Notes importantes**
- Par défaut, 10 tâches sont renvoyées par page dans la pagination.
- Les statuts valides pour une tâche sont :
  - `todo`
  - `in_progress`
  - `done`.
- Les erreurs de validation ou les données invalides retournent une réponse HTTP 400.


