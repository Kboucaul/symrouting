<?php

/**
 * BIENVENUE DANS CE COURS SUR LE COMPOSANT SYMFONY/ROUTING !
 * ----------------------
 * Dans ce cours nous allons étudier ce fabuleux composant qui permet de créer et de gérer des routes (adresses) intelligentes et intelligibles !
 * 
 * PRESENTATION DE L'APPLICATION (SIMPLE) :
 * ----------------
 * Nous avons ici une application de gestion de tâches très simple : pas de base de données, pas de réelles manipulations de données, ce n'est
 * qu'à des fins d'exemples.
 * 
 * Elle possède 3 pages distinctes :
 * - /index.php (ou /index.php?page=list) : permet d'afficher la liste des tâches contenue dans le fichier data.php (voir le fichier pages/list.php)
 * - /index.php?page=show&id=100 : permet d'afficher la tâche dont l'identifiant est 100 en détails (voir le fichier pages/show.php)
 * - /index.php?page=create (en GET) : permet d'afficher le formulaire de création (voir le fichier pages/create.php)
 * - /index.php?page=create (en POST) : permet de traiter le formulaire de création (toujours dans pages/create.php)
 * 
 * CREER DES ROUTES PERSONNALISEES (ET JOLIES ?) :
 * ----------------
 * On souhaite désormais pouvoir gérer tout ça avec des routes plus "propres" :
 * - /index.php (ou /index.php?page=list) deviendrait juste / 
 * - /index.php?page=show&id=100 deviendrait /show/100
 * - /index.php?page=create (en GET) deviendrait /create (en GET)
 * - /index.php?page=create (en POST) deviendrait /create (en POST)
 * 
 * Ca vous dit ? Alors commencez par bien analayser l'application dans son état actuel pour bien la comprendre, et passez à la section suivante
 * 
 */

use App\Controller\TaskController;
use App\Controller\HelloController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

require __DIR__ . '/vendor/autoload.php';

/*
**  Le controller est chargé dans l'autoload, pas besoin de le require :
**  Voir fichier composer.json etapres sa modif bien faire un composer
**  autoload-dump
*/

//$controller = new HelloController();
//On appelle la methode sayHello
//$controller->sayHello();
//$callable = [new HelloController, "sayHello"];
//Revient a dire $controller->sayHello();
//Magnifique!!

//Ces deux pochaines lignes font la meme chose!
/*
    $callable();
    call_user_func($callable);
*/


/*
**  On donne un parametre name par defaut
**  Ex : si on tappe /hello     => Hello Wrld
**       si on tappe /hello/Jog => Hello Jog
**
**  Note : On peut ajouter des parametres
**  ex 'reponse' => 42 => on aura un champ
**  reponse dans notre tableau avec 42.
**  Voir dd(current_route)
**
**  A premiere vu ca ne sert pas trop mais au final
**  on pourra passer des parametres qui n'appartiennent
**  pas a l'URL.
**  Puis plus tard on passera notre controller
**
**  On passe en 3 eme arg des requirments
*/
$helloRoute     = new Route('/hello/{name}',
                //  Qaund on va taper /hello/Jog
                //  On aura la route /hello/name avec name = jog
                //  On aura par defaut name = World
                //  On appellera le controller HelloController
                //['name' => 'World', 'controller' => [new HelloController, "sayHello"]]
                ['name' => 'World', 'controller' => 'App\Controller\HelloController@sayHello']
                /*  Requirement ici on demande via une
                **  regex que name est 3 characteres
                **  Ce sont des contraintes de route
                */
            );




                    //callable : 1 arg => objet, 2em arg methode que l'n veut appeler
$listRoute      = new Route('/', ['controller' => 'App\Controller\TaskController@index']);
$createRoute    = new Route('/create',
 ['controller' => 'App\Controller\TaskController@create'],
 [],
 [],
 'localhost',
 ['http'],
 ['POST','GET']);
//l'identifiant est de base a 100
//['id'] => 100     <====> {id?100}
//['id'] => '\d+'   <====> ['\d+']
/*
**  Ici on passe une chaine et non plus iun objet pour eviter de saturer la memoire en
**  créant systematiquement des instances d'objets dont on ne se servira pas.
**  En effet on appelle une route, inutile de creer alors 4 instances.
**  On va ensuite transformer cette chaine en callable
*/
$showRoute      = new Route('/show/{id?100}', ["controller" => 'App\Controller\TaskController@show'], [
    //Pour la route show on  veut un numerique
    // 1 ou plus
    'id' => '\d+'
]);

/*
**  Comparaison :
**  /create         VS      /index.php?page=create
**  /show?id=100    VS      /index.php?page=show&id=100
*/

/*
**  Creation d'une collection
*/

$collection = new RouteCollection();
$collection->add('list', $listRoute);
$collection->add('create', $createRoute);
$collection->add('show', $showRoute);
$collection->add('hello', $helloRoute);

/*
**  On crée un matcher
*/
//dump($_SERVER);
/*
**  Le requestContext recupere des infos sur la requete actuelle
**  Normalmeent il prend une url de base (il peut se debrouiller seul)
**  Sauf que par defaut il prend GET et ca peut poser des pb pour les
**  formulaires par exemple.
**
**  Sur /create
**      Si on fait un dump('$_SERVER'), on trouve la REQUEST_METHOD =GET
**      Si on poste le form on obtient ('$_SERVER') = POST
**
**  On peut doinc passer $_SERVER a requestContext
**  
*/
$matcher    = new UrlMatcher($collection, new RequestContext('', $_SERVER['REQUEST_METHOD']));

/*
**  On crée un UrlGenerator
**  Cela va nous créer dynamiquement une url avec potentiellement un parametre
*/

$generator = new UrlGenerator($collection, new RequestContext('', $_SERVER['REQUEST_METHOD']));
//dd($generator->generate('show', ['id' => 100]));

//  Le matcher aura touours le nom de la route et potentiellement
//  des parametres de route.
//  $resultat   = $matcher->match('/show/100');
//  dd($resultat);
//  Renvoie un tableau avec le nom de la route matchant avec le param '/'
//  Ici listRoute
//  ex : $resultat = $matcher->match('/');

/*
**  $_SERVER['PATH_INFO] => renvoie le chemin sur lequel on est
**  ex : localhost:3000/create ==> /create
**  /!\ Le path_info disparait quand on est sur '/'
*/
//var_dump($_SERVER['PATH_INFO']);
$pathInfo = '/';
if (isset($_SERVER['PATH_INFO']))
    $pathInfo = $_SERVER['PATH_INFO'];

try {
    $currentRoute = $matcher->match($pathInfo);
    //  On voit bien que notre controller HelloController
    //  fonctionne bien
    //dd($currentRoute);
    //var_dump($resultat);    // ['_route => 'list]
    /* $page = le nom d'un fichier dans pages
    **       = NOM DES ROUTES !
    */
    $controller = $currentRoute['controller'];
    $currentRoute['generator'] = $generator;
    //dump($currentRoute);
    // 'App/Controller/TaskController@sayHello'
    // Ici sayHello est la methode appelée
    // et @ est notre separateur entre controller et s amethode
    $className = substr($controller, 0, strpos($controller, '@'));
    //dd($className) => "App/Controller/TaskController"
    //Genial ici on a cree une instance avec une chaine de char.
    $methode = substr($controller, strpos($controller, '@') + 1);
    //dd($methode) ==> sayHello
    $instance = new $className();
    //dd(instance) ==> objet 

    //revient a faire un
    //$instance->$methode($currentRoute)
    call_user_func([$instance, $methode], $currentRoute);
} catch(ResourceNotFoundException $e) { // == the resource wasn't found
    require 'pages/404.html.php';
    return ;
}

//===================ANCIEN FONCTIONNEMENT===================
//====================SANS COMPOSANT ROUTING=================

/**
 * LES PAGES DISPONIBLES
 * ---------
 * Afin de pouvoir être sur que le visiteur souhaite voir une page existante, on maintient ici une liste des pages existantes
 */
//$availablePages =  [
//    'list', 'show', 'create'
//];

// Par défaut, la page qu'on voudra voir si on ne précise pas (par exemple sur /index.php) sera "list"
//$page = 'list';

// Si on nous envoi une page en GET, on la prend en compte (exemple : /index.php?page=create)
//if (isset($_GET['page'])) {
//    $page = $_GET['page'];
//}

// Si la page demandée n'existe pas (n'est pas dans le tableau $availablePages)
// On affiche la page 404
//if (!in_array($page, $availablePages)) {
//    require 'pages/404.php';
//    return;
//}

/**
 * ❌ ATTENTION DEMANDEE !
 * -----------
 * Ici, un moyen simple d'obeir au visiteur et de lui présenter ce qu'il demande c'est d'inclure le fichier qui porte le même nom que la 
 * variable $page. 
 * 
 * => EXTREMENT DANGEREUX ! Ca veut dire que le visiteur pilote l'inclusion de scripts PHP, quelqu'un de malin pourrait s'en servir pour inclure 
 * un script non prévu ou voulu. On est un peu protégé par la condition juste au dessus, mais c'est quand même HYPER LIMITE.
 * 
 * Comment allons nous réparer ça dans les prochaines sections ?
 * 
 * ❌ AUTRE PROBLEME DE TAILLE ICI : LE COUPLAGE DE L'URL ET DES NOMS DE FICHIERS
 * ------------
 * Le fichier que l'on va inclure porte le même nom que le paramètre $_GET['page']. C'est à dire que si on appelle /index.php?page=create
 * c'est le fichier pages/create.php qui va être inclus.
 * 
 * La conséquence, c'est que si demain je décide que le formulaire de création devrait se trouver sur /index.php?page=new il faudra que je
 * renomme forcément le fichier pages/create.php en pages/new.php et inversement (l'enfer)
 */
//require_once "pages/$page.php";
