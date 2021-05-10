<?php
$noRecord="";
if (!isset($_SESSION['userId'])){

    treat_request($order,['pseudo','keyword','nom','prenom','password','email','create','nouveau']);
    $userId=0;
    $incompatible=false;
    // if user not in session then define $userId
    if (!(isset($_SESSION['userId']))){  // userId is not defined.
        // identification has been submitted
        // check user and password.
        // check if pseudo// password submitted if not leave equals to ""
        if (($order['pseudo']=="")||($order['password']=="")){
            $loginMsg="Un pseudo ou un mot de passe vide ne sont pas acceptés";
            $incompatible=false;
        }
        else{ // pseudo et mot de passe ne sont pas nuls
            //echo "pseudo not null";
            $query="select * from prod_user where pseudo='".$order["pseudo"]."'";
            $table=query_table($query);
            if (sizeof($table)==1){   // if pseudo does not exist
                //echo "pseudo not in database";
                //$doesPseudoExist=false;
                $pseudoMissing=true;
                $incompatible=true;
            }
            else{ // pseudo exists, check password
                //echo "pseudo exists";
                $realpassword=$table[1]['password'];
                if ($realpassword!=$order['password']){
                    //$connectionRefused=1; // password not ok
                    $incompatible=true;
                } 
                else{
                    $isPasswordOk=1;
                    $userId=$table[1]['id']; // password ok record user.
                    //echo "found";
                }
            }
        }
        if ($incompatible){$loginMsg="Le pseudo et le mot de passe sont incompatibles. Inscrivez-vous ou contactez le webmestre.";}
        
        // check if create has been submitted for initialisation
        if ($order['create']){
            if (($order['nom']=="")||($order['prenom']=="")||($order['email']=="")||($order['pseudo']=="")||($order['password']=="")){
                    $loginMsg="Veuillez renseigner tous les champs";
            }
            else{
                 if (!$pseudoMissing){
                    $loginMsg= "Vous êtes déjà inscrit. Contacter l'administrateur si vous avez perdu votre mot de passe";
                    if ($order['pseudo']!=$table[1]['pseudo']){
                        $createMsg= "Votre pseudo n'est pas ".$order['pseudo']." mais ".$table[1]['pseudo'];
                    }
                }
                else{   // pseudo found not in database, password also 
                        $query="INSERT INTO prod_user (nom,prenom,pseudo,password,email) 
                            VALUES ('".$order['nom']."','".$order['prenom']."','".$order['pseudo']."','".$order['password']."','".$order['email']."');";
                        simple_query($query);
                        $query="SELECT * from  prod_user where pseudo='".$order['pseudo']."';";
                        $table=query_table($query);
                        $userId=$table[1]["id"];
                        $_SESSION['userId']=$userId;
                        $loginMsg="Vous êtes inscrit(e)";
                }
            }
        } 
        header("index.php"); 
    }
    else{
        $userId=$_SESSION['userId'];
    }
    // User Id has been defined. 
    //UserId is null so ask for identification
    if ($userId==0){
        
        echo myHeader();
        echo "<form method='post'>";
        
        if ($loginMsg){
            echo $loginMsg;
        }
        else{
            echo "<h2>Identifiez-vous pour continuer ou inscrivez-vous.</h2>";
        }
        echo "<table>
        <tr><td>Pseudo</td><td><input name=pseudo value=".$order['pseudo']." ></input></td></tr>
        <tr><td>Mot de Passe</td><td><input type=password name=password ></input></td></tr>";
        if ($order['nouveau']!=""){
            echo "<tr><td>Nom</td><td><input name=nom value=".$order['nom']."></input></td></tr>
            <tr><td>Prenom</td><td><input name=prenom value=".$order['prenom']." ></input></td></tr>
            <tr><td>e-mail</td><td><input name=email value=".$order['email']."></input></td></tr>";
            echo "<tr><td></td><td><button name=create value=1 >connexion</button></td></tr>";
            echo "<input type='hidden' name='nouveau' value=1></input>";
            
        }
        else{
                echo "<tr><td></td><td><button name=nouveau value=1 >Pour s'inscrire</button></td></tr>";
                echo "<tr><td></td><td><button name=connexion value=1 >connexion</button></td></tr>";
                echo "<script> $('button[name =\"connexion\"]').focus();</script>";
                echo "<tr><td></td><td><a href='0005-producteurs/0004-forgotmail.php'>mot de passe oublié</a></td></tr>";
        }
        echo "
        </table>
        </form>
        </body></html>";
        die();
    }
    else{ // get full info on user
        $_SESSION['userId']=$userId;
        // get info for user
        $query="SELECT * from  prod_user where id='".$userId."';";
        $table=query_table($query);
        //displayinhtml($table);
        $_SESSION['userInfo']=['nom'=>$table[1]['nom'],'prenom'=>$table[1]['prenom'],'email'=>$table[1]['email'],'admin'=>$table[1]['admin'],'userRights'=>$table[1]['userRights'],'userId'=>$userId];
        $query="SELECT * FROM (SELECT *,TIMEDIFF(CURRENT_TIMESTAMP,timestamp) as diff FROM prod_user_connexions) as A WHERE user='$userId' AND diff<'00:05:00'";
        //echo $query;
        $table=query_table($query);
        if (sizeof($table)==1){
            $query="INSERT INTO prod_user_connexions (user,IP) VALUE ('$userId','".$_SERVER['REMOTE_ADDR']."');";
            simple_query($query);
        }
        
    }
}
?>
