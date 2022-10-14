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
    public $title = 'Steps';

    // THIS NEEDS UPDATING!!!!!!!!!!!!!!!!!!!!! AUTHENTICATION!!
    #[Route('/recipe/show/{id}/steps/new', name: 'app_recipe_step_new')]
    public function new(Request $request, ValidatorInterface $validator, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $steps = new Steps();
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
            'title'        => $this->title,
            'form'         => $form
        ]);
    }

    #[Route('/recipe/show/{recipeId}/steps/update/{stepId}', name: 'app_step_update')]
    public function update(Request $request, ManagerRegistry $doctrine, int $recipeId, int $stepId) {
        $owner = $this->checkRecipeOwner($recipeId, $doctrine);

        if (!$owner) {      
            $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'You\'re not authorized to perform this action');
        }

        $text           = $request->request->get('text');
        $step           = $doctrine->getRepository(Steps::class)->find($stepId);
        $recipe         = $doctrine->getRepository(Recipe::class)->find($recipeId);
        $entityManager  = $doctrine->getManager();

        $step->setText($text);
        $recipe->setUpdated(new \DateTime('now'));

        $entityManager->flush();
        
        return $this->redirectToRoute('app_recipe', ['id' => $recipeId]);
    }

    #[Route('/recipe/show/{recipeId}/steps/update/{stepId}/rec/{recId}', name: 'app_step_recommendation_update')]
    public function updateRecommendation(Request $request, ManagerRegistry $doctrine, int $recipeId, int $stepId, mixed $recId) {
        $owner = $this->checkRecipeOwner($recipeId, $doctrine);

        if (!$owner) {      
            $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'You\'re not authorized to perform this action');
        }

        $recipe         = $doctrine->getRepository(Recipe::class)->find($recipeId);
        $entityManager  = $doctrine->getManager();
        $text           = $request->request->get('text');

        if ($recId == 'null') {
            // Create new recommendation
            $recommendation = new Recommendations();

            $recommendation->setRecText($text);
            $recommendation->setType('step');
            $recommendation->setTypeId($stepId);
            $entityManager->persist($recommendation);
        } else {
            // Update existing recommendation
            $recommendation = $doctrine->getRepository(Recommendations::class)->find($recId);

            $recommendation->setRecText($text);
        }

        $recipe->setUpdated(new \DateTime('now'));

        $entityManager->flush();
        
        return $this->redirectToRoute('app_recipe', ['id' => $recipeId]);
    }

    #[Route('/recipe/show/{recipeId}/steps/order/{stepId}/{direction}', name: 'app_step_order')]
    public function order(ManagerRegistry $doctrine, int $recipeId, int $stepId, string $direction) {
        $owner = $this->checkRecipeOwner($recipeId, $doctrine);

        if (!$owner) {      
            $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'You\'re not authorized to perform this action');
        }

        $recipe         = $doctrine->getRepository(Recipe::class)->find($recipeId);
        $steps          = $doctrine->getRepository(Steps::class)->findBy(['recipeId' => $recipeId], ['step' => 'asc']);
        $stepToMove     = $doctrine->getRepository(Steps::class)->find($stepId)->getStep();
        $entityManager  = $doctrine->getManager();

        switch ($direction) {
            case 'up':
                if ($stepToMove == 0) {
                    return $this->redirectToRoute('app_recipe', ['id' => $recipeId]);
                }
                $steps[$stepToMove]->setStep($stepToMove - 1);
                $steps[$stepToMove - 1]->setStep($stepToMove);

                break;
            case 'down':
                if ($stepToMove == count($steps) - 1) {
                    return $this->redirectToRoute('app_recipe', ['id' => $recipeId]);
                }
                $steps[$stepToMove]->setStep($stepToMove + 1);
                $steps[$stepToMove + 1]->setStep($stepToMove);

                break;
            default:
                return $this->redirectToRoute('app_recipe', ['id' => $recipeId]);
        }

        $recipe->setUpdated(new \DateTime('now'));

        $entityManager->flush();
        
        return $this->redirectToRoute('app_recipe', ['id' => $recipeId]);
    }

    #[Route('/recipe/show/{recipeId}/steps/delete/{stepId}', name: 'app_step_delete')]
    public function delete(ManagerRegistry $doctrine, int $recipeId, int $stepId) {
        $owner = $this->checkRecipeOwner($recipeId, $doctrine);

        if (!$owner) {      
            $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'You\'re not authorized to perform this action');
        }

        $recipe         = $doctrine->getRepository(Recipe::class)->find($recipeId);
        $steps          = $doctrine->getRepository(Steps::class)->findBy(['recipeId' => $recipeId], ['step' => 'asc']);
        $stepToDelete   = $doctrine->getRepository(Steps::class)->find($stepId)->getStep();
        $entityManager  = $doctrine->getManager();

        $entityManager->remove($steps[$stepToDelete]);
        $recipe->setUpdated(new \DateTime('now'));

        for ($i=$stepToDelete; $i <= count($steps); $i++) { 
            if (!isset($steps[$i+1])){
                break;
            }

            $steps[$i+1]->setStep($i);
        }

        $entityManager->flush();
        
        return $this->redirectToRoute('app_recipe', ['id' => $recipeId]);
    }

    #[Route('/recipe/show/{recipeId}/steps/delete/{stepId}/rec/{recId}', name: 'app_step_delete_recommendation')]
    public function deleteRecommendation(ManagerRegistry $doctrine, int $recipeId, int $stepId, int $recId) {
        $owner = $this->checkRecipeOwner($recipeId, $doctrine);

        if (!$owner) {      
            $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'You\'re not authorized to perform this action');
        }

        $recipe         = $doctrine->getRepository(Recipe::class)->find($recipeId);
        $recToDelete    = $doctrine->getRepository(Recommendations::class)->find($recId);
        $entityManager  = $doctrine->getManager();

        $entityManager->remove($recToDelete);
        $recipe->setUpdated(new \DateTime('now'));

        $entityManager->flush();
        
        return $this->redirectToRoute('app_recipe', ['id' => $recipeId]);
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