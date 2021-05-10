# popachat
1) Dans le repertoire popachat/0021-functions/501-retrieveFunctions.php on a la fonction
(lignes 3 à 11)

function openLink(){
    //phpinfo();
    $link = mysqli_connect(ini_get("mysqli.default_host"),ini_get("mysqli.default_user"),
                    ini_get("mysqli.default_pw"),'mypop');
    if (!($link)){
    echo "<p>Echec de Connection</p>";
    }
    return $link;
}

Il faut definir le nom d'utilisateur à la base mysql: "mysqli.default_user" et le mot de passe "mysqli.default_pw" sur apache2 dans php.ini
/etc/php/7.4/apache2/php.ini

"mysqli.default_user=...utilisateur
mysqli.default_pw=...mot de passe
mysqli.default_host=localhost : hote

2) Par ailleurs, pour que les menus fonctionnent, dans le fichier /0021-functions/0505-miscellaneousFunctions.php,
il faut préciser l'url d'appel 
ligne 4:
$address=['popbis.marly.ml','popachat.ml','mypopmarly.ml'];

3) Le fichier mypop.sql.gz a été généré par mysqldump.
