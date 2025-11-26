Pour le bon fonctionnement de la base et de l'application, il faut dans un premier temps installer la base de données sur un serveur phpMyAdmin.
	Y importer le fichier saes3-bmonod.sql

Site web :

Ensuite, il faut modifier le fichier connexion.php dans DevWeb et y mettre les informations de votre base de données. 
	static private $hostname = 'localhost';
    static private $database = 'votre_nomDeBD';
    static private $login = 'votre_login';
    static private $password = 'votrePassWord';


Copiez l'intégralité des fichiers du sites (fichier DevWeb) sur un serveur pour pouvoir y accéder. 
	
Une fois fait, il faut accéder au site via le fichier routeur.php via cette page vous pourrez avoir accès a l'entièreté du site et de ses fonctionnalités.

Application Java :

Pour l'application java, il faudra modifier le fichier connexion puis y modifier les informations pour joindre votre base de données.
Compilez et exécutez l'application via votre environnement Java.