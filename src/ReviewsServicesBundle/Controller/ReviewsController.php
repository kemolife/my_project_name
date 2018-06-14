<?php

namespace ReviewsServicesBundle\Controller;

use ReviewsServicesBundle\Entity\Reviews;
use ReviewsServicesBundle\Services\FacebookService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Review controller.
 *
 * @Route("reviews")
 */
class ReviewsController extends Controller
{
    /**
     * Lists all review entities.
     *
     * @Route("/", name="reviews_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $reviews = $em->getRepository('ReviewsServicesBundle:Reviews')->findAll();

        return $this->render('@ReviewsServices/reviews/index.html.twig', array(
            'reviews' => $reviews,
        ));
    }

    /**
     * @Route("/facebook", name="reviews_facebook")
     * @Method("GET")
     */

    public function facebookReviews()
    {
        return $this->container->get('reviews_services.facebook')->execute();
    }

    /**
     * @Route("/google", name="reviews_google")
     * @Method("GET")
     */

    public function googleReviews()
    {
        return $this->container->get('reviews_services.google')->execute();
    }

    /**
     * @Route("/yelp", name="reviews_yelp")
     * @Method("GET")
     */

    public function yelpReviews()
    {
        return $this->container->get('reviews_services.yelp')->execute();
    }

    /**
     * @Route("/tripadvisor", name="reviews_tripadvisor")
     * @Method("GET")
     */

    public function tripadvisorReviews()
    {
        return $this->container->get('reviews_services.tripadvisor')->execute();
    }

    /**
     * @Route("/zomato", name="reviews_zomato")
     * @Method("GET")
     */

    public function zomatoReviews()
    {
        return $this->container->get('reviews_services.zomato')->execute();
    }

    /**
     * @Route("/ratemds", name="reviews_ratemds")
     * @Method("GET")
     */

    public function ratemdsReviews()
    {
        return $this->container->get('reviews_services.ratemds')->execute();
    }

    /**
     * @Route("/ratemyagent", name="reviews_ratemyagent")
     * @Method("GET")
     */

    public function ratemyagentReviews()
    {
        return $this->container->get('reviews_services.ratemyagent')->execute();
    }

    /**
     * @Route("/whitecoat", name="reviews_whitecoat")
     * @Method("GET")
     */

    public function whitecoatReviews()
    {
        return $this->container->get('reviews_services.whitecoat')->execute();
    }

    /**
     * @Route("/bingplaces", name="reviews_bingplaces")
     * @Method("GET")
     */

    public function bingplacesReviews()
    {
        return $this->container->get('reviews_services.bingplaces')->execute();
    }

    /**
     * @Route("/yahoo", name="reviews_yahoo")
     * @Method("GET")
     */

    public function yahooReviews()
    {
        return $this->container->get('reviews_services.yahoo')->execute();
    }

    /**
     * @Route("/parser", name="reviews_parser")
     * @Method("GET")
     */

    public function parserReviews()
    {
        return $this->container->get('reviews_services.parser')->run();
    }

    /**
     * Creates a new review entity.
     *
     * @Route("/new", name="reviews_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $review = new Reviews();
        $form = $this->createForm('ReviewsServicesBundle\Form\ReviewsType', $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($review);
            $em->flush();

            return $this->redirectToRoute('reviews_show', array('id' => $review->getId()));
        }

        return $this->render('@ReviewsServices/reviews/new.html.twig', array(
            'review' => $review,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a review entity.
     *
     * @Route("/{id}", name="reviews_show")
     * @Method("GET")
     */
    public function showAction(Reviews $review)
    {
        $deleteForm = $this->createDeleteForm($review);

        return $this->render('@ReviewsServices/reviews/show.html.twig', array(
            'review' => $review,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing review entity.
     *
     * @Route("/{id}/edit", name="reviews_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Reviews $review)
    {
        $deleteForm = $this->createDeleteForm($review);
        $editForm = $this->createForm('ReviewsServicesBundle\Form\ReviewsType', $review);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('reviews_edit', array('id' => $review->getId()));
        }

        return $this->render('@ReviewsServices/reviews/edit.html.twig', array(
            'review' => $review,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a review entity.
     *
     * @Route("/{id}", name="reviews_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Reviews $review)
    {
        $form = $this->createDeleteForm($review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($review);
            $em->flush();
        }

        return $this->redirectToRoute('reviews_index');
    }

    /**
     * Creates a form to delete a review entity.
     *
     * @param Reviews $review The review entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Reviews $review)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('reviews_delete', array('id' => $review->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
