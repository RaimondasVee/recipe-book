<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\IngredientUnit;
use App\Entity\Steps;
use App\Entity\Recommendations;
use App\Form\RecipeType;
use App\Form\IngredientType;
use App\Form\StepsType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StepsController extends AbstractController
{

    #[Route('/recipe/show/{id}/steps/new', name: 'app_recipe_step_new')]
    public function new(Request $request, ValidatorInterface $validator, ManagerRegistry $doctrine, int $id): Response
    {        
        $entityManager = $doctrine->getManager();
        $steps = new Steps();
        var_dump($id);
        $steps->setRecipeId($id);

        $form = $this->createForm(StepsType::class, $steps);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $errors = $validator->validate($steps);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $steps = $form->getData();

            $entityManager->persist($steps);
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe', ['id' => $id]);
        }

        return $this->renderForm('steps/new.html.twig', [
            'form'         => $form
        ]);
    }
}