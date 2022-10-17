<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\IngredientUnit;
use App\Form\ShopType;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    public $title = 'Shopping List';
    
    #[Route('user/shop', name: 'user_shop')]
    public function index(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {        
        $user = $this->getUser();

        if ($user === null) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        }

        $shop = new Shop();
        $shop->setUser($user->getId());
        $shop->setDate(new \DateTime('now'));

        $form = $this->createForm(ShopType::class, $shop);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $errors = $validator->validate($shop);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }

            $shop          = $form->getData();
            $entityManager = $doctrine->getManager();

            $entityManager->persist($shop);
            $entityManager->flush();

            $id = $shop->getId();

            return $this->redirectToRoute('user_shop_edit', ['id' => $id]);
        }

        // Build Shopping Lists
        $shopListObj = $doctrine->getRepository(Shop::class)->findAllByUser($user->getId());

        $shopList = [];
        foreach ($shopListObj as $key => $value) {
            $shopList[$key] = [
                'id'   => $value->getId(),
                'name' => $value->getName(),
                'date' => $value->getDate(),
                'recipes' => count($value->getRecipes()),
                'items' => count($value->getIngredients()),
            ];
        }

        return $this->renderForm('shop/index.html.twig', [
            'title'    => $this->title,
            'form'     => $form,
            'shopList' => $shopList,
        ]);
    }
    
    #[Route('user/shop/view/{id}', name: 'user_shop_view')]
    public function view(ManagerRegistry $doctrine, int $id): Response
    {
        $shopList      = $this->getList($id, $doctrine);
        $userId        = $this->getUser()->getId();
        $recipeRepo    = $doctrine->getRepository(Recipe::class);
        $unitsRepo     = $doctrine->getRepository(IngredientUnit::class)->findAll();
        $recipesIds    = $shopList->getRecipes();
        $recipes       = [];
        $units         = [];

        foreach ($recipesIds as $key => $value) {

            $recipe = $recipeRepo->find($value);

            // Build Placeholder if No Longer Available
            if ($recipe === null) {
                $recipes[$key] = [
                    'id'       => null,
                    'author'   => null,
                    'name'     => 'No Longer Available',
                    'updated'  => null,
                    'shopQty'  => 0,
                    'shopLast' => null,
                ];

                continue;
            }

            $recipes[$key] = [
                'id'       => $recipe->getId(),
                'author'   => $recipe->getAuthor(),
                'name'     => $recipe->getName(),
                'updated'  => $recipe->getUpdated(),
                'shopQty'  => $recipe->getShopQty(),
                'shopLast' => $recipe->getShopLast(),
            ];
        }

        foreach ($unitsRepo as $key => $value) {
            $units[] = [
                'id'       => $value->getId(),
                'unitName' => $value->getUnitName(),
                'unitAbb'  => $value->getUnitAbbreviation(),
            ];
        }
        
        return $this->renderForm('shop/view.html.twig', [
            'title'       => $this->title,
            'user'        => $userId,
            'recipes'     => $recipes,
            'list'        => $shopList,
            'units'       => $units,
        ]);
    }
    
    #[Route('user/shop/view/{id}/build/ingredients', name: 'user_shop_view_build_ingredients')]
    public function buildIngredients(ManagerRegistry $doctrine, int $id): Response
    {
        $shopList       = $this->getList($id, $doctrine);
        $entityManager  = $doctrine->getManager();
        $ingredientRepo = $doctrine->getRepository(Ingredient::class);
        $unitRepo       = $doctrine->getRepository(IngredientUnit::class);
        $recipesIds     = $shopList->getRecipes();
        $ingredientArr  = [];
        
        foreach ($recipesIds as $key => $recipeId) {
            $recipeIngredients = $ingredientRepo->findByRecipeId($recipeId);

            foreach ($recipeIngredients as $key => $ingredient) {
                $ingredientId     = $ingredient->getId();
                $name             = $ingredient->getName();
                $unit             = $ingredient->getUnit();
                $qty              = $ingredient->getQty();
                $unitName         = $unitRepo->find($unit)->getUnitName();
                $unitAbb          = $unitRepo->find($unit)->getUnitAbbreviation();

                // First We Check if Ingredient Name is Already in Array
                $arrayColumn = array_column($ingredientArr, 'name');
                $arrKey      = false;
                $arrKey      = array_search($name, $arrayColumn);
                if ($arrKey !== false) {

                    // If it's there we need to see if units are the same
                    if ($ingredientArr[$arrKey]['unit'] == $unitName) {

                        $ingredientArr[$arrKey]['ids'][] = $ingredientId;
                        $ingredientArr[$arrKey]['qty'] += $qty;
                        $ingredientArr[$arrKey]['inDish']++;
                        continue;
                    }
                }

                $ingredientArr[] = [
                    'name'    => $name,
                    'unit'    => $unitName,
                    'unitAbb' => $unitAbb,
                    'qty'     => $qty,
                    'inDish'  => 1,
                    'ids'     => [$ingredientId],
                    'cart'    => 0,
                ];

            }
        }
        $shopList->setIngredients($ingredientArr);
        $entityManager->flush();

        return $this->redirectToRoute('user_shop_view', ['id' => $id]);
    }
    
    #[Route('user/shop/view/{id}/update/ingredients/{type}/{index}/{value}', name: 'user_shop_view_ingredients_update_cart')]
    public function updateIngredientCart(ManagerRegistry $doctrine, int $id, string $type, int $index, mixed $value = false): Response
    {
        $shopList      = $this->getList($id, $doctrine);
        $ingredients   = $shopList->getIngredients();
        $entityManager = $doctrine->getManager();

        switch ($type) {
            case 'cart':
                ($ingredients[$index]['cart'] == 0) ? $ingredients[$index]['cart'] = 1 : $ingredients[$index]['cart'] = 0;
                break;
            case 'qty':
                $ingredients[$index]['qty'] = $value;
                break;
            default:
                return new JsonResponse('false');
                break;
        }

        $shopList->setIngredients($ingredients);
        $entityManager->flush();

        return new JsonResponse(true);
    }
    
    #[Route('user/shop/view/{id}/add/ingredients/{name}/{qty}/{unit}', name: 'user_shop_view_ingredients_add')]
    public function addIngredient(ManagerRegistry $doctrine, int $id, string $name, string $unit, float $qty = 1): Response
    {
        $shopList      = $this->getList($id, $doctrine);
        $entityManager = $doctrine->getManager();
        $ingredients   = $shopList->getIngredients();
        $ingredients[] = [
            'ids'     => [],
            'qty'     => $qty,
            'cart'    => 0,
            'name'    => $name,
            'unit'    => $unit,
            'inDish'  => 0,
            'unitAbb' => $unit,
        ];

        $shopList->setIngredients($ingredients);
        $entityManager->flush();

        return new JsonResponse(true);
    }
    
    #[Route('user/shop/view/{id}/remove/ingredients/{index}', name: 'user_shop_view_ingredients_remove')]
    public function removeIngredient(ManagerRegistry $doctrine, int $id, int $index): Response
    {
        $shopList      = $this->getList($id, $doctrine);
        $entityManager = $doctrine->getManager();
        $ingredients   = $shopList->getIngredients();
                         unset($ingredients[$index]);
        $ingredients   = array_values($ingredients);

        $shopList->setIngredients($ingredients);
        $entityManager->flush();

        return new JsonResponse(true);
    }
    
    #[Route('user/shop/edit/{id}', name: 'user_shop_edit')]
    public function edit(ManagerRegistry $doctrine, int $id): Response
    {
        $shopList = $this->getList($id, $doctrine);
        $userId   = $this->getUser()->getId();

        return $this->renderForm('shop/edit.html.twig', [
            'title'            => $this->title,
            'user'             => $userId,
            'shop'             => $shopList,
        ]);
    }
    
    #[Route('user/shop/edit/{id}/add/{recipe}', name: 'user_shop_edit_add_recipe')]
    public function editAdd(ManagerRegistry $doctrine, int $id, string $recipe): Response
    {
        $shopList      = $this->getList($id, $doctrine);
        $entityManager = $doctrine->getManager();
        $recipes       = $shopList->getRecipes();
        $recipes[]     = $recipe;

        // !!! Need to check if recipe exists, but not today...
        $shopList->setRecipes($recipes);
        $entityManager->flush();

        return new JsonResponse(true);
    }
    
    #[Route('user/shop/edit/{id}/remove/{recipe}', name: 'user_shop_edit_remove_recipe')]
    public function editRemove(ManagerRegistry $doctrine, int $id, string $recipe): Response
    {
        $shopList      = $this->getList($id, $doctrine);
        $entityManager = $doctrine->getManager();
        $recipes       = $shopList->getRecipes();
        $arrKey        = array_search($recipe, $recipes, true);
                         unset($recipes[$arrKey]);
        $recipes       = array_values($recipes);

        $shopList->setRecipes($recipes);
        $entityManager->flush();

        return new JsonResponse(true);
    }
    
    #[Route('user/shop/delete/{id}', name: 'user_shop_delete')]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $shopList = $this->getList($id, $doctrine);
        $shopList->remove($shopList->find($id), true);

        return $this->redirectToRoute('user_shop');
    }

    // ##############
    // JSON Responses
    // ##############
    
    #[Route('user/shop/view/{id}/return/ingredients', name: 'user_shop_view_ingredients_json')]
    public function viewIngredients(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, int $id): Response
    {
        $shopList = $this->getList($id, $doctrine);
        
        return new JsonResponse($shopList->getIngredients());
    }

    #[Route('user/shop/view/{id}/return/recipes_in_list', name: 'user_shop_view_recipes_in_list_json')]
    public function viewRecipesInList(ManagerRegistry $doctrine, int $id): Response
    {
        $shopList        = $this->getList($id, $doctrine);
        $recipeRepo      = $doctrine->getRepository(Recipe::class);
        $shopListRecipes = $shopList->getRecipes();
        $recipes         = [];

        foreach ($shopListRecipes as $key => $value) {

            $recipe = $recipeRepo->find($value);

            // Skip Recipe if No Longer Available
            if ($recipe === null) {

                continue;
            }
            
            $recipes[$key] = [
                'id'      => $recipe->getId(),
                'author'  => $recipe->getAuthor(),
                'name'    => $recipe->getName(),
                'updated' => date_format($recipe->getUpdated(), "Y-m-d"),
            ];
        }
        
        return new JsonResponse($recipes);
    }

    #[Route('user/shop/view/{id}/return/recipes_available', name: 'user_shop_view_recipes_available_json')]
    public function viewRecipesAvailable(ManagerRegistry $doctrine, int $id): Response
    {
        $shopList      = $this->getList($id, $doctrine);
        $userId        = $this->getUser()->getId();
        $recipesInList = $shopList->getRecipes();
        $recipes       = $doctrine->getRepository(Recipe::class)->MySqlFindVisibleAndExcludingIDs($userId, $recipesInList);

        // Format Date
        foreach ($recipes as $key => $value) {
            $date = date_create($recipes[$key]['updated']);
            $recipes[$key]['updated'] = date_format($date, "Y-m-d");
        }

        return new JsonResponse($recipes);
    }

    // ##############
    // Class Methods
    // ##############

    private function getList(int $id, ManagerRegistry $doctrine): mixed
    {
        
        $shopList = $doctrine->getRepository(Shop::class)->find($id);

        if ($shopList === null) {
            $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'You\'re not authorized to perform this action');
        }

        $user   = $this->getUser();
        if ($user === null) {
            $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'You\'re not authorized to perform this action');
        }

        $owner = $shopList->getUser();

        if ($owner == $user->getId()) {
            return $shopList;
        }

        $this->denyAccessUnlessGranted('DENY', 'Not Allowed', 'You\'re not authorized to perform this action');
    }
}
