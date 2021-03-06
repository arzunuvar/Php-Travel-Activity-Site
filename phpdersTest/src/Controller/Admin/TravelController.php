<?php

namespace App\Controller\Admin;

use App\Entity\Travel;
use App\Form\TravelType;
use App\Repository\TravelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/travel")
 */
class TravelController extends AbstractController
{
    /**
     * @Route("/", name="admin_travel_index", methods={"GET"})
     */
    public function index(TravelRepository $travelRepository): Response
    {
        return $this->render('admin/travel/index.html.twig', [
            'travels' => $travelRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_travel_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $travel = new Travel();
        $form = $this->createForm(TravelType::class, $travel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            /** @var file $file */
            $file = $form['image']->getData();
            if($file) {
                $fileName = $this->generateUniqueFileName() .'.' . $file->guessExtension();

                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch(FileException $e) {

                }
                $travel->setImage($fileName);
            }



            $entityManager->persist($travel);
            $entityManager->flush();

            return $this->redirectToRoute('admin_travel_index');
        }

        return $this->render('admin/travel/new.html.twig', [
            'travel' => $travel,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_travel_show", methods={"GET"})
     */
    public function show(Travel $travel): Response
    {
        return $this->render('admin/travel/show.html.twig', [
            'travel' => $travel,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_travel_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Travel $travel): Response
    {
        $form = $this->createForm(TravelType::class, $travel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form['image']->getData();
            if($file) {
                $fileName = $this->generateUniqueFileName() .'.' . $file->guessExtension();

                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch(FileException $e) {

                }
                $travel->setImage($fileName);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_travel_index');
        }

        return $this->render('admin/travel/edit.html.twig', [
            'travel' => $travel,
            'form' => $form->createView(),
        ]);
    }


    /**
     * return string
     */
    private function generateUniquefileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }


    /**
     * @Route("/{id}", name="admin_travel_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Travel $travel): Response
    {
        if ($this->isCsrfTokenValid('delete'.$travel->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($travel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_travel_index');
    }
}
