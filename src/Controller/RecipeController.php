<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\IngredientUnit;
use App\Entity\Steps;
use App\Entity\Recommendations;
use App\Entity\User;
use App\Form\RecipeType;
use App\Form\IngredientType;
use App\Form\StepsType;
use App\Form\RecommendationsType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    #[Route('/recipe/index', name: 'app_recipes')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $personal = $request->get('personal');
        if ($personal == true) {
            $user = $this->getUser();

            if ($user === null) {
                $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            }
            $recipe = $doctrine->getRepository(Recipe::class)->findPersonalRecipes($this->getUser()->getId());
        } else {
            $recipe = $doctrine->getRepository(Recipe::class)->findAccessibleRecipes();
        }
        $user   = $doctrine->getRepository(User::class);

        $recipes = [];
        foreach ($recipe as $key => $value) {
            $recipes[$key] = $value->getAll();
            $recipes[$key]['author'] = $user->findUser($recipes[$key]['author'])->getUsername();
        }

        return $this->render('recipe/index.html.twig', [
            'recipes'  => $recipes,
            'personal' => $personal,
        ]);
    }

    #[Route('/recipe/show/{id}', name: 'app_recipe')]
    public function show(Request $request, ValidatorInterface $validator, ManagerRegistry $doctrine, int $id): Response
    {
        $user = $this->getUser();

        // Database Objects
        $recipe             = $doctrine->getRepository(Recipe::class)->find($id);
        $ingredient         = $doctrine->getRepository(Ingredient::class);
        $steps              = $doctrine->getRepository(Steps::class);
        $recommendations    = $doctrine->getRepository(Recommendations::class);
        $unit               = $doctrine->getRepository(IngredientUnit::class);
        $author             = $doctrine->getRepository(User::class);

        if (!$recipe) {
            throw $this->createNotFoundException(
                'No recipes found with recipe id: '.$id
            );
        }

        $recipeData = $recipe->getAll();
        $ingredientData = [];
        $stepsData = [];
        $recommendationsData = [];
        
        $owner = $this->checkRecipeOwner($id, $doctrine);

        if ($recipeData['visibility'] == 'private') {
            if ($user === null) {
                $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            }
            if (!$owner) {      
                $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'The Author of the recipe has set the visibility to private');
            }
        }

        // Do Author
        $recipeData['author'] = $author->findUser($recipeData['author'])->getUsername();

        // Build Ingredients
        $recipeIngredients = $ingredient->findByRecipeId($id);

        if (!empty($recipeIngredients)){
            foreach ($recipeIngredients as $key => $value) {
                $ingredientUnit = $unit->find($value->getUnit());

                $ingredientData[ucwords($value->getName())] = [
                    'id'       => $value->getId(),
                    'name'     => ucwords($value->getName()),
                    'qty'      => $value->getQty(),
                    'unitName' => $ingredientUnit->getUnitName(),
                    'unitAbb'  => $ingredientUnit->getUnitAbbreviation(),
                ];
            }
        }

        // Build Steps
        $recipeSteps = $steps->findByRecipeId($id);
        $stepNumber  = 0;

        if (!empty($recipeSteps)){
            foreach ($recipeSteps as $key => $value) {
                $stepNumber = $value->getStep();

                $stepsData[$value->getStep()] = [
                    'id'   => $value->getId(),
                    'step' => $value->getStep(),
                    'text' => $value->getText(),
                ];

                // Build Step Recommendations
                $recipeRecommendations = $recommendations->findByTypeAndId('step', $value->getId());

                if(!empty($recipeRecommendations)) {
                    foreach ($recipeRecommendations as $rkey => $rvalue) {
                        $stepsData[$value->getStep()]['recommendations'][$rkey] = $rvalue->getRecText();
                    }
                }
            }
        }

        // Build Recipe Recommendations
        $recipeRecommendations = $recommendations->findByTypeAndId('recipe', $id);

        if(!empty($recipeRecommendations)) {
            foreach ($recipeRecommendations as $key => $value) {
                $recommendationsData[] = $value->getRecText();
            }
        }

        // Create all required recipe forms
        $entityManager = $doctrine->getManager();

        // Ingredient Form
        $ingredient = new Ingredient();
        
        // $ingredient->setName('');
        // $ingredient->setQty(0);
        $ingredient->setRecipeId($id);
        
        $form['ingredient'] = $this->createForm(IngredientType::class, $ingredient);
        
        $form['ingredient']->handleRequest($request);
        if ($form['ingredient']->isSubmitted() && $form['ingredient']->isValid()) {

            $errors = $validator->validate($ingredient);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $ingredient = $form['ingredient']->getData();

            $entityManager->persist($ingredient);
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe', ['id' => $id]);
        }

        // Steps Form
        $steps = new Steps();
        $steps->setRecipeId($id);
        $steps->setStep($stepNumber+1);

        $form['steps'] = $this->createForm(StepsType::class, $steps);

        $form['steps']->handleRequest($request);
        if ($form['steps']->isSubmitted() && $form['steps']->isValid()) {

            $errors = $validator->validate($steps);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $steps = $form['steps']->getData();

            $entityManager->persist($steps);
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe', ['id' => $id]);
        }

        // Recipe Recommendation Form
        $recommendations = new Recommendations();
        $recommendations->setType('recipe');
        $recommendations->setTypeId($id);

        $form['recommendations'] = $this->createForm(RecommendationsType::class, $recommendations);

        $form['recommendations']->handleRequest($request);
        if ($form['recommendations']->isSubmitted() && $form['recommendations']->isValid()) {

            $errors = $validator->validate($recommendations);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $steps = $form['recommendations']->getData();

            $entityManager->persist($recommendations);
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe', ['id' => $id]);
        }
        
        return $this->renderForm('recipe/show.html.twig', [
            'owner'               => $owner,
            'recipe'              => $recipeData,
            'ingredients'         => $ingredientData,
            'steps'               => $stepsData,
            'recommendations'     => $recommendationsData,
            'formIngredient'      => $form['ingredient'],
            'formSteps'           => $form['steps'],
            'formRecommendations' => $form['recommendations'],
        ]);
    }
    
    #[Route('/recipe/new', name: 'app_recipe_new')]
    public function new(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->getUser();
        
        $recipe = new Recipe();
        $entityManager = $doctrine->getManager();
        
        $recipe->setStatus('draft');
        $recipe->setVisibility('visible');
        $recipe->setAuthor($user->getId());
        $recipe->setCreated(new \DateTime('now'));
        $recipe->setUpdated(new \DateTime('now'));
        
        $form = $this->createForm(RecipeType::class, $recipe);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $errors = $validator->validate($recipe);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $recipe = $form->getData();

            $entityManager->persist($recipe);
            $entityManager->flush();

            $id = $recipe->getId();

            return $this->redirectToRoute('app_recipe', ['id' => $id]);
        }

        return $this->renderForm('recipe/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/recipe/udpdate/{id}/{statusType}/{statusMessage}', name: 'app_recipe_update_status')]
    public function updateStatus(ManagerRegistry $doctrine, ValidatorInterface $validator, int $id, string $statusType, string $statusMessage): Response
    {
        $owner = $this->checkRecipeOwner($id, $doctrine);

        if (!$owner) {      
            $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'You\'re not authorized to perform this action');
        }

        $recipe         = $doctrine->getRepository(Recipe::class)->find($id);
        $entityManager  = $doctrine->getManager();

        switch ($statusType) {
            case 'visibility':
                switch ($statusMessage) {
                    case 'public':
                        $recipe->setVisibility('public');
                        break;
                    case 'private':
                        $recipe->setVisibility('private');
                        break;
                    default:
                        $this->denyAccessUnlessGranted('DENY', 'Unknown Operation', 'The operation is not defined');
                        break;
                }
                break;
            case 'status':
                switch ($statusMessage) {
                    case 'published':
                        $recipe->setStatus('published');
                        break;
                    case 'draft':
                        $recipe->setStatus('draft');
                        break;
                    default:
                        $this->denyAccessUnlessGranted('DENY', 'Unknown Operation', 'The operation is not defined');
                        break;
                }
                break;
            default:
                $this->denyAccessUnlessGranted('DENY', 'Unknown Operation', 'The operation is not defined');
                break;
        }

        $errors = $validator->validate($recipe);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        $entityManager->persist($recipe);
        $entityManager->flush();

        return $this->redirectToRoute('app_recipe', ['id' => $id]);
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
