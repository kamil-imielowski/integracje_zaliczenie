<?php
/**
 * Created by PhpStorm.
 * User: toma4
 * Date: 13.12.2017
 * Time: 11:17
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Channel;
use AppBundle\Entity\Movie;
use AppBundle\Entity\Settings;
use AppBundle\Form\MovieType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class MovieController extends Controller
{

    /**
     * @Route("/kategorie", name="app_categories")
     */
    public function categoriesAction(){
        $entityMenager = $this->getDoctrine()->getManager();
        $channels = $entityMenager->getRepository(Channel::class)->findAll();

        return $this->render('Movie/categories.html.twig', ["channels" => $channels]);
    }

    /**
     * @Route("/kategorie/{name}", name="channel_movies")
     * @param Channel $channel
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function channelAction(Channel $channel){
        return $this->render("Movie/channelDetails.html.twig", ["channel" => $channel]);
    }

    /**
     * @Route("/najnowsze", name="app_new_movies")
     *
     */
    public function newAction(){
        $entityMenager = $this->getDoctrine()->getManager();
        $movies = $entityMenager->getRepository(Movie::class)->getLastMovies(15);

        return $this->render('Movie/new_movies.html.twig', ["movies" => $movies]);
    }


    /**
     * @Route("film/{id}/{title}", name="movie_details")
     * @param Movie $movie
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailsAction(Movie $movie){

        $advertising_pl1 = $this->forward('AppBundle:Advertising:place', array('place' => 1));
        $advertising_pl2 = $this->forward('AppBundle:Advertising:place', array('place' => 2));

        // update views
        $movie->setViews($movie->getViews()+1);
        $entityMenager = $this->getDoctrine()->getManager();
        $entityMenager->persist($movie); // ma zapisac obiekt
        $entityMenager->flush(); // wykonanie sql

        //$customizeMovies = $entityMenager->getRepository(Movie::class)->getLastMoviesChannel(5, $movie->getChannel()->getId(), $movie->getId());
        $customizeMovies = $this->customizeMovies();

        return $this->render("Movie/details.html.twig", [
            "movie" => $movie,
            "ad" => $advertising_pl2,
            'ad2' => $advertising_pl1,
            'aboveMovies' => $customizeMovies['above'],
            'underMovies' => $customizeMovies['under']]);
    }


    /**
     * @Route("/film/dodaj", name="movie_add")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request){
        $movie = new Movie();

        //kreator formularza na widok -> w klasie form/movietype
        $form = $this->createForm(MovieType::class, $movie);

        if($request->isMethod("post")){ // sprawdzenie czy formularz zostal wyslany
            $form->handleRequest($request); // uzupelnienie formularza o request post (tak jakby na nowo)

            $entityMenager = $this->getDoctrine()->getManager();
            $entityMenager->persist($movie); // ma zapisac obiekt
            $entityMenager->flush(); // wykonanie sql

            return $this->redirectToRoute("movie_details", ["id" => $movie->getId()]);
        }

        return $this->render("Movie/add.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/{id}/film/edytuj", name="movie_edit")
     * @param Request $request
     * @param Movie $movie
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Movie $movie){
        $form = $this->createForm(MovieType::class, $movie);

        if($request->isMethod("post")){
            $form->handleRequest($request); // uzupelnienie formularza o request post (tak jakby na nowo)

            $entityMenager = $this->getDoctrine()->getManager();
            $entityMenager->persist($movie); // ma zapisac obiekt
            $entityMenager->flush(); // wykonanie sql

            return $this->redirectToRoute("movie_details", ["id" => $movie->getId()]);
        }

        return $this->render("Movie/edit.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/{id}/film/usun", name="movie_delete", methods={"DELETE"})
     * @param Movie $movie
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Movie $movie){
        $entityMenager = $this->getDoctrine()->getManager();
        $entityMenager->remove($movie);
        $entityMenager->flush();

        return $this->redirectToRoute("app_new_movies");
    }

    private function customizeMovies(){
        $em = $this->getDoctrine()->getManager();

        $movies = array();

        //$aboveLogin = $em->getRepository(Settings::class)->findOneBy(['valKey' => 'above_ytPlayer']);
        //$underLogin = $em->getRepository(Settings::class)->findOneBy(['valKey' => 'under_ytPlayer']);

        $channels = $em->getRepository(Channel::class)->findAll();
        shuffle ( $channels );
        $channel = $channels[0];
        $channelUnder = $channels[1];

        if(!empty($channel)) {
            $resultmovies = $em->getRepository(Movie::class)->getLastMoviesChannel(5, $channel->getId());
            $movies['above'] = $resultmovies;
        }else{
            $movies['above'] = null;
        }

        if(!empty($channelUnder)) {
            $resultmovies = $em->getRepository(Movie::class)->getLastMoviesChannel(5, $channelUnder->getId());
            $movies['under'] = $resultmovies;
        }else{
            $movies['under'] = null;
        }

        return $movies;

    }
}