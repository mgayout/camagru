# camagru

TODO : 

- Common features 

- [X] Sauvegarde des mdp crypté dans la db
- [ ] Prévention des injections XSS
- [ ] Protection sur le téléchargement de fichier non souhaité
- [ ] Prévention des injections SQL
- [ ] Prévention contre tout types de requêtes externes

- User features

- [X] Création d'un utilisateur
    - [X] Adresse email valide
    - [X] Nom d'utilisateur unique
    - [X] Mot de passe complexe
- [O] Envoi d'un mail pour valider la création du compte
- [X] Connexion à la plateforme
- [O] Mail de renouvellement de mdp
- [X] Déconnexion en 1 clic
- [X] Modification du mail, nom utilisateur ou mdp

- Galery features

- [ ] Création d'une gallerie de photos (utilisateur non connecté)
    - [ ] Message d'erreur : 
        - [ ] Commenter un poste
        - [ ] Liker un poste
- [ ] Affichage du contenu pour tous
- [ ] Contenu trier par date
- [ ] Notification par mail d'un like et commentaire
    - [X] Modifiable dans les parametre du compte
- [ ] Minimum 5 éléments par page

- Editing features

- [X] Accessible uniquement par les profils connectés
    - [X] Rejet des profils non connectés

- Main :
- [ ] Bouton Webcam
- [ ] Bouton Image PC
- [ ] Liste des éléments supperposables

- Side : 
- [ ] Affichage des Images et/ou capture webcam
    - [ ] Sélectionnable ou supprimable

- [ ] Nécessite au moins 1 éléments supperposables pour que l'image soit valide
- [ ] La superposition doit se faire côté server

- Constraints and Mandatory things

- [X] [SERVER] [LANGUAGE] PHP standard library
- [X] [SERVER] [FRAMEWORK] PHP standard library
- [X] [CLIENT] [LANGUAGE] HTML, CSS, JavaScript (Natives API)
- [X] [CLIENT] [FRAMEWORK] CSS, no forbidden JavaScript
- [X] [DOCKER] Déploiement du site avec 1 commande (docker-compose)

- Bonus (Idée PDF)

- [ ] Rendre les échanges avec le serveur dynamiques grâce à AJAX, sans recharger toute la page.
- [ ] Afficher en temps réel les modifications directement sur l'aperçu de la webcam.
- [ ] Mettre en place un défilement infini dans la galerie.
- [ ] Permettre aux utilisateurs de partager leurs images sur les réseaux sociaux.
- [ ] Générer ou afficher une image GIF animée.