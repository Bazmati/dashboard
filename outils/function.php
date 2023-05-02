<?php


//===========================================================
function connexion_bdd()
    {
    require_once("connect.php");
    //si numéro de port
    $connexion = mysqli_connect(SERVEUR,LOGIN,PASSE,BASE) or die("Error " . mysqli_error($connexion));
    //si pas de numéro de port    
    // $connexion = mysqli_connect(SERVEUR,LOGIN,PASSE,BASE) or die("Error " . mysqli_error($connexion));
    
    return $connexion;
    }
//============================================================

function security($chaine){
    $connexion=connexion_bdd();
    $security=addcslashes(mysqli_real_escape_string($connexion,$chaine), "%_");
    mysqli_close($connexion);
    return $security;
}

//=============================================================

function afficher_liste_site(){
    //connexion a la BDD
    $connexion = connexion_bdd();
    //selection des contact dans l'ordre décroissant des date
    $requete = "SELECT * FROM liste_des_sites_web ORDER BY acquisition_liste DESC";
    //execution de la requete
    $resultat = mysqli_query($connexion, $requete);
    //exploitation des réultats rapporter par le serveur
    //à ce stade, se poserla question : on aura 1 seule ou plusieurs lignes?
    $liste_sites = "<div class=\"card col-md-12 table-responsive p-0\" style=\"height: 450px;\">";
    $liste_sites .= "<table id=\"tab_liste_sites\" class=\"table table-hover w-100 table-striped table-head-fixed text-wrap\">";
    $liste_sites .= "<thead>\n";
    $liste_sites .= "<tr>\n";
    $liste_sites .= "<th>Domaine</th>\n";
    $liste_sites .= "<th>Home Page</th>\n";
    $liste_sites .= "<th>Acquisition</th>\n";
    $liste_sites .= "<th>Groupe</th>\n";
    $liste_sites .= "<th>Thématique</th>\n";
    $liste_sites .= "<th>Action</th>\n";
    $liste_sites .="</tr>\n";
    $liste_sites .= "</thead>\n";
    while($row= mysqli_fetch_object($resultat)){
        $liste_sites .= "<tbody>\n";
        $liste_sites .= "<tr>\n";
        $liste_sites .= "<td>".stripslashes($row->domaine_liste)."</td>\n";
        $liste_sites .= "<td>".stripslashes($row->home_page_liste)."</td>\n";
        $liste_sites .= "<td>".stripslashes($row->acquisition_liste)."</td>\n";
        $liste_sites .= "<td>".stripslashes($row->groupe_liste)."</td>\n";
        $liste_sites .= "<td>".stripslashes($row->thematique_liste)."</td>\n";
        $liste_sites .= "<td>\n";
        $liste_sites .="<a class=\"btn btn-info btn-sm col-md-4\" href=\"dashboard.php?module=afficher_liste_site&action=modifier_site&id_liste=".$row->id_liste."\"><i class=\"fas fa-pencil-alt\"></i> Edit</a>\n";
		$liste_sites .="&nbsp;&nbsp;&nbsp;";
        $liste_sites .= "<a class=\"btn btn-block btn-warning btn-sm\" href=\"dashboard.php?action=supprimer_site&id_liste=".$row->id_liste."\"><i class=\"fas fa-trash\"> Delete</a></td>\n";
        $liste_sites .="</tr>\n";
        $liste_sites .= "</tbody>\n";

    }// la boucle while est la mieux pour les base de donné
    $liste_sites .= "</table>";
    $liste_sites .= "</div>";


    mysqli_close($connexion);
    return $liste_sites;
}

//=============================================================

function ajouter_site(){
    $connexion=connexion_bdd();
    $requete = "INSERT INTO liste_des_sites_web SET
    domaine_liste='".$_POST['domaine_liste']."',
    home_page_liste='".$_POST['home_page_liste']."',
    acquisition_liste='".$_POST['acquisition_liste']."',
    groupe_liste='".$_POST['groupe_liste']."',
    thematique_liste='".$_POST['thematique_liste']."'";        
        //on execute la requete
        mysqli_query($connexion, $requete);
        mysqli_close($connexion);
}

//=============================================================

function recherche(){
    $connexion = connexion_bdd();
    $requete = "SELECT home_page_liste FROM liste_des_sites_web";
    // $requete = "SELECT * FROM liste_des_sites_web";
    $results = mysqli_query($connexion, $requete);


    if(!empty($_POST['mot-clé'])){
        while($row = $results->fetch_object()){
            $home_pages = ($row);
            foreach ($home_pages as $home_page) {
                $url = $home_page;
                $url .= '/wp-json/wp/v2/posts?search=';
                $url .= $_POST['mot-clé'];
                $url .= '&per_page=100';
                
                $result = file_get_contents($url);
                $resultats = json_decode($result, true);
                echo "<div class=\"card table-responsive p-0\" style=\"height: 300px;\">";
                echo "<table class=\"table table-bordered table-striped table-head-fixed text-wrap\">";
                echo "<thead>\n";
                echo "<tr>\n";
                echo "<th>\n";
                echo "$home_page";
                echo "</th>\n";
                echo "<th>Date de modification</th>\n";
                echo "<th> 
                        <div class=\"card-tools\">
                            <button type=\"button\" class=\"btn btn-tool\" data-card-widget=\"remove\" title=\"Remove\">
                                <i class=\"fas fa-times\"></i>
                            </button>
                        </div>
                    </th>\n";
                echo "</tr>\n";
                echo "</thead>\n";
                foreach ($resultats as $resultat){
                    // echo '<pre>';
                    // echo $resultat['link'];
                    // echo '</pre>';
                    echo "<tbody >\n";
                    echo "<tr>\n";               
                    echo "<td><a href=\"";
                    print_r ($resultat['link']);
                    echo "\">";
                    print_r ($resultat['title']['rendered']);
                    echo "</a></td>\n";
                    echo "<td>\n";
                    print_r ($resultat['modified']);
                    echo "</td>\n";
                    echo "</tr>\n";
                }                                                          
                echo "</tbody>\n";
                echo "</table>\n";
                echo "</div>";
            }
        }

    mysqli_close($connexion);
    }
}

//=============================================================


function ranxplorer_seo(){
  
        $connexion = connexion_bdd();
        $requete = "SELECT domaine_liste FROM liste_des_sites_web";
        $result = mysqli_query($connexion, $requete);
        $requete1 = "SELECT cle_api FROM api WHERE api.nom_api='ranxplorer'";
        $result1 = mysqli_query($connexion, $requete1);
        $cle = mysqli_fetch_object($result1);
        
        $compteur=0;
        
        $url="";
        while($home_page = mysqli_fetch_object($result)){
            $url='https://api.ranxplorer.com/v1/seo?search=';
            $url .=$home_page->domaine_liste;;
            $compteur++;
            $url .='&limit=2&sortby=Desc_Date';
    
                $ch = curl_init();   
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Accept: application/json',
                    'X-Ranxplorer-Token: '.$cle->cle_api.''));
                    $result1 = curl_exec($ch);
                    
                    $requete1 = "INSERT INTO ranxplorer SET historique_ranxplorer = '".$result1."', id_site = '".$compteur."'";
                    $result1=mysqli_query($connexion, $requete1);
        }
    
    
    mysqli_close($connexion);
}

//=============================================================

function seobserver(){

        $connexion = connexion_bdd();
        $requete = "SELECT domaine_liste FROM liste_des_sites_web";
        $result = mysqli_query($connexion, $requete);
        $requete1 = "SELECT cle_api FROM api WHERE api.nom_api='seobserver'";
        $result1 = mysqli_query($connexion, $requete1);
        $cle = mysqli_fetch_object($result1);
        $home_pages = mysqli_fetch_all($result);
            $item_types="";
            $compteur=0;
        foreach ($home_pages as $home_page) {
            
            $item_types.="{\"item_type\":\"domain\", \"item_value\":\"";
                $item_types.=$home_page[0];
                $item_types.="/\"}, ";
            }
            $item_type = substr($item_types,0, -2);
                $opts = array(
                    'http'=>array(
                        'method'=>"POST",
                        'header'=>"X-SEObserver-key: ".$cle->cle_api."",
                        'content'=>'['.$item_type.']'
                        )
                    );
                    
            $context = stream_context_create($opts);
            
            $datas = file_get_contents("https://api1.seobserver.com/backlinks/metrics.json", false, $context);
            $seobserver_data = json_decode($datas, true);
            
            foreach ($seobserver_data['data'] as $data){
                $compteur++;
                $result_data = json_encode($data);          
                $requete1 = "INSERT INTO seobserver SET historique_seobserver = '".$result_data."', id_site = '".$compteur."'";
                $result = mysqli_query($connexion, $requete1);
            }
        
        
        mysqli_close($connexion);
}



//=============================================================

function matomo_world_visit(){
    $token_auth = 'cafef47327a2baababd418c43842b6d3';
    
    // we call the REST API and request the 100 first keywords for the last month for the idsite=62
    $url = "https://stats.mondomweb.com/";
    $url .= "?module=API&method=VisitTime.getVisitInformationPerLocalTime";
    $url .= "&idSite=1&period=month&date=today&segment=visitDuration>=100";
    $url .= "&format=JSON&filter_limit=10";
    $url .= "&token_auth=$token_auth";
    
    $fetched = file_get_contents($url);
    $content = json_decode($fetched,true);
    
    // case error
    if (!$content) {
        print("No data found");
    }else{    
    print("<h1>fréquence des visites</h1>\n");
    var_dump ($content);
    }
}

//=============================================================

function matomo_kpi_visit(){
    $token_auth = 'cafef47327a2baababd418c43842b6d3';
    
    // we call the REST API and request the 100 first keywords for the last month for the idsite=62
    $url = "https://stats.mondomweb.com/";
    $url .= "?module=API&method=API.getMetadata";
    $url .= "&idSite=1&apiModule=Referrers&apiAction=getKeywords";
    $url .= "&format=xml";
    $url .= "&token_auth=$token_auth";
    
    $fetched = file_get_contents($url);
    $content = $fetched;
    
    // case error
    if (!$content) {
        print("No data found");
    }else{
        print("<h1>Visite depuis le mot clef</h1>\n");
        echo'<pre>';
        print($content);
        echo '</pre';

    }
    
    
}

// ====détecter l'extension du fichier================

	function fichier_type($uploadedFile)
	{
	$tabType = explode(".", $uploadedFile);
	$nb=sizeof($tabType)-1;
	$typeFichier=$tabType[$nb];
	 if($typeFichier == "jpeg")
	   {
	   $typeFichier = "jpg";
	   }
	$extension=strtolower($typeFichier);
	return $extension;
	}

//============================================

function redimage($img_src,$img_dest,$dst_w,$dst_h,$quality)
{
if(!isset($quality))
    {
    $quality=100;
    }
   $extension=fichier_type($img_src);

   // Lit les dimensions de l'image
   $size = @GetImageSize($img_src);
   $src_w = $size[0];
   $src_h = $size[1];
   // Crée une image vierge aux bonnes dimensions   truecolor
   $dst_im = @ImageCreatetruecolor($dst_w,$dst_h);
   imagealphablending($dst_im, false);
   imagesavealpha($dst_im, true);

   // Copie dedans l'image initiale redimensionnée

   if($extension=="jpg")
     {
     $src_im = @ImageCreateFromJpeg($img_src);
     imagecopyresampled($dst_im,$src_im,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);

     // Sauve la nouvelle image
     @ImageJpeg($dst_im,$img_dest,$quality);
     }
   if($extension=="png")
     {
     $src_im = @ImageCreateFromPng($img_src);
     imagecopyresampled($dst_im,$src_im,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);

     // Sauve la nouvelle image
     @ImagePng($dst_im,$img_dest,0);
     }
   if($extension=="gif")
     {
     $src_im = @ImageCreateFromGif($img_src);
     imagecopyresampled($dst_im,$src_im,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);

     // Sauve la nouvelle image
     @ImagePng($dst_im,$img_dest,0);
     }

   // Détruis les tampons
   @ImageDestroy($dst_im);
   @ImageDestroy($src_im);
}
	//===========================pour se loguer=======================================================

	function login($login,$password)
	{
		$connexion=connexion_bdd();
		$login=security($login);
		$password=security($password);

		$requete="SELECT * FROM comptes WHERE login_compte= '" . $login . "' AND pass_compte=SHA1('" . $password . "')";
		$resultat=mysqli_query($connexion, $requete);
		$nb=mysqli_num_rows($resultat);

		if($nb==0)
			{
			return false;
			}
		else
			{
			$ligne=mysqli_fetch_object($resultat);

			//on stocke en mémoire de session les infos que l'on souhaite afficher sur l'accueil du back
			$_SESSION['id_compte']=$ligne->id_compte;
			$_SESSION['prenom_compte']=$ligne->prenom_compte;
			$_SESSION['nom_compte']=$ligne->nom_compte;
			if(!empty($ligne->fichier_compte)){
				$avatar = str_replace("_o","_s",$ligne->fichier_compte);
				$_SESSION['fichier_compte']="<img src=\"" .$avatar. "\" alt=\"\"/>";
			}else{
				$_SESSION['fichier_compte']="<span class=\"dashicons dashicons-admin-users\"></span>";
			}
			header("location:../dashboard.php");
			return true;
			}
		mysqli_close($connexion);
	}

	//======================================

	function afficher_comptes(){
		//connexion à la BDD
		$connexion=connexion_bdd();
		//selection des contacts dans l'odre décroissant des dates
		$requete="SELECT * FROM comptes ORDER BY nom_compte ASC";
		//execution de la requete
		$resultat=mysqli_query($connexion,$requete);
		//exploitation des résultats rapportés par le SERVEUR
		//à ce stade, se poser la question : on aura 1 seule ligne ou plusieurs lignes ?

        $liste_comptes="<div class=\"card table-responsive p-0\" style=\"height: 300px;\">";
        $liste_comptes.="<div class=\"card-header\">";
        $liste_comptes.="<h3 class=\"card-title\">Liste des comptes</h3>";
        $liste_comptes.="</div>";
		$liste_comptes.="<table id=\"tab_resultats\" class=\"table table-bordered table-striped table-head-fixed text-wrap\">\n";
		$liste_comptes.="<tr>\n";
		$liste_comptes.="<th>Nom</th>\n";
		$liste_comptes.="<th>Login</th>\n";
		$liste_comptes.="<th>Avatar</th>\n";
		$liste_comptes.="<th>Action</th>\n";
		$liste_comptes.="<tr>\n";
		while($row=mysqli_fetch_object($resultat))
			{
			$liste_comptes.="<tr>\n";
			$liste_comptes.="<td>".stripslashes($row->nom_compte." ".$row->prenom_compte)."</td>\n";
			$liste_comptes.="<td>".$row->login_compte."</td>\n";
			if(!empty($row->fichier_compte)){
				$image_s = str_replace("_o","_s",$row->fichier_compte);
				$liste_comptes.="<td><img src=\"".$image_s."\" alt=\"\"/></td>\n";
			}else{
				$liste_comptes.="<td><span class=\"dashicons dashicons-admin-users\"></span></td>\n";
			}
			$liste_comptes.="<td class=\"col-md-2\">";
			$liste_comptes.="<a class=\"btn btn-info btn-sm col-md-4\" href=\"dashboard.php?module=comptes&action=modifier_compte&id_compte=".$row->id_compte."\"><i class=\"fas fa-pencil-alt\"></i> Edit</a>\n";
			$liste_comptes.="&nbsp;&nbsp;&nbsp;";
			$liste_comptes.="<a class=\"btn btn-warning btn-sm col-md-4\" href=\"dashboard.php?module=comptes&action=supprimer_compte&id_compte=".$row->id_compte."\"><i class=\"fas fa-trash\"> Delete</a>";
			$liste_comptes.="</td>\n";
			$liste_comptes.="</tr>\n";
			}
		$liste_comptes.="</table>\n";
        $liste_comptes.="</div>\n";
		mysqli_close($connexion);
		return $liste_comptes;
	}

	//======================================

    function afficher_home(){
		//connexion à la BDD
		$connexion=connexion_bdd();
        

		//selection des tables avec correspondance
        $requete2 = "SELECT * FROM liste_des_sites_web as website
                    LEFT JOIN ranxplorer as ranx 
                    ON ranx.id_site = website.id_liste AND ranx.id_ranxplorer = (
                        SELECT MAX(id_ranxplorer) FROM ranxplorer as r WHERE r.id_site = website.id_liste
                    )
                    LEFT JOIN seobserver as obs 
                    ON obs.id_site = website.id_liste AND obs.id_seobserver = (
                        SELECT MAX(id_seobserver) FROM seobserver as o WHERE o.id_site = website.id_liste
                    )";

        $resultats = mysqli_query($connexion, $requete2);
        $traffic1="class=\"traffic1\"";
        $traffic2="class=\"traffic2\"";
                        
        $home="<div class=\"row\">";
        $home.="<div class=\"col-12\">";
        $home.="<div class=\"card table-responsive\">";
        $home.="<div class=\"card-header\">";
        $home.="<h3 class=\"card-title\">Vu d'ensemble</h3>";
        $home.="</div>";
        $home.="<div class=\"card-body\">";       
		$home.="<table id=\"example1\" class=\"table table-bordered table-striped\">\n";
        $home.="<thead>\n";
		$home.="<tr>\n";
		$home.="<th>X</th>\n";
		$home.="<th>Domaine</th>\n";
		$home.="<th>Acquisit.</th>\n";
		$home.="<th>Grp</th>\n";
        $home.="<th>Thématique</th>\n";
        $home.="<th>Traf. Org.</th>\n";
        $home.="<th>Mots Clés</th>\n";
        $home.="<th>CF</th>\n";
        $home.="<th>TF</th>\n";
        $home.="<th>BL</th>\n";
        $home.="<th>RD</th>\n";
        $home.="<th>Topic 1</th>\n";
        $home.="<th>Topic 2</th>\n";
		$home.="</tr>\n";
        $home.="</thead>\n";
        $home.="<tbody>\n";
		while ($rows = mysqli_fetch_array($resultats)){
            $home .= "<tr>";
            $home .= "<td>".$rows[0]."</td>\n";
            $home .= "<td>".$rows[1]."</td>\n";
            $home .= "<td>".$rows[3]."</td>\n";
            $home .= "<td>".$rows[4]."</td>\n";
            $home .= "<td>".$rows[5]."</td>\n";
            $resultat=json_decode($rows[8], true);
            $dif1 = $resultat['data'][1]['Est'] - $resultat['data'][0]['Est'];
            $dif2 = $resultat['data'][1]['Nbkw'] - $resultat['data'][0]['Nbkw'];
            if($resultat['data'][0]['Est'] > $resultat['data'][1]['Est']){
                
                $home .= "<td id=\"traffic\" ".$traffic1."><span id=\"pop\" class=\"badge badge-danger \">".$dif1."</span><a class=\"deco\" href=\"https://ranxplorer.com/domaine/syntheseseo/".$resultat['metas']['search']."\" target=\"_blank\"><i class=\"fas fa-caret-down\"></i></a>".$resultat['data'][0]['Est']."</td>\n";
                
            }else{
                $home .= "<td id=\"traffic\" ".$traffic2."><span id=\"pop\" class=\"badge badge-success \">".$dif1."</span><a class=\"deco\" href=\"https://ranxplorer.com/domaine/syntheseseo/".$resultat['metas']['search']."\" target=\"_blank\"><i class=\"fas fa-caret-up\"></i></a>".$resultat['data'][0]['Est']."</td>\n";
            }
            if($resultat['data'][0]['Nbkw']>$resultat['data'][1]['Nbkw']){

                $home .= "<td id=\"mots_cle\" ".$traffic1."><span id=\"pop\" class=\"badge badge-danger \">".$dif2."</span><a class=\"deco\" href=\"https://ranxplorer.com/domaine/syntheseseo/".$resultat['metas']['search']."\" target=\"_blank\"><i class=\"fas fa-caret-down\"></i></a>".$resultat['data'][0]['Nbkw']."</td>\n";
            }else{
                $home .= "<td id=\"mots_cle\" ".$traffic2."><span id=\"pop\" class=\"badge badge-success \">".$dif2."</span><a class=\"deco\" href=\"https://ranxplorer.com/domaine/syntheseseo/".$resultat['metas']['search']."\" target=\"_blank\"><i class=\"fas fa-caret-up\"></i></a>".$resultat['data'][0]['Nbkw']."</td>\n";
            }
            $resultat=json_decode($rows[12], true);
            $home .= "<td>".$resultat['CitationFlow']."</td>\n";
            $home .= "<td>".$resultat['TrustFlow']."</td>\n";
            $home .= "<td>".$resultat['ExtBackLinks']."</td>\n";
            $home .= "<td>".$resultat['RefDomains']."</td>\n";
            $home .= "<td>".$resultat['TopicalTrustFlow_Topic_0']."</td>\n";
            $home .= "<td>".$resultat['TopicalTrustFlow_Topic_1']."</td>\n";
            $home .= "</tr>\n";
        }          
        $home.="</tbody>\n\n";
        $home.="</table>\n";
        $home.="</div>\n";
        $home.="</div>\n";

        $home.="</div>\n";
        $home.="</div>\n";

		mysqli_close($connexion);
		return $home;
	}

//======================================

function afficher_api(){
    //connexion à la BDD
    $connexion=connexion_bdd();
    //selection des contacts dans l'odre décroissant des dates
    $requete="SELECT * FROM api ORDER BY nom_api ASC";
    //execution de la requete
    $resultat=mysqli_query($connexion,$requete);
    //exploitation des résultats rapportés par le SERVEUR
    //à ce stade, se poser la question : on aura 1 seule ligne ou plusieurs lignes ?

    $liste_comptes="<div class=\"card table-responsive p-0\" style=\"height: 300px;\">";
    $liste_comptes.="<div class=\"card-header\">";
    $liste_comptes.="<h3 class=\"card-title\">Liste des API</h3>";
    $liste_comptes.="</div>";
    $liste_comptes.="<table id=\"tab_api\" class=\"table table-bordered table-striped table-head-fixed text-wrap\">\n";
    $liste_comptes.="<tr>\n";
    $liste_comptes.="<th>Nom</th>\n";
    $liste_comptes.="<th>Clé</th>\n";
    $liste_comptes.="<th>Action</th>\n";
    $liste_comptes.="<tr>\n";
    while($row=mysqli_fetch_object($resultat))
        {
        $liste_comptes.="<tr>\n";
        $liste_comptes.="<td>".stripslashes($row->nom_api)."</td>\n";
        $liste_comptes.="<td>".$row->cle_api."</td>\n";
        $liste_comptes.="<td class=\"col-md-2\">";
        $liste_comptes.="<a class=\"btn btn-info btn-sm col-md-4\" href=\"dashboard.php?module=API&action=modifier_api&id_api=".$row->id_api."\"><i class=\"fas fa-pencil-alt\"></i> Edit</a>\n";
        $liste_comptes.="&nbsp;&nbsp;&nbsp;";
        $liste_comptes.="<a class=\"btn btn-warning btn-sm col-md-4\" href=\"dashboard.php?module=API&action=supprimer_api&id_api=".$row->id_api."\"><i class=\"fas fa-trash\"> Delete</a>";
        $liste_comptes.="</td>\n";
        $liste_comptes.="</tr>\n";
        }
    $liste_comptes.="</table>\n";
    $liste_comptes.="</div>\n";
    mysqli_close($connexion);
    return $liste_comptes;
}