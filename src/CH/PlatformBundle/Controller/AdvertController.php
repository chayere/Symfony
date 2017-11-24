<?php

namespace CH\PlatformBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use CH\PlatformBundle\Form\AdvertType;
use CH\PlatformBundle\Entity\AdvertSkill;
use CH\PlatformBundle\Entity\Application;
use CH\PlatformBundle\Entity\Advert;
use CH\PlatformBundle\Entity\Image;

class AdvertController extends Controller
{

	public function indexAction($page) {

		$nbPerPage = 5;

		if($page < 1) {
			throw new NotFoundHttpException("Page '".$page."' inexisante");
		}

      	$em = $this->getDoctrine()->getManager();
      	$repository = $em->getRepository('CHPlatformBundle:Advert');
      	$adverts = $repository->getAdverts($page, $nbPerPage);

      	$nbPages = (int)(count($adverts)/$nbPerPage);

      	if(count($adverts)%$nbPerPage > 0) {
      		$nbPages = $nbPages + 1;
      	}

      	if($page >= count($adverts)/$nbPerPage + 1) {
      		throw new NotFoundHttpException("Page '".$page."' inexisante");
      	}

      	if(null == $adverts) {
    		echo "Il n'y a pas encore d'annonces sur le site.";	
    	}

		return $this->render('CHPlatformBundle:Advert:index.html.twig', array('listAdverts'=>$adverts, 'nbPages'=>$nbPages, 'page'=>$page));
	}

	public function viewAction($id, Request $request) {

    	$em = $this->getDoctrine()->getManager();

    	$advertRepository = $em->getRepository('CHPlatformBundle:Advert');
    	$advert = $advertRepository->find($id);

    	if(null == $advert) {
    		throw new NotFoundHttpException('L\'annonce d\'id "'. $id .'" n\'existe pas !');	
    	}

    	$applications = $advert->getApplications();

    	$advertSkills = $em->getRepository('CHPlatformBundle:AdvertSkill')->findBy(array('advert'=>$advert));

		return $this->render('CHPlatformBundle:Advert:view.html.twig', array(
			'advert'=>$advert, 
			'applications'=>$applications, 
			'advertSkills'=>$advertSkills
		));

	}

	public function viewSlugAction($slug, $year, $_format) {

		$url = $this->generateUrl('ch_platform_view_slug', array("slug"=>$slug, "year"=>$year, "_format"=>$_format), UrlGeneratorInterface::ABSOLUTE_URL);

		return new Response("On pourrait afficher l'annonce correspondant au
            slug '" .$slug. "', créée en " .$year. " et au format ". $_format .".");

	}

	public function addAction(Request $request) {

		$antispam = $this->get('ch_platform.antispam');

		$advert = new Advert();

		$advert->setDate(new \Datetime());

		$form = $this->createForm(AdvertType::class, $advert);

		if($request->isMethod('POST')) {

			$text = "Ceci est un long texte afin de ne pas être considéré comme spam";

			if($antispam->isSpam($text)) {
				throw new \Exception("Votre message a été détecté comme spam !");
			}

			else {
				$form->handleRequest($request);

				if($form->isValid()) {
					$em = $this->getDoctrine()->getManager();
					$em->persist($advert);
					$em->flush();

					$request->getSession()->getFlashBag()->add('info', 'Annonce bien enregistrée !');
				}
			}

			if(null == $advert->getId()){
				echo "LOL";
			}

			return $this->redirectToRoute('ch_platform_view', array('id'=>$advert->getId()));
		}

		return $this->render('CHPlatformBundle:Advert:add.html.twig', array('form'=>$form->createView()));
	}

	public function editAction($id, Request $request) {

		$em = $this->getDoctrine()->getManager();
		$advert = $em->getRepository('CHPlatformBundle:Advert')->find($id);

		if(null == $advert) {
    		throw new NotFoundHttpException('L\'annonce d\'id "'. $id .'" n\'existe pas !');	
    	}

    	$form = $this->createForm(AdvertType::class, $advert);

		/*$image = $advert->getImage();

		if(null == $image) {
			$image = new Image();
		}

		$image->setUrl("https://statics.sportskeeda.com/wp-content/uploads/2017/02/632998410-lionel-messi-of-fc-barcelona-looks-on-during-gettyimages-1487482168-800.jpg");
		$image->setAlt("Nouvelle image");

		$advert->setImage($image);
		$advert->setContent('LOL');

		$listCategories = $em->getRepository('CHPlatformBundle:Category')->findAll();

		if($advert->getCategories()->isEmpty()) {
			foreach ($listCategories as $category) {
				$advert->addCategory($category);
			}
		}*/

		

		if($request->isMethod('POST')) {

			$form->handleRequest($request);

			if($form->isValid()) {
				$em->flush();
				$request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée !');
			}

			return $this->redirectToRoute('ch_platform_view', array('id'=>$id));
		}

		return $this->render('CHPlatformBundle:Advert:edit.html.twig', array('advert'=>$advert, 'form'=>$form->createView()));

	}

	public function deleteAction($id, Request $request) {

		$em = $this->getDoctrine()->getManager();
    	$advert = $em->getRepository('CHPlatformBundle:Advert')->find($id);

    	if (null === $advert) {
     		throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    	}

    	foreach ($advert->getCategories() as $category) {
    		$advert->removeCategory($category);
    	}

    	$em->flush();

		return $this->render('CHPlatformBundle:Advert:delete.html.twig');

	}

	public function menuAction($limit) {

		$advertRepo = $this->getDoctrine()->getManager()->getRepository('CHPlatformBundle:Advert');

		$list_adverts = $advertRepo->findBy(array(), array('date'=>'desc'), $limit, 0);

		return $this->render('CHPlatformBundle:Advert:menu.html.twig', array('list_adverts'=>$list_adverts));

	}

}