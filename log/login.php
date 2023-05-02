<?php
//permet d'autoriser l'usage des variables de session
session_start();
require_once("../outils/function.php");

//si qq appuie sur le bouton ENTRER du formulaire de connexion
if(isset($_POST['submit']))
	{
	if( !empty($_POST['login_compte']) && !empty($_POST['pass_compte']))
		{
		login($_POST['login_compte'],$_POST['pass_compte']);
		}
	}

include('login.html');
?>
