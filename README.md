### D-mocratieParticipative
## Projet universitaire de 2e année de "démocratie participative"

# Présentation :
Ce projet à été réalisé lors de ma 2e année de BUT informatique à l'IUT d'Orsay.
Il avait pour but la réalisation d'une application de démocratie participative en PHP, HTML et CSS, et en liant cette application à une base de donnée MySQL sur PHPMyAdmin.
Ce projet à été réalisé avec deux de mes camarades : Alexis TIRANT et Iry RABEHAJA.


# Mise en place 
Pour le bon fonctionnement de la base et de l'application, il faut dans un premier temps installer la base de données sur un serveur phpMyAdmin.
Y importer le fichier saes3-bmonod.sql

Ensuite, il faut modifier le fichier connexion.php dans DevWeb et y mettre les informations de votre base de données. 
	static private $hostname = 'localhost';
    static private $database = 'votre_nomDeBD';
    static private $login = 'votre_login';
    static private $password = 'votrePassWord';

Copiez l'intégralité des fichiers du sites (fichier DevWeb) sur un serveur pour pouvoir y accéder. 
Une fois fait, il faut accéder au site via le fichier routeur.php via cette page vous pourrez avoir accès a l'entièreté du site et de ses fonctionnalités.


# Application Java :

Pour l'application java, il faudra modifier le fichier connexion puis y modifier les informations pour joindre votre base de données.
Compilez et exécutez l'application via votre environnement Java.




