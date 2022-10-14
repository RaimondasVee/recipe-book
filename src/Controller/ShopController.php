<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Shop;
use App\Form\ShopType;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

            $shop = $form->getData();

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
            ];
        }

        return $this->renderForm('shop/index.html.twig', [
            'title'    => $this->title,
            'form'     => $form,
            'shopList' => $shopList,
        ]);
    }
    
    #[Route('user/shop/view', name: 'user_shop_view')]
    public function view(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {        
        $user = $this->getUser();

        if ($user === null) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        }
        $entityManager = $doctrine->getManager();
        $shopListObj = $doctrine->getRepository(Shop::class)->findAllByUser($user->getId());
        
        return $this->renderForm('shop/index.html.twig', [
            'title'    => $this->title,
        ]);
    }
    
    #[Route('user/shop/edit/{id}', name: 'user_shop_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, int $id): Response
    {        
        $user = $this->getUser();

        if ($user === null) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        }

        $userId = $user->getId();

        // $entityManager = $doctrine->getManager();
        $shopListObj     = $doctrine->getRepository(Shop::class)->find($id);
        $recipe          = $doctrine->getRepository(Recipe::class);
        $shopListRecipes = $shopListObj->getRecipes();

        $recipesAvailable = $doctrine->getRepository(Recipe::class)->MySqlFindVisibleAndExcludingIDs($userId, $shopListRecipes);
        $recipesInList = [];
        foreach ($shopListRecipes as $key => $value) {
            $recipesInList[$key] = [
                'id'      => $recipe->find($value)->getId(),
                'author'  => $recipe->find($value)->getAuthor(),
                'name'    => $recipe->find($value)->getName(),
                'updated' => $recipe->find($value)->getUpdated(),
            ];
        }

        return $this->renderForm('shop/edit.html.twig', [
            'user'             => $userId,
            'title'            => $this->title,
            'shop'             => $shopListObj,
            'recipesInList'    => $recipesInList,
            'recipesAvailable' => $recipesAvailable,
        ]);
    }
    
    #[Route('user/shop/edit/{id}/add/{recipe}', name: 'user_shop_edit_add_recipe')]
    public function editAdd(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, int $id, string $recipe): Response
    {        
        $user = $this->getUser();

        if ($user === null) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        }

        // $entityManager = $doctrine->getManager();
        $shopListObj   = $doctrine->getRepository(Shop::class)->find($id);
        $entityManager = $doctrine->getManager();

        $recipes = $shopListObj->getRecipes();
        $recipes[] = $recipe;

        // !!! Need to check if recipe exists, but not today...
        $shopListObj->setRecipes($recipes);
        $entityManager->flush();

        return $this->redirectToRoute('user_shop_edit', ['id' => $id]);
    }
    
    #[Route('user/shop/edit/{id}/remove/{recipe}', name: 'user_shop_edit_remove_recipe')]
    public function editRemove(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, int $id, string $recipe): Response
    {        
        $user = $this->getUser();

        if ($user === null) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        }

        // $entityManager = $doctrine->getManager();
        $shopListObj   = $doctrine->getRepository(Shop::class)->find($id);
        $entityManager = $doctrine->getManager();

        $recipes = $shopListObj->getRecipes();

        // Get Array Key to Remove
        $arrKey = array_search($recipe, $recipes, true);
        unset($recipes[$arrKey]);

        // !!! Need to check if recipe exists, but not today...
        $shopListObj->setRecipes($recipes);
        $entityManager->flush();

        return $this->redirectToRoute('user_shop_edit', ['id' => $id]);
    }
    
    #[Route('user/shop/delete', name: 'user_shop_delete')]
    public function delete(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {        
        $user = $this->getUser();

        if ($user === null) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        }
        $entityManager = $doctrine->getManager();
        $shopListObj = $doctrine->getRepository(Shop::class)->findAllByUser($user->getId());
        
        return $this->renderForm('shop/index.html.twig', [
            'title'    => $this->title,
        ]);
    }
}
