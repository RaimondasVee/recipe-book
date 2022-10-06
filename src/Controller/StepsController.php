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

    #[Route('/recipe/show/{recipeId}/steps/delete/{stepId}', name: 'app_step_delete')]
    public function delete(ManagerRegistry $doctrine, int $recipeId, int $stepId) {
        $owner = $this->checkRecipeOwner($recipeId, $doctrine);

        if (!$owner) {      
            $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'You\'re not authorized to perform this action');
        }

        $steps = $doctrine->getRepository(Steps::class)->findBy(['recipeId' => $recipeId], ['step' => 'asc']);

        var_dump($steps);

        $entityManager = $doctrine->getManager();
        // $entityManager->remove($ingredient);
        // $entityManager->flush();

        return false;
        
        // return $this->redirectToRoute('app_recipe', ['id' => $recipeId]);
    }

    private function checkRecipeOwner($id, ManagerRegistry $doctrine): mixed
    {
        $user   = $this->getUser();
        if ($user === null) {
            return false;
        }

        $author = $doctrine->getRepository(Recipe::class)->find($id)->getAuthor();

        if ($author == $user->getId()) {
            return true;
        }

        return false;
    }
}