<?php

namespace App\Controller;

use App\Entity\Personnage;
use App\Form\PersonnageType;
use App\MesServices\HandleImage;
use App\Repository\PersonnageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/personnage")
 */
class PersonnageController extends AbstractController
{
    /**
     * @Route("/", name="personnage_index", methods={"GET"})
     */
    public function index(PersonnageRepository $personnageRepository): Response
    {
        return $this->render('personnage/index.html.twig', [
            'personnages' => $personnageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="personnage_new", methods={"GET","POST"})
     */
    public function new(Request $request, HandleImage $handleImage): Response
    {
        $personnage = new Personnage();
        $form = $this->createForm(PersonnageType::class, $personnage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image_upload')->getData(); 

            if($imageFile)
            {
                $handleImage->saveImage($imageFile, $personnage);

            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($personnage);
            $entityManager->flush();

            return $this->redirectToRoute('personnage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('personnage/new.html.twig', [
            'personnage' => $personnage,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="personnage_show", methods={"GET"})
     */
    public function show(Personnage $personnage): Response
    {
        return $this->render('personnage/show.html.twig', [
            'personnage' => $personnage,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="personnage_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Personnage $personnage, HandleImage $handleImage): Response
    {
        $form = $this->createForm(PersonnageType::class, $personnage);
        $form->handleRequest($request);

        $vintageImage = $personnage->getImage();
        

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image_upload')->getData(); 

            if($imageFile)
            {
                $handleImage->editImage($imageFile, $personnage, $vintageImage); 



            }


            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('personnage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('personnage/edit.html.twig', [
            'personnage' => $personnage,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="personnage_delete", methods={"POST"})
     */
    public function delete(Request $request, Personnage $personnage, HandleImage $handleImage): Response
    {



        if ($this->isCsrfTokenValid('delete'.$personnage->getId(), $request->request->get('_token'))) {

            $vintageImage = $personnage->getImage();

            $handleImage->deleteImage($vintageImage); 

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($personnage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('personnage_index', [], Response::HTTP_SEE_OTHER);
    }
}
