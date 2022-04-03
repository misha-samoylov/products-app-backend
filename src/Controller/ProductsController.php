<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ProductsController extends AbstractController
{
    public function show(ProductRepository $productRepository): Response
    {
        $product = $productRepository->findAll();

        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers);
        $data = $serializer->normalize($product, null);

        $response = new JsonResponse(['success' => true, 'products' => $data]);
        return $response;
    }

    public function add(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator): Response
    {
        $entityManager = $doctrine->getManager();

        $parameters = json_decode($request->getContent(), true);
        $name = $parameters['name'];

        // validate a request
        $input = ['name' => $name];

        $constraints = new Assert\Collection([
            'name' => [new Assert\Length(['min' => 3]), new Assert\NotBlank],
        ]);

        $violations = $validator->validate($input, $constraints);

        if (count($violations) > 0) {
            return new JsonResponse(['success' => false]);
        }

        // save to db
        $product = new Product();
        $product->setName($name);

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    public function delete(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator): Response
    {
        $parameters = json_decode($request->getContent(), true);
        $id = $parameters['id'];

        // validate a request
        $input = ['id' => $id];

        $constraints = new Assert\Collection([
            'id' => [new Assert\Type('integer'), new Assert\NotBlank],
        ]);

        $violations = $validator->validate($input, $constraints);

        if (count($violations) > 0) {
            return new JsonResponse(['success' => false]);
        }

        // find and remove item
        $product = $doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            return new JsonResponse(['success' => false]);
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
