<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

// use Symfony\Component\Validator\Validation;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ProductsController extends AbstractController
{
    public function show(ProductRepository $productRepository): Response
    {
        $product = $productRepository
            ->findAll();

        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers);
        $data = $serializer->normalize($product, null);

        $response = new JsonResponse(['success' => true, 'products' => $data]);
        return $response;
    }

    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        // TODO: Validate request

        $parameters = json_decode($request->getContent(), true);
        $name = $parameters['name'];
        
        $product = new Product();
        $product->setName($name);

        $entityManager->persist($product);
        $entityManager->flush();

        $response = new JsonResponse(['success' => true]);
        return $response;
    }
}
